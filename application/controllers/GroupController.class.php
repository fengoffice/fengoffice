<?php

/**
 * Group controller
 *
 * @version 1.0
 * @author Marcos Saiz <marcos.saiz@gmail.com>
 */
class GroupController extends ApplicationController {

	/**
	 * Construct the GroupController
	 *
	 * @param void
	 * @return GroupController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website'); 
	} // __construct
	
	/**
	 * View specific group
	 *
	 * @param void
	 * @return null
	 */
	function view() {
		if(!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
		}

		$group = PermissionGroups::instance()->findById(get_id());
		if(!($group instanceof PermissionGroup)) {
			flash_error(lang('group dnx'));
			$this->redirectTo('administration');
		}
		tpl_assign('group_users', $group->getUsers());
		tpl_assign('group', $group);
	}
	
	/**
	 * Add group
	 *
	 * @param void
	 * @return null
	 */
	function add() {

		if(!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
		} // if

		$group = new PermissionGroup();
		$group_data = array_var($_POST, 'group');
		
		if(!is_array($group_data)) {
			
			tpl_assign('group', $group);
			tpl_assign('group_data', $group_data);

			// System permissions
			tpl_assign('system_permissions', new SystemPermission());

			// Module permissions
			$module_permissions_info = array();
			$all_modules = TabPanels::instance()->findAll(array("conditions" => "`enabled` = 1", "order" => "ordering"));
			$all_modules_info = array();
			foreach ($all_modules as $module) {
				$all_modules_info[] = array('id' => $module->getId(), 'name' => lang($module->getTitle()), 'ot' => $module->getObjectTypeId());
			}
			tpl_assign('module_permissions_info', $module_permissions_info);
			tpl_assign('all_modules_info', $all_modules_info);
			tpl_assign('system_permissions', new SystemPermission());
			
			// Member permissions
			$parameters = permission_form_parameters(0);
			tpl_assign('permission_parameters', $parameters);
			
			// users
			tpl_assign('groupUserIds', array());
			tpl_assign('users', Contacts::getAllUsers());
			tpl_assign('pg_id', -1);
		} else {
			$group->setFromAttributes($group_data);
			try {
				DB::beginWork();
				$group->setType('user_groups');
				$group->setContactId(0);
				$group->save();
				
				// set permissions
				$pg_id = $group->getId();
				
				// save users
				if ($users = array_var($_POST, 'user')) {
					foreach ($users as $user_id => $val){
						if ($val=='1' && is_numeric($user_id) && (Contacts::instance()->findById($user_id) instanceof Contact)) {
							$cpg = new ContactPermissionGroup();
							$cpg->setPermissionGroupId($pg_id);
							$cpg->setContactId($user_id);
							$cpg->save();
						}
					}
				}
				
				//ApplicationLogs::createLog($group, ApplicationLogs::ACTION_ADD);
				DB::commit();
				flash_success(lang('success add group', $group->getName()));
				ajx_current("back");

			} catch(Exception $e) {
				DB::rollback();
				tpl_assign('error', $e);
				return;
			} // try
			
			try {
				save_user_permissions_background(logged_user(), $pg_id);
			} catch(Exception $e) {
				tpl_assign('error', $e);
			}
		} // if
	} // add_group

	/**
	 * Edit group
	 *
	 * @param void
	 * @return null
	 */
	function edit() {
		$this->setTemplate('add');

		if(!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
		} // if

		$group = PermissionGroups::instance()->findById(get_id());
		if(!($group instanceof PermissionGroup)) {
			flash_error(lang('group dnx'));
			$this->redirectTo('administration', 'groups');
		} // if

		$group_data = array_var($_POST, 'group');
		if(!is_array($group_data)) {
			$pg_id = $group->getId();
			$parameters = permission_form_parameters($pg_id);
			
			// Module Permissions
			$module_permissions = TabPanelPermissions::instance()->findAll(array("conditions" => "`permission_group_id` = $pg_id"));
			$module_permissions_info = array();
			foreach ($module_permissions as $mp) {
				$module_permissions_info[$mp->getTabPanelId()] = 1;
			}
			$all_modules = TabPanels::instance()->findAll(array("conditions" => "`enabled` = 1", "order" => "ordering"));
			$all_modules_info = array();
			foreach ($all_modules as $module) {
				$all_modules_info[] = array('id' => $module->getId(), 'name' => lang($module->getTitle()), 'ot' => $module->getObjectTypeId());
			}
			
			// System Permissions
			$system_permissions = SystemPermissions::instance()->findById($pg_id);
			
			tpl_assign('module_permissions_info', $module_permissions_info);
			tpl_assign('all_modules_info', $all_modules_info);
			tpl_assign('system_permissions', $system_permissions);
			
			tpl_assign('permission_parameters', $parameters);
			
			// users
			$group_users = array();
			$cpgs = ContactPermissionGroups::instance()->findAll(array("conditions" => "`permission_group_id` = $pg_id"));
			foreach($cpgs as $cpg) $group_users[] = $cpg->getContactId();
			tpl_assign('groupUserIds', $group_users);
			tpl_assign('users', Contacts::getAllUsers());
			
			tpl_assign('pg_id', $group->getId());
			tpl_assign('group', $group);
			tpl_assign('group_data', array('name' => $group->getName()));
			
			add_page_action(lang('delete'), "javascript:if(confirm(lang('confirm delete group'))) og.openLink('" . $group->getDeleteUrl() ."');", 'ico-trash', null, null, true);
		} else {
			try {
				$group->setFromAttributes($group_data);
				DB::beginWork();
				$group->save();
				
				// set permissions
				$pg_id = $group->getId();
				//save_permissions($pg_id);
				$gr_users = $group->getUsers();
				$gr_users_ids = array();
				if ($post_users = array_var($_POST, 'user')) {
					foreach ($post_users as $user_id => $val){
						if ($val == '1' && is_numeric($user_id)) {
							$gr_users_ids[] = $user_id;
						}
					}
				}
				foreach ($gr_users as $us){
					if(!in_array($us->getId(), $gr_users_ids)){
						$gr_users_ids[] = $us->getId();
					}
				}
				
				// save users
				ContactPermissionGroups::instance()->delete("`permission_group_id` = $pg_id");
				if ($users = array_var($_POST, 'user')) {
					foreach ($users as $user_id => $val){
						if ($val=='1' && is_numeric($user_id) && (Contacts::instance()->findById($user_id) instanceof Contact)) {
							$cpg = new ContactPermissionGroup();
							$cpg->setPermissionGroupId($pg_id);
							$cpg->setContactId($user_id);
							$cpg->save();
						}
					}
				}
				
				//ApplicationLogs::createLog($group, ApplicationLogs::ACTION_EDIT);
				DB::commit();
				flash_success(lang('success edit group', $group->getName()));
				ajx_current("back");

			} catch(Exception $e) {
				DB::rollback();
				tpl_assign('error', $e);
				return;
			}
			
			try {
				save_user_permissions_background(logged_user(), $pg_id, false, $gr_users_ids);
			} catch(Exception $e) {
				tpl_assign('error', $e);
			}
	
		}
	} // edit

	/**
	 * Delete group
	 *
	 * @param void
	 * @return null
	 */
	function delete() {
		if(!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}

		$group = PermissionGroups::instance()->findById(get_id());
		if(!($group instanceof PermissionGroup)) {
			flash_error(lang('group dnx'));
			ajx_current("empty");
			return ;
		}
		
		if ($group->getContactId() > 0) {
			flash_error(lang('cannot delete personal permissions'));
			ajx_current("empty");
			return ;
		}

		try {
			DB::beginWork();
			$group->delete();
			//ApplicationLogs::createLog($group, ApplicationLogs::ACTION_DELETE);
			DB::commit();

			flash_success(lang('success delete group', $group->getName()));
			ajx_current("back");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete group'));
			ajx_current("empty");
		} // try
	} // delete_group

	
	/**
	 * Retrieves a list of all permission groups.
	 *
	 * @return array An array containing the list of permission groups.
	 */
	function list_all_permission_groups() {
		
		$more_conditions = "";
		$id_no_select = array_var($_REQUEST, 'id_no_select');
		if (is_numeric($id_no_select)) {
			$more_conditions .= " AND id != $id_no_select";
		}

		$name_condition = '';
		if ($name = array_var($_REQUEST, 'name')) {
			$name_condition = " AND (`first_name` LIKE '%{$name}%' OR `surname` LIKE '%{$name}%')";
		}

		$groups = PermissionGroups::instance()->findAll(array("conditions" => "`type` IN ('user_groups','permission_groups') $more_conditions"));;
		$groups_data = array();
		foreach ($groups as $group) {
			
			if ($group->getType() == 'user_groups') {
				$group_name = escape_character($group->getName());
				$group_type = 'group';
			} else if ($group->getType() == 'permission_groups') {
				$conditions = array("conditions" => "`object_id` = {$group->getContactId()}" . $name_condition);
			
				//Get the contact of the permission group
				$contact = Contacts::instance()->findOne(
					$conditions
				);
				if (!$contact instanceof Contact) continue;
				$group_name = escape_character($contact->getName());
				$group_type = 'contact';
			}
			
			$groups_data[$group_name . "-" . $group->getId()] = array(
					'id' => $group->getId(),
					'name' => $group_name,
					'type' => $group_type,
			);
		}
		
		ksort($groups_data);
		$groups_data = array_values($groups_data);
		
		ajx_extra_data(array('objects' => $groups_data, "totalCount" => count($groups_data), 'start' => 0));
		ajx_current("empty");
	}
	
	
	function search_permission_group() {
		$name = trim(array_var($_REQUEST, 'query', ''));
		$start = array_var($_REQUEST, 'start' , 0);
		$orig_limit = array_var($_REQUEST, 'limit');
		$limit = $orig_limit + 1;
		
		$query_name = "";
		if(strlen($name) > 0) {
			$query_name = "AND (c.first_name LIKE '%$name%' OR c.surname LIKE '%$name%' OR pg.name LIKE '%$name%')";
		}
		
		// query for permission groups
		$sql = "SELECT * FROM ".TABLE_PREFIX."permission_groups pg LEFT JOIN ".TABLE_PREFIX."contacts c ON pg.id=c.permission_group_id
			WHERE (pg.type='permission_groups' AND c.user_type >= ".logged_user()->getUserType()." AND c.disabled=0 OR pg.type='user_groups') $query_name
			ORDER BY c.first_name, c.surname, pg.name
			LIMIT $start, $limit";
		
		$rows = DB::executeAll($sql);
		if (!is_array($rows)) $rows = array();
		
		// show more
		$show_more = false;
		if(count($rows) > $orig_limit){
			array_pop($rows);
			$show_more = true;
		}
		
		if($show_more){
			ajx_extra_data(array('show_more' => $show_more));
		}
		
		$tmp_companies = array();
		$tmp_roles = array();
		
		$permission_groups = array();
		foreach ($rows as $pg_data) {
			// basic data
			$data = array(
				'pg_id' => $pg_data['id'],
				'type' => $pg_data['type'] == 'permission_groups' ? 'user' : 'group',
				'iconCls' => '',
				'name' => is_null($pg_data['first_name']) && is_null($pg_data['surname']) ? $pg_data['name'] : trim($pg_data['first_name'] . ' ' . $pg_data['surname']),
			);
			// company name
			$comp_id = array_var($pg_data, 'company_id');
			if ($comp_id > 0) {
				if (!isset($tmp_companies[$comp_id])) $tmp_companies[$comp_id] = Contacts::instance()->findById($comp_id);
				$c = array_var($tmp_companies, $comp_id);
				if ($c instanceof Contact) {
					$data['company_name'] = trim($c->getObjectName());
				}
			}
			// picture
			if ($pg_data['type'] == 'permission_groups') {
				$data['user_id'] = array_var($pg_data, 'object_id');
				if (array_var($pg_data, 'picture_file') != '') {
					$data['picture_url'] = get_url('files', 'get_public_file', array('id' => array_var($pg_data, 'picture_file')));
				}
			}
			// user type
			$user_type_id = array_var($pg_data, 'user_type');
			if ($user_type_id > 0) {
				if (!isset($tmp_roles[$user_type_id])) $tmp_roles[$user_type_id] = PermissionGroups::instance()->findById($user_type_id);
				$rol = array_var($tmp_roles, $user_type_id);
				if ($rol instanceof PermissionGroup) {
					$data['role_id'] = $rol->getId();
					$data['role'] = trim($rol->getName());
					if (in_array($rol->getName(), array('Guest', 'Guest Customer'))) {
						$data['is_guest'] = '1';
					}
				}
			}
			$permission_groups[] = $data;
		}
		
		$row = "search-result-row-medium";
		ajx_extra_data(array('row_class' => $row));
		
		ajx_extra_data(array('permission_groups' => $permission_groups));
			
		
		ajx_current("empty");
	}
} // GroupController

?>