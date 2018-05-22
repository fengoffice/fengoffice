<?php

  /**
  * BaseContactConfigOption class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseContactConfigOption extends DataObject {
  
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
    * Return value of 'category_name' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getCategoryName() {
      return $this->getColumnValue('category_name');
    } // getCategoryName()
    
    /**
    * Set value of 'category_name' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setCategoryName($value) {
      return $this->setColumnValue('category_name', $value);
    } // setCategoryName() 
    
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
    * Return value of 'default_value' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getDefaultValue() {
      return $this->getColumnValue('default_value');
    } //  getDefaultValue()
    
    /**
    * Set value of 'default_value' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function  setDefaultValue($value) {
      return $this->setColumnValue('default_value', $value);
    } //  setDefaultValue() 
    
    /**
    * Return value of 'config_handler_class' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getConfigHandlerClass() {
      return $this->getColumnValue('config_handler_class');
    } // getConfigHandlerClass()
    
    /**
    * Set value of 'config_handler_class' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setConfigHandlerClass($value) {
      return $this->setColumnValue('config_handler_class', $value);
    } // setConfigHandlerClass() 
    
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
    * Return value of 'option_order' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getOptionOrder() {
      return $this->getColumnValue('option_order');
    } // getOptionOrder()
    
    /**
    * Set value of 'option_order' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setOptionOrder($value) {
      return $this->setColumnValue('option_order', $value);
    } // setOptionOrder() 
    
    /**
    * Return value of 'dev_comment' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getDevComment() {
      return $this->getColumnValue('dev_comment');
    } // getDevComment()
    
    /**
    * Set value of 'dev_comment' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setDevComment($value) {
      return $this->setColumnValue('dev_comment', $value);
    } // setDevComment() 
    
    
    /**
     * Return value of 'options' field
     *
     * @access public
     * @param void
     * @return string
     */
    function getOptions() {
    	return $this->getColumnValue('options');
    } // getOptions()
    
    /**
     * Set value of 'options' field
     *
     * @access public
     * @param string $value
     * @return boolean
     */
    function setOptions($value) {
    	return $this->setColumnValue('options', $value);
    }
    
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ConfigOptions 
    */
    function manager() {
      if(!($this->manager instanceof ContactConfigOptions)) $this->manager = ContactConfigOptions::instance();
      return $this->manager;
    } // manager
  
  } //  BaseContactConfigOption 

?>