<?php

  /**
  * ObjectTypes
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class ObjectTypes extends BaseObjectTypes {
  	
  	private static $object_types_by_name = array();
  	private static $object_types_by_id = array();
  	
  	/**
  	 * Request-Level cache  
  	 * @var array
  	 */
  	static $listableObjectTypesIds = null ;
  	
  	static function getAllObjectTypes($external_conditions = "") {
  		$object_types = self::findAll(array(
  				"conditions" => "IF(plugin_id IS NULL OR plugin_id=0, true, (SELECT p.is_activated FROM ".TABLE_PREFIX."plugins p WHERE p.id=plugin_id) = true) AND
			`id` NOT IN (SELECT `object_type_id` FROM ".TabPanels::instance()->getTableName(true)." WHERE `enabled` = 0) $external_conditions"
  		));
  		return $object_types;
  	}
  	
  	/**
  	 * @param unknown_type $external_conditions
  	 */
	static function getAvailableObjectTypesWithDimensionObjects($external_conditions = "") {
		$object_types = self::findAll(array(
			"conditions" => "`type` IN ('content_object', 'dimension_object') AND 
			`name` <> 'file revision' AND name <> 'template_task' AND name <> 'template_milestone'  AND 
			IF(plugin_id IS NULL OR plugin_id=0, true, (SELECT p.is_activated FROM ".TABLE_PREFIX."plugins p WHERE p.id=plugin_id) = true) AND
			`id` NOT IN (SELECT `object_type_id` FROM ".TabPanels::instance()->getTableName(true)." WHERE `enabled` = 0) $external_conditions"
		));
		return $object_types;
	}
  	
  	/**
  	 * @param unknown_type $external_conditions
  	 */
	static function getAvailableObjectTypes($external_conditions = "") {
		$object_types = self::findAll(array(
			"conditions" => "`type` = 'content_object' AND 
			`name` <> 'file revision' AND name <> 'template_task' AND name <> 'template_milestone'  AND 
			IF(plugin_id IS NULL OR plugin_id=0, true, (SELECT p.is_activated FROM ".TABLE_PREFIX."plugins p WHERE p.id=plugin_id) = true) AND
			`id` NOT IN (SELECT `object_type_id` FROM ".TabPanels::instance()->getTableName(true)." WHERE `enabled` = 0) $external_conditions"
		));
		return $object_types;
	}
	
  	/**
  	 * @param unknown_type $external_conditions
  	 */
	static function getAvailableObjectTypesWithTimeslots($external_conditions = "") {
		$object_types = self::findAll(array(
			"conditions" => "`type` IN ('content_object', 'located') AND 
			`name` <> 'file revision' AND name <> 'template_task' AND name <> 'template_milestone' AND `name` <> 'template' AND 
			IF(plugin_id IS NULL OR plugin_id=0, true, (SELECT p.is_activated FROM ".TABLE_PREFIX."plugins p WHERE p.id=plugin_id) = true) AND
			`id` NOT IN (SELECT `object_type_id` FROM ".TabPanels::instance()->getTableName(true)." WHERE `enabled` = 0) $external_conditions"
		));
		return $object_types;
	}
	
	static function isListableObjectType($otid) {
		$listableTypes = self::getListableObjectTypeIds();
		return (!empty($listableTypes[$otid]));
	}
	
	static function getListableObjectTypeIds() {
 		if (is_null(self::$listableObjectTypesIds)) {
			$ids = array(); 
			$sql = "
				SELECT DISTINCT(id) as id  
				FROM ".TABLE_PREFIX."object_types 
				WHERE type IN ('content_object', 'dimension_object', 'comment') AND (
					plugin_id IS NULL OR 
					plugin_id = 0 OR 
					plugin_id IN ( 
						SELECT id FROM ".TABLE_PREFIX."plugins WHERE is_activated > 0 AND is_installed > 0 
					)
				)";
				
			$rows = DB::executeAll($sql);
			foreach ($rows as $row) {
				$ids[array_var($row, 'id')] = array_var($row, 'id');
			}
			self::$listableObjectTypesIds = $ids ;
 		}
		return self::$listableObjectTypesIds;
	}
	
	static function findByName($name) {
		$ot = array_var(self::$object_types_by_name, $name);
		if (!$ot instanceof ObjectType) {
			// cache all object types, they are very few
			$ots = self::findAll();
			foreach ($ots as $ot) {
				self::$object_types_by_name[$ot->getName()] = $ot;
			}
			$ot = array_var(self::$object_types_by_name, $name);
		}
		return $ot;
	}

	function findById($id, $force_reload = false) {
		if(isset($this) && instance_of($this, 'ObjectTypes')) {
			$ot = array_var(self::$object_types_by_id, $id);
			if (!$ot instanceof ObjectType) {
				// cache all object types, they are very few
				$ots = self::findAll();
				foreach ($ots as $ot) {
					self::$object_types_by_id[$ot->getId()] = $ot;
				}
				$ot = array_var(self::$object_types_by_id, $id);
			}
			return $ot;
		} else {
			return ObjectTypes::instance()->findById($id, $force_reload);
		}
	}
	
	
	
	static function getListableObjectsSqlCondition($extra_conditions = "") {
		
		$sql = "
			EXISTS (
				SELECT DISTINCT(id) as id  
				FROM ".TABLE_PREFIX."object_types ot
				WHERE type IN ('content_object', 'dimension_object', 'comment') 
				AND (
				  plugin_id IS NULL OR plugin_id = 0 OR 
				  plugin_id IN (
				    SELECT id FROM ".TABLE_PREFIX."plugins WHERE is_activated > 0 AND is_installed > 0 
				  )
				)
				$extra_conditions
			)
		";
		
		return $sql;
	}
    
  } // ObjectTypes 

?>