<?php

  /**
  * Members
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class Members extends BaseMembers {
    
	static function getSubmembers(Member $member, $recursive = true, $extra_conditions="", $order_by=null, $order_dir=null, $offset = -1, $limit = -1) {
		if (is_null($order_by)) $order_by = "name";
		if (is_null($order_dir)) $order_dir = "ASC";
		
		$params = array(
				'conditions' => '`parent_member_id` = ' . $member->getId() .' '. $extra_conditions,
				'order' => "`$order_by` $order_dir",
		);
		if ($limit > 0 && $offset >= 0) {
			$params['limit'] = $limit;
			$params['offset'] = $offset;
		}
		
		$members = Members::findAll($params);
		if ($recursive) {
	  		foreach ($members as $m) {
	  			$members = array_merge($members, self::getSubmembers($m, $recursive));
	  		}
		}
		
		return $members;
	}
	
	static function getByDimensionObjType($dimension_id, $object_type_id) {
		return Members::findAll(array("conditions" => array("`dimension_id` = ? AND `object_type_id` = ?", $dimension_id, $object_type_id)));
	}
	
	/**
	 * 
	 * Find all members that have $id at 'object_id_column'
	 * Also accepts as optional parameter dimension_id
	 * @return Member
	 */
	static function findByObjectId($id, $dimension_id = null ) {
		$conditions = 	"`object_id` = '$id' ";
		if (!is_null($dimension_id)) {
			$conditions .= " AND dimension_id = '$dimension_id' "; 
		}		
		return self::findAll(array("conditions" => array($conditions) ));
	}	
	
	/**
	 * 
	 * Find one members that have $id at 'object_id_column'
	 * Also accepts as optional parameter dimension_id
	 * @return Member
	 */
	static function findOneByObjectId($id, $dimension_id = null ) {
		$allMembers = self::findByObjectId($id, $dimension_id);
		if(count($allMembers)) {
			return $allMembers[0];	
		}
		return null;
	}
	
	static $members_cache = array();
	static function getMemberById($id) {
		$m = array_var(self::$members_cache, $id);
		if (!$m instanceof Member) {
			$m = Members::findById($id);
			if ($m instanceof Member) {
				self::$members_cache[$id] = $m;
			}
		}
		return $m;
	}
	
	
	function canContainObject($object_type_id, $member_type_id, $dimension_id){
		$res = DB::execute("SELECT dimension_id FROM ".TABLE_PREFIX."dimension_object_type_contents WHERE `dimension_id` = ".$dimension_id." AND 
				`dimension_object_type_id` = ".$member_type_id." AND `content_object_type_id` = '$object_type_id'");
		return $res->numRows() > 0;
	}
	

	/**
	 * @abstract Returns all the parents of the member ids passed by parameters, does not check permissions
	 * @param array $members_ids: child member ids to retrieve parents
	 * @param boolean $only_ids: if true then only the parent member ids will be returned, otherwise the member objects will be returned 
	 */
	static function getAllParentsInHierarchy($members_ids, $only_ids = false) {
		$parent_members = array();
		$parent_member_ids = array();
		
		$tmp_parent_member_ids = array_filter($members_ids);
		while (count($tmp_parent_member_ids) > 0) {
		
			$tmp_parent_member_ids = DB::executeAll("SELECT parent_member_id FROM ".TABLE_PREFIX."members WHERE id IN (".implode(',', $tmp_parent_member_ids).")");
			$tmp_parent_member_ids = array_filter(array_flat($tmp_parent_member_ids));
		
			$parent_member_ids = array_unique(array_merge($parent_member_ids, $tmp_parent_member_ids));
		}
		
		if ($only_ids) {
			return $parent_member_ids;
		} else {
			if (count($parent_member_ids) > 0) {
				$parent_members = Members::findAll(array('conditions' => 'id IN ('.implode(',', $parent_member_ids).')'));
			}
			return $parent_members;
		}
	}
	

	/**
	 * @abstract Returns all the childs of the member ids passed by parameters, does not check permissions
	 * @param array $members_ids: parent member ids to retrieve childs
	 * @param boolean $only_ids: if true then only the child member ids will be returned, otherwise the member objects will be returned 
	 */
	static function getAllChildrenInHierarchy($members_ids, $only_ids = false) {
		$child_members = array();
		$child_member_ids = array();
		
		$tmp_child_member_ids = array_filter($members_ids);
		while (count($tmp_child_member_ids) > 0) {
		
			$tmp_child_member_ids = DB::executeAll("SELECT id FROM ".TABLE_PREFIX."members WHERE parent_member_id IN (".implode(',', $tmp_child_member_ids).")");
			$tmp_child_member_ids = array_filter(array_flat($tmp_child_member_ids));
		
			$child_member_ids = array_unique(array_merge($child_member_ids, $tmp_child_member_ids));
		}
		
		if ($only_ids) {
			return $child_member_ids;
		} else {
			if (count($child_member_ids) > 0) {
				$child_members = Members::findAll(array('conditions' => 'id IN ('.implode(',', $child_member_ids).')'));
			}
			return $child_members;
		}
	}
	
	
	static function getTypeNameToShowByObjectType($dimension_id, $object_type_id, $ot_name=null) {
	   $custom_name = trim(DimensionObjectTypeOptions::getOptionValue($dimension_id, $object_type_id, 'custom_object_type_name'));
       
	   if ($custom_name != '' ){
	       return $custom_name;
	   }else{
	       if (is_null($ot_name)){
	           $ot_name = lang( ObjectTypes::instance()->findById($object_type_id)->getName());
	       }
	       return $ot_name;
	   }	   
	}
	
	static function getCustomMemberNameToShow($dimension_id, $object_type_id) {
	    $custom_name = trim(DimensionObjectTypeOptions::getOptionValue($dimension_id, $object_type_id, 'custom_object_type_name'));
	    
	    $custom_value = '';
	    if ($custom_name != '' ){
	        $custom_value = $custom_name;
	    }
	    return $custom_value;
	}
	
	function getDefinition($ot_id, $dimension_id = null) {
	    
	    $result = array();
	    $ot = ObjectTypes::findById($ot_id);
	    
	    $member_columns = array('id', 'dimension_id', 'object_type_id', 'name', 'parent_member_id');
	    foreach ($member_columns as $c) {
	    	$result[$c]= array( 'id'=>$c, 'type' => $this->getColumnType($c), 'label'=>lang( 'field '.get_class($this).' '.$c ));
	    }
	    
	    if (class_exists($ot->getHandlerClass())) {
	    	
	    	eval('$managerInstance = ' . $ot->getHandlerClass() . "::instance();");
	    	$object_definition = $managerInstance->getDefinition($ot_id);
	    	
	    	foreach ($object_definition as $prop_id => $prop_data) {
	    		if (!isset($result[$prop_id])) {
	    			$result[$prop_id] = $prop_data;
	    		}
	    	}
	    }
	    
	    if (is_null($dimension_id)) {
	    	$dim_ids = DimensionObjectTypes::getDimensionIdsByObjectTypeId($ot_id);
	    	if (count($dim_ids) > 0) $dimension_id = $dim_ids[0];
	    }
	    
	    if (is_numeric($dimension_id) && $dimension_id > 0) {
		    $associations = DimensionMemberAssociations::getAllAssociatationsForObjectType($dimension_id, $ot_id);
		    $dim_reference = Dimensions::findById($dimension_id);
		    
		    foreach ($associations as $association) { /* @var $association DimensionMemberAssociation */
		    	$key = 'dim_association_'.$association->getId();
		    	$assoc_info = $association->getArrayInfo($dim_reference);
		    	
		    	if (is_array($assoc_info)) {
			    	$result[$key] = array(
		    			'id' => array_var($assoc_info, 'id'),
		    			'name' => array_var($assoc_info, 'name'),
		    			'code' => array_var($assoc_info, 'code'),
		    			'assoc_dimension_id' => array_var($assoc_info, 'assoc_dimension_id'),
		    			'assoc_object_type_id' => array_var($assoc_info, 'assoc_object_type_id'),
		    			'is_reverse' => array_var($assoc_info, 'is_reverse'),
			    	);
		    	}
		    }
	    }
	    
	    Hook::fire("additional_member_definition_columns", $ot_id, $result);
	    
	    return $result;
	}

  }
