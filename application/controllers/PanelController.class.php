<?php

class PanelController extends ApplicationController {
	
	var $panels = null;
	
	function __construct() {
		parent::__construct ();
		prepare_company_website_controller ( $this, 'website' );
	} // __construct	
	

	private function loadPanels($options) {
		if (! $this->panels) {
			$contact_pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV(logged_user()->getId(),false);
			$this->panels = array();
			$sql = "
				SELECT * FROM " . TABLE_PREFIX . "tab_panels 
				WHERE 
					enabled = 1 AND					
					( 	
						plugin_id IS NULL OR plugin_id=0 OR
						plugin_id IN (SELECT id FROM ".TABLE_PREFIX."plugins WHERE is_installed = 1 AND is_activated = 1) 
					)
					AND id IN (SELECT tab_panel_id FROM ".TABLE_PREFIX."tab_panel_permissions WHERE permission_group_id IN ($contact_pg_ids))
				ORDER BY ordering ASC ";
			
			$res = DB::execute ( $sql );
			$can_see_billing_info = true;
			Hook::fire('get_can_see_billing_information', array('user'=>logged_user()), $can_see_billing_info);
			while ( $row = $res->fetchRow () ) {
				if(!$can_see_billing_info && $row['id'] == 'income-panel'){
					continue;
				}
				 $url_params = trim($row['url_params']) == '' ? array() : json_decode($row['url_params'], true);
				 
				 if ( $row['default_controller'] == 'member' && $url_params['dim_id'] != '' && $url_params['type_id'] != '') {				     
				     $name_tab = Members::getTypeNameToShowByObjectType($url_params['dim_id'], $url_params['type_id'], null, true);
				 }else{
				     $name_tab = lang($row ['title']);
				 }

				 $dim_id = '';
				 $ot_id = '';
				 foreach ($url_params as $k => $v) {
				 	if ($k == 'dim_id') $dim_id = $v;
				 	if ($k == 'type_id') $ot_id = $v;
				 }
				 
				 $object = array (
					"title" => $name_tab, 
					"id" => $row ['id'], 
				 	"quickAddTitle" => lang ($row['default_controller']), 
					"refreshOnWorkspaceChange" => (bool) $row ['refresh_on_context_change'] , 
				 	"defaultController" => $row['default_controller'] ,
					"defaultContent" => array (
						"type" => "url", 
						"data" => get_url ( $row ['default_controller'], $row ['default_action'], $url_params ) 
					),
					"enabled" => $row ['enabled'], 
					"type" => $row ['type'],
				    "tabTip" => $name_tab,
				    "dimensionId" => $dim_id,
				    "typeId" => $ot_id,
				);
				
				if (config_option('show_tab_icons')) {
					$object["iconCls"] = $row ['icon_cls'];
				}

				
				if ( $row ['initial_controller'] && $row['initial_action'] ) {
					$object["initialContent"] = array (
						"type" => "url", 
						"data" => get_url ( $row ['initial_controller'], $row ['initial_action'], $url_params ) 
					);
				}
				if ($row['id'] == 'more-panel' && config_option('getting_started_step') >= 99) {
					$object['closable'] = true;
					if (!user_config_option('settings_closed')) {
						$this->panels [] = $object;
					}
				} else {
					$this->panels [] = $object;
				}
			}
		}
		
		return $this->panels;
	}
	
	function list_all() {
		ajx_current ( "empty" );
		ajx_extra_data ( array ("panels" => $this->loadPanels ( 'all' ) ) );
	}
}