<?php

  /**
  * BaseMemberRestriction class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseMemberRestriction extends DataObject {
  
  	  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
    /**
    * Return value of 'member_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getMemberId() {
      return $this->getColumnValue('member_id');
    } // getMemberId()
    
    /**
    * Set value of 'member_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setMemberId($value) {
      return $this->setColumnValue('member_id', $value);
    } // setMemberId() 
    
    /**
    * Return value of 'restricted_member_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getRestrictedMemberId() {
      return $this->getColumnValue('restricted_member_id');
    } // getRestrictedMemberId()
    
    /**
    * Set value of 'restricted_member_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setRestrictedMemberId($value) {
      return $this->setColumnValue('restricted_member_id', $value);
    } // setRestrictedMemberId() 
    
    /**
    * Return value of 'order' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getOrder() {
      return $this->getColumnValue('order');
    } // getOrder()
    
    /**
    * Set value of 'order' field
    *
    * @access public   
    * @param string $value
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
    * @return MemberRestrictions 
    */
    function manager() {
      if(!($this->manager instanceof MemberRestrictions)) $this->manager = MemberRestrictions::instance();
      return $this->manager;
    } // manager
  
  } // BaseMemberRestriction 

?>