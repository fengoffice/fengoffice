<?php

/**
 * class Timeslots
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class Timeslots extends BaseTimeslots {

	public function __construct() {
		parent::__construct ();
		$this->object_type_name = 'timeslot';
	}
	
	/**
	 * Return object timeslots
	 *
	 * @param ContentDataObject $object
	 * @return array
	 */
	static function getTimeslotsByObject(ContentDataObject $object, $user = null) {
		$userCondition = '';
		if ($user)
			$userCondition = ' AND `contact_id` = '. $user->getId();

		return self::findAll(array(
          'conditions' => array('`rel_object_id` = ?' . $userCondition, $object->getObjectId()),
          'order' => '`e`.`start_time`'
          ));
	}
	
	
	static function getOpenTimeslotByObject(ContentDataObject $object, $user = null) {
		$userCondition = '';
		if ($user)
			$userCondition = ' AND `user_id` = '. $user->getId();

		return self::findOne(array(
          'conditions' => array('`rel_object_id` = ? AND `end_time`= ? ' . $userCondition, $object->getObjectId(), EMPTY_DATETIME), 
          'order' => '`e`.`start_time`'
          ));
	}


	private $cached_timeslots = null;

	function getOpenTimeslotsByObject($object_id) {
		if ($this->cached_timeslots == null) {
			$this->cached_timeslots = array();
			$cached_ts = self::findAll(array('conditions' => array('`end_time`= ? ', EMPTY_DATETIME), 'order' => 'start_time'));
			foreach ($cached_ts as $ct) {
				if (!isset($this->cached_timeslots[$ct->getRelObjectId()])) $this->cached_timeslots[$ct->getRelObjectId()] = array();
				$this->cached_timeslots[$ct->getRelObjectId()][] = $ct;
			}
		}
		return array_var($this->cached_timeslots, $object_id, array());
	}

	/**
	 * Return number of timeslots for specific object
	 *
	 * @param ContentDataObject $object
	 * @return integer
	 */
	static function countTimeslotsByObject(ContentDataObject $object, $user = null) {
		$userCondition = '';
		if ($user)
			$userCondition = ' AND `contact_id` = '. $user->getId();

		return self::count(array('`rel_object_id` = ? ' . $userCondition, $object->getObjectId()));
	} // countTimeslotsByObject

	/**
	 * Drop timeslots by object
	 *
	 * @param ContentDataObject
	 * @return boolean
	 */
	static function dropTimeslotsByObject(ContentDataObject $object) {
		$timeslots = self::findAll(array('conditions' => array('`rel_object_id` = ?', $object->getObjectId())));
		foreach ($timeslots as $timeslot) {
			$timeslot->delete();
		}
	} // dropTimeslotsByObject

	/**
	 * Returns timeslots based on the set query parameters
	 *
	 * @param User $user
	 * @param string $workspacesCSV
	 * @param DateTimeValue $start_date
	 * @param DateTimeValue $end_date
	 * @param string $object_id
	 * @param array $group_by
	 * @param array $order_by
	 * @return array
	 */
	static function getTaskTimeslots($context, $members = null, $user = null, $start_date = null, $end_date = null, $object_id = 0, $group_by = null, $order_by = null, $limit = 0, $offset = 0, $timeslot_type = 0, $extra_conditions=''){
		
		$commonConditions = "";
		if ($start_date) {
			$commonConditions .= DB::prepareString(' AND `e`.`start_time` >= ? ', array($start_date));
		}
		if ($end_date) {
			$commonConditions .= DB::prepareString(' AND (`e`.`paused_on` <> 0 AND `e`.`paused_on` <= ? OR `e`.`end_time` <> 0 AND `e`.`end_time` <= ?) ', array($end_date, $end_date));
		}
		//User condition
		$commonConditions .= $user ? ' AND `e`.`contact_id` = '. $user->getId() : '';
		
		//Object condition
		$commonConditions .= $object_id > 0 ? ' AND `e`.`rel_object_id` = ' . $object_id : '';
		
		switch($timeslot_type){
			case 0: //Task timeslots
				$conditions = " AND EXISTS (SELECT `obj`.`id` FROM `" . TABLE_PREFIX . "objects` `obj` WHERE `obj`.`id` = `e`.`rel_object_id` AND `obj`.`trashed_on` = 0 AND `obj`.`archived_on` = 0)";
				break;
			case 1: //Time timeslots
				$conditions = " AND `e`.`rel_object_id` = 0";
				break;
			case 2: //All timeslots
				$conditions = " AND (`e`.`rel_object_id` = 0 OR `e`.`rel_object_id` IN (SELECT `obj`.`id` FROM `" . TABLE_PREFIX . "objects` `obj` WHERE `obj`.`trashed_on` = 0 AND `obj`.`archived_on` = 0))";
				break;
			default:
				throw new Error("Timeslot type not recognised: " . $timeslot_type);
		}
		
		if (!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks')) {
			$conditions .= " AND `e`.`contact_id` = " . logged_user()->getId();
		}
		
		$conditions .= $commonConditions . $extra_conditions;
		
		$order_by[] = 'start_time';
		$result = self::instance()->listing(array(
			'order' => $order_by,
			'extra_conditions' => $conditions,
		));
		
		return $result->objects;
	}
	
	static function updateBillingValues() {
		$timeslot_rows = DB::executeAll("SELECT * FROM ".TABLE_PREFIX."timeslots WHERE `end_time` > 0 AND billing_id = 0 AND is_fixed_billing = 0");
	
		$users = Contacts::getAllUsers();
		$usArray = array();
		foreach ($users as $u){
			$usArray[$u->getId()] = $u;
		}
		$count = 0;
	
		$categories_cache = array();
		
		foreach ($timeslot_rows as $ts_row){
			
			if ($ts_row['start_time'] == EMPTY_DATETIME) {
				$ts_row['minutes'] = 0;
			} else {
				$startTime = DateTimeValueLib::makeFromString($ts_row['start_time']);
				if ($ts_row['start_time'] == EMPTY_DATETIME) {
					$endTime = $ts_row['is_paused'] ? DateTimeValueLib::makeFromString($ts_row['paused_on']) : DateTimeValueLib::now();
				} else {
					$endTime = DateTimeValueLib::makeFromString($ts_row['end_time']);
				}
				$timeDiff = DateTimeValueLib::get_time_difference($startTime->getTimestamp(), $endTime->getTimestamp(), $ts_row['subtract']);
				$ts_row['minutes'] = $timeDiff['days'] * 1440 + $timeDiff['hours'] * 60 + $timeDiff['minutes'];
			}
			
			$user = $usArray[$ts_row['contact_id']];
			if ($user instanceof Contact){
				$billing_category_id = $user->getDefaultBillingId();
				if ($billing_category_id > 0){
					
					$hours = $ts_row['minutes'] / 60;
					
					$billing_category = array_var($categories_cache, $billing_category_id);
					if (!$billing_category instanceof BillingCategory) {
						$billing_category = BillingCategories::findById($billing_category_id);
						$categories_cache[$billing_category_id] = $billing_category;
					}
					
					if ($billing_category instanceof BillingCategory){
						$hourly_billing = $billing_category->getDefaultValue();
						
						DB::execute("UPDATE ".TABLE_PREFIX."timeslots SET billing_id='$billing_category_id', hourly_billing='$hourly_billing', 
							fixed_billing='".round($hourly_billing * $hours, 2)."', is_fixed_billing=0 
							WHERE object_id=".$ts_row['object_id']);
						
						$count++;
					}
				}
			} else {
				DB::execute("UPDATE ".TABLE_PREFIX."timeslots SET is_fixed_billing=1 WHERE object_id=".$ts_row['object_id']);
			}
		}
		return $count;
	}
	
	
	
	static function getTimeslotsByUserWorkspacesAndDate(DateTimeValue $start_date, DateTimeValue $end_date, $object_manager, $user = null, $workspacesCSV = null, $object_id = 0){
		return array(); //FIXME or REMOVEME
		$userCondition = '';
		if ($user)
			$userCondition = ' AND `contact_id` = '. $user->getId();
		
		$projectCondition = '';
		if ($workspacesCSV && $object_manager == 'ProjectTasks')
			$projectCondition = ' AND (SELECT count(*) FROM `'. TABLE_PREFIX . 'project_tasks` as `pt`, `' . TABLE_PREFIX . 'workspace_objects` AS `wo` WHERE `pt`.`id` = `rel_object_id` AND `pt`.`trashed_on` = 0 AND ' .
			"`wo`.`object_manager` = 'ProjectTasks' AND `wo`.`object_id` = `object_id` AND `wo`.`workspace_id` IN (" . $workspacesCSV . ')) > 0';
			
		/* TODO: handle permissions with permissions_sql_for_listings */
		$permissions = "";
		if ($object_manager == 'ProjectTasks') {
			$permissions = ' AND (SELECT count(*) FROM `'. TABLE_PREFIX . 'project_tasks` as `pt`, `' . TABLE_PREFIX . 'workspace_objects` AS `wo` WHERE `pt`.`id` = `rel_object_id` AND `pt`.`trashed_on` = 0 AND ' .
			"`wo`.`object_manager` = 'ProjectTasks' AND `wo`.`object_id` = `object_id` AND `wo`.`workspace_id` IN (" . logged_user()->getWorkspacesQuery() . ')) > 0';
		}
			
		$objectCondition = '';
		if ($object_id > 0)
			$objectCondition = ' AND `rel_object_id` = ' . $object_id;
		
		return self::findAll(array(
          'conditions' => array('`start_time` > ? and `end_time` < ?' . $userCondition . $projectCondition . $permissions . $objectCondition, $start_date, $end_date),
          'order' => '`start_time`'
        ));
	
	}

	static function getGeneralTimeslots($context, $user = null, $offset = 0, $limit = 20, $only_count_result = false) {
			
		$user_sql = "";
		if ($user instanceof Contact) {
			$user_sql = " AND contact_id = " . $user->getId();
		}
		
		$result = Timeslots::instance()->listing(array(
			"order" => array('start_time', 'updated_on'),
			"order_dir" => "DESC",
		 	"extra_conditions" => " AND rel_object_id = 0" . $user_sql,
			'only_count_results' => $only_count_result,
			"start" => $offset,
			"limit" => $limit			
		));
		return $result;
	}
	
	static function getTotalMinutesWorkedOnObject($object_id) {
		$sql = " SELECT SUM(GREATEST(TIMESTAMPDIFF(MINUTE,start_time,end_time),0)) - SUM(subtract/60) as total
				FROM `".TABLE_PREFIX."timeslots`
				WHERE `rel_object_id` =  ". $object_id ." 
				AND `end_time` > ".DB::escape(EMPTY_DATETIME).";";
		return array_var(DB::executeOne($sql), "total");
	}
	
	static function getTotalSecondsWorkedOnObject($object_id) {//getTotalSecondsWorkedOnObject
		$totalMinutes = Timeslots::getTotalMinutesWorkedOnObject($object_id);
		$totalSeconds = $totalMinutes * 60;
		return $totalSeconds;
	}
	
} // Timeslots

?>