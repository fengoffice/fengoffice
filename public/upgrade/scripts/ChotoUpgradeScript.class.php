<?php

/**
 * Choto upgrade script will upgrade FengOffice 2.5.1 to FengOffice 2.6.4-beta
 *
 * @package ScriptUpgrader.scripts
 * @version 1.0
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class ChotoUpgradeScript extends ScriptUpgraderScript {

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
	 * Construct the MollejaUpgradeScript
	 *
	 * @param Output $output
	 * @return MollejaUpgradeScript
	 */
	function __construct(Output $output) {
		parent::__construct($output);
		$this->setVersionFrom('2.5.1.5');
		$this->setVersionTo('2.6.4-beta');
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
		if (false && version_compare($installed_version, $this->getVersionFrom()) <= 0 && version_compare($original_version_from, '2.0.0.0-beta') > 0
			 && (!isset($_SESSION['from_feng1']) || !$_SESSION['from_feng1'])) {
			// upgrading from a version lower than this script's 'from' version
			$upgrade_script = tpl_fetch(get_template_path('db_migration/2_6_choto'));
		} else {
			
			if (version_compare($installed_version, '2.6-beta') < 0) {
				
				if (!$this->checkTableExists($t_prefix."member_custom_properties", $this->database_connection)) {
					$upgrade_script .= "
						CREATE TABLE `".$t_prefix."member_custom_properties` (
						  `id` int(10) NOT NULL AUTO_INCREMENT,
						  `object_type_id` int(10) unsigned NOT NULL,
						  `name` varchar(255) ".$default_collation." NOT NULL,
						  `type` varchar(255) ".$default_collation." NOT NULL,
						  `description` text ".$default_collation." NOT NULL,
						  `values` text ".$default_collation." NOT NULL,
						  `default_value` text ".$default_collation." NOT NULL,
						  `is_system` tinyint(1) NOT NULL,
						  `is_required` tinyint(1) NOT NULL,
						  `is_multiple_values` tinyint(1) NOT NULL,
						  `property_order` int(10) NOT NULL,
						  `visible_by_default` tinyint(1) NOT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=InnoDB ".$default_charset.";
						
						CREATE TABLE IF NOT EXISTS `".$t_prefix."member_custom_property_values` (
						  `id` int(10) NOT NULL AUTO_INCREMENT,
						  `member_id` int(10) NOT NULL,
						  `custom_property_id` int(10) NOT NULL,
						  `value` text ".$default_collation." NOT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=InnoDB ".$default_charset.";
					";
				}
				
				if (!$this->checkColumnExists($t_prefix."members", "color", $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `".$t_prefix."members` ADD COLUMN `color` INTEGER UNSIGNED NOT NULL DEFAULT 0;
						UPDATE ".$t_prefix."members SET color=(SELECT color FROM ".$t_prefix."workspaces w WHERE w.object_id=".$t_prefix."members.object_id) 
						WHERE object_type_id=(SELECT id FROM ".$t_prefix."object_types WHERE name='workspace');
					";
				}
				
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."widgets` (`name`,`title`,`plugin_id`,`path`,`default_options`,`default_section`,`default_order`,`icon_cls`) VALUES 
					('active_context_info', 'active context info', 0, '', '', 'left', 0, 'ico-workspace')
					ON DUPLICATE KEY UPDATE name=name;";
				
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."custom_property_values` MODIFY COLUMN `value` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
				";
				
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					('task panel', 'quick_add_task_view_dimensions_combos', '', 'ManageableDimensionsConfigHandler', '0', '0', 'dimensions ids for skip')
					ON DUPLICATE KEY UPDATE `name`=`name`;
					
					INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					('time panel', 'add_timeslot_view_dimensions_combos', '', 'ManageableDimensionsConfigHandler', '0', '0', 'dimensions ids for skip'),
					('task panel', 'show_notify_checkbox_in_quick_add', '0', 'BoolConfigHandler', 0, 0, 'Show notification checkbox in quick add task view')
					ON DUPLICATE KEY UPDATE `name`=`name`;
					
					UPDATE `".$t_prefix."contact_config_categories` 
					 SET is_system = 0
					 WHERE name='time panel';
				";
				
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					 ('general', 'milestone_selector_filter', 'current_and_parents', 'MilestoneSelectorFilterConfigHandler', 0, 0, NULL)
					ON DUPLICATE KEY UPDATE `name`=`name`;
				";
				$upgrade_script .= "
					UPDATE ".$t_prefix."dimension_object_type_contents SET is_multiple=1 
					 WHERE content_object_type_id =(SELECT id FROM ".$t_prefix."object_types WHERE name = 'milestone') 
					 AND dimension_object_type_id IN (SELECT id FROM ".$t_prefix."object_types WHERE name IN ('customer','project','folder','project_folder','customer_folder'));
				";
			}
			
			if (version_compare($installed_version, '2.6-rc') < 0) {
				if (!$this->checkKeyExists($t_prefix."application_logs", "member", $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `".$t_prefix."application_logs` ADD INDEX `member`(`member_id`, `created_on`, `is_silent`);
					";
				}
				
				$upgrade_script .="
					UPDATE `".$t_prefix."config_options` SET `value` = '1' WHERE `name` = 'use tasks dependencies';
				";
			}
			
			if (version_compare($installed_version, '2.6.0.2') < 0) {
				$upgrade_script .= "
				UPDATE `".$t_prefix."contact_config_options`
				SET default_value = ''
				WHERE name='quick_add_task_view_dimensions_combos';
					
				UPDATE `".$t_prefix."contact_config_options`
				SET default_value = ''
				WHERE name='add_timeslot_view_dimensions_combos';
				";
			}
			
			if (version_compare($installed_version, '2.6.1') < 0) {	
				if (!$this->checkColumnExists($t_prefix."custom_properties", "code", $this->database_connection)) {
					$upgrade_script .= "
					ALTER TABLE `".$t_prefix."custom_properties` ADD COLUMN `code` VARCHAR(255) NOT NULL DEFAULT '';
					";
				}
				if (!$this->checkColumnExists($t_prefix."member_custom_properties", "code", $this->database_connection)) {
					$upgrade_script .= "
					ALTER TABLE `".$t_prefix."member_custom_properties` ADD COLUMN `code` VARCHAR(255) NOT NULL DEFAULT '';
					";
				}
			}
			
			if (version_compare($installed_version, '2.6.2-beta') < 0) {
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES 
						('general', 'timeReportShowEstimatedTime', '1', 'BoolConfigHandler', 1, 0, '')
					ON DUPLICATE KEY UPDATE name=name;
				";
			}
			
			if (version_compare($installed_version, '2.6.3-beta') < 0) {
				
				if (!$this->checkTableExists($t_prefix."sharing_table_flags", $this->database_connection)) {
					$upgrade_script .= "
						CREATE TABLE `".$t_prefix."sharing_table_flags` (
						  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
						  `permission_group_id` INTEGER UNSIGNED NOT NULL,
						  `member_id` INTEGER UNSIGNED NOT NULL,
						  `execution_date` DATETIME NOT NULL,
						  `permission_string` TEXT collate utf8_unicode_ci NOT NULL,
						  `created_by_id` INTEGER UNSIGNED NOT NULL,
						  PRIMARY KEY (`id`)
						)
						ENGINE = InnoDB;
					";
				}
				
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."cron_events` (`name`, `recursive`, `delay`, `is_system`, `enabled`, `date`) VALUES
						('check_sharing_table_flags', '1', '10', '1', '1', '0000-00-00 00:00:00')
					ON DUPLICATE KEY UPDATE `name`=`name`;
				";
			}
			
			if (version_compare($installed_version, '2.6.3-rc') < 0) {
			
				if (!$this->checkColumnExists($t_prefix."contact_telephones", "name", $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `".$t_prefix."contact_telephones` ADD COLUMN `name` VARCHAR(256) NOT NULL DEFAULT '';
					";
				}
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES 
						('general', 'can_modify_navigation_panel', '1', 'BoolConfigHandler', 1, 0, '')
					ON DUPLICATE KEY UPDATE name=name;
				";
			}
			
			if(!$this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
				$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
				return false;
			}
			$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
		}
		
		if (version_compare($installed_version, '2.5.0.4') < 0) {
			if (!$this->checkColumnExists("queued_emails", "attachments", $this->database_connection)) {
				$sqls = "
					ALTER TABLE `".$t_prefix."queued_emails` ADD COLUMN `attachments` TEXT;
				";
				$this->executeMultipleQueries($sqls, $t_queries, $e_queries, $this->database_connection);
			}
		}
		
		$this->printMessage('Feng Office has been upgraded. You are now running Feng Office '.$this->getVersionTo().' Enjoy!');

		tpl_assign('additional_steps', $additional_upgrade_steps);

	} // execute
	
} // ChotoUpgradeScript
