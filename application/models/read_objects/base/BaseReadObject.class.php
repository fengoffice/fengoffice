<?php

  /**
  * BaseReadObject class
  *
  * @author Nicolas Medeiros <nicolas@iugo.com.uy>
  */
  abstract class BaseReadObject extends DataObject {
  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
    /** Return value of 'rel_object_id' field
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
    * Return value of 'is_read' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getIsRead() {
      return $this->getColumnValue('is_read');
    } // getIsRead()
    
    /**
    * Set value of 'is_read' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsRead($value) {
      return $this->setColumnValue('is_read', $value);
    } // setIsRead() 
    
    
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
    * Return value of 'updated_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getUpdatedOn() {
      return $this->getColumnValue('updated_on');
    } // getUpdatedOn()
    
    /**
    * Set value of 'updated_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setUpdatedOn($value) {
      return $this->setColumnValue('updated_on', $value);
    } // setUpdatedOn() 
    
    
    
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return LinkedObject 
    */
    function manager() {
      if(!($this->manager instanceof ReadObject)) $this->manager = ReadObjects::instance();
      return $this->manager;
    } // manager
  
  } // BaseReadObject 

?>