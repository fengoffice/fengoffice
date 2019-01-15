<?php

/**
* Class that handles integer config values
*
* @version 1.0 
*/
class SingleMemberTypeConfigHandler extends ConfigHandler {
    
   /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {

    	$dimensions = Dimensions::instance()->findAll(array('conditions' => '`is_manageable` = 1'));
    	$member_types = ObjectTypes::findAll(array(
			"conditions" => "`type` IN ('dimension_group', 'dimension_object') AND 
			`name` <> 'project_folder' AND name <> 'customer_folder' AND 
			IF(plugin_id IS NULL OR plugin_id=0, true, (SELECT p.is_activated FROM ".TABLE_PREFIX."plugins p WHERE p.id=plugin_id) = true)"
		));
    	
    	$options = array();
		
		$option_attributes = $this->getValue() == 0 ? array('selected' => 'selected') : null;
		$options[] = option_tag(lang('none'), 0, $option_attributes);
		
    	foreach ($member_types as $member_type) { /* @var $member_type ObjectType */
			
       		$type_name = $member_type->getObjectTypeName(false);
       		
	       	$option_attributes = $this->getValue() == $member_type->getId() ? array('selected' => 'selected') : null;
	       	$options[] = option_tag($type_name, $member_type->getId(), $option_attributes);
	       	
    	}
    	
    	return select_box($control_name, $options);	 
	}
    
} 
  
  
  
  
