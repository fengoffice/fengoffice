<?php

  abstract class BaseSharingTable extends DataObject {
  
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
    * Return value of 'group_id' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getGroupId() {
      return $this->getColumnValue('group_id');
    } // getGroupId()
    
    /**
    * Set value of 'group_id' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setGroupId($value) {
      return $this->setColumnValue('group_id', $value);
    } // setGroupId() 
  
    function manager() {
      if(!($this->manager instanceof SharingTables)) $this->manager = SharingTables::instance();
      return $this->manager;
    }
  
  }
