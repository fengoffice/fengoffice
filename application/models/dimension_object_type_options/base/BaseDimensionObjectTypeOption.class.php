<?php

  /**
  * BaseDimensionObjectTypeOption class
  *
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  abstract class BaseDimensionObjectTypeOption extends DataObject {
  
  	  
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
     * @return integer
     */
    function getObjectTypeId() {
    	return $this->getColumnValue('object_type_id');
    } // getObjectTypeId()
    
    /**
     * Set value of 'object_type_id' field
     *
     * @access public
     * @param integer $value
     * @return boolean
     */
    function setObjectTypeId($value) {
    	return $this->setColumnValue('object_type_id', $value);
    } // setObjectTypeId()
    
    /**
    * Return value of 'value' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getValue() {
      return $this->getColumnValue('value');
    } // getValue()
    
    /**
    * Set value of 'value' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setValue($value) {
      return $this->setColumnValue('value', $value);
    } // setValue()
    
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
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return DimensionObjectTypeOptions 
    */
    function manager() {
      if(!($this->manager instanceof DimensionObjectTypeOptions)) $this->manager = DimensionObjectTypeOptions::instance();
      return $this->manager;
    } // manager
  
  } // BaseDimensionObjectTypeOption 

?>