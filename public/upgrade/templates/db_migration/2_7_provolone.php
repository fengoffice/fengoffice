-- <?php echo $table_prefix ?> <?php echo $table_prefix ?>
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci
-- <?php echo $engine ?> InnoDB

INSERT INTO `<?php echo $table_prefix ?>contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
	('mails panel', 'view_mail_attachs_expanded', '1', 'BoolConfigHandler', 0, 0, ''),
	('mails panel', 'auto_classify_attachments', '1', 'BoolConfigHandler', 0, 0, ''),
	('calendar panel', 'show_birthdays_in_calendar', '1', 'BoolConfigHandler', 1, 0, '')
ON DUPLICATE KEY UPDATE name=name;

delete from <?php echo $table_prefix ?>contact_widgets where widget_name='active_context_info';
delete from <?php echo $table_prefix ?>widgets where name='active_context_info';

INSERT INTO `<?php echo $table_prefix ?>config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
	('general', 'give_member_permissions_to_new_users', '', 'UserTypeMultipleConfigHandler', '0', '0', NULL),
	('general', 'show_owner_company_name_header', 1, 'BoolConfigHandler', '0', '0', NULL)
ON DUPLICATE KEY UPDATE name=name;

UPDATE `<?php echo $table_prefix ?>config_options` SET `value`=(
	SELECT GROUP_CONCAT(id) FROM <?php echo $table_prefix ?>permission_groups WHERE `name` IN ('Super Administrator', 'Administrator')
)
WHERE `name`='give_member_permissions_to_new_users';

ALTER TABLE `<?php echo $table_prefix ?>sharing_table_flags` ADD COLUMN `object_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;

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
