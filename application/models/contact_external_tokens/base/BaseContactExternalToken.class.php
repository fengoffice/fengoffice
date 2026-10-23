<?php

  /**
  * BaseContactExternalToken class
  */
abstract class BaseContactExternalToken extends DataObject {
  	
  
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
    * Return value of 'contact_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getContactId() {
      return $this->getColumnValue('contact_id');
    } // getContactId()
    
    /**
    * Set value of 'contact_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setContactId($value) {
      return $this->setColumnValue('contact_id', $value);
    } // setContactId() 
    
    /**
    * Return value of 'token' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getToken() {
      return $this->getColumnValue('token');
    } // getToken()
    
    /**
    * Set value of 'token' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setToken($value) {
      return $this->setColumnValue('token', $value);
    } // setToken() 
    
    /**
     * Return value of 'type' field
     *
     * @access public
     * @param void
     * @return string
     */
    function getType() {
        return $this->getColumnValue('type');
    } // getType()
    
    /**
     * Set value of 'type' field
     *
     * @access public
     * @param string $value
     * @return boolean
     */
    function setType($value) {
        return $this->setColumnValue('type', $value);
    } // setType() 
    
    /**
     * Return value of 'external_key' field
     *
     * @access public
     * @param void
     * @return String
     */
    function getExternalKey() {
        return $this->getColumnValue('external_key');
    } // getExternalKey()
    
    /**
     * Set value of 'external_key' field
     *
     * @access public
     * @param String $value
     * @return boolean
     */
    function setExternalKey($value) {
        return $this->setColumnValue('external_key', $value);
    } // setExternalKey()
    
    /**
     * Return value of 'external_name' field
     *
     * @access public
     * @param void
     * @return String
     */
    function getExternalName() {
        return $this->getColumnValue('external_name');
    } // getExternalName()
    
    /**
     * Set value of 'external_name' field
     *
     * @access public
     * @param String $value
     * @return boolean
     */
    function setExternalName($value) {
        return $this->setColumnValue('external_name', $value);
    } // setExternalName()
    
    /**
    * Return value of 'expired_date' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getExpiredDate() {
      return $this->getColumnValue('expired_date');
    } // getExpiredDate()
    
    /**
    * Set value of 'expired_date' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setExpiredDate($value) {
      return $this->setColumnValue('expired_date', $value);
    } // setExpiredDate()     
 
    /**
     * Return value of 'created_date' field
     *
     * @access public
     * @param void
     * @return DateTimeValue
     */
    function getCreatedDate() {
        return $this->getColumnValue('created_date');
    } // getCreatedDate()
    
    /**
     * Set value of 'created_date' field
     *
     * @access public
     * @param DateTimeValue $value
     * @return boolean
     */
    function setCreatedDate($value) {
        return $this->setColumnValue('created_date', $value);
    } // setCreatedDate()   
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ContactExternalTokens 
    */
    function manager() {
      if(!($this->manager instanceof ContactExternalTokens)) $this->manager = ContactExternalTokens::instance();
      return $this->manager;
    } // manager
    
  
} // BaseContactExternalToken

?>