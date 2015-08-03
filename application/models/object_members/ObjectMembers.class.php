<?php

  /**
  * ObjectMembers
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class ObjectMembers extends BaseObjectMembers {
    
    	
  		static function addObjectToMembers($object_id, $members_array){
  			
  			foreach ($members_array as $member){
  				$values = "(".$object_id.",".$member->getId().",0)";
  				DB::execute("INSERT INTO ".TABLE_PREFIX."object_members (object_id,member_id,is_optimization) VALUES $values ON DUPLICATE KEY UPDATE object_id=object_id");
  			}
  			
  			foreach ($members_array as $member){
  				$parents = $member->getAllParentMembersInHierarchy(false, false);
  				$stop = false;
  				foreach ($parents as $parent){
  					if (!$stop){
	  					$exists = self::findOne(array("conditions" => array("`object_id` = ? AND `member_id` = ? ", $object_id, $parent->getId())))!= null;
	  					if (!$exists){
	  						$values = "(".$object_id.",".$parent->getId().",1)";
  							DB::execute("INSERT INTO ".TABLE_PREFIX."object_members (object_id,member_id,is_optimization) VALUES $values ON DUPLICATE KEY UPDATE object_id=object_id");
	  					}
	  					else $stop = true;	
  					} 
  				}
  			}
  		}
  		
  		
		/**
		 * Removes the object from those members where the user can see the object(and its corresponding parents)
		 * 
		 */
  		static function removeObjectFromMembers(ContentDataObject $object, Contact $contact, $context_members, $members_to_remove = null, $check_permissions = true){
  			
  			if (is_null($members_to_remove)) {
  				$member_ids = array_flat(DB::executeAll("SELECT om.member_id FROM ".TABLE_PREFIX."object_members om
  						INNER JOIN ".TABLE_PREFIX."members m ON m.id=om.member_id
  						INNER JOIN ".TABLE_PREFIX."dimensions d On d.id=m.dimension_id 
  						WHERE d.is_manageable=1 AND om.object_id = " . $object->getId()));
  			} else {
  				$member_ids = $members_to_remove;
  			}
  			
  			$memebers_deleted_ids = array();
  			foreach($member_ids as $id){
				
				$member = Members::findById($id);
				if (!$member instanceof Member) continue;
				
				if($check_permissions){
					//can write this object type in the member
					$can_write = $object->canAddToMember($contact, $member, $context_members);
				}else{
					$can_write = true;
				}
				
				if ($can_write){
					$om = self::findById(array('object_id' => $object->getId(), 'member_id' => $id));
					if ($om instanceof ObjectMember) {
						$om->delete();
						$memebers_deleted_ids[] = $id;
					}
					
					$stop = false;
					while ($member->getParentMember() != null && !$stop){
						$member = $member->getParentMember();
						$obj_member = ObjectMembers::findOne(array("conditions" => array("`object_id` = ? AND `member_id` = ? AND 
									`is_optimization` = 1", $object->getId(),$member->getId())));
						if (!is_null($obj_member)) {
							$obj_member->delete();
						}
						else $stop = true;
					}
				}
			}
			
			return $memebers_deleted_ids;
  		}
  		
  		
  		static function getMemberIdsByObject($object_id){
  			if ($object_id) {
	  			$db_res = DB::execute("SELECT member_id FROM ".TABLE_PREFIX."object_members WHERE object_id = $object_id AND is_optimization = 0");
	  			$rows = $db_res->fetchAll();
  			} else {
  				return array();
  			}
  				
  			$member_ids = array();
  			if(count($rows) > 0){
  				foreach ($rows as $row){
  					$member_ids[] = $row['member_id'];
  				}
  			}
  			
  			return $member_ids;
  		}
  		
  		
  		private $cached_object_members = array();
  		function getCachedObjectMembers($object_id, $all_object_ids = null) {
  			if (!isset($this->cached_object_members[$object_id])) {
  				if (is_array($all_object_ids) && count($all_object_ids) > 0) {
  					$obj_cond = "AND object_id IN (".implode(",", $all_object_ids).")";
  				} else {
  					$obj_cond = "AND object_id = $object_id";
  				}
  				$db_res = DB::execute("SELECT object_id, member_id FROM ".TABLE_PREFIX."object_members WHERE is_optimization = 0 $obj_cond");
  				$rows = $db_res->fetchAll();
  				foreach ($rows as $row) {
  					if (!isset($this->cached_object_members[$row['object_id']])) $this->cached_object_members[$row['object_id']] = array();
  					$this->cached_object_members[$row['object_id']][] = $row['member_id'];
  				}
  				
  				if (is_array($all_object_ids)) {
  					foreach ($all_object_ids as $oid) {
  						if (!isset($this->cached_object_members[$oid])) $this->cached_object_members[$oid] = array();
  					}
  				}
  			}
  			return array_var($this->cached_object_members, $object_id, array());
  		}
  		
  		
  		
    	static function getMembersByObject($object_id){
  			$ids = self::getMemberIdsByObject($object_id);
  			$members = Members::findAll(array("conditions" => "`id` IN (".implode(",", $ids).")"));
  			
  			return $members;				  
  		}
  		
  		
  		static function getMembersByObjectAndDimension($object_id, $dimension_id, $extra_conditions = "") {
  			$sql = "
  				SELECT m.* 
  				FROM ".TABLE_PREFIX."object_members om 
  				INNER JOIN ".TABLE_PREFIX."members m ON om.member_id = m.id 
  				WHERE 
  					dimension_id = '$dimension_id' AND 
  					om.object_id = '$object_id' 
  					$extra_conditions
  				ORDER BY m.name";
  			
  			$result = array();
  			$rows = DB::executeAll($sql);
  			if (!is_array($rows)) return $result;
  			
  			foreach ($rows as $row) {
  				$member = new Member();
  				$member->setFromAttributes($row);
  				$member->setId($row['id']);
  				$result[] = $member;
  			}
  			return $result;
  		}
  		
  		static function getMembersIdsByObjectAndExtraCond($object_id, $extra_conditions = "", $limit = "", $use_contact_member_cache = true){
  			if ($object_id) {
  				// Prepare Limit SQL
  				$SQL_LIMIT = '' ;
  				if (is_numeric($limit) && $limit>0){
  					$SQL_LIMIT = "LIMIT 0, ".$limit;
  				}
  				
  				$cache_sql = '';
  				if($use_contact_member_cache){
  					$contact_id = logged_user()->getId();
  					$cache_sql = "INNER JOIN ".TABLE_PREFIX."contact_member_cache cmc ON m.id = cmc.member_id AND cmc.contact_id = '$contact_id'";  						
  				}
  				
  				$sql = "
  					SELECT om.member_id
  					FROM ".TABLE_PREFIX."object_members om
  					INNER JOIN ".TABLE_PREFIX."members m ON om.member_id = m.id
  					$cache_sql
  					WHERE
  						om.object_id = '$object_id'
  						$extra_conditions
  					ORDER BY om.member_id
  					$SQL_LIMIT
  				";
  				$db_res = DB::execute($sql);
  				$rows = $db_res->fetchAll();
  			} else {
  				return array();
  			}
  		
  			$member_ids = array();
  			if(count($rows) > 0){
  				foreach ($rows as $row){
  					$member_ids[] = $row['member_id'];
  				}
  			}
  				
  			return $member_ids;
  		}
     
  		
  } // ObjectMembers 

?>