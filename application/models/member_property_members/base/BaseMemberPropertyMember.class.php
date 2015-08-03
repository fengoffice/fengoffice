<?php

  /**
  * BaseMemberPropertyMember class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseMemberPropertyMember extends DataObject {
  
  	  
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
    * Return value of 'association_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getAssociationId() {
      return $this->getColumnValue('association_id');
    } // getAssociationId()
    
    
    /**
    * Set value of 'association_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setAssociationId($value) {
      return $this->setColumnValue('association_id', $value);
    } // setAssociationId() 
    
    
    /**
    * Return value of 'code' field
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
    * Return value of 'property_member_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getPropertyMemberId() {
      return $this->getColumnValue('property_member_id');
    } // getPropertyMemberId()
    
    /**
    * Set value of 'property_member_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setPropertyMemberId($value) {
      return $this->setColumnValue('property_member_id', $value);
    } // setPropertyMemberId() 
    
    /**
    * Return value of 'is_active' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getIsActive() {
      return $this->getColumnValue('is_active');
    } // getIsRoot()
    
    /**
    * Set value of 'is_active' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setIsActive($value) {
      return $this->setColumnValue('is_active', $value);
    } // setIsActive() 
    
    /**
    * Return value of 'created_on' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getCreatedOn() {
      return $this->getColumnValue('created_on');
    } // getCreatedOn()
    
    /**
    * Set value of 'created_on' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setCreatedOn($value) {
      return $this->setColumnValue('created_on', $value);
    } // setCreatedOn() 
    
    /**
    * Return value of 'created_by_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getCreatedById() {
      return $this->getColumnValue('created_by_id');
    } // getCreatedById()
    
    /**
    * Set value of 'created_by_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setCreatedById($value) {
      return $this->setColumnValue('created_by_id', $value);
    } // setCreatedById() 
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return MemberPropertyMembers 
    */
    function manager() {
      if(!($this->manager instanceof MemberPropertyMembers)) $this->manager = MemberPropertyMembers::instance();
      return $this->manager;
    } // manager
  
  } // BaseMemberPropertyMember 

?>