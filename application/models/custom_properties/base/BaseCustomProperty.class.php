<?php

  /**
  * BaseCustomProperty class
  * Written on Thu, 4 Oct 2009 14:51:09 -0300
  */
  abstract class BaseCustomProperty extends DataObject {
  
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
    * Return value of 'object_type_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getObjectTypeId() {
      return $this->getColumnValue('object_type_id');
    } // getObjectTypeId()
    
    /**
    * Set value of 'object_type_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setObjectTypeId($value) {
      return $this->setColumnValue('object_type_id', $value);
    } // setObjectTypeId() 
      
    /**
    * Return value of 'name' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getName() {
      return $this->getColumnValue('name');
    } // getName()
    
    /**
    * Set value of 'name' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setName($value) {
      return $this->setColumnValue('name', $value);
    } // setName() 
    
    /**
    * Return value of 'code' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getCode() {
      return $this->getColumnValue('code');
    } // getCode()
    
    /**
    * Set value of 'code' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setCode($value) {
      return $this->setColumnValue('code', $value);
    } // setCode() 
    
    /**
    * Return value of 'type' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getType() {
      return $this->getColumnValue('type');
    } // getType()
    
    /**
    * Set value of 'type' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setType($value) {
      return $this->setColumnValue('type', $value);
    } // setType() 
    
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
    * Return value of 'values' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getValues() {
      return $this->getColumnValue('values');
    } // getValues()
    
    /**
    * Set value of 'values' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setValues($value) {
      return $this->setColumnValue('values', $value);
    } // setValues()
    
    /**
    * Return value of 'default_value' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getDefaultValue() {
      return $this->getColumnValue('default_value');
    } // getDefaultValue()
    
    /**
    * Set value of 'default_value' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setDefaultValue($value) {
      return $this->setColumnValue('default_value', $value);
    } // setDefaultValue() 
    
    /**
    * Return value of 'required' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getIsRequired() {
      return $this->getColumnValue('is_required');
    } // getIsRequired()
    
    /**
    * Set value of 'required' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setIsRequired($value) {
      return $this->setColumnValue('is_required', $value);
    } // setIsRequired() 
    
    /**
    * Return value of 'is_multiple_values' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getIsMultipleValues() {
      return $this->getColumnValue('is_multiple_values');
    } // getIsMultipleValues()
    
    /**
    * Set value of 'is_multiple_values' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setIsMultipleValues($value) {
      return $this->setColumnValue('is_multiple_values', $value);
    } // setIsMultipleValues() 
    
    /**
    * Return value of 'property_order' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getOrder() {
      return $this->getColumnValue('property_order');
    } // getOrder()
    
    /**
    * Set value of 'property_order' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setOrder($value) {
      return $this->setColumnValue('property_order', $value);
    } // setOrder() 
    
    /**
    * Return value of 'visible_by_default' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getVisibleByDefault() {
      return $this->getColumnValue('visible_by_default');
    } // getVisibleByDefault()
    
    /**
    * Set value of 'visible_by_default' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setVisibleByDefault($value) {
      return $this->setColumnValue('visible_by_default', $value);
    } // getVisibleByDefault() 
      
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return CustomProperty 
    */
    function manager() {
      if(!($this->manager instanceof CustomProperties )) $this->manager =  CustomProperties::instance();
      return $this->manager;
    } // manager
  
  } // BaseCustomProperty

?>