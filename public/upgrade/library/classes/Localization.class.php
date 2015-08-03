<?php
/**
 * Localization class
 *
 * This class will set up PHP environment to mach locale settings (using
 * setlocale() function) and import apropriate set of words from language
 * folder. Properties of this class are used by some other system classes
 * for outputing data in correct format (for instance DateTimeValueLib).
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class Localization {

	/**
	 * Path to directory where language settings are
	 *
	 * @var string
	 */
	private $language_dir_path = null;

	/**
	 * Locale code
	 *
	 * @var string
	 */
	private $locale;

	/**
	 * Current locale settings, returned by setlocale() function
	 *
	 * @var string
	 */
	private $current_locale;

	/**
	 * Container of langs
	 *
	 * @var Container
	 */
	private $langs;

	/**
	 * Construct the Localization
	 *
	 * @access public
	 * @param string $language_dir_path Path to the language dir
	 * @param string $local
	 * @return Localization
	 */
	function __construct() {
		$this->langs = new Container();
	} // __construct

	/**
	 * Return lang by name
	 *
	 * @param string $name
	 * @param mixed $default Default value that will be returned if lang is not found
	 * @return string
	 */
	function lang($name, $default = null) {
		if(is_null($default)) $default = "Missing lang: $name";
		return $this->langs->get($name, $default);
	} // lang

	/**
	 * Load language settings
	 *
	 * @access public
	 * @param string $locale Locale code
	 * @param string $language_dir Path to directory where we have all
	 *   languages defined
	 * @return null
	 * @throws DirDnxError If language dir does not exists
	 * @throws FileDnxError If language settings file for this local settings
	 *   does not exists in lanuage dir
	 */
	function loadSettings($locale, $languages_dir) {
		$this->setLocale($locale);
		$this->setLanguageDirPath($languages_dir);

		return $this->loadLanguageSettings();

	} // loadSettings

	/**
	 * Load language settings
	 *
	 * @access public
	 * @param void
	 * @throws DirDnxError If language dir does not exists
	 * @throws FileDnxError If language settings file for this local settings
	 *   does not exists in lanuage dir
	 */
	private function loadLanguageSettings() {

		// Check dir...
		if(!is_dir($this->getLanguageDirPath())) {
			throw new DirDnxError($this->getLanguageDirPath());
		} // if

		// Get settings file path and include it
		$settings_file = $this->getLanguageDirPath() . '/' . $this->getLocale() . '.php';
		if(is_file($settings_file)) {
			include $settings_file;
		} else {
			throw new FileDnxError($settings_file, "Failed to find language settings file. Expected location: '$settings_file'.");
		} // if

		// Clear langs
		$this->langs->clear();

		// Get langs dir
		$langs_dir = $this->getLanguageDirPath() . '/' . $this->getLocale();
		if(is_dir($langs_dir)) {
			$files = get_files($langs_dir, 'php');

			// Loop through files and add langs
			if(is_array($files)) {
				sort($files);
				foreach($files as $file) {
					$langs = include $file;
					if(is_array($langs)) $this->langs->append($langs);
				} // foreach
			} // if
			
			//Load plugin langs after fengoffice default langs
			$plugins_dir = $langs_dir . '/plugins';
			if(is_dir($plugins_dir)) {
				sort($files);
				$files = get_files($plugins_dir, 'php');
	
				// Loop through files and add langs
				if(is_array($files)) {
					foreach($files as $file) {
						$langs = include $file;
						if(is_array($langs)) $this->langs->append($langs);
					} // foreach
				} // if
			} // if
		} else {
			throw new DirDnxError($langs_dir);
		} // if

		// Done!
		return true;

	} // loadLanguageSettings

	// -------------------------------------------------------------
	//  Getters and setters
	// -------------------------------------------------------------

	/**
	 * Get language_dir_path
	 *
	 * @access public
	 * @param null
	 * @return string
	 */
	function getLanguageDirPath() {
		return $this->language_dir_path;
	} // getLanguageDirPath

	/**
	 * Set language_dir_path value
	 *
	 * @access public
	 * @param string $value
	 * @return null
	 */
	function setLanguageDirPath($value) {
		$this->language_dir_path = $value;
	} // setLanguageDirPath

	/**
	 * Get locale
	 *
	 * @access public
	 * @param null
	 * @return string
	 */
	function getLocale() {
		return $this->locale;
	} // getLocale

	/**
	 * Set locale value
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setLocale($value) {
		$this->locale = $value;
	} // setLocale

	/**
	 * Return current locale settings
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	//function getCurrentLocale() {
	//  if(trim($this->current_locale)) {
	//    return $this->current_locale;
	//  } else {
	//    return setlocale(LC_ALL, 0);
	//  } // if
	//} // getCurrentLocale

	/**
	 * Interface to langs container
	 *
	 * @access public
	 * @param void
	 * @return Container
	 */
	function langs() {
		return $this->langs;
	} // langs

	/**
	 * Return localization instance
	 *
	 * @access public
	 * @param string $locale Localization code
	 * @return Localization
	 */
	static function instance() {
		static $instance;

		// Prepare instance
		if(!($instance instanceof Localization)) {
			$instance = new Localization();
		} // if

		// Done...
		return $instance;

	} // instance

} // Localization
?>