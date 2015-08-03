<?php

  /**
  * BaseObjectType class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseObjectType extends DataObject {
  
  	  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
    /**
    * Return value of 'id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getId() {
      return $this->getColumnValue('id');
    } // getId()
    
    /**
    * Set value of 'id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setId($value) {
      return $this->setColumnValue('id', $value);
    } // setId() 
    
   
    /**
    * Return value of 'name' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getName() {
      return $this->getColumnValue('name');
    } // getName()
    
    /**
    * Set value of 'name' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setName($value) {
      return $this->setColumnValue('name', $value);
    } // setName() 
    
    /**
    * Return value of 'handler_class' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getHandlerClass() {
      return $this->getColumnValue('handler_class');
    } // getHandlerClass()
    
    /**
    * Set value of 'handler_class' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setHandlerClass($value) {
      return $this->setColumnValue('handler_class', $value);
    } // setHandlerClass() 
    
    /**
    * Return value of 'table_name' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getTableName() {
      return $this->getColumnValue('table_name');
    } // getTableName()
    
    /**
    * Set value of 'table_name' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setTableName($value) {
      return $this->setColumnValue('table_name', $value);
    } // setTableName() 
    
    /**
    * Return value of 'type' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getType() {
      return $this->getColumnValue('type');
    } // getType()
    
    /**
    * Set value of 'type' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setType($value) {
      return $this->setColumnValue('type', $value);
    } // setType() 

    /**
    * Return value of 'icon' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getIcon() {
      return $this->getColumnValue('icon');
    } // getIcon()
    
    /**
    * Set value of 'icon' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setIcon($value) {
      return $this->setColumnValue('icon', $value);
    } // setIcon() 
    
    /**
    * Return value of 'plugin_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getPluginId() {
      return $this->getColumnValue('plugin_id');
    } // getPluginId()
    
    /**
    * Set value of 'plugin_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setPluginId($value) {
      return $this->setColumnValue('plugin_id', $value);
    } // setPluginId() 
    
    
    /**
     * Return value of 'uses_order' field
     *
     * @access public
     * @param void
     * @return bool
     */
    function getUsesOrder() {
    	return $this->getColumnValue('uses_order');
    } // getUsesOrder()
    
    /**
     * Set value of 'uses_order' field
     *
     * @access public
     * @param bool $value
     * @return boolean
     */
    function setUsesOrder($value) {
    	return $this->setColumnValue('uses_order', $value);
    } // setUsesOrder()
    
  
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ObjectTypes 
    */
    function manager() {
      if(!($this->manager instanceof ObjectTypes)) $this->manager = ObjectTypes::instance();
      return $this->manager;
    } // manager
  
  } // BaseObjectType 

?>