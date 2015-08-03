<?php

/**
 * Nutria upgrade script will upgrade Feng Office 1.3.1 to Feng Office 1.4.2
 *
 * @package ScriptUpgrader.scripts
 * @version 1.4
 * @author Carlos Palma <chonwil@gmail.com>
 */
class NutriaUpgradeScript extends ScriptUpgraderScript {

	/**
	 * Array of files and folders that need to be writable
	 *
	 * @var array
	 */
	private $check_is_writable = array(
		'/config/config.php',
		'/config',
		'/public/files',
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
	 * Construct the NutriaUpgradeScript
	 *
	 * @param Output $output
	 * @return NutriaUpgradeScript
	 */
	function __construct(Output $output) {
		parent::__construct($output);
		$this->setVersionFrom('1.3.1');
		$this->setVersionTo('1.4.2');
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

		tpl_assign('table_prefix', TABLE_PREFIX);
		if (defined('DB_ENGINE'))
		tpl_assign('engine', DB_ENGINE);
		else
		tpl_assign('engine', 'InnoDB');

		// ---------------------------------------------------
		//  Execute migration
		// ---------------------------------------------------

		$total_queries = 0;
		$executed_queries = 0;
		$installed_version = installed_version();
		if (version_compare($installed_version, $this->getVersionFrom()) <= 0) {
			// upgrading from a version lower than this script's 'from' version
			$upgrade_script = tpl_fetch(get_template_path('db_migration/1_4_nutria'));
		} else {
			// upgrading from a pre-release of this version (beta, rc, etc)
			$upgrade_script = "";
			if (version_compare($installed_version, '1.4-beta') == 0) {
				$upgrade_script .= "
				INSERT INTO `".TABLE_PREFIX."user_ws_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					('task panel', 'noOfTasks', '8', 'IntegerConfigHandler', '0', '100', NULL),
					('calendar panel', 'start_monday', '', 'BoolConfigHandler', 0, 0, ''),
					('calendar panel', 'show_week_numbers', '', 'BoolConfigHandler', 0, 0, ''),
					('context help', 'show_reporting_panel_context_help', '1', 'BoolConfigHandler', '1', '0', NULL)
				ON DUPLICATE KEY UPDATE id=id;
				UPDATE `".TABLE_PREFIX."user_ws_config_options`
					SET `is_system` = 0 WHERE `name` IN ('start_monday', 'show_week_numbers');
				UPDATE `".TABLE_PREFIX."user_ws_config_categories`
					SET `is_system` = 0 WHERE `name` = 'calendar panel';
				INSERT INTO `".TABLE_PREFIX."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					('general', 'show_feed_links', '0', 'BoolConfigHandler', '0', '0', NULL),
					('mailing', 'user_email_fetch_count', '10', 'IntegerConfigHandler', 0, 0, 'How many emails to fetch when checking for email')
				ON DUPLICATE KEY UPDATE id=id;
				ALTER TABLE `".TABLE_PREFIX."custom_properties` ADD COLUMN `visible_by_default` TINYINT(1) NOT NULL DEFAULT 0 AFTER `property_order`;
				ALTER TABLE `".TABLE_PREFIX."custom_property_values` MODIFY COLUMN `value` text $default_collation NOT NULL;
				-- larger contact fields
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `firstname` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `lastname` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `middlename` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `department` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `job_title` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `w_city` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `w_state` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `w_zipcode` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `w_country` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `w_phone_number` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `w_phone_number2` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `w_fax_number` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `w_assistant_number` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `w_callback_number` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `h_city` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `h_state` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `h_zipcode` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `h_country` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `h_phone_number` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `h_phone_number2` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `h_fax_number` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `h_mobile_number` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `h_pager_number` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `o_city` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `o_state` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `o_zipcode` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `o_country` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `o_phone_number` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `o_phone_number2` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."contacts` MODIFY COLUMN `o_fax_number` varchar(50) $default_collation default NULL;
				
				ALTER TABLE `".TABLE_PREFIX."companies` MODIFY COLUMN `phone_number` varchar(50) $default_collation default NULL;
				ALTER TABLE `".TABLE_PREFIX."companies` MODIFY COLUMN `fax_number` varchar(50) $default_collation default NULL;
				";
			}
			if (version_compare($installed_version, '1.4-rc') <= 0) {
				$upgrade_script .= "
				INSERT INTO `".TABLE_PREFIX."user_ws_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				 ('context help', 'show_administration_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_member_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_contact_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_company_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_upload_file_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_upload_file_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_upload_file_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_upload_file_description_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_upload_file_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_upload_file_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_upload_file_linked_objects_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_note_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_note_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_note_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_note_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_note_subscribers_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_note_linked_object_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_milestone_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_milestone_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_milestone_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_milestone_description_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_milestone_reminders_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_milestone_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_milestone_linked_object_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_milestone_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_print_report_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_task_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_task_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_task_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_task_reminders_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_task_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_task_linked_objects_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_task_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_list_task_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_time_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_webpage_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_webpage_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_webpage_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_webpage_description_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_webpage_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_webpage_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_webpage_linked_objects_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('dashboard', 'show calendar widget', '1', 'BoolConfigHandler', 0, 80, ''),
				 ('dashboard', 'show late tasks and milestones widget', '1', 'BoolConfigHandler', 0, 100, ''),
				 ('dashboard', 'show pending tasks widget', '1', 'BoolConfigHandler', 0, 200, ''),
				 ('dashboard', 'pending tasks widget assigned to filter', '0:0', 'UserCompanyConfigHandler', 0, 210, ''),
				 ('dashboard', 'show emails widget', '1', 'BoolConfigHandler', 0, 300, ''),
				 ('dashboard', 'show messages widget', '1', 'BoolConfigHandler', 0, 400, ''),
				 ('dashboard', 'show documents widget', '1', 'BoolConfigHandler', 0, 500, ''),
				 ('dashboard', 'show charts widget', '1', 'BoolConfigHandler', 0, 600, ''),
				 ('dashboard', 'show tasks in progress widget', '1', 'BoolConfigHandler', 0, 700, ''),
				 ('dashboard', 'show comments widget', '1', 'BoolConfigHandler', 0, 800, ''),
				 ('dashboard', 'show dashboard info widget', '1', 'BoolConfigHandler', 0, 900, ''),
				 ('dashboard', 'always show unread mail in dashboard', '0', 'BoolConfigHandler', 0, 10, 'when false, active workspace email is shown'),
				 ('dashboard', 'calendar_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
				 ('dashboard', 'emails_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
				 ('dashboard', 'messages_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
				 ('dashboard', 'active_tasks_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
				 ('dashboard', 'pending_tasks_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
				 ('dashboard', 'late_tasks_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
				 ('dashboard', 'comments_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
				 ('dashboard', 'documents_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
				 ('dashboard', 'charts_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
				 ('dashboard', 'dashboard_info_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
				 ('task panel', 'can notify from quick add', '1', 'BoolConfigHandler', 0, 0, ''),
				 ('task panel', 'tasksShowWorkspaces', '1', 'BoolConfigHandler', 1, 0, ''),
				 ('task panel', 'tasksShowTime', '1', 'BoolConfigHandler', 1, 0, ''),
				 ('task panel', 'tasksShowDates', '1', 'BoolConfigHandler', 1, 0, ''),
				 ('task panel', 'tasksShowTags', '1', 'BoolConfigHandler', 1, 0, ''),
				 ('task panel', 'tasksGroupBy', 'milestone', 'StringConfigHandler', 1, 0, ''),
				 ('task panel', 'tasksOrderBy', 'priority', 'StringConfigHandler', 1, 0, ''),
				 ('task panel', 'task panel status', '1', 'IntegerConfigHandler', 1, 0, ''),
				 ('task panel', 'task panel filter', 'assigned_to', 'StringConfigHandler', 1, 0, ''),
				 ('task panel', 'task panel filter value', '0:0', 'UserCompanyConfigHandler', 1, 0, ''),
				 ('time panel', 'TM show time type', '0', 'IntegerConfigHandler', 1, 0, ''),
				 ('time panel', 'TM report show time type', '0', 'IntegerConfigHandler', 1, 0, ''),
				 ('time panel', 'TM user filter', '0', 'IntegerConfigHandler', 1, 0, ''),
				 ('time panel', 'TM tasks user filter', '0', 'IntegerConfigHandler', 1, 0, ''),
				 ('general', 'localization', '', 'LocalizationConfigHandler', 0, 100, ''),
				 ('general', 'initialWorkspace', '0', 'InitialWorkspaceConfigHandler', 0, 200, ''),
				 ('general', 'lastAccessedWorkspace', '0', 'IntegerConfigHandler', 1, 0, ''),
				 ('general', 'rememberGUIState', '0', 'BoolConfigHandler', 0, 300, ''),
				 ('general', 'work_day_start_time', '9:00', 'TimeConfigHandler', 0, 400, 'Work day start time'),
				 ('general', 'time_format_use_24', '0', 'BoolConfigHandler', 0, 500, 'Use 24 hours time format'),
				 ('general', 'date_format', 'd/m/Y', 'StringConfigHandler', 0, 600, 'Date objects will be displayed using this format.'),
				 ('general', 'descriptive_date_format', 'l, j F', 'StringConfigHandler', 0, 700, 'Descriptive dates will be displayed using this format.'),
				 ('calendar panel', 'calendar view type', 'viewweek', 'StringConfigHandler', 1, 0, ''),
				 ('calendar panel', 'calendar user filter', '0', 'IntegerConfigHandler', 1, 0, ''),
				 ('calendar panel', 'calendar status filter', '', 'StringConfigHandler', 1, 0, ''),
				 ('calendar panel', 'start_monday', '', 'BoolConfigHandler', 0, 0, ''),
				 ('calendar panel', 'show_week_numbers', '', 'BoolConfigHandler', 0, 0, ''),
				 ('dashboard', 'show getting started widget', '1', 'BoolConfigHandler', '0', '1000', NULL),
				 ('dashboard', 'getting_started_widget_expanded', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_tasks_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_account_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_active_tasks_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_general_timeslots_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_late_tasks_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_pending_tasks_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_documents_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_active_tasks_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_calendar_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_messages_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_dashboard_info_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_comments_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_emails_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_reporting_panel_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('context help', 'show_add_file_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
				 ('general', 'custom_report_tab', 'tasks', 'StringConfigHandler', '1', '0', NULL),
				 ('general', 'show_context_help', 'until_close', 'ShowContextHelpConfigHandler', '0', '0', NULL),
				 ('task panel', 'noOfTasks', '8', 'IntegerConfigHandler', '0', '100', NULL)
				 ON DUPLICATE KEY UPDATE id=id;
				UPDATE `".TABLE_PREFIX."user_ws_config_options`
					SET `is_system` = 1, `category_name` = 'context help' WHERE `name` = 'show_tasks_context_help';
				DELETE FROM `".TABLE_PREFIX."config_options` where name='upgrade_check_enabled';
				";
			}
			if (!$this->checkColumnExists(TABLE_PREFIX.'reports', 'is_order_by_asc', $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".TABLE_PREFIX."reports` ADD COLUMN `is_order_by_asc` TINYINT(1) $default_collation NOT NULL DEFAULT 1;
				";
			}
		}

		$upgrade_script .= "
			UPDATE `".TABLE_PREFIX."user_ws_config_categories` SET `is_system` = 0 WHERE `name` = 'calendar_panel';
		";
		
		if($this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
			$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
		} else {
			$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
			return false;
		} // if
		
		// ---------------------------------------------------
		//  Add SEED to config file
		// ---------------------------------------------------
		
		$config_file = INSTALLATION_PATH . '/config/config.php';
		$config_lines = file($config_file);
		$new_config = array();
		// Check SEED definition existence
		$add_seed = true;
		foreach($config_lines as $line){
			if (str_starts_with(trim($line), "define('SEED'")) {
				$add_seed = false;
				break;
			}
		}
		foreach($config_lines as $line){
			$new_config[] = $line;
			if($add_seed && trim($line) == "<?php"){
				$new_config[] = "  define('SEED', '".DB_USER.DB_PASS.rand(0,10000000000)."');\n";
			}
		} 
		if (!$add_seed) { //remove repeated SEED definitions
			$i = 0;
			$lines_to_remove = array();
			while ($i < count($config_lines)) {
				$line = $new_config[$i];
				if (str_starts_with(trim($line), "define('SEED'")) {
					$lines_to_remove[] = $i;
				}
				$i++;
			}
			// remove SEED lines except the last one
			unset($lines_to_remove[count($lines_to_remove) - 1]);
			foreach($lines_to_remove as $index) {
				$new_config[$index] = '';
			}
		}
		$new_content = join('', $new_config);
		$fp = fopen($config_file, 'w');
		fwrite($fp, $new_content); 

		$this->printMessage('Feng Office has been upgraded. You are now running Feng Office '.$this->getVersionTo().' Enjoy!');
	} // execute
} // NutriaUpgradeScript

?>