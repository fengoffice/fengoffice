<?php

  /**
  * BaseEmailType class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseEmailType extends DataObject {
  
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
    * Return value of 'is_system' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsSystem() {
      return $this->getColumnValue('is_system');
    } // getIcon()
    
    /**
    * Set value of 'is_system' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setIsSystem($value) {
      return $this->setColumnValue('is_system', $value);
    } // setIcon() 
    
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return EmailTypes 
    */
    function manager() {
      if(!($this->manager instanceof EmailTypes)) $this->manager = EmailTypes::instance();
      return $this->manager;
    } // manager
  
  } // BaseEmailType 

?>