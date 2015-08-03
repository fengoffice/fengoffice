<?php
$container_id = gen_id();
?>
<div id="<?php echo $container_id ?>"></div>
<script>
var tasks = [
<?php $first = true;
foreach ($tasks as $task) {
	/*if ($task->getMilestoneId() != 0) {
		// don't show in tasks tasks that will also be listed under milestones.
		// if this is removed, tasks will appear twice. something needs to be done
		// so that updates on one of them reflects on the other.
		continue;
	}*/
	if ($first) {
		$first = false;
	} else {
		echo ",";
	}
	
	$taskInfo = "id:" . $task->getId() . "," .
		"title:'" . str_replace("\n"," ",str_replace("'", "\\'", $task->getTitle())) . "'," .
		"parent:" . $task->getParentId() . "," .
		"milestone:" . $task->getMilestoneId() . "," .
		"subtasks:[]," .
		"assignedTo:'" . str_replace("'", "\\'", $task->getAssignedTo() == null ? '' : $task->getAssignedToName()) . "'," .
		"expanded:false," .
		"completed:" . ($task->isCompleted()?"true":"false") . "," .
		"completedBy:'" . str_replace("'", "\\'", $task->getCompletedByName()) . "'," .
		"isLate:" . ($task->isLate()?"true":"false") . "," .
		"daysLate:" . $task->getLateInDays() . "," .
		"priority:" . $task->getPriority() . "," .
		"duedate:" . ($task->getDueDate() ? $task->getDueDate()->getTimestamp() : '0') . "," . 
		"percentCompleted:" . $task->getPercentCompleted() . "," . 
		"order:" . $task->getOrder();
	
	echo '{' . $taskInfo . '}';
	} // foreach ?>
];

var pepe = new og.TaskItem({id:0,title:'p',expanded:true,subtasks:tasks,container:document.getElementById('<?php echo $container_id ?>'),showOnlySubtasks:true});
</script>

