<?php

  /**
  * First step in Feng Office installation process - Welcome message
  *
  * @package ScriptInstaller
  * @subpackage installation
  * @author Ilija Studen <ilija.studen@gmail.com>
  * @author FengOffice team <development@fengoffice.com>
  */
  class WelcomeStep extends ScriptInstallerStep {
  
    /**
    * Construct the Welcome Step
    *
    * @access public
    * @param void
    * @return WelcomeStep
    */
    function __construct() {
      $this->setName('Welcome');
    } // __construct
    
    /**
    * Show welcome message
    *
    * @access public
    * @param void
    * @return boolean
    */
    function execute() {
      $this->setContentFromTemplate('welcome.php');
      return array_var($_POST, 'submited') == 'submited';
    } // execute
  
  } // WelcomeStep

?>