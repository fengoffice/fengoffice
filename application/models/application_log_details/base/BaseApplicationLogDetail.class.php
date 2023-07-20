<?php

  /**
  * BaseApplicationLogDetail class
  */
  abstract class BaseApplicationLogDetail extends DataObject {
  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
    /**
    * Return value of 'application_log_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getApplicationLogId() {
      return $this->getColumnValue('application_log_id');
    } // getId()
    
    /**
    * Set value of 'application_log_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setApplicationLogId($value) {
      return $this->setColumnValue('application_log_id', $value);
    } // setId() 
    
    /**
    * Return value of 'property' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getProperty() {
      return $this->getColumnValue('property');
    } // getProperty()
    
    /**
    * Set value of 'property' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setProperty($value) {
      return $this->setColumnValue('property', $value);
    } // setProperty() 
    
    /**
    * Return value of 'old_value' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getOldValue() {
      return $this->getColumnValue('old_value');
    } // getOldValue()
    
    /**
    * Set value of 'old_value' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setOldValue($value) {
      return $this->setColumnValue('old_value', $value);
    } // setOldValue() 
    
    /**
    * Return value of 'new_value' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getNewValue() {
      return $this->getColumnValue('new_value');
    } // getNewValue()
    
    /**
    * Set value of 'new_value' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setNewValue($value) {
      return $this->setColumnValue('new_value', $value);
    } // setNewValue() 
    
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ApplicationLogDetails 
    */
    function manager() {
      if(!($this->manager instanceof ApplicationLogDetails)) $this->manager = ApplicationLogDetails::instance();
      return $this->manager;
    } // manager
  
  } // BaseApplicationLogDetail

?>