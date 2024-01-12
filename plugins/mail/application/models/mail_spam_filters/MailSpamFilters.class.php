<?php
/**
 * MailSpamFilters
 * Generado el 9/2/2012
 * 
 */
class MailSpamFilters extends BaseMailSpamFilters {

	static function getByAccount($account) {
		return MailSpamFilters::instance()->findAll(array('conditions' => array('`account_id` = ?', $account->getId())));
	}
        
    static function getRow($account) {
		return MailSpamFilters::instance()->findAll(array('conditions' => array('`account_id` = ? AND `text` = ?', $account->getAccountId(), $account->getFrom())));
	}
        
    static function getFrom($account_id,$from) {
		return MailSpamFilters::instance()->findAll(array('conditions' => array('`account_id` = ? AND `text` = ?', $account_id, $from)));
	}
} 