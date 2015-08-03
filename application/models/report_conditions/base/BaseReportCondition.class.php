<?php

  /**
  * BaseReportCondition class
  *
  * 
  */
  abstract class BaseReportCondition extends DataObject {
  
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
    * Return value of 'report_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getReportId() {
      return $this->getColumnValue('report_id');
    } // getReportId()
    
    /**
    * Set value of 'report_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setReportId($value) {
      return $this->setColumnValue('report_id', $value);
    } // setReportId() 
    
    /**
    * Return value of 'custom_property_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getCustomPropertyId() {
      return $this->getColumnValue('custom_property_id');
    } // getCustomPropertyId()
    
    /**
    * Set value of 'custom_property_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setCustomPropertyId($value) {
      return $this->setColumnValue('custom_property_id', $value);
    } // setCustomPropertyId() 
   
    /**
    * Return value of 'field_name' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getFieldName() {
      return $this->getColumnValue('field_name');
    } // getFieldName()
    
    /**
    * Set value of 'field_name' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setFieldName($value) {
      return $this->setColumnValue('field_name', $value);
    } // setFieldName() 
    
    /**
    * Return value of 'condition' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getCondition() {
      return $this->getColumnValue('condition');
    } // getCondition()
    
    /**
    * Set value of 'condition' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setCondition($value) {
      return $this->setColumnValue('condition', $value);
    } // setCondition() 
    
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
    * Return value of 'is_parametrizable' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsParametrizable() {
      return $this->getColumnValue('is_parametrizable');
    } // getIsParametrizable()
    
    /**
    * Set value of 'is_parametrizable' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsParametrizable($value) {
      return $this->setColumnValue('is_parametrizable', $value);
    } // setIsParametrizable()
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ReportCondition 
    */
    function manager() {
      if(!($this->manager instanceof ReportConditions )) $this->manager =  ReportConditions::instance();
      return $this->manager;
    } // manager
  
  } // BaseReportCondition

?>