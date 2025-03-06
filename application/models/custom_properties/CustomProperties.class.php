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
		$extra_conditions = "";
		Hook::fire('add_custom_property_condition', array('user'=>logged_user()), $extra_conditions);
		return self::instance()->findAll(array(
			'conditions' => array("`object_type` = ? AND `is_required` = ? AND `visible_by_default` = ? $extra_conditions", $object_type, false, false),
			'order' => 'property_order asc'
		));
	}

	/**
	 * Count custom properties that are not visilbe by default.
	 * @param $object_type
	 * @return integer
	 */
	static function countHiddenCustomPropertiesByObjectType($object_type_id) {
		$extra_conditions = "";
		Hook::fire('add_custom_property_condition', array('user'=>logged_user()), $extra_conditions);
		return self::instance()->count(array("`object_type_id` = ? AND `is_required` = ? AND `visible_by_default` = ? $extra_conditions", $object_type_id, false, false));
	}

	/**
	 * Count all custom properties for object type
	 * @param $object_type
	 * @return integer
	 */
	static function countAllCustomPropertiesByObjectType($object_type_id) {
		$extra_conditions = "";
		Hook::fire('add_custom_property_condition', array('user'=>logged_user()), $extra_conditions);
		return self::instance()->count(array("`object_type_id` = ? $extra_conditions", $object_type_id));
	}

	/**
	 * Count custom properties that are visilbe by default.
	 * @param $object_type
	 * @return integer
	 */
	static function countVisibleCustomPropertiesByObjectType($object_type_id) {
		$extra_conditions = "";
		Hook::fire('add_custom_property_condition', array('user'=>logged_user()), $extra_conditions);
		return self::instance()->count(array("`object_type_id` = ? AND (`is_required` = ? OR `visible_by_default` = ?) $extra_conditions", $object_type_id, true, true));
	}



	/**
	 * Return custom properties that are visilbe by default.
	 * @param $object_type
	 * @return unknown_type
	 */
	function get_cp_names_from_ids($cp_ids) {
		$cp_names = array();
		foreach ($cp_ids as $cp_id) {
			$cp = CustomProperties::getCustomProperty($cp_id);
			$cp_names[] = $cp->getName();
		}	
		return $cp_names;
	}

	/**
	 * Return custom properties that are visilbe by default.
	 * @param $object_type
	 * @return unknown_type
	 */
	function get_cp_names_from_cp_inputsId_on_forms($cp_ids) {
		
		$cleaned_ids = [];
		if (is_string($cp_ids)) {
			$cp_ids = explode(',', $cp_ids);
		}

		// Clean the error ids using get_cp_codes_from_inputs_id
		foreach ($cp_ids as $id) {
			// Extract only the numeric part of the id after 'cp'
			if (preg_match('/cp(\d+)/', $id, $matches)) {
				$cleaned_ids[] = $matches[1]; // Add the cleaned code to the array
			}
		}
    
		$cpNames_with_errors_on_form = CustomProperties::instance()->get_cp_names_from_ids($cleaned_ids);
		return $cpNames_with_errors_on_form;
	}


	
	/**
	 * Return all custom properties that an object type has
	 *
	 * @param $object_type
	 * @return array
	 *
	 */
	static function getAllCustomPropertiesByObjectType($object_type, $visibility = 'all', $extra_cond = "", $fire_cond_hook=false, $include_disabled=false, $object = null) {

		if ($fire_cond_hook) {
			Hook::fire('get_custom_properties_conditions', array('ot' => $object_type), $extra_cond);
		}

		if ($visibility != 'all') {
			if ($visibility == 'visible_by_default') {
				$extra_cond .= " AND (visible_by_default = 1 OR is_required = 1)";
			} else {
				$extra_cond .= " AND (visible_by_default = 0 AND is_required = 0)";
			}
		}

		 if ($object && ($object->getObjectTypeName() == 'contact' || $object->getObjectTypeName() == 'company')){
			if($object->isUser()){
				$extra_cond .= " AND (contact_type LIKE 'user' OR contact_type LIKE 'all') ";
			} else {
				$extra_cond .= " AND (contact_type LIKE 'contact' OR contact_type LIKE 'all') ";
			}
		}

		$disabled_cond = "";
		if (!$include_disabled) {
			$disabled_cond = "AND is_disabled=0";
		}
		Hook::fire('add_custom_property_condition', array('user'=>logged_user()), $extra_cond);
		$cond = array("`object_type_id` = ? $extra_cond $disabled_cond", $object_type);
		return self::instance()->findAll(array(
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
		$extra_conditions = "";
		Hook::fire('add_custom_property_condition', array('user'=>logged_user()), $extra_conditions);
		return self::instance()->findAll(array(
			'id' => true,
			'conditions' => array("`object_type_id` = ? $extra_conditions", $object_type),
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
		$extra_conditions = "";
		Hook::fire('add_custom_property_condition', array('user'=>logged_user()), $extra_conditions);
		return self::instance()->findOne(array(
			'conditions' => array("`object_type_id` = ? and `name` = ? $extra_conditions", $object_type, $custom_property_name)
		));
	} //  getCustomPropertyByName

	
	static private $cp_code_cache = array();
    /**
     * Return one custom property, given the object type and the property code
     *
     * @param $object_type
     * @param $custom_property_code
     * @return CustomProperty
     */
	static function getCustomPropertyByCode($object_type, $custom_property_code) {
		$key = $object_type."-".$custom_property_code;
		$cp = array_var(self::$cp_code_cache, $key);
		if (!$cp instanceof CustomProperty) {
			$extra_conditions = "";
			Hook::fire('add_custom_property_condition', array('user'=>logged_user()), $extra_conditions);
			$cp = self::instance()->findOne(array(
				'conditions' => array("`object_type_id` = ? and `code` = ? $extra_conditions", $object_type, $custom_property_code)
			));
			self::$cp_code_cache[$key] = $cp;
		}
		return $cp;
	} //  getCustomPropertyByCode

	/**
	 * Return one custom property given the id
	 *
	 * @param int $prop_id
	 * @return CustomProperty
	 */
	static function getCustomProperty($prop_id) {
		$extra_conditions = "";
		Hook::fire('add_custom_property_condition', array('user'=>logged_user()), $extra_conditions);
		return self::instance()->findOne(array( 'conditions' => array("`id` = ? $extra_conditions", $prop_id) ));
	} //  getCustomProperty


	static function deleteAllByObjectType($object_type){
		return self::instance()->delete("`object_type_id` = " . DB::escape($object_type));
	}

	static function deleteByObjectTypeAndName($object_type, $name) {
		return self::instance()->delete("`object_type_id` = " . DB::escape($object_type) . "' AND `name` = " . DB::escape($name));
	}

} // CustomProperties

?>
