<?php
	
	function total_task_times_print_group($group_obj, $grouped_objects, $options, $skip_groups = array(), $level = 0, $prev = "", &$total = 0, &$billing_total = 0, &$estimated_total = 0) {
		
		$margin_left = 15 * $level;
		$cls_suffix = $level > 2 ? "all" : $level;
		$next_level = $level + 1;
			
		$group_name = $group_obj['group']['name'];
		echo '<div style="margin-left:' . $margin_left.'px;" class="report-group-heading-'.$cls_suffix.'">' . $group_name . '</div>';
		
		$mem_index = $prev . $group_obj['group']['id'];
		
		$group_total = 0;
		$group_billing_total = 0;
		$group_estimated_total = 0;
		
		$table_total = 0;
		$table_billing_total = 0;
		$table_estimated_total = 0;
		// draw the table for the values
		if (isset($grouped_objects[$mem_index]) && count($grouped_objects[$mem_index]) > 0) {
			total_task_times_print_table($grouped_objects[$mem_index], $margin_left, $options, $group_name, $table_total, $table_billing_total, $table_estimated_total);
			$group_total += $table_total;
			$group_billing_total += $table_billing_total;
			$group_estimated_total += $table_estimated_total;
		}
		
		if (!is_array($group_obj['subgroups'])) return;
		
		$subgroups = order_groups_by_name($group_obj['subgroups']);
		
		foreach ($subgroups as $subgroup) {
			$sub_total = 0;
			$sub_total_billing = 0;
			$sub_total_estimated = 0;
			total_task_times_print_group($subgroup, $grouped_objects, $options, array(), $next_level, $prev . $group_obj['group']['id'] . "_", $sub_total, $sub_total_billing, $sub_total_estimated);
			$group_total += $sub_total;
			$group_billing_total += $sub_total_billing;
			$group_estimated_total += $sub_total_estimated;
		}
		
		$total += $group_total;
		$billing_total += $group_billing_total;
		$estimated_total += $group_estimated_total;
		
		
		echo '<div style="margin-left:' . $margin_left . 'px;" class="report-group-footer">' . $group_name;
		if ((array_var($options, 'timeslot_type') == 0 || array_var($options, 'timeslot_type') == 2) && array_var($options, 'show_estimated_time')) {
			echo '<div style="float:right;width:140px;" class="bold right">' . DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($estimated_total * 60), "hm", 60) . '</div>';
		}
		echo '<div style="float:right;width:140px;" class="bold right">' . DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($group_total * 60), "hm", 60) . '</div>';
		if (array_var($options, 'show_billing') == 'checked') {
			echo '<div style="float:right;" class="bold">' . config_option('currency_code', '$') . " " . number_format($billing_total, 2) . '</div>';
		}
		echo '</div>';
		
	}
	
	
	function total_task_times_print_table($objects, $left, $options, $group_name, &$sub_total = 0, &$sub_total_billing = 0, &$sub_total_estimated = 0) {
		echo '<div style="padding-left:'. $left .'px;">';
		echo '<table class="reporting-table"><tr class="reporting-table-heading">';
		echo '<th>' . lang('date') . '</th>';
		echo '<th>' . lang('title') . '</th>';
		echo '<th>' . lang('description') . '</th>';
		echo '<th>' . lang('person') . '</th>';
		if (array_var($options, 'show_billing') == 'checked') {
			echo '<th class="right">' . lang('billing') . '</th>';
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
				if($ts->getIsFixedBilling()){
					echo "<td class='nobr right'>" . config_option('currency_code', '$') . " " . number_format($ts->getFixedBilling(), 2) . "</td>";
					$sub_total_billing += $ts->getFixedBilling();
				}else{
					$min = $ts->getMinutes();
					echo "<td class='nobr right'>" . config_option('currency_code', '$') . " " . number_format(($ts->getHourlyBilling()/60) * $min, 2) . "</td>";
					$sub_total_billing += ($ts->getHourlyBilling()/60) * $min;
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
		
		echo '</table></div>';
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
	
	$sectionDepth = 0;
	$totCols = 6 + count_extra_cols($columns);
	$date_format = user_config_option('date_format');

	if (array_var($post, 'date_type') != 6) {
		if ($start_time instanceof DateTimeValue) $start_time->advance(3600*logged_user()->getTimezone(), true);
		if ($end_time instanceof DateTimeValue) $end_time->advance(3600*logged_user()->getTimezone(), true);
	}
	
	if ($start_time instanceof DateTimeValue) { ?>
		<span class="bold"><?php echo lang('from')?></span>:&nbsp;<?php echo $start_time->format($date_format) ?>
	<?php }
	if ($end_time instanceof DateTimeValue) { ?>
		<span class="bold" style="padding-left:10px"><?php echo lang('to date')?></span>:&nbsp;<?php echo $end_time->format($date_format) ?>
	<?php } ?>
	
	<?php if ($user instanceof Contact) { ?>
		<br />
		<span class="bold"><?php echo lang('reporting user')?></span>:&nbsp;<?php echo clean($user->getObjectName()); ?>
	<?php }	?>

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
		
	
	<div class="timeslot-report-container">
        <?php
        if(count($grouped_timeslots) > 0){
            $total = 0;
            $billing_total = 0;
            $estimated_total = 0;
            
            $groups = order_groups_by_name($grouped_timeslots['groups']);
            foreach ($groups as $gid => $group_obj) {
                    $tmp_total = 0;
                    $tmp_billing_total = 0;
                    $tmp_estimated_total = 0;
                    total_task_times_print_group($group_obj, $grouped_timeslots['grouped_objects'], array_var($_SESSION, 'total_task_times_report_data'), array(), 0, "", $tmp_total, $tmp_billing_total, $tmp_estimated_total);
                    $total += $tmp_total;
                    $billing_total += $tmp_billing_total;
                    $estimated_total += $tmp_estimated_total;
            }
            if(count($groups) >0){
            ?>
            <div class="clear"></div>
            <div class="report-group-footer" style="margin-top:10px;padding-top:10px;border-top:1px solid #bbb;">
            	<span class="bold" style="font-size:150%;"><?php echo lang('total').": "; ?></span>
            	<?php if ((array_var(array_var($_SESSION, 'total_task_times_report_data'), 'timeslot_type') == 0 || array_var(array_var($_SESSION, 'total_task_times_report_data'), 'timeslot_type') == 2 ) && array_var(array_var($_SESSION, 'total_task_times_report_data'), 'show_estimated_time')) { ?>
            	<div style="float:right;width:140px;" class="bold right"><?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($estimated_total * 60), "hm", 60) ?></div>
            	<?php } ?>
            	<div style="float:right;width:140px;" class="bold right"><?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($total * 60), "hm", 60) ?></div>
            	<?php if (array_var(array_var($_SESSION, 'total_task_times_report_data'), 'show_billing') == 'checked') { ?>
            	<div style="float:right;" class="bold"><?php echo config_option('currency_code', '$') . " " . number_format($billing_total, 2) ?></div>
            	<?php }?>
            </div>
            <?php }?>
        <?php }?>
	</div>
    <?php 
	$sumTime = 0;
	$sumBilling = 0;
	$showBillingCol = array_var($post, 'show_billing', false);
	if (count($timeslotsArray) > 0){
    ?>
        <table style="min-width:564px">
<?php if ($task_title) { ?>
		<div style="font-size:120%"><span style="font-weight:bold"><?php echo lang('title')?></span>:&nbsp;<?php echo clean($task_title) ?></div> 
<?php } ?>
		<br/><br/>
            
        <?php 
	//Initialize
	$headerPrinted = false;
	$gbvals = array('','','');
	$sumTimes = array(0,0,0);
	$sumBillings = array(0,0,0);
	$hasGroupBy = is_array($group_by) && count($group_by) > 0;
	$sectionDepth = $hasGroupBy ? count($group_by) : 0;
	$c = 0;
	for ($i = 0; $i < $sectionDepth; $i++) {
		if ($group_by[$i] == 'project_id'){
			$group_by[$i] = 'project_id_' . $c;
			$c++;
		}
	}
	$showSelCol = false; //show selected columns
	$showUserCol = !in_array('user_id', $group_by);
	$showTitleCol = !in_array('id', $group_by);
	if (!$showUserCol) $totCols--;
	if (!$showTitleCol) $totCols--;
	if (!$showBillingCol) $totCols--;
	if (count_extra_cols($columns)>0) $showSelCol = true;
	
	$previousTSRow = null;
        $isAlt = true;
	foreach ($timeslotsArray as $ts)	{
		$showHeaderRow = false;
		//to skip showing workspaces in case there are conditions
		if (isset($has_conditions) && $has_conditions) continue;
		//Footers
		for ($i = $sectionDepth - 1; $i >= 0; $i--){
			$has_difference = false;
			for ($j = 0; $j <= $i; $j++) {
				$has_difference = $has_difference || has_difference($previousTSRow,$ts, $group_by[$j]);
			}
			
			if ($has_difference){
				if ($previousTSRow != null) {
			?>		
<tr style="padding-top:2px;font-weight:bold;">
	<td style="padding:4px;border-top:2px solid #888;font-size:90%;color:#AAA;text-align:left;font-weight:normal"><?php echo truncate(clean(getGroupTitle($group_by[$i], $previousTSRow)),40,'&hellip;') ?></td>
	<td colspan=<?php echo ($showBillingCol)? $totCols -2 : $totCols -1 ?> style="padding:4px;border-top:2px solid #888;text-align:right;"><?php echo lang('total') ?>:&nbsp;<?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($sumTimes[$i] * 60), "hm", 60) ?></td>
	<?php if ($showBillingCol) {
		?><td style="width:30px;padding:4px;border-top:2px solid #888;text-align:right;"><?php echo config_option('currency_code', '$') ?>&nbsp;<?php echo $sumBillings[$i] ?></td><?php 
	} ?>
</tr></table></div></td></tr><?php
				}
				$sumTimes[$i] = 0;
				$sumBillings[$i] = 0;
			}
		}
		
		//Headers
		$has_difference = false;
		for ($i = 0; $i < $sectionDepth; $i++){
			$colspan = 3 - $i;
			$has_difference = $has_difference || has_difference($previousTSRow,$ts, $group_by[$i]);
			$showHeaderRow = $has_difference || $showHeaderRow;
			
			if ($has_difference){?>
			<tr><td colspan=<?php echo $totCols ?>><div style="width=100%;<?php echo $i > 0 ? 'padding-left:20px;padding-right:10px;' : '' ?>padding-top:10px;padding-bottom:5px;"><table style="width:100%">
<tr><td colspan=<?php echo $totCols ?> style="border-bottom:2px solid #888;font-size:<?php echo (150 - (15 * $i)) ?>%;font-weight:bold;">
	<?php echo clean(getGroupTitle($group_by[$i], $ts)) ?></td></tr>

<?php 		}
			$sumTimes[$i] += $ts->getMinutes();
                        if($ts->getIsFixedBilling()){
                            $sumBillings[$i] += $ts->getFixedBilling();
                        }else{
                            $sumBillings[$i] += ($ts->getHourlyBilling()/60) * $ts->getMinutes();
                        }
		}
		
		$isAlt = !$isAlt;
		$previousTSRow = $ts;
		
		if ($showHeaderRow || (!$hasGroupBy && !$headerPrinted)) {
			$headerPrinted = true;
		?><tr><th style="padding:4px;border-bottom:1px solid #666666;width:70px"><?php echo lang('date') ?></th>
	<th style="padding:4px;border-bottom:1px solid #666666"><?php echo lang('description') ?></th>
	<?php if ($showUserCol) { ?><th style="padding:4px;border-bottom:1px solid #666666"><?php echo lang('user') ?></th><?php } ?>
	<th style="padding:4px;text-align:right;border-bottom:1px solid #666666"><?php echo lang('time') ?></th>
	<?php if ($showBillingCol) { ?><th style="padding:4px;text-align:right;border-bottom:1px solid #666666"><?php echo lang('billing') ?></th><?php } ?>
	<?php if ($showSelCol) {
			$cols = get_cols($columns);
			foreach ($cols as $k => $i){
				if (!is_numeric($i)){
					?><th style="padding:4px;border-bottom:1px solid #666666"><?php echo lang("field ProjectTasks ".$i) ?></th><?php 
				} 
				else {
					$cp = CustomProperties::getCustomProperty($i);
					?><th style="padding:4px;border-bottom:1px solid #666666"><?php echo ($cp->getName()) ?></th><?php
				}
			}
		  }//if 
	} ?></tr><?php
		
		//Print row info
?>
<tr>
	<td style="padding:4px;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo format_datetime($ts->getStartTime(), $date_format)?></td>
	<td style="padding:4px; width:250px;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo clean($ts->getDescription()) ?></td>
	<?php if ($showUserCol) { ?><td style="padding:4px;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo clean(Contacts::getUserDisplayName($ts->getContactId())) ?></td><?php } ?>
	<?php $lastStop = $ts->getEndTime() != null ? $ts->getEndTime() : ($ts->isPaused() ? $ts->getPausedOn() : DateTimeValueLib::now()); ?>
	<td style="padding:4px;text-align:right;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo DateTimeValue::FormatTimeDiff($ts->getStartTime(), $lastStop, "hm", 60, $ts->getSubtract()) ?></td>
	<?php if ($showBillingCol) { ?><td style="padding:4px;text-align:right;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo config_option('currency_code', '$') ?>&nbsp;<?php echo $ts->getFixedBilling() ?></td><?php } ?>
	<?php if ($showSelCol) {
			$cols = get_cols($columns);	
			foreach ($cols as $k => $i){
				if ($ts->getObjectManager() == 'ProjectTasks'){
					$task = $ts->getObject();						
						if (!is_numeric($i)){	//for normal properties		
												//currently disabled as at the moment the only columns that can be added are custom properties							
								$value = format_value_to_print_task($task->getColumnValue($i),$task->getColumnType($i));
								?><td style="padding:4px;max-width:250px;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo ($value) ?></td><?php			
							
						} 
						else {//for custom properties									
							$values = CustomPropertyValues::getCustomPropertyValue($task->getId(), $i);	
							if ($values != null){
								$cp = CustomProperties::getCustomProperty($i);											
								$value = format_value_to_print_task($values->getValue(),$cp->getOgType());
								?><td style="padding:4px;max-width:250px;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo ($value) ?></td><?php
							}else{						
								?><td style="padding:4px;max-width:250px;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo ('') ?></td><?php
							}							
						}
				} else{				
					?><td style="padding:4px;max-width:250px;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo ('') ?></td><?php
				}	
								
		   } //foreach ?>
</tr>
<?php } // if
	} //foreach
}

		for ($i = $sectionDepth - 1; $i >= 0; $i--){?>
<tr style="padding-top:2px;text-align:right;font-weight:bold;">
	<td style="padding:4px;border-top:2px solid #888;font-size:90%;color:#AAA;text-align:left;font-weight:normal"><?php echo truncate(clean(getGroupTitle($group_by[$i], $previousTSRow)),40,'&hellip;') ?></td>
	<td colspan=<?php echo ($showBillingCol)? $totCols -2 : $totCols -1 ?> style="padding:4px;border-top:2px solid #888;text-align:right;"><?php echo lang('total') ?>:&nbsp;<?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($sumTimes[$i] * 60), "hm", 60) ?></td>
	<?php if ($showBillingCol) { ?><td style="width:30px;padding:4px;border-top:2px solid #888;text-align:right;"><?php echo config_option('currency_code', '$') ?>&nbsp;<?php echo $sumBillings[$i] ?></td><?php } ?>
</tr></table></div></td></tr>
	<?php }?>
<?php 
// TOTAL TIME
if (count($timeslotsArray) > 0) {
	foreach ($timeslotsArray as $t) {
		if (isset($has_conditions) && $has_conditions && $t->getObjectManager() == 'Projects') continue;
		$sumTime += $t->getMinutes();
		if($ts->getIsFixedBilling()){
			$sumBilling += $t->getFixedBilling();
		}else{
			$sumBilling += ($ts->getHourlyBilling()/60) * $ts->getMinutes();
		}
	}
?>
<tr><td style="text-align: right; border-top: 1px solid #AAA; padding: 10px 0; font-weight: bold;" colspan=<?php echo ($showBillingCol)? $totCols -1 : $totCols ?>>
<div ><?php echo strtoupper(lang("total")) . ": " . DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($sumTime * 60), "hm", 60) ?></div>
</td><?php if ($showBillingCol) { ?><td style="width:30px;padding-left:8px;border-top: 1px solid #AAA;"><div style="text-align: right;padding: 10px 0; font-weight: bold;"><?php echo config_option('currency_code', '$') ?>&nbsp;<?php echo $sumBilling ?></div></td><?php } ?>
</tr>
</table>
<?php }?>