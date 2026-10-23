<?php
/**
 * Milestone Selection Input
 *
 * This view renders the milestone selector of a task when the form is built from property groups.
 * It mirrors the milestone block of the default task form: the combo container id is used by
 * og.reload_task_form_selectors() to reload the options when the task classification changes, and
 * the "apply to subtasks" checkbox is offered when editing a task that already has subtasks.
 *
 * @var ProjectTask $object The task object being created or edited
 * @var string $genid Unique identifier for form element scoping
 * @var int $value Currently selected milestone id
 * @var string $property_perm Permission over the property ('view' renders it disabled)
 */

if (config_option('use_milestones')) {
	$select_attributes = array('id' => $genid . 'taskListFormMilestone', 'tabindex' => '40');
	if ($property_perm == 'view') {
		$select_attributes['disabled'] = 'disabled';
	}
	// the edit action assigns task_data with the configured default for the checkbox
	$apply_to_subtasks = isset($task_data) ? array_var($task_data, 'apply_milestone_subtasks', false) : config_option('apply_milestone_subtasks');
?>
<div id="<?php echo $genid ?>add_task_more_div_milestone_combo">
	<?php echo select_milestone('task[milestone_id]', null, $value, $select_attributes) ?>
</div>
<div class="clear"></div>

<?php if (!$object->isNew() && $object->countAllSubTasks() > 0) { ?>
	<?php echo checkbox_field('task[apply_milestone_subtasks]', $apply_to_subtasks, array('id' => "$genid-checkapplymi", 'style' => 'margin-top: 4px;')) ?>
	<label class="checkbox" for="<?php echo "$genid-checkapplymi" ?>" style="font-weight:normal;"><?php echo lang('apply milestone to subtasks') ?></label>
	<div class="clear"></div>
<?php } ?>
<?php } ?>
