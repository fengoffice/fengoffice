<?php

// PHP5?
if(!version_compare(phpversion(), '5.0', '>=')) {
	die('<strong>Installation error:</strong> in order to run Feng Office you need PHP5. Your current PHP version is: ' . phpversion());
} // if

$compatibility = strtolower(ini_get('zend.ze1_compatibility_mode'));
if($compatibility == 'on' || $compatibility == '1') {
	die('<strong>Installation error:</strong> Feng Office will not run on PHP installations that have <strong>zend.ze1_compatibility_mode</strong> set to On. <strong>Please turn it off</strong> (in your php.ini file) in order to continue.');
} // if

if(!isset($_SESSION)) session_start();
error_reporting(E_ALL);

if(function_exists('date_default_timezone_set')) {
	date_default_timezone_set('GMT');
} // if

define('INSTALLER_PATH', dirname(__FILE__));
define('INSTALLATION_PATH', realpath(INSTALLER_PATH . '/../../'));

// Check the config
$config_path = INSTALLATION_PATH . '/config/config.php';
$config_is_set = false;
if (is_file($config_path)) {
	$config_is_set = @include $config_path;
}
if(is_bool($config_is_set) && $config_is_set) {
	header("Location: ../../index.php");
	die('<strong>Installation error:</strong> Feng Office is already installed');
} else {
	$f = @fopen($config_path, "w");
	@fwrite($f, "<?php return false ?>");
	@fclose($f);
}

// Include library
require_once INSTALLATION_PATH . '/environment/functions/general.php';
require_once INSTALLATION_PATH . '/environment/functions/files.php';
require_once INSTALLATION_PATH . '/environment/functions/utf.php';

require_once INSTALLER_PATH . '/library/constants.php';
require_once INSTALLER_PATH . '/library/functions.php';
require_once INSTALLER_PATH . '/library/classes/ScriptInstaller.class.php';
require_once INSTALLER_PATH . '/library/classes/ScriptInstallerStep.class.php';
require_once INSTALLER_PATH . '/library/classes/ChecklistItem.class.php';
require_once INSTALLER_PATH . '/library/classes/Output.class.php';
require_once INSTALLER_PATH . '/library/classes/Output_Html.class.php';
require_once INSTALLER_PATH . '/library/classes/Output_Console.class.php';
require_once INSTALLER_PATH . '/installation/acInstallation.class.php';

require_once INSTALLER_PATH . '/library/classes/Template.class.php';

?>