<?php
	chdir($argv[1]);
	define("CONSOLE_MODE", true);
	define('PUBLIC_FOLDER', 'public');
	include "init.php";
	
	session_commit(); // we don't need sessions
	@set_time_limit(0); // don't limit execution of cron, if possible
	ini_set('memory_limit', '2048M');


	Env::useHelper('permissions');
	
	debug_log("Start Save user permissions", "permissions_debug.log");
	
	$user_id = array_var($argv, 2);
	$token = array_var($argv, 3);
	
	// log user in
	$user = Contacts::findById($user_id);
	if(!($user instanceof Contact) || !$user->isValidToken($token)) {
		throw new Exception("Cannot login with user $user_id and token '$token'");
	}

	CompanyWebsite::instance()->setLoggedUser($user, false, false, false);
		
	// save permissions
	$pg_id = array_var($argv, 4);
	$is_guest = array_var($argv, 5);
	$permissions_filename = array_var($argv, 6);
	$sys_permissions_filename = array_var($argv, 7);
	$mod_permissions_filename = array_var($argv, 8);
	$root_permissions_filename = array_var($argv, 9);	
	$users_ids_to_check_filename = array_var($argv, 10);
	$root_permissions_genid = array_var($argv, 11);
	$only_member_permissions = array_var($argv, 12) == "1";
	
	$permissions = file_get_contents($permissions_filename);
	$sys_permissions = json_decode(file_get_contents($sys_permissions_filename), true);
	$mod_permissions = json_decode(file_get_contents($mod_permissions_filename), true);
	$root_permissions = json_decode(file_get_contents($root_permissions_filename), true);
	$users_ids_to_check = json_decode(file_get_contents($users_ids_to_check_filename), true);
	
	$perms = array(
		'permissions' => $permissions,
		'sys_perm' => $sys_permissions,
		'mod_perm' => $mod_permissions,
		'root_perm' => $root_permissions,
		'root_perm_genid' => $root_permissions_genid,
	);
	
	debug_log("Before save_permissions", "permissions_debug.log");
	
	// save permissions
	try {
		$result = save_permissions($pg_id, $is_guest, $perms, true, false, false, false, array(), $only_member_permissions);
	} catch (Exception $e) {
		Logger::log("Error saving permissions (1): ".$e->getMessage()."\n".$e->getTraceAsString());
	}
	
	debug_log("Before sharing table update", "permissions_debug.log");
	
	// update sharing table
	try {
		// create flag for this $pg_id
		DB::beginWork();
		$flag = new SharingTableFlag();
		$flag->setPermissionGroupId($pg_id);
		$flag->setMemberId(0);
		$flag->setPermissionString($permissions);
		$flag->setExecutionDate(DateTimeValueLib::now());
		$flag->setCreatedById(logged_user()->getId());
		$flag->save();
		DB::commit();
		
		// populate permission groups
		$permissions_decoded = json_decode($permissions);
		$to_insert = array();
		$to_delete = array();
		if (is_array($permissions_decoded)) {
			foreach ($permissions_decoded as $perm) {
				if ($perm->r) {
					$to_insert[] = "('".$pg_id."','".$perm->m."','".$perm->o."','".$perm->d."','".$perm->w."')";
				} else {
					$to_delete[] = "(permission_group_id='".$pg_id."' AND member_id='".$perm->m."' AND object_type_id='".$perm->o."')";
				}
			}
		}
		if (count($to_insert) > 0) {
			$values = implode(',', $to_insert);
			DB::execute("INSERT INTO ".TABLE_PREFIX."contact_member_permissions (permission_group_id,member_id,object_type_id,can_delete,can_write)
			VALUES $values ON DUPLICATE KEY UPDATE member_id=member_id");
		}
		if (count($to_delete) > 0) {
			$where = implode(' OR ', $to_delete);
			DB::execute("DELETE FROM ".TABLE_PREFIX."contact_member_permissions WHERE $where;");
		}
		
		// root permissions
		$root_permissions_sharing_table_add = array();
		$root_permissions_sharing_table_delete = array();

		if(is_array($result) && array_key_exists('root_permissions_sharing_table_add',$result)){
            $root_permissions_sharing_table_add = $result['root_permissions_sharing_table_add'];
        }

        if(is_array($result) && array_key_exists('root_permissions_sharing_table_delete',$result)){
            $root_permissions_sharing_table_delete = $result['root_permissions_sharing_table_delete'];
        }

        $rp_info = array('root_permissions_sharing_table_delete' => $root_permissions_sharing_table_delete, 'root_permissions_sharing_table_add' => $root_permissions_sharing_table_add);
		
		// update sharing table
		DB::beginWork();
		$sharingTablecontroller = new SharingTableController();
		$sharingTablecontroller->afterPermissionChanged($pg_id, json_decode($permissions), $rp_info);
		// delete flag
		$flag->delete();
		DB::commit();
		
	} catch (Exception $e) {
		DB::rollback();
		Logger::log("Error saving permissions (2): ".$e->getMessage()."\n".$e->getTraceAsString());
	}
	
	debug_log("Before member cache update", "permissions_debug.log");
	
	// save tree
	try {
		DB::beginWork();
		$contactMemberCacheController = new ContactMemberCacheController();
		$group = PermissionGroups::findById($pg_id);
		
		$real_group = null;
		if($group->getType() == 'user_groups'){
			$real_group = $group;
		}
		$users = $group->getUsers();
		$users_ids_checked = array();
		
		//check all users related to the group
		foreach ($users as $us) {
			$users_ids_checked[] = $us->getId();
			$contactMemberCacheController->afterUserPermissionChanged($us, json_decode($permissions), $real_group);
		}
		
		//check all users in users_ids_to_check (we do this because a user can be removed from a group)
		foreach ($users_ids_to_check as $us_id) {
			if(!in_array($us_id, $users_ids_checked)){
				$users_ids_checked[] = $us_id;
				$us = Contacts::findById($us_id);
				if($us instanceof Contact){
					$contactMemberCacheController->afterUserPermissionChanged($us, json_decode($permissions), $real_group);
				}
			}
		}
		DB::commit();
	} catch (Exception $e) {
		DB::rollback();
		Logger::log("Error saving permissions (3): ".$e->getMessage()."\n".$e->getTraceAsString());
	}
	
	debug_log("Before firing hooks", "permissions_debug.log");
	
	// fire hooks
	try {
		DB::beginWork();
		Hook::fire('after_save_contact_permissions', $pg_id, $pg_id);
		DB::commit();
	} catch (Exception $e) {
		DB::rollback();
		Logger::log("Error saving permissions (4): ".$e->getMessage()."\n".$e->getTraceAsString());
	}
	
	// remove contact object from members where permissions were deleted
	if (isset($all_perm_deleted) && is_array($all_perm_deleted)) {
		$user = Contacts::findOne(array('conditions' => 'permission_group_id='.$pg_id));
		if ($user instanceof Contact) {
			$to_remove = array();
			foreach ($all_perm_deleted as $m_id => $must_remove) {
				if ($must_remove) $to_remove[] = $m_id;
			}
			ObjectMembers::removeObjectFromMembers($user, logged_user(), null, $to_remove);
		}
	}
	
	@unlink($permissions_filename);
	@unlink($sys_permissions_filename);
	@unlink($mod_permissions_filename);
	@unlink($root_permissions_filename);
