<?php

  /**
  * BaseReport class
  *
  * 
  */
  abstract class BaseReport extends ContentDataObject {
  
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
    function getId() {
      return $this->getColumnValue('object_id');
    } // getId()
    
    /**
    * Set value of 'object_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setId($value) {
      return $this->setColumnValue('object_id', $value);
    } // setId() 
    
       
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
    * Return value of 'report_object_type_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getReportObjectTypeId() {
      return $this->getColumnValue('report_object_type_id');
    } // getReportObjectTypeId()
    
    /**
    * Set value of 'report_object_type_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setReportObjectTypeId($value) {
      return $this->setColumnValue('report_object_type_id', $value);
    } // setReportObjectTypeId() 
    
    /**
    * Return value of 'order_by' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getOrderBy() {
      return $this->getColumnValue('order_by');
    } // getOrderBy()
    
    /**
    * Set value of 'order_by' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setOrderBy($value) {
      return $this->setColumnValue('order_by', $value);
    } // setOrderBy() 
    
    /**
    * Return value of 'is_order_by_asc' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsOrderByAsc() {
      return $this->getColumnValue('is_order_by_asc');
    } // getIsOrderByAsc()
    
    /**
    * Set value of 'is_order_by_asc' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setIsOrderByAsc($value) {
      return $this->setColumnValue('is_order_by_asc', $value);
    } // setIsOrderByAsc() 
    
    /**
    * Return value of 'ignore_context' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIgnoreContext() {
      return $this->getColumnValue('ignore_context');
    } // getIgnoreContext()
    
    /**
    * Set value of 'ignore_context' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setIgnoreContext($value) {
      return $this->setColumnValue('ignore_context', $value);
    } // setIgnoreContext() 
    

    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return Report 
    */
    function manager() {
      if(!($this->manager instanceof Reports )) $this->manager =  Reports::instance();
      return $this->manager;
    } // manager
  
  } // BaseReport

?>