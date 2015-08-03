<?php

/**
 * Vacio upgrade script will upgrade FengOffice 2.4.1 to FengOffice 2.5.1.4
 *
 * @package ScriptUpgrader.scripts
 * @version 1.0
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class VacioUpgradeScript extends ScriptUpgraderScript {

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
		$this->setVersionFrom('2.4.1');
		$this->setVersionTo('2.5.1.5');
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
			$upgrade_script = tpl_fetch(get_template_path('db_migration/2_5_vacio'));
		} else {
			
			if (version_compare($installed_version, '2.5.1-beta') < 0) {
				if (!$this->checkColumnExists($t_prefix."widgets", "icon_cls", $this->database_connection)) {
					$upgrade_script .= "
					ALTER TABLE `".$t_prefix."widgets` ADD COLUMN `icon_cls` VARCHAR(50) NOT NULL DEFAULT '';
					";
				}
				$upgrade_script .= "
				INSERT INTO ".$t_prefix."widgets (name,title,plugin_id,path,default_options,default_section,default_order,icon_cls) values
				 ('comments','comments',0,'','','left',5, 'ico-comment')
				on duplicate key update name=name;
				
				DELETE FROM ".$t_prefix."widgets WHERE name IN ('completed_tasks', 'crpm_people');
				
				UPDATE ".$t_prefix."widgets SET icon_cls='ico-properties' WHERE name='activity_feed';
				UPDATE ".$t_prefix."widgets SET icon_cls='ico-event' WHERE name='calendar';
				UPDATE ".$t_prefix."widgets SET icon_cls='ico-task' WHERE name='completed_tasks_list';
				UPDATE ".$t_prefix."widgets SET icon_cls='ico-customer' WHERE name='customers';
				UPDATE ".$t_prefix."widgets SET icon_cls='ico-file' WHERE name='documents';
				UPDATE ".$t_prefix."widgets SET icon_cls='ico-email' WHERE name='emails';
				UPDATE ".$t_prefix."widgets SET icon_cls='ico-task' WHERE name='estimated_worked_time';
				UPDATE ".$t_prefix."widgets SET icon_cls='ico-folder' WHERE name='folders';
				UPDATE ".$t_prefix."widgets SET icon_cls='ico-message' WHERE name='messages';
				UPDATE ".$t_prefix."widgets SET icon_cls='ico-task' WHERE name='overdue_upcoming';
				UPDATE ".$t_prefix."widgets SET icon_cls='ico-contact' WHERE name='people';
				UPDATE ".$t_prefix."widgets SET icon_cls='ico-project' WHERE name='projects';
				UPDATE ".$t_prefix."widgets SET icon_cls='ico-properties' WHERE name='statics';
				UPDATE ".$t_prefix."widgets SET icon_cls='ico-properties' WHERE name='summary';
				UPDATE ".$t_prefix."widgets SET icon_cls='ico-workspace' WHERE name='workspaces';
				UPDATE ".$t_prefix."widgets SET icon_cls='ico-workspace' WHERE name='ws_description';
				UPDATE ".$t_prefix."widgets SET icon_cls='ico-comment' WHERE name='comments';
				";
				
				if (!$this->checkTableExists($t_prefix."contact_widget_options", $this->database_connection)) {
					$upgrade_script .= "
						CREATE TABLE `".$t_prefix."contact_widget_options` (
						  `widget_name` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
						  `contact_id` int(11) NOT NULL,
						  `member_type_id` int(11) NOT NULL DEFAULT 0,
						  `option` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
						  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
						  `config_handler_class` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
						  `is_system` tinyint(1) unsigned default 0,
						  PRIMARY KEY (`widget_name`,`contact_id`,`member_type_id`,`option`) USING BTREE
						) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
					";
				}
				
				$upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_widget_options` (widget_name,contact_id,member_type_id,`option`,`value`,config_handler_class,is_system) VALUES
				('overdue_upcoming',0,0,'assigned_to_user',0,'UserCompanyConfigHandler',0),
				('calendar',0,0,'filter_by_myself',0,'BooleanConfigHandler',0)
				ON DUPLICATE KEY UPDATE widget_name=widget_name;
				";
			}
			
			if (version_compare($installed_version, '2.5.1.1') < 0) {
				$upgrade_script .= "
					update ".$t_prefix."system_permissions set can_manage_tasks=1 where permission_group_id in (select id from ".$t_prefix."permission_groups where name in ('Super Administrator','Administrator','Manager','Executive'));
				";
			}
			
			if (version_compare($installed_version, '2.5.1.4') < 0) {
				$upgrade_script .= "
					update `".$t_prefix."contact_config_options` set `default_value`=1 where `name`='viewUsersChecked';
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
	
} // VacioUpgradeScript
