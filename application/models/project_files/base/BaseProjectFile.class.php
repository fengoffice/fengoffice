<?php

  /**
  * BaseProjectFile class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseProjectFile extends ContentDataObject {
  
  	// -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
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
    * Return value of 'description' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getDescription() {
      return $this->getColumnValue('description');
    } // getDescription()
    
    /**
    * Set value of 'description' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setDescription($value) {
      return $this->setColumnValue('description', $value);
    } // setDescription() 
    
    /**
    * Return value of 'is_locked' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsLocked() {
      return $this->getColumnValue('is_locked');
    } // getIsLocked()
    
    /**
    * Set value of 'is_locked' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsLocked($value) {
      return $this->setColumnValue('is_locked', $value);
    } // setIsLocked() 
    
    /**
    * Return value of 'is_visible' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsVisible() {
      return $this->getColumnValue('is_visible');
    } // getIsVisible()
    
    /**
    * Set value of 'is_visible' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsVisible($value) {
      return $this->setColumnValue('is_visible', $value);
    } // setIsVisible() 
    
    /**
    * Return value of 'expiration_time' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getExpirationTime() {
      return $this->getColumnValue('expiration_time');
    } // getExpirationTime()
    
    /**
    * Set value of 'expiration_time' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setExpirationTime($value) {
      return $this->setColumnValue('expiration_time', $value);
    } // setExpirationTime() 
    
    /**
     * Return value of 'checked_out_on' field
     *
     * @access public
     * @param void
     * @return DateTimeValue
     */
    function getCheckedOutOn() {
    	return $this->getColumnValue('checked_out_on');
    } // getCheckedOutOn()

    /**
     * Set value of 'checked_out_on' field
     *
     * @access public
     * @param DateTimeValue $value
     * @return boolean
     */
    function setCheckedOutOn($value) {
    	return $this->setColumnValue('checked_out_on', $value);
    } // setCheckedOutOn()

    /**
     * Return value of 'checked_out_by_id' field
     *
     * @access public
     * @param void
     * @return integer
     */
    function getCheckedOutById() {
    	return $this->getColumnValue('checked_out_by_id');
    } // getCheckedOutById()

    /**
     * Set value of 'checked_out_by_id' field
     *
     * @access public
     * @param integer $value
     * @return boolean
     */
    function setCheckedOutById($value) {
    	return $this->setColumnValue('checked_out_by_id', $value);
    } // setCheckedOutById()
    
    /**
    * Set value of 'was_auto_checked_out' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setWasAutoCheckedAuto($value) {
      return $this->setColumnValue('was_auto_checked_out', $value);
    } //  setWasAutoCheckedAuto() 
    
    /**
    * Return value of 'was_auto_checked_out' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function  getWasAutoCheckedAuto() {
      return $this->getColumnValue('was_auto_checked_out');
    } //  getWasAutoCheckedAuto()

    /**
    * Return value of 'type' field, contains an id of an email if the file is an attachment
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getType() {
      return $this->getColumnValue('type');
    } // getType()
    
    /**
    * Set value of 'type' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setType($value) {
      return $this->setColumnValue('type', $value);
    } // setType() 
    
    /**
    * Return value of 'url' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getUrl() {
      $url = $this->getColumnValue('url');
	  if ($url && strpos($url, ':') === false) {
		$url = "http://". $url;
	  }
	  return $url;
    } // getUrl()
    
    /**
    * Set value of 'url' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setUrl($value) {
      return $this->setColumnValue('url', $value);
    } // setUrl() 
    
    /**
    * Return value of 'mail_id' field, contains an id of an email if the file is an attachment
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getMailId() {
      return $this->getColumnValue('mail_id');
    } // getMailId()
    
    /**
    * Set value of 'mail_id' field (id of an email if the file is an attachment)
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setMailId($value) {
      return $this->setColumnValue('mail_id', $value);
    } // setMailId()

    /** Return value of 'attach_to_notification' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getAttachToNotification() {
      return $this->getColumnValue('attach_to_notification');
    } // getAttachtoNotification()
    
    /**
    * Set value of 'attach_to_notification' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setAttachToNotification($value) {
      return $this->setColumnValue('attach_to_notification', $value);
    } // setAttachtoNotification() 
    
    /** Return value of 'default_subject' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getDefaultSubject() {
      return $this->getColumnValue('default_subject');
    } // getDefaultSubject()
    
    /**
    * Set value of 'default_subject' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setDefaultSubject($value) {
      return $this->setColumnValue('default_subject', $value);
    } // setDefaultSubject() 
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ProjectFiles 
    */
    function manager() {
      if(!($this->manager instanceof ProjectFiles)) $this->manager = ProjectFiles::instance();
      return $this->manager;
    } // manager
    
  
  } // BaseProjectFile 
   
    
?>