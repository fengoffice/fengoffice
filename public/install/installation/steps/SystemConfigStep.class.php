<?php

/**
 * Third step of Feng Office installation -> user is required to enter
 * database connection settings. Script tries the connection params and
 * if correct saves them in /config/config.php file
 *
 * @package ScriptInstaller
 * @subpackage installation
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class SystemConfigStep extends ScriptInstallerStep {

	/**
	 * Cached database connection resource
	 *
	 * @var resource
	 */
	private $database_connection;

	/**
	 * Construct the ConfigStep
	 *
	 * @access public
	 * @param void
	 * @return ConfigStep
	 */
	function __construct() {
		$this->setName('Configuration');
	} // __construct


	/**
	 * Prepare and process config form
	 *
	 * @param void
	 * @return boolean
	 */
	function execute() {
		if((strtolower(array_var($_SERVER, 'HTTPS')) == 'on') || (array_var($_SERVER, 'SERVER_PORT') == 443)) {
			$protocol = 'https://';
		} else {
			$protocol = 'http://';
		} // if

		$request_url = without_slash($protocol . dirname($_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']));
		if(($rpos = strrpos($request_url, '/')) !== false) {
			$installation_url = substr($request_url, 0, $rpos - 7); // remove /public ;)
		} else {
			$installation_url = '';
		} // if

		$config_form_data = array_var($_POST, 'config_form');
		//var_dump($config_form_data) ; exit ;
		
		if(!is_array($config_form_data)) {
			$config_form_data = array(
	          'database_type'   	=> $this->getFromStorage('database_type'),
	          'database_host'   	=> $this->getFromStorage('database_host', 'localhost'),
	          'database_user'   	=> $this->getFromStorage('database_user'),
	          'database_pass'   	=> $this->getFromStorage('database_pass'),
	          'database_name'   	=> $this->getFromStorage('database_name'),
	          'database_prefix'		=> $this->getFromStorage('database_prefix'),
			  'database_engine' 	=> $this->getFromStorage('database_engine'),
	          'absolute_url'   		=> $this->getFromStorage('absolute_url'),
			  'plugins'    			=> $this->getFromStorage('plugins'),
			  'plugins_available' 	=> $this->scanPlugins(),
				
			); 
		} 
		tpl_assign('installation_url', $installation_url);
		tpl_assign('config_form_data', $config_form_data);

		if($this->isSubmited()) {
			$database_type   = (string) array_var($config_form_data, 'database_type');
			$database_host   = (string) array_var($config_form_data, 'database_host');
			$database_user   = (string) array_var($config_form_data, 'database_user');
			$database_pass   = (string) array_var($config_form_data, 'database_pass');
			$database_name   = (string) array_var($config_form_data, 'database_name');
			$database_prefix = (string) array_var($config_form_data, 'database_prefix');
			$database_engine = (string) array_var($config_form_data, 'database_engine');
			$absolute_url    = (string) array_var($config_form_data, 'absolute_url');
			$plugins		 = array_var($config_form_data, 'plugins');
			$connected = false;
			$this->database_connection = @mysql_connect($database_host, $database_user, $database_pass);
			if ($this->database_connection) {
				$connected = @mysql_select_db($database_name, $this->database_connection);
				if (!$connected) {
					$error = @mysql_error();
					$errno = @mysql_errno();
					if ($errno == 1049 && strpos($database_name, '`') === false) {
						// database doesn't exist. Try to create it.
						if (@mysql_query('CREATE DATABASE `' . $database_name . '`', $this->database_connection)) {
							$connected = @mysql_select_db($database_name, $this->database_connection);
						}
					}
				}
			} else {
				$error = @mysql_error();
			}

			if($connected) {
				$this->addToStorage('database_type', $database_type);
				$this->addToStorage('database_host', $database_host);
				$this->addToStorage('database_user', $database_user);
				$this->addToStorage('database_pass', $database_pass);
				$this->addToStorage('database_name', $database_name);
				$this->addToStorage('database_prefix', $database_prefix);
				$this->addToStorage('database_engine', $database_engine);
				$this->addToStorage('absolute_url', $absolute_url);
				$this->addToStorage('plugins'		, $plugins);
				return true;
			} else {
				$this->addError('Failed to connect to database with data you provided: ' . $error);
			} // if
		} // if

		$this->setContentFromTemplate('system_config_form.php');
		return false;
	} // excute

	/**
	 * Add error message to all messages and break the execution
	 *
	 * @access public
	 * @param string $error_message Reason why we are breaking execution
	 * @return boolean
	 */
	function breakExecution($error_message) {
		$this->addToChecklist($error_message, false);
		if(is_resource($this->database_connection)) @mysql_query('ROLLBACK', $this->database_connection);
		$this->setContentFromTemplate('finish.php');
		return false;
	} // breakExecution

	/**
	 * Write $constants in config file
	 *
	 * @access public
	 * @param array $constants
	 * @return boolean
	 */
	function writeConfigFile($constants) {
		tpl_assign('config_file_constants', $constants);
		return file_put_contents(INSTALLATION_PATH . '/config/config.php', tpl_fetch(get_template_path('config_file.php')));
	} // writeConfigFile

	/**
	 * Execute multiple queries
	 *
	 * This one is really quick and dirty because I want to finish this and catch
	 * the bus. Need to be redone ASAP
	 *
	 * This function returns true if all queries are executed successfully
	 *
	 * @access public
	 * @todo Make a better implementation
	 * @param string $sql
	 * @param integer $total_queries Total number of queries in SQL
	 * @param integer $executed_queries Total number of successfully executed queries
	 * @return boolean
	 */
	function executeMultipleQueries($sql, &$total_queries, &$executed_queries) {
		if(!trim($sql)) {
			$total_queries = 0;
			$executed_queries = 0;
			return true;
		} // if

		// Make it work on PHP 5.0.4
		$sql = str_replace(array("\r\n", "\r"), array("\n", "\n"), $sql);

		$queries = explode(";\n", $sql);
		if(!is_array($queries) || !count($queries)) {
			$total_queries = 0;
			$executed_queries = 0;
			return true;
		} // if

		$total_queries = count($queries);
		foreach($queries as $query) {
			if(trim($query)) {
				if(@mysql_query(trim($query))) {
					$executed_queries++;
				} else {
					return false;
				} // if
			} // if
		} // if

		return true;
	}

	function scanPlugins() {
		$plugins = array();
		$dir =	INSTALLATION_PATH."/plugins";
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if (is_dir($dir ."/". $file) && $file!="." && $file!=".."){
					if (file_exists($dir ."/". $file . "/info.php" )){
						
						$plugin_info = include_once $dir ."/". $file . "/info.php";
						array_push($plugins, $plugin_info);
					}
				}
			}
			closedir($dh);
		} 
		usort($plugins, 'plugin_sort') ;
		
		return $plugins;
	}

} 
