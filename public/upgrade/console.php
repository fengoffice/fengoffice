<?php

define('ROOT', dirname(__FILE__) . '/../..');
define('PRODUCT_NAME', 'Feng Office');
define('PRODUCT_URL', 'http://www.fengoffice.com');

require_once dirname(__FILE__) . '/include.php';

if(!isset($argv) || !is_array($argv)) {
	die('There is no input arguments');
} // if

if (count($argv) == 1) {
	if (!file_exists(ROOT."/config/installed_version.php")) {
		die('File does not exists: config/installed_version.php');
	}
	$from_version = include ROOT."/config/installed_version.php";
	$to_version = include ROOT."/version.php";

} else { // version number received in parameters
	$from_version = array_var($argv, 1);
	$to_version = array_var($argv, 2);
	
	if(trim($from_version) == '') {
		die('First argument (current version) is required');
	} // if
	
	if(trim($to_version) == '') {
		die('Second argument (to version) is required');
	} // if
}

// Construct the upgrader and load the scripts
$upgrader = new ScriptUpgrader(new Output_Console(), 'Upgrade Feng Office', 'Upgrade your Feng Office installation');
$upgrader->upgrade($from_version, $to_version);

echo date("Y-m-d H:i:s") . " - Updating plugins...\n";
if (substr(php_uname(), 0, 5) == "Linux") {
	$command = "php ".ROOT."/public/install/plugin-console.php update_all";
	exec("$command");
}
echo date("Y-m-d H:i:s") . " - Finished plugins update.\n";
?>