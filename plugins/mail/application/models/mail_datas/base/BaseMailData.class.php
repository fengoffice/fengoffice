<?php

/**
 * BaseMailData class
 * 
 * The purpose of this class is to separate email data from email metadata
 * so that queries on table mail_contents are faster.
 *
 * @author Ignacio de Soto <ignacio.desoto@fengoffice.com>
 */
abstract class BaseMailData extends DataObject {
  
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
	 * Return value of 'cc' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getCc() {
		return $this->getColumnValue('cc');
	} // getCc()

	/**
	 * Set value of 'cc' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setCc($value) {
		return $this->setColumnValue('cc', $value);
	} // setCc()
	
	/**
	 * Return value of 'bcc' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getBcc() {
		return $this->getColumnValue('bcc');
	} // getBcc()

	/**
	 * Set value of 'bcc' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setBcc($value) {
		return $this->setColumnValue('bcc', $value);
	} // setBcc()

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
	 * Return value of 'content' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getContent() {
		return $this->getColumnValue('content');
	} // getContent()

	/**
	 * Set value of 'content' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setContent($value) {
		return $this->setColumnValue('content', $value);
	} // setContent()

	/**
	 * Return value of 'body_plain' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getBodyPlain() {
		return $this->getColumnValue('body_plain');
	} // getBodyPlain()

	/**
	 * Set value of 'body_plain' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setBodyPlain($value) {
		return $this->setColumnValue('body_plain', $value);
	} // setBodyPlain()

	/**
	 * Return value of 'body_html' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getBodyHtml() {
		return $this->getColumnValue('body_html');
	} // getBodyHtml()

	/**
	 * Set value of 'body_html' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setBodyHtml($value) {
		return $this->setColumnValue('body_html', $value);
	} // setBodyHtml()
	    
    
	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return MailDatas
	 */
	function manager() {
		if(!($this->manager instanceof MailDatas)) $this->manager = MailDatas::instance();
		return $this->manager;
	} // manager

} // BaseMailData

?>