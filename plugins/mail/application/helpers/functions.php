<?php

function can_send_outbox_in_background() {
	return is_exec_available();
}

function send_outbox_emails_in_background($account) {
	
	if (!$account instanceof MailAccount) {
		Logger::log("Cant send outbox emails in background, account is null");
	}
	
	$user = logged_user();
	
	if (substr(php_uname(), 0, 7) == "Windows" || !can_send_outbox_in_background()) {
		//pclose(popen("start /B ". $command, "r"));
		$from_time = DateTimeValueLib::now();
		$from_time = $from_time->add('h', -24);
		
		$mc = new MailController();
		$mc->send_outbox_mails($user, $account, $from_time);
		
	} else {
		
		$script_path = ROOT . "/plugins/mail/application/helpers/send_outbox_emails.php";
		$command = "nice -n19 ".PHP_PATH." $script_path ".ROOT." ".$user->getId()." ".$user->getTwistedToken()." ".$account->getId();
		exec("$command > /dev/null &");
		
	}
}