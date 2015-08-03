<?php

/**
 * Provolone upgrade script will upgrade FengOffice 2.6.4-beta to FengOffice 2.7.1.10
 *
 * @package ScriptUpgrader.scripts
 * @version 1.0
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class ProvoloneUpgradeScript extends ScriptUpgraderScript {

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
		$this->setVersionFrom('2.6.4-beta');
		$this->setVersionTo('2.7.1.10');
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
			$upgrade_script = tpl_fetch(get_template_path('db_migration/2_7_provolone'));
		} else {
			
			if (version_compare($installed_version, '2.7-beta') < 0) {
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
						('mails panel', 'view_mail_attachs_expanded', '1', 'BoolConfigHandler', 0, 0, ''),
						('mails panel', 'auto_classify_attachments', '1', 'BoolConfigHandler', 0, 0, ''),
						('calendar panel', 'show_birthdays_in_calendar', '1', 'BoolConfigHandler', 1, 0, '')
					ON DUPLICATE KEY UPDATE name=name;
				";
				$upgrade_script .= "
					delete from ".$t_prefix."contact_widgets where widget_name='active_context_info';
					delete from ".$t_prefix."widgets where name='active_context_info';
				";
				
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
						('general', 'give_member_permissions_to_new_users', '', 'UserTypeMultipleConfigHandler', '0', '0', NULL),
						('general', 'show_owner_company_name_header', 1, 'BoolConfigHandler', '0', '0', NULL)
					ON DUPLICATE KEY UPDATE name=name;
					UPDATE `".$t_prefix."config_options` SET `value`=(
						SELECT GROUP_CONCAT(id) FROM ".$t_prefix."permission_groups WHERE `name` IN ('Super Administrator', 'Administrator')
					)
					WHERE `name`='give_member_permissions_to_new_users';
				";
				
				if (!$this->checkColumnExists($t_prefix."sharing_table_flags", "object_id", $this->database_connection)) {
					$upgrade_script .= "ALTER TABLE `".$t_prefix."sharing_table_flags` ADD COLUMN `object_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;";
				}
				
				if (!$this->checkTableExists($t_prefix."contact_member_cache", $this->database_connection)) {
					$upgrade_script .= "
					CREATE TABLE `".$t_prefix."contact_member_cache` (
					`contact_id` int(10) UNSIGNED NOT NULL,
					`member_id` int(10) UNSIGNED NOT NULL,
					`parent_member_id` int(10) UNSIGNED NOT NULL default '0',
					`last_activity` DATETIME NOT NULL default '0000-00-00 00:00:00',
					PRIMARY KEY  (`contact_id` , `member_id`),
					KEY `by_contact` USING HASH (`contact_id`),
					KEY `by_parent` USING HASH (`parent_member_id`),
					KEY `last_activity` (`last_activity`)
					)
					ENGINE=InnoDB ".$default_charset.";
					";
				}
				
							
				
				
			}
			
			if (version_compare($installed_version, '2.7.1.8') < 0) {
				$upgrade_script .= "
					DELETE FROM ".$t_prefix."widgets WHERE name IN ('ws_description', 'summary');
					DELETE FROM ".$t_prefix."contact_widgets WHERE widget_name IN ('ws_description', 'summary');
					INSERT INTO `".$t_prefix."widgets` (`name`,`title`,`plugin_id`,`path`,`default_options`,`default_section`,`default_order`,`icon_cls`) VALUES
					('active_context_info','active_context_info',0,'','','left',1,'ico-summary')
					ON DUPLICATE KEY UPDATE name=name;
				";
			}
			
			if (version_compare($installed_version, '2.7.1.9') < 0) {
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
						('general', 'inherit_permissions_from_parent_member', 1, 'BoolConfigHandler', '0', '0', NULL)
					ON DUPLICATE KEY UPDATE name=name;
				";
			}
			
			if(!$this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
				$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
				return false;
			}
			$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
		}
		
		$this->printMessage('Feng Office has been upgraded. You are now running Feng Office '.$this->getVersionTo().' Enjoy!');

		tpl_assign('additional_steps', $additional_upgrade_steps);

	} // execute
	
} // ProvoloneUpgradeScript
