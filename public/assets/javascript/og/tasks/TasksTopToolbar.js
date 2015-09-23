/**
 *  TaskManager
 *
 */
 
og.TasksTopToolbar = function(config) {
	Ext.applyIf(config,{
			id: "tasksPanelTopToolbarObject",
			renderTo: "tasksPanelTopToolbar",
			style:"border:0px none;"
		});
		
	og.TasksTopToolbar.superclass.constructor.call(this, config);

	var allTemplates = [];
	var allTemplatesArray = Ext.util.JSON.decode(document.getElementById(config.allTemplatesHfId).value);
	if (allTemplatesArray && allTemplatesArray.length > 0){
		for (var i = 0; i < allTemplatesArray.length; i++){
			allTemplates[allTemplates.length] = {text: allTemplatesArray[i].t,
				iconCls: 'ico-template',
				handler: function() {
					var tid = this.id;
					og.openLink(og.getUrl('template', 'template_parameters', {id: this.id}), {
						callback: function(success, data) {
							if (success) {
								if(data.parameters.length == 0){
									var url = og.getUrl('template', 'instantiate', {id: tid});
									og.openLink(url);
								}else{
									og.openLink(og.getUrl('template', 'instantiate_parameters', {id: tid}));
								}
							}
						}
					});
				},
				scope: allTemplatesArray[i]
			};
		}
	}

	var menuItems = [{
		text: lang('new task'),
		iconCls: 'ico-task',
		cls: 'tasks-panel-add-button',
		handler: function() {
			var additionalParams = {};
			var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
			if (toolbar.filterNamesCompaniesCombo.isVisible()){
				var value = toolbar.filterNamesCompaniesCombo.getValue();
				if (value) {
					additionalParams.assigned_to_contact_id = value;
				}
			}
			
			og.render_modal_form('', {c:'task', a:'add_task', params: additionalParams});
		}
	}];

	if (og.config.use_milestones) {
		menuItems = menuItems.concat([{
			text: lang('new milestone'),
			iconCls: 'ico-milestone',
			handler: function() {
				/*var url = og.getUrl('milestone', 'add');
				og.openLink(url);*/
				og.render_modal_form('', {c:'milestone', a:'add'});
			}
		}]);
	}
	
	var projectTemplates = [];
	var projectTemplatesArray = Ext.util.JSON.decode(document.getElementById(config.projectTemplatesHfId).value);
	if (projectTemplatesArray && projectTemplatesArray.length > 0){
		for (var i = 0; i < projectTemplatesArray.length; i++){
			projectTemplates[projectTemplates.length] = {text: projectTemplatesArray[i].t,
				iconCls: 'ico-template',
				handler: function() {
					var tid = this.id;
					og.openLink(og.getUrl('template', 'template_parameters', {id: this.id}), {
						callback: function(success, data) {
							if (success) {
								if (data.parameters.length == 0) {
									var url = og.getUrl('template', 'instantiate', {id: tid});
									og.openLink(url);
								} else {
									og.openLink(og.getUrl('template', 'instantiate_parameters', {id: tid}));
								}
							}
						}
					});
				},
				scope: projectTemplatesArray[i]
			};
		}
		projectTemplates[projectTemplates.length] = '-';
		menuItems = menuItems.concat(projectTemplates);
	}
	
	var newTemplate = [{
		text: lang('new template'),
		iconCls: 'ico-template',
		handler: function() {
			var url = og.getUrl('template', 'add');
			og.openLink(url);
		}
	},'-'];
	allTemplates = newTemplate.concat(allTemplates);
	
	
	menuItems = menuItems.concat([{
		text: lang('templates'),
		iconCls: 'ico-template',
		cls: 'scrollable-menu',
		menu: {
			items: allTemplates
		}}]);

	
	
	var butt = new Ext.Button({
		iconCls: 'ico-new',
		text: lang('new'),
		id: 'tasks-panel-new-menu',
		menu: {
			cls:'scrollable-menu',
			items: menuItems
		}
	});
	
	var markactions = {
		markAsRead: new Ext.Action({
			text: lang('mark as read'),
			tooltip: lang('mark as read desc'),
			iconCls: 'ico-mark-as-read',
			disabled: true,
			handler: function() {
				ogTasks.executeAction('markasread');
			},
			scope: this
		}),
		markAsUnread: new Ext.Action({
			text: lang('mark as unread'),
			tooltip: lang('mark as unread desc'),
			iconCls: 'ico-mark-as-read',
			disabled: true,
			handler: function() {
				ogTasks.executeAction('markasunread');
			},
			scope: this
		})
	};
	this.markactions = markactions;
	
	var actions = {
		del: new Ext.Action({
			text: lang('move to trash'),
			tooltip: lang('move selected objects to trash'),
			iconCls: 'ico-trash',
			disabled: true,
			handler: function() {
                            var ids = ogTasks.getSelectedIds()+'';
                            var arr_ids = ids.split(',')
                            for(var i = 0; i < arr_ids.length; i++){
                                var related = og.checkRelated("task",arr_ids[i]);
                                if(related){
                                    break;    
                                }                                
                            }
                            
                            if(related){
                                this.dialog = new og.TaskPopUp("delete",'');
                                this.dialog.setTitle(lang('tasks related'));	                                
                                this.dialog.show();
                            }else{
                                if (confirm(lang('confirm move to trash'))) {
                                        ogTasks.executeAction('delete');
                                }  
                            }
                            
			},
			scope: this
		}),
		complete: new Ext.Action({
			text: lang('do complete'),
                        tooltip: lang('complete selected tasks'),
                        iconCls: 'ico-complete',
			disabled: true,
			handler: function() {
                                var ids = ogTasks.getSelectedIds();
                                var related = false;
                                for(var i = 0; i < ids.length; i++){
                                    var task = ogTasks.getTask(ids[i]);
                                    for(var j = 0; j < task.subtasks.length; j++){
                                        if(task.subtasks[j].status == 0){
                                            related = true;
                                        }                                        
                                        if(related){
                                            break;    
                                        }
                                    }                             
                                }

                                if(related){
                                    this.dialog = new og.TaskCompletePopUp('');
                                    this.dialog.setTitle(lang('do complete'));	                                
                                    this.dialog.show();
                                }else{
                                    ogTasks.executeAction('complete');
                                }
			},
			scope: this
		}),
		markAs: new Ext.Action({
			text: lang('mark as'),
			tooltip: lang('mark as desc'),
			menu: [
				markactions.markAsRead,
				markactions.markAsUnread
			]
		}),
		archive: new Ext.Action({
			text: lang('archive'),
                        tooltip: lang('archive selected object'),
                        iconCls: 'ico-archive-obj',
			disabled: true,
			handler: function() {
                                var ids = ogTasks.getSelectedIds()+'';
                                var arr_ids = ids.split(',')
                                for(var i = 0; i < arr_ids.length; i++){
                                    var related = og.checkRelated("task",arr_ids[i]);
                                    if(related){
                                        break;    
                                    }                                
                                }

                                if(related){
                                    this.dialog = new og.TaskPopUp("archive",'');
                                this.dialog.setTitle(lang('tasks related'));	                                
                                this.dialog.show();
                                }else{
                                    if (confirm(lang('confirm archive selected objects'))) {
					ogTasks.executeAction('archive');
                                    }
                                }
			},
			scope: this
		})
	};
	this.actions = actions;
	
    

    
    
    //Add stuff to the toolbar
	if (!og.loggedUser.isGuest) {
		this.add(butt);
		this.addSeparator();		
		this.add(actions.complete);
		this.add(actions.archive);
		this.add(actions.del);		
		this.addSeparator();
	}
	this.add(actions.markAs);
	this.addSeparator();
	
	this.displayOptions = {
			by: {
		        text: lang('assigned by'),
				checked: (ogTasks.userPreferences.showBy == 1),
				checkHandler: function() {
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowAssignedBy', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
				}
			},
			time: {
				hidden: (typeof(ogTasks.userPreferences.showTime) == "undefined"),
		        text: lang('time'),
				checked: (ogTasks.userPreferences.showTime == 1),
				checkHandler: function() {
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowTime', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
				}
			},
			dates_start: {
		        text: lang('start date'),
				checked: (ogTasks.userPreferences.showStartDates == 1),
				checkHandler: function() {
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowStartDates', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
				}
			},
			dates_end: {
		        text: lang('due date'),
				checked: (ogTasks.userPreferences.showEndDates == 1),
				checkHandler: function() {
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowEndDates', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
				}
			},
			empty_milestones: {
		        text: lang('empty milestones'),
				checked: (ogTasks.userPreferences.showEmptyMilestones == 1),
				checkHandler: function() {
					ogTasks.userPreferences.showEmptyMilestones = 1 - ogTasks.userPreferences.showEmptyMilestones;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowEmptyMilestones', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;					
				},
				hidden: (!og.config.use_milestones)
			},
            time_estimates: {
		        text: lang('estimated time'),
				checked: (ogTasks.userPreferences.showTimeEstimates == 1),
				checkHandler: function() {
					if(this.checked){
						ogTasks.TotalCols.estimatedTime = {title: 'estimated', group_total_field: 'TimeEstimate', row_field: 'estimatedTime'};
					}else{
						delete ogTasks.TotalCols.estimatedTime;				
					}					
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowTimeEstimates', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
				}
			},
			time_pending: {
		        text: lang('pending time'),
				checked: (ogTasks.userPreferences.showTimePending == 1),
				checkHandler: function() {
					if(this.checked){
						ogTasks.TotalCols.pendingTime = {title: 'pending', group_total_field: 'pending_time', row_field: 'pending_time_string'};
					}else{
						delete ogTasks.TotalCols.pendingTime;				
					}
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowTimePending', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
				}
			},
			time_worked: {
		        text: lang('worked time'),
				checked: (ogTasks.userPreferences.showTimeWorked == 1),
				checkHandler: function() {
					if(this.checked){
						ogTasks.TotalCols.workedTime = {title: 'worked', group_total_field: 'worked_time', row_field: 'worked_time_string'};
					}else{
						delete ogTasks.TotalCols.workedTime;				
					}
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowTimeWorked', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
				}
			},
			percent_completed_bar: {
		        text: lang('percent completed'),
				checked: (ogTasks.userPreferences.showPercentCompletedBar == 1),
				checkHandler: function() {
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowPercentCompletedBar', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
				}
			},			
			show_quick_edit: {
		        text: lang('quick edit'),
				checked: (ogTasks.userPreferences.showQuickEdit == 1),
				checkHandler: function() {
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowQuickEdit', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
				}
			},
			show_quick_complete: {
		        text: lang('quick complete'),
				checked: (ogTasks.userPreferences.showQuickComplete == 1),
				checkHandler: function() {
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowQuickComplete', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
				}
			},
			show_quick_comment: {
		        text: lang('quick comment'),
				checked: (ogTasks.userPreferences.showQuickComment == 1),
				checkHandler: function() {
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowQuickComment', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
				}
			},
			show_quick_add_sub_tasks: {
		        text: lang('quick add sub tasks'),
				checked: (ogTasks.userPreferences.showQuickAddSubTasks == 1),
				checkHandler: function() {
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowQuickAddSubTasks', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
				}
			},
			show_classification: {
		        text: lang('classified under'),
				checked: (ogTasks.userPreferences.showClassification == 1),
				checkHandler: function() {
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowClassification', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
				}
			},
			previous_pending_tasks: {
		        text: lang('previous pending tasks'),
				checked: (ogTasks.userPreferences.previousPendingTasks == 1),
				checkHandler: function() {
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;					
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksPreviousPendingTasks', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
										
				},
				hidden: (!og.config.use_tasks_dependencies)
			},
			show_subtasks_structure: {
		        text: lang('subtasks structure'),
				checked: (ogTasks.userPreferences.showSubtasksStructure == 1),
				checkHandler: function() {
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowSubtasksStructure', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;					
				}
			}
		};
	var menu_items =  [
			    this.displayOptions.by,
				this.displayOptions.time,
				this.displayOptions.dates_start,
				this.displayOptions.dates_end,
				this.displayOptions.empty_milestones,
                this.displayOptions.time_estimates,
                this.displayOptions.time_pending,
                this.displayOptions.time_worked,
                this.displayOptions.percent_completed_bar,                
                this.displayOptions.show_quick_edit,
                this.displayOptions.show_quick_complete,             
                this.displayOptions.show_quick_add_sub_tasks,
                this.displayOptions.show_classification,
                this.displayOptions.previous_pending_tasks,
                this.displayOptions.show_subtasks_structure
			];

	// dimension columns
	for (did in og.dimensions) {
		if (isNaN(did)) continue;
		var key = 'lp_dim_' + did + '_show_as_column';
		if (og.preferences['listing_preferences'][key]) {
			menu_items.push({
				text: og.dimensions_info[did].name,
				value: parseInt(did),
				checked: (ogTasks.userPreferences.showDimensionCols.indexOf(parseInt(did)) != -1),
				checkHandler: function() {
					var dim_index = ogTasks.userPreferences.showDimensionCols.indexOf(parseInt(this.value));
					if(dim_index != -1){
						ogTasks.userPreferences.showDimensionCols.splice(dim_index, 1);
					}else{
						ogTasks.userPreferences.showDimensionCols.push(parseInt(this.value));
					}

					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowDimensionCols', value:ogTasks.userPreferences.showDimensionCols.toString()});
					og.openLink(url,{hideLoading:true});
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;			
				}				
			});
			og.breadcrumbs_skipped_dimensions[did] = did;
		}
	}

	this.show_menu = new Ext.Action({
	       	iconCls: 'op-ico-details',
			text: lang('show'),
			menu: {items: menu_items}
		});
		
	this.add(this.show_menu);
	
    this.add('-');
    
    this.add(new Ext.Action({
      id: 'button-print',
      text: lang('print'),
      tooltip: lang('print all groups'),
      iconCls: 'ico-print',
      handler: function() {
    	    
    		var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
    		if (!bottomToolbar) return;
    		var filters = bottomToolbar.getFilters();
    		
    		if(bottomToolbar.groupcombo){
    			filters.tasksGroupBy = bottomToolbar.groupcombo.value;
    		}	
    		if(bottomToolbar.ordercombo){
    			filters.tasksOrderBy = bottomToolbar.ordercombo.value;
    		}
    		
    		filters.draw_options = Ext.util.JSON.encode(this.getDrawOptions());
    		filters.tasks_list_cols = Ext.util.JSON.encode(ogTasks.TasksList.tasks_list_cols);
    		
    		var row_total_cols = [];
    		for (var key in ogTasks.TotalCols){
    			row_total_cols.push({row_field: ogTasks.TotalCols[key].row_field});
    		}
    		filters.row_total_cols = Ext.util.JSON.encode(row_total_cols);
    		
    		og.openLink(og.getUrl('task', 'print_tasks_list'), {
    			preventPanelLoad: true,
				hideLoading: false,
				scope: this,
				post: filters,
				callback: function(success, data) {
					//console.log(html.current.data);
					var html = data.current.data;
					
					var printWindow = ogTasks.createPrintWindow();
				 	printWindow.document.write(html);
				 	ogTasks.closePrintWindow(printWindow);
				 	
				}
    		});
      },
      scope: this
    }));
    
    
    Ext.get('button-print').set({
    	id: "tasks_print_btn"
    });


    
    
    if (ogTasks.extraTopToolbarItems) {
    	for (i=0; i<ogTasks.extraTopToolbarItems.length; i++) {
    		this.add(ogTasks.extraTopToolbarItems[i]);
    	}
    }
};

function ogTasksLoadFilterValuesCombo(newValue){
	var combo = Ext.getCmp('ogTasksFilterValuesCombo');
}

function ogTasksOrderUsers(usersList){
	for (var i = 0; i < usersList.length - 1; i++)
		for (var j = i+1; j < usersList.length; j++)
			if (usersList[i][1].toUpperCase() > usersList[j][1].toUpperCase()){
				var aux = usersList[i];
				usersList[i] = usersList[j];
				usersList[j] = aux;
			}
	return usersList;
}

Ext.extend(og.TasksTopToolbar, Ext.Toolbar, {
	getDrawOptions : function(){
		return {
			show_by : this.show_menu.items[0].menu.items.items[0].checked,
			show_time : this.show_menu.items[0].menu.items.items[1].checked,
			show_start_dates : this.show_menu.items[0].menu.items.items[2].checked,
			show_end_dates : this.show_menu.items[0].menu.items.items[3].checked,
			show_ms : this.show_menu.items[0].menu.items.items[4].checked,
            show_time_estimates : this.show_menu.items[0].menu.items.items[5].checked,            
            show_time_pending : this.show_menu.items[0].menu.items.items[6].checked,
            show_time_worked : this.show_menu.items[0].menu.items.items[7].checked,            
            show_percent_completed_bar : this.show_menu.items[0].menu.items.items[8].checked,
            show_quick_edit : this.show_menu.items[0].menu.items.items[9].checked,
            show_quick_complete : this.show_menu.items[0].menu.items.items[10].checked,
            show_quick_add_sub_tasks : this.show_menu.items[0].menu.items.items[11].checked,
            show_classification : this.show_menu.items[0].menu.items.items[12].checked,
            show_previous_pending_tasks : this.show_menu.items[0].menu.items.items[13].checked,
            show_subtasks_structure : this.show_menu.items[0].menu.items.items[14].checked,
            show_dimension_cols : ogTasks.userPreferences.showDimensionCols
		}
	},
	updateCheckedStatus : function(){
		var checked = false;
		var allIncomplete = true, anyIncomplete = false, allUnread = true, allRead = true;
		
		for(var prop in ogTasksCache.Tasks) {
			var task = ogTasksCache.Tasks[prop];
			if (task.isChecked) {
				checked = true;
				if (task.status == 1) {
					allIncomplete = false;
				} else {
					anyIncomplete = true;
				}
				if (task.isRead) {
					allUnread = false;
				} else {
					allRead = false;
				}
			}
		    
		}
				
		if (!checked){
			this.actions.del.disable();
			this.actions.complete.disable();
			this.actions.archive.disable();
			this.markactions.markAsRead.disable();
			this.markactions.markAsUnread.disable();
		} else {
			this.actions.del.enable();			
			this.actions.archive.enable();
			if (allUnread) {
				this.markactions.markAsUnread.disable();
			} else {
				this.markactions.markAsUnread.enable();
			}
			if (allRead) {
				this.markactions.markAsRead.disable();
			} else {
				this.markactions.markAsRead.enable();
			}
			if (anyIncomplete) {
				this.actions.complete.enable();
			} else {
				this.actions.complete.disable();
			}
				
		}
		
		og.eventManager.fireEvent('task list updateCheckedStatus', {checked:checked});
		
	}
});

Ext.reg("tasksTopToolbar", og.TasksTopToolbar);
