
INSERT INTO `<?php echo $table_prefix ?>plugins` (`name`,`is_installed`, `is_activated`, `priority`, `activated_on`, `activated_by_id`) VALUES 
 ('core_dimensions', 1, 1, 0, NOW(), 1)
 ON DUPLICATE KEY UPDATE id=id;

INSERT INTO `<?php echo $table_prefix ?>dimensions` (`code`,`name`,`is_root`,`is_manageable`,`allows_multiple_selection`,`defines_permissions`, `is_system`, `default_order`, `options`, `permission_query_method`) VALUES
 ('feng_persons', 'People', 1, 0, 0, 1, 1, 99, '{"useLangs":true,"defaultAjax":{"controller":"dashboard", "action": "main_dashboard"},"quickAdd":{"formAction":"?c=contact&a=quick_add"}}', 'not_mandatory' )
 ON DUPLICATE KEY UPDATE id=id;

INSERT INTO <?php echo $table_prefix ?>dimension_options (`dimension_id`, `name`, `value`) VALUES
 ((SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='feng_persons'),'useLangs','1')
ON DUPLICATE KEY UPDATE `value`=`value`;

INSERT INTO `<?php echo $table_prefix ?>object_types` (`name`,`handler_class`,`table_name`,`type`,`icon`,`plugin_id`) VALUES
 ('person', 'Contacts', 'contacts', 'dimension_object', 'contact', (SELECT `id` FROM `<?php echo $table_prefix ?>plugins` WHERE `name`='core_dimensions')),
 ('company', 'Contacts', 'contacts', 'dimension_object', 'company', (SELECT `id` FROM `<?php echo $table_prefix ?>plugins` WHERE `name`='core_dimensions'))
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO `<?php echo $table_prefix ?>dimension_object_types` (`dimension_id`,`object_type_id`,`is_root`, `options`) VALUES
 ((SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='feng_persons'), (SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='person'), 1,'{"defaultAjax":{"controller":"contact", "action": "card"}}'),
 ((SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='feng_persons'), (SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='company'), 1,'{"defaultAjax":{"controller":"contact", "action": "company_card"}}')
ON DUPLICATE KEY UPDATE dimension_id=dimension_id;

INSERT INTO `<?php echo $table_prefix ?>dimension_object_type_hierarchies` (`dimension_id`, `parent_object_type_id`, `child_object_type_id`) VALUES
 ((SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='feng_persons'),
 (SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='company'),
 (SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='person'));

INSERT INTO `<?php echo $table_prefix ?>dimension_object_type_contents` (`dimension_id`,`dimension_object_type_id`,`content_object_type_id`, `is_required`, `is_multiple`)
 SELECT 
 	(SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='feng_persons'),
 	(SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='person'),
 	`id`, 0, 1
 FROM `<?php echo $table_prefix ?>object_types` 
 WHERE `type` IN ('content_object', 'comment', 'located')
ON DUPLICATE KEY UPDATE dimension_id=dimension_id;

INSERT INTO `<?php echo $table_prefix ?>dimension_object_type_contents` (`dimension_id`,`dimension_object_type_id`,`content_object_type_id`, `is_required`, `is_multiple`)
 SELECT 
 	(SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='feng_persons'),
 	(SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='company'),
 	`id`, 0, 1
 FROM `<?php echo $table_prefix ?>object_types` 
 WHERE `type` IN ('content_object', 'comment', 'located')
ON DUPLICATE KEY UPDATE dimension_id=dimension_id;



INSERT INTO `<?php echo $table_prefix ?>members` (`dimension_id`,`object_type_id`, `parent_member_id`, `depth`, `name`, `object_id` )
 SELECT 
 	(SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='feng_persons'),
 	(SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='company'),
 	0, 1, `o`.`name`, `o`.`id`
 FROM `<?php echo $table_prefix ?>objects` `o` INNER JOIN `<?php echo $table_prefix ?>contacts` `c` ON `c`.`object_id`=`o`.`id`
 WHERE `c`.`is_company`=1
ON DUPLICATE KEY UPDATE dimension_id=dimension_id;

INSERT INTO `<?php echo $table_prefix ?>members` (`dimension_id`,`object_type_id`, `parent_member_id`, `depth`, `name`, `object_id` )
 SELECT 
 	(SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='feng_persons'),
 	(SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='person'),
 	(SELECT `m`.`id` FROM `<?php echo $table_prefix ?>members` `m` WHERE `m`.`object_id` = `c`.`company_id` AND `m`.`object_type_id` = (SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='company') AND `m`.`dimension_id`=(SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='feng_persons') LIMIT 1),
 	(SELECT `m`.`depth`+1 FROM `<?php echo $table_prefix ?>members` `m` WHERE `m`.`object_id` = `c`.`company_id` AND `m`.`object_type_id` = (SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='company') AND `m`.`dimension_id`=(SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='feng_persons') LIMIT 1),
 	`o`.`name`, `o`.`id`
 FROM `<?php echo $table_prefix ?>objects` `o` INNER JOIN `<?php echo $table_prefix ?>contacts` `c` ON `c`.`object_id`=`o`.`id`
 WHERE `c`.`is_company`=0
ON DUPLICATE KEY UPDATE dimension_id=dimension_id;




INSERT INTO `<?php echo $table_prefix ?>contact_dimension_permissions` (`permission_group_id`,`dimension_id`, `permission_type` )
 SELECT 
 	(SELECT `permission_group_id` FROM `<?php echo $table_prefix ?>contacts` WHERE `object_id`=`c`.`object_id`),
 	(SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='feng_persons'),
 	'check'
 FROM `<?php echo $table_prefix ?>contacts` `c`
 WHERE `c`.`is_company`=0 AND `c`.`user_type`!=0
ON DUPLICATE KEY UPDATE dimension_id=dimension_id;

INSERT INTO `<?php echo $table_prefix ?>contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_write`, `can_delete`)
 SELECT `c`.`permission_group_id`, `m`.`id`, `ot`.`id`, (`c`.`object_id` = `m`.`object_id`) as `can_write`, (`c`.`object_id` = `m`.`object_id`) as `can_delete`
 FROM `<?php echo $table_prefix ?>contacts` `c` JOIN `<?php echo $table_prefix ?>members` `m`, `<?php echo $table_prefix ?>object_types` `ot` 
 WHERE `c`.`is_company`=0 
 	AND `c`.`user_type`!=0 
	AND `ot`.`type` IN ('content_object', 'located', 'comment')
 	AND `m`.`dimension_id` IN (SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code` = 'feng_persons')
ON DUPLICATE KEY UPDATE member_id=member_id;




INSERT INTO `<?php echo $table_prefix ?>object_members` (`member_id`, `object_id`)
 SELECT `m`.`id`, `o`.`id`
 FROM `<?php echo $table_prefix ?>objects` `o` INNER JOIN `<?php echo $table_prefix ?>members` `m`
 WHERE (`o`.`created_by_id` = `m`.`object_id` OR `o`.`updated_by_id` = `m`.`object_id` OR `o`.`trashed_by_id` = `m`.`object_id` OR `o`.`archived_by_id` = `m`.`object_id`)
	AND `m`.`id` IN (SELECT `id` FROM `<?php echo $table_prefix ?>members` WHERE `dimension_id` IN (SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code` IN ('feng_persons')))
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `<?php echo $table_prefix ?>object_members` (`member_id`, `object_id`)
 SELECT `m`.`id`, `o`.`id`
 FROM `<?php echo $table_prefix ?>objects` `o` INNER JOIN `<?php echo $table_prefix ?>members` `m`
 WHERE `m`.`id` IN (SELECT `id` FROM `<?php echo $table_prefix ?>members` WHERE `dimension_id` IN (SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code` IN ('feng_persons')))
	AND `m`.`object_id` IN (SELECT `s`.`contact_id` FROM `<?php echo $table_prefix ?>object_subscriptions` `s` WHERE `s`.`object_id` = `o`.`id`)
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `<?php echo $table_prefix ?>object_members` (`member_id`, `object_id`)
 SELECT `m`.`id`, `o`.`id`
 FROM `<?php echo $table_prefix ?>objects` `o` INNER JOIN `<?php echo $table_prefix ?>project_tasks` `t` ON `t`.`object_id` = `o`.`id`, `<?php echo $table_prefix ?>members` `m`
 WHERE `m`.`id` IN (SELECT `id` FROM `<?php echo $table_prefix ?>members` WHERE `dimension_id` IN (SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code` IN ('feng_persons')))
	AND `m`.`object_id` IN (`t`.`assigned_by_id`, `t`.`assigned_to_contact_id`)
ON DUPLICATE KEY UPDATE member_id=member_id;

INSERT INTO `<?php echo $table_prefix ?>object_members` (`member_id`, `object_id`)
 SELECT `m`.`id`, `o`.`id`
 FROM `<?php echo $table_prefix ?>objects` `o` INNER JOIN `<?php echo $table_prefix ?>members` `m`
 WHERE `m`.`id` IN (SELECT `id` FROM `<?php echo $table_prefix ?>members` WHERE `dimension_id` IN (SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code` IN ('feng_persons')))
	AND `m`.`object_id` IN (SELECT `ei`.`contact_id` FROM `<?php echo $table_prefix ?>event_invitations` `ei` WHERE `ei`.`event_id` = `o`.`id`)
ON DUPLICATE KEY UPDATE member_id=member_id;
 
INSERT INTO `<?php echo $table_prefix ?>config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`) VALUES
	('system', 'hide_people_vinculations', '1', 'BoolConfigHandler', 1, 0)
ON DUPLICATE KEY UPDATE name=name;

INSERT INTO `<?php echo $table_prefix ?>contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_write`, `can_delete`)
 SELECT `c`.`permission_group_id`, `m`.`id` as member_id, `ot`.`id` as object_type_id, 1, 1
 FROM `<?php echo $table_prefix ?>contacts` `c` JOIN `<?php echo $table_prefix ?>members` `m`, `<?php echo $table_prefix ?>object_types` `ot`
 WHERE `c`.`object_id`=m.object_id
   AND `c`.`permission_group_id` > 0
 	AND `ot`.`type` IN ('located')
 	AND `m`.`dimension_id` IN (SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code` = 'feng_persons')
ON DUPLICATE KEY UPDATE <?php echo $table_prefix ?>contact_member_permissions.member_id=<?php echo $table_prefix ?>contact_member_permissions.member_id;