<?php

  /**
  * BaseObjectTypeHierarchy class
  */
  abstract class BaseObjectTypeHierarchy extends DataObject {
  
  	  
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
    }
    
    /**
    * Set value of 'id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setId($value) {
      return $this->setColumnValue('id', $value);
    }
  	
    /**
    * Return value of 'parent_object_type_id' field
    *
    * @access public
    * @param void
    * @return string
    */
    function getParentObjectTypeId() {
      return $this->getColumnValue('parent_object_type_id');
    }
    
    /**
    * Set value of 'parent_object_type_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setParentObjectTypeId($value) {
      return $this->setColumnValue('parent_object_type_id', $value);
    }
  	
    /**
    * Return value of 'child_object_type_id' field
    *
    * @access public
    * @param void
    * @return string
    */
    function getChildObjectTypeId() {
      return $this->getColumnValue('child_object_type_id');
    }
    
    /**
    * Set value of 'child_object_type_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setChildObjectTypeId($value) {
      return $this->setColumnValue('child_object_type_id', $value);
    }
      
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ObjectTypeHierarchies 
    */
    function manager() {
      if(!($this->manager instanceof ObjectTypeHierarchies)) $this->manager = ObjectTypeHierarchies::instance();
      return $this->manager;
    } // manager
  
  } // BaseObjectTypeHierarchy 

?>