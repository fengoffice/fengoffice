<?php

  /**
  * BaseMaxRoleObjectTypePermission class
  *
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  abstract class BaseMaxRoleObjectTypePermission extends DataObject {
  
  	  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
    /**
    * Return value of 'role_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getRoleId() {
      return $this->getColumnValue('role_id');
    } // getRoleId()
    
    /**
    * Set value of 'role_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setRoleId($value) {
      return $this->setColumnValue('role_id', $value);
    } // setRoleId() 
    
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
    * @return MaxRoleObjectTypePermissions 
    */
    function manager() {
      if(!($this->manager instanceof MaxRoleObjectTypePermissions)) $this->manager = MaxRoleObjectTypePermissions::instance();
      return $this->manager;
    } // manager
  
  } // BaseMaxRoleObjectTypePermission 

?>