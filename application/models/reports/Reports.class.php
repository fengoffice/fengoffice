<?php

/**
 *   Reports class
 *
 * 
 */

class Reports extends BaseReports {

	public function __construct() {
		parent::__construct ();
		$this->object_type_name = 'report';
	}
	/**
	 * Return specific report
	 *
	 * @param $id
	 * @return Report
	 */
	static function getReport($id) {
		return self::findById($id);
	} //  getReport

	/**
	 * Return all reports for an object type
	 *
	 * @param $object_type
	 * @return array
	 */
	static function getAllReportsForObjectType($object_type) {
		return self::findAll(array(
			'conditions' => array("`object_type_id` = ?", $object_type)
		));
	} //  getAllReportsForObjectType

	/**
	 * Return all reports
	 *
	 * @return array
	 */
	static function getAllReports() {
		return self::findAll();
	} //  getAllReports

	/**
	 * Return all reports
	 *
	 * @return array
	 */
	static function getAllReportsByObjectType() {
		$ignore_context_reports = Reports::findAll(array("conditions" => "ignore_context = 1"));
		
		$reports_result = Reports::instance()->listing();
		$reports = $reports_result->objects;
		
		$to_merge_reports = array();
		foreach ($ignore_context_reports as $icr) {
			$add = true;
			foreach ($reports as $r) {
				if ($r->getId() == $icr->getId()) {
					$add = false;
					break;
				}
			}
			if ($add) $to_merge_reports[] = $icr;
		}
		$reports = array_merge($reports, $to_merge_reports);
		$reports = feng_sort($reports, "getObjectName");
		
		$result = array();
		foreach ($reports as $report){
			if (array_key_exists($report->getReportObjectTypeId(), $result)) {
				$result[$report->getReportObjectTypeId()][] = $report;
			} else {
				$result[$report->getReportObjectTypeId()] = array($report);
			}
		}
		return $result;
	}
	
	static function get_extra_contact_column_condition($field, $operator, $value) {
		// build operator
		if (in_array($operator, array('=','<>'))) {
			$cond = " $operator '$value'";
		} else if (in_array($operator, array('like','not like'))) {
			$cond = " $operator '%$value%'";
		} else {
			$cond = " like '$value%'";
		}
		
		// build condition
		switch ($field) {
			case 'is_user':
				return '`user_type` '.($value=='1'?'>':'=').' 0';
			case 'email_address':
				return 'o.id IN (select ce.contact_id from '.TABLE_PREFIX.'contact_emails ce where ce.contact_id=o.id and ce.email_address '.$cond.')';
			case 'mobile_phone':
			case 'work_phone':
			case 'home_phone':
				$type_name = ($filed == 'mobile_phone' ? 'mobile' : ($filed == 'work_phone' ? 'work' : 'home'));
				$type_cond = " AND ce.telephone_type_id in (select t.id from ".TABLE_PREFIX."telephone_types t where t.name='$type_name')";
				return 'o.id IN (select ce.contact_id from '.TABLE_PREFIX.'contact_telephones ce where ce.contact_id=o.id and ce.number '.$cond.' AND '.$type_cond.')';
			case 'personal_webpage':
			case 'work_webpage':
			case 'other_webpage':
				$type_name = ($filed == 'personal_webpage' ? 'personal' : ($filed == 'work_webpage' ? 'work' : 'other'));
				$type_cond = " AND ce.web_type_id in (select t.id from ".TABLE_PREFIX."webpage_types t where t.name='$type_name')";
				return 'o.id IN (select ce.contact_id from '.TABLE_PREFIX.'contact_web_pages ce where ce.contact_id=o.id and ce.url '.$cond.' AND '.$type_cond.')';
			case 'im_values':
				return 'o.id IN (select ce.contact_id from '.TABLE_PREFIX.'contact_im_values ce where ce.contact_id=o.id and ce.value '.$cond.')';
			case 'home_address':
			case 'work_address':
			case 'other_address':
				$type_name = ($filed == 'home_address' ? 'home' : ($filed == 'work_address' ? 'work' : 'other'));
				$type_cond = " AND ce.address_type_id in (select t.id from ".TABLE_PREFIX."address_types t where t.name='$type_name')";
				return "o.id IN (select ce.contact_id from ".TABLE_PREFIX."contact_addresses ce where ce.contact_id=o.id ".$type_cond." and (
					ce.street ".$cond." or ce.city ".$cond." or ce.state ".$cond." or ce.country ".$cond." or ce.zip_code ".$cond."))";
			default:
				return 'true';
			break;
		}
	}
	
	static function get_extra_contact_column_order_by($field, &$order, &$select_columns) {
		$join_params = array(
			'join_type' => "LEFT ",
			'jt_field' => "contact_id",
			'e_field' => "object_id",
		);
		switch ($field) {
			case 'is_user':
				$order = 'user_type';
				$join_params = null;
				break;
			case 'email_address':
				$order = 'IF(ISNULL(jt.email_address),1,0),jt.email_address';
				$join_params['table'] = TABLE_PREFIX."contact_emails";
				$select_columns = array("DISTINCT o.*", "e.*");
				break;
			case 'mobile_phone':
			case 'work_phone':
			case 'home_phone':
				$order = 'IF(ISNULL(jt.number),1,0),jt.number';
				$join_params['table'] = TABLE_PREFIX."contact_telephones";
				$select_columns = array("DISTINCT o.*", "e.*");
				break;
			case 'personal_webpage':
			case 'work_webpage':
			case 'other_webpage':
				$order = 'IF(ISNULL(jt.url),1,0),jt.url';
				$join_params['table'] = TABLE_PREFIX."contact_web_pages";
				$select_columns = array("DISTINCT o.*", "e.*");
				break;
			case 'im_values':
				$order = 'IF(ISNULL(jt.value),1,0),jt.value';
				$join_params['table'] = TABLE_PREFIX."contact_im_values";
				$select_columns = array("DISTINCT o.*", "e.*");
				break;
			case 'home_address':
			case 'work_address':
			case 'other_address':
				$order = 'IF(ISNULL(jt.street) and ISNULL(jt.city) and ISNULL(jt.state) and ISNULL(jt.country) and ISNULL(jt.zip_code),1,0), jt.street, jt.city, jt.state, jt.country, jt.zip_code';
				$join_params['table'] = TABLE_PREFIX."contact_addresses";
				$select_columns = array("DISTINCT o.*", "e.*");
				break;
			default:
				$order = 'first_name';
				$join_params = null;
			break;
		}
		
		return $join_params;
	}
	
	static function get_extra_contact_columns() {
		return array("email_address", "is_user", "mobile_phone", "work_phone", "home_phone", "im_values", 
			"personal_webpage", "work_webpage", "other_webpage", "home_address", "work_address", "other_address");
	}

	/**
	 * Execute a report and return results
	 *
	 * @param $id
	 * @param $params
	 *
	 * @return array
	 */
	static function executeReport($id, $params, $order_by_col = '', $order_by_asc = true, $offset=0, $limit=50, $to_print = false) {
		if (is_null(active_context())) {
			CompanyWebsite::instance()->setContext(build_context_array(array_var($_REQUEST, 'context')));
		}
		$results = array();
		$report = self::getReport($id);
		$show_archived = false;
		if($report instanceof Report){
			$conditionsFields = ReportConditions::getAllReportConditionsForFields($id);
			$conditionsCp = ReportConditions::getAllReportConditionsForCustomProperties($id);
			
			$ot = ObjectTypes::findById($report->getReportObjectTypeId());
			$table = $ot->getTableName();
			
			if ($ot->getType() == 'dimension_object' || $ot->getType() == 'dimension_group') {
				$hook_parameters = array(
					'report' => $report,
					'params' => $params,
					'order_by_col' => $order_by_col,
					'order_by_asc' => $order_by_asc,
					'offset' => $offset,
					'limit' => $limit,
					'to_print' => $to_print,
				);
				$report_result = null;
				Hook::fire('replace_execute_report_function', $hook_parameters, $report_result);
				if ($report_result) {
					return $report_result;
				}
			}
			
			eval('$managerInstance = ' . $ot->getHandlerClass() . "::instance();");
			eval('$item_class = ' . $ot->getHandlerClass() . '::instance()->getItemClass(); $object = new $item_class();');
			
			$order_by = '';
			if (is_object($params)) {
				$params = get_object_vars($params);				
			}
			
			$report_columns = ReportColumns::getAllReportColumns($id);

			$allConditions = "";
			
			$contact_extra_columns = self::get_extra_contact_columns();
			
			if(count($conditionsFields) > 0){
				foreach($conditionsFields as $condField){
					if($condField->getFieldName() == "archived_on"){
						$show_archived = true;
					}
					$skip_condition = false;
					$model = $ot->getHandlerClass();
					$model_instance = new $model();
					$col_type = $model_instance->getColumnType($condField->getFieldName());

					$allConditions .= ' AND ';
					$dateFormat = 'm/d/Y';
					if(isset($params[$condField->getId()])){
						$value = $params[$condField->getId()];
						if ($col_type == DATA_TYPE_DATE || $col_type == DATA_TYPE_DATETIME) {
							$dateFormat = user_config_option('date_format');
						}
					} else {
						$value = $condField->getValue();
					}
					
					if ($ot->getHandlerClass() == 'Contacts' && in_array($condField->getFieldName(), $contact_extra_columns)) {
						$allConditions .= self::get_extra_contact_column_condition($condField->getFieldName(), $condField->getCondition(), $value);
					} else {
						if ($value == '' && $condField->getIsParametrizable()) $skip_condition = true;
						if (!$skip_condition) {
							$field_name = $condField->getFieldName();
							if (in_array($condField->getFieldName(), Objects::getColumns())) {
								$field_name = 'o`.`'.$condField->getFieldName();
							}
							if($condField->getCondition() == 'like' || $condField->getCondition() == 'not like'){
								$value = '%'.$value.'%';
							}
							if ($col_type == DATA_TYPE_DATE || $col_type == DATA_TYPE_DATETIME) {
								if ($value == date_format_tip($dateFormat)) {
									$value = EMPTY_DATE;
								} else {
									$dtValue = DateTimeValueLib::dateFromFormatAndString($dateFormat, $value);
									$value = $dtValue->format('Y-m-d');
								}
							}
							if($condField->getCondition() != '%'){
								if ($col_type == DATA_TYPE_INTEGER || $col_type == DATA_TYPE_FLOAT) {
									$allConditions .= '`'.$field_name.'` '.$condField->getCondition().' '.DB::escape($value);
								} else {
									if ($condField->getCondition()=='=' || $condField->getCondition()=='<=' || $condField->getCondition()=='>='){
										if ($col_type == DATA_TYPE_DATETIME || $col_type == DATA_TYPE_DATE) {
											$equal = 'datediff('.DB::escape($value).', `'.$field_name.'`)=0';
										} else {
											$equal = '`'.$field_name.'` '.$condField->getCondition().' '.DB::escape($value);
										}
										switch($condField->getCondition()){
											case '=':
												$allConditions .= $equal;
												break;
											case '<=':
											case '>=':
												$allConditions .= '(`'.$field_name.'` '.$condField->getCondition().' '.DB::escape($value).' OR '.$equal.') ';
												break;																
										}										
									} else {
										$allConditions .= '`'.$field_name.'` '.$condField->getCondition().' '.DB::escape($value);
									}									
								}
							} else {
								$allConditions .= '`'.$field_name.'` like '.DB::escape("%$value");
							}
						} else $allConditions .= ' true';
					}
				}
			}
			if(count($conditionsCp) > 0){
				$dateFormat = user_config_option('date_format');
				$date_format_tip = date_format_tip($dateFormat);
				
				foreach($conditionsCp as $condCp){
					$cp = CustomProperties::getCustomProperty($condCp->getCustomPropertyId());

					$skip_condition = false;
					
					if(isset($params[$condCp->getId()."_".$cp->getName()])){
						$value = $params[$condCp->getId()."_".$cp->getName()];
					}else{
						$value = $condCp->getValue();
					}
					if ($value == '' && $condCp->getIsParametrizable()) $skip_condition = true;
					if (!$skip_condition) {
						$current_condition = ' AND ';
						$current_condition .= 'o.id IN ( SELECT object_id as id FROM '.TABLE_PREFIX.'custom_property_values cpv WHERE ';
						$current_condition .= ' cpv.custom_property_id = '.$condCp->getCustomPropertyId();
						$fieldType = $object->getColumnType($condCp->getFieldName());

						if($condCp->getCondition() == 'like' || $condCp->getCondition() == 'not like'){
							$value = '%'.$value.'%';
						}
						if ($cp->getType() == 'date') {
							if ($value == $date_format_tip) continue;
							$dtValue = DateTimeValueLib::dateFromFormatAndString($dateFormat, $value);
							$value = $dtValue->format('Y-m-d H:i:s');
						}
						if($condCp->getCondition() != '%'){
							if ($cp->getType() == 'numeric') {
								$current_condition .= ' AND cpv.value '.$condCp->getCondition().' '.DB::escape($value);
							}else if ($cp->getType() == 'boolean') {
								$current_condition .= ' AND cpv.value '.$condCp->getCondition().' '.($value ? '1' : '0');
								if (!$value) {
									$current_condition .= ') OR o.id NOT IN (SELECT object_id as id FROM '.TABLE_PREFIX.'custom_property_values cpv2 WHERE cpv2.object_id=o.id AND cpv2.value=1 AND cpv2.custom_property_id = '.$condCp->getCustomPropertyId();
								}
							}else{
								$current_condition .= ' AND cpv.value '.$condCp->getCondition().' '.DB::escape($value);
							}
						}else{
							$current_condition .= ' AND cpv.value like '.DB::escape("%$value");
						}
						$current_condition .= ')';
						$allConditions .= $current_condition;
					}
				}
			}
			
			$select_columns = array('*');
			$join_params = null;
			if ($order_by_col == '') {
				$order_by_col = $report->getOrderBy();
			}
			
			if ($ot->getHandlerClass() == 'Contacts' && in_array($order_by_col, $contact_extra_columns)) {
				$join_params = self::get_extra_contact_column_order_by($order_by_col, $order_by_col, $select_columns);
			}
			
			$original_order_by_col = $order_by_col;
			if (in_array($order_by_col, self::$external_columns)) {
				$order_by_col = 'name_order';
				$join_params = array(
					'table' => Objects::instance()->getTableName(),
					'jt_field' => 'id',
					'e_field' => $original_order_by_col,
					'join_type' => 'left'
				);
				$select_columns = array();
				$tmp_cols = $managerInstance->getColumns();
				foreach ($tmp_cols as $col) $select_columns[] = "e.$col";
				$tmp_cols = Objects::instance()->getColumns();
				foreach ($tmp_cols as $col) $select_columns[] = "o.$col";
				$select_columns[] = 'jt.name as name_order';
			}
			if ($order_by_asc == null) $order_by_asc = $report->getIsOrderByAsc();

			if ($ot->getName() == 'task' && !SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks')) {
				$allConditions .= " AND assigned_to_contact_id = ".logged_user()->getId();
			}
			if ($managerInstance) {
				if ($order_by_col == "order"){
					$order_by_col = "`$order_by_col`";
				};
				$listing_parameters = array(
					"select_columns" => $select_columns,
					"order" => "$order_by_col",
					"order_dir" => ($order_by_asc ? "ASC" : "DESC"),
					"extra_conditions" => $allConditions,
					"count_results" => true,
					"join_params" => $join_params
				);
				if ($limit > 0) {
					$listing_parameters["start"] = $offset;
					$listing_parameters["limit"] = $limit;
				}
				if($show_archived){
					$listing_parameters["archived"] = true;
				}				
				$result = $managerInstance->listing($listing_parameters);
			}else{
				// TODO Performance Killer
				$result = ContentDataObjects::getContentObjects(active_context(), $ot, $order_by_col, ($order_by_asc ? "ASC" : "DESC"), $allConditions);
			}
			$objects = $result->objects;
			$totalResults = $result->total;

			$results['pagination'] = Reports::getReportPagination($id, $params, $original_order_by_col, $order_by_asc, $offset, $limit, $totalResults);
		
			$dimensions_cache = array();
			
			foreach($report_columns as $column){
				if ($column->getCustomPropertyId() == 0) {
					$field = $column->getFieldName();
					if (str_starts_with($field, 'dim_')) {
						$dim_id = str_replace("dim_", "", $field);
						$dimension = Dimensions::getDimensionById($dim_id);
						$dimensions_cache[$dim_id] = $dimension;
						$doptions = $dimension->getOptions(true);
						$column_name = $doptions && isset($doptions->useLangs) && $doptions->useLangs ? lang($dimension->getCode()) : $dimension->getName();
						
						$results['columns'][$field] = $column_name;
						$results['db_columns'][$column_name] = $field;
					} else {
						if ($managerInstance->columnExists($field) || Objects::instance()->columnExists($field)) {
							$column_name = Localization::instance()->lang('field '.$ot->getHandlerClass().' '.$field);
							if (is_null($column_name)) $column_name = lang('field Objects '.$field);
							$results['columns'][$field] = $column_name;
							$results['db_columns'][$column_name] = $field;
						}else{
							if($ot->getHandlerClass() == 'Contacts'){
								if (in_array($field, $contact_extra_columns)){
									$results['columns'][$field] = lang($field);	
									$results['db_columns'][lang($field)] = $field;
								}
							} else if($ot->getHandlerClass() == 'Timeslots'){
								if (in_array($field, array('time', 'billing'))){
									$results['columns'][$field] = lang('field Objects '.$field);
									$results['db_columns'][lang('field Objects '.$field)] = $field;
								}
							} else if($ot->getHandlerClass() == 'MailContents'){
								if (in_array($field, array('to', 'cc', 'bcc', 'body_plain', 'body_html'))){
									$results['columns'][$field] = lang('field Objects '.$field);
									$results['db_columns'][lang('field Objects '.$field)] = $field;
								}
							}
						}
					}
					
				} else {
					$results['columns'][$column->getCustomPropertyId()] = $column->getCustomPropertyId();					
				}
			}
			
			$report_rows = array();
			foreach($objects as &$object){/* @var $object Object */
				$obj_name = $object->getObjectName();
				$icon_class = $object->getIconClass();
				
				$row_values = array('object_type_id' => $object->getObjectTypeId());
				
				if (!$to_print) {
					$row_values['link'] = '<a class="link-ico '.$icon_class.'" title="' . clean($obj_name) . '" target="new" href="' . $object->getViewUrl() . '">&nbsp;</a>';
				}
				
				foreach($report_columns as $column){
					if ($column->getCustomPropertyId() == 0) {
						
						$field = $column->getFieldName();
						
						if (str_starts_with($field, 'dim_')) {
							$dim_id = str_replace("dim_", "", $field);
							if (!array_var($dimensions_cache, $dim_id) instanceof Dimension) {
								$dimension = Dimensions::getDimensionById($dim_id);
								$dimensions_cache[$dim_id] = $dimension;
							} else {
								$dimension = array_var($dimensions_cache, $dim_id);
							}
							$om_object_id = $object instanceof Timeslot ? $object->getRelObjectId() : $object->getId();
							$members = ObjectMembers::getMembersByObjectAndDimension($om_object_id, $dim_id, " AND om.is_optimization=0");
							
							$value = "";
							foreach ($members as $member) {/* @var $member Member */
								$val = $member->getPath();
								$val .= ($val == "" ? "" : "/") . $member->getName();
								
								if ($value != "") $val = " - $val";
								$value .= $val;
							}
							
							$row_values[$field] = $value;
						} else {
							if ($object instanceof Timeslot) {
								if ($field == 'id') {
									$value = $object->getObjectId();
								} else {
									$value = $object->getColumnValue($field);
									// if it is a task column
									if (in_array($field, ProjectTasks::instance()->getColumns())) {
										$task = ProjectTasks::findById($object->getRelObjectId());
										// if task exists
										if ($task instanceof ProjectTask) {
											$value = $task->getColumnValue($field);
											// if it is an external task column
											if (in_array($field, ProjectTasks::instance()->getExternalColumns())) {
												$value = self::instance()->getExternalColumnValue($field, $value, ProjectTasks::instance());
											} else {
												// if is a date then use format
												if (ProjectTasks::instance()->getColumnType($field) == DATA_TYPE_DATETIME && $value instanceof DateTimeValue) {
													$value = format_value_to_print($field, $value->toMySQL(), DATA_TYPE_DATETIME, $report->getReportObjectTypeId());
												}
											}
										}
										$results['columns'][$field] = lang('field ProjectTasks '.$field);
										$results['db_columns'][lang('field ProjectTasks '.$field)] = $field;
									}
								}
							} else {
								$value = $object->getColumnValue($field);
							}
								
							if ($value instanceof DateTimeValue) {
								$dateFormat = user_config_option('date_format');
								Hook::fire("custom_property_date_format", null, $dateFormat);
								
								$tz = logged_user()->getTimezone();
								if ($object instanceof ProjectTask) {
									if(($field == 'due_date' && !$object->getUseDueTime()) || ($field == 'start_date' && !$object->getUseStartTime())){
										$dateFormat = user_config_option('date_format');
										$tz = 0;
									}								
								}
								$value = format_date($value, $dateFormat, $tz * 3600);
							}
							
							if(in_array($field, $managerInstance->getExternalColumns())){
								if ($object instanceof Timeslot && $field == 'time') {
									$lastStop = $object->getEndTime() != null ? $object->getEndTime() : ($object->isPaused() ? $object->getPausedOn() : DateTimeValueLib::now());
									$seconds = $lastStop->getTimestamp() - $object->getStartTime()->getTimestamp();
									$hours = number_format($seconds / 3600, 2, ',', '.');
									$value = $hours;
									//$value = DateTimeValue::FormatTimeDiff($object->getStartTime(), $lastStop, "hm", 60, $object->getSubtract());
								} else if ($object instanceof Timeslot && $field == 'billing') {
									$value = config_option('currency_code', '$') .' '. $object->getFixedBilling();
								} else {
									$value = self::instance()->getExternalColumnValue($field, $value, $managerInstance);
								}
							} else if ($field != 'link'){
								//$value = html_to_text(html_entity_decode($value));
								if ($object->getColumnType($field) == DATA_TYPE_STRING) {
									// change html block end tags and brs to \n, then remove all other html tags, then replace \n with <br>, to remove all styles and keep the enters
									$value = str_replace(array("</div>", "</p>", "<br>", "<br />", "<br/>"), "\n", $value);
									$value = nl2br(strip_tags($value));
								}
							}
							if(self::isReportColumnEmail($value)) {
								if(logged_user()->hasMailAccounts()){
									$value = '<a class="internalLink" href="'.get_url('mail', 'add_mail', array('to' => clean($value))).'">'.clean($value).'</a></div>';
								}else{
									$value = '<a class="internalLink" target="_self" href="mailto:'.clean($value).'">'.clean($value).'</a></div>';
								}
							}
							$row_values[$field] = $value;
							
							if($ot->getHandlerClass() == 'Contacts'){
								if($managerInstance instanceof Contacts){
									$contact = Contacts::findOne(array("conditions" => "object_id = ".$object->getId()));
									if ($field == "email_address"){
										$row_values[$field] = $contact->getEmailAddress();
									}
									if ($field == "is_user"){
										$row_values[$field] = $contact->getUserType() > 0 && !$contact->getIsCompany();
									}
									if ($field == "im_values"){
										$str = "";
										foreach ($contact->getAllImValues() as $type => $value) {
											$str .= ($str == "" ? "" : " | ") . "$type: $value";
										}
										$row_values[$field] = $str;
									}
									if (in_array($field, array("mobile_phone", "work_phone", "home_phone"))) {
										if ($field == "mobile_phone") $row_values[$field] = $contact->getPhoneNumber('mobile', null, false);
										else if ($field == "work_phone") $row_values[$field] = $contact->getPhoneNumber('work', null, false);
										else if ($field == "home_phone") $row_values[$field] = $contact->getPhoneNumber('home', null, false);
									}
									if (in_array($field, array("personal_webpage", "work_webpage", "other_webpage"))) {
										if ($field == "personal_webpage") $row_values[$field] = $contact->getWebpageUrl('personal');
										else if ($field == "work_webpage") $row_values[$field] = $contact->getWebpageUrl('work');
										else if ($field == "other_webpage") $row_values[$field] = $contact->getWebpageUrl('other');
									}
									if (in_array($field, array("home_address", "work_address", "other_address"))) {
										if ($field == "home_address") $row_values[$field] = $contact->getStringAddress('home');
										else if ($field == "work_address") $row_values[$field] = $contact->getStringAddress('work');
										else if ($field == "other_address") $row_values[$field] = $contact->getStringAddress('other');
									}
								}
							} else if($ot->getHandlerClass() == 'MailContents') {
								if (in_array($field, array('to', 'cc', 'bcc', 'body_plain', 'body_html'))){
									$mail_data = MailDatas::findById($object->getId());
									$row_values[$field] = $mail_data->getColumnValue($field);
									if ($field == "body_html") {
										if (class_exists("DOMDocument")) {
											$d = new DOMDocument;
											$mock = new DOMDocument;
											$d->loadHTML(remove_css_and_scripts($row_values[$field]));
											$body = $d->getElementsByTagName('body')->item(0);
											foreach ($body->childNodes as $child){
												$mock->appendChild($mock->importNode($child, true));
											}
											// if css is inside an html comment => remove it
											$row_values[$field] = preg_replace('/<!--(.*)-->/Uis', '', remove_css($row_values[$field]));
											
										} else {
											$row_values[$field] = preg_replace('/<!--(.*)-->/Uis', '', remove_css_and_scripts($row_values[$field]));
										}
									}
								}
							}
							
							if (!$to_print && $field == "name") {
								$row_values[$field] = '<a target="new-'.$object->getId().'" href="' . $object->getViewUrl() . '">'.$value.'</a>';
							}
						}
					} else {
						
						$colCp = $column->getCustomPropertyId();
						$cp = CustomProperties::getCustomProperty($colCp);
						if ($cp instanceof CustomProperty) { /* @var $cp CustomProperty */
							
							$row_values[$cp->getName()] = get_custom_property_value_for_listing($cp, $object);
							$results['columns'][$colCp] = $cp->getName();
							$results['db_columns'][$cp->getName()] = $colCp;
							
						}
					}
				}
				
				
				Hook::fire("report_row", $object, $row_values);
				
				$report_rows[] = $row_values;
			}
			
			if (!$to_print) {
				if (is_array($results['columns'])) {
					array_unshift($results['columns'], '');
				} else {
					$results['columns'] = array('');
				}
				Hook::fire("report_header", $ot, $results['columns']);
			}
			$results['rows'] = $report_rows;
		}
		
		return $results;
	} //  executeReport
	
	function isReportColumnEmail($col){
		return preg_match(EMAIL_FORMAT, $col);
	}
	
	static function removeDuplicateRows($rows){
		$duplicateIds = array();
		foreach($rows as $row){
			if (!isset($duplicateIds[$row['id']])) $duplicateIds[$row['id']] = 0;
			$duplicateIds[$row['id']]++;
		}
		foreach($duplicateIds as $id => $count){
			if($count < 2){
				unset($duplicateIds[$id]);
			}
		}
		$duplicateIds = array_keys($duplicateIds);
		foreach($rows as $row){
			if(in_array($row['id'], $duplicateIds)){
				foreach($row as $col => $value){
					$cp = CustomProperties::getCustomProperty($col);
					if($cp instanceof CustomProperty && $cp->getIsMultipleValues()){

					}
				}
			}
		}
		return $rows;
	}

	static function getReportPagination($report_id, $params, $order_by='', $order_by_asc=true, $offset, $limit, $total){
		if($total == 0) return '';
		$a_nav = array(
			'<span class="x-tbar-page-first" style="padding-left:12px">&nbsp;</span>', 
			'<span class="x-tbar-page-prev" style="padding-left:12px">&nbsp;</span>', 
			'<span class="x-tbar-page-next" style="padding-left:12px">&nbsp;</span>', 
			'<span class="x-tbar-page-last" style="padding-left:12px">&nbsp;</span>'
		);
		$page = intval($offset / $limit);
		$totalPages = ceil($total / $limit);
		if($totalPages == 1) return '';

		$parameters = '';
		if(is_array($params) && count($params) > 0){
			foreach($params as $id => $value){
				$parameters .= '&params['.$id.']='.$value;
			}
		}
		if($order_by != ''){
			$parameters .= '&order_by='.$order_by.'&order_by_asc='.($order_by_asc ? 1 : 0);
		}
		
		$nav = '';
		if($page != 0){
			$nav .= '<a class="internalLink" href="'.get_url('reporting', 'view_custom_report', array('id' => $report_id, 'offset' => '0', 'limit' => $limit)).$parameters.'">'.sprintf($a_nav[0], $offset).'</a>';
			$off = $offset - $limit;
			$nav .= '<a class="internalLink" href="'.get_url('reporting', 'view_custom_report', array('id' => $report_id, 'offset' => $off, 'limit' => $limit)).$parameters.'">'.$a_nav[1].'</a>&nbsp;';
		}
		for($i = 1; $i < $totalPages + 1; $i++){
			$off = $limit * ($i - 1);
			if(($i != $page + 1) && abs($i - 1 - $page) <= 2 ) $nav .= '<a class="internalLink" href="'.get_url('reporting', 'view_custom_report', array('id' => $report_id, 'offset' => $off, 'limit' => $limit)).$parameters.'">'.$i.'</a>&nbsp;&nbsp;';
			else if($i == $page + 1) $nav .= '<span class="bold">'.$i.'</span>&nbsp;&nbsp;';
		}
		if($page < $totalPages - 1){
			$off = $offset + $limit;
			$nav .= '<a class="internalLink" href="'.get_url('reporting', 'view_custom_report', array('id' => $report_id, 'offset' => $off, 'limit' => $limit)).$parameters.'">'.$a_nav[2].'</a>';
			$off = $limit * ($totalPages - 1);
			$nav .= '<a class="internalLink" href="'.get_url('reporting', 'view_custom_report', array('id' => $report_id, 'offset' => $off, 'limit' => $limit)).$parameters.'">'.$a_nav[3].'</a>';
		}
		return $nav . "<br/><span class='desc'>&nbsp;".lang('total').": $totalPages ".lang('pages').'</span>';
	}


	private static $external_columns = array('user_id', 'contact_id', 'assigned_to_contact_id', 'assigned_by_id', 'completed_by_id', 'approved_by_id', 'milestone_id', 'company_id');
	function getExternalColumnValue($field, $id, $manager = null, $object = null){
		$value = '';
		if($field == 'user_id' || $field == 'contact_id' || $field == 'created_by_id' || $field == 'updated_by_id' || $field == 'assigned_to_contact_id' || $field == 'assigned_by_id' || $field == 'completed_by_id'|| $field == 'approved_by_id'){
			$contact = Contacts::findById($id);
			if($contact instanceof Contact) $value = $contact->getObjectName();
		} else if($field == 'milestone_id'){
			$milestone = ProjectMilestones::findById($id);
			if($milestone instanceof ProjectMilestone) $value = $milestone->getObjectName();
		} else if($field == 'company_id'){
			$company = Contacts::findById($id);
			if($company instanceof Contact && $company->getIsCompany()) $value = $company->getObjectName();
		} else if($field == 'rel_object_id'){
			$value = $id;
		} else if ($manager instanceof ContentDataObjects) {
			$value = $manager->getExternalColumnValue($field, $id);
		}
		
		Hook::fire('custom_reports_get_external_column_value', array('field' => $field, 'external_id' => $id, 'manager' => $manager, 'object' => $object), $value);
		
		return $value;
	}

} // Reports

?>