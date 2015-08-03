<?php

/**
 * BaseExternalCalendarUser class
 * Generado el 22/2/2012
 * 
 */
abstract class BaseExternalCalendarUser extends DataObject {

	// -------------------------------------------------------
	//  Access methods
	// -------------------------------------------------------
    
        /**
	 * Return value of 'id' field
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
	 * @return integer
	 */
	function setId($value) {
		return $this->setColumnValue('id', $value);
	} // setId()
        
	/**
	 * Return value of 'contact_id' field
	 *
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
	 * @return integer
	 */
	function setContactId($value) {
		return $this->setColumnValue('contact_id', $value);
	} // setContactId()
	 
	/**
	 * Return value of 'auth_user' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getAuthUser() {
		return $this->getColumnValue('auth_user');
	} // getAuthUser()

	/**
	 * Set value of 'auth_user' field
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	function setAuthUser($value) {
		return $this->setColumnValue('auth_user', $value);
	} // setAuthUser()
        
        /**
	 * Return value of 'auth_pass' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getAuthPass() {
		return $this->getColumnValue('auth_pass');
	} // getAuthPass()

	/**
	 * Set value of 'auth_pass' field
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	function setAuthPass($value) {
		return $this->setColumnValue('auth_pass', $value);
	} // setAuthPass()
        
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
	 * @return string
	 */
	function setType($value) {
		return $this->setColumnValue('type', $value);
	} // setType()
        
        /**
	 * Return value of 'sync' field
	 * @return integer
	 */
	function getSync() {
		return $this->getColumnValue('sync');
	} // getSync()

	/**
	 * Set value of 'sync' field
	 *
	 * @access public
	 * @param integer $value
	 * @return integer
	 */
	function setSync($value) {
		return $this->setColumnValue('sync', $value);
	} // setSync()
        
        /**
	 * Return value of 'related_to' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getRelatedTo() {
		return $this->getColumnValue('related_to');
	} // getRelatedTo()

	/**
	 * Set value of 'related_to' field
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	function setRelatedTo($value) {
		return $this->setColumnValue('related_to', $value);
	} // setRelatedTo()
        
        /**
        * Return manager instance
        *
        * @access protected
        * @param void
        * @return ExternalCalendarUsers 
        */
        function manager() {
          if(!($this->manager instanceof ExternalCalendarUsers)) $this->manager = ExternalCalendarUsers::instance();
          return $this->manager;
        } // manager
} 
?>