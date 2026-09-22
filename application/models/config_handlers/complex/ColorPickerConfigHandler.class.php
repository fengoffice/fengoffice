<?php

  /**
  * Class that handles color values using native HTML5 color picker
  *
  * @version 1.0
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  class ColorPickerConfigHandler extends ConfigHandler {
    
   /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
    	$value = $this->getValue();
    	
    	$color_index = str_replace("]", "", str_replace("options[", "", $control_name));
    	
    	// Ensure value has # prefix for the color picker
    	$color_value = $value;
    	if (strpos($color_value, '#') !== 0) {
    		$color_value = '#' . $color_value;
    	}
    	
    	$out = '<div class="color-picker-container">';
    	$out .= '<input type="color" class="color-picker-native '.$color_index.'" value="'.$color_value.'" name="'.$control_name.'" id="'.$control_name.'" onchange="og.updateBrandColor(\''.$color_index.'\', \''.$control_name.'\')" />';
    	$out .= '</div>';
    	$out .= '<script>
    	og.updateBrandColor = function(colorIndex, controlName) {
    		var colorValue = document.getElementById(controlName).value;
    		// Remove # prefix for storage
    		og.config.brand_colors[colorIndex] = colorValue.substring(1);
    		og.createBrandColorsSheet(og.config.brand_colors);
    	};
    	</script>';
    	
    	return $out;
    }
    

    
    function rawToPhp($value) {
      return $value;
    }
    
	function phpToRaw($value) {
		$value = str_replace("#", "", $value);
		return $value;
	}

  } 
  
  
  
