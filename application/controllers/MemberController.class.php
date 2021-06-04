<?php

/**
 * Member controller
 *
 * @version 1.0
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class MemberController extends ApplicationController {
        
	/**
	 * Prepare this controller
	 *
	 * @param void
	 * @return MemberController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	}
	
	
	function init() {
		
		$ot = ObjectTypes::findById(array_var($_REQUEST, 'type_id'));
		$dim = Dimensions::findById(array_var($_REQUEST, 'dim_id'));
		
		$config = array(
			'object_type_id' => array_var($_REQUEST, 'type_id'),
			'object_subtype_id' => array_var($_REQUEST, 'subtype_id'),
			'dimension_id' => array_var($_REQUEST, 'dim_id'),
			'dimension_code' => $dim instanceof Dimension ? $dim->getCode() : '',
			'object_type_name' => $ot instanceof ObjectType ? $ot->getName() : '',
		);
		
		require_javascript("og/MemberManager.js");
		ajx_current("panel", "members", null, $config, true);
		ajx_replace(true);
	}
	
	function get_dimension_id() {
		ajx_current("empty");
		$data_to_return = array();
		
		$members_ids = json_decode(array_var($_REQUEST, 'member_id'));
		
		foreach ($members_ids as $key=>$m){
			$member = Members::instance()->findById($m);
			if ($member instanceof Member) {
				$data = array();
				$data['dim_id'] = $member->getDimensionId();
				$data['member_id'] = $member->getId();
				$data_to_return['dim_ids'][] = $data;
			}
		}
		ajx_extra_data(($data_to_return));
	}
	
	
	
	function build_listing_order_parameters($order, $order_dir, $member_type) {
		$order_join_sql = "";
		
		switch ($order){
			/*case 'task_completion_p':
			 $order = "d.task_completion_p";
			 break;
			 case 'time_worked_p':
			 $order = "d.time_worked_p";
			 break;
			 case 'total_tasks':
			 $order = "d.total_tasks";
			 break;
			 case 'completed_tasks':
			 $order = "d.completed_tasks";
			 break;
			 case 'total_estimated_time':
			 $order = "d.total_estimated_time";
			 break;
			 case 'total_worked_time':
			 $order = "d.total_worked_time";
			 break;*/
			case 'name':
			case 'description':
				$order = "mem.".$order;
				break;
		
			case 'mem_path':
				$order_join_sql = "LEFT JOIN ".TABLE_PREFIX."members mem_parent ON mem.parent_member_id = mem_parent.id";
				$order = "mem_parent.`name`";
				break;
			default:
				// check if order column is a custom property
				if (str_starts_with($order, "cp_")) {
					$cp_id = str_replace("cp_", "", $order);
					
					if ($member_type->getType() == 'dimension_group') {
						$cp = MemberCustomProperties::findById($cp_id);
						if ($cp instanceof MemberCustomProperty) {
							$order_join_sql = "LEFT JOIN ".TABLE_PREFIX."member_custom_property_values cpv ON cpv.member_id=mem.id AND cpv.custom_property_id=$cp_id";
							if ($cp->getType() == 'contact' || $cp->getType() == 'user') {
								$order_join_sql .= " LEFT JOIN ".TABLE_PREFIX."objects ocpv ON ocpv.id=cpv.`value`";
								$order = "ocpv.`name`";
							} else {
								if($cp->getType() == 'numeric'){
                                    $order = "CAST(cpv.value AS DECIMAL(10,4))";
                                }else{
                                    $order = "cpv.`value`";
                                }
							}
						} else {
							$order = 'mem.`name`';
						}
						
					} else if ($member_type->getType() == 'dimension_object') {
						$cp = CustomProperties::findById($cp_id);
						if ($cp instanceof CustomProperty) {
							$order_join_sql = "LEFT JOIN ".TABLE_PREFIX."custom_property_values cpv ON cpv.object_id=mem.object_id AND cpv.custom_property_id=$cp_id";
							if ($cp->getType() == 'contact' || $cp->getType() == 'user') {
								$order_join_sql .= " LEFT JOIN ".TABLE_PREFIX."objects ocpv ON ocpv.id=cpv.`value`";
								$order = "ocpv.`name`";
							} else {
                                if($cp->getType() == 'numeric'){
                                    $order = "CAST(cpv.value AS DECIMAL(10,4))";
                                }else{
                                    $order = "cpv.`value`";
                                }
							}
						} else {
							$order = 'mem.`name`';
						}
						
					} else {
						$order = 'mem.`name`';
					}
					
				} else if (str_starts_with($order, "dimassoc_")) {
					// check if order column is an associated dimension
					$assoc_id = str_replace("dimassoc_", "", $order);
					$assoc = DimensionMemberAssociations::findById($assoc_id);
					if ($assoc instanceof DimensionMemberAssociation) {
						if ($assoc->getObjectTypeId() == $member_type->getId()) {
							$order_join_sql .= "
								LEFT JOIN ".TABLE_PREFIX."member_property_members ord_mpm ON ord_mpm.member_id=mem.id AND ord_mpm.association_id=$assoc_id
								LEFT JOIN ".TABLE_PREFIX."members ord_mem ON ord_mem.id=ord_mpm.property_member_id";
						} else {
							$order_join_sql .= "
								LEFT JOIN ".TABLE_PREFIX."member_property_members ord_mpm ON ord_mpm.property_member_id=mem.id AND ord_mpm.association_id=$assoc_id
								LEFT JOIN ".TABLE_PREFIX."members ord_mem ON ord_mem.id=ord_mpm.member_id";
						}
						$order = "ord_mem.`name`";
					} else {
						$order = 'mem.`name`';
					}
					
				} else {
					// check if order column is specific from associated member type table
					
					$table_name = $member_type instanceof ObjectType ? trim($member_type->getTableName()) : '';
					if ($table_name != '' && checkTableExists(TABLE_PREFIX.$table_name) && check_column_exists(TABLE_PREFIX.$table_name, $order)) {
						$order = "obj_type_table." . $order;
					}  else {
						// check if associated obj has the column
						$ord_found = false;
						if ($member_type->getType() == 'dimension_object' && $member_type->getHandlerClass() != '') {
							eval('$member_ot_manager = '.$member_type->getHandlerClass().'::instance();');
							
							$assoc_objs_cols = $member_ot_manager->getAssociatedObjectsFixedColumns();
							$assoc_ot_managers = $member_ot_manager->getAssociatedObjectManagers();
							
							foreach ($assoc_objs_cols as $key_col => $cols) {
								if (!$ord_found) {
									foreach ($cols as $col) {
										if ($col['col'] == $order) {
											$assoc_ot_manager = array_var($assoc_ot_managers, $key_col);
											if ($assoc_ot_manager instanceof ContentDataObjects) {
												$order_join_sql .= "
													LEFT JOIN ".TABLE_PREFIX . $member_type->getTableName()." mem_obj ON mem_obj.object_id=mem.object_id
													LEFT JOIN ".$assoc_ot_manager->getTableName()." assoc_obj ON assoc_obj.object_id=mem_obj.$key_col
												";
												$order = "assoc_obj.$order";
												$ord_found = true;
											}
											break;
										}
									}
								}
							}
						}
						
						// no allowed order => set order as name
						if (!$ord_found) {
							$order = 'mem.`name`';
						}
					}
				}
				break;
		}
		if (!$order_dir) {
			$order_dir = 'ASC';
		}
		
		return array('order' => $order, 'order_dir' => $order_dir, 'order_join_sql' => $order_join_sql);
	}
	
    /**
     * @param $dimension
     * @param $member_type_id
     * @param string $mem_table_prefix
     * @param array $filter_members_id
     * Structure [id_dimension_member_associations => value]
     * Example [23 => 1044]
     * @return array
     */
	function build_listing_associated_dimensions_parameters($dimension, $member_type_id, $mem_table_prefix='mem',$filter_members_id=[]) {
		if (!$member_type_id) return array();
		if (!$dimension instanceof Dimension) return array();
		
		$persons_dim = Dimensions::findByCode('feng_persons');
		if ($persons_dim instanceof Dimension) $persons_dim_id = $persons_dim->getId();
		else $persons_dim_id = 0;
		
		// get associations (exclude persons dimension)
		$associated_dimension_ids = array();
		$dimension_associations = DimensionMemberAssociations::findAll(array(
				"conditions" => array("(`dimension_id` = ? AND `associated_dimension_id` != ? AND `object_type_id` = ?)
						OR (`associated_dimension_id` = ? AND `dimension_id` != ? AND `associated_object_type_id` = ?)",
						$dimension->getId(), $persons_dim_id, $member_type_id, $dimension->getId(), $persons_dim_id, $member_type_id)
		));
		
		// dimension ids
		$enabled_dimensions = config_option("enabled_dimensions");
		foreach ($dimension_associations as $da) {
			/* @var $da DimensionMemberAssociation */
			if ($da->getDimensionId() == $dimension->getId() && in_array($da->getAssociatedDimensionMemberAssociationId(), $enabled_dimensions)) {
				$associated_dimension_ids[] = $da->getAssociatedDimensionMemberAssociationId();
			} else if (in_array($da->getDimensionId(), $enabled_dimensions)) {
				$associated_dimension_ids[] = $da->getDimensionId();
			}
		}
		
		// get current selected members that area associated to this dimension
		$associated_member_ids = array();
		$context = active_context();
		if (is_array($context)) {
			foreach ($context as $sel) {
				if ($sel instanceof Member && in_array($sel->getDimensionId(), $associated_dimension_ids)) {
					$associated_member_ids[] = $sel->getId();
				}
			}
		}
		
		// build associated member conditions
		$member_association_cond = "";
		foreach ($associated_member_ids as $amid) {
			if (is_numeric($amid) && $amid > 0) {
				// include associated member's children in the conditions
				$assoc_mem_ids = array($amid);
				
				$child_mem_ids = Members::instance()->getAllChildrenInHierarchy(array($amid), true);
				if (count($child_mem_ids) > 0) {
					$assoc_mem_ids = array_merge($assoc_mem_ids, $child_mem_ids);
				}
				
				$mem_ids_str = "(".implode(',', $assoc_mem_ids).")";
				
				$member_association_cond .= "
					AND EXISTS (SELECT `mpm`.id FROM ".TABLE_PREFIX."member_property_members `mpm` WHERE `mpm`.member_id = $mem_table_prefix.id AND `mpm`.property_member_id IN $mem_ids_str )
				";
			}
		}
		
		// joins to retrieve dimension association columns
		$only_first_row_of_joins = array();
		$dimension_association_joins = array();
		$dimension_association_sel_cols = "";
		$group_by = "";
		$da = 0;
		foreach ($dimension_associations as $dassoc) {
			$da = $dassoc->getId();
			/* @var $da DimensionMemberAssociation */
			$thecol = $dassoc->getDimensionId() == $dimension->getId() ? "member_id" : "property_member_id";
			$thecol_assoc = $dassoc->getDimensionId() == $dimension->getId() ? "property_member_id" : "member_id";
			
			$join_str = "LEFT JOIN ".TABLE_PREFIX."member_property_members mem_pm".$da." ON mem_pm".$da.".$thecol=$mem_table_prefix.id AND mem_pm".$da.".association_id=".$dassoc->getId()."
			";
			$dimension_association_sel_cols .= ", GROUP_CONCAT(COALESCE(mem_pm".$da.".$thecol_assoc, '0')) AS dimassoc_".$dassoc->getId();
			
			$dimension_association_joins[] = $join_str;
			
			$first_row_cond = " AND IF (mem_pm".$da.".id>0, mem_pm".$da.".id=(SELECT pu.id FROM ".TABLE_PREFIX."member_property_members pu WHERE pu.association_id=$da AND pu.$thecol=$mem_table_prefix.id LIMIT 1), true) ";
			$only_first_row_of_joins[] = $first_row_cond;
		}
		$dimension_association_joins_sql = "";
		if (count($dimension_association_joins)) {
			$dimension_association_joins_sql = implode(" ", $dimension_association_joins);
			$group_by = "GROUP BY $mem_table_prefix.id";
		}
		if(!empty($filter_members_id) and is_array($filter_members_id)){
		    foreach($filter_members_id as $key=>$value){
                $member_association_cond.=" AND mem_pm{$key}.property_member_id  = {$value} ";
            }
        }
		
		return array(
			'assocs' => $dimension_associations, 'dims' => $associated_dimension_ids, 
			'assoc_members' => $associated_member_ids, 'member_assoc_cond' => $member_association_cond,
			'assoc_joins_sql' => $dimension_association_joins_sql, 'assoc_joins_cols' => $dimension_association_sel_cols, 'group_by' => $group_by,	
			'only_first_row_of_joins' => $only_first_row_of_joins,
		);
	}
	
	function build_listing_parent_condition($dimension, $parent_id) {
        $parent=array();
		if(is_array($parent_id)){
            foreach($parent_id as $member_id){
                $parent[]=Members::findById($member_id);
            }
        }else {
            $member=null;
		if ($parent_id > 0) {
                $member = Members::findById($parent_id);
		} else {
			$context = active_context();
			if (is_array($context)) {
			  foreach ($context as $sel) {
				if ($sel instanceof Member && $sel->getDimensionId() == $dimension->getId()) {
                            $member = $sel;
					break;
				}
			  }
			}
		}
            if($member){
                $parent=array($member);
            }
            
        }
		
		if(count($parent)>0){
		$all_parent_ids = array();
            foreach($parent as $member){
                if ($member instanceof Member) {
                    $all_parent_ids = array_merge($all_parent_ids,$member->getAllChildrenIds(false));
                    $all_parent_ids[] = $member->getId();
                }
		}
		
            return count($all_parent_ids)>0 ? "AND mem.parent_member_id IN (".implode(',',$all_parent_ids).")" : "";
        }
		return "";
	}
	
	function setCPConditions($cp_filters, &$SQL_EXTRA_JOINS, &$extra_conditions){
	    foreach($cp_filters as $cp_condition){
	        $SQL_EXTRA_JOINS .= " INNER JOIN ".TABLE_PREFIX."custom_property_values cpv".$cp_condition['id']." ON mem.object_id = cpv".$cp_condition['id'].".object_id";
	        
	        $extra_conditions.= " AND cpv".$cp_condition['id'].".custom_property_id=".$cp_condition['id'];
	        $extra_conditions.= " AND cpv".$cp_condition['id'].".value" .' '.$cp_condition['condition'] . ' '. $cp_condition['value'];
	    }
	}
	
	function listing($parameters = null) {
		$return_the_list = true;
		// if parameters not specified => use the request
		if (is_null($parameters)) {
			ajx_current("empty");
			$parameters = $_REQUEST;
			$return_the_list = false;
		}
		
		// get all variables from parameters array
		$start = array_var($parameters,'start', '0');
		$limit = array_var($parameters,'limit', config_option('files_per_page'));
		$order = array_var($parameters,'sort');
		$order_dir = array_var($parameters,'dir');
		$dimension_id = array_var($parameters, 'dim_id');
		$member_type_id = array_var($parameters, 'type_id');
		$parent_id = array_var($parameters, 'parent_id');
		$filter_members_id = array_var($parameters,"filter_members_id");
		
        //this is to bring just the first levels of sonf of the defined parent_id in the otherside just will bring all the element with unde the parent_id
        $just_parent_sons = array_var($parameters, 'just_parent_sons',false);
        //--
		$extra_conditions = array_var($parameters, 'extra_conditions');
        $SQL_EXTRA_JOINS = array_var($parameters, 'extra_join_conditions');
		$use_definition = array_var($parameters, 'use_definition');
		$cp_filters = array_var($parameters, 'cp_filters');
        $sub_type_object = array_var($parameters, 'sub_type_object');
        $exclude_associations_data = array_var($parameters, 'exclude_associations_data');

        // text search filter parameters
        $join_with_searchable_objects = array_var($parameters, 'join_with_searchable_objects');
        $search_string = array_var($parameters, 'search_string');
        $only_allowed_properties = array_var($parameters, 'only_allowed_properties');
        // --

		if ( isset($cp_filters) ){
		    $this->setCPConditions($cp_filters, $SQL_EXTRA_JOINS, $extra_conditions);
		}
		
		if (!is_numeric($start)) $start = 0;
		if (!is_numeric($limit)) $limit = config_option('files_per_page');
		
		// find current dimension
		$dimension = Dimensions::findById($dimension_id);
		
		// find member type
		$member_type = ObjectTypes::findById($member_type_id);
		
	
		// dimension associations params
		$assoc_params = array();
		if (!$exclude_associations_data) {
			$assoc_params = $this->build_listing_associated_dimensions_parameters($dimension, $member_type_id,'mem',$filter_members_id);
		}
		//$dimension_associations = array_var($assoc_params, 'assocs');
		//$associated_dimension_ids = array_var($assoc_params, 'dims');
		//$associated_member_ids = array_var($assoc_params, 'assoc_members');
		$member_association_cond = array_var($assoc_params, 'member_assoc_cond');

		// join params to retrieve dimension association columns
		$dimension_association_joins_sql = array_var($assoc_params, 'assoc_joins_sql');
		$dimension_association_sel_cols = array_var($assoc_params, 'assoc_joins_cols');
		$group_by = array_var($assoc_params, 'group_by');


		// parent member conditions
        if ($just_parent_sons == false) {
            $parent_member_cond = $this->build_listing_parent_condition($dimension, $parent_id);
        } else {
            if (!is_null($parent_id)) {
                if(is_array($parent_id)){
                    $parent_member_cond = "AND mem.parent_member_id IN (".implode(",",$parent_id).")";
                }else{
                    $parent_member_cond = "AND mem.parent_member_id IN ({$parent_id})";
                }
            }
        }

        
		// member type additional attributes join
		$object_type_join_sql = "";
		$object_type_cols_sql = "";
        $object_subtype_cond = "";
		if ($member_type instanceof ObjectType) {
			$table_name = trim($member_type->getTableName());
			if ($table_name != '' && checkTableExists(TABLE_PREFIX.$table_name)) {
				$object_type_join_sql = "INNER JOIN ".TABLE_PREFIX."$table_name obj_type_table ON obj_type_table.object_id=mem.object_id";
				$object_type_cols_sql = ", obj_type_table.*";
			}

			if ($member_type->getType() == 'dimension_object') {
				$object_type_join_sql .= "
					INNER JOIN ".TABLE_PREFIX."objects o ON o.id=mem.object_id";
			}

			$add_joins = array();
			Hook::fire("member_listing_additional_joins", array('ot' => $member_type), $add_joins);
			foreach ($add_joins as $j) {
				$object_type_join_sql .= " " . $j['join'];
				$object_type_cols_sql .= ", " . $j['cols'];
			}

            if(!is_null($sub_type_object) && !empty($sub_type_object) ){
                $object_type_join_sql .= " INNER JOIN ".TABLE_PREFIX."object_subtypes ob_sub_t on ob_sub_t.id=o.object_subtype_id";
                $object_subtype_cond = " AND ob_sub_t.name like '$sub_type_object' ";
            }
		}		
		
		// get order params
		$order_params = $this->build_listing_order_parameters($order, $order_dir, $member_type);
		$order = array_var($order_params, 'order');
		$order_dir = array_var($order_params, 'order_dir');
		$order_join_sql = array_var($order_params, 'order_join_sql');
		
		// build member permissions permission conditions
		$pg_array = logged_user()->getPermissionGroupIds();
		if (logged_user()->isAdministrator() || !$dimension->getDefinesPermissions()) {
			$permission_conditions = "";
		} else {
			$permission_conditions = "AND EXISTS (SELECT cmp.member_id FROM ".TABLE_PREFIX."contact_member_permissions cmp 
					WHERE cmp.member_id=mem.id AND cmp.permission_group_id IN (".implode(',',$pg_array)."))";
		}

		$searchable_objects_cond = "";
		$searchable_objects_join_sql = "";
		if ($member_type->getType() == 'dimension_object' && $join_with_searchable_objects) {

			$searchable_objects_join_sql = "INNER JOIN ".TABLE_PREFIX."searchable_objects so ON so.rel_object_id=mem.object_id";
			if (strpos($search_string, '&') !== false) {
				$searchable_objects_cond = "AND so.content like " . DB::escape('%'.$search_string.'%');
			} else {
				$searchable_objects_cond = "AND MATCH (so.content) AGAINST ('\"$search_string\"' IN BOOLEAN MODE)";
			}

			if ($only_allowed_properties) {
				$allowed_property_rows = DB::executeAll("SELECT property FROM ".TABLE_PREFIX."api_searchable_properties
						WHERE object_type_id='".$member_type->getId()."'");
				$allowed_properties = array_flat(array_filter($allowed_property_rows));
				if (count($allowed_properties) > 0) {
					$searchable_objects_cond .= "AND column_name IN ('".implode("','", $allowed_properties)."')";
				} else {
					$searchable_objects_cond .= "AND FALSE";
				}
			}
		}


		// select columns sql part
		$columns_sql = "SELECT mem.*, mem.id as member_id $object_type_cols_sql $dimension_association_sel_cols";
		
		// from table sql part 
		$from_sql = "FROM ".TABLE_PREFIX."members mem";
		
		// joins sql part
		$joins_sql = "
			$order_join_sql
			$object_type_join_sql
			$dimension_association_joins_sql
			$searchable_objects_join_sql
		";

		//extra joins
        $joins_sql .= $SQL_EXTRA_JOINS;

		// sql conditions part
		$all_conditions_sql = "
				mem.dimension_id=".$dimension->getId()." AND mem.archived_by_id=0
				$permission_conditions
				$member_association_cond
				$parent_member_cond
				$extra_conditions
				$object_subtype_cond
				$searchable_objects_cond
		";
		
		// main sql query part
		$main_sql = "
			$from_sql
			$joins_sql
			WHERE
				$all_conditions_sql
		";
		
		// final sql query
		$data_sql = "
				$columns_sql
				$main_sql
				$group_by
				ORDER BY $order $order_dir
				LIMIT $start, $limit
		";
		
		// execute query
		$rows = DB::executeAll($data_sql);
		if (!is_array($rows)) $rows = array();
		
		// count results sql
		$total_count_sql = "
				SELECT count(distinct(mem.id)) as total_count
				$main_sql
		";
		
		// execute count query
		$count_row = DB::executeOne($total_count_sql);
		$total_count = array_var($count_row, 'total_count');

		// build list of members information
		if ($use_definition) {
			$object = $this->prepareObjectUsingDefinition($rows, $start, $limit, $dimension, $member_type_id, $total_count);
		} else {
			$object = $this->prepareObject($rows, $start, $limit, $dimension, $member_type_id, $total_count);
		}
		// additional data over result
		$params = array('type_id' => $member_type_id, 'from_sql' => $from_sql, 'joins_sql' => $joins_sql, 'conditions_sql' => $all_conditions_sql, 
				'group_by' => $group_by, 'order' => $order, 'order_dir' => $order_dir, 'start' => $start, 'limit' => $limit);
		Hook::fire('member_listing_additional_data', $params, $object);
		
		// return the data or send it to the view
		if ($return_the_list) {
			return $object;
		} else {
			ajx_extra_data($object);
			tpl_assign("listing", $object);
		}
		
	}
	
	
	
	
	function prepareObjectUsingDefinition($rows, $start, $limit, $dimension, $member_type_id, $total, $groups_info=null) {
		$ot = ObjectTypes::findById($member_type_id);
		
		$object = array(
				"totalCount" => $total,
				"start" => $start,
				"dimension_id" => $dimension->getId(),
				"object_type_id" => $member_type_id,
				"dimension_name" => $dimension->getName(),
				"object_type_name" => $ot instanceof ObjectType ? $ot->getName() : $dimension->getName(),
				"members" => array(),
		);
		
		foreach ($rows as $info) {
			$m = Members::findById(array_var($info, 'member_id'));
			if ($m instanceof Member) {
				$object["members"][] = $m->getObjectData();
			}
		}
		
		return $object;
	}
	
	
	
	
	
	
	
	function prepareObject($rows, $start, $limit, $dimension, $member_type_id, $total, $groups_info=null) {
		
		$ot = ObjectTypes::findById($member_type_id);
		
		$object = array(
			"totalCount" => $total,
			"start" => $start,
			"dimension_id" => $dimension->getId(),
			"object_type_id" => $member_type_id,
			"dimension_name" => $dimension->getName(),
			"object_type_name" => $ot instanceof ObjectType ? $ot->getName() : $dimension->getName(),
			"members" => array(),
		);
		if (is_array($groups_info)) {
			$object['groups_info'] = $groups_info;
		}
		$member_ids = array();
		$ids = array();
		for ($i = 0; $i < $limit; $i++){
			if (isset($rows[$i])){
				$info = $rows[$i];
				if (!isset($info['icon_cls'])) {
					$info['icon_cls'] = $ot instanceof ObjectType ? $ot->getIconClass() : "";
				}
				
				$path_ids = array();
				$m = Members::findById(array_var($info, 'member_id'));
				if (!$m instanceof Member) {
					continue;
				}
				$all_parents = $m->getAllParentMembersInHierarchy();
				foreach ($all_parents as $parent) {
					if (!isset($path_ids[$dimension->getId()])) $path_ids[$dimension->getId()] = array();
					if (!isset($path_ids[$dimension->getId()][$parent->getObjectTypeId()])) $path_ids[$dimension->getId()][$parent->getObjectTypeId()] = array();
					$path_ids[$dimension->getId()][$parent->getObjectTypeId()][] = $parent->getId();
					break;
				}
				$info['mem_path'] = json_encode($path_ids);
				
				// calculated info
			/*	$more_info = array(
					'total_tasks' => array_var($info, 'total_tasks'),
					'completed_tasks' => array_var($info, 'completed_tasks'),
					'task_completion_p' => number_format(array_var($info, 'task_completion_p'), 2),
					'total_estimated_time' => array_var($info, 'total_estimated_time'),
					'total_worked_time' => array_var($info, 'total_worked_time'),
					'time_worked_p' => number_format(array_var($info, 'time_worked_p'), 2),
				);
				$info = array_merge($info, $more_info);*/
				
				$object["members"][] = $info;
				$member_ids[] = array_var($info, 'member_id');
			}
		}
		
		foreach ($member_ids as $k => &$mid) {
			if (!is_numeric($mid) || $mid==0) unset($member_ids[$k]);
		}
		
		Hook::fire('after_member_prepare_object', array('member_ids' => $member_ids, 'member_type_id' => $member_type_id, 'field' => 'members'), $object);
	
		return $object;
	}
	
	
	function get_parent_permissions() {
		ajx_current("empty");
		
		$dim_id = array_var($_REQUEST, 'dim_id');
		$parent = array_var($_REQUEST, 'parent');
		
		$permission_parameters = array();
		$permission_parameters = get_default_member_permission($parent, $permission_parameters);
		
		$pg_data = array();
		$perms = array();
		foreach ($permission_parameters['member_permissions'] as $pg_id => $p) {
			if (is_array($p) && count($p) > 0) {
				$perms[$pg_id] = $p;
				// type picture_url name is_guest company_name role
				$pg = PermissionGroups::findById($pg_id);
				if ($pg->getType() == 'permission_groups') {
					$c = Contacts::findById($pg->getContactId());
					$name = $name = escape_character($c->getObjectName());
					$picture_url = $c->getPictureUrl();
					$company_name = ($c->getCompany() instanceof Contact ? escape_character($c->getCompany()->getObjectName()) : "");
					$type = 'contact';
					$is_guest = $c->isGuest() ? "1" : "0";
					$role = $c->getUserTypeName();
				} else {
					$name = escape_character($pg->getName());
					$picture_url = "";
					$company_name = "";
					$type = 'group';
					$is_guest = "0";
					$role = "";
				}
				
				$pg_data[$pg_id] = array('pg_id' => $pg_id, 'type' => $type, 'picture_url' => $picture_url, 'name' => $name, 'is_guest' => $is_guest, 'company_name' => $company_name, 'role' => $role);
			}
		}
		
		ajx_extra_data(array('perms' => $perms, 'pg_data' => $pg_data));
		
	} 
	
	

	
	/**
	 * Adds a member to a dimension
	 */
	function add() {

		
		if (!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$member_data = array_var($_POST, 'member');
		$member = new Member();
		
		if (!is_array($member_data)) {
			
			// check if exists a specific controller that handles this member edition (e.g.: customers)
			$overriden_edit_url = $this->get_overriden_url($member, 'add', array_var($_GET, 'dim_id'), array_var($_GET, 'type'), array_var($_GET, 'parent'));
			if (!is_null($overriden_edit_url)) {
				redirect_to($overriden_edit_url);
			}
			
			$member_data = array();
			if ($name = array_var($_GET,'name') ) {
				$member_data['name'] = $name;
			}
			if ($parent = array_var($_GET,'parent')) {
				tpl_assign('parent_sel', $parent); 
			}
			tpl_assign('member_data', $member_data);
			
			$ret = array();
			Hook::fire('check_additional_member_permissions', array('action' => 'add', 'parent_member_id' => $parent, 'pg_ids' => logged_user()->getPermissionGroupIds()), $ret);
			if (count($ret) > 0 && !array_var($ret, 'ok')) {
				flash_error(array_var($ret, 'message'));
				ajx_current("empty");
				return;
			}
			
			// Permissions
			$permission_parameters = permission_member_form_parameters();
			
			$logged_user_pg = array();
			foreach ($permission_parameters['allowed_object_types'] as $ot){
				$logged_user_pg[] = array(
					'o' => $ot->getId(),
					'w' => 1,
					'd' => can_manage_dimension_members(logged_user()) ? 1 : 0,
					'r' => 1
				);
			}
			$permission_parameters['member_permissions'][logged_user()->getPermissionGroupId()] = $logged_user_pg;
			
			$permission_parameters = get_default_member_permission($parent,$permission_parameters);
			
			tpl_assign('permission_parameters', $permission_parameters);
			//--
			
			$sel_dim = get_id("dim_id");
			$current_dimension = Dimensions::getDimensionById($sel_dim);
			if (!$current_dimension instanceof Dimension) {
				flash_error("dimension dnx");
				ajx_current("empty");
				return;
			}
			$member->setDimensionId($current_dimension->getId());
			$member->setObjectTypeId(array_var($_GET, 'type'));
			
			tpl_assign("member", $member);
			tpl_assign("current_dimension", $current_dimension);
			
			$ot_ids = implode(",", DimensionObjectTypes::getObjectTypeIdsByDimension($current_dimension->getId()));
			$dimension_obj_types = ObjectTypes::findAll(array("conditions" => "`id` IN ($ot_ids)"));
			$dimension_obj_types_info = array();
			foreach ($dimension_obj_types as $ot) {
				$info = $ot->getArrayInfo(array('id', 'name', 'type'));
				$info['name'] = lang(array_var($info, 'name'));
				$dimension_obj_types_info[] = $info;
			}
			tpl_assign('dimension_obj_types', $dimension_obj_types_info);
			if (isset($_GET['type'])) {
				tpl_assign('obj_type_sel', $_GET['type']);
			} else {
				if (count($dimension_obj_types_info) == 1) {
					tpl_assign('obj_type_sel', $dimension_obj_types_info[0]['id']);
				}
			}
			
			tpl_assign('parents', array());
			tpl_assign('can_change_type', true);
			
			
			$restricted_dim_defs = DimensionMemberRestrictionDefinitions::findAll(array("conditions" => array("`dimension_id` = ?", $sel_dim)));
			$ot_with_restrictions = array();
			foreach($restricted_dim_defs as $rdef) {
				if (!isset($ot_with_restrictions[$rdef->getObjectTypeId()])) $ot_with_restrictions[$rdef->getObjectTypeId()] = true;
			}
			tpl_assign('ot_with_restrictions', $ot_with_restrictions);
			
			$associations = DimensionMemberAssociations::findAll(array("conditions" => array("`dimension_id` = ?", $sel_dim)));
			$ot_with_associations = array();
			foreach($associations as $assoc) {
				if (!isset($ot_with_associations[$assoc->getObjectTypeId()])) $ot_with_associations[$assoc->getObjectTypeId()] = true;
			}
			tpl_assign('ot_with_associations', $ot_with_associations);
			
			if (array_var($_GET, 'rest_genid') != "") tpl_assign('rest_genid', array_var($_GET, 'rest_genid'));
			if (array_var($_GET, 'prop_genid') != "") tpl_assign('prop_genid', array_var($_GET, 'prop_genid'));
			
			Hook::fire('before_add_member', array('member'=>$member, 'parent'=>$parent), $ret);
			
		} else {
		try {
			$ok = $this->saveMember($member_data, $member);
			
			if (config_option('add_default_permissions_for_users') && array_var($_GET, 'quick')) {
				if ($member->getParentMemberId() == 0) {
					// if added from quick-add add default permissions for executives, managers and administrators
					$user_types = implode(',', config_option('give_member_permissions_to_new_users'));
					if (trim($user_types) != "") {
						$users = Contacts::findAll(array('conditions' => "user_type IN (".$user_types.")"));
			
						if (!array_var($_REQUEST, 'permissions')) $_REQUEST['permissions'] = "[]";
						$permissions_decoded = json_decode(array_var($_REQUEST, 'permissions'));
						foreach ($users as $user) {
							$role_perms = RoleObjectTypePermissions::findAll(array('conditions' => array("role_id=?", $user->getUserType())));
							foreach ($role_perms as $role_perm) {
								$pg_obj = new stdClass();
								$pg_obj->pg = $user->getPermissionGroupId();
								$pg_obj->o = $role_perm->getObjectTypeId();
								$pg_obj->d = $role_perm->getCanDelete();
								$pg_obj->w = $role_perm->getCanWrite();
								$pg_obj->r = 1;
								$permissions_decoded[] = $pg_obj;
							}
						}
						$_REQUEST['permissions'] = json_encode($permissions_decoded);
					}
				} else {
					// inherit permissions from parent member
					if ($member->getParentMemberId() > 0) {
						$perm_params = get_default_member_permission($member->getParentMemberId(), array());
						if (is_array($perm_params) && is_array(array_var($perm_params, 'member_permissions'))) {
							$mem_perms = array_var($perm_params, 'member_permissions');
							$permissions_decoded = array();
							foreach ($mem_perms as $pg_id => $perms) {
								foreach ($perms as $perm) {
									$pg_obj = new stdClass();
									$pg_obj->pg = $pg_id;
									$pg_obj->o = array_var($perm, 'o');
									$pg_obj->d = array_var($perm, 'd');
									$pg_obj->w = array_var($perm, 'w');
									$pg_obj->r = array_var($perm, 'r');
									$permissions_decoded[] = $pg_obj;
								}
							}
							$_REQUEST['permissions'] = json_encode($permissions_decoded);
						}
					}
				}
			}
			
			Env::useHelper('permissions');
			save_member_permissions_background(logged_user(), $member, array_var($_REQUEST, 'permissions'));
			
			if ($ok) {
				ApplicationLogs::createLog($member, ApplicationLogs::ACTION_ADD);
				ajx_extra_data( array(
					"member"=>array(
						"id" => $member->getId(),
						"dimension_id" => $member->getDimensionId()
					)
				));
				$ret = null;
				Hook::fire('after_add_member', $member, $ret);
				
				$select_node = intval(DimensionObjectTypeOptions::getOptionValue($member->getDimensionId(), $member->getObjectTypeId(), 'select_after_creation'));
				if ($select_node) {
					evt_add("external dimension member click", array('dim_id' => $member->getDimensionId(),'member_id' => $member->getId()));
				} else {
					evt_add("update dimension tree node", array('dim_id' => $member->getDimensionId(), 'member_id' => $member->getId(), 'select_node' => $select_node));
				}
								
				if (array_var($_POST, 'rest_genid')) evt_add('reload member restrictions', array_var($_POST, 'rest_genid'));
				if (array_var($_POST, 'prop_genid')) evt_add('reload member properties', array_var($_POST, 'prop_genid'));
				if (array_var($_GET, 'current') == 'overview-panel' && array_var($_GET, 'quick') ) {
					//ajx_current("reload");
				}
				
				ajx_current("back");
				
			}
		} catch (Exception $e) {
			flash_error($e->getMessage());
			ajx_current("empty");
		}

		}
	}
	
	/**
	 * @abstract Checks if for this member type exists a specific controller that handles the method passed by parameter 
	 * @param Member $member
	 * @param string $method
	 * @param integer $dim_id
	 * @param integer $type_id
	 * @param integer $parent_member_id
	 */
	private function get_overriden_url($member, $method, $dim_id=null, $type_id=null, $parent_member_id=null) {
		
		$t = ObjectTypes::instance()->findById($member->getObjectTypeId());
		if (!$t instanceof ObjectType && $type_id) {
			$t = ObjectTypes::instance()->findById($type_id);
		}
		if (!$t instanceof ObjectType) return null;
		
		$class_name = Env::getControllerClass($t->getName());
		$controller_exists = controller_exists($t->getName(), $t->getPluginId());
		
		if ($controller_exists) {
			Env::useController($t->getName());
			eval('$controller = new '.$class_name.'();');
		}
		if ($controller_exists && $t->getHandlerClass()!='' && $controller && method_exists($controller, $method)) {
			if ($method == 'add') {
				$params = array("dim_id" => $dim_id, "type" => $t->getId());
				if ($parent_member_id > 0) $params['parent'] = $parent_member_id;
				
				return get_url($t->getName(), $method, $params);
				
			} else {
				return get_url($t->getName(), $method, array("id" => $member->getObjectId() > 0 ? $member->getObjectId() : $member->getId()));
			}
		}
		
		return null;
	}
	
	
	
	
	function edit() {
		if (!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$member = Members::findById(get_id());
		if (!$member instanceof Member) {
			flash_error(lang('member dnx'));
			ajx_current("empty");
			return;
		}
		
		// check if exists a specific controller that handles this member edition (e.g.: customers)
		$overriden_edit_url = $this->get_overriden_url($member, 'edit');
		if (!is_null($overriden_edit_url)) {
			redirect_to($overriden_edit_url);
		}
		
		$ret = array();
		Hook::fire('check_additional_member_permissions', array('action' => 'edit', 'member' => $member, 'pg_ids' => logged_user()->getPermissionGroupIds()), $ret);
		if (count($ret) > 0 && !array_var($ret, 'ok')) {
			flash_error(array_var($ret, 'message'));
			ajx_current("empty");
			return;
		}
		
		$this->setTemplate('add');
		$member_data = array_var($_POST, 'member');
		
		if (!is_array($member_data)) {
			
			// New ! Permissions
			$permission_parameters = permission_member_form_parameters($member);
			tpl_assign('permission_parameters', $permission_parameters);
			//--
			
			tpl_assign("member", $member);
			$member_data['name'] = $member->getName();
			$member_data['description'] = $member->getDescription();
			
			$current_dimension = $member->getDimension();
			if (!$current_dimension instanceof Dimension) {
				flash_error("dimension dnx");
				ajx_current("empty");
				return;
			}
			tpl_assign("current_dimension", $current_dimension);
			
			$ot_ids = implode(",", DimensionObjectTypes::getObjectTypeIdsByDimension($current_dimension->getId()));
			$dimension_obj_types = ObjectTypes::findAll(array("conditions" => "`id` IN ($ot_ids)"));
			$dimension_obj_types_info = array();
			foreach ($dimension_obj_types as $ot) {
				$info = $ot->getArrayInfo(array('id', 'name', 'type'));
				$info['name'] = lang(array_var($info, 'name'));
				$dimension_obj_types_info[] = $info;
			}
			tpl_assign('dimension_obj_types', $dimension_obj_types_info);
			tpl_assign('obj_type_sel', $member->getObjectTypeId());
			
			tpl_assign('parents', self::getAssignableParents($member->getDimensionId(), $member->getObjectTypeId()));
			tpl_assign('parent_sel', $member->getParentMemberId());
			
			tpl_assign("member_data", $member_data);
			
			tpl_assign('can_change_type', false);
			
			$restricted_dim_defs = DimensionMemberRestrictionDefinitions::findAll(array("conditions" => array("`dimension_id` = ?", $member->getDimensionId())));
			$ot_with_restrictions = array();
			foreach($restricted_dim_defs as $rdef) {
				if (!isset($ot_with_restrictions[$rdef->getObjectTypeId()])) $ot_with_restrictions[$rdef->getObjectTypeId()] = true;
			}
			tpl_assign('ot_with_restrictions', $ot_with_restrictions);
			
			$associations = DimensionMemberAssociations::findAll(array("conditions" => array("`dimension_id` = ?", $member->getDimensionId())));
			$ot_with_associations = array();
			foreach($associations as $assoc) {
				if (!isset($ot_with_associations[$assoc->getObjectTypeId()])) $ot_with_associations[$assoc->getObjectTypeId()] = true;
			}
			tpl_assign('ot_with_associations', $ot_with_associations);
			
		} else {
			try {
				$old_parent = $member->getParentMemberId();
				
				$ok = $this->saveMember($member_data, $member, false);
				
				Env::useHelper('permissions');
				save_member_permissions_background(logged_user(), $member, array_var($_REQUEST, 'permissions'), $old_parent);
				
				if ($ok) {
					ApplicationLogs::createLog($member, ApplicationLogs::ACTION_EDIT);
					$ret = null;
					Hook::fire('after_edit_member', $member, $ret);
					//evt_add("reload dimension tree", array('dim_id' => $member->getDimensionId(), 'mid' => $member->getId(), 'pid' => $member->getParentMemberId()));
					evt_add("update dimension tree node", array('dim_id' => $member->getDimensionId(), 'member_id' => $member->getId()));
				}
			} catch (Exception $e) {
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		}
	}
	
	function saveMember($member_data, Member $member, $is_new = true,$is_api_call = false) {
		/*if (!array_var($member_data, 'parent_member_id') && !SystemPermissions::userHasSystemPermission(logged_user(), 'can_manage_security')) {
			$ot = ObjectTypes::findById(array_var($member_data, 'object_type_id'));
			$ot_name = $ot instanceof ObjectType ? lang('the '.$ot->getName()) : ' ';
			throw new Exception(lang('you cant add member without security permissions', $ot_name));
		}*/
		
		$previous_data = array();
		
		try {
			DB::beginWork();
			if (!$is_new) {
				$old_parent = $member->getParentMemberId();
				
				$previous_data = $member->getDataForHistory();
			} else {
				$member->setObjectTypeId(array_var($member_data, 'object_type_id'));
			}
			
			/* @var $member Member */
			$object_type = ObjectTypes::findById($member->getObjectTypeId());
			
			if ($object_type->getType() == 'dimension_object') {
				$color_cp = CustomProperties::getCustomPropertyByCode($member->getObjectTypeId(), 'color_special');
				if ($color_cp instanceof CustomProperty) {
					if (isset($_REQUEST['object_custom_properties'][$color_cp->getId()]['color'])) {
						$member_data['color'] = $_REQUEST['object_custom_properties'][$color_cp->getId()]['color'];
					} else if (isset($_REQUEST['member_custom_properties'][$color_cp->getId()]['color'])) {
						$member_data['color'] = $_REQUEST['member_custom_properties'][$color_cp->getId()]['color'];
					}
				}
			}
			
			if (!isset($member_data['color']) && array_var($member_data, 'parent_member_id') > 0) {
				$p = Members::findById(array_var($member_data, 'parent_member_id'));
				$member_data['color'] = $p->getColor();
			}
			
			$member_data['name'] = trim(remove_css_and_scripts($member_data['name']));
						
			$member->setFromAttributes($member_data);
			
			if (!$object_type instanceof ObjectType) {
				throw new Exception(lang("you must select a valid object type"));
			}
			
			if ($member->getParentMemberId() == 0) {
				$dot = DimensionObjectTypes::findById(array('dimension_id' => $member->getDimensionId(), 'object_type_id' => $member->getObjectTypeId()));
				if (!$dot->getIsRoot()) {
					throw new Exception(lang("member cannot be root", lang($object_type->getName())));
				}
				$member->setDepth(1);
			}
			else {
				$allowedParents = $this->getAssignableParents($member->getDimensionId(), $member->getObjectTypeId());
				if (!$is_new) $childrenIds = $member->getAllChildrenIds(true);
				$hasValidParent = false ;
				if ($member->getId() == $member->getParentMemberId() ||  (!$is_new && in_array($member->getParentMemberId(), $childrenIds))) {
					$p_name = $member->getParentMember() instanceof Member ? $member->getParentMember()->getName() : '';
					throw new Exception(lang("invalid parent member", $member_data['name'], $p_name));
				}
				foreach ($allowedParents as $parent) {
					if ( $parent['id'] == $member->getParentMemberId() ){
						$hasValidParent = true;	
						break ;
					}
				}
				if (!$hasValidParent){
					$p_name = $member->getParentMember() instanceof Member ? $member->getParentMember()->getName() : '';
					throw new Exception(lang("invalid parent member", $member_data['name'], $p_name));
				}
				$parent = Members::findById($member->getParentMemberId());
				if ($parent instanceof Member) $member->setDepth($parent->getDepth() + 1);
				else $member->setDepth(1);
			}
				
			$ret = array();
			if ($is_new) {
				Hook::fire('check_additional_member_permissions', array('action' => 'add', 'member' => $member, 'parent_member_id' => $member->getParentMemberId(), 'pg_ids' => logged_user()->getPermissionGroupIds()), $ret);
			} else {
				Hook::fire('check_additional_member_permissions', array('action' => 'edit', 'member' => $member, 'pg_ids' => logged_user()->getPermissionGroupIds()), $ret);
			}
			if (count($ret) > 0 && !array_var($ret, 'ok')) {
				throw new Exception(array_var($ret, 'message'));
			}
			
			$dimension_object = null;
			if ($object_type->getType() == 'dimension_object') {
				$handler_class = $object_type->getHandlerClass();
				if ($is_new || $member->getObjectId() == 0) {
					eval('$dimension_object = '.$handler_class.'::instance()->newDimensionObject();');
				} else {
					$dimension_object = Objects::findObject($member->getObjectId());
					if (!$dimension_object instanceof ContentDataObject) {
						eval('$dimension_object = '.$handler_class.'::instance()->newDimensionObject();');
					}
					$handler_class = get_class($dimension_object->manager());
				}
				if ($dimension_object) {
					$dimension_object->modifyMemberValidations($member);
					$dimension_obj_data = array_var($_POST, 'dim_obj');
					if (!array_var($dimension_obj_data, 'name')) $dimension_obj_data['name'] = $member->getName();
					
					eval('$fields = '.$handler_class.'::instance()->getPublicColumns();');
					
					foreach ($fields as $field) {
						if (array_var($field, 'type') == DATA_TYPE_DATETIME) {
							$dimension_obj_data[$field['col']] = getDateValue($dimension_obj_data[$field['col']]);
						}
					}
					
					$dimension_object->setFromAttributes($dimension_obj_data, $member);
					$dimension_object->save();
					$member->setObjectId($dimension_object->getId());
					$member->save();
					Hook::fire("after_add_dimension_object_member", array('member' => $member, 'is_new' => $is_new, 
						'dimension_object' => $dimension_object, 'dimension_obj_data' => $dimension_obj_data), $null);
				}
			} else {
				$member->save();
				
			}
			
			// add custom properties
			if (Plugins::instance()->isActivePlugin('member_custom_properties')) {
				$mcp_controller = new MemberCustomPropertiesController();
				$mcp_controller->add_custom_properties($member, $dimension_object);
			}
			
			// save associated members
			save_associated_dimension_members(array('member' => $member, 'request' => $_REQUEST, 'is_new' => $is_new));
			
			// Other dimensions member restrictions
			$restricted_members = array_var($_POST, 'restricted_members');
			if (is_array($restricted_members)) {
				MemberRestrictions::clearRestrictions($member->getId());
				foreach ($restricted_members as $dim_id => $dim_members) {
					foreach ($dim_members as $mem_id => $member_restrictions) {
						
						$restricted = isset($member_restrictions['restricted']);
						if ($restricted) {
							$order_num = array_var($member_restrictions, 'order_num', 0);
							
							$member_restriction = new MemberRestriction();
							$member_restriction->setMemberId($member->getId());
							$member_restriction->setRestrictedMemberId($mem_id);
							$member_restriction->setOrder($order_num);
							$member_restriction->save();
						}
					}
				}
			}
			
			// Save member property members (also check for required associations)
			if (array_var($_POST, 'save_properties')) {
				$required_association_ids = DimensionMemberAssociations::getRequiredAssociatations($member->getDimensionId(), $member->getObjectTypeId(), true);
				$missing_req_association_ids = array_fill_keys($required_association_ids, true);
				
				// if keeps record change is_active, if not delete record
				$old_properties = MemberPropertyMembers::getAssociatedPropertiesForMember($member->getId());
				foreach ($old_properties as $property){
					$association = DimensionMemberAssociations::findById($property->getAssociationId());
					if (!$association->getKeepsRecord()){
						$property->delete();
					}
				}
				

				$new_properties = array();
				$associated_members = array_var($_POST, 'associated_members', array());
				
				foreach($associated_members as $prop_member_id => $assoc_id) {
					$active_association = null;
					
					if (isset($missing_req_association_ids[$assoc_id])) $missing_req_association_ids[$assoc_id] = false;
					
					$conditions = "`association_id` = $assoc_id AND `member_id` = ".$member->getId()." AND `is_active` = 1";
					
					$active_associations = MemberPropertyMembers::find(array('conditions'=>$conditions));
					if (count($active_associations)>0) $active_association = $active_associations[0];
					
					$association = DimensionMemberAssociations::findById($assoc_id);
					if ($active_association instanceof MemberPropertyMember){
						if ($active_association->getPropertyMemberId() != $prop_member_id){
							if ($association->getKeepsRecord()){
								$active_association->setIsActive(false);
								$active_association->save();
							}
							// save current association
							$mpm = new MemberPropertyMember();
							$mpm->setAssociationId($assoc_id);
							$mpm->setMemberId($member->getId());
							$mpm->setPropertyMemberId($prop_member_id);
							$mpm->setIsActive(true);
							$mpm->save();
							$new_properties[] =  $mpm;
						}
					}
					else{
						// save current association
						$mpm = new MemberPropertyMember();
						$mpm->setAssociationId($assoc_id);
						$mpm->setMemberId($member->getId());
						$mpm->setPropertyMemberId($prop_member_id);
						$mpm->setIsActive(true);
						$mpm->save();
						$new_properties[] =  $mpm;
					}
				}
				
				$missing_names = array();
				$missing_count = 0;
				foreach ($missing_req_association_ids as $assoc => $missing) {
					$assoc_instance = DimensionMemberAssociations::findById($assoc);
					if ($assoc_instance instanceof DimensionMemberAssociation) {
						$assoc_dim = Dimensions::getDimensionById($assoc_instance->getAssociatedDimensionMemberAssociationId());
						if ($assoc_dim instanceof Dimension) {
							if (!in_array($assoc_dim->getName(), $missing_names)) $missing_names[] = $assoc_dim->getName();
						}
					}
					if ($missing) $missing_count++;
				}
				if ($missing_count > 0) {
					throw new Exception(lang("missing required associations", implode(", ", $missing_names)));
				}
				
				$args = array($member, $old_properties, $new_properties);
				Hook::fire('edit_member_properties', $args, $ret);
			}
			
			
			$ret = null;
			Hook::fire('after_member_save', array('member' => $member, 'previous_data' => $previous_data, 'is_new' => $is_new), $ret);
			
			if ($is_new) {
				// set all permissions for the creator
				$dimension = $member->getDimension();

				$changed_pgs = array();

				$allowed_object_types = array();
				$dim_obj_types = $dimension->getAllowedObjectTypeContents();
				foreach ($dim_obj_types as $dim_obj_type) {
					// To draw a row for each object type of the dimension
					if (!in_array($dim_obj_type->getContentObjectTypeId(), $allowed_object_types) && $dim_obj_type->getDimensionObjectTypeId() == $member->getObjectTypeId()) {
						$allowed_object_types[] = $dim_obj_type->getContentObjectTypeId();
					}
				}
				$allowed_object_types[]=$object_type->getId();
				foreach ($allowed_object_types as $ot) {
					$cmp = ContactMemberPermissions::findOne(array('conditions' => 'permission_group_id = '.logged_user()->getPermissionGroupId().' AND member_id = '.$member->getId().' AND object_type_id = '.$ot));
					if (!$cmp instanceof ContactMemberPermission) {
						$cmp = new ContactMemberPermission();
						$cmp->setPermissionGroupId(logged_user()->getPermissionGroupId());
						$cmp->setMemberId($member->getId());
						$cmp->setObjectTypeId($ot);
					}
					$cmp->setCanWrite(1);
					$cmp->setCanDelete(1);
					$cmp->save();

					$changed_pgs[$cmp->getPermissionGroupId()] = $cmp->getPermissionGroupId();
				}
				
				// set all permissions for permission groups that has allow all in the dimension
				$permission_groups = ContactDimensionPermissions::findAll(array("conditions" => array("`dimension_id` = ? AND `permission_type` = 'allow all'", $dimension->getId())));
				if (is_array($permission_groups)) {
					foreach ($permission_groups as $pg) {
						foreach ($allowed_object_types as $ot) {
							$cmp = ContactMemberPermissions::findById(array('permission_group_id' => $pg->getPermissionGroupId(), 'member_id' => $member->getId(), 'object_type_id' => $ot));
							if (!$cmp instanceof ContactMemberPermission) {
								$cmp = new ContactMemberPermission();
								$cmp->setPermissionGroupId($pg->getPermissionGroupId());
								$cmp->setMemberId($member->getId());
								$cmp->setObjectTypeId($ot);
							}
							$cmp->setCanWrite(1);
							$cmp->setCanDelete(1);
							$cmp->save();
						}

						$changed_pgs[$pg->getPermissionGroupId()] = $pg->getPermissionGroupId();
					}
				}
				
				// Inherit permissions from parent node, if they are not already set
				if ( $member->getDepth() && $member->getParentMember() ) {
					$parentNodeId = $member->getParentMember()->getId();
					$condition = "member_id = $parentNodeId" ;
					foreach ( ContactMemberPermissions::instance()->findAll(array("conditions"=>$condition)) as $parentPermission ){
						/* @var $parentPermission ContactMemberPermission */
						$g = $parentPermission->getPermissionGroupId() ;
						$t = $parentPermission->getObjectTypeId() ;
						$w = $parentPermission->getCanWrite() ;
						$d = $parentPermission->getCanDelete() ;
						$existsCondition = "member_id = ".$member->getId()." AND permission_group_id= $g AND object_type_id = $t";
						if (!ContactMemberPermissions::instance()->count(array("conditions"=>$existsCondition))){
							$newPermission = new ContactMemberPermission();
							$newPermission->setPermissionGroupId($g);
							$newPermission->setObjectTypeId($t);
							$newPermission->setCanWrite($w);
							$newPermission->setCanDelete($d);
							$newPermission->setMemberId($member->getId());
							$newPermission->save();

							$changed_pgs[$parentPermission->getPermissionGroupId()] = $parentPermission->getPermissionGroupId();
						}
					}
				}

				//Update Contact Member cache
				$contactMemberCacheController = new ContactMemberCacheController();
				$contactMemberCacheController->afterMemberPermissionChanged(array('changed_pgs' => $changed_pgs, 'member' => $member));

				// Fill sharing table if is a dimension object (after permission creation);
				if (isset($dimension_object) && $dimension_object instanceof ContentDataObject) {
					$dimension_object->addToSharingTable();
					$dimension_object->addToSearchableObjects();
				}
				
			} else {
				
				// if parent changed 
				if ($old_parent != $member->getParentMemberId()) {
					Env::useHelper('dimension');
					update_all_childs_depths($member, $old_parent);
					
					evt_add('member parent changed', array('m' => $member->getId(), 'p' => $member->getParentMemberId(), 'op' => $old_parent, 'd' => $member->getDimensionId()));
				}
				
				// if member name has changed, then update searchable_objects in associations records with this member
				if (isset($previous_data['original_member_data'])
						&& $previous_data['original_member_data']['name'] != $member->getName()) {

					DB::execute("
						UPDATE ".TABLE_PREFIX."searchable_objects
						SET content=".DB::escape($member->getName())."
						WHERE assoc_member_id=".$member->getId()."
					");
				}
			}

			
			DB::commit();
			
			$ret = null;
			Hook::fire('after_member_save_and_commit', array('member' => $member, 'is_new' => $is_new), $ret);

            if(!$is_api_call){
                flash_success(lang('success save member', lang(ObjectTypes::findById($member->getObjectTypeId())->getName()), $member->getName()));
                ajx_current("back");
                if (array_var($_REQUEST, 'modal')) {
                    evt_add("reload current panel");
                }
            }
			// Add od to array on new members
			if ($is_new) {
				$member_data['member_id'] = $member->getId();
			}
			$member_data['archived'] = $member->getArchivedById();
			$member_data['path'] = trim(clean($member->getPath()));
			$member_data['ico'] = $member->getIconClass();
			if (isset($allowed_object_types) && is_array($allowed_object_types)) {
				$member_data['perms'] = array();
				foreach ($allowed_object_types as $ot_id) $member_data['perms'][$ot_id] = true;
			}
            if(!$is_api_call){
			    evt_add("after member save", $member_data);
            }
			return $member;
		} catch (Exception $e) {
			DB::rollback();
		    if($is_api_call){
		        return $e->getMessage();
            }
			flash_error($e->getMessage());
			throw $e;
			ajx_current("empty");
		}
	}
	
	function delete_multiple() {
		if(!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$member_ids_csv = array_var($_REQUEST, 'id');
		$member_ids = explode(',', $member_ids_csv);
		$member_ids = array_filter($member_ids, 'is_numeric');
		
		// get object type name
		$member_type_name = 'members';
		if (count($member_ids) > 0) {
			$tmp_member = Members::findById($member_ids[0]);
			if ($tmp_member instanceof Member) {
				$ot = ObjectTypes::findById($tmp_member->getObjectTypeId());
				if ($ot instanceof ObjectType) {
					$member_type_name = $ot->getPluralObjectTypeName();
				}
			}
		}
			
		$deleted_count = 0;
		$not_deleted_count = 0;
		
		try {
			DB::beginWork();
			
			foreach ($member_ids as $member_id) {
				$ok = $this->delete($member_id);
				if ($ok) $deleted_count++;
				else $not_deleted_count++;
			}
			
			DB::commit();
			
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
			ajx_current("empty");
			return;
		}
		
		if ($not_deleted_count == 0) {
			flash_success(lang('x members deleted', $member_type_name, $deleted_count));
		} else {
			flash_success(lang('x members deleted y members not deleted', $member_type_name, $deleted_count, $not_deleted_count));
		}
		
		ajx_current("reload");
	}
	
	function delete($id = null) {
		if(!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		if (is_null($id)) {
			$member = Members::findById(get_id());
			$show_messages = true;
			$use_transaction = true;
		} else {
			$member = Members::findById($id);
			$show_messages = false;
			$use_transaction = false;
		}
		if (!$member instanceof Member) {
			ajx_current("empty");
			return;
		}
		
		$ret = array();
		Hook::fire('check_additional_member_permissions', array('action' => 'delete', 'member' => $member, 'pg_ids' => logged_user()->getPermissionGroupIds()), $ret);
		if (count($ret) > 0 && !array_var($ret, 'ok')) {
			flash_error(array_var($ret, 'message'));
			ajx_current("empty");
			return;
		}
		
		try {
			
			if ($use_transaction) {
				DB::beginWork();
			}
			
			if (!$member->canBeDeleted($error_message)) {
				throw new Exception($error_message);
			}
			$dim_id = $member->getDimensionId();
			
			// Remove from sharing table
			$sqlDeleteSharingTable = "DELETE sh FROM `".TABLE_PREFIX."sharing_table` sh
										LEFT JOIN `".TABLE_PREFIX."object_members` om
										ON        om.object_id = sh.object_id
										WHERE     om.member_id = ".$member->getId()." AND om.is_optimization = 0;";
			
			DB::execute($sqlDeleteSharingTable);
			
			$affectedObjectsRows = DB::executeAll("SELECT distinct(object_id) AS object_id FROM ".TABLE_PREFIX."object_members where member_id = ".$member->getId()." AND is_optimization = 0") ;
			if (is_array($affectedObjectsRows) && count($affectedObjectsRows) > 0) {
				// build an array with all the affected object ids
				$all_affeceted_object_ids = array();
				foreach ( $affectedObjectsRows as $row ) {
					$all_affeceted_object_ids[] = $row['object_id'];
				}
				
				$trash_objects_in_member = array_var($_REQUEST, 'trash_objects_in_member');
				set_user_config_option('trash_objects_in_member_after_delete', $trash_objects_in_member);
				
				$user_ids = array_flat(DB::executeAll("SELECT object_id FROM ".TABLE_PREFIX."contacts WHERE user_type > 0"));
				
				if ($trash_objects_in_member) {
					/** 
					 * Calculate which objects cannot be trashed
					 * 1) users
					 * 2) objects classified in other members of dimensions that defines permissions
					 */
					$object_ids_to_keep_sql = "
						SELECT om.object_id 
						FROM ".TABLE_PREFIX."object_members om 
						INNER JOIN ".TABLE_PREFIX."members m on m.id=om.member_id
						INNER JOIN ".TABLE_PREFIX."dimensions d on d.id=m.dimension_id
						INNER JOIN ".TABLE_PREFIX."objects o on o.id=om.object_id
						LEFT JOIN ".TABLE_PREFIX."contacts c on c.object_id=o.id
						WHERE 
							om.member_id<>".$member->getId()." AND om.is_optimization=0
							AND d.defines_permissions=1 AND d.code<>'feng_persons'
							AND (c.user_type IS NULL OR c.user_type>0)
					";
					$object_ids_to_keep = array_flat(DB::executeAll($object_ids_to_keep_sql));
					
					// ensure that no user is going to be trashed
					$object_ids_to_keep = array_merge($object_ids_to_keep, $user_ids);
					
					// calculate the object ids that can be trashed
					$object_ids_to_trash = array_diff($all_affeceted_object_ids, $object_ids_to_keep);
					
					if (count($object_ids_to_trash) > 0) {
						$now_str = DateTimeValueLib::now()->toMySQL();
						// mark objects as trashed
						DB::execute("UPDATE ".TABLE_PREFIX."objects 
							SET trashed_by_id=".logged_user()->getId().",
								trashed_on='$now_str'
							WHERE id IN (".implode(',', $object_ids_to_trash).")");
						
						// add entries in application_logs for each trashed object
						$app_logs_columns = array('taken_by_id', 'rel_object_id', 'object_name', 'created_on', 'created_by_id', 'action', 'log_data');
						$app_logs_rows = array();
						foreach ($object_ids_to_trash as $oid) {
							$app_logs_rows[] = array(logged_user()->getId(), $oid, '', $now_str, logged_user()->getId(), 'trash', 'trashed when deleting member '.$member->getId());
						}
						massiveInsert(TABLE_PREFIX."application_logs", $app_logs_columns, $app_logs_rows, 500);
					}
				}
				
				// recalculate sharing table for all the affected objects, exclude users
				$ids_to_recalculate_cache = array_diff($all_affeceted_object_ids, $user_ids);
				$ids_str = implode(',', $ids_to_recalculate_cache);
				add_multilple_objects_to_sharing_table($ids_str, logged_user());
			}
			
			// remove member associations
			MemberPropertyMembers::delete('member_id = '.$member->getId().' OR property_member_id = '.$member->getId());
			MemberRestrictions::delete('member_id = '.$member->getId().' OR restricted_member_id = '.$member->getId());
			
			// remove from permissions tables
			ContactMemberPermissions::delete('member_id = '.$member->getId());
			PermissionContexts::delete('member_id = '.$member->getId());
			
			// remove associated content object
			if ($member->getObjectId() > 0) {
				$mobj = Objects::findObject($member->getObjectId());
				if ($mobj instanceof ContentDataObject) $mobj->delete();
			}
			
			// delete from object_members
			ObjectMembers::delete('member_id = '.$member->getId());
			
			Hook::fire('delete_member', $member, $ret);

			$parent_id = $member->getParentMemberId();
			
			$ok = $member->delete(false);
			if ($ok) {
				evt_add("reload dimension tree", array('dim_id' => $dim_id, 'node' => null));
				evt_add("try to select member", array('dimension_id' => $dim_id, 'id' => $parent_id));
			}
			
			if ($use_transaction) {
				DB::commit();
			}
			
			if ($show_messages) {
				flash_success(lang('success delete member', $member->getName()));
			}
			if (get_id('start')) {
				ajx_current("start");
			} else {
				if (get_id('dont_reload')) {
					ajx_current("empty");
				} else {
					ajx_current("reload");
				}
			}
			return true;
		} catch (Exception $e) {
			if ($use_transaction) {
				DB::rollback();
			} else {
				throw $e;
			}
			if ($show_messages) {
				flash_error($e->getMessage());
			}
			ajx_current("empty");
		}
	}
        
	function get_dimension_object_fields() {
		ajx_current("empty");
		if (!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		}
		
		$object_type = ObjectTypes::findById(get_id());
		if (!$object_type instanceof ObjectType) {
			flash_error(lang('object type dnx'));
			return;
		}
		
		$handler_class = $object_type->getHandlerClass();
		eval('$fields = '.$handler_class.'::instance()->getPublicColumns();');
		
		if (get_id('mem_id') > 0) {
			$date_format = user_config_option('date_format');
			$member = Members::findById(get_id('mem_id'));
			if ($member instanceof Member) {
				$dim_obj = Objects::findObject($member->getObjectId());
			}
			if (isset($dim_obj) && !is_null($dim_obj)) {
				foreach($fields as &$field) {
					$value = $dim_obj->getColumnValue($field['col']);
					if ($field['type'] == DATA_TYPE_DATETIME && $value instanceOf DateTimeValue) {
					  	$value = $value->format($date_format);
					}
					$field['val'] = $value;
				}
			}
		} else {
			// inherit color from parent
			$color_columns = array();
			foreach ($fields as $f) {
				if ($f['type'] == DATA_TYPE_WSCOLOR) {
					$color_columns[] = $f['col'];
				}
			}
			$parent_id = get_id('parent_id');
			if (count($color_columns) > 0 && $parent_id > 0) {
				$parent_member = Members::findById($parent_id);
				if ($parent_member instanceof Member) {
					$dimension_object = Objects::findObject($parent_member->getObjectId());
					if ($dimension_object instanceof ContentDataObject) {
						foreach ($color_columns as $col) {
							foreach ($fields as &$f) {
								if ($f['col'] == $col && $dimension_object->columnExists($col)) {
									$f['val'] = $dimension_object->getColumnValue($col);
								}
							}
						}
					}
				}
			}
		}

		$data = array( 'fields' => $fields, 'title' => lang($object_type->getName()) );
		
		ajx_extra_data($data);
	}
	
	function get_dimensions_for_restrictions() {
		if(!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$dim_id = get_id();
		$obj_type = get_id('otype');
		
		$restricted_dim_defs = DimensionMemberRestrictionDefinitions::findAll(array("conditions" => array("`dimension_id` = ? AND `object_type_id` = ?", $dim_id, $obj_type)));
		$restricted_ids_csv = "";
		$orderable_dimensions_otypes = array();
		foreach($restricted_dim_defs as $def) {
			$restricted_ids_csv .= ($restricted_ids_csv == "" ? "" : ",") . $def->getRestrictedDimensionId();
			if ($def->getIsOrderable()) 
				$orderable_dimensions_otypes[] = $def->getRestrictedDimensionId() . "_" . $def->getRestrictedObjectTypeId();
		}
		if ($restricted_ids_csv == "") $restricted_ids_csv = "0";
		$dimensions = Dimensions::findAll(array("conditions" => array("`id` <> ? AND `id` IN ($restricted_ids_csv)", $dim_id)));

		$childs_info = array();
		$members = array();
		foreach($dimensions as $dim) {
			$root_members = Members::findAll(array('conditions' => array('`dimension_id`=? AND `parent_member_id`=0', $dim->getId()), 'order' => '`name` ASC'));
			foreach ($root_members as $mem) {
				$members[$dim->getId()][] = $mem;
				$members[$dim->getId()] = array_merge($members[$dim->getId()], $mem->getAllChildrenSorted());
			}
			//generate child array info
			foreach($members[$dim->getId()] as $pmember) {
				$childs_info[] = array("p" => $pmember->getID(), "ch" => $pmember->getAllChildrenIds(), "d" => $pmember->getDimensionId());
			}
		}
		ajx_extra_data(array('childs' => $childs_info));
		
		$orderable_members = array();
		foreach ($members as $d => $dim_members) {
			foreach ($dim_members as $mem) {
				if (in_array($d."_".$mem->getObjectTypeId(), $orderable_dimensions_otypes)) $orderable_members[] = $mem->getId();
			}
		}
		
		$member_id = get_id('mem_id');
		if ($member_id > 0) {
			// actual restrictions
			$restrictions_info = array();
			$restrictions = MemberRestrictions::findAll(array("conditions" => array("`member_id` = ?", $member_id)));
			foreach ($restrictions as $rest) {
				$restrictions_info[$rest->getRestrictedMemberId()] = $rest->getOrder();
			}
			tpl_assign('restrictions', $restrictions_info);
			
			$actual_order_info = array();
			$actual_order = array_keys($restrictions_info);
			foreach($actual_order as $mem_id) {
				$break = false;
				foreach ($members as $d => $dim_members) {
					foreach ($dim_members as $member) {
						if ($member->getId() == $mem_id) {
							$actual_order_info[] = array('dim'=>$d, 'mem'=>$mem_id, 'parent' => $member->getParentMemberId());
							$break = true;
							break;
						}
					}
					if ($break) break;
				}
			}
			ajx_extra_data(array('actual_order' => $actual_order_info));
		}
		
		tpl_assign('genid', array_var($_GET, 'genid'));
		tpl_assign('members', $members);
		tpl_assign('dimensions', $dimensions);
		tpl_assign('orderable_dimensions_otypes', $orderable_dimensions_otypes);
		
		ajx_extra_data(array('ord_members' => $orderable_members));

		$this->setTemplate('dim_restrictions');
	}
	
	
	
	function get_dimensions_for_properties() {
		if(!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$dim_id = get_id();
		$obj_type = get_id('otype');
		$parent_id = get_id('parent');
		
		if ($parent_id == 0) {
			$dim_obj_type = DimensionObjectTypes::findById(array('dimension_id' => $dim_id, 'object_type_id' => $obj_type));
			if (!$dim_obj_type->getIsRoot()) {
				flash_error(lang('parent member must be selected to set properties'));
				ajx_current("empty");
				return;
			}
		}
		
		$dimensions = array();
		$associations_info = array();
		$associations_info_tmp = array();
		$member_parents = array();
		
		$associations = DimensionMemberAssociations::getAssociatations($dim_id, $obj_type);
		foreach ($associations as $assoc) {
			if (Plugins::instance()->isActivePlugin('core_dimensions') && config_option('hide_people_vinculations')) {
				$persons_dim = Dimensions::findByCode('feng_persons');
				if ($assoc->getAssociatedDimensionMemberAssociationId() == $persons_dim->getId()) {
					continue;
				}
			}
			$assoc_info = array('id' => $assoc->getId(), 'required' => $assoc->getIsRequired(), 'multi' => $assoc->getIsMultiple(), 'ot' => $assoc->getAssociatedObjectType());
			$assoc_info['members'] = Members::getByDimensionObjType($assoc->getAssociatedDimensionMemberAssociationId(), $assoc->getAssociatedObjectType());
			
			$ot = ObjectTypes::findById($assoc->getAssociatedObjectType());
			$assoc_info['ot_name'] = $ot->getName();
			
			if (!isset($associations_info_tmp[$assoc->getAssociatedDimensionMemberAssociationId()])) {
				$associations_info_tmp[$assoc->getAssociatedDimensionMemberAssociationId()] = array();
				$dimensions[] = Dimensions::getDimensionById($assoc->getAssociatedDimensionMemberAssociationId());
			}
			$associations_info_tmp[$assoc->getAssociatedDimensionMemberAssociationId()][] = $assoc_info;
		}
		
		// check for restrictions
		if ($parent_id > 0) {
			$parent = Members::findById($parent_id);
			$all_parents = $parent->getAllParentMembersInHierarchy();
			$all_parent_ids = array($parent_id);
			foreach ($all_parents as $p) $all_parent_ids[] = $p->getId();
		} else {
			$all_parent_ids = array(0);
		}
		
		$all_property_members = array();
		
		foreach ($associations_info_tmp as $assoc_dim => $ot_infos) {
			
			foreach ($ot_infos as $info) {
				$restriction_defs = DimensionMemberRestrictionDefinitions::findAll(array("conditions" => "`dimension_id` = $dim_id AND `restricted_dimension_id` = $assoc_dim 
					AND `restricted_object_type_id` = ".$info['ot']));
				
				if (!is_array($restriction_defs) || count($restriction_defs) == 0) {
					// no restriction definitions => include all members
					$associations_info[$assoc_dim][] = $info;
					$restricted_dimensions[$assoc_dim] = false;
				} else {
					// restriction definition found => filter members
					$restricted_dimensions[$assoc_dim] = true;
					$restrictions = array();
					$rest_members = array();
					$conditions = "";
					foreach ($restriction_defs as $rdef) {
						
						$conditions = "`restricted_member_id` IN (SELECT `id` FROM ".Members::instance()->getTableName(true)." WHERE 
							`object_type_id` = ".$rdef->getRestrictedObjectTypeId()." AND `dimension_id` = $assoc_dim) AND `member_id` IN (".implode(",", $all_parent_ids).")";

						$restrictions[] = MemberRestrictions::findAll(array("conditions" => $conditions));
					}
					
					$to_intersect = array();
					foreach ($restrictions as $k => $rests) {
						$to_intersect[$k] = array();
						foreach ($rests as $rest) {
							$to_intersect[$k][] = $rest->getRestrictedMemberId();
						}
						if (count($to_intersect[$k]) == 0) unset($to_intersect[$k]);
					}
					
					$apply_filter = true;
			    	$intersection = array_var($to_intersect, 0, array());
			    	if (count($to_intersect) > 1) {
			    		$k = 1;
			    		while ($k < count($to_intersect)) {
			    			$intersection = array_intersect($intersection, $to_intersect[$k++]);
			    		}
			    	} else if (count($to_intersect) == 0) {
			    		// no restrictions found for members
			    		$apply_filter = false;
			    	}
			    	
					if ($apply_filter) 
						$rest_members = Members::findAll(array("conditions" => "`id` IN (".implode(",", $intersection).")"));
					else 
						$rest_members = $info['members'];
					
					$new_info = $info;
					$new_info['members'] = $rest_members;
					$associations_info[$assoc_dim][] = $new_info;
					
					foreach ($rest_members as $member) {
						if (!isset($member_parents[$assoc_dim])) $member_parents[$assoc_dim] = array();
						if ($member->getParentMemberId() > 0) {
							$member_parents[$assoc_dim][$member->getId()] = $member->getParentMemberId();
						}
					}
				}
			}
		}
		
		foreach ($associations_info as $assoc_dim => $ot_infos) {
			foreach ($ot_infos as $info) {
				foreach ($info['members'] as $mem) $all_property_members[] = $mem->getId();
			}
		}
		
		// para cada $info['ot'] ver si en el resultado hay miembros que los restringen
		foreach ($associations_info as $assoc_dim => &$ot_infos) {
			foreach ($ot_infos as &$info) {
				$restriction_defs = DimensionMemberRestrictionDefinitions::findAll(array("conditions" => "`restricted_dimension_id` = $assoc_dim 
					AND `restricted_object_type_id` = ".$info['ot']));

				$restrictions = array();
				foreach ($restriction_defs as $rdef) {
					$restrictions_tmp = MemberRestrictions::findAll(array("conditions" => "`member_id` IN (
						SELECT `id` FROM ".Members::instance()->getTableName(true)." WHERE `dimension_id` = ".$rdef->getDimensionId()." AND `object_type_id` = ".$rdef->getObjectTypeId()." AND `id` IN (".implode(",", $all_property_members)."))"));
					
					$restrictions = array_merge($restrictions, $restrictions_tmp);
				}
				
				$restricted_ids = array();
				if (count($restrictions) == 0) continue;
				
				foreach ($restrictions as $rest) $restricted_ids[] = $rest->getRestrictedMemberId();
				$tmp = array();
				foreach ($info['members'] as $rmem) {
					if (in_array($rmem->getId(), $restricted_ids)) $tmp[] = $rmem;
				}
				$info['members'] = $tmp;
			}
		}

		
		$req_dimensions = array();
		foreach ($associations_info as $assoc_dim => &$ot_infos) {
			$required_count = 0;
			foreach ($ot_infos as &$info) {
				if ($info['required']) $required_count++;
			}
			$req_dimensions[$assoc_dim] = $required_count > 0;
		}

		$member_id = get_id('mem_id');
		$actual_associations_info = array();
		if ($member_id > 0) {
			// actual associations
			$actual_associations = MemberPropertyMembers::getAssociatedPropertiesForMember($member_id);
			foreach ($actual_associations as $actual_assoc) {
				$actual_associations_info[$actual_assoc->getPropertyMemberId()] = true;
			}
		}
		
		tpl_assign('genid', array_var($_GET, 'genid'));
		tpl_assign('dimensions', $dimensions);
		tpl_assign('associations', $associations_info);
		tpl_assign('actual_associations', $actual_associations_info);
		tpl_assign('req_dimensions', $req_dimensions);
		tpl_assign('restricted_dimensions', isset($restricted_dimensions) ? $restricted_dimensions : array());
		
		ajx_extra_data(array('parents' => $member_parents, 'genid' => array_var($_GET, 'genid')));
		
		$this->setTemplate('dim_properties');
	}
	
	
	
	function get_assignable_parents() {
		if(!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$dim_id = get_id('dim');
		$otype_id = get_id('otype');
		
		$parents_info = self::getAssignableParents($dim_id, $otype_id);
		
		ajx_extra_data(array("parents" => $parents_info));
		ajx_current("empty");
	}
	
	private function getAssignableParents($dim_id, $otype_id) {
		$parents = Members::findAll(array("conditions" => array("`object_type_id` IN (
			SELECT `parent_object_type_id` FROM `". DimensionObjectTypeHierarchies::instance()->getTableName() ."` WHERE `dimension_id` = ? AND `child_object_type_id` = ?
		)", $dim_id, $otype_id)));
		
		$parents_info = array();
		foreach ($parents as $parent) {
			$parents_info[] = array('id' => $parent->getId(), 'name' => $parent->getName());
		}
		
		$dim_obj_type = DimensionObjectTypes::findById(array('dimension_id' => $dim_id, 'object_type_id' => $otype_id));
		if ($dim_obj_type && $dim_obj_type->getIsRoot()) {
			array_unshift($parents_info, array('id' => 0, 'name' => lang('none')));
		}
		
		return $parents_info;
	}
	
	
	
	
	function edit_permissions() {
		if (!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$member = Members::findById(get_id());
		if (!$member instanceof Member) {
			flash_error(lang('member dnx'));
			ajx_current("empty");
			return;
		}
		
		if (!array_var($_POST, 'permissions')) {

			$permission_parameters = permission_member_form_parameters($member);
			tpl_assign('permission_parameters', $permission_parameters);

		} else {
			try {
				DB::beginWork();
				
				save_member_permissions($member);

				DB::commit();
				flash_success(lang('success user permissions updated'));
				ajx_current("back");
			} catch (Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		}
	}
	
	
	function quick_add_form() {
		ajx_current("empty");
		$this->setLayout('empty');
		$dimension_id = array_var($_GET, 'dimension_id');
		$dimension = is_numeric($dimension_id) ? Dimensions::instance()->findById($dimension_id) : null;
		
		if ($dimension instanceof Dimension){

			$object_Types = array();
			$parent_member_id = array_var($_GET, 'parent_member_id');
			
			$parent_member = Members::instance()->findById($parent_member_id);
			if ($parent_member instanceof Member) {
				$object_types = DimensionObjectTypes::getChildObjectTypes($parent_member);
				if(count($object_types) == 0){
					$parent_member = null;
					$object_types = DimensionObjectTypes::instance()->findAll(array("conditions"=>"enabled=1 AND dimension_id = $dimension_id AND is_root = 1 AND object_type_id<>(SELECT id from ".TABLE_PREFIX."object_types WHERE name='company')"));
				}				
			} else {
				$object_types = DimensionObjectTypes::instance()->findAll(array("conditions"=>"enabled=1 AND dimension_id = $dimension_id AND is_root = 1 AND object_type_id<>(SELECT id from ".TABLE_PREFIX."object_types WHERE name='company')"));
			}
			
			$obj_types = array();
			$editUrls = array();
			foreach ($object_types as $object_type ) {
				
				$options = $object_type->getOptions(1);
				if (isset($options->defaultAjax) && $options->defaultAjax->controller != "dashboard" )  {
					
					$editUrls[$object_type->getObjectTypeId()] = get_url( $options->defaultAjax->controller, 'add' );
					
				}else{
					
					$t = ObjectTypes::instance()->findById($object_type->getObjectTypeId());
					$obj_types[$t->getId()] = $t;
					
					$class_name = Env::getControllerClass($t->getName());
					$controller_exists = controller_exists($t->getName(), $t->getPluginId());
					if ($controller_exists) {
						Env::useController(ucfirst($t->getName()));
						eval('$controller = new '.$class_name.'();');
					}
					if ($t && controller_exists($t->getName(), $t->getPluginId()) && $t->getHandlerClass()!='' && $controller_exists && method_exists($controller, 'add')) {
						$params = array("type" => $t->getId());
						if ($parent_member instanceof Member) $params['parent'] = $parent_member->getId();
						
						$editUrls[$t->getId()] = get_url($t->getName(), 'add', $params);
					} else {
						$params = array("dim_id" => $dimension_id, "type" => $t->getId());
						if ($parent_member instanceof Member) $params['parent'] = $parent_member->getId();
						
						$editUrls[$t->getId()] = get_url('member', 'add' , $params);
					}
				}
			}
			
			$urls = array();
			foreach ($editUrls as $ot_id => $url) {
				$ot = array_var($obj_types, $ot_id);
				if ($ot instanceof ObjectType) {
					$link_text = ucfirst(Members::getTypeNameToShowByObjectType($dimension_id, $ot->getId()));
					$iconcls = $ot->getIconClass();
				} else {
					$link_text = lang('new');
					$iconcls = "";
				}
				$urls[] = array('link_text' => $link_text, 'url' => $url, 'iconcls' => $iconcls);
			}
			
			Hook::fire('member_quick_add_urls', array('dimension' => $dimension, 'object_types' => $object_types, 'parent_member' => $parent_member), $urls);
			
			if (count($urls) > 1) {
				ajx_extra_data(array('draw_menu' => 1, 'urls' => $urls));
			} else {
				ajx_extra_data(array('urls' => $urls));
			}
			
		} else {
			Logger::log("Invalid dimension: $dimension_id");
		}
		
	}
	
	
	/**
	 * After drag and drop
	 */
	function add_default_permissions() {
		ajx_current("empty");
		
		$mem_id = array_var($_REQUEST, 'member_id');
		$user_ids = explode(',', array_var($_REQUEST, 'user_ids'));
		foreach ($user_ids as $k => &$uid) if (!is_numeric($uid)) unset($user_ids[$k]);
		
		if (can_manage_security(logged_user()) && is_numeric($mem_id)) {
			$member = Members::findById($mem_id);
			$users = Contacts::findAll(array('conditions' => 'id IN ('.implode(',', $user_ids).')'));
			
			if ($member instanceof Member &&  is_array($users) && count($users) > 0) {
				$permissions_decoded = array();
				foreach ($users as $user) {
					$role_perms = RoleObjectTypePermissions::findAll(array('conditions' => array("role_id=?", $user->getUserType())));
					foreach ($role_perms as $role_perm) {
						$pg_obj = new stdClass();
						$pg_obj->pg = $user->getPermissionGroupId();
						$pg_obj->o = $role_perm->getObjectTypeId();
						$pg_obj->d = $role_perm->getCanDelete();
						$pg_obj->w = $role_perm->getCanWrite();
						$pg_obj->r = 1;
						$permissions_decoded[] = $pg_obj;
					}
				}
				$permissions = json_encode($permissions_decoded);
				
				Env::useHelper('permissions');
				try {
					DB::beginWork();
					
					save_member_permissions_background(logged_user(), $member, $permissions);
					
					DB::commit();
				} catch (Exception $e) {
					DB::rollback();
					flash_error($e->getMessage());
				}
			}
		}
	}
	
	/**
	 * Used for Drag & Drop, adds objects to a member
	 * @author alvaro
	 */
	function add_objects_to_member() {
		$ids = json_decode(array_var($_POST, 'objects'));
		$mem_id = array_var($_POST, 'member');
		$reclassify_in_associations = array_var($_POST, 'reclassify_in_associations');
		
		if (!is_array($ids) || count($ids) == 0) {
			ajx_current("empty");
			return;
		}
		$is_multiple = false;
		if (count($ids) > 1){
		    $is_multiple = true;
		}
		
		try {
			DB::beginWork();
		  if ($mem_id) {
		  	
		  	$user_ids = array();
			$member = Members::findById($mem_id);
			
			$objects = array();
			$prev_classification = array();
			$from = array();
			foreach ($ids as $oid) {
				/* @var $obj ContentDataObject */
				$obj = Objects::findObject($oid);
				if ($obj instanceof ContentDataObject && $obj->canAddToMember(logged_user(), $member, active_context())) {
					// to use when saving the application log
					$old_content_object = ContentDataObjects::generateOldContentObjectData($obj);
					$obj->old_content_object = $old_content_object;
					// --
					
					$prev_classification[$obj->getId()] = $obj->getMemberIds();
					
					$null = null;
					Hook::fire('before_classify_additional_verifications', array('object' => $obj, 'member_ids' => array($member->getId())), $null);
					
					$dim_obj_type_content = DimensionObjectTypeContents::findOne(array('conditions' => array('`dimension_id`=? AND `dimension_object_type_id`=? AND `content_object_type_id`=?', $member->getDimensionId(), $member->getObjectTypeId(), $obj->getObjectTypeId())));
					if (!($dim_obj_type_content instanceof DimensionObjectTypeContent)) continue;
					if (!$dim_obj_type_content->getIsMultiple() || array_var($_POST, 'remove_prev')) {
						$db_res = DB::execute("SELECT group_concat(om.member_id) as old_members FROM ".TABLE_PREFIX."object_members om INNER JOIN ".TABLE_PREFIX."members m ON om.member_id=m.id WHERE m.dimension_id=".$member->getDimensionId()." AND om.object_id=".$obj->getId());
						$row = $db_res->fetchRow();
						if (array_var($row, 'old_members') != "") $from[$obj->getId()] = $row['old_members'];
						// remove from previous members
						ObjectMembers::delete('`object_id` = ' . $obj->getId() . ' AND `member_id` IN (SELECT `m`.`id` FROM `'.TABLE_PREFIX.'members` `m` WHERE `m`.`dimension_id` = '.$member->getDimensionId().')');
					}
					
					$obj->addToMembers(array($member),null,$is_multiple);
					$obj->addToRelatedMembers(array($member), false, $reclassify_in_associations);
					$obj->save();
					
					$obj->addToSharingTable();
					$objects[] = $obj;					
									
					if (Plugins::instance()->isActivePlugin('mail') && $obj instanceof MailContent) {
						$conversation = MailContents::getMailsFromConversation($obj);
						foreach ($conversation as $conv_email) {
							if (array_var($_POST, 'attachment') && $conv_email->getHasAttachments()) {
								MailUtilities::parseMail($conv_email->getContent(), $decoded, $parsedEmail, $warnings);
								$classification_data = array();
								for ($j=0; $j < count(array_var($parsedEmail, "Attachments", array())); $j++) {
									$classification_data["att_".$j] = true;
								}
								MailController::classifyFile($classification_data, $conv_email, $parsedEmail, array($member), array_var($_POST, 'remove_prev'), false);
							}
						}
					}
					
					// if object is contact ask to add default permissions in member
					if ($obj instanceof Contact && $obj->isUser() && can_manage_security(logged_user())) {
						$user_ids[] = $obj->getId();
					}
				} else {
					throw new Exception(lang('you dont have permissions to classify object in member', $obj->getName(), $member->getName()));
				}
			}
			
			// if object is contact ask to add default permissions in member
			if (can_manage_security(logged_user()) && count($user_ids) > 0 && $member->getDimension()->getDefinesPermissions()) {
				evt_add('ask to assign default permissions', array('user_ids' => $user_ids, 'member' => array('id' => $member->getId(), 'name' => clean($member->getName())), ''));
			}
			
			Hook::fire('after_dragdrop_classify', array('objects' => $objects, 'prev_classification' => $prev_classification), $member);
			
			$display_name = $member->getName();
			$lang_key = count($ids)>1 ? 'objects moved to member success' : 'object moved to member success';
			$log_datas = array();
			$actions = array();
			
			// add to application logs
			foreach ($objects as $obj) {
				$actions[$obj->getId()] = array_var($from, $obj->getId()) ? ApplicationLogs::ACTION_MOVE : ApplicationLogs::ACTION_COPY;
				$log_datas[$obj->getId()] = (array_var($from, $obj->getId()) ? "from:" . array_var($from, $obj->getId()) . ";" : "") . "to:" . $member->getId();
			}
			
			
			
		  } else {
			if ($dim_id = array_var($_POST, 'dimension')) {
				$dimension = Dimensions::getDimensionById($dim_id);
				$from = array();
				foreach ($ids as $oid) {
					/* @var $obj ContentDataObject */
					$obj = Objects::findObject($oid);
					if ($obj instanceof ContentDataObject) {
						// to use when saving the application log
						$old_content_object = ContentDataObjects::generateOldContentObjectData($obj);
						$obj->old_content_object = $old_content_object;
						// --
						
						$db_res = DB::execute("SELECT group_concat(om.member_id) as old_members FROM ".TABLE_PREFIX."object_members om INNER JOIN ".TABLE_PREFIX."members m ON om.member_id=m.id WHERE m.dimension_id=".$dim_id." AND om.object_id=".$obj->getId());
						$row = $db_res->fetchRow();
						if (array_var($row, 'old_members') != "") $from[$obj->getId()] = $row['old_members'];
						// remove from previous members
						ObjectMembers::delete('`object_id` = ' . $obj->getId() . ' AND `member_id` IN (
							SELECT `m`.`id` FROM `'.TABLE_PREFIX.'members` `m` WHERE `m`.`dimension_id` = '.$dim_id.')');
						
						$obj->addToMembers(array());
						$obj->addToSharingTable();
						$objects[] = $obj;
					}
				}
				
				$display_name = $dimension->getName();
				$lang_key = count($ids)>1 ? 'objects removed from' : 'object removed from';
				$log_datas = array();
				$actions = array();
				
				// add to application logs
				foreach ($objects as $obj) {
					$actions[$obj->getId()] = array_var($from, $obj->getId()) ? ApplicationLogs::ACTION_MOVE : ApplicationLogs::ACTION_COPY;
					$log_datas[$obj->getId()] = (array_var($from, $obj->getId()) ? "from:" . array_var($from, $obj->getId()) . ";" : "");
				}
			}
		  }
		  
		  DB::commit();
		  
		  foreach ($objects as $object) {
		  	ApplicationLogs::instance()->createLog($object, $actions[$object->getId()], false, true, true, $log_datas[$object->getId()]);
		  }
		  
		  flash_success(lang($lang_key, $display_name));
		  if (array_var($_POST, 'reload')) ajx_current('reload');
		  else ajx_current('empty');
		
		} catch (Exception $e) {
			DB::rollback();
			ajx_current("empty");
			flash_error($e->getMessage());
		}
	}
	

	
	function archive() {
		if (!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$member = Members::findById(get_id());
		if (!$member instanceof Member) {
			flash_error(lang('member dnx'));
			ajx_current("empty");
			return;
		}
		if (get_id('user')) $user = Contacts::findById($get_id('user'));
		else $user = logged_user();
		
		if (!$user instanceof Contact) {
			ajx_current("empty");
			return;
		}
		
		try {
			DB::beginWork();
			set_time_limit(0);
			
			$count = $member->archive($user);
			
			evt_add("reload dimension tree", array('dim_id' => $member->getDimensionId()));
			if (array_var($_REQUEST, 'dont_back')) ajx_current("empty");
			else ajx_current("back");
			DB::commit();
			ApplicationLogs::createLog($member,ApplicationLogs::ACTION_ARCHIVE);
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
			ajx_current("empty");
		}
	}
	
	
	function unarchive() {
		if (!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$member = Members::findById(get_id());
		if (!$member instanceof Member) {
			flash_error(lang('member dnx'));
			ajx_current("empty");
			return;
		}
		if (get_id('user')) $user = Contacts::findById($get_id('user'));
		else $user = logged_user();
		
		if (!$user instanceof Contact) {
			ajx_current("empty");
			return;
		}
		
		try {
			DB::beginWork();
			set_time_limit(0);
			
			$count = $member->unarchive($user);
			
			evt_add("reload dimension tree", array('dim_id' => $member->getDimensionId()));
			
			if (array_var($_REQUEST, 'dont_back')) ajx_current("empty");
			else ajx_current("back");
			flash_success(lang('success unarchive member', $member->getName(), $count));
			DB::commit();
			ApplicationLogs::createLog($member, ApplicationLogs::ACTION_UNARCHIVE);
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
			ajx_current("empty");
		}
	}
	
	// get member selectors for add to the view 
	function get_rendered_member_selectors() {
		$object_members = array();
		$objectId = 0;
		if(get_id()){
			$object = Objects::findObject(get_id());
			$object_type_id = $object->manager()->getObjectTypeId();
			$object_members = $object->getMemberIds();
			$objectId = get_id();
		}else{
			$object_type_id = array_var($_GET, 'objtypeid');
			if(array_var($_GET,'members')){
				$object_members = explode(',', array_var($_GET,'members'));	
			}			
		}
		
		if(count($object_members) == 0){
			$object_members = active_context_members(false);
		}
		
		$genid = array_var($_GET, 'genid');
		$listeners = array();
	
		//ob_start — Turn on output buffering
		//no output is sent from the script (other than headers), instead the output is stored in an internal buffer.
		ob_start();
		//get skipped dimensions for this view
		$view_name = array_var($_GET, 'view_name');
		$dimensions_to_show = explode(",",user_config_option($view_name."_view_dimensions_combos"));
		$dimensions_to_skip = array_diff(get_user_dimensions_ids(), $dimensions_to_show);
		
		render_member_selectors($object_type_id, $genid, $object_members, array('listeners' => $listeners),$dimensions_to_skip,null,false);
	
		ajx_current("empty");
	
		//Gets the current buffer contents and delete current output buffer.
		//ob_get_clean() essentially executes both ob_get_contents() and ob_end_clean().
		ajx_extra_data(array("htmlToAdd" => ob_get_clean()));
		ajx_extra_data(array("objectId" => $objectId));
		
	
	} // get_rendered_member_selectors
	
	
	
	
	function save_permission_group() {
		ajx_current("empty");
		if (!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		}
		$member = Members::findById(array_var($_REQUEST, 'member_id'));
		if (!$member instanceof Member) {
			flash_error(lang('member dnx'));
			return;
		}
		
		$members = array($member);
		
		// if apply to submembers is checked get submembers verifying logged user permissions
		if (array_var($_REQUEST, 'apply_submembers') > 0) {
			$dimension = $member->getDimension();
			$pg_ids_str = implode(',', logged_user()->getPermissionGroupIds());
			
			$extra_conditions = "";
			if (!logged_user()->isAdministrator() && !$dimension->hasAllowAllForContact($pg_ids_str)) {
				$extra_conditions = " AND EXISTS (SELECT cmp.member_id FROM ".TABLE_PREFIX."contact_member_permissions cmp 
					WHERE cmp.member_id=".TABLE_PREFIX."members.id AND cmp.permission_group_id IN (". $pg_ids_str ."))";
			}
			$childs = $member->getAllChildren(true, null, $extra_conditions);
			$members = array_merge($members, $childs);
		}
		
		$pg_id = array_var($_REQUEST, 'pg_id');
		$permissions = array_var($_REQUEST, 'perms');
		
		$all_permissions = array();
		foreach ($members as $member) {
			$all_permissions[$member->getId()] = json_decode($permissions);
			foreach ($all_permissions[$member->getId()] as &$perm) {
				$perm->m = $member->getId();
			}
		}
		$all_permissions_str = json_encode(array_flat($all_permissions));
		$_POST['permissions'] = $all_permissions_str;
		
		try {
			DB::beginWork();
			
			$_POST['root_perm_genid'] = 'dummy_root_perm_genid';
			save_user_permissions_background(logged_user(), $pg_id, false, array(), true);
			
			$null = null;
			Hook::fire('after_save_member_permissions_for_pg', $_REQUEST, $null);
			
			DB::commit();
			flash_success(lang("permissions successfully saved"));
			
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		}
	}

    function get_contacts_of_member(){
        ajx_current("empty");
        
        $response = null;
        $member_ids = explode(',', array_var($_REQUEST, 'customer_id', ''));
        $invoice_id = array_var($_REQUEST, 'invoice_id');
        
        foreach ($member_ids as $member_id) {
	        $member = Members::findById($member_id);
	        $member_data = $this->getContactsOfMember($member, array_var($_REQUEST, 'company_data'), $invoice_id);
	        
	        if (is_null($response)) {
	        	$response = $member_data;
	        } else {
	        	if (isset($member_data['contacts']['Company']) && count($member_data['contacts']['Company']) > 0) {
	        		$response['contacts']['Company'][] = $member_data['contacts']['Company'][0];
	        	}
	        }
        }
        ajx_extra_data(array('response' => $response));
    }
    
    function getContactsOfMember($member,$need_company_data,$invoice_id = null){
    	// prevent errors when no member is given
    	if (!$member instanceof Member) {
    		return array();
    	}
        $cp_contacts = self::getCPContactsOfMember($member);
        ksort($cp_contacts['contacts']);
        if($need_company_data){
            $cp_contacts['contacts']['Company'][]=array_merge(['name'=>$member->getName()],InvoiceController::customerDataLogic($member->getId(),$invoice_id));
        }
        return $cp_contacts;
    }

    function getCPContactsOfMember(Member $member){
        
        $return =['custom_properties'=>[],'contacts'=>[]];
        if(!is_null($member)){
            /** @var CustomProperty $cp */
            //ask if we have billing contact property
            $cps = array_filter(CustomProperties::getAllCustomPropertiesByObjectType(Customers::instance()->getObjectTypeId()),function($cp){
                return $cp->getType()=='contact';
            });
            foreach($cps as $cp){
                /** @var CustomProperty $cp */
                $cp_value = CustomPropertyValues::getCustomPropertyValue($member->getObjectId(), $cp->getId());
                if ($cp_value instanceof CustomPropertyValue) {
                    $contact = Contacts::findById($cp_value->getValue());
                    if ($contact instanceof Contact) {
                         $contact_info = $contact->getArrayInfo();
                         $contact_info['code'] = $cp->getCode();
                         $return['contacts'][$cp->getName()][] = $contact_info;
                    }
                }
                $return['custom_properties'][] = $cp->getArrayInfo();
            }
            
            return $return;
        }else{
            return false;
        }
    }

    function get_by_id(){
        ajx_current("empty");
        ajx_extra_data(['response'=>Members::findById(array_var($_REQUEST, 'customer_id'))->getArrayInfo()]);
    }


	static function api_show_member_by_id($id) {
		return Members::getMemberById($id);
	}
}