<?php

/**
 * MaxRoleObjectTypePermissions
 *
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class MaxRoleObjectTypePermissions extends BaseMaxRoleObjectTypePermissions {
	
	static function getAllMaxRoleObjectTypePermissionsInfo() {
		$objects = self::findAll(array('raw_data' => true));
		$info = array();
		foreach ($objects as $obj) {
			$data = array();
			foreach ($obj->getColumns() as $col) {
				$data[$col] = $obj->getColumnValue($col);
			}
			if (!isset($info[$data['role_id']])) $info[$data['role_id']] = array();
			$info[$data['role_id']][$data['object_type_id']] = $data;
		}
		
		return $info;
	}
	
} 
