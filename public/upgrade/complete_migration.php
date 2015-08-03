<?php
chdir(dirname(__FILE__) . "/../../");
define("CONSOLE_MODE", true);
define('PUBLIC_FOLDER', 'public');
include "init.php";

Env::useHelper('format');

define('SCRIPT_MEMORY_LIMIT', 1024 * 1024 * 1024); // 1 GB
// amount of objects to be processed
if (isset($argv[1]) && is_numeric($argv[1])) {
	define('OBJECT_COUNT', $argv[1]);
} else {
	define('OBJECT_COUNT', 1000000);
}
$separator = "-----------------------------------------------------";

if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
	define('COMPLETE_MIGRATION_OUT', 'console');
} else {
	define('COMPLETE_MIGRATION_OUT', 'file');
}

@set_time_limit(0);

ini_set('memory_limit', ((SCRIPT_MEMORY_LIMIT / (1024*1024))+50).'M');

function complete_migration_print($text) {
	if (COMPLETE_MIGRATION_OUT == 'console') {
		echo $text;
	} else if (COMPLETE_MIGRATION_OUT == 'file') {
		file_put_contents(ROOT . "/complete_migration_out.txt", $text, FILE_APPEND);
	}
}

function complete_migration_check_table_exists($table_name, $connection) {
	$res = mysql_query("SHOW TABLES", $connection);
	while ($row = mysql_fetch_array($res)) {
		if ($row[0] == $table_name) return true;
	}
	return false;
}


if (!complete_migration_check_table_exists(TABLE_PREFIX . "processed_objects", DB::connection()->getLink())) {
	DB::execute("CREATE TABLE `" . TABLE_PREFIX . "processed_objects` (
				  `object_id` INTEGER UNSIGNED,
				  PRIMARY KEY (`object_id`)
				) ENGINE = InnoDB;");
}

try {

$sql = "";
$first_row = true;

$cant = 0;
$count = 0;
$processed_objects = array();

$user = Contacts::findOne(array("conditions"=>"user_type = (SELECT id FROM ".TABLE_PREFIX."permission_groups WHERE name='Super Administrator')"));
$object_controller = new ObjectController();
$objects = Objects::findAll(array('id'=>true, "conditions" => "id NOT IN(SELECT object_id FROM ".TABLE_PREFIX."processed_objects)", "order" => "id DESC", "limit" => OBJECT_COUNT));

foreach ($objects as $obj) {
	$cobj = Objects::findObject($obj);
	if ($cobj instanceof ContentDataObject) {
		
		if (!$cobj instanceof Workspace) {
			$mem_ids = $cobj->getMemberIds();
			if (count($mem_ids) > 0) {
				$object_controller->add_to_members($cobj, $mem_ids, $user);
			} else {
				$cobj->addToSharingTable();
			}
			$cobj->addToSearchableObjects(true);
		}
		
		// add mails to sharing table for account owners
		if ($cobj instanceof MailContent && $cobj->getAccount() instanceof MailAccount) {
			$db_result = DB::execute("SELECT contact_id FROM ".TABLE_PREFIX."mail_account_contacts WHERE account_id = ".$cobj->getAccountId());
			$macs = $db_result->fetchAll();
			if ($macs && is_array($macs) && count($macs) > 0) {
				$pgs = array();
				foreach ($macs as $mac) {
					$contact_id = $mac['contact_id'];
					$mac_pgs = DB::executeAll("SELECT permission_group_id FROM ".TABLE_PREFIX."contacts WHERE object_id=$contact_id");
					foreach ($mac_pgs as $mac_pg) $pgs[$mac_pg['permission_group_id']] = $mac_pg['permission_group_id'];
				}
				if ($sql == "" && count($pgs) > 0) $sql = "INSERT INTO ".TABLE_PREFIX."sharing_table (group_id, object_id) VALUES ";
				foreach ($pgs as $pgid) {
					$sql .= ($first_row ? "" : ", ") . "('$pgid', '{$cobj->getId()}')";
					$first_row = false;
				}
				unset($macs);
				unset($pgs);
				
				$count = ($count + 1) % 500;
				if ($sql != "" && $count == 0) {
					$sql .= " ON DUPLICATE KEY UPDATE group_id=group_id;";
					DB::execute($sql);
					$sql = "";
					$first_row = true;
				}
			}
		}
		$processed_objects[] = $cobj->getId();
		
		// check memory to stop script
		if (count($processed_objects) >= OBJECT_COUNT || memory_get_usage(true) > SCRIPT_MEMORY_LIMIT) {
			$processed_objects_ids = "(" . implode("),(", $processed_objects) . ")";
			DB::execute("INSERT INTO ".TABLE_PREFIX."processed_objects (object_id) VALUES $processed_objects_ids ON DUPLICATE KEY UPDATE object_id=object_id");
			
			$rest = Objects::count("id NOT IN(SELECT object_id FROM ".TABLE_PREFIX."processed_objects)");
			$row = DB::executeOne("SELECT COUNT(object_id) AS 'row_count' FROM ".TABLE_PREFIX."processed_objects");
			$proc_count = $row['row_count'];
			
			$status_message = "Memory limit exceeded (".format_filesize(memory_get_usage(true))."). Script terminated. Processed Objects: $proc_count. Total: ".($proc_count+$rest).". Please execute 'Fill searchable objects and sharing table' again.";
			$_SESSION['hide_back_button'] = 1;
			
			complete_migration_print("\n".date("H:i:s")." - Iteration finished or Memory limit exceeded (".format_filesize(memory_get_usage(true)).") script terminated.\nProcessed Objects: ".count($processed_objects). ".\nTotal processed objects: $proc_count.\n$rest objects left.\n$separator\n");
			$processed_objects = array();
			break;
		}
		$cant++;
		
	} else {
		$processed_objects[] = $obj;
	}
	$cobj = null;
}

// add mails to sharing table for account owners
if ($sql != "") {
	$sql .= " ON DUPLICATE KEY UPDATE group_id=group_id;";
	DB::execute($sql);
	$sql = "";
}
if (count($processed_objects) > 0) {
	$processed_objects_ids = "(" . implode("),(", $processed_objects) . ")";
	DB::execute("INSERT INTO ".TABLE_PREFIX."processed_objects (object_id) VALUES $processed_objects_ids ON DUPLICATE KEY UPDATE object_id=object_id");
}

if (COMPLETE_MIGRATION_OUT != 'console') {
	
	$all = Objects::count();
	$row = DB::executeOne("SELECT COUNT(object_id) AS 'row_count' FROM ".TABLE_PREFIX."processed_objects");
	$proc_count = $row['row_count'];
	
	if ($all <= $proc_count) {
		
		unset($_SESSION['hide_back_button']);
		$status_message = "Execution of 'Fill searchable objects and sharing table' completed.";
		foreach ($_SESSION['additional_steps'] as $k => $step) {
			if ($step['url'] == 'complete_migration.php') unset($_SESSION['additional_steps'][$k]);
		}
		
	} else {
	
		if (!isset($_SESSION['additional_steps'])) $_SESSION['additional_steps'] = array();
		$add_step = true;
		foreach ($_SESSION['additional_steps'] as $step) {
			if ($step['url'] == 'complete_migration.php') $add_step = false;
		}
		if ($add_step) {
			$_SESSION['additional_steps'][] = array(
				'url' => 'complete_migration.php',
				'name' => 'Fill searchable objects and sharing table',
				'filename' => ROOT."/".PUBLIC_FOLDER."/upgrade/complete_migration.php"
			);
		}
		
	}
	
	if (!isset($_SESSION['status_messages'])) $_SESSION['status_messages'] = array();
	if (isset($status_message)) $_SESSION['status_messages']['complete_migration'] = $status_message;

	redirect_to(ROOT_URL . "/" . PUBLIC_FOLDER ."/upgrade/", false);
}

} catch (Exception $e) {
	complete_migration_print("ERROR: ".$e->getMessage()."\n");
	complete_migration_print("Trace:\n".$e->getTraceAsString()."\n");
}