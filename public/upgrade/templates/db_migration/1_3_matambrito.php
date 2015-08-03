-- <?php echo $table_prefix ?> og_
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci
-- <?php echo $engine ?> InnoDB

INSERT INTO `<?php echo $table_prefix ?>config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
 ('system', 'project_logs_per_page', '10', 'IntegerConfigHandler', 1, 0, NULL),
 ('system', 'messages_per_page', '5', 'IntegerConfigHandler', 1, 0, NULL),
 ('system', 'max_avatar_width', '50', 'IntegerConfigHandler', 1, 0, NULL),
 ('system', 'max_avatar_height', '50', 'IntegerConfigHandler', 1, 0, NULL),
 ('system', 'logs_per_project', '5', 'IntegerConfigHandler', 1, 0, NULL),
 ('system', 'max_logo_width', '50', 'IntegerConfigHandler', 1, 0, NULL),
 ('system', 'max_logo_height', '50', 'IntegerConfigHandler', 1, 0, NULL),
 ('system', 'files_per_page', '50', 'IntegerConfigHandler', 1, 0, NULL),
 ('general', 'upgrade_last_check_datetime', '2006-09-02 13:46:47', 'DateTimeConfigHandler', 1, 0, 'Date and time of the last upgrade check'),
 ('general', 'upgrade_last_check_new_version', '0', 'BoolConfigHandler', 1, 0, 'True if system checked for the new version and found it. This value is used to hightligh upgrade tab in the administration'),
 ('general', 'file_storage_adapter', 'fs', 'FileStorageConfigHandler', 0, 0, 'What storage adapter should be used? fs or mysql'),
 ('general', 'theme', 'default', 'ThemeConfigHandler', 0, 0, NULL),
 ('general', 'days_on_trash', '30', 'IntegerConfigHandler', 0, 0, 'Days before a file is deleted from trash. 0 = Not deleted'),
 ('mailing', 'exchange_compatible', '0', 'BoolConfigHandler', 0, 0, NULL),
 ('mailing', 'mail_transport', 'mail()', 'MailTransportConfigHandler', 0, 0, 'Values: ''mail()'' - try to emulate mail() function, ''smtp'' - use SMTP connection'),
 ('mailing', 'smtp_server', '', 'StringConfigHandler', 0, 0, ''),
 ('mailing', 'smtp_port', '25', 'IntegerConfigHandler', 0, 0, NULL),
 ('mailing', 'smtp_authenticate', '0', 'BoolConfigHandler', 0, 0, 'Use SMTP authentication'),
 ('mailing', 'smtp_username', '', 'StringConfigHandler', 0, 0, NULL),
 ('mailing', 'smtp_password', '', 'PasswordConfigHandler', 0, 0, NULL),
 ('mailing', 'smtp_secure_connection', 'no', 'SecureSmtpConnectionConfigHandler', 0, 0, 'Values: no, ssl, tls'),
 ('modules', 'enable_notes_module', '1', 'BoolConfigHandler', 0, 0, 'Enable or disable notes tab.'),
 ('modules', 'enable_email_module', '1', 'BoolConfigHandler', 0, 0, 'Enable or disable email tab.'),
 ('modules', 'enable_contacts_module', '1', 'BoolConfigHandler', 0, 0, 'Enable or disable contacts tab.'),
 ('modules', 'enable_calendar_module', '1', 'BoolConfigHandler', 0, 0, 'Enable or disable calendar tab.'),
 ('modules', 'enable_documents_module', '1', 'BoolConfigHandler', 0, 0, 'Enable or disable documents tab.'),
 ('modules', 'enable_tasks_module', '1', 'BoolConfigHandler', 0, 0, 'Enable or disable tasks tab.'),
 ('modules', 'enable_weblinks_module', '1', 'BoolConfigHandler', 0, 0, 'Enable or disable weblinks tab.'),
 ('modules', 'enable_time_module', '1', 'BoolConfigHandler', 0, 0, 'Enable or disable time tab.'),
 ('modules', 'enable_reporting_module', '0', 'BoolConfigHandler', 0, 0, 'Enable or disable reporting tab.')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO `<?php echo $table_prefix ?>user_ws_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES 
 ('dashboard', 'show calendar widget', '1', 'BoolConfigHandler', 0, 80, ''),
 ('dashboard', 'show late tasks and milestones widget', '1', 'BoolConfigHandler', 0, 100, ''),
 ('dashboard', 'show pending tasks widget', '1', 'BoolConfigHandler', 0, 200, ''),
 ('dashboard', 'pending tasks widget assigned to filter', '0:0', 'UserCompanyConfigHandler', 0, 210, ''),
 ('dashboard', 'show emails widget', '1', 'BoolConfigHandler', 0, 300, ''),
 ('dashboard', 'show messages widget', '1', 'BoolConfigHandler', 0, 400, ''),
 ('dashboard', 'show documents widget', '1', 'BoolConfigHandler', 0, 500, ''),
 ('dashboard', 'show charts widget', '1', 'BoolConfigHandler', 0, 600, ''),
 ('dashboard', 'show tasks in progress widget', '1', 'BoolConfigHandler', 0, 700, ''),
 ('dashboard', 'show comments widget', '1', 'BoolConfigHandler', 0, 800, ''),
 ('dashboard', 'show dashboard info widget', '1', 'BoolConfigHandler', 0, 900, ''),
 ('dashboard', 'always show unread mail in dashboard', '0', 'BoolConfigHandler', 0, 10, 'when false, active workspace email is shown'),
 ('dashboard', 'calendar_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
 ('dashboard', 'emails_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
 ('dashboard', 'messages_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
 ('dashboard', 'active_tasks_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
 ('dashboard', 'pending_tasks_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
 ('dashboard', 'late_tasks_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
 ('dashboard', 'comments_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
 ('dashboard', 'documents_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
 ('dashboard', 'charts_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
 ('dashboard', 'dashboard_info_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'can notify from quick add', '1', 'BoolConfigHandler', 0, 0, ''),
 ('task panel', 'tasksShowWorkspaces', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowTime', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowDates', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowTags', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksGroupBy', 'milestone', 'StringConfigHandler', 1, 0, ''),
 ('task panel', 'tasksOrderBy', 'priority', 'StringConfigHandler', 1, 0, ''),
 ('task panel', 'task panel status', '1', 'IntegerConfigHandler', 1, 0, ''),
 ('task panel', 'task panel filter', 'assigned_to', 'StringConfigHandler', 1, 0, ''),
 ('task panel', 'task panel filter value', '0:0', 'UserCompanyConfigHandler', 1, 0, ''),
 ('time panel', 'TM show time type', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('time panel', 'TM report show time type', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('time panel', 'TM user filter', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('time panel', 'TM tasks user filter', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('general', 'localization', 'en_us', 'LocalizationConfigHandler', 0, 100, ''),
 ('general', 'initialWorkspace', '0', 'InitialWorkspaceConfigHandler', 0, 200, ''),
 ('general', 'lastAccessedWorkspace', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('general', 'rememberGUIState', '0', 'BoolConfigHandler', 0, 300, ''),
 ('general', 'work_day_start_time', '9:00', 'TimeConfigHandler', 0, 400, 'Work day start time'),
 ('general', 'time_format_use_24', '0', 'BoolConfigHandler', 0, 500, 'Use 24 hours time format'),
 ('calendar panel', 'calendar view type', 'viewweek', 'StringConfigHandler', 1, 0, ''),
 ('calendar panel', 'calendar user filter', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('calendar panel', 'calendar status filter', '', 'StringConfigHandler', 1, 0, '')
ON DUPLICATE KEY UPDATE id=id;

ALTER TABLE `<?php echo $table_prefix ?>mail_accounts` ADD COLUMN `outgoing_transport_type` VARCHAR(5) NOT NULL default '';

CREATE TABLE  `<?php echo $table_prefix ?>billing_categories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) <?php echo $default_collation ?> default '',
  `description` text <?php echo $default_collation ?>,
  `default_value` float NOT NULL default 0,
  `report_name` varchar(100) <?php echo $default_collation ?> default '',
  `created_on` datetime default NULL,
  `created_by_id` int(10) unsigned NOT NULL default '0',
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned default NULL,
 PRIMARY KEY  (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>workspace_billings` (
  `project_id` int(10) unsigned NOT NULL,
  `billing_id` int(10) unsigned NOT NULL,
  `value` float NOT NULL default 0,
  `created_on` datetime default NULL,
  `created_by_id` int(10) unsigned NOT NULL default '0',
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned default NULL,
 PRIMARY KEY  (`project_id`, `billing_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;


ALTER TABLE `<?php echo $table_prefix ?>timeslots` ADD COLUMN `fixed_billing` FLOAT NOT NULL DEFAULT 0;
ALTER TABLE `<?php echo $table_prefix ?>timeslots` ADD COLUMN `hourly_billing` FLOAT NOT NULL DEFAULT 0;
ALTER TABLE `<?php echo $table_prefix ?>timeslots` ADD COLUMN `is_fixed_billing` FLOAT NOT NULL DEFAULT 0;
ALTER TABLE `<?php echo $table_prefix ?>timeslots` ADD COLUMN `billing_id` int(10) unsigned NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>users` ADD COLUMN `default_billing_id` INTEGER(10) UNSIGNED DEFAULT 0;

DELETE FROM `<?php echo $table_prefix ?>object_reminders`;
ALTER TABLE `<?php echo $table_prefix ?>object_reminders` ADD COLUMN `context` VARCHAR(40) NOT NULL default '';
ALTER TABLE `<?php echo $table_prefix ?>object_reminders` ADD COLUMN `date` datetime NOT NULL default '0000-00-00 00:00:00';

CREATE TABLE `<?php echo $table_prefix ?>object_reminder_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` VARCHAR(40) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

INSERT INTO `<?php echo $table_prefix ?>object_reminder_types` (`name`) VALUES
  ('reminder_email'),
  ('reminder_popup');

CREATE TABLE  `<?php echo $table_prefix ?>shared_objects` (
  `object_id` INTEGER UNSIGNED NOT NULL,
  `object_manager` VARCHAR(45) NOT NULL,
  `user_id` INTEGER UNSIGNED NOT NULL,
  `created_on` DATETIME NOT NULL,
  `created_by_id` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (`object_id`, `object_manager`, `user_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

INSERT INTO `<?php echo $table_prefix ?>user_ws_config_categories` (`name`, `is_system`, `type`, `category_order`) VALUES 
 ('calendar panel', 1, 0, 4)
 ON DUPLICATE KEY UPDATE name=name;

ALTER TABLE `<?php echo $table_prefix ?>companies` MODIFY COLUMN `name` varchar(100) <?php echo $default_collation ?> default NULL;

UPDATE `<?php echo $table_prefix ?>user_ws_config_options` SET
	`config_handler_class` = 'BoolConfigHandler',
	`dev_comment` = 'Notification checkbox default value'
	WHERE `name` = 'can notify from quick add';

UPDATE `<?php echo $table_prefix ?>user_ws_config_options` SET `default_value` = 'es_la' WHERE `name` = 'localization' AND `default_value` = 'es_uy';
UPDATE `<?php echo $table_prefix ?>user_ws_config_option_values` `v`, `<?php echo $table_prefix ?>user_ws_config_options` `o` SET `v`.`value` = 'es_la' WHERE `o`.`name` = 'localization' AND `o`.`id` = `v`.`option_id` AND `v`.`value` = 'es_uy';

DROP TABLE IF EXISTS `<?php echo $table_prefix ?>eventtypes`;
ALTER TABLE `<?php echo $table_prefix ?>project_events` DROP COLUMN `eventtype`;
