<?php

class LocalizationConfigHandler extends ConfigHandler {

	/**
	 * Array of available locales (subfolders of languages folder)
	 *
	 * @var array
	 */
	private $available_locales = array();

	/**
	 * Constructor
	 *
	 * @param void
	 * @return LocalizationConfigHandler
	 */
	function __construct() {
		$language_dir = with_slash(ROOT . "/language");

		if (is_dir($language_dir)) {
			$d = dir($language_dir);
			while (($entry = $d->read()) !== false) {
				if (str_starts_with($entry, '.') || $entry == "CVS") {
					continue;
				} // if

				if (is_dir($language_dir . $entry)) {
					$this->available_locales[] = $entry;
				} // if
			} // while
			$d->close();
			sort($this->available_locales);
		} // if
	} // __construct

	function getValue() {
		$value = $this->rawToPhp($this->getRawValue());
		if ($value == "") {
			return DEFAULT_LOCALIZATION;
		} else {
			return $value;
		}
	}
	
	/**
	 * Render form control
	 *
	 * @param string $control_name
	 * @return string
	 */
	function render($control_name, $default = null) {
		$options = array();
		if ($default){
			$options[] = option_tag($default['text'], $default['value'], array('selected' => true));
		}
		foreach($this->available_locales as $locale) {
			$option_attributes = $this->getValue() == $locale && !$default ? array('selected' => true) : null;
			$options[] = option_tag(get_language_name($locale), $locale, $option_attributes);
		} // foreach

		return select_box($control_name, $options);
	} // render

} // LocalizationConfigHandler

?>