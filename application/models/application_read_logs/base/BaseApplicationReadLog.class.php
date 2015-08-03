<?php

  /**
  * Base class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseApplicationReadLog extends DataObject {
  
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
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return BaseApplicationReadLogs 
    */
    function manager() {
      if(!($this->manager instanceof BaseApplicationReadLogs)) $this->manager = BaseApplicationReadLogs::instance();
      return $this->manager;
    } // manager
  
  } // Base 

?>