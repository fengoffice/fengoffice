<?php

  /**
  * BaseBilling class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseBilling extends ContentDataObject {
  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
    /**
    * Return value of 'object_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getObjectId() {
      return $this->getColumnValue('object_id');
    } // getObjectId()
    
    /**
    * Set value of 'object_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setObjectId($value) {
      return $this->setColumnValue('object_id', $value);
    } // setObjectId() 
    
        
    /**
    * Return value of 'value' field
    *
    * @access public
    * @param void
    * @return float 
    */
    function getValue() {
      return $this->getColumnValue('value');
    } // getValue()   
    
    /**
    * Set value of 'value' field
    *
    * @access public   
    * @param float $value
    * @return boolean
    */
    function setValue($value) {
      return $this->setColumnValue('value', $value);
    } // setValue() 
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return Billings 
    */
    function manager() {
      if(!($this->manager instanceof Billings)) $this->manager = Billings::instance();
      return $this->manager;
    } // manager
  
  } // BaseBilling

?>