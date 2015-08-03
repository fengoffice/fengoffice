<?php
chdir(dirname(__FILE__));
define("CONSOLE_MODE", true);
define('PUBLIC_FOLDER', 'public');
include "init.php";
header("Content-type: text/plain");


$admin_pg = PermissionGroups::findOne(array('conditions' => "`name`='Super Administrator'"));

$all_roles_max_permissions = RoleObjectTypePermissions::getAllRoleObjectTypePermissionsInfo();

$admin_perms = $all_roles_max_permissions[$admin_pg->getId()];
$all_object_types = array();
foreach ($admin_perms as &$aperm) {
	$all_object_types[] = $aperm['object_type_id'];
}

$users = Contacts::getAllUsers();
echo date('H:i:s')." - Processing ".count($users)." users...\n";
foreach ($users as $user) {
	/* @var $user Contact */
	$max_permissions = array_var($all_roles_max_permissions, $user->getUserType());
	$pg_id = $user->getPermissionGroupId();
	
	foreach ($all_object_types as $ot) {
		if (!$ot) continue;
		$max = array_var($max_permissions, $ot);
		
		if (!$max) {
			// cannot read -> delete in contact_member_permissions
			$sql = "DELETE FROM ".TABLE_PREFIX."contact_member_permissions WHERE permission_group_id=$pg_id AND object_type_id=$ot";
			DB::execute($sql);
			
		} else {
			// cut can_delete and can_write using max permissions
			$can_d = $max['can_delete'] ? "1" : "0";
			$can_w = $max['can_write'] ? "1" : "0";
			
			$sql = "UPDATE ".TABLE_PREFIX."contact_member_permissions 
					SET can_delete=(can_delete AND $can_d), can_write=(can_write AND $can_w)
					WHERE permission_group_id=$pg_id AND object_type_id=$ot";
			DB::execute($sql);
			
		}
	}
	
	echo $user->getObjectName()."\n";
}
echo date('H:i:s')." -------------------------------\n";