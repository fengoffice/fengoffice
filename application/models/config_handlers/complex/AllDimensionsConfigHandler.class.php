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
       $value =  $this->getValue();
       $dimensions  = Dimensions::instance()->findAll(array('conditions' => "`code` != 'feng_persons'"));
       
       $onchange_fn = array_Var($additional_params, 'onchange_fn');
       
       $out = '' ;
       foreach ($dimensions as $dim) { /* @var $dim Dimension */
			$checked = array_search($dim->getId(), $value) !== false;
			
			$attr = array('id' => 'all_dim_'.$dim->getId());
			
			if ($onchange_fn != "") {
				$attr['onchange'] = "$onchange_fn(this, ".$dim->getId().");";
			}
			
	       	$out .= '<div class="dimension" >';
	       	$out .= checkbox_field($control_name.'['.$dim->getId().']', $checked, $attr );
	       	$out .= label_tag($dim->getName(), 'all_dim_'.$dim->getId(), false, null, '');
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
		if (is_array($value) && count($value)) {
			$value = array_filter($value);
			return implode(',', $value);
		}else{
			return $value;
		}
	}

  } 
  
  
  
  
