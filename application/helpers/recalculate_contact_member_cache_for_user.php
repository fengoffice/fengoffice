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
	
	// get the users - argv[4] is a CSV, so one process now covers every contact queued by the
	// originating request instead of one process per contact.
	$contact_ids = array_filter(array_map('trim', explode(',', array_var($argv, 4, ''))));

	foreach ($contact_ids as $contact_id) {
		$contact = Contacts::instance()->findById($contact_id);

		// recalculate the member cache
		if ($contact instanceof Contact) {
			try {
				ContactMemberCaches::updateContactMemberCacheAllMembers($contact);
			} catch (Exception $e) {
				// One failing contact must not cost the remaining ones in the batch.
				Logger::log("Error recalculating contact member cache for contact $contact_id: ".$e->getMessage()."\n".$e->getTraceAsString());
			}
		}
	}
	
