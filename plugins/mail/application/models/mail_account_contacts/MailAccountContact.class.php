<?php

/**
 * MailAccountContact class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class MailAccountContact extends BaseMailAccountContact {

	function getContact() {
		return Contacts::instance()->findById($this->getContactId());
	}
	
	function getAccount() {
		return MailAccounts::instance()->findById($this->getAccountId());
	}

}
?>