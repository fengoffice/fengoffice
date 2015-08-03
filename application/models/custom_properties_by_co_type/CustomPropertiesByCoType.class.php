<?php

/**
 *   CustomPropertyByCoType class
 *
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class CustomPropertiesByCoType extends BaseCustomPropertiesByCoType {

	/**
	 * Return custom property ids for the object type
	 *
	 * @param $co_type
	 * @return string
	 */
	function getCustomPropertyIdsByCoTypeCSV($co_type) {
		$ids ='';
		$sql = "SELECT `cp_id` FROM `".$this->getTableName()."` WHERE `co_type_id` = $co_type";
		$rows = DB::executeAll($sql);
		if (is_array($rows)) {
			foreach ($rows as $r) {
				$ids .= ($ids == '' ? '' : ',') . $r['cp_id'];
			}
		}
		return $ids;
	} //  getCustomPropertyValue
	
	/**
	 * Return co type ids for custom property
	 *
	 * @param $cp_id
	 * @return string
	 */
	function getCoTypesIdsForCpCSV($cp_id) {
		$ids ='';
		$sql = "SELECT `co_type_id` FROM `".$this->getTableName()."` WHERE `cp_id` = $cp_id";
		$rows = DB::executeAll($sql);
		if (is_array($rows)) {
			foreach ($rows as $r) {
				$ids .= ($ids == '' ? '' : ',') . $r['co_type_id'];
			}
		}
		return $ids;
	} //  getCustomPropertyValue
	
	static function clearCoTypesForCustomProperty($cp_id) {
		return self::delete("`cp_id` = $cp_id");
	}
	
} // CustomProperties

?>