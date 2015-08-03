-- <?php echo $table_prefix ?> og_
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci


 
CREATE TABLE `<?php echo $table_prefix ?>read_objects` (
  `rel_object_manager` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `rel_object_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `is_read` int(1) NOT NULL default '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`rel_object_manager`,`rel_object_id`,`user_id`)
) ENGINE=InnoDB <?php echo $default_charset ?>;


INSERT INTO  `<?php echo $table_prefix ?>read_objects`
	SELECT 'MailContents' as rel_object_manager, id as rel_object_id, created_by_id as user_id, is_read, created_on FROM `<?php echo $table_prefix ?>mail_contents`;


ALTER TABLE `<?php echo $table_prefix ?>mail_contents` DROP `is_read`;
ALTER TABLE `<?php echo $table_prefix ?>mail_contents` ADD `from_name` VARCHAR( 250 ) NULL AFTER `from` ;
ALTER TABLE `<?php echo $table_prefix ?>mail_contents` ADD `state` INT( 1 ) NOT NULL DEFAULT '0' COMMENT '0:nothing, 1:sent; 2:draft' AFTER `size` ;

ALTER TABLE `<?php echo $table_prefix ?>project_tasks` ADD COLUMN `from_template_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `<?php echo $table_prefix ?>project_milestones` ADD COLUMN `from_template_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;

-- Task or other object templates that are included in a workspace
CREATE TABLE `<?php echo $table_prefix ?>workspace_templates` (
  `workspace_id` int(10) unsigned NOT NULL default 0,
  `object_manager` varchar(50) NOT NULL default '',
  `template_id` int(10) unsigned NOT NULL default 0,
  `include_subws` int(1) unsigned NOT NULL default 0,
  `created_by_id` int(10) unsigned default NULL,
  `created_on` datetime default NULL,
  PRIMARY KEY  (`workspace_id`, `object_manager`, `template_id`),
  KEY `workspace_id` (`workspace_id`),
  KEY `object_manager` (`object_manager`),
  KEY `object_id` (`template_id`)
) ENGINE=InnoDB <?php echo $default_charset ?>;

ALTER TABLE `<?php echo $table_prefix ?>searchable_objects` ADD COLUMN `user_id` INTEGER(10) UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE `<?php echo $table_prefix ?>contacts` ADD COLUMN `updated_by_id` INTEGER(10) UNSIGNED NOT NULL DEFAULT 0;


-- user and-or workspace configuration options
CREATE TABLE  `<?php echo $table_prefix ?>user_ws_config_categories` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `is_system` tinyint(1) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `category_order` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `order` (`category_order`)
) ENGINE=InnoDB <?php echo $default_charset ?>;


CREATE TABLE `<?php echo $table_prefix ?>user_ws_config_options` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `category_name` varchar(30) <?php echo $default_collation ?> NOT NULL default '',
  `name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `default_value` text <?php echo $default_collation ?>,
  `config_handler_class` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `is_system` tinyint(1) unsigned NOT NULL default '0',
  `option_order` smallint(5) unsigned NOT NULL default '0',
  `dev_comment` varchar(255) <?php echo $default_collation ?> default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `order` (`option_order`),
  KEY `category_id` (`category_name`)
) ENGINE=InnoDB <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>user_ws_config_option_values` (
  `option_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `workspace_id` int(10) unsigned NOT NULL default '0',
  `value` text <?php echo $default_collation ?>,
  PRIMARY KEY  (`option_id`,`user_id`,`workspace_id`),
  KEY `option_id` (`option_id`)
) ENGINE=InnoDB <?php echo $default_charset ?>;

-- default config option data

INSERT INTO `<?php echo $table_prefix ?>user_ws_config_categories` (`id`,`name`,`is_system`,`type`,`category_order`) VALUES 
 (1,'dashboard',0,0,0),
 (2,'task panel',0,0,1);
 
 INSERT INTO `<?php echo $table_prefix ?>user_ws_config_options` (`id`,`category_name`,`name`,`default_value`,`config_handler_class`,`is_system`,`option_order`,`dev_comment`) VALUES 
 (1,'dashboard','show calendar widget','1','BoolConfigHandler',0,0,''),
 (2,'dashboard','show late tasks and milestones widget','1','BoolConfigHandler',0,100,''),
 (3,'dashboard','show pending tasks widget','1','BoolConfigHandler',0,200,''),
 (4,'dashboard','pending tasks widget assigned to filter','0:0','UserCompanyConfigHandler',0,210,''),
 (5,'dashboard','show emails widget','1','BoolConfigHandler',0,300,''),
 (6,'dashboard','show messages widget','1','BoolConfigHandler',0,400,''),
 (7,'dashboard','show documents widget','1','BoolConfigHandler',0,500,''),
 (8,'dashboard','show charts widget','1','BoolConfigHandler',0,600,''),
 (9,'task panel','my tasks is default view','1','BoolConfigHandler',0,0,'');
 
 ALTER TABLE `<?php echo $table_prefix ?>projects` ADD COLUMN `p1` INTEGER UNSIGNED NOT NULL DEFAULT 0;
 ALTER TABLE `<?php echo $table_prefix ?>projects` ADD COLUMN `p2` INTEGER UNSIGNED NOT NULL DEFAULT 0;
 ALTER TABLE `<?php echo $table_prefix ?>projects` ADD COLUMN `p3` INTEGER UNSIGNED NOT NULL DEFAULT 0;
 ALTER TABLE `<?php echo $table_prefix ?>projects` ADD COLUMN `p4` INTEGER UNSIGNED NOT NULL DEFAULT 0;
 ALTER TABLE `<?php echo $table_prefix ?>projects` ADD COLUMN `p5` INTEGER UNSIGNED NOT NULL DEFAULT 0;
 ALTER TABLE `<?php echo $table_prefix ?>projects` ADD COLUMN `p6` INTEGER UNSIGNED NOT NULL DEFAULT 0;
 ALTER TABLE `<?php echo $table_prefix ?>projects` ADD COLUMN `p7` INTEGER UNSIGNED NOT NULL DEFAULT 0;
 ALTER TABLE `<?php echo $table_prefix ?>projects` ADD COLUMN `p8` INTEGER UNSIGNED NOT NULL DEFAULT 0;
 ALTER TABLE `<?php echo $table_prefix ?>projects` ADD COLUMN `p9` INTEGER UNSIGNED NOT NULL DEFAULT 0;
 ALTER TABLE `<?php echo $table_prefix ?>projects` ADD COLUMN `p10` INTEGER UNSIGNED NOT NULL DEFAULT 0;
 
 ALTER TABLE `<?php echo $table_prefix ?>mail_accounts` ADD `email_addr` VARCHAR( 100 ) <?php echo $default_collation ?> NOT NULL AFTER `email`;
 UPDATE `<?php echo $table_prefix ?>mail_accounts` Set `email_addr` = `email`;
 
ALTER TABLE `<?php echo $table_prefix ?>project_files` ADD COLUMN `was_auto_checked_out` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;
