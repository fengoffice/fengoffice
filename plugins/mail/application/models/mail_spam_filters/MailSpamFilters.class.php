<?php
/**
 * MailSpamFilters
 * Generado el 9/2/2012
 * 
 */
class MailSpamFilters extends BaseMailSpamFilters {

	function getByAccount($account) {
		return MailSpamFilters::findAll(array('conditions' => array('`account_id` = ?', $account->getId())));
	}
        
        function getRow($account) {
		return MailSpamFilters::findAll(array('conditions' => array('`account_id` = ? AND `text` = ?', $account->getAccountId(), $account->getFrom())));
	}
        
        function getFrom($account_id,$from) {
		return MailSpamFilters::findAll(array('conditions' => array('`account_id` = ? AND `text` = ?', $account_id, $from)));
	}
} 