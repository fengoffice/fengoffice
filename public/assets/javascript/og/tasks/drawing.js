/**
 * drawing.js
 *
 * This module holds the rendering logic for groups and tasks
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */

//************************************
//*		Main function
//************************************

ogTasks.draw = function () {
    //first load the groups from server
    if (!ogTasks.Groups.loaded) {
    	ogTasks.resetPaginationVariables();
        ogTasks.getGroups();
        return;
    }
    ogTasks.Groups.loaded = false;
    ogTasks.LevelMultiplier = 20;

    if (typeof ogTasks.userPreferences.showTasksListAsGantt != 'undefined' && ogTasks.userPreferences.showTasksListAsGantt) {
        return;
    }

    var start = new Date();

    for (var i = 0; i < this.Tasks.length; i++)
        this.Tasks[i].divInfo = [];

    var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
    var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');

    if (!bottomToolbar || !topToolbar) return;

    var displayCriteria = bottomToolbar.getDisplayCriteria();
    var drawOptions = topToolbar.getDrawOptions();

    //Drawing
    var sb = new StringBuffer();
    var header_html = ogTasks.newTaskFormTopList();
    if ($("#ogTasksPanelColNamesThead").length == 0) {
	    sb.append(header_html);
    }
    //Draw all groups
    var first_group_to_draw_index = -1;
    for (var i = 0; i < this.Groups.length; i++) {
    	var gr_html = this.drawGroup(displayCriteria, drawOptions, this.Groups[i]);
        if (gr_html) {
        	if (first_group_to_draw_index == -1) {
        		first_group_to_draw_index = i;
        	}
        	sb.append(gr_html);
        }
    }

    //Message
    if (this.Groups.length == 0 && ogTasks.groupsPaginationOffset == 0) {
        var context_names = og.contextManager.getActiveContextNames();
        if (context_names.length == 0) context_names.push(lang('all'));

        sb.append('<tr id="no_tasks_info"><td colspan="10">' +
            '<div class="inner-message">' + lang('no tasks to display', '"' + context_names.join('", "') + '"') + '</div>' +
            '</td></tr>');
    }
    if (this.Groups.length == 0) {
    	ogTasks.allGroupsLoaded = true;
    }
    /*
    if (this.Groups.length >= ogTasks.groupsPaginationCount) {
    	sb.append('</div><div id="tasksPanelGroupsPagination" class="tasks-group-pagination-link-container">' +
    			'<a href="#" onclick="ogTasks.loadMoreGroups();return false;">' +
    			lang('load more task groups') +
    			'</a></div>');
    }*/

    var container = document.getElementById('tasksPanelContainer');
    if (container) {
    	if (ogTasks.groupsPaginationOffset == 0) {
    		container.innerHTML = '';
    	}
        container.innerHTML += sb.toString();
    }
    ogTasks.initColResize();
    if (this.Groups.length != 0) {
        ogTasks.drawAllGroupsTasks(first_group_to_draw_index);
    }

    ogTasks.initDragDrop();
}

ogTasks.initColResize = function () {
    $("#tasksPanelContainer").colResizable({disable: true});//remove previous colResize
    $("#tasksPanelContainer").colResizable({
        fixed: false,
        minWidth: 50,
        postbackSafe: true,
        disable: false
    });
}
//ogTasks.toggleSubtasksShow = false;
ogTasks.toggleSubtasks = function (taskId, groupId, not_expand) {
    
    var expander = document.getElementById('ogTasksPanelFixedExpanderT' + taskId + 'G' + groupId);
    var task = this.getTask(taskId);
    if ($("[data-level!='1'][data-parent-id='" + taskId + "']").length) {
        
        task.isExpanded = !task.isExpanded;
        if (task.isExpanded && not_expand != true) {
            $("[data-parent-id='" + taskId + "']").show();
        } else {
            $("[data-parent-id='" + taskId + "']").hide();
            if (task.subtasksIds && task.subtasksIds.length > 0) {
                task.subtasksIds.forEach(function (value) {
                    ogTasks.toggleSubtasks(value, groupId, true);
                });
            }
        }
    } else {
        if (task.subtasksIds.length > 0 && not_expand != true && !task.toggleSubtasksShow) {
            task.isExpanded = !task.isExpanded;
            og.getSubTasksAndDraw(task, groupId);
            task.toggleSubtasksShow = true;
        }
    }
    if (expander) {
        expander.className = "og-task-expander " + ((task.isExpanded) ? 'toggle_expanded' : 'toggle_collapsed');
    }
}

ogTasks.loadAllDescriptions = function (task_ids) {
    ogTasks.all_descriptions_loaded = false;
    og.openLink(og.getUrl('task', 'get_task_descriptions'), {
        hideLoading: true,
        scope: this,
        method: 'POST',
        post: {ids: task_ids.join(',')},
        callback: function (success, data) {
            for (i = 0; i < ogTasks.Tasks.length; i++) {
                var task = ogTasks.Tasks[i];
                if (data.descriptions['t' + task.id]) {
                    task.description = data.descriptions['t' + task.id];
                }
            }
            ogTasks.all_descriptions_loaded = true;
        }
    });
}

//************************************
//*		Draw group
//************************************
ogTasks.drawGroup = function (displayCriteria, drawOptions, group) {
	if ($("#ogTasksPanelGroup" + group.group_id).length > 0) return '';
    group.view = [];
    if (displayCriteria.group_by == 'milestone') {
        var sb = new StringBuffer();
        var milestone = this.getMilestone(group.group_id);
        if (milestone) {
            if (milestone.isUrgent) {
                group.group_icon = 'ico-urgent-milestone';
            }

            if (milestone.completedById) {
                var user = this.getUser(milestone.completedById, true);
                var tooltip = '';
                if (user) {
                    var time = new Date(milestone.completedOn * 1000);
                    var now = new Date();
                    var timeFormatted = time.getYear() != now.getYear() ? time.dateFormat('M j, Y') : time.dateFormat('M j');
                    tooltip = lang('completed by name on', og.clean(user.name), timeFormatted).replace(/'\''/g, '\\\'');
                }
                group.group_name = "<a href='#' style='text-decoration:line-through' class='internalLink' onclick='og.openLink(\"" + og.getUrl('milestone', 'view', {id: group.group_id}) + "\")' title='" + tooltip + "'>" + og.clean(group.group_name) + '</a>';
            } else {
                group.group_name = "<a href='#' class='internalLink' onclick='og.openLink(\"" + og.getUrl('milestone', 'view', {id: group.group_id}) + "\")'>" + og.clean(group.group_name) + '</a>';
            }

            //Due date
            var date = new Date();
            date.setTime((milestone.dueDate + date.getTimezoneOffset() * 60) * 1000);
            var now = new Date();
            var dateFormatted = date.getYear() != now.getYear() ? date.dateFormat('M j, Y') : date.dateFormat('M j');

            css_class = "milestone-date";
            if (milestone.completedById > 0) {
                css_class = "milestone-date milestone-completed";
            } else {
                if ((date < now))
                    css_class = "milestone-date milestone-delayed";
            }
            group.view.push({
                id: 'ogTasksPanelMileGroupDate' + group.group_id,
                text: lang('due') + ':&nbsp;' + dateFormatted,
                css_class: css_class
            });

            //Percent complete
            group.view.push({
                id: 'ogTasksPanelCompleteBar' + group.group_id,
                text: this.drawMilestoneCompleteBar(group),
                css_class: "group-milestone-complete-bar"
            });

        }
    }

    //Member Path
    var mem_path = "";
    var mpath = Ext.util.JSON.decode(group.group_memPath);
    if (mpath) mem_path = og.getEmptyCrumbHtml(mpath, ".task-breadcrumb-container");

    //get template for the row
    var source = $("#task-list-group-template").html();
    //compile the template
    var template = Handlebars.compile(source);

    //template data
    var data = {
        group: group,
        mem_path: mem_path,
        cols_total: ogTasks.TasksList.tasks_list_cols.length
    }

    //instantiate the template
    var html = template(data);

    html = html + ogTasks.newTaskGroupTotals(group);

    return html;
}

ogTasks.newTaskGroupTotals = function (group) {
    //get template for the row
    var source = $("#task-list-group-totals-template").html();
    //compile the template
    var template = Handlebars.compile(source);

    var total_cols = [];

    for (var i = 0; i < ogTasks.TasksList.tasks_list_cols.length; i++) {
        if (ogTasks.TasksList.tasks_list_cols[i].id == "task_name") {
            var total = lang('total') + ':';
        } else {
            var total = group[ogTasks.TasksList.tasks_list_cols[i].group_total_field];
        }

        total_cols.push({id: ogTasks.TasksList.tasks_list_cols[i].id, text: total});
    }

    var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
    var drawOptions = topToolbar.getDrawOptions();
    drawOptions.groupId = group.group_id;

    if (group.total_tasks_loaded < group.root_total) {
        drawOptions.showMore = true;
    } else {
        drawOptions.showMore = false;
    }

    //template data
    var data = {
        draw_options: drawOptions,
        group: group,
        total_cols: total_cols
    }
    if (ogTasks.additional_task_list_columns) {
        data.additional_task_list_columns = ogTasks.additional_task_list_columns;
    }

    //instantiate the template
    var html = template(data);

    return html;
}


ogTasks.drawMilestoneCompleteBar = function (group) {
    var html = '';
    var milestone = this.getMilestone(group.group_id);
    if (!milestone) return html;
    var complete = 0;
    var completedTasks = parseInt(milestone.completedTasks);
    var totalTasks = parseInt(milestone.totalTasks);
    var tasks = this.flattenTasks(group.group_tasks);
    for (var i = 0; i < tasks.length; i++) {
        var t = tasks[i];
        if (t.milestoneId == group.group_id) {
            completedTasks += (t.status == 1 && (t.statusOnCreate == 0)) ? parseInt(1) : parseInt(0);
            completedTasks -= (t.status == 0 && (t.statusOnCreate == 1)) ? parseInt(1) : parseInt(0);
            totalTasks = (t.isCreatedClientSide) ? totalTasks + parseInt(1) : totalTasks + parseInt(0);
        }
    }
    if (totalTasks > 0)
        complete = ((100 * completedTasks) / totalTasks);
    html += "<table><tr><td style='padding-left:15px;padding-top:5px'>" +
        "<table style='height:7px;width:50px'><tr><td style='height:7px;width:" + (complete) + "%;background-color:#6C2'></td><td style='width:" + (100 - complete) + "%;background-color:#DDD'></td></tr></table>" +
        "</td><td style='padding-left:3px;line-height:12px'><span style='font-size:8px;color:#AAA'>(" + completedTasks + '/' + totalTasks + ")</span></td></tr></table>";

    return html;
}

ogTasks.minutesToHoursAndMinutes = function (minutes) {
    var total_estimate_split = Math.round(minutes * 100 / 60) / 100;
    var total_estimate = (total_estimate_split + '').split(".");
    var hours_estimate = total_estimate[0] + " " + lang('hours');
    var minutes_estimate = "";
    if (total_estimate[1]) {
        if (total_estimate[1].length == 1) {
            minutes_estimate = ", " + Math.round(((total_estimate[1] * 60) / 10)) + " " + lang('minutes');
        } else {
            minutes_estimate = ", " + Math.round(((total_estimate[1] * 60) / 100)) + " " + lang('minutes');
        }
        var format_total_estimate = hours_estimate + minutes_estimate;
    } else {
        var format_total_estimate = hours_estimate;
    }
    return format_total_estimate;
}

ogTasks.drawGroupActions = function (group) {
    var html = '<a id="ogTasksPanelGroupSoloOn' + group.group_id + '" style="margin-right:15px;display:' + (group.solo ? "none" : "inline") + '" href="#" class="internalLink" onClick="ogTasks.hideShowGroups(\'' + group.group_id + '\')" title="' + lang('hide other groups') + '">' + (lang('hide others')) + '</a>' +
        '<a id="ogTasksPanelGroupSoloOff' + group.group_id + '" style="display:' + (group.solo ? "inline" : "none") + ';margin-right:15px;" href="#" class="internalLink" onClick="ogTasks.hideShowGroups(\'' + group.group_id + '\')" title="' + lang('show all groups') + '">' + (lang('show all')) + '</a>' +
        '<a href="#" class="internalLink ogTasksGroupAction ico-print" style="margin-right:15px;" onClick="ogTasks.printGroup(\'' + group.group_id + '\')" title="' + lang('print this group') + '">' + (lang('print')) + '</a>';
    if (ogTasks.userPermissions.can_add) {
        html += '<a href="#" class="internalLink ogTasksGroupAction ico-add" onClick="ogTasks.drawAddNewTaskForm(\'' + group.group_id + '\')" title="' + lang('add a new task to this group') + '">' + (lang('add task')) + '</a>';
    }
    return html;
}


ogTasks.hideShowGroups = function (group_id) {
    var group = this.getGroup(group_id);
    if (group) {
        var soloOn = document.getElementById('ogTasksPanelGroupSoloOn' + group_id);
        var soloOff = document.getElementById('ogTasksPanelGroupSoloOff' + group_id);
        group.solo = !group.solo;

        soloOn.style.display = group.solo ? 'none' : 'inline';
        soloOff.style.display = group.solo ? 'inline' : 'none';

        for (var i = 0; i < this.Groups.length; i++) {
            if (this.Groups[i].group_id != group_id) {
                var groupEl = document.getElementById('ogTasksPanelGroup' + this.Groups[i].group_id);
                if (groupEl)
                    groupEl.style.display = group.solo ? 'none' : 'block';
            }
        }

        if (group.solo)
            this.expandGroup(group_id);
        else
            this.collapseGroup(group_id);
    }
}


ogTasks.expandGroup = function (group_id) {
    var div = document.getElementById('ogTasksGroupExpandTasks' + group_id);
    var divLink = document.getElementById('ogTasksGroupExpandTasksTitle' + group_id);
    if (div) {
        var group = this.getGroup(group_id);
        group.isExpanded = true;
        var html = '';
        var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
        var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
        var displayCriteria = bottomToolbar.getDisplayCriteria();
        var drawOptions = topToolbar.getDrawOptions();
        for (var i = og.noOfTasks; i < group.group_tasks.length; i++)
            html += this.drawTask(group.group_tasks[i], drawOptions, displayCriteria, group.group_id, 1);
        div.innerHTML = html;
        divLink.style.display = 'none';
        ogTasks.expandedGroups.push(group.group_id);

        //init action btns
        var btns = $("#ogTasksGroupExpandTasks" + group_id + " .tasksActionsBtn").toArray();
        og.initPopoverBtns(btns);

        //init breadcrumbs
        og.eventManager.fireEvent('replace all empty breadcrumb', null);

        /*		if (drawOptions.show_workspaces)
                    og.showWsPaths('ogTasksGroupExpandTasks' + group_id);*/
    }
}


ogTasks.collapseGroup = function (group_id) {
    var div = document.getElementById('ogTasksGroupExpandTasks' + group_id);
    var divLink = document.getElementById('ogTasksGroupExpandTasksTitle' + group_id);
    if (div) {
        var group = this.getGroup(group_id);
        group.isExpanded = false;
        div.innerHTML = '';
        divLink.style.display = 'block';
    }
}

ogTasks.expandCollapseAllTasksGroup = function (group_id) {
    var group = this.getGroup(group_id);
    if (group) {
        var expander = document.getElementById('ogTasksPanelGroupExpanderG' + group_id);
        if (group.alltasks_collapsed) {
            group.alltasks_collapsed = false;
            if (expander) expander.className = 'og-task-expander toggle_expanded';
        } else {
            group.alltasks_collapsed = true;
            if (expander) expander.className = 'og-task-expander toggle_collapsed';
        }

        $("#ogTasksPanelGroup" + group.group_id + " .task-list-row").slideToggle();
    }
}


ogTasks.drawAddTask = function (id_subtask, group_id, level) {
    //Draw indentation
    // FIXME: quick add task
    var padding = (15 * (level + 1)) + ogTasks.LevelMultiplier;
    return '<div class="ogTasksTaskRow" style="padding-left:' + padding + 'px">' +
        '</div>';

}


//************************************
//*		Draw task
//************************************
ogTasks.drawGroupNextTask = function (group, drawOptions, displayCriteria) {
    var task_id = group.group_tasks_order[group.task_interval_iteration];
    ogTasks.drawTask(group.group_tasks[task_id], drawOptions, displayCriteria, group.group_id, 1);

    //start all clocks on the list
    var clocks = $(".og-timeslot-work-started span");

    for (i = 0; i < clocks.length; i++) {
        var clockId = clocks[i].id;
        clockId = clockId.replace("timespan", "");
        var user_start_time = parseInt($("#" + clockId + "user_start_time").val());

        og.startClock(clockId, user_start_time);
    }


    $("#tasksPanel"+og.genid).parent().css('overflow', 'hidden');

    var btns = $(".tasksActionsBtn").toArray();
    og.initPopoverBtns(btns);


    ++group.task_interval_iteration;
    if (group.task_interval_iteration == group.group_tasks_order.length) {
        clearInterval(group.task_interval);

        ++ogTasks.Groups.group_interval_iteration;
        var group = ogTasks.Groups[ogTasks.Groups.group_interval_iteration];

        //check if is the last group
        if (typeof group != 'undefined') {
            ogTasks.drawGroupTasks(group);
        } else {
            //if is the last task of the last group
            if (ogTasks.Groups.group_interval_iteration == ogTasks.Groups.length) {
                ogTasks.initColResize();
                og.eventManager.fireEvent('replace all empty breadcrumb', null);
				ogTasks.isLoadingGroups = false;
				// if there is no scrollbar and there are more task groups to load -> load them
				var task_content_div = $("#tasksPanelContent").get(0);
				if (!ogTasks.allGroupsLoaded && task_content_div && task_content_div.scrollHeight == task_content_div.clientHeight) {
					ogTasks.loadMoreGroups();
				}
            }
        }
    }
}

ogTasks.drawGroupTasks = function (group) {
    var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
    var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');

    var displayCriteria = bottomToolbar.getDisplayCriteria();
    var drawOptions = topToolbar.getDrawOptions();
    group.isExpanded = ogTasks.expandedGroups.indexOf(group.group_id) > -1;

    group.task_interval_iteration = 0;
    group.task_interval = setInterval(function () {
        ogTasks.drawGroupNextTask(group, drawOptions, displayCriteria)
    }, 50);
};

ogTasks.drawAllGroupsTasks = function (first_group_to_draw_index) {
    og.loading();

    if (!first_group_to_draw_index || first_group_to_draw_index < 0) {
    	ogTasks.Groups.group_interval_iteration = 0;
    } else {
    	ogTasks.Groups.group_interval_iteration = first_group_to_draw_index;
    }

    var group = ogTasks.Groups[ogTasks.Groups.group_interval_iteration];

    ogTasks.drawGroupTasks(group);

    og.hideLoading();
};

ogTasks.drawTask = function (task, drawOptions, displayCriteria, group_id, level, target, returnHtml) {
    if (!task) return;
    //Draw indentation
    var pos = (task.divInfo && !isNaN(task.divInfo.length)) ? task.divInfo.length : 0;
    task.divInfo[pos] = {
        group_id: group_id,
        drawOptions: drawOptions,
        displayCriteria: displayCriteria,
        group_id: group_id,
        level: level
    };

    var html = this.drawTaskRow(task, drawOptions, displayCriteria, group_id, level, returnHtml);

    if (typeof returnHtml != 'undefined') {
        return html;
    } else if (typeof target != 'undefined') {
        $(target).append(html);
    } else {
        $("#ogTasksPanelGroup" + group_id).append(html);
    }
}

ogTasks.removeTaskFromView = function (task) {
    $("[id^='ogTasksPanelTask" + task.id + "']").each(function (index) {
        $(this).remove();
    });

    //parent
    if (task.parentId > 0) {
        var parent = ogTasksCache.getTask(task.parentId);
        if (typeof parent != "undefined" && parent.subtasksIds.length == 0) {
            ogTasks.reDrawTask(parent);
        }
    }
}

ogTasks.reDrawTask = function (task) {
    //ogTasks.drawTask
    var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
    var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
    var displayCriteria = bottomToolbar.getDisplayCriteria();
    var drawOptions = topToolbar.getDrawOptions();

    //parent
    if (drawOptions.show_subtasks_structure) {
        if (task.parentId > 0) {
            var parent = ogTasksCache.getTask(task.parentId);
            if (typeof parent != 'undefined') {
                //if is not rendered and is subtask?
                $("[id^='ogTasksPanelSubtasksT" + parent.id + "']").each(function (index) {
                    var group_id = $(this).attr('id');
                    var remove = "ogTasksPanelSubtasksT" + task.id + "G";
                    group_id = group_id.replace(remove, "");

                    if ($("#ogTasksPanelTask" + task.id + "G" + group_id).length == 0) {
                        var html = ogTasks.drawTask(task, drawOptions, displayCriteria, group_id, $(this).attr("data-level"), null, true);
                        $(this).append(html);
                        //init action btns
                        var btns = $("#ogTasksPanelTask" + task.id + "G" + group_id + " .tasksActionsBtn").toArray();
                        og.initPopoverBtns(btns);
                    }
                });
                //update parent task
                $("[id^='ogTasksPanelTask" + parent.id + "']").each(function (index) {
                    var group_id = $(this).attr('id');
                    var remove = "ogTasksPanelTask" + parent.id + "G";
                    group_id = group_id.replace(remove, "");

                    var html = ogTasks.drawTask(parent, drawOptions, displayCriteria, group_id, $(this).attr("data-level"), null, true);
                    $("#ogTasksPanelTask" + parent.id + "G" + group_id).replaceWith(html);
                    //init action btns
                    var btns = $("#ogTasksPanelTask" + parent.id + "G" + group_id + " .tasksActionsBtn").toArray();
                    og.initPopoverBtns(btns);
                });
            }
        } else {
            //if is not rendered redraw all groups from server
            if ($("[id^='ogTasksPanelTask" + task.id + "']").length == 0) {
                ogTasks.Groups.loaded = false;
                ogTasks.draw();
                return;
            }
        }
    } else {
        //if is not rendered redraw all groups from server
        if ($("[id^='ogTasksPanelTask" + task.id + "']").length == 0) {
            ogTasks.Groups.loaded = false;
            ogTasks.draw();
            return;
        }
    }

    //update this task rows
    $("[id^='ogTasksPanelTask" + task.id + "']").each(function (index) {
        var group_id = $(this).attr('id');
        var remove = "ogTasksPanelTask" + task.id + "G";
        group_id = group_id.replace(remove, "");

        var html = ogTasks.drawTask(task, drawOptions, displayCriteria, group_id, $(this).attr("data-level"), null, true);
        $("#ogTasksPanelTask" + task.id + "G" + group_id).replaceWith(html);
        //init action btns
        var btns = $("#ogTasksPanelTask" + task.id + "G" + group_id + " .tasksActionsBtn").toArray();
        og.initPopoverBtns(btns);
    });

    //start all clocks on the list
    var clocks = $(".og-timeslot-work-started span");

    for (i = 0; i < clocks.length; i++) {
        var clockId = clocks[i].id;
        clockId = clockId.replace("timespan", "");
        var user_start_time = parseInt($("#" + clockId + "user_start_time").val());

        og.startClock(clockId, user_start_time);
    }

    og.eventManager.fireEvent('replace all empty breadcrumb', null);
    
    // draw parent task's elbows
    if (task.parentId > 0) {
    	ogTasks.drawElbows(task.parentId);
    }
}

ogTasks.drawTaskRow = function (task, drawOptions, displayCriteria, group_id, level, returnHtml) {
    var sb = new StringBuffer();
    var tgId = "T" + task.id + 'G' + group_id;
    
    // check if task has been already rendered in this group, dont check this if only the html is to return and update the current row container
    if (!returnHtml && $("#tasksPanel"+ og.genid +" #ogTasksPanelTask"+ task.id +"G"+ group_id).length > 0) {
    	return;
    }

    //checkbox container class by priority
    var priorityColor = "priority-default";
    if (typeof task.priority != 'undefined') {
        priorityColor = "priority-" + task.priority;
    }

    //subtask expander
    var showSubtasksExpander = false;
    var subtasksExpander = "toggle_collapsed";
    if (drawOptions.show_subtasks_structure && task.subtasksIds.length > 0) {
        showSubtasksExpander = true;
        if (task.isExpanded) {
            subtasksExpander = "toggle_expanded";
        }

    }


    //Draw the Assigned user
    var assignedTo = false;
    if (task.assignedToId) {
        assignedTo = og.allUsers[task.assignedToId];
    }

    //Draw the Assigned user
    var assignedBy = false;
    if (task.assignedById) {
        assignedBy = og.allUsers[task.assignedById];
    }

    //Draw the task name
    taskName = task.title;
    var tooltip = '';
    //if is completed
    if (task.status > 0) {
        var user = this.getUser(task.completedById, true);
        if (user) {
            var time = new Date(task.completedOn * 1000);
            var now = new Date();
            var timeFormatted = time.getYear() != now.getYear() ? time.dateFormat('M j, Y') : time.dateFormat('M j');
            tooltip = lang('completed by name on', og.clean(user.name), timeFormatted).replace(/'\''/g, '\\\'');
        }
    }

    //Member Path
    mem_path = "";
    var mpath = Ext.util.JSON.decode(task.memPath);

    if (mpath) mem_path = og.getEmptyCrumbHtml(mpath, ".task-breadcrumb-container", og.breadcrumbs_skipped_dimensions);

    //dimesions breadcrumbs
    var dim_classification = new Array();
    //remove empty values from show_dimension_cols
    drawOptions.show_dimension_cols = drawOptions.show_dimension_cols.filter(function (x) { return (x != '') });
    for (var x = 0; x < drawOptions.show_dimension_cols.length; x++) {
        did = drawOptions.show_dimension_cols[x];
        var dim_mpath = {};
        var exclude_parents_path = false;

        if (!isNaN(did)) {
            dim_mpath[did] = mpath[did];
        }

        if (ogTasks.override_task_dim_col_value && ogTasks.override_task_dim_col_value.length > 0) {
            for (var i = 0; i < ogTasks.override_task_dim_col_value.length; i++) {
                var fn = ogTasks.override_task_dim_col_value[i];
                if (typeof(fn) == 'function') {
                    var result = fn.call(null, did, mpath);
                    if (result) {
                        dim_mpath = result.dim_mpath;
                        exclude_parents_path = result.exclude_parents_path;
                    }
                }
            }
        }

        if (isNaN(did)) {
            for (z in dim_mpath) {
                did = z;
                break;
            }
        }
        // ignore disabled dimensions
        if (og.config.enabled_dimensions.indexOf(did+"") == -1) {
        	continue;
        }

        var dim_mem_path = "";
        if (typeof mpath[did] != "undefined") {
            dim_mem_path = og.getEmptyCrumbHtml(dim_mpath, ".task-breadcrumb-container", null, null, exclude_parents_path,true);
        }

        var key = 'lp_dim_' + did + '_show_as_column';
        if (og.preferences['listing_preferences'][key]) {
            dim_classification.push(
                {
                    id: 'task_clasification_dim_' + drawOptions.show_dimension_cols[x],
                    dim_mem_path: dim_mem_path
                }
            );
        }
    }

    //Dates
    var start_date = '';
    task.already_started = false;
    if (task.startDate) {
        var date = new Date(task.startDate * 1000);
        date = new Date(Date.parse(date.toUTCString().slice(0, -4)));
        var hm_format = task.useStartTime ? (og.preferences['time_format_use_24'] == 1 ? ' <br> G:i' : ' <br> g:i A') : '';
        var now = new Date();
        var dateFormatted = date.getYear() != now.getYear() ? date.dateFormat('M j, Y' + hm_format) : date.dateFormat('M j' + hm_format);
        start_date = dateFormatted;
        if (date < now) task.already_started = true;
    }
    var due_date = '';
    var due_date_late = false;
    if (task.dueDate) {
        var date = new Date((task.dueDate) * 1000);
        date = new Date(Date.parse(date.toUTCString().slice(0, -4)));
        var hm_format = task.useDueTime ? (og.preferences['time_format_use_24'] == 1 ? ' <br> G:i' : ' <br> g:i A') : '';
        var now = new Date();
        var dateFormatted = date.getYear() != now.getYear() ? date.dateFormat('M j, Y' + hm_format) : date.dateFormat('M j' + hm_format);
        due_date = dateFormatted;

        if (task.status == 0 && date < now) {
            due_date_late = true;
        }
    }

    //Draw time tracking
    var userIsWorking = false;
    var userPaused = false;
    var userStartTime = 0;
    var userState = 'started';
    var userPausedTime = '';
    var workingOnUsers = new Array();
    var showWorkingOnUsers = false;
    if (drawOptions.show_time) {
        //is working
        if (task.workingOnIds) {
            var ids = (task.workingOnIds + ' ').split(',');
            for (var i = 0; i < ids.length; i++) {
                if (this.currentUser && ids[i] == this.currentUser.id) {
                    userIsWorking = true;
                    userStartTime = task.workingOnTimes[i];
                    var pauses = (task.workingOnPauses + ' ').split(',');
                    userPaused = pauses[i] == 1;
                    if (userPaused) {
                        userState = 'paused';
                        userPausedTime = og.calculateTimeForClock(new Date(), userStartTime);
                    }
                } else {
                    var usrId = parseInt(ids[i]);
                    workingOnUsers.push(og.allUsers[usrId]);
                    showWorkingOnUsers = true;
                }
            }
        }
    }
    if (drawOptions.show_time_quick) {
        
    }

    //task actions
    var taskActions = new Array();
    
    taskActions.push({
        act_collapsed: !drawOptions.show_quick_add_sub_tasks,
        act_onclick: "ogTasks.drawAddNewTaskForm",
        act_onclick_param: [{param_val: "'" + group_id + "',"}, {param_val: task.id + ","}, {param_val: level}],
        act_text: lang('add subtask'),
        act_id: "ogTasksPanelExpander" + tgId,
        act_class: "add-subtask-link ico-add coViewAction"
    });
    taskActions.push({
        act_collapsed: !drawOptions.show_quick_edit,
        act_onclick: "ogTasks.drawEditTaskForm",
        act_onclick_param: [{param_val: task.id + ","}, {param_val: "'" + group_id + "'"}],
        act_text: lang('edit'),
        act_class: "ico-edit coViewAction"
    });

    if (task.mark_as_started) {
        taskActions.push({
            act_collapsed: !drawOptions.show_quick_mark_as_started,
            act_onclick: "ogTasks.ToggleChangeMarkAsStarted",
            act_onclick_param: [{param_val: task.id}],
            act_text: lang('unmark as started this task'),
            act_class: "ico-undo coViewAction"
        });
    } else {
        taskActions.push({
            act_collapsed: !drawOptions.show_quick_mark_as_started,
            act_onclick: "ogTasks.ToggleChangeMarkAsStarted",
            act_onclick_param: [{param_val: task.id}],
            act_text: lang('mark as started this task'),
            act_class: "ico-start coViewAction"
        });
    }

    if (task.status) {
        taskActions.push({
            act_collapsed: !drawOptions.show_quick_complete,
            act_onclick: "ogTasks.ToggleCompleteStatus",
            act_onclick_param: [{param_val: task.id + ","}, {param_val: task.status}],
            act_text: lang('reopen this task'),
            act_class: "ico-reopen coViewAction"
        });
    } else {
        taskActions.push({
            act_collapsed: !drawOptions.show_quick_complete,
            act_onclick: "ogTasks.ToggleCompleteStatus",
            act_onclick_param: [{param_val: task.id + ","}, {param_val: task.status}],
            act_text: lang('complete this task'),
            act_class: "ico-complete coViewAction"
        });
    }
    /*
    if (drawOptions.show_time_quick && task.canAddTimeslots) {
        taskActions.push({
            act_collapsed: true,
            act_onclick: "ogTasks.AddWorkTime",
            act_onclick_param: [{param_val: task.id}],
            act_text: lang('add work'),
            act_class: "ico-time-s coViewAction"
        });
    }*/


    //mark the last collapsed action with a bool
    for (var i = taskActions.length; i > 0; i--) {
        if (taskActions[i - 1].act_collapsed) {
            taskActions[i - 1].act_last = true;
            break;
        }
    }

    var collapsed_actions = 0;
    var show_quick_actions_container = false;
    for (var i = taskActions.length; i > 0; i--) {
        if (!taskActions[i - 1].act_collapsed) {
            show_quick_actions_container = true;
        } else {
            collapsed_actions++;
        }
    }

    var show_actions_popover_button = collapsed_actions > 0;

    //updating waiting tasks
    waiting_tasks = task.dependants;


    var row_total_cols = [];
    for (var key in ogTasks.TotalCols) {
        var row_field = ogTasks.TotalCols[key].row_field;
        var color = '#888';
        if (row_field == 'worked_time_string' && task.TimeEstimate != '0' && task.TimeEstimate < task.worked_time) {
            color = '#f00';
        }
        row_total_cols.push({text: task[row_field], color: color});
    }

    // dimension columns
    var row_dim_cols = [];
    for (did in og.dimensions_info) {
        if (isNaN(did)) continue;
        var key = 'lp_dim_' + did + '_show_as_column';
        if (og.preferences['listing_preferences'][key]) {

        }
    }

    //get template for the row
    if (typeof ogTasks.task_list_row_template == "undefined") {
        var source = $("#task-list-row-template").html();
        //compile the template
        var template = Handlebars.compile(source);
        ogTasks.task_list_row_template = template;
    }

    var action_trigger = "focus";
    var is_safari = navigator.userAgent ? navigator.userAgent.indexOf("Safari") > -1 : false;
    var is_safari_vendor = navigator.vendor ? navigator.vendor.indexOf("Apple") > -1 : false;
    if (is_safari && is_safari_vendor) {
        action_trigger = "click";
    }

    color_start_date = "#888";
    if (task.already_started && !task.mark_as_started) {
        color_start_date = "#F00";
    }
    //template data
    var data = {
        task: task,
        task_actions: taskActions,
        action_trigger: action_trigger,
        show_quick_actions_container: show_quick_actions_container,
        show_actions_popover_button: show_actions_popover_button,
        can_add_timeslots: task.canAddTimeslots,
        genid: og.genid,
        start_date: start_date,
        due_date: due_date,
        due_date_late: due_date_late,
        draw_options: drawOptions,
        subtasksExpander: subtasksExpander,
        showSubtasksExpander: showSubtasksExpander,
        priorityColor: priorityColor,
        tgId: tgId,
        group_id: group_id,
        assigned_to_show_name: og.config.tasks_show_assigned_to_name,
        assigned_to: assignedTo,
        assigned_by: assignedBy,
        view_url: og.getUrl('task', 'view', {id: task.id}),
        task_name: taskName,
        tool_tip: tooltip,
        mem_path: mem_path,
        dim_classification: dim_classification,
        percent_completed_bar: ogTasks.buildTaskPercentCompletedBar(task),
        level: level,
        user_is_working: userIsWorking,
        user_paused: userPaused,
        user_paused_time: userPausedTime,
        user_state: userState,
        user_start_time: userStartTime,
        working_on_users: workingOnUsers,
        show_working_on_users: showWorkingOnUsers,
        row_total_cols: row_total_cols,
        color_start_date: color_start_date
    }

    if (ogTasks.additional_task_list_columns) {
        data.additional_task_list_columns = [];
        for (var i = 0; i < ogTasks.additional_task_list_columns.length; i++) {
            var col = ogTasks.additional_task_list_columns[i];
            data.additional_task_list_columns.push({
                id: col.id,
                cls: col.cls ? col.cls : '',
                html: task.additional_data[col.id] ? task.additional_data[col.id].html : ''
            });
        }
    }
    //instantiate the template
    var html = ogTasks.task_list_row_template(data);

    sb.append(html);
    return sb.toString();
}

ogTasks.closeTimeslot = function (tId,callback) {
    if (og.config.tasks_show_description_on_time_forms) {
        //get template
        var source = $("#small-task-timespan-template").html();
        //compile the template
        var template = Handlebars.compile(source);

        //template data
        var data = {
            taskId: tId,
            genid: og.genid
        }

        //instantiate the template
        var html = template(data);

        var modal_params = {
            'escClose': true,
            'overlayClose': true,
            'minWidth': 400,
            'minHeight': 200,
            'closeHTML': '<a id="ogTasksPanelAT_close_link" class="modal-close modal-close-img"></a>'
        };


        $.modal(html, modal_params);

        $("#small-task-timespan-modal-form" + og.genid).submit(function (event) {
            var parameters = [];
            var form_params = $(this).serializeArray();

            for (i = 0; i < form_params.length; i++) {
                parameters[form_params[i].name] = form_params[i].value;
            }

            ogTasks.executeActionFinal("close_work", tId, parameters['timeslot[description]'],callback);

            ogTasks.closeModal();

            event.preventDefault();
        });
    } else {
        ogTasks.executeActionFinal("close_work", tId,'',callback);
    }
}

ogTasks.drawSubtasks = function (params) {
    var task = ogTasksCache.getTask(params.task_id);
    var group_id = params.group_id;

    var $task_view = $('#ogTasksPanelTask' + task.id + 'G' + group_id);
    var subtasks_container_id = 'SubtasksT' + task.id + 'G' + group_id;
    var level = parseInt($task_view.attr("data-level")) + ogTasks.LevelMultiplier;

    var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
    var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
    var displayCriteria = bottomToolbar.getDisplayCriteria();
    var drawOptions = topToolbar.getDrawOptions();

    // reverse the array because rows are inserted in reverse order (using function "after" of the task parent)
    task.subtasksIds = task.subtasksIds.reverse();

    for (var i = 0; i < task.subtasksIds.length; i++) {
        var subtask = ogTasks.getTask(task.subtasksIds[i]);
        var subtask_row = ogTasks.drawTask(subtask, drawOptions, displayCriteria, group_id, level, null, 1);
        subtask_row = $(subtask_row).attr("class", $task_view.attr("class"));
        $task_view.after(subtask_row);
    }
    var btns = $("." + subtasks_container_id + " .tasksActionsBtn").toArray();
    og.initPopoverBtns(btns);

    og.eventManager.fireEvent('replace all empty breadcrumb', null);
    ogTasks.drawElbows(params.task_id);
}

/**
 * This function refreshh the elbows of task passed in parameter and his father
 * @param task_id
 */
ogTasks.refresElbows = function (task_id) {
    var task_parent_id = $("[data-task-id='" + task_id + "']").first().attr("data-parent-id");
    var task_grand_id = $("[data-task-id='" + task_parent_id + "']").first().attr("data-parent-id");
    ogTasks.drawElbows(task_grand_id);//for my father
    ogTasks.drawElbows(task_parent_id);//for me
}

/**
 * Function that will remove all the childs taks with recursivity
 * Also makes the toggle of the parent task id passed is not collapsed
 * Then is gonna click again to the toggle to retrive again the task and will draw correctly the elbows
 * @param parent_id
 */
ogTasks.removeTaskToRefreshElbows = function (parent_id) {
    $("[data-parent-id=" + parent_id + "]").each(function (index,element) {
        var $element = $(element);
        var current_task_id = $element.attr("data-task-id");
        //check if the element have childs
        if ($("[data-parent-id=" + current_task_id + "]").length > 0) {
            ogTasks.removeTaskToRefreshElbows(current_task_id);
        }
        $element.remove();
    });
    var $toggle = $("[data-task-toggle="+parent_id+"]").first();
    $toggle.removeClass("toggle_expanded").addClass("toggle_collapsed");
    $toggle.click();
    $toggle.removeClass("toggle_collapsed").addClass("toggle_expanded");
}

/**
 * This fucntion that work all the elbows for the childs of the task
 * @param parentTaskId
 */
ogTasks.drawElbows = function (parentTaskId) {
    var task_level = parseInt($("[data-parent-id='" + parentTaskId + "']").attr("data-level"));
    var $parent = $("[data-task-id='" + parentTaskId + "']").first();
    var margin_of_level = task_level / 2;
    var count_of_eblow_lines = 0;
    var margin_elbow_line = 0;
    if (task_level <= 21) {//this mean is the first pass of elbows and we need it without margin
        margin_of_level = 0;
    } else {
        //this mean that we had displayed a 2 sublevels
        count_of_eblow_lines = parseInt((task_level - ogTasks.LevelMultiplier) / ogTasks.LevelMultiplier);
        var elbows_line_parent = $parent.find(".task-elbow-line").length;
        if ($parent.find("[data-elbow-type]").hasClass("task-elbow-end")) {
            //check if my father is the last element, because of that we dont draw the last elbow line
            count_of_eblow_lines = elbows_line_parent;
            if (elbows_line_parent > 0) {
                margin_elbow_line = parseInt($parent.find(".task-elbow-line").css('marginLeft').replace("px", ""));
            }
        } else {
            //check i don`t have more elbows line than my myfather
            if (elbows_line_parent == 0) {
                if ($parent.find(".task-elbow").length > 0) {
                    margin_elbow_line = parseInt($parent.find(".task-elbow").css('marginLeft').replace("px", ""));
                }
                count_of_eblow_lines = 1
            } else {
                count_of_eblow_lines = elbows_line_parent + 1;
            }
        }
    }
    //here we clean all the elbowls line in the parentTaskid to not duplicated class
    $("[data-parent-id='" + parentTaskId + "'][data-elbow-line-container]").removeClass("task-elbow");
  //  $("[data-parent-id='" + parentTaskId + "'][data-elbow-line-container]").removeClass("task-elbow-end");
    $("[data-parent-id='" + parentTaskId + "']").each(function (index, value) {
        var $value = $(value);
        for (var i = count_of_eblow_lines; i > 0; i--) {
            var margin_line = (((i - 1) * ogTasks.LevelMultiplier) + margin_elbow_line);
            if (i == count_of_eblow_lines) {
                //this is the last to show align to the right line so we have to take the same margin as the elbow or elbow-end of my parent
                var my_parent = $("[data-task-id=" + $value.attr('data-parent-id') + "]").first();
                if (my_parent.find(".task-elbow").length > 0) {
                    margin_line = parseInt(my_parent.find(".task-elbow").css('marginLeft').replace("px", ""));
                } else {
                    margin_line = parseInt(my_parent.find(".task-elbow-line").last().css('marginLeft').replace("px", ""));
                }
            }
            $value.find("[data-elbow-line-container]").prepend("<span class='task-elbow-line' style='margin-left: " + margin_line + "px;'></span>");

        }
        $value.find("[data-elbow-type]").addClass("task-elbow").css("marginLeft", parseInt(margin_of_level));
        if (margin_of_level > 0) {
            $value.find("[data-task-span-name]").css("marginLeft", parseInt(margin_of_level) + 25);
        }
    });
    
    $('.tasks-panel-group').each(function (i,v){
        var rowAux = $(v).find("[data-parent-id='" + parentTaskId + "'].task-list-row-template:last");
        rowAux.find("[data-elbow-type]").removeClass("task-elbow").addClass("task-elbow-end").css("marginLeft", parseInt(margin_of_level));
    });
    //$("[data-parent-id='" + parentTaskId + "'].task-list-row-template:last").find("[data-elbow-type]").removeClass("task-elbow").addClass("task-elbow-end").css("marginLeft", parseInt(margin_of_level));
}


ogTasks.ToggleCompleteStatus = function (task_id, status) {
    var related = false;
    if (status == 0) {
        var task = ogTasks.getTask(task_id);
        for (var j = 0; j < task.subtasks.length; j++) {
            if (task.subtasks[j].status == 0) {
                related = true;
            }
            if (related) {
                break;
            }
        }
    }

    if (related) {
        this.dialog = new og.TaskCompletePopUp(task_id);
        this.dialog.setTitle(lang('do complete'));
        this.dialog.show();
    } else {
        ogTasks.ToggleCompleteStatusOk(task_id, status, '');
    }
}

ogTasks.ToggleCompleteStatusOk = function (task_id, status, opt) {
    var action = (status == 0) ? 'complete_task' : 'open_task';
    og.openLink(og.getUrl('task', action, {id: task_id, quick: true, options: opt}), {
        callback: function (success, data) {
            if (success || !data.errorCode) {
                if (data.task) {
                    //Set task data
                    var task = ogTasksCache.addTasks(data.task);

                    //update dependants
                    if (task.status) {
                        ogTasks.updateDependantTasks(task.id, false);
                    } else {
                        ogTasks.updateDependantTasks(task.id, true);
                    }

                    ogTasks.UpdateTask(task.id, false);
                } else {
                    ogTasks.UpdateTask(task_id, true);
                }

                if (data.more_tasks) {
                    for (var j = 0; j < data.more_tasks.length; j++) {
                        ogTasks.drawTaskRowAfterEdit({'task': data.more_tasks[j]});
                    }
                }
                ogTasks.refreshGroupsTotals();
            }
        },
        scope: this
    });
}

ogTasks.ToggleChangeMarkAsStarted = function (task_id) {

    og.openLink(og.getUrl('task', "change_mark_as_started", {id: task_id, quick: true}), {
        callback: function (success, data) {
            if (!success || data.errorCode) {

            } else {
                if (data.task) {
                    //Set task data
                    var task = ogTasksCache.addTasks(data.task);

                    ogTasks.UpdateTask(task.id, false);
                } else {
                    ogTasks.UpdateTask(task_id, true);
                }

                ogTasks.refreshGroupsTotals();
            }
        },
        scope: this
    });
}

ogTasks.loadTimeslotUsers = function (genid, task_id) {

    og.openLink(og.getUrl('timeslot', 'get_users_for_timeslot', {task_id: task_id}), {
        callback: function (success, data) {
            if (data.users && data.users.length > 0) {
                for (var i = 0; i < data.users.length; i++) {
                    var u = data.users[i];
                    var sel = u.id == og.loggedUser.id ? 'selected="selected"' : '';
                    $('#' + genid + 'tsUser').append('<option value="' + u.id + '" ' + sel + '>' + u.name + '</option>');
                }
                $('#' + genid + 'tsUserContainer').show();

            } else {
                $('#' + genid + 'tsUser').remove();
                $('#' + genid + 'tsUserContainer').append('<input type="hidden" name="timeslot[contact_id]" value="' + og.loggedUser.id + '" />');
            }
        }
    });

}

ogTasks.AddWorkTime = function (task_id) {
    og.render_modal_form('', {
        c: 'time',
        a: 'add',
        params: {object_id: task_id, contact_id: og.loggedUser.id, dont_reload: 1}
    });
    return;
}


ogTasks.readTask = function (task_id, isUnRead) {
    var task = ogTasks.getTask(task_id);
    if (!isUnRead) {
        og.openLink(
            og.getUrl('task', 'multi_task_action'),
            {
                method: 'POST', post: {ids: task_id, action: 'markasread'}, callback: function (success, data) {
                    if (!success || data.errorCode) {
                    } else {
                        var td = document.getElementById('ogTasksPanelMarkasTd' + task_id);
                        td.innerHTML = "<div title=\"" + lang('mark as unread') + "\" id=\"readunreadtask" + task_id + "\" class=\"db-ico ico-read\" onclick=\"ogTasks.readTask(" + task_id + ",true)\" />";
                        task.isRead = true;
                    }
                }
            }
        );
    } else {
        og.openLink(
            og.getUrl('task', 'multi_task_action'),
            {
                method: 'POST', post: {ids: task_id, action: 'markasunread'}, callback: function (success, data) {
                    if (!success || data.errorCode) {
                    } else {
                        var td = document.getElementById('ogTasksPanelMarkasTd' + task_id);
                        td.innerHTML = "<div title=\"" + lang('mark as read') + "\" id=\"readunreadtask" + task_id + "\" class=\"db-ico ico-unread\" onclick=\"ogTasks.readTask(" + task_id + ",false)\" />";
                        task.isRead = false;
                    }
                }
            }
        );
    }
}
//this is executed only when click on buttons bar and not when edit via modal the task
//in all the buttons doesnt send never from_server in true but i leave because is my first day and also in a find usages appear some lines with pass true
ogTasks.UpdateTask = function (task_id, from_server) {
    if (typeof from_server != 'undefined' && from_server) {
        og.openLink(og.getUrl('task', 'get_task_data', {id: task_id, task_info: true}), {
            callback: function (success, data) {
                if (!success || data.errorCode) {

                } else {
                    //Set task data
                    ogTasks.drawTaskRowAfterEdit(data);
                }
            },
            scope: this
        });
    } else {
        var task = ogTasksCache.getTask(task_id);
        ogTasks.reDrawTask(task);
        ogTasks.refresElbows(task_id);
    }
}

ogTasks.buildTaskPercentCompletedBar = function (task) {
    var color_cls = 'task-percent-completed-';

    if (task.percentCompleted < 25) color_cls += '0';
    else if (task.percentCompleted < 50) color_cls += '25';
    else if (task.percentCompleted < 75) color_cls += '50';
    else if (task.percentCompleted < 100) color_cls += '75';
    else if (task.percentCompleted == 100) color_cls += '100';
    else color_cls += 'more-estimate';

    var percent_complete = 100;
    if (task.percentCompleted <= 100) {
        percent_complete = task.percentCompleted;
    }

    var html = "<span><span class='nobr'><table style='display:inline;'><tr><td style='padding-left:15px;padding-top:6px'>" +
        "<table style='height:7px;width:50px'><tr><td style='height:7px;width:" + percent_complete + "%;' class='" + color_cls + "'></td><td style='width:" + (100 - percent_complete) + "%;background-color:#DDD'></td></tr></table>" +
        "</td><td style='padding-left:3px;line-height:12px'><span class='percent_num' style='font-size:8px;color:#777'>" + percent_complete + "%</span></td></tr></table></span></span>";

    return html;
}


ogTasks.UpdateDependants = function (task, complete, prev_status) {
    var deps = this.getDependencyCount(task.id);
    if (deps) {
        var dependants = deps.dependants.split(',');
        for (var i = 0; i < dependants.length; i++) {
            var dependant_id = dependants[i];
            var dc = this.getDependencyCount(dependant_id);
            if (dc) {
                if (complete) {
                    dc.count -= 1;
                    this.UpdateTask(dependant_id);
                } else {
                    // Reopen: add 1 and reopen parents
                    if (prev_status == 1) {
                        dc.count += 1;
                        var dep = this.getTask(dependant_id);
                        dep.status = 0;
                        this.UpdateTask(dependant_id);
                        this.UpdateDependants(dep, false);
                    }
                }
            }
        }
    }
}

ogTasks.initTasksList = function () {
    var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
    var drawOptions = topToolbar.getDrawOptions();

    var tasks_list_cols = [];

    //actions
    tasks_list_cols.push(
        {
            id: 'task_actions',
            col_width: '70px'
        }
    );

    //assigned by
    if (drawOptions.show_by) {
        tasks_list_cols.push(
            {
                id: 'task_assigned_by_id',
                title: lang('by uppercase'),
                group_total_field: '',
                data: 'data-resizable=1',
                row_field: 'assignedById',
                col_width: '30px'
            }
        );
    }

    //assigned to
    tasks_list_cols.push(
        {
            id: 'task_assigned_to_id',
            title: lang('to'),
            group_total_field: '',
            data: 'data-resizable=1',
            row_field: 'assignedToId',
            col_width: '30px'
        }
    );

    //task name
    tasks_list_cols.push(
        {
            id: 'task_name',
            title: lang('task'),
            data: 'data-resizable=1',
            group_total_field: '',
            row_field: 'title',
            col_width: 'auto'
        }
    );

    //clasification
    if (drawOptions.show_classification) {
        tasks_list_cols.push(
            {
                id: 'task_clasification',
                title: lang('classified under'),
                data: 'data-resizable=1',
                group_total_field: '',
                row_field: 'memPath',
                col_width: 'auto'
            }
        );
    }

    //dimesions breadcrumbs
    for (x in drawOptions.show_dimension_cols) {
        did = drawOptions.show_dimension_cols[x];
        ot_id = null;
        if (typeof(did) == 'function') continue;

        if (isNaN(did) && did.indexOf("-") != -1) {
            exp = did.split("-");
            did = exp[0];
            if (!isNaN(ot_id)) ot_id = exp[1];
        }
        if (did == 0 || !og.dimensions_info[did]) continue;

        var key = 'lp_dim_' + did + '_show_as_column';
        if (og.preferences['listing_preferences'][key]) {
            tasks_list_cols.push(
                {
                    id: 'task_clasification' + drawOptions.show_dimension_cols[x],
                    css_class: 'task_clasification',
                    title: ot_id ? lang(og.objectTypes[ot_id].name + 's') : og.dimensions_info[did].name,
                    data: 'data-resizable=1',
                    group_total_field: '',
                    col_width: 'auto'
                }
            );
        }
    }

    //percent complete bar
    if (drawOptions.show_percent_completed_bar) {
        tasks_list_cols.push(
            {
                id: 'task_completed_bar',
                title: lang('completed'),
                group_total_field: '',
                row_field: 'ogTasks.buildTaskPercentCompletedBar(task)',
                col_width: '100px'
            }
        );
    }

    //start date
    if (drawOptions.show_start_dates) {
        tasks_list_cols.push(
            {
                id: 'task_start_date',
                title: lang('start m'),
                group_total_field: '',
                row_field: 'startDate',
                col_width: '100px'
            }
        );
    }

    //due date
    if (drawOptions.show_end_dates) {
        tasks_list_cols.push(
            {
                id: 'task_due_date',
                title: lang('due m'),
                group_total_field: '',
                row_field: 'dueDate',
                col_width: '100px'
            }
        );
    }

    //time estimated
    if (drawOptions.show_time_estimates) {
        tasks_list_cols.push(
            {
                id: 'task_estimated',
                title: lang('estimated'),
                group_total_field: 'estimatedTime',
                row_field: 'estimatedTime',
                col_width: '100px'
            }
        );
    }

    //time pending
    if (drawOptions.show_time_pending) {
        tasks_list_cols.push(
            {
                id: 'task_pending',
                title: lang('pending'),
                group_total_field: 'pending_time_string',
                row_field: 'pending_time_string',
                col_width: '100px'
            }
        );
    }

    //time worked
    if (drawOptions.show_time_worked) {
        tasks_list_cols.push(
            {
                id: 'task_worked',
                title: lang('worked'),
                group_total_field: 'worked_time_string',
                row_field: 'worked_time_string',
                col_width: '100px'
            }
        );
    }

    // additional columns
    if (ogTasks.additional_task_list_columns) {
        for (var i = 0; i < ogTasks.additional_task_list_columns.length; i++) {
            var col = ogTasks.additional_task_list_columns[i];
            var field = col.row_field ? col.row_field : col.id;
            var width = col.width ? col.width : '100px';

            if (drawOptions[col.id]) {
                tasks_list_cols.push({
                    id: col.id,
                    title: col.name,
                    group_total_field: '',
                    row_field: field,
                    col_width: width
                });
            }
        }
    }

    //previous tasks
    if (drawOptions.show_previous_pending_tasks) {
        tasks_list_cols.push(
            {
                id: 'task_previous',
                title: lang('previous tasks'),
                group_total_field: '',
                row_field: 'previous_tasks_total',
                col_width: '100px'
            }
        );
    }


    // custom properties
    for (x in ogTasks.custom_properties) {
        var cp = ogTasks.custom_properties[x];
        if (typeof(cp) == 'object' && ogTasks.userPreferences['tasksShowCP_' + cp.id] == 1) {
            tasks_list_cols.push({
                id: 'cp_' + cp.id,
                css_class: '',
                title: cp.name,
                data: 'data-resizable=1',
                group_total_field: '',
                col_width: 'auto'
            });
        }
    }

    //quick actions
    tasks_list_cols.push(
        {
            id: 'task_quick_actions',
            col_width: '100px'
        }
    );

    //actions btn
    tasks_list_cols.push(
        {
            id: 'task_btn_actions',
            col_width: '100px'
        }
    );

    ogTasks.TasksList.tasks_list_cols = tasks_list_cols;
}

ogTasks.newTaskFormTopList = function () {
    ogTasks.initTasksList();
    var title_cols = [];

    //title_cols.push({text:group[row_field], cssclass:'task-date-container'});

    //get template for the row
    var source = $("#task-list-col-names-template").html();
    //compile the template
    var template = Handlebars.compile(source);

    var add_btn_position = 'task_quick_actions';
    for (var i = 0; i < ogTasks.TasksList.tasks_list_cols.length; i++) {
        var col = ogTasks.TasksList.tasks_list_cols[i];
        if (ogTasks.TasksList.tasks_list_cols[i].id == 'task_name') {
            add_btn_position = ogTasks.TasksList.tasks_list_cols[i + 1].id;
        }
    }

    //template data
    var data = {
        tasks_list_cols: ogTasks.TasksList.tasks_list_cols,
        add_btn_position: add_btn_position,
        genid: og.genid
    }

    //instantiate the template
    var html = template(data);

    return html;
}


/*******************************************************/
/**************** DRAG & DROP FUNCTIONS ****************/
/*******************************************************/
ogTasks.initDragDrop = function () {

    $(".tasks-panel-group").sortable({
        connectWith: ".tasks-panel-group-droppable:not(.ui-sortable-helper)",
        stop: function (event, object, c, d) {
            ogTasks.processTaskDrop(event, object);
            $(".dragging").removeClass("dragging");

            var add_trs = $(object.item).children('.task-list-row');
            for (var i = 0; i < add_trs.length; i++) {
                var tr = add_trs[i];
                $(tr).remove();
                $(object.item).after(tr);
            }
        },
        helper: function (e, item) {
            var ids_str = ogTasks.getSelectedIds() + '';
            var selected_ids = ids_str.split(',');
            $(item).addClass('dragging');

            var html = item;
            var processed_ids = [item[0].id];

            for (var i = 0; i < selected_ids.length; i++) {
                var sel_task = ogTasks.getTask(selected_ids[i]);
                if (sel_task && sel_task.divInfo && sel_task.divInfo[0]) {
                    var sel_el_id = "ogTasksPanelTask" + sel_task.id + "G" + sel_task.divInfo[0].group_id;
                    var sel_el = $("#" + sel_el_id);
                    if (sel_el.length > 0 && processed_ids.indexOf(sel_el[0].id) == -1) {
                        $(sel_el).addClass('dragging');

                        $(html).append(sel_el[0]);
                        processed_ids.push(sel_el[0].id);
                    }
                }
            }

            return html;
        },
        handle: ".ddhandle",
        cursor: "move",
        dropOnEmpty: false,
        placeholder: "tasks-dd-placeholder"
    });
    $(".tasks-panel-group").disableSelection();
};

ogTasks.processTaskDrop = function (event, object) {
    if (object.item.length > 0 && object.item[0].parentNode && object.item[0].parentNode.id) {

        var from_group_id = event.target.id.replace("ogTasksPanelGroup", "");
        var to_group_id = object.item[0].parentNode.id.replace("ogTasksPanelGroup", "");

        var task_id = object.item[0].id.replace("ogTasksPanelTask", "");
        var gpos = task_id.indexOf("G");
        if (gpos >= 0) {
            task_id = task_id.substring(0, gpos);
        }

        var ids_str = ogTasks.getSelectedIds() + '';
        ids_str += (ids_str == '' ? '' : ',') + task_id;
        var task_ids = ids_str.split(',');

        var valid_dropzone = $(event.toElement).closest(".tasks-panel-group-droppable");

        // check if dropped to a dimension tree node
        var member_id = null;
        var dimension_id = null;
        if (event.toElement.hasAttribute("ext:tree-node-id")) {
            var node_id = $(event.toElement).attr("ext:tree-node-id");
            if (!isNaN(node_id)) {
                member_id = node_id;
            }
            dimension_id = parseInt($(event.toElement).closest(".x-panel.x-tree").attr('id').replace('dimension-panel-', ''));

        } else if (event.toElement.id.indexOf("extdd-") >= 0) {
            var node_id = $(event.toElement).closest(".x-tree-node-el").attr("ext:tree-node-id");
            if (!isNaN(node_id)) {
                member_id = node_id;
            }
            dimension_id = parseInt($(event.toElement).closest(".x-panel.x-tree").attr('id').replace('dimension-panel-', ''));
        }

        // classify task
        if (dimension_id != null && !isNaN(dimension_id)) {
            // ensure that task is not moved to another group
            ogTasks.cancelDrop();

            ogTasks.classifyTasks(task_ids, member_id, dimension_id, from_group_id);

        } else {
            // valid dropzone and source group != to group
            if (valid_dropzone.length > 0 && from_group_id != to_group_id) {

                ogTasks.changeTasksGroup(task_ids, from_group_id, to_group_id);

            } else {
                // Invalid dropzone
                ogTasks.cancelDrop();
            }
        }
    }
}

ogTasks.cancelDrop = function () {
    try {
        $(".tasks-panel-group").sortable("cancel");
    } catch (e) {
        // try/catch to avoid js crash, this error does not need to be handled
    }
}

ogTasks.changeTasksGroup = function (task_ids, from_group_id, to_group_id) {

    var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
    var filters = bottomToolbar.getFilters();
    var displayCriteria = bottomToolbar.getDisplayCriteria();

    if (displayCriteria.group_by.indexOf('dimension_') >= 0) {
        // grouping by dimension -> classify selected tasks
        var dimension_id = displayCriteria.group_by.replace('dimension_', '');
        ogTasks.classifyTasks(task_ids, to_group_id, dimension_id, from_group_id);

    } else {
        // Possible grouping: 'nothing','milestone','priority','assigned_to','due_date','start_date','created_on','created_by','completed_on','completed_by','status'
        // Do not modify if grouping by 'created_on' or 'created_by' or 'completed_on' or 'completed_by'

        switch (displayCriteria.group_by) {
            case 'assigned_to':
            case 'priority':
            case 'status':
            case 'milestone':
                ogTasks.editTasksAttribute(task_ids, displayCriteria.group_by, to_group_id, from_group_id);
                break;

            case 'due_date':
            case 'start_date':
                // TODO: prompt exact date and update tasks
                //var date_value = '';
                //ogTasks.editTasksAttribute(task_ids, displayCriteria.group_by, date_value, from_group_id);
                ogTasks.promptDate(task_ids, displayCriteria.group_by, to_group_id, from_group_id);
                break;

            default:
                // Invalid grop group
                ogTasks.cancelDrop();
                break;
        }
    }
}

ogTasks.promptDate = function (task_ids, attribute, to_group_id, from_group_id) {
    var extid = Ext.id();
    var title = (attribute == 'due_date' ? lang('new due date') : lang('new start date'));
    var description = (attribute == 'due_date' ? lang('new due date desc') : lang('new start date desc'));

    var source = $("#change-tasks-date").html();
    var template = Handlebars.compile(source);
    var html = template({genid: extid, attribute: attribute, title: title, description: description});

    var modal_params = {
        'escClose': true,
        'overlayClose': true,
        'minWidth': 400,
        'minHeight': 200,
        'closeHTML': '<a id="ogTasksPanelAT_close_link" class="modal-close modal-close-img"></a>',
        'onClose': function (dialog) {
            ogTasks.cancelDrop();
            $.modal.close();
        },
        'onShow': function (dialog) {

            var dtp = new og.DateField({
                renderTo: extid + '_date_picker_container',
                name: 'new_date_value',
                emptyText: og.preferences.date_format_tip,
                id: extid + '_date_picker',
                value: ''
            });
        }

    };
    $.modal(html, modal_params);

    $("#change-tasks-date-modal-form-" + extid).submit(function (event) {
        var date_picker = Ext.getCmp(extid + "_date_picker");
        var dp_value = date_picker.getValue();
        var date_value = dp_value ? dp_value.format(og.preferences.date_format) : '';
        ogTasks.editTasksAttribute(task_ids, attribute, date_value, from_group_id);
        $.modal.close();
        return false;
    });
}

ogTasks.editTasksAttribute = function (task_ids, attribute, new_value, from_group_id) {

    var params = {
        task_ids: Ext.util.JSON.encode(task_ids),
        attribute: attribute,
        new_value: new_value
    };
    og.openLink(og.getUrl('task', 'edit_tasks_attribute'), {
        method: 'POST',
        post: params,
        callback: function (success, data) {
            for (var x = 0; x < task_ids.length; x++) {
                var t = ogTasks.getTask(task_ids[x]);
                if (t) {
                    $("#ogTasksPanelTask" + t.id + "G" + from_group_id).remove();
                    ogTasks.UpdateTask(t.id, true);
                }
            }
        }
    });
}

ogTasks.classifyTasks = function (task_ids, member_id, dimension_id, from_group_id) {
    var multiple_sel = task_ids.length > 1;
    var is_classified_in_dim = false;

    // check if there is any selected task classified in dimension_id
    for (var i = 0; i < task_ids.length; i++) {
        var task = ogTasks.getTask(task_ids[i]);
        var classification = Ext.util.JSON.decode(task.memPath);
        var task_classified_in_dim = classification && typeof(classification[dimension_id]) == 'object';
        if (task_classified_in_dim) {
            is_classified_in_dim = true;
        }
    }
    var rm_prev = 0;

    // if there are tasks classified in dimension_id => check if remove previous members of dimension_id
    if (is_classified_in_dim && !isNaN(member_id) && member_id > 0) {
        if (og.preferences['drag_drop_prompt'] == 'prompt') {
            var rm_prev = confirm(lang('do you want to mantain the current associations of this obj with members of', og.dimensions_info[dimension_id].name)) ? "0" : "1";
        } else if (og.preferences['drag_drop_prompt'] == 'move') {
            var rm_prev = 1;
        } else if (og.preferences['drag_drop_prompt'] == 'keep') {
            var rm_prev = 0;
        }
    }

    // set request parameters
    var params = {
        objects: Ext.util.JSON.encode(task_ids),
        remove_prev: rm_prev
    };
    if (!isNaN(member_id) && member_id > 0) {
        params.member = member_id; // classify in member_id
    } else {
        params.dimension = dimension_id; // unclassify from dimension_id
    }

    // make the classification request
    og.openLink(og.getUrl('member', 'add_objects_to_member'), {
        method: 'POST',
        post: params,
        callback: function (success, data) {
            for (var x = 0; x < task_ids.length; x++) {
                var t = ogTasks.getTask(task_ids[x]);
                if (t) {
                    $("#ogTasksPanelTask" + t.id + "G" + from_group_id).remove();
                    ogTasks.UpdateTask(t.id, true);
                }
            }
        }
    });
}


ogTasks.createDimensionColumnMenuItems = function (did, option_name, ignore_listing_preferences) {
    var menu_items = [];

    var key = 'lp_dim_' + did + '_show_as_column';
    if (ignore_listing_preferences || og.preferences['listing_preferences'][key]) {

        if (og.dimensions_info[did]) {
            var general_item = ogTasks.createDimensionColumnMenuItem(did, og.dimensions_info[did].name, did, option_name);
            menu_items.push(general_item);
        }

        if (ogTasks.list_dimension_column_hooks) {
            for (var j = 0; j < ogTasks.list_dimension_column_hooks.length; j++) {
                var fn = ogTasks.list_dimension_column_hooks[j];
                if (typeof(fn) == 'function') {
                    more_items = fn.call(null, did, option_name);
                    if (more_items && more_items.length > 0) {
                        menu_items = menu_items.concat(more_items);
                    }
                }
            }
        }

        if (ogTasks.userPreferences.showDimensionCols.indexOf(did) != -1) {
            og.breadcrumbs_skipped_dimensions[did] = did.toString();
        } else {
            og.breadcrumbs_skipped_dimensions[did] = 0;
        }
    }

    return menu_items;
}


ogTasks.createDimensionColumnMenuItem = function (did, label, menu_key, option_name) {

    if (!option_name) option_name = 'tasksShowDimensionCols';

    var checked = ogTasks.userPreferences.showDimensionCols.indexOf(menu_key) != -1;
    if (option_name.indexOf("gantt") == 0) {
        checked = ogTasks.ganttPreferences.ganttShowDimensionCols.indexOf(menu_key) != -1;
    }

    var menu_config = {
        option_name: option_name,
        text: label,
        value: menu_key,
        checked: checked,
        hideOnClick: false,
        checkHandler: function () {
            if (this.option_name.indexOf("gantt") == 0) {
                var dim_index = ogTasks.ganttPreferences.ganttShowDimensionCols.indexOf(this.value);
                if (dim_index != -1) {
                    ogTasks.ganttPreferences.ganttShowDimensionCols.splice(dim_index, 1);
                } else {
                    ogTasks.ganttPreferences.ganttShowDimensionCols.push(this.value);
                }
            } else {
                var dim_index = ogTasks.userPreferences.showDimensionCols.indexOf(this.value);
                if (dim_index != -1) {
                    ogTasks.userPreferences.showDimensionCols.splice(dim_index, 1);
                } else {
                    ogTasks.userPreferences.showDimensionCols.push(this.value);
                }
            }

            var opt_val = ogTasks.userPreferences.showDimensionCols.toString();
            if (this.option_name.indexOf("gantt") == 0) {
                checked = ogTasks.ganttPreferences.ganttShowDimensionCols.toString();
            }

            var url = og.getUrl('account', 'update_user_preference', {name: this.option_name, value: opt_val});
            //og.openLink(url, {hideLoading: true});

            if (this.value.indexOf("-") == -1 && this.option_name.indexOf("gantt") == -1) {
                var d = this.value.toString();
                if (ogTasks.userPreferences.showDimensionCols.indexOf(d) != -1) {
                    og.breadcrumbs_skipped_dimensions[d] = d;
                } else {
                    og.breadcrumbs_skipped_dimensions[d] = 0;
                }
            }
            
            //var tp = Ext.getCmp("tasks-panel");
            //if (tp) tp.reset();
            ogTasksMakeRequestAndReloadWithTimeout(url);
        }
    };

    return menu_config;
}
