<?php

/**
 * 
 * Global cache wrapper, if any memory cache system , such as APC, is installed it tries to use it.
 * To add other library add its operations in the switch instructions of this class functions, and the order preference in the $cache_preference variable.
 * 
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 *
 */
class GlobalCache {
	
	private static $cache_preference = array('APCWrapper');
	
	static function isAvailable() {
		foreach (self::$cache_preference as $class) {
			switch ($class) {
				case 'APCWrapper': if (APCWrapper::isAvailable()) return true;
				default: return false;
			}
		}
	}
	
	static function add($key, $value, $ttl = null) {
		foreach (self::$cache_preference as $class) {
			switch ($class) {
				case 'APCWrapper': if (APCWrapper::isAvailable()) return APCWrapper::add($key, $value, $ttl);
				default: return false;
			}
		}
	}
	
	static function update($key, $value, $ttl = null) {
		foreach (self::$cache_preference as $class) {
			switch ($class) {
				case 'APCWrapper': if (APCWrapper::isAvailable()) return APCWrapper::update($key, $value, $ttl);
				default: return false;
			}
		}
	}
	
	static function get($key, &$success){
		foreach (self::$cache_preference as $class) {
			switch ($class) {
				case 'APCWrapper': if (APCWrapper::isAvailable()) return APCWrapper::get($key, $success);
				default: return false;
			}
		}
	}
	
	static function delete($key) {
		foreach (self::$cache_preference as $class) {
			switch ($class) {
				case 'APCWrapper': if (APCWrapper::isAvailable()) return APCWrapper::delete($key);
				default: return false;
			}
		}
	}
	
	static function key_exists($key) {
		foreach (self::$cache_preference as $class) {
			switch ($class) {
				case 'APCWrapper': if (APCWrapper::isAvailable()) return APCWrapper::key_exists($key);
				default: return false;
			}
		}
	}	
}