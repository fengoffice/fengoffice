<?php

class TemplateController extends ApplicationController {
	
	var $dbDateFormat = "Y-m-d" ;

	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	}

	function index() {
		if (!can_manage_templates(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		
		$templates=COTemplates::instance()->findAll();
		tpl_assign('templates', $templates);
	}
	
	
	private function verify_repetitive_tasks_have_date_params(COTemplate $template) {
		
		$tasks_with_missing_properties = array();
		
		$template_tasks = TemplateTasks::findAll(array(
			"conditions" => "(repeat_forever > 0 OR repeat_num > 0 OR repeat_end > 0) AND template_id=".$template->getId()
		));
		
		foreach ($template_tasks as $template_task) {
			/* @var $template_task TemplateTask */
			$repeat_by = $template_task->getRepeatBy(); // due_date or start_date
			$template_obj_prop = TemplateObjectProperties::findOne(array(
				"conditions" => array("template_id=? AND object_id=? AND property=?", $template->getId(), $template_task->getId(), $repeat_by)
			));
			
			if (!$template_obj_prop instanceof TemplateObjectProperty) {
				$tasks_with_missing_properties[] = $template_task;
			}
		}
		
		return $tasks_with_missing_properties;
	}
	
	function remove_repetition_from_inconsistent_template_tasks() {
		$template_id = array_var($_REQUEST, 'template_id');
		$template = COTemplates::findById($template_id);
		if ($template instanceof COTemplate) {
			$tasks_with_missing_properties = $this->verify_repetitive_tasks_have_date_params($template);
			if (count($tasks_with_missing_properties) > 0) {
				try {
					DB::beginWork();
					
					foreach ($tasks_with_missing_properties as $t) {
						/* @var $t TemplateTask */
						$t->setRepeatForever(0);
						$t->setRepeatNum(0);
						$t->setRepeatEnd(0);
						$t->setRepeatD(0);
						$t->setRepeatM(0);
						$t->setRepeatY(0);
						$t->setRepeatBy('');
						$t->save();
					}
					
					DB::commit();
					flash_success('repetition removed from tasks successfully');
					ajx_current("back");
					
				} catch (Exception $e) {
					DB::rollback();
					flash_error($e->getMessage());
					ajx_current("empty");
				}
			}
		}
	}
	

	function add() {
		if (!can_manage_templates(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		$template = new COTemplate();
		$template_data = array_var($_POST, 'template');
		if (!is_array($template_data)) {
			$template_data = array(
				'name' => '',
				'description' => ''
				);
			
			//delete old temporaly template tasks
			self::deleteOldTemporalyTemplateObj();
			
		} else {
			//No le agrego miembros
// 			$member_ids = json_decode(array_var($_POST, 'members'));
// 			$context = active_context();
// 			$member_ids = array();
// 			foreach ($context as $selection) {
// 				if ($selection instanceof Member) $member_ids[] = $selection->getId();
// 			}
// 			if(count($selected_members)==0){
// 				$member_ids=$object->getMemberIds();
// 			}
// 			$controller->add_to_members($copy, $selected_members);
			$cotemplate = new COTemplate();
			$cotemplate->setFromAttributes($template_data);
			$object_ids = array();
			try {
				$decoded_prop_inputs = json_decode($_POST['all_prop_inputs'], true);
				
				DB::beginWork();
				$cotemplate->save();
				
				$objects = $this->get_prop_input_decoded($decoded_prop_inputs, 'objects');
				
				if(!empty($objects)){
					foreach ($objects as $objid) {
						$object = Objects::findObject($objid);
						$additional_attributes = array();
						if ($object instanceof ProjectTask) {
							$add_attr_milestones = array_var($_POST, "milestones");
							if (is_array($add_attr_milestones)) $additional_attributes['milestone'] = array_var($add_attr_milestones, $objid);
						}
						$oid = $cotemplate->addObject($object, $additional_attributes);
						if ($oid) $object_ids[$objid] = $oid;
	// 					COTemplates::validateObjectContext($object, $member_ids);
					}
				}
				
				$objectPropertyValues = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValues');
				$propValueParams = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValueParam');
				$propValueOperation = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValueOperation');
				$propValueAmount = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValueAmount');
				$propValueUnit = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValueUnit');
				$propValueVarUnit = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValueVarUnit');
				$propValueTime = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValueTime');
				
				$propValueNumType = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValueNumType');
				$propValueNumOp = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValueNumOp');
				$propValueNumConst = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValueNumConst');
				
				if (is_array($objectPropertyValues)) {
					foreach($objectPropertyValues as $objInfo => $propertyValues){
						foreach($propertyValues as $property => $value){
							

							
							$split = explode(":", $objInfo);
							$object_id = $split[1];
							$templateObjPropValue = new TemplateObjectProperty();
							$templateObjPropValue->setTemplateId($cotemplate->getId());
							$templateObjPropValue->setObjectId($object_ids[$objInfo]);
							//$templateObjPropValue->setObjectManager($split[0]);
							$templateObjPropValue->setProperty($property);
							$propValue = '';
							if(isset($propValueParams[$objInfo][$property])){

								$propValue = null;
								Hook::fire('override_template_param_definition', array('controller'=>$this, 'obj_id'=>$objInfo, 'property'=>$property, 'decoded_prop_inputs'=>$decoded_prop_inputs), $propValue);
								
								if (is_null($propValue)) {
									// if the hook didn't override the definition of the parameter then continue with the usual procedure

									$param = trim($propValueParams[$objInfo][$property]);
									$operation = trim($propValueOperation[$objInfo][$property]);
									$amount = trim($propValueAmount[$objInfo][$property]);
									$unit = trim($propValueUnit[$objInfo][$property]);
									$var_unit = trim(array_var($propValueVarUnit[$objInfo], $property));

									$amount_type = trim(array_var($propValueNumType[$objInfo], $property));
									if ($amount_type != '' && $amount_type != 'fixed') {
										$amount_op = trim(array_var($propValueNumOp[$objInfo], $property));
										$amount_const = trim(array_var($propValueNumConst[$objInfo], $property));

										$amount = '{'.$amount_type.'}'.$amount_op.$amount_const;
									}

									$propValue = '{'.$param.'}'.$var_unit.$operation.$amount.$unit;
									
									if (isset($propValueTime[$objInfo])) {
										$time = array_var($propValueTime[$objInfo], $property);
										if ($param == 'task_creation' && config_option('use_time_in_task_dates')) {
											$tval = getTimeValue($time);
											if (is_array($tval)) {
												$propValue .= "|".str_pad($tval['hours'], 2, '0', STR_PAD_LEFT).":".str_pad($tval['mins'], 2, '0', STR_PAD_LEFT);
											}
										}
									}
								}
							}else{
								if(is_array($value)){
									$propValue = $value[0];
								}else{
									$propValue = $value;
								}
							}
							$templateObjPropValue->setValue($propValue);
							$templateObjPropValue->save();
						}
					}
				}
				$parameters = array_var($_POST, 'parameters');
				if (is_array($parameters)) {
					foreach($parameters as $parameter){
						$newTemplateParameter = new TemplateParameter();
						$newTemplateParameter->setTemplateId($cotemplate->getId());
						$newTemplateParameter->setName($parameter['name']);
						$newTemplateParameter->setType($parameter['type']);
						$newTemplateParameter->setDefaultValue(array_var($parameter, 'default_value'));
						
						Hook::fire('template_parameter_default_value', array('parameter' => $parameter), $newTemplateParameter);
						
						$newTemplateParameter->save();
					}
				}
								
// 				$object_controller = new ObjectController();
// 				$object_controller->add_to_members($cotemplate, $member_ids);
				
//				evt_add('reload tab panel', 'tasks-panel');
				
				DB::commit();
				
				$tasks_with_missing_properties = $this->verify_repetitive_tasks_have_date_params($cotemplate);
				if (count($tasks_with_missing_properties) > 0) {
					ajx_current("empty");
					$tasks_array = array();
					foreach ($tasks_with_missing_properties as $t) {
						$tasks_array[] = array('id' => $t->getId(), 'name' => $t->getName());
					}
					evt_add("ask user to fix template repetitive tasks", array('template_id' => $cotemplate->getId(), 'tasks' => $tasks_array));
				} else {
					ApplicationLogs::createLog($cotemplate, ApplicationLogs::ACTION_ADD);
					flash_success(lang("success add template"));
					if (array_var($_POST, "add_to")) {
						ajx_current("start");
					} else {
						ajx_current("back");
					}
				}
			} catch (Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		}
		$objects = array();
		if (array_var($_GET, 'id')) {
			/*	TODO: Feng 2 
		  	$object = Objects::findObject(array_var($_GET, 'id'));
			if ($object instanceof ProjectDataObject) {
				$objects[] = $object;
				tpl_assign('add_to', true);
			}
			*/
		}
		tpl_assign('objects', $objects);
		tpl_assign('cotemplate', $template);
		tpl_assign('template_data', $template_data);
	}

	/**
	 * Add template objects to the view
	 * @param template_id
	 * @return array
	 */
	function add_template_object_to_view($template_id) {
		$objects = array();
		$template_objects_cond = " AND EXISTS (SELECT * FROM ".TABLE_PREFIX."template_objects tobj WHERE tobj.object_id=o.id AND tobj.template_id='$template_id')";
		$tasks_conditions = array('conditions' => '`template_id` = '.$template_id . $template_objects_cond,  "order" => "depth,name");
		$milestones_conditions = array('conditions' => '`template_id` = '.$template_id . $template_objects_cond,  "order" => "name");
		$tasks = TemplateTasks::findAll($tasks_conditions);			
		$milestones = TemplateMilestones::findAll($milestones_conditions);	
				
		foreach ($milestones as $milestone){
			$objectId = $milestone->getObjectId();
			$id = $milestone->getId();
			$objectTypeName = $milestone->getObjectTypeName();
			$objectName = $milestone->getObjectName();
			$manager = get_class($milestone->manager());
			$ico = "ico-milestone";
			$action = "add";
			$objects[] = $this->prepareObject($objectId, $id, $objectName, $objectTypeName, $manager, $action,null, null, null, $ico, $milestone->getObjectTypeId());
		}
		
		foreach ($tasks as $task){
			$objectId = $task->getObjectId();
			$id = $task->getId();
			$objectTypeName = $task->getObjectTypeName();
			$objectName = $task->getObjectName();
			$manager = get_class($task->manager());
			$milestoneId = $task instanceof TemplateTask ? $task->getMilestoneId() : '0';
			$subTasks = $task->getSubTasks();
			$parentId = $task->getParentId();
			$ico = "ico-task";
			$action = "add";
			$objects[] = $this->prepareObject($objectId, $id, $objectName, $objectTypeName, $manager, $action,$milestoneId, $subTasks, $parentId, $ico, $task->getObjectTypeId(), $task->isRepetitive());
		}
		
		return $objects;
	}
		
	function prepareObject($objectId, $id, $objectName, $objectTypeName, $manager, $action,$milestoneId = null , $subTasks = null, $parentId = null, $ico = null, $objectTypeId=0, $is_repetitive=0) {
		$object = array(
				"object_id" => $objectId,
				"object_type_id" => $objectTypeId,
				"type" => $objectTypeName,
				"id" => $id,
				"name" => $objectName,
				"manager" => $manager,
				"milestone_id" => $milestoneId,
				"sub_tasks" => $subTasks,
				"ico" => $ico,
				"parent_id" => $parentId,
				"action" => $action,
				"is_repetitive" => $is_repetitive ? "1" : "0",
		);
			
		return $object;
	}
	
	//delete old temporaly template tasks
	private function deleteOldTemporalyTemplateObj(){
		//delete Dependencies
		$temp_tasks = TemplateTasks::getAllTaskTemplatesBySessionId(logged_user()->getId());
		foreach ($temp_tasks as $tmp){
			$id = $tmp->getId();
			$dep = ProjectTaskDependencies::findOne(array('conditions' => "(`previous_task_id` = $id OR `task_id` = $id )"));
			if ($dep instanceof ProjectTaskDependency) {
				$dep->delete();				
			} 
		}
		
		//delete obj
		$conditions = array('conditions' => '`session_id` =  '.logged_user()->getId());
		if(logged_user()->getId() > 0){
			TemplateTasks::delete($conditions);
			TemplateMilestones::delete($conditions);
		}
	}
	
	
	function get_prop_input_decoded($decoded_prop_inputs, $prefix) {
		$decoded_var = array();
		
		foreach ($decoded_prop_inputs as $key => $value) {
			if (str_starts_with($key, $prefix)) {
				preg_match_all("/\[(.*?)\]/", $key, $matches);
				$subkeys = $matches[1];
				if (!isset($decoded_var[$subkeys[0]])) {
					$decoded_var[$subkeys[0]] = array();
				}
				if (isset($subkeys[1])) {
					if (isset($subkeys[2])) {
						if (isset($subkeys[3])) {
							$decoded_var[$subkeys[0]][$subkeys[1]][$subkeys[2]][$subkeys[3]] = $value;
						} else {
							$decoded_var[$subkeys[0]][$subkeys[1]][$subkeys[2]] = $value;
						}
					} else {
						$decoded_var[$subkeys[0]][$subkeys[1]] = $value;
					}
				} else {
					$decoded_var[$subkeys[0]] = $value;
				}
			}
		}
		
		return $decoded_var;
	}
	
	function edit() {
		if (!can_manage_templates(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add');

		$cotemplate = COTemplates::findById(get_id());
		if(!($cotemplate instanceof COTemplate)) {
			flash_error(lang('template dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$cotemplate->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$template_data = array_var($_POST, 'template');
		$object_properties = array();
		if(!is_array($template_data)) {
			$template_data = array(
				'name' => $cotemplate->getObjectName(),
				'description' => $cotemplate->getDescription(),
			); // array
			foreach($cotemplate->getObjects() as $obj){
			    
			    $object_template_properties = TemplateObjectProperties::getPropertiesByTemplateObject(get_id(), $obj->getObjectId());
			        
			    $object_properties[$obj->getObjectId()] = $object_template_properties;
			}
			
			
			//delete old temporaly template tasks
			self::deleteOldTemporalyTemplateObj();
		} else {
			$cotemplate->setFromAttributes($template_data);
			try {
				$decoded_prop_inputs = json_decode($_POST['all_prop_inputs'], true);
				
				$member_ids = json_decode(array_var($_POST, 'members'));
				DB::beginWork();
				$tmp_objects = $cotemplate->getObjects();
				$cotemplate->removeObjects();
				$cotemplate->save();
				
				$objects = $this->get_prop_input_decoded($decoded_prop_inputs, 'objects');
				
				foreach ($objects as $objid) {
					
					$object = Objects::findObject($objid);
					
					$additional_attributes = array();
					if ($object instanceof ProjectTask) {
						$add_attr_milestones = array_var($_POST, "milestones");
						if (is_array($add_attr_milestones)) $additional_attributes['milestone'] = array_var($add_attr_milestones, $objid);
					}
					$oid = $cotemplate->addObject($object, $additional_attributes);
					if ($oid) $object_ids[$objid] = $oid;
				}

				TemplateObjectProperties::deletePropertiesByTemplate(get_id());
				
				$objectPropertyValues = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValues');
				$propValueParams = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValueParam');
				$propValueOperation = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValueOperation');
				$propValueAmount = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValueAmount');
				$propValueUnit = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValueUnit');
				$propValueVarUnit = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValueVarUnit');
				$propValueTime = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValueTime');
				
				$propValueNumType = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValueNumType');
				$propValueNumOp = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValueNumOp');
				$propValueNumConst = $this->get_prop_input_decoded($decoded_prop_inputs, 'propValueNumConst');
				
				if (is_array($objectPropertyValues)) {
					foreach($objectPropertyValues as $objInfo => $propertyValues){
						foreach($propertyValues as $property => $value){

							if (!isset($object_ids[$objInfo])) $object_ids[$objInfo] = $objInfo;
										
							$split = explode(":", $objInfo);
							$templateObjPropValue = new TemplateObjectProperty();
							$templateObjPropValue->setTemplateId($cotemplate->getId());
							$templateObjPropValue->setObjectId($object_ids[$objInfo]);
							$templateObjPropValue->setProperty($property);
							$propValue = '';
							if(isset($propValueParams[$objInfo][$property])){

								$propValue = null;
								Hook::fire('override_template_param_definition', array('controller'=>$this, 'obj_id'=>$objInfo, 'property'=>$property, 'decoded_prop_inputs'=>$decoded_prop_inputs), $propValue);
								
								if (is_null($propValue)) {
									// if the hook didn't override the definition of the parameter then continue with the usual procedure
									
									$param = trim($propValueParams[$objInfo][$property]);
									$operation = trim(array_var($propValueOperation[$objInfo], $property));
									$amount = trim(array_var($propValueAmount[$objInfo], $property));
									$unit = trim(array_var($propValueUnit[$objInfo], $property));
									$var_unit = trim(array_var($propValueVarUnit[$objInfo], $property));

									$amount_type = trim(array_var($propValueNumType[$objInfo], $property));
									if ($amount_type != '' && $amount_type != 'fixed') {
										$amount_op = trim(array_var($propValueNumOp[$objInfo], $property));
										$amount_const = trim(array_var($propValueNumConst[$objInfo], $property));

										$amount = '{'.$amount_type.'}'.$amount_op.$amount_const;
									}

									$propValue = '{'.$param.'}'.$var_unit.$operation.$amount.$unit;
									
									if (isset($propValueTime[$objInfo])) {
										$time = array_var($propValueTime[$objInfo], $property);
										if ($param == 'task_creation' && config_option('use_time_in_task_dates')) {
											$tval = getTimeValue($time);
											if (is_array($tval)) {
												$propValue .= "|".str_pad($tval['hours'], 2, '0', STR_PAD_LEFT).":".str_pad($tval['mins'], 2, '0', STR_PAD_LEFT);
											}
										}
									}
								}
							}else{
								if(is_array($value)){
									$propValue = $value[0];
								}else{
									$propValue = $value;
								}
							}
							$templateObjPropValue->setValue($propValue);
							$templateObjPropValue->save();
						}
					}
				}
				TemplateParameters::deleteParametersByTemplate(get_id());
				$parameters = array_var($_POST, 'parameters');
				if (is_array($parameters)) {
					foreach($parameters as $parameter){
						$newTemplateParameter = new TemplateParameter();
						$newTemplateParameter->setTemplateId($cotemplate->getId());
						$newTemplateParameter->setName(rtrim($parameter['name'], " "));
						$newTemplateParameter->setType($parameter['type']);
						$newTemplateParameter->setDefaultValue(array_var($parameter, 'default_value'));
						
						Hook::fire('template_parameter_default_value', array('parameter' => $parameter), $newTemplateParameter);
						
						$newTemplateParameter->save();
					}
				}
				
				//delete permanently objs
				foreach ($tmp_objects as $obj){
					if(is_null($objects)){
						$objects = array();
					}
					if(!in_array($obj->getId(), $objects)){
						$obj->delete();
					}
				}
								
				DB::commit();
				
				$tasks_with_missing_properties = $this->verify_repetitive_tasks_have_date_params($cotemplate);
				if (count($tasks_with_missing_properties) > 0) {
					ajx_current("empty");
					$tasks_array = array();
					foreach ($tasks_with_missing_properties as $t) {
						$tasks_array[] = array('id' => $t->getId(), 'name' => $t->getName());
					}
					evt_add("ask user to fix template repetitive tasks", array('template_id' => $cotemplate->getId(), 'tasks' => $tasks_array));
				} else {
				
					ApplicationLogs::createLog($cotemplate, ApplicationLogs::ACTION_EDIT);
					flash_success(lang("success edit template"));
					ajx_current("back");
				}
			} catch (Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("back");
			}
		}
				
		$objects = $this->add_template_object_to_view($cotemplate->getId());
		
		tpl_assign('object_properties', $object_properties);
		tpl_assign('parameters', TemplateParameters::getParametersByTemplate(get_id()));
		tpl_assign('objects', $objects);
		tpl_assign('cotemplate', $cotemplate);
		tpl_assign('template_data', $template_data);
	}

	function view() {
		if (!can_manage_templates(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		$cotemplate = COTemplates::findById(get_id());
		if(!($cotemplate instanceof COTemplate)) {
			flash_error(lang('template dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$cotemplate->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		tpl_assign('cotemplate', $cotemplate);
		ajx_set_no_toolbar(true);
		ApplicationReadLogs::createLog($cotemplate, ApplicationReadLogs::ACTION_READ);
		
	}

	function delete() {
		if (!can_manage_templates(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$cotemplate = COTemplates::findById(get_id());
		if(!($cotemplate instanceof COTemplate)) {
			flash_error(lang('template dnx'));
			return;
		} // if

		if(!$cotemplate->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if

		try {
			DB::beginWork();
			$cotemplate->delete();
			ApplicationLogs::createLog($cotemplate, ApplicationLogs::ACTION_DELETE);
			DB::commit();
			flash_success(lang('success delete template', $cotemplate->getObjectName()));
			if (array_var($_GET, 'popup', false)) {
				ajx_current("reload");
			} else {
				ajx_current("back");
			}
		} catch(Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		} // try
	}

	function add_to() {
		if (!can_manage_templates(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		$manager = array_var($_GET, 'manager');
		$id = get_id('id',$_REQUEST);
		
		$template_data = array_var($_POST, 'add_to_temp');
		
		$object = Objects::findObject($id);
		
		if (is_array($template_data)) {
			$template_id =  array_var($template_data, 'template');
			$go_deep = array_var($template_data, 'copy-tasks',false);
						
			$template = COTemplates::findById($template_id);
			if ($template instanceof COTemplate) {
				try {
					DB::beginWork();
					$template->addObject($object, null, $go_deep);
					DB::commit();
					flash_success(lang('success add object to template'));
					ajx_current("start");
				} catch(Exception $e) {
					DB::rollback();
					flash_error($e->getMessage());
				}
			}
		}
		tpl_assign('templates', COTemplates::findAll());
		tpl_assign("object", $object);
	}
	

	function template_parameters(){
		if (!can_instantiate_templates(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		$id = get_id();
		$parameters = TemplateParameters::getParametersByTemplate($id);
		ajx_current("empty");
		ajx_extra_data(array('parameters' => $parameters));
	}
	
	
	
	function save_instantiated_parameters($template, $parameters, $parameterValues) {
		$instantiation_id = config_option('last_template_instantiation_id') + 1;
		
		foreach ($parameters as $param) {
			/* @var $param TemplateParameter */
			$param_val = array_var($parameterValues, $param->getName(), '');
			
			Hook::fire('before_saving_instantiated_template_param', array('param' => $param, 'template' => $template, 'inst_id' => $instantiation_id), $param_val);
			$param_val = escape_character($param_val);
			
			DB::execute("INSERT INTO `".TABLE_PREFIX."template_instantiated_parameters` (`template_id`, `instantiation_id`, `parameter_name`, `value`) VALUES
					('".$template->getId()."', '$instantiation_id', '".escape_character($param->getName())."', ".DB::escape($param_val).") ON DUPLICATE KEY UPDATE template_id=template_id");
		}
		
		set_config_option('last_template_instantiation_id', $instantiation_id);
		return $instantiation_id;
	}
	
	
	function instantiate($arguments = null) {
		// let the process finish if the connection is lost
		set_time_limit(0);
		ini_set("memory_limit", "512M");
		// -------------

		if (!can_instantiate_templates(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}

		$selected_members = array();
		$id = array_var($arguments, 'id', get_id());
	
		
		$template = COTemplates::findById($id);
		if (!$template instanceof COTemplate) {
			flash_error(lang("template dnx"));
			ajx_current("empty");
			return;
		}
		$parameters = TemplateParameters::getParametersByTemplate($id);
		$parameterValues = array_var($arguments, 'parameterValues', array_var($_POST, 'parameterValues'));
		if(count($parameters) > 0 && !isset($parameterValues)) {
			ajx_current("back");
			return;
		}
		
		$instantiation_id = $this->save_instantiated_parameters($template, $parameters, $parameterValues);
		
		if(array_var($_REQUEST, 'members') || array_var($arguments, 'members')){
			$selected_members = array_var($arguments, 'members', json_decode(array_var($_REQUEST, 'members')));
		}else{
			$context = active_context();
			
			foreach ($context as $selection) {
				if ($selection instanceof Member) $selected_members[] = $selection->getId();
			}
		}
		if (array_var($_REQUEST, 'additional_member_ids')) {
			$add_mem_ids = json_decode(array_var($_REQUEST, 'additional_member_ids'));
			if (is_array($add_mem_ids)) {
				foreach ($add_mem_ids as $add_mem_id) {
					if (is_numeric($add_mem_id)) $selected_members[] = $add_mem_id;
				}
			}
		}

		//Linked objects
		if (array_var($_REQUEST, 'linked_objects')) {
			$linked_objects_ids = json_decode(array_var($_REQUEST, 'linked_objects'));
			$linked_objects = array();
			if (is_array($linked_objects_ids)) {
				foreach ($linked_objects_ids as $linked_object_id) {
					$linked_object = Objects::findObject($linked_object_id);
					if ($linked_object instanceof ApplicationDataObject) {
						$linked_objects[] = $linked_object;
					}
				}
			}
		}

		
		$objects = $template->getObjects() ;
		$controller  = new ObjectController();
/*		if (count($selected_members) > 0) {
			$selected_members_instances = Members::findAll(array('conditions' => 'id IN ('.implode($selected_members).')'));
		} else {
			$selected_members_instances = array();
		}
*/		
		DB::beginWork();
		
		$active_context = active_context();
		$copies = array();
		$copiesIds = array();
		$dependencies = array();
		
		Hook::fire("verify_objects_before_template_instantiation", $template, $objects);
		
		// sort tasks by depth, so first level task are instantiated first, and subtasks can find their parent
		$tmp_sorted_objects = array();
		foreach ($objects as $o) {
			$d = $o instanceof TemplateTask ? $o->getDepth() : "0";
			$tmp_sorted_objects[$d."-".$o->getId()] = $o;
		}
		ksort($tmp_sorted_objects);
		$objects = $tmp_sorted_objects;
		// --
		
		foreach ($objects as $object) {
			if (!$object instanceof ContentDataObject) continue;
			// copy object
			if ($object instanceof TemplateTask) {
				
				//dependencies
				$ptasks = ProjectTaskDependencies::getDependenciesForTemplateTask($object->getId());
				if(!empty( $ptasks )){
					foreach ($ptasks as $d) {
						$dependencies[] = $d;
					}											
				}
											
				$copy = $object->copyToProjectTask($instantiation_id);
			}else if ($object instanceof TemplateMilestone) {
				$copy = $object->copyToProjectMilestone();
							
			}else{
				$copy = $object->copy(false);
				if ($copy->columnExists('from_template_id')) {
					$copy->setColumnValue('from_template_id', $object->getId());
				
				}
			}
			if ($copy->columnExists('is_template')) {
				$copy->setColumnValue('is_template', false);
			}

			// set flag to skip calculations in this step, they will be done later
			$copy->dont_calculate_financials = true;
			$copy->dont_calculate_project_financials = true;
						
			$copy->save();
			$copies[] = $copy;
			$copiesIds[$object->getId()] = $copy->getId();
			
			$ret = null;
			Hook::fire('after_template_object_instantiation', array('template' => $template, 'original' => $object, 'copy' => $copy), $ret);
			
			
			/* Set instantiated object members:
			 * 		if no member is active then the instantiated object is put in the same members as the original
			 * 		if any members are selected then the instantiated object will be put in those members  
			 */
			$template_object_members = $object->getMembers();
			
			$object_members = array();
			
			if (count($selected_members) == 0) {
				//change members according to context 
				foreach( $active_context as $selection ) {
					if ($selection instanceof Member) { // member selected
						foreach( $template_object_members as $i => $object_member){
							if ($object_member instanceof Member && $object_member->getObjectTypeId() == $selection->getObjectTypeId()) {
								unset($template_object_members[$i]);
							}						
						}
						
						$object_members[] = $selection->getId();
					}
				}
			} else {
				$object_members = $selected_members;
			}
			foreach( $template_object_members as $object_member ) {
				$object_members[] = $object_member->getId();
			}
			
			// add the members that must be autocassified using dimension associations according to the current set of members
			Env::useHelper('dimension');
			append_related_members_to_autoclassify($object_members);
			
			// classify the task
			$controller->add_to_members($copy, $object_members, null, false);
			
			// set property values as defined in template
			if($object instanceof TemplateTask && $copy instanceof ProjectTask)
			{
			    instantiate_template_task_parameters($object, $copy, $parameterValues);
			}

			// subscribe assigned to
			if ($copy instanceof ProjectTask) {
				foreach($copy->getOpenSubTasks(false) as $m_task){
					if ($m_task->getAssignedTo() instanceof Contact) {
						$m_task->subscribeUser($m_task->getAssignedTo());
					}
				}
				if ($copy->getAssignedTo() instanceof Contact) {
					$copy->subscribeUser($copy->getAssignedTo());
				}
			} else if ($copy instanceof ProjectMilestone) {
				foreach($copy->getTasks(false) as $m_task){
					if ($m_task->getAssignedTo() instanceof Contact) {
						$m_task->subscribeUser($m_task->getAssignedTo());
					}
				}
			}
			
			// save the new generated object again (now with all the data saved, such as members, etc) 
			// to ensure that all the additional calculated data can be calculated correctly (such as object billings)
			$copy->save();
		}

		foreach ($copies as $c) {
			if ($c instanceof ProjectTask) {
				
				// check permissions for the assigned user
				$assigned = $c->getAssignedToContact();
				if ($assigned instanceof Contact) {
					$allowed_users = allowed_users_to_assign($c->getMembers(), true, false);
					$allowed = false;
					foreach ($allowed_users as $auser) {
						if ($auser->getId() == $assigned->getId()) {
							$allowed = true;
							break;
						}
					}
					if (!$allowed) {
						$text = lang('couldnt assign user to task due to permissions', $assigned->getObjectName(), $c->getObjectName());
						evt_add("popup", array('title' => lang('information'), 'message' => $text));
						
						$c->setAssignedToContactId(0);
						$c->save();
					}
				}
				
				if ($c->getMilestoneId() > 0) {
					// find milestone in copies
					foreach ($copies as $m) {
						if ($m instanceof ProjectMilestone && $m->getFromTemplateObjectId() == $c->getMilestoneId()) {
							$c->setMilestoneId($m->getId());
							$c->save();
							break;
						}
					}
				}

				//if is subtask we search for the project task id of the parent
				if($c->getParentId() > 0){	
					foreach ($copies as $cp) {
						if($cp instanceof ProjectTask){
							if($cp->getFromTemplateObjectId() == $c->getParentId()){
								$c->setParentId($cp->getId());	
								$c->save();	
								break;						
							}
						}
						
					}					
				}

				//linked objects
				if (isset($linked_objects) && is_array($linked_objects)) {
					foreach ($linked_objects as $linked_object) {
						if ($linked_object instanceof ApplicationDataObject) {
							$c->linkObject($linked_object);
						}
					}
				}
			}			
		}
		
		//copy dependencies
		foreach ($dependencies as $dependencie) {
			if ($dependencie instanceof ProjectTaskDependency) {
				$dep = new ProjectTaskDependency();
				$dep->setPreviousTaskId($copiesIds[$dependencie->getPreviousTaskId()]);
				$dep->setTaskId($copiesIds[$dependencie->getTaskId()]);
				$dep->save();						
			}
		}
		
		DB::commit();

		foreach ($copies as $c) {
			if ($c instanceof ProjectTask) {
				ApplicationLogs::createLog($c, ApplicationLogs::ACTION_ADD);

                // notify asignee
                if(1) { //array_var($task_data, 'send_notification')
                    if(($c instanceof ProjectTask) && ($c->getAssignedToContactId() != $c->getAssignedById())) {
                        try {
                            Notifier::taskAssigned($c);
                        } catch(Exception $e) {
                            evt_add("debug", $e->getMessage());
                        } // try
                    }
                }
			}

			// allow calculations for this task from this point
			$c->dont_calculate_financials = false;
			
			// avoid calling project financials recalculations repeatedly once per task, 
			// do that in the general hook after all tasks are done
			$c->dont_calculate_project_financials = true;
			
			$ret = null;
			Hook::fire('after_template_object_instantiation_and_commit', array('template' => $template, 'object' => $c), $ret);
		}

		// This hook allows to execute additional general tasks after the template is completely instantiated (like recalculate project's financials)
		Hook::fire('after_task_template_instantiation', array('template' => $template, 'objects' => $copies), $ret);
		
		if (is_array($parameters) && count($parameters) > 0){
			ajx_current("back");
		}else{
			ajx_current("back");
		}
		
		flash_success(lang('success instatiate template', $template->getName()));
		if (array_var($_GET, 'from_email') > 0) {
			evt_add('reload tab panel', 'tasks-panel');
		}
	}
	
	

	function instantiate_parameters(){
		if(is_array(array_var($_POST, 'parameterValues'))){
			ajx_current("back");
			
			$params = array_var($_POST, 'parameterValues');
			$template_id = get_id();
			$error = null;
			Hook::fire('before_instantiate_paramters', array('id' => $template_id, 'params' => array_var($_POST, 'parameterValues')), $error);

			foreach ($params as $param_name => $val) {
				$tp = TemplateParameters::findOne(array('conditions' => array("template_id=? AND REPLACE(`name`,\"'\",'')=?", $template_id, $param_name)));
				if ($tp instanceof TemplateParameter && $tp->getColumnValue('is_required') && !$val) {
					$error = lang('custom property value required', $param_name);
				} else if ($tp instanceof TemplateParameter && $tp->getColumnValue('type') == 'date' && !isDate($val)) {
					$error = lang('custom property value required', $param_name);
				}
			}

			if ($error) {
				flash_error($error);
				ajx_current("empty");
				return;
			}
			
			$this->instantiate();
			
			
		}else{
			$id = get_id();

			$additional_member_ids = array();
			if($add_mem_id = get_id('member_id')){
				$additional_member_ids[] = $add_mem_id;
				
				// ensure that new member is in context before rendering the template parameters form
				if (!in_array($add_mem_id, active_context_members(false))) {
					$current_context = active_context();
					$add_mem = Members::findById($add_mem_id);
					if ($add_mem instanceof Member) $current_context[] = $add_mem;
					CompanyWebsite::instance()->setContext($current_context);
				}
			}
			$linked_objects = array();
			$parameters = TemplateParameters::getParametersByTemplate($id);
			$params = array();
			foreach($parameters as $parameter){
				$params[] = $parameter->getArrayInfo();
			}

			$template = COTemplates::findById($id);
			if (!$template instanceof COTemplate) {
				flash_error(lang("template dnx"));
				ajx_current("empty");
				return;
			}

			if (array_var($_REQUEST, 'additional_member_ids')) {
				$additional_member_ids = array_merge($additional_member_ids,json_decode(array_var($_REQUEST, 'additional_member_ids')));
			}

			if (array_var($_REQUEST, 'linked_objects')) {
				$linked_objects = json_decode(array_var($_REQUEST, 'linked_objects'));
			}

			tpl_assign('id', $id);
			tpl_assign('additional_member_ids', $additional_member_ids);
			tpl_assign('linked_objects', $linked_objects);
			tpl_assign('parameters', $params);
			tpl_assign('template', $template);
		}
	}

	/**
	 * @TODO: check if this function is used somewhere and fix it or delete it
	 * @deprecated
	 */
	function assign_to_ws() {
		if (!can_manage_templates(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		$template_id = get_id();
		$cotemplate = COTemplates::findById($template_id);
		if (!$cotemplate instanceof COTemplate) {
			flash_error(lang("template dnx"));
			ajx_current("empty");
			return;
		}
	//	$selected = WorkspaceTemplates::getWorkspacesByTemplate($template_id);
		tpl_assign('workspaces', logged_user()->getWorkspaces());
		tpl_assign('selected', $selected);
		tpl_assign('cotemplate', $cotemplate);
		$checked = array_var($_POST, 'ws_ids');
		if ($checked != null) {
			try {
				DB::beginWork();
	//			WorkspaceTemplates::deleteByTemplate($template_id);
				$wss = Projects::findByCSVIds($checked);
				foreach ($wss as $ws){
	//				$obj = new WorkspaceTemplate();
					$obj->setWorkspaceId($ws->getId());
					$obj->setTemplateId($template_id);
					$obj->setInludeSubWs(false);
					$obj->save();
				}
				DB::commit();
				flash_success(lang('success assign workspaces'));
				ajx_current("back");
			} catch (Exception $exc){
				DB::rollback();
				flash_error(lang('error assign workspace') . $exc->getMessage());
				ajx_current("empty");
			}
		}
	}

	function get_object_properties(){
		$obj_id = get_id();
		$obj = Objects::findObject($obj_id);
		$manager = $obj->manager();
		$objectProperties = $manager->getTemplateObjectProperties();
		
		/*
		//sort array $props:
		usort($props, function ($prop1, $prop2) {
		    // Warning: this does not work with older php versions
		    return $prop1['name'] <=> $prop2['name'];
		});
		*/
		
		ajx_current("empty");
		//ajx_extra_data(array('properties' => $props));
		ajx_extra_data(array('properties' => $objectProperties));
	}
	
	
	function get_template_tasks_data() {
		ajx_current("empty");
		
		$ids = explode(',', array_var($_REQUEST, 'ids'));
		foreach($ids as $k => &$id) {
			if (!is_numeric($id)) unset($ids[$k]);
		}
		$objects = array();
		if (count($ids) > 0) {
			$tasks = TemplateTasks::findAll(array('conditions' => 'id IN ('.implode(',', $ids).')'));
			$ot = ObjectTypes::findByName('template_task');
			foreach ($tasks as $task) {
				$objects[] = $this->prepareObject($task->getId(), $task->getId(), $task->getObjectName(), $ot->getName(), $task->manager(), "", $task->getMilestoneId(), array(), $task->getParentId(), 'ico-task');
			}
		}
		
		ajx_extra_data(array('tasks' => $objects));
	}


	function copy_task_template($template_id = null) {

		if (!can_manage_templates(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}

		if (!$template_id) {
			$template_id = array_var($_REQUEST, 'template_id');
		}
		$original_template = COTemplates::instance()->findById($template_id);

		if ($original_template instanceof COTemplate) {

			try {
				DB::beginWork();

				$template_columns = COTemplates::instance()->getColumns();
				
				// copy template columns
				$new_template = new COTemplate();
				foreach ($template_columns as $col) {
					if ($col != 'object_id') {
						$new_template->setColumnValue($col, $original_template->getColumnValue($col));
					}
				}
				$new_template->setObjectName(lang('copy of', $original_template->getName()));
				$new_template->save();
	
				// copy template parameters
				$original_parameters = TemplateParameters::getParametersByTemplate($template_id);
				$param_columns = TemplateParameters::instance()->getColumns();
	
				foreach ($original_parameters as $param) {
					$new_param = new TemplateParameter();
					foreach ($param_columns as $col) {
						if ($col != 'id' && $col != 'template_id') {
							$new_param->setColumnValue($col, $param->getColumnValue($col));
						}
					}
					$new_param->setTemplateId($new_template->getId());
					$new_param->save();
				}
	
				// get all template objects and copy them
				$original_template_objects = $original_template->getObjects();
				$task_columns = TemplateTasks::instance()->getColumns();
				$milestone_columns = TemplateMilestones::instance()->getColumns();
	
				$new_template_tasks = array(); // keep new tasks so we can iterate them and update parent and milestone with correct values
				$template_objects_mapping = array(); // map correspondency between old objects and their new copies to use them when saving properties
	
				foreach ($original_template_objects as $template_obj) {
	
					// copy template tasks
					if ($template_obj instanceof TemplateTask) {
						$new_template_task = new TemplateTask();
						foreach ($task_columns as $col) {
							if ($col != 'id' && $col != 'object_id' && $col != 'template_id') {
								$new_template_task->setColumnValue($col, $template_obj->getColumnValue($col));
							}
						}
						$new_template_task->setTemplateId($new_template->getId());
						$new_template_task->setObjectName($template_obj->getObjectName());
						$new_template_task->save();

						$new_object = $new_template_task;
	
						$new_template_tasks[] = $new_template_task;
						$template_objects_mapping[$template_obj->getId()] = $new_template_task->getId();
	
					} else if ($template_obj instanceof TemplateMilestone) {
						// copy template milestones
						
						$new_template_mile = new TemplateMilestone();
						foreach ($milestone_columns as $col) {
							if ($col != 'id' && $col != 'object_id' && $col != 'template_id') {
								$new_template_mile->setColumnValue($col, $template_obj->getColumnValue($col));
							}
						}
						$new_template_mile->setTemplateId($new_template->getId());
						$new_template_mile->setObjectName($template_obj->getObjectName());
						$new_template_mile->save();
	
						$new_object = $new_template_mile;
	
						$template_objects_mapping[$template_obj->getId()] = $new_template_mile->getId();
					}

					// create a TemplateObject
					$to = new TemplateObject();
					$to->setObject($new_object);
					$to->setTemplate($new_template);
					$to->save();

					// copy classification
					$members = $template_obj->getMembers();
					ObjectMembers::addObjectToMembers($new_object->getId(), $members);

					// copy custom property values
					$cp_vals = CustomPropertyValues::getAllCustomPropertyValuesForObject($template_obj->getId());
					foreach ($cp_vals as $cp_val) {
						$new_cp_val = new CustomPropertyValue();
						$new_cp_val->setObjectId($new_object->getId());
						$new_cp_val->setCustomPropertyId($cp_val->getCustomPropertyId());
						$new_cp_val->setValue($cp_val->getValue());
						$new_cp_val->setCurrencyId($cp_val->getCurrencyId());
						$new_cp_val->save();
					}
				}

				// set correct parent ids and milestone ids to tasks
				foreach ($new_template_tasks as $new_template_task) {
					$need_save = false;
					if ($new_template_task->getParentId() > 0) {
						$new_parent_id = array_var($template_objects_mapping, $new_template_task->getParentId());
						$new_template_task->setParentId($new_parent_id);
						$need_save = true;
					}
					if ($new_template_task->getMilestoneId() > 0) {
						$new_milestone_id = array_var($template_objects_mapping, $new_template_task->getMilestoneId());
						$new_template_task->setMilestoneId($new_milestone_id);
						$need_save = true;
					}
					if ($need_save) {
						$new_template_task->save();
					}
				}
	
				// copy template object properties
				foreach ($template_objects_mapping as $old_object_id => $new_object_id) {
	
					$template_obj_properties = TemplateObjectProperties::getPropertiesByTemplateObject($template_id, $old_object_id);
					foreach ($template_obj_properties as $prop) {
						$new_property = new TemplateObjectProperty();
						$new_property->setTemplateId($new_template->getId());
						$new_property->setObjectId($new_object_id);
						$new_property->setProperty($prop->getProperty());
						$new_property->setValue($prop->getValue());
						$new_property->save();
					}
				}

				DB::commit();

				ApplicationLogs::createLog($new_template, ApplicationLogs::ACTION_ADD);
				flash_success(lang("success copy template"));
				ajx_current("reload");

			} catch (Exception $e) {
				flash_error($e->getMessage());
				DB::rollback();
				ajx_current("empty");
			}


		}

	}


}

?>