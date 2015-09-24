ogTasks.addNewTaskGroup = function(data, group_index, draw){
	var group = {};
	
	for (var key in data.groups[group_index]){							
		if(key != 'group_tasks'){
			group[key] = data.groups[group_index][key];
		}												
	}
	
	group.group_tasks = {};
	group.group_tasks_order = new Array();
	group.total_tasks_loaded = 0;
		
	group.total = parseInt(group.total);	
	group.root_total = parseInt(group.root_total);	
		
	group.offset = parseInt(og.noOfTasks);
	
	
	if (typeof draw != 'undefined' && draw){	
		var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
		var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
		
		var displayCriteria = bottomToolbar.getDisplayCriteria();
		var drawOptions = topToolbar.getDrawOptions();
		
		var group_html = ogTasks.drawGroup(displayCriteria, drawOptions, group);
		
		if(typeof group.group_order != 'undefined'){
			var target_order = 0;
			var target_id = 0;
			for (var i = 0; i < ogTasks.Groups.length; i++) {
				if(ogTasks.Groups[i].group_order > target_order && ogTasks.Groups[i].group_order < group.group_order){
					target_order = ogTasks.Groups[i].group_order;
					target_id = ogTasks.Groups[i].group_id;
				}				
			}
			if(target_id == 0){
				$("#ogTasksPanelAddNewTaskThead").after(group_html);				
			}else{
				$("#ogTasksPanelGroup"+target_id+"Totals").after(group_html);
			}	
				
		}else{
			$("#tasksPanelContainer").append(group_html);
		}
	};
	//keep all values for gorup object but change group_tasks from task data to ogTasksTask object
	for (var j = 0; j < data.groups[group_index].group_tasks.length; j++){
		var task_data = data.groups[group_index].group_tasks[j];
		var task = ogTasksCache.addTasks(task_data);

		ogTasks.addTaskToGroup(group, task, draw);		
	}
	
	ogTasks.Groups.push(group);
};

ogTasks.removeTaskGroup = function(group){
	for (var i = 0; i < ogTasks.Groups.length; i++) {
		if (ogTasks.Groups[i].group_id == group.group_id) {
			ogTasks.Groups.splice(i, 1);
		}
	}
	
	if($("#ogTasksPanelGroupCont"+group.group_id).length > 0){
		$("#ogTasksPanelGroupCont"+group.group_id).remove();
	}
};

ogTasks.updateTaskGroups = function(data, add_new_tasks){	
	var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
	var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
	
	var displayCriteria = bottomToolbar.getDisplayCriteria();
	var drawOptions = topToolbar.getDrawOptions();
	
	for (var i = 0; i < data.groups.length; i++){
		var group = ogTasks.getGroup(data.groups[i].group_id);
		
		if (typeof add_new_tasks != 'undefined'){			
			
			for (var j = 0; j < data.groups[i].group_tasks.length; j++){
				var task_data = data.groups[i].group_tasks[j];
				var task = ogTasksCache.addTasks(task_data);
				
				ogTasks.addTaskToGroup(group, task, true);							
			}		
		}
		
		//update group params
		for (var key in data.groups[i]){							
			if(key != 'group_tasks'){
				group[key] = data.groups[i][key];
			}												
		}
		group.rendering = false;	
		
		//update group totals
		$("#ogTasksPanelGroup"+group.group_id+"Totals").replaceWith(ogTasks.newTaskGroupTotals(group));	
		
	}	
};

ogTasks.getGroup = function(id){
	for (var i = 0; i < ogTasks.Groups.length; i++) {
		if (ogTasks.Groups[i].group_id == id) {
			return ogTasks.Groups[i];
		}
	}
	return null;
};

ogTasks.addTaskToGroup = function(group, task, draw){
	var update = false;
	//check if this task is new on the group or not
	if(group.group_tasks[task.id]){
		update = true;
	}
	
	group.group_tasks[task.id] = task;
	
	var task_id = parseInt(task.id);
	if(group.group_tasks_order.indexOf(task_id) == -1){
		group.group_tasks_order.push(task_id);
	}
	
	if(!update){
		group.total_tasks_loaded ++;
	}
	
	if (typeof draw != 'undefined' && draw){	
		if(update){
			ogTasks.reDrawTask(task);
		}else{
			var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
			var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
			
			var displayCriteria = bottomToolbar.getDisplayCriteria();
			var drawOptions = topToolbar.getDrawOptions();
						
			$("#rx__no_tasks_info").remove(); 
			
			ogTasks.drawTask(task, drawOptions, displayCriteria, group.group_id, 1);
			
			var btns = $("#ogTasksPanelTask" + task.id + "G"+group.group_id +" .tasksActionsBtn").toArray();
			og.initPopoverBtns(btns);	
		}	
	}	
};

ogTasks.removeTaskFromGroup = function(group, task){
	delete group.group_tasks[task.id];
	
	group.total_tasks_loaded --;
	
	if($("#ogTasksPanelTask"+task.id+"G"+group.group_id).length > 0){
		$("#ogTasksPanelTask"+task.id+"G"+group.group_id).remove();
	}
	
	if(group.total_tasks_loaded <= 0){
		ogTasks.removeTaskGroup(group);
	}
};

ogTasks.updateTaskGroupsForTask = function(data){	
	var task = ogTasksCache.getTask(data.taskId);
	var task_groups_ids = new Array();
	for (var i = 0; i < data.groups.length; i++){
		var group = ogTasks.getGroup(data.groups[i].group_id);
		if(!group){
			ogTasks.addNewTaskGroup(data, i, true);
			group = ogTasks.getGroup(data.groups[i].group_id);
		}else{
			var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
			var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
			var displayCriteria = bottomToolbar.getDisplayCriteria();
			var drawOptions = topToolbar.getDrawOptions();
			
			//don't add th task if the parent is in this group 
			if(drawOptions.show_subtasks_structure && task.parentId > 0){
				if($("#ogTasksPanelTask"+task.parentId+"G"+group.group_id).length == 0){
					ogTasks.addTaskToGroup(group, task, true);
				}	
			}else{
				ogTasks.addTaskToGroup(group, task, true);
			}
					
		}		
		
		task_groups_ids.push(group.group_id);
	}
	
	//remove task from old groups
	for (var j = 0; j < ogTasks.Groups.length; j++) {
		var group = ogTasks.Groups[j];
		if (group.group_tasks[task.id] && task_groups_ids.indexOf(group.group_id) == -1) {
				ogTasks.removeTaskFromGroup(group, task);				
		}
	}
	
	ogTasks.refreshGroupsTotals();
};