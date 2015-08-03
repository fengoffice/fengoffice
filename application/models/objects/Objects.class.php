<?php

  /**
  * Objects
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class Objects extends BaseObjects {

  	/**
  	 * Returns the content object from its id
  	 * @return ContentDataObject
  	 * @param $object_id id of the content object to get
  	 */
    static function findObject($object_id){
    	
    	$object = self::findById($object_id);
    	if (is_null($object)) return null;
    	$object_type = $object->getType();
    	if (!$object_type instanceof ObjectType) return null;
    	$handler_class = $object_type->getHandlerClass();
    	
    	eval('$concrete_object = '.$handler_class.'::findById('.$object_id.');');
    	
    	$object_type = null;
    	$object = null;
    	
    	return $concrete_object;
    }

    
    /**
 	 * 
 	 * Enter description here ...
 	 * @param unknown_type $options
 	 * @author Pepe
 	 */
	static function getObjects($context = null, $start = null, $limit = null, $order = null, $order_dir = null, $trashed = false, $archived = false, $filters = null, $start = 0 , $limit=null, $obj_type_types=null){

		switch ($order) {
			case 'name' :
					$order = 'o.name' ;
					break;	
			default :
					$order = 'updated_on';
					break;
		}
		
		if (!$order_dir){
			switch ($order){
				case 'name': $order_dir = 'ASC'; break;
				default: $order_dir = 'DESC';
			}
		}
		
		return self::getObjectsFromContext($context, $order, $order_dir, $trashed, $archived, $filters, $start, $limit, $obj_type_types);
	}
    
    
    
    
    /**
     * Enter description here...
     *
     * @param Array $context: array of the selected dimensions and/or members
     * @deprecated use ContentDataObjects::listing() instead 
     */
    static function getObjectsFromContext($context, $order=null, $order_dir=null, $trashed = false, $archived = false, $filters = null, $start = 0 , $limit=null, $obj_type_types=null) {
    	
    	//Filters conditions
    	$filter_conditions = self::prepareFiltersConditions($filters);
    	$name_filter_condition = $filter_conditions[0];
    	$obj_ids_filter_condition = $filter_conditions[1];
    	$type_filter_condition = $filter_conditions[2];
    	
    	//Trash && Archived conditions
    	$conditions = self::prepareTrashandArchivedConditions($trashed, $archived);
    	$trashed_cond = $conditions[0];
    	$archived_cond = $conditions[1];
    	
    	//Order conditions
    	$order_conditions = self::prepareOrderConditions($order, $order_dir);
        
		//Dimension conditions
    	$member_conditions = self::prepareDimensionConditions($context);
    	if ($member_conditions == "") $member_conditions = "true";
    	
    	$limit_query = "";
    	if ($limit !== null && $start !== null) {
    		$limit_query = "LIMIT $start , $limit " ;
    	} 
    	
    	if ($obj_type_types == null) {
    		$obj_type_types = array('content_object');
    	}
    	
    	// $exists_member_cond: checks if the logged user deleted or archived the object then he always can see it in the trash can or archived objs panel
    	$trashed_by_id_cond = $trashed ? "OR `o`.`trashed_by_id` = ".logged_user()->getId() : "";
    	$archived_by_id_cond = $archived ? "OR `o`.`archived_by_id` = ".logged_user()->getId() : "";
    	$exists_member_cond = "(NOT `om`.`member_id` IS NULL $trashed_by_id_cond $archived_by_id_cond)";
    	
    	$sql_count = "SELECT count( DISTINCT `o`.`id` ) AS total FROM `".TABLE_PREFIX."objects` `o` 
				INNER JOIN `".TABLE_PREFIX."object_types` `ot` ON `ot`.`id` = `o`.`object_type_id` 
				LEFT JOIN `".TABLE_PREFIX."object_members` `om` ON `o`.`id` = `om`.`object_id` 
				LEFT JOIN `".TABLE_PREFIX."project_tasks` `t` ON `t`.`object_id` = `o`.`id`
				LEFT JOIN `".TABLE_PREFIX."project_milestones` `m` ON `m`.`object_id` = `o`.`id`
	    		
				WHERE $trashed_cond $archived_cond
				AND $exists_member_cond
				AND ( `t`.`is_template` IS NULL OR `t`.`is_template` = 0 )
				AND ( `m`.`is_template` IS NULL OR `m`.`is_template` = 0 )
				AND `ot`.`type` IN ('". implode("','", $obj_type_types) ."')
				AND ($member_conditions) $name_filter_condition $obj_ids_filter_condition $type_filter_condition $order_conditions";
    		
    	$total = array_var(DB::executeOne($sql_count), "total");
    	
    	$sql = "SELECT DISTINCT `o`.`id` FROM `".TABLE_PREFIX."objects` `o` 
				INNER JOIN `".TABLE_PREFIX."object_types` `ot` ON `ot`.`id` = `o`.`object_type_id`
				LEFT JOIN `".TABLE_PREFIX."object_members` `om` ON `o`.`id` = `om`.`object_id` 
				LEFT JOIN `".TABLE_PREFIX."project_tasks` `t` ON `t`.`object_id` = `o`.`id`
				LEFT JOIN `".TABLE_PREFIX."project_milestones` `m` ON `m`.`object_id` = `o`.`id`
	    		
				WHERE $trashed_cond $archived_cond
				AND $exists_member_cond
				AND ( `t`.`is_template` IS NULL OR `t`.`is_template` = 0 )
				AND ( `m`.`is_template` IS NULL OR `m`.`is_template` = 0 )
				AND `ot`.`type` IN ('". implode("','", $obj_type_types) ."')
				AND ($member_conditions) $name_filter_condition $obj_ids_filter_condition $type_filter_condition $order_conditions
				$limit_query ";
	    		
    	$result = DB::execute($sql);
    	$rows = $result->fetchAll();
    	$objects = array();
    	if (!is_null($rows)) {
    		$ids = array();
	    	foreach ($rows as $row) {
	    		$ids[] = array_var($row, 'id');
	    	}
	    	if (count($ids) > 0) {
	    		$q_order = "";
	    		if (!is_null($order)) {
			    	if (!is_array($order)) $q_order = "`". str_replace("o.", "", $order) ."` $order_dir";
		    		else {
		    			$q_order = "";
		    			foreach($order as $o){
		    				$q_order .= ($q_order == "" ? "" : ", ") . "`" . str_replace("o.", "", $o) . "` $order_dir";
		    			}
		    		}
	    		}
	    		$query_params = array("conditions" => "`id` IN (".implode(",",$ids).")");
	    		if (trim($q_order) != "") $query_params["order"] = $q_order;
	    		$objects = Objects::findAll($query_params);
	    	}
    	}
    	
    	$result = new stdClass();
    	$result->objects = $objects ;
    	$result->total = $total ;
    	
    	return $result ;
    }
    
    
    static function prepareFiltersConditions($filters){
    	$name_filter_condition = "";
    	$obj_ids_filter_condition = "";
    	$type_filter_condition = "";
    	if (!is_null($filters) && is_array($filters)) {
    		$type_filters = array_var($filters, 'types');
	    	if (is_array($type_filters) && count($type_filters) > 0) {
	    		$type_filters_ids = ObjectTypes::findAll(array('id' => 'true', 'conditions' => "`name` IN (". DB::escape($type_filters).")"));
	    		$type_filter_condition = " AND `o`.`object_type_id` IN (". implode(",",$type_filters_ids) .")";
	    	}
	    	
	    	$name_filter = array_var($filters, 'name');
	    	$name_filter_condition = is_null($name_filter) ? "" : " AND `o`.`name` LIKE ". DB::escape("$name_filter%");
	    	
	    	$obj_ids_filter = array_var($filters, 'object_ids');
	    	$obj_ids_filter_condition = is_null($obj_ids_filter) ? "" : " AND `o`.`id` IN ($obj_ids_filter)";
    	}
    	
    	return array($name_filter_condition, $obj_ids_filter_condition, $type_filter_condition);
    }
    
    
    static function prepareTrashAndArchivedConditions($trashed, $archived){
    	$trashed_cond = "`o`.`trashed_on` " .($trashed ? ">" : "="). " " . DB::escape(EMPTY_DATETIME);
    	if ($trashed) {
    		$archived_cond = "";
    	} else {
    		$archived_cond = "AND `o`.`archived_on` " .($archived ? ">" : "="). " " . DB::escape(EMPTY_DATETIME);
    	}
    	return array($trashed_cond, $archived_cond);
    }
    
    
    static function prepareOrderConditions($order, $order_dir){
    	$order_conditions = "";
    	if ($order && $order_dir){
    		if (!is_array($order)) $order_conditions = "ORDER BY $order $order_dir";
    		else {
    			$i = 0;
    			foreach($order as $o){
    				if ($i==0)$order_conditions.= "ORDER BY $o $order_dir";
    				else $order_conditions.= ", $o $order_dir";
    				$i++;
    			}
    		}
    	}
    	return $order_conditions;
    }
    
    
    static function prepareDimensionConditions($context){
  	
    	//get contact's permission groups ids
    	$pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV(logged_user()->getId(), false);    	

    	$dm_conditions = "";
    	
    	$context_dimensions = array ();
    	$selection_members = array();// - stores the ids of all members selected in context
    	$selected_dimensions = array();// - stores the ids of all dimensions selected in context
    	$properties = array(); //- stores associations between dimensions
    	$redefined_context = array();// - if there are dimensions that are associated to another dimension in the context, we may need to redefine the context

    	foreach ($context as $selection) {
    		if ($selection instanceof Member){
    			$selection_members[]=$selection;
    		}
    	}
    	
    	foreach ($context as $selection) {
    		if ($selection instanceof Member){
    			$dimension = $selection->getDimension();
    			$dimension_id = $dimension->getId();
    			$selected_dimensions[] = $dimension;
    			$context_dimensions[$dimension_id]['allowed_members'] = array(); // - stores the ids of the members where we must search for objects
    			$context_dimensions[$dimension_id]['object_types'] = array();// - stores the ids of those content object types that we must search for
    		
    			
    		   	//first get all the object types of the member that is selected and its children
    			$member_object_types = array();
    			$member_object_types[] = $selection->getObjectTypeId();
    			$context_dimensions[$dimension_id]['allowed_members'][] =  $selection->getId();
    			
    			$children = $selection->getAllChildrenInHierarchy();
    			foreach($children as $child) {
    				$context_dimensions[$dimension_id]['allowed_members'][] = $child->getId();
    				if (!in_array($child->getObjectTypeId(), $member_object_types))
						$member_object_types[]=  $child->getObjectTypeId();
    			}
    			
    			//now let's check which content object type ids can hang from the object types that correspond to these members in this dimension
    			foreach ($member_object_types as $object_type){
    				$content_object_types = DimensionObjectTypeContents::getContentObjectTypeIds($dimension_id, $object_type);
    				foreach ($content_object_types as $co_type){
    					if (!in_array($co_type, $context_dimensions[$dimension_id]['object_types'])){
    						$context_dimensions[$dimension_id]['object_types'][] = $co_type;
    					}
    				}
    			}
    			
    			if ($dimension->canContainObjects()){
    				$allowed_members = $context_dimensions[$dimension_id]['allowed_members'];
    				$object_types = $context_dimensions[$dimension_id]['object_types'];
    				$dm_conditions .= self::prepareQuery($dm_conditions, $dimension, $allowed_members, $object_types, $pg_ids, 'AND', $selection_members);
    				$redefined_context[] = $dimension_id;
    			}
    			else{ 
	    		    //let's check if this dimension is property of another	
	    			$associated_dimensions_ids = $dimension->getAssociatedDimensions();
	    			if (count($associated_dimensions_ids)>0){
	    				foreach ($associated_dimensions_ids as $aid){
	    					$properties[$dimension_id][] = $aid;
	    				}
	    			}
    			}
    		}
    		else{
    			$all_members = $selection->getAllMembers();
    			foreach($all_members as $member) {
    				if (!isset($context_dimensions[$selection->getId()]['allowed_members'])) $context_dimensions[$selection->getId()]['allowed_members'] = array();
    				$context_dimensions[$selection->getId()]['allowed_members'][] = $member->getId();
    			}
    			//get all the content object type ids that can hang in the dimension
    			$context_dimensions[$selection->getId()]['object_types']= DimensionObjectTypeContents::getContentObjectTypeIds($selection->getId());
    			if ($selection->canContainObjects()){
	    			$allowed_members = array_var($context_dimensions[$selection->getId()], 'allowed_members', array());
	    			$object_types = array_var($context_dimensions[$selection->getId()], 'object_types', array());
	    			$dm_conditions .= self::prepareQuery($dm_conditions, $selection, $allowed_members, $object_types, $pg_ids, 'OR', $selection_members, true);
    			}		
    		}
    	}
    	
    	if(count($properties)>0){
    		foreach ($properties as $property=>$values){
    			foreach ($values as $dim_id){
    				if (!in_array($dim_id, $redefined_context)){
    					$redefined_context[] = $dim_id;
    				}
    			}
    		}
    		return self::prepareAssociationConditions($redefined_context, $context_dimensions, $properties, $pg_ids, $selection_members);
	    }
    	
    	$dimensions = Dimensions::findAll();
    	foreach ($dimensions as $dimension){
    		if ($dimension->canContainObjects() && !in_array($dimension, $context) && !in_array($dimension, $selected_dimensions)){
    			$member_ids = array();
    			$all_members = $dimension->getAllMembers();
    			foreach($all_members as $member) {
    				$member_ids[] = $member->getId();
    			}
    			$object_types = DimensionObjectTypeContents::getContentObjectTypeIds($dimension->getId());
    			$dm_conditions .= self::prepareQuery($dm_conditions, $dimension, $member_ids, $object_types, $pg_ids, 'OR', $selection_members, true);
    		}
	    }
    	
    	
    	return $dm_conditions;
    }
    
    
    static function prepareQuery($dm_conditions, $dimension, $member_ids, $object_type_ids, $pg_ids, $operator, $selection_members, $all = false){
    	$permission_conditions ="";
    	$member_ids_csv = count($member_ids) > 0 ? implode(",", $member_ids) : '0';
    	$check = $dimension->getDefinesPermissions() && !$dimension->hasAllowAllForContact($pg_ids);
    	if ($check){
    		
    	    // context permissions
    	    $context_conditions = "";
    	    foreach ($object_type_ids as $obj_type_id){
	    	    $context_permission_member_ids = array();
	    		$context_permission_member_ids = ContactMemberPermissions::getActiveContextPermissions(logged_user(),$obj_type_id, $selection_members, $member_ids);
	    		if (count($context_permission_member_ids)!= 0) {
			    	$context_conditions .= "OR EXISTS (SELECT `om2`.`object_id` FROM `".TABLE_PREFIX."object_members` `om2` WHERE
			    						`om2`.`object_id` = `om`.`object_id` AND `o`.`object_type_id` = $obj_type_id 
			    						AND `om2`.`member_id` IN (" .implode(",", $context_permission_member_ids)."))";
			    }
	    	}
    		
    		$permission_conditions = "AND EXISTS (SELECT `cmp`.`member_id` FROM `".TABLE_PREFIX."contact_member_permissions` 
    						`cmp` WHERE `om2`.`member_id` = `cmp`.`member_id` AND `cmp`.`permission_group_id` IN ($pg_ids) AND 
    						`o`.`object_type_id` = `cmp`.`object_type_id`) $context_conditions";
    		
    	}
    	$not_exists = "OR NOT EXISTS (SELECT `om2`.`object_id` FROM `".TABLE_PREFIX."object_members` `om2` WHERE
    						`om2`.`object_id` = `om`.`object_id` AND `om2`.`member_id` IN (".$member_ids_csv.")
    						AND `om2`.`is_optimization` = 0)";
    	
    	$dm_condition = "EXISTS (SELECT `om2`.`object_id` FROM `".TABLE_PREFIX."object_members` `om2` WHERE
    						`om2`.`object_id` = `om`.`object_id` AND `om2`.`member_id` IN (".$member_ids_csv.")
    						AND `om2`.`is_optimization` = 0 $permission_conditions)";
    	
    	if ($all){
    		$condition = "($dm_condition $not_exists)";
    		$operator = "AND";
    	} 
    	else $condition = $dm_condition;
    	$dm_conditions = $dm_conditions != "" ? " $operator $condition" : " $condition";
    	
    	return $dm_conditions;
    }
    
    
    static function prepareAssociationConditions($redefined_context, $dimensions, $properties, $pg_ids, $selection_members){
    	
    	$is_property = array();
    	foreach ($properties as $p=>$value){
	    		//obtener miembros de la dimension asociada que tienen como propiedad los miembros seleccionados de esta dimension
	    		foreach ($value as $v){
	    			$associations = DimensionMemberAssociations::getAllAssociations($v, $p);
			    		if (!is_null($associations)){
			    			foreach ($associations as $association){
			    				$is_property[$v] = true;
			    				$v_ids_csv = is_array($dimensions[$v]['allowed_members']) && count($dimensions[$v]['allowed_members']) > 0 ? implode(",",$dimensions[$v]['allowed_members']) : '0';
			    				$p_ids_csv = is_array($dimensions[$p]['allowed_members']) && count($dimensions[$p]['allowed_members']) > 0 ? implode(",",$dimensions[$p]['allowed_members']): '0';
			    				$prop_members = MemberPropertyMembers::getAssociatedMembers($association->getId(),$v_ids_csv, $p_ids_csv);
			    				if (count($prop_members)>0)
			    					$property_members[] = $prop_members;
			    			}
			    		}
	    		}
	    }
    		
    	// intersect the allowed members for each property
    	$member_intersection = array_var($property_members, 0, array());
    	if (count($property_members) > 1) {
    		$k = 1;
    		while ($k < count($property_members)) {
    			$member_intersection = array_intersect($member_intersection, $property_members[$k++]);
    		}
    	}

    	$association_conditions = "";
    	foreach ($redefined_context as $key=>$value){
	    		$dimension = Dimensions::getDimensionById($value);
	    		$object_types = $dimensions[$value]['object_types'];
	    		if (!isset($is_property[$value])) $member_ids = $dimensions[$value]['allowed_members'];
	    		else $member_ids = $member_intersection;
	    		$association_conditions.= self::prepareQuery($association_conditions, $dimension, $member_ids, $object_types, $pg_ids, 'AND', $selection_members);
    	}
    	$dims = Dimensions::findAll();
    	foreach ($dims as $dim){
    		if (!in_array($dim->getId(), $redefined_context) && !isset($properties[$dim->getId()]) && $dim->canContainObjects()){
    			$member_ids = array();
    			$all_members = $dim->getAllMembers();
    			foreach($all_members as $member) {
    				$member_ids[] = $member->getId();
    			}
    			$object_types = DimensionObjectTypeContents::getContentObjectTypeIds($dimension->getId());
		    	$association_conditions.= self::prepareQuery($association_conditions, $dim, $member_ids, $object_types, $pg_ids, 'OR', $selection_members, true);
	    			
    		}
    	}
    	
    	return $association_conditions;
    }
    
    
  } // Objects 
