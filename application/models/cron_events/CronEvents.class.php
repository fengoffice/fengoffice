<?php

/**
 * CronEvents
 *
 * @author Ignacio de Soto <ignacio.desoto@fengoffice.com>
 */
class CronEvents extends BaseCronEvents {

	function getDueEvents($date = null) {
		if (defined('REMOVE_AUTOLOADER_BEFORE_CRON') && REMOVE_AUTOLOADER_BEFORE_CRON) {
			@unlink(CACHE_DIR . "/autoloader.php");
		}
		if (!$date instanceof DateTimeValue) $date = DateTimeValueLib::now();
		$events = self::findAll(array(
			'conditions' => array(
				'`date` <= ?',
				$date
			)
		));
		if (!is_array($events)) return array();
		return $events;
	}
	
	function getUserEvents() {
		return self::findAll(array(
			'conditions' => array(
				'`is_system` = ?',
				false
			)
		));
	}
	
	/**
	 * 
	 * @param $name string
	 * @return CronEvent
	 */
	function getByName($name) {
		return self::findOne(array(
			'conditions' => array(
				'`name` = ?',
				$name
			)
		));
	}
	
} // CronEvents

?>