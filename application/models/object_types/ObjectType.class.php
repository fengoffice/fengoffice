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
			eval('$item_class = '.$handler_class.'::instance()->getItemClass();  $instance = new $item_class();');
			return $instance && $instance->isLinkableObject();
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
	
} // ObjectType

?>