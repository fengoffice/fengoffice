<?php

/**
 * DimensionObjectTypeOptions
 *
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class DimensionObjectTypeOptions extends BaseDimensionObjectTypeOptions {
	
	static function getOptionValue($dimension_id, $object_type_id, $name, $object_subtype_id = null) {
		$value = null;
		$option = null;
		if ($object_subtype_id != null) {
		    if (strpos($object_subtype_id, 'ostId_') === 0 && Plugins::instance()->isActivePlugin('object_subtypes')) {
        		$object_subtype_id = intval(substr($object_subtype_id, strlen('ostId_')));
			$option = self::instance()->findOne(array('conditions' => array('dimension_id=? AND object_type_id=? AND object_subtype_id=? AND name=?', $dimension_id, $object_type_id, $object_subtype_id, $name.'_ostId_'.$object_subtype_id)));
   			}
		} else {
			$option = self::instance()->findOne(array('conditions' => array('dimension_id=? AND object_type_id=? AND name=?', $dimension_id, $object_type_id, $name)));
		}	
		if ($option instanceof DimensionObjectTypeOption) {
			$value = $option->getValue();
		}
		
		return $value;
	}


	static function setOptionValue($dimension_id, $object_type_id, $name, $value) {
		$object_subtype_id = '';
		if (strpos($object_type_id, 'ostId_') === 0) {
			if (!Plugins::instance()->isActivePlugin('object_subtypes')) {
				return;
			}
			//if this is the case,the object type is in deed an object subtype
        	$object_subtype_id = intval(substr($object_type_id, strlen('ostId_')));
			$subtype = ObjectSubTypes::instance()->findOne(array('conditions' => array('id=?', $object_subtype_id)));
			if (!$subtype instanceof ObjectSubtype) {
				return;
			}
        	$object_type_id = $subtype->getObjectTypeId();
			$option = self::instance()->findOne(array('conditions' => array('dimension_id=? AND object_type_id=? AND object_subtype_id=? AND name=?', $dimension_id, $object_type_id, $object_subtype_id, $name.'_ostId_'.$object_subtype_id)));
   		} else {
			$option = self::instance()->findOne(array('conditions' => array('dimension_id=? AND object_type_id=? AND name=?', $dimension_id, $object_type_id, $name)));
		}

		if (!$option instanceof DimensionObjectTypeOption) {
			$option = new DimensionObjectTypeOption();
			$option->setDimensionId($dimension_id);
			$option->setObjectTypeId($object_type_id);
			$option->setObjectSubTypeId($object_subtype_id);
			if ($object_subtype_id != '') {
				$option->setName($name.'_ostId_'.$object_subtype_id);
			} else {
				$option->setName($name);
			}
			
		}		
		$option->setValue($value);
		$option->save();
	}
	
	
	static function getOptionValuesForAllObjectTypes($dimension_id, $name) {
	
		return self::instance()->findAll(array('conditions' => array('dimension_id=? AND name=?', $dimension_id, $name)));
		
	}
	
} // DimensionObjectTypeOptions 
