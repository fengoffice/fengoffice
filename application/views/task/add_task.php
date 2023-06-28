<?php
	require_javascript('og/modules/addTaskForm.js');
	require_javascript("og/ObjectPicker.js");
	
	if (!$task instanceof ProjectTask && !$task instanceof TemplateTask) {
		$task = new ProjectTask();
	}

	$task_status=$task->getColumnValue('invoicing_status');
    if($task_status=='invoiced')
    {
    	$task_input_disabled="disabled";
    }

	$object = $task;
	$genid = gen_id();
	
	if ($task->isNew()) {
		$params = array("copyId" => array_var($task_data, 'copyId'));
		if (isset($modal) && $modal) {
			$params['ajax'] = 1;
		}
		$form_url = get_url('task', 'add_task', $params);
	} else {
		$form_url = $task->getEditListUrl();
	}
	
	// on submit functions
	if (array_var($_REQUEST, 'modal')) {
		if (array_var($_REQUEST, 'from_email') > 0) {
			$callback_fn = "og.reloadCurrentPanel";
		} else {
			$callback_fn = "ogTasks.drawTaskRowAfterEdit";
		}
		$on_submit = "og.setDescription(); og.checkRepeatOptionEntries(); og.checkPercentCompleted(); og.submit_modal_form('".$genid."submit-edit-form', $callback_fn); return false;";
	} else {
		$on_submit = "return App.modules.addTaskForm.checkSubmitAddTask('".$genid."','". $task->manager()->getObjectTypeId()."') && og.setDescription() && og.checkRepeatOptionEntries() && og.checkPercentCompleted() ". 
		((array_var($task_data, 'multi_assignment') && Plugins::instance()->isActivePlugin('crpm')) ? "&& typeof('og.TaskMultiAssignment')=='function' ? og.TaskMultiAssignment() : true" : "").";";
	}
    
	$co_type = array_var($task_data, 'object_subtype');
	
	if (config_option('use tasks dependencies')) {
		require_javascript('og/tasks/task_dependencies.js');
	}
	
	$cp_count = CustomProperties::countAllCustomPropertiesByObjectType($object->getObjectTypeId());
	$cp_count_others = CustomProperties::countHiddenCustomPropertiesByObjectType($object->getObjectTypeId());
	$has_custom_properties = $cp_count > 0;
	
	$categories = array(); Hook::fire('object_edit_categories', $task, $categories);
	
    $loc = user_config_option('localization');
	if (strlen($loc) > 2) $loc = substr($loc, 0, 2);
?>
<script>
og.genid = '<?php echo $genid?>';
og.config.multi_assignment = '<?php echo config_option('multi_assignment') && Plugins::instance()->isActivePlugin('crpm') ? '1' : '0' ?>';
</script>
<style>
.task .coInputMainBlock .dimension-selector-container label {
	margin-right: 10px;
	min-width: 0px;
}
.task .custom-properties label {
	max-width: 190px;
	min-width: 190px;
}
</style>
<form id="<?php echo $genid ?>submit-edit-form" class="add-task" action="<?php echo $form_url ?>" method="post" onsubmit="<?php echo $on_submit?>">

<div class="task">
<div class="coInputHeader">

  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><?php
		if ($task->isNew()) {
			if (array_var($task_data, 'is_template', false)) {
				echo lang('new task template');
			} else if (isset($base_task) && $base_task instanceof ProjectTask) {
				echo lang('new task from template');
			} else {
				echo $object->getAddEditFormTitle();
			}
		} else {
			echo $object->getAddEditFormTitle();
		}
		echo ": ";
		//$ignored = null; Hook::fire("object_name_prefix", array('object' => $task), $ignored);
	?></div>
  </div>

  <div>
	<div class="coInputName">
	<?php
		$task_name = array_var($task_data, 'name', $task->getName());
		Hook::fire("render_object_name_prefix", array('object' => $task), $task_name);
		
		echo text_field('task[name]', $task_name, array('class' => 'title', 'id' => 'ogTasksPanelATTitle', "size"=>"255", "maxlength"=>"255", 'placeholder' => lang('task')));
	?>
	</div>
		
	<div class="coInputButtons">
		<?php echo submit_button($task->isNew() ? (array_var($task_data, 'is_template', false) ? lang('save template') : $object->getSubmitButtonFormTitle()) : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?>
	</div>
	<div class="clear"></div>
  </div>

</div>

<div class="coInputMainBlock">
	<input id="<?php echo $genid?>updated-on-hidden" type="hidden" name="updatedon" value="<?php echo $task->isNew() ? '': $task->getUpdatedOn()->getTimestamp() ?>">
	<input id="<?php echo $genid?>merge-changes-hidden" type="hidden" name="merge-changes" value="" >
	<input id="<?php echo $genid?>genid" type="hidden" name="genid" value="<?php echo $genid ?>" >
	<input id="<?php echo $genid?>view_related" type="hidden" name="view_related" value="<?php echo (isset($task_related) && $can_manage_repetitive_properties_of_tasks == 1 ? $task_related : "")?>" />
	<input id="<?php echo $genid?>type_related" type="hidden" name="type_related" value="only" />
	<input id="<?php echo $genid?>multi_assignment_aplly_change" type="hidden" name="task[multi_assignment_aplly_change]" value="" />
	<input id="<?php echo $genid?>view_add" type="hidden" name="view_add" value="true" />
	<input id="<?php echo $genid?>control_dates" type="hidden" name="control_dates" value="false" />
	
	
	<input id="<?php echo $genid?>task_id" type="hidden" name="task_id" value="<?php echo $task->isNew() ? '0': $task->getId() ?>" />
	
	<div id="<?php echo $genid?>tabs" class="edit-form-tabs">
	
		<ul id="<?php echo $genid?>tab_titles">
			
			<li><a href="#<?php echo $genid?>add_task_basic_div"><?php echo lang('basic data') ?></a></li>
			<li><a href="#<?php echo $genid?>add_task_desc_div"><?php echo lang('description') ?></a></li>
			<li><a href="#<?php echo $genid?>add_task_more_details_div"><?php echo lang('more details') ?></a></li>
			
			<?php if ($cp_count_others > 0 || config_option('use_object_properties') ) { ?>
			<li><a href="#<?php echo $genid?>add_custom_properties_div"><?php echo lang('custom properties') ?></a></li>
			<?php } ?>
			
			<li><a href="#<?php echo $genid?>add_subscribers_div"><?php echo lang('object subscribers') ?></a></li>
			
			<?php foreach ($categories as $category) {
					if (array_var($category, 'hidden')) continue;
				?>
			<li><a href="#<?php echo $genid . $category['name'] ?>"><?php echo $category['name'] ?></a></li>
			<?php } ?>
		</ul>
		
	
	<div id="<?php echo $genid ?>add_task_basic_div" class="task-data form-tab">
	<table>
		<tr>
			<td class="left-section-td">
				<div class="left-section">
				
					<div class="dataBlock">
						<!-- needs a table because ext dropdown list is not aligned otherwise -->
						<table><tr><td>
							<label><?php echo lang('assign to') ?>:</label> 
						</td><td>
							<input type="hidden" id="<?php echo $genid ?>taskFormAssignedTo" name="task[assigned_to_contact_id]" value="<?php echo array_var($task_data, 'assigned_to_contact_id')?>"></input>
							<div id="<?php echo $genid ?>assignto_container_div"></div>
						</td></tr></table>
						<div class="clear"></div>
					</div>
					
					<?php 
						$can_notify_assigned = user_config_option('can notify from quick add');
						$is_assigned = array_var($task_data, 'assigned_to_contact_id') != 0;
						$assigned_to_me = array_var($task_data, 'assigned_to_contact_id') == logged_user()->getId(); 			
						$show_notif_checkbox_div = $can_notify_assigned && $task->isNew() && $is_assigned && !$assigned_to_me;
						$check_notif_checkbox = $show_notif_checkbox_div;
						
					?>
					
					<div id="<?php echo $genid ?>taskFormSendNotificationDiv" style="display:<?php echo ($show_notif_checkbox_div ? 'block' : 'none') ?>" class="dataBlock">
						<label for="<?php echo $genid ?>taskFormSendNotification" class="checkbox" title="<?php echo lang('send task assigned to notification') ?>"><?php echo lang('notify assigned user')?></label>
						
						<?php echo checkbox_field('task[send_notification]', $check_notif_checkbox, array('id' => $genid . 'taskFormSendNotification', 'title' => lang('send task assigned to notification'))) ?>
						
						<input type="hidden" name="original_assigned_user" id="<?php echo $genid?>originalAssignedUser" value="<?php echo array_var($task_data, 'assigned_to_contact_id')?>" />
						<div class="clear"></div>
					</div>
					
					<div class="dataBlock">
						<?php echo label_tag(lang('start date')) ?>
						
						<?php 
							$sd_listeners = array("change" => "function(){ og.init_rep_by_selectbox('".$genid."'); }");
						?>
					
						<div style="float:left;"><?php echo pick_date_widget2('task_start_date', array_var($task_data, 'start_date'), $genid, 60, true, $genid.'start_date', $sd_listeners) ?></div>
						<?php if (config_option('use_time_in_task_dates')) { ?>
						<div style="float:left;margin-left:10px;"><?php echo pick_time_widget2('task_start_time', $task->getUseStartTime() ? array_var($task_data, 'start_date') : user_config_option('work_day_start_time'), $genid, 65, null, $genid.'start_date_time') ?></div>
						<?php } ?>
					
						<div class="clear"></div>
					</div>
					<div class="dataBlock">	
						<?php echo label_tag(lang('due date')) ?>
						
						<?php 
							$dd_listeners = array("change" => "function(){ og.init_rep_by_selectbox('".$genid."'); }");
						?>
						
						<div style="float:left;"><?php echo pick_date_widget2('task_due_date', array_var($task_data, 'due_date'), $genid, 70, true, $genid.'due_date', $dd_listeners); ?></div>
						<?php if (config_option('use_time_in_task_dates')) { ?>
						<div style="float:left;margin-left:10px;"><?php echo pick_time_widget2('task_due_time', $task->getUseDueTime() ? array_var($task_data, 'due_date') : user_config_option('work_day_end_time'), $genid, 75, null, $genid.'due_date_time'); ?></div>
						<?php } ?>
						<div class="clear"></div>
					
					</div>
					<?php 
						if (!$task->isNew() && $task->getTimezoneId() != logged_user()->getUserTimezoneId()) {
					?><div class="dataBlock" style="margin-bottom:8px;"><?php
							echo timezone_selector_hidden($task, $genid);
					?><div class='clear'></div></div><?php
						}
					?>
					<div class="clear"></div>
					<?php 
					
						$prevent_adding_estimated_time_to_parent = false;					
						Hook::fire('get_prevent_adding_time_to_parent', array('time_type' => 'estimated', 'is_parent' => $task->isParent()), $prevent_adding_estimated_time_to_parent);						
						
						if(config_option('use_task_estimated_time') && !$prevent_adding_estimated_time_to_parent){?>
						<div class="dataBlock" id='<?php echo $genid ?>add_task_time_div'>
						<?php
							echo label_tag(lang('estimated time'));
							$totalTime = array_var($task_data, 'time_estimate', 0);
							$minutes = $totalTime % 60;
							$hours = ($totalTime - $minutes) / 60;
						?>
							<?php echo lang("hours") ?>:&nbsp;
							<?php echo text_field("task[time_estimate_hours]", $hours, array('id' => 'ogTasksPanelATHours', 'style' => 'width:30px')) ?>
							<span style="margin-left:10px"><?php echo lang("minutes") ?>:&nbsp;</span>
							<select name="task[time_estimate_minutes]" size="1" id="ogTasksPanelATMinutes">
							<?php
								$minutes = ($totalTime % 60);
								$minuteOptions = array(0,5,10,15,20,25,30,35,40,45,50,55);
								Hook::fire('override_minute_options', array('name' => 'minuteOptions'), $minuteOptions);
								
								// if the task has an amount of minutes that is not present in the minuteOptions then add it
								// this can happen when estimated time is calculated using some formula in a template
								if (!in_array($minutes, $minuteOptions)) {
									$minuteOptions[] = $minutes;
									sort($minuteOptions, SORT_NUMERIC);
								}

								$options_count = count($minuteOptions);
								for($i = 0; $i < $options_count; $i++) {
									echo "<option value=\"" . $minuteOptions[$i] . "\"";
									if($minutes == $minuteOptions[$i]) echo ' selected="selected"';
									echo ">" . $minuteOptions[$i] . "</option>\n";
								}
							?></select>
						</div>
					<?php } ?>
					
					
					<div class="dataBlock">
					<?php echo label_tag(lang('task priority')) ?>
					<?php echo select_task_priority('task[priority]', array_var($task_data, 'priority', ProjectTasks::PRIORITY_NORMAL)) ?>
						<div class="clear"></div>
					</div>
					<?php if(config_option('use_task_percent_completed')){ ?>
						<div class="dataBlock">
							<?php echo label_tag('Manual percent completed'); ?>
							<?php echo checkbox_field('task[is_manual_percent_completed]', array_var($task_data, 'is_manual_percent_completed', false), array('id' => $genid . '_is_manual_percent_completed', 'onchange' => 'og.updateIsManualPercentCompleted();')); ?>
						</div>
						<?php 
							$show_percent_completed = array_var($task_data, 'is_manual_percent_completed', false) ? '' : 'display:none;';
						?>
						<div class="dataBlock" style="<?php echo $show_percent_completed; ?>" id="<?php echo $genid; ?>_percent_completed_container">
							<?php echo label_tag(lang('percent completed')) ?>
							<?php echo input_field('task[percent_completed]', array_var($task_data, 'percent_completed', 0), array('id' => $genid.'_task_percent_completed','class' => 'short', ''.$task_input_disabled.''=>$task_input_disabled)) ?> 
								<div class="clear"></div>
						</div>
					<?php } ?>
					<?php $null = null; Hook::fire('before_render_main_custom_properties', array('object' => $object, 'task_data' => $task_data, 'genid' => $genid), $null);?>
					
					
					<div class="bottom-section">
						<?php if ($has_custom_properties || config_option('use_object_properties')) { ?>
						<div>
							<div id="<?php echo $genid ?>not_required_custom_properties_container">
								<div id="<?php echo $genid ?>not_required_custom_properties" class="main-custom-properties-div">
								<?php
								if ($cp_count <= 10) {
									echo render_object_custom_properties($task, false, null, 'visible_by_default');
								} ?>
								</div>
							</div>
						<?php echo render_add_custom_properties($task); ?>
						</div>
						<?php } ?>
					</div>
				</div>
  			</td>
			<td class="right-section-td">
			<div class="right-section">
				<div id="<?php echo $genid ?>add_task_select_context_div" class="context-selector-container">
				<?php
					$listeners = array('on_selection_change' => 'og.reload_task_form_selectors()');
					if ($task->isNew()) {
						render_member_selectors($task->manager()->getObjectTypeId(), $genid, array_var($task_data, 'selected_members_ids', null), array('select_current_context' => true, 'listeners' => $listeners, 'object' => $object), null, null, false);
					} else {
						render_member_selectors($task->manager()->getObjectTypeId(), $genid, array_var($task_data, 'selected_members_ids', $task->getMemberIds()), array('listeners' => $listeners, 'object' => $object), null, null, false);
					}
				?>
				<div class="clear"></div>
				</div>
				
				<?php if (config_option('use_milestones')) : ?>
				<div class="dataBlock">
					<label><?php echo lang('milestone') ?>:</label>
					<div style="float:left;" id="<?php $genid ?>add_task_more_div_milestone_combo" >
						<?php  echo select_milestone('task[milestone_id]', null, array_var($task_data, 'milestone_id'), array('id' => $genid . 'taskListFormMilestone')) ?>    		
					</div>
					<div class="clear"></div>
					
				<?php 	if (!$task->isNew() && $task->countAllSubTasks() > 0) { ?>
					<label></label>
					<?php echo checkbox_field('task[apply_milestone_subtasks]', array_var($task_data, 'apply_milestone_subtasks', false), array("id" => "$genid-checkapplymi", "style" => "margin-top: 4px;")) ?>
					<label class="checkbox" for="<?php echo "$genid-checkapplymi" ?>" style="font-weight:normal;"><?php echo lang('apply milestone to subtasks') ?></label>
					<div class="clear"></div>
				<?php 	} ?>
				</div>
				<?php else : ?>
					<input type="hidden" value="<?php echo array_var($task_data, 'milestone_id', '0')?>" name="task[milestone_id]" id="<?php echo $genid;?>taskListFormMilestone"/>
					<input type="hidden" value="<?php echo array_var($task_data, 'apply_milestone_subtasks', '0')?>" name="task[apply_milestone_subtasks]" id="<?php echo $genid;?>-checkapplymi"/>
				<?php endif; ?>
				
				
				<div class="dataBlock">
				
					<?php echo label_tag(lang('parent task'), $genid . 'addTaskTaskList') ?>
					<?php if (isset($task_data['parent_id'])&& $task_data['parent_id'] == 0) {?>
														
						<span id="no-task-selected<?php echo $genid?>"><?php echo lang('none')?></span>
						<a style="margin-left: 10px" id="<?php echo $genid ?>parent_before" href="#" onclick="og.pickParentTask(this)"><?php echo lang('set parent task') ?></a>
						
					<?php }else{
						$parentTask = ProjectTasks::findById(array_var($task_data, 'parent_id'));
						if ($parentTask instanceof ProjectTask){?>
						<span style="display: none;" id="no-task-selected<?php echo $genid?>"><?php echo lang('none')?></span>
						<a style="display: none;margin-left: 10px" id="<?php echo $genid ?>parent_before" href="#" onclick="og.pickParentTask(this)"><?php echo lang('set parent task') ?></a> 
						<div class="og-add-template-object">
							<input type="hidden" name="task[parent_id]" value="<?php echo $parentTask->getId() ?>" />
							<div class="parent-task-name action-ico ico-task"> <?php echo $parentTask->getTitle() ?> </div>
							<a style="float:left" href="#" onclick="og.removeParentTask(this.parentNode)" class="remove" style="display: block;"><?php echo lang('remove')?> </a> 
						</div>
					<?php }
						}?>
						<div class="clear"></div>
				</div>

				<?php if (config_option('use tasks dependencies')) { ?>
				<div class="dataBlock">
				<?php echo label_tag(lang('previous tasks')) ?><br />
				<?php 	
					if (!$task->isNew())
						$previous_tasks = ProjectTaskDependencies::findAll(array('conditions' => 'task_id = '.$task->getId()));
					else $previous_tasks = array();
				?>
					<div>
						<div>
					<?php if (count($previous_tasks) == 0) { ?>
						<span id="<?php echo $genid?>no_previous_selected"><?php echo lang('none') ?></span>
						<script>if (!og.previousTasks) og.previousTasks = []; og.previousTasksIdx = og.previousTasks.length;</script>
					<?php } else {
						$k=0; ?>
						<script>
							og.previousTasks=[];
							og.previousTasksIdx = '<?php echo count($previous_tasks)?>';
						</script>
						<input type="hidden" name="task[clean_dep]" value="1" />
						<?php 
							foreach ($previous_tasks as $task_dep) {
								$task_prev = ProjectTasks::findById($task_dep->getPreviousTaskId());
						?>
							<div class="og-add-template-object previous-task">
								<input type="hidden" name="task[previous]['<?php echo $k?>']" value="<?php echo $task_prev->getId()?>" />
								<div class="previous-task-name action-ico ico-task"><?php echo clean($task_prev->getTitle()) ?></div>
								<a href="#" onclick="og.removePreviousTask(this.parentNode, '<?php echo $genid?>', '<?php echo $k?>')" class="removeDiv link-ico ico-delete" style="display: block;"><?php echo lang('remove') ?></a>
							</div>
							<script>
								var obj={id:'<?php echo $task_dep->getPreviousTaskId() ?>'};
								og.previousTasks[og.previousTasks.length] = obj;
							</script>
							<div class="clear"></div>
						<?php $k++;
							}
						} ?>
						</div>
						<a class="coViewAction ico-add" id="<?php echo $genid?>previous_before" href="#"  
							onclick="og.pickPreviousTask(this, '<?php echo $genid?>', '<?php echo $task->getId()?>')"><?php echo lang('add previous task') ?></a>
						
					</div>
				
				</div>
				<div class="clear"></div>
				<?php } ?>
				
			</div>
  			</td>
		</tr>
	</table>
  	<div class="clear"></div>

  	<?php Hook::fire('draw_additional_task_html', $genid, $task); ?>
  	
  	</div>

  	<div id="<?php echo $genid ?>add_task_desc_div" class="task-data form-tab">
  	
  		<?php 
  		if(config_option("wysiwyg_tasks")){
			if(array_var($task_data, 'type_content') == "text"){
				$ckEditorContent = purify_html(nl2br(array_var($task_data, 'text')));
			}else{
				$ckEditorContent = purify_html(nl2br(array_var($task_data, 'text')));
			}
		?>
		
		<div id="<?php echo $genid ?>ckcontainer" style="height: 100%" class="dataBlock">
			<?php //echo label_tag(lang('description'), $genid . 'taskListFormDescription');?>
			<textarea cols="80" id="<?php echo $genid ?>ckeditor" name="task[text]" rows="100"><?php echo clean($ckEditorContent) ?></textarea>
		</div>
		<script>
			var h = document.getElementById("<?php echo $genid ?>ckcontainer").offsetHeight;
			if (h > 370) {
				h = 350;
				$("#<?php echo $genid ?>ckcontainer").css('height', (h+20)+'px');
			}
			
			var editor = CKEDITOR.replace('<?php echo $genid ?>ckeditor', {
				height: '300px',
				allowedContent: true,
				enterMode: CKEDITOR.ENTER_BR,
				shiftEnterMode: CKEDITOR.ENTER_BR,
				disableNativeSpellChecker: false,
				language: '<?php echo $loc ?>',
				customConfig: '',
				contentsCss: ['<?php echo get_javascript_url('ckeditor/contents.css').'?rev='.product_version_revision();?>', '<?php echo get_stylesheet_url('og/ckeditor_override.css').'?rev='.product_version_revision();?>'],
				toolbarCanCollapse: false,
				toolbar: [
							['Font','FontSize','-','Bold','Italic','Underline', 'Strike', '-',
							'Blockquote', 'SpellChecker', 'Scayt','-',
							'TextColor','BGColor','RemoveFormat','-',
							'Link','Unlink','-',
							'NumberedList','BulletedList','-',
							'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']
						],
				on: {
					instanceReady: function(ev) {
						editor.resetDirty();
					}
				},
				fillEmptyBlocks: false,
				removePlugins: 'scayt,liststyle,magicline,contextmenu,tabletools',
				entities_additional : '#39,#336,#337,#368,#369'
			});

			og.setDescription = function() {
				var form = Ext.getDom('<?php echo $genid ?>submit-edit-form');
				if (form.preventDoubleSubmit) return false;

				setTimeout(function() {
					form.preventDoubleSubmit = false;
				}, 2000);

				var editor = og.getCkEditorInstance('<?php echo $genid ?>ckeditor');
				form['task[text]'].value = editor.getData();

				return true;
			};
		</script>
	<?php } else { ?>
		<div>
	<?php
			if(array_var($task_data, 'type_content') == "text"){
				$content_text = array_var($task_data, 'text');
			}else{
				$content_text = html_to_text(html_entity_decode(nl2br(array_var($task_data, 'text')), null, "UTF-8"));
			}
			
			echo label_tag(lang('description'), $genid . 'taskListFormDescription');
			echo textarea_field('task[text]', $content_text, array('class' => 'huge', 'id' => $genid . 'taskListFormDescription'));
	?>
		</div>
		<script>
			og.setDescription = function() {
				return true;
			};
		</script>
	<?php }?>
	</div>

  	<div id="<?php echo $genid ?>add_task_more_details_div" class="task-data form-tab">
		
		<div class="reminders-div sub-section-div" style="border-top:0px none;">
			<h2><?php echo lang('object reminders')?></h2>
			<div id="<?php echo $genid ?>add_reminders_content">
					<?php 
					$render_defaults = true;
					if ($task->isNew()) {
						$render_defaults = user_config_option("add_task_default_reminder");
					}
					?>
					<?php echo render_add_reminders($task, 'due_date',null,null,"task",$render_defaults); ?>
			</div>
		</div>
		
		<?php if ( $can_manage_repetitive_properties_of_tasks == 1 ){?>
		<div class="repeat-options-div sub-section-div">
			<h2><?php echo lang('repeating task')?></h2>
			<div id="<?php echo $genid ?>task_repeat_options_div">
			<?php
				if(!$task->isCompleted()){
					$occ = array_var($task_data, 'occ');
					$rsel1 = array_var($task_data, 'rsel1', true);
					$rsel2 = array_var($task_data, 'rsel2', '');
					$rsel3 = array_var($task_data, 'rsel3', '');
					$rnum = array_var($task_data, 'rnum', '');
					$rend = array_var($task_data, 'rend', '');
					// calculate what is visible given the repeating options
					$hide = '';
					if((!isset($occ)) OR $occ == 1 OR $occ=="") $hide = "display: none;";
			?>
			  <table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td align="left" valign="top" style="padding-bottom:6px">
						<table><tr><td><span style="padding-top:2px;"><?php echo lang('CAL_REPEAT')?></span> 
							<select name="task[occurance]" onChange="og.changeTaskRepeat()">
								<option value="1" id="<?php echo $genid ?>today"<?php if(isset($occ) && $occ == 1) echo ' selected="selected"'?>><?php echo lang('CAL_ONLY_TODAY')?></option>
								<option value="2" id="<?php echo $genid ?>daily"<?php if(isset($occ) && $occ == 2) echo ' selected="selected"'?>><?php echo lang('CAL_DAILY_EVENT')?></option>
								<option value="3" id="<?php echo $genid ?>weekly"<?php if(isset($occ) && $occ == 3) echo ' selected="selected"'?>><?php echo lang('CAL_WEEKLY_EVENT')?></option>
								<option value="4" id="<?php echo $genid ?>monthly"<?php if(isset($occ) && $occ == 4) echo ' selected="selected"'?>><?php echo lang('CAL_MONTHLY_EVENT') ?></option>
								<option value="5" id="<?php echo $genid ?>yearly"<?php if(isset($occ) && $occ == 5) echo  ' selected="selected"'?>><?php echo lang('CAL_YEARLY_EVENT') ?></option>
							</select>
						</td></tr></table>
					</td>
				</tr>
				<tr>
					<td>
						<div id="<?php echo $genid ?>repeat_options" style="align: center; text-align: left; <?php echo $hide ?>">
							<div>
								<?php echo lang('CAL_EVERY') . " " .text_field('task[occurance_jump]', array_var($task_data, 'rjump', '1'), array('size' => '2', 'id' => $genid.'occ_jump', 'maxlength' => '100', 'style'=>'width:25px')) ?>
								<span id="<?php echo $genid ?>word"></span>
							</div>
							<script type="text/javascript">
								og.selectRepeatMode = function(mode) {
									var id = '';
									if (mode == 1) id = 'repeat_opt_forever';
									else if (mode == 2) id = 'repeat_opt_times';
									else if (mode == 3) id = 'repeat_opt_until';
									if (id != '') {
										el = document.getElementById('<?php echo $genid ?>'+id);
										if (el) el.checked = true;
									} 
								}
	
								og.viewDays = function(view) {
									var btn = Ext.get('<?php echo $genid ?>repeat_days');
									if(view){
										if (btn) btn.dom.style.display = 'block';
									}else{
										if (btn) btn.dom.style.display = 'none';
									}
								}
							</script>
							<table>
								<tr><td colspan="2" style="vertical-align:middle; height: 22px;">
									<?php echo radio_field('task[repeat_option]', $rsel1, array('id' => $genid.'repeat_opt_forever','value' => '1', 'style' => 'vertical-align:middle', 'onclick' => 'og.viewDays(true)')) ."&nbsp;". lang('CAL_REPEAT_FOREVER')?>
								</td></tr>
								<tr><td colspan="2" style="vertical-align:middle">
									<?php echo radio_field('task[repeat_option]', $rsel2, array('id' => $genid.'repeat_opt_times','value' => '2', 'style' => 'vertical-align:middle', 'onclick' => 'og.viewDays(true)')) ."&nbsp;". lang('CAL_REPEAT');
									echo "&nbsp;" . text_field('task[repeat_num]', $rnum, array('size' => '3', 'id' => $genid.'repeat_num', 'maxlength' => '3', 'style'=>'width:25px', 'onfocus' => 'og.selectRepeatMode(2);')) ."&nbsp;". lang('CAL_TIMES') ?>
								</td></tr>
								<tr><td style="vertical-align:middle"><?php echo radio_field('task[repeat_option]', $rsel3,array('id' => $genid.'repeat_opt_until','value' => '3', 'style' => 'vertical-align:middle', 'onclick' => 'og.viewDays(true)')) ."&nbsp;". lang('CAL_REPEAT_UNTIL');?></td>
									<td style="padding-left:8px;">
									<?php 
										$listeners = array('focus' => "function(){ og.selectRepeatMode(3); }");
										echo pick_date_widget2(
												'task[repeat_end]', 
												$rend, 
												$genid, 
												99, 
												true, 
												null,
												$listeners); 
									?>
								</td></tr>
							</table>
							<script type="text/javascript">
								var els = document.getElementsByName('task[repeat_end]');
								for (i=0; i<els.length; i++) {
									els[i].onchange = function() {
										og.selectRepeatMode(3);
									}
								}
							</script>
							<div style="padding-top: 4px;">
								<?php echo lang('repeat by') . ' ' ?>
								<select name="task[repeat_by]" id="<?php echo $genid?>_rep_by">
									<option value="start_date" id="<?php echo $genid ?>rep_by_start_date"<?php if (array_var($task_data, 'repeat_by') == 'start_date') echo ' selected="selected"'?>><?php echo lang('field ProjectTasks start_date')?></option>
									<option value="due_date" id="<?php echo $genid ?>rep_by_due_date"<?php if (array_var($task_data, 'repeat_by') == 'due_date') echo ' selected="selected"'?>><?php echo lang('field ProjectTasks due_date')?></option>
								</select>
								<span id="<?php echo $genid?>_rep_by_warning" class="form-message error" style="display:none;"><?php echo lang('repeat by date warning')?></span>
							</div>
						</div>
					</td>
				</tr>
	
				<tr id="<?php echo $genid ?>repeat_days" style="display: none;">
					<td>
					<table>
						<?php if (!Plugins::instance()->isActivePlugin('crpm')) { ?>
						<tr><td><input class="checkbox" type="checkbox" value="1" name="task[working_days]" /> <?php echo lang('repeat working days')?></td></tr>
						<?php } ?>
						<?php
							$html = "";
							Hook::fire('form_repeat_by_more_checkboxes', array('object' => $object), $html);
							if ($html) echo $html;
						?>
					</table>
					<div style="padding-top: 4px;">
						<select name="task[move_direction_non_working_days]" id="<?php echo $genid?>_move_direction">
							<option value="advance" id="<?php echo $genid ?>move_direction_advance" <?php if (array_var($task_data, 'move_direction_non_working_days') == 'advance') echo ' selected="selected"'?>><?php echo lang('advance') ?></option>
							<option value="move_back" id="<?php echo $genid ?>move_direction_backward"<?php if (array_var($task_data, 'move_direction_non_working_days') == 'move_back') echo ' selected="selected"'?>><?php echo lang('move backwards') ?></option>
						</select>
						<?php echo lang('by one day until a working day is found') ?>
					</div>
					</td>
				</tr>
			  </table>
			<?php }else{ 
				echo lang('option repetitive task completed');
			}?>
			</div>
		</div>
		<?php } ?>
		
		<?php if (isset($from_email) && $from_email instanceof MailContent) { ?>
			<input type="hidden" name="task[from_email]" value="<?php echo $from_email->getId()?>"/>
		<?php } ?>
		
		<?php if($task->isNew() || $task->canLinkObject(logged_user())) { ?>
		<div class="linked-objects-div sub-section-div">
			<h2><?php echo lang('linked objects')?></h2>
			<div id="<?php echo $genid ?>add_linked_objects_div">
			<?php
				$pre_linked_objects = null;
				if (isset($from_email) && $from_email instanceof MailContent) {
					$pre_linked_objects = array($from_email);
					$attachments = $from_email->getLinkedObjects();
					foreach ($attachments as $att) {
						if ($att instanceof ProjectFile) {
							$pre_linked_objects[] = $att;
						}
					}
				}
				echo render_object_link_form($task, $pre_linked_objects)
			
			?>
			</div>
		</div>
		<?php } ?>
		
		<?php if (!config_option('multi_assignment')) { ?>
		<div class="subtasks-div sub-section-div">
			<h2><?php echo lang('subtasks')?></h2>
			<div id="<?php echo $genid ?>add_task_subtasks_div">
  	
				<div class="dataBlock">
					<?php echo checkbox_field('task[apply_assignee_subtasks]', false, array('id' => $genid . 'taskFormApplyAssignee')) ?>
					<label for="<?php echo $genid ?>taskFormApplyAssignee" class="checkbox" style="font-weight:normal;margin-right:5px;"><?php echo lang('apply assignee to subtasks') ?></label>
					<div class="clear"></div>
				</div>
				
		  		<div id="<?php echo $genid ?>subtasks" class="subtasks-container">
		  		</div>
		  		<div class="add-subtask-container">
		  			<a href="#" class="link-ico ico-add" onclick="ogTasks.drawAddSubTaskInputs('<?php echo $genid?>')"><?php echo lang('add sub task')?></a>
		  			<a href="#" class="link-ico ico-undo" onclick="ogTasks.undoRemoveSubtasks('<?php echo $genid?>')" style="display:none;margin-left:20px;" id="<?php echo $genid?>undo_remove"><?php echo lang('undo remove subtasks')?></a>
		  		</div>
	  		
	  		</div>
		</div>
		<?php } ?>
  	</div>
  	
  		
  
		<?php if ($cp_count_others > 0 || config_option('use_object_properties') ) { ?>
		<div id="<?php echo $genid ?>add_custom_properties_div" class="form-tab other-custom-properties-div">
			<div id="<?php echo $genid ?>not_required_custom_properties_container">
		    	<div id="<?php echo $genid ?>not_required_custom_properties">
		      	<?php echo render_object_custom_properties($task, false, null, 'others') ?>
		      	</div>
		    </div>
	      <?php echo render_add_custom_properties($task); ?>
	 	</div>
	 	<?php } ?>
  
	    <div id="<?php echo $genid ?>add_subscribers_div" class="form-tab">
	    
		    
			<div id="<?php echo $genid ?>taskFormSendNotificationSubscribersDiv" style="display:<?php echo (array_var($task_data, 'display_notification_checkbox')) ? 'block' : 'none' ?>" class="dataBlock">
				<?php echo checkbox_field('task[send_notification_subscribers]', array_var($task_data, 'send_notification_subscribers'), array('id' => $genid . 'taskFormSendNotificationSubscribers', 'style' => 'margin-left:5px;margin-top:3px;')) ?>
				<label for="<?php echo $genid ?>taskFormSendNotificationSubscribers" class="checkbox"><?php echo lang('send task subscribers notification') ?></label>
				<div class="clear"></div>
			</div>
			
			<?php $subscriber_ids = array();
				if (!$task->isNew()) {
					$subscriber_ids = $task->getSubscriberIds();
				} else {
					$subscriber_ids[] = logged_user()->getId();
				}
			?><input type="hidden" id="<?php echo $genid ?>subscribers_ids_hidden" value="<?php echo implode(',',$subscriber_ids)?>"/>
			<input type="hidden" id="<?php echo $genid ?>original_subscribers" value="<?php echo implode(',',$subscriber_ids)?>"/>
			<div id="<?php echo $genid ?>add_subscribers_content"><?php
				foreach ($subscriber_ids as $subid) {
					echo '<input type="hidden" name="subscribers[user_'.$subid.']" value="1"/>';
				} 
			?></div>
		</div>
	
	
	<?php foreach ($categories as $category) { ?>
		<div id="<?php echo $genid . $category['name'] ?>" class="form-tab">
			<?php echo $category['content'] ?>
		</div>
	<?php } ?>
	
	
   	</div>
	

	
	
	
	<?php echo input_field("task[is_template]", array_var($task_data, 'is_template', false), array("type" => "hidden")); ?>
	<?php
	if (!array_var($_REQUEST, 'modal')) { 
		echo submit_button($task->isNew() ? (array_var($task_data, 'is_template', false) ? lang('save template') : lang('add task list')) : lang('save changes'));
	} else {
		if (array_var($_REQUEST, 'reload')) echo input_field('reload', array_var($_REQUEST, 'reload'), array('type' => 'hidden'));
		if (array_var($_REQUEST, 'use_ajx')) echo input_field('use_ajx', array_var($_REQUEST, 'use_ajx'), array('type' => 'hidden'));
	}
	?>
</div>
</div>
</form>

<script>
	if (!ogTasks.usersStore) ogTasks.usersStore = {};
	og.add_task_genid = '<?php echo $genid ?>';

	var is_new_task = <?php echo $task->isNew() ? '1' : '0'?>;
	var original_assigned_user = '<?php echo array_var($task_data, 'assigned_to_contact_id', 0) ?>';
	var can_notify_assigned = <?php echo $can_notify_assigned ? '1' : '0'?>;
	var start = true;
	if (!is_new_task) {
		var current_dimension_members_json = Ext.util.JSON.encode(member_selector['<?php echo $genid ?>'].sel_context);
	} else {
		var current_dimension_members_json = og.contextManager.plainContext();
	}

	var can_manage_repetitive_properties = <?php echo $can_manage_repetitive_properties_of_tasks == 1 ? 'true' : 'false'; ?>;
	
	
	og.drawAssignedToSelectBox = function(companies, only_me, groups) {
		ogTasks.usersStore['<?php echo $genid ?>'] = ogTasks.buildAssignedToComboStore(companies, only_me, groups);
		var assignCombo = new Ext.form.ComboBox({
			renderTo:'<?php echo $genid ?>assignto_container_div',
			name: 'taskFormAssignedToCombo',
			id: '<?php echo $genid ?>taskFormAssignedToCombo',
			value: original_assigned_user,
			store: ogTasks.usersStore['<?php echo $genid ?>'],
			displayField:'text',
	        mode: 'local',
	        cls: 'assigned-to-combo',
	        triggerAction: 'all',
	        selectOnFocus:true,
	        width: 244,
	        listWidth: 244,
	        listClass: 'assigned-to-combo-list',
	        valueField: 'value',
	        emptyText: (lang('select user or group') + '...'),
	        valueNotFoundText: ''
		});
		assignCombo.on('select', og.onAssignToComboSelect);

		assignedto = document.getElementById('<?php echo $genid ?>taskFormAssignedTo');
		
	}
	
	og.onAssignToComboSelect = function(combo, selected, idx) {
		// ensure that no html tags are inside the input
		var plain_text = og.removeTags(selected.data.text);
		$('#<?php echo $genid ?>taskFormAssignedToCombo').val(plain_text);
		
		// reload other components
		combo = Ext.getCmp('<?php echo $genid ?>taskFormAssignedToCombo');
		assignedto = document.getElementById('<?php echo $genid ?>taskFormAssignedTo');
		if (assignedto) assignedto.value = combo.getValue();
		assigned_user = combo.getValue();
		
		og.enableDisableNotifyAssignedCheckbox(assigned_user, '<?php echo $genid ?>');

		ogTasks.applyAssignedToSubtasksInTaskForm('<?php echo $genid?>');

		var dimension_members_json = Ext.util.JSON.encode(member_selector['<?php echo $genid ?>'].sel_context);
		og.render_tasks_form_subscribers(dimension_members_json);

		// more processing...
		if (og.after_assigned_to_change_fn) {
			for (var x=0; x<og.after_assigned_to_change_fn.length; x++) {
				var fn = og.after_assigned_to_change_fn[x];
				if (typeof(fn) == 'function') {
					fn.call(null, '<?php echo $genid ?>', combo, selected);
				}
			}
		}
		
	}

	og.enableDisableNotifyAssignedCheckbox = function(assigned_user, genid) {

		var original_assigned = $("#" + genid + "originalAssignedUser").val();
		
		var show_div = can_notify_assigned && assigned_user > 0 && assigned_user != og.loggedUser.id;

		var check = show_div && (assigned_user != original_assigned || is_new_task);
		
		if (show_div) {
			$("#" + genid + "taskFormSendNotificationDiv").removeClass('desc').slideDown();
		} else {
			$("#" + genid + "taskFormSendNotificationDiv").addClass('desc').slideUp();
		}

		if (check) {
			$("#" + genid + "taskFormSendNotification").attr('checked', 'checked');
		} else {
			$("#" + genid + "taskFormSendNotification").removeAttr('checked');
		}
		
	}

	og.redrawUserLists = function(context){
		if (!og.redrawingUserList) {
			og.redrawingUserList = true ;
			var prev_value = 0;
			var combo = Ext.getCmp('<?php echo $genid ?>taskFormAssignedToCombo');
			if (combo) {
				combo.collapse();
				combo.disable();
				prev_value = combo.getValue();
			}
			
			parameters = context ? {context: context} : {};
			if (og.task_redraw_users_extra_params) parameters.extra_params = og.task_redraw_users_extra_params;
			
			og.openLink(og.getUrl('task', 'allowed_users_to_assign', parameters), {callback: function(success, data){
				only_me = data.only_me ? data.only_me : null;
				if (combo) {
					combo.reset();
					combo.store.removeAll();
					combo.store.loadData(ogTasks.buildAssignedToComboStore(data.companies, only_me, data.groups));
					let usrControlFound=0;
					for(let i=0;i<data.companies.length;i++)
					{
					    if(data.companies[i]['users'].find(contactAux=>contactAux.id == prev_value) || (prev_value==-1 || prev_value==0))
					    { 
						    combo.setValue(prev_value);
							usrControlFound=1;
							break;
					    }
					}
					if(prev_value!=0 && usrControlFound==0)
					{
     					combo.setValue(0);
					    $('#<?php echo $genid ?>taskFormAssignedTo').val(0);
					    og.err(lang("the user assign to this task doesn't have permission over the selected project. The task is now unassigned"));
					}
					combo.enable();
				} else {
					og.drawAssignedToSelectBox(data.companies, only_me, data.groups);
				}
				ogTasks.usersStore['<?php echo $genid?>'] = ogTasks.buildAssignedToComboStore(data.companies, only_me, data.groups);
				// update subtasks assigned_to selector
				var count = $(".subtask-inputs-container.<?php echo $genid?>").length;
				for (var i=0; i<count; i++) {
					var com = Ext.getCmp('<?php echo $genid?>subtask_assigned_to_' + i);
					if (com) {
						var previous_value = com.getValue();
						com.reset();
						com.store.removeAll();
						com.store.loadData(ogTasks.usersStore['<?php echo $genid?>']);
						com.setValue(previous_value);
						com.enable();
					}
				}
				og.redrawingUserList = false;
				og.eventManager.fireEvent('after usersStore init', '<?php echo $genid?>');

				// ensure that no html tags are inside the input
				var plain_text = og.removeTags($('#<?php echo $genid ?>taskFormAssignedToCombo').val());
				$('#<?php echo $genid ?>taskFormAssignedToCombo').val(plain_text);
			}});
			setTimeout(function() { 
				og.redrawingUserList = false;
			}, 1500);
		}		
	}
	
	og.changeTaskRepeat = function() {
		var ro = document.getElementById("<?php echo $genid ?>repeat_options");
		
		if (ro){ 
			ro.style.display = 'none';

			var word = '';
			var opt_display = '';
			if(document.getElementById("<?php echo $genid ?>daily").selected){
				word = '<?php echo escape_single_quotes(lang("days"))?>';
			} else if(document.getElementById("<?php echo $genid ?>weekly").selected){
				word = '<?php echo escape_single_quotes(lang("weeks"))?>';
			} else if(document.getElementById("<?php echo $genid ?>monthly").selected){
				word = '<?php echo escape_single_quotes(lang("months"))?>';
			} else if(document.getElementById("<?php echo $genid ?>yearly").selected){
				word = '<?php echo escape_single_quotes(lang("years"))?>';
			} else opt_display = 'none';
			
			document.getElementById("<?php echo $genid ?>word").innerHTML = word;
			if (ro) ro.style.display = opt_display;

			// if no option selected => select repeat forever
			if (!$("#<?php echo $genid ?>repeat_opt_forever").attr('checked') && !$("#<?php echo $genid ?>repeat_opt_times").attr('checked') 
					&& !$("#<?php echo $genid ?>repeat_opt_until").attr('checked')) {
				$("#<?php echo $genid ?>repeat_opt_forever").attr('checked', 'checked');
			}

			if(document.getElementById("<?php echo $genid ?>today").selected){
				og.viewDays(false);
			}else{
				og.viewDays(true);
			}
		}
		
	}
	og.init_rep_by_selectbox('<?php echo $genid; ?>');


	og.checkRepeatOptionEntries = function() {
		if(can_manage_repetitive_properties){
			var repeatForever = document.getElementById("<?php echo $genid ?>repeat_opt_forever");
			var repeatNum = document.getElementById("<?php echo $genid ?>repeat_opt_times");
			var repeatUntil = document.getElementById("<?php echo $genid ?>repeat_opt_until");
			
			if(repeatNum === null || repeatUntil === null){
				return;
			}

			if(repeatNum.checked){
				var repeatUntilInput = document.getElementById("<?php echo $genid ?>task[repeat_end]Cmp");
				repeatUntilInput.value = null;
			} else if(repeatUntil.checked){
				var repeatNumInput = document.getElementById("<?php echo $genid ?>repeat_num");
				repeatNumInput.value = '';
			}
		}
	}

	og.checkPercentCompleted = function() {
		var percent_completed = document.getElementById("<?php echo $genid ?>_task_percent_completed");
		if(percent_completed){
			if(percent_completed.value > 100){
				percent_completed.value = 100;
			} else if(percent_completed.value < 0){
				percent_completed.value = 0;
			}
		}
	}

	og.reload_task_form_selectors = function(is_new, render_add_subscribers) {
		render_add_subscribers = (typeof render_add_subscribers == "undefined") ? true : render_add_subscribers;
		if (!is_new) {
			var dimension_members_json = Ext.util.JSON.encode(member_selector['<?php echo $genid ?>'].sel_context);
		} else {
			var dimension_members_json = og.contextManager.plainContext();
		}
		var milestone_el = document.getElementById('<?php echo $genid ?>taskListFormMilestone');
		var actual_value = milestone_el ? milestone_el.value : 0;
		var milestone_div = Ext.get('<?php $genid ?>add_task_more_div_milestone_combo');
		if (milestone_div) {
			milestone_div.load({
				url: og.getUrl('milestone', 'render_add_milestone', {
					context: dimension_members_json,
					genid: '<?php echo $genid ?>',
					selected: actual_value
				}),
				scripts: true
			});
		}
	
		if(render_add_subscribers){
			og.render_tasks_form_subscribers(dimension_members_json);
		}
		
		og.redrawUserLists(dimension_members_json);

		// Change billable if hour_types and and advanced billing plugins are activated
		var hour_type_active = <?php echo Plugins::instance()->isActivePlugin('hour_types') ? '1' : '0'; ?>;
		var advanced_billing_active = <?php echo Plugins::instance()->isActivePlugin('advanced_billing') ? '1' : '0'; ?>;
		if(hour_type_active && advanced_billing_active){
			og.setIsBillable(dimension_members_json);
		} 
		// Update current selected member
		current_dimension_members_json = dimension_members_json;
	}

	og.setIsBillable = function(dimension_members_json){
		var current_billable = $('#<?php echo $genid ?>is_billableYes').attr('checked') == 'checked' ? 1 : 0;
		var member_params = {member_ids: dimension_members_json, current_member_ids: current_dimension_members_json, current_billable: current_billable};
		og.openLink(og.getUrl('billing_definition','get_labor_category_billable_for_task_form', member_params), {
			callback: function(success, data) {
				if(data.has_value){
					if(data.is_billable){
						if(current_billable != data.is_billable){
							if(confirm(lang('You are changing from a non-billable labor category to a billable one. This will set the \'Billable\' property for this task to \'Yes\''))){
								$('#<?php echo $genid ?>is_billableYes').attr('checked','checked');
							}
						}
					} else {
						if(current_billable != data.is_billable){
							if(confirm(lang('You are changing from a billable labor category to a non-billable one. This will set the \'Billable\' property for this task to \'No\''))){
								$('#<?php echo $genid ?>is_billableNo').attr('checked','checked');
							}
						}
					}
				}
			}
		});
	}

	og.render_tasks_form_subscribers = function(dimension_members_json) {
		var combo = Ext.getCmp('<?php echo $genid ?>taskFormAssignedToCombo');
		if (combo /*&& combo.getValue()!=''*/){
			assigned_to = combo.getValue();
		}
		else assigned_to = 0;

		var uids = App.modules.addMessageForm.getCheckedUsers('<?php echo $genid ?>');
		Ext.get('<?php echo $genid ?>add_subscribers_content').load({
			url: og.getUrl('object', 'render_add_subscribers', {
				context: dimension_members_json,
				users: uids,
				genid: '<?php echo $genid ?>',
				assigned_to: assigned_to,
				otype: '<?php echo $task->manager()->getObjectTypeId()?>'
			}),
			scripts: true
		});
	}
	
	

	Ext.extend(og.TaskPopUp, Ext.Window, {
		accept: function() {
			var opt = $("#<?php echo $genid?>type_related").val();
			if(opt == "pending"){
				var url = og.getUrl('task', 'edit_task', {id : <?php echo $pending_task_id ?>, replace : true});
				og.openLink(url, {method: 'POST', scope: this});
			}
			this.close();
		},
		listeners:{
            beforeclose:function(){
            	showRepeatOpt();
            }
        }    
	});

	//User combo
	og.drawAssignedToSelectBox([], false, []);
	if(<?php echo $task->isNew() ? '0' : '1'?>){
		// parse the mem path string
		var mempath = Ext.util.JSON.decode('<?php echo json_encode($task->getMembersIdsToDisplayPath()) ?>');

		var task_members_json = {};
		// iterate the mempath object, key = dimension_id, value = member ids grouped by member type id
		for (var dim_id in mempath) {
			task_members_json[dim_id] = [];
			// get the members grouped by type
			ots_data = mempath[dim_id];
			// foreach member type, proecess the members
			for (var ot_id in ots_data) {
				if (!isNaN(ot_id) && ots_data[ot_id] && ots_data[ot_id].length > 0) {
					// process the members of the current member tpye
					for (var x in ots_data[ot_id]) {
						// get the member id
						var m = ots_data[ot_id][x];
						// add the member id to the result
						task_members_json[dim_id].push(m);
					}
				}
			}
		}
		task_members_json = Ext.util.JSON.encode(task_members_json);
		
	}else{
		var task_members_json = og.contextManager.plainContext();
	}

	// more task form initializations
	if (og.more_add_task_form_init_fn) {
		for (var x=0; x<og.more_add_task_form_init_fn.length; x++) {
			var fn = og.more_add_task_form_init_fn[x];
			if (typeof(fn) == 'function') {
				fn.call(null, og.add_task_genid);
			}
		}
	}
	
		
	og.redrawUserLists(task_members_json);

	function selectRelated(val){
		$("#<?php echo $genid?>type_related").val(val);
	}

	function showRepeatOpt(){
		var val = $("#<?php echo $genid?>type_related").val();
		if(val == "only"){
			$("#<?php echo $genid?>task_repeat_options").hide();
		}else{
			$("#<?php echo $genid?>task_repeat_options").show();
		}
	}


	og.pickParentTask = function(before) {
		og.ObjectPicker.show(function (objs) {
			if (objs && objs.length > 0) {
				var obj = objs[0].data;
				if (obj.type != 'task') {
					og.msg(lang("error"), lang("object type not supported"), 4, "err");
				} else {
					og.addParentTask(this, obj);
				}
			}
		}, before, {
			types: ['task'],
			selected_type: 'task'
		});
	};

	og.addParentTask = function(before, obj) {
		var parent = before.parentNode;
		var count = parent.getElementsByTagName('input').length;
		var div = document.createElement('div');
		div.className = "og-add-template-object " + (count % 2 ? " odd" : "");
		div.innerHTML =
			'<input type="hidden" name="task[parent_id]" value="' + obj.object_id + '" />' +
			'<div class="parent-task-name action-ico ico-'+obj.type+'">' + og.clean(obj.name) + '</div>' +
			'<a href="#" onclick="og.removeParentTask(this.parentNode)" class="removeDiv link-ico ico-delete" style="display: block;">'+lang('remove')+'</div>';
		bef = document.getElementById('<?php echo $genid?>parent_before');
		label = document.getElementById('no-task-selected<?php echo $genid?>');
		if (label) label.style.display = 'none';
		if (bef) bef.style.display = 'none';
		if (parent) parent.insertBefore(div, before);
	};

	og.removeParentTask = function(div) {
		var parent = div.parentNode;
		if (parent) parent.removeChild(div);
		bef = document.getElementById('<?php echo $genid?>parent_before');
		label = document.getElementById('no-task-selected<?php echo $genid?>');
		if (bef) bef.style.display = 'inline';
		if (label) label.style.display = 'inline';
		
	};

	og.updateIsManualPercentCompleted = function () {
		var is_manual = $('#<?php echo $genid ?>_is_manual_percent_completed').attr('checked') == 'checked' ? 1 : 0;
		var percent_completed_container = $('#<?php echo $genid ?>_percent_completed_container');
		if (is_manual) {
			percent_completed_container.show();
		} else {
			percent_completed_container.hide();
		}
	};
		

	$(document).ready(function() {
		if($("#<?php echo $genid?>view_related").val()){
			<?php if($task->isCompleted()){ ?>
			this.dialog = new og.TaskPopUp('task_complete','');
			<?php }else{?>
			this.dialog = new og.TaskPopUp('','');
			<?php }?>
			this.dialog.setTitle(lang('tasks related'));
			this.dialog.show();
			selectRelated("news");
		}
		
		<?php if(!$task->isCompleted()){ ?>
			og.changeTaskRepeat();
		<?php }?>
		
		var listenerId = og.eventManager.addListener('after usersStore init',function(){		
			<?php
			if (!$task->isNew()) {
				$subtasks = ProjectTasks::findAll(array('conditions' => "parent_id=".$task->getId()." AND trashed_by_id=0"));
				foreach ($subtasks as $st) {
					$st_name = clean(escape_character($st->getObjectName()));
					$st_name = preg_replace('/\s+/', ' ', trim($st_name)); // remove enters
					?>
					ogTasks.drawAddSubTaskInputs('<?php echo $genid ?>', {id:'<?php echo $st->getId()?>', name:'<?php echo $st_name?>', assigned_to:'<?php echo $st->getAssignedToContactId()?>'});
			<?php
				}
			} else {
				/*if (isset($task_data['subtasks'])) {
					foreach ($task_data['subtasks'] as $st) {
						$st_name = clean(escape_character($st->getObjectName()));
						$st_name = preg_replace('/\s+/', ' ', trim($st_name)); // remove enters
					?>
						ogTasks.drawAddSubTaskInputs('<?php echo $genid ?>', {id:'<?php echo $st->getId()?>', name:'<?php echo $st_name?>', assigned_to:'<?php echo $st->getAssignedToContactId()?>'});
					<?php
					}
				} else {*/
			?>
					ogTasks.drawAddSubTaskInputs('<?php echo $genid ?>');
			<?php
				//}
			}
			?>
		
			og.eventManager.removeListener(listenerId) ;
		});	

		$('#<?php echo $genid?>taskFormApplyAssignee').change(function(event){
			ogTasks.applyAssignedToSubtasksInTaskForm('<?php echo $genid?>');
		});


		<?php if ($cp_count > 10) { ?>
		$('#<?php echo $genid ?>not_required_custom_properties').html('<div class="widget-body loading">'+lang('loading')+'</div>');
		var render_cps_params = {id:'<?php echo $task->getId()?>', ot_id: <?php echo $task->getObjectTypeId()?>, visibility:'visible_by_default'};
		if (og.more_params_for_render_cps_params) {
			for (var x=0; x<og.more_params_for_render_cps_params.length; x++) {
				var moreparams = og.more_params_for_render_cps_params[x].call(null, '<?php echo $task->getObjectTypeName()?>');
				for (k in moreparams) render_cps_params[k]=moreparams[k];
			}
		}
		og.openLink(og.getUrl('object','render_cps', render_cps_params), {
			callback: function(success, data) {
				$('#<?php echo $genid ?>not_required_custom_properties').html(data.html);
				$("#modal-forms-container").scrollTop(0);
			}
		});
		<?php } ?>
		

		$("#<?php echo $genid?>tabs").tabs();

		
		$("#ogTasksPanelATTitle").focus();
	});
	
</script>
