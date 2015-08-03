<?php

  /**
  * BaseContactMemberPermission class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseContactMemberPermission extends DataObject {
  
  	  
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
    } // getPermissionGroupId()
    
    /**
    * Set value of 'permission_group_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setPermissionGroupId($value) {
      return $this->setColumnValue('permission_group_id', $value);
    } // setPermissionGroupId() 
    
    /**
    * Return value of 'member_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getMemberId() {
      return $this->getColumnValue('member_id');
    } // getMemberId()
    
    /**
    * Set value of 'member_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setMemberId($value) {
      return $this->setColumnValue('member_id', $value);
    } // setMemberId()
    
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
    * Return value of 'can_write' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getCanWrite() {
      return $this->getColumnValue('can_write');
    } // getCanWrite()
    
    /**
    * Set value of 'can_write' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setCanWrite($value) {
      return $this->setColumnValue('can_write', $value);
    } // setCanWrite() 
    
    
    /**
    * Return value of 'can_delete' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getCanDelete() {
      return $this->getColumnValue('can_delete');
    } // getCanDelete()
    
    
    /**
    * Set value of 'can_delete' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setCanDelete($value) {
      return $this->setColumnValue('can_delete', $value);
    } // setCanDelete() 
    
        
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ContactMemberPermissions 
    */
    function manager() {
      if(!($this->manager instanceof ContactMemberPermissions)) $this->manager = ContactMemberPermissions::instance();
      return $this->manager;
    } // manager
  
  } // BaseContactMemberPermission 

?>