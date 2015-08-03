<?php

/**
 * BaseMailAccountContact class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
abstract class BaseMailAccountContact extends DataObject {

	// -------------------------------------------------------
	//  Access methods
	// -------------------------------------------------------

	/**
	 * Return value of 'id' field
	 * @return integer
	 */
	function getId() {
		return $this->getColumnValue('id');
	}
	 
	/**
	 * Set value of 'id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setId($value) {
		return $this->setColumnValue('id', $value);
	}
	 
	/**
	 * Return value of 'account_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getAccountId() {
		return $this->getColumnValue('account_id');
	} // getAccountId()

	/**
	 * Set value of 'account_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setAccountId($value) {
		return $this->setColumnValue('account_id', $value);
	} // setAccountId()

	/**
	 * Return value of 'contact_id' field
	 * @return integer
	 */
	function getContactId() {
		return $this->getColumnValue('contact_id');
	}
	 
	/**
	 * Set value of 'contact_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setContactId($value) {
		return $this->setColumnValue('contact_id', $value, true);
	}
	
	/**
	 * Return value of 'can_edit' field
	 * @return boolean
	 */
	function getCanEdit() {
		return $this->getColumnValue('can_edit');
	}
	 
	/**
	 * Set value of 'can_edit' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setCanEdit($value) {
		return $this->setColumnValue('can_edit', $value);
	}
	
	/**
	 * Return value of 'is_default' field
	 * @return boolean
	 */
	function getIsDefault() {
		return $this->getColumnValue('is_default');
	}
	 
	/**
	 * Set value of 'is_default' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setIsDefault($value) {
		return $this->setColumnValue('is_default', $value);
	}
	
	/**
	 * Return value of 'signature' field
	 * @return string
	 */
	function getSignature() {
		return $this->getColumnValue('signature');
	}
	 
	/**
	 * Set value of 'signature' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setSignature($value) {
		return $this->setColumnValue('signature', $value);
	}

	/**
	 * Return value of 'sender_name' field
	 * @return string
	 */
	function getSenderName() {
		return $this->getColumnValue('sender_name');
	}
	 
	/**
	 * Set value of 'sender_name' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setSenderName($value) {
		return $this->setColumnValue('sender_name', $value);
	}
	
	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return MailAccountContacts
	 */
	function manager() {
		if(!($this->manager instanceof MailAccountContacts)) $this->manager = MailAccountContacts::instance();
		return $this->manager;
	}
	
	/**
	 * Return value of 'last_error_state' field
	 * @return integer (MA_NO_ERROR, MA_ERROR_UNREAD, MA_ERROR_READ)
	 */
	function getLastErrorState() {
		return $this->getColumnValue('last_error_state');
	}
	 
	/**
	 * Set value of 'last_error_state' field
	 *
	 * @access public
	 * @param integer $value (MA_NO_ERROR, MA_ERROR_UNREAD, MA_ERROR_READ)
	 * @return boolean
	 */
	function setLastErrorState($value) {
		return $this->setColumnValue('last_error_state', $value);
	}

} 
