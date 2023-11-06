<?php

/**
 * DimensionObjectTypeOptions
 *
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class DimensionObjectTypeOptions extends BaseDimensionObjectTypeOptions {
	
	static function getOptionValue($dimension_id, $object_type_id, $name) {
		
		$value = null;
		$option = self::instance()->findOne(array('conditions' => array('dimension_id=? AND object_type_id=? AND name=?', $dimension_id, $object_type_id, $name)));
		if ($option instanceof DimensionObjectTypeOption) {
			$value = $option->getValue();
		}
		
		return $value;
	}
	
	static function setOptionValue($dimension_id, $object_type_id, $name, $value) {
		
		$option = self::instance()->findOne(array('conditions' => array('dimension_id=? AND object_type_id=? AND name=?', $dimension_id, $object_type_id, $name)));
		if (!$option instanceof DimensionObjectTypeOption) {
			$option = new DimensionObjectTypeOption();
			$option->setDimensionId($dimension_id);
			$option->setObjectTypeId($object_type_id);
			$option->setName($name);
		}
		$option->setValue($value);
		$option->save();
	}
	
	
	static function getOptionValuesForAllObjectTypes($dimension_id, $name) {
	
		return self::instance()->findAll(array('conditions' => array('dimension_id=? AND name=?', $dimension_id, $name)));
		
	}
	
} // DimensionObjectTypeOptions 
