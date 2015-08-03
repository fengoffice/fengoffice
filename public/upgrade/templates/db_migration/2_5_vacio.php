-- <?php echo $table_prefix ?> <?php echo $table_prefix ?>
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci
-- <?php echo $engine ?> InnoDB

INSERT INTO <?php echo $table_prefix ?>widgets (name,title,plugin_id,path,default_options,default_section,default_order,icon_cls) VALUES
 ('comments','comments',0,'','','left',5,'ico-comment')
on duplicate key update name=name;

DELETE FROM <?php echo $table_prefix ?>widgets WHERE name IN ('completed_tasks', 'crpm_people');
				
UPDATE <?php echo $table_prefix ?>widgets SET icon_cls='ico-properties' WHERE name='activity_feed';
UPDATE <?php echo $table_prefix ?>widgets SET icon_cls='ico-event' WHERE name='calendar';
UPDATE <?php echo $table_prefix ?>widgets SET icon_cls='ico-task' WHERE name='completed_tasks_list';
UPDATE <?php echo $table_prefix ?>widgets SET icon_cls='ico-customer' WHERE name='customers';
UPDATE <?php echo $table_prefix ?>widgets SET icon_cls='ico-file' WHERE name='documents';
UPDATE <?php echo $table_prefix ?>widgets SET icon_cls='ico-email' WHERE name='emails';
UPDATE <?php echo $table_prefix ?>widgets SET icon_cls='ico-task' WHERE name='estimated_worked_time';
UPDATE <?php echo $table_prefix ?>widgets SET icon_cls='ico-folder' WHERE name='folders';
UPDATE <?php echo $table_prefix ?>widgets SET icon_cls='ico-message' WHERE name='messages';
UPDATE <?php echo $table_prefix ?>widgets SET icon_cls='ico-task' WHERE name='overdue_upcoming';
UPDATE <?php echo $table_prefix ?>widgets SET icon_cls='ico-contact' WHERE name='people';
UPDATE <?php echo $table_prefix ?>widgets SET icon_cls='ico-project' WHERE name='projects';
UPDATE <?php echo $table_prefix ?>widgets SET icon_cls='ico-properties' WHERE name='statics';
UPDATE <?php echo $table_prefix ?>widgets SET icon_cls='ico-properties' WHERE name='summary';
UPDATE <?php echo $table_prefix ?>widgets SET icon_cls='ico-workspace' WHERE name='workspaces';
UPDATE <?php echo $table_prefix ?>widgets SET icon_cls='ico-workspace' WHERE name='ws_description';
UPDATE <?php echo $table_prefix ?>widgets SET icon_cls='ico-comment' WHERE name='comments';

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

INSERT INTO `<?php echo $table_prefix ?>contact_widget_options` (widget_name,contact_id,member_type_id,`option`,`value`,config_handler_class,is_system) VALUES
('overdue_upcoming',0,0,'assigned_to_user',0,'UserCompanyConfigHandler',0),
('calendar',0,0,'filter_by_myself',0,'BooleanConfigHandler',0)
ON DUPLICATE KEY UPDATE widget_name=widget_name;

update `<?php echo $table_prefix ?>contact_config_options` set `default_value`=1 where `name`='viewUsersChecked';