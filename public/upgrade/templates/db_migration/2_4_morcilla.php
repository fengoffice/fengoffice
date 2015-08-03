-- <?php echo $table_prefix ?> <?php echo $table_prefix ?>
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci
-- <?php echo $engine ?> InnoDB

ALTER TABLE `<?php echo $table_prefix ?>contact_addresses` MODIFY COLUMN `street` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `<?php echo $table_prefix ?>system_permissions` ADD COLUMN `can_manage_contacts` BOOLEAN NOT NULL DEFAULT 0;

INSERT INTO `<?php echo $table_prefix ?>contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES 
	('listing preferences', 'breadcrumb_member_count', '5', 'IntegerConfigHandler', '0', '5', NULL)
ON DUPLICATE KEY UPDATE name=name;

INSERT INTO `<?php echo $table_prefix ?>object_types` (`name`,`handler_class`,`table_name`,`type`,`icon`,`plugin_id`) VALUES
 ('template_task', 'TemplateTasks', 'template_tasks', 'content_object', 'task', null),
 ('template_milestone', 'TemplateMilestones', 'template_milestones', 'content_object', 'milestone', null)
ON DUPLICATE KEY UPDATE name=name;

INSERT INTO `<?php echo $table_prefix ?>contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES 
	('general', 'timeReportDate', '4', 'IntegerConfigHandler', 1, 0, ''),
	('general', 'timeReportDateStart', '0000-00-00 00:00:00', 'DateTimeConfigHandler', 1, 0, ''),
	('general', 'timeReportDateEnd', '0000-00-00 00:00:00', 'DateTimeConfigHandler', 1, 0, ''),
	('general', 'timeReportPerson', '0', 'IntegerConfigHandler', 1, 0, ''),
	('general', 'timeReportTimeslotType', '2', 'IntegerConfigHandler', 1, 0, ''),
	('general', 'timeReportGroupBy', '0,0,0', 'StringConfigHandler', 1, 0, ''),
	('general', 'timeReportAltGroupBy', '0,0,0', 'StringConfigHandler', 1, 0, ''),
	('general', 'timeReportShowBilling', '0', 'BoolConfigHandler', 1, 0, '')
ON DUPLICATE KEY UPDATE name=name;

ALTER TABLE `<?php echo $table_prefix ?>project_tasks` ADD `from_template_object_id` int(10) unsigned DEFAULT '0' AFTER from_template_id;
ALTER TABLE `<?php echo $table_prefix ?>project_milestones` ADD `from_template_object_id` int(10) unsigned DEFAULT '0' AFTER from_template_id;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>template_tasks` (
  `template_id` int(10) unsigned DEFAULT NULL,
  `session_id` int(10) DEFAULT NULL,
  `object_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `text` text <?php echo $default_collation ?>,
  `due_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `assigned_to_contact_id` int(10) unsigned DEFAULT NULL,
  `assigned_on` datetime DEFAULT NULL,
  `assigned_by_id` int(10) unsigned DEFAULT NULL,
  `time_estimate` int(10) unsigned NOT NULL DEFAULT '0',
  `completed_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `completed_by_id` int(10) unsigned DEFAULT NULL,
  `started_on` datetime DEFAULT NULL,
  `started_by_id` int(10) unsigned NOT NULL,
  `priority` int(10) unsigned DEFAULT '200',
  `state` int(10) unsigned DEFAULT NULL,
  `order` int(10) unsigned DEFAULT '0',
  `milestone_id` int(10) unsigned DEFAULT NULL,
  `is_template` tinyint(1) NOT NULL DEFAULT '0',
  `from_template_id` int(10) NOT NULL DEFAULT '0',
  `from_template_object_id` int(10) unsigned DEFAULT '0',
  `repeat_end` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `repeat_forever` tinyint(1) NOT NULL,
  `repeat_num` int(10) unsigned NOT NULL DEFAULT '0',
  `repeat_d` int(10) unsigned NOT NULL,
  `repeat_m` int(10) unsigned NOT NULL,
  `repeat_y` int(10) unsigned NOT NULL,
  `repeat_by` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `object_subtype` int(10) unsigned NOT NULL DEFAULT '0',
  `percent_completed` int(10) unsigned NOT NULL DEFAULT '0',
  `use_due_time` tinyint(1) DEFAULT '0',
  `use_start_time` tinyint(1) DEFAULT '0',
  `original_task_id` int(10) unsigned DEFAULT '0',
  `type_content` enum('text','html') NOT NULL DEFAULT 'text',
  PRIMARY KEY (`object_id`),
  KEY `parent_id` (`parent_id`),
  KEY `completed_on` (`completed_on`),
  KEY `order` (`order`),
  KEY `milestone_id` (`milestone_id`),
  KEY `priority` (`priority`),
  KEY `assigned_to` USING HASH (`assigned_to_contact_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>template_milestones` (
  `template_id` int(10) unsigned DEFAULT NULL,
  `session_id` int(10) DEFAULT NULL,
  `object_id` int(10) unsigned NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `due_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `is_urgent` BOOLEAN NOT NULL default '0',
  `completed_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `completed_by_id` int(10) unsigned default NULL,
  `is_template` BOOLEAN NOT NULL default '0',
  `from_template_id` int(10) NOT NULL default '0',
  `from_template_object_id` int(10) unsigned DEFAULT '0',
  PRIMARY KEY  (`object_id`),
  KEY `due_date` (`due_date`),
  KEY `completed_on` (`completed_on`)
) ENGINE= InnoDB;

INSERT INTO `<?php echo $table_prefix ?>template_milestones`(`template_id`, `session_id`, `object_id`, `description`, `due_date`, `is_urgent`, `completed_on`, `completed_by_id`, `is_template`, `from_template_id`, `from_template_object_id`)
 SELECT <?php echo $table_prefix ?>template_objects.template_id,'0' AS `session_id` ,<?php echo $table_prefix ?>project_milestones.`object_id`, `description`, `due_date`, `is_urgent`, `completed_on`, `completed_by_id`, `is_template`, `from_template_id`, `from_template_object_id`
 FROM `<?php echo $table_prefix ?>template_objects` 
 INNER JOIN `<?php echo $table_prefix ?>project_milestones` 
 ON <?php echo $table_prefix ?>template_objects.object_id = <?php echo $table_prefix ?>project_milestones.object_id AND <?php echo $table_prefix ?>project_milestones.is_template=1
ON DUPLICATE KEY UPDATE <?php echo $table_prefix ?>template_milestones.template_id=<?php echo $table_prefix ?>template_milestones.template_id;

DELETE FROM `<?php echo $table_prefix ?>project_milestones` WHERE `is_template` = 1;

UPDATE `<?php echo $table_prefix ?>objects` SET object_type_id = (SELECT id FROM `<?php echo $table_prefix ?>object_types` WHERE name = 'template_milestone')
WHERE id IN (SELECT object_id FROM `<?php echo $table_prefix ?>template_milestones`);

UPDATE `<?php echo $table_prefix ?>cron_events` set enabled=0, is_system=1 WHERE name='check_upgrade';
update <?php echo $table_prefix ?>project_tasks set percent_completed=100 where completed_on <> '0000-00-00 00:00:00';


INSERT INTO `<?php echo $table_prefix ?>contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
VALUES ('mails panel', 'attach_to_notification', '1', 'BoolConfigHandler', '0', '0', NULL)
ON DUPLICATE KEY UPDATE name=name;

ALTER TABLE `<?php echo $table_prefix ?>project_files` ADD `attach_to_notification` TINYINT( 1 ) NOT NULL DEFAULT 0;
ALTER TABLE `<?php echo $table_prefix ?>project_files` ADD `default_subject` TEXT;

INSERT INTO `<?php echo $table_prefix ?>config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) 
VALUES ('general', 'notify_myself_too', 0, 'BoolConfigHandler', '0', '100', '')
ON DUPLICATE KEY UPDATE name=name;

ALTER TABLE `<?php echo $table_prefix ?>contact_member_permissions` ADD INDEX (`member_id`);