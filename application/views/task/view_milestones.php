<?php
$container_id = gen_id();
?>
<div id="<?php echo $container_id ?>"></div>
<script>
var milestones = [
<?php $first = true;
foreach ($milestones as $milestone) {
	if ($first) {
		$first = false;
	} else {
		echo ",";
	}
	$milestoneInfo = "id:" . $milestone->getId() .",".
		"title:'" . clean(str_replace("\n"," ",str_replace("'", "\\'", $milestone->getName()))) . "'," .
		"subtasks:[]," .
		"expanded:false," .
		"completed:" . ($milestone->isCompleted()?"true":"false") . "," .
		"completedBy:'" . clean(str_replace("'", "\\'", $milestone->getCompletedByName())) . "'," .
		"isLate:" . ($milestone->isLate()?"true":"false") . "," .
		"daysLate:" . $milestone->getLateInDays() . "," .
		"duedate:" . $milestone->getDueDate()->getTimestamp();
	
	echo '{' . $milestoneInfo . '}';
} // foreach ?>
];

var container = document.getElementById('<?php echo $container_id?>');
for (var i=0; i < milestones.length; i++) {
	var m = milestones[i];
	m.container = container;
	new og.MilestoneItem(m);
}

var newMilestoneForm = og.MilestoneItem.createAddMilestoneForm({
	toggleText: lang('add new milestone'),
	container: container
});
container.appendChild(newMilestoneForm);
</script>
