<?php

		chdir(dirname(__FILE__).'/../..');
		define("CONSOLE_MODE", true);
		define('PUBLIC_FOLDER', 'public');
		include "init.php";

		if (!config_option("sent_mails_sync")){
				echo(lang('no access permissions'));
				?><br><a href="<?php echo ROOT_URL?>/index.php?c=access&a=index" target="_top">Go back to Feng Office</a><?php 		
				return;
		}
		
		set_time_limit(0);		
		if (logged_user()->isGuest()) {
			echo(lang('no access permissions'));
			?><br><a href="<?php echo ROOT_URL?>/index.php?c=access&a=index" target="_top">Go back to Feng Office</a><?php
			return;
		}

		$id = get_id();
		if ($id>0){
			$account = MailAccounts::findById($id);
		}
		else{ 
			$email_address = array_var($_GET, 'email');			
			$user_name = array_var($_GET, 'username');
			if (isset ($email_address) && isset ($user_name)){
				$user_conditions = array("conditions" => array("`username`='".$user_name."'"));
			
				$user = Users::findOne($user_conditions);
			
				if (!isset ($user)){	
					echo(lang('cant find user'));
					?><br><a href="<?php echo ROOT_URL?>/index.php?c=access&a=index" target="_top">Go back to Feng Office</a><?php 		
					return;
				}			
											
				$account_conditions = array("conditions" => array("`email_addr`='".$email_address."' AND `user_id`='".$user->getId()."'"));
				$account = MailAccounts::findOne($account_conditions);
				if (!isset ($account)){		
					echo(lang('cant find account'));
					?><br><a href="<?php echo ROOT_URL?>/index.php?c=access&a=index" target="_top">Go back to Feng Office</a><?php
					return;
				}					
			} 
		}			
		
		if(!($account instanceof MailAccount)) {
			echo(lang('mailAccount dnx'));			
			return;
		}	

		$pass = $account->getSyncPass();		
		$server = $account->getSyncServer();
		$folder = $account->getSyncFolder();
		$address = $account->getSyncAddr();		
		if($pass == null || $server == null || $folder == null || $address == null) {		
			echo(lang('cant sync account'));	
		    ?><br><a href="<?php echo ROOT_URL?>/index.php?c=access&a=index" target="_top">Go back to Feng Office</a><?php
			return;
		}			
		$conditions = array("conditions" => array("`sync`=0 AND `state` = 3 AND `account_id` =".$account->getId()));			
		
		$check_sync_box = MailUtilities::checkSyncMailbox($server, $account->getSyncSsl(), $account->getOutgoingTrasnportType(), $account->getSyncSslPort(), $folder, $address, $pass);		
				
		if ($check_sync_box){
			$sent_mails = MailContents::findAll($conditions);			
			if (count($sent_mails)==0){
				echo(lang('mails on imap acc already sync'));											
				?><br><a href="<?php echo ROOT_URL?>/index.php?c=access&a=index" target="_top">Go back to Feng Office</a><?php
				return;
			}		
			foreach ($sent_mails as $mail){			
				try{
					DB::beginWork();				
					$content = $mail->getContent();		
					MailUtilities::sendToServerThroughIMAP($server, $account->getSyncSsl(), $account->getOutgoingTrasnportType(), $account->getSyncSslPort(), $folder, $address, $pass, $content);			
					$mail->setSync(true);
					$mail->save();
					DB::commit();				
				}
				catch(Exception $e){			
					DB::rollback();
				}						
			}			
			echo(lang('sync complete'));			
		}else{
			echo(lang('invalid sync settings'));			
		}
		?><br><a href="<?php echo ROOT_URL?>/index.php?c=access&a=index" target="_top">Go back to Feng Office</a>
			



