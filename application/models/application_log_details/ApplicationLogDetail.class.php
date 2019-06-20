<?php

/**
 * ApplicationLogDetail class
 */
class ApplicationLogDetail extends BaseApplicationLogDetail {

	
	/**
	 * Return user who made this acction
	 *
	 * @access public
	 * @param void
	 * @return Contact
	 */
	function getApplicationLog() {
		return ApplicationLogs::findById($this->getApplicationLogId());
	} // getApplicationLog

	
} // ApplicationLogDetail

?>