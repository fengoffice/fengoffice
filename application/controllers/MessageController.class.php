<?php

/**
 * Message controller
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class MessageController extends ApplicationController {

	/**
	 * Construct the MessageController
	 *
	 * @access public
	 * @param void
	 * @return MessageController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct
	
	// ---------------------------------------------------
	//  Index
	// ---------------------------------------------------
	
	function init() {
		require_javascript("og/MessageManager.js");
		ajx_current("panel", "messages", null, null, true);
		ajx_replace(true);
	}
	
	function list_all() {
		ajx_current("empty");
		
		// Get all variables from request
		$start = array_var($_GET,'start', 0);
		$limit = array_var($_GET,'limit', config_option('files_per_page'));
		$order = array_var($_GET,'sort');
		$order_dir = array_var($_GET,'dir');
		$tag = array_var($_GET,'tag');
		$action = array_var($_GET,'action');
		$attributes = array(
			"ids" => explode(',', array_var($_GET,'ids')),
			"types" => explode(',', array_var($_GET,'types')),
			"accountId" => array_var($_GET,'account_id'),
		);
		
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
		
		//if order by custom prop
		if (strpos($order, 'p_') == 1 ){
			$cpId = substr($order, 3);
			$order = 'customProp';
		}
		$join_params = array();
		$select_columns = array('*');
		$extra_conditions = "";

		switch ($order){
			case 'updatedOn':
				$order = '`updated_on`';
				break;
			case 'createdOn':
				$order = '`created_on`';
				break;
			case 'name':
				$order = '`name`';
				break;
			case 'customProp':
				$order = 'IF(ISNULL(jt.value),1,0),jt.value';
				$join_params['join_type'] = "LEFT ";
				$join_params['table'] = "".TABLE_PREFIX."custom_property_values";
				$join_params['jt_field'] = "object_id";
				$join_params['e_field'] = "object_id";
				$join_params['on_extra'] = "AND custom_property_id = ".$cpId;
				$extra_conditions.= " AND ( custom_property_id = ".$cpId. " OR custom_property_id IS NULL)";
				$select_columns = array("DISTINCT o.*", "e.*");
				break;
			default:
				$order = '`updated_on`';  
				break;
		}
		if (!$order_dir){
			switch ($order){
				case 'name': $order_dir = 'ASC'; break;
				default: $order_dir = 'DESC';
			}
		}
		
		Hook::fire("listing_extra_conditions", null, $extra_conditions);
		
		$only_count_result = array_var($_GET, 'only_result',false);
		$context = active_context();
		$res = ProjectMessages::instance()->listing(array(
			"order" => $order,
			"order_dir" => $order_dir,
			"start" => $start,
			"limit" => $limit,
			"extra_conditions" => $extra_conditions,
			'count_results' => false,
			'only_count_results' => $only_count_result,
			"join_params"=> $join_params,
			"select_columns"=> $select_columns
		));
		$messages = $res->objects ; 
		
		// Prepare response object
		$object = $this->prepareObject($messages, $start, $limit, $res->total);
		ajx_extra_data($object);
		tpl_assign("listing", $object);
		
	}
	
	/**
	 * Resolve action to perform
	 *
	 * @param string $action
	 * @param array $attributes
	 * @return string $message
	 */
	private function resolveAction($action, $attributes){
				 
		$resultMessage = "";
		$resultCode = 0;
		switch ($action){
			case "delete":
				$succ = 0; $err = 0;
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$message = ProjectMessages::findById($id);
					if (isset($message) && $message->canDelete(logged_user())){
						try{
							DB::beginWork();
							$message->trash();
							DB::commit();
							ApplicationLogs::createLog($message, ApplicationLogs::ACTION_TRASH);
							$succ++;
						} catch(Exception $e){
							DB::rollback();
							$err++;
						}
					} else {
						$err++;
					}
				}; // for
				if ($err > 0) {
					$resultCode = 2;
					$resultMessage = lang("error delete objects", $err) . ($succ > 0 ? "\n".lang("success delete objects", $succ) : "");
				} else {
					$resultMessage = lang("success delete objects", $succ);
				}
				break;
			case "markasread":
				$succ = 0; $err = 0;
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$message = ProjectMessages::findById($id);
					try {
						$message->setIsRead(logged_user()->getId(),true);						
						$succ++;
					} catch(Exception $e) {
						$err ++;
					} // try
				}; // for
				if ($err > 0) {
					$resultCode = 2;
					$resultMessage = lang("error markasread objects", $err) . "<br />" . ($succ > 0 ? lang("success markasread objects", $succ) : "");
				}
				break;
			case "markasunread":
				$succ = 0; $err = 0;
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$message = ProjectMessages::findById($id);
					try {
						$message->setIsRead(logged_user()->getId(),false);						
						$succ++;
					} catch(Exception $e) {
						$err ++;
					} // try
				}; // for
				if ($err > 0) {
					$resultCode = 2;
					$resultMessage = lang("error markasunread objects", $err) . "<br />" . ($succ > 0 ? lang("success markasunread objects", $succ) : "");
				}
				break;
			case "archive":
				$succ = 0; $err = 0;
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$message = ProjectMessages::findById($id);
					if (isset($message) && $message->canEdit(logged_user())){
						try{
							DB::beginWork();
							$message->archive();
							DB::commit();
							ApplicationLogs::createLog($message, ApplicationLogs::ACTION_ARCHIVE);
							$succ++;
						} catch(Exception $e){
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
				$resultMessage = lang("Unimplemented action: '" . $action . "'");// if 
				$resultCode = 2;	
				break;		
		} // switch
		return array("errorMessage" => $resultMessage, "errorCode" => $resultCode);
	}
	
	/**
	 * Prepares return object for a list of emails and messages
	 *
	 * @param array $totMsg
	 * @param integer $start
	 * @param integer $limit
	 * @return array
	 */
	private function prepareObject($totMsg, $start, $limit, $total) {
		$object = array(
			"totalCount" => $total,
			"start" => $start,
			"messages" => array()
		);
		$custom_properties = CustomProperties::getAllCustomPropertiesByObjectType(ProjectMessages::instance()->getObjectTypeId());
		$ids = array();
		for ($i = 0; $i < $limit; $i++){
			if (isset($totMsg[$i])){
				$msg = $totMsg[$i];
				if ($msg instanceof ProjectMessage){
					$text = $msg->getText();
					if (strlen($text) > 100) $text = substr_utf($text,0,100) . "...";
					$object["messages"][$i] = array(
					    "id" => $i,
						"ix" => $i,
						"object_id" => $msg->getId(),
						"ot_id" => $msg->getObjectTypeId(),
						"type" => $msg->getObjectTypeName(),
						"name" => $msg->getObjectName(),
						"text" => html_to_text($text),
						"date" => $msg->getUpdatedOn() instanceof DateTimeValue ? ($msg->getUpdatedOn()->isToday() ? format_time($msg->getUpdatedOn()) : format_datetime($msg->getUpdatedOn())) : '',
						"is_today" => $msg->getUpdatedOn() instanceof DateTimeValue ? $msg->getUpdatedOn()->isToday() : 0,
						"userId" => $msg->getCreatedById(),
						"userName" => $msg->getCreatedByDisplayName(),
						"updaterId" => $msg->getUpdatedById() ? $msg->getUpdatedById() : $msg->getCreatedById(),
						"updaterName" => $msg->getUpdatedById() ? $msg->getUpdatedByDisplayName() : $msg->getCreatedByDisplayName(),
						"memPath" => json_encode($msg->getMembersIdsToDisplayPath()),
					);
					$ids[] = $msg->getId();
					
					foreach ($custom_properties as $cp) {
						$object["messages"][$i]['cp_'.$cp->getId()] = get_custom_property_value_for_listing($cp, $msg);
					}
    			}
			}
		}
		
		$read_objects = ReadObjects::getReadByObjectList($ids, logged_user()->getId());
		foreach($object["messages"] as &$data) {
			$data['isRead'] = isset($read_objects[$data['object_id']]);
		}
		
		return $object;
	}
	

	// ---------------------------------------------------
	//  Messages
	// ---------------------------------------------------
	
	/**
	 * View single message
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function view() {
		$this->addHelper('textile');

		$message = ProjectMessages::findById(get_id());
		if(!($message instanceof ProjectMessage)) {
			flash_error(lang('message dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$message->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$this->setHelp("view_message");
		
		//read object for this user
		$message->setIsRead(logged_user()->getId(),true);
		tpl_assign('message', $message);
		tpl_assign('subscribers', $message->getSubscribers());
		ajx_extra_data(array("title" => $message->getTitle(), 'icon'=>$message->getIconClass()));
		ajx_set_no_toolbar(true);
		
		ApplicationReadLogs::createLog($message, ApplicationReadLogs::ACTION_READ);
	} // view
	
	/**
	 * View a message in a printer-friendly format.
	 *
	 */
	function print_view() {
		$this->setLayout("html");
		$this->addHelper('textile');
		
		$message = ProjectMessages::findById(get_id());
		if(!($message instanceof ProjectMessage)) {
			flash_error(lang('message dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$message->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		tpl_assign('message', $message);
	} // print_view

	/**
	 * Add message
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function add() {
		$this->setTemplate('add_message');
		
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current('empty');
			return;
		}
		
		$notAllowedMember = '';
		if(!ProjectMessage::canAdd(logged_user(), active_context(), $notAllowedMember )) {
			if (str_starts_with($notAllowedMember, '-- req dim --')) flash_error(lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in)));
			else trim($notAllowedMember) == "" ? flash_error(lang('you must select where to keep', lang('the message'))) : flash_error(lang('no context permissions to add',lang("messages"),$notAllowedMember ));
			ajx_current("empty");
			return;
		} // if
		
		$message = new ProjectMessage();
		tpl_assign('message', $message);

		$message_data = array_var($_POST, 'message');
		if(!is_array($message_data)) {
			// set layout for modal form
			if (array_var($_REQUEST, 'modal')) {
				$this->setLayout("json");
				tpl_assign('modal', true);
			}
			$message_data = array();
		} // if
		tpl_assign('message_data', $message_data);

		if(is_array(array_var($_POST, 'message'))) {
			foreach ($message_data as $k => &$v) {
				$v = remove_scripts($v);
			}
			try {
				if(config_option('untitled_notes'))
				{
					if(!array_var($message_data, "name"))
					{
						$message_data["name"] = lang("untitled note");
					}
				}
				// Aliases
				if(config_option("wysiwyg_messages")){
					$message_data['type_content'] = "html";
					$message_data['text'] = preg_replace("/[\n|\r|\n\r]/", '', array_var($message_data, 'text'));
				}else{
					$message_data['type_content'] = "text";
				}
				$message->setFromAttributes($message_data);
				
				DB::beginWork();
				
				$message->save();
				
				
				$object_controller = new ObjectController();
				
				$member_ids = json_decode(array_var($_POST, 'members'));
				
				$object_controller->add_to_members($message, $member_ids);
                                $object_controller->add_subscribers($message);
				$object_controller->link_to_new_object($message);				
				$object_controller->add_custom_properties($message);
				
				DB::commit();
				ApplicationLogs::createLog($message, ApplicationLogs::ACTION_ADD);
				
				flash_success(lang('success add message', $message->getObjectName()));
				if (array_var($_POST, 'popup', false)) {
					ajx_current("reload");
	          	} else {
	          		ajx_current("back");
	          	}
	          	if (array_var($_REQUEST, 'modal')) {
	          		evt_add("reload current panel");
	          	}
	          	ajx_add("overview-panel", "reload");          	
					
				// Error...
			} catch(Exception $e) {
				DB::rollback();
				$message->setNew(true);
				flash_error($e->getMessage());
				ajx_current("empty");
				
			} // try

		} // if
	} // add

	/**
	 * Edit specific message
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit() {
		$this->setTemplate('add_message');
		
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current('empty');
			return;
		}

		$message = ProjectMessages::findById(get_id());
		if(!($message instanceof ProjectMessage)) {
			flash_error(lang('message dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$message->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		$message_data = array_var($_POST, 'message');
		if(!is_array($message_data)) {
			$message_data = array(
				'name' => $message->getObjectName(),
				'text' => $message->getText(),
				'type_content' => $message->getTypeContent(),
			);
			// set layout for modal form
			if (array_var($_REQUEST, 'modal')) {
				$this->setLayout("json");
				tpl_assign('modal', true);
			}
		} // if
		
		tpl_assign('message', $message);
		tpl_assign('message_data', $message_data);

		if(is_array(array_var($_POST, 'message'))) {
			foreach ($message_data as $k => &$v) {
				$v = remove_scripts($v);
			}
			try {
				
				//MANAGE CONCURRENCE WHILE EDITING
				/* FIXME or REMOVEME
				$upd = array_var($_POST, 'updatedon');
				if ($upd && $message->getUpdatedOn()->getTimestamp() > $upd && !array_var($_POST,'merge-changes') == 'true')
				{
					ajx_current('empty');
					evt_add("handle edit concurrence", array(
						"updatedon" => $message->getUpdatedOn()->getTimestamp(),
						"genid" => array_var($_POST,'genid')
					));
					return;
				}
				if (array_var($_POST,'merge-changes') == 'true')
				{
					$this->setTemplate('view');
					$edited_note = ProjectMessages::findById($message->getId());
					tpl_assign('message', $edited_note);
					tpl_assign('subscribers', $edited_note->getSubscribers());
					ajx_extra_data(array("name" => $edited_note->getObjectName(), 'icon'=>'ico-message'));
					ajx_set_no_toolbar(true);
					ajx_set_panel(lang ('tab name',array('name'=>$edited_note->getObjectName())));					
					return;
				}
				*/
				if(config_option("wysiwyg_messages")){
					$message_data['type_content'] = "html";
					$message_data['text'] = preg_replace("/[\n|\r|\n\r]/", '', array_var($message_data, 'text'));
				}else{
					$message_data['type_content'] = "text";
				}
				$message->setFromAttributes($message_data);

				DB::beginWork();
				$message->save();
				
				$object_controller = new ObjectController();
				
				$member_ids = json_decode(array_var($_POST, 'members'));
				
				$object_controller->add_to_members($message, $member_ids);
			    $object_controller->link_to_new_object($message);
				$object_controller->add_subscribers($message);
				$object_controller->add_custom_properties($message);
				
				$message->resetIsRead();
				
				DB::commit();
				ApplicationLogs::createLog($message, ApplicationLogs::ACTION_EDIT);
				
				flash_success(lang('success edit message', $message->getObjectName()));
				if (array_var($_POST, 'popup', false)) {
					ajx_current("reload");
	          	} else {
	          		ajx_current("back");
	          	}
	          	
	          	if (array_var($_REQUEST, 'modal')) {
	          		evt_add("reload current panel");
	          	}

			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // edit

	/**
	 * Delete specific message
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function delete() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current('empty');
			return;
		}
		
		ajx_current("empty");
		$message = ProjectMessages::findById(get_id());
		if(!($message instanceof ProjectMessage)) {
			flash_error(lang('message dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$message->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try {

			DB::beginWork();
			$message->trash();
			DB::commit();
			ApplicationLogs::createLog($message, ApplicationLogs::ACTION_TRASH);
			
			flash_success(lang('success deleted message', $message->getObjectName()));
			if (array_var($_POST, 'popup', false)) {
				ajx_current("reload");
          	} else {
          		ajx_current("back");
          	}
          	ajx_add("overview-panel", "reload");          	
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete message'));
			ajx_current("empty");
		} // try
	} // delete



} // MessageController

?>