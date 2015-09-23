<?php
	require_javascript('og/modules/addTaskForm.js');
	require_javascript("og/ObjectPicker.js");
	
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
	
	if (!isset($additional_onsubmit)) $additional_onsubmit = "";
	
	// on submit functions
	if (array_var($_REQUEST, 'modal')) {
		$on_submit = "og.setDescription(); og.submit_modal_form('".$genid."submit-edit-form', og.redrawTemplateObjectsLists); return false;";
	} else {
		$on_submit = "return App.modules.addTaskForm.checkSubmitAddTask('".$genid."','". $task->manager()->getObjectTypeId()."') && og.setDescription()". ($additional_onsubmit != "" ? " && $additional_onsubmit" : "").
		((array_var($task_data, 'multi_assignment') && Plugins::instance()->isActivePlugin('crpm')) ? "&& typeof('og.TaskMultiAssignment')=='function' ? og.TaskMultiAssignment() : true" : "").";";
	}
    
	$co_type = array_var($task_data, 'object_subtype');
	
	if (config_option('use tasks dependencies')) {
		require_javascript('og/tasks/task_dependencies.js');
	}
	
	$has_custom_properties = CustomProperties::countAllCustomPropertiesByObjectType(ProjectTasks::instance()->getObjectTypeId()) > 0;
	
	$categories = array(); Hook::fire('object_edit_categories', $task, $categories);
	
    $loc = user_config_option('localization');
	if (strlen($loc) > 2) $loc = substr($loc, 0, 2);
	$projectTask = new ProjectTask();
?>
<script>
og.genid = '<?php echo $genid?>';
og.config.multi_assignment = '<?php echo config_option('multi_assignment') && Plugins::instance()->isActivePlugin('crpm') ? '1' : '0' ?>';
</script>
<style>
.coInputMainBlock .dimension-selector-container label {
	margin-right: 10px;
	min-width: 0px;
}
</style>
<form id="<?php echo $genid ?>submit-edit-form" class="add-task" action="<?php echo $form_url ?>" method="post" onsubmit="<?php echo $on_submit?>">

<div class="task">
<div class="coInputHeader">

  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><?php 
		if ($task->isNew()) {
			echo lang('new task');//'new task template'
		} else {
			echo lang('edit task');//'edit task template'
		}
	?></div>
  </div>

  <div>
	<div class="coInputName">
	<?php
		echo text_field('task[name]', array_var($task_data, 'name'), array('class' => 'title', 'id' => 'ogTasksPanelATTitle', "size"=>"255", "maxlength"=>"255", 'placeholder' => lang('task')));
	?>
	</div>
		
	<div class="coInputButtons">
		<?php echo submit_button($task->isNew() ? (array_var($task_data, 'is_template', false) ? lang('save template') : lang('add task list')) : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?>
	</div>
	<div class="clear"></div>
  </div>

</div>

<div class="coInputMainBlock">
	<input id="<?php echo $genid?>template_task" type="hidden" name="template_task" value="<?php echo array_var($_GET, 'template_task', false)?>" />
	<input id="<?php echo $genid?>updated-on-hidden" type="hidden" name="updatedon" value="<?php echo $task->isNew() ? '': $task->getUpdatedOn()->getTimestamp() ?>">
	<input id="<?php echo $genid?>merge-changes-hidden" type="hidden" name="merge-changes" value="" >
	<input id="<?php echo $genid?>genid" type="hidden" name="genid" value="<?php echo $genid ?>" >
	<input id="<?php echo $genid?>view_related" type="hidden" name="view_related" value="<?php echo (isset($task_related) ? $task_related : "")?>" />
	<input id="<?php echo $genid?>type_related" type="hidden" name="type_related" value="only" />
	<input id="<?php echo $genid?>multi_assignment_aplly_change" type="hidden" name="task[multi_assignment_aplly_change]" value="" />
	<input id="<?php echo $genid?>view_add" type="hidden" name="view_add" value="true" />
	<input id="<?php echo $genid?>control_dates" type="hidden" name="control_dates" value="false" />
	<input id="<?php echo $genid?>template_id" type="hidden" name="template_id" value="<?php echo $template_id ?>" />
	<input id="<?php echo $genid?>additional_tt_params" type="hidden" name="additional_tt_params" value="<?php echo str_replace('"', "'", $additional_tt_params)?>" />
        
	

	
	<div id="<?php echo $genid?>tabs" class="edit-form-tabs">
	
		<ul id="<?php echo $genid?>tab_titles">
			
			<li><a href="#<?php echo $genid?>add_task_basic_div"><?php echo lang('basic data') ?></a></li>
			<li><a href="#<?php echo $genid?>add_task_desc_div"><?php echo lang('description') ?></a></li>
			<li><a href="#<?php echo $genid?>add_task_more_details_div"><?php echo lang('more details') ?></a></li>
			
			
			<?php if (false && ($has_custom_properties || config_option('use_object_properties')) ) { ?>
			<li><a href="#<?php echo $genid?>add_custom_properties_div"><?php echo lang('custom properties') ?></a></li>
			<?php } ?>
			
			<li><a href="#<?php echo $genid?>add_subscribers_div"><?php echo lang('object subscribers') ?></a></li>
			
			<?php foreach ($categories as $category) { ?>
			<li><a href="#<?php echo $genid . $category['id'] ?>"><?php echo $category['name'] ?></a></li>
			<?php } ?>
		</ul>
		
	
	<div id="<?php echo $genid ?>add_task_basic_div" class="task-data form-tab">
	<div class="left-section">
	
		<div class="dataBlock">
			<?php $defaultNotifyValue = user_config_option('can notify from quick add'); ?>
			<!-- needs a table because ext dropdown list is not aligned otherwise -->
			<table><tr><td>
				<label><?php echo lang('assign to') ?>:</label> 
			</td><td>
				<input type="hidden" id="<?php echo $genid ?>taskFormAssignedTo" name="task[assigned_to_contact_id]" value="<?php echo array_var($task_data, 'assigned_to_contact_id')?>"></input>
				<div id="<?php echo $genid ?>assignto_container_div"></div>
			</td></tr></table>
			<div class="clear"></div>
		</div>
		
		<div class="dataBlock">
    		<?php echo label_tag(lang('start date')) ?>
    	
			<div style="float:left;"><?php echo pick_date_widget2('task_start_date', array_var($task_data, 'start_date'), $genid, 60, true, $genid.'start_date') ?></div>
			<?php if (config_option('use_time_in_task_dates')) { ?>
			<div style="float:left;margin-left:10px;"><?php echo pick_time_widget2('task_start_time', $task->getUseStartTime() ? array_var($task_data, 'start_date') : user_config_option('work_day_start_time'), $genid, 65, null, $genid.'start_date_time') ?></div>
			<?php } ?>
		
			<div class="clear"></div>
		</div>
		<div class="dataBlock">	
			<?php echo label_tag(lang('due date')) ?>
    	
    		<div style="float:left;"><?php echo pick_date_widget2('task_due_date', array_var($task_data, 'due_date'), $genid, 70, true, $genid.'due_date'); ?></div>
    		<?php if (config_option('use_time_in_task_dates')) { ?>
    		<div style="float:left;margin-left:10px;"><?php echo pick_time_widget2('task_due_time', $task->getUseDueTime() ? array_var($task_data, 'due_date') : user_config_option('work_day_end_time'), $genid, 75, null, $genid.'due_date_time'); ?></div>
    		<?php } ?>
    		<div class="clear"></div>
		
		</div>
		
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
				for($i = 0; $i < 12; $i++) {
					echo "<option value=\"" . $minuteOptions[$i] . "\"";
					if($minutes == $minuteOptions[$i]) echo ' selected="selected"';
					echo ">" . $minuteOptions[$i] . "</option>\n";
				}
			?></select>
		</div>
		
		
		
		<div class="dataBlock">
		<?php echo label_tag(lang('task priority')) ?>
		<?php echo select_task_priority('task[priority]', array_var($task_data, 'priority', ProjectTasks::PRIORITY_NORMAL)) ?>
			<div class="clear"></div>
		</div>
		
		<?php if(array_var($task_data, 'time_estimate') == 0){?>
		<div class="dataBlock">
		<?php echo label_tag(lang('percent completed')) ?>
		<?php echo input_field('task[percent_completed]', array_var($task_data, 'percent_completed', 0), array('class' => 'short')) ?>
			<div class="clear"></div>
		</div>
		<?php }?>
		
		
		
		<?php $task_types = ProjectCoTypes::getObjectTypesByManager('ProjectTasks');
			if (count($task_types) > 0) {?>
		<div class="dataBlock"><?php
				echo label_tag(lang('object type'));
				echo select_object_type('task[object_subtype]', $task_types, array_var($task_data, 'object_subtype', config_option('default task co type')), array('onchange' => "og.onChangeObjectCoType('$genid', '".$task->getObjectTypeId()."', ".($task->isNew() ? "0" : $task->getId()).", this.value)"));
		?></div><?php
			}
		?>
		
		
		<?php if ($has_custom_properties || config_option('use_object_properties')) { ?>
		<div id="<?php echo $genid ?>add_custom_properties_div">
			<div id="<?php echo $genid ?>not_required_custom_properties_container">
		    	<div id="<?php echo $genid ?>not_required_custom_properties">
		      	<?php echo render_object_custom_properties($task, false, $co_type) ?>
		      	</div>
		    </div>
	      <?php echo render_add_custom_properties($task); ?>
	 	</div>
	 	<?php } ?>
		
  	</div>
  	
  	<div class="right-section">
  		<div id="<?php echo $genid ?>add_task_select_context_div" class="context-selector-container">
		<?php
			$listeners = array('on_selection_change' => 'og.reload_task_form_selectors()');
			if ($task->isNew()) {
				render_member_selectors($projectTask->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'listeners' => $listeners), null, null, false);
			} else {
				render_member_selectors($projectTask->getObjectTypeId(), $genid, array_var($task_data, 'selected_members_ids', $task->getMemberIds()), array('listeners' => $listeners), null, null, false);
			}
		?>
		</div>
		
		<?php if (config_option('use_milestones')) : ?>
	    <div class="dataBlock">
			<label><?php echo lang('milestone') ?>:</label>
		    <div style="float:left;" id="<?php $genid ?>add_task_more_div_milestone_combo" >
	    		<?php  echo select_milestone('task[milestone_id]', null, array_var($task_data, 'milestone_id'), array('id' => $genid . 'taskListFormMilestone', 'template_milestone' => '1', 'template_id' => $template_id)) ?>    		
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
    			<a style="margin-left: 10px" id="<?php echo $genid ?>parent_before" href="#" onclick="og.pickParentTemplateTask(this, '<?php echo $genid?>', '<?php echo $task->getId()?>', '<?php echo $template_id?>')"><?php echo lang('set parent task') ?></a>
    			
    		<?php }else{
    			if(array_var($_GET, 'template_task', false)){
    				$parentTask = TemplateTasks::findById($task_data['parent_id']);
    			}else{
    				$parentTask = ProjectTasks::findById($task_data['parent_id']);
    			} 				
 				
 				if ($parentTask instanceof ProjectTask || $parentTask instanceof TemplateTask){?>
 				<span style="display: none;" id="no-task-selected<?php echo $genid?>"><?php echo lang('none')?></span>
    			<a style="display: none;margin-left: 10px" id="<?php echo $genid ?>parent_before" href="#" onclick="og.pickParentTemplateTask(this, '<?php echo $genid?>', '<?php echo $task->getId()?>', '<?php echo $template_id?>')"><?php echo lang('set parent task') ?></a> 
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
						$task = TemplateTasks::findById($task_dep->getPreviousTaskId());
				?>
					<div class="og-add-template-object previous-task">
						<input type="hidden" name="task[previous]['<?php echo $k?>']" value="<?php echo $task->getId()?>" />
						<div class="previous-task-name action-ico ico-task"><?php echo clean($task->getTitle()) ?></div>
						<a href="#" onclick="og.removePreviousTask(this.parentNode, '<?php echo $genid?>', '<?php echo $k?>')" class="removeDiv link-ico ico-delete" style="display: block;"><?php echo lang('remove') ?></a>
						<div class="clear"></div>
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
					onclick="og.pickPreviousTemplateTask(this, '<?php echo $genid?>', '<?php echo $task->getId()?>','<?php echo $template_id?>')"><?php echo lang('add previous task') ?></a>
				
			</div>
		
		</div>
		<div class="clear"></div>
		<?php } ?>
		
  	</div>
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
				height: h,
				allowedContent: true,
				enterMode: CKEDITOR.ENTER_DIV,
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
				entities_additional : '#39,#336,#337,#368,#369'
			});

			og.setDescription = function() {
				var form = Ext.getDom('<?php echo $genid ?>submit-edit-form');
				if (form && form.preventDoubleSubmit) return false;

				setTimeout(function() {
					if (form) form.preventDoubleSubmit = false;
				}, 2000);

				var editor = og.getCkEditorInstance('<?php echo $genid ?>ckeditor');
				if (form) form['task[text]'].value = editor.getData();

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
				$render_defaults = false;
				if ($task->isNew()) {
					$render_defaults = user_config_option("add_task_default_reminder");
				}
				?>
				<?php echo render_add_reminders($task, 'due_date',null,null,"task",$render_defaults); ?>
			</div>
		</div>
		
		<div class="repeat-options-div sub-section-div">
			<h2><?php echo lang('repeating task')?></h2>
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
					<div id="<?php echo $genid ?>repeat_options" style="width: 400px; align: center; text-align: left; <?php echo $hide ?>">
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
								echo "&nbsp;" . text_field('task[repeat_num]', $rnum, array('size' => '3', 'id' => $genid.'repeat_num', 'maxlength' => '3', 'style'=>'width:25px', 'onchange' => 'og.selectRepeatMode(2);')) ."&nbsp;". lang('CAL_TIMES') ?>
							</td></tr>
							<tr><td style="vertical-align:middle"><?php echo radio_field('task[repeat_option]', $rsel3,array('id' => $genid.'repeat_opt_until','value' => '3', 'style' => 'vertical-align:middle', 'onclick' => 'og.viewDays(true)')) ."&nbsp;". lang('CAL_REPEAT_UNTIL');?></td>
								<td style="padding-left:8px;"><?php echo pick_date_widget2('task[repeat_end]', $rend, $genid, 99);?>
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
							<select name="task[repeat_by]">
								<option value="start_date" id="<?php echo $genid ?>rep_by_start_date"<?php if (array_var($task_data, 'repeat_by') == 'start_date') echo ' selected="selected"'?>><?php echo lang('field ProjectTasks start_date')?></option>
								<option value="due_date" id="<?php echo $genid ?>rep_by_due_date"<?php if (array_var($task_data, 'repeat_by') == 'due_date') echo ' selected="selected"'?>><?php echo lang('field ProjectTasks due_date')?></option>
							</select>
						</div>
					</div>
				</td>
			</tr>

			<tr id="<?php echo $genid ?>repeat_days" style="display: none;">
				<td>
				<table>
					<tr><td><input class="checkbox" type="checkbox" value="1" name="task[repeat_saturdays]" /> <?php echo lang('repeat on saturdays')?></td></tr>
					<tr><td><input class="checkbox" type="checkbox" value="1" name="task[repeat_sundays]" /> <?php echo lang('repeat on sundays')?></td></tr>
					<tr><td><input class="checkbox" type="checkbox" value="1" name="task[working_days]" /> <?php echo lang('repeat working days')?></td></tr>
				</table>
				</td>
			</tr>
		  </table>
		<?php }else{ 
			echo lang('option repetitive task completed');
		}?>
		</div>
	
		
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
	<?php } // if ?>
	
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
		
  	</div>
  	
  	
  	
  	  
		<?php if (false && ($has_custom_properties || config_option('use_object_properties')) ) { ?>
		<div id="<?php echo $genid ?>add_custom_properties_div" class="form-tab">
			<div id="<?php echo $genid ?>not_required_custom_properties_container">
		    	<div id="<?php echo $genid ?>not_required_custom_properties">
		      	<?php echo render_object_custom_properties($task, false, $co_type) ?>
		      	</div>
		    </div>
	      <?php echo render_add_custom_properties($task); ?>
	 	</div>
	 	<?php } ?>
  
	    <div id="<?php echo $genid ?>add_subscribers_div" class="form-tab">
	    
		    <div id="<?php echo $genid ?>taskFormSendNotificationDiv" style="display:<?php echo (array_var($task_data, 'display_notification_checkbox')) ? 'block' : 'none' ?>"  class="dataBlock">
				<?php echo checkbox_field('task[send_notification]', array_var($task_data, 'send_notification'), array('id' => $genid . 'taskFormSendNotification', 'style' => 'margin-left:5px;margin-top:3px;')) ?>
				<label for="<?php echo $genid ?>taskFormSendNotification" class="checkbox"><?php echo lang('send task assigned to notification') ?></label>
				<div class="clear"></div>
			</div>
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
			<div id="<?php echo $genid ?>add_subscribers_content">
			</div>
		</div>
	
	
	
	<?php foreach ($categories as $category) { ?>
		<div id="<?php echo $genid . $category['id'] ?>" class="form-tab">
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

	var assigned_user = '<?php echo array_var($task_data, 'assigned_to_contact_id', 0) ?>';
	var start = true;
	
	og.drawAssignedToSelectBox = function(companies, only_me, groups) {
		ogTasks.usersStore['<?php echo $genid ?>'] = ogTasks.buildAssignedToComboStore(companies, only_me, groups);
		var assignCombo = new Ext.form.ComboBox({
			renderTo:'<?php echo $genid ?>assignto_container_div',
			name: 'taskFormAssignedToCombo',
			id: '<?php echo $genid ?>taskFormAssignedToCombo',
			value: assigned_user,
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
		if (assignedto){
			assignedto.value = assigned_user;
		}
	}
	
	og.onAssignToComboSelect = function() {
		combo = Ext.getCmp('<?php echo $genid ?>taskFormAssignedToCombo');
		assignedto = document.getElementById('<?php echo $genid ?>taskFormAssignedTo');
		if (assignedto) assignedto.value = combo.getValue();
		assigned_user = combo.getValue();
		
		ogTasks.applyAssignedToSubtasksInTaskForm('<?php echo $genid?>');
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
			og.openLink(og.getUrl('task', 'allowed_users_to_assign', parameters), {callback: function(success, data){
				only_me = data.only_me ? data.only_me : null;
				if (combo) {
					combo.reset();
					combo.store.removeAll();
					combo.store.loadData(ogTasks.buildAssignedToComboStore(data.companies, only_me, data.groups));
					combo.setValue(prev_value);
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
			}});
			setTimeout(function() { 
				og.redrawingUserList = false;
			}, 1500);
		}		
	}
	
	og.changeTaskRepeat = function() {
		var ro = document.getElementById("<?php echo $genid ?>repeat_options");
		if (ro) ro.style.display = 'none';
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

		if(document.getElementById("<?php echo $genid ?>today").selected){
			og.viewDays(false);
		}else{
			og.viewDays(true);
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
	/*	if (milestone_div) {
			milestone_div.load({
				url: og.getUrl('milestone', 'render_add_milestone', {
					context: dimension_members_json,
					genid: '<?php echo $genid ?>',
					selected: actual_value,
					template_milestone: 1,
					template_id: '<?php echo $template_id ?>'
				}),
				scripts: true
			});
		}*/
	
		var uids = App.modules.addMessageForm.getCheckedUsers('<?php echo $genid ?>');

		if(render_add_subscribers){
			Ext.get('<?php echo $genid ?>add_subscribers_content').load({
				url: og.getUrl('object', 'render_add_subscribers', {
					context: dimension_members_json,
					users: uids,
					genid: '<?php echo $genid ?>',
					otype: '<?php echo $projectTask->manager()->getObjectTypeId()?>'
				}),
				scripts: true
			});
		}
		
		og.redrawUserLists(dimension_members_json);
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

	var listenerId = og.eventManager.addListener('after member_selector init',function(){
		og.reload_task_form_selectors(<?php echo $task->isNew() ? '1' : '0'?>, false);
		og.eventManager.removeListener(listenerId) ;
	});	

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

	<?php if ($task->isNew()){ ?>
		COUNT_LINE = 1;
	<?php	if (count($multi_assignment) > 0) {
				foreach($multi_assignment as $assignment){ ?>
					addMultiAssignment('<?php echo $genid ?>','<?php echo $assignment['assigned_to_contact_id'] ?>' , '<?php echo $assignment['name'] ?>', '<?php echo $assignment['time_estimate_hours'] ?>', '<?php echo $assignment['time_estimate_minutes'] ?>');		
	<?php		}
			}
		}
	?>

	og.pickParentTemplateTask = function(before, genid, task_id, template_id) {
		var extra_list_params = {
				template_id:template_id
		};
		og.ObjectPicker.show(function (objs) {
			if (objs && objs.length > 0) {
				var obj = objs[0].data;
				if (obj.type != 'template_task') {
					og.msg(lang("error"), lang("object type not supported"), 4, "err");
				} else {
					og.addParentTask(this, obj, genid);
				}
			}
		}, before, {
			types: ['template_task'],
			selected_type: 'template_task',
			extra_list_params : extra_list_params
		},'', task_id);
	};
	
	og.addParentTask = function(before, obj) {
		var parent = before.parentNode;
		var count = parent.getElementsByTagName('input').length;
		var div = document.createElement('div');
		div.className = "og-add-template-object " + (count % 2 ? " odd" : "");
		div.innerHTML =
			'<input type="hidden" name="task[parent_id]" value="' + obj.object_id + '" />' +
			'<div class="parent-task-name action-ico ico-'+obj.type+'">' + og.clean(obj.name) + '</div>' +
			'<a href="#" onclick="og.removeParentTask(this.parentNode)" class="removeDiv link-ico ico-delete" style="display: block;">'+lang('remove')+'<div class="clear"></div></div>';
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
	

	$(document).ready(function() {
		if($("#<?php echo $genid?>view_related").val()){
			<?php if($task->isCompleted()){ ?>
			this.dialog = new og.TaskPopUp('task_complete','');
			<?php }else{?>
			this.dialog = new og.TaskPopUp('','');
			<?php }?>
			this.dialog.setTitle(lang('tasks related'));
			this.dialog.show();
		}
		
		<?php if(!$task->isCompleted()){ ?>
			og.changeTaskRepeat();
		<?php }?>

		<?php
			if (!$task->isNew()) {
				$subtasks = ProjectTasks::findAll(array('conditions' => "parent_id=".$task->getId()." AND trashed_by_id=0"));
				foreach ($subtasks as $st) {
					$st_name = clean(escape_character($st->getObjectName()));
					?>
					ogTasks.drawAddSubTaskInputs('<?php echo $genid ?>', {id:'<?php echo $st->getId()?>', name:'<?php echo $st_name?>', assigned_to:'<?php echo $st->getAssignedToContactId()?>'});
		<?php
				}
			} else {?>
			ogTasks.drawAddSubTaskInputs('<?php echo $genid ?>');
		<?php
			}
		?>

		$('#<?php echo $genid?>taskFormApplyAssignee').change(function(event){
			ogTasks.applyAssignedToSubtasksInTaskForm('<?php echo $genid?>');
		});

		$("#<?php echo $genid?>tabs").tabs();

		setTimeout(function() {
			var w = 20;
			var tabs = $("#<?php echo $genid?>tabs .ui-tabs-anchor");
			for (x=0; x<tabs.length; x++) {
				var t = tabs[x];
				w += $(t).outerWidth() + 5;
			}
			
			$("#<?php echo $genid?>tabs").css({'min-width': w+'px'});
			$("#<?php echo $genid?>tabs").parent().css({'overflow-x': 'auto'});
		}, 100);

		$("#ogTasksPanelATTitle").focus();

	});
	
</script>