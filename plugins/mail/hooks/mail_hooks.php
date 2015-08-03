<?php
Hook::register('mail');

function mail_render_administration_icons($ignored, &$icons){
	if (can_manage_security(logged_user())) {
		$icons[] = array(
			'ico' => 'ico-large-email',
			'url' => get_url('administration', 'mail_accounts'),
			'name' => lang('mail accounts'),
			'extra' => '<a class="internalLink coViewAction ico-add" href="' . get_url('mail', 'add_account') . '">' . lang('add mail account') . '</a>',
		);
	}
}

function mail_allowed_subscribers($object, &$contacts) {
	if ($object instanceof MailContent) {
		$person_dim = Dimensions::findByCode('feng_persons');
		$person_dim_id = $person_dim instanceof Dimension ? $person_dim->getId() : "0";
		$sql = "SELECT member_id FROM ".TABLE_PREFIX."object_members om INNER JOIN ".TABLE_PREFIX."members m ON m.id=om.member_id
			WHERE om.object_id = ".$object->getId()." AND om.is_optimization=0 AND m.dimension_id NOT IN (".$person_dim_id.")";
		$member_ids_res = DB::executeAll($sql);
		
		$member_ids = array();
		foreach ($member_ids_res as $row) {
			if (trim($row['member_id']) != "") $member_ids[] = $row['member_id'];
		}
		
		if (!$member_ids || count($member_ids) == 0) {
			$contacts = array(logged_user());
		}
	}
}

function mail_delete_member($member){
    DB::executeAll("UPDATE ".TABLE_PREFIX."mail_accounts SET member_id=0 WHERE member_id = '".$member->getId()."'");
}

function mail_on_page_load(){
	//check if have outbox mails
	$usu = logged_user();
	$accounts = MailAccounts::instance()->getMailAccountsByUser($usu);
	$account_ids = array();
	foreach ($accounts as $acc) {
		$account_ids[] = $acc->getId();
	}
	
	if (count($account_ids) == 0) return;
	
	$accounts_sql = " AND account_id IN (".implode(',', $account_ids).")";
	
	$user_pg_ids = $usu->getPermissionGroupIds();
	if (count($user_pg_ids) == 0) return;
	
	$permissions_sql = " AND EXISTS (SELECT sh.group_id FROM ".TABLE_PREFIX."sharing_table sh WHERE sh.object_id=o.id AND sh.group_id IN (".implode(',',$user_pg_ids)."))";
	
	$conditions = array("conditions" => array("`state` >= 200 AND (`state`%2 = 0) AND `archived_on`=0 AND `trashed_on`=0 $accounts_sql $permissions_sql AND `created_by_id` =".$usu->getId()));
	$outbox_mails = MailContents::findAll($conditions);
	if ($outbox_mails!= null){
		if (count($outbox_mails)>=1){
			$arguments = array("conditions" => array("`context` LIKE 'mails_in_outbox%' AND `contact_id` = ".$usu->getId().";"));
			$exist_reminder = ObjectReminders::find($arguments);
			if (!(count($exist_reminder)>0)){
				$reminder = new ObjectReminder();
			
				$minutes = 0;
				$reminder->setMinutesBefore($minutes);
				$reminder->setType("reminder_popup");
				$reminder->setContext("mails_in_outbox ".count($outbox_mails));
				$reminder->setObject($usu);
				$reminder->setUserId($usu->getId());
				$reminder->setDate(DateTimeValueLib::now());
				$reminder->save();
			}
		}
	}
}

function mail_do_mark_as_read_unread_objects($ids_to_mark, $read) {
	$all_accounts = array();
	$all_accounts_ids = array();
	
	foreach ($ids_to_mark as $id) {
		$obj = Objects::findObject($id);
		if ($obj instanceof MailContent && logged_user() instanceof Contact) {
			//conversation set the rest of the conversation
			$uds_to_mark_from_conver = array();
			if (user_config_option('show_emails_as_conversations')) {
				$emails_in_conversation = MailContents::getMailsFromConversation($obj);
				foreach ($emails_in_conversation as $email) {
					//$id is marked on object controller only mark the rest of the conversation
					if($id != $email->getId()){		
						$email->setIsRead(logged_user()->getId(), $read);
						$uds_to_mark_from_conver[] = $email->getUid();
					}
				}
			}

			//make the array with accounts and uids to send to the mail server
			//accounts
			if(!in_array($obj->getAccountId(), $all_accounts_ids)){
				$account = $obj->getAccount();
				
				//if logged user is owner of this account and is imap
				if($account instanceof MailAccount && $account->getContactId() == logged_user()->getId() && $account->getIsImap()){
					$all_accounts_ids[] = $obj->getAccountId();
					$all_accounts[$account->getId()]['account'] = $account;					
				}
			}
			//uids
			if(in_array($obj->getAccountId(), $all_accounts_ids)){
				//add conversations uids
				//mientras ande mal el uid de los mails enviados si estan sincronizados no usar esta parte
				/*if (user_config_option('show_emails_as_conversations')) {
					foreach ($uds_to_mark_from_conver as $uid_conver){
						$all_accounts[$obj->getAccountId()]['uids'][] = $uid_conver;
					}
				}*/
				
				$all_accounts[$obj->getAccountId()]['folders'][$obj->getImapFolderName()][] = $obj->getUid();
			}
		} 
	}
			
	//foreach account send uids by folder to mark in the mail server
	foreach ($all_accounts as $account_data){
		$account = $account_data['account'];
		$folders = $account_data['folders'];
		foreach ($folders as $key => $folder){
			$folder_name = $key;
			$uids = $folder;
			if(!empty($folder_name)){
				try {
					MailUtilities::setReadUnreadImapMails($account, $folder_name, $uids, $read);
				} catch (Exception $e) {
					Logger::log("Could not set mail as read on mail server, exception:\n".$e->getMessage());
				}
			}
		}
		
	}	

}