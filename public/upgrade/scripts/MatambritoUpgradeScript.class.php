<?php

/**
 * Matambrito upgrade script will upgrade Feng Office 1.2.1 to Feng Office 1.3.1
 *
 * @package ScriptUpgrader.scripts
 * @version 1.3
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class MatambritoUpgradeScript extends ScriptUpgraderScript {

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
	 * Construct the MatambritoUpgradeScript
	 *
	 * @param Output $output
	 * @return MatambritoUpgradeScript
	 */
	function __construct(Output $output) {
		parent::__construct($output);
		$this->setVersionFrom('1.2.1');
		$this->setVersionTo('1.3.1');
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
		if (version_compare($installed_version, "1.2.1") <= 0) {
			$upgrade_script = tpl_fetch(get_template_path('db_migration/1_3_matambrito'));
		} else {
			// change from es_uy to es_la
			$upgrade_script = "UPDATE `".TABLE_PREFIX."user_ws_config_options` SET `default_value` = 'es_la' WHERE `name` = 'localization' AND `default_value` = 'es_uy';
			UPDATE `".TABLE_PREFIX."user_ws_config_option_values` `v`, `".TABLE_PREFIX."user_ws_config_options` `o` SET `v`.`value` = 'es_la' WHERE `o`.`name` = 'localization' AND `o`.`id` = `v`.`option_id` AND `v`.`value` = 'es_uy';
			ALTER TABLE `".TABLE_PREFIX."users` MODIFY COLUMN `default_billing_id` INTEGER(10) UNSIGNED DEFAULT 0;
			UPDATE `".TABLE_PREFIX."user_ws_config_options` SET
				`config_handler_class` = 'BoolConfigHandler',
				`dev_comment` = 'Notification checkbox default value'
				WHERE `name` = 'can notify from quick add';
			";
		}

		if($this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
			$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
		} else {
			$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
			return false;
		} // if
		@unlink('templates/db_migration/onion.php');
		@unlink('templates/db_migration/dulceDeLeche.php');
		@unlink('templates/db_migration/tortaFrita.php');
		@unlink('templates/db_migration/churro.php');
		@unlink('templates/db_migration/empanada.php');
		@unlink('templates/db_migration/milanga.php');
		@unlink('templates/db_migration/bondiola.php');
		@unlink('templates/db_migration/chinchulin.php');
		@unlink('templates/db_migration/matambrito.php');
		@unlink('scripts/OnionUpgradeScript.class.php');
		@unlink('scripts/PapayaUpgradeScript.class.php');
		@unlink(INSTALLATION_PATH . '/language/es_uy.php');
		@unlink_dir(INSTALLATION_PATH . '/language/es_uy');
		
		$cookiepath = "/";
		$configfile = @file_get_contents(INSTALLATION_PATH . '/config/config.php');
		if ($configfile) {
			$configfile = str_replace("es_uy", "es_la", $configfile);
			$configfile = preg_replace("/[^\\(]*COOKIE_PATH[^,]*,[^\\)]*/", "'COOKIE_PATH', '/'", $configfile);
			@file_put_contents(INSTALLATION_PATH . '/config/config.php', $configfile);
		}

		$this->printMessage('Feng Office has been upgraded. You are now running Feng Office '.$this->getVersionTo().' Enjoy!');
	} // execute
} // MatambritoUpgradeScript

?>