<?php

if (!class_exists('Env', false)) {
	class Env {
		function isDebugging() {
			return false;
		}
		function isDebuggingDB() {
			return false;
		}
		function isDebuggingTime() {
			return false;
		}
	}
}

/**
 * Figazza upgrade script will upgrade Feng Office 1.4.2 to Feng Office 1.5.3
 *
 * @package ScriptUpgrader.scripts
 * @version 1.1
 * @author Ignacio de Soto <ignacio.desoto@fengoffice.com>
 */
class FigazzaUpgradeScript extends ScriptUpgraderScript {

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
	 * Construct the FigazzaUpgradeScript
	 *
	 * @param Output $output
	 * @return FigazzaUpgradeScript
	 */
	function __construct(Output $output) {
		parent::__construct($output);
		$this->setVersionFrom('1.4.2');
		$this->setVersionTo('1.5.3');
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
			$upgrade_script = tpl_fetch(get_template_path('db_migration/1_5_figazza'));
		} else {
			// upgrading from a pre-release of this version (beta, rc, etc)
			$upgrade_script = "";
			if (version_compare($installed_version, "1.5-beta3") < 0) {
				$upgrade_script .= "
				  ALTER TABLE `".TABLE_PREFIX."users` ADD COLUMN `can_manage_time` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;
				  ALTER TABLE `".TABLE_PREFIX."groups` ADD COLUMN `can_manage_time` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;
				";
			}
			if (version_compare($installed_version, "1.5-rc") < 0) {
				$upgrade_script .= "
					INSERT INTO `".TABLE_PREFIX."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
						('mailing', 'smtp_address', '', 'StringConfigHandler', 0, 0, '')
					ON DUPLICATE KEY UPDATE id=id;
				";
			}
			$upgrade_script .= "
				DELETE FROM `".TABLE_PREFIX."cron_events` WHERE `name` = 'backup';
				INSERT INTO `".TABLE_PREFIX."user_ws_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES 
					('mails panel', 'email_polling', '0', 'IntegerConfigHandler', '1', '0', NULL),
					('mails panel', 'show_unread_on_title', '0', 'BoolConfigHandler', '1', '0', NULL)
				ON DUPLICATE KEY UPDATE id=id;
			";
		}
		
		$upgrade_script .= "
			ALTER TABLE `".TABLE_PREFIX."mail_accounts` MODIFY COLUMN `del_from_server` INTEGER NOT NULL default 0;
		";

		if (version_compare($installed_version, '1.4.4') < 0) {
			$upgrade_script .= "
				ALTER TABLE `".TABLE_PREFIX."project_tasks`
				 ADD COLUMN `repeat_end` DATETIME NOT NULL default '0000-00-00 00:00:00',
				 ADD COLUMN `repeat_forever` tinyint(1) NOT NULL,
				 ADD COLUMN `repeat_num` int(10) unsigned NOT NULL default '0',
				 ADD COLUMN `repeat_d` int(10) unsigned NOT NULL,
				 ADD COLUMN `repeat_m` int(10) unsigned NOT NULL,
				 ADD COLUMN `repeat_y` int(10) unsigned NOT NULL,
				 ADD COLUMN `repeat_by` varchar(15) collate utf8_unicode_ci NOT NULL default '';
			";
		}
		
		if (!$this->checkColumnExists(TABLE_PREFIX.'users', 'updated_by_id', $this->database_connection)) {
			$upgrade_script .= "
				ALTER TABLE `".TABLE_PREFIX."users` ADD COLUMN `updated_by_id` int(10) unsigned default NULL;
			";
		}
		if (!$this->checkColumnExists(TABLE_PREFIX.'reports', 'is_order_by_asc', $this->database_connection)) {
			$upgrade_script = "
				ALTER TABLE `".TABLE_PREFIX."reports` ADD COLUMN `is_order_by_asc` TINYINT(1) $default_collation NOT NULL DEFAULT 1;
				$upgrade_script
			";
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
		
		// UPGRADE CUSTOM PROPERTY MULTIPLE VALUES
		if (version_compare($installed_version, $this->getVersionFrom()) <= 0) {
			$res = mysql_query("SELECT * FROM `".TABLE_PREFIX."custom_property_values` WHERE `custom_property_id` IN (SELECT `id` FROM `".TABLE_PREFIX."custom_properties` WHERE `is_multiple_values` = 1)");
			while ($row = mysql_fetch_assoc($res)) {
				$id = $row['id'];
				$cid = $row['custom_property_id'];
				$oid = $row['object_id'];
				$value = $row['value'];
				$values = explode(",", $value);
				$valuestrings = array();
				foreach ($values as $val) {
					$valuestrings[] = "($oid, $cid, '$val')";
				}
				$valuestring = implode(",", $valuestrings);
				mysql_query("INSERT INTO `".TABLE_PREFIX."custom_property_values` (`object_id`, `custom_property_id`, `value`) VALUES $valuestring");
				mysql_query("DELETE FROM `".TABLE_PREFIX."custom_property_values` WHERE `id` = $id");
			}
		}
		
		// UPGRADE PUBLIC FILES
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
				$avatar = $row['avatar_file'];
				$id = $row['id'];
				$path = ROOT . "/public/files/$avatar";
				if (is_file($path)) {
					$fid = FileRepository::addFile($path, array('type' => 'image/png'));
					mysql_query("UPDATE `".TABLE_PREFIX."users` SET `avatar_file` = '$fid' WHERE `id` = $id", $this->database_connection);
					$count++;
				}
			}
			$res = mysql_query("SELECT `id`, `picture_file` FROM `".TABLE_PREFIX."contacts` WHERE `picture_file` <> ''", $this->database_connection);
			while ($row = mysql_fetch_assoc($res)) {
				$picture = $row['picture_file'];
				$id = $row['id'];
				$path = ROOT . "/public/files/$picture";
				if (is_file($path)) {
					$fid = FileRepository::addFile($path, array('type' => 'image/png'));
					mysql_query("UPDATE `".TABLE_PREFIX."contacts` SET `picture_file` = '$fid' WHERE `id` = $id", $this->database_connection);
					$count++;
				}
			}
			$res = mysql_query("SELECT `id`, `logo_file` FROM `".TABLE_PREFIX."companies` WHERE `logo_file` <> ''", $this->database_connection);
			while ($row = mysql_fetch_assoc($res)) {
				$logo = $row['logo_file'];
				$id = $row['id'];
				$path = ROOT . "/public/files/$logo";
				if (is_file($path)) {
					$fid = FileRepository::addFile($path, array('type' => 'image/png'));
					mysql_query("UPDATE `".TABLE_PREFIX."companies` SET `logo_file` = '$fid' WHERE `id` = $id", $this->database_connection);
					$count++;
				}
			}
			$this->printMessage("$count public files migrated to upload directory.");
		}
		
		$this->printMessage('Feng Office has been upgraded. You are now running Feng Office '.$this->getVersionTo().' Enjoy!');
	} // execute
} // FigazzaUpgradeScript

?>