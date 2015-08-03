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

<div class="adminMainBlock">
<table><tr>
		<th><?php echo lang('task') ?></th>
		<?php if(!$object instanceof TemplateTask){?>
			<th><?php echo lang('status') ?></th>
		<?php }?>
		<th><?php echo lang('actions') ?></th>
		<th></th>
	</tr>
<?php
	$row_cls = "dashAltRow";
	foreach ($previous_tasks as $pt) {
		$all_dep_completed = true;
		
		if($object instanceof ProjectTask){
			$ptask = ProjectTasks::findById($pt->getPreviousTaskId());
			$task_link = get_url('task','view',array('id'=>$ptask->getId()));
			
			$ptask_deps = ProjectTaskDependencies::getDependenciesForTask($ptask->getId());
			foreach ($ptask_deps as $pt_dep) {
				$pptask = ProjectTasks::findById($pt_dep->getPreviousTaskId());
				if (!$pptask->isCompleted()) {
					$all_dep_completed = false;
					break;
				}
			}
		}elseif ($object instanceof TemplateTask){
			$ptask = TemplateTasks::findById($pt->getPreviousTaskId());
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
		<td style="padding:2px 10px;">
			<a class="internalLink coViewAction ico-task" href="<?php echo $task_link ?>"><?php echo $ptask->getTitle() ?></a>
		</td>
		<?php if(!$object instanceof TemplateTask){?>
			<td style="padding:2px 10px;">
				<span class="desc <?php echo $status_cls ?>" style="padding:2px;">
					<?php echo $all_dep_completed ? ($ptask->isCompleted() ? lang('complete') : lang('pending')) : lang('task depends on incomplete tasks') ?>
				</span>
			</td>
		<?php }?>
		<td style="padding:2px 10px;"><?php
			if(!$ptask instanceof TemplateTask){
				if (!$ptask->isCompleted() && $ptask->canEdit(logged_user())) {
					if ($all_dep_completed) {
						echo '<a class="internalLink coViewAction ico-complete" href="'.$ptask->getCompleteUrl(rawurlencode($task_link)).'">'.lang('do complete').'</a>';
					} else {
						echo '<a class="internalLink coViewAction ico-expand" href="'.$task_link.'">'.lang('view').'</a>';
					}
				}
				if ($ptask->isCompleted() && $ptask->canEdit(logged_user())) {
					echo '<a class="internalLink coViewAction ico-reopen" href="'.$ptask->getOpenUrl(rawurlencode($task_link)).'">'.lang('open task').'</a>';
				}
			}
		?></td>
		<td><a class="internalLink coViewAction ico-delete" href="<?php echo get_url('taskdependency', 'remove', array('pt' => $ptask->getId(), 't' => $object->getId())) ?>">&nbsp;</a></td>
	</tr>
<?php } ?>
</table>
</div>

<?php } ?>

<a onclick="<?php echo $picker_func ?>" href="#" class="coViewAction ico-add"><?php echo lang('add previous task')?></a>

<div class="desc"><?php echo lang('this task has x previous open tasks', $incomplete_previous); ?></div>
