<?php
$task_list = $object;
$time_estimate = $task_list->getTimeEstimate();
$total_minutes = $task_list->getTotalMinutes();
$overall_worked_time = $task_list->getOverallWorkedTime(); // includes subtask's worked time
$total_time_estimate = $task_list->getTotalTimeEstimate(); // includes subtask's time estimate
$pending_time = $time_estimate - $total_minutes;

if ($time_estimate >= 0 || $total_minutes >=0){?>
<br/>
<table>
<?php if(config_option('use_task_percent_completed')){ ?>
	<tr>
		<td><div style="font-weight:bold"><?php echo lang('percent completed'). ':&nbsp;'?></div></td>
		<td><?php echo taskPercentCompletedBar($task_list) //$task_list->getPercentCompleted() . "%"; ?></td>
	</tr>
<?php } ?>

<?php if ($total_time_estimate > 0 && config_option('use_task_estimated_time'))   {?>
	<tr>
		<td><div style="font-weight:bold"><?php echo lang('estimated time'). ':&nbsp;'?></div></td>
		<td><?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($time_estimate * 60), 'hm', 60) ?></td>
	</tr>

	<tr>
		<td><div style="font-weight:bold"><?php echo lang('total estimated time'). ':&nbsp;'?></div></td>
		<td><?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($total_time_estimate * 60), 'hm', 60) ?></td>
	</tr>
<?php }?>

<?php if ($overall_worked_time > 0 && $show_timeslot_section) {?>
	<tr>
		<td><div style="font-weight:bold"><?php echo lang('total time worked'). ':&nbsp;' ?></div></td>
		<td><span style="font-size:120%;font-weight:bold;<?php echo ($time_estimate > 0 && $total_minutes > $time_estimate) ? 'color:#FF0000':'' ?>"><?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($total_minutes * 60), 'hm', 60) ?></span></td>
	</tr>

	<tr>
		<td><div style="font-weight:bold"><?php echo lang('overall worked time'). ':&nbsp;' ?></div></td>
		<td><span style="font-size:120%;font-weight:bold;"><?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($overall_worked_time * 60), 'hm', 60) ?></span></td>
	</tr>

<?php } ?>

<?php if ($pending_time > 0 && (config_option('use_task_estimated_time') && config_option('use_task_pending_time'))) {?>
	<tr>
		<td><div style="font-weight:bold"><?php echo lang('pending time'). ':&nbsp;'?></div></td>
		<td><?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($pending_time * 60), 'hm', 60)?></td>
	</tr>
<?php } ?>

</table>
<?php if ($show_timeslot_section == '1' && config_option('use_task_percent_completed')){ ?>
	<div class="desc"><?php echo lang('percent completed detail', isset($counter) ? $counter : '0') ?></div>
<?php } ?>

<?php } ?>





