<?php
@error_reporting(E_ERROR | E_WARNING | E_PARSE);
if(session_id() == "") {
	@session_start();
}
// ---------------------------------------------------
//  Directories
// ---------------------------------------------------
define('ROOT', dirname(__FILE__));
define('PLUGIN_PATH',	   ROOT . '/plugins' ) ;
define('APPLICATION_PATH', ROOT . '/application');
define('LIBRARY_PATH',     ROOT . '/library');
define('CACHE_DIR',        ROOT . '/cache');
define('THEMES_DIR',       ROOT . '/public/assets/themes');

if(!defined('PUBLIC_FOLDER')) {
	define('PUBLIC_FOLDER', 'public'); // this file can be included through public/index.php
} // if

set_include_path(ROOT . PATH_SEPARATOR . APPLICATION_PATH . PATH_SEPARATOR . get_include_path());
set_include_path(LIBRARY_PATH . "/ezcomponents" . PATH_SEPARATOR . get_include_path());
set_include_path(LIBRARY_PATH . "/PEAR" . PATH_SEPARATOR . get_include_path());
set_include_path(LIBRARY_PATH . "/pdf" . PATH_SEPARATOR . get_include_path());


// ---------------------------------------------------
//  Fix some $_SERVER vars (taken from wordpress code)
// ---------------------------------------------------

// Fix for IIS, which doesn't set REQUEST_URI
if(!isset($_SERVER['REQUEST_URI']) || trim($_SERVER['REQUEST_URI']) == '') {
	$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME']; // Does this work under CGI?

	// Append the query string if it exists and isn't null
	if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
		$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
	} // if
} // if

// Fix for PHP as CGI hosts that set SCRIPT_FILENAME to something ending in php.cgi for all requests
if ( isset($_SERVER['SCRIPT_FILENAME']) && ( strpos($_SERVER['SCRIPT_FILENAME'], 'php.cgi') == strlen($_SERVER['SCRIPT_FILENAME']) - 7 ) ) {
	$_SERVER['SCRIPT_FILENAME'] = $_SERVER['PATH_TRANSLATED'];
} // if

// Fix for Dreamhost and other PHP as CGI hosts
if(strstr($_SERVER['SCRIPT_NAME'], 'php.cgi')) unset($_SERVER['PATH_INFO']);

if(trim($_SERVER['PHP_SELF']) == '') $_SERVER['PHP_SELF'] = preg_replace("/(\?.*)?$/",'', $_SERVER["REQUEST_URI"]);

// ---------------------------------------------------
//  Check if script is installed
// ---------------------------------------------------

// If script is not installed config.php will return false. Othervise it will
// return NULL. If we get false redirect to install folder
$config_is_set = @include_once(ROOT . '/config/config.php');
if(!is_bool($config_is_set) || !$config_is_set) {
	header("Location: public/install");
	print "Feng Office is not installed. Please redirect your browser to <a href=\"" . PUBLIC_FOLDER . "/install\">" . PUBLIC_FOLDER . "/install</a> folder and follow installation procedure";
	die();
} // if

// ---------------------------------------------------
//  config.php + extended config
// ---------------------------------------------------

if (!defined('FILES_DIR')) define('FILES_DIR', ROOT . '/upload'); // place where we will upload project files
define('PRODUCT_NAME', 'Feng Office');
define('PRODUCT_URL', 'http://www.fengoffice.com');
define('PRODUCT_LOGO_FILENAME', 'feng_logo.png');
define('DEFAULT_HELP_LINK', 'http://fengoffice.com/web/wiki');

define('MAX_SEARCHABLE_FILE_SIZE', 1048576); // if file type is searchable script will load its content into search index. Using this constant you can set the max filesize of the file that will be imported. Noone wants 500MB in search index for single file
define('SESSION_LIFETIME', 7200);
define('REMEMBER_LOGIN_LIFETIME', 1209600); // two weeks

// Defaults
define('DEFAULT_CONTROLLER', 'access');
define('DEFAULT_ACTION', 'index');
define('DEFAULT_THEME', 'default');

define('SLIMEY_PATH', ROOT_URL . '/public/assets/javascript/slimey/');
define('PLUGINS_URL', ROOT_URL . '/plugins' ) ;

if (!defined('PHP_PATH')) {
	define('PHP_PATH', 'php');
}

// ---------------------------------------------------
//  Init...
// ---------------------------------------------------

include_once 'environment/environment.php';

if (Env::isDebuggingTime()) {
	TimeIt::start("Total");
}

include_once 'library/json/json.php';

// Lets prepare everything for autoloader
require APPLICATION_PATH . '/functions.php'; // __autoload() function is defined here...

if (!$callbacks = spl_autoload_functions()) $callbacks = array();
foreach ($callbacks as $callback) {
	spl_autoload_unregister($callback);
}
spl_autoload_register('feng__autoload');
foreach ($callbacks as $callback) {
	spl_autoload_register($callback);
}


@include CACHE_DIR . '/autoloader.php';

// Prepare logger... We might need it early...
//if(Env::isDebugging()) {
	Logger::setSession(new Logger_Session('default'));
	Logger::setBackend(new Logger_Backend_File(CACHE_DIR . '/log.php'));
	 
	set_error_handler('__production_error_handler');
	set_exception_handler('__production_exception_handler');
/*} else {
	Logger::setSession(new Logger_Session('default'));
	Logger::setBackend(new Logger_Backend_Null());
} // if*/

register_shutdown_function('__shutdown');

// Connect to database...
try {
	DB::connect(DB_ADAPTER, array(
      'host'    => DB_HOST,
      'user'    => DB_USER,
      'pass'    => DB_PASS,
      'name'    => DB_NAME,
      'persist' => DB_PERSIST
	)); // connect
	if(defined('DB_CHARSET') && trim(DB_CHARSET)) {
		DB::execute("SET NAMES ?", DB_CHARSET);
	} // if
} catch(Exception $e) {
	if(Env::isDebugging()) {
		Env::dumpError($e);
	} else {
		Logger::log($e, Logger::FATAL);
		Env::executeAction('error', 'db_connect');
	} // if
} // try

// Init application
if(Env::isDebugging()) {
	benchmark_timer_set_marker('Init application');
} // if

// We need to call application.php after the routing is executed because
// some of the application classes may need CONTROLLER, ACTION or $_GET
// data collected by the matched route
require_once APPLICATION_PATH . '/application.php';
if (!defined('DONT_USE_FENG_UTF8') || !DONT_USE_FENG_UTF8) {
	require_once LIBRARY_PATH . '/utf8/utf8.php';
}

// Set handle request timer...
if(Env::isDebugging()) {
	benchmark_timer_set_marker('Handle request');
} // if

// Remove injection from url parameters
foreach($_GET as $k => &$v) {
	$v = remove_css_and_scripts($v);
}

// Get controller and action and execute...
try {
	if (!defined( 'CONSOLE_MODE' )) {
		Env::executeAction(request_controller(), request_action()) ;
	}
} catch(Exception $e) {
	if(Env::isDebugging()) {
		Logger::log($e, Logger::FATAL);
		Env::dumpError($e);
	} else {
		Logger::log($e, Logger::FATAL);
		redirect_to(get_url('error', 'execute_action'));
	} // if
} // try

if (Env::isDebuggingTime()) {
	TimeIt::stop();
	if (array_var($_REQUEST, 'a') != 'popup_reminders') {
		Env::useHelper('format');
		$report = TimeIt::getTimeReportByType();
		$report .= "\nMemory Usage: " . format_filesize(memory_get_usage(true));
		file_put_contents('cache/log.time', "Request: ".print_r($_REQUEST,1)."\nTime Report:\n------------\n$report\n--------------------------------------\n", FILE_APPEND);
	}
}

?>