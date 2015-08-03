<?php

/**
 * MailAccountContacts
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class MailAccountContacts extends BaseMailAccountContacts {
	
	const MA_NO_ERROR = 0;
	const MA_ERROR_UNREAD = 1;
	const MA_ERROR_READ = 2;

	function getByAccount($account) {
		return MailAccountContacts::findAll(array('conditions' => array('`account_id` = ?', $account->getId())));
	}
	
	function getByContact($user) {
		return MailAccountContacts::findAll(array('conditions' => array('`contact_id` = ?', $user->getId())));
	}
	
	function getByAccountAndContact($account, $user) {
		if ($account instanceof MailAccount && $user instanceof Contact) {
			return MailAccountContacts::findOne(array('conditions' => array('`account_id` = ? AND `contact_id` = ?', $account->getId(), $user->getId())));
		}
		return null;
	}
	
	function deleteByAccount($account) {
		return MailAccountContacts::delete(array('`account_id` = ?', $account->getId()));
	}
	
	function deleteByContact($user) {
		return MailAccountContacts::delete(array('`contact_id` = ?', $user->getId()));
	}
	
	function countByAccount($account) {
		return MailAccountContacts::count(array('`account_id` = ?', $account->getId()));
	}
	
} 