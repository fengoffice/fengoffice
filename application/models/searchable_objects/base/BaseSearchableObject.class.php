<?php

  /**
  * BaseSearchableObject class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseSearchableObject extends DataObject {
  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
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
    * Return value of 'column_name' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getColumnName() {
      return $this->getColumnValue('column_name');
    } // getColumnName()
    
    /**
    * Set value of 'column_name' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setColumnName($value) {
      return $this->setColumnValue('column_name', $value);
    } // setColumnName() 
    
    /**
    * Return value of 'content' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getContent() {
      return $this->getColumnValue('content');
    } // getContent()
    
    /**
    * Set value of 'content' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setContent($value) {
      return $this->setColumnValue('content', $value);
    } // setContent() 
    
   
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
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return SearchableObjects 
    */
    function manager() {
      if(!($this->manager instanceof SearchableObjects)) $this->manager = SearchableObjects::instance();
      return $this->manager;
    } // manager
  
  } // BaseSearchableObject 

?>