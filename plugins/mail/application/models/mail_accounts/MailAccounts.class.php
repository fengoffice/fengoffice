<?php

/**
 * MailAccounts
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class MailAccounts extends BaseMailAccounts
{

	private $accounts_cache = array();

	function getAccountById($account_id)
	{
		if (isset($this) && instance_of($this, 'MailAccounts')) {
			if (!isset($this->accounts_cache[$account_id])) {
				$this->accounts_cache[$account_id] = $this->findById($account_id);
			}
			return array_var($this->accounts_cache, $account_id);
		} else {
			return MailAccounts::instance()->getAccountById($account_id);
		}
	}

	/**
	 * Return Mail accounts by user
	 *
	 * @param user
	 * @return array
	 */
	static function getMailAccountsByUser(Contact $user)
	{
		//return MailAccounts::instance()->findAll(array("conditions"=>"contact_id = ".logged_user()->getId()));

		$accounts = array();
		$accountUsers = MailAccountContacts::getByContact($user);
		foreach ($accountUsers as $au) {
			$account = $au->getAccount();
			if ($account instanceof MailAccount) {
				$accounts[] = $account;
			}
		}
		return self::sortAccountsByName($accounts);
	}

	/**
	 * Keep only accounts that are not excluded from synchronizing.
	 *
	 * @param MailAccount[] $accounts
	 * @return MailAccount[]
	 */
	static function filterSynchronizableAccounts($accounts) {
		if (!is_array($accounts)) {
			return array();
		}
		$filtered = array();
		foreach ($accounts as $account) {
			if ($account instanceof MailAccount && $account->shouldSynchronize()) {
				$filtered[] = $account;
			}
		}
		return $filtered;
	}

	static function getMailAccountsEditByUser(Contact $user)
	{
		//return MailAccounts::instance()->findAll(array("conditions"=>"contact_id = ".logged_user()->getId()));

		$accounts = array();
		$accountUsers = MailAccountContacts::getByContact($user);
		foreach ($accountUsers as $au) {
			$account = $au->getAccount();
			if ($account instanceof MailAccount) {
				$accounts[] = $account;
			}
		}
		return self::sortAccountsByName($accounts);
	}

	/**
	 * Sort mail accounts alphabetically by display name (fallback: email).
	 *
	 * @param MailAccount[] $accounts
	 * @return MailAccount[]
	 */
	static function sortAccountsByName($accounts) {
		if (!is_array($accounts) || count($accounts) < 2) {
			return $accounts;
		}
		usort($accounts, function ($a, $b) {
			$name_a = $a instanceof MailAccount ? trim($a->getName()) : '';
			$name_b = $b instanceof MailAccount ? trim($b->getName()) : '';
			if ($name_a === '') {
				$name_a = $a instanceof MailAccount ? $a->getEmail() : '';
			}
			if ($name_b === '') {
				$name_b = $b instanceof MailAccount ? $b->getEmail() : '';
			}
			return strcasecmp($name_a, $name_b);
		});
		return $accounts;
	}
} // MailAccounts 

?>