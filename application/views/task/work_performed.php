<?php
$task_list = $object;
$time_estimate = $task_list->getTimeEstimate();
$total_minutes = $task_list->getTotalMinutes();
$pending_time = $time_estimate - $total_minutes;

if ($time_estimate >= 0 || $total_minutes >=0){?>
<br/>
<table>
<tr>
	<td><div style="font-weight:bold"><?php echo lang('percent completed'). ':&nbsp;'?></div></td>
	<td><?php echo taskPercentCompletedBar($task_list) //$task_list->getPercentCompleted() . "%"; ?></td>
</tr>

<?php if ($time_estimate > 0) {?>
<tr><td>
	<div style="font-weight:bold"><?php echo lang('estimated time'). ':&nbsp;'?></div></td><td> 
		<?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($time_estimate * 60), 'hm', 60) ?></td></tr>
<?php }?>

<?php if ($total_minutes > 0) {?>
	<tr><td><div style="font-weight:bold"><?php echo lang('total time worked'). ':&nbsp;' ?></div></td><td>
		<span style="font-size:120%;font-weight:bold;<?php echo ($time_estimate > 0 && $total_minutes > $time_estimate) ? 'color:#FF0000':'' ?>">
			<?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($total_minutes * 60), 'hm', 60) ?>
		</span></td></tr>
<?php } ?>

<tr>
	<td><div style="font-weight:bold"><?php echo lang('pending time'). ':&nbsp;'?></div></td>
	<td><?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($pending_time * 60), 'hm', 60)?></td>
</tr>
</table>

<div class="desc"><?php echo lang('percent completed detail', isset($counter) ? $counter : '0') ?></div>

<?php } ?>





