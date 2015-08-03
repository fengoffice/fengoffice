-- <?php echo $table_prefix ?> og_
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci
-- <?php echo $engine ?> InnoDB

INSERT INTO `<?php echo $table_prefix ?>config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
 ('general', 'detect_mime_type_from_extension', '0', 'BoolConfigHandler', '0', '0', NULL)
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO `<?php echo $table_prefix ?>user_ws_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES 
 ('general', 'reset_password', '', 'StringConfigHandler', '1', '0', 'Used to store per-user tokens to validate password reset requests'),
 ('general', 'drag_drop_prompt', 'prompt', 'DragDropPromptConfigHandler', '0', '0', NULL),
 ('general', 'search_engine', 'match', 'SearchEngineConfigHandler', 0, 700, ''),
 ('task panel', 'task_display_limit', '500', 'IntegerConfigHandler', '0', '200', NULL),
 ('mails panel', 'mail_drag_drop_prompt', 'prompt', 'MailDragDropPromptConfigHandler', '0', '102', NULL),
 ('mails panel', 'show_emails_as_conversations', '1', 'BoolConfigHandler', '0', '0', NULL),
 ('mails panel', 'mails account filter', '', 'StringConfigHandler', '1', '0', NULL),
 ('mails panel', 'mails classification filter', 'all', 'StringConfigHandler', '1', '0', NULL),
 ('mails panel', 'mails read filter', 'all', 'StringConfigHandler', '1', '0', NULL),
 ('mails panel', 'hide_quoted_text_in_emails', '1', 'BoolConfigHandler', 0, 110, NULL),
 ('dashboard', 'show_two_weeks_calendar', '1', 'BoolConfigHandler', '0', '0', NULL),
 ('general', 'autodetect_time_zone', '1', 'BoolConfigHandler', '0', '0', NULL),
 ('mails panel', 'max_spam_level', '0', 'IntegerConfigHandler', '0', '100', NULL),
 ('mails panel', 'create_contacts_from_email_recipients', '0', 'BoolConfigHandler', '0', '101', NULL)
ON DUPLICATE KEY UPDATE id=id;

UPDATE `<?php echo $table_prefix ?>user_ws_config_options` SET `config_handler_class` = 'RememberGUIConfigHandler' WHERE `name` = 'rememberGUIState';

UPDATE `<?php echo $table_prefix ?>user_ws_config_options` SET `category_name` = 'general' WHERE `name` = 'work_day_start_time';

ALTER TABLE `<?php echo $table_prefix ?>contacts`
 ADD COLUMN `archived_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
 ADD COLUMN `archived_by_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;
 
ALTER TABLE `<?php echo $table_prefix ?>companies`
 ADD COLUMN `archived_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
 ADD COLUMN `archived_by_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>project_charts`
 ADD COLUMN `archived_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
 ADD COLUMN `archived_by_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>project_events`
 ADD COLUMN `archived_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
 ADD COLUMN `archived_by_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>project_files`
 ADD COLUMN `archived_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
 ADD COLUMN `archived_by_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>project_messages`
 ADD COLUMN `archived_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
 ADD COLUMN `archived_by_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>project_milestones`
 ADD COLUMN `archived_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
 ADD COLUMN `archived_by_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>project_tasks`
 ADD COLUMN `archived_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
 ADD COLUMN `archived_by_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>project_webpages`
 ADD COLUMN `archived_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
 ADD COLUMN `archived_by_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;
 
ALTER TABLE `<?php echo $table_prefix ?>project_events`
 ADD COLUMN `repeat_dow` int(10) unsigned NOT NULL,
 ADD COLUMN `repeat_wnum` int(10) unsigned NOT NULL,
 ADD COLUMN `repeat_mjump` int(10) unsigned NOT NULL;

DELETE FROM `<?php echo $table_prefix ?>report_conditions` WHERE `custom_property_id` <> 0 AND `custom_property_id` NOT IN (SELECT `id` FROM `<?php echo $table_prefix ?>custom_properties`);
DELETE FROM `<?php echo $table_prefix ?>report_columns` WHERE `custom_property_id` <> 0 AND `custom_property_id` NOT IN (SELECT `id` FROM `<?php echo $table_prefix ?>custom_properties`);
 
ALTER TABLE `<?php echo $table_prefix ?>mail_accounts`
  ADD COLUMN `workspace` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  ADD COLUMN `sender_name` varchar(100) <?php echo $default_collation ?> NOT NULL default '',
  ADD INDEX `user_id` (`user_id`);

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>mail_account_users` (
 `id` INT(10) NOT NULL AUTO_INCREMENT,
 `account_id` INT(10) NOT NULL,
 `user_id` INT(10) NOT NULL,
 `can_edit` BOOLEAN NOT NULL default '0',
 `is_default` BOOLEAN NOT NULL default '0',
 `signature` text <?php echo $default_collation ?> NOT NULL,
 `sender_name` varchar(100) <?php echo $default_collation ?> NOT NULL default '',
 PRIMARY KEY (`id`),
 UNIQUE KEY `uk_useracc` (`account_id`, `user_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

INSERT INTO `<?php echo $table_prefix ?>mail_account_users`
    (`account_id`, `user_id`, `can_edit`, `is_default`, `signature`, `sender_name`)
  SELECT `id`, `user_id`, '1', `is_default`, `signature`, '' FROM `<?php echo $table_prefix ?>mail_accounts`;

ALTER TABLE `<?php echo $table_prefix ?>groups` ADD COLUMN 
  `can_add_mail_accounts` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;
  
ALTER TABLE `<?php echo $table_prefix ?>users` ADD COLUMN 
  `can_add_mail_accounts` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;

UPDATE `<?php echo $table_prefix ?>groups` SET `can_add_mail_accounts` = '1' WHERE `id` = 10000000;

UPDATE `<?php echo $table_prefix ?>users` SET `can_add_mail_accounts` = '1' WHERE `id` = 1;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>mail_conversations` (
 `id` INT(10) NOT NULL AUTO_INCREMENT,
 PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

ALTER TABLE `<?php echo $table_prefix ?>application_logs` MODIFY COLUMN `action` enum('upload','open','close','delete','edit','add','trash','untrash','subscribe','unsubscribe','tag','comment','link','unlink','login','untag','archive','unarchive') <?php echo $default_collation ?> default NULL;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>project_co_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `object_manager` varchar(45) NOT NULL,
  `name` varchar(45) NOT NULL,
  `created_by_id` int(10) unsigned NOT NULL,
  `created_on` datetime NOT NULL,
  `updated_by_id` int(10) unsigned NOT NULL,
  `updated_on` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `object_manager` (`object_manager`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>custom_properties_by_co_type` (
  `co_type_id` INTEGER UNSIGNED NOT NULL,
  `cp_id` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (`co_type_id`, `cp_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

ALTER TABLE `<?php echo $table_prefix ?>project_tasks` ADD COLUMN `object_subtype` int(10) unsigned NOT NULL default '0';

ALTER TABLE `<?php echo $table_prefix ?>project_contacts` ADD INDEX contact_project_ids(`contact_id`, `project_id`);

INSERT INTO `<?php echo $table_prefix ?>cron_events` (`name`, `recursive`, `delay`, `is_system`, `enabled`, `date`) VALUES
	('clear_tmp_folder', '1', '1440', '1', '1', '0000-00-00 00:00:00')
ON DUPLICATE KEY UPDATE id=id;

ALTER TABLE `<?php echo $table_prefix ?>mail_contents`
 ADD COLUMN `archived_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
 ADD COLUMN `archived_by_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
 ADD COLUMN `message_id` varchar(255) <?php echo $default_collation ?> NOT NULL COMMENT 'Message-Id header',
 ADD COLUMN `in_reply_to_id` varchar(255) <?php echo $default_collation ?> NOT NULL COMMENT 'Message-Id header of the previous email in the conversation',
 ADD COLUMN `conversation_id` int(10) unsigned NOT NULL default '0',
 ADD COLUMN `received_date` datetime NOT NULL default '0000-00-00 00:00:00',
 MODIFY COLUMN `sent_date` datetime NOT NULL default '0000-00-00 00:00:00',
 MODIFY COLUMN `uid` varchar(255) <?php echo $default_collation ?> NOT NULL default '',
 ADD INDEX `conversation_id` (`conversation_id`),
 ADD INDEX `message_id` (`message_id`),
 ADD INDEX `received_date` (`received_date`),
 ADD INDEX `state` (`state`),
 ADD INDEX `in_reply_to_id` (`in_reply_to_id`);


UPDATE `<?php echo $table_prefix ?>mail_contents` SET `received_date` = `sent_date`;

OPTIMIZE TABLE `<?php echo $table_prefix ?>_mail_contents`;