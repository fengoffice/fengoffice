<?php


  /**
  * Plugins
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class Plugins extends BasePlugins {
    
  	var $all  = null ; 
  	
  	var $active = null ; 
    
  	var $current = null ; 
  	
  	
	/**
	 * 
	 */
  	function getAll() {
  		if ( $this->all == null ) {
  			$this->all = $this->findAll(array("conditions" => array(
  				"is_installed = 1 " 			 
  			)));							
  		}
  		return $this->all ;
  	}

	
	/**
	 * Returns the plugin object of a given name.
	 * @param string $name the name of the plugin we search
	 * @return Plugin the plugin object, or null if not exists
	 */
	function getByName($name) {
		return $this->findOne(array("conditions" => array("name = ?", $name)));
	}
  	
	/**
	 * 
	 * 
	 * @return array
	 */
  	function getActive() {
  		if ( $this->active == null ) {
  			$this->active = $this->findAll(array("conditions" => array(" is_installed = 1 AND is_activated = 1"), "order" => "priority"));
  		}
  		return $this->active ;
  	}
  	
  	function isActivePlugin($name) {
  		if (!isset($this) || !$this instanceof Plugins) {
  			return self::instance()->isActivePlugin($name);
  		}
		$this->getActive();
  		foreach ($this->active as $active_plugin) {
  			if ($active_plugin->getName() == $name) return true;
  		}
  		
  		return false;
  	}
  	
  	function isInstalledPlugin($name) {
  		if (!isset($this) || !$this instanceof Plugins) {
  			return self::instance()->isInstalledPlugin($name);
  		}
		$this->getAll();
  		foreach ($this->all as $installed_plugin) {
  			if ($installed_plugin->getName() == $name) return true;
  		}
  		
  		return false;
  	}

  	/**
  	 * 
  	 * 
  	 * @param unknown_type $plugin
  	 */
  	function setCurrent($plugin) {
  		if  ( $plugin instanceof Plugin ) {
  			$this->current = $plugin ;
  		}
  	}
    
  	/**
  	 * 
  	 * 
  	 */
  	function getCurrent() {
  		return $this->current ;
  	}
  	
  	
  } // Plugins 
