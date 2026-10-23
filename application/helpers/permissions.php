<?php

// Functions that check permissions
// Recomendation: Before changing this, talk with marcos.saiz@fengoffice.com

  	define('ACCESS_LEVEL_READ', 1);
  	define('ACCESS_LEVEL_WRITE', 2);
  	define('ACCESS_LEVEL_DELETE', 3);

  	  	
  	/**
  	 * Returns whether a user can manage security.
  	 *
  	 * @param Contact $user
  	 * @return boolean
  	 */
  	function can_manage_security(Contact $user){
		return SystemPermissions::userHasSystemPermission($user, 'can_manage_security');	
  	}
  	
	/**
  	 * Returns whether a user can manage contacts.
  	 *
  	 * @param Contact $user
  	 * @return boolean
  	 */
  	function can_manage_contacts(Contact $user, $include_groups = true){
  		if($user->isAdministrator()) return true;
  		$can_manage_contacts = false;
		$pg_ids = $user->getPermissionGroupIds();
		if (count($pg_ids) > 0) {
			$pgs = SystemPermissions::instance()->findAll(array('conditions' => 'permission_group_id IN ('.implode(',',$pg_ids).')'));
			foreach ($pgs as $pg) {
				if ($pg->getColumnValue('can_manage_contacts')) {
					$can_manage_contacts = true;
					break;
				}
			}
		}
		return $can_manage_contacts;
  	}
  	
  	
	/**
  	 * Returns whether a user can manage time.
  	 *
  	 * @param Contact $user
  	 * @return boolean
  	 */
  	function can_manage_time(Contact $user){
		return SystemPermissions::userHasSystemPermission($user, 'can_manage_time');
  	}
  	
  	/**
  	 * Returns whether a user can add mail accounts.
  	 *
  	 * @param Contact $user
  	 * @return boolean
  	 */
  	function can_add_mail_accounts(Contact $user){
		return SystemPermissions::userHasSystemPermission($user, 'can_add_mail_accounts');
  	}
  	
  	function can_manage_templates(Contact $user) {
		return SystemPermissions::userHasSystemPermission($user, 'can_manage_templates');
  	}

	function can_reopen_task(Contact $user) {
		return SystemPermissions::userHasSystemPermission($user, 'can_reopen_task');
  	}

	function can_instantiate_templates(Contact $user) {
		return SystemPermissions::userHasSystemPermission($user, 'can_instantiate_templates');
	}

  	function can_manage_dimensions(Contact $user) {
		return SystemPermissions::userHasSystemPermission($user, 'can_manage_dimensions');
  	}
  	function can_manage_dimension_members(Contact $user) {
		return SystemPermissions::userHasSystemPermission($user, 'can_manage_dimension_members');
  	}
  	function can_manage_tasks(Contact $user) {
		return SystemPermissions::userHasSystemPermission($user, 'can_manage_tasks');
  	}
  	function can_task_assignee(Contact $user) {
		return SystemPermissions::userHasSystemPermission($user, 'can_task_assignee');
  	}
  	function can_manage_billing(Contact $user) {
		return SystemPermissions::userHasSystemPermission($user, 'can_manage_billing');
  	}
  	function can_view_billing(Contact $user) {
		return SystemPermissions::userHasSystemPermission($user, 'can_view_billing');
  	}
  	
  	
  	function can_add_timeslots($user, $members) {
		return can_add($user, $members, Timeslots::instance()->getObjectTypeId());
  	}
  	
  	/**
  	 * Returns whether a user can manage configuration.
  	 *
  	 * @param Contact $user
  	 * @return boolean
  	 */
  	function can_manage_configuration(Contact $user){
		return SystemPermissions::userHasSystemPermission($user, 'can_manage_configuration');
  	}

  	
  	function can_manage_tabs(Contact $user){
		return $user->isAdminGroup();
  	}
  	
  	function can_manage_plugins(Contact $user){
		return $user->isAdminGroup();
  	}
  	
  	/**
  	 * Returns whether a user can link objects.
  	 *
  	 * @param Contact $user
  	 * @return boolean
  	 */
  	function can_link_objects(Contact $user){
  		return SystemPermissions::userHasSystemPermission($user, 'can_link_objects');
  	}  	
	
	/**
	 * Return true if $user can add an object of type $object_type_id in $member. False otherwise.
	 *
	 * @param Contact $user
	 * @param Member $member
	 * @param array $context_members
	 * @param $object_type_id
	 * @return boolean
	 */
	function can_add_to_member(Contact $user, $member, $context_members, $object_type_id, $check_dimension = true){
		if(TemplateTasks::instance()->getObjectTypeId() == $object_type_id){
			$object_type_id = ProjectTasks::instance()->getObjectTypeId();
		}
		if(TemplateMilestones::instance()->getObjectTypeId() == $object_type_id){
			$object_type_id = ProjectMilestones::instance()->getObjectTypeId();
		}
		if (!$member instanceof Member && is_array($member) && isset($member['id'])) {
			$member = Members::instance()->findById($member['id']);
		}
		
		if ( $user->isGuest() || !$member || !$member->canContainObject($object_type_id)) {
			return false;	
		}
		try {
			
			$contact_pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV($user->getId(),false);

			if ($check_dimension) $dimension = $member->getDimension();
			
			//dimension does not define permissions - user can freely add in all members
			if ($check_dimension && !$dimension->getDefinesPermissions()) return true;
			
			//dimension defines permissions and user has maximum level of permissions so can freely in all members
			if ($check_dimension && $dimension->hasAllowAllForContact($contact_pg_ids)) return true;
			
			//check
			if (ContactMemberPermissions::contactCanReadObjectTypeinMember($contact_pg_ids, $member->getId(), $object_type_id, true, false, $user)) {
				$max_role_ot_perm = MaxRoleObjectTypePermissions::instance()->findOne(array('conditions' => "object_type_id='$object_type_id' AND role_id = '". $user->getUserType() ."'"));
				// if user max permission cannot write this object type then return false
				if ($max_role_ot_perm && $max_role_ot_perm->getCanWrite()) {
					return true;
				} else {
					return false;
				}
			}
			//check for context permissions that allow user to add in this member
			if ($context_members){
				$member_ids = array();
				foreach ($context_members as $member_obj) $member_ids[] = $member_obj instanceof Member ? $member_obj->getId() : $member_obj;
				$member_ids = array_filter($member_ids, 'is_numeric');
				$allowed_members = ContactMemberPermissions::getActiveContextPermissions($user,$object_type_id, $context_members, $member_ids, true);
				if (in_array($member, $allowed_members)) return true;
			}	
			
		}
		catch(Exception $e) {
			tpl_assign('error', $e);
			return false;
		}
		return false;
	}
	
	
	/**
	 * Return true if $user can add an object of type $object_type_id in $member. False otherwise.
	 *
	 * @param Contact $user
	 * @param array $context
	 * @param $object_type_id
	 * @return boolean
	 */
	function can_add(Contact $user, $context, $object_type_id, &$notAllowedMember = ''){
		if ($user->isGuest()) return false;
		$membersInContext = 0;
		$can_add = false;
		$required_dimensions_ids = DimensionObjectTypeContents::getRequiredDimensions($object_type_id);
		$dimensions_in_context = array();
		
		$no_required_dimensions = count($required_dimensions_ids) == 0; 
		
		foreach ($required_dimensions_ids as $id){
			$dimensions_in_context[$id]= false;
		}
		
		$enabled_dimensions = config_option('enabled_dimensions');
		
		Hook::fire('can_add_modify_context_array', array(), $context);
		
		$contact_pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV($user->getId(),false);
		if (is_array($context)) {
			foreach($context as $selection){
				$sel_dimension = $selection instanceof Dimension ? $selection : ($selection instanceof Member ? $selection->getDimension() : null);
				
				//$can_add = false;
				if ($selection instanceof Member){
					
					$dimension = $selection->getDimension();

					if((!$dimension->getDefinesPermissions() || !in_array($dimension->getId(), $enabled_dimensions)) && !in_array($dimension->getId(), $required_dimensions_ids)){
						continue;
					}
					
					$membersInContext++;
					if (can_add_to_member($user, $selection, $context, $object_type_id)){
						//if ($no_required_dimensions) return true;
						$dimension_id = $selection->getDimensionId();
						$can_add = true;
						$dimensions_in_context[$dimension_id]=true;
					}else{
						$notAllowedMember = $selection->getName();
						return false;
					}
				}
				// Revoke explicty permission
				if ($can_add && !$no_required_dimensions){
					foreach ($dimensions_in_context as $key=>$value){
						$dim = Dimensions::getDimensionById($key);
						if(!$value && $dim->getDefinesPermissions() && $dim->deniesAllForContact($contact_pg_ids)){
							$can_add = false;
						}
					}
				}
			}
		}
		
		// All dimensions in 'all'.
		// If The object has no required dimensions, and no dimensions are selected: check for contact_member_permissions with member_id=0
		if ($no_required_dimensions && $membersInContext == 0) {
			$mailot = ObjectTypes::findByName('mail');
			if ($mailot instanceof ObjectType && $mailot->getId() == $object_type_id) {
				$can_add = true;
			} else {
				$can_add = false;
				if (config_option('let_users_create_objects_in_root') && $contact_pg_ids != '' && ($user->isAdminGroup() || $user->isExecutive() || $user->isManager())) {
					$cmp = ContactMemberPermissions::instance()->findOne(array('conditions' => 'member_id=0 AND object_type_id='.$object_type_id.' AND permission_group_id IN ('.$contact_pg_ids.')'));
					$can_add = $cmp instanceof ContactMemberPermission && $cmp->getCanWrite();
				}
			}
		}
		
		// All dimensions in 'all'.
		// if there are required dimensions and no members selected then show correct error message.
		if (!$no_required_dimensions && $membersInContext == 0 && !$can_add) {
			$dim_names = array();
			$required_dimensions = Dimensions::instance()->findAll(array('conditions' => 'id IN ('.implode(',',$required_dimensions_ids).')'));
			foreach ($required_dimensions as $dim) {
				$dim_names[] = $dim->getName();
			}
			$notAllowedMember = "-- req dim --".implode(",",$dim_names);
		}
		
		return $can_add;
	}


	function get_can_add_error_message($notAllowedMember, $objectTypeLang, $context = null) {
		if (str_starts_with($notAllowedMember, '-- req dim --')) {
			return lang('must choose at least one member of',
				str_replace_first('-- req dim --', '', $notAllowedMember));
		}
		if (trim($notAllowedMember) != '') {
			return lang('no context permissions to add', $objectTypeLang, $notAllowedMember);
		}
		if ($context === null) $context = active_context();
		$member_count = 0;
		$mem_names = array();
		if (is_array($context)) {
			foreach ($context as $c) {
				if ($c instanceof Member) {
					$member_count++;
					$mem_names[] = $c->getName();
				}
			}
		}
		if ($member_count > 0) {
			return lang('you dont have permissions to add this object in members',
				$objectTypeLang, implode(', ', $mem_names));
		}
		return lang('must choose at least one member');
	}
	
	
	/**
	 * Return true is $user can read the $object. False otherwise.
	 *
	 * @param Contact $user
	 * @param Member $member
	 * @param array $context_members
	 * @param $object_type_id
	 * @return boolean
	 */
	function can_read(Contact $user, $members, $object_type_id){
		return can_access($user, $members, $object_type_id, ACCESS_LEVEL_READ);
	}
	
	/**
	 * Return true is $user can read the $object. False otherwise.
	 * Query executed in sharing table
	 *
	 * @param Contact $user
	 * @param $object_id
	 * @return boolean
	 */
	function can_read_sharing_table(Contact $user, $object_id, $allow_super_admin = true) {
		if($allow_super_admin && $user->isAdministrator()){
			return true;
		}
		$perm = SharingTables::instance()->findOne(array('conditions' => array("object_id=? AND group_id IN (SELECT permission_group_id FROM ".TABLE_PREFIX."contact_permission_groups WHERE contact_id = '".$user->getId()."')", $object_id)));
		return !is_null($perm);
	}
	
	/**
	 * Return true if $user can write the object of $object_type_id. False otherwise.
	 *
	 * @param Contact $user
	 * @param Member $member
	 * @param array $context_members
	 * @param $object_type_id
	 * @return boolean
	 */
	function can_write(Contact $user, $members, $object_type_id){
		if ($user->isGuest()) return false;
		return can_access($user, $members, $object_type_id, ACCESS_LEVEL_WRITE);
	}
	
	
		
	/**
	 * Return true is $user can delete an $object. False otherwise.
	 *
	 * @param Contact $user
	 * @param array $members
	 * @param $object_type_id
	 * @return boolean
	 */
	function can_delete(Contact $user, $members, $object_type_id){
		if ($user->isGuest()) return false;
		return can_access($user, $members, $object_type_id, ACCESS_LEVEL_DELETE);
	}
	
	
	/**
	 * Return true is $user can access an $object. False otherwise.
	 *
	 * @param Contact $user
	 * @param array $members
	 * @param $object_type_id
	 * @return boolean
	 */
	function can_access(Contact $user, $members, $object_type_id, $access_level, $allow_super_admin = true){
		if($allow_super_admin && $user->isAdministrator()){
			return true;
		}
		$write = $access_level == ACCESS_LEVEL_WRITE;
		$delete = $access_level == ACCESS_LEVEL_DELETE;
		
		if (($user->isGuest() && $access_level!= ACCESS_LEVEL_READ)) return false;
		Hook::fire('can_access_modify_members_array', array('user'=>$user, 'object_type_id'=>$object_type_id, 'access_level' => $access_level), $members);
		try {
			$contact_pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV($user->getId(),false);
			$allow_all_cache = array();
			$dimension_query_methods = array();

			// if no manageable member then check if user has permissions wihout classifying 
			$manageable_members = array();
			foreach ($members as $mem) {
				if ($mem instanceof Member && $mem->getDimension()->getIsManageable() && $mem->getDimension()->getDefinesPermissions()) $manageable_members[] = $mem->getId();
			}
			if (count($manageable_members) == 0) {
				$return = false;
				if (config_option('let_users_create_objects_in_root') && $contact_pg_ids != "" && ($user->isAdminGroup() || $user->isExecutive() || $user->isManager())) {
					$cond = $delete ? 'AND can_delete = 1' : ($write ? 'AND can_write = 1' : '');
					$cmp = ContactMemberPermissions::instance()->findOne(array('conditions' => "member_id=0 AND object_type_id=$object_type_id AND permission_group_id IN ($contact_pg_ids) $cond"));
					$return = $cmp instanceof ContactMemberPermission;
				}
				return $return;
			}
			
			$max_role_ot_perm = MaxRoleObjectTypePermissions::instance()->findOne(array('conditions' => "object_type_id='$object_type_id' AND role_id = '". $user->getUserType() ."'"));
			
			$enabled_dimensions = config_option('enabled_dimensions');
			$dimension_permissions = array();
			foreach($members as $k => $m){
				if (!$m instanceof Member) {
					unset($members[$k]);
					continue;
				}
				
				$dimension = $m->getDimension();
				if(!$dimension->getDefinesPermissions() || !in_array($dimension->getId(), $enabled_dimensions)){
					continue;
				}
				$dimension_id = $dimension->getId();
				if (!isset($dimension_permissions[$dimension_id])) {
					$dimension_permissions[$dimension_id]=false;
				}
										
				if (!$dimension_permissions[$dimension_id]){
					if ($m->canContainObject($object_type_id)){
						
						if (!isset($dimension_query_methods[$dimension->getId()])) {
							$dimension_query_methods[$dimension->getId()] = $dimension->getPermissionQueryMethod();
						}
						
						//dimension defines permissions and user has maximum level of permissions
						if (isset($allow_all_cache[$dimension_id])) {
							$allow_all = $allow_all_cache[$dimension_id];
						} else {
							$allow_all = $dimension->hasAllowAllForContact($contact_pg_ids);
							$allow_all_cache[$dimension_id] = $allow_all;
						}
						if ($allow_all) {
							$dimension_permissions[$dimension_id]=true;
						}
						
						//check individual members
						if (!$dimension_permissions[$dimension_id] && ContactMemberPermissions::contactCanReadObjectTypeinMember($contact_pg_ids, $m->getId(), $object_type_id, $write, $delete, $user)){
							if ($max_role_ot_perm) {
								if ($access_level == ACCESS_LEVEL_DELETE && $max_role_ot_perm->getCanDelete() || $access_level == ACCESS_LEVEL_WRITE && $max_role_ot_perm->getCanWrite() || $access_level == ACCESS_LEVEL_READ) { 
									$dimension_permissions[$dimension_id]=true;
								}
							}
						}
					} else {
						unset($dimension_permissions[$dimension_id]);
					}
				}
			}

			$allowed = true;
			// check that user has permissions in all mandatory query method dimensions
			$mandatory_count = 0;
			foreach ($dimension_query_methods as $dim_id => $qmethod) {
				if (!in_array($dim_id, $enabled_dimensions)) continue;
				if ($qmethod == DIMENSION_PERMISSION_QUERY_METHOD_MANDATORY) {
					$mandatory_count++;
					if (!array_var($dimension_permissions, $dim_id)) {
						// if one of the members belong to a mandatory dimension and user does not have permissions on it then return false
						return false;
					}
				}
			}
			
			// If no members in mandatory dimensions then check for not mandatory ones 
			if ($allowed && $mandatory_count == 0) {
				foreach ($dimension_query_methods as $dim_id => $qmethod) {
					if ($qmethod == DIMENSION_PERMISSION_QUERY_METHOD_NOT_MANDATORY) {
						if (array_var($dimension_permissions, $dim_id)) {
							// if has permissions over any member of a non mandatory dimension then return true
							return true;
						} else {
							$allowed = false;
						}
					}
				}
			}

			if ($allowed && count($dimension_permissions)) {
				return true;	
			}
			
			// Si hasta aca tienen perm en todas las dim, return true. Si hay alguna que no tiene perm sigo
			
			//Check Context Permissions
			$member_ids = array();
			foreach ($members as $member_obj) $member_ids[] = $member_obj->getId();
			$allowed_members = ContactMemberPermissions::getActiveContextPermissions($user, $object_type_id, $members, $member_ids, $write, $delete);
			$count=0;
			foreach($members as $m){
				$count++;
				if (!in_array($m->getId(), $allowed_members)) return false;
				else if ($count==count($members)) return true;
			}
			
		}
		catch(Exception $e) {
			tpl_assign('error', $e);
			return false;
		}
		return false;
	}
	
	
	
	function get_all_children_sorted($member_ids, $order='name') {
		$all_children = array();
	
		$children = DB::executeAll("SELECT * FROM ".TABLE_PREFIX."members WHERE parent_member_id IN (".implode(',',$member_ids).") ORDER BY $order ASC");

		$parents_ids = array();
		if (is_array($children) && count($children) > 0) {
			foreach ($children as $child) {
				$all_children[] = $child;
				$parents_ids[] = $child['id'];
			}

			$all_children = array_merge($all_children, get_all_children_sorted($parents_ids));
		}
	
		return $all_children;
	}
	
	function get_all_parents_sorted($member_info, $order='name') {
		$all_parents = array();
		if($member_info['parent_member_id'] == 0){
			return $all_parents;
		}
		
		$parents = DB::executeAll("SELECT * FROM ".TABLE_PREFIX."members WHERE id=".$member_info['parent_member_id']." ORDER BY $order ASC");
		if (is_array($parents) && count($parents) > 0) {
			foreach ($parents as $parent) {
				$all_parents[] = $parent;
				$all_parents = array_merge($all_parents, get_all_parents_sorted($parent));
			}
		}
	
		return $all_parents;
	}


	function permission_form_parameters_is_guest_context($pg_id) {
		$pg_id = (int) $pg_id;
		if ($pg_id <= 0) return false;

		$permission_group = PermissionGroups::instance()->findById($pg_id);
		if ($permission_group instanceof PermissionGroup && $permission_group->getType() == 'roles') {
			$guest_roles = PermissionGroups::getGuestPermissionGroups();
			foreach ($guest_roles as $guest_role) {
				if ($guest_role->getId() == $pg_id) return true;
			}
			return false;
		}

		$contact = Contacts::instance()->findOne(array('conditions' => 'permission_group_id = '.$pg_id));
		return ($contact instanceof Contact && $contact->isGuest());
	}

	function permission_form_parameters($pg_id) {
		set_time_limit(0);
		ini_set('memory_limit', '512M');
		$member_permissions = array();		
		$dimensions = array();
		$dims = Dimensions::instance()->findAll(array('order' => 'default_order'));
		$members = array();
		$member_types = array();
		$allowed_object_types = array();
		$allowed_object_types_by_member_type[] = array();
		$root_permissions = array();
		$enabled_dimensions = config_option("enabled_dimensions");
		$is_guest_context = permission_form_parameters_is_guest_context($pg_id);
		
		foreach($dims as $dim) {
			if ($dim->getDefinesPermissions() && in_array($dim->getId(), $enabled_dimensions) && $dim->getIsManageable()) {
				$dimensions[] = $dim;
				$root_members = DB::executeAll("SELECT * FROM ".TABLE_PREFIX."members WHERE dimension_id=".$dim->getId()." ORDER BY parent_member_id, name ASC");
				if (is_array($root_members)) {
					$members[$dim->getId()] = array();
					foreach ($root_members as $mem) {
						$members[$dim->getId()][] = $mem;
					}
				}
				
				$allowed_object_types[$dim->getId()] = array();
				
				$dim_obj_types = $dim->getAllowedObjectTypeContents();
				foreach ($dim_obj_types as $dim_obj_type) {
					// To draw a row for each object type of the dimension
					if (!in_array($dim_obj_type->getContentObjectTypeId(), $allowed_object_types[$dim->getId()])) {
						$allowed_object_types[$dim->getId()][] = $dim_obj_type->getContentObjectTypeId();
					}
					
					// To enable or disable object types depending on the selected member
					if (!is_array(array_var($allowed_object_types_by_member_type, $dim_obj_type->getDimensionObjectTypeId()))) {
						$allowed_object_types_by_member_type[$dim_obj_type->getDimensionObjectTypeId()] = array();
					}
					$allowed_object_types_by_member_type[$dim_obj_type->getDimensionObjectTypeId()][] = $dim_obj_type->getContentObjectTypeId();
					
				}
				
				if ($dim->deniesAllForContact($pg_id)) {
					$cmp_count = ContactMemberPermissions::instance()->count("`permission_group_id` = $pg_id and member_id in (select m.id from ".TABLE_PREFIX."members m where m.dimension_id=".$dim->getId().")");
					if ($cmp_count > 0) {
						$dim->setContactDimensionPermission($pg_id, 'check');
					}
				}
				
				if (!$is_guest_context && $dim->hasAllowAllForContact($pg_id)) {
					if (isset($members[$dim->getId()])) {
						foreach ($members[$dim->getId()] as $mem) {
							$member_permissions[$mem['id']] = array();
							foreach ($dim_obj_types as $dim_obj_type) {
								if ($dim_obj_type->getDimensionObjectTypeId() == $mem['object_type_id']) {
									$member_permissions[$mem['id']][] = array(
										'o' => $dim_obj_type->getContentObjectTypeId(),
										'w' => 1,
										'd' => 1,
										'r' => 1
									);
								}
							}
						}
					}
				} else if (!$dim->deniesAllForContact($pg_id)) {
					if (isset($members[$dim->getId()])) {
						$tmp_ids = array();
						foreach ($members[$dim->getId()] as $mem) {
							$tmp_ids[] = $mem['id'];
						}
						$mem_pgs = array();
						if (is_array($tmp_ids) && count($tmp_ids)) {
							$pgs = DB::executeAll("SELECT * FROM ".TABLE_PREFIX."contact_member_permissions WHERE permission_group_id=$pg_id AND member_id IN (".implode(',',$tmp_ids).")
									AND object_type_id IN (SELECT ot.id FROM ".TABLE_PREFIX."object_types ot WHERE ot.type IN ('content_object','located'))");
							if(is_array($pgs)){
								foreach ($pgs as $p) {
									if (!isset($mem_pgs[$p['member_id']])) $mem_pgs[$p['member_id']] = array();
									$mem_pgs[$p['member_id']][] = $p;
								}
							}
						}
						
						foreach ($members[$dim->getId()] as $mem) {
							$member_permissions[$mem['id']] = array();
							if (isset($mem_pgs[$mem['id']]) && is_array($mem_pgs[$mem['id']])) {
								foreach ($mem_pgs[$mem['id']] as $pg) {
									$member_permissions[$mem['id']][] = array(
											'o' => $pg['object_type_id'],
											'w' => $pg['can_write'],
											'd' => $pg['can_delete'],
											'r' => 1
									);
								}
							}
						}
					}
				}
				
				if (isset($members[$dim->getId()])) {
					foreach($members[$dim->getId()] as $member) {
						$member_types[$member['id']] = $member['object_type_id'];
					}
				}
			}
		}
		
		if (config_option('let_users_create_objects_in_root')) {
			$root_cmps = ContactMemberPermissions::instance()->findAll(array('conditions' => 'permission_group_id = '.$pg_id.' AND member_id = 0'));
			foreach ($root_cmps as $root_cmp) {
				$root_permissions[$root_cmp->getObjectTypeId()] = array('w' => $root_cmp->getCanWrite(), 'd' => $root_cmp->getCanDelete(), 'r' => 1);
			}
		}

		// Role defaults must not exceed fixed role ceilings; user edits load DB values as-is.
		$is_user_permission_group = false;
		$role_id = 0;
		if ((int) $pg_id > 0) {
			$permission_group = PermissionGroups::instance()->findById((int)$pg_id);
			if ($permission_group instanceof PermissionGroup && $permission_group->getType() == 'roles') {
				$role_id = (int) $pg_id;
			} else {
				$contact = Contacts::instance()->findOne(array('conditions' => 'permission_group_id = '.(int)$pg_id));
				if ($contact instanceof Contact) {
					$role_id = (int) $contact->getUserType();
					$is_user_permission_group = true;
				}
			}
		}

		if ($role_id > 0 && !$is_user_permission_group) {
			$max_permissions = array();
			$res = DB::executeAll("
				SELECT object_type_id, can_delete, can_write
				FROM ".TABLE_PREFIX."max_role_object_type_permissions
				WHERE role_id = ".$role_id."
			");
			if ($res) {
				foreach ($res as $row) {
					$max_permissions[(int)$row['object_type_id']] = array(
						'd' => (int)$row['can_delete'],
						'w' => (int)$row['can_write'],
						'r' => 1
					);
				}
			}
			$root_permissions = clamp_root_permissions_array($root_permissions, $max_permissions);
		}
		
		$all_object_types = ObjectTypes::instance()->findAll(array("conditions" => "`type` IN ('content_object', 'located') AND name <> 'template_task' AND name <> 'template_milestone' AND `name` <> 'template' AND `name` <> 'file revision'"));
		return array(
			'member_types' => $member_types,
			'allowed_object_types_by_member_type' => $allowed_object_types_by_member_type,
			'allowed_object_types' => $allowed_object_types,
			'all_object_types' => $all_object_types,
			'member_permissions' => $member_permissions,
			'dimensions' => $dimensions,
			'root_permissions' => $root_permissions,
		);
	}
	
	
	/**
	 * For new users, apply role module defaults when POST did not include any module permissions.
	 *
	 * @param int $pg_id User permission group id
	 * @param mixed $mod_permissions_data
	 * @param bool $is_new_user
	 * @return array|null
	 */
	function apply_default_module_permissions_for_new_user($pg_id, $mod_permissions_data, $is_new_user = false) {
		if (!$is_new_user || (int)$pg_id <= 0) {
			return $mod_permissions_data;
		}
		if (is_array($mod_permissions_data) && count($mod_permissions_data) > 0) {
			return $mod_permissions_data;
		}

		$role_id = 0;
		$tmp_contact = Contacts::instance()->findOne(array('conditions' => 'permission_group_id = '.(int)$pg_id));
		if ($tmp_contact instanceof Contact) {
			$role_id = (int)$tmp_contact->getUserType();
		}
		if ($role_id <= 0) {
			return $mod_permissions_data;
		}

		$mod_permissions_data = array();
		foreach (TabPanelPermissions::getRoleModules($role_id) as $tab_id) {
			$mod_permissions_data[$tab_id] = 1;
		}
		return $mod_permissions_data;
	}

	function save_permissions($pg_id, $is_guest = false, $permissions_data = null, $save_cmps = true, $update_sharing_table = true, $fire_hook = true, $update_contact_member_cache = true, $users_ids_to_check = array(), $only_member_permissions=false, $is_new_user=false) {
	    $return_info = array();
		$rp_genid = null;
		$rp_permissions_data = array();

		if (is_null($permissions_data)) {
			
			// system permissions
			$sys_permissions_data = array_var($_POST, 'sys_perm');
			// module permissions
			$mod_permissions_data = array_var($_POST, 'mod_perm');
			// root permissions
			if ($rp_genid = array_var($_POST, 'root_perm_genid')) {
				$rp_permissions_data = array();
				foreach ($_POST as $name => $value) {
					if (str_starts_with($name, $rp_genid . 'rg_root_')) {
						$rp_permissions_data[$name] = $value;
					}
				}
			}
			// member permissions
			$permissionsString = array_var($_POST, 'permissions');
			
		} else {
			
			// system permissions
			$sys_permissions_data = array_var($permissions_data, 'sys_perm');
			// module permissions
			$mod_permissions_data = array_var($permissions_data, 'mod_perm');
			// root permissions
			$rp_genid = array_var($permissions_data, 'root_perm_genid');
			$rp_permissions_data = array_var($permissions_data, 'root_perm');
			// member permissions
			$permissionsString = array_var($permissions_data, 'permissions');
			
		}

		$mod_permissions_data = apply_default_module_permissions_for_new_user($pg_id, $mod_permissions_data, $is_new_user);
		
		try {
			DB::beginWork();
			
			$changed_members = array();
					
			// save module permissions
			if (!$only_member_permissions) {
			  try {
				TabPanelPermissions::clearByPermissionGroup($pg_id, true);
				if (!is_null($mod_permissions_data) && is_array($mod_permissions_data)) {
					foreach($mod_permissions_data as $tab_id => $val) {
						DB::execute("INSERT INTO ".TABLE_PREFIX."tab_panel_permissions (permission_group_id,tab_panel_id) VALUES ('$pg_id','$tab_id') ON DUPLICATE KEY UPDATE permission_group_id=permission_group_id");
					}
				}
			  } catch (Exception $e) {
				Logger::log("Error saving module permissions for permission group $pg_id: ".$e->getMessage()."\n".$e->getTraceAsString());
				throw $e;
			  }
			}
			
			$root_permissions_sharing_table_delete = array();
			$root_permissions_sharing_table_add = array();
			if (logged_user() instanceof Contact && can_manage_security(logged_user())) {
				try {
					
				  if (!$only_member_permissions) {
					// save system permissions
					$system_permissions = SystemPermissions::instance()->findById($pg_id);
					if (!$system_permissions instanceof SystemPermission) {
						$system_permissions = new SystemPermission();
						$system_permissions->setPermissionGroupId($pg_id);
					}
					$system_permissions->setAllPermissions(false);
					$other_permissions = array();
					Hook::fire('add_user_permissions', $pg_id, $other_permissions);
					foreach ($other_permissions as $k => $v) {
						$system_permissions->setColumnValue($k, false);
					}
					
					// check max permissions for role, in case of modifying user's permissions
					$role_id = "-1";
					// Stays empty when the permission group is not a user's, since a user group has no
					// role. It is read further down to decide whether root permissions may be edited, so
					// it has to be defined on every path.
					$user_type_name = '';
					$tmp_contact = Contacts::instance()->findOne(array('conditions' => 'permission_group_id = '.$pg_id));
					if ($tmp_contact instanceof Contact) {
						$role_id = $tmp_contact->getUserType();
					}
					$max_role_system_permissions = MaxSystemPermissions::instance()->findOne(array('conditions' => 'permission_group_id = '.$role_id));
					// $sys_permissions_data is null whenever the request carries no system permissions,
					// for instance a member only save or a form without the system permissions tab.
					if ($max_role_system_permissions instanceof MaxSystemPermission && is_array($sys_permissions_data)) {
						// iterate over the keys: the loop unsets entries of the array it walks
						foreach (array_keys($sys_permissions_data) as $col) {
							$max_val = $max_role_system_permissions->getColumnValue($col);
							if (!$max_val) {
								unset($sys_permissions_data[$col]);
							}
						}
					}
					// don't allow to write emails for collaborators and guests
					if ($tmp_contact instanceof Contact) {
						$user_type_name = $tmp_contact->getUserTypeName() ?? '';
						if (!in_array($user_type_name, array('Super Administrator','Administrator','Manager','Executive'))) {
							$mail_ot = ObjectTypes::findByName('mail');
							if ($mail_ot instanceof ObjectType) {
								DB::executeAll("UPDATE ".TABLE_PREFIX."contact_member_permissions SET can_write=0, can_delete=0 WHERE object_type_id=".$mail_ot->getId()." AND permission_group_id=$pg_id");
							}
						}
					}
					
					$sys_permissions_data['can_task_assignee'] = !$is_guest;
					$system_permissions->setFromAttributes($sys_permissions_data);
					$system_permissions->setUseOnDuplicateKeyWhenInsert(true);
					$system_permissions->save();
					
					//object type root permissions
					$can_edit_root_permissions = config_option('let_users_create_objects_in_root') && in_array($user_type_name, array('Super Administrator','Administrator','Manager','Executive'));
					$can_apply_default_root_permissions = config_option('let_users_create_objects_in_root') && $is_new_user && (int)$role_id > 0;
					$has_root_permissions_data = false;
					if ($rp_genid && is_array($rp_permissions_data)) {
						foreach ($rp_permissions_data as $name => $value) {
							if (str_starts_with($name, $rp_genid . 'rg_root_')) {
								$has_root_permissions_data = true;
								break;
							}
						}
					}
					if ($rp_genid && $can_edit_root_permissions && $has_root_permissions_data) {
						//ContactMemberPermissions::instance()->delete("permission_group_id = $pg_id AND member_id = 0");
						foreach ($rp_permissions_data as $name => $value) {
							if (str_starts_with($name, $rp_genid . 'rg_root_')) {
								$rp_ot = substr($name, strrpos($name, '_')+1);

                                if (!is_numeric($rp_ot) || $rp_ot <= 0) continue;
                                $value = clamp_root_permission_level_for_role((int)$value, (int)$role_id, (int)$rp_ot);

                                $root_perm_cmp = ContactMemberPermissions::instance()->findById(array('permission_group_id' => $pg_id, 'member_id' => 0, 'object_type_id' => $rp_ot));
                                if (!$root_perm_cmp instanceof ContactMemberPermission) {
                                    if($value >= 1){
                                        $root_perm_cmp = new ContactMemberPermission();
                                        $root_perm_cmp->setPermissionGroupId($pg_id);
                                        $root_perm_cmp->setMemberId('0');
                                        $root_perm_cmp->setObjectTypeId($rp_ot);

                                        $root_permissions_sharing_table_add[] = $rp_ot;
                                    }else{
                                        continue;
                                    }
                                }elseif($value == 0){
                                    //DELETE
                                    ContactMemberPermissions::instance()->delete("permission_group_id = $pg_id AND member_id = 0 AND object_type_id = $rp_ot");
                                    $root_permissions_sharing_table_delete[] = $rp_ot;
                                }

                                if ($root_perm_cmp instanceof ContactMemberPermission && $value >= 1) {
                                    $root_perm_cmp->setCanWrite($value >= 2);
                                    $root_perm_cmp->setCanDelete($value >= 3);
                                    $root_perm_cmp->save();
                                    
                                    if ($is_new_user && !in_array($rp_ot, $root_permissions_sharing_table_add)) {
                                    	$root_permissions_sharing_table_add[] = $rp_ot;
                                    }
                                }
							}
						}
					} elseif ($can_apply_default_root_permissions && !$has_root_permissions_data) {
						$default_permissions = RoleObjectTypePermissions::instance()->findAll(array('conditions' => 'role_id = '.(int)$role_id));
						foreach ($default_permissions as $p) {
							$value = $p->getCanDelete() ? 3 : ($p->getCanWrite() ? 2 : 1);
							$value = clamp_root_permission_level_for_role($value, (int)$role_id, (int)$p->getObjectTypeId());
							if ($value < 1) continue;

							$root_perm_cmp = ContactMemberPermissions::instance()->findById(array(
								'permission_group_id' => $pg_id,
								'member_id' => 0,
								'object_type_id' => $p->getObjectTypeId()
							));
							if (!$root_perm_cmp instanceof ContactMemberPermission) {
								$root_perm_cmp = new ContactMemberPermission();
								$root_perm_cmp->setPermissionGroupId($pg_id);
								$root_perm_cmp->setMemberId('0');
								$root_perm_cmp->setObjectTypeId($p->getObjectTypeId());
								$root_permissions_sharing_table_add[] = $p->getObjectTypeId();
							}
							$root_perm_cmp->setCanWrite($value >= 2);
							$root_perm_cmp->setCanDelete($value >= 3);
							$root_perm_cmp->save();
						}
					}
				  }
				} catch (Exception $e) {
					
					Logger::log("Error saving system and root permissions for permission group $pg_id: ".$e->getMessage()."\n".$e->getTraceAsString());
					throw $e;
				}
			}
            $return_info['root_permissions_sharing_table_add'] = $root_permissions_sharing_table_add;
            $return_info['root_permissions_sharing_table_delete'] = $root_permissions_sharing_table_delete;
			
			// set all permissions to read_only if user is guest
			if ($is_guest) {
				try {
					$all_saved_permissions = ContactMemberPermissions::instance()->findAll(array("conditions" => "`permission_group_id` = $pg_id"));
					foreach ($all_saved_permissions as $sp) {/* @var $sp ContactMemberPermission */
						if ($sp->getCanDelete() || $sp->getCanWrite()) {
							$sp->setCanDelete(false);
							$sp->setCanWrite(false);
							$sp->save();
						}
					}
					$cdps = ContactDimensionPermissions::instance()->findAll(array("conditions" => "`permission_type` = 'allow all'"));
					foreach ($cdps as $cdp) {
						$cdp->setPermissionType('check');
						$cdp->save();
					}
				} catch (Exception $e) {
					Logger::log("Error setting guest user permissions to read_only for permission group $pg_id: ".$e->getMessage()."\n".$e->getTraceAsString());
					throw $e;
				}
			}
				
			// check the status of the changed dimensions to set 'allow_all', 'deny_all' or 'check'
			try {
					
				$dimensions = Dimensions::instance()->findAll(array("conditions" => array("`id` IN (SELECT DISTINCT `dimension_id` FROM ".Members::instance()->getTableName(true)." WHERE `id` IN (?))", $changed_members)));
				foreach ($dimensions as $dimension) {
					$dimension->setContactDimensionPermission($pg_id, 'check');
				}
					
			} catch (Exception $e) {
				Logger::log("Error setting dimension permissions for permission group $pg_id: ".$e->getMessage()."\n".$e->getTraceAsString());
				throw $e;
			}
			
			
			//member permissions
			if ($permissionsString && $permissionsString != ''){
				$permissions = json_decode($permissionsString);
			}
			
			if (isset($permissions) && !is_null($permissions) && is_array($permissions)) {
				try {
					$tmp_contact = Contacts::instance()->findOne(array('conditions' => 'permission_group_id = '.$pg_id));
					if ($tmp_contact instanceof Contact) {
						$user_type_name = $tmp_contact->getUserTypeName() ?? '';
						$role_id = $tmp_contact->getUserType();
						$max_role_ot_perms = MaxRoleObjectTypePermissions::instance()->findAll(array('conditions' => "role_id = '$role_id'"));
					}
					$mail_ot = ObjectTypes::findByName('mail');
					
					$sql_insert_values = "";
					$sql_insert_values_array = array();
					$member_object_types_to_delete = array();
					$allowed_members_ids= array();
					foreach ($permissions as &$perm) {
						if (!isset($all_perm_deleted[$perm->m])) {
							$all_perm_deleted[$perm->m] = true;
						}
						$allowed_members_ids[$perm->m]=array();
						$allowed_members_ids[$perm->m]['pg']=$pg_id;
						if ($perm->r) {
							if(isset($allowed_members_ids[$perm->m]['w'])){
								if($allowed_members_ids[$perm->m]['w']!=1){
									$allowed_members_ids[$perm->m]['w'] = $is_guest ? false : $perm->w;
								}
							}else{
								$allowed_members_ids[$perm->m]['w'] = $is_guest ? false : $perm->w;
							}
							if(isset($allowed_members_ids[$perm->m]['d'])){
								if($allowed_members_ids[$perm->m]['d']!=1){
									$allowed_members_ids[$perm->m]['d'] = $is_guest ? false : $perm->d;
								}
							}else{
								$allowed_members_ids[$perm->m]['d'] = $is_guest ? false : $perm->d;
							}

							// check max permissions for user type
							if ($tmp_contact instanceof Contact) {
								$max_perm = null;
								foreach($max_role_ot_perms as $max_role_ot_perm) {
									if ($max_role_ot_perm->getObjectTypeId() == $perm->o) {
										$max_perm = $max_role_ot_perm;
									}
								}
								if ($max_perm) {
									if (!$max_perm->getCanDelete()) {
										$perm->d = 0;
									}
									if (!$max_perm->getCanWrite()) {
										$perm->w = 0;
									}
								} else {
									$perm->d = 0;
									$perm->w = 0;
									$perm->r = 0;
								}
							}
							
							if ($save_cmps) {
								// don't allow to write emails for collaborators and guests
								if ($tmp_contact instanceof Contact && !in_array($user_type_name, array('Super Administrator','Administrator','Manager','Executive'))) {
									if ($mail_ot instanceof ObjectType && $perm->o == $mail_ot->getId()) {
										$perm->d = 0;
										$perm->w = 0;
									}
								}
								//$sql_insert_values .= ($sql_insert_values == "" ? "" : ",") . "('".$pg_id."','".$perm->m."','".$perm->o."','".$perm->d."','".$perm->w."')";
								$sql_insert_values_array[] = "('".$pg_id."','".$perm->m."','".$perm->o."','".$perm->d."','".$perm->w."')";
								
								if (!isset($member_object_types_to_delete[$perm->m])) $member_object_types_to_delete[$perm->m] = array();
								$member_object_types_to_delete[$perm->m][] = $perm->o;
							}
							
							$all_perm_deleted[$perm->m] = false;
							
						} else {
							if (is_numeric($perm->m) && is_numeric($perm->o)) {
								DB::execute("DELETE FROM ".TABLE_PREFIX."contact_member_permissions WHERE member_id='".$perm->m."' AND object_type_id='".$perm->o."' AND permission_group_id=$pg_id");
							}
						}
						
						$changed_members[] = $perm->m;
					}
					
					if ($save_cmps) {
						if (isset($all_perm_deleted) && count($all_perm_deleted) > 0) {
							$member_ids_to_delete = array();
							foreach ($all_perm_deleted as $mid => $del) {
								if ($del) {
									// also check in contact_member_permissions
									$cmps = ContactMemberPermissions::instance()->findAll(array('conditions' => 'permission_group_id='.$pg_id." AND member_id=$mid"));
									if (!is_array($cmps) || count($cmps) == 0) {
										$member_ids_to_delete[] = $mid;
									}
								}
							}
							if (count($member_ids_to_delete) > 0) {
								DB::execute("DELETE FROM ".TABLE_PREFIX."contact_member_permissions WHERE member_id IN (".implode(',',$member_ids_to_delete).") AND permission_group_id=$pg_id");
							}
						}
						$sql_delete_values_array = array();
						foreach ($member_object_types_to_delete as $mid => $obj_type_ids) {
							if (count($obj_type_ids) > 0) {
								$sql_delete_values_array[] = "(member_id='$mid' AND object_type_id IN (".implode(',',$obj_type_ids)."))";
								//DB::execute("DELETE FROM ".TABLE_PREFIX."contact_member_permissions WHERE member_id=$mid AND object_type_id IN (".implode(',',$obj_type_ids).") AND permission_group_id=$pg_id");
							}
						}
						$splitted_delete_values = array_chunk($sql_delete_values_array, 100);
						foreach ($splitted_delete_values as $values_to_delete) {
							$sql_delete_values = implode(' OR ', $values_to_delete);
							DB::execute("DELETE FROM ".TABLE_PREFIX."contact_member_permissions WHERE ($sql_delete_values) AND permission_group_id=$pg_id");
						}
						
						if (count($sql_insert_values_array) > 0) {
							$splitted_insert_values = array_chunk($sql_insert_values_array, 1000);
							foreach ($splitted_insert_values as $values_to_insert) {
								$sql_insert_values = implode(',', $values_to_insert);
								if ($sql_insert_values != "") {
									DB::execute("INSERT INTO ".TABLE_PREFIX."contact_member_permissions (permission_group_id, member_id, object_type_id, can_delete, can_write) VALUES $sql_insert_values ON DUPLICATE KEY UPDATE member_id=member_id");
								}
							}
						}
						
					}
					
				} catch (Exception $e) {
					Logger::log("Error saving member permissions for permission group $pg_id: ".$e->getMessage()."\n".$e->getTraceAsString());
					throw $e;
				}
			}
			
			DB::commit();
		} catch (Exception $e) {
			Logger::log("Error saving permissions for permission group $pg_id: ".$e->getMessage()."\n".$e->getTraceAsString());
			DB::rollback();
		}

		// Let plugins mirror object types that cannot be edited in the permissions UI onto their
		// editable parent object type (e.g. expense_item follows expense). This must run after the
		// contact_member_permissions rows are committed but BEFORE the sharing table and contact
		// member cache are rebuilt below, so that member visibility stays consistent with the parent.
		$member_ot_perms_hook_ret = null;
		Hook::fire('after_save_member_object_type_permissions', array('pg_id' => $pg_id, 'changed_members' => isset($changed_members) ? $changed_members : array()), $member_ot_perms_hook_ret);

		try {

			if (isset($permissions) && !is_null($permissions) && is_array($permissions)) {
				if ($update_sharing_table) {
					try {
						$sharingTablecontroller = new SharingTableController();

                        if (!$only_member_permissions) {
                            $rp_info = array('root_permissions_sharing_table_delete' => $root_permissions_sharing_table_delete, 'root_permissions_sharing_table_add' => $root_permissions_sharing_table_add);
                            $sharingTablecontroller->afterPermissionChanged($pg_id, $permissions, $rp_info);
                        }else{
                            $sharingTablecontroller->afterPermissionChanged($pg_id, $permissions);
                        }

					} catch (Exception $e) {
						Logger::log("Error saving permissions to sharing table for permission group $pg_id: ".$e->getMessage()."\n".$e->getTraceAsString());
						throw $e;
					}
				}
				
				if ($update_contact_member_cache) {
					try {
						$contactMemberCacheController = new ContactMemberCacheController();
						$group = PermissionGroups::instance()->findById($pg_id);
						
						$real_group = null;
						if($group->getType() == 'user_groups'){
							$real_group = $group;
						}
						$users = $group->getUsers();
						$users_ids_checked = array();
						
						foreach ($users as $us) {
							$users_ids_checked[] = $us->getId();
							$contactMemberCacheController->afterUserPermissionChanged($us, $permissions, $real_group);
						}
						
						//check all users related to the group
						foreach ($users_ids_to_check as $us_id) {
							if(!in_array($us_id, $users_ids_checked)){
								$users_ids_checked[] = $us_id;
								$us = Contacts::instance()->findById($us_id);
								if($us instanceof Contact){
									$contactMemberCacheController->afterUserPermissionChanged($us, $permissions, $real_group);
								}
							}
						}
					} catch (Exception $e) {
						Logger::log("Error saving permissions to contact member cache for permission group $pg_id: ".$e->getMessage()."\n".$e->getTraceAsString());
						throw $e;
					}
				}
	
			}
		} catch (Exception $e) {
			Logger::log("Error saving module permissions for permission group $pg_id: ".$e->getMessage()."\n".$e->getTraceAsString());
		}
		
		
		if ($fire_hook) {
			Hook::fire('after_save_contact_permissions', $pg_id, $pg_id);
		}
		
		// remove contact object from members where permissions were deleted
		$user = Contacts::instance()->findOne(array('conditions' => 'permission_group_id='.$pg_id));
		if ($user instanceof Contact) {
			$to_remove = array();
			if (isset($all_perm_deleted) && is_array($all_perm_deleted)) {
				foreach ($all_perm_deleted as $m_id => $must_remove) {
					if ($must_remove) $to_remove[] = $m_id;
				}
				ObjectMembers::removeObjectFromMembers($user, logged_user(), null, $to_remove);
			}
		}
		return $return_info;
	}
	
	
	
	function permission_member_form_parameters($member = null, $dimension_id = null) {
		if ( $member ) {
			$dim = $member->getDimension();
		}elseif (array_var( $_REQUEST,'dim_id')) {
			$dim = Dimensions::getDimensionById(array_var( $_REQUEST,'dim_id'));
		}elseif (!is_null($dimension_id)) {
			$dim = Dimensions::getDimensionById($dimension_id);
		}
		
		if (!$dim instanceof Dimension) {
			Logger::log("Invalid dimension: " . ($member instanceof Member ? " for member ".$member->getId() : "request: ".print_r($_REQUEST, 1)));
			throw new Exception("Invalid dimension");
		}
		
		
		$allowed_object_types = array();
		$dim_obj_types = $dim->getAllowedObjectTypeContents();
		foreach ($dim_obj_types as $dim_obj_type) {
			// To draw a row for each object type of the dimension
			if ( !array_key_exists($dim_obj_type->getContentObjectTypeId(), $allowed_object_types) && (!$member || $dim_obj_type->getDimensionObjectTypeId() == $member->getObjectTypeId()) ) {
				$allowed_object_types[$dim_obj_type->getContentObjectTypeId()] = ObjectTypes::instance()->findById($dim_obj_type->getContentObjectTypeId());
				$allowed_object_types_json[] = $dim_obj_type->getContentObjectTypeId();
			}
		}
		
		$permission_groups = array();
		$users = Contacts::instance()->getAllUsers();
		foreach ($users as $u) $permission_groups[] = $u->getPermissionGroupId();
		
		$user_group_ids = array();
		$non_personal_groups = PermissionGroups::getNonRolePermissionGroups();
		foreach ($non_personal_groups as $group) {
			$user_group_ids[] = $group->getId();
			$permission_groups[] = $group->getId();
		}
		
		$disabled_ots = array();
		$disableds = DB::executeAll("SELECT object_type_id FROM ".TABLE_PREFIX."tab_panels WHERE object_type_id>0 AND enabled=0");
		if (is_array($disableds)) {
			$disabled_ots = array_flat($disableds);
		}
		
		$ws_ot = ObjectTypes::findByName('workspace')->getId();
		$disabled_ots[] = $ws_ot;
		$disabled_ot_cond = "";
		if (count($disabled_ots) > 0) {
			$disabled_ot_cond = "AND object_type_id NOT IN (".implode(",",$disabled_ots).")";
		}
		
		foreach ($permission_groups as $pg_id) {
			if ($dim->hasAllowAllForContact($pg_id)) {
				$member_permissions[$pg_id] = array();
				foreach ($dim_obj_types as $dim_obj_type) {
					if ($member && $dim_obj_type->getDimensionObjectTypeId() == $member->getObjectTypeId()) {
						$member_permissions[$pg_id][] = array(
							'o' => $dim_obj_type->getContentObjectTypeId(),
							'w' => 1,
							'd' => 1,
							'r' => 1
						);
					}elseif(!$member){
						// WHEN CREATING a new member dont allow any user 
						$member_permissions[$pg_id][] = array(
							'o' => $dim_obj_type->getContentObjectTypeId(),
							'w' => 0,
							'd' => 0,
							'r' => 0
						);
					}
				}
				
			} else if (!$dim->deniesAllForContact($pg_id) || in_array($pg_id, $user_group_ids)) {
				// query the permissions for user groups and contacts that are not denied in all members of the dimension
				$member_permissions[$pg_id] = array();
				if ($member) {
					$mpgs = ContactMemberPermissions::instance()->findAll(array("conditions" => array("`permission_group_id` = ? AND `member_id` = ? 
							AND object_type_id IN (".implode(',', $allowed_object_types_json).") $disabled_ot_cond", $pg_id, $member->getId())));
					if (is_array($mpgs)) {
						foreach ($mpgs as $mpg) {
							$member_permissions[$mpg->getPermissionGroupId()][] = array(
								'o' => $mpg->getObjectTypeId(),
								'w' => $mpg->getCanWrite() ? 1 : 0,
								'd' => $mpg->getCanDelete() ? 1 : 0,
								'r' => 1
							);
						}
					}
				}
				
			}
		}
		
		return array(
			'member' => $member,
			'allowed_object_types' => $allowed_object_types,
			'allowed_object_types_json' => $allowed_object_types_json,
			'permission_groups' => $permission_groups,
			'member_permissions' => isset($member_permissions) ? $member_permissions : array(),
		);
	}
	
	function get_default_member_permission($parent,$permission_parameters) {
		//inherit permission from parent
		if ($parent != 0 && config_option('inherit_permissions_from_parent_member')) {
			$parent_member = Members::getMemberById($parent);
			if ($parent_member instanceof Member) {
				$parent_permissions = permission_member_form_parameters($parent_member);
		
				$permission_parameters['permission_groups'] = $parent_permissions['permission_groups'];
				$permission_parameters['member_permissions'] = $parent_permissions['member_permissions'];
			}
		}
			
		// Add default permissions for executives, managers and administrators
		if (config_option('add_default_permissions_for_users')) {
			if ($parent == 0) {
				$user_types = implode(',', config_option('give_member_permissions_to_new_users'));
				if (trim($user_types) != "") {
					$users = Contacts::instance()->findAll(array('conditions' => "user_type IN (".$user_types.")"));
					
					foreach ($users as $user) {
						if (!isset($permission_parameters['member_permissions'][$user->getPermissionGroupId()]) || count($permission_parameters['member_permissions'][$user->getPermissionGroupId()]) == 0) {
							$user_pg = array();
							foreach ($permission_parameters['allowed_object_types'] as $ot){
								$role_perm = RoleObjectTypePermissions::instance()->findOne(array('conditions' => array("role_id=? AND object_type_id=?", $user->getUserType(), $ot->getId())));
								$user_pg[] = array(
										'o' => $ot->getId(),
										'w' => $role_perm instanceof RoleObjectTypePermission ? ($role_perm->getCanWrite()?1:0) : 0,
										'd' => $role_perm instanceof RoleObjectTypePermission ? ($role_perm->getCanDelete()?1:0) : 0,
										'r' => $role_perm instanceof RoleObjectTypePermission ? 1 : 0,
								);
							}
							
							$permission_parameters['member_permissions'][$user->getPermissionGroupId()] = $user_pg;
						}
					}
				}
			}
		}
		
		return $permission_parameters;
		
	}
	
	function save_member_permissions($member, $permissionsString = null, $save_cmps = true, $update_sharing_table = true, $fire_hook = true, $update_contact_member_cache = true) {
		@set_time_limit(0);
		ini_set('memory_limit', '1024M');
        $permissions = false;
		if (!$member instanceof Member) return;
		if (is_null($permissionsString)) {
			$permissionsString = array_var($_POST, 'permissions');
		}
		if ($permissionsString && $permissionsString != ''){
			$permissions = json_decode($permissionsString);
		}
		
		$sharingTablecontroller = new SharingTableController();
		$contactMemberCacheController = new ContactMemberCacheController();
		$changed_pgs = array();
		
		// if the user does not have privileges to set the permissions
		// then build the default permissions based on the config options and the parent
		if (!$permissions) {
			$add_log = ApplicationLogs::instance()->findOne(array("order"=>"id DESC", "conditions"=>array("member_id=?", $member->getId())));
			$is_new_member = !($add_log instanceof ApplicationLog) || $add_log->getAction()=='add';
			
			if ($is_new_member) {
				$permission_parameters = permission_member_form_parameters($member);
				$permission_parameters = get_default_member_permission($member->getParentMemberId(), $permission_parameters);
				
				if (count($permission_parameters['member_permissions']) > 0) {
					$permissions = array();
					foreach ($permission_parameters['member_permissions'] as $pg_id => $perms_data) {
						foreach ($perms_data as $perm_data) {
							$perm = new stdClass();
							$perm->pg = $pg_id;
							$perm->r = $perm_data['r'];
							$perm->w = $perm_data['w'];
							$perm->d = $perm_data['d'];
							$perm->o = $perm_data['o'];
							
							$permissions[] = $perm;
						}
					}
				}
			}
		}
		// -- end default permissions creation
		
		$sql_insert_values = "";
		if (isset($permissions) && is_array($permissions)) {
			
			$allowed_pg_ids= array();
			foreach ($permissions as $k => &$perm) {
				if ($perm->r) {
					$allowed_pg_ids[$perm->pg]=array();
					if(isset($allowed_pg_ids[$perm->pg]['w'])){
						if(!$allowed_pg_ids[$perm->pg]['w']){
							$allowed_pg_ids[$perm->pg]['w']=$perm->w;
						}
					}else{
						$allowed_pg_ids[$perm->pg]['w']=$perm->w;
					}
					if(isset($allowed_pg_ids[$perm->pg]['d'])){
						if(!$allowed_pg_ids[$perm->pg]['d']){
							$allowed_pg_ids[$perm->pg]['d']=$perm->d;
						}
					}else{
						$allowed_pg_ids[$perm->pg]['d']=$perm->d;
					}
					
					// check max permissions for user type
					$tmp_contact = Contacts::instance()->findOne(array('conditions' => 'permission_group_id = '.$perm->pg));
					if ($tmp_contact instanceof Contact) {
						$max_role_ot_perms = MaxRoleObjectTypePermissions::instance()->findAll(array('conditions' => "role_id = '". $tmp_contact->getUserType() ."'"));
						$max_perm = null;
						foreach($max_role_ot_perms as $max_role_ot_perm) {
							if ($max_role_ot_perm->getObjectTypeId() == $perm->o) {
								$max_perm = $max_role_ot_perm;
							}
						}
						$perm->m = $member->getId();
						if ($max_perm) {
							if (!$max_perm->getCanDelete()) {
								$perm->d = 0;
							}
							if (!$max_perm->getCanWrite()) {
								$perm->w = 0;
							}
						} else {
							$perm->d = 0;
							$perm->w = 0;
							$perm->r = 0;
							unset($permissions[$k]);
							continue;
						}
					}

					if ($save_cmps) {
						$sql_insert_values .= ($sql_insert_values == "" ? "" : ",") . "('".$perm->pg."','".$member->getId()."','".$perm->o."','".$perm->d."','".$perm->w."')";
					}
				}
				
				$perm->m = $member->getId();
				$changed_pgs[$perm->pg] = $perm->pg;
			}
			if ($save_cmps) {
				if (count($changed_pgs) > 0) {
					DB::execute("DELETE FROM ".TABLE_PREFIX."contact_member_permissions WHERE permission_group_id IN (".implode(',',$changed_pgs).") AND member_id=".$member->getId());
				}
				if ($sql_insert_values != "") {
					DB::execute("INSERT INTO ".TABLE_PREFIX."contact_member_permissions (permission_group_id, member_id, object_type_id, can_delete, can_write) VALUES $sql_insert_values ON DUPLICATE KEY UPDATE member_id=member_id");
				}

				// Let plugins mirror object types that cannot be edited in the permissions UI onto
				// their editable parent (e.g. expense_item follows expense) before the sharing table
				// and contact member cache are rebuilt below, so member visibility stays consistent.
				foreach ($changed_pgs as $chg_pg_id) {
					$member_ot_perms_hook_ret = null;
					Hook::fire('after_save_member_object_type_permissions', array('pg_id' => $chg_pg_id, 'changed_members' => array($member->getId())), $member_ot_perms_hook_ret);
				}
			}

			foreach ($permissions as $p) {
				if (!$p->m) $p->m = $member->getId();
			}
			if ($update_sharing_table) {
				foreach ($changed_pgs as $pg_id) {
					$sharingTablecontroller->afterPermissionChanged($pg_id, $permissions);
				}
			}
			if ($update_contact_member_cache) {
				$contactMemberCacheController->afterMemberPermissionChanged(array('changed_pgs' => $changed_pgs, 'member' => $member));
			}
			
			
			foreach ($allowed_pg_ids as $key=>$mids){
				$root_cmp = ContactMemberPermissions::instance()->findById(array('permission_group_id' => $key, 'member_id' => $member->getId(), 'object_type_id' => $member->getObjectTypeId()));
				if (!$root_cmp instanceof ContactMemberPermission) {
					$root_cmp = new ContactMemberPermission();
					$root_cmp->setPermissionGroupId($key);
					$root_cmp->setMemberId($member->getId());
					$root_cmp->setObjectTypeId($member->getObjectTypeId());
				}
				$root_cmp->setCanWrite($mids['w']==true ? 1 : 0);
				$root_cmp->setCanDelete($mids['d']==true ? 1 : 0);
				$root_cmp->save();
				
			}
		}
		
		// check the status of the dimension to set 'allow_all', 'deny_all' or 'check'
		$dimension = $member->getDimension();
		foreach ($changed_pgs as $pg_id) {
			$dimension->setContactDimensionPermission($pg_id, 'check');
		}
		
		if ($fire_hook) {
			Hook::fire('after_save_member_permissions', array('member' => $member, 'user_id' => logged_user()->getId()), $member);
		}
		
		return array('changed_pgs' => $changed_pgs, 'member' => $member);
	}

	/**
	 * Returns the users with permissions for the object type $object_type for the context $context
	 * 
	 * @param $object_type_id Object Type
	 * @param $context array Context
	 * @param $access_level (ACCESS_LEVEL_READ, ACCESS_LEVEL_WRITE, ACCESS_LEVEL_DELETE)
	 * @param $extra_conditions string Extra conditions to add to the users query
	 * @param $to_assign true if this function is called to fill the "assigned to" combobox when editing a task
	 */
	function allowed_users_in_context($object_type_id, $context = null, $access_level = ACCESS_LEVEL_READ, $extra_conditions = "", $for_tasks_filter = false, $include_member_childs = false) {
		$members = array();
		if (isset($context) && is_array($context)) {
			foreach ($context as $selection) {
				if ($selection instanceof Member && $selection->getDimension()->getDefinesPermissions() && $selection->getDimension()->getIsManageable()) {
					$members[] = $selection;
				}
			}
		}
		
		$users_with_permissions = array();
		
		if (count($members) == 0) {

            if(TemplateTasks::instance()->getObjectTypeId() == $object_type_id || TemplateMilestones::instance()->getObjectTypeId() == $object_type_id){
                $users_with_permissions = Contacts::getAllUsers($extra_conditions);
            }else{
                // users with permissions in root
                if (config_option('let_users_create_objects_in_root')) {

                    /*$users_with_permissions = Contacts::instance()->findAll(array("conditions" => "
						disabled=0 AND permission_group_id IN (
							SELECT cmp.permission_group_id FROM ".TABLE_PREFIX."contact_member_permissions cmp
							INNER JOIN ".TABLE_PREFIX."permission_groups pg ON pg.id=cmp.permission_group_id
							WHERE cmp.member_id=0 AND cmp.object_type_id='$object_type_id' AND pg.type='permission_groups'
						)
					"));*/
                	$users_with_permissions = Contacts::getAllUsers($extra_conditions);
                }
            }

			
		} else {
            if(TemplateTasks::instance()->getObjectTypeId() == $object_type_id){
                $object_type_id = ProjectTasks::instance()->getObjectTypeId();;
            }
            if(TemplateMilestones::instance()->getObjectTypeId() == $object_type_id){
                $object_type_id = ProjectMilestones::instance()->getObjectTypeId();;
            }
			// foreach user check if can access in $members for $object_type_id and $access_level
			$users = Contacts::getAllUsers($extra_conditions);
			foreach ($users as $user){
				$can_access = $user->isAdministrator() || can_access($user, $members, $object_type_id, $access_level);
				if ($can_access) {
					$users_with_permissions[] = $user;
				}
			}
		}
		
		return $users_with_permissions;
		
	}

	function get_users_with_system_permission($system_permission_name, $include_inactive = false) {
		$permission_group_ids = SystemPermissions::getAllPermissionGroupIdsWithSystemPermission($system_permission_name);
		$contacts_ids = ContactPermissionGroups::getAllContactsIdsByPermissionGroupIds($permission_group_ids);
	
		if (empty($contacts_ids)) {
			return [];
		}
	
		$conditions = "object_id IN (" . implode(",", $contacts_ids) . ")";
		if (!$include_inactive) {
			$conditions = "disabled = 0 AND " . $conditions;
		}
	
		return Contacts::instance()->findAll([
			"conditions" => $conditions
		]);
	}
	

	function can_save_permissions_in_background() {
		if (defined('DONT_SAVE_PERMISSIONS_IN_BACKGROUND') && DONT_SAVE_PERMISSIONS_IN_BACKGROUND) {
			return false;
		}
		return defined('SAVE_PERMISSIONS_IN_BACKGROUND') && SAVE_PERMISSIONS_IN_BACKGROUND && is_exec_available();
	}

	/**
	 * Tells whether a database error is a transient lock conflict, i.e. another connection got there
	 * first rather than the statement itself being wrong, so retrying it makes sense.
	 *
	 * 1020 = record has changed since last read, 1205 = lock wait timeout, 1213 = deadlock.
	 *
	 * The message check is not a fallback: since PHP 8.1 mysqli reports errors as exceptions by default,
	 * so these surface as mysqli_sql_exception and never reach the adapter that would wrap them in a
	 * DBQueryError. The error number branch only applies when that reporting mode is turned off.
	 *
	 * @param Exception $e
	 * @return boolean
	 */
	function is_retryable_db_lock_error($e) {
		if ($e instanceof DBQueryError && method_exists($e, 'getErrorNumber')) {
			if (in_array((int) $e->getErrorNumber(), array(1020, 1205, 1213))) return true;
		}
		$message = $e->getMessage();
		return strpos($message, 'Lock wait timeout exceeded') !== false
			|| strpos($message, 'Deadlock found') !== false
			|| strpos($message, 'Record has changed since last read') !== false;
	}

	/**
	 * Writes the submitted member permissions straight into contact_member_permissions, so a permission
	 * change shows up before the background process has finished the full save.
	 *
	 * This is an optimistic write only: save_member_permissions(), run by the background process, redoes
	 * the same work authoritatively and also rebuilds the sharing table and the contact member cache.
	 * Callers must treat a failure here as non fatal.
	 *
	 * The statements are chunked, like the ones in save_permissions(), because a single INSERT or DELETE
	 * spanning every permission group holds locks over that whole range for as long as it runs, which is
	 * what makes concurrent permission saves time out against each other. This function opens no
	 * transaction of its own, so whether the chunks commit individually depends on the caller: most
	 * commit their own work before calling, but MemberController::add_default_permissions() does have
	 * one open. A caller that swallows a failure here while holding a transaction should be aware that
	 * a deadlock (1213) rolls the whole transaction back, unlike a lock wait timeout (1205), which with
	 * innodb_rollback_on_timeout off only rolls back the offending statement.
	 *
	 * The ids coming from the payload are validated and cast before being interpolated, as
	 * save_member_permissions() already does with the same values.
	 *
	 * @param Member $member
	 * @param string $permissions JSON array of permission objects ({pg,o,d,w,r})
	 * @return void
	 */
	function apply_member_permissions_optimistically($member, $permissions) {
		$permissions_decoded = json_decode($permissions);
		if (!is_array($permissions_decoded)) return;

		$member_id = (int) $member->getId();
		$to_insert = array();
		$to_delete = array();
		foreach ($permissions_decoded as $perm) {
			if (!isset($perm->pg) || !isset($perm->o) || !is_numeric($perm->pg) || !is_numeric($perm->o)) continue;

			$pg_id = (int) $perm->pg;
			$object_type_id = (int) $perm->o;
			if (!empty($perm->r)) {
				$can_delete = isset($perm->d) ? (int) $perm->d : 0;
				$can_write = isset($perm->w) ? (int) $perm->w : 0;
				$to_insert[] = "('$pg_id','$member_id','$object_type_id','$can_delete','$can_write')";
			} else {
				$to_delete[] = "(permission_group_id='$pg_id' AND member_id='$member_id' AND object_type_id='$object_type_id')";
			}
		}

		foreach (array_chunk($to_insert, 1000) as $values_to_insert) {
			DB::execute("INSERT INTO ".TABLE_PREFIX."contact_member_permissions (permission_group_id,member_id,object_type_id,can_delete,can_write)
				VALUES ".implode(',', $values_to_insert)." ON DUPLICATE KEY UPDATE member_id=member_id");
		}
		foreach (array_chunk($to_delete, 100) as $values_to_delete) {
			DB::execute("DELETE FROM ".TABLE_PREFIX."contact_member_permissions WHERE ".implode(' OR ', $values_to_delete));
		}
	}

	function save_member_permissions_background($user, $member, $permissions, $old_parent_id=-1) {
		
		if (substr(php_uname(), 0, 7) == "Windows" || !can_save_permissions_in_background()){
			//pclose(popen("start /B ". $command, "r"));
			save_member_permissions($member, $permissions);
			
			if ($old_parent_id != -1 && $old_parent_id != $member->getParentMemberId()) {
				member_parent_changed_refresh_object_permisssions($member, $old_parent_id, $user, $member->getParentMemberId());
			}
		} else {

			// Optimistic write, so the new permissions are visible without waiting for the background
			// process. That process redoes this work authoritatively (sharing table and contact member
			// cache included), so a failure here must never abort the request: log it and let the
			// background save be the source of truth. Without this guard a lock conflict on these
			// statements aborted the whole action before the process below was ever launched, leaving
			// the member with no permissions at all.
			try {
				apply_member_permissions_optimistically($member, $permissions);
			} catch (Exception $e) {
				Logger::log("Could not pre-save the permissions of member ".$member->getId()
					.", leaving them to the background process: ".$e->getMessage());
			}

			// save permissions in background
			$perm_filename = ROOT ."/tmp/perm_".gen_id();
			file_put_contents($perm_filename, $permissions);
			
			// Queue instead of spawning. A request that saves many members produces one process
			// for all of them; the per-member permission string keeps its own perm_* file, which
			// the worker reads and deletes.
			Env::useHelper('background_jobs');
			queue_background_job('save_member_permissions', array(
				'member_id'            => $member->getId(),
				'permissions_filename' => $perm_filename,
				'old_parent_id'        => $old_parent_id,
			), $user->getId(), $user->getTwistedToken());
		}
	}
	
	
	
	function get_user_pgs_with_permissions_in_my_members($object_type_id, $user = null) {
		if ($object_type_id <= 0) {
			return array();
		}
		if (is_null($user) || !$user instanceof Contact || !$user->isUser()) {
			$user = logged_user();
		}
		
		$sql = "select distinct(cmp.permission_group_id) from ".TABLE_PREFIX."contact_member_permissions cmp
			inner join ".TABLE_PREFIX."permission_groups pg on pg.id=cmp.permission_group_id
			where pg.type in ('permission_groups','user_groups') and cmp.object_type_id='$object_type_id' and member_id in (
			  select distinct(cmp2.member_id) from ".TABLE_PREFIX."contact_member_permissions cmp2 where cmp2.permission_group_id in (
			    select cpg2.permission_group_id from ".TABLE_PREFIX."contact_permission_groups cpg2 where cpg2.contact_id=".$user->getId()."
			  )
			)";
		return array_flat(DB::executeAll($sql));
	}
	
	
	function generate_perm_objects_from_apply_to_permissions($perms, $dim_member_ids) {
		
		$tmp_permissions = json_decode(array_var($_POST, 'permissions'));
		
		$tmp_permissions_with_keys = array();
		foreach ($tmp_permissions as $tmp_perm) {
			if (!isset($tmp_permissions_with_keys[$tmp_perm->m])) $tmp_permissions_with_keys[$tmp_perm->m] = array();
			$tmp_permissions_with_keys[$tmp_perm->m][$tmp_perm->o] = $tmp_perm;
		}
		
		foreach ($dim_member_ids as $dim_member_id) {
			foreach ($perms as $perm) {
				if (!isset($tmp_permissions_with_keys[$dim_member_id])) $tmp_permissions_with_keys[$dim_member_id] = array();
				if (!isset($tmp_permissions_with_keys[$dim_member_id][$perm->o])) {
					$new_perm = new stdClass();
					$new_perm->m = $dim_member_id;
					$new_perm->o = $perm->o;
					$new_perm->d = $perm->d;
					$new_perm->w = $perm->w;
					$new_perm->r = $perm->r;
					$tmp_permissions_with_keys[$dim_member_id][$perm->o] = $new_perm;
				}
			}
		}
		
		return json_encode(array_filter(array_flat($tmp_permissions_with_keys)));
	}
	
	/**
	 * Returns the ids of object types that are not editable in the permissions UI ("shadow" object
	 * types that must be derived from a parent, e.g. expense_item follows expense). Plugins register
	 * their ids through the 'non_editable_member_object_type_ids' hook.
	 *
	 * @return array of int
	 */
	function get_non_editable_member_object_type_ids() {
		$ot_ids = array();
		Hook::fire('non_editable_member_object_type_ids', null, $ot_ids);
		if (!is_array($ot_ids)) return array();
		return array_values(array_unique(array_filter(array_map('intval', $ot_ids))));
	}

	/**
	 * Removes permission entries for non-editable "shadow" object types from a permissions payload.
	 *
	 * These object types are hidden from every permissions form, so they can only be derived from
	 * their parent (done by the 'after_save_member_object_type_permissions' hook). They must never be
	 * persisted straight from the submitted payload: a cascade ("apply to submembers / all") copies
	 * the hidden parent value down to every child, and the background save re-inserts the raw payload
	 * verbatim, which would otherwise re-create the shadow rows after the hook removed them.
	 *
	 * @param string $permissionsString JSON array of permission objects ({m,o,d,w,r})
	 * @return string filtered JSON payload
	 */
	function remove_non_editable_object_type_permissions_from_payload($permissionsString) {
		if (!$permissionsString || $permissionsString == '') return $permissionsString;

		$ot_ids = get_non_editable_member_object_type_ids();
		if (count($ot_ids) == 0) return $permissionsString;

		$perms = json_decode($permissionsString);
		if (!is_array($perms)) return $permissionsString;

		$filtered = array();
		foreach ($perms as $perm) {
			if (isset($perm->o) && in_array((int) $perm->o, $ot_ids)) continue;
			$filtered[] = $perm;
		}
		return json_encode($filtered);
	}

	/**
	 * Generates the permissions for each member when user checks in apply to all members or apply to all submembers
	 */
	function generate_perm_objects_from_apply_to_settings() {
		
		if ($apply_to_sub_json = array_var($_POST, 'apply_to_submembers_permissions')) {
			$apply_to_sub = json_decode($apply_to_sub_json);
			
			//foreach ($apply_to_sub as $dim_id => $dim_perms) {
				//foreach ($dim_perms as $parent_id => $perms) {
				foreach ($apply_to_sub as $parent_id => $perms) {
					//$dim_member_ids = Members::instance()->findAll(array("id"=>true, "conditions"=>array("dimension_id=?",$dim_id)));
					$parent_member = Members::getMemberById($parent_id);
					if ($parent_member instanceof Member) {
						$dim_member_ids = $parent_member->getAllChildrenIds(true);
						if (count($dim_member_ids) > 0) {
							$_POST['permissions'] = generate_perm_objects_from_apply_to_permissions($perms, $dim_member_ids);
						}
					}
				}
			//}
		}
		
		if ($apply_to_all_json = array_var($_POST, 'apply_to_all_permissions')) {
			$apply_to_all = json_decode($apply_to_all_json);
			
			foreach ($apply_to_all as $dim_id => $perms) {
				$dim_member_ids = Members::instance()->findAll(array("id"=>true, "conditions"=>array("dimension_id=?",$dim_id)));
				if (count($dim_member_ids) > 0) {
					$_POST['permissions'] = generate_perm_objects_from_apply_to_permissions($perms, $dim_member_ids);
				}
			}
		}
	}
	
	
	function save_user_permissions_background($user, $pg_id, $is_guest=false, $users_ids_to_check = array(), $only_member_permissions=false, $is_new_user=false) {
		
		// system permissions
		$sys_permissions_data = array_var($_POST, 'sys_perm');
		// module permissions
		$mod_permissions_data = array_var($_POST, 'mod_perm');
		// root permissions
		$rp_permissions_data = array();
		$set_root_permissions = false;
		$tmp_contact = Contacts::instance()->findOne(array('conditions' => "permission_group_id=$pg_id"));
		if ($tmp_contact instanceof Contact && $tmp_contact->getUserType() > 0) {
			if ($is_new_user || in_array($tmp_contact->getUserTypeName(), array('Super Administrator','Administrator','Manager','Executive'))) {
				$set_root_permissions = true;
			}
		}
		$rp_genid = array_var($_POST, 'root_perm_genid', '0');
		if ($rp_genid && $set_root_permissions) {
			foreach ($_POST as $name => $value) {
				if (str_starts_with($name, $rp_genid . 'rg_root_')) {
					$rp_permissions_data[$name] = $value;
				}
			}
		}
		
		// gets the apply_to_all and apply_to_submembers settings to generate the resulting permission objects for each member
		generate_perm_objects_from_apply_to_settings();

		// member permissions
		$permissionsString = array_var($_POST, 'permissions');

		// Drop non-editable "shadow" object types (e.g. expense_item) from the payload so they are
		// never persisted directly. A cascade copies the hidden parent value onto every child and the
		// background re-inserts the raw payload; the after_save_member_object_type_permissions hook is
		// the single source of truth that derives them from their parent object type.
		$permissionsString = remove_non_editable_object_type_permissions_from_payload($permissionsString);
		$_POST['permissions'] = $permissionsString;

		$mod_permissions_data = apply_default_module_permissions_for_new_user($pg_id, $mod_permissions_data, $is_new_user);
		
		
		if (substr(php_uname(), 0, 7) == "Windows" || !can_save_permissions_in_background()){
			//pclose(popen("start /B ". $command, "r"));
			save_permissions($pg_id, $is_guest, null, true, true, true, true, $users_ids_to_check, $only_member_permissions, $is_new_user);
			
		} else {

			// save permissions in background
			$perm_filename = ROOT ."/tmp/uperm_".gen_id();
			file_put_contents($perm_filename, $permissionsString);
			
			$sys_filename = ROOT ."/tmp/sys_".gen_id();
			file_put_contents($sys_filename, json_encode($sys_permissions_data));
			
			$mod_filename = ROOT ."/tmp/mod_".gen_id();
			file_put_contents($mod_filename, json_encode($mod_permissions_data));
			
			$rp_filename = ROOT ."/tmp/rp_".gen_id();
			file_put_contents($rp_filename, json_encode($rp_permissions_data));
			
			$usrcheck_filename = ROOT ."/tmp/usrcheck_".gen_id();
			file_put_contents($usrcheck_filename, json_encode($users_ids_to_check));
			
			$ret=null;
			Hook::fire('before_save_user_permissions_background', array('pg_id'=>$pg_id, 'request'=>$_REQUEST), $ret);
			
			$only_mem_perm_str = $only_member_permissions ? "1" : "0";
			$is_guest_str = $is_guest ? "1" : "0";
			$new_user_str = $is_new_user ? "1" : "0";
			
			Env::useHelper('background_jobs');
			queue_background_job('save_user_permissions', array(
				'pg_id'                       => $pg_id,
				'is_guest'                    => $is_guest_str,
				'permissions_filename'        => $perm_filename,
				'sys_permissions_filename'    => $sys_filename,
				'mod_permissions_filename'    => $mod_filename,
				'root_permissions_filename'   => $rp_filename,
				'users_ids_to_check_filename' => $usrcheck_filename,
				'root_permissions_genid'      => $rp_genid,
				'only_member_permissions'     => $only_mem_perm_str,
				'is_new_user'                 => $new_user_str,
			), $user->getId(), $user->getTwistedToken());
			
		}
	}
	
	
	
	function add_object_to_sharing_table($object, $user) {
		if (!$object instanceof ContentDataObject) return;
		
		if (substr(php_uname(), 0, 7) == "Windows" || !can_save_permissions_in_background() || !$user instanceof Contact){
			$object->addToSharingTable();
		} else {
			Env::useHelper('background_jobs');
			queue_background_job('add_object_to_sharing_table', array($object->getId()), $user->getId(), $user->getTwistedToken());
		}
	}
	
	function add_multilple_objects_to_sharing_table($ids_str, $user) {
		
		if (substr(php_uname(), 0, 7) == "Windows" || !can_save_permissions_in_background()){
			$ids = explode(',', $ids_str);
			foreach ($ids as $id) {
				$object = Objects::instance()->findObject($id);
				if ($object instanceof ContentDataObject) {
					$object->addToSharingTable();
				}
			}
		} else {
			Env::useHelper('background_jobs');
			queue_background_job('add_object_to_sharing_table', explode(',', $ids_str), $user->getId(), $user->getTwistedToken());
		}
	}
	
	
	function recalculate_contact_member_cache_for_user($user, $logged_user) {
		if (!$logged_user instanceof Contact) return;
		
		if (substr(php_uname(), 0, 7) == "Windows" || !can_save_permissions_in_background()){
			ContactMemberCaches::updateContactMemberCacheAllMembers($user);
		} else {
			Env::useHelper('background_jobs');
			queue_background_job('recalculate_contact_member_cache_for_user', array($user->getId()), $logged_user->getId(), $logged_user->getTwistedToken());
		}
	}
	
	/**
	 * Function called after editing a member and changing its parent, it will refresh the permissions for all the objects within the member.
	 * If it is possible this function should be executed in background
	 */
	function member_parent_changed_refresh_object_permisssions($member, $old_parent_id, $user, $new_parent_id) {
		if (substr(php_uname(), 0, 7) == "Windows" || !can_save_permissions_in_background()){
			
			do_member_parent_changed_refresh_object_permisssions($member->getId(), $old_parent_id, $new_parent_id);
			
		} else {
			Env::useHelper('background_jobs');
			queue_background_job('member_parent_changed_refresh_object_permisssions', array(
				'member_id'     => $member->getId(),
				'old_parent_id' => $old_parent_id,
				'new_parent_id' => $new_parent_id,
			), $user->getId(), $user->getTwistedToken());
		}
	}
	
	function do_member_parent_changed_refresh_object_permisssions($member_id, $old_parent_id, $new_parent_id) {
		$member = Members::instance()->findById($member_id);
		if (!$member instanceof Member) {
			return;
		}
		
		if ($old_parent_id > 0) {
			$parent_ids = array();
				$all_parents = Members::instance()->findById($old_parent_id)->getAllParentMembersInHierarchy(true);
			foreach ($all_parents as $p){
				$parent_ids[] = $p->getId();
				}

			//old parent hierarchy remove optimization
			foreach ($all_parents as $parent){
				$childs = get_all_children_sorted(array($parent->getId()));

				$childs_ids = array();
				foreach ($childs as $child){
					if($child['id'] != $member_id){
						$childs_ids[] = $child['id'];
			}
		}
			
				$childs_ids[] = $parent->getId();

				//start transaction
				if(count($childs_ids) > 0){

					//Get all objects in this member that must not be any more in this parent
					$old_obj_sql = "SELECT om.object_id
					FROM " . TABLE_PREFIX . "object_members om
					WHERE om.member_id=" . $member->getId() . "
					AND NOT EXISTS (
						SELECT omm.object_id
						FROM " . TABLE_PREFIX . "object_members omm
						WHERE om.object_id = omm.object_id
						AND omm.is_optimization = 0
						AND omm.member_id IN (" . implode(",", $childs_ids) . ")
					)
					";

					$object_ids = DB::executeAll($old_obj_sql);
					$object_ids = array_filter(array_flat($object_ids));

					//Delete objects from this parent
					if (count($object_ids) > 0) {
						DB::execute("DELETE FROM " . TABLE_PREFIX . "object_members WHERE member_id=" . $parent->getId() . " AND is_optimization = 1 AND object_id IN (" . implode(",", $object_ids) . ")");
					}
				}
			}
		}

		//Add optimization for new parent hierarchy
		if ($new_parent_id > 0) {
			$new_parent_ids = array();
			$all_new_parents = Members::instance()->findById($new_parent_id)->getAllParentMembersInHierarchy(true);;

			foreach ($all_new_parents as $np) {
				$new_parent_ids[] = $np->getId();


				$sql = "INSERT INTO " . TABLE_PREFIX . "object_members
					SELECT om.object_id , " . $np->getId() . ",1
					FROM " . TABLE_PREFIX . "object_members om
					WHERE om.member_id=" . $member->getId() . "

					ON DUPLICATE KEY UPDATE object_id=om.object_id;";

				DB::execute($sql);
			}

		}

	}

	/*
	 * This function returns all users with root permissions for at least one object type
	 */
	function get_users_with_permissions_in_root() {
		if (config_option('let_users_create_objects_in_root')){
			$users_with_permissions_in_root = Contacts::instance()->findAll(array("conditions" => "
						disabled=0 AND permission_group_id IN (
							SELECT cmp.permission_group_id FROM ".TABLE_PREFIX."contact_member_permissions cmp
							INNER JOIN ".TABLE_PREFIX."permission_groups pg ON pg.id=cmp.permission_group_id
							WHERE cmp.member_id=0 AND pg.type='permission_groups'
						)
					"));

			$users_ids = array();
			foreach ($users_with_permissions_in_root as $user) {
				$users_ids[] = $user->getId();
			}

			//super admins
			$admins = Contacts::instance()->findAll(array('conditions' => "user_type = 1"));
			foreach ($admins as $admin) {
				if(!in_array($admin->getId(),$users_ids) ){
					$users_with_permissions_in_root[] = $admin;
				}
			}

			return $users_with_permissions_in_root;
		}else{
			return array();
		}
	}
	
	
	
	function rebuild_sharing_table_for_pg_background($pg_id) {
		
		if (substr(php_uname(), 0, 7) == "Windows" || !can_save_permissions_in_background()){
			
			rebuild_sharing_table_for_pg($pg_id);
			
		} else {
			$user = logged_user();

			Env::useHelper('background_jobs');
			queue_background_job('rebuild_sharing_table_for_pg', array($pg_id), $user->getId(), $user->getTwistedToken());
		}
	}
	
	
	function rebuild_sharing_table_for_pg($pg_id) {
		$permissions_array = array();
		/*
		$cmp_rows = DB::executeAll("SELECT * FROM ".TABLE_PREFIX."contact_member_permissions WHERE permission_group_id=$pg_id");
		foreach ($cmp_rows as $row) {
			$p = new stdClass();
			$p->m = array_var($row, 'member_id');
			$p->o = array_var($row, 'object_type_id');
			$p->d = array_var($row, 'can_delete');
			$p->w = array_var($row, 'can_write');
			$p->r = 1;
			$permissions_array[] = $p;
		}
		*/
		$sharing_table_controller = new SharingTableController();
		$sharing_table_controller->afterPermissionChanged(array($pg_id), $permissions_array);
		
	}

	/**
	 * Clamp editable system permission defaults against the fixed role ceiling.
	 * (Used by "default permissions by role" and user permission load UI.)
	 *
	 * @param SystemPermission $system_permissions
	 * @param int $role_id
	 * @return SystemPermission
	 */
	function clamp_role_system_permission_object($system_permissions, $role_id) {
		if (!$system_permissions instanceof SystemPermission) {
			return $system_permissions;
		}

		$max_system_permissions = MaxSystemPermissions::instance()->findById($role_id);
		if (!($max_system_permissions instanceof MaxSystemPermission)) {
			return $system_permissions;
		}

		$columns = SystemPermissions::instance()->getColumns();
		foreach ($columns as $column) {
			if ($column === 'permission_group_id') continue;
			if (!$max_system_permissions->getColumnValue($column)) {
				$system_permissions->setColumnValue($column, false);
			}
		}

		return $system_permissions;
	}

	/**
	 * Clamp root object type permission levels (read/write/delete) against fixed max.
	 *
	 * Input/output structure matches what the permission views expect:
	 * ['d' => 0|1, 'w' => 0|1, 'r' => 0|1]
	 *
	 * @param array $root_perms
	 * @param array $max_perms
	 * @return array
	 */
	function clamp_root_permissions_array($root_perms, $max_perms) {
		if (!is_array($root_perms)) return array();
		if (!is_array($max_perms) || count($max_perms) === 0) return $root_perms;

		$clamped = $root_perms;

		foreach ($root_perms as $ot_id => $perm) {
			$max = array_var($max_perms, $ot_id, null);
			if (is_null($max)) {
				// Missing max row means this role has no root permission for this object type.
				unset($clamped[$ot_id]);
				continue;
			}

			$cur_d = (int) array_var($perm, 'd', 0);
			$cur_w = (int) array_var($perm, 'w', 0);
			$cur_r = (int) array_var($perm, 'r', 0);

			$level = 0;
			if ($cur_d === 1) $level = 3;
			elseif ($cur_w === 1) $level = 2;
			elseif ($cur_r === 1) $level = 1;

			$max_d = (int) (array_key_exists('d', $max) ? $max['d'] : array_var($max, 'can_delete', 0));
			$max_w = (int) (array_key_exists('w', $max) ? $max['w'] : array_var($max, 'can_write', 0));

			if ($level >= 3 && !$max_d) {
				$level = $max_w ? 2 : 1;
			}
			if ($level >= 2 && !$max_w) {
				$level = 1;
			}

			if ($level === 0) {
				unset($clamped[$ot_id]);
			} else {
				$clamped[$ot_id] = array(
					'd' => ($level >= 3) ? 1 : 0,
					'w' => ($level >= 2) ? 1 : 0,
					'r' => ($level >= 1) ? 1 : 0,
				);
			}
		}

		return $clamped;
	}

	function get_root_permission_max_level_for_role($role_id, $object_type_id) {
		static $cache = array();

		$role_id = (int)$role_id;
		$object_type_id = (int)$object_type_id;
		if ($role_id <= 0 || $object_type_id <= 0) return 0;

		if (!isset($cache[$role_id])) {
			$cache[$role_id] = array();
			$max_perms = MaxRoleObjectTypePermissions::instance()->findAll(array('conditions' => "role_id = '$role_id'"));
			if (is_array($max_perms)) {
				foreach ($max_perms as $max_perm) {
					$level = 1;
					if ($max_perm->getCanDelete()) {
						$level = 3;
					} else if ($max_perm->getCanWrite()) {
						$level = 2;
					}
					$cache[$role_id][(int)$max_perm->getObjectTypeId()] = $level;
				}
			}
		}

		return array_var($cache[$role_id], $object_type_id, 0);
	}

	function clamp_root_permission_level_for_role($level, $role_id, $object_type_id) {
		$level = (int)$level;
		$max_level = get_root_permission_max_level_for_role($role_id, $object_type_id);

		if ($level < 0) $level = 0;
		if ($level > 3) $level = 3;
		return min($level, $max_level);
	}
	
	
	
