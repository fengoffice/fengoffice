<?php

/**
 * ContactPermissionGroup class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class ContactPermissionGroup extends BaseContactPermissionGroup {

	function getPermissionGroup() {
		return PermissionGroups::instance()->findById($this->getPermissionGroupId());
	}
	
	
	function getContactDimensionPermission($dimension_id) {
		return ContactDimensionPermissions::instance()->findOne(array('conditions' => '`dimension_id` = ' . $dimension_id));
	}
	
	function getPermissionTypeForDimension($dimension_id) {
		$dimension_permission = $this->getContactDimensionPermission($dimension_id);
		if ($dimension_permission instanceof ContactDimensionPermission) 
			return $dimension_permission->getPermissionType();
		return '';
	}
} // ContactPermissionGroup

?>