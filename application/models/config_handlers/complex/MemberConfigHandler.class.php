<?php

/**
* Class that handles member config values
*
* @version 1.0 
*/
class MemberConfigHandler extends ConfigHandler {
    
   /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
    	
    	$options = null;
    	$opt_options = $this->getConfigOption()->getOptions();
    	if ($opt_options) {
    		$options = json_decode($opt_options, true);
    	}
    	
    	if (!$options) return;
    	$is_multiple = array_var($options, 'is_multiple', false);
    	
    	$sel_options = array(
    		'is_multiple' => $is_multiple,
    		'width' => '300',
    		'hide_label' => true,
    		'hidden_field_name' => $control_name,
    	);
    	
    	$dimension = Dimensions::findById($options['dim_id']);
    	if (isset($options['mem_type_ids'])) {
    		$sel_options['allowedMemberTypes'] = $options['mem_type_ids'];
    	}
    	
    	$selected_values = $is_multiple ? $this->getValue() : array($this->getValue());
    	
    	ob_start();
    	render_single_member_selector($dimension, null, $selected_values, $sel_options);
    	$html = ob_get_clean();
    	
    	return $html;
	}
	
	
	function rawToPhp($value) {
		$options = null;
		$opt_options = $this->getConfigOption()->getOptions();
		if ($opt_options) {
			$options = json_decode($opt_options, true);
		}
		$is_multiple = array_var($options, 'is_multiple', false);
		
		if (is_numeric($value)) return $value;
		$vals = json_decode($value);
		
		if ($is_multiple) {
			return $vals;
		} else {
			return array_var($vals, 0);
		}
	}
	
	function phpToRaw($value) {
		$options = null;
		$opt_options = $this->getConfigOption()->getOptions();
		if ($opt_options) {
			$options = json_decode($opt_options, true);
		}
		$is_multiple = array_var($options, 'is_multiple', false);
		
		if (is_numeric($value)) return $value;
		
		if ($is_multiple) {
			return $value;
		} else {
			$vals = json_decode($value);
			return array_var($vals, 0);
		}
	}
    
}
