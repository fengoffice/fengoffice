<?php

/**
 * BaseMailSpamFilter class
 * Generado el 9/2/2012
 * 
 */
abstract class BaseMailSpamFilter extends DataObject {

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
	 * Return value of 'text_type' field
	 * @return string
	 */
	function getTextType() {
		return $this->getColumnValue('text_type');
	}
	 
	/**
	 * Set value of 'text_type' field
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	function setTextType($value) {
		return $this->setColumnValue('text_type', $value, true);
	}
	
	/**
	 * Return value of 'text' field
	 * @return string
	 */
	function getText() {
		return $this->getColumnValue('text');
	}
	 
	/**
	 * Set value of 'text' field
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	function setText($value) {
		return $this->setColumnValue('text', $value);
	}
	
	/**
	 * Return value of 'spam_state' field
	 * @return string
	 */
	function getSpamState() {
		return $this->getColumnValue('spam_state');
	}
	 
	/**
	 * Set value of 'spam_state' field
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	function setSpamState($value) {
		return $this->setColumnValue('spam_state', $value);
	}
	
	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return MailMailSpamFilters
	 */
	function manager() {
		if(!($this->manager instanceof MailSpamFilters)) $this->manager = MailSpamFilters::instance();
		return $this->manager;
	}
} 