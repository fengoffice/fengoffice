<?php

  /**
  * BaseContactDimensionPermission class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseContactDimensionPermission extends DataObject {
  
  	  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
    /**
    * Return value of 'permission_group_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getPermissionGroupId() {
      return $this->getColumnValue('permission_group_id');
    } // getId()
    
    /**
    * Set value of 'permission_group_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setPermissionGroupId($value) {
      return $this->setColumnValue('permission_group_id', $value);
    } // setId() 
    
    /**
    * Return value of 'dimension_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getContactDimensionId() {
      return $this->getColumnValue('dimension_id');
    } // getCode()
    
    /**
    * Set value of 'dimension_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setContactDimensionId($value) {
      return $this->setColumnValue('dimension_id', $value);
    } // setCode()
    
    /**
    * Return value of 'permission_type' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getPermissionType() {
      return $this->getColumnValue('permission_type');
    } // getName()
    
    /**
    * Set value of 'permission_type' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setPermissionType($value) {
      return $this->setColumnValue('permission_type', $value);
    } // setName() 

    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ContactDimensionPermissions 
    */
    function manager() {
      if(!($this->manager instanceof ContactDimensionPermissions)) $this->manager = ContactDimensionPermissions::instance();
      return $this->manager;
    } // manager
  
  } // BaseContactDimensionPermission 

?>