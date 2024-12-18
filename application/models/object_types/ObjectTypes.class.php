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
  	
	/**
	* Return all status available for the object type
	* @var int
	* @return array
	*/
	static function getObjectStatusTypes(int $object_type_id) {

		$sql = "
				SELECT *
				FROM ".TABLE_PREFIX."object_status_types 
				WHERE object_type_id = $object_type_id
				";

		$rows = DB::executeAll($sql);

		return $rows;

	}

  	static function getAllObjectTypes($external_conditions = "") {
  		$object_types = self::instance()->findAll(array(
  			"conditions" => "IF(plugin_id IS NULL OR plugin_id=0, true, (SELECT p.is_activated FROM ".TABLE_PREFIX."plugins p WHERE p.id=plugin_id) = true) $external_conditions"
  		));
  		return $object_types;
  	}
  	
  	/**
  	 * @param unknown_type $external_conditions
  	 */
	static function getAvailableObjectTypesWithDimensionObjects($external_conditions = "") {
		$object_types = self::instance()->findAll(array(
			"conditions" => "`type` IN ('content_object', 'dimension_object') AND 
			`name` <> 'file revision' AND name <> 'template_task' AND name <> 'template_milestone'  AND 
			IF(plugin_id IS NULL OR plugin_id=0, true, (SELECT p.is_activated FROM ".TABLE_PREFIX."plugins p WHERE p.id=plugin_id) = true) $external_conditions"
		));
		return $object_types;
	}
  	
  	/**
  	 * @param unknown_type $external_conditions
  	 */
	static function getAvailableObjectTypes($external_conditions = "") {
		$object_types = self::instance()->findAll(array(
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
		$object_types = self::instance()->findAll(array(
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
			$ots = self::instance()->findAll();
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
				$ots = self::instance()->findAll();
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
	
	static function getPluralObjectTypeName($object_type_id) {
		$ot = ObjectTypes::instance()->findById($object_type_id);
		if ($ot instanceof ObjectType) {
			return $ot->getPluralObjectTypeName();
		}
		return '';
	}
	
	
	/**
	 * Get the SQL condition for selecting the object types that are listable.
	 * 
	 * @param string $extra_conditions Extra conditions to add to the SQL query.
	 * @param boolean $show_dim_members Whether to include dimension members in the results.
	 * @return string The SQL condition.
	 */
	static function getListableObjectsSqlCondition($extra_conditions = "", $show_dim_members = false) {
		// The object types that are listable
		$ot_types = "'content_object', 'comment', 'located'";
		
		// If the user wants to include dimension members, add them to the list of object types
		if ($show_dim_members) {
			$ot_types .= ", 'dimension_group', 'dimension_object'";
		}
		
		// Build the SQL query
		$sql = "
				SELECT DISTINCT(id) as id  
				FROM ".TABLE_PREFIX."object_types ot
				WHERE ot.type IN ($ot_types) 
				AND (
				  ot.plugin_id IS NULL OR ot.plugin_id = 0 OR 
				  ot.plugin_id IN (
				    SELECT id FROM ".TABLE_PREFIX."plugins WHERE is_activated > 0 AND is_installed > 0 
				  )
				)
				$extra_conditions
		";
		
		// Execute the query and get the IDs of the object types
		$rows = DB::executeAll($sql);
		$ids = [];
		foreach($rows as $k=>$v){
			$ids[]=$rows[$k]['id'];
		}
		
		// Build the final SQL condition
		$sql = "o.object_type_id IN (".implode(",",$ids).")";
		
		return $sql;
	}
    
  } // ObjectTypes 

?>