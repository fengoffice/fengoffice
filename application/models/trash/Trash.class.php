<?php
class Trash {
	function purge_trash($days = null, $limit = null, $extra_conditions = "") {
		ini_set('memory_limit', '512M');
		Env::useHelper("permissions");
		
		if (is_null($days)) {
			$days = config_option("days_on_trash");
		}
		if (is_null($limit)) {
			$limit = 1000;
		}
		
		$count = 0;
		if ($days >= 0) {
			$object_ids = array();
			$date = DateTimeValueLib::now()->add("d", -$days);
			
			$mail_join = "";
			$mail_cond = "";
			if (Plugins::instance()->isActivePlugin('mail')) {
				$mail_join = "LEFT JOIN ".TABLE_PREFIX."mail_contents mc ON mc.object_id=o.id";
				$mail_cond = "AND NOT COALESCE(mc.is_deleted, false)";
			}
			
			$perm_join = "";
			$perm_conditions = "";
			if (logged_user() instanceof Contact) {
				$logged_user_pgids = logged_user()->getPermissionGroupIds();
				
				$perm_join = "INNER JOIN ".TABLE_PREFIX."object_members om on om.object_id=o.id 
						INNER JOIN ".TABLE_PREFIX."contact_member_permissions cmp ON cmp.member_id=om.member_id AND cmp.object_type_id=o.object_type_id";
				
				$perm_conditions = "AND cmp.can_delete=1 AND cmp.permission_group_id IN (".implode(',', $logged_user_pgids).")";
			}
			
			$sql = "SELECT o.id as id, o.name as name, ot.name as ot_name, ot.table_name as table_name
					FROM ".TABLE_PREFIX."objects o 
					INNER JOIN ".TABLE_PREFIX."object_types ot ON ot.id=o.object_type_id $mail_join $perm_join
					WHERE trashed_by_id > 0 AND trashed_on < '".$date->toMySQL()."' $mail_cond $extra_conditions $perm_conditions
					LIMIT $limit";
			
			$rows = DB::executeAll($sql);
			
			foreach ($rows as $row) {
				try {
					DB::beginWork();
					
					$id = $row['id'];
					$ot_name = $row['ot_name'];
					$name = $row['name'];
					$table_name = $row['table_name'];
					$object_ids[] = $id;
					
					// delete object information
					$tables_to_delete = self::get_tables_to_clean($ot_name);
					if ($ot_name != 'mail' && $ot_name != 'invoice') {
						$tables_to_delete[] = array('table' => TABLE_PREFIX . $table_name, 'column' => 'object_id');
					}
					
					foreach ($tables_to_delete as $table_info) {
						$table = $table_info['table'];
						$column = $table_info['column'];
						
						$sql = "DELETE FROM `$table` WHERE `$column` = '$id'";
						DB::execute($sql);
					}
					
					// save log
					$log = new ApplicationLog();
					if (logged_user() instanceof Contact) {
						$log->setTakenById(logged_user()->getId());
					}
					$log->setRelObjectId($id);
					$log->setObjectName($name);
					$log->setAction(ApplicationLogs::ACTION_DELETE);
					$log->setIsSilent(true);
					$log->save();
					
					DB::commit();
					$count++;
				} catch (DBQueryError $e) {
					DB::rollback();
					Logger::log("Error delting object in purge_trash: " . $e->getMessage()."\n".$e->getSQL()."\n", Logger::ERROR);
				}
			}
			
			$ignored = null;
			Hook::fire('after_object_delete_permanently', $object_ids, $ignored);
		}
		return $count;
	}
	
	
	
	private function get_tables_to_clean($object_type_name) {
		$result = array();
		$result[] = array('table' => TABLE_PREFIX."objects", 'column' => 'id');
		$result[] = array('table' => TABLE_PREFIX."object_members", 'column' => 'object_id');
		$result[] = array('table' => TABLE_PREFIX."comments", 'column' => 'rel_object_id');
		$result[] = array('table' => TABLE_PREFIX."sharing_table", 'column' => 'object_id');
		$result[] = array('table' => TABLE_PREFIX."object_subscriptions", 'column' => 'object_id');
		$result[] = array('table' => TABLE_PREFIX."object_reminders", 'column' => 'object_id');
		$result[] = array('table' => TABLE_PREFIX."timeslots", 'column' => 'rel_object_id');
		$result[] = array('table' => TABLE_PREFIX."read_objects", 'column' => 'rel_object_id');
		$result[] = array('table' => TABLE_PREFIX."linked_objects", 'column' => 'object_id');
		$result[] = array('table' => TABLE_PREFIX."linked_objects", 'column' => 'rel_object_id');
		$result[] = array('table' => TABLE_PREFIX."searchable_objects", 'column' => 'rel_object_id');
		$result[] = array('table' => TABLE_PREFIX."custom_property_values", 'column' => 'object_id');
		$result[] = array('table' => TABLE_PREFIX."object_properties", 'column' => 'rel_object_id');
		
		
		switch ($object_type_name) {
			case 'mail':
				$result[] = array('table' => TABLE_PREFIX."mail_datas", 'column' => 'id');
				break;
			case 'file':
				$result[] = array('table' => TABLE_PREFIX."project_file_revisions", 'column' => 'file_id');
				break;
			case 'task':
				$result[] = array('table' => TABLE_PREFIX."project_task_dependencies", 'column' => 'task_id');
				$result[] = array('table' => TABLE_PREFIX."project_task_dependencies", 'column' => 'previous_task_id');
				break;
			case 'contact':
				$result[] = array('table' => TABLE_PREFIX."contact_addresses", 'column' => 'contact_id');
				$result[] = array('table' => TABLE_PREFIX."contact_emails", 'column' => 'contact_id');
				$result[] = array('table' => TABLE_PREFIX."contact_im_values", 'column' => 'contact_id');
				$result[] = array('table' => TABLE_PREFIX."contact_passwords", 'column' => 'contact_id');
				$result[] = array('table' => TABLE_PREFIX."contact_telephones", 'column' => 'contact_id');
				$result[] = array('table' => TABLE_PREFIX."contact_web_pages", 'column' => 'contact_id');
				$result[] = array('table' => TABLE_PREFIX."contact_config_option_values", 'column' => 'contact_id');
				$result[] = array('table' => TABLE_PREFIX."contact_permission_groups", 'column' => 'contact_id');
				$result[] = array('table' => TABLE_PREFIX."contact_widgets", 'column' => 'contact_id');
				$result[] = array('table' => TABLE_PREFIX."contact_widget_options", 'column' => 'contact_id');
				break;
			default:
				break;
		}

		Hook::fire('on_delete_tables_to_clean', array('type_name' => $object_type_name), $result);
		
		return $result;
	}  
}
?>