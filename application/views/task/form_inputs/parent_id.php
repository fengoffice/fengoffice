<?php
/** 
 * Parent Task Selection Input
 * 
 * This view renders the input for selecting a parent task. It handles both
 * regular project tasks and template tasks by checking the request parameters.
 * 
 * @var ProjectTask|TemplateTask $object The task object being created or edited
 * @var string $genid Unique identifier for form element scoping
 */

$parentContainerId = 'parent-task-container-' . $genid;

// Check if we are dealing with a task inside a template definition
$is_template_task = array_var($_REQUEST, 'template_task', false);
$pick_parent_task_onclick = "og.pickParentTask(this);";
$template_id = null;
$template_task = null;
$parent_task_id = $object->getParentId();

// For template tasks, we use specialized selection logic and managers
if ($is_template_task) {
	$template_id = array_var($_REQUEST, 'template_id', 0);
	$template_task = TemplateTasks::instance()->findById(get_id());
	if (!$template_task instanceof TemplateTask) {
		$template_task = TemplateTasks::instance()->findById(array_var($_REQUEST, 'template_task_id', 0));
	}
	$template_task_id = $template_task instanceof TemplateTask ? $template_task->getId() : '';
	
	// Override onclick handler to use template-specific picker
	$pick_parent_task_onclick = "og.pickParentTemplateTask(this, '" . $genid . "', '" . $template_task_id . "', '" . $template_id ."');";
	
	if ($template_task instanceof TemplateTask) {
		$parent_task_id = $template_task->getParentId();
	}
}

?>
<div id="<?php echo $parentContainerId ?>">
<?php
// Case 1: No parent task is currently set
if ($parent_task_id == 0) {
?>
	<span id="no-task-selected<?php echo $genid ?>"></span>
	<a class="link" tabindex="999" id="<?php echo $genid ?>parent_before" href="#" onclick="<?php echo $pick_parent_task_onclick ?> return false;">
		<i class="icon-circle-plus"></i> <?php echo lang('set parent task') ?>
	</a>
<?php
// Case 2: A parent task is set
} else {
	// Retrieve the parent task object using the appropriate manager
	if ($is_template_task) {
		$parentTask = TemplateTasks::instance()->findById($parent_task_id);
	} else {
		$parentTask = ProjectTasks::instance()->findById($parent_task_id);
	}

	// Render the selected parent task as a removable badge
	if ($parentTask instanceof ProjectTask || $parentTask instanceof TemplateTask) {
		?>
		<span style="display: none;" id="no-task-selected<?php echo $genid ?>"></span>
		<a tabindex="999" style="display: none;" id="<?php echo $genid ?>parent_before" href="#" onclick="<?php echo $pick_parent_task_onclick ?> return false;">
			<i class="icon-circle-plus"></i> <?php echo lang('set parent task') ?>
		</a>
		<div class="selected-object-wrapper og-add-template-object" id="parent-task-div-<?php echo $genid ?>">
			<input type="hidden" name="task[parent_id]" value="<?php echo $parentTask->getId() ?>" />
			<div class="object-badge">
				<i class="icon-list-todo"></i>
				<span class="name"><?php echo clean($parentTask->getTitle()) ?></span>
				<a tabindex="999" href="#" onclick="og.removeParentTask(this.parentNode.parentNode, '<?php echo $genid ?>'); return false;" class="object-remove-btn" title="<?php echo lang('remove') ?>"><i class="icon-circle-x"></i></a>
			</div>
		</div>
		<?php
	}
}
?>
</div>
