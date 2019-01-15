<?php

/**
 * Dimension controller
 *
 * @version 1.0
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class DimensionController extends ApplicationController {

	/**
	 * Prepare this controller
	 *
	 * @param void
	 * @return ProjectController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct
	

	/**
	 * Gets all the dimensions that user can see plus those wich must be displayed in the panels 
	 * 
	 */
	function get_context(){
		ajx_current("empty");

		// User config root dimensions
		$dids = explode ("," ,user_config_option('root_dimensions', null, logged_user()->getId() ));
		foreach ($dids as  $id) {
			if (is_numeric($id) && $id > 0 ) {
				$user_root_dimensions[$id] = true ;
			}
		}		
		
		//All dimensions
		$all_dimensions = Dimensions::findAll(array('order'=>'default_order ASC , id ASC'));
		$dimensions_to_show = array();
		
		foreach ($all_dimensions as $dim){
			if (!isset($user_root_dimensions[$dim->getId()])) $user_root_dimensions[$dim->getId()] = false;
		}
		
		$contact_pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV(logged_user()->getId(),false);
		
		foreach ($all_dimensions as $dim){
			$did = $dim->getId();
			if (isset($user_root_dimensions) && count($user_root_dimensions)) {
				if ( isset($user_root_dimensions[$did]) && $user_root_dimensions[$did] ){
					$dim->setIsRoot(true);
				}else{
					$dim->setIsRoot(false);
				}
			}
					
			$added=false;
			
			if (!$dim->getDefinesPermissions()){
				$dimensions_to_show ['dimensions'][] = $dim;
				$added = true;
			}
			else{
				/*if dimension does not deny everything for each contact's PG, show it*/
				if (!$dim->deniesAllForContact($contact_pg_ids)  || logged_user()->isAdministrator()){
					$dimensions_to_show ['dimensions'][] = $dim;
					$added = true;
				}
			}
			if ($dim->getIsRoot()&& $added){
					$dimensions_to_show ['is_root'][] = true;
			}
			
		}
		return $dimensions_to_show;
	}
	
	/** 
	 * Returns the dimension members the user has permission to with latest activity so that they can be displayed
	 *  
	 * $dimension_id = id of the dimension to look at
	 * $object_type_id = id of the dimension member type
	 * $logs_amount_range = amount of logs to look for in the application_logs
	 * $minimum_display = minimum amount of dimension members to return, otherwise return all
	 * $maximum_display = maximum amount of dimension members to return  
	*/
	function latest_active_dimension_members($dimension_id, $object_type_id, $allowed_member_type_ids = null, $logs_amount_range="1000", $minimum_display, $maximum_display) {
		//sql query created to filter the members with latest activity through the $extra_conditions variable below
        $sql = "SELECT DISTINCT `".TABLE_PREFIX."object_members`.`member_id`,`".TABLE_PREFIX."application_logs`.`id`
				FROM `".TABLE_PREFIX."application_logs`, `".TABLE_PREFIX."object_members`, `".TABLE_PREFIX."members`
				WHERE (`".TABLE_PREFIX."application_logs`.`rel_object_id` = `".TABLE_PREFIX."object_members`.`object_id`) 
					  AND (`".TABLE_PREFIX."object_members`.`member_id` = `".TABLE_PREFIX."members`.`id` AND `".TABLE_PREFIX."members`.`object_type_id` = '".mysqli_real_escape_string(DB::connection()->getLink(), $object_type_id)."')
				ORDER BY `".TABLE_PREFIX."application_logs`.`id` DESC LIMIT ".mysqli_real_escape_string(DB::connection()->getLink(), $logs_amount_range);
        $members_to_filter = DB::executeAll($sql);
        $member_amount = 0;
        //if the dimension members in the search are below the minimum amount to be displayed, show all dimension members the user can access to
        if (is_array($members_to_filter)){
        	$members_to_filter_string = '';
        	foreach ($members_to_filter as $row) {
        		//do not repeat member_ids that already are in the array
        		if (!stristr($members_to_filter_string, ($row['member_id']))){
        			$members_to_filter_string .= "'".$row['member_id']."',";
        			$member_amount++;
        		}
        		//show only up to the limit specified
        		if ($member_amount >= $maximum_display) break;
        	}
        	$members_to_filter_string = substr_replace($members_to_filter_string ,"",-1);
        }
        if ($member_amount > $minimum_display){
            $extra_conditions = " AND id IN (".$members_to_filter_string.")";
		}else{
			$extra_conditions = "";
		}
        $list_dim_members = $this->initial_list_dimension_members($dimension_id,null, array($object_type_id), false, $extra_conditions);
        return $list_dim_members['members'];
	}
	
	/**
	 * Returns all the members to be displayed in the panel that corresponds to the dimension for which the id is received by
	 * parameter.
	 * It is called when the application is first loaded.
	 * @todo: return only the members that are going to be retrieved
	 * @todo: add a function to retrieve the rest of the members - dimension_members - and make it more efficient
	 * @todo: add a funciton to retrieve a specific set of members
	 * @todo: check where this function is called
	 * @todo: check (and fix) that the system doesn't use the left-panel navigation tree to get member's data
	 *
	 */
	function initial_list_dimension_members($dimension_id, $object_type_id, $allowed_member_type_ids = null, $return_all_members = false, $extra_conditions = "", $limit=null, $return_member_objects = false, $order=null, $return_only_members_name=false, $filter_by_members=array(), $access_level=ACCESS_LEVEL_READ, $use_member_cache=false){
		$list_was_filtered_by = array();
		$allowed_member_types = array();
		$item_object = null ;
		if(logged_user()->isAdministrator())$return_all_members=true;
		$contact_pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV(logged_user()->getId(),false);
		$dimension = Dimensions::getDimensionById($dimension_id);
		
		if ($object_type_id != null){
			$dimension_object_type_contents = $dimension->getObjectTypeContent($object_type_id);
			foreach ($dimension_object_type_contents as $dotc){
				$dot_id = $dotc->getDimensionObjectTypeId();
				if (is_null($allowed_member_type_ids) || in_array($dot_id, $allowed_member_type_ids)) {
					$allowed_member_types[] = $dot_id;
				}
			}
			
			$object_type = ObjectTypes::findById($object_type_id);
			if ($object_type instanceof ObjectType && $object_type->getType() == 'dimension_object' ) {
				eval('$ot_manager = '.$object_type->getHandlerClass().'::instance();');
				if (isset($ot_manager)) {
					eval('$item_object = new '.$ot_manager->getItemClass().'();');
				}
			}
		}
		
		$extra_conditions .= " AND archived_by_id=0";
		
		if ($dimension instanceof Dimension){
			if (count($allowed_member_types) > 0) {
				$extra_conditions = " AND object_type_id IN (".implode(",",$allowed_member_types).")" . $extra_conditions;
			}
			
			if (count($filter_by_members) > 0) {
				$filters = array();
				foreach ($filter_by_members as $fm) {
					$dma = DimensionMemberAssociations::findOne(array(
						'conditions' => "dimension_id=".$fm->getDimensionId()." AND object_type_id=".$fm->getObjectTypeId()." AND associated_dimension_id=$dimension_id".
							" OR associated_dimension_id=".$fm->getDimensionId()." AND associated_object_type_id=".$fm->getObjectTypeId()." AND dimension_id=$dimension_id"
					));
					if ($dma instanceof DimensionMemberAssociation) {
						$filters[] = $fm;
					}
				}
				
				if (count($filters) > 0) {
					$real_applied_filters = null;
					$filter_by_members_sql = $this->get_association_filter_conditions($dimension, $filters, $real_applied_filters);
					$extra_conditions .= $filter_by_members_sql;
					
					if ($real_applied_filters) {
						$list_was_filtered_by = $real_applied_filters;
					}
				}
			}
			
			$parent = 0;
			if (is_null($order)) $order = "parent_member_id, name";
			if (!$dimension->getDefinesPermissions() || $dimension->hasAllowAllForContact($contact_pg_ids) || $return_all_members){
				$all_members = $dimension->getAllMembers(false, $order, true, $extra_conditions, $limit);
			}
			else if ($dimension->hasCheckForContact($contact_pg_ids)){
				if($use_member_cache){
					//use the contact member cache
					$params = array(
							"dimension" => $dimension,
							"contact_id" => logged_user()->getId(),
							"parent_member_id" => 0,
							"start" => $limit['offset'],
							"limit" => $limit['limit'],
							"extra_condition" => $extra_conditions,
							"order" => '`name`',
							"order_dir" => 'ASC',
					);
					$all_members = ContactMemberCaches::getAllMembersWithCachedParentId($params);
					
				}else{
					$member_list = $dimension->getAllMembers(false, $order, true, $extra_conditions, $limit);
					$allowed_members = array();
					foreach ($member_list as $dim_member){
						if (ContactMemberPermissions::instance()->contactCanAccessMemberAll($contact_pg_ids, $dim_member->getId(), logged_user(), $access_level)) {
							$allowed_members[] = $dim_member;
						}
					}
					$all_members = $allowed_members;
				}
			}
			if (!isset($all_members)) {
				$all_members = array();	
			}
			
			$tmp_array = array();
			foreach ($filter_by_members as $filter_id) {
				if ($filter_id) $tmp_array[] = $filter_id;
			}
			$filter_by_members = $tmp_array;
			
			if ($return_member_objects) {
				$result = array('members' => $all_members, 'list_was_filtered_by' => $list_was_filtered_by);
			} else {
				$members_result = $this->buildMemberList($all_members, $dimension, $allowed_member_type_ids,$allowed_member_types, $item_object, $object_type_id, $return_only_members_name);
				$result = array('members' => $members_result, 'list_was_filtered_by' => $list_was_filtered_by);
			}
			return $result;
		}
		return null;
	}
	
	/**
	 * @param Dimension $dimension The dimension to load its members
	 * @param array $selected_members The member ids that will filter the member list
	 * @param array $members_used_to_filter The subset of $selected_members that was actually used to filter
	 */
	function get_association_filter_conditions($dimension, $selected_members, &$members_used_to_filter) {
		$sql_str = "";
		$mem_ids = array();
		$members_used_to_filter = array();
		
		foreach ($selected_members as $member) {
			if (!$member instanceof Member) continue;
			$association_ids = DimensionMemberAssociations::getAllAssociationIds($member->getDimensionId(), $dimension->getId());
			if (count($association_ids) == 0) continue;
				
			$associations = DimensionMemberAssociations::findAll(array('conditions' => 'id IN ('.implode(',', $association_ids).')'));
	
			if (count($associations) > 0) {
				$associated_members_ids = array();
	
				$filter_by_mem_ids = array($member->getId());
				$child_ids = Members::instance()->getAllChildrenInHierarchy(array($member->getId()), true);
				$filter_by_mem_ids = array_merge($filter_by_mem_ids, $child_ids);
				$filter_by_mem_ids_csv = implode(',', array_filter($filter_by_mem_ids));
				
				foreach ($associations as $assoc){ /* @var $assoc DimensionMemberAssociation */
					if ($assoc->getDimensionId() == $dimension->getId()) {
						$tmp_ids_csv = MemberPropertyMembers::getAllMemberIds($assoc->getId(), $filter_by_mem_ids_csv);
					} else {
						$tmp_ids_csv = MemberPropertyMembers::getAllPropertyMemberIds($assoc->getId(), $filter_by_mem_ids_csv);
					}
					$mem_ids = array_merge($mem_ids, explode(',', $tmp_ids_csv));
					
					if ($tmp_ids_csv != "") {
						$members_used_to_filter[] = $member->getId();
					}
				}
			}
		}
		$mem_ids = array_filter(array_unique($mem_ids));
		if (count($mem_ids) > 0) {
			// include parents, ensure that get_member_childs filters by selected members
			$parent_ids = array();
			$p_ids_tmp = DB::executeAll("SELECT parent_member_id FROM ".TABLE_PREFIX."members WHERE id IN (". implode(',', $mem_ids) .")");
			$p_ids_tmp = array_filter(array_flat($p_ids_tmp));
			
			while ($p_ids_tmp && count($p_ids_tmp) > 0) {
				$parent_ids = array_merge($parent_ids, $p_ids_tmp);
				$p_ids_tmp = DB::executeAll("SELECT parent_member_id FROM ".TABLE_PREFIX."members WHERE id IN (". implode(',', $p_ids_tmp) .")");
				$p_ids_tmp = array_filter(array_flat($p_ids_tmp));
			}
			if (count($parent_ids) > 0) {
				$mem_ids = array_merge($mem_ids, $parent_ids);
				$mem_ids = array_filter(array_unique($mem_ids));
			}
			
			$sql_str = " AND id IN (". implode(',', $mem_ids) .")";
		} else {
			// if is filtering but there is no association then return empty result
			if (isset($association_ids) && count($association_ids) > 0) {
				$sql_str = " AND false";
			}
		}
	
		return $sql_str;
	}
	

	
	
	function initial_list_dimension_members_tree() {
		$dimension_id = array_var($_REQUEST, 'dimension_id');
		$checkedField = (array_var($_REQUEST, 'checkboxes'))?"checked":"_checked";
		$objectTypeId = array_var($_REQUEST, 'object_type_id', null );
		$offset = array_var($_REQUEST, 'offset', 0);
		$limit = array_var($_REQUEST, 'limit', 100);
		
		$allowedMemberTypes = json_decode(array_var($_REQUEST, 'allowedMemberTypes', null ));	
		if (!is_array($allowedMemberTypes)) {
			$allowedMemberTypes = null;
		}
		
		$only_names = array_var($_REQUEST, 'onlyname', false);
		
		$name = trim(array_var($_REQUEST, 'query', ''));
		$extra_cond = $name == "" ? "" : " AND name LIKE '%".$name."%'";
		
		$return_all_members = false;
		
		$selected_member_ids = json_decode(array_var($_REQUEST, 'selected_ids', "[0]"));
		$selected_members = Members::findAll(array('conditions' => 'id IN ('.implode(',',$selected_member_ids).')'));
		
		$limit_obj = array(
			'offset' => $offset,
			'limit' => $limit + 1,
		);
		
		$list_dim_members = $this->initial_list_dimension_members($dimension_id, $objectTypeId, $allowedMemberTypes, $return_all_members, $extra_cond, $limit_obj, false, null, $only_names, $selected_members);
		$memberList = $list_dim_members['members'];
		
		// add view more and remove last element
		$more_nodes_left = false;
		if (count($memberList) > $limit) {
			$more_nodes_left = true;
			array_pop($memberList);
		}
		
		// updates the name of the members using the configuration if exists
		build_member_list_text_to_show_in_trees($memberList);
		
		$tree = buildTree($memberList, "parent", "children", "id", "name", $checkedField);
		
		ajx_current("empty");
		ajx_extra_data(array('dimension_members' => $tree, 'dimension_id' => $dimension_id, 'more_nodes_left' => $more_nodes_left));
	}
	
	//return only root members
	function initial_list_dimension_members_tree_root() {
		$dimension_id = array_var($_REQUEST, 'dimension_id');
		$checkedField = (array_var($_REQUEST, 'checkboxes'))?"checked":"_checked";
		$objectTypeId = array_var($_REQUEST, 'object_type_id', null );
		$offset = array_var($_REQUEST, 'offset', 0);
		$limit = array_var($_REQUEST, 'limit', 100);
	
		$allowedMemberTypes = json_decode(array_var($_REQUEST, 'allowedMemberTypes', null ));
		if (!is_array($allowedMemberTypes)) {
			$allowedMemberTypes = null;
		}
	
		$only_names = array_var($_REQUEST, 'onlyname', false);
	
		$name = trim(array_var($_REQUEST, 'query', ''));
		
		$extra_cond = $name == "" ? "" : " AND name LIKE '%".$name."%'";
		
		$filter_ids = array_var($_REQUEST, 'filter_by_ids');
		if ($filter_ids) {
			$filter_ids_arr = array_filter(explode(',', $filter_ids));
			if (is_array($filter_ids_arr) && count($filter_ids_arr) > 0) {
				$extra_cond .= " AND id IN (".implode(',', $filter_ids_arr).") ";
			}
		}
		
		$dimension = Dimensions::getDimensionById($dimension_id);
		
		$use_member_cache= true;
		//Super admins are not using the contact member cache
		if(logged_user()->isAdministrator() || !$dimension->getDefinesPermissions()){
			$extra_cond .= "AND `parent_member_id`=0";
			$use_member_cache= false;
		}
		$return_all_members = false;
	
		$selected_member_ids = json_decode(array_var($_REQUEST, 'selected_ids', "[0]"));
		$selected_members = Members::findAll(array('conditions' => 'id IN ('.implode(',',$selected_member_ids).')'));
		
		Hook::fire('list_dimension_members_tree_modify_member_filter', array('dimension' => $dimension), $selected_members);
		
		// check if this dimension has to be filtered by the selected members
		$real_sel_members = array();
		foreach ($selected_members as $sel_mem) {
			$dma = DimensionMemberAssociations::findOne(array(
				'conditions' => "dimension_id=".$sel_mem->getDimensionId()." AND object_type_id=".$sel_mem->getObjectTypeId()." AND associated_dimension_id=$dimension_id"
			));
			if ($dma instanceof DimensionMemberAssociation) {
				$assoc_config = $dma->getConfig();
				if (array_var($assoc_config, 'dont_filter_associated_selector')) continue;
			}
			$real_sel_members[] = $sel_mem;
		}
		$selected_members = $real_sel_members;
		
		$limit_obj = array(
			'offset' => $offset,
			'limit' => $limit + 1,
		);
		
		$list_dim_members = $this->initial_list_dimension_members($dimension_id, $objectTypeId, $allowedMemberTypes, $return_all_members, $extra_cond, $limit_obj, false, null, $only_names, $selected_members,null,$use_member_cache);
		$memberList = $list_dim_members['members'];
		$list_was_filtered_by = $list_dim_members['list_was_filtered_by'];
		
		// add view more and remove last element
		$more_nodes_left = false;
		if (count($memberList) > $limit) {
			$more_nodes_left = true;
			array_pop($memberList);
		}
		
		// updates the name of the members using the configuration if exists
		build_member_list_text_to_show_in_trees($memberList);
		
		$tree = buildTree($memberList, "parent", "children", "id", "name", $checkedField);
	
		ajx_current("empty");
		
		//$dids = explode ("," ,user_config_option('root_dimensions', null, logged_user()->getId() ));
		//if(in_array($dimension_id, $dids)){
		ajx_extra_data(array(
				'dimension_members' => $tree, 'dimension_id' => $dimension_id, 
				'dimensions_root_members' => true, 'more_nodes_left' => $more_nodes_left,
				'list_was_filtered_by' => $list_was_filtered_by,
		));
		//}
	}
	
	//serach members by name
	function search_dimension_members_tree() {
		$dimension_id = array_var($_REQUEST, 'dimension_id');
		$dimension = Dimensions::getDimensionById($dimension_id);
		$name = trim(array_var($_REQUEST, 'query', ''));
		$random = trim(array_var($_REQUEST, 'random', 0));
		$start = array_var($_REQUEST, 'start' , 0);
		$limit = array_var($_REQUEST, 'limit');
		$order = array_var($_REQUEST, 'order', 'id');
		$parents = array_var($_REQUEST, 'parents' , true);
		$ignore_context_filters = array_var($_REQUEST, 'ignore_context_filters');
		$filter_ids = array_var($_REQUEST, 'filter_by_ids');
		
		$allowed_member_types_str = array_var($_REQUEST, 'allowed_member_types' , '');
		if ($allowed_member_types_str != '') {
			$allowed_member_types = explode(',', $allowed_member_types_str);
		} else {
			$allowed_member_types = array();
		}
		
		$ids_filter_sql = "";
		if ($filter_ids) {
			$filter_ids_arr = array_filter(explode(',', $filter_ids));
			if (is_array($filter_ids_arr) && count($filter_ids_arr) > 0) {
				$ids_filter_sql = " AND id IN (".implode(',', $filter_ids_arr).") ";
			}
		}
		
		if(strlen($name) > 0 || $random){
			//get the member list
			//Super admins are not using the contact member cache
			if(logged_user()->isAdministrator() || !$dimension->getDefinesPermissions()){
				$limit_t = '';
				if(isset($limit)){
					$limit_t = $limit+1;
				}
				
				$search_name_cond = "";
				if(!$random){
				    $name = mysqli_real_escape_string(DB::connection()->getLink(), $name);
					$search_name_cond = " AND name LIKE '%".$name."%'";
				}
				
				// if there is a member type configured to show any other properties with the name, then search by them too
				append_other_properties_search_conditions($dimension, $name, $search_name_cond);
				
				$member_type_cond = "";
				if (count($allowed_member_types) > 0) {
					$member_type_cond = " AND object_type_id IN (".implode(',', $allowed_member_types).")";
				}
				
				$more_conds = "";
				if (!$ignore_context_filters) {
					$filter_by_members = array();
					$context = active_context();
					foreach ($context as $selection) {
						if ($selection instanceof Member) $filter_by_members[] = $selection;
					}
					
					$real_applied_filters = null;
					$filter_by_members_sql = $this->get_association_filter_conditions($dimension, $filter_by_members, $real_applied_filters);
					$more_conds .= $filter_by_members_sql;
				}
				
				$memberList = Members::findAll(array('conditions' => array("`dimension_id`=? AND archived_by_id=0 $ids_filter_sql $search_name_cond $member_type_cond $more_conds", $dimension_id), 'order' => '`'.$order.'` ASC', 'offset' => $start, 'limit' => $limit_t));
				
				//include all parents
				//Check hierarchy
				if($parents){
					$members_ids = array();
					$parent_members = array();
					foreach ($memberList as $mem){
						$members_ids[] = $mem->getId();
					}
				/*	foreach ($memberList as $mem){
						$parents = $mem->getAllParentMembersInHierarchy(false);
						foreach ($parents as $parent){
							if(!in_array($parent->getId(), $members_ids)){
								$members_ids[] = $parent->getId();	
								$parent_members[] = $parent;
							}
						}
					}*/
					
					$parent_members = Members::getAllParentsInHierarchy($members_ids);
					
					$memberList = array_merge($memberList,$parent_members);
				}
			}else{
				//Use contact member cache
				$params = array();
				$params["dimension"] = $dimension;
				$params["contact_id"] = logged_user()->getId();
				$params["get_all_parent_in_hierarchy"] = $parents;
				$params["order"] = $order;
				if(!$random){
					$params["member_name"] = $name;
				}
				if(isset($limit)){
					$params["start"] = $start;
					$params["limit"] = $limit + 1;
				}
				
				$params["extra_condition"] = " AND m.archived_by_id=0 ";
				if (count($allowed_member_types) > 0) {
					$params["extra_condition"] .= "$ids_filter_sql AND m.object_type_id IN (".implode(',', $allowed_member_types).")";
				}
				
				// if there is a member type configured to show any other properties with the name, then search by them too
				$additional_query_string_conditions = "";
				append_other_properties_search_conditions($dimension, $name, $additional_query_string_conditions);
				if (trim($additional_query_string_conditions) != "") {
					$additional_query_string_conditions = str_replace(TABLE_PREFIX."members.", "m.", $additional_query_string_conditions);
					unset($params["member_name"]);
					$params["extra_condition"] .= " $additional_query_string_conditions";
				}
				
				$memberList = ContactMemberCaches::getAllMembersWithCachedParentId($params);
			}
			
			// updates the name of the members using the configuration if exists
			build_member_list_text_to_show_in_trees($memberList);
			
			//show more
			$show_more = false;
			if(isset($limit) && count($memberList) > $limit){
				array_pop($memberList); 
				$show_more = true;
			}			
			
			if(!empty($memberList)){
				$allMemebers = $this->buildMemberList($memberList, $dimension, array(),array(), null, null);
						
				if(isset($limit)){
					ajx_extra_data(array('show_more' => $show_more));
				}

				$row = "search-result-row-medium";
				if(!$dimension->canHaveHierarchies()){
					$row = "search-result-row-small";
				}
				ajx_extra_data(array('row_class' => $row));
				ajx_extra_data(array('members' => $allMemebers));
			}
		}
		ajx_extra_data(array('query' => $name));
		ajx_extra_data(array('dimension_id' => $dimension_id));
		ajx_extra_data(array('time' => array_var($_REQUEST, 'time')));
		ajx_current("empty");			
	}
	
	function reload_dimensions_js () {
		ajx_current("empty");
		$dimensions = Dimensions::findAll();
		
		$ot_extra_cond = "";
		Hook::fire('available_object_types_extra_cond', null, $ot_extra_cond);
		$ots = ObjectTypes::getAvailableObjectTypesWithTimeslots($ot_extra_cond);
		
		$dims_info = array();
		$perms_info = array();
		foreach ($dimensions as $dim) {
			$dims_info[$dim->getId()] = array();
			$perms_info[$dim->getId()] = array();
			$members = $dim->getAllMembers();
			foreach ($members as $member) {
				$mem_info = array();
				$mem_info['id'] = $member->getId();
				$mem_info['name'] = clean($member->getName());
				$mem_info['ot'] = $member->getObjectTypeId();
				$mem_info['path'] = $dim->getIsManageable() ? trim(clean($member->getPath())) : '';
				$mem_info['ico'] = $member->getIconClass();
				$mem_info['color'] = $member->getMemberColor();
				$mem_info['parent_id'] = $member->getParentMemberId();
				$mem_info['archived'] = $member->getArchivedById();
								
				$p_info = array();
				if ($dim->getIsManageable()) {
					foreach ($ots as $ot) {
						$p_info[$ot->getId()] = $dim->getDefinesPermissions() ? can_read(logged_user(), array($member), $ot->getId()): true;
					}
				}
				
				$dims_info[$dim->getId()][$member->getId()] = $mem_info;
				$perms_info[$dim->getId()][$member->getId()] = $p_info;
			}
		}
		ajx_extra_data(array("dims" => $dims_info, "perms" => $perms_info));
	}
	
	function load_dimensions_info() {
		ajx_current("empty");
		$dimensions = Dimensions::findAll();
		$enabled_dimension_ids = config_option('enabled_dimensions');
		
		$dim_names = array();
		foreach ($dimensions as $dim) {
			if (!in_array($dim->getId(), $enabled_dimension_ids)) continue;
			$dim_name = clean($dim->getName());
			
			$dim_names[$dim->getId()] = array("name" => $dim_name);
		}
		ajx_extra_data(array("dim_names" => $dim_names));
	}
	
	//return all childs of a member
	function get_member_childs() {
		$mem_id = array_var($_GET, 'member');
		$offset = array_var($_REQUEST, 'offset', 0);
		$limit = array_var($_REQUEST, 'limit', 100);
		$ignore_context_filters = array_var($_REQUEST, 'ignore_context_filters');
		$new_limit = $limit + 1;
		
		if ((function_exists('logged_user') && logged_user() instanceof Contact && ContactMemberPermissions::contactCanAccessMemberAll(implode(',', logged_user()->getPermissionGroupIds()), $mem_id, logged_user(), ACCESS_LEVEL_READ))) {
			$mem = Members::getMemberById($mem_id);
			if($mem instanceof Member){
				
				$dim_filter_conds = "";
				if (!$ignore_context_filters) {
					// check for other dimensions filtering this dimension
					$selected_members = array();
					foreach (active_context() as $selection) {
						if ($selection instanceof Member && $selection->getDimensionId() != $mem->getDimensionId()) {
							$selected_members[] = $selection;
						}
					}
					if (count($selected_members) > 0) {
						$applied_filters = null;
						$dim_filter_conds = $this->get_association_filter_conditions($mem->getDimension(), $selected_members, $applied_filters);
					}
				} else {
					// check for other dimensions filtering this dimension in the member selector
					$selected_member_ids_csv = array_var($_REQUEST, 'selected_member_ids');
					if ($selected_member_ids_csv != "") {
						$selected_members = array();
						$selected_member_ids = array_filter(explode(",", $selected_member_ids_csv));
						if (count($selected_member_ids) > 0) {
							$selected_members = Members::findAll(array("conditions"=>"id IN (".implode(',', $selected_member_ids).")"));
						}
						if (count($selected_members) > 0) {
							$applied_filters = null;
							$dim_filter_conds = $this->get_association_filter_conditions($mem->getDimension(), $selected_members, $applied_filters);
						}
					}
				}
				
				//Do not use contact member cache for superadmins
				if(!logged_user()->isAdministrator() && $mem->getDimension()->getDefinesPermissions()){
					//use the contact member cache
					$dimension = $mem->getDimension();
					$params = array(
							"dimension" => $dimension,
							"contact_id" => logged_user()->getId(),
							"parent_member_id" => $mem->getId(),
							"extra_condition" => " $dim_filter_conds AND m.archived_by_id=0 ",
							"start" => $offset,
							"limit" => $new_limit,
							"order" => '`name`',
							"order_dir" => 'ASC',
					);
					$childs = $member_cache_list = ContactMemberCaches::getAllMembersWithCachedParentId($params);
				}else{
					$childs = Members::getSubmembers($mem, false, " $dim_filter_conds AND archived_by_id=0 ", null, null, $offset, $new_limit);
				}
				
				$more_nodes_left = false;
				if (count($childs) > $limit) {
					$more_nodes_left = true;
					array_pop($childs);
				}
				
				// build resultant member list
				$members = $this->buildMemberList($childs, $mem->getDimension(),  null, null, null, null);
				
				ajx_extra_data(array("members" => $members, "dimension" => $mem->getDimensionId(), "member_id" => $mem->getId(), "more_nodes_left" => $more_nodes_left));			
			}
		} else {
			$mem = Members::getMemberById($mem_id);
			$dim_id = $mem instanceof Member ? $mem->getDimensionId() : 0;
			ajx_extra_data(array("members" => array(), "dimension" => $dim_id, "member_id" => $mem_id, "more_nodes_left" => false));
		}
		ajx_current("empty");
	}
	
	//return all parents of a member
	function get_member_parents() {
		$mem_id = array_var($_GET, 'member');
		if ((function_exists('logged_user') && logged_user() instanceof Contact && ContactMemberPermissions::contactCanAccessMemberAll(implode(',', logged_user()->getPermissionGroupIds()), $mem_id, logged_user(), ACCESS_LEVEL_READ))) {
			$mem = Members::getMemberById($mem_id);
			if($mem instanceof Member){
				$parents = $mem->getAllParentMembersInHierarchy(true);
				
				$members = $this->buildMemberList($parents, $mem->getDimension(),  null, null, null, null);
				
				// updates the name of the members using the configuration if exists
				build_member_list_text_to_show_in_trees($members);
				
				ajx_extra_data(array("member_id" => $mem_id));
				ajx_extra_data(array("members" => $members));
				ajx_extra_data(array('dimension_id' => $mem->getDimensionId()));
			}
		}
		ajx_current("empty");
	}
	
	//return all members in member_ids array 
	function get_members() {
		$member_ids = json_decode(array_var($_REQUEST, 'member_ids', null ));
		if (!is_array($member_ids)) {
			$member_ids = null;
		}else{		
			$all_members = array();
			$all_members_ids = array();
			foreach ($member_ids as $m) {
				if ((function_exists('logged_user') && logged_user() instanceof Contact && ContactMemberPermissions::contactCanAccessMemberAll(implode(',', logged_user()->getPermissionGroupIds()), $m, logged_user(), ACCESS_LEVEL_READ))) {
					$mem = Members::getMemberById($m);
					if($mem instanceof Member){
						$parents = $mem->getAllParentMembersInHierarchy(true);
						$all_members[] = $this->buildMemberList($parents, $mem->getDimension(),  null, null, null, null);									
					}
				}
			}
					
			ajx_extra_data(array("members" => $all_members));		
		}
		ajx_current("empty");
	}
	
	
	function buildMemberList($all_members, $dimension,  $allowed_member_type_ids, $allowed_object_type_ids, $item_object, $object_type_id, $return_only_name=false) {
		$dot_array = array(); // Dimension Object Types array (cache)
		$membersset = array();
		foreach ($all_members as $m) {
			$membersset[$m->getId()] = true;
		}
		$members = array();
		foreach ($all_members as $m) {
			/* @var  $m Member */
	//		if ($m->getArchivedById() > 0) continue;
			if ($object_type_id != null){
				$selectable = in_array($m->getObjectTypeId(), $allowed_object_type_ids) ? true : false;
				if ($selectable && isset($item_object)) {
					if (! $item_object->canAdd(logged_user(), array($m)) ) continue;
				}
			}else{
				$selectable = true ;
			}
			if ( count($allowed_member_type_ids) && !in_array($m->getObjectTypeId(), $allowed_member_type_ids) ) {
				continue;	
			}
			$tempParent = $m->getParentMemberId();
			
			//check if have parent member id from Contact Member Cache
			if(isset($m->cached_parent_member_id)){
				$tempParent = $m->cached_parent_member_id;
			}else{
				if(!logged_user()->isAdministrator() && $dimension->getDefinesPermissions()){
					$x = $m;
					while ($x instanceof Member && !isset($membersset[$tempParent])) {
						$tempParent = $x->getParentMemberId();
						if ($x->getParentMemberId() == 0) break;
						$x = $x->getParentMember();
					}
					if (!$x instanceof Member) {
						$tempParent = 0;
					}
				}else{
					$tempParent = $m->getParentMemberId();
				}
				
			}
			
			$memberOptions = '';
			
			// SET member options (dimension object types table)
			// CHeck dot cache, if not set goto database and add to cache
			if ( empty($dot_array[$dimension->getId()]) || empty ($dot_array[$dimension->getId()][$m->getObjectTypeId()]) ) {
				$dot = DimensionObjectTypes::instance()->findOne(array("conditions" =>"dimension_id = ".$dimension->getId() ." AND object_type_id = ".$m->getObjectTypeId()));
				if ($dot instanceof DimensionObjectType){
					if (empty($dot_array['dimension_id'])) {
						$dot_array[$dimension->getId()] = array();
					}
					$dot_array[$dimension->getId()][$m->getObjectTypeId()] = $dot;
				}
			}
			if ( !empty($dot_array[$dimension->getId()]) || ($dot_array[$dimension->getId()][$m->getObjectTypeId()]) instanceof DimensionObjectType ) {
				$dot =  $dot_array[$dimension->getId()][$m->getObjectTypeId()];
				if ($dot) $memberOptions = $dot->getOptions(true);
			}
			
			if ($return_only_name) {
				$path = trim($m->getPath());
				$member = array(
					"id" => $m->getId(),
					"name" => $m->getName(),
					"path" => $path,
					"depth" => $m->getDepth(),
					"to_show" => $m->getName() . ($path != "" ? " ($path)" : ""),
					"dim" => $m->getDimensionId(),
					"object_type_id" => $m->getObjectTypeId(),
					"ico" => "ico-color".$m->getColor() . " " . $m->getIconClass(),
				);
			} else {
				//Do not use contact member cache for superadmins
				if(!logged_user()->isAdministrator() && $dimension->getDefinesPermissions()){
					//check childs from contact member cache
					$childsIds = ContactMemberCaches::getAllChildrenIdsFromCache(logged_user()->getId(), $m->getId());
				}else{
					$childsIds = $m->getAllChildrenIds(false,null,"");
				}				
				$totalChilds = count($childsIds);
				$haveChilds = ($totalChilds > 0)? true : false; 
				
				/* @var $m Member */
				$additional_member_class = "";
				Hook::fire('additional_member_node_class', $m, $additional_member_class);
				$member = array(
					"id" => $m->getId(),
					"color" => $m->getMemberColor(),
					"name" => clean($m->getName()),
					"text" => clean($m->getName()),
					"leaf" => true,
					"parent" => $tempParent,
					"realParent" => $m->getParentMemberId(),
					"object_id" => $m->getObjectId(),
					"options"  => $memberOptions,
					"depth" => $m->getDepth(),
					"cls" => $additional_member_class,
					"iconCls" => "ico-color".$m->getColor() . " " . $m->getIconClass(),
					"selectable" => isset($selectable) ? $selectable : false,
					"dimension_id" => $m->getDimensionId(),
					"object_type_id" => $m->getObjectTypeId(),
					"expandable" => $haveChilds,
					"realTotalChilds" => $totalChilds,
					"allow_childs" => $m->allowChilds()
				);
				// Member Actions
				if (can_manage_dimension_members(logged_user())){
					$editUrl = '';
					// If member has an object linked, take object edit url
					
					if ($ot = ObjectTypes::findById($m->getObjectTypeId())) {
						if ($handler = $ot->getHandlerClass() ){
							eval ("\$itemClass = $handler::instance()->getItemClass();");
							if ($itemClass) {
								$instance = new $itemClass();
								$instance->setId($m->getObjectId());
								$instance->setObjectId($m->getObjectId());
								if ($instance instanceof Contact) {
									if ($ot->getName() == 'company') $instance->setIsCompany(1);
								}
								$editUrl = $instance->getEditUrl();
							}
						}
					}
					
					// Take default membewr edit url if not overwitten
					if (!$editUrl) {
						$editUrl = get_url('member', 'edit', array('id'=> $m->getId()));
					}
					$member['actions'] = array(array(
						'url' => $editUrl,
			  			'text' => '',
			  			'iconCls' => 'ico-edit',
			  			'class' => 'action-edit'
					));	
				}
			}
			$members[] = $member;
		}
		
		// re-sort by parent and name
		$tmp_members = array();
		foreach ($members as $m) {
			$key = strtolower(htmlentities(array_var($m, 'name')));
			$tmp_members[str_pad(array_var($m, 'depth'), 20, "0", STR_PAD_LEFT) . $key . array_var($m, 'id')] = $m;
		}
		
		ksort($tmp_members, SORT_STRING);

		//remove array keys to prevent json problems
		$tmp_members2 = array();
		foreach ($tmp_members as $m2) {
			$tmp_members2[] = $m2;
		}

		$members = $tmp_members2;		
		return $members ;
	}
	
	
	
	function linked_object_filters() {
		$genid = gen_id();
		
		$listeners = array('on_selection_change' => "Ext.getCmp('dimFilter').fireEvent('memberselected', member_selector['$genid'].sel_context);");		
		$options = array('select_current_context' => true, 'listeners' => $listeners, 'width' => 300, 'horizontal' => true);
		
		if (array_var($_REQUEST, 'show_associated_dimension_filters')) {
			$options['allow_non_manageable'] = true;
		}
		$options['dont_select_associated_members'] = true;
		
        $object_type_id = ProjectMessages::instance()->getObjectTypeId();
        if (array_var($_REQUEST, 'object_id')) {
            $object = Objects::findObject(array_var($_REQUEST, 'object_id'));
            if($object instanceof ContentDataObject){
                $object_type_id = $object->getObjectTypeId();
            }
        }
        if (array_var($_REQUEST, 'object_type_id')) {
            $object_type_id = array_var($_REQUEST, 'object_type_id');
        }

		render_member_selectors($object_type_id, $genid, null, $options, null, null, false);
		
		die();
	}
	
	
	function list_members() {
		if (!logged_user()->isAdminGroup()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_set_no_toolbar();
		
		$dim_id = array_var($_REQUEST, 'dim', 0);
		$dimension = Dimensions::findById($dim_id);
		if (!$dimension instanceof Dimension) {
			flash_error(lang('dimension snx'));
			ajx_current("empty");
			return;
		}
		
		if (isset($_REQUEST['page'])) {
			ajx_replace(true);
		}
		
		// parameters
		$page = array_var($_REQUEST, 'page');
		$order_by = array_var($_REQUEST, 'order');
		$order_by_dir = array_var($_REQUEST, 'order_dir');
		
		// pagination params
		$items_x_page = array_var($_REQUEST, 'items_x_page', 20);
		if (!$page) $page = 1;
		$offset = $items_x_page * ($page - 1);
		
		// order defaults
		if (!$order_by) $order_by = 'name';
		
		if (!in_array($order_by_dir, array('ASC', 'DESC'))) $order_by_dir = 'ASC';
		
		// this function already checks dimension options
		$dim_name = $dimension->getName();
		
		// permissions sql
		$perm_sql = "";
		if ($dimension->getDefinesPermissions() && !logged_user()->isAdministrator()) {
			$pg_ids = logged_user()->getPermissionGroupIds();
			$perm_sql = " AND EXISTS (SELECT cmp.member_id FROM ".TABLE_PREFIX."contact_member_permissions cmp WHERE cmp.member_id=m.id AND cmp.permission_group_id IN (".implode(',', $pg_ids)."))";
		}
		$main_sql = "SELECT m.id, l.created_on, l.created_by_id, l.member_id FROM ".TABLE_PREFIX."members m LEFT JOIN ".TABLE_PREFIX."application_logs l ON l.member_id=m.id AND l.action='add' WHERE m.dimension_id='$dim_id' $perm_sql";
		
		$sql = "$main_sql
				ORDER BY $order_by $order_by_dir 
				LIMIT $offset, $items_x_page";
		
		$rows = DB::executeAll($sql);
		
		$count_sql = str_replace("SELECT m.id, l.created_on, l.created_by_id, l.member_id FROM", "SELECT count(*) as total FROM", $main_sql);
		$count_row = DB::executeAll($count_sql);
		
		$members = array();
		$ids = array();
		$log_data = array();
		foreach ($rows as $row) {
			$members[] = Members::findById($row['id']);
			$log_data[$row['id']] = array('created_on' => $row['created_on'], 'created_by_id' => $row['created_by_id']);
			
		}
		
		$member_type_ids = array_flat(DB::executeAll("SELECT object_type_id FROM ".TABLE_PREFIX."dimension_object_types WHERE dimension_id=$dim_id AND is_root=1"));
		$member_types = ObjectTypes::findAll(array('conditions' => 'id IN ('.implode(',', $member_type_ids).')'));
		
		tpl_assign('members', $members);
		tpl_assign('log_data', $log_data);
		tpl_assign('dimension', $dimension);
		tpl_assign('dimension_name', $dim_name);
		tpl_assign('member_types', $member_types);
		
		tpl_assign('page', $page);
		tpl_assign('total_items', $count_row[0]['total']);
		tpl_assign('items_x_page', $items_x_page);
		tpl_assign('order_by', $order_by);
		tpl_assign('order_by_dir', $order_by_dir);
	}
	
	
	
	
	
	
	
	function dimension_tree_for_permissions() {
		$dimension_id = array_var($_REQUEST, 'dimension_id');
		$checkedField = (array_var($_REQUEST, 'checkboxes'))?"checked":"_checked";
		$objectTypeId = array_var($_REQUEST, 'object_type_id', null );
		
		$allowedMemberTypes = json_decode(array_var($_REQUEST, 'allowedMemberTypes', null ));
		if (!is_array($allowedMemberTypes)) {
			$allowedMemberTypes = null;
		}
	
		$only_names = array_var($_REQUEST, 'onlyname', false);
	
		$name = trim(array_var($_REQUEST, 'query', ''));
		$extra_cond = $name == "" ? "" : " AND name LIKE '%".$name."%'";
	
		if (array_var($_REQUEST, 'new_user')) {
			if (isset($_REQUEST['forced_members'])) {
				$forced_members = json_decode(array_var($_REQUEST, 'forced_members', ''));
				$fms = array(0);
				if (is_array($forced_members) && count($forced_members) > 0) {
					foreach ($forced_members as $fm) {
						if (is_numeric($fm)) $fms[] = $fm;
					}
				}
				if (count($fms) > 0) $extra_cond .= " AND id IN (".implode(',', $fms).")";
			}
			
			if (isset($_REQUEST['excluded_members'])) {
				$excluded_members = json_decode(array_var($_REQUEST, 'excluded_members', ''));
				$ems = array(0);
				if (is_array($excluded_members) && count($excluded_members) > 0) {
					foreach ($excluded_members as $em) {
						if (is_numeric($em)) $ems[] = $em;
					}
				}
				if (count($ems) > 0) $extra_cond .= " AND id NOT IN (".implode(',', $ems).")";
			}
			
		} else {
			// only use available object types
			$ots = ObjectTypes::getAvailableObjectTypes();
			$available_ots_csv = "";
			foreach ($ots as $ot) {
				$available_ots_csv .= ($available_ots_csv == "" ? "" : ",") . $ot->getId();
			}
			if (trim($available_ots_csv) != "") {
				$ot_cond = " AND cmp.object_type_id IN ($available_ots_csv)";
			} else {
				$ot_cond = "";
			}
			if (array_var($_REQUEST, 'only_with_perm')) {
				$extra_cond .= " AND EXISTS (SELECT cmp.member_id FROM ".TABLE_PREFIX."contact_member_permissions cmp WHERE cmp.member_id=id AND cmp.permission_group_id=".array_var($_REQUEST, 'pg', '-1')." $ot_cond)";
			} else if (array_var($_REQUEST, 'only_without_perm')) {
				$extra_cond .= " AND NOT EXISTS (SELECT cmp.member_id FROM ".TABLE_PREFIX."contact_member_permissions cmp WHERE cmp.member_id=id AND cmp.permission_group_id=".array_var($_REQUEST, 'pg', '-1')." $ot_cond)";
			}
		}
		
		$return_all_members = false;
	
		$selected_member_ids = json_decode(array_var($_REQUEST, 'selected_ids', "[0]"));
		$selected_members = Members::findAll(array('conditions' => 'id IN ('.implode(',',$selected_member_ids).')'));
	
		$list_dim_members = $this->initial_list_dimension_members($dimension_id, $objectTypeId, $allowedMemberTypes, $return_all_members, $extra_cond, null, false, null, $only_names, $selected_members);
		$memberList = $list_dim_members['members'];
		
		// add missing parents
		$missing_parent_ids = array();
		$all_members = array();
		foreach ($memberList as $m) {
			$all_members[$m['id']] = $m['id'];
		}
		foreach ($memberList as $m) {
			if ($m['parent'] > 0 && !isset($all_members[$m['parent']])) $missing_parent_ids[$m['parent']] = $m['parent'];
		}
		
		while (count($missing_parent_ids) > 0) {
			$missing_members = DB::executeAll("SELECT m.*, ot.icon FROM ".TABLE_PREFIX."members m INNER JOIN ".TABLE_PREFIX."object_types ot ON ot.id=m.object_type_id WHERE m.id IN (".implode(',', $missing_parent_ids).")");
			$missing_parent_ids = array();
			$new_missing = array();

			foreach ($missing_members as $mem) {
				$m = array(
					"id" => $mem['id'],
					"name" => clean($mem['name']),
					"parent" => $mem['parent_member_id'],
					"realParent" => $mem['parent_member_id'],
					"object_id" => $mem['object_id'],
					"depth" => $mem['depth'],
					"iconCls" => 'ico-' . $mem['icon'],
					"dimension_id" => $mem['dimension_id'],
					"object_type_id" => $mem['object_type_id'],
					"expandable" => true,
				);
				$memberList[str_pad(array_var($m, 'parent'), 20, "0", STR_PAD_LEFT) . strtolower(array_var($m, 'name')) . array_var($m, 'id')] = $m;
				$new_missing[] = $m;
				$all_members[$m['id']] = $m;
			}
			foreach ($new_missing as $m) {
				if ($m['parent'] > 0 && !isset($all_members[$m['parent']])) $missing_parent_ids[$m['parent']] = $m['parent'];
			}
		}
		// --
	
		$tree = buildTree($memberList, "parent", "children", "id", "name", $checkedField);
	
		ajx_current("empty");
		ajx_extra_data(array('dimension_members' => $tree, 'dimension_id' => $dimension_id));
	}


	//return all members in member_ids array
	function get_allowed_users_in_members($member_ids = null) {
		if (!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}

		$from_view = false;
		if(is_null($member_ids)){
			$member_ids = json_decode(array_var($_REQUEST, 'member_ids', null ));
			$from_view = true;
		}

		$result = null;
		if (is_array($member_ids)) {
			$all_users = array();


			if(count($member_ids) > 1){
				$users_by_member_ids= array();
				foreach ($member_ids as $m) {
					$contactMemberCache = ContactMemberCaches::getContactsIdsByMemberId($m);
					$users_by_member_ids[$m] = $contactMemberCache;

				}

				$result = call_user_func_array('array_intersect', $users_by_member_ids);
			}else{
				$contactMemberCache = ContactMemberCaches::getContactsIdsByMemberId($member_ids[0]);
				$result = $contactMemberCache;
			}
		}

		//super admins
		$admins = Contacts::findAll(array('conditions' => "user_type = 1"));
		foreach ($admins as $admin) {
			if(!in_array($admin->getId(),$result) ){
				$result[] = $admin->getId();
			}
		}

		if($from_view){
			ajx_extra_data(array("users_ids" => $result));
			ajx_current("empty");
		}else{
			return $result;
		}
	}
	
	
	
	function get_default_associated_members() {
		ajx_current("empty");
		$member_id = array_var($_REQUEST, 'member_id');
		$dimension_id = array_var($_REQUEST, 'dim_id');
		$assoc_id = array_var($_REQUEST, 'assoc_id');
		
		if (!is_numeric($member_id) || !is_numeric($dimension_id) || !is_numeric($assoc_id)) {
			return;
		}
		
		$rows = DB::executeAll("SELECT selected_member_id FROM ".TABLE_PREFIX."dimension_member_association_default_selections
				WHERE association_id='$assoc_id' AND member_id='$member_id'");
		
		$sel_member_ids = array();
		if (is_array($rows)) {
			$sel_member_ids = array_flat($rows);
		}
		
		ajx_extra_data(array('dimension_id' => $dimension_id, 'member_ids' => $sel_member_ids));
	}
	
	
	
	function render_member_selector() {
		
		$dim_id = array_var($_REQUEST, 'dim_id');
		
		foreach ($_REQUEST as $k => $v) {
			tpl_assign($k, $v);
		}
		
		if (is_numeric($dim_id)) {
			$dim = Dimensions::findById($dim_id);
			tpl_assign('dim', $dim);
		}
	}
}
