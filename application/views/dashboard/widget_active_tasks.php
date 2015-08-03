
<?php
$show_help_option = user_config_option('show_context_help'); 
if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_active_tasks_widget_context_help', true, logged_user()->getId()))) { 
	render_context_help($this, 'chelp active tasks widget', 'active_tasks_widget');
} ?>

<div style="padding:10px">
<table id="dashTableTIP" style="width:100%;">
<?php
$c = 0;
foreach ($tasks_in_progress as $task) {
	$stCount = $task->countAllSubTasks();
	$c++;
	$text = $task->getText();
	if ($text != '')
		$text = ": " . $text;
	if(strlen_utf($text)>100)
		$text = substr_utf($text,0,100) . " ...";
	$text = clean($text);
	?>
		<tr class="<?php echo $c % 2 == 1? '':'dashAltRow'?>"><td><div class="db-ico ico-task">&nbsp;></div></td><td style="padding-left:5px;padding-bottom:2px">
	<?php $dws = $task->getWorkspaces(logged_user()->getWorkspacesQuery());
	$projectLinks = array();
	foreach ($dws as $ws) {
		$projectLinks[] = $ws->getId();
	}
	echo '<span class="project-replace">' . implode(',',$projectLinks) . '</span>';?>
	<a class='internalLink' href='<?php echo $task->getViewUrl() ?>'><?php echo clean($task->getTitle())?><?php echo $text ?></a></td>
	<?php /*<td align="right"><?php $timeslot = Timeslots::getOpenTimeslotByObject($task,logged_user());
		if ($timeslot) { 
			if (!$timeslot->isPaused()) {?>
			<div id="<?php echo $genid . $task->getId() ?>timespan"></div>
			<script>
			og.startClock('<?php echo $genid . $task->getId() ?>', <?php echo $timeslot->getSeconds() ?>);
			</script>
		<?php } else {?>
			<div id="<?php echo $genid . $task->getId() ?>timespan">
			<?php $totalSeconds = $timeslot->getSeconds(); 
		$seconds = $totalSeconds % 60;
		$minutes = (($totalSeconds - $seconds) / 60) % 60;
		$hours = (($totalSeconds - $seconds - ($minutes * 60)) / 3600);
		echo (($hours < 10)? '0':'') . $hours . ':' . (($minutes < 10)? '0':'') . $minutes . ':' . (($seconds < 10)? '0':'') . $seconds;
		?>
			</div>
			
		<?php }} ?>
		</td> */?>
	</tr>
<?php } // foreach ?>
</table>
</div>