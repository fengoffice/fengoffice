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
			$emails = self::findAll(array(
				'conditions' => array('`timestamp` >= ?', $date),
				'order' => '`timestamp` ASC'
			));
			self::delete(array('`timestamp` < ?', $date));
		} else {
			$emails = self::findAll();
		}
		return $emails;
	}

} // QueuedEmails

?>