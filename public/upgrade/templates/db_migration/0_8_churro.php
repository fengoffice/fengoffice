-- <?php echo $table_prefix ?> og_
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci

-- Migrate user that have telephone nombers or IMs to contacts 
INSERT INTO `<?php echo $table_prefix ?>contacts`
	(`email`, `firstname`,`lastname`,`company_id`,  `w_phone_number`,
	`w_fax_number`, `h_mobile_number`,  `h_phone_number`,	`created_by_id`,	`created_on`, `user_id`)
SELECT
	`email`,`username`,`display_name`,`company_id`,`office_number`,
	`fax_number`,`mobile_number`,`home_number`,	`created_by_id`,	`created_on`,`id`
FROM `<?php echo $table_prefix ?>users` WHERE
	`office_number` OR `fax_number` OR `mobile_number` OR `home_number` OR
	exists (SELECT * FROM `<?php echo $table_prefix ?>user_im_values` where `<?php echo $table_prefix ?>user_im_values`.`user_id` = `<?php echo $table_prefix ?>users`.`id`);



ALTER TABLE `<?php echo $table_prefix ?>users` DROP COLUMN `office_number`;
ALTER TABLE `<?php echo $table_prefix ?>users` DROP COLUMN `fax_number`;
ALTER TABLE `<?php echo $table_prefix ?>users` DROP COLUMN `mobile_number`;
ALTER TABLE `<?php echo $table_prefix ?>users` DROP COLUMN `home_number`;
DROP TABLE `<?php echo $table_prefix ?>user_im_values`;


CREATE TABLE  `<?php echo $table_prefix ?>timeslots` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `object_id` int(10) unsigned NOT NULL,
  `object_manager` varchar(50) <?php echo $default_collation ?> NOT NULL,
  `start_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `end_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `user_id` int(10) unsigned NOT NULL,
  `description` text <?php echo $default_collation ?> NOT NULL,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned NOT NULL,
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  INDEX `ObjectID` (`object_id`,`object_manager`)
) ENGINE=InnoDB <?php echo $default_charset ?>;

ALTER TABLE `<?php echo $table_prefix ?>project_tasks` ADD COLUMN `assigned_on` datetime default NULL;
ALTER TABLE `<?php echo $table_prefix ?>project_tasks` ADD COLUMN `assigned_by_id` int(10) unsigned default NULL;

ALTER TABLE `<?php echo $table_prefix ?>project_tasks` ADD COLUMN `time_estimate` INTEGER UNSIGNED NOT NULL DEFAULT 0;

UPDATE `<?php echo $table_prefix ?>project_tasks` SET `priority` = 200 WHERE `priority` = 0;

ALTER TABLE `<?php echo $table_prefix ?>project_tasks` ADD COLUMN `is_template` BOOLEAN NOT NULL DEFAULT '0';
ALTER TABLE `<?php echo $table_prefix ?>project_milestones` ADD COLUMN `is_template` BOOLEAN NOT NULL DEFAULT '0';
ALTER TABLE `<?php echo $table_prefix ?>mail_contents` ADD `is_read` INT( 1 ) NOT NULL DEFAULT '0' AFTER `is_private` ;
