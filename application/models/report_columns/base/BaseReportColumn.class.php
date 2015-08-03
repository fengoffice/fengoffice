<?php

  /**
  * BaseReportColumn class
  *
  * 
  */
  abstract class BaseReportColumn extends DataObject {
  
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
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ReportColumn 
    */
    function manager() {
      if(!($this->manager instanceof ReportColumns )) $this->manager =  ReportColumns::instance();
      return $this->manager;
    } // manager
  
  } // BaseReportCondition

?>