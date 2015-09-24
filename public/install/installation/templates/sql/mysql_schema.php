-- <?php echo $table_prefix ?> fo_
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci
-- <?php echo $engine ?> InnoDB
-- varchar cannot be larger than 256
-- blob/text cannot have default values
-- sql queries must finish with ;\n (line break inmediately after ;)

CREATE TABLE `<?php echo $table_prefix ?>administration_tools` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `controller` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `action` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `order` tinyint(3) unsigned NOT NULL default '0',
  `visible` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>dimensions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `code` varchar(30) <?php echo $default_collation ?> NOT NULL default '',
  `name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `is_root` tinyint(1) unsigned NOT NULL default '0',
  `is_manageable` tinyint(1) unsigned NOT NULL default '0',
  `allows_multiple_selection` tinyint(1) unsigned NOT NULL default '0',
  `defines_permissions` tinyint(1) unsigned NOT NULL default '0',
  `is_system` tinyint(1) unsigned NOT NULL default '0',
  `is_default` tinyint(1) unsigned NOT NULL default '0',
  `default_order` int(10) NOT NULL default '0',
  `options` TEXT NOT NULL,
  `permission_query_method` enum('mandatory','not_mandatory') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'mandatory',
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `code` (`code`) USING BTREE,
  KEY `by_name` (`name`),
  KEY `defines_perm`(`defines_permissions`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>members` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `dimension_id` int(10) unsigned NOT NULL,
  `object_type_id` int(10) unsigned NOT NULL,
  `parent_member_id` int(10) unsigned NOT NULL default '0',
  `depth` int(2) unsigned NOT NULL,
  `name` varchar(160) <?php echo $default_collation ?> NOT NULL default '',
  `description` TEXT NOT NULL,
  `object_id` int(10) unsigned,
  `order` int(10) unsigned NOT NULL default '0',
  `color` int(10) unsigned NOT NULL default '0',
  `archived_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `by_parent` USING HASH (`parent_member_id`),
  KEY `by_dimension` (`dimension_id`,`parent_member_id`,`name`),
  KEY `by_object_id` (`object_id`),
  KEY `archived_on` (`archived_on`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>member_restrictions` (
  `member_id` int(10) unsigned NOT NULL,
  `restricted_member_id` int(10) unsigned NOT NULL,
  `order` smallint unsigned NOT NULL,
  PRIMARY KEY  (`member_id`,`restricted_member_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>member_property_members` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `association_id` int(10) unsigned NOT NULL,	
  `member_id` int(10) unsigned NOT NULL,
  `property_member_id` int(10) unsigned NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL default '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  INDEX `member_id_property_member_id` (`member_id`, `property_member_id`),
  INDEX  `is_active` (`is_active`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>dimension_member_restriction_definitions` (
  `dimension_id` int(10) unsigned NOT NULL,
  `object_type_id` int(10) unsigned NOT NULL,
  `restricted_dimension_id` int(10) unsigned NOT NULL,
  `restricted_object_type_id` int(10) unsigned NOT NULL,
  `is_orderable` tinyint(1) unsigned NOT NULL default '0',
  `enforce_order_progression` tinyint(1) unsigned NOT NULL default '0',
  `is_required` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`dimension_id`,`object_type_id`,`restricted_dimension_id`,`restricted_object_type_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>dimension_member_associations` (
  `id` int(10) unsigned NOT NULL auto_increment,	
  `dimension_id` int(10) unsigned NOT NULL,
  `object_type_id` int(10) unsigned NOT NULL,
  `associated_dimension_id` int(10) unsigned NOT NULL,
  `associated_object_type_id` int(10) unsigned NOT NULL,
  `is_required` tinyint(1) unsigned NOT NULL default '0',
  `is_multiple` tinyint(1) unsigned NOT NULL default '0',
  `keeps_record` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `by_associated` USING HASH (`associated_dimension_id`,`associated_object_type_id`),
  KEY `by_dimension_objtype` USING HASH (`dimension_id`, `object_type_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>dimension_object_types` (
  `dimension_id` int(10) unsigned NOT NULL,
  `object_type_id` int(10) unsigned NOT NULL,
  `is_root` tinyint(1) unsigned NOT NULL default '0',
  `options` TEXT NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY  (`dimension_id`,`object_type_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>dimension_object_type_hierarchies` (
  `dimension_id` int(10) unsigned NOT NULL,
  `parent_object_type_id` int(10) unsigned NOT NULL,
  `child_object_type_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`dimension_id`,`parent_object_type_id`,`child_object_type_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>dimension_options` (
  `dimension_id` INTEGER UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `value` TEXT NOT NULL,
  PRIMARY KEY (`dimension_id`, `name`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>dimension_object_type_options` (
  `dimension_id` INTEGER UNSIGNED NOT NULL,
  `object_type_id` INTEGER UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `value` TEXT NOT NULL,
  PRIMARY KEY (`dimension_id`, object_type_id, `name`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>object_members` (
  `object_id` int(10) unsigned NOT NULL,
  `member_id` int(10) unsigned NOT NULL,
  `is_optimization` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`object_id`,`member_id`),
  KEY `member_id` (`member_id`),
  INDEX `is_optimization` (`is_optimization`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>dimension_object_type_contents` (
  `dimension_id` int(10) unsigned NOT NULL,
  `dimension_object_type_id` int(10) unsigned NOT NULL,
  `content_object_type_id` int(10) unsigned NOT NULL,
  `is_required` tinyint(1) unsigned NOT NULL default '0',
  `is_multiple` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`dimension_id`,`dimension_object_type_id`,`content_object_type_id`),
  KEY `by_co_obj_type` USING HASH (`content_object_type_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>object_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `handler_class` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `table_name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `type` enum('content_object','dimension_object','dimension_group', 'located', 'comment', '') <?php echo $default_collation ?> default NULL,
  `icon` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `plugin_id` int(10) unsigned not null default 0,
  `uses_order` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`),
  KEY `plugin_id` USING HASH (`plugin_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>objects` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `object_type_id` int(10) unsigned NOT NULL,
  `name` varchar(255) <?php echo $default_collation ?> NOT NULL default '',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default NULL,	
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned default NULL,
  `trashed_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned default NULL,
  `archived_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `created_on` (`created_on`),
  KEY `updated_on` (`updated_on`),
  KEY `trashed_on` (`trashed_on`),
  KEY `archived_on` (`archived_on`),
  KEY `object_type` (`object_type_id`),
  KEY `name` USING HASH (`name`),
  KEY `type_trash_arch` (`object_type_id`,`trashed_on`,`archived_on`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>plugins` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `is_installed` tinyint(1) unsigned NOT NULL default '0',
  `is_activated` tinyint(1) unsigned NOT NULL default '0',
  `priority` smallint unsigned NOT NULL default '0',
  `activated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `activated_by_id` int(10) unsigned default NULL,
  `version` int(10) unsigned default '1',
  PRIMARY KEY  (`id`), 
  UNIQUE KEY `name` (`name`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>permission_groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `contact_id` int(10) unsigned,
  `is_context` tinyint(1) unsigned NOT NULL default '0',
  `plugin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `parent_id` int(10) unsigned NOT NULL default '0',
  `type` ENUM( 'roles', 'permission_groups', 'user_groups') NULL,
  PRIMARY KEY  (`id`), 
  UNIQUE KEY `name` (`name`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>system_permissions` (
  `permission_group_id` int(10) unsigned NOT NULL,
  `can_manage_security` tinyint(1) unsigned NOT NULL default '0',
  `can_manage_configuration` tinyint(1) unsigned NOT NULL default '0',
  `can_manage_templates` tinyint(1) unsigned NOT NULL default '0',
  `can_manage_time` tinyint(1) unsigned NOT NULL default '0',
  `can_add_mail_accounts` tinyint(1) unsigned NOT NULL default '0',
  `can_manage_dimensions` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_dimension_members` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_tasks` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_task_assignee` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_billing` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_view_billing` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_see_assigned_to_other_tasks` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `can_manage_contacts` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_update_other_users_invitations` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_link_objects` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY  (`permission_group_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>max_system_permissions` (
  `permission_group_id` int(10) unsigned NOT NULL,
  `can_manage_security` tinyint(1) unsigned NOT NULL default '0',
  `can_manage_configuration` tinyint(1) unsigned NOT NULL default '0',
  `can_manage_templates` tinyint(1) unsigned NOT NULL default '0',
  `can_manage_time` tinyint(1) unsigned NOT NULL default '0',
  `can_add_mail_accounts` tinyint(1) unsigned NOT NULL default '0',
  `can_manage_dimensions` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_dimension_members` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_tasks` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_task_assignee` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_billing` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_view_billing` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_see_assigned_to_other_tasks` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `can_manage_contacts` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_update_other_users_invitations` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_link_objects` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY  (`permission_group_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>specific_permissions` (
  `permission_group_id` int(10) unsigned NOT NULL,
  `can_change_project_status` tinyint(1) unsigned NOT NULL default '0',
  `can_revert_project_status` tinyint(1) unsigned NOT NULL default '0',
  `can_assign_supervisor` tinyint(1) unsigned NOT NULL default '0',
  `can_extend_quota` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`permission_group_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>contact_dimension_permissions` (
  `permission_group_id` int(10) unsigned NOT NULL,
  `dimension_id` int(10) unsigned NOT NULL,
  `permission_type` enum('allow all','deny all','check') <?php echo $default_collation ?> default NULL,
  PRIMARY KEY  (`permission_group_id`, `dimension_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>contact_member_permissions` (
  `permission_group_id` int(10) unsigned NOT NULL,
  `member_id` int(10) unsigned NOT NULL,
  `object_type_id` int(10) unsigned NOT NULL,
  `can_write` tinyint(1) unsigned NOT NULL default '0',
  `can_delete` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`permission_group_id`, `member_id`, `object_type_id`),
  KEY `member_id`(`member_id`),
  KEY `obj_type`(`object_type_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>contact_permission_groups` (
  `contact_id` int(10) unsigned NOT NULL,
  `permission_group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`contact_id`, `permission_group_id`),
  KEY `contact_id` (`contact_id`),
  KEY `permission_group_id` (`permission_group_id`)  
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>permission_contexts` (
  `contact_id` int(10) unsigned NOT NULL,
  `permission_group_id` int(10) unsigned NOT NULL,
  `member_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`contact_id`, `permission_group_id`, `member_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>contacts` (
  `object_id` int(10) unsigned NOT NULL auto_increment,
  `first_name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `surname` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `is_company` tinyint(1) unsigned NOT NULL default '0',
  `company_id` int(10) unsigned,
  `department` varchar(50) <?php echo $default_collation ?> default NULL,
  `job_title` varchar(50) <?php echo $default_collation ?> default NULL,
  `birthday` datetime default NULL,
  `timezone` float(3,1) NOT NULL default '0.0',
  `user_type` smallint unsigned NOT NULL default '0',
  `is_active_user` tinyint(1) unsigned NOT NULL default '0',
  `token` varchar(40) <?php echo $default_collation ?> NOT NULL default '',
  `salt` varchar(13) <?php echo $default_collation ?> NOT NULL default '',
  `twister` varchar(10) <?php echo $default_collation ?> NOT NULL default '',
  `display_name` varchar(50) <?php echo $default_collation ?> default NULL,
  `permission_group_id` int(10) unsigned NOT NULL,
  `username` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `contact_passwords_id` int(10) unsigned NOT NULL,
  `picture_file` varchar(100) <?php echo $default_collation ?> default NULL,
  `picture_file_small` varchar(100) <?php echo $default_collation ?> default NULL,
  `picture_file_medium` varchar(100) <?php echo $default_collation ?> default NULL,
  `avatar_file` varchar(44) <?php echo $default_collation ?> default NULL,
  `comments` text <?php echo $default_collation ?>,
  `last_login` DATETIME <?php echo $default_collation ?>,
  `last_visit` DATETIME <?php echo $default_collation ?>,
  `last_activity` DATETIME <?php echo $default_collation ?>,
  `personal_member_id` int(10) unsigned,
  `disabled` tinyint(1) NOT NULL default 0,
  `default_billing_id` int(10) NOT NULL default 0,
  PRIMARY KEY  (`object_id`),
  KEY `first_name` USING BTREE (`first_name`,`surname`),
  KEY `surname` USING BTREE (`surname`,`first_name`),
  KEY `company` (`is_company`,`company_id`,`department`),
  KEY `username` (`user_type`,`username`),
  KEY `perm_group` USING HASH (`permission_group_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>contact_addresses` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `contact_id` int(10) unsigned NOT NULL, 
  `address_type_id` int(10) unsigned NOT NULL,
  `street` text <?php echo $default_collation ?>,
  `city` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `state` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `country` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `zip_code` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `is_main` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `by_contact` USING HASH (`contact_id`,`is_main`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>contact_telephones` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `contact_id` int(10) unsigned NOT NULL, 
  `telephone_type_id` int(10) unsigned NOT NULL, 
  `number` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `name` varchar(256) <?php echo $default_collation ?> NOT NULL DEFAULT '',
  `is_main` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `by_contact` (`contact_id`,`is_main`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>contact_emails` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `contact_id` int(10) unsigned NOT NULL, 
  `email_type_id` int(10) unsigned NOT NULL, 
  `email_address` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `is_main` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `by_contact` (`contact_id`,`is_main`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>contact_web_pages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `contact_id` int(10) unsigned NOT NULL, 
  `web_type_id` int(10) unsigned NOT NULL,
  `url` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `by_contact` USING HASH (`contact_id`,`web_type_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>contact_im_values` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `contact_id` int(10) unsigned NOT NULL, 
  `im_type_id` int(10) unsigned NOT NULL, 
  `value` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `is_main` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `by_contact` (`contact_id`,`is_main`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>address_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(30) <?php echo $default_collation ?> NOT NULL default '',
  `is_system` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`) 
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>telephone_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(30) <?php echo $default_collation ?> NOT NULL default '',
  `is_system` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>email_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(30) <?php echo $default_collation ?> NOT NULL default '',
  `is_system` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>webpage_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(30) <?php echo $default_collation ?> NOT NULL default '',
  `is_system` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>im_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(30) <?php echo $default_collation ?> NOT NULL default '',
  `icon` varchar(30) <?php echo $default_collation ?> NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>application_logs` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `taken_by_id` int(10) unsigned default NULL,
  `rel_object_id` int(10) NOT NULL default '0',
  `object_name` text <?php echo $default_collation ?>,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default NULL,
  `action` enum('upload','open','close','delete','edit','add','trash','untrash','subscribe','unsubscribe','tag','comment','link','unlink','login','logout','untag','archive','unarchive','move','copy','read','download','checkin','checkout') <?php echo $default_collation ?> default NULL,
  `is_private` tinyint(1) unsigned NOT NULL default '0',
  `is_silent` tinyint(1) unsigned NOT NULL default '0',
  `member_id` int(10) NOT NULL default '0',
  `log_data` text <?php echo $default_collation ?>,
  PRIMARY KEY  (`id`),
  KEY `created_on` USING BTREE (`created_on`,`is_silent`),
  KEY `object` (`rel_object_id`,`created_on`,`is_silent`),
  KEY `member` (`member_id`,`created_on`,`is_silent`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>comments` (
  `object_id` int(10) unsigned NOT NULL auto_increment,
  `rel_object_id` int(10) unsigned NOT NULL default '0',
  `text` text <?php echo $default_collation ?>,
  `author_name` varchar(50) <?php echo $default_collation ?> default NULL,
  `author_email` varchar(100) <?php echo $default_collation ?> default NULL,
  `author_homepage` varchar(100) <?php echo $default_collation ?> NOT NULL default '',
  PRIMARY KEY  (`object_id`),
  KEY `object_id` (`rel_object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;


CREATE TABLE `<?php echo $table_prefix ?>config_categories` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `is_system` tinyint(1) unsigned NOT NULL default '0',
  `category_order` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `order` (`category_order`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>config_options` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `category_name` varchar(30) <?php echo $default_collation ?> NOT NULL default '',
  `name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `value` text <?php echo $default_collation ?>,
  `config_handler_class` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `is_system` tinyint(1) unsigned NOT NULL default '0',
  `option_order` smallint(5) unsigned NOT NULL default '0',
  `dev_comment` varchar(255) <?php echo $default_collation ?> default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `order` (`option_order`),
  KEY `category_id` (`category_name`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>file_repo` (
  `id` varchar(40) <?php echo $default_collation ?> NOT NULL default '',
  `content` longblob NOT NULL,
  `order` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `order` (`order`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>file_repo_attributes` (
  `id` varchar(40) <?php echo $default_collation ?> NOT NULL default '',
  `attribute` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `value` text <?php echo $default_collation ?> NOT NULL,
  PRIMARY KEY  (`id`,`attribute`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>file_types` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `extension` varchar(10) <?php echo $default_collation ?> NOT NULL default '',
  `icon` varchar(30) <?php echo $default_collation ?> NOT NULL default '',
  `is_searchable` tinyint(1) unsigned NOT NULL default '0',
  `is_image` tinyint(1) unsigned NOT NULL default '0',
  `is_allow` TINYINT( 1 ) NOT NULL DEFAULT '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `extension` (`extension`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>linked_objects` (
  `rel_object_id` int(10) unsigned NOT NULL default '0',
  `object_id` int(10) unsigned NOT NULL default '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default NULL,
  PRIMARY KEY(`rel_object_id`,`object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>object_subscriptions` (
  `object_id` int(10) unsigned NOT NULL default '0',
  `contact_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`object_id`,`contact_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>object_reminders` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `object_id` int(10) unsigned NOT NULL default '0',
  `contact_id` int(10) unsigned NOT NULL default '0',
  `type` VARCHAR(40) NOT NULL default '',
  `context` varchar(40) NOT NULL default '',
  `minutes_before` int(10) default NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `type_date` (`type`,`date`),
  KEY `obj_date` (`object_id`,`date`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>object_reminder_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` VARCHAR(40) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>object_contact_permissions` (
  `rel_object_id` INTEGER UNSIGNED NOT NULL,
  `contact_id` INTEGER UNSIGNED NOT NULL,
  `can_read` TINYINT(1) UNSIGNED NOT NULL,
  `can_write` TINYINT(1) UNSIGNED NOT NULL,
  `can_delete` TINYINT(1) UNSIGNED NOT NULL,
  PRIMARY KEY(`rel_object_id`, `contact_id`)
) ENGINE = <?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>object_properties` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `rel_object_id` int(10) unsigned NOT NULL,
  `name` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`id`),
  INDEX `ObjectID` (`rel_object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>project_events` (
  `object_id` int(10) unsigned NOT NULL,
  `start` datetime default NULL,
  `duration` datetime default NULL,
  `description` text <?php echo $default_collation ?>,
  `private` char(1) <?php echo $default_collation ?> NOT NULL default '0',
  `repeat_end` date default NULL,
  `repeat_forever` TINYINT(1) UNSIGNED NOT NULL,
  `repeat_num` mediumint(9) NOT NULL default '0',
  `repeat_d` smallint(6) NOT NULL default '0',
  `repeat_m` smallint(6) NOT NULL default '0',
  `repeat_y` smallint(6) NOT NULL default '0',
  `repeat_h` smallint(6) NOT NULL default '0',
  `repeat_dow` int(10) unsigned NOT NULL default '0',
  `repeat_wnum` int(10) unsigned NOT NULL default '0',
  `repeat_mjump` int(10) unsigned NOT NULL default '0',
  `type_id` int(11) NOT NULL default '0',
  `special_id` text <?php echo $default_collation ?>,
  `update_sync` DATETIME DEFAULT NULL,
  `ext_cal_id` INT(10) UNSIGNED NOT NULL,
  `original_event_id` INT( 10 ) UNSIGNED NULL DEFAULT '0',
  PRIMARY KEY  (`object_id`),
  KEY `start` (`start`),
  KEY `repeat_h` (`repeat_h`),
  KEY `type_id` (`type_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>project_file_revisions` (
  `object_id` int(10) unsigned NOT NULL auto_increment,
  `file_id` int(10) unsigned NOT NULL default '0',
  `file_type_id` smallint(5) unsigned NOT NULL default '0',
  `repository_id` varchar(40) <?php echo $default_collation ?> NOT NULL default '',
  `thumb_filename` varchar(44) <?php echo $default_collation ?> default NULL,
  `revision_number` int(10) unsigned NOT NULL default '0',
  `comment` text <?php echo $default_collation ?>,
  `type_string` varchar(255) <?php echo $default_collation ?> NOT NULL default '',
  `filesize` int(10) unsigned NOT NULL default '0',
  `hash` text <?php echo $default_collation ?>,
  PRIMARY KEY  (`object_id`),
  KEY `filesize` (`filesize`),
  KEY `file_id` USING BTREE (`file_id`,`revision_number`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>project_files` (
  `object_id` int(10) unsigned NOT NULL,
  `description` text <?php echo $default_collation ?>,
  `is_locked` tinyint(1) unsigned NOT NULL default '0',
  `is_visible` tinyint(1) unsigned NOT NULL default '0',
  `expiration_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `checked_out_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `checked_out_by_id` int(10) unsigned DEFAULT 0,
  `was_auto_checked_out` tinyint(1) unsigned NOT NULL default '0',
  `type` int(1) NOT NULL DEFAULT 0,
  `url` varchar(255) NULL,
  `mail_id` int(10) unsigned NOT NULL default '0',
  `attach_to_notification` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `default_subject` text <?php echo $default_collation ?> NOT NULL,
  PRIMARY KEY  (`object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>project_forms` (
  `object_id` int(10) unsigned NOT NULL,
  `description` text <?php echo $default_collation ?> NOT NULL,
  `success_message` text <?php echo $default_collation ?> NOT NULL,
  `action` enum('add_comment','add_task') <?php echo $default_collation ?> NOT NULL default 'add_comment',
  `in_object_id` int(10) unsigned NOT NULL default '0',
  `is_visible` tinyint(1) unsigned NOT NULL default '0',
  `is_enabled` tinyint(1) unsigned NOT NULL default '0',
  `order` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>project_messages` (
  `object_id` int(10) unsigned NOT NULL,
  `text` text <?php echo $default_collation ?>,
  `type_content` ENUM( 'text', 'html' ) NOT NULL DEFAULT 'text',
  PRIMARY KEY  (`object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>project_milestones` (
  `object_id` int(10) unsigned NOT NULL,
  `description` text <?php echo $default_collation ?>,
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
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>project_tasks` (
  `object_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned default NULL,
  `parents_path` varchar(255) NOT NULL default '',
  `depth` int(2) unsigned NOT NULL default '0',
  `text` text <?php echo $default_collation ?>,
  `due_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `start_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `assigned_to_contact_id` int(10) unsigned default NULL,
  `assigned_on` datetime default NULL,
  `assigned_by_id` int(10) unsigned default NULL,
  `time_estimate` int(10) unsigned NOT NULL default '0',
  `completed_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `completed_by_id` int(10) unsigned default NULL,
  `started_on` DATETIME DEFAULT NULL,
  `started_by_id` INTEGER UNSIGNED NOT NULL,
  `priority` INTEGER UNSIGNED default 200,
  `state` INTEGER UNSIGNED,
  `order` int(10) unsigned  default '0',
  `milestone_id` INTEGER UNSIGNED,
  `is_template` BOOLEAN NOT NULL default '0',
  `from_template_id` int(10) NOT NULL default '0',
  `from_template_object_id` int(10) unsigned DEFAULT '0',
  `repeat_end` DATETIME NOT NULL default '0000-00-00 00:00:00',
  `repeat_forever` tinyint(1) NOT NULL,
  `repeat_num` int(10) unsigned NOT NULL default '0',
  `repeat_d` int(10) unsigned NOT NULL,
  `repeat_m` int(10) unsigned NOT NULL,
  `repeat_y` int(10) unsigned NOT NULL,
  `repeat_by` varchar(15) collate utf8_unicode_ci NOT NULL default '',
  `object_subtype` int(10) unsigned NOT NULL default '0',
  `percent_completed` int(10) unsigned NOT NULL default '0',
  `use_due_time` BOOLEAN default '0',
  `use_start_time` BOOLEAN default '0',
  `original_task_id` INT( 10 ) UNSIGNED NULL DEFAULT '0',
  `instantiation_id` int(10) unsigned NOT NULL default '0',
  `type_content` ENUM( 'text', 'html' ) NOT NULL DEFAULT 'text',
  PRIMARY KEY  (`object_id`),
  KEY `parent_id` (`parent_id`),
  KEY `completed_on` (`completed_on`),
  KEY `order` (`order`),
  KEY `milestone_id` (`milestone_id`),
  KEY `priority` (`priority`),
  KEY `assigned_to` USING HASH (`assigned_to_contact_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>workspaces` (
  `object_id` int(10) unsigned NOT NULL auto_increment,
  `description` text <?php echo $default_collation ?>,
  `show_description_in_overview` tinyint(1) unsigned NOT NULL default '0',
  `color` int(10) unsigned default 0,
  PRIMARY KEY  (`object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>searchable_objects` (
  `rel_object_id` int(10) unsigned NOT NULL default '0',
  `column_name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `content` text <?php echo $default_collation ?> NOT NULL,
  `contact_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`rel_object_id`,`column_name`),
  FULLTEXT KEY `content` (`content`),
  KEY `rel_obj_id` (`rel_object_id`)
) ENGINE=MyISAM <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>project_webpages` (
  `object_id` int(10) unsigned NOT NULL,
  `url` text <?php echo $default_collation ?>,
  `description` text <?php echo $default_collation ?>,
  PRIMARY KEY  (`object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;


-- save gui state
CREATE TABLE  `<?php echo $table_prefix ?>guistate` (
  `contact_id` int(10) unsigned NOT NULL default '1',
  `name` varchar(100) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`contact_id`,`name`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>project_charts` (
  `object_id` int(10) unsigned NOT NULL,
  `type_id` int(10) unsigned default NULL,
  `display_id` int(10) unsigned default NULL,
  `show_in_project` tinyint(1) unsigned NOT NULL default '1',
  `show_in_parents` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>project_chart_params` (
  `id` int(10) unsigned NOT NULL,
  `chart_id` int(10) unsigned NOT NULL,
  `value` varchar(80) NOT NULL,
  PRIMARY KEY  (`id`,`chart_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>timeslots` (
  `object_id` int(10) unsigned NOT NULL auto_increment,
  `rel_object_id` int(10) unsigned NOT NULL,
  `start_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `end_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `contact_id` int(10) unsigned NOT NULL,
  `description` text <?php echo $default_collation ?> NOT NULL,
  `paused_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `subtract` int(10) unsigned NOT NULL default '0',
  `fixed_billing` float NOT NULL default '0',
  `hourly_billing` float NOT NULL default '0',
  `is_fixed_billing` float NOT NULL default '0',
  `billing_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`object_id`),
  KEY `rel_obj_id` (`rel_object_id`) USING BTREE,
  KEY `end_time` (`end_time`),
  KEY `contact_end` (`contact_id`,`end_time`),
  KEY `contact_start` (`contact_id`,`start_time`),
  KEY `start_time` (`start_time`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>read_objects` (
  `rel_object_id` int(10) unsigned NOT NULL default '0',
  `contact_id` int(10) unsigned NOT NULL default '0',
  `is_read` int(1) NOT NULL default '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`rel_object_id`,`contact_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>event_invitations` (
  `event_id` int(10) unsigned NOT NULL default '0',
  `contact_id` int(10) unsigned NOT NULL default '0',
  `invitation_state` int(10) unsigned NOT NULL default '0',
  `synced` int(1) DEFAULT '0',
  `special_id` text <?php echo $default_collation ?>,
  PRIMARY KEY (`event_id`, `contact_id`),
  KEY `contact_id` (`contact_id`,`event_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>templates` (
  `object_id` int(10) unsigned NOT NULL auto_increment,
  `description` text <?php echo $default_collation ?>,
  `can_instance_from_mail` int(1) NOT NULL default '0',
  PRIMARY KEY  (`object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>template_objects` (
  `template_id` int(10) unsigned NOT NULL default '0',
  `object_id` int(10) unsigned NOT NULL default 0,
  `created_by_id` int(10) unsigned default NULL,
  `created_on` datetime default NULL,
  PRIMARY KEY  (`template_id`, `object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

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

CREATE TABLE  `<?php echo $table_prefix ?>billings` (
  `object_id` int(10) unsigned NOT NULL,
  `value` float NOT NULL default '0',
 PRIMARY KEY  (`object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>sharing_table` (
  `group_id` INTEGER UNSIGNED NOT NULL,
  `object_id` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (`group_id`, `object_id`),
  INDEX `object_id`(`object_id`)  
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>sharing_table_flags` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `permission_group_id` INTEGER UNSIGNED NOT NULL,
  `member_id` INTEGER UNSIGNED NOT NULL,
  `object_id` INTEGER UNSIGNED NOT NULL,
  `execution_date` DATETIME NOT NULL,
  `permission_string` TEXT <?php echo $default_collation ?> NOT NULL,
  `created_by_id` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
)
ENGINE = <?php echo $engine ?>;

CREATE TABLE `<?php echo $table_prefix ?>contact_passwords` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `contact_id` int(10) NOT NULL,
  `password` varchar(40) NOT NULL,
  `password_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>custom_properties` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `object_type_id` int(10) unsigned NOT NULL,
  `name` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `code` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `type` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `description` text <?php echo $default_collation ?> NOT NULL,
  `values` text <?php echo $default_collation ?> NOT NULL,
  `default_value` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `is_required` tinyint(1) NOT NULL,
  `is_multiple_values` tinyint(1) NOT NULL,
  `property_order` int(10) NOT NULL,
  `visible_by_default` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>custom_property_values` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `object_id` int(10) NOT NULL,
  `custom_property_id` int(10) NOT NULL,
  `value` text <?php echo $default_collation ?>,
  PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>queued_emails` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `to` text <?php echo $default_collation ?>,
  `cc` text <?php echo $default_collation ?>,
  `bcc` text <?php echo $default_collation ?>,
  `from` text <?php echo $default_collation ?>,
  `subject` text <?php echo $default_collation ?>,
  `body` text <?php echo $default_collation ?>,
  `attachments` text,
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>reports` (
  `object_id` int(10) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `report_object_type_id` int(10)unsigned NOT NULL, 
  `order_by` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `is_order_by_asc` tinyint(1) <?php echo $default_collation ?> NOT NULL,
  `ignore_context` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`object_id`),
  KEY `object_type` (`report_object_type_id`)
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

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>template_parameters` (
  `id` INT( 10 ) NOT NULL AUTO_INCREMENT,
  `template_id` INT( 10 ) NOT NULL,
  `name` VARCHAR( 255 ) <?php echo $default_collation ?> NOT NULL,
  `type` VARCHAR( 255 ) <?php echo $default_collation ?> NOT NULL,
  `default_value` text <?php echo $default_collation ?> NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>template_object_properties` (
`template_id` INT( 10 ) NOT NULL ,
`object_id` INT( 10 ) NOT NULL ,
`property` VARCHAR( 255 ) <?php echo $default_collation ?> NOT NULL ,
`value` TEXT NOT NULL ,
PRIMARY KEY ( `template_id` , `object_id` ,`property` )
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;


CREATE TABLE  `<?php echo $table_prefix ?>application_read_logs` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `taken_by_id` int(10) NOT NULL default '0',
  `rel_object_id` int(10) NOT NULL default '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default NULL,
  `action` enum('read','download') <?php echo $default_collation ?> default NULL,
  PRIMARY KEY  (`id`),
  KEY `created_on` (`created_on`),
  KEY `object_key` (`rel_object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>administration_logs` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `title` varchar(50) NOT NULL default '',
  `log_data` text NOT NULL,
  `category` enum('system','security') NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `created_on` (`created_on`),
  KEY `category` (`category`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>contact_config_categories` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `is_system` tinyint(1) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `category_order` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `order` (`category_order`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>contact_config_options` (
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
  KEY `category_id` USING BTREE (`category_name`,`is_system`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>contact_config_option_values` (
  `option_id` int(10) unsigned NOT NULL default '0',
  `contact_id` int(10) unsigned NOT NULL default '0',
  `value` text <?php echo $default_collation ?>,
  `member_id` INT( 10 ) UNSIGNED NULL DEFAULT '0',
  PRIMARY KEY  ( `option_id` , `contact_id` , `member_id` )
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>project_co_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `object_manager` varchar(45) <?php echo $default_collation ?> NOT NULL,
  `name` varchar(45) <?php echo $default_collation ?> NOT NULL,
  `created_by_id` int(10) unsigned NOT NULL,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned NOT NULL,
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `object_manager` (`object_manager`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>tab_panels` (
  `id` varchar(40) <?php echo $default_collation ?> NOT NULL,
  `title` varchar(128) <?php echo $default_collation ?> NOT NULL,
  `icon_cls` varchar(40) <?php echo $default_collation ?> NOT NULL,
  `refresh_on_context_change` tinyint(1) NOT NULL,
  `default_controller` varchar(45) <?php echo $default_collation ?> NOT NULL,
  `default_action` varchar(45) <?php echo $default_collation ?> NOT NULL,
  `initial_controller` varchar(45) <?php echo $default_collation ?> NOT NULL,
  `initial_action` varchar(45) <?php echo $default_collation ?> NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `type` enum('system','plugin') <?php echo $default_collation ?> NOT NULL,
  `ordering` int(10) NOT NULL,
  `plugin_id` int(10) unsigned NOT NULL default 0,
  `object_type_id` int(10) unsigned NOT NULL default 0,
  PRIMARY KEY (`id`),
  KEY `enabled` (`enabled`,`type`,`plugin_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>historic_values` (
  `object_id` int(10) unsigned NOT NULL,
  `name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `value` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`object_id`,`created_on`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>tab_panel_permissions` (
  `permission_group_id` INTEGER UNSIGNED NOT NULL,
  `tab_panel_id` varchar(40) <?php echo $default_collation ?> NOT NULL default '',
  PRIMARY KEY (`permission_group_id`, `tab_panel_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>project_task_dependencies` (
  `previous_task_id` int(10) unsigned NOT NULL,
  `task_id` int(10) unsigned NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`previous_task_id`,`task_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>widgets` (
  `name` varchar(64) NOT NULL,
  `title` varchar(255) NOT NULL,
  `plugin_id` int(10) unsigned NOT NULL DEFAULT 0,
  `path` varchar(512) NOT NULL,
  `default_options` text NOT NULL,
  `default_section` varchar(64) NOT NULL,
  `default_order` int(10) NOT NULL,
  `icon_cls` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`name`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;


CREATE TABLE `<?php echo $table_prefix ?>contact_widgets` (
  `widget_name` varchar(40) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `section` varchar(40) NOT NULL,
  `order` int(11) NOT NULL,
  `options` varchar(255) NOT NULL,
  PRIMARY KEY (`widget_name`,`contact_id`) USING BTREE
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;


CREATE TABLE `<?php echo $table_prefix ?>role_object_type_permissions` (
  `role_id` INTEGER UNSIGNED NOT NULL,
  `object_type_id` INTEGER UNSIGNED NOT NULL,
  `can_delete` BOOLEAN NOT NULL,
  `can_write` BOOLEAN NOT NULL,
  PRIMARY KEY (`role_id`, `object_type_id`)
) ENGINE = <?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>max_role_object_type_permissions` (
  `role_id` INTEGER UNSIGNED NOT NULL,
  `object_type_id` INTEGER UNSIGNED NOT NULL,
  `can_delete` BOOLEAN NOT NULL,
  `can_write` BOOLEAN NOT NULL,
  PRIMARY KEY (`role_id`, `object_type_id`)
) ENGINE = <?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>external_calendar_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int(10) unsigned NOT NULL,
  `auth_user` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `auth_pass` text COLLATE utf8_unicode_ci NOT NULL,
  `type` text COLLATE utf8_unicode_ci NOT NULL,
  `sync` TINYINT( 1 ) NULL DEFAULT '0',
  `related_to` VARCHAR( 255 ) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = <?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>external_calendars` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ext_cal_user_id` int(10) unsigned NOT NULL,
  `original_calendar_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `calendar_visibility` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `calendar_name` text COLLATE utf8_unicode_ci NOT NULL,
  `calendar_feng` TINYINT( 1 ) NOT NULL DEFAULT '0',
  `sync` TINYINT( 1 ) NOT NULL DEFAULT '0',
  `related_to` VARCHAR( 255 ) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = <?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>external_calendar_properties` (
  `external_calendar_id` int(10) unsigned NOT NULL,
  `key` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `value` text <?php echo $default_collation ?> NOT NULL, 
  PRIMARY KEY (`external_calendar_id`,`key`)
) ENGINE = <?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>template_tasks` (
  `template_id` int(10) unsigned DEFAULT NULL,
  `session_id` int(10) DEFAULT NULL,
  `object_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `parents_path` varchar(255) NOT NULL default '',
  `depth` int(2) unsigned NOT NULL default '0',
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

CREATE TABLE `<?php echo $table_prefix ?>template_milestones` (
  `template_id` int(10) unsigned DEFAULT NULL,
  `session_id` int(10) DEFAULT NULL,
  `object_id` int(10) unsigned NOT NULL,
  `description` text <?php echo $default_collation ?>,
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
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>contact_widget_options` (
  `widget_name` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `contact_id` int(11) NOT NULL,
  `member_type_id` int(11) NOT NULL DEFAULT 0,
  `option` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `config_handler_class` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `is_system` tinyint(1) unsigned default 0,
  PRIMARY KEY (`widget_name`,`contact_id`,`member_type_id`,`option`) USING BTREE
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>member_custom_properties` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `object_type_id` int(10) unsigned NOT NULL,
  `name` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `code` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `type` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `description` text <?php echo $default_collation ?> NOT NULL,
  `values` text <?php echo $default_collation ?> NOT NULL,
  `default_value` text <?php echo $default_collation ?> NOT NULL,
  `is_system` tinyint(1) NOT NULL,
  `is_required` tinyint(1) NOT NULL,
  `is_multiple_values` tinyint(1) NOT NULL,
  `property_order` int(10) NOT NULL,
  `visible_by_default` tinyint(1) NOT NULL,
  `is_special` tinyint(1) NOT NULL,
  `is_disabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>member_custom_property_values` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `member_id` int(10) NOT NULL,
  `custom_property_id` int(10) NOT NULL,
  `value` text <?php echo $default_collation ?> NOT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` USING HASH (`member_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>contact_member_cache` (
  `contact_id` int(10) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED NOT NULL,
  `parent_member_id` int(10) UNSIGNED NOT NULL default '0',
  `last_activity` DATETIME NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`contact_id` , `member_id`),
  KEY `by_contact` USING HASH (`contact_id`),
  KEY `by_parent` USING HASH (`parent_member_id`),
  KEY `last_activity` (`last_activity`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>template_instantiated_parameters` (
  `template_id` INTEGER UNSIGNED NOT NULL,
  `instantiation_id` INTEGER UNSIGNED NOT NULL,
  `parameter_name` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `value` TEXT NOT NULL,
  PRIMARY KEY (`template_id`, `instantiation_id`, `parameter_name`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>sent_notifications` (
 `id` int(10) NOT NULL AUTO_INCREMENT,
 `queued_email_id` int(10) NOT NULL DEFAULT 0,
 `sent_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `to` text <?php echo $default_collation ?>,
 `cc` text <?php echo $default_collation ?>,
 `bcc` text <?php echo $default_collation ?>,
 `from` text <?php echo $default_collation ?>,
 `subject` text <?php echo $default_collation ?>,
 `body` text <?php echo $default_collation ?>,
 `attachments` text <?php echo $default_collation ?>,
 `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;