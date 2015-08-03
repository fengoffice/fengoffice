<?php

/**
 * Pamplona upgrade script will upgrade FengOffice 2.1 to FengOffice 2.2.4.1
 *
 * @package ScriptUpgrader.scripts
 * @version 1.0
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class PamplonaUpgradeScript extends ScriptUpgraderScript {

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
	 * Construct the PamplonaUpgradeScript
	 *
	 * @param Output $output
	 * @return PamplonaUpgradeScript
	 */
	function __construct(Output $output) {
		parent::__construct($output);
		$this->setVersionFrom('2.1');
		$this->setVersionTo('2.2.4.1');
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

		$original_version_from = array_var(array_var($_POST, 'form_data'), 'upgrade_from', $installed_version);
		if (version_compare($installed_version, $this->getVersionFrom()) <= 0 && version_compare($original_version_from, '2.0.0.0-beta') > 0
			 && (!isset($_SESSION['from_feng1']) || !$_SESSION['from_feng1'])) {
			// upgrading from a version lower than this script's 'from' version
			$upgrade_script = tpl_fetch(get_template_path('db_migration/2_2_pamplona'));
		} else {
			
			//UPDATE VERSION 2.2-beta
			if (version_compare($installed_version, '2.2-beta') < 0) {
				//ACTIVITY FEED
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."widgets` (`name`, `title`, `plugin_id`, `path`, `default_options`, `default_section`, `default_order`)
						VALUES ('activity_feed', 'activity_feed', 0, '', '', 'left', 0)
					ON DUPLICATE KEY UPDATE name=name;
					INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
						VALUES ('dashboard', 'filters_dashboard', '0,0,10,0', 'StringConfigHandler', '0', '0', 'first position: entry to see the dimension, second position: view timeslot, third position: recent activities to show, fourth position: view views and downloads')
					ON DUPLICATE KEY UPDATE name=name;
				";
				if (!$this->checkColumnExists($t_prefix."contact_config_option_values", "member_id", $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `".$t_prefix."contact_config_option_values` ADD `member_id` INT( 10 ) UNSIGNED NULL DEFAULT '0';
						ALTER TABLE `".$t_prefix."contact_config_option_values` drop PRIMARY KEY;
						ALTER TABLE `".$t_prefix."contact_config_option_values` ADD PRIMARY KEY ( `option_id` , `contact_id` , `member_id` );
					";
				}
				
				// color config options
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."config_categories` (`name`, `is_system`) VALUES ('brand_colors', 1) ON DUPLICATE KEY UPDATE name=name;
					INSERT INTO `".$t_prefix."config_options` (`category_name`,`name`,`value`,`config_handler_class`,`is_system`) VALUES
						('brand_colors', 'brand_colors_head_back', '', 'StringConfigHandler', 1),
						('brand_colors', 'brand_colors_head_font', '', 'StringConfigHandler', 1),
						('brand_colors', 'brand_colors_tabs_back', '', 'StringConfigHandler', 1),
						('brand_colors', 'brand_colors_tabs_font', '', 'StringConfigHandler', 1)
					ON DUPLICATE KEY UPDATE name=name;
				";
				
				//SYNC GOOGLE CALENDARS
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."project_events` CHANGE `special_id` `special_id` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
				";
				//CLASSIFY EMAILS
				if (!$this->checkColumnExists($t_prefix."project_file_revisions", "hash", $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `".$t_prefix."project_file_revisions` ADD `hash` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
					";
				}
				
				//SCRIPT UPDATE OLD NOTES SEARCH BY NAME
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."searchable_objects` (`rel_object_id`, `column_name`, `content`, `contact_id`)
						SELECT id,'name',name,'0' FROM `".$t_prefix."objects` WHERE `object_type_id` = (SELECT id FROM ".$t_prefix."object_types WHERE name='message')
					ON DUPLICATE KEY UPDATE rel_object_id=id,column_name='name';
				";

				//add the contact tab to the database if it does not exist
				if (!$this->checkValueExists($t_prefix."tab_panels", 'id', 'contacts-panel', $this->database_connection)) {
					$upgrade_script .= "
						INSERT INTO `".$t_prefix."tab_panels` (`id`,`title`,`icon_cls`,`refresh_on_context_change`,`default_controller`,`default_action`,`initial_controller`,`initial_action`,`enabled`,`type`,`ordering`,`plugin_id`,`object_type_id`) VALUES 
						 ('contacts-panel','contacts','ico-contacts',1,'contact','init','','',0,'system',7,0, (SELECT id FROM ".$t_prefix."object_types WHERE name='contact'))
						ON DUPLICATE KEY UPDATE name=name;
						INSERT INTO `".$t_prefix."tab_panel_permissions` (`permission_group_id`, `tab_panel_id`) VALUES 
						 ((SELECT id FROM ".$t_prefix."permission_groups WHERE name = 'Super Administrator'),	'contacts-panel'),
						 ((SELECT id FROM ".$t_prefix."permission_groups WHERE name = 'Administrator'), 'contacts-panel'),  
						 ((SELECT id FROM ".$t_prefix."permission_groups WHERE name = 'Manager'), 'contacts-panel'),  
						 ((SELECT id FROM ".$t_prefix."permission_groups WHERE name = 'Executive'), 'contacts-panel'),  
						 ((SELECT id FROM ".$t_prefix."permission_groups WHERE name = 'Collaborator Customer'), 'contacts-panel'),  
						 ((SELECT id FROM ".$t_prefix."permission_groups WHERE name = 'Non-Exec Director'), 'contacts-panel')
						ON DUPLICATE KEY UPDATE tab_panel_id=tab_panel_id;
					";
				}
				
				//change tasks and notes to WYSIWYG text
				$upgrade_script .="
					UPDATE `".$t_prefix."config_options` SET `value` = '1' WHERE `".$t_prefix."config_options`.`name` = 'wysiwyg_messages' OR `".$t_prefix."config_options`.`name` = 'wysiwyg_tasks';
				";
			}
			
			if (version_compare($installed_version, '2.2.0.1') < 0) {
				if (!$this->checkColumnExists($t_prefix."dimensions", 'permission_query_method', $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `".$t_prefix."dimensions` ADD COLUMN `permission_query_method` ENUM('mandatory','not_mandatory') NOT NULL DEFAULT 'mandatory';
					";
				}
			}
			
			if (version_compare($installed_version, '2.2.1-beta') < 0) {
				$upgrade_script .= "
					UPDATE ".$t_prefix."contact_config_options SET default_value='due_date' WHERE name='tasksGroupBy';
					INSERT INTO `".$t_prefix."config_options` (`category_name`,`name`,`value`,`config_handler_class`,`is_system`) VALUES
						('general', 'use_milestones', (SELECT count(*) FROM ".$t_prefix."project_milestones)>0, 'BoolConfigHandler', 0),
						('general', 'show_tab_icons', '1', 'BoolConfigHandler', '0')
					ON DUPLICATE KEY UPDATE name=name;
				";
			}
			
			if (version_compare($installed_version, '2.2.2-beta') < 0 ) {
				if (!$this->checkColumnExists($t_prefix."system_permissions", "can_see_assigned_to_other_tasks", $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `".$t_prefix."system_permissions` ADD COLUMN `can_see_assigned_to_other_tasks` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1;
					";
				}
				$upgrade_script .= "
					UPDATE `".$t_prefix."system_permissions` SET can_see_assigned_to_other_tasks = 1;
					INSERT INTO ".$t_prefix."widgets (name, title, plugin_id, path, default_options, default_section, default_order) VALUES
					 ('completed_tasks_list', 'completed tasks list', 0, '', '', 'right', 150)
					ON DUPLICATE KEY UPDATE name=name;
				";
			}
			
			if (version_compare($installed_version, '2.2.3-beta') < 0 ) {
				if (!$this->checkColumnExists($t_prefix."reports", "ignore_context", $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `".$t_prefix."reports`
						 ADD COLUMN `ignore_context` BOOLEAN NOT NULL DEFAULT 1,
						 ADD INDEX `object_type`(`report_object_type_id`);
					";
				}
			}
			
			if (version_compare($installed_version, '2.2.4.1') < 0 ) {
				if (!$this->checkColumnExists($t_prefix."dimensions", "is_required", $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `".$t_prefix."dimensions` ADD COLUMN `is_required` BOOLEAN NOT NULL DEFAULT 0;
						INSERT INTO `".$t_prefix."contact_config_categories` (`name`, `is_system`, `type`, `category_order`) VALUES
						 ('listing preferences', 0, 0, 10)
						ON DUPLICATE KEY UPDATE name=name;
						INSERT INTO ".$t_prefix."searchable_objects (rel_object_id, column_name, content, contact_id)
						 SELECT id, 'object_id', id, 0 FROM ".$t_prefix."objects
						ON DUPLICATE KEY UPDATE rel_object_id=rel_object_id;
					";
				}
			}
		}
		
		if($this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
			$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
		} else {
			$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
			return false;
		}
		
		if (version_compare($installed_version, '2.2-beta') < 0) {
			// drop brand_colors column and use config options
			if ($this->checkColumnExists($t_prefix."contacts", 'brand_colors', $this->database_connection)) {
				$db_res = mysql_query("SELECT brand_colors FROM ".$t_prefix."contacts WHERE is_company=1 LIMIT 1", $this->database_connection);
				$row = mysql_fetch_assoc($db_res);
				$colors = explode("#", $row['brand_colors']);
				$head_back = (isset($colors[1]) && $colors[1] != "" ? $colors[1] : "000000");
				$tabs_back = (isset($colors[2]) && $colors[2] != "" ? $colors[2] : "14780e");
				$tabs_font = (isset($colors[3]) && $colors[3] != "" ? $colors[3] : "ffffff");
				$head_font = (isset($colors[4]) && $colors[4] != "" ? $colors[4] : "ffffff");
				
				$sqls = "
					UPDATE ".$t_prefix."config_options SET value='$head_back' WHERE name='brand_colors_head_back';
					UPDATE ".$t_prefix."config_options SET value='$head_font' WHERE name='brand_colors_head_font';
					UPDATE ".$t_prefix."config_options SET value='$tabs_back' WHERE name='brand_colors_tabs_back';
					UPDATE ".$t_prefix."config_options SET value='$tabs_font' WHERE name='brand_colors_tabs_font';
					ALTER TABLE ".$t_prefix."contacts DROP COLUMN brand_colors;
				";
				$this->executeMultipleQueries($sqls, $t_queries, $e_queries, $this->database_connection);
			}
		}
		
		$this->printMessage('Feng Office has been upgraded. You are now running Feng Office '.$this->getVersionTo().' Enjoy!');

		tpl_assign('additional_steps', $additional_upgrade_steps);

	} // execute
	
} // MollejaUpgradeScript
