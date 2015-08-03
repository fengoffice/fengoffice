<?php

  /**
  * BaseContactTelephone class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseContactTelephone extends DataObject {
  
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
    * Return value of 'telephone_type_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getTelephoneTypeId() {
      return $this->getColumnValue('telephone_type_id');
    } // getTelephoneTypeId()
    
    /**
    * Set value of 'telephone_type_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setTelephoneTypeId($value) {
      return $this->setColumnValue('telephone_type_id', $value);
    } // setTelephoneTypeId() 
    
    /**
    * Return value of 'number' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getNumber() {
      return $this->getColumnValue('number');
    } // getNumber()
    
    /**
    * Set value of 'number' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setNumber($value) {
      return $this->setColumnValue('number', $value);
    } // setNumber() 
    
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
    * @return ContactTelephones 
    */
    function manager() {
      if(!($this->manager instanceof ContactTelephones)) $this->manager = ContactTelephones::instance();
      return $this->manager;
    } // manager
  
  } // BaseContactTelephone 

?>