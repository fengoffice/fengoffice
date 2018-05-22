<?php


  abstract class BaseObjectTypeDependency extends DataObject {
  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
    /**
    * Return value of 'object_type_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getObjectTypeId() {
      return $this->getColumnValue('object_type_id');
    }
    
    /**
    * Set value of 'object_type_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setObjectTypeId($value) {
      return $this->setColumnValue('object_type_id', $value);
    }
  
    /**
    * Return value of 'dependant_object_type_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getDependantObjectTypeId() {
      return $this->getColumnValue('dependant_object_type_id');
    }
    
    /**
    * Set value of 'dependant_object_type_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setDependantObjectTypeId($value) {
      return $this->setColumnValue('dependant_object_type_id', $value);
    }
    
    
    

    function manager() {
      if(!($this->manager instanceof BaseObjectTypeDependencies)) $this->manager = ObjectTypeDependencies::instance();
      return $this->manager;
    } 
  } 
