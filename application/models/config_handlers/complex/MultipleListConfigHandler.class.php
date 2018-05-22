<?php

  class MultipleListConfigHandler extends ConfigHandler {
  
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      $genid = gen_id();
      
      $data = $this->getConfigOption()->getOptions();
      $possible_values = explode(",", $data);
      $current_values = $this->getValue();
      
      foreach ($possible_values as $value) {
      	
      	$checked = array_search($value, $current_values) !== false;
      	$out .= '<div class="checkbox-config-option">';
      	$out .= label_tag(lang($value), $genid.'_'.$control_name.'_'.$value, false, array('style' => 'cursor:pointer;'), '');
      	$out .= checkbox_field($control_name . '[' . $value . ']', $checked, array('id' => $genid.'_'.$control_name.'_'.$value));
      	$out .= '</div >';
      	
      }
      
      $attributes = array('id' => 'multiple_list_' . $this->getConfigOption()->getName());
      
      return $out;
    } // render
    
    
    function rawToPhp($value) {
    	return explode(",", $value);
    }
    
    function phpToRaw($value) {
    	if (is_array($value) && count($value)) {
    		unset($value[0]);
    		return implode(',', array_keys($value));
    	}else{
    		return $value;
    	}
    }
  
  } // MultipleListConfigHandler

