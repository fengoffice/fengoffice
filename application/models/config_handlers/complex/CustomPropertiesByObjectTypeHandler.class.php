<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CustomPropertiesByObjectTypeHandler
 *
 * @author Matias
 */
class CustomPropertiesByObjectTypeHandler extends ConfigHandler {
    
    
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
        
        $class = $this->getConfigOption()->getOptions();
        $idObject = ObjectTypes::findByName($class)->getId();
        $propList = CustomProperties::getAllCustomPropertiesByObjectType($idObject);
        $value = $this->getValue();
        
        $out = '';
        foreach ($propList as $cp) {
            $checked = array_search($cp->getId(), $value) !== false;
            
            $attr = array('id' => 'cp_' . $cp->getId(), 'value' => $cp->getId());

            $out .= '<div class="custom_properties" >';
            $out .= checkbox_field($control_name . '[' . $cp->getId() . ']', $checked, $attr);
            $out .= label_tag($cp->getName(), 'cp_' . $cp->getId(), false, null, '');
            $out .= '</div >';
        }

        $out .= '<input type="hidden" name="' . $control_name . '[0]" value=" ">';
        return $out;
    }
    
    function rawToPhp($value) {
      return explode(",", $value);
    }
}
