<?php

/**
 * Anis upgrade script will upgrade FengOffice 3.2.3 to FengOffice 3.3.0.4
 *
 * @package ScriptUpgrader.scripts
 * @version 1.0
 */
class AnisUpgradeScript extends ScriptUpgraderScript {

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
	 * Construct the AnisUpgradeScript
	 *
	 * @param Output $output
	 * @return AnisUpgradeScript
	 */
	function __construct(Output $output) {
		parent::__construct($output);
		$this->setVersionFrom('3.2.3');
		$this->setVersionTo('3.3.0.4');
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
		if (version_compare($installed_version, '3.3-beta') < 0) {
			if (!$this->checkColumnExists($t_prefix."dimension_object_types", "enabled", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."dimension_object_types`
						ADD COLUMN `enabled` BOOLEAN NOT NULL DEFAULT 1;
				";
			}
			
			$upgrade_script .= "
				CREATE TABLE IF NOT EXISTS `".$t_prefix."dimension_options` (
				  `dimension_id` INTEGER UNSIGNED NOT NULL,
				  `name` VARCHAR(100) NOT NULL,
				  `value` TEXT NOT NULL,
				  PRIMARY KEY (`dimension_id`, `name`)
				) ENGINE=InnoDB;
			";
			
			$upgrade_script .= "
				CREATE TABLE IF NOT EXISTS `".$t_prefix."dimension_object_type_options` (
				  `dimension_id` INTEGER UNSIGNED NOT NULL,
				  `object_type_id` INTEGER UNSIGNED NOT NULL,
				  `name` VARCHAR(100) NOT NULL,
				  `value` TEXT NOT NULL,
				  PRIMARY KEY (`dimension_id`, object_type_id, `name`)
				) ENGINE=InnoDB;
			";
			
			
			if (!$this->checkColumnExists($t_prefix."members", "description", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."members` ADD COLUMN `description` TEXT NOT NULL;
				";
			}
			
			if ($this->checkTableExists($t_prefix."member_custom_properties", $this->database_connection) && !$this->checkColumnExists($t_prefix."member_custom_properties", "is_special", $this->database_connection)) {
				// add columns in member custom properties to specify fixed properties 
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."member_custom_properties`
					 ADD COLUMN `is_special` BOOLEAN NOT NULL,
					 ADD COLUMN `is_disabled` BOOLEAN NOT NULL;
				";
				
				// create special member custom properties for description and color and fill their values with the current description and color values
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."member_custom_properties` (`object_type_id`, `name`, `code`, `type`, `description`, `values`, `default_value`, `is_system`, `is_required`, `is_multiple_values`, `property_order`, `visible_by_default`, `is_special`, `is_disabled`)
					  SELECT mt.id, 'Color', 'color_special','color','','','',0,0,0,30,1, 1, 0
					  FROM ".$t_prefix."object_types mt WHERE mt.`type` IN ('dimension_object','dimension_group')
					ON DUPLICATE KEY UPDATE `code`=`code`;

					INSERT INTO `".$t_prefix."member_custom_properties` (`object_type_id`, `name`, `code`, `type`, `description`, `values`, `default_value`, `is_system`, `is_required`, `is_multiple_values`, `property_order`, `visible_by_default`, `is_special`, `is_disabled`)
					  SELECT mt.id, 'Description', 'description_special', 'memo','','','',0,0,0,31,1, 1, 0
					  FROM ".$t_prefix."object_types mt WHERE mt.`type` IN ('dimension_object','dimension_group')
					ON DUPLICATE KEY UPDATE `code`=`code`;

					insert into ".$t_prefix."member_custom_property_values (`member_id`, `custom_property_id`, `value`)
					  select m.id, (select id from ".$t_prefix."member_custom_properties where code='description_special' and is_special=1 and object_type_id=m.object_type_id), m.description
					  from fo_members m where m.description != ''
					on duplicate key update `value`=description;
					
					insert into ".$t_prefix."member_custom_property_values (`member_id`, `custom_property_id`, `value`)
					  select m.id, (select id from ".$t_prefix."member_custom_properties where code='color_special' and is_special=1 and object_type_id=m.object_type_id), m.color
					  from fo_members m where m.color != ''
					on duplicate key update `value`=color;
				";
			}
			
			$upgrade_script .="
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					('task panel', 'tasksShowDimensionCols', '', 'StringConfigHandler', 1, 0, '')
				ON DUPLICATE KEY UPDATE name=name;
			";
			
			$upgrade_script .= "
				CREATE TABLE IF NOT EXISTS `".$t_prefix."sent_notifications` (
				 `id` int(10) NOT NULL AUTO_INCREMENT,
				 `queued_email_id` int(10) NOT NULL DEFAULT 0,
				 `sent_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				 `to` text COLLATE utf8_unicode_ci,
				 `cc` text COLLATE utf8_unicode_ci,
				 `bcc` text COLLATE utf8_unicode_ci,
				 `from` text COLLATE utf8_unicode_ci,
				 `subject` text COLLATE utf8_unicode_ci,
				 `body` text COLLATE utf8_unicode_ci,
				 `attachments` text COLLATE utf8_unicode_ci,
				 `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				 PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			";
			
			
			$upgrade_script .= "
				UPDATE `".$t_prefix."contact_config_options` SET is_system=1 WHERE `name`='show_notify_checkbox_in_quick_add';
			";
			$upgrade_script .= "
				UPDATE `".$t_prefix."contact_config_options` SET default_value=1 WHERE `name`='can notify from quick add';
			";
		}
		
		
		if (version_compare($installed_version, '3.3-rc') < 0) {
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."cron_events` (`name`, `recursive`, `delay`, `is_system`, `enabled`, `date`) VALUES
				('rebuild_contact_member_cache', '1', '1440', '1', '1', '0000-00-00 00:00:00')
				ON DUPLICATE KEY UPDATE name=name;
			";
			
			$upgrade_script .= "
				UPDATE ".$t_prefix."contact_config_options SET default_value=0 WHERE `name`='attach_to_notification';
			";
		}
		
		if (version_compare($installed_version, '3.3') < 0) {
			$upgrade_script .= "
				DELETE FROM `".$t_prefix."guistate` WHERE `name` = 'contact-manager';
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
	
} // AnisUpgradeScript
