<?php
 
/**
 * ProjectTask class
 * Generated on Sat, 04 Mar 2006 12:50:11 +0100 by DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 * Modif: Marcos Saiz <marcos.saiz@gmail.com> 24/3/08
 */
class ProjectTask extends BaseProjectTask {


	protected $searchable_columns = array('name', 'text');
		
	protected $allow_timeslots = true;
	
	public $timeslots_count = 0 ;
	
	public $timeslots = null ;

	/**
	 * Cached task array
	 *
	 * @var array
	 */
	private $all_tasks;

	/**
	 * Cached open task array
	 *
	 * @var array
	 */
	private $open_tasks;

	/**
	 * Cached completed task array
	 *
	 * @var array
	 */
	private $completed_tasks;

	/**
	 * Cached number of open tasks
	 *
	 * @var integer
	 */
	private $count_all_tasks;

	/**
	 * Cached number of open tasks in this list
	 *
	 * @var integer
	 */
	private $count_open_tasks = null;

	/**
	 * Cached number of completed tasks in this list
	 *
	 * @var integer
	 */
	private $count_completed_tasks = null;

	/**
	 * Cached array of related forms
	 *
	 * @var array
	 */
	private $related_forms;

	/**
	 * Cached completed by reference
	 *
	 * @var Contact
	 */
	private $completed_by;

	private $milestone;
	
	private $id = null;
	
	function getId() {
		if ($this->id == null)
			$this->id = parent::getId();
		return $this->id;
	}
	
	function setId($value) {
		parent::setId($value);
		$this->id = $value;
	}
	
	function getMilestone(){
		if ($this->getMilestoneId() > 0 && !$this->milestone){
			$this->milestone = ProjectMilestones::findById($this->getMilestoneId());
		}
		return $this->milestone;
	}
	
	/**
	 * Return parent task that this task belongs to
	 *
	 * @param void
	 * @return ProjectTask
	 */
	function getParent() {
		if ($this->getParentId()==0) return null;
		$parent = ProjectTasks::findById($this->getParentId());
		return $parent instanceof ProjectTask  ? $parent : null;
	} // getParent
	
	/**
	 * Return the user that last assigned the task
	 *
	 * @access public
	 * @param void
	 * @return Contact
	 */
	function getAssignedBy() {
		return Contacts::findById($this->getAssignedById());
	} // getAssignedBy()

	/**
	 * Set the user that last assigned the task
	 *
	 * @access public
	 * @param Contact $value
	 * @return boolean
	 */
	function setAssignedBy($user) {
		if ($user instanceof Contact) {
			$this->setAssignedById($user->getId());
		}
	}

	/**
	 * Return owner user or company
	 *
	 * @access public
	 * @param void
	 * @return ApplicationDataObject
	 */
	function getAssignedTo() {
		if($this->getAssignedToContactId() > 0) {
			return $this->getAssignedToContact();
		} else {
			return null;
		} // if
	} // getAssignedTo
	
	function getAssignedToName() {
		$user = $this->getAssignedToContact();
		if ($user instanceof Contact) {
			return $user->getObjectName();
		} else {
			return lang("anyone");
		} // if
	} // getAssignedTo
	
	function isAssigned() {
		return $this->getAssignedToContactId() > 0;
	} // getAssignedTo

	/**
	 * Return owner user
	 *
	 * @access public
	 * @param void
	 * @return Contact
	 */
	function getAssignedToContact() {
		$ret = null;
		if ($this->getAssignedToContactId() > 0) {
			$ret = Contacts::findById($this->getAssignedToContactId());
		}
		return $ret;
	} // 

	/**
	 * Returns true if this task was not completed
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function isOpen() {
		return !$this->isCompleted();
	} // isOpen

	/**
	 * Returns true if this task is completed
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function isCompleted() {
		return $this->getCompletedOn() instanceof DateTimeValue;
	} // isCompleted

	/**
	 * Check if this task is late
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function isLate() {
		if($this->isCompleted()) return false;
		if(!$this->getDueDate() instanceof DateTimeValue) return false;
		return !$this->isToday() && ($this->getDueDate()->getTimestamp() < DateTimeValueLib::now()->add('h', logged_user()->getTimezone())->getTimestamp());
	} // isLate
	
	/**
	 * Check if this task is today
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function isToday() {
		$now = DateTimeValueLib::now()->add('h', logged_user()->getTimezone());
		$due = $this->getDueDate();
		// getDueDate and similar functions can return NULL
		if(!($now instanceof DateTimeValue)) return false;
		if(!($due instanceof DateTimeValue)) return false;

		return $now->getDay() == $due->getDay() &&
		$now->getMonth() == $due->getMonth() &&
		$now->getYear() == $due->getYear();
	} // isToday
	
	/**
	 * Return number of days that this task is late for
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getLateInDays() {
		if (!$this->getDueDate() instanceof DateTimeValue) return 0;
		$due_date_start = $this->getDueDate();
		$due_date_start->add('h', logged_user()->getTimezone());
		$today = DateTimeValueLib::now();
		$today = $today->add('h', logged_user()->getTimezone());
		
		return abs(floor($due_date_start->getTimestamp() / 86400) - floor($today->getTimestamp() / 86400));
	} // getLateInDays
	
	function getLeftInDays() {
		if (!$this->getDueDate() instanceof DateTimeValue) return 0;
		$due_date_start = $this->getDueDate();
		$due_date_start->add('h', logged_user()->getTimezone());
		$today = DateTimeValueLib::now();
		$today = $today->add('h', logged_user()->getTimezone());
		
		return abs(floor($due_date_start->getTimestamp() / 86400) - floor($today->getTimestamp() / 86400));
	}
	
	
	function getDescription() {
		return $this->getText();
	}

	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	function canAdd(Contact $user, $context, &$notAllowedMember = ''){
		return can_add($user, $context, ProjectTasks::instance()->getObjectTypeId(), $notAllowedMember);
	}
	
	
	/**
	 * Return true if $user can view this task lists
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canView(Contact $user) {
		$other_perm_conditions = SystemPermissions::userHasSystemPermission($user, 'can_see_assigned_to_other_tasks') || $this->getAssignedToContactId() == $user->getId();
		return can_read($user, $this->getMembers(), $this->getObjectTypeId()) && $other_perm_conditions;
	} // canView
	
	/**
	 * Return true if $user can link this task
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canLinkObject(Contact $user) {
		if(can_read($user, $this->getMembers(), $this->getObjectTypeId())) {
			return can_link_objects($user);
		}
		return parent::canLinkObject();		
	}
	
	/**
	 * Private function to check whether a task is asigned to user or company user
	 *
	 * @param Contact $user
	 * @return unknown
	 */
	private function isAsignedToUserOrCompany(Contact $user){
		return ($user->getId() == $this->getAssignedToContactId());
	}
	/**
	 * Check if specific user can update this task
	 *
	 * @access public
	 * @param Contact $user
	 * @return boolean
	 */
	function canEdit(Contact $user) {
		if(can_write($user, $this->getMembers(), $this->getObjectTypeId())) {
			return true;
		} // if
		$task_list = $this->getParent();
		return $task_list instanceof ProjectTask ? $task_list->canEdit($user, $this->getMembers()) : false;
	} // canEdit
	
	
	/**
	 * Check if specific user can change task status
	 *
	 * @access public
	 * @param Contact $user
	 * @return boolean
	 */
	function canChangeStatus(Contact $user) {
		return (can_manage_tasks($user) || $this->isAsignedToUserOrCompany($user));
	} // canChangeStatus

	/**
	 * Check if specific user can delete this task
	 *
	 * @access public
	 * @param Contact $user
	 * @return boolean
	 */
	function canDelete(Contact $user) {
		if (can_delete($user,$this->getMembers(), $this->getObjectTypeId()))
			return true;
		$task_list = $this->getParent();
		return $task_list instanceof ProjectTask ? $task_list->canDelete($user) : false;
	} // canDelete

	/**
	 * Check if user can reorder tasks in this list
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canReorderTasks(Contact $user) {
		return can_write($user, $this->getMembers(), $this->getObjectTypeId());
	} // canReorderTasks


	/**
	 * Check if specific user can add task to this list
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canAddSubTask(Contact $user) {
		return can_write($user, $this->getMembers(), $this->getObjectTypeId());
	} // canAddTask
	// ---------------------------------------------------
	//  Operations
	// ---------------------------------------------------

	/**
	 * Complete this task and subtasks and check if we need to complete the parent
	 *
	 * @access public
	 * @param void
	 * @return $log_info
	 */
	function completeTask($options) {
		if (!$this->canChangeStatus(logged_user())) {
			flash_error('no access permissions');
			ajx_current("empty");
			return;
		}
		
		$ret=null;
		Hook::fire("before_completing_task", array('task' => $this), $ret);
		
		$this->setCompletedOn(DateTimeValueLib::now());
		$this->setCompletedById(logged_user()->getId());

		if($options == "yes"){
			foreach ($this->getAllSubTasks() as $subt) {
				$subt->completeTask($options);
			}
		}

		if(user_config_option('close timeslot open')){
			$timeslots = Timeslots::getOpenTimeslotsByObject($this->getId());
			if ($timeslots){
				foreach ($timeslots as $timeslot){
					if ($timeslot->isOpen())
					$timeslot->close();
					$timeslot->save();
				}
			}
		}

		// check if all previuos tasks are completed
		$log_info = "";
		if (config_option('use tasks dependencies')) {
			$saved_ptasks = ProjectTaskDependencies::findAll(array('conditions' => 'task_id = '. $this->getId()));
			foreach ($saved_ptasks as $pdep) {
				$ptask = ProjectTasks::findById($pdep->getPreviousTaskId());
				if ($ptask instanceof ProjectTask && !$ptask->isCompleted()) {
					flash_error(lang('previous tasks must be completed before completion of this task'));
					ajx_current("empty");
					return;
				}
			}
			//Seeking the subscribers of the completed task not to repeat in the notifications
			$contact_notification = array();
			$task = ProjectTasks::findById($this->getId());
			foreach ($task->getSubscribers() as $task_sub){
				$contact_notification[] = $task_sub->getId();
			}
			//Send notification to subscribers of the task_dependency on the task completed
			$next_dependency = ProjectTaskDependencies::findAll(array('conditions' => 'previous_task_id = '. $this->getId()));
			foreach ($next_dependency as $ndep) {
				$ntask = ProjectTasks::findById($ndep->getTaskId());
				if ($ntask instanceof ProjectTask) {
					foreach ($ntask->getSubscribers() as $task_dep){
						if(!in_array($task_dep->getId(), $contact_notification))
						$log_info .= $task_dep->getId().",";
					}
				}
			}
		}
		$this->setPercentCompleted(100);
		$this->save();
		return $log_info;
	} // completeTask

	/**
	 * Open this task and check if we need to reopen list again
	 *
	 * @access public
	 * @param void
	 * @return $log_info
	 */
	function openTask() {
		if (!$this->canChangeStatus(logged_user())) {
			flash_error('no access permissions');
			ajx_current("empty");
			return;
		}
		$this->setCompletedOn(null);
		$this->setCompletedById(0);
		$this->save();

		$this->calculatePercentComplete();

		$log_info = "";
		if (config_option('use tasks dependencies')) {
			//Seeking the subscribers of the open task not to repeat in the notifications
			$contact_notification = array();
			foreach ($this->getSubscribers() as $task_sub){
				$contact_notification[] = $task_sub->getId();
			}
			$saved_stasks = ProjectTaskDependencies::findAll(array('conditions' => 'previous_task_id = '. $this->getId()));
			foreach ($saved_stasks as $sdep) {
				$stask = ProjectTasks::findById($sdep->getTaskId());
				if ($stask instanceof ProjectTask && $stask->isCompleted()) {
					$stask->openTask();
				}
				foreach ($stask->getSubscribers() as $task_dep){
					if(!in_array($task_dep->getId(), $contact_notification)) {
						$log_info .= $task_dep->getId().",";
					}
				}
			}
		}
		
		/*
		 * this is done in the controller
		$task_list = $this->getParent();
		if(($task_list instanceof ProjectTask) && $task_list->isCompleted()) {
			$open_tasks = $task_list->getOpenSubTasks();
			if(!empty($open_tasks)) $task_list->open();
		} // if*/
		
		return $log_info;
	} // openTask

	function getRemainingDays(){
		if (is_null($this->getDueDate()))
			return null;
		else{
			$due = $this->getDueDate();
			$date = DateTimeValueLib::now()->add('h', logged_user()->getTimezone())->getTimestamp();
			$nowDays = floor($date/(60*60*24));
			$dueDays = floor($due->getTimestamp()/(60*60*24));
			return $dueDays - $nowDays;
		}
	}
	
	function cloneTask($new_st_date='',$new_due_date='',$copy_status = false,$copy_repeat_options = true,$parent_subtask=0) {

		$new_task = new ProjectTask();
		
		if($parent_subtask != 0){
			$new_task->setParentId($parent_subtask);
		}else{
			$new_task->setParentId($this->getParentId());
		}
		$new_task->setObjectName($this->getObjectName());
		$new_task->setText($this->getText());
		$new_task->setAssignedToContactId($this->getAssignedToContactId());
		$new_task->setAssignedOn($this->getAssignedOn());
		$new_task->setAssignedById($this->getAssignedById());
		$new_task->setTimeEstimate($this->getTimeEstimate());
		$new_task->setStartedOn($this->getStartedOn());
		$new_task->setStartedById($this->getStartedById());
		$new_task->setPriority(($this->getPriority()));
		$new_task->setState($this->getState());
		$new_task->setOrder($this->getOrder());
		$new_task->setMilestoneId($this->getMilestoneId());
		$new_task->setFromTemplateId($this->getFromTemplateId());
		$new_task->setUseStartTime($this->getUseStartTime());
		$new_task->setUseDueTime($this->getUseDueTime());
		$new_task->setTypeContent($this->getTypeContent());
		if($this->getParentId() == 0){//if not subtask
			if($this->getOriginalTaskId() == 0){
				$new_task->setOriginalTaskId($this->getObjectId());
			}else{
				$new_task->setOriginalTaskId($this->getOriginalTaskId());
			}
		}
		if ($this->getDueDate() instanceof DateTimeValue ) {
			$new_task->setDueDate(new DateTimeValue($this->getDueDate()->getTimestamp()));
		}
		if ($this->getStartDate() instanceof DateTimeValue ) {
			$new_task->setStartDate(new DateTimeValue($this->getStartDate()->getTimestamp()));
		}
		if ($copy_status) {
			$new_task->setCompletedById($this->getCompletedById());
			$new_task->setCompletedOn($this->getCompletedOn());
		}
		if ($copy_repeat_options) {
			$new_task->setRepeatEnd($this->getRepeatEnd());
			$new_task->setRepeatForever($this->getRepeatForever());
			$new_task->setRepeatNum($this->getRepeatNum());
			$new_task->setRepeatBy($this->getRepeatBy());
			$new_task->setRepeatD($this->getRepeatD());
			$new_task->setRepeatM($this->getRepeatM());
			$new_task->setRepeatY($this->getRepeatY());
		}		
		if($new_st_date != "") {
			if ($new_task->getStartDate() instanceof DateTimeValue) $new_task->setStartDate($new_st_date);
		}
		if($new_due_date != "") {
			if ($new_task->getDueDate() instanceof DateTimeValue) $new_task->setDueDate($new_due_date);
		}		
		$new_task->save();
		
		
		// Copy members, linked_objects, custom_properties, subscribers, reminders and comments
		copy_additional_object_data($this, $new_task);

		// Ensure that assigned user is subscribed
		if ($new_task->getAssignedTo() instanceof Contact) {
			$new_task->subscribeUser($new_task->getAssignedTo());
		}
		
		$sub_tasks = $this->getAllSubTasks();
		foreach ($sub_tasks as $st) {
			$new_dates = $this->getNextRepetitionDatesSubtask($st,$new_task, $new_st_date, $new_due_date);
			if ($st->getParentId() == $this->getId()) {
				$new_st = $st->cloneTask(array_var($new_dates, 'st'),array_var($new_dates, 'due'),$copy_status, $copy_repeat_options, $new_task->getId());
				if ($copy_status) {
					$new_st->setCompletedById($st->getCompletedById());
					$new_st->setCompletedOn($st->getCompletedOn());
					$new_st->save();
				}
				$new_task->attachTask($new_st);
			}
		}
                
		return $new_task;
	}
	
	function clearRepeatOptions() {
		$this->setRepeatEnd(EMPTY_DATETIME);
		$this->setRepeatForever(0);
		$this->setRepeatNum(0);
		$this->setRepeatBy('');
		$this->setRepeatD(0);
		$this->setRepeatM(0);
		$this->setRepeatY(0);
	}
        
	private function getNextRepetitionDatesSubtask($subtask, $task, &$new_st_date, &$new_due_date) {
		$new_due_date = null;
		$new_st_date = null;
                
		if ($subtask->getStartDate() instanceof DateTimeValue ) {
			$new_st_date = new DateTimeValue($subtask->getStartDate()->getTimestamp());
		}
		if ($subtask->getDueDate() instanceof DateTimeValue ) {
			$new_due_date = new DateTimeValue($subtask->getDueDate()->getTimestamp());
		}
		if ($task->getRepeatD() > 0) {
			if ($new_st_date instanceof DateTimeValue) {
				$new_st_date = $new_st_date->add('d', $task->getRepeatD());
			}                        
			if ($new_due_date instanceof DateTimeValue) {
				$new_due_date = $new_due_date->add('d', $task->getRepeatD());
			}
		} else if ($task->getRepeatM() > 0) {
			if ($new_st_date instanceof DateTimeValue) {
				$new_st_date = $new_st_date->add('M', $task->getRepeatM());
			}
			if ($new_due_date instanceof DateTimeValue) {
				$new_due_date = $new_due_date->add('M', $task->getRepeatM());
			}
		} else if ($task->getRepeatY() > 0) {
			if ($new_st_date instanceof DateTimeValue) {
				$new_st_date = $new_st_date->add('y', $task->getRepeatY());
			}
			if ($new_due_date instanceof DateTimeValue) {
				$new_due_date = $new_due_date->add('y', $task->getRepeatY());
			}
		}

		$new_st_date = $this->correct_days_task_repetitive($new_st_date);
		$new_due_date = $this->correct_days_task_repetitive($new_due_date);
		
		return array('st' => $new_st_date, 'due' => $new_due_date);
	}
        
        function correct_days_task_repetitive($date){
            if($date != ""){
                $working_days = explode(",",config_option("working_days"));
                if(!in_array(date("w",  $date->getTimestamp()), $working_days)){
                    $date = $date->add('d', 1);
                    $this->correct_days_task_repetitive($date);
                }
            }
            return $date;
        }
	
	// ---------------------------------------------------
	//  TaskList Operations
	// ---------------------------------------------------

	/**
	 * Add subtask to this list
	 *
	 * @param string $text
	 * @param Contact $assigned_to_user
	 * @param Company $assigned_to_company
	 * @return ProjectTask
	 * @throws DAOValidationError
	 */
	function addSubTask($text, $assigned_to = null) {
		$task = new ProjectTask();
		$task->setText($text);

		if($assigned_to instanceof Contact) 
			$task->setAssignedToContactId($assigned_to->getId());

		$this->attachTask($task); // this one will save task
		return $task;
	} // addTask

	/**
	 * Attach subtask to thistask
	 *
	 * @param ProjectTask $task
	 * @return null
	 */
	function attachTask(ProjectTask $task) {
		if($task->getParentId() == $this->getId()) return;

		$task->setParentId($this->getId());
		$task->save();

		if($this->isCompleted()) $this->open();
	} // attachTask

	/**
	 * Detach subtask from this task
	 *
	 * @param ProjectTask $task
	 * @param ProjectTaskList $attach_to If you wish you can detach and attach task to
	 *   other list with one save query
	 * @return null
	 */
	function detachTask(ProjectTask $task, $attach_to = null) {
		if($task->getParentId() <> $this->getId()) return;

		if($attach_to instanceof ProjectTask) {
			$attach_to->attachTask($task);
		} else {
			$task->setParentId(0);
			$task->save();
		}
	} // detachTask

	/**
	 * Complete this task lists
	 *
	 * @access public
	 * @param DateTimeValue $on Completed on
	 * @param Contact $by Completed by
	 * @return null
	 */
	function complete(DateTimeValue $on, $by) {
		$by_id = $by instanceof Contact ? $by->getId() : 0;
		$this->setCompletedOn($on);
		$this->setCompletedById($by_id);
		$this->save();
		ApplicationLogs::createLog($this, ApplicationLogs::ACTION_CLOSE);
	} // complete

	/**
	 * Open this list
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function open() {
		$this->setCompletedOn(NULL);
		$this->setCompletedById(0);
		$this->save();
		ApplicationLogs::createLog($this, ApplicationLogs::ACTION_OPEN);
	} // open

	// ---------------------------------------------------
	//  Related object
	// ---------------------------------------------------

	/**
	 * Return all tasks from this list
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getSubTasks($include_trashed = true, $include_archived = true) {
		$include = "";
		if(!$include_trashed){
			$include = "`trashed_by_id` = 0 AND ";
		}
		if(!$include_archived){
			$include .= "`archived_by_id` = 0 AND ";
		}
		if(is_null($this->all_tasks)) {
			$this->all_tasks = ProjectTasks::findAll(array(
          'conditions' => $include.'`parent_id` = ' . DB::escape($this->getId()),
          'order' => '`order`, `created_on`'			
          )); // findAll
          if (is_null($this->all_tasks)) $this->all_tasks = array();
		} // if

		return $this->all_tasks;
	} // getTasks
	
	/**
	 * Return all tasks from this list
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getSubTasksIds() {
		$subtasks_ids = array();
		$condition = ' AND `parent_id` = ' . DB::escape($this->getId());
				
		$subtasks_rows = ProjectTasks::instance()->listing(array(
				"select_columns" => array("`object_id`"),
				"extra_conditions" => $condition,
				"count_results" => false,
				"raw_data" => true,
		))->objects;
		for ($i = 0; $i < count($subtasks_rows); $i++){
			$subtasks_ids[] = (int)$subtasks_rows[$i]['object_id'];
		}
		
		return $subtasks_ids;
	} // getTasks

	/**
	 * Return all tasks from this list
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getAllSubTasks($include_trashed = true) {
		if(is_null($this->all_tasks)) {
			$this->all_tasks = ProjectTasks::findAll(array(
          'conditions' => '`parent_id` = ' . DB::escape($this->getId()),
          'order' => '`order`, `created_on`',
			'include_trashed' => $include_trashed
          )); // findAll
          if (is_null($this->all_tasks)) $this->all_tasks = array();
		} // if
		
		$tasks = $this->all_tasks;
		$result = $tasks;
		
		for ($i = 0; $i < count($tasks); $i++){
			$tsubtasks = $tasks[$i]->getAllSubTasks($include_trashed);
			for ($j = 0; $j < count($tsubtasks); $j++)
				$result[] = $tsubtasks[$j];
		}
		
		return $result;
	} // getTasks
	
	
	/**
	 * Gets all subtasks ids recursively
	 */
	function getAllSubtaskIdsInHierarchy() {
		$subtasks_ids = array();
		$this->getAllSubtaskIdsInHierarchyRecursive($subtasks_ids);
		
		return $subtasks_ids;
	}
	
	/**
	 * Private function to get the subtasks ids recursively
	 */
	private function getAllSubtaskIdsInHierarchyRecursive(&$all_subtasks_ids) {
		$subtasks_ids = $this->getSubTasksIds();
		foreach ($subtasks_ids as $sub_id) {
			$all_subtasks_ids[$sub_id] = $sub_id;
			$sub = ProjectTasks::findById($sub_id);
			if ($sub instanceof ProjectTask) {
				$sub->getAllSubtaskIdsInHierarchyRecursive($all_subtasks_ids);
			}
		}
	}
	
	/**
	 * Gets all subtasks info recursively
	 */
	function getAllSubtaskInfoInHierarchy() {
		$subtasks = array();
		$this->getAllSubtaskInfoInHierarchyRecursive($subtasks, 1);
	
		return $subtasks;
	}
	
	/**
	 * Private function to get the subtasks info recursively
	 */
	private function getAllSubtaskInfoInHierarchyRecursive(&$subtasks, $depth=0) {
		$subtasks_ids = $this->getSubTasksIds();
		foreach ($subtasks_ids as $sub_id) {
			$sub = ProjectTasks::findById($sub_id);
			if ($sub instanceof ProjectTask) {
				$subtasks[$sub_id] = $sub->getArrayInfo();
				$subtasks[$sub_id]['depth'] = $depth;
				$sub->getAllSubtaskInfoInHierarchyRecursive($subtasks, $depth+1);
			}
		}
	}
	
	/**
	 * Return open tasks
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getOpenSubTasks() {
		if(is_null($this->open_tasks)) {
			$this->open_tasks = ProjectTasks::findAll(array(
          'conditions' => '`parent_id` = ' . DB::escape($this->getId()) . ' AND `completed_on` = ' . DB::escape(EMPTY_DATETIME) . ' AND `trashed_on` = ' . DB::escape(EMPTY_DATETIME),
          'order' => '`order`, `created_on`'
          )); // findAll
		} // if

		return $this->open_tasks;
	} // getOpenTasks

	/**
	 * Return completed tasks
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getCompletedSubTasks() {
		if(is_null($this->completed_tasks)) {
			$this->completed_tasks = ProjectTasks::findAll(array(
          'conditions' => '`parent_id` = ' . DB::escape($this->getId()) . ' AND `completed_on` > ' . DB::escape(EMPTY_DATETIME),
          'order' => '`completed_on` DESC'
          )); // findAll
		} // if

		return $this->completed_tasks;
	} // getCompletedTasks

	/**
	 * Return number of all tasks in this list
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function countAllSubTasks() {
		if(is_null($this->count_all_tasks)) {
			if(is_array($this->all_tasks)) {
				$this->count_all_tasks = count($this->all_tasks);
			} else {
				$this->count_all_tasks = ProjectTasks::count('`parent_id` = ' . DB::escape($this->getId()));
			} // if
		} // if
		return $this->count_all_tasks;
	} // countAllTasks

	/**
	 * Return number of open tasks
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function countOpenSubTasks() {
		if(is_null($this->count_open_tasks)) {
			if(is_array($this->open_tasks)) {
				$this->count_open_tasks = count($this->open_tasks);
			} else {
				$this->count_open_tasks = ProjectTasks::count('`parent_id` = ' . DB::escape($this->getId()) . ' AND `completed_on` = ' . DB::escape(EMPTY_DATETIME));
			} // if
		} // if
		return $this->count_open_tasks;
	} // countOpenTasks

	/**
	 * Return number of completed tasks
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function countCompletedSubTasks() {
		if(is_null($this->count_completed_tasks)) {
			if(is_array($this->completed_tasks)) {
				$this->count_completed_tasks = count($this->completed_tasks);
			} else {
				$this->count_completed_tasks = ProjectTasks::count('`parent_id` = ' . DB::escape($this->getId()) . ' AND `completed_on` > ' . DB::escape(EMPTY_DATETIME));
			} // if
		} // if
		return $this->count_completed_tasks;
	} // countCompletedTasks

	/**
	 * Get project forms that are in relation with this task list
	 *
	 * @param void
	 * @return array
	 */
	function getRelatedForms() {
		if(is_null($this->related_forms)) {
			$this->related_forms = ProjectForms::findAll(array(
          'conditions' => '`action` = ' . DB::escape(ProjectForm::ADD_TASK_ACTION) . ' AND `in_object_id` = ' . DB::escape($this->getId()),
          'order' => '`order`'
          )); // findAll
		} // if
		return $this->related_forms;
	} // getRelatedForms

	/**
	 * Return user who completed this task
	 *
	 * @access public
	 * @param void
	 * @return Contact
	 */
	function getCompletedBy() {
		if(!($this->completed_by instanceof Contact)) {
			$this->completed_by = Contacts::findById($this->getCompletedById());
		} // if
		return $this->completed_by;
	} // getCompletedBy

	/**
	 * Return the name of who completed this task
	 *
	 * @access public
	 * @param void
	 * @return Contact
	 */
	function getCompletedByName() {
		if ($this->isCompleted()){
			if(!($this->completed_by instanceof Contact)) {
				$this->completed_by = Contacts::findById($this->getCompletedById());
			} // if
			if ($this->completed_by instanceof Contact) {
				return $this->completed_by->getObjectName();
			} else {
				return '';
			}
		} else return '';
	} // getCompletedBy
	


	// ---------------------------------------------------
	//  URLs
	// ---------------------------------------------------

	/**
	 * Return edit task URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return get_url('task', 'edit_task', array('id' => $this->getId()));
	} // getEditUrl

	/**
	 * Return edit list URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditListUrl() {
		return get_url('task', 'edit_task', array('id' => $this->getId()));
	} // getEditUrl

	/**
	 * Return delete task URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('task', 'delete_task', array('id' => $this->getId()));
	} // getDeleteUrl

	/**
	 * Return delete task list URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteListUrl() {
		return get_url('task', 'delete_task', array('id' => $this->getId()));
	} // getDeleteUrl

	/**
	 * Return comete task URL
	 *
	 * @access public
	 * @param string $redirect_to Redirect to this URL (referer will be used if this URL is not provided)
	 * @return string
	 */
	function getCompleteUrl($redirect_to = null) {
		$params = array(
        'id' => $this->getId()
		); // array

		if(trim($redirect_to)) {
			$params['redirect_to'] = $redirect_to;
		} // if

		return get_url('task', 'complete_task', $params);
	} // getCompleteUrl

	/**
	 * Return open task URL
	 *
	 * @access public
	 * @param string $redirect_to Redirect to this URL (referer will be used if this URL is not provided)
	 * @return string
	 */
	function getOpenUrl($redirect_to = null) {
		$params = array(
        'id' => $this->getId()
		); // array

		if(trim($redirect_to)) {
			$params['redirect_to'] = $redirect_to;
		} // if

		return get_url('task', 'open_task', $params);
	} // getOpenUrl


	/**
	 * Return add task url
	 *
	 * @param boolean $redirect_to_list Redirect back to the list when task is added. If false
	 *   after submission user will be redirected to projects tasks page
	 * @return string
	 */
	function getAddTaskUrl($redirect_to_list = true) {
		$attributes = array('id' => $this->getId());
		if($redirect_to_list) {
			$attributes['back_to_list'] = true;
		} // if
		return get_url('task', 'add_task', $attributes);
	} // getAddTaskUrl

	/**
	 * Return reorder tasks URL
	 *
	 * @param boolean $redirect_to_list
	 * @return string
	 */
	function getReorderTasksUrl($redirect_to_list = true) {
		$attributes = array('task_list_id' => $this->getId());
		if($redirect_to_list) {
			$attributes['back_to_list'] = true;
		} // if
		return get_url('task', 'reorder_tasks', $attributes);
	} // getReorderTasksUrl
	 
	/**
	 * Return view list URL
	 *
	 * @param void
	 * @return string
	 */
	function getViewUrl() {
		return get_url('task', 'view', array('id' => $this->getId()));
	} // getViewUrl
	
	/**
	 * Return print URL
	 *
	 * @param void
	 * @return string
	 */
	function getPrintUrl() {
		return get_url('task', 'print_task', array('id' => $this->getId()));
	} // getViewUrl

	/**
	 * This function will return URL of this specific list on project tasks page
	 *
	 * @param void
	 * @return string
	 */
	function getOverviewUrl() {
		/*TODO re-implement
		$project = $this->getProject();
		if($project instanceof Project) {
			return $project->getTasksUrl() . '#taskList' . $this->getId();
		} // if*/
		return '';
	} // getOverviewUrl

	// ---------------------------------------------------
	//  System
	// ---------------------------------------------------

	/**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return null
	 */
	function validate(&$errors) {
		if(!$this->getObject()->validatePresenceOf('name')) $errors[] = lang('task title required');
		if(!$this->validateMinValueOf('percent_completed', 0)) $errors[] = lang('task percent completed must be greater than 0');
		//if(!$this->validateMaxValueOf('percent_completed', 100)) $errors[] = lang('task percent completed must be lower than 100');
	} // validate

	 
	/**
	 * Delete this task lists
	 *
	 * @access public
	 * @param boolean $delete_childs
	 * @return boolean
	 */
	function delete($delete_children = true) {
		if($delete_children)  {
			$children = $this->getSubTasks();
			foreach($children as $child) {
				$child->setDontMakeCalculations($this->getDontMakeCalculations());
				$child->delete(true);
			}
		}
		ProjectTaskDependencies::delete('( task_id = '. $this->getId() .' OR previous_task_id = '.$this->getId().')');
				
		$task_list = $this->getParent();
		if($task_list instanceof ProjectTask) $task_list->detachTask($this);
		return parent::delete();
	} // delete
	
	function trash($trash_children = true, $trashDate = null) {
		if (is_null($trashDate))
			$trashDate = DateTimeValueLib::now();
		if($trash_children)  {
			$children = $this->getAllSubTasks();
			foreach($children as $child) {
				$child->setDontMakeCalculations($this->getDontMakeCalculations());
				$child->trash(true,$trashDate);
			}
		}
		return parent::trash($trashDate);
	} // delete
	
	function archive($archive_children = true, $archiveDate = null) {
		if (is_null($archiveDate))
			$archiveDate = DateTimeValueLib::now();
		if($archive_children)  {
			$children = $this->getAllSubTasks();
			foreach($children as $child) {
				$child->setDontMakeCalculations($this->getDontMakeCalculations());
				$child->archive(true,$archiveDate);
			}
		}
		return parent::archive($archiveDate);
	} // delete

	/**
	 * Save this list
	 *
	 * @param void
	 * @return boolean
	 */
	function save() {
		if (!$this->isNew()) {
			$old_me = ProjectTasks::findById($this->getId(), true);
			if (!$old_me instanceof ProjectTask) return; // TODO: check this!!!
			// This was added cause deleting some tasks was giving an error, couldn't reproduce it again, but this solved it 
		}
		if ($this->isNew() ||
				$this->getAssignedToContactId() != $old_me->getAssignedToContactId()) {
			$this->setAssignedBy(logged_user());
			$this->setAssignedOn(DateTimeValueLib::now());
		}
		
		$due_date_changed = false;
		if (!$this->isNew()) {
			$old_due_date = $old_me->getDueDate();
			$due_date = $this->getDueDate();
			if ($due_date instanceof DateTimeValue) {
				if (!$old_due_date instanceof DateTimeValue || $old_due_date->getTimestamp() != $due_date->getTimestamp()) {
					$due_date_changed = true;
				}
			} else {
				if ($old_due_date instanceof DateTimeValue) {
					$due_date_changed = true;
				}
			}
		}
		
		//update Depth And Parents Path
		$parent_id_changed = false;
		$new_parent_id = $this->getParentId();
		if (!$this->isNew()) {
			$old_parent_id = $old_me->getParentId();			
			if($old_parent_id != $new_parent_id){				
				$this->updateDepthAndParentsPath($new_parent_id);
			}
		}else{
			$this->updateDepthAndParentsPath($new_parent_id);
		}
		
		parent::save();
		
		if ($due_date_changed) {
			$id = $this->getId();
			$sql = "UPDATE `".TABLE_PREFIX."object_reminders` SET
				`date` = date_sub((SELECT `due_date` FROM `".TABLE_PREFIX."project_tasks` WHERE `object_id` = $id),
					interval `minutes_before` minute) WHERE `object_id` = $id;";
			DB::execute($sql);
		}
		
		$old_parent_id = isset($old_me) && $old_me instanceof ProjectTask ? $old_me->getParentId() : 0;
		if ($this->isNew() || $old_parent_id != $new_parent_id) {
			//update Depth And Parents Path for subtasks
			$subtasks = $this->getSubTasks();
			if(is_array($subtasks)) {
				foreach($subtasks as $subtask) {
					$subtask->updateDepthAndParentsPath($this->getId());
					$subtask->save();
				} // if
			} // if
		}

		return true;

	} // save
	
	function updateDepthAndParentsPath($new_parent_id){
		if($new_parent_id > 0){
			//set Parents Path
			$parents_ids = array();
			$parent = $this->getParent();
			if(!$parent instanceof ProjectTask){
				return;
			}
			$stop = false;
			while (!$stop) {
				$parents_ids[] = $parent->getId();
				if($parent->getParentId() > 0){
					$parent = $parent->getParent();
					if (!$parent instanceof ProjectTask) {
						$stop = true;
					}
				}else{
					$stop = true;
				}
			}			
			$parents_path = implode(',', $parents_ids);
			$this->setParentsPath($parents_path);		
			
			//set Depth
			$this->setDepth(count($parents_ids));				
		}else{
			$this->setParentsPath('');
			$this->setDepth(0);
		}	
	}

	function unarchive($unarchive_children = true){
		$archiveTime = $this->getArchivedOn();
		parent::unarchive();
		if ($unarchive_children){
			$children = $this->getAllSubTasks();
			foreach($children as $child) {
				if ($child->isArchived() && $child->getArchivedOn()->getTimestamp() == $archiveTime->getTimestamp()) {
					$child->setDontMakeCalculations($this->getDontMakeCalculations());
					$child->unarchive(false);
				}
			}
		}
	}

	function untrash($untrash_children = true){
		$deleteTime = $this->getTrashedOn();
		parent::untrash();
		if ($untrash_children){
			$children = $this->getAllSubTasks();
			foreach($children as $child) {
				if ($child->isTrashed() && $child->getTrashedOn()->getTimestamp() == $deleteTime->getTimestamp()) {
					$child->setDontMakeCalculations($this->getDontMakeCalculations());
					$child->untrash(false);
				}
			}
		}
/* FIXME
		if ($this->hasOpenTimeslots()){
			$openTimeslots = $this->getOpenTimeslots();
			foreach ($openTimeslots as $timeslot){
				if (!$timeslot->isPaused()){
					$timeslot->setPausedOn($deleteTime);
					$timeslot->resume();
					$timeslot->save();
				}
			}
		}*/
	}

	/**
	 * Drop all tasks that are in this list
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function deleteSubTasks() {
		return ProjectTasks::delete(DB::escapeField('parent_id') . ' = ' . DB::escape($this->getId()));
	} // deleteTasks


	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------


	/**
	 * Return object URl
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getViewUrl();
	} // getObjectUrl
	
	/**
	 * Return object for task listing
	 *
	 * @return unknown
	 */
	function getDashboardObject(){
    	if($this->getUpdatedById() > 0 && $this->getUpdatedBy() instanceof Contact){
    		$updated_by_id = $this->getUpdatedBy()->getObjectId();
    		$updated_by_name = $this->getUpdatedByDisplayName();
			$updated_on = $this->getObjectUpdateTime() instanceof DateTimeValue ? ($this->getObjectUpdateTime()->isToday() ? format_time($this->getObjectUpdateTime()) : format_datetime($this->getObjectUpdateTime())) : lang('n/a');	
    	}else {
    		if($this->getCreatedBy())
    			$updated_by_id = $this->getCreatedBy()->getId();
    		else
    			$updated_by_id = lang('n/a');
    		$updated_by_name = $this->getCreatedByDisplayName();
			$updated_on = $this->getObjectCreationTime() instanceof DateTimeValue ? ($this->getObjectCreationTime()->isToday() ? format_time($this->getObjectCreationTime()) : format_datetime($this->getObjectCreationTime())) : lang('n/a');
    	}
    	if ($this instanceof ProjectTask)
    		$parent_id = $this->getParentId();
    	else 
    		$parent_id = $this->getId();
   	
		$deletedOn = $this->getTrashedOn() instanceof DateTimeValue ? ($this->getTrashedOn()->isToday() ? format_time($this->getTrashedOn()) : format_datetime($this->getTrashedOn(), 'M j')) : lang('n/a');
		if ($this->getTrashedById() > 0)
			$deletedBy = Contacts::findById($this->getTrashedById());
    	if (isset($deletedBy) && $deletedBy instanceof Contact) {
    		$deletedBy = $deletedBy->getObjectName();
    	} else {
    		$deletedBy = lang("n/a");
    	}
    	
		$archivedOn = $this->getArchivedOn() instanceof DateTimeValue ? ($this->getArchivedOn()->isToday() ? format_time($this->getArchivedOn()) : format_datetime($this->getArchivedOn(), 'M j')) : lang('n/a');
		if ($this->getArchivedById() > 0)
			$archivedBy = Contacts::findById($this->getArchivedById());
    	if (isset($archivedBy) && $archivedBy instanceof Contact) {
    		$archivedBy = $archivedBy->getObjectName();
    	} else {
    		$archivedBy = lang("n/a");
    	}
    		
    	return array(
				"id" => $this->getObjectTypeName() . $this->getId(),
				"object_id" => $this->getId(),
				"ot_id" => $this->getObjectTypeId(),
				"name" => $this->getObjectName(),
				"type" => $this->getObjectTypeName(),
				"tags" => project_object_tags($this),
				"createdBy" => $this->getCreatedByDisplayName(),
				"createdById" => $this->getCreatedById(),
    			"dateCreated" => $this->getObjectCreationTime() instanceof DateTimeValue ? ($this->getObjectCreationTime()->isToday() ? format_time($this->getObjectCreationTime()) : format_datetime($this->getObjectCreationTime())) : lang('n/a'),
				"updatedBy" => $updated_by_name,
				"updatedById" => $updated_by_id,
				"dateUpdated" => $updated_on,
				"url" => $this->getObjectUrl(),
				"parentId" => $parent_id,
				"status" => "Pending",
				"manager" => get_class($this->manager()),
    			"deletedById" => $this->getTrashedById(),
    			"deletedBy" => $deletedBy,
    			"dateDeleted" => $deletedOn,
    			"archivedById" => $this->getArchivedById(),
    			"archivedBy" => $archivedBy,
    			"dateArchived" => $archivedOn,
    			"isRead" => $this->getIsRead(logged_user()->getId())
			);
    }

    /**
	 * Returns true if the task has a subtask with id $id.
	 * 
	 * @param integer $id id to look for
	 * @return boolean
	 */
	function hasChild($id) {
		foreach ($this->getSubTasks() as $sub) {
			if ($sub->getId() == $id || $sub->hasChild($id)) {
				return true;
			}
		}
		return false;
	}
	
	
	/**
	 * Begin task templates
	 */
	function getAssignTemplateToWSUrl(){
		return get_url('administration','assign_task_template_to_ws',array('id'=> $this->getId()));
	}
	/**
	 * End task templates
	 */

	function getArrayInfo($full = false){
		$task = $this;
		$col_names = $task->getColumns();
		$ob_col_names = $task->getObject()->getColumns();
		$raw_data = array();
		
		foreach($ob_col_names as $ob_col_name) {
			$raw_data[$ob_col_name] = $task->getColumnValue($ob_col_name);
		}
		foreach($col_names as $col_name) {
			$raw_data[$col_name] = $task->getColumnValue($col_name);
		}
		
		foreach($raw_data as $key => $raw){
			if($raw instanceof DateTimeValue){
				$raw_data[$key] = $raw->toMySQL();
			}
		}
		
		//is read
		$raw_data['isread'] = $task->getIsRead(logged_user()->getId());
		return ProjectTasks::getArrayInfo($raw_data, $full);		
	}
	
	function isRepetitive() {
		return ($this->getRepeatForever() > 0 || $this->getRepeatNum() > 0 || 
			($this->getRepeatEnd() instanceof DateTimeValue && $this->getRepeatEnd()->toMySQL() != EMPTY_DATETIME) );
	}
	
	function getOpenTimeslots(){
		return Timeslots::instance()->getOpenTimeslotsByObject($this->getId());
	}
	
	/**
	 * Notifies the user of comments and due date of this task
	 *
	 * @param Contact $user
	 */
	function subscribeUser($user) {
		parent::subscribeUser($user);
	}
	
	/**
	 * Stops notifying user of comments and due date
	 *
	 * @param unknown_type $user
	 */
	function unsubscribeUser($user) {
		parent::unsubscribeUser($user);
		//ObjectReminders::clearByObject($this);
	}
	
	
	
	function apply_members_to_subtasks($members, $recursive = false) {
		if (!is_array($members) || count($members)==0) return;
		
		foreach ($this->getSubTasks() as $subtask) {
			$subtask->addToMembers($members);
			Hook::fire ('after_add_to_members', $subtask, $members);
			if ($recursive) {
				$subtask->apply_members_to_subtasks($members, $recursive);
			}
		}
	}


	function calculatePercentComplete() {
		if (!$this->isCompleted() && $this->getTimeEstimate() > 0){
			$total_time = 0;			
			$totalSeconds = Timeslots::getTotalSecondsWorkedOnObject($this->getId());
			
			$total_percentComplete = round(($totalSeconds * 100) / ($this->getTimeEstimate() * 60));
			if ($total_percentComplete < 0) $total_percentComplete = 0;
			
			$this->setPercentCompleted($total_percentComplete);
			$this->save();
		}
	}
	
} // ProjectTask
