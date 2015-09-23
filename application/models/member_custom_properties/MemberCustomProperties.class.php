<?php

/**
 *   CustomProperty class
 */
class  MemberCustomProperties extends  BaseMemberCustomProperties {

	/**
	 * Return custom properties that are not visilbe by default.
	 * @param $object_type
	 * @return unknown_type
	 */
	static function getHiddenMemberCustomPropertiesByObjectType($object_type) {
		if (!Plugins::instance()->isActivePlugin('member_custom_properties')) {
			return array();
		}
		return self::findAll(array(
			'conditions' => array("`object_type` = ? AND `is_required` = ? AND `visible_by_default` = ? AND is_disabled=0", $object_type, false, false),
			'order' => 'property_order asc'
		));
	}
	
	/**
	 * Count custom properties that are not visilbe by default.
	 * @param $object_type
	 * @return integer
	 */
	static function countHiddenMemberCustomPropertiesByObjectType($object_type_id) {
		if (!Plugins::instance()->isActivePlugin('member_custom_properties')) {
			return 0;
		}
		return self::count(array("`object_type_id` = ? AND `is_required` = ? AND `visible_by_default` = ? AND is_disabled=0", $object_type_id, false, false));
	}
	
	/**
	 * Count custom properties that are visilbe by default.
	 * @param $object_type
	 * @return integer
	 */
	static function countVisibleMemberCustomPropertiesByObjectType($object_type_id) {
		if (!Plugins::instance()->isActivePlugin('member_custom_properties')) {
			return 0;
		}
		return self::count(array("`object_type_id` = ? AND (`is_required` = ? OR `visible_by_default` = ?) AND is_disabled=0", $object_type_id, true, true));
	}

	/**
	 * Return all custom properties that an object type has
	 *
	 * @param $object_type
	 * @return array
	 * 
	 */
	static function getAllMemberCustomPropertiesByObjectType($object_type, $visibility='all', $include_disabled=false) {
		if (!Plugins::instance()->isActivePlugin('member_custom_properties')) {
			return array();
		}
		$visibility_cond = "";
		if ($visibility != 'all') {
			$visibility_cond = " AND visible_by_default = ". ($visibility == 'visible_by_default' ? '1' : '0');
		}
		$disabled_cond = "";
		if (!$include_disabled) {
			$disabled_cond = "AND is_disabled=0";
		}
		$cond = array("`object_type_id` = ? $visibility_cond $disabled_cond", $object_type);
		
		return self::findAll(array(
			'conditions' => $cond,
			'order' => 'property_order asc'
		));
	} //  getAllMemberCustomPropertiesByObjectType
	
	
	/**
	 * Returns an array of the custom property ids for a given object type
	 *
	 * @param $object_type
	 * @return array
	 */
	static function getCustomPropertyIdsByObjectType($object_type) {
		if (!Plugins::instance()->isActivePlugin('member_custom_properties')) {
			return array();
		}
		return self::findAll(array(
			'id' => true,
			'conditions' => array("`object_type_id` = ?", $object_type),
			'order' => 'property_order asc'
		));
	} //  getAllMemberCustomPropertiesByObjectType
	

	/**
	 * Return one custom property, given the object type and the property name
	 *
	 * @param String $custom_property_name
	 * @return array
	 */
	static function getCustomPropertyByName($object_type, $custom_property_name) {
		if (!Plugins::instance()->isActivePlugin('member_custom_properties')) {
			return null;
		}
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
		if (!Plugins::instance()->isActivePlugin('member_custom_properties')) {
			return null;
		}
		return self::findOne(array( 'conditions' => array("`id` = ? ", $prop_id) ));
	} //  getCustomProperty

	
	static function deleteAllByObjectType($object_type){
		if (!Plugins::instance()->isActivePlugin('member_custom_properties')) {
			return null;
		}
		return self::delete("`object_type_id` = " . DB::escape($object_type));
	}

	static function deleteByObjectTypeAndName($object_type, $name) {
		if (!Plugins::instance()->isActivePlugin('member_custom_properties')) {
			return null;
		}
		return self::delete("`object_type_id` = " . DB::escape($object_type) . "' AND `name` = " . DB::escape($name));
	}
	
	
	static function getMemberCustomPropertySingleValueByCode($code, $member_id) {
		if (!Plugins::instance()->isActivePlugin('member_custom_properties')) {
			return "";
		}
		$cp = MemberCustomProperties::instance()->findOne(array('conditions' => "code='$code'"));
		if ($cp instanceof MemberCustomProperty) {
			$cpvalue = MemberCustomPropertyValues::getMemberCustomPropertyValue($member_id, $cp->getId());
			if ($cpvalue instanceof MemberCustomPropertyValue) {
				return $cpvalue->getValue();
			}
		}
		return "";
	}
	
	static function getMemberCustomPropertyMultipleValueByCode($code, $member_id) {
		if (!Plugins::instance()->isActivePlugin('member_custom_properties')) {
			return array();
		}
		$values = array();
		$cp = MemberCustomProperties::instance()->findOne(array('conditions' => "code='$code'"));
		if ($cp instanceof MemberCustomProperty) {
			$cpvalues = MemberCustomPropertyValues::getMemberCustomPropertyValues($member_id, $cp->getId());
			foreach ($cpvalues as $cpvalue) {
				$values[] = $cpvalue->getValue();
			}
		}
		return $values;
	}

} // MemberCustomProperties

?>