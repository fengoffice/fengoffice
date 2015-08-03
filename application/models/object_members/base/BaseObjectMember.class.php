<?php

  /**
  * BaseObjectMember class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseObjectMember extends DataObject {
  
  	  
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
    * Return value of 'member_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getMemberId() {
      return $this->getColumnValue('member_id');
    } // getMemberId()
    
    /**
    * Set value of 'member_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setMemberId($value) {
      return $this->setColumnValue('member_id', $value);
    } // setMemberId()
    
    /**
    * Return value of 'is_optimization' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getIsOptimization() {
      return $this->getColumnValue('is_optimization');
    } // getIsOptimization()
    
    /**
    * Set value of 'is_optimization' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setIsOptimization($value) {
      return $this->setColumnValue('is_optimization', $value);
    } // setIsOptimization() 
    
         
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ObjectMembers 
    */
    function manager() {
      if(!($this->manager instanceof ObjectMembers)) $this->manager = ObjectMembers::instance();
      return $this->manager;
    } // manager
  
  } // BaseObjectMember 

?>