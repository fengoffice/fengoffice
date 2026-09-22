<?php 
	$previous_tasks = ProjectTaskDependencies::getDependenciesForTask($object->getId());
	$incomplete_previous = 0; 
	
	$picker_func = "og.pickPreviousTaskFromView(". $object->getId() .")";
	if($object instanceof TemplateTask){
		$picker_func = "og.pickPreviousTemplateTaskFromView(". $object->getId() .", ". $object->getTemplateId() .")";
	}
?>

<div class="commentsTitle"><?php echo lang('previous tasks') ?></div>
<?php if (is_array($previous_tasks) && count($previous_tasks) > 0) { ?>

<div class="adminMainBlock" style="padding-left:0;">
<table><tr>
		<th><?php echo lang('task') ?></th>
		<?php if(!$object instanceof TemplateTask){?>
			<th><?php echo lang('status') ?></th>
		<?php }?>
		<th colspan="2" class="center"><?php echo lang('actions') ?></th>
	</tr>
<?php
	$row_cls = "dashAltRow";
	foreach ($previous_tasks as $pt) {
		$all_dep_completed = true;
		
		if($object instanceof ProjectTask){
			$ptask = ProjectTasks::instance()->findById($pt->getPreviousTaskId());
			$task_link = get_url('task','view',array('id'=>$ptask->getId()));
			
			$ptask_deps = ProjectTaskDependencies::getDependenciesForTask($ptask->getId());
			foreach ($ptask_deps as $pt_dep) {
				$pptask = ProjectTasks::instance()->findById($pt_dep->getPreviousTaskId());
				if (!$pptask->isCompleted()) {
					$all_dep_completed = false;
					break;
				}
			}
		}elseif ($object instanceof TemplateTask){
			$ptask = TemplateTasks::instance()->findById($pt->getPreviousTaskId());
			$task_link = get_url('task','view',array('id'=>$ptask->getId(), 'template_task'=>1));
		}		
		if (!$ptask instanceof ProjectTask && !$ptask instanceof TemplateTask) {
			$pt->delete();
			continue;
		}
		$status_cls = $ptask->isCompleted() ? "og-wsname-color-24" : "og-wsname-color-18";
		$incomplete_previous += $ptask->isCompleted() ? 0 : 1;
				
		if (!$all_dep_completed) $status_cls = "og-wsname-color-19";
		$row_cls = $row_cls == "" ? "dashAltRow" : "";
?>
	<tr class="<?php echo $row_cls ?>">
		<td>
			<a class="internalLink ico-action" href="<?php echo $task_link ?>"><i class="icon-list-todo"></i> <?php echo $ptask->getTitle() ?></a>
		</td>
		<?php if(!$object instanceof TemplateTask){?>
			<td>
				<span class="desc <?php echo $status_cls ?>" style="padding:2px;">
					<?php echo $all_dep_completed ? ($ptask->isCompleted() ? lang('complete') : lang('pending')) : lang('task depends on incomplete tasks') ?>
				</span>
			</td>
		<?php }?>
		<td><?php
			if(!$ptask instanceof TemplateTask){
				if (!$ptask->isCompleted() && $ptask->canEdit(logged_user())) {
					if ($all_dep_completed) {
						echo '<a class="internalLink ico-action complete" href="'.$ptask->getCompleteUrl(rawurlencode($task_link)).'"><i class="icon-check"></i> '.lang('do complete').'</a>';
					} else {
						echo '<a class="internalLink ico-action" href="'.$task_link.'"><i class="icon-maximize-2"></i> '.lang('view').'</a>';
					}
				}
				if ($ptask->isCompleted() && $ptask->canEdit(logged_user())) {
					echo '<a class="internalLink coViewAction ico-reopen" href="'.$ptask->getOpenUrl(rawurlencode($task_link)).'">'.lang('open task').'</a>';
				}
			}
		?></td>
		<td><?php if ($object->canEdit(logged_user())) { ?>
			<a class="internalLink ico-action trash" href="<?php echo get_url('taskdependency', 'remove', array('pt' => $ptask->getId(), 't' => $object->getId())) ?>"><i class="icon-trash-2"></i></a>
		<?php } ?></td>
	</tr>
<?php } ?>
</table>
</div>

<?php } ?>

<?php if ($object->canEdit(logged_user())) { ?>
<div>
	<button class="btn btn-primary btn-sm" id="<?php echo $genid ?>_add_prev_task_link"><i class="icon-circle-plus"></i>&nbsp;<?php echo lang('add previous task') ?></button>
</div>
<div class="clear"></div>
<?php } ?>

<div class="desc"><?php echo lang('this task has x previous open tasks', $incomplete_previous); ?></div>
<script>
$(document).ready(function() {
	$("#<?php echo $genid ?>_add_prev_task_link").click(function() {
		<?php echo $picker_func ?>
	});
});
</script>