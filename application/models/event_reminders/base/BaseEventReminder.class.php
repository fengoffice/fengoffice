<?php

  /**
  * BaseEventReminder class
  *
  * @author Marcos Saiz <marcos.saiz@gmail.com>
  */
  abstract class BaseEventReminder extends DataObject {
  
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
    * Return value of 'notification_date' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getNotificationDate() {
      return $this->getColumnValue('notification_date');
    } //   getNotificationDate()
    
    /**
    * Set value of 'notification_date' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function   setNotificationDate($value) {
      return $this->setColumnValue('notification_date', $value);
    } // setNotificationDate() 
    
    /**
    * Return value of 'notify_by' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getNotifyBy() {
      return $this->getColumnValue('notify_by');
    } //  getNotifyBy()
    
    /**
    * Set value of 'notify_by' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function  setNotifyBy($value) {
      return $this->setColumnValue('notify_by', $value);
    } //  setNotifyBy() 
    
    
    /**
    * Return value of 'sent' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsSent() {
      return $this->getColumnValue('sent');
    } // getIsSent()
    
    /**
    * Set value of 'sent' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function  setIsSent($value) {
      return $this->setColumnValue('sent', $value);
    } // setIsSent() 
    
    /**
    * Return value of 'event_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getEventId() {
      return $this->getColumnValue('event_id');
    } // getEventId()
    
    /**
    * Set value of 'event_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setEventId($value) {
      return $this->setColumnValue('event_id', $value);
    } // setEventId()
    
    /**
    * Return value of 'created_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getCreatedOn() {
      return $this->getColumnValue('created_on');
    } // getCreatedOn()
    
    /**
    * Set value of 'created_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setCreatedOn($value) {
      return $this->setColumnValue('created_on', $value);
    } // setCreatedOn() 
    
    /**
    * Return value of 'created_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getCreatedById() {
      return $this->getColumnValue('created_by_id');
    } // getCreatedById()
    
    /**
    * Set value of 'created_by_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setCreatedById($value) {
      return $this->setColumnValue('created_by_id', $value);
    } // setCreatedById() 
    
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return EventReminders 
    */
    function manager() {
      if(!($this->manager instanceof EventReminders)) $this->manager = EventReminders::instance();
      return $this->manager;
    } // manager
  
  } // BaseEventReminder 

?>