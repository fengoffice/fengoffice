<?php 

class ObjectTypeDependencies extends BaseObjectTypeDependencies {
	
	static function getMainObjectTypeId($dependant_ot_id) {
		$row = DB::executeOne("
					SELECT object_type_id FROM ".self::instance()->getTableName()."
					WHERE dependant_object_type_id='$dependant_ot_id'
				");
		
		if (is_array($row)) {
			return array_var($row, 'object_type_id');
		}
		return null;
	}
	
	static function getDependantObjectTypeIds($dependant_ot_id) {
		$rows = DB::executeAll("
					SELECT dependant_object_type_id FROM ".self::instance()->getTableName()."
					WHERE object_type_id='$dependant_ot_id'
				");
		
		$ids = array();
		if (is_array($rows)) {
			$ids = array_filter(array_flat($rows));
		}
		return $ids;
	}
}
