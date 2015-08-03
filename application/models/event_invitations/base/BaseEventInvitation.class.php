<?php

  /**
  * BaseEventInvitation class
  *
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  abstract class BaseEventInvitation extends DataObject {
  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
    /**
    * Return value of 'event_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getEventId() {
      return $this->getColumnValue('event_id');
    } // getId()
    
    /**
    * Set value of 'event_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setEventId($value) {
      return $this->setColumnValue('event_id', $value);
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
    * Return value of 'invitation_state' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getInvitationState() {
      return $this->getColumnValue('invitation_state');
    } // getInvitationState()
    
    /**
    * Set value of 'invitation_state' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setInvitationState($value) {
      return $this->setColumnValue('invitation_state', $value);
    } // setInvitationState() 
    
    /**
     * Set value of 'synced' field
     *
     * @access public
     * @param Date $value
     * @return boolean
     */
    function setUpdateSync() {
    	return $this->setColumnValue('synced', 1);
    } // setUpdateSync() 
    
    function setUpdateSyncFalse() {
    	return $this->setColumnValue('synced', 0);
    } // setUpdateSync()
    
    /**
     * Return value of 'synced' field
     *
     * @access public
     * @return Date
     */
    function getUpdateSync() {
    	return $this->getColumnValue('synced');
    } // getUpdateSync()
    
    function setSpecialId($value) {
    	return $this->setColumnValue('special_id', $value);
    } // setSpecialId()
   
     function getSpecialId() {
     return $this->getColumnValue('special_id');
    } // getSpecialId()
        
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return EventInvitations 
    */
    function manager() {
      if(!($this->manager instanceof EventInvitations)) $this->manager = EventInvitations::instance();
      return $this->manager;
    } // manager
  
  } // BaseEventInvitation

?>