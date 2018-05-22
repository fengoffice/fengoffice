<?php

  /**
  * BaseObjectTypeHierarchyOption class
  */
  abstract class BaseObjectTypeHierarchyOption extends DataObject {
  
  	  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
  	/**
    * Return value of 'hierarchy_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getHierarchyId() {
      return $this->getColumnValue('hierarchy_id');
    }
    
    /**
    * Set value of 'hierarchy_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setHierarchyId($value) {
      return $this->setColumnValue('hierarchy_id', $value);
    }
  	
    /**
    * Return value of 'dimension_id' field
    *
    * @access public
    * @param void
    * @return string
    */
    function getDimensionId() {
      return $this->getColumnValue('dimension_id');
    }
    
    /**
    * Set value of 'dimension_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setDimensionId($value) {
      return $this->setColumnValue('dimension_id', $value);
    }
  	
    /**
    * Return value of 'member_type_id' field
    *
    * @access public
    * @param void
    * @return string
    */
    function getMemberTypeId() {
      return $this->getColumnValue('member_type_id');
    }
    
    /**
    * Set value of 'member_type_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setMemberTypeId($value) {
      return $this->setColumnValue('member_type_id', $value);
    }
  	
    /**
    * Return value of 'option' field
    *
    * @access public
    * @param void
    * @return string
    */
    function getOption() {
      return $this->getColumnValue('option');
    }
    
    /**
    * Set value of 'option' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setOption($value) {
      return $this->setColumnValue('option', $value);
    }
  	
    /**
    * Return value of 'value' field
    *
    * @access public
    * @param void
    * @return string
    */
    function getValue() {
      return $this->getColumnValue('value');
    }
    
    /**
    * Set value of 'value' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setValue($value) {
      return $this->setColumnValue('value', $value);
    }
      
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ObjectTypeHierarchyOptions 
    */
    function manager() {
      if(!($this->manager instanceof ObjectTypeHierarchyOptions)) $this->manager = ObjectTypeHierarchyOptions::instance();
      return $this->manager;
    } // manager
  
  } // BaseObjectTypeHierarchyOption 

?>