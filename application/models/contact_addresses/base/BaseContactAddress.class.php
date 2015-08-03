<?php

  /**
  * BaseContactAddress class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseContactAddress extends DataObject {
  
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
    * Return value of 'address_type_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getAddressTypeId() {
      return $this->getColumnValue('address_type_id');
    } // getAddressTypeId()
    
    /**
    * Set value of 'address_type_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setAddressTypeId($value) {
      return $this->setColumnValue('address_type_id', $value);
    } // setAddressTypeId() 
    
    /**
    * Return value of 'street' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getStreet() {
      return $this->getColumnValue('street');
    } // getStreet()
    
    /**
    * Set value of 'street' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setStreet($value) {
      return $this->setColumnValue('street', $value);
    } // setStreet() 
    
    /**
    * Return value of 'city' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getCity() {
      return $this->getColumnValue('city');
    } // getCity()
    
    /**
    * Set value of 'city' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setCity($value) {
      return $this->setColumnValue('city', $value);
    } // setCity() 
    
    /**
    * Return value of 'state' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getState() {
      return $this->getColumnValue('state');
    } // getState()
    
    /**
    * Set value of 'state' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setState($value) {
      return $this->setColumnValue('state', $value);
    } // setState() 
    
    /**
    * Return value of 'country' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getCountry() {
      return $this->getColumnValue('country');
    } // getCountry()
    
    /**
    * Set value of 'country' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setCountry($value) {
      return $this->setColumnValue('country', $value);
    } // setCountry() 
    
    /**
    * Return value of 'zip_code' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getZipCode() {
      return $this->getColumnValue('zip_code');
    } // getZipCode()
    
    /**
    * Set value of 'zip_code' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setZipCode($value) {
      return $this->setColumnValue('zip_code', $value);
    } // setZipCode() 
    
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
    * @return ContactAddresses 
    */
    function manager() {
      if(!($this->manager instanceof ContactAddresses)) $this->manager = ContactAddresses::instance();
      return $this->manager;
    } // manager
  
  } // BaseContactAddress 

?>