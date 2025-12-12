<?php

/**
 * Class ObjectTypePropertiesConfigHandler
 * Handles the rendering and transformation of configuration controls for multiple object types.
 */
class ObjectTypePropertiesConfigHandler extends ConfigHandler {

    /**
     * Renders the configuration control.
     *
     * @param string $control_name The name of the control.
     * @return string The rendered control HTML.
     */
    function render($control_name) {
        $genid = gen_id();
        $object_types = ObjectTypes::getAvailableObjectTypesWithTimeslots();

        // Get the object type ID from the config option
        $options = json_decode($this->getConfigOption()->getOptions(), true);
		
		$ot_id = array_var($options, 'ot');
		$include_common_cols = array_var($options, 'include_common_cols', false);

        $object_type = ObjectTypes::instance()->findById($ot_id);
        if (!$object_type) {
            return '';
        }

        // Get the object type properties
        $object_type_properties = $object_type->getObjectTypeProperties($include_common_cols, true, true);

        // Get the current value
        $selected_properties = $this->getValue();

        // Render the control
        $out = '';
        foreach ($object_type_properties as $prop) {

            $input_id = $genid . '_' . $control_name . '_' . $prop['id'];
            $input_name = $control_name . '[' . $prop['id'] . ']';

            // Check if the property is selected
            $checked = array_search($prop['id'], $selected_properties) !== false;

            // Render the checkbox
            $out .= '<div class="checkbox-config-option">';
            $out .= label_tag($prop['name'], $input_id, false, array('style' => 'cursor:pointer;'), '');
            $out .= checkbox_field($input_name, $checked, array('id' => $input_id));
            $out .= '</div>';
        }

        // Add a hidden field to ensure the value is sent even if nothing is selected
        $out .= '<input type="hidden" name="' . $control_name . '[0]" value=" "><div class="clear"></div>';
        return $out;
    }

    /**
     * Transforms a raw string value to a PHP array.
     *
     * @param string $value The raw string value.
     * @return array The transformed PHP array.
     */
    function rawToPhp($value) {
        $values = explode(",", $value);
        foreach ($values as $k => &$val) {
            if (trim($val) == "") unset($values[$k]);
        }
        return $values;
    }

    /**
     * Transforms a PHP array to a raw string value.
     *
     * @param array $value The PHP array.
     * @return string The transformed raw string.
     */
    function phpToRaw($value) {
        if (is_array($value) && count($value)) {
            unset($value[0]);
            return implode(',', array_keys($value));
        } else {
            return $value;
        }
    }
}
