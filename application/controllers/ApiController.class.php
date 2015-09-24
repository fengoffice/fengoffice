<?php

/**
 * Middle class to API - FengOffice integration
 */
class ApiController extends ApplicationController {

    private $response = NULL;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        prepare_company_website_controller($this, 'website');
        $this->setLayout('empty');
    }

    /**
     * Default action
     */
    public function index() {
        try {
            $request = $_REQUEST;
            //Handle action
            $action = $request['m'];
            if (isset($request['args'])) {
                $request['args'] = json_decode($request['args'], 1);
            } else {
                $request['args'] = array();
            }
            if (method_exists($this, $action))
                $response = $this->$action($request);

            tpl_assign('response', $response);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Read a object-
     */
    private function get_object($request) {
        try {
            $tasks = Objects::findObject($request['oid']);
            /* @var $tasks ProjectTask */
            if ($tasks->canView(logged_user())) {
                return $this->response('json', $tasks->getArrayInfo(true));
            } else {
                $this->response('json', false);
            }
        } catch (Exception $exception) {
            throw $exception;
        }
    }
    
	/*
     * Get an object's comments     
     */
	private function get_comments($request){
		try {
            $object = Objects::findObject($request['oid']);            
            $comments = $object->getComments();    
            $clean_comments = array();        
            foreach($comments as $comment) {
            	$user_name = $comment->getCreatedByDisplayName();
            	$updated_on = format_datetime($comment->getUpdatedOn());
            	$text = escape_html_whitespace(convert_to_links(clean($comment->getText())));             
            	$clean_comment = array("author"=>$user_name, "date"=>$updated_on, "text"=>$text);
            	$clean_comments[] = $clean_comment;            	
            }            
            return $this->response('json', $clean_comments);
        } catch (Exception $exception) {
            throw $exception;
        }    
    }

//    private function list_members($request) {
//            $service = $request ['srv'];
//
//            $members = array();
//            $type = ObjectTypes::instance()->findByName($service);
//            $typeName = $type->getName();
//            $typeId = $type->getId();
//            foreach (Members::instance()->findAll(array("conditions"=>"object_type_id = $typeId")) as $member){
//                    /* @var $member Member */
//                    $memberInfo = array(
//                            'id'=>$member->getId(),
//                            'name'=>$member->getName(),
//                            'type'=>'project',
//                            'path' => $member->getPath()
//                    );
//
//                    $members[] = $memberInfo;
//            }
//            return $this->response ( 'json', $members );
//
//    }

    //provides all of the members from the dimension member in question
    private function list_members($request) {
    	$service = $request ['srv'];
        $start = (!empty($request['args']['start'])) ? $request['args']['start'] : 0;
        $limit = (!empty($request['args']['limit'])) ? $request['args']['limit'] : null;
        $name = (!empty($request['args']['name'])) ? $request['args']['name'] : "";
        
        $members = array();
        $type = ObjectTypes::instance()->findByName($service);
        $typeId = $type->getId();
        if($service == "workspace"){
            $dimension_id = Dimensions::findByCode('workspaces')->getId();
        }else{
            $dimension_id = Dimensions::findByCode('customer_project')->getId();
        }
        $limit_obj = array(
        		'offset' => $start,
        		'limit' => $limit,
        );
        $extra_conditions = null;
        if ($name!=""){
        	$extra_conditions = "AND name LIKE '%".$name."%'";
        }
        $params = array('dim_id' => $dimension_id, 'type_id' => $typeId, 'start'=>$start,'limit'=>$limit);
        $memberController = new MemberController();
        $object = $memberController->list_all($params);
        foreach ($object["members"] as $m) {
        	$member = Members::getMemberById($m['id']);
        	$memberInfo = array(
        			'id' => $m['id'],
        			'name' => $m['name'],
        			'type' => $service,
        			'path' => $member->getPath()
        	);
        	
        	$members[] = $memberInfo;
        }
        return $this->response('json', $members);
    }
    
    //provides the latest active members from the dimension member in question
 	private function list_latest_active_members($request) {
        $service = $request ['srv'];        
        $members = array();
        $type = ObjectTypes::instance()->findByName($service);
        $typeId = $type->getId();
        if($service == "workspace"){
            $dimension_id = Dimensions::findByCode('workspaces')->getId();            
        }else{
            $dimension_id = Dimensions::findByCode('customer_project')->getId();
        }
        $ids = array();
        $dimensionController = new DimensionController();       
        foreach ($dimensionController->latest_active_dimension_members($dimension_id, $typeId, null, user_config_option("mobile_logs_amount_to_search"),
        														user_config_option("mobile_minimum_display_dimension_members"), user_config_option("mobile_maximum_display_dimension_members")) as $member) {
            $ids [] = $member['object_id'];
        }
        if (count($ids)) {
            $args['conditions'] = " `object_id` IN (" . implode(",", $ids) . ") AND object_type_id = $typeId";
            $args['order'] = " name ASC";            
            foreach (Members::instance()->findAll($args) as $member) {
                /* @var $member Member */
                $memberInfo = array(
                    'id' => $member->getId(),
                    'name' => $member->getName(),
                    'type' => $service,
                    'path' => $member->getPath()
                );

                $members[] = $memberInfo;
            }
        }
        return $this->response('json', $members);
    }

    private function list_contacts_assigned_to($request) {
        $members = (!empty($request['args']['members']) && count(empty($request['args']['members']))) ? $request['args']['members'] : null;
        $contacts = allowed_users_to_assign_all_mobile($members);
        return $this->response('json', $contacts);
    }

    /**
     * Retrive list of objects
     * @params mixed options
     * @return object list
     * @throws Exception
     */
    private function listing($request) {
        try {
            $service = $request['srv'];

            $order = (!empty($request['args']['order'])) ? $request['args']['order'] : null;
            $order_dir = (!empty($request['args']['order_dir'])) ? $request['args']['order_dir'] : null;
            $members = (!empty($request['args']['members']) && count(empty($request['args']['members']))) ? $request['args']['members'] : null;
            $start = (!empty($request['args']['start'])) ? $request['args']['start'] : 0;
            $limit = (!empty($request['args']['limit'])) ? $request['args']['limit'] : null;

            $query_options = array(
                //'ignore_context' => true,
                'order' => $order,
                'order_dir' => $order_dir,
                'member_ids' => $members,
                'extra_conditions' => '',
                'start' => $start,
                'limit' => $limit
            );

            // COMMON FILTERS: For all content Types
            if (!empty($request['args']['created_by_id'])) {
                $query_options['extra_conditions'] = " AND created_by_id = " . $request['args']['created_by_id'] . " ";
            }

            // TYPE DEPENDENT FILTERS :
            switch ($service) {

                case 'ProjectTasks' :
                    if (!empty($request['args']['assigned_to'])) {
                        $query_options['extra_conditions'] = " AND assigned_to_contact_id = " . $request['args']['assigned_to'] . " ";
                    }

                    $task_status_condition = "";
                    $now = DateTimeValueLib::now()->format('Y-m-j 00:00:00');

                    if (isset($request['args']['status'])) {
                        $status = (int) $request['args']['status'];
                    } else {
                        $status = 1; // Read Filters Config options in the API? think about this.. 
                    }
                    switch ($status) {
                        case 0: // Incomplete tasks
                            $task_status_condition = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME);
                            break;
                        case 1: // Complete tasks
                            $task_status_condition = " AND `completed_on` > " . DB::escape(EMPTY_DATETIME);
                            break;
                        case 10: // Active tasks
                            $task_status_condition = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `start_date` <= '$now'";
                            break;
                        case 11: // Overdue tasks
                            $task_status_condition = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `due_date` < '$now'";
                            break;
                        case 12: // Today tasks
                            $task_status_condition = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `due_date` = '$now'";
                            break;
                        case 13: // Today + Overdue tasks
                            $task_status_condition = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `due_date` <= '$now'";
                            break;
                        case 14: // Today + Overdue tasks
                            $task_status_condition = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `due_date` <= '$now'";
                            break;
                        case 20: // Actives task by current user
                            $task_status_condition = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `start_date` <= '$now' AND `assigned_to_contact_id` = " . logged_user()->getId();
                            break;
                        case 21: // Subscribed tasks by current user
                            $res20 = DB::execute("SELECT object_id FROM " . TABLE_PREFIX . "object_subscriptions WHERE `contact_id` = " . logged_user()->getId());
                            $subs_rows = $res20->fetchAll($res20);
                            foreach ($subs_rows as $row)
                                $subs[] = $row['object_id'];
                            unset($res20, $subs_rows, $row);
                            $task_status_condition = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `id` IN(" . implode(',', $subs) . ")";
                            break;
                        case 2:
                            break;
                    }
                    if (!empty($task_status_condition)) {
                        $query_options['extra_conditions'] .= $task_status_condition;
                    }
                    break;
            }// Case ProjectTasks


            eval('$service_instance = '.$service.'::instance();');
            $result = $service_instance->listing($query_options);
            //$result = $service->instance()->listing($query_options);
            $temp_objects = array();
            foreach ($result->objects as $object) {
                if ($service == "ProjectTasks") {
                    array_push($temp_objects, $object->getArrayInfo(1,true));
                } else {
                    array_push($temp_objects, $object->getArrayInfo());
                }
            }

            return $this->response('json', $temp_objects);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    private function complete_task($request) {
        $response = false;
        if ($id = $request['id']) {
            if ($task = ProjectTasks::instance()->findById($id)) {
                if ($task->canChangeStatus(logged_user())) {
                    try {
                        if (isset($request['action']) && $request['action'] == 'complete') {
                            $task->complete(DateTimeValueLib::now(), logged_user());
                            $task->setPercentCompleted(100);
                            $task->save();
                        } else {
                            $task->openTask();
                        }

                        $response = true;
                    } catch (Exception $e) {
                        $response = false;
                    }
                }
            }
        }
        return $this->response('json', $response);
    }

    private function trash($request) {
        $response = false;
        if ($id = $request['id']) {
            if ($object = Objects::findObject($id)) {
                if ($object->canDelete(logged_user())) {
                    try {
                        $object->trash();
                        Hook::fire('after_object_trash', $object, $null);
                        $response = true;
                    } catch (Exception $e) {
                        $response = false;
                    }
                }
            }
        }
        return $this->response('json', $response);
    }

    private function save_object($request) {
        $response = false;
        if (!empty($request ['args'])) {
            $service = $request ['srv'];
            switch ($service) {
                case "task" :
                    if ($request ['args'] ['id']) {
                        $object = ProjectTasks::instance()->findByid($request ['args'] ['id']);
                    } else {
                        $object = new ProjectTask ();
                    }
                    if ($object instanceof ProjectTask) {
                        if (!empty($request ['args'] ['title'])) {
                            $object->setObjectName($request ['args'] ['title']);
                        }
                        if (!empty($request ['args'] ['description'])) {
                            $object->setText($request ['args'] ['description']);
                        }
                        if (!empty($request ['args'] ['due_date'])) {
                        	if ($request ['args'] ['due_date'] != '' && $request ['args'] ['due_date'] != date_format_tip('dd/mm/yyyy')) {
                        		$date_format = 'dd/mm/yyyy';
                        		$object->setDueDate(DateTimeValueLib::dateFromFormatAndString($date_format, $value));
                        	}                           
                        }
                        if (!empty($request ['args'] ['completed'])) {
                            $object->setPercentCompleted($request ['args'] ['completed']);
                        }
                        if (!empty($request ['args'] ['assign_to'])) {
                            $object->setAssignedToContactId($request ['args'] ['assign_to']);
                        }
                        if (!empty($request ['args'] ['priority'])) {
                            $object->setPriority($request ['args'] ['priority']);
                        }
                    }
                    break;

                case 'note' :
                    if ($request ['args'] ['id']) {
                        $object = ProjectMessages::instance()->findByid($request ['args'] ['id']);
                    } else {
                        $object = new ProjectMessage();
                    }
                    if ($object instanceof ProjectMessage) {
                        if (!empty($request ['args'] ['title'])) {
                            $object->setObjectName($request ['args'] ['title']);
                        }
                        if (!empty($request ['args'] ['title'])) {
                            $object->setText($request ['args'] ['text']);
                        }
                    }
                    break;
            }// END SWITCH

            if ($object) {
                try {
                    $context = array();
                    $members = array();
                    if (!empty($request['args']['members'])) {
                        $members = $request['args']['members'];
                        $context = get_context_from_array($members);
                    }

                    //Check permissions: 
                    if ($request['args']['id'] && $object->canEdit(logged_user()) ||
                            !$request['args']['id'] && $object->canAdd(logged_user(), $context)) {
                        DB::beginWork();
                        $object->save();
                        $object_controller = new ObjectController ();
                        if (!$request['args']['id']) {

                            $object_controller->add_to_members($object, $members);
                        }
                        DB::commit();
                        $response = true;
                    }
                } catch (Exception $e) {
                    DB::rollback();
                    return false;
                }
            }
        }
        return $this->response('json', $response);
    }
    
    private function add_comment($request) {
    	$_GET['object_id'] = $request ['args'] ['id'];
    	$_POST['comment[text]'] = $request ['args'] ['comment'];
    	CommentController::add();
    	/*$response = false;
    	if (!empty($request ['args'])) {
    		$object = ProjectTasks::instance()->findByid($request ['args'] ['id']);
    		$comment = new Comment();
    		$comment_data = ($request ['args'] ['comment']);
    		
    		
    		try {
    			$comment->setFromAttributes($comment_data);
	    		$comment->setRelObjectId($object->getId());
	    		$comment->setObjectName(substr_utf($comment->getText(), 0, 250));

	    		DB::beginWork();
	    		$comment->save();
    			
	    		$comment->addToMembers($object->getMembers());
	    		$comment->addToSharingTable();
	    		
	    		// Subscribe user to object
	    		if(!$object->isSubscriber(logged_user())) {
	    			$object->subscribeUser(logged_user());
	    		}
	    		if (strlen($comment->getText()) < 100) {
	    			$comment_head = $comment->getText();
	    		} else {
	    			$lastpos = strpos($comment->getText(), " ", 100);
	    			if ($lastpos === false) $comment_head = $comment->getText();
	    			else $comment_head = substr($comment->getText(), 0, $lastpos) . "...";
	    		}
	    		$comment_head = html_to_text($comment_head);
	    		DB::commit();
    			$response = true;
    		} catch (Exception $e) {
    				DB::rollback();
    				return false;
    			}
    		}*/
    	return $this->response('json', true);
    }
    
    private function active_plugin($request){
        $plugin = $request ['plugin'];
        $active = 0;
        if(Plugins::instance()->isActivePlugin($plugin)){
            $active = 1;
        }
        $plugin_state = array('plugin_state' => $active);
        
        return $this->response('json', $plugin_state);
    }

    /**
     * Response formated API results
     * @param response type
     * @param response content
     * @return formated API result
     * @throws Exception
     */
    private function response($type = NULL, $response) {
        switch ($type) {
            case 'json':
                return json_encode($response);
            default:
                throw new Exception('Response type must be defined');
        }
    }

}