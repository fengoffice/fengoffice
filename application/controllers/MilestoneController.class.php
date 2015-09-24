<?php

/**
 * Milestone controller
 *
 * @package Taus.application
 * @subpackage controller
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class MilestoneController extends ApplicationController {

	/**
	 * Construct the MilestoneController
	 *
	 * @access public
	 * @param void
	 * @return MilestoneController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct
	
	
	private function milestone_item(ProjectMilestone $milestone) {
		return array(
			"id" => $milestone->getId(),
			"title" => $milestone->getObjectName(),
			"completed" => $milestone->isCompleted(),
			"completedBy" => $milestone->getCompletedByName(),
			"isLate" => $milestone->isLate(),
			"daysLate" => $milestone->getLateInDays(),
			"duedate" => $milestone->getDueDate()->getTimestamp(),
			"urgent" => $milestone->getIsUrgent()
		);
	}
	
	/**
	 * Show view milestone page
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function view() {
		$this->addHelper('textile');

		$milestone = ProjectMilestones::findById(get_id());
		if(!($milestone instanceof ProjectMilestone)) {
			flash_error(lang('milestone dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$milestone->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		ajx_extra_data(array("title" => $milestone->getObjectName(), "urgent" => $milestone->getIsUrgent() ,'icon'=>'ico-milestone'));
		ajx_set_no_toolbar(true);
		tpl_assign('milestone', $milestone);
		
		ApplicationReadLogs::createLog($milestone, ApplicationReadLogs::ACTION_READ);
	} // view

	/**
	 * Show and process add milestone form
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function add() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add_milestone');
		
		$notAllowedMember = '' ;
		if(!ProjectMilestone::canAdd(logged_user(), active_context(), $notAllowedMember)) {
			if (str_starts_with($notAllowedMember, '-- req dim --')) flash_error(lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in)));
			else trim($notAllowedMember) == "" ? flash_error(lang('you must select where to keep', lang('the milestone'))) : flash_error(lang('no context permissions to add',lang("milestones"),$notAllowedMember));
			ajx_current("empty");
			return;
		} 

		$milestone_data = array_var($_POST, 'milestone');
		
		if(!is_array($milestone_data)) {
			// set layout for modal form
			if (array_var($_REQUEST, 'modal')) {
				$this->setLayout("json");
				tpl_assign('modal', true);
			}
			$milestone_data = array(
				'due_date' => '',
				'name' => array_var($_GET, 'name', ''),
				'assigned_to' => array_var($_GET, 'assigned_to', '0'),
				'is_template' => array_var($_GET, "is_template", false)
			); // array
		} // if
		
		//is template milestone?
		if(array_var($_REQUEST, 'template_milestone') == true){
			$milestone = new TemplateMilestone();
			$this->setTemplate(get_template_path('add_template_milestone', 'template_milestone'));
		
		}else{
			$milestone = new ProjectMilestone();
		}
		
		
		tpl_assign('milestone_data', $milestone_data);
		tpl_assign('milestone', $milestone);

		if (is_array(array_var($_POST, 'milestone'))) {
			$milestone_data['due_date'] = getDateValue(array_var($milestone_data, 'due_date_value'),DateTimeValueLib::now()->beginningOfDay());
			$milestone_data['object_type_id'] = $milestone->getObjectTypeId();
			
			$milestone->setFromAttributes($milestone_data);
			
			$urgent = array_var($milestone_data, 'is_urgent');
			$milestone->setIsUrgent($urgent);

			try {
				$member_ids = json_decode(array_var($_POST, 'members'));
				
				if($milestone instanceof TemplateMilestone){
					$milestone->setSessionId(logged_user()->getId());					
				}
				DB::beginWork();

				$milestone->save();
				$object_controller = new ObjectController();
			    $object_controller->add_to_members($milestone, $member_ids);
			    $object_controller->add_subscribers($milestone);
			    $object_controller->link_to_new_object($milestone);
				$object_controller->add_custom_properties($milestone);
				$object_controller->add_reminders($milestone);

				if (array_var($_GET, 'copyId', 0) > 0) {
					// copy remaining stuff from the milestone with id copyId
					$toCopy = ProjectMilestones::findById(array_var($_GET, 'copyId'));
					if ($toCopy instanceof ProjectMilestone) {
						ProjectMilestones::copyTasks($toCopy, $milestone, array_var($milestone_data, 'is_template', false));
					}
				}
				
				DB::commit();
				ApplicationLogs::createLog($milestone, ApplicationLogs::ACTION_ADD);
				
				//Send Template milestone to view
				if($milestone instanceof TemplateMilestone){
					$object = array(
							"action" => "add",
							"object_id" => $milestone->getObjectId(),
							"type" => $milestone->getObjectTypeName(),
							"id" => $milestone->getId(),
							"name" => $milestone->getObjectName(),
							"ico" => "ico-milestone",
							"manager" => get_class($milestone->manager())							
					);
					
					evt_add("template object added", array('object' => $object));
				}

				// Send notification
				try {
					if(!$milestone instanceof TemplateMilestone && array_var($milestone_data, 'send_notification')) {
						Notifier::milestoneAssigned($milestone); // send notification
					} // if
				} catch(Exception $e) {

				} // try

				
				$is_template = $milestone instanceof TemplateMilestone;
				
				if (array_var($_REQUEST, 'modal')) {
					
					ajx_current("empty");
					$this->setLayout("json");
					$this->setTemplate(get_template_path("empty"));
					
					// reload milestone info because plugins may have updated some task info (for example: name prefix)
					if ($is_template) {
						$milestone = TemplateMilestones::findById($milestone->getId());
						$params = array('msg' => lang('success add milestone', $milestone->getObjectName()), 'milestone' => $milestone->getArrayInfo(), 'reload' => array_var($_REQUEST, 'reload'));
						if ($milestone instanceof TemplateMilestone) {
							$params = $object;
						}
						print_modal_json_response($params, true, array_var($_REQUEST, 'use_ajx'));
					} else {
						$milestone = ProjectMilestones::findById($milestone->getId());
						flash_success(lang('success add milestone', $milestone->getObjectName()));
						evt_add("reload current panel");
					}
					
					
				} else {
					if ($milestone instanceof TemplateMilestone) {
						flash_success(lang('success add template', $milestone->getObjectName()));
					} else {
						flash_success(lang('success add milestone', $milestone->getObjectName()));
					}
					if (array_var($task_data, 'inputtype') != 'taskview') {
						ajx_current("back");
					} else {
						ajx_current("reload");
					}
				}

			} catch(Exception $e) {
				DB::rollback();
				if (array_var($_REQUEST, 'modal')) {
					$this->setLayout("json");
					$this->setTemplate(get_template_path("empty"));
					print_modal_json_response(array('errorCode' => 1, 'errorMessage' => $e->getMessage()));
				} else {
					flash_error($e->getMessage());
				}
				ajx_current("empty");
			} // try
		} // if
	} // add

	/**
	 * Show and process edit milestone form
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add_milestone');
		
		if(array_var($_REQUEST, "template_milestone")){
			$milestone = TemplateMilestones::findById(get_id());
			$this->setTemplate(get_template_path('add_template_milestone', 'template_milestone'));
			if(!($milestone instanceof TemplateMilestone)) {
				flash_error(lang('milestone dnx'));
				ajx_current("empty");
				return;
			} // if
		}else{
			$milestone = ProjectMilestones::findById(get_id());
			if(!($milestone instanceof ProjectMilestone)) {
				flash_error(lang('milestone dnx'));
				ajx_current("empty");
				return;
			} // if
		}
		if(!$milestone->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}

		$milestone_data = array_var($_POST, 'milestone');
		if(!is_array($milestone_data)) {
			// set layout for modal form
			if (array_var($_REQUEST, 'modal')) {
				$this->setLayout("json");
				tpl_assign('modal', true);
			}
			$milestone_data = array(
	          'name'        => $milestone->getObjectName(),
	          'due_date'    => $milestone->getDueDate(),
	          'description' => $milestone->getDescription(),
	          'is_urgent' 	=> $milestone->getIsUrgent()
			); // array
		} // if

		tpl_assign('milestone_data', $milestone_data);
		tpl_assign('milestone', $milestone);

		if(is_array(array_var($_POST, 'milestone'))) {
			if (array_var($milestone_data, 'due_date_value') != ''){
				$milestone_data['due_date'] = getDateValue(array_var($milestone_data, 'due_date_value'));
			} else {
				$now = DateTimeValueLib::now();
				$milestone_data['due_date'] = DateTimeValueLib::make(0, 0, 0, $now->getMonth(), $now->getDay(), $now->getYear());
			}
			
			$milestone->setFromAttributes($milestone_data);
			$urgent = array_var($milestone_data, 'is_urgent');
			$milestone->setIsUrgent($urgent);

			try {
				$member_ids = json_decode(array_var($_POST, 'members'));
				
				DB::beginWork();
				$milestone->save();
				
				$object_controller = new ObjectController();
				$object_controller->add_to_members($milestone, $member_ids);
			    $object_controller->add_subscribers($milestone);
			    $object_controller->link_to_new_object($milestone);
				$object_controller->add_custom_properties($milestone);
				$object_controller->add_reminders($milestone);
			    
				DB::commit();
				ApplicationLogs::createLog($milestone, ApplicationLogs::ACTION_EDIT);
				
				//Send Template milestone to view
				if($milestone instanceof TemplateMilestone){
					$object = array(
							"action" => "edit",
							"object_id" => $milestone->getObjectId(),
							"type" => $milestone->getObjectTypeName(),
							"id" => $milestone->getId(),
							"name" => $milestone->getObjectName(),
							"ico" => "ico-milestone",
							"manager" => get_class($milestone->manager())
					);
						
					evt_add("template object added", array('object' => $object));
				}
				
				$is_template = $milestone instanceof TemplateMilestone;
				if (array_var($_REQUEST, 'modal')) {
						
					ajx_current("empty");
					$this->setLayout("json");
					$this->setTemplate(get_template_path("empty"));
						
					// reload milestone info because plugins may have updated some task info (for example: name prefix)
					if ($is_template) {
						$milestone = TemplateMilestones::findById($milestone->getId());
						$params = array('msg' => lang('success edit milestone', $milestone->getObjectName()), 'milestone' => $milestone->getArrayInfo(), 'reload' => array_var($_REQUEST, 'reload'));
						if ($milestone instanceof TemplateMilestone) {
							$params = $object;
						}
						print_modal_json_response($params, true, array_var($_REQUEST, 'use_ajx'));
					} else {
						$milestone = ProjectMilestones::findById($milestone->getId());
						flash_success(lang('success edit milestone', $milestone->getObjectName()));
						evt_add("reload current panel");
					}
						
						
				} else {
					if ($milestone instanceof TemplateMilestone) {
						flash_success(lang('success edit template', $milestone->getObjectName()));
					} else {
						flash_success(lang('success edit milestone', $milestone->getObjectName()));
					}
					if (array_var($task_data, 'inputtype') != 'taskview') {
						ajx_current("back");
					} else {
						ajx_current("reload");
					}
				}
				/*
				flash_success(lang('success edit milestone', $milestone->getObjectName()));
				if (array_var($_REQUEST, 'modal')) {
					evt_add("reload current panel");
				}
				ajx_current("back");*/

			} catch(Exception $e) {
				DB::rollback();
				if (array_var($_REQUEST, 'modal')) {
					$this->setLayout("json");
					$this->setTemplate(get_template_path("empty"));
					print_modal_json_response(array('errorCode' => 1, 'errorMessage' => $e->getMessage()));
				} else {
					flash_error($e->getMessage());
				}
				ajx_current("empty");
			} // try
		} // if
	} // edit

	/**
	 * Delete single milestone
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function delete() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$milestone = ProjectMilestones::findById(get_id());
		if(!($milestone instanceof ProjectMilestone)) {
			flash_error(lang('milestone dnx'));
			return;
		} // if

		if(!$milestone->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if

		try {
			DB::beginWork();
			$milestone->trash();
			DB::commit();
			ApplicationLogs::createLog($milestone, ApplicationLogs::ACTION_TRASH);
			
			if ($is_template) {
				flash_success(lang('success delete template', $milestone->getObjectName()));
			} else {
				flash_success(lang('success deleted milestone', $milestone->getObjectName()));
			}
			if (array_var($_GET, 'quick', false)) {
				ajx_current('empty');
			} else {
				ajx_current('back');
			}
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete milestone'));
		} // try
	} // delete

	/**
	 * Complete specific milestone
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function complete() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$milestone = ProjectMilestones::findById(get_id());
		if(!($milestone instanceof ProjectMilestone)) {
			flash_error(lang('milestone dnx'));
			return;
		} // if

		if(!$milestone->canChangeStatus(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if

		try {

			$milestone->setCompletedOn(DateTimeValueLib::now());
			$milestone->setCompletedById(logged_user()->getId());

			DB::beginWork();
			$milestone->save();
			DB::commit();
			ApplicationLogs::createLog($milestone, ApplicationLogs::ACTION_CLOSE);
			
			flash_success(lang('success complete milestone', $milestone->getObjectName()));
			$redirect_to = array_var($_GET, 'redirect_to', false);
			if (array_var($_GET, 'quick', false)) {
				ajx_current("empty");
			} else {
				ajx_current("reload");
			}
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error complete milestone'));
		} // try

	} // complete

	/**
	 * Open specific milestone
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function open() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$milestone = ProjectMilestones::findById(get_id());
		if(!($milestone instanceof ProjectMilestone)) {
			flash_error(lang('milestone dnx'));
			return;
		} // if

		if(!$milestone->canChangeStatus(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if

		try {

			$milestone->setCompletedOn(null);
			$milestone->setCompletedById(0);

			DB::beginWork();
			$milestone->save();
			DB::commit();
			ApplicationLogs::createLog($milestone, ApplicationLogs::ACTION_OPEN);
			
			flash_success(lang('success open milestone', $milestone->getObjectName()));
			$redirect_to = array_var($_GET, 'redirect_to', false);
			if (array_var($_GET, 'quick', false)) {
				ajx_current("empty");
			} else {
				ajx_current("reload");
			}
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error open milestone'));
		} // try

	} // open

	/**
	 * Copy milestone
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function copy_milestone() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$notAllowedMember = '';
		if(!ProjectMilestone::canAdd(logged_user(), active_context(), $notAllowedMember)) {
			if (str_starts_with($notAllowedMember, '-- req dim --')) flash_error(lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in)));
			else trim($notAllowedMember) == "" ? flash_error(lang('you must select where to keep', lang('the milestone'))) : flash_error(lang('no context permissions to add',lang("milestones"),$notAllowedMember));
			ajx_current("empty");
			return;
		} // if
		
		$id = get_id();
		$milestone = ProjectMilestones::findById($id);
		if (!$milestone instanceof ProjectMilestone) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$milestone_data = array(
			'name' => $milestone instanceof TemplateMilestone ? $milestone->getObjectName() : lang("copy of", $milestone->getObjectName()),
			'description' => $milestone->getDescription(),
			'copyId' => $milestone->getId(),
		); // array
		if ($milestone->getDueDate() instanceof DateTimeValue) {
			$milestone_data['due_date'] = $milestone->getDueDate()->getTimestamp();
		}

		$newmilestone = new ProjectMilestone();
		tpl_assign('milestone_data', $milestone_data);
		tpl_assign('milestone', $newmilestone);
		tpl_assign('base_milestone', $milestone);
		$this->setTemplate("add_milestone");
	} // copy_milestone
	
		
	/**
	 * Create a new milestone template
	 *
	 */
	function new_template() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$notAllowedMember = '';
		if(!ProjectMilestone::canAdd(logged_user(), active_context(),$notAllowedMember)) {
			if (str_starts_with($notAllowedMember, '-- req dim --')) flash_error(lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in)));
			else trim($notAllowedMember) == "" ? flash_error(lang('you must select where to keep', lang('the milestone'))) : flash_error(lang('no context permissions to add',lang("milestones"),$notAllowedMember));
			ajx_current("empty");
			return;
		} // if
		
		$id = get_id();
		$milestone = ProjectMilestones::findById($id);
		if (!$milestone instanceof ProjectMilestone) {
			$milestone_data = array('is_template' => true);
		} else {
			$milestone_data = array(
				'name' => $milestone->getObjectName(),
				'description' => $milestone->getDescription(),
				'copyId' => $milestone->getId(),
				'is_template' => true,
			); // array
			if ($milestone->getDueDate() instanceof DateTimeValue) {
				$milestone_data['due_date'] = $milestone->getDueDate()->getTimestamp();
			}
		}

		$milestone = new ProjectMilestone();
		tpl_assign('milestone_data', $milestone_data);
		tpl_assign('milestone', $milestone);
		$this->setTemplate("add_milestone");
	} // new_template
	
	function change_due_date() {
		$milestone = ProjectMilestones::findById(get_id());
		if(!$milestone->canEdit(logged_user())){	    	
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
	    }
	    
	    $year = array_var($_GET, 'year', $milestone->getDueDate()->getYear());
	    $month = array_var($_GET, 'month', $milestone->getDueDate()->getMonth());
	    $day = array_var($_GET, 'day', $milestone->getDueDate()->getDay());
	    try {
	    	DB::beginWork();
	    	$milestone->setDueDate(new DateTimeValue(mktime(0, 0, 0, $month, $day, $year)));
	    	$milestone->save();
	    	DB::commit();
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error change due date milestone'));
		} // try
	    ajx_current("empty");
	}
	
	function render_add_milestone() {
		$genid = array_var($_GET, 'genid', '');				
		tpl_assign('genid', $genid);
		$context = build_context_array(array_var($_GET, 'context', ''));
		tpl_assign('context', $context);
		$task_data = array('milestone_id' => array_var($_GET, 'selected'));
		tpl_assign('task_data', $task_data);
		$this->setLayout("html");
		$this->setTemplate("add_select_milestone");	
	}
	
	/**
	 * Returns the milestones included in the present workspace and all of its parents. This is because tasks from a particular workspace
	 * can only be assigned to milestones from that workspace and from any of its parents.
	 */
	function get_assignable_milestones() {
		ajx_current("empty");
		$ms = ProjectMilestones::findAll();
		if ($ms === null) $ms = array();
		$ms_info = array();
		foreach ($ms as $milestone) {
			$ms_info[] = $milestone->getArrayInfo();
		}
		ajx_extra_data(array('milestones' => $ms_info));
	}
	
} // MilestoneController

?>