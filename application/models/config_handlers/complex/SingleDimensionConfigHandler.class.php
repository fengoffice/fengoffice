<?php

/**
* Class that handles integer config values
*
* @version 1.0 
*/
class SingleDimensionConfigHandler extends ConfigHandler {
    
   /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
    	
    	$dimensions = Dimensions::instance()->findAll(array('conditions' => '`is_manageable` = 1'));
    	
    	$options = array();
		
		$option_attributes = $this->getValue() == 0 ? array('selected' => 'selected') : null;
		$options[] = option_tag(lang('none'), 0, $option_attributes);
		
    	foreach ($dimensions as $dim) { /* @var $dim Dimension */
			
       		if (in_array($dim->getId(), config_option('enabled_dimensions'))) {
       			
       			$dim_name = $dim->getName();
       			
	       		$option_attributes = $this->getValue() == $dim->getId() ? array('selected' => 'selected') : null;
	       		$options[] = option_tag($dim_name, $dim->getId(), $option_attributes);
	       		
       		}
    	}
    	
    	return select_box($control_name, $options);	 
	}
    
} 
  
  
  
  
