<?php

final class acInstallation {

	public $plugins;
	/**
	 * Output object
	 *
	 * @var Output
	 */
	private $output;

	/**
	 * MySQL connection resource
	 *
	 * @var resource
	 */
	private $database_connection;

	/**
	 * Type of the database
	 *
	 * @var string
	 */
	private $database_type = 'mysqli';

	/**
	 * Database host
	 *
	 * @var string
	 */
	private $database_host;

	/**
	 * Database username
	 *
	 * @var string
	 */
	private $database_username;

	/**
	 * Database password
	 *
	 * @var string
	 */
	private $database_password;

	/**
	 * Database name
	 *
	 * @var string
	 */
	private $database_name;

	/**
	 * Table prefix
	 *
	 * @var string
	 */
	private $table_prefix;

	/**
	 * Database engine
	 *
	 * @var string
	 */
	private $database_engine = 'InnoDB';

	/**
	 * Absolute URL
	 *
	 * @var string
	 */
	private $absolute_url;

	/**
	 * Default Localization
	 *
	 * @var string
	 */
	private $default_localization;

	/**
	 * Constructor
	 *
	 * @param Output $output
	 * @return acInstallation
	 */
	function __construct(Output $output) {
		$this->setOutput($output);
	} // __construct

	/**
	 * Prepare and process config form
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function execute() {
		$database_type   = $this->getDatabaseType();
		$database_host   = $this->getDatabaseHost();
		$database_user   = $this->getDatabaseUsername();
		$database_pass   = $this->getDatabasePassword();
		$database_name   = $this->getDatabaseName();
		$database_prefix = $this->getTablePrefix();
		$database_engine = $this->getDatabaseEngine();
		$absolute_url    = $this->getAbsoluteUrl();
		$plugins   		 = $this->getPlugins();
		$default_localization = $this->getDefaultLocalization();

		$connected = false;
		if($this->database_connection = @mysqli_connect($database_host, $database_user, $database_pass)) {
		    $connected = @mysqli_select_db($this->database_connection, $database_name);
		} // if

		if($connected) {
			$this->printMessage('Database connection has been established successfully');
		} else {
			return $this->breakExecution('Failed to connect to database with data you provided');
		} // if

		// ---------------------------------------------------
		//  Check if we have InnoDB support
		// ---------------------------------------------------

		if (strtolower($this->getDatabaseEngine()) == 'innodb') {
			if ($this->haveInnoDbSupport()) {
				$this->printMessage('InnoDB storage engine is supported');
			} else {
				return $this->breakExecution('InnoDB storage engine is not supported. Enable it on your database or choose MyISAM and try again.');
			}
		} // if
		
		$constants = array(
	        'DB_ADAPTER'           => $database_type,
	        'DB_HOST'              => $database_host,
	        'DB_USER'              => $database_user,
	        'DB_PASS'              => $database_pass,
	        'DB_NAME'              => $database_name,
	        'DB_PERSIST'           => true,
		    'TABLE_PREFIX'         => $database_prefix,
			'DB_ENGINE' 	       => $database_engine,
	        'ROOT_URL'             => $absolute_url,
	        'DEFAULT_LOCALIZATION' => $default_localization,
			'COOKIE_PATH'          => "/",
	        'DEBUG'                => false,
			'SEED'				   => md5($database_user.$database_pass.rand(0,10000000000))
		); // array

		tpl_assign('table_prefix', $database_prefix);
		tpl_assign('absolute_url', $absolute_url);
		tpl_assign('engine', $database_engine);

		// Check MySQL version
		$mysql_version = mysqli_get_server_info($this->database_connection);
		if($mysql_version && version_compare($mysql_version, '4.1', '>=')) {
			$constants['DB_CHARSET'] = 'utf8';
			@mysqli_query($this->database_connection, "SET NAMES 'utf8'");
			tpl_assign('default_collation', 'collate utf8_unicode_ci');
			tpl_assign('default_charset', 'DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
		} else {
			tpl_assign('default_collation', '');
			tpl_assign('default_charset', '');
		} // if

		@mysqli_query($this->database_connection, 'BEGIN WORK');

		// Database construction
		$total_queries = 0;
		$executed_queries = 0;
		if($this->executeMultipleQueries(tpl_fetch(get_template_path('sql/mysql_schema.php')), $total_queries, $executed_queries)) {
			$this->printMessage("Tables created in '$database_name'. (Executed queries: $executed_queries)");
		} else {
			return $this->breakExecution('#1 - Failed to import database construction. MySQL said: ' . mysqli_error($this->database_connection));
		} // if

		// Initial data
		$total_queries = 0;
		$executed_queries = 0;
		if($this->executeMultipleQueries(tpl_fetch(get_template_path('sql/mysql_initial_data.php')), $total_queries, $executed_queries)) {
			$this->printMessage("Initial data imported into '$database_name'. (Executed queries: $executed_queries)");
		} else {
			return $this->breakExecution('Failed to import initial data. MySQL said: ' . mysqli_error($this->database_connection));
		} // if
		
		//Execute plugin sql files
		$sql_plugins_dir = get_template_path('sql/plugins');
		if (is_dir($sql_plugins_dir)) {
			$handle = opendir($sql_plugins_dir);
			while ($file = readdir($handle)) {
				if ($file != 'dummy.txt' && is_file(get_template_path("sql/plugins/$file"))) {
					$total_queries = 0;
					$executed_queries = 0;
					if($this->executeMultipleQueries(tpl_fetch(get_template_path("sql/plugins/$file")), $total_queries, $executed_queries)) {
						$this->printMessage("Plugin executed: $file. (Executed queries: $executed_queries)");
					} else {
						return $this->breakExecution('Failed to execute plugin: '. $file . '. MySQL said: ' . mysqli_error($this->database_connection));
					} // if
				}
			}
			closedir($handle);
		}
		
		//Execute plugin php files
		$php_plugins_dir = get_template_path('php/plugins');
		if (is_dir($php_plugins_dir)) {
			$handle = opendir($php_plugins_dir);
			while ($file = readdir($handle)) {
				$file_path = get_template_path("php/plugins/$file");
				if ($file != 'dummy.txt' && is_file($file_path)) {
					include $file_path;
				}
			}
			closedir($handle);
		}

		$this->installPlugins($plugins);
				
		
		@mysqli_query($this->database_connection, 'COMMIT');

		
		if ($this->writeConfigFile($constants)) {
			$this->printMessage('Configuration data has been successfully added to the configuration file');
		} else {
			return $this->breakExecution('Failed to write config data into config file');
		} // if
		
		if ($this->writeInstalledVersionFile(require INSTALLATION_PATH . '/version.php')) {
			$this->printMessage('File installed_version.php created successfully');
		} else {
			return $this->breakExecution('Failed to create installed_version file');
		} // if

		return true;
	} // excute

	// ---------------------------------------------------
	//  Util methods
	// ---------------------------------------------------

	/**
	 * Add error message to all messages and break the execution
	 *
	 * @access public
	 * @param string $error_message Reason why we are breaking execution
	 * @return boolean
	 */
	function breakExecution($error_message) {
		$this->printMessage(preg_replace('/\s+/', ' ', trim($error_message)), true);
		if(is_resource($this->database_connection)) {
		    @mysqli_query($this->database_connection, 'ROLLBACK');
		} // if
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

	function writeInstalledVersionFile($version) {
		tpl_assign('version', $version);
		return file_put_contents(INSTALLATION_PATH . '/config/installed_version.php', tpl_fetch(get_template_path('installed_version.php')));
	}
	
	/**
	 * This function will return true if server we are connected on has InnoDB support
	 *
	 * @param void
	 * @return boolean
	 */
	function haveInnoDbSupport() {
		$mysql_version = mysqli_get_server_info($this->database_connection);
		
		// check if mysql is >= mysql 5.6 
		if($mysql_version && (version_compare($mysql_version, '5.6', '>=') || strpos($mysql_version, 'MariaDB') !== false)){
			//mysql 5.6 have_innodb is deprecated
		    if($res = mysqli_query($this->database_connection, "SHOW ENGINES")){
				while($rows = mysqli_fetch_assoc($res)) {
					$engine = strtolower(array_var($rows, 'Engine'));
					$support = strtolower(array_var($rows, 'Support'));
			
					if($engine == 'innodb' && ($support == 'default' || $support == 'yes')){
						return true;
					} // if
				} // while
			} // if
		}else{
		    if($result = mysqli_query($this->database_connection, "SHOW VARIABLES LIKE 'have_innodb'")) {
				if($row = mysqli_fetch_assoc($result)) {
					return strtolower(array_var($row, 'Value')) == 'yes';
				} // if
			} // if
		}
		
		return false;
	} // haveInnoDBSupport

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
			    

//delete, dubgging
//$this->printMessage("Query: " . $query);
//$this->printMessage("Query number: " . $queries);
			    
        if(@mysqli_query($this->database_connection, trim($query))) {
					$executed_queries++;
				} else {
					$this->printMessage("Found faulty query: " . preg_replace('/\s+/', ' ', trim($query)), true);
					return false;
				} // if
			} // if
		} // if

		return true;
	} // executeMultipleQueries

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
	 * @return null
	 */
	function setOutput(Output $value) {
		$this->output = $value;
		return $value;
	} // setOutput

	/**
	 * Print message through output object
	 *
	 * @param string $message
	 * @param boolean $is_error
	 * @return null
	 */
	function printMessage($message, $is_error = false) {
		if($this->output instanceof Output) {
			$this->output->printMessage($message, $is_error);
		} // if
	} // printMessage

	/**
	 * Get database_type
	 *
	 * @param null
	 * @return string
	 */
	function getDatabaseType() {
		return $this->database_type;
	} // getDatabaseType

	/**
	 * Set database_type value
	 *
	 * @param string $value
	 * @return null
	 */
	function setDatabaseType($value) {
		$this->database_type = $value;
	} // setDatabaseType

	/**
	 * Get database_host
	 *
	 * @param null
	 * @return string
	 */
	function getDatabaseHost() {
		return $this->database_host;
	} // getDatabaseHost

	/**
	 * Set database_host value
	 *
	 * @param string $value
	 * @return null
	 */
	function setDatabaseHost($value) {
		$this->database_host = $value;
	} // setDatabaseHost

	/**
	 * Get database_username
	 *
	 * @param null
	 * @return string
	 */
	function getDatabaseUsername() {
		return $this->database_username;
	} // getDatabaseUsername

	/**
	 * Set database_username value
	 *
	 * @param string $value
	 * @return null
	 */
	function setDatabaseUsername($value) {
		$this->database_username = $value;
	} // setDatabaseUsername

	/**
	 * Get database_password
	 *
	 * @param null
	 * @return string
	 */
	function getDatabasePassword() {
		return $this->database_password;
	} // getDatabasePassword

	/**
	 * Set database_password value
	 *
	 * @param string $value
	 * @return null
	 */
	function setDatabasePassword($value) {
		$this->database_password = $value;
	} // setDatabasePassword

	/**
	 * Get database_name
	 *
	 * @param null
	 * @return string
	 */
	function getDatabaseName() {
		return $this->database_name;
	} // getDatabaseName

	/**
	 * Set database_name value
	 *
	 * @param string $value
	 * @return null
	 */
	function setDatabaseName($value) {
		$this->database_name = $value;
	} // setDatabaseName

	/**
	 * Get table_prefix
	 *
	 * @param null
	 * @return string
	 */
	function getTablePrefix() {
		return $this->table_prefix;
	} // getTablePrefix

	/**
	 * Set table_prefix value
	 *
	 * @param string $value
	 * @return null
	 */
	function setTablePrefix($value) {
		$this->table_prefix = $value;
	} // setTablePrefix

	/** Get database_engine
	 *
	 * @param null
	 * @return string
	 */
	function getDatabaseEngine() {
		return $this->database_engine;
	} // getDatabaseEngine

	/**
	 * Set database_engine value
	 *
	 * @param string $value
	 * @return null
	 */
	function setDatabaseEngine($value) {
		$this->database_engine = $value;
	} // setDatabaseEngine

	/**
	 * Get absolute_url
	 *
	 * @param null
	 * @return string
	 */
	function getAbsoluteUrl() {
		return $this->absolute_url;
	} // getAbsoluteUrl

	/**
	 * Set absolute_url value
	 *
	 * @param string $value
	 * @return null
	 */
	function setAbsoluteUrl($value) {
		$this->absolute_url = $value;
	} // setAbsoluteUrl

	/**
	 * Get plugins
	 *
	 * @param null
	 * @return string
	 */
	function getPlugins() {
		return $this->plugins;
	}

	function setPlugins($value) {
		$this->plugins = $value;
	}

	/**
	 * Get default_localization
	 *
	 * @param null
	 * @return string
	 */
	function getDefaultLocalization() {
		return ($this->default_localization)?($this->default_localization):'en_us';
	} // getDefaultLocalization

	/**
	 * Set default_localization value
	 *
	 * @param string $value
	 * @return null
	 */
	function setDefaultLocalization($value) {
		$this->default_localization = $value;
	} 
	
	/**
	 * @param array of string $pluginNames
	 */
	function installPlugins($pluginNames) {

		if (count(array($pluginNames))) {
			foreach  ($pluginNames as $name ) {
			    
				$path  = INSTALLATION_PATH."/plugins/$name/info.php";
				if (file_exists($path)) {
					//1. Insert into PLUGIN TABLE

//PROVISORY DEBUG - DELETE!
				    print_r(INSTALLATION_PATH."/plugins/$name/info.php \n");
				    
				    
				    $pluginInfo = include_once $path;

					$cols = "name, is_installed, is_activated, version, activated_on";
					$values = "'$name', 1, 1, ".array_var($pluginInfo,'version').", now()"  ;
					if (is_numeric(array_var($pluginInfo,'id')) ){
						$cols = "id, ". $cols ;
						$values = array_var($pluginInfo,'id').", ".$values;
					}
					$sql = "INSERT INTO ". $this->table_prefix ."plugins ($cols) VALUES ($values) "; 
					if (@mysqli_query($this->database_connection, $sql)){
					    $id = @mysqli_insert_id($this->database_connection) ;
						$pluginInfo['id'] = $id  ;
					}else{
						//WHAT WAS THIS? /// return false ;
						$this->breakExecution("Error while inserting into plugins $name \n" .mysqli_error($this->database_connection) );						
						
					}
					
					
					//2. IF Plugin defines types, INSERT INTO ITS TABLE
					if (count(array_var($pluginInfo,'types',array()))){
						foreach ($pluginInfo['types'] as $k => $type ) {
							if (isset($type['name'])) {
								$sql = "
									INSERT INTO ". $this->table_prefix ."object_types (name, handler_class, table_name, type, icon, plugin_id)
									 	VALUES (
									 	'".array_var($type,"name")."', 
									 	'".array_var($type,"handler_class")."', 
									 	'".array_var($type,"table_name")."', 
									 	'".array_var($type,"type")."', 
									 	'".array_var($type,"icon")."', 
										$id
									)";
								if (@mysqli_query($this->database_connection, $sql)){
								    $pluginInfo['types'][$k]['id'] = @mysqli_insert_id($this->database_connection) ;
								    $type['id'] =  @mysqli_insert_id($this->database_connection) ;
									
								}else{
									echo "FAIL INSTALLING TYPES <br>";
									echo mysqli_error($this->database_connection)."<br/>";
									echo $sql."<br/>";
								}
								

							}
						}
					}
					//2. IF Plugin defines tabs, INSERT INTO ITS TABLE
					if (count(array_var($pluginInfo,'tabs',array()))){
						foreach ($pluginInfo['tabs'] as $k => $tab ) {
							if (isset($tab['title'])) {
								$type_id = array_var($type,"id") ;
								$sql = "
									INSERT INTO ". $this->table_prefix ."tab_panels (
										id,
										title, 
										icon_cls, 
										refresh_on_context_change, 
										default_controller, 
										default_action, 
										initial_controller, 
										initial_action, 
										enabled, 
										type,  
										plugin_id, 
										object_type_id )
								 	VALUES (
								 		'".array_var($tab,'id')."', 
								 		'".array_var($tab,'title')."', 
								 		'".array_var($tab,'icon_cls')."',
								 		'".array_var($tab,'refresh_on_context_change')."',
								 		'".array_var($tab,'default_controller')."',
								 		'".array_var($tab,'default_action')."',
										'".array_var($tab,'initial_controller')."',
										'".array_var($tab,'initial_action')."',
										'".array_var($tab,'enabled',1)."',
										'".array_var($tab,'type')."',
										$id,
										".array_var($tab,'object_type_id')."
									)";
										if (!@mysqli_query($this->database_connection, $sql)){
									echo $sql ;
									echo mysqli_error($this->database_connection);
								}
							}
						}
					}
					
					// Create schema sql query
					$schema_creation = INSTALLATION_PATH."/plugins/$name/install/sql/mysql_schema.php" ;
					if ( file_exists($schema_creation) ){
						$total_queries = 0;
						$executed_queries = 0;
						if($this->executeMultipleQueries(tpl_fetch($schema_creation), $total_queries, $executed_queries)) {
							$this->printMessage("Schema created for plugin $name ");	
						}else{
							$this->breakExecution("Error while creating schema for plugin $name".mysqli_error($this->database_connection));
							//$this->printMessage("Error while creating schema for plugin $name".mysqli_error($this->database_connection));
						}
					} 

					// Create schema sql query
					$schema_query = INSTALLATION_PATH."/plugins/$name/install/sql/mysql_initial_data.php" ;
					if ( file_exists($schema_query) ){
						$total_queries = 0;
						$executed_queries = 0;
						if($this->executeMultipleQueries(tpl_fetch($schema_query), $total_queries, $executed_queries)) {
							$this->printMessage("Initial data loaded for plugin  '$name'.".mysqli_error($this->database_connection));	
						}else{
							$this->breakExecution("Error while loading inital data for plugin '$name'. ".mysqli_error($this->database_connection));
						}	
					}
						
					$install_script = INSTALLATION_PATH."/plugins/$name/install/install.php" ;
					if ( file_exists($install_script) ){
						$queries = array();
					
						include_once $install_script;
						$function_name = $name."_get_additional_install_queries";
						if (function_exists($function_name)) {
							$queries = $function_name($this->database_connection, $this->table_prefix);
						}
					
						$total_queries = 0;
						$executed_queries = 0;
						if($this->executeMultipleQueries(implode("\n", $queries), $total_queries, $executed_queries)) {
							$this->printMessage("File install.php processed for plugin $name ");
						}else{
							echo mysqli_error($this->database_connection);
							$this->breakExecution("Error while executing install.php for plugin '$name'.".mysqli_error($this->database_connection));
							$this->database_connection->rollback();
							return false;
						}
					}
				}
			}
		}
	}
}

