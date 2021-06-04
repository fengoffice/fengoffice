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
    }

// __construct

    function index() {

        //Get Users Info
        $users = array();
        $context = active_context();
        if (!can_manage_time(logged_user())) {
        	$users = logged_user()->getCompanyId() > 0 ? Contacts::getAllUsers(" AND `company_id` = " . logged_user()->getCompanyId()) : array(logged_user());
        } else {
            //if (logged_user()->isMemberOfOwnerCompany()) {
                $users = Contacts::getAllUsers();
            /*} else {
                $users = logged_user()->getCompanyId() > 0 ? Contacts::getAllUsers(" AND `company_id` = " . logged_user()->getCompanyId()) : array(logged_user());
            }*/
        }
		
		// filter users by permissions only if any member is selected.
		$selected_members = active_context_members(false);
		if (count($selected_members) > 0) {
			$tmp_users = array();
			foreach ($users as $user) {
				if (can_read($user, $context, Timeslots::instance()->getObjectTypeId())) {
					$tmp_users[] = $user;
				}
			}
			$users = $tmp_users;
		}
		

        /*
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
         */

        tpl_assign('users', $users);
        ajx_set_no_toolbar(true);
    }

    function add() {

        if (logged_user()->isGuest()) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        }

        $context = active_context();
        $context_member_count = 0;
        foreach ($context as $c) {
            if ($c instanceof Member)
                $context_member_count++;
        }

/*
        $notAllowedMember = '';
        if ($context_member_count > 0 && !Timeslot::canAdd(logged_user(), $context, $notAllowedMember)) {
            if (str_starts_with($notAllowedMember, '-- req dim --'))
                $msg = lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in));
            else
                trim($notAllowedMember) == "" ? $msg = lang('you must select where to keep', lang('the task')) : $msg = lang('no context permissions to add', lang("time"), $notAllowedMember);

            flash_error($msg);
            ajx_current("empty");
            return;
        }
        */

        $timeslot_data = array_var($_POST, 'timeslot');
        if (!is_array($timeslot_data)) {
            $timeslot = new Timeslot();
            $timeslot->setContactId(array_var($_REQUEST, "contact_id", logged_user()->getId()));
            $timeslot->setRelObjectId(array_var($_REQUEST, "object_id"));
            $dont_reload = array_var($_REQUEST, "dont_reload");

            
            $show_paused_time = user_config_option('show_pause_time_action');
            if (!config_option('show_pause_time_action')) {
                $show_paused_time = 0;
            }
            $preferences = array(
              'show_paused_time'=> $show_paused_time,
              'automatic_calculation_time'=> user_config_option('automatic_calculation_time'),
              'automatic_calculation_start_time'=> user_config_option('automatic_calculation_start_time')
            );
            // load preferences
            tpl_assign('time_preferences',$preferences);
            
            //Get Users Info
            $users = array();
            if (can_manage_time(logged_user())) {

                /*if (logged_user()->isMemberOfOwnerCompany()) {
                    $users = Contacts::getAllUsers();
                } else {
                    $users = logged_user()->getCompanyId() > 0 ? Contacts::getAllUsers(" AND `company_id` = " . logged_user()->getCompanyId()) : array(logged_user());
                }*/
                $users = Contacts::getAllUsers();
                // filter users by permissions only if any member is selected.
                $members = $timeslot->getMembers();
                if (count($members) > 0) {
                    $tmp_users = array();
                    foreach ($users as $user) {
                        if (can_add($user, $members, Timeslots::instance()->getObjectTypeId()))
                            $tmp_users[] = $user;
                    }
                    $users = $tmp_users;
                }

                tpl_assign('users', $users);
            }else {
                if (can_add(logged_user(), $context, Timeslots::instance()->getObjectTypeId()))
                    $users = array(logged_user());
            }

            $pre_selected_member_ids = null;
            $rel_obj = $timeslot->getRelObject();
            if ($rel_obj instanceof ContentDataObject) {
                $pre_selected_member_ids = $rel_obj->getMemberIds();
            } else {
            	$pre_selected_member_ids = active_context_members(false);
            	$all_assoc_member_ids = array();
            	foreach ($pre_selected_member_ids as $mid) {
            		$assoc_ids = MemberPropertyMembers::getAllAssociatedMemberIds($mid, true);
            		$assoc_ids = array_flat($assoc_ids);
            		$all_assoc_member_ids = array_unique(array_merge($all_assoc_member_ids, $assoc_ids));
            	}
            	$pre_selected_member_ids = array_unique(array_merge($pre_selected_member_ids, $all_assoc_member_ids));
            }
            Hook::fire('preselected_time_form_member_ids', array('object' => $timeslot), $pre_selected_member_ids);
            
            tpl_assign('pre_selected_member_ids', $pre_selected_member_ids);

            tpl_assign('dont_reload', $dont_reload);

            tpl_assign('timeslot', $timeslot);
            $this->setTemplate('edit_timeslot');
        } else {

            $ok = $this->add_timeslot(array(
                'timeslot' => $timeslot_data,
                'object_id' => array_var($_REQUEST, "object_id"),
                'members' => array_var($_REQUEST, "members"),
                'use_current_time' => true,
            ));

            if ($ok) {
                $dont_reload = array_var($_REQUEST, "dont_reload");
                if ($dont_reload) {
                    $t = ProjectTasks::findById(array_var($_REQUEST, "object_id"));
                    if ($t instanceof ProjectTask) {
                        $tdata = $t->getArrayInfo();
                        evt_add('update tasks in list', array('tasks' => array($tdata)));
                    }
                } else {
                    evt_add("reload current panel");
                }
            }
        }
    }

    private function parse_hours_and_minutes_to_save($timeslot_data) {
        // if end time is spacified calculate the amount of hours and minutes using start and date inputs
        if (array_var($timeslot_data, 'specify_end_time') > 0) {

            $sd = getDateValue(array_var($timeslot_data, 'date'));
            $st = getTimeValue(array_var($timeslot_data, 'start_time'));
            $ed = getDateValue(array_var($timeslot_data, 'end_date'));
            $et = getTimeValue(array_var($timeslot_data, 'end_time'));
            if (!$sd instanceof DateTimeValue || !$ed instanceof DateTimeValue || !$st || !$et) {
                throw new Exception(lang('you have to fill all the date and time fields'));
            }
            $sd->setHour($st['hours']);
            $sd->setMinute($st['mins']);
            $sd->setSecond(0);
            $ed->setHour($et['hours']);
            $ed->setMinute($et['mins']);
            $ed->setSecond(0);

            $diff_seconds = $ed->getTimestamp() - $sd->getTimestamp();
            $diff_minutes = floor($diff_seconds / 60);

            $sub_h = array_var($timeslot_data, 'subtract_hours', 0);
            $sub_m = array_var($timeslot_data, 'subtract_minutes', 0);
            $sub_total_mins = $sub_h * 60 + $sub_m;

            $diff_minutes -= $sub_total_mins;
            if ($diff_minutes <= 0) {
                throw new Exception(lang('time has to be greater than 0'));
            }

            $hoursToAdd = floor($diff_minutes / 60);
            $minutes = $diff_minutes % 60;
        } else {
            // an amount of time has been specified
            $hoursToAdd = array_var($timeslot_data, 'hours', 0);
            $minutes = array_var($timeslot_data, 'minutes', 0);
        }

        return array('hours' => $hoursToAdd, 'minutes' => $minutes);
    }

    function add_timeslot($parameters = null, $use_transaction = true) {
        if (is_null($parameters)) {
            $object_id = array_var($_REQUEST, "object_id", false);
            $parameters = $_POST;
            $parameters["use_current_time"] = array_var($_REQUEST, "use_current_time");
        } else {
            $object_id = array_var($parameters, "object_id", false);
        }

        ajx_current("empty");
        $timeslot_data = array_var($parameters, 'timeslot');
        $sd = getDateValue(array_var($timeslot_data, 'date'));

        // The MySQL supported range is '1000-01-01' to '9999-12-31'
        if(!$sd instanceof DateTimeValue || $sd->getYear() > 9999 || $sd->getYear() < 1000){
            flash_error(lang('incorrect date'));
            ajx_current("empty");
            return;
        }
       
        if ($object_id) {
            $object = Objects::findObject($object_id);
            if (!($object instanceof ContentDataObject) || !($object->canAddTimeslot(logged_user()))) {
                flash_error(lang('no access permissions'));
                ajx_current("empty");
                return;
            }

            if (array_var($parameters, 'members')) {
                $member_ids = json_decode(array_var($parameters, 'members'));
            } else {
                $member_ids = $object->getMemberIds();
            }
        } else {
            $member_ids = json_decode(array_var($parameters, 'members', array()));
            // clean member_ids
            $tmp_mids = array();
            foreach ($member_ids as $mid) {
                if (!is_null($mid) && trim($mid) != "")
                    $tmp_mids[] = $mid;
            }
            $member_ids = $tmp_mids;

            if (empty($member_ids)) {
                if (!can_add(logged_user(), active_context(), Timeslots::instance()->getObjectTypeId())) {
                    flash_error(lang('no access permissions'));
                    ajx_current("empty");
                    return;
                }
            } else {
                if (count($member_ids) > 0) {
                    $enteredMembers = Members::findAll(array('conditions' => 'id IN (' . implode(",", $member_ids) . ')'));
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
            $hhmm = $this->parse_hours_and_minutes_to_save($timeslot_data);

            $hoursToAdd = array_var($hhmm, 'hours', 0);
            $minutes = array_var($hhmm, 'minutes', 0);

            // if paused time is specified then add it to the total lapse
            $sub_hours = array_var($timeslot_data, 'subtract_hours', 0);
            $sub_minutes = array_var($timeslot_data, 'subtract_minutes', 0);
            if ($sub_hours > 0 || $sub_minutes > 0) {
                $hoursToAdd += $sub_hours;
                $minutes += $sub_minutes;
                if ($minutes > 60) {
                    $minutes = $minutes - 60;
                    $hoursToAdd += 1;
                }
            }
            $timeslot_data['subtract'] = 60 * ($sub_hours * 60 + $sub_minutes);

            if (strpos($hoursToAdd, ',') && !strpos($hoursToAdd, '.'))
                $hoursToAdd = str_replace(',', '.', $hoursToAdd);
            if (strpos($hoursToAdd, ':') && !strpos($hoursToAdd, '.')) {
                $pos = strpos($hoursToAdd, ':') + 1;
                $len = strlen($hoursToAdd) - $pos;
                $minutesToAdd = substr($hoursToAdd, $pos, $len);
                if (!strlen($minutesToAdd) <= 2 || !strlen($minutesToAdd) > 0) {
                    $minutesToAdd = substr($minutesToAdd, 0, 2);
                }
                $mins = $minutesToAdd / 60;
                $hours = substr($hoursToAdd, 0, $pos - 1);
                $hoursToAdd = $hours + $mins;
            }
            if ($minutes) {
                $min = str_replace('.', '', ($minutes / 6));
                $hoursToAdd = $hoursToAdd + ("0." . $min);
            }

            if ($hoursToAdd <= 0) {
                flash_error(lang('time has to be greater than 0'));
                return;
            }

            $logged_user_tz_hours_offset = logged_user()->getUserTimezoneValue() / 3600;

            //Get the Date of the timeslot
            //We start with simply the date (not the exact start hour+minutes)
            $startTime = getDateValue(array_var($timeslot_data, 'date'));

            //Case 1. If the date is not set, we'll use current time (minus the total hours worked/logged) as the default.
            if (!$startTime instanceof DateTimeValue) {
                $startTime = DateTimeValueLib::now();
                $startTime->add('h', -$hoursToAdd);
                //This was here for debugging purposes (leaving as an example for future use, if needed)
                //Logger::log_r("StartTime is: ".$startTime->toICalendar());
            } else {                
                //We now get the Hours and minutes entered by the user (if entered)
                $startTimeHours = getTimeValue(array_var($timeslot_data, 'start_time'));
                
                //Case 2. If start hours+minutes were entered by the user, we set the hours and minutes.
                if ($startTimeHours) {
                	
                    $startTime->setHour($startTimeHours['hours']);
                    $startTime->setMinute($startTimeHours['mins']);
                    //We take the timezone into consideration, adding the timezone offset.
                    $startTime->add('h', -$logged_user_tz_hours_offset);
                    
                } else {
                    //Case 3. The start hours+minutes were not entered
                    //We'll use the current time as default. But this is a bit tricky due to the potential timezone differences
                    $starthoursandminutes = DateTimeValueLib::now();
                    
                    $startTime->setHour($starthoursandminutes->getHour());
                    $startTime->setMinute($starthoursandminutes->getMinute());
                      
                    //If the date the user selected is today, then that's easy.
                    //It's very similar to case 1 (See above). 
                    if ($startTime->getDay() == $starthoursandminutes->getDay()) {
                    	
                        $starthoursandminutes->add('h', -$hoursToAdd);
                        $startTime->setHour($starthoursandminutes->getHour());
                        $startTime->setMinute($starthoursandminutes->getMinute());
                        
                    } else {
                        //Case 4. If the date the user selected is not today, there might be a conflict
                        //This process is to fix the date, when the timezone falls in a different day than the gmt
                        //Because the user intended for the timeslot's start to fall on the date they entered.
                        
                        //We check whether the GMT and the user TZ both have the same day.
                        $dateinGMT = $startTime->getDay();
                        $startTime->add('h', $logged_user_tz_hours_offset);
                        $dateinUserTZ = $startTime->getDay(); //we offset here - we'll fix below
                        $diffInDays = $dateinGMT-$dateinUserTZ;
                        
                        //Roll back the offset
                        $startTime->add('h', -$logged_user_tz_hours_offset);
                        
                        //If there is a difference, we'll correct the day.
                        $startTime->add('d', $diffInDays);
                        
                        $startTime->setHour($starthoursandminutes->getHour());
                        $startTime->setMinute($starthoursandminutes->getMinute());
                    }
                }
            }
            
            //Now we set the EndTime
            $endTime = new DateTimeValue($startTime->getTimestamp());
            $endTime->add('h', $hoursToAdd);
            
            $timeslot_data['start_time'] = $startTime;
            $timeslot_data['end_time'] = $endTime;
            $timeslot_data['description'] = $timeslot_data['description'];
            $timeslot_data['name'] = $timeslot_data['description'];
            $timeslot_data['rel_object_id'] = $object_id;
            $timeslot = new Timeslot();

            
            //Only admins can change timeslot user
            if (!array_var($timeslot_data, 'contact_id', false) || !SystemPermissions::userHasSystemPermission(logged_user(), 'can_manage_time')) {
                $timeslot_data['contact_id'] = logged_user()->getId();
            }
            $timeslot->setFromAttributes($timeslot_data);

            // Billing
            if (!Plugins::instance()->isActivePlugin('advanced_billing')) {
                $user = Contacts::findById($timeslot_data['contact_id']);
                if ($user instanceof Contact && $user->isUser()) {
                    $billing_category_id = $user->getDefaultBillingId();
                    $bc = BillingCategories::findById($billing_category_id);
                    if ($bc instanceof BillingCategory) {
                        $timeslot->setBillingId($billing_category_id);
                        $hourly_billing = $bc->getDefaultValue();
                        $timeslot->setHourlyBilling($hourly_billing);
                        $timeslot->setFixedBilling($hourly_billing * $hoursToAdd);
                        $timeslot->setIsFixedBilling(false);
                    }
					$currency_info = Currencies::getDefaultCurrencyInfo();
					$timeslot->setRateCurrencyId($currency_info['id']);
                }
            } else {
                $timeslot->setForceRecalculateBilling(true);
            }

            if ($use_transaction) {
                DB::beginWork();
            }
            $timeslot->save();

            $task = ProjectTasks::findById($object_id);
            if ($task instanceof ProjectTask) {
                $task->calculatePercentComplete();
            }

            if (!isset($member_ids) || !is_array($member_ids) || count($member_ids) == 0) {
                $member_ids = json_decode(array_var($parameters, 'members'));
            }
            $additional_member_ids = array_var($timeslot_data, 'additional_member_ids');
            if (is_array($additional_member_ids)) {
                if (!is_array($member_ids))
                    $member_ids = array();
                $member_ids = array_filter(array_merge($member_ids, $additional_member_ids));
            }
            if (isset($timeslot_data['ignore_member_ids']) && is_array($timeslot_data['ignore_member_ids'])) {
                foreach ($timeslot_data['ignore_member_ids'] as $ign_mid) {
                    foreach ($member_ids as $k => &$mid) {
                        if ($ign_mid == $mid) {
                            unset($member_ids[$k]);
                        }
                    }
                }
            }

            $object_controller = new ObjectController();
            $object_controller->add_custom_properties($timeslot);
            if (!is_null($member_ids)) {
                $object_controller->add_to_members($timeslot, $member_ids, null, false);
            }

            if ($use_transaction) {
                DB::commit();
            }
            ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_ADD);

            $show_billing = can_manage_billing(logged_user());
            ajx_extra_data(array("timeslot" => $timeslot->getArrayInfo($show_billing, true, true), "real_obj_id" => $timeslot->getRelObjectId()));

            return $timeslot;
        } catch (Exception $e) {
            if ($use_transaction) {
                DB::rollback();
            }
            flash_error($e->getMessage());
        } // try
    }

    function edit_timeslot() {

        $timeslot = Timeslots::findById(get_id());
        if (!$timeslot instanceof Timeslot) {
            flash_error(lang('timeslot dnx'));
            ajx_current("empty");
            return;
        }
        
        if (!can_write(logged_user(), $timeslot->getMembers(), Timeslots::instance()->getObjectTypeId())) {
        	flash_error(lang('no access permissions'));
        	ajx_current("empty");
        	return;
        }
        
        $show_paused_time = user_config_option('show_pause_time_action');
        if (!config_option('show_pause_time_action')) {
            $show_paused_time = 0;
        }
        $preferences = array(
          'show_paused_time'=> $show_paused_time,
          'automatic_calculation_time'=> user_config_option('automatic_calculation_time'),
          'automatic_calculation_start_time'=> user_config_option('automatic_calculation_start_time')
        );
        // load preferences
        tpl_assign('time_preferences',$preferences);
        
        
        $timeslot_data = array_var($_POST, 'timeslot');
        if (!is_array($timeslot_data)) {
            //Get Users Info
            $users = array();
            if (can_manage_time(logged_user())) {

                /*if (logged_user()->isMemberOfOwnerCompany()) {
                    $users = Contacts::getAllUsers();
                } else {
                    $users = logged_user()->getCompanyId() > 0 ? Contacts::getAllUsers(" AND `company_id` = " . logged_user()->getCompanyId()) : array(logged_user());
                }*/
                $users = Contacts::getAllUsers();
                // filter users by permissions only if any member is selected.
                $members = $timeslot->getMembers();
                if (count($members) > 0) {
                    $tmp_users = array();
                    foreach ($users as $user) {
                        if (can_read($user, $members, Timeslots::instance()->getObjectTypeId()))
                            $tmp_users[] = $user;
                    }
                    $users = $tmp_users;
                }

                tpl_assign('users', $users);
            }else {
                if (can_add(logged_user(), $context, Timeslots::instance()->getObjectTypeId()))
                    $users = array(logged_user());
            }

            Hook::fire('modify_timeslot_before_edit', array('request' => $_REQUEST), $timeslot);

            tpl_assign('timeslot', $timeslot);
            tpl_assign('edit_mode',1);
        } else {
            $sd = getDateValue(array_var($timeslot_data, 'date'));

            // The MySQL supported range is '1000-01-01' to '9999-12-31'
            if(!$sd instanceof DateTimeValue || $sd->getYear() > 9999 || $sd->getYear() < 1000){
                flash_error(lang('incorrect date'));
                ajx_current("empty");
                return;
            }

            $timeslot->setRelObjectId(array_var($_REQUEST, "object_id"));
            
            // FORM SENT...
            //context permissions or members
            $member_ids = json_decode(array_var($_POST, 'members', array()));
            // clean member_ids
            $tmp_mids = array();
            foreach ($member_ids as $mid) {
                if (!is_null($mid) && trim($mid) != "")
                    $tmp_mids[] = $mid;
            }
            $member_ids = $tmp_mids;

            if (empty($member_ids)) {
                if (!can_add(logged_user(), active_context(), Timeslots::instance()->getObjectTypeId())) {
                    flash_error(lang('no access permissions'));
                    ajx_current("empty");
                    return;
                }
            } else {
                if (count($member_ids) > 0) {
                    $enteredMembers = Members::findAll(array('conditions' => 'id IN (' . implode(",", $member_ids) . ')'));
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
            	$transacion_started = false;
                $hhmm = $this->parse_hours_and_minutes_to_save($timeslot_data);

                $hoursToAdd = array_var($hhmm, 'hours', 0);
                $minutes = array_var($hhmm, 'minutes', 0);

                // if paused time is specified then add it to the total lapse
                $sub_hours = array_var($timeslot_data, 'subtract_hours', 0);
                $sub_minutes = array_var($timeslot_data, 'subtract_minutes', 0);
                if ($sub_hours > 0 || $sub_minutes > 0) {
                    $hoursToAdd += $sub_hours;
                    $minutes += $sub_minutes;
                    if ($minutes > 60) {
                        $minutes = $minutes - 60;
                        $hoursToAdd += 1;
                    }
                }
                $timeslot_data['subtract'] = 60 * ($sub_hours * 60 + $sub_minutes);

                if (strpos($hoursToAdd, ',') && !strpos($hoursToAdd, '.')) {
                    $hoursToAdd = str_replace(',', '.', $hoursToAdd);
                }
                if (strpos($hoursToAdd, ':') && !strpos($hoursToAdd, '.')) {
                    $pos = strpos($hoursToAdd, ':') + 1;
                    $len = strlen($hoursToAdd) - $pos;
                    $minutesToAdd = substr($hoursToAdd, $pos, $len);
                    if (!strlen($minutesToAdd) <= 2 || !strlen($minutesToAdd) > 0) {
                        $minutesToAdd = substr($minutesToAdd, 0, 2);
                    }
                    $mins = $minutesToAdd / 60;
                    $hours = substr($hoursToAdd, 0, $pos - 1);
                    $hoursToAdd = $hours + $mins;
                }

                if ($minutes) {
                    $min = str_replace('.', '', ($minutes / 6));
                    $hoursToAdd = $hoursToAdd + ("0." . $min);
                }

                if ($hoursToAdd <= 0) {
                	throw new Exception(lang('time has to be greater than 0'));
                }

                $logged_user_tz_hours_offset = logged_user()->getUserTimezoneValue() / 3600;

                $startTime = getDateValue(array_var($timeslot_data, 'date'));
                if (isset($timeslot_data['start_time'])) {
                    $startTimeHours = getTimeValue($timeslot_data['start_time']);
                    if ($startTimeHours) {
                        $startTime->add('h', $startTimeHours['hours']);
                        $startTime->add('m', $startTimeHours['mins']);
                    } else {
                        $startTime->add('h', 8);
                    }
                } else {
                    $startTime->add('h', 8);
                }

                $endTime = getDateValue(array_var($timeslot_data, 'date'));
                $endTime = $endTime->add('h', $startTime->getHour() + $hoursToAdd);
                $endTime = $endTime->add('m', $startTime->getMinute());

                // save timeslot dates in gmt0
                $startTime->add('h', -$logged_user_tz_hours_offset);
                $endTime->add('h', -$logged_user_tz_hours_offset);

                $timeslot_data['start_time'] = $startTime;
                $timeslot_data['end_time'] = $endTime;
                $timeslot_data['name'] = $timeslot_data['description'];

                // get old properties to check if has to recalculate billing
                $old_user_id = $timeslot->getContactId();
                $old_member_ids = array_flat(DB::executeAll("SELECT om.member_id FROM `" . TABLE_PREFIX . "object_members` om
					inner join " . TABLE_PREFIX . "members m on om.member_id=m.id
					inner join " . TABLE_PREFIX . "dimensions d on d.id=m.dimension_id
					where om.object_id=" . $timeslot->getId() . " and d.is_manageable and om.is_optimization=0;"));

                //Only admins can change timeslot user
                if (!array_var($timeslot_data, 'contact_id') && !logged_user()->isAdminGroup()) {
                    $timeslot_data['contact_id'] = $timeslot->getContactId();
                }
                $timeslot->setFromAttributes($timeslot_data);

                // set to recalculate billing if user changed
                if ($timeslot->getContactId() != $old_user_id) {
                    $timeslot->setForceRecalculateBilling(true);
                }
                // set to recalculate billing if members changed
                if (count(array_diff($member_ids, $old_member_ids)) > 0 || count(array_diff($old_member_ids, $member_ids)) > 0) {
                    $timeslot->setForceRecalculateBilling(true);
                }

                if (!Plugins::instance()->isActivePlugin('advanced_billing')) {
                    $user = Contacts::findById($timeslot_data['contact_id']);
                    if ($user instanceof Contact && $user->isUser()) {
                        $billing_category_id = $user->getDefaultBillingId();
                        $bc = BillingCategories::findById($billing_category_id);
                        if ($bc instanceof BillingCategory) {
                            $timeslot->setBillingId($billing_category_id);
                            $hourly_billing = $bc->getDefaultValue();
                            $timeslot->setHourlyBilling($hourly_billing);
                            $timeslot->setFixedBilling($hourly_billing * $hoursToAdd);
                            $timeslot->setIsFixedBilling(false);
                        }
                    }
                }
                
                DB::beginWork();
                $transacion_started = true;
                
                $timeslot->save();

                $member_ids = json_decode(array_var($_POST, 'members', ''));
                $object_controller = new ObjectController();
                $object_controller->add_custom_properties($timeslot);
                $object_controller->add_to_members($timeslot, $member_ids);
                
                // update old related object calculated worked time columns
                $old_object_id = array_var($_REQUEST, "old_object_id");
                if ($old_object_id > 0 && $old_object_id != $timeslot->getRelObjectId()) {
	                $old_related_object = Objects::findObject($old_object_id);
	                if ($old_related_object instanceof ContentDataObject) {
	                	$old_related_object->onDeleteTimeslot($timeslot);
	                }
                }
                
                DB::commit();
                ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_EDIT);
                ajx_current("reload");
                evt_add("reload current panel");

                ajx_extra_data(array("timeslot" => $timeslot->getArrayInfo()));
            } catch (Exception $e) {
                if ($transacion_started) DB::rollback();
                ajx_current("empty");
                flash_error($e->getMessage());
            } // try
        }
    
        
     }

    function delete_timeslot() {
        ajx_current("empty");
        $timeslot = Timeslots::findById(get_id());

        if (!$timeslot instanceof Timeslot) {
            flash_error(lang('timeslot dnx'));
            return;
        }

        if (!$timeslot->canDelete(logged_user())) {
            flash_error(lang('no access permissions'));
            return;
        }

        try {
            DB::beginWork();
            $timeslot->trash();
            DB::commit();
            ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_TRASH);

            ajx_extra_data(array("timeslotId" => get_id()));
        } catch (Exception $e) {
            DB::rollback();
            flash_error($e->getMessage());
        } // try
    }

    function list_all($only_return_objects = false) {
        ajx_current("empty");
        ini_set('memory_limit', '1G');
        // Get all variables from request
        $start = array_var($_REQUEST, 'start', 0);
        $limit = array_var($_REQUEST, 'limit', config_option('files_per_page'));
        $order = array_var($_REQUEST, 'sort', 'start_time');
        $order_dir = array_var($_REQUEST, 'dir');

        $only_count_result = array_var($_REQUEST, 'only_result', false);
        $rel_object_id = array_var($_REQUEST, 'rel_object_id');
        $only_closed = array_var($_REQUEST, 'only_closed');
        $ignore_context = array_var($_REQUEST, 'ignore_context');

        $type_filter = array_var($_REQUEST, 'type_filter');
        $user_filter = array_var($_REQUEST, 'user_filter');
        $period_filter = array_var($_REQUEST, 'period_filter');
        $from_filter = array_var($_REQUEST, 'from_filter');
        $to_filter = array_var($_REQUEST, 'to_filter');
        
        $load_totals_row = array_var($_REQUEST, 'load_totals_row');

        $dim_order = null;
        $cp_order = null;

        $join_params = array();
        $select_columns = array('*');
        $extra_conditions = "";

        $attributes = array(
            "ids" => explode(',', array_var($_GET, 'ids')),
        );

        $action = array_var($_GET, 'action');

        //Resolve actions to perform
        $actionMessage = array();
        if (isset($action)) {
            $actionMessage = $this->resolveAction($action, $attributes);
            if ($actionMessage["errorCode"] == 0) {
                flash_success($actionMessage["errorMessage"]);
            } else {
                flash_error($actionMessage["errorMessage"]);
            }
        }

        if ($rel_object_id)
            $extra_conditions .= " AND rel_object_id='$rel_object_id' ";

        $extra_conditions .= " AND end_time > 0 ";

        switch ($type_filter) {
            case 1: $extra_conditions .= " AND rel_object_id>0 ";
                break;
            case 2: $extra_conditions .= " AND rel_object_id=0 ";
                break;
            default: break;
        }

        if(!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_others_timeslots')){
			$extra_conditions .= " AND contact_id = " . logged_user()->getId();
		} elseif($user_filter) {
            $extra_conditions .= " AND contact_id='$user_filter' ";
        }
        
        $now = DateTimeValueLib::now();
        $now->advance(logged_user()->getUserTimezoneValue(), true);
        
        $from_date = null;
		$to_date = null;
                
        switch ($period_filter){
            case 0:
                break;
            case 1: // today
            	$from_date = DateTimeValueLib::make(0,0,0,$now->getMonth(),$now->getDay(),$now->getYear());
            	$to_date = DateTimeValueLib::make(23,59,59,$now->getMonth(),$now->getDay(),$now->getYear());
                break;
            case 2: // this week
            	$sunday = $now->getMondayOfWeek()->add('d',-1);
            	$nextSunday = $now->getMondayOfWeek()->add('w',1)->add('d',-2);
            	$from_date = DateTimeValueLib::make(0,0,0,$sunday->getMonth(),$sunday->getDay(),$sunday->getYear());
            	$to_date = DateTimeValueLib::make(23,59,59,$nextSunday->getMonth(),$nextSunday->getDay(),$nextSunday->getYear());
                break;
            case 3: // last week
            	$sunday = $now->getMondayOfWeek()->add('w',-1)->add('d',-1);
            	$nextSunday = $now->getMondayOfWeek()->add('d',-2);
            	$from_date = DateTimeValueLib::make(0,0,0,$sunday->getMonth(),$sunday->getDay(),$sunday->getYear());
            	$to_date = DateTimeValueLib::make(23,59,59,$nextSunday->getMonth(),$nextSunday->getDay(),$nextSunday->getYear());
                break;
            case 4: // this month
				$from_date = DateTimeValueLib::make(0,0,0,$now->getMonth(),1,$now->getYear());
				$to_date = DateTimeValueLib::make(23,59,59,$now->getMonth(),1,$now->getYear())->add('M',1)->add('d',-1);
                break;
            case 5: // last month
				$now->add('M',-1);
				$from_date = DateTimeValueLib::make(0,0,0,$now->getMonth(),1,$now->getYear());
				$to_date = DateTimeValueLib::make(23,59,59,$now->getMonth(),1,$now->getYear())->add('M',1)->add('d',-1);
                break;
			case 6: //Date interval
				$from_date = getDateValue($from_filter);
				if ($from_date instanceof DateTimeValue) {
					$from_date = $from_date->beginningOfDay();
				}
				
				$to_date = getDateValue($to_filter);
				if ($to_date instanceof DateTimeValue) {
					$to_date = $to_date->endOfDay();
				}
				break;
            default :
                break;
        }
        
        
		if ($from_date instanceof DateTimeValue) {
			$from_date->beginningOfDay();
			$from_date->advance(-1 * logged_user()->getUserTimezoneValue(), true);
			$extra_conditions .= " AND e.start_time >= '" . $from_date->toMySQL() . "'";
		}
		if ($to_date instanceof DateTimeValue) {
			$to_date->endOfDay();
			$to_date->advance(-1 * logged_user()->getUserTimezoneValue(), true);
			$extra_conditions .= " AND e.start_time <= '" . $to_date->toMySQL() . "'";
		}
		
		Hook::fire('additional_timeslots_tab_filters', $_REQUEST, $extra_conditions);
		
        
        $co = ContactConfigOptions::getByName('current_time_module_filters');
        if (!$co instanceof ContactConfigOption) {
        	$co = new ContactConfigOption();
        	$co->setFromAttributes(array(
        		'category_name' => 'system',
        		'name' => 'current_time_module_filters',
        		'default_value' => '',
        		'config_handler_class' => 'StringConfigHandler',
        		'is_system' => '1',
        		'dev_comment' => '',
        		'options' => '',
        	));
        	$co->save();
        }

        $current_time_module_filters = array();
        $current_time_module_filters['type_filter'] = $type_filter;
        $current_time_module_filters['user_filter'] = $user_filter;
        $current_time_module_filters['period_filter'] = $period_filter;
        $current_time_module_filters['from_filter'] = $from_filter;
        $current_time_module_filters['to_filter'] = $to_filter;
        
        if (!$load_totals_row) {
        	Hook::fire('additional_timeslots_tab_filters_config', $_REQUEST, $current_time_module_filters);
        }
        
        if (!$load_totals_row && array_var($_REQUEST, 'current') == 'time-panel') {
        	set_user_config_option('current_time_module_filters', json_encode($current_time_module_filters), logged_user()->getId());
        }
        
        switch ($order) {
            case 'updatedOn':
                $order = '`updated_on`';
                break;
            case 'createdOn':
                $order = '`created_on`';
                break;
            case 'name':
                $order = 'contact_name';
                $join_params = array(
                    'table' => Objects::instance()->getTableName(),
                    'jt_field' => 'id',
                    'e_field' => 'contact_id',
                );
                $select_columns = array("e.*, o.*, jt.`name` as contact_name");
                break;
            case 'description':
            case 'start_time':
            case 'end_time':
            case 'subtract':
            case 'worked_time':
            case 'fixed_billing':
            case 'fixed_cost':
            case 'invoicing_status':
                break;
            default:
                //if order by custom prop
                if (strpos($order, 'cp_') == 1) {
                    $cp_order = substr($order, 3);
                    $order = 'customProp';
                } else if (str_starts_with($order, "dim_")) {
                    $dim_order = substr($order, 4);
                    $order = 'dimensionOrder';
                } else {
                    $order = '`start_time`';
                }
                break;
        }

        if (!$order_dir) {
            $order_dir = 'ASC';
        }

        Hook::fire("listing_extra_conditions", null, $extra_conditions);

        $only_query_totals_row = isset($load_totals_row) && $load_totals_row;

        $res = Timeslots::instance()->listing(array(
            "join_ts_with_task" => false,
            "order" => $order,
            "order_dir" => $order_dir,
            "dim_order" => $dim_order,
            "cp_order" => $cp_order,
            "start" => $start,
            "limit" => $limit,
            "ignore_context" => $ignore_context,
            "extra_conditions" => $extra_conditions,
            "count_results" => false,
            "only_count_results" => $only_count_result,
            "join_params" => $join_params,
        	"select_columns" => $select_columns,
        	"fire_additional_data_hook" => $only_query_totals_row,
        	"only_query_totals_row" => $only_query_totals_row,
        ));
        $result_timeslots = $res->objects;
        
        if ($only_return_objects) {
            return $result_timeslots;
        }

        // get active timeslots to put in the top of the list (only in the first page)
        if (!$only_closed && $start == 0) {
            $active_timeslots = Timeslots::instance()->listing(array(
                        "extra_conditions" => " AND end_time = '" . EMPTY_DATETIME . "' AND contact_id = " . logged_user()->getId()
                    ))->objects;
            foreach ($active_timeslots as $active_ts) {
                array_unshift($result_timeslots, $active_ts);
            }
        }

        // Prepare response object
        $object = $this->prepareObject($result_timeslots, $start, $limit, $res);
        ajx_extra_data($object);
        tpl_assign("listing", $object);
    }

    private function prepareObject($totMsg, $start, $limit, $res_obj) {

        $object = array(
            "totalCount" => $res_obj->total,
            "start" => $start,
            "timeslots" => array()
        );
        foreach ($res_obj as $k => $v) {
            if ($k != 'total' && $k != 'objects')
                $object[$k] = $v;
        }

        $show_billing = can_manage_billing(logged_user());

        $rel_object_ids = array();

        $custom_properties = CustomProperties::getAllCustomPropertiesByObjectType(Timeslots::instance()->getObjectTypeId());
        $ids = array();
        for ($i = 0; $i < $limit; $i++) {
            if (isset($totMsg[$i])) {
                $msg = $totMsg[$i];
                if ($msg instanceof Timeslot) {
                    $msg->setObjectTypeId(Timeslots::instance()->getObjectTypeId());
                    $general_info = $msg->getObject()->getArrayInfo();
                    $info = array_merge($msg->getArrayInfo($show_billing, true, true), $general_info);
                    $info["ix"] = $i;

                    if ($msg->getRelObjectId() > 0)
                        $rel_object_ids[$i] = $msg->getRelObjectId();

                    $add_cls = "";
                    if (!$msg->getEndTime())
                        $add_cls = "open-timeslot ";

                    Hook::fire("additional_task_timeslot_class", $msg, $add_cls);
                    if ($add_cls)
                        $info['add_cls'] .= $add_cls;

                    $add_columns = array();
                    $function = "view_timeslot_render_more_columns";
                    Hook::fire($function, $msg, $add_columns);
                    foreach ($add_columns as $col_id => $val) {
                        if (!isset($info[$col_id]))
                            $info[$col_id] = $val;
                    }
                    
                    $info['can_view_history'] = logged_user()->isAdminGroup();

                    $object["timeslots"][$i] = $info;
                    $ids[] = $msg->getId();

                    foreach ($custom_properties as $cp) {
                        $object["timeslots"][$i]['cp_' . $cp->getId()] = get_custom_property_value_for_listing($cp, $msg);
                    }
                }
            }
        }

        if (count($rel_object_ids) > 0) {
            $rel_object_name_rows = DB::executeAll("SELECT id, name FROM " . TABLE_PREFIX . "objects WHERE id IN (" . implode(',', $rel_object_ids) . ")");
            foreach ($object["timeslots"] as &$data) {
                if ($data['rel_object_id'] > 0) {
                    foreach ($rel_object_name_rows as $r) {
                        if ($data['rel_object_id'] == $r['id']) {
                            $data['rel_object_name'] = $r['name'];
                            break;
                        }
                    }
                }
            }
        }

        $read_objects = ReadObjects::getReadByObjectList($ids, logged_user()->getId());
        if (is_array($object["timeslots"])) {
            foreach ($object["timeslots"] as &$data) {
                $data['isRead'] = isset($read_objects[$data['object_id']]);
            }
        }

        return $object;
    }

    private function resolveAction($action, $attributes) {

        $resultMessage = "";
        $resultCode = 0;
        switch ($action) {
            case "trash":
                $succ = 0;
                $err = 0;
                for ($i = 0; $i < count($attributes["ids"]); $i++) {
                    $id = $attributes["ids"][$i];
                    if (!is_numeric($id)) continue;
                    
                    $message = Timeslots::findById($id);
                    if (!$message instanceof Timeslot) continue;
                    
                    if ($message instanceof Timeslot && $message->canDelete(logged_user())) {
                        try {
                        	$do_rollback_if_error = true;
                            DB::beginWork();
                            $message->trash();
                            DB::commit();
                            $do_rollback_if_error = false;
                            ApplicationLogs::createLog($message, ApplicationLogs::ACTION_TRASH);
                            $succ++;
                        } catch (Exception $e) {
                            if ($do_rollback_if_error) DB::rollback();
                            $err++;
                        }
                    } else {
                        $err++;
                    }
                }; // for
                if ($err > 0) {
                    $resultCode = 2;
                    $resultMessage = lang("error delete objects", $err) . ($succ > 0 ? "\n" . lang("success delete objects", $succ) : "");
                } else {
                    $resultMessage = lang("success delete objects", $succ);
                }
                break;

            case "archive":
                $succ = 0;
                $err = 0;
                for ($i = 0; $i < count($attributes["ids"]); $i++) {
                    $id = $attributes["ids"][$i];
                    if (!is_numeric($id)) continue;
                    
                    $message = Timeslots::findById($id);
                    if (!$message instanceof Timeslot) continue;
                    
                    if ($message instanceof Timeslot && $message->canEdit(logged_user())) {
                        try {
                            DB::beginWork();
                            $message->archive();
                            DB::commit();
                            ApplicationLogs::createLog($message, ApplicationLogs::ACTION_ARCHIVE);
                            $succ++;
                        } catch (Exception $e) {
                            DB::rollback();
                            $err++;
                        }
                    } else {
                        $err++;
                    }
                }; // for
                if ($err > 0) {
                    $resultCode = 2;
                    $resultMessage = lang("error archive objects", $err) . "<br />" . ($succ > 0 ? lang("success archive objects", $succ) : "");
                } else {
                    $resultMessage = lang("success archive objects", $succ);
                }
                break;
            default:
                $resultMessage = lang("Unimplemented action: '" . $action . "'"); // if 
                $resultCode = 2;
                break;
        } // switch
        return array("errorMessage" => $resultMessage, "errorCode" => $resultCode);
    }

}

// TimeController