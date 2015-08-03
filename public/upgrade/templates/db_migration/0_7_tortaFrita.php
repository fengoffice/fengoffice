-- <?php echo $table_prefix ?> og_
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci

-- create "object in multiple workspaces" table
CREATE TABLE `<?php echo $table_prefix ?>workspace_objects` (
  `workspace_id` int(10) unsigned NOT NULL default 0,
  `object_manager` varchar(50) NOT NULL default '',
  `object_id` int(10) unsigned NOT NULL default 0,
  `created_by_id` int(10) unsigned default NULL,
  `created_on` datetime default NULL,
  PRIMARY KEY  (`workspace_id`, `object_manager`, `object_id`),
  KEY `workspace_id` (`workspace_id`),
  KEY `object_manager` (`object_manager`),
  KEY `object_id` (`object_id`)
) ENGINE=InnoDB <?php echo $default_charset ?>;

-- migrate messages to "multiple workspaces"
INSERT INTO `<?php echo $table_prefix ?>workspace_objects`
	(`workspace_id`,
	`object_manager`,
	`object_id`,
	`created_by_id`,
	`created_on`) 
SELECT
	`project_id` as `workspace_id`,
	'ProjectMessages' as `object_manager`,
	`id` as `object_id`,
	`created_by_id`,
	`created_on`
FROM `<?php echo $table_prefix ?>project_messages` where `project_id` <> 0;

ALTER TABLE `<?php echo $table_prefix ?>project_messages` DROP COLUMN `project_id`;

ALTER TABLE `<?php echo $table_prefix ?>mail_accounts` ADD COLUMN `smtp_server` VARCHAR(100) NOT NULL default '';
ALTER TABLE `<?php echo $table_prefix ?>mail_accounts` ADD COLUMN `smtp_use_auth` INTEGER UNSIGNED NOT NULL default 0;
ALTER TABLE `<?php echo $table_prefix ?>mail_accounts` ADD COLUMN `smtp_username` VARCHAR(100) default '';
ALTER TABLE `<?php echo $table_prefix ?>mail_accounts` ADD COLUMN `smtp_password` VARCHAR(100) default '';
ALTER TABLE `<?php echo $table_prefix ?>mail_accounts` ADD COLUMN `smtp_port` INTEGER UNSIGNED NOT NULL default 25;

CREATE TABLE  `<?php echo $table_prefix ?>project_charts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `project_id` int(10) unsigned default NULL,
  `type_id` int(10) unsigned default NULL,
  `display_id` int(10) unsigned default NULL,
  `title` varchar(100) <?php echo $default_collation ?> default NULL,
  `show_in_project` tinyint(1) unsigned NOT NULL default '1',
  `show_in_parents` tinyint(1) unsigned NOT NULL default '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default NULL,
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>project_chart_params` (
  `id` int(10) unsigned NOT NULL,
  `chart_id` int(10) unsigned NOT NULL,
  `value` varchar(80) NOT NULL,
  PRIMARY KEY  (`id`,`chart_id`)
) ENGINE=InnoDB <?php echo $default_charset ?>;

ALTER TABLE `<?php echo $table_prefix ?>project_webpages` MODIFY COLUMN `url` TEXT <?php echo $default_collation ?> NOT NULL;

-- migrate files to "multiple workspaces"
INSERT INTO `<?php echo $table_prefix ?>workspace_objects`
	(`workspace_id`,
	`object_manager`,
	`object_id`,
	`created_by_id`,
	`created_on`) 
SELECT
	`project_id` as `workspace_id`,
	'ProjectFiles' as `object_manager`,
	`id` as `object_id`,
	`created_by_id`,
	`created_on`
FROM `<?php echo $table_prefix ?>project_files` where `project_id` <> 0;

ALTER TABLE `<?php echo $table_prefix ?>project_files` DROP COLUMN `project_id`;


UPDATE `<?php echo $table_prefix ?>project_file_revisions` SET
	`type_string` = 'text/html'
WHERE `type_string` = 'txt';

ALTER TABLE `<?php echo $table_prefix ?>project_webpages` ADD COLUMN `updated_on` datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE `<?php echo $table_prefix ?>project_webpages` ADD COLUMN `updated_by_id` int(10) unsigned default NULL;

UPDATE `<?php echo $table_prefix ?>users` SET `auto_assign` = 0;

UPDATE `<?php echo $table_prefix ?>project_tasks` SET
	`title` = `text`
WHERE `title` = '' OR `title` = NULL;
