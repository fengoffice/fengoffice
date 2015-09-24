<?php

  /**
  * BaseMember class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseMember extends DataObject {
  
  	  
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
    * Return value of 'dimension_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getDimensionId() {
      return $this->getColumnValue('dimension_id');
    } // getDimensionId()
    
    /**
    * Set value of 'dimension_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setDimensionId($value) {
      return $this->setColumnValue('dimension_id', $value);
    } // setDimensionId()
    
    /**
    * Return value of 'object_type_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getObjectTypeId() {
      return $this->getColumnValue('object_type_id');
    } // getObjectTypeId()
    
    /**
    * Set value of 'object_type_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setObjectTypeId($value) {
      return $this->setColumnValue('object_type_id', $value);
    } // setObjectTypeId()
    
    /**
    * Return value of 'parent_member_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getParentMemberId() {
      return $this->getColumnValue('parent_member_id');
    } // getParentMemberId()
    
    /**
    * Set value of 'parent_member_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setParentMemberId($value) {
      return $this->setColumnValue('parent_member_id', $value);
    } // setParentMemberId() 
    
    /**
    * Return value of 'depth' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getDepth() {
      return $this->getColumnValue('depth');
    } // getDepth()
    
    /**
    * Set value of 'depth' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setDepth($value) {
      return $this->setColumnValue('depth', $value);
    } // setDepth() 
    
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
    * Return value of 'object_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getObjectId() {
      return $this->getColumnValue('object_id');
    } // getObjectId()
    
    /**
    * Set value of 'object_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setObjectId($value) {
      return $this->setColumnValue('object_id', $value);
    } // setObjectId() 

    /**
    * Return value of 'archived_on' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getArchivedOn() {
      return $this->getColumnValue('archived_on');
    } // getArchivedOn()
    
    /**
    * Set value of 'archived_on' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setArchivedOn($value) {
      return $this->setColumnValue('archived_on', $value);
    } // setArchivedOn() 
      
    /**
    * Return value of 'archived_by_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getArchivedById() {
      return $this->getColumnValue('archived_by_id');
    } // getArchivedById()
    
    /**
    * Set value of 'archived_by_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setArchivedById($value) {
      return $this->setColumnValue('archived_by_id', $value);
    } // setArchivedById()
    
    /**
    * Return value of 'color' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getColor() {
      return $this->getColumnValue('color');
    } // getColor()
    
    /**
    * Set value of 'color' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setColor($value) {
      return $this->setColumnValue('color', $value);
    } // setColor() 
    
    
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
    * @return Members 
    */
    function manager() {
      if(!($this->manager instanceof Members)) $this->manager = Members::instance();
      return $this->manager;
    } // manager
  
  } // BaseMember 

?>