<?php

/**
 *   CustomProperty class
 */
class  CustomProperties extends  BaseCustomProperties {

	/**
	 * Return custom properties that are not visilbe by default.
	 * @param $object_type
	 * @return unknown_type
	 */
	static function getHiddenCustomPropertiesByObjectType($object_type) {
		return self::findAll(array(
			'conditions' => array("`object_type` = ? AND `is_required` = ? AND `visible_by_default` = ?", $object_type, false, false),
			'order' => 'property_order asc'
		));
	}
	
	/**
	 * Count custom properties that are not visilbe by default.
	 * @param $object_type
	 * @return integer
	 */
	static function countHiddenCustomPropertiesByObjectType($object_type_id) {
		return self::count(array("`object_type_id` = ? AND `is_required` = ? AND `visible_by_default` = ?", $object_type_id, false, false));
	}
	
	/**
	 * Count all custom properties for object type
	 * @param $object_type
	 * @return integer
	 */
	static function countAllCustomPropertiesByObjectType($object_type_id) {
		return self::count(array("`object_type_id` = ?", $object_type_id));
	}
	
	/**
	 * Count custom properties that are visilbe by default.
	 * @param $object_type
	 * @return integer
	 */
	static function countVisibleCustomPropertiesByObjectType($object_type_id) {
		return self::count(array("`object_type_id` = ? AND (`is_required` = ? OR `visible_by_default` = ?)", $object_type_id, true, true));
	}

	/**
	 * Return all custom properties that an object type has
	 *
	 * @param $object_type
	 * @return array
	 * 
	 */
	static function getAllCustomPropertiesByObjectType($object_type, $co_type = null) {
		$cond = array("`object_type_id` = ?", $object_type);
		
		return self::findAll(array(
			'conditions' => $cond,
			'order' => 'property_order asc'
		));
	} //  getAllCustomPropertiesByObjectType
	
	
	/**
	 * Returns an array of the custom property ids for a given object type
	 *
	 * @param $object_type
	 * @return array
	 */
	static function getCustomPropertyIdsByObjectType($object_type) {
		return self::findAll(array(
			'id' => true,
			'conditions' => array("`object_type_id` = ?", $object_type),
			'order' => 'property_order asc'
		));
	} //  getAllCustomPropertiesByObjectType
	

	/**
	 * Return one custom property, given the object type and the property name
	 *
	 * @param String $custom_property_name
	 * @return array
	 */
	static function getCustomPropertyByName($object_type, $custom_property_name) {
		return self::findOne(array(
			'conditions' => array("`object_type_id` = ? and `name` = ? ", $object_type, $custom_property_name)
		));
	} //  getCustomPropertyByName

	/**
	 * Return one custom property given the id
	 *
	 * @param int $prop_id
	 * @return CustomProperty
	 */
	static function getCustomProperty($prop_id) {
		return self::findOne(array( 'conditions' => array("`id` = ? ", $prop_id) ));
	} //  getCustomProperty

	
	static function deleteAllByObjectType($object_type){
		return self::delete("`object_type_id` = " . DB::escape($object_type));
	}

	static function deleteByObjectTypeAndName($object_type, $name) {
		return self::delete("`object_type_id` = " . DB::escape($object_type) . "' AND `name` = " . DB::escape($name));
	}

} // CustomProperties

?>