<?php

/**
 * Application Read Logs manager class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class ApplicationReadLogs extends BaseApplicationReadLogs {

	const ACTION_READ         = 'read';
	const ACTION_DOWNLOAD     = 'download';
		
	/**
	 * Create new log entry and return it
	 *
	 * Delete actions are automatically marked as silent if $is_silent value is not provided (not NULL)
	 *
	 * @param ApplicationDataObject $object
	 * @param Project $project
	 * @param DataManager $manager
	 * @param boolean $save Save log object before you save it
	 * @return ApplicationReadLog
	 */
	static function createLog(ApplicationDataObject $object, $action = null, $save = true, $log_data = '') {
		if(is_null($action)) {
			$action = self::ACTION_READ;
		} // if
		if(!self::isValidAction($action)) {
			throw new Error("'$action' is not valid log action");
		} // if

		try {
			Notifier::notifyAction($object, $action, $log_data);
		} catch (Exception $ex) {
			
		}
		
		$manager = $object->manager();
		if(!($manager instanceof DataManager)) {
			throw new Error('Invalid object manager');
		} // if

		$log = new ApplicationReadLog();

		if (logged_user() instanceof Contact) {
			$log->setTakenById(logged_user()->getId());
		} else {
			$log->setTakenById(0);
		}
		$log->setRelObjectId($object->getObjectId());
		$log->setAction($action);
		
		if($save) {
			$log->save();
		} // if
		
		return $log;
	} // createLog

	
	/**
	 * Check if specific action is valid
	 *
	 * @param string $action
	 * @return boolean
	 */
	static function isValidAction($action) {
		static $valid_actions = null;

		if(!is_array($valid_actions)) {
			$valid_actions = array(
			self::ACTION_READ,
			self::ACTION_DOWNLOAD
			); // array
		} // if

		return in_array($action, $valid_actions);
	} // isValidAction

	/**
	 * Return entries related to specific object
	 *
	 * $limit and $offset are there to control the range of the result,
	 * usually we don't want to pull the entire log but just the few most recent entries. If NULL they will be ignored
	 *
	 * @param ApplicationDataObject $object
	 * @param integer $limit
	 * @param integer $offset
	 * @return array
	 */
	static function getObjectLogs($object, $limit = null, $offset = null, $extra_conditions = "") {

		return self::findAll(array(
			'conditions' => array('`rel_object_id` = (?)' . $extra_conditions, $object->getId()),
			'order' => '`created_on` DESC',
			'limit' => $limit,
			'offset' => $offset,
		)); // findAll
	} // getObjectLogs
	
	
	static function countObjectLogs($object, $extra_conditions = "") {
	
		return self::count("`rel_object_id` = ".$object->getId() ." ". $extra_conditions);
		
	}

} // ApplicationReadLogs

?>