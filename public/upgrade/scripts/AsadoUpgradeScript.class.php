<?php

/**
 * Asado upgrade script will upgrade FengOffice 1.7.4 to FengOffice 2.0
 *
 * @package ScriptUpgrader.scripts
 * @version 1.1
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class AsadoUpgradeScript extends ScriptUpgraderScript {

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
		$this->setVersionFrom('1.7.4');
		$this->setVersionTo('2.0.1');
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

		$installed_version = installed_version();
		
		$t_prefix = TABLE_PREFIX;
		if (version_compare($installed_version, '1.7.5') <= 0 && TABLE_PREFIX != "fo_") $t_prefix = "fo_";
		tpl_assign('table_prefix', $t_prefix);
		
		if (defined('DB_ENGINE')) tpl_assign('engine', DB_ENGINE);
		else tpl_assign('engine', 'InnoDB');

		// ---------------------------------------------------
		//  Execute migration
		// ---------------------------------------------------
		
		$additional_upgrade_steps = array();
		
		// RUN QUERIES
		$total_queries = 0;
		$executed_queries = 0;
		
		$upgrade_script = "";
	
		// upgrading from version 1.x
		if (version_compare($installed_version, '2.0.0.0-beta') < 0) {
			ini_set('memory_limit', '1024M');
			@set_time_limit(0);
			
			$upgrade_script .= tpl_fetch(get_template_path('db_migration/2_0_asado'));
			
			if($this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
				$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
			} else {
				$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
				return false;
			}
			
			$_SESSION['from_feng1'] = true;
			
			$upgrade_script = "";
			
			@unlink(ROOT . '/cache/autoloader.php');
			
			include ROOT . '/environment/classes/AutoLoader.class.php';
			include ROOT . '/environment/constants.php';
			
			if (!$callbacks = spl_autoload_functions()) $callbacks = array();
			foreach ($callbacks as $callback) {
				spl_autoload_unregister($callback);
			}
			spl_autoload_register('feng_upg_autoload');
			foreach ($callbacks as $callback) {
				spl_autoload_register($callback);
			}
			
			@include ROOT . '/cache/autoloader.php';
			
			define('DONT_LOG', true);
			define('FORCED_TABLE_PREFIX', 'fo_');
			if (!defined('FILE_STORAGE_FILE_SYSTEM')) define('FILE_STORAGE_FILE_SYSTEM', 'fs');
			if (!defined('FILE_STORAGE_MYSQL')) define('FILE_STORAGE_MYSQL', 'mysql');
			if (!defined('MAX_SEARCHABLE_FILE_SIZE')) define('MAX_SEARCHABLE_FILE_SIZE', 1048576);
			
			try {
				DB::connect(DB_ADAPTER, array(
			      'host'    => DB_HOST,
			      'user'    => DB_USER,
			      'pass'    => DB_PASS,
			      'name'    => DB_NAME,
			      'persist' => DB_PERSIST
				));
				if(defined('DB_CHARSET') && trim(DB_CHARSET)) {
					DB::execute("SET NAMES ?", DB_CHARSET);
				}
			} catch(Exception $e) {
				$this->printMessage("Error connecting to database: ".$e->getMessage()."\n".$e->getTraceAsString());
			}
			
			try {
				$db_result = DB::execute("SELECT value FROM ".$t_prefix."config_options WHERE name = 'file_storage_adapter'");
				$db_result_row = $db_result->fetchRow();
				if($db_result_row['value'] == FILE_STORAGE_FILE_SYSTEM) {
					if (!defined('FILES_DIR')) define('FILES_DIR', ROOT . '/upload');
					FileRepository::setBackend(new FileRepository_Backend_FileSystem(FILES_DIR, TABLE_PREFIX));
				} else {
					FileRepository::setBackend(new FileRepository_Backend_DB(TABLE_PREFIX));
				}
			
				PublicFiles::setRepositoryPath(ROOT . '/public/files');
				if (!defined('PUBLIC_FOLDER')) define('PUBLIC_FOLDER', 'public');
				if(trim(PUBLIC_FOLDER) == '') {
					PublicFiles::setRepositoryUrl(with_slash(ROOT_URL) . 'files');
				} else {
					PublicFiles::setRepositoryUrl(with_slash(ROOT_URL) . 'public/files');
				}


				$member_parents = array();
				$members = Members::findAll();
				foreach ($members as $member) {
					$member_parents[$member->getId()] = $member->getAllParentMembersInHierarchy(false, false);
				}

				$object_members = DB::executeAll('SELECT * FROM '.$t_prefix.'object_members WHERE is_optimization=0 and not exists (SELECT x.object_id FROM '.$t_prefix.'object_members x where x.object_id=fo_object_members.object_id and x.is_optimization=1)');
				foreach ($object_members as $om) {
					$parents = isset($member_parents[$om['member_id']]) ? $member_parents[$om['member_id']] : array();
					if (count($parents) > 0) {
						$sql_values = "";
						foreach ($parents as $p) {
							$sql_values .= ($sql_values == "" ? "" : ",") . "(".$om['object_id'].",".$p->getId().",1)";
						}
						$sql = "INSERT INTO ".$t_prefix."object_members (object_id, member_id, is_optimization) VALUES $sql_values ON DUPLICATE KEY UPDATE is_optimization=1;";
                		DB::execute($sql);
					}
				}
				$this->printMessage("Finished generating Object Members");
				
				foreach ($members as $m) {
					if ($m->getParentMember() instanceof Member && $m->getDimensionId() != $m->getParentMember()->getDimensionId()) {
						$m->setDimensionId($m->getParentMember()->getDimensionId());
						$m->save();
					}
				}
				
				$app_move_logs = ApplicationLogs::findAll(array("conditions" => "action = 'move'"));
				foreach ($app_move_logs as &$app_log) {/* @var $app_log ApplicationLog */
					
					$exp_log_data = explode(";", $app_log->getLogData());
					
					if (count($exp_log_data) > 1) {
						$old_to = array_var($exp_log_data, 1);
						$old_from = array_var($exp_log_data, 0);
					} else {
						$old_to = array_var($exp_log_data, 0);
						$old_from = "";
					}
					
					$to_id = str_replace("to:", "", $old_to);
					$new_to_id = Members::instance()->findOne(array("id" => true, "conditions" => "ws_id = '$to_id'"));
					if (count($new_to_id) > 0) $new_to_id = $new_to_id[0];
					
					$new_from_ids = "";
					$from_ids = str_replace("from:", "", $old_from);
					if ($from_ids != "") {
						$new_from_ids_array = Members::instance()->findAll(array("id" => true, "conditions" => "ws_id IN ($from_ids)"));
						$new_from_ids = implode(",", $new_from_ids_array);
					}
					
					if ($new_to_id) {
						if ($new_from_ids) {
							$log_data = "from:$new_from_ids;to:$new_to_id";
						} else {
							$log_data = "to:$new_to_id";
						}						
						$app_log->setLogData($log_data);
						$app_log->save();
					}
				}

			} catch (Exception $e) {
				die("\nError occurred:\n-----------------\n".$e->getMessage()."\n".$e->getTraceAsString());
			}
			
			//tpl_assign('install_inv_dw', true);
			$additional_upgrade_steps[] = array(
				'url' => 'complete_migration.php?out=file',
				'name' => 'Fill searchable objects and sharing table',
				'filename' => dirname(__FILE__)."/../complete_migration.php"
			);
		
		} else {
			
			// upgrading from a pre-release of this version (beta, rc, etc)
			
			if (version_compare($installed_version, '2.0.0.4') <= 0) {
				if (!$this->checkTableExists($t_prefix.'role_object_type_permissions', $this->database_connection)) {
					$upgrade_script .= "
						CREATE TABLE `".$t_prefix."role_object_type_permissions` (
						  `role_id` INTEGER UNSIGNED NOT NULL,
						  `object_type_id` INTEGER UNSIGNED NOT NULL,
						  `can_delete` BOOLEAN NOT NULL,
						  `can_write` BOOLEAN NOT NULL,
						  PRIMARY KEY (`role_id`, `object_type_id`)
						) ENGINE = InnoDB;
						INSERT INTO ".$t_prefix."role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
						 SELECT p.id, o.id, 1, 1
						 FROM `".$t_prefix."object_types` o JOIN `".$t_prefix."permission_groups` p
						 WHERE o.`name` IN ('message','weblink','file','task','milestone','event','contact','mail','timeslot','report','comment')
						 AND p.`name` IN ('Super Administrator','Administrator','Manager','Executive');
						INSERT INTO ".$t_prefix."role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
						 SELECT p.id, o.id, 0, 1
						 FROM `".$t_prefix."object_types` o JOIN `".$t_prefix."permission_groups` p
						 WHERE o.`name` IN ('message','weblink','file','task','milestone','event','contact','timeslot','report','comment')
						 AND p.`name` IN ('Collaborator Customer');
						INSERT INTO ".$t_prefix."role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
						 SELECT p.id, o.id, 0, 1
						 FROM `".$t_prefix."object_types` o JOIN `".$t_prefix."permission_groups` p
						 WHERE o.`name` IN ('message','weblink','file','task','milestone','event','timeslot','comment')
						 AND p.`name` IN ('Internal Collaborator','External Collaborator');
						INSERT INTO ".$t_prefix."role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
						 SELECT p.id, o.id, 0, 0
						 FROM `".$t_prefix."object_types` o JOIN `".$t_prefix."permission_groups` p
						 WHERE o.`name` IN ('message','weblink','file','event','comment')
						 AND p.`name` IN ('Guest Customer');
						INSERT INTO ".$t_prefix."role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
						 SELECT p.id, o.id, 0, 0
						 FROM `".$t_prefix."object_types` o JOIN `".$t_prefix."permission_groups` p
						 WHERE o.`name` IN ('message','weblink','event','comment')
						 AND p.`name` IN ('Guest');
						INSERT INTO ".$t_prefix."role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
						 SELECT p.id, o.id, 0, 0
						 FROM `".$t_prefix."object_types` o JOIN `".$t_prefix."permission_groups` p
						 WHERE o.`name` IN ('message','weblink','file','task','milestone','event','contact','timeslot','report','comment')
						 AND p.`name` IN ('Non-Exec Director');
						UPDATE ".$t_prefix."role_object_type_permissions SET can_write = 1 WHERE object_type_id = (SELECT id FROM ".$t_prefix."object_types WHERE name='comment');
					";
				}
				if (!$this->checkTableExists($t_prefix.'widgets', $this->database_connection)) {
					$upgrade_script .= "
						CREATE TABLE  `".$t_prefix."widgets` (
						  `name` varchar(64) NOT NULL,
						  `title` varchar(255) NOT NULL,
						  `plugin_id` int(10) unsigned NOT NULL,
						  `path` varchar(512) NOT NULL,
						  `default_options` text NOT NULL,
						  `default_section` varchar(64) NOT NULL,
						  `default_order` int(10) NOT NULL,
						  PRIMARY KEY (`name`)
						) ENGINE = InnoDB;
					";
				}
				
				if($this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
					$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
				} else {
					$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
					return false;
				}
			}
			
			
			if (version_compare($installed_version, '2.0.0.5') <= 0) {
				if (!$this->checkColumnExists($t_prefix.'contacts', 'default_billing_id', $this->database_connection)) {
					$upgrade_script = "
						ALTER TABLE `".$t_prefix."contacts` ADD COLUMN `default_billing_id` INTEGER NOT NULL DEFAULT 0;
						ALTER TABLE `".$t_prefix."project_tasks`
						 ADD COLUMN `use_due_time` BOOLEAN DEFAULT 0,
						 ADD COLUMN `use_start_time` BOOLEAN DEFAULT 0;
						UPDATE ".$t_prefix."project_tasks t SET
						 t.due_date = ADDTIME(t.due_date, CONCAT(SUBSTRING_INDEX((SELECT c.timezone FROM ".$t_prefix."contacts c WHERE c.object_id=(SELECT o.updated_by_id FROM ".$t_prefix."objects o WHERE o.id=t.object_id)), '.', 1), ':', SUBSTRING_INDEX(abs((SELECT c.timezone FROM ".$t_prefix."contacts c WHERE c.object_id=(SELECT o.updated_by_id FROM ".$t_prefix."objects o WHERE o.id=t.object_id)) % 1)*60, '.', 1)))
						 WHERE t.due_date > 0;
						UPDATE ".$t_prefix."project_tasks t SET
						 t.start_date = ADDTIME(t.start_date, CONCAT(SUBSTRING_INDEX((SELECT c.timezone FROM ".$t_prefix."contacts c WHERE c.object_id=(SELECT o.updated_by_id FROM ".$t_prefix."objects o WHERE o.id=t.object_id)), '.', 1), ':', SUBSTRING_INDEX(abs((SELECT c.timezone FROM ".$t_prefix."contacts c WHERE c.object_id=(SELECT o.updated_by_id FROM ".$t_prefix."objects o WHERE o.id=t.object_id)) % 1)*60, '.', 1)))
						 WHERE t.start_date > 0;
						INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
						 ('general', 'work_day_end_time', '18:00', 'TimeConfigHandler', 0, 410, 'Work day end time');						
					";
				}
				
				if($this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
					$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
				} else {
					$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
					return false;
				}
			}
			
					
			if (version_compare($installed_version, '2.0.0.6') <= 0) {
				//WS Widgets
				$upgrade_script = "
					UPDATE `".$t_prefix."contact_config_options` SET `default_value` = '15' WHERE `".$t_prefix."contact_config_options`.`name` = 'noOfTasks' LIMIT 1 ;
					UPDATE ".$t_prefix."widgets SET default_section = 'none' WHERE name = 'people' AND NOT EXISTS (SELECT id from ".$t_prefix."plugins WHERE name = 'crpm');
					UPDATE ".$t_prefix."dimensions SET options = '{\"defaultAjax\":{\"controller\":\"dashboard\", \"action\": \"main_dashboard\"}, \"quickAdd\":true,\"showInPaths\":true}' 
						WHERE  code='workspaces';
					UPDATE `".$t_prefix."tab_panels` SET default_action = 'main_dashboard', initial_action = 'main_dashboard'
						WHERE id = 'overview-panel' ;
					UPDATE ".$t_prefix."object_types SET type = 'dimension_object', handler_class='Workspaces', table_name = 'workpaces' WHERE name = 'workspace' ;
					UPDATE ".$t_prefix."dimension_object_types SET OPTIONS = '{\"defaultAjax\":{\"controller\":\"dashboard\", \"action\": \"main_dashboard\"}}' 
						WHERE dimension_id = (SELECT id FROM ".$t_prefix."dimensions WHERE code = 'workspaces');
					CREATE TABLE IF NOT EXISTS `".$t_prefix."contact_widgets` (
					  `widget_name` varchar(40) NOT NULL,
					  `contact_id` int(11) NOT NULL,
					  `section` varchar(40) NOT NULL,
					  `order` int(11) NOT NULL,
					  `options` varchar(255) NOT NULL,
					  PRIMARY KEY (`widget_name`,`contact_id`) USING BTREE
					) ENGINE=InnoDB;
					INSERT INTO ".$t_prefix."widgets(name, title, plugin_id, default_section,default_order) 
					 VALUES ('messages','notes',0,'none',1000)
					 ON DUPLICATE KEY update name = name;
					INSERT INTO ".$t_prefix."dimension_object_type_contents (dimension_id, dimension_object_type_id, content_object_type_id, is_required, is_multiple)
					 SELECT d.id, ot.id, (SELECT tmp.id FROM ".$t_prefix."object_types tmp WHERE tmp.name='contact'), 0, 1
					 FROM ".$t_prefix."dimensions d JOIN ".$t_prefix."object_types ot
					 WHERE d.code = 'customer_project' AND ot.name IN ('customer', 'project', 'folder', 'customer_folder', 'project_folder')
					ON DUPLICATE KEY UPDATE dimension_id=dimension_id;
					UPDATE ".$t_prefix."dimension_object_type_contents SET is_multiple = 1 WHERE content_object_type_id = (SELECT id FROM ".$t_prefix."object_types WHERE name='mail');
				";
				
				if (@mysql_fetch_row(@mysql_query(("SELECT id from ".$t_prefix."plugins WHERE name = 'workspaces'")))) {
					$upgrade_script.="INSERT INTO ".$t_prefix."widgets(name, title, plugin_id, default_section,default_order) 
						VALUES ('ws_description', 'workspace description',(SELECT id from ".$t_prefix."plugins WHERE name = 'workspaces'), 'left',-100)
						ON DUPLICATE KEY update name = name ;";
				}
				
					
				if($this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
					$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
				} else {
					$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
					return false;
				}
				
				
				if ($obj = @mysql_fetch_object(@mysql_query("SELECT id FROM ".$t_prefix."object_types WHERE name = 'workspace' " ))) {
					$wsTypeId = $obj->id ;
					$res = @mysql_query("SELECT * FROM ".$t_prefix."members WHERE dimension_id = (SELECT id FROM ".$t_prefix."dimensions WHERE code='workspaces')" ) ; 
					while ( $m = @mysql_fetch_object($res) ) {
						@mysql_query("INSERT INTO ".$t_prefix."objects (object_type_id, name) VALUES ($wsTypeId, '".$m->name."' )" );
						if ( $id = @mysql_insert_id()){
							@mysql_query("INSERT INTO ".$t_prefix."workspaces (object_id) VALUES ($id)");
							@mysql_query("UPDATE ".$t_prefix."members SET object_id=$id WHERE id = $m->id ");
						}
					}
				}
			}
                        
			if (version_compare($installed_version, '2.0.0.7') <= 0) {
				$upgrade_script = "";
				if (!$this->checkTableExists($t_prefix.'mail_spam_filters', $this->database_connection)) {
					$upgrade_script .= "
                                                    CREATE TABLE IF NOT EXISTS `".$t_prefix."mail_spam_filters` (
                                                     `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                                     `account_id` int(10) unsigned NOT NULL,
                                                     `text_type` enum('email_address','subject') COLLATE utf8_unicode_ci NOT NULL,
                                                     `text` text COLLATE utf8_unicode_ci NOT NULL,
                                                     `spam_state` enum('no spam','spam') COLLATE utf8_unicode_ci NOT NULL,
                                                     PRIMARY KEY (`id`)
                                                    ) ENGINE=InnoDB;
                                        ";
				}

				$upgrade_script .= "INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) 
					VALUES ('general', 'untitled_notes', '0', 'BoolConfigHandler', '0', '0', NULL) ON DUPLICATE KEY UPDATE name=name;";
				
				if($this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
					$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
				} else {
					$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
					return false;
				}
			}
                        
			if (version_compare($installed_version, '2.0.0.8') < 0) {
				$upgrade_script = "";
				if (!$this->checkTableExists($t_prefix.'external_calendar_users', $this->database_connection)) {
					$upgrade_script .= "
                                                    CREATE TABLE IF NOT EXISTS `".$t_prefix."external_calendar_users` (
                                                      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                                      `contact_id` int(10) unsigned NOT NULL,
                                                      `auth_user` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
                                                      `auth_pass` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
                                                      `type` text COLLATE utf8_unicode_ci NOT NULL,
                                                      `sync` TINYINT( 1 ) NULL DEFAULT '0',
                                                      PRIMARY KEY (`id`)
                                                    ) ENGINE = InnoDB;
					";
				}
                                
				if (!$this->checkTableExists($t_prefix.'external_calendars', $this->database_connection)) {
					$upgrade_script .= "
                                                    CREATE TABLE IF NOT EXISTS `".$t_prefix."external_calendars` (
                                                      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                                      `ext_cal_user_id` int(10) unsigned NOT NULL,
                                                      `calendar_user` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                                                      `calendar_visibility` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                                                      `calendar_name` text COLLATE utf8_unicode_ci NOT NULL,
                                                      `calendar_feng` TINYINT( 1 ) NOT NULL DEFAULT '0',
                                                      PRIMARY KEY (`id`)
                                                    ) ENGINE = InnoDB;
					";
				}

				if (!$this->checkColumnExists($t_prefix.'project_events', 'ext_cal_id', $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `".$t_prefix."project_events`  ADD `ext_cal_id` INT(10) UNSIGNED NOT NULL;
					";
				}
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."project_events` CHANGE `special_id` `special_id` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
					UPDATE `".$t_prefix."file_types` SET `is_searchable` = '1' WHERE `extension` = 'docx';
					UPDATE `".$t_prefix."file_types` SET `is_searchable` = '1' WHERE `extension` = 'pdf';
					INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
						VALUES ('general', 'repeating_task', '0', 'BoolConfigHandler', '0', '0', '')
					ON DUPLICATE KEY UPDATE name=name;
					INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
						VALUES ('calendar panel', 'calendar task filter', 'pending', 'StringConfigHandler', '1', '0', NULL),
							('task panel', 'close timeslot open', '1', 'BoolConfigHandler', '0', '0', NULL),
							('calendar panel', 'reminders_events', 'reminder_email,1,60', 'StringConfigHandler', '0', '0', NULL)
					ON DUPLICATE KEY UPDATE name=name;
					INSERT INTO `".$t_prefix."cron_events` (`name`, `recursive`, `delay`, `is_system`, `enabled`, `date`)
						VALUES ('import_google_calendar', '1', '10', '0', '0', '0000-00-00 00:00:00'),
							('export_google_calendar', '1', '10', '0', '0', '0000-00-00 00:00:00')
					ON DUPLICATE KEY UPDATE name=name;
					";
				
				$upgrade_script .= "
					DELETE FROM `".$t_prefix."config_options` WHERE `name`='use_time_in_task_dates' AND NOT EXISTS (SELECT id FROM `".$t_prefix."plugins` WHERE `name`='crpm' AND is_activated=1);
					INSERT INTO ".$t_prefix."contact_config_options (category_name, name, default_value, config_handler_class, is_system, option_order) VALUES
						('general','show_object_direct_url',0,'BoolConfigHandler',0,0),
						('general','drag_drop_prompt','prompt','DragDropPromptConfigHandler',0,0)
					 ON DUPLICATE KEY UPDATE name = name;
				";
				
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."tab_panels` (`id`,`title`,`icon_cls`,`refresh_on_context_change`,`default_controller`,`default_action`,`initial_controller`,`initial_action`,`enabled`,`type`,`ordering`,`plugin_id`,`object_type_id`) VALUES 
					('contacts-panel','contacts','ico-contacts',1,'contact','init','','',0,'system',7,0,16) ON DUPLICATE KEY UPDATE title=title;
				";

				if($this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
					$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
				} else {
					$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
					return false;
				}
			}
                        
           	if (version_compare($installed_version, '2.0.1') < 0) {
				$upgrade_script = "";
                                
				$upgrade_script .= "INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
					VALUES ('general', 'working_days', '1,2,3,4,5,6,7', 'StringConfigHandler', '0', '0', NULL);
					ALTER TABLE `".$t_prefix."project_tasks` ADD `original_task_id` INT( 10 ) UNSIGNED NULL DEFAULT '0';
					ALTER TABLE `".$t_prefix."project_tasks` ADD `type_content` ENUM( 'text', 'html' ) NOT NULL DEFAULT 'text';
					ALTER TABLE `".$t_prefix."project_events` ADD `original_event_id` INT( 10 ) UNSIGNED NULL DEFAULT '0';
					ALTER TABLE `".$t_prefix."project_messages` ADD `type_content` ENUM( 'text', 'html' ) NOT NULL DEFAULT 'text';
				";

				$upgrade_script .= "INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
					VALUES ('general', 'wysiwyg_tasks', '0', 'BoolConfigHandler', '0', '0', NULL),
					('general', 'wysiwyg_messages', '0', 'BoolConfigHandler', '0', '0', NULL),
					('task panel', 'tasksShowTimeEstimates', '1', 'BoolConfigHandler', '1', '0', NULL)
				ON DUPLICATE KEY UPDATE name=name;
				";
				
				$upgrade_script .= "UPDATE `".$t_prefix."widgets` SET plugin_id = (SELECT id FROM `".$t_prefix."plugins` WHERE name='workspaces') WHERE name='workspaces';
				";
				
				// clean old users dimension
				$upgrade_script .= "DELETE FROM `".$t_prefix."object_members` WHERE member_id IN (SELECT `id` FROM `".$t_prefix."members` WHERE `dimension_id` IN (SELECT `id` FROM `".$t_prefix."dimensions` WHERE `code`='feng_users'));
					DELETE FROM `".$t_prefix."contact_dimension_permissions` WHERE dimension_id IN (SELECT `id` FROM `".$t_prefix."dimensions` WHERE `code`='feng_users');
					DELETE FROM `".$t_prefix."members` WHERE dimension_id IN (SELECT `id` FROM `".$t_prefix."dimensions` WHERE `code`='feng_users');
					DELETE FROM `".$t_prefix."dimension_object_type_contents` WHERE dimension_id IN (SELECT `id` FROM `".$t_prefix."dimensions` WHERE `code`='feng_users');
					DELETE FROM `".$t_prefix."dimension_object_type_hierarchies` WHERE dimension_id IN (SELECT `id` FROM `".$t_prefix."dimensions` WHERE `code`='feng_users');
					DELETE FROM `".$t_prefix."dimension_object_types` WHERE dimension_id IN (SELECT `id` FROM `".$t_prefix."dimensions` WHERE `code`='feng_users');
					DELETE FROM `".$t_prefix."dimensions` WHERE code='feng_users';
					DELETE FROM `".$t_prefix."object_types` WHERE name='user';
					UPDATE ".$t_prefix."contacts c SET c.personal_member_id = 0 WHERE c.user_type>0 AND NOT (SELECT count(m2.id) FROM ".$t_prefix."members m2 WHERE m2.object_id=c.personal_member_id)=0;
				";

				if($this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
					$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
				} else {
					$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
					return false;
				}
			}
			
			
			// Plugin Version Support 
			$upgrade_script = '';
			if(!$this->checkColumnExists($t_prefix."plugins", 'version', $this->database_connection)) { 
				$upgrade_script = 'ALTER TABLE '.$t_prefix.'plugins ADD COLUMN `version` INTEGER  NOT NULL  DEFAULT 1 AFTER `name` ';
				if($this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
					$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
				} else {
					$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
					return false;
				}
			}
		}
		
		$this->printMessage('Feng Office has been upgraded. You are now running Feng Office '.$this->getVersionTo().' Enjoy!');
		
		
		tpl_assign('additional_steps', $additional_upgrade_steps);
		
	} // execute
} // AsadoUpgradeScript

?>
