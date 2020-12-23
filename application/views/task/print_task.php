<style>
body {
	font-family: sans-serif;
	font-size:11px;
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
th {
	border-bottom:2px solid #333;
}
.body {
	margin-left: 20px;
	padding: 10px;
}
table {
	border-collapse:collapse;
	border-spacing:0;
}

.printHeader {
	border-bottom: 1px solid #AAA;
}
.og-custom-properties tr
{
border-bottom:1px solid black;
}

table.print-task-timeslots {
	width: 100%;
	border: 1px solid #333;
	border-bottom: 2px solid #333;
}
table.print-task-timeslots td {
	min-width: 100px;
	padding: 4px 8px;
	border-bottom: 1px solid #ccc;
}
table.print-task-timeslots th {
	padding: 10px 8px;
}

</style>

<div class="print" style="padding:7px;width:100%;max-width:1000px">

<div class="printHeader">
<table style="width:100%"><tr><td style="width:44px"><img src="public/assets/themes/default/images/32x32/tasks.png"/></td>
<td><h1 style="<?php echo $task->isCompleted()? 'text-decoration:line-through' : '' ?>"><?php echo clean($task->getObjectName()) ?></h1></td><td align=right style="color:#666;padding-left:10px">
<?php if ($task->getStartDate() || $task->getDueDate() ) { ?>
<table style="white-space:nowrap"><?php
	if ($task->getStartDate())
		echo '<tr><td align=right>' . lang('start date') . ':&nbsp;</td><td>' . $task->getStartDate()->format("M d"). '</td></tr>';
	if ($task->getDueDate())
		echo '<tr><td align=right>' . lang('due date') . ':&nbsp;</td><td>' . $task->getDueDate()->format("M d") . '</td></tr>';
?>
</table> <?php }// if ?>
</td></tr></table>
</div>

<?php if (count($task->getMembers()) > 0) { ?>
            <p><b>
		        <?php    	
				$contexts = array();
				$members =  $task->getMembers();
				if(count($members)>0){
					foreach ($members as $member){
						$dim = $member->getDimension();
						if($dim->getIsManageable()){
							if ($dim->getCode() == "customer_project" || $dim->getCode() == "customers"){
								$obj_type = ObjectTypes::findById($member->getObjectTypeId());
								if ($obj_type instanceof ObjectType) {
									echo lang($dim->getCode()). ": ";
									echo $contexts[$dim->getCode()][$obj_type->getName()][]= '<span style="'.get_workspace_css_properties($member->getMemberColor()).'">'. $member->getName() .'</span>';
									echo '<br />';
								}
							}else{
								echo lang($dim->getCode()). ": ";
								echo $contexts[$dim->getCode()][]= '<span style="'.get_workspace_css_properties($member->getMemberColor()).'">'. $member->getName() .'</span>';
								echo '<br />';
							}
						}
					}
				}
				?>
            </b></p>
<?php } // if ?>

<?php if ($task->getAssignedTo() instanceof Contact) { ?>
<p><b><?php echo lang('assigned to') ?>:</b>&nbsp;<?php echo clean($task->getAssignedToName()) ?></p>
<?php } // if ?>

<?php 
$priority = '';
if ($task->getPriority() >= ProjectTasks::PRIORITY_URGENT) {
	$priority = '<div class="og-task-priority-high"><span style="font-weight:bold">'.lang('task priority').": </span>".lang('urgent priority').'</div>';
} else if ($task->getPriority() >= ProjectTasks::PRIORITY_HIGH) {
	$priority = '<div class="og-task-priority-high"><span style="font-weight:bold">'.lang('task priority').": </span>".lang('high priority').'</div>';
} else if ($task->getPriority() == ProjectTasks::PRIORITY_NORMAL) {
	$priority = '<div class="og-task-priority-normal"><span style="font-weight:bold">'.lang('task priority').": </span>".lang('normal priority').'</div>';
} else if ($task->getPriority() <= ProjectTasks::PRIORITY_LOW) {
	$priority = '<div class="og-task-priority-low"><span style="font-weight:bold">'.lang('task priority').": </span>".lang('low priority').'</div>';
}
echo $priority;
?>

<?php if ($task->getMilestone() instanceof ProjectMilestone) { ?>
<p><b><?php echo lang('milestone') ?>:</b>&nbsp;<?php echo clean($task->getMilestone()->getObjectName()) ?></p>
<?php } // if ?>

<?php if ($task->getText() != '') { ?>
<p><b><?php echo lang('description') ?>:</b></p>
<div style="margin:5px 5px 5px 0;padding:5px;border:1px solid #AAA">
<?php 
    if($task->getTypeContent() == "text"){
        echo escape_html_whitespace(convert_to_links(clean($task->getText())));
    }else{
        echo purify_html(nl2br($task->getText()));
    }
?>
</div>
<?php } // if ?>

<?php 
	$hasIncompleteSubtasks = is_array($task->getOpenSubTasks()) && count($task->getOpenSubTasks()) > 0;
	$hasCompletedSubtasks = is_array($task->getCompletedSubTasks()) && count($task->getCompletedSubTasks()) > 0;
if ($hasIncompleteSubtasks || $hasCompletedSubtasks) { ?>
<div style="margin-bottom:0px;margin-top:20px"><img src="public/assets/themes/default/images/16x16/tasks.png"/>&nbsp;&nbsp;<b><?php echo lang('subtasks') ?>:</b></div>
<ul style="margin-top:2px">
<?php
	if ($hasIncompleteSubtasks) {
		$otArray = $task->getOpenSubTasks();
		foreach ($otArray as $ot){
			echo '<li>'. ($ot->getAssignedToContact() instanceof Contact ? '<b>' . $ot->getAssignedToName() . ':&nbsp;</b>' : ''). $ot->getObjectName() . '</li>';
		} // foreach
	} // if
	if ($hasCompletedSubtasks) {
		$otArray = $task->getCompletedSubTasks();
		foreach ($otArray as $ot){
			echo '<li style="text-decoration:line-through">'. ($ot->getAssignedToContact() instanceof Contact ? '<b>' . $ot->getAssignedToName() . ':&nbsp;</b>' : ''). $ot->getObjectName() . '</li>';
		} // foreach
	} // if?>
</ul>
<?php } // if ?>

<?php 
// estimated time
// worked time
// percent complete

$percent_completed = $task->getPercentCompleted();
if ($percent_completed > 100) $percent_completed = 100;
else if ($percent_completed < 0) $percent_completed = 0;


$time_estimate = $task->getTimeEstimate();
$total_minutes = $task->getTotalMinutes();
$pending_time = $time_estimate - $total_minutes;
?>

<p><b><?php echo lang('percent completed') ?>:</b>&nbsp;<?php echo $percent_completed .'%' ?></p>

<?php 
if ($time_estimate > 0) {
	?><p><b><?php echo lang('estimated time') ?>:</b>&nbsp;<?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($time_estimate * 60), 'hm', 60) ?></p><?php
}
if ($total_minutes > 0) {
	?><p><b><?php echo lang('total time worked') ?>:</b>&nbsp;<?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($total_minutes * 60), 'hm', 60) ?></p><?php
}
if ($pending_time > 0) {
	?><p><b><?php echo lang('pending time') ?>:</b>&nbsp;<?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($pending_time * 60), 'hm', 60) ?></p><?php
}
?>

<?php $timeslots = $task->getTimeslots();
	  if (count($timeslots) > 0) { ?>

<table class="print-task-timeslots">
	<tr><th><?php echo lang('name')?></th><th><?php echo lang('worked time')?></th><th><?php echo lang('start time')?></th><th><?php echo lang('description')?></th></tr>
<?php foreach ($timeslots as $timeslot) { /* @var $timeslot Timeslot */
	$contact = Contacts::findById($timeslot->getContactId());
	$contact_name = $contact ? $contact->getName() : '';
	format_time_column_value($value)
	?>
	<tr>
		<td><?php echo $contact_name?></td>
		<td style="text-align:center;"><?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($timeslot->getColumnValue('worked_time') * 60), 'hm', 60)?></td>
		<td><?php echo format_datetime($timeslot->getStartTime())?></td>
		<td><?php echo clean($timeslot->getDescription())?></td>
	</tr>
<?php } ?>
</table>

<?php } // if ?>

<br/>

<?php $has_custom_properties = CustomProperties::countAllCustomPropertiesByObjectType($task->getObjectTypeId()) > 0;
	  if ($has_custom_properties) { ?>
<p><b><?php echo lang('custom properties') ?>:</b></p>
<div style="margin-left:14px;padding:6px;border:1px solid #AAA">
<?php echo str_replace(lang('custom properties'), "", render_custom_properties($task));?>
</div>
<?php } ?>



<?php if ($task->hasComments() != '') { ?>
<br/>
<?php echo render_object_comments_for_print($task, $task->getViewUrl()); ?>
<?php } // if ?>

</div>

<script>
var myListf = document.getElementsByClassName("cpboolfalse");
for (var i=0;i<myListf.length;i++)
{
	myListf[i].innerHTML='<?php echo lang('no') ?>';
};

var myListt = document.getElementsByClassName("cpbooltrue");
for (var i=0;i<myListt.length;i++)
{
	myListt[i].innerHTML='<?php echo lang('yes') ?>';
};

window.print();
</script>