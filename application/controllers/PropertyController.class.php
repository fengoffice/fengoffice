<?php

/**
 * Property controller
 *
 * @version 1.0
 */
class PropertyController extends ApplicationController {

	/**
	 * Construct the PropertyController
	 *
	 * @access public
	 * @param void
	 * @return PropertyController
	 */
	function __construct() {
		parent::__construct();
	}
	
	function get_custom_properties(){
		$object_type = array_var($_GET, 'object_type');
		if($object_type){
			$cp = CustomProperties::getAllCustomPropertiesByObjectType($object_type);
			$customProperties = array();
			foreach($cp as $custom){
				$prop = array();
				$prop['id'] = $custom->getId();
				$prop['name'] = $custom->getName();
				$prop['object_type'] = $custom->getObjectTypeId();
				$prop['description'] = $custom->getDescription();
				$prop['type'] = $custom->getType();
				$prop['values'] = $custom->getValues();
				$prop['default_value'] = $custom->getDefaultValue();
				$prop['required'] = $custom->getIsRequired();
				$prop['multiple_values'] = $custom->getIsMultipleValues();
				$prop['visible_by_default'] = $custom->getVisibleByDefault();
				$prop['co_types'] = '';//CustomPropertiesByCoType::instance()->getCoTypesIdsForCpCSV($custom->getId());
				$prop['order'] = $custom->getOrder();
				$customProperties[] = $prop;
			}
			ajx_current("empty");
			ajx_extra_data(array("custom_properties" => $customProperties));
		}
	}
}
?>