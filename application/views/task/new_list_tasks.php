<?php
	require_javascript("og/CSVCombo.js");
	require_javascript("og/DateField.js");
	
	if (config_option('use tasks dependencies')) {
		require_javascript('og/tasks/task_dependencies.js');
	}

	if(config_option('multi_assignment') && Plugins::instance()->isActivePlugin('crpm')){
		require_javascript('multi_assignment.js', 'crpm');
	}

	$loc = user_config_option('localization');
	if (strlen($loc) > 2) $loc = substr($loc, 0, 2);

	$genid = gen_id();
	
	$all_templates_array = array();
	$project_templates_array = array();
	$templates_array = array();
	$project_templates_array = array();
	$tasks_array = array();
	$internal_milestones_array = array();
	$external_milestones_array = array();
	$users_array = array();
	$companies_array = array();
	$allUsers_array = array();
	$object_subtypes_array = array();
	
	
	if (isset($all_templates) && !is_null($all_templates)){
		foreach($all_templates as $template) {
			$all_templates_array[] = $template->getArrayInfo();
		}
	}
	
	if (isset($project_templates) && !is_null($project_templates)) {
		foreach($project_templates as $template) {
			$project_templates_array[] = $template->getArrayInfo();
		}
	}
	
	$assigned_users = array();
	$ids = array();
	if (isset($tasks)) {
		foreach($tasks as $task) {
			$ids[] = $task['id'];
			$tasks_array[] = ProjectTasks::getArrayInfo($task);
			if ($task['assigned_to_contact_id'] > 0) {
				$assigned_users[$task['assigned_to_contact_id']] = $task['assigned_to_contact_id'];
			}
		}

		$read_objects = ReadObjects::getReadByObjectList($ids, logged_user()->getId());
		foreach($tasks_array as &$data) {
			$data['isread'] = isset($read_objects[$data['id']]);
		}
	}
	
	if (is_array($internalMilestones)) {
		foreach($internalMilestones as $milestone) {
			$internal_milestones_array[] = $milestone->getArrayInfo();
		}
	}
	
	if (is_array($externalMilestones)) {
		foreach($externalMilestones as $milestone) {
			$external_milestones_array[] = $milestone->getArrayInfo();
		}
	}
	
	$user_ids = array();
	foreach($users as $user) {
		$user_info = $user->getArrayInfo();
		if ($user->getId() == logged_user()->getId()) {
			$user_info['isCurrent'] = true;
		}
		$user_ids[$user->getId()] = $user->getId();
		$users_array[] = $user_info;
	}
	// add assigned users to users array
	foreach ($assigned_users as $auser) {
		if (!in_array($auser, $user_ids)) {
			$user = Contacts::findById($auser);
			if ($user instanceof Contact) $users_array[] = $user->getArrayInfo();
		}
	}
	
	foreach($allUsers as $usr) {
		$allUsers_array[] = $usr->getArrayInfo();
	}
	
	foreach($companies as $company) {
		$companies_array[] = $company->getArrayInfo();
	}
	
	foreach($object_subtypes as $ot) {
		$object_subtypes_array[] = $ot->getArrayInfo();
	}
	
	if (!isset($dependency_count)) $dependency_count = array();
?>

<script>
og.noOfTasks = '<?php echo user_config_option('noOfTasks') ?>';
og.genid = '<?php echo $genid?>';
og.config.wysiwyg_tasks = '<?php echo config_option('wysiwyg_tasks') ? true : false ?>';
og.config.use_tasks_dependencies = '<?php echo config_option('use tasks dependencies') ? "1" : "0" ?>';
og.config.time_format_use_24 = '<?php echo user_config_option('time_format_use_24') ? ' - G:i' : ' - g:i A' ?>';
og.config.time_format_use_24_duetime = '<?php echo user_config_option('time_format_use_24') ? 'G:i' : 'g:i A' ?>';
og.config.work_day_start_time = '<?php echo strtotime(user_config_option('work_day_start_time')) ?>';
og.config.work_day_end_time = '<?php echo strtotime(user_config_option('work_day_end_time')) ?>';
og.config.multi_assignment = '<?php echo config_option('multi_assignment') && Plugins::instance()->isActivePlugin('crpm') ? '1' : '0' ?>';
og.config.use_milestones = <?php echo config_option('use_milestones') ? 'true' : 'false' ?>;
og.config.show_notify_checkbox_in_quick_add = <?php echo user_config_option('show_notify_checkbox_in_quick_add') ? 'true' : 'false' ?>;
og.config.tasks_show_description_on_time_forms = <?php echo user_config_option('tasksShowDescriptionOnTimeForms') ? 'true' : 'false' ?>;
og.config.quick_add_task_combos = <?php 
		$object = "";
		$dimensions_user = get_user_dimensions_ids();
		$dimensions_to_show = explode(",",user_config_option("quick_add_task_view_dimensions_combos"));
		$dimensions_to_skip = array_diff($dimensions_user, $dimensions_to_show);
		
		//sort combos
		function cmp($a, $b)
		{
			return ($a < $b) ? -1 : 1;
		}
		usort($dimensions_to_show, "cmp");
				
		foreach ($dimensions_to_show as $key=>$dimension_id){
			if (in_array($dimension_id, $dimensions_to_skip)) continue;
			$dim = Dimensions::instance()->getDimensionById($dimension_id);
			if($dim instanceof Dimension){
				if($key!=0) $object .=",";
				$object .= "{name : '". escape_character($dim->getName())."', desc : '".escape_character(lang('add new relation ' . $dim->getCode()))."'}";
			}
		}		
		echo "[".$object."]";
?>;
ogTasks.custom_properties = <?php echo json_encode($cp_values)?>;



</script>         
         

      
<div id="taskPanelHiddenFields">
	<input type="hidden" id="hfProjectTemplates" value="<?php echo clean(str_replace('"',"'", escape_character(json_encode($project_templates_array)))) ?>"/>
	<input type="hidden" id="hfAllTemplates" value="<?php echo clean(str_replace('"',"'", escape_character(json_encode($all_templates_array)))) ?>"/>
	<input type="hidden" id="hfTasks" value="<?php echo clean(str_replace('"',"'", escape_character(json_encode($tasks_array)))) ?>"/>
	<input type="hidden" id="hfIMilestones" value="<?php echo clean(str_replace('"',"'", escape_character(json_encode($internal_milestones_array)))) ?>"/>
	<input type="hidden" id="hfEMilestones" value="<?php echo clean(str_replace('"',"'", escape_character(json_encode($external_milestones_array)))) ?>"/>
	<input type="hidden" id="hfUsers" value="<?php echo clean(str_replace('"',"'", escape_character(json_encode($users_array)))) ?>"/>
	<input type="hidden" id="hfAllUsers" value="<?php echo clean(str_replace('"',"'", escape_character(json_encode($allUsers_array)))) ?>"/>
	<input type="hidden" id="hfCompanies" value="<?php echo clean(str_replace('"',"'", escape_character(json_encode($companies_array)))) ?>"/>
	<input type="hidden" id="hfUserPreferences" value="<?php echo clean(str_replace('"',"'", escape_character(json_encode($userPreferences)))) ?>"/>
	<input type="hidden" id="hfUserPermissions" value="<?php echo clean(str_replace('"',"'", escape_character(json_encode($userPermissions)))) ?>"/>
	<input type="hidden" id="hfObjectSubtypes" value="<?php echo clean(str_replace('"',"'", escape_character(json_encode($object_subtypes_array)))) ?>"/>
	<input type="hidden" id="hfDependencyCount" value="<?php echo clean(str_replace('"',"'", escape_character(json_encode($dependency_count)))) ?>"/>
	<input id="<?php echo $genid?>type_related" type="hidden" name="type_related" value="only" />
	<input id="<?php echo $genid?>complete_task" type="hidden" name="complete_task" value="yes" />        
</div>

<div id="tasksPanel" class="ogContentPanel" style="background-color:white;background-color:#F0F0F0;height:100%;width:100%;">
    
	<div id="tasksPanelTopToolbar" class="x-panel-tbar" style="width:100%;display:block;background-color:#F0F0F0;"></div>
	<div id="tasksPanelBottomToolbar" class="x-panel-tbar" style="width:100%;display:block;background-color:#F0F0F0;"></div>
	<div id="tasksPanelContent" style="background-color:white;padding-top:0px;overflow-y:scroll;overflow-x:auto;position:relative;">
	<?php if (isset($displayTooManyTasks) && $displayTooManyTasks){ ?>
	<div class="tasksPanelWarning ico-warning32" style="font-size:10px;color:#666;background-repeat:no-repeat;padding-left:40px;max-width:920px; margin:20px;border:1px solid #E3AD00;background-color:#FFF690;background-position:4px 4px;">
		<div style="font-weight:bold;width:99%;text-align:center;padding:4px;color:#AF8300;"><?php echo lang('too many tasks to display', user_config_option('task_display_limit')) ?></div>
	</div>
	<?php } ?>
		<table id="tasksPanelContainer" style="background-color:white;padding-top:0px;position: absolute;width: 100%;table-layout: fixed;">
	<?php if(!(isset($tasks) || $userPreferences['groupBy'] == 'milestone')) { ?>
			<div style="font-size:130%;width:100%;text-align:center;padding-top:10px;color:#777;"><?php echo lang('no tasks to display') ?></div>
	<?php } ?>			
		</table>
	</div>
</div>

<script type="text/javascript">
	if (!ogTasks.tasks_object_type_id) ogTasks.tasks_object_type_id = '<?php echo ProjectTasks::instance()->getObjectTypeId() ?>';
	if (rx__TasksDrag)
		rx__TasksDrag.initialize();

	ogTasks.userPreferences = Ext.util.JSON.decode(document.getElementById('hfUserPreferences').value);

	ogTasks.userPermissions = Ext.util.JSON.decode(document.getElementById('hfUserPermissions').value);
	

	var mili = 0;
	if (og.TasksTopToolbar == 'undefined') {
		mili = 500;
	}

	ogTasks.expandedGroups = [];

	// to prevent js execution before the js files are received
	setTimeout(function () {
		var ogTasksTT = new og.TasksTopToolbar({
			projectTemplatesHfId:'hfProjectTemplates',
			allTemplatesHfId:'hfAllTemplates',
			renderTo:'tasksPanelTopToolbar'
		});
		var ogTasksBT = new og.TasksBottomToolbar({
			renderTo:'tasksPanelBottomToolbar',
			usersHfId:'hfUsers',
			companiesHfId:'hfCompanies',
			internalMilestonesHfId:'hfIMilestones',
			externalMilestonesHfId:'hfEMilestones',
			subtypesHfId:'hfObjectSubtypes'
		});
	
		og.defaultTaskType = '<?php echo config_option('default task co type') ?>';
		
		function resizeTasksPanel(e, id) {
			var tpc = document.getElementById('tasksPanelContent');
			if (tpc) {
				tpc.style.height = (document.getElementById('tasksPanel').clientHeight - 68) + 'px';
			} else {
				og.removeDomEventHandler(window, 'resize', id);
			}
		}
		if (Ext.isIE) {
			og.addDomEventHandler(document.getElementById('tasksPanelContent'), 'resize', resizeTasksPanel);
		} else {
			og.addDomEventHandler(window, 'resize', resizeTasksPanel);
		}
		resizeTasksPanel();
		ogTasks.loadDataFromHF();

		ogTasks.draw();

	}, mili);

	Ext.extend(og.TaskPopUp, Ext.Window, {
		accept: function() {
			var task_id = $("#related_task_id").val();
			var action = $("#action_related").val();
			var opt = $("#<?php echo $genid?>type_related").val();
			if(action == "edit"){
				ogTasks.SubmitNewTask(task_id, false);
			}else{
				ogTasks.executeAction(action,'',opt);
			}
			this.close();
		}
	});

	function selectRelated(val){
		$("#<?php echo $genid?>type_related").val(val);
	}

	Ext.extend(og.TaskCompletePopUp, Ext.Window, {
		accept: function() {
			var task_id = $("#complete_task_id").val();
			var opt = $("#<?php echo $genid?>complete_task").val();
			if(task_id != ""){
				ogTasks.ToggleCompleteStatusOk(task_id, 0 ,opt);
			}else{
				ogTasks.executeAction('complete','',opt);
			}
			this.close();
			$("#<?php echo $genid?>complete_task").val("yes");
		}
	});

	function selectTaskCompletePopUp(val){
		$("#<?php echo $genid?>complete_task").val(val);
	}

	function loadCKeditor(id, height){
		var instance = CKEDITOR.instances['<?php echo $genid ?>ckeditor' + id];
		if(instance){
			CKEDITOR.remove(instance);
		}
		var editor = CKEDITOR.replace('<?php echo $genid ?>ckeditor' + id, {
			height: height,
			allowedContent: true,
			enterMode: CKEDITOR.ENTER_DIV,
			shiftEnterMode: CKEDITOR.ENTER_BR,
			disableNativeSpellChecker: false,
			language: '<?php echo $loc ?>',
			customConfig: '',
			contentsCss: ['<?php echo get_javascript_url('ckeditor/contents.css').'?rev='.product_version_revision();?>', '<?php echo get_stylesheet_url('og/ckeditor_override.css').'?rev='.product_version_revision();?>'],
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
					og.adjustCkEditorArea('<?php echo $genid ?>',id);
					editor.resetDirty();
				}
			},
			entities_additional : '#39,#336,#337,#368,#369'
		});
	}
	
	

	
	
	
</script>
<?php 
	// to include additional templates in the tasks list
	$more_content_templates = array();
	Hook::fire("include_tasks_template", null, $more_content_templates);
	foreach ($more_content_templates as $ct) {
		$this->includeTemplate(get_template_path(array_var($ct, 'template'), array_var($ct, 'controller'), array_var($ct, 'plugin')));
	}
