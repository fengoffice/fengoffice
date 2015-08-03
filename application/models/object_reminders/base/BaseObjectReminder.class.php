<?php

  /**
  * BaseObjectReminder class
  *
  * @author Ignacio de Soto <ignacio.desoto@fengoffice.com>
  */
  abstract class BaseObjectReminder extends DataObject {
  
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
    } // setId
    
  	
    /**
    * Return value of 'object_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getObjectId() {
      return $this->getColumnValue('object_id');
    } // getObjectId()
    
    /**
    * Set value of 'object_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setObjectId($value) {
      return $this->setColumnValue('object_id', $value);
    } // setObjectId() 
    
    /**
    * Return value of 'user_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getUserId() {
      return $this->getColumnValue('contact_id');
    } // getUserId()
    
    /**
    * Set value of 'user_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setUserId($value) {
      return $this->setColumnValue('contact_id', $value);
    } // setUserId() 
    
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
    * Return value of 'context' field
    *
    * @access public
    * @param void
    * @return string
    */
    function getContext() {
      return $this->getColumnValue('context');
    } // getContext()
    
    /**
    * Set value of 'context' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setContext($value) {
      return $this->setColumnValue('context', $value);
    } // setContext()
    
    /**
    * Return value of 'minutes_before' field
    *
    * @access public
    * @param void
    * @return integer
    */
    function getMinutesBefore() {
      return $this->getColumnValue('minutes_before');
    } // getMinutesBefore()
    
    /**
    * Set value of 'minutes_before' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setMinutesBefore($value) {
      return $this->setColumnValue('minutes_before', $value);
    } // setMinutesBefore
    
    /**
    * Return value of 'date' field
    *
    * @access public
    * @param void
    * @return integer
    */
    function getDate() {
      return $this->getColumnValue('date');
    } // getDate()
    
    /**
    * Set value of 'date' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setDate($value) {
      return $this->setColumnValue('date', $value);
    } // setDate
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ObjectReminders 
    */
    function manager() {
      if(!($this->manager instanceof ObjectReminders)) $this->manager = ObjectReminders::instance();
      return $this->manager;
    } // manager
  
  } // BaseObjectReminder 

?>