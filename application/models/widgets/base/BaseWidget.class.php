<?php abstract class BaseWidget extends DataObject {
	
	function getObjectId() {
		return $this->getColumnValue ( 'object_id' );
	}
	

	function manager() {
		if (! ($this->manager instanceof Widgets))
			$this->manager = Widgets::instance ();
		return $this->manager;
	}
	
	function getName() {
		return $this->getColumnValue ( 'name' );
	}
	
	function setName($name) {
		$this->setColumnValue ( 'name', $name );
	}
	
	function getTitle() {
		return $this->getColumnValue ( 'title' );
	}
	
	function setTitle($title) {
		$this->setColumnValue ( 'title', $title );
	}
	
	function getPluginId() {
		return $this->getColumnValue ( 'plugin_id' );
	}
	
	function setPluginId($value) {
		$this->setColumnValue ( 'plugin_id', $value );
	}
	
	function getPath() {
		return $this->getColumnValue ( 'path' );
	}
	
	function setPath($value) {
		$this->setColumnValue ( 'path', $value );
	}
	
	function getDefaultOrder() {
		return $this->getColumnValue ( 'default_order' );
	}
	
	function setDefaultOrder($value) {
		return $this->setColumnValue ( 'default_order', $value );
	}
	
	function getDefaultSection() {
		return $this->getColumnValue ( 'default_section' );
	}
	
	function setDefaultSection($value) {
		return $this->setColumnValue ( 'default_section', $value );
	}
	
	function getIconCls() {
		return $this->getColumnValue ( 'icon_cls' );
	}
	
	function setIconCls($value) {
		return $this->setColumnValue ( 'icon_cls', $value );
	}

} 