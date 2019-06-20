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
      
      $more_values = array();
      $keys_to_unset = array();
      foreach ($possible_values as $k => $value) {
      	if (str_starts_with($value, "<dynamic_options:") && str_ends_with($value, ">")) {
      		$table_name = TABLE_PREFIX . str_replace(array("<dynamic_options:",">"), "", $value);
      		if (checkTableExists($table_name)) {
      			$rows = DB::executeAll("SELECT `id`, `name` FROM `$table_name`");
      			if (is_array($rows)) {
      				foreach ($rows as $row) {
      					$more_values[] = array(
      						'id' => $row['id'],
      						'text' => $row['name'],
      					);
      				}
      			}
      		}
      		
      		$keys_to_unset[] = $k;
      	}
      }
      foreach ($keys_to_unset as $k) unset($possible_values[$k]);
      
      foreach ($more_values as $v) $possible_values[] = $v;
      
      foreach ($possible_values as $value) {
      	
      	if (is_array($value)) {
      		$option_id = $value['id'];
      		$option_text = $value['text'];
      	} else {
      		$option_id = $value;
      		$option_text = lang($value);
      	}
      	
      	$checked = array_search($option_id, $current_values) !== false;
      	$out .= '<div class="checkbox-config-option">';
      	$out .= label_tag($option_text, $genid.'_'.$control_name.'_'.$option_id, false, array('style' => 'cursor:pointer;'), '');
      	$out .= checkbox_field($control_name . '[' . $option_id . ']', $checked, array('id' => $genid.'_'.$control_name.'_'.$option_id));
      	$out .= '</div >';
      	
      }
      
      // dummy input to ensure that always is sent something to the server
      $out .= '<input type="hidden" value="0" name="'.$control_name.'[0]">';
      
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

