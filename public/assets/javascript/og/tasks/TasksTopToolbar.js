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
									og.render_modal_form('', {c:'template', a:'instantiate_parameters', params: {id: tid}, 
										overlayClose:false, escClose:false, hideCloseIcon:false
									});
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
		id: 'new_button_task',
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
	
	if (og.replace_list_new_action && og.replace_list_new_action.task) {
		for (var k=0; k<og.replace_list_new_action.task.menu.items.items.length; k++) {
			var act = new Ext.Action(og.replace_list_new_action.task.menu.items.items[k].initialConfig);
			menuItems.push(act);
		}
	}

	if (og.config.use_milestones) {
		menuItems = menuItems.concat([{
			text: lang('new milestone'),
			iconCls: 'ico-milestone',
			handler: function() {
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
	

	if(og.loggedUser.can_instantiate_templates){
		menuItems = menuItems.concat([{
			text: lang('templates'),
			iconCls: 'ico-template',
			cls: 'scrollable-menu',
			menu: {
				items: allTemplates
			}}]);
	}


	
	
	var butt = new Ext.Button({
		iconCls: 'ico-new',
		text: lang('new'),
		id: 'new_menu_task',
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

	if(ogTasks.additional_mark_actions){
		for(var i = 0; i < ogTasks.additional_mark_actions.length; i++){
			var action = ogTasks.additional_mark_actions[i];
			var action_name = action['action_name'];
			markactions[action_name] = new Ext.Action({
				text: action['text'],
				tooltip: action['tooltip'],
				iconCls: action['iconCls'],
				handler: action['handler'],
				scope: this	
			});
		}
	}

	this.markactions = markactions;

	this.markactions_menuitems = [
		markactions.markAsRead,
		markactions.markAsUnread
	];
	if (markactions.markAsBillable) this.markactions_menuitems.push(markactions.markAsBillable);
	if (markactions.markAsNonBillable) this.markactions_menuitems.push(markactions.markAsNonBillable);
	
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
			menu: this.markactions_menuitems
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
				hideOnClick: false,	           
				checkHandler: function() {
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowAssignedBy', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);
				}
			},
			time: {
				hidden: (typeof(ogTasks.userPreferences.showTime) == "undefined"),
                                text: lang('time'),
				checked: (ogTasks.userPreferences.showTime == 1),
				hideOnClick: false,
				checkHandler: function() {
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowTime', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);
				}
			},
            time_quick: {
				hidden: (typeof(ogTasks.userPreferences.showTimeQuick) == "undefined"),
                                text: lang('quick time'),
				checked: (ogTasks.userPreferences.showTimeQuick == 1),
				hideOnClick: false,
				checkHandler: function() {
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowTimeQuick', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);
				}
			},
			dates_start: {
		        text: lang('start date'),
				checked: (ogTasks.userPreferences.showStartDates == 1),
				hideOnClick: false,
				checkHandler: function() {
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowStartDates', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);
				}
			},
			dates_end: {
		        text: lang('due date'),
				checked: (ogTasks.userPreferences.showEndDates == 1),
				hideOnClick: false,
				checkHandler: function() {
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowEndDates', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);
				}
			},
			empty_milestones: {
		        text: lang('empty milestones'),
				checked: (ogTasks.userPreferences.showEmptyMilestones == 1),
				hideOnClick: false,
				checkHandler: function() {
					ogTasks.userPreferences.showEmptyMilestones = 1 - ogTasks.userPreferences.showEmptyMilestones;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowEmptyMilestones', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);				
				},
				hidden: (!og.config.use_milestones)
			},
            time_estimates: {
		        text: lang('estimated time'),
				checked: (ogTasks.userPreferences.showTimeEstimates == 1),
				hideOnClick: false,
				checkHandler: function() {
					if(this.checked){
						ogTasks.TotalCols.estimatedTime = {title: 'estimated', group_total_field: 'TimeEstimate', row_field: 'estimatedTime'};
					}else{
						delete ogTasks.TotalCols.estimatedTime;				
					}					
					
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowTimeEstimates', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);
				}
			},
			total_time_estimates: {
		        text: lang('total estimated time'),
				checked: (ogTasks.userPreferences.showTotalTimeEstimates == 1),
				hideOnClick: false,
				checkHandler: function() {
					if(this.checked){
						ogTasks.TotalCols.estimatedTime = {title: 'total estimated', group_total_field: 'TotalTimeEstimate', row_field: 'totalTimeEstimateString'};
					}else{
						delete ogTasks.TotalCols.estimatedTime;				
					}					
					
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowTotalTimeEstimates', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);
				}
			},
			time_pending: {
		        text: lang('pending time'),
				checked: (ogTasks.userPreferences.showTimePending == 1),
				hideOnClick: false,
				checkHandler: function() {
					if(this.checked){
						ogTasks.TotalCols.pendingTime = {title: 'pending', group_total_field: 'pending_time', row_field: 'pending_time_string'};
					}else{
						delete ogTasks.TotalCols.pendingTime;				
					}
					
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowTimePending', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);
				}
			},
			time_worked: {
		        text: lang('worked time'),
				checked: (ogTasks.userPreferences.showTimeWorked == 1),
				hideOnClick: false,
				checkHandler: function() {
					if(this.checked){
						ogTasks.TotalCols.workedTime = {title: 'worked', group_total_field: 'worked_time', row_field: 'worked_time_string'};
					}else{
						delete ogTasks.TotalCols.workedTime;				
					}
					
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowTimeWorked', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);
				}
			},
			total_worked_time: {
				text: lang('total worked time'),
				checked: (ogTasks.userPreferences.showTotalTimeWorked == 1),
				hideOnClick: false,	
				checkHandler: function() {
					if(this.checked){
						ogTasks.TotalCols.totalWorkedTime = {title: 'total worked', group_total_field: 'overall_worked_time', row_field: 'overall_worked_time_string'};
					}else{
						delete ogTasks.TotalCols.totalWorkedTime;				
					}
					
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowTotalTimeWorked', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);
				}
											
			},
			percent_completed_bar: {
		        text: lang('percent completed'),
				checked: (ogTasks.userPreferences.showPercentCompletedBar == 1),
				hideOnClick: false,
				checkHandler: function() {
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowPercentCompletedBar', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);
				}
			},			
			show_quick_edit: {
		        text: lang('quick edit'),
				checked: (ogTasks.userPreferences.showQuickEdit == 1),
				hideOnClick: false,
				checkHandler: function() {
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowQuickEdit', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);
				}
			},
			show_quick_mark_as_started: {
		        text: lang('quick mark as started'),
				checked: (parseInt(ogTasks.userPreferences.showQuickMarkAsStarted) == 1),
				hideOnClick: false,
				checkHandler: function() {
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowQuickMarkAsStarted', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);				
				}
			},
			show_quick_complete: {
		        text: lang('quick complete'),
				checked: (ogTasks.userPreferences.showQuickComplete == 1),
				hideOnClick: false,
				checkHandler: function() {
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowQuickComplete', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);
				}
			},
			show_quick_comment: {
		        text: lang('quick comment'),
				checked: (ogTasks.userPreferences.showQuickComment == 1),
				hideOnClick: false,
				checkHandler: function() {
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowQuickComment', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);
				}
			},
			show_quick_add_sub_tasks: {
		        text: lang('quick add sub tasks'),
				checked: (ogTasks.userPreferences.showQuickAddSubTasks == 1),
				hideOnClick: false,
				checkHandler: function() {
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowQuickAddSubTasks', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);
				}
			},
			show_classification: {
		        text: lang('classified under'),
				checked: (ogTasks.userPreferences.showClassification == 1),
				hideOnClick: false,
				checkHandler: function() {
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowClassification', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);
				}
			},
			previous_pending_tasks: {
		        text: lang('previous pending tasks'),
				checked: (ogTasks.userPreferences.previousPendingTasks == 1),
				hideOnClick: false,
				checkHandler: function() {
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksPreviousPendingTasks', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);
										
				},
				hidden: (!og.config.use_tasks_dependencies)
			},
			show_subtasks_structure: {
		        text: lang('subtasks structure'),
				checked: (ogTasks.userPreferences.showSubtasksStructure == 1),
				hideOnClick: false,
				checkHandler: function() {
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowSubtasksStructure', value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);		
				}
			}
		};
	var menu_items =  [
			    this.displayOptions.by,
				this.displayOptions.time,
				this.displayOptions.time_quick,
				this.displayOptions.dates_start,
				this.displayOptions.dates_end,
				this.displayOptions.empty_milestones,
                this.displayOptions.time_estimates,
				this.displayOptions.total_time_estimates,
                this.displayOptions.time_pending,
                this.displayOptions.time_worked,
				this.displayOptions.total_worked_time,
                this.displayOptions.percent_completed_bar,                
                this.displayOptions.show_quick_edit,
                this.displayOptions.show_quick_mark_as_started,
                this.displayOptions.show_quick_complete,             
                this.displayOptions.show_quick_add_sub_tasks,
                this.displayOptions.show_classification,
                this.displayOptions.previous_pending_tasks,
                this.displayOptions.show_subtasks_structure,
    ];

	for (var cp_order=0; cp_order < ogTasks.custom_properties.length; cp_order++) {
		var cp = ogTasks.custom_properties[cp_order];
		var opt_key = 'tasksShowCP_'+cp.id;
		this.displayOptions[opt_key] = {
			id: opt_key,
	        text: cp.name,
			checked: (ogTasks.userPreferences[opt_key] == 1),
			hideOnClick: false,
			checkHandler: function() {
				ogTasks.userPreferences[this.id] = (this.checked ? 1 : 0);
				var url = og.getUrl('account', 'update_user_preference', {name: this.id, value:(this.checked ? 1 : 0)});
				ogTasksMakeRequestAndReloadWithTimeout(url);
			}
		}
		menu_items.push(this.displayOptions[opt_key]);
	}
	
	// dimension columns
	for (did in og.dimensions_info) {
		if (isNaN(did)) continue;
		
		tmp_menu_items = ogTasks.createDimensionColumnMenuItems(did);
		
		if (tmp_menu_items && tmp_menu_items.length > 0) {
			menu_items = menu_items.concat(tmp_menu_items);
		}
	}
	
	if (ogTasks.additional_task_list_columns) {
		for (var i=0; i<ogTasks.additional_task_list_columns.length; i++) {
			var col = ogTasks.additional_task_list_columns[i];
			menu_items.push({
				configId: col.id,
		        text: col.name,
				checked: (ogTasks.userPreferences[col.id] == 1),
				hideOnClick: false,
				checkHandler: function() {
					var url = og.getUrl('account', 'update_user_preference', {name: this.configId, value:(this.checked?1:0)});
					ogTasksMakeRequestAndReloadWithTimeout(url);
				}
			});
		}
	}

	this.show_menu = new Ext.Action({
		id: 'table-show-columns-task',
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

function ogTasksMakeRequestAndReloadWithTimeout(url) {
	og.openLink(url,{
		hideLoading:true, 
		callback: function(success, data) {
			ogTasksWaitTimeOutAndDraw();				
		}
	});
}

function ogTasksWaitTimeOutAndDraw (){
	
	// timeout to reload the panel
	if (og.task_show_by_select_timeout) {
		clearTimeout(og.task_show_by_select_timeout);
	}

	//draw table with tasks again and hide window with selects to show
	og.task_show_by_select_timeout = setTimeout(function(){
		
		var tp = Ext.getCmp("tasks-panel");
        if (tp) tp.reset();
		/*ogTasks.redrawGroups = false;
		ogTasks.draw();
		ogTasks.redrawGroups = true;*/
		
		var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
		topToolbar.items.get('table-show-columns-task').menu.hide();
		
	}, 3000);
	
	
}


Ext.extend(og.TasksTopToolbar, Ext.Toolbar, {
	getDrawOptions : function(){
		var draw_options = {
			show_by : this.show_menu.items[0].menu.items.items[0].checked,
			show_time : this.show_menu.items[0].menu.items.items[1].checked,
			show_time_quick : this.show_menu.items[0].menu.items.items[2].checked,
			show_start_dates : this.show_menu.items[0].menu.items.items[3].checked,
			show_end_dates : this.show_menu.items[0].menu.items.items[4].checked,
			show_ms : this.show_menu.items[0].menu.items.items[5].checked,
			show_time_estimates : this.show_menu.items[0].menu.items.items[6].checked,  
			show_total_time_estimates : this.show_menu.items[0].menu.items.items[7].checked,          
			show_time_pending : this.show_menu.items[0].menu.items.items[8].checked,
			show_time_worked : this.show_menu.items[0].menu.items.items[9].checked,
			show_total_time_worked: this.show_menu.items[0].menu.items.items[10].checked,   
			show_percent_completed_bar : this.show_menu.items[0].menu.items.items[11].checked,
			show_quick_edit : this.show_menu.items[0].menu.items.items[12].checked,
			show_quick_mark_as_started : this.show_menu.items[0].menu.items.items[13].checked,
			show_quick_complete : this.show_menu.items[0].menu.items.items[14].checked,
			show_quick_add_sub_tasks : this.show_menu.items[0].menu.items.items[15].checked,
			show_classification : this.show_menu.items[0].menu.items.items[16].checked,
			show_previous_pending_tasks : this.show_menu.items[0].menu.items.items[17].checked,
			show_subtasks_structure : this.show_menu.items[0].menu.items.items[18].checked,
			show_dimension_cols : ogTasks.userPreferences.showDimensionCols            
		}
		
		var show_cp_config = {};
		var the_menu_items = this.show_menu.items[0].menu.items.items;
		for (var x=0; x<the_menu_items.length; x++) {
			var mitem = the_menu_items[x];
			if (mitem.id.indexOf('tasksShowCP_') == 0) {
				var cpid = mitem.id.replace('tasksShowCP_', '');
				show_cp_config[cpid] = mitem.checked;
			}
		}
		draw_options.tasksShowCP = show_cp_config;
		
		if (ogTasks.additional_task_list_columns) {
			for (var i=0; i<ogTasks.additional_task_list_columns.length; i++) {
				var col = ogTasks.additional_task_list_columns[i];
				draw_options[col.id] = ogTasks.userPreferences[col.id] ? true : false;
			}
		}
		return draw_options;
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
