<?php

  /**
  * BaseApplicationLog class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseApplicationLog extends DataObject {
  
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
    * Return value of 'taken_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getTakenById() {
      return $this->getColumnValue('taken_by_id');
    } // getTakenById()
    
    /**
    * Set value of 'taken_by_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setTakenById($value) {
      return $this->setColumnValue('taken_by_id', $value);
    } // setTakenById() 
    
    /**
    * Return value of 'rel_object_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getRelObjectId() {
      return $this->getColumnValue('rel_object_id');
    } // getRelObjectId()
    
    /**
    * Set value of 'rel_object_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setRelObjectId($value) {
      return $this->setColumnValue('rel_object_id', $value);
    } // setRelObjectId() 
    
    /**
    * Return value of 'object_name' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getObjectName() {
      return $this->getColumnValue('object_name');
    } // getObjectName()
    
    /**
    * Set value of 'object_name' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setObjectName($value) {
      return $this->setColumnValue('object_name', $value);
    } // setObjectName() 
    
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
    * Return value of 'action' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getAction() {
      return $this->getColumnValue('action');
    } // getAction()
    
    /**
    * Set value of 'action' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setAction($value) {
      return $this->setColumnValue('action', $value);
    } // setAction() 
    
    /**
    * Return value of 'is_private' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsPrivate() {
      return $this->getColumnValue('is_private');
    } // getIsPrivate()
    
    /**
    * Set value of 'is_private' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsPrivate($value) {
      return $this->setColumnValue('is_private', $value);
    } // setIsPrivate() 
    
    /**
    * Return value of 'is_silent' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsSilent() {
      return $this->getColumnValue('is_silent');
    } // getIsSilent()
    
    /**
    * Set value of 'is_silent' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsSilent($value) {
      return $this->setColumnValue('is_silent', $value);
    } // setIsSilent() 
     
    /**
    * Return value of 'log_data' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getLogData() {
      return $this->getColumnValue('log_data');
    } // getLogData()
    
    /**
    * Set value of 'log_data' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setLogData($value) {
      return $this->setColumnValue('log_data', $value);
    } // setLogData()
    
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
    * @return ApplicationLogs 
    */
    function manager() {
      if(!($this->manager instanceof ApplicationLogs)) $this->manager = ApplicationLogs::instance();
      return $this->manager;
    } // manager
  
  } // BaseApplicationLog 

?>