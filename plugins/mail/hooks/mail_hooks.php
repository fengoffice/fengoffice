<?php
Hook::register('mail');

// when deactivating mail plugin we also need to deactivate mail_rules to avoid issues
function mail_on_plugin_deactivate($params, &$ignored) {

	if (array_var($params, 'plugin') == 'mail') {

		$mail_rules_plugin = Plugins::instance()->getByName("mail_rules");
		if ($mail_rules_plugin instanceof Plugin) {
			$mail_rules_plugin->deactivate();
		}
	}
}

function mail_additional_general_config_option($params, &$options) {
	$cat = array_var($params, 'category');
	$cat_name = $cat instanceof ConfigCategory ? $cat->getName() : "";
	
	if ($cat_name == 'mail module') {
		$options[] = array(
			'id' => 'mail_accounts',
			'url' => get_url('administration', 'mail_accounts'),
			'name' => lang('mail accounts'),
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
	
	$utils = new MailUtilities();
	$utils->check_if_outbox_has_pending_mails(logged_user());
	
	/*
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
	}*/
}

function mail_do_mark_as_read_unread_objects($ids_to_mark, $read) {
	$all_accounts = array();
	$all_accounts_ids = array();
	
	// update mail list
	$dont_remove = array_var($_REQUEST, 'dont_remove');
	if ($read && user_config_option('mails read filter') == 'unread' || !$read && user_config_option('mails read filter') == 'read') {
		evt_add("remove from email list", array('ids' => $ids_to_mark, 'remove_later' => $dont_remove));
	}
	
	if (!Plugins::instance()->isActivePlugin('advanced_mail_imap_sync')) {
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
}

function mail_custom_reports_external_column_info($params, &$field) {
	$ot = array_var($params, 'object_type');
	$fname = array_var($params, 'field_name');
	
	if ($ot instanceof ObjectType && $ot->getName() == 'mail') {
		if (in_array($fname, array('to','cc','bcc','body_plain','body_html'))) {
			$field['type'] = 'text';
		}
	}
}


function mail_after_object_controller_trash($ids, &$ignored) {
	if (!is_array($ids)) {
		$ids = explode(',', $ids);
	}
	evt_add("remove from email list", array('ids' => $ids));
}

function mail_after_object_controller_archive($ids, &$ignored) {
	if (!is_array($ids)) {
		$ids = explode(',', $ids);
	}
	evt_add("remove from email list", array('ids' => $ids));
}

function mail_fill_sharing_table_for_classified_object_modify_group_ids($params, &$gids) {
    $object_id = array_var($params, 'object_id');

    $object = Objects::findObject($object_id);
    $mail_ot = ObjectTypes::instance()->findByName('mail');

    if ($mail_ot instanceof ObjectType && $object instanceof MailContent && $object->getObjectTypeId() == $mail_ot->getId()) {
        $mail_gids = array_filter(array_flat(DB::executeAll("
			SELECT cpg.permission_group_id
			FROM ".TABLE_PREFIX."contact_permission_groups cpg
			INNER JOIN ".TABLE_PREFIX."contacts c ON c.permission_group_id=cpg.permission_group_id
			WHERE cpg.contact_id IN (
				SELECT mac.contact_id FROM ".TABLE_PREFIX."mail_account_contacts mac 
				WHERE mac.account_id = (
					SELECT mc.account_id FROM ".TABLE_PREFIX."mail_contents mc WHERE mc.object_id='$object_id'
				)
			);
		")));
        foreach ($mail_gids as $mail_gid){
            if(!in_array($mail_gid, $gids)) {
                $gids[] = $mail_gid;
            }
        }

    }
}

function mail_fill_sharing_table_for_unclassified_object_modify_group_ids($params, &$gids) {
	$object_id = array_var($params, 'object_id');
	
	$object = Objects::findObject($object_id);
	$mail_ot = ObjectTypes::instance()->findByName('mail');

	//Mails
	if ($mail_ot instanceof ObjectType && $object instanceof MailContent && $object->getObjectTypeId() == $mail_ot->getId()) {
		$gids = array_filter(array_flat(DB::executeAll("
			SELECT cpg.permission_group_id
			FROM ".TABLE_PREFIX."contact_permission_groups cpg
			INNER JOIN ".TABLE_PREFIX."contacts c ON c.permission_group_id=cpg.permission_group_id
			WHERE cpg.contact_id IN (
				SELECT mac.contact_id FROM ".TABLE_PREFIX."mail_account_contacts mac 
				WHERE mac.account_id = (
					SELECT mc.account_id FROM ".TABLE_PREFIX."mail_contents mc WHERE mc.object_id='$object_id'
				)
			);
		")));
	}

	//Attachments
    $file_ot = ObjectTypes::findByName('file');
    if ($file_ot instanceof ObjectType && $object->getObjectTypeId() == $file_ot->getId()) {
        $mail_object = Objects::findObject($object->getMailId());

        if($mail_object instanceof MailContent){

            $gids = array_filter(array_flat(DB::executeAll("
			SELECT c.permission_group_id
			FROM ".TABLE_PREFIX."mail_account_contacts mac 
			INNER JOIN ".TABLE_PREFIX."contacts c ON c.object_id = mac.contact_id
	        WHERE mac.account_id = ".$mail_object->getAccountId().";
		")));
        }
    }
}

/**
 * This function will add all mails ids where the permission group have permission to the mail account
 * Only check mails that are classified in this members
 */
function mail_get_classified_objects_ids_by_permission_group_modify_object_ids($params, &$object_ids) {
    $permission_group_id = array_var($params, 'permission_group_id');
    $object_type_ids = array_var($params, 'object_type_ids');
    $members_ids = array_var($params, 'members_ids');

    $mail_ot = ObjectTypes::instance()->findByName('mail');

    if(!is_null($object_type_ids) && $mail_ot instanceof ObjectType && in_array($mail_ot->getId(),$object_type_ids)){
        //Get all mail accounts where this permission group have permissions
        $mail_account_ids = array_filter(array_flat(DB::executeAll("
			SELECT mac.account_id
			FROM ".TABLE_PREFIX."mail_account_contacts mac 
			INNER JOIN ".TABLE_PREFIX."contacts c ON c.object_id = mac.contact_id
	        WHERE c.permission_group_id = $permission_group_id;
		")));

        if(is_array($mail_account_ids) && count($mail_account_ids) > 0){
            //Get all mails ids for this mail accounts that are classified in this members
            $main_select_sql = "
              SELECT mc.object_id
              FROM ".TABLE_PREFIX."object_members om
              INNER JOIN ".TABLE_PREFIX."mail_contents mc ON mc.object_id = om.object_id
              WHERE om.member_id IN (".implode(',',$members_ids).")
              AND mc.account_id IN (".implode(',',$mail_account_ids).");
	        ";

            $mails_ids = array_filter(array_flat(DB::executeAll($main_select_sql)));

            if(is_array($mails_ids) && count($mails_ids) > 0){
                $object_ids = array_unique(array_merge($object_ids, $mails_ids));
            }
        }

    }
}
