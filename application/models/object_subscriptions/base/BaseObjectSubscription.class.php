<?php

  /**
  * BaseObjectSubscription class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseObjectSubscription extends DataObject {
  
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
    } // setUserId() 
    
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ObjectSubscriptions 
    */
    function manager() {
      if(!($this->manager instanceof ObjectSubscriptions)) $this->manager = ObjectSubscriptions::instance();
      return $this->manager;
    } // manager
  
  } // BaseObjectSubscription 

?>