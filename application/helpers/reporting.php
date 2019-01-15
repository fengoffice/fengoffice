<?php

function render_report_header_button($button_data) {
	
	echo input_field(array_var($button_data, 'name'), array_var($button_data, 'text'), array(
			'id' => array_var($button_data, 'id', ''),
			'type' => 'button',
			'onclick' => array_var($button_data, 'onclick', 'return true;'),
			'class' => "report-header-button " . array_var($button_data, 'iconcls')
	));
}
function render_report_header_button_small($button_data) {

    echo input_field(
            array_var($button_data, 'name'),
            array_var($button_data,'text'),
            array(
        'id' => array_var($button_data, 'id', ''),
        'type' => 'button',
        'title' => array_var($button_data, 'title', ''),
        'onclick' => array_var($button_data, 'onclick', 'return true;'),
        'class' => "report-header-button-small " . array_var($button_data, 'iconcls')
    ));
}

function report_values_to_arrays($results, $report) {
	if (!isset($results['group_by_criterias']) || count($results['group_by_criterias']) == 0) {
		return report_values_to_arrays_plain($results, $report);
	}
	
	Hook::fire('modify_custom_report_results', array('report' => $report), $results);
	
	$groups = $results['grouped_rows'];
	
	$headers = array();
	$columns = array_var($results, 'columns');
	foreach ($columns['order'] as $col) {
		if ($col == 'object_type_id' || $col == 'link') continue;
		$h = array_var($columns['names'], $col);
		if ($h) $headers[] = $h;
	}
	
	$all_report_rows = array();
	foreach($groups as $g) {
		$group_rows = get_report_grouped_values_as_array($g, $results, $report, 0);
		$all_report_rows = array_merge($all_report_rows, $group_rows);
	}
	
	$add_data_rows = array();
	Hook::fire('get_additional_report_rows', array('results' => $results, 'report' => $report), $add_data_rows);
	if (count($add_data_rows) > 0) {
		$all_report_rows = array_merge($all_report_rows, $add_data_rows);
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
				$tz_offset = array_var($row, 'tz_offset');
				$formatted_val = format_value_to_print($col, $value, $val_type, array_var($row, 'object_type_id'), '', $date_format, $tz_offset);
			}
			if ($formatted_val == '--') $formatted_val = "";
				
			$formatted_val = strip_tags($formatted_val);
				
			$values_array[] = $formatted_val;
		}
		
		$all_data_rows[] = $values_array;
		
		Hook::fire('after_report_table_plain_row', array('row' => $row, 'report' => $report, 'results' => $results), $all_data_rows);
	}
	
	$add_data_rows = array();
	Hook::fire('get_additional_report_rows', array('results' => $results, 'report' => $report), $add_data_rows);
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
                Hook::fire('set_custom_format_value_date',array('report'=>$report), $r);
		$r = str_replace(array("\r\n","\r","\n"), " ", $r);
		foreach ($r as &$ritem) $ritem = html_entity_decode($ritem);
		
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
	$external_columns = $report->getReportExternalColumns();
	
	?>
    <table class="report <?php echo $to_print ? '':'custom-report scroll' ?>">
        <thead>
		<tr class="header-custom-report custom-report-table-heading">
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
			<th class="bold">
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
				<a href="#" style="color:white;" onclick="<?php echo $echo_link ? "og.reorderCustomReport(this, ".$report->getId().",'$col',".($asc ? '1' : '0').");" : "#" ?>" <?php echo ($echo_link ? "" : 'style="cursor:default;"') ?>>
					<?php echo clean(array_var($columns['names'], $col)) ?>
				</a>
		<?php }
			  if(!$to_print && $sorted){ ?>
				<span class="db-ico ico-<?php echo !$asc ? 'asc' : 'desc' ?>" style="padding:2px 0 0 18px;">&nbsp;</span>
		<?php } ?>
			</th>
		<?php 
		}
		?>
		</tr>
        </thead>
        <tbody>

	<?php
		$isAlt = true; 
		foreach($rows as $row) {
                        //var_dump($row);
			$isAlt = !$isAlt;
			$i = 0; 
	?>
	<tr class="report-row-tbody report-data <?php echo ($isAlt ? 'alt' : '')?>">

		<?php
                        $types = array_var($columns,'types');
			foreach ($columns['order'] as $col) {
				if ($col == 'object_type_id') continue;



				$value = array_var($row, $col);
				$type = array_var($columns['types'], $col);

                                $numeric_type = !in_array($col, $external_columns) && in_array($type, array(DATA_TYPE_INTEGER, DATA_TYPE_FLOAT, 'numeric'));
		?>
			<td <?php echo $numeric_type ? 'class="right"' : ''?>>
		<?php 
				$val_type = ($col == 'link' ? '' : array_var($types, $col));
                                $date_custom = $report->getColumnValue('date_format');
				$date_format = is_numeric($col) ? "Y-m-d" : user_config_option('date_format');
                                if ($date_custom != '') {
                                    $date_format = $date_custom;
                                }
				if ($val_type == 'DATETIME') {
					echo $value;
				} else {
					$tz_offset = array_var($row, 'tz_offset');
					echo format_value_to_print($col, $value, $val_type, array_var($row, 'object_type_id'), '', $date_format, $tz_offset);
				}
		?>
			</td>
		<?php
				$i++;
			}
		?>

		<?php
			$null = null;
			Hook::fire('after_report_table_html_row', array('row' => $row, 'report' => $report, 'results' => $results), $null);
		?>
	</tr>
	<?php 
		} // end foreach rows
		
		$null=null; 
		Hook::fire('render_additional_report_rows', array('results' => $results, 'report' => $report), $null);
	?>
        </tbody>
	</table>
	<?php
	
	return ob_get_clean();
}



function echo_report_group_html($group_data, $results, $report, $level=0) {
	
	$columns = array_var($results, 'columns');
	
	$i = 0;
	foreach ($group_data as $gd) {
		if (!$report->getColumnValue("hide_group_details")) {
			// dont show the last group header if it is shown as columns
			if (!$report->getColumnValue('show_last_group_as_column') || $level < max(array_keys(array_var($results, 'group_totals', array())))) {
			    echo '<tr><th colspan="'.count($columns['order']).'" class="report-group-heading-'.$level.' indent-'.$level.'">'.$gd['name'].'</th></tr>';
			}
		} else {
			// when hiding details dont show the title and show the totals before the subgroups
			$null=null;
			Hook::fire('render_additional_report_group_rows', array('results' => $results, 'report' => $report, 'group' => $gd, 'level' => $level), $null);
		}
		
		$i++;
		
		if (isset($gd['groups'])) {
			echo_report_group_html($gd['groups'], $results, $report, $level+1);
			
			if (!$report->getColumnValue("hide_group_details")) {
				// when hiding details dont show the title and show the totals before the subgroups
				$null=null;
				Hook::fire('render_additional_report_group_rows', array('results' => $results, 'report' => $report, 'group' => $gd, 'level' => $level), $null);
			}
			
		} else if (isset($gd['items'])) {
			
			$rows = array_var($results, 'rows');
			$pagination = array_var($results, 'pagination');
			
			?>
			<!--<tbody class="report-data">-->
			<?php
			if (!$report->getColumnValue("hide_group_details")) {
				
				$external_columns = $report->getReportExternalColumns();
				
				$isAlt = true;
				foreach($gd['items'] as $item_data) {
					$isAlt = !$isAlt;
					$row = array_var($rows, $item_data['mid']);
					$i = 0; 
			?>
				<tr class="report-data" <?php echo ($isAlt ? ' style="background-color:#F4F8F9"' : "");?>>
				<?php
					foreach ($columns['order'] as $col) {
						if ($col == 'object_type_id') continue;
						
						if (strrpos($col, '|') !== false) {
							$exploded = explode('|', $col);
							$mem_ids = array();
							foreach ($exploded as $k=>$exp) {
								if ($k == count($exploded)-1) $value = $exp;
								else $mem_ids[] = $exp;
							}
							$col = substr_utf($col, strrpos($col, '|')+1);
							
							$o = Objects::findObject($item_data['mid']);
							if ($o instanceof ContentDataObject) {
								$obj_mem_ids = $o->getMemberIds();
								$intersect = array_intersect($mem_ids, $obj_mem_ids);
								if (count($intersect) == count($mem_ids)) {
									$value = array_var($row, $col);
								}
							}
							
						} else {
							$value = array_var($row, $col);
						}
						
						$type = array_var($columns['types'], $col);
						$numeric_type = !in_array($col, $external_columns) && in_array($type, array(DATA_TYPE_INTEGER, DATA_TYPE_FLOAT, 'numeric'));
				?>
					<td style="<?php echo ($col == 'link' ? 'width:16px;padding:0 0 0 2px;border-right:0 none;' : '') ?>" <?php echo $numeric_type ? 'class="right"' : ''?>>
				<?php 
						$val_type = ($col == 'link' ? '' : array_var($types, $col));
						$date_format = is_numeric($col) ? "Y-m-d" : user_config_option('date_format');
						if ($val_type == 'DATETIME') {
							echo $value;
						} else {
							$tz_offset = array_var($row, 'tz_offset');
							echo format_value_to_print($col, $value, $val_type, array_var($row, 'object_type_id'), '', $date_format, $tz_offset);
						}
				?>
					</td>
				<?php
						$i++;
					}
					$null = null;
					Hook::fire('after_report_table_html_row', array('row' => $row, 'report' => $report, 'results' => $results), $null);
				?>
				</tr>
			<?php 
				} // end foreach rows
			}
			
			if (!$report->getColumnValue("hide_group_details")) {
				// when hiding details dont show the title and show the totals before the subgroups
				$null=null;
				Hook::fire('render_additional_report_group_rows', array('results' => $results, 'report' => $report, 'group' => $gd, 'level' => $level), $null);
			}
				
			?>
            <!--</tbody>-->
            <?php
		}
	}
    ?>

    <?php
	
	
	
}

function report_table_html($results, $report, $parametersUrl="", $to_print=false) {
    
    if (!isset($results['group_by_criterias']) || count($results['group_by_criterias']) == 0) {
		return report_table_html_plain($results, $report, $parametersUrl, $to_print);
	}
	
	ob_start();
	Hook::fire('modify_custom_report_results', array('report' => $report), $results);
	$groups = $results['grouped_rows'];
	$add_thead_cls = $report->getColumnValue("hide_group_details") ? "no-details" : "";
	?>
	<table class="report <?php echo $to_print ? '':'custom-report scroll' ?>" style="<?php echo $to_print ? '':'' ?>" >
        <thead>
		<tr class="custom-report-table-heading <?php echo $add_thead_cls ?>">
		<?php if (!$report->getColumnValue("hide_group_details")) { ?>
            <th>&nbsp;</th>
        <?php } ?>
		<?php
		$columns = array_var($results, 'columns');
		$after_link = false;
		foreach ($columns['order'] as $col) {
			if($col != 'link') {
				if ($after_link) {
					//echo "<th colspan='2'>";
					echo "<th>";
					$after_link = false;
				} else {
					echo "<th>";
				}
			  	echo clean(array_var($columns['names'], $col));
                echo ' '.get_format_value_to_header($col, $report->getReportObjectTypeId());
				echo "</th>";
			} else {
				$after_link = true;
			}
		}
		?>
		</tr>
        </thead>
	    <tbody style="<?php echo $to_print ? '':'' ?>">
        <?php
	foreach($groups as $g) {
	    echo_report_group_html($g, $results, $report, 0);
    }

	echo "<tr><td>&nbsp;</td></tr>";
	$null=null;
	Hook::fire('render_additional_report_rows', array('results' => $results, 'report' => $report,'final_total' => true), $null);
	
	?>
    </tbody>
    </table>
    <?php
	
	return ob_get_clean();
	
}




function get_report_grouped_values_as_array($group_data, $results, $report, $level=0) {
	$all_rows = array();

	$columns = array_var($results, 'columns');

	$i = 0;
	foreach ($group_data as $gd) {
		
		if (!$report->getColumnValue('show_last_group_as_column') || $level < max(array_keys(array_var($results, 'group_totals', array())))) {
			$row_vals = array();
			
			$first = true;
			foreach ($columns as $c) {
				$row_vals[] = $first ? $gd['name'] : "";
				$first = false;
			}
			$all_rows[] = $row_vals;
			
			$i++;
		}

		if (isset($gd['groups'])) {

			$group_rows = get_report_grouped_values_as_array($gd['groups'], $results, $report, $level+1);
			$all_rows = array_merge($all_rows, $group_rows);
			Hook::fire('get_additional_report_group_rows_csv', array('results' => $results, 'report' => $report, 'group' => $gd, 'level' => $level), $all_rows);		
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
							$tz_offset = array_var($row, 'tz_offset');
							$formatted_val = format_value_to_print($col, $value, $val_type, array_var($row, 'object_type_id'), '', $date_format, $tz_offset);
						}

						if ($formatted_val == '--') $formatted_val = "";
						$formatted_val = strip_tags($formatted_val);
						$item_values[] = $formatted_val;

						$i++;
					}

					$all_rows[] = $item_values;
				}
			}

			Hook::fire('get_additional_report_group_rows_csv', array('results' => $results, 'report' => $report, 'group' => $gd, 'level' => $level), $all_rows);
		
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



function get_cp_contact_name($gb_keys, $index, $row, &$cp_contact_cache) {
	$cp_id = $gb_keys[$index]['cp_contact'];
	$cp_contact_id = $row[$gb_keys[$index]['k']];
	
	if (!isset($cp_contact_cache[$cp_id])) $cp_contact_cache[$cp_id] = array();
	
	if (!isset($cp_contact_cache[$cp_id][$cp_contact_id])) {
		$cp_contact = Contacts::findById($cp_contact_id);
		if ($cp_contact instanceof Contact) {
			$cp_contact_cache[$cp_id][$cp_contact_id] = $cp_contact->getObjectName();
		}
	}
	
	return array_var($cp_contact_cache[$cp_id], $cp_contact_id);
}


function group_custom_report_results($rows, $group_by_criterias, $ot,$formatDate = true) {
	
	$gb_keys = array();

	foreach ($group_by_criterias as $gb) {
		switch ($gb['type']) {
			case 'association': $gkey = '_group_id_a_'.$gb['id']; break;
			case 'custom_property': $gkey = '_group_id_cp_'.$gb['id']; break;
			case 'parent_member': $gkey = '_group_id_pm_'.$gb['id']; break;
			case 'classification': $gkey = '_group_id_dim_'.$gb['id']; break;
			case 'fixed_property': $gkey = '_group_id_fp_'.$gb['id']; break;
			case 'intersection': $gkey = '_group_id_inter_'.$gb['id']; break;
		}
		
		if (!$gkey) $gkey = "";
		$gname = str_replace("_id_", "_name_", $gkey);
		$gb_keys[] = array('k' => $gkey, 'n' => $gname, 'is_date' => false);
	}
	
	/* @var $ot ObjectType */
	if ($ot instanceof ObjectType && $ot->getHandlerClass() != '') {
		eval('$managerInstance = ' . $ot->getHandlerClass() . "::instance();");
		if ($managerInstance) {
			foreach ($gb_keys as &$gbk) {
				if (str_starts_with($gbk['k'], '_group_id_fp_')) {
					$col = str_replace('_group_id_fp_', '', $gbk['k']);
					if (in_array($managerInstance->getColumnType($col), array(DATA_TYPE_DATETIME, DATA_TYPE_DATE))) {
						$gbk['is_date'] = true;
					}
				} else if (str_starts_with($gbk['k'], '_group_id_cp_')) {
					$cp_id = str_replace('_group_id_cp_', '', $gbk['k']);
					$cp = CustomProperties::findById($cp_id);
					if ($cp instanceof CustomProperty && ($cp->getType()=='contact' || $cp->getType()=='user')) {
						$gbk['cp_contact'] = $cp_id;
					}
					if ($cp instanceof CustomProperty && ($cp->getType()=='date' || $cp->getType()=='datetime')) {
						$gbk['is_date'] = true;
					}
				}
			}
		}
	}
	
	$date_format = user_config_option('date_format');
	$mysql_date_format_re = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/";
	
	$cp_contact_cache = array();
	
	$grouped_temp = array();
	
	foreach ($rows as $row) {
		if (!empty($row)) {
			
			if (isset($gb_keys[0])) {
				$k0 = $row[$gb_keys[0]['k']];
				//$n0 = isset($row[$gb_keys[0]['k']."_toorder"]) ? $row[$gb_keys[0]['k']."_toorder"] : $row[$gb_keys[0]['n']].'_'.$row[$gb_keys[0]['k']];
				if (isset($row[$gb_keys[0]['k']."_toorder"])){
                    $n0 = $row[$gb_keys[0]['k']."_toorder"];
                }else{
                    $n0 = $row[$gb_keys[0]['n']].'_'.$row[$gb_keys[0]['k']];
                    if ($row[$gb_keys[0]['n']] == ''){
                        $n0 = 'zzzzz_unclassified'; // unclassified group must be at the end of the list
                    }
                }
				if ($gb_keys[0]['is_date'] && $formatDate){
					//$n0 = gmdate('Y-m-d', strtotime($k0));
					if (preg_match($mysql_date_format_re, $k0)) {
						$n0 =  format_date(DateTimeValueLib::dateFromFormatAndString("Y-m-d", $k0),null,0);
					} else {
						$n0 =  format_date(DateTimeValueLib::dateFromFormatAndString($date_format, $k0),null,0);
					}
                	$row[$gb_keys[0]['n']] = $n0;
				}
				else if (array_var($gb_keys[0], 'cp_contact')) {
					$n0 = get_cp_contact_name($gb_keys, 0, $row, $cp_contact_cache);
					$row[$gb_keys[0]['n']] = $n0;
				}
				
				if ($n0 == '') $n0 = 'zzzzz_unclassified'; // unclassified group must be at the end of the list
				
				if (!isset($grouped_temp[$n0])) {
				    if ($gb_keys[0]['is_date'] && $formatDate){
				    	$date = $n0;
                        $grouped_temp[$n0] = array(
                            'id' =>  $date,
                            'name' => $k0 ? $k0 : lang('unclassified'),
                            'original_gkey' => $gb_keys[0]['k'],
                        );
                        $k0 = $date;
                    }else{
                        $grouped_temp[$n0] = array(
                            'id' =>  $k0,
                            'name' => $row[$gb_keys[0]['n']] ? $row[$gb_keys[0]['n']] : lang('unclassified'),
                            'original_gkey' => $gb_keys[0]['k'],
                        );
                    }
				}else{
				    $k0 = $grouped_temp[$n0]['id'];
                }
                
				if (isset($gb_keys[1])) {
					$k1 = $row[$gb_keys[1]['k']];
					//$n1 = isset($row[$gb_keys[1]['k']."_toorder"]) ? $row[$gb_keys[1]['k']."_toorder"] : $row[$gb_keys[1]['n']].'_'.$row[$gb_keys[1]['k']];

                    if (isset($row[$gb_keys[1]['k']."_toorder"])){
                        $n1 = $row[$gb_keys[1]['k']."_toorder"];
                    }else{
                        $n1 = $row[$gb_keys[1]['n']].'_'.$row[$gb_keys[1]['k']];
                        if ($row[$gb_keys[1]['n']] == ''){
                            $n1 = 'zzzzz_unclassified'; // unclassified group must be at the end of the list
                        }
                    }

					
					if ($gb_keys[1]['is_date']){
					    //$n1 = gmdate('Y-m-d', strtotime($k1));
						if (preg_match($mysql_date_format_re, $k1)) {
							$n1 =  format_date(DateTimeValueLib::dateFromFormatAndString("Y-m-d", $k1),null,0);
						} else {
                        	$n1 = format_date(DateTimeValueLib::dateFromFormatAndString($date_format, $k1),null,0);
						}
					    $row[$gb_keys[1]['n']] = $n1;					    
					} 
					else if (array_var($gb_keys[1], 'cp_contact')) {
						$n1 = get_cp_contact_name($gb_keys, 1, $row, $cp_contact_cache);
						$row[$gb_keys[1]['n']] = $n1;
					}
                    
                    if (!isset($grouped_temp[$n0]['groups'][$n1])) {
                        if ($gb_keys[1]['is_date']){
							$date = $n1;
                            $grouped_temp[$n0]['groups'][$n1] = array(
                                'id' =>  $k0."_".$date,
                                'name' => $k1 ? $k1 : lang('unclassified'),
                                'original_gkey' => $gb_keys[0]['k'].",".$gb_keys[1]['k'],
                            );
                            $k1 = $date;
                        }else{
                            $grouped_temp[$n0]['groups'][$n1] = array(
                                'id' => $k0."_".$k1,
                                'name' => $row[$gb_keys[1]['n']] ? $row[$gb_keys[1]['n']] : lang('unclassified'),
                                'original_gkey' => $gb_keys[0]['k'].",".$gb_keys[1]['k'],
                            );
                        }
                    }
                    
					if (isset($gb_keys[2])) {
						$k2 = $row[$gb_keys[2]['k']];
						//$n2 = isset($row[$gb_keys[2]['k']."_toorder"]) ? $row[$gb_keys[2]['k']."_toorder"] : $row[$gb_keys[2]['n']];

                        if (isset($row[$gb_keys[2]['k']."_toorder"])){
                            $n2 = $row[$gb_keys[2]['k']."_toorder"];
                        }else{
                            $n2 = $row[$gb_keys[2]['n']].'_'.$row[$gb_keys[2]['k']];
                            if ($row[$gb_keys[2]['n']] == ''){
                                $n2 = 'zzzzz_unclassified'; // unclassified group must be at the end of the list
                            }
                        }
						
						if ($gb_keys[2]['is_date']){
						    //$n2 = gmdate('Y-m-d', strtotime($k2));
							if (preg_match($mysql_date_format_re, $k2)) {
								$n2 =  format_date(DateTimeValueLib::dateFromFormatAndString("Y-m-d", $k2),null,0);
							} else {
                            	$n2 =  format_date(DateTimeValueLib::dateFromFormatAndString($date_format, $k2),null,0);
							}
						    $row[$gb_keys[2]['n']] = $n2;
						}
						else if (array_var($gb_keys[2], 'cp_contact')) {
							$n2 = get_cp_contact_name($gb_keys, 2, $row, $cp_contact_cache);
							$row[$gb_keys[2]['n']] = $n2;
						}
						
						if ($n2 == '') $n2 = 'zzzzz_unclassified'; // unclassified group must be at the end of the list
						
						if (!isset($grouped_temp[$n0]['groups'][$n1]['groups'][$n2])) {
                            if ($gb_keys[2]['is_date']){
                                
                                //$date = format_date(DateTimeValueLib::dateFromFormatAndString($date_format, $k2),null,0);
                                $date = $n2;
                                $grouped_temp[$n0]['groups'][$n1]['groups'][$n2] = array(
                                    'id' => $k0."_".$k1."_".$date,
                                    'name' => $k2 ? $k2 : lang('unclassified'),
                                    'original_gkey' => $gb_keys[0]['k'].",".$gb_keys[1]['k'].",".$gb_keys[2]['k']
                                );
                                $k2 = $date;
                            }else{
                                $grouped_temp[$n0]['groups'][$n1]['groups'][$n2] = array(
                                    'id' => $k0."_".$k1."_".$k2,
                                    'name' => $row[$gb_keys[2]['n']] ? $row[$gb_keys[2]['n']] : lang('unclassified'),
                                    'original_gkey' => $gb_keys[0]['k'].",".$gb_keys[1]['k'].",".$gb_keys[2]['k'],
                                );
                            }
						}
						
						$grouped_temp[$n0]['groups'][$n1]['groups'][$n2]['items'][] = $row;
						
					} else {
						
						$grouped_temp[$n0]['groups'][$n1]['items'][] = $row;
						
					}
				} else {
						
					$grouped_temp[$n0]['items'][] = $row;
					
				}
			}
		}
	}
	
	// ensure the correct group order
    // This function is assigning formatting to the data
    // @ToDo - remove all formatting from this function and put it on the "view layer"
	foreach ($grouped_temp as $k0 => &$v0) {
		if (isset($v0['groups'])) {
			foreach ($v0['groups'] as $k1 => &$v1) {
				if (isset($v1['groups'])) {
					ksort($v1['groups']);
					
					if (isset($gb_keys[2]['is_date']) && $gb_keys[2]['is_date'] && $formatDate) {
						foreach ($v1['groups'] as $k2 => &$v2) {
						    // This is where formatting is applied (in this case, to the date values).
                            // We need to remove this from here and move to the view/rendering class.
							if (preg_match($mysql_date_format_re, $v2['name'])) {
								$v1['name'] =  format_date(DateTimeValueLib::dateFromFormatAndString("Y-m-d", $v2['name']),null,0);
							} else {
								$v2['name'] = format_date(DateTimeValueLib::dateFromFormatAndString($date_format, $v2['name']), null, 0);
							}
						}
					}
				}
				
				if (isset($gb_keys[1]['is_date']) && $gb_keys[1]['is_date'] && $formatDate) {
					if (preg_match($mysql_date_format_re, $v1['name'])) {
						$v1['name'] =  format_date(DateTimeValueLib::dateFromFormatAndString("Y-m-d", $v1['name']),null,0);
					} else {
						$v1['name'] = format_date(DateTimeValueLib::dateFromFormatAndString($date_format, $v1['name']), null, 0);
					}
				}
			}
			ksort($v0['groups']);
		}
		
		if (isset($gb_keys[0]['is_date']) && $gb_keys[0]['is_date'] && $formatDate) {
			if (preg_match($mysql_date_format_re, $v2['name'])) {
				$v0['name'] =  format_date(DateTimeValueLib::dateFromFormatAndString("Y-m-d", $v0['name']),null,0);
			} else {
				$v0['name'] = format_date(DateTimeValueLib::dateFromFormatAndString($date_format, $v0['name']), null, 0);
			}
		}
	}
	ksort($grouped_temp);
	
	// build result
	$grouped_results = array(array());
	foreach ($grouped_temp as $k => $v) {
		$grouped_results[$k] = array($v);
	}
	
	return $grouped_results;
}


function build_report_conditions_html($report_id, $parameters=array(), $conditions=null, $disabled_params=array()) {
	$report = Reports::findById($report_id);
	if (!$report instanceof Report) return "";
	
	if (is_null($conditions)) {
		$conditions = ReportConditions::getAllReportConditions($report->getId());
	}
	
	return build_report_conditions_html_main($report, $parameters, $conditions, $disabled_params);
}


function build_report_conditions_html_main($report, $parameters=array(), $conditions=array(), $disabled_params=array()) {

	$object_type = ObjectTypes::findById($report->getReportObjectTypeId());
	
	$rc = new ReportingController();
	$types = $rc->get_report_column_types($report->getId());
	
	$conditionHtml = "";
	
	if (count($conditions) > 0) {
		$conditions_per_block = ceil(count($conditions) / 2);
		if ($conditions_per_block < 4) $conditions_per_block = 4;
		$conditions_count = 0;
		
		$disabled_param_ids = isset($disabled_params) ? array_keys($disabled_params) : null;
		if (!is_array($disabled_param_ids)) $disabled_param_ids = array();
		
	    $date_format_tip = date_format_tip(user_config_option('date_format'));
	    $model = $object_type instanceof ObjectType ? $object_type->getHandlerClass() : "Objects";
	    eval('$managerInstance = ' . $model . "::instance();");
	    
		foreach ($conditions as $condition) {
			// dont print ignored conditions
		    if (in_array($condition->getId(), $disabled_param_ids)) {
		    	continue;
		    }
		    //if coltype not in array types, it push to array for then check if the type is date to format
		    if (!array_key_exists($condition->getFieldName(), $types)){
		        if ($condition->getCustomPropertyId() > 0) {
		            $cp = CustomProperties::getCustomProperty($condition->getCustomPropertyId());
		            $condition_type_column = strtoupper($cp->getType());
		        }else{
		            $condition_type_column = $managerInstance->getColumnType($condition->getFieldName());
		        }
		        
		        $types[$condition->getFieldName()] = $condition_type_column;
		    }
		    
		    if($condition->getCustomPropertyId() > 0){
				if (!$object_type instanceof ObjectType) continue;
	
				if (in_array($object_type->getType(), array('dimension_group'))) {
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
			}
			
			if (str_starts_with($coltype, 'DATE') && !$condition->getIsParametrizable()) {
    			$cond_value = DateTimeValueLib::dateFromFormatAndString('m/d/Y', $condition->getValue())->format(user_config_option('date_format'));
    			$condition->setValue($cond_value);
			}
			
			$paramValue = array_var($parameters, $condition->getId(), '');
			if (!$paramValue) {
				$paramValue = array_var($parameters, $paramName, '');
			}
			$value = $condition->getIsParametrizable() ? clean($paramValue) : clean($condition->getValue());
			$externalCols = $managerInstance->getExternalColumns();
			if(in_array($condition->getFieldName(), $externalCols)){
				$value = clean(Reports::instance()->getExternalColumnValue($condition->getFieldName(), $value, $managerInstance));
			}
			
			//set headers conditions when condition is status and report is over tasks
			if ($object_type->getHandlerClass() == 'ProjectTasks' && $condition->getFieldName() == 'status'){

			    if($condition->getIsParametrizable() && count($parameters) && isset($parameters[$condition->getId()."_status"])){
			        $paramValue = $parameters[$condition->getId()."_status"];
			        $paramValue = in_array($paramValue, array('1',1)) ? lang('completed') : lang('pending');
			    }else{
			        $value = in_array($value, array('1',1)) ? lang('completed') : lang('pending');
			    }
			}

			$cond_html = null;
			Hook::fire('custom_report_cond_html', array('field' => $condition, 'report' => $report, 'report_ot' => $object_type, 'value' => $paramValue), $cond_html);
			$already_rendered = false;
			if ($cond_html) {
				$conditionHtml .= $cond_html;
				$already_rendered = true;
			}
			
			if (!$already_rendered && $value != '' && $value != $date_format_tip) {
				$conditionHtml .= '<li>' . $name . ' ' . ($condition->getCondition() != '%' ? $condition->getCondition() : lang('ends with') ) . ' ' . format_value_to_print($condition->getFieldName(), $value, $coltype, '', '"', user_config_option('date_format')) . '</li>';
				$conditions_count++;
				if ($conditions_count % $conditions_per_block == 0) {
					$conditionHtml .= '</ul><ul>';
				}
			}
		}
	}
	
	return $conditionHtml;
}



function custom_report_info_blocks($params) {

	$id = array_var($params, 'id');
	$results = array_var($params, 'results');
	$rep_params = array_var($params, 'parameters');
	$disabled_params = array_var($params, 'disabled_params');
	$conditionHtml = build_report_conditions_html($id, $rep_params, null, $disabled_params);
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

<?php 
	if (is_numeric($id) && $id > 0) {
		$ret=null; 
		Hook::fire('more_report_header_info', array('report_id' => $id, 'results' => $results), $ret);
	}
?>

<div class="custom-report-info-block no-border">
	<h5><?php echo lang('report date')?>:</h5>
	<p><?php echo format_datetime(DateTimeValueLib::now()); ?></p>
</div>

<div class="clear"></div>
<?php 
}



function parse_custom_report_group_by($group_by, $group_by_options = array()) {
	
	$group_by_criterias = array();
	
	if (is_array($group_by)) {
		$group_by = array_filter($group_by);
		foreach ($group_by as $gb) {
			$exploded = explode('_', $gb);
			$type = array_shift($exploded);
			$id = implode('_', $exploded);
			
			$options = null;
			if (is_array($group_by_options)) {
				$options = array_var($group_by_options, $gb);
			}
			
			switch ($type) {
				case 'a': $group_by_criterias[] = array('type' => 'association', 'id' => $id, 'options' => $options);
					break;
				case 'cp': $group_by_criterias[] = array('type' => 'custom_property', 'id' => $id, 'options' => $options);
					break;
				case 'pm': $group_by_criterias[] = array('type' => 'parent_member', 'id' => $id, 'options' => $options);
					break;
				case 'dim': $group_by_criterias[] = array('type' => 'classification', 'id' => $id, 'options' => $options);
					break;
				case 'fp': $group_by_criterias[] = array('type' => 'fixed_property', 'id' => $id, 'options' => $options);
					break;
				case 'intersection': $group_by_criterias[] = array('type' => 'intersection', 'id' => '0', 'options' => $options);
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
	
	$disabled_p = array_var($_REQUEST, 'disabled_params');
    if (!is_array($disabled_p)) $disabled_p = json_decode($disabled_p, true);
	$disabled_params = array();
	if (is_array($disabled_p)) {
		foreach ($disabled_p as $k => $v) if ($v) $disabled_params[] = $k;
	}

	$ot = ObjectTypes::instance()->findById($report->getReportObjectTypeId());
	
	$model = $ot->getHandlerClass();
	$model_instance = null;
	if ($model) {
		$model_instance = new $model();
	}
	
	$allConditions = "";
	
	$conditionsFields = array_var($parameters, 'conditionsFields', ReportConditions::getAllReportConditionsForFields($report->getId()));
	$conditionsCp = array_var($parameters, 'conditionsCp', ReportConditions::getAllReportConditionsForCustomProperties($report->getId()));
	
	$show_archived = false;
	
	if(count($conditionsFields) > 0){
		foreach($conditionsFields as $condField){
			if($condField->getFieldName() == "archived_on"){
				$show_archived = true;
			}

			$skip_condition = false;
			if ($condField->getIsParametrizable() && in_array($condField->getId(), $disabled_params)) $skip_condition = true;
			
			//status is calculated, so compare with completed_on field
			if ($ot->getName() == 'task' && $condField->getFieldName() == "status") {
			    
			    if (!$skip_condition) {
			        if (isset($params[$condField->getId() . "_status"])) {
			            $val = $params[$condField->getId() . "_status"];
			        } else {
			            $val = $condField->getValue();
			        }
			        $allConditions .= in_array($val, array(0, '0')) ? " AND `e`.`completed_on` = " . DB::escape(EMPTY_DATETIME) . " " : " AND `e`.`completed_on` != " . DB::escape(EMPTY_DATETIME) . " ";
			    }
			    continue;
			}
			
			if ($model_instance) {
				$col_type = $model_instance->getCOColumnType($condField->getFieldName());
			}
			

			if (isset($params[$condField->getId()])) {
				$value = $params[$condField->getId()];
				if ($col_type == DATA_TYPE_DATE || $col_type == DATA_TYPE_DATETIME) {
					$dateFormat = user_config_option('date_format');
				}
				if ($value == date_format_tip($dateFormat)) $value = "";
					
			} else {
				$value = $condField->getValue();
				$dateFormat = 'm/d/Y';
			}
			
			$possible_columns = $model_instance->getColumns();
			if (in_array($ot->getType(), array('dimension_object', 'dimension_group'))) {
				$possible_columns = array_merge($possible_columns, Members::getColumns());
			} else {
				$possible_columns = array_merge($possible_columns, Objects::getColumns());
			}
			
			if (in_array($condField->getFieldName(), $possible_columns)) {
				
				$allConditions .= ' AND ';
		
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
							$field_name = 'e`.`'.$condField->getFieldName();
						}
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
	
			} else {
				if ($model_instance instanceof Contacts) {
					
					$allConditions .= " AND ". Reports::get_extra_contact_column_condition($condField->getFieldName(), $condField->getCondition(), $value);
				
				} else if (Plugins::instance()->isActivePlugin('mail') && $model_instance instanceof MailContents) {
					
					if (in_array($condField->getFieldName(), array('to', 'cc', 'bcc', 'body_plain', 'body_html'))){
						$pre = '';
						$post = '';
						$oper = $condField->getCondition();
						if ($oper == '%') {
							$oper = 'like';
							$pre = '%';
						} else if($oper == 'like' || $oper == 'not like') {
							$pre = '%';
							$post = '%';
						}
						$allConditions .= ' AND jt.`'.$condField->getFieldName().'` '.$oper.' '.DB::escape($pre. $value .$post);
					}
				}
				
				Hook::fire('report_conditions_extra_fields', array('field' => $condField, 'report' => $report, 'report_ot' => $ot, 'value' => $value, 'disabled_params' => $disabled_params), $allConditions);
			}
		}
	}
	
	if(count($conditionsCp) > 0){
		$dateFormat = user_config_option('date_format');
		$date_format_tip = date_format_tip($dateFormat);
	
		foreach($conditionsCp as $condCp){
			if (in_array($ot->getType(), array('dimension_group'))) {
				if (Plugins::instance()->isActivePlugin('member_custom_properties')) {
					$cp = MemberCustomProperties::getCustomProperty($condCp->getCustomPropertyId());
				} else {
					continue;
				}
			} else {
				$cp = CustomProperties::getCustomProperty($condCp->getCustomPropertyId());
			}
	
			$skip_condition = false;
				
			//Parametric field
			if(isset($params[$condCp->getId()."_".$cp->getName()])){
				$value = $params[$condCp->getId()."_".$cp->getName()];
                if ($cp->getType() == 'date' || $cp->getType() == 'datetime') {
                    $dateFormat = user_config_option('date_format');
                }
                if ($value == date_format_tip($dateFormat)) $value = "";
			}else{
				$value = $condCp->getValue();
                if ($cp->getType() == 'date') {
                    $dateFormat = 'm/d/Y';
                }elseif ($cp->getType() == 'datetime') {
                    $dateFormat = 'm/d/Y H:i:s';
                }
			}
			
			if ($condCp->getIsParametrizable() && in_array($condCp->getId(), $disabled_params)) $skip_condition = true;
			
			if (!$skip_condition) {
				$current_condition = ' AND ';
				$close_bracket = false;
				if (in_array($ot->getType(), array('dimension_group'))) {
					if (!$value) {
						$close_bracket = true;
						$current_condition .= "(NOT EXISTS (SELECT member_id FROM ".TABLE_PREFIX."member_custom_property_values cpv WHERE cpv.member_id=m.id AND cpv.custom_property_id = ".$condCp->getCustomPropertyId().") 
							OR m.id IN ( SELECT member_id as id FROM ".TABLE_PREFIX."member_custom_property_values cpv WHERE ";
					} else {
						$current_condition .= 'm.id IN ( SELECT member_id as id FROM '.TABLE_PREFIX.'member_custom_property_values cpv WHERE ';
					}
				} else {
					if (!$value) {
						$close_bracket = true;
						$current_condition .= "(NOT EXISTS (SELECT object_id FROM ".TABLE_PREFIX."custom_property_values cpv WHERE cpv.object_id=o.id AND cpv.custom_property_id = ".$condCp->getCustomPropertyId().") 
							OR o.id IN ( SELECT object_id as id FROM ".TABLE_PREFIX."custom_property_values cpv WHERE ";
					} else {
						$current_condition .= 'o.id IN ( SELECT object_id as id FROM '.TABLE_PREFIX.'custom_property_values cpv WHERE ';
					}
				}
				$current_condition .= ' cpv.custom_property_id = '.$condCp->getCustomPropertyId();
					
				if($condCp->getCondition() == 'like' || $condCp->getCondition() == 'not like'){
					$value = '%'.$value.'%';
				}
				if ($cp->getType() == 'date' || $cp->getType() == 'datetime') {

					if ($value == $date_format_tip) continue;
					$dtValue = DateTimeValueLib::dateFromFormatAndString($dateFormat, $value);

                    if ($cp->getType() == 'date') {
                        $value = $dtValue->format('Y-m-d');
                    } elseif ($cp->getType() == 'datetime') {
                        $value = $dtValue->format('Y-m-d H:i:s');
                    }
                }
				if($condCp->getCondition() != '%'){
					if ($cp->getType() == 'numeric' && is_numeric($value)) {
						$current_condition .= ' AND cpv.value '.$condCp->getCondition().' '.$value;
					}else if ($cp->getType() == 'boolean') {						
					    $current_condition .= ' AND cpv.value '.$condCp->getCondition().' '.$value;					    
					}else{
						$current_condition .= ' AND cpv.value '.$condCp->getCondition().' '.DB::escape($value);
					}
				}else{
					$current_condition .= ' AND cpv.value like '.DB::escape("%$value");
				}
				$current_condition .= ')';
				if ($close_bracket) $current_condition .= ')';
				
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
