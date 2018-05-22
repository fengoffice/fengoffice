<?php

  class TimeAmountConfigHandler extends ConfigHandler {
    
    
    function render($control_name) {
    	
    	$exploded = explode(":", $this->getValue());
    	$hours = array_var($exploded, 0, 0);
    	$minutes = array_var($exploded, 1, 0);
    	
    	$input_attr = array("style" => "width:30px;", "onkeyup" => "event.target.value = event.target.value.replace(/[^0-9]/g, '')");
    	
    	echo text_field($control_name."[hours]", $hours, $input_attr);
    	echo "&nbsp;" . lang('hours');
    	
    	echo "&nbsp;&nbsp;";
    	echo text_field($control_name."[minutes]", $minutes, $input_attr);
    	echo "&nbsp;" . lang('minutes');
    	
    }
    
    function phpToRaw($value) {
    	if (is_array($value)) {
    		return $value['hours'] .":". $value['minutes'];
    	} else {
    		return $value;
    	}
    }
    
  }    
?>
