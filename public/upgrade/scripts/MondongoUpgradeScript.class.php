<?php

/**
 * Mondongo upgrade script will upgrade FengOffice 2.7.1.1 to FengOffice 3.1
 *
 * @package ScriptUpgrader.scripts
 * @version 1.0
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class MondongoUpgradeScript extends ScriptUpgraderScript {

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
		$this->setVersionFrom('2.7.1.1');
		$this->setVersionTo('3.1');
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
		if (version_compare($installed_version, '3.0-beta') < 0) {
			
			if (!$this->checkColumnExists($t_prefix . "system_permissions", "can_manage_contacts", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."system_permissions` ADD COLUMN `can_manage_contacts` BOOLEAN NOT NULL DEFAULT 0;
				";
			}
			
			$upgrade_script .= "
				INSERT INTO ".$t_prefix."tab_panels (id, title, icon_cls, default_controller, default_action, type, ordering, refresh_on_context_change, initial_controller, initial_action, enabled, plugin_id, object_type_id) VALUES
				('more-panel','getting started','ico-more-tab','more','index','system',100,0,'','',1,0,0)
				ON DUPLICATE KEY UPDATE id=id;
				
				INSERT INTO ".$t_prefix."tab_panel_permissions (permission_group_id, tab_panel_id)
				SELECT c.permission_group_id, 'more-panel' FROM ".$t_prefix."contacts c WHERE c.user_type > 0
				ON DUPLICATE KEY UPDATE tab_panel_id=tab_panel_id;
				
				INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				('system', 'getting_started_step', '99', 'IntegerConfigHandler', '1', '0', NULL)
				ON DUPLICATE KEY UPDATE name=name;
						
				UPDATE `".$t_prefix."config_options` SET `value`= '0'
					WHERE `name`='show_owner_company_name_header';
				
				CREATE TABLE `fixed_sharing_table` (
				  `group_id` INTEGER UNSIGNED NOT NULL,
				  `object_id` INTEGER UNSIGNED NOT NULL,
				  PRIMARY KEY (`group_id`, `object_id`),
				  INDEX `object_id`(`object_id`)
				) ENGINE = InnoDB;
				
				INSERT INTO fixed_sharing_table (group_id, object_id)
				  SELECT group_id, object_id FROM ".$t_prefix."sharing_table
				ON DUPLICATE KEY UPDATE fixed_sharing_table.group_id=fixed_sharing_table.group_id;
				
				DROP TABLE ".$t_prefix."sharing_table;
				RENAME TABLE fixed_sharing_table TO ".$t_prefix."sharing_table;
			";
			
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					('task panel', 'tasksShowPercentCompletedBar', '0', 'BoolConfigHandler', 1, 0, ''),
					('task panel', 'tasksShowTimeEstimates', '1', 'BoolConfigHandler', 1, 0, ''),
					('task panel', 'tasksShowTimePending', '0', 'BoolConfigHandler', 1, 0, ''),
					('task panel', 'tasksShowTimeWorked', '0', 'BoolConfigHandler', 1, 0, ''),
					('task panel', 'tasksShowQuickEdit', '1', 'BoolConfigHandler', 1, 0, ''),
					('task panel', 'tasksShowQuickComplete', '0', 'BoolConfigHandler', 1, 0, ''),
					('task panel', 'tasksShowQuickComment', '0', 'BoolConfigHandler', 1, 0, ''),
					('task panel', 'tasksShowQuickAddSubTasks', '0', 'BoolConfigHandler', 1, 0, ''),
					('task panel', 'tasksShowStartDates', '0', 'BoolConfigHandler', 1, 0, ''),
					('task panel', 'tasksShowEndDates', '1', 'BoolConfigHandler', 1, 0, ''),
					('task panel', 'tasksShowAssignedBy', '0', 'BoolConfigHandler', 1, 0, ''),
					('task panel', 'tasksShowClassification', '1', 'BoolConfigHandler', 1, 0, ''),
					('task panel', 'tasksShowDescriptionOnTimeForms', '1', 'BoolConfigHandler', 0, 0, '')
				ON DUPLICATE KEY UPDATE name=name;

				DELETE FROM ".$t_prefix."config_options WHERE name = 'tasksShowTimeEstimates';
			";
			
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."config_categories` (`name`, `is_system`, `category_order`) VALUES
					('brand_colors', 0, 6)
				ON DUPLICATE KEY UPDATE is_system=0;
				UPDATE `".$t_prefix."config_options` SET is_system=0 WHERE name IN ('brand_colors_head_back', 'brand_colors_head_font');
				UPDATE `".$t_prefix."config_options` SET config_handler_class='ColorPickerConfigHandler' WHERE name IN ('brand_colors_head_back', 'brand_colors_head_font', 'brand_colors_tabs_back', 'brand_colors_tabs_font');
				UPDATE `".$t_prefix."config_options` SET is_system=1 WHERE name IN ('brand_colors_tabs_back', 'brand_colors_tabs_font');
				UPDATE `".$t_prefix."config_options` SET `value`='e7e7e7' WHERE `name`='brand_colors_tabs_back';
				UPDATE `".$t_prefix."config_options` SET `value`='000000' WHERE `name`='brand_colors_tabs_font';
				
				DELETE FROM ".$t_prefix."widgets WHERE name IN ('ws_description', 'summary');
				DELETE FROM ".$t_prefix."contact_widgets WHERE widget_name IN ('ws_description', 'summary');
				
				INSERT INTO `".$t_prefix."widgets` (`name`,`title`,`plugin_id`,`path`,`default_options`,`default_section`,`default_order`,`icon_cls`) VALUES
				 ('active_context_info','active_context_info',0,'','','left',1,'ico-summary')
				ON DUPLICATE KEY UPDATE name=name;
				
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES 
 				 ('general', 'settings_closed', '0', 'BoolConfigHandler', 1, 0, '')
 				ON DUPLICATE KEY UPDATE name=name;
				
				INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				 ('general', 'enabled_dimensions', (SELECT GROUP_CONCAT(id) FROM `".$t_prefix."dimensions` WHERE `code` NOT IN ('feng_persons')), 'RootDimensionsConfigHandler', '1', '0', NULL)
				ON DUPLICATE KEY UPDATE name=name;
				
				update ".$t_prefix."tab_panels set plugin_id=(SELECT id from ".$t_prefix."plugins where name='mail') where id='mails-panel';
			";
			
			if (!$this->checkTableExists($t_prefix."max_system_permissions", $this->database_connection)) {
				$upgrade_script .= "
					CREATE TABLE ".$t_prefix."max_system_permissions LIKE ".$t_prefix."system_permissions;
					
					INSERT INTO `".$t_prefix."max_system_permissions` (`permission_group_id`, `can_manage_security`, `can_manage_configuration`, `can_manage_templates`, `can_manage_time`, `can_add_mail_accounts`, `can_manage_dimensions`, `can_manage_dimension_members`, `can_manage_tasks`, `can_task_assignee`, `can_manage_billing`, `can_view_billing`, `can_see_assigned_to_other_tasks`, `can_manage_contacts`) VALUES
					((SELECT id FROM ".$t_prefix."permission_groups WHERE name = 'Super Administrator'),	1,	1,	1,	1,	1,		1,	1,	1,	1,	1,	1,	1, 1),
					((SELECT id FROM ".$t_prefix."permission_groups WHERE name = 'Administrator'),	1,	1,	1,	1,	1,		1,	1,	1,	1,	1,	1,	1, 1),
					((SELECT id FROM ".$t_prefix."permission_groups WHERE name = 'Manager'),	1,	0,	1,	1,	1,		0,	1,	1,	1,	1,	1,	1, 1),
					((SELECT id FROM ".$t_prefix."permission_groups WHERE name = 'Executive'),	1,	0,	0,	0,	1,		0,	1,	1,	1,	0,	1,	1, 1),
					((SELECT id FROM ".$t_prefix."permission_groups WHERE name = 'Collaborator Customer'),	0,	0,	0,	0,	0,		0,	0,	0,	1,	0,	0,	0, 0),
					((SELECT id FROM ".$t_prefix."permission_groups WHERE name = 'Internal Collaborator'),	0,	0,	0,	0,	0,		0,	0,	0,	1,	0,	0,	1, 0),
					((SELECT id FROM ".$t_prefix."permission_groups WHERE name = 'External Collaborator'),	0,	0,	0,	0,	0,		0,	0,	0,	1,	0,	0,	0, 0),
					((SELECT id FROM ".$t_prefix."permission_groups WHERE name = 'Guest Customer'),	0,	0,	0,	0,	0,		0,	0,	0,	0,	0,	0,	0, 0),
					((SELECT id FROM ".$t_prefix."permission_groups WHERE name = 'Guest'),	0,	0,	0,	0,	0,		0,	0,	0,	0,	0,	0,	0, 0),
					((SELECT id FROM ".$t_prefix."permission_groups WHERE name = 'Non-Exec Director'),	0,	0,	0,	0,	0,		0,	0,	0,	0,	0,	1,	1, 0)
					ON DUPLICATE KEY UPDATE permission_group_id=permission_group_id;
				";
			}
		}
		
		if (version_compare($installed_version, '3.0-rc') < 0) {
			$upgrade_script .= "
				UPDATE ".$t_prefix."role_object_type_permissions SET can_delete=0, can_write=0 WHERE 
					object_type_id IN (SELECT id FROM ".$t_prefix."object_types WHERE name NOT IN ('file','timeslot','comment')) AND 
					role_id IN (SELECT id FROM ".$t_prefix."permission_groups WHERE type='roles' AND name IN ('Internal Collaborator','External Collaborator','Collaborator Customer'));
			";
		}
		
		if (version_compare($installed_version, '3.0') < 0) {
			$upgrade_script .= "
				UPDATE ".$t_prefix."system_permissions SET can_see_assigned_to_other_tasks=0, can_view_billing=0
				WHERE permission_group_id IN (SELECT id FROM ".$t_prefix."permission_groups WHERE name IN ('Collaborator Customer'));
				UPDATE ".$t_prefix."max_system_permissions SET can_see_assigned_to_other_tasks=0, can_view_billing=0
				WHERE permission_group_id IN (SELECT id FROM ".$t_prefix."permission_groups WHERE name IN ('Collaborator Customer'));
				UPDATE ".$t_prefix."system_permissions SET can_see_assigned_to_other_tasks=0, can_view_billing=0
				WHERE permission_group_id IN (SELECT permission_group_id FROM ".$t_prefix."contacts WHERE user_type IN (SELECT id FROM ".$t_prefix."permission_groups WHERE name IN ('Collaborator Customer')));
				
				UPDATE ".$t_prefix."system_permissions SET can_see_assigned_to_other_tasks=0
				WHERE permission_group_id IN (SELECT id FROM ".$t_prefix."permission_groups WHERE name IN ('External Collaborator', 'Guest Customer', 'Guest'));
				UPDATE ".$t_prefix."max_system_permissions SET can_see_assigned_to_other_tasks=0
				WHERE permission_group_id IN (SELECT id FROM ".$t_prefix."permission_groups WHERE name IN ('External Collaborator', 'Guest Customer', 'Guest'));
				UPDATE ".$t_prefix."system_permissions SET can_see_assigned_to_other_tasks=0
				WHERE permission_group_id IN (SELECT permission_group_id FROM ".$t_prefix."contacts WHERE user_type IN (SELECT id FROM ".$t_prefix."permission_groups WHERE name IN ('External Collaborator', 'Guest Customer', 'Guest')));
				
				UPDATE ".$t_prefix."role_object_type_permissions SET can_write = 1 
				WHERE object_type_id = (SELECT id FROM ".$t_prefix."object_types WHERE name='comment') 
					AND role_id IN (SELECT p.id FROM `".$t_prefix."permission_groups` p WHERE p.`name` IN ('Non-Exec Director','Guest Customer'));
				UPDATE ".$t_prefix."role_object_type_permissions SET can_write = 0 
				WHERE object_type_id = (SELECT id FROM ".$t_prefix."object_types WHERE name='comment') 
					AND role_id IN (SELECT p.id FROM `".$t_prefix."permission_groups` p WHERE p.`name` IN ('Guest'));
			";
		}
		
		if (version_compare($installed_version, '3.0.4') < 0) {
			$upgrade_script .= "
				ALTER TABLE `".$t_prefix."project_file_revisions` ADD INDEX (`filesize`);
			";
		}
		
		if (version_compare($installed_version, '3.0.5') < 0) {
			if (!$this->checkColumnExists($t_prefix."system_permissions", "can_update_other_users_invitations", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."system_permissions` ADD COLUMN `can_update_other_users_invitations` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;
					UPDATE ".$t_prefix."system_permissions SET can_update_other_users_invitations=1
					WHERE permission_group_id IN (SELECT id FROM ".$t_prefix."permission_groups WHERE name IN ('Super Administrator', 'Administrator'));
				";
			}
			if (!$this->checkColumnExists($t_prefix."max_system_permissions", "can_update_other_users_invitations", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."max_system_permissions` ADD COLUMN `can_update_other_users_invitations` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;
					UPDATE ".$t_prefix."max_system_permissions SET can_update_other_users_invitations=1
					WHERE permission_group_id IN (SELECT id FROM ".$t_prefix."permission_groups WHERE name IN ('Super Administrator', 'Administrator', 'Manager', 'Executive'));
				";
			}
		}
		
		if (version_compare($installed_version, '3.0.6') < 0) {
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					('calendar panel', 'show_multiple_color_events', '0', 'BoolConfigHandler', 0, 0, '')
				ON DUPLICATE KEY UPDATE name=name;
			";
		}
		
		if (version_compare($installed_version, '3.0.7') < 0) {
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					('task panel', 'tasksShowSubtasksStructure', '1', 'BoolConfigHandler', 1, 0, '')
				ON DUPLICATE KEY UPDATE name=name;
				
				UPDATE `".$t_prefix."project_tasks` SET `assigned_to_contact_id`=0 WHERE `assigned_to_contact_id` IS NULL;
				UPDATE `".$t_prefix."project_tasks` SET `completed_by_id`=0 WHERE `completed_by_id` IS NULL;
				UPDATE `".$t_prefix."project_tasks` SET `milestone_id`=0 WHERE `milestone_id` IS NULL;
				
				UPDATE ".$t_prefix."system_permissions SET can_task_assignee=0 WHERE permission_group_id IN (
				  SELECT c.permission_group_id FROM ".$t_prefix."contacts c WHERE c.user_type IN (SELECT pg.id FROM ".$t_prefix."permission_groups pg WHERE pg.name IN ('Guest', 'Guest Customer', 'Non-Exec Director'))
				);
			";
		}
		
		if (version_compare($installed_version, '3.0.8') < 0) {
			if (!$this->checkColumnExists($t_prefix."object_types", "uses_order", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."object_types` ADD COLUMN `uses_order` INTEGER UNSIGNED NOT NULL DEFAULT 0;
				";
			}
			if (!$this->checkColumnExists($t_prefix."members", "order", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."members` ADD COLUMN `order` INTEGER UNSIGNED NOT NULL DEFAULT 0;
				";
			}
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
	
} // MondongoUpgradeScript
