<?php

  /**
  * BaseDimensionObjectTypeHierarchy class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseDimensionObjectTypeHierarchy extends DataObject {
  
  	  
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
    * Return value of 'parent_object_type_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getParentObjectTypeId() {
      return $this->getColumnValue('parent_object_type_id');
    } // getParentObjectTypeId()
    
    /**
    * Set value of 'parent_object_type_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setParentObjectTypeId($value) {
      return $this->setColumnValue('parent_object_type_id', $value);
    } // setParentObjectTypeId()
    
    /**
    * Return value of 'child_object_type_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getChildObjectTypeId() {
      return $this->getColumnValue('child_object_type_id');
    } // getChildObjectTypeId()
    
    /**
    * Set value of 'child_object_type_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setChildObjectTypeId($value) {
      return $this->setColumnValue('child_object_type_id', $value);
    } // setChildObjectTypeId() 
         
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return DimensionObjectTypeHierarchies 
    */
    function manager() {
      if(!($this->manager instanceof DimensionObjectTypeHierarchyies)) $this->manager = DimensionObjectTypeHierarchies::instance();
      return $this->manager;
    } // manager
  
  } // BaseDimensionObjectTypeHierarchy 

?>