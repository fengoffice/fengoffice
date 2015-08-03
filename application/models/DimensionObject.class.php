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
	
	function getIconClass(){
		return '';
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