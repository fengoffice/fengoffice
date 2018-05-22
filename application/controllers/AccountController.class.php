<?php

/**
 * User account controller with all the parts related to it (profile update, private messages etc)
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>, Marcos Saiz <marcos.saiz@fengoffice.com>
 */
class AccountController extends ApplicationController {

	/**
	 * Construct the AccountController
	 *
	 * @access public
	 * @param void
	 * @return AccountController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
		if (array_var($_GET, 'current') != 'administration') {
			ajx_set_panel("account");
		}
	} // __construct

	/**
	 * Show account index page
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function index() {
		$this->setHelp("account");
		$this->setTemplate("card");
		$this->setControllerName("user");
		tpl_assign('user', logged_user());
		ajx_set_no_toolbar(true);
		
		tpl_assign('logs', $logs);
	} // index

	/**
	 * Edit logged user profile. 
	 * Called with different POST format from "administration/users/edit user profile " and from "profile/edit my profile" 
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit_profile() {
		ajx_set_panel("");
		$user = Contacts::findById(get_id());
		if(!($user instanceof Contact && $user->isUser()) || $user->getDisabled()) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		} // if

		
		$company = $user->getCompany();
		/*if(!($company instanceof Contact)) {
			flash_error(lang('company dnx'));
			ajx_current("empty");
			return;
		} // if
		*/

		if(!$user->canUpdateProfile(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$redirect_to = array_var($_GET, 'redirect_to');
		if((trim($redirect_to)) == '' || !is_valid_url($redirect_to)) {
			$redirect_to = $user->getCardUserUrl();
		} // if
		tpl_assign('redirect_to', null);

		$user_data = array_var($_POST, 'user');
		if(!is_array($user_data)) {
			$user_data = array(
	          'username'      => $user->getUsername(),
	          'email'         => $user->getEmailAddress(),
	          'display_name'  => $user->getObjectName(),
	          'user_timezone_id' => $user->getUserTimezoneId(),
	          'company_id'    => $user->getCompanyId(),
	          'is_admin'      => $user->isAdministrator(),
			  'type'          => $user->getUserType(),
			); // array

		} // if

		tpl_assign('user', $user);
		tpl_assign('company', $company);
		tpl_assign('user_data', $user_data);
		tpl_assign('billing_categories', BillingCategories::findAll());
		// Permission Groups
		$groups = PermissionGroups::getNonPersonalSameLevelPermissionsGroups('`parent_id`,`id` ASC');
		tpl_assign('groups', $groups);
		$roles= SystemPermissions::getAllRolesPermissions();
		tpl_assign('roles', $roles);
		$tabs= TabPanelPermissions::getAllRolesModules();
		tpl_assign('tabs_allowed', $tabs);
		// Submit user
		if(is_array(array_var($_POST, 'user'))) {
			$company_id = array_var($user_data,'company_id');
			if($company_id && !(Contacts::findById($company_id) instanceof Contact)){
				ajx_current("empty");
				flash_error(lang("company dnx"));
				return ;
			}
			try {
				DB::beginWork();

				$user->setTimezone(array_var($user_data,'timezone'));
				$user->setDefaultBillingId(array_var($user_data,'default_billing_id'));
				$user->setUpdatedOn(DateTimeValueLib::now());
				
				if (logged_user()->isAdministrator()){
					$user->setUsername(array_var($user_data,'username'));
				} else {
					$user->setCompanyId(array_var($user_data,'company_id'));
				}
				
				$user->save();
				
				$autotimezone = array_var($user_data, 'autodetect_time_zone', null);
				if ($autotimezone !== null) {
					set_user_config_option('autodetect_time_zone', $autotimezone, $user->getId());
				}
				
				$object_controller = new ObjectController();
			  	$object_controller->add_custom_properties($user);
			  
				$ret = null;
				Hook::fire('after_edit_profile', $user, $ret);
				$pg_id = $user->getPermissionGroupId();
				DB::commit();

				flash_success(lang('success update profile'));
				ajx_current("back");
				ajx_add("overview-panel", "reload");
			} catch(Exception $e) {
				DB::rollback();
				ajx_current("empty");
				flash_error($e->getMessage());
			} // try
		} // if
	} // edit_profile

	/**
	 * Edit logged user password
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit_password() {
		$user = Contacts::findById(get_id());
		if(!($user instanceof Contact && $user->isUser()) || $user->getDisabled()) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$user->canChangePassword(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$redirect_to = array_var($_GET, 'redirect_to');
		if((trim($redirect_to)) == '' || !is_valid_url($redirect_to)) {
			$redirect_to = $user->getCardUserUrl();
		} // if
		tpl_assign('redirect_to', null);

		$password_data = array_var($_POST, 'password');
		tpl_assign('user', $user);

		if(is_array($password_data)) {
			$old_password = array_var($password_data, 'old_password');
			$new_password = array_var($password_data, 'new_password');
			$new_password_again = array_var($password_data, 'new_password_again');

			try {
				if(!logged_user()->isAdminGroup()) {
					if(trim($old_password) == '') {
						throw new Error(lang('old password required'));
					} // if
					if(!$user->isValidPassword($old_password)) {
						throw new Error(lang('invalid old password'));
					} // if
				} // if

				if(trim($new_password) == '') {
					throw new Error(lang('password value required'));
				} // if
				if($new_password <> $new_password_again) {
					throw new Error(lang('passwords dont match'));
				} // if
				
				$user_password = new ContactPassword();
				$user_password->setContactId(get_id());
				$user_password->password_temp = $new_password;
				$user_password->setPasswordDate(DateTimeValueLib::now());
				$user_password->setPassword(cp_encrypt($new_password, $user_password->getPasswordDate()->getTimestamp()));
				$user_password->save();

				$user->setPassword($new_password);
				$user->setUpdatedOn(DateTimeValueLib::now());
				$user->save();
				
				if ($user->getId() == logged_user()->getId()) {
					CompanyWebsite::instance()->logUserIn($user, Cookie::getValue("remember", 0));
				}

				ApplicationLogs::createLog($user, ApplicationLogs::ACTION_EDIT);
				flash_success(lang('success edit user', $user->getUsername()));
				ajx_current("back");

			} catch(Exception $e) {
				ajx_current("empty");
				flash_error($e->getMessage());
			} // try
		} // if
	} // edit_password
	
	
	/**
	 * Edit logged user external tokens
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit_external_tokens() {
	    
	    $user_id = array_var($_GET, 'user_id');
	    $user = Contacts::findById($user_id);

	    if(!($user instanceof Contact && $user->isUser()) || $user->getDisabled()) {
	        flash_error(lang('user dnx'));
	        ajx_current("empty");
	        return;
	    } // if
	    
	    if(!$user->canChangeExternalToken(logged_user())) {
	        flash_error(lang('no access permissions'));
	        ajx_current("empty");
	        return;
	    } // if
	    
	    tpl_assign('redirect_to', null);
	    
	    $token_data = array_var($_REQUEST, 'token_data');
	    tpl_assign('user', $user);
	    $external_tokens = ContactExternalTokens::instance()->findAll(array('conditions' =>  "`contact_id` = ".$user_id ));
	    
	    tpl_assign('external_tokens', $external_tokens);
	    
	    if(is_array($token_data)) {

	        $external_key = array_var($token_data, 'external_key');
	        $external_name = array_var($token_data, 'external_name');
	        $token_type = array_var($token_data, 'type');
	        $action = array_var($token_data, 'action');
	        $token_id = array_var($token_data, 'token_id');
	        
	        try {
	            
	            if (isset($action) && $action == 'edit'){
	                $contact_external_token = ContactExternalTokens::instance()->findById($token_id);	                
	                $contact_external_token->setType($token_type);
	                $contact_external_token->setExternalKey($external_key);
	                $contact_external_token->setExternalName($external_name);
	                $contact_external_token->save();
	                
	                ApplicationLogs::createLog($user, ApplicationLogs::ACTION_EDIT);
	                evt_add("reload current panel");
	                flash_success(lang('success edited token'));
	                
	            }else if(isset($action) && $action == 'resetToken'){
	                

	                $contact_external_token = ContactExternalTokens::instance()->findById($token_id);
	                
	                $new_token = str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789".uniqid());	                
	                $contact_external_token->setToken($new_token);
	                $contact_external_token->save();
	                ajx_extra_data(array('token' => $new_token));	                
	                ApplicationLogs::createLog($user, ApplicationLogs::ACTION_EDIT);
	                ajx_current("reload");	                
	                flash_success(lang('success edited token'));
	                
	            }else if($action == 'add'){
	                //create new token
	                $new_token = str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789".uniqid());	                
	                $contact_external_token = new ContactExternalToken();
	                $contact_external_token->setContactId($user_id);
	                $contact_external_token->setToken($new_token);
	                $contact_external_token->setType($token_type);
	                $contact_external_token->setExternalKey($external_key);
	                $contact_external_token->setExternalName($external_name);
	                $contact_external_token->setCreatedDate(DateTimeValueLib::now());
	                //$contact_external_token->setExpiredDate(DateTimeValueLib::now());
	                $contact_external_token->save();
	                
	                ApplicationLogs::createLog($user, ApplicationLogs::ACTION_ADD);
	                
	                evt_add("reload current panel");
	                flash_success(lang('success created token'));	                
	            }
	            
	            
	        } catch(Exception $e) {
	            ajx_current("empty");
	            flash_error($e->getMessage());
	        } // try
	    } // if
	} // edit_external_token

	/**
	 * Delete external token
	 *
	 * @param $id
	 * @return null
	 */	
	function delete_external_token() {
	    $id = array_var($_GET, 'token_id');
	    $user = logged_user();
	    
	    if(!isset($id)){
	        flash_error(lang('user dnx'));	        
	        return;
	    } //if
	    
	    if(!($user instanceof Contact && $user->isUser()) || $user->getDisabled()) {
	        flash_error(lang('user dnx'));
	        ajx_current("empty");
	        return;
	    } // if
	    
	    if(!$user->canChangeExternalToken(logged_user())) {
	        flash_error(lang('no access permissions'));
	        ajx_current("empty");
	        return;
	    } // if
	    
	    $token = ContactExternalTokens::instance()->findById($id);
	    
	    
	    if (isset($token)){
	       
	       try{    
	            DB::beginWork();
	            
	            $token->delete();
	            
	            DB::commit();
	            
	            ApplicationLogs::createLog($user, ApplicationLogs::ACTION_DELETE);
	            flash_success(lang('success deleted token'));
	            ajx_current("reload");	            
	        
	       }catch(Exception $e) {
	        DB::rollback();
	        flash_error(lang('error deleted token'));
	        ajx_current("empty");
	       } // try
	    }else{
	        ajx_current("empty");
	    }	    
	    
	}
	
	/**
	 * Show update permissions page
	 *
	 * @param void
	 * @return null
	 */
	function update_permissions() {
		$user = Contacts::findById(get_id());
		if(!($user instanceof Contact && $user->isUser()) || $user->getDisabled()) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$user->canUpdatePermissions(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$redirect_to = array_var($_GET, 'redirect_to');
		if((trim($redirect_to)) == '' || !is_valid_url($redirect_to)) {
			$redirect_to = $user->getCardUserUrl();
		} // if
		
		$sys_permissions_data = array_var($_POST, 'sys_perm');
		
		if(!is_array($sys_permissions_data)) {
			$pg_id = $user->getPermissionGroupId();
			$parameters = permission_form_parameters($pg_id);
			
			// Module Permissions
			$module_permissions = TabPanelPermissions::findAll(array("conditions" => "`permission_group_id` = $pg_id"));
			$module_permissions_info = array();
			foreach ($module_permissions as $mp) {
				$module_permissions_info[$mp->getTabPanelId()] = 1;
			}
			$all_modules = TabPanels::findAll(array("conditions" => "`enabled` = 1 AND (plugin_id is NULL OR plugin_id = 0 OR plugin_id IN (SELECT id FROM ".TABLE_PREFIX."plugins WHERE is_activated > 0 AND is_installed > 0))", "order" => "ordering"));
			$all_modules_info = array();
			foreach ($all_modules as $module) {
				$all_modules_info[] = array('id' => $module->getId(), 'name' => lang($module->getTitle()), 'ot' => $module->getObjectTypeId());
			}
			
			// System Permissions
			$system_permissions = SystemPermissions::findById($pg_id);
			
			tpl_assign('module_permissions_info', $module_permissions_info);
			tpl_assign('all_modules_info', $all_modules_info);
			if (!$system_permissions instanceof SystemPermission) {
				$system_permissions = new SystemPermission();				
			}
			tpl_assign('system_permissions', $system_permissions);
			
			tpl_assign('permission_parameters', $parameters);
			
			$more_permissions = array();
			Hook::fire('add_user_permissions', $pg_id, $more_permissions);
			tpl_assign('more_permissions', $more_permissions);
			
			tpl_assign('pg_id', $pg_id);
			
			// Permission Groups
			$groups = PermissionGroups::getNonPersonalSameLevelPermissionsGroups('`parent_id`,`id` ASC');
			tpl_assign('groups', $groups);
			$roles = SystemPermissions::getAllRolesPermissions();
			tpl_assign('roles', $roles);
			$tabs = TabPanelPermissions::getAllRolesModules();
			tpl_assign('tabs_allowed', $tabs);
			tpl_assign('guest_groups', PermissionGroups::instance()->getGuestPermissionGroups());
		}
		
		
		tpl_assign('user', $user);
		tpl_assign('redirect_to', $redirect_to);

		if(array_var($_POST, 'submitted') == 'submitted') {
			$user_data = array_var($_POST, 'user');
			if (!is_array($user_data)) $user_data = array();
			try{
				DB::beginWork();
				$do_rollback = true;
				$pg_id = $user->getPermissionGroupId();
				$type = array_var(array_var(array_var($_POST, 'contact'), 'user'), 'type');
				$user->setUserType($type);
				$user->save();
				
				DB::commit();
				$do_rollback = false;
				save_user_permissions_background(logged_user(), $pg_id, $user->isGuest());
				
				flash_success(lang('success user permissions updated'));
				ajx_current("back");
			} catch(Exception $e) {
				if ($do_rollback) DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		} // if
	} // update_permissions

	/**
	 * Edit logged user avatar
	 *
	 * @param void
	 * @return null
	 */
	function edit_picture() {
		$user = Contacts::findById(get_id());
		if (!($user instanceof Contact && $user->isUser()) || $user->getDisabled()) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$user->canUpdateProfile(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$redirect_to = array_var($_GET, 'redirect_to');
		if((trim($redirect_to)) == '' || !is_valid_url($redirect_to)) {
			$redirect_to = $user->getUpdatePictureUrl();
		} // if
		tpl_assign('redirect_to', $redirect_to);

		$avatar = array_var($_FILES, 'new_avatar');
		tpl_assign('user', $user);

		if(is_array($avatar)) {
			try {
				if(!isset($avatar['name']) || !isset($avatar['type']) || !isset($avatar['size']) || !isset($avatar['tmp_name']) || !is_readable($avatar['tmp_name'])) {
					throw new InvalidUploadError($avatar, lang('error upload file'));
				} // if

				$valid_types = array('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/gif', 'image/png','image/x-png');
				$max_width   = config_option('max_avatar_width', 50);
				$max_height  = config_option('max_avatar_height', 50);

				if(!in_array($avatar['type'], $valid_types) || !($image = getimagesize($avatar['tmp_name']))) {
					throw new InvalidUploadError($avatar, lang('invalid upload type', 'JPG, GIF, PNG'));
				} // if

				$old_file = $user->getPicturePath();
				DB::beginWork();

				$user->setUpdatedOn(DateTimeValueLib::now());
				if(!$user->setPicture($avatar['tmp_name'], $avatar['type'], $max_width, $max_height)) {
					throw new InvalidUploadError($avatar, lang('error edit avatar'));
				} // if

				
				DB::commit();
				ApplicationLogs::createLog($user, ApplicationLogs::ACTION_EDIT);
				if(is_file($old_file)) {
					@unlink($old_file);
				} // if

				flash_success(lang('success edit avatar'));
				ajx_current("back");
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // edit_picture

	/**
	 * Delete avatar
	 *
	 * @param void
	 * @return null
	 */
	function delete_picture() {
		$user = Contacts::findById(get_id());
		if(!($user instanceof Contact && $user->isUser()) || $user->getDisabled()) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$user->canUpdateProfile(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$redirect_to = array_var($_GET, 'redirect_to');
		if((trim($redirect_to)) == '' || !is_valid_url($redirect_to)) {
			$redirect_to = $user->getUpdatePictureUrl();
		} // if
		tpl_assign('redirect_to', $redirect_to);

		if(!$user->hasPicture()) {
			flash_error(lang('avatar dnx'));
			ajx_current("empty");
			return;
		} // if

		try {
			DB::beginWork();
			$user->setUpdatedOn(DateTimeValueLib::now());
			$user->deletePicture();
			$user->save();
			

			DB::commit();
			
			ApplicationLogs::createLog($user, ApplicationLogs::ACTION_EDIT);
			flash_success(lang('success delete avatar'));
			ajx_current("back");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete avatar'));
			ajx_current("empty");
		} // try

	} // delete_picture
	
	function update_user_preference(){
		ajx_current("empty");
		$option_name = array_var($_GET,'name');
		$option_value = array_var($_GET,'value');
		if($option_name != ''){
			try{
				DB::beginWork();
				set_user_config_option($option_name, $option_value, logged_user()->getId());
				evt_add('user preference changed', array('name' => $option_name, 'value' => $option_value));
				DB::commit();
			} catch(Exception $e){
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		}
		
	}
	
	function get_user_preference(){
		ajx_current("empty");
		$option_name = array_var($_REQUEST,'name');
		$option_value = "";
		if($option_name != ''){
			$option_value = user_config_option($option_name);
		}
		ajx_extra_data(array('opt_val' => $option_value));
	}
	
	function disable() {
		ajx_set_panel(array_var($_REQUEST, "current"));
		$user = Contacts::findById(get_id());
		if (!($user instanceof Contact && $user->isUser())) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		}
		
		if (!$user->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		try {
			DB::beginWork();
			$user->disable(false);
			$ret = null ; 
			Hook::fire("user_disabled", $user, $ret );
			DB::commit();
			ApplicationLogs::createLog($user, ApplicationLogs::ACTION_TRASH);
			
			ajx_current("reload");
			if(array_var($_GET,'current')!="administration") {
				evt_add("reload company users", array('company_id' => $user->getCompanyId()));
			}
			
			flash_success(lang('success disable user', $user->getObjectName()));
			
		} catch (Exception $e) {
			flash_error($e->getMessage());
			DB::rollback();
			ajx_current("empty");
		}
	}
	
	
	function delete_user() {
		$user = Contacts::findById(get_id());
		if (!($user instanceof Contact && $user->isUser())) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		}
		
		if (!$user->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		try {
			DB::beginWork();
			$user->disable();
			$ret = null ; 
			Hook::fire("user_disabled", $user, $ret );
			DB::commit();
			ApplicationLogs::createLog($user, ApplicationLogs::ACTION_TRASH);
			flash_success(lang('success delete user', $user->getObjectName()));
			
			if(array_var($_GET,'current')=="administration") {
				ajx_current("reload");
			}else{
				evt_add('current panel back');
				ajx_current("empty");
			}
			
		} catch (Exception $e) {
			flash_error($e->getMessage());
			DB::rollback();
			ajx_current("empty");
		}
	}
	
	function restore_user() {
		ajx_set_panel(array_var($_REQUEST, "current"));
		
		$user = Contacts::findById(get_id());
		if (!($user instanceof Contact && $user->isUser())) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		}
		
		if (!$user->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		try {
			DB::beginWork();
			$user->setDisabled(false);
			if (trim($user->getTokenDisabled()) != "" && trim($user->getToken()) == "") {
				$user->setToken($user->getTokenDisabled());
			}
			$user->unarchive();
			$ret = null ; 
			Hook::fire("user_restored", $user, $ret );			
			DB::commit();
			ApplicationLogs::createLog($user, ApplicationLogs::ACTION_UNTRASH);
			
			flash_success(lang('success restore user', $user->getObjectName()));
			ajx_current("reload");
			
		} catch (Exception $e) {
			flash_error($e->getMessage());
			DB::rollback();
			ajx_current("empty");
		}
	}
	
	
	function set_timezone() {
		$tz_name = array_var($_REQUEST, 'tz_name');
		$tz_offset = array_var($_REQUEST, 'tz_offset');
		
		$zone_id = null;
		$zone = Timezones::getTimezoneFromName($tz_name);
		if (is_array($zone)) {
			$zone_id = $zone['id'];
		}
		
		$user_tz_hours = logged_user()->getUserTimezoneValue() / 3600;
		
		// change user timezone id
		if ($zone_id && ($zone_id != logged_user()->getTimezoneId() || $tz_offset != $user_tz_hours)) {
			$sql = "UPDATE ".TABLE_PREFIX."contacts SET 
						user_timezone_id = '$zone_id',
						timezone = '$tz_offset'
					WHERE object_id = ".logged_user()->getId();
			
			DB::execute($sql);
			
			// @TODO: When DST has changed we have to create a reminder to the logged user and the administrator so they can change the timezone configuration.
			/*
			if ($zone['has_dst']) {
				if ($zone['using_dst'] && ($tz_offset * 3600) == $zone['gmt_offset']) {
					Timezones::updateUsingDst($zone['id'], '0');
				}
				if (!$zone['using_dst'] && ($tz_offset * 3600) == $zone['gmt_dst_offset']) {
					Timezones::updateUsingDst($zone['id'], '1');
				}
			}
			*/
		}
		
		
		ajx_current("empty");
	}
	
	
	function get_country_timezones() {
		$ccode = array_var($_REQUEST, 'code');
		$zones = Timezones::getTimezonesByCountryCode($ccode);
		
		$zones_data = array();
		foreach ($zones as $z) {
			$zones_data[$z['id']] = Timezones::getFormattedDescription($z);
		}
		
		ajx_current("empty");
		ajx_extra_data(array('timezones' => $zones_data));
	}
	
	function add_token() {
        $user_id = array_var($_REQUEST, 'user_id');
        $user = Contacts::findById($user_id);
	    if(!($user instanceof Contact && $user->isUser()) || $user->getDisabled()) {
	        flash_error(lang('user dnx'));
	        ajx_current("empty");
	        return;
	    } // if
	    
	    if (!can_manage_configuration(logged_user())) {
	        flash_error("no access permissions");
	        return;
	    }
	    
	    if (logged_user()->isGuest()) {
	        flash_error(lang('no access permissions'));
	        ajx_current('empty');
	        return;
	    }
	    $this->setTemplate("add_token");
	    tpl_assign('user', $user);
	    tpl_assign('modal', true);
	    $action = array_var($_REQUEST, 'action');
	    if ($action){
	        tpl_assign('action', 'edit');
	        $token_id = array_var($_REQUEST, 'token_id');	        
	        $token_obj = ContactExternalTokens::instance()->findById($token_id);
	        tpl_assign('token', $token_obj);
	    }
	    
	}

} // AccountController

?>