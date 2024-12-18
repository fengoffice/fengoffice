<?php

/**
 * ProjectTasks, generated on Sat, 04 Mar 2006 12:50:11 +0100 by
 * DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ProjectTasks extends BaseProjectTasks {

	function __construct() {
		parent::__construct();
		$this->object_type_name = 'task';
	}
	
	const ORDER_BY_ORDER = 'order';
	const ORDER_BY_STARTDATE = 'startDate';
	const ORDER_BY_DUEDATE = 'dueDate';
	const PRIORITY_URGENT = 400;
	const PRIORITY_HIGH = 300;
	const PRIORITY_NORMAL = 200;
	const PRIORITY_LOW = 100;
	
	private static $custom_properties = null;

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
		
		$tasks = array();
		foreach ($result->objects as $task) {
			if ($task->canView(logged_user())) {
				$tasks[] = $task;
			}
		}
		
		return $tasks;
	}
	
	/**
	 * Returns all task templates
	 *
	 */
	function getAllTaskTemplates($only_parent_task_templates = false, $archived = false) {
		if ($archived)
			$archived_cond = "AND `archived_on` <> 0";
		else
			$archived_cond = "AND `archived_on` = 0";
		
		$conditions = " `is_template` = true $archived_cond";
		if ($only_parent_task_templates)
			$conditions .= "  and `parent_id` = 0  ";
		$order_by = "`title` ASC";
		$tasks = ProjectTasks::instance()->find( array ('conditions' => $conditions, 'order' => $order_by ) );
		if (! is_array ( $tasks ))
			$tasks = array ();
		return $tasks;
	}
	
	static function maxOrder($parentId = null, $milestoneId = null) {
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
			FROM `" . TABLE_PREFIX . "project_tasks` t  
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
	 * @param DateTimeValue $date_start in user gmt
	 * @param DateTimeValue $date_end	in user gmt 
	 * @return array
	 */
	static function getRangeTasksByUser(DateTimeValue $date_start, DateTimeValue $date_end, $assignedUser, $task_filter = null, $archived = false, $raw_data = false, $limit = 50) {
		
		$from_date = new DateTimeValue ( $date_start->getTimestamp ());
		$from_date = $from_date->beginningOfDay ();
		$to_date = new DateTimeValue ( $date_end->getTimestamp ());
		$to_date = $to_date->endOfDay ();
		
		$orig_from_date = new DateTimeValue($from_date->getTimestamp());
		$orig_from_date_sql = $orig_from_date->toMySQL();
		$orig_to_date = new DateTimeValue($to_date->getTimestamp());
		$orig_to_date_sql = $orig_to_date->toMySQL();
		
		//set dates to gmt 0 for sql
		$from_date->advance(-logged_user()->getUserTimezoneValue());
		$to_date->advance(-logged_user()->getUserTimezoneValue());	
			
		$assignedFilter = '';
		if ($assignedUser instanceof Contact) {
			$assignedFilter = ' AND (`assigned_to_contact_id` = ' . $assignedUser->getId () . ' OR `assigned_to_contact_id` = \'' . $assignedUser->getCompanyId () . '\') ';
		}
		$rep_condition = " (`repeat_forever` = 1 OR `repeat_num` > 0 OR (`repeat_end` > 0 AND `repeat_end` >= '" . $from_date->toMySQL () . "')) ";
		
		$archived_cond = " AND `archived_on` ".($archived ? '<>' : '=')." 0";
		
		$completed_cond = "";
		if ($task_filter == 'complete') {
			$completed_cond = "AND `completed_on` <> '".EMPTY_DATETIME."'";
		} else if ($task_filter == 'pending') {
			$completed_cond = "AND `completed_on` = '".EMPTY_DATETIME."'";
		}
		
		$conditions = DB::prepareString(' AND `is_template` = false '.$completed_cond.' AND 
			(IF (due_date>0, 
				IF (use_due_time, (`due_date` >= ? AND `due_date` < ?), (`due_date` >= \''.$orig_from_date_sql.'\' AND `due_date` < \''.$orig_to_date_sql.'\')), false) 
			OR IF (start_date>0, 
				IF (use_start_time, (`start_date` >= ? AND `start_date` < ?), (`start_date` >= \''.$orig_from_date_sql.'\' AND `start_date` < \''.$orig_to_date_sql.'\')), false)
			OR ' . $rep_condition . ') ' . $archived_cond . $assignedFilter, 
			array($from_date, $to_date, $from_date, $to_date)
		);
		
		$other_perm_conditions = SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks');
		if(!$other_perm_conditions){
			$conditions = " AND (`assigned_to_contact_id` = ". logged_user()->getId () ." OR o.`created_by_id` = ". logged_user()->getId () .")";
		}
		
		$listing_params = array(
			"extra_conditions" => $conditions,
			"raw_data" => $raw_data,
		);
		if ($limit) {
			$listing_params["limit"] = $limit;
		}
		$result = self::instance()->listing($listing_params);
		
		return $result->objects;
	} // getDayTasksByUser
	

	/**
	 * Returns an unsaved copy of the task. Copies everything except open/closed state,
	 * anything that needs the task to have an id (like tags, properties, subtask),
	 * administrative info like who created the task and when, etc.
	 *
	 * @param ProjectTask $task
	 * @return ProjectTask
	 */
	static function createTaskCopy(ProjectTask $task) {
		$new = new ProjectTask ();
		$new->setMilestoneId ( $task->getMilestoneId () );
		$new->setParentId ( $task->getParentId () );
		$new->setObjectName($task->getObjectName()) ;
		$new->setAssignedToContactId ( $task->getAssignedToContactId () );
		$new->setPriority ( $task->getPriority () );
		$new->setTimeEstimate ( $task->getTimeEstimate () );
		$new->setText ( $task->getText () );
		$new->setOrder ( ProjectTasks::maxOrder ( $new->getParentId (), $new->getMilestoneId () ) );
		$new->setStartDate ( $task->getStartDate () );
		$new->setDueDate ( $task->getDueDate () );
		return $new;
	}
	
	/**
	 * Copies subtasks from taskFrom to taskTo.
	 *
	 * @param ProjectTask $taskFrom
	 * @param ProjectTask $taskTo
	 */
	static function copySubTasks(ProjectTask $taskFrom, ProjectTask $taskTo, $as_template = false) {
		foreach ( $taskFrom->getSubTasks () as $sub ) {
			if ($sub->getId() == $taskTo->getId()) continue;
			$new = ProjectTasks::createTaskCopy ( $sub );
			$new->setParentId ( $taskTo->getId () );
			$new->setMilestoneId ( $taskTo->getMilestoneId () );
			$new->setOrder ( ProjectTasks::maxOrder ( $new->getParentId (), $new->getMilestoneId () ) );
			
			$new->save ();
			
			$object_controller = new ObjectController();
			if (count($taskFrom->getMemberIds())) {
				$object_controller->add_to_members($new, $taskFrom->getMemberIds());
			}
			$new->copyCustomPropertiesFrom ( $sub );
			$new->copyLinkedObjectsFrom ( $sub );
			ProjectTasks::copySubTasks ( $sub, $new, $as_template );
		}
	}
	
	static function getUpcomingWithoutDate($limit = null, $user_id = null) {
		$conditions = " AND is_template = 0 AND `e`.`completed_by_id` = 0 AND `e`.`due_date` = '0000-00-00 00:00:00' " ;
		
		if (!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks')) {
			$conditions .= " AND assigned_to_contact_id = ".logged_user()->getId();
		} else {
			$user = Contacts::instance()->findById($user_id);
			if ($user instanceof Contact) {
				$conditions .= " AND assigned_to_contact_id = ".$user->getId();
			}
		}
		
		$tasks_result = self::instance()->listing(array(
			"start"=> 0,
			"limit"=>$limit, 
			"extra_conditions"=>$conditions, 
			"fire_additional_data_hook" => false,
			"order"=>  array('due_date', 'priority') , 
			"order_dir" => "ASC"
		));
		return $tasks_result->objects;
	}


	static function getOverdueAndUpcomingObjects($limit = null, $user_id = null) {
		$conditions_tasks = " AND is_template = 0 AND `e`.`completed_by_id` = 0 AND `e`.`due_date` > 0";
		$conditions_milestones = " AND is_template = 0 AND `e`.`completed_by_id` = 0 AND `e`.`due_date` > 0";
		
		if (!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks')) {
			$conditions_tasks .= " AND assigned_to_contact_id = ".logged_user()->getId();
		} else {
			$user = Contacts::instance()->findById($user_id);
			if ($user instanceof Contact) {
				$conditions_tasks .= " AND assigned_to_contact_id = ".$user->getId();
			}
		}
		
		$tasks_result = self::instance()->listing(array(
			"limit" => $limit, 
			"extra_conditions" => $conditions_tasks, 
			"fire_additional_data_hook" => false,
			"order"=>  array('due_date', 'priority'), 
			"order_dir" => "ASC"
		));
		$tasks = $tasks_result->objects;
		
		if ($user_id == null) {
			$milestones_result = ProjectMilestones::instance()->listing(array(
				"limit" => $limit, 
				"extra_conditions" => $conditions_milestones, 
				"fire_additional_data_hook" => false,
				"order" => array('due_date'), 
				"order_dir" => "ASC"
			));
			$milestones = $milestones_result->objects;
		} else {
			$milestones = array();
		}
		
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
	function findByContext( $options = array () ) {
		// Initialize method result
		$result = new stdClass();
		$result->total = 0 ;
		$result->objects = array() ;
		
		// Read arguments and Init Vars
		$limit = array_var($options,'limit');
		$members = active_context_members(false); // 70
		$type_id = self::instance()->getObjectTypeId();
		if (!count($members)) return $result ; 
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
    		$task =  ProjectTasks::instance()->findById($row['id']);
    		if ($task && $task instanceof ProjectTask) {
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
			
			$db_res = DB::execute("SELECT object_id, original_task_id FROM ".TABLE_PREFIX."project_tasks WHERE $obj_cond");
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
			return self::instance()->findAll(array('conditions' => 'object_id IN ('.implode(',', $related).')'));
		}
		return array();
	}

	static function findByRelated($task_id) {
		return ProjectTasks::instance()->findAll(array('conditions' => array('`original_task_id` = ?', $task_id)));
	}

	static function findByTaskAndRelated($task_id,$original_task_id) {
		return ProjectTasks::instance()->findAll(array('conditions' => array('(`original_task_id` = ? OR `object_id` = ?) AND `object_id` <> ?', $original_task_id,$original_task_id,$task_id)));
	}
	
	
	static function getArrayInfo($raw_data, $full = false, $include_members_data = false, $include_mem_path = true, $include_open_timeslots = true, $include_subtasks_ids = true){
		$desc = isset($raw_data['text']) ? $raw_data['text'] : '';
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
		$tmp_task = new ProjectTask();
		$tmp_task->setObjectId($raw_data['id']);
		$tmp_task->setId($raw_data['id']);
		$tmp_task->setAssignedToContactId($raw_data['assigned_to_contact_id']);
		
		
		$result = array(
			'id' => (int)$raw_data['id'],
			'name' => $raw_data['name'],
			'description' => $desc,
			'members' => $member_ids,
			'createdOn' => strtotime($raw_data['created_on']),
			'createdById' => (int)$raw_data['created_by_id'],
			'otype' => $raw_data['object_subtype'],
			'percentCompleted' => (int)$raw_data['percent_completed'],			
		//	'memPath' => str_replace('"',"'", escape_character(json_encode($tmp_task->getMembersIdsToDisplayPath())))
		);
		if ($include_mem_path && count($member_ids) > 0) {
			$result['memPath'] = str_replace('"',"'", escape_character(json_encode($tmp_task->getMembersIdsToDisplayPath())));
		}
		if ($include_members_data && count($member_ids) > 0) {
			$task_members = array();
			foreach ($member_ids as $member_id) $task_members[] = Members::getMemberById($member_id); // uses cache
			//$task_members = Members::instance()->findAll(array("conditions" => "id IN (".implode(',', $member_ids).")"));
			$task_members = array_filter($task_members);
			$members_data = array();
			foreach ($task_members as $m) {
				/* @var $m Member */
				$m_data = array(
						'id' => $m->getId(),
						'name' => $m->getName(),
						'dimension_id' => $m->getDimensionId()
				);
				$m_ot = ObjectTypes::instance()->findById($m->getObjectTypeId());
				if ($m_ot instanceof ObjectType) {
					$m_data['object_type_name'] = $m_ot->getName();
				}
				$members_data[] = $m_data;
			}
			$result['members_data'] = $members_data;
		}
		
		if(isset($raw_data['isread'])){
			$result['isread'] = $raw_data['isread'];
		}
		
		if(isset($raw_data['mark_as_started'])){
		    $result['mark_as_started'] = $raw_data['mark_as_started'] ? "1" : "0";
		}

		$result['multiAssignment'] = (int)array_var($raw_data, 'multi_assignment');
			
		if ($raw_data['completed_by_id'] > 0) {
			$result['status'] = 1;
		}
			
		if ($raw_data['parent_id'] > 0) {
			$result['parentId'] = (int)$raw_data['parent_id'];
		}
		
		if ($include_subtasks_ids) {
			$result['subtasksIds'] = $tmp_task->getSubTasksIds();
		}
				
		//if ($this->getPriority() != 200)
		$result['priority'] = (int)$raw_data['priority'];

		if ($raw_data['milestone_id'] > 0) {
			$result['milestoneId'] = (int)$raw_data['milestone_id'];
		}
		
		if ($raw_data['assigned_by_id'] > 0) {
			$result['assignedById'] = (int)$raw_data['assigned_by_id'];
		}
			
		if ($raw_data['assigned_to_contact_id'] > 0) {
			$result['assignedToContactId'] = (int)$raw_data['assigned_to_contact_id'];
		}
		$result['atName'] = $tmp_task->getAssignedToName();

		if ($raw_data['completed_by_id'] > 0) {
			$result['completedById'] = (int)$raw_data['completed_by_id'];
			$result['completedOn'] = strtotime($raw_data['completed_on']);;
		}
			
		if ($raw_data['due_date'] != EMPTY_DATETIME) {
			$result['useDueTime'] = $raw_data['use_due_time'] ? 1 : 0;
			if($result['useDueTime']){
				$result['dueDate'] = strtotime($raw_data['due_date']) + Timezones::getTimezoneOffsetToApplyFromArray($raw_data);
			}else{
				$result['dueDate'] = strtotime($raw_data['due_date']);
			}			
		}
		if ($raw_data['start_date'] != EMPTY_DATETIME) {
			$result['useStartTime'] = $raw_data['use_start_time'] ? 1 : 0;			
			if($result['useStartTime']){
				$result['startDate'] = strtotime($raw_data['start_date']) + Timezones::getTimezoneOffsetToApplyFromArray($raw_data);
			}else{
				$result['startDate'] = strtotime($raw_data['start_date']);
			}			
		}

		$time_estimate = $raw_data['time_estimate'];
		$result['timeEstimate'] = $raw_data['time_estimate'];
		if ($time_estimate > 0) $result['timeEstimateString'] = str_replace(',',',<br>',DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($time_estimate * 60), 'hm', 60));

		$total_time_estimate = $raw_data['total_time_estimate'];
		if($total_time_estimate > 0) {
			$result['totalTimeEstimate'] = $total_time_estimate;
			$result['totalTimeEstimateString'] = str_replace(',',',<br>',DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($total_time_estimate * 60), 'hm', 60));
		} else {
			$result['totalTimeEstimate'] = 0;
		}

		$result['timeZone'] = Timezones::getTimezoneOffsetToApplyFromArray($raw_data);

		if ($include_open_timeslots) {
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
						$result['pauseTime'] = $t->getPausedOn()->getTimestamp();
					}
				}
				$result['workingOnTimes'] = $time;
				$result['workingOnIds'] = $users;
				$result['workingOnPauses'] = $paused;
			}
		}
				
		//$total_minutes = $tmp_task->getTotalMinutes();
		$total_minutes = $raw_data['total_worked_time'];
		
		if ($total_minutes > 0){
			$result['worked_time'] = $total_minutes;
			$result['worked_time_string'] = str_replace(',',',<br>',DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($total_minutes * 60), 'hm', 60));
		}else{
			$result['worked_time'] = 0;
		}

		//Logger::log_r($raw_data);
		$overall_worked_minutes = $raw_data['overall_worked_time_plus_subtasks'];
		if($overall_worked_minutes > 0){
			$result['overall_worked_time_plus_subtasks'] = $overall_worked_minutes;
			$result['overall_worked_time'] = $overall_worked_minutes;
			$result['overall_worked_time_string'] = str_replace(',',',<br>',DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($overall_worked_minutes * 60), 'hm', 60));
		}else{
			$result['overall_worked_time_plus_subtasks'] = $overall_worked_minutes;
			$result['overall_worked_time'] = 0;
		}

		// Remaining time
		$remaining_time = $raw_data['remaining_time'];
		if ($remaining_time != 0){
			$result['remaining_time'] = $remaining_time;
			$result['remaining_time_string'] = str_replace(',',',<br>',DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($remaining_time * 60), 'hm', 60));
		}else{
			$result['remaining_time'] = 0;
		}

		// Total remaining time
		$total_remaining_time = $raw_data['total_remaining_time'];
		if ($total_remaining_time != 0){
			$result['total_remaining_time'] = $total_remaining_time;
			$result['total_remaining_time_string'] = str_replace(',',',<br>',DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($total_remaining_time * 60), 'hm', 60));
		}else{
			$result['total_remaining_time'] = 0;
		}


		// Pending time
		$pending_time = $time_estimate - $total_minutes;		
		if ($pending_time > 0){
			$result['pending_time'] = $pending_time;
			$result['pending_time_string'] = str_replace(',',',<br>',DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($pending_time * 60), 'hm', 60));
		}else{
			$result['pending_time'] = 0;
		}
		
		if ($raw_data['repeat_forever'] > 0 || $raw_data['repeat_num'] > 0 || ($raw_data['repeat_end'] != EMPTY_DATETIME && $raw_data['repeat_end'] != '')) {
			$result['repetitive'] = 1;
		}
		
		$tmp_members = array();
		if (count($member_ids) > 0) {
			//$tmp_members = Members::instance()->findAll(array("conditions" => "id IN (".implode(',', $member_ids).")"));
			foreach ($member_ids as $member_id) $tmp_members[] = Members::getMemberById($member_id); // uses cache
		}
		$result['can_add_timeslots'] = can_add_timeslots(logged_user(), $tmp_members);
		
		//tasks dependencies
		if (config_option('use tasks dependencies')) {
			//get all dependant tasks ids, not completed yet
			$pending_tasks_ids = ProjectTaskDependencies::getDependenciesForTaskOnlyPendingIds($tmp_task->getId());
			
			//get the total of previous tasks 
			$result['dependants'] = $pending_tasks_ids;	
			
			$result['previous_tasks_total'] = ProjectTaskDependencies::countPendingPreviousTasks($tmp_task->getId());	
		}
		
		$cp_values = array();
		if (is_null(self::$custom_properties)) {
			self::$custom_properties = CustomProperties::getAllCustomPropertiesByObjectType(self::instance()->getObjectTypeId());
		}
		foreach (self::$custom_properties as $cp) {
			$cp_values[] = array(
					'id' => $cp->getId(),
					'value' => get_custom_property_value_for_listing($cp, $result['id']),
			);
		}
		$result['custom_properties'] = $cp_values;
		
		Hook::fire('task_info_additional_data', $raw_data, $result);
		
		return $result;
	}
	
	static function getLastRepetitiveTaskId($task_id) {
	    
	    $db_res = DB::execute("SELECT original_task_id FROM `" . TABLE_PREFIX . "project_tasks` WHERE object_id = $task_id");
	    $rows = $db_res->fetchAll();
	    if (is_array($rows)) {
	        if ($rows[0]['original_task_id'] > 0){
                $db_res = DB::execute("SELECT MAX(object_id) as object_id FROM `" . TABLE_PREFIX . "project_tasks` WHERE original_task_id = ".$rows[0]['original_task_id'] );
	        }else{
	            $db_res = DB::execute("SELECT MAX(object_id) as object_id FROM `" . TABLE_PREFIX . "project_tasks` WHERE original_task_id = ".$task_id );
	        }
	        $rows = $db_res->fetchAll();
	        if (is_array($rows)) {
	            return (isset($rows[0]['object_id']) && $rows[0]['object_id'] > 0 ? $rows[0]['object_id'] : null);
	        }
	      
	    }
	   
	    return null;
	}

	function getColumnsToAggregateInTotals() {
		$parent_cols = parent::getColumnsToAggregateInTotals();
		$cols = array(
			'time_estimate' => array('operation' => 'sum', 'format' => 'time'),
			'total_worked_time' => array('operation' => 'sum', 'format' => 'time'),
			'remaining_time' => array('operation' => 'sum', 'format' => 'time'),
		);

		return array_merge($parent_cols, $cols);
	}

	static function checkTaskInTemplate($task, $template)
	{
		if (Plugins::instance()->isActivePlugin('advanced_billing') && ($task instanceof ProjectTask || $task instanceof TemplateTask))
		{
		    $project_ot=ObjectTypes::findByName('project');
			$project_member = null;
			$invoice_template = null;

		    $task_members = $task->getMembers();
		    foreach($task_members as $task_member)
		    {
			    if($task_member->getObjectTypeId()==$project_ot->getId())
			    {
				    $project_member = $task_member;
			    }
		    }
			
    	    if ($project_member instanceof Member) {
    		    $cp = CustomProperties::getCustomPropertyByCode($project_ot->getId(), 'invoice_template');
    		    $cp_val = CustomPropertyValues::getCustomPropertyValue($project_member->getObjectId(), $cp->getId());
    		    if ($cp_val)
			    {
			        $invoice_template = IncomeInvoiceTemplates::instance()->findById($cp_val->getValue());
			    }
		    }

			if($invoice_template)
			{
			    if($invoice_template->getTemplateType()==$template)
			    {
				    return true;
			    }
			}
		}
		return false;
	}
	

	/**
	 * Recursive function to get all the subtask hierarchy of a set of tasks
	 * Makes one query per level, much more efficient than getting each task object and get all its subtasks
	 * @param array $task_ids
	 * @param int $level
	 * @return array The subtasks array
	 */
	static function getAllSubtasksIdsBulk($task_ids, $level=0) {
		$all_subtask_ids = array();

		if ($level > 25) return;
		if (!isset($task_ids) || count($task_ids) == 0) return $all_subtask_ids;

		if (count($task_ids) > 0) {
			$rows = DB::executeAll("
				SELECT object_id 
				FROM ".TABLE_PREFIX."project_tasks 
				WHERE parent_id IN(".implode(',',$task_ids).")
			");
			$current_level_subtasks = array_filter(array_flat($rows));
		} else {
			$current_level_subtasks = array();
		}

		if (isset($current_level_subtasks) && is_array($current_level_subtasks) && count($current_level_subtasks) > 0) {

			$next_levels_subtasks = self::getAllSubtasksIdsBulk($current_level_subtasks, $level+1);
			if (count($next_levels_subtasks) > 0) {
				$all_subtask_ids = array_merge($all_subtask_ids, $next_levels_subtasks);
			}
			
			$all_subtask_ids =  array_merge($all_subtask_ids, $current_level_subtasks);
		}

		return array_unique($all_subtask_ids);
	}
	
} // ProjectTasks
