<?php

/**
 * Chivito upgrade script will upgrade Feng Office 1.5 to Feng Office 1.6.2
 *
 * @package ScriptUpgrader.scripts
 * @version 1.1
 * @author Ignacio de Soto <ignacio.desoto@fengoffice.com>
 */
class ChivitoUpgradeScript extends ScriptUpgraderScript {

	/**
	 * Array of files and folders that need to be writable
	 *
	 * @var array
	 */
	private $check_is_writable = array(
		'/config/config.php',
		'/config/installed_version.php',
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
	 * Construct the ChivitoUpgradeScript
	 *
	 * @param Output $output
	 * @return ChivitoUpgradeScript
	 */
	function __construct(Output $output) {
		parent::__construct($output);
		$this->setVersionFrom('1.5.3');
		$this->setVersionTo('1.6.2');
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
		
		// RUN QUERIES
		$total_queries = 0;
		$executed_queries = 0;
		$installed_version = installed_version();
		if (version_compare($installed_version, $this->getVersionFrom()) <= 0) {
			// upgrading from a version lower than this script's 'from' version
			$upgrade_script = tpl_fetch(get_template_path('db_migration/1_6_chivito'));
		} else {
			// upgrading from a pre-release of this version (beta, rc, etc)
			$upgrade_script = "";
			if (version_compare($installed_version, "1.6-beta2") < 0) {
				$upgrade_script .= "
					INSERT INTO `".TABLE_PREFIX."user_ws_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
						('general', 'search_engine', 'match', 'SearchEngineConfigHandler', 0, 700, ''),
					 	('mails panel', 'mails account filter', '', 'StringConfigHandler', '1', '0', NULL),
						('mails panel', 'mails classification filter', 'all', 'StringConfigHandler', '1', '0', NULL),
						('mails panel', 'mails read filter', 'all', 'StringConfigHandler', '1', '0', NULL),
		 				('dashboard', 'show_two_weeks_calendar', '1', 'BoolConfigHandler', '0', '0', NULL)
		 			ON DUPLICATE KEY UPDATE id=id;
		 			UPDATE `".TABLE_PREFIX."user_ws_config_options` SET `category_name` = 'general' WHERE `name` = 'work_day_start_time';
		 			ALTER TABLE `".TABLE_PREFIX."mail_contents` ADD INDEX `in_reply_to_id` (`in_reply_to_id`);
				";
			}
			if (version_compare($installed_version, "1.6-beta3") < 0) {
				$upgrade_script .= "
		 			ALTER TABLE `".TABLE_PREFIX."mail_contents`
		 			  ADD COLUMN `received_date` datetime NOT NULL default '0000-00-00 00:00:00',
		 			  ADD INDEX `received_date` (`received_date`);
		 			UPDATE `".TABLE_PREFIX."mail_contents` SET `received_date` = `sent_date`;
		 			UPDATE `".TABLE_PREFIX."user_ws_config_options` SET `default_value` = '1' WHERE `name` = 'autodetect_time_zone';
				";
			}
			if (version_compare($installed_version, "1.6-rc") < 0) {
				$upgrade_script .= "
					INSERT INTO `".TABLE_PREFIX."cron_events` (`name`, `recursive`, `delay`, `is_system`, `enabled`, `date`) VALUES
						('clear_tmp_folder', '1', '1440', '1', '1', '0000-00-00 00:00:00')
					ON DUPLICATE KEY UPDATE id=id;
					ALTER TABLE `".TABLE_PREFIX."mail_contents`
						MODIFY COLUMN `message_id` varchar(255) $default_collation NOT NULL COMMENT 'Message-Id header',
						MODIFY COLUMN `in_reply_to_id` varchar(255) $default_collation NOT NULL COMMENT 'Message-Id header of the previous email in the conversation',
						MODIFY COLUMN `uid` varchar(255) $default_collation NOT NULL default '';
					INSERT INTO `".TABLE_PREFIX."user_ws_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
						('mails panel', 'hide_quoted_text_in_emails', '1', 'BoolConfigHandler', 0, 110, NULL)
		 			ON DUPLICATE KEY UPDATE id=id;
				";
			}
			if (version_compare($installed_version, "1.6") < 0) {
				$upgrade_script .= "
		 			ALTER TABLE `".TABLE_PREFIX."mail_contents`
		 			  ADD INDEX `state` (`state`);
				";
			}
			if (version_compare($installed_version, "1.6.1") < 0) {
				$upgrade_script .= "
					INSERT INTO `".TABLE_PREFIX."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					  ('general', 'detect_mime_type_from_extension', '0', 'BoolConfigHandler', '0', '0', NULL)
					ON DUPLICATE KEY UPDATE id=id;
					UPDATE `".TABLE_PREFIX."user_ws_config_options` SET `config_handler_class` = 'RememberGUIConfigHandler' WHERE `name` = 'rememberGUIState';
				";
			}
			if (version_compare($installed_version, "1.6.2") < 0) {
				$upgrade_script .= "
					INSERT INTO `".TABLE_PREFIX."user_ws_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					  ('task panel', 'task_display_limit', '500', 'IntegerConfigHandler', '0', '200', NULL)
					ON DUPLICATE KEY UPDATE id=id;
				";
			}
		}
		
		// rename gelsheet tables before upgrading if name is wrong and if engine is case sensitive
		if ($this->checkTableExists(TABLE_PREFIX.'gs_fontStyles', $this->database_connection) && !$this->checkTableExists(TABLE_PREFIX.'gs_fontstyles', $this->database_connection)) {
			$upgrade_script = "
				RENAME TABLE `" . TABLE_PREFIX . "gs_fontStyles` TO `" . TABLE_PREFIX . "gs_fontstyles`;
			" . $upgrade_script;
		}
		if ($this->checkTableExists(TABLE_PREFIX.'gs_mergedCells', $this->database_connection) && !$this->checkTableExists(TABLE_PREFIX.'gs_mergedcells', $this->database_connection)) {
			$upgrade_script = "
				RENAME TABLE `" . TABLE_PREFIX . "gs_mergedCells` TO `" . TABLE_PREFIX . "gs_mergedcells`;
			" . $upgrade_script;
		}

		if($this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
			$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
		} else {
			$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
			return false;
		} // if
		
		
		// UPGRADE PUBLIC FILES (mark as public)
		if (version_compare($installed_version, $this->getVersionFrom()) <= 0) {
			// load FileRepository classes
			include_once ROOT . "/environment/library/database/adapters/AbstractDBAdapter.class.php";
			include_once ROOT . "/environment/library/database/DB.class.php";
			include_once ROOT . "/environment/library/database/DBResult.class.php";
			include_once ROOT . "/environment/classes/Inflector.class.php";
			include_once ROOT . "/library/filerepository/FileRepository.class.php";
			include_once ROOT . "/library/filerepository/errors/FileNotInRepositoryError.class.php";
			include_once ROOT . "/library/filerepository/errors/FileRepositoryAddError.class.php";
			include_once ROOT . "/library/filerepository/errors/FileRepositoryDeleteError.class.php";
			include_once ROOT . "/library/filerepository/backend/FileRepository_Backend.class.php";
			DB::connect(DB_ADAPTER, array(
				'host'    => DB_HOST,
				'user'    => DB_USER,
				'pass'    => DB_PASS,
				'name'    => DB_NAME,
				'persist' => DB_PERSIST
			)); // connect
			if(defined('DB_CHARSET') && trim(DB_CHARSET)) {
				DB::execute("SET NAMES ?", DB_CHARSET);
			} // if
			$res = mysql_query("SELECT `value` FROM `".TABLE_PREFIX."config_options` WHERE `name` = 'file_storage_adapter'");
			$row = mysql_fetch_assoc($res);
			$adapter = $row['value'];
			if ($adapter == 'mysql') {
				include_once ROOT . "/library/filerepository/backend/FileRepository_Backend_DB.class.php";
				FileRepository::setBackend(new FileRepository_Backend_DB(TABLE_PREFIX));
			} else {
				include_once ROOT . "/library/filerepository/backend/FileRepository_Backend_FileSystem.class.php";
				FileRepository::setBackend(new FileRepository_Backend_FileSystem(ROOT . "/upload", TABLE_PREFIX));
			}
			$res = mysql_query("SELECT `id`, `avatar_file` FROM `".TABLE_PREFIX."users` WHERE `avatar_file` <> ''", $this->database_connection);
			$count = 0;
			while ($row = mysql_fetch_assoc($res)) {
				$fid = $row['avatar_file'];
				FileRepository::setFileAttribute($fid, 'public', true);
				$count++;
			}
			$res = mysql_query("SELECT `id`, `picture_file` FROM `".TABLE_PREFIX."contacts` WHERE `picture_file` <> ''", $this->database_connection);
			while ($row = mysql_fetch_assoc($res)) {
				$fid = $row['picture_file'];
				FileRepository::setFileAttribute($fid, 'public', true);
				$count++;
			}
			$res = mysql_query("SELECT `id`, `logo_file` FROM `".TABLE_PREFIX."companies` WHERE `logo_file` <> ''", $this->database_connection);
			while ($row = mysql_fetch_assoc($res)) {
				$fid = $row['logo_file'];
				FileRepository::setFileAttribute($fid, 'public', true);
				$count++;
			}
			$this->printMessage("$count public files updated.");
		}

		$this->printMessage('Feng Office has been upgraded. You are now running Feng Office '.$this->getVersionTo().' Enjoy!');
	} // execute
} // ChivitoUpgradeScript

?>