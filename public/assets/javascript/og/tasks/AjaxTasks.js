// reset pagination offset and groups per page
ogTasks.resetPaginationVariables = function() {

	// Save view state (collapsed groups, scroll position, expanded subtasks, task counts) so
	// it can be restored after reload. Also capture the total number of groups that were loaded
	// so we can reload that exact count in a single request instead of fetching all groups.
	ogTasks.savedGroupCollapsedState = {};
	ogTasks.savedExpandedSubtasks = {};
	ogTasks.savedGroupTasksLoaded = {};
	ogTasks.restoreStateOnLoad = false;
	ogTasks.savedScrollTop = 0;
	var previousGroupsCount = ogTasks.Groups.length;
	for (var i = 0; i < ogTasks.Groups.length; i++) {
		var g = ogTasks.Groups[i];
		if (g.alltasks_collapsed) {
			ogTasks.savedGroupCollapsedState[g.group_id] = true;
			ogTasks.restoreStateOnLoad = true;
		}
		ogTasks.savedGroupTasksLoaded[g.group_id] = g.total_tasks_loaded;
	}
	// Save tasks whose lazily-loaded subtasks were expanded.
	for (var tid in ogTasksCache.Tasks) {
		var t = ogTasksCache.Tasks[tid];
		if (t.isExpanded && t.toggleSubtasksShow && t.divInfo && t.divInfo.length > 0) {
			var groupIds = [];
			for (var k = 0; k < t.divInfo.length; k++) {
				var gid = t.divInfo[k].group_id;
				if (groupIds.indexOf(gid) === -1) groupIds.push(gid);
			}
			if (groupIds.length > 0) {
				ogTasks.savedExpandedSubtasks[t.id] = groupIds;
				ogTasks.restoreStateOnLoad = true;
			}
		}
	}
	// Use lastScrollTop (set by scroll listener) rather than reading from the DOM directly,
	// because the panel may already be hidden at this point and scrollTop would read as 0.
	if (ogTasks.lastScrollTop > 0) {
		ogTasks.savedScrollTop = ogTasks.lastScrollTop;
		ogTasks.restoreStateOnLoad = true;
	}
	ogTasks.lastScrollTop = 0;

	ogTasks.Groups.length = 0;

	ogTasks.allGroupsLoaded = false;
	ogTasks.totalGroupsCount = 0;

	ogTasks.groupsPaginationOffset = 0;

	// Reset manual loading flag so scroll auto-loading is re-enabled after a filter change
	ogTasks.manualGroupLoading = false;

	if (ogTasks.userPreferences.showTasksListAsGantt) {
		ogTasks.groupsPaginationCount = 1000;
	} else if (ogTasks.restoreStateOnLoad) {
		// Reload exactly as many groups as were previously loaded in a single request
		// so their collapsed/expanded state is restored without fetching unloaded groups.
		ogTasks.groupsPaginationCount = Math.max(previousGroupsCount, ogTasks.userPreferences.groupsPaginationCount);
	} else {
		ogTasks.groupsPaginationCount = ogTasks.userPreferences.groupsPaginationCount;
	}
}

// Called when a task name link is clicked. Saves view state before the DOM is replaced
// so it can be restored when the panel comes back without a full getGroups() reload.
ogTasks.onTaskLinkClick = function(taskId) {
	ogTasks.viewingTaskId = taskId;
	// Remember the current context when entering the task, so see if it has changed when returning to the list.
	ogTasks.previousContext = og.contextManager.plainContext();
	// Scroll position — lastScrollTop is maintained by the scroll listener and is valid here.
	ogTasks.savedScrollTop = (ogTasks.lastScrollTop > 0) ? ogTasks.lastScrollTop : 0;
	// Expanded subtasks — capture now while task objects are still in their current state.
	ogTasks.savedExpandedSubtasks = {};
	for (var tid in ogTasksCache.Tasks) {
		var t = ogTasksCache.Tasks[tid];
		if (t.isExpanded && t.toggleSubtasksShow && t.divInfo && t.divInfo.length > 0) {
			var groupIds = [];
			for (var k = 0; k < t.divInfo.length; k++) {
				var gid = t.divInfo[k].group_id;
				if (groupIds.indexOf(gid) === -1) groupIds.push(gid);
			}
			if (groupIds.length > 0) {
				ogTasks.savedExpandedSubtasks[t.id] = groupIds;
			}
		}
	}
};

// load the next page of groups
ogTasks.loadMoreGroups = function() {
	if (!ogTasks.allGroupsLoaded && !ogTasks.isLoadingGroups) {
		// Capture collapse state before new groups are added
		var shouldCollapse = ogTasks.areAllGroupsCollapsed();
		if (shouldCollapse) {
			var listenerId = og.eventManager.addListener('after ogTasks.Groups list completely loaded', function () {
				og.eventManager.removeListener(listenerId);
				ogTasks.collapseAllLoadedGroups();
			});
		}
		// load the groups
		ogTasks.getGroups();
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
	} else if (ogTasks.restoreStateOnLoad) {
		// When restoring state, use the highest saved task count as the per-group limit so that
		// all groups are restored to their previous task count in this single request.
		var maxSaved = 0;
		for (var gid in ogTasks.savedGroupTasksLoaded) {
			if (ogTasks.savedGroupTasksLoaded[gid] > maxSaved) {
				maxSaved = ogTasks.savedGroupTasksLoaded[gid];
			}
		}
		// Only override the server default when the saved count is larger than the
		// default per-group limit.  If it is smaller (e.g. a member filter was active
		// and only 2 tasks matched), using it would cap the unfiltered reload to that
		// reduced count instead of falling back to the user's normal default.
		if (maxSaved > parseInt(og.noOfTasks, 10)) {
			filters.limit = maxSaved;
		}
	}

	if (typeof(ogTasks.groupsPaginationOffset) != 'undefined') {
		filters.groups_offset = ogTasks.groupsPaginationOffset;
	}
	if (typeof(ogTasks.groupsPaginationCount) != 'undefined') {
		filters.groups_count = ogTasks.groupsPaginationCount;
	}
	
	ogTasks.isLoadingGroups = true;
	ogTasks.updateGroupPaginationButtons();

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
					} else if (typeof ogTasks.groupsPaginationCount !== 'undefined' &&
					           data.groups.length < parseInt(ogTasks.groupsPaginationCount)) {
						// Partial page: fewer groups than requested means this is the last page
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

				if (data.total_groups_count !== undefined) {
					ogTasks.totalGroupsCount = data.total_groups_count;
				}

				ogTasks.Groups.loaded = true;
				ogTasks.Groups.loadedAt = Date.now();

				//fire event
				og.eventManager.fireEvent('after ogTasks.Groups list completely loaded', null);
				
				ogTasks.draw();
			}
	});
};

ogTasks.showAllTasks = function(group_id) {
	var group = ogTasks.getGroup(group_id);
	if (!group) return;

	if (group.alltasks_collapsed) {
		ogTasks.expandCollapseAllTasksGroup(group_id);
	}

	var BATCH_SIZE = 100;

	function loadNextBatch() {
		var grp = ogTasks.getGroup(group_id);
		if (!grp || grp.offset >= grp.root_total) return;

		var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
		if (!bottomToolbar) return;

		var filters = bottomToolbar.getFilters();
		if (bottomToolbar.groupcombo) filters.tasksGroupBy = bottomToolbar.groupcombo.value;
		if (bottomToolbar.ordercombo) filters.tasksOrderBy = bottomToolbar.ordercombo.value;

		filters.start = grp.offset;
		filters.limit = BATCH_SIZE;
		filters.groupId = grp.group_id;

		var offsetBefore = grp.offset;
		og.openLink(og.getUrl('task', 'get_tasks_groups_list'), {
			hideLoading: true,
			post: filters,
			callback: function(success, data) {
				if (!success) return;
				ogTasks.updateTaskGroups(data, true);
				og.eventManager.fireEvent('replace all empty breadcrumb', null);
				// Guard: if the server omitted the group, offset won't have advanced
				// and the next call would repeat the same request indefinitely.
				var grpAfter = ogTasks.getGroup(group_id);
				if (grpAfter && grpAfter.offset > offsetBefore) {
					loadNextBatch();
				}
			}
		});
	}

	// Find the customer_project dimension and preload all its members into
	// og.dimensions before batching, so breadcrumbs render on first draw
	// instead of going through the lazy empty-breadcrumb cycle.
	var projectsDimId = null;
	if (og.dimensions_info) {
		for (var did in og.dimensions_info) {
			if (!isNaN(did) && og.dimensions_info[did] && og.dimensions_info[did].code === 'customer_project') {
				projectsDimId = parseInt(did, 10);
				break;
			}
		}
	}

	if (projectsDimId && !ogMemberCache.areDimRootMembersLoaded(projectsDimId)) {
		og.openLink(og.getUrl('dimension', 'initial_list_dimension_members_tree', {
			dimension_id: projectsDimId,
			limit: 5000
		}), {
			hideLoading: true,
			callback: function(success, data) {
				if (data && data.dimension_members) {
					(function walk(nodes) {
						if (!nodes) return;
						for (var i = 0; i < nodes.length; i++) {
							var n = nodes[i];
							if (n && n.id) og.addMemberToOgDimensions(projectsDimId, n);
							if (n && n.children && n.children.length) walk(n.children);
						}
					}(data.dimension_members));
					ogMemberCache.addDimToDimRootMembers(projectsDimId);
				}
				loadNextBatch();
			}
		});
	} else {
		loadNextBatch();
	}
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

	// Ensure the group is expanded so the additional tasks will be visible when loaded.
	if (group.alltasks_collapsed) {
		ogTasks.expandCollapseAllTasksGroup(group_id);
	}
	
	filters.start = group.offset;	
	
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
                    if (s && data && data.tasks) {
                        for (var j = 0; j < data.tasks.length; j++) {
                            var task_data = data.tasks[j];
                            ogTasksCache.addTasks(task_data);
                        }
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
	var key = ogTasks._subtasksLoadKey(task.id, groupId);
	if (ogTasks._subtasksLoadInFlight[key]) {
		return;
	}
	if (!task.subtasksIds || task.subtasksIds.length === 0) {
		return;
	}
	ogTasks._subtasksLoadInFlight[key] = true;
	og.getTasksFromServer(task.subtasksIds, ogTasks.drawSubtasks, {
		task_id: task.id,
		group_id: groupId,
		_loadKey: key
	});
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
