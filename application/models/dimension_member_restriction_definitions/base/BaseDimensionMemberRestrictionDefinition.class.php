<?php

  /**
  * BaseDimensionMemberRestrictionDefinition class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseDimensionMemberRestrictionDefinition extends DataObject {
  
  	  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
    /**
    * Return value of 'dimension_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getDimensionId() {
      return $this->getColumnValue('dimension_id');
    } // getDimensionId()
    
    /**
    * Set value of 'dimension_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setDimensionId($value) {
      return $this->setColumnValue('dimension_id', $value);
    } // setDimensionId() 
    
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
    * Return value of 'restricted_dimension_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getRestrictedDimensionId() {
      return $this->getColumnValue('restricted_dimension_id');
    } // getRestrictedDimensionId()
    
    /**
    * Set value of 'restricted_dimension_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setRestrictedDimensionId($value) {
      return $this->setColumnValue('restricted_dimension_id', $value);
    } // setRestrictedDimensionId() 
    
    /**
    * Return value of 'restricted_object_type_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getRestrictedObjectTypeId() {
      return $this->getColumnValue('restricted_object_type_id');
    } // getRestrictedObjectType()
    
    /**
    * Set value of 'restricted_object_type_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setRestrictedObjectTypeId($value) {
      return $this->setColumnValue('restricted_object_type_id', $value);
    } // setRestrictedObjectType() 
    
    /**
    * Return value of 'is_orderable' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getIsOrderable() {
      return $this->getColumnValue('is_orderable');
    } // getIsManageable()
    
    /**
    * Set value of 'is_orderable' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setIsOrderable($value) {
      return $this->setColumnValue('is_orderable', $value);
    } // setIsManageable() 
    
    /**
    * Return value of 'is_required' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getIsRequired() {
      return $this->getColumnValue('is_required');
    } // getIsRequired()
    
    /**
    * Set value of 'is_required' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setIsRquired($value) {
      return $this->setColumnValue('is_required', $value);
    } // setIsRequired() 
    
   
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return DimensionMemberRestrictionDefinitions 
    */
    function manager() {
      if(!($this->manager instanceof DimensionMemberRestrictionDefinitions)) $this->manager = DimensionMemberRestrictionDefinitions::instance();
      return $this->manager;
    } // manager
  
  } // BaseDimensionMemberRestrictionDefinition 

?>