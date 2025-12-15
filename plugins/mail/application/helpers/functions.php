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


/***************************************
 * IMAP folders functions
 **************************************/


/**
 * Remove a mail from the IMAP folders
 * @param object $account the MailAccount object
 * @param object $imap the IMAP connection object
 * @param object $mail the MailContent object
 * @param array $folders_to_remove the names of the folders to remove the mail from
 */
function remove_mail_from_imap_folders($account, $imap, $mail, $folders_to_remove) {
	// Remove the email from old folders
	foreach ($folders_to_remove as $folder_name) {
		if (is_null($folder_name)) continue;
		
		// if for any reason we have more than one uid for the same email in the same folder try to delete all of them
		$uids_in_folder_rows = DB::executeAll("SELECT uid FROM ".TABLE_PREFIX."mail_content_imap_folders
					WHERE object_id=".$mail->getId()." AND folder=".DB::escape($folder_name));
			
		$folder = utf8_decode($folder_name);
		$imap->selectMailbox($folder);
			
		$uids_in_folder = array_filter(array_flat($uids_in_folder_rows));
		
		if (!$uids_in_folder || count($uids_in_folder) == 0) {
			$uids_in_folder = $imap->search('HEADER Message-ID '.$mail->getMessageId(), true);
			if (PEAR::isError($uids_in_folder)) {
				$uids_in_folder = array();
			}
		}
			
		if (is_array($uids_in_folder) && count($uids_in_folder)) {
			$uids_str = implode(',', $uids_in_folder);
			$result = $imap->deleteMessages($uids_str, true);
			$imap->expunge();
	
			// delete regs in mail_content_imap_folders for this folder
			$sql = "DELETE FROM ".TABLE_PREFIX."mail_content_imap_folders
					WHERE object_id=".$mail->getId()." AND folder=".DB::escape($folder_name);
			DB::executeAll($sql);
				
		}
	}
}



/**
 * Copy a mail to new folders in the IMAP server
 * @param object $account the MailAccount object
 * @param object $imap the IMAP connection object
 * @param object $mail the MailContent object
 * @param array $folders_to_add the names of the folders to add the mail to
 */
function copy_mail_to_imap_folders($account, $imap, $mail, $folders_to_add) {
	
	// Get the source folder and uid of the mail
	$source_row = DB::executeOne("SELECT * FROM ".TABLE_PREFIX."mail_content_imap_folders
					WHERE object_id=".$mail->getId());
	
	// Add the email to the new folders
	if ($source_row) {
		$source_folder = utf8_decode($source_row['folder']);
		$source_uid = $source_row['uid'];
	} else {
		$source_folder = $mail->getImapFolderName();
		$source_uid = $mail->getUid();
	}
		
	if ($source_folder) {
		// copy to destiny folders
		$imap->selectMailbox($source_folder);
		
		foreach ($folders_to_add as $folder_name) {
			if (is_null($folder_name)) continue;

			$dest_folder = utf8_decode($folder_name);
			
			// check if this message already exists in destiny folder
			$imap->selectMailbox($dest_folder);
			$exist_uids = $imap->search('HEADER Message-ID '.$mail->getMessageId(), true);
			$exists_in_folder = is_array($exist_uids) && count($exist_uids) > 0;
			
			$imap->selectMailbox($source_folder);

			// if not exists -> copy it
			if (!$exists_in_folder) {
				$ret = $imap->cmdUidCopy($source_uid, $dest_folder);
				
				// get the new uid of the message in the folder
				$response_string = $ret['RESPONSE']['STR_CODE']; // [COPYUID 1500469762 18 14] Copy completed (0.003 + 0.000 + 0.002 secs).
				$matches = array();
				preg_match_all("/\[COPYUID (.*) (.*) (.*)\]/", $response_string, $matches);
				$new_uid = array_var($matches[3], 0);
				
				// update feng tables
				if ($ret && !Pear::isError($ret)) {
					fill_mail_content_imap_folder_table($imap, $account, $mail, $folder_name, $new_uid);
				}
			} else {
				// if already exists -> update feng info
				fill_mail_content_imap_folder_table($imap, $account, $mail, $folder_name, $exist_uids[0]);
			}
		}
	} else {
		// append to destiny folders
		foreach ($folders_to_add as $folder_name) {
			if (is_null($folder_name)) continue;

			$dest_folder = utf8_decode($folder_name);
			
			// check if this message already exists in destiny folder
			$imap->selectMailbox($dest_folder);
			$exist_uids = $imap->search('HEADER Message-ID '.$mail->getMessageId(), true);
			$exists_in_folder = is_array($exist_uids) && count($exist_uids) > 0;
			
			$imap->selectMailbox($source_folder);

			// if not exists -> append it
			if (!$exists_in_folder) {
				$eml = $mail->getContent();
				$ret = $imap->cmdAppend($dest_folder, $eml);
				
				// get the new uid of the message in the folder
				$response_string = $ret['RESPONSE']['STR_CODE']; // [APPENDUID 1500469762 18] Copy completed (0.003 + 0.000 + 0.002 secs).
				$matches = array();
				preg_match_all("/\[APPENDUID (.*) (.*)\]/", $response_string, $matches);
				$new_uid = array_var($matches[2], 0);
				
				// update feng tables
				if ($ret && !Pear::isError($ret)) {
					fill_mail_content_imap_folder_table($imap, $account, $mail, $folder_name, $new_uid);
				}
			} else {
				// if already exists -> update feng info
				fill_mail_content_imap_folder_table($imap, $account, $mail, $folder_name, $exist_uids[0]);
			}
		}
	}
	
	
}

/**
 * Move a mail to new folders in the IMAP server
 * (copy → then remove)
 *
 * @param object $account the MailAccount object
 * @param object $imap the IMAP connection object
 * @param object $mail the MailContent object
 * @param array $folders_to_add array of folder names
 */
function move_mail_to_imap_folders($account, $imap, $mail, $folders_to_add, $folders_to_remove) {

    Logger::log(
        "MOVE: Starting move_mail_to_imap_folders for mail ID ".$mail->getId(),
        Logger::DEBUG,
        null,
        'imap_move'
    );

    // 1) Copy to new folders
    Logger::log(
        "MOVE: Copying mail to folders: ".json_encode($folders_to_add),
        Logger::DEBUG,
        null,
        'imap_move'
    );

    copy_mail_to_imap_folders($account, $imap, $mail, $folders_to_add);

    // 2) Remove from old folders
    Logger::log(
        "MOVE: Removing mail from folders: ".json_encode($folders_to_remove),
        Logger::DEBUG,
        null,
        'imap_move'
    );

    remove_mail_from_imap_folders($account, $imap, $mail, $folders_to_remove);

    Logger::log(
        "MOVE: Finished move_mail_to_imap_folders for mail ID ".$mail->getId(),
        Logger::DEBUG,
        null,
        'imap_move'
    );
}




/**
 * Fill the mail_content_imap_folder table with the new uid and other info,
 * this is used when we move or copy a mail to a new folder
 * @param object $imap the IMAP connection object
 * @param object $account the MailAccount object
 * @param object $mail the MailContent object
 * @param string $folder_name the name of the folder
 * @param string $new_uid the new uid of the message in the folder
 */
function fill_mail_content_imap_folder_table($imap, $account, $mail, $folder_name, $new_uid) {
	if (!$new_uid) {
		// if we don't have the new uid, get it from the folder
		$imap->selectMailbox(utf8_decode($folder_name));
		$uids = $imap->search('HEADER Message-ID '.$mail->getMessageId(), true);
		$new_uid = array_var($uids, 0);
	}
	
	if ($new_uid) {
		// insert or update the row in the table
		$sql = "INSERT INTO ".TABLE_PREFIX."mail_content_imap_folders (account_id, message_id, folder, uid, object_id) VALUES
					(".$account->getId().",".DB::escape($mail->getMessageId()).",".DB::escape($folder_name).", '$new_uid', ".$mail->getId().")
				ON DUPLICATE KEY UPDATE uid='$new_uid'";
		DB::execute($sql);
		// remove the old rows with empty folder
		DB::execute("DELETE FROM ".TABLE_PREFIX."mail_content_imap_folders
				WHERE account_id=".$account->getId()." AND object_id=".$mail->getId()." AND folder=''");
	}
	
}
