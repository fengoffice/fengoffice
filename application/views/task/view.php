<?php
require_javascript("og/modules/addTaskForm.js");

/* 
 * This section builds the actions menu 
 */
if (isset($task_list) && $task_list instanceof ProjectTask) {
	$tz_offset = Timezones::getTimezoneOffsetToApply($task_list);
	$tz_offset = $tz_offset/3600;
	
	if (!$task_list->isTrashed()){
		if (!$task_list->isCompleted() && ($task_list->canEdit(logged_user()) || $task_list->getAssignedTo() == logged_user())) {
			add_page_action(lang('do complete'), $task_list->getCompleteUrl(rawurlencode(get_url('task','view',array('id'=>$task_list->getId()))), 'task view - complete') , 'ico-complete', null, null, true);
			
			if($task_list->isMarkedAsStarted()){
			    add_page_action(lang('unmark as started this task'), $task_list->getChangeMarkStartedUrl(rawurlencode(get_url('task','view',array('id'=>$task_list->getId()))), 'task view - unmark as started') , 'ico-undo', null, null, true);			    
			}else{
			    add_page_action(lang('mark as started this task'), $task_list->getChangeMarkStartedUrl(rawurlencode(get_url('task','view',array('id'=>$task_list->getId()))), 'task view - mark as started') , 'ico-start', null, null, true);
			}
		} // if
		if ($task_list->isCompleted() && ($task_list->canEdit(logged_user()) || $task_list->getAssignedTo() == logged_user())) {
			add_page_action(lang('open task'), $task_list->getOpenUrl(rawurlencode(get_url('task','view',array('id'=>$task_list->getId()))), 'task view - reopen') , 'ico-reopen', null, null, true);
		} // if

		if($task_list->canEdit(logged_user())) {
			add_page_action(lang('edit'), "javascript:og.render_modal_form('', {c:'task', a:'edit_task', params: {id:".$task_list->getId().",reload:1, req_channel:'task view - edit task'}});", 'ico-edit', null, null, true);
			if (!$task_list->isArchived())
				add_page_action(lang('archive'), "javascript:if(confirm(lang('confirm archive object'))) og.openLink('" . $task_list->getArchiveUrl() ."');", 'ico-archive-obj');
			else
				add_page_action(lang('unarchive'), "javascript:if(confirm(lang('confirm unarchive object'))) og.openLink('" . $task_list->getUnarchiveUrl() ."');", 'ico-unarchive-obj');
		} // if
	}

	if ($task_list->canDelete(logged_user())) {
		if ($task_list->isTrashed()) {
			add_page_action(lang('restore from trash'), "javascript:if(confirm(lang('confirm restore objects'))) og.openLink('" . $task_list->getUntrashUrl() ."');", 'ico-restore', null, null, true);
			add_page_action(lang('delete permanently'), "javascript:if(confirm(lang('confirm delete permanently'))) og.openLink('" . $task_list->getDeletePermanentlyUrl() ."');", 'ico-delete', null, null, true);
		} else {
			add_page_action(lang('move to trash'), "javascript:if(confirm(lang('confirm move to trash'))) og.openLink('" . $task_list->getTrashUrl() ."');", 'ico-trash', null, null, true);
		} // if
	} // if

	if (!$task_list->isTrashed() && !logged_user()->isGuest()){
		
		$ret=null; Hook::fire('view_task_actions', $task_list, $ret);
		
		if ($task_list->isRepetitive()) {
		    if ($can_manage_repetitive_properties_of_tasks)
			 add_page_action(lang('generate repetitition'), get_url("task", "generate_new_repetitive_instance", array("id" => $task_list->getId(), 'req_channel'=>'task view - generate repetition')), 'ico-recurrent', null, null, true);
		} else {
			add_page_action(lang('copy task'), "javascript:og.render_modal_form('', {c:'task', a:'copy_task', params: {id:".$task_list->getId().", req_channel:'task view - copy task'}});", 'ico-copy', null, null, true);
		}
		if (can_manage_templates(logged_user())) {
			add_page_action(lang('add to a template'), get_url("template", "add_to", array("manager" => 'ProjectTasks', "id" => $task_list->getId())), 'ico-template');
		} // if
	} // if
	
	add_page_action(lang('print'), get_url('task', 'print_task', array("id" => $task_list->getId())), 'ico-print', '_blank');
	

	$null = null;
	Hook::fire("view_task_add_actions", array('task' => $task_list), $null);
	
	//FIXME Fix reorder subtasks
	/*if($task_list->canReorderTasks(logged_user()) && is_array($task_list->getOpenSubTasks())) {
	add_page_action(lang('reorder sub tasks'), $task_list->getReorderTasksUrl($on_list_page), 'ico-properties');
	} // if*/
	$this->assign('on_list_page', true);
	?>

<div style="padding: 7px">
<div class="tasks"><?php

/*
 * This section builds the task title
*/
$title = $task_list->getObjectName() != '' ? $task_list->getObjectName() : $task_list->getText();
$description = '';
$parentInf = '';
//
$task = ProjectTasks::instance()->findById(get_id());
 
//start
if (($task_list->getParent() instanceof ProjectTask)&& $task->canEdit(logged_user())) {
	$parent = $task_list->getParent();
	$parentInf = '<div class="member-path-dim-block"><b>'.lang('subtask of', $parent->getViewUrl(), $parent->getObjectName() != ''? clean($parent->getObjectName()) : clean($parent->getText()))." ".'</b></div>';
}
//end

$status = '<div class="taskStatus">';
if(!$task_list->isCompleted()) {
	if ($task_list->canEdit(logged_user()) && !$task_list->isTrashed())
	$status .= '<b>'.lang('status').': </b><a class=\'internalLink \' style="background-position:0 -501px !important;" href=\'' . $task_list->getCompleteUrl(rawurlencode(get_url('task','view',array('id'=>$task_list->getId()))), 'task view - complete from status link') . '\' title=\''
	.escape_single_quotes(lang('complete task')) . '\'>' . lang('pending') . '</a>';
	else
	$status .= '<div style="display:inline;"><b>'.lang('status').': </b>' . lang('pending') . '</div>';
}
else {
	$status .= lang('status').': ';
	if ($task_list->canEdit(logged_user()) && !$task_list->isTrashed())
	$status .= '<a class=\'internalLink og-ico ico-complete\' href=\'' . $task_list->getOpenUrl(rawurlencode(get_url('task','view',array('id'=>$task_list->getId()))), 'task view - reopen from status link') . '\' title=\''
	. escape_single_quotes(lang('open task')) . '\'>' . lang('complete') . '</a>';
	else
	$status .= '<div style="display:inline;" class="og-ico ico-complete">' . lang('complete') . '</div>';
}
$status.= '</div>';

if ($task_list->getAssignedTo()){
	$description .= '<span style="font-weight:bold">' . lang("assigned to") . ': </span><a class=\'internalLink\' style="color:white" href=\''
	. $task_list->getAssignedTo()->getCardUserUrl() . '\' title=\'' . escape_single_quotes(lang('user card of', clean($task_list->getAssignedToName()))). '\'>'
	. clean($task_list->getAssignedToName()) . '</a>';
	if ($task_list->getAssignedBy() instanceof Contact) {
		$description .= ' <span style="font-weight:bold">' . lang("by") . ': </span> <a class=\'internalLink\' style="color:white" href=\''
		. $task_list->getAssignedBy()->getCardUserUrl() . '\' title=\'' . escape_single_quotes(lang('user card of', clean($task_list->getAssignedBy()->getObjectName()))). '\'>'
		. clean($task_list->getAssignedBy()->getObjectName()) . '</a>';
		if ($task_list->getAssignedOn() instanceof DateTimeValue) {
			$description .= ' <span style="font-weight:bold">' . lang("on") . ': </span>'
			. format_date($task_list->getAssignedOn(), null, $tz_offset);
		}
	}
}

$milestone = '';
if ($task_list->getMilestone() instanceof ProjectMilestone){
	$m = $task_list->getMilestone();
	$milestone .= '<div class="member-path-dim-block"><div class="og-ico ico-milestone"><b>'.lang('milestones').': </b><a class=\'internalLink\' href=\''
	. $m->getViewUrl() . '\' title=\'' . escape_single_quotes(lang('view milestone') . '\'>' . clean($m->getObjectName())) . '</a></div>';
}

$priority = '';
if ($task_list->getPriority() >= ProjectTasks::PRIORITY_URGENT) {
	$priority = '<div class="og-task-priority-high"><span style="font-weight:bold">'.lang('task priority').": </span>".lang('urgent priority').'</div>';
}else if ($task_list->getPriority() >= ProjectTasks::PRIORITY_HIGH) {
	$priority = '<div class="og-task-priority-high"><span style="font-weight:bold">'.lang('task priority').": </span>".lang('high priority').'</div>';
} else if ($task_list->getPriority() <= ProjectTasks::PRIORITY_LOW) {
	$priority = '<div class="og-task-priority-low"><span style="font-weight:bold">'.lang('task priority').": </span>".lang('low priority').'</div>';
}

$variables = array();

tpl_assign("parentInf", $parentInf);
tpl_assign("milestone", $milestone);
tpl_assign("priority", $priority);
tpl_assign("status", $status);
tpl_assign("variables", $variables);
tpl_assign("content_template", array('task_list', 'task'));
tpl_assign('object', $task_list);
//tpl_assign('title', clean($title));
tpl_assign('iconclass', $task_list->isTrashed()? 'ico-large-tasks-trashed' : ($task_list->isArchived() ? 'ico-large-tasks-archived' : 'ico-large-tasks'));


$this->includeTemplate(get_template_path('view', 'co'));
?></div>
</div>
<?php } //if isset ?>

<script>
if (typeof(App.modules.addTaskForm.hideAllAddTaskForms) == 'function') App.modules.addTaskForm.hideAllAddTaskForms();
</script>