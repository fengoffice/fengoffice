<?php
	chdir($argv[1]);
	define("CONSOLE_MODE", true);
	define('PUBLIC_FOLDER', 'public');
	include "init.php";
	
	session_commit(); // we don't need sessions
	@set_time_limit(0); // don't limit execution of cron, if possible
	ini_set('memory_limit', '2048M');
	
	

	Env::useHelper('permissions');
	
	$user_id = array_var($argv, 2);
	$token = array_var($argv, 3);
	
	// log user in
	$user = Contacts::instance()->findById($user_id);
	if(!($user instanceof Contact) || !$user->isValidToken($token)) {
		throw new Exception("Cannot login with user $user_id and token '$token'");
	}

	CompanyWebsite::instance()->setLoggedUser($user, false, false, false);
		
	Env::useHelper('background_jobs');

	// argv[4] is a spool file listing every member queued by the originating request, so one
	// process now covers what used to be one process per member. Claim it first: the rename is
	// atomic, so the cron sweeper and this request's shutdown handler can never both process it.
	$spool_argument = array_var($argv, 4);
	$spool_file = claim_background_job_spool($spool_argument);
	if ($spool_file === null) {
		die(); // gone, or another worker owns it
	}
	$spool_base = background_job_spool_base($spool_file);
	$jobs = read_background_job_spool($spool_file);

	foreach ($jobs as $job) {
		$member_id = array_var($job, 'member_id');
		$permissions_filename = array_var($job, 'permissions_filename');
		$old_parent_id = array_var($job, 'old_parent_id', -1);

		// The payload file is deleted once its member is done, so a missing file means this job
		// already ran and the batch is being retried after a crash. Re-running it would be
		// destructive: save_member_permissions() treats an unreadable permission string as
		// "no permissions supplied" and rewrites the member from default permissions.
		if (!is_file($permissions_filename)) {
			continue;
		}

		$permissions = file_get_contents($permissions_filename);

		$member = Members::instance()->findById($member_id);
		if (!$member instanceof Member) {
			@unlink($permissions_filename);
			continue;
		}

		try {
			// transaction to save permission tables
			// A lock conflict here is transient: concurrent permission saves contend on the same
			// contact_member_permissions rows, so retry a few times before giving up.
			$result = null;
			$max_attempts = 3;
			for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
				try {
					DB::beginWork();
					$result = save_member_permissions($member, $permissions, true, false, false, false);
					if ($old_parent_id != -1 && $old_parent_id != $member->getParentMemberId()) {
						do_member_parent_changed_refresh_object_permisssions($member_id, $old_parent_id, $member->getParentMemberId());
					}
					DB::commit();
					break;
				} catch (Exception $e) {
					DB::rollback();
					$result = null;
					if ($attempt < $max_attempts && is_retryable_db_lock_error($e)) {
						Logger::log("Lock conflict saving permissions of member $member_id (attempt $attempt of $max_attempts), retrying: ".$e->getMessage());
						sleep($attempt * 5);
						continue;
					}
					Logger::log("Error saving permissions (1): ".$e->getMessage()."\n".$e->getTraceAsString());
				}
			}

			// Nothing was saved, so there is nothing to propagate to the sharing table, the contact member
			// cache or the hooks below. Keep the payload file: it is the only record of what should have
			// been saved, and it allows the save to be replayed. The rest of the batch still runs.
			if (!is_array($result)) {
				Logger::log("Permissions of member $member_id were not saved, keeping the payload in $permissions_filename");
				continue;
			}

			$changed_pgs = array_var($result, 'changed_pgs');
		
			if (is_array($changed_pgs)) {
				foreach ($changed_pgs as $pg_id) {
					try {
						// create flag for this $pg_id
						DB::beginWork();
						$flag = new SharingTableFlag();
						$flag->setPermissionGroupId($pg_id);
						$flag->setMemberId($member->getId());
						$flag->setPermissionString($permissions);
						$flag->setExecutionDate(DateTimeValueLib::now());
						$flag->setCreatedById(logged_user()->getId());
						$flag->save();
						DB::commit();
					
					} catch (Exception $e) {
						DB::rollback();
						Logger::log("Error saving permissions (2): ".$e->getMessage()."\n".$e->getTraceAsString());
					}
				}
			}
		
			$flags_to_delete = array();
		
			// transactions to update_sharing table
			$sharingTablecontroller = new SharingTableController();
			if (is_array($changed_pgs)) {
				$perm_array = json_decode($permissions) ?? [];
				foreach ($perm_array as $pa) {
					if (!isset($pa->m)) $pa->m = $member->getId();
				}
			
				foreach ($changed_pgs as $pg_id) {
					try {
						// update sharing table
						DB::beginWork();
						$sharingTablecontroller->afterPermissionChanged($pg_id, $perm_array);
					
						$flags_to_delete[] = $pg_id;
					
						DB::commit();
					
					} catch (Exception $e) {
						DB::rollback();
						Logger::log("Error saving permissions (2): ".$e->getMessage()."\n".$e->getTraceAsString());
					}
				}
			}
		
			// save tree
			try {
				DB::beginWork();
				$contactMemberCacheController = new ContactMemberCacheController();
				$contactMemberCacheController->afterMemberPermissionChanged($result);
				DB::commit();
			} catch (Exception $e) {
				DB::rollback();
				Logger::log("Error saving permissions (3): ".$e->getMessage()."\n".$e->getTraceAsString());
			}
		
			// transaction for the hooks
			try {
				DB::beginWork();
				$varret = array_var($result, 'member');
				Hook::fire('after_save_member_permissions', array('member' => array_var($result, 'member'), 'user_id' => $user_id), $varret);
			
				DB::commit();
			} catch (Exception $e) {
				DB::rollback();
				Logger::log("Error saving permissions (4): ".$e->getMessage()."\n".$e->getTraceAsString());
			}
		
		
			// delete processed flags
			if (count($flags_to_delete) > 0) {
				try {
					DB::beginWork();
				
					// delete flags
					SharingTableFlags::instance()->delete("member_id=$member_id AND permission_group_id IN (".implode(',', $flags_to_delete).")");
				
					DB::commit();
				} catch (Exception $e) {
					DB::rollback();
					Logger::log("Error saving permissions (5 - failed to delete processed flags [".implode(',',$flags_to_delete)."]): ".$e->getMessage()."\n".$e->getTraceAsString());
				}
			}
		} catch (Exception $e) {
			// One failing member must not cost the remaining ones in the batch.
			Logger::log("Error saving permissions for member $member_id: ".$e->getMessage()."\n".$e->getTraceAsString());
		}

		@unlink($permissions_filename);
	}

	@unlink($spool_file);
	// The originating request may have recreated the base spool with lines appended after this
	// batch was claimed; its sidecar must survive so those can still be recovered.
	if (!is_file($spool_base)) {
		@unlink($spool_base . '.meta');
	}
