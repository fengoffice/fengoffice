<?php

/**
 * Pastafrola upgrade script will upgrade FengOffice 1.6 to FengOffice 1.7.3.3
 *
 * @package ScriptUpgrader.scripts
 * @version 1.1
 * @author Ignacio de Soto <ignacio.desoto@opengoo.org>
 */
class PastafrolaUpgradeScript extends ScriptUpgraderScript {

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
	 * Construct the PastafrolaUpgradeScript
	 *
	 * @param Output $output
	 * @return PastafrolaUpgradeScript
	 */
	function __construct(Output $output) {
		parent::__construct($output);
		$this->setVersionFrom('1.6.2');
		$this->setVersionTo('1.7.3.3');
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
			$upgrade_script = tpl_fetch(get_template_path('db_migration/1_7_pastafrola'));
		} else {
			// upgrading from a pre-release of this version (beta, rc, etc)
			$upgrade_script = "";
			if (version_compare($installed_version, '1.7-beta') <= 0) {
				$upgrade_script .= "
					INSERT INTO `" . TABLE_PREFIX . "config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
						('system', 'notification_from_address', '', 'StringConfigHandler', 1, 0, 'Address to use as from field in email notifications. If empty, users address is used');
				";
			}
			if (version_compare($installed_version, '1.7-rc') <= 0) {
				$upgrade_script .= "
					INSERT INTO `" . TABLE_PREFIX . "config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
						('general', 'use_owner_company_logo_at_header', '0', 'BoolConfigHandler', 0, 0, '')
					ON DUPLICATE KEY UPDATE id=id;
					DELETE FROM `" . TABLE_PREFIX . "config_options` WHERE `category_name`='general' AND `name`='detect_mime_type_from_extension';
					INSERT INTO `" . TABLE_PREFIX . "user_ws_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES 
 						('general', 'detect_mime_type_from_extension', '0', 'BoolConfigHandler', 0, 800, '')
					ON DUPLICATE KEY UPDATE id=id;
				";
			}
			if (version_compare($installed_version, '1.7-rc2') <= 0) {
				$upgrade_script .= "
					ALTER TABLE `" . TABLE_PREFIX . "administration_tools` ADD COLUMN `visible` BOOLEAN NOT NULL DEFAULT 1;
					UPDATE `" . TABLE_PREFIX . "administration_tools` SET `visible`=0 WHERE `name`='mass_mailer';
				";
			}
			if (version_compare($installed_version, '1.7') <= 0) {
				$upgrade_script .= "
					ALTER TABLE `" . TABLE_PREFIX . "mail_accounts` 
					 ADD COLUMN `last_error_date` DATETIME NOT NULL default '0000-00-00 00:00:00',
					 ADD COLUMN `last_error_msg` VARCHAR(255) NOT NULL default '',
					 ADD COLUMN `sync_addr` VARCHAR( 100 ) NOT NULL default '',
					 ADD COLUMN `sync_pass` VARCHAR( 40 ) NOT NULL default '',
					 ADD COLUMN `sync_server` VARCHAR( 100 ) NOT NULL default '',
					 ADD COLUMN `sync_ssl` BOOL NOT NULL DEFAULT '0',
					 ADD COLUMN `sync_ssl_port` INT( 11 ) NOT NULL DEFAULT '993',
					 ADD COLUMN `sync_folder` VARCHAR( 100 ) NOT NULL default '';
					ALTER TABLE `" . TABLE_PREFIX . "mail_account_users` ADD COLUMN `last_error_state` INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0:no error,1:err unread, 2:err read';
					INSERT INTO `" . TABLE_PREFIX . "user_ws_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES 
					 ('mails panel', 'mail_account_err_check_interval', '300', 'IntegerConfigHandler', 0, 120, NULL),
					 ('mails panel', 'classify_mail_with_conversation', '1', 'BoolConfigHandler', 0, 130, NULL),
					 ('task panel', 'tasksShowEmptyMilestones', '1', 'BoolConfigHandler', 1, 0, '')
					ON DUPLICATE KEY UPDATE id=id;
					INSERT INTO `" . TABLE_PREFIX . "file_types` (`extension`, `icon`, `is_searchable`, `is_image`) VALUES
						('docx', 'doc.png', 0, 0),
						('xlsx', 'xls.png', 0, 0)
					ON DUPLICATE KEY UPDATE id=id;
					INSERT INTO `" . TABLE_PREFIX . "config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
						('system', 'min_chars_for_match', '3', 'IntegerConfigHandler', 1, 0, 'If search criteria len is less than this, then use always LIKE'),
						('mailing', 'sent_mails_sync', '1', 'BoolConfigHandler', '0', '0', 'imap email accounts synchronization possibility')
					ON DUPLICATE KEY UPDATE id=id;
					ALTER TABLE `" . TABLE_PREFIX . "application_logs` MODIFY COLUMN `action` enum('upload','open','close','delete','edit','add','trash','untrash','subscribe','unsubscribe','tag','untag','comment','link','unlink','login','logout','archive','unarchive','move','copy','read','download','checkin','checkout') collate utf8_unicode_ci default NULL;
					ALTER TABLE `" . TABLE_PREFIX . "mail_contents` ADD COLUMN `sync` BOOL NOT NULL DEFAULT '0';
				";
			}
			
			if (version_compare($installed_version, '1.7.2') <= 0) {
				$upgrade_script .= "
					INSERT INTO `" . TABLE_PREFIX . "config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
						('passwords', 'block_login_after_x_tries', '0', 'BoolConfigHandler', '0', '20', NULL),
						('mailing', 'check_spam_in_subject', '0', 'BoolConfigHandler', 0, 0, '')
					ON DUPLICATE KEY UPDATE id=id;
				";
			}
			
			if (!$this->checkTableExists(TABLE_PREFIX.'administration_logs', $this->database_connection)) {
				$upgrade_script .= "
					CREATE TABLE  `" . TABLE_PREFIX . "administration_logs` (
					  `id` int(10) unsigned NOT NULL auto_increment,
					  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
					  `title` varchar(50) NOT NULL default '',
					  `log_data` text NOT NULL,
					  `category` enum('system','security') NOT NULL,
					  PRIMARY KEY  (`id`),
					  KEY `created_on` (`created_on`),
					  KEY `category` (`category`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
				";
			}
		}
		
		if($this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
			$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
		} else {
			$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
			return false;
		} // if
		
		$this->printMessage('Feng Office has been upgraded. You are now running Feng Office '.$this->getVersionTo().' Enjoy!');
	} // execute
} // PastafrolaUpgradeScript

?>