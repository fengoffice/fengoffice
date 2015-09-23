<?php

  /**
  * PermissionGroups
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class PermissionGroups extends BasePermissionGroups {
    
  	function getUserTypeGroups($order = '`name` ASC') {
  		return self::findAll(array("conditions" => "`contact_id` = 0 AND `parent_id` = 0 AND `type`='roles'", "order" => $order));
  	}
  	
    function getNonPersonalPermissionGroups($order = '`name` ASC') {
    	return self::findAll(array("conditions" => "`contact_id` = 0 AND `parent_id` != 0 AND `type`='roles'", "order" => $order));
    }
    function getNonPersonalSameLevelPermissionsGroups($order = '`name` ASC') {
    	return self::findAll(array("conditions" => "`contact_id` = 0 AND `parent_id` != 0 AND `type`='roles' AND `id` >= ".logged_user()->getUserType(), "order" => $order));
    }
    function getParentId($group_id){
    	return self::findById($group_id)->getParentId();
    }
    
    function getGuestPermissionGroups() {
    	return self::findAll(array("conditions" => "parent_id IN (SELECT p.id FROM ".TABLE_PREFIX."permission_groups p WHERE p.name='GuestGroup')"));
    }
    
    function getCollaboratorPermissionGroups() {
    	return self::findAll(array("conditions" => "parent_id IN (SELECT p.id FROM ".TABLE_PREFIX."permission_groups p WHERE p.name='CollaboratorGroup')"));
    }
    
    function getExecutivePermissionGroups() {
    	return self::findAll(array("conditions" => "parent_id IN (SELECT p.id FROM ".TABLE_PREFIX."permission_groups p WHERE p.name='ExecutiveGroup')"));
    }
    
    static function getNonRolePermissionGroups() {
		$order = '`name` ASC';
        return self::findAll(array("conditions" => "`type` = 'user_groups'",  "order" => $order));
    }
    
    function getDefaultRolesByType() {
    	$result = array();
    	
    	$exe_group = PermissionGroups::findOne(array('id' => true, 'conditions' => "name='ExecutiveGroup' AND type='roles'"));
    	$col_group = PermissionGroups::findOne(array('id' => true, 'conditions' => "name='CollaboratorGroup' AND type='roles'"));
    	$gue_group = PermissionGroups::findOne(array('id' => true, 'conditions' => "name='GuestGroup' AND type='roles'"));
    	
    	$exe = PermissionGroups::findOne(array('id' => true, 'conditions' => "name='Executive' AND type='roles'"));
    	$col = PermissionGroups::findOne(array('id' => true, 'conditions' => "name='Internal Collaborator' AND type='roles'"));
    	$gue = PermissionGroups::findOne(array('id' => true, 'conditions' => "name='Guest' AND type='roles'"));
    	
    	$result[$exe_group[0]] = $exe[0];
    	$result[$col_group[0]] = $col[0];
    	$result[$gue_group[0]] = $gue[0];
    	
    	return $result;
    }
    
    function getUserGroupsInfo($extra_conditions = "", $order = "name", $escape=true) {
    	$result = array();
    	$extra_cond = "type = 'user_groups'";
    	$extra_cond .= $extra_conditions ? $extra_conditions : "";
    	$pgs = self::findAll(array('conditions' => $extra_cond, 'order' => $order));
    	foreach ($pgs as $pg) {
    		$result[$pg->getId()] = array('id' => $pg->getId());
    		if ($escape) {
    			$result[$pg->getId()]['name'] = escape_character($pg->getName());
    		} else {
    			$result[$pg->getId()]['name'] = str_replace("'", "&apos;", $pg->getName());
    		}
    	}
    	return $result;
    }
    
  } // PermissionGroups 

?>