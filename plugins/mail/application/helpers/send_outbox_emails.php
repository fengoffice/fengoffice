<?php
	chdir($argv[1]);
	define("CONSOLE_MODE", true);
	define('PUBLIC_FOLDER', 'public');
	include "init.php";
	
	session_commit(); // we don't need sessions
	@set_time_limit(0); // don't limit execution of cron, if possible
	ini_set('memory_limit', '1G');
	
	

	Env::useHelper('functions', 'mail');
	
	$user_id = array_var($argv, 2);
	$token = array_var($argv, 3);
	$account_id = array_var($argv, 4);
	
	// log user in
	$user = Contacts::instance()->findById($user_id);
	if(!($user instanceof Contact) || !$user->isValidToken($token)) {
		throw new Exception("Cannot login with user $user_id and token '$token'");
	}

	CompanyWebsite::instance()->setLoggedUser($user, false, false, false);
	
	$account = MailAccounts::instance()->findById($account_id);
	if (!$account instanceof MailAccount) {
		$_GET['acc_id'] = $account_id;
	}
	
	$from_time = DateTimeValueLib::now();
	$from_time = $from_time->add('h', -24);
	
	try {
		// send the outobox for the account
		$mc = new MailController();
		$mc->send_outbox_mails($user, $account, $from_time);
		
	} catch (Exception $e) {
		Logger::log("Error sending outbox emails in background:\n".$e->getMessage());
		Logger::log($e->getTraceAsString());
	}
	
	
	