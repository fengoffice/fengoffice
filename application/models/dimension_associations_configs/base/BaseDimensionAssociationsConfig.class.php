<?php

  /**
  * BaseDimensionAssociationsConfig class
  */
  abstract class BaseDimensionAssociationsConfig extends DataObject {
  
  	  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
  	/**
    * Return value of 'association_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getAssociationId() {
      return $this->getColumnValue('association_id');
    }
    
    /**
    * Set value of 'association_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setAssociationId($value) {
      return $this->setColumnValue('association_id', $value);
    }
    
    /**
     * Return value of 'config_name' field
     *
     * @access public
     * @param void
     * @return string
     */
    function getConfigName() {
    	return $this->getColumnValue('config_name');
    }
    
    /**
     * Set value of 'config_name' field
     *
     * @access public
     * @param string $value
     * @return boolean
     */
    function setConfigName($value) {
    	return $this->setColumnValue('config_name', $value);
    }
    
    /**
     * Return value of 'type' field
     *
     * @access public
     * @param void
     * @return string
     */
    function getType() {
    	return $this->getColumnValue('type');
    }
    
    /**
     * Set value of 'type' field
     *
     * @access public
     * @param string $value
     * @return boolean
     */
    function setType($value) {
    	return $this->setColumnValue('type', $value);
    }
  	
    /**
    * Return value of 'value' field
    *
    * @access public
    * @param void
    * @return string
    */
    function getValue() {
      return $this->getColumnValue('value');
    }
    
    /**
    * Set value of 'value' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setValue($value) {
      return $this->setColumnValue('value', $value);
    }
      
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return DimensionAssociationsConfigs 
    */
    function manager() {
      if(!($this->manager instanceof DimensionAssociationsConfigs)) $this->manager = DimensionAssociationsConfigs::instance();
      return $this->manager;
    } // manager
  
  } // BaseDimensionAssociationsConfig 

?>