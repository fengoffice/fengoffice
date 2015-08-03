<?php
$argv or die("Are you using console ? \n");
$usage = "USAGE: plugin-console.php COMMAND [list, install, activate, install_activate, deactivate, update, update_all] PLUGIN_NAME \n" ;
chdir(dirname(__FILE__) . '/../..');
define("CONSOLE_MODE", true);
define("PLUGIN_MANAGER_CONSOLE", true );
if(!defined('PUBLIC_FOLDER')) define('PUBLIC_FOLDER', 'public');
require_once 'init.php';

$success_message = "";

try {
	
	
	if(!isset($argv) || !is_array($argv)) {
		die("There is no input arguments\n");
	} // if
	
	$command = array_var($argv, 1);
	$arg1 = array_var($argv, 2);
	$usr = Contacts::findOne(array("conditions" => "user_type > 0", "order" => "user_type"));
	$usr or die("No users found\n");
	CompanyWebsite::instance()->logUserIn($usr);
	
	$ctrl = new PluginController();
	trim($command) or die("Command is required \n".$usage);
	
	$plugins = $ctrl->index();
	
	if ($command == 'list') {
		foreach ($plugins as $plg){
			/* @var $plg Plugin */
			echo "---------------------------------------------\n";
			echo "NAME: \t\t".$plg->getSystemName() ."\n" ;
			echo "VERSION: \t".$plg->getVersion() ."\n"  ;
			echo "STATUS: \t".( ($plg->isInstalled())?'Installed ':'Uninstalled ' ).( ($plg->isActive())?'Activated ':'Inactive ' ) ."\n";
	
			if ( $plg->updateAvailable() ) {
				echo "*** There is a new version of this plugin *** \n";
			}
		}
	} else if ($command == 'update_all') {
		$ctrl->updateAll();
	} else {
		$arg1 or die("Plugin is required \n$usage");
		$plg = Plugins::instance()->findOne(array("conditions"=>" name = '$arg1'"));
		$plg or die("ERROR: plugin $arg1 not found\n");
		
		switch($command) {
			case 'update':
				$ctrl->update($plg->getId());
				$success_message = "Plugin ".$plg->getName()." successfully updated.";
				break;
			case 'install':
				$ctrl->install($plg->getId());
				$success_message = "Plugin ".$plg->getName()." successfully installed.";
				break;
			case 'activate':
				$plg->activate();
				$success_message = "Plugin ".$plg->getName()." successfully activated.";
				break;
			case 'install_activate':
				$ctrl->install($plg->getId());
				$plg->activate();
				$success_message = "Plugin ".$plg->getName()." successfully installed and activated.";
				break;
			case 'deactivate':
				$plg->deactivate();
				$success_message = "Plugin ".$plg->getName()." successfully deactivated.";
				break;
			case 'uninstall':
				$ctrl->uninstall($plg->getId());
				$success_message = "Plugin ".$plg->getName()." successfully uninstalled.";
				break;
			default:
				die("Invalid command \n$usage");
				break;	
		}
		
	}

} catch (Exception $e) {
	echo $e->getMessage();
	echo "\n".$e->getTraceAsString()."\n";
}

echo "$success_message\n";