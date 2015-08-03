<?php

  /**
  * MemberPropertyMembers
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class MemberPropertyMembers extends BaseMemberPropertyMembers {
    
    static function getAssociatedMembers($association_id, $member_ids_csv, $property_member_ids_csv, $is_active = true){
    	
    	$sql = "SELECT DISTINCT (`member_id`) FROM `".TABLE_PREFIX."member_property_members` WHERE 
    		`association_id` = $association_id AND `member_id` IN ($member_ids_csv) AND `property_member_id` IN ($property_member_ids_csv)
    		AND `is_active` = $is_active";
  		
    	$result = DB::execute($sql);
    	$rows = $result->fetchAll();
    	$member_ids = array();
    	if ($rows){
	    	foreach ($rows as $row){
	    		$member_ids[] = (int)$row['member_id'];
	    	}
    	}

    	return $member_ids;	
    }
    
    
    static function getAllPropertyMemberIds($association_id, $member_id, $is_active = true){
    	$sql = "SELECT DISTINCT (`property_member_id`) FROM `".TABLE_PREFIX."member_property_members` WHERE 
    		`association_id` = $association_id AND `member_id` = $member_id AND `is_active` = $is_active";
    	
    	$result = DB::execute($sql);
    	$rows = $result->fetchAll();
    	$property_member_ids = array();
    	if ($rows){
	    	foreach ($rows as $row){
	    		$property_member_ids[] = (int)$row['property_member_id'];
	    	}
    	}

    	$property_member_ids_csv = count($property_member_ids) > 0 ? implode(",", $property_member_ids) : '';
    	return $property_member_ids_csv;	
    }
    
    
     static function getAllMemberIds($association_id, $property_member_id, $is_active = true){
    	$sql = "SELECT DISTINCT (`member_id`) FROM `".TABLE_PREFIX."member_property_members` WHERE 
    		`association_id` = $association_id AND `property_member_id` = $property_member_id AND `is_active` = $is_active";
    	
    	$result = DB::execute($sql);
    	$rows = $result->fetchAll();
    	$member_ids = array();
    	if ($rows){
	    	foreach ($rows as $row){
	    		$member_ids[] = (int)$row['member_id'];
	    	}
    	}

    	$member_ids_csv = count($member_ids) > 0 ? implode(",", $member_ids) : '';
    	return $member_ids_csv;	
    }
    
  	static function getAllPropertyMembers($association_id, $invert = false, $is_active = true){
    	$sql = "SELECT `property_member_id`, `member_id` FROM `".TABLE_PREFIX."member_property_members` WHERE `association_id` = $association_id AND `is_active` = $is_active";
    	
    	$key = $invert ? 'member_id' : 'property_member_id';
    	$val = $invert ? 'property_member_id' : 'member_id';
    	
    	$result = DB::execute($sql);
    	$rows = $result->fetchAll();
    	$member_ids = array();
    	if ($rows){
	    	foreach ($rows as $row){
	    		$prop_m = $row[$key];
	    		if (!isset($member_ids[$prop_m])) $member_ids[$prop_m] = array();
	    		$member_ids[$prop_m][] = (int)$row[$val];
	    	}
    	}

    	return $member_ids;	
    }
    
  	static function getAssociatedMemberRecords($association_id, $member_ids_csv, $property_member_ids_csv = null, $is_active = true){
		$conditions = "`association_id` = $association_id AND `member_id` IN ($member_ids_csv) AND `is_active` = $is_active";
		
		if ($property_member_ids_csv != null) {
			$conditions .= " AND `property_member_id` IN ($property_member_ids_csv)";
		}
		
		return self::findAll(array("conditions" => $conditions));
    		
    }
    
    static function getAssociatedPropertiesForMember($member_id, $is_active = true) {
    	return self::findAll(array("conditions" => "`member_id` = $member_id AND `is_active` = $is_active"));
    }
    
    static function isMemberAssociated($member_id){
    	return self::count("member_id = '$member_id' OR property_member_id = '$member_id'") > 0;
    }
    
  } // MemberPropertyMembers 

?>