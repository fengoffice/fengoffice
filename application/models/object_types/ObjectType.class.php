<?php

/**
 * ObjectType class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class ObjectType extends BaseObjectType {
	
	function getIconClass($large = false, $trashed = false, $archived = false) {
		$class = "ico-";
		if ($large) $class .= "large-";
		$class .= $this->getIcon();
		if ($trashed) $class .= "-trashed";
		else if ($archived) $class .= "-archived";
		
		return $class;
	}
	
	
	function getArrayInfo($columns = null) {
		$info = array();
		if (is_null($columns)) {
			$columns = $this->getColumns();
			$was_null = true;
		}
		foreach ($columns as $col) {
			$info[$col] = $this->getColumnValue($col);
		}
		if (isset($was_null) && $was_null) $columns = null;
		return $info;
	}

	function getIsLinkableObjectType() {
		$handler_class = $this->getHandlerClass();
		try {
			if (class_exists($handler_class)) {
				eval('$item_class = '.$handler_class.'::instance()->getItemClass();  $instance = new $item_class();');
			}
			return isset($instance) && $instance->isLinkableObject();
		}catch(Exception $e) {
			return false ;
		}
	}
	
	/**
	 * FIXME for Feng 2: Add color attribute for object types and members
	 * Hardcoded color list by object type name
	 */
	function getColor() {
		$color = null;
		switch ($this->getName()) {
			case 'project': $color = 11; break;
			case 'component': $color = 12; break;
			case 'program': $color = 23; break;
			case 'stage': $color = 12; break;
			case 'state': $color = 24; break;
			default: break;
		}
		return $color;
	}
	
	
	
	function getObjectTypeController() {
		$controller = "";
		if ($this->getType() == 'content_object') {
			$controller = $this->getName();
			switch ($this->getName()) {
				case 'weblink': $controller = 'webpage'; break;
				case 'file': $controller = 'files'; break;
			}
		}
		return $controller;
	}
	
	
	function getObjectTypeAddAction() {
		$action = "";
		if ($this->getType() == 'content_object') {
			$action = 'add';
			switch ($this->getName()) {
				case 'task': $action = 'add_task'; break;
				case 'file': $action = 'add_file'; break;
			}
		}
		return $action;
	}


	function getPluralObjectTypeName() {
		return $this->getObjectTypeName(true);
	}
	
	function getObjectTypeName($is_plural = false) {
		$dim_ids = DimensionObjectTypes::getDimensionIdsByObjectTypeId($this->getId());
		if ( is_array($dim_ids) && count($dim_ids) > 0 ) {
		  	$type_name = trim(Members::getTypeNameToShowByObjectType($dim_ids[0], $this->getId(), null, $is_plural));
		} else {
		  	$type_name = lang($this->getName(). ($is_plural ? 's' : ''));
		}
		return $type_name;
	}
	

	/**
	 * Return an array of object properties, including custom properties, system columns, and common columns.
	 * 
	 * @param boolean $include_common_cols If true, include common columns.
	 * @param boolean $include_cp_prefix If true, prefix custom property ids with 'cp_'.
	 * @return array Array of object properties
	 */
	function getObjectTypeProperties($include_common_cols = true, $include_cp_prefix = true, $include_contact_external_properties = false) {
		$all_properties = array();

		// Add custom properties
		$custom_properties = CustomProperties::getAllCustomPropertiesByObjectType($this->getId());
		foreach($custom_properties as $cp){				
			if ($cp->getType() != 'table') {
				$cp_id = $include_cp_prefix ? "cp_" . $cp->getId() : $cp->getId();
				$all_properties[] = array('id' => $cp_id, 'name' => $cp->getName(), 'type' => $cp->getType(), 'values' => $cp->getValues(), 'multiple' => $cp->getIsMultipleValues());
			}
		}

		// Add main object type columns
		$object_columns = array();
		if (class_exists($this->getHandlerClass())) {
			$manager_instance = $this->getHandlerClass()::instance();
			$system_columns = $manager_instance->getSystemColumns();
			
			foreach ($manager_instance->getColumns() as $col_name) {
				if (!in_array($col_name, $system_columns)) {
					$object_columns[$col_name] = $manager_instance->getColumnType($col_name);
				}
			};

			// Add common columns if necessary
			if ($include_common_cols) {
				$common_columns = Objects::instance()->getColumns(false);
				$common_columns = array_diff_key($common_columns, array_flip($system_columns));
				$object_columns = array_merge($object_columns, $common_columns);
			}
		}

		// Format the main object type columns and common columns
		foreach($object_columns as $name => $type){
			if ($this->getName() == 'contact' && $name == 'job_title') continue;
			
			if($type == DATA_TYPE_FLOAT || $type == DATA_TYPE_INTEGER){
				$type = 'numeric';
			}else if($type == DATA_TYPE_STRING){
				$type = 'text';
			}else if($type == DATA_TYPE_BOOLEAN){
				$type = 'boolean';
			}else if($type == DATA_TYPE_DATE || $type == DATA_TYPE_DATETIME){
				$type = 'date';
			}
			
			$field_name = Localization::instance()->lang('field '.$this->getHandlerClass().' '.$name);
			if (is_null($field_name)) $field_name = lang('field Objects '.$name);
			
			$all_properties[] = array('id' => $name, 'name' => $field_name, 'type' => $type);
		}

		if ($this->getName() == 'contact' && $include_contact_external_properties) {
			$all_properties[] = array('id' => 'email', 'name' => lang('email'), 'type' => 'email');
			$all_properties[] = array('id' => 'phone', 'name' => lang('phone'), 'type' => 'phone');
			$all_properties[] = array('id' => 'address', 'name' => lang('address'), 'type' => 'address');
			$all_properties[] = array('id' => 'website', 'name' => lang('website'), 'type' => 'website');
		}
		if ($this->getName() == 'contact') {
			$job_title_cp = CustomProperties::getCustomPropertyByCode($this->getId(), 'job_title');
			if (!$job_title_cp) {
				$all_properties[] = array('id' => 'job_title', 'name' => lang('job_title'), 'type' => 'text');
			}
		}

		// Sort the properties
		usort($all_properties, function($a, $b) {
			return strcmp($a['name'], $b['name']);
		});

		return $all_properties;
	}
	
} // ObjectType

?>