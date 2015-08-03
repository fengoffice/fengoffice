<?php

/**
 * Controller for handling time management
 *
 * @version 1.0
 * @author Carlos Palma <chonwil@gmail.com>
 */
class TimeController extends ApplicationController {

	/**
	 * Construct the TimeController
	 *
	 * @access public
	 * @param void
	 * @return TimeController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct
	
	function index() {
		
		$tasksUserId = array_var($_GET, 'tu');
		if (is_null($tasksUserId)) {
			$tasksUserId = user_config_option('TM tasks user filter', logged_user()->getId());
		} else if (user_config_option('TM tasks user filter') != $tasksUserId) {
			set_user_config_option('TM tasks user filter', $tasksUserId, logged_user()->getId());
		}
				
		$timeslotsUserId = array_var($_GET, 'tsu');
		if (is_null($timeslotsUserId)) {
			$timeslotsUserId = user_config_option('TM user filter', 0);
		} else if (user_config_option('TM user filter') != $timeslotsUserId) {
			set_user_config_option('TM user filter', $timeslotsUserId, logged_user()->getId());
		}
		
		if (!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks')) {
			$timeslotsUserId = logged_user()->getId();
		}
				
		$showTimeType = array_var($_GET, 'stt');
		if (is_null($showTimeType)) {
			$showTimeType = user_config_option('TM show time type', 0);
		} else if (user_config_option('TM show time type') != $showTimeType) {
			set_user_config_option('TM show time type', $showTimeType, logged_user()->getId());
		}
		
		$start = array_var($_GET, 'start', 0);
		$limit = 20;
		
		$tasksUser = Contacts::findById($tasksUserId);
		$timeslotsUser = Contacts::findById($timeslotsUserId);	
		
		//Active tasks view
		$open_timeslots = Timeslots::instance()->listing(array(
			"extra_conditions" => " AND end_time = '".EMPTY_DATETIME."' AND contact_id = ".$tasksUserId 
		))->objects;
		$tasks = array();
		foreach($open_timeslots as $open_timeslot) {
			$task = ProjectTasks::findById($open_timeslot->getRelObjectId());
			if ($task instanceof ProjectTask && !$task->isCompleted() && !$task->isTrashed() && !$task->isArchived()) $tasks[] = $task;
		}
		ProjectTasks::populateTimeslots($tasks);
		
		//Timeslots view
		$total = 0;
		switch ($showTimeType){
			case 0: //Show only timeslots added through the time panel
				$result = Timeslots::getGeneralTimeslots(active_context(), $timeslotsUser, $start, $limit);
				$timeslots = $result->objects;
				$get_total = Timeslots::getGeneralTimeslots(active_context(), $timeslotsUser, $start, $limit, true);
				$total = $get_total->total;
				break;
			default:
				throw new Error('Unrecognised TM show time type: ' . $showTimeType);
		}
		
		//Get Users Info
		$users = array();
		$context = active_context();
		if (!can_manage_time(logged_user())) {
			if (can_add(logged_user(), $context, Timeslots::instance()->getObjectTypeId())) $users = array(logged_user());
		} else {
			if (logged_user()->isMemberOfOwnerCompany()) {
				$users = Contacts::getAllUsers();
			} else {
				$users = logged_user()->getCompanyId() > 0 ? Contacts::getAllUsers(" AND `company_id` = ". logged_user()->getCompanyId()) : array(logged_user());
			}
			// filter users by permissions only if any member is selected.
			$selected_members = active_context_members(false);
			if (count($selected_members) > 0) {
				$tmp_users = array();
				foreach ($users as $user) {
					if (can_add($user, $context, Timeslots::instance()->getObjectTypeId())) $tmp_users[] = $user;
				}
				$users = $tmp_users;
			}
		}
		
		//Get Companies Info
		if (logged_user()->isMemberOfOwnerCompany() || logged_user()->isAdminGroup()) {
			$companies = Contacts::getCompaniesWithUsers();
		} else {
			$companies = array();
			if (logged_user()->getCompanyId() > 0) $companies[] = logged_user()->getCompany();
		}
		
		$required_dimensions = DimensionObjectTypeContents::getRequiredDimensions(Timeslots::instance()->getObjectTypeId());
		$draw_inputs = !$required_dimensions || count($required_dimensions) == 0;
		if (!$draw_inputs) {
			$ts_ots = DimensionObjectTypeContents::getDimensionObjectTypesforObject(Timeslots::instance()->getObjectTypeId());
			$context = active_context();
			foreach ($context as $sel) {
				if ($sel instanceof Member) {
					foreach ($ts_ots as $ts_ot) {
						if ($sel->getDimensionId() == $ts_ot->getDimensionId() && $sel->getObjectTypeId() == $ts_ot->getDimensionObjectTypeId()) {
							$draw_inputs = true;
							break;
						}
					}
					if ($draw_inputs) break;
				}
			}
		}
		
		tpl_assign('draw_inputs', $draw_inputs);
		tpl_assign('selected_user', logged_user()->getId());
		tpl_assign('timeslots', $timeslots);
		tpl_assign('tasks', $tasks);
		if (count($tasks) > 0) tpl_assign('all_users', Contacts::getAllUsers());
		tpl_assign('users', $users);
		tpl_assign('start', $start);
		tpl_assign('limit', $limit);
		tpl_assign('total', $total);
		tpl_assign('companies', $companies);
		ajx_set_no_toolbar(true);
	}
	
	function add_timeslot(){
		$object_id = array_var($_REQUEST, "object_id",false);
		
		ajx_current("empty");
		$timeslot_data = array_var($_POST, 'timeslot');
		
		if($object_id){
			$object = Objects::findObject($object_id);			
			if(!($object instanceof ContentDataObject) || !($object->canAddTimeslot(logged_user()))) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			}
			$member_ids = $object->getMemberIds();
		}else{
			$member_ids = json_decode(array_var($_POST, 'members',array()));	
			// clean member_ids
			$tmp_mids = array();
			foreach ($member_ids as $mid) {
				if (!is_null($mid) && trim($mid) != "") $tmp_mids[] = $mid;
			}
			$member_ids = $tmp_mids;
			
			if(empty($member_ids)){
				if (!can_add(logged_user(), active_context(), Timeslots::instance()->getObjectTypeId())) {
					flash_error(lang('no access permissions'));
					ajx_current("empty");
					return;
				}
			}else{
				if (count($member_ids) > 0) {
					$enteredMembers = Members::findAll(array('conditions' => 'id IN ('.implode(",", $member_ids).')'));
				} else {
					$enteredMembers = array();
				}
				if (!can_add(logged_user(), $enteredMembers, Timeslots::instance()->getObjectTypeId())) {
					flash_error(lang('no access permissions'));
					ajx_current("empty");
					return;
				}
			}
			
			$object_id = 0;
		}		
		
		try {
			$hoursToAdd = array_var($timeslot_data, 'hours',0);
			$minutes = array_var($timeslot_data, 'minutes',0);
                        
			if (strpos($hoursToAdd,',') && !strpos($hoursToAdd,'.'))
				$hoursToAdd = str_replace(',','.',$hoursToAdd);
			if (strpos($hoursToAdd,':') && !strpos($hoursToAdd,'.')) {
				$pos = strpos($hoursToAdd,':') + 1;
				$len = strlen($hoursToAdd) - $pos;
				$minutesToAdd = substr($hoursToAdd,$pos,$len);
				if( !strlen($minutesToAdd)<=2 || !strlen($minutesToAdd)>0){
					$minutesToAdd = substr($minutesToAdd,0,2);
				}
				$mins = $minutesToAdd / 60;
				$hours = substr($hoursToAdd, 0, $pos-1);
				$hoursToAdd = $hours + $mins;
			}
			if($minutes){
				$min = str_replace('.','',($minutes/6));
				$hoursToAdd = $hoursToAdd + ("0.".$min);
			}
				
			if ($hoursToAdd <= 0){
				flash_error(lang('time has to be greater than 0'));
				return;
			}
			
			$startTime = getDateValue(array_var($timeslot_data, 'date'));
			$startTime = $startTime->add('h', 8 - logged_user()->getTimezone());
			$endTime = getDateValue(array_var($timeslot_data, 'date'));
			$endTime = $endTime->add('h', 8 - logged_user()->getTimezone() + $hoursToAdd);
			
			//use current time
			if( array_var($_REQUEST, "use_current_time",false)){
				$currentStartTime = DateTimeValueLib::now();
				$currentEndTime = DateTimeValueLib::now();
				$currentStartTime = $currentStartTime->add('h', -$hoursToAdd);	
				
				$startTime->setHour($currentStartTime->getHour());
				$startTime->setMinute($currentStartTime->getMinute());
				$endTime->setHour($currentEndTime->getHour());
				$endTime->setMinute($currentEndTime->getMinute());				
			}
			
			$timeslot_data['start_time'] = $startTime;
			$timeslot_data['end_time'] = $endTime;
			$timeslot_data['description'] = html_to_text($timeslot_data['description']);
			$timeslot_data['name'] = $timeslot_data['description'];
			$timeslot_data['rel_object_id'] = $object_id;//array_var($timeslot_data,'project_id');
			$timeslot = new Timeslot();
		
			
			
			//Only admins can change timeslot user
			if (!array_var($timeslot_data, 'contact_id', false) || !SystemPermissions::userHasSystemPermission(logged_user(), 'can_manage_time')) {
				$timeslot_data['contact_id'] = logged_user()->getId();
			}
			$timeslot->setFromAttributes($timeslot_data);			
			$user = Contacts::findById($timeslot_data['contact_id']);
			$billing_category_id = $user->getDefaultBillingId();
			$bc = BillingCategories::findById($billing_category_id);
			if ($bc instanceof BillingCategory) {
				$timeslot->setBillingId($billing_category_id);
				$hourly_billing = $bc->getDefaultValue();
				$timeslot->setHourlyBilling($hourly_billing);
				$timeslot->setFixedBilling($hourly_billing * $hoursToAdd);
				$timeslot->setIsFixedBilling(false);
			}
			DB::beginWork();
			$timeslot->save();
			
			$task = ProjectTasks::findById($object_id);
			if($task instanceof ProjectTask) {
				$task->calculatePercentComplete();
			}
			
			if (!isset($member_ids) || !is_array($member_ids) || count($member_ids) == 0) {
				$member_ids = json_decode(array_var($_POST, 'members'));
			}
			$object_controller = new ObjectController();
			$object_controller->add_to_members($timeslot, $member_ids);
			
			DB::commit();
			ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_ADD);
			
			$show_billing = can_manage_billing(logged_user());
			ajx_extra_data(array("timeslot" => $timeslot->getArrayInfo($show_billing),"real_obj_id" => $timeslot->getRelObjectId()));
		} catch(Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		} // try
	}
	
	function edit_timeslot(){
		
		ajx_current("empty");
		$timeslot_data = array_var($_POST, 'timeslot');
		$timeslot = Timeslots::findById(array_var($timeslot_data,'id',0));
	
		if (!$timeslot instanceof Timeslot){
			flash_error(lang('timeslot dnx'));
			return;
		}
				
		//context permissions or members
		$member_ids = json_decode(array_var($_POST, 'members',array()));
		// clean member_ids
		$tmp_mids = array();
		foreach ($member_ids as $mid) {
			if (!is_null($mid) && trim($mid) != "") $tmp_mids[] = $mid;
		}
		$member_ids = $tmp_mids;
				
		if(empty($member_ids)){
			if (!can_add(logged_user(), active_context(), Timeslots::instance()->getObjectTypeId())) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			}
		}else{
			if (count($member_ids) > 0) {
				$enteredMembers = Members::findAll(array('conditions' => 'id IN ('.implode(",", $member_ids).')'));
			} else {
				$enteredMembers = array();
			}
			if (!can_add(logged_user(), $enteredMembers, Timeslots::instance()->getObjectTypeId())) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			}
		}
					
		try {
			$hoursToAdd = array_var($timeslot_data, 'hours',0);
			$minutes = array_var($timeslot_data, 'minutes',0);

			if (strpos($hoursToAdd,',') && !strpos($hoursToAdd,'.'))
			$hoursToAdd = str_replace(',','.',$hoursToAdd);
			if (strpos($hoursToAdd,':') && !strpos($hoursToAdd,'.')) {
				$pos = strpos($hoursToAdd,':') + 1;
				$len = strlen($hoursToAdd) - $pos;
				$minutesToAdd = substr($hoursToAdd,$pos,$len);
				if( !strlen($minutesToAdd)<=2 || !strlen($minutesToAdd)>0){
					$minutesToAdd = substr($minutesToAdd,0,2);
				}
				$mins = $minutesToAdd / 60;
				$hours = substr($hoursToAdd, 0, $pos-1);
				$hoursToAdd = $hours + $mins;
			}

			if($minutes){
				$min = str_replace('.','',($minutes/6));
				$hoursToAdd = $hoursToAdd + ("0.".$min);
                        }
				
			if ($hoursToAdd <= 0){
				flash_error(lang('time has to be greater than 0'));
				return;
			}
			
			$startTime = getDateValue(array_var($timeslot_data, 'date'));
			$startTime = $startTime->add('h', 8 - logged_user()->getTimezone());
			$endTime = getDateValue(array_var($timeslot_data, 'date'));
			$endTime = $endTime->add('h', 8 - logged_user()->getTimezone() + $hoursToAdd);
			$timeslot_data['start_time'] = $startTime;
			$timeslot_data['end_time'] = $endTime;
			$timeslot_data['name'] = $timeslot_data['description'];
			
			//Only admins can change timeslot user
			if (!array_var($timeslot_data, 'contact_id') && !logged_user()->isAdministrator()) {
				$timeslot_data['contact_id'] = $timeslot->getContactId();
			}
			$timeslot->setFromAttributes($timeslot_data);
			
			$user = Contacts::findById($timeslot_data['contact_id']);
			$billing_category_id = $user->getDefaultBillingId();
			$bc = BillingCategories::findById($billing_category_id);
			if ($bc instanceof BillingCategory) {
				$timeslot->setBillingId($billing_category_id);
				$hourly_billing = $bc->getDefaultValue();
				$timeslot->setHourlyBilling($hourly_billing);
				$timeslot->setFixedBilling($hourly_billing * $hoursToAdd);
				$timeslot->setIsFixedBilling(false);
			}
			DB::beginWork();
			$timeslot->save();
			
			$member_ids = json_decode(array_var($_POST, 'members', ''));
			$object_controller = new ObjectController();
			$object_controller->add_to_members($timeslot, $member_ids);
			
			DB::commit();
			ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_EDIT);
			
			ajx_extra_data(array("timeslot" => $timeslot->getArrayInfo()));
		} catch(Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		} // try
	}
	
	function delete_timeslot(){
		ajx_current("empty");
		$timeslot = Timeslots::findById(get_id());
		
		if (!$timeslot instanceof Timeslot){
			flash_error(lang('timeslot dnx'));
			return;
		}
		
		if (!$timeslot->canDelete(logged_user())){
			flash_error(lang('no access permissions'));
			return;
		}
		
		try {
			DB::beginWork();
			$timeslot->delete();
			DB::commit();
			ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_DELETE);
			
			ajx_extra_data(array("timeslotId" => get_id()));
		} catch(Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		} // try
	}

} // TimeController

?>