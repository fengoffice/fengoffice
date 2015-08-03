<?php

  /**
  * Class that handles status config
  *
  * @version 1.0 
  */
  class StatusConfigHandler extends ConfigHandler {
    
   /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
    	$status = array();
    	
    	$config_opt = $this->getConfigOption();
    	$config_opt_name = $config_opt->getName();
    	
    	if($config_opt_name == "default_customer_status_value"){
    		$status =  array(array(0,"-")) ;
    		foreach ( CustomerStatusManager::instance()->findAll() as $status_obj) {
    			$text  = lang($status_obj->getName()) ;
    			$value = $status_obj->getId() ;
    			$status[] =  array ($value, $text) ;
    		}
    	}
    	
    	if($config_opt_name == "default_project_status_value"){
    		$status = array() ;
    		for ($i=1; $i<=7; $i++) $status[] = array($i, lang('project_field_status'.$i));
    	}
    	
    	return simple_select_box($control_name, $status,$this->getRawValue());
    }
    

    
    /**
    * Convert raw value to php
    *
    * @param string $value
    * @return mixed
    */
    function rawToPhp($value) {
      return (integer) $value;
    } // rawToPhp
    
	function phpToRaw($value) {
		return (string) $value;
	}

  } 
  
  
  
  
