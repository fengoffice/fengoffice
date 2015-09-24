/**
 * main.js
 *
 * This module holds the structure information for all elements used in the ordering and grouping algorithms,
 * and holds the code for ordering and grouping tasks.
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */

ogTasks.Tasks = [];
ogTasks.Users = [];
ogTasks.Companies = [];
ogTasks.Milestones = [];

ogTasks.TasksList = {};

ogTasks.Groups = [];

ogTasks.redrawGroups = true;

//ogTasks.prevWsValue = -1; //Used to view if ws selector changed its value, to refresh the assingedto combo
ogTasks.assignedTo = '-1'; //Used to init the assignedto combo when it is refreshed
ogTasks.selectedMilestone = 0;

//************************************
//*		Structure definitions
//************************************

ogTasksTask = function(){
	this.id;
	this.title;
	this.description;
	this.dependants;
	this.createdOn;
	this.createdBy;
	this.status = 0;
	this.statusOnCreate = 0;
	this.parentId = 0;
	this.priority = 200;
	this.milestoneId;
	this.assignedToId;
	this.assignedById;
	this.dueDate;
	this.startDate;
	this.workingOnIds;
	this.workingOnTimes;
	this.workingOnPauses;
	this.previous_tasks_total;
	this.pauseTime;
	this.isAdditional = false;
	this.isRead = true;
	this.completedById;
	this.completedOn;
	this.repetitive = false;
	this.otype;
	this.percentCompleted = 0;
	this.members;
	this.depCount;
	this.memPath;
	this.useDueTime = false;
	this.useStartTime = false;
	this.multiAssignment = 0;
	
	this.createdByName;
	this.assignedToName;
	this.milestoneName;
	this.workspacePaths;
	
	this.subtasks = [];
	this.subtasksIds = [];
	this.parent;
	
	this.divInfo = [];
	this.isChecked = false;
	this.isExpanded = false;
	this.isCreatedClientSide = false;
	
	this.canAddTimeslots = false;
}

ogTasksTask.prototype.flatten = function(){
	var result = [this];
	if (this.subtasks.length > 0) {
		for (var i = 0; i < this.subtasks.length; i++) {
			result = result.concat(this.subtasks[i].flatten());
		}
	}
	return result;
}

ogTasksTask.prototype.setFromTdata = function(tdata){
	this.id = tdata.id;
	this.title = tdata.name;
	this.description = tdata.description;
	this.createdOn = tdata.createdOn;
	this.createdBy = tdata.createdById;
		
	var dummyDate = new Date();

	if (tdata.dependants) this.dependants = tdata.dependants; else this.dependants = [];
	if (tdata.status) this.status = tdata.status; else this.status = 0;
	if (tdata.parentId) this.parentId = tdata.parentId; else this.parentId = 0;
	if (tdata.priority) this.priority = tdata.priority; else this.priority = 200;
	if (tdata.milestoneId) this.milestoneId = tdata.milestoneId; else this.milestoneId = null;
	if (tdata.assignedToContactId) this.assignedToId = tdata.assignedToContactId; else this.assignedToId = null;
	if (tdata.assignedById) this.assignedById = tdata.assignedById; else this.assignedById = null;
	if (tdata.dueDate) this.dueDate = tdata.dueDate; else this.dueDate = null;
	if (tdata.startDate) this.startDate = tdata.startDate; else this.startDate = null;
	if (tdata.workingOnIds) this.workingOnIds = tdata.workingOnIds; else this.workingOnIds = null;
	if (tdata.workingOnTimes) this.workingOnTimes = tdata.workingOnTimes; else this.workingOnTimes = null;
	if (tdata.workingOnPauses) this.workingOnPauses = tdata.workingOnPauses; else this.workingOnPauses = null;
	if (tdata.previous_tasks_total) this.previous_tasks_total = tdata.previous_tasks_total; else this.previous_tasks_total = 0;
	if (tdata.pauseTime) this.pauseTime = tdata.pauseTime; else this.pauseTime = null;
	if (tdata.completedById) this.completedById = tdata.completedById; else this.completedById = null;
	if (tdata.completedOn) this.completedOn = tdata.completedOn; else this.completedOn = null;
	if (tdata.repetitive) this.repetitive = true;
	if (tdata.isread) this.isRead = true; else this.isRead = false;
	if (tdata.otype) this.otype = tdata.otype; else this.otype = null;
	if (tdata.percentCompleted) this.percentCompleted = tdata.percentCompleted; else this.percentCompleted = 0;
	if (tdata.members) this.members = tdata.members; else this.members = [];
	
	if (tdata.timeEstimateString) this.estimatedTime = tdata.timeEstimateString; else this.estimatedTime = '';
	
	if (tdata.pending_time) this.pending_time = tdata.pending_time; else this.pending_time = 0;
	if (tdata.pending_time_string) this.pending_time_string = tdata.pending_time_string; else this.pending_time_string = '';
	if (tdata.worked_time) this.worked_time = tdata.worked_time; else this.worked_time = 0;
	if (tdata.worked_time_string) this.worked_time_string = tdata.worked_time_string; else this.worked_time_string = '';
	
	if (tdata.timeEstimate) this.TimeEstimate = tdata.timeEstimate; else this.TimeEstimate = 0;
	if (tdata.depCount) this.depCount = tdata.depCount; else this.depCount = null;
	if (tdata.memPath) this.memPath = tdata.memPath; else this.memPath = [];
	if (tdata.useDueTime) this.useDueTime = tdata.useDueTime;
	if (tdata.useStartTime) this.useStartTime = tdata.useStartTime;
	if (tdata.multiAssignment) this.multiAssignment = tdata.multiAssignment;
	
	if (tdata.subtasksIds) this.subtasksIds = tdata.subtasksIds;
	
	if (tdata.can_add_timeslots) this.canAddTimeslots = tdata.can_add_timeslots;
}

ogTasksMilestone = function(id, title, dueDate, totalTasks, completedTasks, isInternal, isUrgent){
	this.id = id;
	this.title = title;
	
	var dummyDate = new Date();
	this.dueDate = dueDate;
	
	this.completedTasks = completedTasks;
	this.totalTasks = totalTasks;
	this.isInternal = isInternal;
	this.isUrgent = isUrgent;
	this.completedById;
}

ogTasksCompany = function(id, name){
	this.id = id;
	this.name = name;
}

ogTasksUser = function(id, name, companyId){
	this.id = id;
	this.name = name;
	this.companyId = companyId;
}

ogTasksObjectSubtype = function(id, name){
	this.id = id;
	this.name = name;
}

ogTasksDependencyCount = function(id, count, dependants){
	this.id = id;
	this.count = count;
	this.dependants = dependants;
}

//************************************
//*		Data loading
//************************************

ogTasks.loadDataFromHF = function(){
	var result = [];
	var tasksString = document.getElementById('hfTasks').value;
	result['tasks'] = Ext.util.JSON.decode(tasksString);
	result['internalMilestones'] = Ext.util.JSON.decode(document.getElementById('hfIMilestones').value);
	result['externalMilestones'] = Ext.util.JSON.decode(document.getElementById('hfEMilestones').value);
	result['users'] = Ext.util.JSON.decode(document.getElementById('hfUsers').value);
	result['allUsers'] = Ext.util.JSON.decode(document.getElementById('hfAllUsers').value);
	result['companies'] = Ext.util.JSON.decode(document.getElementById('hfCompanies').value);
	result['objectSubtypes'] = Ext.util.JSON.decode(document.getElementById('hfObjectSubtypes').value);
	result['dependencyCount'] = Ext.util.JSON.decode(document.getElementById('hfDependencyCount').value);
	
	return ogTasks.loadData(result);
}


ogTasks.loadData = function(data){
	
	var i;
		
	this.Users = [];
	for (var i = 0; i < data['users'].length; i++){
		var udata = data['users'][i];
		if (udata.id){
			var user =  new ogTasksUser(udata.id,udata.name,udata.cid);
			this.Users[ogTasks.Users.length] = user;
			if (udata.isCurrent)
				this.currentUser = user;
		}
	}
	
	this.TotalCols = {};
	var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
	var drawOptions = topToolbar.getDrawOptions();	
	if (drawOptions.show_time_estimates) {
		this.TotalCols.estimatedTime = {title: 'estimated', group_total_field: 'TimeEstimate', row_field: 'estimatedTime'};
	}
	if (drawOptions.show_time_pending) {
		this.TotalCols.pendingTime = {title: 'pending', group_total_field: 'pending_time', row_field: 'pending_time_string'};	
	}
	if (drawOptions.show_time_worked) {
		this.TotalCols.workedTime = {title: 'worked', group_total_field: 'worked_time', row_field: 'worked_time_string'};	
	}
	
	this.AllUsers = [];
	for (var i = 0; i < data['allUsers'].length; i++){
		var udata = data['allUsers'][i];
		if (udata.id){
			var user =  new ogTasksUser(udata.id,udata.name,udata.cid);
			this.AllUsers[ogTasks.AllUsers.length] = user;
		}
	}

	this.Companies = [];
	for (var i = 0; i < data['companies'].length; i++){
		var cdata = data['companies'][i];
		if (cdata.id)
			this.Companies[ogTasks.Companies.length] = new ogTasksCompany(cdata.id,cdata.name);
	}
	
	this.Milestones = [];
	for (var i = 0; i < data['internalMilestones'].length; i++){
		var mdata = data['internalMilestones'][i];
		if (mdata.id){
			with (mdata) {
				var milestone = new ogTasksMilestone(id,t,dd,tnum,tc,true,is_urgent);
			}
			if (milestone) {
				if (mdata.compId) milestone.completedById = mdata.compId;
				if (mdata.compOn) milestone.completedOn = mdata.compOn;
				this.Milestones[ogTasks.Milestones.length] = milestone;
			}
		}
	}
	for (var i = 0; i < data['externalMilestones'].length; i++){
		var mdata = data['externalMilestones'][i];
		if (mdata.id){
			with (mdata) {
				var milestone = new ogTasksMilestone(id,t,dd,tnum,tc,false,is_urgent);
			}
			if (mdata.compId) milestone.completedById = mdata.compId;
			if (mdata.compOn) milestone.completedOn = mdata.compOn;
			this.Milestones[ogTasks.Milestones.length] = milestone;
		}
	}
	
	this.ObjectSubtypes = [];
	for (var i = 0; i < data['objectSubtypes'].length; i++){
		var otdata = data['objectSubtypes'][i];
		if (otdata.id){
			var ot =  new ogTasksObjectSubtype(otdata.id,otdata.name);
			this.ObjectSubtypes[ogTasks.ObjectSubtypes.length] = ot;
		}
	}
	
	this.DependencyCount = [];
	for (var i = 0; i < data['dependencyCount'].length; i++){
		var dcdata = data['dependencyCount'][i];
		if (dcdata.id){
			var dc =  new ogTasksDependencyCount(dcdata.id, dcdata.count, dcdata.dependants);
			this.DependencyCount[ogTasks.DependencyCount.length] = dc;
		}
	}
}



//************************************
//*		Grouping algorithms
//************************************

ogTasks.getBottomParent = function(task){
	if (task.parent)
		return this.getBottomParent(task.parent);
	else
		return task;
}


//************************************
//*		Ordering Algorithms
//************************************
ogTasks.TaskSelected = function(checkbox, task_id, group_id){
	var task = this.getTask(task_id);
	task.isChecked = checkbox.checked;
	var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
	topToolbar.updateCheckedStatus();
	
	//if (task.isChecked) rx__TasksDrag.addTaskToMove(task_id);
	//else rx__TasksDrag.removeTaskToMove(task_id);
}


ogTasks.GroupSelected = function(checkbox, group_id){
	this.expandGroup(group_id);
	var group = this.getGroup(group_id);
	var tasks = [];
	
	for (var i in group.group_tasks) {
		tasks.push(group.group_tasks[i]);
	}
			

	for (var i = 0; i < tasks.length; i++){
		tasks[i].isChecked = checkbox.checked;
		var tgId = "T" + tasks[i].id + 'G' + group_id;
		var chkTask = document.getElementById('ogTasksPanelChk' + tgId);
		chkTask.checked = checkbox.checked;
		
		//if (chkTask.checked) rx__TasksDrag.addTaskToMove(tasks[i].id);
		//else rx__TasksDrag.removeTaskToMove(tasks[i].id);
		
		var table = document.getElementById('ogTasksPanelTaskTable' + tgId);
		if (table)
			table.className = checkbox.checked ? 'ogTasksTaskTableSelected' : 'ogTasksTaskTable';
	}
	var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
	topToolbar.updateCheckedStatus();
}


//************************************
//*		Helpers
//************************************

ogTasks.updateDependantTasks = function(task_id, add){
	var task = ogTasksCache.getTask(task_id);
	var dependant_task;
	for (var i = 0; i < task.dependants.length; i++){		
		dependant_task = ogTasksCache.getTask(task.dependants[i]);
		if (add){
			dependant_task.previous_tasks_total++;
		}else{
			dependant_task.previous_tasks_total--;
		}
		ogTasks.UpdateTask(task.dependants[i],true);
	}
}

ogTasks.executeAction = function(actionName, ids, options){
	if (!ids)
		var ids = this.getSelectedIds();
	
	og.openLink(og.getUrl('task', 'multi_task_action'), {
		method: 'POST',
		post: {
			"ids": ids.join(','),
			"action" : actionName,
			"options": options
		},
		callback: function(success, data) {
			if (success && ! data.errorCode) {
				for (var i = 0; i < data.tasks.length; i++){
					var tdata = data.tasks[i];
					var task = ogTasksCache.addTasks(tdata);
					if (actionName == 'delete' || actionName == 'archive'){
						
						//update dependants 
						if (actionName == 'delete'){
							ogTasks.updateDependantTasks(task.id,false);
						}
						
						//remove task from cache	
						ogTasksCache.removeTask(task);
						ogTasks.removeTaskFromView(task);
					}else{						
						ogTasks.UpdateTask(task.id,false);	
					}
				}
				
				var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
				topToolbar.updateCheckedStatus();
				
				ogTasks.refreshGroupsTotals();
			} else {
			
			}
		},
		scope: this
	});
}

ogTasks.setAllCheckedValue = function(checked){
	for (var i = 0; i < this.Tasks.length; i++){
		this.Tasks[i].isChecked = checked;
	}
}

ogTasks.getSelectedIds = function(){
	var result = [];
	for(var prop in ogTasksCache.Tasks) {
		var task = ogTasksCache.Tasks[prop];
		if (task.isChecked) {
			result.push(task.id);
		}
	}
	return result;
}

ogTasks.setAllExpandedValue = function(expanded){
	for (var i = 0; i < this.Tasks.length; i++){
		this.Tasks[i].isExpanded = expanded;
	}
}

ogTasks.getUserCompanyName = function(assigned_to){
	var user = this.getUser(assigned_to, true);
	if (user) {
		return user.name;
	} else {
		user = this.getCompany(assigned_to);
		if (user) {
			return user.name;
		}
	}
	return "";
}

ogTasks.getTask = function(id){
	return ogTasksCache.getTask(id);	
}

ogTasks.removeTask = function(id){
	rx__TasksDrag.removeTaskToMove(id);
	for (var i = 0; i < this.Tasks.length; i++) {
		if (this.Tasks[i].id == id){
			if (this.Tasks[i].milestoneId > 0) {
				var mstone = ogTasks.getMilestone(this.Tasks[i].milestoneId);
				if (mstone && !this.Tasks[i].isCreatedClientSide) {
					mstone.totalTasks -= 1;
					mstone.completedTasks -= (this.Tasks[i].status == 0 && (this.Tasks[i].statusOnCreate == 1))? 1:0 ? 1 : 0;
				}
			}
			this.Tasks.splice(i,1);
			return true;
		}
	}
	return false;
}
ogTasks.redrawTask = function(id){
	for (var i = 0; i < this.Tasks.length; i++) {
		if (this.Tasks[i].id == id){
			if (this.Tasks[i].milestoneId > 0) {
				var mstone = ogTasks.getMilestone(this.Tasks[i].milestoneId);
				if (mstone && !this.Tasks[i].isCreatedClientSide) {
					mstone.totalTasks -= 1;
					mstone.completedTasks -= (this.Tasks[i].status == 0 && (this.Tasks[i].statusOnCreate == 1))? 1:0 ? 1 : 0;
				}
			}
			this.Tasks.splice(i,1);
			return true;
		}
	}
	return false;
}
ogTasks.getMilestone = function(id){
	for (var i = 0; i < this.Milestones.length; i++) {
		if (this.Milestones[i].id == id) {
			return this.Milestones[i];
		}
	}
	return null;
}

ogTasks.getUser = function(id, lookInAll){
	if (lookInAll) {
		for (var i = 0; i < this.AllUsers.length; i++) {
			if (this.AllUsers[i].id == id) {
				return this.AllUsers[i];
			}
		}
	} else {
		for (var i = 0; i < this.Users.length; i++) {
			if (this.Users[i].id == id) {
				return this.Users[i];
			}
		}
	}
	return null;
}

ogTasks.getCompany = function(id){
	for (var i = 0; i < this.Companies.length; i++) {
		if (this.Companies[i].id == id) {
			return this.Companies[i];
		}
	}
	return null;
}

ogTasks.getObjectSubtype = function(id){
	for (var i = 0; i < this.ObjectSubtypes.length; i++) {
		if (this.ObjectSubtypes[i].id == id) {
			return this.ObjectSubtypes[i];
		}
	}
	return null;
}

ogTasks.getDependencyCount = function(id){
	for (var i = 0; i < this.DependencyCount.length; i++) {
		if (this.DependencyCount[i].id == id) {
			return this.DependencyCount[i];
		}
	}
	return null;
}

ogTasks.setSubtasksFromData = function(task, subtdata){
	for (var j = 0; j < task.subtasks.length; j++) {
		var subt = task.subtasks[j];
		for (var k = 0; k < subtdata.length; k++) {
			if (subtdata[k].id == subt.id) {
				subt.setFromTdata(subtdata[k]);
				break;
			}
		}
		ogTasks.setSubtasksFromData(subt, subtdata);
	}
}

//--------------------------------
//		Mouse movements
//--------------------------------

ogTasks.mouseMovement = function(task_id, group_id, mouse_is_over){
	if (og.loggedUser.isGuest) return;
	if (mouse_is_over){
		if (!task_id)
			this.groupMouseOver(group_id);
		else
			this.taskMouseOver(task_id, group_id);
		ogTaskEvents.lastTaskId = task_id;
		ogTaskEvents.lastGroupId = group_id;
		
		ogTaskEvents.showGroupHeader = group_id == ogTaskEvents.lastGroupId;
	} else {
		if (!task_id) {
			ogTaskEvents.mouseOutTimeout = setTimeout('ogTasks.groupMouseOut("' + group_id + '")',20);
		} else {
			ogTaskEvents.mouseOutTimeout = setTimeout('ogTasks.taskMouseOut(' + task_id + ',"' + group_id + '")',20);
		}
		ogTaskEvents.lastTaskId = null;
		ogTaskEvents.lastGroupId = null;
	}
}

ogTasks.groupMouseOver = function(group_id){
	var actions = document.getElementById('ogTasksPanelGroupActions' + group_id);
	if (actions) {
		actions.style.opacity = '1.0';
		actions.style.filter = 'alpha(opacity=100)';
	}
}

ogTasks.groupMouseOut = function(group_id){
	if (!ogTaskEvents.lastGroupId || ogTaskEvents.lastGroupId != group_id){
		var actions = document.getElementById('ogTasksPanelGroupActions' + group_id);
		if (actions) {
			actions.style.opacity = '0.35';
			actions.style.filter = 'alpha(opacity=35)';
		}
	}
}


ogTasks.taskMouseOver = function(task_id, group_id){
	var table = document.getElementById('ogTasksPanelTaskTableT' + task_id + 'G' + group_id);
	if (table)
		table.className = 'ogTasksTaskTableSelected';
	var expander = document.getElementById('ogTasksPanelExpanderT' + task_id + 'G' + group_id);
	if (expander)
		expander.style.visibility='visible';
	var actions = document.getElementById('ogTasksPanelTaskActionsT' + task_id + 'G' + group_id);
	if (actions)
		actions.style.visibility='visible';
	this.groupMouseOver(group_id);
}

ogTasks.taskMouseOut = function(task_id, group_id){
	if (!ogTaskEvents.lastTaskId || ogTaskEvents.lastTaskId != task_id){
		var table = document.getElementById('ogTasksPanelTaskTableT' + task_id + 'G' + group_id);
		var chk = document.getElementById('ogTasksPanelChkT' + task_id + 'G' + group_id);
		if (table && chk)
			if (!chk.checked)
				table.className = 'ogTasksTaskTable';
		var expander = document.getElementById('ogTasksPanelExpanderT' + task_id + 'G' + group_id);
		if (expander)
			expander.style.visibility='hidden';
		var actions = document.getElementById('ogTasksPanelTaskActionsT' + task_id + 'G' + group_id);
		if (actions)
			actions.style.visibility='hidden';
		this.groupMouseOut(group_id);
	}
}

ogTasks.flattenTasks = function(tasks){
	var result = [];
	for (var i = 0; i < tasks.length; i++) {
		result = result.concat(tasks[i].flatten());
	}
	return result;
}

$(function (){
	
	
 });