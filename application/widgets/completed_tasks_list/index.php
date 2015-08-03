<?php
$genid = gen_id();
$limit = 20;
$total = $limit;
$page = 10;

$task_assignment_conditions = "";
if (!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks')) {
	$task_assignment_conditions = " AND assigned_to_contact_id = ".logged_user()->getId();
}

$tasks_result = ProjectTasks::instance()->listing(array(
	"order" => "completed_on",
	"order_dir" => "DESC",
	"extra_conditions" => " AND is_template = 0 AND completed_by_id > 0 $task_assignment_conditions",
	"limit" => $limit + 1,
));

$tasks = $tasks_result->objects;

$active_members = array();
$context = active_context();
if (is_array($context)) {
	foreach ($context as $selection) {
		if ($selection instanceof Member) $active_members[] = $selection;
	}
}
if (count($active_members) > 0) {
	$mnames = array();
	$allowed_contact_ids = array();
	foreach ($active_members as $member) {
		$mnames[] = clean($member->getName());
	}
	$widget_title = lang('completed tasks'). ' '. lang('in').' '. implode(", ", $mnames);
}

if ($tasks_result->total > 0) {
	include 'template.php';
}