<?php

  /**
  * BasePermissionGroup class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BasePermissionGroup extends DataObject {
  
  	  
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
    * Set value of 'parent_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setParentId($value) {
      return $this->setColumnValue('parent_id', $value);
    } // setPrentId() 
    
     /**
    * Return value of 'parent_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getParentId() {
      return $this->getColumnValue('parent_id');
    } // getParentId()
    
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
    * Return value of 'contact_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getContactId() {
      return $this->getColumnValue('contact_id');
    } // getIsRoot()
    
    /**
    * Set value of 'contact_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setContactId($value) {
      return $this->setColumnValue('contact_id', $value);
    } // setIsRoot() 
    
    /**
    * Return value of 'is_context' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getIsContext() {
      return $this->getColumnValue('is_context');
    } // getIsManageable()
    
    /**
    * Set value of 'is_context' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setIsContext($value) {
      return $this->setColumnValue('is_context', $value);
    } // setIsManageable() 
    
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
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return PermissionGroups 
    */
    function manager() {
      if(!($this->manager instanceof PermissionGroups)) $this->manager = PermissionGroups::instance();
      return $this->manager;
    } // manager
  
  } // BasePermissionGroup 

?>