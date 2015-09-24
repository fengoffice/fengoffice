<?php

/**
 * Access login, used for handling login / logout requests
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class AccessController extends ApplicationController {

	/**
	 * Construct controller
	 *
	 * @param void
	 * @return null
	 */
	function __construct() {
		parent::__construct();

		$this->setLayout('dialog');
		$this->addHelper('form', 'breadcrumbs', 'pageactions', 'tabbednavigation', 'company_website', 'project_website');
	} // __construct

	/**
	 * Show and process login form
	 *
	 * @param void
	 * @return null
	 */
	function login() {
		include_once ROOT . "/library/browser/Browser.php";
		if (Browser::instance()->getBrowser() == Browser::BROWSER_IE && Browser::instance()->getVersion() < 7) {
			flash_error(lang("ie browser outdated"));
		}
		$this->addHelper('form');

		if(function_exists('logged_user') && (logged_user() instanceof Contact && logged_user()->isUser())) {
			$ref_controller = null;
			$ref_action = null;
			$ref_params = array();
			foreach($_GET as $k => $v) {
				if(str_starts_with($k, 'ref_')) {
					$ref_var_name = trim(substr($k, 4, strlen($k)));
					switch ($ref_var_name) {
						case 'c':
							$ref_controller = $v;
							break;
						case 'a':
							$ref_action = $v;
							break;
						default:
							$ref_params[$ref_var_name] = $v;
					} // switch
				} // if
			} // if
			$this->redirectTo($ref_controller, $ref_action, $ref_params);
		} // if

		$login_data = array_var($_POST, 'login');
		$localization = array_var($_POST, 'configOptionSelect');
		
		if(!is_array($login_data)) {
			$login_data = array();
			foreach($_GET as $k => $v) {
				if(str_starts_with($k, 'ref_')) $login_data[htmlspecialchars($k)] = htmlspecialchars($v);
			} // foreach
		} // if

		tpl_assign('login_data', $login_data);

		if(is_array(array_var($_POST, 'login'))) {
			$username = array_var($login_data, 'username');
			$password = array_var($login_data, 'password');
			$remember = array_var($login_data, 'remember') == 'checked';

			if (config_option('block_login_after_x_tries')) {
				$from_time = DateTimeValueLib::now();
				$from_time = $from_time->add('m', -10);
				$sec_logs = AdministrationLogs::getLastLogs(AdministrationLogs::ADM_LOG_CATEGORY_SECURITY, "invalid login", array_var($_SERVER, 'REMOTE_ADDR'), 10, "`created_on` > '".$from_time->toMySQL()."'");
				if (is_array($sec_logs) && count($sec_logs) >= 5) {
					AdministrationLogs::createLog("invalid login", array_var($_SERVER, 'REMOTE_ADDR'), AdministrationLogs::ADM_LOG_CATEGORY_SECURITY);
					tpl_assign('error', new Error(lang('invalid login data')));
					$this->render();
				}
			}

			if(trim($username) == '') {
				AdministrationLogs::createLog("invalid login", array_var($_SERVER, 'REMOTE_ADDR'), AdministrationLogs::ADM_LOG_CATEGORY_SECURITY);
				tpl_assign('error', new Error(lang('username value missing')));
				$this->render();
			} // if

			if(trim($password) == '') {
				AdministrationLogs::createLog("invalid login", array_var($_SERVER, 'REMOTE_ADDR'), AdministrationLogs::ADM_LOG_CATEGORY_SECURITY);
				tpl_assign('error', new Error(lang('password value missing')));
				$this->render();
			} // if
			
			if (preg_match(EMAIL_FORMAT, $username)) {
				$user = Contacts::getByEmail($username);
				
			} else {
				$user = Contacts::getByUsername($username);
			}
			if(!($user instanceof Contact && $user->isUser()) || $user->getDisabled()) {
				AdministrationLogs::createLog("invalid login", array_var($_SERVER, 'REMOTE_ADDR'), AdministrationLogs::ADM_LOG_CATEGORY_SECURITY);
				tpl_assign('error', new Error(lang('invalid login data')));
				$this->render();
			} // if
	
			$userIsValidPassword = false;
			// If ldap authentication is enabled ldap.config.php will return true.
			$config_ldap_file_path = ROOT . '/config/ldap.config.php'; 
			$config_ldap_is_set = file_exists($config_ldap_file_path) && include_once($config_ldap_file_path);				
			if($config_ldap_is_set === true) {
			  $userIsValidPassword = $user->isValidPasswordLdap($username, $password, $config_ldap);
			}
			if (!$userIsValidPassword){
			  	$userIsValidPassword = $user->isValidPassword($password);
			}
		
			if (!$userIsValidPassword) {
				AdministrationLogs::createLog("invalid login", array_var($_SERVER, 'REMOTE_ADDR'), AdministrationLogs::ADM_LOG_CATEGORY_SECURITY);
				tpl_assign('error', new Error(lang('invalid login data')));
				$this->render();
			} // if
			
			//Start change user language
			if ($localization != 'Default' && self::check_valid_localization($localization)) {
				set_user_config_option('localization',$localization,$user->getId());
			}
			
			$ref_controller = null;
			$ref_action = null;
			$ref_params = array();

			foreach($login_data as $k => $v) {
				if(str_starts_with($k, 'ref_')) {
					$ref_var_name = trim(substr($k, 4, strlen($k)));
					switch ($ref_var_name) {
						case 'c':
							$ref_controller = $v;
							break;
						case 'a':
							$ref_action = $v;
							break;
						default:
							$ref_params[$ref_var_name] = $v;
					} // switch
				} // if
			} // if
			if(!count($ref_params)) $ref_params = null;
						
			if(ContactPasswords::validatePassword($password)){
				$newest_password = ContactPasswords::getNewestContactPassword($user->getId());
				if(!$newest_password instanceof ContactPassword){
					$user_password = new ContactPassword();
					$user_password->setContactId($user->getId());
					$user_password->setPasswordDate(DateTimeValueLib::now());
					$user_password->setPassword(cp_encrypt($password, $user_password->getPasswordDate()->getTimestamp()));
					$user_password->password_temp = $password;
					$user_password->save();
				}else{
					if(ContactPasswords::isContactPasswordExpired($user->getId())){
						$this->redirectTo('access', 'change_password', 
						array('id' => $user->getId(),
							'msg' => 'expired',
							'ref_c' => $ref_controller,
							'ref_a' => $ref_action,
							$ref_params));
					}
				}
			}else{
				$this->redirectTo('access', 'change_password', 
						array('id' => $user->getId(),
							'msg' => 'invalid',
							'ref_c' => $ref_controller,
							'ref_a' => $ref_action,
							$ref_params));
			}
			
			
			try {
				CompanyWebsite::instance()->logUserIn($user, $remember);
				$ip  = get_ip_address();
				ApplicationLogs::createLog($user,ApplicationLogs::ACTION_LOGIN,false,false,true,$ip);
			} catch(Exception $e) {
				tpl_assign('error', new Error(lang('invalid login data')));
				$this->render();
			} // try

			if($ref_controller && $ref_action) {
				$this->redirectTo($ref_controller, $ref_action, $ref_params);
			} else {
				$this->redirectTo('access', 'index');
			} // if
		} // if
	} // login

	function index() {
		include ROOT . "/library/browser/Browser.php";
		$browser = new Browser();
		if ($browser->getBrowser() == Browser::BROWSER_IE && $browser->getVersion() < 7) {
			flash_error(lang("ie browser outdated"));
		}
		if (is_ajax_request()) {
			$timezone =  array_var($_GET,'utz');
			if ($timezone && $timezone != ''){
				$usu = logged_user();
				if ($usu instanceof Contact && $usu->isUser() && !$usu->getDisabled()){
					$usu->setTimezone($timezone);
					$usu->save();
				}
			}
			$this->redirectTo('dashboard', 'main_dashboard');
		} else {
			if (!(logged_user() instanceof Contact && logged_user()->isUser())) {
				$this->redirectTo('access', 'login');
			}
			
			
			
			$this->setLayout("website");
			$this->setTemplate(get_template_path("empty"));
			
			
			
		}
	}
	
	/**
	 * Show and change password form
	 *
	 * @param void
	 * @return null
	 */
	function change_password(){
		$user = Contacts::findById(get_id());
					
		if(!($user instanceof Contact && $user->isUser()) || $user->getDisabled()) return;
		
		tpl_assign('user_id', get_id());
		
		if(array_var($_GET, 'msg') && array_var($_GET, 'msg') == 'expired'){
			$reason = lang('password expired');
		}else{
			$reason = lang('password invalid');
		}
		tpl_assign('reason', $reason);
				
		if(is_array(array_var($_POST, 'changePassword'))) {
			
			$changePassword_data = array_var($_POST, 'changePassword');
		
			$username = array_var($changePassword_data, 'username');
			$old_password = array_var($changePassword_data, 'oldPassword');
			$new_password = array_var($changePassword_data, 'newPassword');
			$repeat_password = array_var($changePassword_data, 'repeatPassword');
			
			if (trim($username) != $user->getUsername()) {
				tpl_assign('error', new Error(lang('invalid login data')));
				$this->render();
			}
			
			if(trim($old_password) == '') {
				tpl_assign('error', new Error(lang('old password required')));
				$this->render();
			} // if
			
			if(!$user->isValidPassword($old_password)) {
				tpl_assign('error', new Error(lang('invalid old password')));
				$this->render();
			} // if

			if(trim($new_password == '')) {
				tpl_assign('error', new Error(lang('password value missing')));
				$this->render();
			} // if

			if($new_password != $repeat_password) {
				tpl_assign('error', new Error(lang('passwords dont match')));
				$this->render();
			} // if

			if(!ContactPasswords::validateMinLength($new_password)){
				$min_pass_length = config_option('min_password_length', 0);
				tpl_assign('error', new Error(lang('password invalid min length', $min_pass_length)));
				$this->render();
			}
			
			if(!ContactPasswords::validateNumbers($new_password)){
				$pass_numbers = config_option('password_numbers', 0);
				tpl_assign('error', new Error(lang('password invalid numbers', $pass_numbers)));
				$this->render();
			}
			
			if(!ContactPasswords::validateUppercaseCharacters($new_password)){
				$pass_uppercase = config_option('password_uppercase_characters', 0);
				tpl_assign('error', new Error(lang('password invalid uppercase', $pass_uppercase)));
				$this->render();
			}
			
			if(!ContactPasswords::validateMetacharacters($new_password)){
				$pass_metacharacters = config_option('password_metacharacters', 0);
				tpl_assign('error', new Error(lang('password invalid metacharacters', $pass_metacharacters)));
				$this->render();
			}
			
			if(!ContactPasswords::validateAgainstPasswordHistory($user->getId(), $new_password)){
				tpl_assign('error', new Error(lang('password exists history')));
				$this->render();
			}
			
			if(!ContactPasswords::validateCharDifferences($user->getId(), $new_password)){
				tpl_assign('error', new Error(lang('password invalid difference')));
				$this->render();
			}
			
			$user_password = new ContactPassword();
			$user_password->setPasswordDate(DateTimeValueLib::now());
			$user_password->setContactId($user->getId());
			$user_password->setPassword(cp_encrypt($new_password, $user_password->getPasswordDate()->getTimestamp()));
			$user_password->password_temp = $new_password;
			$user_password->save();
			
			$user->setPassword($new_password);
			$user->save();
			
			try {
				CompanyWebsite::instance()->logUserIn($user, $remember);
			} catch(Exception $e) {
				tpl_assign('error', new Error(lang('invalid login data')));
				$this->render();
			} // try
			
			$ref_controller = null;
			$ref_action = null;
			$ref_params = array();

			foreach($login_data as $k => $v) {
				if(str_starts_with($k, 'ref_')) {
					$ref_var_name = trim(substr($k, 4, strlen($k)));
					switch ($ref_var_name) {
						case 'c':
							$ref_controller = $v;
							break;
						case 'a':
							$ref_action = $v;
							break;
						default:
							$ref_params[$ref_var_name] = $v;
					} // switch
				} // if
			} // if
			if(!count($ref_params)) $ref_params = null;
			
			if($ref_controller && $ref_action) {
				$this->redirectTo($ref_controller, $ref_action, $ref_params);
			} else {
				//$this->redirectTo('dashboard');
				header("Location: ".ROOT_URL);exit;
			} // if			
		}		
		
	}
	
	/**
	 * Log user back in
	 *
	 * @access public
	 * @param void
	 * @return null
	*/
	function relogin() {
		ajx_current("empty");

		$login_data = array_var($_POST, 'login');
		if (!is_array($login_data)) {
			$login_data = array();
		} // if
		$username = array_var($login_data, 'username');
		$password = array_var($login_data, 'password');
		$remember = array_var($login_data, 'remember', '') != '';
		if (function_exists('logged_user') && (logged_user() instanceof Contact) && logged_user()->getUsername() == $username && logged_user()->isUser()) {
			flash_error(lang("already logged in"));
			return;
		} // if

		if (trim($username == '')) {
			flash_error(lang("username value missing"));
			return;
		} // if

		if (trim($password) == '') {
			flash_error(lang("password value missing"));
			return;
		} // if

		$user = Contacts::getByUsername($username, owner_company());
		if (!($user instanceof Contact && $user->isUser()) || $user->getDisabled()) {
			flash_error(lang('invalid login data'));
			return;
		} // if

		if (!$user->isValidPassword($password)) {
			flash_error(lang('invalid login data'));
			return;
		} // if

		try {
			CompanyWebsite::instance()->logUserIn($user, $remember);
		} catch(Exception $e) {
			flash_error(lang('invalid login data'));
			return;
		} // try
		
	} // relogin
	
	/**
	 * Log user out
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function logout() {
		ApplicationLogs::createLog(logged_user(),ApplicationLogs::ACTION_LOGOUT,false,false,true,get_ip_address());
		CompanyWebsite::instance()->logUserOut();
		$this->redirectTo('access', 'login');
	} // logout

	/**
	 * Render and process forgot password form
	 *
	 * @param void
	 * @return null
	 */
	function forgot_password() {
		if (isset($_GET['your_email'])) {
			$your_email = trim(array_var($_GET, 'your_email'));
		} else {
			$your_email = trim(array_var($_POST, 'your_email'));
		}
		tpl_assign('your_email', $your_email);

		if(array_var($_REQUEST, 'submited') == 'submited') {
			if(!is_valid_email($your_email)) {
				tpl_assign('error', new InvalidEmailAddressError($your_email, lang('invalid email address')));
				$this->render();
			} // if

			$user = Contacts::getByEmail($your_email);
			if(!($user instanceof Contact && $user->isUser()) || $user->getDisabled()) {
				flash_error(lang('email address not in use', $your_email));
				$this->redirectTo('access', 'forgot_password');
			} // if

			$token = sha1(gen_id() . (defined('SEED') ? SEED : ''));
			$timestamp = time() + 60*60*24;
			set_user_config_option('reset_password', $token . ";" . $timestamp, $user->getId());

			try {
				DB::beginWork();
				Notifier::forgotPassword($user, $token);
				flash_success(lang('success forgot password'));
				DB::commit();
			} catch(Exception $e) {
				DB::rollback();
				flash_error(lang('error forgot password'));
			} // try

			$this->redirectTo('access', 'forgot_password', array('instructions_sent'=>1));
		} // if
	} // forgot_password

	/**
	 * Finish the installation - create owner company and administrator
	 *
	 * @param void
	 * @return null
	 */
	function complete_installation() {
		
		if(Contacts::getOwnerCompany() instanceof Contact) {
			die('Owner company already exists'); // Somebody is trying to access this method even if the user already exists
		} // if

		$form_data = array_var($_POST, 'form');
		tpl_assign('form_data', $form_data);

		if(array_var($form_data, 'submited') == 'submited') {
			try {
				$admin_password = trim(array_var($form_data, 'admin_password'));
				$admin_password_a = trim(array_var($form_data, 'admin_password_a'));

				if(trim($admin_password) == '') {
					throw new Error(lang('password value required'));
				} // if

				if($admin_password <> $admin_password_a) {
					throw new Error(lang('passwords dont match'));
				} // if

				DB::beginWork();

				Contacts::delete(); // clear users table

				// Create a company
				$company = new Contact();
				$company->setFirstName(array_var($form_data, 'company_name'));
				$company->setObjectName();
				$company->setIsCompany(true);
				$company->save();
				
				// Init default colors
				set_config_option('brand_colors_head_back', "424242");
				set_config_option('brand_colors_tabs_back', "e7e7e7");
				set_config_option('brand_colors_head_font', "FFFFFF");
				set_config_option('brand_colors_tabs_font', "333333");

				// Create the administrator user
				$administrator = new Contact();
				$pergroup = PermissionGroups::findOne(array('conditions'=>"`name`='Super Administrator'"));
				$administrator->setUserType($pergroup->getId());
				$administrator->setCompanyId($company->getId());
				$administrator->setUsername(array_var($form_data, 'admin_username'));
				
				
				$administrator->setPassword($admin_password);
				$administrator->setFirstname(array_var($form_data, 'admin_username'));
				$administrator->setObjectName();
				$administrator->save();
				
				$user_password = new ContactPassword();
				$user_password->setContactId($administrator->getId());
				$user_password->password_temp = $admin_password;
				$user_password->setPasswordDate(DateTimeValueLib::now());
				$user_password->setPassword(cp_encrypt($admin_password, $user_password->getPasswordDate()->getTimestamp()));
				$user_password->save();
				
				//Add email after save because is needed. 
				$administrator->addEmail(array_var($form_data, 'admin_email'), 'personal', true);
				
				//permissions
				$permission_group = new PermissionGroup();
				$permission_group->setName('Account Owner');
				$permission_group->setContactId($administrator->getId());
				$permission_group->setIsContext(false);
				$permission_group->setType("permission_groups");
				$permission_group->save();
				
				$administrator->setPermissionGroupId($permission_group->getId());
				$administrator->save();
				
				$company->setCreatedById($administrator->getId());
				$company->setUpdatedById($administrator->getId());
				$company->save();
				
				$contact_pg = new ContactPermissionGroup();
				$contact_pg->setContactId($administrator->getId());
				$contact_pg->setPermissionGroupId($permission_group->getId());
				$contact_pg->save();
				
				// tab panel permissions
				$panels = TabPanels::getEnabled();
				foreach ($panels as $panel) {
					$tpp = new TabPanelPermission();
					$tpp->setPermissionGroupId($administrator->getPermissionGroupId());
					$tpp->setTabPanelId($panel->getId());
					$tpp->save();
				}
				
				// dimension permissions
				$dimensions = Dimensions::findAll();
				foreach ($dimensions as $dimension) {
					if ($dimension->getDefinesPermissions()) {
						$cdp = ContactDimensionPermissions::findOne(array("conditions" => "`permission_group_id` = ".$administrator->getPermissionGroupId()." AND `dimension_id` = ".$dimension->getId()));
						if (!$cdp instanceof ContactDimensionPermission) {
							$cdp = new ContactDimensionPermission();
							$cdp->setPermissionGroupId($administrator->getPermissionGroupId());
							$cdp->setContactDimensionId($dimension->getId());
						}
						$cdp->setPermissionType('allow all');
						$cdp->save();
						
						// contact member permisssion entries
						$members = $dimension->getAllMembers();
						foreach ($members as $member) {
							$ots = DimensionObjectTypeContents::getContentObjectTypeIds($dimension->getId(), $member->getObjectTypeId());
							$ots[]=$member->getObjectId();
							foreach ($ots as $ot) {
								$cmp = ContactMemberPermissions::findOne();
								if (!$cmp instanceof ContactMemberPermission) {
									$cmp = new ContactMemberPermission(array("conditions" => "`permission_group_id` = ".$administrator->getPermissionGroupId()." AND `member_id` = ".$member->getId()." AND `object_type_id` = $ot"));
									$cmp->setPermissionGroupId($administrator->getPermissionGroupId());
									$cmp->setMemberId($member->getId());
									$cmp->setObjectTypeId($ot);
								}
								$cmp->setCanWrite(1);
								$cmp->setCanDelete(1);
								$cmp->save();
							}
						}
					}
				}
				
				// system permissions
				$sp = new SystemPermission();
				$sp->setPermissionGroupId($administrator->getPermissionGroupId());
				$sp->setAllPermissions(true);
				$sp->save();
				
				// root permissions
				DB::executeAll("
				INSERT INTO ".TABLE_PREFIX."contact_member_permissions (permission_group_id, member_id, object_type_id, can_delete, can_write)
				  SELECT ".$administrator->getPermissionGroupId().", 0, rtp.object_type_id, rtp.can_delete, rtp.can_write FROM ".TABLE_PREFIX."role_object_type_permissions rtp 
				  WHERE rtp.object_type_id NOT IN (SELECT id FROM ".TABLE_PREFIX."object_types WHERE name IN ('mail','template','file_revision')) AND rtp.role_id in (
				    SELECT pg.id FROM ".TABLE_PREFIX."permission_groups pg WHERE pg.type='roles' AND pg.name IN ('Super Administrator','Administrator','Manager','Executive')
				  )
				ON DUPLICATE KEY UPDATE member_id=0;");
				
				Hook::fire('after_user_add', $administrator, $null);
				
				DB::commit();

				$this->redirectTo('access', 'login');
			} catch(Exception $e) {
				tpl_assign('error', $e);
				DB::rollback();
			} // try
		} // if
	} // complete_installation

	
	function get_javascript_translation() {
		$content = "/* start */\n";
		$fileDir = ROOT . "/language/" . Localization::instance()->getLocale();
		
		//Get Feng Office translation files
		$filenames = get_files($fileDir, "js");
		sort($filenames);
		foreach ($filenames as $f) {
			$content .= "\n/* $f */\n";
			$content .= "try {";				
			$content .= file_get_contents($f);
			$content .= "} catch (e) {}";
		}
		
		$plugins = Plugins::instance ()->getActive ();
		
		foreach ( $plugins as $plugin ) {

			$plg_dir = $plugin->getLanguagePath () . "/" . Localization::instance()->getLocale ();
			if (is_dir ( $plg_dir )) {
				$files = get_files($plg_dir, 'js');
				if (is_array ( $files )) {
					sort ( $files );
					
					foreach ( $files as $file ) {
						$content .= "\n/* $file */\n";
						$content .= "try {";
						/**
						 * The js file can contain PHP code so use include instead of file_get_contents.
						 * To avoid sending headers, use output buffer.
						 * This change help to avoid the need of multiple lang files.. javascripts and phps. 
						 * You can create only one php file containing all traslations, 
						 * and this will populate client and server side langs datasorces  
						 */ 
						ob_start();  
						include $file ; 
						$content .= ob_get_contents();
						ob_end_clean(); //!important: Clean output buffer to save memory
						$content .= "} catch (e) {}";
					}
				}
			}
		}
		$content .= "\n/* end */\n";
		$this->setLayout("json");
		$this->renderText($content, true);
	}
	
	function get_javascript_translation_default() {
		$defaultLang = "en_us";
		$content = "/* start */\n";		
		$fileDir = ROOT . "/language/".$defaultLang;
	
		//Get Feng Office translation files
		$filenames = get_files($fileDir, "js");
			
		sort($filenames);
		foreach ($filenames as $f) {
			$content .= "\n/* $f */\n";
			$content .= "try {";
			$content .= file_get_contents($f);
			$content .= "} catch (e) {}";
		}
	
		// include all installed plugins, no matter if they they have not been activated
		$plugins = Plugins::instance ()->getAll();
	
		foreach ( $plugins as $plugin ) {
			$plg_dir = $plugin->getLanguagePath () . "/" . $defaultLang;
			if (is_dir ( $plg_dir )) {
				$files = get_files($plg_dir, 'js');
				if (is_array ( $files )) {
					sort ( $files );
						
					foreach ( $files as $file ) {
						$content .= "\n/* $file */\n";
						$content .= "try {";
						/**
						 * The js file can contain PHP code so use include instead of file_get_contents.
						 * To avoid sending headers, use output buffer.
						 * This change help to avoid the need of multiple lang files.. javascripts and phps.
						 * You can create only one php file containing all traslations,
						 * and this will populate client and server side langs datasorces
						 */
						ob_start();
						include $file ;
						$content .= ob_get_contents();
						ob_end_clean(); //!important: Clean output buffer to save memory
						$content .= "} catch (e) {}";
					}
				}
			}
		}
		$content .= "\n/* end */\n";
		
		$content = str_replace("addLangs", "addLangsDefault", $content);
		$this->setLayout("json");
		$this->renderText($content, true);
	}
	
	function reset_password() {
		$tok = array_var($_GET,'t');
		$uid = array_var($_GET,'uid');
                $type_notifier = array_var($_GET,'type_notifier');
		
		if (!$tok || !$uid) {
			flash_error(lang('invalid parameters'));
			$this->redirectTo('access', 'login');
		}
		$user = Contacts::findById($uid);
		if (!($user instanceof Contact && $user->isUser()) || $user->getDisabled()) {
			flash_error(lang('user dnx'));
			$this->redirectTo('access', 'login');
		}
		$stok = user_config_option('reset_password', null, $user->getId());
		if (!$stok) {
			flash_error(lang('reset password expired', lang('forgot password')));
			$this->redirectTo('access', 'login');
		}
		$split = explode(";", $stok);
		if (count($split) < 2) {
			flash_error(lang('reset password expired', lang('forgot password')));
			$this->redirectTo('access', 'login');
		}
		$token = $split[0];
		$timestamp = $split[1];
		if ($timestamp < time()) {
			set_user_config_option('reset_password', '', $user->getId());
			flash_error(lang('reset password expired', lang('forgot password')));
			$this->redirectTo('access', 'login');
		}
		if ($token != $tok) {
			flash_error(lang('reset password expired', lang('forgot password')));
			$this->redirectTo('access', 'login');
		}
		tpl_assign('token', $token);
		tpl_assign('user', $user);
                tpl_assign('type_notifier', $type_notifier);
                
		$new_password = array_var($_POST, 'new_password');
		if ($new_password) {
			$repeat_password = array_var($_POST, 'repeat_password');
			if ($new_password != $repeat_password) {
				flash_error(lang('passwords dont match'));
				return;
			}
			try{
				$user_password = new ContactPassword();
				$user_password->setContactId($user->getId());
				$user_password->password_temp = $new_password;
				$user_password->setPasswordDate(DateTimeValueLib::now());
				$user_password->setPassword(cp_encrypt($new_password, $user_password->getPasswordDate()->getTimestamp()));
				$user_password->save();
		
				$user->setPassword($new_password);
				$user->setUpdatedOn(DateTimeValueLib::now());
				$user->save();
				set_user_config_option('reset_password', '', $user->getId());
				flash_success(lang('success reset password'));
				CompanyWebsite::instance()->logUserOut();
				$this->redirectTo('access', 'login');
			}catch(Exception $e){
				flash_error($e->getMessage());
			}

		}
	}
	
	function view_help_manual() {
		$this->redirectToUrl(help_link());
	}
	
	private function check_valid_localization($localization) {
		$language_dir = with_slash(ROOT . "/language");
		$result = false;
		if (is_dir($language_dir)) {
			$d = dir($language_dir);
			while (!$result && ($entry = $d->read()) !== false) {
				if (str_starts_with($entry, '.') || str_starts_with($entry, '..') || $entry == "CVS") {
					continue;
				}
				$result = is_dir($language_dir . $entry) && $entry == $localization;
			}
			$d->close();
		}
		return $result;
	}
} // AccessController

?>