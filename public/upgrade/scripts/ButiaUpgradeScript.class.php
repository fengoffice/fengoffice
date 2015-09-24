<?php

/**
 * Butia upgrade script will upgrade FengOffice 3.1.5.3 to FengOffice 3.2.3
 *
 * @package ScriptUpgrader.scripts
 * @version 1.0
 */
class ButiaUpgradeScript extends ScriptUpgraderScript {

	/**
	 * Array of files and folders that need to be writable
	 *
	 * @var array
	 */
	private $check_is_writable = array(
		'/config/config.php',
		'/config',
		'/cache',
		'/tmp',
		'/upload'
	 ); // array

	 /**
	 * Array of extensions taht need to be loaded
	 *
	 * @var array
	 */
	private $check_extensions = array(
		'mysql', 'gd', 'simplexml'
	); // array

	 /**
	 * Construct the ButiaUpgradeScript
	 *
	 * @param Output $output
	 * @return ButiaUpgradeScript
	 */
	function __construct(Output $output) {
		parent::__construct($output);
		$this->setVersionFrom('3.1.5.3');
		$this->setVersionTo('3.2.3');
	} // __construct

	function getCheckIsWritable() {
		return $this->check_is_writable;
	}

	function getCheckExtensions() {
		return $this->check_extensions;
	}
	
	/**
	 * Execute the script
	 *
	 * @param void
	 * @return boolean
	 */
	function execute() {
		if (!@mysql_ping($this->database_connection)) {
			if ($dbc = mysql_connect(DB_HOST, DB_USER, DB_PASS)) {
				if (mysql_select_db(DB_NAME, $dbc)) {
					$this->printMessage('Upgrade script has connected to the database.');
				} else {
					$this->printMessage('Failed to select database ' . DB_NAME);
					return false;
				}
				$this->setDatabaseConnection($dbc);
			} else {
				$this->printMessage('Failed to connect to database');
				return false;
			}
		}
		
		// ---------------------------------------------------
		//  Check MySQL version
		// ---------------------------------------------------

		$mysql_version = mysql_get_server_info($this->database_connection);
		if($mysql_version && version_compare($mysql_version, '4.1', '>=')) {
			$constants['DB_CHARSET'] = 'utf8';
			@mysql_query("SET NAMES 'utf8'", $this->database_connection);
			tpl_assign('default_collation', $default_collation = 'collate utf8_unicode_ci');
			tpl_assign('default_charset', $default_charset = 'DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
		} else {
			tpl_assign('default_collation', $default_collation = '');
			tpl_assign('default_charset', $default_charset = '');
		} // if

		$installed_version = installed_version();
		$t_prefix = TABLE_PREFIX;
		$additional_upgrade_steps = array();
						
		// RUN QUERIES
		$total_queries = 0;
		$executed_queries = 0;

		$upgrade_script = "";
		
		$v_from = array_var($_POST, 'form_data');
		$original_version_from = array_var($v_from, 'upgrade_from', $installed_version);
		
		
		// Set upgrade queries	
		if (version_compare($installed_version, '3.2-beta') < 0) {
			$upgrade_script .= "
				CREATE TABLE IF NOT EXISTS `".$t_prefix."external_calendar_properties` (
				  `external_calendar_id` int(10) unsigned NOT NULL,
				  `key` varchar(255) ".$default_collation." NOT NULL,
				  `value` text  ".$default_collation." NOT NULL, 
				  PRIMARY KEY (`external_calendar_id`,`key`)
				) ENGINE=InnoDB ".$default_charset.";
			";	
			
			$upgrade_script .= "
				TRUNCATE TABLE `".$t_prefix."external_calendar_users`;
				ALTER TABLE `".$t_prefix."external_calendar_users` MODIFY auth_pass text;
			";
			
			$upgrade_script .= "
				UPDATE `".$t_prefix."project_events` SET ext_cal_id=0
				WHERE ext_cal_id > 0;
			";
			
			$upgrade_script .= "
				TRUNCATE TABLE `".$t_prefix."external_calendars`;
			";
			
			if ($this->checkColumnExists($t_prefix."external_calendars", "calendar_user", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE  `".$t_prefix."external_calendars` CHANGE `calendar_user` `original_calendar_id` varchar(255);
				";
			}
			
			if (!$this->checkColumnExists($t_prefix."external_calendars", "sync", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."external_calendars` ADD COLUMN `sync` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER calendar_feng;
				";
			}
			
			if (!$this->checkColumnExists($t_prefix."external_calendars", "related_to", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."external_calendars` ADD COLUMN `related_to` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER sync;
				";
			}
			
			$upgrade_script .= "
				CREATE TABLE IF NOT EXISTS `".$t_prefix."template_instantiated_parameters` (
				  `template_id` INTEGER UNSIGNED NOT NULL,
				  `instantiation_id` INTEGER UNSIGNED NOT NULL,
				  `parameter_name` VARCHAR(255) NOT NULL DEFAULT '',
				  `value` TEXT NOT NULL,
				  PRIMARY KEY (`template_id`, `instantiation_id`, `parameter_name`)
				) ENGINE = InnoDB;
			";
			
			if (!$this->checkColumnExists($t_prefix."project_tasks", "instantiation_id", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."project_tasks` ADD COLUMN `instantiation_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;
				";
			}
			
			if (!$this->checkColumnExists($t_prefix."queued_emails", "cc", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."queued_emails`
					 ADD COLUMN `cc` TEXT NOT NULL AFTER `to`,
					 ADD COLUMN `bcc` TEXT NOT NULL AFTER `cc`;
				";
			}
			
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				 ('system', 'last_template_instantiation_id', '0', 'IntegerConfigHandler', '1', '0', NULL)
				ON DUPLICATE KEY UPDATE name=name;
			";
			
			
			// max member permissions by role
			if (!$this->checkTableExists($t_prefix.'max_role_object_type_permissions', $this->database_connection)) {
				$upgrade_script .= "
					CREATE TABLE `".$t_prefix."max_role_object_type_permissions` (
					  `role_id` INTEGER UNSIGNED NOT NULL,
					  `object_type_id` INTEGER UNSIGNED NOT NULL,
					  `can_delete` BOOLEAN NOT NULL,
					  `can_write` BOOLEAN NOT NULL,
					  PRIMARY KEY (`role_id`, `object_type_id`)
					) ENGINE = InnoDB;
				";
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."max_role_object_type_permissions` SELECT * FROM `".$t_prefix."role_object_type_permissions`;
				";
				
				$upgrade_script .= "
					DELETE FROM `".$t_prefix."role_object_type_permissions` 
					WHERE object_type_id=(select id from ".$t_prefix."object_types where name='report') 
						AND role_id IN (SELECT id FROM ".$t_prefix."permission_groups WHERE `type`='roles' AND name IN ('Guest Customer','Guest','Non-Exec Director'));
				";
			}
			
			
			if (!$this->checkColumnExists($t_prefix."template_parameters", "default_value", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."template_parameters`
					 ADD COLUMN `default_value` TEXT NOT NULL;
				";
			}
			
			
			if($mysql_version && version_compare($mysql_version, '5.6', '>=')) {
				//bad performance 
			/*	$upgrade_script .= "
					CREATE TABLE `".$t_prefix."searchable_objects_new` (
						`rel_object_id` int(10) unsigned NOT NULL default '0',
						`column_name` varchar(50) collate utf8_unicode_ci NOT NULL default '',
						`content` text collate utf8_unicode_ci NOT NULL,
						`contact_id` int(10) unsigned NOT NULL default '0',
						PRIMARY KEY  (`rel_object_id`,`column_name`),
						FULLTEXT KEY `content` (`content`),
						KEY `rel_obj_id` (`rel_object_id`)
					) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
				";
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."searchable_objects_new` SELECT * FROM `".$t_prefix."searchable_objects` ORDER BY rel_object_id, column_name;
					RENAME TABLE `".$t_prefix."searchable_objects` TO `".$t_prefix."searchable_objects_old`;
					RENAME TABLE `".$t_prefix."searchable_objects_new` TO `".$t_prefix."searchable_objects`;
					DROP TABLE `".$t_prefix."searchable_objects_old`;
				";*/
			}
			
		}
		
		if (version_compare($installed_version, '3.2-rc') < 0) {
			$upgrade_script .= "
				INSERT INTO ".$t_prefix."max_role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
				 SELECT p.id, o.id, 0, 0
				 FROM `".$t_prefix."object_types` o JOIN `".$t_prefix."permission_groups` p
				 WHERE o.`name` IN ('message','weblink','file','task','milestone','event','contact','mail','timeslot','report','comment','invoice','expense','objective')
				 AND p.`name` IN ('Guest Customer')
				ON DUPLICATE KEY UPDATE role_id=role_id;
				UPDATE ".$t_prefix."max_system_permissions SET can_see_assigned_to_other_tasks=1
				WHERE permission_group_id IN (SELECT id FROM ".$t_prefix."permission_groups WHERE `type`='roles' AND name IN ('Guest Customer'));
				UPDATE ".$t_prefix."system_permissions SET can_see_assigned_to_other_tasks=1
				WHERE permission_group_id IN (SELECT id FROM ".$t_prefix."permission_groups WHERE `type`='roles' AND name IN ('Guest Customer'));
			";
		}
		
		if (version_compare($installed_version, '3.2-rc2') < 0) {
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					('general', 'listingContactsBy', '0', 'BoolConfigHandler', '0', '0', NULL),
					('task panel', 'pushUseWorkingDays', '1', 'BoolConfigHandler', '1', '0', NULL),
					('task panel', 'zoom in gantt', '3', 'IntegerConfigHandler', 1, 0, NULL)
				ON DUPLICATE KEY UPDATE name=name;
			";

			//max_role_object_type_permissions
			$upgrade_script .= "
				DELETE FROM ".$t_prefix."max_role_object_type_permissions 
				WHERE object_type_id IN (
					 SELECT o.id
					 FROM `".$t_prefix."object_types` o 
					 WHERE o.`name` IN ('message','weblink','file','task','milestone','event','contact','timeslot','report','comment','template')				
				 );
				 
				 INSERT INTO ".$t_prefix."max_role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
				 SELECT p.id, o.id, 1, 1
				 FROM `".$t_prefix."object_types` o JOIN `".$t_prefix."permission_groups` p
				 WHERE o.`name` IN ('message','weblink','file','task','milestone','event','contact','timeslot','report','comment','template')
				 AND p.`name` IN ('Super Administrator','Administrator','Manager','Executive');
								 
				INSERT INTO ".$t_prefix."max_role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
				 SELECT p.id, o.id, 0, 1
				 FROM `".$t_prefix."object_types` o JOIN `".$t_prefix."permission_groups` p
				 WHERE o.`name` IN ('message','weblink','file','timeslot','comment','contact','report')
				 AND p.`name` IN ('Collaborator Customer','Internal Collaborator','External Collaborator');
				 
				INSERT INTO ".$t_prefix."max_role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
				 SELECT p.id, o.id, 0, 0
				 FROM `".$t_prefix."object_types` o JOIN `".$t_prefix."permission_groups` p
				 WHERE o.`name` IN ('task','milestone','event')
				 AND p.`name` IN ('Collaborator Customer','Internal Collaborator','External Collaborator');
								 
				INSERT INTO ".$t_prefix."max_role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
				 SELECT p.id, o.id, 0, 0
				 FROM `".$t_prefix."object_types` o JOIN `".$t_prefix."permission_groups` p
				 WHERE o.`name` IN ('message','weblink','file','task','milestone','event','contact','timeslot','report','comment')
				 AND p.`name` IN ('Guest Customer','Guest','Non-Exec Director');
			";
			
			//role_object_type_permissions
			$upgrade_script .= "
				DELETE FROM ".$t_prefix."role_object_type_permissions
				WHERE object_type_id IN (
					SELECT o.id
					FROM `".$t_prefix."object_types` o
					WHERE o.`name` IN ('message','weblink','file','task','milestone','event','contact','timeslot','report','comment','template')
				);

				INSERT INTO ".$t_prefix."role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
				 SELECT p.id, o.id, 1, 1
				 FROM `".$t_prefix."object_types` o JOIN `".$t_prefix."permission_groups` p
				 WHERE o.`name` IN ('message','weblink','file','task','milestone','event','contact','timeslot','report','comment','template')
				 AND p.`name` IN ('Super Administrator','Administrator','Manager')
				ON DUPLICATE KEY UPDATE role_id=role_id;
				 
				INSERT INTO ".$t_prefix."role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
				 SELECT p.id, o.id, 0, 1
				 FROM `".$t_prefix."object_types` o JOIN `".$t_prefix."permission_groups` p
				 WHERE o.`name` IN ('message','weblink','file','task','milestone','event','contact','timeslot','report','comment','template')
				 AND p.`name` IN ('Executive')
				ON DUPLICATE KEY UPDATE role_id=role_id;
				
				INSERT INTO ".$t_prefix."role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
				 SELECT p.id, o.id, 0, 1
				 FROM `".$t_prefix."object_types` o JOIN `".$t_prefix."permission_groups` p
				 WHERE o.`name` IN ('file','timeslot','comment')
				 AND p.`name` IN ('Collaborator Customer','Internal Collaborator')
				ON DUPLICATE KEY UPDATE role_id=role_id;
				
				INSERT INTO ".$t_prefix."role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
				 SELECT p.id, o.id, 0, 0
				 FROM `".$t_prefix."object_types` o JOIN `".$t_prefix."permission_groups` p
				 WHERE o.`name` IN ('task','milestone','event','report','contact')
				 AND p.`name` IN ('Collaborator Customer','Internal Collaborator')
				ON DUPLICATE KEY UPDATE role_id=role_id;
				
				INSERT INTO ".$t_prefix."role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
				 SELECT p.id, o.id, 0, 1
				 FROM `".$t_prefix."object_types` o JOIN `".$t_prefix."permission_groups` p
				 WHERE o.`name` IN ('timeslot','comment')
				 AND p.`name` IN ('External Collaborator')
				ON DUPLICATE KEY UPDATE role_id=role_id;
				
				INSERT INTO ".$t_prefix."role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
				 SELECT p.id, o.id, 0, 0
				 FROM `".$t_prefix."object_types` o JOIN `".$t_prefix."permission_groups` p
				 WHERE o.`name` IN ('task','file','milestone')
				 AND p.`name` IN ('External Collaborator')
				ON DUPLICATE KEY UPDATE role_id=role_id;
				 
				INSERT INTO ".$t_prefix."role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
				 SELECT p.id, o.id, 0, 1
				 FROM `".$t_prefix."object_types` o JOIN `".$t_prefix."permission_groups` p
				 WHERE o.`name` IN ('comment')
				 AND p.`name` IN ('Non-Exec Director','Guest Customer')
				ON DUPLICATE KEY UPDATE role_id=role_id;
			";
			
			//max_system_permissions
			$upgrade_script .= "
				UPDATE `".$t_prefix."max_system_permissions` SET `can_see_assigned_to_other_tasks`=1 WHERE `permission_group_id` IN (
						SELECT id FROM `".$t_prefix."permission_groups` WHERE `type`='roles' AND `name` IN ('External Collaborator','Guest')
					);
			";
			
			//system_permissions
			$upgrade_script .= "
				UPDATE `".$t_prefix."system_permissions` SET `can_update_other_users_invitations`=1 WHERE `permission_group_id` IN (
					SELECT permission_group_id FROM `".$t_prefix."contacts` WHERE `user_type` IN (
						SELECT id FROM `".$t_prefix."permission_groups` WHERE `type`='roles' AND `name` IN ('Manager')
					)
				);
				
				UPDATE `".$t_prefix."system_permissions` SET `can_manage_security`=0 WHERE `permission_group_id` IN (
					SELECT permission_group_id FROM `".$t_prefix."contacts` WHERE `user_type` IN (
						SELECT id FROM `".$t_prefix."permission_groups` WHERE `type`='roles' AND `name` IN ('Executive')
					)
				);
				
				UPDATE `".$t_prefix."system_permissions` SET `can_manage_tasks`=1 WHERE `permission_group_id` IN (
					SELECT permission_group_id FROM `".$t_prefix."contacts` WHERE `user_type` IN (
						SELECT id FROM `".$t_prefix."permission_groups` WHERE `type`='roles' AND `name` IN ('Executive')
					)
				);
				
				UPDATE `".$t_prefix."system_permissions` SET `can_see_assigned_to_other_tasks`=1 WHERE `permission_group_id` IN (
					SELECT permission_group_id FROM `".$t_prefix."contacts` WHERE `user_type` IN (
						SELECT id FROM `".$t_prefix."permission_groups` WHERE `type`='roles' AND `name` IN ('Collaborator Customer')
					)
				);
				
				UPDATE `".$t_prefix."system_permissions` SET `can_see_assigned_to_other_tasks`=0 WHERE `permission_group_id` IN (
					SELECT permission_group_id FROM `".$t_prefix."contacts` WHERE `user_type` IN (
						SELECT id FROM `".$t_prefix."permission_groups` WHERE `type`='roles' AND `name` IN ('Internal Collaborator')
					)
				);
			";
			
			
			// config option to specify mail field where the notification recipients should go
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				 ('general', 'check_unique_mail_contact_comp', '0', 'BoolConfigHandler', 0, 0, NULL),
				 ('mailing', 'notification_recipients_field', 'to', 'MailFieldConfigHandler', '0', '10', NULL)
				ON DUPLICATE KEY UPDATE name=name;
			";
			
			$upgrade_script .= "
				UPDATE ".$t_prefix."tab_panels SET title='settings', icon_cls='ico-administration' WHERE id='more-panel';
			";
			
			if (!$this->checkColumnExists($t_prefix."templates", "can_instance_from_mail", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."templates` ADD COLUMN `can_instance_from_mail` int(1) NOT NULL default '0';
				";
			}
			
			$upgrade_script .= "
				ALTER TABLE `".$t_prefix."member_custom_property_values` ADD INDEX (`member_id`);
			";
		}
		
		if (version_compare($installed_version, '3.2.1-beta') < 0) {
			$upgrade_script .= "
				ALTER TABLE `".$t_prefix."objects` ADD INDEX (`object_type_id`, `trashed_on`, `archived_on`);
			";
			
			$upgrade_script .= "
				INSERT INTO `" . $t_prefix . "contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
				VALUES ('general', 'notify_myself_too', 0, 'BoolConfigHandler', '0', '100', '')
				ON DUPLICATE KEY UPDATE name=name;
			";
			
			$upgrade_script .= "
				DELETE FROM ".$t_prefix."config_options
				WHERE name = 'notify_myself_too';
			";
		}
		
		if (version_compare($installed_version, '3.2.1.1') < 0) {
			if (!$this->checkColumnExists($t_prefix."contacts", "picture_file_small", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `" . $t_prefix . "contacts`
					 ADD COLUMN `picture_file_small` VARCHAR(100) NOT NULL AFTER `picture_file`,
					 ADD COLUMN `picture_file_medium` VARCHAR(100) NOT NULL AFTER `picture_file_small`;
				";
			}	
		}
		
		if (version_compare($installed_version, '3.2.2-alpha') < 0) {
			// config option to specify which address fields are mandatory in case of adding an address
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				 ('general', 'mandatory_address_fields', '', 'AddressFieldsConfigHandler', 0, 0, NULL)
				ON DUPLICATE KEY UPDATE name=name;
			";
		}
		
		if (version_compare($installed_version, '3.2.2-beta') < 0) {
			$upgrade_script .= "
				DELETE FROM ".$t_prefix."role_object_type_permissions
				WHERE object_type_id IN (
					SELECT o.id
					FROM `".$t_prefix."object_types` o
					WHERE o.`name` IN ('comment','template')
				);
				
				DELETE FROM ".$t_prefix."max_role_object_type_permissions 
				WHERE object_type_id IN (
					 SELECT o.id
					 FROM `".$t_prefix."object_types` o 
					 WHERE o.`name` IN ('comment','template')				
				);
				 
				 
				DELETE FROM ".$t_prefix."contact_member_permissions 
				WHERE object_type_id IN (
					 SELECT o.id
					 FROM `".$t_prefix."object_types` o 
					 WHERE o.`name` IN ('comment','template')				
				);
			";
		}
		
		if (version_compare($installed_version, '3.2.3-beta') < 0) {
			$upgrade_script .= "
			INSERT INTO `" . $t_prefix . "contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES 
				('task panel', 'tasksPreviousPendingTasks', '1', 'BoolConfigHandler', 1, 0, ''),
				('task panel', 'can notify subscribers', '1', 'BoolConfigHandler', 0, 0, 'Notification checkbox default value')
					ON DUPLICATE KEY UPDATE id=id;
			";
		}
		
		
		// Execute all queries
		if(!$this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
			$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
			return false;
		}
		$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
		
		
		$this->printMessage('Feng Office has been upgraded. You are now running Feng Office '.$this->getVersionTo().' Enjoy!');

		tpl_assign('additional_steps', $additional_upgrade_steps);

	} // execute
	
} // ButiaUpgradeScript
