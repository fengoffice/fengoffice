-- <?php echo $table_prefix ?> fo_
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci
-- <?php echo $engine ?> InnoDB

<?php
echo "\n";
include dirname(__FILE__)."/../../../install/installation/templates/sql/mysql_schema.php";
echo "\n";
include dirname(__FILE__)."/../../../install/installation/templates/sql/mysql_initial_data.php";
echo "\n";
?>


-- temp columns
ALTER TABLE `fo_members`
 ADD COLUMN `ws_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
 ADD INDEX `ws_id` (`ws_id`);

ALTER TABLE `fo_objects`
 ADD COLUMN `f1_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
 ADD INDEX `f1_id` (`f1_id`);


-- PLUGINS
INSERT INTO fo_plugins (`name`, `is_installed`, `is_activated`, `priority`, `activated_on`, `activated_by_id`) VALUES
 ('workspaces', 1, 1, -10, NOW(), 1),
 ('core_dimensions', 1, 1, 10, NOW(), 1),
 ('mail', 1, 1, 20, NOW(), 1);

INSERT INTO `fo_object_types` (`name`, `handler_class`, `table_name`, `type`, `icon`, `plugin_id`) VALUES
 ('mail', 'MailContents', 'mail_contents', 'content_object', 'mail', (SELECT id FROM fo_plugins WHERE name='mail')),
 ('company', 'Contacts', 'contacts', 'dimension_object', 'company', (SELECT id FROM fo_plugins WHERE name='core_dimensions')),
 ('person', 'Contacts', 'contacts', 'dimension_object', 'contact', (SELECT id FROM fo_plugins WHERE name='core_dimensions'));


-- TABS
UPDATE fo_tab_panels SET enabled = (SELECT value FROM og_config_options WHERE name='enable_notes_module') WHERE id = 'webpages-panel';
UPDATE fo_tab_panels SET enabled = (SELECT value FROM og_config_options WHERE name='enable_email_module') WHERE id = 'mails-panel';
UPDATE fo_tab_panels SET enabled = (SELECT value FROM og_config_options WHERE name='enable_contacts_module') WHERE id = 'contacts-panel';
UPDATE fo_tab_panels SET enabled = (SELECT value FROM og_config_options WHERE name='enable_calendar_module') WHERE id = 'calendar-panel';
UPDATE fo_tab_panels SET enabled = (SELECT value FROM og_config_options WHERE name='enable_weblinks_module') WHERE id = 'webpages-panel';
UPDATE fo_tab_panels SET enabled = (SELECT value FROM og_config_options WHERE name='enable_documents_module') WHERE id = 'documents-panel';
UPDATE fo_tab_panels SET enabled = (SELECT value FROM og_config_options WHERE name='enable_tasks_module') WHERE id = 'tasks-panel';
UPDATE fo_tab_panels SET enabled = (SELECT value FROM og_config_options WHERE name='enable_time_module') WHERE id = 'time-panel';
UPDATE fo_tab_panels SET enabled = (SELECT value FROM og_config_options WHERE name='enable_reporting_module') WHERE id = 'reporting-panel';


INSERT INTO `fo_tab_panels` (`id`,`ordering`,`title`,`icon_cls`,`refresh_on_context_change`,`default_controller`,`default_action`,`initial_controller`,`initial_action`,`type`,`object_type_id`, `enabled`) VALUES
 ('mails-panel', 4, 'email tab', 'ico-mail', 1, 'mail', 'init', '', '', 'system', (SELECT id FROM fo_object_types WHERE name='mail'), 1);

-- WS WIDGETS
INSERT INTO fo_widgets(name, title, plugin_id, default_section,default_order) VALUES
 ('ws_description', 'workspace description', (SELECT id from fo_plugins WHERE name = 'workspaces'), 'top', -100),
 ('workspaces', 'workspaces', (SELECT id from fo_plugins WHERE name = 'workspaces'), 'right', 1)
ON DUPLICATE KEY update name = name;

-- DIMENSIONS

INSERT INTO `fo_dimensions` (`code`, `name`, `is_root`, `is_manageable`, `allows_multiple_selection`, `defines_permissions`, `is_system`, `options`) VALUES
 ('workspaces', 'Workspaces', 1, 1, 0, 1, 1, '{"defaultAjax":{"controller":"dashboard", "action": "main_dashboard"}, "quickAdd":true,"showInPaths":true}'),
 ('tags', 'Tags', 1, 1, 0, 0, 1, '{"quickAdd":true,"showInPaths":true}'),
 ('feng_persons', 'People', 1, 0, 0, 1, 1,'{"useLangs":true,"defaultAjax":{"controller":"contact", "action": "init"},"quickAdd":{"formAction":"?c=contact&a=quick_add"}}');

UPDATE `fo_config_options` SET value='1,2' WHERE name='enabled_dimensions';

INSERT INTO `fo_dimension_object_type_hierarchies` (`dimension_id`, `parent_object_type_id`, `child_object_type_id`) VALUES
 ((SELECT id FROM fo_dimensions WHERE code='workspaces'), (SELECT id FROM fo_object_types WHERE name='workspace'), (SELECT id FROM fo_object_types WHERE name='workspace')),
 ((SELECT id FROM fo_dimensions WHERE code='feng_persons'), (SELECT id FROM fo_object_types WHERE name='company'), (SELECT id FROM fo_object_types WHERE name='person')),
 ((SELECT id FROM fo_dimensions WHERE code='feng_persons'), (SELECT id FROM fo_object_types WHERE name='person'), (SELECT id FROM fo_object_types WHERE name='workspace'));

INSERT INTO fo_dimension_object_types (dimension_id, object_type_id, is_root, options) VALUES
 ((SELECT id FROM fo_dimensions WHERE code = 'workspaces'), (SELECT id FROM fo_object_types WHERE name='workspace'), 1, '{"defaultAjax":{"controller":"dashboard", "action": "main_dashboard"}}'),
 ((SELECT id FROM fo_dimensions WHERE code = 'tags'), (SELECT id FROM fo_object_types WHERE name='tag'), 1, ''),
 ((SELECT id FROM fo_dimensions WHERE code = 'feng_persons'), (SELECT id FROM fo_object_types WHERE name = 'workspace'), 1, ''),
 ((SELECT id FROM fo_dimensions WHERE code = 'feng_persons'), (SELECT id FROM fo_object_types WHERE name = 'person'), 1, '{"defaultAjax":{"controller":"contact", "action": "card"}}'),
 ((SELECT id FROM fo_dimensions WHERE code = 'feng_persons'), (SELECT id FROM fo_object_types WHERE name = 'company'), 1, '{"defaultAjax":{"controller":"contact", "action": "company_card"}}');

INSERT INTO fo_dimension_object_type_contents (dimension_id, dimension_object_type_id, content_object_type_id, is_required, is_multiple)
 SELECT (SELECT id FROM fo_dimensions WHERE code = 'workspaces'), (SELECT id FROM fo_object_types WHERE name='workspace'), ot.id, 0, 1
 FROM fo_object_types ot WHERE ot.type IN ('content_object', 'comment', 'located');

INSERT INTO fo_dimension_object_type_contents (dimension_id, dimension_object_type_id, content_object_type_id, is_required, is_multiple)
 SELECT (SELECT id FROM fo_dimensions WHERE code = 'tags'), (SELECT id FROM fo_object_types WHERE name='tag'), ot.id, 0, 1
 FROM fo_object_types ot WHERE ot.type IN ('content_object', 'comment', 'located');

INSERT INTO fo_dimension_object_type_contents (dimension_id, dimension_object_type_id, content_object_type_id, is_required, is_multiple)
 SELECT (SELECT id FROM fo_dimensions WHERE code = 'feng_persons'), (SELECT id FROM fo_object_types WHERE name='person'), ot.id, 0, 1
 FROM fo_object_types ot WHERE ot.type IN ('content_object', 'comment', 'located');

INSERT INTO fo_dimension_object_type_contents (dimension_id, dimension_object_type_id, content_object_type_id, is_required, is_multiple)
 SELECT (SELECT id FROM fo_dimensions WHERE code = 'feng_persons'), (SELECT id FROM fo_object_types WHERE name='company'), ot.id, 0, 1
 FROM fo_object_types ot WHERE ot.type IN ('content_object', 'comment', 'located');


-- WORKSPACES

INSERT INTO `fo_members` (`dimension_id`, `object_type_id`, `parent_member_id`, `depth`, `name`, `object_id`, `ws_id`, `archived_on`, `archived_by_id`)
 SELECT 1, 1, 0, 1, `name`, 0, `id`, `completed_on`, `completed_by_id` FROM `og_projects` WHERE `p1` = `id`;

INSERT INTO `fo_members` (`dimension_id`, `object_type_id`, `parent_member_id`, `depth`, `name`, `object_id`, `ws_id`, `archived_on`, `archived_by_id`)
 SELECT 1, 1, (SELECT `id` FROM `fo_members` WHERE `ws_id` = `p1`), 2, `name`, 0, `id`, `completed_on`, `completed_by_id`
 FROM `og_projects` WHERE `p2` = `id` AND `p1` IN (SELECT `ws_id` FROM `fo_members` WHERE `dimension_id`= 1 AND `depth` = 1);

INSERT INTO `fo_members` (`dimension_id`, `object_type_id`, `parent_member_id`, `depth`, `name`, `object_id`, `ws_id`, `archived_on`, `archived_by_id`)
 SELECT 1, 1, (SELECT `id` FROM `fo_members` WHERE `ws_id` = `p2`), 3, `name`, 0, `id`, `completed_on`, `completed_by_id`
 FROM `og_projects` WHERE `p3` = `id` AND `p2` IN (SELECT `ws_id` FROM `fo_members` WHERE `dimension_id`= 1 AND `depth` = 2);

INSERT INTO `fo_members` (`dimension_id`, `object_type_id`, `parent_member_id`, `depth`, `name`, `object_id`, `ws_id`, `archived_on`, `archived_by_id`)
 SELECT 1, 1, (SELECT `id` FROM `fo_members` WHERE `ws_id` = `p3`), 4, `name`, 0, `id`, `completed_on`, `completed_by_id`
 FROM `og_projects` WHERE `p4` = `id` AND `p3` IN (SELECT `ws_id` FROM `fo_members` WHERE `dimension_id`= 1 AND `depth` = 3);

INSERT INTO `fo_members` (`dimension_id`, `object_type_id`, `parent_member_id`, `depth`, `name`, `object_id`, `ws_id`, `archived_on`, `archived_by_id`)
 SELECT 1, 1, (SELECT `id` FROM `fo_members` WHERE `ws_id` = `p4`), 5, `name`, 0, `id`, `completed_on`, `completed_by_id`
 FROM `og_projects` WHERE `p5` = `id` AND `p4` IN (SELECT `ws_id` FROM `fo_members` WHERE `dimension_id`= 1 AND `depth` = 4);

INSERT INTO `fo_members` (`dimension_id`, `object_type_id`, `parent_member_id`, `depth`, `name`, `object_id`, `ws_id`, `archived_on`, `archived_by_id`)
 SELECT 1, 1, (SELECT `id` FROM `fo_members` WHERE `ws_id` = `p5`), 6, `name`, 0, `id`, `completed_on`, `completed_by_id`
 FROM `og_projects` WHERE `p6` = `id` AND `p5` IN (SELECT `ws_id` FROM `fo_members` WHERE `dimension_id`= 1 AND `depth` = 5);

INSERT INTO `fo_members` (`dimension_id`, `object_type_id`, `parent_member_id`, `depth`, `name`, `object_id`, `ws_id`, `archived_on`, `archived_by_id`)
 SELECT 1, 1, (SELECT `id` FROM `fo_members` WHERE `ws_id` = `p6`), 7, `name`, 0, `id`, `completed_on`, `completed_by_id`
 FROM `og_projects` WHERE `p7` = `id` AND `p6` IN (SELECT `ws_id` FROM `fo_members` WHERE `dimension_id`= 1 AND `depth` = 6);

INSERT INTO `fo_members` (`dimension_id`, `object_type_id`, `parent_member_id`, `depth`, `name`, `object_id`, `ws_id`, `archived_on`, `archived_by_id`)
 SELECT 1, 1, (SELECT `id` FROM `fo_members` WHERE `ws_id` = `p7`), 8, `name`, 0, `id`, `completed_on`, `completed_by_id`
 FROM `og_projects` WHERE `p8` = `id` AND `p7` IN (SELECT `ws_id` FROM `fo_members` WHERE `dimension_id`= 1 AND `depth` = 7);

INSERT INTO `fo_members` (`dimension_id`, `object_type_id`, `parent_member_id`, `depth`, `name`, `object_id`, `ws_id`, `archived_on`, `archived_by_id`)
 SELECT 1, 1, (SELECT `id` FROM `fo_members` WHERE `ws_id` = `p8`), 9, `name`, 0, `id`, `completed_on`, `completed_by_id`
 FROM `og_projects` WHERE `p9` = `id` AND `p8` IN (SELECT `ws_id` FROM `fo_members` WHERE `dimension_id`= 1 AND `depth` = 8);

INSERT INTO `fo_members` (`dimension_id`, `object_type_id`, `parent_member_id`, `depth`, `name`, `object_id`, `ws_id`, `archived_on`, `archived_by_id`)
 SELECT 1, 1, (SELECT `id` FROM `fo_members` WHERE `ws_id` = `p9`), 10, `name`, 0, `id`, `completed_on`, `completed_by_id`
 FROM `og_projects` WHERE `p10` = `id` AND `p9` IN (SELECT `ws_id` FROM `fo_members` WHERE `dimension_id`= 1 AND `depth` = 9);


-- TAGS

INSERT INTO `fo_members` (`dimension_id`, `object_type_id`, `parent_member_id`, `depth`, `name`, `object_id`)
 SELECT DISTINCT 2, 2, 0, 1, `tag`, 0 FROM `og_tags`;


-- USERS

INSERT INTO `fo_objects` (`name`, `f1_id`, `object_type_id`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`)
 SELECT IF(`c`.`display_name`!='', `c`.`display_name`, `c`.`username`), `c`.`id`, (SELECT `id` FROM `fo_object_types` WHERE `name` = 'contact'),
  `c`.`created_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`created_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`updated_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`updated_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact'))
 FROM `og_users` `c`;

INSERT INTO `fo_contacts` (`object_id`, `first_name`, `is_company`, `timezone`, `company_id`, `user_type`, `is_active_user`, `token`, `salt`, `twister`, `display_name`, `username`, `picture_file`, `default_billing_id`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `u`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')), IF(`display_name`!='', `display_name`, `username`), 0, `u`.`timezone`, 
 	(SELECT `id` FROM `fo_objects` WHERE `f1_id` = `u`.`company_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='company')), (IF(`u`.`type`='admin', IF(`u`.`id`=(SELECT MIN(uu.id) FROM og_users uu WHERE uu.`type`='admin'), 1, 2), IF(`u`.`type`='guest', 12, 4))),
 	 1, `u`.`token`, `u`.`salt`, `u`.`twister`, `u`.`display_name`, `u`.`username`, `u`.`avatar_file`, `u`.`default_billing_id`
 FROM `og_users` `u`;

INSERT INTO fo_contact_passwords (contact_id, password, password_date)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `o`.`user_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')), o.password, o.password_date
 FROM og_user_passwords o;

INSERT INTO `fo_contact_emails` (`contact_id`, `email_type_id`, `is_main`, `email_address`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')), 2, 1, `c`.`email`
 FROM `og_users` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;


-- COMPANIES

INSERT INTO `fo_objects` (`name`, `f1_id`, `object_type_id`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`, `trashed_on`, `trashed_by_id`, `archived_on`, `archived_by_id`)
 SELECT `c`.`name`, `c`.`id`, (SELECT `id` FROM `fo_object_types` WHERE `name` = 'company'),
  `c`.`created_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`created_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`updated_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`updated_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`trashed_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`trashed_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`archived_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`archived_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact'))
 FROM `og_companies` `c`;

UPDATE fo_contacts c INNER JOIN og_users u ON u.id = (SELECT `f1_id` FROM `fo_objects` WHERE `id` = `c`.`object_id`) 
 SET c.company_id = (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `u`.`company_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='company'))
 WHERE c.is_company=0;

INSERT INTO `fo_contacts` (`object_id`, `first_name`, `is_company`, `timezone`, `picture_file`, `comments`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='company')), `name`, 1, `c`.`timezone`, `c`.`logo_file`, `c`.`notes`
 FROM `og_companies` `c`;

INSERT INTO `fo_contact_emails` (`contact_id`, `email_type_id`, `is_main`, `email_address`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='company')), 3, 1, `c`.`email`
 FROM `og_companies` `c`;

INSERT INTO `fo_contact_telephones` (`contact_id`, `telephone_type_id`, `is_main`, `number`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='company')), 2, 1, `c`.`phone_number`
 FROM `og_companies` `c`;

INSERT INTO `fo_contact_telephones` (`contact_id`, `telephone_type_id`, `is_main`, `number`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='company')), 8, 0, `c`.`fax_number`
 FROM `og_companies` `c`;

INSERT INTO `fo_contact_web_pages` (`contact_id`, `web_type_id`, `url`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='company')), 2, `c`.`homepage`
 FROM `og_companies` `c`;

INSERT INTO `fo_contact_addresses` (`contact_id`, `address_type_id`, `street`, `city`, `state`, `country`, `zip_code`, `is_main`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='company')), 2, `c`.`address`, `c`.`city`, `c`.`state`, `c`.`country`, `c`.`zipcode`, 1
 FROM `og_companies` `c`;





-- USER PERMISSIONS

INSERT INTO `fo_permission_groups` (`name`, `contact_id`, `type`)
 SELECT CONCAT('User ', `object_id`, ' Personal'), `object_id`, 'permission_groups' FROM `fo_contacts` WHERE `user_type` != 0
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;

UPDATE `fo_contacts` SET `permission_group_id` = (SELECT `id` FROM `fo_permission_groups` WHERE `contact_id` = `object_id`);

INSERT INTO `fo_contact_permission_groups` (`contact_id`, `permission_group_id`)
 SELECT `object_id`, `permission_group_id`
 FROM `fo_contacts`
 WHERE `user_type` != 0
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;

INSERT INTO fo_system_permissions (permission_group_id, can_manage_security, can_manage_configuration, can_manage_templates, can_manage_time, can_add_mail_accounts, can_manage_dimension_members, can_manage_dimensions, can_manage_tasks, can_task_assignee, can_manage_billing, can_view_billing)
 SELECT c.permission_group_id, pg.can_manage_security, pg.can_manage_configuration, pg.can_manage_templates, pg.can_manage_time, pg.can_add_mail_accounts, pg.can_manage_dimension_members, pg.can_manage_dimensions, pg.can_manage_tasks, pg.can_task_assignee, pg.can_manage_billing, pg.can_view_billing
 FROM fo_contacts c INNER JOIN fo_system_permissions pg ON pg.permission_group_id = c.user_type
 WHERE c.user_type > 0;

INSERT INTO `fo_tab_panel_permissions` (`permission_group_id`, `tab_panel_id`)
 SELECT `c`.`permission_group_id`, `tp`.`id`
 FROM `og_users` `u` INNER JOIN `fo_objects` `o` ON `u`.`id` = `o`.`f1_id` INNER JOIN `fo_contacts` `c` ON `c`.`object_id` = `o`.`id` JOIN `fo_tab_panels` `tp`
 WHERE `c`.`user_type` != 0;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `c`.`permission_group_id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_messages`=1), (`pu`.`can_write_messages`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_objects` `o` ON `pu`.`user_id` = `o`.`f1_id` INNER JOIN `fo_contacts` `c` ON `c`.`object_id` = `o`.`id` JOIN `fo_object_types` `ot`
 WHERE `c`.`user_type` != 0 AND `ot`.`name` = 'message' AND `pu`.`can_read_messages`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `c`.`permission_group_id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_tasks`=1), (`pu`.`can_write_tasks`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_objects` `o` ON `pu`.`user_id` = `o`.`f1_id` INNER JOIN `fo_contacts` `c` ON `c`.`object_id` = `o`.`id` JOIN `fo_object_types` `ot`
 WHERE `c`.`user_type` != 0 AND `ot`.`name` = 'task' AND `pu`.`can_read_tasks`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `c`.`permission_group_id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_milestones`=1), (`pu`.`can_write_milestones`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_objects` `o` ON `pu`.`user_id` = `o`.`f1_id` INNER JOIN `fo_contacts` `c` ON `c`.`object_id` = `o`.`id` JOIN `fo_object_types` `ot`
 WHERE `c`.`user_type` != 0 AND `ot`.`name` = 'milestone' AND `pu`.`can_read_milestones`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `c`.`permission_group_id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_files`=1), (`pu`.`can_write_files`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_objects` `o` ON `pu`.`user_id` = `o`.`f1_id` INNER JOIN `fo_contacts` `c` ON `c`.`object_id` = `o`.`id` JOIN `fo_object_types` `ot`
 WHERE `c`.`user_type` != 0 AND `ot`.`name` = 'file' AND `pu`.`can_read_files`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `c`.`permission_group_id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_events`=1), (`pu`.`can_write_events`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_objects` `o` ON `pu`.`user_id` = `o`.`f1_id` INNER JOIN `fo_contacts` `c` ON `c`.`object_id` = `o`.`id` JOIN `fo_object_types` `ot`
 WHERE `c`.`user_type` != 0 AND `ot`.`name` = 'event' AND `pu`.`can_read_events`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `c`.`permission_group_id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_weblinks`=1), (`pu`.`can_write_weblinks`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_objects` `o` ON `pu`.`user_id` = `o`.`f1_id` INNER JOIN `fo_contacts` `c` ON `c`.`object_id` = `o`.`id` JOIN `fo_object_types` `ot`
 WHERE `c`.`user_type` != 0 AND `ot`.`name` = 'weblink' AND `pu`.`can_read_weblinks`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `c`.`permission_group_id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_mails`=1), (`pu`.`can_write_mails`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_objects` `o` ON `pu`.`user_id` = `o`.`f1_id` INNER JOIN `fo_contacts` `c` ON `c`.`object_id` = `o`.`id` JOIN `fo_object_types` `ot`
 WHERE `c`.`user_type` != 0 AND `ot`.`name` = 'mail' AND `pu`.`can_read_mails`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `c`.`permission_group_id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_contacts`=1), (`pu`.`can_write_contacts`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_objects` `o` ON `pu`.`user_id` = `o`.`f1_id` INNER JOIN `fo_contacts` `c` ON `c`.`object_id` = `o`.`id` JOIN `fo_object_types` `ot`
 WHERE `c`.`user_type` != 0 AND `ot`.`name` = 'contact' AND `pu`.`can_read_contacts`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `c`.`permission_group_id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_comments`=1), (`pu`.`can_write_comments`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_objects` `o` ON `pu`.`user_id` = `o`.`f1_id` INNER JOIN `fo_contacts` `c` ON `c`.`object_id` = `o`.`id` JOIN `fo_object_types` `ot`
 WHERE `c`.`user_type` != 0 AND `ot`.`name` = 'comment' AND `pu`.`can_read_comments`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

-- give permissions over timeslots, reports and templates in all workspaces where the user can manage tasks.
INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `c`.`permission_group_id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_tasks`=1), (`pu`.`can_write_tasks`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_objects` `o` ON `pu`.`user_id` = `o`.`f1_id` INNER JOIN `fo_contacts` `c` ON `c`.`object_id` = `o`.`id` JOIN `fo_object_types` `ot`
 WHERE `c`.`user_type` != 0 AND `ot`.`name` IN ('template','report','timeslot') AND `pu`.`can_read_tasks`=1
ON DUPLICATE KEY UPDATE member_id=member_id;


-- GROUP PERMISSIONS

INSERT INTO `fo_permission_groups` (`name`, `contact_id`, `type`)
 SELECT `name`, `id`, 'user_groups' FROM `og_groups`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;

INSERT INTO `fo_contact_permission_groups` (`contact_id`, `permission_group_id`)
 SELECT `c`.`object_id`, (SELECT `id` FROM `fo_permission_groups` WHERE `contact_id` = `gu`.`group_id`)
 FROM `og_group_users` `gu` INNER JOIN `fo_objects` `o` ON `o`.`f1_id` = `gu`.`user_id` INNER JOIN `fo_contacts` `c` on o.id=c.object_id
 WHERE `c`.`user_type` != 0 AND (SELECT `id` FROM `fo_permission_groups` WHERE `contact_id` = `gu`.`group_id`)
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;

INSERT INTO `fo_system_permissions` (`permission_group_id`, `can_manage_security`, `can_manage_configuration`, `can_manage_templates`, `can_manage_time`, `can_add_mail_accounts`, `can_manage_dimension_members`, `can_task_assignee`)
 SELECT (SELECT `p`.`id` FROM `fo_permission_groups` `p` WHERE `p`.`contact_id`=`u`.`id`),
  `u`.`can_manage_security`, `u`.`can_manage_configuration`, `u`.`can_manage_templates`, `u`.`can_manage_time`, `u`.`can_add_mail_accounts`, `u`.`can_manage_workspaces`, 1
 FROM `og_groups` `u`;

INSERT INTO `fo_tab_panel_permissions` (`permission_group_id`, `tab_panel_id`)
 SELECT (SELECT `p`.`id` FROM `fo_permission_groups` `p` WHERE `p`.`contact_id`=`u`.`id`), `tp`.`id`
 FROM `og_groups` `u` JOIN `fo_tab_panels` `tp`;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `pg`.`id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_messages`=1), (`pu`.`can_write_messages`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_permission_groups` `pg` ON `pu`.`user_id` = `pg`.`contact_id` JOIN `fo_object_types` `ot`
 WHERE `pu`.`user_id` >= 10000000 AND `ot`.`name` = 'message' AND `pu`.`can_read_messages`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `pg`.`id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_tasks`=1), (`pu`.`can_write_tasks`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_permission_groups` `pg` ON `pu`.`user_id` = `pg`.`contact_id` JOIN `fo_object_types` `ot`
 WHERE `pu`.`user_id` >= 10000000 AND `ot`.`name` = 'task' AND `pu`.`can_read_tasks`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `pg`.`id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_milestones`=1), (`pu`.`can_write_milestones`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_permission_groups` `pg` ON `pu`.`user_id` = `pg`.`contact_id` JOIN `fo_object_types` `ot`
 WHERE `pu`.`user_id` >= 10000000 AND `ot`.`name` = 'milestone' AND `pu`.`can_read_milestones`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `pg`.`id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_files`=1), (`pu`.`can_write_files`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_permission_groups` `pg` ON `pu`.`user_id` = `pg`.`contact_id` JOIN `fo_object_types` `ot`
 WHERE `pu`.`user_id` >= 10000000 AND `ot`.`name` = 'file' AND `pu`.`can_read_files`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `pg`.`id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_events`=1), (`pu`.`can_write_events`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_permission_groups` `pg` ON `pu`.`user_id` = `pg`.`contact_id` JOIN `fo_object_types` `ot`
 WHERE `pu`.`user_id` >= 10000000 AND `ot`.`name` = 'event' AND `pu`.`can_read_events`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `pg`.`id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_weblinks`=1), (`pu`.`can_write_weblinks`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_permission_groups` `pg` ON `pu`.`user_id` = `pg`.`contact_id` JOIN `fo_object_types` `ot`
 WHERE `pu`.`user_id` >= 10000000 AND `ot`.`name` = 'weblink' AND `pu`.`can_read_weblinks`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `pg`.`id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_mails`=1), (`pu`.`can_write_mails`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_permission_groups` `pg` ON `pu`.`user_id` = `pg`.`contact_id` JOIN `fo_object_types` `ot`
 WHERE `pu`.`user_id` >= 10000000 AND `ot`.`name` = 'mail' AND `pu`.`can_read_mails`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `pg`.`id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_contacts`=1), (`pu`.`can_write_contacts`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_permission_groups` `pg` ON `pu`.`user_id` = `pg`.`contact_id` JOIN `fo_object_types` `ot`
 WHERE `pu`.`user_id` >= 10000000 AND `ot`.`name` = 'contact' AND `pu`.`can_read_contacts`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `pg`.`id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_comments`=1), (`pu`.`can_write_comments`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_permission_groups` `pg` ON `pu`.`user_id` = `pg`.`contact_id` JOIN `fo_object_types` `ot`
 WHERE `pu`.`user_id` >= 10000000 AND `ot`.`name` = 'comment' AND `pu`.`can_read_comments`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

-- give permissions over timeslots, reports and templates (same permissions as tasks)
INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_delete`, `can_write`)
 SELECT `pg`.`id`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `pu`.`project_id` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')), `ot`.`id`, (`pu`.`can_write_tasks`=1), (`pu`.`can_write_tasks`=1)
 FROM `og_project_users` `pu` INNER JOIN `fo_permission_groups` `pg` ON `pu`.`user_id` = `pg`.`contact_id` JOIN `fo_object_types` `ot`
 WHERE `pu`.`user_id` >= 10000000 AND `ot`.`name` IN ('template','report','timeslot') AND `pu`.`can_read_tasks`=1
ON DUPLICATE KEY UPDATE member_id=member_id;

UPDATE `fo_permission_groups` SET `contact_id` = 0 WHERE `contact_id` >= 10000000;


INSERT INTO `fo_contact_dimension_permissions` (`permission_group_id`, `dimension_id`, `permission_type`)
 SELECT pg.id, d.id, 'check' FROM fo_permission_groups pg JOIN fo_dimensions d WHERE pg.id > 13 AND d.defines_permissions
ON DUPLICATE KEY UPDATE permission_type=permission_type;



-- WORKSPACE DIMENSION OBJECTS

INSERT INTO `fo_objects` (`name`, `f1_id`, `object_type_id`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`)
 SELECT `c`.`name`, `c`.`id`, (SELECT `id` FROM `fo_object_types` WHERE `name` = 'workspace'),
  `c`.`created_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`created_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact') limit 1),
  `c`.`updated_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`updated_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact') limit 1)
 FROM `og_projects` `c`;


INSERT INTO `fo_workspaces` (`object_id`, `description`, `show_description_in_overview`, `color`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='workspace')),
  `c`.`description`, `c`.`show_description_in_overview`, `c`.`color`
 FROM `og_projects` `c`;


UPDATE `fo_members` `m` SET
 `m`.`object_id` = (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `m`.`ws_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='workspace'))
WHERE `m`.`object_type_id` = (SELECT `id` FROM `fo_object_types` WHERE `name` = 'workspace');

UPDATE `fo_objects` SET `f1_id` = 0 WHERE `object_type_id` = (SELECT `id` FROM `fo_object_types` WHERE `name` = 'workspace');

UPDATE fo_members m SET m.color=(SELECT w.color FROM fo_workspaces w WHERE w.object_id=m.object_id);

-- CONTENT OBJECTS

-- messages

INSERT INTO `fo_objects` (`name`, `f1_id`, `object_type_id`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`, `trashed_on`, `trashed_by_id`, `archived_on`, `archived_by_id`)
 SELECT `c`.`title`, `c`.`id`, (SELECT `id` FROM `fo_object_types` WHERE `name` = 'message'),
  `c`.`created_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`created_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`updated_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`updated_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`trashed_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`trashed_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`archived_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`archived_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact'))
 FROM `og_project_messages` `c`;

INSERT INTO `fo_project_messages` (`object_id`, `text`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='message')), `c`.`text`
 FROM `og_project_messages` `c`;


-- milestones

INSERT INTO `fo_objects` (`name`, `f1_id`, `object_type_id`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`, `trashed_on`, `trashed_by_id`, `archived_on`, `archived_by_id`)
 SELECT c.`name`, c.`id`, (SELECT `id` FROM `fo_object_types` WHERE `name` = 'milestone'),
  `c`.`created_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`created_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`updated_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`updated_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`trashed_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`trashed_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`archived_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`archived_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact'))
 FROM `og_project_milestones` c;

INSERT INTO `fo_project_milestones` (`object_id`, `description`, `due_date`, `is_urgent`, `completed_on`, `completed_by_id`, `is_template`, `from_template_id`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='milestone')), `c`.`description`, `c`.`due_date`, `c`.`is_urgent`, `c`.`completed_on`, `c`.`completed_by_id`, `c`.`is_template`, `c`.`from_template_id`
 FROM `og_project_milestones` `c`;


-- tasks

INSERT INTO `fo_objects` (`name`, `f1_id`, `object_type_id`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`, `trashed_on`, `trashed_by_id`, `archived_on`, `archived_by_id`)
 SELECT c.`title`, c.`id`, (SELECT `id` FROM `fo_object_types` WHERE `name` = 'task'),
  `c`.`created_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`created_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`updated_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`updated_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`trashed_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`trashed_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`archived_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`archived_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact'))
 FROM `og_project_tasks` c;

INSERT INTO `fo_project_tasks` (`object_id`, `text`, `parent_id`, `due_date`, `start_date`, `assigned_on`, `assigned_by_id`, `time_estimate`, `completed_on`, `completed_by_id`, `started_on`, `started_by_id`, `priority`, `state`, `order`, `milestone_id`, `is_template`, `from_template_id`, `repeat_end`, `repeat_forever`, `repeat_num`, `repeat_d`, `repeat_m`, `repeat_y`, `repeat_by`, `object_subtype`, `percent_completed`, `assigned_to_contact_id`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='task')), `c`.`text`, `c`.`parent_id`, `c`.`due_date`, `c`.`start_date`, `c`.`assigned_on`, 
 IF(`c`.`assigned_by_id` > 0, (SELECT o.id FROM fo_objects o WHERE o.f1_id = c.assigned_by_id AND o.object_type_id = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')), 0),
 `c`.`time_estimate`, `c`.`completed_on`,
 IF(`c`.`completed_by_id` > 0, (SELECT o.id FROM fo_objects o WHERE o.f1_id = c.completed_by_id AND o.object_type_id = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')), 0), 
 `c`.`started_on`, 
 IF(`c`.`started_by_id` > 0, (SELECT o.id FROM fo_objects o WHERE o.f1_id = c.started_by_id AND o.object_type_id = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')), 0),
 `c`.`priority`, `c`.`state`, `c`.`order`,
 (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`milestone_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='milestone')),
 `c`.`is_template`, `c`.`from_template_id`, `c`.`repeat_end`, `c`.`repeat_forever`, `c`.`repeat_num`, `c`.`repeat_d`, `c`.`repeat_m`, `c`.`repeat_y`, `c`.`repeat_by`, `c`.`object_subtype`, 0,
 IF (`c`.`assigned_to_user_id`> 0,
   (SELECT o.id FROM fo_objects o WHERE o.f1_id = c.assigned_to_user_id AND o.object_type_id = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
   (SELECT o.id FROM fo_objects o WHERE o.f1_id = c.assigned_to_company_id AND o.object_type_id = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='company'))
 )
 FROM `og_project_tasks` `c`;

UPDATE fo_project_tasks SET parent_id = (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `parent_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='task')) WHERE parent_id > 0;

-- weblinks

INSERT INTO `fo_objects` (`name`, `f1_id`, `object_type_id`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`, `trashed_on`, `trashed_by_id`, `archived_on`, `archived_by_id`)
 SELECT c.`title`, c.`id`, (SELECT `id` FROM `fo_object_types` WHERE `name` = 'weblink'),
  `c`.`created_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`created_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`updated_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`updated_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`trashed_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`trashed_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`archived_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`archived_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact'))
 FROM `og_project_webpages` c;

INSERT INTO `fo_project_webpages` (`object_id`, `url`, `description`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='weblink')), `c`.`url`, `c`.`description`
 FROM `og_project_webpages` `c`;


-- files

INSERT INTO `fo_objects` (`name`, `f1_id`, `object_type_id`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`, `trashed_on`, `trashed_by_id`, `archived_on`, `archived_by_id`)
 SELECT c.`filename`, c.`id`, (SELECT `id` FROM `fo_object_types` WHERE `name` = 'file'),
  `c`.`created_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`created_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`updated_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`updated_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`trashed_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`trashed_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`archived_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`archived_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact'))
 FROM `og_project_files` c;

INSERT INTO `fo_project_files` (`object_id`, `url`, `description`, `is_visible`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='file')), `c`.`url`, `c`.`description`, `c`.`is_visible`
 FROM `og_project_files` `c`;
 
 
 -- file revisions

INSERT INTO `fo_objects` (`name`, `f1_id`, `object_type_id`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`, `trashed_on`, `trashed_by_id`)
 SELECT '', c.`id`, (SELECT `id` FROM `fo_object_types` WHERE `name` = 'file revision'),
  `c`.`created_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`created_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`updated_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`updated_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`trashed_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`trashed_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact'))
 FROM `og_project_file_revisions` c;

INSERT INTO `fo_project_file_revisions` (`object_id`, `file_id`, `file_type_id`, `repository_id`, `thumb_filename`, `revision_number`, `comment`, `type_string`, `filesize`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='file revision')), 
 (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`file_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='file')), 
 `c`.`file_type_id`, `c`.`repository_id`, `c`.`thumb_filename`, `c`.`revision_number`, `c`.`comment`, `c`.`type_string`, `c`.`filesize`
 FROM `og_project_file_revisions` `c`;

UPDATE fo_project_file_revisions r SET r.file_type_id = (SELECT f.id FROM fo_file_types f INNER JOIN og_file_types o ON f.extension=o.extension WHERE o.id=r.file_type_id) WHERE r.file_type_id>0;

-- events

INSERT INTO `fo_objects` (`name`, `f1_id`, `object_type_id`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`, `trashed_on`, `trashed_by_id`, `archived_on`, `archived_by_id`)
 SELECT c.`subject`, c.`id`, (SELECT `id` FROM `fo_object_types` WHERE `name` = 'event'),
  `c`.`created_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`created_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`updated_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`updated_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`trashed_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`trashed_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`archived_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`archived_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact'))
 FROM `og_project_events` c;

INSERT INTO `fo_project_events` (`object_id`, `start`, `duration`, `description`, `private`, `repeat_end`, `repeat_forever`, `repeat_num`, `repeat_d`, `repeat_m`, `repeat_y`, `repeat_h`, `repeat_dow`, `repeat_wnum`, `repeat_mjump`, `type_id`, `special_id`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='event')), `c`.`start`, `c`.`duration`, `c`.`description`, `c`.`private`, `c`.`repeat_end`, `c`.`repeat_forever`, `c`.`repeat_num`, `c`.`repeat_d`, `c`.`repeat_m`, `c`.`repeat_y`, `c`.`repeat_h`, `c`.`repeat_dow`, `c`.`repeat_wnum`, `c`.`repeat_mjump`, `c`.`type_id`, `c`.`special_id`
 FROM `og_project_events` `c`;

INSERT INTO `fo_event_invitations` (`event_id`, `contact_id`, `invitation_state`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`event_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='event')),
  (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`user_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')), `c`.`invitation_state` 
 FROM `og_event_invitations` `c`;

 
 -- mails

CREATE TABLE IF NOT EXISTS `fo_mail_contents` (
  `object_id` int(10) unsigned NOT NULL,
  `account_id` int(10) unsigned NOT NULL default '0',
  `uid` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `from` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `from_name` VARCHAR( 250 ) NULL,
  `sent_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `received_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `has_attachments` int(1) NOT NULL default '0',
  `size` int(10) NOT NULL default '0',
  `state` INT( 1 ) NOT NULL DEFAULT '0' COMMENT '0:nothing, 1:sent, 2:draft',
  `is_deleted` int(1) NOT NULL default '0',
  `is_shared` INT(1) NOT NULL default '0',
  `imap_folder_name` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `account_email` varchar(100) collate utf8_unicode_ci default '',
  `content_file_id` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  `message_id` varchar(255) collate utf8_unicode_ci NOT NULL COMMENT 'Message-Id header',
  `in_reply_to_id` varchar(255) collate utf8_unicode_ci NOT NULL COMMENT 'Message-Id header of the previous email in the conversation',
  `conversation_id` int(10) unsigned NOT NULL default '0',
  `sync` int(1) NOT NULL default '0',
  PRIMARY KEY  (`object_id`),
  KEY `account_id` (`account_id`),
  KEY `sent_date` (`sent_date`),
  KEY `received_date` (`received_date`),
  KEY `uid` (`uid`),
  KEY `conversation_id` (`conversation_id`),
  KEY `message_id` (`message_id`),
  KEY `state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `fo_mail_datas` (
  `id` int(10) unsigned NOT NULL,
  `to` text collate utf8_unicode_ci NOT NULL,
  `cc` text collate utf8_unicode_ci NOT NULL,
  `bcc` text collate utf8_unicode_ci NOT NULL,
  `subject` text collate utf8_unicode_ci,
  `content` text collate utf8_unicode_ci,
  `body_plain` longtext collate utf8_unicode_ci,
  `body_html` longtext collate utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `fo_mail_accounts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `contact_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  `email` varchar(100) collate utf8_unicode_ci default '',
  `email_addr` VARCHAR( 100 ) collate utf8_unicode_ci NOT NULL default '',
  `password` varchar(40) collate utf8_unicode_ci default '',
  `server` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `is_imap` int(1) NOT NULL default '0',
  `incoming_ssl` int(1) NOT NULL default '0',
  `incoming_ssl_port` int default '995',
  `smtp_server` VARCHAR(100) collate utf8_unicode_ci NOT NULL default '',
  `smtp_use_auth` INTEGER UNSIGNED NOT NULL default 0,
  `smtp_username` VARCHAR(100) collate utf8_unicode_ci,
  `smtp_password` VARCHAR(100) collate utf8_unicode_ci,
  `smtp_port` INTEGER UNSIGNED NOT NULL default 25,
  `del_from_server` INTEGER NOT NULL default 0,
  `outgoing_transport_type` VARCHAR(5) collate utf8_unicode_ci NOT NULL default '',
  `last_checked` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_default` BOOLEAN NOT NULL default '0',
  `signature` text collate utf8_unicode_ci NOT NULL,
  `sender_name` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `last_error_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_error_msg` varchar(255) collate utf8_unicode_ci NOT NULL,
  `sync_addr` varchar(100) collate utf8_unicode_ci NOT NULL,
  `sync_pass` varchar(40) collate utf8_unicode_ci NOT NULL,
  `sync_server` varchar(100) collate utf8_unicode_ci NOT NULL,
  `sync_ssl` tinyint(1) NOT NULL default '0',
  `sync_ssl_port` int(11) NOT NULL default '993',
  `sync_folder` varchar(100) collate utf8_unicode_ci NOT NULL,
  `member_id` int(11) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  INDEX `contact_id` (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `fo_mail_account_imap_folder` (
  `account_id` int(10) unsigned NOT NULL default '0',
  `folder_name` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `check_folder` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`account_id`,`folder_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `fo_mail_account_contacts` (
 `id` INT(10) NOT NULL AUTO_INCREMENT,
 `account_id` INT(10) NOT NULL,
 `contact_id` INT(10) NOT NULL,
 `can_edit` BOOLEAN NOT NULL default '0',
 `is_default` BOOLEAN NOT NULL default '0',
 `signature` text collate utf8_unicode_ci NOT NULL,
 `sender_name` varchar(100) collate utf8_unicode_ci NOT NULL default '',
 `last_error_state` int(1) unsigned NOT NULL default '0' COMMENT '0:no error,1:err unread, 2:err read',
 PRIMARY KEY (`id`),
 UNIQUE KEY `uk_contactacc` (`account_id`, `contact_id`),
 KEY `ix_account` (`account_id`),
 KEY `ix_contact` (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `fo_mail_conversations` (
 `id` INT(10) NOT NULL AUTO_INCREMENT,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `fo_mail_spam_filters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(10) unsigned NOT NULL,
  `text_type` enum('email_address','subject') COLLATE utf8_unicode_ci NOT NULL,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  `spam_state` enum('no spam','spam') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO `fo_mail_accounts` (`id`, `contact_id`, `name`, `email`, `email_addr`, `password`, `server`, `is_imap`, `incoming_ssl`, `incoming_ssl_port`, `smtp_server`, `smtp_use_auth`, `smtp_username`, `smtp_password`, `smtp_port`, `del_from_server`, `outgoing_transport_type`, `last_checked`, `is_default`, `signature`, `sender_name`, `last_error_date`, `last_error_msg`, `sync_addr`, `sync_pass`, `sync_server`, `sync_ssl`, `sync_ssl_port`, `sync_folder`, `member_id`)
SELECT `c`.`id`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`user_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')), `c`.`name`, `c`.`email`, `c`.`email_addr`, `c`.`password`, `c`.`server`, `c`.`is_imap`, `c`.`incoming_ssl`, `c`.`incoming_ssl_port`, `c`.`smtp_server`, `c`.`smtp_use_auth`, `c`.`smtp_username`, `c`.`smtp_password`, `c`.`smtp_port`, `c`.`del_from_server`, `c`.`outgoing_transport_type`, `c`.`last_checked`, `c`.`is_default`, `c`.`signature`, `c`.`sender_name`, `c`.`last_error_date`, `c`.`last_error_msg`, `c`.`sync_addr`, `c`.`sync_pass`, `c`.`sync_server`, `c`.`sync_ssl`, `c`.`sync_ssl_port`, `c`.`sync_folder`, (SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`ws_id` = `c`.`workspace` AND `m`.`dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces'))
FROM `og_mail_accounts` `c`;

INSERT INTO `fo_mail_account_contacts` (`account_id`, `contact_id`, `can_edit`, `is_default`, `signature`, `sender_name`, `last_error_state`)
SELECT `c`.`account_id`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`user_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')), `c`.`can_edit`, `c`.`is_default`, `c`.`signature`, `c`.`sender_name`, `c`.`last_error_state`
FROM `og_mail_account_users` `c`;

INSERT INTO `fo_mail_account_imap_folder` (`account_id`, `folder_name`, `check_folder`)
SELECT `c`.`account_id`, `c`.`folder_name`, `c`.`check_folder`
FROM `og_mail_account_imap_folder` `c`;

INSERT INTO `fo_mail_conversations` (`id`)
SELECT `c`.`id` FROM `og_mail_conversations` `c`;

INSERT INTO `fo_objects` (`name`, `f1_id`, `object_type_id`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`, `trashed_on`, `trashed_by_id`, `archived_on`, `archived_by_id`)
 SELECT c.`subject`, c.`id`, (SELECT `id` FROM `fo_object_types` WHERE `name` = 'mail'),
  `c`.`created_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`created_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`received_date`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`created_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`trashed_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`trashed_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`archived_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`archived_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact'))
 FROM `og_mail_contents` c;

INSERT INTO `fo_mail_contents` (`object_id`, `account_id`, `uid`, `from`, `from_name`, `sent_date`, `received_date`, `has_attachments`, `size`, `state`, `is_deleted`, `is_shared`, `imap_folder_name`, `account_email`, `content_file_id`, `message_id`, `in_reply_to_id`, `conversation_id`, `sync`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='mail')), `c`.`account_id`, `c`.`uid`, `c`.`from`, `c`.`from_name`, `c`.`sent_date`, `c`.`received_date`, `c`.`has_attachments`, `c`.`size`, `c`.`state`, `c`.`is_deleted`, `c`.`is_shared`, `c`.`imap_folder_name`, `c`.`account_email`, `c`.`content_file_id`, `c`.`message_id`, `c`.`in_reply_to_id`, `c`.`conversation_id`, `c`.`sync`
 FROM `og_mail_contents` `c`;

INSERT INTO `fo_mail_datas` (`id`, `to`, `cc`, `bcc`, `subject`, `content`, `body_plain`, `body_html`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='mail')), `c`.`to`, `c`.`cc`, `c`.`bcc`, `c`.`subject`, `c`.`content`, `c`.`body_plain`, `c`.`body_html`
 FROM `og_mail_datas` `c`
ON DUPLICATE KEY UPDATE fo_mail_datas.id=fo_mail_datas.id;
DELETE FROM `fo_mail_datas` WHERE `id`=0;



-- templates

INSERT INTO `fo_objects` (`name`, `f1_id`, `object_type_id`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`)
 SELECT c.`name`, c.`id`, (SELECT `id` FROM `fo_object_types` WHERE `name` = 'template'),
  `c`.`created_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`created_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`updated_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`updated_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact'))
 FROM `og_templates` c;

INSERT INTO `fo_templates` (`object_id`, `description`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='template')), `c`.`description`
 FROM `og_templates` `c`;

INSERT INTO `fo_template_parameters` (`template_id`, `name`, `type`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`template_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='template')), `c`.`name`, `c`.`type`
 FROM `og_template_parameters` `c`;

INSERT INTO `fo_template_objects` (`template_id`, `object_id`, `created_by_id`, `created_on`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`template_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='template')),
 (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`handler_class`=`c`.`object_manager`)),
 `c`.`created_by_id`, `c`.`created_on`
 FROM `og_template_objects` `c`;

INSERT INTO `fo_template_object_properties` (`template_id`, `object_id`, `property`, `value`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`template_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='template')),
 (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`handler_class`=`c`.`object_manager`)),
 `c`.`property`, `c`.`value`
 FROM `og_template_object_properties` `c`;


 -- timeslots

INSERT INTO `fo_objects` (`name`, `f1_id`, `object_type_id`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`)
 SELECT c.`description`, c.`id`, (SELECT `id` FROM `fo_object_types` WHERE `name` = 'timeslot'),
  `c`.`created_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`created_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`updated_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`updated_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact'))
 FROM `og_timeslots` c;

INSERT INTO `fo_timeslots` (`object_id`, `rel_object_id`, `start_time`, `end_time`, `contact_id`, `description`, `paused_on`, `subtract`, `fixed_billing`, `hourly_billing`, `is_fixed_billing`, `billing_id`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='timeslot')), 
 IF (`c`.`object_manager` = 'Projects', 0, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`handler_class`=`c`.`object_manager`))),
 `c`.`start_time`, `c`.`end_time`, (SELECT x.id FROM fo_objects x WHERE x.f1_id=`c`.`user_id` AND x.object_type_id = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')), 
 `c`.`description`, `c`.`paused_on`, `c`.`subtract`, `c`.`fixed_billing`, `c`.`hourly_billing`, `c`.`is_fixed_billing`, `c`.`billing_id`
 FROM `og_timeslots` `c`;

INSERT INTO `fo_billing_categories` (`id`,`name`,`description`,`default_value`,`report_name`,`created_on`,`created_by_id`,`updated_on`,`updated_by_id`)
 SELECT `c`.`id`,`c`.`name`,`c`.`description`,`c`.`default_value`,`c`.`report_name`,
  `c`.`created_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`created_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`updated_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`updated_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact'))
 FROM `og_billing_categories` `c`;

-- reports

INSERT INTO `fo_objects` (`name`, `f1_id`, `object_type_id`)
 SELECT `name`, `id`, (SELECT `id` FROM `fo_object_types` WHERE `name` = 'report')
 FROM `og_reports`;

INSERT INTO `fo_reports` (`object_id`, `description`, `report_object_type_id`, `order_by`, `is_order_by_asc`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='report')),
 `c`.`description`, (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE IF(c.object_type='Contacts', ot.name='contact', `ot`.`handler_class`=`c`.`object_type`)), `c`.`order_by`, `c`.`is_order_by_asc`
 FROM `og_reports` `c`;

INSERT INTO `fo_report_columns` (`report_id`, `custom_property_id`, `field_name`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`report_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='report')), 
 `c`.`custom_property_id`, `c`.`field_name`
 FROM `og_report_columns` `c`;

INSERT INTO `fo_report_conditions` (`report_id`, `custom_property_id`, `field_name`, `condition`, `value`, `is_parametrizable`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`report_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='report')), 
 `c`.`custom_property_id`, `c`.`field_name`, `c`.`condition`, `c`.`value`, `c`.`is_parametrizable`
 FROM `og_report_conditions` `c`;


-- CUSTOM PROPERTIES
INSERT INTO fo_custom_properties (`id`, `object_type_id`, `name`, `type`, `description`, `values`, `default_value`, `is_required`, `is_multiple_values`, `property_order`, `visible_by_default`)
 SELECT `cp`.`id`, (SELECT id FROM fo_object_types WHERE handler_class=object_type limit 1), `cp`.`name`, `cp`.`type`, `cp`.`description`, `cp`.`values`, `cp`.`default_value`, `cp`.`is_required`, `cp`.`is_multiple_values`, `cp`.`property_order`, `cp`.`visible_by_default`
 FROM og_custom_properties cp
 WHERE cp.object_type <> 'Projects'
ON DUPLICATE KEY UPDATE name=cp.name;

INSERT INTO fo_custom_property_values (`object_id`, `custom_property_id`, `value`)
 SELECT (
	SELECT `id` FROM `fo_objects` WHERE `f1_id` = `cpv`.`object_id` AND `object_type_id` = (
		SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`handler_class`=(
			SELECT cp.object_type FROM og_custom_properties cp WHERE cp.id=cpv.custom_property_id limit 1
		) limit 1
	) limit 1
 ) as oid,  `cpv`.`custom_property_id`, `cpv`.`value`
 FROM og_custom_property_values cpv
 WHERE 
 	NOT ((SELECT `id` FROM `fo_objects` WHERE `f1_id` = `cpv`.`object_id` AND `object_type_id` = (
		SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`handler_class`=(
			SELECT cp.object_type FROM og_custom_properties cp WHERE cp.id=cpv.custom_property_id limit 1
		) limit 1
	) limit 1) is NULL)
ON DUPLICATE KEY UPDATE custom_property_id=cpv.custom_property_id;

-- WORKSPACE CUSTOM PROPERTIES
INSERT INTO fo_member_custom_properties (`id`, `object_type_id`, `name`, `type`, `description`, `values`, `default_value`, `is_required`, `is_multiple_values`, `property_order`, `visible_by_default`)
 SELECT `cp`.`id`, (SELECT id FROM fo_object_types WHERE name='workspace' limit 1), `cp`.`name`, `cp`.`type`, `cp`.`description`, `cp`.`values`, `cp`.`default_value`, `cp`.`is_required`, `cp`.`is_multiple_values`, `cp`.`property_order`, `cp`.`visible_by_default`
 FROM og_custom_properties cp
 WHERE cp.object_type = 'Projects'
ON DUPLICATE KEY UPDATE name=cp.name;

INSERT INTO fo_member_custom_property_values (`member_id`, `custom_property_id`, `value`)
 SELECT (
	SELECT `id` FROM `fo_members` WHERE `ws_id` = `cpv`.`object_id` AND `object_type_id` = (
		SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='workspace' limit 1
	) limit 1
 ) as oid,  `cpv`.`custom_property_id`, `cpv`.`value`
 FROM og_custom_property_values cpv
 WHERE 
 	NOT ((SELECT `id` FROM `fo_members` WHERE `ws_id` = `cpv`.`object_id` AND `object_type_id` = (
		SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='workspace' limit 1
	) limit 1) is NULL)
ON DUPLICATE KEY UPDATE custom_property_id=cpv.custom_property_id;


-- COMMENTS
INSERT INTO `fo_objects` (`name`, `f1_id`, `object_type_id`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`, `trashed_on`, `trashed_by_id`)
 SELECT `c`.`text`, `c`.`id`, (SELECT `id` FROM `fo_object_types` WHERE `name` = 'comment'),
  `c`.`created_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`created_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`updated_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`updated_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`trashed_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`trashed_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact'))
 FROM `og_comments` `c`;

INSERT INTO `fo_comments` (`object_id`, `text`, `rel_object_id`, `author_name`, `author_email`, `author_homepage`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='comment')), `c`.`text`,
 	(SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`rel_object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`type`='content_object' AND `ot`.`handler_class`=`c`.`rel_object_manager`)), '', '', '' 
 FROM `og_comments` `c`;


-- SUBSCRIPTIONS
INSERT INTO `fo_object_subscriptions` (`object_id`, `contact_id`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`handler_class`=`c`.`object_manager`)),
  (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`user_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact'))
 FROM `og_object_subscriptions` `c`
 WHERE `c`.`object_manager` IN ('ProjectMessages','ProjectWebpages','ProjectTasks','ProjectFiles','ProjectMilestones','ProjectEvents')
 AND NOT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`handler_class`=`c`.`object_manager`)) IS NULL;


-- LINKED OBJECTS
INSERT INTO `fo_linked_objects` (`rel_object_id`, `object_id`, `created_on`, `created_by_id`)
 SELECT
 	(SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`rel_object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE IF(`c`.`rel_object_manager` = 'Companies', ot.name='company', IF(`c`.`rel_object_manager` = 'Contacts', name='contact_tmp', `ot`.`handler_class`=`c`.`rel_object_manager`)) limit 1) limit 1),
 	(SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE IF(`c`.`object_manager` = 'Companies', ot.name='company', IF(`c`.`object_manager` = 'Contacts', name='contact_tmp', `ot`.`handler_class`=`c`.`object_manager`)) limit 1) limit 1),
  	`c`.`created_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`created_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact') limit 1)
 FROM `og_linked_objects` `c`
ON DUPLICATE KEY UPDATE `fo_linked_objects`.`rel_object_id`=`fo_linked_objects`.`rel_object_id`;
DELETE FROM `fo_linked_objects` WHERE rel_object_id=null OR rel_object_id = 0 OR object_id=null OR object_id = 0;


-- APPLICATION LOGS ***********************************************************

INSERT INTO `fo_application_logs` (`taken_by_id`, `rel_object_id`, `object_name`, `created_on`, `created_by_id`, `action`, `is_private`, `is_silent`, `log_data`)
 SELECT
  (SELECT `id` FROM `fo_objects` INNER JOIN fo_contacts k ON k.object_id=id WHERE k.user_type>0 AND `f1_id` = `c`.`taken_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')) as USERID,
  (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`rel_object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`handler_class`=`c`.`rel_object_manager` AND `ot`.`type`='content_object') limit 1) as OBJECTID,
  `c`.`object_name`, `c`.`created_on`, `c`.`created_by_id`, `c`.`action`, `c`.`is_private`, `c`.`is_silent`, `c`.`log_data`
 FROM `og_application_logs` `c`
 WHERE exists (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`handler_class`=`c`.`rel_object_manager` AND `ot`.`type`='content_object')
ON DUPLICATE KEY UPDATE fo_application_logs.taken_by_id=fo_application_logs.taken_by_id;
DELETE FROM fo_application_logs WHERE rel_object_id=0 OR rel_object_id IS NULL OR taken_by_id=0 OR taken_by_id IS NULL;
UPDATE fo_application_logs SET created_by_id = taken_by_id;

UPDATE fo_application_logs SET
  log_data = (SELECT `id` FROM `fo_objects` WHERE `f1_id` = SUBSTRING_INDEX(log_data, ':', -1) AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`handler_class` = SUBSTRING_INDEX(log_data, ':', 1) AND `ot`.`type`='content_object') limit 1)
WHERE `action` IN ('link', 'unlink');

UPDATE fo_application_logs SET
  log_data = CONCAT('to:', (SELECT `id` FROM `fo_members` WHERE ws_id = SUBSTRING_INDEX(log_data, ':', -1)))
WHERE `action` = 'copy';

-- move actions are migrated in AsadoUpgradeScript

-- ****************************************************************************


-- READ OBJECTS
INSERT INTO `fo_read_objects` (`rel_object_id`, `contact_id`, `is_read`, `created_on`)
 SELECT
  (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`rel_object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`handler_class`=`c`.`rel_object_manager` AND `ot`.`type`='content_object') limit 1),
  (SELECT `id` FROM `fo_objects` INNER JOIN fo_contacts k ON k.object_id=id WHERE k.user_type>0 AND `f1_id` = `c`.`user_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`is_read`, `c`.`created_on`
 FROM `og_read_objects` `c`
ON DUPLICATE KEY UPDATE fo_read_objects.rel_object_id=fo_read_objects.rel_object_id;
DELETE FROM fo_read_objects WHERE rel_object_id=0;


-- APPLICATION READ LOGS
INSERT INTO `fo_application_read_logs` (`taken_by_id`, `rel_object_id`, `created_on`, `created_by_id`, `action`)
 SELECT
  (SELECT `id` FROM `fo_objects` INNER JOIN fo_contacts k ON k.object_id=id WHERE k.user_type>0 AND `f1_id` = `c`.`taken_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`rel_object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`handler_class`=`c`.`rel_object_manager` AND `ot`.`type`='content_object') limit 1),
  `c`.`created_on`, `c`.`created_by_id`, `c`.`action`
 FROM `og_application_read_logs` `c`
ON DUPLICATE KEY UPDATE fo_application_read_logs.taken_by_id=fo_application_read_logs.taken_by_id;
DELETE FROM fo_application_read_logs WHERE rel_object_id=0;
UPDATE fo_application_read_logs SET created_by_id = taken_by_id;

-- ****************************************************************************


-- REMINDERS
INSERT INTO fo_object_reminders (`object_id`, `contact_id`, `type`, `context`, `minutes_before`, `date`)
 SELECT (SELECT o.id FROM fo_objects o WHERE o.f1_id=r.object_id AND o.object_type_id=(SELECT ot.id FROM fo_object_types ot WHERE ot.handler_class=r.object_manager)),
  IF (r.user_id > 0, (SELECT `id` FROM `fo_objects` WHERE f1_id = r.user_id AND object_type_id = (SELECT ot.id FROM fo_object_types ot WHERE ot.name='contact') limit 1), 0),
  r.`type`, r.`context`, r.`minutes_before`, r.`date`
 FROM og_object_reminders r;


-- file repo

DROP TABLE fo_file_repo;
CREATE TABLE fo_file_repo LIKE og_file_repo;
INSERT fo_file_repo SELECT * FROM og_file_repo;

-- file repo attributes

DROP TABLE fo_file_repo_attributes;
CREATE TABLE fo_file_repo_attributes LIKE og_file_repo_attributes;
INSERT fo_file_repo_attributes SELECT * FROM og_file_repo_attributes;


-- object members (workspaces)

INSERT INTO `fo_object_members` (`object_id`, `member_id`, `is_optimization`)
SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`handler_class`=`c`.`object_manager`)) as obj,
(SELECT `id` FROM `fo_members` WHERE `ws_id` = `c`.`workspace_id` AND `dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')) as mem, 0
FROM `og_workspace_objects` `c` WHERE `c`.`object_manager` IN ('ProjectMessages','ProjectWebpages','ProjectTasks','ProjectFiles','ProjectMilestones','ProjectEvents','MailContents','Reports','COTemplates','Comments','Timeslots','Companies')
	AND EXISTS (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`handler_class`=`c`.`object_manager`))
ON DUPLICATE KEY UPDATE `member_id`=`member_id`;


-- remove deleted emails from object_members
DELETE FROM fo_object_members WHERE object_id IN (SELECT m.object_id FROM fo_mail_contents m WHERE m.is_deleted=1);

-- object_members para timeslots (usar col: rel_object_id de la tabla de feng17 cuando el manager es Projects, sino poner en el mismo miembro que el obj asociado)

INSERT INTO `fo_object_members` (`object_id`, `member_id`, `is_optimization`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='timeslot')), 
 	(SELECT m.id FROM fo_members m WHERE m.ws_id = `c`.`object_id` AND m.dimension_id = (SELECT id FROM fo_dimensions WHERE code = 'workspaces')), 0
 FROM `og_timeslots` `c` WHERE `c`.`object_manager` = 'Projects'
ON DUPLICATE KEY UPDATE `member_id`=`member_id`;



-- CONFIG OPTIONS
UPDATE fo_config_options f SET f.value = (SELECT o.value FROM og_config_options o WHERE o.name = f.name)
WHERE f.name IN ('file_storage_adapter');
UPDATE fo_config_options f SET f.value = (SELECT o.value FROM og_config_options o WHERE o.name = f.name)
WHERE f.category_name = 'passwords';

-- luego de la migracion generar las entradas en object_members para los padres (is_optimization = 1) [OK en AsadoUpgradeScript.class.php]
-- searchable objects [OK en AsadoUpgradeScript.class.php]
-- sharing table [OK en AsadoUpgradeScript.class.php]






INSERT INTO `fo_object_types` (`name`, `handler_class`, `table_name`, `type`, `icon`, `plugin_id`)
 VALUES ('contact_tmp', 'Contacts', 'contacts', 'content_object', 'contact', 0);



-- CONTACTS

INSERT INTO `fo_objects` (`name`, `f1_id`, `object_type_id`, `created_on`, `created_by_id`, `updated_on`, `updated_by_id`, `trashed_on`, `trashed_by_id`, `archived_on`, `archived_by_id`)
 SELECT CONCAT(`c`.`firstname`,' ',`c`.`lastname`), `c`.`id`, (SELECT `id` FROM `fo_object_types` WHERE `name` = 'contact_tmp'),
  `c`.`created_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`created_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`updated_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`updated_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`trashed_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`trashed_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')),
  `c`.`archived_on`, (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`archived_by_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact'))
 FROM `og_contacts` `c` where `c`.`user_id` = 0;

INSERT INTO `fo_contacts` (`object_id`, `first_name`, `surname`, `is_company`, `timezone`, `picture_file`, `comments`, `company_id`, `department`, `job_title`, `birthday`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), `c`.`firstname`, `c`.`lastname`, 0, `c`.`timezone`, `c`.`picture_file`, `c`.`notes`,
 	(SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`company_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='company')), `c`.`department`, `c`.`job_title`, `c`.`o_birthday`
 FROM `og_contacts` `c` WHERE `c`.`user_id` = 0;


-- CONTACT DATA

INSERT INTO `fo_contact_emails` (`contact_id`, `email_type_id`, `is_main`, `email_address`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 2, 1, `c`.`email`
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;
INSERT INTO `fo_contact_emails` (`contact_id`, `email_type_id`, `is_main`, `email_address`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 2, 0, `c`.`email2`
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;
INSERT INTO `fo_contact_emails` (`contact_id`, `email_type_id`, `is_main`, `email_address`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 2, 0, `c`.`email3`
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;

DELETE FROM `fo_contact_emails` WHERE `contact_id` = 0 OR `email_address` = '';

INSERT INTO `fo_contact_telephones` (`contact_id`, `telephone_type_id`, `is_main`, `number`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 2, 1, `c`.`w_phone_number`
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;
INSERT INTO `fo_contact_telephones` (`contact_id`, `telephone_type_id`, `is_main`, `number`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 2, 0, `c`.`w_phone_number2`
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;
INSERT INTO `fo_contact_telephones` (`contact_id`, `telephone_type_id`, `is_main`, `number`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 1, 0, `c`.`h_phone_number`
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;
INSERT INTO `fo_contact_telephones` (`contact_id`, `telephone_type_id`, `is_main`, `number`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 1, 0, `c`.`h_phone_number2`
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;
INSERT INTO `fo_contact_telephones` (`contact_id`, `telephone_type_id`, `is_main`, `number`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 3, 0, `c`.`o_phone_number`
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;
INSERT INTO `fo_contact_telephones` (`contact_id`, `telephone_type_id`, `is_main`, `number`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 3, 0, `c`.`o_phone_number2`
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;
INSERT INTO `fo_contact_telephones` (`contact_id`, `telephone_type_id`, `is_main`, `number`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 8, 0, `c`.`w_fax_number`
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;
INSERT INTO `fo_contact_telephones` (`contact_id`, `telephone_type_id`, `is_main`, `number`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 4, 0, `c`.`w_assistant_number`
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;
INSERT INTO `fo_contact_telephones` (`contact_id`, `telephone_type_id`, `is_main`, `number`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 5, 0, `c`.`w_callback_number`
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;
INSERT INTO `fo_contact_telephones` (`contact_id`, `telephone_type_id`, `is_main`, `number`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 7, 0, `c`.`h_pager_number`
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;
INSERT INTO `fo_contact_telephones` (`contact_id`, `telephone_type_id`, `is_main`, `number`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 6, 0, `c`.`h_mobile_number`
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;
DELETE FROM `fo_contact_telephones` WHERE `number` = '';

INSERT INTO `fo_contact_web_pages` (`contact_id`, `web_type_id`, `url`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 2, `c`.`w_web_page`
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;
INSERT INTO `fo_contact_web_pages` (`contact_id`, `web_type_id`, `url`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 1, `c`.`h_web_page`
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;
INSERT INTO `fo_contact_web_pages` (`contact_id`, `web_type_id`, `url`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 3, `c`.`o_web_page`
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;

INSERT INTO `fo_contact_addresses` (`contact_id`, `address_type_id`, `street`, `city`, `state`, `country`, `zip_code`, `is_main`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 2, `c`.`w_address`, `c`.`w_city`, `c`.`w_state`, `c`.`w_country`, `c`.`w_zipcode`, 1
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;
INSERT INTO `fo_contact_addresses` (`contact_id`, `address_type_id`, `street`, `city`, `state`, `country`, `zip_code`, `is_main`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 1, `c`.`h_address`, `c`.`h_city`, `c`.`h_state`, `c`.`h_country`, `c`.`h_zipcode`, 0
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;
INSERT INTO `fo_contact_addresses` (`contact_id`, `address_type_id`, `street`, `city`, `state`, `country`, `zip_code`, `is_main`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')), 3, `c`.`o_address`, `c`.`o_city`, `c`.`o_state`, `c`.`o_country`, `c`.`o_zipcode`, 0
 FROM `og_contacts` `c`
ON DUPLICATE KEY UPDATE `contact_id`=`contact_id`;




-- contact object members (workspaces)

INSERT INTO `fo_object_members` (`object_id`, `member_id`, `is_optimization`)
SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp')) as obj,
(SELECT `id` FROM `fo_members` WHERE `ws_id` = `c`.`workspace_id` AND `dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')) as mem, 0
FROM `og_workspace_objects` `c` WHERE `c`.`object_manager` IN ('Contacts')
	AND EXISTS (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact_tmp'))
ON DUPLICATE KEY UPDATE `member_id`=`member_id`;


-- companies object members (workspaces)

INSERT INTO `fo_object_members` (`object_id`, `member_id`, `is_optimization`)
SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='company')) as obj,
(SELECT `id` FROM `fo_members` WHERE `ws_id` = `c`.`workspace_id` AND `dimension_id` = (SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces')) as mem, 0
FROM `og_workspace_objects` `c` WHERE `c`.`object_manager` IN ('Companies')
	AND EXISTS (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='company'))
ON DUPLICATE KEY UPDATE `member_id`=`member_id`;




-- object properties
INSERT INTO fo_object_properties (`rel_object_id`,`name`,`value`)
 SELECT (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `op`.`rel_object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot`
 	WHERE IF(`op`.`rel_object_manager` = 'Companies', ot.name='company', IF(`op`.`rel_object_manager` = 'Contacts', name='contact_tmp', `ot`.`handler_class`=`op`.`rel_object_manager`)))
 ), op.name, op.value
 FROM og_object_properties op
ON DUPLICATE KEY UPDATE fo_object_properties.name=fo_object_properties.name;



UPDATE fo_objects SET object_type_id = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact')
WHERE object_type_id IN (SELECT `ot2`.`id` FROM `fo_object_types` `ot2` WHERE `ot2`.`name` IN ('contact_tmp', 'company'));

DELETE FROM fo_object_types WHERE name IN ('contact_tmp');



-- CREATE MEMBERS IN PERSONS DIMENSION

INSERT INTO `fo_members` (`dimension_id`,`object_type_id`, `parent_member_id`, `depth`, `name`, `object_id` )
 SELECT 
 	(SELECT `id` FROM `fo_dimensions` WHERE `code`='feng_persons'),
 	(SELECT `id` FROM `fo_object_types` WHERE `name`='company'),
 	0, 1, `o`.`name`, `o`.`id`
 FROM `fo_objects` `o` INNER JOIN `fo_contacts` `c` ON `c`.`object_id`=`o`.`id`
 WHERE `c`.`is_company`=1
 ON DUPLICATE KEY UPDATE dimension_id=dimension_id;

INSERT INTO `fo_members` (`dimension_id`,`object_type_id`, `parent_member_id`, `depth`, `name`, `object_id` )
 SELECT 
 	(SELECT `id` FROM `fo_dimensions` WHERE `code`='feng_persons'),
 	(SELECT `id` FROM `fo_object_types` WHERE `name`='person'),
 	(SELECT `m`.`id` FROM `fo_members` `m` WHERE `m`.`object_id` = `c`.`company_id` AND `m`.`object_type_id` = (SELECT `id` FROM `fo_object_types` WHERE `name`='company') AND `m`.`dimension_id`=(SELECT `id` FROM `fo_dimensions` WHERE `code`='feng_persons') LIMIT 1),
 	(SELECT `m`.`depth`+1 FROM `fo_members` `m` WHERE `m`.`object_id` = `c`.`company_id` AND `m`.`object_type_id` = (SELECT `id` FROM `fo_object_types` WHERE `name`='company') AND `m`.`dimension_id`=(SELECT `id` FROM `fo_dimensions` WHERE `code`='feng_persons') LIMIT 1),
 	`o`.`name`, `o`.`id`
 FROM `fo_objects` `o` INNER JOIN `fo_contacts` `c` ON `c`.`object_id`=`o`.`id`
 WHERE `c`.`is_company`=0
 ON DUPLICATE KEY UPDATE dimension_id=dimension_id;

INSERT INTO `fo_contact_dimension_permissions` (`permission_group_id`,`dimension_id`, `permission_type` )
 SELECT 
 	(SELECT `permission_group_id` FROM `fo_contacts` WHERE `object_id`=`c`.`object_id`),
 	(SELECT `id` FROM `fo_dimensions` WHERE `code`='feng_persons'),
 	'check'
 FROM `fo_contacts` `c`
 WHERE `c`.`is_company`=0 AND `c`.`user_type`!=0
 ON DUPLICATE KEY UPDATE dimension_id=dimension_id;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_write`, `can_delete`)
 SELECT `c`.`permission_group_id`, `m`.`id`, `ot`.`id`, (`c`.`object_id` = `m`.`object_id`) as `can_write`, (`c`.`object_id` = `m`.`object_id`) as `can_delete`
 FROM `fo_contacts` `c` JOIN `fo_members` `m`, `fo_object_types` `ot` 
 WHERE `c`.`is_company`=0 
 	AND `c`.`user_type`!=0 
	AND `ot`.`type` IN ('content_object', 'located', 'comment')
 	AND `m`.`dimension_id` IN (SELECT `id` FROM `fo_dimensions` WHERE `code` = 'feng_persons')
 	AND `c`.`object_id` = `m`.`object_id`
 ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `fo_contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_write`, `can_delete`)
 SELECT `c`.`permission_group_id`, `m`.`id`, `ot`.`id`, (`c`.`object_id` = `m`.`object_id`) as `can_write`, (`c`.`object_id` = `m`.`object_id`) as `can_delete`
 FROM `fo_contacts` `c` JOIN `fo_members` `m`, `fo_object_types` `ot`
 WHERE `c`.`is_company`=0
 	AND `c`.`user_type`!=0
 	AND `ot`.`type` IN ('content_object', 'located', 'comment')
 	AND `m`.`dimension_id` IN (SELECT `id` FROM `fo_dimensions` WHERE `code` = 'feng_persons')
   AND `m`.`object_type_id` IN (20,21)
   AND `m`.`object_id` IN (SELECT om.object_id FROM fo_object_members om WHERE om.member_id IN (SELECT cmp.member_id FROM fo_contact_member_permissions cmp WHERE cmp.permission_group_id=c.permission_group_id))
 ON DUPLICATE KEY UPDATE member_id=member_id;



UPDATE `fo_contact_config_options` 
 SET default_value = concat((SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces'),',', (SELECT `id` FROM `fo_dimensions` WHERE `code`='feng_persons'),',', (SELECT `id` FROM `fo_dimensions` WHERE `code`='tags')) 
 WHERE name='root_dimensions';
UPDATE `fo_config_options` 
 SET value = concat((SELECT `id` FROM `fo_dimensions` WHERE `code`='workspaces'),',', (SELECT `id` FROM `fo_dimensions` WHERE `code`='feng_persons'),',', (SELECT `id` FROM `fo_dimensions` WHERE `code`='tags')) 
 WHERE name='enabled_dimensions';

-- TAGS object members

insert into fo_object_members
 SELECT
   (SELECT `id` FROM `fo_objects` WHERE `f1_id` = `c`.`rel_object_id` AND `object_type_id` <> (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact') AND `object_type_id` = (
     SELECT `ot`.`id` FROM `fo_object_types` `ot`
     WHERE IF(`c`.`rel_object_manager`='Companies', `ot`.`name`='company', IF(`c`.`rel_object_manager`='Contacts', `ot`.`name`='contact', `ot`.`handler_class`=`c`.`rel_object_manager`))
   ) limit 1) as obj,
 	(SELECT id FROM fo_members WHERE name = c.tag AND dimension_id = (SELECT id FROM fo_dimensions WHERE code = 'tags') limit 1),
   0
 FROM og_tags c
on duplicate key update member_id=member_id;

insert into fo_object_members
 SELECT
   (SELECT `id` FROM `fo_objects` inner join fo_contacts co on co.object_id=id and co.is_company=1
     WHERE `f1_id` = `c`.`rel_object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact') limit 1) as obj,
 	(SELECT id FROM fo_members WHERE name = c.tag AND dimension_id = (SELECT id FROM fo_dimensions WHERE code = 'tags') limit 1),
   0
 FROM og_tags c WHERE c.rel_object_manager='Companies'
on duplicate key update member_id=member_id;

insert into fo_object_members
 SELECT
   (SELECT `id` FROM `fo_objects` inner join fo_contacts co on co.object_id=id and co.is_company=0
     WHERE `f1_id` = `c`.`rel_object_id` AND `object_type_id` = (SELECT `ot`.`id` FROM `fo_object_types` `ot` WHERE `ot`.`name`='contact') limit 1) as obj,
 	(SELECT id FROM fo_members WHERE name = c.tag AND dimension_id = (SELECT id FROM fo_dimensions WHERE code = 'tags') limit 1),
   0
 FROM og_tags c WHERE c.rel_object_manager='Contacts'
on duplicate key update member_id=member_id;


-- EMAIL CONFIG OPTIONS

INSERT INTO `fo_config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
 SELECT o.category_name, o.name, o.value, o.config_handler_class, o.is_system, o.option_order, o.dev_comment FROM og_config_options o
 WHERE o.category_name = 'mailing'
ON DUPLICATE KEY UPDATE `fo_config_options`.`value`=`o`.`value`;

INSERT INTO fo_contact_config_options (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
 SELECT o.category_name, o.name, o.default_value, o.config_handler_class, o.is_system, o.option_order, o.dev_comment FROM og_user_ws_config_options o
 WHERE o.category_name = 'mails panel'
ON DUPLICATE KEY UPDATE `fo_contact_config_options`.`default_value`=`o`.`default_value`;

INSERT INTO `fo_contact_config_categories` (`name`, `is_system`, `type`, `category_order`) VALUES 
 ('mails panel', 0, 0, 5)
ON DUPLICATE KEY UPDATE name=name;

INSERT INTO `fo_cron_events` (name, recursive, delay, is_system, enabled) VALUES ('check_mail', 1, 10, 0, 1) ON DUPLICATE KEY UPDATE name=name;


-- personal workspaces

UPDATE `fo_contacts` c SET c.`personal_member_id` = (SELECT `id` FROM `fo_members` `m` WHERE `m`.`dimension_id`=(SELECT id FROM fo_dimensions WHERE code='workspaces') AND `m`.`ws_id`=(SELECT u.personal_project_id FROM og_users u WHERE u.id = (SELECT f1_id FROM fo_objects o WHERE o.id = c.object_id)))
WHERE c.user_type > 0;

-- set personal workspaces under person member

-- CREATE TABLE tmp_members (member_id INT, dim_id INT, o_id INT, depth INT, parent_id INT) ENGINE=MEMORY;
-- INSERT INTO tmp_members (member_id, dim_id, o_id, depth, parent_id)
--  SELECT id, dimension_id, object_id, depth, parent_member_id from fo_members
--  WHERE dimension_id IN (SELECT id FROM fo_dimensions WHERE code='feng_persons') AND object_id IN (SELECT object_id FROM fo_contacts WHERE user_type > 0);

-- UPDATE fo_members SET
--  dimension_id = (SELECT id FROM fo_dimensions WHERE code='feng_persons'),
--  parent_member_id = (
--    SELECT m.member_id FROM tmp_members m WHERE m.o_id = (
--      SELECT o.id FROM fo_objects o INNER JOIN fo_contacts c ON o.id = c.object_id
--      WHERE c.user_type>0 AND o.f1_id = (SELECT u.id FROM og_users u WHERE personal_project_id = ws_id)
--    )
--  ),
--  depth = (
--    SELECT m.depth + 1 FROM tmp_members m WHERE m.o_id = (
--      SELECT o.id FROM fo_objects o INNER JOIN fo_contacts c ON o.id = c.object_id
--      WHERE c.user_type>0 AND o.f1_id = (SELECT u.id FROM og_users u WHERE personal_project_id = ws_id)
--    )
--  )
-- WHERE id IN (SELECT personal_member_id FROM fo_contacts WHERE personal_member_id > 0);

-- DROP TABLE tmp_members;


-- no nulls in ids
update fo_objects set trashed_by_id=0 where trashed_by_id is null;
update fo_objects set archived_by_id=0 where archived_by_id is null;
update fo_objects set created_by_id=0 where created_by_id is null;
update fo_objects set updated_by_id=0 where updated_by_id is null;



-- companies comments
update fo_comments set fo_comments.rel_object_id = (select o.id from fo_objects o inner join fo_contacts co on co.object_id=o.id where o.object_type_id=16 and co.is_company=1 and o.f1_id=(
  select ogc.rel_object_id FROM og_comments ogc where ogc.rel_object_manager='Companies' AND ogc.`text`=fo_comments.`text` limit 1
)) where fo_comments.rel_object_id=0;

-- fix null contact fields
update fo_contacts set company_id=0 where company_id is null;
update fo_contacts set display_name='' where display_name is null;
update fo_contacts set avatar_file='' where avatar_file is null;
update fo_contacts set last_login='0000-00-00 00:00:00' where last_login is null;
update fo_contacts set last_visit='0000-00-00 00:00:00' where last_visit is null;
update fo_contacts set personal_member_id=0 where personal_member_id is null;
