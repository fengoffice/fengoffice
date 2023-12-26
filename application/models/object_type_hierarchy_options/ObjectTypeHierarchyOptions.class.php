<?php

  /**
  * ObjectTypeHierarchyOptions
  */
  class ObjectTypeHierarchyOptions extends BaseObjectTypeHierarchyOptions {
    
  	
  	
  	static function getOptionValue($hierarchy_id, $dimension_id, $member_type_id, $config_name) {
  		
  		$option_val = self::instance()->findById(array('hierarchy_id' => $hierarchy_id, 'dimension_id' => $dimension_id, 'member_type_id' => $member_type_id, 'option' => $config_name));
  		if ($option_val instanceof ObjectTypeHierarchyOption) {
  			return $option_val->getValue();
  		}
  		
  		return "";
  	}
  	
  }

?>