<?php
abstract class DimensionObject extends ContentDataObject {
	
	
	protected $is_commentable = false;
	protected $is_linkable_object = false;
	
	
	/**
	 * A Dimension Object has one to one member associated.
	 * This function returns the memeber associatted with this CO. 
	 * Note that this is different that the members that the object belongs to: 
	 * - For this call getMembers() instead
	 */
	public function getSelfMember() {
		return Members::findOneByObjectId($this->getId());
	}
	
	public function getPath( $asString = true ) {
		$member = Members::findOneByObjectId($this->getId());
		$parents = array();
		foreach ( $member->getAllParentMembersInHierarchy(false) as $parent ){
			$parents[] = $parent->getName();
		}
		$parents = array_reverse($parents);
		
		
		if ($asString) {
			if(count($parents)) {
				return "(".implode(" | ", $parents).")";
			}else{
				return "";
			}
		}else{
			return $parents ;
		}	
	}
	
	function getIconClass($large = false){
		return '';
	}
	
	
	/**
	 * Additional searchable data for dimension_objects
	 * {@inheritDoc}
	 * @see ApplicationDataObject::addToSearchableObjects()
	 */
	function addToSearchableObjects($wasNew=false) {
		// call content data objects function
		parent::addToSearchableObjects($wasNew);
		
		// add assocaited member names to searchable objects
		$member = $this->getSelfMember();
		if ($member instanceof Member) {
			$sql_values = array();
			
			// get all the associations with other dimensions
			$associations = DimensionMemberAssociations::getAssociatations($member->getDimensionId(), $member->getObjectTypeId());
			
			// foreach assocaition get the associated member ids
			foreach ($associations as $a) {
				// ignore persons dimension
				$persons_dim = Dimensions::findByCode('feng_persons');
				if ($a->getAssociatedDimensionMemberAssociationId() == $persons_dim->getId()) {
					continue;
				}
				
				$property_member_ids_csv = MemberPropertyMembers::getAllPropertyMemberIds($a->getId(), $member->getId());
				
				if (trim($property_member_ids_csv) != '') {
					// foreach associated member add its name and id to searchable objects table
					$property_members = Members::instance()->findAll(array("conditions" => "id IN ($property_member_ids_csv)"));
					foreach ($property_members as $pm) {
						if ($pm instanceof Member) {
							$sql_values[] = "('".$this->getId()."','assoc_".$a->getId()."',".DB::escape($pm->getName()).",'".$pm->getId()."')";
						}
					}
				}
			}
			
			// execute the query
			if (count($sql_values) > 0) {
				$sql = "INSERT INTO ".TABLE_PREFIX."searchable_objects (rel_object_id, column_name, content, assoc_member_id) VALUES 
						".implode(',', $sql_values)."
						ON DUPLICATE KEY UPDATE content=content";
				
				DB::execute($sql);
			}
		}
	}
	
	
	
	function addToSharingTable() {
		parent::addToSharingTable(); //  Default processing
		$oid = $this->getId() ;
		if ( $member = $this->getSelfMember() ) {
			$mid = $member->getId();
			$sql = "
				SELECT distinct(permission_group_id) as gid
			 	FROM ".TABLE_PREFIX."contact_member_permissions 
			 	WHERE member_id = $mid 
			";
			$rows = DB::executeAll($sql);
			if (is_array($rows)) {
				$values = array();
				foreach ($rows as $row ) {
					if ($gid = array_var($row, 'gid')) {
						$values[] = "($oid, $gid)";
					}
				}
				if (count($values) > 0) {
					$values_str = implode(",", $values);
					DB::execute("INSERT INTO ".TABLE_PREFIX."sharing_table (object_id, group_id) VALUES $values_str ON DUPLICATE KEY UPDATE group_id=group_id");
				}
			}
		}
	}
	
}