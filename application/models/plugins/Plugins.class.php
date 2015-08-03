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
		$this->getActive();
  		foreach ($this->active as $active_plugin) {
  			if ($active_plugin->getName() == $name) return true;
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
