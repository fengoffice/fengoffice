<?php 
class  ContactMemberCacheController extends ApplicationController {
	
	/**
	 * 
	 * @param Contact $user
	 * @param array $permissions
	 * @param ContactPermissionGroup $group
	 */
	function afterUserPermissionChanged($user, $permissions, $group = null) {
		
		//get members ids
		$membersIds = array();
				
		if(is_null($group)){
			//get all members affected from $permission
			foreach ($permissions as $permission) {
				$memberId = $permission->m;
				if(!in_array($memberId,$membersIds)){
					$membersIds[] = $memberId;					
				}
			}
		}else{
			// dimension
			$dimensions = Dimensions::findAll();
			$contact_pg_ids = $group->getId();
			
			//get all allowed members for the group
			$allowed_members = array();
			foreach ($dimensions as $dimension) {
				$member_list = array();
				if ($dimension->getDefinesPermissions()) {
					$member_list = DB::executeAll("SELECT * FROM ".TABLE_PREFIX."members WHERE dimension_id = ".$dimension->getId()." ORDER BY id");
				}
				foreach ($member_list as $dim_member){
					if (ContactMemberPermissions::instance()->contactCanAccessMemberAll($contact_pg_ids, $dim_member['id'], $user, ACCESS_LEVEL_READ, false)) {
						$allowed_members[] = $dim_member['id'];
					}
				}				
			}
			$membersIds = $allowed_members;
		}
				
		foreach ($membersIds as $member_id) {			
			ContactMemberCaches::updateContactMemberCache($user, $member_id);				
		}
	}
	
	/**
	 *	
	 * @param array $permissions with the member and the changed_pgs
	 */
	function afterMemberPermissionChanged($permissions) {
		$member = array_var($permissions, 'member');		
		//get all users in the set of permissions groups
		$permissionGroupIds = array();
		foreach (array_var($permissions, 'changed_pgs') as $pg_id) {
			$permissionGroupId = $pg_id;
			
			if(!in_array($permissionGroupId,$permissionGroupIds)){
				$permissionGroupIds[] = $permissionGroupId;				
			}
		}
		
		if(count($permissionGroupIds) > 0){	
			$usersIds = ContactPermissionGroups::getAllContactsIdsByPermissionGroupIds($permissionGroupIds);
			foreach ($usersIds as $us_id) {
				$user = Contacts::findById($us_id);
				ContactMemberCaches::updateContactMemberCache($user, $member->getId());
			}
			
		}else{
			//update this member for all user in cache 
			$contacts = Contacts::getAllUsers();
			foreach ($contacts as $contact) {
				ContactMemberCaches::updateContactMemberCache($contact, $member->getId());
			}
		}
	}
	
	
	
}