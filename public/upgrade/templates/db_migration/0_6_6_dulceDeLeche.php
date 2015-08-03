-- <?php echo $table_prefix ?> og_
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci

ALTER TABLE `<?php echo $table_prefix ?>project_tasks` 
		ADD COLUMN `start_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `<?php echo $table_prefix ?>project_tasks` 
		ADD COLUMN `due_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `<?php echo $table_prefix ?>projects` 
		ADD COLUMN `parent_id` INTEGER NOT NULL DEFAULT 0;