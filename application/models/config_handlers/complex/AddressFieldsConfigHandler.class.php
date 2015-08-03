<?php
class AddressFieldsConfigHandler extends ConfigHandler {
	
	/**
	 * Render form control
	 *
	 * @param string $control_name        	
	 * @return string
	 */
	function render($control_name) {
		$value = $this->getValue();
		
		$fields = array('street', 'city', 'state', 'zip_code', 'country');
		
		$out = '';
		foreach ( $fields as $field ) {
			$checked = array_search ($field, $value) !== false;
			
			$out .= '<div class="checkbox-config-option">';
			$out .= label_tag(lang($field));
			$out .= checkbox_field($control_name . '[' . $field . ']', $checked);
			$out .= '</div>';
		}
		$out .= '<div class="clear"></div>';
		
		return $out;
	}
	
	/**
	 * Convert raw value to php
	 *
	 * @param string $value        	
	 * @return mixed
	 */
	function rawToPhp($value) {
		$tmp = explode ( ",", $value );
		$res = array ();
		foreach ( $tmp as $val ) {
			if (trim ( $val ) != "") {
				$res [] = $val;
			}
		}
		return $res;
	}
	
	
	function phpToRaw($value) {
		if (is_array ( $value ) && count ( $value )) {
			unset ( $value [0] );
			return implode ( ',', array_keys ( $value ) );
		} else {
			return $value;
		}
	}
}
  
  
  
  