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
		
	// argv[4] is a CSV, so one process now covers every permission group queued by the originating
	// request instead of one process per group.
	$pg_ids = array_filter(array_map('trim', explode(',', array_var($argv, 4, ''))));

	foreach ($pg_ids as $pg_id) {
		try {
			DB::beginWork();

			rebuild_sharing_table_for_pg($pg_id);

			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			Logger::log("Error rebuilding sharing table for pg: $pg_id: ".$e->getMessage()."\n".$e->getTraceAsString());
		}
	}
	
	

	
