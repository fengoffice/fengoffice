<?php

  /**
  * BaseDimension class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseDimension extends DataObject {
  
  	  
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
    * Return value of 'code' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getCode() {
      return $this->getColumnValue('code');
    } // getCode()
    
    /**
    * Set value of 'code' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setCode($value) {
      return $this->setColumnValue('code', $value);
    } // setCode()
    
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
    * Return value of 'is_root' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getIsRoot() {
      return $this->getColumnValue('is_root');
    } // getIsRoot()
    
    /**
    * Set value of 'is_root' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setIsRoot($value) {
      return $this->setColumnValue('is_root', $value);
    } // setIsRoot() 
    
    function getIsDefault() {
    	return $this->getColumnValue('is_default');
    }
    
    function setIsDefault($value) {
    	$this->setColumnValue("is_default", $value);
    }
    
    
  	function getIsRequired() {
    	return $this->getColumnValue('is_required');
    }
    
    function setIsRequired($value) {
    	$this->setColumnValue("is_required", $value);
    }
    
    /**
    * Return value of 'is_manageable' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getIsManageable() {
      return $this->getColumnValue('is_manageable');
    } // getIsManageable()
    
    /**
    * Set value of 'is_manageable' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setIsManageable($value) {
      return $this->setColumnValue('is_manageable', $value);
    } // setIsManageable() 
    
    /**
    * Return value of 'allows_multiple_selection' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getAllowsMultipleSelection() {
      return $this->getColumnValue('allows_multiple_selection');
    } // getAllowsMultipleSelection()
    
    /**
    * Set value of 'allows_multiple_selection' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setAllowsMultipleSelection($value) {
      return $this->setColumnValue('allows_multiple_selection', $value);
    } // setAllowsMultipleSelection() 
    
    /**
    * Return value of 'defines_permissions' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getDefinesPermissions() {
      return $this->getColumnValue('defines_permissions');
    } // getDefinesPermissions()
    
    /**
    * Set value of 'defines_permissions' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setDefinesPermissions($value) {
      return $this->setColumnValue('defines_permissions', $value);
    } // setDefinesPermissions() 
    
    /**
    * Return value of 'is_system' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getIsSystem() {
      return $this->getColumnValue('is_system');
    } // getIsSystem()
    
    /**
    * Set value of 'is_system' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setIsSystem($value) {
      return $this->setColumnValue('is_system', $value);
    } // setIsSystem()    
    
    
    function getDefaultOrder() {
      return $this->getColumnValue('default_order');
    }
    
    function setDefaultOrder($value) {
      return $this->setColumnValue('default_order', $value);
    }
    
    
    /**
    * Return value of 'permission_query_method' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getPermissionQueryMethod() {
      return $this->getColumnValue('permission_query_method');
    } // getPermissionQueryMethod()
    
    /**
    * Set value of 'permission_query_method' field
    *
    * @access public   
    * @param string $value
    * @return string
    */
    function setPermissionQueryMethod($value) {
      return $this->setColumnValue('permission_query_method', $value);
    } // setPermissionQueryMethod()
    
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return Dimensions 
    */
    function manager() {
      if(!($this->manager instanceof Dimensions)) $this->manager = Dimensions::instance();
      return $this->manager;
    } // manager
  
  } // BaseDimension 

?>