<?php

/**
 * Paella upgrade script will upgrade FengOffice 3.4.4.64 to FengOffice 3.6.3-rc7
 *
 * @package ScriptUpgrader.scripts
 * @version 1.0
 */
class PaellaUpgradeScript extends ScriptUpgraderScript {

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
	 * Construct the PaellaUpgradeScript
	 *
	 * @param Output $output
	 * @return PaellaUpgradeScript
	 */
	function __construct(Output $output) {
		parent::__construct($output);
		$this->setVersionFrom('3.4.4.52');
		$this->setVersionTo('3.6.3-rc7');
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
		
		
		
		
		// Set upgrade queries	
		if (version_compare($installed_version, '3.5-alpha') < 0) {
			
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."file_types` (`extension`, `icon`, `is_searchable`, `is_image`) VALUES ('ics', 'ics.png', '0', '0')
				ON DUPLICATE KEY UPDATE `extension`=`extension`;
			";
			if (!$this->checkColumnExists($t_prefix."tab_panels", "url_params", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."tab_panels` ADD COLUMN `url_params` varchar(255) COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '';
				";
			}
		}
		
		if (version_compare($installed_version, '3.5-beta') < 0) {
			$upgrade_script .= "
				insert into ".$t_prefix."object_members
					select t.object_id, om.member_id, om.is_optimization from ".$t_prefix."timeslots t
					inner join ".$t_prefix."object_members om on om.object_id=t.rel_object_id
					where t.rel_object_id>0
				on duplicate key update ".$t_prefix."object_members.object_id=".$t_prefix."object_members.object_id;
			";
			if (!$this->checkColumnExists($t_prefix."config_options", "options", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."config_options` ADD COLUMN `options` varchar(255) COLLATE 'utf8_unicode_ci' DEFAULT '';
				";
			}
		}
		
		if (version_compare($installed_version, '3.5-beta2') < 0) {
			if (!$this->checkColumnExists($t_prefix."timeslots", "worked_time", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."timeslots` ADD COLUMN `worked_time` int(10) unsigned NOT NULL DEFAULT 0;
				";
			}
			
			$upgrade_script .= "
				update ".$t_prefix."timeslots set worked_time=GREATEST(TIMESTAMPDIFF(MINUTE,start_time,end_time),0) - (subtract/60);
			";
		}
		
		
		if (version_compare($installed_version, '3.5.0.3') < 0) {
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				('task panel', 'tasksShowAssignedToName', '0', 'BoolConfigHandler', 0, 0, '')
				ON DUPLICATE KEY UPDATE name=name;
			";
		}
		
		
		if (version_compare($installed_version, '3.5.0.7') < 0) {
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_categories` (`name`, `is_system`, `type`, `category_order`) VALUES 
					('contact panel', 0, 0, 8)
				ON DUPLICATE KEY UPDATE name=name;
			";
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES 
				 ('contact panel', 'show_inactive_users_in_list', '1', 'BoolConfigHandler', '0', '0', NULL)
				ON DUPLICATE KEY UPDATE name=name;
			";
		}

		
		if (version_compare($installed_version, '3.5.0.10') < 0) {
			$upgrade_script .= "
				INSERT INTO ".$t_prefix."dimension_associations_config (association_id, config_name, value)
					SELECT id, 'autoclassify_in_property_member', '1'
					FROM ".$t_prefix."dimension_member_associations WHERE associated_dimension_id NOT IN (SELECT id FROM ".$t_prefix."dimensions WHERE code='feng_persons')
				ON DUPLICATE KEY UPDATE value=value;
		
				INSERT INTO ".$t_prefix."dimension_associations_config (association_id, config_name, value)
					SELECT id, 'allow_remove_from_property_member', '1'
					FROM ".$t_prefix."dimension_member_associations WHERE associated_dimension_id NOT IN (SELECT id FROM ".$t_prefix."dimensions WHERE code='feng_persons')
				ON DUPLICATE KEY UPDATE value=value;
			";
		}
		
		
		if (version_compare($installed_version, '3.5.1-beta3') < 0) {
			if (!$this->checkColumnExists($t_prefix."contacts", "token_disabled", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."contacts` ADD `token_disabled` varchar(40) COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '';
				";
				$upgrade_script .= "
					UPDATE `".$t_prefix."contacts` SET `token_disabled`=`token` WHERE user_type > 0 AND disabled=1;
					UPDATE `".$t_prefix."contacts` SET `token`='' WHERE user_type > 0 AND disabled=1;
				";
			}
		}
		
		if (version_compare($installed_version, '3.5.1-rc') < 0) {
			if (!$this->checkValueExists($t_prefix."contact_config_options", 'name', 'show_associated_dims_in_breadcrumbs', $this->database_connection)) {
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					('listing preferences', 'show_associated_dims_in_breadcrumbs', '0', 'BoolConfigHandler', '0', '20', NULL)
					ON DUPLICATE KEY UPDATE category_name=category_name;
				";
			}
		}

        if (version_compare($installed_version, '3.5.1.4') < 0) {
            $upgrade_script .= "
            INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
				VALUES ('mailing', 'disable_notifications_for_object_type', '', 'MultipleObjectTypeConfigHandler', 0, 0, '')
                ON DUPLICATE KEY UPDATE `category_name`=`category_name`;
            ";
            
        }
        
        if (version_compare($installed_version, '3.5.1.5') < 0) {
        	if (!$this->checkColumnExists($t_prefix."contact_config_options", 'options', $this->database_connection)) {
				$upgrade_script .= "
       				ALTER TABLE `".$t_prefix."contact_config_options` ADD `options` varchar(255) COLLATE 'utf8_unicode_ci';
				";
        	}
        }
        
        if (version_compare($installed_version, '3.5.2-beta') < 0) {
        	if (!$this->checkValueExists($t_prefix."contact_config_options", "name", "enable_archive_confirmation", $this->database_connection)) {
	        	$upgrade_script .= "
					INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
						('general', 'enable_archive_confirmation', '1', 'BoolConfigHandler', 0, 0, ''),
						('general', 'enable_trash_confirmation', '1', 'BoolConfigHandler', 0, 0, '')
					ON DUPLICATE KEY UPDATE name=name;
				";
        	}
        	
        	$upgrade_script .= "
				ALTER TABLE `".$t_prefix."sharing_table` ADD INDEX `group_id` (`group_id`);
			";
        }

        if (version_compare($installed_version, '3.5.3-beta2') < 0) {
            if (!$this->checkValueExists($t_prefix."config_options", "name", "notifications_add_members_in_subject", $this->database_connection)) {
                $upgrade_script .= "
                   INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
				        VALUES ('mailing', 'notifications_add_members_in_subject', '', 'ManageableDimensionsConfigHandler', 0, 0, '')
                        ON DUPLICATE KEY UPDATE `category_name`=`category_name`;
                ";
            }
        }
        
        if (version_compare($installed_version, '3.5.3-beta2') < 0) {
            $upgrade_script .= "
                CREATE TABLE IF NOT EXISTS `".$t_prefix."contact_external_tokens` (
                `id` int(10) unsigned NOT NULL auto_increment,
                `contact_id` int(10) unsigned NOT NULL,
                `token` text COLLATE utf8_unicode_ci default '',
                `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL default '',
                `external_key` varchar(255) COLLATE utf8_unicode_ci default '',
                `external_name` varchar(255) COLLATE utf8_unicode_ci default '',
                `created_date` datetime default '0000-00-00 00:00:00',
                `expired_date` datetime default '0000-00-00 00:00:00',
                  PRIMARY KEY  (`id`)
                ) ENGINE=InnoDB;
            ";
        }
        
        if (version_compare($installed_version, '3.5.3-rc') < 0) {
            if (!$this->checkColumnExists($t_prefix."project_tasks", "mark_as_started", $this->database_connection)) {
                $upgrade_script .= "
					ALTER TABLE `".$t_prefix."project_tasks` ADD `mark_as_started` BOOLEAN NOT NULL default '0';
				";
            }
            
            if (!$this->checkColumnExists($t_prefix."system_permissions", "can_manage_repetitive_properties_of_tasks", $this->database_connection)) {
                $upgrade_script .= "
					ALTER TABLE `".$t_prefix."system_permissions` ADD `can_manage_repetitive_properties_of_tasks` BOOLEAN NOT NULL default '0';
				";
                
                $upgrade_script .= "
                    UPDATE `".$t_prefix."system_permissions` SET `can_manage_repetitive_properties_of_tasks`=1 WHERE `permission_group_id` IN (
                        SELECT permission_group_id FROM `".$t_prefix."contacts` WHERE `user_type` IN (
                            SELECT id FROM `".$t_prefix."permission_groups` WHERE `type`='roles' AND `name` IN ('Super Administrator','Administrator','Manager','Executive')
                        )
                    );";
                
                $upgrade_script .= "
                                    UPDATE `".$t_prefix."system_permissions` set `can_manage_repetitive_properties_of_tasks`=1 WHERE `permission_group_id` IN ( 
                                        SELECT id FROM `".$t_prefix."permission_groups` WHERE `name` IN ('Super Administrator', 'Administrator', 'Manager','Executive')
                                    );";
                
                
                                                                                            
            }
            
            if (!$this->checkColumnExists($t_prefix."max_system_permissions", "can_manage_repetitive_properties_of_tasks", $this->database_connection)) {
                $upgrade_script .= "
					ALTER TABLE `".$t_prefix."max_system_permissions` ADD `can_manage_repetitive_properties_of_tasks` BOOLEAN NOT NULL default '0';
				";
                
                $upgrade_script .= "
                    UPDATE `".$t_prefix."max_system_permissions` SET `can_manage_repetitive_properties_of_tasks`=1 WHERE `permission_group_id` IN (
                        SELECT permission_group_id FROM `".$t_prefix."contacts` WHERE `user_type` IN (
                            SELECT id FROM `".$t_prefix."permission_groups` WHERE `type`='roles' AND `name` IN ('Super Administrator','Administrator','Manager','Executive')
                        )
                    );";
                
                $upgrade_script .= "
                    UPDATE `".$t_prefix."max_system_permissions` set `can_manage_repetitive_properties_of_tasks` = 1
                    WHERE `permission_group_id` IN ( 
                        SELECT `id` 
                        FROM `".$t_prefix."permission_groups`
                        WHERE `name` IN ('Super Administrator', 'Administrator', 'Manager','Executive')
                    );";
            }
            
            $upgrade_script .= "
                INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
				VALUES ('general', 'use_task_work_performed', '1', 'BoolConfigHandler', 0, 0, '')
                ON DUPLICATE KEY UPDATE `category_name`=`category_name`;
            ";
                
            $upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				('task panel', 'tasksShowQuickMarkAsStarted', '1', 'BoolConfigHandler', 1, 0, '')	
				ON DUPLICATE KEY UPDATE name=name;
			";
                            
        }

        if (version_compare($installed_version, '3.5.3') < 0) {
        	$upgrade_script .= "
				UPDATE `".$t_prefix."custom_properties` SET `type`='datetime' WHERE `type`='date' AND code!='workday';
			";
        	$upgrade_script .= "
				UPDATE `".$t_prefix."custom_property_values` cpv
				INNER JOIN `".$t_prefix."custom_properties` cp ON cpv.custom_property_id=cp.id
				SET cpv.value = DATE_FORMAT(cpv.value, '%Y-%m-%d %H:%i:%s')
				WHERE cp.`type`='datetime' AND cpv.value!='' AND cpv.value != DATE_FORMAT(cpv.value, '%Y-%m-%d %H:%i:%s');
			";
        	
            if (!$this->checkKeyExists($t_prefix."custom_property_values", "object_id", $this->database_connection)) {
                $upgrade_script .= "
					ALTER TABLE `".$t_prefix."custom_property_values`
					ADD INDEX `object_id` (`object_id`);
				";
            }
            if (!$this->checkKeyExists($t_prefix."custom_property_values", "custom_property_id", $this->database_connection)) {
                $upgrade_script .= "
					ALTER TABLE `".$t_prefix."custom_property_values`
					ADD INDEX `custom_property_id` (`custom_property_id`);
				";
            }
            if (!$this->checkKeyExists($t_prefix."custom_property_values", "value", $this->database_connection)) {
                $upgrade_script .= "
					ALTER TABLE `".$t_prefix."custom_property_values`
					ADD INDEX `value` (`value`(255));
				";
            }
        }
        
        if (version_compare($installed_version, '3.6.0-beta.1') < 0) {

            $upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				('task panel', 'tasksShowTimeQuick', '1', 'BoolConfigHandler', 1, 0, '')	
				ON DUPLICATE KEY UPDATE name=name;
			";

            $upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				('time panel', 'show_start_time_action', '1', 'BoolConfigHandler', 0, 0, '')	
				ON DUPLICATE KEY UPDATE name=name;
			";

            $upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				('time panel', 'show_pause_time_action', '1', 'BoolConfigHandler', 0, 0, '')	
				ON DUPLICATE KEY UPDATE name=name;
			";

            $upgrade_data = '{"option": [{"value": "1","text": "config_start_calc"},{"value": "2","text": "config_end_calc"},{"value": "3","text": "always_show_modal"}]}';

           $upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`, `options`) VALUES
				('time panel', 'automatic_calculation_time', '1', 'ListConfigHandler', 0, 0, '', '".$upgrade_data."')	
				ON DUPLICATE KEY UPDATE `name`=`name`;
			";

            $upgrade_script .= "
                INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`)
				VALUES ('general', 'show_pause_time_action', '1', 'BoolConfigHandler', 0, 0, '')
                ON DUPLICATE KEY UPDATE `category_name`=`category_name`;
            ";
        }

        if (version_compare($installed_version, '3.6.0-beta.2') < 0) {
            if (!$this->checkColumnExists($t_prefix."currencies", "external_id", $this->database_connection)) {
                $upgrade_script .= "
					ALTER TABLE `" . $t_prefix . "currencies` ADD `external_id` INTEGER UNSIGNED NOT NULL;
				";
            }
        }
        
        if (version_compare($installed_version, '3.6.1-beta') < 0) {
            $upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				('time panel', 'stop_running_timeslots', '0', 'BoolConfigHandler', '0', '0', '')
				ON DUPLICATE KEY UPDATE `name`=`name`;
			";
        }

        if (version_compare($installed_version, '3.6.1-beta4') < 0) {
            $upgrade_data = '{"option": [{"value": "1","text": "config_start_calc"},{"value": "2","text": "config_end_calc"},{"value": "3","text": "always_show_modal"}]}';

            $upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`, `options`) VALUES
				('time panel', 'automatic_calculation_time', '1', 'ListConfigHandler', 0, 0, '', '".$upgrade_data."')	
				ON DUPLICATE KEY UPDATE `name`=`name`;
			";


            $upgrade_data = '{"option": [{"value": "1","text": "config_dates_calc"},{"value": "2","text": "config_hours_calc"},{"value": "3","text": "always_show_modal"}]}';

            $upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`, `options`) VALUES
				('time panel', 'automatic_calculation_start_time', '0', 'ListConfigHandler', 0, 0, '', '".$upgrade_data."')	
				ON DUPLICATE KEY UPDATE `name`=`name`;
			";
        }

        if (version_compare($installed_version, '3.6.2-beta3') < 0) {

            $upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`, `options`) VALUES
				('contact panel', 'properties_for_contact_component', '', 'ContactPropertySelectorConfigHandler', '0', '0','','contact')	
				ON DUPLICATE KEY UPDATE `name`=`name`;
			";
        }

        if (version_compare($installed_version, '3.6.2-beta6') < 0) {
            if (!$this->checkValueExists($t_prefix."custom_properties","code","prefix_code", $this->database_connection)){
                $upgrade_script .= "
				INSERT INTO ".$t_prefix."custom_properties (object_type_id, name, code, `type`,`is_special`,`description`) VALUES
                    ((SELECT id FROM ".$t_prefix."object_types WHERE name='contact'), 'Prefix', 'prefix_code', 'text', 1, '');
			";
            }


        }

        if (version_compare($installed_version, '3.6.2-beta23') < 0) {
            if (!$this->checkColumnExists($t_prefix."searchable_objects", "assoc_member_id", $this->database_connection)) {
                $upgrade_script .= "
					ALTER TABLE `".$t_prefix."searchable_objects` ADD `assoc_member_id` int(10) unsigned NOT NULL DEFAULT 0;
					ALTER TABLE `".$t_prefix."searchable_objects` ADD INDEX `assoc_member_id` (`assoc_member_id`);
				";
            }
        }

        if (version_compare($installed_version, '3.6.2-beta25') < 0) {

            $upgrade_script .= "
		  		INSERT INTO ".$t_prefix."searchable_objects (rel_object_id, column_name, content)
		  		SELECT contact_id, CONCAT('email_addres',email_type_id), email_address FROM ".$t_prefix."contact_emails
		  		ON DUPLICATE KEY UPDATE `rel_object_id`=`rel_object_id`;
  			";

        }
        
        if (version_compare($installed_version, '3.6.3-rc') < 0) {
        	if (!$this->checkValueExists($t_prefix."contact_config_options", "name", "tasksGroupsPaginationCount", $this->database_connection)) {
		        $upgrade_script .= "
					INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					('task panel', 'tasksGroupsPaginationCount', '5', 'IntegerConfigHandler', 0, 0, '')
	        		ON DUPLICATE KEY UPDATE name=name;
	        	";
        	}
        }
        
        if (version_compare($installed_version, '3.6.3-rc4') < 0) {
        	if (!$this->checkKeyExists($t_prefix."project_tasks", "start_date", $this->database_connection)) {
        		$upgrade_script .= "
			        ALTER TABLE `".$t_prefix."project_tasks`
			        ADD INDEX `start_date` (`start_date`),
			        ADD INDEX `due_date` (`due_date`),
			        ADD INDEX `completed_by_id` (`completed_by_id`);
				";
        	}
        }

		// Execute all queries
		if(!$this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
			$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
			return false;
		}
		$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
		
		$this->printMessage('Feng Office has been upgraded. You are now running Feng Office '.$this->getVersionTo().' Enjoy!');

		tpl_assign('additional_steps', $additional_upgrade_steps);

	} // execute
	
} // PaellaUpgradeScript
