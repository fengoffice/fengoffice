<?php

/**
 * TemplateTasks
 *
 */
class TemplateTasks extends BaseTemplateTasks {

	function __construct() {
		parent::__construct();
		$this->object_type_name = 'template_task';
	}
	
	const ORDER_BY_ORDER = 'order';
	const ORDER_BY_STARTDATE = 'startDate';
	const ORDER_BY_DUEDATE = 'dueDate';
	const PRIORITY_URGENT = 400;
	const PRIORITY_HIGH = 300;
	const PRIORITY_NORMAL = 200;
	const PRIORITY_LOW = 100;
	

	/**
	 * Return tasks on which the user has an open timeslot
	 *
	 * @return array
	 */
	static function getOpenTimeslotTasks($context, Contact $user, $assigned_to_contact = null, $archived = false) {
		
		$archived_cond = " AND `o`.`archived_on` " . ($archived ? "<>" : "=") . " 0 ";
		
		$open_timeslot = " AND `e`.`object_id` IN (SELECT `t`.`rel_object_id` FROM " . Timeslots::instance()->getTableName(true) . " `t` WHERE `t`.`contact_id` = " . $user->getId () . " AND `t`.`end_time` = '" . EMPTY_DATETIME . "')";
		
		$assigned_to_str = "";
		if ($assigned_to_contact) {
			if ($assigned_to_contact == - 1)
				$assigned_to_contact = 0;
			$assigned_to_str = " AND `e`.`assigned_to_contact_id` = " . DB::escape ( $assigned_to_contact ) . " ";
		}
			
		$result = self::instance()->listing(array(
			"order" => 'due_date',
			"order_dir" => "ASC",
			"extra_conditions" => ' AND `is_template` = false' . $archived_cond . $assigned_to_str . $open_timeslot
		));

		$objects = $result->objects;

		return $objects;
	}
	
	/**
	 * Returns all task templates
	 *
	 */
	static function getAllTaskTemplates($only_parent_task_templates = false, $archived = false) {
		if ($archived)
			$archived_cond = "AND `archived_on` <> 0";
		else
			$archived_cond = "AND `archived_on` = 0";
		
		$conditions = " `is_template` = true $archived_cond";
		if ($only_parent_task_templates)
			$conditions .= "  and `parent_id` = 0  ";
		$order_by = "`title` ASC";
		$tasks = TemplateTasks::find ( array ('conditions' => $conditions, 'order' => $order_by ) );
		if (! is_array ( $tasks ))
			$tasks = array ();
		return $tasks;
	}
	
	/**
	 * Returns all task templates for a template
	 *@param template_id
	 */
	static function getAllTaskTemplatesByTemplate($template_id) {
		
		$conditions = " `template_id` = $template_id";
		$conditions .= "  AND `parent_id` = 0  ";
		$tasks = TemplateTasks::find ( array ('conditions' => $conditions) );
		if (! is_array ( $tasks ))
			$tasks = array ();
		return $tasks;
	}
	
	/**
	 * Returns all task templates for a session_id 
	 *@param template_id
	 */
	static function getAllTaskTemplatesBySessionId($session_id) {
	
		$conditions = "`session_id` = $session_id";
		$tasks = TemplateTasks::find ( array ('conditions' => $conditions) );
		if (! is_array ( $tasks ))
			$tasks = array ();
		return $tasks;
	}
	
	function maxOrder($parentId = null, $milestoneId = null) {
		$condition = "`trashed_on` = 0 AND `is_template` = false AND `archived_on` = 0";
		if (is_numeric ( $parentId )) {
			$condition .= " AND ";
			$condition .= " `parent_id` = " . DB::escape ( $parentId );
		}
		if (is_numeric ( $milestoneId )) {
			$condition .= " AND ";
			$condition .= " `milestone_id` = " . DB::escape ( $milestoneId );
		}
		$res = DB::execute ( "
			SELECT max(`order`) as `max` 
			FROM `" . TABLE_PREFIX . "template_tasks` t  
			INNER JOIN `" . TABLE_PREFIX . "objects` o" . " ON t.object_id = o.id  
			WHERE " . $condition );
		if ($res->numRows () < 1) {
			return 0;
		} else {
			$row = $res->fetchRow ();
			return $row ["max"] + 1;
		}
	}
	
	/**
	 * Return Day tasks this user have access on
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getRangeTasksByUser(DateTimeValue $date_start, DateTimeValue $date_end, $assignedUser, $task_filter = null, $archived = false) {
		
		$from_date = new DateTimeValue ( $date_start->getTimestamp () - logged_user()->getTimezone() * 3600 );
		$from_date = $from_date->beginningOfDay ();
		$to_date = new DateTimeValue ( $date_end->getTimestamp () - logged_user()->getTimezone() * 3600);
		$to_date = $to_date->endOfDay ();
		
		$assignedFilter = '';
		if ($assignedUser instanceof Contact) {
			$assignedFilter = ' AND (`assigned_to_contact_id` = ' . $assignedUser->getId () . ' OR `assigned_to_contact_id` = \'' . $assignedUser->getCompanyId () . '\') ';
		}
		$rep_condition = " (`repeat_forever` = 1 OR `repeat_num` > 0 OR (`repeat_end` > 0 AND `repeat_end` >= '" . $from_date->toMySQL () . "')) ";
		
		if ($archived)
			$archived_cond = " AND `archived_on` <> 0";
		else
			$archived_cond = " AND `archived_on` = 0";
		
		switch($task_filter){
			case 'complete':
				$conditions = DB::prepareString(' AND `completed_on` <> ?', array(EMPTY_DATETIME));
				break;
			case 'pending':
			default:
				$conditions = DB::prepareString(' AND `is_template` = false AND `completed_on` = ? AND (IF(due_date>0,(`due_date` >= ? AND `due_date` < ?),false) OR IF(start_date>0,(`start_date` >= ? AND `start_date` < ?),false) OR ' . $rep_condition . ') ' . $archived_cond . $assignedFilter, array(EMPTY_DATETIME,$from_date, $to_date, $from_date, $to_date));
				break;
		}
                
		$result = self::instance()->listing(array(
			"extra_conditions" => $conditions
		));
		
		return $result->objects;
	} // getDayTasksByUser
	

	/**
	 * Returns an unsaved copy of the task. Copies everything except open/closed state,
	 * anything that needs the task to have an id (like tags, properties, subtask),
	 * administrative info like who created the task and when, etc.
	 *
	 * @param TemplateTask $task
	 * @return TemplateTask
	 */
	function createTaskCopy(TemplateTask $task) {
		$new = new TemplateTask ();
		$new->setMilestoneId ( $task->getMilestoneId () );
		$new->setParentId ( $task->getParentId () );
		$new->setObjectName($task->getObjectName()) ;
		$new->setAssignedToContactId ( $task->getAssignedToContactId () );
		$new->setPriority ( $task->getPriority () );
		$new->setTimeEstimate ( $task->getTimeEstimate () );
		$new->setText ( $task->getText () );
		$new->setOrder ( TemplateTasks::maxOrder ( $new->getParentId (), $new->getMilestoneId () ) );
		$new->setStartDate ( $task->getStartDate () );
		$new->setDueDate ( $task->getDueDate () );
		return $new;
	}
	
	/**
	 * Copies subtasks from taskFrom to taskTo.
	 *
	 * @param TemplateTask $taskFrom
	 * @param TemplateTask $taskTo
	 */
	function copySubTasks(TemplateTask $taskFrom, TemplateTask $taskTo, $as_template = false) {
		foreach ( $taskFrom->getSubTasks () as $sub ) {
			if ($sub->getId() == $taskTo->getId()) continue;
			$new = TemplateTasks::createTaskCopy ( $sub );
			
			$new->setParentId ( $taskTo->getId () );
			$new->setMilestoneId ( $taskTo->getMilestoneId () );
			$new->setOrder ( TemplateTasks::maxOrder ( $new->getParentId (), $new->getMilestoneId () ) );
			
			$new->save ();
			
			$object_controller = new ObjectController();
			if (count($taskFrom->getMemberIds())) {
				$object_controller->add_to_members($new, $taskFrom->getMemberIds());
			}
			$new->copyCustomPropertiesFrom ( $sub );
			$new->copyLinkedObjectsFrom ( $sub );
			TemplateTasks::copySubTasks ( $sub, $new, $as_template );
		}
	}
	
		
	static function getUpcomingWithoutDate($limit = null ) {
		$conditions = " AND is_template = 0 AND `e`.`completed_by_id` = 0 AND `e`.`due_date` = '0000-00-00 00:00:00' " ;
		
		if (!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks')) {
			$conditions .= " AND assigned_to_contact_id = ".logged_user()->getId();
		}
		
		$tasks_result = self::instance()->listing(array(
			"start"=> 0,
			"limit"=>$limit, 
			"extra_conditions"=>$conditions, 
			"order"=>  array('due_date', 'priority') , 
			"order_dir" => "ASC"
		));
		return $tasks_result->objects;
	}


	static function getOverdueAndUpcomingObjects($limit = null) {
		$conditions_tasks = " AND is_template = 0 AND `e`.`completed_by_id` = 0 AND `e`.`due_date` > 0";
		$conditions_milestones = " AND is_template = 0 AND `e`.`completed_by_id` = 0 AND `e`.`due_date` > 0";
		
		if (!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks')) {
			$conditions_tasks .= " AND assigned_to_contact_id = ".logged_user()->getId();
		}
		
		$tasks_result = self::instance()->listing(array(
			"limit" => $limit, 
			"extra_conditions" => $conditions_tasks, 
			"order"=>  array('due_date', 'priority'), 
			"order_dir" => "ASC"
		));
		$tasks = $tasks_result->objects;
		
		$milestones_result = TemplateMilestones::instance()->listing(array(
			"limit" => $limit, 
			"extra_conditions" => $conditions_milestones, 
			"order" => array('due_date'), 
			"order_dir" => "ASC"
		));
		$milestones = $milestones_result->objects;
		
		$ordered = array();
		foreach ($tasks as $task) { /* @var $task ProjectTask */
			if (!$task->isCompleted() && $task->getDueDate() instanceof  DateTimeValue ) {
				if (!isset($ordered[$task->getDueDate()->getTimestamp()])){ 
					$ordered[$task->getDueDate()->getTimestamp()] = array();
				}
				$ordered[$task->getDueDate()->getTimestamp()][] = $task;
			}
		}
		foreach ($milestones as $milestone) {
			if (!isset($ordered[$milestone->getDueDate()->getTimestamp()])) {
				$ordered[$milestone->getDueDate()->getTimestamp()] = array();
			}
			$ordered[$milestone->getDueDate()->getTimestamp()][] = $milestone;
		}
		
		ksort($ordered, SORT_NUMERIC);
		
		$ordered_flat = array();
		foreach ($ordered as $k => $values) {
			foreach ($values as $v) $ordered_flat[] = $v;
		}
		
		return $ordered_flat;
	}
	
	
	/**
	 * 
	 * @deprecated by listing
	 * @param unknown_type $context
	 * @param unknown_type $object_type
	 * @param unknown_type $order
	 * @param unknown_type $order_dir
	 * @param unknown_type $extra_conditions
	 * @param unknown_type $join_params
	 * @param unknown_type $trashed
	 * @param unknown_type $archived
	 * @param unknown_type $start
	 * @param unknown_type $limit
	 */
	static function getContentObjects($context, $object_type, $order=null, $order_dir=null, $extra_conditions=null, $join_params=null, $trashed=false, $archived=false, $start = 0 , $limit=null){
		
		if (is_null($extra_conditions)) $extra_conditions = "";
		$extra_conditions .= " AND `e`.`is_template` = 0";
		
		
		return parent::getContentObjects($context, $object_type, $order, $order_dir, $extra_conditions, $join_params, $trashed, $archived, $start, $limit);
		
	}
	
	
	
	/**
	 * Same that getContentObjects but reading from sahring table 
	 * @deprecated by parent::listing()
	 **/
	static function findByContext( $options = array () ) {
		// Initialize method result
		$result = new stdClass();
		$result->total = 0 ;
		$result->objects = array() ;
		
		// Read arguments and Init Vars
		$limit = array_var($options,'limit');
		$members = active_context_members(false); // 70
		$type_id = self::instance()->getObjectTypeId();
		if (!count($members)) return $res ; 
		$uid = logged_user()->getId() ;
		if ($limit>0){
			$limit_sql = "LIMIT $limit";
		}else{
			$limit_sql = '' ;
		}
		
		// Build Main SQL
	    $sql = "
	    	SELECT distinct(id) FROM ".TABLE_PREFIX."objects
	    	WHERE 
	    		id IN ( 
	    			SELECT object_id FROM ".TABLE_PREFIX."sharing_table
	    			WHERE group_id  IN (
		     			SELECT permission_group_id FROM ".TABLE_PREFIX."contact_permission_groups WHERE contact_id = $uid
					)
				) AND 
				id IN (
	 				SELECT object_id FROM ".TABLE_PREFIX."object_members 
	 				WHERE member_id IN (".implode(',', $members).")
	 				GROUP BY object_id
	 				HAVING count(member_id) = ".count($members)."
				) AND 
				object_type_id = $type_id AND ".SQL_NOT_DELETED."  
			$limit_sql";
			
		// Execute query and build the resultset	
	    $rows = DB::executeAll($sql);
		foreach ($rows as $row) {
    		$task =  TemplateTasks::findById($row['id']);
    		if ($task && $task instanceof TemplateTask) {
    			if($task->getDueDate()){
	    			$k  = "#".$task->getDueDate()->getTimestamp().$task->getId();
					$result->objects[$k] = $task ;
    			}else{
    				$result->objects[] = $task ;
    			}
				$result->total++;
    		}
		}
		
		// Sort by key
		ksort($result->objects);
		
		// Remove keys	
		$result->objects = array_values($result->objects);
		return $result;
	}
	
	
	private $cached_related = array();
	function findByRelatedCached($task_id, $all_task_ids = null) {
		if (!isset($this->cached_related[$task_id])) {
			if (is_array($all_task_ids) && count($all_task_ids) > 0) {
				$obj_cond = "original_task_id IN (".implode(",", $all_task_ids).")";
			} else {
				$obj_cond = "original_task_id = $task_id";
			}
			
			$db_res = DB::execute("SELECT object_id, original_task_id FROM ".TABLE_PREFIX."template_tasks WHERE $obj_cond");
			$rows = $db_res->fetchAll();
			if (is_array($rows)) {
				foreach ($rows as $row) {
					if (!isset($this->cached_related[$row['original_task_id']])) $this->cached_related[$row['original_task_id']] = array();
					$this->cached_related[$row['original_task_id']][] = $row['object_id'];
				}
			}
			
			if (is_array($all_task_ids)) {
				foreach ($all_task_ids as $tid) {
					if (!isset($this->cached_related[$tid])) $this->cached_related[$tid] = array();
				}
			}
		}
		
		$related = array_var($this->cached_related, $task_id, array());
		if (count($related) > 0) {
			return self::findAll(array('conditions' => 'object_id IN ('.implode(',', $related).')'));
		}
		return array();
	}

	function findByRelated($task_id) {
		return TemplateTasks::findAll(array('conditions' => array('`original_task_id` = ?', $task_id)));
	}

	function findByTaskAndRelated($task_id,$original_task_id) {
		return TemplateTasks::findAll(array('conditions' => array('(`original_task_id` = ? OR `object_id` = ?) AND `object_id` <> ?', $original_task_id,$original_task_id,$task_id)));
	}
	
	
	static function getArrayInfo($raw_data, $full = false){
		$desc = "";
		if ($full) {
			if(config_option("wysiwyg_tasks")){
				if($raw_data['type_content'] == "text"){
					$desc = nl2br(htmlspecialchars($raw_data['text']));
				}else{
					$desc = purify_html(nl2br($raw_data['text']));
				}
			}else{
				if($raw_data['type_content'] == "text"){
					$desc = htmlspecialchars($raw_data['text']);
				}else{
					$desc = html_to_text(html_entity_decode(nl2br($raw_data['text']), null, "UTF-8"));
				}
			}
		}

		$member_ids = ObjectMembers::instance()->getCachedObjectMembers($raw_data['id']);
		$tmp_task = new TemplateTask();
		$tmp_task->setObjectId($raw_data['id']);
		$tmp_task->setId($raw_data['id']);
		$tmp_task->setAssignedToContactId($raw_data['assigned_to_contact_id']);
		
		$result = array(
			'id' => $raw_data['id'],
			't' => $raw_data['name'],
			'desc' => $desc,
			'members' => $member_ids,
			'c' => strtotime($raw_data['created_on']),
			'cid' => (int)$raw_data['created_by_id'],
			'otype' => $raw_data['object_subtype'],
			'pc' => (int)$raw_data['percent_completed'],
			'memPath' => str_replace('"',"'", escape_character(json_encode($tmp_task->getMembersIdsToDisplayPath())))
		);

		$result['mas'] = (int)array_var($raw_data, 'multi_assignment');
			
		if ($raw_data['completed_by_id'] > 0) {
			$result['s'] = 1;
		}
			
		if ($raw_data['parent_id'] > 0) {
			$result['pid'] = (int)$raw_data['parent_id'];
		}
		//if ($this->getPriority() != 200)
		$result['pr'] = (int)$raw_data['priority'];

		if ($raw_data['milestone_id'] > 0) {
			$result['mid'] = (int)$raw_data['milestone_id'];
		}
			
		if ($raw_data['assigned_to_contact_id'] > 0) {
			$result['atid'] = (int)$raw_data['assigned_to_contact_id'];
		}
		$result['atName'] = $tmp_task->getAssignedToName();

		if ($raw_data['completed_by_id'] > 0) {
			$result['cbid'] = (int)$raw_data['completed_by_id'];
			$result['con'] = strtotime($raw_data['completed_on']);;
		}
			
		if ($raw_data['due_date'] != EMPTY_DATETIME) {
			$result['dd'] = strtotime($raw_data['due_date']) + logged_user()->getTimezone() * 3600;
			$result['udt'] = $raw_data['use_due_time'] ? 1 : 0;
		}
		if ($raw_data['start_date'] != EMPTY_DATETIME) {
			$result['sd'] = strtotime($raw_data['start_date']) + logged_user()->getTimezone() * 3600;
			$result['ust'] = $raw_data['use_start_time'] ? 1 : 0;
		}

		$time_estimate = $raw_data['time_estimate'];
		$result['te'] = $raw_data['time_estimate'];
		if ($time_estimate > 0) $result['et'] = DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($time_estimate * 60), 'hm', 60) ;


		$result['tz'] = logged_user()->getTimezone() * 3600;

		$ot = $tmp_task->getOpenTimeslots();

		if ($ot){
			$users = array();
			$time = array();
			$paused = array();
			foreach ($ot as $t){
				if (!$t instanceof Timeslot) continue;
				$time[] = $t->getSeconds();
				$users[] = $t->getContactId();
				$paused[] = $t->isPaused()?1:0;
				if ($t->isPaused() && $t->getContactId() == logged_user()->getId()) {
					$result['wpt'] = $t->getPausedOn()->getTimestamp();
				}
			}
			$result['wt'] = $time;
			$result['wid'] = $users;
			$result['wp'] = $paused;
		}

		if ($raw_data['repeat_forever'] > 0 || $raw_data['repeat_num'] > 0 || $raw_data['repeat_end'] != EMPTY_DATETIME) {
			$result['rep'] = 1;
		}
		
		return $result;
	}
	
} // TemplateTasks
