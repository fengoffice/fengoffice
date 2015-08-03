-- <?php echo $table_prefix ?> fo_
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci
-- <?php echo $engine ?> InnoDB

ALTER TABLE `<?php echo $table_prefix ?>project_events` CHANGE `special_id` `special_id` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `<?php echo $table_prefix ?>project_file_revisions` ADD `hash` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

ALTER TABLE `<?php echo $table_prefix ?>contact_config_option_values` ADD `member_id` INT( 10 ) UNSIGNED NULL DEFAULT '0';
ALTER TABLE `<?php echo $table_prefix ?>contact_config_option_values` drop PRIMARY KEY;
ALTER TABLE `<?php echo $table_prefix ?>contact_config_option_values` ADD PRIMARY KEY ( `option_id` , `contact_id` , `member_id` );

INSERT INTO `<?php echo $table_prefix ?>widgets` (`name`, `title`, `plugin_id`, `path`, `default_options`, `default_section`, `default_order`) VALUES
 ('activity_feed', 'activity_feed', 0, '', '', 'left', 0)
ON DUPLICATE KEY UPDATE name=name;

INSERT INTO `<?php echo $table_prefix ?>contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
 ('dashboard', 'filters_dashboard', '0,0,10,0', 'StringConfigHandler', '0', '0', 'first position: entry to see the dimension, second position: view timeslot, third position: recent activities to show, fourth position: view views and downloads')
ON DUPLICATE KEY UPDATE name=name;

INSERT INTO `<?php echo $table_prefix ?>config_categories` (`name`, `is_system`) VALUES ('brand_colors', 1) ON DUPLICATE KEY UPDATE name=name;
INSERT INTO `<?php echo $table_prefix ?>config_options` (`category_name`,`name`,`value`,`config_handler_class`,`is_system`) VALUES
	('brand_colors', 'brand_colors_head_back', '', 'StringConfigHandler', 1),
	('brand_colors', 'brand_colors_head_font', '', 'StringConfigHandler', 1),
	('brand_colors', 'brand_colors_tabs_back', '', 'StringConfigHandler', 1),
	('brand_colors', 'brand_colors_tabs_font', '', 'StringConfigHandler', 1)
ON DUPLICATE KEY UPDATE name=name;

INSERT INTO `<?php echo $table_prefix ?>searchable_objects` (`rel_object_id`, `column_name`, `content`, `contact_id`) 
 SELECT id,'name',name,'0' FROM `<?php echo $table_prefix ?>objects` WHERE `object_type_id` = (SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name='message')
ON DUPLICATE KEY UPDATE rel_object_id=id,column_name='name';

INSERT INTO `<?php echo $table_prefix ?>tab_panels` (`id`,`title`,`icon_cls`,`refresh_on_context_change`,`default_controller`,`default_action`,`initial_controller`,`initial_action`,`enabled`,`type`,`ordering`,`plugin_id`,`object_type_id`) VALUES 
 ('contacts-panel','contacts','ico-contacts',1,'contact','init','','',0,'system',7,0, (SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name='contact')) 
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO `<?php echo $table_prefix ?>tab_panel_permissions` (`permission_group_id`, `tab_panel_id`) VALUES 
	((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'contacts-panel'),
	((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'), 'contacts-panel'),  
	((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'), 'contacts-panel'),  
	((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'), 'contacts-panel'),  
	((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'), 'contacts-panel'),  
	((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'), 'contacts-panel') 
ON DUPLICATE KEY UPDATE tab_panel_id = tab_panel_id;

ALTER TABLE `<?php echo $table_prefix ?>dimensions` ADD COLUMN `permission_query_method` ENUM('mandatory','not_mandatory') NOT NULL DEFAULT 'mandatory';

UPDATE <?php echo $table_prefix ?>contact_config_options SET default_value='due_date' WHERE name='tasksGroupBy';
INSERT INTO `<?php echo $table_prefix ?>config_options` (`category_name`,`name`,`value`,`config_handler_class`,`is_system`) VALUES
 ('general', 'use_milestones', (SELECT count(*) FROM <?php echo $table_prefix ?>project_milestones)>0, 'BoolConfigHandler', 0),
 ('general', 'show_tab_icons', '1', 'BoolConfigHandler', '0')
ON DUPLICATE KEY UPDATE name=name;

ALTER TABLE `<?php echo $table_prefix ?>event_invitations` ADD INDEX `contact_id`(`contact_id`, `event_id`);

ALTER TABLE `<?php echo $table_prefix ?>system_permissions` ADD COLUMN `can_see_assigned_to_other_tasks` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1;

INSERT INTO <?php echo $table_prefix ?>widgets (name, title, plugin_id, path, default_options, default_section, default_order) VALUES
 ('completed_tasks_list', 'completed tasks list', 0, '', '', 'right', 150)
ON DUPLICATE KEY UPDATE name=name;

ALTER TABLE `<?php echo $table_prefix ?>reports`
 ADD COLUMN `ignore_context` BOOLEAN NOT NULL DEFAULT 1,
 ADD INDEX `object_type`(`report_object_type_id`);

ALTER TABLE `<?php echo $table_prefix ?>dimensions` ADD COLUMN `is_required` BOOLEAN NOT NULL DEFAULT 0;

INSERT INTO `<?php echo $table_prefix ?>contact_config_categories` (`name`, `is_system`, `type`, `category_order`) VALUES
 ('listing preferences', 0, 0, 10)
ON DUPLICATE KEY UPDATE name=name;

INSERT INTO <?php echo $table_prefix ?>searchable_objects (rel_object_id, column_name, content, contact_id)
 SELECT id, 'object_id', id, 0 FROM <?php echo $table_prefix ?>objects
ON DUPLICATE KEY UPDATE rel_object_id=rel_object_id;