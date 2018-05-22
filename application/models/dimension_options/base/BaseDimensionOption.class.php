<?php

  /**
  * BaseDimensionOption class
  *
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  abstract class BaseDimensionOption extends DataObject {
  
  	  
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
    * @return DimensionOptions 
    */
    function manager() {
      if(!($this->manager instanceof DimensionOptions)) $this->manager = DimensionOptions::instance();
      return $this->manager;
    } // manager
  
  } // BaseDimensionOption 

?>