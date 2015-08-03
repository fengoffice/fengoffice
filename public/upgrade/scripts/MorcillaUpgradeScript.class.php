<?php

/**
 * Morcilla upgrade script will upgrade FengOffice 2.3.2.1 to FengOffice 2.4.1
 *
 * @package ScriptUpgrader.scripts
 * @version 1.0
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class MorcillaUpgradeScript extends ScriptUpgraderScript {

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
		$this->setVersionFrom('2.3.2.1');
		$this->setVersionTo('2.4.1');
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
		$upgrade_loop_script = "";
		$upgrade_after_loop_script = "";
		
		$multi_assignment_create = "";
		$multi_assignment = "";
		
		if ($this->checkColumnExists($t_prefix.'project_tasks', 'multi_assignment', $this->database_connection)) {
			$multi_assignment_create .= "ALTER TABLE `".$t_prefix."template_tasks` ADD `multi_assignment` TINYINT( 1 ) NULL DEFAULT 0;";
			$multi_assignment .= ", `multi_assignment`";
		}
		
		$v_from = array_var($_POST, 'form_data');
		$original_version_from = array_var($v_from, 'upgrade_from', $installed_version);
		if (false && version_compare($installed_version, $this->getVersionFrom()) <= 0 && version_compare($original_version_from, '2.0.0.0-beta') > 0
			 && (!isset($_SESSION['from_feng1']) || !$_SESSION['from_feng1'])) {
			// upgrading from a version lower than this script's 'from' version
			$upgrade_script = tpl_fetch(get_template_path('db_migration/2_4_morcilla'));
		} else {
			
			if (version_compare($installed_version, '2.4-beta') < 0) {
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES 
						('general', 'timeReportDate', '4', 'IntegerConfigHandler', 1, 0, ''),
						('general', 'timeReportDateStart', '0000-00-00 00:00:00', 'DateTimeConfigHandler', 1, 0, ''),
						('general', 'timeReportDateEnd', '0000-00-00 00:00:00', 'DateTimeConfigHandler', 1, 0, ''),
						('general', 'timeReportPerson', '0', 'IntegerConfigHandler', 1, 0, ''),
						('general', 'timeReportTimeslotType', '2', 'IntegerConfigHandler', 1, 0, ''),
						('general', 'timeReportGroupBy', '0,0,0', 'StringConfigHandler', 1, 0, ''),
						('general', 'timeReportAltGroupBy', '0,0,0', 'StringConfigHandler', 1, 0, ''),
						('general', 'timeReportShowBilling', '0', 'BoolConfigHandler', 1, 0, '')
					ON DUPLICATE KEY UPDATE name=name;
				";
				if (!$this->checkColumnExists($t_prefix . "contact_addresses", "street", $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `".$t_prefix."contact_addresses` MODIFY COLUMN `street` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
					";
				}
				if (!$this->checkColumnExists($t_prefix . "system_permissions", "can_manage_contacts", $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `".$t_prefix."system_permissions` ADD COLUMN `can_manage_contacts` BOOLEAN NOT NULL DEFAULT 0;
					";
				}
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES 
						('listing preferences', 'breadcrumb_member_count', '5', 'IntegerConfigHandler', '0', '5', NULL)
					ON DUPLICATE KEY UPDATE name=name;
					
					INSERT INTO `".$t_prefix."object_types` (`name`,`handler_class`,`table_name`,`type`,`icon`,`plugin_id`) VALUES
 						('template_task', 'TemplateTasks', 'template_tasks', 'content_object', 'task', null),
						('template_milestone', 'TemplateMilestones', 'template_milestones', 'content_object', 'milestone', null)
					ON DUPLICATE KEY UPDATE name=name;
				";
				if (!$this->checkColumnExists($t_prefix . "project_tasks", "from_template_object_id", $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `".$t_prefix."project_tasks` ADD `from_template_object_id` int(10) unsigned DEFAULT '0' AFTER from_template_id;
						/* from template object id queda en 0 para todos los objetos instanciados de templates ya que no se puede obtener */
					";
				}
				if (!$this->checkColumnExists($t_prefix . "project_milestones", "from_template_object_id", $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `".$t_prefix."project_milestones` ADD `from_template_object_id` int(10) unsigned DEFAULT '0' AFTER from_template_id;
						/* from template object id queda en 0 para todos los objetos instanciados de templates ya que no se puede obtener */
					";
				}
				$upgrade_script .= "
					CREATE TABLE IF NOT EXISTS `".$t_prefix."template_tasks` (
					  `template_id` int(10) unsigned DEFAULT NULL,
					  `session_id` int(10) DEFAULT NULL,
					  `object_id` int(10) unsigned NOT NULL,
					  `parent_id` int(10) unsigned DEFAULT NULL,
					  `text` text COLLATE utf8_unicode_ci NOT NULL,
					  `due_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					  `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					  `assigned_to_contact_id` int(10) unsigned DEFAULT NULL,
					  `assigned_on` datetime DEFAULT NULL,
					  `assigned_by_id` int(10) unsigned DEFAULT NULL,
					  `time_estimate` int(10) unsigned NOT NULL DEFAULT '0',
					  `completed_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					  `completed_by_id` int(10) unsigned DEFAULT NULL,
					  `started_on` datetime DEFAULT NULL,
					  `started_by_id` int(10) unsigned NOT NULL,
					  `priority` int(10) unsigned DEFAULT '200',
					  `state` int(10) unsigned DEFAULT NULL,
					  `order` int(10) unsigned DEFAULT '0',
					  `milestone_id` int(10) unsigned DEFAULT NULL,
					  `is_template` tinyint(1) NOT NULL DEFAULT '0',
					  `from_template_id` int(10) NOT NULL DEFAULT '0',
					  `from_template_object_id` int(10) unsigned DEFAULT '0',
					  `repeat_end` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					  `repeat_forever` tinyint(1) NOT NULL,
					  `repeat_num` int(10) unsigned NOT NULL DEFAULT '0',
					  `repeat_d` int(10) unsigned NOT NULL,
					  `repeat_m` int(10) unsigned NOT NULL,
					  `repeat_y` int(10) unsigned NOT NULL,
					  `repeat_by` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
					  `object_subtype` int(10) unsigned NOT NULL DEFAULT '0',
					  `percent_completed` int(10) unsigned NOT NULL DEFAULT '0',
					  `use_due_time` tinyint(1) DEFAULT '0',
					  `use_start_time` tinyint(1) DEFAULT '0',
					  `original_task_id` int(10) unsigned DEFAULT '0',
					  `type_content` enum('text','html') NOT NULL DEFAULT 'text',
					  PRIMARY KEY (`object_id`),
					  KEY `parent_id` (`parent_id`),
					  KEY `completed_on` (`completed_on`),
					  KEY `order` (`order`),
					  KEY `milestone_id` (`milestone_id`),
					  KEY `priority` (`priority`),
					  KEY `assigned_to` USING HASH (`assigned_to_contact_id`)
					) ENGINE= InnoDB;
					
					CREATE TABLE IF NOT EXISTS `".$t_prefix."template_milestones` (
					  `template_id` int(10) unsigned DEFAULT NULL,
					  `session_id` int(10) DEFAULT NULL,
					  `object_id` int(10) unsigned NOT NULL,
					  `description` text COLLATE utf8_unicode_ci NOT NULL,
					  `due_date` datetime NOT NULL default '0000-00-00 00:00:00',
					  `is_urgent` BOOLEAN NOT NULL default '0',
					  `completed_on` datetime NOT NULL default '0000-00-00 00:00:00',
					  `completed_by_id` int(10) unsigned default NULL,
					  `is_template` BOOLEAN NOT NULL default '0',
					  `from_template_id` int(10) NOT NULL default '0',
					  `from_template_object_id` int(10) unsigned DEFAULT '0',
					  PRIMARY KEY  (`object_id`),
					  KEY `due_date` (`due_date`),
					  KEY `completed_on` (`completed_on`)
					) ENGINE= InnoDB;
					
					/* copy milestones */
					INSERT INTO `".$t_prefix."template_milestones`(`template_id`, `session_id`, `object_id`, `description`, `due_date`, `is_urgent`, `completed_on`, `completed_by_id`, `is_template`, `from_template_id`, `from_template_object_id`)
					SELECT ".$t_prefix."template_objects.template_id,'0' AS `session_id` ,".$t_prefix."project_milestones.`object_id`, `description`, `due_date`, `is_urgent`, `completed_on`, `completed_by_id`, `is_template`, `from_template_id`, `from_template_object_id`
					FROM `".$t_prefix."template_objects` 
					INNER JOIN `".$t_prefix."project_milestones` 
					ON ".$t_prefix."template_objects.object_id = ".$t_prefix."project_milestones.object_id
					AND ".$t_prefix."project_milestones.is_template =1
					ON DUPLICATE KEY UPDATE ".$t_prefix."template_milestones.template_id=".$t_prefix."template_milestones.template_id;
					
					/* delete from project milestones, milestones that are template milestones */
					DELETE FROM `".$t_prefix."project_milestones` WHERE `is_template` = 1;
					
					/* change the object type from project milestone to template milestone */
					UPDATE `".$t_prefix."objects` SET object_type_id = (SELECT id FROM `".$t_prefix."object_types` WHERE name = 'template_milestone')
					WHERE id IN (SELECT object_id FROM `".$t_prefix."template_milestones`);
					
					UPDATE `".$t_prefix."cron_events` set enabled=0, is_system=1 WHERE name='check_upgrade';
					update ".$t_prefix."project_tasks set percent_completed=100 where completed_on <> '0000-00-00 00:00:00';
				";
			}
			
			
			
			
			if (version_compare($installed_version, '2.4.1-beta') <= 0) {
				$upgrade_script .= "
					INSERT INTO `" . $t_prefix . "contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
					VALUES ('mails panel', 'attach_to_notification', '1', 'BoolConfigHandler', '0', '0', NULL)
					ON DUPLICATE KEY UPDATE name=name;
				";
				if (!$this->checkColumnExists($t_prefix . "project_files", "attach_to_notification", $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `" . $t_prefix . "project_files` ADD `attach_to_notification` TINYINT( 1 ) NOT NULL DEFAULT 0;
					";
				}
				if (!$this->checkColumnExists($t_prefix . "project_files", "default_subject", $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE `" . $t_prefix . "project_files` ADD `default_subject` TEXT;
					";
				}
				$upgrade_script .= "
					INSERT INTO `" . $t_prefix . "config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) 
					VALUES ('general', 'notify_myself_too', 0, 'BoolConfigHandler', '0', '100', '')
					ON DUPLICATE KEY UPDATE name=name;
				";
				
				$upgrade_script .= "
					ALTER TABLE `" . $t_prefix . "contact_member_permissions` ADD INDEX (`member_id`);
				";
			}
			
		}
		
		
		
		if($this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
			if (version_compare($installed_version, '2.4-beta') < 0){
				$while_condition = true;
				//add multi_assignment to template tasks table
				$upgrade_before_loop_script = $multi_assignment_create;
				//copy tasks
				$upgrade_before_loop_script .= "
					/* Copy tasks that are in templates objects */
					INSERT INTO `".$t_prefix."template_tasks`(`template_id`, `session_id`, `object_id`, `parent_id`, `text`, `due_date`, `start_date`, `assigned_to_contact_id`, `assigned_on`, `assigned_by_id`, `time_estimate`, `completed_on`, `completed_by_id`, `started_on`, `started_by_id`, `priority`, `state`, `order`, `milestone_id`, `is_template`, `from_template_id`, `from_template_object_id`, `repeat_end`, `repeat_forever`, `repeat_num`, `repeat_d`, `repeat_m`, `repeat_y`, `repeat_by`, `object_subtype`, `percent_completed`, `use_due_time`, `use_start_time`, `original_task_id`, `type_content`".$multi_assignment.")
					SELECT ".$t_prefix."template_objects.template_id,'0' AS `session_id` ,".$t_prefix."project_tasks.`object_id`, `parent_id`, `text`, `due_date`, `start_date`, `assigned_to_contact_id`, `assigned_on`, `assigned_by_id`, `time_estimate`, `completed_on`, `completed_by_id`, `started_on`, `started_by_id`, `priority`, `state`, `order`, `milestone_id`, `is_template`, `from_template_id`, `from_template_object_id`, `repeat_end`, `repeat_forever`, `repeat_num`, `repeat_d`, `repeat_m`, `repeat_y`, `repeat_by`, `object_subtype`, `percent_completed`, `use_due_time`, `use_start_time`, `original_task_id`, `type_content`".$multi_assignment."
					FROM `".$t_prefix."template_objects` 
					INNER JOIN `".$t_prefix."project_tasks` 
					ON ".$t_prefix."template_objects.object_id = ".$t_prefix."project_tasks.object_id
					AND ".$t_prefix."project_tasks.is_template =1
					ON DUPLICATE KEY UPDATE session_id = 0;
				";
				// Copy subtasks for tasks that are in template tasks.
				$upgrade_loop_script = "
					INSERT INTO `".$t_prefix."template_tasks`(`template_id`, `session_id`, `object_id`, `parent_id`, `text`, `due_date`, `start_date`, `assigned_to_contact_id`, `assigned_on`, `assigned_by_id`, `time_estimate`, `completed_on`, `completed_by_id`, `started_on`, `started_by_id`, `priority`, `state`, `order`, `milestone_id`, `is_template`, `from_template_id`, `from_template_object_id`, `repeat_end`, `repeat_forever`, `repeat_num`, `repeat_d`, `repeat_m`, `repeat_y`, `repeat_by`, `object_subtype`, `percent_completed`, `use_due_time`, `use_start_time`, `original_task_id`, `type_content`".$multi_assignment.")
					SELECT '0' AS `template_id`,'0' AS `session_id`, pt.`object_id`, pt.`parent_id`, pt.`text`, pt.`due_date`, pt.`start_date`, pt.`assigned_to_contact_id`, pt.`assigned_on`, pt.`assigned_by_id`, pt.`time_estimate`, pt.`completed_on`, pt.`completed_by_id`, pt.`started_on`, pt.`started_by_id`, pt.`priority`, pt.`state`, pt.`order`, pt.`milestone_id`, pt.`is_template`, pt.`from_template_id`, pt.`from_template_object_id`, pt.`repeat_end`, pt.`repeat_forever`, pt.`repeat_num`, pt.`repeat_d`, pt.`repeat_m`, pt.`repeat_y`, pt.`repeat_by`, pt.`object_subtype`, pt.`percent_completed`, pt.`use_due_time`, pt.`use_start_time`, pt.`original_task_id`, pt.`type_content`".$multi_assignment."
					FROM `".$t_prefix."project_tasks` AS pt
					WHERE pt.parent_id IN
					(SELECT pt2.`object_id`
					FROM `".$t_prefix."template_tasks` AS pt2)
					ON DUPLICATE KEY UPDATE ".$t_prefix."template_tasks.parent_id = pt.parent_id;
							
					/* copy template id from task to subtask */
					UPDATE ".$t_prefix."template_tasks AS t1, ".$t_prefix."template_tasks AS t2 SET t1.template_id=t2.template_id
					WHERE t1.parent_id=t2.object_id;
				";
					
				$check_continue_condition ="
					SELECT object_id
					FROM `".$t_prefix."project_tasks` AS pt
					WHERE pt.parent_id IN (SELECT pt2.object_id
						FROM `".$t_prefix."template_tasks` AS pt2)
					AND NOT EXISTS (SELECT * FROM `".$t_prefix."template_tasks` AS pt3
						WHERE pt3.object_id = pt.object_id);
					";
					
				$upgrade_after_loop_script .= "
					/* insert into template objects subtasks */
					INSERT INTO `".$t_prefix."template_objects`(`template_id`, `object_id`)
					SELECT tt.template_id, tt.object_id
					FROM `".$t_prefix."template_tasks` AS tt
					WHERE NOT EXISTS (SELECT * FROM `".$t_prefix."template_objects` AS to1 WHERE tt.object_id = to1.object_id);
					UPDATE `".$t_prefix."template_objects` t1, `".$t_prefix."template_objects` t2
					SET t1.created_by_id = t2.created_by_id, t1.created_on = t2.created_on
					WHERE t1.template_id = t2.template_id AND t2.created_by_id IS NOT NULL;
					
					/* delete from project task, tasks that are template tasks */
					DELETE FROM `".$t_prefix."project_tasks` WHERE `is_template` = 1;

					/* change the object type from project task to template task */
					UPDATE `".$t_prefix."objects` SET object_type_id = (SELECT id FROM `".$t_prefix."object_types` WHERE name = 'template_task')
					WHERE id IN (SELECT object_id FROM `".$t_prefix."template_tasks`);
				";
				
				// Copy tasks
				if(!$this->executeMultipleQueries($upgrade_before_loop_script, $total_queries, $executed_queries, $this->database_connection)) {
					$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
					return false;
				}
					
				while ($while_condition){
					// Copy subtasks
					if($this->executeMultipleQueries($upgrade_loop_script, $total_queries, $executed_queries, $this->database_connection)) {
						// Check if continue
						$res = mysql_query($check_continue_condition , $this->database_connection);
						
						$rows = array();
						while($row = mysql_fetch_array($res)){
							$rows[] = $row;
						}
						
						// is empty?
						if(!is_array($rows) || (count($rows) < 1)){
							$while_condition = false;
						}
						
					} else {
						$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
						return false;
					}
				}
					
				
				
			}
			
			if (version_compare($installed_version, '2.4-rc') < 0){
				$upgrade_after_loop_script .= "
					DELETE FROM `".$t_prefix."object_members` where object_id IN (SELECT object_id FROM  `".TABLE_PREFIX."template_tasks`);
					DELETE FROM `".$t_prefix."sharing_table` where object_id IN (SELECT object_id FROM  `".TABLE_PREFIX."template_tasks`);
					INSERT INTO `".$t_prefix."config_options` (`category_name`,`name`,`value`,`config_handler_class`,`is_system`) VALUES
						('general', 'add_default_permissions_for_users', '0', 'BoolConfigHandler', '0')
					ON DUPLICATE KEY UPDATE name=name;
				";
			}
			
			// after loop
			if(!$this->executeMultipleQueries($upgrade_after_loop_script, $total_queries, $executed_queries, $this->database_connection)) {
				$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
				return false;
			}
			$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
		} else {
			$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
			return false;
		}
		
		if (version_compare($installed_version, '2.5.0.4') < 0) {
			if (!$this->checkColumnExists("queued_emails", "attachments", $this->database_connection)) {
				$sqls = "
					ALTER TABLE ".$t_prefix."queued_emails` ADD COLUMN `attachments` TEXT;
				";
				$this->executeMultipleQueries($sqls, $t_queries, $e_queries, $this->database_connection);
			}
		}
		
		$this->printMessage('Feng Office has been upgraded. You are now running Feng Office '.$this->getVersionTo().' Enjoy!');

		tpl_assign('additional_steps', $additional_upgrade_steps);

	} // execute
	
} // MorcillaUpgradeScript
