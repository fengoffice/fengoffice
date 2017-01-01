<?php


  abstract class BaseCurrency extends DataObject {
  
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
    }
    
    /**
    * Set value of 'id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setId($value) {
      return $this->setColumnValue('id', $value);
    } 
    
    
    function getSymbol() {
      return $this->getColumnValue('symbol');
    }
    
    function setSymbol($value) {
      return $this->setColumnValue('symbol', $value);
    }

    
  	function getName() {
      return $this->getColumnValue('name');
    }
    
    function setName($value) {
      return $this->setColumnValue('name', $value);
    }
    
    
  	function getShortName() {
      return $this->getColumnValue('short_name');
    }
    
    function setShortName($value) {
      return $this->setColumnValue('short_name', $value);
    }
    
    
  	function getIsDefault() {
      return $this->getColumnValue('is_default');
    }
    
    function setIsDefault($value) {
      return $this->setColumnValue('is_default', $value);
    }
    

    function manager() {
      if(!($this->manager instanceof BaseCurrencies)) $this->manager = BaseCurrencies::instance();
      return $this->manager;
    } 
  } 
