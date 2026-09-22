<?php
	chdir($argv[1]);
	define("CONSOLE_MODE", true);
	define('PUBLIC_FOLDER', 'public');
	include "init.php";
	
	session_commit(); // we don't need sessions
	@set_time_limit(0); // don't limit execution of cron, if possible
	ini_set('memory_limit', '1024M');
	
	Env::useHelper('permissions');
	
	$user_id = array_var($argv, 2);
	$token = array_var($argv, 3);
	
	// log user in
	$user = Contacts::instance()->findById($user_id);
	if(!($user instanceof Contact) || !$user->isValidToken($token)) {
		die();
	}
	CompanyWebsite::instance()->setLoggedUser($user, false, false, false);
	
	Env::useHelper('background_jobs');

	// get parameters - argv[4] is a spool file listing every member queued by the originating
	// request, so one process now covers what used to be one process per member. Claim it first:
	// the rename is atomic, so the cron sweeper and this request's shutdown handler can never both
	// process it.
	$spool_argument = array_var($argv, 4);
	$spool_file = claim_background_job_spool($spool_argument);
	if ($spool_file === null) {
		die(); // gone, or another worker owns it
	}
	$spool_base = background_job_spool_base($spool_file);
	$jobs = read_background_job_spool($spool_file);

	// execute the permissions rebuild
	foreach ($jobs as $job) {
		$member_id = array_var($job, 'member_id');
		$old_parent_id = array_var($job, 'old_parent_id');
		$new_parent_id = array_var($job, 'new_parent_id');

		try {
			DB::beginWork();
			do_member_parent_changed_refresh_object_permisssions($member_id, $old_parent_id, $new_parent_id);
			DB::commit();
		} catch (Exception $e) {
			// One failing member must not cost the remaining ones in the batch.
			Logger::log("ERROR updating permissions after changing member parent for member ($member_id)");
			DB::rollback();
		}
	}

	@unlink($spool_file);
	// The originating request may have recreated the base spool with lines appended after this
	// batch was claimed; its sidecar must survive so those can still be recovered.
	if (!is_file($spool_base)) {
		@unlink($spool_base . '.meta');
	}
