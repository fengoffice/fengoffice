<?php

define('ADMIN_SESSION_TIMEOUT', 3600);

/**
 * Administration controller
 *
 * @version 3.7
 * @author Ilija Studen <ilija.studen@gmail.com>
 * @author Feng Office 
 * 
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
	 * List object types for custom properties
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
		
		$ot_types = array('content_object');
		Hook::fire("administration_custom_properties_ot_types", array(), $ot_types);
		$ot_types_str = implode("','", $ot_types);
		
		$object_types = array();
		$ordered_object_types = array();
		// get all object types, exclude object types of disabled plugins
		$object_types_tmp = ObjectTypes::instance()->findAll(array(
			"conditions" => "`type` IN ('$ot_types_str') 
				AND IF(plugin_id IS NULL OR plugin_id=0, true, (SELECT p.is_activated FROM ".TABLE_PREFIX."plugins p WHERE p.id=plugin_id) = true)
				AND `name` <> 'template_task' AND name <> 'template_milestone' AND `name` <> 'file revision'", 
			"order" => "name"	
		));
		foreach ($object_types_tmp as $ot) {
			$ordered_object_types[$ot->getId()] = $ot->getPluralObjectTypeName();
			$object_types[$ot->getId()] = $ot->getName();
		}
		asort($ordered_object_types, SORT_STRING);
		
		tpl_assign('object_types', $object_types);
		tpl_assign('ordered_object_types', $ordered_object_types);
		
	} // custom_properties
	
	function list_custom_properties_for_type() {
		if(!can_manage_configuration(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$object_type_id = get_id();
		$object_type = ObjectTypes::findById($object_type_id);
		if (!$object_type instanceof ObjectType) {
			flash_error(lang('object dnx'));
			ajx_current("empty");
			return;
		}
		
		$extra_params = array('extra_conditions' => '', 'form_title' => '', 'add_link_text' => '');
		Hook::fire('list_custom_properties_for_type_extra_parameters', array('request' => $_REQUEST, 'object_type' => $object_type), $extra_params);
		
		$extra_conditions = array_var($extra_params, 'extra_conditions');
		
		$custom_properties = CustomProperties::getAllCustomPropertiesByObjectType($object_type->getId(), 'all', $extra_conditions, true, true);
		
		tpl_assign('object_type', $object_type);
		tpl_assign('dont_fire_hook', array_var($_REQUEST, 'dont_fire_hook'));
		tpl_assign('extra_params', $extra_params);
		tpl_assign('custom_properties', $custom_properties);
		
	}
	
	function save_custom_properties_for_type() {
		ajx_current("empty");
		
		if(!can_manage_configuration(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		}
		
		$obj_type_id = array_var($_REQUEST, 'ot_id');
		$object_type = ObjectTypes::findById($obj_type_id);
		if (!$object_type instanceof ObjectType) {
			flash_error(lang('object dnx'));
			return;
		}
		
		$custom_properties_parameter = array_var($_POST, 'custom_properties');
		if (is_string($custom_properties_parameter)) {
			$custom_properties = json_decode($custom_properties_parameter, true);
		} else {
			$custom_properties = $custom_properties_parameter;
		}
		
		if (is_array($custom_properties)) {
		  try {
			DB::beginWork();
			
			foreach ($custom_properties as $order => $data) {
				$new_cp = null;
				$data = (array) $data; //needs to be array, apparently
				if($data['id'] != '') {
					if (is_numeric($data['id'])) {
						$new_cp = CustomProperties::getCustomProperty($data['id']);
					} else {
						continue;
					}
				}
				
				// don't modify properties of other object types
				$is_cp_from_other_ot = false;
				if ($new_cp instanceof CustomProperty && $new_cp->getObjectTypeId() != $obj_type_id) {
					$is_cp_from_other_ot = true;
				}
				if ($new_cp == null) {
					$new_cp = new CustomProperty();
				}
			
				if($data['deleted'] == "1"){
					if (!$new_cp->isNew()) {
						$new_cp->delete();
					}
					continue;
				}
                                if($data['is_disabled'] == "1"){
                                        $new_cp->setIsDisabled(1);
                                        $new_cp->save();
					//continue;
				}
				
				if (array_var($data, 'name') == '') {
					if (array_var($data, 'id') == 0) {
						continue;
					} else {
						throw new Exception(lang('custom property name empty'));
					}
				}
				
				if (array_var($data, 'type') == 'boolean') {
					$data['default_value'] = array_var($data, 'default_value_bool');
				}

				if (array_var($data, 'type') == 'numeric') {
					if ($data['default_value'] != '' && !is_numeric($data['default_value'])) {
						throw new Exception(lang('default value must be numeric', $data['name']));
					}
				}
				
				if (!$is_cp_from_other_ot) {
					$new_cp->setFromAttributes($data);
					$new_cp->setObjectTypeId($obj_type_id);
					$new_cp->setOrder($order);
					
					if ($data['type'] == 'list' || $data['type'] == 'table') {
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
					
					$new_cp->save();
				}
				
				
				$ret = null;
				Hook::fire('after_custom_property_save', array('ot_id' => $obj_type_id, 'cp' => $new_cp, 'data' => $data, 'order' => $order), $ret);
			}
			
			DB::commit();
			flash_success(lang('custom properties updated'));
			ajx_current("back");
			
			evt_add("reload custom property definition", array('ot' => $object_type->getArrayInfo(array('id','name'))));
			
		  } catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		  }
			
		}
	}
	

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
						$date = new DateTimeValue($date->getTimestamp() - logged_user()->getUserTimezoneValue());
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
							if ($man_instance instanceof ContentDataObjects && array_var($subtype, 'id', 0) > 0) {
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
			
			//Replaced for PHP7 (Note by @Conrado: I don't know if it will work yet)
			$title = DB::escape($tab['title']);
			
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
	
	

	


	function timezones() {
		if(!can_manage_configuration(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$countries = Countries::getAll();
		$grouped_time_zones = Timezones::getAllTimezonesGroupedByCountry();
		
		tpl_assign('countries', $countries);
		tpl_assign('grouped_time_zones', $grouped_time_zones);
	}
	
	
	function timezones_submit() {
		ajx_current("empty");
		
		$default_timezone = array_var($_REQUEST, 'default_timezone');
		
		$data = array_var($_REQUEST, 'timezones');
		$with_dst = array();
		$without_dst = array();
		
		foreach ($data as $zone_id => $options) {
			$using_dst = array_var($options, 'using_dst');
			if ($using_dst == 1) {
				$with_dst[] = $zone_id;
			} else {
				$without_dst[] = $zone_id;
			}
		}
		
		try {
			DB::beginWork();
			
			set_config_option('default_timezone', $default_timezone);
			
			if (count($with_dst) > 0) {
				DB::execute("
					UPDATE ".TABLE_PREFIX."timezones SET using_dst=1 WHERE id IN (".implode(',', $with_dst).");
				");
			}
			
			if (count($without_dst) > 0) {
				DB::execute("
					UPDATE ".TABLE_PREFIX."timezones SET using_dst=0 WHERE id IN (".implode(',', $without_dst).");
				");
			}
			
			DB::commit();

			flash_success(lang('timezone options edited successfully'));
			ajx_current("back");
		
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
			ajx_current("empty");
		}
		
	}
} 
