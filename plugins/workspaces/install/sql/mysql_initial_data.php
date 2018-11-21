INSERT INTO `<?php echo $table_prefix ?>dimensions` (`code`, `name`, `is_root`, `is_manageable`, `allows_multiple_selection`, `defines_permissions`, `is_system`,`default_order`, `options`, `permission_query_method` ) VALUES
 ('workspaces', 'Workspaces', 1, 1, 0, 1, 1, 2,'{"defaultAjax":{"controller":"dashboard", "action": "main_dashboard"}, "quickAdd":true,"showInPaths":true,"useLangs":true}', 'mandatory'),
 ('tags', 'Tags', 1, 1, 0, 0, 1, 3,'{"defaultAjax":{"controller":"dashboard", "action": "main_dashboard"},"quickAdd":true,"showInPaths":true,"useLangs":true}', 'not_mandatory');

INSERT INTO <?php echo $table_prefix ?>dimension_options (`dimension_id`, `name`, `value`) VALUES 
 ((SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='workspaces'),'useLangs','1'),
 ((SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='workspaces'),'showInPaths','1'),
 ((SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='tags'),'useLangs','1'),
 ((SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='tags'),'showInPaths','1')
ON DUPLICATE KEY UPDATE `value`=`value`;

INSERT INTO `<?php echo $table_prefix ?>dimension_object_types` (`dimension_id`, `object_type_id`, `is_root`,`options` ) VALUES
 ((SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='workspaces'), (SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='workspace'), 1, '{"defaultAjax":{"controller":"dashboard", "action": "main_dashboard"}}'),
 ((SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='tags'), (SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='tag'), 1, '{"defaultAjax":{"controller":"dashboard", "action": "main_dashboard"}}');

INSERT INTO <?php echo $table_prefix ?>dimension_object_type_options (`dimension_id`, `object_type_id`, `name`, `value`) VALUES 
 ((SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='workspaces'), (SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='workspace'),'select_after_creation','1'),
 ((SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='tags'), (SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='workspace'),'select_after_creation','1')
ON DUPLICATE KEY UPDATE `value`=`value`;

INSERT INTO `<?php echo $table_prefix ?>dimension_object_type_hierarchies` (`dimension_id`, `parent_object_type_id`, `child_object_type_id`) VALUES
 ((SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='workspaces'), (SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='workspace'), (SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='workspace'));

INSERT INTO `<?php echo $table_prefix ?>dimension_object_type_contents` (`dimension_id`,`dimension_object_type_id`,`content_object_type_id`, `is_required`, `is_multiple`)
 SELECT 
 	(SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='workspaces'),
 	(SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='workspace'),
 	`id`, 0, 1
 FROM `<?php echo $table_prefix ?>object_types` 
 WHERE `type` IN ('content_object', 'comment', 'located')
 ON DUPLICATE KEY UPDATE dimension_id=dimension_id;

INSERT INTO `<?php echo $table_prefix ?>dimension_object_type_contents` (`dimension_id`,`dimension_object_type_id`,`content_object_type_id`, `is_required`, `is_multiple`)
 SELECT 
 	(SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='tags'),
 	(SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='tag'),
 	`id`, 0, 1
 FROM `<?php echo $table_prefix ?>object_types` 
 WHERE `type` IN ('content_object', 'comment', 'located')
 ON DUPLICATE KEY UPDATE dimension_id=dimension_id;

INSERT INTO `<?php echo $table_prefix ?>contact_dimension_permissions` (`permission_group_id`, `dimension_id`, `permission_type`) VALUES
 (1, (SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='workspaces'), 'allow all');


UPDATE `<?php echo $table_prefix ?>contact_config_options` 
 SET default_value = concat(default_value,',', (SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='workspaces') ) 
 WHERE name='root_dimensions';

UPDATE `<?php echo $table_prefix ?>config_options` 
 SET value = concat(`<?php echo $table_prefix ?>config_options`.`value`,',', (SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='workspaces')) 
 WHERE name='enabled_dimensions';


INSERT INTO `<?php echo $table_prefix ?>contact_dimension_permissions` (permission_group_id, dimension_id, permission_type)
  SELECT DISTINCT(permission_group_id), (SELECT id FROM `<?php echo $table_prefix ?>dimensions` WHERE code = 'workspaces'), 'allow all'
  FROM <?php echo $table_prefix ?>contacts WHERE user_type IN (SELECT id FROM `<?php echo $table_prefix ?>permission_groups` WHERE name IN ('Super Administrator', 'Administrator'))
ON duplicate key UPDATE dimension_id = dimension_id;

UPDATE `<?php echo $table_prefix ?>tab_panels` SET default_action = 'main_dashboard', initial_action = 'main_dashboard' WHERE id = 'overview-panel' ;



INSERT INTO <?php echo $table_prefix ?>widgets(`name`, `title`, `plugin_id`, `default_section`,`default_order`,`icon_cls`,`path`, `default_options`) VALUES
 ('workspaces', 'workspaces', (SELECT id from <?php echo $table_prefix ?>plugins WHERE name = 'workspaces'), 'left', 3, 'ico-workspace', '', '')
ON DUPLICATE KEY update name = name;


-- DELETED INSERT INTO <?php echo $table_prefix ?>dimension_object_type_contents

INSERT INTO `<?php echo $table_prefix ?>dimension_member_associations` (`dimension_id`,`object_type_id`,`associated_dimension_id`, `associated_object_type_id`, `is_required`,`is_multiple`, `keeps_record`) VALUES
((SELECT id from <?php echo $table_prefix ?>dimensions WHERE code = 'workspaces'),(SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name = 'workspace'),(SELECT id from <?php echo $table_prefix ?>dimensions WHERE code = 'feng_persons'),(SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name = 'person'),0,1,0),
((SELECT id from <?php echo $table_prefix ?>dimensions WHERE code = 'workspaces'),(SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name = 'workspace'),(SELECT id from <?php echo $table_prefix ?>dimensions WHERE code = 'feng_persons'),(SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name = 'company'),0,1,0)
ON DUPLICATE KEY UPDATE dimension_id=dimension_id;

INSERT INTO `<?php echo $table_prefix ?>contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`) VALUES
 ('listing preferences', concat('lp_dim_workspaces_show_as_column'), '1', 'BoolConfigHandler', 0, 0),
 ('listing preferences', concat('lp_dim_tags_show_as_column'), '1', 'BoolConfigHandler', 0, 0)
ON DUPLICATE KEY UPDATE name=name;

UPDATE `<?php echo $table_prefix ?>contact_config_options` 
 SET default_value = concat((SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='workspaces'),',', (SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='tags'),',',(default_value)) 
 WHERE name='quick_add_task_view_dimensions_combos';

UPDATE `<?php echo $table_prefix ?>contact_config_options` 
 SET default_value = concat((SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='workspaces'),',', (SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='tags'),',',(default_value)) 
 WHERE name='add_timeslot_view_dimensions_combos';

INSERT INTO `<?php echo $table_prefix ?>tab_panels` (`id`,`title`,`icon_cls`,`refresh_on_context_change`,`default_controller`,`default_action`,`initial_controller`,`initial_action`,`enabled`,`type`,`ordering`,`plugin_id`,`object_type_id`,`url_params`) VALUES
 ('workspaces-panel', 'workspaces', 'ico-workspaces', 1, 'member', 'init', '', '', 0, 'system', 20, (SELECT `id` FROM `<?php echo $table_prefix ?>plugins` WHERE `name`='workspaces'), (SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name='workspace'),
	CONCAT('{"dim_id":',(SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='workspaces'),', "type_id":',(SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name='workspace'),'}') ),
 ('tags-panel', 'tags', 'ico-tags', 1, 'member', 'init', '', '', 0, 'system', 20, (SELECT `id` FROM `<?php echo $table_prefix ?>plugins` WHERE `name`='workspaces'), (SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name='tag'),
	CONCAT('{"dim_id":',(SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='tags'),', "type_id":',(SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name='tag'),'}') )
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO <?php echo $table_prefix ?>tab_panel_permissions (permission_group_id, tab_panel_id)
 SELECT pg.id, 'workspaces-panel' FROM <?php echo $table_prefix ?>permission_groups pg WHERE pg.type='roles' AND pg.name IN ('Super Administrator','Administrator','Manager','Executive')
ON DUPLICATE KEY UPDATE tab_panel_id=tab_panel_id;

INSERT INTO <?php echo $table_prefix ?>tab_panel_permissions (permission_group_id, tab_panel_id)
 SELECT pg.id, 'tags-panel' FROM <?php echo $table_prefix ?>permission_groups pg WHERE pg.type='roles' AND pg.name IN ('Super Administrator','Administrator','Manager','Executive')
ON DUPLICATE KEY UPDATE tab_panel_id=tab_panel_id;

INSERT INTO <?php echo $table_prefix ?>tab_panel_permissions (permission_group_id, tab_panel_id)
 SELECT pg.id, 'workspaces-panel' FROM <?php echo $table_prefix ?>permission_groups pg WHERE pg.type='permission_groups' AND pg.contact_id IN (
	SELECT c.object_id FROM <?php echo $table_prefix ?>contacts c WHERE c.user_type IN (SELECT pg.id FROM <?php echo $table_prefix ?>permission_groups pg WHERE pg.name IN ('Super Administrator','Administrator','Manager','Executive'))
 )
ON DUPLICATE KEY UPDATE tab_panel_id=tab_panel_id;

INSERT INTO <?php echo $table_prefix ?>tab_panel_permissions (permission_group_id, tab_panel_id)
 SELECT pg.id, 'tags-panel' FROM <?php echo $table_prefix ?>permission_groups pg WHERE pg.type='permission_groups' AND pg.contact_id IN (
	SELECT c.object_id FROM <?php echo $table_prefix ?>contacts c WHERE c.user_type IN (SELECT pg.id FROM <?php echo $table_prefix ?>permission_groups pg WHERE pg.name IN ('Super Administrator','Administrator','Manager','Executive'))
 )
ON DUPLICATE KEY UPDATE tab_panel_id=tab_panel_id;

INSERT INTO `<?php echo $table_prefix ?>custom_properties` (`object_type_id`, `name`, `code`, `type`, `description`, `values`, `default_value`, `is_required`, `is_multiple_values`, `property_order`, `visible_by_default`, `is_special`, `is_disabled`)
	SELECT mt.id, 'Color', 'color_special','color','','','',0,0,30,1, 1, 0
	FROM <?php echo $table_prefix ?>object_types mt WHERE name IN ('workspace')
ON DUPLICATE KEY UPDATE `code`=`code`;

INSERT INTO `<?php echo $table_prefix ?>custom_properties` (`object_type_id`, `name`, `code`, `type`, `description`, `values`, `default_value`, `is_required`, `is_multiple_values`, `property_order`, `visible_by_default`, `is_special`, `is_disabled`)
	SELECT mt.id, 'Description', 'description_special', 'memo','','','',0,0,31,1, 1, 0
	FROM <?php echo $table_prefix ?>object_types mt WHERE name IN ('workspace')
ON DUPLICATE KEY UPDATE `code`=`code`;