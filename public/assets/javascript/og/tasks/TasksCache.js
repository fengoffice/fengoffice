//wraper to store tasks on ogTasks.Tasks
ogTasksCache = {
		'Tasks' : {}
};

ogTasksCache.addTasks = function(task_data){
	var task = new ogTasksTask();
	task.setFromTdata(task_data);
		
	this.Tasks[task.id] = task;
	
	//parent
	if(task.parentId > 0){
		var parent = ogTasksCache.getTask(task.parentId);
		if (typeof parent != "undefined") {
			if(parent.subtasksIds.indexOf(task.id) == -1){
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
		if(parent.subtasksIds.indexOf(task.id) != -1){
			parent.subtasksIds.splice(parent.subtasksIds.indexOf(task.id), 1);
		}
	}
	
	delete ogTasksCache.Tasks.id;
};