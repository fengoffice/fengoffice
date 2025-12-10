<?php

  /**
  * Class that handles calculation method config
  *
  * @version 1.0 
  */
  class CalculationMethodConfigHandler extends ConfigHandler {
    
   /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
    	$calc_methods = array();
    	
      // Option 0: 'T&M' (Time and Material) - default
      // Option 1: 'Fixed Fee'
    	for ($i=0; $i<2; $i++) {
        $calc_methods[] = array($i, lang('default_earned_value_calcultion_method'.$i));
      }

    	return simple_select_box($control_name, $calc_methods,$this->getRawValue());
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
  
  
  
  
