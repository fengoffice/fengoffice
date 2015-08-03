-- <?php echo $table_prefix ?> og_
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci


INSERT INTO `<?php echo $table_prefix ?>user_ws_config_options` (`id`,`category_name`,`name`,`default_value`,`config_handler_class`,`is_system`,`option_order`,`dev_comment`) VALUES 
 (10,'dashboard','show tasks in progress widget','1','BoolConfigHandler',0,0,''),
 (11,'task panel','can notify from quick add','0','BoolConfigHandler',0,0,''),
 (12,'task panel','tasksShowWorkspaces','1','BoolConfigHandler',1,0,''),
 (13,'task panel','tasksShowTime','1','BoolConfigHandler',1,0,''),
 (14,'task panel','tasksShowDates','1','BoolConfigHandler',1,0,''),
 (15,'task panel','tasksShowTags','1','BoolConfigHandler',1,0,''),
 (16,'task panel','tasksGroupBy','milestone','StringConfigHandler',1,0,''),
 (17,'task panel','tasksOrderBy','priority','StringConfigHandler',1,0,''),
 (18,'task panel','task panel status','1','IntegerConfigHandler',1,0,''),
 (19,'task panel','task panel filter','assigned_to','StringConfigHandler',1,0,''),
 (20,'task panel','task panel filter value','0:0','UserCompanyConfigHandler',1,0,''),
 (21,'dashboard','show comments widget','1','BoolConfigHandler',0,0,''),
 (22,'dashboard','always show unread mail in dashboard','0','BoolConfigHandler',0,10,'when false, active workspace email is shown')
 ON DUPLICATE KEY UPDATE id=id;
  
CREATE TABLE `<?php echo $table_prefix ?>object_subscriptions` (
  `object_id` int(10) unsigned NOT NULL default '0',
  `object_manager` varchar(50) NOT NULL,
  `user_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`object_id`,`object_manager`,`user_id`)
) ENGINE=InnoDB <?php echo $default_charset ?>;

INSERT INTO `<?php echo $table_prefix ?>object_subscriptions` (`object_id`, `object_manager`, `user_id`) 
	SELECT `message_id` as `object_id`, 'ProjectMessages' as `object_manager`, `user_id`
	FROM `<?php echo $table_prefix ?>message_subscriptions`;

DROP TABLE `<?php echo $table_prefix ?>message_subscriptions`;

CREATE TABLE `<?php echo $table_prefix ?>object_reminders` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `object_id` int(10) unsigned NOT NULL default '0',
  `object_manager` varchar(50) NOT NULL,
  `user_id` int(10) unsigned NOT NULL default '0',
  `type` varchar(40) NOT NULL default '',
  `minutes_before` int(10) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>event_invitations` (
  `event_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `invitation_state` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`event_id`, `user_id`)
) ENGINE=InnoDB <?php echo $default_charset ?>;

INSERT INTO `<?php echo $table_prefix ?>event_invitations` (`event_id`, `user_id`, `invitation_state`)
 SELECT `id`, `created_by_id` , 1 as `estado` FROM `<?php echo $table_prefix ?>project_events`
 ON DUPLICATE KEY UPDATE `event_id`=`event_id`;
 