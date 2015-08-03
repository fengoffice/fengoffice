<?php

  /**
  * BaseContactConfigCategory class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseContactConfigCategory extends DataObject {
  
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
    } // getIsSystem()
    
    /**
    * Set value of 'is_system' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsSystem($value) {
      return $this->setColumnValue('is_system', $value);
    } // setIsSystem() 
    
    /**
    * Return value of 'category_order' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getCategoryOrder() {
      return $this->getColumnValue('category_order');
    } // getCategoryOrder()
    
    /**
    * Set value of 'category_order' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setCategoryOrder($value) {
      return $this->setColumnValue('category_order', $value);
    } // setCategoryOrder() 
    
    /**
    * Return value of 'type' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getType() {
      return $this->getColumnValue('type');
    } // getType()
    
    /**
    * Set value of 'type' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setType($value) {
      return $this->setColumnValue('type', $value);
    } // setType() 
    
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ContactConfigCategories 
    */
    function manager() {
      if(!($this->manager instanceof ContactConfigCategories)) $this->manager = ContactConfigCategories::instance();
      return $this->manager;
    } // manager
  
  } // BaseConfigCategory 

?>