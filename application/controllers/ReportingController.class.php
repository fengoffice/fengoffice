<?php

/**
 * Controller that is responsible for handling project events related requests
 *
 * @version 1.0
 * @author Marcos Saiz <marcos.saiz@gmail.com>
 * @adapted from Reece calendar <http://reececalendar.sourceforge.net/>.
 * Acknowledgements at the bottom.
 */

class ReportingController extends ApplicationController {

	/**
	 * Construct the ReportingController
	 *
	 * @access public
	 * @param void
	 * @return ReportingController
	 */
	function __construct()
	{
		parent::__construct();
		prepare_company_website_controller($this, 'website');
		Env::useHelper('grouping');
	} // __construct

	function chart_details()
	{
		$pcf = new ProjectChartFactory();
		$chart = $pcf->loadChart(get_id());
		$chart->ExecuteQuery();
		tpl_assign('chart', $chart);
		ajx_set_no_toolbar(true);
	}

	function init() {
		require_javascript("og/ReportingManager.js");
		ajx_current("panel", "reporting");
		ajx_replace(true);
	}
	
	/**
	 * Show reporting index page
	 *
	 * @param void
	 * @return null
	 */
	function add_chart() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$factory = new ProjectChartFactory();
		$types = $factory->getChartTypes();

		$chart_data = array_var($_POST, 'chart');
		if(!is_array($chart_data)) {
			$chart_data = array(
				'type_id' => 1,
				'display_id' => 20,
				'show_in_project' => 1,
				'show_in_parents' => 0
			); // array
		} // if
		tpl_assign('chart_data', $chart_data);


		if (is_array(array_var($_POST, 'chart'))) {
			$project = Projects::findById(array_var($chart_data, 'project_id'));
			if (!$project instanceof Project) {
				flash_error(lang('project dnx'));
				ajx_current("empty");
				return;
			}
			$chart = $factory->getChart(array_var($chart_data, 'type_id'));
			$chart->setDisplayId(array_var($chart_data, 'display_id'));
			$chart->setTitle(array_var($chart_data, 'title'));

			if (array_var($chart_data, 'save') == 1){
				$chart->setFromAttributes($chart_data);

				try {
					DB::beginWork();
					$chart->save();
					$chart->setProject($project);
					DB::commit();
					flash_success(lang('success add chart', $chart->getTitle()));
					ajx_current('back');
				} catch(Exception $e) {
					DB::rollback();
					flash_error($e->getMessage());
					ajx_current("empty");
				}
				return;
			}

			$chart->ExecuteQuery();
			tpl_assign('chart', $chart);
			ajx_replace(true);
		}
		tpl_assign('chart_displays', $factory->getChartDisplays());
		tpl_assign('chart_list', $factory->getChartTypes());
	}

	function delete_chart() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$chart = ProjectCharts::findById(get_id());
		if(!($chart instanceof ProjectChart)) {
			flash_error(lang('chart dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$chart->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try {

			DB::beginWork();
			$chart->trash();
			DB::commit();
			ApplicationLogs::createLog($chart, ApplicationLogs::ACTION_TRASH);
			
			flash_success(lang('success deleted chart', $chart->getTitle()));
			ajx_current("back");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete chart'));
			ajx_current("empty");
		} // try
	}

	/**
	 * Show reporting add chart page
	 *
	 * @param void
	 * @return null
	 */
	function index()
	{           
            /**
                * Allow to override views when ajax request is sent
            */
            Hook::fire('override_action_view', $this, $ret);
            ajx_set_no_toolbar(true);
            
	}

	function list_all()
	{
		ajx_current("empty");
	}



	// ---------------------------------------------------
	//  Tasks Reports
	// ---------------------------------------------------

	function total_task_times_p(){
		//set params from config options
		$report_data['date_type'] = user_config_option('timeReportDate');
			
		$now_date = DateTimeValueLib::now();
		if (strtotime(user_config_option('timeReportDateStart'))){//this return null if date is 0000-00-00 00:00:00
			$report_data['start_value'] = user_config_option('timeReportDateStart');
		}else{
			$dateStart = format_date($now_date, DATE_MYSQL, 0);//today
			$report_data['start_value'] = $dateStart;
		}
		
		if (strtotime(user_config_option('timeReportDateEnd'))){//this return null if date is 0000-00-00 00:00:00
			$report_data['end_value'] = user_config_option('timeReportDateEnd');
		}else{
			$dateEnd = format_date($now_date, DATE_MYSQL, 0);//today
			$report_data['end_value'] = $dateEnd;
		}
					
		$report_data['user'] = user_config_option('timeReportPerson');
		$report_data['timeslot_type'] = user_config_option('timeReportTimeslotType');
		
		$report_data['show_estimated_time'] = user_config_option('timeReportShowEstimatedTime');
		
		$group = explode( ',', user_config_option('timeReportGroupBy') );
		$report_data['group_by_1'] = array_var($group, 0);
		$report_data['group_by_2'] = array_var($group, 1);
		$report_data['group_by_3'] = array_var($group, 2);
		
		$altGroup = explode( ',', user_config_option('timeReportAltGroupBy') );
		$report_data['alt_group_by_1'] = array_var($altGroup, 0);
		$report_data['alt_group_by_2'] = array_var($altGroup, 1);
		$report_data['alt_group_by_3'] = array_var($altGroup, 2);
		
		$report_data['show_billing'] = user_config_option('timeReportShowBilling');
		$report_data['show_cost'] = user_config_option('timeReportShowCost');
		
		$report_data['task_status'] = user_config_option('timeReportTaskStatus');
		
		$cp_ids = CustomProperties::getCustomPropertyIdsByObjectType(ProjectTasks::instance()->getObjectTypeId());
		tpl_assign('has_custom_properties', count($cp_ids) > 0);
		
		$sel_member_ids = active_context_members(false);
		if (count($sel_member_ids) == 0) {
			$users = Contacts::getAllUsers();
		} else {
			$users = allowed_users_in_context(Timeslots::instance()->getObjectTypeId(), active_context(), ACCESS_LEVEL_READ, "", false, true);
		}
		$_SESSION['total_task_times_report_data'] = $report_data;
		tpl_assign('report_data', $report_data);
		tpl_assign('users', $users);
		tpl_assign('has_billing', BillingCategories::count() > 0 || Plugins::instance()->isActivePlugin('advanced_billing'));
	}
	
	function total_task_times_print(){
		if (!isset($_REQUEST['exportCSV'])) {
			$this->setLayout("html");
			$this->setTemplate('report_printer');
		}

		if (isset($_POST['post'])) {
			$report_data = json_decode(str_replace("'",'"', array_var($_POST, 'post')),true);
		} else {
			$report_data = $_POST;
		}
                
		$this->total_task_times($report_data, null, true);
	}
	
	function total_task_times($report_data = null, $task = null, $csv = null){
		if (!$report_data) {
			$report_data = array_var($_POST, 'report');
			set_user_config_option('timeReportDate', $report_data['date_type'] , logged_user()->getId());
			
			$dateStart = getDateValue($report_data['start_value']);
			if ($dateStart instanceof DateTimeValue) {
				set_user_config_option('timeReportDateStart', $dateStart , logged_user()->getId());
			}
			
			$dateEnd = getDateValue($report_data['end_value']);
			if ($dateEnd instanceof DateTimeValue) {
				set_user_config_option('timeReportDateEnd', $dateEnd , logged_user()->getId());
			}
			
			set_user_config_option('timeReportShowEstimatedTime', array_var($report_data, 'show_estimated_time') == 'checked', logged_user()->getId());
			
			set_user_config_option('timeReportPerson', $report_data['user'] , logged_user()->getId());
			set_user_config_option('timeReportTimeslotType', $report_data['timeslot_type'] , logged_user()->getId());
			set_user_config_option('timeReportShowBilling', isset($report_data['show_billing']) ? 1:0 , logged_user()->getId());
			set_user_config_option('timeReportShowCost', isset($report_data['show_cost']) ? 1:0 , logged_user()->getId());
			set_user_config_option('timeReportTaskStatus', $report_data['task_status'] , logged_user()->getId());
			
			$group = $report_data['group_by_1'].", ".$report_data['group_by_2'].", ".$report_data['group_by_3'];
			$altGroup = $report_data['alt_group_by_1'].",".$report_data['alt_group_by_2'].",".$report_data['alt_group_by_3'];
			
			set_user_config_option('timeReportGroupBy', $group , logged_user()->getId());
			set_user_config_option('timeReportAltGroupBy', $altGroup , logged_user()->getId());
			
			$_SESSION['total_task_times_report_data'] = $report_data;
		}
		
		//Logger::log_r($report_data);
		
		if (array_var($_GET, 'export') == 'csv' || (isset($csv) && $csv == true)){
			$context = build_context_array(array_var($_REQUEST, 'context'));
			CompanyWebsite::instance()->setContext($context);
			if (!$report_data) {
				if (isset($_REQUEST['parameters'])) $report_data = json_decode(str_replace("'",'"', $_REQUEST['parameters']), true);
				else $report_data = $_REQUEST;
			}
			tpl_assign('context', $context);
			$this->setTemplate('total_task_times_csv');
		} else {
			$context = active_context();
		}
		
		$columns = array_var($report_data, 'columns');
		if (!is_array($columns)) $columns = array_var($_POST, 'columns', array());
									
		asort($columns); //sort the array by column order
		foreach($columns as $column => $order){
			if ($order > 0) {
				$newColumn = new ReportColumn();
				//$newColumn->setReportId($newReport->getId());
				if(is_numeric($column)){
					$newColumn->setCustomPropertyId($column);
				}else{
					$newColumn->setFieldName($column);
				}				
			}
		}
	
		$user = Contacts::findById(array_var($report_data, 'user'));
		
		$now = DateTimeValueLib::now();
		$now->advance(logged_user()->getUserTimezoneValue(), true);
		switch (array_var($report_data, 'date_type')){
			case 1: //Today
				$st = DateTimeValueLib::make(0,0,0,$now->getMonth(),$now->getDay(),$now->getYear());
				$et = DateTimeValueLib::make(23,59,59,$now->getMonth(),$now->getDay(),$now->getYear());break;
			case 2: //This week
				$monday = $now->getMondayOfWeek();
				$nextMonday = $now->getMondayOfWeek()->add('w',1)->add('d',-1);
				$st = DateTimeValueLib::make(0,0,0,$monday->getMonth(),$monday->getDay(),$monday->getYear());
				$et = DateTimeValueLib::make(23,59,59,$nextMonday->getMonth(),$nextMonday->getDay(),$nextMonday->getYear());break;
			case 3: //Last week
				$monday = $now->getMondayOfWeek()->add('w',-1);
				$nextMonday = $now->getMondayOfWeek()->add('d',-1);
				$st = DateTimeValueLib::make(0,0,0,$monday->getMonth(),$monday->getDay(),$monday->getYear());
				$et = DateTimeValueLib::make(23,59,59,$nextMonday->getMonth(),$nextMonday->getDay(),$nextMonday->getYear());break;
			case 4: //This month
				$st = DateTimeValueLib::make(0,0,0,$now->getMonth(),1,$now->getYear());
				$et = DateTimeValueLib::make(23,59,59,$now->getMonth(),1,$now->getYear())->add('M',1)->add('d',-1);break;
			case 5: //Last month
				$now->add('M',-1);
				$st = DateTimeValueLib::make(0,0,0,$now->getMonth(),1,$now->getYear());
				$et = DateTimeValueLib::make(23,59,59,$now->getMonth(),1,$now->getYear())->add('M',1)->add('d',-1);break;
			case 6: //Date interval
				$st = getDateValue(array_var($report_data, 'start_value'));
				$st = $st->beginningOfDay();
				
				$et = getDateValue(array_var($report_data, 'end_value'));
				$et = $et->endOfDay();
				break;
		}
		
		if ($st instanceof DateTimeValue) {
			$st->add('s',-logged_user()->getUserTimezoneValue());
		}
		if ($et instanceof DateTimeValue) {
			$et->add('s',-logged_user()->getUserTimezoneValue());
		}
				
		$timeslotType = array_var($report_data, 'timeslot_type', 0);
		$group_by = array();
		for ($i = 1; $i <= 3; $i++){
			if ($timeslotType == 0)
				$gb = array_var($report_data, 'group_by_' . $i);
			else
				$gb = array_var($report_data, 'alt_group_by_' . $i);

			if ($gb != '0') $group_by[] = $gb;
		}
		
		$dateFormat = user_config_option('date_format');
		$date_format_tip = date_format_tip($dateFormat);
		
		$extra_conditions = "";
		$conditions = array_var($_POST, 'conditions', array());
		foreach ($conditions as $cond) {
			if ($cond['deleted'] > 0) continue;
			if (array_var($cond, 'custom_property_id') > 0) {
				if (!in_array($cond['condition'], array('like', 'not like', '=', '<=', '>=', '<', '>', '<>', '%'))) continue;
				
				$cp = CustomProperties::getCustomProperty($cond['custom_property_id']);
				if (!$cp instanceof CustomProperty) continue;
				
				$current_condition = ' AND e.rel_object_id IN ( SELECT object_id as id FROM '.TABLE_PREFIX.'custom_property_values cpv WHERE cpv.custom_property_id = '.$cond['custom_property_id'];
				
				$value = $cond['value'];
				
				if($cond['condition'] == 'like' || $cond['condition'] == 'not like'){
					$value = '%'.$cond['value'].'%';
				}
				if ($cp->getType() == 'date') {
					if ($value == $date_format_tip) continue;
					$dtValue = DateTimeValueLib::dateFromFormatAndString($dateFormat, $value);
					$value = $dtValue->format('Y-m-d H:i:s');
				}
				if($cond['condition'] != '%'){
					if ($cp->getType() == 'numeric') {
						$current_condition .= ' AND cpv.value '.$cond['condition'].' '.DB::escape($value);
					}else if ($cp->getType() == 'boolean') {
						$current_condition .= ' AND cpv.value '.$cond['condition'].' '.($value ? '1' : '0');
						if (!$value) {
							$current_condition .= ') OR o.id NOT IN (SELECT object_id as id FROM '.TABLE_PREFIX.'custom_property_values cpv2 WHERE cpv2.object_id=o.id AND cpv2.value=1 AND cpv2.custom_property_id = '.$cp->getId();
						}
					}else{
						$current_condition .= ' AND cpv.value '.$cond['condition'].' '.DB::escape($value);
					}
				}else{
					$current_condition .= ' AND cpv.value like '.DB::escape("%$value");
				}
				$current_condition .= ')';
				$extra_conditions .= $current_condition;
				
			}
		}

		$timeslot_type = array_var($report_data, 'timeslot_type');
		
		if ($timeslotType != 1) {
			$task_status = array_var($report_data, 'task_status', 'all');
			if ($task_status == 'pending') {
				$extra_conditions .= " AND IF(e.rel_object_id=0, true, (SELECT pt.completed_by_id FROM ".TABLE_PREFIX."project_tasks pt WHERE pt.object_id=e.rel_object_id)=0) ";
			} else if ($task_status == 'completed') {
				$extra_conditions .= " AND IF(e.rel_object_id=0, true, (SELECT pt.completed_by_id FROM ".TABLE_PREFIX."project_tasks pt WHERE pt.object_id=e.rel_object_id)>0) ";
			}
		}

		if ($timeslot_type != 1) {
			$task_status = lang(array_var($report_data, 'task_status', 'all'));
		} else {
			$task_status = null;
		}


		
		$timeslots = Timeslots::getTaskTimeslots($context, null, $user, $st, $et, array_var($report_data, 'task_id', 0), $group_by, null, null, null, $timeslotType, $extra_conditions);
		
		$unworkedTasks = null;
		if (array_var($report_data, 'include_unworked') == 'checked') {
			$unworkedTasks = ProjectTasks::getPendingTasks(logged_user(), $workspace);
			tpl_assign('unworkedTasks', $unworkedTasks);
		}
		
		
		$gb_criterias = array();
		foreach ($group_by as $text) {
			if (in_array($text, array('contact_id', 'rel_object_id'))) $gb_criterias[] = array('type' => 'column', 'value' => $text);
			else if (in_array($text, array('milestone_id', 'priority'))) $gb_criterias[] = array('type' => 'assoc_obj', 'fk' => 'rel_object_id', 'value' => $text);
			else if (str_starts_with($text, 'dim_')) $gb_criterias[] = array('type' => 'dimension', 'value' => str_replace_first('dim_', '', $text));
			else if ($text == 'first_level_task_only') $gb_criterias[] = array('type' => 'first_level_task_only', 'value' => $text);
		}
		$grouped_timeslots = groupObjects($gb_criterias, $timeslots);
		
		switch($timeslot_type){
			case 0:
				$timeslot_type = lang('task timeslots');
				break;
			case 1:
				$timeslot_type = lang('time timeslots');
				break;
			case 2:
				$timeslot_type = lang('all timeslots');
				break;
		}

		$timeslots_grouped_by = array();
		if ($timeslot_type == lang('task timeslots')){
			$gbs = array();
			Hook::fire('total_task_timeslots_group_by_criterias', null, $gbs);
			$group_names = array(array_var($report_data, 'group_by_1'), array_var($report_data, 'group_by_2'), array_var($report_data, 'group_by_3'));
			foreach($group_names as $group_name){
				if($group_name == 'rel_object_id') {
					$timeslots_grouped_by[] = lang('task');
				} elseif($group_name == 'contact_id') {
					$timeslots_grouped_by[] = lang('user');
				} elseif($group_name == 'priority') {
					$timeslots_grouped_by[] = lang('priority');
				} elseif($group_name == 'milestone_id') {
					$timeslots_grouped_by[] = lang('milestone');
				} else {
					foreach($gbs as $gb) {
						if($group_name == $gb['val']){
							$timeslots_grouped_by[] = $gb['name'];
						}
					}
				}
			}

		} elseif($timeslot_type == lang('time timeslots') || $timeslot_type == lang('all timeslots')) {
			$gbs = array();
			Hook::fire('total_task_timeslots_group_by_criterias', null, $gbs);
			$group_names = array(array_var($report_data, 'alt_group_by_1'), array_var($report_data, 'alt_group_by_2'), array_var($report_data, 'alt_group_by_3'));
			foreach($group_names as $group_name){
				if($group_name == 'rel_object_id') {
					$timeslots_grouped_by[] = lang('task');
				} elseif ($group_name == 'contact_id') {
					$timeslots_grouped_by[] = lang('user');
				} elseif ($group_name == 'first_level_task_only') {
					$timeslots_grouped_by[] = lang('first level task only');
				} else {
					foreach($gbs as $gb) {
						if($group_name == $gb['val']){
							$timeslots_grouped_by[] = $gb['name'];
						}
					}
				}
			}
		}
		$timeslots_grouped_by = implode(", ", $timeslots_grouped_by);

		tpl_assign('columns', $columns);
		tpl_assign('grouped_timeslots', $grouped_timeslots);
		if (array_var($report_data, 'date_type') == 6) {
			$st->advance(logged_user()->getUserTimezoneValue(), true);
			$et->advance(logged_user()->getUserTimezoneValue(), true);
		}
		tpl_assign('start_time', $st);
		tpl_assign('end_time', $et);
		tpl_assign('user', $user);
		tpl_assign('timeslots_grouped_by', $timeslots_grouped_by);
		tpl_assign('task_status', $task_status);
		tpl_assign('timeslot_type', $timeslot_type);
		tpl_assign('post', $report_data);
		tpl_assign('title', lang('task time report'));
		tpl_assign('allow_export', false);
		if (array_var($_GET, 'export') == 'csv' || (isset($csv) && $csv == true)) {
			
			$filename = $this->total_task_times_csv_export($grouped_timeslots);
			ajx_extra_data(array('filename' => "$filename.csv"));
			ajx_current("empty");
			
		}else{
			tpl_assign('template_name', 'total_task_times');
			$this->setTemplate('report_wrapper');
		}
	}

	function total_task_times_by_task_print(){
		$this->setLayout("html");

		$task = ProjectTasks::findById(get_id());

		$st = DateTimeValueLib::make(0,0,0,1,1,1900);
		$et = DateTimeValueLib::make(23,59,59,12,31,2036);

		$timeslotsArray = Timeslots::getTaskTimeslots(active_context(), null,null,$st,$et, get_id());

		tpl_assign('columns', array());
		tpl_assign('user', array());
		tpl_assign('group_by', array());
		tpl_assign('grouped_timeslots', array());
		tpl_assign('template_name', 'total_task_times');
		tpl_assign('estimate', $task->getTimeEstimate());
		tpl_assign('timeslotsArray', $timeslotsArray);
		tpl_assign('title',lang('task time report'));
		tpl_assign('task_title', $task->getTitle());
		tpl_assign('start_time', $st);
		tpl_assign('end_time', $et);
		$this->setTemplate('report_printer');
	}


	function total_task_times_vs_estimate_comparison_p(){
		$users = owner_company()->getContacts();
		$workspaces = logged_user()->getActiveProjects();

		tpl_assign('workspaces', $workspaces);
		tpl_assign('users', $users);
	}

	function total_task_times_vs_estimate_comparison($report_data = null, $task = null){
		$this->setTemplate('report_wrapper');

		if (!$report_data)
		$report_data = array_var($_POST, 'report');

		$start = getDateValue(array_var($report_data, 'start_value'));
		$end = getDateValue(array_var($report_data, 'end_value'));

		$st = $start->beginningOfDay();
		$et = $end->endOfDay();
		$st = new DateTimeValue($st->getTimestamp() - logged_user()->getUserTimezoneValue());
		$et = new DateTimeValue($et->getTimestamp() - logged_user()->getUserTimezoneValue());

//		$timeslots = Timeslots::getTimeslotsByUserWorkspacesAndDate($st, $et, 'ProjectTasks', null, $workspacesCSV, array_var($report_data, 'task_id',0));
		$timeslots = array();

		tpl_assign('timeslots', $timeslots);
		tpl_assign('start_time', $st);
		tpl_assign('end_time', $et);
		tpl_assign('user', $user);
		tpl_assign('post', $report_data);
		tpl_assign('template_name', 'total_task_times');
		tpl_assign('title',lang('task time report'));
	}

	
	
	
	// ---------------------------------------------------
	//  Custom Reports
	// ---------------------------------------------------

	function add_custom_report(){
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		tpl_assign('url', get_url('reporting', 'add_custom_report'));
		$report_data = array_var($_POST, 'report');
		if(is_array($report_data)){
			foreach ($report_data as $k => &$v) {
				$v = remove_scripts($v);
			}
			tpl_assign('report_data', $report_data);
			$conditions = array_var($_POST, 'conditions');
			if(!is_array($conditions)) {
				$conditions = array();
			}
			tpl_assign('conditions', $conditions);
			$columns = array_var($_POST, 'columns');
			if(is_array($columns) && count($columns) > 0){
				tpl_assign('columns', $columns);
				$newReport = new Report();
				
				$member_ids = json_decode(array_var($_POST, 'members'));
				
				$notAllowedMember = '';
				if(!logged_user()->isManager() && !logged_user()->isAdminGroup() && !$newReport->canAdd(logged_user(), active_context(), $notAllowedMember )) {
					if (str_starts_with($notAllowedMember, '-- req dim --')) flash_error(lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in)));
					else trim($notAllowedMember) == "" ? flash_error(lang('you must select where to keep', lang('the report'))) : flash_error(lang('no context permissions to add', lang("report"), $notAllowedMember ));
					ajx_current("empty");
					return;
				}
				
				$newReport->setObjectName($report_data['name']);
				$newReport->setDescription($report_data['description']);
				$newReport->setReportObjectTypeId($report_data['report_object_type_id']);
				$newReport->setOrderBy($report_data['order_by']);
				$newReport->setIsOrderByAsc($report_data['order_by_asc'] == 'asc');
				$newReport->setIgnoreContext(array_var($report_data, 'ignore_context') == 'checked');
                                
				try{
					DB::beginWork();
					$newReport->save();
					$allowed_columns = $this->get_allowed_columns($report_data['report_object_type_id'], true);
					foreach($conditions as $condition){
						if($condition['deleted'] == "1") continue;
						foreach ($allowed_columns as $ac){
							if ($condition['field_name'] == $ac['id']){
								$newCondition = new ReportCondition();
								$newCondition->setFromAttributes($condition);
								$newCondition->setReportId($newReport->getId());
								
								$condValue = array_key_exists('value', $condition) ? $condition['value'] : '';
								if($condition['field_type'] == 'boolean'){
								    $newCondition->setValue(array_key_exists('value', $condition) ? $condition['value'] : '0');
								}else if($condition['field_type'] == 'date' || $condition['field_type'] == 'datetime'){
									if ($condValue != '') {
										$dtFromWidget = DateTimeValueLib::dateFromFormatAndString(user_config_option('date_format'), $condValue);

										if($condition['field_type'] == 'date'){
										$newCondition->setValue(date("m/d/Y", $dtFromWidget->getTimestamp()));
                                        }elseif ($condition['field_type'] == 'datetime'){
                                            $newCondition->setValue(date("m/d/Y H:i:s", $dtFromWidget->getTimestamp()));
                                        }
									}
								}else if($condition['field_type'] == 'date_range_time_range'){

									$from = getDateValue($condition['value']['from']);
									$to = getDateValue($condition['value']['to']);
									$from_time = getTimeValue($condition['value']['from_time']);
									$to_time = getTimeValue($condition['value']['to_time']);
									
									$from_str = $from instanceof DateTimeValue ? $from->toMySQL() : '';
									$to_str = $to instanceof DateTimeValue ? $to->toMySQL() : '';
									$ft = is_array($from_time) ? $from_time['hours'].':'.$from_time['mins'] : '';
									$tt = is_array($to_time) ? $to_time['hours'].':'.$to_time['mins'] : '';
									
									$newCondition->setValue(json_encode(array('from' => $from_str, 'to' => $to_str, 'from_time' => $ft, 'to_time' => $tt)));
									
								}else{
									$newCondition->setValue($condValue);
								}
								$newCondition->setIsParametrizable(isset($condition['is_parametrizable']));
								$newCondition->save();
							}
						}
					}
					
					asort($columns); //sort the array by column order
					foreach($columns as $column => $order){
						if ($order > 0) {
							$newColumn = new ReportColumn();
							$newColumn->setReportId($newReport->getId());
							if(is_numeric($column)){
								$newColumn->setCustomPropertyId($column);
							}else{
								$newColumn->setFieldName($column);
							}
							$newColumn->save();
						}
					}
					
					$no_need_to_add_to_members = count($member_ids) == 0 && (logged_user()->isManager() || logged_user()->isAdminGroup());
					if (!$no_need_to_add_to_members) {
						$object_controller = new ObjectController();
						if (!is_null($member_ids)) {
							$object_controller->add_to_members($newReport, $member_ids);
						}
					} else {
						$newReport->addToSharingTable();
					}
					
					DB::commit();
					flash_success(lang('custom report created'));
					ajx_current('back');
				}catch(Exception $e){
					DB::rollback();
					flash_error($e->getMessage());
					ajx_current("empty");
				}
			}
		}
		$selected_type = array_var($_GET, 'type', '');
		
		$types = array(array("", lang("select one")));
		$object_types = ObjectTypes::getAvailableObjectTypes();
		
		$object_types[] = ObjectTypes::findByName('timeslot');
		
		Hook::fire('custom_reports_object_types', array('object_types' => $object_types), $object_types);
		
		foreach ($object_types as $ot) {
		    
			if ( $ot->getType() == 'dimension_object' ){
			    $dimension_ids = DimensionObjectTypes::getDimensionIdsByObjectTypeId($ot->getId());
			    if ( count($dimension_ids) > 0 ){
			        $types[] = array($ot->getId(), Members::getTypeNameToShowByObjectType($dimension_ids[0], $ot->getId()) );
			    }			    
			}else{
			    $types[] = array($ot->getId(), lang($ot->getName()));
			}
		    
		}
		if ($selected_type != '')
			tpl_assign('allowed_columns', $this->get_allowed_columns($selected_type));
		
		tpl_assign('object_types', $types);
		tpl_assign('selected_type', $selected_type);
                
                
		$new_report = new Report();
		tpl_assign('object', $new_report);
	}

	function edit_custom_report(){
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$report_id = array_var($_GET, 'id');
		$report = Reports::getReport($report_id);

		if(!$report->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		if(is_array(array_var($_POST, 'report'))) {
			try{
				ajx_current("empty");
				$report_data = array_var($_POST, 'report');
				foreach ($report_data as $k => &$v) {
					$v = remove_scripts($v);
				}
				
				$member_ids = json_decode(array_var($_POST, 'members'));
				
				DB::beginWork();
				$report->setObjectName($report_data['name']);
				$report->setDescription($report_data['description']);
				$report->setReportObjectTypeId($report_data['report_object_type_id']);
				$report->setOrderBy($report_data['order_by']);
				$report->setIsOrderByAsc($report_data['order_by_asc'] == 'asc');
				$report->setIgnoreContext(array_var($report_data, 'ignore_context') == 'checked');
				$report->save();				
					
				$conditions = array_var($_POST, 'conditions');
				if (!is_array($conditions)) {
					$conditions = array();
				}
				
				foreach($conditions as $condition){
					$newCondition = new ReportCondition();
					if($condition['id'] > 0){
						$newCondition = ReportConditions::getCondition($condition['id']);
					}
					if($condition['deleted'] == "1"){
						$newCondition->delete();
						continue;
					}
					$newCondition->setFromAttributes($condition);
					$newCondition->setReportId($report_id);
					
					if($condition['field_type'] == 'boolean'){
					    $newCondition->setValue(array_key_exists('value', $condition) ? $condition['value'] : '0');
					}else if($condition['field_type'] == 'date' || $condition['field_type'] == 'datetime'){
						if (array_var($condition, 'value') == '') $newCondition->setValue('');
						else {
							$dtFromWidget = DateTimeValueLib::dateFromFormatAndString(user_config_option('date_format'), $condition['value']);

                            if($condition['field_type'] == 'date'){
							$newCondition->setValue(date("m/d/Y", $dtFromWidget->getTimestamp()));
                            }elseif ($condition['field_type'] == 'datetime'){
                                $newCondition->setValue(date("m/d/Y H:i:s", $dtFromWidget->getTimestamp()));
                            }
						}
						
					} else if($condition['field_type'] == 'date_range_time_range') {
						
						$from = getDateValue($condition['value']['from']);
						$to = getDateValue($condition['value']['to']);
						$from_time = getTimeValue($condition['value']['from_time']);
						$to_time = getTimeValue($condition['value']['to_time']);
						
						$from_str = $from instanceof DateTimeValue ? $from->toMySQL() : '';
						$to_str = $to instanceof DateTimeValue ? $to->toMySQL() : '';
						$ft = is_array($from_time) ? $from_time['hours'].':'.$from_time['mins'] : '';
						$tt = is_array($to_time) ? $to_time['hours'].':'.$to_time['mins'] : '';
						
						$newCondition->setValue(json_encode(array('from' => $from_str, 'to' => $to_str, 'from_time' => $ft, 'to_time' => $tt)));
						
					}else{
						$newCondition->setValue(isset($condition['value']) ? $condition['value'] : '');
					}
					$newCondition->setIsParametrizable(isset($condition['is_parametrizable']));
					$newCondition->save();
				}
				ReportColumns::delete('report_id = ' . $report_id);
				$columns = array_var($_POST, 'columns');
				
				asort($columns); //sort the array by column order
				foreach($columns as $column => $order){
					if ($order > 0) {
						$newColumn = new ReportColumn();
						$newColumn->setReportId($report_id);

						if (str_starts_with($column,'group_cp_')){
                            $list_of_terms = explode('_',$column);
                            $newColumn->setCustomPropertyId($list_of_terms[2]);
                            $newColumn->setFieldName($column);
                        }else{
                            if(is_numeric($column)){
                                $newColumn->setCustomPropertyId($column);
                            }else{
                                $newColumn->setFieldName($column);
                            }
                        }
                        $newColumn->save();
					}
				}
				
				$object_controller = new ObjectController();
				if (!is_null($member_ids) && count($member_ids) > 0) {
					$object_controller->add_to_members($report, $member_ids);
				}
					
				DB::commit();
				flash_success(lang('custom report updated'));
				ajx_current('back');
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		}else{
			$this->setTemplate('add_custom_report');
			tpl_assign('url', get_url('reporting', 'edit_custom_report', array('id' => $report_id)));
			if($report instanceof Report){
				tpl_assign('id', $report_id);
				$report_data = array(
					'name' => $report->getObjectName(),
					'description' => $report->getDescription(),
					'report_object_type_id' => $report->getReportObjectTypeId(),
					'order_by' => $report->getOrderBy(),
					'order_by_asc' => $report->getIsOrderByAsc(),
					'ignore_context' => $report->getIgnoreContext(),
				);
				tpl_assign('report_data', $report_data);
				$conditions = ReportConditions::getAllReportConditions($report_id);
				tpl_assign('conditions', $conditions);
				$columns = ReportColumns::getAllReportColumns($report_id);
				$colIds = array();
				foreach($columns as $col){
					if($col->getCustomPropertyId() > 0){
                        if (str_starts_with($col->getFieldName(),'group_cp_')){
                            $colIds[] = $col->getFieldName();
                        }else{
				    		$colIds[] = $col->getCustomPropertyId();
                        }
					}else{
						$colIds[] = $col->getFieldName();
					}
				}
				tpl_assign('columns', $colIds);
			}

			$selected_type = $report->getReportObjectTypeId();
			
			$types = array(array("", lang("select one")));
			$object_types = ObjectTypes::getAvailableObjectTypes();
			$object_types[] = ObjectTypes::findByName('timeslot');
			
			Hook::fire('custom_reports_object_types', array('object_types' => $object_types), $object_types);
			
			foreach ($object_types as $ot) {
				$types[] = array($ot->getId(), lang($ot->getName()));
			}
			
			tpl_assign('object_types', $types);
			tpl_assign('selected_type', $selected_type);
                        
			tpl_assign('object', $report);
			
			tpl_assign('allowed_columns', $this->get_allowed_columns($selected_type), true);
		}
	}

	function edit_default_report(){

		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$report_id = array_var($_GET, 'id');
		$report = Reports::getReport($report_id);

		if(!$report->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} 

		if(is_array(array_var($_POST, 'report'))) {
			try{
				ajx_current("empty");
				$report_data = array_var($_POST, 'report');
				foreach ($report_data as $k => &$v) {
					$v = remove_scripts($v);
				}

				DB::beginWork();
				$report->setObjectName($report_data['name']);
				$report->setDescription($report_data['description']);
				$report->save();
				
				DB::commit();
				flash_success(lang('custom report updated'));
				ajx_current('back');

			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} else {
			$this->setTemplate('edit_default_report');
			tpl_assign('url', get_url('reporting', 'edit_default_report', array('id' => $report_id)));
			if($report instanceof Report){
				tpl_assign('id', $report_id);
				$report_data = array(
					'name' => Localization::instance()->lang_exists($report->getObjectName()) ? lang($report->getObjectName()) : $report->getObjectName(),
					'description' => Localization::instance()->lang_exists($report->getDescription()) ? lang($report->getDescription()) : $report->getDescription(),
					'order_by' => $report->getOrderBy(),
					'order_by_asc' => $report->getIsOrderByAsc(),
					'ignore_context' => $report->getIgnoreContext(),
				);
				tpl_assign('report_data', $report_data);
			}
			tpl_assign('object', $report);
		}
	}

	function view_custom_report($report_id = null, $view_attributes = null, $params = null,$to_print = false) {
		
		if (!$report_id) {
			$report_id = array_var($_REQUEST, 'id');
		}
		
		if (array_var($_REQUEST, 'replace')) {
			ajx_replace();
		}
		$report = Reports::getReport($report_id);
		if (!$report instanceof Report) {
			flash_error("report dnx");
			ajx_current("empty");
			return;
		}
		tpl_assign('id', $report_id);
		
			
		$conditions = ReportConditions::getAllReportConditions($report_id);
		$paramConditions = array();
		foreach($conditions as $condition){
			if($condition->getIsParametrizable()){
				$paramConditions[] = $condition;
			}
		}
		
		$ot = ObjectTypes::findById($report->getReportObjectTypeId());
		if (class_exists($ot->getHandlerClass())) {
			eval('$managerInstance = ' . $ot->getHandlerClass() . "::instance();");
			$externalCols = $managerInstance->getExternalColumns();
			
			if ($ot->getName() == 'mail') {
				foreach ($externalCols as $k => $ecol) {
					if (in_array($ecol, array('to','cc','bcc','body_plain','body_html'))) unset($externalCols[$k]);
				}
			}
			
		} else {
			$externalCols = array();
		}
		
		$cps = CustomProperties::getAllCustomPropertiesByObjectType($report->getReportObjectTypeId());
		foreach ($cps as $cp) {
			if ($cp->getType() == 'contact' || $cp->getType() == 'user') {
				$externalCols[] = $cp->getId();
			}
		}
		
		tpl_assign('object_type', $ot);
		$externalFields = array();
		foreach($externalCols as $extCol){
			$externalFields[$extCol] = $this->get_ext_values($extCol, $report->getReportObjectTypeId());
		}
		if (!$params) {
			$params = array_var($_REQUEST, 'params');
		}
		if (count($paramConditions) > 0 && !$params) {
			
			$this->setTemplate('custom_report_parameters');
			tpl_assign('model', $report->getReportObjectTypeId());
			tpl_assign('title', $report->getObjectName());
			tpl_assign('description', $report->getDescription());
			tpl_assign('conditions', $paramConditions);
			tpl_assign('external_fields', $externalFields);
			
		} else {
			
			ajx_set_no_toolbar(true);
			
			$parametersUrl = '';
			if ($params) {
				if (!is_array($params)) $params = json_decode($params, true);
				foreach($params as $id => $value){
					$parametersUrl .= '&params['.$id.']='.$value;
				}
			}
			
			$offset = array_var($_REQUEST, 'offset', 0);
			$limit = array_var($_REQUEST, 'limit', 50);
			$order_by = array_var($_REQUEST, 'order_by', '');
			$order_by_asc = array_var($_REQUEST, 'order_by_asc', false);
			
			$results = Reports::executeReport($report_id, $params, $order_by, $order_by_asc, $offset, $limit);
			
			$ot = ObjectTypes::findById($report->getReportObjectTypeId());

			tpl_assign('results', $results);
			tpl_assign('model', $ot->getHandlerClass());
			tpl_assign('types', self::get_report_column_types($report_id));
			tpl_assign('post', $_REQUEST);
			tpl_assign('order_by', $order_by);
			tpl_assign('order_by_asc', $order_by_asc);
			tpl_assign('description', $report->getDescription());
			tpl_assign('conditions', $conditions);
			tpl_assign('parameters', $params);
			tpl_assign('disabled_params', array_var($_REQUEST, 'disabled_params'));
			tpl_assign('parameterURL', $parametersUrl);
			tpl_assign('id', $report_id);
			tpl_assign('to_print', $to_print);
			
			$this->setTemplate('report_wrapper');
			tpl_assign('template_name', 'view_custom_report');
			tpl_assign('title', $report->getObjectName());
			tpl_assign('genid', gen_id());
			
			if ($view_attributes) {
				foreach ($view_attributes as $k => $v) tpl_assign($k, $v);
			}
		
			ApplicationReadLogs::createLog($report, ApplicationReadLogs::ACTION_READ);
			
			return $results;
		}
	}
	
	
	function print_custom_report() {
		ajx_current("empty");
		set_time_limit(0);
		$old_memory_limit = ini_get('memory_limit');
		if (php_config_value_to_bytes($old_memory_limit) < 512 *1024*1024) {
			ini_set("memory_limit", "512M");
		}
		
		$report_id = array_var($_GET, 'id');

		$report = Reports::getReport($report_id);
		if (!$report instanceof Report) {
			flash_error("report dnx");
			return;
		}

		$filename = "report_print_".gen_id().".html";
		$filepath = ROOT . "/tmp/$filename";
		
		$params = array();
		$params_str = array_var($_REQUEST, 'params');
		if ($params_str) $params = json_decode(str_replace("'", '"',$params_str), true);
		
		$disabled_params = array_var($_REQUEST, 'disabled_params');
		
		$_GET['limit'] = 0;
		$_REQUEST['limit'] = 0;
		$results = $this->view_custom_report($report_id, null, $params);
		
		ob_start();
		custom_report_info_blocks(array('id' => $report_id, 'results' => $results, 'parameters' => $params, 'disabled_params' => $disabled_params));
		$html = ob_get_clean();
		
		$html .= report_table_html($results, $report, '', true);
		
		ajx_extra_data(array('html' => $html));
		
	}
	
	function export_custom_report_csv() {
		ajx_current("empty");
		set_time_limit(0);
		$old_memory_limit = ini_get('memory_limit');
		if (php_config_value_to_bytes($old_memory_limit) < 512 *1024*1024) {
			ini_set("memory_limit", "512M");
		}
		
		$report_id = array_var($_GET, 'id');
		
		$report = Reports::getReport($report_id);
		if (!$report instanceof Report) {
			flash_error("report dnx");
			return;
		}
		
		$filename = $report->getName().".csv";
		$filepath = ROOT . "/tmp/$filename";
		
		$params_str = array_var($_REQUEST, 'report_params');
		if ($params_str) $params = json_decode(str_replace("'", '"',$params_str), true);
		else $params = array();
		
		$_GET['limit'] = 0;
		$_REQUEST['limit'] = 0;
		$results = $this->view_custom_report($report_id, null, $params);
		
		$csv = report_table_csv($results, $report);
		
		file_put_contents($filepath, $csv);
		
		ajx_extra_data(array("filename" => $filename));
		
	}
	
	function export_custom_report_pdf() {
		ajx_current("empty");
		set_time_limit(0);
		$old_memory_limit = ini_get('memory_limit');
		if (php_config_value_to_bytes($old_memory_limit) < 512 *1024*1024) {
			ini_set("memory_limit", "512M");
		}
		
		$report_id = array_var($_GET, 'id');
		
		$report = Reports::getReport($report_id);
		if (!$report instanceof Report) {
			flash_error("report dnx");
			return;
		}
		
		$html_filename = gen_id().'pdf.html';
		$html_filepath = ROOT . "/tmp/$html_filename";
		
		$params = array();
		$params_str = array_var($_REQUEST, 'report_params');
		if ($params_str) $params = json_decode(str_replace("'", '"',$params_str), true);
		
		$disabled_params = array_var($_REQUEST, 'disabled_params');
		
		$_GET['limit'] = 0;
		$_REQUEST['limit'] = 0;
		$results = $this->view_custom_report($report_id, null, $params,true);
		
		// include all css
		$html = stylesheet_tag('website.css');
		$css_html = "";
		Hook::fire('additional_report_print_css', null, $css_html);
		if ($css_html) $html .= $css_html;
		
		// to avoid thead and tbody overlapping
		$html .= stylesheet_tag('og/pdf_export.css');
		
		// title html
		$html .= '<div class="report-print-header"><div class="title-container"><h1>'.clean($report->getName()).'</h1></div></div><div class="clear"></div>';
		
		ob_start();
		custom_report_info_blocks(array('id' => $report_id, 'results' => $results, 'parameters' => $params, 'disabled_params' => $disabled_params));
		$html .= ob_get_clean();
		
		// build html
		$html .= report_table_html($results, $report, '', true);
		file_put_contents($html_filepath, $html);
		
		// convert html to pdf
		$orientation = array_var($_REQUEST, 'pdfPageLayout') == 'L' ? 'Landscape' : 'Portrait';
		$page_size = array_var($_REQUEST, 'pdfPageSize');
		
		$pdf_data = convert_to_pdf($html, $orientation, str_replace(" ", "_", $report->getObjectName()), $page_size);
		$real_pdf_filename = $pdf_data['name'];
		
		ajx_extra_data(array("filename" => $real_pdf_filename, 'size' => $pdf_data['size']));
		
	}
	
	

	function view_custom_report_print(){
		$this->setLayout("html");
		set_time_limit(0);
		$params = json_decode(str_replace("'",'"', array_var($_POST, 'post')),true);
		$report_params = json_decode(str_replace("'",'"', array_var($_POST, 'report_params')),true);
				
		$report_id = array_var($_POST, 'id');
		$order_by = array_var($_POST, 'order_by');
		if(!isset($order_by)) $order_by = '';
		tpl_assign('order_by', $order_by);
		$order_by_asc = array_var($_POST, 'order_by_asc');
		if(!isset($order_by_asc)) $order_by_asc = true;
		tpl_assign('order_by_asc', $order_by_asc);
		$report = Reports::getReport($report_id);
		$limit = array_var($_POST, 'exportCSV') || array_var($_POST, 'exportPDF') ? -1 : 50;
		$results = Reports::executeReport($report_id, $report_params, $order_by, $order_by_asc, 0, $limit, true);
		if(isset($results['columns'])) tpl_assign('columns', $results['columns']);
		if(isset($results['rows'])) tpl_assign('rows', $results['rows']);
		tpl_assign('db_columns', $results['db_columns']);

		if(array_var($_POST, 'exportCSV')){
			$filename = $this->generateCSVReport($report, $results);
			ajx_current("empty");
			ajx_extra_data(array('filename' => $filename));
		}else if(array_var($_POST, 'exportPDF') && !is_exec_available()){
			$this->generatePDFReport($report, $results);
		}else{
			tpl_assign('types', self::get_report_column_types($report_id));
			tpl_assign('template_name', 'view_custom_report');
			tpl_assign('title', $report->getObjectName());
			$ot = ObjectTypes::findById($report->getReportObjectTypeId());
			tpl_assign('model', $ot->getHandlerClass());
			tpl_assign('description', $report->getDescription());
			$conditions = ReportConditions::getAllReportConditions($report_id);
			tpl_assign('conditions', $conditions);
			tpl_assign('parameters', $params);
			tpl_assign('id', $report_id);
			tpl_assign('to_print', true);
			
			if (array_var($_POST, 'exportPDF')) {
				$this->setLayout("empty");
				tpl_assign('save_html_in_file', true);
				$html_filename = ROOT.'/tmp/'.gen_id().'pdf.html';
				$pdf_filename = $report->getObjectName() . '.pdf';
				tpl_assign('html_filename', $html_filename);
				tpl_assign('pdf_filename', $pdf_filename);
				tpl_assign('orientation', array_var($_POST, 'pdfPageLayout') == 'L' ? 'Landscape' : 'Portrait');
				ob_start();
			}
			$this->setTemplate('report_printer');
		}
	}
	
	function download_file() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		
		if(array_var($_REQUEST, 'file_name')){
			$file_name = array_var($_REQUEST, 'file_name');
		}
		if(array_var($_REQUEST, 'file_type')){
			$file_type = array_var($_REQUEST, 'file_type');
		}
		if(array_var($_REQUEST, 'file_size')){
			$size = array_var($_REQUEST, 'file_size');
		}
		
		$file_path = ROOT."/tmp/".$file_name;
		if(!file_exists($file_path)) {
			exit;
		}
		
		if (!isset($size)) $size = filesize($file_path);
		
		//user current date
		$now_date = DateTimeValueLib::now();
		$now_date->advance(logged_user()->getUserTimezoneValue());
		$now = $now_date->format('Y-m-d_H:i:s');
		
		download_file($file_path, $file_type, $file_name, true, true, $size);
		
	//	unlink($file_path);
		
		die();
	}
	
	function generateCSVReport($report, $results){
		$contents = "";
		
		$ot = ObjectTypes::findById($report->getReportObjectTypeId());
		Hook::fire("report_header", $ot, $results['columns']);
		
		$types = self::get_report_column_types($report->getId());
		$filename = str_replace(' ', '_',$report->getObjectName()).date('_YmdHis');
		foreach($results['columns'] as $col){
			$contents .= $col.';';
		}
		$contents .= "\n";
		foreach($results['rows'] as $row) {
			$i = 0;
			foreach($row as $k => $value){
				if ($k == 'object_type_id') continue;
				$db_col = isset($results['columns'][$i]) && isset($results['db_columns'][$results['columns'][$i]]) ? $results['db_columns'][$results['columns'][$i]] : '';

				$value = str_replace(array("\r\n","\n","\r"), " ", html_to_text($value));
				$cell = format_value_to_print($db_col, $value, ($k == 'link'?'':array_var($types, $k)), array_var($row, 'object_type_id'), '', is_numeric(array_var($results['db_columns'], $k)) ? "Y-m-d" : user_config_option('date_format'));
				if (function_exists('mb_internal_encoding')) {
					$cell = iconv(mb_internal_encoding(),"UTF-8",html_entity_decode($cell ,ENT_COMPAT));
				}
				$contents .= $cell.';';
				$i++;
			}
			$contents .= "\n";
		}
		file_put_contents(ROOT."/tmp/$filename.csv", $contents);
		return "$filename.csv";
	}
	
	function generatePDFReport(Report $report, $results){
		
		$types = self::get_report_column_types($report->getId());
		$ot = ObjectTypes::findById($report->getReportObjectTypeId());
		if (class_exists($ot->getHandlerClass())) {
			eval('$managerInstance = ' . $ot->getHandlerClass() . "::instance();");
			$externalCols = $managerInstance->getExternalColumns();
		} else {
			$externalCols = array();
		}
		$filename = str_replace(' ', '_',$report->getObjectName()).date('_YmdHis');
		
		$actual_encoding = function_exists('mb_internal_encoding') ? mb_internal_encoding() : 'utf-8';
		
		Hook::fire("report_header", $ot, $results['columns']);
		
		$pageLayout = $_POST['pdfPageLayout'];
		$fontSize = $_POST['pdfFontSize'];
		include_once(LIBRARY_PATH . '/pdf/fpdf.php');
		$pdf = new FPDF($pageLayout);
		$pdf->setTitle($report->getObjectName());
		$pdf->AddPage();
		$pdf->SetFont('Arial','',$fontSize);
		$pdf->Cell(80);
		if (strtoupper($actual_encoding) == "UTF-8") {
			$report_title = html_entity_decode($report->getObjectName(), ENT_COMPAT);
		} else {
			if (function_exists('mb_internal_encoding')) {
				$report_title = iconv(mb_internal_encoding(), "UTF-8", html_entity_decode($report->getObjectName(), ENT_COMPAT));
			} else {
				$report_title = html_entity_decode($report->getObjectName(), ENT_COMPAT);
			}
		}
		$pdf->Cell(30, 10, $report_title);
		$pdf->Ln(20);
		$colSizes = array();
		$maxValue = array();
		$fixed_col_sizes = array();
		foreach($results['rows'] as $row) {
			$i = 0;
			array_shift ($row);
			foreach($row as $k => $value){
				if(!isset($maxValue[$i])) $maxValue[$i] = '';
				if(strlen(strip_tags($value)) > strlen($maxValue[$i])){
					$maxValue[$i] = strip_tags($value);
				}
				$i++;  
			}
    	}
    	$k=0;
    	foreach ($maxValue as $str) {
    		$idx = 0;
    		$col_title = "";
    		foreach ($results['columns'] as $c) {
    			if ($k == $idx) {
    				$col_title = $c;
    				break;
    			} else {
    				$idx++; 
    			}
    		}
    		$col_title_len = $pdf->GetStringWidth($col_title);
    		$colMaxTextSize = max($pdf->GetStringWidth($str), $col_title_len);
    		$db_col = array_var($results['columns'], $k);
    		$colType = array_var($types, array_var($results['db_columns'], $db_col, ''), '');
    		if($colType == DATA_TYPE_DATETIME && !($report->getObjectTypeName() == 'event' && $results['db_columns'][$db_col] == 'start')){
    			$colMaxTextSize = $colMaxTextSize / 2;
    			if ($colMaxTextSize < $col_title_len) $colMaxTextSize = $col_title_len;
    		}
    		$fixed_col_sizes[$k] = $colMaxTextSize;
    		$k++;
    	}
    	
    	$fixed_col_sizes = self::fix_column_widths(($pageLayout=='P'?172:260), $fixed_col_sizes);
    	
    	$max_char_len = array();
		$i = 0;
		foreach($results['columns'] as $col){
			$colMaxTextSize = $fixed_col_sizes[$i];
			$colFontSize = $colMaxTextSize + 5;
			$colSizes[$i] = $colFontSize;
			
			if (strtoupper($actual_encoding) == "UTF-8") {
				$col_name = html_entity_decode($col, ENT_COMPAT);
			} else {
				if (function_exists('mb_internal_encoding')) {
					$col_name = iconv(mb_internal_encoding(), "UTF-8", html_entity_decode($col, ENT_COMPAT));
				} else {
					$col_name = html_entity_decode($col, ENT_COMPAT);
				}
			}
			$pdf->Cell($colFontSize, 7, $col_name);
    		$max_char_len[$i] = self::get_max_length_from_pdfsize($pdf, $colFontSize);
    		$i++;
		}
		
		$lastColX = $pdf->GetX();
		$pdf->Ln();
		$pdf->Line($pdf->GetX(), $pdf->GetY(), $lastColX, $pdf->GetY());
		foreach($results['rows'] as $row) {
			$i = 0;
			$more_lines = array();
			$col_offsets = array();
			foreach($row as $k => $value){
				if ($k == 'object_type_id') continue;
				$db_col = isset($results['columns'][$i]) && isset($results['db_columns'][$results['columns'][$i]]) ? $results['db_columns'][$results['columns'][$i]] : '';

				$cell = format_value_to_print($db_col, html_to_text($value), ($k == 'link'?'':array_var($types, $k)), array_var($row, 'object_type_id'), '', is_numeric(array_var($results['db_columns'], $k)) ? "Y-m-d" : user_config_option('date_format'));
				
				if (strtoupper($actual_encoding) == "UTF-8") {
					$cell = html_entity_decode($cell, ENT_COMPAT);
				} else {
					if (function_exists('mb_internal_encoding')) {
						$cell = iconv(mb_internal_encoding(), "UTF-8", html_entity_decode($cell, ENT_COMPAT));
					} else {
						$cell = html_entity_decode($cell, ENT_COMPAT);
					}
				}
				
				$splitted = self::split_column_value($cell, $max_char_len[$i]);
				$cell = $splitted[0];
				if (count($splitted) > 1) {
					array_shift($splitted);
					$ml = 0;
					foreach ($splitted as $sp_val) {
						if (!isset($more_lines[$ml]) || !is_array($more_lines[$ml])) $more_lines[$ml] = array();
						$more_lines[$ml][$i] = $sp_val;
						$ml++;
					}
					$col_offsets[$i] = $pdf->x;
				}
				
				$pdf->Cell($colSizes[$i],7,$cell);
				$i++;
			}
			foreach ($more_lines as $ml_values) {
				$pdf->Ln();
				foreach ($ml_values as $col_idx => $col_val) {
					$pdf->SetX($col_offsets[$col_idx]);
					$pdf->Cell($colSizes[$col_idx],7,$col_val);
				}
			}
			$pdf->Ln();
			$pdf->SetDrawColor(220, 220, 220);
			$pdf->Line($pdf->GetX(), $pdf->GetY(), $lastColX, $pdf->GetY());
			$pdf->SetDrawColor(0, 0, 0);
		}
		$filename = ROOT."/tmp/".gen_id().".pdf";
		$pdf->Output($filename, "F");
		download_file($filename, "application/pdf", $report->getObjectName(), true);
		unlink($filename);
		die();
	}
	
	/**
	 * Returns an array containing the fixed widths of every column.
	 * If the sum of the column widths is longer than the page's width
	 * the bigger columns are resized to fit the page.
	 *
	 * @param integer $total_width
	 * @param array $max_col_valuesues
	 * @return array containing the fixed widths for every column
	 */
	function fix_column_widths($total_width, $max_col_values) {
		$fixed_widths = array();
		$columns_to_adjust = array();
		$to_add = 0;
		
		$average = floor($total_width / count($max_col_values));
		foreach ($max_col_values as $k => $width) {
			if ($width <= $average) {
				$fixed_widths[$k] = $width;
				$to_add += floor($average - $width);
			} else {
				$columns_to_adjust[] = $k;
			}
		}
		if (count($columns_to_adjust) > 0)
			$new_col_width = $average + (floor($to_add / count($columns_to_adjust)));

		foreach ($columns_to_adjust as $col) {
			if ($max_col_values[$col] > $new_col_width) $fixed_widths[$col] = $new_col_width;
			else $fixed_widths[$col] = $max_col_values[$col];
		}
		
		return $fixed_widths;
	}
	
	/**
	 * Gets the aproximated character count that can be written in the space delimited by $width.
	 *
	 * @param $pdf
	 * @param $width
	 * @return integer
	 */
	function get_max_length_from_pdfsize($pdf, $width) {
		$cw = &$pdf->CurrentFont['cw'];
		$w = 0;
		$i = 0;
		while($w < $width) {
			$w += $cw['a'] * $pdf->FontSize / 1000;
			$i++;
		}
		return $i;
	}
	
	/**
	 * Splits a value in pieces of maximum length = $length.
	 * The split point is the last position of a space char that is before the piece length 
	 *
	 * @param $value: string value to split
	 * @param $length: int max length of each piece
	 * @return array containing the pieces after splitting the value
	 */
	function split_column_value($value, $length) {
		if (strlen($value) <= $length) return array($value);
		$splitted = array();
		$i=0;
		while (strlen($value) > $length) {
			$pos = -1;
			while ($pos !== false && $pos < $length) {
				$pos_ant = $pos;
				$pos = strpos($value, " ", $pos+1);
			}
			if ($pos_ant != -1) $pos = $pos_ant;

			$splitted[$i] = substr($value, 0, $pos+1);
			$value = substr($value, $pos+1);
			$i++;
		}
		$splitted[$i] = $value;
		return $splitted;
	}
	
	

	function delete_custom_report(){
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$report_id = array_var($_GET, 'id');
		$report = Reports::getReport($report_id);

		if(!$report->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try{
			DB::beginWork();
			$report->delete();
			DB::commit();
			ajx_current("reload");
		}catch(Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
			ajx_current("empty");
		} // try
	}

	function get_object_fields(){
		$fields = $this->get_allowed_columns(array_var($_GET, 'object_type'));

		if (array_var($_GET, 'object_type') == Timeslots::instance()->getObjectTypeId()) {
			$tmp = array();
			foreach ($fields as $f) {
				if (($f['id'] == 'time' || $f['id'] == 'billing') && $f['type'] == 'external') continue;
				$tmp[] = $f;
			}
			$fields = $tmp;
		}
		
		ajx_current("empty");
		ajx_extra_data(array('fields' => $fields));
	}

	function get_object_fields_custom_properties(){ //returns only the custom properties
		$fields = $this->get_allowed_columns_custom_properties(array_var($_GET, 'object_type'), false);

		ajx_current("empty");
		ajx_extra_data(array('fields' => $fields));
	}
	
	
	private function get_allowed_columns_custom_properties($object_type, $include_common_cols=true) {
		//return array(); //FIXME: no usar todo lo de custom properties por el momento
		$fields = array();
		if(isset($object_type)){
			if (!is_numeric($object_type)) {
				$otype = ObjectTypes::instance()->findOne(array('conditions' => array('handler_class=?', $object_type)));
				if ($otype instanceof ObjectType) $object_type = $otype->getId();
			}
			$customProperties = CustomProperties::getAllCustomPropertiesByObjectType($object_type);
			$objectFields = array();
			foreach($customProperties as $cp){				
				if ($cp->getType() != 'table')
					$fields[] = array('id' => $cp->getId(), 'name' => $cp->getName(), 'type' => $cp->getType(), 'values' => $cp->getValues(), 'multiple' => $cp->getIsMultipleValues());
			}
			$ot = ObjectTypes::findById($object_type);
			
			if (class_exists($ot->getHandlerClass())) {
				eval('$managerInstance = ' . $ot->getHandlerClass() . "::instance();");
				
				if ($include_common_cols) {
					$common_columns = Objects::instance()->getColumns(false);
					$common_columns = array_diff_key($common_columns, array_flip($managerInstance->getSystemColumns()));
					$objectFields = array_merge($objectFields, $common_columns);
				}
			}
			
			foreach($objectFields as $name => $type){
				if($type == DATA_TYPE_FLOAT || $type == DATA_TYPE_INTEGER){
					$type = 'numeric';
				}else if($type == DATA_TYPE_STRING){
					$type = 'text';
				}else if($type == DATA_TYPE_BOOLEAN){
					$type = 'boolean';
				}else if($type == DATA_TYPE_DATE || $type == DATA_TYPE_DATETIME){
					$type = 'date';
				}
				
				$field_name = Localization::instance()->lang('field '.$ot->getHandlerClass().' '.$name);
				if (is_null($field_name)) $field_name = lang('field Objects '.$name);
				
				$fields[] = array('id' => $name, 'name' => $field_name, 'type' => $type);
			}
	
		}
		usort($fields, array(&$this, 'compare_FieldName'));
		return $fields;
	}
	
	function get_object_column_list(){
		$option_groups = null;
		$allowed_columns = $this->get_allowed_columns(array_var($_GET, 'object_type'));
		$columns = array_var($_GET, 'columns', array());
		if (array_var($_GET, 'object_type') == Timeslots::instance()->getObjectTypeId()) {
			$task_ot = ObjectTypes::findByName('task');
			$task_columns = $this->get_allowed_columns($task_ot->getId());
			
			$ts_group_count = count($allowed_columns);
			$task_columns_count = 0;

			foreach ($task_columns as $t) {
				if (str_starts_with($t['id'], 'dim_') || str_starts_with($t['id'], 'repeat_')
						|| in_array($t['id'], array('id','name'))) continue;
						$allowed_columns[] = $t;
						$task_columns_count++;
			}
			
			$contact_ot = ObjectTypes::findByName('contact');
			$contact_columns = $this->get_allowed_columns($contact_ot->getId());
			
			$contact_columns_count = 0;
			foreach ($contact_columns as $t) {
				if (str_starts_with($t['id'], 'dim_') || str_starts_with($t['id'], 'repeat_')
						|| in_array($t['id'], array('id','name'))) continue;
				
				// only custom properties for now
				if (is_numeric($t['id'])) { 
					$allowed_columns[] = $t;
					$contact_columns_count++;
				}
			}
			
			$option_groups = array(
				array('count' => $ts_group_count, 'name' => lang('timeslot').' '.lang('columns')),
				array('count' => $task_columns_count, 'name' => lang('task').' '.lang('columns')),
				array('count' => $contact_columns_count, 'name' => lang('contact').' '.lang('columns')),
			);
		}




                $ret = array($allowed_columns,$option_groups);
                Hook::fire('get_allow_columns_for_reports', array('object_type' => array_var($_GET, 'object_type'), 'columns' => $columns),$ret);
                $allowed_columns = $ret[0];
                $option_groups = $ret[1];


                $ret = array($allowed_columns,$option_groups);
                Hook::fire('get_allow_taxes_row_for_reports', array('object_type' => array_var($_GET, 'object_type'), 'columns' => $columns),$ret);
                $allowed_columns = $ret[0];
                $option_groups = $ret[1];


                tpl_assign('option_groups', $option_groups);
		tpl_assign('allowed_columns', $allowed_columns);
		tpl_assign('columns', explode(',', $columns));
		tpl_assign('order_by', array_var($_GET, 'orderby'));
		tpl_assign('order_by_asc', array_var($_GET, 'orderbyasc'));
		tpl_assign('genid', array_var($_GET, 'genid'));
		
		$this->setLayout("html");
		$this->setTemplate("column_list");
	}

	function get_object_column_list_task(){
		$allowed_columns = $this->get_allowed_columns_custom_properties(array_var($_GET, 'object_type'));
		$for_task = true;
		
		tpl_assign('allowed_columns', $allowed_columns);
		tpl_assign('columns', explode(',', array_var($_GET, 'columns', array())));	
		tpl_assign('genid', array_var($_GET, 'genid'));
		tpl_assign('for_task', $for_task);
		
		$this->setLayout("html");
		$this->setTemplate("column_list");
	}
	
	function get_external_field_values(){
		$field = array_var($_GET, 'external_field');
		$report_type = array_var($_GET, 'report_type');
		$values = $this->get_ext_values($field, $report_type);
		ajx_current("empty");
		ajx_extra_data(array('values' => $values));
	}

	private function get_ext_values($field, $ot_id = null){
		$values = array(array('id' => '', 'name' => lang('none')));
		$contact_cp = false;
		$user_cp = false;
		if (is_numeric($field)) {
			$cp = CustomProperties::getCustomProperty($field);
			if ($cp instanceof CustomProperty) {
				if ($cp->getType() == 'contact') $contact_cp = true;
				if ($cp->getType() == 'user') $user_cp = true;
			}
		}
		if($user_cp || $field == 'contact_id' || $field == 'created_by_id' || $field == 'updated_by_id' || $field == 'assigned_to_contact_id' || $field == 'completed_by_id'
			|| $field == 'approved_by_id'){
			$users = Contacts::getAllUsers();
			Hook::fire('filter_report_external_user_col_values', array('field' => $field, 'ot_id' => $ot_id), $users);
			
			foreach($users as $user){
				$values[] = array('id' => $user->getId(), 'name' => $user->getObjectName());
			}
		}else if($field == 'milestone_id'){
			$milestones = ProjectMilestones::getActiveMilestonesByUser(logged_user());
			foreach($milestones as $milestone){
				$values[] = array('id' => $milestone->getId(), 'name' => $milestone->getObjectName());
			}
		} else if ($field == 'company_id') {
			$companies = Contacts::findAll(array('conditions' => 'is_company > 0'));
			foreach ($companies as $comp) {
				$values[] = array('id' => $comp->getId(), 'name' => $comp->getObjectName());
			}
		} else if ($contact_cp) {
			$contacts = Contacts::instance()->listing(array('extra_conditions' => ' AND is_company=0 '))->objects;
			foreach ($contacts as $contact) {
				$values[] = array('id' => $contact->getId(), 'name' => $contact->getObjectName());
			}
		}
		
		Hook::fire('custom_reports_get_possible_external_column_values', array('field' => $field, 'ot_id' => $ot_id), $values);
		
		return $values;
	}

	function get_allowed_columns($object_type) {
		$fields = array();
		if(isset($object_type)){
			$customProperties = CustomProperties::getAllCustomPropertiesByObjectType($object_type);
			$objectFields = array();
			
			foreach($customProperties as $cp){
				if ($cp->getType() == 'table') continue;
                //if ($cp->getType() == 'contact') continue;
				$fields[] = array('id' => $cp->getId(), 'name' => $cp->getName(), 'type' => $cp->getType(), 'values' => $cp->getValues(), 'multiple' => $cp->getIsMultipleValues());
			}
			
			$ot = ObjectTypes::findById($object_type);
			if (class_exists($ot->getHandlerClass())) {
				$managerInstance = null;
				eval('$managerInstance = ' . $ot->getHandlerClass() . "::instance();");
				$objectColumns = $managerInstance->getColumns();
				$externalFields = $managerInstance->getExternalColumns();
				$calculatedColumns = $managerInstance->getCalculatedColumns();
			} else {
				$objectColumns = array();
				$externalFields = array();
				$calculatedColumns = array();
			}
			
			$objectFields = array();
			
			if (class_exists($ot->getHandlerClass())) {
				$objectColumns = array_diff($objectColumns, $managerInstance->getSystemColumns());
				foreach($objectColumns as $column){
					if (!in_array($column, $externalFields)) {
						$objectFields[$column] = $managerInstance->getColumnType($column);
					}
				}
			}
			
			foreach ($calculatedColumns as $calc_col_name) {
				$objectFields[$calc_col_name] = 'calculated';
			}
			
			if ($ot->getType() == 'dimension_group') {
				$common_columns = array('id' => DATA_TYPE_INTEGER, 'name' => DATA_TYPE_STRING, 'archived_on' => DATA_TYPE_DATETIME, 'archived_by_id' => DATA_TYPE_INTEGER);
			} else {
				$common_columns = Objects::instance()->getColumns(false);
			}
			if (class_exists($ot->getHandlerClass())) {
				$system_columns = $managerInstance->getSystemColumns();
				$common_columns = array_diff_key($common_columns, array_flip($system_columns));
			}
			$objectFields = array_merge($objectFields, $common_columns);

			foreach($objectFields as $name => $type){
				if($type == DATA_TYPE_FLOAT || $type == DATA_TYPE_INTEGER){
					$type = 'numeric';
				}else if($type == DATA_TYPE_STRING){
					$type = 'text';
				}else if($type == DATA_TYPE_BOOLEAN){
					$type = 'boolean';
				}else if($type == DATA_TYPE_DATE || $type == DATA_TYPE_DATETIME){
					$type = 'date';
				}
				
				$field_name = Localization::instance()->lang('field '.$ot->getHandlerClass().' '.$name);
				if (is_null($field_name)) $field_name = lang('field Objects '.$name);

				$fields_array = array('id' => $name, 'name' => $field_name, 'type' => $type);
				
				$task_ot = ObjectTypes::findByName('task');
				if ($task_ot instanceof ObjectType && $object_type == $task_ot->getId() && $name == 'priority') {
					$fields_array = array('id' => 'priority', 'name' => lang('priority'), 'type' => 'list', 'values' => '100,200,300,400');
				}
				
				$fields[] = $fields_array;
			}
	
			if (class_exists($ot->getHandlerClass())) {
				
				foreach($externalFields as $extField){
					$field_name = Localization::instance()->lang('field '.$ot->getHandlerClass().' '.$extField);
					if (is_null($field_name)) $field_name = lang('field Objects '.$extField);
					
					$f = array('id' => $extField, 'name' => $field_name, 'type' => 'external', 'multiple' => 0);
					Hook::fire('custom_reports_external_column_info', array('object_type' => $ot, 'field_name' => $extField), $f);
					
					$fields[] = $f;
				}
			}
			//if Object type is person
			$contact_ot = ObjectTypes::findByName('contact');
			if ($contact_ot instanceof ObjectType && $object_type == $contact_ot->getId()) {
				$fields[] = array('id' => 'is_user', 'name' => lang('is_user'), 'type' => 'boolean');
				$fields[] = array('id' => 'email_address', 'name' => lang('email address'), 'type' => 'text');
				$fields[] = array('id' => 'mobile_phone', 'name' => lang('mobile phone'), 'type' => 'text');
				$fields[] = array('id' => 'work_phone', 'name' => lang('work phone'), 'type' => 'text');
				$fields[] = array('id' => 'home_phone', 'name' => lang('home phone'), 'type' => 'text');
				$fields[] = array('id' => 'im_values', 'name' => lang('instant messaging'), 'type' => 'text');
				$fields[] = array('id' => 'personal_webpage', 'name' => lang('personal_webpage'), 'type' => 'text');
				$fields[] = array('id' => 'work_webpage', 'name' => lang('work_webpage'), 'type' => 'text');
				$fields[] = array('id' => 'other_webpage', 'name' => lang('other_webpage'), 'type' => 'text');
				$fields[] = array('id' => 'home_address', 'name' => lang('home_address'), 'type' => 'text');
				$fields[] = array('id' => 'work_address', 'name' => lang('work_address'), 'type' => 'text');
				$fields[] = array('id' => 'other_address', 'name' => lang('other_address'), 'type' => 'text');
				$fields[] = array('id' => 'postal_address', 'name' => lang('postal_address'), 'type' => 'text');
			}
			if (!array_var($_REQUEST, 'noaddcol')) {
				Hook::fire('custom_reports_additional_columns', array('object_type' => $ot), $fields);
			}
			Hook::fire('custom_reports_fixed_additional_columns', array('object_type' => $ot), $fields);
		}
		usort($fields, array(&$this, 'compare_FieldName'));
		return $fields;
	}

	function compare_FieldName($field1, $field2){
		return strnatcasecmp($field1['name'], $field2['name']);
	}

	function get_report_column_types($report_id) {
		$col_types = array();
		$report = Reports::getReport($report_id);
		if (!$report instanceof Report) return $col_types;
		
		$ot = ObjectTypes::findById($report->getReportObjectTypeId());
		if (!$ot instanceof ObjectType) return $col_types;
		
		$model = trim($ot->getHandlerClass());
		if ($model && class_exists($model)) {
			$manager = new $model();
	
			$columns = ReportColumns::getAllReportColumns($report_id);
	
			foreach ($columns as $col) {
				$cp_id = $col->getCustomPropertyId();
				if ($cp_id == 0) {
					$col_types[$col->getFieldName()] = $manager->getColumnType($col->getFieldName());
				} else {
					$cp = CustomProperties::getCustomProperty($cp_id);
					if ($cp) {
						$col_types[$cp->getName()] = $cp->getOgType();
					}
				}
			}
		}
		return $col_types;
	}
	
	
	
	
	
	
	
	
	
	
	private function cvs_total_task_times_group($group_obj, $grouped_objects, $options, $skip_groups = array(), $level = 0, $prev = "", &$total = 0) {
		$text = "";
	
		$pad_str = "";
		for ($k = 0; $k < $level; $k++) $pad_str .= "   ";
	
		$cls_suffix = $level > 2 ? "all" : $level;
		$next_level = $level + 1;
			
		$group_name = $group_obj['group']['name'];
	
		$text .= '"'. $pad_str . $group_name . '"'. "\n";
	
		$mem_index = $prev . $group_obj['group']['id'];
	
		$group_total = 0;
	
		$table_total = 0;
		// draw the table for the values
		if (isset($grouped_objects[$mem_index]) && count($grouped_objects[$mem_index]) > 0) {
			$text .= $this->cvs_total_task_times_table($grouped_objects[$mem_index], $pad_str, $options, $group_name, $table_total);
			$group_total += $table_total;
		}
	
		if (!is_array($group_obj['subgroups'])) return;
	
		$subgroups = order_groups_by_name($group_obj['subgroups']);
	
		foreach ($subgroups as $subgroup) {
			$sub_total = 0;
			$text .= $this->cvs_total_task_times_group($subgroup, $grouped_objects, $options, $skip_groups, $next_level, $prev . $group_obj['group']['id'] . "_", $sub_total);
			$group_total += $sub_total;
		}
	
		$total += $group_total;
	
		$text .= "$group_name;;;".lang('subtotal'). ': '.";" . $group_total.";\n\n";
	
		return $text;
	}
	
	private function cvs_total_task_times_table($objects, $pad_str, $options, $group_name, &$sub_total = 0) {
		$text = "";
	
		$column_titles = array(
				lang('date'),
				lang('title'),
				lang('description'),
				lang('person'),
				lang('time') .' ('.lang('hours').')'
		);
		Hook::fire('total_tasks_times_csv_columns', $column_titles, $column_titles);
		
		foreach ($column_titles as $ct) {
			$text .= $ct . ';';
		}
		$text .= "\n";
	
		$sub_total = 0;
	
		foreach ($objects as $ts) {
			$text .= $pad_str . format_date($ts->getStartTime()) . ';';
				
			$name = ($ts->getRelObjectId() == 0 ? $ts->getObjectName() : $ts->getRelObject()->getObjectName());
			$name = str_replace("\r", " ", str_replace("\n", " ", str_replace("\r\n", " ", $name)));
			$text .= $name . ';';
				
			$desc = $ts->getDescription();
			$desc = str_replace("\r", " ", str_replace("\n", " ", str_replace("\r\n", " ", $desc)));
			$desc = '"'.$desc.'"';
			$text .= $desc .';';
				
			$text .= ($ts->getUser() instanceof Contact ? $ts->getUser()->getObjectName() : '') .';';
			$lastStop = $ts->getEndTime() != null ? $ts->getEndTime() : ($ts->isPaused() ? $ts->getPausedOn() : DateTimeValueLib::now());
			$mystring = DateTimeValue::FormatTimeDiff($ts->getStartTime(), $lastStop, "m", 60, $ts->getSubtract());
			$resultado = preg_replace("[^0-9]", "", $mystring);
			$resultado = is_numeric($resultado) ? round(($resultado/60),5) : 0;
			$text .= $resultado;
			$sub_total += $resultado;
				
			$new_values = null;
			Hook::fire('total_tasks_times_csv_column_values', $ts, $new_values);
			if (is_array($new_values) && count($new_values) > 0) {
				foreach ($new_values as $nv) {
					$nv = str_replace("\r", " ", str_replace("\n", " ", str_replace("\r\n", " ", $nv)));
					$text .= ';' . $nv;
				}
			}
				
			$text .= "\n";
		}
	
		return $text;
	}
	
	private function total_task_times_csv_export($grouped_timeslots) {
		$text = "";
	
		$skip_groups = array();
		if (!isset($context)) $context = active_context();
		foreach ($context as $selection) {
			if ($selection instanceof Member) {
				$sel_parents = $selection->getAllParentMembersInHierarchy();
				foreach ($sel_parents as $sp) $skip_groups[] = $sp->getId();
			}
		}
	
		$groups = order_groups_by_name($grouped_timeslots['groups']);
		$total = 0;
		foreach ($groups as $gid => $group_obj) {
			$text .= $this->cvs_total_task_times_group($group_obj, $grouped_timeslots['grouped_objects'], array_var($_SESSION, 'total_task_times_parameters'), $skip_groups, 0, "", $total);
		}
	
		$text .= ";;;".lang('total'). ': '.";" .$total.";\n";
	
	
		$filename = lang('task time report');
		file_put_contents(ROOT."/tmp/$filename.csv", $text);
				
		return $filename;
	}
   

}

