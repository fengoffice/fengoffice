<?php

/**
 * DimensionOptions
 *
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class DimensionOptions extends BaseDimensionOptions {
	
	static function getOptionValue($dimension_id, $name) {
		
		$value = null;
		$option = self::findOne(array('conditions' => array('dimension_id=? AND name=?', $dimension_id, $name)));
		if ($option instanceof DimensionOption) {
			$value = $option->getValue();
		}
		
		return $value;
	}
	
	static function setOptionValue($dimension_id, $name, $value) {
		
		$option = self::findOne(array('conditions' => array('dimension_id=? AND name=?', $dimension_id, $name)));
		if (!$option instanceof DimensionOption) {
			$option = new DimensionOption();
			$option->setDimensionId($dimension_id);
			$option->setName($name);
		}
		$option->setValue($value);
		$option->save();
	}
	
} // DimensionOptions 
