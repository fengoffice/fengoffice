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
            $object = Objects::findObject($request['oid']);
            /* @var $tasks ProjectTask */
            if ($object->canView(logged_user())) {
            	
            	if ($object instanceof ProjectTask) {
            		$object_data = $object->getArrayInfo(true, true);
                } elseif($object instanceof PaymentReceipt){
                    $object_data = $object->getArrayInfo(false, true);
                    $product_type = ProductTypes::instance()->findById($object->getProductTypeId());
                    if($product_type instanceof ProductType){
                        $object_data['product_type'] = $product_type->getName();
                    }
                    $object_data['members_data'] = build_api_members_data($object);
                } else {
            		if ($object instanceof Timeslot) {
            			$object_data = $object->getArrayInfo(false, true);
            		} else {
		            	$object_data = $object->getArrayInfo();
            		}
	            	$object_data['members_data'] = build_api_members_data($object);
            	}
            	
                return $this->response('json', $object_data);
            } else {
                $this->response('json', false);
            }
        } catch (Exception $exception) {
            throw $exception;
        }
    }

	/*
     * Get an object's timeslots     
     */
	private function get_timeslots($request){
		try {
            $clean_timeslots = array();
            
            if (!is_null($request['oid'])) {
                // If the request has an Object id, retrieve the timeslots of that task.
                $object = Objects::findObject($request['oid']);
                if ($object instanceof ContentDataObject) {
    	            $timeslots = $object->getTimeslots();
    	            foreach($timeslots as $timeslot) {
    	            	
    	            	$data = $timeslot->getArrayInfo();
    	            	
    	            	$data['paused_desc'] = "";
    	            	$formatted = DateTimeValue::FormatTimeDiff($timeslot->getStartTime(), $timeslot->getEndTime(), "hm", 60, $timeslot->getSubtract());
    	            	if ($timeslot->getSubtract() > 0) {
    	            		$now = DateTimeValueLib::now();
    	            		$data['paused_desc'] = DateTimeValue::FormatTimeDiff($now, $now, "hm", 60, $timeslot->getSubtract());
    	            	}
    	            	$data['formatted'] = $formatted;
    	            	
    	            	$clean_timeslots[] = $data;
    	            }
                }
            }
            
            return $this->response('json', $clean_timeslots);
            
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

    //provides all of the members from the dimension member in question
    private function list_members($request) {  
    	$service = $request ['srv'];
        $start = (!empty($request['args']['start'])) ? $request['args']['start'] : 0;
        $limit = (!empty($request['args']['limit'])) ? $request['args']['limit'] : null;
        $name = (!empty($request['args']['name'])) ? $request['args']['name'] : "";
        $show_subprojects = array_var($request['args'], 'subprojects', 'show') == "show";

        //Escape name - replace special character ' with \' 
        $name = escape_character($name);
        //This was wrong. Delete:
        //$name = DB::escape($name);
        
        // escape service - replace special character ' with \'
        $service = escape_character($service);
        //This was wrong. Delete:
        //$service = DB::escape($service);
        
        // allow only numeric in start and limit parameters
        if (!is_numeric($start)) {
        	$start = 0;
        }
        if (!is_numeric($limit)) {
        	$limit = null;
        }
        
        $members = array();
        $type = ObjectTypes::instance()->findByName($service);
        
        //Debugging. Delete:
        
        if (!is_null($type))
          $typeId = $type->getId();
        

        switch ($service){
            case "workspace":
                $dimension_id = Dimensions::findByCode('workspaces')->getId();
                break;
            case "customer":
                Env::useHelper('functions', 'crpm');
                $dimension_id = get_customers_dimension()->getId();
                break;
            //@TODO - Bring any and all dimensions that are relevant to the installation
            default:
                $dimension_id = Dimensions::findByCode('customer_project')->getId();
                break;
        }
        
// This we'll delete soon.      
//         $limit_obj = array(
//         		'offset' => $start,
//         		'limit' => $limit,
//         );
        
        $extra_conditions = '';

        if($dimension_id == Dimensions::findByCode('customer_project')->getId() && !$show_subprojects){
            $extra_conditions .= " AND mem.parent_member_id=0";
        }

        //Chek if display_name is abiliable. 
        if (check_column_exists( TABLE_PREFIX ."members", "display_name")) {
            $name_type = 'display_name'; 
        } else {
            $name_type  = 'name';
        }

        if ($name!="") {
            $extra_conditions .= " AND mem.$name_type  LIKE '%".$name."%' ";
        }

        $params = array(
        		'dim_id' => $dimension_id, 
        		'type_id' => $typeId, 
        		'start' => $start, 
        		'limit' => $limit, 
        		'extra_conditions' => $extra_conditions,
        		'exclude_associations_data' => true,
                'sort' => $name_type,
        );
        
        $memberController = new MemberController();
        $object = $memberController->listing($params);
        
        
        foreach ($object["members"] as $m) {
        	$member = Members::getMemberById($m['id']);
        	$memberInfo = array(
        			'id' => $m['id'],
        			'name' => $m['display_name'],
        			'type' => $service,
        			'path' => $member->getPath()
        	//@TODO If name should have custom property concatenated 
        	//@TODO If requested - also return custom Properties
        	
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
        $members = (!empty($request['args']['members']) && count($request['args']['members'])) ? $request['args']['members'] : null;
		$obj_type_name = !empty($request['args']['type']) ? $request['args']['type'] : "task";
		$obj_type = ObjectTypes::findByName($obj_type_name);
		$obj_type_id = $obj_type ? $obj_type->getId() : null;
        $contacts = allowed_users_to_assign_all_mobile($members, $obj_type_id);
        return $this->response('json', $contacts);
    }

    private function list_budgeted_expenses($request) {
        $members = !empty($request['args']['members']) ? $request['args']['members'] : null;
        $query_options = array(
            'member_ids' => $members,
        );
        $exp = Expenses::instance();
        $expenses = $exp->listing($query_options);
        $tmp_objects = array();
        foreach($expenses->objects as $object){
            if($object instanceof Expense){
                $expense_data = $object->getArrayInfo();
                array_push($tmp_objects, $expense_data);
            }
        }

        return $this->response('json', $tmp_objects);
    }

    private function list_expense_types($request) {
        $members = !empty($request['args']['members']) ? $request['args']['members'] : null;
        $query_options = array(
            'member_ids' => $members,
        );

        $actual_expense_types = config_option('set_actual_expense_type');
        $filter_actual_expense_types = array_filter($actual_expense_types);
        $tmp_objects = array();
        $tmp_array=[];
        $i=0;
        foreach($actual_expense_types as $type)
        {
            $tmp_array[$i]['key']=$type;
            $tmp_array[$i]['value']=lang($type);
            $i++;
        }
        array_push($tmp_objects, $tmp_array);
        return $this->response('json', $tmp_objects);
    }

    ///retrive all payment methods from custom propertie
    private function list_payment_methods($request) {

        $cp_values = '';
        $expense_ot = ObjectTypes::findByName('expense');
        $payment_receipt_ot = ObjectTypes::findByName('payment_receipt');
        $payment_receipt_ot->getId();

        $cp = CustomProperties::getCustomPropertyByCode($payment_receipt_ot->getId(), 'quickbooks_payment_method');
        Hook::fire('override_list_custom_property_values', array('cp' => $cp), $cp_values);
        $cp_values_arr=[];
        $tmp_objects=[];
        $cp_values_arr=explode(",",$cp_values);
        
        $tmp_array=[];
        $i=0;
        foreach($cp_values_arr as $type)
        {
            $tmp_value=explode("@",$type);
            $tmp_array[$i]['key']=$tmp_value[0];
            $tmp_array[$i]['value']=$tmp_value[1];
            $i++;
        }

       array_push($tmp_objects, $tmp_array);
       return $this->response('json', $tmp_objects);
    }

    ///retrive all payment methods from custom propertie
    private function list_payment_accounts($request) {

        $cp_values = [];
        $expense_ot = ObjectTypes::findByName('expense');
        $payment_receipt_ot = ObjectTypes::findByName('payment_receipt');
        $payment_receipt_ot->getId();
        //quickbooks_payment_account
        //quickbooks_payment_method
        $cp = CustomProperties::getCustomPropertyByCode($payment_receipt_ot->getId(), 'quickbooks_payment_account');
        Hook::fire('override_list_custom_property_values', array('cp' => $cp), $cp_values);

        $cp_values_arr=[];
		if (is_string($cp_values)) {
			$cp_values_arr=explode(",",$cp_values);
		} else {
			$cp_values_arr=$cp_values;
		}
        $tmp_array=[];
        $tmp_objects=[];
        $i=0;
        foreach($cp_values_arr as $type)
        {
            $tmp_value=explode("@",$type);
            $tmp_array[$i]['key']=$tmp_value[0];
            $tmp_array[$i]['value']=$tmp_value[1];
            $i++;
        }
        
       array_push($tmp_objects, $tmp_array);
      return $this->response('json', $tmp_objects);
    }

    private function get_is_billable_by_budget_expense_id($request) {
        $bud_expense_id = !empty($request['args']['id']) ? $request['args']['id'] : 0;
        $expense = Expenses::instance()->findById($bud_expense_id);
        $is_billable = 0;
        if($expense instanceof Expense){
            $is_billable = $expense->getIsBillable();
        }
        $result = array('is_billable' => $is_billable);

        return $this->response('json', $result);
    }


    /**
     * Retrive list of members for a specific dimension
     * @params mixed options
     * receives $request['args']['params']['dimension_code']
     * @return object list
     */
    private function list_dimension_members($request)
    {
        //get the dimension id from the dimension code
        $dimension_dim = Dimensions::findByCode($request['args']['params']['dimension_code']);//hour_types

        //set auxiliar variables
        $tmp_array=[];
        $tmp_objects=[];
        $i=0;

        //get the members from the dimension id if exists
        if ($dimension_dim instanceof Dimension) {
            $dimensions = Members::instance()->findAll(array(
                "conditions" => "dimension_id = ".$dimension_dim->getId(),
                "order" => " name",
            ));
        
            foreach($dimensions as $dimension)
            {
                $tmp_array[$i]['key']=$dimension->getId();
                $tmp_array[$i]['value']=$dimension->getName();
                $i++;
            }
        }
       
        //push the array to the object
        array_push($tmp_objects, $tmp_array);
       
        //return the object
        return $this->response('json', $tmp_objects);
    }

	/**
	 * Returns the allowed/available dimensions for a given object type form.
	 *
	 * This is intended for mobile clients that need to render dimension selectors
	 * for "time" (timeslot) and "expenses" (payment receipt) forms using the same
	 * rules as the web UI (Dimensions::getAllowedDimensions + user-allowed dims).
	 *
	 * Expected input (any of):
	 * - args.object_type: 'task' | 'timeslot' | 'task_time' | 'payment_receipt' | 'expense_time' | 'expense'
	 * - args.content_object_type_id: numeric object type id
	 */
	private function list_available_dimensions($request) {
		try {
			Env::useHelper('dimension');

			$args = array_var($request, 'args', array());
			$obj_type = array_var($args, 'object_type', '');
			$content_object_type_id = (int) array_var($args, 'content_object_type_id', 0);

			if ($content_object_type_id <= 0) {
				// Backwards/semantic aliases for mobile clients
				$normalized = strtolower(trim((string) $obj_type));
				if ($normalized === 'task_time') {
					$normalized = 'timeslot';
				} else if ($normalized === 'expense_time' || $normalized === 'expense') {
					$normalized = 'payment_receipt';
				}

				if ($normalized !== '') {
					$ot = ObjectTypes::findByName($normalized);
					if ($ot instanceof ObjectType) {
						$content_object_type_id = (int) $ot->getId();
					}
				}
			}

			if ($content_object_type_id <= 0) {
				return $this->response('json', array());
			}

			$user_dimensions = get_user_dimensions_ids(); // dimension_id => true
			$enabled_dimension_ids = config_option('enabled_dimensions');

			$result = array();
			$allowed = Dimensions::getAllowedDimensions($content_object_type_id);
			// Match desktop behavior: allow plugins/config to filter allowed dimensions
			// the same way `render_member_selectors()` does.
			if (is_array($allowed)) {
				Hook::fire("allowed_dimensions_in_member_selector", array(
					'ot' => $content_object_type_id,
					'options' => array(),
				), $allowed);
			}
			if (is_array($allowed)) {
				foreach ($allowed as $dim_row) {
					$dim_id = (int) array_var($dim_row, 'dimension_id', 0);
					$dim_code = (string) array_var($dim_row, 'dimension_code', '');
					if ($dim_id <= 0 || $dim_code === '' || $dim_code === 'feng_persons') {
						continue;
					}
					if (!isset($user_dimensions[$dim_id])) {
						continue;
					}
					if (is_array($enabled_dimension_ids) && !in_array($dim_id, $enabled_dimension_ids)) {
						continue;
					}

					$dim = Dimensions::instance()->findById($dim_id);
					if (!($dim instanceof Dimension)) {
						continue;
					}

					$custom_name = DimensionOptions::getOptionValue($dim_id, 'custom_dimension_name');
					$dimension_name = ($custom_name && trim($custom_name) !== "") ? $custom_name : lang($dim_code);
					Hook::fire('edit_dimension_name', array('dimension' => $dim_id), $dimension_name);

					$default_member = DimensionOptions::instance()->getOptionValue($dim_id, 'default_value');
					$result[] = array(
						'id' => $dim_id,
						'code' => $dim_code,
						'name' => $dimension_name,
						'is_manageable' => (bool) $dim->getIsManageable(),
						'defines_permissions' => (bool) $dim->getDefinesPermissions(),
						'is_required' => (bool) array_var($dim_row, 'is_required', false),
						'is_multiple' => (bool) array_var($dim_row, 'is_multiple', false),
						'default_member_id' => is_numeric($default_member) ? (int) $default_member : 0,
					);
				}
			}

			// Align with desktop forms that use property groups: only expose
			// classification dimensions configured there (e.g. hide Newspaper
			// on expenses when it is not in the payment_receipt property groups).
			$result = filter_dimensions_by_property_groups_for_forms($content_object_type_id, $result);

			return $this->response('json', $result);
		} catch (Exception $e) {
			Logger::log_r("ERROR - ApiController::list_available_dimensions: ".$e->getMessage());
			return $this->response('json', array());
		}
	}

	/**
	 * Returns enabled/visible main modules (tab panels) for the logged user.
	 *
	 * Mobile can use this as the source of truth for which top-level modules to show
	 * (e.g. if "Notes" is disabled in desktop, it should not appear in mobile).
	 *
	 * Mirrors the logic in `PanelController::loadPanels()`:
	 * - tab panel must be enabled
	 * - plugin must be installed+activated (if panel belongs to a plugin)
	 * - user must have tab_panel_permissions via its permission groups
	 */
	private function list_available_modules($request) {
		try {
			$contact_pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV(logged_user()->getId(), false);
			if (!$contact_pg_ids) {
				return $this->response('json', array());
			}

			$sql = "
				SELECT * FROM " . TABLE_PREFIX . "tab_panels
				WHERE
					enabled = 1 AND
					(
						plugin_id IS NULL OR plugin_id=0 OR
						plugin_id IN (SELECT id FROM ".TABLE_PREFIX."plugins WHERE is_installed = 1 AND is_activated = 1)
					)
					AND id IN (SELECT tab_panel_id FROM ".TABLE_PREFIX."tab_panel_permissions WHERE permission_group_id IN ($contact_pg_ids))
				ORDER BY ordering ASC
			";

			$res = DB::execute($sql);
			$modules = array();

			$can_see_billing_info = true;
			Hook::fire('get_can_see_billing_information', array('user' => logged_user()), $can_see_billing_info);

			while ($row = $res->fetchRow()) {
				if (!$can_see_billing_info && $row['id'] == 'income-panel') {
					continue;
				}

				$url_params = trim($row['url_params']) == '' ? array() : json_decode($row['url_params'], true);

				if ($row['default_controller'] == 'member' && array_var($url_params, 'dim_id') != '' && array_var($url_params, 'type_id') != '') {
					$title = TabPanels::getMemberTabTitle($url_params['dim_id'], $url_params['type_id']);
				} else {
					$title = lang($row['title']);
				}

				$modules[] = array(
					'id' => $row['id'],
					'title' => $title,
					'iconCls' => $row['icon_cls'],
					'type' => $row['type'],
					'default_controller' => $row['default_controller'],
					'default_action' => $row['default_action'],
					'url_params' => $url_params,
				);
			}

			return $this->response('json', $modules);
		} catch (Exception $e) {
			Logger::log_r("ERROR - ApiController::list_available_modules: ".$e->getMessage());
			return $this->response('json', array());
		}
	}

	/**
	 * Billing / cost visibility for the logged user (system permissions).
	 *
	 * Same semantics as the web UI: hooks default to true when no plugin overrides them;
	 * with billing_and_cost_permissions active, values come from the user's permission groups.
	 * Mobile clients can call this once (e.g. after login) to hide billing- or cost-related form sections.
	 *
	 * Request: m=get_billing_cost_permissions
	 */
	private function get_billing_cost_permissions($request) {
		try {
			$can_see_billing_information = true;
			Hook::fire('get_can_see_billing_information', array('user' => logged_user()), $can_see_billing_information);
			$can_see_cost_information = true;
			Hook::fire('get_can_see_cost_information', array('user' => logged_user()), $can_see_cost_information);

			return $this->response('json', array(
				// Long names (semantic)
				'can_see_billing_information' => (bool) $can_see_billing_information,
				'can_see_cost_information' => (bool) $can_see_cost_information,
				// Short aliases (backwards-compat for existing mobile/web JS expectations)
				'can_see_billing_info' => (bool) $can_see_billing_information,
				'can_see_cost_info' => (bool) $can_see_cost_information,
			));
		} catch (Exception $e) {
			Logger::log_r("ERROR - ApiController::get_billing_cost_permissions: " . $e->getMessage());
			return $this->response('json', array(
				'can_see_billing_information' => false,
				'can_see_cost_information' => false,
				'can_see_billing_info' => false,
				'can_see_cost_info' => false,
			));
		}
	}

    private function list_labor_categories($request) {

        $labor_categories_dim = Dimensions::findByCode('hour_types');

        if ($labor_categories_dim instanceof Dimension) {
            $labor_categories = Members::instance()->findAll(array(
                "conditions" => "dimension_id = ".$labor_categories_dim->getId(),
                "order" => " name",
            ));
        }

        $tmp_array=[];
        $tmp_objects=[];
        $i=0;
        foreach($labor_categories as $labor_category)
        {
            $tmp_array[$i]['key']=$labor_category->getId();
            $tmp_array[$i]['value']=$labor_category->getName();
            $i++;
        }
        
       array_push($tmp_objects, $tmp_array);
       
       return $this->response('json', $tmp_objects);
    }

    private function list_product_types($request) {
        $member_ids = !empty($request['args']['members']) ? $request['args']['members'] : null;
        $members = array();
        foreach($member_ids as $m_id){
            $mem = Members::instance()->findById($m_id);
            if($mem instanceof Member){
                $members[] = $mem;
            }
        }
        // Use associated members if only one member is selected and it is a project
        $project_ot_id = ObjectTypes::instance()->findByName('project')->getId();
        $use_associated_members = count($members) == 1 && $members[0]->getObjectTypeId() == $project_ot_id;
        if($use_associated_members){
            $project_members = MemberPropertyMembers::getAllAssociatedMemberIds($members[0]->getId(), true);
          
            foreach($project_members as $pm_ids){
				if (!is_array($pm_ids)) {
					$pm_ids = array($pm_ids);
				}
				foreach ($pm_ids as $pm_id) {
					$property_member = Members::instance()->findById($pm_id);
					if($property_member instanceof Member){
						$members[] = $property_member;
					}
				}
            }
        }

        $prod_typ_data = ProductTypes::getFilteredProductTypesData($members);

        return $this->response('json', $prod_typ_data);
    }

    private function get_product_type_by_id($request) {
        $prod_type_id = !empty($request['args']['id']) ? $request['args']['id'] : 0;
        $prod_type= ProductTypes::instance()->findById($prod_type_id);
        $prod_type_data = array();
        if($prod_type instanceof ProductType){
            $prod_type_data = $prod_type->getArrayInfo();
        }

        return $this->response('json', $prod_type_data);
    }

    private function product_types_by_budgeted_expense($request) {
        $bud_expense_id = !empty($request['args']['id']) ? $request['args']['id'] : 0;
        $filtered_product_types = array();
        if($bud_expense_id > 0){
            $expense_items = ExpenseItems::instance()->findAll(array('conditions' => 'expense_id ='.$bud_expense_id));
            foreach($expense_items as $item){
                if($item->getProductTypeId() > 0){
                    $pt = ProductTypes::instance()->findById($item->getProductTypeId());
                    if ($pt instanceof ProductType) {
                        $filtered_product_types[] = $pt->getArrayInfo();
                    }
                }
            }
        }

        return $this->response('json', $filtered_product_types);
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

            //Debugging
            //Logger::log_r("API function: listing. Service is: ". $service . "\n");
            
            
            $order = (!empty($request['args']['order'])) ? $request['args']['order'] : null;
            $order_dir = (!empty($request['args']['order_dir'])) ? $request['args']['order_dir'] : null;
            $members = (!empty($request['args']['members']) && count($request['args']['members'])) ? $request['args']['members'] : null;
            $start = (!empty($request['args']['start'])) ? $request['args']['start'] : 0;
            $limit = (!empty($request['args']['limit'])) ? $request['args']['limit'] : null;
            $group_by_dimension_id = (!empty($request['args']['group_by_dimension_id'])) ? $request['args']['group_by_dimension_id'] : null;
            
            // escape order parameters
            if ($order) {                
                //Wasn't necessary (was actually breaking the system). Delete in future version
                //$order = DB::escape($order);
                
            	if (!in_array(strtolower($order_dir), array("asc","desc"))) {
            		$order_dir = "asc";
            	}
            } else {
            	$order_dir = "";
            }
            
            // allow only numeric in $members parameter
            if ($members && is_array($members)) {
            	$members = array_filter($members, 'is_numeric');
            } else {
            	$members = null;
            }
            
            // allow only numeric in start and limit parameters
            if (!is_numeric($start)) {
            	$start = 0;
            }
            if (!is_numeric($limit)) {
            	$limit = null;
            }
            if (!is_numeric($group_by_dimension_id)) {
            	$group_by_dimension_id = null;
            }

            $query_options = array(
                //'ignore_context' => true,
                'order' => $order,
                'order_dir' => $order_dir,
                'member_ids' => $members,
                'extra_conditions' => '',
                'start' => $start,
                'limit' => $limit
            );

            if ($group_by_dimension_id) {
            	$query_options['dim_order'] = (int) $group_by_dimension_id;
            	$query_options['order_dir'] = 'ASC';
            }

            // COMMON FILTERS: For all content Types
            // only numeric for created by id
            if (!empty($request['args']['created_by_id']) && is_numeric($request['args']['created_by_id'])) {
                $query_options['extra_conditions'] = " AND created_by_id = " . $request['args']['created_by_id'] . " ";
            }

            // TYPE DEPENDENT FILTERS :
            switch ($service) {

                case 'Timeslots' :
                	// only numeric for assigned to
                    if (!empty($request['args']['assigned_to']) && is_numeric($request['args']['assigned_to'])) {
                        $query_options['extra_conditions'] = " AND contact_id = " . $request['args']['assigned_to'] . " ";
                    }
                    if(!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_others_timeslots')){
                        $query_options['extra_conditions'] = " AND contact_id = " . logged_user()->getId() . " ";
                    }

                	break;
                	
                case 'ProjectTasks' :
                	// only numeric for assigned to
                    if (!empty($request['args']['assigned_to']) && is_numeric($request['args']['assigned_to'])) {
                        $query_options['extra_conditions'] = " AND assigned_to_contact_id = " . $request['args']['assigned_to'] . " ";
                    }

                    $task_status_condition = "";
                    $now = DateTimeValueLib::now()->format('Y-m-j 00:00:00');

                    // only numeric for status parameter
                    if (isset($request['args']['status']) && is_numeric($request['args']['status'])) {
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
            
            
            $object_managers = DB::executeAll("SELECT handler_class 
            		FROM ".TABLE_PREFIX."object_types 
            		WHERE `type` IN ('content_object','dimension_object','located')");
            $object_managers = array_flat($object_managers);
            
            // allow only object classes in the $service parameter
            if (!in_array($service, $object_managers)) {
            	throw new Error("Invalid service");
            }


            eval('$service_instance = '.$service.'::instance();');
            $result = $service_instance->listing($query_options);
            //$result = $service->instance()->listing($query_options);

            $temp_objects = array();
            if($service == "Expenses"){
                $temp_objects['budgeted_expenses'] = array();
                $temp_objects['actual_expenses'] = array();
            }

            foreach ($result->objects as $object) {
                if ($service == "ProjectTasks") {
					$object_data = $object->getArrayInfo(1,true);
					$object_data['members_data'] = build_api_members_data($object);
                    array_push($temp_objects, $object_data);
                } elseif($service == "Expenses") {
                    $object_data = $object->getArrayInfo();

                    Env::useHelper('functions', 'expenses2');
                    add_name_plus_dimension_to_expense_data($object_data, $object);

                    $extra_conditions = " AND `expense_id` = ".$object->getObjectId();

                    if(!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_expenses_of_others')){
                        $extra_conditions .= " AND paid_by_id = " . logged_user()->getId();
                    }

                    // Get all the actual expenses that are linked to this expense
                    $result = PaymentReceipts::instance()->listing(array(
                        "order" => 'date',
                        "order_dir" => 'ASC',
                        "start" => 0,
                        "limit" => 0,
                        "ignore_context" => true,
                        "extra_conditions" => $extra_conditions,
                        "count_results" => false,
                        "only_count_results" => false,
                        "member_ids" => $members,
                        "dim_order" => $group_by_dimension_id,
                    ));
                    $payments = array();
                    foreach($result->objects as $payment){
                        $payments[] = $payment->getArrayInfo();
                    }
                    $object_data['payments'] = $payments;
                    $object_data['members_data'] = build_api_members_data($object);
                    array_push($temp_objects['budgeted_expenses'], $object_data);

					$temp_objects['preferences'] = array(
						'hide_cost_in_actual_expenses_form' => user_config_option('hide_cost_in_actual_expenses_form'),
					);

                } else {
                	if ($object instanceof Timeslot) {
                		$object_data = $object->getArrayInfo(false, true);
                	} else {
                		$object_data = $object->getArrayInfo();
                	}
                	$object_data['members_data'] = build_api_members_data($object);
                    array_push($temp_objects, $object_data);
                }
            }

            // Get all the actual expenses(AKA payment_receipts) that are not linked to the 
            // budgeted expenses
            if($service == "Expenses") {
                $object_data = array(
                    "object_id" => 0,
                    "name" => "Not budgeted expenses",
                );
                $extra_conditions = " AND `expense_id` = 0";
                if(!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_expenses_of_others')){
                    $extra_conditions .= " AND paid_by_id = " . logged_user()->getId();
                }
                $result = PaymentReceipts::instance()->listing(array(
                    "order" => 'date',
                    "order_dir" => 'ASC',
                    "start" => $start,
                    "limit" => $limit,
                    "ignore_context" => true,
                    "extra_conditions" => $extra_conditions,
                    "count_results" => false,
                    "only_count_results" => false,
                    "member_ids" => $members,
                    "dim_order" => $group_by_dimension_id,
                ));
                $payments = array();
                foreach($result->objects as $payment){
                    $payment_info = $payment->getArrayInfo();
                    $payment_info['members_data'] = build_api_members_data($payment);
                    $payments[] = $payment_info;
                }
                $object_data['payments'] = $payments;
                array_push($temp_objects['actual_expenses'], $object_data);
				
				$temp_objects['preferences'] = array(
					'hide_cost_in_actual_expenses_form' => user_config_option('hide_cost_in_actual_expenses_form'),
				);
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
                            $task->completeTask();
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
                    	DB::beginWork();
                        $object->trash();
                        Hook::fire('after_object_trash', $object, $null);
                        $response = true;
                        DB::commit();
                    } catch (Exception $e) {
                    	DB::rollback();
                        $response = false;
                    }
                }
            }
        }
        return $this->response('json', $response);
    }

    private function save_object($request) {
        $response = false;
        $additional_members = array();
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
                        	$dd = DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, $request['args']['due_date']);
                        	$dd->add('s', -1*logged_user()->getUserTimezoneValue());
                       		$object->setDueDate($dd);
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

                case 'timeslot' :
                    if ($request ['args'] ['id']) {
                        $object = Timeslots::instance()->findByid($request ['args'] ['id']);
                    } else {
                        $object = null;
                    }
                    if ($object instanceof Timeslot) {
                        if (!empty($request ['args'] ['description'])) {
                            $object->setObjectName($request ['args'] ['description']);
                            $object->setDescription($request ['args'] ['description']);
                        }
						if (!empty($request['args']['start_time']) && !empty($request['args']['end_time']) && isset($request['args']['date'])) {
							try {
								$tz_offset = Timezones::getTimezoneOffsetToApply($object);
								$st = DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, $request['args']['date']);
								$s_time = getTimeValue($request['args']['start_time']);
								$end_date_str = !empty($request['args']['end_date']) ? $request['args']['end_date'] : $request['args']['date'];
								$et = DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, $end_date_str);
								$e_time = getTimeValue($request['args']['end_time']);
								if ($st instanceof DateTimeValue && $et instanceof DateTimeValue && $s_time && $e_time) {
									$st->setHour($s_time['hours']);
									$st->setMinute($s_time['mins']);
									$st->setSecond(0);
									$et->setHour($e_time['hours']);
									$et->setMinute($e_time['mins']);
									$et->setSecond(0);
									$st = new DateTimeValue($st->getTimestamp() - $tz_offset);
									$et = new DateTimeValue($et->getTimestamp() - $tz_offset);
									$object->setStartTime($st);
									$object->setEndTime($et);
								}
							} catch (Exception $e) {
								Logger::log_r("ERROR - Timeslots API: Invalid start/end time input");
							}
						} else {
							if (isset($request['args']['date'])) {
								// if user inputs a date, then update the start date before calculating end date
								try {
									$start_date = DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, $request['args']['date']);
									if ($start_date instanceof DateTimeValue) {
										$start_date->setHour($object->getStartTime()->getHour());
										$start_date->setMinute($object->getStartTime()->getMinute());
										$start_date->setSecond($object->getStartTime()->getSecond());
										$object->setStartTime($start_date);
									}
								} catch (Exception $e) {
									Logger::log_r("ERROR - Timeslots API: Invalid date input: ".$request['args']['date']);
								}
							}
							$worked_minutes = $request['args']['hours']*60 + $request['args']['minutes'];
							$end_time = new DateTimeValue($object->getStartTime()->getTimestamp());
							$end_time->add('m', $worked_minutes);
							$object->setEndTime($end_time);
						}
                    }
                    break;

                case 'expense':
                    if ($request ['args'] ['id']) {
                        $object = PaymentReceipts::instance()->findByid($request ['args'] ['id']);
                    } else {
                        $object = new PaymentReceipt ();
                    }
                    if ($object instanceof PaymentReceipt) {
                        $can_see_billing_information = true;
                        Hook::fire('get_can_see_billing_information', array('user' => logged_user()), $can_see_billing_information);
                        $can_see_cost_information = true;
                        Hook::fire('get_can_see_cost_information', array('user' => logged_user()), $can_see_cost_information);

                        if (!empty($request ['args'] ['name'])) {
                            $object->setObjectName($request ['args'] ['name']);
                        }
                        if (!empty($request ['args'] ['description'])) {
                            $object->setDescription($request ['args'] ['description']);
                        }
                        if (!empty($request ['args'] ['date'])) {
                        	$date = DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, $request['args']['date']);
                        	$date->add('s', -1*logged_user()->getUserTimezoneValue());
                       		$object->setDate($date);
                        }
                        if (!empty($request ['args'] ['quantity'])) {
                            $object->setQuantity($request ['args'] ['quantity']);
                        } else {
                            $object->setQuantity(0);
                        }
                        if (empty($request ['args'] ['currency_id'])) {
                            $c = Currencies::getDefaultCurrencyInfo();
                            $currency_id = $c ? $c['id'] : 1;
                        } else {
                            $currency_id = $request ['args'] ['currency_id'];
                        }
                        $object->setCurrencyId($currency_id);
                        if ($can_see_cost_information) {
                            if (!empty($request ['args'] ['unit_cost'])) {
                                $object->setUnitCost($request ['args'] ['unit_cost']);
                            } else {
                                $object->setUnitCost(0);
                            }
                            if (!empty($request ['args'] ['amount'])) {
                                $object->setAmount($request ['args'] ['amount']);
                            } else {
                                $object->setAmount(0);
                            }
                            $object->setTotalCostWithoutTaxes($object->getAmount());
                        }

                        if ($can_see_billing_information) {
                            if (!empty($request ['args'] ['unit_price'])) {
                                $object->setUnitPrice($request ['args'] ['unit_price']);
                            } else {
                                $object->setUnitPrice(0);
                            }
                            if (!empty($request ['args'] ['total_price'])) {
                                $object->setTotalPrice($request ['args'] ['total_price']);
                            } else {
                                $object->setTotalPrice(0);
                            }
                        }
                        if (!empty($request ['args'] ['paid_by_id']) || $request ['args'] ['paid_by_id'] == 0) {
                            $object->setPaidById($request ['args'] ['paid_by_id']);
                        }
                        if (!empty($request ['args'] ['product_type_id']) || $request ['args'] ['product_type_id'] == 0) {
                            $product_type_id = $request ['args'] ['product_type_id'];
                            $object->setProductTypeId($product_type_id);

                            // Get expense category member using product type
                            $product_type = ProductTypes::getById($product_type_id);
                            $dim_exp_category = Dimensions::findByCode('expense_categories');
                            if($product_type instanceof ProductType && $dim_exp_category instanceof Dimension){
                                $dim_id_exp_category = $dim_exp_category->getId();
                                $expense_category_ptm = ProductTypeMembers::instance()->findOne(array('conditions' => 'product_type_id='.$product_type_id.' AND dimension_id='.$dim_id_exp_category)); 
                                if($expense_category_ptm instanceof ProductTypeMember){
                                    $additional_members[] = $expense_category_ptm->getMemberId();
                                }
                                
                            }
                        }
                        if (!empty($request ['args'] ['expense_id']) || $request ['args'] ['expense_id'] == 0) {
                            $object->setExpenseId($request ['args'] ['expense_id']);
                            $expense_category_dimension = Dimensions::findByCode('expense_categories')->getId();
                            /*
                            // Remove expense category member from object
                            $object_members = $object->getMembers();
                            foreach ($object_members as $member){
                                if($member->getDimensionId() == $expense_category_dimension){
                                    ObjectMembers::removeObjectFromMembers( $object, logged_user(), array($member), array($member->getId()));
                                }
                            }
                            // Assign new expense category to the object
                            if($request ['args'] ['expense_id'] > 0){
                                $expense = Expenses::instance()->findById($request ['args'] ['expense_id']);
                                $expense_members = $expense->getMembers();
                                foreach ($expense_members as $member){
                                    if($member->getDimensionId() == $expense_category_dimension){
                                        $object->addToMembers(array($member));
                                    }
                                }
                            }
                            */
                        }
                        // Task for actual expenses: desktop forms set this via JS from the budgeted expense;
                        // mobile/API clients typically only send expense_id, so inherit task_id when missing.
                        $api_task_id = array_var($request['args'], 'task_id');
                        if (is_numeric($api_task_id) && (int) $api_task_id > 0) {
                            $object->setTaskId((int) $api_task_id);
                        }
                        if ((int) $object->getTaskId() === 0) {
                            $linked_budget_expense_id = array_var($request['args'], 'expense_id');
                            if (is_numeric($linked_budget_expense_id) && (int) $linked_budget_expense_id > 0) {
                                $linked_budget = Expenses::instance()->findById((int) $linked_budget_expense_id);
                                if ($linked_budget instanceof Expense) {
                                    $bud_task_id = (int) $linked_budget->getColumnValue('task_id');
                                    if ($bud_task_id > 0) {
                                        $object->setTaskId($bud_task_id);
                                    }
                                }
                            }
                        }
                        if (!empty($request ['args'] ['billable'])) {
                            $object->setIsBillable(1);
                        } else {
                            $object->setIsBillable(0);
                        }
                        if($request ['args'] ['old_document_id'] != $object->getDocumentId()){
                            FileRepository::deleteFile($object->getDocumentId());
                            $object->setDocumentId($request ['args'] ['old_document_id']);
                        }
                        if (!empty($_POST['file_content_encoded'])) {
                        	// decode and save the file contents
                        	$tmp_path = ROOT."/tmp/".gen_id();
                        	file_put_contents($tmp_path, base64_decode($_POST['file_content_encoded']));
                        	$file_type = $_POST['file_type'];
                        	
                        	// save file in repository
                        	$document_id = FileRepository::addFile($tmp_path, array('type' => $file_type, 'public' => true));
                        	
                        	if ($document_id) {
                        		// delete previous file
	                        	if ($object->getDocumentId() != '') {
	                        		FileRepository::deleteFile($object->getDocumentId());
	                        	}
	                        	
	                        	// set the new file id
                        		$object->setDocumentId($document_id);
                        	}
                        }
                        if (!empty($request ['args'] ['payment_receipt']['expense_type'])) {
                            $object->setExpenseType($request ['args'] ['payment_receipt']['expense_type']);
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
                    if(!empty($request['args']['members'])){
                        $members = $request['args']['members'];
                    }


                    if($request ['srv']=='expense')//if object is expense
                    {
                        //if it has budgeted expense, get all form bugeted
                        if (!empty($request['args']['expense_id'])) {
                            $expenseAux = Expenses::instance()->findById($request['args'] ['expense_id']);
                            if($expenseAux instanceof Expense)
                            {
                                $members = $expenseAux->getMemberIds();
                                // Budgeted expenses can be tied to a task via task_id; that link is not always
                                // present in getMembers()/getMemberIds(), so merge task members for classification.
                                $bud_task_id = (int) $expenseAux->getColumnValue('task_id');
                                if ($bud_task_id > 0) {
                                    $task = ProjectTasks::instance()->findById($bud_task_id);
                                    if ($task instanceof ProjectTask) {
                                        foreach ($task->getMemberIds() as $task_member_id) {
                                            if (!in_array($task_member_id, $members)) {
                                                $members[] = $task_member_id;
                                            }
                                        }
                                    }
                                }
                            }

                        }else if(!empty($request['args']['members'])){
                                $members = $request['args']['members'];
                        }

                        //if it has default aproval status add
                        //get default approval status
						$status_dim = Dimensions::findByCode('status_timesheet');
						if ($status_dim instanceof Dimension) {
							$approval_status_dimension_id = $status_dim->getId();
							if($approval_status_dimension_id>0)
							{
								$default_value = DimensionOptions::instance()->getOptionValue($approval_status_dimension_id, 'default_value');
								if($default_value>0)
								{
									$members[]=$default_value;
								}
							}
						}
                    }

                    //if members exists 
                    if(count($members)>0)
                    {
                        $context = get_context_from_array($members);
                        if(count($members) >= 1) {
                             // Use associated members if only one member is selected and it is a project
                            $project_ot_id = ObjectTypes::instance()->findByName('project')->getId();
                            $member = Members::instance()->findById($members[0]);
                            if($member->getObjectTypeId() == $project_ot_id){
								$skipped_association_codes = array('project_billing_client');
                                $project_members = MemberPropertyMembers::getAllAssociatedMemberIds($member->getId(), true, true, $skipped_association_codes);
                                foreach($project_members as $pm_ids){
                                    if (!is_array($pm_ids)) {
                                        $pm_ids = array($pm_ids);
                                    }
                                    foreach ($pm_ids as $pm_id) {
                                        $property_member = Members::instance()->findById($pm_id);
                                        if($property_member instanceof Member){
                                            $members[] = $property_member->getId();
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $members = array_merge($members, $additional_members);

                    //Check permissions: 
                    if ($request['args']['id'] && $object->canEdit(logged_user()) ||
                            !$request['args']['id'] && $object->canAdd(logged_user(), $context)) {
                        DB::beginWork();
                        $object->save();
                        $object_controller = new ObjectController ();
                        if (!$request['args']['id']) {
                            $object_controller->add_to_members($object, $members);
                        } else if ($object->getObjectTypeName() == 'payment_receipt' || $object->getObjectTypeName() == 'timeslot') {
                            $object_controller->add_to_members($object, $members,null,true,false,true,true);
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
    


	private function add_timeslot($request) {
	
		//if member came by args 
        $members = array_var($request['args'], 'members', array());
        
        //if not, get the task and then the members of the task!
        if(!$members)
        {
            $taskAux = ProjectTasks::instance()->findById($request['args'] ['object_id']);
            if($taskAux instanceof ProjectTask)
            {
                $members = $taskAux->getMemberIds();
            }
        }

		// Get project member
		$project_member = null;
		$project_ot_id = ObjectTypes::findByName('project')->getId();
		foreach($members as $m){
			$member = Members::instance()->findById($m);
			if($member instanceof Member){
				if($member->getObjectTypeId() == $project_ot_id) $project_member = $member;
			}
		}

		// Get client member associated to the project
		if($project_member instanceof Member){
			Env::useHelper('functions', 'crpm');
			$client_member = get_client_member_of_project($project_member);
			if($client_member instanceof Member){
				$members[] = $client_member->getId();
			}
		}

		$ts_date = DateTimeValueLib::now();
		if (isset($request ['args'] ['date'])) {
			try {
				$ts_date = DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, $request ['args'] ['date']);
			} catch (Exception $e) {
				Logger::log_r("ERROR - Timeslots API: Invalid date input: ".$request ['args'] ['date']);
			}
		}

		$parameters = array();
		$parameters['members'] = json_encode($members);
		$parameters['timeslot'] = array(
			'hours' => $request ['args'] ['hours'],
			'minutes' => $request ['args'] ['minutes'],
			'date' => $ts_date,
			'description' => $request ['args'] ['description'],
			'contact_id' => $request ['args'] ['contact_id'],
		);

		if (!empty($request['args']['start_time'])) {
			$parameters['timeslot']['start_time'] = $request['args']['start_time'];
		}
		if (!empty($request['args']['end_time'])) {
			$parameters['timeslot']['specify_end_time'] = 1;
			$parameters['timeslot']['end_time'] = $request['args']['end_time'];
			$end_date = $ts_date;
			if (!empty($request['args']['end_date'])) {
				try {
					$end_date = DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, $request['args']['end_date']);
				} catch (Exception $e) {
					Logger::log_r("ERROR - Timeslots API: Invalid end_date input: ".$request['args']['end_date']);
				}
			}
			$parameters['timeslot']['end_date'] = $end_date;
		}

		// set task id if present
		if (isset($request ['args'] ['object_id'])) {
			$parameters['object_id'] = $request ['args'] ['object_id'];
		}

		$controller = new TimeController();
		$timeslot = $controller->add_timeslot($parameters);
        if($timeslot instanceof Timeslot){

            $this->api_ensure_timeslot_status_timesheet_member($timeslot);
            $timeslot = Timeslots::instance()->findById($timeslot->getId());

            $modified = false;
            Hook::fire('after_api_add_timeslot', array('timeslot' => $timeslot), $modified);
            
            if ($modified && Plugins::instance()->isActivePlugin('advanced_billing')) {
                Env::useHelper('functions', 'advanced_billing');
                calculate_timeslot_rate_and_cost($timeslot);
            }

            return $this->response('json', true);
        }else{
            return $this->response('json', false);
        }
    }

    /**
     * Only if status_dimension_timesheet is active and a default value is set for the
     * status_timesheet dimension (Internal Payment Status), classify the timeslot created via mobile.
     */
    private function api_ensure_timeslot_status_timesheet_member(Timeslot $timeslot) {
        if (!Plugins::instance()->isActivePlugin('status_dimension_timesheet')) {
            return;
        }
        $dim = Dimensions::findByCode('status_timesheet');
        if (!($dim instanceof Dimension)) {
            return;
        }
        $dim_id = (int) $dim->getId();
        $member_id = (int) DimensionOptions::instance()->getOptionValue($dim_id, 'default_value');
        if ($member_id <= 0) {
            return;
        }
        // Don't overwrite an existing classification.
        foreach ($timeslot->getMembers() as $m) {
            if ($m instanceof Member && (int) $m->getDimensionId() === $dim_id) {
                return;
            }
        }
        $member = Members::getMemberById($member_id);
        if (!($member instanceof Member) || (int) $member->getDimensionId() !== $dim_id) {
            return;
        }
        try {
            DB::beginWork();
            ObjectMembers::addObjectToMembers($timeslot->getId(), array($member));
            $timeslot->addToSharingTable();
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Logger::log_r("ERROR - ApiController::api_ensure_timeslot_status_timesheet_member: ".$e->getMessage());
        }
    }
    
    private function add_comment($request) {
    	$_GET['object_id'] = $request ['args'] ['id'];
    	$_POST['comment'] = array('text' => $request ['args'] ['comment']);
    	
    	$controller = new CommentController();
    	$controller->add();
    	
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
     * @return string API result
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

    /**
     * API responds to get data for Project Statistics Widget
     */
    public function get_widget_project_statistics_info() {
        // IMPORTANT save and close sessions to allow other processes to run in parallel
        session_write_close();

        $members = array_var($_REQUEST, 'members', array());
        $res_data = array();
        if (Plugins::instance()->isActivePlugin('crpm')) {
            Env::useHelper('widget_functions', 'crpm');
            ini_set('memory_limit', '1G');

            $task_assignment_conditions = "";
            if (!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks')) {
                $task_assignment_conditions = " AND assigned_to_contact_id = ".logged_user()->getId();
            }

            $tasks = get_tasks_for_statistics_widget($members);
            $task_stats = array('upcoming' => 0, 'late' => 0, 'complete' => 0, 'today' => 0, 'no_due_date' => 0, 'total' => count($tasks));

            $today = DateTimeValueLib::now();
            $today->beginningOfDay();
                
            if (is_array($tasks) && count($tasks) > 0) {
                foreach ($tasks as $obj) {
                    if ($obj['completed_by_id'] > 0) {
                        $task_stats['complete'] = $task_stats['complete'] + 1;
                    } else {
                        $due_date = DateTimeValueLib::makeFromString($obj['due_date']);
                        if ($due_date instanceof DateTimeValue) $due_date->advance(logged_user()->getUserTimezoneValue(), true);
                        if ($due_date->getTimestamp() < 1) {
                            $task_stats['no_due_date'] += 1;
                        } else if ($due_date instanceof DateTimeValue && $due_date->getTimestamp() < $today->getTimestamp()) {
                            $task_stats['late'] = $task_stats['late'] + 1;
                        } else {
                            $task_stats['upcoming'] = $task_stats['upcoming'] + 1;
                        }
                    }
                }
            } 

            $res_data = array(
                array( 
                'name' => " ".lang('upcoming')." ",
                'value' => array_var($task_stats, 'upcoming'),
                'list_url' => 'javascript:og.reload_tasks_list_with_status(15)',
                'color' => '#fbc85a' // Yellow
                ) , array( 
                'name' => " ".lang('overdue')." ",
                'value' => array_var($task_stats, 'late'),
                'list_url' => 'javascript:og.reload_tasks_list_with_status(11)',
                'color' => '#fd7066' // Red
                ) , array( 
                'name' => " ".lang('completed')." ",
                'value' => array_var($task_stats, 'complete'),
                'list_url' => 'javascript:og.reload_tasks_list_with_status(1)',
                'color' => '#0cbe9b' // Green
                ) , array( 
                'name' => " ".lang('without due date')." ",
                'value' => array_var($task_stats, 'no_due_date'),
                'list_url' => 'javascript:og.reload_tasks_list_with_status(14)',
                'color' => '#b4b3b3' // Grey
                )  
            );
        }

        ajx_current("empty");
        $this->setLayout("json");
		$this->renderText(json_encode($res_data), true);
    }

}
