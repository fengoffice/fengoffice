<?php

  /**
  * MailAccounts
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  class MailAccounts extends BaseMailAccounts {
  	
	private $accounts_cache = array();
	
	function getAccountById($account_id) {
		if(isset($this) && instance_of($this, 'MailAccounts')) {
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
  	function getMailAccountsByUser(Contact $user){	
  		//return MailAccounts::findAll(array("conditions"=>"contact_id = ".logged_user()->getId()));
  		
  		$accounts = array();
  		$accountUsers = MailAccountContacts::getByContact($user);
  		foreach ($accountUsers as $au) {
  			$account = $au->getAccount();
  			if ($account instanceof MailAccount) {
  				$accounts[] = $account;
  			}
  		}
  		return $accounts;
  	}
        
        function getMailAccountsEditByUser(Contact $user){	
  		//return MailAccounts::findAll(array("conditions"=>"contact_id = ".logged_user()->getId()));
  		
  		$accounts = array();
  		$accountUsers = MailAccountContacts::getByContact($user);
  		foreach ($accountUsers as $au) {
                        $account = $au->getAccount();
                        if ($account instanceof MailAccount) {
                                $accounts[] = $account;
                        }
  		}
  		return $accounts;
  	}
  } // MailAccounts 

?>