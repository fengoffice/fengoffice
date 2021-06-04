<?php

/**
 *   Reports class
 *
 * 
 */
class Reports extends BaseReports {

    public function __construct() {
        parent::__construct();
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
    }
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
    }

//  getAllReportsForObjectType

    /**
     * Return all reports
     *
     * @return array
     */
    static function getAllReports() {
        return self::findAll();
    }

//  getAllReports

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
            if ($add)
                $to_merge_reports[] = $icr;
        }
        $reports = array_merge($reports, $to_merge_reports);
        $reports = feng_sort($reports, "getObjectName");

        $result = array();
        foreach ($reports as $report) {
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
        if (in_array($operator, array('=', '<>'))) {
            $cond = " $operator '$value'";
        } else if (in_array($operator, array('like', 'not like'))) {
            $cond = " $operator '%$value%'";
        } else {
            $cond = " like '$value%'";
        }

        // build condition
        switch ($field) {
            case 'is_user':
                return '`user_type` ' . ($value == '1' ? '>' : '=') . ' 0';
            case 'email_address':
                return 'o.id IN (select ce.contact_id from ' . TABLE_PREFIX . 'contact_emails ce where ce.contact_id=o.id and ce.email_address ' . $cond . ')';
            case 'mobile_phone':
            case 'work_phone':
            case 'home_phone':
                $type_name = ($field == 'mobile_phone' ? 'mobile' : ($field == 'work_phone' ? 'work' : 'home'));
                $type_cond = " AND ce.telephone_type_id in (select t.id from " . TABLE_PREFIX . "telephone_types t where t.name='$type_name')";
                return 'o.id IN (select ce.contact_id from ' . TABLE_PREFIX . 'contact_telephones ce where ce.contact_id=o.id and ce.number ' . $cond . $type_cond . ')';
            case 'personal_webpage':
            case 'work_webpage':
            case 'other_webpage':
                $type_name = ($field == 'personal_webpage' ? 'personal' : ($field == 'work_webpage' ? 'work' : 'other'));
                $type_cond = " AND ce.web_type_id in (select t.id from " . TABLE_PREFIX . "webpage_types t where t.name='$type_name')";
                return 'o.id IN (select ce.contact_id from ' . TABLE_PREFIX . 'contact_web_pages ce where ce.contact_id=o.id and ce.url ' . $cond . $type_cond . ')';
            case 'im_values':
                return 'o.id IN (select ce.contact_id from ' . TABLE_PREFIX . 'contact_im_values ce where ce.contact_id=o.id and ce.value ' . $cond . ')';
            case 'home_address':
            case 'work_address':
            case 'other_address':
            case 'postal_address':
                $type_name = ($field == 'home_address' ? 'home' : ($field == 'work_address' ? 'work' : ($field == 'postal_address' ? 'postal' : 'other')));
                $type_cond = " AND ce.address_type_id in (select t.id from " . TABLE_PREFIX . "address_types t where t.name='$type_name')";
                return "o.id IN (select ce.contact_id from " . TABLE_PREFIX . "contact_addresses ce where ce.contact_id=o.id " . $type_cond . " and (
					ce.street " . $cond . " or ce.city " . $cond . " or ce.state " . $cond . " or ce.country " . $cond . " or ce.zip_code " . $cond . "))";
            default:
                return '';
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
                $join_params['table'] = TABLE_PREFIX . "contact_emails";
                $select_columns = array("DISTINCT o.*", "e.*");
                break;
            case 'mobile_phone':
            case 'work_phone':
            case 'home_phone':
                $order = 'IF(ISNULL(jt.number),1,0),jt.number';
                $join_params['table'] = TABLE_PREFIX . "contact_telephones";
                $select_columns = array("DISTINCT o.*", "e.*");
                break;
            case 'personal_webpage':
            case 'work_webpage':
            case 'other_webpage':
                $order = 'IF(ISNULL(jt.url),1,0),jt.url';
                $join_params['table'] = TABLE_PREFIX . "contact_web_pages";
                $select_columns = array("DISTINCT o.*", "e.*");
                break;
            case 'im_values':
                $order = 'IF(ISNULL(jt.value),1,0),jt.value';
                $join_params['table'] = TABLE_PREFIX . "contact_im_values";
                $select_columns = array("DISTINCT o.*", "e.*");
                break;
            case 'home_address':
            case 'work_address':
            case 'other_address':
            case 'postal_address':
                $order = 'IF(ISNULL(jt.street) and ISNULL(jt.city) and ISNULL(jt.state) and ISNULL(jt.country) and ISNULL(jt.zip_code),1,0), jt.street, jt.city, jt.state, jt.country, jt.zip_code';
                $join_params['table'] = TABLE_PREFIX . "contact_addresses";
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
            "personal_webpage", "work_webpage", "other_webpage", "home_address", "work_address", "other_address", "postal_address");
    }

    /**
     * Execute a report and return results
     *
     * @param $id
     * @param $params
     *
     * @return array
     */
    static function executeReport($id, $params, $order_by_col = '', $order_by_asc = true, $offset = 0, $limit = 50, $to_print = false) {
        if (is_null(active_context())) {
            CompanyWebsite::instance()->setContext(build_context_array(array_var($_REQUEST, 'context')));
        }
        $results = array();
        $report = self::getReport($id);
        $show_archived = false;
        if ($report instanceof Report) {
            $ot = ObjectTypes::findById($report->getReportObjectTypeId());
            $table = $ot->getTableName();

            $contact_ot = ObjectTypes::findByName('contact');

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


            eval('$managerInstance = ' . $ot->getHandlerClass() . "::instance();");
            eval('$item_class = ' . $ot->getHandlerClass() . '::instance()->getItemClass(); $object = new $item_class();');

            $order_by = '';
            if (is_object($params)) {
                $params = get_object_vars($params);
            }

            $report_columns = ReportColumns::getAllReportColumns($id);

            Hook::fire('remove_billing_columns_from_view', array('object_type' => $ot),$report_columns);
            $contact_extra_columns = self::get_extra_contact_columns();



            $cond_sql_obj = build_report_conditions_sql(array('report' => $report, 'params' => $params));
            $allConditions = array_var($cond_sql_obj, 'all_conditions');

            $select_columns = array('*');
            $join_params = null;
            if ($order_by_col == '') {
                $order_by_col = $report->getOrderBy();
            }

            if ($ot->getHandlerClass() == 'Contacts' && in_array($order_by_col, $contact_extra_columns)) {
                $join_params = self::get_extra_contact_column_order_by($order_by_col, $order_by_col, $select_columns);
            } else if ($ot->getHandlerClass() == 'MailContents') {
                $join_params = array(
                    'table' => TABLE_PREFIX . "mail_datas",
                    'jt_field' => 'id',
                    'e_field' => 'object_id',
                    'join_type' => 'inner'
                );
            }
            if ($ot->getHandlerClass() == 'IncomeInvoices') {
                $join_params = array(
                    'table' => TABLE_PREFIX . "currencies",
                    'jt_field' => 'id',
                    'e_field' => 'currency_id',
                    'join_type' => 'inner'
                );
                $select_columns = array('o.*,e.*', 'jt.id as cur_id');
            }

            // add object_billing join
            Hook::fire('get_additional_sql_join', array('object_class' => $ot->getHandlerClass()),$join_params);
            Hook::fire('get_additional_sql_select', array('object_class' => $ot->getHandlerClass()),$select_columns);

            $original_order_by_col = $order_by_col;
            $cp_order = null;
            $join_str = '';

            if (in_array($order_by_col, self::$external_columns)) {
                $order_by_col = 'name_order';
                //$original_order_by_col = "e.$original_order_by_col";
                
                $select_columns = array();
                if (is_null($join_params)) {
	                $join_params = array(
	                    'table' => Objects::instance()->getTableName(),
	                    'jt_field' => 'id',
	                	'e_field' => $original_order_by_col,
	                    'join_type' => 'left'
	                );
	                $select_columns[] = 'jt.name as name_order';
                } else {
                	if (!isset($join_params['on_extra'])) $join_params['on_extra'] = '';
                	$join_params['on_extra'] .= "
                		INNER JOIN ".TABLE_PREFIX."objects order_table ON order_table.id = e.$original_order_by_col
                	";
                	$select_columns[] = 'order_table.name as name_order';
                }
                $tmp_cols = $managerInstance->getColumns();
                foreach ($tmp_cols as $col)
                    $select_columns[] = "e.$col";
                $tmp_cols = Objects::instance()->getColumns();
                foreach ($tmp_cols as $col)
                    $select_columns[] = "o.$col";
                
            } else {
                if (in_array($order_by_col, $managerInstance->getColumns())) {
                    $original_order_by_col = "e.$order_by_col";
                } else if (in_array($order_by_col, Objects::instance()->getColumns())) {
                    $original_order_by_col = "o.$order_by_col";
                } else if (is_numeric($order_by_col)) {
                    //when is ordering by CP
                    $cp = CustomProperties::instance()->findById($order_by_col);
                    if ($cp instanceof CustomProperty &&
                            !in_array($cp->getType(), CustomProperties::instance()->getNonOrderableColumnTypes())) {

                        //when is not grouping, will go to listing with this parameters   
                        $cp_order = $cp->getId();
                        $original_order_by_col = 'customProp';

                        //only when report have group by
                        if ($report->getColumnValue('group_by', '') != '') {
                        	
                        	$join_str = null;
                        	if ($ot->getName() == 'timeslot' && $cp->getObjectTypeId() == $contact_ot->getId()) {
                        		$join_str = " LEFT JOIN " . TABLE_PREFIX . "custom_property_values cpropval ON cpropval.object_id=e.contact_id
					        		AND (cpropval.custom_property_id=" . $cp->getId() . " OR cpropval.custom_property_id IS NULL) ";
                        	}

                        	if (!$join_str) {
                            	$join_str = " LEFT JOIN " . TABLE_PREFIX . "custom_property_values cpropval ON cpropval.object_id=o.id
					        		AND (cpropval.custom_property_id=" . $cp->getId() . " OR cpropval.custom_property_id IS NULL) ";
                        	}
                        	
                            //if is grouping by CP numeric, convert value to INTEGER to order correctly by numeric
                            if ($cp->getType() == 'numeric') {
                                $cp_concat_string = "CONVERT(cpropval.value,SIGNED INTEGER)";
                            } else {
                                $cp_concat_string = "cpropval.value";
                            }

                            $more_select_columns = ", $cp_concat_string as customProp";
                        }
                    }
                } else {

                    $new_order_params = null;
                    Hook::fire('custom_report_override_order_column', array('report' => $report, 'ot' => $ot, 'order_by_col' => $order_by_col), $new_order_params);

                    if (!is_null($new_order_params)) {
                        $order_by_col = $new_order_params['order_by_col'];
                        $original_order_by_col = $new_order_params['order_by_col'];
                        $join_params = $new_order_params['join_params'];
                    } else {
                        $order_by_col = "name";
                        $original_order_by_col = "o.name";
                    }
                }
            }
            if ($order_by_asc == null)
                $order_by_asc = $report->getIsOrderByAsc();

            if ($ot->getName() == 'task' && !SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks')) {
                $allConditions .= " AND  e.assigned_to_contact_id = " . logged_user()->getId();
            }

            if ($ot->getName() == 'timeslot') {
                $allConditions .= " AND   (e.rel_object_id=0 OR (SELECT aux.trashed_by_id FROM " . TABLE_PREFIX . "objects aux WHERE aux.id=e.rel_object_id)=0) ";
                if(!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_others_timeslots')){
                    $allConditions .= " AND e.contact_id = " . logged_user()->getId();
                }
            }

            if ($ot->getName() == 'payment_receipt' && !SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_expenses_of_others')) {
                $allConditions .= " AND e.paid_by_id = " . logged_user()->getId();
            }

            Hook::fire('custom_report_extra_conditions', array('report' => $report), $allConditions);
            
            $report_options = array(
                'report' => $report,
                'order' => $original_order_by_col,
                'order_dir' => $order_by_asc ? 'ASC' : 'DESC',
                'offset' => $offset,
                'limit' => $limit,
                'conditions' => $allConditions,
                'select_columns' => $select_columns,
                'join_params' => $join_params,
                'join_str' => $join_str,
                'more_select_columns' => $more_select_columns
            );
            $results = null;
            Hook::fire('execute_object_custom_report', $report_options, $results);

            if (is_null($results)) {
                $results = array();
                Hook::fire('additional_totals_column_as_array', array('object_type' => $ot), $select_columns);
                if ($managerInstance) {
                    if ($order_by_col == "order") {
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
                    if (is_numeric($order_by_col)) {
                        $listing_parameters['cp_order'] = $order_by_col;
                    }

                    if ($limit > 0) {
                        $listing_parameters["start"] = $offset;
                        $listing_parameters["limit"] = $limit;
                    }
                    if ($show_archived) {
                        $listing_parameters["archived"] = true;
                    }
                    $result = $managerInstance->listing($listing_parameters);
                } else {
                    // TODO Performance Killer
                    $result = ContentDataObjects::getContentObjects(active_context(), $ot, $order_by_col, ($order_by_asc ? "ASC" : "DESC"), $allConditions);
                }
                $objects = $result->objects;
                $totalResults = $result->total;
                if (isset($result->totals)) {
                    $results['totals'] = $result->totals;
                }

                $use_obj_id_as_row_key = false;
            } else {

                $objects = array_var($results, 'objects');
                $totalResults = array_var($results, 'total');

                $use_obj_id_as_row_key = array_var($results, 'use_obj_id_as_row_key');
            }

            if (!isset($results['pagination'])) {
                $results['pagination'] = Reports::getReportPagination($id, $params, $original_order_by_col, $order_by_asc, $offset, $limit, $totalResults);
            }

            $dimensions_cache = array();

            $results['columns'] = array('names' => array(), 'order' => array(), 'types' => array());
            foreach ($report_columns as $column) {
                if ($column->getCustomPropertyId() == 0) {
                    $field = $column->getFieldName();
                    if (str_starts_with($field, 'dim_')) {
                        $dim_id = str_replace("dim_", "", $field);
                        $dimension = Dimensions::getDimensionById($dim_id);
                        $dimensions_cache[$dim_id] = $dimension;

                        $column_name = $dimension->getName();

                        $results['columns']['names'][$field] = $column_name;
                        $results['columns']['order'][] = $field;
                        $results['columns']['types'][$field] = DATA_TYPE_STRING;
                    } else {
                        $is_calculated_column = $managerInstance && in_array($field, $managerInstance->getCalculatedColumns());

                        if ($managerInstance->columnExists($field) || Objects::instance()->columnExists($field) || $is_calculated_column) {
                            $column_name = Localization::instance()->lang('field ' . $ot->getHandlerClass() . ' ' . $field);
                            if (is_null($column_name))
                                $column_name = lang('field Objects ' . $field);

                            $results['columns']['names'][$field] = $column_name;
                            $results['columns']['order'][] = $field;
                        }else {
                            if ($ot->getHandlerClass() == 'Contacts') {
                                if (in_array($field, $contact_extra_columns)) {
                                    $results['columns']['names'][$field] = lang($field);
                                    $results['columns']['order'][] = $field;
                                }
                            } else if ($ot->getHandlerClass() == 'Timeslots') {
                                if (in_array($field, array('time', 'billing'))) {
                                    $results['columns']['names'][$field] = lang('field Objects ' . $field);
                                    $results['columns']['order'][] = $field;
                                }
                            } else if ($ot->getHandlerClass() == 'MailContents') {
                                if (in_array($field, array('to', 'cc', 'bcc', 'body_plain', 'body_html'))) {
                                    $results['columns']['names'][$field] = lang('field Objects ' . $field);
                                    $results['columns']['order'][] = $field;
                                }
                            }
                        }
                        if ($is_calculated_column) {
                            $results['columns']['types'][$field] = 'calculated';
                        } else {
                            $results['columns']['types'][$field] = $managerInstance->getColumnType($field);
                        }

                        Hook::fire('custom_reports_fixed_additional_columns_def', array('object_type' => $ot, 'field' => $field), $results);
                    }

                    Hook::fire('get_columns_to_header_report', array('field' => $field, 'object_type' => $ot),$results);
                    Hook::fire('get_taxes_columns_to_header_report', array('field' => $field, 'object_type' => $ot),$results);

                } else {

                    $cp = CustomProperties::getCustomProperty($column->getCustomPropertyId());
                    if ($cp instanceof CustomProperty) {
                        $results['columns']['names'][$column->getCustomPropertyId()] = $cp->getName();
                        $results['columns']['order'][] = $column->getCustomPropertyId();
                        $results['columns']['types'][$column->getCustomPropertyId()] = $cp->getType();
                    }
                }

            }
            Hook::fire('get_more_columns_to_header_report', array('object_type' => $ot,'report'=>$report),$results);

            $report_rows = array();
            foreach ($objects as &$object) {/* @var $object Object */
                $obj_name = $object->getObjectName();
                $icon_class = $object->getIconClass();

                $row_values = array('object_type_id' => $object->getObjectTypeId());

                $tz_offset = Timezones::getTimezoneOffsetToApply($object, logged_user());
                $row_values['tz_offset'] = $tz_offset;

                if (!$to_print) {
                    $row_values['link'] = '<a class="link-ico ' . $icon_class . '" title="' . clean($obj_name) . '" target="new" href="' . $object->getViewUrl() . '">&nbsp;</a>';
                }
                foreach ($report_columns as $column) {
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

                            //$om_object_id = $object instanceof Timeslot ? $object->getRelObjectId() : $object->getId();
                            $om_object_id = $object->getId();
                            $members = ObjectMembers::getMembersByObjectAndDimension($om_object_id, $dim_id, " AND om.is_optimization=0");

                            $value = "";
                            foreach ($members as $member) {/* @var $member Member */
                                $val = $member->getPath();
                                $val .= ($val == "" ? "" : "/") . $member->getName();

                                if ($value != "")
                                    $val = " - $val";
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
                                    if ($field == 'name' || in_array($field, ProjectTasks::instance()->getColumns()) || in_array($field, ProjectTasks::instance()->getCalculatedColumns())) {
                                        $task = ProjectTasks::findById($object->getRelObjectId());
                                        // if task exists
                                        if ($task instanceof ProjectTask) {
                                            $value = $task->getColumnValue($field);
                                            // if it is an external task column
                                            if (in_array($field, ProjectTasks::instance()->getExternalColumns())) {
                                                $value = self::instance()->getExternalColumnValue($field, $value, ProjectTasks::instance());
                                            } elseif (in_array($field, ProjectTasks::instance()->getCalculatedColumns())) {
                                                if ($field == 'status') {
                                                    $value = self::getCalculatedColumnValue($report, $task, $field);
                                                }
                                            } else {
                                                // if is a date then use format
                                                if (ProjectTasks::instance()->getColumnType($field) == DATA_TYPE_DATETIME && $value instanceof DateTimeValue) {
                                                	$value = format_value_to_print($field, $value->toMySQL(), DATA_TYPE_DATETIME, ProjectTasks::instance()->getObjectTypeId());
                                                } else {
                                                    $value = format_value_to_print($field, $value, ProjectTasks::instance()->getColumnType($field), ProjectTasks::instance()->getObjectTypeId());
                                                }
                                            }
                                        }

                                        if (!isset($results['columns']['names'][$field])) {
                                            $results['columns']['names'][$field] = lang('field ProjectTasks ' . $field);
                                            $results['columns']['order'][] = $field;
                                            $results['columns']['types'][$field] = ProjectTasks::instance()->getColumnType($field);
                                        }
                                    }
                                }
                            } else {
                                $value = $object->getColumnValue($field);
                            }

                            if ($value instanceof DateTimeValue) {

                                // check if the value for this object in the same column has already been processed
                                if (isset($dates_cache) && array_var($dates_cache, $object->getId()) && array_var($dates_cache[$object->getId()], $field)) {
                                    $value = $dates_cache[$object->getId()][$field];
                                } else {
                                    $dateFormat = user_config_option('date_format');

                                    if ($object->getColumnType($field) == DATA_TYPE_DATETIME) {
                                        $dateFormat .= " " . (user_config_option('time_format_use_24') ? "G:i" : "g:i A");
                                    }

                                    $add_timezone_offset = true;
                                    if ($object instanceof ProjectTask) {
                                        if (($field == 'due_date' && !$object->getUseDueTime()) || ($field == 'start_date' && !$object->getUseStartTime())) {
                                            $dateFormat = user_config_option('date_format');
                                            $add_timezone_offset = false;
                                        }
                                    }

                                    if ($object instanceof IncomeInvoice) {
                                        if ($field == 'expiration_date') {
                                            $add_timezone_offset = false;
                                        }
                                    }

                                    $dt = new DateTimeValue($value->getTimestamp());
                                    if ($add_timezone_offset) {
                                        $dt->add('s', $tz_offset);
                                    }
                                    $value = $dt->format($dateFormat);

                                    if (!isset($dates_cache))
                                        $dates_cache = array();
                                    if (!isset($dates_cache[$object->getId()]))
                                        $dates_cache[$object->getId()] = array();
                                    $dates_cache[$object->getId()][$field] = $value;
                                }
                            }
                            if (in_array($field, $managerInstance->getExternalColumns())) {

                                if ($object instanceof Timeslot && $field == 'time') {

                                    $value = $object->getEndTime()->getTimestamp() - $object->getStartTime()->getTimestamp() - $object->getSubtract();
                                    $value = floor($value / 60); // format_value_to_print requires time in minutes
                                    $value = format_value_to_print('time', $value, DATA_TYPE_INTEGER, $report->getReportObjectTypeId());
                                } else {
                                    $value = self::instance()->getExternalColumnValue($field, $value, $managerInstance);
                                }
                            } else if (in_array($field, $managerInstance->getCalculatedColumns())) {

                                $value = self::getCalculatedColumnValue($report, $object, $field);
                            } else if ($field != 'link') {
                                //$value = html_to_text(html_entity_decode($value));
                                if ($object->getColumnType($field) == DATA_TYPE_STRING) {
                                    // change html block end tags and brs to \n, then remove all other html tags, then replace \n with <br>, to remove all styles and keep the enters
                                    $value = str_replace(array("</div>", "</p>", "<br>", "<br />", "<br/>"), "\n", $value);
                                    $value = nl2br(strip_tags($value));
                                }
                            }
                            if (self::isReportColumnEmail($value)) {
                                if (logged_user()->hasMailAccounts()) {
                                    $value = '<a class="internalLink" href="' . get_url('mail', 'add_mail', array('to' => clean($value))) . '">' . clean($value) . '</a></div>';
                                } else {
                                    $value = '<a class="internalLink" target="_self" href="mailto:' . clean($value) . '">' . clean($value) . '</a></div>';
                                }
                            }



                            Hook::fire('get_value_columns_to_body_report', array('field' => $field,'object'=>$object),$value);
                            Hook::fire('get_taxes_value_columns_to_body_report', array('field' => $field,'object'=>$object),$value);

                            Hook::fire('custom_reports_override_column_format', array('field' => $field, 'manager' => $managerInstance, 'object' => $object,'report'=>$report), $value);
                            $row_values[$field] = $value;

                            if ($ot->getHandlerClass() == 'Contacts') {
                                if ($managerInstance instanceof Contacts) {
                                    $contact = Contacts::findOne(array("conditions" => "object_id = " . $object->getId()));
                                    if ($field == "email_address") {
                                        $row_values[$field] = $contact->getEmailAddress();
                                    }
                                    if ($field == "is_user") {
                                        $row_values[$field] = $contact->getUserType() > 0 && !$contact->getIsCompany();
                                    }
                                    if ($field == "im_values") {
                                        $str = "";
                                        foreach ($contact->getAllImValues() as $type => $value) {
                                            $str .= ($str == "" ? "" : " | ") . "$type: $value";
                                        }
                                        $row_values[$field] = $str;
                                    }
                                    if (in_array($field, array("mobile_phone", "work_phone", "home_phone"))) {
                                        if ($field == "mobile_phone")
                                            $phone_type = 'mobile';
                                        else if ($field == "work_phone")
                                            $phone_type = 'work';
                                        else if ($field == "home_phone")
                                            $phone_type = 'home';

                                        $row_values[$field] = $contact->getPhoneNumber($phone_type, null, false);
                                        if (!$row_values[$field] && $contact->getCompanyId() > 0 && config_option('reports_inherit_company_phones')) {
                                            $company = $contact->getCompany();
                                            if ($company instanceof Contact) {
                                                $row_values[$field] = $company->getPhoneNumber($phone_type, null, false);
                                            }
                                        }
                                    }
                                    if (in_array($field, array("personal_webpage", "work_webpage", "other_webpage"))) {
                                        if ($field == "personal_webpage")
                                            $webpage_type = 'personal';
                                        else if ($field == "work_webpage")
                                            $webpage_type = 'work';
                                        else if ($field == "other_webpage")
                                            $webpage_type = 'other';

                                        $row_values[$field] = $contact->getWebpageUrl($webpage_type);
                                    }
                                    if (str_ends_with($field, "_address") && $field != 'email_address') {
                                        $address_type = str_replace("_address", "", $field);

                                        $row_values[$field] = $contact->getStringAddress($address_type);
                                        if (!$row_values[$field] && $contact->getCompanyId() > 0 && config_option('reports_inherit_company_address')) {
                                            $company = $contact->getCompany();
                                            if ($company instanceof Contact) {
                                                $row_values[$field] = $company->getStringAddress($address_type);
                                            }
                                        }
                                    }
                                }
                            } else if ($ot->getHandlerClass() == 'MailContents') {
                                if (in_array($field, array('to', 'cc', 'bcc', 'body_plain', 'body_html'))) {
                                    $mail_data = MailDatas::findById($object->getId());
                                    $row_values[$field] = $mail_data->getColumnValue($field);
                                    if ($field == "body_html") {
                                        if (class_exists("DOMDocument")) {
                                            $d = new DOMDocument;
                                            $mock = new DOMDocument;
                                            $d->loadHTML(remove_css_and_scripts($row_values[$field]));
                                            $body = $d->getElementsByTagName('body')->item(0);
                                            foreach ($body->childNodes as $child) {
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
                                $row_values[$field] = '<a target="new-' . $object->getId() . '" href="' . $object->getViewUrl() . '">' . $value . '</a>';
                            }
                        }



                    } else {

                        $colCp = $column->getCustomPropertyId();
                        $cp = CustomProperties::getCustomProperty($colCp);
                        if ($cp instanceof CustomProperty) { /* @var $cp CustomProperty */

                        	if ($ot->getName() == 'timeslot' && $cp->getObjectTypeId() == $contact_ot->getId()) {
                        		$object_contact = Contacts::findById($object->getContactId());
                        		$row_values[$cp->getId()] = get_custom_property_value_for_listing($cp, $object_contact);
                        		
                        	} else if ($ot->getName() == 'timeslot' && $cp->getObjectTypeId() != Timeslots::instance()->getObjectTypeId()) {
                        		$cp_ot = ObjectTypes::findById($cp->getObjectTypeId());
                        		if ($cp_ot instanceof ObjectType && $cp_ot->getType() == 'dimension_object') {
                        			$obj_members = $object->getMembers();
                        			$member = null;
                        			foreach ($obj_members as $m) {
                        				if ($m instanceof Member && $m->getObjectTypeId() == $cp->getObjectTypeId()) {
                        					$member = $m;
                        					break;
                        				}
                        			}
                        			if ($member instanceof Member) {
                        				$member_object = Objects::findObject($member->getObjectId());
                        				if ($member_object instanceof ContentDataObject) {
                        					$row_values[$cp->getId()] = get_custom_property_value_for_listing($cp, $member_object);
                        				}
                        			}
                        		}
                        	} else {
	                            $row_values[$cp->getId()] = get_custom_property_value_for_listing($cp, $object);
                        	}
                        }
                    }
                }
                $row_values['id'] = $object->getId();

                Hook::fire("report_results_more_columns_value", array('report' => $report, 'object'=>$object), $row_values);

                if ($use_obj_id_as_row_key) {
                    $report_rows[$object->getId()] = $row_values;
                } else {
                    $report_rows[] = $row_values;
                }
            }
            if (!$to_print) {
                if (is_array($results['columns']['names'])) {
                    $results['columns']['names']['link'] = '';
                    array_unshift($results['columns']['order'], 'link');
                } else {
                    $results['columns']['names'] = array('');
                    $results['columns']['order'] = array('link');
                }
            }

            $results['rows'] = $report_rows;

            Hook::fire("report_results_more_data", array('report' => $report, 'objects' => $objects), $results);
        }

        return $results;
    }

//  executeReport

    function isReportColumnEmail($col) {
        return preg_match(EMAIL_FORMAT, $col);
    }

    static function removeDuplicateRows($rows) {
        $duplicateIds = array();
        foreach ($rows as $row) {
            if (!isset($duplicateIds[$row['id']]))
                $duplicateIds[$row['id']] = 0;
            $duplicateIds[$row['id']] ++;
        }
        foreach ($duplicateIds as $id => $count) {
            if ($count < 2) {
                unset($duplicateIds[$id]);
            }
        }
        $duplicateIds = array_keys($duplicateIds);
        foreach ($rows as $row) {
            if (in_array($row['id'], $duplicateIds)) {
                foreach ($row as $col => $value) {
                    $cp = CustomProperties::getCustomProperty($col);
                    if ($cp instanceof CustomProperty && $cp->getIsMultipleValues()) {

                    }
                }
            }
        }
        return $rows;
    }

    static function getReportPagination($report_id, $params, $order_by = '', $order_by_asc = true, $offset, $limit, $total) {
        if ($total == 0 || $limit == 0)
            return '';
        $a_nav = array(
            '<span class="x-tbar-page-first" style="padding-left:12px">&nbsp;</span>',
            '<span class="x-tbar-page-prev" style="padding-left:12px">&nbsp;</span>',
            '<span class="x-tbar-page-next" style="padding-left:12px">&nbsp;</span>',
            '<span class="x-tbar-page-last" style="padding-left:12px">&nbsp;</span>'
        );
        $page = intval($offset / $limit);
        $totalPages = ceil($total / $limit);
        if ($totalPages == 1)
            return '';

        $parameters = '';
        if (is_array($params) && count($params) > 0) {
            foreach ($params as $id => $value) {
                $parameters .= '&params[' . $id . ']=' . $value;
            }
        }
        if ($order_by != '') {
            $parameters .= '&order_by=' . $order_by . '&order_by_asc=' . ($order_by_asc ? 1 : 0);
        }

        $nav = '';
        if ($page != 0) {
            $nav .= '<a class="internalLink" href="#" onclick="og.reports.go_to_custom_report_page({offset:0, limit:' . $limit . ', link:this});">' . sprintf($a_nav[0], $offset) . '</a>';

            $off = $offset - $limit;
            $nav .= '<a class="internalLink" href="#" onclick="og.reports.go_to_custom_report_page({offset:' . $off . ', limit:' . $limit . ', link:this});">' . $a_nav[1] . '</a>&nbsp;';
        }
        for ($i = 1; $i < $totalPages + 1; $i++) {
            $off = $limit * ($i - 1);
            if (($i != $page + 1) && abs($i - 1 - $page) <= 2) {
                $nav .= '<a class="internalLink" href="#" onclick="og.reports.go_to_custom_report_page({offset:' . $off . ', limit:' . $limit . ', link:this});">' . $i . '</a>&nbsp;&nbsp;';
            } else if ($i == $page + 1)
                $nav .= '<span class="bold">' . $i . '</span>&nbsp;&nbsp;';
        }
        if ($page < $totalPages - 1) {
            $off = $offset + $limit;
            $nav .= '<a class="internalLink" href="#" onclick="og.reports.go_to_custom_report_page({offset:' . $off . ', limit:' . $limit . ', link:this});">' . $a_nav[2] . '</a>';

            $off = $limit * ($totalPages - 1);
            $nav .= '<a class="internalLink" href="#" onclick="og.reports.go_to_custom_report_page({offset:' . $off . ', limit:' . $limit . ', link:this});">' . $a_nav[3] . '</a>';
        }
        return '<div class="pagination-div">'. $nav . "&nbsp;<span class='desc'>&nbsp;" . lang('total') . ": $totalPages " . lang('pages') . '</span></div>';
    }

    private static $external_columns = array('user_id', 'contact_id', 'assigned_to_contact_id', 'assigned_by_id', 'completed_by_id', 'approved_by_id', 'milestone_id', 'company_id', 'rel_object_id');

    function getExternalColumnValue($field, $id, $manager = null, $object = null) {
        $value = '';
        if ($field == 'user_id' || $field == 'contact_id' || $field == 'created_by_id' || $field == 'updated_by_id' || $field == 'assigned_to_contact_id' || $field == 'assigned_by_id' || $field == 'completed_by_id' || $field == 'approved_by_id') {
            $contact = Contacts::findById($id);
            if ($contact instanceof Contact)
                $value = $contact->getObjectName();
        } else if ($field == 'milestone_id') {
            $milestone = ProjectMilestones::findById($id);
            if ($milestone instanceof ProjectMilestone)
                $value = $milestone->getObjectName();
        } else if ($field == 'company_id') {
            $company = Contacts::findById($id);
            if ($company instanceof Contact && $company->getIsCompany())
                $value = $company->getObjectName();
        } else if ($field == 'rel_object_id') {
            $obj = Objects::findObject($id);
            $value = $obj instanceof ContentDataObject ? $obj->getObjectName() : "";
        } else if ($manager instanceof ContentDataObjects) {
            $value = $manager->getExternalColumnValue($field, $id);
        }

        Hook::fire('custom_reports_get_external_column_value', array('field' => $field, 'external_id' => $id, 'manager' => $manager, 'object' => $object), $value);

        return $value;
    }

    static function getCalculatedColumnValue($report, $object, $column) {
        $value = "";
        Hook::fire("get_calculated_column_value", array('report' => $report, 'object' => $object, 'column' => $column), $value);

        return $value;
    }

}

// Reports
?>