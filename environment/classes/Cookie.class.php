<?php

/**
 * Simple interface for setting and getting cookie values
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class Cookie {

	static function getPrefix() {
		if (defined('USE_COOKIE_PREFIX')) {
			if (USE_COOKIE_PREFIX == 1) {
				return preg_replace('/[^a-zA-Z0-9]/', '_', ROOT_URL);
			} else if (USE_COOKIE_PREFIX) {
				return USE_COOKIE_PREFIX;
			} else {
				return '';
			}
		}
	}
	
	/**
	 * Return value from the cookien
	 *
	 * @param string $name Variable name
	 * @param mixed $default
	 * @return mixed
	 */
	static function getValue($name, $default = null) {
		$name = Cookie::getPrefix() . $name;
		return array_var($_COOKIE, $name, $default);
	} // getValue

	/**
	 * Set cookie value
	 *
	 * @param string $name Variable name
	 * @param mixed $value
	 * @param integer $expiration Number of seconds from current time when this cookie need to expire
	 * @return null
	 */
	static function setValue($name, $value, $expiration = null, $domainF = null) {
		$expiration_time = DateTimeValueLib::now();
		if((integer) $expiration > 0) {
			$expiration_time->advance($expiration);
		} else {
			$expiration_time->advance(3600); // one hour
		} // if

		$path = defined('COOKIE_PATH') ? COOKIE_PATH : '/';
		$domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';
		$secure = defined('COOKIE_SECURE') ? COOKIE_SECURE : false;
		
		if(!is_null($domainF)){
			$domain = $domainF;
		}
		$name = Cookie::getPrefix() . $name;
		setcookie($name, $value, $expiration_time->getTimestamp(), $path, $domain, $secure);
	} // setValue

	/**
	 * Unset specific cookie value
	 *
	 * @param string $name
	 * @return null
	 */
	static function unsetValue($name) {
		self::setValue($name, false);
	} // unsetValue

} // Cookie

?>