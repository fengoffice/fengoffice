<?php

  /**
  * Class that handles integer config values
  *
  * @version 1.0 
  */
  class AllDimensionsConfigHandler extends ConfigHandler {
    
   /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
  	function render($control_name) {
  		return $this->do_render($control_name);
  	}
  	
    function do_render($control_name, $additional_params=array()) {
       $is_default = array_var($additional_params, 'is_default');
       $value =  $this->getValue();
    
       if(!is_null($is_default) && $control_name == 'widget_dimensions'){
         if($is_default){
           $co_widget_dimensions = ContactConfigOptions::getByName('widget_dimensions');
          $value = array_filter(explode(',', $co_widget_dimensions->getDefaultValue()));
         } else {
          $value = array_filter(explode(',', user_config_option('widget_dimensions')));
         }
       }
       $dimensions  = Dimensions::instance()->findAll(array('conditions' => "`code` != 'feng_persons'"));
       $enabled_dimension_ids = config_option('enabled_dimensions');
       
       $onchange_fn = array_var($additional_params, 'onchange_fn');
       
       $out = '' ;
       foreach ($dimensions as $dim) { /* @var $dim Dimension */
       		if (!in_array($dim->getId(), $enabled_dimension_ids)) continue;
			$checked = array_search($dim->getId(), $value) !== false;
			
			$attr = array('id' => $this->getConfigOption()->getName().'_all_dim_'.$dim->getId());
			
			if ($onchange_fn != "") {
        if(is_null($is_default)){
          $attr['onchange'] = "$onchange_fn(this, ".$dim->getId().");";
        } else {
          $is_default = $is_default == 1 ? 1 : 0;
          $attr['onchange'] = "$onchange_fn(this, ".$dim->getId().", ".$is_default.");";
        }
			}
			
	       	$out .= '<div class="dimension" >';
	       	$out .= checkbox_field($control_name.'['.$dim->getId().']', $checked, $attr );
	       	$out .= label_tag($dim->getName(), $this->getConfigOption()->getName().'_all_dim_'.$dim->getId(), false, null, '');
	       	$out .= '</div >';
       }
      
       $out .= '<input type="hidden" name="'.$control_name.'[0]" value=" ">';      
	   return $out;	 
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
		if (is_array($value)) {
			// don't save anything that is not numeric
			$value = array_filter($value, 'is_numeric');
			if (count($value) > 0) {
				return implode(',', $value);
			} else {
				return "";
			}
		}else{
			return $value;
		}
	}

  } 
  
  
  
  
