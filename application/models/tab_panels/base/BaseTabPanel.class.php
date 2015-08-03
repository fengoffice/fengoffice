<?php

  /**
  * BaseTabPanel class
  *
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  abstract class BaseTabPanel extends DataObject {
  
  	  
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
    * Return value of 'title' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getTitle() {
      return $this->getColumnValue('title');
    } // getTitle()
    
    /**
    * Set value of 'title' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setTitle($value) {
      return $this->setColumnValue('title', $value);
    } // setTitle()
    
    /**
    * Return value of 'icon_cls' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getIconCls() {
      return $this->getColumnValue('icon_cls');
    } // getIconCls()
    
    /**
    * Set value of 'icon_cls' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setIconCls($value) {
      return $this->setColumnValue('icon_cls', $value);
    } // setIconCls() 
    
    
    /**
    * Return value of 'refresh_on_context_change' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getRefreshOnContextChange() {
      return $this->getColumnValue('refresh_on_context_change');
    } // getRefreshOnContextChange()
        
    /**
    * Set value of 'refresh_on_context_change' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setRefreshOnContextChange($value) {
      return $this->setColumnValue('refresh_on_context_change', $value);
    } // setRefreshOnContextChange() 
    
    
    /**
    * Return value of 'default_controller' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getDefaultController() {
      return $this->getColumnValue('default_controller');
    } // getDefaultController()
        
    /**
    * Set value of 'default_controller' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setDefaultController($value) {
      return $this->setColumnValue('default_controller', $value);
    } // setDefaultController()

    /**
    * Return value of 'default_action' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getDefaultAction() {
      return $this->getColumnValue('default_action');
    } // getDefaultAction()
        
    /**
    * Set value of 'default_action' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setDefaultAction($value) {
      return $this->setColumnValue('default_action', $value);
    } // setDefaultAction()
    
    
    /**
    * Return value of 'initial_controller' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getInitialController() {
      return $this->getColumnValue('initial_controller');
    } // getInitialController()
        
    /**
    * Set value of 'initial_controller' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setInitialController($value) {
      return $this->setColumnValue('initial_controller', $value);
    } // setInitialController()

    /**
    * Return value of 'initial_action' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getInitialAction() {
      return $this->getColumnValue('initial_action');
    } // getInitialAction()
        
    /**
    * Set value of 'initial_action' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setInitialAction($value) {
      return $this->setColumnValue('initial_action', $value);
    } // setInitialAction()
    
    /**
    * Return value of 'enabled' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getEnabled() {
      return $this->getColumnValue('enabled');
    } // getEnabled()
        
    /**
    * Set value of 'enabled' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setEnabled($value) {
      return $this->setColumnValue('enabled', $value);
    } // setEnabled()
    
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
    * Return value of 'ordering' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getOrdering() {
      return $this->getColumnValue('ordering');
    } // getOrdering()
        
    /**
    * Set value of 'ordering' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setOrdering($value) {
      return $this->setColumnValue('ordering', $value);
    } // setOrdering()
    
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
    * Return value of 'object_type_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getObjectTypeId() {
      return $this->getColumnValue('object_type_id');
    } // getObjectTypeId()
        
    /**
    * Set value of 'object_type_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setObjectTypeId($value) {
      return $this->setColumnValue('object_type_id', $value);
    } // setObjectTypeId()
         
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return TabPanels 
    */
    function manager() {
      if(!($this->manager instanceof TabPanels)) $this->manager = TabPanels::instance();
      return $this->manager;
    } // manager

  
  } // BaseTabPanel 

?>