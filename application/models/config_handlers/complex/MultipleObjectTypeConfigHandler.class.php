<?php

  /**
  * Class that handles integer config values
  *
  * @version 1.0
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  class MultipleObjectTypeConfigHandler extends ConfigHandler {
    
    function render($control_name) {
    	$genid = gen_id();
    	$object_types = ObjectTypes::getAvailableObjectTypes();
    	
    	$value =  $this->getValue();
    	$out = '';
		foreach ( $object_types as $ot ) {
		
			$checked = array_search($ot->getId(), $value) !== false;
			$out .= '<div  class="checkbox-config-option">';
			$out .= label_tag(lang($ot->getName()), $genid.'_'.$control_name.'_'.$ot->getId(), false, array('style' => 'cursor:pointer;'), '');
			$out .= checkbox_field($control_name . '[' . $ot->getId () . ']', $checked, array('id' => $genid.'_'.$control_name.'_'.$ot->getId()));
			$out .= '</div >';
			
		}
		
		$out .= '<input type="hidden" name="' . $control_name . '[0]" value=" "><div class="clear"></div>';
		return $out;
	} // render
    
    
    function rawToPhp($value) {
    	$values = explode(",", $value);
    	foreach ($values as $k => &$val) {
    		if (trim($val) == "") unset($values[$k]);
    	}
    	return $values;
    }
    
    function phpToRaw($value) {
    	if (is_array($value) && count($value)) {
    		unset($value[0]);
    		return implode(',', array_keys($value));
    	}else{
    		return $value;
    	}
    }
    
  } // UserCompanyConfigHandler

?>