/**
 *  
 * This module holds the rendering logic for the add new task div
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
 
 //************************************
//*		Draw add new task form
//************************************

ogTasks.drawAddNewTaskForm = function(group_id, parent_id, level, position){
	var additionalParams = {};
	var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
	if (toolbar.filterNamesCompaniesCombo.isVisible()){
		var value = toolbar.filterNamesCompaniesCombo.getValue();
		if (value) {
			additionalParams.assigned_to_contact_id = value;
		}
	}
	if (parent_id > 0)
		additionalParams.parent_id = parent_id;
	
	og.render_modal_form('', {c:'task', a:'add_task', params: additionalParams});
	return;
	
	var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
	var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
	var filters = bottomToolbar.getFilters();
	var displayCriteria = bottomToolbar.getDisplayCriteria();
	var drawOptions = topToolbar.getDrawOptions();
	
	if (parent_id > 0)
		var parentTask = ogTasks.getTask(parent_id);
	
	if (displayCriteria.group_by == 'milestone' && group_id != 'unclassified'){
		var milestone_id = group_id;
	} else if (parentTask && parentTask.milestoneId > 0){
		var milestone_id = parentTask.milestoneId;
	} else if (filters.filter == 'milestone') {
		var milestone_id = Ext.getCmp('ogTasksFilterMilestonesCombo').getValue();
	} else {
		var milestone_id = 0;
	}
	
	var assignedToValue = null;
	if (displayCriteria.group_by == 'assigned_to' && group_id != 'unclassified'){
		assignedToValue = group_id;
	} else if(parentTask && parentTask.assignedToId){
		assignedToValue = parentTask.assignedToId;
	} else if (filters.filter == 'assigned_to') {
		assignedToValue = filters.fval;
	}
	
	var member_id = null;
	if (displayCriteria.group_by.indexOf('dimension_') == 0) {
		var dim_id = displayCriteria.group_by.replace('dimension_', '');
		member_id = group_id;
	}
	
	
	var priority = 200;
	if (displayCriteria.group_by == 'priority' && group_id != 'unclassified'){
		priority = group_id;
	}
	
	if (parent_id > 0)
		var containerName = 'ogTasksPanelTask' + parent_id + 'G' + group_id;
	else
		var containerName = 'ogTasksPanelGroup' + group_id;
	
	var task = {
		parentId: parent_id,
		milestoneId: milestone_id,
		member_id: member_id,
		title: '',
		description: '',
		priority: priority,
		dueDate: '',
		startDate: '',
		assignedTo: assignedToValue,
		taskId: 0,
		time_estimated: 0,
		multiAssignment: 0,
		isEdit: false,
		position: position
	};

	this.drawTaskForm(containerName, task);
	
	if(og.config.wysiwyg_tasks){
		var height = $("#tasks_quick_add_selectors").height();
		height = "auto";
		loadCKeditor(0,height);
	}
}

ogTasks.drawEditTaskForm = function(task_id, group_id){
	var task = this.getTask(task_id);
	var containerName = 'ogTasksPanelTask' + task.id + 'G' + group_id;
	if (task){
		og.render_modal_form('', {c:'task', a:'edit_task', params: {id:task.id, use_ajx:1}});
	}
}

ogTasks.performDrawEditTaskForm = function(containerName, task) {
	this.drawTaskForm(containerName, {
		title: task.title,
		description: task.description,
		priority: task.priority,
		members: task.members,
		dueDate: task.dueDate,
		startDate: task.startDate,
		assignedTo: task.assignedToId,
		taskId: task.id,
		time_estimated: task.TimeEstimate,
		multiAssignment: task.multiAssignment,
		isEdit: true
	});
	if(og.config.wysiwyg_tasks){
		var height = $("#tasks_quick_add_selectors").height();
		height = "auto";
		loadCKeditor(task.id, height);
	}
}


//submit the form when the user press enter
ogTasks.checkEnterPress = function (e,id)
{
	var characterCode;
	if (e && e.which) {
		characterCode = e.which;
	} else {
		characterCode = e.keyCode;
	}
	if (characterCode == 13) {
		ogTasks.SubmitNewTask(id,true);
		return false;
	}
	return true;
}

ogTasks.drawAddNewTaskFromData = function(container_id){
	var task = {
		name:'lala'
	};
	$("#"+container_id+" :input").each(function(){
		var input = $(this);
		task[input.attr("name")]=input.val();
		input.val("");
	});
	
	og.render_modal_form('', {c:'task', a:'add_task', params: task});
}

ogTasks.drawTaskForm = function(container_id, data){
	this.hideAddNewTaskForm();
        
	var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
	var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
	var drawOptions = topToolbar.getDrawOptions();
	var padding = (15/* * level*/) - 1;
	
	var html = "<div style='margin-left:" + padding + "px; margin-right:" + padding + "px' class='ogTasksAddTaskForm'>";
	
	if (data.member_id && bottomToolbar.getDisplayCriteria().group_by.indexOf('dimension_') == 0) {
		html += "<input type='hidden' id='ogTasksPanelATMemberId' value='" + data.member_id + "'>";
	}
	
	if (data.parentId > 0){
		var parentTask = ogTasks.getTask(data.parentId);
		html += "<input type='hidden' id='ogTasksPanelATParentId' value='" + data.parentId + "'>";
	}
	html += "<b>" + lang('title') + ":</b><br/>";
        
	html += "<input id='ogTasksPanelATTitle' type='text' class='title' name='task[name]' tabIndex=1000 value='' maxlength='255' size='255'>";
		
	
	html += "<table style='width:100%; margin-top:7px'><tr>";
	
	//First column
	html += "<td style='padding-left:10px; margin-right:10px;width:420px;'>";
	
	// <TASK SELECTORS>
	html +="<div id='tasks_quick_add_selectors'>";
	
	// <MEMBERS SELECTORS>
	//this function only return a visual combo to display, must be repleaced by ajax.
	function dimCombo (dim){
		var comboHtml = '<div style="width: 100%; display: table;">' +
							'<label style="font-size: 100%;"><b>'+ dim.name +':</b></label>' +
							'<div style="float: right; width: 267px;">' +
								'<div class="search-selector" id="og_1409320459_864588member-seleector-dim3">' +
									'<div class="search-input-div">' +
										'<input type="text" placeholder="'+ dim.desc +'" class="search-input ico-search-m">' +
									'</div>' +								
								'</div>' +
							'</div>' +
						'</div>' +
						'<div style="height: 4px;"></div>';
		return comboHtml;
	}
	
	html +="<div id='member_selectors_quick_add'>";
	for (var x=0; x<og.config.quick_add_task_combos.length; x++) {
		if (og.config.quick_add_task_combos[x]) {
			html += dimCombo (og.config.quick_add_task_combos[x]);
		}
	}
	html +="</div>";
	// </MEMBERS SELECTORS>
	
	// <ASSIGN_TO COMBO>
	html += "<div id='ogTasksPanelATAssigned' class='small-member-selector'>" +
				"<b>" + lang('assigned to') + ":&nbsp;</b>" +
				"<span id='ogTasksPanelATAssignedCont' style='float:right;'></span>" +
			"</div>"; 
	// </ASSIGN_TO COMBO>
	
	// <PANEL DATES>
	html += "<div id='ogTasksPanelATDates'>";
	
	var time_picker_html_start = og.config.use_time_in_task_dates ? "<div style='float:left;margin-left: 5px;' id='ogTasksPanelATStartTime'></div>" : "";
	html += "<div id='ogTasksPanelStartDateCont' class='small-member-selector'>" +
				"<div style='float:left;width: 125px;'><b>" + lang('start date') + ":</b></div>&nbsp;" +
				"<div style='float:right;width: 267px;'>" +
					"<div style='float:left;' id='ogTasksPanelATStartDate'></div>" +
					time_picker_html_start +
				"</div>"+
			"</div>";
	
	var time_picker_html_duetime = og.config.use_time_in_task_dates ? "<div style='float:left;margin-left: 5px;' id='ogTasksPanelATDueTime'></div>" : "";
	html += "<div id='ogTasksPanelDueDateCont' class='small-member-selector'>" +
				"<div style='float:left;width: 125px;'><b>" + lang('due date') + ":</b></div>&nbsp;" +
				"<div style='float:right;width: 267px;'>" +
					"<div style='float:left;' id='ogTasksPanelATDueDate'></div>" + 
					time_picker_html_duetime +
				"</div>"+
			"</div>";
	
	html += "</div>";
	// </PANEL DATES>
	
	
	
	if (drawOptions.show_time_estimates) {
		var totalTime = data.time_estimated;
		var minutes = totalTime % 60;
		var hours = (totalTime - minutes) / 60;
		
		// Minute options
		var minuteOptionsHtml = "";
		var minuteOption = 0;
		for (var i = 0; i < 12; i++) {
			minuteOption = i * 5;
			minuteOptionsHtml += "<option value=\"" + minuteOption + "\"";
			if (minutes == minuteOption) minuteOptionsHtml += ' selected="selected"';
			minuteOptionsHtml += ">" + minuteOption + "</option>\n";
		}
		
		html += "<div id='ogTasksPanelATTime' class='small-member-selector'>" +
					"<b>" + lang('estimated time') + ":</b>" +
					"<div class='timeSelectors'>" +
						"<input type='text' id='ogTasksPanelATHours' style='width:25px' tabIndex=1000  name='task[time_estimate_hours]' value='" + hours + "'/>" +
						"&nbsp;" + 
						lang('hours') + 
						"&nbsp;" +
						"<select name='task[time_estimate_minutes]' id='ogTasksPanelATMinutes' size='1' tabindex='1000'>" +
						minuteOptionsHtml +
						"</select>" +
						"&nbsp;" + 
						lang('minutes') + 
					"</div>" +
				"</div>";
	}
	
	// Priority
	html += "<div id='ogTasksPanelATPriority' class='small-member-selector'>" +
				"<b>" + lang('priority') + ":&nbsp;</b>" +			
				"<span id='ogTasksPanelATPriorityCont' style='float:right;margin-right: 165px;'></span>" +
			"</div>";
			
	//Show all options
    html += "<a href='#' class='internalLink' style='float: left;' tabIndex=1000 onclick='ogTasks.TaskFormShowAll(" + data.taskId + ")' id='ogTasksPanelATShowAll'>" + lang('all options') + "...</a>";
    
	html += "<input type='hidden' value='false' name='control_dates' id='control_dates'/>";
	// <BUTTONS>
	html +="<div style='display: inline-block; float: left; clear: left;'>";
	if (og.config.multi_assignment == 1) {
		if (typeof window.loadMultiAssignmentHtml == 'function') {
			if (data.multiAssignment) {
				html += "<button onclick='if (og.TaskMultiAssignment) {og.TaskMultiAssignment();return false;}' tabIndex=1001 type='submit' class='submit'>"
						+ (data.isEdit ? lang('save changes') : lang('add task'))
						+ "</button>&nbsp;&nbsp;<button class='submit' tabIndex=1001 onclick='ogTasks.hideAddNewTaskForm();return false;'>"
						+ lang('cancel') + "</button>";
			} else {
				html += "<button onclick='ogTasks.SubmitNewTask("
						+ data.taskId
						+ ", true);return false;' tabIndex=1001 type='submit' class='submit'>"
						+ (data.isEdit ? lang('save changes') : lang('add task'))
						+ "</button>&nbsp;&nbsp;<button class='submit' tabIndex=1001 onclick='ogTasks.hideAddNewTaskForm();return false;'>"
						+ lang('cancel') + "</button>";
			}
		} else {
			html += "<button onclick='ogTasks.SubmitNewTask("
					+ data.taskId
					+ ", true);return false;' tabIndex=1001 type='submit' class='submit'>"
					+ (data.isEdit ? lang('save changes') : lang('add task'))
					+ "</button>&nbsp;&nbsp;<button class='submit' tabIndex=1001 onclick='ogTasks.hideAddNewTaskForm();return false;'>"
					+ lang('cancel') + "</button>";
		}
	} else {
		html += "<button onclick='ogTasks.SubmitNewTask("
				+ data.taskId
				+ ", true);return false;' tabIndex=1001 type='submit' class='submit'>"
				+ (data.isEdit ? lang('save changes') : lang('add task'))
				+ "</button>&nbsp;&nbsp;<button class='submit' tabIndex=1001 onclick='ogTasks.hideAddNewTaskForm();return false;'>"
				+ lang('cancel') + "</button>";
	}
	html +="</div>";
	// </BUTTONS>
	
	// </TASK SELECTORS>
	html +="</div>";
	
	//End First column
	html += "</td>";
	
	//Second column
	html += "<td>";
    
	//Description
	if(og.config.wysiwyg_tasks){
    	html += "<div id='ogTasksPanelATDesc'><b>" + lang('description') + ":</b><br/>";
        html += "<textarea id='" + og.genid + "ckeditor" + data.taskId + "' cols='40' rows='10' name='task[text]' class='short ckeditor' tabIndex=1000 style='height:50px'>" + data.description + "</textarea></div>";
    }else{
        html += "<div id='ogTasksPanelATDesc'><b>" + lang('description') + ":</b><br/>";
        html += "<textarea id='ogTasksPanelATDescCtl' cols='40' rows='10' name='task[text]' class='short' tabIndex=1000 style='height:50px'>" + data.description + "</textarea></div>";
    }
    
	//MultiAssignment
	if(og.config.multi_assignment == 1){
         if(typeof window.loadMultiAssignmentHtml == 'function'){
        	 html += '<a href="#" class="link-ico ico-add" onclick="addMultiAssignment(\'' + og.genid + '\',\'\',\'\',\'\',\'\');return false;">' +  lang('add multi assignment') + '</a>';
         }            
    }
	
    //Send mail notification
	var chkIsVisible = data.assignedTo && data.assignedTo != '0';
	var chkIsChecked = parseInt(chkIsVisible && ogTasks.userPreferences.defaultNotifyValue); //&& (!this.currentUser || data.assignedTo != this.currentUser.id)
	if(og.config.show_notify_checkbox_in_quick_add){
		html += '<div  id="ogTasksPanelATNotifyDiv" style="float: right;"><label for="ogTasksPanelATNotify">' + lang('send notification') + '<input style="width:14px; vertical-align: middle; margin-left: 10px;" type="checkbox" tabindex="1000" name="task[notify]" id="ogTasksPanelATNotify" ' + (chkIsChecked? 'checked':'') + '/>&nbsp;</label></div>';
	}
	
	//MultiAssignment
	if(og.config.multi_assignment == 1){
         if(typeof window.loadMultiAssignmentHtml == 'function'){
        	 html += loadMultiAssignmentHtml(data.taskId);
         }            
    }
	
	html += "<div id='ogTasksPanelATContext' style='padding-top:5px;padding-bottom: 10px; display:none'><table><tr><td style='width:120px;'><b>" + lang('context') + ":&nbsp;</b></td><td><input type=\"hidden\" id=\"ogTasksPanelMembers\" name=\"members\" value=\"\"/><div id='ogTasksPanelContextSelector'>";

	html += og.popupMemberChooserHtml('', ogTasks.tasks_object_type_id, "ogTasksPanelMembers", data.members, true);
	html += "</div></td>";
	
	html += "</tr></table></div>";
	html += "<div id='ogTasksPanelATMilestone' style='padding-top:5px; display:none'><table><tr><td style='width:120px;'><b>" + lang('milestone') + ":&nbsp;</b></td><td><div id='ogTasksPanelMilestoneSelector'></div></td>";
	
	html += "</tr></table></div>";
	if (data.milestoneId) {
		html += "<input type='hidden' name='task_milestone_id' value='"+data.milestoneId+"'/>";
	}
	html += "<div id='ogTasksPanelATObjectType' style='padding-top:5px;'><table><tr><td style='width:120px;'><b>" + lang('object type') + ":&nbsp;</b></td><td><input id='ogTasksPanelObjectTypeSelector' style='min-width:120px;max-width:300px' type='text' value='" + (data.otype ? data.otype : og.defaultTaskType) + "' name='task[object_subtype]'/></td></tr></table></div>";
       
	
    //End Second column
    html += "</td>";
	
	
    html += "</tr><tr>";
	
	html += "</tr></table>";
	html += '</div>';
	
	var div = document.createElement('div');
	div.className = 'ogTasksTaskRow';
	div.id = 'ogTasksPanelAT';
	div.innerHTML = html;
	
	
	var modal_params = {
			'escClose': true,
			'overlayClose': false,
			'closeHTML': '<a id="ogTasksPanelAT_close_link" class="modal-close"></a>'
		};
	if(data.position){
		modal_params['maxWidth'] =($( window ).width()-$('#ogTasksPanelListATTitle').offset().left*2);
		modal_params['position'] =[$('#ogTasksPanelListATTitle').offset().top -30,$('#ogTasksPanelListATTitle').offset().left -30];
	}
	
	$.modal(div,modal_params);
	  
	  	 
	//Create Ext components
	var object_subtypes = ogTasks.ObjectSubtypes;
	var co_types = [];
	for (var i=0; i < object_subtypes.length; i++) {
		co_types.push([object_subtypes[i].id, og.clean(object_subtypes[i].name)]);
	}
	new Ext.form.ComboBox({
		store: new Ext.data.SimpleStore({
       		fields: ["value", "text"],
       		data: co_types
		}),
		id: 'ogTasksPanelObjectTypeSelector',
		valueField: 'value',
		displayField : 'text',
		//typeAhead : true,
		mode : 'local',
		triggerAction : 'all',
		selectOnFocus : true,
		width : 140,
		valueNotFoundText : '',
		applyTo : "ogTasksPanelObjectTypeSelector"
	});
	if (co_types.length == 0) {
   		document.getElementById('ogTasksPanelATObjectType').style.display = 'none';
   	}

   	var milestoneCombo = bottomToolbar.filterMilestonesCombo.cloneConfig({
		name: 'task[milestone_id]',
		renderTo: 'ogTasksPanelMilestoneSelector',
		id: 'ogTasksPanelATMilestoneCombo',
		hidden: true,
		width: 200,
		value: data.milestoneId,
		tabIndex:1000
	});
	ogTasks.selectedMilestone = data.milestoneId;
	og.openLink(og.getUrl('milestone', 'get_assignable_milestones'), {callback:ogTasks.drawMilestonesCombo});
	
	var get_params = {};
	if (data.members && data.members.length > 0) {
		get_params['member_ids'] = data.members;
	}
	
	var member_to_render = [];
	if (data.member_id && data.member_id > 0) {
		member_to_render = data.member_id;
	}else{
		
	}
	
	var task_cp_vals = ogTasks.custom_properties[data.taskId];
	
	if (task_cp_vals) {
		var extra_params = {};
		extra_params['cp_list_values'] = [];
		for (cpi in task_cp_vals) {
			extra_params['cp_list_values'].push({
				cp_id: cpi,
				values: task_cp_vals[cpi]
			});
		}
		get_params['extra_params'] = Ext.util.JSON.encode(extra_params);
	}
	ogTasks.assignedTo = data.assignedTo ? data.assignedTo : 0;
	og.openLink(og.getUrl('task', 'allowed_users_to_assign', get_params), {callback:ogTasks.drawAssignedToCombo});
	
	//re render member selectors
	if(og.config.quick_add_task_combos.length > 0){   
		og.openLink(og.getUrl('member', 'get_rendered_member_selectors', {id: data.taskId, view_name: 'quick_add_task', members: member_to_render, objtypeid: ogTasks.tasks_object_type_id,genid: og.genid, ajax: true}), {
			callback: function(success, data) {
				$('#member_selectors_quick_add').html(data.htmlToAdd);
				var height_sel = $("#tasks_quick_add_selectors").height()
				//resize description size
				$("#ogTasksPanelATDesc").css({
				    height:height_sel +20
				});
				
				//add selection change listener to members selectors combos
				if(og.genid in member_selector){
					for (var dim in og.dimensions_info) {
						if(dim in member_selector[og.genid].properties){
							member_selector[og.genid].properties[dim].listeners["on_selection_change"] = 'ogTasks.renderAssignedToCombo()';
						}
					}
				}
								
				var editor = og.getCkEditorInstance(og.genid + 'ckeditor' + data.objectId);
				// The height value now applies to the editing area.
				editor.resize( '100%', height_sel);					
			},
			scope: this
		});
	}
	document.getElementById('ogTasksPanelATTitle').value = data.title;
	document.getElementById('ogTasksPanelATTitle').focus();
	
	if (data.startDate){
		var date = new Date(data.startDate * 1000);
		date = new Date(Date.parse(date.toUTCString().slice(0, -4)));
		var sd = date.dateFormat(og.preferences['date_format']);
		var starttime = date.dateFormat(og.preferences['time_format_use_24'] == 1 ? 'G:i' : 'g:i A');
	} else sd = '';
	var DtStart = new og.DateField({
		renderTo:'ogTasksPanelATStartDate',
		id:'ogTasksPanelATStartDateCmp',
		style:'width:100px',
		emptyText: og.preferences.date_format_tip,
		tabIndex:1000,
		value: sd
	});
	if (data.dueDate){
		var date = new Date(data.dueDate * 1000);
		date = new Date(Date.parse(date.toUTCString().slice(0, -4)));
		var dd = date.dateFormat(og.preferences['date_format']);
		var duetime = date.dateFormat(og.preferences['time_format_use_24'] == 1 ? 'G:i' : 'g:i A');
	} else dd = '';
	var DtDue = new og.DateField({
		renderTo:'ogTasksPanelATDueDate',
		id:'ogTasksPanelATDueDateCmp',
		style:'width:100px',
		tabIndex:1000,
		value: dd,
		emptyText: og.preferences.date_format_tip,
		listeners: {
			'change': {
				fn: function(due, val, old) {
					if (this.getValue() && this.getValue() > due.getValue()) {
						alert(lang("warning start date greater than due date"));
					}
				},
				scope: DtStart
			}
		}
	});
	DtStart.on('change', function(start, val, old) {
		if (this.getValue() && this.getValue() < start.getValue()) {
			alert(lang("warning start date greater than due date"));
		}
	},
	DtDue);

	if (og.config.use_time_in_task_dates) {
		if (starttime == undefined) {
			var start_time = new Date(og.config.work_day_start_time * 1000);
			start_time = new Date(Date.parse(start_time.toUTCString().slice(0, -4)));
			starttime = start_time.dateFormat(og.preferences['time_format_use_24'] == 1 ? 'G:i' : 'g:i A');
		}
		var startTime = new Ext.form.TimeField({
			renderTo:'ogTasksPanelATStartTime',
			id: 'ogTasksPanelATStartTimeCmp',
			width: 80,
                        format: og.config.time_format_use_24_duetime,
			tabIndex: 1000,
			emptyText: 'hh:mm',
			value: starttime
		});
		
		if (duetime == undefined) {
			var date_time = new Date(og.config.work_day_end_time * 1000);
			date_time = new Date(Date.parse(date_time.toUTCString().slice(0, -4)));
			duetime = date_time.dateFormat(og.preferences['time_format_use_24'] == 1 ? 'G:i' : 'g:i A');
		}
		var dueTime = new Ext.form.TimeField({
			renderTo:'ogTasksPanelATDueTime',
			id: 'ogTasksPanelATDueTimeCmp',
			width: 80,
			format: og.config.time_format_use_24_duetime,
			tabIndex: 1000,
			emptyText: 'hh:mm',
			value: duetime
		});
	}
	
	var priorityCombo = bottomToolbar.filterPriorityCombo.cloneConfig({
		name: 'task[priority]',
		renderTo: 'ogTasksPanelATPriorityCont',
		id: 'ogTasksPanelATPriorityCombo',
		hidden: false,
		width: 100,
		value: data.priority,
		tabIndex:1000
	});
}

ogTasks.closeModal = function(){
	$('#ogTasksPanelAT_close_link').click();
}

ogTasks.addNewTaskShowMore = function(){

	document.getElementById('ogTasksPanelATShowMore').style.display = 'none';
	document.getElementById('ogTasksPanelATShowAll').style.display = 'inline';
	
	document.getElementById('ogTasksPanelATDesc').style.display = 'block';
	
	if (document.getElementById('ogTasksPanelATDates'))
		document.getElementById('ogTasksPanelATDates').style.display = 'block';
	
	if (document.getElementById('ogTasksPanelATPriority'))
		document.getElementById('ogTasksPanelATPriority').style.display = 'block';
		
	document.getElementById('ogTasksPanelATAssigned').style.visibility = 'visible';
	document.getElementById('ogTasksPanelATContext').style.display = 'block';
	document.getElementById('ogTasksPanelATMilestone').style.display = 'block';
	if (ogTasks.ObjectSubtypes && ogTasks.ObjectSubtypes.length > 0) {
		document.getElementById('ogTasksPanelATObjectType').style.display = 'block';
	} else {
		document.getElementById('ogTasksPanelATObjectType').style.display = 'none';
	}
	document.getElementById('ogTasksPanelATDesc').focus(); 
}

ogTasks.TaskFormShowAll = function(task_id){
	var params = this.GetNewTaskParameters(false,task_id);
	if (task_id)
		og.openLink(og.getUrl('task', 'edit_task', {id:task_id}), {'post' : params});
	else
		og.openLink(og.getUrl('task', 'add_task'), {'post' : params});
}

ogTasks.hideAddNewTaskForm = function(){
	ogTasks.closeModal();
	
	var oldForm = document.getElementById('ogTasksPanelAT');
	if (oldForm)
		oldForm.parentNode.removeChild(oldForm);	
}

ogTasks.GetNewTaskParameters = function(wrapWith,task_id){
	var parameters = [];

	//Members
	var members = $("#"+og.genid +"members").val();
	parameters.members=members;
	
	//Conditional fields
	var parentField = document.getElementById('ogTasksPanelATParentId');
	if (parentField)
		parameters["parent_id"] = parentField.value;
            
    var controlDates = document.getElementById('control_dates');
	if (controlDates)
		parameters["control_dates"] = controlDates.value;
	
	var hoursPanel = document.getElementById('ogTasksPanelATHours');
	if (hoursPanel)
		parameters["hours"] = hoursPanel.value;
            
    var minutePanel = document.getElementById('ogTasksPanelATMinutes');
	if (minutePanel)
		parameters["minutes"] = minutePanel.value;
	
	var startPanel = Ext.getCmp('ogTasksPanelATStartDateCmp');
	if (startPanel && startPanel.getValue() != '') {
		parameters["task_start_date"] = startPanel.getValue().format(og.preferences['date_format']);
		var startTimePanel = Ext.getCmp('ogTasksPanelATStartTimeCmp');
		if (startTimePanel && startTimePanel.getValue() != '') {
			parameters["task_start_time"] = startTimePanel.getValue();
		}
	}
	
	var duePanel = Ext.getCmp('ogTasksPanelATDueDateCmp');
	if (duePanel && duePanel.getValue() != '') {
		parameters["task_due_date"] = duePanel.getValue().format(og.preferences['date_format']);
		var dueTimePanel = Ext.getCmp('ogTasksPanelATDueTimeCmp');
		if (dueTimePanel && dueTimePanel.getValue() != '') {
			parameters["task_due_time"] = dueTimePanel.getValue();
		}
	}
		
	var notify = document.getElementById('ogTasksPanelATNotify');
	if (notify && notify.style.display != 'none' && notify.checked)
		parameters["notify"] = true;
	else
		parameters["notify"] = false;

	if (og.config.wysiwyg_tasks) {
		var editor = og.getCkEditorInstance(og.genid + 'ckeditor' + task_id);
		parameters["text"] = editor.getData();
	} else {
		var description = document.getElementById('ogTasksPanelATDescCtl');
		parameters["text"] = description ? description.value : "";
	}
	
	var applyMI = document.getElementById('ogTasksPanelApplyMI');
	parameters["apply_milestone_subtasks"] = applyMI && applyMI.checked ? "checked" : "";
	
	var applyWS = document.getElementById('ogTasksPanelApplyWS');
	parameters["apply_ws_subtasks"] = applyWS && applyWS.checked ? "checked" : "";
	
	var applyAT = document.getElementById('ogTasksPanelApplyAssignee');
	parameters["apply_assignee_subtasks"] = applyAT && applyAT.checked ? "checked" : "";
	
	var milestones_combo = Ext.getCmp('ogTasksPanelATMilestoneCombo');
	var milestone_id = milestones_combo ? milestones_combo.getValue() : (milestone_hf = document.getElementById('task_milestone_id') ? milestone_hf.value : null);
	if (milestone_id) parameters["milestone_id"] = milestone_id;
	
	//Always visible
	parameters["assigned_to_contact_id"] = Ext.getCmp('ogTasksPanelATUserCompanyCombo').getValue();
	parameters["priority"] = Ext.getCmp('ogTasksPanelATPriorityCombo').getValue();
	parameters["name"] = document.getElementById('ogTasksPanelATTitle').value;
	parameters["object_subtype"] = Ext.getCmp('ogTasksPanelObjectTypeSelector').getValue();
	
	//parameters["members"] = document.getElementById('ogTasksPanelMembers').value;
	if (member_input = document.getElementById('ogTasksPanelATMemberId')) {
		parameters["member_id"] = member_input.value;
	}
	
	// multi_assignment
	if (og.config.multi_assignment == 1) {
		if (typeof window.loadMultiAssignmentHtml == 'function') {
			var applyChange = document.getElementById(og.genid + 'multi_assignment_aplly_change');
			if (applyChange)
				parameters["multi_assignment_aplly_change"] = applyChange.value;

			var multi_assignment = {};
			var assigned_to_contact_id = new Array();
			var name = new Array();
			var time_estimate_hours = new Array();
			var time_estimate_minutes = new Array();
			var pos = 1;
			var line = 0;
			$("#" + og.genid + "multi_assignment :input").each(function() {
				if (pos == 1) {
					assigned_to_contact_id[line] = $(this).val();
				} else if (pos == 3) {
					name[line] = $(this).val();
				} else if (pos == 4) {
					time_estimate_hours[line] = $(this).val();
				} else if (pos == 5) {
					time_estimate_minutes[line] = $(this).val();
				}

				if (pos == 5) {
					pos = 1;
					line++;
				} else {
					pos++;
				}
			});

			for (i = 0; i < line; i++) {
				multi_assignment[i] = {
					assigned_to_contact_id : assigned_to_contact_id[i],
					name : name[i],
					time_estimate_hours : time_estimate_hours[i],
					time_estimate_minutes : time_estimate_minutes[i]
				};
			}
		}
	}

	if (wrapWith) {
		var params2 = [];
		for (var i in parameters) {
			if (parameters[i] || parameters[i] === 0 || parameters[i] === "") {
				params2[wrapWith + "[" + i + "]"] = parameters[i];
			}
		}
		if (og.config.multi_assignment == 1) {
			if (typeof window.loadMultiAssignmentHtml == 'function') {
				params2["multi_assignment"] = Ext.util.JSON.encode(multi_assignment);
			}
		}
		return params2;
	} else {
		if (og.config.multi_assignment == 1) {
			if (typeof window.loadMultiAssignmentHtml == 'function') {
				parameters["multi_assignment"] = Ext.util.JSON.encode(multi_assignment);
			}
		}
		return parameters;
	}
}

ogTasks.SubmitNewTask = function(task_id,view_popup){
	if (typeof window.og.ControlQuickDates == 'function') {
		var continuing_process = og.ControlQuickDates(task_id);
		if (!continuing_process) {
			return false;
		}
	}
	var parameters = this.GetNewTaskParameters('task', task_id);
	var url = '';
	if (task_id > 0) {
		if (view_popup) {
			var related = og.checkRelated("task", task_id);
			if (related) {
				this.dialog = new og.TaskPopUp("edit", task_id);
				this.dialog.setTitle(lang('tasks related'));
				this.dialog.show();
				return false;
			} else {
				url = og.getUrl('task', 'quick_edit_task', {
					id : task_id
				});
			}
		} else {
			var opt = $("#" + og.genid + "type_related").val();
			parameters["type_related"] = opt;
			url = og.getUrl('task', 'quick_edit_task', {
				id : task_id
			});
		}
	} else {
		url = og.getUrl('task', 'quick_add_task');
	}
	
	og.openLink(url, {
		method: 'POST',
		post: parameters,
		callback: function(success, data) {
			if (success && ! data.errorCode) {
				this.drawTaskRowAfterEdit(data);
			} else {
				if (!data.errorMessage || data.errorMessage == '') {
					og.err(lang("error adding task"));
				}
			}
		},
		scope: this
	});
	
	ogTasks.hideAddNewTaskForm();
}

//this function is called after edit or add
ogTasks.drawTaskRowAfterEdit = function(data) {
	if (!data || !data.task) return;
	
	var task = ogTasksCache.addTasks(data.task);
	
	//get the groups that this task belongs to and draw
	ogTasks.getGroupsForTask(task.id);	
	
	
	var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
	if (!topToolbar) return;
	var drawOptions = topToolbar.getDrawOptions();
	
	if(drawOptions.show_subtasks_structure && task.parentId > 0){
		//redraw all parent views
		var parent = ogTasksCache.getTask(task.parentId);
		ogTasks.reDrawTask(task);
	}
}

ogTasks.drawTasksRowsAfterAddEdit = function(data) {
	if (!data || !data.tasks) return;
	for ( var j = 0; j < data.tasks.length; j++) {
		ogTasks.drawTaskRowAfterEdit({'task':data.tasks[j]});
	}
}

ogTasks.buildAssignedToComboStore = function(companies, only_me, groups) {
	var usersStore = [];
	var comp_array = [];
	var cantU = 0;
	var cantC = 0;
	var preserveSelectedUser = false;
	
	if (!only_me) {
		usersStore[cantU++] = ['0', lang('dont assign')];
	}
	
	if (og.config.multi_assignment == 1 && groups && groups.length > 0) {
		for (i=0; i<groups.length; i++) {
			var group = groups[i];                        
			comp_array[cantC++] = ["group-" + group.id, og.clean(group.name)];
		}
		comp_array[cantC++] = ['0', '--'];
	}
	
	if (companies) {
		for (i=0; i<companies.length; i++) {
			comp = companies[i];
			if (!only_me && comp.id > 0) {
				comp_array[cantC++] = [comp.id, og.clean(comp.name)];
			}
			for (j=0; j<comp.users.length; j++) {
				usr = comp.users[j];
				if (usr.isCurrent) {
					var uname = lang('me') + " (" + usr.name + ")";
					usersStore.unshift([usr.id, uname]);
					cantU++;
				} else if (!only_me) {
					usersStore[cantU++] = [usr.id, og.clean(usr.name)];
				}
				if(usr.id == ogTasks.assignedTo){
					preserveSelectedUser = true;
				}
			}
		}
		// sort user list
		var me = usersStore.shift();
		if (!only_me) var dont_assign = usersStore.shift();
		usersStore.sort(function(a, b){
			var namea = a[1].toLowerCase();
			var nameb = b[1].toLowerCase();
			if (namea < nameb) return -1;
			if (namea > nameb) return 1;
			return 0;
		});
		if (!only_me) usersStore.unshift(dont_assign);
		usersStore.unshift(me);
		
		if (og.config['can_assign_tasks_to_companies'] && comp_array.length > 0) {
			usersStore[cantU++] = ['0', '--'];
		}
	}
	if (og.config['can_assign_tasks_to_companies'] && comp_array.length > 0) {
		usersStore = usersStore.concat(comp_array);
	}
	
	if(!preserveSelectedUser){
		ogTasks.assignedTo = 0;
	}
	
	// remove undefined values
	var cleanStore = [];
	for (x in usersStore) {
		if (usersStore[x] && typeof(usersStore[x]) != 'function') cleanStore.push(usersStore[x]);
	}
	
	return cleanStore;
}

ogTasks.buildMilestonesComboStore = function(ms) {
	var milestonesData = [[0,"--" + lang('none') + "--"]];
    for (i in ms){
    	if (typeof(ms[i]) == 'function') continue;
    	if (ms[i].id)
    		milestonesData[milestonesData.length] = [ms[i].id, ms[i].t];
    }
	return milestonesData;
}

ogTasks.drawAssignedToCombo = function(success, data) {
	var only_me = data.only_me ? data.only_me : null;
	var usersStore = ogTasks.buildAssignedToComboStore(data.companies, only_me, data.groups);
	var prev_combo = Ext.get('ogTasksPanelATUserCompanyCombo');
	if (prev_combo) prev_combo.remove();
		
	var namesCombo = new Ext.form.ComboBox({
		name: 'task[assigned_to]',
		renderTo: 'ogTasksPanelATAssignedCont',
		id: 'ogTasksPanelATUserCompanyCombo',
		store: usersStore,
		hidden: false,
		width: 265,
		displayField:'text',
        //typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        value: ogTasks.assignedTo,
		emptyText: (lang('select user or group') + '...'),
	    valueNotFoundText: '',
		tabIndex:1000,
		listeners: {
			'select':function(combo, record){
				var checkbox = document.getElementById('ogTasksPanelATNotify');
				if (checkbox){
					var checkboxDiv = document.getElementById('ogTasksPanelATNotifyDiv');
					var user = ogTasks.getUser(record.data.value);
					if (user && record.data.value != '-1' && record.data.value != '0'){
						checkboxDiv.style.display = 'block';
						var currentUser = ogTasks.currentUser;
						if (ogTasks.userPreferences.defaultNotifyValue == 1) {
							checkbox.checked = (record.data.value != (currentUser.id));
						} else {
							checkbox.checked = false;
						}
						ogTasks.assignedTo = combo.getValue();
					} else {
						checkboxDiv.style.display = 'none';
						checkbox.checked = false;
					}
				}
			}
		}
	});
}

//for quick add/edit tasks
ogTasks.renderAssignedToCombo = function() {
	//get selected members
	var member_ids_input = Ext.fly(Ext.get(og.genid + member_selector[og.genid].hiddenFieldName));
	var selected_members = member_ids_input.getValue();
		
	var slected_user = Ext.getCmp('ogTasksPanelATUserCompanyCombo').getValue();
	//render allowed users to assign in task quick add/edit
	var task_user = $("#ogTasksPanelATUserCompanyCombo");
	if (task_user && task_user.is(":visible")) {
		var get_params = {};	
		get_params['member_ids'] = JSON.parse(selected_members).toString(); 
		ogTasks.assignedTo = slected_user; 
		og.openLink(og.getUrl('task', 'allowed_users_to_assign', get_params), {callback:ogTasks.drawAssignedToCombo});
	}
}

ogTasks.drawMilestonesCombo = function(success, data) {
	var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
	mStore = ogTasks.buildMilestonesComboStore(data.milestones);
	prev_combo = Ext.get('ogTasksPanelATMilestoneCombo');
	if (prev_combo) {
		m_val = Ext.getCmp('ogTasksPanelATMilestoneCombo').getValue();		
		var found = false;
		for (var i=0; i<mStore.length; i++) {
			if (mStore[i][0] == m_val) {
				ogTasks.selectedMilestone = mStore[i][0];
				found = true;
				break;
			}
		}
		if (!found) ogTasks.selectedMilestone = 0;
		prev_combo.remove();
	}

	var milestoneCombo = bottomToolbar.filterMilestonesCombo.cloneConfig({
		name: 'task[milestone_id]',
		renderTo: 'ogTasksPanelMilestoneSelector',
		id: 'ogTasksPanelATMilestoneCombo',
		store: mStore,
		hidden: false,
		width: 200,
		value: ogTasks.selectedMilestone,
		tabIndex:1000
	});
}


// Add subtask functions for add/edit task form
ogTasks.drawAddSubTaskInputs = function(genid, data) {
		
	if (!data) data = {};
	if (!data.id) data.id = 0;
	if (!data.name) data.name = '';
	if (!data.assigned_to) data.assigned_to = 0;

	if (!ogTasks.subtask_count) ogTasks.subtask_count = {};
	if (!ogTasks.subtask_count[genid]) ogTasks.subtask_count[genid] = 0;
	
	var i = ogTasks.subtask_count[genid];
	
	var html = '<div class="subtask-inputs-container '+genid+'">';
	html += '<div class="inputs-container">';
	html += '<input type="hidden" name="task[subtasks]['+i+'][id]" value="'+ data.id +'">';
	html += '<input type="text" name="task[subtasks]['+i+'][name]" value="'+ data.name +'" placeholder="'+ lang('task') +'" class="subtask-name">';
	html += '<input type="hidden" name="task[subtasks]['+i+'][assigned_to]" value="'+ data.assigned_to +'" id="'+ genid + '_' + i +'_assigned_to">';
	html += '<input type="hidden" name="task[subtasks]['+i+'][deleted]" value="0" id="'+ genid + '_' + i +'_deleted" class="deleted-hf">';
	html += '</div>';
	html += '<div id="'+ genid +'_'+ i +'assigned_to_container" class="assigned-container"></div>';
	html += '<div class="remove-link-container">';
	html += '<a href="#" class="link-ico ico-delete remove-subtask-link" onclick="this.parentNode.parentNode.style.display=\'none\'; document.getElementById(\''+genid +'_'+ i +'_deleted\').value=1; document.getElementById(\''+ genid +'undo_remove\').style.display=\'\'">'+ lang('remove') +'</a>';
	html += '</div><div class="clear"></div></div>';
	
	$('#'+ genid +'subtasks').append(html);
	
	if (!ogTasks.usersStore || !ogTasks.usersStore[genid]) {
		var parameters = og.contextManager.plainContext() ? {context: og.contextManager.plainContext()} : {};
		og.openLink(og.getUrl('task', 'allowed_users_to_assign', parameters), {callback: function(success, d){
			only_me = d.only_me ? d.only_me : null;
			ogTasks.usersStore[genid] = ogTasks.buildAssignedToComboStore(d.companies, only_me, d.groups);
			ogTasks.drawAddSubTaskAssignedToInput(ogTasks.usersStore[genid], data, genid, i);
		}});
	} else {
		ogTasks.drawAddSubTaskAssignedToInput(ogTasks.usersStore[genid], data, genid, i);
	}

	ogTasks.subtask_count[genid] = ogTasks.subtask_count[genid] + 1;
}

ogTasks.drawAddSubTaskAssignedToInput = function(usersStore, data, genid, i) {
	var container = document.getElementById(genid +'_'+ i +'assigned_to_container');
	if (!container) {
		// if container no longer exists => do nothing 
		// for example when modal form is closed before form callback is executed and the container has been removed from dom
		return;
	}
	only_me = data.only_me ? data.only_me : null;
	var stAssignCombo = new Ext.form.ComboBox({
		subtask_index: i,
		renderTo: genid +'_'+ i +'assigned_to_container',
		name: 'subtask_assigned_to_' + i,
		id: genid + 'subtask_assigned_to_' + i,
		value: data.assigned_to,
		store: usersStore,
		displayField:'text',
        mode: 'local',
        cls: 'assigned-to-combo',
        triggerAction: 'all',
        selectOnFocus: true,
        valueField: 'value',
        emptyText: (lang('select user or group') + '...')
	});
	stAssignCombo.on('select', function(combo, record, index) {
		var hf = document.getElementById(genid + '_' + combo.subtask_index + '_assigned_to');
		if (hf) hf.value = combo.getValue();
	});
}

ogTasks.applyAssignedToSubtasksInTaskForm = function(genid) {
	if ($('#'+ genid +'taskFormApplyAssignee').attr('checked') == 'checked') {
		var combos_aux = $('.assigned-to-combo');
		for (var i=0; i<combos_aux.length; i++) {
			if (combos_aux[i].id.indexOf(genid + 'subtask_assigned_to_') >= 0) {
				
				var combo = Ext.getCmp(combos_aux[i].id);
				var original_assigned = document.getElementById(genid + 'taskFormAssignedTo');
				if (original_assigned) {
					combo.setValue(original_assigned.value);
					var idx = combos_aux[i].id.substring(combos_aux[i].id.lastIndexOf('_')+1);
					$('#'+ genid +'_'+ idx +'_assigned_to').val(original_assigned.value);
				}
			}
		}
	}
}

ogTasks.undoRemoveSubtasks = function(genid) {
	$('.'+ genid +'.subtask-inputs-container').show();
	$('#'+ genid +'undo_remove').hide();
	var inputs = $('.'+ genid +'.subtask-inputs-container input.deleted-hf');
	for (var i=0; i<inputs.length; i++) {
		$(inputs[i]).val(0);
	}
}