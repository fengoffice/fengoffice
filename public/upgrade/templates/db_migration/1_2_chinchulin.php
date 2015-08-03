-- <?php echo $table_prefix ?> og_
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci
-- <?php echo $engine ?> InnoDB

ALTER TABLE `<?php echo $table_prefix ?>application_logs` MODIFY COLUMN `action` ENUM('upload','open','close','delete','edit','add','trash','untrash', 'subscribe', 'unsubscribe', 'tag', 'comment', 'link', 'unlink', 'login') DEFAULT NULL;
ALTER TABLE `<?php echo $table_prefix ?>application_logs` ADD COLUMN `log_data` TEXT;

INSERT INTO `<?php echo $table_prefix ?>config_categories` (`name`, `is_system`, `category_order`) VALUES
 ('system', 1, 0),
 ('general', 0, 1),
 ('mailing', 0, 2),
 ('modules', 0, 3)
ON DUPLICATE KEY UPDATE id=id;

UPDATE `<?php echo $table_prefix ?>config_options` SET `category_name` = 'modules' WHERE `name` = 'enable_email_module';
DELETE FROM `<?php echo $table_prefix ?>config_options` WHERE `name` = 'time_format_use_24';

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

INSERT INTO `<?php echo $table_prefix ?>user_ws_config_categories` (`name`, `is_system`, `type`, `category_order`) VALUES 
 ('general', 0, 0, 0),
 ('dashboard', 0, 0, 1),
 ('task panel', 0, 0, 2),
 ('time panel', 1, 0, 3)
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
 ('dashboard', 'always show unread mail in dashboard', '0', 'BoolConfigHandler', 0, 10, 'when false, active workspace email is shown'),
 ('task panel', 'can notify from quick add', '1', 'BoolConfigHandler', 0, 0, 'Notification checkbox default value'),
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
 ('general', 'time_format_use_24', '0', 'BoolConfigHandler', 0, 500, 'Use 24 hours time format')
ON DUPLICATE KEY UPDATE id=id;
 
CREATE TABLE  `<?php echo $table_prefix ?>gs_books` (
  `BookId` int(10) unsigned NOT NULL auto_increment,
  `BookName` varchar(45) <?php echo $default_collation ?> NOT NULL default '',
  `UserId` int(10) unsigned NOT NULL COMMENT 'Book Owner',
  PRIMARY KEY  (`BookId`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?> AUTO_INCREMENT=189809 COMMENT='System Workbooks';

CREATE TABLE  `<?php echo $table_prefix ?>gs_cells` (
  `SheetId` int(10) unsigned NOT NULL,
  `DataColumn` int(10) unsigned NOT NULL,
  `DataRow` int(10) unsigned NOT NULL,
  `CellFormula` varchar(255) <?php echo $default_collation ?> default NULL,
  `CellValue` text <?php echo $default_collation ?> NOT NULL,
  `FontStyleId` int(10) unsigned NOT NULL default '0',
  `LayoutStyleId` int(11) NOT NULL default '0',
  PRIMARY KEY  (`SheetId`,`DataColumn`,`DataRow`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?> COMMENT='Sheet data';

CREATE TABLE  `<?php echo $table_prefix ?>gs_columns` (
  `SheetId` int(11) NOT NULL,
  `ColumnIndex` int(11) NOT NULL,
  `ColumnSize` int(11) NOT NULL,
  `FontStyleId` int(11) NOT NULL,
  `LayerStyleId` int(11) NOT NULL,
  `LayoutStyleId` int(11) NOT NULL,
  PRIMARY KEY  (`SheetId`,`ColumnIndex`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>gs_fontStyles` (
  `FontStyleId` int(11) NOT NULL,
  `BookId` int(11) NOT NULL,
  `FontId` int(11) NOT NULL,
  `FontSize` decimal(8,1) NOT NULL default '10.0',
  `FontBold` tinyint(1) NOT NULL default '0',
  `FontItalic` tinyint(1) NOT NULL default '0',
  `FontUnderline` tinyint(1) NOT NULL default '0',
  `FontColor` varchar(6) <?php echo $default_collation ?> NOT NULL default '',
  PRIMARY KEY  USING BTREE (`FontStyleId`,`BookId`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>gs_fonts` (
  `FontId` int(11) NOT NULL auto_increment,
  `FontName` varchar(63) <?php echo $default_collation ?> NOT NULL default '',
  PRIMARY KEY  (`FontId`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?> AUTO_INCREMENT=7;

INSERT INTO `<?php echo $table_prefix ?>gs_fonts` VALUES  (1,'Arial'),
 (2,'Times New Roman'),
 (3,'Verdana'),
 (4,'Courier'),
 (5,'Lucida Sans Console'),
 (6,'Tahoma');

CREATE TABLE  `<?php echo $table_prefix ?>gs_mergedCells` (
  `SheetId` int(11) NOT NULL,
  `MergedCellRow` int(11) NOT NULL,
  `MergedCellCol` int(11) NOT NULL,
  `MergedRows` int(11) default NULL,
  `MergedCols` int(11) default NULL
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>gs_rows` (
  `SheetId` int(11) NOT NULL,
  `RowIndex` int(11) NOT NULL,
  `RowSize` int(11) NOT NULL,
  `FontStyleId` int(11) NOT NULL,
  `LayerStyleId` int(11) NOT NULL,
  `LayoutStyleId` int(11) NOT NULL,
  PRIMARY KEY  (`SheetId`,`RowIndex`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>gs_sheets` (
  `SheetId` int(10) unsigned NOT NULL auto_increment,
  `BookId` int(10) unsigned NOT NULL,
  `SheetName` varchar(45) <?php echo $default_collation ?> NOT NULL default '',
  `SheetIndex` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`SheetId`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?> AUTO_INCREMENT=1142 COMMENT='Workbooks Sheets';

CREATE TABLE  `<?php echo $table_prefix ?>gs_userbooks` (
  `UserBookId` int(10) unsigned NOT NULL auto_increment,
  `UserId` int(10) unsigned NOT NULL,
  `BookId` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`UserBookId`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>gs_users` (
  `UserId` int(10) unsigned NOT NULL auto_increment,
  `UserName` varchar(45) <?php echo $default_collation ?> NOT NULL default '',
  `UserLastName` varchar(45) <?php echo $default_collation ?> NOT NULL default '',
  `UserNickname` varchar(45) <?php echo $default_collation ?> NOT NULL default '',
  `UserPassword` varchar(45) <?php echo $default_collation ?> NOT NULL default '',
  `LanguageId` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`UserId`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?> AUTO_INCREMENT=4 COMMENT='Sytem Users';


INSERT INTO `<?php echo $table_prefix ?>gs_users` VALUES  (1,'Open','Goo','open','goo',1);

CREATE TABLE `<?php echo $table_prefix ?>cron_events` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`name` varchar(45) <?php echo $default_collation ?> NOT NULL default '',
	`recursive` boolean NOT NULL default '1',
	`delay` int(10) unsigned NOT NULL default 0,
	`is_system` boolean NOT NULL default '0',
	`enabled` boolean NOT NULL default '1',
	`date` datetime NOT NULL default '0000-00-00 00:00:00',
	PRIMARY KEY (`id`),
	UNIQUE KEY `uk_name` (`name`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

INSERT INTO `<?php echo $table_prefix ?>cron_events` (`name`, `recursive`, `delay`, `is_system`, `enabled`, `date`) VALUES
	('check_mail', '1', '10', '0', '1', '0000-00-00 00:00:00'),
	('purge_trash', '1', '1440', '1', '1', '0000-00-00 00:00:00'),
	('check_upgrade', '1', '1440', '0', '1', '0000-00-00 00:00:00'), 
	('send_reminders', '1', '10', '0', '1', '0000-00-00 00:00:00');



INSERT INTO `<?php echo $table_prefix ?>workspace_objects` (`workspace_id`, `object_manager`, `object_id`, `created_by_id`, `created_on`)
 SELECT `project_id`, 'MailContents' as `controller`, `id`, `created_by_id` , `created_on` FROM `<?php echo $table_prefix ?>mail_contents` WHERE `project_id` <> 0
 ON DUPLICATE KEY UPDATE `object_id`=`object_id`;
 
ALTER TABLE `<?php echo $table_prefix ?>mail_contents` DROP COLUMN `project_id`;
ALTER TABLE `<?php echo $table_prefix ?>mail_contents` ADD COLUMN `imap_folder_name` VARCHAR(100) NOT NULL DEFAULT '';
ALTER TABLE `<?php echo $table_prefix ?>mail_contents` ADD COLUMN `account_email` VARCHAR(100) DEFAULT '';

UPDATE `<?php echo $table_prefix ?>mail_contents` mail SET account_email = (SELECT email FROM `<?php echo $table_prefix ?>mail_accounts` ac WHERE ac.id = mail.account_id);

CREATE TABLE  `<?php echo $table_prefix ?>mail_account_imap_folder` (
  `account_id` int(10) unsigned NOT NULL default '0',
  `folder_name` varchar(100) <?php echo $default_collation ?> NOT NULL default '',
  `check_folder` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`account_id`,`folder_name`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

ALTER TABLE `<?php echo $table_prefix ?>mail_accounts` ADD COLUMN `del_from_server` INTEGER UNSIGNED NOT NULL DEFAULT 0;

-- fix timezone problems
ALTER TABLE `<?php echo $table_prefix ?>contacts` MODIFY COLUMN `timezone` float(3,1) NOT NULL default '0.0';
ALTER TABLE `<?php echo $table_prefix ?>companies` MODIFY COLUMN `timezone` float(3,1) NOT NULL default '0.0';
ALTER TABLE `<?php echo $table_prefix ?>users` MODIFY COLUMN `timezone` float(3,1) NOT NULL default '0.0';

-- add PPT file type

INSERT INTO `<?php echo $table_prefix ?>file_types` (`extension`, `icon`, `is_searchable`, `is_image`) VALUES ('ppt', 'ppt.png', 0, 0);

ALTER TABLE `<?php echo $table_prefix ?>mail_contents` ADD COLUMN `content_file_id` VARCHAR(40) NOT NULL default '';
ALTER TABLE `<?php echo $table_prefix ?>mail_contents` ADD INDEX `sent_date` USING BTREE(`sent_date`);
