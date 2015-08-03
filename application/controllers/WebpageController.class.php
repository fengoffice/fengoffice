<?php

/**
 * Webpage controller
 *
 * @version 1.0
 * @author Carlos Palma <chonwil@gmail.com>
 */
class WebpageController extends ApplicationController {

	/**
	 * Construct the WebpageController
	 *
	 * @access public
	 * @param void
	 * @return WebpageController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct

	function init() {
		require_javascript("og/WebpageManager.js");
		ajx_current("panel", "webpages", null, null, true);
		ajx_replace(true);
	}
	
	/**
	 * Add webpage
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
		$this->setTemplate('add');
		
		$notAllowedMember = '';
		if(!ProjectWebpage::canAdd(logged_user(), active_context(), $notAllowedMember)) {
			if (str_starts_with($notAllowedMember, '-- req dim --')) flash_error(lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in)));
			else trim($notAllowedMember) == "" ? flash_error(lang('you must select where to keep', lang('the webpage'))) : flash_error(lang('no context permissions to add',lang("webpages"), $notAllowedMember));
			ajx_current("empty");
			return;
		} // if

		$webpage = new ProjectWebpage();
		
		if(is_array(array_var($_POST, 'webpage'))) {
			// set layout for modal form
			if (array_var($_REQUEST, 'modal')) {
				$this->setLayout("json");
				tpl_assign('modal', true);
			}
		}
		$webpage_data = array_var($_POST, 'webpage');
		
		if(is_array(array_var($_POST, 'webpage'))) {
			foreach ($webpage_data as $k => &$v) {
				$v = remove_scripts($v);
			}
			try {
				if(substr_utf($webpage_data['url'],0,7) != 'http://' && substr_utf($webpage_data['url'],0,7) != 'file://' && substr_utf($webpage_data['url'],0,8) != 'https://' && substr_utf($webpage_data['url'],0,6) != 'about:' && substr_utf($webpage_data['url'],0,6) != 'ftp://') {
					$webpage_data['url'] = 'http://' . $webpage_data['url'];
				}
				
				$webpage->setFromAttributes($webpage_data);
				
				DB::beginWork();
				$webpage->save();

				$member_ids = json_decode(array_var($_POST, 'members'));
				
				//link it!
				$object_controller = new ObjectController();
				$object_controller->add_subscribers($webpage);
				$object_controller->add_to_members($webpage, $member_ids);
				$object_controller->link_to_new_object($webpage);
				$object_controller->add_subscribers($webpage);
				$object_controller->add_custom_properties($webpage);

				DB::commit();
				ApplicationLogs::createLog($webpage, ApplicationLogs::ACTION_ADD);


				flash_success(lang('success add webpage', $webpage->getObjectName()));
				ajx_current("back");
				
				if (array_var($_REQUEST, 'modal')) {
					evt_add("reload current panel");
				}
				
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}

		}

		tpl_assign('webpage', $webpage);
		tpl_assign('webpage_data', $webpage_data);
	} // add

	/**
	 * Edit specific webpage
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
		$this->setTemplate('add');

		$webpage = ProjectWebpages::findById(get_id());
		if(!($webpage instanceof ProjectWebpage)) {
			flash_error(lang('webpage dnx'));
			ajx_current("empty");
			return;
		}

		if(!$webpage->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}

		$webpage_data = array_var($_POST, 'webpage');
		if(!is_array($webpage_data)) {
			// set layout for modal form
			if (array_var($_REQUEST, 'modal')) {
				$this->setLayout("json");
				tpl_assign('modal', true);
			}
			$webpage_data = array(
	          'url' => $webpage->getUrl(),
	          'name' => $webpage->getObjectName(),
	          'description' => $webpage->getDescription(),
			);
		}

		if(is_array(array_var($_POST, 'webpage'))) {
			foreach ($webpage_data as $k => &$v) {
				$v = remove_scripts($v);
			}
			try {
				$webpage->setFromAttributes($webpage_data);
				
				DB::beginWork();
				
				$webpage->save();

				$member_ids = json_decode(array_var($_POST, 'members'));
				
				$object_controller = new ObjectController();
				$object_controller->add_to_members($webpage, $member_ids);
				$object_controller->link_to_new_object($webpage);
				$object_controller->add_subscribers($webpage);
				$object_controller->add_custom_properties($webpage);

				
				$webpage->resetIsRead();
				
				DB::commit();
				ApplicationLogs::createLog($webpage, ApplicationLogs::ACTION_EDIT);
				
				flash_success(lang('success edit webpage', $webpage->getObjectName()));
				ajx_current("back");
				
				if (array_var($_REQUEST, 'modal')) {
					evt_add("reload current panel");
				}
				
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		}

		tpl_assign('webpage', $webpage);
		tpl_assign('webpage_data', $webpage_data);
	} // edit

	/**
	 * Delete specific webpage
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
		$webpage = ProjectWebpages::findById(get_id());
		if(!($webpage instanceof ProjectWebpage)) {
			flash_error(lang('webpage dnx'));
			ajx_current("empty");
			return;
		}

		if(!$webpage->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}

		try {

			DB::beginWork();
			$webpage->trash();
			DB::commit();
			ApplicationLogs::createLog($webpage, ApplicationLogs::ACTION_TRASH);

			flash_success(lang('success deleted webpage', $webpage->getObjectName()));
			ajx_current("back");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete webpage'));
			ajx_current("empty");
		}
	} // delete

	function list_all() {
		ajx_current("empty");
		
		$context = active_context() ;
			
		$start = array_var($_GET, 'start', 0);
		$limit = array_var($_GET, 'limit', config_option('files_per_page'));
		
		$order = array_var($_GET, 'sort');
		if ($order == "updatedOn" || $order == "updated" || $order == "date" || $order == "dateUpdated") $order = "updated_on";
		
		$order_dir = array_var($_GET, 'dir');
		$page = (integer) ($start / $limit) + 1;
		$hide_private = !logged_user()->isMemberOfOwnerCompany();

		if (array_var($_GET,'action') == 'delete') {
			$ids = explode(',', array_var($_GET, 'webpages'));
			$succ = 0; $err = 0;
			foreach ($ids as $id) {
				$web_page = ProjectWebpages::findById($id);
				if (isset($web_page) && $web_page->canDelete(logged_user())) {
					try{
						DB::beginWork();
						$web_page->trash();
						DB::commit();
						ApplicationLogs::createLog($web_page, ApplicationLogs::ACTION_TRASH);
						$succ++;
					} catch(Exception $e){
						DB::rollback();
						$err++;
					}
				} else {
					$err++;
				}
			}
			if ($succ > 0) {
				flash_success(lang("success delete objects", $succ));
			}
			if ($err > 0) {
				flash_error(lang("error delete objects", $err));
			}
		}  else if (array_var($_GET, 'action') == 'markasread') {
			$ids = explode(',', array_var($_GET, 'ids'));
			$succ = 0; $err = 0;
				foreach ($ids as $id) {
				$webpage = ProjectWebpages::findById($id);
					try {
						$webpage->setIsRead(logged_user()->getId(),true);
						$succ++;
						
					} catch(Exception $e) {						
						$err ++;
					}
				}
			if ($succ <= 0) {
				flash_error(lang("error markasread files", $err));
			}
		} else if (array_var($_GET, 'action') == 'markasunread') {
			$ids = explode(',', array_var($_GET, 'ids'));
			$succ = 0; $err = 0;
				foreach ($ids as $id) {
				$webpage = ProjectWebpages::findById($id);
					try {
						$webpage->setIsRead(logged_user()->getId(),false);
						$succ++;
						
					} catch(Exception $e) {						
						$err ++;
					}
				}
			if ($succ <= 0) {
				flash_error(lang("error markasunread files", $err));
			}
		} else if (array_var($_GET,'action') == 'archive') {
			$ids = explode(',', array_var($_GET, 'webpages'));
			$succ = 0; $err = 0;
			foreach ($ids as $id) {
				$web_page = ProjectWebpages::findById($id);
				if (isset($web_page) && $web_page->canEdit(logged_user())) {
					try{
						DB::beginWork();
						$web_page->archive();
						DB::commit();
						ApplicationLogs::createLog($web_page, ApplicationLogs::ACTION_ARCHIVE);
						$succ++;
					} catch(Exception $e){
						DB::rollback();
						$err++;
					}
				} else {
					$err++;
				}
			}
			if ($succ > 0) {
				flash_success(lang("success archive objects", $succ));
			}
			if ($err > 0) {
				flash_error(lang("error archive objects", $err));
			}
		}
		
		$extra_conditions = "";
		Hook::fire("listing_extra_conditions", null, $extra_conditions);
		
		$only_count_result = array_var($_GET, 'only_result',false);
		
		$res =  ProjectWebpages::instance()->listing(array(
			"start" =>$start,
			"limit" => $limit,
			"order" => $order , 
			"order_dir" => $order_dir,
			"extra_conditions" => $extra_conditions,
			'count_results' => false,
			'only_count_results' => $only_count_result
		));
		
		$object = array(
			"totalCount" => $res->total,
			"start" => $start,
			"webpages" => array()
		);
		$custom_properties = CustomProperties::getAllCustomPropertiesByObjectType(ProjectWebpages::instance()->getObjectTypeId());
		if (isset($res->objects)) {
			$index = 0;
			$ids = array();
			foreach ($res->objects as $w) {
				$ids[] = $w->getId();
				$object["webpages"][$index] = array(
					"ix" => $index,
					"id" => $w->getId(),
					"object_id" => $w->getObjectId(),
					"ot_id" => $w->getObjectTypeId(),
					"name" => $w->getObjectName(),
					"description" => $w->getDescription(),
					"url" => $w->getUrl(),
					"updatedOn" => $w->getUpdatedOn() instanceof DateTimeValue ? ($w->getUpdatedOn()->isToday() ? format_time($w->getUpdatedOn()) : format_datetime($w->getUpdatedOn())) : '',
					"updatedOn_today" => $w->getUpdatedOn() instanceof DateTimeValue ? $w->getUpdatedOn()->isToday() : 0,
					"updatedBy" => $w->getUpdatedByDisplayName(),
					"updatedById" => $w->getUpdatedById(),
					"memPath" => json_encode($w->getMembersIdsToDisplayPath()),
				);
				
				foreach ($custom_properties as $cp) {
					$object["webpages"][$index]['cp_'.$cp->getId()] = get_custom_property_value_for_listing($cp, $w);
				}
				$index++;
			}
			
			$read_objects = ReadObjects::getReadByObjectList($ids, logged_user()->getId());
			foreach($object["webpages"] as &$data) {
				$data['isRead'] = isset($read_objects[$data['object_id']]);
			}
		}
		ajx_extra_data($object);
	}
	
	function view() {
		$this->addHelper("textile");
		$weblink = ProjectWebpages::findById(get_id());
		if(!($weblink instanceof ProjectWebpage)) {
			flash_error(lang('weblink dnx'));
			ajx_current("empty");
			return;
		}

		if(!$weblink->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$weblink->setIsRead(logged_user()->getId(),true);

		tpl_assign('object', $weblink);
		ajx_extra_data(array("title" => $weblink->getObjectName(), 'icon'=>'ico-weblink'));
		ajx_set_no_toolbar(true);
		
		ApplicationReadLogs::createLog($weblink, ApplicationReadLogs::ACTION_READ);
	}
} // WebpageController

?>