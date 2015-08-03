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
	$user = Contacts::findById($user_id);
	if(!($user instanceof Contact) || !$user->isValidToken($token)) {
		throw new Exception("Cannot login with user $user_id and token '$token'");
	}

	CompanyWebsite::instance()->setLoggedUser($user, false, false, false);
		
	// save permissions
	$member_id = array_var($argv, 4);
	$permissions_filename = array_var($argv, 5);
	$old_parent_id = array_var($argv, 6);
	
	$permissions = file_get_contents($permissions_filename);
	
	$member = Members::findById($member_id);
	if ($member instanceof Member) {
		// transaction to save permission tables
		try {
			DB::beginWork();
			$result = save_member_permissions($member, $permissions, true, false, false, false);
			if ($old_parent_id != -1 && $old_parent_id != $member->getParentMemberId()) {
				do_member_parent_changed_refresh_object_permisssions($member_id, $old_parent_id);
			}
			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			Logger::log("Error saving permissions (1): ".$e->getMessage()."\n".$e->getTraceAsString());
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
			$perm_array = json_decode($permissions);
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
			Hook::fire('after_save_member_permissions', array('member' => array_var($result, 'member'), 'user_id' => $user_id), array_var($result, 'member'));
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
				SharingTableFlags::delete("member_id=$member_id AND permission_group_id IN (".implode(',', $flags_to_delete).")");
				
				DB::commit();
			} catch (Exception $e) {
				DB::rollback();
				Logger::log("Error saving permissions (5 - failed to delete processed flags [".implode(',',$flags_to_delete)."]): ".$e->getMessage()."\n".$e->getTraceAsString());
			}
		}
	}
	
	@unlink($permissions_filename);
	
