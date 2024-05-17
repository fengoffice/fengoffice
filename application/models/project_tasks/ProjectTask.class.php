<?php
 
/**
 * ProjectTask class
 * Generated on Sat, 04 Mar 2006 12:50:11 +0100 by DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 * Modif: Marcos Saiz <marcos.saiz@gmail.com> 24/3/08
 */
class ProjectTask extends BaseProjectTask {

	/**
	 * set to false when completing or reopening a task, no need to recalculate the parents path in that actions
	 * 
	 * @var boolean
	 */
	protected $update_parents_path = true;

	protected $searchable_columns = array('name', 'text');
		
	protected $allow_timeslots = true;
	
	public $timeslots_count = 0 ;
	
	public $timeslots = null ;

	public $dont_calculate_financials = false;
	public $dont_calculate_project_financials = false;

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
			$this->milestone = ProjectMilestones::instance()->findById($this->getMilestoneId());
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
		$parent = ProjectTasks::instance()->findById($this->getParentId());
		return $parent instanceof ProjectTask  ? $parent : null;
	} // getParent
	
	/**
	 * Return all parent tasks that this task belongs to
	 *
	 * @param void
	 * @return array of ProjectTasks objects or empty array
	 */
	function getAllParents() {
		$parents = array();
		$current_task = $this;
		while($current_task->getParentId()!=0) {
			$parent = ProjectTasks::instance()->findById($current_task->getParentId());
			if($parent instanceof ProjectTask) {
				$parents[] = $parent;
				$current_task = $parent;
			} else {
				break;
			}
		}
		return $parents;
	} // getAllParents

	/**
	 * Return the user that last assigned the task
	 *
	 * @access public
	 * @param void
	 * @return Contact
	 */
	function getAssignedBy() {
		return Contacts::instance()->findById($this->getAssignedById());
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
			$ret = Contacts::instance()->findById($this->getAssignedToContactId());
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
	 * Returns true if this task is mark as started
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function isMarkedAsStarted() {
	    return $this->getMarkAsStarted();
	} // isMarkedAsStarted
	
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
		return !$this->isToday() && ($this->getDueDate()->getTimestamp() < DateTimeValueLib::now()->add('s', Timezones::getTimezoneOffsetToApply($this))->getTimestamp());
	} // isLate
	
	/**
	 * Check if this task is today
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function isToday() {
		$now = DateTimeValueLib::now()->add('s', logged_user()->getUserTimezoneValue());
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
		
		$tz_offset = Timezones::getTimezoneOffsetToApply($this);
		
		$due_date_start = $this->getDueDate();

        if($this->getUseDueTime()) {
            $due_date_start->add('s', $tz_offset);
        }
		$today = DateTimeValueLib::now();
		$today = $today->add('s', $tz_offset);
		
		return abs(floor($due_date_start->getTimestamp() / 86400) - floor($today->getTimestamp() / 86400));
	} // getLateInDays
	
	function getLeftInDays() {
		if (!$this->getDueDate() instanceof DateTimeValue) return 0;
		
		$tz_offset = Timezones::getTimezoneOffsetToApply($this);
		
		$due_date_start = $this->getDueDate();

        if($this->getUseDueTime()) {
            $due_date_start->add('s', $tz_offset);
        }

		$today = DateTimeValueLib::now();
		$today = $today->add('s', $tz_offset);
		
		return abs(floor($due_date_start->getTimestamp() / 86400) - floor($today->getTimestamp() / 86400));
	}
	
	
	function getDescription() {
		return $this->getText();
	}

	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	static function canAdd(Contact $user, $context, &$notAllowedMember = ''){
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
	 * @return boolean
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
	 * Check if specific user can change task status, complete or reopen
	 *
	 * @access public
	 * @param Contact $user
	 * @return boolean
	 */
	function canChangeStatus(Contact $user) {
		$continue_check = true;
		Hook::fire('can_change_task_status', array('task' => $this, 'user' => $user), $continue_check);
		if (!$continue_check) {
			return false;
		}
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
	//  ContentDataObject override
	// ---------------------------------------------------
	/**
	 * This event is triggered when we create a new timeslot
	 *
	 * @param Timeslot $timeslot
	 * @return boolean
	 */
	function onAddTimeslot(Timeslot $timeslot, $params = array()) {
		$params['total_worked_time_column'] = 'total_worked_time';
		return parent::onAddTimeslot($timeslot, $params);
	}
	
	/**
	 * This event is trigered when Timeslot that belongs to this object is updated
	 *
	 * @param Timeslot $timeslot
	 * @return boolean
	 */
	function onEditTimeslot(Timeslot $timeslot, $params = array()) {
		$params['total_worked_time_column'] = 'total_worked_time';
		return parent::onAddTimeslot($timeslot, $params);
	}
	
	/**
	 * This event is triggered when timeslot that belongs to this object is deleted
	 *
	 * @param Timeslot $timeslot
	 * @return boolean
	 */
	function onDeleteTimeslot(Timeslot $timeslot, $params = array()) {
		$params['total_worked_time_column'] = 'total_worked_time';
		return parent::onAddTimeslot($timeslot, $params);
	}
	
	
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
	function completeTask($options = array()) {
		if (!$this->canChangeStatus(logged_user())) {
			flash_error('no access permissions');
			ajx_current("empty");
			return;
		}
		
		$ret=null;
		Hook::fire("before_completing_task", array('task' => $this), $ret);
		
		$this->setCompletedOn(DateTimeValueLib::now());
		$this->setCompletedById(logged_user()->getId());

		
		if(user_config_option('close timeslot open')){
			$timeslots = Timeslots::instance()->getOpenTimeslotsByObject($this->getId());
			if ($timeslots){
				foreach ($timeslots as $timeslot){
					// to use when saving the application log
					$old_content_object = $timeslot->generateOldContentObjectData();

					if ($timeslot->isOpen())
					$timeslot->close();
					$timeslot->save();

					ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_EDIT, false, true);
				}
			}
		}

		// check if all previuos tasks are completed
		$log_info = "";
		$ignore_task_dependencies = array_var($options, 'ignore_task_dependencies');
		
		if (config_option('use tasks dependencies') && !$ignore_task_dependencies) {
			$saved_ptasks = ProjectTaskDependencies::instance()->findAll(array('conditions' => 'task_id = '. $this->getId()));
			foreach ($saved_ptasks as $pdep) {
				$ptask = ProjectTasks::instance()->findById($pdep->getPreviousTaskId());
				if ($ptask instanceof ProjectTask && !$ptask->isCompleted()) {
					throw new Exception(lang('previous tasks must be completed before completion of this task'));
				}
			}
			//Seeking the subscribers of the completed task not to repeat in the notifications
			$contact_notification = array();
			$task = ProjectTasks::instance()->findById($this->getId());
			foreach ($task->getSubscribers() as $task_sub){
				$contact_notification[] = $task_sub->getId();
			}
			//Send notification to subscribers of the task_dependency on the task completed
			$next_dependency = ProjectTaskDependencies::instance()->findAll(array('conditions' => 'previous_task_id = '. $this->getId()));
			foreach ($next_dependency as $ndep) {
				$ntask = ProjectTasks::instance()->findById($ndep->getTaskId());
				if ($ntask instanceof ProjectTask) {
					foreach ($ntask->getSubscribers() as $task_dep){
						if(!in_array($task_dep->getId(), $contact_notification))
						$log_info .= $task_dep->getId().",";
					}
				}
			}
		}
		
		$new_task = null;
		// if task is repetitive, generate a complete instance of this task and modify repeat values
		if ($this->isRepetitive()) {
			$task_controller = new TaskController();
			$complete_last_task = false;
			
			// calculate next repetition date
			$opt_rep_day = array('saturday' => false, 'sunday' => false);
			$new_dates = $task_controller->getNextRepetitionDates($this, $opt_rep_day, $new_st_date, $new_due_date, array());
		
			// if this is the last task of the repetetition, complete it, do not generate a new instance
			if ($this->getRepeatNum() > 0) {
				$this->setRepeatNum($this->getRepeatNum() - 1);
				if ($this->getRepeatNum() == 0) {
					$complete_last_task = true;
				}
			}
			if (!$complete_last_task && $this->getRepeatEnd() instanceof DateTimeValue) {
				if ($this->getRepeatBy() == 'start_date' && array_var($new_dates, 'st') > $this->getRepeatEnd() ||
					$this->getRepeatBy() == 'due_date' && array_var($new_dates, 'due') > $this->getRepeatEnd() ) {
		
					$complete_last_task = true;
				}
			}
		
			if (!$complete_last_task) {

				$new_st = array_var($new_dates, 'st');
				$new_due = array_var($new_dates, 'due');
				
				$daystoadd = 0;
				$move_direction = $this->getMoveDirectionNonWorkingDays() ? $this->getMoveDirectionNonWorkingDays() : 'advance';
				$params = array('task' => $this, 'new_st_date' => $new_st, 'new_due_date' => $new_due, 'move_direction' => $move_direction);
				Hook::fire('check_valid_repetition_date_days_add', $params, $daystoadd);
				
				if ($daystoadd != 0) {
					if ($new_st) $new_st->add('d', $daystoadd);
					if ($new_due) $new_due->add('d', $daystoadd);
				}

				// generate new pending task
				$new_task = $this->cloneTask($new_st, $new_due);
				
				// clean this task's repetition options
				$this->setRepeatEnd(EMPTY_DATETIME);
				$this->setRepeatForever(0);
				$this->setRepeatNum(0);
				$this->setRepeatD(0);
				$this->setRepeatM(0);
				$this->setRepeatY(0);
				$this->setRepeatBy("");
		
				ajx_current('reload');
			}
		}

		$this->setPercentCompleted(100);
		Hook::fire('calculate_executed_cost_and_price', array(), $this);
		$this->update_parents_path = false;
		$this->save();
		
		$null = null;
		Hook::fire("after_task_complete", array('task' => $this), $null);
		
		return array('log_info' => $log_info, 'new_task' => $new_task);
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
		$this->update_parents_path = false;

		$this->calculatePercentComplete();
		Hook::fire('calculate_executed_cost_and_price', array(), $this);
		$this->save();

		if($this->parent_id > 0){
			$parent_task = ProjectTasks::instance()->findById($this->parent_id);
			if($parent_task instanceof ProjectTask){
				$parent_task->calculatePercentComplete();
				Hook::fire('calculate_executed_cost_and_price', array(), $parent_task);
				$parent_task->save();
			}
		}

		$log_info = "";
		if (config_option('use tasks dependencies')) {
			//Seeking the subscribers of the open task not to repeat in the notifications
			$contact_notification = array();
			foreach ($this->getSubscribers() as $task_sub){
				$contact_notification[] = $task_sub->getId();
			}
			$saved_stasks = ProjectTaskDependencies::instance()->findAll(array('conditions' => 'previous_task_id = '. $this->getId()));
			foreach ($saved_stasks as $sdep) {
				$stask = ProjectTasks::instance()->findById($sdep->getTaskId());
				if ($stask instanceof ProjectTask && $stask->isCompleted()) {
					$stask->openTask();
				}
				foreach ($stask->getSubscribers() as $task_dep){
					if($task_dep && !in_array($task_dep->getId(), $contact_notification)) {
						$log_info .= $task_dep->getId().",";
					}
				}
			}
		}
		
		
		return $log_info;
	} // openTask

	function getRemainingDays(){
		if (is_null($this->getDueDate()))
			return null;
		else{
			$due = $this->getDueDate();
			$date = DateTimeValueLib::now()->add('s', logged_user()->getUserTimezoneValue())->getTimestamp();
			$nowDays = floor($date/(60*60*24));
			$dueDays = floor($due->getTimestamp()/(60*60*24));
			return $dueDays - $nowDays;
		}
	}
	
	function cloneTask($new_st_date='',$new_due_date='',$copy_status = false,$copy_repeat_options = true,$parent_subtask=0, $count = null) {

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
			if(is_null($count)){
				$new_task->setRepeatNum($this->getRepeatNum());
			}
			$new_task->setRepeatEnd($this->getRepeatEnd());
			$new_task->setMoveDirectionNonWorkingDays($this->getMoveDirectionNonWorkingDays());
			$new_task->setRepeatForever($this->getRepeatForever());
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
		
		$null = null;
		Hook::fire('task_clone_more_attributes', array('original' => $this, 'copy' => $new_task), $null);
		
		$new_task->save();
		
		
		// Copy members, linked_objects, custom_properties, subscribers, reminders and comments
		copy_additional_object_data($this, $new_task);

		// Ensure that assigned user is subscribed
		if ($new_task->getAssignedTo() instanceof Contact) {
			$new_task->subscribeUser($new_task->getAssignedTo());
		}
		
		$sub_tasks = $this->getAllSubTasks();
		foreach ($sub_tasks as $st) {
			$new_dates = $this->getNextRepetitionDatesSubtask($st,$new_task, $new_st_date, $new_due_date, $count);
			$daystoadd = 0;
			$params = array('task' => $new_task, 'new_st_date' => $new_st_date, 'new_due_date' => $new_due_date, 'move_direction' => $new_task->getMoveDirectionNonWorkingDays());
			Hook::fire('check_valid_repetition_date_days_add', $params, $daystoadd);
			if ($daystoadd != 0) {
				if ($new_st_date)
					$new_st_date->add('d', $daystoadd);
				if ($new_due_date)
					$new_due_date->add('d', $daystoadd);
			}
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
                
		

		Hook::fire('after_task_cloned', array('original' => $this, 'new_task' => $new_task), $new_task);
		
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
		$this->setMoveDirectionNonWorkingDays('advance');
	}
        
	private function getNextRepetitionDatesSubtask($subtask, $task, &$new_st_date, &$new_due_date, $count = null) {
		$new_due_date = null;
		$new_st_date = null;
		$count = is_null($count) ? 1 : $count + 1;       
		if ($subtask->getStartDate() instanceof DateTimeValue ) {
			$new_st_date = new DateTimeValue($subtask->getStartDate()->getTimestamp());
		}
		if ($subtask->getDueDate() instanceof DateTimeValue ) {
			$new_due_date = new DateTimeValue($subtask->getDueDate()->getTimestamp());
		}
		if ($task->getRepeatD() > 0) {
			if ($new_st_date instanceof DateTimeValue) {
				$new_st_date = $new_st_date->add('d', $task->getRepeatD()*$count);
			}                        
			if ($new_due_date instanceof DateTimeValue) {
				$new_due_date = $new_due_date->add('d', $task->getRepeatD()*$count);
			}
		} else if ($task->getRepeatM() > 0) {
			if ($new_st_date instanceof DateTimeValue) {
				$new_st_date = $new_st_date->add('M', $task->getRepeatM()*$count);
			}
			if ($new_due_date instanceof DateTimeValue) {
				$new_due_date = $new_due_date->add('M', $task->getRepeatM()*$count);
			}
		} else if ($task->getRepeatY() > 0) {
			if ($new_st_date instanceof DateTimeValue) {
				$new_st_date = $new_st_date->add('y', $task->getRepeatY()*$count);
			}
			if ($new_due_date instanceof DateTimeValue) {
				$new_due_date = $new_due_date->add('y', $task->getRepeatY()*$count);
			}
		}

		$correct_the_days = true;
		Hook::fire('check_working_days_to_correct_repetition', array('task' => $subtask), $correct_the_days);
		if ($correct_the_days) {
			$new_st_date = $this->correct_days_task_repetitive($new_st_date, $task);
			$new_due_date = $this->correct_days_task_repetitive($new_due_date, $task);
		}
		
		return array('st' => $new_st_date, 'due' => $new_due_date);
	}
        
        function correct_days_task_repetitive($date, $task){
            if($date != ""){
				$task_move_direction = $task->getMoveDirectionNonWorkingDays() ? $task->getMoveDirectionNonWorkingDays() : 'advance';
				$move_direction = $task_move_direction == 'advance' ? 1 : -1;
				$rep_fixed_days_str = trim($task->getColumnValue('repeat_fixed_days'));
				if ($rep_fixed_days_str != '') {
					$working_days = explode(",",$rep_fixed_days_str);
					$d = new DateTimeValue($date->getTimestamp());
					$d->add('s', $task->getTimezoneValue());
					$dw = $d->format('w');
					while(!in_array($dw, $working_days)){
						$date = $d->add('d', $move_direction);
						$dw = $d->format('w');
					}
					$date = $d->add('s', $task->getTimezoneValue() * (-1));
				}
            }
            return $date;
        }
	
    function changeMarkAsStarted(){
        if (!$this->canChangeStatus(logged_user())) {
            flash_error('no access permissions');
            ajx_current("empty");
            return;
        }
        
        $this->setMarkAsStarted(!$this->getMarkAsStarted());
        $this->save();
        
    }
	// ---------------------------------------------------
	//  TaskList Operations
	// ---------------------------------------------------

	/**
	 * Add subtask to this list
	 *
	 * @param string $text
	 * @param Contact $assigned_to_user
	 * @param Contact $assigned_to_company
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

		if($this->isCompleted()) $this->openTask();
	} // attachTask

	/**
	 * Detach subtask from this task
	 *
	 * @param ProjectTask $task
	 * @param ProjectTask $attach_to If you wish you can detach and attach task to
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
	function getSubTasks($include_trashed = true, $include_archived = true, $dont_get_from_cache = false) {
		$include = "";
		if(!$include_trashed){
			$include = "`trashed_by_id` = 0 AND ";
		}
		if(!$include_archived){
			$include .= "`archived_by_id` = 0 AND ";
		}
		if(is_null($this->all_tasks) || $dont_get_from_cache) {
			$this->all_tasks = ProjectTasks::instance()->findAll(array(
          'conditions' => $include.'`parent_id` = ' . DB::escape($this->getId()),
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
	function getSubTasksIds($extra_conditions = "") {
		$subtasks_ids = array();
		$condition = $extra_conditions . ' AND `parent_id` = ' . DB::escape($this->getId());
				
		$subtasks_rows = ProjectTasks::instance()->listing(array(
				"select_columns" => array("e.`object_id`"),
				"extra_conditions" => $condition,
				"count_results" => false,
				"fire_additional_data_hook" => false,
				"raw_data" => true,
		))->objects;
		
		if (is_array($subtasks_rows)) {
			for ($i = 0; $i < count($subtasks_rows); $i++){
				$subtasks_ids[] = (int)$subtasks_rows[$i]['object_id'];
			}
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
			$this->all_tasks = ProjectTasks::instance()->findAll(array(
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
	 * Return all subtask ids for the task
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getAllSubTasksIds($include_trashed = true) {
		$tasks = $this->getAllSubTasks($include_trashed);
		$result = array();
		for ($i = 0; $i < count($tasks); $i++){
			if(!$include_trashed && $tasks[$i]->isTrashed()) continue;
			$result[] = $tasks[$i]->getId();
		}
		return $result;
	} // getAllSubTasksIds
	
	
	
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
			$sub = ProjectTasks::instance()->findById($sub_id);
			if ($sub instanceof ProjectTask) {
				$sub->getAllSubtaskIdsInHierarchyRecursive($all_subtasks_ids);
			}
		}
	}
	
	/**
	 * Gets all subtasks info recursively
	 */
	function getAllSubtaskInfoInHierarchy($conditions = "") {
		$subtasks = array();
		$this->getAllSubtaskInfoInHierarchyRecursive($subtasks, 1, $conditions);
	
		return $subtasks;
	}
	
	/**
	 * Private function to get the subtasks info recursively
	 */
	private function getAllSubtaskInfoInHierarchyRecursive(&$subtasks, $depth=0, $conditions = "") {
		$subtasks_ids = $this->getSubTasksIds($conditions);
		foreach ($subtasks_ids as $sub_id) {
			$sub = ProjectTasks::instance()->findById($sub_id);
			if ($sub instanceof ProjectTask) {
				$subtasks[$sub_id] = $sub->getArrayInfo();
				$subtasks[$sub_id]['depth'] = $depth;
				$sub->getAllSubtaskInfoInHierarchyRecursive($subtasks, $depth+1, $conditions);
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
			$subtasks = ProjectTasks::instance()->findAll(array(
	          'conditions' => '`parent_id` = ' . DB::escape($this->getId()) . ' AND `completed_on` = ' . DB::escape(EMPTY_DATETIME) . ' AND `trashed_on` = ' . DB::escape(EMPTY_DATETIME),
	          'order' => '`order`, `created_on`'
	        )); // findAll
        	$this->open_tasks = is_null($subtasks) ? array() : $subtasks;
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
			$subtasks = ProjectTasks::instance()->findAll(array(
	          'conditions' => '`parent_id` = ' . DB::escape($this->getId()) . ' AND `completed_on` > ' . DB::escape(EMPTY_DATETIME),
	          'order' => '`completed_on` DESC'
	        )); // findAll
	        $this->completed_tasks = is_null($subtasks) ? array() : $subtasks;
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
				$this->count_all_tasks = ProjectTasks::instance()->count('`parent_id` = ' . DB::escape($this->getId()));
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
				$this->count_open_tasks = ProjectTasks::instance()->count('`parent_id` = ' . DB::escape($this->getId()) . ' AND `completed_on` = ' . DB::escape(EMPTY_DATETIME));
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
				$this->count_completed_tasks = ProjectTasks::instance()->count('`parent_id` = ' . DB::escape($this->getId()) . ' AND `completed_on` > ' . DB::escape(EMPTY_DATETIME));
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
			$this->related_forms = ProjectForms::instance()->findAll(array(
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
			$this->completed_by = Contacts::instance()->findById($this->getCompletedById());
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
				$this->completed_by = Contacts::instance()->findById($this->getCompletedById());
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
	function getEditListUrl($req_channel = '') {
		return get_url('task', 'edit_task', array('id' => $this->getId(), 'req_channel' => $req_channel));
	} // getEditUrl

	/**
	 * Return delete task URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl($req_channel = '') {
		return get_url('task', 'delete_task', array('id' => $this->getId(), 'req_channel' => $req_channel));
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
	function getCompleteUrl($redirect_to = null, $req_channel = null) {
		$params = array(
        'id' => $this->getId()
		); // array

		if(trim($redirect_to)) {
			$params['redirect_to'] = $redirect_to;
		} // if
		if(trim($req_channel)) {
			$params['req_channel'] = $req_channel;
		}

		return get_url('task', 'complete_task', $params);
	} // getCompleteUrl
	
	function getChangeMarkStartedUrl($redirect_to = null, $req_channel = null) {
	    $params = array(
	        'id' => $this->getId()
	    ); // array
	    
	    if(trim($redirect_to)) {
	        $params['redirect_to'] = $redirect_to;
	    } // if
		if(trim($req_channel)) {
			$params['req_channel'] = $req_channel;
		}
	    
	    return get_url('task', 'change_mark_as_started', $params);
	} // getCompleteUrl
	
	/**
	 * Return open task URL
	 *
	 * @access public
	 * @param string $redirect_to Redirect to this URL (referer will be used if this URL is not provided)
	 * @return string
	 */
	function getOpenUrl($redirect_to = null, $req_channel = null) {
		$params = array(
        'id' => $this->getId()
		); // array

		if(trim($redirect_to)) {
			$params['redirect_to'] = $redirect_to;
		} // if
		if(trim($req_channel)) {
			$params['req_channel'] = $req_channel;
		}

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
	function validate($errors) {
		if(!$this->getObject()->validatePresenceOf('name')) $errors[] = lang('task title required');
		if(!$this->validateMinValueOf('percent_completed', 0)) $errors[] = lang('task percent completed must be greater than 0');
		//if(!$this->validateMaxValueOf('percent_completed', 100)) $errors[] = lang('task percent completed must be lower than 100');
	} // validate


	/**
	 * Unlinks all the timeslots and expenses related to this task
	 */
	function unlinkRelatedObjects() {

		$timeslots = Timeslots::getTimeslotsByObject($this);
		foreach ($timeslots as $ts) {
			$old_content_object = $ts->generateOldContentObjectData();
			$ts->override_workflow_permissions = true;
			$ts->setRelObjectId(0);
			$ts->save();
			ApplicationLogs::createLog($ts, ApplicationLogs::ACTION_EDIT, false, true);
		}

		if (Plugins::instance()->isActivePlugin('expenses2')) {
			$b_expenses = Expenses::getBudgetedExpensesByTask($this->getId());
			$a_expenses = PaymentReceipts::getActualExpensesByTask($this->getId());
			$all_expenses = array_filter(array_merge($b_expenses, $a_expenses));

			foreach ($all_expenses as $exp) {
				$old_content_object = $exp->generateOldContentObjectData();
				$exp->setTaskId(0);
				$exp->save();
				ApplicationLogs::createLog($exp, ApplicationLogs::ACTION_EDIT, false, true);
			}
		}
	}
	 
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
		ProjectTaskDependencies::instance()->delete('( task_id = '. $this->getId() .' OR previous_task_id = '.$this->getId().')');
				
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
		$this->unlinkRelatedObjects();
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
			$old_me = ProjectTasks::instance()->findById($this->getId(), true);
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
			$old_parent_id = isset($old_me) && $old_me instanceof ProjectTask ? $old_me->getParentId() : 0;
			if($this->update_parents_path && $old_parent_id != $new_parent_id){
				$this->updateDepthAndParentsPath($new_parent_id);
			}
		}else{
			$this->updateDepthAndParentsPath($new_parent_id);
		}

		Hook::fire('set_non_billable_property_using_old_task_and_new_task', array('new_task' => $this, 'old_task' => $old_me ?? null), $this);

		parent::save();
		
		$this->calculateTotalTimeEstimate();
		$this->calculateAndSaveOverallTotalWorkedTime();

		Hook::fire('save_additional_data_in_related_members', array('object' => $this), $this);

		if ($due_date_changed) {
			$id = $this->getId();
			$sql = "UPDATE `".TABLE_PREFIX."object_reminders` SET
				`date` = date_sub((SELECT `due_date` FROM `".TABLE_PREFIX."project_tasks` WHERE `object_id` = $id),
					interval `minutes_before` minute) WHERE `object_id` = $id;";
			DB::execute($sql);
		}
		
		$old_parent_id = isset($old_me) && $old_me instanceof ProjectTask ? $old_me->getParentId() : 0;
		if ($this->isNew() || ($this->update_parents_path && $old_parent_id != $new_parent_id)) {
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

	function calculateTotalTimeEstimate() {
		$task_id = $this->getId();
		$total_time_estimate = $this->getTimeEstimate();

		// Get subtask's total time estimate
		$sql = "SELECT SUM(total_time_estimate) as subtasks_total_time_estimate 
				FROM ".TABLE_PREFIX."project_tasks pt 
				INNER JOIN ".TABLE_PREFIX."objects o ON o.id=pt.object_id 
				WHERE pt.parent_id=".$task_id." AND o.trashed_by_id=0 AND o.archived_by_id=0";
		
		$row = DB::executeOne($sql);
		$subtasks_total_minutes = array_var($row, 'subtasks_total_time_estimate', 0);
		$total_time_estimate += $subtasks_total_minutes;

		// Set total time estimate
		$sql = "UPDATE `".TABLE_PREFIX."project_tasks` SET `total_time_estimate` = $total_time_estimate WHERE `object_id` = $task_id;";
		DB::execute($sql);

		// Recalculate total time estimate for parent task
		$parent = $this->getParent();
		if($parent instanceof ProjectTask) {
			$parent->calculateTotalTimeEstimate();
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

	}

	/**
	 * Drop all tasks that are in this list
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function deleteSubTasks() {
		return ProjectTasks::instance()->delete(DB::escapeField('parent_id') . ' = ' . DB::escape($this->getId()));
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
	 * @return array
	 */
	function getDashboardObject(){
    	if($this->getUpdatedById() > 0 && $this->getUpdatedBy() instanceof Contact){
    		$updated_by_id = $this->getUpdatedBy()->getObjectId();
    		$updated_by_name = $this->getUpdatedByDisplayName();
			$updated_on = $this->getObjectUpdateTime() instanceof DateTimeValue ? ($this->getObjectUpdateTime()->isToday() ? format_time($this->getObjectUpdateTime()) : format_datetime($this->getObjectUpdateTime())) : lang('n/a');	
    	}else {
    		if($this->getCreatedBy())
    			$updated_by_id = $this->getCreatedById();
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
			$deletedBy = Contacts::instance()->findById($this->getTrashedById());
    	if (isset($deletedBy) && $deletedBy instanceof Contact) {
    		$deletedBy = $deletedBy->getObjectName();
    	} else {
    		$deletedBy = lang("n/a");
    	}
    	
		$archivedOn = $this->getArchivedOn() instanceof DateTimeValue ? ($this->getArchivedOn()->isToday() ? format_time($this->getArchivedOn()) : format_datetime($this->getArchivedOn(), 'M j')) : lang('n/a');
		if ($this->getArchivedById() > 0)
			$archivedBy = Contacts::instance()->findById($this->getArchivedById());
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
	 * Returns true if the task has one or more subtasks .
	 * 
	 * @return boolean
	 */
	function isParent($task_id = null) {
		
		if($task_id) {
			$task = ProjectTasks::instance()->findById($task_id);
		} else {
			$task = $this;
		}
		
		return count($task->getSubTasks()) > 0 ;
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

	function getArrayInfo($full = false, $include_members_data = false, $include_mem_path = true, $include_open_timeslots = true, $include_subtasks_ids = true){
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
		$raw_data['mark_as_started'] = $task->getMarkAsStarted();
		return ProjectTasks::getArrayInfo($raw_data, $full, $include_members_data, $include_mem_path, $include_open_timeslots, $include_subtasks_ids);
	}
	
	function isRepetitive() {
		return ($this->getRepeatForever() > 0 || $this->getRepeatNum() > 0 || 
			($this->getRepeatEnd() instanceof DateTimeValue && $this->getRepeatEnd()->toMySQL() != EMPTY_DATETIME) );
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
	 * @param Contact $user
	 */
	function unsubscribeUser($user) {
		parent::unsubscribeUser($user);
		//ObjectReminders::clearByObject($this);
	}
	
	
	
	function apply_members_to_subtasks($members, $recursive = false) {
		if (!is_array($members) || count($members)==0) return;

		$ignored_dimension_ids = config_option('ignored_dimensions_for_subtasks');
		
		foreach ($this->getSubTasks() as $subtask) {/* @var $subtask ProjectTask */

			// Check ignored dimensions to keep the ignored data when applying changes on subtask
			$keep_members = array();
			foreach($subtask->getMembers() as $st_member){
				if (in_array($st_member->getDimensionId(), $ignored_dimension_ids)) {
					$keep_members[] = $st_member;
				}
			}
			
			// Add ignored dimentions to initialization of subtask members
			$members_to_apply = array();
			foreach($keep_members as $km){
				$members_to_apply[] = $km;
			}

			// dont apply members of dimensions with single selection and subtask has already one of them
			foreach ($members as $m) {/* @var $m Member */
				$dim = $m->getDimension();
				$dotc = DimensionObjectTypeContents::instance()->findOne(array("conditions" => array(
					"dimension_id=? AND dimension_object_type_id=? AND content_object_type_id=?",
					$dim->getId(), $m->getObjectTypeId(), ProjectTasks::instance()->getObjectTypeId()
				)));
				if ($dotc->getIsMultiple()) {
					$members_to_apply[] = $m;
				} else {
					// check if subtask as one in this dimension
					$subtask_members = $subtask->getMembers();
					$has_one = false;
					foreach ($subtask_members as $sm) {
						if ($sm->getDimensionId() == $dim->getId()) {
							$has_one = true;
							break;
						}
					}
					if (!$has_one) {
						$members_to_apply[] = $m;
					}
				}
			}

			// classify subtask
			$subtask->addToMembers($members_to_apply); 
			Hook::fire ('after_add_to_members', $subtask, $members);
			if ($recursive) {
				$subtask->apply_members_to_subtasks($members, $recursive);
			}
		}
	}


	private function get_ignored_dimensions_when_reclassify_related_objects() {

		$ignored_dimension_ids = config_option("ignored_dims_task_related_objs") ?? [];

		$clients_dimension_id = 0;
		$projects_dimension_id = 0;
		if (Plugins::instance()->isActivePlugin('crpm')) {
			Env::useHelper('functions', 'crpm');
			$clients_dimension_id = get_customers_dimension()->getId();
			$projects_dimension_id = Dimensions::findByCode('customer_project')->getId();

			// don't allow to ignore clients or projects dimension in reclassification
			$ignored_dimension_ids = array_diff($ignored_dimension_ids, array($projects_dimension_id, $clients_dimension_id));
		}

		return $ignored_dimension_ids;
	}

	function override_related_objects_classification() {

		$ignored_dimension_ids = $this->get_ignored_dimensions_when_reclassify_related_objects();

		$task_members = $this->getMembers(false);

		// remove client and project dimension members of the members set to apply to other objects, also the ones that belong to the ignored dimensions config option
		$members_to_override = array();
		foreach ($task_members as $member) {
			if (!in_array($member->getDimensionId(), $ignored_dimension_ids)) {
				$members_to_override[] = $member;
			}
		}

		// get the related time entries
		$timeslots = $this->getTimeslots();
		foreach ($timeslots as $timeslot) {
			$old_content_object = $timeslot->generateOldContentObjectData();

			// override each time entry classification
			$this->override_related_object_classification($timeslot, $members_to_override);

		
			$timeslot->setForceRecalculateBilling(true);
			if (Plugins::instance()->isActivePlugin('advanced_billing')) {
				Env::useHelper('functions', 'advanced_billing');
				calculate_timeslot_rate_and_cost($timeslot);
			}
			// save log
			ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_EDIT, false, true);
		}


		if (Plugins::instance()->isActivePlugin('expenses2')) {

			$b_expenses = Expenses::getBudgetedExpensesByTask($this->getId());
			foreach ($b_expenses as $expense) {
				$old_content_object = $expense->generateOldContentObjectData();

				// override each time entry classification
				$this->override_related_object_classification($expense, $members_to_override);
	
				// save log
				ApplicationLogs::createLog($expense, ApplicationLogs::ACTION_EDIT, false, true);
			}

			$a_expenses = PaymentReceipts::getActualExpensesByTask($this->getId());
			foreach ($a_expenses as $expense) {
				$old_content_object = $expense->generateOldContentObjectData();

				// override each time entry classification
				$this->override_related_object_classification($expense, $members_to_override);
	
				// save log
				ApplicationLogs::createLog($expense, ApplicationLogs::ACTION_EDIT, false, true);
			}
		}

	}

	function override_related_object_classification(ContentDataObject $object, $members_to_override) {

		$ignored_dimension_ids = $this->get_ignored_dimensions_when_reclassify_related_objects() ?? [];

		$members_to_remove = array();
		foreach ($members_to_override as $m) {
			// check if we should ignore this member using the config option
			if (!in_array($m->getDimensionId(), $ignored_dimension_ids)) {
				// get the related object member of the same type
				$rel_obj_member = $object->getMemberOfType($m->getObjectTypeId());
				if ($rel_obj_member instanceof Member) {
					// if the related object is classified in a member of the same type we have to remove that classification
					$members_to_remove[] = $rel_obj_member->getId();
				}
			}
		}

		// remove classification in members that we are going to override
		if (count($members_to_remove) > 0) {
			ObjectMembers::removeObjectFromMembers($object, logged_user(), null, $members_to_remove, false);
		}

		// classify related object in task's members
		ObjectMembers::addObjectToMembers($object->getId(), $members_to_override);

	}


	function calculatePercentComplete($prevent_parent_update = false) {
		if (!$this->isCompleted() && !$this->getIsManualPercentCompleted()) {
			$task_id = $this->getId();
			$numerator = 0;
			$denominator = 0;

			$parent_time_estimate = $this->getTimeEstimate();
			if ($parent_time_estimate > 0) {
				$totalSeconds = Timeslots::getTotalSecondsWorkedOnObject($task_id);
				$parent_percent_completed = round(($totalSeconds * 100) / ($parent_time_estimate * 60));
				if($parent_percent_completed > 100) $parent_percent_completed = 100;
				$numerator = $parent_percent_completed * $parent_time_estimate;
				$denominator = $parent_time_estimate;
			} 

			$subtasks = $this->getSubTasks(false, false);
			foreach($subtasks as $subtask){
				$subtask_percent_completed_calculations = $subtask->calculateRecursiveSubtaskPercentCompleted();
				$numerator += $subtask_percent_completed_calculations['numerator'];
				$denominator += $subtask_percent_completed_calculations['denominator'];
			}

			$total_percent_completed = 0;
			if ($denominator > 0) {
				$total_percent_completed = round($numerator / $denominator);
			}

			if ($total_percent_completed < 0) $total_percent_completed = 0;
			
			$sql = "UPDATE `".TABLE_PREFIX."project_tasks` SET `percent_completed` = $total_percent_completed WHERE `object_id` = $task_id;";
			if($this instanceof ProjectTask) $this->setPercentCompleted($total_percent_completed);
			
			DB::execute($sql);		
		}
		if (!$prevent_parent_update) {
			$parent = $this->getParent();
			if($parent instanceof ProjectTask) {
				$parent->calculatePercentComplete();
			}
		}	
	}

	function calculateRecursiveSubtaskPercentCompleted() {
		$numerator = 0;
		$denominator = 0;

		$subtasks = $this->getSubTasks(false, false);
		if(count($subtasks) > 0) {
			$parent_time_estimate = $this->isCompleted() ? $this->getTotalTimeEstimate() : $this->getTimeEstimate();
			if ($parent_time_estimate > 0) {
				if ($this->isCompleted()) {
					$parent_percent_completed = 100;
				} else {
					$totalSeconds = Timeslots::getTotalSecondsWorkedOnObject($this->getId());
					$parent_percent_completed = round(($totalSeconds * 100) / ($parent_time_estimate * 60));
					if($parent_percent_completed > 100) $parent_percent_completed = 100;
				}
				$numerator = $parent_percent_completed * $parent_time_estimate;
				$denominator = $parent_time_estimate;
			} 

			if(!$this->isCompleted()) {
				$subtasks = $this->getSubTasks(false, false);
				foreach($subtasks as $subtask){
					$subtask_percent_completed_calculations = $subtask->calculateRecursiveSubtaskPercentCompleted();
					$numerator += $subtask_percent_completed_calculations['numerator'];
					$denominator += $subtask_percent_completed_calculations['denominator'];
				}
			}
		} else {
			$time_estimate = $this->getTimeEstimate();
			$task_percent_completed = $this->isCompleted() ? 100 : $this->getPercentCompleted();
			$percent_completed = $task_percent_completed > 100 ? 100 : $task_percent_completed;
			$numerator = $percent_completed * $time_estimate;
			$denominator = $time_estimate;
		}

		if ($denominator == 0) $numerator = 0;

		return array('numerator' => $numerator, 'denominator' => $denominator);

	}


	function calculateAndSaveOverallTotalWorkedTime() {
		$this->calculateAndSetOverallTotalWorkedTime();
		
		$parent = $this->getParent();
		if($parent instanceof ProjectTask) {
			$parent->calculateAndSaveOverallTotalWorkedTime();
		}
	}

	function calculateAndSetOverallTotalWorkedTime() {
		$sql = "SELECT (SUM(GREATEST(TIMESTAMPDIFF(MINUTE,start_time,end_time),0)) - SUM(subtract/60)) as overall_total_minutes 
				FROM ".TABLE_PREFIX."timeslots ts 
				INNER JOIN ".TABLE_PREFIX."objects o ON o.id=ts.object_id 
				WHERE ts.rel_object_id=".$this->getId()." AND o.trashed_by_id=0";
		
		$row = DB::executeOne($sql);
		$overall_total_minutes = array_var($row, 'overall_total_minutes', 0);

		$subtasks = $this->getSubTasks(false, false, true);
		$subtask_total_minutes = 0;
		foreach($subtasks as $subtask){
			$subtask_total_minutes += $subtask->getOverallWorkedTime();
		}
		$overall_total_minutes += $subtask_total_minutes;

		// Set total worked time
		$task_id = $this->getId();
		$sql = "UPDATE `".TABLE_PREFIX."project_tasks` SET `overall_worked_time_plus_subtasks` = $overall_total_minutes WHERE `object_id` = $task_id;";
		DB::execute($sql);
	}

	function changeInvoicingStatus($status) {
		if (ProjectTasks::instance()->columnExists('invoicing_status')) {
			// to use when saving the application log
			$old_content_object = $this->generateOldContentObjectData();

			$old_status = $this->getColumnValue('invoicing_status');
			
			$this->setColumnValue('invoicing_status', $status);
			if($status == 'pending') {
				$this->setColumnValue('invoice_id', 0);
			}
			$this->save();
			
			$ret = null;
			Hook::fire("after_change_object_inv_status", array('object' => $this, 'old_status' => $old_status), $ret);

			ApplicationLogs::createLog($this, ApplicationLogs::ACTION_EDIT, false, true);
		}
	}
	
} // ProjectTask
