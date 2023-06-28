<?php

/**
 * Handle all timeslot related requests
 *
 * @version 1.0
 * @author Carlos Palma <chonwil@gmail.com>
 */
class TimeslotController extends ApplicationController {

	/**
	 * Construct the TimeslotController
	 *
	 * @access public
	 * @param void
	 * @return TimeslotController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct

	/**
	 * Open timeslot
	 *
	 * @param void
	 * @return null
	 */
	function open() {

		$this->setTemplate('add_timeslot');

		$object_id = get_id('object_id');

		$object = Objects::findObject($object_id);
		if($object instanceof ContentDataObject && !($object->canAddTimeslot(logged_user()))) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}

		$timeslot = new Timeslot();
		$dt = DateTimeValueLib::now();
		$timeslot->setStartTime($dt);
		$timeslot->setContactId(logged_user()->getId());
		if ($object instanceof ContentDataObject) {
			$timeslot->setRelObjectId($object_id);
		}
        
		if (Plugins::instance()->isActivePlugin('advanced_billing')) {
			$invoicing_status = 'pending';
			Hook::fire('get_initial_invoicing_status_for_timeslot_using_members_and_task', array('task_id' => $object_id, 'get_task_members' => true), $invoicing_status);
			$timeslot->setColumnValue('invoicing_status', $invoicing_status);
		}

        $allOpenTimeslot = Timeslots::getAllOpenTimeslotByObjectByUser(logged_user());
		
		try{
			DB::beginWork();
            
            foreach ($allOpenTimeslot as $time){
			   $this->handle_open_timeslot($time);
            }
			
			$timeslot->save();
			
			$object_controller = new ObjectController();
			if ($object instanceof ContentDataObject) {
				$object_controller->add_to_members($timeslot, $object->getMemberIds());
			} else {
				$object_controller->add_to_members($timeslot, active_context_members(false));
			}
			
			
			DB::commit();
			ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_OPEN);
			
			flash_success(lang('success open timeslot'));
			ajx_current("reload");
		} catch (Exception $e) {
			DB::rollback();
			ajx_current("empty");
			flash_error($e->getMessage());
		}
	} 
	
	function add_timespan() {
	
		//$object_id = get_id('object_id');
		$object_id = array_var($_REQUEST, "object_id");
		
		$object = Objects::findObject($object_id);
		if(!($object instanceof ContentDataObject) || !($object->canAddTimeslot(logged_user()))) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}

		$timeslot_data = array_var($_POST, 'timeslot');
		$hours = array_var($timeslot_data, 'hours');
		$minutes = array_var($timeslot_data, 'minutes');
		
		if (strpos($hours,',') && !strpos($hours,'.')) {
			$hours = str_replace(',','.',$hours);
		}
		
		if($minutes){
			$min = str_replace('.','',($minutes/6));
			$hours = $hours + ("0.".$min);
		}

		if ($hours <= 0){
			flash_error(lang('time has to be greater than 0'));
			ajx_current("empty");
		}else{

			$timeslot = new Timeslot();
			$dt = DateTimeValueLib::now();
			$dt2 = DateTimeValueLib::now();
			$timeslot->setEndTime($dt);
			$dt2 = $dt2->add('h', -$hours);
			$timeslot->setStartTime($dt2);
			$timeslot->setDescription(array_var($timeslot_data, 'description'));
			$timeslot->setContactId(array_var($timeslot_data, 'contact_id', logged_user()->getId()));
			$timeslot->setRelObjectId($object_id);
			
			// Billing
			if (!Plugins::instance()->isActivePlugin('advanced_billing')) {
				$user = Contacts::findById(array_var($timeslot_data, 'contact_id', logged_user()->getId()));
				$billing_category_id = $user->getDefaultBillingId();
				$bc = BillingCategories::findById($billing_category_id);
				if ($bc instanceof BillingCategory) {
					$timeslot->setBillingId($billing_category_id);
					$hourly_billing = $bc->getDefaultValue();
					$timeslot->setHourlyBilling($hourly_billing);
					$timeslot->setFixedBilling(number_format($hourly_billing * $hours, 2));
					$timeslot->setIsFixedBilling(false);
				}
				$currency_info = Currencies::getDefaultCurrencyInfo();
				$timeslot->setRateCurrencyId($currency_info['id']);
			}

			try{
				DB::beginWork();
				$timeslot->save();

				$task = ProjectTasks::findById($object_id);
				if($task instanceof ProjectTask) {
					$task->calculatePercentComplete();
					
					$object_controller = new ObjectController();
					$object_controller->add_to_members($timeslot, $task->getMemberIds());
				}
					
				DB::commit();
				if($task instanceof ProjectTask) {
					$this->notifier_work_estimate($task);
				}
				ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_ADD);
					
				flash_success(lang('success create timeslot'));
				ajx_current("reload");
			} catch (Exception $e) {
				DB::rollback();
				ajx_current("empty");
				flash_error($e->getMessage());
			}
				
		}
	}

	/*
	 * Close timeslot
	 *
	 * @param void
	 * @return null
	 */
	function close() {

		$this->setTemplate('add_timeslot');

		$cancel_timer = array_var($_GET, 'cancel') && array_var($_GET, 'cancel') == 'true';
		$timeslot = Timeslots::findById(get_id());
		if(!($timeslot instanceof Timeslot)) {
			flash_error(lang('timeslot dnx'));
			ajx_current("empty");
			return;
		}

		$object = $timeslot->getRelObject();
		if($object instanceof ContentDataObject && !($object->canAddTimeslot(logged_user()))) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$timeslot_data = array_var($_POST, 'timeslot');
		$timeslot->close();
		$timeslot->setFromAttributes($timeslot_data);

		Hook::fire('round_minutes_to_fifteen', array('timeslot' => $timeslot), $ret);
		//Billing
		if (!Plugins::instance()->isActivePlugin('advanced_billing')) {
			$user = Contacts::findById(array_var($timeslot_data, 'contact_id', logged_user()->getId()));
			$billing_category_id = $user->getDefaultBillingId();
			$bc = BillingCategories::findById($billing_category_id);
			if ($bc instanceof BillingCategory) {
				$timeslot->setBillingId($billing_category_id);
				$hourly_billing = $bc->getDefaultValue();
				$timeslot->setHourlyBilling($hourly_billing);
				$timeslot->setFixedBilling(number_format($hourly_billing * $hours, 2));
				$timeslot->setIsFixedBilling(false);
			}
		}
		
		try{
			// to use when saving the application log
			$old_content_object = $timeslot->generateOldContentObjectData();
			
			DB::beginWork();
			if (array_var($_GET, 'cancel') && array_var($_GET, 'cancel') == 'true'){
				$timeslot->delete();
			}else{
				$timeslot->save();
			}
			
			$object = $timeslot->getRelObject();
			if($object instanceof ProjectTask) {
				$object->calculatePercentComplete();
				Hook::fire('calculate_estimated_and_executed_financials', array(), $object);
			}
			
			DB::commit();
			if($object instanceof ProjectTask) {
				$this->notifier_work_estimate($object);
			}
			
			if (array_var($_GET, 'cancel') && array_var($_GET, 'cancel') == 'true') {
				flash_success(lang('success cancel timeslot'));
			} else { 
				Hook::fire('after_timer_closed', $timeslot, $ret);
				flash_success(lang('success close timeslot', ''));
			}	
			
			ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_CLOSE);

			ajx_current("reload");
		} catch (Exception $e) {
			DB::rollback();
			ajx_current("empty");
			flash_error($e->getMessage());
		}
	}
	
	function close_timer() {
		$this->setTemplate('close_timer');
		$timeslot = Timeslots::findById(get_id());
		if(!($timeslot instanceof Timeslot)) {
			flash_error(lang('timeslot dnx'));
			ajx_current("empty");
			return;
		}

		$object = $timeslot->getRelObject();
		if($object instanceof ContentDataObject && !($object->canAddTimeslot(logged_user()))) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}

		tpl_assign('timeslot', $timeslot);
	}
		
		
	

	function handle_open_timeslot($time) {
		$config = user_config_option('stop_running_timeslots');
		if($config == 1) {
			$this->internal_close($time);
		} else if ($config == 2) {
			$this->internal_pause($time);
		}
	}
	
	function internal_close($time){ 
	    $config = user_config_option('stop_running_timeslots') == 1; 
	    if ($config){
			// to use when saving the application log
			$old_content_object = $time->generateOldContentObjectData();

            $date = format_date(null,DATE_MYSQL);
			$time->getEndTime($date);
			$time->close();
			Hook::fire('round_minutes_to_fifteen', array('timeslot' => $time), $ret);
        
            //Billing
            if (!Plugins::instance()->isActivePlugin('advanced_billing')) {
                $user = Contacts::findById($time->getContactId());
                $billing_category_id = $user->getDefaultBillingId();
                $bc = BillingCategories::findById($billing_category_id);
                if ($bc instanceof BillingCategory) {
                    $time->setBillingId($billing_category_id);
                    $hourly_billing = $bc->getDefaultValue();
                    $time->setHourlyBilling($hourly_billing);
                    $time->setFixedBilling(number_format($hourly_billing * $hours, 2));
                    $time->setIsFixedBilling(false);
                }
            }
            $time->save();
            
            $object = $time->getRelObject();
            if($object instanceof ProjectTask) {
                $object->calculatePercentComplete();
            }
            
			ApplicationLogs::createLog($time, ApplicationLogs::ACTION_CLOSE, false, true);
        }
    }

	function internal_pause($time) {
		$config = user_config_option('stop_running_timeslots') == 2; 
		$is_paused = $time->isPaused();
	    if ($config && !$is_paused){
			try{
				// to use when saving the application log
				$old_content_object = $time->generateOldContentObjectData();

				DB::beginWork();
				$time->pause();
				$time->save();
				DB::commit();
				
				ApplicationLogs::createLog($time, ApplicationLogs::ACTION_EDIT, false, true);

			//	flash_success(lang('success pause timeslot'));
				ajx_current("reload");
			} catch (Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
			}
        
            
       }
	}
	
	function pause() {

		ajx_current("empty");

		$timeslot = Timeslots::findById(get_id());
		if(!($timeslot instanceof Timeslot)) {
			flash_error(lang('timeslot dnx'));
			return;
		}

		$object = $timeslot->getRelObject();
		if($object instanceof ContentDataObject && !($object->canAddTimeslot(logged_user()))) {
			flash_error(lang('no access permissions'));
			return;
		}
		
		if(!($timeslot->canEdit(logged_user()))) {
			flash_error(lang('no access permissions'));
			return;
		}
		
		try{
			// to use when saving the application log
			$old_content_object = $timeslot->generateOldContentObjectData();

			DB::beginWork();
			$timeslot->pause();
			$timeslot->save();
			DB::commit();

			ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_EDIT);
				
			flash_success(lang('success pause timeslot'));
			ajx_current("reload");
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		}
	} 
	
	function resume() {

		ajx_current("empty");

		$timeslot = Timeslots::findById(get_id());
		if(!($timeslot instanceof Timeslot)) {
			flash_error(lang('timeslot dnx'));
			return;
		}

		$object = $timeslot->getRelObject();
		if($object instanceof ContentDataObject && !($object->canAddTimeslot(logged_user()))) {
			flash_error(lang('no access permissions'));
			return;
		}
		
		if(!($timeslot->canEdit(logged_user()))) {
			flash_error(lang('no access permissions'));
			return;
		}

		
		try{
			// to use when saving the application log
			$old_content_object = $timeslot->generateOldContentObjectData();

			DB::beginWork();
			
			$timeslot->resume();
			$timeslot->save();
			DB::commit();
			
			ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_OPEN);

			flash_success(lang('success resume timeslot'));
			ajx_current("reload");
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		}
	} 

	/**
	 * Edit timeslot
	 *
	 * @param void
	 * @return null
	 */
	function edit() {

		$this->setTemplate('add_timeslot');
		
		$timeslot = Timeslots::findById(get_id());
		if(!($timeslot instanceof Timeslot)) {
			flash_error(lang('timeslot dnx'));
			ajx_current("empty");
			return;
		}

		$object = $timeslot->getRelObject();
		if(!($object instanceof ContentDataObject)) {
			flash_error(lang('object dnx'));
			ajx_current("empty");
			return;
		}
		
		if(!($object->canAddTimeslot(logged_user()))) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		if(!($timeslot->canEdit(logged_user()))) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$timeslot_data = array_var($_POST, 'timeslot');
		if(!is_array($timeslot_data)) {
			$timeslot_data = array(
				'contact_id' => $timeslot->getContactId(),
				'description' => $timeslot->getDescription(),
          		'start_time' => $timeslot->getStartTime(),
          		'end_time' => $timeslot->getEndTime(),
          		'is_fixed_billing' => $timeslot->getIsFixedBilling(),
          		'hourly_billing' => $timeslot->getHourlyBilling(),
          		'rate_currency_id' => $timeslot->getRateCurrencyId(),
          		'fixed_billing' => $timeslot->getFixedBilling()
			);
		}

		tpl_assign('timeslot_form_object', $object);
		tpl_assign('timeslot', $timeslot);
		tpl_assign('timeslot_data', $timeslot_data);
		tpl_assign('show_billing', BillingCategories::count() > 0);
		
		if(is_array(array_var($_POST, 'timeslot'))) {
			try {
				// to use when saving the application log
				$old_content_object = $timeslot->generateOldContentObjectData();

				$old_user_id = $timeslot->getContactId();
				
				$timeslot->setFromAttributes($timeslot_data);
				
				$timeslot->setContactId(array_var($timeslot_data, 'contact_id', logged_user()->getId()));
				$timeslot->setDescription(array_var($timeslot_data, 'description'));
				
				if ($timeslot->getContactId() != $old_user_id) {
					$timeslot->setForceRecalculateBilling(true);
				}
       			
				$st = getDateValue(array_var($timeslot_data, 'start_value'),DateTimeValueLib::now());
				$s_time = getTimeValue(array_var($timeslot_data, 'start_time'));
				$st->setHour($s_time['hours']);
				$st->setMinute($s_time['mins']);
				
				$et = getDateValue(array_var($timeslot_data, 'end_value'),DateTimeValueLib::now());
				$e_time = getTimeValue(array_var($timeslot_data, 'end_time'));
				$et->setHour($e_time['hours']);
				$et->setMinute($e_time['mins']);
				
				$tz_offset = Timezones::getTimezoneOffsetToApply($timeslot);
				
				$st = new DateTimeValue($st->getTimestamp() - $tz_offset);
				$et = new DateTimeValue($et->getTimestamp() - $tz_offset);
				$timeslot->setStartTime($st);
				$timeslot->setEndTime($et);
				
				if ($timeslot->getStartTime() > $timeslot->getEndTime()){
					flash_error(lang('error start time after end time'));
					ajx_current("empty");
					return;
				}
				
				$seconds = array_var($timeslot_data,'subtract_seconds',0);
				$minutes = array_var($timeslot_data,'subtract_minutes',0);
				$hours = array_var($timeslot_data,'subtract_hours',0);
				
				$subtract = $seconds + 60 * $minutes + 3600 * $hours;
				if ($subtract < 0){
					flash_error(lang('pause time cannot be negative'));
					ajx_current("empty");
					return;
				}
				
				$testEndTime = new DateTimeValue($timeslot->getEndTime()->getTimestamp());
				
				$testEndTime->add('s',-$subtract);
				
				if ($timeslot->getStartTime() > $testEndTime){
					flash_error(lang('pause time cannot exceed timeslot time'));
					ajx_current("empty");
					return;
				}
				
				$timeslot->setSubtract($subtract);				
				
				if ($timeslot->getUser()->getDefaultBillingId()) {
					$timeslot->setIsFixedBilling(array_var($timeslot_data,'is_fixed_billing',false));
					$timeslot->setHourlyBilling(array_var($timeslot_data,'hourly_billing',0));
					if ($timeslot->getIsFixedBilling()){
						$timeslot->setFixedBilling(array_var($timeslot_data,'fixed_billing',0));
					} else {
						$timeslot->setFixedBilling($timeslot->getHourlyBilling() * $timeslot->getMinutes() / 60);
					}
					if ($timeslot->getBillingId() == 0 && ($timeslot->getHourlyBilling() > 0 || $timeslot->getFixedBilling() > 0)){
						$timeslot->setBillingId($timeslot->getUser()->getDefaultBillingId());
					}
					$currency_info = Currencies::getDefaultCurrencyInfo();
					$timeslot->setRateCurrencyId($currency_info['id']);
				}
				
				DB::beginWork();
				$timeslot->save();
				
				$task = ProjectTasks::findById($timeslot->getRelObjectId());
				if($task instanceof ProjectTask) {
					$task->calculatePercentComplete();
				}
				
				$member_ids = json_decode(array_var($_POST, 'members'));
				if (!is_null($member_ids)) {
					$object_controller = new ObjectController();
					$object_controller->add_to_members($timeslot, $member_ids);
				}
				
				DB::commit();
				$this->notifier_work_estimate($task);
				ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_EDIT);

				flash_success(lang('success edit timeslot'));
				ajx_current("back");
			} catch(Exception $e) {
				DB::rollback();
				Logger::log($e->getTraceAsString());
				flash_error(lang('error edit timeslot').": ".$e->getMessage());
				ajx_current("empty");
			}
		}
	} // edit

	/**
	 * Delete specific timeslot
	 *
	 * @param void
	 * @return null
	 */
	function delete() {
		
		$timeslot = Timeslots::findById(get_id());
		if(!($timeslot instanceof Timeslot)) {
			flash_error(lang('timeslot dnx'));
			ajx_current("empty");
			return;
		}

		$object = $timeslot->getRelObject();
		if(!($object instanceof ContentDataObject)) {
			flash_error(lang('object dnx'));
			ajx_current("empty");
			return;
		}

		if(trim($object->getObjectUrl())) $redirect_to = $object->getObjectUrl();

		if(!$timeslot->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
                     
		try {
			$timeslot_delete = $timeslot;      
                        
			DB::beginWork();
			$timeslot->delete();
			$object->onDeleteTimeslot($timeslot);
			DB::commit();
			ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_DELETE);
			
			if ($object instanceof ProjectTask) {
				$object->calculatePercentComplete();
			}
			
			flash_success(lang('success delete timeslot'));
			ajx_current("reload");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete timeslot'));
			ajx_current("empty");
		}

	} // delete
	
	function delete_all_from_task() {
	    $task_id = get_id('object_id');
	    $timeslots = Timeslots::instance()->findAll(array('conditions' => array('`rel_object_id` = ?', $task_id)));
	    
	    if (!is_array($timeslots) || !count($timeslots)){	    
	        flash_error(lang('timeslot dnx'));
	        ajx_current("empty");
	        return;
	    }
	    
        try {            

            DB::beginWork();
            
            foreach($timeslots as $timeslot) {
                
                if(!($timeslot instanceof Timeslot)) {                    
                    continue;
                }
                
                $object = $timeslot->getRelObject();
                if(!($object instanceof ContentDataObject)) {
                    continue;
                }
                
                if($timeslot->canDelete(logged_user())) {

					if(Plugins::instance()->isActivePlugin('income') && $timeslot->getColumnValue('invoicing_status') == 'invoiced'){
						flash_error(lang('you cannot delete invoiced time entry'));
						ajx_current("empty");
						return;
					}

					$timeslot->trash();

                    // $timeslot->delete();
                    $object->onDeleteTimeslot($timeslot);
                    
                    if ($object instanceof ProjectTask) {
                        $object->calculatePercentComplete();
                    }
                }
                                
                
            }            
            DB::commit();
            
            foreach($timeslots as $timeslot) {
                ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_DELETE);
            }
                        
            flash_success(lang('success delete all timeslot'));
            ajx_current("reload");
        } catch(Exception $e) {
            DB::rollback();
            flash_error(lang('error delete all timeslot'));
            ajx_current("empty");
        }

	}

	function taskHasInvoicedTimeslots($task){
		$result = false;
		if(Plugins::instance()->isActivePlugin('income') && $task instanceof ProjectTask){
			$task_id = $task->getObjectId();
			$timeslots = Timeslots::instance()->findAll(array('conditions' => array('`rel_object_id` = ' . $task_id . ' AND `invoicing_status`="invoiced"')));
			if (count($timeslots)){	    
				$result = true;
			}	
		} 
		return $result;
	}

	function notifier_work_estimate($task){
		if($task->getPercentCompleted() > 100){
			Notifier::workEstimate($task);
		}
	}
	
	
	function get_users_for_timeslot() {
		ajx_current("empty");
		$user_info = array();
		
		if (can_manage_time(logged_user())) {
			
			$timeslot = Timeslots::findById(get_id());
			
			$rel_object = null;
			if (array_var($_GET, 'task_id')) {
				$rel_object = ProjectTasks::findById(array_var($_GET, 'task_id'));
			}
			
			if (is_null($rel_object) && $timeslot instanceof Timeslot) {
				$rel_object = $timeslot->getRelObject();
			}
			
			if (can_manage_time(logged_user())) {
				if (logged_user()->isMemberOfOwnerCompany()) {
					$users = Contacts::getAllUsers();
				} else {
					$users = logged_user()->getCompanyId() > 0 ? Contacts::getAllUsers(" AND `company_id` = ". logged_user()->getCompanyId()) : array(logged_user());
				}
			} else {
				$users = array(logged_user());
			}
			$tmp_users = array();
			foreach ($users as $user) {
				
				if ($rel_object instanceof ProjectTask) {
					$is_assigned = $rel_object->getAssignedToContactId() == $user->getId();
					$members = $rel_object->getMembers();
				} else {
					$is_assigned = false;
					$members = $timeslot instanceof Timeslot ? $timeslot->getMembers() : array();
				}
				
				if ($is_assigned || can_add($user, $members, Timeslots::instance()->getObjectTypeId())) {
					$tmp_users[] = $user;
				}
			}
			$users = $tmp_users;
			
			$user_info = array();
			foreach ($users as $user) {
				$user_info[] = array('id' => $user->getId(), 'name' => $user->getObjectName());
			}
			
		}
		
		ajx_extra_data(array('users' => $user_info));
	}

} // TimeslotController

?>