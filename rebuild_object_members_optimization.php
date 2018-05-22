<?php
chdir(dirname(__FILE__));
header("Content-type: text/plain");
define("CONSOLE_MODE", true);
include "init.php";
Env::useHelper('format');

define('SCRIPT_MEMORY_LIMIT', 1024 * 1024 * 1024); // 1 GB

@set_time_limit(0);
ini_set('memory_limit', ((SCRIPT_MEMORY_LIMIT / (1024*1024))+50).'M');

$i = 0;
$objects_ids = Objects::instance()->findAll(array('columns' => array('id'),'id' =>true));//,'conditions' => 'object_type_id = 6'

echo "\nObjects to process: " . count($objects_ids) . "\n-----------------------------------------------------------------";

foreach ($objects_ids as $object_id) {	
	$object = Objects::findObject($object_id);
	$i++;
	if ($object instanceof ContentDataObject) {
		$members = $object->getMembers();
		
		DB::execute("DELETE FROM ".TABLE_PREFIX."object_members WHERE object_id = ".$object->getId()." AND is_optimization = 1;");

		ObjectMembers::addObjectToMembers($object->getId(),$members);		
	} else {
		// 					
	}

	if ($i % 100 == 0) {
		echo "\n$i objects processed. Mem usage: " . format_filesize(memory_get_usage(true));
	}
}
