<?php 

class PluginController extends ApplicationController {
	
	/**
	 * Remove invalid characters froim version  
	 */
	private function cleanVersion($version){
		 return str_replace(array('.',' ','_','-'), '' , $replace, strtolower($version));
	}
	
	function scanPlugins() {
		$plugins = array();
		$dir =	ROOT."/plugins";
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
		foreach ($plugins as $plg){
			if (! Plugins::instance()->findOne(array("conditions"=>"name = '".$plg['name'] ."'")) ) {
				$plugin = new Plugin();
				//if ( isset($plg["id"]) && is_numeric($plg["id"]) ) {
					//$plugin->setId($plg['id']);
				//}
				$plugin->setName($plg["name"]);
				$plugin->setIsActivated(0);
				$plugin->setIsInstalled(0);
				$plugin->setVersion(array_var($plg,'version'));
				$plugin->save();					
			}
		}
	} 
	
	function show_error_message($plugin, $error, $action) {

		$name = $plugin instanceof Plugin ? $plugin->getName() : 'n/a';
		$message = "Error executing $action for plugin '$name':\n\n" . $error->getMessage() . "\n\n" . $error->getTraceAsString();

		if (defined('CONSOLE_MODE')) { // executing by command line
			
			fwrite(STDERR, $message . "\n");
			throw $error; // for plugin-console.php to catch and exit with error

		} else { // executing by interface
			ajx_extra_data(array('errorMessage' => nl2br($message)));
		}
	}

	function show_success_message($message) {
		if (defined('CONSOLE_MODE')) { // executing by command line
			echo $message . "\n";
		}
	}

	function update($id = null) {
		ajx_current("empty");
		$from_post = false;
		if (empty($id)){
			$id = array_var($_POST, 'id');
			$from_post = true;
		}
		try {
			$name = '';
			if ( $plg = Plugins::instance()->findById($id)) {
				if ($plg->isInstalled() && $plg->updateAvailable()){
					
					// ensure that some specific columns are present before using objects in any update
					$this->check_columns_exist_before_updates();

					$name = $plg->getName();
					$plg->update();
					
					DimensionAssociationsConfigs::ensureAllAssociationsHaveConfigOptions();

					$this->show_success_message("Plugin $name updated successfully");
				}
			}
		} catch (Error $e) {
			$this->show_error_message($plg, $e, 'update');
		} catch (Exception $e) {
			$this->show_error_message($plg, $e, 'update');
		}
	}
	
	function updateAll() {

		// ensure that some specific columns are present before using objects in any update
		$this->check_columns_exist_before_updates();

		try {
			$plugins = Plugins::instance()->findAll(array('conditions' => 'is_installed=1'));
			foreach ($plugins as $plg) {
				if ($plg->updateAvailable()) {
					$plg->update();
					$this->show_success_message("Plugin ".$plg->getName()." updated successfully");
				}
			}
			DimensionAssociationsConfigs::ensureAllAssociationsHaveConfigOptions();
		} catch (Error $e) {
			$this->show_error_message($plg, $e, 'update');
		} catch (Exception $e) {
			$this->show_error_message($plg, $e, 'update');
		}
	}
	
	function uninstall($id = null) {
		ajx_current("empty");
		if (!$id) {
			$id=get_id();
		}
		if ( $plg  = Plugins::instance()->findById($id)) {
			if (!$plg->isInstalled()) return ;
			$plg->setIsInstalled(0);
			$plg->save();
			$name= $plg->getSystemName();
			$path = ROOT . "/plugins/$name/uninstall.php";
			if (file_exists($path)){
				include_once $path;
			}
			$this->show_success_message("Plugin $name uninstalled successfully");
		}
	}
	
	function install($id = null){
		ajx_current("empty");
		$from_post = false;
		if (empty($id)){
			$id = array_var($_POST, 'id');
			$from_post = true;
		}
		if ($plg = Plugins::instance()->findById($id)) {
			$name = $plg->getName();
			try {
				$this->executeInstaller($name);
				$plg->setIsInstalled(1);
				$plg->save();
				
				DimensionAssociationsConfigs::ensureAllAssociationsHaveConfigOptions();

				$this->show_success_message("Plugin ".$plg->getName()." installed successfully");

			} catch (Error $e) {
				$this->show_error_message($plg, $e, 'install');
			} catch (Exception $e) {
				$this->show_error_message($plg, $e, 'install');
			}
		}
	}
	
	function activate(){
		ajx_current("empty");
		$id=array_var($_POST,'id');
		if ( $plg  = Plugins::instance()->findById($id)) {
			$plg->activate();
			$this->show_success_message("Plugin ".$plg->getName()." activated successfully");
		}
	}
	
	function deactivate() {
		ajx_current("empty");
		$id=array_var($_POST,'id');
		if ( $plg  = Plugins::instance()->findById($id)) {
			$plg->deactivate();
			$this->show_success_message("Plugin ".$plg->getName()." deactivated successfully");
		}
	}
	
	function __construct() {
		if (!defined('PLUGIN_MANAGER') && !defined('PLUGIN_MANAGER_CONSOLE')) {
			die(lang('no access permissions'));
		}
		parent::__construct();
		prepare_company_website_controller($this, 'website'); 
		if(!can_manage_plugins(logged_user())) {
			die(lang('no access permissions'));
		}
	}

	private function get_deprecated_plugins() {
		return array(
			"advanced_expenses",
			"aoac",
			"attendance_absence_tracking",
			"bca",
			"diprode",
			"expenses",
			"fidelis",
			"interra_networks",
			"inventory_management",
			"overtime_calculations",
			"paemfe",
			"status_dimension_1",
			"weill_cornell_reports",
		);
	}
	
	function index() {
		require_javascript("og/modules/plugins.js");
		$this->scanPlugins(); // If there are plguins not scanned		

		$deprecated_plugins = $this->get_deprecated_plugins();

		$plugins = Plugins::instance()->findAll(array(
			"conditions" => "name NOT IN ('".implode("','", $deprecated_plugins)."')",
			"order"=>"name ASC",
		));
				
		tpl_assign('plugins', $plugins);
		return $plugins ;
	}
	
	function ensure_installed_and_activated($plugin_name) {
		
		$plugin = Plugins::instance()->findOne(array('conditions' => "name='$plugin_name'"));
		if (!Plugins::instance()->isActivePlugin($plugin_name)) {
			if (!$plugin instanceof Plugin) return;
			if (!$plugin->isInstalled()) {
				$this->executeInstaller($plugin_name);
				$plugin = Plugins::instance()->findOne(array('conditions' => "name='$plugin_name'"));
			}
			$plugin->activate();
		}
		
		// ensure that is running in the last version
		if ($plugin instanceof Plugin) {
			$plugin->update();
		}
		
		return true;
	}

	/**
	 * check that plugin is installed and activated
	 */
	function check_installed_and_activated($plugin_name) {
		return Plugins::instance()->isActivePlugin($plugin_name);
	}

	/**
	 * Here add the columns that are needed before executing any update that use objects
	 * that tries to load columns that not exists because they are added in a future update
	 * 
	 * This is a temporary patch before developing a more structured way to prevent this kind of errors
	 */
	function check_columns_exist_before_updates() {
		
		if ($this->check_installed_and_activated('advanced_billing')) {
			Env::useHelper('update_script_functions', 'advanced_billing');
			add_fixed_fee_and_eaned_value_columns_to_task_table();
		}

	}
	
	
/**
 * @param array of string $pluginNames
 */
static function executeInstaller($name) {
	
	$table_prefix = TABLE_PREFIX;
	tpl_assign('table_prefix', $table_prefix);
	
	$default_charset = 'DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
	tpl_assign('default_charset', $default_charset);
	
	$default_collation = 'collate utf8_unicode_ci';
	tpl_assign('default_collation', $default_collation);
	
	$engine = DB_ENGINE;
	tpl_assign('engine', $engine);
		
	try {
		$path = ROOT . "/plugins/$name/info.php";
		if (file_exists ( $path )) {
			DB::beginWork ();
			$pluginInfo = include_once $path;
			
			//0. Check if exists in plg table
			$sql = "SELECT id FROM " . TABLE_PREFIX . "plugins WHERE name = '$name' ";
			$plg_obj = DB::executeOne($sql);
			if (! $plg_obj) {
				//1. Insert into PLUGIN TABLE
				$cols = "name, is_installed, is_activated, version";
				$values = "'$name', 1, 1 ,'".array_var ( $pluginInfo, 'version' )."'";
				if (is_numeric ( array_var ( $pluginInfo, 'id' ) )) {
					$cols = "id, " . $cols;
					$values = array_var ( $pluginInfo, 'id' ) . ", " . $values;
				}
				$sql = "INSERT INTO " . TABLE_PREFIX . "plugins ($cols) VALUES ($values) ON DUPLICATE KEY UPDATE version='".array_var ( $pluginInfo, 'version' )."'";
				
				DB::executeOne($sql);
				$id = DB::lastInsertId();
				$pluginInfo ['id'] = $id;
				
			} else {
				$id = $plg_obj['id'];
				$pluginInfo ['id'] = $id;
				
				DB::executeOne("UPDATE " . TABLE_PREFIX . "plugins SET version='".array_var ( $pluginInfo, 'version' )."' WHERE id='".array_var ( $pluginInfo, 'id' )."'");
			}
			
			if (isset($pluginInfo['dependences']) && is_array($pluginInfo['dependences'])) {
				foreach ($pluginInfo['dependences'] as $dep_plugin_name) {
					if (!Plugins::instance()->isActivePlugin($dep_plugin_name)) {
						throw new Exception("To install this plugin you need to install '$dep_plugin_name' first.");
					}
				}
			}
			
			//2. IF Plugin defines types, INSERT INTO ITS TABLE
			if (count ( array_var ( $pluginInfo, 'types', array() ) )) {
				foreach ( $pluginInfo ['types'] as $k => $type ) {
					if (isset ( $type ['name'] )) {
						$sql = "
							INSERT INTO " . TABLE_PREFIX . "object_types (name, handler_class, table_name, type, icon, plugin_id)
							 	VALUES (
							 	'" . array_var ( $type, "name" ) . "', 
							 	'" . array_var ( $type, "handler_class" ) . "', 
							 	'" . array_var ( $type, "table_name" ) . "', 
							 	'" . array_var ( $type, "type" ) . "', 
							 	'" . array_var ( $type, "icon" ) . "', 
								$id
							) ON DUPLICATE KEY UPDATE name=name";
						
						DB::executeOne($sql);
						$last_id = DB::lastInsertId();
						$pluginInfo['types'][$k]['id'] = $last_id;
						$type['id'] = $last_id;
					}
				}
			}
			
			//2. IF Plugin defines tabs, INSERT INTO ITS TABLE
			if (count ( array_var ( $pluginInfo, 'tabs', array() ) ) > 0) {
				foreach ( $pluginInfo ['tabs'] as $k => $tab ) {
					if (isset ( $tab ['title'] )) {
						$type_id = array_var ( $type, "id" );
						$sql = "
							INSERT INTO " . TABLE_PREFIX . "tab_panels (
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
						 		'" . array_var ( $tab, 'id' ) . "', 
						 		'" . array_var ( $tab, 'title' ) . "', 
						 		'" . array_var ( $tab, 'icon_cls' ) . "',
						 		'" . array_var ( $tab, 'refresh_on_context_change' ) . "',
						 		'" . array_var ( $tab, 'default_controller' ) . "',
						 		'" . array_var ( $tab, 'default_action' ) . "',
								'" . array_var ( $tab, 'initial_controller' ) . "',
								'" . array_var ( $tab, 'initial_action' ) . "',
								'" . array_var ( $tab, 'enabled', 1 ) . "',
								'" . array_var ( $tab, 'type' ) . "',
								$id,
								" . array_var ( $tab, 'object_type_id' ) . "
							) ON DUPLICATE KEY UPDATE 
								id = VALUES(`id`), 
								title = VALUES(`title`), 
								icon_cls = VALUES(`icon_cls`), 
								refresh_on_context_change = VALUES(`refresh_on_context_change`), 
								default_controller = VALUES(`default_controller`), 
								default_action = VALUES(`default_action`), 
								initial_controller = VALUES(`initial_controller`), 
								initial_action = VALUES(`initial_action`), 
								enabled = VALUES(`enabled`), 
								type = VALUES(`type`), 
								plugin_id = VALUES(`plugin_id`),
								object_type_id = VALUES(`object_type_id`);
							";
						
						DB::executeOne($sql);
						
						// INSERT INTO TAB PANEL PERMISSSION
						$sql = "
							INSERT INTO " . TABLE_PREFIX . "tab_panel_permissions (
								permission_group_id,
								tab_panel_id 
							)
						 	VALUES ( 1,'" . array_var ( $tab, 'id' ) . "' ),  ( 2,'" . array_var ( $tab, 'id' ) . "' )  ON DUPLICATE KEY UPDATE permission_group_id = permission_group_id ";
						
						DB::executeOne($sql);
					}
				}
			}
			
			// Create schema sql query
			
			$schema_creation = ROOT . "/plugins/$name/install/sql/mysql_schema.php";
			if (file_exists ( $schema_creation )) {
				$total_queries = 0;
				$executed_queries = 0;
				executeMultipleQueries ( tpl_fetch ( $schema_creation ), $total_queries, $executed_queries );
				Logger::log("Schema created for plugin $name");
			}
			// Create schema sql query
			$schema_query = ROOT . "/plugins/$name/install/sql/mysql_initial_data.php";
			if (file_exists ( $schema_query )) {
				$total_queries = 0;
				$executed_queries = 0;
				executeMultipleQueries ( tpl_fetch ( $schema_query ), $total_queries, $executed_queries );
				Logger::log ( "Initial data loaded for plugin  '$name'." . mysqli_error(DB::connection()->getLink()) );
			}
			
			$install_script = ROOT . "/plugins/$name/install/install.php";
			if (file_exists ( $install_script )) {
				$queries = array();
			
				include_once $install_script;
				$function_name = $name."_get_additional_install_queries";
				if (function_exists($function_name)) {
					$queries = $function_name(DB::connection()->getLink(), TABLE_PREFIX);
				}
			
				$total_queries = 0;
				$executed_queries = 0;
				executeMultipleQueries ( implode("\n", $queries), $total_queries, $executed_queries );
				Logger::log ( "File install.php processed for plugin  '$name'." . mysqli_error(DB::connection()->getLink()) );
			}
			
			DB::execute("UPDATE ".TABLE_PREFIX."plugins SET is_installed=1 WHERE name='$name'");
			
			DB::commit ();
			
			@unlink(ROOT . '/cache/autoloader.php');
			
			return true;
		}
		
	} catch (Exception $e) {
		Logger::log("ERROR installing plugin $name".$e->getMessage());
		DB::rollback();
		throw $e;
	}
	
	return false;
}
	
}

