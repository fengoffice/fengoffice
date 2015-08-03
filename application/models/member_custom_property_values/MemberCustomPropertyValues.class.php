<?php

/**
 *   MemberCustomPropertyValues class
 */
class MemberCustomPropertyValues extends BaseMemberCustomPropertyValues {


	/**
	 * Return custom property value for the object
	 *
	 * @param $object_id
	 * @param $custom_property_id
	 * @return array
	 */
	static function getMemberCustomPropertyValue($object_id, $custom_property_id) {
		return self::findOne(array(
			'conditions' => array("`member_id` = ? AND `custom_property_id` = ?", $object_id, $custom_property_id)
		)); // findOne
	} //  getMemberCustomPropertyValue
	
	/**
	 * Return custom property values for the object
	 *
	 * @param $object_id
	 * @param $custom_property_id
	 * @return array
	 */
	static function getMemberCustomPropertyValues($object_id, $custom_property_id) {
		return self::findAll(array(
			'conditions' => array("`member_id` = ? AND `custom_property_id` = ?", $object_id, $custom_property_id)
		)); // findAll
	} //  getMemberCustomPropertyValue
	
	/**
	 * Delete custom property values for the object
	 *
	 * @param $object_id
	 * @param $custom_property_id
	 * 
	 */
	static function deleteMemberCustomPropertyValues($object_id, $custom_property_id) {
		return self::delete(array("`member_id` = ? AND `custom_property_id` = ?", $object_id, $custom_property_id)); 
	} //  deleteMemberCustomPropertyValues
	
	/**
	 * Return custom property value count for the object
	 *
	 * @param $object_id
	 * @return array
	 */
	static function getMemberCustomPropertyValueCount($object) {
		return count(self::findAll(array(
			'conditions' => array("`member_id` = ? AND `custom_property_id` in (SELECT `id` FROM " . CustomProperties::instance()->getTableName(true) . " where `object_type_id` = ?)"  , $object->getObjectId(), $object->getObjectTypeId())
		))); // findAll
	} //  getMemberCustomPropertyValue
	

} // CustomProperties

?>