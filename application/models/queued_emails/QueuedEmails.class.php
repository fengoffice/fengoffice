<?php

/**
 * QueuedEmails class
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class QueuedEmails extends BaseQueuedEmails {

	/**
	 * Returns all queued emails younger than the given date (or all if none given)
	 * and deletes emails younger than the given date if one given.
	 * @return array
	 */
	static function getQueuedEmails($date = null) {
		if ($date instanceof DateTimeValue) {
			$emails = self::instance()->findAll(array(
				'conditions' => array('`timestamp` >= ? AND `timestamp` <= ?', $date, DateTimeValueLib::now()),
				'order' => '`timestamp` ASC'
			));
			self::instance()->delete(array('`timestamp` < ?', $date));
		} else {
			$emails = self::instance()->findAll(array(
				'conditions' => array('`timestamp` <= ?', DateTimeValueLib::now()),
				'order' => '`timestamp` ASC'
			));
		}
		return $emails;
	}

} // QueuedEmails

?>