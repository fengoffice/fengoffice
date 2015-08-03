<?php

  /**
  * BasePermissionContext class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BasePermissionContext extends DataObject {
  
  	  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
    /**
    * Return value of 'contact_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getContactId() {
      return $this->getColumnValue('contact_id');
    } // getContactId()
    
    /**
    * Set value of 'contact_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setContactId($value) {
      return $this->setColumnValue('contact_id', $value);
    } // setContactId() 
    
    /**
    * Return value of 'permission_group_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getPermissionGroupId() {
      return $this->getColumnValue('permission_group_id');
    } // getPermissionGroupId()
    
    /**
    * Set value of 'permission_group_id' field
    *
    * @access public   
    * @param string $value
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
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return PermissionContexts 
    */
    function manager() {
      if(!($this->manager instanceof PermissionContexts)) $this->manager = PermissionContexts::instance();
      return $this->manager;
    } // manager
  
  } // BasePermissionContext 

?>