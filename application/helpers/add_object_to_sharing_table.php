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
	
	$ids = array();
	
	// add object to sharing table
	$object_id = array_var($argv, 4);
	if (is_numeric($object_id)) {
		$ids[] = $object_id;
	} else {
		$ids = explode(',', $object_id);
	}
	
	// add all object_ids to sharing table flags
	$sql_values = "";
	foreach ($ids as $id) {
		$sql_values .= ($sql_values == "" ? "" : ",") . "($id, $user_id, NOW())";
	}
	if ($sql_values != "") {
		DB::execute("INSERT INTO ".TABLE_PREFIX."sharing_table_flags (object_id, created_by_id, execution_date) VALUES $sql_values ON DUPLICATE KEY UPDATE object_id=object_id");
	}
	
	foreach ($ids as $id) {
		$object = Objects::findObject($id);
		if ($object instanceof ContentDataObject) {
			try {
				DB::beginWork();
				$object->addToSharingTable();
				DB::execute("DELETE FROM ".TABLE_PREFIX."sharing_table_flags WHERE object_id=".$object->getId());
				DB::commit();
			} catch (Exception $e) {
				DB::rollback();
				Logger::log("Error saving permissions: object $id - ".$e->getMessage()."\n".$e->getTraceAsString());
			}
		}
	}
