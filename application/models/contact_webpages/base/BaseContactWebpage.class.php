<?php

  /**
  * BaseContactebpage class
  */
  abstract class BaseContactWebpage extends DataObject {
  
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
    * Return value of 'web_type_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getWebTypeId() {
      return $this->getColumnValue('web_type_id');
    } // getWebTypeId()
    
    /**
    * Set value of 'web_type_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setWebTypeId($value) {
      return $this->setColumnValue('web_type_id', $value);
    } // setWebTypeId() 
    
    /**
    * Return value of 'URL' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getUrl() {
      return $this->getColumnValue('url');
    } // getURL()
    
    /**
    * Set value of 'URL' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setUrl($value) {
      return $this->setColumnValue('url', $value);
    } // setURL() 
    
   /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ContactWebpages 
    */
    function manager() {
      if(!($this->manager instanceof ContactWebpages)) $this->manager = ContactWebpages::instance();
      return $this->manager;
    } // manager
  
  } // BaseContactWebpage 

?>