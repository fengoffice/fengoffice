<?php

  /**
  * Class that handles new client contact type
  *
  * @version 1.0 
  */
  class NewClientContactTypeConfigHandler extends ConfigHandler {
    
   /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
    	$options = array();
    	
    	$contact_types[] =  array (ObjectTypes::findByName("contact")->getId(), lang("contact")) ;
    	$contact_types[] =  array (ObjectTypes::findByName("company")->getId(), lang("company")) ;
    	
    	$unknown_id = 0;
    	$option_attributes = $this->getRawValue() == $unknown_id ? array('selected' => 'selected') : null;
    	$options[] = option_tag(lang("unknown"), $unknown_id, $option_attributes);
    	
    	$contact_id = ObjectTypes::findByName("contact")->getId();
    	$option_attributes = $this->getRawValue() == $contact_id ? array('selected' => 'selected') : null;
    	$options[] = option_tag(lang("contact"), $contact_id, $option_attributes);
    	
    	$company_id = ObjectTypes::findByName("company")->getId();    	
    	$option_attributes = $this->getRawValue() == $company_id ? array('selected' => 'selected') : null;
    	$options[] = option_tag(lang("company"), $company_id, $option_attributes);   	
    	
    	
    	return select_box($control_name, $options);
    }
    

    
    /**
    * Convert raw value to php
    *
    * @param string $value
    * @return mixed
    */
    function rawToPhp($value) {
      return explode(",", $value);
    } // rawToPhp
    
	function phpToRaw($value) {
		if (is_array($value) && count($value)) {
			unset($value[0]);
			return implode(',', array_keys($value));
		}else{
			return $value;
		}
	}

  } 
  
  
  
  
