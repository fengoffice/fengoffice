-- <?php echo $table_prefix ?> og_
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci
-- <?php echo $engine ?> InnoDB

UPDATE `<?php echo $table_prefix ?>user_ws_config_options` SET `is_system` = 1 where `id` >= 12 and `id` <=20;
ALTER TABLE `<?php echo $table_prefix ?>timeslots` ADD COLUMN `paused_on` datetime NOT NULL default '0000-00-00 00:00:00' AFTER `description`, ADD COLUMN `subtract` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `paused_on`;

INSERT INTO `<?php echo $table_prefix ?>user_ws_config_categories` (`id`,`name`,`is_system`,`type`,`category_order`) VALUES  (3,'time panel',1,0,2);


-- User config options
INSERT INTO `<?php echo $table_prefix ?>user_ws_config_options` (`id`,`category_name`,`name`,`default_value`,`config_handler_class`,`is_system`,`option_order`,`dev_comment`) VALUES 
 (23,'time panel','TM show time type','0','IntegerConfigHandler',1,0,''),
 (24,'time panel','TM report show time type','0','IntegerConfigHandler',1,0,''),
 (25,'time panel','TM user filter','0','IntegerConfigHandler',1,0,''),
 (26,'time panel','TM tasks user filter','0','IntegerConfigHandler',1,0,'');
 
 
 
INSERT INTO <?php echo $table_prefix ?>searchable_objects
  SELECT 'ProjectCharts', id, 'uid', concat('ch', format(((id+50) / 100) - 1,0), right(id + 100, 2)), project_id, 0, 0 FROM <?php echo $table_prefix ?>project_charts;

INSERT INTO <?php echo $table_prefix ?>searchable_objects
  SELECT 'Companies', id, 'uid', concat('co', format(((id+50) / 100) - 1,0), right(id + 100, 2)), 0, 0, 0 FROM <?php echo $table_prefix ?>companies;

INSERT INTO <?php echo $table_prefix ?>searchable_objects
  SELECT 'Contacts', id, 'uid', concat('ct', format(((id+50) / 100) - 1,0), right(id + 100, 2)), 0, 0, 0 FROM <?php echo $table_prefix ?>contacts;

INSERT INTO <?php echo $table_prefix ?>searchable_objects
  SELECT 'MailContents', id, 'uid', concat('mc', format(((id+50) / 100) - 1,0), right(id + 100, 2)), project_id, 0, created_by_id FROM <?php echo $table_prefix ?>mail_contents;

INSERT INTO <?php echo $table_prefix ?>searchable_objects
  SELECT 'ProjectEvents', id, 'uid', concat('ev', format(((id+50) / 100) - 1,0), right(id + 100, 2)), project_id, 0, 0 FROM <?php echo $table_prefix ?>project_events;

INSERT INTO <?php echo $table_prefix ?>searchable_objects
  SELECT 'ProjectFileRevisions', id, 'uid', concat('d', format(((file_id+50) / 100) - 1,0), right(file_id + 100, 2),'r',revision_number), 0, 0, 0 FROM <?php echo $table_prefix ?>project_file_revisions;

INSERT INTO <?php echo $table_prefix ?>searchable_objects
  SELECT 'ProjectFiles', id, 'uid', concat('d', format(((id+50) / 100) - 1,0), right(id + 100, 2)), 0, 0, 0 FROM <?php echo $table_prefix ?>project_files;

INSERT INTO <?php echo $table_prefix ?>searchable_objects
  SELECT 'ProjectMessages', id, 'uid', concat('me', format(((id+50) / 100) - 1,0), right(id + 100, 2)), 0, 0, 0 FROM <?php echo $table_prefix ?>project_messages;

INSERT INTO <?php echo $table_prefix ?>searchable_objects
  SELECT 'ProjectMilestones', id, 'uid', concat('mi', format(((id+50) / 100) - 1,0), right(id + 100, 2)), project_id, 0, 0 FROM <?php echo $table_prefix ?>project_milestones;

INSERT INTO <?php echo $table_prefix ?>searchable_objects
  SELECT 'ProjectTasks', id, 'uid', concat('ta', format(((id+50) / 100) - 1,0), right(id + 100, 2)), project_id, 0, 0 FROM <?php echo $table_prefix ?>project_tasks;

INSERT INTO <?php echo $table_prefix ?>searchable_objects
  SELECT 'ProjectWebpages', id, 'uid', concat('wp', format(((id+50) / 100) - 1,0), right(id + 100, 2)), project_id, 0, 0 FROM <?php echo $table_prefix ?>project_webpages;

INSERT INTO <?php echo $table_prefix ?>searchable_objects
  SELECT 'Projects', id, 'uid', concat('ws', format(((id+50) / 100) - 1,0), right(id + 100, 2)), id, 0, 0 FROM <?php echo $table_prefix ?>projects;

INSERT INTO <?php echo $table_prefix ?>searchable_objects
  SELECT 'Users', id, 'uid', concat('us', format(((id+50) / 100) - 1,0), right(id + 100, 2)), 0, 0, 0 FROM <?php echo $table_prefix ?>users;

ALTER TABLE `<?php echo $table_prefix ?>comments` ADD COLUMN `trashed_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `<?php echo $table_prefix ?>comments` ADD COLUMN `trashed_by_id` INTEGER(10) NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>companies` ADD COLUMN `trashed_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `<?php echo $table_prefix ?>companies` ADD COLUMN `trashed_by_id` INTEGER(10) NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>contacts` ADD COLUMN `trashed_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `<?php echo $table_prefix ?>contacts` ADD COLUMN `trashed_by_id` INTEGER(10) NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>project_charts` ADD COLUMN `trashed_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `<?php echo $table_prefix ?>project_charts` ADD COLUMN `trashed_by_id` INTEGER(10) NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>project_events` ADD COLUMN `trashed_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `<?php echo $table_prefix ?>project_events` ADD COLUMN `trashed_by_id` INTEGER(10) NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>project_file_revisions` ADD COLUMN `trashed_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `<?php echo $table_prefix ?>project_file_revisions` ADD COLUMN `trashed_by_id` INTEGER(10) NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>project_files` ADD COLUMN `trashed_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `<?php echo $table_prefix ?>project_files` ADD COLUMN `trashed_by_id` INTEGER(10) NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>project_forms` ADD COLUMN `trashed_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `<?php echo $table_prefix ?>project_forms` ADD COLUMN `trashed_by_id` INTEGER(10) NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>project_messages` ADD COLUMN `trashed_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `<?php echo $table_prefix ?>project_messages` ADD COLUMN `trashed_by_id` INTEGER(10) NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>project_milestones` ADD COLUMN `trashed_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `<?php echo $table_prefix ?>project_milestones` ADD COLUMN `trashed_by_id` INTEGER(10) NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>project_tasks` ADD COLUMN `trashed_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `<?php echo $table_prefix ?>project_tasks` ADD COLUMN `trashed_by_id` INTEGER(10) NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>project_webpages` ADD COLUMN `trashed_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `<?php echo $table_prefix ?>project_webpages` ADD COLUMN `trashed_by_id` INTEGER(10) NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>mail_contents` ADD COLUMN `trashed_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `<?php echo $table_prefix ?>mail_contents` ADD COLUMN `trashed_by_id` INTEGER(10) NOT NULL DEFAULT 0;

INSERT INTO `<?php echo $table_prefix ?>workspace_objects`
	(`workspace_id`,
	`object_manager`,
	`object_id`,
	`created_by_id`,
	`created_on`) 
SELECT
	`project_id` as `workspace_id`,
	'ApplicationLogs' as `object_manager`,
	`id` as `object_id`,
	`created_by_id`,
	`created_on`
FROM `<?php echo $table_prefix ?>application_logs` where `project_id` <> 0;

ALTER TABLE `<?php echo $table_prefix ?>application_logs` DROP COLUMN `project_id`;
ALTER TABLE `<?php echo $table_prefix ?>application_logs` MODIFY COLUMN `action` enum('upload','open','close','delete','edit','add','trash','untrash') <?php echo $default_collation ?> default NULL;

INSERT INTO `<?php echo $table_prefix ?>config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
	VALUES ('general', 'days_on_trash', '30', 'IntegerConfigHandler', 0, 0, 'Days before a file is deleted from trash. 0 = Not deleted');

UPDATE `<?php echo $table_prefix ?>file_types` SET `extension` = 'xls', `icon` = 'xls.png' WHERE `extension` = 'xsl';

-- delete deprecated user_ws_option #9

DELETE FROM `<?php echo $table_prefix ?>user_ws_config_options` where id=9 and name='my tasks is default view' limit 1;
DELETE FROM `<?php echo $table_prefix ?>user_ws_config_option_values` where option_id = 9;

INSERT INTO `<?php echo $table_prefix ?>config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES ('general', 'work_day_start_time', '9:00', 'TimeConfigHandler', 0, 0, 'Work day start time');
INSERT INTO `<?php echo $table_prefix ?>config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES ('general', 'time_format_use_24', '0', 'BoolConfigHandler', 0, 0, 'Use 24 hours time format');



-- templates

CREATE TABLE `<?php echo $table_prefix ?>templates` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) <?php echo $default_collation ?> NOT NULL default '',
  `description` text <?php echo $default_collation ?>,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned NOT NULL,
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned NOT NULL,
  `old_manager` varchar(50) NOT NULL default '',
  `old_id` int(10) unsigned NOT NULL default 0,
  PRIMARY KEY  (`id`),
  INDEX `name` (`name`),
  INDEX `updated_on` (`updated_on`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>template_objects` (
  `template_id` int(10) unsigned NOT NULL default '0',
  `object_manager` varchar(50) NOT NULL default '',
  `object_id` int(10) unsigned NOT NULL default 0,
  `created_by_id` int(10) unsigned default NULL,
  `created_on` datetime default NULL,
  PRIMARY KEY  (`template_id`, `object_manager`, `object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

-- import existing templates

INSERT INTO `<?php echo $table_prefix ?>templates`
	(`name`,
	`description`,
	`created_on`,
	`created_by_id`,
	`updated_on`,
	`updated_by_id`,
	`old_manager`,
	`old_id`)
SELECT
	`title` as `name`,
	`text` as `description`,
	`created_on`,
	`created_by_id`,
	`updated_on`,
	`updated_by_id`,
	'ProjectTasks' as `old_manager`,
	`id` as `old_id`
FROM `<?php echo $table_prefix ?>project_tasks`
WHERE `is_template` = 1 AND `parent_id` = 0;

INSERT INTO `<?php echo $table_prefix ?>templates`
	(`name`,
	`description`,
	`created_on`,
	`created_by_id`,
	`updated_on`,
	`updated_by_id`,
	`old_manager`,
	`old_id`)
SELECT
	`name`,
	`description`,
	`created_on`,
	`created_by_id`,
	`updated_on`,
	`updated_by_id`,
	'ProjectMilestones' as `old_manager`,
	`id` as `old_id`
FROM `<?php echo $table_prefix ?>project_milestones`
WHERE `is_template` = 1;

INSERT INTO `<?php echo $table_prefix ?>template_objects`
	(`template_id`,
	`object_manager`,
	`object_id`,
	`created_by_id`,
	`created_on`)
SELECT
	`id` as `template_id`,
	`old_manager` as `object_manager`,
	`old_id` as `object_id`,
	`created_by_id`,
	`created_on`
FROM `<?php echo $table_prefix ?>templates`;

UPDATE `<?php echo $table_prefix ?>workspace_templates` `a`, `<?php echo $table_prefix ?>templates` `b` SET
	`a`.`template_id` = `b`.`id`
WHERE `a`.`object_manager` = `b`.`old_manager` AND `a`.`template_id` = `b`.`old_id`;

ALTER TABLE `<?php echo $table_prefix ?>workspace_templates` DROP COLUMN `object_manager`;
ALTER TABLE `<?php echo $table_prefix ?>templates` DROP COLUMN `old_manager`;
ALTER TABLE `<?php echo $table_prefix ?>templates` DROP COLUMN `old_id`;

ALTER TABLE `<?php echo $table_prefix ?>users` ADD COLUMN `can_manage_templates` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `<?php echo $table_prefix ?>groups` ADD COLUMN `can_manage_templates` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;

UPDATE `<?php echo $table_prefix ?>users` SET `can_manage_templates` = 1 WHERE `id` = 1;
UPDATE `<?php echo $table_prefix ?>groups` SET `can_manage_templates` = 1 WHERE `id` = 10000000;
