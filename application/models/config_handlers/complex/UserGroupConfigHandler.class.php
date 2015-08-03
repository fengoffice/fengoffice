<?php

  /**
  * Class that handles integer config values
  *
  * @version 1.0
  * @author Carlos Palma <chonwil@gmail.com>
  */
  class UserGroupConfigHandler extends ConfigHandler {
    
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
    	
    	$groups = PermissionGroups::getNonRolePermissionGroups();
    	
    	$selected = config_option('default_guest_user_group');
    	
    	$options = array();
    	$attrs = $selected == 0 ? array('selected' => 'selected') : array();
    	$options[] = option_tag(lang('none'), 0, $attrs);
    	
    	foreach ($groups as $group) {
    		$attrs = $selected == $group->getId() ? array('selected' => 'selected') : array();
    		$options[] = option_tag(clean($group->getName()), $group->getId(), $attrs);
    	}
    	
    	echo select_box($control_name, $options);
    } // render
    
  } // UserCompanyConfigHandler

?>