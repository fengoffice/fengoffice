<?php
set_page_title(lang('task list'));

?>

<style>
body {
	font-family: sans-serif;
}
.header {
	border-bottom: 1px solid black;
	padding: 10px;
}
h1 {
	font-size: 150%;
	margin: 15px 0;
	
}
h2 {
	font-size: 120%;
	margin: 15px 0;
}
td {
	text-align: left;
	margin: 15px 0;
}
</style>

<div class="print-view-all-task">

<div class="header">

<b><?php
echo '<h1>' . lang('task report') .  '</h1> ';
echo lang('workspaces') ?>:</b> 
<?php 
if(active_project())
	echo active_project()->getName();
else 
	echo lang('all'); 
	
echo '<br><b>' . lang('assigned to') . ': </b>';
if(array_var($_GET, 'assigned_to','0:0') != '0:0')
	echo array_var($_GET, 'assigned_to','');
else 
	echo lang('anybody'); 
	
echo '<br><b>' . lang('status') . ': </b>';
if(array_var($_GET, 'status', 'pending')!='pending')
	echo lang(array_var($_GET, 'status', ""));
else 
	echo lang('pending'); 
	
echo '<br><b>' . lang('priority') . ': </b>';
if(array_var($_GET, 'priority', 'all')!='all'){
	$val = array_var($_GET, 'priority', '200') ;
	switch ($val){
		case '100': echo lang('low priority'); break ;
		case '200': echo lang('normal priority'); break ;
		case '300': echo lang('high priority'); break ;
		case '400': echo lang('urgent priority'); break ;
	}
}
else 
	echo lang('all'); 
	?>
	
	<br />
</div>

<table>
<tr><th><?php echo lang('completed')?></th>
<th><?php echo lang('due date')?></th>
<th><?php echo lang('assigned to')?></th>
<th><?php echo lang('title')?></th>
<th><?php echo lang('workspace')?></th>
<th><?php echo lang('updated')?></th>
</tr>
<?php	foreach ($tasks as $task) { 	?>
			<tr><td><?php echo ($task->isCompleted())?lang('yes'):lang('no') ?></td>
			<td><?php echo $task->getDueDate()?$task->getDueDate()->format("M d Y H:i:s"):lang('n/a')?></td>
			<td><?php echo $task->getAssignedToName()?></td>
			<td><?php echo $task->getTitle()?></td>
			<td><?php echo $task->getProject()->getName()?></td>
			<td><?php echo lang('last updated by on', $task->getUpdatedByDisplayName(), $task->getUpdatedOn()->format("M d Y H:i:s"))?></td>
			</tr>
<?php 	} ?>
</table>


</div>

<script>
window.print();
</script>