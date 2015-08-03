<?php

  /**
  * BaseContactImValue class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseContactImValue extends DataObject {
  
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
    * Return value of 'im_type_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getImTypeId() {
      return $this->getColumnValue('im_type_id');
    } // getImTypeId()
    
    /**
    * Set value of 'im_type_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setImTypeId($value) {
      return $this->setColumnValue('im_type_id', $value);
    } // setImTypeId() 
    
    /**
    * Return value of 'value' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getValue() {
      return $this->getColumnValue('value');
    } // getValue()
    
    /**
    * Set value of 'value' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setValue($value) {
      return $this->setColumnValue('value', $value);
    } // setValue() 
    
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
    * @return ContactImValues 
    */
    function manager() {
      if(!($this->manager instanceof ContactImValues)) $this->manager = ContactImValues::instance();
      return $this->manager;
    } // manager
  
  } // BaseContactImValue 

?>