<?php

function render_report_header_button($button_data) {
	
	echo input_field(array_var($button_data, 'name'), array_var($button_data, 'text'), array(
			'id' => array_var($button_data, 'id', ''),
			'type' => 'button',
			'onclick' => array_var($button_data, 'onclick', 'return true;'),
			'class' => "report-header-button " . array_var($button_data, 'iconcls')
	));
}

function report_values_to_arrays($results, $report) {
	if (!isset($results['group_by_criterias']) || count($results['group_by_criterias']) == 0) {
		return report_values_to_arrays_plain($results, $report);
	}
	
	$groups = $results['grouped_rows'];
	
	$headers = array();
	$columns = array_var($results, 'columns');
	foreach ($columns['order'] as $col) {
		if ($col == 'object_type_id' || $col == 'link') continue;
		$headers[] = $columns['names'][$col];
	}
	
	$all_report_rows = array();
	foreach($groups as $g) {
		$group_rows = get_report_grouped_values_as_array($g, $results, $report, 0);
		$all_report_rows = array_merge($all_report_rows, $group_rows);
	}
	
	return array('headers' => $headers, 'values' => $all_report_rows);
}



function report_values_to_arrays_plain($results, $report, $with_header = true) {
	$columns = array_var($results, 'columns');
	$rows = array_var($results, 'rows');
	$pagination = array_var($results, 'pagination');
	
	$ot = ObjectTypes::findById($report->getReportObjectTypeId());
	
	$headers = array();
	if ($with_header) {
		foreach ($columns['order'] as $col) {
			if ($col == 'object_type_id' || $col == 'link') continue;
			$headers[] = $columns['names'][$col];
		}
	}
	
	$all_data_rows = array();
	foreach($rows as $row) {
		$values_array = array();
	
		foreach ($columns['order'] as $col) {
			if ($col == 'object_type_id' || $col == 'link') continue;
	
			$value = array_var($row, $col);
				
			$val_type = array_var($types, $col);
			$date_format = is_numeric($col) ? "Y-m-d" : user_config_option('date_format');
				
			if ($val_type == 'DATETIME') {
				$formatted_val = $value;
			} else {
				$formatted_val = format_value_to_print($col, $value, $val_type, array_var($row, 'object_type_id'), '', $date_format);
			}
			if ($formatted_val == '--') $formatted_val = "";
				
			$formatted_val = strip_tags($formatted_val);
				
			$values_array[] = $formatted_val;
		}
		
		$all_data_rows[] = $values_array;
	}
	
	$add_data_rows = array();
	Hook::fire('get_additional_report_rows', array('results' => $results, 'report_id' => $report->getId()), $add_data_rows);
	if (count($all_data_rows) > 0) {
		$all_data_rows = array_merge($all_data_rows, $add_data_rows);
	}
	
	return array('headers' => $headers, 'values' => $all_data_rows);
}

function report_table_csv($results, $report) {
	
	$all_data = report_values_to_arrays($results, $report);
	
	$headers = array_var($all_data, 'headers');
	$all_data_rows = array_var($all_data, 'values');
	
	$all_csv_rows = array();
	foreach ($all_data_rows as $r) {
		$r = str_replace(array("\r\n","\r","\n"), " ", $r);
		$all_csv_rows[] = '"'. implode('","', $r) .'"';
	}
	
	$csv = '"'. implode('","', $headers) .'"' . "\n";
	$csv .= implode("\n", $all_csv_rows);
	
	return $csv;
}



function report_table_html_plain($results, $report, $parametersUrl="", $to_print=false) {
	ob_start();
	
	$columns = array_var($results, 'columns');
	$rows = array_var($results, 'rows');
	$pagination = array_var($results, 'pagination');
	
	$ot = ObjectTypes::findById($report->getReportObjectTypeId());
	?>
	<table class="report">
		<tr>
	<?php
	
		$last_order_by = array_var($_REQUEST, 'order_by', $report->getOrderBy());
		$last_order_by_asc = array_var($_REQUEST, 'order_by_asc', $report->getIsOrderByAsc());
		
		foreach ($columns['order'] as $col) {
			$sorted = false;
			$asc = true;
			
			if($col != 'link' && $col != 'located_under' && $col == $last_order_by) {
				$sorted = true;
				$asc = !$last_order_by_asc;
			}
		?>
			<td style="padding-right:10px;border-bottom:1px solid #666" class="bold">
		<?php 
			if ($to_print) {
				
				echo clean(array_var($columns['names'], $col));
				
			} else if($col != 'link') {
				
			  	$allow_link = true;
			  	if ($ot instanceof Timeslot && in_array($col, ProjectTasks::instance()->getColumns())) {
			  		$allow_link = false;
			  	}
			  	$echo_link = $allow_link && !(is_numeric($col) || str_starts_with($col, "dim_") || $col == 'time' || $col == 'billing');
			  	?>
				<a href="#" onclick="<?php echo $echo_link ? "og.reorderCustomReport(this, ".$report->getId().",'$col',".($asc ? '1' : '0').");" : "#" ?>" <?php echo ($echo_link ? "" : 'style="cursor:default;"') ?>>
					<?php echo clean(array_var($columns['names'], $col)) ?>
				</a>
		<?php }
			  if(!$to_print && $sorted){ ?>
				<span class="db-ico ico-<?php echo !$asc ? 'asc' : 'desc' ?>" style="padding:2px 0 0 18px;">&nbsp;</span>
		<?php } ?>
			</td>
		<?php 
		}
		?>
		</tr>
	<?php
		$isAlt = true; 
		foreach($rows as $row) {
			$isAlt = !$isAlt;
			$i = 0; 
	?>
		<tr<?php echo ($isAlt ? ' style="background-color:#F4F8F9"' : "");?>>
		<?php
			foreach ($columns['order'] as $col) {
				if ($col == 'object_type_id') continue;
				
				$value = array_var($row, $col);
				$type = array_var($columns['types'], $col);
				$numeric_type = in_array($type, array(DATA_TYPE_INTEGER, DATA_TYPE_FLOAT, 'numeric'));
		?>
			<td style="padding-right:10px;" <?php echo $numeric_type ? 'class="right"' : ''?>>
		<?php 
				$val_type = ($col == 'link' ? '' : array_var($types, $col));
				$date_format = is_numeric($col) ? "Y-m-d" : user_config_option('date_format');
				if ($val_type == 'DATETIME') {
					echo $value;
				} else {
					echo format_value_to_print($col, $value, $val_type, array_var($row, 'object_type_id'), '', $date_format);
				}
		?>
			</td>
		<?php
				$i++;
			}
		?>
		</tr>
	<?php 
		} // end foreach rows
		
		$null=null; 
		Hook::fire('render_additional_report_rows', array('results' => $results, 'report_id' => $report->getId()), $null);
	?>
	</table>
	<?php
	
	return ob_get_clean();
}



function echo_report_group_html($group_data, $results, $report, $level=0) {
	
	$columns = array_var($results, 'columns');
	
	$i = 0;
	foreach ($group_data as $gd) {
		
		if ($i == 0) {
			echo '<tr><th colspan="'.count($columns['order']).'" class="report-group-heading-'.$level.'">'.$gd['name'].'</th></tr>';
		}
		
		$i++;
		
		if (isset($gd['groups'])) {
			
			echo_report_group_html($gd['groups'], $results, $report, $level+1);
			
			if ($i == count($group_data)) {
				$null=null;
				Hook::fire('render_additional_report_group_rows', array('results' => $results, 'report' => $report, 'group' => $gd, 'level' => $level), $null);
			}
			
		} else if (isset($gd['items'])) {
			
			$rows = array_var($results, 'rows');
			$pagination = array_var($results, 'pagination');
			
			$ot = ObjectTypes::findById($report->getReportObjectTypeId());
			
			?>
			<tbody class="report-data">
			<?php
			if (!$report->getColumnValue("hide_group_details")) {
				
				$isAlt = true;
				foreach($gd['items'] as $item_data) {
					$isAlt = !$isAlt;
					$row = array_var($rows, $item_data['mid']);
					$i = 0; 
			?>
				<tr<?php echo ($isAlt ? ' style="background-color:#F4F8F9"' : "");?>>
				<?php
					foreach ($columns['order'] as $col) {
						if ($col == 'object_type_id') continue;
						
						$value = array_var($row, $col);
				?>
					<td style="padding-right:10px;" <?php echo is_numeric($value) ? 'class="right"' : ''?>>
				<?php 
						$val_type = ($col == 'link' ? '' : array_var($types, $col));
						$date_format = is_numeric($col) ? "Y-m-d" : user_config_option('date_format');
						if ($val_type == 'DATETIME') {
							echo $value;
						} else {
							echo format_value_to_print($col, $value, $val_type, array_var($row, 'object_type_id'), '', $date_format);
						}
				?>
					</td>
				<?php
						$i++;
					}
				?>
				</tr>
			<?php 
				} // end foreach rows
			}
			
			$null=null;
			Hook::fire('render_additional_report_group_rows', array('results' => $results, 'report' => $report, 'group' => $gd, 'level' => $level), $null);
				
			?></tbody><?php
		}
	}
	
	
	
	
}

function report_table_html($results, $report, $parametersUrl="", $to_print=false) {
	
	if (!isset($results['group_by_criterias']) || count($results['group_by_criterias']) == 0) {
		return report_table_html_plain($results, $report, $parametersUrl, $to_print);
	}
	
	ob_start();
	
	$groups = $results['grouped_rows'];
	
	?>
	<table class="report">
		<tr class="custom-report-table-heading">
		<?php
		$columns = array_var($results, 'columns');
		foreach ($columns['order'] as $col) {
			echo "<th>";
			if($col != 'link') {
			  	echo clean(array_var($columns['names'], $col));
			}
			echo "</th>";
		}
		?>
		</tr>
	<?php
	
	foreach($groups as $g) {
		echo_report_group_html($g, $results, $report, 0);
	}
	
	echo "<tr><td>&nbsp;</td></tr>";
	$null=null;
	Hook::fire('render_additional_report_rows', array('results' => $results, 'report_id' => $report->getId()), $null);
	
	echo "</table>";
	
	return ob_get_clean();
	
}




function get_report_grouped_values_as_array($group_data, $results, $report, $level=0) {
	$all_rows = array();

	$columns = array_var($results, 'columns');

	$i = 0;
	foreach ($group_data as $gd) {

		$row_vals = array();
		if ($i == 0) {
			$first = true;
			foreach ($columns as $c) {
				$row_vals[] = $first ? $gd['name'] : "";
				$first = false;
			}
			$all_rows[] = $row_vals;
		}
		$i++;

		if (isset($gd['groups'])) {

			$group_rows = get_report_grouped_values_as_array($gd['groups'], $results, $report, $level+1);
			$all_rows = array_merge($all_rows, $group_rows);

			if ($i == count($group_data)) {
				$row_vals=null;
				Hook::fire('get_additional_report_group_rows_csv', array('results' => $results, 'report' => $report, 'group' => $gd, 'level' => $level), $row_vals);
				$all_rows = array_merge($all_rows, $row_vals);
			}

		} else if (isset($gd['items'])) {

			$rows = array_var($results, 'rows');
			$pagination = array_var($results, 'pagination');

			$ot = ObjectTypes::findById($report->getReportObjectTypeId());

			if (!$report->getColumnValue("hide_group_details")) {

				foreach($gd['items'] as $item_data) {
					$row = array_var($rows, $item_data['mid']);
					$i = 0;

					$item_values = array();

					foreach ($columns['order'] as $col) {
						if ($col == 'object_type_id' || $col == 'link') continue;

						$value = array_var($row, $col);
						$val_type = array_var($types, $col);
						$date_format = is_numeric($col) ? "Y-m-d" : user_config_option('date_format');

						if ($val_type == 'DATETIME') {
							$formatted_val = $value;
						} else {
							$formatted_val = format_value_to_print($col, $value, $val_type, array_var($row, 'object_type_id'), '', $date_format);
						}

						if ($formatted_val == '--') $formatted_val = "";
						$formatted_val = strip_tags($formatted_val);
						$item_values[] = $formatted_val;

						$i++;
					}

					$all_rows[] = $item_values;
				}
			}

			$row_vals=null;
			Hook::fire('get_additional_report_group_rows_csv', array('results' => $results, 'report' => $report, 'group' => $gd, 'level' => $level), $row_vals);
			$all_rows = array_merge($all_rows, $row_vals);

		}
	}
	return $all_rows;
}


function build_custom_report_group_name($gbk, $row, $ot) {
	$name = array_var($row, $gbk['n']);
	
	// fixed properties
	if (str_starts_with($gbk['k'], "_group_id_fp_")) {
		$property = str_replace("_group_id_fp_", "", $gbk['k']);
		$prop_val = trim(array_var($row, $gbk['n']));
		
		switch ($property) {
			case 'priority':
				$name = lang('priority ' . $prop_val);
				break;
			default:
				eval('$managerInstance = ' . $ot->getHandlerClass() . "::instance();");
				if ($managerInstance && ($managerInstance->getColumnType($property) == DATA_TYPE_DATE || $managerInstance->getColumnType($property) == DATA_TYPE_DATETIME)) {
					if ($prop_val == EMPTY_DATETIME || $prop_val == EMPTY_DATE) {
						$name = lang('unclassified');
					} else {
						if ($managerInstance->getColumnType($col) == DATA_TYPE_DATE) {
							$name = format_datetime(DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, $prop_val));
						} else {
							$name = format_datetime(DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, $prop_val));
						}
					}
				} else {
					$name = $prop_val;
				}
		}
	}
	
	return $name;
}


function group_custom_report_results($rows, $group_by_criterias, $ot) {
	
	$linear_groups = array();

	$grouped_results = array();
	$gb_keys = array();

	foreach ($group_by_criterias as $gb) {
		switch ($gb['type']) {
			case 'association': $gkey = '_group_id_a_'.$gb['id']; break;
			case 'custom_property': $gkey = '_group_id_cp_'.$gb['id']; break;
			case 'parent_member': $gkey = '_group_id_pm_'.$gb['id']; break;
			case 'classification': $gkey = '_group_id_dim_'.$gb['id']; break;
			case 'fixed_property': $gkey = '_group_id_fp_'.$gb['id']; break;
		}
		
		if (!$gkey) $gkey = "";
		$gname = str_replace("_id_", "_name_", $gkey);
		$gb_keys[] = array('k' => $gkey, 'n' => $gname);
	}

	foreach ($rows as $row) {

		$row_g_key = "";
		$row_g_name = "";
		$original_gkey = "";
		foreach ($gb_keys as $gbk) {
			
			$gb_name = build_custom_report_group_name($gbk, $row, $ot);
			
			$row_g_key .= ($row_g_key == "" ? "" : "_") . array_var($row, $gbk['k']);
			$row_g_name .= ($row_g_name == "" ? "" : "%|%") . $gb_name;
			
			$original_gkey .= ($original_gkey == "" ? "" : ",") . $gbk['k'];
		}

		if (!isset($linear_groups[$row_g_key])) {
			$linear_groups[$row_g_key] = array(
					'id' => $row_g_key,
					'name' => $row_g_name,
					'original_gkey' => $original_gkey,
					'items' => array()
			);
		}
		$linear_groups[$row_g_key]['items'][] = $row;
	}

	$groups = array();

	foreach ($linear_groups as $k => $data) {

		$exploded = explode('_', $k);
		$reversed = array_reverse($exploded);

		$last = null;
		$names = null;
		$i = 0;

		foreach ($reversed as $rev) {
				
			if (!$last) {
				$x = array($rev => $data);
				$last = $x;
				$names = array_reverse(explode('%|%', $data['name']));
			} else {
				// group information
				foreach ($last as $l) {
					$d = $l;
					break;
				}
				$exp_tmp = explode(',', $d['original_gkey']);
				array_splice($exp_tmp, count($exp_tmp)-1);
				$orig_key = implode(',', $exp_tmp);
				
				$exp_tmp = explode('_', $d['id']);
				array_splice($exp_tmp, count($exp_tmp)-1);
				$g_id = implode('_', $exp_tmp);
				
				$y = array($rev => array('groups' => $last, 'original_gkey' => $orig_key, 'id' => $g_id));
				$last = $y;
			}
			
			$n = array_shift($names);
			if ($n == '') $n = lang('unclassified');
			$last[$rev]['name'] = $n;
			
			$i++;
		}

		if (!isset($groups[$rev])) $groups[$rev] = array();
		$groups[$rev][] = $last[$rev];

	}

	return $groups;
}



function build_report_conditions_html($report_id, $parameters=array()) {

	$report = Reports::findById($report_id);
	if (!$report instanceof Report) return "";
	
	$object_type = ObjectTypes::findById($report->getReportObjectTypeId());
	
	$rc = new ReportingController();
	$types = $rc->get_report_column_types($report_id);
	
	$conditions = ReportConditions::getAllReportConditions($report->getId());
	$conditionHtml = "";
	
	if (count($conditions) > 0) {
		$model = $object_type instanceof ObjectType ? $object_type->getHandlerClass() : "Objects";
		foreach ($conditions as $condition) {
			if($condition->getCustomPropertyId() > 0){
				if (!$object_type instanceof ObjectType) continue;
	
				if (in_array($object_type->getType(), array('dimension_object','dimension_group'))) {
					if (Plugins::instance()->isActivePlugin('member_custom_properties')) {
						$mcp = MemberCustomProperties::getCustomProperty($condition->getCustomPropertyId());
						$name = clean($mcp->getName());
						$paramName = $condition->getId()."_".$mcp->getName();
						$coltype = $mcp->getOgType();
					}
				} else {
					$cp = CustomProperties::getCustomProperty($condition->getCustomPropertyId());
					$name = clean($cp->getName());
					$paramName = $condition->getId()."_".$cp->getName();
					$coltype = $cp->getOgType();
				}
			}else{
				$name = Localization::instance()->lang('field ' . $model . ' ' . $condition->getFieldName());
				if (!$name) {
					$name = lang('field Objects ' . $condition->getFieldName());
				}
					
				$coltype = array_key_exists($condition->getFieldName(), $types)? $types[$condition->getFieldName()]:'';
				$paramName = $condition->getFieldName();
				if (str_starts_with($coltype, 'DATE') && !$condition->getIsParametrizable()) {
					$cond_value = DateTimeValueLib::dateFromFormatAndString('m/d/Y', $condition->getValue())->format(user_config_option('date_format'));
					$condition->setValue($cond_value);
				}
			}
			$paramValue = isset($parameters[$paramName]) ? $parameters[$paramName] : '';
			$value = $condition->getIsParametrizable() ? clean($paramValue) : clean($condition->getValue());
				
			eval('$managerInstance = ' . $model . "::instance();");
			$externalCols = $managerInstance->getExternalColumns();
			if(in_array($condition->getFieldName(), $externalCols)){
				$value = clean(Reports::instance()->getExternalColumnValue($condition->getFieldName(), $value, $managerInstance));
			}
			if ($value != '') {
				$conditionHtml .= '<li>' . $name . ' ' . ($condition->getCondition() != '%' ? $condition->getCondition() : lang('ends with') ) . ' ' . format_value_to_print($condition->getFieldName(), $value, $coltype, '', '"', user_config_option('date_format')) . '</li>';
			}
		}
	}
	
	return $conditionHtml;
}



function custom_report_info_blocks($params) {

	$id = array_var($params, 'id');
	$results = array_var($params, 'results');
	$rep_params = array_var($params, 'parameters');

	$conditionHtml = build_report_conditions_html($id, $rep_params);
	?>
	
<?php if ($conditionHtml != "") : ?>
<div class="custom-report-info-block">
	<div class="bold"><?php echo lang('conditions')?>:</div>
	<ul><?php echo $conditionHtml; ?></ul>
</div>
<?php endif; ?>

<?php if (count(active_context_members(false)) > 0) : ?>
<div class="custom-report-info-block">
	<h5><?php echo lang('showing information for')?>:</h5>
	<ul>
	<?php
		$context = active_context();
		foreach ($context as $selection) :
			if ($selection instanceof Member) : ?>
				<li><span class="coViewAction <?php echo $selection->getIconClass()?>"><?php echo $selection->getName()?></span></li>	
	<?php 	endif;
		endforeach;
	?>
	</ul>
</div>
<?php endif; ?>

<?php $ret=null; Hook::fire('more_report_header_info', array('report_id' => $id, 'results' => $results), $ret);?>

<div class="custom-report-info-block no-border">
	<h5><?php echo lang('report date')?>:</h5>
	<p><?php echo format_datetime(DateTimeValueLib::now()); ?></p>
</div>

<div class="clear"></div>
<?php 
}



function parse_custom_report_group_by($group_by) {
	
	$group_by_criterias = array();
	
	if (is_array($group_by)) {
		$group_by = array_filter($group_by);
		foreach ($group_by as $gb) {
			$exploded = explode('_', $gb);
			$type = array_shift($exploded);
			$id = implode('_', $exploded);
			
			switch ($type) {
				case 'a': $group_by_criterias[] = array('type' => 'association', 'id' => $id);
					break;
				case 'cp': $group_by_criterias[] = array('type' => 'custom_property', 'id' => $id);
					break;
				case 'pm': $group_by_criterias[] = array('type' => 'parent_member', 'id' => $id);
					break;
				case 'dim': $group_by_criterias[] = array('type' => 'classification', 'id' => $id);
					break;
				case 'fp': $group_by_criterias[] = array('type' => 'fixed_property', 'id' => $id);
					break;
				default:
					break;
			}
		}
	}
	return $group_by_criterias;
}




function build_report_conditions_sql($parameters) {
	
	$report = array_var($parameters, 'report');
	if (!$report instanceof Report) {
		return;
	}
	
	$params = array_var($parameters, 'params');
	
	$ot = ObjectTypes::instance()->findById($report->getReportObjectTypeId());
	
	$model = $ot->getHandlerClass();
	$model_instance = null;
	if ($model) {
		$model_instance = new $model();
	}
	
	$allConditions = "";
	
	$conditionsFields = ReportConditions::getAllReportConditionsForFields($report->getId());
	$conditionsCp = ReportConditions::getAllReportConditionsForCustomProperties($report->getId());
	
	$show_archived = false;
	
	if(count($conditionsFields) > 0){
		foreach($conditionsFields as $condField){
			if($condField->getFieldName() == "archived_on"){
				$show_archived = true;
			}
			$skip_condition = false;
			if ($model_instance) {
				$col_type = $model_instance->getColumnType($condField->getFieldName());
			}
	
			$allConditions .= ' AND ';
			$dateFormat = 'm/d/Y';
			if(isset($params[$condField->getId()])){
				$value = $params[$condField->getId()];
				if ($col_type == DATA_TYPE_DATE || $col_type == DATA_TYPE_DATETIME) {
					$dateFormat = user_config_option('date_format');
				}
				if ($value == date_format_tip($dateFormat)) $value = "";
				
			} else {
				$value = $condField->getValue();
			}
			
			if ($value == '' && $condField->getIsParametrizable()) $skip_condition = true;
	
			if (!$skip_condition) {
				$field_name = $condField->getFieldName();
					
				if (in_array($ot->getType(), array('dimension_object', 'dimension_group'))) {
					if (in_array($condField->getFieldName(), Members::getColumns())) {
						$field_name = 'm`.`'.$condField->getFieldName();
		
					} else  if (in_array($condField->getFieldName(), Objects::getColumns())) {
						$field_name = 'o`.`'.$condField->getFieldName();
					}
				} else {
					if (in_array($condField->getFieldName(), Objects::getColumns())) {
						$field_name = 'o`.`'.$condField->getFieldName();
					} else {
						$field_name = $condField->getFieldName();
					}
				}
				
				if($condField->getCondition() == 'like' || $condField->getCondition() == 'not like'){
					$value = '%'.$value.'%';
				}
				if ($col_type == DATA_TYPE_DATE || $col_type == DATA_TYPE_DATETIME) {
					if ($value == date_format_tip($dateFormat)) {
						$value = EMPTY_DATE;
					} else {
						Logger::log_r(array('f' => $dateFormat, 'v' => $value));
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
	
	if(count($conditionsCp) > 0){
		$dateFormat = user_config_option('date_format');
		$date_format_tip = date_format_tip($dateFormat);
	
		foreach($conditionsCp as $condCp){
			if (in_array($ot->getType(), array('dimension_object', 'dimension_group'))) {
				if (Plugins::instance()->isActivePlugin('member_custom_properties')) {
					$cp = MemberCustomProperties::getCustomProperty($condCp->getCustomPropertyId());
				} else {
					continue;
				}
			} else {
				$cp = CustomProperties::getCustomProperty($condCp->getCustomPropertyId());
			}
	
			$skip_condition = false;
				
			if(isset($params[$condCp->getId()."_".$cp->getName()])){
				$value = $params[$condCp->getId()."_".$cp->getName()];
			}else{
				$value = $condCp->getValue();
			}
			if ($value == '' && $condCp->getIsParametrizable()) $skip_condition = true;
			if (!$skip_condition) {
				$current_condition = ' AND ';
				if (in_array($ot->getType(), array('dimension_object', 'dimension_group'))) {
					$current_condition .= 'm.id IN ( SELECT member_id as id FROM '.TABLE_PREFIX.'member_custom_property_values cpv WHERE ';
				} else {
					$current_condition .= 'o.id IN ( SELECT object_id as id FROM '.TABLE_PREFIX.'custom_property_values cpv WHERE ';
				}
				$current_condition .= ' cpv.custom_property_id = '.$condCp->getCustomPropertyId();
					
				if($condCp->getCondition() == 'like' || $condCp->getCondition() == 'not like'){
					$value = '%'.$value.'%';
				}
				if ($cp->getType() == 'date') {
					if ($value == $date_format_tip) continue;
					$dtValue = DateTimeValueLib::dateFromFormatAndString($dateFormat, $value);
					$value = $dtValue->format('Y-m-d H:i:s');
				}
				if($condCp->getCondition() != '%'){
					if ($cp->getType() == 'numeric' && is_numeric($value)) {
						$current_condition .= ' AND cpv.value '.$condCp->getCondition().' '.$value;
					}else if ($cp->getType() == 'boolean') {
						$current_condition .= ' AND cpv.value '.$condCp->getCondition().' '.($value ? '1' : '0');
						if (!$value) {
							if (in_array($ot->getType(), array('dimension_object', 'dimension_group'))) {
								$current_condition .= ') OR m.id NOT IN (SELECT member_id as id FROM '.TABLE_PREFIX.'member_custom_property_values cpv2 
									WHERE cpv2.member_id=o.id AND cpv2.value=1 AND cpv2.custom_property_id = '.$condCp->getCustomPropertyId();
							} else {
								$current_condition .= ') OR o.id NOT IN (SELECT object_id as id FROM '.TABLE_PREFIX.'custom_property_values cpv2
									WHERE cpv2.object_id=o.id AND cpv2.value=1 AND cpv2.custom_property_id = '.$condCp->getCustomPropertyId();
							}
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
	
	if (!$show_archived) {
		if (in_array($ot->getType(), array('dimension_object', 'dimension_group'))) {
			$allConditions .= " AND m.archived_by_id=0 ";
		} else {
			$allConditions .= " AND o.archived_by_id=0 ";
		}
	}
	
	
	return array(
			'all_conditions' => $allConditions,
	);
}