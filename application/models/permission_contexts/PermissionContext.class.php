<?php

/**
 * PermissionContext class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class PermissionContext extends BasePermissionContext {
	
	function getMember(){
		return Members::findById($this->getMemberId());
	}
	
} // PermissionContext

?>