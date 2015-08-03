<?php

/**
 * Initialize environment: load required files, set environment options etc.
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */

// Environment path is used by many environment classes. If not
// defined do it now
if(!defined('ENVIRONMENT_PATH')) define('ENVIRONMENT_PATH', dirname(__FILE__));

//  include_once ENVIRONMENT_PATH . '/classes/Session.class.php'; // required to use manual session handling

if(!ini_get('session.auto_start') || (strtolower(ini_get('session.auto_start')) == 'off')) {
	 
	if ( !isset($_GET['avoid_session']) || (isset($_GET['avoid_session']) && !$_GET['avoid_session']) ){
		session_start(); // Start the session
	}
}

include_once ENVIRONMENT_PATH . '/classes/Env.class.php';
include_once ENVIRONMENT_PATH . '/constants.php';
include_once ENVIRONMENT_PATH . '/functions/utf.php';
include_once ENVIRONMENT_PATH . '/functions/general.php';
include_once ENVIRONMENT_PATH . '/functions/files.php';

// Configure PHP
if (ini_get('memory_limit') > 0 && php_config_value_to_bytes(ini_get('memory_limit')) < 64*1024*1024) {
	ini_set('memory_limit', '64M');
}
ini_set('short_open_tag', 'on');
ini_set('date.timezone', 'GMT');
if(function_exists('date_default_timezone_set')) {
	date_default_timezone_set('GMT');
} else {
	putenv('TZ=GMT');
} // if

if(defined('DEBUG') && DEBUG) {
	//set_time_limit(120);
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
} else {
	ini_set('display_errors', 0);
} // if


// Remove slashes is magic quotes gpc is on from $_GET, $_POST and $_COOKIE
fix_input_quotes();

// Debug
if(Env::isDebugging()) {
	include_once ENVIRONMENT_PATH . '/classes/debug/BenchmarkTimer.class.php';
	benchmark_timer_start();
	benchmark_timer_set_marker('Init environment');
} // if

// Include autoloader...
include ENVIRONMENT_PATH . '/classes/AutoLoader.class.php';
include ENVIRONMENT_PATH . '/classes/event/event.php';
include ENVIRONMENT_PATH . '/classes/hook/Hook.class.php';
include ENVIRONMENT_PATH . '/classes/ajax/ajax.php';
include ENVIRONMENT_PATH . '/classes/template/template.php';
include ENVIRONMENT_PATH . '/classes/flash/flash.php';
include ENVIRONMENT_PATH . '/classes/help/help.php';
include ENVIRONMENT_PATH . '/classes/localization/localization.php';

include ENVIRONMENT_PATH . '/classes/logger/Logger_Entry.class.php';
include ENVIRONMENT_PATH . '/classes/logger/Logger_Session.class.php';
include ENVIRONMENT_PATH . '/classes/logger/Logger_Backend.class.php';
include ENVIRONMENT_PATH . '/classes/logger/Logger.class.php';
include ENVIRONMENT_PATH . '/classes/logger/backend/Logger_Backend_File.class.php';
include ENVIRONMENT_PATH . '/classes/logger/backend/Logger_Backend_Null.class.php';

include ENVIRONMENT_PATH . '/classes/timeit/TimeIt.class.php';

// Init libraries
Env::useLibrary('database');

?>