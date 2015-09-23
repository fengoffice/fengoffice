<?php

define('ADMIN_SESSION_TIMEOUT', 3600);

/**
 * Administration controller
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class AdministrationController extends ApplicationController {

	/**
	 * Construct the AdministrationController
	 *
	 * @access public
	 * @param void
	 * @return AdministrationController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
		//ajx_set_panel("administration");

		// Access permissions
		if(!(logged_user()->isExecutiveGroup())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
		} // if

		//Autentify password
		if (config_option('ask_administration_autentification')) {
			$last_login = array_var($_SESSION, 'admin_login', 0);
			if ($last_login < time() - ADMIN_SESSION_TIMEOUT) {
				if (array_var($_GET, 'a') != 'password_autentify') {
					$ref_controller = null;
					$ref_action = null;
					$ref_params = array();
					foreach($_GET as $k => $v) {
						$ref_var_name = $k;
						switch ($ref_var_name) {
							case 'c':
								$ref_controller = $v;
								break;
							case 'a':
								$ref_action = $v;
								break;
							default:
								$ref_params[$ref_var_name] = $v;
						}// switch
					}
					$url = get_url($ref_controller, $ref_action, $ref_params);
					$this->redirectTo('administration', 'password_autentify', array('url' => $url));
				}
			} else {
				$_SESSION['admin_login'] = time();
			}
		}//if
	} // __construct

	/**
	 * Show administration index
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function index() {
		if(!(logged_user()->isExecutiveGroup())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
	} // index
    
    
	function scolors () {
		if (can_manage_configuration(logged_user())) {
			
			$valid_options = ConfigCategories::getOptionsFromCategory('brand_colors');
			foreach ($_POST as $k => $v) {
				print("$k => $v");
				if (in_array($k, $valid_options)) {
					set_config_option($k, str_replace("#", "", $v));
				}
			}
			
		}
		exit;
    }

	/**
	 * Validate user information in order to give acces to the administration panel
	 * */
	function password_autentify() {
		if(!logged_user()->isCompanyAdmin(owner_company())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		if (isset($_POST['enetedPassword'])) {
			$userName = array_var($_POST,'userName');

			$pass = array_var($_POST,'enetedPassword');

			if(trim($userName) == '') {
				flash_error(lang('username value missing'));
				ajx_current("empty");
				return;
			} // if
			if(trim($pass) == '') {
				flash_error(lang('password value missing'));
				ajx_current("empty");
				return;
			} // if
				
			$user = Contacts::getByUsername($userName);
			if(!($user instanceof Contact)) {
				flash_error(lang('invalid login data'));
				ajx_current("empty");
				return;
			} // if
				
			if(!$user->isValidPassword($pass)) {
				flash_error(lang('invalid login data'));
				ajx_current("empty");
				return;
			} // if

			if ($userName != logged_user()->getUsername()){
				flash_error(lang('invalid login data'));
				ajx_current("empty");
				return;
			}
				
			$_SESSION['admin_login'] = time();
			$this->redirectToUrl($_POST['url']);
		} else {
			$last_login = array_var($_SESSION, 'admin_login', 0);
			if ($last_login >= time() - ADMIN_SESSION_TIMEOUT) {
				$this->redirectToUrl(array_var($_GET, 'url', get_url('administration', 'index')));
			}
		}

		tpl_assign('url', array_var($_GET, 'url', get_url('administration', 'index')));

	}

	/**
	 * Show company page
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function company() {
		if(!can_manage_configuration(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		tpl_assign('company', owner_company());
		ajx_set_no_toolbar(true);
		$this->setTemplate(get_template_path('view_company', 'contact'));
	} // company

	/**
	 * Show owner company members
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function members() {
		if(!can_manage_security(logged_user())){
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		tpl_assign('isMemberList' , true);
		tpl_assign('company', owner_company());
		tpl_assign('users_by_company', Contacts::getGroupedByCompany());
	} // members



	/**
	 * List clients
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function clients() {
		if(!can_manage_security(logged_user())){
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		tpl_assign('clients', owner_company()->getClientCompanies());
	} // clients

	/**
	 * List custom properties
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function custom_properties() {
		if(!can_manage_configuration(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$object_types = ObjectTypes::instance()->findAll(array("conditions" => "`type` IN ('content_object')  AND `name` <> 'template_task' AND name <> 'template_milestone' AND `name` <> 'file revision'", "order" => "name"));
		$ordered_object_types = array();
		foreach ($object_types as $ot) {
			$ordered_object_types[$ot->getId()] = lang($ot->getName());
		}
		asort($ordered_object_types, SORT_STRING);
		$select_options = array('<option value="" selected>'.lang('select one').'</option>');
		foreach ($ordered_object_types as $k => $v) {
			$select_options[] = '<option value="'.$k.'">'.$v.'</option>';
		}
		
		tpl_assign('object_types', $select_options);
		$custom_properties = array_var($_POST, 'custom_properties');
		$obj_type_id = array_var($_POST, 'objectType');
		$delete = 0;
		if (is_array($custom_properties)) {
			try {
				DB::beginWork();
				foreach ($custom_properties as $id => $data) {
					$new_cp = null;
					if($data['id'] != ''){
						$new_cp = CustomProperties::getCustomProperty($data['id']);
					}
					if ($new_cp == null) {
						$new_cp = new CustomProperty();
					}
					
					if($data['deleted'] == "1"){
						$delete = $delete +1;
						if (!$new_cp->isNew()) {
							$new_cp->delete();
						}
						continue;
					}
					$new_cp->setObjectTypeId($obj_type_id);
					$new_cp->setName($data['name']);
					$new_cp->setType($data['type']);
					$new_cp->setDescription($data['description']);
					if ($data['type'] == 'list') {
						$values = array();
						$list = explode(",", $data['values']);
						foreach ($list as $l) {
							$values[] = trim($l);
						}
						$value = implode(",", $values);
						$new_cp->setValues($value);
					} else {
						$new_cp->setValues($data['values']);
					}
					if($data['type'] == 'boolean'){
						$new_cp->setDefaultValue(isset($data['default_value_boolean']));
					}else{
						$new_cp->setDefaultValue($data['default_value']);
					}
					$new_cp->setIsRequired(isset($data['required']));
					$new_cp->setIsMultipleValues(isset($data['multiple_values']));
					$new_cp->setOrder($data['order']-$delete);
					$new_cp->setVisibleByDefault(isset($data['visible_by_default']));
					$new_cp->save();
					
					if (is_array(array_var($data, 'applyto'))) {
						$applyto = array_var($data, 'applyto');
						foreach ($applyto as $co_type => $value) {
							if ($value == 'true') {
								if (!CustomPropertiesByCoType::findById(array('co_type_id' => $co_type, 'cp_id' => $new_cp->getId()))) {
									$obj = new CustomPropertyByCoType();
									$obj->setCoTypeId($co_type);
									$obj->setCpId($new_cp->getId());
									$obj->save();
								}
							} else {
								$obj = CustomPropertiesByCoType::findById(array('co_type_id' => $co_type, 'cp_id' => $new_cp->getId()));
								if ($obj) $obj->delete();
							}
						}
					}
				}
				DB::commit();
				flash_success(lang('custom properties updated'));
				ajx_current('back');
			} catch (Exception $ex) {
				DB::rollback();
				flash_error($ex->getMessage());
			}
		}
	} // custom_properties

	/**
	 * List groups
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function groups() {
		if(!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$groups = PermissionGroups::getNonRolePermissionGroups();
		$gr_lengths = array();
		foreach ($groups as $gr) {
			$count = ContactPermissionGroups::count("`permission_group_id` = ".$gr->getId());
			$gr_lengths[$gr->getId()] = $count;
		}
		tpl_assign('gr_lengths', $gr_lengths);
		tpl_assign('permission_groups', $groups);
	}

	/**
	 * Show configuration index page
	 *
	 * @param void
	 * @return null
	 */
	function configuration() {
		if(!can_manage_configuration(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		$this->addHelper('textile');
		tpl_assign('config_categories', ConfigCategories::getAll());
	} // configuration

	/**
	 * List all available administration tools
	 *
	 * @param void
	 * @return null
	 */
	function tools() {
		if(!can_manage_configuration(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		tpl_assign('tools', AdministrationTools::getAll());
	} // tools

	/**
	 * List all templates
	 *
	 * @param void
	 * @return null
	 */
	function task_templates() {
		if(!can_manage_templates(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		tpl_assign('task_templates', ProjectTasks::getAllTaskTemplates());
	} // tools



	/**
	 * Show upgrade page
	 *
	 * @param void
	 * @return null
	 */
	function upgrade() {
		if(!can_manage_configuration(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		$this->addHelper('textile');

		$version_feed = VersionChecker::check(true);
		if(!($version_feed instanceof VersionsFeed)) {
			flash_error(lang('error check for upgrade'));
			$this->redirectTo('administration', 'upgrade');
		} // if

		tpl_assign('versions_feed', $version_feed);
	} // upgrade

	function auto_upgrade() {
		$this->setLayout("dialog");
		
		$version_number = array_var($_GET, 'version');
		if (!$version_number) {
			flash_error(lang('error upgrade version must be specified'));
			return;
		}
		$versions_feed = VersionChecker::check(true);
		$versions = $versions_feed->getNewVersions(product_version());
		if (count($versions) <= 0) {
			flash_error(lang('error upgrade version not found', $version_number));
			return;
		}
		$zipurl = null;
		foreach ($versions as $version) {
			if ($version->getVersionNumber() == $version_number) {
				$zipurl = $version->getDownloadLinkByFormat("zip")->getUrl();
				break;
			}
		}
		@set_time_limit(0);
		if (!$zipurl) {
			flash_error(lang('error upgrade invalid zip url', $version_number));
			return;
		}
		$zipname = "fengoffice_" . str_replace(" ", "_", $version_number) . ".zip";
		try {
			$in = fopen($zipurl, "r");
			$zippath = "tmp/" . $zipname;
			$out = fopen($zippath, "w");
			fwrite($out, stream_get_contents($in));
			fclose($out);
			fclose($in);
			$zip = zip_open($zippath);
			if (!is_resource($zip)) {
				flash_error("error upgrade cannot open zip file");
				return;
			}
			while ($zip_entry  = zip_read($zip)) {
				$completePath = dirname(zip_entry_name($zip_entry));
				$completeName = zip_entry_name($zip_entry);
				$completePath = substr($completePath, strpos($completePath, "fengoffice") + strlen("fengoffice") + 1);
				$completeName = substr($completeName, strpos($completeName, "fengoffice") + strlen("fengoffice") + 1);
		
				@mkdir($completePath, 0777, true);
		
				if (zip_entry_open($zip, $zip_entry, "r")) {
					if ($fd = @fopen($completeName, 'w')) {
						fwrite($fd, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
						fclose($fd);
					} else {
						// Empty directory
						@mkdir($completeName, 0777);
					}
					zip_entry_close($zip_entry);
				}
			}
			zip_close($zip);
		} catch (Error $ex) {
			flash_error($ex->getMessage());
			return;
		}
		$this->redirectToUrl("public/upgrade/index.php?upgrade_to=" . urlencode($version_number));
	}
	
	
	// ---------------------------------------------------
	//  Tool implementations
	// ---------------------------------------------------

	/**
	 * Render and execute test mailer form
	 *
	 * @param void
	 * @return null
	 */
	function tool_test_email() {
		set_time_limit(0);
		if(!can_manage_configuration(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		$tool = AdministrationTools::getByName('test_mail_settings');
		if(!($tool instanceof AdministrationTool)) {
			flash_error(lang('administration tool dnx', 'test_mail_settings'));
			$this->redirectTo('administration', 'tools');
		} // if

		$test_mail_data = array_var($_POST, 'test_mail');

		tpl_assign('tool', $tool);
		tpl_assign('test_mail_data', $test_mail_data);

		if(is_array($test_mail_data)) {
			try {
				$recepient = trim(array_var($test_mail_data, 'recepient'));
				$message = trim(array_var($test_mail_data, 'message'));

				$errors = array();

				if($recepient == '') {
					$errors[] = lang('test mail recipient required');
				} else {
					if(!is_valid_email($recepient)) {
						$errors[] = lang('test mail recipient invalid format');
					} // if
				} // if

				if($message == '') {
					$errors[] = lang('test mail message required');
				} // if

				if(count($errors)) {
					throw new FormSubmissionErrors($errors);
				} // if
				$to = array($recepient);
				$success = Notifier::sendEmail($to, logged_user()->getEmailAddress(), lang('test mail message subject'), $message);
				if($success) {
					flash_success(lang('success test mail settings'));
				} else {
					flash_error(lang('error test mail settings'));
				} // if
				ajx_current("back");
			} catch(Exception $e) {
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // tool_test_email

	/**
	 * Send multiple emails using this simple tool
	 *
	 * @param void
	 * @return null
	 */
	function tool_mass_mailer() {
		flash_error(lang('no access permissions'));
		ajx_current("empty");
		return;
		/*
		if(!logged_user()->isCompanyAdmin(owner_company())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		$tool = AdministrationTools::getByName('mass_mailer');
		if(!($tool instanceof AdministrationTool)) {
			flash_error(lang('administration tool dnx', 'test_mail_settings'));
			$this->redirectTo('administration', 'tools');
		} // if

		$massmailer_data = array_var($_POST, 'massmailer');

		tpl_assign('tool', $tool);
		tpl_assign('grouped_users', Users::getGroupedByCompany());
		tpl_assign('massmailer_data', $massmailer_data);

		if(is_array($massmailer_data)) {
			try {
				$subject = trim(array_var($massmailer_data, 'subject'));
				$message = trim(array_var($massmailer_data, 'message'));

				$errors = array();

				if($subject == '') {
					$errors[] = lang('massmailer subject required');
				} // if

				if($message == '') {
					$errors[] = lang('massmailer message required');
				} // if

				$users = Users::getAll();
				$recepients = array();
				if(is_array($users)) {
					foreach($users as $user) {
						if(array_var($massmailer_data, 'user_' . $user->getId()) == 'checked') {
							$recepients[] = Notifier::prepareEmailAddress($user->getEmailAddress('user'), $user->getObjectName());
						} // if
					} // foreach
				} // if

				if(!count($recepients)) {
					$errors[] = lang('massmailer select recepients');
				} // if

				if(count($errors)) {
					throw new FormSubmissionErrors($errors);
				} // if

				if(Notifier::sendEmail($recepients, Notifier::prepareEmailAddress(logged_user()->getEmailAddress('user'), logged_user()->getObjectName()), $subject, $message)) {
					flash_success(lang('success massmail'));
				} else {
					flash_error(lang('error massmail'));
				} // if
				ajx_current("back");
			} catch(Exception $e) {
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
		*/
	} // tool_mass_mailer

	function cron_events() {
		if(!can_manage_configuration(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		$events = CronEvents::getUserEvents();
		tpl_assign("events", $events);
		$cron_events = array_var($_POST, 'cron_events');
		if (is_array($cron_events)) {
			try {
				DB::beginWork();
				foreach ($cron_events as $id => $data) {
					$event = CronEvents::findById($id);
					$date = getDateValue($data['date']);
					if ($date instanceof DateTimeValue) {
						$this->parseTime($data['time'], $hour, $minute);
						$date->add("m", $minute);
						$date->add("h", $hour);
						$date = new DateTimeValue($date->getTimestamp() - logged_user()->getTimezone() * 3600);
						$event->setDate($date);
					}
					$delay = $data['delay'];
					if (is_numeric($delay)) {
						$event->setDelay($delay);
					}
					$enabled = array_var($data, 'enabled') == 'checked';
					$event->setEnabled($enabled);
					$event->save();
				}
				DB::commit();
				flash_success(lang("success update cron events"));
				ajx_current("back");
			} catch (Exception $ex) {
				DB::rollback();
				flash_error($ex->getMessage());
			}
		}
	}

	/**
	 * Returns hour and minute in 24 hour format
	 *
	 * @param string $time_str
	 * @param int $hour
	 * @param int $minute
	 */
	private function parseTime($time_str, &$hour, &$minute) {
		$exp = explode(':', $time_str);
		$hour = $exp[0];
		$minute = $exp[1];
		if (str_ends_with($time_str, 'M')) {
			$exp = explode(' ', $minute);
			$minute = $exp[0];
			if ($exp[1] == 'PM' && $hour < 12) {
				$hour = ($hour + 12) % 24;
			}
			if ($exp[1] == 'AM' && $hour == 12) {
				$hour = 0;
			}
		}
	}
	
	
	function mail_accounts() {
		if (!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		if (Plugins::instance()->isActivePlugin('mail')) {
			//$my_accounts = MailAccounts::getMailAccountsByUser(logged_user());
			$all_accounts = MailAccounts::findAll();
		}
		//tpl_assign('my_accounts', $my_accounts);
		tpl_assign('all_accounts', $all_accounts);
	}

	
	function object_subtypes() {
		if(!logged_user()->isAdminGroup()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		$co_types = array();
		$managers = array("tasks" => "ProjectTasks");
		foreach ($managers as $title => $manager) {
			$co_types[$manager] = ProjectCoTypes::getObjectTypesByManager($manager);
		}
		
		tpl_assign('managers', $managers);
		tpl_assign('co_types', $co_types);
		
		$object_subtypes = array_var($_POST, 'subtypes');
		if (is_array($object_subtypes)) {
			try {
				DB::beginWork();
				foreach ($object_subtypes as $manager => $subtypes) {
					foreach ($subtypes as $subtype) {
						$type = ProjectCoTypes::findById(array_var($subtype, 'id', 0));
						if (!$type instanceof ProjectCoType) {
							$type = new ProjectCoType();
							$type->setObjectManager($manager);
						}
						if (!array_var($subtype, 'deleted')) {
							$type->setName(array_var($subtype, 'name', ''));
							$type->save();
						} else {
							eval('$man_instance = ' . $manager . "::instance();");
							if ($man_instance instanceof ProjectDataObjects && array_var($subtype, 'id', 0) > 0) {
								$objects = $man_instance->findAll(array('conditions' => "`object_subtype`=".array_var($subtype, 'id', 0)));
								if (is_array($objects)) {
									foreach ($objects as $obj) {
										if ($obj instanceof DataObject) {
											$obj->setColumnValue('object_subtype', 0);
											$obj->save();
										}
									}
								}
							}
							if ($type instanceof ProjectCoType) $type->delete();
						}
					}
				}
				DB::commit();
				flash_success(lang("success save object subtypes"));
				ajx_current("back");
			} catch (Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		}
	}
	
	
	
	
	/**
	 * Add/edit Dimension Members
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit_members() {
		if(!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		$dimensions = Dimensions::findAll(array('conditions' => '`is_manageable` = 1'));
		$members = array();
		
		$logged_user_pgs = implode(',', logged_user()->getPermissionGroupIds());
		
		foreach($dimensions as $dim) {
			//if ($dim->deniesAllForContact($logged_user_pgs)) continue;
			
			$allows_all = $dim->hasAllowAllForContact($logged_user_pgs);
			
			$root_members = Members::findAll(array('conditions' => array('`dimension_id`=? AND `parent_member_id`=0', $dim->getId()), 'order' => '`name` ASC'));
			
			foreach ($root_members as $mem) {
				if ($dim->getDefinesPermissions() && !$allows_all) {
					if (!$mem->canBeReadByContact($logged_user_pgs, logged_user())) continue;
				}
				$members[$dim->getId()][] = $mem;
				$members[$dim->getId()] = array_merge($members[$dim->getId()], $mem->getAllChildrenSorted());
			}
		}
		
		tpl_assign('members', $members);
		tpl_assign('dimensions', $dimensions);
	}

	function tabs() {
		if(!can_manage_configuration(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} 
		tpl_assign('tabs', TabPanels::instance()->findAll(array(
			"order"=>"ordering",
			"conditions" => "plugin_id is NULL OR plugin_id = 0 OR plugin_id IN (SELECT id FROM ".TABLE_PREFIX."plugins WHERE is_activated > 0 AND is_installed > 0)"
		)));
	}
	
	function tabs_submit() {
		ajx_current("empty");
		evt_add("tabs changed", null);
		if(!can_manage_configuration(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		foreach ($_POST['tabs'] as $id => $tab) {
			$ordering = (int) $tab['ordering'];
			$title = mysql_real_escape_string($tab['title']);
			$enabled = (array_var($tab, 'enabled') == "on") ? 1 : 0;
			
			if ($tp = TabPanels::instance()->findById($id)){
				$tp->setOrdering($ordering);
				$tp->setTitle($title);
				$tp->setEnabled($enabled);
				if ($enabled){
					$pg_id = logged_user()->getPermissionGroupId();
					if(!TabPanelPermissions::isModuleEnabled($tp->getId(), $pg_id)){					
						$tpp = new TabPanelPermission();
						$tpp->setPermissionGroupId($pg_id);
						$tpp->setTabPanelId($tp->getId());
						$tpp->save();
					}
				}
				$tp->save();
			}
			
		}
		
	}

	function documents() {
		if(!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
	}

	function documents_allow() {
		if(!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		tpl_assign('file_types', FileTypes::instance()->findAll());
	}

	function documents_allow_submit() {
		ajx_current("empty");
		if(!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		foreach ($_POST['file_types'] as $id => $extension) {
			$allow = ($extension['allow'] == "on") ? 1 : 0;
				
			if ($ft = FileTypes::instance()->findById($id)){
				$ft->setIsAllow($allow);
				$ft->save();
			}
		}
		flash_success(lang('success file extension'));
	}
	
	
	
	
	
	function dimension_options() {
		if(!can_manage_dimensions(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$enabled_dimensions = config_option('enabled_dimensions');
		
		// custom dimension name
		$dimensions = Dimensions::findAll(array('conditions' => 'id IN ('.implode(',', $enabled_dimensions).')'));
		
		$custom_dimension_names = array();
		foreach ($dimensions as $dim) {
			$cdim_name = $dim->getOptionValue('custom_dimension_name');
			if (is_null($cdim_name)) {
				$cdim_name = "";
			}
			$custom_dimension_names[$dim->getId()] = $cdim_name;
		}
		
		// enabled dimension object types
		$dots = DimensionObjectTypes::findAll(array('conditions' => 'dimension_id IN ('.implode(',', $enabled_dimensions).')'));
		
		tpl_assign('custom_dimension_names', $custom_dimension_names);
		tpl_assign('dimension_ots', $dots);
	}
	
	function dimension_options_submit() {
		ajx_current("empty");
		if(!can_manage_dimensions(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		}
		
		$folder_ots = ObjectTypes::findAll(array('id' => true, 'conditions' => "name IN ('folder','project_folder','customer_folder')"));
		
		$data = array_var($_POST, 'enabled_dots');
		$names_data = array_var($_POST, 'custom_names');
		if (is_array($data) || is_array($names_data)) {
			try {
				DB::beginWork();
				
				// enabled dimension object types
				foreach ($data as $dim_id => $ots_info) {
					foreach ($ots_info as $ot_id => $enabled) {
						DB::execute("UPDATE ".TABLE_PREFIX."dimension_object_types SET enabled=".DB::escape($enabled)." 
							WHERE dimension_id=".DB::escape($dim_id)." AND object_type_id=".DB::escape($ot_id));
						
						// if object type is folder, set the same configuration to all folder types
						if (in_array($ot_id, $folder_ots)) {
							DB::execute("UPDATE ".TABLE_PREFIX."dimension_object_types SET enabled=".DB::escape($enabled)."
								WHERE dimension_id=".DB::escape($dim_id)." AND object_type_id IN (".implode(',', $folder_ots).")");
						}
					}
				}
				
				// custom dimension names
				foreach ($names_data as $dim_id => $custom_name) {
					$cname = trim($custom_name);
					$dimension = Dimensions::getDimensionById($dim_id);
					if ($dimension instanceof Dimension) {
						$dimension->setOptionValue('custom_dimension_name', $cname);
					}
				}
				
				DB::commit();
				flash_success(lang("success edit dimension options"));
				evt_add('tabs changed');
				//ajx_current("back");
				
			} catch (Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		}
	}

} 
