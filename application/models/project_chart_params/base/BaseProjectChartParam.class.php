<?php
  /**
  * BaseProjectChart class
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  abstract class BaseProjectChartParam extends ApplicationDataObject {
  
  	 
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
    * Return value of 'chart_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getChartId() {
      return $this->getColumnValue('chart_id');
    } // getChartId()
    
    /**
    * Set value of 'chart_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setChartId($value) {
      return $this->setColumnValue('chart_id', $value);
    } // setChartId() 
    
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
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ProjectCharts 
    */
    function manager() {
      if(!($this->manager instanceof ProjectChartParams)) $this->manager = ProjectChartParams::instance();
      return $this->manager;
    } // manager
  
  } // BaseProjectChart 
?>