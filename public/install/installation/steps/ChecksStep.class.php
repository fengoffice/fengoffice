<?php

/**
 * Checks step - check environment - PHP version, are folder writable etc
 *
 * @package ScriptInstaller
 * @subpackage installation
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ChecksStep extends ScriptInstallerStep {

	/**
	 * Array of files and folders that need to writable
	 *
	 * @var array
	 */
	private $check_is_writable = null;

	/**
	 * Array of extensions that need to be present for Feng Office to be installed
	 *
	 * @var array
	 */
	private $check_extensions = null;

	/**
	 * Construct the ChecksStep
	 *
	 * @access public
	 * @param void
	 * @return ChecksStep
	 */
	function __construct() {
		$this->setName('Environment check');

		$this->check_is_writable = array(
        '/config',
        '/cache',
        '/upload',
		'/tmp'
		); // array

		$this->check_extensions = array(
        'mysql', 'gd', 'simplexml'
        ); // array
	} // __construct

	/**
	 * Transform PHP notation like 8M, to bytes
	 *
	 * @param unknown_type $val
	 * @return unknown
	 */
	function return_bytes($val) {
		$val = trim($val);
		if($val && strlen($val)){
			$last = strtolower($val[strlen($val)-1]);
			switch($last) {
				// The 'G' modifier is available since PHP 5.1.0
				case 'g':
					$val *= 1024;
				case 'm':
					$val *= 1024;
				case 'k':
					$val *= 1024;
			}
		}
		return $val;
	}


	/**
	 * Execute environment checks
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function execute() {
		$all_ok = true;

		// Check PHP version
		if(version_compare(PHP_VERSION, '5.0.2', 'ge')) {
			$this->addToChecklist('PHP version is ' . PHP_VERSION, true);
		} else {
			$this->addToChecklist('You PHP version is ' . PHP_VERSION . '. PHP 5.0.2 or newer is required', false);
			$all_ok = false;
		} // if

		foreach($this->check_extensions as $extension_name) {
			if(extension_loaded($extension_name)) {
				$this->addToChecklist("'$extension_name' extension is loaded", true);
			} else {
				$this->addToChecklist("'$extension_name' extension is not loaded", false);
				$all_ok = false;
			} // if
		} // if

		if(is_array($this->check_is_writable)) {
			foreach($this->check_is_writable as $relative_folder_path) {
				$check_this = INSTALLATION_PATH . $relative_folder_path;

				$is_writable = false;
				if(is_file($check_this)) {
					$is_writable = file_is_writable($check_this);
				} elseif(is_dir($check_this)) {
					$is_writable = folder_is_writable($check_this);
				} else {
					 
				}

				if($is_writable) {
					$this->addToChecklist("$relative_folder_path is writable", true);
				} else {
					$this->addToChecklist("$relative_folder_path is not writable", false);
					$all_ok = false;
				} // if
			} // foreach
		} // if
		$memory_config_value = ini_get('memory_limit');
		if($memory_config_value && $memory_config_value!==0 && trim($memory_config_value) != ''){
			$memory_limit = $this->return_bytes($memory_config_value); // Memory allocated to PHP scripts
			if ($memory_limit > 0){
				$suggested_memory = 12582912;
				if ( $memory_limit < $suggested_memory ) {
					$this->addToChecklist("PHP Variable 'memory_limit' is $memory_limit which might not be enough for Feng Office. You should increase it to at least $suggested_memory in your php.ini.", false);
				}
			}
		}

		$this->setContentFromTemplate('checks.php');

		if(ini_get('zend.ze1_compatibility_mode')) {
			$this->addToChecklist('zend.ze1_compatibility_mode is set to On. This can cause some strange problems. It is strongly suggested to turn this value to Off (in your php.ini file)', false);
		} // if

		if($all_ok) {
			return $this->isSubmited();
		} // if

		$this->setNextDisabled(true);
		return false;
	} // execute

} // ChecksStep

?>