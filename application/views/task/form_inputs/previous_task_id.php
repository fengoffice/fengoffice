<?php
/**
 * Previous Task Selection Input
 *
 * This view renders the input for selecting previous tasks (task dependencies). It handles both
 * regular project tasks and template tasks by checking the request parameters.
 *
 * @var ProjectTask|TemplateTask $object The task object being created or edited
 * @var string $genid Unique identifier for form element scoping
 */

if (config_option('use tasks dependencies')) {

	$task = $object;

	// Check if we are dealing with a task inside a template definition
	$is_template_task = array_var($_REQUEST, 'template_task', false);
	$task_id = $task->isNew() ? 0 : $task->getId();
	$pick_previous_task_onclick = "og.pickPreviousTask(this, '" . $genid . "', '" . $task_id . "')";

	// For template tasks, we use specialized selection logic and managers.
	// The object passed to this view is a temporary ProjectTask copy of the template task that is always "new", so resolve the real template task here
	if ($is_template_task) {
		$template_id = array_var($_REQUEST, 'template_id', 0);
		$template_task = TemplateTasks::instance()->findById(get_id());
		if (!$template_task instanceof TemplateTask) {
			$template_task = TemplateTasks::instance()->findById(array_var($_REQUEST, 'template_task_id', 0));
		}
		$task_id = $template_task instanceof TemplateTask ? $template_task->getId() : 0;

		// Override onclick handler to use template-specific picker
		$pick_previous_task_onclick = "og.pickPreviousTemplateTask(this, '" . $genid . "', '" . $task_id . "', '" . $template_id . "')";
	}

	$previous_tasks = array();
	if ($task_id > 0) {
		$previous_tasks = ProjectTaskDependencies::instance()->findAll(array('conditions' => 'task_id = ' . $task_id));
	}
	?>
	<div class="dataBlock">
		<div>
			<div>
				<?php if (count($previous_tasks) == 0) { ?>
					<span id="<?php echo $genid ?>no_previous_selected"></span>
					<script>
						if (!og.previousTasks) og.previousTasks = [];
						og.previousTasksIdx = og.previousTasks.length;
					</script>
				<?php } else {
					$k = 0; ?>
					<script>
						og.previousTasks = [];
						og.previousTasksIdx = '<?php echo count($previous_tasks) ?>';
					</script>
					<input type="hidden" name="task[clean_dep]" value="1" />
					<?php
					foreach ($previous_tasks as $task_dep) {
						// Retrieve the previous task object using the appropriate manager
						if ($is_template_task) {
							$task_prev = TemplateTasks::instance()->findById($task_dep->getPreviousTaskId());
						} else {
							$task_prev = ProjectTasks::instance()->findById($task_dep->getPreviousTaskId());
						}
						// Skip dependencies whose previous task does not resolve (e.g. orphan rows)
						if (!$task_prev instanceof ProjectTask && !$task_prev instanceof TemplateTask) {
							continue;
						}
					?>
						<div class="object-badge">
							<input type="hidden" name="task[previous]['<?php echo $k ?>']" value="<?php echo $task_prev->getId() ?>" />
							<i class="icon-list-todo"></i>
							<span id="<?php echo $genid?>task_name" class="name"><?php echo clean($task_prev->getTitle()) ?></span>

							<a id="<?php echo $genid?>remove_task"
							href="#"
							onclick="og.removePreviousTask(this.parentNode, '<?php echo $genid ?>', '<?php echo $k ?>')"
							class="object-remove-btn"
							title="<?php echo lang('remove') ?>">
								<i class="icon-circle-x"></i>
							</a>
						</div>
						<script>
							var obj = {
								id: '<?php echo $task_dep->getPreviousTaskId() ?>'
							};
							og.previousTasks[og.previousTasks.length] = obj;
						</script>
						<div class="clear"></div>
				<?php $k++;
					}
				} ?>
			</div>
			<a class="link" id="<?php echo $genid ?>previous_before" href="#" onclick="<?php echo $pick_previous_task_onclick ?>"><i class="icon-circle-plus"></i> <?php echo lang('add previous task') ?></a>
		</div>
	</div>
	<div class="clear"></div>
<?php } ?>
