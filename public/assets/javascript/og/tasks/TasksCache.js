//wraper to store tasks on ogTasks.Tasks
ogTasksCache = {
		'Tasks' : {}
};

ogTasksCache.addTasks = function(task_data){
	var task = new ogTasksTask();
	task.setFromTdata(task_data);

	// When refreshing an existing task (e.g. after viewing it), preserve runtime state that the
	// server response doesn't include: subtask IDs built up by prior lazy loads, and the
	// expanded/toggled flags so that onTaskLinkClick can capture the task for restore on
	// subsequent navigations.
	// IMPORTANT: only preserve subtasksIds when the server response OMITS the key. When the
	// server explicitly sends subtasksIds (even an empty array, e.g. after a child was trashed),
	// it is authoritative and must overwrite the stale cache — otherwise a trashed subtask whose
	// parent now reports zero children would be resurrected from the old client state and redrawn.
	var existing = this.Tasks[task.id];
	if (existing) {
		var serverProvidedSubtasks = (typeof task_data.subtasksIds !== 'undefined');
		if (!serverProvidedSubtasks && existing.subtasksIds.length > 0) {
			task.subtasksIds = existing.subtasksIds.slice();
		}
		if (existing.isExpanded) task.isExpanded = existing.isExpanded;
		if (existing.toggleSubtasksShow) task.toggleSubtasksShow = existing.toggleSubtasksShow;
	}

	this.Tasks[task.id] = task;
	
	//parent
	if(task.parentId > 0){
		var parent = ogTasksCache.getTask(task.parentId);
		if (typeof parent != "undefined") {
			// Keep the parent's existing subtasksIds order (from getSubTasksIds /
			// tasksOrderSubtasksWithFilterCriteria). Do NOT remove+push: get_tasks
			// returns children ordered by the list "Order by", which would scramble
			// name-ordered nested subtasks on expand.
			if(parent.subtasksIds.indexOf(task.id) === -1){
				parent.subtasksIds.push(task.id);
			}
		}		
	}
		
	return task;
};

ogTasksCache.getTask = function(id){
	return this.Tasks[id];
};

ogTasksCache.removeTask = function(task){

	//parent
	if(task.parentId > 0){
		var parent = ogTasksCache.getTask(task.parentId);

		if(typeof parent != "undefined" && parent.subtasksIds.indexOf(task.id) != -1){
			parent.subtasksIds.splice(parent.subtasksIds.indexOf(task.id), 1);
		}
	}

	delete ogTasksCache.Tasks[task.id];
};