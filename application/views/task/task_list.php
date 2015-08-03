<?php
require_javascript("og/modules/addTaskForm.js");
$task_list = $object;
$genid = gen_id();
$description = "";
?>
<script>
  if(App.modules.addTaskForm) {
    App.modules.addTaskForm.task_lists[<?php echo $task_list->getId() ?>] = {
      id               : <?php echo $task_list->getId() ?>,
	  can_add_task     : <?php echo ($task_list->canAddSubTask(logged_user()) && !$task_list->isTrashed()) ? 'true' : 'false' ?>,
      add_task_link_id : 'addTaskForm<?php echo $task_list->getId() ?>ShowLink',
      task_form_id     : 'addTaskForm<?php echo $task_list->getId() ?>',
      text_id          : 'addTaskText<?php echo $task_list->getId() ?>',
      assign_to_id     : 'addTaskAssignTo<?php echo $task_list->getId() ?>',
      submit_id        : 'addTaskSubmit<?php echo $task_list->getId() ?>'
    };
  } // if
</script>

		
<?php if ($task_list->getObjectSubtype() > 0) {
		$subType = ProjectCoTypes::findById($task_list->getObjectSubtype());
		if ($subType instanceOf ProjectCoType ) {
			echo "<div><span class='bold'>" . lang('object type') . ":</span> " . $subType->getName() . "</div>";
		}
	  }
?>



<?php if($task_list->getText()) { ?>
  
	<div class="wysiwyg-description"><?php
		if($task_list->getTypeContent() == "text"){
			echo escape_html_whitespace(convert_to_links(clean($task_list->getText())));
		}else{
			echo convert_to_links(purify_html(nl2br($task_list->getText())));
		}
	?></div>
  
<?php } // if ?>

<?php
/**
 * This section displays the most important information of the task. 
 */
?>
<?php 
if($task_list->getAssignedTo()){
	if (logged_user()->getId() == $task_list->getAssignedTo()->getId()){
		$username = lang('me');
	}else{
		$username = clean($task_list->getAssignedToName());
	}
 	$description .= '<span style="font-weight:bold">' . lang("assigned to") . ': </span><a class=\'internalLink\' href=\''
	. $task_list->getAssignedTo()->getCardUserUrl() . '\' title=\'' . escape_single_quotes(lang('user card of', clean($task_list->getAssignedToName()))). '\'>'
	. $username . '</a>';
} 
 ?>
<div>
	<?php if ($description != "" || $priority != "") { ?>
	<div style="width:50%; float: left;">
		<div class="member-path-dim-block" style="font-weight:bold;"><?php echo $description;?></div>
		<div class="member-path-dim-block" style="font-weight:bold;"><?php if (isset($priority)){echo $priority;} ?></div>
	</div>
	<?php } ?>
	
	<?php if (($task_list->getDueDate() instanceof DateTimeValue) || ($task_list->getStartDate() instanceof DateTimeValue) || isset($status)) { ?>
	<div style="width:50%; float: left;">
	<?php if ($task_list->getDueDate() instanceof DateTimeValue) {
	 		if ($task_list->getDueDate()->getYear() > DateTimeValueLib::now()->getYear()) { ?> 
			<div class="member-path-dim-block dueDate"><span class="bold"><?php echo lang('due date') ?>: </span><?php echo format_datetime($task_list->getDueDate(), null, 0) ?></div>
	  <?php } else { ?> 
	 		<div class="member-path-dim-block dueDate"><span class="bold"><?php echo lang('due date') ?>: </span><?php
	 		
	 		$tm = null;
	 		if(!$task_list->getUseDueTime()){
	 			$tm = 0;
	 		}	 		
	 	  	echo format_descriptive_date($task_list->getDueDate(),$tm);
	 	  	if ($task_list->getUseDueTime()) {
	 	  		echo " ".lang('by time')." " . format_time($task_list->getDueDate(), user_config_option('time_format_use_24') ? 'G:i' : 'g:i A');
	 	  	}
	 	  	
	 	  ?></div>
	  <?php }
		} ?>
		
	<?php if ($task_list->getStartDate() instanceof DateTimeValue) {
	 		if ($task_list->getStartDate()->getYear() > DateTimeValueLib::now()->getYear()) { ?> 
			<div class="member-path-dim-block startDate"><span class="bold"><?php echo lang('start date') ?>: </span><?php echo format_datetime($task_list->getStartDate(), null, 0) ?></div>
	  <?php } else { ?> 
	 		<div class="member-path-dim-block startDate"><span class="bold"><?php echo lang('start date') ?>: </span><?php
	 		 
	 		$tm = null;
	 		if(!$task_list->getUseDueTime()){
	 			$tm = 0;
	 		}
	 	  	echo format_descriptive_date($task_list->getStartDate(),$tm);
	 	  	if ($task_list->getUseDueTime()) {
	 	  		echo " ".lang('by time')." " . format_time($task_list->getStartDate(), user_config_option('time_format_use_24') ? 'G:i' : 'g:i A');
	 	  	}
	 	  	
	 	  ?></div>
	  <?php }
		} ?>
		 
		 <div class="member-path-dim-block" style="font-weight:bold"><?php if (isset($status)){echo $status;} ?></div>
	</div>
	<?php } ?>
	
</div> 
<div class="clear"></div>





<?php if ($task_list->isRepetitive()) { ?>
	<div style="font-weight:bold">
	<?php 
		echo '<br>' . lang('this task repeats'). '&nbsp;';
		if ($task_list->getRepeatForever()) echo lang('forever');
		else if ($task_list->getRepeatNum()) echo lang('n times', $task_list->getRepeatNum());
		else if ($task_list->getRepeatEnd()) echo lang('until x', format_date($task_list->getRepeatEnd()));
		echo ", " . lang ('every') . " ";
		if ($task_list->getRepeatD() > 0) echo lang('n days', $task_list->getRepeatD()) . ".";
		else if ($task_list->getRepeatM() > 0) echo lang('n months', $task_list->getRepeatM()) . ".";
		else if ($task_list->getRepeatY() > 0) echo lang('n years', $task_list->getRepeatY()) . ".";
	?>
	</div>
<?php }
?>
