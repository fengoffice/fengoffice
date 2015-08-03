<?php

/**
 * Molleja upgrade script will upgrade FengOffice 2.0.1 to FengOffice 2.1
 *
 * @package ScriptUpgrader.scripts
 * @version 1.0
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class MollejaUpgradeScript extends ScriptUpgraderScript {

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
		$this->setVersionFrom('2.0.1');
		$this->setVersionTo('2.1');
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
			$upgrade_script = tpl_fetch(get_template_path('db_migration/2_1_molleja'));
		} else {
			// UPDATE VERSION 2.1-beta
			if (version_compare($installed_version, '2.1-beta') < 0) {
				if (!$this->checkColumnExists($t_prefix."members", 'archived_on', $this->database_connection)) {
					$upgrade_script .= "ALTER TABLE `".$t_prefix."members`
						 ADD COLUMN `archived_by_id` INTEGER UNSIGNED NOT NULL,
						 ADD COLUMN `archived_on` DATETIME NOT NULL,
						 ADD INDEX `archived_on`(`archived_on`);";
				}
				
				// INDEXED ODT AND FODT
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."file_types` (`id` ,`extension` ,`icon` ,`is_searchable` ,`is_image`) VALUES
					 ('34', 'odt', 'doc.png', '1', '0'), ('35', 'fodt', 'doc.png', '1', '0')
					ON DUPLICATE KEY UPDATE id=id;";
			}

			// UPDATE VERSION 2.1-rc
			if (version_compare($installed_version, '2.1-rc') < 0) {
				//TYPES IN PERMISSION GROUPS
				if (!$this->checkColumnExists($t_prefix."permission_groups", 'type', $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE  `".$t_prefix."permission_groups` ADD `type` ENUM(  'roles',  'permission_groups',  'user_groups' ) NOT NULL;";
				}
				$upgrade_script .= "
					UPDATE `".$t_prefix."permission_groups` SET `type` = 'roles' WHERE `id` <= 13;
					UPDATE `".$t_prefix."permission_groups` SET `type` = 'permission_groups' WHERE `contact_id` > 0;
					UPDATE `".$t_prefix."permission_groups` SET `type` = 'user_groups' WHERE `contact_id` = 0 AND `id` > 13;";
			}

			//UPDATE VERSION 2.1
			if (version_compare($installed_version, '2.1') < 0) {
				// FILE EXTENSION PREVENTION UPLOADING
				if (!$this->checkColumnExists($t_prefix."file_types", 'is_allow', $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `".$t_prefix."file_types` ADD COLUMN `is_allow` TINYINT(1) NOT NULL DEFAULT '1';";
				}
				
				//CLASIFFY EVENTS
				if (!$this->checkColumnExists($t_prefix."external_calendar_users", 'related_to', $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `".$t_prefix."external_calendar_users` ADD `related_to` VARCHAR( 255 ) NOT NULL;";
				}
				//PERFORMANCE SYNC EVENTS
				if (!$this->checkColumnExists($t_prefix."project_events", 'update_sync', $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `".$t_prefix."project_events` ADD `update_sync` DATETIME NOT NULL AFTER `special_id`;";
				}
			}
		}
		
		if($this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
			$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
		} else {
			$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
			return false;
		}

		$this->printMessage('Feng Office has been upgraded. You are now running Feng Office '.$this->getVersionTo().' Enjoy!');

		tpl_assign('additional_steps', $additional_upgrade_steps);

	} // execute
	
} // MollejaUpgradeScript
