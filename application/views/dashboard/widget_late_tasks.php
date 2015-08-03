<?php
	$show_help_option = user_config_option('show_context_help'); 
	if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_late_tasks_widget_context_help', true, logged_user()->getId()))) { 
		render_context_help($this, 'chelp late tasks widget', 'late_tasks_widget');
	} // if ?>

<div style="padding:10px">
		
<?php if($hasLate) { 
	$c = 0;
	?>
<div>
  <table id="dashTableMS" style="width:100%">
<?php
	if (isset($late_milestones) && is_array($late_milestones) && count($late_milestones))
	foreach($late_milestones as $milestone) { 
	$c++;
	?>
	<tr class="<?php echo $c % 2 == 1? '':'dashAltRow'; echo ' ' . ($c > 5? 'noDispLM':''); ?>" style="<?php echo $c > 5? 'display:none':'' ?>">
	<td><div class="db-ico ico-milestone">&nbsp;</div></td>
    <td style="padding-left:5px;padding-bottom:2px">
    <?php $dws = $milestone->getWorkspaces(logged_user()->getWorkspacesQuery());
		$projectLinks = array();
		foreach ($dws as $ws) {
			$projectLinks[] = $ws->getId();
		}
		echo '<span class="project-replace">' . implode(',',$projectLinks) . '</span>';?>
    <a class="internalLink" href="<?php echo $milestone->getViewUrl() ?>" title="<?php echo clean($milestone->getName()) ?>">
<?php if($milestone->getAssignedTo() instanceof ApplicationDataObject) { ?>
    <span style="font-weight:bold"> <?php echo clean($milestone->getAssignedTo()->getObjectName()) ?>: </span><?php echo clean($milestone->getName()) ?>
<?php } else { ?>
    <?php echo clean($milestone->getName()) ?>
<?php } // if ?>
	</a></td>
    <td style="text-align:right;"><?php echo lang('days late', $milestone->getLateInDays()) ?></td>
	</tr>
<?php } // foreach ?>
<?php if ($c > 5) { ?>
<tr>
	<td></td>
	<td></td>
	<td>
			<div id="dashLM" style="width:100%; text-align:right">
		<a href="#" onclick="og.hideAndShowByClass('dashLM', 'noDispLM', 'dashTableMS'); return false;">
			<?php echo lang("show more amount", $c -5) ?>...
		</a>
		</div>
	</td>
</tr>
<?php
	
 	if ($c >= 10) {?>
	<tr class="noDispLM" style="display:none">
		<td></td><td></td>
		<td style="text-align:right">
		<a href="#" onmousedown="og.openLink(og.getUrl('task', 'new_list_tasks', {status:'pending'}), {caller:'tasks-panel'});" onclick="Ext.getCmp('tabs-panel').activate('tasks-panel');">
		<?php echo lang('show all') ?>...</a>
	</tr>
	<?php } ?>
<?php } //if ?>

<?php
	$c=1;
	if (isset($late_tasks) && is_array($late_tasks) && count($late_tasks))
	foreach($late_tasks as $task) { 
	$c++;
	?>
    <tr class="<?php echo $c % 2 == 1? '':'dashAltRow' ; echo ' ' . ($c > 5? 'noDispLT':''); ?>" style="<?php echo $c > 5? 'display:none':'' ?>"><td><div class="db-ico ico-task"></div></td>
    
    <td style="padding-left:5px;padding-bottom:2px">
    <?php 
		$dws = $task->getWorkspaces(logged_user()->getWorkspacesQuery());
		$projectLinks = array();
		foreach ($dws as $ws) {
			$projectLinks[] = $ws->getId();
		}
		echo '<span class="project-replace">' . implode(',',$projectLinks) . '</span>';?>
	<a class="internalLink" href="<?php echo $task->getViewUrl() ?>" title="<?php echo clean($task->getTitle()) ?>">
<?php if($task->getAssignedTo() instanceof ApplicationDataObject) { ?>
    <span style="font-weight:bold"> <?php echo clean($task->getAssignedTo()->getObjectName()) ?>: </span><?php echo clean(strlen($task->getTitle()) > 40 ? utf8_substr($task->getTitle(), 0, 40)." ..." : $task->getTitle()) ?>
<?php } else { ?>
    <?php echo clean($task->getTitle()) ?>
<?php } // if ?>
	</a></td>
     <td style="text-align:right"><?php echo lang('days late', $task->getLateInDays()) ?></td>
	</tr>
<?php } // foreach ?>

<?php if ($c > 5) { ?>
<tr>
	<td></td>
	<td></td>
	<td>
			<div id="dashLT" style="width:100%; text-align:right">
		<a href="#" onclick="og.hideAndShowByClass('dashLT', 'noDispLT', 'dashTableMS'); return false;">
			<?php echo lang("show more amount", $c -5) ?>...
		</a>
		</div>
	</td>
</tr>
<?php
	
 	if ($c >= 10) {?>
	<tr class="noDispLT" style="display:none">
		<td></td><td></td>
		<td style="text-align:right">
		<a href="#" onmousedown="og.openLink(og.getUrl('task', 'new_list_tasks', {status:'pending'}), {caller:'tasks-panel'});" onclick="Ext.getCmp('tabs-panel').activate('tasks-panel');">
		<?php echo lang('show all') ?>...</a>
	</tr>
	<?php } ?>
<?php } //if ?>

  </table>
  
 </div>
<?php } // if ?>

<?php if($hasToday) { 
	$c = 0; ?>
  <div class="dashSubtitle" style="<?php echo $hasLate ? '': 'padding-top:0px' ?>"><?php echo lang('today') ?></div>
  <div>
  <table id="todayTaskTable" style="width:100%">
<?php 
	if (isset($today_milestones) && is_array($today_milestones) && count($today_milestones))
	foreach($today_milestones as $milestone) { 
	$c++;?>
    <tr class="<?php echo $c % 2 == 1? '':'dashAltRow'; echo ' ' . ($c > 5? 'noDispToday':''); ?>" style="<?php echo $c > 5? 'display:none':'' ?>">
    <td><div class="db-ico ico-milestone"></div></td>
    <td style="padding-left:5px;padding-bottom:2px">
    <?php 
		$dws = $milestone->getWorkspaces(logged_user()->getWorkspacesQuery());
		$projectLinks = array();
		foreach ($dws as $ws) {
			$projectLinks[] = $ws->getId();
		}
		echo '<span class="project-replace">' . implode(',',$projectLinks) . '</span>';?>
    <a class="internalLink" href="<?php echo $milestone->getViewUrl() ?>" title="<?php echo clean($milestone->getName()) ?>">
<?php if($milestone->getAssignedTo() instanceof ApplicationDataObject) { ?>
    <span style="font-weight:bold"> <?php echo clean($milestone->getAssignedTo()->getObjectName()) ?>: </span><?php echo clean($milestone->getName()) ?>
<?php } else { ?>
    <?php echo clean($milestone->getName()) ?>
<?php } // if ?>
	</a></td></tr>
<?php } // foreach ?>

<?php if ($c > 5) { ?>
<tr>
	<td></td>
	<td>
			<div id="dashToday" style="width:100%; text-align:right">
		<a href="#" onclick="og.hideAndShowByClass('dashToday', 'noDispToday', 'todayTaskTable'); return false;">
			<?php echo lang("show more amount", $c -5) ?>...
		</a>
		</div>
	</td>
</tr>
<?php
	if ($c >= 10) {?>
	<tr class="noDispToday" style="display:none">
		<td></td>
		<td style="text-align:right">
		<a href="#" onmousedown="og.openLink(og.getUrl('task', 'new_list_tasks', {status:'pending'}), {caller:'tasks-panel'});" onclick="Ext.getCmp('tabs-panel').activate('tasks-panel');">
		<?php echo lang('show all') ?>Today Miles...</a>
	</tr>
	<?php } ?>
<?php } //if ?>

<?php 
	$c=0;
	if (isset($today_tasks) && is_array($today_tasks) && count($today_tasks))
	foreach($today_tasks as $task) { 
	$c++;?>
	
    <tr class="<?php echo $c % 2 == 1? '':'dashAltRow' ; echo ' ' . ($c > 5? 'noDispTodayT':''); ?>" style="<?php echo $c > 5? 'display:none':'' ?>">
    <td><div class="db-ico ico-task"></div></td>
    <td style="padding-left:5px;padding-bottom:2px">
    <?php 
		$dws = $task->getWorkspaces(logged_user()->getWorkspacesQuery());
		$projectLinks = array();
		foreach ($dws as $ws) {
			$projectLinks[] =$ws->getId();
		}
		echo  '<span class="project-replace">' . implode(',',$projectLinks) . '</span>';?>
	<a class="internalLink" href="<?php echo $task->getViewUrl() ?>" title="<?php echo clean($task->getTitle()) ?>">
<?php if($task->getAssignedTo() instanceof ApplicationDataObject) { ?>
    <span style="font-weight:bold"> <?php echo clean($task->getAssignedTo()->getObjectName()) ?>: </span><?php echo clean(strlen($task->getTitle()) > 40 ? utf8_substr($task->getTitle(), 0, 40)." ..." : $task->getTitle()) ?>
<?php } else { ?>
    <?php echo clean($task->getTitle()) ?>
<?php } // if ?>
	</a></td></tr>
<?php } // foreach ?>

<?php if ($c > 5) { ?>
<tr>
	<td></td>
	<td>
			<div id="dashTodayT" style="width:100%; text-align:right">
		<a href="#" onclick="og.hideAndShowByClass('dashTodayT', 'noDispTodayT', 'todayTaskTable'); return false;">
			<?php echo lang("show more amount", $c -5) ?>...
		</a>
		</div>
	</td>
</tr>
<?php
	if ($c >= 10) {?>
	<tr class="noDispTodayT" style="display:none">
		<td></td>
		<td style="text-align:right">
		<a href="#" onmousedown="og.openLink(og.getUrl('task', 'new_list_tasks', {status:'pending'}), {caller:'tasks-panel'});" onclick="Ext.getCmp('tabs-panel').activate('tasks-panel');">
		<?php echo lang('show all') ?>...</a>
	</tr>
	<?php } ?>
<?php } //if ?>

  </table></div>
<?php } // if ?>
</div>