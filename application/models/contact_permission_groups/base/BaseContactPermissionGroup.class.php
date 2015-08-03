<?php

  /**
  * BaseContactPermissionGroup class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseContactPermissionGroup extends DataObject {
  
  	  
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
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ContactPermissionGroups 
    */
    function manager() {
      if(!($this->manager instanceof ContactPermissionGroups)) $this->manager = ContactPermissionGroups::instance();
      return $this->manager;
    } // manager
  
  } // BaseContactPermissionGroup 

?>