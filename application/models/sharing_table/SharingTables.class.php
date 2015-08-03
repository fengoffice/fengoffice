<?php

class SharingTables extends BaseSharingTables {
	
	/** 
	 * 
	 * @param array $groupIds
	 * @param int $objectId
	 */
	public function populateGroups ( $groupIds, $objectId) {
		if ( !is_array($groupIds) ) {
			throw new Error(lang("empty group array"), null , null );
		}
		
		// Delete old rows
		self::delete("object_id = $objectId");

		// Insert new rows
		$table = self::getTableName();
		$cols = array("group_id", "object_id") ;
		$rows = array() ;
		foreach ($groupIds as $gid) {
			$rows[] = array( $gid, $objectId);
		}
		massiveInsert($table, $cols, $rows, 100, " ON DUPLICATE KEY UPDATE ".$table.".group_id=".$table.".group_id;");
		$rows = null;
	}
	
	/**
	 * 
	 * @param array $objectIds
	 * @param int $groupId
	 */
	public function populateObjects($objectIds, $groupId ) {
		
		if ( !is_array($objectIds) ) {
			throw new Error(lang("empty group array"), null , null );
		}

		// Insert new rows
		$table = SharingTables::getTableName();
		$cols = array("group_id", "object_id") ;
		$rows = array() ;
		foreach ($objectIds as $oid) {
			$rows[] = array($groupId, $oid );
		}
		massiveInsert($table, $cols, $rows, 10000, " ON DUPLICATE KEY UPDATE ".$table.".group_id=".$table.".group_id;");
		$rows = null;
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
			$object_ids = Objects::instance()->findAll(array('id' => true, "conditions" => "updated_on >= '$start_date' $end_cond"));
			$obj_count = 0;
			DB::beginWork();
			foreach ($object_ids as $id) {
				$obj = Objects::findObject($id);
				if ($obj instanceof ContentDataObject) {
					$obj->addToSharingTable();
					$obj_count++;
				}
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
