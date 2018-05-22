<?php

/**
 * Bauru upgrade script will upgrade FengOffice 3.3.2-beta to FengOffice 3.4.4.52
 *
 * @package ScriptUpgrader.scripts
 * @version 1.0
 */
class BauruUpgradeScript extends ScriptUpgraderScript {

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
	 * Construct the BauruUpgradeScript
	 *
	 * @param Output $output
	 * @return BauruUpgradeScript
	 */
	function __construct(Output $output) {
		parent::__construct($output);
		$this->setVersionFrom('3.3.2-beta');
		$this->setVersionTo('3.4.4.52');
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
		if (version_compare($installed_version, '3.4-beta') < 0) {
			// dummy query
			$upgrade_script .= "
				UPDATE ".$t_prefix."config_options SET is_system=1 WHERE name='messages_per_page';
			";
		}
		
		if (version_compare($installed_version, '3.4-rc') < 0) {
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					('general', 'timeReportTaskStatus', 'all', 'StringConfigHandler', 1, 0, '')
				ON DUPLICATE KEY UPDATE name=name;
			";
		}
		
		if (version_compare($installed_version, '3.4.0.16') < 0) {
			// fix contacts that were created from emails and have some user fields
			$upgrade_script .= "
				ALTER TABLE `".$t_prefix."contact_emails`
				CHANGE `email_address` `email_address` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
			";
		}
		
		if (version_compare($installed_version, '3.4.1-beta') < 0) {
			// fix contacts that were created from emails and have some user fields
			$upgrade_script .= "
				delete from ".$t_prefix."permission_groups
				where id in (select permission_group_id from ".$t_prefix."contacts where user_type=0 and permission_group_id>0);
				
				delete from ".$t_prefix."system_permissions
				where permission_group_id in (select permission_group_id from ".$t_prefix."contacts where user_type=0 and permission_group_id>0);
				
				update ".$t_prefix."contacts set
				  permission_group_id=0,
				  token='', salt='', twister='',
				  display_name='', username='', company_id=0
				where user_type=0 and permission_group_id>0;
			";
			
			if (!$this->checkColumnExists($t_prefix."custom_properties", "is_special", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."custom_properties`
					 ADD COLUMN `is_special` BOOLEAN NOT NULL DEFAULT 0,
					 ADD COLUMN `is_disabled` BOOLEAN NOT NULL DEFAULT 0;
				";
			}
			
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				 ('mailing', 'show company logo in notifications', '1', 'BoolConfigHandler', 0, 0, NULL)
				ON DUPLICATE KEY UPDATE name=name;
			";
			
			$upgrade_script .= "
				CREATE TABLE IF NOT EXISTS `".$t_prefix."object_selector_temp_values` (
				  `user_id` int(11) NOT NULL DEFAULT 0,
				  `identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				  `value` text COLLATE utf8_unicode_ci NOT NULL,
				  PRIMARY KEY (`user_id`,`identifier`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			";
			
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."cron_events` (`name`, `recursive`, `delay`, `is_system`, `enabled`, `date`) VALUES	
				 ('clean_object_selector_temp_selection', '1', '360', '1', '1', '0000-00-00 00:00:00')
				ON DUPLICATE KEY UPDATE name=name;
			";
			
			$upgrade_script .= "
				UPDATE `".$t_prefix."contacts` SET username=TRIM(CONCAT(first_name,' ',surname))
				WHERE user_type>0 AND username='';
			";
			
			// custom property for job title
			$upgrade_script .= "
				INSERT INTO ".$t_prefix."custom_properties (`object_type_id`,`name`,`code`,`type`,`visible_by_default`,`is_special`) VALUES
				((SELECT id FROM ".$t_prefix."object_types WHERE name='contact'), 'Job title', 'job_title', 'text', 1, 1);
			";
			$upgrade_script .= "
				INSERT INTO ".$t_prefix."custom_property_values (`object_id`,`custom_property_id`,`value`) SELECT
					c.object_id, (SELECT cp.id FROM ".$t_prefix."custom_properties cp WHERE cp.code='job_title'), c.job_title
					FROM ".$t_prefix."contacts c WHERE c.job_title<>''
				ON DUPLICATE KEY UPDATE ".$t_prefix."custom_property_values.object_id=".$t_prefix."custom_property_values.object_id;
			";
			$upgrade_script .= "
				ALTER TABLE `".$t_prefix."custom_property_values`
				ADD INDEX (`object_id`, `custom_property_id`);
			";
		}
		
		if (version_compare($installed_version, '3.4.1-rc') < 0) {
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					('calendar panel', 'displayed events amount', '3', 'IntegerConfigHandler', 0, 0, '')
				ON DUPLICATE KEY UPDATE name=name;
			";
			
			$upgrade_script .= "
				UPDATE `".$t_prefix."object_types` SET `table_name` = 'project_file_revisions' WHERE `name` = 'file revision';
			";
			
			$upgrade_script .= "
				ALTER TABLE `".$t_prefix."members` ADD INDEX (`name`);
			";
		}

		if (version_compare($installed_version, '3.4.1') < 0) {
			
			if (!$this->checkColumnExists($t_prefix."project_tasks", "total_worked_time", $this->database_connection)) {
				// add total worked time column to tasks
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."project_tasks` ADD `total_worked_time` int(10) unsigned NOT NULL DEFAULT 0;
				";
				// add index by total worked time
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."project_tasks` ADD INDEX (`total_worked_time`);
				";
			}
			// calculate total worked time foreach task
			$upgrade_script .= "
				UPDATE ".$t_prefix."project_tasks SET total_worked_time = (
					SELECT (SUM(GREATEST(TIMESTAMPDIFF(MINUTE,start_time,end_time),0)) - SUM(subtract/60)) 
					FROM ".$t_prefix."timeslots ts 
					WHERE ts.rel_object_id=".$t_prefix."project_tasks.object_id
				);
			";
		}
		
		if (version_compare($installed_version, '3.4.1.1') < 0) {
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					('task panel', 'tasksUseDateFilters', '1', 'BoolConfigHandler', 0, 0, '')
				ON DUPLICATE KEY UPDATE name=name;
			";
		}

		if (version_compare($installed_version, '3.4.1.9') < 0) {
			if (!$this->checkColumnExists($t_prefix."system_permissions", "can_instantiate_templates", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."system_permissions` ADD COLUMN `can_instantiate_templates` tinyint(1) unsigned NOT NULL default '0';
	
					UPDATE `".$t_prefix."system_permissions` SET `can_instantiate_templates`=1 WHERE `permission_group_id` IN (
						SELECT permission_group_id FROM `".$t_prefix."contacts` WHERE `user_type` IN (
							SELECT id FROM `".$t_prefix."permission_groups` WHERE `type`='roles' AND `name` IN ('Super Administrator','Administrator','Manager','Executive')
						)
					);
	
					UPDATE `".$t_prefix."system_permissions` SET `can_instantiate_templates`=1 WHERE `permission_group_id` IN (
						SELECT id FROM `".$t_prefix."permission_groups` WHERE `type`='roles' AND `name` IN ('Super Administrator','Administrator','Manager','Executive')
					);
	
					ALTER TABLE `".$t_prefix."max_system_permissions` ADD COLUMN `can_instantiate_templates` tinyint(1) unsigned NOT NULL default '0';
	
					UPDATE `".$t_prefix."max_system_permissions` SET `can_instantiate_templates`=1 WHERE `permission_group_id` IN (
						SELECT id FROM `".$t_prefix."permission_groups` WHERE `type`='roles' AND `name` IN ('Super Administrator','Administrator','Manager','Executive')
					);
				";
			}
		}

		if (version_compare($installed_version, '3.4.2-beta') < 0) {
			
			if (!$this->checkColumnExists($t_prefix."reports", "is_default", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."reports` 
						ADD COLUMN `is_default` tinyint(1) NOT NULL DEFAULT 0,
						ADD COLUMN `code` varchar(255) COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '';
				";
			}
			
			$upgrade_script .= "
				ALTER TABLE `".$t_prefix."member_property_members` ADD INDEX (`property_member_id`, `member_id`);
			";
			
			$upgrade_script .= "
				UPDATE ".$t_prefix."members SET name=LTRIM(name);
			";
		}

		if (version_compare($installed_version, '3.4.2.1') < 0) {
			if ($this->checkColumnExists($t_prefix . "contacts", "first_name", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE  `" . $t_prefix . "contacts` MODIFY `first_name` varchar(255) COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '';
				";
			}

			if ($this->checkColumnExists($t_prefix . "contacts", "surname", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE  `" . $t_prefix . "contacts` MODIFY `surname` varchar(255) COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '';
				";
			}
		}

		if (version_compare($installed_version, '3.4.3-beta') < 0) {
			
			if (!$this->checkTableExists($t_prefix."dimension_associations_config", $this->database_connection)) {
		
				$upgrade_script .= "
					CREATE TABLE IF NOT EXISTS `".$t_prefix."dimension_associations_config` (
						`association_id` int(10) unsigned NOT NULL,
						`config_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
						`value` text COLLATE utf8_unicode_ci NOT NULL,
						PRIMARY KEY (`association_id`,`config_name`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
				";
			}
			
			$upgrade_script .= "
				CREATE TABLE IF NOT EXISTS `".$t_prefix."dimension_member_association_default_selections` (
					`association_id` INTEGER UNSIGNED NOT NULL,
					`member_id` INTEGER UNSIGNED NOT NULL,
					`selected_member_id` INTEGER UNSIGNED NOT NULL,
					PRIMARY KEY (`association_id`, `member_id`, `selected_member_id`)
				) ENGINE = InnoDB;
			";
			
			if (!$this->checkColumnExists("".$t_prefix."dimension_member_associations", "allows_default_selection", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."dimension_member_associations` ADD `allows_default_selection` tinyint(1) unsigned NOT NULL;
				";
			}
		}
		
		if (version_compare($installed_version, '3.4.3.7') < 0) {
			
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
			
			$upgrade_script .= "
				UPDATE ".$t_prefix."dimensions SET is_manageable=1 WHERE is_manageable=0 AND code!='feng_persons';
			";
		}
		
		
		if (version_compare($installed_version, '3.4.3.14') < 0) {
			
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."contact_config_categories` (`name`, `is_system`, `type`, `category_order`) VALUES 
				('reporting', 0, 0, 15);
		
				INSERT INTO `".$t_prefix."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				('reporting', 'report_time_colums_display', 'friendly', 'TimeFormatConfigHandler', 0, 1, '');
			";
		}

		if (version_compare($installed_version, '3.4.3.17') < 0) {

			$upgrade_script .= "
				INSERT INTO `".$t_prefix."config_options` (`category_name`,`name`,`value`,`config_handler_class`,`is_system`) VALUES
					('brand_colors', 'brand_colors_texture', 1, 'BoolConfigHandler', 0)
				ON DUPLICATE KEY UPDATE name=name;
			";
		}
		
		if (version_compare($installed_version, '3.4.4-beta') < 0) {
				
			$upgrade_script .= "
				ALTER TABLE `".TABLE_PREFIX."members`
				CHANGE `name` `name` varchar(511) COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '';
			";
			
			$upgrade_script .= "
				CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."object_type_dependencies` (
				  `object_type_id` INTEGER UNSIGNED NOT NULL,
				  `dependant_object_type_id` INTEGER UNSIGNED NOT NULL,
				  PRIMARY KEY (`object_type_id`,`dependant_object_type_id`)
				) ENGINE = InnoDB;
			";
		}
		
		if (version_compare($installed_version, '3.4.4.2') < 0) {
			$upgrade_script .= "
				INSERT INTO `".TABLE_PREFIX."config_categories` (`name`, `is_system`, `category_order`) VALUES
				('reports', 0, 5);
			";
			$upgrade_script .= "
				INSERT INTO `".TABLE_PREFIX."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				('reports', 'reports_inherit_company_address', '', 'BoolConfigHandler', '0', '0', NULL),
				('reports', 'reports_inherit_company_phones', '', 'BoolConfigHandler', '0', '0', NULL);
			";
		}
		
		if (version_compare($installed_version, '3.4.4.3') < 0) {
			if (!$this->checkColumnExists($t_prefix."custom_properties", "show_in_lists", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."custom_properties` ADD COLUMN `show_in_lists` BOOLEAN NOT NULL DEFAULT 0;
				";
				$upgrade_script .= "
					UPDATE `".$t_prefix."custom_properties` SET `show_in_lists`=`visible_by_default`;
				";
			}
		}
		
		if (version_compare($installed_version, '3.4.4.6') < 0) {
			if (!$this->checkTableExists($t_prefix."object_type_hierarchies", $this->database_connection)) {
				
				$upgrade_script .= "
					CREATE TABLE IF NOT EXISTS `".$t_prefix."object_type_hierarchies` (
					  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
					  `parent_object_type_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
					  `child_object_type_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
					  PRIMARY KEY (`id`)
					) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
				";
				
				$upgrade_script .= "
					CREATE TABLE IF NOT EXISTS `".$t_prefix."object_type_hierarchy_options` (
					  `hierarchy_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
					  `dimension_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
					  `member_type_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
					  `option` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
					  `value` text COLLATE utf8_unicode_ci,
					  PRIMARY KEY (`hierarchy_id`, `dimension_id`, `member_type_id`, `option`)
					) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
				";
			}
			
			if (!$this->checkColumnExists($t_prefix."sent_notifications", "object_id", $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."sent_notifications` ADD `object_id` int(10) NOT NULL DEFAULT '0';
				";
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."queued_emails` ADD `object_id` int(10) NOT NULL DEFAULT '0';
				";
			}
		}
		

		if (version_compare($installed_version, '3.4.4.8') < 0) {
			if (!$this->checkColumnExists($t_prefix."timeslots", 'rate_currency_id', $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."timeslots` ADD COLUMN `rate_currency_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;
					UPDATE `".$t_prefix."timeslots` SET rate_currency_id = (SELECT id FROM ".$t_prefix."currencies LIMIT 1);
					ALTER TABLE `".$t_prefix."timeslots`
						CHANGE `fixed_billing` `fixed_billing` decimal(20,3) NOT NULL DEFAULT 0,
						CHANGE `hourly_billing` `hourly_billing` decimal(20,3) NOT NULL DEFAULT 0;
				";
			}
		}

		if (version_compare($installed_version, '3.4.4.10') < 0) {
			$upgrade_script .= "
				INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
				('mailing', 'notification_from_name', '', 'StringConfigHandler', 0, 0, '');
			";
		}
		
		
		if (version_compare($installed_version, '3.4.4.12') < 0) {
			
			if (!$this->checkColumnExists($t_prefix."objects", 'timezone_id', $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."objects`
						ADD COLUMN `timezone_id` int(10) unsigned NOT NULL DEFAULT 0,
						ADD COLUMN `timezone_value` int(10) NOT NULL DEFAULT 0;
				";
			}
			if (!$this->checkColumnExists($t_prefix."contacts", 'user_timezone_id', $this->database_connection)) {
				$upgrade_script .= "
					ALTER TABLE `".$t_prefix."contacts` ADD COLUMN `user_timezone_id` int(10) unsigned NOT NULL DEFAULT 0;
				";
			}
			
			if (!$this->checkValueExists($t_prefix."config_options", "name", "default_timezone", $this->database_connection)) {
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					('system', 'default_timezone', '', 'TimezoneConfigHandler', 1, 0, '');
				";
			}
			
			if (!$this->checkTableExists($t_prefix."countries", $this->database_connection)) {
				
				$upgrade_script .= "
					CREATE TABLE IF NOT EXISTS `".$t_prefix."countries` (
					  `code` char(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
					  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
					  PRIMARY KEY `code` (`code`),
					  KEY `name` (`name`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
				";
				
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."countries` (`code`, `name`) VALUES
					('AF',	'Afghanistan'),
					('AX',	'Aland Islands'),
					('AL',	'Albania'),
					('DZ',	'Algeria'),
					('AS',	'American Samoa'),
					('AD',	'Andorra'),
					('AO',	'Angola'),
					('AI',	'Anguilla'),
					('AQ',	'Antarctica'),
					('AG',	'Antigua and Barbuda'),
					('AR',	'Argentina'),
					('AM',	'Armenia'),
					('AW',	'Aruba'),
					('AU',	'Australia'),
					('AT',	'Austria'),
					('AZ',	'Azerbaijan'),
					('BS',	'Bahamas'),
					('BH',	'Bahrain'),
					('BD',	'Bangladesh'),
					('BB',	'Barbados'),
					('BY',	'Belarus'),
					('BE',	'Belgium'),
					('BZ',	'Belize'),
					('BJ',	'Benin'),
					('BM',	'Bermuda'),
					('BT',	'Bhutan'),
					('BO',	'Bolivia'),
					('BQ',	'Bonaire, Saint Eustatius and Saba '),
					('BA',	'Bosnia and Herzegovina'),
					('BW',	'Botswana'),
					('BV',	'Bouvet Island'),
					('BR',	'Brazil'),
					('IO',	'British Indian Ocean Territory'),
					('VG',	'British Virgin Islands'),
					('BN',	'Brunei'),
					('BG',	'Bulgaria'),
					('BF',	'Burkina Faso'),
					('BI',	'Burundi'),
					('KH',	'Cambodia'),
					('CM',	'Cameroon'),
					('CA',	'Canada'),
					('CV',	'Cape Verde'),
					('KY',	'Cayman Islands'),
					('CF',	'Central African Republic'),
					('TD',	'Chad'),
					('CL',	'Chile'),
					('CN',	'China'),
					('CX',	'Christmas Island'),
					('CC',	'Cocos Islands'),
					('CO',	'Colombia'),
					('KM',	'Comoros'),
					('CK',	'Cook Islands'),
					('CR',	'Costa Rica'),
					('HR',	'Croatia'),
					('CU',	'Cuba'),
					('CW',	'Cura├ºao'),
					('CY',	'Cyprus'),
					('CZ',	'Czech Republic'),
					('CD',	'Democratic Republic of the Congo'),
					('DK',	'Denmark'),
					('DJ',	'Djibouti'),
					('DM',	'Dominica'),
					('DO',	'Dominican Republic'),
					('TL',	'East Timor'),
					('EC',	'Ecuador'),
					('EG',	'Egypt'),
					('SV',	'El Salvador'),
					('GQ',	'Equatorial Guinea'),
					('ER',	'Eritrea'),
					('EE',	'Estonia'),
					('ET',	'Ethiopia'),
					('FK',	'Falkland Islands'),
					('FO',	'Faroe Islands'),
					('FJ',	'Fiji'),
					('FI',	'Finland'),
					('FR',	'France'),
					('GF',	'French Guiana'),
					('PF',	'French Polynesia'),
					('TF',	'French Southern Territories'),
					('GA',	'Gabon'),
					('GM',	'Gambia'),
					('GE',	'Georgia'),
					('DE',	'Germany'),
					('GH',	'Ghana'),
					('GI',	'Gibraltar'),
					('GR',	'Greece'),
					('GL',	'Greenland'),
					('GD',	'Grenada'),
					('GP',	'Guadeloupe'),
					('GU',	'Guam'),
					('GT',	'Guatemala'),
					('GG',	'Guernsey'),
					('GN',	'Guinea'),
					('GW',	'Guinea-Bissau'),
					('GY',	'Guyana'),
					('HT',	'Haiti'),
					('HM',	'Heard Island and McDonald Islands'),
					('HN',	'Honduras'),
					('HK',	'Hong Kong'),
					('HU',	'Hungary'),
					('IS',	'Iceland'),
					('IN',	'India'),
					('ID',	'Indonesia'),
					('IR',	'Iran'),
					('IQ',	'Iraq'),
					('IE',	'Ireland'),
					('IM',	'Isle of Man'),
					('IL',	'Israel'),
					('IT',	'Italy'),
					('CI',	'Ivory Coast'),
					('JM',	'Jamaica'),
					('JP',	'Japan'),
					('JE',	'Jersey'),
					('JO',	'Jordan'),
					('KZ',	'Kazakhstan'),
					('KE',	'Kenya'),
					('KI',	'Kiribati'),
					('XK',	'Kosovo'),
					('KW',	'Kuwait'),
					('KG',	'Kyrgyzstan'),
					('LA',	'Laos'),
					('LV',	'Latvia'),
					('LB',	'Lebanon'),
					('LS',	'Lesotho'),
					('LR',	'Liberia'),
					('LY',	'Libya'),
					('LI',	'Liechtenstein'),
					('LT',	'Lithuania'),
					('LU',	'Luxembourg'),
					('MO',	'Macao'),
					('MK',	'Macedonia'),
					('MG',	'Madagascar'),
					('MW',	'Malawi'),
					('MY',	'Malaysia'),
					('MV',	'Maldives'),
					('ML',	'Mali'),
					('MT',	'Malta'),
					('MH',	'Marshall Islands'),
					('MQ',	'Martinique'),
					('MR',	'Mauritania'),
					('MU',	'Mauritius'),
					('YT',	'Mayotte'),
					('MX',	'Mexico'),
					('FM',	'Micronesia'),
					('MD',	'Moldova'),
					('MC',	'Monaco'),
					('MN',	'Mongolia'),
					('ME',	'Montenegro'),
					('MS',	'Montserrat'),
					('MA',	'Morocco'),
					('MZ',	'Mozambique'),
					('MM',	'Myanmar'),
					('NA',	'Namibia'),
					('NR',	'Nauru'),
					('NP',	'Nepal'),
					('NL',	'Netherlands'),
					('AN',	'Netherlands Antilles'),
					('NC',	'New Caledonia'),
					('NZ',	'New Zealand'),
					('NI',	'Nicaragua'),
					('NE',	'Niger'),
					('NG',	'Nigeria'),
					('NU',	'Niue'),
					('NF',	'Norfolk Island'),
					('KP',	'North Korea'),
					('MP',	'Northern Mariana Islands'),
					('NO',	'Norway'),
					('OM',	'Oman'),
					('PK',	'Pakistan'),
					('PW',	'Palau'),
					('PS',	'Palestinian Territory'),
					('PA',	'Panama'),
					('PG',	'Papua New Guinea'),
					('PY',	'Paraguay'),
					('PE',	'Peru'),
					('PH',	'Philippines'),
					('PN',	'Pitcairn'),
					('PL',	'Poland'),
					('PT',	'Portugal'),
					('PR',	'Puerto Rico'),
					('QA',	'Qatar'),
					('CG',	'Republic of the Congo'),
					('RE',	'Reunion'),
					('RO',	'Romania'),
					('RU',	'Russia'),
					('RW',	'Rwanda'),
					('BL',	'Saint Barth├®lemy'),
					('SH',	'Saint Helena'),
					('KN',	'Saint Kitts and Nevis'),
					('LC',	'Saint Lucia'),
					('MF',	'Saint Martin'),
					('PM',	'Saint Pierre and Miquelon'),
					('VC',	'Saint Vincent and the Grenadines'),
					('WS',	'Samoa'),
					('SM',	'San Marino'),
					('ST',	'Sao Tome and Principe'),
					('SA',	'Saudi Arabia'),
					('SN',	'Senegal'),
					('RS',	'Serbia'),
					('CS',	'Serbia and Montenegro'),
					('SC',	'Seychelles'),
					('SL',	'Sierra Leone'),
					('SG',	'Singapore'),
					('SX',	'Sint Maarten'),
					('SK',	'Slovakia'),
					('SI',	'Slovenia'),
					('SB',	'Solomon Islands'),
					('SO',	'Somalia'),
					('ZA',	'South Africa'),
					('GS',	'South Georgia and the South Sandwich Islands'),
					('KR',	'South Korea'),
					('SS',	'South Sudan'),
					('ES',	'Spain'),
					('LK',	'Sri Lanka'),
					('SD',	'Sudan'),
					('SR',	'Suriname'),
					('SJ',	'Svalbard and Jan Mayen'),
					('SZ',	'Swaziland'),
					('SE',	'Sweden'),
					('CH',	'Switzerland'),
					('SY',	'Syria'),
					('TW',	'Taiwan'),
					('TJ',	'Tajikistan'),
					('TZ',	'Tanzania'),
					('TH',	'Thailand'),
					('TG',	'Togo'),
					('TK',	'Tokelau'),
					('TO',	'Tonga'),
					('TT',	'Trinidad and Tobago'),
					('TN',	'Tunisia'),
					('TR',	'Turkey'),
					('TM',	'Turkmenistan'),
					('TC',	'Turks and Caicos Islands'),
					('TV',	'Tuvalu'),
					('VI',	'U.S. Virgin Islands'),
					('UG',	'Uganda'),
					('UA',	'Ukraine'),
					('AE',	'United Arab Emirates'),
					('GB',	'United Kingdom'),
					('US',	'United States'),
					('UM',	'United States Minor Outlying Islands'),
					('UY',	'Uruguay'),
					('UZ',	'Uzbekistan'),
					('VU',	'Vanuatu'),
					('VA',	'Vatican'),
					('VE',	'Venezuela'),
					('VN',	'Vietnam'),
					('WF',	'Wallis and Futuna'),
					('EH',	'Western Sahara'),
					('YE',	'Yemen'),
					('ZM',	'Zambia'),
					('ZW',	'Zimbabwe');
				";
			}
			
			if (!$this->checkTableExists($t_prefix."timezones", $this->database_connection)) {
			
				$upgrade_script .= "
					CREATE TABLE IF NOT EXISTS `".$t_prefix."timezones` (
					  `id` int(10) NOT NULL AUTO_INCREMENT,
					  `country_code` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
					  `name` varchar(35) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
					  `has_dst` tinyint(1) NOT NULL DEFAULT '0',
					  `gmt_offset` int(10) NOT NULL DEFAULT '0',
					  `gmt_dst_offset` int(10) NOT NULL DEFAULT '0',
					  `using_dst` tinyint(1) NOT NULL DEFAULT '0',
					  PRIMARY KEY (`id`),
					  KEY `country_code` (`country_code`),
					  KEY `name` (`name`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
				";
				
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."timezones` (`id`, `country_code`, `name`, `has_dst`, `gmt_offset`, `gmt_dst_offset`, `using_dst`) VALUES
					(1,	'AD',	'Europe/Andorra',	1,	3600,	7200,	0),
					(2,	'AE',	'Asia/Dubai',	0,	14400,	14400,	0),
					(3,	'AF',	'Asia/Kabul',	0,	16200,	16200,	0),
					(4,	'AG',	'America/Antigua',	0,	-14400,	-14400,	0),
					(5,	'AI',	'America/Anguilla',	0,	-14400,	-14400,	0),
					(6,	'AL',	'Europe/Tirane',	1,	3600,	7200,	0),
					(7,	'AM',	'Asia/Yerevan',	0,	14400,	18000,	0),
					(8,	'AO',	'Africa/Luanda',	0,	3600,	3600,	0),
					(9,	'AQ',	'Antarctica/McMurdo',	1,	43200,	46800,	1),
					(10,	'AQ',	'Antarctica/Casey',	0,	28800,	28800,	0),
					(11,	'AQ',	'Antarctica/Davis',	0,	25200,	25200,	0),
					(12,	'AQ',	'Antarctica/DumontDUrville',	0,	36000,	36000,	0),
					(13,	'AQ',	'Antarctica/Mawson',	0,	18000,	18000,	0),
					(14,	'AQ',	'Antarctica/Palmer',	1,	-14400,	-10800,	1),
					(15,	'AQ',	'Antarctica/Rothera',	0,	-10800,	-10800,	0),
					(16,	'AQ',	'Antarctica/Syowa',	0,	10800,	10800,	0),
					(17,	'AQ',	'Antarctica/Troll',	0,	0,	7200,	0),
					(18,	'AQ',	'Antarctica/Vostok',	0,	21600,	21600,	0),
					(19,	'AR',	'America/Argentina/Buenos_Aires',	0,	-10800,	-7200,	0),
					(20,	'AR',	'America/Argentina/Cordoba',	0,	-10800,	-7200,	0),
					(21,	'AR',	'America/Argentina/Salta',	0,	-10800,	-7200,	0),
					(22,	'AR',	'America/Argentina/Jujuy',	0,	-10800,	-7200,	0),
					(23,	'AR',	'America/Argentina/Tucuman',	0,	-10800,	-7200,	0),
					(24,	'AR',	'America/Argentina/Catamarca',	0,	-10800,	-7200,	0),
					(25,	'AR',	'America/Argentina/La_Rioja',	0,	-10800,	-7200,	0),
					(26,	'AR',	'America/Argentina/San_Juan',	0,	-10800,	-7200,	0),
					(27,	'AR',	'America/Argentina/Mendoza',	0,	-10800,	-7200,	0),
					(28,	'AR',	'America/Argentina/San_Luis',	0,	-10800,	-10800,	0),
					(29,	'AR',	'America/Argentina/Rio_Gallegos',	0,	-10800,	-7200,	0),
					(30,	'AR',	'America/Argentina/Ushuaia',	0,	-10800,	-7200,	0),
					(31,	'AS',	'Pacific/Pago_Pago',	0,	-39600,	-39600,	0),
					(32,	'AT',	'Europe/Vienna',	1,	3600,	7200,	0),
					(33,	'AU',	'Australia/Lord_Howe',	1,	37800,	39600,	1),
					(34,	'AU',	'Antarctica/Macquarie',	0,	39600,	39600,	0),
					(35,	'AU',	'Australia/Hobart',	1,	36000,	39600,	1),
					(36,	'AU',	'Australia/Currie',	1,	36000,	39600,	1),
					(37,	'AU',	'Australia/Melbourne',	1,	36000,	39600,	1),
					(38,	'AU',	'Australia/Sydney',	1,	36000,	39600,	1),
					(39,	'AU',	'Australia/Broken_Hill',	1,	34200,	37800,	1),
					(40,	'AU',	'Australia/Brisbane',	0,	36000,	39600,	0),
					(41,	'AU',	'Australia/Lindeman',	0,	36000,	39600,	0),
					(42,	'AU',	'Australia/Adelaide',	1,	34200,	37800,	1),
					(43,	'AU',	'Australia/Darwin',	0,	34200,	37800,	0),
					(44,	'AU',	'Australia/Perth',	0,	28800,	32400,	0),
					(45,	'AU',	'Australia/Eucla',	0,	31500,	35100,	0),
					(46,	'AW',	'America/Aruba',	0,	-14400,	-14400,	0),
					(47,	'AX',	'Europe/Mariehamn',	1,	7200,	10800,	0),
					(48,	'AZ',	'Asia/Baku',	1,	14400,	18000,	0),
					(49,	'BA',	'Europe/Sarajevo',	1,	3600,	7200,	0),
					(50,	'BB',	'America/Barbados',	0,	-14400,	-10800,	0),
					(51,	'BD',	'Asia/Dhaka',	0,	21600,	25200,	0),
					(52,	'BE',	'Europe/Brussels',	1,	3600,	7200,	0),
					(53,	'BF',	'Africa/Ouagadougou',	0,	0,	0,	0),
					(54,	'BG',	'Europe/Sofia',	1,	7200,	10800,	0),
					(55,	'BH',	'Asia/Bahrain',	0,	10800,	10800,	0),
					(56,	'BI',	'Africa/Bujumbura',	0,	7200,	7200,	0),
					(57,	'BJ',	'Africa/Porto-Novo',	0,	3600,	3600,	0),
					(58,	'BL',	'America/St_Barthelemy',	0,	-14400,	-14400,	0),
					(59,	'BM',	'Atlantic/Bermuda',	1,	-14400,	-10800,	0),
					(60,	'BN',	'Asia/Brunei',	0,	28800,	28800,	0),
					(61,	'BO',	'America/La_Paz',	0,	-14400,	-12756,	0),
					(62,	'BQ',	'America/Kralendijk',	0,	-14400,	-14400,	0),
					(63,	'BR',	'America/Noronha',	0,	-7200,	-3600,	0),
					(64,	'BR',	'America/Belem',	0,	-10800,	-7200,	0),
					(65,	'BR',	'America/Fortaleza',	0,	-10800,	-7200,	0),
					(66,	'BR',	'America/Recife',	0,	-10800,	-7200,	0),
					(67,	'BR',	'America/Araguaina',	1,	-10800,	-7200,	0),
					(68,	'BR',	'America/Maceio',	0,	-10800,	-7200,	0),
					(69,	'BR',	'America/Bahia',	0,	-10800,	-7200,	0),
					(70,	'BR',	'America/Sao_Paulo',	1,	-10800,	-7200,	1),
					(71,	'BR',	'America/Campo_Grande',	1,	-14400,	-10800,	1),
					(72,	'BR',	'America/Cuiaba',	1,	-14400,	-10800,	1),
					(73,	'BR',	'America/Santarem',	0,	-10800,	-10800,	0),
					(74,	'BR',	'America/Porto_Velho',	0,	-14400,	-10800,	0),
					(75,	'BR',	'America/Boa_Vista',	0,	-14400,	-10800,	0),
					(76,	'BR',	'America/Manaus',	0,	-14400,	-10800,	0),
					(77,	'BR',	'America/Eirunepe',	0,	-18000,	-14400,	0),
					(78,	'BR',	'America/Rio_Branco',	0,	-18000,	-14400,	0),
					(79,	'BS',	'America/Nassau',	1,	-18000,	-14400,	0),
					(80,	'BT',	'Asia/Thimphu',	0,	21600,	21600,	0),
					(81,	'BW',	'Africa/Gaborone',	0,	7200,	7200,	0),
					(82,	'BY',	'Europe/Minsk',	0,	10800,	10800,	0),
					(83,	'BZ',	'America/Belize',	0,	-21600,	-18000,	0),
					(84,	'CA',	'America/St_Johns',	1,	-12600,	-9000,	0),
					(85,	'CA',	'America/Halifax',	1,	-14400,	-10800,	0),
					(86,	'CA',	'America/Glace_Bay',	1,	-14400,	-10800,	0),
					(87,	'CA',	'America/Moncton',	1,	-14400,	-10800,	0),
					(88,	'CA',	'America/Goose_Bay',	1,	-14400,	-10800,	0),
					(89,	'CA',	'America/Blanc-Sablon',	0,	-14400,	-10800,	0),
					(90,	'CA',	'America/Toronto',	1,	-18000,	-14400,	0),
					(91,	'CA',	'America/Nipigon',	1,	-18000,	-14400,	0),
					(92,	'CA',	'America/Thunder_Bay',	1,	-18000,	-14400,	0),
					(93,	'CA',	'America/Iqaluit',	1,	-18000,	-14400,	0),
					(94,	'CA',	'America/Pangnirtung',	1,	-18000,	-14400,	0),
					(95,	'CA',	'America/Atikokan',	0,	-18000,	-18000,	0),
					(96,	'CA',	'America/Winnipeg',	1,	-21600,	-18000,	0),
					(97,	'CA',	'America/Rainy_River',	1,	-21600,	-18000,	0),
					(98,	'CA',	'America/Resolute',	1,	-21600,	-18000,	0),
					(99,	'CA',	'America/Rankin_Inlet',	1,	-21600,	-18000,	0),
					(100,	'CA',	'America/Regina',	0,	-21600,	-21600,	0),
					(101,	'CA',	'America/Swift_Current',	0,	-21600,	-21600,	0),
					(102,	'CA',	'America/Edmonton',	1,	-25200,	-21600,	0),
					(103,	'CA',	'America/Cambridge_Bay',	1,	-25200,	-21600,	0),
					(104,	'CA',	'America/Yellowknife',	1,	-25200,	-21600,	0),
					(105,	'CA',	'America/Inuvik',	1,	-25200,	-21600,	0),
					(106,	'CA',	'America/Creston',	0,	-25200,	-25200,	0),
					(107,	'CA',	'America/Dawson_Creek',	0,	-25200,	-25200,	0),
					(108,	'CA',	'America/Fort_Nelson',	1,	-25200,	-25200,	0),
					(109,	'CA',	'America/Vancouver',	1,	-28800,	-25200,	0),
					(110,	'CA',	'America/Whitehorse',	1,	-28800,	-25200,	0),
					(111,	'CA',	'America/Dawson',	1,	-28800,	-25200,	0),
					(112,	'CC',	'Indian/Cocos',	0,	23400,	23400,	0),
					(113,	'CD',	'Africa/Kinshasa',	0,	3600,	3600,	0),
					(114,	'CD',	'Africa/Lubumbashi',	0,	7200,	7200,	0),
					(115,	'CF',	'Africa/Bangui',	0,	3600,	3600,	0),
					(116,	'CG',	'Africa/Brazzaville',	0,	3600,	3600,	0),
					(117,	'CH',	'Europe/Zurich',	1,	3600,	7200,	0),
					(118,	'CI',	'Africa/Abidjan',	0,	0,	0,	0),
					(119,	'CK',	'Pacific/Rarotonga',	0,	-36000,	-34200,	0),
					(120,	'CL',	'America/Santiago',	1,	-14400,	-10800,	1),
					(121,	'CL',	'Pacific/Easter',	1,	-21600,	-18000,	1),
					(122,	'CM',	'Africa/Douala',	0,	3600,	3600,	0),
					(123,	'CN',	'Asia/Shanghai',	0,	28800,	32400,	0),
					(124,	'CN',	'Asia/Urumqi',	0,	21600,	21600,	0),
					(125,	'CO',	'America/Bogota',	0,	-18000,	-14400,	0),
					(126,	'CR',	'America/Costa_Rica',	0,	-21600,	-18000,	0),
					(127,	'CU',	'America/Havana',	1,	-18000,	-14400,	0),
					(128,	'CV',	'Atlantic/Cape_Verde',	0,	-3600,	-3600,	0),
					(129,	'CW',	'America/Curacao',	0,	-14400,	-14400,	0),
					(130,	'CX',	'Indian/Christmas',	0,	25200,	25200,	0),
					(131,	'CY',	'Asia/Nicosia',	1,	7200,	10800,	0),
					(132,	'CZ',	'Europe/Prague',	1,	3600,	7200,	0),
					(133,	'DE',	'Europe/Berlin',	1,	3600,	7200,	0),
					(134,	'DE',	'Europe/Busingen',	1,	3600,	7200,	0),
					(135,	'DJ',	'Africa/Djibouti',	0,	10800,	10800,	0),
					(136,	'DK',	'Europe/Copenhagen',	1,	3600,	7200,	0),
					(137,	'DM',	'America/Dominica',	0,	-14400,	-14400,	0),
					(138,	'DO',	'America/Santo_Domingo',	0,	-14400,	-16200,	0),
					(139,	'DZ',	'Africa/Algiers',	0,	3600,	3600,	0),
					(140,	'EC',	'America/Guayaquil',	0,	-18000,	-18000,	0),
					(141,	'EC',	'Pacific/Galapagos',	0,	-21600,	-21600,	0),
					(142,	'EE',	'Europe/Tallinn',	1,	7200,	10800,	0),
					(143,	'EG',	'Africa/Cairo',	1,	7200,	10800,	0),
					(144,	'EH',	'Africa/El_Aaiun',	1,	0,	3600,	0),
					(145,	'ER',	'Africa/Asmara',	0,	10800,	10800,	0),
					(146,	'ES',	'Europe/Madrid',	1,	3600,	7200,	0),
					(147,	'ES',	'Africa/Ceuta',	1,	3600,	7200,	0),
					(148,	'ES',	'Atlantic/Canary',	1,	0,	3600,	0),
					(149,	'ET',	'Africa/Addis_Ababa',	0,	10800,	10800,	0),
					(150,	'FI',	'Europe/Helsinki',	1,	7200,	10800,	0),
					(151,	'FJ',	'Pacific/Fiji',	1,	43200,	46800,	1),
					(152,	'FK',	'Atlantic/Stanley',	0,	-10800,	-10800,	0),
					(153,	'FM',	'Pacific/Chuuk',	0,	36000,	36000,	0),
					(154,	'FM',	'Pacific/Pohnpei',	0,	39600,	39600,	0),
					(155,	'FM',	'Pacific/Kosrae',	0,	39600,	39600,	0),
					(156,	'FO',	'Atlantic/Faroe',	1,	0,	3600,	0),
					(157,	'FR',	'Europe/Paris',	1,	3600,	7200,	0),
					(158,	'GA',	'Africa/Libreville',	0,	3600,	3600,	0),
					(159,	'GB',	'Europe/London',	1,	0,	3600,	1),
					(160,	'GD',	'America/Grenada',	0,	-14400,	-14400,	0),
					(161,	'GE',	'Asia/Tbilisi',	0,	14400,	14400,	0),
					(162,	'GF',	'America/Cayenne',	0,	-10800,	-10800,	0),
					(163,	'GG',	'Europe/Guernsey',	1,	0,	3600,	0),
					(164,	'GH',	'Africa/Accra',	0,	0,	1200,	0),
					(165,	'GI',	'Europe/Gibraltar',	1,	3600,	7200,	0),
					(166,	'GL',	'America/Godthab',	1,	-10800,	-7200,	0),
					(167,	'GL',	'America/Danmarkshavn',	0,	0,	-7200,	0),
					(168,	'GL',	'America/Scoresbysund',	1,	-3600,	0,	0),
					(169,	'GL',	'America/Thule',	1,	-14400,	-10800,	0),
					(170,	'GM',	'Africa/Banjul',	0,	0,	0,	0),
					(171,	'GN',	'Africa/Conakry',	0,	0,	0,	0),
					(172,	'GP',	'America/Guadeloupe',	0,	-14400,	-14400,	0),
					(173,	'GQ',	'Africa/Malabo',	0,	3600,	3600,	0),
					(174,	'GR',	'Europe/Athens',	1,	7200,	10800,	0),
					(175,	'GS',	'Atlantic/South_Georgia',	0,	-7200,	-7200,	0),
					(176,	'GT',	'America/Guatemala',	0,	-21600,	-18000,	0),
					(177,	'GU',	'Pacific/Guam',	0,	36000,	36000,	0),
					(178,	'GW',	'Africa/Bissau',	0,	0,	0,	0),
					(179,	'GY',	'America/Guyana',	0,	-14400,	-14400,	0),
					(180,	'HK',	'Asia/Hong_Kong',	0,	28800,	32400,	0),
					(181,	'HN',	'America/Tegucigalpa',	0,	-21600,	-18000,	0),
					(182,	'HR',	'Europe/Zagreb',	1,	3600,	7200,	0),
					(183,	'HT',	'America/Port-au-Prince',	1,	-18000,	-14400,	0),
					(184,	'HU',	'Europe/Budapest',	1,	3600,	7200,	0),
					(185,	'ID',	'Asia/Jakarta',	0,	25200,	25200,	0),
					(186,	'ID',	'Asia/Pontianak',	0,	25200,	25200,	0),
					(187,	'ID',	'Asia/Makassar',	0,	28800,	28800,	0),
					(188,	'ID',	'Asia/Jayapura',	0,	32400,	32400,	0),
					(189,	'IE',	'Europe/Dublin',	1,	0,	3600,	0),
					(190,	'IL',	'Asia/Jerusalem',	1,	7200,	10800,	0),
					(191,	'IM',	'Europe/Isle_of_Man',	1,	0,	3600,	0),
					(192,	'IN',	'Asia/Kolkata',	0,	19800,	23400,	0),
					(193,	'IO',	'Indian/Chagos',	0,	21600,	21600,	0),
					(194,	'IQ',	'Asia/Baghdad',	0,	10800,	14400,	0),
					(195,	'IR',	'Asia/Tehran',	1,	12600,	16200,	0),
					(196,	'IS',	'Atlantic/Reykjavik',	0,	0,	0,	0),
					(197,	'IT',	'Europe/Rome',	1,	3600,	7200,	0),
					(198,	'JE',	'Europe/Jersey',	1,	0,	3600,	0),
					(199,	'JM',	'America/Jamaica',	0,	-18000,	-14400,	0),
					(200,	'JO',	'Asia/Amman',	1,	7200,	10800,	0),
					(201,	'JP',	'Asia/Tokyo',	0,	32400,	36000,	0),
					(202,	'KE',	'Africa/Nairobi',	0,	10800,	10800,	0),
					(203,	'KG',	'Asia/Bishkek',	0,	18000,	21600,	0),
					(204,	'KH',	'Asia/Phnom_Penh',	0,	25200,	25200,	0),
					(205,	'KI',	'Pacific/Tarawa',	0,	43200,	43200,	0),
					(206,	'KI',	'Pacific/Enderbury',	0,	46800,	46800,	0),
					(207,	'KI',	'Pacific/Kiritimati',	0,	50400,	50400,	0),
					(208,	'KM',	'Indian/Comoro',	0,	10800,	10800,	0),
					(209,	'KN',	'America/St_Kitts',	0,	-14400,	-14400,	0),
					(210,	'KP',	'Asia/Pyongyang',	0,	30600,	30600,	0),
					(211,	'KR',	'Asia/Seoul',	0,	32400,	36000,	0),
					(212,	'KW',	'Asia/Kuwait',	0,	10800,	10800,	0),
					(213,	'KY',	'America/Cayman',	0,	-18000,	-18000,	0),
					(214,	'KZ',	'Asia/Almaty',	0,	21600,	25200,	0),
					(215,	'KZ',	'Asia/Qyzylorda',	0,	18000,	21600,	0),
					(216,	'KZ',	'Asia/Aqtobe',	0,	18000,	21600,	0),
					(217,	'KZ',	'Asia/Aqtau',	0,	14400,	18000,	0),
					(218,	'KZ',	'Asia/Oral',	0,	14400,	18000,	0),
					(219,	'LA',	'Asia/Vientiane',	0,	25200,	25200,	0),
					(220,	'LB',	'Asia/Beirut',	1,	7200,	10800,	0),
					(221,	'LC',	'America/St_Lucia',	0,	-14400,	-14400,	0),
					(222,	'LI',	'Europe/Vaduz',	1,	3600,	7200,	0),
					(223,	'LK',	'Asia/Colombo',	0,	19800,	23400,	0),
					(224,	'LR',	'Africa/Monrovia',	0,	0,	0,	0),
					(225,	'LS',	'Africa/Maseru',	0,	7200,	10800,	0),
					(226,	'LT',	'Europe/Vilnius',	1,	7200,	10800,	0),
					(227,	'LU',	'Europe/Luxembourg',	1,	3600,	7200,	0),
					(228,	'LV',	'Europe/Riga',	1,	7200,	10800,	0),
					(229,	'LY',	'Africa/Tripoli',	1,	7200,	7200,	0),
					(230,	'MA',	'Africa/Casablanca',	1,	0,	3600,	0),
					(231,	'MC',	'Europe/Monaco',	1,	3600,	7200,	0),
					(232,	'MD',	'Europe/Chisinau',	1,	7200,	10800,	0),
					(233,	'ME',	'Europe/Podgorica',	1,	3600,	7200,	0),
					(234,	'MF',	'America/Marigot',	0,	-14400,	-14400,	0),
					(235,	'MG',	'Indian/Antananarivo',	0,	10800,	10800,	0),
					(236,	'MH',	'Pacific/Majuro',	0,	43200,	43200,	0),
					(237,	'MH',	'Pacific/Kwajalein',	0,	43200,	43200,	0),
					(238,	'MK',	'Europe/Skopje',	1,	3600,	7200,	0),
					(239,	'ML',	'Africa/Bamako',	0,	0,	0,	0),
					(240,	'MM',	'Asia/Yangon',	0,	23400,	23400,	0),
					(241,	'MN',	'Asia/Ulaanbaatar',	1,	28800,	32400,	0),
					(242,	'MN',	'Asia/Hovd',	1,	25200,	28800,	0),
					(243,	'MN',	'Asia/Choibalsan',	1,	28800,	32400,	0),
					(244,	'MO',	'Asia/Macau',	0,	28800,	32400,	0),
					(245,	'MP',	'Pacific/Saipan',	0,	36000,	36000,	0),
					(246,	'MQ',	'America/Martinique',	0,	-14400,	-10800,	0),
					(247,	'MR',	'Africa/Nouakchott',	0,	0,	0,	0),
					(248,	'MS',	'America/Montserrat',	0,	-14400,	-14400,	0),
					(249,	'MT',	'Europe/Malta',	1,	3600,	7200,	0),
					(250,	'MU',	'Indian/Mauritius',	0,	14400,	18000,	0),
					(251,	'MV',	'Indian/Maldives',	0,	18000,	18000,	0),
					(252,	'MW',	'Africa/Blantyre',	0,	7200,	7200,	0),
					(253,	'MX',	'America/Mexico_City',	1,	-21600,	-18000,	0),
					(254,	'MX',	'America/Cancun',	1,	-18000,	-18000,	0),
					(255,	'MX',	'America/Merida',	1,	-21600,	-18000,	0),
					(256,	'MX',	'America/Monterrey',	1,	-21600,	-18000,	0),
					(257,	'MX',	'America/Matamoros',	1,	-21600,	-18000,	0),
					(258,	'MX',	'America/Mazatlan',	1,	-25200,	-21600,	0),
					(259,	'MX',	'America/Chihuahua',	1,	-25200,	-21600,	0),
					(260,	'MX',	'America/Ojinaga',	1,	-25200,	-21600,	0),
					(261,	'MX',	'America/Hermosillo',	0,	-25200,	-21600,	0),
					(262,	'MX',	'America/Tijuana',	1,	-28800,	-25200,	0),
					(263,	'MX',	'America/Bahia_Banderas',	1,	-21600,	-18000,	0),
					(264,	'MY',	'Asia/Kuala_Lumpur',	0,	28800,	26400,	0),
					(265,	'MY',	'Asia/Kuching',	0,	28800,	30000,	0),
					(266,	'MZ',	'Africa/Maputo',	0,	7200,	7200,	0),
					(267,	'NA',	'Africa/Windhoek',	1,	3600,	7200,	1),
					(268,	'NC',	'Pacific/Noumea',	0,	39600,	43200,	0),
					(269,	'NE',	'Africa/Niamey',	0,	3600,	3600,	0),
					(270,	'NF',	'Pacific/Norfolk',	0,	39600,	45000,	0),
					(271,	'NG',	'Africa/Lagos',	0,	3600,	3600,	0),
					(272,	'NI',	'America/Managua',	0,	-21600,	-18000,	0),
					(273,	'NL',	'Europe/Amsterdam',	1,	3600,	7200,	0),
					(274,	'NO',	'Europe/Oslo',	1,	3600,	7200,	0),
					(275,	'NP',	'Asia/Kathmandu',	0,	20700,	20700,	0),
					(276,	'NR',	'Pacific/Nauru',	0,	43200,	43200,	0),
					(277,	'NU',	'Pacific/Niue',	0,	-39600,	-39600,	0),
					(278,	'NZ',	'Pacific/Auckland',	1,	43200,	46800,	1),
					(279,	'NZ',	'Pacific/Chatham',	1,	45900,	49500,	1),
					(280,	'OM',	'Asia/Muscat',	0,	14400,	14400,	0),
					(281,	'PA',	'America/Panama',	0,	-18000,	-18000,	0),
					(282,	'PE',	'America/Lima',	0,	-18000,	-14400,	0),
					(283,	'PF',	'Pacific/Tahiti',	0,	-36000,	-36000,	0),
					(284,	'PF',	'Pacific/Marquesas',	0,	-34200,	-34200,	0),
					(285,	'PF',	'Pacific/Gambier',	0,	-32400,	-32400,	0),
					(286,	'PG',	'Pacific/Port_Moresby',	0,	36000,	36000,	0),
					(287,	'PG',	'Pacific/Bougainville',	0,	39600,	39600,	0),
					(288,	'PH',	'Asia/Manila',	0,	28800,	32400,	0),
					(289,	'PK',	'Asia/Karachi',	0,	18000,	21600,	0),
					(290,	'PL',	'Europe/Warsaw',	1,	3600,	7200,	0),
					(291,	'PM',	'America/Miquelon',	1,	-10800,	-7200,	0),
					(292,	'PN',	'Pacific/Pitcairn',	0,	-28800,	-28800,	0),
					(293,	'PR',	'America/Puerto_Rico',	0,	-14400,	-10800,	0),
					(294,	'PS',	'Asia/Gaza',	1,	7200,	10800,	0),
					(295,	'PS',	'Asia/Hebron',	1,	7200,	10800,	0),
					(296,	'PT',	'Europe/Lisbon',	1,	0,	3600,	0),
					(297,	'PT',	'Atlantic/Madeira',	1,	0,	3600,	0),
					(298,	'PT',	'Atlantic/Azores',	1,	-3600,	0,	0),
					(299,	'PW',	'Pacific/Palau',	0,	32400,	32400,	0),
					(300,	'PY',	'America/Asuncion',	1,	-14400,	-10800,	1),
					(301,	'QA',	'Asia/Qatar',	0,	10800,	10800,	0),
					(302,	'RE',	'Indian/Reunion',	0,	14400,	14400,	0),
					(303,	'RO',	'Europe/Bucharest',	1,	7200,	10800,	0),
					(304,	'RS',	'Europe/Belgrade',	1,	3600,	7200,	0),
					(305,	'RU',	'Europe/Kaliningrad',	0,	7200,	10800,	0),
					(306,	'RU',	'Europe/Moscow',	0,	10800,	14400,	0),
					(307,	'RU',	'Europe/Simferopol',	1,	10800,	10800,	0),
					(308,	'RU',	'Europe/Volgograd',	0,	10800,	14400,	0),
					(309,	'RU',	'Europe/Kirov',	0,	10800,	14400,	0),
					(310,	'RU',	'Europe/Astrakhan',	0,	14400,	14400,	0),
					(311,	'RU',	'Europe/Samara',	0,	14400,	18000,	0),
					(312,	'RU',	'Europe/Ulyanovsk',	0,	14400,	14400,	0),
					(313,	'RU',	'Asia/Yekaterinburg',	0,	18000,	21600,	0),
					(314,	'RU',	'Asia/Omsk',	0,	21600,	25200,	0),
					(315,	'RU',	'Asia/Novosibirsk',	0,	25200,	25200,	0),
					(316,	'RU',	'Asia/Barnaul',	0,	25200,	25200,	0),
					(317,	'RU',	'Asia/Tomsk',	0,	25200,	25200,	0),
					(318,	'RU',	'Asia/Novokuznetsk',	0,	25200,	28800,	0),
					(319,	'RU',	'Asia/Krasnoyarsk',	0,	25200,	28800,	0),
					(320,	'RU',	'Asia/Irkutsk',	0,	28800,	32400,	0),
					(321,	'RU',	'Asia/Chita',	0,	32400,	36000,	0),
					(322,	'RU',	'Asia/Yakutsk',	0,	32400,	36000,	0),
					(323,	'RU',	'Asia/Khandyga',	0,	32400,	39600,	0),
					(324,	'RU',	'Asia/Vladivostok',	0,	36000,	39600,	0),
					(325,	'RU',	'Asia/Ust-Nera',	0,	36000,	43200,	0),
					(326,	'RU',	'Asia/Magadan',	0,	39600,	43200,	0),
					(327,	'RU',	'Asia/Sakhalin',	0,	39600,	39600,	0),
					(328,	'RU',	'Asia/Srednekolymsk',	0,	39600,	43200,	0),
					(329,	'RU',	'Asia/Kamchatka',	0,	43200,	46800,	0),
					(330,	'RU',	'Asia/Anadyr',	0,	43200,	46800,	0),
					(331,	'RW',	'Africa/Kigali',	0,	7200,	7200,	0),
					(332,	'SA',	'Asia/Riyadh',	0,	10800,	10800,	0),
					(333,	'SB',	'Pacific/Guadalcanal',	0,	39600,	39600,	0),
					(334,	'SC',	'Indian/Mahe',	0,	14400,	14400,	0),
					(335,	'SD',	'Africa/Khartoum',	0,	10800,	10800,	0),
					(336,	'SE',	'Europe/Stockholm',	1,	3600,	7200,	0),
					(337,	'SG',	'Asia/Singapore',	0,	28800,	26400,	0),
					(338,	'SH',	'Atlantic/St_Helena',	0,	0,	0,	0),
					(339,	'SI',	'Europe/Ljubljana',	1,	3600,	7200,	0),
					(340,	'SJ',	'Arctic/Longyearbyen',	1,	3600,	7200,	0),
					(341,	'SK',	'Europe/Bratislava',	1,	3600,	7200,	0),
					(342,	'SL',	'Africa/Freetown',	0,	0,	0,	0),
					(343,	'SM',	'Europe/San_Marino',	1,	3600,	7200,	0),
					(344,	'SN',	'Africa/Dakar',	0,	0,	0,	0),
					(345,	'SO',	'Africa/Mogadishu',	0,	10800,	10800,	0),
					(346,	'SR',	'America/Paramaribo',	0,	-10800,	-10800,	0),
					(347,	'SS',	'Africa/Juba',	0,	10800,	10800,	0),
					(348,	'ST',	'Africa/Sao_Tome',	0,	0,	0,	0),
					(349,	'SV',	'America/El_Salvador',	0,	-21600,	-18000,	0),
					(350,	'SX',	'America/Lower_Princes',	0,	-14400,	-14400,	0),
					(351,	'SY',	'Asia/Damascus',	1,	7200,	10800,	0),
					(352,	'SZ',	'Africa/Mbabane',	0,	7200,	10800,	0),
					(353,	'TC',	'America/Grand_Turk',	1,	-14400,	-14400,	0),
					(354,	'TD',	'Africa/Ndjamena',	0,	3600,	7200,	0),
					(355,	'TF',	'Indian/Kerguelen',	0,	18000,	18000,	0),
					(356,	'TG',	'Africa/Lome',	0,	0,	0,	0),
					(357,	'TH',	'Asia/Bangkok',	0,	25200,	25200,	0),
					(358,	'TJ',	'Asia/Dushanbe',	0,	18000,	25200,	0),
					(359,	'TK',	'Pacific/Fakaofo',	0,	46800,	46800,	0),
					(360,	'TL',	'Asia/Dili',	0,	32400,	32400,	0),
					(361,	'TM',	'Asia/Ashgabat',	0,	18000,	21600,	0),
					(362,	'TN',	'Africa/Tunis',	0,	3600,	7200,	0),
					(363,	'TO',	'Pacific/Tongatapu',	0,	46800,	50400,	0),
					(364,	'TR',	'Europe/Istanbul',	1,	10800,	10800,	0),
					(365,	'TT',	'America/Port_of_Spain',	0,	-14400,	-14400,	0),
					(366,	'TV',	'Pacific/Funafuti',	0,	43200,	43200,	0),
					(367,	'TW',	'Asia/Taipei',	0,	28800,	32400,	0),
					(368,	'TZ',	'Africa/Dar_es_Salaam',	0,	10800,	10800,	0),
					(369,	'UA',	'Europe/Kiev',	1,	7200,	10800,	0),
					(370,	'UA',	'Europe/Uzhgorod',	1,	7200,	10800,	0),
					(371,	'UA',	'Europe/Zaporozhye',	1,	7200,	10800,	0),
					(372,	'UG',	'Africa/Kampala',	0,	10800,	10800,	0),
					(373,	'UM',	'Pacific/Johnston',	0,	-36000,	-34200,	0),
					(374,	'UM',	'Pacific/Midway',	0,	-39600,	-39600,	0),
					(375,	'UM',	'Pacific/Wake',	0,	43200,	43200,	0),
					(376,	'US',	'America/New_York',	1,	-18000,	-14400,	0),
					(377,	'US',	'America/Detroit',	1,	-18000,	-14400,	0),
					(378,	'US',	'America/Kentucky/Louisville',	1,	-18000,	-14400,	0),
					(379,	'US',	'America/Kentucky/Monticello',	1,	-18000,	-14400,	0),
					(380,	'US',	'America/Indiana/Indianapolis',	1,	-18000,	-14400,	0),
					(381,	'US',	'America/Indiana/Vincennes',	1,	-18000,	-14400,	0),
					(382,	'US',	'America/Indiana/Winamac',	1,	-18000,	-14400,	0),
					(383,	'US',	'America/Indiana/Marengo',	1,	-18000,	-14400,	0),
					(384,	'US',	'America/Indiana/Petersburg',	1,	-18000,	-14400,	0),
					(385,	'US',	'America/Indiana/Vevay',	1,	-18000,	-14400,	0),
					(386,	'US',	'America/Chicago',	1,	-21600,	-18000,	0),
					(387,	'US',	'America/Indiana/Tell_City',	1,	-21600,	-18000,	0),
					(388,	'US',	'America/Indiana/Knox',	1,	-21600,	-18000,	0),
					(389,	'US',	'America/Menominee',	1,	-21600,	-18000,	0),
					(390,	'US',	'America/North_Dakota/Center',	1,	-21600,	-18000,	0),
					(391,	'US',	'America/North_Dakota/New_Salem',	1,	-21600,	-18000,	0),
					(392,	'US',	'America/North_Dakota/Beulah',	1,	-21600,	-18000,	0),
					(393,	'US',	'America/Denver',	1,	-25200,	-21600,	0),
					(394,	'US',	'America/Boise',	1,	-25200,	-21600,	0),
					(395,	'US',	'America/Phoenix',	0,	-25200,	-21600,	0),
					(396,	'US',	'America/Los_Angeles',	1,	-28800,	-25200,	0),
					(397,	'US',	'America/Anchorage',	1,	-32400,	-28800,	0),
					(398,	'US',	'America/Juneau',	1,	-32400,	-28800,	0),
					(399,	'US',	'America/Sitka',	1,	-32400,	-28800,	0),
					(400,	'US',	'America/Metlakatla',	1,	-32400,	-28800,	0),
					(401,	'US',	'America/Yakutat',	1,	-32400,	-28800,	0),
					(402,	'US',	'America/Nome',	1,	-32400,	-28800,	0),
					(403,	'US',	'America/Adak',	1,	-36000,	-32400,	0),
					(404,	'US',	'Pacific/Honolulu',	0,	-36000,	-34200,	0),
					(405,	'UY',	'America/Montevideo',	1,	-10800,	-7200,	0),
					(406,	'UZ',	'Asia/Samarkand',	0,	18000,	21600,	0),
					(407,	'UZ',	'Asia/Tashkent',	0,	18000,	25200,	0),
					(408,	'VA',	'Europe/Vatican',	1,	3600,	7200,	0),
					(409,	'VC',	'America/St_Vincent',	0,	-14400,	-14400,	0),
					(410,	'VE',	'America/Caracas',	0,	-14400,	-14400,	0),
					(411,	'VG',	'America/Tortola',	0,	-14400,	-14400,	0),
					(412,	'VI',	'America/St_Thomas',	0,	-14400,	-14400,	0),
					(413,	'VN',	'Asia/Ho_Chi_Minh',	0,	25200,	25200,	0),
					(414,	'VU',	'Pacific/Efate',	0,	39600,	43200,	0),
					(415,	'WF',	'Pacific/Wallis',	0,	43200,	43200,	0),
					(416,	'WS',	'Pacific/Apia',	1,	46800,	50400,	1),
					(417,	'YE',	'Asia/Aden',	0,	10800,	10800,	0),
					(418,	'YT',	'Indian/Mayotte',	0,	10800,	10800,	0),
					(419,	'ZA',	'Africa/Johannesburg',	0,	7200,	10800,	0),
					(420,	'ZM',	'Africa/Lusaka',	0,	7200,	7200,	0),
					(421,	'ZW',	'Africa/Harare',	0,	7200,	7200,	0);
				";
			}
		}
		
		if (version_compare($installed_version, '3.4.4.15') < 0) {
			
			if (!$this->checkValueExists($t_prefix."address_types", "name", "postal", $this->database_connection)) {
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."address_types` (`name`, `is_system`) VALUES ('postal', '1');
				";
			}
		}
		
		
		if (version_compare($installed_version, '3.4.4.17') < 0) {
				
			if (!$this->checkValueExists($t_prefix."config_options", "name", "apply_milestone_subtasks", $this->database_connection)) {
				$upgrade_script .= "
					INSERT INTO `".$t_prefix."config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
					('general', 'apply_milestone_subtasks', '1', 'BoolConfigHandler', 0, 0, '');
				";
			}
		}
		
		

		// Execute all queries
		if(!$this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
			$this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
			return false;
		}
		$this->printMessage("Database schema transformations executed (total queries: $total_queries)");
		
		
		if (version_compare($installed_version, '3.4.4.12') < 0) {
			$this->printMessage("Updating users timezone settings...");
			include_once ROOT . "/public/upgrade/helpers/update_user_timezones.php";
			_tz_upgrade_user_and_company_timezones($t_prefix, $this->database_connection);
			_tz_upgrade_default_system_timezone($t_prefix, $this->database_connection);
			_tz_upgrade_objects_timezone($t_prefix, $this->database_connection);
		}
		
		$this->printMessage('Feng Office has been upgraded. You are now running Feng Office '.$this->getVersionTo().' Enjoy!');

		tpl_assign('additional_steps', $additional_upgrade_steps);

	} // execute
	
} // BauruUpgradeScript
