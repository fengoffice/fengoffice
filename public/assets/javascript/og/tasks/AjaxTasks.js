// reset pagination offset and groups per page
ogTasks.resetPaginationVariables = function() {
	
	ogTasks.Groups.length = 0;
	
	ogTasks.allGroupsLoaded = false;
	
	ogTasks.groupsPaginationOffset = 0;
	
	if (ogTasks.userPreferences.showTasksListAsGantt) {
		ogTasks.groupsPaginationCount = 1000;
	} else {
		ogTasks.groupsPaginationCount = ogTasks.userPreferences.groupsPaginationCount;
	}
}

// load the next page of groups
ogTasks.loadMoreGroups = function() {
	if (!ogTasks.allGroupsLoaded && !ogTasks.isLoadingGroups) {
		
		// load the groups
		ogTasks.getGroups();
		// remove pagination link
		//$("#tasksPanelGroupsPagination").remove();
	}
}

//get all groups from server with a few tasks in each one and draw them
ogTasks.getGroups = function(dont_reset_groups){
	var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
	if (!bottomToolbar) return;
	var filters = bottomToolbar.getFilters();
	
	if(bottomToolbar.groupcombo){
		filters.tasksGroupBy = bottomToolbar.groupcombo.value;
	}	
	if(bottomToolbar.ordercombo){
		filters.tasksOrderBy = bottomToolbar.ordercombo.value;
	}
	
	//for gantt we load all tasks untill we have ajax support for gantt
	if (typeof ogTasks.userPreferences.showTasksListAsGantt != 'undefined' && ogTasks.userPreferences.showTasksListAsGantt) {
		filters.limit = 500;
	}

	if (typeof(ogTasks.groupsPaginationOffset) != 'undefined') {
		filters.groups_offset = ogTasks.groupsPaginationOffset;
	}
	if (typeof(ogTasks.groupsPaginationCount) != 'undefined') {
		filters.groups_count = ogTasks.groupsPaginationCount;
	}
	
	ogTasks.isLoadingGroups = true;
	
	og.openLink(og.getUrl('task', 'get_tasks_groups_list'), {
			hideLoading: false,
			scope: this,
			post: filters,
			callback: function(success, data) {
				
				if (data.groups) {
					if (data.groups.length == 0) {
						ogTasks.isLoadingGroups = false;
						ogTasks.Groups.loaded = true;
						ogTasks.allGroupsLoaded = true;
					}
					for (var i = 0; i < data.groups.length; i++){
						ogTasks.removeTaskGroup(data.groups[i]);
						ogTasks.addNewTaskGroup(data, i);
					}
				}
				
				// update groups pagination offset
				if (typeof(ogTasks.groupsPaginationOffset) != 'undefined') {
					ogTasks.groupsPaginationOffset = parseInt(data.new_groups_offset) + parseInt(ogTasks.groupsPaginationCount);
				}
				
				ogTasks.Groups.loaded = true; 
				
				//fire event
				og.eventManager.fireEvent('after ogTasks.Groups list completely loaded', null);
				
				ogTasks.draw();
			}
	});
};

ogTasks.showAllTasks = function(group_id){
	ogTasks.showMoreTasks(group_id, true);
};

ogTasks.showMoreTasks = function(group_id, show_all){
	var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
	if (!bottomToolbar) return;
	var filters = bottomToolbar.getFilters();
	if(bottomToolbar.groupcombo){
		filters.tasksGroupBy = bottomToolbar.groupcombo.value;
	}	
	if(bottomToolbar.ordercombo){
		filters.tasksOrderBy = bottomToolbar.ordercombo.value;
	}
	
	var group = ogTasks.getGroup(group_id);
	
	filters.start = group.offset;	
	
	group.offset = group.offset + parseInt(og.noOfTasks);
	
	if (typeof show_all == "undefined") {
		show_all = false;		
	}
	
	if(show_all){
		filters.limit = group.root_total;
	}	
	
	filters.groupId = group.group_id;
	og.openLink(og.getUrl('task', 'get_tasks_groups_list'), {
			hideLoading: true,
			scope: this,
			post: filters,
			callback: function(success, data) {
				ogTasks.updateTaskGroups(data, true);
				
				og.eventManager.fireEvent('replace all empty breadcrumb', null);
			}
	});
};

og.getTasksFromServer = function (tasks_ids, func_callback, callback_extra_params) {
        if (tasks_ids.length > 0) {
            og.openLink(og.getUrl('task', 'get_tasks', {tasks_ids: Ext.encode(tasks_ids)}), {
                hideLoading: true,
                callback: function (s, data) {
                    for (var j = 0; j < data.tasks.length; j++) {
                        var task_data = data.tasks[j];
                        var task = ogTasksCache.addTasks(task_data);
                    }

                    //execute the callback function 
                    if (typeof callback_extra_params == "undefined") {
                        callback_extra_params = {};
                    }

                    if (typeof func_callback != "undefined") {
                        func_callback(callback_extra_params);
                    }
                }
            })
        }
    };

og.getSubTasksAndDraw = function(task, groupId){		
	og.getTasksFromServer(task.subtasksIds, ogTasks.drawSubtasks, {task_id:task.id, group_id:groupId});
}

ogTasks.refreshGroupsTotals = function(group_id){
	if(this.Groups.length==0) {
		return;
	};
	
	var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
	if (!bottomToolbar) return;
	var filters = bottomToolbar.getFilters();
	if(bottomToolbar.groupcombo){
		filters.tasksGroupBy = bottomToolbar.groupcombo.value;
	}	
	if(bottomToolbar.ordercombo){
		filters.tasksOrderBy = bottomToolbar.ordercombo.value;
	}		
	
	filters.start = 0;	
	filters.limit = 0;
	
	if (typeof group_id != 'undefined'){
		filters.groupId = group_id;
	}	
	
	filters.only_totals = 1; // dont load all tasks if only refreshing the totals
	
	og.openLink(og.getUrl('task', 'get_tasks_groups_list'), {
			hideLoading: true,
			scope: this,
			post: filters,
			callback: function(success, data) {
				
				ogTasks.updateTaskGroups(data);	
								
				og.eventManager.fireEvent('replace all empty breadcrumb', null);
			}
	});
};


ogTasks.getGroupsForTask = function(task_id){
	var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
	if (!bottomToolbar) return;
	var filters = bottomToolbar.getFilters();
	filters.taskId = task_id;
	og.openLink(og.getUrl('task', 'get_groups_for_task'), {
				hideLoading: true,
				scope: this,
				post: filters,
				callback: function(success, data) {
					ogTasks.updateTaskGroupsForTask(data);								
				}
	});
};
