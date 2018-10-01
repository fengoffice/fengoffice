<?php
/**
 * Created by PhpStorm.
 * User: Damian
 * Date: 17/04/2018
 */

class ContactPropertySelectorConfigHandler extends ConfigHandler
{

    /**
     * Render form control
     *
     * @param string $control_name
     * @return string
     */
    function render($control_name)
    {

        $class = $this->getConfigOption()->getOptions();
        $idObject = ObjectTypes::findByName($class)->getId();
        $handler_class = ObjectTypes::findByName($class)->getHandlerClass();
        eval('$managerInstance = ' . $handler_class . "::instance();");
        //$prop_list = $managerInstance->getColumns();
        $cp_prop_list = CustomProperties::getAllCustomPropertiesByObjectType($idObject);
        $value = $this->getValue();

        $out = '';

        if($class == 'contact'){
            $checked = array_search('email', $value) !== false;

            $attr = array('id' => 'email', 'value' => 'email' ,'style'=>'display:  inline-block;vertical-align:  middle;margin-right: 5px;');
            $attr2 = array('style'=>'display:  inline-block;vertical-align:  middle;margin-right: 5px;');
            $out .= '<div class="properties" >';
            $out .= checkbox_field($control_name . '[' . 'email' . ']', $checked, $attr);
            $out .= label_tag(lang('email'), 'email', false, $attr2, '');
            $out .= '</div >';
        }

        /*
         * This code add all properties of a object
                foreach ($prop_list as $prop) {
                    $checked = array_search($prop, $value) !== false;

                    $attr = array('id' => $prop, 'value' => $prop);

                    $out .= '<div class="properties" >';
                    $out .= checkbox_field($control_name . '[' . $prop . ']', $checked, $attr);
                    $out .= label_tag(lang($prop), $prop, false, null, '');
                    $out .= '</div >';
                }
        */

        foreach ($cp_prop_list as $cp) {
            if($cp->getType() == 'text' | $cp->getType() == 'numeric' | ($cp->getType() == 'list' && $cp->getIsMultipleValues() == 0)){
                $checked = array_search($cp->getId(), $value) !== false;

                $attr = array('id' => 'cp_' . $cp->getId(), 'value' => $cp->getId(),'style'=>'display:  inline-block;vertical-align:  middle;margin-right: 5px;');
                $attr2 = array('style'=>'display:  inline-block;vertical-align:  middle;margin-right: 5px;');

                $out .= '<div class="custom_properties" >';
                $out .= checkbox_field($control_name . '[' . $cp->getId() . ']', $checked, $attr);
                $out .= label_tag($cp->getName(), 'cp_' . $cp->getId(), false, $attr2, '');
                $out .= '</div >';
            }
        }

        $out .= '<input type="hidden" name="' . $control_name . '[0]" value=" ">';
        return $out;
    }

    function rawToPhp($value)
    {
        return explode(",", $value);
    }

    function setValue($value)
    {
        $value = implode(",",$value);
        return parent::setValue($value);
    }
}