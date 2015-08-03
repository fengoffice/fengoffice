<?php

/**
 * MailAccountContact class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class MailAccountContact extends BaseMailAccountContact {

	function getContact() {
		return Contacts::findById($this->getContactId());
	}
	
	function getAccount() {
		return MailAccounts::findById($this->getAccountId());
	}

}
?>