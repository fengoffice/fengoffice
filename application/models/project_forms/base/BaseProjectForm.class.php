<?php

  /**
  * BaseProjectForm class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseProjectForm extends ProjectDataObject {
  
  	  
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
    * Return value of 'description' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getDescription() {
      return $this->getColumnValue('description');
    } // getDescription()
    
    /**
    * Set value of 'description' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setDescription($value) {
      return $this->setColumnValue('description', $value);
    } // setDescription() 
    
    /**
    * Return value of 'success_message' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getSuccessMessage() {
      return $this->getColumnValue('success_message');
    } // getSuccessMessage()
    
    /**
    * Set value of 'success_message' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setSuccessMessage($value) {
      return $this->setColumnValue('success_message', $value);
    } // setSuccessMessage() 
    
    /**
    * Return value of 'action' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getAction() {
      return $this->getColumnValue('action');
    } // getAction()
    
    /**
    * Set value of 'action' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setAction($value) {
      return $this->setColumnValue('action', $value);
    } // setAction() 
    
    /**
    * Return value of 'in_object_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getInObjectId() {
      return $this->getColumnValue('in_object_id');
    } // getInObjectId()
    
    /**
    * Set value of 'in_object_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setInObjectId($value) {
      return $this->setColumnValue('in_object_id', $value);
    } // setInObjectId() 
    
    /**
    * Return value of 'is_visible' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsVisible() {
      return $this->getColumnValue('is_visible');
    } // getIsVisible()
    
    /**
    * Set value of 'is_visible' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsVisible($value) {
      return $this->setColumnValue('is_visible', $value);
    } // setIsVisible() 
    
    /**
    * Return value of 'is_enabled' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsEnabled() {
      return $this->getColumnValue('is_enabled');
    } // getIsEnabled()
    
    /**
    * Set value of 'is_enabled' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsEnabled($value) {
      return $this->setColumnValue('is_enabled', $value);
    } // setIsEnabled() 
    
    /**
    * Return value of 'order' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getOrder() {
      return $this->getColumnValue('order');
    } // getOrder()
    
    /**
    * Set value of 'order' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setOrder($value) {
      return $this->setColumnValue('order', $value);
    } // setOrder() 
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ProjectForms 
    */
    function manager() {
      if(!($this->manager instanceof ProjectForms)) $this->manager = ProjectForms::instance();
      return $this->manager;
    } // manager
  
  } // BaseProjectForm 

?>