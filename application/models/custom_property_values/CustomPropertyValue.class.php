<?php

/**
 * CustomPropertyValue class
 */
class CustomPropertyValue extends BaseCustomPropertyValue {

	/**
	 * Construct the object
	 *
	 * @param void
	 * @return null
	 */
	function __construct() {
		parent::__construct();
	} // __construct

	/**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return null
	 */
	function validate(&$errors) {
		$cp = CustomProperties::getCustomProperty($this->getCustomPropertyId());
		if($cp instanceof CustomProperty){
			if($cp->getIsRequired() && ($this->getValue() == '')){
				$errors[] = lang('custom property value required', $cp->getName());
			}
			if($cp->getType() == 'numeric'){
				if($cp->getIsMultipleValues()){
					foreach(explode(',', $this->getValue()) as $value){
						if($value != '' && !is_numeric($value)){
							$errors[] = lang('value must be numeric', $cp->getName());
						}
					}
				}else{
					if($this->getValue() != '' && !is_numeric($this->getValue())){
						$errors[] = lang('value must be numeric', $cp->getName());
					}
				}
			}
		}//if
	} // validate


} // ObjectPropertyValue

?>