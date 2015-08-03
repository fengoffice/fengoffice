<?php

  /**
  * BaseProjectEvent class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseProjectEvent extends ContentDataObject {
  
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
    * Return value of 'duration' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getDuration() {
      return $this->getColumnValue('duration');
    } // getduration()
    
    /**
    * Set value of 'folder_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setDuration($value) {
      return $this->setColumnValue('duration', $value);
    } // setduration() 
    
    /**
    * Return value of 'forever' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getRepeatForever() {
      return $this->getColumnValue('repeat_forever');
    } //  getForever()
    
    /**
    * Set value of 'forever' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function  setRepeatForever($value) {
      return $this->setColumnValue('repeat_forever', $value);
    } //  setForever() 
    
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
    * Return value of 'repeat_end' field
    *
    * @access public
    * @param void
    * @return Date 
    */
    function getRepeatEnd() {
      return $this->getColumnValue('repeat_end');
    } //  getRepeatEnd()
    
    /**
    * Set value of 'repeat_end' field
    *
    * @access public   
    * @param Date $value
    * @return boolean
    */
    function  setRepeatEnd($value) {
      return $this->setColumnValue('repeat_end', $value);
    } //  setRepeatEnd() 
    
    /**
    * Set value of 'repeat_end' field
    *
    * @access public   
    * @param Integer $value
    * @return boolean
    */
    function  setRepeatNum($value) {
      return $this->setColumnValue('repeat_num', $value);
    } //  setRepeatNum() 
    
    /**
    * Return value of 'repeat_end' field
    *
    * @access public
    * @param void
    * @return  Integer 
    */
    function getRepeatNum() {
      return $this->getColumnValue('repeat_num');
    } //  getRepeatNum()
    
    /**
    * Set value of 'repeat_end' field
    *
    * @access public   
    * @param Integer $value
    * @return boolean
    */
    function  setRepeatD($value) {
      return $this->setColumnValue('repeat_d', $value);
    } //  setRepeatEnd() 
    
    /**
    * Return value of 'repeat_end' field
    *
    * @access public
    * @param void
    * @return  Integer 
    */
    function getRepeatD() {
      return $this->getColumnValue('repeat_d');
    } //  getRepeatEnd()
    /**
    * Set value of 'repeat_end' field
    *
    * @access public   
    * @param Integer $value
    * @return boolean
    */
    function  setRepeatM($value) {
      return $this->setColumnValue('repeat_m', $value);
    } //  setRepeatEnd() 
    
    /**
    * Return value of 'repeat_end' field
    *
    * @access public
    * @param void
    * @return  Integer 
    */
    function getRepeatM() {
      return $this->getColumnValue('repeat_m');
    } //  getRepeatEnd()
    /**
    * Set value of 'repeat_end' field
    *
    * @access public   
    * @param Integer $value
    * @return boolean
    */
    function  setRepeatY($value) {
      return $this->setColumnValue('repeat_y', $value);
    } //  setRepeatEnd() 
    
    /**
    * Return value of 'repeat_end' field
    *
    * @access public
    * @param void
    * @return  Integer 
    */
    function getRepeatY() {
      return $this->getColumnValue('repeat_y');
    } //  getRepeatEnd()
    /**
    * Set value of 'repeat_end' field
    *
    * @access public   
    * @param Integer $value
    * @return boolean
    */
    function  setRepeatH($value) {
      return $this->setColumnValue('repeat_h', $value);
    } //  setRepeatEnd() 
    
    /**
    * Return value of 'repeat_end' field
    *
    * @access public
    * @param void
    * @return  Integer 
    */
    function getRepeatH() {
      return $this->getColumnValue('repeat_h');
    } //  getRepeatEnd()
    
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
    * Set value of 'type_id' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setTypeId($value) {
      return $this->setColumnValue('type_id', $value);
    } // setIsLocked() 
    
    /**
    * Return value of 'type_id' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getTypeId() {
      return $this->getColumnValue('type_id');
    } // getIsVisible()
    
    /**
    * Set value of 'special_id' field
    *
    * @access public   
    * @param string $value
    * @return string
    */
    function setSpecialID($value) {
      return $this->setColumnValue('special_id', $value);
    } // setSpecialID() 
    
    /**
    * Return value of 'special_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getSpecialID() {
      return $this->getColumnValue('special_id');
    } // setSpecialID()
    
    /**
    * Set value of 'update_sync' field
    *
    * @access public   
    * @param Date $value
    * @return Date
    */
    function setUpdateSync($value) {
      return $this->setColumnValue('update_sync', $value);
    } // setUpdateSync() 
    
    /**
    * Return value of 'update_sync' field
    *
    * @access public
    * @return Date 
    */
    function getUpdateSync() {
      return $this->getColumnValue('update_sync');
    } // getUpdateSync()
    
    /**
    * Return value of 'start' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getStart() {
      return $this->getColumnValue('start');
    } // getStart()
    
    /**
    * Set value of 'start' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setStart($value) {
      return $this->setColumnValue('start', $value);
    } // setStart() 
    
    

    /**
    * Return value of 'repeat_dow' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getRepeatDow() {
      return $this->getColumnValue('repeat_dow');
    } // getRepeatDow()
    
    /**
    * Set value of 'repeat_dow' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setRepeatDow($value) {
      return $this->setColumnValue('repeat_dow', $value);
    } // setRepeatDow()
    
        /**
    * Return value of 'repeat_wnum' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getRepeatWnum() {
      return $this->getColumnValue('repeat_wnum');
    } // getRepeatWnum()
    
    /**
    * Set value of 'repeat_wnum' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setRepeatWnum($value) {
      return $this->setColumnValue('repeat_wnum', $value);
    } // setRepeatWnum()
    
        /**
    * Return value of 'repeat_mjump' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getRepeatMjump() {
      return $this->getColumnValue('repeat_mjump');
    } // getRepeatMjump()
    
    /**
    * Set value of 'repeat_mjump' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setRepeatMjump($value) {
      return $this->setColumnValue('repeat_mjump', $value);
    } // setRepeatMjump()
    
    /**
    * Return value of 'ext_cal_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getExtCalId() {
      return $this->getColumnValue('ext_cal_id');
    } // getExtCalId()
    
    /**
    * Set value of 'ext_cal_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setExtCalId($value) {
      return $this->setColumnValue('ext_cal_id', $value);
    } // setExtCalId() 
    
    /**
     * Return value of 'original_event_id' field
     *
     * @access public
     * @param void
     * @return integer
     */
    function getOriginalEventId() {
            return $this->getColumnValue('original_event_id');
    } // getOriginalEventId()

    /**
     * Set value of 'original_event_id' field
     *
     * @access public
     * @param integer $value
     * @return boolean
     */
    function setOriginalEventId($value) {
            return $this->setColumnValue('original_event_id', $value);
    } // setOriginalEventId()
    
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ProjectEvents 
    */
    function manager() {
      if(!($this->manager instanceof ProjectEvents)) $this->manager = ProjectEvents::instance();
      return $this->manager;
    } // manager
  
  } // BaseProjectEvent 

?>