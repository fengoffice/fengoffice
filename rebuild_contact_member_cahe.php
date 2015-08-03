<?php
chdir(dirname(__FILE__));
header("Content-type: text/plain");
define("CONSOLE_MODE", true);
include "init.php";
Env::useHelper('format');
Env::useHelper('permissions');

define('SCRIPT_MEMORY_LIMIT', 1024 * 1024 * 1024); // 1 GB

@set_time_limit(0);
ini_set('memory_limit', ((SCRIPT_MEMORY_LIMIT / (1024*1024))+50).'M');

//UPDATE depth for all members
//update root members
DB::execute("UPDATE ".TABLE_PREFIX."members SET depth = 1  WHERE parent_member_id = 0;");
//clean root members
DB::execute("UPDATE ".TABLE_PREFIX."members SET depth = 2  WHERE parent_member_id != 0 AND depth = 1;");

$members_depth = DB::executeAll("SELECT id FROM ".TABLE_PREFIX."members WHERE parent_member_id =0 ORDER BY id");
$members_depth = array_flat($members_depth);
$members_depth = implode(",", $members_depth);

$depth = 2;
$max_depth = DB::executeOne("SELECT  MAX(depth) AS depth FROM `".TABLE_PREFIX."members`");
	
//update all depths
for ($i = $depth; $i <= $max_depth['depth']; $i++) {
	//update members depth
	DB::execute("UPDATE ".TABLE_PREFIX."members SET depth = ".$depth." WHERE parent_member_id  IN (".$members_depth.");");

	//Get member from next depth
	$members_depth = DB::executeAll("SELECT id FROM ".TABLE_PREFIX."members WHERE depth= ".$depth." ORDER BY id");
	$members_depth = array_flat($members_depth);
	$members_depth = implode(",", $members_depth);

	$depth++;
}
//END UPDATE depth for all members

echo "\nStart Truncate  contact_member_cache\n-----------------------------------------------------------------";
DB::execute("TRUNCATE TABLE ".TABLE_PREFIX."contact_member_cache;");
echo "\nEnd Truncate  contact_member_cache\n-----------------------------------------------------------------";
$users = Contacts::getAllUsers();

$dimensions = Dimensions::findAll();
$dimensions_ids = array();
foreach ($dimensions as $dimension) {
	if ($dimension->getDefinesPermissions()) {
		$dimensions_ids[] = $dimension->getId();
	}
}

$dimensions_ids = implode(",",$dimensions_ids);
$root_members = DB::executeAll("SELECT * FROM ".TABLE_PREFIX."members WHERE dimension_id IN (".$dimensions_ids.") AND parent_member_id=0 ORDER BY id");
foreach ($users as $user) {
	echo "\n".$user->getName();
	try {
		DB::beginWork();
		foreach ($root_members as $member) {			
			ContactMemberCaches::updateContactMemberCache($user, $member['id'], $member['parent_member_id']);
		}
		DB::commit();
	} catch (Exception $e) {
		DB::rollback();
		echo $e->__toString();
	}
}
//END Load the contact member cache

echo "\nEnd rebuild  contact_member_cache\n-----------------------------------------------------------------";
