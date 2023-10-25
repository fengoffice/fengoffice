<?php

  /**
  * TabPanelPermissions
  *
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  class TabPanelPermissions extends BaseTabPanelPermissions {

     static function clearByPermissionGroup($pg_id, $only_active_panels = false) {
     	$extra_conditions = "";
     	if ($only_active_panels) {
     		$extra_conditions = " AND tab_panel_id IN (SELECT id FROM ".TABLE_PREFIX."tab_panels WHERE enabled=1)";
     	}
     	self::instance()->delete("`permission_group_id` = '$pg_id' $extra_conditions");
     }
     
     
    static function isModuleEnabled($tab_panel_id, $pg_ids){
     	$tab_permission = self::instance()->findOne(array('conditions'=>"`tab_panel_id` = '$tab_panel_id' AND `permission_group_id` IN ($pg_ids)"));
     	
     	if (!is_null($tab_permission))return true;
     	return false;
     }
   	static function getRoleModules($rol){
     	$tab_permission = self::instance()->findAll(array('conditions'=>"`permission_group_id`='$rol'"));
     	$tabs=array();
     	foreach($tab_permission as $tab){
     		$tabs[]=$tab->getColumnValue('tab_panel_id');
     	}
     	return $tabs;
    }
    static function getAllRolesModules(){
    	$groups=PermissionGroups::getNonPersonalPermissionGroups('`parent_id`,`id` ASC');
  		$roles_permissions=array();
  		$tabs=array();
  		foreach($groups as $group){
  			$tabs[$group->getId()]=array();
  			$tabs[$group->getId()]=self::getRoleModules($group->getId());
  		}
  		return $tabs;
    }
  		
  } // TabPanelPermissions 

?>