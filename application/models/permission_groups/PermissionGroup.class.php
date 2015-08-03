<?php

/**
 * PermissionGroup class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class PermissionGroup extends BasePermissionGroup {
	
	function getUsers() {
		return Contacts::findAll(array("conditions" => "`id` IN ( SELECT `contact_id` FROM ".ContactPermissionGroups::instance()->getTableName(true)." 
			WHERE `permission_group_id` = ".$this->getId().")"));
	}
	
	function getViewUrl() {
		return get_url('group', 'view', array("id" => $this->getId()));
	}
	
	function getEditUrl() {
		return get_url('group', 'edit', array("id" => $this->getId()));
	}
	
	function getDeleteUrl() {
		return get_url('group', 'delete', array("id" => $this->getId()));
	}
	
	/**
	 * Add permissions for a contact on members
	 * @param array $members_id  Array with the ids of members
	 * @param array $rol_permissions Array with the permissions for the user type of the contact
	 * @return null
	 */
	function addPermissions($members_id, $rol_permissions) {
		//permissions
		$permissions = "";
		foreach ($rol_permissions as $permission){
			if ($permissions != "") $permissions .= ','; 
			$permissions .= '{"pg":"'.$this->getId().'","o":'.$permission['object_type_id'].',"d":'.$permission['can_delete'].',"w":'.$permission['can_write'].',"r":1}';
		}
		$permissions = "[".$permissions."]";
		
		//members
		$members = array();
		foreach ($members_id as $member_id){
			$mem = Members::findById($member_id);
			if (!$mem instanceof Member) {
				continue;
			}
			$members[] = $mem;
		}
		
		
		//save permissions 
		foreach($members as $member){
			save_member_permissions_background(logged_user(), $member, $permissions);	
		}
	}
	
	function delete() {
		// delete system permissions
		SystemPermissions::delete("`permission_group_id` = ".$this->getId());
		// delete member permissions
		ContactMemberPermissions::delete("`permission_group_id` = ".$this->getId());
		// delte dimension permissions
		ContactDimensionPermissions::delete("`permission_group_id` = ".$this->getId());
		// delete contact_permission_group entries
		ContactPermissionGroups::delete("`permission_group_id` = ".$this->getId());
		// delete tab panel permissions
		TabPanelPermissions::delete("`permission_group_id` = ".$this->getId());
		
		parent::delete();
	}
	
} // PermissionGroup

?>