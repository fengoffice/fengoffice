<?php 
	/**
	 * Feng2 Plugin update engine 
	 */
	function mail_update_1_2() {
		DB::execute("UPDATE ".TABLE_PREFIX."tab_panels SET type = 'plugin', plugin_id = (SELECT id FROM ".TABLE_PREFIX."plugins WHERE name='mail') WHERE id='mails-panel'");
	}
	
	function mail_update_2_3() {
		DB::execute("CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."mail_spam_filters` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `account_id` int(10) unsigned NOT NULL,
		  `text_type` enum('email_address','subject') COLLATE utf8_unicode_ci NOT NULL,
		  `text` text COLLATE utf8_unicode_ci NOT NULL,
		  `spam_state` enum('no spam','spam') COLLATE utf8_unicode_ci NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
	}
	
	function mail_update_3_4() {
		// config option to remember columns on mail list
		DB::execute("
				INSERT INTO ".TABLE_PREFIX."contact_config_options (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				('mails panel', 'folder_received_columns', 'from,subject,account,date,folder,actions', 'StringConfigHandler', 0, 0, NULL),
 				('mails panel', 'folder_sent_columns', 'to,subject,account,date,folder,actions', 'StringConfigHandler', 0, 0, NULL),
				('mails panel', 'folder_draft_columns', 'to,subject,account,date,folder,actions', 'StringConfigHandler', 0, 0, NULL),
				('mails panel', 'folder_junk_columns', 'from,subject,account,date,folder,actions', 'StringConfigHandler', 0, 0, NULL),
				('mails panel', 'folder_outbox_columns', 'to,subject,account,date,folder,actions', 'StringConfigHandler', 0, 0, NULL)
				ON DUPLICATE KEY UPDATE name = name;
				");
	}

	function mail_update_4_5() {
		// config option to remember columns on mail list
		DB::execute("
			insert into ".TABLE_PREFIX."widgets (name,title,plugin_id,path,default_options,default_section,default_order,icon_cls) values
			 ('emails','emails',0,'','','right',10,'ico-email')
			on duplicate key update name=name;
		");
		//setting these user config options as invisible as they should not be shown in the user preferences
		DB::execute("UPDATE ".TABLE_PREFIX."contact_config_options SET `is_system` = '1' WHERE `name` = 'folder_received_columns';");
		DB::execute("UPDATE ".TABLE_PREFIX."contact_config_options SET `is_system` = '1' WHERE `name` = 'folder_sent_columns';");
		DB::execute("UPDATE ".TABLE_PREFIX."contact_config_options SET `is_system` = '1' WHERE `name` = 'folder_draft_columns';");
		DB::execute("UPDATE ".TABLE_PREFIX."contact_config_options SET `is_system` = '1' WHERE `name` = 'folder_junk_columns';");
		DB::execute("UPDATE ".TABLE_PREFIX."contact_config_options SET `is_system` = '1' WHERE `name` = 'folder_outbox_columns';");
	}
	
	function mail_update_5_6() {
		// add a column to know the last mail in conversation for each folder
		if (!check_column_exists(TABLE_PREFIX."mail_contents", "conversation_last")) {
			DB::execute("
				ALTER TABLE `".TABLE_PREFIX."mail_contents` ADD COLUMN `conversation_last` int(1) NOT NULL default '1' AFTER conversation_id;
			");
		}		
	}
	
	function mail_update_6_7() {
		if (!check_column_exists(TABLE_PREFIX."mail_accounts", "mark_read_on_server")) {
			DB::execute("
				ALTER TABLE `".TABLE_PREFIX."mail_accounts` ADD COLUMN `mark_read_on_server` int(1) NOT NULL default '1';
			");
		}
	}
	
	
	function mail_update_7_8() {
		
		$sent_mails = MailContents::findAll(array('conditions' => "`state`=3 AND `has_attachments`=1"));
		foreach ($sent_mails as $mail) {
			if (!$mail instanceof MailContent) continue;
			/* @var $mail MailContent */
			$attachments = array();
			MailUtilities::parseMail($mail->getContent(), $decoded, $parsedEmail, $warnings);
			if (isset($parsedEmail['Attachments'])) {
				$attachments = $parsedEmail['Attachments'];
			} else if ($mail->getHasAttachments() && !in_array($parsedEmail['Type'], array('html', 'text', 'delivery-status')) && isset($parsedEmail['FileName'])) {
				// if the email is the attachment
				$attachments = array(array('Data' => $parsedEmail['Data'], 'Type' => $parsedEmail['Type'], 'FileName' => $parsedEmail['FileName']));
			}
			foreach ($attachments as $att) {
				$file = ProjectFiles::getByFilename($att['FileName']);
				/* @var $file ProjectFile */
				if ($file instanceof ProjectFile) {
					$file->setMailId($mail->getId());
					$file->setMarkTimestamps(false);// dont change updated_on date
					$file->save();
					$file->addToSharingTable();
				}
			}
		}
		DB::executeAll("UPDATE ".TABLE_PREFIX."objects o INNER JOIN ".TABLE_PREFIX."project_files f ON f.object_id=o.id
			SET o.updated_by_id=o.created_by_id, o.updated_on=o.created_on
			WHERE f.mail_id>0;");
	}
	
	function mail_update_8_9() {
		DB::execute("ALTER TABLE `".TABLE_PREFIX."mail_datas` ADD INDEX `to`(`to`(255)), ADD INDEX `subject`(`subject`(255));");
	}
	
	function mail_update_9_10() {

		DB::execute("INSERT INTO ".TABLE_PREFIX."role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
		 SELECT p.id, o.id, 1, 1
		 FROM `".TABLE_PREFIX."object_types` o JOIN `".TABLE_PREFIX."permission_groups` p
		 WHERE o.`name` IN ('mail')
		 AND p.`name` IN ('Super Administrator','Administrator','Manager','Executive')
		ON DUPLICATE KEY UPDATE role_id=role_id;");
	}
	
	function mail_update_10_11() {
		DB::execute("ALTER TABLE `".TABLE_PREFIX."mail_accounts` MODIFY COLUMN `member_id` VARCHAR(100) NOT NULL;");
	}

	function mail_update_11_12() {
	
		DB::execute("INSERT INTO ".TABLE_PREFIX."max_role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
		 SELECT p.id, o.id, 1, 1
		 FROM `".TABLE_PREFIX."object_types` o JOIN `".TABLE_PREFIX."permission_groups` p
		 WHERE o.`name` IN ('mail')
		 AND p.`name` IN ('Super Administrator','Administrator','Manager','Executive')
		ON DUPLICATE KEY UPDATE role_id=role_id;");
	}
	
	function mail_update_12_13() {
		if (!check_column_exists(TABLE_PREFIX."templates", "can_instance_from_mail")) {
			DB::execute("
				ALTER TABLE `".TABLE_PREFIX."templates` ADD COLUMN `can_instance_from_mail` int(1) NOT NULL default '0';
			");
		}
	}
	
	function mail_update_13_14() {
		DB::execute("
			INSERT INTO `".TABLE_PREFIX."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				('mails panel', 'check_attach_word', '1', 'BoolConfigHandler', 0, 0, NULL)
			ON DUPLICATE KEY UPDATE name=name;
		");
	}
	
	function mail_update_14_15() {
		DB::execute("
			DELETE FROM ".TABLE_PREFIX."max_role_object_type_permissions 
			WHERE object_type_id IN (
				 SELECT o.id
				 FROM `".TABLE_PREFIX."object_types` o 
				 WHERE o.`name` IN ('mail')
			);
 		");
 		DB::execute("
			INSERT INTO ".TABLE_PREFIX."max_role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
			 SELECT p.id, o.id, 1, 1
			 FROM `".TABLE_PREFIX."object_types` o JOIN `".TABLE_PREFIX."permission_groups` p
			 WHERE o.`name` IN ('mail')
			 AND p.`name` IN ('Super Administrator','Administrator','Manager','Executive')
			ON DUPLICATE KEY UPDATE role_id=role_id;
		");
 		DB::execute("
			INSERT INTO ".TABLE_PREFIX."max_role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
			 SELECT p.id, o.id, 0, 0
			 FROM `".TABLE_PREFIX."object_types` o JOIN `".TABLE_PREFIX."permission_groups` p
			 WHERE o.`name` IN ('mail')
			 AND p.`name` IN ('Collaborator Customer','Internal Collaborator','External Collaborator','Guest Customer')
			ON DUPLICATE KEY UPDATE role_id=role_id;
		");
 		DB::execute("
			DELETE FROM ".TABLE_PREFIX."role_object_type_permissions
			WHERE object_type_id IN (
				SELECT o.id
				FROM `".TABLE_PREFIX."object_types` o
				WHERE o.`name` IN ('mail')
			);
		");
 		DB::execute("
			INSERT INTO ".TABLE_PREFIX."role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
			 SELECT p.id, o.id, 1, 1
			 FROM `".TABLE_PREFIX."object_types` o JOIN `".TABLE_PREFIX."permission_groups` p
			 WHERE o.`name` IN ('mail')
			 AND p.`name` IN ('Super Administrator','Administrator','Manager')
			ON DUPLICATE KEY UPDATE role_id=role_id;
		");
 		DB::execute("
			INSERT INTO ".TABLE_PREFIX."role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
			 SELECT p.id, o.id, 0, 1
			 FROM `".TABLE_PREFIX."object_types` o JOIN `".TABLE_PREFIX."permission_groups` p
			 WHERE o.`name` IN ('mail')
			 AND p.`name` IN ('Executive')
			ON DUPLICATE KEY UPDATE role_id=role_id;
		");
	}
	
	function mail_update_15_16() {
		DB::execute("
			INSERT INTO `".TABLE_PREFIX."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				('mails panel', 'check_is_defult_account', '1', 'BoolConfigHandler', 0, 0, NULL),
				('mails panel', 'auto_classify_attachments', '1', 'BoolConfigHandler', 0, 0, NULL)
			ON DUPLICATE KEY UPDATE name=name;
		");
	}