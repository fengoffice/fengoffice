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

	function init_overview() {
		if (user_config_option("overviewAsList")){
			require_javascript("og/OverviewManager.js");
			ajx_current("panel", "overview", null, null, true);
			ajx_replace(true);
		}else{
			ajx_set_no_toolbar(true);
		}
	}
	
	/**
	 * 
	 * 
	 */
	public function activity_feed()
	{
		ajx_set_no_back(true);
		require_javascript("og/modules/dashboardComments.js");
		require_javascript("jquery/jquery.scrollTo-min.js");
		
		$filesPerPage = config_option('files_per_page');
		$start = array_var($_GET,'start') ? (integer)array_var($_GET,'start') : 0;
		$limit = array_var($_GET,'limit') ? array_var($_GET,'limit') : $filesPerPage;

		$order = array_var($_GET,'sort');
		$orderdir = array_var($_GET,'dir');
		$page = (integer) ($start / $limit) + 1;
		
		$extra_conditions = " AND jt.type IN ('content_object', 'comment')";
		
		$trashed = array_var($_GET, 'trashed', false);
		$archived = array_var($_GET, 'archived', false);

		$pagination = ContentDataObjects::listing(array(
			"start" => $start,
			"limit" => $limit,
			"order" => $order,
			"order_dir" => $orderdir,
			"trashed" => $trashed,
			"archived" => $archived,
			"count_results" => false,
			"extra_conditions" => $extra_conditions,
			"join_params" => array(
				"jt_field" => "id",
				"e_field" => "object_type_id",
				"table" => TABLE_PREFIX."object_types",
			)
		));
		$result = $pagination->objects; 
		$total_items = $pagination->total ;
		 
		if(!$result) $result = array();

		$info = array();
		foreach ($result as $obj) {
			
			$info_elem =  $obj->getArrayInfo($trashed, $archived);
			
			$instance = Objects::instance()->findObject($info_elem['object_id']);
			$info_elem['url'] = $instance->getViewUrl();
		
			if( method_exists($instance, "getText")) {
				$info_elem['content'] = $instance->getText();
			}
			$info_elem['picture'] = $instance->getCreatedBy()->getPictureUrl();
			$info_elem['friendly_date'] = friendly_date($instance->getCreatedOn());
			$info_elem['comment'] = $instance->getComments();		
			
			if ($instance instanceof  Contact) {
				if( $instance->isCompany() ) {
					$info_elem['icon'] = 'ico-company';
					$info_elem['type'] = 'company';
				}
			}
			$info_elem['isRead'] = $instance->getIsRead(logged_user()->getId()) ;
			$info_elem['manager'] = get_class($instance->manager()) ;
			
			$info[] = $info_elem;
		}
		
		$listing = array(
			"totalCount" => $total_items,
			"start" => $start,
			"objects" => $info
		);
		
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
			
			if (BillingCategories::count() > 0 && active_project() instanceof Project){
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
		//FIXME $outbox_mails = MailContents::findAll($conditions);
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
			echo $w->execute();
		}
		exit;
		//TODO Avoid exit : find the way to do that with the framework
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
			$w->execute();
		}
		
	}
}

		
function widget_sort(Widget $a, Widget $b) {
    if ($a->getDefaultOrder() == $b->getDefaultOrder()) {
        return 0;
    }
    return ($a->getDefaultOrder() < $b->getDefaultOrder()) ? -1 : 1;
}
	
			
