<?php

/**
 * Dashboard controller
 *
 * @author Ilija Studen <ilija.studen@gmail.com>, Marcos Saiz <marcos.saiz@fengoffice.com>
 */
class DashboardController extends ApplicationController {

	/**
	 * Construct controller and check if we have logged in user
	 *
	 * @param void
	 * @return null
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
		$this->addHelper('calendar');
	} // __construct
	
	/**
	 * 
	 * 
	 */
	public function activity_feed()
	{
		ajx_set_no_back(true);
		require_javascript("og/modules/dashboardComments.js");
		require_javascript("jquery/jquery.scrollTo-min.js");

		// Restrict the listing to content_objects and comments, matching the old
		// extra_conditions filter: jt.type IN ('content_object', 'comment').
		$_GET['only_content_objects'] = 1;
		$_GET['include_comments'] = 1;

		$obj_controller = new ObjectController();
		$params = $obj_controller->get_list_objects_params();
		$params['count_results'] = false;
		$params['show_all_linked_objects'] = true;

		$listing = $obj_controller->get_objects_list($params);

		unset($_GET['only_content_objects'], $_GET['include_comments']);

		// Add activity-feed-specific fields not included by get_objects_list.
		foreach ($listing['objects'] as &$info_elem) {
			$instance = Objects::findObject($info_elem['object_id']);
			if (!$instance instanceof ContentDataObject) continue;

			if (method_exists($instance, 'getText')) {
				$info_elem['content'] = $instance->getText();
			}
			$info_elem['picture'] = $instance->getCreatedBy() ? $instance->getCreatedBy()->getPictureUrl() : '';
			$info_elem['friendly_date'] = friendly_date($instance->getCreatedOn());
			$info_elem['comment'] = $instance->getComments();
		}
		unset($info_elem);

		tpl_assign("feeds", $listing);
	}
	
	/**
	 * Show dashboard index page
	 *
	 * @param void
	 * @return null
	 */
	function index() {
		$this->setHelp('dashboard');
		ajx_set_no_toolbar(true);
		
		$logged_user = logged_user();
		
		$activity_log = null;
		$include_private = $logged_user->isMemberOfOwnerCompany();
		$include_silent = $logged_user->isAdminGroup();

		// FIXME
		$activity_log = array();//ApplicationLogs::getOverallLogs($include_private, $include_silent, $wscsv, config_option('dashboard_logs_count', 15));

		/* FIXME if (user_config_option('show charts widget') && module_enabled('reporting')) {
			$charts = ProjectCharts::getChartsAtProject(active_project(), active_tag());
			tpl_assign('charts', $charts);
			
			if (BillingCategories::instance()->count() > 0 && active_project() instanceof Project){
				tpl_assign('billing_chart_data', active_project()->getBillingTotalByUsers(logged_user()));
			}
		}*/
		if (user_config_option('show messages widget') && module_enabled('notes')) {
			//FIXME list($messages, $pagination) = ProjectMessages::getMessages(active_tag(), active_project(), 0, 10, '`updated_on`', 'DESC', false);
			tpl_assign('messages', $messages);
		}
		if (user_config_option('show comments widget')) {
			//FIXME $comments = Comments::getSubscriberComments(active_project(), $tag);
			tpl_assign('comments', $comments);
		}
		if (user_config_option('show documents widget') && module_enabled('documents')) {
			//FIXME list($documents, $pagination) = ProjectFiles::getProjectFiles(active_project(), null, false, ProjectFiles::ORDER_BY_MODIFYTIME, 'DESC', 1, 10, false, active_tag(), null);
			tpl_assign('documents', $documents);
		}
		
		if (user_config_option('show emails widget') && module_enabled('email')) {
			/* FIXME $activeWs = active_project();
			list($unread_emails, $pagination) = MailContents::getEmails($tag, null, 'received', 'unread', '', $activeWs, 0, 10);

			if ($activeWs && user_config_option('always show unread mail in dashboard')) {
				// add unread unclassified emails
				list($all_unread, $pagination) = MailContents::getEmails($tag, null, 'received', 'unread', 'unclassified', null, 0, 10);
				$unread_emails = array_merge($unread_emails, $all_unread);
			}*/
			
			tpl_assign('unread_emails', $unread_emails);
		}
		
		//Tasks widgets
		$show_pending = user_config_option('show pending tasks widget')  && module_enabled('tasks');
		$show_in_progress = user_config_option('show tasks in progress widget') && module_enabled('tasks');
		$show_late = user_config_option('show late tasks and milestones widget') && module_enabled('tasks');
		if ($show_pending || $show_in_progress || $show_late) {
			$assigned_to = explode(':', user_config_option('pending tasks widget assigned to filter'));
			$to_company = array_var($assigned_to, 0,0);
			$to_user = array_var($assigned_to, 1, 0);
			tpl_assign('assigned_to_user_filter',$to_user);
			tpl_assign('assigned_to_company_filter',$to_company);
		}
		if ($show_pending) {
			//FIXME $tasks = ProjectTasks::getProjectTasks(active_project(), ProjectTasks::ORDER_BY_DUEDATE, 'ASC', null, null, $tag, $to_company, $to_user, null, true, 'all', false, false, false, 10);
			tpl_assign('dashtasks', $tasks);
		}
		if ($show_in_progress) {
			//FIXME $tasks_in_progress = ProjectTasks::getOpenTimeslotTasks(logged_user(),logged_user(), active_project(), $tag,$to_company,$to_user);
			tpl_assign('tasks_in_progress', $tasks_in_progress);
		}
		if ($show_late) {
			//FIXME tpl_assign('today_milestones', $logged_user->getTodayMilestones(active_project(), $tag, 10));
			//FIXME tpl_assign('late_milestones', $logged_user->getLateMilestones(active_project(), $tag, 10));
			//FIXME tpl_assign('today_tasks', ProjectTasks::getDayTasksByUser(DateTimeValueLib::now(), $logged_user, active_project(), $tag, $to_company, $to_user, 10));
			//FIXME tpl_assign('late_tasks', ProjectTasks::getLateTasksByUser($logged_user, active_project(), $tag, $to_company, $to_user, 10));
		}
		
		tpl_assign('activity_log', $activity_log);
		
		$usu = logged_user();
		$conditions = array("conditions" => array("`state` >= 200 AND (`state`%2 = 0) AND `trashed_on=0 AND `created_by_id` =".$usu->getId()));
		//FIXME $outbox_mails = MailContents::instance()->findAll($conditions);
		if ($outbox_mails!= null){
			if (count($outbox_mails)==1){		
				flash_error(lang('outbox mail not sent', 1));
			} else if (count($outbox_mails)>1){
				flash_error(lang('outbox mails not sent', count($outbox_mails)));
			}
		}
	} // index

	/**
	 * Show my projects page
	 *
	 * @param void
	 * @return null
	 */
	function my_projects() {
		$this->addHelper('textile');
		tpl_assign('active_projects', logged_user()->getActiveProjects());
		tpl_assign('finished_projects', logged_user()->getFinishedProjects());
	} // my_projects

	/**
	 * Show milestones and tasks assigned to specific user
	 *
	 * @param void
	 * @return null
	 */
	function my_tasks() {
		tpl_assign('active_projects', logged_user()->getActiveProjects());
	} // my_tasks
	
	
	
	//*************** Main dashboard ***********************//

	/**
	 * @author Ignacio Vazquez
	 */
	function main_dashboard(){
		if (user_config_option("overviewAsList")){
			require_javascript("og/OverviewManager.js");
			ajx_current("panel", "overview", null, null, true);
			ajx_replace(true);
		}else{
			ajx_set_no_toolbar(true);
		}
	}
	
	function load_widget () {
		$this->setLayout('empty');
		ajx_current('empty');
		$this->setTemplate('empty');
		$name = $_GET['name'];
		if ($w = Widgets::instance()->findById($name) ){ /* @var $w Widget */
			$output = $w->execute();
			echo $output;
		}
		exit;
		//TODO Avoid exit : find the way to do that with the framework
	}
	
	/**
	 * API endpoint to save user config option
	 */
	function save_user_config_option() {
		if (!logged_user() instanceof Contact) {
			ajx_current("empty");
			return;
		}
		
		$option_name = array_var($_POST, 'option_name');
		$option_value = array_var($_POST, 'option_value');
		
		if (!$option_name) {
			ajx_current("empty");
			return;
		}
		
		$result = set_user_config_option($option_name, $option_value, logged_user()->getId());
		
		if (is_ajax_request()) {
			if ($result) {
				ajx_current("success");
			} else {
				ajx_current("error");
			}
		} else {
			// prevent framework to try loading a template that doesn't exist
			die();
		}
	}

	/**
	 * API endpoint to save multiple widget options atomically in a single transaction.
	 * POST params: widget_name, options (JSON array of {name, value} objects).
	 */
	function save_widget_options() {
		if (!logged_user() instanceof Contact) {
			ajx_current("empty");
			if (!is_ajax_request()) die();
			return;
		}
		$widget_name = array_var($_POST, 'widget_name');
		$options_raw = array_var($_POST, 'options');
		if (!$widget_name || !$options_raw) {
			ajx_current("empty");
			if (!is_ajax_request()) die();
			return;
		}
		$options = json_decode($options_raw, true);
		if (!is_array($options) || empty($options)) {
			ajx_current("error");
			if (!is_ajax_request()) die();
			return;
		}
		// Normalize and validate each entry. Values arrive as native JSON types
		// (integer for numeric options, array for JSON options) so we re-encode
		// arrays to their string representation before persisting.
		$normalized = array();
		foreach ($options as $entry) {
			$name      = isset($entry['name'])  ? $entry['name']  : '';
			$value_raw = isset($entry['value']) ? $entry['value'] : '';
			if (!$name) {
				ajx_current("error");
				if (!is_ajax_request()) die();
				return;
			}
			$value = is_array($value_raw) ? json_encode($value_raw) : strval($value_raw);
			if (strlen($value) > 65535) {
				ajx_current("error");
				if (!is_ajax_request()) die();
				return;
			}
			$normalized[] = array('name' => $name, 'value' => $value);
		}
		try {
			DB::beginWork();
			foreach ($normalized as $entry) {
				$name  = $entry['name'];
				$value = $entry['value'];
				$opt = ContactWidgetOptions::instance()->findOne(array('conditions' =>
					array('contact_id=? AND widget_name=? AND `option`=?',
						logged_user()->getId(), $widget_name, $name)
				));
				if (!$opt instanceof ContactWidgetOption) {
					$opt = new ContactWidgetOption();
					$opt->setContactId(logged_user()->getId());
					$opt->setWidgetName($widget_name);
					$opt->setMemberTypeId(0);
					$opt->setOption($name);
				}
				$opt->setValue($value);
				$opt->save();
			}
			DB::commit();
			ajx_current("success");
		} catch (Exception $e) {
			DB::rollback();
			ajx_current("error");
		}
		if (!is_ajax_request()) die();
	}

	/**
	 * API endpoint to save a single option to contact_widget_options for the logged user.
	 */
	function save_widget_option() {
		if (!logged_user() instanceof Contact) {
			ajx_current("empty");
			if (!is_ajax_request()) die();
			return;
		}
		$widget_name  = array_var($_POST, 'widget_name');
		$option_name  = array_var($_POST, 'option_name');
		$option_value = array_var($_POST, 'option_value');
		if (strlen($option_value) > 65535) {
			ajx_current("error");
			if (!is_ajax_request()) die();
			return;
		}
		if ($option_name === 'columns' && $option_value !== '' && json_decode($option_value) === null) {
			ajx_current("error");
			if (!is_ajax_request()) die();
			return;
		}
		if (!$widget_name || !$option_name) {
			ajx_current("empty");
			if (!is_ajax_request()) die();
			return;
		}
		try {
			$opt = ContactWidgetOptions::instance()->findOne(array('conditions' =>
				array('contact_id=? AND widget_name=? AND `option`=?',
					logged_user()->getId(), $widget_name, $option_name)
			));
			if (!$opt instanceof ContactWidgetOption) {
				$opt = new ContactWidgetOption();
				$opt->setContactId(logged_user()->getId());
				$opt->setWidgetName($widget_name);
				$opt->setMemberTypeId(0);
				$opt->setOption($option_name);
			}
			$opt->setValue($option_value);
			$opt->save();
			ajx_current("success");
		} catch (Exception $e) {
			ajx_current("error");
		}
		if (!is_ajax_request()) die();
	}

}



/**
 * @author pepe
 */
class DashboardTools {
	
	static $widgets = array(); 

	static function renderSection($name) {

		$widgetsToRender = array();
		
		self::$widgets = Widgets::instance()->findAll(array(
			"conditions" => " plugin_id = 0 OR plugin_id IS NULL OR plugin_id IN ( SELECT id FROM ".TABLE_PREFIX."plugins WHERE is_activated > 0 AND is_installed > 0 )",
			"order" => "default_order",
			"order_dir" => "DESC",
		));
		
		// If exists an instance of cw for this section, render the widgets with the options overriden
		foreach (self::$widgets as $w) {
			
			if 	($cw = ContactWidgets::instance()->findById(array('contact_id'=>logged_user()->getId(),'widget_name'=>$w->getName()))){
				if ( $cw->getSection() == $name ) {
					$w->setDefaultOrder($cw->getOrder());
					$widgetsToRender[] = $w ;
				}
			}elseif($w->getDefaultSection() == $name){
				$widgetsToRender[] = $w ;
			}
		}
		
		usort($widgetsToRender, "widget_sort") ;
		foreach ($widgetsToRender as $k => $w) {
			//$start = microtime(true);
			$w->execute();
			//if ((microtime(true)-$start)>1) Logger::log_r($w->getName()." rendered in: ".(microtime(true)-$start));
		}
		
	}
}

		
function widget_sort(Widget $a, Widget $b) {
    if ($a->getDefaultOrder() == $b->getDefaultOrder()) {
        return 0;
    }
    return ($a->getDefaultOrder() < $b->getDefaultOrder()) ? -1 : 1;
}
