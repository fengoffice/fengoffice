<?php


  /**
  * SystemPermissions
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class SystemPermissions extends BaseSystemPermissions {
    
  		private static $permission_cache = array();
  		private static $permission_group_ids_cache = array();
  	
  		static function userHasSystemPermission(Contact $user, $system_permission){
  			if($user instanceof Contact && $user->isAdministrator()) return true;
  			
  			if (array_var(self::$permission_cache, $user->getId())) {
  				if (array_key_exists($system_permission, self::$permission_cache[$user->getId()])) {
  					return array_var(self::$permission_cache[$user->getId()], $system_permission);
  				}
  			}
  			
  			if (array_var(self::$permission_group_ids_cache, $user->getId())) {
  				$contact_pg_ids = self::$permission_group_ids_cache[$user->getId()];
  			} else {
				$contact_pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV($user->getId(),false);
				self::$permission_group_ids_cache[$user->getId()] = $contact_pg_ids;
  			}
  			
			$permission = self::findOne(array('conditions' => "`$system_permission` = 1 AND `permission_group_id` IN ($contact_pg_ids)"));
			
			// check max system permission
			$max_role_system_permissions = MaxSystemPermissions::findOne(array('conditions' => 'permission_group_id = '.$user->getUserType()));
			if ($max_role_system_permissions instanceof MaxSystemPermission) {
				$max_val = $max_role_system_permissions->getColumnValue($system_permission);
				if (!$max_val) {
					$permission = null;
				}
			}
			
			if (!array_var(self::$permission_cache, $user->getId())) {
				self::$permission_cache[$user->getId()] = array();
			}
			if (!array_key_exists($system_permission, self::$permission_cache[$user->getId()])) {
				self::$permission_cache[$user->getId()][$system_permission] = !is_null($permission);
			}
			
			if (!is_null($permission)) return true;
			return false;
  			
  		}
  		
  		function roleHasSystemPermission($role_id,$system_permission){
  			$permission = self::findOne(array('conditions' => "`$system_permission` = 1 AND `permission_group_id` = '$role_id'"));
  			if (!is_null($permission)) return true;
			return false;
  		}
  	
  		function getRolePermissions($role_id){
  			$permission = self::findOne(array('conditions'=>"`permission_group_id` = '$role_id'"));
  			if ($permission instanceof SystemPermission) {
  				return $permission->getSettedPermissions();
  			}
  		}
  		
  		function getNotRolePermissions($role_id){
  			$permission = self::findOne(array('conditions'=>"`permission_group_id` = '$role_id'"));
  			if ($permission instanceof SystemPermission) {
  				return $permission->getNotSettedPermissions();
  			}
		}
  		
  		function getAllRolesPermissions(){
  			$groups = PermissionGroups::instance()->getNonPersonalSameLevelPermissionsGroups('`parent_id`,`id` ASC');
  			$roles_permissions = array();
  			foreach($groups as $group){
  				$roles_permissions[$group->getId()] = self::getRolePermissions($group->getId());
  			}
  			return $roles_permissions;
  		}
  } // SystemPermissions 
