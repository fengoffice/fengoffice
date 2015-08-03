<?php

  /**
  * BaseContactEmail class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseContactEmail extends DataObject {
  
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
    * Return value of 'email_type_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getEmailTypeId() {
      return $this->getColumnValue('email_type_id');
    } // getEmailTypeId()
    
    /**
    * Set value of 'email_type_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setEmailTypeId($value) {
      return $this->setColumnValue('email_type_id', $value);
    } // setEmailTypeId() 
    
    /**
    * Return value of 'email_address' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getEmailAddress() {
      return $this->getColumnValue('email_address');
    } // getEmailAddress()
    
    /**
    * Set value of 'email_address' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setEmailAddress($value) {
      return $this->setColumnValue('email_address', $value);
    } // setEmailAddress() 
    
    /**
    * Return value of 'is_main' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsMain() {
      return $this->getColumnValue('is_main');
    } // getIsMain()
    
    /**
    * Set value of 'is_default' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsMain($value) {
      return $this->setColumnValue('is_main', $value);
    } // setIsMain() 
    
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ContactEmails 
    */
    function manager() {
      if(!($this->manager instanceof ContactEmails)) $this->manager = ContactEmails::instance();
      return $this->manager;
    } // manager
  
  } // BaseContactEmail 

?>