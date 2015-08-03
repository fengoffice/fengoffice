<?php

class ContactMemberCaches extends BaseContactMemberCaches {
	
	static function getContactMemberCacheById($id) {
		$m = ContactMemberCaches::findById($id);
		return $m;
	}
	
	/**
	 * 
	  * @param array $args 
	 *		dimension : Required;
	 *		start = 0;
	 * 		limit = null;	
	 * 		parent_member_id; 
	 *		contact_id; 	 
	 *      member_name : Search objects in this list of members with this name;
	 *  	 
	 * @return Ambigous <NULL, multitype:ContactMemberCache >
	 */
	static function getAllContactMemberCache($args = array()) {
		$start = array_var($args,'start');
		$limit = array_var($args,'limit');
		$order = array_var($args,'order', 'id');
		$dimension = array_var($args,'dimension');
		$parent_member_id = array_var($args,'parent_member_id',null);
		$contact_id = array_var($args,'contact_id',null);
		$member_name = array_var($args,'member_name',null);
		$extra_condition = array_var($args,'extra_condition',null);
		
		// Prepare Condition SQL
		$SQL_CONDITION = "";
		if (!is_null($contact_id)) {
			$SQL_CONDITION .= " AND cmc.contact_id = ".$contact_id;
		}
		
		if (!is_null($parent_member_id)) {
			$SQL_CONDITION .= " AND cmc.parent_member_id = ".$parent_member_id;
		}
		
		if (!is_null($member_name)) {
			$member_name = mysql_real_escape_string($member_name, DB::connection()->getLink());
			$SQL_CONDITION .= " AND m.name LIKE '%".$member_name."%'";
		}
		
		if (!is_null($extra_condition)) {
			$SQL_CONDITION .= $extra_condition;
		}
		
		// Prepare Limit SQL
		if (is_numeric($limit) && $limit>0){
			$SQL_LIMIT = " LIMIT $start,$limit";
		}else{
			$SQL_LIMIT = '' ;
		}
		
		// Prepare SQL
		$sql = "SELECT cmc.* FROM ".TABLE_PREFIX."contact_member_cache cmc 
					INNER JOIN  ".TABLE_PREFIX."members m ON cmc.member_id = m.id 					
					WHERE m.dimension_id = ".$dimension->getId()."
					$SQL_CONDITION
					ORDER BY $order DESC 
					$SQL_LIMIT ;
		";
		
		// Run!
		$rows = DB::executeAll($sql);
				
		// Empty?
		if(!is_array($rows) || (count($rows) < 1)) return null;
		
		$objects = array();
		foreach($rows as $row) {
			// construct item...
			$object = new ContactMemberCache();
			$object->loadFromRow($row);
			
			if(instance_of($object, 'ContactMemberCache')) $objects[] = $object;
		} // foreach
		return count($objects) ? $objects : null;
	}
	
	/**
	 *This function return all memnbers that match args, with an extra temp param cached_parent_member_id.  
	 * @param array $args
	 *		dimension : Required;
	 *      get_all_parent_in_hierarchy = false: when searching is important to return all hierarchy;
	 *
	 * @return Ambigous <array(), multitype:Member >
	 */
	static function getAllMembersWithCachedParentId($args = array()) {
		$dimension = array_var($args,'dimension');
		$all_parent_in_hierarchy = array_var($args,'get_all_parent_in_hierarchy',false);
		$all_members = array();
		
		//Get all contact member caches
		$member_cache_list = ContactMemberCaches::getAllContactMemberCache($args);
		
		//Build member list to return
		if(is_array($member_cache_list) && count($member_cache_list) > 0){
			$members_ids = array();
			$members_parents_ids = array();
			foreach ($member_cache_list as $member_cache){
				$members_ids[] = $member_cache->getMemberId();
				$members_parents_ids[$member_cache->getMemberId()] = $member_cache->getParentMemberId();
			}

			//Check hierarchy
			if($all_parent_in_hierarchy){
				foreach ($member_cache_list as $member_cache){
					$child = $member_cache->getParentMemberCache();
										
					while($child != null){
						if(!in_array($child->getMemberId(), $members_ids)){
							$members_ids[] = $child->getMemberId();
							$members_parents_ids[$child->getMemberId()] = $child->getParentMemberId();
							$child = $child->getParentMemberCache();
						}else{
							break;
						}
					}
								
				}				
			}
			
			
			//Get all members	
			$extra_conditions = " AND id IN (".implode(",",$members_ids).")";
			$all_members = $dimension->getAllMembers(false, null, true, $extra_conditions, null);
				
			//Add an extra temp param with the cached parent id
			foreach ($all_members as $member){
				$member->cached_parent_member_id = $members_parents_ids[$member->getId()];
			}
				
		}
		
		return $all_members;
	}
	
	/**
	 * This function updates all user inheritance line cache for a member
	 * @param unknown_type $user
	 * @param int $member_id
	 * @param int $parent_member_id - is better for performance if you pass this param
	 */
	static function updateContactMemberCache($user, $member_id, $parent_member_id = null) {
		$contact_member_cache_to_save = array();
		//Contact Permission Group Ids
		$contact_pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV($user->getId());
						
		$member_info['id'] = $member_id;
		
		if(is_null($parent_member_id)){
			$member = Members::getMemberById($member_id);
			if ($member instanceof Member) {
				$member_info['parent_member_id'] = $member->getParentMemberId();
			} else {
				$member_info['parent_member_id'] = 0;
			}
		}else{
			$member_info['parent_member_id'] = $parent_member_id;
		}
		
		//PARENTS
		//Get all the parent members that are in the same inheritance line (the order is important)
		$parents = get_all_parents_sorted($member_info);
		$parents = array_reverse($parents);
		
		$parentMembersSet = array();
		foreach ($parents as $m) {
			//Check Permissions
			if (ContactMemberPermissions::instance()->contactCanAccessMemberAll($contact_pg_ids, $m['id'], $user, ACCESS_LEVEL_READ, false)) {
				//new parent for this member
				$lastParentId = end(array_keys($parentMembersSet));
				if(!$lastParentId){
					$lastParentId = 0;
				}

				$parentMembersSet[$m['id']] = true;
				
				$id = array('contact_id' => $user->getId(), 'member_id' => $m['id']);
				$contactMemberCache = ContactMemberCaches::getContactMemberCacheById($id);
		
				if($contactMemberCache instanceof ContactMemberCache){
					//update the ContactMemberCache
					$contactMemberCache->setParentMemberId($lastParentId);
					$contactMemberCache->save();
				}else{
					//create the ContactMemberCache
					$contact_member_cache_to_save[] = array( $user->getId(), $m['id'], $lastParentId);
				}				
			}			
		}
		
		$lastParentId = end(array_keys($parentMembersSet));
		if(!$lastParentId){
			$lastParentId = 0;
		}
		
		//CURRENT MEMBER
		//Check Permissions for this member
		$cmc_id = array('contact_id' => $user->getId(), 'member_id' => $member_id);
		$contactMemberCache = ContactMemberCaches::getContactMemberCacheById($cmc_id);
		if (!ContactMemberPermissions::instance()->contactCanAccessMemberAll($contact_pg_ids, $member_id, $user, ACCESS_LEVEL_READ, false)) {
			if($contactMemberCache instanceof ContactMemberCache){
				//delete the ContactMemberCache
				$contactMemberCache->delete();
			}
		}else{
			if($contactMemberCache instanceof ContactMemberCache){
				//update the ContactMemberCache
				$contactMemberCache->setParentMemberId($lastParentId);
				$contactMemberCache->save();
			}else{
				//create the ContactMemberCache
				$contact_member_cache_to_save[] = array( $user->getId(), $member_id, $lastParentId);
			}
			
			$lastParentId = $member_id;
		}
		
		//CHILDS
		//Get all member childs recursive
		$childs = get_all_children_sorted($member_info);
		
		$lastParentIdByDepth = array();
		if(isset($member) && $member instanceof Member){
			$cm_depth = $member->getDepth();
		}else{
			$cm_depth = count($parents) + 1;
		}
		$lastParentIdByDepth[$cm_depth] = $lastParentId;
				
		foreach ($childs as $m) {
			//Check Permissions
			if (ContactMemberPermissions::instance()->contactCanAccessMemberAll($contact_pg_ids, $m['id'], $user, ACCESS_LEVEL_READ, false)) {
				$tempParent = 0;				
				for ($i = $cm_depth; $i <= $m['depth']; $i++) {
					if(isset($lastParentIdByDepth[$i-1]) && $lastParentIdByDepth[$i-1] > 0){
						$tempParent = $lastParentIdByDepth[$i-1];						
					}					
				}
								
				$lastParentIdByDepth[$m['depth']] = $m['id'];				
		
				$cmc_id = array('contact_id' => $user->getId(), 'member_id' => $m['id']);
				$contactMemberCache = ContactMemberCaches::getContactMemberCacheById($cmc_id);
					
				if($contactMemberCache instanceof ContactMemberCache){
					$contactMemberCache->setParentMemberId($tempParent);
						
					$contactMemberCache->save();
				}else{
					//create the ContactMemberCache
					$contact_member_cache_to_save[] = array( $user->getId(), $m['id'], $tempParent);
				}
					
			}else{
				$lastParentIdByDepth[$m['depth']] = 0;
			}
		}
		
		// Insert new rows
		$table = TABLE_PREFIX."contact_member_cache";
		$cols = array("contact_id", "member_id", "parent_member_id") ;
		if(count($contact_member_cache_to_save) > 0){
			massiveInsert($table, $cols, $contact_member_cache_to_save);
		}
		
	}
	
	static function getAllChildrenIdsFromCache($contact_id, $parent_id) {
		// Prepare SQL
		$sql = "SELECT cmc.member_id FROM ".TABLE_PREFIX."contact_member_cache cmc
					WHERE cmc.contact_id = ".$contact_id."
							AND cmc.parent_member_id = ".$parent_id."		
					ORDER BY member_id DESC					
				";
		
		// Run!
		$rows = DB::executeAll($sql);
		
		$ids = array();
		
		// Empty?
		if(!is_array($rows) || (count($rows) < 1)) return $ids;
				
		foreach($rows as $row) {
			$ids[] = $row['member_id'];			
		} // foreach
		return $ids;
	}
	
	
	/**
	 * This function updates all user inheritance line cache for all members
	 * @param Contact $user
	 */
	static function updateContactMemberCacheAllMembers($user) {
		if ($user instanceof Contact && $user->isUser()) {
			$dimensions = Dimensions::findAll();
			$dimensions_ids = array();
			foreach ($dimensions as $dimension) {
				if ($dimension->getDefinesPermissions()) {
					$dimensions_ids[] = $dimension->getId();
				}
			}
			
			$dimensions_ids = implode(",",$dimensions_ids);
			$root_members = DB::executeAll("SELECT * FROM ".TABLE_PREFIX."members WHERE dimension_id IN (".$dimensions_ids.") AND parent_member_id=0 ORDER BY id");
			foreach ($root_members as $member) {
				ContactMemberCaches::updateContactMemberCache($user, $member['id'], $member['parent_member_id']);
			}
		}
	}
}
