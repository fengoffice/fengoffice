<?php

  abstract class BaseContactMemberCache extends DataObject {
  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------

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
     * Return value of 'parent_member_id' field
     *
     * @access public
     * @param void
     * @return integer
     */
    function getParentMemberId() {
    	return $this->getColumnValue('parent_member_id');
    } // getParentMemberId()
    
    /**
     * Set value of 'parent_member_id' field
     *
     * @access public
     * @param integer $value
     * @return boolean
     */
    function setParentMemberId($value) {
    	return $this->setColumnValue('parent_member_id', $value);
    } // setParentMemberId()
    
    /**
     * Return value of 'last_activity' field
     *
     * @access public
     * @param void
     * @return DateTimeValue
     */
    function getLastActivity() {
    	return $this->getColumnValue('last_activity');
    } // getLastActivity()
    
    /**
     * Set value of 'last_activity' field
     *
     * @access public
     * @param DateTimeValue $value
     * @return boolean
     */
    function setLastActivity($value) {
    	return $this->setColumnValue('last_activity', $value);
    } // setLastActivity()
  
    function manager() {
      if(!($this->manager instanceof ContactMemberCaches)) $this->manager = ContactMemberCaches::instance();
      return $this->manager;
    }
  
  }
