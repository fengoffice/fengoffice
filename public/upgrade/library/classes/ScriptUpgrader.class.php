<?php

/**
 * Script update framework. This simple tool will let us build most complext update scripts witout any problems
 *
 * @package ScriptUpdater
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
final class ScriptUpgrader {

	/**
	 * Output object
	 *
	 * @var Output
	 */
	private $output;

	/**
	 * Upgrader name field
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Upgrader description
	 *
	 * @var string
	 */
	private $description;

	/**
	 * Array of available upgrade scripts
	 *
	 * @var array
	 */
	private $scripts = array();

	/**
	 * Array of reported checklist items
	 *
	 * @var array
	 */
	private $checklist_items = array();

	/**
	 * Construct the ScriptUpgrader
	 *
	 * @param Output $output
	 * @param string $name
	 * @param string $desc
	 * @return ScriptUpgrader
	 */
	function __construct(Output $output, $name = null, $desc = null) {
		$this->setOutput($output);
		$this->setName($name);
		$this->setDescription($desc);

		$this->loadScripts();
	} // __construct

	/**
	 * Execute upgrade script that is responsible for upgrade process from installed version to target version
	 *
	 * @param string $version_from
	 * @param string $to_version
	 * @return null
	 */
	function upgrade($version_from, $version_to) {
		$scripts = $this->getScripts();
		// check if version_to exists and there's a path from current version to version_to.
		if (is_array($scripts)) {
			// scripts are sorted according to the "to" upgrade version
			$exists = false;
			$current = $version_from;
			foreach ($scripts as $script) {
				if ($script->worksFor($current)) {
					$current = $script->getVersionTo();
					if (version_compare($current, $version_to) == 0) {
						$exists = true;
						break;
					}
				}
			} // foreach
			if (!$exists) {
				$this->printMessage("There is no upgrade path from version $version_from to $version_to.");
				return;
			}
			
			// Check if config has "mysql" as db adapter, if so then change it to "mysqli"
			$config_file_handle = fopen(INSTALLATION_PATH . '/config/config.php', 'r');
			$update_config_contents = false;
			if ($config_file_handle) {
				$all_config_lines = "";
				while ((($line = fgets($config_file_handle)) !== false)) {
					if (strpos($line, 'DB_ADAPTER') !== false) {
						if (strpos($line, '"mysql"') !== false || strpos($line, "'mysql'") !== false) {
							$update_config_contents = true;
							$line = str_replace('mysql', 'mysqli', $line);
						}
					}
					$all_config_lines .= $line;
				}
				fclose($config_file_handle);
			}
			if ($update_config_contents) {
				file_put_contents(INSTALLATION_PATH . '/config/config.php', $all_config_lines);
				$this->printMessage('Updated DB_ADAPTER setting to mysqli in config/config.php');
			}
			// --
			
			// include config file
			$config_is_set = @include_once INSTALLATION_PATH . '/config/config.php';
			if (!$config_is_set) {
				$this->printMessage('Valid config file was not found!', true);
				return false;
			} else {
				$this->printMessage('Config file found and loaded.');
			} // if
			
			// check preconditions
			$write_checks = array();
			$ext_checks = array();
			foreach ($scripts as $script) {
				if ((version_compare($script->getVersionTo(), $version_from) > 0) &&
						version_compare($script->getVersionTo(), $version_to) <= 0) {
					$write_checks = array_merge($write_checks, $script->getCheckIsWritable());
					$ext_checks = array_merge($ext_checks, $script->getCheckExtensions());
				} // if
			} // foreach
			$write_checks = array_unique($write_checks);
			$ext_checks = array_unique($ext_checks);
			
			// check for writable files and folders
			foreach ($write_checks as $relative_path) {
				$path = INSTALLATION_PATH . $relative_path;
				if (is_file($path)) {
					if (file_is_writable($path)) {
						$this->printMessage("File '$relative_path' exists and is writable");
					} else {
						$this->printMessage("File '$relative_path' is not writable", true);
						return false;
					} // if
				} else if(is_dir($path)) {
					if(folder_is_writable($path)) {
						$this->printMessage("Folder '$relative_path' exists and is writable");
					} else {
						$this->printMessage("Folder '$relative_path' is not writable", true);
						return false;
					} // if
				} else {
					$this->printMessage("'$relative_path' does not exists on the system", true);
					return false;
				} // if
			} // foreach

			// check for loaded extensions
			foreach ($ext_checks as $extension_name) {
				if (extension_loaded($extension_name)) {
					$this->printMessage("Extension '$extension_name' is loaded");
				} else {
					$this->printMessage("Extension '$extension_name' is not loaded", true);
					return false;
				} // if
			} // foreach
			
			// connect to database
			if ($dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASS)) {
			    if (mysqli_select_db($dbc, DB_NAME)) {
					$this->printMessage('Upgrade script has connected to the database.');
				} else {
					$this->printMessage('Failed to select database ' . DB_NAME);
					return false;
				} // if
			} else {
				$this->printMessage('Failed to connect to database');
				return false;
			} // if

			// check MySQL version
			$mysql_version = mysqli_get_server_info($dbc);
			if ($mysql_version && version_compare($mysql_version, "4.1", '>=')) {
				$constants['DB_CHARSET'] = 'utf8';
				@mysqli_query($dbc, "SET NAMES 'utf8'");
				tpl_assign('default_collation', $default_collation = 'collate utf8_unicode_ci');
				tpl_assign('default_charset', $default_charset = 'DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
			} else {
				tpl_assign('default_collation', $default_collation = '');
				tpl_assign('default_charset', $default_charset = '');
			} // if

			tpl_assign('table_prefix', TABLE_PREFIX);
			if (defined('DB_ENGINE')) {
				tpl_assign('engine', DB_ENGINE);
			} else {
				tpl_assign('engine', 'InnoDB');
			}

			// check test query
			$test_table_name = TABLE_PREFIX . 'test_table';
			$test_table_sql = "CREATE TABLE `$test_table_name` (
				`id` int(10) unsigned NOT NULL auto_increment,
				`name` varchar(50) $default_collation NOT NULL default '',
				PRIMARY KEY  (`id`)
				) ENGINE=InnoDB $default_charset;";

			if (@mysqli_query($dbc, $test_table_sql)) {
				$this->printMessage('Test query has been executed. Its safe to proceed with database migration.');
				@mysqli_query($dbc, "DROP TABLE `$test_table_name`");
			} else {
				$this->printMessage('Failed to executed test query. MySQL said: ' . mysqli_error($dbc), true);
				return false;
			} // if
			
			// execute scripts
			foreach($scripts as $script) {
				if((version_compare($script->getVersionTo(), $version_from) > 0) && version_compare($script->getVersionTo(), $version_to) <= 0) {
					if ($this->getOutput() instanceof Output_Console) {
						$this->printMessage(date('Y-m-d H:i:s') . " - Starting upgrade to " . $script->getVersionTo());
					}
					
					$script->setDatabaseConnection($dbc);
					if ($script->execute() === false) {
						$this->printMessage("Error upgrading to version " . $script->getVersionTo());
						break;
					}
					$last_correct_version = $script->getVersionTo();
					tpl_assign('version', $last_correct_version);
					file_put_contents(INSTALLATION_PATH . '/config/installed_version.php', tpl_fetch(get_template_path('installed_version')));
					
					if ($this->getOutput() instanceof Output_Console) {
						$this->printMessage(date('Y-m-d H:i:s') . " - Finished upgrade to " . $script->getVersionTo());
					}
				} // if
			} // foreach
			
			// update the plugins that have a pending update function to execute
			$this->updatePlugins();
			
			// clean autoloader
			file_put_contents(INSTALLATION_PATH . "/cache/autoloader.php", "");
			@unlink(INSTALLATION_PATH . "/cache/autoloader.php");
			
			if (isset($last_correct_version)) {
				@mysqli_query($dbc, "UPDATE `".TABLE_PREFIX."config_options` SET `value` = '$last_correct_version' WHERE `name` = 'installed_version'");
			    @mysqli_query($dbc, "UPDATE `".TABLE_PREFIX."config_options` SET `value` = 0 WHERE `name` = 'upgrade_last_check_new_version'");
				tpl_assign('version', $last_correct_version);
				file_put_contents(INSTALLATION_PATH . '/config/installed_version.php', tpl_fetch(get_template_path('installed_version')));
			}
		} // if
	} // upgrade


	function updatePlugins() {
		
		$plugin_console_script = INSTALLATION_PATH . "/public/install/plugin-console.php";
		$plugin_update_all_command = "php $plugin_console_script update_all";
		
		if (is_exec_available()) {
			$exec_result = null;
			$exec_return_var = null;

			$command_suffix = $this->getOutput() instanceof Output_Console ? "" : " 2>&1";

			exec($plugin_update_all_command . $command_suffix, $exec_result, $exec_return_var);

			$is_error = $exec_return_var > 0;
			if ($is_error) {
				$this->printMessage("Error found when updating plugins", $is_error);
			} else {
				$this->printMessage("Updating plugins...");
			}

			foreach ($exec_result as $exec_result_line) {
				$this->printMessage($exec_result_line, $is_error);
			}

			if ($is_error) {
				if (!$exec_result) {
					// if empty error message then tell the user to try updating plugins in terminal
					$this->printMessage("To finish updating the plugins please try executing the following command in your terminal:", true);
					$this->printMessage($plugin_update_all_command, true);
				} else {
					$this->printMessage("Please contact your administrator to solve this issue.", true);
				}
			} else {
				$this->printMessage("Finished plugins update.");
			}

		} else {

			$this->printMessage("Couldn't execute update for plugins due to 'exec' function is not available. ", true);
			$this->printMessage("To finish updating the plugins please execute the following command in your terminal:", true);
			$this->printMessage($plugin_update_all_command, true);

		}
	}


	// ---------------------------------------------------
	//  Utils
	// ---------------------------------------------------

	/**
	 * Set content for layout
	 *
	 * @access public
	 * @param string $content
	 * @return null
	 */
	function setContent($content) {
		tpl_assign('content_for_layout', $content);
	} // setContent

	/**
	 * Load all scripts from /scripts folder
	 *
	 * @param void
	 * @return null
	 */
	private function loadScripts() {
		$script_path = UPGRADER_PATH . '/scripts';

		$d = dir($script_path);

		$scripts = array();
		while(($entry = $d->read()) !== false) {
			if(($entry == '.') || ($entry == '..')) {
				continue;
			} // if
			$file_path = $script_path . '/' . $entry;

			if(is_readable($file_path) && str_ends_with($file_path, '.class.php')) {
				include_once $file_path;
				$script_class = substr($entry, 0, strlen($entry) - 10);
				$script = new $script_class($this->getOutput());
				if($script instanceof $script_class) {
					$script->setUpgrader($this);
					$scripts[] = $script;
				} // if
			} // if
		} // while
		$d->close();

		if(count($scripts)) {
			usort($scripts, 'compare_scripts_by_version_to');
			$this->scripts = $scripts;
		} // if
	} // loadScripts

	// ---------------------------------------------------
	//  Getters and setters
	// ---------------------------------------------------

	/**
	 * Get output
	 *
	 * @param null
	 * @return Output
	 */
	function getOutput() {
		return $this->output;
	} // getOutput

	/**
	 * Set output value
	 *
	 * @param Output $value
	 * @return Output
	 */
	function setOutput(Output $value) {
		$this->output = $value;
		return $value;
	} // setOutput

	/**
	 * Get name
	 *
	 * @param null
	 * @return string
	 */
	function getName() {
		return $this->name;
	} // getName

	/**
	 * Set name value
	 *
	 * @param string $value
	 * @return null
	 */
	function setName($value) {
		$this->name = $value;
	} // setName

	/**
	 * Get description
	 *
	 * @param null
	 * @return string
	 */
	function getDescription() {
		return $this->description;
	} // getDescription

	/**
	 * Set description value
	 *
	 * @param string $value
	 * @return null
	 */
	function setDescription($value) {
		$this->description = $value;
	} // setDescription

	/**
	 * Return array of loaded scripts
	 *
	 * @param void
	 * @return array
	 */
	function getScripts() {
		return $this->scripts;
	} // getScripts
	
	function getScriptsSince($version) {
		$ret = array();
		foreach ($this->scripts as $s) {
			if (version_compare($s->getVersionTo(), $version) > 0) {
				$ret[] = $s; 
			}
		}
		return $ret;
	} // getScriptsSince

	/**
	 * Return all checklist items
	 *
	 * @param void
	 * @return array
	 */
	function getChecklistItems() {
		return $this->checklist_items;
	} // getChecklistItems

	/**
	 * Add checklist item to the list
	 *
	 * @param string $group
	 * @param string $text
	 * @param boolean $checked
	 * @return null
	 */
	function addChecklistItem($group, $text, $checked = false) {
		if(!isset($this->checklist_items[$group]) || !is_array($this->checklist_items[$group])) {
			$this->checklist_items[$group] = array();
		} // if
		$this->checklist_items[$group][] = new ChecklistItem($text, $checked);
	} // addChecklistItem

	function printMessage($message, $is_error = false) {
		$this->getOutput()->printMessage($message, $is_error);
	}
	
} // ScriptUpgrader

?>