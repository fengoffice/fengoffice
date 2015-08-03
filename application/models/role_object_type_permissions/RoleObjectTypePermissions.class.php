<?php

/**
 * RoleObjectTypePermissions
 *
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class RoleObjectTypePermissions extends BaseRoleObjectTypePermissions {
	
	static function getAllRoleObjectTypePermissionsInfo() {
		$objects = self::findAll(array('raw_data' => true));
		$info = array();
		foreach ($objects as $obj) {
			$data = array();
			foreach ($obj->getColumns() as $col) {
				$data[$col] = $obj->getColumnValue($col);
			}
			if (!isset($info[$data['role_id']])) $info[$data['role_id']] = array();
			$info[$data['role_id']][$data['object_type_id']] = $data;
		}
		
		return $info;
	}
	
	static function createDefaultUserPermissions(Contact $user, Member $member, $remove_previous = true) {
		$role_id = $user->getUserType();
		$permission_group_id = $user->getPermissionGroupId();
		$member_id = $member->getId();
		
		try {
			if ($remove_previous) {
				ContactMemberPermissions::delete("permission_group_id = '$permission_group_id' AND member_id = $member_id");
			}
			
			$shtab_permissions = array();
			$new_permissions = array();
			$role_permissions = self::findAll(array('conditions' => "role_id = '$role_id'"));
			foreach ($role_permissions as $role_perm) {
				if ($member->canContainObject($role_perm->getObjectTypeId())) {
					$cmp = new ContactMemberPermission();
					$cmp->setPermissionGroupId($permission_group_id);
					$cmp->setMemberId($member_id);
					$cmp->setObjectTypeId($role_perm->getObjectTypeId());
					$cmp->setCanDelete($role_perm->getCanDelete());
					$cmp->setCanWrite($role_perm->getCanWrite());
					$cmp->save();
					$new_permissions[] = $cmp;
					
					$perm = new stdClass();
					$perm->m = $member_id;
					$perm->r = 1;
					$perm->w = $role_perm->getCanWrite();
					$perm->d = $role_perm->getCanDelete();
					$perm->o = $role_perm->getObjectTypeId();
					$shtab_permissions[] = $perm;
				}
			}
			if (count($shtab_permissions)) {
				$cdp = ContactDimensionPermissions::instance()->findOne(array('conditions' => "permission_group_id = '$permission_group_id' AND dimension_id = ".$member->getDimensionId()));
				if (!$cdp instanceof ContactDimensionPermission) {
					$cdp = new ContactDimensionPermission();
					$cdp->setPermissionGroupId($permission_group_id);
					$cdp->setContactDimensionId($member->getDimensionId());
					$cdp->setPermissionType('check');
					$cdp->save();
				} else {
					if ($cdp->getPermissionType() == 'deny all') {
						$cdp->setPermissionType('check');
						$cdp->save();
					}
				}
				$stCtrl = new SharingTableController();
				$stCtrl->afterPermissionChanged($permission_group_id, $shtab_permissions);
			}
			
			return $new_permissions;
			
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	
	static function createDefaultUserPermissionsAllDimension(Contact $user, $dimension_id, $remove_previous = true) {
		$role_id = $user->getUserType();
		$permission_group_id = $user->getPermissionGroupId();
		
		$dimension = Dimensions::getDimensionById($dimension_id);
		if (!$dimension instanceof Dimension || !$dimension->getDefinesPermissions()) return;
		
		try {
			
			$shtab_permissions = array();
			$new_permissions = array();
			$role_permissions = self::findAll(array('conditions' => "role_id = '$role_id'"));
			$members = Members::findAll(array('conditions' => 'dimension_id = '.$dimension_id));
			
			foreach ($members as $member) {
				$member_id = $member->getId();
				if ($remove_previous) {
					ContactMemberPermissions::delete("permission_group_id = $permission_group_id AND member_id = $member_id");
				}
				
				foreach ($role_permissions as $role_perm) {
					if ($member->canContainObject($role_perm->getObjectTypeId())) {
						$cmp = new ContactMemberPermission();
						$cmp->setPermissionGroupId($permission_group_id);
						$cmp->setMemberId($member_id);
						$cmp->setObjectTypeId($role_perm->getObjectTypeId());
						$cmp->setCanDelete($role_perm->getCanDelete());
						$cmp->setCanWrite($role_perm->getCanWrite());
						$cmp->save();
						$new_permissions[] = $cmp;
						
						$perm = new stdClass();
						$perm->m = $member_id;
						$perm->r = 1;
						$perm->w = $role_perm->getCanWrite();
						$perm->d = $role_perm->getCanDelete();
						$perm->o = $role_perm->getObjectTypeId();
						$shtab_permissions[] = $perm;
					}
				}
			}
			
			if (count($shtab_permissions)) {
				$cdp = ContactDimensionPermissions::instance()->findOne(array('conditions' => "permission_group_id = '$permission_group_id' AND dimension_id = $dimension_id"));
				if (!$cdp instanceof ContactDimensionPermission) {
					$cdp = new ContactDimensionPermission();
					$cdp->setPermissionGroupId($permission_group_id);
					$cdp->setContactDimensionId($dimension_id);
					$cdp->setPermissionType('check');
					$cdp->save();
				} else {
					if ($cdp->getPermissionType() == 'deny all') {
						$cdp->setPermissionType('check');
						$cdp->save();
					}
				}
				$stCtrl = new SharingTableController();
				$stCtrl->afterPermissionChanged($permission_group_id, $shtab_permissions);
			}
			
			return $new_permissions;
			
		} catch (Exception $e) {
			throw $e;
		}
	}
} 
