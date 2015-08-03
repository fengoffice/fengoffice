<?php

  /**
  * BaseMemberCustomPropertyValue class
  * Written on Thu, 4 Oct 2009 14:51:09 -0300
  */
  abstract class BaseMemberCustomPropertyValue extends DataObject {
  
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
    } // getObjectId()
    
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
    * Return value of 'object_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getMemberId() {
      return $this->getColumnValue('member_id');
    } // getObjectId()
    
    /**
    * Set value of 'object_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setMemberId($value) {
      return $this->setColumnValue('member_id', $value);
    } // setObjectId() 
    
    /**
    * Return value of 'custom_property_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getCustomPropertyId() {
      return $this->getColumnValue('custom_property_id');
    } // getMemberCustomPropertyId()
    
    /**
    * Set value of 'custom_property_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setCustomPropertyId($value) {
      return $this->setColumnValue('custom_property_id', $value);
    } // setMemberCustomPropertyId() 
    
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
    * @return MemberCustomPropertyValue 
    */
    function manager() {
      if(!($this->manager instanceof MemberCustomPropertyValues )) $this->manager =  MemberCustomPropertyValues::instance();
      return $this->manager;
    } // manager
  
  } // BaseMemberCustomPropertyValue

?>