<?php
	
	function total_task_times_print_group($group_obj, $grouped_objects, $options, $skip_groups = array(), $level = 0, $prev = "", &$total = 0, &$billing_total = 0, &$cost_total = 0, &$estimated_total = 0) {
		
		$margin_left = 15 * $level;
		$cls_suffix = $level > 2 ? "all" : $level;
		$next_level = $level + 1;
			
		$group_name = $group_obj['group']['name'];
		
		$header_colspan = 6;
		if (array_var($options, 'show_estimated_time')) $header_colspan++;
		if (array_var($options, 'show_cost')) $header_colspan++;
		if (array_var($options, 'show_billing')) $header_colspan++;
		
		echo '<tbody><tr><td colspan='.$header_colspan.'>';
		echo '<div class="report-group-heading-'.$cls_suffix.'">' . $group_name . '</div>';
		echo '</td></tr></tbody>';
		
		$mem_index = $prev . $group_obj['group']['id'];
		
		$group_total = 0;
		$group_billing_total = 0;
		$group_cost_total = 0;
		$group_estimated_total = 0;
		
		$table_total = 0;
		$table_billing_total = 0;
		$table_cost_total = 0;
		$table_estimated_total = 0;
		// draw the table for the values
		if (isset($grouped_objects[$mem_index]) && count($grouped_objects[$mem_index]) > 0) {
			total_task_times_print_table($grouped_objects[$mem_index], $margin_left, $options, $group_name, $table_total, $table_billing_total, $table_cost_total, $table_estimated_total);
			$group_total += $table_total;
			$group_billing_total += $table_billing_total;
			$group_cost_total += $table_cost_total;
			$group_estimated_total += $table_estimated_total;
		}
		
		if (!is_array($group_obj['subgroups'])) return;
		
		$subgroups = order_groups_by_name($group_obj['subgroups']);
		
		foreach ($subgroups as $subgroup) {
			$sub_total = 0;
			$sub_total_billing = 0;
			$sub_total_cost = 0;
			$sub_total_estimated = 0;
			total_task_times_print_group($subgroup, $grouped_objects, $options, array(), $next_level, $prev . $group_obj['group']['id'] . "_", $sub_total, $sub_total_billing, $sub_total_cost, $sub_total_estimated);
			$group_total += $sub_total;
			$group_billing_total += $sub_total_billing;
			$group_cost_total += $sub_total_cost;
			$group_estimated_total += $sub_total_estimated;
		}
		
		$total += $group_total;
		$billing_total += $group_billing_total;
		$cost_total += $group_cost_total;
		$estimated_total += $group_estimated_total;
		
		$def_currency = Currencies::getDefaultCurrencyInfo();
		$def_c_symbol = !empty($def_currency) ? $def_currency['symbol'] : config_option('currency_code', '$');
		
		
		$gname_colspan = 4;
		/*if (!array_var($options, 'show_estimated_time')) $gname_colspan++;
		if (!array_var($options, 'show_cost')) $gname_colspan++;
		if (!array_var($options, 'show_billing')) $gname_colspan++;
		*/
		echo '<tbody><tr>';
		
		echo '<td class="bold" colspan="'.$gname_colspan.'">'.$group_name.'</td>';
		
		if (array_var($options, 'show_billing') == 'checked') {
			echo '<td class="bold right">' . $def_c_symbol . " " . number_format($billing_total, 2) . '</td>';
		}
		
		if (array_var($options, 'show_cost') == 'checked') {
			echo '<td class="bold right">' . $def_c_symbol . " " . number_format($cost_total, 2) . '</td>';
		}
		
		echo '<td class="bold right">' . DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($group_total * 60), "hm", 60) . '</td>';
		
		if ((array_var($options, 'timeslot_type') == 0 || array_var($options, 'timeslot_type') == 2) && array_var($options, 'show_estimated_time')) {
			echo '<td class="bold right">' . DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($estimated_total * 60), "hm", 60) . '</td>';
		}
		
		echo '</tr></tbody>';
		
		/*
		echo '<div style="margin-left:' . $margin_left . 'px;padding-right:4px;" class="report-group-footer">' . $group_name;
		if ((array_var($options, 'timeslot_type') == 0 || array_var($options, 'timeslot_type') == 2) && array_var($options, 'show_estimated_time')) {
			echo '<div style="float:right;width:140px;" class="bold right">' . DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($estimated_total * 60), "hm", 60) . '</div>';
		}
		echo '<div style="float:right;width:140px;" class="bold right">' . DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($group_total * 60), "hm", 60) . '</div>';

		if (array_var($options, 'show_cost') == 'checked') {
			echo '<div style="float:right;width:140px;text-align:right;margin-left:8px;" class="bold">' . $def_c_symbol . " " . number_format($cost_total, 2) . '</div>';
		}
		
		if (array_var($options, 'show_billing') == 'checked') {
			echo '<div style="float:right;" class="bold">' . $def_c_symbol . " " . number_format($billing_total, 2) . '</div>';
		}
		echo '</div>';
		*/
		
	}
	
	
	function total_task_times_print_table($objects, $left, $options, $group_name, &$sub_total = 0, &$sub_total_billing = 0, &$sub_total_cost = 0, &$sub_total_estimated = 0) {
		//echo '<div style="padding-left:'. $left .'px;">';
		//echo '<table class="reporting-table"><tr class="reporting-table-heading">';
		echo '<tbody ><tr class="reporting-table-heading">';
		echo '<th>' . lang('date') . '</th>';
		echo '<th>' . lang('title') . '</th>';
		echo '<th>' . lang('description') . '</th>';
		echo '<th>' . lang('person') . '</th>';
		if (array_var($options, 'show_billing') == 'checked') {
			echo '<th class="right">' . lang('billing') . '</th>';
		}
		if (array_var($options, 'show_cost') == 'checked') {
			echo '<th class="right">' . lang('cost') . '</th>';
		}
		echo '<th class="right">' . lang('time') . '</th>';
		if ((array_var($options, 'timeslot_type') == 0 || array_var($options, 'timeslot_type') == 2) && array_var($options, 'show_estimated_time')) {
			echo '<th class="right">' . lang('estimated') . '</th>';
		}
		echo '</tr>';
		
		$sub_total = 0;
		$tasks = array();
		
		$alt_cls = "";
		foreach ($objects as $ts) { /* @var $ts Timeslot */
			echo "<tr $alt_cls>";
			echo "<td class='date'>" . format_date($ts->getStartTime()) . "</td>";
			echo "<td class='name'>" . ($ts->getRelObjectId() == 0 ? clean($ts->getObjectName()) : clean($ts->getRelObject()->getObjectName())) ."</td>";
			echo "<td class='name'>" . nl2br(clean($ts->getDescription())) ."</td>";
			echo "<td class='person'>" . clean($ts->getUser() instanceof Contact ? $ts->getUser()->getObjectName() : '') ."</td>";
			
			if (array_var($options, 'show_billing') == 'checked') {
				$currency = Currencies::getCurrency($ts->getRateCurrencyId());
				$c_symbol = $currency instanceof Currency ? $currency->getSymbol() : config_option('currency_code', '$');
				
				if($ts->getIsFixedBilling()){
					echo "<td class='nobr right'>" . $c_symbol . " " . number_format($ts->getFixedBilling(), 2) . "</td>";
					$sub_total_billing += $ts->getFixedBilling();
				}else{
					$min = $ts->getMinutes();
					echo "<td class='nobr right'>" . $c_symbol . " " . number_format(($ts->getHourlyBilling()/60) * $min, 2) . "</td>";
					$sub_total_billing += ($ts->getHourlyBilling()/60) * $min;
				}
			}
			
			if (array_var($options, 'show_cost') == 'checked') {
				$currency = Currencies::getCurrency($ts->getColumnValue('cost_currency_id'));
				$c_symbol = $currency instanceof Currency ? $currency->getSymbol() : config_option('currency_code', '$');
				
				if($ts->getColumnValue('is_fixed_cost')){
					echo "<td class='nobr right' style='width:140px;'>" . $c_symbol . " " . number_format($ts->getColumnValue('fixed_cost'), 2) . "</td>";
					$sub_total_cost += $ts->getColumnValue('fixed_cost');
				}else{
					$min = $ts->getMinutes();
					echo "<td class='nobr right' style='width:140px;'>" . $c_symbol . " " . number_format(($ts->getColumnValue('hourly_cost')/60) * $min, 2) . "</td>";
					$sub_total_cost += ($ts->getColumnValue('hourly_cost')/60) * $min;
				}
			}
			
			$lastStop = $ts->getEndTime() != null ? $ts->getEndTime() : ($ts->isPaused() ? $ts->getPausedOn() : DateTimeValueLib::now());
			echo "<td class='time nobr right'>" . DateTimeValue::FormatTimeDiff($ts->getStartTime(), $lastStop, "hm", 60, $ts->getSubtract()) ."</td>";
			if((array_var($options, 'timeslot_type') == 0 || array_var($options, 'timeslot_type') == 2) && $ts->getRelObject() instanceof ProjectTask && array_var($options, 'show_estimated_time')) {
				echo "<td class='time nobr right'>" . DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($ts->getRelObject()->getTimeEstimate() * 60), 'hm', 60) ."</td>";
				$task = $ts->getRelObject();
				
				//check if I have the estimated time of this task
				if(!in_array($task->getId(), $tasks)){
					$sub_total_estimated += $task->getTimeEstimate();
				}
				$tasks[] = $task->getId();
			} elseif ((array_var($options, 'timeslot_type') == 0 || array_var($options, 'timeslot_type') == 2) && array_var($options, 'show_estimated_time')){
				echo "<td class='time nobr right'> 0 </td>";
			}
			echo "</tr>";
			
			$sub_total += $ts->getMinutes();
			$alt_cls = $alt_cls == "" ? 'class="alt-row"' : "";
		}
		
		//echo '</table></div>';
		echo '</tbody>';
	}
        
	function has_difference($previousTSRow, $tsRow, $field){
		
		if (is_array($previousTSRow))
			$previousTS = $previousTSRow["ts"];
		$ts = $tsRow["ts"];
		
		return !isset($previousTS) || $previousTS == null ||
				($field == 'id' && $previousTS->getObject()->getId() != $ts->getObject()->getId()) ||
				($field == 'user_id' && $previousTS->getUserId() != $ts->getUserId()) ||
				($field == 'state' && $previousTS->getObject()->getState() != $ts->getObject()->getState()) ||
				($field == 'project_id_0' && $previousTSRow["wsId0"] != $tsRow["wsId0"]) ||
				($field == 'project_id_1' && $previousTSRow["wsId1"] != $tsRow["wsId1"]) ||
				($field == 'project_id_2' && $previousTSRow["wsId2"] != $tsRow["wsId2"]) ||
				($field == 'priority' && $previousTS->getObject()->getPriority() != $ts->getObject()->getPriority()) ||
				($field == 'milestone_id' && $previousTS->getObject()->getMilestoneId() != $ts->getObject()->getMilestoneId());
	}
	
	function get_cols($columns){ //get the columns selected by the user to be shown
		if (!is_array($columns)) $columns = array();
		$cols = array();		
		foreach($columns as $k=>$i){					
			if ($i != 0){
				$cols[] = $k;
			}		 					
		}		
		return $cols;		
	}
	
	function count_extra_cols($columns){ //counts the columns selected by the user to be shown
		$cols = get_cols($columns);
		if ($cols == null)
			return 0;
		else
			return count($cols);
	}
	?>

	<?php if (count(active_context_members(false)) > 0) : ?>
		<div class="clear"></div>
		<div style="margin-bottom: 10px; padding-bottom: 5px; float:left;">
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
		<div class="clear"></div>
	<?php endif; ?>

	<?php
	$def_currency = Currencies::getDefaultCurrencyInfo();
	$def_c_symbol = !empty($def_currency) ? $def_currency['symbol'] : config_option('currency_code', '$');
	
	$sectionDepth = 0;
	$totCols = 7 + count_extra_cols($columns);
	$date_format = user_config_option('date_format');

	if (array_var($post, 'date_type') != 6) {
		if ($start_time instanceof DateTimeValue) $start_time->advance(logged_user()->getUserTimezoneValue(), true);
		if ($end_time instanceof DateTimeValue) $end_time->advance(logged_user()->getUserTimezoneValue(), true);
	}
	
	if ($start_time instanceof DateTimeValue) { ?>
		<span class="bold"><?php echo lang('from')?></span>:&nbsp;<?php echo $start_time->format($date_format) ?>
	<?php }
	if ($end_time instanceof DateTimeValue) { ?>
		<span class="bold" style="padding-left:10px"><?php echo lang('to date')?></span>:&nbsp;<?php echo $end_time->format($date_format) ?>
	<?php } ?>
	
	<?php if (isset($timeslot_type)){ ?>
		<br />
		<span class="bold"><?php echo lang("timeslots")?></span>:&nbsp;<?php echo $timeslot_type; ?>
	<?php }	?>

	<?php if ($user instanceof Contact) { ?>
		<br />
		<span class="bold"><?php echo lang('reporting user')?></span>:&nbsp;<?php echo clean($user->getObjectName()); ?>
	<?php }	?>

	<?php if(isset($timeslots_grouped_by)){ ?>
		<br />
		<span class="bold"><?php echo lang("group by")?></span>:&nbsp;<?php echo $timeslots_grouped_by; ?>
	<?php }	?>

	<?php if (isset($task_status)){ ?>
		<br />
		<span class="bold"><?php echo lang('task status')?></span>:&nbsp;<?php echo $task_status; ?>
	<?php }	?>

		
	
	<div class="timeslot-report-container">
		<table class="reporting-table">
        <?php
        if(count($grouped_timeslots) > 0){
            $total = 0;
            $billing_total = 0;
            $cost_total = 0;
            $estimated_total = 0;
            
            $groups = order_groups_by_name($grouped_timeslots['groups']);
            foreach ($groups as $gid => $group_obj) {
                    $tmp_total = 0;
                    $tmp_billing_total = 0;
                    $tmp_cost_total = 0;
                    $tmp_estimated_total = 0;
                    total_task_times_print_group($group_obj, $grouped_timeslots['grouped_objects'], array_var($_SESSION, 'total_task_times_report_data'), array(), 0, "", $tmp_total, $tmp_billing_total, $tmp_cost_total, $tmp_estimated_total);
                    $total += $tmp_total;
                    $billing_total += $tmp_billing_total;
                    $cost_total += $tmp_cost_total;
                    $estimated_total += $tmp_estimated_total;
            }
            if(count($groups) >0){
            ?>
            
            <tbody class="bold report-group-footer" style="font-size:150%;">
            <tr>
            	<td colspan="4"><?php echo lang('total').": "; ?></td>
            	
            	<?php if (array_var(array_var($_SESSION, 'total_task_times_report_data'), 'show_billing') == 'checked') { ?>
            	<td class="right"><?php echo $def_c_symbol . " " . number_format($billing_total, 2) ?></td>
            	<?php }?>
            	
            	<?php if (array_var(array_var($_SESSION, 'total_task_times_report_data'), 'show_cost') == 'checked') { ?>
            	<td class="right"><?php echo $def_c_symbol . " " . number_format($cost_total, 2) ?></td>
            	<?php }?>
            	
            	<td class="right"><?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($total * 60), "hm", 60) ?></td>
            	
            	<?php if ((array_var(array_var($_SESSION, 'total_task_times_report_data'), 'timeslot_type') == 0 || array_var(array_var($_SESSION, 'total_task_times_report_data'), 'timeslot_type') == 2 ) && array_var(array_var($_SESSION, 'total_task_times_report_data'), 'show_estimated_time')) { ?>
            	<td class="right"><?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($estimated_total * 60), "hm", 60) ?></td>
            	<?php } ?>
            	
            </tr>
            </tbody>
            <?php }?>
        <?php }?>
        </table>
	</div>
    <?php 
	$sumTime = 0;
	$sumBilling = 0;
	$sumCost = 0;
	$showBillingCol = array_var($post, 'show_billing', false);
	$showCostsCol = array_var($post, 'show_cost', false);
	

		for ($i = $sectionDepth - 1; $i >= 0; $i--){?>
<tr style="padding-top:2px;text-align:right;font-weight:bold;">
	<td style="padding:4px;border-top:2px solid #888;font-size:90%;color:#AAA;text-align:left;font-weight:normal"><?php echo truncate(clean(getGroupTitle($group_by[$i], $previousTSRow)),40,'&hellip;') ?></td>
	<td colspan=<?php echo ($showBillingCol)? $totCols -2 : $totCols -1 ?> style="padding:4px;border-top:2px solid #888;text-align:right;"><?php echo lang('total') ?>:&nbsp;<?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($sumTimes[$i] * 60), "hm", 60) ?></td>
	
	<?php if ($showBillingCol) { ?>
	<td style="width:30px;padding:4px;border-top:2px solid #888;text-align:right;">
		<?php echo $def_c_symbol ?>&nbsp;<?php echo $sumBillings[$i] ?>
	</td>
	<?php } ?>
	
	<?php if ($showCostsCol) { ?>
	<td style="width:30px;padding:4px;border-top:2px solid #888;text-align:right;">
		<?php echo $def_c_symbol ?>&nbsp;<?php echo $sumCosts[$i] ?>
	</td>
	<?php } ?>
	
</tr></table></div></td></tr>
	<?php }?>
<?php 
// TOTAL TIME
