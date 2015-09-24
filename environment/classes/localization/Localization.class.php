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
	 * strftime() function format used for presenting date and time
	 *
	 * @var string
	 */
	private $datetime_format = '';

	/**
	 * strftime() function format used for presenting date
	 *
	 * @var string
	 */
	private $date_format = '';

	/**
	 * Descriptive date format is string used in date() function that will autput date
	 * in such a way that it tells as much as it can: with day it is and when it is.
	 * This one is used for such things as milestones and tasks where you need to see
	 * as much info about due date as you can from a simple, short string
	 *
	 * @var string
	 */
	private $descriptive_date_format = '';

	/**
	 * strftime() function format used for presenting time
	 *
	 * @var string
	 */
	private $time_format = 'H:i';

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
		return $this->langs->get($name, $default);
	} // lang
	
	/**
	 * Returns true if key exists in langs array
	 *
	 * @param string $name
	 * @return boolean
	 */
	function lang_exists($name) {
		if (is_null($name)) return false;
		else return $this->langs->has($name);
	}

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
	 * does not exists in lanuage dir
	 */
	private function loadLanguageSettings() {
		// Check dir...
		if (! is_dir ( $this->getLanguageDirPath () )) {
			throw new DirDnxError ( $this->getLanguageDirPath () );
		} // if
		

		// Get settings file path and include it
		$settings_file = $this->getLanguageDirPath () . '/' . $this->getLocale () . '.php';
		if (is_file ( $settings_file )) {
			include $settings_file;
		} else {
			$base_settings = $this->getLanguageDirPath () . '/default.php';
			if (is_file ( $base_settings )) {
				include $base_settings;
			} else {
				throw new FileDnxError ( $settings_file, "Failed to find language settings file. Expected location: '$settings_file'." );
			}
		}
		
		// Clear langs
		$this->langs->clear ();
		
		// Get langs dir - ONLY PHP langs !
		$langs_dir = $this->getLanguageDirPath () . '/' . $this->getLocale ();
		if (is_dir ( $langs_dir )) {
			$files = get_files ( $langs_dir, 'php' );
			
			// Loop through files and add langs
			if (is_array ( $files )) {
				sort ( $files );
				foreach ( $files as $file ) {
					$langs = include $file;
					if (is_array ( $langs ))
						$this->langs->append ( $langs );
				}
			}
			
			// Plugins - Only PHP langs - include all installed plugins, no matter if they they have not been activated
			$plugins = Plugins::instance()->getAll();
			foreach ( $plugins as $plugin ) {
				/* @var $plugin Plugin */
				$plg_dir = $plugin->getLanguagePath () . "/" . $this->getLocale ();
				if (is_dir ( $plg_dir )) {
					$files = get_files ( $plg_dir, 'php' );
					// Loop through files and add langs
					if (is_array ( $files )) {
						sort ( $files );
						foreach ( $files as $file ) {
							$langs = include $file;
							
							if (is_array ( $langs ))
								$this->langs->append ( $langs );
						}
					}
				}
			}
		
		} else {
			throw new DirDnxError ( $langs_dir );
		} // if
		

		// Done!
		return true;
	
	} // loadLanguageSettings

	/**
	 * Return formated date
	 *
	 * @access public
	 * @param DateTimeValue $date
	 * @param float $timezone Timezone offset in hours
	 * @return string
	 */
	function formatDate(DateTimeValue $date, $timezone = 0) {
		if ($this->date_format == '') $this->date_format = user_config_option('date_format');
		return $this->dateByLocalization($this->date_format, $date->getTimestamp(), $timezone);
	} // formatDate

	/**
	 * * Descriptive date format is string used in date() function that will autput date
	 * in such a way that it tells as much as it can: with day it is and when it is.
	 * This one is used for such things as milestones and tasks where you need to see
	 * as much info about due date as you can from a simple, short string
	 *
	 * @param DateTimeValue $date
	 * @param float $timezone Timezone offset in hours
	 * @return string
	 */
	function formatDescriptiveDate(DateTimeValue $date, $timezone = 0) {
		if ($this->descriptive_date_format == '') $this->descriptive_date_format = user_config_option('descriptive_date_format');
		return $this->dateByLocalization($this->descriptive_date_format, $date->getTimestamp(), $timezone);
	} // formatDescriptiveDate

	/**
	 * Return formated datetime
	 *
	 * @access public
	 * @param DateTimeValue $date
	 * @param float $timezone Timezone offset in hours
	 * @return string
	 */
	function formatDateTime(DateTimeValue $date, $timezone = 0) {
		if ($this->datetime_format == '') $this->datetime_format = user_config_option('date_format') . " " . $this->time_format;
		return $this->dateByLocalization($this->datetime_format, $date->getTimestamp(), $timezone);
	} // formatDateTime

	/**
	 * Return fromated time
	 *
	 * @access public
	 * @param DateTimeValue $date
	 * @param float $timezone Timezone offset in hours
	 * @return string
	 */
	function formatTime(DateTimeValue $date, $timezone = 0, $view_timezone = false) {
		return $this->dateByLocalization($this->time_format, $date->getTimestamp(), $timezone, $view_timezone);
	} // formatTime

	/**
	 * Returns datetime as a formatted string, depending on localization.
	 * Month and Day descriptions are taken from language definitions.
	 *
	 * @param string $format Same format as function date(...)
	 * @param int $timestamp
	 * @return string
	 */
	function dateByLocalization($format, $timestamp, $timezone = 0, $view_timezone = false) {
		if ($timestamp == 0) { 
			$timestamp = time();
		}
		$timestamp += ($timezone * 3600);
		
		$names['l'] = array(-1 => 'w', 0 => lang('sunday'), 1 => lang('monday'), 2 => lang('tuesday'), 3 => lang('wednesday'), 4 => lang('thursday'), 5 => lang('friday'), 6 => lang('saturday'), 7 => lang('sunday'));
		$names['D'] = array(-1 => 'w', 0 => lang('sunday short'), 1 => lang('monday short'), 2 => lang('tuesday short'), 3 => lang('wednesday short'), 4 => lang('thursday short'), 5 => lang('friday short'), 6 => lang('saturday short'), 7 => lang('sunday short'));
		$names['F'] = array(-1 => 'n', 1 => lang('month 1'), 2 => lang('month 2'), 3 => lang('month 3'), 4 => lang('month 4'), 5 => lang('month 5'), 6 => lang('month 6'), 7 => lang('month 7'), 8 => lang('month 8'), 9 => lang('month 9'), 10 => lang('month 10'), 11 => lang('month 11'), 12 => lang('month 12') );
		$names['M'] = array(-1 => 'n');
		for ($i = 1; $i <= 12; $i++) {
			$names['M'][$i] = substr_utf($names['F'][$i], 0, 3);
		}
		
		$str_date = '';
		$i = 0;
		while ( (strpos($format, 'l', $i) !== FALSE) || (strpos($format, 'D', $i) !== FALSE) ||
				(strpos($format, 'F', $i) !== FALSE) || (strpos($format, 'M', $i) !== FALSE) ) {
			
			$ch['l'] = strpos($format, 'l', $i);
			$ch['D'] = strpos($format, 'D', $i);
			$ch['F'] = strpos($format, 'F', $i);
			$ch['M'] = strpos($format, 'M', $i);
			
			foreach ($ch as $k => $v) {
				if ($v === FALSE)
				unset($ch[$k]);
			}
			
			$a = min($ch);
			$str_date .= date(substr($format, $i, $a-$i), $timestamp) . $names[$format[$a]][date($names[$format[$a]][-1], $timestamp)];
			$i = $a + 1;
		}
		$str_date .= date(substr($format, $i), $timestamp);
                if($view_timezone){
                    $str_date .= " (GMT " . $timezone . ")";
                }
		return $str_date;
	}

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
	 * Get datetime format
	 *
	 * @access public
	 * @param null
	 * @return string
	 */
	function getDateTimeFormat() {
		return $this->datetime_format;
	} // getDateTimeFormat

	/**
	 * Set datetime foramt value
	 *
	 * @access public
	 * @param string $value
	 * @return null
	 */
	function setDateTimeFormat($value) {
		$this->datetime_format = (string) $value;
	} // setDateTimeFormat

	/**
	 * Get date format
	 *
	 * @access public
	 * @param null
	 * @return string
	 */
	function getDateFormat() {
		return $this->date_format;
	} // getDateFormat

	/**
	 * Set date format value
	 *
	 * @access public
	 * @param string $value
	 * @return null
	 */
	function setDateFormat($value) {
		$this->date_format = (string) $value;
	} // setDateFormat

	/**
	 * Get time format
	 *
	 * @access public
	 * @param null
	 * @return string
	 */
	function getTimeFormat() {
		return $this->time_format;
	} // getTimeFormat

	/**
	 * Set time format value
	 *
	 * @access public
	 * @param string $value
	 * @return null
	 */
	function setTimeFormat($value) {
		$this->time_format = (string) $value;
	} // setTimeFormat

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