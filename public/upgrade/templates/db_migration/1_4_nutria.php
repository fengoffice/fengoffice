-- <?php echo $table_prefix ?> og_
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci
-- <?php echo $engine ?> InnoDB

INSERT INTO `<?php echo $table_prefix ?>cron_events` (`name`, `recursive`, `delay`, `is_system`, `enabled`, `date`) VALUES
 ('send_password_expiration_reminders', '1', '1440', '1', '1', '0000-00-00 00:00:00')
ON DUPLICATE KEY UPDATE id=id;
	
INSERT INTO `<?php echo $table_prefix ?>config_categories` (`name`, `is_system`, `category_order`) VALUES
 ('passwords', 0, 4)
ON DUPLICATE KEY UPDATE id=id;
	
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
 ('general', 'file_revision_comments_required', '0', 'BoolConfigHandler', 0, 0, 'If set, file revision comments are required'),
 ('general', 'currency_code', '$', 'StringConfigHandler', 0, 0, 'Currency code'),
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
 ('modules', 'enable_reporting_module', '1', 'BoolConfigHandler', 0, 0, 'Enable or disable reporting tab.'),
 ('passwords', 'min_password_length', '0', 'IntegerConfigHandler', 0, '1', NULL),
 ('passwords', 'password_numbers', '0', 'IntegerConfigHandler', 0, '2', NULL),
 ('passwords', 'password_uppercase_characters', '0', 'IntegerConfigHandler', 0, '3', NULL),
 ('passwords', 'password_metacharacters', '0', 'IntegerConfigHandler', 0, '4', NULL),
 ('passwords', 'password_expiration', '0', 'IntegerConfigHandler', 0, '5', NULL),
 ('passwords', 'password_expiration_notification', '0', 'IntegerConfigHandler', 0, '6', NULL),
 ('passwords', 'account_block', '0', 'BoolConfigHandler', 0, '7', NULL),
 ('passwords', 'new_password_char_difference', '0', 'BoolConfigHandler', '0', '8', NULL),
 ('passwords', 'validate_password_history', '0', 'BoolConfigHandler', '0', '9', NULL),
 ('general', 'checkout_notification_dialog', '0', 'BoolConfigHandler', '0', '0', NULL),
 ('general', 'currency_code', '$', 'StringConfigHandler', '0', '0', NULL),
 ('general', 'file_revision_comments_required', '0', 'BoolConfigHandler', '0', '0', NULL),
 ('general', 'show_feed_links', '0', 'BoolConfigHandler', '0', '0', NULL),
 ('mailing', 'user_email_fetch_count', '10', 'IntegerConfigHandler', 0, 0, 'How many emails to fetch when checking for email')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO `<?php echo $table_prefix ?>user_ws_config_categories` (`name`, `is_system`, `type`, `category_order`) VALUES 
 ('general', 0, 0, 0),
 ('dashboard', 0, 0, 1),
 ('task panel', 0, 0, 2),
 ('time panel', 1, 0, 3),
 ('calendar panel', 0, 0, 4),
 ('context help', 1, 0, 5)
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
 ('general', 'localization', '', 'LocalizationConfigHandler', 0, 100, ''),
 ('general', 'initialWorkspace', '0', 'InitialWorkspaceConfigHandler', 0, 200, ''),
 ('general', 'lastAccessedWorkspace', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('general', 'rememberGUIState', '1', 'BoolConfigHandler', 0, 300, ''),
 ('general', 'work_day_start_time', '9:00', 'TimeConfigHandler', 0, 400, 'Work day start time'),
 ('general', 'time_format_use_24', '0', 'BoolConfigHandler', 0, 500, 'Use 24 hours time format'),
 ('general', 'date_format', 'd/m/Y', 'StringConfigHandler', 0, 600, 'Date objects will be displayed using this format.'),
 ('general', 'descriptive_date_format', 'l, j F', 'StringConfigHandler', 0, 700, 'Descriptive dates will be displayed using this format.'),
 ('calendar panel', 'calendar view type', 'viewweek', 'StringConfigHandler', 1, 0, ''),
 ('calendar panel', 'calendar user filter', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('calendar panel', 'calendar status filter', '', 'StringConfigHandler', 1, 0, ''),
 ('calendar panel', 'start_monday', '', 'BoolConfigHandler', 0, 0, ''),
 ('calendar panel', 'show_week_numbers', '', 'BoolConfigHandler', 0, 0, ''),
 ('dashboard', 'show getting started widget', '1', 'BoolConfigHandler', '0', '1000', NULL),
 ('dashboard', 'getting_started_widget_expanded', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_tasks_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_account_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_active_tasks_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_general_timeslots_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_late_tasks_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_pending_tasks_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_documents_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_active_tasks_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_calendar_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_messages_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_dashboard_info_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_comments_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_emails_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_reporting_panel_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_file_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_administration_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_member_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_contact_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_company_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_description_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_linked_objects_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_note_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_note_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_note_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_note_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_note_subscribers_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_note_linked_object_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_description_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_reminders_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_linked_object_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_print_report_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_reminders_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_linked_objects_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_list_task_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_time_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_description_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_linked_objects_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_tag_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_description_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_repeat_options_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_reminders_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_linked_objects_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_inivitation_context_help', '1', 'BoolConfigHandler', '1', '0', NULL), 
 ('general', 'custom_report_tab', 'tasks', 'StringConfigHandler', '1', '0', NULL),
 ('general', 'show_context_help', 'until_close', 'ShowContextHelpConfigHandler', '0', '0', NULL),
 ('task panel', 'noOfTasks', '8', 'IntegerConfigHandler', '0', '100', NULL)
ON DUPLICATE KEY UPDATE id=id;


CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>user_passwords` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `password` varchar(40) NOT NULL,
  `password_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>custom_properties` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `object_type` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `name` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `type` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `description` text <?php echo $default_collation ?> NOT NULL,
  `values` text <?php echo $default_collation ?> NOT NULL,
  `default_value` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `is_required` tinyint(1) NOT NULL,
  `is_multiple_values` tinyint(1) NOT NULL,
  `property_order` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>custom_property_values` (
  `object_id` int(10) NOT NULL AUTO_INCREMENT,
  `custom_property_id` int(10) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`object_id`,`custom_property_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

ALTER TABLE `<?php echo $table_prefix ?>project_files` ADD COLUMN `type` int(1) NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>project_files` ADD COLUMN `url` varchar(255) NULL;

ALTER TABLE `<?php echo $table_prefix?>project_webpages` MODIFY COLUMN `description` text <?php echo $default_collation ?>;

ALTER TABLE `<?php echo $table_prefix?>project_files` ADD COLUMN `mail_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix?>companies` ADD COLUMN `notes` text <?php echo $default_collation ?>;

INSERT INTO `<?php echo $table_prefix ?>cron_events` (`name`, `recursive`, `delay`, `is_system`, `enabled`, `date`) VALUES
  ('send_notifications_through_cron', '1', '1', '0', '0', '0000-00-00 00:00:00'),
  ('backup', '1', '10080', '0', '0', '0000-00-00 00:00:00');

ALTER TABLE `<?php echo $table_prefix ?>project_tasks` MODIFY COLUMN `from_template_id` int(10) NOT NULL default '0';

ALTER TABLE `<?php echo $table_prefix ?>project_milestones` MODIFY COLUMN `from_template_id` int(10) NOT NULL default '0';

ALTER TABLE `<?php echo $table_prefix ?>project_file_revisions` MODIFY COLUMN `type_string` varchar(255) <?php echo $default_collation ?> NOT NULL default '';

UPDATE `<?php echo $table_prefix?>project_events` `pe` SET
`start`=ADDDATE(`start`, INTERVAL (SELECT CONCAT('\'',-1*FLOOR(`timezone`),':',FLOOR(abs(`timezone`)%1)*60,'\'') FROM `<?php echo $table_prefix?>users` `u` WHERE `u`.`id` = `pe`.`created_by_id`) HOUR_MINUTE),
`duration`=ADDDATE(`duration`, INTERVAL (SELECT CONCAT('\'',-1*FLOOR(`timezone`),':',FLOOR(abs(`timezone`)%1)*60,'\'') FROM `<?php echo $table_prefix?>users` `u` WHERE `u`.`id` = `pe`.`created_by_id`) HOUR_MINUTE);

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>reports` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `description` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `object_type` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `order_by` varchar(255) <?php echo $default_collation ?> NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>report_columns` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `report_id` int(10) NOT NULL,
  `custom_property_id` int(10) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>report_conditions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `report_id` int(10) NOT NULL,
  `custom_property_id` int(10) NOT NULL,
  `field_name` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `condition` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `value` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `is_parametrizable` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

UPDATE `<?php echo $table_prefix ?>user_ws_config_options` SET `default_value` = '' WHERE `category_name` = 'general' AND `name` = 'localization';
UPDATE `<?php echo $table_prefix ?>user_ws_config_options` SET `default_value` = 1 WHERE `category_name` = 'general' AND `name` = 'rememberGUIState';

ALTER TABLE `<?php echo $table_prefix ?>users` ADD COLUMN `can_manage_reports` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `can_manage_templates`;
ALTER TABLE `<?php echo $table_prefix ?>groups` ADD COLUMN `can_manage_reports` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `can_manage_templates`;
UPDATE `<?php echo $table_prefix ?>users` SET can_manage_reports = 1 WHERE `id` IN (SELECT `user_id` FROM `<?php echo $table_prefix ?>group_users` WHERE `group_id` = 10000000);

ALTER TABLE `<?php echo $table_prefix ?>custom_properties` ADD COLUMN `visible_by_default` TINYINT(1) NOT NULL DEFAULT 0 AFTER `property_order`;

ALTER TABLE `<?php echo $table_prefix ?>custom_property_values` MODIFY COLUMN `value` text <?php echo $default_collation ?> NOT NULL;

ALTER TABLE `<?php echo $table_prefix ?>reports` ADD COLUMN `is_order_by_asc` TINYINT(1) <?php echo $default_collation ?> NOT NULL DEFAULT 1;
-- larger contact fields
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `firstname` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `lastname` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `middlename` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `department` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `job_title` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `w_city` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `w_state` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `w_zipcode` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `w_country` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `w_phone_number` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `w_phone_number2` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `w_fax_number` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `w_assistant_number` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `w_callback_number` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `h_city` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `h_state` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `h_zipcode` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `h_country` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `h_phone_number` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `h_phone_number2` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `h_fax_number` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `h_mobile_number` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `h_pager_number` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `o_city` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `o_state` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `o_zipcode` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `o_country` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `o_phone_number` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `o_phone_number2` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `o_fax_number` varchar(50) <?php echo $default_collation ?> default NULL;

ALTER TABLE `<?php echo $table_prefix ?>companies` MODIFY COLUMN `phone_number` varchar(50) <?php echo $default_collation ?> default NULL;
ALTER TABLE `<?php echo $table_prefix ?>companies` MODIFY COLUMN `fax_number` varchar(50) <?php echo $default_collation ?> default NULL;
