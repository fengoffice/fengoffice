<?php

/**
 * ProjectMilestone class
 * Generated on Sat, 04 Mar 2006 12:50:11 +0100 by DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ProjectMilestone extends BaseProjectMilestone {

	/**
	 * Array of searchable columns
	 *
	 * @var array
	 */
	protected $searchable_columns = array('name', 'description');

	/**
	 * Cached Contact object of person who completed this milestone
	 *
	 * @var Contact
	 */
	private $completed_by;
	
	/**
	 * Cache of open tasks
	 *
	 * @var array
	 */
	private $open_tasks;
	
	/**
	 * Cache of completed tasks
	 *
	 * @var array
	 */
	private $completed_tasks;
	
	/**
	 * Return if this milestone is completed
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function isCompleted() {
		if(is_null($this->getDueDate())) return false;
		return (boolean) $this->getCompletedOn();
	} // isCompleted

	/**
	 * Check if this milestone is late
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function isLate() {
		if($this->isCompleted()) return false;
		if(is_null($this->getDueDate())) return true;
		return !$this->isToday() && ($this->getDueDate()->getTimestamp() < time());
	} // isLate

	/**
	 * Check if this milestone is today
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function isToday() {
		$now = DateTimeValueLib::now();
		$due = $this->getDueDate();

		// getDueDate and similar functions can return NULL
		if(!($due instanceof DateTimeValue)) return false;

		return $now->getDay() == $due->getDay() &&
		$now->getMonth() == $due->getMonth() &&
		$now->getYear() == $due->getYear();
	} // isToday

	/**
	 * Return the name of the user that completed the milestone
	 *
	 */
	function getCompletedByName() {
		if (!$this->isCompleted()) {
			return '';
		} else {
			return $this->getCompletedBy()->getObjectName();
		}
	}
	
	/**
	 * Check if this is upcoming milestone
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function isUpcoming() {
		return /*!$this->isCompleted() && */!$this->isToday() && ($this->getLeftInDays() > 0);
	} // isUpcoming

	/**
	 * Return number of days that this milestone is late for
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getLateInDays() {
		$due_date_start = $this->getDueDate()->beginningOfDay();
		return floor(abs($due_date_start->getTimestamp() - DateTimeValueLib::now()->getTimestamp()) / 86400);
	} // getLateInDays

	/**
	 * Return number of days that is left
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getLeftInDays() {
		$due_date_start = $this->getDueDate()->endOfDay();
		return floor(abs($due_date_start->getTimestamp() - DateTimeValueLib::now()->beginningOfDay()->getTimestamp()) / 86400);
	} // getLeftInDays

	/**
	 * Return difference between specific datetime and due date time in seconds
	 *
	 * @access public
	 * @param DateTime $diff_to
	 * @return integer
	 */
	private function getDueDateDiff(DateTimeValue $diff_to) {
		return $this->getDueDate()->getTimestamp() - $diff_to->getTimestamp();
	} // getDueDateDiff

	// ---------------------------------------------------
	//  Related object
	// ---------------------------------------------------

	
	/**
	 * Return all tasks connected with this milestone
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getTasks($is_template = false) {
		//FIXME check permissions here
		return ProjectTasks::findAll(array(
	        'conditions' => '`is_template` = '.( $is_template ? '1' : '0') .' AND `milestone_id` = ' . DB::escape($this->getId()). " AND `trashed_on` = 0 ",
	        'order' => 'created_on'
        )); // findAll
	} // getTasks

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
          'conditions' => '`milestone_id` = ' . DB::escape($this->getId()) . ' AND `trashed_on` = 0 AND `completed_on` = ' . DB::escape(EMPTY_DATETIME),
          'order' => '`order`, `created_on`' 
          )); // findAll
		} // if

		return $this->open_tasks;
	} // getOpenSubTasks
	
	
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
          'conditions' => '`milestone_id` = ' . DB::escape($this->getId()) . ' AND `trashed_on` = 0 AND `completed_on` > ' . DB::escape(EMPTY_DATETIME),
          'order' => '`completed_on` DESC'
          )); // findAll
		} // if

		return $this->completed_tasks;
	} // getCompletedTasks
	
	/**
	 * Return Contact object of person who completed this milestone
	 *
	 * @param void
	 * @return Contact
	 */
	function getCompletedBy() {
		if ($this->isCompleted()){
			if(is_null($this->completed_by)) $this->completed_by = Contacts::findById($this->getCompletedById());
			return $this->completed_by;
		} else return null;
	} // getCompletedBy

	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	function canAdd(Contact $user, $context, &$notAllowedMember = ''){
		return can_add($user, $context, ProjectMilestones::instance()->getObjectTypeId(),$notAllowedMember);
	}
	
	
	/**
	 * Returns true if $contact can view this milestone
	 *
	 * @param Contact $contact
	 * @return boolean
	 */
	function canView(Contact $user) {
		return can_read($user, $this->getMembers(), $this->getObjectTypeId());
	} // canView

	/**
	 * Check if specific user can edit this milestone
	 *
	 * @access public
	 * @param Contact $contact
	 * @return boolean
	 */
	function canEdit(Contact $user) {
		return can_write($user, $this->getMembers(), $this->getObjectTypeId());
	} // canEdit

	/**
	 * Can chagne status of this milestone (completed / open)
	 *
	 * @access public
	 * @param Contact $contact
	 * @return boolean
	 */
	function canChangeStatus(Contact $contact) {
		return can_write($contact, $this->getMembers(), $this->getObjectTypeId());
	} // canChangeStatus

	/**
	 * Check if specific user can delete this milestone
	 *
	 * @access public
	 * @param Contact $contact
	 * @return boolean
	 */
	function canDelete(Contact $contact) {
		return can_delete($contact,$this->getMembers(), $this->getObjectTypeId());
	} // canDelete

	// ---------------------------------------------------
	//  URL
	// ---------------------------------------------------

	function getViewUrl() {
		return get_url('milestone', 'view', array('id' => $this->getId()));
	} // getViewUrl

	/**
	 * Return edit milestone URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return get_url('milestone', 'edit', array('id' => $this->getId()));
	} // getEditUrl

	/**
	 * Return delete milestone URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('milestone', 'delete', array('id' => $this->getId()));
	} // getDeleteUrl

	/**
	 * Return complete milestone url
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getCompleteUrl($redirect_to = '') {
		$params = array(
        	'id' => $this->getId()
		);
		if (trim($redirect_to) != '') {
			$params["redirect_to"] = $redirect_to;
		}
		return get_url('milestone', 'complete', $params);
	} // getCompleteUrl
	

	/**
	 * Return open milestone url
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getOpenUrl($redirect_to = '') {
		$params = array(
        	'id' => $this->getId()
		);
		if (trim($redirect_to) != '') {
			$params["redirect_to"] = $redirect_to;
		}
		return get_url('milestone', 'open', $params);
	} // getOpenUrl


	/**
	 * Return add task list URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getAddTaskUrl() {
		return get_url('task', 'add_task', array('milestone_id' => $this->getId()));
	} // getAddTaskUrl

	// ---------------------------------------------------
	//  System functions
	// ---------------------------------------------------

	/**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return boolean
	 */
	function validate(&$errors) {
		if(!$this->getObject()->validatePresenceOf('name')) $errors[] = lang('milestone name required');
		if(!$this->validatePresenceOf('due_date')) $errors[] = lang('milestone due date required');
	} // validate

	/**
	 * Delete this object and reset all relationship. This function will not delete any of related objec
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	
	function save() {
		parent::save();
		/*if ($this->getDueDate() instanceof DateTimeValue) {
			$id = $this->getId();
			$sql = "UPDATE `".TABLE_PREFIX."object_reminders` SET
				`date` = date_sub((SELECT `due_date` FROM `".TABLE_PREFIX."project_milestones` WHERE `id` = $id),
					interval `minutes_before` minute) WHERE
					`object_manager` = 'ProjectMilestones' AND `object_id` = $id;";
			DB::execute($sql);
		}*/
	}
	
	function delete() {
		try {
			DB::execute("UPDATE " . ProjectTasks::instance()->getTableName(true) . " SET `milestone_id` = '0' WHERE `milestone_id` = " . DB::escape($this->getId()));
			return parent::delete();
		} catch(Exception $e) {
			throw $e;
		} // try

	} // delete

	/**
	 * Trash this object and reset all relationship. This function will not trash any of related objects
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function trash($trashDate = null) {
		try {
			DB::execute("UPDATE " . ProjectTasks::instance()->getTableName(true) . " SET `milestone_id` = '0' WHERE `milestone_id` = " . DB::escape($this->getId()));
			return parent::trash($trashDate);
		} catch(Exception $e) {
			throw $e;
		} // try
		

	} // trash
	
	
	/**
	 * Moves the tasks that do not comply with the following rule: Tasks of a milestone must belong to its workspace or any of its subworkspaces.
	 * 
	 * @param Member $newMember The new member
	 * @return unknown_type
	 */
	function move_inconsistent_tasks(Member $newMember){
		return;
	}
	
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

	function getTitle() {
		return $this->getObjectName();
	}
	
	function getArrayInfo(){
		
		$info = ProjectMilestones::getMilestonesInfo($this->getId());
		$tnum = array_var($info, 'tnum', 0);
		$tc = array_var($info, 'tc', 0);
		
		$result = array(
			'id' => $this->getId(),
			't' => $this->getTitle(),
			'tnum' => $tnum,
			'tc' => $tc,
			'dd' => $this->getDueDate()->getTimestamp()
		);
		
		if ($this->getCompletedById() > 0){
			$result['compId'] = $this->getCompletedById();
			$result['compOn'] = $this->getCompletedOn()->getTimestamp();
		}
		
		$result['is_urgent'] = $this->getIsUrgent();
		
		return $result;
	}
	
} // ProjectMilestone

?>