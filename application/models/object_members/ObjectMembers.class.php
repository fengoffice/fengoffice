<?php

  /**
  * ObjectMembers
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class ObjectMembers extends BaseObjectMembers {

		/**
		 * Per-request cache for getMembersIdsByObjectAndExtraCond results.
		 * Structure: $_members_extra_cache[$variant][$object_id] = array of rows
		 * $variant encodes extra_conditions + limit + use_contact_member_cache + user_id.
		 */
		private static $_members_extra_cache = array();

		/**
		 * Batch pre-warms the getMembersIdsByObjectAndExtraCond cache for multiple objects
		 * at once, replacing N individual queries with a single IN(...) query.
		 * Callers that process a batch of objects should invoke this before the loop.
		 * Objects not present in this pre-warm fall back to the normal per-object query.
		 *
		 * @param array  $object_ids
		 * @param string $extra_conditions   Raw SQL appended to the WHERE clause (same as in getMembersIdsByObjectAndExtraCond)
		 * @param string $limit              Numeric LIMIT or "" for no limit
		 * @param bool   $use_contact_member_cache
		 */
		static function prefetchMembersForObjects(array $object_ids, $extra_conditions = "", $limit = "", $use_contact_member_cache = true) {
			if (empty($object_ids)) return;

			$contact_id = $use_contact_member_cache ? logged_user()->getId() : '0';
			$variant    = md5($extra_conditions . '|' . $limit . '|' . ($use_contact_member_cache ? '1' : '0') . '|' . $contact_id);

			if (!isset(self::$_members_extra_cache[$variant])) {
				self::$_members_extra_cache[$variant] = array();
			}

			$to_fetch = array();
			foreach ($object_ids as $id) {
				$id = (int) $id;
				if (!array_key_exists($id, self::$_members_extra_cache[$variant])) {
					$to_fetch[]                                    = $id;
					self::$_members_extra_cache[$variant][$id] = array(); // placeholder so missing objects return []
				}
			}
			if (empty($to_fetch)) return;

			$cache_sql = '';
			if ($use_contact_member_cache) {
				$cache_sql = "INNER JOIN " . TABLE_PREFIX . "contact_member_cache cmc ON m.id = cmc.member_id AND cmc.contact_id = '$contact_id'";
			}

			$ids_list = implode(',', $to_fetch);
			// NOTE: SQL LIMIT cannot be applied per-object in a batch query.
			// When $limit is set, the getter applies it in PHP after reading from cache.
			$sql = "
				SELECT om.object_id, om.member_id, m.object_type_id, om.is_optimization
				FROM " . TABLE_PREFIX . "object_members om
				INNER JOIN " . TABLE_PREFIX . "members m ON om.member_id = m.id
				$cache_sql
				WHERE om.object_id IN ($ids_list)
					$extra_conditions
				ORDER BY om.object_id, om.member_id
			";

			$rows = DB::executeAll($sql);
			if ($rows) {
				foreach ($rows as $row) {
					$oid = (int) $row['object_id'];
					self::$_members_extra_cache[$variant][$oid][] = array(
						'member_id'      => $row['member_id'],
						'object_type_id' => $row['object_type_id'],
						'is_optimization'=> $row['is_optimization'],
					);
				}
			}
		}


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
	  					$exists = self::instance()->findOne(array("conditions" => array("`object_id` = ? AND `member_id` = ? ", $object_id, $parent->getId())))!= null;
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
  			
  			if (!$check_permissions || $contact->isAdministrator()) {
  				$member_ids = array_filter($member_ids);
  				if (count($member_ids) > 0) {
  					DB::execute("DELETE FROM ".TABLE_PREFIX."object_members WHERE member_id IN (".implode(',', $member_ids).") AND object_id=".$object->getId());
  				
  					$all_parent_member_ids = Members::getAllParentsInHierarchy($member_ids, true);
	  				
	  				if (count($all_parent_member_ids) > 0) {
	  					DB::execute("DELETE FROM ".TABLE_PREFIX."object_members WHERE is_optimization=1 AND member_id IN (".implode(',', $all_parent_member_ids).") AND object_id=".$object->getId());
	  				}
  				}
  				$memebers_deleted_ids = $member_ids;
  				
  			} else {
  				
	  			
	  			$memebers_deleted_ids = array();
	  			foreach($member_ids as $id){
					
					$member = Members::getMemberById($id);
					if (!$member instanceof Member) continue;
					
					if($check_permissions){
						//can write this object type in the member
						$can_write = $object->canAddToMember($contact, $member, $context_members);
					}else{
						$can_write = true;
					}
					
					if ($can_write){
						$om = self::instance()->findById(array('object_id' => $object->getId(), 'member_id' => $id));
						if ($om instanceof ObjectMember) {
							$om->delete();
							$memebers_deleted_ids[] = $id;
						}
						
						$stop = false;
						while ($member->getParentMember() != null && !$stop){
							$member = $member->getParentMember();
							$obj_member = ObjectMembers::instance()->findOne(array("conditions" => array("`object_id` = ? AND `member_id` = ? AND 
										`is_optimization` = 1", $object->getId(),$member->getId())));
							if (!is_null($obj_member)) {
								$obj_member->delete();
							}
							else $stop = true;
						}
					}
				}
  			}
  			
			return $memebers_deleted_ids;
  		}
  		
  		
  		static function getMemberIdsByObject($object_id, $exclude_ot_ids = array()){
  			if ($object_id) {
				
				$ot_cond = "";
				$member_join = "";
				if (count($exclude_ot_ids) > 0) {	
					$member_join = "INNER JOIN ".TABLE_PREFIX."members m ON m.id=om.member_id";
					$ot_cond = "AND m.object_type_id NOT IN (".implode(",", $exclude_ot_ids).")";
				}
	  			$db_res = DB::execute("SELECT member_id FROM ".TABLE_PREFIX."object_members om $member_join WHERE om.object_id = $object_id AND is_optimization = 0 $ot_cond");
	  			$rows = $db_res->fetchAll();
  			} else {
  				return array();
  			}
  				
  			$member_ids = array();
  			if(is_array($rows) && count($rows) > 0){
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
		
		function clearCachedObjectMembers($object_id) {
			unset($this->cached_object_members[$object_id]);
		}

		/**
		 * Evict all per-dimension cache entries for $object_id from the static
		 * $_members_extra_cache so that getMembersIdsByObjectAndExtraCond()
		 * re-queries the DB for fresh data after a member save.
		 * Call this alongside clearCachedObjectMembers() after add_to_members /
		 * remove_from_members operations.
		 */
		static function clearMembersExtraCacheForObject($object_id) {
			$object_id = (int) $object_id;
			foreach (self::$_members_extra_cache as &$entries) {
				unset($entries[$object_id]);
			}
			unset($entries); // break the reference
		}



    	static function getMembersByObject($object_id){
  			$ids = self::getMemberIdsByObject($object_id);
  			$members = Members::instance()->findAll(array("conditions" => "`id` IN (".implode(",", $ids).")"));
  			
  			return $members;				  
  		}
  		
  		
  		static function getMembersByObjectAndDimension($object_id, $dimension_id, $extra_conditions = "", $only_ids = false) {
  			$sel_cols = $only_ids ? "m.id" : "m.*";
  			$sql = "
  				SELECT $sel_cols 
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
  			
  			if ($only_ids) {
  				return array_filter(array_flat($rows));
  			}
  			
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
  				// Check pre-warm cache before hitting the database
  				$contact_id = $use_contact_member_cache ? logged_user()->getId() : '0';
  				$variant    = md5($extra_conditions . '|' . $limit . '|' . ($use_contact_member_cache ? '1' : '0') . '|' . $contact_id);
  				if (isset(self::$_members_extra_cache[$variant]) && array_key_exists((int) $object_id, self::$_members_extra_cache[$variant])) {
  					$cached = self::$_members_extra_cache[$variant][(int) $object_id];
  					// The batch prewarm skips SQL LIMIT, so apply it here if needed
  					if (is_numeric($limit) && $limit > 0) {
  						return array_slice($cached, 0, (int) $limit);
  					}
  					return $cached;
  				}

  				// Cache miss — fall back to individual DB query (original behavior)
  				// Prepare Limit SQL
  				$SQL_LIMIT = '' ;
  				if (is_numeric($limit) && $limit>0){
  					$SQL_LIMIT = "LIMIT 0, ".$limit;
  				}

  				if($use_contact_member_cache){
  					$cache_sql = "INNER JOIN ".TABLE_PREFIX."contact_member_cache cmc ON m.id = cmc.member_id AND cmc.contact_id = '$contact_id'";
  				} else {
  					$cache_sql = '';
  				}

  				$sql = "
  					SELECT om.member_id, m.object_type_id, om.is_optimization
  					FROM ".TABLE_PREFIX."object_members om
  					INNER JOIN ".TABLE_PREFIX."members m ON om.member_id = m.id
  					$cache_sql
  					WHERE
  						om.object_id = '$object_id'
  						$extra_conditions
  					ORDER BY om.member_id
  					$SQL_LIMIT
  				";
  				$result = DB::executeAll($sql);

  				// Populate cache for subsequent calls within the same request
  				if (!isset(self::$_members_extra_cache[$variant])) {
  					self::$_members_extra_cache[$variant] = array();
  				}
  				self::$_members_extra_cache[$variant][(int) $object_id] = $result ? $result : array();

  				return $result;
  			} else {
  				return array();
  			}
  		}

      /**
       * Check if an object is in root
       *
       * @param integer   $object_id  Object id
       * @return boolean
       */
      static function is_object_in_root($object_id){
          $enabled_dimensions_sql = "";
          $enabled_dimensions_ids = implode(',', array_filter(config_option('enabled_dimensions')));
          if ($enabled_dimensions_ids != "") {
              $enabled_dimensions_sql = "AND m.dimension_id IN ($enabled_dimensions_ids)";
          }
          $sql = "SELECT count(om.member_id) as total_mem FROM ".TABLE_PREFIX."object_members om
			INNER JOIN ".TABLE_PREFIX."members m ON m.id=om.member_id
			INNER JOIN ".TABLE_PREFIX."dimensions d ON d.id=m.dimension_id
			WHERE om.object_id='$object_id' AND om.is_optimization=0
			AND d.defines_permissions=1 $enabled_dimensions_sql";
          $r = DB::executeOne($sql);

          $is_classified = $r['total_mem'] > 0;

          return !$is_classified;
      }
     
  		
  } // ObjectMembers 

?>