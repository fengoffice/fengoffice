<?php

class SharingTables extends BaseSharingTables {

    /**
     * Fill sharing table for an specific object
     *
     * @param integer   $object_id  Object id
     */
    public function fill_sharing_table_by_object ($object_id) {
        $object = Objects::findObject($object_id);
        if ($object instanceof ContentDataObject) {
            $this->delete_object_from_sharing_table($object_id);
            $is_in_root = ObjectMembers::is_object_in_root($object_id);

            // fill the sharing table
            try{
                if ($is_in_root) {
                        $this->fill_sharing_table_for_unclassified_object($object_id, $object->getObjectTypeId());
                } else {
                    $this->fill_sharing_table_for_classified_object($object_id);
                }
            } catch(Exception $e) {
                Logger::log("Error fill_sharing_table_by_object: object $object_id - ".$e->getMessage()."\n".$e->getTraceAsString());
            } // try
        }
    }

    /**
     * Fill sharing table for an specific permission group
     *
     * @param integer   $pg_id  Permission group id
     * @param array   $modified_members by object type
     * @param array   $root_perm_info
     *
     * Example of $modified_members array
            $modified_members = array(
                'ot_id1' => array('member_id1','member_id2'),
                'ot_id2' => array('member_id3','member_id4')
            );
     */
    public function fill_sharing_table_by_permission_group($pg_id, $modified_members, $root_perm_info = null) {
        try{
            //##START Classified objects logic
            $this->fill_sharing_table_by_permission_group_for_specific_members($pg_id, $modified_members);
            //##END Classified objects logic


            //##START unclassified objects logic
            if(!is_null($root_perm_info)) {
                foreach ($root_perm_info['root_permissions_sharing_table_add'] as $object_type_id){
                    $root_group_ids = PermissionGroups::get_groups_with_root_permissions_by_object_type($object_type_id);
                    $this->fill_sharing_table_for_unclassified_objects_by_permission_group_and_object_type($pg_id, $root_group_ids, $object_type_id);
                }

                foreach ($root_perm_info['root_permissions_sharing_table_delete'] as $object_type_id){
                    $root_group_ids = PermissionGroups::get_groups_with_root_permissions_by_object_type($object_type_id);
                    $this->fill_sharing_table_for_unclassified_objects_by_permission_group_and_object_type($pg_id, $root_group_ids, $object_type_id);
                }
            }
            //##END unclassified objects logic
        } catch(Exception $e) {
            Logger::log("Error fill_sharing_table_by_permission_group: permission group $pg_id - ".$e->getMessage()."\n".$e->getTraceAsString());
        } // try
    }

    /**
     * Calculate sharing table by permission group for specific members
     *  This function will add and remove from sharing table in order to ensure that the sharing table is correct for this permission group and objects classified on this members
     *
     * @param integer   $pg_id  Permission group id
     * @param array   $modified_members by object type
     *
     * Example of $modified_members array
        $modified_members = array(
            'ot_id1' => array('member_id1','member_id2'),
            'ot_id2' => array('member_id3','member_id4')
        );

     */
    private function fill_sharing_table_by_permission_group_for_specific_members($pg_id, $modified_members){
        if(is_array($modified_members)){
            //foreach object type
            foreach ($modified_members as $ot_id => $members_by_ot){
                if(is_array($members_by_ot)){
                    $members_by_ot = array_unique($members_by_ot);

                    //Get all objects ids from sharing table for this permission group and this members
                    $object_ids_from_sharing_table = $this->get_objects_for_permission_group_and_members($pg_id, $members_by_ot, $ot_id);

                    //Get all objects ids classified in this members and where this permission group have permission to see them
                    $object_ids_with_permission = PermissionGroups::get_classified_objects_ids_by_permission_group($pg_id, array($ot_id), $members_by_ot);

                    //REMOVE FROM SHARING TABLE
                    //Delete all objects ids where this permission group does not have permissions anymore
                    $object_ids_to_remove = array_diff($object_ids_from_sharing_table, $object_ids_with_permission);
                    if(is_array($object_ids_to_remove) && count($object_ids_to_remove) > 0){
                        $this->delete_objects_from_sharing_table_by_permission_group($pg_id, $object_ids_to_remove);
                    }

                    //ADD TO SHARING TABLE
                    //Add all objects ids where this permission group have permissions and they aren't on the sharing table
                    $object_ids_to_add = array_diff($object_ids_with_permission, $object_ids_from_sharing_table);
                    if(is_array($object_ids_to_add) && count($object_ids_to_add) > 0) {
                        $this->insert_objects_for_a_group($object_ids_to_add, $pg_id);
                    }
                }
            }
        }

    }

    /**
     * Fill sharing table for an specific classified object
     *
     * @param integer   $object_id  Object id
     */
    private function fill_sharing_table_for_classified_object($object_id) {

        $group_ids = PermissionGroups::get_groups_with_permissions_on_a_classified_object($object_id);

        Hook::fire("fill_sharing_table_for_classified_object_modify_group_ids", array("object_id" => $object_id), $group_ids);


        if (count($group_ids) > 0) {
            $this->insert_groups_for_an_object($group_ids, $object_id);
        }
    }

    /**
     * Fill sharing table for an specific unclassified object for all permission groups with root permission for this object type
     *
     * @param integer   $object_id  Object id
     * @param integer   $object_type_id  Object type id
     */
    private function fill_sharing_table_for_unclassified_object ($object_id, $object_type_id) {
        if (config_option('let_users_create_objects_in_root')) {
            $group_ids = PermissionGroups::get_groups_with_root_permissions_by_object_type($object_type_id);

            Hook::fire("fill_sharing_table_for_unclassified_object_modify_group_ids", array("object_id" => $object_id), $group_ids);

            if (count($group_ids) > 0) {
                $this->insert_groups_for_an_object($group_ids, $object_id);
            }
        }
    }

    /**
     * Fill sharing table for unclassified objects by object type for specific permission group
     *
     * @param integer   $pg_id  Permission group id
     * @param array   $root_group_ids  Groups with root permission for this $object_type_id
     * @param integer   $object_type_id  Object type id
     */
    private function fill_sharing_table_for_unclassified_objects_by_permission_group_and_object_type ($pg_id, $root_group_ids,$object_type_id) {
        if (config_option('let_users_create_objects_in_root')) {

            $mail_ot = ObjectTypes::findByName('mail');
            if ($mail_ot instanceof ObjectType && $mail_ot->getId() == $object_type_id) {
                return;
            }

            if(in_array($pg_id,$root_group_ids)){
                $insert_root_object_ids_sql = $this->get_root_object_ids_sql($object_type_id, $pg_id.' AS group_id, o.id', $pg_id);
                $insert_sql = "INSERT INTO ".TABLE_PREFIX."sharing_table (group_id, object_id)  
                               $insert_root_object_ids_sql 
                               ON DUPLICATE KEY UPDATE ".TABLE_PREFIX."sharing_table.object_id=".TABLE_PREFIX."sharing_table.object_id";
                DB::execute($insert_sql);
            }else{
                $root_object_ids_sql = $this->get_root_object_ids_sql($object_type_id, 'o.id', $pg_id);
                //remove from sharing table
                $delete_sql = "DELETE FROM ".TABLE_PREFIX."sharing_table 
                                WHERE group_id = $pg_id AND object_id IN ($root_object_ids_sql)
                               ;";
                DB::execute($delete_sql);
            }
        }
    }

    /**
     * Get sql string to select all objects in root for an object type
     *
     * @param integer   $object_type_id  Object type id
     * @param string   $select_fields  filds to be selected
     * @param integer   $pg_id  Permission Group id
     * @return string  the sql query to select all objects in root for $object_type_id
     */
    private function get_root_object_ids_sql($object_type_id, $select_fields, $pg_id) {
        $enabled_dimensions_sql = "";
        $enabled_dimensions_ids = implode(',', array_filter(config_option('enabled_dimensions')));
        if ($enabled_dimensions_ids != "") {
            $enabled_dimensions_sql = "AND m.dimension_id IN ($enabled_dimensions_ids)";
        }

        $file_ot = ObjectTypes::findByName('file');
        $additional_where = "";
        $additional_join = "";
        if ($file_ot->getId() == $object_type_id && Plugins::instance()->isActivePlugin('mail')) {
            $additional_join .= "INNER JOIN ".TABLE_PREFIX."project_files e ON e.object_id=o.id";

            $additional_where .= "AND IF(e.mail_id=0, true, EXISTS (SELECT mac.contact_id FROM ".TABLE_PREFIX."mail_account_contacts mac 
							WHERE mac.contact_id IN (SELECT cpg.contact_id FROM ".TABLE_PREFIX."contact_permission_groups cpg WHERE permission_group_id=$pg_id) 
								AND mac.account_id=(SELECT mc.account_id FROM ".TABLE_PREFIX."mail_contents mc WHERE mc.object_id=e.mail_id)))";
        }

        $root_object_ids_sql = "SELECT $select_fields 
                                        FROM ".TABLE_PREFIX."objects o $additional_join
                                        WHERE o.object_type_id = $object_type_id 
                                        AND NOT EXISTS(
						                    SELECT om.object_id 
						                    FROM ".TABLE_PREFIX."object_members om 
						                    INNER JOIN ".TABLE_PREFIX."members m ON m.id=om.member_id
                                            INNER JOIN ".TABLE_PREFIX."dimensions d ON d.id=m.dimension_id
						                    WHERE om.object_id=o.id 
						                    AND om.is_optimization=0
                                            AND d.defines_permissions=1 
                                            AND d.is_manageable=1 
                                            $enabled_dimensions_sql
                                        ) 
                                        $additional_where";

        return $root_object_ids_sql;
    }

    /**
     * Insert into sharing table groups ids for an object id
     *
     * @param array $group_ids permission groups ids
     * @param int $objectId
     */
    private function insert_groups_for_an_object ($group_ids, $objectId) {
        // Insert new rows
        $table = self::getTableName();
        $cols = array("group_id", "object_id") ;
        $rows = array() ;
        foreach ($group_ids as $gid) {
            $rows[] = array( $gid, $objectId);
        }
        massiveInsert($table, $cols, $rows, 100, " ON DUPLICATE KEY UPDATE ".$table.".group_id=".$table.".group_id;");
        $rows = null;
    }

    /**
     *Insert into sharing table object ids for a permission group id
     *
     * @param array $object_ids
     * @param int $group_id
     */
    private function insert_objects_for_a_group($object_ids, $group_id ) {
        // Insert new rows
        $table = SharingTables::getTableName();
        $cols = array("group_id", "object_id") ;
        $rows = array() ;
        foreach ($object_ids as $oid) {
            $rows[] = array($group_id, $oid );
        }
        massiveInsert($table, $cols, $rows, 10000, " ON DUPLICATE KEY UPDATE ".$table.".group_id=".$table.".group_id;");
        $rows = null;
    }

    /**
     * Delete from sharing table all records for an specific object
     *
     * @param integer   $object_id  Object id
     */
    private function delete_object_from_sharing_table($object_id){
        $cond = "object_id = $object_id";
        SharingTables::instance()->delete($cond);
    }

    /**
     * Delete from sharing table all records by object ids and permission group id
     *
     * @param integer   $pg_id  Permission group id
     * @param array   $object_ids  Object ids
     */
    private function delete_objects_from_sharing_table_by_permission_group($pg_id, $object_ids){
        $cond = "group_id = $pg_id";
        $cond .= " AND object_id IN (".implode(',',$object_ids).")";

        SharingTables::instance()->delete($cond);
    }

    /**
     * Get all objects ids from sharing table by permission group id and members ids
     *
     * @param integer   $pg_id  Permission group id
     * @param array   $members_ids  Memebers ids
     * @param integer   $ot_id  Object type id
     * @return array with objects ids
     */
    private function get_objects_for_permission_group_and_members($pg_id, $members_ids, $ot_id){
        //Exclude archived members
        $sharing_table_objects_sql = "
                        SELECT sh.object_id
                        FROM ".TABLE_PREFIX."sharing_table sh
                        INNER JOIN ".TABLE_PREFIX."object_members om ON sh.object_id = om.object_id
                        INNER JOIN ".TABLE_PREFIX."objects o ON o.id = om.object_id
                        INNER JOIN ".TABLE_PREFIX."members m ON m.id=om.member_id
                        WHERE sh.group_id = $pg_id
                        AND o.object_type_id = $ot_id
                        AND m.id IN (".implode(',',$members_ids).")
                        AND m.archived_on = 0
                        AND om.is_optimization = 0
                    ";
        $object_ids_from_sharing_table = DB::executeAll($sharing_table_objects_sql);

        return array_filter(array_flat($object_ids_from_sharing_table));
    }
	
	public function rebuild($start_date=null, $end_date=null) {
		if (!$start_date) {
			$start_date = config_option('last_sharing_table_rebuild');
		}
		if ($start_date instanceof DateTimeValue) {
			$start_date = $start_date->toMySQL();
		}
		if ($end_date instanceof DateTimeValue) {
			$end_date = $end_date->toMySQL();
		}
		if ($end_date) {
			$end_cond = "AND updated_on <= '$end_date'";
		}
		
		try {
			$object_ids = DB::executeAll("SELECT id, object_type_id FROM ".TABLE_PREFIX."objects WHERE updated_on >= '$start_date' $end_cond");
			$obj_count = 0;
			DB::beginWork();
			foreach ($object_ids as $info) {
				$oid = $info['id'];
				$tid = $info['object_type_id'];
				ContentDataObjects::addObjToSharingTable($oid);
				$obj_count++;
			}
			set_config_option('last_sharing_table_rebuild', DateTimeValueLib::now()->toMySQL());
			DB::commit();
		} catch(Exception $e) {
			DB::rollback();
			Logger::log("Failed to rebuild sharing table: ".$e->getMessage()."\nTrace: ".$e->getTraceAsString());
		}
		
		return $obj_count;
	}
}
