<?php 
class  SharingTableController extends ApplicationController {
	
	/**
	 * When updating perrmissions, sharing table should be updated
	 * @param stdClass $permission:  
	 * 			[m] => 36 : Member Id 
	 * 			[o] => 3 : Object Type Id 
	 * 			[d] => 0 //delete
	 * 			[w] => 1 //write
	 * 			[r] => 1 //read 
	 * @throws Exception
	 */
	function afterPermissionChanged($groups, $permissions, $root_perm_info = null) {
		if (!is_array($groups)) {
			if (is_numeric($groups)) $groups = array($groups);
			else return;
		}
		foreach ($groups as $group) {
			$this->after_permission_changed($group, $permissions, $root_perm_info);
			$this->adjust_root_permissions($group, $root_perm_info);
		}
	}
	
	
	function after_permission_changed($group = null, $permissions = null, $root_perm_info = null) {
		@set_time_limit(0);
		$die = false;
		if ($group == null || $permissions == null) {
			$die = true;
			if ($group == null) {
				$group = array_var($_REQUEST, 'group');
			}
			if ($permissions == null) {
				$permissions = json_decode(array_var($_REQUEST, 'permissions'));
			}
		}
		
		// CHECK PARAMETERS
		if(!count($permissions)){
			return false;
		}
		if (!is_numeric($group) || !$group) {
			throw new Error("Error filling sharing table. Invalid Paramenters for afterPermissionChanged method");
		}

		// INIT LOCAL VARS
		$stManager = SharingTables::instance();
		$affectedObjects = array();
		$members = array();
		$general_condition = '';
		$read_condition = '';
		$read_conditions = array();
		$delete_condition = '';
		$delete_conditions = array();

		$all_read_conditions = array();
		$read_count = 0;
		$all_del_conditions = array();
		$del_count = 0;
		
		// BUILD OBJECT_IDs SUB-QUERIES
		$from = "FROM ".TABLE_PREFIX."object_members om INNER JOIN ".TABLE_PREFIX."objects o ON o.id = om.object_id";
		foreach ($permissions as $permission) {
			$memberId = $permission->m;
			$objectTypeId = $permission->o;
			if (!$memberId || !$objectTypeId) continue;
			$delete_conditions[] = " ( object_type_id = '$objectTypeId' AND om.member_id = '$memberId' AND om.is_optimization = 0 ) ";
			$del_count++;
			if ($del_count >= 20) {
				$all_del_conditions[] = $delete_conditions;
				$delete_conditions = array();
				$del_count = 0;
			}
			if ($permission->r) {
				if(!isset($read_conditions[$objectTypeId."_".$memberId])){
					$read_conditions[$objectTypeId."_".$memberId] = " ( object_type_id = '$objectTypeId' AND om.member_id = '$memberId' ) ";
					
					$read_count++;
					if ($read_count >= 500) {
						$all_read_conditions[] = $read_conditions;
						$read_count = 0;
						$read_conditions = array();
					}
				}				
			}
		}
		$all_read_conditions[] = $read_conditions;
		$all_del_conditions[] = $delete_conditions;
		
		// DELETE THE AFFECTED OBJECTS FROM SHARING TABLE
		foreach ($all_del_conditions as $delete_conditions) {
			
			if (!is_array($delete_conditions) || count($delete_conditions) == 0) continue;
			/*
			// check if the permission group still can view any of the affected objects (if they are classified in another dimension member)
			$del_objs = DB::executeAll("SELECT object_id, o.object_type_id $from WHERE ".implode(' OR ' , $delete_conditions ));
			
			$del_objs_can_read = array();
			foreach ($del_objs as $do_row) {
				$do = $do_row['object_id'];
				$ot_id = $do_row['object_type_id'];
				
				$mems = ObjectMembers::instance()->getMembersByObject($object_id);
				if (can_access_pgids(array($group), $mems, $ot_id, ACCESS_LEVEL_READ)) {
					$del_objs_can_read[] = $do;
				}
			}
			
			// objects that were included to be deleted but still can be read
			$not_to_del_objs_cond = "";
			if (count($del_objs_can_read) > 0) {
				$not_to_del_objs_cond = " AND object_id NOT IN (".implode(',',$del_objs_can_read).")";
			}*/
			
			// delete registers only for objects that cannot be read anymore for this permission group
			$oids = DB::executeAll("SELECT object_id $from WHERE ".implode(' OR ' , $delete_conditions )."");
			if (is_array($oids) && count($oids) > 0) {
				$oids = array_flat($oids);
				$stManager->delete("object_id IN (".implode(',',$oids).") AND group_id = '$group'");
			}
		}
		
		// 2.0 POPULATE THE SHARING TABLE AGAIN WITH THE READ-PERMISSIONS (If there are)
		// 2.1 Check mandatory dimensions, if an objects belongs to a member in a mandatory dimension then the permission group must have permissions in the member, 
		//     if user doesn't have permissions ther, then the user cannot read the object, no matter what other permissions are active 
		$enabled_dimensions_sql = "";
		$enabled_dimensions_ids = implode(',', config_option('enabled_dimensions'));
		if ($enabled_dimensions_ids != "") {
			$enabled_dimensions_sql = "AND id IN ($enabled_dimensions_ids)";
		}
		
		$mandatory_dim_ids = Dimensions::findAll(array('id' => true, 'conditions' => "`defines_permissions`=1 $enabled_dimensions_sql AND `permission_query_method`='".DIMENSION_PERMISSION_QUERY_METHOD_MANDATORY."'"));
		$mdim_conds = "";
		if (count($mandatory_dim_ids) > 0) {
			foreach ($mandatory_dim_ids as $md_id) {
				$mdim_conds .= "
				AND IF (
					(SELECT count(om1.object_id) FROM ".TABLE_PREFIX."object_members om1 INNER JOIN ".TABLE_PREFIX."members m1 ON m1.id=om1.member_id 
					WHERE om1.object_id=o.id AND om1.is_optimization=0 AND m1.dimension_id=$md_id)=0, 
						true, 
						EXISTS (SELECT cmp.permission_group_id FROM ".TABLE_PREFIX."contact_member_permissions cmp WHERE cmp.permission_group_id=$group AND cmp.object_type_id=o.object_type_id
							AND cmp.member_id IN (
								SELECT om2.member_id FROM ".TABLE_PREFIX."object_members om2 WHERE om2.object_id=o.id AND om2.is_optimization=0 AND om2.member_id IN (
									SELECT m2.id FROM ".TABLE_PREFIX."members m2 WHERE m2.dimension_id=$md_id
								)
							)
						)
				)";
			}
		} 
		
		// 2.2 Select objects that have read permissions for this permission group
		foreach ($all_read_conditions as $read_conditions) {
			if (isset($read_conditions) && count($read_conditions)) {
				$st_new_rows = "
					SELECT $group AS group_id, object_id $from
					WHERE om.is_optimization=0 AND (". implode(' OR ', $read_conditions) . ") $mdim_conds";
	
				$st_insert_sql =  "INSERT INTO ".TABLE_PREFIX."sharing_table(group_id, object_id) $st_new_rows ON DUPLICATE KEY UPDATE ".TABLE_PREFIX."sharing_table.group_id=".TABLE_PREFIX."sharing_table.group_id;";
				DB::execute($st_insert_sql);
			}
		}
		if ($die) die();
	}
	
	
	function adjust_root_permissions($group, $root_perm_info = null) {
		// ROOT PERMISSIONS
		if (!is_null($root_perm_info)) {
			// user does not have permissions for object_type_ids
			$root_permissions_sharing_table_delete = array_var($root_perm_info, 'root_permissions_sharing_table_delete');
			if (is_array($root_permissions_sharing_table_delete)) {
				foreach ($root_permissions_sharing_table_delete as $object_type_id) {
					$cond = "group_id=$group AND object_id IN (SELECT o.id FROM ".TABLE_PREFIX."objects o WHERE o.object_type_id = $object_type_id AND NOT EXISTS(
						SELECT om.object_id FROM ".TABLE_PREFIX."object_members om WHERE om.object_id=o.id AND om.member_id IN (SELECT m.id FROM ".TABLE_PREFIX."members m WHERE m.dimension_id IN (
							SELECT d.id FROM ".TABLE_PREFIX."dimensions d WHERE d.is_manageable=1
						))
					))";
					SharingTables::instance()->delete($cond);
				}
			}
			
			// user has permissions for object_type_ids
			$root_permissions_sharing_table_add = array_var($root_perm_info, 'root_permissions_sharing_table_add');
			if (is_array($root_permissions_sharing_table_add)) {
				$file_ot = ObjectTypes::findByName('file');
				foreach ($root_permissions_sharing_table_add as $object_type_id) {
					$additional_where = "";
					$additional_join = "";
					if ($file_ot->getId() == $object_type_id && Plugins::instance()->isActivePlugin('mail')) {
						$additional_join .= "INNER JOIN ".TABLE_PREFIX."project_files e ON e.object_id=o.id";
						
						$additional_where .= "AND IF(e.mail_id=0, true, EXISTS (SELECT mac.contact_id FROM ".TABLE_PREFIX."mail_account_contacts mac 
							WHERE mac.contact_id IN (SELECT cpg.contact_id FROM ".TABLE_PREFIX."contact_permission_groups cpg WHERE permission_group_id=$group) 
								AND mac.account_id=(SELECT mc.account_id FROM ".TABLE_PREFIX."mail_contents mc WHERE mc.object_id=e.mail_id)))";
					}
					
					$sql = "SELECT o.id FROM ".TABLE_PREFIX."objects o $additional_join WHERE o.object_type_id = $object_type_id AND NOT EXISTS(
						SELECT om.object_id FROM ".TABLE_PREFIX."object_members om WHERE om.object_id=o.id AND om.member_id IN (SELECT m.id FROM ".TABLE_PREFIX."members m WHERE m.dimension_id IN (
							SELECT d.id FROM ".TABLE_PREFIX."dimensions d WHERE d.is_manageable=1
						))
					) $additional_where";
					$rows = DB::executeAll($sql);
					$ids = array_flat($rows);
					
					$values = "";
					foreach ($ids as $id) {
						$values .= ($values == "" ? "" : ",") . "('$id','$group')";
					}
					DB::execute("INSERT INTO ".TABLE_PREFIX."sharing_table (object_id, group_id) VALUES $values ON DUPLICATE KEY UPDATE group_id=group_id;");
				}
			}
		}
	}
}