<?php

/**
 * BaseMailAccount class
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
abstract class BaseMailAccount extends DataObject {

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
	 * Return value of 'email' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEmail() {
		return $this->getColumnValue('email');
	} // getEmail()

	/**
	 * Set value of 'email' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setEmail($value) {
		return $this->setColumnValue('email', $value);
	} // setEmail()


	/**
	 * Return value of 'email_addr' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEmailAddress() {
		return $this->getColumnValue('email_addr');
	} // getEmailAddress()

	/**
	 * Set value of 'email' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setEmailAddress($value) {
		return $this->setColumnValue('email_addr', $value);
	} // setEmailAddress()


	/**
	 * Return value of 'password' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getPassword() {
		return $this->getColumnValue('password');
	} // getPassword()

	/**
	 * Set value of 'password' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setPassword($value) {
		return $this->setColumnValue('password', $value);
	} // setPassword()

	/**
	 * Return value of 'server' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getServer() {
		return $this->getColumnValue('server');
	} // getServer()

	/**
	 * Set value of 'server' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setServer($value) {
		return $this->setColumnValue('server', $value);
	} // setServer()

	/**
	 * Return value of 'smtp_server' server name field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getSmtpServer() {
		return $this->getColumnValue('smtp_server');
	} // getsmtp()

	/**
	 * Set value of 'smtp_server' server name field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setSmtpServer($value) {
		return $this->setColumnValue('smtp_server', $value);
	} // setsmtp()

	/**
	 * Return value of 'smtpPort' server name field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getSmtpPort() {
		return $this->getColumnValue('smtp_port');
	} // getsmtpPort()

	/**
	 * Set value of 'smtpPort' server name field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setSmtpPort($value) {
		return $this->setColumnValue('smtp_port', $value);
	} // setsmtpPort()

	/**
	 * Return value of 'smtpUsername' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getSmtpUsername() {
		return $this->getColumnValue('smtp_username');
	} // getsmtpUsername()

	/**
	 * Set value of 'smtpUsername' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setSmtpUsername($value) {
		return $this->setColumnValue('smtp_username', $value);
	} // setsmtpUsername()

	/**
	 * Return value of 'smtpPassword' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getSmtpPassword() {
		return $this->getColumnValue('smtp_password');
	} // getsmtpPassword()

	/**
	 * Set value of 'smtpPassword' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setSmtpPassword($value) {
		return $this->setColumnValue('smtp_password', $value);
	} // setsmtpPassword()

	/**
	 * Return value of 'smtp_use_auth' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getSmtpUseAuth() {
		return $this->getColumnValue('smtp_use_auth');
	} //  getSmtpUseAuth()

	/**
	 * Set value of 'smtp_use_auth' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function  setSmtpUseAuth($value) {
		return $this->setColumnValue('smtp_use_auth', $value);
	} //  setSmtpUseAuth()

	/**
	 * Return value of 'is_imap' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsImap() {
		return $this->getColumnValue('is_imap');
	} // getIsImap()

	/**
	 * Set value of 'is_imap' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setIsImap($value) {
		return $this->setColumnValue('is_imap', $value);
	} // setIsImap()

	/**
	 * Return value of 'incoming_ssl' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIncomingSsl() {
		return $this->getColumnValue('incoming_ssl');
	} // getIncomingSsl()

	/**
	 * Set value of 'incoming_ssl' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setIncomingSsl($value) {
		return $this->setColumnValue('incoming_ssl', $value);
	} // setIncomingSsl()

	/**
	 * Return value of 'incoming_ssl_port' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getIncomingSslPort() {
		return $this->getColumnValue('incoming_ssl_port');
	} // getIncomingSslPort()

	/**
	 * Set value of 'incoming_ssl_port' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setIncomingSslPort($value) {
		return $this->setColumnValue('incoming_ssl_port', $value);
	} // setIncomingSslPort()

	/**
	 * Return value of 'del_from_server' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getDelFromServer() {
		return $this->getColumnValue('del_from_server');
	} // getDelFromServer()

	/**
	 * Set value of 'del_from_server' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setDelFromServer($value) {
		return $this->setColumnValue('del_from_server', $value);
	} // setDelFromServer()
	
	/**
	 * Return value of 'mark_read_on_server' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getMarkReadOnServer() {
		return $this->getColumnValue('mark_read_on_server');
	} // getMarkReadOnServer()
	
	/**
	 * Set value of 'mark_read_on_server' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setMarkReadOnServer($value) {
		return $this->setColumnValue('mark_read_on_server', $value);
	} // setMarkReadOnServer()


	/**
	 * Return value of 'outgoing_transport_type' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getOutgoingTrasnportType() {
		return $this->getColumnValue('outgoing_transport_type');
	} // getOutgoingTrasnportType()

	/**
	 * Set value of 'outgoing_transport_type' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setOutgoingTrasnportType($value) {
		return $this->setColumnValue('outgoing_transport_type', $value);
	} // setOutgoingTrasnportType()
	
	/**
	 * Set value of 'last_checked' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setLastChecked($value) {
		return $this->setColumnValue('last_checked', $value);
	} // setLastChecked()

	/**
	 * Return value of 'last_checked' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getLastChecked() {
		return $this->getColumnValue('last_checked');
	} // getLastChecked()
	
	/**
	 * Return value of 'is_default' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsDefault() {
		return $this->getColumnValue('is_default');
	} // getIsDefault()

	/**
	 * Set value of 'is_default' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setIsDefault($value) {
		return $this->setColumnValue('is_default', $value);
	} // setIsDefault()
	
	/**
	 * Return value of 'signature' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getSignature() {
		return $this->getColumnValue('signature');
	} // getSignature()

	/**
	 * Set value of 'signature' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setSignature($value) {
		return $this->setColumnValue('signature', $value);
	} // setSignature()

	/**
	 * Return value of 'sender_name' field
	 * @return string
	 */
	function getSenderName() {
		return $this->getColumnValue('sender_name');
	}
	
	/**
	 * Set value of 'sender_name' field
	 * @param string $value
	 * @return boolean
	 */
	function setSenderName($value) {
		return $this->setColumnValue('sender_name', $value);
	}
	
		
	/**
	 * Set value of 'last_error_date' field
	 *
	 * @access public
	 * @param DateTimeValie $value
	 * @return boolean
	 */
	function setLastErrorDate($value) {
		return $this->setColumnValue('last_error_date', $value);
	} // setLastErrorDate()

	/**
	 * Return value of 'last_error_date' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getLastErrorDate() {
		return $this->getColumnValue('last_error_date');
	} // getLastErrorDate()
	
	
	/**
	 * Return value of 'last_error_msg' field
	 * @return string
	 */
	function getLastErrorMsg() {
		return $this->getColumnValue('last_error_msg');
	}
	
	/**
	 * Set value of 'last_error_msg' field
	 * @param string $value
	 * @return boolean
	 */
	function setLastErrorMsg($value) {
		return $this->setColumnValue('last_error_msg', $value);
	}
		
	/**
	 * Return value of 'sync_addr' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getSyncAddr() {
		return $this->getColumnValue('sync_addr');
	} // getsyncAddr()

	/**
	 * Set value of 'sync_addr' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setSyncAddr($value) {
		return $this->setColumnValue('sync_addr', $value);
	} // setSyncAddr()
	
		
	/**
	 * Return value of 'sync_pass' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getSyncPass() {
		return $this->getColumnValue('sync_pass');
	} // getSyncPass()

	/**
	 * Set value of 'sync_pass' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setSyncPass($value) {
		return $this->setColumnValue('sync_pass', $value);
	} // setSyncPass()
	
	/**
	 * Return value of 'sync_server' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getSyncServer() {
		return $this->getColumnValue('sync_server');
	} // getSyncServer()

	/**
	 * Set value of 'sync_server' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setSyncServer($value) {
		return $this->setColumnValue('sync_server', $value);
	} // setSyncServer()
	
	/**
	 * Return value of 'sync_ssl' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getSyncSsl() {
		return $this->getColumnValue('sync_ssl');
	} // getSyncSsl()

	/**
	 * Set value of 'sync_ssl' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setSyncSsl($value) {
		return $this->setColumnValue('sync_ssl', $value);
	} // setSyncSsl()
	
	/**
	 * Return value of 'sync_ssl_port' field
	 *
	 * @access public
	 * @param void
	 * @return int
	 */
	function getSyncSslPort() {
		return $this->getColumnValue('sync_ssl_port');
	} // getSyncSslPort()

	/**
	 * Set value of 'sync_ssl_port' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setSyncSslPort($value) {
		return $this->setColumnValue('sync_ssl_port', $value);
	} // setSyncSslPort()	

	/**
	 * Return value of 'sync_folder' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getSyncFolder() {
		return $this->getColumnValue('sync_folder');
	} // getSyncFolder()

	/**
	 * Set value of 'sync_folder' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setSyncFolder($value) {
		return $this->setColumnValue('sync_folder', $value);
	} // setSyncSslPort()	
	
	
	
	function getMemberId() {
		return $this->getColumnValue('member_id');
	}
	
	function setMemberId($memberId) {
		return $this->setColumnValue("member_id", $memberId);
	}
	
	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return MailAccounts
	 */
	function manager() {
		if(!($this->manager instanceof MailAccounts)) $this->manager = MailAccounts::instance();
		return $this->manager;
	} // manager

} // BaseMailAccount

?>