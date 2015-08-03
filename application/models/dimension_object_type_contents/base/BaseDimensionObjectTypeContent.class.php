<?php

  /**
  * BaseDimensionObjectTypeContent class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseDimensionObjectTypeContent extends DataObject {
  
  	  
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
    * Return value of 'dimension_object_type_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getDimensionObjectTypeId() {
      return $this->getColumnValue('dimension_object_type_id');
    } // getDimensionObjectTypeId()
    
    /**
    * Set value of 'dimension_object_type_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setDimensionObjectTypeId($value) {
      return $this->setColumnValue('dimension_object_type_id', $value);
    } // setDimensionObjectTypeId()
    
    /**
    * Return value of 'content_object_type_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getContentObjectTypeId() {
      return $this->getColumnValue('content_object_type_id');
    } // getContentObjectTypeId()
    
    /**
    * Set value of 'content_object_type_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setContentObjectTypeId($value) {
      return $this->setColumnValue('content_object_type_id', $value);
    } // setContentObjectTypeId() 
    
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
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return Dimensions 
    */
    function manager() {
      if(!($this->manager instanceof DimensionObjectTypeContents)) $this->manager = DimensionObjectTypeContents::instance();
      return $this->manager;
    } // manager
  
  } // BaseDimensionObjectTypeContent 

?>