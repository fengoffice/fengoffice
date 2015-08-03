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
	$user = Contacts::findById($user_id);
	if(!($user instanceof Contact) || !$user->isValidToken($token)) {
		die();
	}
	CompanyWebsite::instance()->setLoggedUser($user, false, false, false);
	
	// get parameters
	$member_id = array_var($argv, 4);
	$old_parent_id = array_var($argv, 5);
	
	// execute the permissions rebuild
	try {
		DB::beginWork();
		do_member_parent_changed_refresh_object_permisssions($member_id, $old_parent_id);
		DB::commit();
	} catch (Exception $e) {
		Logger::log("ERROR updating permissions after changing member parent for member ($member_id)");
		DB::rollback();
	}