<?php

/**
 * ObjectReminders
 *
 * @author Ignacio de Soto <ignacio.desoto@fengoffice.com>
 */
class ObjectReminders extends BaseObjectReminders {

	/**
	 * Returns reminders that a user has for a specific object.
	 *
	 * @param ContentDataObject $object
	 * @param Contact $user
	 */
	function getAllRemindersByObjectAndUser($object, $user, $context = null, $include_subscriber_reminders = false) {
		if (isset($context)) {
			$extra = ' AND `context` = ' . DB::escape($context);
		} else {
			$extra = "";
		}
		if ($include_subscriber_reminders) {
			$usercond = '(`contact_id` = ? OR `contact_id` = 0)';
		} else {
			$usercond = '`contact_id` = ?';
		}
		$reminders = ObjectReminders::findAll(array(
        	'conditions' => array("`object_id` = ? AND $usercond" . $extra,
					$object->getId(),
        			$user->getId()
		)));
		return $reminders;
	}
	
	/**
	 * Returns reminders for an object
	 * @param $object
	 * @return unknown_type
	 */
	function getByObject($object) {
		return self::findAll(array(
			'conditions' => array("`object_id` = ?",
				$object->getId()
		)));
	}
	
	function getDueReminders($type = null) {
		if (isset($type)) {
			$extra = ' AND `type` = ' . DB::escape($type);
		} else {
			$extra = "";
		}
		return ObjectReminders::findAll(array(
			'conditions' => array(
				"`date` > '0000-00-00 00:00:00' AND `date` < ?" . $extra, DateTimeValueLib::now(),
			),
			'limit' => config_option('cron reminder limit', 100)
		));
	}
	
	/**
	 * Return array of users that have reminders for an object
	 *
	 * @param ContentDataObject $object
	 * @return array
	 */
	static function getUsersByObject(ContentDataObject $object) {
		$users = array();
		$reminders = ObjectReminders::findAll(array(
        	'conditions' => '`object_id` = ' . DB::escape($object->getId())
		)); // findAll
		if(is_array($reminders)) {
			foreach($reminders as $reminder) {
				$user = $reminder->getUser();
				if($user instanceof Contact) $users[] = $user;
			} // foreach
		} // if
		return count($users) ? $users : null;
	} // getUsersByObject

	/**
	 * Return array of objects that $user has reminders for
	 *
	 * @param Contact $user
	 * @return array
	 */
	static function getObjectsByUser(Contact $user) {
		$objects = array();
		$reminders = ObjectReminders::findAll(array(
        	'conditions' => '`contact_id` = ' . DB::escape($user->getId())
		)); // findAll
		if(is_array($Reminders)) {
			foreach($Reminders as $Reminder) {
				$object = $Reminder->getObject();
				if($object instanceof ContentDataObject) $objects[] = $object;
			} // foreach
		} // if
		return $objects;
	} // getObjectsByUser

	/**
	 * Clear reminders by object
	 *
	 * @param ContentDataObject $object
	 * @return boolean
	 */
	static function clearByObject(ContentDataObject $object) {
		return ObjectReminders::delete(
      		'`object_id` = ' . DB::escape($object->getId()));
	} // clearByObject

	static function clearByObjectAndUser(ContentDataObject $object, Contact $user, $include_subscribers = false) {
		if ($include_subscribers) {
			$usercond = '(`contact_id` = '. DB::escape($user->getId()) .' OR `contact_id` = 0)';
		} else {
			$usercond = '`contact_id` = '. DB::escape($user->getId());
		}
		return ObjectReminders::delete(
      		'`object_id` = ' . DB::escape($object->getId()) .
      		" AND $usercond"
		);
	}
	
	/**
	 * Clear Reminders by user
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	static function clearByUser(Contact $user) {
		return ObjectReminders::delete('`contact_id` = ' . DB::escape($user->getId()));
	} // clearByUser
        
        function findByEvent($event_id) {
                return ObjectReminders::findAll(array('conditions' => array('`object_id` = ?', $event_id)));
        }

} // ObjectReminders

?>