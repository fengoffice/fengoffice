<?php

// ---------------------------------------------------
//  System callback functions, registered automaticly
//  or in application/application.php
// ---------------------------------------------------




/**
 * Gets called, when an undefined class is being instanciated
 *d
 * @param_string $load_class_name
 */
function feng__autoload($load_class_name) {
	static  $loader ;
	//$loader = null;
	$class_name = strtoupper($load_class_name);

	// Try to get this data from index...
	if(isset($GLOBALS[AutoLoader::GLOBAL_VAR])) {
		if(isset($GLOBALS[AutoLoader::GLOBAL_VAR][$class_name])) {
			return include $GLOBALS[AutoLoader::GLOBAL_VAR][$class_name];
		} // if
	} // if
	//pre_print_r($loader) ;exit;
	
	if(!$loader) {
		$loader = new AutoLoader();
		$loader->addDir(ROOT . '/application');
		$loader->addDir(ROOT . '/environment');
		$loader->addDir(ROOT . '/library');
		
		//TODO Pepe: No tengo la conexion ni las clases de DB en este momento.. me conecto derecho 
		$temp_link  = mysql_connect(DB_HOST, DB_USER, DB_PASS) ;
		mysql_select_db(DB_NAME) ;
		$res = mysql_query("SELECT name FROM ".TABLE_PREFIX."plugins WHERE is_installed = 1;");
		while ($row = mysql_fetch_object($res)) {	
			$plugin_name =  strtolower($row->name) ;
			$dir  = ROOT . '/plugins/'.$plugin_name.'/application' ;
			if (is_dir($dir)) {
				$loader->addDir($dir); 
			}
		}
		mysql_close($temp_link);
		
		
		$loader->setIndexFilename(CACHE_DIR . '/autoloader.php');
		
	} // if

	try {
		$loader->loadClass($class_name);
	} catch(Exception $e) {
		try {
			if (function_exists("__autoload")) __autoload($class_name);
		} catch(Exception $ex) {
			die('Caught Exception in AutoLoader: ' . $ex->__toString());
		}
	} // try
} // __autoload

/**
 * Feng Office shutdown function
 *
 * @param void
 * @return null
 */
function __shutdown() {
	DB::close();
	$logger_session = Logger::getSession();
	if(($logger_session instanceof Logger_Session) && !$logger_session->isEmpty()) {
		Logger::saveSession();
	} // if
} // __shutdown

/**
 * This function will be used as error handler for production
 *
 * @param integer $code
 * @param string $message
 * @param string $file
 * @param integer $line
 * @return null
 */
function __production_error_handler($code, $message, $file, $line) {
	// Skip non-static method called staticly type of error...
	if($code == 2048) {
		return;
	} // if

	Logger::log("Error: $message in '$file' on line $line (error code: $code)", Logger::ERROR);
/*	$trace = debug_backtrace();
	Logger::log("trace count: ".count($trace));	
	foreach($trace as $tn=>$tr) {
		if (is_array($tr)) {
			Logger::log($tn . ": " . (isset($tr['file']) ? $tr['file']:'No File') . " " . (isset($tr['line']) ? $tr['line']:'No Line'));
		} 
	}*/
} // __production_error_handler

/**
 * This function will be used as exception handler in production environment
 *
 * @param Exception $exception
 * @return null
 */
function __production_exception_handler($exception) {
	Logger::log($exception, Logger::FATAL);
} // __production_exception_handler

// ---------------------------------------------------
//  Get URL
// ---------------------------------------------------

/**
 * Return an application URL
 *
 * If $include_project_id variable is presend active_project variable will be added to the list of params if we have a
 * project selected (active_project() function returns valid project instance)
 *
 * @param string $controller_name
 * @param string $action_name
 * @param array $params
 * @param string $anchor
 * @param boolean $include_project_id
 * @return string
 */
function get_url($controller_name = null, $action_name = null, $params = null, $anchor = null, $include_project_id = false) {
	$controller = trim($controller_name) ? $controller_name : DEFAULT_CONTROLLER;
	$action = trim($action_name) ? $action_name : DEFAULT_ACTION;
	if(!is_array($params) && !is_null($params)) {
		$params = array('id' => $params);
	}

	$url_params = array('c=' . $controller, 'a=' . $action);

	if(is_array($params)) {
		foreach($params as $param_name => $param_value) {
			if(is_bool($param_value)) {
				$url_params[] = $param_name . '=1';
			} else {
				$url_params[] = $param_name . '=' . urlencode($param_value);
			}
		}
	}

	if(trim($anchor) <> '') {
		$anchor = '#' . $anchor;
	}

	return with_slash(ROOT_URL) . 'index.php?' . implode('&', $url_params) . $anchor;
} // get_url

function get_sandbox_url($controller_name = null, $action_name = null, $params = null, $anchor = null, $include_project_id = false) {
	$controller = trim($controller_name) ? $controller_name : DEFAULT_CONTROLLER;
	$action = trim($action_name) ? $action_name : DEFAULT_ACTION;
	if(!is_array($params) && !is_null($params)) {
		$params = array('id' => $params);
	} // if

	$url_params = array('c=' . $controller, 'a=' . $action);

	if($include_project_id) {
		if(function_exists('active_project') && (active_project() instanceof Project)) {
			if(!(is_array($params) && isset($params['active_project']))) {
				$url_params[] = 'active_project=' . active_project()->getId();
			} // if
		} // if
	} // if

	if(is_array($params)) {
		foreach($params as $param_name => $param_value) {
			if(is_bool($param_value)) {
				$url_params[] = $param_name . '=1';
			} else {
				$url_params[] = $param_name . '=' . urlencode($param_value);
			} // if
		} // foreach
	} // if

	if(trim($anchor) <> '') {
		$anchor = '#' . $anchor;
	} // if

	if (defined('SANDBOX_URL')) {
		return with_slash(SANDBOX_URL) . 'index.php?' . implode('&', $url_params) . $anchor;
	} else {
		return with_slash(ROOT_URL) . 'index.php?' . implode('&', $url_params) . $anchor;
	}
} // get_sandbox_url

// ---------------------------------------------------
//  Product
// ---------------------------------------------------

/**
 * Return product name. This is a wrapper function that abstracts the product name
 *
 * @param void
 * @return string
 */
function product_name() {
	return PRODUCT_NAME;
} // product_name

/**
 * Return product version, wrapper function.
 *
 * @param void
 * @return string
 */
function product_version() {
	if (defined('DISPLAY_VERSION')) return DISPLAY_VERSION;
	return include ROOT . '/version.php';
} // product_version

/**
 * Return revision, to add as parameters when including static files, to control the browser's cache.
 *
 * @return string
 */
function product_version_revision() {
	try{
		$revision = @include ROOT . '/revision.php';
		return $revision;
	}
	catch(Exception $e){}
	
	return "";
}

function get_product_logo_filename() {
	return PRODUCT_LOGO_FILENAME;
}

function get_product_logo_url() {
	return get_image_url(get_product_logo_filename());
}

/**
 * Return installed version, wrapper function.
 *
 * @param void
 * @return string
 */
function installed_version() {
	$installed_version = config_option('installed_version');
	if ($installed_version) {
		return $installed_version;
	} else {
		$version = @include ROOT . '/config/installed_version.php';
		if ($version) {
			return $version;
		} else {
			return "unknown";
		}
	}
} // installed_version


/**
 * Returns product signature (name and version). If user is not logged in and
 * is not member of owner company he will see only product name
 *
 * @param void
 * @return string
 */
function product_signature() {
	if(function_exists('logged_user') && (logged_user() instanceof Contact)) {
		$result = lang('footer powered', clean(PRODUCT_URL), clean(product_name()) . ' ' . product_version());
		if(Env::isDebugging()) {
			ob_start();
			benchmark_timer_display(false);
			$result .= '. ' . ob_get_clean();
			if(function_exists('memory_get_usage')) {
				$result .= '. ' . format_filesize(memory_get_usage());
			} // if
		} // if
		return $result;
	} else {
		return  lang('footer powered', clean(PRODUCT_URL), clean(product_name()));
	} // if
} // product_signature

// ---------------------------------------------------
//  Request, routes replacement methods
// ---------------------------------------------------

/**
 * Return matched requst controller
 *
 * @access public
 * @param void
 * @return string
 */
function request_controller() {
	$controller = trim(array_var($_GET, 'c', DEFAULT_CONTROLLER));
	return $controller && is_valid_function_name($controller) ? $controller : DEFAULT_CONTROLLER;
} // request_controller

/**
 * Return matched request action
 *
 * @access public
 * @param void
 * @return string
 */
function request_action() {
	$action = trim(array_var($_GET, 'a', DEFAULT_ACTION));
	return $action && is_valid_function_name($action) ? $action : DEFAULT_ACTION;
} // request_action

// ---------------------------------------------------
//  Controllers and stuff
// ---------------------------------------------------

/**
 * Set internals of specific company website controller
 *
 * @access public
 * @param PageController $controller
 * @param string $layout Project or company website layout. Or any other...
 * @return null
 */
function prepare_company_website_controller(PageController $controller, $layout = 'website') {

	if (defined('CONSOLE_MODE') && CONSOLE_MODE) return;
	
	// If we don't have logged user prepare referer params and redirect user to login page
	if(!(logged_user() instanceof Contact)) {
		$ref_params = array();
		foreach($_GET as $k => $v) $ref_params['ref_' . $k] = $v;		
		$controller->redirectTo('access', 'login', $ref_params);
	} // if

	$controller->setLayout($layout);
	$controller->addHelper('form', 'breadcrumbs', 'pageactions', 'tabbednavigation', 'company_website', 'project_website', 'textile', 'dimension');
} // prepare_company_website_controller

// ---------------------------------------------------
//  Company website interface
// ---------------------------------------------------

/**
 * Return owner company object if we are on company website and it is loaded
 *
 * @access public
 * @param void
 * @return Company
 */
function owner_company() {
	return CompanyWebsite::instance()->getCompany();
} // owner_company

/**
 * Return logged user if we are on company website
 *
 * @access public
 * @param void
 * @return Contact
 */
function logged_user() {
	return CompanyWebsite::instance()->getLoggedUser();
} // logged_user

//FIXME remove function
function active_project(){
	return null;
}

//FIXME remove function
function active_tag(){
	return null;
}


//FIXME remove function
function active_projects() {
	return true;
}

//FIXME remove function
function active_or_personal_project() {
	return true;
}

//FIXME remove function
function personal_project() {
	return true;
}
/**
 * 
 * @Feng 2.0 - ivazquez 
 * 
 */
function active_context() {
	return CompanyWebsite::instance()->getContext() ;
}

function current_dimension_id() {
	return array_var($_REQUEST,'currentdimension');
}

function current_member(){
	$did = current_dimension_id();
	if ( $did == 0 ) {
		return null ;
	}else{ 
		foreach (active_context() as $item){
			if ($item instanceof Member) {
				if ( $item->getDimensionId() == $did ) {
					return $item;
				}
			}
		}
	}
	return null ;   
}

function current_member_search(){
	$members = array();
	foreach (active_context() as $item){
		if ($item instanceof Member) {
			$members[] = $item;
		}
	}
	return $members;
}

function context_type() {
	foreach ( active_context() as $ctx ) {
		if ( $ctx instanceof Member ) {
			return "mixed";		
		}	
	}
	return "all";
}


function active_context_members($full = true ) {
	
	$ctxMembers  = array ();
	if (is_array(active_context())) {
		foreach (active_context() as $ctx) {
			if ( $ctx instanceof Member ) {
				/* @var Dimension $ctx */
				$ctxMembers[$ctx->getId()] = $ctx->getId() ;
				if($full){
					foreach ( Members::getSubmembers($ctx, 1) as $sub ) {
						$ctxMembers[$sub->getId()] = $sub->getId() ;		
					}
				}
				
			}
			
			if  ( $full && $ctx instanceof Dimension ) {
				/// @var Dimension $ctx 
				foreach ($ctx->getAllMembers() as $member) {
					$ctxMembers[$member->getId()] = $member->getId() ;
					foreach ( Members::getSubmembers($member, 1) as $sub ) {
						$ctxMembers[$sub->getId()] = $sub->getId() ;
					}
				} 
			}
		}
	}
	return $ctxMembers ;
}

function get_context_from_array($ids){
	$context = array();
	foreach ($ids as $id) {
		$member = Members::findById($id) ;
		$context[] = $member;
	}
	return $context ;
}


function active_context_can_contain_member_type($dimension_id, $member_type_id) {
	$context = active_context();
	foreach ($context as $selection) {
		if ($selection instanceof Dimension && $selection->getId() == $dimension_id) {
			// if no member of this dimension is selected return true
			return true;
		} else {
			if ($selection instanceof Member && $selection->getDimensionId() == $dimension_id) {
				// check if member type parameter can be descendant of the selected member type
				$child_ots = DimensionObjectTypeHierarchies::getAllChildrenObjectTypeIds($dimension_id, $selection->getObjectTypeId());
				return in_array($member_type_id, $child_ots);
			}
		}
	}
	return true;
}

/**
 * Return which is the upload hook
 * @return string
 */
function upload_hook() {
	if (!defined('UPLOAD_HOOK')) define('UPLOAD_HOOK', 'fengoffice');
	return UPLOAD_HOOK;
}


// ---------------------------------------------------
//  Config interface
// ---------------------------------------------------

/**
 * Return config option value
 *
 * @access public
 * @param string $name Option name
 * @param mixed $default Default value that is returned in case of any error
 * @return mixed
 */
function config_option($option, $default = null) {
	// check the cache for the option value
	if (GlobalCache::isAvailable()) {
		$option_value = GlobalCache::get('config_option_'.$option, $success);
		if ($success) return $option_value;
	}
	// value not found in cache
	$option_value = ConfigOptions::getOptionValue($option, $default);
	if (GlobalCache::isAvailable()) {
		GlobalCache::update('config_option_'.$option, $option_value);
	}
	
	return $option_value;
} // config_option

/**
 * Set value of specific configuration option
 *
 * @param string $option_name
 * @param mixed $value
 * @return boolean
 */
function set_config_option($option_name, $value) {
	$config_option = ConfigOptions::getByName($option_name);
	if(!($config_option instanceof ConfigOption)) {
		return false;
	}

	$config_option->setValue($value);
	
	// update cache if available
	if (GlobalCache::isAvailable()) {
		GlobalCache::update('config_option_'.$option_name, $value);
	}
	
	return $config_option->save();
} // set_config_option

/**
 * Return user config option value
 *
 * @access public
 * @param string $name Option name
 * @param mixed $default Default value that is returned in case of any error
 * @param int $user_id User Id, if null logged user is taken
 * @return mixed
 */
function user_config_option($option, $default = null, $user_id = null, $options_members = false) {
	if (is_null($user_id)) {
		if (logged_user() instanceof Contact) {
			$user_id = logged_user()->getId();
		} else if (is_null($default)) {
			$def_value = null;
			// check the cache for the option default value
			if (GlobalCache::isAvailable()) {
				$def_value = GlobalCache::get('user_config_option_def_'.$option, $success);
				if ($success) return $def_value;
			}
			// default value not found in cache
			$def_value = ContactConfigOptions::getDefaultOptionValue($option, $default);
			if (GlobalCache::isAvailable()) {
				GlobalCache::update('user_config_option_def_'.$option, $def_value);
			}
			return $def_value;
		} else {
			return $default;
		}
	}
	
	// check the cache for the option value
	if (GlobalCache::isAvailable()) {
		$option_value = GlobalCache::get('user_config_option_'.$user_id.'_'.$option, $success);
		if ($success) return $option_value;
	}
        
        if($options_members){
            $members = implode ( ',',active_context_members(false));
            // default value not found in cache
            $option_value = ContactConfigOptions::getOptionValue($option, $user_id, $default, $members);
        }else{
            $option_value = ContactConfigOptions::getOptionValue($option, $user_id, $default);
        }
	if (GlobalCache::isAvailable()) {
		GlobalCache::update('user_config_option_'.$user_id.'_'.$option, $option_value);
	}
	
	return $option_value;
} // user_config_option

function user_has_config_option($option_name, $user_id = 0, $workspace_id = 0) {
	//FIXME
	return;
	if (!$user_id && logged_user() instanceof User) {
		$user_id = logged_user()->getId();
	} else {
		return false;
	}
	$option = UserWsConfigOptions::getByName($option_name);
	if (!$option instanceof UserWsConfigOption) return false;
	$value = UserWsConfigOptionValues::findById(array(
		'option_id' => $option->getId(),
		'user_id' => $user_id,
		'workspace_id' => $workspace_id));
	return $value instanceof UserWsConfigOptionValue;
}

function default_user_config_option($option, $default = null) {
	return UserWsConfigOptions::getDefaultOptionValue($option, $default);
}


/**
 * Return user config option value
 *
 * @access public
 * @param string $name Option name
 * @param mixed $default Default value that is returned in case of any error
 * @param int $user_id User Id, if null logged user is taken
 * @return mixed
 */
function load_user_config_options_by_category_name($category_name) {
	ContactConfigOptions::getOptionsByCategoryName($category_name, true);
} // config_option

/**
 * Set value of specific user configuration option
 *
 * @param string $option_name
 * @param mixed $value
 * @param int $user_id User Id, if null logged user is taken
 * @return boolean
 */
function set_user_config_option($option_name, $value, $user_id = null ) {
	$config_option = ContactConfigOptions::getByName($option_name);
	if(!($config_option instanceof ContactConfigOption)) {
		return false;
	}
	$config_option->setContactValue($value, $user_id);
	
	// update cache if available
	if (GlobalCache::isAvailable()) {
		GlobalCache::update('user_config_option_'.$user_id.'_'.$option_name, $value);
	}
	
	return $config_option->save();
} // set_config_option


function alert($text) {
	evt_add("popup", array('title' => "Debug", 'message' => $text));
}
function alert_r($var) {
	alert(print_r($var,1));
}

function get_back_trace($return_array = false) {
	$back_trace = debug_backtrace();
	$array = array();
	foreach ($back_trace as $trace) 
		$array[] = $trace['file']." - line: ".$trace['line']." - ".(isset($trace['class'])?$trace['class']."::":"").$trace['function'];
	
	return ($return_array ? $array : print_r($array, 1));
}


// ---------------------------------------------------
//  Encryption/Decryption
// ---------------------------------------------------

function cp_encrypt($password, $time){
	//appending padding characters
	$newPass = rand(0,9) . rand(0,9);
	$c = 1;
	while ($c < 15 && (int)substr($newPass,$c-1,1) + 1 != (int)substr($newPass,$c,1)){
		$newPass .= rand(0,9);
		$c++;
	}
	$newPass .= $password;
	
	//applying XOR
	$newSeed = md5(SEED . $time);
	$passLength = strlen($newPass);
	while (strlen($newSeed) < $passLength) $newSeed.= $newSeed;
	$result = (substr($newPass,0,$passLength) ^ substr($newSeed,0,$passLength));
	
	return base64_encode($result);
}

function cp_decrypt($password, $time){
	$b64decoded = base64_decode($password);
	
	//applying XOR
	$newSeed = md5(SEED . $time);
	$passLength = strlen($b64decoded);
	while (strlen($newSeed) < $passLength) $newSeed.= $newSeed;
	$original_password = (substr($b64decoded,0,$passLength) ^ substr($newSeed,0,$passLength));
	
	//removing padding
	$c = 1;
	while($c < 15 && (int)substr($original_password,$c-1,1) + 1 != (int)substr($original_password,$c,1)){
		$c++;
	}
	return substr($original_password,$c+1);
}

// ---------------------------------------------------
//  Filesystem
// ---------------------------------------------------

function remove_dir($dir) {
	$dh = @opendir($dir);
	if (!is_resource($dh)) return;
    while (false !== ($obj = readdir($dh))) {
		if($obj == '.' || $obj == '..') continue;
		$path = "$dir/$obj";
		if (is_dir($path)) {
			remove_dir($path);
		} else {
			@unlink($path);
		}
	}
	@closedir($dh);
	@rmdir($dir);
}

function help_link() {
	$link = Localization::instance()->lang('wiki help link');
	if (is_null($link)) {
		$link = DEFAULT_HELP_LINK;
	}
	return $link;
}

// ---------------------------------------------------
//  Localization
// ---------------------------------------------------

/**
 * This returns the localization of the logged user, if not defined returns the one defined in config.php
 *
 * @return string
 */
function get_locale() {
	$locale = user_config_option("localization");
	if (!$locale) $locale = DEFAULT_LOCALIZATION;
	
	return $locale;
}

function get_ext_language_file($loc) {
	if (is_file(ROOT . "/language/$loc/_config.php")) {
		$config = include ROOT . "/language/$loc/_config.php";
		if (is_array($config)) {
			return array_var($config, '_ext_language_file', 'ext-lang-en-min.js');
		}
	}
	return 'ext-lang-en-min.js';
}

function get_language_name($loc) {
	if (is_file(ROOT . "/language/$loc/_config.php")) {
		$config = include ROOT . "/language/$loc/_config.php";
		if (is_array($config)) {
			return array_var($config, '_language_name', $loc);
		}
	}
	return $loc;
}

function get_workspace_css_properties($num) {
	static $workspaces_css = array (
    "main"  => array( "padding" => "1px 5px", "font-size" => "90%"),
    "0"  => array("border-color" => "#777777", "background-color" => "#EEEEEE", "color" => "#777777"),
    "1"  => array("color" => "#DEE5F2", "background-color" => "#5A6986", "border-color" => "#5A6986"),
    "2"  => array("color" => "#E0ECFF", "background-color" => "#206CE1", "border-color" => "#206CE1"),
    "3"  => array("color" => "#DFE2FF", "background-color" => "#0000CC", "border-color" => "#0000CC"),
    "4"  => array("color" => "#E0D5F9", "background-color" => "#5229A3", "border-color" => "#5229A3"),
    "5"  => array("color" => "#FDE9F4", "background-color" => "#854F61", "border-color" => "#854F61"),
    "6"  => array("color" => "#FFE3E3", "background-color" => "#CC0000", "border-color" => "#CC0000"),
    "7"  => array("color" => "#FFF0E1", "background-color" => "#EC7000", "border-color" => "#EC7000"),
    "8"  => array("color" => "#FADCB3", "background-color" => "#B36D00", "border-color" => "#B36D00"),
    "9"  => array("color" => "#F3E7B3", "background-color" => "#AB8B00", "border-color" => "#AB8B00"),
    "10"  => array("color" => "#FFFFD4", "background-color" => "#636330", "border-color" => "#636330"),
    "11"  => array("color" => "#F9FFEF", "background-color" => "#64992C", "border-color" => "#64992C"),
    "12"  => array("color" => "#F1F5EC", "background-color" => "#006633", "border-color" => "#006633"),
    "13"  => array("color" => "#5A6986", "background-color" => "#DEE5F2", "border-color" => "#5A6986"),
    "14"  => array("color" => "#206CE1", "background-color" => "#E0ECFF", "border-color" => "#206CE1"),
    "15"  => array("color" => "#0000CC", "background-color" => "#DFE2FF", "border-color" => "#0000CC"),
    "16"  => array("color" => "#5229A3", "background-color" => "#E0D5F9", "border-color" => "#5229A3"),
    "17"  => array("color" => "#854F61", "background-color" => "#FDE9F4", "border-color" => "#854F61"),
    "18"  => array("color" => "#CC0000", "background-color" => "#FFE3E3", "border-color" => "#CC0000"),
    "19"  => array("color" => "#EC7000", "background-color" => "#FFF0E1", "border-color" => "#EC7000"),
    "20"  => array("color" => "#B36D00", "background-color" => "#FADCB3", "border-color" => "#B36D00"),
    "21"  => array("color" => "#AB8B00", "background-color" => "#F3E7B3", "border-color" => "#AB8B00"),
    "22"  => array("color" => "#636330", "background-color" => "#FFFFD4", "border-color" => "#636330"),
    "23"  => array("color" => "#64992C", "background-color" => "#F9FFEF", "border-color" => "#64992C"),
    "24"  => array("color" => "#006633", "background-color" => "#F1F5EC", "border-color" => "#006633"),   
);
	
	if (!$num) $num = 0;
	return "border-color: ".$workspaces_css[$num]['border-color']."; background-color: ".$workspaces_css[$num]['background-color']."; color: ".$workspaces_css[$num]['color']."; 
	padding: ".$workspaces_css['main']['padding']."; font-size: ".$workspaces_css['main']['font-size'].";";
    
}


function module_enabled($module, $default = null) { 
	$module .= '-panel';
	$contact_pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV(logged_user()->getId(),false);
	return TabPanelPermissions::instance()->isModuleEnabled($module, $contact_pg_ids);
}


function create_user_from_email($email, $name, $type = 'guest', $send_notification = true) {
	return create_user(array(
		'username' => substr($email, 0, strpos($email, '@')),
		'display_name' => trim($name),
		'email' => $email,
		'type' => $type,
		'company_id' => owner_company()->getId(),
		'send_email_notification' => $send_notification,
	), '');
}


function create_user($user_data, $permissionsString, $rp_permissions_data = array(), $save_permissions = true) {
    
	// try to find contact by some properties 
	$contact_id = array_var($user_data, "contact_id") ;
	$contact =  Contacts::instance()->findById($contact_id) ; 
	
	if (!is_valid_email(array_var($user_data, 'email'))) {
		throw new Exception(lang("email value is required"));
	}

	if (!$contact instanceof Contact) {
		// Create a new user
		$contact = new Contact();
		$contact->setUsername(array_var($user_data, 'username'));
		$contact->setDisplayName(array_var($user_data, 'display_name'));
		$contact->setCompanyId(array_var($user_data, 'company_id'));
		$contact->setUserType(array_var($user_data, 'type'));
		$contact->setTimezone(array_var($user_data, 'timezone'));
		$contact->setFirstname($contact->getObjectName() != "" ? $contact->getObjectName() : $contact->getUsername());
		$contact->setObjectName();
		$user_from_contact = false;
	} else {
		// Create user from contact
		$contact->setUserType(array_var($user_data, 'type'));
		if (array_var($user_data, 'company_id')) {
			$contact->setCompanyId(array_var($user_data, 'company_id'));
		}	
		$contact->setUsername(array_var($user_data, 'username'));
		$contact->setTimezone(array_var($user_data, 'timezone'));
		$user_from_contact = true;
	}
	$contact->save();
	if (is_valid_email(array_var($user_data, 'email'))) {
		$user = Contacts::getByEmail(array_var($user_data, 'email'));
		if(!$user)
			$contact->addEmail(array_var($user_data, 'email'), 'personal', true);
	}
	
	
	//permissions
	$additional_name = "";
	$tmp_pg = PermissionGroups::findOne(array('conditions' => "`name`='User ".$contact->getId()." Personal'"));
	if ($tmp_pg instanceof PermissionGroup) {
		$additional_name = "_".gen_id();
	}
	$permission_group = new PermissionGroup();
	$permission_group->setName('User '.$contact->getId().$additional_name.' Personal');
	$permission_group->setContactId($contact->getId());
	$permission_group->setIsContext(false);
	$permission_group->setType("permission_groups");
	$permission_group->save();
	$contact->setPermissionGroupId($permission_group->getId());
	
	$null=null;
	Hook::fire('on_create_user_perm_group', $permission_group, $null);
	
	$contact_pg = new ContactPermissionGroup();
	$contact_pg->setContactId($contact->getId());
	$contact_pg->setPermissionGroupId($permission_group->getId());
	$contact_pg->save();

	if ( can_manage_security(logged_user()) ) {
		
		$sp = new SystemPermission();
		if (!$user_from_contact) {
			$rol_permissions=SystemPermissions::getRolePermissions(array_var($user_data, 'type'));
			if (is_array($rol_permissions)) {
				foreach($rol_permissions as $pr){
					$sp->setPermission($pr);
				}
			}
		}
		$sp->setPermissionGroupId($permission_group->getId());

		if (isset($user_data['can_manage_security'])) $sp->setCanManageSecurity(array_var($user_data, 'can_manage_security'));
		if (isset($user_data['can_manage_configuration'])) $sp->setCanManageConfiguration(array_var($user_data, 'can_manage_configuration'));
		if (isset($user_data['can_manage_templates'])) $sp->setCanManageTemplates(array_var($user_data, 'can_manage_templates'));
		if (isset($user_data['can_manage_time'])) $sp->setCanManageTime(array_var($user_data, 'can_manage_time'));
		if (isset($user_data['can_add_mail_accounts'])) $sp->setCanAddMailAccounts(array_var($user_data, 'can_add_mail_accounts'));
		if (isset($user_data['can_manage_dimensions'])) $sp->setCanManageDimensions(array_var($user_data, 'can_manage_dimensions'));
		if (isset($user_data['can_manage_dimension_members'])) $sp->setCanManageDimensionMembers(array_var($user_data, 'can_manage_dimension_members'));
		if (isset($user_data['can_manage_tasks'])) $sp->setCanManageTasks(array_var($user_data, 'can_manage_tasks'));
		if (isset($user_data['can_task_assignee'])) $sp->setCanTasksAssignee(array_var($user_data, 'can_task_assignee'));
		if (isset($user_data['can_manage_billing'])) $sp->setCanManageBilling(array_var($user_data, 'can_manage_billing'));
		if (isset($user_data['can_view_billing'])) $sp->setCanViewBilling(array_var($user_data, 'can_view_billing'));
		if (isset($user_data['can_see_assigned_to_other_tasks'])) $sp->setColumnValue('can_see_assigned_to_other_tasks', array_var($user_data, 'can_see_assigned_to_other_tasks'));
		
		Hook::fire('add_user_permissions', $sp, $other_permissions);
		if (!is_null($other_permissions) && is_array($other_permissions)) {
			foreach ($other_permissions as $k => $v) {
				$sp->setColumnValue($k, array_var($user_data, $k));
			}
		}
		$sp->save();
		
		$permissions_sent = array_var($_POST, 'manual_permissions_setted') == 1;
		
		// give permissions for user if user type defined in "give_member_permissions_to_new_users" config option
		$allowed_user_type_ids = config_option('give_member_permissions_to_new_users');
		if ($contact->isAdministrator() || !$permissions_sent && in_array($contact->getUserType(), $allowed_user_type_ids)) {
			ini_set('memory_limit', '512M');
			$permissions = array();
			$default_permissions = RoleObjectTypePermissions::instance()->findAll(array('conditions' => 'role_id = '.$contact->getUserType()));
			
			$dimensions = Dimensions::findAll();
			foreach ($dimensions as $dimension) {
				if ($dimension->getDefinesPermissions()) {
					$cdp = ContactDimensionPermissions::findOne(array("conditions" => "`permission_group_id` = ".$contact->getPermissionGroupId()." AND `dimension_id` = ".$dimension->getId()));
					if (!$cdp instanceof ContactDimensionPermission) {
						$cdp = new ContactDimensionPermission();
						$cdp->setPermissionGroupId($contact->getPermissionGroupId());
						$cdp->setContactDimensionId($dimension->getId());
					}
					$cdp->setPermissionType('check');
					$cdp->save();
					
					// contact member permisssion entries
					$members = DB::executeAll('SELECT * FROM '.TABLE_PREFIX.'members WHERE dimension_id='.$dimension->getId());
					foreach ($members as $member) {
						foreach ($default_permissions as $p) {
							// Add persmissions to sharing table
							$perm = new stdClass();
							$perm->m = $member['id'];
							$perm->r= 1;
							$perm->w= $p->getCanWrite();
							$perm->d= $p->getCanDelete();
							$perm->o= $p->getObjectTypeId();
							$permissions[] = $perm;
						}
					}
				}
			}
			$_POST['permissions'] = json_encode($permissions);
		} else {
			if ($permissions_sent) {
				$_POST['permissions'] = $permissionsString;
			} else {
				$_POST['permissions'] = "";
			}
		}
		
		if (config_option('let_users_create_objects_in_root') && ($contact->isAdminGroup() || $contact->isExecutive() || $contact->isManager())) {
			if ($permissions_sent) {
				foreach ($rp_permissions_data as $name => $value) {
					$ot_id = substr($name, strrpos($name, '_')+1);
					$cmp = new ContactMemberPermission();
					$cmp->setPermissionGroupId($permission_group->getId());
					$cmp->setMemberId(0);
					$cmp->setObjectTypeId($ot_id);
					$cmp->setCanDelete($value >= 3);
					$cmp->setCanWrite($value >= 2);
					$cmp->save();
				}
			} else {
				$default_permissions = RoleObjectTypePermissions::instance()->findAll(array('conditions' => 'role_id = '.$contact->getUserType()));
				foreach ($default_permissions as $p) {
					$cmp = new ContactMemberPermission();
					$cmp->setPermissionGroupId($permission_group->getId());
					$cmp->setMemberId(0);
					$cmp->setObjectTypeId($p->getObjectTypeId());
					$cmp->setCanDelete($p->getCanDelete());
					$cmp->setCanWrite($p->getCanWrite());
					$cmp->save();
				}
			}
		}
	}
	if(!isset($_POST['sys_perm']) && !$user_from_contact){
		$rol_permissions=SystemPermissions::getRolePermissions(array_var($user_data, 'type'));
		$_POST['sys_perm']=array();
		if (is_array($rol_permissions)) {
			foreach($rol_permissions as $pr){
				$_POST['sys_perm'][$pr]=1;
			}
		}
		
	}
	if(!isset($_POST['mod_perm']) && !$user_from_contact){
		$tabs_permissions=TabPanelPermissions::getRoleModules(array_var($user_data, 'type'));
		$_POST['mod_perm']=array();
		foreach($tabs_permissions as $pr){
			$_POST['mod_perm'][$pr]=1;
		}
	}
        
    $password = '';
	if (array_var($user_data, 'password_generator') == 'specify') {
		$perform_password_validation = true;
		// Validate input
		$password = array_var($user_data, 'password');
		if (trim($password) == '') {
			throw new Error(lang('password value required'));
		} // if
		if ($password <> array_var($user_data, 'password_a')) {
			throw new Error(lang('passwords dont match'));
		} // if
	} else {
		$user_data['password_generator'] = 'link';
		$perform_password_validation = false;
	}

	$contact->setPassword($password);   
	$contact->save();

	$user_password = new ContactPassword();
	$user_password->setContactId($contact->getId());
	$user_password->setPasswordDate(DateTimeValueLib::now());
	$user_password->setPassword(cp_encrypt($password, $user_password->getPasswordDate()->getTimestamp()));
	$user_password->password_temp = $password;
	$user_password->perform_validation = $perform_password_validation;
	$user_password->save();
        
	if (array_var($user_data, 'autodetect_time_zone', 1) == 1) {
		set_user_config_option('autodetect_time_zone', 1, $contact->getId());
	}
	
	/* create contact for this user*/

	ApplicationLogs::createLog($contact, ApplicationLogs::ACTION_ADD);

	// Set role permissions for active members
	$active_context = active_context();
	$sel_members = array();
	if (is_array($active_context) && !$permissions_sent) {
		$tmp_perms = array();
		if ($_POST['permissions'] != "") {
			$tmp_perms = json_decode($_POST['permissions']);
		}
		foreach ($active_context as $selection) {
			if ($selection instanceof Member) {
				$sel_members[] = $selection;
				$has_project_permissions = ContactMemberPermissions::instance()->count("permission_group_id = '".$contact->getPermissionGroupId()."' AND member_id = ".$selection->getId()) > 0;
				if (!$has_project_permissions) {
					$new_cmps = RoleObjectTypePermissions::createDefaultUserPermissions($contact, $selection);
					
					foreach ($new_cmps as $new_cmp) {
						$perm = new stdClass();
						$perm->m = $new_cmp->getMemberId();
						$perm->r= 1;
						$perm->w= $new_cmp->getCanWrite();
						$perm->d= $new_cmp->getCanDelete();
						$perm->o= $new_cmp->getObjectTypeId();
						$tmp_perms[] = $perm;
					}
				}
			}
		}
		if (count($tmp_perms) > 0) {
			$_POST['permissions'] = json_encode($tmp_perms);
		}
	}
	
	if($save_permissions){
		//save_permissions($contact->getPermissionGroupId(), $contact->isGuest());
		save_user_permissions_background(logged_user(), $contact->getPermissionGroupId(), $contact->isGuest());
	}
	Hook::fire('after_user_add', $contact, $null);
	
	// add user content object to associated members
	if (count($sel_members) > 0) {
		ObjectMembers::addObjectToMembers($contact->getId(), $sel_members);
		$contact->addToSharingTable();
	}
	
	return $contact;
}

// Warning don't use this function inside a mysql transaction, use it after comit.
function send_notification($user_data, $contact_id){
	$contact = Contacts::findById($contact_id);//$contact->getId()
	$password = '';
	// Send notification
	try {
		if (array_var($user_data, 'send_email_notification') && $contact->getEmailAddress()) {
			if (array_var($user_data, 'password_generator', 'link') == 'link') {
				// Generate link password
				$user = Contacts::getByEmail(array_var($user_data, 'email'));
				$token = sha1(gen_id() . (defined('SEED') ? SEED : ''));
				$timestamp = time() + 60*60*24;
				set_user_config_option('reset_password', $token . ";" . $timestamp, $user->getId());
				Notifier::newUserAccountLinkPassword($contact, $password, $token);
			} else {
				$password = array_var($user_data, 'password');
				Notifier::newUserAccount($contact, $password);
			}
				
		}
	} catch(Exception $e) {
		Logger::log($e->getTraceAsString());
	} // try
}

function utf8_safe($text) {
	$safe = html_entity_decode(htmlentities($text, ENT_COMPAT, "UTF-8"), ENT_COMPAT, "UTF-8");
	return preg_replace('/[\xF0-\xF4][\x80-\xBF][\x80-\xBF][\x80-\xBF]/', "", $safe);
}

function clean_csv_addresses($csv) {
	$addrs = explode(",", $csv);
	$parsed = array();
	$pending = false;
	foreach ($addrs as $addr) {
		$addr = trim($addr);
		if ($pending) {
			$addr = $pending . ", " . $addr;
			$pending = false;
		}
		if ($addr == "") continue;
		if ($addr[0] == '"') {
			$pos = strpos($addr, '"', 1);
			if ($pos !== false) {
				// valid address
			} else {
				// name contained a comma so it was split
				$pending = $addr;
				continue;
			}
			if (strpos($addr, '<') === false) {
				// invalid address. has quoted name part but no email address. leave it as is just in case
				$parsed[] = $addr;
				continue;
			}
		}
		if (strpos($addr, '<') === false) {
			$addr = "<$addr>";
		}
		$parsed[] = $addr;
	}
	return implode(",", $parsed);
}

/**
 * Converts HTML to plain text
 * @param $html
 * @return string
 */
function html_to_text($html) {
	include_once "library/html2text/class.html2text.inc";
	$h2t = new html2text($html);
	return $h2t->get_text(); 
}

/**
 * Returns an array with the enum values of an enum column
 * @param string $table: name of the table to check
 * @param string $column: name of the enum column to retrieve its values
 * @return An array with the enum values of an enum column.
 */
function get_enum_values($table, $column) {
	$sql = "SHOW COLUMNS FROM `$table` LIKE '$column';";
	$result = DB::execute($sql);
	$row = $result->fetchRow();
	preg_match_all( "/'(.*?)'/" , $row['Type'], $enum_array );
	$enum_fields = $enum_array[1];
	return $enum_fields;
}


function get_user_dimensions_ids(){
		
	//All dimensions
		$all_dimensions = Dimensions::findAll();
		$dimensions_to_show = array();
		
		foreach ($all_dimensions as $dim){
			if (!$dim->getDefinesPermissions()){
				$dimensions_to_show [$dim->getId()] = $dim->getId();
			}
			else{
				$contact_pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV(logged_user()->getId(),false);
				/*if dimension does not deny everything for each contact's PG, show it*/
				if (!$dim->deniesAllForContact($contact_pg_ids)){
					$dimensions_to_show [$dim->getId()] = $dim->getId();
				}
			}
		}
		return $dimensions_to_show;
}

function build_context_array($context_plain) {
	$context = null ;
	if (!empty($context_plain)) {
		$dimensions = json_decode($context_plain) ;
		if ($dimensions) {
			$context = array () ;
			foreach ($dimensions as $dimensionId => $members) {
				if (!$dimensionId || $dimensionId == 'undefined') continue;
				if ($members && is_array($members)) {
					//cambiar
					foreach ($members as $member) {
						if ($member && is_numeric($member)) { 
							$member = Members::findById($member) ;													
							if ($member instanceof Member ){
								$context[] = $member ;
							}
						}elseif($member === 0 && count($members)<=1){
							// IS root. Retrieve the dimension 
							$dimension = Dimensions::getDimensionById($dimensionId) ;								
							if ($dimension instanceof Dimension ){					
								$context[] = $dimension ;
							}
						}
					}
				}
			}
		}
	}
	return $context;
}

/**
 * @param string  $tableName
 * @param array $cols
 * @param array $rows
 * @param int $packageSize
 */
function massiveInsert($tableName, $cols,  $rows, $packageSize = 100, $on_duplicate_key="") {

	$total = count($rows);
	$totalPackets = ceil($total/$packageSize);
	$cols = implode(",", $cols);
	for ($i = 0 ; $i < $totalPackets ; $i++ ) {
		$sql = "INSERT INTO $tableName ($cols) VALUES  ";
		for ($j = $i * $packageSize ; $j < min ( ($i+1) * $packageSize , $total ) ; $j++ ) {
			$sql.= " (";
			$sql.="'".implode("','",$rows[$j])."'";
			$sql.=")";
			if ($j + 1 <  min ( ($i+1) * $packageSize , $total ) ){
				$sql.=",";
			}
		}
		
		$sql .= $on_duplicate_key;
			
		if (!DB::execute($sql)){
			throw new DBQueryError($sql);
		}
		$sql = null;
	}
	$cols = null;
} 


function prepare_email_addresses($addr_str) {
	// exclude \n \t characters
	$addr_str = str_replace(array("\n","\r","\t"), "", $addr_str);
	// replace ; with , to separate email addresses
	$addr_str = str_replace(";", ",", $addr_str);
	
	$result = array();
	$addresses = explode(",", $addr_str);
	foreach ($addresses as $addr) {
		$addr = trim($addr);
		if ($addr == '') continue;
		$pos = strpos($addr, "<");
		if ($pos !== FALSE && strpos($addr, ">", $pos) !== FALSE) {
			$name = trim(substr($addr, 0, $pos));
			$val = trim(substr($addr, $pos + 1, -1));
			if (preg_match(EMAIL_FORMAT, $val)) {
				$result[] = array($val, $name);
			}
		} else {
			if (preg_match(EMAIL_FORMAT, $addr)) {
				$result[] = array($addr);
			}
		}
	}
	return $result;
}

/**
 * Iused by installers (plugin installers) 
 */
function executeMultipleQueries($sql, &$total_queries = null , &$executed_queries = null ) {
	if(!trim($sql)) {
		$total_queries = 0;
		$executed_queries = 0;
	} // if

	// Make it work on PHP 5.0.4
	$sql = str_replace(array("\r\n", "\r"), array("\n", "\n"), $sql);

	$queries = explode(";\n", $sql);
	if(!is_array($queries) || !count($queries)) {
		$total_queries = 0;
		$executed_queries = 0;
	} 

	$total_queries = count($queries);
	foreach($queries as $query) {
		if(trim($query)) {
			DB::executeOne(trim($query));
			$executed_queries++;
		}
	}
}

function getAllRoleUsers($role){
	$contacts=Contacts::getAllUsers(" AND `user_type` = $role");
	$pgs=array();
	if(!$contacts)return false;
	foreach ($contacts as $contact){
		$pgs[]=$contact->getPermissionGroupId();
	}
	return $pgs;
}

function render_mailto($address) {
	if (logged_user()->hasEmailAccounts()){
		return "<a href=".get_url('mail', 'add_mail', array('to' => clean($address))).">".$address."</a>";
	}else{
		return "<a href='mailto:$address'>$address</a>";
	}
}

/**
 * Generic sort for many type of arrays
 * @param array $array
 * @param property, key or method $field 
 * @autor PHPepe.com
 */
function feng_sort($array, $field = 'getName', $id = 'getId', $removeDuplicateId = false){
	$ids = array();	
	$index = array(); 
	foreach ($array as $k => $row){
		// Elem is associative array and exists the key
		if (is_array($row) && array_key_exists($field, $row)){
			$val = strtolower($row[$field]);
			// Remove Duplicated ids
			if ($id && isset($row[$id])){
				if ($removeDuplicateId && isset($ids[$row[$id]])){
					continue ;
				}else{
					$ids[$row[$id]] = true ;
				}
			}
		}elseif (is_object($row) && ( isset($row->$field) || method_exists($row, $field))) {
			// Elem is an object and has $field as a propery or method
			if ( method_exists($row, $field)) {
				$val =  strtolower($row->$field());
			}elseif (property_exists($row, $field)){
				$val =  strtolower($row->$field);
			}

			// Remove Duplicated ids
			if ($id && method_exists($row, $field)){
				// $field is a method method
				if ($removeDuplicateId && isset($ids[$row->$id()])){
					continue ;
				}else{
					$ids[$row->$id()] = true ;
				}
			}
			
		}
		if (!empty($val) && !isset($index[$val]) ){
			$index[$val] = $row ;
		}else{
			$index[] = $row ;
		}
	}
	ksort($index);
	return $index; 
}

function controller_exists($name, $plugin_id) {
	$class_filename = ucfirst($name)."Controller.class.php";
	if ($plugin_id && ($plugin = Plugins::instance()->findById($plugin_id)) instanceof Plugin ){
		$plgName = $plugin->getName();
		return file_exists(ROOT."/plugins/".$plgName."/application/controllers/".$class_filename);
	}else{
		return file_exists(ROOT."/application/controllers/".$class_filename);
	}
}

function decodeAsciiHex($input) {
    $output = "";

    $isOdd = true;
    $isComment = false;

    for($i = 0, $codeHigh = -1; $i < strlen($input) && $input[$i] != '>'; $i++) {
        $c = $input[$i];

        if($isComment) {
            if ($c == '\r' || $c == '\n')
                $isComment = false;
            continue;
        }

        switch($c) {
            case '\0': case '\t': case '\r': case '\f': case '\n': case ' ': break;
            case '%': 
                $isComment = true;
            break;

            default:
                $code = hexdec($c);
                if($code === 0 && $c != '0')
                    return "";

                if($isOdd)
                    $codeHigh = $code;
                else
                    $output .= chr($codeHigh * 16 + $code);

                $isOdd = !$isOdd;
            break;
        }
    }

    if($input[$i] != '>')
        return "";

    if($isOdd)
        $output .= chr($codeHigh * 16);

    return $output;
}
function decodeAscii85($input) {
    $output = "";

    $isComment = false;
    $ords = array();
    
    for($i = 0, $state = 0; $i < strlen($input) && $input[$i] != '~'; $i++) {
        $c = $input[$i];

        if($isComment) {
            if ($c == '\r' || $c == '\n')
                $isComment = false;
            continue;
        }

        if ($c == '\0' || $c == '\t' || $c == '\r' || $c == '\f' || $c == '\n' || $c == ' ')
            continue;
        if ($c == '%') {
            $isComment = true;
            continue;
        }
        if ($c == 'z' && $state === 0) {
            $output .= str_repeat(chr(0), 4);
            continue;
        }
        if ($c < '!' || $c > 'u')
            return "";

        $code = ord($input[$i]) & 0xff;
        $ords[$state++] = $code - ord('!');

        if ($state == 5) {
            $state = 0;
            for ($sum = 0, $j = 0; $j < 5; $j++)
                $sum = $sum * 85 + $ords[$j];
            for ($j = 3; $j >= 0; $j--)
                $output .= chr($sum >> ($j * 8));
        }
    }
    if ($state === 1)
        return "";
    elseif ($state > 1) {
        for ($i = 0, $sum = 0; $i < $state; $i++)
            $sum += ($ords[$i] + ($i == $state - 1)) * pow(85, 4 - $i);
        for ($i = 0; $i < $state - 1; $i++)
            $ouput .= chr($sum >> ((3 - $i) * 8));
    }

    return $output;
}
function decodeFlate($input) {
    return @gzuncompress($input);
}

function getObjectOptions($object) {
    $options = array();
    if (preg_match("#<<(.*)>>#ismU", $object, $options)) {
        $options = explode("/", $options[1]);
        @array_shift($options);

        $o = array();
        for ($j = 0; $j < @count($options); $j++) {
            $options[$j] = preg_replace("#\s+#", " ", trim($options[$j]));
            if (strpos($options[$j], " ") !== false) {
                $parts = explode(" ", $options[$j]);
                $o[$parts[0]] = $parts[1];
            } else
                $o[$options[$j]] = true;
        }
        $options = $o;
        unset($o);
    }

    return $options;
}
function getDecodedStream($stream, $options) {
    $data = "";
    if (empty($options["Filter"]))
        $data = $stream;
    else {
        $length = !empty($options["Length"]) ? $options["Length"] : strlen($stream);
        $_stream = substr($stream, 0, $length);

        foreach ($options as $key => $value) {
            if ($key == "ASCIIHexDecode")
                $_stream = decodeAsciiHex($_stream);
            if ($key == "ASCII85Decode")
                $_stream = decodeAscii85($_stream);
            if ($key == "FlateDecode")
                $_stream = decodeFlate($_stream);
        }
        $data = $_stream;
    }
    return $data;
}
function getDirtyTexts(&$texts, $textContainers) {
    for ($j = 0; $j < count($textContainers); $j++) {
        if (preg_match_all("#\[(.*)\]\s*TJ#ismU", $textContainers[$j], $parts))
            $texts = array_merge($texts, @$parts[1]);
        elseif(preg_match_all("#Td\s*(\(.*\))\s*Tj#ismU", $textContainers[$j], $parts))
            $texts = array_merge($texts, @$parts[1]);
    }
}
function getCharTransformations(&$transformations, $stream) {
    preg_match_all("#([0-9]+)\s+beginbfchar(.*)endbfchar#ismU", $stream, $chars, PREG_SET_ORDER);
    preg_match_all("#([0-9]+)\s+beginbfrange(.*)endbfrange#ismU", $stream, $ranges, PREG_SET_ORDER);

    for ($j = 0; $j < count($chars); $j++) {
        $count = $chars[$j][1];
        $current = explode("\n", trim($chars[$j][2]));
        for ($k = 0; $k < $count && $k < count($current); $k++) {
            if (preg_match("#<([0-9a-f]{2,4})>\s+<([0-9a-f]{4,512})>#is", trim($current[$k]), $map))
                $transformations[str_pad($map[1], 4, "0")] = $map[2];
        }
    }
    for ($j = 0; $j < count($ranges); $j++) {
        $count = $ranges[$j][1];
        $current = explode("\n", trim($ranges[$j][2]));
        for ($k = 0; $k < $count && $k < count($current); $k++) {
            if (preg_match("#<([0-9a-f]{4})>\s+<([0-9a-f]{4})>\s+<([0-9a-f]{4})>#is", trim($current[$k]), $map)) {
                $from = hexdec($map[1]);
                $to = hexdec($map[2]);
                $_from = hexdec($map[3]);

                for ($m = $from, $n = 0; $m <= $to; $m++, $n++)
                    $transformations[sprintf("%04X", $m)] = sprintf("%04X", $_from + $n);
            } elseif (preg_match("#<([0-9a-f]{4})>\s+<([0-9a-f]{4})>\s+\[(.*)\]#ismU", trim($current[$k]), $map)) {
                $from = hexdec($map[1]);
                $to = hexdec($map[2]);
                $parts = preg_split("#\s+#", trim($map[3]));
                
                for ($m = $from, $n = 0; $m <= $to && $n < count($parts); $m++, $n++)
                    $transformations[sprintf("%04X", $m)] = sprintf("%04X", hexdec($parts[$n]));
            }
        }
    }
}
function getTextUsingTransformations($texts, $transformations) {
    $document = "";
    for ($i = 0; $i < count($texts); $i++) {
        $isHex = false;
        $isPlain = false;

        $hex = "";
        $plain = "";
        for ($j = 0; $j < strlen($texts[$i]); $j++) {
            $c = $texts[$i][$j];
            switch($c) {
                case "<":
                    $hex = "";
                    $isHex = true;
                break;
                case ">":
                    $hexs = str_split($hex, 4);
                    for ($k = 0; $k < count($hexs); $k++) {
                        $chex = str_pad($hexs[$k], 4, "0");
                        if (isset($transformations[$chex]))
                            $chex = $transformations[$chex];
                        $document .= html_entity_decode("&#x".$chex.";");
                    }
                    $isHex = false;
                break;
                case "(":
                    $plain = "";
                    $isPlain = true;
                break;
                case ")":
                    $document .= $plain;
                    $isPlain = false;
                break;
                case "\\":
                    $c2 = $texts[$i][$j + 1];
                    if (in_array($c2, array("\\", "(", ")"))) $plain .= $c2;
                    elseif ($c2 == "n") $plain .= '\n';
                    elseif ($c2 == "r") $plain .= '\r';
                    elseif ($c2 == "t") $plain .= '\t';
                    elseif ($c2 == "b") $plain .= '\b';
                    elseif ($c2 == "f") $plain .= '\f';
                    elseif ($c2 >= '0' && $c2 <= '9') {
                        $oct = preg_replace("#[^0-9]#", "", substr($texts[$i], $j + 1, 3));
                        $j += strlen($oct) - 1;
                        $plain .= html_entity_decode("&#".octdec($oct).";");
                    }
                    $j++;
                break;

                default:
                    if ($isHex)
                        $hex .= $c;
                    if ($isPlain)
                        $plain .= $c;
                break;
            }
        }
        $document .= "\n";
    }

    return $document;
}

function pdf2text($filename) {
    $infile = @file_get_contents($filename, FILE_BINARY);
    if (empty($infile))
        return "";

    $transformations = array();
    $texts = array();

    preg_match_all("#obj(.*)endobj#ismU", $infile, $objects);
    $objects = @$objects[1];

    for ($i = 0; $i < count($objects); $i++) {
        $currentObject = $objects[$i];

        if (preg_match("#stream(.*)endstream#ismU", $currentObject, $stream)) {
            $stream = ltrim($stream[1]);

            $options = getObjectOptions($currentObject);
            if (!(empty($options["Length1"]) && empty($options["Type"]) && empty($options["Subtype"])))
                continue;

            $data = getDecodedStream($stream, $options); 
            if (strlen($data)) {
                if (preg_match_all("#BT(.*)ET#ismU", $data, $textContainers)) {
                    $textContainers = @$textContainers[1];
                    getDirtyTexts($texts, $textContainers);
                } else
                    getCharTransformations($transformations, $data);
            }
        }
    }

    return getTextUsingTransformations($texts, $transformations);
}

function docx2text($filename) {
    return readZippedXML($filename, "word/document.xml","docx");
}

function odt2text($filename) {
    return readZippedXML($filename, "content.xml","odt");
}

function fodt2text($filename,$id) {    
    Env::useLibrary('ezcomponents');
    
    $odt = new ezcDocumentOdt();
    $odt->loadFile( $filename );

    $docbook = $odt->getAsDocbook();

    $converter = new ezcDocumentDocbookToRstConverter();
    $rst = $converter->convert( $docbook );
    
    $file_path_txt = 'tmp/fodt2text_' . $id . '.txt';
    file_put_contents( $file_path_txt, $rst );
    $content = file_get_contents($file_path_txt); //Guardamos archivo.txt en $archivo
    unlink($file_path_txt);
    return $content;
}

function readZippedXML($archiveFile, $dataFile, $type = null) {
    // Create new ZIP archive
    $zip = new ZipArchive;

    // Open received archive file
    if (true === $zip->open($archiveFile)) {
        // If done, search for the data file in the archive
        if (($index = $zip->locateName($dataFile)) !== false) {
            // If found, read it to the string
            $data = $zip->getFromIndex($index);
            
            //convert tabs to blank spaces
            if(!is_null($type)){
            	if($type == "odt"){
            		$data = str_replace ( "<text:tab/>" , " " , $data);
            	}elseif ($type == "docx"){
            		$data = str_replace ( "<w:tab/>" , " " , $data);
            	}
            }
            
            // Close archive file
            $zip->close();
            // Load XML from a string
            // Skip errors and warnings
            $xml = DOMDocument::loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
            // Return data without XML formatting tags
            return strip_tags($xml->saveXML());
        }
        $zip->close();
    }

    // In case of failure return empty string
    return "";
} 

function make_post_async($url, $params)	{
	foreach ($params as $key => &$val) {
		if (is_array($val)) $val = implode(',', $val);
		$post_params[] = $key.'='.urlencode($val);
	}
	$post_string = implode('&', $post_params);

	$parts = parse_url($url);

	$fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);

	$out = "POST ".$parts['path']." HTTP/1.1\r\n";
	$out.= "Host: ".$parts['host']."\r\n";
	$out.= "Content-Type: application/x-www-form-urlencoded\r\n";
	$out.= "Content-Length: ".strlen($post_string)."\r\n";
	$out.= "Connection: Close\r\n\r\n";
	if (isset($post_string)) $out.= $post_string;

	fwrite($fp, $out);
	sleep(1);
	fclose($fp);
}

/**
 * This function returns an array containing the column names of a table
 *
 * @param string $table_name Name of the table
 * @return array of strings
 */
function get_table_columns($table_name) {
	$cols = array();
	$res = mysql_query("DESCRIBE `$table_name`", DB::connection()->getLink());
	while($row = mysql_fetch_array($res)) {
		$cols[] = $row['Field'];
	}
	return $cols;
} // get_table_columns

/**
 * Checks if a column exists in a table
 *
 *  This function returns true if the column exists
 *
 * @param string $table_name Name of the table
 * @param string $col_name Name of the column
 * @return boolean
 */
function check_column_exists($table_name, $col_name) {
	$res = mysql_query("DESCRIBE `$table_name`", DB::connection()->getLink());
	while($row = mysql_fetch_array($res)) {
		if ($row['Field'] == $col_name) return true;
	}
	return false;
} // checkColumnExists

/**
 * Checks if a table exists
 *
 *  This function returns true if the table exists
 *
 * @param string $table_name Name of the table
 * @return boolean
 */
function checkTableExists($table_name) {
	$res = mysql_query("SHOW TABLES", DB::connection()->getLink());
	while ($row = mysql_fetch_array($res)) {
		if ($row[0] == $table_name) return true;
	}
	return false;
}

/**
 * Checks if 'exec()' function is enabled
 * 
 * @return boolean
 */
function is_exec_available() {
	if (ini_get('safe_mode')) {
		return false;
	} else {
		$d = ini_get('disable_functions');
		$s = ini_get('suhosin.executor.func.blacklist');
		if ("$d$s") {
			$array = preg_split('/,\s*/', "$d,$s");
			if (in_array('exec', $array)) {
				return false;
			}
		}
	}
	return true;
}

function pdf_convert_and_download($html_filename, $download_filename=null, $orientation="Portrait") {
	
	$html_to_convert = file_get_contents($html_filename);
	
	if (!$download_filename) $download_filename = gen_id() . '.pdf';
	
	//generate the pdf
	$pdf_filename = convert_to_pdf($html_to_convert, $orientation, gen_id());
	
	if($pdf_filename) {
		include_once ROOT . "/library/browser/Browser.php";
		if (Browser::instance()->getBrowser() == Browser::BROWSER_IE) {
			evt_add('download_tmp_file', array('file_name' => $pdf_filename, 'file_type' => 'application/pdf'));
		} else {
			download_file(ROOT."/tmp/".$pdf_filename, 'application/pdf', $download_filename, true);
		}
	}
}

function convert_to_pdf($html_to_convert, $orientation='Protrait', $genid) {
	$pdf_filename = null;
	
	if(is_exec_available()){
		//controlar q sea linux
		$pdf_filename = $genid . "_pdf.pdf";
		$pdf_path = "tmp/".$pdf_filename;
		
		$tmp_html_path = "tmp/tmp_html_".$genid.".html";
		file_put_contents($tmp_html_path, $html_to_convert);
		
		if (!in_array($orientation, array('Portrait', 'Landscape'))) $orientation = 'Portrait';
		
		//convert png to pdf in background
		exec("wkhtmltopdf -s A4 --encoding utf8 -O $orientation ".$tmp_html_path." ".$pdf_path." > /dev/null &", $result, $return_var);
		
		if ($return_var > 0){
			Logger::log("command not found convert",Logger::WARNING);
			return false;
		}
			
		//wait for the file
		$seconds = 8;
		while (!file_exists(ROOT."/tmp/".$pdf_filename) && $seconds > 0){
			sleep(1);
			$seconds = $seconds - 1;
		}
		//give time to finish
		sleep(2);
			
		//delete the png file
		unlink($tmp_html_path);
			
		//check if pdf exist
		if (!file_exists(ROOT."/tmp/".$pdf_filename)) {
			return false;
		}
		return $pdf_filename;
	}
	
}


/**
 * @param $picture: uploaded file data (taken from $_FILES)
 * @param $crop_data: array with new x-y coords, width and height
 * @return The path to the new generated image 
 **/
function process_uploaded_cropped_picture_file($picture, $crop_data) {

	$valid_exts = array('jpeg', 'jpg', 'png', 'gif');

	if (! $picture['error']) {
			
		$ext = strtolower(pathinfo($picture['name'], PATHINFO_EXTENSION));
		if (in_array($ext, $valid_exts)) {
			$path = 'tmp/' . uniqid() . '.' . $ext;
			$size = getimagesize($picture['tmp_name']);
			
			$x = (int) $crop_data['x'];
			$y = (int) $crop_data['y'];
			$w = (int) $crop_data['w'] ? $crop_data['w'] : $size[0];
			$h = (int) $crop_data['h'] ? $crop_data['h'] : $size[1];
			
			$ratio = $w / $h;
			if ($w < $h) {
				if ($w < 200) {
					$nw = 200;
					$nh = 200 / $ratio;
				} else {
					$nw = $w;
					$nh = $h;
				}
			} else {
				if ($h < 200) {
					$nh = 200;
					$nw = 200 * $ratio;
				} else {
					$nw = $w;
					$nh = $h;
				}
			}
			
			// if image width or height is greater than 500px (preview max size) then resize crop area to fit the same area as in the preview.
			if ($size[0] > 500 || $size[1] > 500) {
				$p = 500 / ($size[0] > $size[1] ? $size[0] : $size[1]);
				
				$x = $x / $p;
				$y = $y / $p;
				$w = $w / $p;
				$h = $h / $p;
				$nw = $nw / $p;
				$nh = $nh / $p;
			}

			$data = file_get_contents($picture['tmp_name']);
			$vImg = imagecreatefromstring($data);
			$dstImg = imagecreatetruecolor($nw, $nh);
			imagecopyresampled($dstImg, $vImg, 0, 0, $x, $y, $nw, $nh, $w, $h);
			imagepng($dstImg, $path);
			imagedestroy($dstImg);

			return $path;
				
		}
	}

}




function print_modal_json_response($data, $dont_process_response = true, $use_ajx = false) {
	$object = array('dont_process_response' => $dont_process_response);
	$object = array_merge($object, $data);
	if ($use_ajx) {
		ajx_extra_data($object);
	} else {
		echo json_encode($object);
	}
}






function associate_member_to_status_member($project_member, $old_project_status, $status_member_id, $status_dimension, $status_ot=null, $remove_prev_associations=true) {

	if ($status_dimension instanceof Dimension && in_array($status_dimension->getId(), config_option('enabled_dimensions'))) {

		// asociate project objects to the new project_status member
		if ($old_project_status != $status_member_id) {

			$object_members = ObjectMembers::instance()->findAll(array('conditions' => "member_id = ".$project_member->getId()." AND is_optimization=0"));

			// remove objects from old project_type member
			if ($old_project_status > 0) {
				foreach ($object_members as $om) {
					$obj = Objects::findObject($om->getObjectId());
					if ($obj instanceof ContentDataObject) {
						ObjectMembers::removeObjectFromMembers($obj, logged_user(), null, array($old_project_status));
					}
				}
			}

			// add objects to new project_type member
			if (is_numeric($status_member_id) && $status_member_id > 0) {
				$member_to_add = Members::findById($status_member_id);
				foreach ($object_members as $om) {
					ObjectMembers::addObjectToMembers($om->getObjectId(), array($member_to_add));
				}
				
				if ($member_to_add instanceof Member && $member_to_add->getObjectId()>0) {
					$rel_obj = Objects::findObject($member_to_add->getObjectId());
					if ($rel_obj instanceof ContentDataObject) {
						ObjectMembers::addObjectToMembers($rel_obj->getId(), array($project_member));
						$rel_obj->addToSharingTable();
						$null=null; Hook::fire("after_auto_classifying_associated_object_of_member", array('obj' => $rel_obj, 'mem' => $project_member), $null);
					}
				}
			}
			
		}


		$member_dimension = $project_member->getDimension();

		$a = DimensionMemberAssociations::instance()->findOne(array('conditions' => array('dimension_id=? AND object_type_id=? AND associated_dimension_id=?'.
				($status_ot instanceof ObjectType ? ' AND associated_object_type_id='.$status_ot->getId() : ''),
				$member_dimension->getId(), $project_member->getObjectTypeId(), $status_dimension->getId())));

		// create relation between members and remove old relations
		if ($a instanceof DimensionMemberAssociation) {
			if (is_numeric($status_member_id) && $status_member_id > 0) {

				$mpm = MemberPropertyMembers::findOne(array('id' => true, 'conditions' => array('association_id = ? AND member_id = ? AND property_member_id = ?', $a->getId(), $project_member->getId(), $status_member_id)));
				if (is_null($mpm)) {
					$sql = "INSERT INTO " . TABLE_PREFIX . "member_property_members (association_id, member_id, property_member_id, is_active, created_on, created_by_id)
						VALUES (" . $a->getId() . "," . $project_member->getId() . "," . $status_member_id . ", 1, NOW()," . logged_user()->getId() . ");";

					DB::executeAll($sql);
				}

			}
			if ($remove_prev_associations) {
				MemberPropertyMembers::instance()->delete('association_id = '.$a->getId().' AND member_id = '.$project_member->getId() . " AND property_member_id <> '$status_member_id'");
			}
		}
		
		
		$a = DimensionMemberAssociations::instance()->findOne(array('conditions' => array('associated_dimension_id=? AND associated_object_type_id=? AND dimension_id=?'.
				($status_ot instanceof ObjectType ? ' AND object_type_id='.$status_ot->getId() : ''),
				$member_dimension->getId(), $project_member->getObjectTypeId(), $status_dimension->getId())));
		
		// create relation between members and remove old relations
		if ($a instanceof DimensionMemberAssociation) {
			if (is_numeric($status_member_id) && $status_member_id > 0) {
		
				$mpm = MemberPropertyMembers::findOne(array('id' => true, 'conditions' => array('association_id = ? AND member_id = ? AND property_member_id = ?', $a->getId(), $project_member->getId(), $status_member_id)));
				if (is_null($mpm)) {
					$sql = "INSERT INTO " . TABLE_PREFIX . "member_property_members (association_id, member_id, property_member_id, is_active, created_on, created_by_id)
						VALUES (" . $a->getId() . "," . $status_member_id . "," . $project_member->getId() . ", 1, NOW()," . logged_user()->getId() . ");";
		
					DB::executeAll($sql);
				}
		
			}
			if ($remove_prev_associations) {
				MemberPropertyMembers::instance()->delete('association_id = '.$a->getId().' AND property_member_id = '.$project_member->getId() . " AND member_id <> '$status_member_id'");
			}
		}
	}
}

function get_all_associated_status_member_ids($member, $dimension, $ot=null) {
	$ids = array();
	if ($member instanceof Member && $dimension instanceof Dimension) {
		$member_dimension = $member->getDimension();
		if (!$member_dimension instanceof Dimension) return 0;

		$a = DimensionMemberAssociations::instance()->findOne(array('conditions' => array('dimension_id=? AND object_type_id=? AND associated_dimension_id=?'.
				($ot instanceof ObjectType ? ' AND associated_object_type_id='.$ot->getId() : ''),
				$member_dimension->getId(), $member->getObjectTypeId(), $dimension->getId())));

		// create relation between members and remove old relations
		if ($a instanceof DimensionMemberAssociation) {
			$mpms = MemberPropertyMembers::findAll(array('conditions' => array('association_id = ? AND member_id = ?', $a->getId(), $member->getId())));
			foreach ($mpms as $mpm) {
				$ids[] = intval($mpm->getPropertyMemberId());
			}
		}
	}
	return $ids;
}


function get_associated_status_member_id($member, $dimension, $ot=null) {
	if ($member instanceof Member && $dimension instanceof Dimension) {
		$member_dimension = $member->getDimension();
		if (!$member_dimension instanceof Dimension) return 0;

		$a = DimensionMemberAssociations::instance()->findOne(array('conditions' => array('dimension_id=? AND object_type_id=? AND associated_dimension_id=?'.
				($ot instanceof ObjectType ? ' AND associated_object_type_id='.$ot->getId() : ''),
				$member_dimension->getId(), $member->getObjectTypeId(), $dimension->getId())));
		
		// create relation between members and remove old relations
		if ($a instanceof DimensionMemberAssociation) {
			$mpm = MemberPropertyMembers::findOne(array('conditions' => array('association_id = ? AND member_id = ?', $a->getId(), $member->getId())));
			if ($mpm instanceof MemberPropertyMember) {
				return $mpm->getPropertyMemberId();
			}
		}
	}
	return 0;
}


function instantiate_template_task_parameters(TemplateTask $object, ProjectTask $copy, $parameterValues = array()) {
	
	$objProp = TemplateObjectProperties::getPropertiesByTemplateObject($object->getTemplateId(), $object->getId());
	$manager = $copy->manager();
	
	foreach($objProp as $property) {
	
		$propName = $property->getProperty();
		$value = $property->getValue();
	
		if ($manager->getColumnType($propName) == DATA_TYPE_STRING || $manager->getColumnType($propName) == DATA_TYPE_INTEGER) {
			if (is_array($parameterValues)){
				$is_present = false;
				foreach($parameterValues as $param => $val){
					if (strpos($value, '{'.$param.'}') !== FALSE) {
						$value = str_replace('{'.$param.'}', $val, $value);
						$is_present = true;
					}
				}
				// if parameter not present replace the parameter code with empty string
				if (!$is_present) {
					$value = preg_replace('/[{].*[}]/U', '', $value);
				}
			}
		} else if($manager->getColumnType($propName) == DATA_TYPE_DATE || $manager->getColumnType($propName) == DATA_TYPE_DATETIME) {
			$operator = '+';
			if (strpos($value, '+') === false) {
				$operator = '-';
			}
			$opPos = strpos($value, $operator);
			if ($opPos !== false) {
				// Is parametric
				$dateParam = substr($value, 1, strpos($value, '}') - 1);
				if ($dateParam == 'task_creation') {
					$date = DateTimeValueLib::now();					
				} else {
					$date = getDateValue($parameterValues[$dateParam]);
					if (!$date instanceof DateTimeValue) {				
						$date = DateTimeValueLib::now();						
					}
						
					if ($copy instanceof ProjectTask && config_option('use_time_in_task_dates') && $propName == "due_date"){
						$copy->setUseDueTime(1);
						
						$hour_min = getTimeValue(user_config_option('work_day_end_time'));
						$hour_min['hours'];
						$hour_min['mins'];
						
						$date->setHour($hour_min['hours']);
						$date->setMinute($hour_min['mins']);
						
						$date = $date->add('s', -logged_user()->getTimezone()*3600);										
					}
					if ($copy instanceof ProjectTask && config_option('use_time_in_task_dates') && $propName == "start_date"){
						$copy->setUseStartTime(1);
						
						$hour_min = getTimeValue(user_config_option('work_day_start_time'));
						$hour_min['hours'];
						$hour_min['mins'];
						
						$date->setHour($hour_min['hours']);
						$date->setMinute($hour_min['mins']);
						
						$date = $date->add('s', -logged_user()->getTimezone()*3600);						
					}
					
				}
	
				$dateUnit = substr($value, strlen($value) - 1); // d, w or m (for days, weeks or months)
				if($dateUnit == 'm') {
					$dateUnit = 'M'; // make month unit uppercase to call DateTimeValue::add with correct parameter
				}
				$dateNum = (int) substr($value, strpos($value,$operator), strlen($value) - 2);
				
				//Hook::fire('template_param_date_calculation', array('op' => $operator, 'date' => $date, 'template_id' => $object->getTemplateId(), 'original' => $object, 'copy' => $copy), $dateNum);
				
				$value = $date->add($dateUnit, $dateNum);
			}else{
				$value = DateTimeValueLib::dateFromFormatAndString(user_config_option('date_format'), $value);
			}
		}
		if($value != '') {
			if (!$copy->setColumnValue($propName, $value)){
				$copy->object->setColumnValue($propName, $value);
			}
			if ($propName == 'text' && $copy->getTypeContent() == 'text') {
				$copy->setText(html_to_text($copy->getText()));
			}
			$copy->save();
		}
		
	}
	
	// Ensure that assigned user is subscribed
	if ($copy instanceof ProjectTask && $copy->getAssignedTo() instanceof Contact) {
		$copy->subscribeUser($copy->getAssignedTo());
	}
	
	$ret = null;
	Hook::fire('after_template_object_param_instantiation', array('template_id' => $object->getTemplateId(), 'original' => $object, 'copy' => $copy, 'parameter_values' => $parameterValues), $ret);
}



/**
 * Copies related data from an object to another (members, linked_objects, custom_properties, subscribers, reminders and comments)
 * @param $object: Original object to copy data
 * @param $copy: Object to be modified with the data of the $orignal object
 * @param $options: set which type of data will not be copied
 */
function copy_additional_object_data($object, &$copy, $options=array()) {
	if (!$object instanceof ContentDataObject || !$copy instanceof ContentDataObject) {
		// if not valid objects return
		return;
	}

	$copy_members = !array_var($options, 'dont_copy_members');
	$copy_linked_objects = !array_var($options, 'dont_copy_linked_objects');
	$copy_custom_properties = !array_var($options, 'dont_copy_custom_properties');
	$copy_subscribers = !array_var($options, 'dont_copy_subscribers');
	$copy_reminders = !array_var($options, 'dont_copy_reminders');
	$copy_comments = !array_var($options, 'dont_copy_comments');

	$controller = new ObjectController();

	// copy members
	if ($copy_members) {
		$object_members = $object->getMembers();
		$copy->addToMembers($object_members);
		Hook::fire ('after_add_to_members', $copy, $object_members);
		$copy->addToSharingTable();
	}

	// copy linked objects
	if ($copy_linked_objects) {
		$copy->copyLinkedObjectsFrom($object);
	}

	// copy custom properties
	if ($copy_custom_properties) {
		// custom properties defined in "settings"
		$cp_object_type_id = $object->getObjectTypeId();
		if ($object instanceof TemplateTask || $object instanceof TemplateMilestone) {
			$cp_object_type_id = $copy->getObjectTypeId();
		}
		$custom_props = CustomProperties::getAllCustomPropertiesByObjectType($cp_object_type_id);
		foreach ($custom_props as $c_prop) {
			$values = CustomPropertyValues::getCustomPropertyValues($object->getId(), $c_prop->getId());
			if (is_array($values)) {
				foreach ($values as $val) {
					$cp = new CustomPropertyValue();
					$cp->setObjectId($copy->getId());
					$cp->setCustomPropertyId($val->getCustomPropertyId());
					$cp->setValue($val->getValue());
					$cp->save();
				}
			}
		}

		// object properties (key-value)
		$copy->copyCustomPropertiesFrom($object);
	}

	// copy subscribers
	if ($copy_subscribers) {
		$subscribers_array = array();
		foreach ($object->getSubscriberIds() as $user_id) {
			$subscribers_array["user_" . $user_id] = "1";
		}
		$controller->add_subscribers($copy, $subscribers_array);
	}

	// copy reminders
	if ($copy_reminders) {
		$reminders = ObjectReminders::getByObject($object);
		foreach ($reminders as $reminder) {
			$copy_reminder = new ObjectReminder();
			$copy_reminder->setContext($reminder->getContext());
			$reminder_date = $copy->getColumnValue($reminder->getContext());
			if ($reminder_date instanceof DateTimeValue) {
				$reminder_date = new DateTimeValue($reminder_date->getTimestamp());
				$reminder_date->add('m', -$reminder->getMinutesBefore());
			}
			$copy_reminder->setDate($reminder_date);
			$copy_reminder->setMinutesBefore($reminder->getMinutesBefore());
			$copy_reminder->setObject($copy);
			$copy_reminder->setType($reminder->getType());
			$copy_reminder->setUserId($reminder->getUserId());
			$copy_reminder->save();
		}
	}

	// copy comments
	if ($copy_comments) {
		foreach ($object->getAllComments() as $com) {
			$new_com = new Comment();
			$new_com->setAuthorEmail($com->getAuthorEmail());
			$new_com->setAuthorName($com->getAuthorName());
			$new_com->setAuthorHomepage($com->getAuthorHomepage());
			$new_com->setCreatedById($com->getCreatedById());
			$new_com->setCreatedOn($com->getCreatedOn());
			$new_com->setUpdatedById($com->getUpdatedById());
			$new_com->setUpdatedOn($com->getUpdatedOn());
			$new_com->setText($com->getText());
			$new_com->setRelObjectId($copy->getId());

			$new_com->save();
		}
	}

}



function get_time_info($timestamp) {
	$sign = $timestamp >= 0 ? 1 : -1;
	
	$timestamp = abs($timestamp);
	$days = floor($timestamp / (60*60*24));
	
	$ts_hours = $timestamp % (60*60*24);
	$hours = floor($ts_hours / (60*60));
	
	//$ts_mins = $ts_hours - ($hours*60*60);
	$ts_mins = $ts_hours % (60*60);
	$mins = floor($ts_mins / (60));
	
	return array('days' => $days, 'hours' => $hours, 'mins' => $mins, 'sign' => $sign);
}

//escapes a character from a string, escapes ' by default, or all characters according to $all
function escape_character($string, $char="'", $all = false) {
	if ($all){
		return addslashes($string);
	}else{
		return str_replace($char, "\\".$char, $string);
	}
}