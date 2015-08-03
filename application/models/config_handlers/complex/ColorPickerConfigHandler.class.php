<?php

  /**
  * Class that handles color values
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
    	
    	$out = '<div class="color-picker-container"><input type="text" class="color-picker '.$color_index.'" value="#'.$value.'" name="'.$control_name.'" id="'.$control_name.'" /></div>';
    	$out .= '<script>
    	$(".color-picker-container .color-picker.'.$color_index.'").modcoder_excolor({
    		shadow : false,
    		background_color : "#eeeeee",
    		backlight : false,
    		callback_on_ok : function() {
    			og.config.brand_colors["'.$color_index.'"] = document.getElementById("'.$control_name.'").value.substring(1,7);
    			og.createBrandColorsSheet(og.config.brand_colors);
    		}
    	});
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
  
  
  
  