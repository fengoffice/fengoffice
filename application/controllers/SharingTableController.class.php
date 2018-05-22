<?php 
class  SharingTableController extends ApplicationController {
	
	/**
	 * When updating perrmissions, sharing table should be updated
	 * @param stdClass $permission:  
	 * 			[m] => 36 : Member Id 
	 * 			[o] => 3 : Object Type Id 
	 * 			[d] => 0 //delete
	 * 			[w] => 1 //write
	 * 			[r] => 1 //read 
	 * @throws Exception
	 */
	function afterPermissionChanged($groups, $permissions, $root_perm_info = null) {
		if (!is_array($groups)) {
			if (is_numeric($groups)) $groups = array($groups);
			else return;
		}

        $modified_members = array();
        foreach ($permissions as $permission) {
            $memberId = $permission->m;
            $objectTypeId = $permission->o;
            if (!$memberId || !$objectTypeId) continue;
            $modified_members[$objectTypeId][] = $memberId;
        }

        foreach ($groups as $group) {
            SharingTables::instance()->fill_sharing_table_by_permission_group($group, $modified_members, $root_perm_info);
		}
	}
	
	
}