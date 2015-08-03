<?php

  /**
  * BaseContactConfigOptionValue class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseContactConfigOptionValue extends DataObject {
  
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
    function getOptionId() {
      return $this->getColumnValue('option_id');
    } // getId()
    
    /**
    * Set value of 'id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setOptionId($value) {
      return $this->setColumnValue('option_id', $value);
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
    * Return value of 'member_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getMemberId() {
      return $this->getColumnValue('member_id');
    } // getMemberId()
    
    /**
    * Set value of 'member_id' field
    *
    * @access public   
    * @param integer $value
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
    * @return  ContactConfigOptionValues 
    */
    function manager() {
      if(!($this->manager instanceof ContactConfigOptionValues )) $this->manager =  ContactConfigOptionValues::instance();
      return $this->manager;
    } // manager
  
  } // BaseConfigOption 

?>