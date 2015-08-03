<?php

  /**
  * BaseObjectProperty class
  * Written on Tue, 27 Oct 2007 16:53:08 -0300
  *
  * @author Marcos Saiz <marcos.saiz@fengoffice.com>
  */
  abstract class BaseObjectProperty extends DataObject {
  
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
    * Return value of 'name' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getPropertyName() {
      return $this->getColumnValue('name');
    } // getPropertyname()
    
    /**
    * Set value of 'name' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setPropertyName($value) {
      return $this->setColumnValue('name', $value);
    } // setPropertyName() 
    
  
    /**
    * Return value of 'value' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getPropertyValue() {
      return $this->getColumnValue('value');
    } // getPropertyValue()
    
    /**
    * Set value of 'value' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setPropertyValue($value) {
      return $this->setColumnValue('value', $value);
    } // setPropertyValue() 
    
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
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ObjectProperty 
    */
    function manager() {
      if(!($this->manager instanceof ObjectProperties )) $this->manager =  ObjectProperties::instance();
      return $this->manager;
    } // manager
  
  } // BaseObjectProperty

?>