<?php

/**
 * Controller for handling task list and task related requests
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class TaskController extends ApplicationController {

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
    }

// __construct

    private function task_item(ProjectTask $task) {
        return array(
            "id" => $task->getId(),
            "title" => clean($task->getObjectName()),
            "parent" => $task->getParentId(),
            "milestone" => $task->getMilestoneId(),
            "assignedTo" => $task->getAssignedTo() ? $task->getAssignedToName() : '',
            "completed" => $task->isCompleted(),
            "completedBy" => $task->getCompletedByName(),
            "isLate" => $task->isLate(),
            "daysLate" => $task->getLateInDays(),
            "priority" => $task->getPriority(),
            "percentCompleted" => $task->getPercentCompleted(),
            "duedate" => ($task->getDueDate() ? $task->getDueDate()->getTimestamp() : '0'),
            "order" => $task->getOrder()
        );
    }

    function quick_add_task() {
        if (logged_user()->isGuest()) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        }

        $notAllowedMember = '';
        if (!ProjectTask::canAdd(logged_user(), active_context(), $notAllowedMember)) {
            if (str_starts_with($notAllowedMember, '-- req dim --'))
                flash_error(lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember )));
            else
                trim($notAllowedMember) == "" ? flash_error(lang('you must select where to keep', lang('the task'))) : flash_error(lang('no context permissions to add', lang("tasks"), $notAllowedMember));
            ajx_current("empty");
            return;
        }

        ajx_current("empty");
        $task = new ProjectTask();
        $task_data = array_var($_POST, 'task');
        $parent_id = array_var($task_data, 'parent_id', 0);
        $parent = ProjectTasks::instance()->findById($parent_id);

        if (is_array($task_data)) {
            $task_data['due_date'] = getDateValue(array_var($task_data, 'task_due_date'));
            $task_data['start_date'] = getDateValue(array_var($task_data, 'task_start_date'));

            if ($task_data['due_date'] instanceof DateTimeValue) {
                $duetime = getTimeValue(array_var($task_data, 'task_due_time'));
                if (is_array($duetime)) {
                    $task_data['due_date']->setHour(array_var($duetime, 'hours'));
                    $task_data['due_date']->setMinute(array_var($duetime, 'mins'));
                    $task_data['due_date']->advance(logged_user()->getUserTimezoneValue() * -1);
                }
                $task_data['use_due_time'] = is_array($duetime);
            }
            if ($task_data['start_date'] instanceof DateTimeValue) {
                $starttime = getTimeValue(array_var($task_data, 'task_start_time'));
                if (is_array($starttime)) {
                    $task_data['start_date']->setHour(array_var($starttime, 'hours'));
                    $task_data['start_date']->setMinute(array_var($starttime, 'mins'));
                    $task_data['start_date']->advance(logged_user()->getUserTimezoneValue() * -1);
                }
                $task_data['use_start_time'] = is_array($starttime);
            }

            if (config_option("wysiwyg_tasks")) {
                $task_data['type_content'] = "html";
                $task_data['text'] = str_replace(array("\r", "\n", "\r\n"), array('', '', ''), array_var($task_data, 'text'));
            } else {
                $task_data['type_content'] = "text";
            }

            $task_data['object_type_id'] = $task->getObjectTypeId();

            $task->setFromAttributes($task_data);

            if (array_var($task_data, 'is_completed', false) == 'true') {
                $task->setCompletedOn(DateTimeValueLib::now());
                $task->setCompletedById(logged_user()->getId());
            }

            try {
                DB::beginWork();
                $task->save();
                $totalMinutes = (array_var($task_data, 'hours') * 60) + (array_var($task_data, 'minutes'));
                $task->setTimeEstimate($totalMinutes);
                $task->save();

                $gb_member_ids = array_var($task_data, 'members');
                $member_ids = array();
                $persons_dim = Dimensions::findByCode('feng_persons');
                $persons_dim_id = $persons_dim instanceof Dimension ? $persons_dim->getId() : 0;
                if ($parent) {
                    if (count($parent->getMembers()) > 0) {
                        foreach ($parent->getMembers() as $member) {
                            if ($member->getDimensionId() != $persons_dim_id) {
                                $member_ids[] = $member->getId();
                            }
                        }
                    }
                    $task->setMilestoneId($parent->getMilestoneId());
                    $task->save();
                }

                if (count($member_ids) == 0) {
                    $member_ids = active_context_members(false);
                }

                // get member ids
                if ($gb_member_ids && !empty($gb_member_ids)) {
                    $member_ids = json_decode(array_var($task_data, 'members'));
                }

                $object_controller = new ObjectController();
                $object_controller->add_to_members($task, $member_ids);

                $assignee = $task->getAssignedToContact();
                $assignee_to_me = false;
                if ($assignee instanceof Contact) {
                    $task->subscribeUser($assignee);

                    //do not notify my self
                    if ($assignee->getId() == logged_user()->getId()) {
                        $assignee_to_me = true;
                    }
                }

                // create default reminder by user config option
                if ($task->getDueDate() != null && user_config_option("add_task_default_reminder")) {
                    $reminder = new ObjectReminder();
                    $def = explode(",", user_config_option("reminders_tasks"));
                    $minutes = $def[2] * $def[1];
                    $reminder->setMinutesBefore($minutes);
                    $reminder->setType($def[0]);
                    $reminder->setContext("due_date");
                    $reminder->setObject($task);
                    $reminder->setUserId(0);
                    $date = $task->getDueDate();

                    if ($date instanceof DateTimeValue) {
                        $rdate = new DateTimeValue($date->getTimestamp() - $minutes * 60);
                        $reminder->setDate($rdate);
                    }
                    $reminder->save();
                }

                $subs = array();
                if (config_option('multi_assignment') && Plugins::instance()->isActivePlugin('crpm')) {
                    $json_subtasks = json_decode(array_var($_POST, 'multi_assignment'), true);
                    $subtasks = array();
                    $line = 0;
                    if (is_array($json_subtasks)) {
                        foreach ($json_subtasks as $json_subtask) {
                            $subtasks[$line]['assigned_to_contact_id'] = $json_subtask['assigned_to_contact_id'];
                            $subtasks[$line]['name'] = $json_subtask['name'];
                            $subtasks[$line]['time_estimate_hours'] = $json_subtask['time_estimate_hours'];
                            $subtasks[$line]['time_estimate_minutes'] = $json_subtask['time_estimate_minutes'];
                            $line++;
                        }
                    }
                    Hook::fire('save_subtasks', array('task' => $task, 'is_new' => true), $subtasks);

                    $subtasks = ProjectTasks::instance()->findAll(array(
                                'conditions' => '`parent_id` = ' . DB::escape($task->getId())
                    )); // findAll
                    foreach ($subtasks as $sub) {
                        $subs[] = $sub->getArrayInfo();
                    }
                }

                // subscribe
                $task->subscribeUser(logged_user());

                //for calculate member status we save de task again after the object have the members
                $task->save();

                DB::commit();
                $isSailent = true;
                // notify asignee
                if ((array_var($task_data, 'notify') == 'true' || (user_config_option("can notify from quick add") && !user_config_option("show_notify_checkbox_in_quick_add"))) && !$assignee_to_me) {
                    $isSailent = false;
                    try {
                        Notifier::taskAssigned($task);
                    } catch (Exception $e) {
                        Logger::log($e->getMessage());
                        Logger::log($e->getTraceAsString());
                    } // try
                }
                ApplicationLogs::createLog($task, ApplicationLogs::ACTION_ADD, null, $isSailent);
                ajx_extra_data(array("task" => $task->getArrayInfo(), 'subtasks' => $subs));
                flash_success(lang('success add task', $task->getObjectName()));
            } catch (Exception $e) {
                DB::rollback();
                flash_error($e->getMessage());
            } // try
        } // if
    }

    function quick_edit_multiple_task() {
        if (logged_user()->isGuest()) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        }
        ajx_current("empty");

        $tasks_info = array();
        $task_names = array();

        $tasks_data = array_var($_POST, 'tasks');

        foreach ($tasks_data as $task_data) {
            $task_id = array_var($task_data, 'id');

            $task = ProjectTasks::instance()->findById($task_id);
            if (!($task instanceof ProjectTask)) {
                continue;
            }

            if (!$task->canEdit(logged_user())) {
                continue;
            }

            $this->do_quick_edit_task($task_data, $task);

            $p = $task->getParent();
            $parent = $p instanceof ProjectTask ? $p->getArrayInfo() : '';

            $tasks_info[] = array("task" => $task->getArrayInfo(), 'subtasks' => $subs, 'parent' => $parent);
            $task_names[] = $task->getObjectName();
        }
        if (count($tasks_info) > 0) {
            ajx_extra_data(array("tasks" => $tasks_info));
            flash_success(lang('success edit task list', implode(', ', $task_names)));
        }
    }

    function quick_edit_task() {
        if (logged_user()->isGuest()) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        }
        ajx_current("empty");

        $task = ProjectTasks::instance()->findById(get_id());
        if (!($task instanceof ProjectTask)) {
            flash_error(lang('task list dnx'));
            return;
        }

        if (!$task->canEdit(logged_user())) {
            flash_error(lang('no access permissions'));
            return;
        }

        $task_data = array_var($_POST, 'task');
        $type_related = array_var($_POST, 'type_related');

        $this->do_quick_edit_task($task_data, $task, $type_related);
        $subs = array();
        $p = $task->getParent();
        $parent = $p instanceof ProjectTask ? $p->getArrayInfo() : '';
        ajx_extra_data(array("task" => $task->getArrayInfo(), 'subtasks' => $subs, 'parent' => $parent));
        flash_success(lang('success edit task', $task->getObjectName()));
    }

    private function do_quick_edit_task($task_data, &$task, $type_related = null) {
        // set task dates
        if (is_array($task_data)) {
            foreach ($task_data as $k => &$v) {
                $v = remove_scripts($v);
            }
            $send_edit = false;
            if ($task->getAssignedToContactId() == array_var($task_data, 'assigned_to_contact_id')) {
                $send_edit = true;
            }
            $task_data['due_date'] = getDateValue(array_var($task_data, 'task_due_date'));
            $task_data['start_date'] = getDateValue(array_var($task_data, 'task_start_date'));

            if ($task_data['due_date'] instanceof DateTimeValue) {
                $duetime = getTimeValue(array_var($task_data, 'task_due_time'));
                if (is_array($duetime)) {
                    $task_data['due_date']->setHour(array_var($duetime, 'hours'));
                    $task_data['due_date']->setMinute(array_var($duetime, 'mins'));
                    $task_data['due_date']->advance(logged_user()->getUserTimezoneValue() * -1);
                }
                $task_data['use_due_time'] = is_array($duetime);
            }
            if ($task_data['start_date'] instanceof DateTimeValue) {
                $starttime = getTimeValue(array_var($task_data, 'task_start_time'));
                if (is_array($starttime)) {
                    $task_data['start_date']->setHour(array_var($starttime, 'hours'));
                    $task_data['start_date']->setMinute(array_var($starttime, 'mins'));
                    $task_data['start_date']->advance(logged_user()->getUserTimezoneValue() * -1);
                }
                $task_data['use_start_time'] = is_array($starttime);
            }

            //control date subtask with parent
            if (array_var($task_data, 'control_dates') == "child") {
                $parent = $task->getParent();
                if ($parent->getStartDate() instanceof DateTimeValue && $task_data['start_date'] instanceof DateTimeValue) {
                    if ($task_data['start_date']->getTimestamp() < $parent->getStartDate()->getTimestamp()) {
                        $parent->setStartDate($task_data['start_date']);
                        $parent->setUseStartTime($task_data['use_start_time']);
                    }
                } else {
                    $parent->setStartDate($task_data['start_date']);
                    $parent->setUseStartTime(array_var($task_data, 'use_start_time', 0));
                }
                if ($parent->getDueDate() instanceof DateTimeValue && $task_data['due_date'] instanceof DateTimeValue) {
                    if ($task_data['due_date']->getTimestamp() > $parent->getDueDate()->getTimestamp()) {
                        $parent->setDueDate($task_data['due_date']);
                        $parent->setUseDueTime($task_data['use_due_time']);
                    }
                } else {
                    $parent->setDueDate($task_data['due_date']);
                    $parent->setUseDueTime(array_var($task_data, 'use_due_time', 0));
                }
                // calculate and set estimated time
                if (array_var($task_data, 'hours') !== null || array_var($task_data, 'minutes') !== null) {
                    $totalMinutes = (array_var($task_data, 'hours') * 60) + (array_var($task_data, 'minutes'));
                    $parent->setTimeEstimate($totalMinutes);
                }
                $parent->save();
            }

            if (config_option("wysiwyg_tasks")) {
                $task_data['type_content'] = "html";
                if (array_var($task_data, 'text') !== null) {
                    $task_data['text'] = str_replace(array("\r", "\n", "\r\n"), array('', '', ''), array_var($task_data, 'text'));
                }
            } else {
                $task_data['type_content'] = "text";
            }
            $task->setFromAttributes($task_data);

            if (array_var($_GET, 'dont_mark_as_read')) {
                $is_read = $task->getIsRead(logged_user()->getId());
            }

            try {
                DB::beginWork();

                if (config_option('multi_assignment') && Plugins::instance()->isActivePlugin('crpm')) {
                    if (array_var($task_data, 'multi_assignment_aplly_change') == 'subtask') {
                        $null = null;
                        Hook::fire('edit_subtasks', $task, $null);
                    }
                }

                // calculate and set estimated time
                if (array_var($task_data, 'hours') !== null || array_var($task_data, 'minutes') !== null) {
                    $totalMinutes = (array_var($task_data, 'hours') * 60) + (array_var($task_data, 'minutes'));
                    $task->setTimeEstimate($totalMinutes);
                }
                $task->save();

                $task->calculatePercentComplete();

                // get member ids
                $member_ids = array();
                if (array_var($task_data, 'members')) {
                    $member_ids = json_decode(array_var($task_data, 'members'));
                }

                if (isset($task_data['keep_members']) && $task_data['keep_members']) {
                    $member_ids = $task->getMemberIds();
                }

                $remove_members_from_dim = array_var($task_data, 'remove_from_dimension');
                if (isset($task_data['remove_from_dimension'])) {
                    $old_members = $task->getMembers();
                    foreach ($old_members as $old_mem) {
                        if ($old_mem->getDimensionId() != $remove_members_from_dim)
                            $member_ids[] = $old_mem->getId();
                    }
                }

                // get member id when changing member via drag & drop
                if (array_var($task_data, 'member_id')) {
                    $member_ids[] = array_var($task_data, 'member_id');
                }

                // drag & drop - also apply changes to subtasks
                $tasks_to_update = $task->getAllSubTasks();
                $tasks_to_update[] = $task;

                $assignee = $task->getAssignedToContact();
                if ($assignee instanceof Contact) {
                    $task->subscribeUser($assignee);
                }

                // add to members, subscribers, etc
                $object_controller = new ObjectController();
                if (count($member_ids) > 0 || !isset($task_data['remove_from_dimension'])) {
                    foreach ($tasks_to_update as $task_to_update) {
                        $object_controller->add_to_members($task_to_update, $member_ids);
                    }
                }

                $task->resetIsRead();

                $log_info = '';
                if ($send_edit == true) {
                    $log_info = $task->getAssignedToContactId();
                } else if ($send_edit == false) {
                    $task->setAssignedBy(logged_user());
                    $task->save();
                }


                //edit reminders accordingly
                if ($task->getDueDate() != null && !$task->isCompleted() && $task->getSubscriberIds() != null) { //to make sure the task has a due date and it is not completed yet, and that it has subscribed people						
                    $old_reminders = ObjectReminders::getByObject($task);
                    if ($old_reminders != null) {
                        $object_controller = new ObjectController();
                        $object_controller->update_reminders($task, $old_reminders); //updating the old ones						
                    } else if ($task->getAssignedTo() == null //if there is no asignee, but it still has subscribers
                            || (user_config_option("add_self_task_autoreminder") && logged_user()->getId() == $task->getAssignedToContactId()) //if the user is going to set up reminders for his own tasks
                            || (user_config_option("add_task_autoreminder") && logged_user()->getId() != $task->getAssignedToContactId())) { //if the user is going to set up reminders for tasks assigned to its colleagues			
                        $reminder = new ObjectReminder();
                        $def = explode(",", user_config_option("reminders_tasks"));
                        $minutes = $def[2] * $def[1];
                        $reminder->setMinutesBefore($minutes);
                        $reminder->setType($def[0]);
                        $reminder->setContext("due_date");
                        $reminder->setObject($task);
                        $reminder->setUserId(0);
                        $date = $task->getDueDate();
                        if ($date instanceof DateTimeValue) {
                            $rdate = new DateTimeValue($date->getTimestamp() - $minutes * 60);
                            $reminder->setDate($rdate);
                        }
                        $reminder->save();
                    }
                }

                // subscribe
                $task->subscribeUser(logged_user());

                if ($type_related == "all" || $type_related == "news") {
                    $task_data['members'] = $member_ids;
                    unset($task_data['due_date']);
                    unset($task_data['use_due_time']);
                    unset($task_data['start_date']);
                    unset($task_data['use_start_time']);
                    $this->repetitive_tasks_related($task, "edit", $type_related, $task_data);
                }

                //for calculate member status we save de task again after the object have the members
                $task->save();

                DB::commit();

                // notify asignee
                if ((array_var($task_data, 'notify') == 'true' && $send_edit == false) || (user_config_option("can notify from quick add") && !user_config_option("show_notify_checkbox_in_quick_add") && $send_edit == false && !array_var($task_data, 'notify') == 'false')) {
                    try {
                        Notifier::taskAssigned($task);
                    } catch (Exception $e) {
                        Logger::log('Error sending notifications for task: ' . $task->getId());
                    } // try
                }

                $isSailent = true;
                if (array_var($task_data, 'notify') == 'true')
                    $isSailent = false;
                ApplicationLogs::createLog($task, ApplicationLogs::ACTION_EDIT, false, $isSailent, true, $log_info);

                $subs = array();
                $subtasks = $task->getAllSubTasks(); // findAll

                foreach ($subtasks as $sub) {
                    //control date parent whit subtask
                    if (array_var($task_data, 'control_dates') == "father") {
                        if ($sub->getStartDate() instanceof DateTimeValue) {
                            if ($task->getStartDate() instanceof DateTimeValue) {
                                if ($task->getStartDate()->getTimestamp() > $sub->getStartDate()->getTimestamp()) {
                                    $sub->setStartDate($task->getStartDate());
                                }
                            }
                        } else {
                            if ($task->getStartDate() instanceof DateTimeValue)
                                $sub->setStartDate($task->getStartDate());
                        }
                        $sub->setUseStartTime($task->getUseStartTime());
                        if ($sub->getDueDate() instanceof DateTimeValue) {
                            if ($task->getDueDate() instanceof DateTimeValue) {
                                if ($task->getDueDate()->getTimestamp() < $sub->getDueDate()->getTimestamp()) {
                                    $sub->setDueDate($task->getDueDate());
                                }
                            }
                        } else {
                            if ($task->getDueDate() instanceof DateTimeValue) {
                                $sub->setDueDate($task->getDueDate());
                            }
                        }
                        $sub->setUseDueTime($task->getUseDueTime());
                        $sub->save();
                    }
                    $subs[] = $sub->getArrayInfo();
                }
            } catch (Exception $e) {
                DB::rollback();
                flash_error($e->getMessage());
            } // try
        } // if
    }

    function get_task_data() {
        ajx_current("empty");
        $id = get_id();
        $task = ProjectTasks::instance()->findById($id);
        if ($task instanceof ProjectTask && $task->canView(logged_user())) {
            $data = array('id' => $id);
            if (array_var($_REQUEST, 'desc')) {
                $desc = $task->getText();
                $data['desc'] = $desc;
            }

            if (array_var($_REQUEST, 'task_info')) {
                $data['task'] = $task->getArrayInfo();
            }
        }
        ajx_extra_data($data);
    }

    function get_task_descriptions() {
        ajx_current("empty");
        $ids = explode(',', $_REQUEST['ids']);
        foreach ($ids as $k => &$id) {
            if (!is_numeric($id))
                $id = 0;
        }

        $data = array();
        if (is_array($ids) && count($ids) > 0) {
            $rows = DB::executeAll("SELECT object_id, `text` FROM " . TABLE_PREFIX . "project_tasks WHERE object_id IN (" . implode(',', $ids) . ")");
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $data['t' . $row['object_id']] = $row['text'];
                }
            }
        }
        ajx_extra_data(array('descriptions' => $data));
    }

    function multi_task_action() {
        ajx_current("empty");
        $ids = explode(',', array_var($_POST, 'ids'));
        $action = array_var($_POST, 'action');
        $options = array_var($_POST, 'options');

        if (!is_array($ids) || trim(array_var($_POST, 'ids')) == '' || count($ids) <= 0) {
            flash_error(lang('no items selected'));
            return;
        }

		// let ObjectController handle the trash operations 
        // if the task is not repetitive and more tasks needs to be deleted
		if ($action == 'delete' && !($options == "news" || $options == "all")) {
			$object_controller = new ObjectController();
			$object_controller->trash();
			ajx_current("reload");
			return;
		}

        $count_tasks = ProjectTasks::instance()->count('object_id in (' . implode(',', $ids) . ')');
        $tasksToReturn = array();
        $subt_info = array();
        $showSuccessMessage = true;
        $additional_error_message_info = "";
        $pending_subtasks_after_complete = array();

        $application_logs = array();
        try {
            DB::beginWork();
            $all_tasks = array();
            foreach ($ids as $id) {
                $task = Objects::findObject($id);
                if (!$task instanceof ProjectTask)
                    continue;
                $task->setDontMakeCalculations(true); // all the calculations should be after all tasks are saved
                $all_tasks[] = $task;
                
                // to use when saving the application log
                $old_content_object = $task->generateOldContentObjectData();
            	
                switch ($action) {
                    case 'complete':
                        if ($task->canEdit(logged_user())) {

                        	$result = $task->completeTask();
                            $log_info = $result['log_info'];
							if ($result['save_log']) {
								$application_logs[] = array($task, ApplicationLogs::ACTION_CLOSE, false, false, true, substr($log_info, 0, -1));
							}

                            $has_pending_sub = false;
                            foreach ($task->getAllSubTasks() as $sub) {
                                $tasksToReturn[] = $sub->getArrayInfo();
                                if ($sub->getCompletedById() == 0)
                                    $has_pending_sub = true;
                            }
                            if ($has_pending_sub) {
                                $pending_subtasks_after_complete[] = $task->getId();
                            }
                            $tasksToReturn[] = $task->getArrayInfo();
                        }
                        break;
                    case 'delete':
                        if ($task->canDelete(logged_user())) {
                            $tasksToReturn[] = $task->getArrayInfo();
                            $task->trash();
                            $application_logs[] = array($task, ApplicationLogs::ACTION_TRASH);
                            if ($options == "news" || $options == "all") {
                                $tasksToReturn_related = $this->repetitive_tasks_related($task, "delete", $options);
                                foreach ($tasksToReturn_related as $tasksToReturn_rel) {
                                    $tasksToReturn[] = array('id' => $tasksToReturn_rel);
                                }
                            }
                        }
                        break;
                    case 'archive':
                        if ($task->canEdit(logged_user())) {
                            $tasksToReturn[] = $task->getArrayInfo();
                            $task->archive();
                            $application_logs[] = array($task, ApplicationLogs::ACTION_ARCHIVE);
                            if ($options == "news" || $options == "all") {
                                $tasksToReturn_related = $this->repetitive_tasks_related($task, "archive", $options);
                                foreach ($tasksToReturn_related as $tasksToReturn_rel) {
                                    $tasksToReturn[] = array('id' => $tasksToReturn_rel);
                                }
                            }
                        }
                        break;
                    case 'start_work':
                        if ($task->canAddTimeslot(logged_user())) {
                            $timeslot = $task->addTimeslot(logged_user());
                            $application_logs[] = array($timeslot, ApplicationLogs::ACTION_OPEN, false, true);
                            $tasksToReturn[] = $task->getArrayInfo();
                            $showSuccessMessage = false;
                        }
                        break;
                    case 'close_work':
                        if ($task->canAddTimeslot(logged_user())) {
                            $timeslot = $task->closeTimeslots(logged_user(), array_var($_POST, 'options'));
                            $application_logs[] = array($timeslot, ApplicationLogs::ACTION_CLOSE, false, true);
                            $showSuccessMessage = false;
                            $task->calculatePercentComplete();
                            $tasksToReturn[] = $task->getArrayInfo();
                            Hook::fire('after_timer_closed', $timeslot, $ret);
			            	Hook::fire('calculate_estimated_and_executed_financials', array(), $task);
                        }
                        break;

                    case 'cancel_work':
                        if ($task->canAddTimeslot(logged_user())) {
                            $task->deleteTimeslots(logged_user());
                            $tasksToReturn[] = $task->getArrayInfo();
                            $showSuccessMessage = false;
                        }
                        break;
                    case 'pause_work':
                        if ($task->canAddTimeslot(logged_user())) {
                            $task->pauseTimeslots(logged_user());
                            $tasksToReturn[] = $task->getArrayInfo();
                            $showSuccessMessage = false;
                        }
                        break;
                    case 'resume_work':
                        if ($task->canAddTimeslot(logged_user())) {
                            $task->resumeTimeslots(logged_user());
                            $tasksToReturn[] = $task->getArrayInfo();
                            $showSuccessMessage = false;
                        }
                        break;
                    case 'reassign_tasks':
                        if (can_manage_tasks(logged_user()) && $task->canEdit(logged_user())) {
                            $user_id = array_var($_POST, 'reassign_to');
                            $user = Contacts::instance()->findById($user_id);

                            if ($user instanceof Contact && $user->isUser()) {

                                if (!can_task_assignee($user)) {
                                    $additional_error_message_info .= "\n - " . lang('task x cant be assigned to user y', $task->getName(), $user->getName());
                                } else {
                                    $task->setAssignedToContactId($user->getId());
                                    Hook::fire('calculate_estimated_and_executed_financials', array(), $task);
                                    $task->save();
                                    $tasksToReturn[] = $task->getArrayInfo();
                                    $application_logs[] = array($task, ApplicationLogs::ACTION_EDIT, false, array_var($_POST, 'send_subs_notifications'));

                                    if (array_var($_POST, 'send_assigned_to_notification')) {
                                        Notifier::taskAssigned($task);
                                    }
                                }
                            } else {
                                $task->setAssignedToContactId(0);
                                Hook::fire('calculate_estimated_and_executed_financials', array(), $task);
                                $task->save();
                                $tasksToReturn[] = $task->getArrayInfo();
                                $application_logs[] = array($task, ApplicationLogs::ACTION_EDIT, false, array_var($_POST, 'send_subs_notifications'));
                            }
                        }
                        break;
                    case 'push_tasks_dates':
                        if ($task->canEdit(logged_user())) {
                            $time_push = 0;
                            $time = array_var($_POST, 'time');

                            if (isset($time["days"]) && (int) $time["days"] != 0) {
                                $time_push = (int) $time["days"] * 24;
                            }

                            if (config_option('use_time_in_task_dates')) {
                                if (isset($time["hours"]) && (int) $time["hours"] != 0) {
                                    $time_push += (int) $time["hours"];
                                }
                            }

                            if (user_config_option('pushUseWorkingDays') != isset($time["use_only_working_days"])) {
                                set_user_config_option('pushUseWorkingDays', $time["use_only_working_days"], logged_user()->getId());
                            }

                            if ($time_push != 0) {
                                $dd = $task->getDueDate() instanceof DateTimeValue ? $task->getDueDate() : null;
                                $sd = $task->getStartDate() instanceof DateTimeValue ? $task->getStartDate() : null;

                                if ($dd) {
                                    $time_push_dd = $time_push;
                                    Hook::fire('task_push_dates_calculation', array('date' => $dd, 'only_working_days' => $time["use_only_working_days"]), $time_push_dd);
                                    $task->setDueDate($dd->advance($time_push_dd * 3600, false));
                                }
                                if ($sd) {
                                    $time_push_sd = $time_push;
                                    Hook::fire('task_push_dates_calculation', array('date' => $sd, 'only_working_days' => $time["use_only_working_days"]), $time_push_sd);
                                    $task->setStartDate($sd->advance($time_push_sd * 3600, false));
                                }
                            }
                            $task->save();
                            $tasksToReturn[] = $task->getArrayInfo();
                            $showSuccessMessage = false;
                        }
                        break;
                    case 'markasread':
                        $task->setIsRead(logged_user()->getId(), true);
                        $tasksToReturn[] = $task->getArrayInfo();
                        $showSuccessMessage = false;
                        break;
                    case 'markasunread':
                        $task->setIsRead(logged_user()->getId(), false);
                        $tasksToReturn[] = $task->getArrayInfo();
                        $showSuccessMessage = false;
                        break;
                    case 'markasbillable':
                        $task->setColumnValue('is_billable', 1);
                        $task->save();
                        $tasksToReturn[] = $task->getArrayInfo();
                        $showSuccessMessage = false;
                        break;
                    case 'markasnonbillable':
                        $task->setColumnValue('is_billable', 0);
                        $task->save();
                        $tasksToReturn[] = $task->getArrayInfo();
                        $showSuccessMessage = false;
                        break;
                    default:
                        //DB::rollback();
                        flash_error(lang('invalid action'));
                        return;
                } // end switch
            } // end foreach
 
            $ignored = null;
            Hook::fire('after_multi_object_action', array('objects' => $all_tasks, 'action' => $action, 'options' => $options), $ignored);

            DB::commit();

            foreach ($application_logs as $log) {
                if (count($log) >= 2 && $log[0] instanceof ApplicationDataObject) {
                    call_user_func_array('ApplicationLogs::createLog', $log);
                }
            }

            // ask to complete pending subtasks of completed tasks
            if (count($pending_subtasks_after_complete) > 0) {
                evt_add('ask to complete subtasks', array('parent_id' => implode(',', $pending_subtasks_after_complete)));
            }

            if (count($tasksToReturn) < $count_tasks) {
                flash_error(lang('tasks updated') . '. ' . lang('some tasks could not be updated due to permission restrictions') . $additional_error_message_info);
            } else if ($showSuccessMessage) {
                flash_success(lang('tasks updated'));
            }
            if (count($subt_info) > 0) {
                ajx_extra_data(array("tasks" => $tasksToReturn, 'subtasks' => $subt_info));
            } else {
                ajx_extra_data(array('tasks' => $tasksToReturn));
            }
        } catch (Exception $e) {
            DB::rollback();
            flash_error($e->getMessage());
        }
    }

    private function get_tasks_request_conditions() {
        // get query parameters, save user preferences if necessary
        $status = array_var($_REQUEST, 'status', null);
        if (is_null($status) || $status == '') {
            $status = user_config_option('task panel status', 2);
        } else
        if (user_config_option('task panel status') != $status) {
            set_user_config_option('task panel status', $status, logged_user()->getId());
        }

        $previous_filter = user_config_option('task panel filter', 'no_filter');

        $filter_from_date = getDateValue(array_var($_REQUEST, 'from_date'));
        if ($filter_from_date instanceof DateTimeValue) {
            $copFromDate = $filter_from_date;
            $filter_from_date = $filter_from_date->toMySQL();
        }

        $tasks_from_date = '';

        $filter_to_date = getDateValue(array_var($_REQUEST, 'to_date'));
        if ($filter_to_date instanceof DateTimeValue) {
            $copToDate = $filter_to_date;
            $filter_to_date = $filter_to_date->toMySQL();
        }
        $tasks_to_date = '';

        // only apply saved task dates filters if dates filter inputs are enabled
        if (user_config_option('tasksUseDateFilters')) {
            if (user_config_option('tasksDateStart') != $filter_from_date) {
                if ($filter_from_date != EMPTY_DATETIME || array_var($_REQUEST, 'resetDateStart')) {
                    set_user_config_option('tasksDateStart', $copFromDate, logged_user()->getId());
                } else {
                    $filter_from_date = user_config_option('tasksDateStart');
                }
            }

            if (user_config_option('tasksDateEnd') != $filter_to_date) {
                if ($filter_to_date != EMPTY_DATETIME || array_var($_REQUEST, 'resetDateEnd')) {
                    set_user_config_option('tasksDateEnd', $copToDate, logged_user()->getId());
                } else {
                    $filter_to_date = user_config_option('tasksDateEnd');
                }
            }
        }

		// if filter is empty then use empty datetime constant to prevent wrong filter initialization with now()
		if ($filter_from_date == '') {
			$filter_from_date = EMPTY_DATETIME;
		}
		if ($filter_to_date == '') {
			$filter_to_date = EMPTY_DATETIME;
		}
		// ---

        if ((($filter_from_date != EMPTY_DATETIME)) || (($filter_to_date != EMPTY_DATETIME))) {
            if (($filter_from_date != EMPTY_DATETIME)) {
                $dateFrom = DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, $filter_from_date);
                $dateFrom->advance(logged_user()->getUserTimezoneValue() * -1);
                $dateFrom = $dateFrom->toMySQL();
            }
            if (($filter_to_date != EMPTY_DATETIME)) {
                $dateTo = DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, $filter_to_date);
                $dateTo->setHour(23);
                $dateTo->setMinute(59);
                $dateTo->setSecond(59);
                $dateTo->advance(logged_user()->getUserTimezoneValue() * -1);
                $dateTo = $dateTo->toMySQL();
            }
            if ((($filter_from_date != EMPTY_DATETIME)) && (($filter_to_date != EMPTY_DATETIME))) {

                $tasks_from_date = " AND (((`e`.`start_date` BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "') AND `e`.`start_date` != " . DB::escape(EMPTY_DATETIME) . ") OR ((`e`.`due_date` BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "') AND `e`.`due_date` != " . DB::escape(EMPTY_DATETIME) . "))";
            } elseif (($filter_from_date != EMPTY_DATETIME)) {
                $tasks_from_date = " AND (`e`.`start_date` > '" . $dateFrom . "' OR `e`.`due_date` > '" . $dateFrom . "') ";
            } else {

                $tasks_from_date = "AND ((`e`.`start_date` < '" . $dateTo . "' AND `e`.`start_date` != " . DB::escape(EMPTY_DATETIME) . ") OR (`e`.`due_date` < '" . $dateTo . "' AND `e`.`due_date` != " . DB::escape(EMPTY_DATETIME) . "))";
            }
        } else {
            $tasks_from_date = "";
        }
        $filter = array_var($_REQUEST, 'filter');
        if (is_null($filter) || $filter == '') {
            $filter = $previous_filter;
        } else if ($previous_filter != $filter) {
            set_user_config_option('task panel filter', $filter, logged_user()->getId());
        }

        if ($filter != 'no_filter') {
            $filter_value = array_var($_REQUEST, 'fval');
            if (is_null($filter_value) || $filter_value == '') {
                $filter_value = user_config_option('task panel filter value', null, logged_user()->getId());
                set_user_config_option('task panel filter value', $filter_value, logged_user()->getId());
                $filter = $previous_filter;
                set_user_config_option('task panel filter', $filter, logged_user()->getId());
            } else
            if (user_config_option('task panel filter value') != $filter_value) {
                set_user_config_option('task panel filter value', $filter_value, logged_user()->getId());
            }
        }
        /* 	$isJson = array_var($_GET,'isJson',false);
          if ($isJson) ajx_current("empty");
         */
        $template_condition = "`e`.`is_template` = 0 ";

        //Get the task query conditions
        $task_filter_condition = "";

        switch ($filter) {
            case 'assigned_to':
                $assigned_to = $filter_value;
                if ($assigned_to > 0) {
                    $task_filter_condition = " AND (`assigned_to_contact_id` = " . $assigned_to . ") ";
                } else {
                    if ($assigned_to == -1)
                        $task_filter_condition = " AND `assigned_to_contact_id` = 0";
                }
                break;
            case 'assigned_by':
                if ($filter_value != 0) {
                    $task_filter_condition = " AND  `assigned_by_id` = " . $filter_value . " ";
                }
                break;
            case 'created_by':
                if ($filter_value != 0) {
                    $task_filter_condition = " AND  o.`created_by_id` = " . $filter_value . " ";
                }
                break;
            case 'completed_by':
                if ($filter_value != 0) {
                    $task_filter_condition = " AND  e.`completed_by_id` = " . $filter_value . " ";
                }
                break;
            case 'milestone':
                $task_filter_condition = " AND  `milestone_id` = " . $filter_value . " ";
                break;
            case 'priority':
                $task_filter_condition = " AND  `priority` = " . $filter_value . " ";
                break;
            case 'subtype':
                if ($filter_value != 0) {
                    $task_filter_condition = " AND  `object_subtype` = " . $filter_value . " ";
                }
                break;
            case 'subscribed_to':
                if ($filter_value > 0) {
                    /*$res20 = DB::execute("SELECT object_id FROM " . TABLE_PREFIX . "object_subscriptions WHERE `contact_id` = " . $filter_value);
                    $subs_rows = $res20->fetchAll($res20);
                    $subs = array();
                    if (count($subs_rows) > 0) {
                        foreach ($subs_rows as $row)
                            $subs[] = $row['object_id'];
                        unset($res20, $subs_rows, $row);
                        if (count($subs) > 0) {
                            $task_filter_condition = ($status==1 ? "" : " AND `e`.`completed_on` = " . DB::escape(EMPTY_DATETIME)) . " AND `o`.`id` IN(" . implode(',', $subs) . ")";
                        }
                    } else {
                        $task_filter_condition = ($status==1 ? "" : " AND `e`.`completed_on` = " . DB::escape(EMPTY_DATETIME)) . " AND `o`.`id` = -1";
                    }
                    */
                    $subscribed_to_subquery = "SELECT object_id FROM " . TABLE_PREFIX . "object_subscriptions WHERE `contact_id` = " . $filter_value;
                    $task_filter_condition = ($status==1 ? "" : " AND `e`.`completed_on` = " . DB::escape(EMPTY_DATETIME)) . " AND `o`.`id` IN(" . $subscribed_to_subquery . ")";
                }
                break;
            case 'no_filter':
                $task_filter_condition = "";
                break;
            default:
                $result = null;
                Hook::fire('additional_task_list_filter_conditions', array('filter' => $filter, 'filter_value' => $filter_value), $result);
                if (!$result) {
                    flash_error(lang('task filter criteria not recognised', $filter));
                } else {
                    $task_filter_condition = implode(' ', array_var($result, 'conditions', array()));
                }
        }

        $task_status_condition = "";
        $now_date = DateTimeValueLib::now();
        $now_date->advance(logged_user()->getUserTimezoneValue());
        $now = $now_date->format('Y-m-d 00:00:00');
        $now_end = $now_date->format('Y-m-d 23:59:59');
        switch ($status) {
            case 0: // Incomplete tasks
                $task_status_condition = " AND `e`.`completed_on` = " . DB::escape(EMPTY_DATETIME);
                break;
            case 1: // Complete tasks
                $task_status_condition = " AND `e`.`completed_on` > " . DB::escape(EMPTY_DATETIME);
                break;
            case 10: // Active tasks
                $task_status_condition = " AND (SELECT COUNT(ts.object_id) FROM " . TABLE_PREFIX . "timeslots ts WHERE ts.rel_object_id=o.id AND ts.end_time = '" . EMPTY_DATETIME . "') > 0";
                break;
            case 11: // Overdue tasks
                //$task_status_condition = " AND `e`.`completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `e`.`due_date` < '$now'";
                $task_status_condition = " AND `e`.`completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `e`.`due_date` < '$now' AND `e`.`due_date` != " .DB::escape(EMPTY_DATETIME);
                break;
            case 12: // Today tasks
                $task_status_condition = " AND `e`.`completed_on` = " . DB::escape(EMPTY_DATETIME);
                $task_status_condition .= " AND (((`e`.`start_date` BETWEEN '" . $now . "' AND '" . $now_end . "') AND `e`.`start_date` != " . DB::escape(EMPTY_DATETIME) . ") OR ((`e`.`due_date` BETWEEN '" . $now . "' AND '" . $now_end . "') AND `e`.`due_date` != " . DB::escape(EMPTY_DATETIME) . "))";
                break;
            case 13: // Today + Overdue tasks
            	$task_status_condition = " AND `e`.`completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `e`.`due_date` <= '$now_end'";
            	break;
            case 14: // Without due date
            	$task_status_condition = " AND `e`.`due_date` = " . DB::escape(EMPTY_DATETIME);
            	break;
            case 15: // Upcoming tasks
            	$task_status_condition = " AND `e`.`due_date` >= " . DB::escape($now) . " AND `e`.`completed_on` = " . DB::escape(EMPTY_DATETIME);
            	break;
            case 20: // Actives task by current user
                $task_status_condition = " AND `e`.`completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `e`.`start_date` <= '$now' AND `e`.`assigned_to_contact_id` = " . logged_user()->getId();
                break;
            case 21: // Subscribed tasks by current user
                $res20 = DB::execute("SELECT object_id FROM " . TABLE_PREFIX . "object_subscriptions WHERE `contact_id` = " . logged_user()->getId());
                $subs_rows = $res20->fetchAll($res20);
                foreach ($subs_rows as $row)
                    $subs[] = $row['object_id'];
                unset($res20, $subs_rows, $row);
                $task_status_condition = " AND `e`.`completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `o`.`id` IN(" . implode(',', $subs) . ")";
                break;
            case 2: // All tasks
                break;
            default:
                throw new Exception('Task status "' . $status . '" not recognised');
        }

        $task_assignment_conditions = "";
        if (!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks')) {
            $task_assignment_conditions = " AND assigned_to_contact_id = " . logged_user()->getId();
        }

        $conditions = "AND $template_condition $task_filter_condition $task_status_condition $task_assignment_conditions $tasks_from_date";

        $data = array();
        $data['conditions'] = $conditions;
        $data['filterValue'] = isset($filter_value) ? $filter_value : '';
        $data['filter'] = $filter;
        $data['status'] = $status;
        $data['limit'] = array_var($_REQUEST, 'limit', user_config_option('task_display_limit', 999));
        return $data;
    }

    //TASK GROUP HELPER START
    private function cmpGroupOrder($a, $b) {
        return strcmp($a["group_order"], $b["group_order"]);
    }

    private function getGroupTotals($conditions, $group_time_estimate = null, $join_on_extra = null) {
        if (is_null($join_on_extra)) {
            $join_on_extra = "";
        }
        $totals = array();
        //group totals
        $join_params['join_type'] = "LEFT ";
        $join_params['table'] = TABLE_PREFIX . "timeslots";
        $join_params['jt_field'] = "rel_object_id";
        $join_params['e_field'] = "object_id";
        $join_params['on_extra'] = $join_on_extra;
 
        // Estimated time
        $total_estimated = "SUM(time_estimate) AS group_time_estimate ";

        // Worked time
        $total_worked = "SUM(total_worked_time) AS group_time_worked";
        
        // Remaining time
        $remaining_time = "SUM(remaining_time) AS group_remaining_time";

        //querys returning total worked time, total estimated time and total pending time
        //time worked is the addition of all timeslots minus the addition of all pauses
        //time estimated is the addition of the substractions of estimated and worked, grouping by task to substract
        $group_totals = ProjectTasks::instance()->listing(array(
            "select_columns" => array("time_estimate", "total_worked_time", "remaining_time", "GREATEST(CONVERT(time_estimate, SIGNED INTEGER) - CONVERT(total_worked_time, SIGNED INTEGER), 0) AS pending"),
                "join_params" => $join_params,
                "extra_conditions" => $conditions,
                "group_by" => "e.`object_id`",
                "query_wraper_start" => "SELECT $total_estimated,  $total_worked, $remaining_time,  COALESCE(SUM(pending), 0) AS group_time_pending FROM (",
                "query_wraper_end" => ") AS pending_calc",
                "count_results" => false,
                "fire_additional_data_hook" => false,
                "raw_data" => true,
            ))->objects;  

        $group_time_estimate = $group_totals[0]['group_time_estimate'];
        $group_time_worked = $group_totals[0]['group_time_worked'];
        $group_time_worked = is_null($group_time_worked) ? 0 : $group_time_worked;
        $group_time_remaining = $group_totals[0]['group_remaining_time'];
        
        //$group_time_pending = $group_time_estimate - $group_time_worked;
        $group_time_pending = $group_totals[0]['group_time_pending'];
        if ($group_time_pending < 0) $group_time_pending = 0;

        // Overall group worked time includes subtasks time
        $group_overall_time_worked = $group_totals[0]['group_time_worked'];
        $group_overall_time_worked = is_null($group_overall_time_worked) ? 0 : $group_overall_time_worked;

        $totals['estimatedTime'] = str_replace(',', ',<br>', DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($group_time_estimate * 60), 'hm', 60));
        $totals['totalEstimatedTime'] = str_replace(',', ',<br>', DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($group_time_estimate * 60), 'hm', 60));

        $totals['worked_time'] = $group_time_worked;
        $totals['worked_time_string'] = ($group_time_worked <= 0) ? "" : str_replace(',', ',<br>', DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($group_time_worked * 60), 'hm', 60));
        $totals['pending_time'] = $group_time_pending; 
        $totals['pending_time_string'] = ($group_time_pending <= 0) ? "" : str_replace(',', ',<br>', DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($group_time_pending * 60), 'hm', 60));

        // Remaining time and Total remaining time
        $totals['remaining_time'] = $group_time_remaining;
        $totals['remaining_time_string'] = str_replace(',', ',<br>', DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($group_time_remaining * 60), 'hm', 60));
        $totals['total_remaining_time'] = $group_time_remaining;
        $totals['total_remaining_time_string'] = str_replace(',', ',<br>', DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($group_time_remaining * 60), 'hm', 60));


        // Overall worked time includes subtasks time
        $totals['overall_worked_time'] = $group_overall_time_worked;
        $totals['overall_worked_time_string'] = ($group_overall_time_worked <= 0) ? "" : str_replace(',', ',<br>', DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($group_overall_time_worked * 60), 'hm', 60));

		// add more totals to the group for columns that are added by plugins, like the financial info
        Hook::fire('add_task_group_totals', array(
			'group_totals' => $group_totals, 
			'join_params' => $join_params,
			'conditions' => $conditions,
		), $totals);

        return $totals;
    }

    private function getDateGroups($date_field, $conditions, $show_more_conditions, $list_subtasks_cond, $only_totals = false, &$groups_offset = 0, $groups_count = 0) {
		if (!isset($show_more_conditions)) $show_more_conditions = array();
        $groupId = array_var($show_more_conditions, 'groupId');
        $start   = array_var($show_more_conditions, 'start', 0);
        $limit   = array_var($show_more_conditions, 'limit', user_config_option('noOfTasks'));

		$groups_conditions = $this->getDateGroupsConditions($date_field);
        
        // move to offset
        if ($groups_count > 0) {
        	$groups_conditions = array_slice($groups_conditions, $groups_offset, count($groups_conditions));
        }
        
        $groups_with_tasks = 0;

        $groups = array();
        foreach ($groups_conditions as $group_conditions) {
            if (!is_null($groupId) && $group_conditions['id'] != $groupId) {
                continue;
            }
            $group_conditions_cond = $conditions . " AND " . $group_conditions['conditions'];

            $group = ProjectTasks::instance()->listing(array(
                        "select_columns" => array("COUNT(o.id) AS total "),
                        "extra_conditions" => $group_conditions_cond . $list_subtasks_cond,
                        "count_results" => false,
						"fire_additional_data_hook" => false,
                        "raw_data" => true,
                    ))->objects;

            if ($group[0]["total"] > 0) {
                $group[0]["group_name"] = $group_conditions['group_name'];
                $group[0]["group_order"] = $group_conditions['group_order'];
                $group[0]["group_id"] = $group_conditions['id'];

                if (!$only_totals) {
                	$tasks_in_group = $this->getTasksInGroup($group_conditions_cond . $list_subtasks_cond, $start, $limit);
	                $group[0]['root_total'] = $tasks_in_group['total_roots_tasks'];
	                $group[0]['group_tasks'] = $tasks_in_group['tasks'];
                }

                //group totals
                $totals = $this->getGroupTotals($group_conditions_cond);
                foreach ($totals as $key => $total) {
                    $group[0][$key] = $total;
                }

                $groups[] = $group[0];
                
                $groups_with_tasks++;
                // when group count is reached -> finish iteration
                if ($groups_with_tasks >= $groups_count) {
                	break;
                }
            } else {
            	// advance offset if group has no tasks
            	$groups_offset++;
            }
        }

        return $groups;
    }

    private function getDateGroupsConditions($date_field) {
        $t_prefix = "`e`.";
        if ($date_field == 'created_on') {
            $t_prefix = "`o`.";
        }

        $unknown_group_name = $date_field;
        $date_field = $t_prefix . "`" . $date_field . "`";

        $date_groups = array();

        $current = DateTimeValueLib::now();
        $current->advance(logged_user()->getUserTimezoneValue());

        //Relative dates starts
        $relative = array();
        $relative['first_day_of_year'] = strToTime('first day of January this year', $current->getTimestamp());
        $relative['first_day_of_last_month'] = strToTime('first day of previous month', $current->getTimestamp());
        $relative['first_day_of_this_month'] = strToTime('first day of this month', $current->getTimestamp());
        $relative['first_day_of_last_week'] = strToTime('monday previous week', $current->getTimestamp());
        $relative['last_day_of_last_week'] = strToTime('sunday previous week', $current->getTimestamp());
        $relative['first_day_of_this_week'] = strToTime('monday this week', $current->getTimestamp());
        $relative['yesterday'] = strToTime('yesterday', $current->getTimestamp());
        $relative['today'] = strToTime('today', $current->getTimestamp());
        $relative['tomorrow'] = strToTime('tomorrow', $current->getTimestamp());
        $relative['last_day_of_this_week'] = strToTime('sunday this week', $current->getTimestamp());
        $relative['first_day_of_next_week'] = strToTime('monday next week', $current->getTimestamp());
        $relative['last_day_of_next_week'] = strToTime('sunday next week', $current->getTimestamp());
        $relative['last_day_of_this_month'] = strToTime('last day of this month', $current->getTimestamp());
        $relative['last_day_of_next_month'] = strToTime('last day of next month', $current->getTimestamp());
        $relative['last_day_of_next_3_months'] = strToTime('last day of +3 month', $current->getTimestamp());
        $relative['last_day_of_this_year'] = strToTime('last day of december this year', $current->getTimestamp());

        foreach ($relative as $key => &$value) {
            $value = DateTimeValueLib::makeFromString(date(DATE_MYSQL, $value));
            $value->beginningOfDay();
        }
        //Relative dates ends
        $relative_ends = array();
        foreach ($relative as $key => &$value) {
            $new_value = clone $value;
            $relative_ends[$key] = $new_value->endOfDay();
        }

        //if use_time_in_task_dates today condition is `due_date` >= '2015-03-04 02:00:00' AND `due_date` <= '2015-03-05 01:59:59' in GMT - 2
        //else today condition is `due_date` >= '2015-03-04 00:00:00' AND `due_date` <= '2015-03-04 23:59:59'
        //example date 04/03/2015
        if (config_option('use_time_in_task_dates') || $date_field == "`created_on`") {
            foreach ($relative as $key => &$value) {
                $value->advance(-logged_user()->getUserTimezoneValue());
            }

            foreach ($relative_ends as $key => &$value) {
                $value->advance(-logged_user()->getUserTimezoneValue());
            }
        }

        $not_empty_date = " AND " . $date_field . " <> '0000-00-00 00:00:00'";

        //before this year //check if last month is on last year
        $group_1 = array();
        $group_1['group_name'] = lang('before this year');
        $group_1['group_order'] = 1;
        $group_1['id'] = 'group_before_this_year';
        $condition = $relative['first_day_of_year'] < $relative['first_day_of_last_month'] ?
                $date_field . " < '" . $relative['first_day_of_year']->toMySQL() . "'" . $not_empty_date : 
                $date_field . " < '" . $relative['first_day_of_last_month']->toMySQL() . "'" . $not_empty_date;
        $group_1['conditions'] = $condition;
        $date_groups[] = $group_1;

        //this year (before last month)
        if ($relative['first_day_of_year'] < $relative['first_day_of_last_month']) {
            $group_2 = array();
            $group_2['group_name'] = lang('this year (before last month)');
            $group_2['group_order'] = 2;
            $group_2['id'] = 'group_this_year_before_last_month';
            $condition = $date_field . " >= '" . $relative['first_day_of_year']->toMySQL() . "'";
            $condition .= " AND " . $date_field . " < '" . $relative['first_day_of_last_month']->toMySQL() . "'";
            $group_2['conditions'] = $condition;
            $date_groups[] = $group_2;
        }

        //last month
        $group_3 = array();
        $group_3['group_name'] = lang('last month');
        $group_3['group_order'] = 3;
        $group_3['id'] = 'group_last_month';
        $condition = $date_field . " >= '" . $relative['first_day_of_last_month']->toMySQL() . "'";
        $condition .= $relative['first_day_of_this_month'] < $relative['first_day_of_last_week'] ?
                " AND " . $date_field . " < '" . $relative['first_day_of_this_month']->toMySQL() . "'" :
                " AND " . $date_field . " < '" . $relative['first_day_of_last_week']->toMySQL() . "'";
        $group_3['conditions'] = $condition;
        $date_groups[] = $group_3;

        //this month(before last week)
        if ($relative['first_day_of_this_month'] < $relative['first_day_of_last_week']) {
            $group_4 = array();
            $group_4['group_name'] = lang('this month(before last week)');
            $group_4['group_order'] = 4;
            $group_4['id'] = 'group_this_month_before_last_week';
            $condition = $date_field . " >= '" . $relative['first_day_of_this_month']->toMySQL() . "'";
            $condition .= " AND " . $date_field . " < '" . $relative['first_day_of_last_week']->toMySQL() . "'";
            $group_4['conditions'] = $condition;
            $date_groups[] = $group_4;
        }

        //last week (week start on monday and finish on sunday)
        $group_5 = array();
        $group_5['group_name'] = lang('last week');
        $group_5['group_order'] = 5;
        $group_5['id'] = 'group_last_week';
        $condition = $date_field . " >= '" . $relative['first_day_of_last_week']->toMySQL() . "'";
        $condition .= $relative['yesterday'] == $relative['last_day_of_last_week'] ? 
                " AND " . $date_field . " <= '" . $relative['last_day_of_last_week']->toMySQL() . "'" :
                " AND " . $date_field . " <= '" . $relative_ends['last_day_of_last_week']->toMySQL() . "'";
        $group_5['conditions'] = $condition;
        $date_groups[] = $group_5;
      
        //this week(before yesterday)
        if ($relative['first_day_of_this_week'] < $relative['yesterday']) {
            $group_6 = array();
            $group_6['group_name'] = lang('this week(before yesterday)');
            $group_6['group_order'] = 6;
            $group_6['id'] = 'group_this_week_before_yesterday';
            $condition = $date_field . " >= '" . $relative['first_day_of_this_week']->toMySQL() . "'";
            $condition .= " AND " . $date_field . " < '" . $relative['yesterday']->toMySQL() . "'";
            $group_6['conditions'] = $condition;
            $date_groups[] = $group_6;
        }

        //yesterday
        $group_7 = array();
        $group_7['group_name'] = lang('yesterday');
        $group_7['group_order'] = 7;
        $group_7['id'] = 'group_yesterday';
        $condition = $date_field . " >= '" . $relative['yesterday']->toMySQL() . "'";
        $condition .= " AND " . $date_field . " <= '" . $relative_ends['yesterday']->toMySQL() . "'";
        $group_7['conditions'] = $condition;
        $date_groups[] = $group_7;

        //today
        $group_8 = array();
        $group_8['group_name'] = lang('today');
        $group_8['group_order'] = 8;
        $group_8['id'] = 'group_today';
        $condition = $date_field . " >= '" . $relative['today']->toMySQL() . "'";
        $condition .= " AND " . $date_field . " <= '" . $relative_ends['today']->toMySQL() . "'";
        $group_8['conditions'] = $condition;
        $date_groups[] = $group_8;

        //tomorrow
        $group_9 = array();
        $group_9['group_name'] = lang('tomorrow');
        $group_9['group_order'] = 9;
        $group_9['id'] = 'group_tomorrow';
        $condition = $date_field . " >= '" . $relative['tomorrow']->toMySQL() . "'";
        $condition .= " AND " . $date_field . " <= '" . $relative_ends['tomorrow']->toMySQL() . "'";
        $group_9['conditions'] = $condition;
        $date_groups[] = $group_9;

        //this week(later tomorrow)
        if ($relative['tomorrow'] < $relative['last_day_of_this_week']) {
            $group_10 = array();
            $group_10['group_name'] = lang('this week(later tomorrow)');
            $group_10['group_order'] = 10;
            $group_10['id'] = 'group_this_week_later_tomorrow';
            $condition = $date_field . " > '" . $relative_ends['tomorrow']->toMySQL() . "'";
            $condition .= " AND " . $date_field . " <= '" . $relative_ends['last_day_of_this_week']->toMySQL() . "'";
            $group_10['conditions'] = $condition;
            $date_groups[] = $group_10;
        }

        //next week
        $group_11 = array();
        $group_11['group_name'] = lang('next week');
        $group_11['group_order'] = 11;
        $group_11['id'] = 'group_next_week';
        $condition = $relative['tomorrow'] == $relative['first_day_of_next_week'] ? 
                $date_field . " >= '" . $relative_ends['first_day_of_next_week']->toMySQL() . "'" :
                $date_field . " >= '" . $relative['first_day_of_next_week']->toMySQL() . "'";
        $condition .= " AND " . $date_field . " <= '" . $relative_ends['last_day_of_next_week']->toMySQL() . "'";
        $group_11['conditions'] = $condition;
        $date_groups[] = $group_11;

        //this month(after next week)
        if ($relative['last_day_of_next_week'] < $relative['last_day_of_this_month']) {
            $group_12 = array();
            $group_12['group_name'] = lang('this month(after next week)');
            $group_12['group_order'] = 12;
            $group_12['id'] = 'group_this_month_after_next_week';
            $condition = $date_field . " > '" . $relative_ends['last_day_of_next_week']->toMySQL() . "'";
            $condition .= " AND " . $date_field . " <= '" . $relative_ends['last_day_of_this_month']->toMySQL() . "'";
            $group_12['conditions'] = $condition;
            $date_groups[] = $group_12;
        }

        //next month  //before next week
        $group_13 = array();
        $group_13['group_name'] = lang('next month');
        $group_13['group_order'] = 13;
        $group_13['id'] = 'group_next_month';
        $condition = $relative['last_day_of_next_week'] < $relative['last_day_of_this_month'] ?
                $date_field . " > '" . $relative_ends['last_day_of_this_month']->toMySQL() . "'" :
                $date_field . " > '" . $relative_ends['last_day_of_next_week']->toMySQL() . "'";
        $condition .= " AND " . $date_field . " <= '" . $relative_ends['last_day_of_next_month']->toMySQL() . "'";
        $group_13['conditions'] = $condition;
        $date_groups[] = $group_13;

        //next three months(after next month)
        $group_14 = array();
        $group_14['group_name'] = lang('next three months(after next month)');
        $group_14['group_order'] = 14;
        $group_14['id'] = 'group_next_three_months_after_next_month';
        $condition = $date_field . " > '" . $relative_ends['last_day_of_next_month']->toMySQL() . "'";
        $condition .= " AND " . $date_field . " <= '" . $relative_ends['last_day_of_next_3_months']->toMySQL() . "'";
        $group_14['conditions'] = $condition;
        $date_groups[] = $group_14;

        //this year
        if ($relative['last_day_of_next_3_months'] < $relative['last_day_of_this_year']) {
            $group_15 = array();
            $group_15['group_name'] = lang('this year');
            $group_15['group_order'] = 15;
            $group_15['id'] = 'group_this_year';
            $condition = $date_field . " > '" . $relative_ends['last_day_of_next_3_months']->toMySQL() . "'";
            $condition .= " AND " . $date_field . " <= '" . $relative_ends['last_day_of_this_year']->toMySQL() . "'";
            $group_15['conditions'] = $condition;
            $date_groups[] = $group_15;
        }

        //after this year //before next three months
        $group_16 = array();
        $group_16['group_name'] = lang('after this year');
        $group_16['group_order'] = 16;
        $group_16['id'] = 'group_after_this_year';
        $condition = $relative['last_day_of_next_3_months'] < $relative['last_day_of_this_year'] ? 
                $date_field . " > '" . $relative_ends['last_day_of_this_year']->toMySQL() . "'" . $not_empty_date :
                $date_field . " > '" . $relative_ends['last_day_of_next_3_months']->toMySQL() . "'" . $not_empty_date;
        $group_16['conditions'] = $condition;
        $date_groups[] = $group_16;

        //no date EMPTY_DATETIME
        if ($unknown_group_name == 'due_date') {
            $unknown_group_name = 'without due date';
        } elseif ($unknown_group_name == 'start_date') {
            $unknown_group_name = 'without start date';
        } else {
            $unknown_group_name = 'without date';
        }
        $group_17 = array();
        $group_17['group_name'] = lang($unknown_group_name);
        $group_17['group_order'] = 17;
        $group_17['id'] = 'group_undefined';
        $group_17['conditions'] = $date_field . " = '" . EMPTY_DATETIME . "'";
        $date_groups[] = $group_17;

        return $date_groups;
    }

    private function getPriorityGroups($conditions, $show_more_conditions, $list_subtasks_cond, $only_totals = false, &$groups_offset = 0, $groups_count = 0) {
        $priority_field = "`priority`";
		if (!isset($show_more_conditions)) $show_more_conditions = array();
        $groupId = array_var($show_more_conditions, 'groupId');
        $start   = array_var($show_more_conditions, 'start', 0);
        $limit   = array_var($show_more_conditions, 'limit', user_config_option('noOfTasks'));
        
        if ($groups_offset < 4) {
        	$groups_offset += $groups_count;
        } else {
        	// dont reload the groups if already loaded
        	return array();
        }

        $groups = ProjectTasks::instance()->listing(array(
                    "sql_before_columns" => 'DISTINCT ',
                    "select_columns" => array($priority_field . " AS group_id ", $priority_field . " AS group_name ", "COUNT(o.id) AS total"),
                    "extra_conditions" => $conditions . $list_subtasks_cond,
                    "group_by" => " `group_name`",
                    "count_results" => false,
					"fire_additional_data_hook" => false,
                    "raw_data" => true,
                ))->objects;
		
		if (!$groups) $groups = array();

        $more_group_ret = array();
        foreach ($groups as $key => $group) {
            if (!is_null($groupId) && $group['group_id'] != $groupId) {
                continue;
            }
            $group_conditions = " AND " . $priority_field . " = '" . $group['group_id'] . "'";
            if (!$only_totals) {
	            $tasks_in_group = $this->getTasksInGroup($conditions . $group_conditions . $list_subtasks_cond, $start, $limit);
	            $groups[$key]['root_total'] = $tasks_in_group['total_roots_tasks'];
	            $groups[$key]['group_tasks'] = $tasks_in_group['tasks'];
            }

            $groups[$key]['group_name'] = lang('priority ' . $group['group_id']);
            switch ($group['group_id']) {
                case 100:
                    $groups[$key]['group_icon'] = 'ico-task-low-priority';
                    $groups[$key]['group_order'] = 4;
                    break;
                case 200:
                    $groups[$key]['group_icon'] = 'ico-task';
                    $groups[$key]['group_order'] = 3;
                    break;
                case 300:
                    $groups[$key]['group_icon'] = 'ico-task-high-priority';
                    $groups[$key]['group_order'] = 2;
                    break;
                case 400:
                    $groups[$key]['group_icon'] = 'ico-task-high-priority';
                    $groups[$key]['group_order'] = 1;
                    break;
            }

            //group totals
            $totals = $this->getGroupTotals($conditions . $group_conditions);
            foreach ($totals as $total_key => $total) {
                $groups[$key][$total_key] = $total;
            }

            if (!is_null($groupId)) {
                $more_group_ret[] = $groups[$key];
            }
        }

        if (!is_null($groupId)) {
            return $more_group_ret;
        } else {
			if (isset($groups) && is_array($groups)) {
				usort($groups, array("TaskController", "cmpGroupOrder"));
			}
            return $groups;
        }
    }

    private function getMilestoneGroups($conditions, $show_more_conditions, $list_subtasks_cond, $include_empty_milestones = true, $only_totals = false, &$groups_offset = 0, $groups_count = 0) {
        $milestone_field = "`milestone_id`";
		if (!isset($show_more_conditions)) $show_more_conditions = array();
        $groupId = array_var($show_more_conditions, 'groupId');
        $start   = array_var($show_more_conditions, 'start', 0);
        $limit   = array_var($show_more_conditions, 'limit', user_config_option('noOfTasks'));

        $join_params['join_type'] = "LEFT ";
        $join_params['table'] = TABLE_PREFIX . "project_milestones";
        $join_params['jt_field'] = "object_id";
        $join_params['e_field'] = "milestone_id";
        $groups = ProjectTasks::instance()->listing(array(
                    "sql_before_columns" => 'DISTINCT ',
                    "select_columns" => array($milestone_field . " AS group_id ", $milestone_field . " AS group_name ", "COUNT(o.id) AS total"),
                    "extra_conditions" => $conditions . $list_subtasks_cond,
                    "group_by" => " `group_name`",
                    "order" => " ISNULL(`jt`.`due_date`), `jt`.`due_date`",
                    "order_dir" => 'ASC',
                    "join_params" => $join_params,
                    "count_results" => false,
					"fire_additional_data_hook" => false,
                    "raw_data" => true,
                ))->objects;
		
		if (!$groups) $groups = array();
		if ($groups_count > 0) {
			$groups = array_slice($groups, $groups_offset, count($groups));
		}
		
        $more_group_ret = array();
        foreach ($groups as $key => $group) {
            if (!is_null($groupId) && $group['group_id'] != $groupId) {
                continue;
            }
            $group_conditions = " AND " . $milestone_field . " = '" . $group['group_id'] . "'";
            if (!$only_totals) {
	            $tasks_in_group = $this->getTasksInGroup($conditions . $group_conditions . $list_subtasks_cond, $start, $limit);
	            $groups[$key]['root_total'] = $tasks_in_group['total_roots_tasks'];
	            $groups[$key]['group_tasks'] = $tasks_in_group['tasks'];
            }
            
            if ($group['group_id'] > 0) {
                $milestone = ProjectMilestones::instance()->findById($group['group_id']);
                $groups[$key]['group_name'] = $milestone->getName();
            } else {
                $groups[$key]['group_name'] = lang('unclassified');
            }
            $groups[$key]['group_icon'] = 'ico-milestone';

            //group totals
            $totals = $this->getGroupTotals($conditions . $group_conditions);
            foreach ($totals as $total_key => $total) {
                $groups[$key][$total_key] = $total;
            }

            $more_group_ret[] = $groups[$key];
            
            if (count($more_group_ret) >= $groups_count) {
            	return $more_group_ret;
            }
        }

        if (user_config_option('tasksShowEmptyMilestones') && $include_empty_milestones) {
            //group totals
            $join_params['join_type'] = "LEFT ";
            $join_params['table'] = TABLE_PREFIX . "project_tasks";
            $join_params['jt_field'] = "milestone_id";
            $join_params['e_field'] = "object_id";

            $empty_milestones = ProjectMilestones::instance()->listing(array(
                        "select_columns" => array("`e`.`object_id` AS group_id ", "`name` AS group_name "),
                        "extra_conditions" => " AND `jt`.`object_id` IS NULL",
                        "join_params" => $join_params,
                        "count_results" => false,
                        "raw_data" => true,
                    ))->objects;

            foreach ($empty_milestones as $keym => $empty_milestone) {
                $empty_group = array();
                $empty_group['group_name'] = $empty_milestone['group_name'];
                $empty_group['group_id'] = $empty_milestone['group_id'];
                $empty_group['group_icon'] = 'ico-milestone';
                $empty_group['group_tasks'] = array();
                $more_group_ret[] = $empty_group;
            }
        }

        return $more_group_ret;
    }

    private function getUsersGroups($user_field, $conditions, $show_more_conditions, $list_subtasks_cond, $only_totals = false, &$groups_offset = 0, $groups_count = 0) {
        $unknown_text = 'unknown';
        switch ($user_field) {
            case 'assigned_to':
                $user_field = "`assigned_to_contact_id`";
                $unknown_text = 'unassigned';
                break;
            case 'created_by':
                $user_field = "o.`created_by_id`";
                break;
            case 'completed_by':
                $user_field = "`completed_by_id`";
                $unknown_text = 'pending';
                break;
            default:
                return array();
        }
		
		if (!isset($show_more_conditions)) $show_more_conditions = array();
        $groupId = array_var($show_more_conditions, 'groupId');
        $start   = array_var($show_more_conditions, 'start', 0);
        $limit   = array_var($show_more_conditions, 'limit', user_config_option('noOfTasks'));

        $users_groups = array();

        $groups = ProjectTasks::instance()->listing(array(
                    "sql_before_columns" => 'DISTINCT ',
                    "select_columns" => array($user_field . " AS group_id ", $user_field . " AS group_name ", "COUNT(o.id) AS total"),
                    "extra_conditions" => $conditions . $list_subtasks_cond,
                    "group_by" => " `group_name`",
                    "count_results" => false,
					"fire_additional_data_hook" => false,
                    "raw_data" => true,
                ))->objects;
		
		if (!$groups) $groups = array();
        if ($groups_count > 0) {
        	$groups = array_slice($groups, $groups_offset, count($groups));
        }

        $more_group_ret = array();
        foreach ($groups as $key => $group) {
            if (!is_null($groupId) && $group['group_id'] != $groupId) {
                continue;
            }

            $group_conditions = " AND " . $user_field . " = '" . $group['group_id'] . "'";
            if (!$only_totals) {
	            $tasks_in_group = $this->getTasksInGroup($conditions . $group_conditions . $list_subtasks_cond, $start, $limit);
	            $groups[$key]['root_total'] = $tasks_in_group['total_roots_tasks'];
	            $groups[$key]['group_tasks'] = $tasks_in_group['tasks'];
            }

            $contact = Contacts::instance()->findById($group['group_id']);
            if ($contact instanceof Contact) {
                $groups[$key]['group_name'] = $contact->getName();
                $groups[$key]['group_icon'] = 'ico-user';
            } else {
                $groups[$key]['group_name'] = lang($unknown_text);
                $groups[$key]['group_icon'] = 'ico-user';
            }

            //group totals
            $totals = $this->getGroupTotals($conditions . $group_conditions);
            foreach ($totals as $total_key => $total) {
                $groups[$key][$total_key] = $total;
            }

            $more_group_ret[] = $groups[$key];
            
            if (count($more_group_ret) >= $groups_count) {
            	break;
            }
        }

        return $more_group_ret;
        
    }

    private function getStatusGroups($conditions, $show_more_conditions, $list_subtasks_cond, $only_totals = false, &$groups_offset = 0, $groups_count = 0) {

		if (!isset($show_more_conditions)) $show_more_conditions = array();
        $groupId = array_var($show_more_conditions, 'groupId');
        $start   = array_var($show_more_conditions, 'start', 0);
        $limit   = array_var($show_more_conditions, 'limit', user_config_option('noOfTasks'));
        
        if ($groups_offset < 2) {
        	$groups_offset += $groups_count;
        } else {
        	// dont reload the groups if already loaded
        	return array();
        }

        $groups = ProjectTasks::instance()->listing(array(
                    "sql_before_columns" => 'DISTINCT ',
                    "select_columns" => array("(completed_by_id > 0) AS group_id ", "(completed_by_id > 0) AS group_name ", "COUNT(o.id) AS total"),
                    "extra_conditions" => $conditions . $list_subtasks_cond,
                    "group_by" => " `group_name`",
                    "count_results" => false,
					"fire_additional_data_hook" => false,
                    "raw_data" => true,
                ))->objects;

        $more_group_ret = array();
        foreach ($groups as $key => $group) {
            if (!is_null($groupId) && $group['group_id'] != $groupId) {
                continue;
            }

            $group_conditions = "";
            if ($group['group_id']) {
                $group_conditions = " AND completed_by_id > 0 ";
                $groups[$key]['group_name'] = lang('complete');
                $groups[$key]['group_icon'] = 'ico-complete';
            } else {
                $group_conditions = " AND completed_by_id = 0 ";
                $groups[$key]['group_name'] = lang('incomplete');
                $groups[$key]['group_icon'] = 'ico-delete';
            }

            if ($group_conditions != "") {
            	if (!$only_totals) {
	                $tasks_in_group = $this->getTasksInGroup($conditions . $group_conditions . $list_subtasks_cond, $start, $limit);
	                $groups[$key]['root_total'] = $tasks_in_group['total_roots_tasks'];
	                $groups[$key]['group_tasks'] = $tasks_in_group['tasks'];
            	}
                //group totals
                $totals = $this->getGroupTotals($conditions . $group_conditions);
                foreach ($totals as $total_key => $total) {
                    $groups[$key][$total_key] = $total;
                }
            }

            if (!is_null($groupId)) {
                $more_group_ret[] = $groups[$key];
            }
        }

        if (!is_null($groupId)) {
            return $more_group_ret;
        } else {
            return $groups;
        }
    }

    private function getDimensionGroups($dim_id, $member_type_id, $conditions, $show_more_conditions, $list_subtasks_cond, $only_totals = false, &$groups_offset = 0, $groups_count = 0) {
		if (!isset($show_more_conditions)) $show_more_conditions = array();
        $groupId = array_var($show_more_conditions, 'groupId');
        $start   = array_var($show_more_conditions, 'start', 0);
        $limit   = array_var($show_more_conditions, 'limit', user_config_option('noOfTasks'));

        $join_params['join_type'] = "INNER ";
        $join_params['table'] = TABLE_PREFIX . "object_members";
        $join_params['jt_field'] = "object_id";
        $join_params['e_field'] = "object_id";

        //this condition is used when we want show more task for a member
        $member_more_cond = "";
        if (!is_null($groupId) && $groupId > 0) {
            $member_more_cond = " AND  `jt`.`member_id` = $groupId";
        }

        $join_params['on_extra'] = " INNER  JOIN `" . TABLE_PREFIX . "members` `jtm` ON `jt`.`member_id` = `jtm`.`id` AND `jtm`.`dimension_id` = $dim_id AND `jtm`.`object_type_id` = $member_type_id $member_more_cond ";

        $join_params['on_extra'] .= " AND NOT EXISTS (
 										SELECT my.id FROM " . TABLE_PREFIX . "members my
 										INNER JOIN " . TABLE_PREFIX . "object_members tom ON my.id=tom.member_id
										WHERE my.dimension_id='$dim_id' AND my.object_type_id='$member_type_id' AND tom.object_id=o.id
  										AND my.depth > jtm.depth
		)";

		// initialize variables to prevent warnings/errors in the log
		$groups_to_return = array();
		$total_groups = 0;

        if (is_null($groupId) || $groupId > 0) {
            $groups = ProjectTasks::instance()->listing(array(
                        "sql_before_columns" => 'DISTINCT ',
                        "select_columns" => array("`jtm`.`id` AS group_id ", "`jtm`.`parent_member_id` AS group_parent ", "`jtm`.`name` AS group_name ", "`jtm`.`object_type_id` AS group_parent_type_id ", "`jtm`.`color` AS group_icon ", "COUNT(`e`.`object_id`) AS total"),
                        "extra_conditions" => $conditions . $list_subtasks_cond,
                        "group_by" => " `jtm`.`id`",
                        "order" => " `jtm`.`name`",
                        "order_dir" => " ASC",
                        "join_params" => $join_params,
                        "count_results" => false,
						"fire_additional_data_hook" => false,
                        "raw_data" => true,
                    ))->objects;

			if (!$groups) $groups = array();
            $total_groups = count($groups);
			// move to offset
			if ($groups_count > 0) {
				$groups = array_slice($groups, $groups_offset, count($groups));
			}
			$groups_to_return = array();
			
            foreach ($groups as $key => $group) {
                if (!is_null($groupId) && $group['group_id'] != $groupId) {
                    continue;
                }


                $group_conditions = " AND `jtm`.`id` = " . $group['group_id'];
                if (!$only_totals) {
	                $tasks_in_group = $this->getTasksInGroup($conditions . $group_conditions . $list_subtasks_cond, $start, $limit, $join_params);
	                $groups[$key]['root_total'] = $tasks_in_group['total_roots_tasks'];
	                $groups[$key]['group_tasks'] = $tasks_in_group['tasks'];
                }
                
                //group totals
                $group_time_estimate = ProjectTasks::instance()->listing(array(
                            "select_columns" => array("SUM(time_estimate) AS group_time_estimate "),
                            "extra_conditions" => $conditions . $group_conditions,
                            "join_params" => $join_params,
                            "count_results" => false,
							"fire_additional_data_hook" => false,
                            "raw_data" => true,
                        ))->objects;
                $group_time_estimate = $group_time_estimate[0]['group_time_estimate'];

                $join_on_extra = " INNER  JOIN `" . TABLE_PREFIX . "object_members` `jtom` ON `e`.`object_id` = `jtom`.`object_id` ";
                $join_on_extra .= " INNER  JOIN `" . TABLE_PREFIX . "members` `jtm` ON `jtom`.`member_id` = `jtm`.`id` AND `jtm`.`dimension_id` = $dim_id AND `jtm`.`object_type_id` = $member_type_id ";

                $join_on_extra .= " AND NOT EXISTS (
 										SELECT my.id FROM " . TABLE_PREFIX . "members my
 										INNER JOIN " . TABLE_PREFIX . "object_members tom ON my.id=tom.member_id
										WHERE my.dimension_id='$dim_id' AND my.object_type_id='$member_type_id' AND tom.object_id=o.id
  										AND my.depth > jtm.depth
				)";

                $totals = $this->getGroupTotals($conditions . $group_conditions, $group_time_estimate, $join_on_extra);

                foreach ($totals as $total_key => $total) {
                    $groups[$key][$total_key] = $total;
                }

                $groups[$key]['group_icon'] = "ico-color" . $group['group_icon'];

                //breadcrumb
                if ($group['group_parent']) {
                    $groups[$key]['group_memPath'] = json_encode(array($dim_id => array($group['group_parent_type_id'] => array($group['group_parent']))));
                }
                
                $groups_to_return[] = $groups[$key];
                
                if (count($groups_to_return) >= $groups_count) {
                	return $groups_to_return;
                }
            }
        }
        
        if ($groups_offset > $total_groups) {
	        // this means that the unknown group has already been loaded
        	return $groups_to_return;
        }

        //START unknown group
        if (is_null($groupId) || $groupId == 0) {
            $unknown_group['group_id'] = 0;
            $member_type = ObjectTypes::instance()->findById($member_type_id);
            $unknown_group['group_name'] = lang('without a member') . " " . lang($member_type->getName());

            $conditions .= " AND NOT EXISTS (
 										SELECT my.id FROM " . TABLE_PREFIX . "members my
 										INNER JOIN " . TABLE_PREFIX . "object_members tom ON my.id=tom.member_id
										WHERE my.dimension_id='$dim_id' AND my.object_type_id='$member_type_id' AND tom.object_id=o.id
			)";

            $tasks_in_group = $this->getTasksInGroup($conditions . $list_subtasks_cond, $start, $limit, '');

            $unknown_group['root_total'] = $tasks_in_group['total_roots_tasks'];
            $unknown_group['group_tasks'] = $tasks_in_group['tasks'];

            $unknown_group_totals = ProjectTasks::instance()->listing(array(
                        "select_columns" => array("time_estimate"),
                        "extra_conditions" => $conditions,
                        "join_params" => $join_params,
                        "count_results" => false,
                        "raw_data" => true,
						"fire_additional_data_hook" => false,
                        "query_wraper_start" => "SELECT count(*)  AS total  , SUM(time_estimate) AS group_time_estimate FROM (",
                        "query_wraper_end" => " ) AS temporal ",
                    ))->objects;

            $unknown_group['total'] = $unknown_group_totals[0]['total'];
            $unknown_group['group_time_estimate'] = $unknown_group_totals[0]['group_time_estimate'];
            $unknown_group['estimatedTime'] = str_replace(',', ',<br>', DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($unknown_group['group_time_estimate'] * 60), 'hm', 60));
            if (count($unknown_group['group_tasks']) > 0) {
                $groups_to_return[] = $unknown_group;
            }
        }
        //END unknown group

        return $groups_to_return;
    }

    private function getNothingGroups($conditions, $show_more_conditions, $list_subtasks_cond, $only_totals = false, &$groups_offset = 0, $groups_count = 0) {
        if (!isset($show_more_conditions)) $show_more_conditions = array();
        $groupId = array_var($show_more_conditions, 'groupId');
        $start   = array_var($show_more_conditions, 'start', 0);
        $limit   = array_var($show_more_conditions, 'limit', user_config_option('noOfTasks'));

        
        if ($groups_offset < 1) {
        	$groups_offset += $groups_count;
        } else {
        	// dont reload the groups if already loaded
        	return array();
        }

        $groups = ProjectTasks::instance()->listing(array(
                    "sql_before_columns" => 'DISTINCT ',
                    "select_columns" => array("COUNT(o.id) AS total"),
                    "extra_conditions" => $conditions . $list_subtasks_cond,
                    "count_results" => false,
					"fire_additional_data_hook" => false,
                    "raw_data" => true,
                ))->objects;

        //$more_group_ret = array();
        foreach ($groups as $key => $group) {
        	if (!$only_totals) {
        	    $tasks_in_group = $this->getTasksInGroup($conditions . $list_subtasks_cond, $start, $limit);
	            //$tasks_in_group = $this->getTasksInGroup($conditions . $group_conditions . $list_subtasks_cond, $start, $limit);
	
	            if (count($tasks_in_group['tasks']) <= 0) {
	                $groups = array();
	                continue;
	            }
        	}else{
                // *** LC 2023-10-03
                $tasks_in_group = array( 
                    'total_roots_tasks' => 0,
                    'tasks' => array(),
				);
                // end
            }

            $group_conditions = "";
            $groups[$key]['group_id'] = "nothing";
            $groups[$key]['group_name'] = lang('tasks');
            $groups[$key]['group_icon'] = 'ico-task';

            $groups[$key]['root_total'] = $tasks_in_group['total_roots_tasks'];
            $groups[$key]['group_tasks'] = $tasks_in_group['tasks'];

            //group totals
            $totals = $this->getGroupTotals($conditions . $group_conditions);
            foreach ($totals as $total_key => $total) {
                $groups[$key][$total_key] = $total;
            }
        }
        return $groups;
    }

    private function getTasksInGroup($conditions, $start, $limit, $join_params = null, $group_by = null) {
        $this->getListingOrderBy($order, $order_dir);

        //START tasks tree
        $list_subtasks = user_config_option('tasksShowSubtasksStructure') && !user_config_option('show_tasks_list_as_gantt');
        if ($list_subtasks) {
            // get the sql that brings all the root tasks ids

            $listing_sql = ProjectTasks::instance()->listing(array(
                "select_columns" => array("*"),
                "extra_conditions" => $conditions . " AND e2.object_id IN (e.parents_path) ",
                "join_params" => $join_params,
                "group_by" => $group_by,
                "count_results" => false,
				"fire_additional_data_hook" => false,
                "raw_data" => true,
                "only_return_query_string" => true,
            ));

            // generate the sql that appends to the general conditions the part to exclude the tasks that 
            // have their parent in the current group with the same conditions
            $sub_listing_sql = str_replace(array("`e`.", "e."), "e2.", $listing_sql);
            $sub_listing_sql = str_replace("project_tasks e", "project_tasks e2", $sub_listing_sql);
			
			/* these 2 lines have been commented because this is not necessary, as they are in a subquery, and sometimes were the provoking a bug
            $sub_listing_sql = str_replace(array("`om`.", "om."), "om2.", $sub_listing_sql);
            $sub_listing_sql = str_replace("object_members om", "object_members om2", $sub_listing_sql);
            $sub_listing_sql = str_replace("object_members tom", "object_members tom2", $sub_listing_sql);
            */

            $sub_listing_sql = str_replace("e2.parents_path", "e.parents_path", $sub_listing_sql);

            $conditions = $conditions . " AND NOT EXISTS($sub_listing_sql)";
        }
        //END tasks tree
        
        $original_order = $order;
        // when order is by assigned user => join with objects table and order by name
        if ($order == 'assigned_to_contact_id') {
            if (is_null($join_params) || empty($join_params)) {
                $join_params = array();
                $join_params['join_type'] = "LEFT ";
                $join_params['table'] = TABLE_PREFIX . "objects";
                $join_params['jt_field'] = "id";
                $join_params['e_field'] = "assigned_to_contact_id";

                $order = "jt.name";
            } else {
                $extra = " LEFT JOIN `" . TABLE_PREFIX . "objects` `c` ON `c`.`id` = `e`.`assigned_to_contact_id` ";

                if (isset($join_params['on_extra']))
                    $join_params['on_extra'] .= $extra;
                else
                    $join_params['on_extra'] = $extra;

                $order = "c.name";
            }
        }
        
        $hook_order_result = null;
        Hook::fire("override_tasks_list_order_by", array('order' => $order, 'join_params' => $join_params), $hook_order_result);
        if (is_array($hook_order_result)) {
        	if ($hook_order_result['order']) {
        		$order = $hook_order_result['order'];
        		if ($hook_order_result['order_dir']) $order_dir = $hook_order_result['order_dir'];
        	}
        	if ($hook_order_result['join_params']) {
        		$join_params = $hook_order_result['join_params'];
        	}
        }
        
        // when ordering by priority, ensure that they are ordered by name too
        if (in_array($original_order, array('priority', 'assigned_to_contact_id'))) {
        	$order = array($order, array('col' => 'o.name', 'dir' => 'ASC'));
        }

        $tasks_listing = ProjectTasks::instance()->listing(array(
            "select_columns" => array("e.*", "o.*"),
            "extra_conditions" => $conditions,
            "join_params" => $join_params,
            "group_by" => $group_by,
            "start" => $start,
            "limit" => $limit,
            "order" => $order,
            "order_dir" => $order_dir,
            "count_results" => true,
			"fire_additional_data_hook" => false,
            "raw_data" => true,
        ));

        $tasks =  $tasks_listing->objects ? $tasks_listing->objects : array();
        $total_see_roots_tasks = $tasks_listing->total;

        $task_ids = array();
        $tasks_array = array();
        
        foreach ($tasks as $task) {
            if (Plugins::instance()->isActivePlugin('advanced_billing')) {
				$full = true;
				$include_members_data = true;
				$tasks_array[] = ProjectTasks::getArrayInfo($task, $full, $include_members_data);
			} else {
				$tasks_array[] = ProjectTasks::getArrayInfo($task);
			}
            $task_ids[] = $task['object_id'];
        }

        $read_objects = ReadObjects::getReadByObjectList($task_ids, logged_user()->getId());
        foreach ($tasks_array as &$data) {
            $data['isread'] = isset($read_objects[$data['id']]);
        }

        $return_array = array();
        $return_array['total_roots_tasks'] = $total_see_roots_tasks;
        $return_array['tasks'] = $tasks_array;
        return $return_array;
    }

    private function getRootNodes(array $dataset_see) {
        $root_nodes_ids = array();

        $ids_array = array();
        foreach ($dataset_see as $node) {
            $ids_array[] = $node['object_id'];
        }

        foreach ($dataset_see as $node) {
            //is root 
            if ($node['parent_id'] == 0) {
                $root_nodes_ids[] = $node['object_id'];
                continue;
            }

            //check if there's an ancestor
            $is_root = true;
            $parents_ids = explode(",", $node['parents_path']);
            foreach ($parents_ids as $parent_id) {
                if (in_array($parent_id, $ids_array)) {
                    $is_root = false;
                    continue;
                }
            }
            if ($is_root) {
                $root_nodes_ids[] = $node['object_id'];
            }
        }

        return $root_nodes_ids;
    }

    private function getGroups($groupBy, $conditions, $show_more_conditions, $include_empty_milestones = true, $only_totals = false, &$groups_offset = 0, $groups_count = 0) {
        $groups = array();

        $group_by_date = array('due_date', 'start_date', 'created_on', 'completed_on');
        $group_by_priority = array('priority');
        $group_by_user = array('assigned_to', 'created_by', 'completed_by');
        $group_by_status = array('status');
        $group_by_nothing = array('nothing');
        $group_by_milestone = array('milestone');

        $list_subtasks_cond = "";

        //Group by date
        if (in_array($groupBy, $group_by_date)) {
            $groups = $this->getDateGroups($groupBy, $conditions, $show_more_conditions, $list_subtasks_cond, $only_totals, $groups_offset, $groups_count);
            //Group by priority
        } elseif (in_array($groupBy, $group_by_priority)) {
            $groups = $this->getPriorityGroups($conditions, $show_more_conditions, $list_subtasks_cond, $only_totals, $groups_offset, $groups_count);
            //Group by users
        } elseif (in_array($groupBy, $group_by_user)) {
            $groups = $this->getUsersGroups($groupBy, $conditions, $show_more_conditions, $list_subtasks_cond, $only_totals, $groups_offset, $groups_count);
            //Group by status
        } elseif (in_array($groupBy, $group_by_status)) {
            $groups = $this->getStatusGroups($conditions, $show_more_conditions, $list_subtasks_cond, $only_totals, $groups_offset, $groups_count);
            //Group by milestone
        } elseif (in_array($groupBy, $group_by_milestone)) {
            $groups = $this->getMilestoneGroups($conditions, $show_more_conditions, $list_subtasks_cond, $include_empty_milestones, $only_totals, $groups_offset, $groups_count);
            //Group by nothing
        } elseif (in_array($groupBy, $group_by_nothing)) {
            $groups = $this->getNothingGroups($conditions, $show_more_conditions, $list_subtasks_cond, $only_totals, $groups_offset, $groups_count);
            //Group by dimension
        } elseif (substr($groupBy, 0, 16) === "dimmembertypeid_") {
            $dim_str = substr($groupBy, 16);
            $dim_arr = explode("_", $dim_str);

            $dim_id = (int) $dim_arr[0];
            $member_type_id = (int) $dim_arr[1];

            //If Group by folder check context in order to decide which folder type use
            //Remove this part when the folders are all the same
            $otf = ObjectTypes::findByName('folder');
            if ($otf instanceof ObjectType && $otf->getId() == $member_type_id) {
                $acontext = active_context();

                $ot_customer = ObjectTypes::findByName('customer');
                $ot_customer_id = $ot_customer instanceof ObjectType ? $ot_customer->getId() : -1;

                $ot_project = ObjectTypes::findByName('project');
                $ot_project_id = $ot_project instanceof ObjectType ? $ot_project->getId() : -1;

                foreach ($acontext as $scontext) {
                    if ($scontext instanceof Member) {
                        $scontext_ot = $scontext->getObjectTypeId();
                        if ($scontext_ot == $ot_project_id) {
                            $ot_project_folder = ObjectTypes::findByName('project_folder');
                            if ($ot_project_folder instanceof ObjectType) $member_type_id = $ot_project_folder->getId();
                        }
                        if ($scontext_ot == $ot_customer_id) {
                            $ot_customer_folder = ObjectTypes::findByName('customer_folder');
                            if ($ot_customer_folder instanceof ObjectType) $member_type_id = $ot_customer_folder->getId();
                        }
                    }
                }
            }

            $groups = $this->getDimensionGroups($dim_id, $member_type_id, $conditions, $show_more_conditions, $list_subtasks_cond, $only_totals, $groups_offset, $groups_count);
        }

        return $groups;
    }

    //Return the groups where the task belongs to
    function get_groups_for_task() {
        ajx_current("empty");
        $task_id = array_var($_REQUEST, 'taskId', 0);
        $conditions = " AND `e`.`object_id` = $task_id";
        $groupBy = user_config_option('tasksGroupBy');
        $groups = $this->getGroups($groupBy, $conditions, null, false);

        if (is_null($groups)) {
            $groups = array();
        }
        $data['taskId'] = $task_id;
        $data['groups'] = $groups;
        ajx_extra_data($data);
    }

    //TASK GROUP HELPER END

    function get_tasks_groups_list() {
        ajx_current("empty");
        $data = array();
        $request_conditions = $this->get_tasks_request_conditions();
        $conditions = $request_conditions['conditions'];

        $groupId = array_var($_REQUEST, 'groupId', null);
        $start = array_var($_REQUEST, 'start', 0);
        $limit = array_var($_REQUEST, 'limit', user_config_option('noOfTasks'));
        $show_more_conditions = array("groupId" => $groupId, "start" => $start, "limit" => $limit);
        $only_totals = array_var($_REQUEST, 'only_totals');
        $groups_offset = array_var($_REQUEST, 'groups_offset');
        $groups_count = array_var($_REQUEST, 'groups_count');
        // load all groups data when requesting only totals
        if ($only_totals) {
        	$groups_offset = 0;
        	$groups_count = 1000;
        }

        //Groups
        $groupBy = array_var($_REQUEST, 'tasksGroupBy', user_config_option('tasksGroupBy'));

        if (array_var($_REQUEST, 'tasksOrderBy', false)) {
            set_user_config_option('tasksOrderBy', array_var($_REQUEST, 'tasksOrderBy'), logged_user()->getId());
        }

        $groups = $this->getGroups($groupBy, $conditions, $show_more_conditions, true, $only_totals, $groups_offset, $groups_count);
        if (is_null($groups)) {
            $groups = array();
        }
        $data['groups'] = $groups;
        $data['new_groups_offset'] = $groups_offset;
        ajx_extra_data($data);
    }

    private function getListingOrderBy(&$order_by, &$order_dir) {
        $order_by = user_config_option('tasksOrderBy');
        $order_dir = user_config_option('tasksListingOrder');
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
    }

    function get_tasks() {
        ajx_current("empty");
        $data = array();
        $tasks_array = array();

        $tasks_ids = array_map('intval', json_decode(array_var($_REQUEST, 'tasks_ids', null)));
        if (is_array($tasks_ids)) {
            $conditions = " AND e.`object_id` IN (" . implode(',', $tasks_ids) . ")";

            $this->getListingOrderBy($order_by, $order_dir);
            
            $join_params = null;
            $hook_order_result = null;
            Hook::fire("override_tasks_list_order_by", array('order' => $order_by, 'join_params' => $join_params), $hook_order_result);
            if (is_array($hook_order_result)) {
            	if ($hook_order_result['order']) {
            		$order_by = $hook_order_result['order'];
            		if ($hook_order_result['order_dir']) $order_dir = $hook_order_result['order_dir'];
            	}
            	if ($hook_order_result['join_params']) {
            		$join_params = $hook_order_result['join_params'];
            	}
            }

            $tasks = ProjectTasks::instance()->listing(array(
            			"select_columns" => array("e.*", "o.*"),
                        "extra_conditions" => $conditions,
                        "count_results" => false,
						"fire_additional_data_hook" => false,
                        "order" => $order_by,
                        "order_dir" => $order_dir,
            			"join_params" => $join_params,
                        "raw_data" => true,
                    ))->objects;

            $tasks_array = array();
            foreach ($tasks as $task) {
            	$tasks_array[] = ProjectTasks::getArrayInfo($task);
            }
        }

        $data['tasks'] = $tasks_array;
        ajx_extra_data($data);
    }

    function users_for_tasks_list_filter() {
        ajx_current("empty");
        // Get Users Info
        if (logged_user()->isGuest()) {
            $users = array(logged_user());
        } else {
            $users = allowed_users_to_assign(null, true, false, true);
        }

        $users_data = array();
        $user_ids = array(-1);
        foreach ($users as $user) {
            $user_ids[] = $user->getId();
            $users_data[] = $user->getArrayInfo();
        }

        // only companies with users
        $companies = Contacts::instance()->findAll(array(
                    "conditions" => "e.is_company = 1",
                    "join" => array(
                        "table" => Contacts::instance()->getTableName(),
                        "jt_field" => "object_id",
                        "j_sub_q" => "SELECT xx.object_id FROM " . Contacts::instance()->getTableName(true) . " xx WHERE
				xx.is_company=0 AND xx.company_id = e.object_id AND xx.object_id IN (" . implode(",", $user_ids) . ") LIMIT 1"
                    )
        ));

        $companies_data = array();
        foreach ($companies as $comp) {
            $companies_data[] = $comp->getArrayInfo();
        }

        ajx_extra_data(array('companies' => $companies_data, 'users' => $users_data));
    }

    function new_list_tasks() {
        //load config options into cache for better performance
        load_user_config_options_by_category_name('task panel');

        $isJson = array_var($_GET, 'isJson', false);
        if ($isJson)
            ajx_current("empty");

        $request_conditions = $this->get_tasks_request_conditions();
        $conditions = $request_conditions['conditions'];
        $filter_value = $request_conditions['filterValue'];
        $filter = $request_conditions['filter'];
        $status = $request_conditions['status'];

        $tasks = array();

        $pendingstr = $status == 0 ? " AND `e`.`completed_on` = " . DB::escape(EMPTY_DATETIME) . " " : "";
        $milestone_conditions = " AND `is_template` = false " . $pendingstr;

        //Find all internal milestones for these tasks
        $internalMilestones = ProjectMilestones::instance()->listing(array("extra_conditions" => $milestone_conditions))->objects;

        //Find all external milestones for these tasks, external milestones are the ones that belong to a parent member and have tasks in the current member
        $milestone_ids = array();
        $task_ids = array();
        if ($tasks) {
            foreach ($tasks as $task) {
                $task_ids[] = $task['id'];
                if ($task['milestone_id'] != 0) {
                    $milestone_ids[$task['milestone_id']] = $task['milestone_id'];
                }
            }

            // generate request cache
            ObjectMembers::instance()->getCachedObjectMembers(0, $task_ids);
            ProjectTasks::instance()->findByRelatedCached(0, $task_ids);
        }


        // custom properties options, check if there is a config option to show each cp, if not then create it
        $cps_definition = array();
        $cps = CustomProperties::getAllCustomPropertiesByObjectType(ProjectTasks::instance()->getObjectTypeId());
        foreach ($cps as $cp) {/* @var $cp CustomProperty */
            $cp_data = array(
                'id' => $cp->getId(),
                'code' => $cp->getCode(),
                'name' => clean($cp->getName()),
                'show_in_lists' => $cp->getShowInLists(),
            );

            $config_option_name = 'tasksShowCP_' . $cp->getId();
            $conf_opt = ContactConfigOptions::getByName($config_option_name);
            if (is_null($conf_opt)) {
                $conf_opt = new ContactConfigOption();
                $conf_opt->setName($config_option_name);
                $conf_opt->setCategoryName('task panel');
                $conf_opt->setIsSystem(true);
                $conf_opt->setDefaultValue($cp->getShowInLists() ? '1' : '0');
                $conf_opt->setConfigHandlerClass('BoolConfigHandler');
                $conf_opt->save();

                $cp_data['is_visible'] = $cp->getShowInLists();
            } else {
                $cp_data['is_visible'] = user_config_option($config_option_name);
            }

            if (!isset($cps_definition[$cp->getOrder()]))
                $cps_definition[$cp->getOrder()] = array();
            $cps_definition[$cp->getOrder()][] = $cp_data;
        }
        $tmp = array();
        foreach ($cps_definition as $order => $cp_defs) {
            $tmp = array_merge($tmp, $cp_defs);
        }
        $cps_definition = $tmp;

        tpl_assign('cps_definition', $cps_definition);

        $int_milestone_ids = array();
        foreach ($internalMilestones as $milestone) {
            $int_milestone_ids[] = $milestone->getId();
        }

        $milestone_ids = array_diff($milestone_ids, $int_milestone_ids);

        if (count($milestone_ids) == 0)
            $milestone_ids[] = 0;
        $ext_milestone_conditions = " `is_template` = false " . $pendingstr . ' AND `object_id` IN (' . implode(',', $milestone_ids) . ')';

        $externalMilestones = ProjectMilestones::instance()->findAll(array('conditions' => $ext_milestone_conditions));

        tpl_assign('tasks', $tasks);


        if (!$isJson) {

            $all_templates = COTemplates::instance()->findAll(array('conditions' => '`trashed_by_id` = 0 AND `archived_by_id` = 0'));

            tpl_assign('all_templates', $all_templates);

            if (user_config_option('task_display_limit') > 0 && count($tasks) > user_config_option('task_display_limit')) {
                tpl_assign('displayTooManyTasks', true);
                array_pop($tasks);
            }

            
            //These variables were not initialized before tp_assign.
            //But why are they passed? Are they used?
            $users='';
            $allUsers='';
            $companies='';
            
            tpl_assign('object_subtypes', array());
            tpl_assign('internalMilestones', $internalMilestones);
            tpl_assign('externalMilestones', $externalMilestones);
            tpl_assign('users', $users);
            tpl_assign('allUsers', $allUsers);
            tpl_assign('companies', $companies);

            $dateStart = '';
            $dateEnd = '';
            if (user_config_option('tasksUseDateFilters')) {
                if (strtotime(user_config_option('tasksDateStart'))) {//this return null if date is 0000-00-00 00:00:00
                    $dateStart = new DateTime('@' . strtotime(user_config_option('tasksDateStart')));
                    $dateStart = $dateStart->format(user_config_option('date_format'));
                }
                if (strtotime(user_config_option('tasksDateEnd'))) {//this return null if date is 0000-00-00 00:00:00
                    $dateEnd = new DateTime('@' . strtotime(user_config_option('tasksDateEnd')));
                    $dateEnd = $dateEnd->format(user_config_option('date_format'));
                }
            }

            $userPref = array();

            $showDimensionCols = explode(',', user_config_option('tasksShowDimensionCols'));

            $userPref = array(
                'filterValue' => isset($filter_value) ? $filter_value : '',
                'filter' => $filter,
                'dateStart' => $dateStart,
                'dateEnd' => $dateEnd,
                'status' => $status,
            //    'showTime' => user_config_option('tasksShowTime'),
                'showTimeQuick' => user_config_option('tasksShowTimeQuick'),
                'showDates' => user_config_option('tasksShowDates'),
                'showStartDates' => user_config_option('tasksShowStartDates'),
                'showEndDates' => user_config_option('tasksShowEndDates'),
                'showBy' => user_config_option('tasksShowAssignedBy'),
                'showClassification' => user_config_option('tasksShowClassification'),
                'showSubtasksStructure' => user_config_option('tasksShowSubtasksStructure'),
                'showTags' => user_config_option('tasksShowTags', 0),
                'showEmptyMilestones' => user_config_option('tasksShowEmptyMilestones', 1),
                'showTimeEstimates' => user_config_option('tasksShowTimeEstimates', 1),
                'showTotalTimeEstimates' => user_config_option('tasksShowTotalTimeEstimates', 1),
                'showTimePending' => user_config_option('tasksShowTimePending', 1),
                'showTimeWorked' => user_config_option('tasksShowTimeWorked', 1),
                'showTotalTimeWorked' => user_config_option('tasksShowTotalTimeWorked', 1),
                'showRemainingTime' => user_config_option('tasksShowRemainingTime', 0),
                'showTotalRemainingTime' => user_config_option('tasksShowTotalRemainingTime', 0),
                'showPercentCompletedBar' => user_config_option('tasksShowPercentCompletedBar', 1),
                'showQuickEdit' => user_config_option('tasksShowQuickEdit', 1),
                'showQuickComplete' => user_config_option('tasksShowQuickComplete', 1),
                'showQuickComment' => user_config_option('tasksShowQuickComment', 1),
                'showQuickAddSubTasks' => user_config_option('tasksShowQuickAddSubTasks', 1),
                'showQuickMarkAsStarted' => user_config_option('tasksShowQuickMarkAsStarted', 1),
                'showDimensionCols' => $showDimensionCols,
                'groupBy' => user_config_option('tasksGroupBy'),
                'orderBy' => user_config_option('tasksOrderBy'),
                'listingOrder' => user_config_option('tasksListingOrder'),
                'previousPendingTasks' => user_config_option('tasksPreviousPendingTasks', 1),
                'defaultNotifyValue' => user_config_option('can notify from quick add'),
                'groupsPaginationCount' => user_config_option('tasksGroupsPaginationCount'),
            );

                
            if (user_config_option('show_start_time_action')) {
                $userPref['showTime'] = user_config_option('tasksShowTime');
            }
            
            foreach ($cps as $cp) {/* @var $cp CustomProperty */
                $config_option_name = 'tasksShowCP_' . $cp->getId();
                $userPref[$config_option_name] = user_config_option($config_option_name);
            }

            hook::fire('tasks_user_preferences', null, $userPref);

            tpl_assign('userPreferences', $userPref);

            tpl_assign('userPermissions', array('can_add' => ProjectTask::canAdd(logged_user(), active_context()) ? 1 : 0));

            ajx_set_no_toolbar(true);
        }
    }

    /**
     * View task page
     *
     * @access public
     * @param void
     * @return null
     */
    function view() {
        if (array_var($_REQUEST, "template_task")) {
            $task_list = TemplateTasks::instance()->findById(get_id());
            if (!($task_list instanceof TemplateTask)) {
                flash_error(lang('task list dnx'));
                ajx_current("empty");
                return;
            } // if
            $this->setTemplate(get_template_path('view', 'template_task'));
        } else {
            $task_list = ProjectTasks::instance()->findById(get_id());

            $this->addHelper('textile');

            if (!($task_list instanceof ProjectTask)) {
                flash_error(lang('task list dnx'));
                ajx_current("empty");
                return;
            } // if

            if (!$task_list->canView(logged_user())) {
                flash_error(lang('no access permissions'));
                ajx_current("empty");
                return;
            } // if
        }

        $can_manage_repetitive_properties_of_tasks = SystemPermissions::userHasSystemPermission(logged_user(), 'can_manage_repetitive_properties_of_tasks');
        tpl_assign('can_manage_repetitive_properties_of_tasks', $can_manage_repetitive_properties_of_tasks);

        //read object for this user
        $task_list->setIsRead(logged_user()->getId(), true);
        
        $last_task_of_repetition = null;
        $last_related_task_id = ProjectTasks::getLastRepetitiveTaskId($task_list->getId());
        if ($last_related_task_id > 0) {
        	$last_task_of_repetition = ProjectTasks::instance()->findById($last_related_task_id);
        }

        tpl_assign('last_task_of_repetition', $last_task_of_repetition);
        tpl_assign('task_list', $task_list);

        $this->addHelper('textile');
        ajx_extra_data(array("title" => $task_list->getObjectName(), 'icon' => 'ico-task'));
        ajx_set_no_toolbar(true);

        ApplicationReadLogs::createLog($task_list, ApplicationReadLogs::ACTION_READ);
    }

// view

    function print_task() {
        $this->setLayout("html");
        $task = ProjectTasks::instance()->findById(get_id());

        if (!($task instanceof ProjectTask)) {
            flash_error(lang('task list dnx'));
            ajx_current("empty");
            return;
        } // if

        if (!$task->canView(logged_user())) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        } // if

        tpl_assign('task', $task);
        $this->setTemplate('print_task');
    }

// print_task

    function print_tasks_list() {
        $this->setLayout("html");

        $request_conditions = $this->get_tasks_request_conditions();
        $conditions = $request_conditions['conditions'];

        $groupId = array_var($_REQUEST, 'groupId', null);
        $start = 0;
        $limit = null;
        $show_more_conditions = array("groupId" => $groupId, "start" => $start, "limit" => $limit);

        //Groups
        $groupBy = array_var($_REQUEST, 'tasksGroupBy', user_config_option('tasksGroupBy'));

        if (array_var($_REQUEST, 'tasksOrderBy', false)) {
            set_user_config_option('tasksOrderBy', array_var($_REQUEST, 'tasksOrderBy'), logged_user()->getId());
        }
        
        $gr_offset = 0;
        $groups = $this->getGroups($groupBy, $conditions, $show_more_conditions, true, false, $gr_offset, 99999);
        
        if (is_null($groups)) {
            $groups = array();
        }

        // Get subtasks
        $subtasks = array();
        foreach ($groups as $group) {
            foreach ($group['group_tasks'] as $task) {
                if (count(array_var($task, 'subtasksIds')) > 0) {
                    $t = ProjectTasks::instance()->findById($task['id']);
                    $all_subtasks_info = $t->getAllSubtaskInfoInHierarchy($conditions);
                    $subtasks[$task['id']] = $all_subtasks_info;
                }
            }
        }

        // reorder tasks, put subtasks below the parent task
        if (count($subtasks) > 0) {
            foreach ($groups as &$group) {
                $old_tasks = $group['group_tasks'];
                $group['group_tasks'] = array();

                foreach ($old_tasks as $t) {
                    $group['group_tasks'][] = $t;

                    if (isset($subtasks[$t['id']])) {
                        foreach ($subtasks[$t['id']] as $subt) {
                            $group['group_tasks'][] = $subt;
                        }
                    }
                }
            }
        }
        // ----------------------

        $draw_options = json_decode(array_var($_REQUEST, 'draw_options'), true);

        $tasks_list_cols = json_decode(array_var($_REQUEST, 'tasks_list_cols'), true);

        $row_total_cols = json_decode(array_var($_REQUEST, 'row_total_cols'), true);

        tpl_assign('draw_options', $draw_options);
        tpl_assign('tasks_list_cols', $tasks_list_cols);
        tpl_assign('row_total_cols', $row_total_cols);
        tpl_assign('groups', $groups);
    }

    /**
     * Add new task
     *
     * @access public
     * @param void
     * @return null
     */
    function add_task() {
        //is template task?
        // Frontend sends template_task 1 and template_id, or 0 if it's a new template
        $isTemplateTask = false;
        if (array_var($_REQUEST, 'template_task') == true) {
            $isTemplateTask = true;
            if (array_var($_REQUEST, 'template_id')) {
                $template_id = array_var($_REQUEST, 'template_id');
            } else {
                $template_id = 0;
            }
            tpl_assign('template_id', $template_id);

            tpl_assign('additional_tt_params', array_var($_REQUEST, 'additional_tt_params'));
        }

        if (logged_user()->isGuest()) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        }

        $email = null;
        $context = active_context();
        $member_ids = json_decode(array_var($_POST, 'members', null));
        if (is_array($member_ids) && count($member_ids) > 0) {
            $context = Members::instance()->findAll(array('conditions' => 'id IN (' . implode(',', $member_ids) . ')'));
        }
        $context_member_count = 0;
        foreach ($context as $c) {
            if ($c instanceof Member)
                $context_member_count++;
        }


        $notAllowedMember = '';
        if ($context_member_count > 0 && !ProjectTask::canAdd(logged_user(), $context, $notAllowedMember) && !$isTemplateTask) {
            if (!str_starts_with($notAllowedMember, '-- req dim --')) {
                trim($notAllowedMember) == "" ? $msg = lang('you must select where to keep', lang('the task')) : $msg = lang('no context permissions to add', lang("tasks"), $notAllowedMember);
                flash_error($msg);
                ajx_current("empty");
                return;
            }
        }

        //is template task?
        if (array_var($_REQUEST, 'template_task') == true) {
            $task = new TemplateTask();
            $this->setTemplate(get_template_path('add_template_task', 'template_task'));
        } else {
            $task = new ProjectTask();
        }

        $task_data = array_var($_POST, 'task');
        if (is_array($task_data)) {
            if (str_starts_with($notAllowedMember, '-- req dim --')) {
                $msg = lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in));
                flash_error($msg);
                ajx_current("empty");
                return;
            }
            foreach ($task_data as $k => &$v) {
                $v = remove_scripts($v);
            }
        }

        if (!is_array($task_data)) {
            // set layout for modal form
            if (array_var($_REQUEST, 'modal')) {
                $this->setLayout("json");
                tpl_assign('modal', true);
            }

            $dd = getDateValue(array_var($_POST, 'task_due_date', ''));
            if ($dd instanceof DateTimeValue) {
                $duetime = getTimeValue(array_var($_POST, 'task_due_time'));
                if (is_array($duetime)) {
                    $dd->setHour(array_var($duetime, 'hours'));
                    $dd->setMinute(array_var($duetime, 'mins'));
                }
                $task->setUseDueTime(is_array($duetime));
            }
            $sd = getDateValue(array_var($_POST, 'task_start_date', ''));
            if ($sd instanceof DateTimeValue) {
                $starttime = getTimeValue(array_var($_POST, 'task_start_time'));
                if (is_array($starttime)) {
                    $sd->setHour(array_var($starttime, 'hours'));
                    $sd->setMinute(array_var($starttime, 'mins'));
                }
                $task->setUseStartTime(is_array($starttime));
            }

            $time_estimate = (array_var($_POST, 'hours', 0) * 60) + array_var($_POST, 'minutes', 0);
            if (config_option("wysiwyg_tasks")) {
                $text_post = preg_replace("/[\n|\r|\n\r]/", '', array_var($_POST, 'text', ''));
            } else {
                $text_post = array_var($_POST, 'text', '');
            }

            $task_data = array(
                'milestone_id' => array_var($_REQUEST, 'milestone_id', 0),
                'project_id' => 1,
                'name' => array_var($_REQUEST, 'name', ''),
                'assigned_to_contact_id' => array_var($_REQUEST, 'assigned_to_contact_id', '0'),
                'selected_members_ids' => json_decode(array_var($_POST, 'members', null)),
                'parent_id' => array_var($_REQUEST, 'parent_id', 0),
                'priority' => array_var($_POST, 'priority', ProjectTasks::PRIORITY_NORMAL),
                'text' => $text_post,
                'start_date' => $sd,
                'due_date' => $dd,
                'time_estimate' => $time_estimate,
                'is_template' => array_var($_POST, "is_template", array_var($_GET, "is_template", false)),
                'percent_completed' => array_var($_POST, "percent_completed", ''),
                'object_subtype' => array_var($_POST, "object_subtype", config_option('default task co type')),
                'send_notification_subscribers' => user_config_option("can notify subscribers"),
                'is_manual_percent_completed' => $task->getIsManualPercentCompleted(),
            ); // array
            //if is subtask copy parent dates and assigned
            if (array_var($_REQUEST, 'parent_id', 0)) {
                $parent_task = ProjectTasks::instance()->findById(array_var($_REQUEST, 'parent_id'));
                if ($parent_task instanceof ProjectTask) {
                    $dd = $parent_task->getDueDate() instanceof DateTimeValue ? $parent_task->getDueDate() : null;
                    if ($dd instanceof DateTimeValue && $parent_task->getUseDueTime()) {
                        $dd->advance($parent_task->getTimezoneValue());
                    }
                    $task->setUseDueTime($parent_task->getUseDueTime());

                    $sd = $parent_task->getStartDate() instanceof DateTimeValue ? $parent_task->getStartDate() : null;
                    if ($sd instanceof DateTimeValue && $parent_task->getUseStartTime()) {
                        $sd->advance($parent_task->getTimezoneValue());
                    }
                    $task->setUseStartTime($parent_task->getUseStartTime());

                    $task_data['start_date'] = $sd;
                    $task_data['due_date'] = $dd;

                    //copy assigned
                    $task_data['assigned_to_contact_id'] = $parent_task->getAssignedToContactId();

                    //copy milestone
                    $task_data['milestone_id'] = $parent_task->getMilestoneId();

                    //copy clasification
                    $parent_member_ids = $parent_task->getMemberIds();
                    Hook::fire('modify_subtasks_member_ids', array('task' => $task, 'parent' => $parent_task), $parent_member_ids);
                    $task_data['selected_members_ids'] = $parent_member_ids;
                }
            }

            if (Plugins::instance()->isActivePlugin('mail')) {
                $from_email = array_var($_GET, 'from_email');
                $email = MailContents::instance()->findById($from_email);
                if ($email instanceof MailContent) {
                    $task_data['name'] = $email->getSubject();
                    $task_data['text'] = lang('create task from email description', $email->getSubject(), $email->getFrom(), $email->getTextBody());
                    $task_data['selected_members_ids'] = $email->getMemberIds();
                    tpl_assign('from_email', $email);
                }
            }

            tpl_assign('additional_onsubmit', array_var($_REQUEST, 'additional_onsubmit'));
            $can_manage_repetitive_properties_of_tasks = SystemPermissions::userHasSystemPermission(logged_user(), 'can_manage_repetitive_properties_of_tasks');
            tpl_assign('can_manage_repetitive_properties_of_tasks', $can_manage_repetitive_properties_of_tasks);
        } // if

        if (array_var($_GET, 'replace')) {
            ajx_replace(true);
        }

        tpl_assign('task_data', $task_data);
        tpl_assign('task', $task);
        tpl_assign('pending_task_id', 0);

        $subtasks = array();
		$subtasks_data = array_var($_POST, 'multi_assignment');
		if (isset($subtasks_data)) {
			if (is_array($subtasks_data)) {
				$subtasks = $subtasks_data;
			} else if (is_string($subtasks_data)) {
				$subtasks = json_decode(array_var($_POST, 'multi_assignment'), true);
			}
		}
        tpl_assign('multi_assignment', $subtasks);

        if (is_array(array_var($_POST, 'task'))) {
            // Adds a new task via POST
            try {
                $estimated_price = array_var(array_var($_POST, 'task'),'estimated_price');
                $estimated_price = str_replace(',', '', $estimated_price);
                $task_data['estimated_price'] = $estimated_price;
                // order
                $task->setOrder(ProjectTasks::maxOrder(array_var($task_data, "parent_id", 0), array_var($task_data, "milestone_id", 0)));

                try {
                    $task_data['due_date'] = getDateValue(array_var($_POST, 'task_due_date'));
                    $task_data['start_date'] = getDateValue(array_var($_POST, 'task_start_date'));
                } catch (Exception $e) {
                    throw new Exception(lang('date format error', date_format_tip(user_config_option('date_format'))));
                }

                if ($task_data['due_date'] instanceof DateTimeValue) {
                    $duetime = getTimeValue(array_var($_POST, 'task_due_time'));
                    if (is_array($duetime)) {
                        $task_data['due_date']->setHour(array_var($duetime, 'hours'));
                        $task_data['due_date']->setMinute(array_var($duetime, 'mins'));
                        $task_data['due_date']->advance(logged_user()->getUserTimezoneValue() * -1);
                    }
                    $task_data['use_due_time'] = is_array($duetime);
                }
                if ($task_data['start_date'] instanceof DateTimeValue) {
                    $starttime = getTimeValue(array_var($_POST, 'task_start_time'));
                    if (is_array($starttime)) {
                        $task_data['start_date']->setHour(array_var($starttime, 'hours'));
                        $task_data['start_date']->setMinute(array_var($starttime, 'mins'));
                        $task_data['start_date']->advance(logged_user()->getUserTimezoneValue() * -1);
                    }
                    $task_data['use_start_time'] = is_array($starttime);
                }


                if ($task_data['start_date'] instanceof DateTimeValue && $task_data['due_date'] instanceof DateTimeValue) {
                    if ($task_data['start_date']->getTimestamp() > $task_data['due_date']->getTimestamp()) {
                        throw new Exception(lang('start date cannot be greater than due date'));
                    }
                }


                $task_data['is_template'] = $isTemplateTask;
                $err_msg = $this->setRepeatOptions($task_data);
                if ($err_msg) {
                    throw new Exception($err_msg);
                }

                if (config_option("wysiwyg_tasks")) {
                    $task_data['type_content'] = "html";
                    $task_data['text'] = str_replace(array("\r", "\n", "\r\n"), array('', '', ''), array_var($task_data, 'text'));
                } else {
                    $task_data['type_content'] = "text";
                }
                $task_data['object_type_id'] = $task->getObjectTypeId();
                $member_ids = json_decode(array_var($_POST, 'members'));

                $task->setFromAttributes($task_data);
                if (!can_task_assignee(logged_user())) {
                    flash_error(lang('no access permissions'));
                    ajx_current("empty");
                    return;
                }
                // check if user can assing task to other users
                if ($task->getAssignedToContactId() != logged_user()->getId() && !SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks')) {
                	//throw new Exception(lang('no access permissions'));
                	$task->setAssignedToContactId(logged_user()->getId());
                }
                
                $totalMinutes = ((int)array_var($task_data, 'time_estimate_hours', 0) * 60) + (int)(array_var($task_data, 'time_estimate_minutes', 0));
                $task->setTimeEstimate($totalMinutes);

                $id = array_var($_GET, 'id', 0);
                if ($task instanceof TemplateTask) {
                    //evt_add("template task added", array("id_template_task" => $file->getId()));

                    $parent = TemplateTasks::instance()->findById($id);
                    if ($parent instanceof TemplateTask) {
                        $task->setParentId($id);
                        $member_ids = $parent->getMemberIds();
                    }

                    //template id
                    $task->setTemplateId($template_id);
                } else {
                    $parent = ProjectTasks::instance()->findById($id);
                    if ($parent instanceof ProjectTask) {
                        $task->setParentId($id);
                        $member_ids = $parent->getMemberIds();
                        Hook::fire('modify_subtasks_member_ids', array('task' => $task, 'parent' => $parent), $member_ids);
                    }
                }

                if ($task->getParentId() > 0 && $task->hasChild($task->getParentId())) {
                    flash_error(lang('task child of child error'));
                    ajx_current("empty");
                    return;
                }

                if ($task instanceof TemplateTask) {
                    $task->setSessionId(logged_user()->getId());
                }

                Hook::fire('update_calculated_and_manual_data', $task_data, $task);

                DB::beginWork();
                $task->save();


                // dependencies
                if (config_option('use tasks dependencies')) {
                    $previous_tasks = array_var($task_data, 'previous');
                    if (is_array($previous_tasks)) {
                        foreach ($previous_tasks as $ptask) {
                            if ($ptask == $task->getId())
                                continue;
                            $dep = ProjectTaskDependencies::instance()->findById(array('previous_task_id' => $ptask, 'task_id' => $task->getId()));
                            if (!$dep instanceof ProjectTaskDependency) {
                                $dep = new ProjectTaskDependency();
                                $dep->setPreviousTaskId($ptask);
                                $dep->setTaskId($task->getId());
                                $dep->save();
                            }
                        }
                    }
                }


                if (array_var($_GET, 'copyId', 0) > 0) {
                    // copy remaining stuff from the task with id copyId
                    $toCopy = ProjectTasks::instance()->findById(array_var($_GET, 'copyId'));
                    if ($toCopy instanceof ProjectTask) {
                        ProjectTasks::copySubTasks($toCopy, $task, array_var($task_data, 'is_template', false));
                    }
                }

                // if task is added from task view -> add subscribers
                if (array_var($task_data, 'inputtype') == 'taskview') {
                    if (!isset($_POST['subscribers']))
                        $_POST['subscribers'] = array();
                    $_POST['subscribers']['user_' . logged_user()->getId()] = '1';
                    if ($task->getAssignedToContactId() > 0 && Contacts::instance()->findById($task->getAssignedToContactId())->getUserType()) {
                        $_POST['subscribers']['user_' . $task->getAssignedToContactId()] = '1';
                    }
                }

                // Add assigned user to the subscibers list
                if (isset($_POST['subscribers']) && $task->getAssignedToContactId() > 0 && Contacts::instance()->findById($task->getAssignedToContactId())) {
                    $_POST['subscribers']['user_' . $task->getAssignedToContactId()] = '1';
                }

                //Link objects
                $object_controller = new ObjectController();

                if (!is_null($member_ids)) {
                    if ($task instanceof TemplateTask) {
                        $object_controller->add_to_members($task, $member_ids, null, false);
                    } else {
                        $object_controller->add_to_members($task, $member_ids);
                    }
                }
                $notify_subscribers = user_config_option("can notify subscribers");
                $is_template = $task instanceof TemplateTask;
                $object_controller->add_subscribers($task, null, !$is_template, $notify_subscribers);
                $object_controller->link_to_new_object($task);
                $object_controller->add_custom_properties($task);

                $object_controller->add_reminders($task);


                if (config_option('multi_assignment') && Plugins::instance()->isActivePlugin('crpm')) {
                    $subtasks = array_var($_POST, 'multi_assignment');
                    Hook::fire('save_subtasks', array('task' => $task, 'is_new' => true), $subtasks);
                    Hook::fire('calculate_estimated_executed_financials', array(), $task);
                }

                if ($task instanceof ProjectTask) {
                    $generated_count = $this->repetitive_task($task, array());

                    // reload all tasks, to ensure that all new tasks are shown
                    if ($generated_count > 0) {
                    	$_REQUEST['reload'] = true;
                    }
                }

                //for calculate member status we save de task again after the object have the members
                $task->save();

                DB::commit();

                // save subtasks added in 'subtasks' tab
                DB::beginWork();
                $sub_member_ids = $member_ids;
                Hook::fire('modify_subtasks_member_ids', array('task' => $task, 'parent' => $parent), $sub_member_ids);
                $sub_tasks_to_log = $this->saveSubtasks($task, array_var($task_data, 'subtasks'), $sub_member_ids);
                DB::commit();

                foreach ($sub_tasks_to_log['add'] as $st_to_log) {
                    ApplicationLogs::createLog($st_to_log, ApplicationLogs::ACTION_ADD);
                }
                foreach ($sub_tasks_to_log['edit'] as $st_to_log) {
                    ApplicationLogs::createLog($st_to_log, ApplicationLogs::ACTION_EDIT);
                }
                foreach ($sub_tasks_to_log['trash'] as $st_to_log) {
                    ApplicationLogs::createLog($st_to_log, ApplicationLogs::ACTION_TRASH);
                }


                //Send Template task to view
                if ($task instanceof TemplateTask) {
                    $objectId = $task->getObjectId();
                    $id = $task->getId();
                    $objectTypeName = $task->getObjectTypeName();
                    $objectName = $task->getObjectName();
                    $manager = get_class($task->manager());
                    $milestoneId = $task instanceof TemplateTask ? $task->getMilestoneId() : '0';
                    $subTasks = array();
                    $parentId = $task->getParentId();
                    $ico = "ico-task";
                    $action = "add";
					$template_controller = new TemplateController();
                    $object = $template_controller->prepareObject($objectId, $id, $objectName, $objectTypeName, $manager, $action, $milestoneId, $subTasks, $parentId, $ico, $task->getObjectTypeId(), $task->isRepetitive());

                    $template_task_data = array('object' => $object);

                    if (array_var($_REQUEST, 'additional_tt_params')) {
                        $additional_tt_params = json_decode(str_replace("'", '"', array_var($_REQUEST, 'additional_tt_params')), true);
                        foreach ($additional_tt_params as $k => $v)
                            $template_task_data[$k] = $v;
                    }

                    if (!array_var($_REQUEST, 'modal')) {
                        evt_add("template object added", $template_task_data);
                    }
                }

                // notify asignee
                $exclude_from_notification = array();
                $exclude_from_notification[$task->getAssignedToContactId()] = $task->getAssignedToContactId();
                if (array_var($task_data, 'send_notification')) {
                    if (($task instanceof ProjectTask) && ($task->getAssignedToContactId() != $task->getAssignedById())) {
                        try {
                            Notifier::taskAssigned($task);
                        } catch (Exception $e) {
                            evt_add("debug", $e->getMessage());
                        } // try
                    }

                    // notify asignee for subtasks
                    foreach ($sub_tasks_to_log['assigned'] as $st_to_log) {
                        if ($st_to_log instanceof ProjectTask && $st_to_log->getAssignedToContactId() != $st_to_log->getAssignedById()) {
                            try {
                                $exclude_from_notification[$st_to_log->getAssignedToContactId()] = $st_to_log->getAssignedToContactId();
                                Notifier::taskAssigned($st_to_log);
                            } catch (Exception $e) {
                                evt_add("debug", $e->getMessage());
                            } // try
                        }
                    }
                }

                //notify subscribers
                $isSilent = true;
                if ((array_var($task_data, 'send_notification_subscribers'))) {
                    $isSilent = false;
                }
                
                ApplicationLogs::createLog($task, ApplicationLogs::ACTION_ADD, null, $isSilent, true, null, $exclude_from_notification);

                if (array_var($_REQUEST, 'modal')) {

                    ajx_current("empty");
                    $this->setLayout("json");
                    $this->setTemplate(get_template_path("empty"));

                    // reload task info because plugins may have updated some task info (for example: name prefix)
                    if ($is_template) {
                        $task = TemplateTasks::instance()->findById($task->getId());
                    } else {
                        $task = ProjectTasks::instance()->findById($task->getId());
                    }

                    $params = array('msg' => lang('success add task list', $task->getObjectName()), 'task' => $task->getArrayInfo(), 'reload' => array_var($_REQUEST, 'reload'));
                    if ($task instanceof TemplateTask) {
                        $params['msg'] = lang('success add template', $task->getObjectName());
                        $params['object'] = $template_task_data['object'];
                    }

                    if (array_var($_REQUEST, 'reload')) {
                        evt_add("reload current panel");
                    }

                    print_modal_json_response($params, true, array_var($_REQUEST, 'use_ajx'));
                } else {
                    if ($task instanceof TemplateTask) {
                        flash_success(lang('success add template', $task->getObjectName()));
                    } else {
                        flash_success(lang('success add task list', $task->getObjectName()));
                    }
                    if (array_var($task_data, 'inputtype') != 'taskview') {
                        ajx_current("back");
                    } else {
                        ajx_current("reload");
                    }
                }

                $null = null;
                Hook::fire('after_task_controller_add_task', array('task' => $task), $null);
                
                return $task;
                
            } catch (Exception $e) {
                DB::rollback();
                if (array_var($_REQUEST, 'modal')) {
                    $this->setLayout("json");
                    $this->setTemplate(get_template_path("empty"));
                    print_modal_json_response(array('errorCode' => 1, 'errorMessage' => $e->getMessage(), 'showMessage' => 1), true, array_var($_REQUEST, 'use_ajx'));
                } else {
                    flash_error($e->getMessage());
                }
                ajx_current("empty");
            } // try
        } // if
    }

// add_task

    /**
     * Save subtasks added in task form, at 'subtasks' tab
     * @param $parent_task: ProjectTask - The parent task to set in the subtasks to save
     * @param $subtasks_data: array - An array with all the subtasks data
     * @param $member_ids: array with the member ids to classify the subtasks
     * @return array with the application logs to generate
     */
    private function saveSubtasks($parent_task, $subtasks_data, $member_ids) {
        $to_log = array('add' => array(), 'edit' => array(), 'trash' => array(), 'assigned' => array());
        $subs = $parent_task->getSubscriberIds();
        $subs_array = array();
        foreach ($subs as $sid)
            $subs_array['user_' . $sid] = 1;

        if ($parent_task instanceof ProjectTask && is_array($subtasks_data)) {
            foreach ($subtasks_data as $stdata) {
                $st = null;
                if ($stdata['id'] > 0) {
                    $st = ProjectTasks::instance()->findById($stdata['id']);
                    // subtask has been deleted, delete object and continue with next subtask
                    if ($stdata['deleted'] == 1 && $st instanceof ProjectTask) {
                        /* $st->trash(false);
                          $st->save(); */
                        DB::execute("UPDATE " . TABLE_PREFIX . "objects SET trashed_by_id=" . logged_user()->getId() . ", trashed_on=NOW() WHERE id=" . $st->getId());
                        $to_log['trash'][] = $st;
                        continue;
                    }
                }

                $new_subtask = false;
                // new subtask
                if (!$st instanceof ProjectTask) {
                    $st = new ProjectTask();
                    $new_subtask = true;
                }

                if (trim($stdata['name'] == ''))
                    continue;

                $changed = false;
                if ($st->getObjectName() != $stdata['name'] || $st->getAssignedToContactId() != $stdata['assigned_to']) {
                    $changed = true;
                }

                if ($new_subtask || $changed) {

                    if ($stdata['assigned_to'] > 0 && $stdata['assigned_to'] != $st->getAssignedToContactId()) {
                        $to_log['assigned'][] = $st;
                    }

                    if ($new_subtask) {
                        //if is new subtask copy parent dates
                        $dd = $parent_task->getDueDate();
                        if ($dd instanceof DateTimeValue) {
                            $st->setDueDate($dd);
                        }
                        $st->setUseDueTime($parent_task->getUseDueTime());

                        $sd = $parent_task->getStartDate();
                        if ($sd instanceof DateTimeValue) {
                            $st->setStartDate($sd);
                        }
                        $st->setUseStartTime($parent_task->getUseStartTime());

                        //copy milestone
                        $st->setMilestoneId($parent_task->getMilestoneId());
                    }

                    $st->setParentId($parent_task->getId());
                    $st->setObjectName($stdata['name']);
                    $st->setAssignedToContactId($stdata['assigned_to']);
                    $st->setPriority(array_var($stdata, 'priority', ProjectTasks::PRIORITY_NORMAL));
                    $st->setTypeContent(config_option("wysiwyg_tasks") ? 'html' : 'text');
                    $st->save();

                    $object_controller = new ObjectController();
                    $object_controller->add_to_members($st, $member_ids);

                    $st_subs_array = $subs_array;
                    if ($stdata['assigned_to'] > 0 && !in_array($stdata['assigned_to'], $subs)) {
                        $st_subs_array['user_' . $stdata['assigned_to']] = 1;
                    }
                    $notify_subscribers = user_config_option("can notify subscribers");
                    $object_controller->add_subscribers($st, $st_subs_array, true, $notify_subscribers);

                    if ($new_subtask)
                        $to_log['add'][] = $st;
                    else
                        $to_log['edit'][] = $st;
                }
            }
        }

        return $to_log;
    }

    /**
     * Copy task
     *
     * @access public
     * @param void
     * @return null
     */
    function copy_task() {
        if (logged_user()->isGuest()) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        }

        $notAllowedMember = '';
        if (!ProjectTask::canAdd(logged_user(), active_context(), $notAllowedMember)) {
            if (str_starts_with($notAllowedMember, '-- req dim --'))
                flash_error(lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in)));
            else
                trim($notAllowedMember) == "" ? flash_error(lang('you must select where to keep', lang('the task'))) : flash_error(lang('no context permissions to add', lang("tasks"), $notAllowedMember));
            ajx_current("empty");
            return;
        } // if

        $id = get_id();
        $task = ProjectTasks::instance()->findById($id);
        if (!$task instanceof ProjectTask) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        }
        $title = $task instanceof TemplateTask ? $task->getObjectName() : lang("copy of", $task->getObjectName());
        $dd = $task->getDueDate() instanceof DateTimeValue ? $task->getDueDate()->advance($task->getTimezoneValue(), false) : null;
        $sd = $task->getStartDate() instanceof DateTimeValue ? $task->getStartDate()->advance($task->getTimezoneValue(), false) : null;

        /*$subtasks = ProjectTasks::instance()->findAll(array('conditions' => "parent_id=".$task->getId()." AND trashed_by_id=0"));
        foreach ($subtasks as &$st) $st->setId(0);*/
        
        $task_data = array(
            'milestone_id' => $task->getMilestoneId(),
            'title' => $title,
            'name' => $title, //Alias for title
            'due_date' => getDateValue($dd),
            'start_date' => getDateValue($sd),
            'assigned_to_contact_id' => $task->getAssignedToContactId(),
            'parent_id' => $task->getParentId(),
            'priority' => $task->getPriority(),
            'time_estimate' => $task->getTimeEstimate(),
            'text' => $task->getText(),
            'copyId' => $task->getId(),
            'percent_completed' => $task->getPercentCompleted(),
            'is_manual_percent_completed' => $task->getIsManualPercentCompleted(),
        	'selected_members_ids' => $task->getMemberIds(),
        	//'subtasks' => $subtasks,
        ); // array
        $newtask = new ProjectTask();
        if ($task->getUseStartTime()) {
            $newtask->setUseStartTime($task->getUseStartTime());
        }
        if ($task->getUseDueTime()) {
            $newtask->setUseDueTime($task->getUseDueTime());
        }
        Hook::fire('task_clone_more_attributes', array('original' => $task, 'copy' => $newtask), $null);
        tpl_assign('task_data', $task_data);
        tpl_assign('task', $newtask);
        tpl_assign('base_task', $task);
        tpl_assign('pending_task_id', 0);
        tpl_assign('multi_assignment', array());
        tpl_assign('req_channel', array_var($_REQUEST, 'req_channel'));
        $this->setTemplate("add_task");
    }

// copy_task

    /**
     * Edit task
     *
     * @access public
     * @param void
     * @return null
     */
    function edit_task() {
        $isTemplateTask = false;
        if (array_var($_REQUEST, 'template_task') == true) {
            $isTemplateTask = true;
        }
        if (logged_user()->isGuest()) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        }
        $this->setTemplate('add_task');

        if (array_var($_REQUEST, "template_task")) {
            $task = TemplateTasks::instance()->findById(array_var($_REQUEST, "template_task_id", get_id()));
            $this->setTemplate(get_template_path('add_template_task', 'template_task'));
            if (array_var($_REQUEST, 'template_id')) {
                $template_id = array_var($_REQUEST, 'template_id');
            } else {
                $template_id = $task->getTemplateId();
            }

            tpl_assign('additional_tt_params', array_var($_REQUEST, 'additional_tt_params'));

            tpl_assign('template_id', $template_id);
            if (!($task instanceof TemplateTask)) {
                flash_error(lang('task list dnx'));
                ajx_current("empty");
                return;
            } // if
        } else {
            $task = ProjectTasks::instance()->findById(get_id());
            if (!($task instanceof ProjectTask)) {
                flash_error(lang('task list dnx'));
                ajx_current("empty");
                return;
            } // if

            if (!$task->canEdit(logged_user())) {
                flash_error(lang('no access permissions'));
                ajx_current("empty");
                return;
            } // if		
        }

        //save original time data from task to calculate time diference and apply to related tasks if is necessary
        $previous_start_date = $task->getStartDate();
        $previous_due_date = $task->getDueDate();

        $can_manage_repetitive_properties_of_tasks = SystemPermissions::userHasSystemPermission(logged_user(), 'can_manage_repetitive_properties_of_tasks');

        if ($can_manage_repetitive_properties_of_tasks) {
            $last_related_task_id = ProjectTasks::getLastRepetitiveTaskId($task->getId());

            if ($last_related_task_id > 0 && $last_related_task_id != $task->getId()) {
                $last_repetitive_task = ProjectTasks::instance()->findById($last_related_task_id);
            }
        }

        if (array_var($_GET, 'replace')) {
            ajx_replace(true);
        }

        $task_data = array_var($_POST, 'task');
        $time_estimate = ((int)array_var($_POST, 'hours', 0) * 60) + (int)array_var($_POST, 'minutes', 0);
        if ($time_estimate > 0) {
            $estimatedTime = $time_estimate;
        } else {
            $estimatedTime = $task->getTimeEstimate();
        }
        if (!is_array($task_data)) {
            // set layout for modal form
            if (array_var($_REQUEST, 'modal')) {
                $this->setLayout("json");
                tpl_assign('modal', true);
            }

            if (isset($last_repetitive_task) && $last_repetitive_task instanceof ProjectTask) {
                $this->getRepeatOptions($last_repetitive_task, $occ, $rsel1, $rsel2, $rsel3, $rnum, $rend, $rjump);
            } else {
                $this->getRepeatOptions($task, $occ, $rsel1, $rsel2, $rsel3, $rnum, $rend, $rjump);
            }

            $dd = $task->getDueDate() instanceof DateTimeValue ? $task->getDueDate() : null;
            if ($dd instanceof DateTimeValue && $task->getUseDueTime()) {
                $dd->advance($task->getTimezoneValue());
            }
            $sd = $task->getStartDate() instanceof DateTimeValue ? $task->getStartDate() : null;
            if ($sd instanceof DateTimeValue && $task->getUseStartTime()) {
                $sd->advance($task->getTimezoneValue());
            }

            $post_dd = null;
            if (array_var($_POST, 'task_due_date')) {
                $post_dd = getDateValue(array_var($_POST, 'task_due_date'));
                if ($post_dd instanceof DateTimeValue) {
                    $duetime = getTimeValue(array_var($_POST, 'task_due_time'));
                    if (is_array($duetime)) {
                        $post_dd->setHour(array_var($duetime, 'hours'));
                        $post_dd->setMinute(array_var($duetime, 'mins'));
                        $post_dd->advance($task->getTimezoneValue());
                    }
                }
            }

            $post_st = null;
            if (array_var($_POST, 'task_start_date')) {
                $post_st = getDateValue(array_var($_POST, 'task_start_date'));
                if ($post_st instanceof DateTimeValue) {
                    $starttime = getTimeValue(array_var($_POST, 'task_start_time'));
                    if (is_array($starttime)) {
                        $post_st->setHour(array_var($starttime, 'hours'));
                        $post_st->setMinute(array_var($starttime, 'mins'));
                        $post_st->advance($task->getTimezoneValue());
                    }
                }
            }
            if (config_option("wysiwyg_tasks")) {
                $text_post = preg_replace("/[\n|\r|\n\r]/", '', array_var($_POST, 'text', $task->getText()));
            } else {
                $text_post = array_var($_POST, 'text', $task->getText());
            }

            $count_timeslots = count(Timeslots::getTimeslotsByObject($task));

            $task_data = array(
                'name' => array_var($_POST, 'name', $task->getObjectName()),
                'text' => $text_post,
                'milestone_id' => array_var($_POST, 'milestone_id', $task->getMilestoneId()),
                'due_date' => getDateValue($post_dd, $dd),
                'start_date' => getDateValue($post_st, $sd),
                'parent_id' => $task->getParentId(),
                'assigned_to_contact_id' => array_var($_POST, 'assigned_to_contact_id', $task->getAssignedToContactId()),
                'selected_members_ids' => json_decode(array_var($_POST, 'members', null)),
                'priority' => array_var($_POST, 'priority', $task->getPriority()),
                'time_estimate' => $estimatedTime,
                'percent_completed' => $task->getPercentCompleted(),
                'count_timeslots' => $count_timeslots,
                'forever' => $task->getRepeatForever(),
                'rend' => $rend,
                'rnum' => $rnum,
                'rjump' => $rjump,
                'rsel1' => $rsel1,
                'rsel2' => $rsel2,
                'rsel3' => $rsel3,
                'occ' => $occ,
                'repeat_by' => $task->getRepeatBy(),
                'object_subtype' => array_var($_POST, "object_subtype", ($task->getObjectSubtype() != 0 ? $task->getObjectSubtype() : config_option('default task co type'))),
                'type_content' => $task->getTypeContent(),
                'multi_assignment' => $task->getColumnValue('multi_assignment', 0),
                'send_notification_subscribers' => user_config_option("can notify subscribers"),
                'apply_milestone_subtasks' => config_option('apply_milestone_subtasks'),
                'is_manual_percent_completed' => $task->getIsManualPercentCompleted(),
            ); // array
            //control dates of parent and subtasks
            $task_data ['type_control'] = "";
            $parent_data = $task->getParent();
            if ($parent_data) {
                $task_data ['type_control'] = "child";
                $task_data ['control_title'] = $parent_data->getObjectName();
                $task_data ['control_due_date'] = $parent_data->getDueDate() instanceof DateTimeValue ? $parent_data->getDueDate()->getTimestamp() + $parent_data->getTimezoneValue() : null;
                $task_data ['control_start_date'] = $parent_data->getStartDate() instanceof DateTimeValue ? $parent_data->getStartDate()->getTimestamp() + $parent_data->getTimezoneValue() : null;
            }
            $subtask_data = $task->getAllSubTasks();
            if ($subtask_data) {
                $task_data ['type_control'] = "father";
                $task_data ['control_title'] = $task->getObjectName();
                $task_data ['control_due_date'] = getDateValue($post_dd, $dd) instanceof DateTimeValue ? getDateValue($post_dd, $dd)->getTimestamp() : null;
                $task_data ['control_start_date'] = getDateValue($post_st, $sd)instanceof DateTimeValue ? getDateValue($post_st, $sd)->getTimestamp() : null;
            }
        } // if
        //I find all those related to the task to find out if the original
        $task_related = ProjectTasks::findByRelated($task->getObjectId());
        if (!$task_related) {
            //is not the original as the original look plus other related
            if ($task->getOriginalTaskId() != "0") {
                $task_related = ProjectTasks::findByTaskAndRelated($task->getObjectId(), $task->getOriginalTaskId());
            }
        }
        if ($task_related) {
            $pending_id = 0;
            foreach ($task_related as $t_rel) {
                if ($task->getStartDate() <= $t_rel->getStartDate() && $task->getDueDate() <= $t_rel->getDueDate() && !$t_rel->isCompleted()) {
                    $pending_id = $t_rel->getId();
                    break;
                }
            }
            tpl_assign('pending_task_id', $pending_id);
            tpl_assign('task_related', true);
        } else {
            tpl_assign('pending_task_id', 0);
            tpl_assign('task_related', false);
        }
        tpl_assign('task', $task);
        tpl_assign('task_data', $task_data);

        tpl_assign('can_manage_repetitive_properties_of_tasks', $can_manage_repetitive_properties_of_tasks);


        if (is_array(array_var($_POST, 'task'))) {
            $estimated_price = array_var(array_var($_POST, 'task'),'estimated_price');
            $estimated_price = str_replace(',', '', $estimated_price);
            $task_data['estimated_price'] = $estimated_price;
            foreach ($task_data as $k => &$v) {
                $v = remove_scripts($v);
            }
            $send_edit = false;
            if ($task->getAssignedToContactId() == array_var($task_data, 'assigned_to_contact_id')) {
                $send_edit = true;
            }
            $contact_ot_id = ObjectTypes::findByName('person')->getId();
            $previous_member_ids = $task->getMemberIds(array($contact_ot_id));

            $old_owner = $task->getAssignedTo();
            if (array_var($task_data, 'parent_id') == $task->getId()) {
                flash_error(lang("task own parent error"));
                ajx_current("empty");
                return;
            }

            // timezone offset to apply in dates
            $zone_offset = $task->getTimezoneValue();
            if (array_var($_REQUEST, 'timezone_edited') && array_var($_REQUEST, 'timezone_id')) {
                $zone_id = array_var($_REQUEST, 'timezone_id');
                $zone_offset = Timezones::getTimezoneOffset($zone_id);
                $task_data['timezone_id'] = $zone_id;
                $task_data['timezone_value'] = $zone_offset;
            }

            try {
				$old_content_object = $task->generateOldContentObjectData();
            	
                try {
                    $task_data['due_date'] = getDateValue(array_var($_POST, 'task_due_date'));
                    $task_data['start_date'] = getDateValue(array_var($_POST, 'task_start_date'));
                } catch (Exception $e) {
                    throw new Exception(lang('date format error', date_format_tip(user_config_option('date_format'))));
                }

                if ($task_data['due_date'] instanceof DateTimeValue) {
                    $duetime = getTimeValue(array_var($_POST, 'task_due_time'));
                    if (is_array($duetime)) {
                        $task_data['due_date']->setHour(array_var($duetime, 'hours'));
                        $task_data['due_date']->setMinute(array_var($duetime, 'mins'));
                        $task_data['due_date']->advance($zone_offset * -1);
                    }
                    $task_data['use_due_time'] = is_array($duetime);
                }
                if ($task_data['start_date'] instanceof DateTimeValue) {
                    $starttime = getTimeValue(array_var($_POST, 'task_start_time'));
                    if (is_array($starttime)) {
                        $task_data['start_date']->setHour(array_var($starttime, 'hours'));
                        $task_data['start_date']->setMinute(array_var($starttime, 'mins'));
                        $task_data['start_date']->advance($zone_offset * -1);
                    }
                    $task_data['use_start_time'] = is_array($starttime);
                }

                if ($task_data['start_date'] instanceof DateTimeValue && $task_data['due_date'] instanceof DateTimeValue) {
                    if ($task_data['start_date']->getTimestamp() > $task_data['due_date']->getTimestamp()) {
                        throw new Exception(lang('start date cannot be greater than due date'));
                    }
                }

                //control date subtask whit parent
                if (array_var($_POST, 'control_dates') == "child") {
                    $parent = $task->getParent();
                    if ($parent->getStartDate() instanceof DateTimeValue && $task_data['start_date'] instanceof DateTimeValue) {
                        if ($task_data['start_date']->getTimestamp() < $parent->getStartDate()->getTimestamp()) {
                            $parent->setStartDate($task_data['start_date']);
                            $parent->setUseStartTime($task_data['use_start_time']);
                        }
                    } else {
                        $parent->setStartDate($task_data['start_date']);
                        $parent->setUseStartTime(array_var($task_data, 'use_start_time', 0));
                    }
                    if ($parent->getDueDate() instanceof DateTimeValue && $task_data['due_date'] instanceof DateTimeValue) {
                        if ($task_data['due_date']->getTimestamp() > $parent->getDueDate()->getTimestamp()) {
                            $parent->setDueDate($task_data['due_date']);
                            $parent->setUseDueTime($task_data['use_due_time']);
                        }
                    } else {
                        $parent->setDueDate($task_data['due_date']);
                        $parent->setUseDueTime(array_var($task_data, 'use_due_time', 0));
                    }
                    // calculate and set estimated time
                    $totalMinutes = ((int)array_var($task_data, 'time_estimate_hours') * 60) + (int)(array_var($task_data, 'time_estimate_minutes'));
                    $parent->setTimeEstimate($totalMinutes);
                    $parent->save();
                }
                $task_data['is_template'] = $isTemplateTask;
                $err_msg = $this->setRepeatOptions($task_data);
                if ($err_msg) {
                    throw new Exception($err_msg);
                }

                if (!isset($task_data['parent_id'])) {
                    $task_data['parent_id'] = 0;
                }

                $member_ids = json_decode(array_var($_POST, 'members'));

                // keep old dates to check for subtasks
                $old_start_date = $task->getStartDate();
                $old_due_date = $task->getDueDate();

                // Save previous parent task to recalculate total values and percent complete
                $recalculate_old_parent = false;
                if($task->getParentId() > 0 && $task->getParentId() != $task_data['parent_id']){
                    $old_parent = $task->getParent();
                    if ($old_parent instanceof ProjectTask) {
                        $recalculate_old_parent = true;
                    }
                }

                if (config_option("wysiwyg_tasks")) {
                    $task_data['type_content'] = "html";
                    $task_data['text'] = str_replace(array("\r", "\n", "\r\n"), array('', '', ''), array_var($task_data, 'text'));
                } else {
                    $task_data['type_content'] = "text";
                }
                $task->setFromAttributes($task_data);


                $totalMinutes = ((int)array_var($task_data, 'time_estimate_hours') * 60) + (int)(array_var($task_data, 'time_estimate_minutes'));
                $task->setTimeEstimate($totalMinutes);

                if ($task->getParentId() > 0 && $task->hasChild($task->getParentId())) {
                    flash_error(lang('task child of child error'));
                    ajx_current("empty");
                    return;
                }

                if (isset($task_data['percent_completed']) && $task_data['percent_completed'] >= 0 && $task_data['percent_completed'] <= 100) {
                    $task->setPercentCompleted($task_data['percent_completed']);
                }
                Hook::fire('update_calculated_and_manual_data', $task_data, $task);

                DB::beginWork();
                
                if (!$task->isCompleted() && $can_manage_repetitive_properties_of_tasks) {
                	if (isset($last_repetitive_task) && $last_repetitive_task instanceof ProjectTask) {
                		// current task is not the last of the repetition
                		$this->updateLastTaskRepetitive($last_repetitive_task, $task);
	                    $this->resetRepeatProperties($task);
                	} else if ($last_related_task_id > 0 && !$last_repetitive_task instanceof ProjectTask) {
                		// current task is the last of the repetition
                		// get the unmodified task from the database
                	/*	$old_original_task = ProjectTasks::instance()->findById($task->getId(), true);
                		// generate the next repetition to keep the "template" of the rep.
                		$last_repetitive_task = $this->generate_new_repetitive_instance($old_original_task);
                		// clear current task's repetition options
                		if ($last_repetitive_task instanceof ProjectTask) {
	                		$task->clearRepeatOptions();
                		}
                		// reload all tasks, to ensure that all new tasks are shown
                		$_REQUEST['reload'] = true;*/
                	}
                }

                $task->save(); 
                $task->calculatePercentComplete();

                if ($recalculate_old_parent) {
                    $old_parent->save();
                    $old_parent->calculatePercentComplete();
                }

                // dependencies
                if (config_option('use tasks dependencies')) {
                    $previous_tasks = array_var($task_data, 'previous');
                    if (is_array($previous_tasks)) {
                        foreach ($previous_tasks as $ptask) {
                            if ($ptask == $task->getId())
                                continue;
                            $dep = ProjectTaskDependencies::instance()->findById(array('previous_task_id' => $ptask, 'task_id' => $task->getId()));
                            if (!$dep instanceof ProjectTaskDependency) {
                                $dep = new ProjectTaskDependency();
                                $dep->setPreviousTaskId($ptask);
                                $dep->setTaskId($task->getId());
                                $dep->save();
                            }
                        }

                        $saved_ptasks = ProjectTaskDependencies::instance()->findAll(array('conditions' => 'task_id = ' . $task->getId()));
                        foreach ($saved_ptasks as $pdep) {
                            if (!in_array($pdep->getPreviousTaskId(), $previous_tasks))
                                $pdep->delete();
                        }
                    } else {
                        ProjectTaskDependencies::instance()->delete('task_id = ' . $task->getId());
                    }
                }

                // Add assigned user to the subscibers list
                if ($task->getAssignedToContactId() > 0 && Contacts::instance()->findById($task->getAssignedToContactId())) {
                    if (!isset($_POST['subscribers']))
                        $_POST['subscribers'] = array();
                    $_POST['subscribers']['user_' . $task->getAssignedToContactId()] = '1';
                }

                $object_controller = new ObjectController();
                if (!is_null($member_ids)) {
                    if ($isTemplateTask) {
                        $object_controller->add_to_members($task, $member_ids, null, false);
                    } else {
                        $object_controller->add_to_members($task, $member_ids);
                    }
                }
                $is_template = $task instanceof TemplateTask;
                $notify_subscribers = user_config_option("can notify subscribers");
                
                $object_controller->add_subscribers($task, null, !$is_template, $notify_subscribers);
                $object_controller->link_to_new_object($task);
                $object_controller->add_custom_properties($task);

                if (!$task->isCompleted()) { //to make sure the task it is not completed yet, and that it has subscribed people
                    $old_reminders = ObjectReminders::getByObject($task);

                    $object_controller->add_reminders($task); //adding the new reminders, if any
                    $object_controller->update_reminders($task, $old_reminders); //updating the old ones

                    if (logged_user() instanceof Contact &&
                            (!is_array($old_reminders) || count($old_reminders) == 0) &&
                            (user_config_option("add_task_autoreminder") &&
                            logged_user()->getId() != $task->getAssignedToContactId() || //if the user is going to set up reminders for tasks assigned to its colleagues
                            user_config_option("add_self_task_autoreminder") && 
                            logged_user()->getId() == $task->getAssignedToContactId() //if the user is going to set up reminders for his own tasks
                            )
                        ) { //if there is no asignee, but it still has subscribers
                        $reminder = new ObjectReminder();
                        $def = explode(",", user_config_option("reminders_tasks"));
                        $minutes = $def[2] * $def[1];
                        $reminder->setMinutesBefore($minutes);
                        $reminder->setType($def[0]);
                        $reminder->setContext("due_date");
                        $reminder->setObject($task);
                        $reminder->setUserId(0);
                        $date = $task->getDueDate();
                        if ($date instanceof DateTimeValue) {
                            $rdate = new DateTimeValue($date->getTimestamp() - $minutes * 60);
                            $reminder->setDate($rdate);
                        }
                        $reminder->save();
                    }
                }
              
                // copy members to subtask only for tasks, not template tasks
                if ($task instanceof ProjectTask) {
                    if (!is_array($member_ids) || count($member_ids) == 0)
                        $member_ids = array(0);
                    
                    Hook::fire('modify_subtasks_member_ids', array('task' => $task, 'parent' => isset($parent) ? $parent : null), $member_ids);
                    $members = Members::instance()->findAll(array('conditions' => "id IN (" . implode(',', $member_ids) . ")"));
                    
                    if($previous_member_ids != $member_ids){ 
						// apply the classification changes to all the subtasks
                        $task->apply_members_to_subtasks($members, true);

						// apply the classification changes to related time entries and expenses
						$task->override_related_objects_classification();
                    }
                } 

                // apply values to subtasks
                $assigned_to = $task->getAssignedToContactId();
                $subtasks = $task->getAllSubTasks();
                $milestone_id = $task->getMilestoneId();
                $apply_ms = array_var($task_data, 'apply_milestone_subtasks');
                $apply_at = array_var($task_data, 'apply_assignee_subtasks', '');
                foreach ($subtasks as $sub) {
                    $modified = false;
                    //if ($apply_at || !($sub->getAssignedToContactId() > 0)) {
                    if ($apply_at) {
                        $sub->setAssignedToContactId($assigned_to);
                        $modified = true;
                    }
                    if ($apply_ms) {
                        $sub->setMilestoneId($milestone_id);
                        $modified = true;
                    }
                    if ($modified) {
                        $sub->save();
                    }

                    //control date parent whit subtask
                    if ($_POST['control_dates'] == "father") {
                        if ($sub->getStartDate() instanceof DateTimeValue) {
                            if ($task->getStartDate() instanceof DateTimeValue) {
                                if ($task->getStartDate()->getTimestamp() > $sub->getStartDate()->getTimestamp())
                                    $sub->setStartDate($task->getStartDate());
                            }
                        }else {
                            if ($task->getStartDate() instanceof DateTimeValue)
                                $sub->setStartDate($task->getStartDate());
                        }
                        $sub->setUseStartTime($task->getUseStartTime());
                        if ($sub->getDueDate() instanceof DateTimeValue) {
                            if ($task->getDueDate() instanceof DateTimeValue) {
                                if ($task->getDueDate()->getTimestamp() < $sub->getDueDate()->getTimestamp())
                                    $sub->setDueDate($task->getDueDate());
                            }
                        }else {
                            if ($task->getDueDate() instanceof DateTimeValue)
                                $sub->setDueDate($task->getDueDate());
                        }
                        $sub->setUseDueTime($task->getUseDueTime());
                        $sub->save();
                    }
                }

                $task->resetIsRead();

                $log_info = '';
                if ($send_edit == true) {
                    $log_info = $task->getAssignedToContactId();
                } else if ($send_edit == false) {
                    $task->setAssignedBy(logged_user());
                    $task->save();
                }

                if ($task instanceof ProjectTask) {
                    $generated_count = $this->repetitive_task($task, array());
                    if ($generated_count > 0) {
                    	$_REQUEST['reload'] = true;
                    }
                }

                if (isset($_POST['type_related'])) {
                    if ($_POST['type_related'] == "all" || $_POST['type_related'] == "news") {
                        $task_data['members'] = json_decode(array_var($_POST, 'members'));

                        $task_data['previous_sd'] = $previous_start_date;
                        $task_data['previous_dd'] = $previous_due_date;

                        $task_data['new_sd'] = $task_data['start_date'];
                        $task_data['new_dd'] = $task_data['due_date'];

                        foreach ($task_data as $k => &$v) {
                            if (str_starts_with($k, "repeat_") || $k == "occurance_jump")
                                unset($task_data[$k]);
                        }

                        //apply change to related tasks
                        $modified_task_ids = $this->repetitive_tasks_related($task, "edit", $_POST['type_related'], $task_data);

                        if (count($modified_task_ids) > 0) {
                            $mtdata = array();
                            $modified_tasks = ProjectTasks::instance()->findAll(array('conditions' => "id IN (" . implode(',', $modified_task_ids) . ")"));
                            foreach ($modified_tasks as $mtask) {
                                $mtdata[] = $mtask->getArrayInfo();
                            }
                            if (count($mtdata) > 0) {
                                evt_add('update tasks in list', array('tasks' => $mtdata));
                            }
                        }
                    }
                }

                
                // Save the subtasks added/edited in the multi assignment section of the form
                if (config_option('multi_assignment') && Plugins::instance()->isActivePlugin('crpm')) {
                	$subtasks = array_var($_POST, 'multi_assignment');
                	Hook::fire('save_subtasks', array('task' => $task, 'is_new' => false), $subtasks);
                }
                

                //for calculate member status we save de task again after the object have the members
                $task->save();

                // save subtasks added in 'subtasks' tab
                $sub_tasks_to_log = $this->saveSubtasks($task, array_var($task_data, 'subtasks'), $member_ids);
                DB::commit();

                foreach ($sub_tasks_to_log['add'] as $st_to_log) {
                    ApplicationLogs::createLog($st_to_log, ApplicationLogs::ACTION_ADD);
                }
                foreach ($sub_tasks_to_log['edit'] as $st_to_log) {
                    ApplicationLogs::createLog($st_to_log, ApplicationLogs::ACTION_EDIT);
                }
                foreach ($sub_tasks_to_log['trash'] as $st_to_log) {
                    ApplicationLogs::createLog($st_to_log, ApplicationLogs::ACTION_TRASH);
                }


                //Send Template task to view
                if ($task instanceof TemplateTask) {
                    $objectId = $task->getObjectId();
                    $id = $task->getId();
                    $objectTypeName = $task->getObjectTypeName();
                    $objectName = $task->getObjectName();
                    $manager = get_class($task->manager());
                    $milestoneId = $task instanceof TemplateTask ? $task->getMilestoneId() : '0';
                    $subTasks = $task->getSubTasks();
                    $parentId = $task->getParentId();
                    $ico = "ico-task";
                    $action = "edit";
                    $object = TemplateController::prepareObject($objectId, $id, $objectName, $objectTypeName, $manager, $action, $milestoneId, $subTasks, $parentId, $ico, $task->getObjectTypeId(), $task->isRepetitive());

                    $template_task_data = array('object' => $object);

                    if (array_var($_REQUEST, 'additional_tt_params')) {
                        $additional_tt_params = json_decode(str_replace("'", '"', array_var($_REQUEST, 'additional_tt_params')), true);
                        foreach ($additional_tt_params as $k => $v)
                            $template_task_data[$k] = $v;
                    }

                    if (!array_var($_REQUEST, 'modal')) {
                        evt_add("template object added", $template_task_data);
                    }
                }

                $exclude_from_notification = array();
                try {
                    // notify asignee
                    if (array_var($task_data, 'send_notification') && ($task->getAssignedToContactId() != $task->getAssignedById())) {
                        $new_owner = $task->getAssignedTo();
                        if ($new_owner instanceof Contact) {
                            $exclude_from_notification[$task->getAssignedToContactId()] = $task->getAssignedToContactId();
                            Notifier::taskAssigned($task);
                        } // if
                    } // if
                    // notify assignee of new subtasks and subtasks that changed its assigned user.
                    foreach ($sub_tasks_to_log['assigned'] as $st_to_log) {
                        if ($st_to_log instanceof ProjectTask && $st_to_log->getAssignedToContactId() != $st_to_log->getAssignedById()) {
                            $exclude_from_notification[$st_to_log->getAssignedToContactId()] = $st_to_log->getAssignedToContactId();
                            Notifier::taskAssigned($st_to_log);
                        }
                    }
                } catch (Exception $e) {
                    Logger::log('Error sending notifications for task: ' . $task->getId());
                    Logger::log($e->getMessage());
                    Logger::log($e->getTraceAsString());
                } // try
                //notify subscribers
                $isSilent = true;
                if ((array_var($task_data, 'send_notification_subscribers'))) {
                    $isSilent = false;
                }
                $task->old_content_object = $old_content_object;
                $notify_subscribers = user_config_option("can notify subscribers");
                ApplicationLogs::createLog($task, ApplicationLogs::ACTION_EDIT, false, !$notify_subscribers, true, $log_info, $exclude_from_notification);

                //flash_success(lang('success edit task list', $task->getObjectName()));
                if (array_var($_REQUEST, 'modal')) {
                    if (array_var($_REQUEST, 'reload')) {
                        evt_add("reload current panel");
                    } else {
                        ajx_current("empty");
                        $this->setLayout("json");
                        $this->setTemplate(get_template_path("empty"));

                        // reload task info because plugins may have updated some task info (for example: name prefix)
                        if ($is_template) {
                            $task = TemplateTasks::instance()->findById($task->getId());
                        } else {
                            $task = ProjectTasks::instance()->findById($task->getId());
                        }

                        $params = array('msg' => lang('success edit task list', $task->getObjectName()), 'task' => $task->getArrayInfo(), 'reload' => array_var($_REQUEST, 'reload'));
                        if ($task instanceof TemplateTask) {
                            //$params['msg'] = lang('success edit template', $task->getObjectName());
                            $params['object'] = $template_task_data['object'];
                        }
                        //print_modal_json_response($params, true, array_var($_REQUEST, 'use_ajx'));
                        ajx_extra_data($params);
                    }
                } else {
                    ajx_current("back");
                }

                // if has subtasks and dates were changed, ask the user if the subtasks dates should also be changed
                if ($task instanceof ProjectTask && $task->countOpenSubTasks() > 0) {
                    // check if there was any due date changes
                    $dd_advance_info = null;
                    if ($task->getDueDate() instanceof DateTimeValue && $old_due_date instanceof DateTimeValue && $old_due_date->getTimestamp() != $task->getDueDate()->getTimestamp()) {
                        $dd_to_advance_ts = $task->getDueDate()->getTimestamp() - $old_due_date->getTimestamp();
                        if ($dd_to_advance_ts != 0) {
                            $dd_advance_info = get_time_info($dd_to_advance_ts);
                        }
                    }

                    // check if there was any start date changes
                    $sd_advance_info = null;
                    if ($task->getStartDate() instanceof DateTimeValue && $old_start_date instanceof DateTimeValue && $old_start_date->getTimestamp() != $task->getStartDate()->getTimestamp()) {
                        $sd_to_advance_ts = $task->getStartDate()->getTimestamp() - $old_start_date->getTimestamp();
                        if ($sd_to_advance_ts != 0) {
                            $sd_advance_info = get_time_info($sd_to_advance_ts);
                        }
                    }

                    if ($dd_advance_info != null || $sd_advance_info != null) {
                        evt_add('ask to change subtasks dates', array('dd_diff' => $dd_advance_info, 'sd_diff' => $sd_advance_info, 'task_id' => $task->getId()));
                    }
                }

                // Calculate task financials for previous parent id
                $old_parent_id = $old_content_object->getParentId();
                $task_parent_id = $task->getParentId();
                if($old_parent_id != $task_parent_id){
                    $old_parent_task = ProjectTasks::instance()->findById($old_parent_id);
                    Hook::fire('calculate_estimated_and_executed_financials', $params, $old_parent_task);
                }
            } catch (Exception $e) {
                DB::rollback();
                if (array_var($_REQUEST, 'modal')) {
                    $this->setLayout("json");
                    $this->setTemplate(get_template_path("empty"));
                    print_modal_json_response(array('errorCode' => 1, 'errorMessage' => $e->getMessage(), 'showMessage' => 1), true, true);
                } else {
                    flash_error($e->getMessage());
                }
                ajx_current("empty");
            } // try
        } // if
    }

// edit_task

    function updateLastTaskRepetitive($last_repetitive_task, $task) {
        $last_repetitive_task->setRepeatForever($task->getRepeatForever());
        $last_repetitive_task->setRepeatEnd($task->getRepeatEnd());
        $last_repetitive_task->setRepeatNum($task->getRepeatNum());
        $last_repetitive_task->setRepeatD($task->getRepeatD());
        $last_repetitive_task->setRepeatM($task->getRepeatM());
        $last_repetitive_task->setRepeatY($task->getRepeatY());
        $last_repetitive_task->setRepeatBy($task->getRepeatBy());
        $last_repetitive_task->save();
    }

    function resetRepeatProperties(&$task) {
        $task->setRepeatForever(0);
        $task->setRepeatEnd(EMPTY_DATETIME);
        $task->setRepeatNum(0);
        $task->setRepeatD(0);
        $task->setRepeatM(0);
        $task->setRepeatY(0);
        $task->setRepeatBy("");
    }

    function advance_subtasks_dates() {
        ajx_current("empty");

        $task = ProjectTasks::instance()->findById(array_var($_REQUEST, 'task_id'));
        if ($task instanceof ProjectTask && (array_var($_REQUEST, 'sd_diff') || array_var($_REQUEST, 'dd_diff'))) {

            $sd_diff = null;
            if (array_var($_REQUEST, 'sd_diff')) {
                $sd_diff = json_decode(array_var($_REQUEST, 'sd_diff'), true);
            }
            $dd_diff = null;
            if (array_var($_REQUEST, 'dd_diff')) {
                $dd_diff = json_decode(array_var($_REQUEST, 'dd_diff'), true);
            }

            $subtasks = $task->getOpenSubTasks();

            try {
                DB::beginWork();
                foreach ($subtasks as $subt) {/* @var $subt ProjectTask */
                    $modified = false;

                    if (is_array($dd_diff) && $subt->getDueDate() instanceof DateTimeValue) {
                        $seconds = array_var($dd_diff, 'days') * (60 * 60 * 24) + array_var($dd_diff, 'hours') * (60 * 60) + array_var($dd_diff, 'mins') * (60);
                        $seconds = array_var($dd_diff, 'sign', 1) * $seconds;
                        $new_dd = $subt->getDueDate()->advance($seconds, false);
                        $subt->setDueDate($new_dd);
                        $modified = true;
                    }

                    if (is_array($sd_diff) && $subt->getStartDate() instanceof DateTimeValue) {
                        $seconds = array_var($sd_diff, 'days') * (60 * 60 * 24) + array_var($sd_diff, 'hours') * (60 * 60) + array_var($sd_diff, 'mins') * (60);
                        $seconds = array_var($sd_diff, 'sign', 1) * $seconds;
                        $new_sd = $subt->getStartDate()->advance($seconds, false);
                        $subt->setStartDate($new_sd);
                        $modified = true;
                    }
                    if ($modified) {
                        $subt->save();
                    }
                }
                DB::commit();

                flash_success('success update subtasks dates');
                ajx_current("reload");
            } catch (Exception $e) {
                DB::rollback();
                flash_error($e->getMessage());
            }
        }
    }

    /**
     * Delete task
     *
     * @access public
     * @param void
     * @return null
     */
    function delete_task() {
        if (logged_user()->isGuest()) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        }
        ajx_current("empty");
        $project = active_or_personal_project();
        $task = ProjectTasks::instance()->findById(get_id());
        if (!($task instanceof ProjectTask)) {
            flash_error(lang('task dnx'));
            return;
        } // if

        if (!$task->canDelete(logged_user())) {
            flash_error(lang('no access permissions'));
            return;
        } // if

        try {
            DB::beginWork();
            $is_template = $task instanceof TemplateTask;
            $task->trash();
            DB::commit();
            ApplicationLogs::createLog($task, ApplicationLogs::ACTION_TRASH);

            if ($is_template) {
                flash_success(lang('success delete template', $task->getObjectName()));
            } else {
                flash_success(lang('success delete task list', $task->getObjectName()));
            }
            if (array_var($_GET, 'quick', false)) {
                ajx_current('empty');
            } else if (array_var($_GET, 'taskview', false)) {
                ajx_current('reload');
            } else {
                ajx_current('back');
            }
        } catch (Exception $e) {
            DB::rollback();
            flash_error(lang('error delete task list'));
        } // try
    }

// delete_task
    // ---------------------------------------------------
    //  Tasks
    // ---------------------------------------------------

    //function that generate repetiion dates
    function getNextRepetitionDates($task, $opt_rep_day, &$new_st_date, &$new_due_date, $repetition_params = array()) { 
        $new_due_date = null;
        $new_st_date = null;
        $original_st_date = array_var($repetition_params, 'original_st_date');
        $original_due_date = array_var($repetition_params, 'original_due_date');
        $count = array_var($repetition_params, 'count');

        if ($task->getStartDate() instanceof DateTimeValue) {
            $new_st_date = new DateTimeValue($task->getStartDate()->getTimestamp());
        }
        if ($task->getDueDate() instanceof DateTimeValue) {
            $new_due_date = new DateTimeValue($task->getDueDate()->getTimestamp());
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

        $correct_the_days = true;
        Hook::fire('check_working_days_to_correct_repetition', array('task' => $task), $correct_the_days);
        if ($correct_the_days) {
            $new_st_date = $this->correct_days_task_repetitive($new_st_date);
            $new_due_date = $this->correct_days_task_repetitive($new_due_date);
        }

        return array('st' => $new_st_date, 'due' => $new_due_date);
    }

    function generate_new_repetitive_instance($task = null) {
        $use_transaction = false;
        if (is_null($task)) {
        	ajx_current("empty");
        	$use_transaction = true;
        	$task = ProjectTasks::instance()->findById(get_id());
        }
        if (!($task instanceof ProjectTask)) {
            flash_error(lang('task dnx'));
            return;
        } // if

        if (!$task->isRepetitive()) {
            if ($use_transaction) {
            	flash_error(lang('task not repetitive'));
            }
            return;
        }

        $this->getNextRepetitionDates($task, array(), $new_st_date, $new_due_date);

        $daystoadd = 0;
        $params = array('task' => $task, 'new_st_date' => $new_st_date, 'new_due_date' => $new_due_date);
        Hook::fire('check_valid_repetition_date_days_add', $params, $daystoadd);

        if ($daystoadd > 0) {
            if ($new_st_date)
                $new_st_date->add('d', $daystoadd);
            if ($new_due_date)
                $new_due_date->add('d', $daystoadd);
        }

        // if this is the last task of the repetetition, do not generate a new instance
        if ($task->getRepeatNum() > 0) {
            $task->setRepeatNum($task->getRepeatNum() - 1);
            if ($task->getRepeatNum() == 0) {
                flash_error(lang('task cannot be instantiated more times'));
                return;
            }
        }
        if ($task->getRepeatEnd() instanceof DateTimeValue) {
            if ($task->getRepeatBy() == 'start_date' && $new_st_date > $task->getRepeatEnd() ||
                    $task->getRepeatBy() == 'due_date' && $new_due_date > $task->getRepeatEnd()) {
                flash_error(lang('task cannot be instantiated more times'));
                return;
            }
        }
        try {

            // generate new pending task
            $new_task = $task->cloneTask($new_st_date, $new_due_date);
            $task->clearRepeatOptions();
            foreach ($new_task->getAllSubTasks() as $subt) {
                $subt->setCompletedById(0);
                $subt->setCompletedOn(EMPTY_DATETIME);
                $subt->save();
            }

            if ($use_transaction) {
            	DB::beginWork();
            }

            $new_task->save();
            $task->save();

            if ($use_transaction) {
	            DB::commit();
	            flash_success(lang("new task repetition generated"));
            }

            ajx_current("back");
        } catch (Exception $e) {
        	if ($use_transaction) {
            	DB::rollback();
	            flash_error($e->getMessage());
        	}
        }
        return $new_task;
    }

    /**
     * Complete task
     *
     * @access public
     * @param void
     * @return null
     */
    function complete_task() { 
        $options = array_var($_GET, 'options');
        if (logged_user()->isGuest()) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        }

        ajx_current("empty");
        $task = ProjectTasks::instance()->findById(get_id());
        if (!($task instanceof ProjectTask)) {
            flash_error(lang('task dnx'));
            return;
        } // if
        // don't complete the same task twice
        if ($task->getCompletedById() > 0) {
            ajx_current("empty");
            return;
        }

        if (!$task->canEdit(logged_user()) && $task->getAssignedTo() != logged_user()) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        }
        //	
        if (!$task->canChangeStatus(logged_user())) {
            flash_error(lang('no access permissions'));
            return;
        } // if

        try {
        	// to use when saving the application log
        	$old_content_object = $task->generateOldContentObjectData();
        	
            $reload_view = false;
            DB::beginWork();

            $result = $task->completeTask();
            $log_info = $result['log_info'];
            $new_task = $result['new_task'];

            DB::commit();
            // reload task's object new values
            $task = ProjectTasks::instance()->findById($task->getId(), true);
            $task->old_content_object = $old_content_object;
            
			if ($result['save_log']) {
            	ApplicationLogs::createLog($task, ApplicationLogs::ACTION_CLOSE, false, false, true, substr($log_info, 0, -1));
			}
            flash_success(lang('success complete task'));

            $pending_subtasks = 0;
            $subt_info = array();
            foreach ($task->getAllSubTasks() as $sub) {
                $subt_info[] = $sub->getArrayInfo();
                if ($sub->getCompletedById() == 0)
                    $pending_subtasks++;
            }
            if ($pending_subtasks > 0) {
                evt_add('ask to complete subtasks', array('parent_id' => $task->getId()));
            }

            $more_tasks = array();
            if (isset($new_task) && $new_task instanceof ProjectTask) {
                $more_tasks[] = $new_task->getArrayInfo();
            }

            Timeslots::instance()->clearTimeslotsCache($task->getId());
            ajx_extra_data(array("task" => $task->getArrayInfo(), 'subtasks' => $subt_info, 'more_tasks' => $more_tasks));

            if (array_var($_GET, 'quick', false) && !$reload_view) {
                ajx_current("empty");
            } else {
                ajx_current("reload");
            }
        } catch (Exception $e) {
            DB::rollback();
            flash_error($e->getMessage());
        } // try
    }

// complete_task

    function complete_subtasks() {

        ajx_current("empty");
        $task = ProjectTasks::instance()->findById(get_id());
        if (!($task instanceof ProjectTask)) {
            flash_error(lang('task dnx'));
            return;
        }

        if (!$task->canEdit(logged_user()) && $task->getAssignedTo() != logged_user()) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        }

        try {
            DB::beginWork();

            $completed_tasks = array();
            $log_info = array();
            $subtasks = $task->getAllSubTasks();

            // all subtasks must be completed
            $options = array('ignore_task_dependencies' => true);

            foreach ($subtasks as $sub) {
                if ($sub->getCompletedById() == 0 && $sub->canChangeStatus(logged_user())) {
                	$sub_result = $sub->completeTask($options);
					if ($sub_result['save_log']) {
						$log_info[$sub->getId()] = $sub_result['log_info'];
						$completed_tasks[] = $sub;
					}
                }
            }

            DB::commit();
            flash_success(lang('success complete subtasks of', $task->getObjectName()));

            $tasks_info = array();
            foreach ($completed_tasks as $sub) {
                if (isset($log_info[$sub->getId()])) {
                    $linfo = array_var($log_info, $sub->getId(), '');
                    ApplicationLogs::createLog($sub, ApplicationLogs::ACTION_CLOSE, false, true, true, substr($linfo, 0, -1));
                }
                $tasks_info[] = $sub->getArrayInfo();
            }

            ajx_extra_data(array("tasks" => $tasks_info));
        } catch (Exception $e) {
            DB::rollback();
            flash_error($e->getMessage());
        }
    }

    /**
     * Reopen completed task
     *
     * @access public
     * @param void
     * @return null
     */
    function open_task() {
        if (logged_user()->isGuest()) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        }
        ajx_current("empty");
        $task = ProjectTasks::instance()->findById(get_id());
        if (!($task instanceof ProjectTask)) {
            flash_error(lang('task dnx'));
            return;
        } // if
        // don't open the same task twice
        if ($task->getCompletedById() == 0) {
            ajx_current("empty");
            return;
        }

        if (!$task->canChangeStatus(logged_user())) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        } // if

        try {
        	// to use when saving the application log
        	$old_content_object = $task->generateOldContentObjectData();
        	
            DB::beginWork();
            $log_info = $task->openTask();

            /* FIXME $opened_tasks = array();
              $parent = $task->getParent();
              while ($parent instanceof ProjectTask && $parent->isCompleted()) {
              $parent->openTask();
              $opened_tasks[] = $parent->getId();
              $milestone = ProjectMilestones::instance()->findById($parent->getMilestoneId());
              if ($milestone instanceof ProjectMilestones && $milestone->isCompleted()) {
              $milestone->setCompletedOn(EMPTY_DATETIME);
              ajx_extra_data(array("openedMilestone" => $milestone->getId()));
              }
              $parent = $parent->getParent();
              }
              ajx_extra_data(array("openedTasks" => $opened_tasks)); */

            //Already called in openTask
            //ApplicationLogs::createLog($task, ApplicationLogs::ACTION_OPEN);
            DB::commit();
            ApplicationLogs::createLog($task, ApplicationLogs::ACTION_OPEN, false, false, true, substr($log_info, 0, -1));
            flash_success(lang('success open task'));

            //$redirect_to = array_var($_GET, 'redirect_to', false);
            if (array_var($_GET, 'quick', false)) {
                ajx_current("empty");
                ajx_extra_data(array("task" => $task->getArrayInfo()));
            } else {
                ajx_current("reload");
            }
        } catch (Exception $e) {
            DB::rollback();
            flash_error(lang('error open task'));
        } // try
    }

// open_task

    /**
     * Create a new template
     *
     */
    function new_template() {
        if (logged_user()->isGuest()) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        }

        $notAllowedMember = '';
        if (!ProjectTask::canAdd(logged_user(), active_context(), $notAllowedMember)) {
            if (str_starts_with($notAllowedMember, '-- req dim --'))
                flash_error(lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in)));
            else
                trim($notAllowedMember) == "" ? flash_error(lang('you must select where to keep', lang('the task'))) : flash_error(lang('no context permissions to add', lang("tasks"), $notAllowedMember));
            ajx_current("empty");
            return;
        } // if


        $id = get_id();
        $task = ProjectTasks::instance()->findById($id);
        if (!$task instanceof ProjectTask) {
            $task_data = array('is_template' => true);
        } else {
            $task_data = array(
                'milestone_id' => $task->getMilestoneId(),
                'title' => $task->getObjectName(),
                'assigned_to' => $task->getAssignedToContactId(),
                'parent_id' => $task->getParentId(),
                'priority' => $task->getPriority(),
                'time_estimate' => $task->getTimeEstimate(),
                'text' => $task->getText(),
                'is_template' => true,
                'copyId' => $task->getId(),
            ); // array
            if ($task->getStartDate() instanceof DateTimeValue) {
                $task_data['start_date'] = $task->getStartDate()->getTimestamp();
            }
            if ($task->getDueDate() instanceof DateTimeValue) {
                $task_data['due_date'] = $task->getDueDate()->getTimestamp();
            }
        }

        $task = new ProjectTask();
        tpl_assign('task_data', $task_data);
        tpl_assign('task', $task);
        $this->setTemplate("add_task");
    }

// new_template

    function allowed_users_to_assign() {

        $members = array();
        $member_ids = explode(',', array_var($_GET, 'member_ids'));
        if (count($member_ids) > 0) {
            $tmp_members = Members::instance()->findAll(array('conditions' => 'id IN (' . implode(',', $member_ids) . ')'));
            foreach ($tmp_members as $m) {
                if ($m->getDimension()->getIsManageable())
                    $members[] = $m;
            }
        }

        if (count($members) == 0) {
            $context_plain = array_var($_GET, 'context');
            $context = null;
            if (!is_null($context_plain))
                $context = build_context_array($context_plain);
        } else {
            $context = $members;
        }

        $for_template_var = array_var($_GET, 'for_template_var');

        $comp_array = allowed_users_to_assign($context, !$for_template_var, true, true);
        $object = array(
            "companies" => $comp_array
        );

        Hook::fire('modify_allowed_users_to_assign', array('params' => array_var($_REQUEST, 'extra_params')), $object);

        if (count($comp_array) == 1 && count($comp_array[0]['users']) == 1 && $comp_array[0]['users'][0]['id'] == logged_user()->getId()) {
            $object['only_me'] = "1";
        }

        ajx_extra_data($object);
        ajx_current("empty");

        return $object;
    }

// allowed_users_to_assign

    function change_start_due_date() {
        $task = ProjectTasks::instance()->findById(get_id());
        if (!$task->canEdit(logged_user())) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        }

        // to use when saving the application log
        $old_content_object = $task->generateOldContentObjectData();
        
        $tochange = array_var($_GET, 'tochange', '');

        $conserve_times = array_var($_GET, 'conserve_times', 0);

        if (($tochange == 'both' || $tochange == 'due') && $task->getDueDate() instanceof DateTimeValue) {
            $old_due_date = $task->getDueDate(); //gmt 0

            $year = array_var($_GET, 'year', $task->getDueDate()->getYear());
            $month = array_var($_GET, 'month', $task->getDueDate()->getMonth());
            $day = array_var($_GET, 'day', $task->getDueDate()->getDay());
            $hour = array_var($_GET, 'hour', $task->getDueDate()->getHour());
            $minute = array_var($_GET, 'min', $task->getDueDate()->getMinute());



            if ($conserve_times) {
                $old_due_date->advance($task->getTimezoneValue());
                $hour = $old_due_date->getHour();
                $minute = $old_due_date->getMinute();

                $new_date = new DateTimeValue(mktime($hour, $minute, 0, $month, $day, $year));

                $new_date->advance($task->getTimezoneValue() * -1); //gmt 0
            } else {
                $new_date = new DateTimeValue(mktime($hour, $minute, 0, $month, $day, $year));
                if (isset($_GET['hour']) && isset($_GET['min'])) {
                    $new_date->advance($task->getTimezoneValue() * -1);
                }
            }

            $task->setDueDate($new_date);
        }
        if (($tochange == 'both' || $tochange == 'start') && $task->getStartDate() instanceof DateTimeValue) {
            $old_start_date = $task->getStartDate(); //gmt 0

            $year = array_var($_GET, 'year', $task->getStartDate()->getYear());
            $month = array_var($_GET, 'month', $task->getStartDate()->getMonth());
            $day = array_var($_GET, 'day', $task->getStartDate()->getDay());
            $hour = array_var($_GET, 'hour', $task->getStartDate()->getHour());
            $minute = array_var($_GET, 'min', $task->getStartDate()->getMinute());

            if ($conserve_times) {
                $old_start_date->advance($task->getTimezoneValue());
                $hour = $old_start_date->getHour();
                $minute = $old_start_date->getMinute();

                $new_date = new DateTimeValue(mktime($hour, $minute, 0, $month, $day, $year));

                $new_date->advance($task->getTimezoneValue() * -1); //gmt 0
            } else {
                $new_date = new DateTimeValue(mktime($hour, $minute, 0, $month, $day, $year));
                if (isset($_GET['hour']) && isset($_GET['min'])) {
                    $new_date->advance($task->getTimezoneValue() * -1);
                }
            }

            $task->setStartDate($new_date);
        }

        try {
            DB::beginWork();
            $task->save();
            DB::commit();
            
            ApplicationLogs::createLog($task, ApplicationLogs::ACTION_EDIT, false, false, true);
            
        } catch (Exception $e) {
            DB::rollback();
            flash_error(lang('error change date'));
        } // try
        ajx_current("empty");
    }

    private function getRepeatOptions($task, &$occ, &$rsel1, &$rsel2, &$rsel3, &$rnum, &$rend, &$rjump) {
        //Repeating options
        $rsel1 = false;
        $rsel2 = false;
        $rsel3 = false;
        $rend = null;
        $rnum = null;
        $occ = 1;
        if ($task->getRepeatD() > 0) {
            $occ = 2;
            $rjump = $task->getRepeatD();
        }
        if ($task->getRepeatD() > 0 AND $task->getRepeatD() % 7 == 0) {
            $occ = 3;
            $rjump = $task->getRepeatD() / 7;
        }
        if ($task->getRepeatM() > 0) {
            $occ = 4;
            $rjump = $task->getRepeatM();
        }
        if ($task->getRepeatY() > 0) {
            $occ = 5;
            $rjump = $task->getRepeatY();
        }
        if ($task->getRepeatEnd())
            $rend = $task->getRepeatEnd();
        if ($task->getRepeatNum() > 0)
            $rnum = $task->getRepeatNum();
        if (!isset($rjump) || !is_numeric($rjump))
            $rjump = 1;
        // decide which repeat type it is
        if ($task->getRepeatForever())
            $rsel1 = true; //forever
        else if (isset($rnum) AND $rnum > 0)
            $rsel2 = true; //repeat n-times
        else if (isset($rend) AND $rend instanceof DateTimeValue)
            $rsel3 = true; //repeat until
    }

    private function setRepeatOptions(&$task_data) {

		// don't process repetitive options it they are not present in the task's data
		// to avoid overriding the repetition options defined in the original task if the user doesn't have permissions to edit rep. options.
		if (!isset($task_data['repeat_option'])) {
			return;
		}

        // repeat options
        $repeat_d = 0;
        $repeat_m = 0;
        $repeat_y = 0;
        $repeat_h = 0;
        $rend = '';
        $forever = 0;
        $jump = array_var($task_data, 'occurance_jump');

        $rnum = "";
        
        if (array_var($task_data, 'repeat_option') == 1) {
            $forever = 1; 
        } elseif (array_var($task_data, 'repeat_option') == 2) {
            $rnum = array_var($task_data, 'repeat_num');
            if($rnum == '') return lang('please provide input for repeat times');
            if (isset($rnum) && $rnum) {
                if (!is_numeric($rnum) || $rnum < 1 || $rnum > 1000)
                    throw new Exception(lang('repeat x times must be a valid number between 1 and 1000'));
            }
        } elseif (array_var($task_data, 'repeat_option') == 3) {
            $rend = getDateValue(array_var($task_data, 'repeat_end'));
            if(!$rend instanceof DateTimeValue) return lang('please provide date for repeat until');
        }
       
        if (isset($jump) && $jump) {
            if (!is_numeric($jump) || $jump < 1 || $jump > 1000)
                throw new Exception(lang('repeat period must be a valid number between 1 and 1000'));
        } else {
            $occurrance = array_var($task_data, 'occurance');
            if ($occurrance && $occurrance != 1)
                return lang('repeat period must be a valid number between 1 and 1000');
        }

        // check for repeating options
        // 1=repeat once, 2=repeat daily, 3=weekly, 4=monthy, 5=yearly, 6=holiday repeating
        $oend = null;
        switch (array_var($task_data, 'occurance')) {
            case "1":
                $forever = 0;
                $task_data['repeat_d'] = 0;
                $task_data['repeat_m'] = 0;
                $task_data['repeat_y'] = 0;
                $rnum = 0;
                $task_data['repeat_by'] = '';
                break;
            case "2":
                $task_data['repeat_d'] = $jump;
                $task_data['repeat_m'] = 0;
                $task_data['repeat_y'] = 0;
                if (isset($forever) && $forever == 1)
                    $oend = null;
                else
                    $oend = $rend;
                break;
            case "3":
                $task_data['repeat_d'] = 7 * $jump;
                $task_data['repeat_m'] = 0;
                $task_data['repeat_y'] = 0;
                if (isset($forever) && $forever == 1)
                    $oend = null;
                else
                    $oend = $rend;
                break;
            case "4":
                $task_data['repeat_m'] = $jump;
                $task_data['repeat_d'] = 0;
                $task_data['repeat_y'] = 0;
                if (isset($forever) && $forever == 1)
                    $oend = null;
                else
                    $oend = $rend;
                break;
            case "5":
                $task_data['repeat_y'] = $jump;
                $task_data['repeat_d'] = 0;
                $task_data['repeat_m'] = 0;
                if (isset($forever) && $forever == 1)
                    $oend = null;
                else
                    $oend = $rend;
                break;
            default: break;
        }
        $task_data['repeat_num'] = $rnum;
        $task_data['repeat_forever'] = $forever;
        $task_data['repeat_end'] = $oend;

        if (($task_data['repeat_num'] || $task_data['repeat_forever'] || $task_data['repeat_end']) && !$task_data['is_template']) {
            if ($task_data['repeat_by'] == 'start_date' && !$task_data['start_date'] instanceof DateTimeValue) {
                return lang('to repeat by start date you must specify task start date');
            }
            if ($task_data['repeat_by'] == 'due_date' && !$task_data['due_date'] instanceof DateTimeValue) {
                return lang('to repeat by due date you must specify task due date');
            }
        }
        return null;
    }


    /**
     * Generates the repetitive instances of a task
     * @param ProjectTask $task: Task to replicate
     * @param array $opt_rep_day: days options array
     * @param DateTimeValue $forced_repeat_end: if defined then the last repetition generated won't be after this date
     */
    function repetitive_task($task, $opt_rep_day, $forced_repeat_end = null) {
        $generated_count = 0;

        $working_days_only = 1; // True
        $move_direction = $task->getMoveDirectionNonWorkingDays() ? $task->getMoveDirectionNonWorkingDays() : 'advance';
        // Get template ID for the repetitive task
        $template_id = $task->getColumnValue('from_template_id');
        // Use template data, if tasks created by template
        if($template_id){
            // Get template
            $template = COTemplates::instance()->findById($template_id);
            
            if ($template instanceof COTemplate) {
	            // Get data form  use_only_working_days column for the template, expecting either 1 or 0
	            $working_days_only = $template->getColumnValue('use_only_working_days');
	            // Get data from nw_days_todo_action column for the template, expecting "advance" or "move_back"
	            $move_direction = $template->getColumnValue('nw_days_todo_action');
            }
            
            // check if due or start dates depends on any template parameter, if so then use the parameter as the original date
            $original_dates = find_original_dates_for_template_repetitive_task($task);
            if (array_var($original_dates, 'original_due_date') instanceof DateTimeValue) {
            	$original_due_date = array_var($original_dates, 'original_due_date');
            }
            if (array_var($original_dates, 'original_st_date') instanceof DateTimeValue) {
            	$original_st_date = array_var($original_dates, 'original_st_date');
            }
            if($task->getRepeatM() && $task->getOriginalTaskId() > 0){
                $row = DB::executeOne("
                    SELECT count(distinct(".$task->getRepeatBy().")) as 'count'
                    FROM ".TABLE_PREFIX."project_tasks 
                    WHERE original_task_id=".$task->getOriginalTaskId().";
                ");
                $generated_count = $row['count'];
            }
            
        }
        if ($task->isRepetitive() && (!$task->getRepeatForever() || $forced_repeat_end instanceof DateTimeValue )) {
            $last_task = null;
            if ($task->getRepeatNum() > 0) {

            	if (!isset($original_st_date)) {
	                if ($task->getStartDate() instanceof DateTimeValue){
	                    $original_st_date = $task->getStartDate();
	                } else {
	                    $original_st_date = NULL;
	                }
            	}
            	if (!isset($original_due_date)) {
	                if ($task->getDueDate() instanceof DateTimeValue){
	                    $original_due_date = $task->getDueDate();
	                } else {
	                    $original_due_date = NULL;
	                }
            	}
                $repetition_params = array(
                    'original_st_date' => $original_st_date,
                    'original_due_date' => $original_due_date
                );
                $task->setRepeatNum($task->getRepeatNum() - 1);
                while ($task->getRepeatNum() > 0) {
                    $repetition_params['count'] = $generated_count;
                    $this->getNextRepetitionDates($task, $opt_rep_day, $new_st_date, $new_due_date, $repetition_params);

                    if($working_days_only == 1) {
                        $daystoadd = 0;
                        $params = array('task' => $task, 'new_st_date' => $new_st_date, 'new_due_date' => $new_due_date, 'move_direction' => $move_direction);
                        Hook::fire('check_valid_repetition_date_days_add', $params, $daystoadd);
                        if ($daystoadd != 0) {
                            if ($new_st_date)
                                $new_st_date->add('d', $daystoadd);
                            if ($new_due_date)
                                $new_due_date->add('d', $daystoadd);
                        }
                    }

                    $task->setRepeatNum($task->getRepeatNum() - 1);

                    // generate completed task
                    $last_task = $task->cloneTask($new_st_date, $new_due_date, true, false, 0, $generated_count);
                    // set next values for repetetive task
                    if ($task->getStartDate() instanceof DateTimeValue)
                        $task->setStartDate($new_st_date);
                    if ($task->getDueDate() instanceof DateTimeValue)
                        $task->setDueDate($new_due_date);
                    foreach ($task->getAllSubTasks() as $subt) {
                        $subt->setCompletedById(0);
                        $subt->setCompletedOn(EMPTY_DATETIME);
                        $subt->save();
                    }
                    $task->save();

                    $generated_count++;
                }
              
                if (isset($original_due_date))
                    $task->setDueDate($original_due_date);
                if (isset($original_st_date))
                    $task->setStartDate($original_st_date);
                $task->save();
          } elseif ($task->getRepeatForever() == 0 || $forced_repeat_end instanceof DateTimeValue) {
                if ($forced_repeat_end instanceof DateTimeValue) {
                    $task_end = $forced_repeat_end;
                } else {
                    $task_end = $task->getRepeatEnd();
                }
                
                $new_st_date = "";
                $new_due_date = "";

                // Safe original due date in the variable
                if (!isset($original_due_date)) {
	                if ($task->getDueDate() instanceof DateTimeValue) {
	                    $original_due_date = new DateTimeValue($task->getDueDate()->getTimestamp());
	                } else {
	                    $original_due_date = NULL;
	                }
                }
                //Safe original start date in the variable
                if (!isset($original_st_date)) {
	                if ($task->getStartDate() instanceof DateTimeValue) {
	                	$original_st_date = new DateTimeValue($task->getStartDate()->getTimestamp());
	                } else {
	                    $original_st_date = NULL;
	                }
                }
                $repetition_params = array(
                    'original_st_date' => $original_st_date,
                    'original_due_date' => $original_due_date
                );
                
                $first_iteration = true;
                while ($task->getRepeatBy() == 'start_date' && ($new_st_date == "" || $new_st_date->getTimestamp() <= $task_end->getTimestamp()) ||
                		$task->getRepeatBy() == 'due_date' && ($new_due_date == "" || $new_due_date->getTimestamp() <= $task_end->getTimestamp())) {
                    $repetition_params['count'] = $generated_count;
                    
                    if ($first_iteration) {
                    	// Generate the first task of the repetition with the original start and due date, no need to calculate repetition dates for the first task
                    	// this is only needed when generating instances for a repetition with fixed end date
                    	if ($task->getRepeatEnd() instanceof DateTimeValue) {
                    		$last_task = $task->cloneTask($task->getStartDate(), $task->getDueDate(), true, false, 0, $generated_count);
	                    	$generated_count++;
                    	}
                    	
                    } else {
                    	// @TODO change getNextRepetiotionDates to return new dates in an array
                    	// $new_dates = $this -> getNextRepetitionDates()
                    	$this->getNextRepetitionDates($task, $opt_rep_day, $new_st_date, $new_due_date, $repetition_params);
                    	
                    	if($working_days_only == 1) {
                    		$daystoadd = 0;
                    		$params = array('task' => $task, 'new_st_date' => $new_st_date, 'new_due_date' => $new_due_date, 'move_direction' => $move_direction);
                    		Hook::fire('check_valid_repetition_date_days_add', $params, $daystoadd);
                    		if ($daystoadd != 0) {
                    			if ($new_st_date)
                    				$new_st_date->add('d', $daystoadd);
                    			if ($new_due_date)
                    				$new_due_date->add('d', $daystoadd);
                    		}
                    	}
                    	
                    	// generate completed task
                    	if ($task->getRepeatBy() == 'start_date' && ($new_st_date == "" || $new_st_date->getTimestamp() <= $task_end->getTimestamp()) ||
                    		$task->getRepeatBy() == 'due_date' && ($new_due_date == "" || $new_due_date->getTimestamp() <= $task_end->getTimestamp())){
                    				
                    		$last_task = $task->cloneTask($new_st_date,$new_due_date,true, false, 0, $generated_count);
                    		// set next values for repetetive task
                    		if ($task->getStartDate() instanceof DateTimeValue ) $task->setStartDate($new_st_date);
                    		if ($task->getDueDate() instanceof DateTimeValue ) $task->setDueDate($new_due_date);
                    		foreach ($task->getAllSubTasks() as $subt) {
                    			$subt->setCompletedById(0);
                  				$subt->setCompletedOn(EMPTY_DATETIME);
                    			$subt->save();
                    		}
                    				
                    		$generated_count++;
                    		
                    	} else {
                    		// check if $task is inside the time period, using the end of day of $task_end
                    		$task_end->endOfDay();
                    		if ($task->getRepeatBy() == 'start_date' && $new_st_date->getTimestamp() <= $task_end->getTimestamp()
                    			|| $task->getRepeatBy() == 'due_date' && $new_due_date->getTimestamp() <= $task_end->getTimestamp()) {
                    			
	                    		// this is the last task, so set their dates to the last caculated dates
	                    		if ($task->getStartDate() instanceof DateTimeValue ) $task->setStartDate($new_st_date);
	                    		if ($task->getDueDate() instanceof DateTimeValue ) $task->setDueDate($new_due_date);
	                    		$task->save();
	                    		
                    		} else {
                    			// delete $last_task, if it is repeated with the last $task instance
                    			if ($last_task instanceof ProjectTask && 
                    				($task->getRepeatBy() == 'due_date' && $task->getDueDate()->getTimestamp() == $last_task->getDueDate()->getTimestamp() ||
                    				$task->getRepeatBy() == 'start_date' && $task->getStartDate()->getTimestamp() == $last_task->getStartDate()->getTimestamp())) {
                    					$last_task->delete(); 
                    			}
                    		}
                    	}
                    }
                    $first_iteration = false;
                    
                }
            }
            // if there are more repetitions to generate then copy repetition settings to the last task
            if ($last_task instanceof ProjectTask && $task->getRepeatForever() && $forced_repeat_end instanceof DateTimeValue) {
                $last_task->setRepeatForever($task->getRepeatForever());
                $last_task->setRepeatEnd($task->getRepeatEnd());
                $last_task->setRepeatNum($task->getRepeatNum());
                $last_task->setRepeatD($task->getRepeatD());
                $last_task->setRepeatM($task->getRepeatM());
                $last_task->setRepeatY($task->getRepeatY());
                $last_task->setRepeatBy($task->getRepeatBy());
                $last_task->save();
                
                // ensure that this task is classified
                $task_object_members = $task->getMembers();
                $last_task->addToMembers($task_object_members);
                Hook::fire ('after_add_to_members', $last_task, $task_object_members);
                $last_task->addToSharingTable();
                
            }
            if ($last_task instanceof ProjectTask){
                $task->setRepeatForever(0);
                $task->setRepeatEnd(EMPTY_DATETIME);
                $task->setRepeatNum(0);
                $task->setRepeatD(0);
                $task->setRepeatM(0);
                $task->setRepeatY(0);
                $task->setRepeatBy("");
                $task->save();
            }
        }
        return $generated_count;
    }

    /**
     * To handle drag and drop in tasks list
     */
    function edit_tasks_attribute() {
        ajx_current("empty");

        $attribute = array_var($_POST, 'attribute');
        $new_value = array_var($_POST, 'new_value');

        $task_ids = json_decode(array_var($_POST, 'task_ids'), true);
        if (is_array($task_ids) && count($task_ids) > 0) {
            $application_logs = array();

            $tasks = ProjectTasks::instance()->findAll(array('conditions' => 'id IN (' . implode(',', $task_ids) . ')'));
            try {

                foreach ($tasks as $task) {
                    /* @var $task ProjectTask */

                    if ($task->canEdit(logged_user())) {
                    	
                    	// to use when saving the application log
                    	$old_content_object = $task->generateOldContentObjectData();
                    	
                        switch ($attribute) {
                            case 'assigned_to':
                                $user = Contacts::instance()->findById($new_value);
                                if ($user instanceof Contact && $user->isUser() && can_task_assignee($user) && $task->getAssignedToContactId() != $user->getId()) {
                                    $task->setAssignedToContactId($user->getId());
                                    $task->setAssignedOn(DateTimeValueLib::now());
                                    $task->setAssignedById(logged_user()->getId());
                                    $task->save();

                                    $application_logs[] = array($task, ApplicationLogs::ACTION_EDIT, false, logged_user()->getId() != $user->getId());
                                }
                                break;
                            case 'status':
                                if ($new_value == 1) {
                                    // complete task
                                    $task->setCompletedOn(DateTimeValueLib::now());
                                    $task->setCompletedById(logged_user()->getId());
                                    $application_logs[] = array($task, ApplicationLogs::ACTION_CLOSE);
                                } else {
                                    // reopen task
                                    $task->setCompletedOn(EMPTY_DATETIME);
                                    $task->setCompletedById(0);
                                    $application_logs[] = array($task, ApplicationLogs::ACTION_OPEN);
                                }
                                $task->save();
                                break;
                            case 'priority':
                            case 'milestone':
                                $col = $attribute == 'milestone' ? 'milestone_id' : $attribute;
                                $task->setColumnValue($col, $new_value);
                                $task->save();
                                $application_logs[] = array($task, ApplicationLogs::ACTION_EDIT);
                                break;

                            case 'due_date':
                            case 'start_date':
                                $new_date_value = getDateValue($new_value);
                                $new_date_value = new DateTimeValue($new_date_value->getTimestamp() - $task->getTimezoneValue());

                                $task->setColumnValue($attribute, $new_date_value);
                                $task->save();
                                $application_logs[] = array($task, ApplicationLogs::ACTION_EDIT);

                                break;
                            default:
                                break;
                        }
                    }
                }
            } catch (Exception $e) {
                DB::rollback();
                ajx_current("empty");
                flash_error($e->getMessage());
            }

            foreach ($application_logs as $log) {
                if (count($log) >= 2 && $log[0] instanceof ApplicationDataObject) {
                    call_user_func_array('ApplicationLogs::createLog', $log);
                }
            }
        }
    }

    function repetitive_tasks_related($task, $action, $type_related = "", $task_data = array()) { 
    	
    	// if task is completed, only modify the current task, don't affect the rest of the sequence
    	if ($task->isCompleted()) return array();
    	
        //I find all those related to the task to find out if the original        
        $task_related = ProjectTasks::findByRelated($task->getObjectId());
        if (!$task_related) {
            //is not the original as the original look plus other related
            if ($task->getOriginalTaskId() != "0") {
                $task_related = ProjectTasks::findByTaskAndRelated($task->getObjectId(), $task->getOriginalTaskId());
            }
        }
        if ($task_related) {
            switch ($action) {
                case "edit":
                    $edited_tasks = array();
                    foreach ($task_related as $t_rel) {
                        //if user select option This task and all to come forward
                        if ($type_related == "news") {
                            //compare if the related task is more new that the original for include to update
                            if ($task_data['previous_sd'] <= $t_rel->getStartDate() && $task_data['previous_dd'] <= $t_rel->getDueDate()) {
                                $this->repetitive_task_related_edit($t_rel, $task_data);
                                $edited_tasks[] = $t_rel->getId();
                            }
                        } else {
                            $this->repetitive_task_related_edit($t_rel, $task_data);
                            $edited_tasks[] = $t_rel->getId();
                        }
                    }

                    return $edited_tasks;
                    break;
                case "delete":
                    $delete_task = array();
                    foreach ($task_related as $t_rel) {
                        $task_rel = Objects::findObject($t_rel->getId());
                        if ($type_related == "news") {
                            if ($task->getStartDate() <= $t_rel->getStartDate() && $task->getDueDate() <= $t_rel->getDueDate()) {
                                $delete_task[] = $t_rel->getId();
                                $task_rel->trash();
                            }
                        } else {
                            $delete_task[] = $t_rel->getId();
                            $task_rel->trash();
                        }
                    }
                    return $delete_task;
                    break;
                case "archive":
                    $archive_task = array();
                    foreach ($task_related as $t_rel) {
                        $task_rel = Objects::findObject($t_rel->getId());
                        if ($type_related == "news") {
                            if ($task->getStartDate() <= $t_rel->getStartDate() && $task->getDueDate() <= $t_rel->getDueDate()) {
                                $archive_task[] = $t_rel->getId();
                                $t_rel->archive();
                            }
                        } else {
                            $archive_task[] = $t_rel->getId();
                            $t_rel->archive();
                        }
                    }
                    return $archive_task;
                    break;
            }
        }
    }

    function setCorrectStartAndDueDates($task, &$task_data) {

        if ($task_data['previous_sd'] instanceof DateTimeValue && $task_data['new_sd'] instanceof DateTimeValue) {

            if ($task_data['new_sd']->getTimestamp() > 0) {
                $diff = abs($task_data['previous_sd']->getTimestamp() - $task_data['new_sd']->getTimestamp());

                if ($task_data['previous_sd'] > $task_data['new_sd']) {
                    $processed_date = $task->getStartDate()->getTimestamp() - $diff;
                } else {
                    $processed_date = $task->getStartDate()->getTimestamp() + $diff;
                }

                $datetime_aux = new DateTimeValue($processed_date);

                $task_data['start_date'] = $datetime_aux;
            } else {
                $task_data['start_date'] = null;
            }
        }


        if ($task_data['previous_dd'] instanceof DateTimeValue && $task_data['new_dd'] instanceof DateTimeValue) {

            if ($task_data['new_dd']->getTimestamp() > 0) {

                $diff = abs($task_data['previous_dd']->getTimestamp() - $task_data['new_dd']->getTimestamp());

                if ($task_data['previous_dd'] > $task_data['new_dd']) {
                    $processed_date = $task->getDueDate()->getTimestamp() - $diff;
                } else {
                    $processed_date = $task->getDueDate()->getTimestamp() + $diff;
                }

                $datetime_aux = new DateTimeValue($processed_date);

                $task_data['due_date'] = $datetime_aux;
            } else {
                $task_data['due_date'] = null;
            }
        }
    }

    function repetitive_task_related_edit($task, $task_data) {
        //calculate time changed in original task and apply in related tasks
        $this->setCorrectStartAndDueDates($task, $task_data);

        $task->setFromAttributes($task_data);

        $totalMinutes = ((int)array_var($task_data, 'time_estimate_hours') * 60) + (int)(array_var($task_data, 'time_estimate_minutes'));
        $task->setTimeEstimate($totalMinutes);

        if ($task->getParentId() > 0 && $task->hasChild($task->getParentId())) {
            flash_error(lang('task child of child error'));
            ajx_current("empty");
            return;
        }

        $task->save();

        $task->setObjectName(array_var($task_data, 'name'));
        $task->save();

        // dependencies
        if (config_option('use tasks dependencies')) {
            $previous_tasks = array_var($task_data, 'previous');
            if (is_array($previous_tasks)) {
                foreach ($previous_tasks as $ptask) {
                    if ($ptask == $task->getId())
                        continue;
                    $dep = ProjectTaskDependencies::instance()->findById(array('previous_task_id' => $ptask, 'task_id' => $task->getId()));
                    if (!$dep instanceof ProjectTaskDependency) {
                        $dep = new ProjectTaskDependency();
                        $dep->setPreviousTaskId($ptask);
                        $dep->setTaskId($task->getId());
                        $dep->save();
                    }
                }

                $saved_ptasks = ProjectTaskDependencies::instance()->findAll(array('conditions' => 'task_id = ' . $task->getId()));
                foreach ($saved_ptasks as $pdep) {
                    if (!in_array($pdep->getPreviousTaskId(), $previous_tasks))
                        $pdep->delete();
                }
            } else {
                ProjectTaskDependencies::instance()->delete('task_id = ' . $task->getId());
            }
        }

        // Add assigned user to the subscibers list
        if ($task->getAssignedToContactId() > 0 && Contacts::instance()->findById($task->getAssignedToContactId())) {
            if (!isset($_POST['subscribers']))
                $_POST['subscribers'] = array();
            $_POST['subscribers']['user_' . $task->getAssignedToContactId()] = '1';
        }
        $notify_subscribers = user_config_option("can notify subscribers");

        $object_controller = new ObjectController();
        $object_controller->add_to_members($task, array_var($task_data, 'members'));
        $object_controller->add_subscribers($task, null, true, $notify_subscribers);
        $object_controller->link_to_new_object($task);
        $object_controller->add_custom_properties($task);
        $object_controller->add_reminders($task);

        // apply values to subtasks
        $assigned_to = $task->getAssignedToContactId();
        $subtasks = $task->getAllSubTasks();
        $milestone_id = $task->getMilestoneId();
        $apply_ms = array_var($task_data, 'apply_milestone_subtasks');
        $apply_at = array_var($task_data, 'apply_assignee_subtasks', '');
        foreach ($subtasks as $sub) {
            $modified = false;
            if ($apply_at || !($sub->getAssignedToContactId() > 0)) {
                $sub->setAssignedToContactId($assigned_to);
                $modified = true;
            }
            if ($apply_ms) {
                $sub->setMilestoneId($milestone_id);
                $modified = true;
            }
            if ($modified) {
                $sub->save();
            }
        }

        $task->resetIsRead();
        Hook::fire('after_task_repetition_edited', array('task' => $task), $task);
        ApplicationLogs::createLog($task, ApplicationLogs::ACTION_EDIT);
    }

    function check_related_task() {
        ajx_current("empty");
        //I find all those related to the task to find out if the original
        $task_related = ProjectTasks::findByRelated(array_var($_REQUEST, 'related_id'));

        $can_manage_repetitive_properties_of_tasks = SystemPermissions::userHasSystemPermission(logged_user(), 'can_manage_repetitive_properties_of_tasks');

        if ($can_manage_repetitive_properties_of_tasks) {
            if (!$task_related) {

                $task_related = ProjectTasks::instance()->findById(array_var($_REQUEST, 'related_id'));
                //is not the original as the original look plus other related
                if ($task_related->getOriginalTaskId() != "0") {
                    ajx_extra_data(array("status" => true));
                } else {
                    ajx_extra_data(array("status" => false));
                }
            } else {
                ajx_extra_data(array("status" => true));
            }
        } else {
            ajx_extra_data(array("status" => false));
        }
    }

    function correct_days_task_repetitive($date) {
        if ($date != "") {
            $working_days = explode(",", config_option("working_days"));
            if (!in_array(date("w", $date->getTimestamp()), $working_days)) {
                $date = $date->add('d', 1);
                $this->correct_days_task_repetitive($date);
            }
        }
        return $date;
    }

    function change_mark_as_started() {
        if (logged_user()->isGuest()) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        }

        if (isset($_GET['id'])) {
            $task = ProjectTasks::instance()->findById($_GET['id']);
        } else {
            $task = ProjectTasks::instance()->findById(get_id());
        }

        if (!($task instanceof ProjectTask)) {
            flash_error(lang('task dnx'));
            return;
        } // if

        if (!$task->canEdit(logged_user())) {
            flash_error(lang('no access permissions'));
            ajx_current("empty");
            return;
        }
        //if


        try {
        	// to use when saving the application log
        	$old_content_object = $task->generateOldContentObjectData();
        	
            DB::beginWork();

            $task->changeMarkAsStarted();

            DB::commit();
            ApplicationLogs::createLog($task, ApplicationLogs::ACTION_EDIT, false, false, true);
            if ($task->getMarkAsStarted()) {
                flash_success(lang('success mark as started task'));
            } else {
                flash_success(lang('success unmark as started task'));
            }

            ajx_extra_data(array("task" => $task->getArrayInfo()));

            if (array_var($_GET, 'quick', false)) {
                ajx_current("empty");
            } else {
                ajx_current("reload");
            }
        } catch (Exception $e) {
            DB::rollback();
            flash_error($e->getMessage());
        } // try
    }
    
    
    
    function render_task_work_performed_summary() { 
    	ajx_current("empty");
    	$html = '';
    	
    	$object = ProjectTasks::instance()->findById(get_id());
    	if ($object instanceof ProjectTask) {
    		tpl_assign('object', $object);
    		tpl_assign('show_timeslot_section', true);
    		$html = tpl_fetch(get_template_path('work_performed', 'task'));
    	}
    	ajx_extra_data(array('html' => $html));
    }


    function render_task_financials_summary() { 
    	ajx_current("empty");
    	$html = '';
    	
    	$object = ProjectTasks::instance()->findById(get_id());
    	if ($object instanceof ProjectTask && Plugins::instance()->isActivePlugin('advanced_billing')) {
    		Env::useHelper('functions', 'advanced_billing');
            $html = get_task_estimated_executed_view_info($object);
    	}
    	ajx_extra_data(array('html' => $html));
    }
    
    
    /**
     * Resets the tasks list filters so it can be reloaded from outside (e.g.: a widget)
     */
    function set_task_list_filters_to_reload() {
    	
    	ajx_current("empty");
    	
    	$status_id = array_var($_REQUEST, 'status_id', '0');
    	
    	set_user_config_option('task panel filter', 'no_filter', logged_user()->getId());
    	set_user_config_option('task panel status', $status_id, logged_user()->getId());
    	
    }
    
}

// TaskController
