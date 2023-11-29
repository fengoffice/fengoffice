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
	static function getAllRemindersByObjectAndUser($object, $user, $context = null, $include_subscriber_reminders = false) {
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
		$reminders = ObjectReminders::instance()->findAll(array(
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
	static function getByObject($object) {
		return self::instance()->findAll(array(
			'conditions' => array("`object_id` = ?",
				$object->getId()
		)));
	}
	
	static function getDueReminders($type = null) {
		if (isset($type)) {
			$extra = ' AND `type` = ' . DB::escape($type);
		} else {
			$extra = "";
		}
		$yesterday = DateTimeValueLib::now();
		$yesterday = $yesterday->add('d', -1);
		/*$template_cond = " AND (SELECT o.object_type_id FROM ".TABLE_PREFIX."objects o WHERE o.id=object_id) NOT IN (
				SELECT ot.id FROM ".TABLE_PREFIX."object_types ot WHERE ot.name IN ('template_task','template_milestone')
		)";*/
		
		return ObjectReminders::instance()->findAll(array(
			'conditions' => array(
				"`date` > ? AND `date` < ?" . $extra, $yesterday, DateTimeValueLib::now(),
			),
			'order' => "date desc",
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
		$reminders = ObjectReminders::instance()->findAll(array(
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
		$reminders = ObjectReminders::instance()->findAll(array(
        	'conditions' => '`contact_id` = ' . DB::escape($user->getId())
		)); // findAll
		if(is_array($reminders)) {
			foreach($reminders as $Reminder) {
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
		return ObjectReminders::instance()->delete(
      		'`object_id` = ' . DB::escape($object->getId()));
	} // clearByObject

	static function clearByObjectAndUser(ContentDataObject $object, Contact $user, $include_subscribers = false) {
		if ($include_subscribers) {
			$usercond = '(`contact_id` = '. DB::escape($user->getId()) .' OR `contact_id` = 0)';
		} else {
			$usercond = '`contact_id` = '. DB::escape($user->getId());
		}
		return ObjectReminders::instance()->delete(
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
		return ObjectReminders::instance()->delete('`contact_id` = ' . DB::escape($user->getId()));
	} // clearByUser
        
        function findByEvent($event_id) {
                return ObjectReminders::instance()->findAll(array('conditions' => array('`object_id` = ?', $event_id)));
        }

} // ObjectReminders

?>