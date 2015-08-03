<?php

final class acInstallation {

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
	private $database_type = 'mysql';

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
		if($this->database_connection = @mysql_connect($database_host, $database_user, $database_pass)) {
			$connected = @mysql_select_db($database_name, $this->database_connection);
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
		$mysql_version = mysql_get_server_info($this->database_connection);
		if($mysql_version && version_compare($mysql_version, '4.1', '>=')) {
			$constants['DB_CHARSET'] = 'utf8';
			@mysql_query("SET NAMES 'utf8'", $this->database_connection);
			tpl_assign('default_collation', 'collate utf8_unicode_ci');
			tpl_assign('default_charset', 'DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
		} else {
			tpl_assign('default_collation', '');
			tpl_assign('default_charset', '');
		} // if

		@mysql_query('BEGIN WORK', $this->database_connection);

		// Database construction
		$total_queries = 0;
		$executed_queries = 0;
		if($this->executeMultipleQueries(tpl_fetch(get_template_path('sql/mysql_schema.php')), $total_queries, $executed_queries)) {
			$this->printMessage("Tables created in '$database_name'. (Executed queries: $executed_queries)");
		} else {
			return $this->breakExecution('Failed to import database construction. MySQL said: ' . mysql_error($this->database_connection));
		} // if

		// Initial data
		$total_queries = 0;
		$executed_queries = 0;
		if($this->executeMultipleQueries(tpl_fetch(get_template_path('sql/mysql_initial_data.php')), $total_queries, $executed_queries)) {
			$this->printMessage("Initial data imported into '$database_name'. (Executed queries: $executed_queries)");
		} else {
			return $this->breakExecution('Failed to import initial data. MySQL said: ' . mysql_error($this->database_connection));
		} // if
		
		//Execute plugin sql files
		$handle = opendir(get_template_path('sql/plugins'));
		while ($file = readdir($handle)) {
			if ($file != 'dummy.txt' && is_file(get_template_path("sql/plugins/$file"))) {
				$total_queries = 0;
				$executed_queries = 0;
				if($this->executeMultipleQueries(tpl_fetch(get_template_path("sql/plugins/$file")), $total_queries, $executed_queries)) {
					$this->printMessage("Plugin executed: $file. (Executed queries: $executed_queries)");
				} else {
					return $this->breakExecution('Failed to execute plugin: '. $file . '. MySQL said: ' . mysql_error($this->database_connection));
				} // if
			}
		}
		closedir($handle);

		$this->installPlugins($plugins);
		
		
		
		
		@mysql_query('COMMIT', $this->database_connection);

		
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
		$this->printMessage($error_message, true);
		if(is_resource($this->database_connection)) {
			@mysql_query('ROLLBACK', $this->database_connection);
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
		if($result = mysql_query("SHOW VARIABLES LIKE 'have_innodb'", $this->database_connection)) {
			if($row = mysql_fetch_assoc($result)) {
				return strtolower(array_var($row, 'Value')) == 'yes';
			} // if
		} // if
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
				if(@mysql_query(trim($query))) {
					$executed_queries++;
				} else {
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
		@mysql_query('BEGIN WORK');
		
		tpl_assign('table_prefix', $this->table_prefix);
		tpl_assign('engine', $this->database_engine);
		
		if (count($pluginNames)) {
			foreach  ($pluginNames as $name ) {
				$path  = INSTALLATION_PATH."/plugins/$name/info.php";
				if (file_exists($path)) {
					$pluginInfo = include_once $path;
					//0. Check if exists in plg table
					$sql = "SELECT id FROM ". $this->table_prefix."plugins WHERE name = '$name' ";
					$res = @mysql_query($sql);
					if (!$res) {
						@mysql_query('ROLLBACK');
						return false;
					}
					$plg_obj =  mysql_fetch_object($res);
					if (!$plg_obj){
						//1. Insert into PLUGIN TABLE
						$cols = "name, is_installed, is_activated";
						$values = "'$name', 1, 1 " ;
						if (is_numeric(array_var($pluginInfo,'id')) ){
							$cols = "id, ". $cols ;
							$values = array_var($pluginInfo,'id').", ".$values ;
						}
						$sql = "INSERT INTO ". $this->table_prefix ."plugins ($cols) VALUES ($values) "; 
						if (@mysql_query($sql)){
							$id = @mysql_insert_id() ;
							$pluginInfo['id'] = $id  ;
						}else{
							echo "ERROR: ".mysql_error();
							@mysql_query('ROLLBACK');
							return false ;
						}
					}else {
						$id = $plg_obj->id;
						$pluginInfo['id'] = $id ;
					}
					//2. IF Plugin defines types, INSERT INTO ITS TABLE
					if (count(array_var($pluginInfo,'types'))){
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
								if (@mysql_query($sql)){
									$pluginInfo['types'][$k]['id'] = @mysql_insert_id() ;
									$type['id'] =  @mysql_insert_id() ;
									
								}else{
									echo $sql."<br/>";
									echo mysql_error()."<br/>";
									@mysql_query('ROLLBACK');
									return false;
								}
								

							}
						}
					}
					//2. IF Plugin defines tabs, INSERT INTO ITS TABLE
					if (count(array_var($pluginInfo,'tabs'))){
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
										
								if (!@mysql_query($sql)){
									echo $sql ;
									echo mysql_error();
									@mysql_query('ROLLBACK');
									$this->breakExecution();
									return false ;
								}
								
								// INSERT INTO TAB PANEL PERMISSSION
								$sql = "
									INSERT INTO ". $this->table_prefix ."tab_panel_permissions (
										permission_group_id,
										tab_panel_id 
									)
								 	VALUES ( 1,'".array_var($tab,'id')."' ),  ( 2,'".array_var($tab,'id')."' )  ON DUPLICATE KEY UPDATE permission_group_id = permission_group_id ";
								
								if (!@mysql_query($sql)){
									echo $sql ;
									echo mysql_error();
									@mysql_query('ROLLBACK');
									$this->breakExecution();
									return false ;
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
							$this->breakExecution("Error while creating schema for plugin $name".mysql_error());
							DB::rollback();
							return false;
							//$this->printMessage("Error while creating schema for plugin $name".mysql_error());
						}
					} 

					// Create schema sql query
					$schema_query = INSTALLATION_PATH."/plugins/$name/install/sql/mysql_initial_data.php" ;
					if ( file_exists($schema_query) ){
						$total_queries = 0;
						$executed_queries = 0;
						if($this->executeMultipleQueries(tpl_fetch($schema_query), $total_queries, $executed_queries)) {
							$this->printMessage("Initial data loaded for plugin  '$name'.".mysql_error());	
						}else{
							echo mysql_error();
							$this->breakExecution("Error while loading inital data for plugin '$name'.".mysql_error());
							DB::rollback();
							return false;
						}	
					}
						
					$install_script = INSTALLATION_PATH."/plugins/$name/install/install.php" ;
					if ( file_exists($install_script) ){
						include_once $install_script;
					}
					@mysql_query('COMMIT');
				}
			}
			return true ;
		}
	}
}

