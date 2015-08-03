<?php
$not_overdue_limit = 5 ;
$overdue_limit = 20 ;

$show_more = false ;

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
	$widget_title = lang('late tasks and upcoming tasks'). ' '. lang('in').' '. implode(", ", $mnames);
}

$assigned_to_user = null;
$w_option_assigned_to = ContactWidgetOptions::instance()->getContactOption('overdue_upcoming', logged_user()->getId(), 'assigned_to_user');
if (array_var($w_option_assigned_to, 'value')) {
	$assigned_to_user = array_var($w_option_assigned_to, 'value');
}

// Not due tasks
$not_due_tasks = ProjectTasks::getUpcomingWithoutDate($not_overdue_limit+1, $assigned_to_user);
if ( count($not_due_tasks) > $not_overdue_limit ) {
	$show_more = true;
	array_pop($not_due_tasks);
}


// Due Tasks
$overdue_upcoming_objects = ProjectTasks::getOverdueAndUpcomingObjects ($overdue_limit+1, $assigned_to_user); // FIXME: performance Killer
if ( count($overdue_upcoming_objects) > $overdue_limit ) {
	$show_more = true;
	array_pop($overdue_upcoming_objects);
}

$overdue_upcoming_objects = array_merge($not_due_tasks, $overdue_upcoming_objects);
$users = array();

if (count($overdue_upcoming_objects) > 0) {
	// Render only when the context isnt 'all' and you have perms 
	$render_add = active_context_members(false) && ProjectTask::canAdd(logged_user(), active_context());
	
	if ($render_add) {
		$users[] = array(0, lang('dont assign'));	
		foreach ( allowed_users_to_assign() as $company ){
			foreach ($company['users'] as $user ) {
				$name  = logged_user()->getId() == $user['id'] ? lang('me') : $user['name'] ;
				$users[] = array($user['id'], $name);	
			}
		}
	}
	
	include_once 'template.php';
}