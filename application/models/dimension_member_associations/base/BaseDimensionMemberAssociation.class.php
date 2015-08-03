<?php

  /**
  * BaseDimensionMemberAssociation class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseDimensionMemberAssociation extends DataObject {
  
  	  
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
    * Return value of 'dimension_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getDimensionId() {
      return $this->getColumnValue('dimension_id');
    } // getDimensionMemberAssociationId()
    
    /**
    * Set value of 'dimension_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setDimensionId($value) {
      return $this->setColumnValue('dimension_id', $value);
    } // setDimensionMemberAssociationId() 
    
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
    * Return value of 'associated_dimension_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getAssociatedDimensionMemberAssociationId() {
      return $this->getColumnValue('associated_dimension_id');
    } // getAssociatedDimensionMemberAssociationId()
    
    /**
    * Set value of 'associated_dimension_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setAssociatedDimensionMemberAssociationId($value) {
      return $this->setColumnValue('associated_dimension_id', $value);
    } // setAssociatedDimensionMemberAssociationId() 
    
    /**
    * Return value of 'associated_object_type_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getAssociatedObjectType() {
      return $this->getColumnValue('associated_object_type_id');
    } // getAssociatedObjectType()
    
    /**
    * Set value of 'associated_object_type_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setAssociatedObjectType($value) {
      return $this->setColumnValue('associated_object_type_id', $value);
    } // setAssociatedObjectType() 
    
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
    function setIsRequired($value) {
      return $this->setColumnValue('is_required', $value);
    } // setIsRequired() 
    
    /**
    * Return value of 'is_multiple' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getIsMultiple() {
      return $this->getColumnValue('is_multiple');
    } // getIsMultiple()
    
    /**
    * Set value of 'is_multiple' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setIsMultiple($value) {
      return $this->setColumnValue('is_multiple', $value);
    } // setIsMultiple() 
    
    /**
    * Return value of 'keeps_record' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getKeepsRecord() {
      return $this->getColumnValue('keeps_record');
    } // getKeepsRecord()
    
    /**
    * Set value of 'keeps_record' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setKeepsRecord($value) {
      return $this->setColumnValue('keeps_record', $value);
    } // setKeepsRecord() 
      
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return DimensionMemberAssociations 
    */
    function manager() {
      if(!($this->manager instanceof DimensionMemberAssociations)) $this->manager = DimensionMemberAssociations::instance();
      return $this->manager;
    } // manager
  
  } // BaseDimensionMemberAssociation 

?>