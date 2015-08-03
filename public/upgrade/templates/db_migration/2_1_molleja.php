-- <?php echo $table_prefix ?> fo_
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci
-- <?php echo $engine ?> InnoDB

ALTER TABLE `<?php echo $table_prefix ?>members`
 ADD COLUMN `archived_by_id` INTEGER UNSIGNED NOT NULL,
 ADD COLUMN `archived_on` DATETIME NOT NULL,
 ADD INDEX `archived_on`(`archived_on`);

INSERT INTO `<?php echo $table_prefix ?>file_types` (`id` ,`extension` ,`icon` ,`is_searchable` ,`is_image`) VALUES
 ('34', 'odt', 'doc.png', '1', '0'), ('35', 'fodt', 'doc.png', '1', '0')
ON DUPLICATE KEY UPDATE id=id;

ALTER TABLE `<?php echo $table_prefix ?>file_types` ADD COLUMN `is_allow` TINYINT(1) NOT NULL DEFAULT '1';

ALTER TABLE `<?php echo $table_prefix ?>external_calendar_users` ADD `related_to` VARCHAR( 255 ) NOT NULL;
                                
ALTER TABLE `<?php echo $table_prefix ?>project_events` ADD `update_sync` DATETIME NOT NULL AFTER `special_id`;

ALTER TABLE  `<?php echo $table_prefix ?>permission_groups` ADD  `type` ENUM(  'roles',  'permission_groups',  'user_groups' ) NOT NULL;
UPDATE `<?php echo $table_prefix ?>permission_groups` SET `type` = 'roles' WHERE `id` <= 13;
UPDATE `<?php echo $table_prefix ?>permission_groups` SET `type` = 'permission_groups' WHERE `contact_id` > 0;
UPDATE `<?php echo $table_prefix ?>permission_groups` SET `type` = 'user_groups' WHERE `contact_id` = 0 AND `id` > 13;