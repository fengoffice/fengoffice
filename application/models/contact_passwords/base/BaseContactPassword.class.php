<?php

  /**
  * BaseContactPassword class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseContactPassword extends DataObject {
  	
  	var $password_temp = '';
  
  
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
    * Return value of 'password' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getPassword() {
      return $this->getColumnValue('password');
    } // getPassword()
    
    /**
    * Set value of 'password' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setPassword($value) {
      return $this->setColumnValue('password', $value);
    } // setPassword() 
    
       
    /**
    * Return value of 'password_date' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getPasswordDate() {
      return $this->getColumnValue('password_date');
    } // getPasswordDate()
    
    /**
    * Set value of 'password_date' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setPasswordDate($value) {
      return $this->setColumnValue('password_date', $value);
    } // setPasswordDate()     
 
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ContactPasswords 
    */
    function manager() {
      if(!($this->manager instanceof ContactPasswords)) $this->manager = ContactPasswords::instance();
      return $this->manager;
    } // manager
    
  
  } // ContactPassword 

?>