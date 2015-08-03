<?php

  /**
  * Class that handles integer config values
  *
  * @version 1.0
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  class UserTypeMultipleConfigHandler extends ConfigHandler {
    
    function render($control_name) {
    	$genid = gen_id();
    	$groups = PermissionGroups::findAll(array('conditions' => "`type`='roles' AND `parent_id`>0"));
    	
    	$value =  $this->getValue();
    	$out = '';
		foreach ( $groups as $group ) { /* @var $dim Dimension */
		
			$checked = array_search($group->getId(), $value) !== false;
			$out .= '<div class="checkbox-config-option">';
			$out .= label_tag($group->getName(), $genid.'_'.$control_name.'_'.$group->getId(), false, array('style' => 'cursor:pointer;'), '');
			$out .= checkbox_field($control_name . '[' . $group->getId () . ']', $checked, array('id' => $genid.'_'.$control_name.'_'.$group->getId()));
			$out .= '</div >';
			
		}
		
		$out .= '<input type="hidden" name="' . $control_name . '[0]" value=" "><div class="clear"></div>';
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
    
  } // UserCompanyConfigHandler

?>