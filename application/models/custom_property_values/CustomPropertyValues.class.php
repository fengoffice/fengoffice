<?php

/**
 *   CustomPropertyValues class
 */
class CustomPropertyValues extends BaseCustomPropertyValues {


	/**
	 * Return custom property value for the object
	 *
	 * @param $object_id
	 * @param $custom_property_id
	 * @return CustomPropertyValue
	 */
	static function getCustomPropertyValue($object_id, $custom_property_id) {
		return self::instance()->findOne(array(
			'conditions' => array("`object_id` = ? AND `custom_property_id` = ?", $object_id, $custom_property_id)
		)); // findOne
	} //  getCustomPropertyValue
	
	/**
	 * Return custom property values for the object
	 *
	 * @param $object_id
	 * @param $custom_property_id
	 * @return array
	 */
	static function getCustomPropertyValues($object_id, $custom_property_id) {
		return self::instance()->findAll(array(
			'conditions' => array("`object_id` = ? AND `custom_property_id` = ?", $object_id, $custom_property_id)
		)); // findAll
	} //  getCustomPropertyValue
	
	/**
	 * Return all custom property values for the object
	 *
	 * @param $object_id
	 * @return array
	 */
	static function getAllCustomPropertyValuesForObject($object_id) {
		return self::instance()->findAll(array(
			'conditions' => array("`object_id` = ?", $object_id)
		)); // findAll
	} //  getAllCustomPropertyValuesForObject
	
	/**
	 * Delete custom property values for the object
	 *
	 * @param $object_id
	 * @param $custom_property_id
	 * 
	 */
	static function deleteCustomPropertyValues($object_id, $custom_property_id) {
		return self::instance()->delete(array("`object_id` = ? AND `custom_property_id` = ?", $object_id, $custom_property_id)); 
	} //  deleteCustomPropertyValues
	
	/**
	 * Return custom property value count for the object
	 *
	 * @param $object_id
	 * @return array
	 */
	static function getCustomPropertyValueCount($object, $visibility='all') {
		$visibility_cond = "";
		if ($visibility != 'all') {
			if ($visibility == 'visible_by_default') $visibility_cond = " AND visible_by_default=1";
			else $visibility_cond = " AND visible_by_default=0";
		}
		return count(self::instance()->findAll(array(
			'conditions' => array("`object_id` = ? AND `custom_property_id` in (SELECT `id` FROM " . 
				CustomProperties::instance()->getTableName(true) . " where `object_type_id` = ? $visibility_cond)"  , $object->getObjectId(), $object->getObjectTypeId())
		))); // findAll
	} //  getCustomPropertyValue
	

} // CustomProperties

?>