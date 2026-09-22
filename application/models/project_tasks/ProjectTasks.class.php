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
	private static $_subtask_ids_cache = array();
	private static $_visible_cp_ids = null;

	/**
	 * Returns an array of object columns that are available to be shown in the custom properties form.
	 * This extends the function in the parent class.
	 * 
	 * @access protected
	 * @return array Array of object columns available to be shown in the custom properties form.
	 */
	function getColumnsAvailableInForms() {
		$columns = [
			'assigned_to_contact_id',
			'start_date',
			'due_date',
			'time_estimate',
			'percent_completed',
			'priority',
			'parent_id',
			'is_billable',
			'previous_task_id',
		];

		// milestones are an optional feature: the column is always offered so property groups can hold
		// it, and its form input renders nothing while use_milestones is off (like previous_task_id)
		$columns[] = 'milestone_id';

		return $columns;
	}

	/**
	 * Returns an array of external columns that are available to be shown in the custom properties form.
	 * These columns are not part of the object itself, but are related to it in some way.
	 * For example, a task can have a property that shows the name of the previous task.
	 * 
	 * @return array Array of external columns available to be shown in the custom properties form.
	 */
	public function getExternalColumnsForCustomPropertiesForm() {
		return ['previous_task_id'];
	}

	/**
	 * Whether the class can use property groups.
	 * 
	 * @return bool true if the class can use property groups, false otherwise.
	 */
	function canUsePropertyGroups() {
		return true;
	}

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
	 * Return subtasks of the given parent tasks, for calendar rendering.
	 * Subtasks are returned regardless of whether they have their own start/due date;
	 * the calendar view decides where to place them (own date slot, or anchored to the parent's day).
	 *
	 * @access public
	 * @param array $parent_ids
	 * @param string $task_filter 'pending', 'complete' or null
	 * @param bool $archived
	 * @return array
	 */
	static function getSubtasksForCalendar(array $parent_ids, $task_filter = null, $archived = false) {
		$parent_ids = array_filter(array_unique(array_map('intval', $parent_ids)));
		if (empty($parent_ids)) return array();

		$archived_cond = " AND `archived_on` ".($archived ? '<>' : '=')." 0";

		$completed_cond = "";
		if ($task_filter == 'complete') {
			$completed_cond = "AND `completed_on` <> '".EMPTY_DATETIME."'";
		} else if ($task_filter == 'pending') {
			$completed_cond = "AND `completed_on` = '".EMPTY_DATETIME."'";
		}

		$conditions = " AND `is_template` = false ".$completed_cond." AND `parent_id` IN (".implode(',', $parent_ids).")".$archived_cond;

		if (!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks')) {
			$conditions .= " AND (`assigned_to_contact_id` = ". logged_user()->getId () ." OR o.`created_by_id` = ". logged_user()->getId () .")";
		}

		$order_args = self::getSubtasksListingOrderArgs();
		$result = self::instance()->listing(array(
			"extra_conditions" => $conditions,
			"order" => $order_args['order'],
			"order_dir" => $order_args['order_dir'],
			"join_params" => $order_args['join_params'],
		));

		return $result->objects;
	} // getSubtasksForCalendar


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
	 * Creates and returns a temporary task object based on a template task, to be used when rendering the template task form.
	 * The returned object is not saved and has the same attribute values as the template task, but with the object type set to task and the id set to the template task id (to be used in the form as a reference to the template task).
	 * @param TemplateTask $task
	 * @return ProjectTask
	 */
	static function createTmpTaskCopyFromTemplateTask(TemplateTask $task) {
		// create a new temporary task object
		$tmp_task = new ProjectTask();
		// copy all columns
		$tmp_task->setFromAttributes($task->getAllAttributeValues());
		// set the id, so the form can reference the template task for custom property values
		$tmp_task->getObject()->setId($task->getId());
		// set the object subtype so the form can render the correct custom properties and groups
		$tmp_task->getObject()->setColumnValue('object_subtype_id', $task->getObjectSubtypeId());
		// set the object type as 'task' so the form can render the identical properties form as for a normal task
		$tmp_task->setObjectTypeId(ProjectTasks::instance()->getObjectTypeId());
		// set the member ids and members so the form can render the members field with the correct values
		$tmp_task->setMemberIds($task->getMemberIds());
		$tmp_task->setMembers($task->getMembers());

		// return the temporary task object
		return $tmp_task;
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
	
	
	/**
	 * Pre-fetch subtask IDs for a set of parent task IDs in a single listing query.
	 * Results are stored in $_subtask_ids_cache and used by ProjectTask::getSubTasksIds().
	 *
	 * @param array $parent_ids
	 * @return void
	 */
	static function prefetchSubtaskIds(array $parent_ids) {
		if (empty($parent_ids)) return;

		$to_fetch = array();
		foreach ($parent_ids as $id) {
			$id = (int) $id;
			if (!array_key_exists($id, self::$_subtask_ids_cache)) {
				self::$_subtask_ids_cache[$id] = array();
				$to_fetch[] = $id;
			}
		}
		if (empty($to_fetch)) return;

		// Exclude trashed subtasks so the prefetched cache matches ProjectTask::getSubTasksIds().
		// trashed_by_id lives on the objects table (alias `o`), not the entity table (`e`).
		$condition = ' AND e.`parent_id` IN (' . implode(',', $to_fetch) . ') AND o.`trashed_by_id` = 0';
		if (!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks')) {
			$condition .= ' AND e.`assigned_to_contact_id` = ' . logged_user()->getId();
		}

		$order_args = self::getSubtasksListingOrderArgs();
		$rows = self::instance()->listing(array(
			// DISTINCT avoids duplicate IDs when ordering by dim_* (object_members join can multiply rows).
			'select_columns' => array('DISTINCT e.`object_id`', 'e.`parent_id`'),
			'extra_conditions' => $condition,
			'count_results' => false,
			'fire_additional_data_hook' => false,
			'raw_data' => true,
			'order' => $order_args['order'],
			'order_dir' => $order_args['order_dir'],
			'join_params' => $order_args['join_params'],
		))->objects;

		if (is_array($rows)) {
			foreach ($rows as $row) {
				self::$_subtask_ids_cache[(int) $row['parent_id']][] = (int) $row['object_id'];
			}
		}
	}

	/**
	 * Listing order args for nested subtasks, driven by user preference
	 * tasksOrderSubtasksWithFilterCriteria.
	 *
	 * YES: same order criteria as the tasks list filter (tasksOrderBy / tasksListingOrder).
	 * NO: order by name ASC.
	 *
	 * @return array{order: mixed, order_dir: string, join_params: array|null}
	 */
	static function getSubtasksListingOrderArgs() {
		if (!user_config_option('tasksOrderSubtasksWithFilterCriteria', 1)) {
			return array(
				'order' => 'o.name',
				'order_dir' => 'ASC',
				'join_params' => null,
			);
		}

		$order_by = user_config_option('tasksOrderBy');
		$order_dir = user_config_option('tasksListingOrder');
		$original_order = $order_by;
		$join_params = null;

		switch ($order_by) {
			case 'name':
				break;
			case 'assigned_to':
				$order_by = 'assigned_to_contact_id';
				break;
			case 'created_on':
			case 'due_date':
			case 'start_date':
			case 'completed_on':
				$order_by = "($order_by='0000-00-00 00:00:00'), $order_by";
				break;
		}

		if ($order_by == 'assigned_to_contact_id') {
			$join_params = array(
				'join_type' => 'LEFT ',
				'table' => TABLE_PREFIX . 'objects',
				'jt_field' => 'id',
				'e_field' => 'assigned_to_contact_id',
			);
			$order_by = 'jt.name';
		}

		if (str_starts_with($order_by, 'dim_')) {
			$exploded = explode('_', $order_by);
			$order_dim_id = $exploded[1];
			$order_ot_id = $exploded[2];

			$join_params = array(
				'join_type' => 'LEFT ',
				'table' => TABLE_PREFIX . 'object_members',
				'jt_field' => 'object_id',
				'e_field' => 'object_id',
				'on_extra' => " LEFT JOIN `" . TABLE_PREFIX . "members` `mem_order` ON `mem_order`.`id`=`jt`.`member_id` AND `mem_order`.`dimension_id` = $order_dim_id AND `mem_order`.`object_type_id` = $order_ot_id",
			);
			$order_by = 'mem_order.display_name';
		}

		$hook_order_result = null;
		Hook::fire('override_tasks_list_order_by', array('order' => $order_by, 'join_params' => $join_params), $hook_order_result);
		if (is_array($hook_order_result)) {
			if ($hook_order_result['order']) {
				$order_by = $hook_order_result['order'];
				if ($hook_order_result['order_dir']) $order_dir = $hook_order_result['order_dir'];
			}
			if ($hook_order_result['join_params']) {
				$join_params = $hook_order_result['join_params'];
			}
		}

		// Keep ties stable by name when order is still scalar.
		// Do not wrap again if the override_tasks_list_order_by hook already returned an array
		// (e.g. cp_ orders from advanced_core already include their own name tie-break).
		if ($original_order == 'name') {
			$order_by = 'o.name';
		} else if (!is_array($order_by)) {
			$order_by = array($order_by, array('col' => 'o.name', 'dir' => 'ASC'));
		}

		return array(
			'order' => $order_by,
			'order_dir' => $order_dir,
			'join_params' => $join_params,
		);
	}

	/**
	 * Batch pre-warms the ObjectMembers::getMembersIdsByObjectAndExtraCond cache for
	 * all dimensions that show in breadcrumb paths, covering a batch of task IDs.
	 * Call this before a loop over getArrayInfo() to eliminate N+1 member queries.
	 *
	 * @param array $task_ids
	 */
	static function prefetchMembersForListing(array $task_ids) {
		if (empty($task_ids)) return;
		$ot_id     = self::instance()->getObjectTypeId();
		$dimensions = Dimensions::getAllowedDimensions($ot_id);
		foreach ($dimensions as $dimension) {
			$dim = Dimensions::getDimensionById($dimension['dimension_id']);
			if (!intval($dim->getOptionValue('showInPaths'))) continue;
			$extra_cond = " AND m.dimension_id = " . (int) $dimension['dimension_id'];
			// use_contact_member_cache=false matches the call inside getMembersIdsToDisplayPath()
			ObjectMembers::prefetchMembersForObjects($task_ids, $extra_cond, "", false);
		}
	}

	/**
	 * Return prefetched subtask IDs for a parent task, or null if not yet prefetched.
	 *
	 * @param int $parent_id
	 * @return array|null
	 */
	static function getCachedSubtaskIds($parent_id) {
		return array_key_exists($parent_id, self::$_subtask_ids_cache) ? self::$_subtask_ids_cache[$parent_id] : null;
	}

	static function getArrayInfo($raw_data, $full = false, $include_members_data = false, $include_mem_path = true, $include_open_timeslots = true, $include_subtasks_ids = true, $include_description = true, $subtasks_extra_conditions = ''){
		$text = isset($raw_data['text']) ? $raw_data['text'] : '';
		$type_content = isset($raw_data['type_content']) ? $raw_data['type_content'] : 'text';
		$task_id = isset($raw_data['id']) ? $raw_data['id'] : (isset($raw_data['object_id']) ? $raw_data['object_id'] : 0);
		$tz_offset = Timezones::getTimezoneOffsetToApplyFromArray($raw_data);
		$zero_dt = new DateTimeValue(0);

		$desc = $include_description ? $text : ''; // fallback when $full is false
		if ($full && $include_description) {
			if(config_option("wysiwyg_tasks")){
				if($type_content == "text"){
					$desc = nl2br(htmlspecialchars($text));
				}else{
					$desc = purify_html($text);
				}
			}else{
				if($type_content == "text"){
					$desc = htmlspecialchars($text);
				}else{
					$desc = html_to_text(html_entity_decode(nl2br($text), null, "UTF-8"));
				}
			}
		}

		$member_ids = ($task_id > 0) ? ObjectMembers::instance()->getCachedObjectMembers($task_id) : array();
		$tmp_task = new ProjectTask();
		$tmp_task->setObjectId($task_id);
		$tmp_task->setId($task_id);
		$tmp_task->setAssignedToContactId(isset($raw_data['assigned_to_contact_id']) ? $raw_data['assigned_to_contact_id'] : 0);
		
		
		$result = array(
			'id' => (int)$task_id,
			'name' => isset($raw_data['name']) ? $raw_data['name'] : '',
			'description' => $desc,
			'members' => $member_ids,
			'createdOn' => isset($raw_data['created_on']) ? strtotime($raw_data['created_on']) : 0,
			'createdById' => isset($raw_data['created_by_id']) ? (int)$raw_data['created_by_id'] : 0,
			// The real object subtype lives in fo_objects.object_subtype_id (added by the object_subtypes plugin)
			// The legacy fo_project_tasks.object_subtype column is effectively unused (0),
			// so prefer object_subtype_id and only fall back to the legacy column when the plugin is inactive.
			'otype' => (isset($raw_data['object_subtype_id']) && $raw_data['object_subtype_id'] !== null && $raw_data['object_subtype_id'] !== '')
				? (int)$raw_data['object_subtype_id']
				: (isset($raw_data['object_subtype']) ? (int)$raw_data['object_subtype'] : 0),
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
						'dimension_id' => $m->getDimensionId(),
						'object_type_id' => $m->getObjectTypeId()
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
			$result['subtasksIds'] = $tmp_task->getSubTasksIds($subtasks_extra_conditions);
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
				$result['dueDate'] = strtotime($raw_data['due_date']) + $tz_offset;
			}else{
				$result['dueDate'] = strtotime($raw_data['due_date']);
			}
		}
		if ($raw_data['start_date'] != EMPTY_DATETIME) {
			$result['useStartTime'] = $raw_data['use_start_time'] ? 1 : 0;
			if($result['useStartTime']){
				$result['startDate'] = strtotime($raw_data['start_date']) + $tz_offset;
			}else{
				$result['startDate'] = strtotime($raw_data['start_date']);
			}
		}

		$time_estimate = $raw_data['time_estimate'];
		$result['timeEstimate'] = $raw_data['time_estimate'];
		if ($time_estimate > 0) $result['timeEstimateString'] = str_replace(',',',<br>',DateTimeValue::FormatTimeDiff($zero_dt, new DateTimeValue($time_estimate * 60), 'hm', 60));

		$total_time_estimate = $raw_data['total_time_estimate'];
		if($total_time_estimate > 0) {
			$result['totalTimeEstimate'] = $total_time_estimate;
			$result['totalTimeEstimateString'] = str_replace(',',',<br>',DateTimeValue::FormatTimeDiff($zero_dt, new DateTimeValue($total_time_estimate * 60), 'hm', 60));
		} else {
			$result['totalTimeEstimate'] = 0;
		}

		$result['timeZone'] = $tz_offset;

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
			$result['worked_time_string'] = str_replace(',',',<br>',DateTimeValue::FormatTimeDiff($zero_dt, new DateTimeValue($total_minutes * 60), 'hm', 60));
		}else{
			$result['worked_time'] = 0;
		}

		//Logger::log_r($raw_data);
		$overall_worked_minutes = $raw_data['overall_worked_time_plus_subtasks'];
		if($overall_worked_minutes > 0){
			$result['overall_worked_time_plus_subtasks'] = $overall_worked_minutes;
			$result['overall_worked_time'] = $overall_worked_minutes;
			$result['overall_worked_time_string'] = str_replace(',',',<br>',DateTimeValue::FormatTimeDiff($zero_dt, new DateTimeValue($overall_worked_minutes * 60), 'hm', 60));
		}else{
			$result['overall_worked_time_plus_subtasks'] = $overall_worked_minutes;
			$result['overall_worked_time'] = 0;
		}

		// Remaining time
		$remaining_time = max((int) $raw_data['remaining_time'], 0);
		if ($remaining_time != 0){
			$result['remaining_time'] = $remaining_time;
			$result['remaining_time_string'] = str_replace(',',',<br>',DateTimeValue::FormatTimeDiff($zero_dt, new DateTimeValue($remaining_time * 60), 'hm', 60));
		}else{
			$result['remaining_time'] = 0;
		}

		// Total remaining time
		$total_remaining_time = max((int) $raw_data['total_remaining_time'], 0);
		if ($total_remaining_time != 0){
			$result['total_remaining_time'] = $total_remaining_time;
			$result['total_remaining_time_string'] = str_replace(',',',<br>',DateTimeValue::FormatTimeDiff($zero_dt, new DateTimeValue($total_remaining_time * 60), 'hm', 60));
		}else{
			$result['total_remaining_time'] = 0;
		}


		// Pending time
		$pending_time = $time_estimate - $total_minutes;
		if ($pending_time > 0){
			$result['pending_time'] = $pending_time;
			$result['pending_time_string'] = str_replace(',',',<br>',DateTimeValue::FormatTimeDiff($zero_dt, new DateTimeValue($pending_time * 60), 'hm', 60));
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
		// self::$custom_properties is a static property — only load it once per request,
		// not on every getArrayInfo() call (previous code unconditionally overwrote it).
		if (is_null(self::$custom_properties)) {
			self::$custom_properties = CustomProperties::getAllCustomPropertiesByObjectType(self::instance()->getObjectTypeId());
			if (is_null(self::$custom_properties)) self::$custom_properties = array();
		}

		// Cache which CPs are visible for the duration of the request (same user, same CP set)
		if (self::$_visible_cp_ids === null) {
			self::$_visible_cp_ids = array();
			foreach (self::$custom_properties as $cp) {
				self::$_visible_cp_ids[$cp->getId()] = (bool) user_config_option('tasksShowCP_' . $cp->getId());
			}
		}

		foreach (self::$custom_properties as $cp) {
			$cp_type = $cp->getType();
			$display_value = self::$_visible_cp_ids[$cp->getId()] ? get_custom_property_value_for_listing($cp, $result['id']) : '';
			// For contact/user CPs expose the raw stored value (contact ID) so the
			// inline editor can pre-select the correct option in the selector.
			$raw_value = null;
			if (in_array($cp_type, array('contact', 'user', 'display_member_property', 'user_select', 'date', 'datetime', 'boolean'))) {
				$cpv_row = DB::executeOne("SELECT `value` FROM " . TABLE_PREFIX . "custom_property_values WHERE object_id=" . (int)$result['id'] . " AND custom_property_id=" . $cp->getId());
				$raw_value = $cpv_row ? $cpv_row['value'] : null;
			}
			// 'display_member_property' values live on the referenced member, not on the task
			// itself (the after-save hook propagates and then deletes the task-level row).
			// Pull the raw value transitively so the inline editor can pre-populate the input.
			if ($cp_type == 'display_member_property' && $raw_value === null
				&& function_exists('mfio_resolve_external_cp') && $tmp_task instanceof ContentDataObject) {
				$resolved_cp = mfio_resolve_external_cp($cp);
				if ($resolved_cp) {
					$mem = $tmp_task->getMemberOfType($resolved_cp['mem_type_id']);
					if ($mem instanceof Member) {
						$mem_val = $mem->getCustomPropertyValue($resolved_cp['ext_cp_id'], false);
						if ($mem_val !== '' && $mem_val !== null) {
							$raw_value = $mem_val;
						}
					}
				}
			}

			$cp_values[] = array(
				'id'        => $cp->getId(),
				'value'     => $display_value,
				'type'      => $cp_type,
				'raw_value' => $raw_value,
			);
		}
		$result['custom_properties'] = $cp_values;
		
		Hook::fire('task_info_additional_data', $raw_data, $result);

		return $result;
	}

	/**
	 * Lean variant of getArrayInfo() built specifically for the Excel export
	 * action (TaskController::export_tasks_excel). It produces the same shape
	 * for the fields the spreadsheet actually reads but pre-skips work whose
	 * output is only consumed by the on-screen list / inline editor:
	 *
	 *   - description sanitization (export never renders descriptions)
	 *   - getOpenTimeslots / workingOn* (UI-only)
	 *   - can_add_timeslots (UI-only)
	 *   - ProjectTaskDependencies queries (UI-only)
	 *   - per-CP raw_value DB::executeOne (inline editor pre-population only)
	 *
	 * IMPORTANT: this method is strictly additive. It does not modify
	 * getArrayInfo() and is not called from any non-export code path; do not
	 * use it for anything other than building rows for the tasks Excel export.
	 *
	 * Callers MUST have pre-warmed the relevant caches for the task ids being
	 * processed (CustomPropertyValues::prefetchForObjects, ObjectMembers, etc.).
	 *
	 * @param array $raw_data       Raw row from ProjectTasks::instance()->listing(... raw_data => true)
	 * @param bool  $include_subtasks_ids
	 * @return array
	 */
	static function getArrayInfoForExport($raw_data, $include_subtasks_ids = true) {
		$task_id = isset($raw_data['id']) ? $raw_data['id'] : (isset($raw_data['object_id']) ? $raw_data['object_id'] : 0);
		$tz_offset = Timezones::getTimezoneOffsetToApplyFromArray($raw_data);
		$zero_dt = new DateTimeValue(0);

		$member_ids = ($task_id > 0) ? ObjectMembers::instance()->getCachedObjectMembers($task_id) : array();
		$tmp_task = new ProjectTask();
		$tmp_task->setObjectId($task_id);
		$tmp_task->setId($task_id);
		$tmp_task->setAssignedToContactId(isset($raw_data['assigned_to_contact_id']) ? $raw_data['assigned_to_contact_id'] : 0);

		$result = array(
			'id' => (int)$task_id,
			'name' => isset($raw_data['name']) ? $raw_data['name'] : '',
			'description' => '',
			'members' => $member_ids,
			'createdOn' => isset($raw_data['created_on']) ? strtotime($raw_data['created_on']) : 0,
			'createdById' => isset($raw_data['created_by_id']) ? (int)$raw_data['created_by_id'] : 0,
			// See note in getArrayInfo(): prefer the plugin's object_subtype_id over
			// the unused legacy object_subtype column.
			'otype' => (isset($raw_data['object_subtype_id']) && $raw_data['object_subtype_id'] !== null && $raw_data['object_subtype_id'] !== '')
				? (int)$raw_data['object_subtype_id']
				: (isset($raw_data['object_subtype']) ? (int)$raw_data['object_subtype'] : 0),
			'percentCompleted' => (int)$raw_data['percent_completed'],
		);

		if (count($member_ids) > 0) {
			$result['memPath'] = str_replace('"',"'", escape_character(json_encode($tmp_task->getMembersIdsToDisplayPath())));

			$task_members = array();
			foreach ($member_ids as $member_id) $task_members[] = Members::getMemberById($member_id); // uses cache
			$task_members = array_filter($task_members);
			$members_data = array();
			foreach ($task_members as $m) {
				/* @var $m Member */
				$m_data = array(
					'id' => $m->getId(),
					'name' => $m->getName(),
					'dimension_id' => $m->getDimensionId(),
					'object_type_id' => $m->getObjectTypeId(),
				);
				$m_ot = ObjectTypes::instance()->findById($m->getObjectTypeId());
				if ($m_ot instanceof ObjectType) {
					$m_data['object_type_name'] = $m_ot->getName();
				}
				$members_data[] = $m_data;
			}
			$result['members_data'] = $members_data;
		}

		if (isset($raw_data['isread']))         $result['isread'] = $raw_data['isread'];
		if (isset($raw_data['mark_as_started'])) $result['mark_as_started'] = $raw_data['mark_as_started'] ? "1" : "0";

		$result['multiAssignment'] = (int)array_var($raw_data, 'multi_assignment');

		if ($raw_data['completed_by_id'] > 0) $result['status'] = 1;
		if ($raw_data['parent_id'] > 0)       $result['parentId'] = (int)$raw_data['parent_id'];

		if ($include_subtasks_ids) {
			// Export path intentionally lists ALL subtasks and does not honor the
			// "show subtasks outside status filter" preference (unlike the on-screen
			// tasks list via getArrayInfo). The spreadsheet is expected to be complete.
			$result['subtasksIds'] = $tmp_task->getSubTasksIds();
		}

		$result['priority'] = (int)$raw_data['priority'];

		if ($raw_data['milestone_id'] > 0)   $result['milestoneId']   = (int)$raw_data['milestone_id'];
		if ($raw_data['assigned_by_id'] > 0) $result['assignedById']  = (int)$raw_data['assigned_by_id'];
		if ($raw_data['assigned_to_contact_id'] > 0) $result['assignedToContactId'] = (int)$raw_data['assigned_to_contact_id'];

		$result['atName'] = $tmp_task->getAssignedToName();

		if ($raw_data['completed_by_id'] > 0) {
			$result['completedById'] = (int)$raw_data['completed_by_id'];
			$result['completedOn']   = strtotime($raw_data['completed_on']);
		}

		if ($raw_data['due_date'] != EMPTY_DATETIME) {
			$result['useDueTime'] = $raw_data['use_due_time'] ? 1 : 0;
			$result['dueDate'] = $result['useDueTime']
				? strtotime($raw_data['due_date']) + $tz_offset
				: strtotime($raw_data['due_date']);
		}
		if ($raw_data['start_date'] != EMPTY_DATETIME) {
			$result['useStartTime'] = $raw_data['use_start_time'] ? 1 : 0;
			$result['startDate'] = $result['useStartTime']
				? strtotime($raw_data['start_date']) + $tz_offset
				: strtotime($raw_data['start_date']);
		}

		$time_estimate = $raw_data['time_estimate'];
		$result['timeEstimate'] = $raw_data['time_estimate'];
		if ($time_estimate > 0) $result['timeEstimateString'] = str_replace(',', ',<br>', DateTimeValue::FormatTimeDiff($zero_dt, new DateTimeValue($time_estimate * 60), 'hm', 60));

		$total_time_estimate = $raw_data['total_time_estimate'];
		if ($total_time_estimate > 0) {
			$result['totalTimeEstimate']       = $total_time_estimate;
			$result['totalTimeEstimateString'] = str_replace(',', ',<br>', DateTimeValue::FormatTimeDiff($zero_dt, new DateTimeValue($total_time_estimate * 60), 'hm', 60));
		} else {
			$result['totalTimeEstimate'] = 0;
		}

		$result['timeZone'] = $tz_offset;

		// Worked time
		$total_minutes = $raw_data['total_worked_time'];
		if ($total_minutes > 0) {
			$result['worked_time']        = $total_minutes;
			$result['worked_time_string'] = str_replace(',', ',<br>', DateTimeValue::FormatTimeDiff($zero_dt, new DateTimeValue($total_minutes * 60), 'hm', 60));
		} else {
			$result['worked_time'] = 0;
		}

		$overall_worked_minutes = $raw_data['overall_worked_time_plus_subtasks'];
		if ($overall_worked_minutes > 0) {
			$result['overall_worked_time_plus_subtasks'] = $overall_worked_minutes;
			$result['overall_worked_time']               = $overall_worked_minutes;
			$result['overall_worked_time_string']        = str_replace(',', ',<br>', DateTimeValue::FormatTimeDiff($zero_dt, new DateTimeValue($overall_worked_minutes * 60), 'hm', 60));
		} else {
			$result['overall_worked_time_plus_subtasks'] = $overall_worked_minutes;
			$result['overall_worked_time']               = 0;
		}

		$remaining_time = max((int) $raw_data['remaining_time'], 0);
		if ($remaining_time != 0) {
			$result['remaining_time']        = $remaining_time;
			$result['remaining_time_string'] = str_replace(',', ',<br>', DateTimeValue::FormatTimeDiff($zero_dt, new DateTimeValue($remaining_time * 60), 'hm', 60));
		} else {
			$result['remaining_time'] = 0;
		}

		$total_remaining_time = max((int) $raw_data['total_remaining_time'], 0);
		if ($total_remaining_time != 0) {
			$result['total_remaining_time']        = $total_remaining_time;
			$result['total_remaining_time_string'] = str_replace(',', ',<br>', DateTimeValue::FormatTimeDiff($zero_dt, new DateTimeValue($total_remaining_time * 60), 'hm', 60));
		} else {
			$result['total_remaining_time'] = 0;
		}

		$pending_time = $time_estimate - $total_minutes;
		if ($pending_time > 0) {
			$result['pending_time']        = $pending_time;
			$result['pending_time_string'] = str_replace(',', ',<br>', DateTimeValue::FormatTimeDiff($zero_dt, new DateTimeValue($pending_time * 60), 'hm', 60));
		} else {
			$result['pending_time'] = 0;
		}

		if ($raw_data['repeat_forever'] > 0 || $raw_data['repeat_num'] > 0 || ($raw_data['repeat_end'] != EMPTY_DATETIME && $raw_data['repeat_end'] != '')) {
			$result['repetitive'] = 1;
		}

		// Custom properties — same shape as getArrayInfo, but raw_value is left
		// as null because the export never renders the inline editor.
		// self::$custom_properties / self::$_visible_cp_ids are populated lazily
		// by the first getArrayInfo() call of the request; if the export action
		// is the first task code to run, we still need to populate them.
		if (is_null(self::$custom_properties)) {
			self::$custom_properties = CustomProperties::getAllCustomPropertiesByObjectType(self::instance()->getObjectTypeId());
			if (is_null(self::$custom_properties)) self::$custom_properties = array();
		}
		if (self::$_visible_cp_ids === null) {
			self::$_visible_cp_ids = array();
			foreach (self::$custom_properties as $cp) {
				self::$_visible_cp_ids[$cp->getId()] = (bool) user_config_option('tasksShowCP_' . $cp->getId());
			}
		}

		$cp_values = array();
		foreach (self::$custom_properties as $cp) {
			$display_value = self::$_visible_cp_ids[$cp->getId()] ? get_custom_property_value_for_listing($cp, $result['id']) : '';
			$cp_values[] = array(
				'id'        => $cp->getId(),
				'value'     => $display_value,
				'type'      => $cp->getType(),
				'raw_value' => null, // not consumed by the export
			);
		}
		$result['custom_properties'] = $cp_values;

		// Keep the hook so advanced_billing / other plugins can still inject
		// additional_data (cost / price / earned_value columns) into the row.
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


	/**
	 * Initializes the task list related contact config options
	 * @return void
	 */
	function initTaskListUserPreferences() {
		// Initialize the task list group by allowed properties user preference
		$this->initTaskListGroupByAllowedPropertiesUserPreference();

		// Initialize the task list order by allowed properties user preference
		$this->initTaskListOrderByAllowedPropertiesUserPreference();

		// Initialize the task list filter by allowed properties user preference
		$this->initTaskListFilterByAllowedPropertiesUserPreference();
	}

	/**
	 * Initializes the task list group by allowed properties user preference
	 *
	 * This function sets up the default values for the task list group by options
	 * config option.
	 * 
	 * If the option already exists the function does nothing
	 *
	 * @return void
	 */
	private function initTaskListGroupByAllowedPropertiesUserPreference() {

		$option = ContactConfigOptions::instance()->getByName('task_list_group_by_options');
		if (!$option instanceof ContactConfigOption) {
			$option = new ContactConfigOption();
			$option->setName('task_list_group_by_options');
			$option->setCategoryName('task panel');
			$option->setConfigHandlerClass('TaskListGroupByValuesConfigHandler');
			$option->setOptionOrder(250);
			
			$default_value_array = [
				'milestone', 'priority', 'assigned_to', 'due_date', 'start_date', 'created_on', 'created_by', 'completed_on', 'completed_by', 'status'
			];

			$default_value_array = array_merge($default_value_array, $this->getDimensionAndCustomPropertiesForTaskListOptions());

			$option->setDefaultValue(implode(',', $default_value_array));

			$option->save();
		}

	}

	/**
	 * Initializes the task list order by allowed properties user preference
	 *
	 * This function sets up the default values for the task list order by options
	 * config option.
	 *
	 * If the option already exists the function does nothing
	 */
	private function initTaskListOrderByAllowedPropertiesUserPreference() {
		$option = ContactConfigOptions::instance()->getByName('task_list_order_by_options');
		if (!$option instanceof ContactConfigOption) {
			$option = new ContactConfigOption();
			$option->setName('task_list_order_by_options');
			$option->setCategoryName('task panel');
			$option->setConfigHandlerClass('TaskListOrderByValuesConfigHandler');
			$option->setOptionOrder(251);

			$default_value_array = [
				'priority', 'name', 'due_date', 'created_on', 'completed_on', 'assigned_to', 'start_date', 'percent_completed'
			];

			$default_value_array = array_merge($default_value_array, $this->getDimensionAndCustomPropertiesForTaskListOptions());

			$option->setDefaultValue(implode(',', $default_value_array));

			$option->save();
		}
	}

	/**
	 * Initializes the task list filter by allowed properties user preference
	 *
	 * This function sets up the default values for the task list filter by options
	 * config option.
	 *
	 * If the option already exists the function does nothing
	 *
	 * @return void
	 */
	private function initTaskListFilterByAllowedPropertiesUserPreference() {
		if (!Plugins::instance()->isActivePlugin('advanced_core')) {
			return;
		}
		$option = ContactConfigOptions::instance()->getByName('task_list_filter_by_options');
		if (!$option instanceof ContactConfigOption) {
			$option = new ContactConfigOption();
			$option->setName('task_list_filter_by_options');
			$option->setCategoryName('task panel');
			$option->setConfigHandlerClass('TaskListFilterByValuesConfigHandler');
			$option->setOptionOrder(252);

			$default_value_array = ['created_by','completed_by','assigned_to','assigned_by','milestone','priority','subscribed_to','start_date','due_date','invoicing_status'];
			
			if (Plugins::instance()->isActivePlugin('object_subtypes')) {
				$default_value_array[] = 'object_subtype';
			}

			// ADD THE CUSTOM PROPERTIES OPTIONS - ONLY DATE AND USER PROPERTIES
			$task_cps = CustomProperties::instance()->getAllCustomPropertiesByObjectType($this->getObjectTypeId());
			foreach ($task_cps as $cp) {
				if (in_array($cp->getType(), ['user', 'date', 'datetime'])) {
					$default_value_array[] = 'cp_'.$cp->getId();
				}
			}

			$option->setDefaultValue(implode(',', $default_value_array));

			$option->save();
		}
	}


	function getDimensionAndCustomPropertiesForTaskListOptions() {
		$default_value_array = [];

		// ADD THE CUSTOM PROPERTIES OPTIONS
		$task_cps = CustomProperties::instance()->getAllCustomPropertiesByObjectType($this->getObjectTypeId());
		foreach ($task_cps as $cp) {
			$default_value_array[] = 'cp_'.$cp->getId();
		}

		// ADD THE DIMENSION OPTIONS
		$task_member_types = ProjectTasks::instance()->getTaskMemberTypesForListOptions(false);
		foreach ($task_member_types as $task_member_type) {
			$default_value_array[] = 'dim_'.$task_member_type['dim_id'].'_'.$task_member_type['mem_type_id'];
		}
		
		return $default_value_array;
	}



	/**
	 * Returns an array of task member types for the task list options
	 *
	 * If $include_folders is true, the function will include the folder object types
	 * in the result. Otherwise, it will exclude the folder object types.
	 *
	 * The function loops through all the enabled dimensions and their object types.
	 * For each object type, it checks if the object type is not a folder and
	 * if $include_folders is true, or if the object type is not a folder and
	 * $include_folders is false. If the condition is true, it adds the object type to
	 * the result array.
	 *
	 * @param bool $include_folders Whether to include the folder object types in the result
	 * @return array
	 */
	function getTaskMemberTypesForListOptions($include_folders = true) {

		$task_member_types = array();

		$enabled_dimension_ids = config_option('enabled_dimensions');
		$enabled_dimensions = Dimensions::instance()->findAll(array('conditions' => '`id` IN ('. implode(",", $enabled_dimension_ids) .')'));
		foreach ($enabled_dimensions as $enabled_dimension) {
			$ot_ids = implode(",", DimensionObjectTypes::getObjectTypeIdsByDimension($enabled_dimension->getId()));
			$dimension_obj_types = ObjectTypes::instance()->findAll(array("conditions" => "`id` IN ($ot_ids)"));
			
			$no_folder_ots_count = 0;
			foreach ($dimension_obj_types as $ot) {
				if ($ot->getName() != 'folder' && $ot->getName() != 'project_folder' && $ot->getName() != 'customer_folder') {
					$no_folder_ots_count++;
				}
			}

			foreach ($dimension_obj_types as $ot) {
				$mem_type_name = $ot->getObjectTypeName();
				if ($no_folder_ots_count == 1 && $ot->getName() != 'folder') {
					$mem_type_name = $enabled_dimension->getName();
				}

				$add_mem_type = true;
				if (strpos($ot->getName(), 'folder') !== false && !$include_folders) {
					$add_mem_type = false;
				}

				if ($add_mem_type) {
					$task_member_types[] = array('dim_id' => $enabled_dimension->getId(), 'dim_name' => $enabled_dimension->getName(), 'mem_type_id' => $ot->getId(), 'mem_type_name' => $mem_type_name);
				}
				
			}
		}

		return $task_member_types;

	}
	
} // ProjectTasks
