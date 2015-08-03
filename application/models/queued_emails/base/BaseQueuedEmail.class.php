<?php

/**
 * BaseQueuedEmail class
 *
 * @author Ignacio de Soto <ignacio.desoto@fengoffice.com>
 */
abstract class BaseQueuedEmail extends DataObject {

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
	 * Return value of 'to' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getTo() {
		return $this->getColumnValue('to');
	} // getTo()

	/**
	 * Set value of 'to' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setTo($value) {
		return $this->setColumnValue('to', $value);
	} // setTo()
	
	/**
	 * Return value of 'to' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getCc() {
		return $this->getColumnValue('cc');
	} // getTo()
	
	/**
	 * Set value of 'to' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setCc($value) {
		return $this->setColumnValue('cc', $value);
	}
	
	/**
	 * Return value of 'to' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getBcc() {
		return $this->getColumnValue('bcc');
	} // getTo()
	
	/**
	 * Set value of 'to' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setBcc($value) {
		return $this->setColumnValue('bcc', $value);
	}

	/**
	 * Return value of 'from' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getFrom() {
		return $this->getColumnValue('from');
	} // getFrom()

	/**
	 * Set value of 'from' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setFrom($value) {
		return $this->setColumnValue('from', $value);
	} // setFrom()

	/**
	 * Return value of 'subject' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getSubject() {
		return $this->getColumnValue('subject');
	} // getSubject()

	/**
	 * Set value of 'subject' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setSubject($value) {
		return $this->setColumnValue('subject', $value);
	} // setSubject()

	/**
	 * Return value of 'body' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getBody() {
		return $this->getColumnValue('body');
	} // getBody()

	/**
	 * Set value of 'body' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setBody($value) {
		return $this->setColumnValue('body', $value);
	} // setBody()

	/**
	 * Return value of 'attachments' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getAttachments() {
		return $this->getColumnValue('attachments');
	} // getAttachments()

	/**
	 * Set value of 'attachments' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setAttachments($value) {
		return $this->setColumnValue('attachments', $value);
	} // setAttachments()

	/**
	 * Return value of 'timestamp' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getTimestamp() {
		return $this->getColumnValue('timestamp');
	} // getTimestamp()

	/**
	 * Set value of 'timestamp' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setTimestamp($value) {
		return $this->setColumnValue('timestamp', $value);
	} // setTimestamp()

	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return QueuedEmails
	 */
	function manager() {
		if(!($this->manager instanceof QueuedEmails)) $this->manager = QueuedEmails::instance();
		return $this->manager;
	} // manager

} // BaseQueuedEmail

?>