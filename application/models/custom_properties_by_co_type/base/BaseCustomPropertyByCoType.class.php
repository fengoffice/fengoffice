<?php

  /**
  * BaseCustomPropertyByCoType class
  *
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  abstract class BaseCustomPropertyByCoType extends DataObject {
  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
    /**
    * Return value of 'co_type_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getCoTypeId() {
      return $this->getColumnValue('co_type_id');
    } // getCoTypeId()
    
    /**
    * Set value of 'co_type_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setCoTypeId($value) {
      return $this->setColumnValue('co_type_id', $value);
    } // setCoTypeId() 
    
    /**
    * Return value of 'cp_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getCpId() {
      return $this->getColumnValue('cp_id');
    } // getCpId()
    
    /**
    * Set value of 'cp_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setCpId($value) {
      return $this->setColumnValue('cp_id', $value);
    } // setCpId() 
      
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return CustomPropertiesByCoType 
    */
    function manager() {
      if(!($this->manager instanceof CustomPropertiesByCoType )) $this->manager =  CustomPropertiesByCoType::instance();
      return $this->manager;
    } // manager
  
  } // BaseCustomPropertyByCoType

?>