<?php

/**
 * Chorizo upgrade script will upgrade FengOffice 2.2.4.1 to FengOffice 2.3.2.1
 *
 * @package ScriptUpgrader.scripts
 * @version 1.0
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class ChorizoUpgradeScript extends ScriptUpgraderScript {

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
		$this->setVersionFrom('2.2.4.1');
		$this->setVersionTo('2.3.2.1');
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
			$upgrade_script = tpl_fetch(get_template_path('db_migration/2_3_chorizo'));
		} else {
			
			if (version_compare($installed_version, '2.3-beta') < 0) {
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
						VALUES 
						('task panel', 'reminders_tasks', 'reminder_email,1,1440', 'StringConfigHandler', '0', '23', NULL),
						('task panel', 'add_task_autoreminder', '0', 'BoolConfigHandler', '0', '21', NULL),
						('task panel', 'add_self_task_autoreminder', '1', 'BoolConfigHandler', '0', '22', NULL),
						('task panel', 'add_task_default_reminder', '1', 'BoolConfigHandler', '0', '20', NULL),
						('calendar panel', 'add_event_autoreminder', '1', 'BoolConfigHandler', '0', '0', NULL),
						('calendar panel', 'autoassign_events', '0', 'BoolConfigHandler', '0', '0', NULL),
						('calendar panel', 'event_send_invitations', '1', 'BoolConfigHandler', '0', '0', NULL),
						('calendar panel', 'event_subscribe_invited', '1', 'BoolConfigHandler', '0', '0', NULL),
						('mails panel', 'mails_per_page', '50', 'IntegerConfigHandler', '0', '0', NULL),
						('general', 'access_member_after_add', '1', 'BoolConfigHandler', '0', '1300', NULL),
						('general', 'access_member_after_add_remember', '0', 'BoolConfigHandler', '0', '1301', NULL),
						('general', 'sendEmailNotification', '1', 'BoolConfigHandler', '1', '0', 'Send email notification to new user'),
 						('general', 'viewContactsChecked', '1', 'BoolConfigHandler', '1', '0', 'in people panel is view contacts checked'),
 						('general', 'viewUsersChecked', '1', 'BoolConfigHandler', '0', '0', 'in people panel is view users checked'),
 						('general', 'viewCompaniesChecked', '1', 'BoolConfigHandler', '1', '0', 'in people panel is view companies checked'),
						('general', 'contacts_per_page', '50', 'IntegerConfigHandler', '0', '1200', NULL)
					ON DUPLICATE KEY UPDATE name=name;
					INSERT INTO `".$t_prefix."config_options` (`category_name`,`name`,`value`,`config_handler_class`,`is_system`) VALUES
						('general', 'can_assign_tasks_to_companies', '1', 'BoolConfigHandler', '0'),
						('general', 'use_object_properties', '0', 'BoolConfigHandler', '0')
					ON DUPLICATE KEY UPDATE name=name;
					UPDATE `".$t_prefix."config_options` SET `value` = if ((SELECT count(*) FROM ".$t_prefix."object_properties)>0, 1, 0) WHERE `name`='use_object_properties';
					UPDATE `".$t_prefix."config_options` SET `value` = '1' WHERE `name`='can_assign_tasks_to_companies';
				";
			}
			
			if (version_compare($installed_version, '2.3.1-beta') < 0) {
				$upgrade_script .= "
					INSERT INTO ".$t_prefix."searchable_objects (rel_object_id, column_name, content, contact_id)
						SELECT contact_id, CONCAT('email_adress', id), email_address , '0' FROM ".$t_prefix."contact_emails
					ON DUPLICATE KEY UPDATE rel_object_id=rel_object_id;
					INSERT INTO ".$t_prefix."searchable_objects (rel_object_id, column_name, content, contact_id)
						SELECT contact_id, CONCAT('phone_number', id), number, '0' FROM ".$t_prefix."contact_telephones
					ON DUPLICATE KEY UPDATE rel_object_id=rel_object_id;
					INSERT INTO ".$t_prefix."searchable_objects (rel_object_id, column_name, content, contact_id)
						SELECT contact_id, CONCAT('web_url', id), url , '0' FROM ".$t_prefix."contact_web_pages
					ON DUPLICATE KEY UPDATE rel_object_id=rel_object_id;
					INSERT INTO ".$t_prefix."searchable_objects (rel_object_id, column_name, content, contact_id)
						SELECT contact_id, CONCAT('im_value', id), value , '0' FROM ".$t_prefix."contact_im_values
					ON DUPLICATE KEY UPDATE rel_object_id=rel_object_id;
					INSERT INTO ".$t_prefix."searchable_objects (rel_object_id, column_name, content, contact_id)
						SELECT contact_id, CONCAT('address', id), CONCAT(street,' ',city,' ',state,' ',country,' ',zip_code) , '0' FROM ".$t_prefix."contact_addresses
					ON DUPLICATE KEY UPDATE rel_object_id=rel_object_id;
					INSERT INTO `".$t_prefix."contact_config_options`(`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES ('task panel','tasksDateStart','0000-00-00 00:00:00','DateTimeConfigHandler',1,0,'date from to filter out task list'),
('task panel','tasksDateEnd','0000-00-00 00:00:00','DateTimeConfigHandler',1,0,'the date up to filter the list of tasks')
					ON DUPLICATE KEY UPDATE id=id;
											
					update ".$t_prefix."contacts set company_id=0 where company_id is null;
					update ".$t_prefix."contacts set display_name='' where display_name is null;
					update ".$t_prefix."contacts set avatar_file='' where avatar_file is null;
					update ".$t_prefix."contacts set last_login='0000-00-00 00:00:00' where last_login is null;
					update ".$t_prefix."contacts set last_visit='0000-00-00 00:00:00' where last_visit is null;
					update ".$t_prefix."contacts set personal_member_id=0 where personal_member_id is null;
					UPDATE `".$t_prefix."config_options` SET `is_system` = '1' WHERE `name`='viewUsersChecked';

					INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
					VALUES ('general', 'updateOnLinkedObjects', '0', 'BoolConfigHandler', '0', '0', 'Update objects when linking others')ON DUPLICATE KEY UPDATE name=name;
				";

				if (!$this->checkColumnExists($t_prefix."event_invitations", "synced", $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE ".$t_prefix."event_invitations ADD synced int(1) DEFAULT '0';
					";
				}
				if (!$this->checkColumnExists($t_prefix."event_invitations", "special_id", $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE ".$t_prefix."event_invitations ADD special_id text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
					";
				}
			}
			
			if (version_compare($installed_version, '2.3.1-rc') < 0) {
				$upgrade_script .= "
					update ".$t_prefix."config_options set is_system=1 where name='exchange_compatible';
					UPDATE `".$t_prefix."widgets` SET `default_section` = 'right' WHERE `title`='people';
					UPDATE `".$t_prefix."contact_config_options` SET `default_value` = 'F j, Y (l)' WHERE `name`='descriptive_date_format';
							
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
				VALUES ('dashboard', 'overviewAsList', '0', 'BoolConfigHandler', '1', '0', 'View Overview as list')
				ON DUPLICATE KEY UPDATE name=name;
				";
			}
		}
		if (version_compare($installed_version, '2.3.1.1') < 0) {
			$upgrade_script .= "
					DELETE FROM `".$t_prefix."contact_config_option_values` WHERE `option_id` = ( SELECT `id` FROM `".$t_prefix."contact_config_options` WHERE `name` = 'updateOnLinkedObjects');
					DELETE FROM `".$t_prefix."contact_config_options` WHERE `name` = 'updateOnLinkedObjects';
				
					INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
					VALUES ('general', 'updateOnLinkedObjects', '0', 'BoolConfigHandler', '0', '0', 'Update objects when linking others')
					ON DUPLICATE KEY UPDATE name=name;
				";
		}
		
		if (version_compare($installed_version, '2.3.2-beta') < 0) {
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."config_options` (`category_name`,`name`,`value`,`config_handler_class`,`is_system`) VALUES
					('general', 'let_users_create_objects_in_root', '1', 'BoolConfigHandler', '0')
				ON DUPLICATE KEY UPDATE name=name;
				
				INSERT INTO ".$t_prefix."contact_member_permissions (permission_group_id, member_id, object_type_id, can_delete, can_write)
				  SELECT c.permission_group_id, 0, rtp.object_type_id, rtp.can_delete, rtp.can_write FROM ".$t_prefix."role_object_type_permissions rtp 
				  INNER JOIN ".$t_prefix."contacts c ON c.user_type=rtp.role_id
				  WHERE rtp.object_type_id NOT IN (SELECT id FROM ".$t_prefix."object_types WHERE name IN ('mail','template','file_revision')) AND rtp.role_id in (
				    SELECT pg.id FROM ".$t_prefix."permission_groups pg WHERE pg.type='roles' AND pg.name IN ('Super Administrator','Administrator','Manager','Executive')
				  )
				ON DUPLICATE KEY UPDATE member_id=0;
				
				INSERT INTO ".$t_prefix."sharing_table (group_id, object_id)
				SELECT cmp.permission_group_id, o.id FROM ".$t_prefix."objects o
				INNER JOIN ".$t_prefix."contact_member_permissions cmp ON cmp.object_type_id=o.object_type_id AND cmp.member_id=0
				WHERE o.object_type_id IN (SELECT ot.id FROM ".$t_prefix."object_types ot WHERE ot.name!='mail' and ot.type IN ('content_object','comment','located'))
				AND NOT EXISTS (
				  SELECT om.object_id FROM ".$t_prefix."object_members om
				  WHERE om.object_id=o.id
				  AND om.member_id IN (
				    SELECT m.id FROM ".$t_prefix."members m WHERE m.dimension_id IN (SELECT d.id FROM ".$t_prefix."dimensions d WHERE d.defines_permissions=1 AND d.is_manageable=1)
				  )
				) ON DUPLICATE KEY UPDATE group_id=group_id;
			";
		
			if (version_compare($installed_version, '2.3.2-rc') < 0) {
				$upgrade_script .= "
					CREATE TABLE `to_delete` (`id` INTEGER UNSIGNED, PRIMARY KEY (`id`)) ENGINE = InnoDB;
					insert into to_delete select o.id from ".$t_prefix."object_types o inner join ".$t_prefix."object_types o2 on o.id>o2.id and o.name=o2.name;
					delete from ".$t_prefix."object_types where id in (select id from to_delete);
					DROP TABLE `to_delete`;
					ALTER TABLE `".$t_prefix."object_types` DROP INDEX `name`,
					 ADD UNIQUE INDEX `name` USING BTREE(`name`);
				";
			}
			
			
			if (version_compare($installed_version, '2.3.2-rc2') < 0) {
				$upgrade_script .= "
					UPDATE `".$t_prefix."administration_tools` SET `visible` = '0' WHERE `name`='mass_mailer';
					DELETE FROM `".$t_prefix."contact_emails` WHERE `contact_id` = '0';
				";
				
				if (!$this->checkColumnExists($t_prefix."application_logs", "member_id", $this->database_connection)) {
					$upgrade_script .= "
						ALTER TABLE ".$t_prefix."application_logs ADD member_id int(10) NOT NULL default '0';
					";
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
	
} // ChorizoUpgradeScript
