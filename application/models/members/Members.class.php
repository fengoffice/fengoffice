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
		$conditions = 	"`object_id` = $id ";
		if (!is_null($dimension_id)) {
			$conditions .= " AND dimension_id = $dimension_id "; 
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

  }
