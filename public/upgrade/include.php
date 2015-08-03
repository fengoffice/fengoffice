<?php

  // PHP5?
  if(!version_compare(phpversion(), '5.0', '>=')) {
    die('<strong>Upgrade error:</strong> in order to run Feng Office you need PHP5. Your current PHP version is: ' . phpversion());
  } // if

  session_start();
  error_reporting(E_ALL);
  
  if(function_exists('date_default_timezone_set')) {
    date_default_timezone_set('GMT');
  } // if
  
  define('UPGRADER_PATH', dirname(__FILE__)); // upgrader is here
  define('INSTALLATION_PATH', realpath(UPGRADER_PATH . '/../../')); // Feng Office installation that we need to upgrade is here
  
  require_once UPGRADER_PATH . '/library/functions.php';
  require_once UPGRADER_PATH . '/library/classes/ScriptUpgrader.class.php';
  require_once UPGRADER_PATH . '/library/classes/ScriptUpgraderScript.class.php';
  require_once UPGRADER_PATH . '/library/classes/ChecklistItem.class.php';
  require_once UPGRADER_PATH . '/library/classes/Output.class.php';
  require_once UPGRADER_PATH . '/library/classes/Output_Console.class.php';
  require_once UPGRADER_PATH . '/library/classes/Output_Html.class.php';
  require_once UPGRADER_PATH . '/library/classes/Localization.class.php';
  
  require_once UPGRADER_PATH . '/library/classes/Template.class.php';
  
  require_once INSTALLATION_PATH . '/config/config.php';
  require_once INSTALLATION_PATH . '/environment/functions/general.php';
  require_once INSTALLATION_PATH . '/environment/functions/files.php';
  require_once INSTALLATION_PATH . '/environment/functions/utf.php';
  require_once INSTALLATION_PATH . '/environment/classes/Error.class.php';
  require_once INSTALLATION_PATH . '/environment/classes/errors/filesystem/FileDnxError.class.php';
  require_once INSTALLATION_PATH . '/environment/classes/errors/filesystem/DirDnxError.class.php';
  require_once INSTALLATION_PATH . '/environment/classes/container/IContainer.class.php';
  require_once INSTALLATION_PATH . '/environment/classes/container/Container.class.php';
  
  if (!defined('DEFAULT_LOCALIZATION')) define('DEFAULT_LOCALIZATION', 'en_us');
  Localization::instance()->loadSettings(DEFAULT_LOCALIZATION, INSTALLATION_PATH . '/language');
  // Set exception handler
  set_exception_handler('dump_upgrader_exception');

?>