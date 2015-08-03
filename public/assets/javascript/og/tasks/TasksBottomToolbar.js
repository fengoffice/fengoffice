/**
 *  TaskManager
 *
 */

og.TasksBottomToolbar = function(config) {
	Ext.applyIf(config,
		{
			id:"tasksPanelBottomToolbarObject",
			renderTo: "tasksPanelBottomToolbar",
			style:"border:0px none; padding-top:0; padding-left:5px;"
		});
		
	og.TasksBottomToolbar.superclass.constructor.call(this, config);
	
	var groupcombo_store_data = [
		['nothing', '--' + lang('nothing (groups)') + '--']
		,['milestone', lang('milestone')]
		,['priority',lang('priority')]
		,['assigned_to', lang('assigned to')]
		,['due_date', lang('due date')]
		,['start_date', lang('start date')]
		,['created_on', lang('created on')]
		,['created_by', lang('created by')]
		,['completed_on', lang('completed on')]
		,['completed_by', lang('completed by')]
		,['status', lang('status')]
	];
	
	if (!og.config.use_milestones) {
		// remove milestone option from group by select options
		for (var x=0; x<groupcombo_store_data.length; x++) {
			if (groupcombo_store_data[x][0] == 'milestone') {
				groupcombo_store_data.splice(x, 1);
			}
		}
	}
	
	if (ogTasks.additional_groupby_dimensions) {
		for (i=0; i<ogTasks.additional_groupby_dimensions.length; i++) {
			var gb = ogTasks.additional_groupby_dimensions[i];
			var found = false;
			for (k=0; k<groupcombo_store_data.length; k++) {
				gsd = groupcombo_store_data[k];
				found = gsd[0] == 'dimension_' + gb.id;
				if (found) break;
			}
			if (!found) groupcombo_store_data.push(['dimension_' + gb.id, gb.name]);
		}
	}
	
    this.groupcombo = new Ext.form.ComboBox({
    	id: 'ogTasksGroupByCombo',
        store: new Ext.data.SimpleStore({
        	fields: ['value', 'text'],
        	data : groupcombo_store_data
    	}),
        displayField:'text',
        //typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:120,
        valueField: 'value',
        listeners: {
        	'select' : function(combo, record) {
        		ogTasks.setAllCheckedValue(false);
        		ogTasks.setAllExpandedValue(false);
        		ogTasks.expandedGroups = [];
				
        		var url = og.getUrl('account', 'update_user_preference', {name: 'tasksGroupBy', value:record.data.value});
				og.openLink(url,{hideLoading:true});
				
				ogTasks.draw();
        	}
        }
    });
    this.groupcombo.setValue(ogTasks.userPreferences.groupBy);
	
    this.ordercombo = new Ext.form.ComboBox({
    	id: 'ogTasksOrderByCombo',
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : [['priority',lang('priority')]
	        	,['name', lang('task name')]
	        	,['due_date', lang('due date')]
	        	,['created_on', lang('created on')]
	        	,['completed_on', lang('completed on')]
	        	,['assigned_to', lang('assigned to')]
	        	,['start_date', lang('start date')]
	        	,['percent_completed', lang('progress')]]
	    	}),
        displayField:'text',
        //typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:120,
        valueField: 'value',
        listeners: {
        	'select' : function(combo, record) {
				ogTasks.redrawGroups = false;
				
				ogTasks.redrawGroups = true;
				var url = og.getUrl('account', 'update_user_preference', {name: 'tasksOrderBy', value:record.data.value});
				og.openLink(url,{hideLoading:true});
				
				ogTasks.draw();
        	}
        }
    });
    this.ordercombo.setValue(ogTasks.userPreferences.orderBy);
    
    var filtercombo_store_data = [['no_filter','--' + lang('no filter') + '--']
		,['created_by',lang('created by')]
		,['completed_by', lang('completed by')]
		,['assigned_to', lang('assigned to')]
		,['assigned_by', lang('assigned by')]
		,['milestone', lang('milestone')]
		,['priority', lang('priority')]
		,['subscribed_to', lang('subscribed to')]
	];
    
    if (!og.config.use_milestones) {
		// remove milestone option from group by select options
		for (var x=0; x<filtercombo_store_data.length; x++) {
			if (filtercombo_store_data[x][0] == 'milestone') {
				filtercombo_store_data.splice(x, 1);
			}
		}
	}
    
    this.filtercombo = new Ext.form.ComboBox({
    	id: 'ogTasksFilterCombo',
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : filtercombo_store_data
	    }),
        displayField:'text',
        //typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:100,
        valueField: 'value',
        listeners: {
        	'select' : function(combo, record) {
        		switch(record.data.value){
        			case 'no_filter':
        				Ext.getCmp('ogTasksFilterNamesCombo').hide();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').hide();
        				Ext.getCmp('ogTasksFilterMilestonesCombo').hide();
        				Ext.getCmp('ogTasksFilterPriorityCombo').hide();
        				Ext.getCmp('ogTasksFilterSubtypeCombo').hide();
						var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
        				toolbar.load();
        				break;
        			case 'milestone':
        				Ext.getCmp('ogTasksFilterNamesCombo').hide();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').hide();
        				Ext.getCmp('ogTasksFilterMilestonesCombo').show();
        				Ext.getCmp('ogTasksFilterMilestonesCombo').setValue('');
        				Ext.getCmp('ogTasksFilterPriorityCombo').hide();
        				Ext.getCmp('ogTasksFilterSubtypeCombo').hide();
        				break;
        			case 'priority':
        				Ext.getCmp('ogTasksFilterNamesCombo').hide();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').hide();
        				Ext.getCmp('ogTasksFilterMilestonesCombo').hide();
        				Ext.getCmp('ogTasksFilterPriorityCombo').show();
        				Ext.getCmp('ogTasksFilterPriorityCombo').setValue('');
        				Ext.getCmp('ogTasksFilterSubtypeCombo').hide();
        				break;
        			case 'assigned_to':
        				Ext.getCmp('ogTasksFilterNamesCombo').hide();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').show();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').setValue('');
        				Ext.getCmp('ogTasksFilterMilestonesCombo').hide();
        				Ext.getCmp('ogTasksFilterPriorityCombo').hide();
        				Ext.getCmp('ogTasksFilterSubtypeCombo').hide();
        				break;
        			case 'subtype':
        				Ext.getCmp('ogTasksFilterNamesCombo').hide();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').hide();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').setValue('');
        				Ext.getCmp('ogTasksFilterMilestonesCombo').hide();
        				Ext.getCmp('ogTasksFilterPriorityCombo').hide();
        				Ext.getCmp('ogTasksFilterSubtypeCombo').show();
        				break;
        			default:
        				Ext.getCmp('ogTasksFilterNamesCombo').show();
        				Ext.getCmp('ogTasksFilterNamesCombo').setValue('');
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').hide();
        				Ext.getCmp('ogTasksFilterMilestonesCombo').hide();
        				Ext.getCmp('ogTasksFilterPriorityCombo').hide();
        				Ext.getCmp('ogTasksFilterSubtypeCombo').hide();
        				break;
        		}
        	}
        }
    });
    this.filtercombo.setValue(ogTasks.userPreferences.filter);

    
    
    var currentUser = '';
    var usersArray = Ext.util.JSON.decode(document.getElementById(config.usersHfId).value);
    var companiesArray = Ext.util.JSON.decode(document.getElementById(config.companiesHfId).value);
    for (var i=0; i<usersArray.length; i++){
		if (usersArray[i].isCurrent) {
			currentUser = usersArray[i].id;
		}
	}
	var ucsData = [[currentUser, lang('me')],['0',lang('everyone')],['-1', lang('unassigned')],['0','--']];
	
	ucsOtherUsers = [];
	for (var i=0; i<usersArray.length; i++){
		var companyName = '';
		var j;
		for (var j=0; j<companiesArray.length; j++){
			if (companiesArray[j] && companiesArray[j].id == usersArray[i].cid) {
				companyName = companiesArray[j].name;
				break;
			}
		}
		if (usersArray[i] && typeof(usersArray[i]) != 'function') {
			var toshow = og.clean(usersArray[i].name) + (usersArray[i].cid ? ' : ' + og.clean(companyName) : "");
			ucsOtherUsers[ucsOtherUsers.length] = [usersArray[i].id, toshow];
		}
		if (usersArray[i].isCurrent) {
			currentUser = usersArray[i].id;
		}
	}
	
	var compData = [];
	if (og.config.can_assign_tasks_to_companies) {
		compData = compData.concat([['0','--']]);
		for (var i=0; i<companiesArray.length; i++){
			if (companiesArray[i].id) compData[compData.length] = [companiesArray[i].id, og.clean(companiesArray[i].name)];
		}
	}
	
	//ucsData = ucsData.concat(ogTasksOrderUsers(ucsOtherUsers)).concat(compData);
	ucsData = ucsData.concat(ucsOtherUsers).concat(compData);
    this.filterNamesCompaniesCombo = new Ext.form.ComboBox({
    	id: 'ogTasksFilterNamesCompaniesCombo',
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : ucsData
	    }),
	    hidden: ogTasks.userPreferences.filter != 'assigned_to',
        displayField:'text',
        //typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:140,
        listWidth: 'auto',
        valueField: 'value',
        emptyText: (lang('select user or group') + '...'),
        valueNotFoundText: '',
        listeners: {
        	'blur' : function(combo) {
        		if (combo.el.dom.value == "") {
        			combo.setValue(0);
        			var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
            		if (toolbar.filterNamesCompaniesCombo == this) {
            			toolbar.load();
            		}
        		}
        	},
        	'select' : function(combo, record) {
				var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
        		if (toolbar.filterNamesCompaniesCombo == this) {
        			toolbar.load();
        		}else{
        			if (this.initialConfig.isInternalSelector) {
        				ogTasks.UserCompanySelected(this.initialConfig.controlName, record.data.value, this.initialConfig.taskId);
        			}
        		}
        	}
        }
    });
    this.filterNamesCompaniesCombo.setValue(ogTasks.userPreferences.filterValue);
    
    for (var i=0; i<usersArray.length; i++){
		if (usersArray[i].isCurrent)
			currentUser = usersArray[i].id;
	}
	var uData = [[currentUser, lang('me')],['0',lang('everyone')],['0','--']];
	uDOtherUsers = [];
	for (var i=0; i<usersArray.length; i++){
		if (usersArray[i] && !usersArray[i].isCurrent && usersArray[i].id) {
			var companyName = '';
			var j;
			for (var j=0; j<companiesArray.length; j++){
				if (companiesArray[j] && companiesArray[j].id == usersArray[i].cid) {
					companyName = companiesArray[j].name;
					break;
				}
			}

			var toshow = og.clean(usersArray[i].name) + (usersArray[i].cid ? ' : ' + og.clean(companyName) : "");
			uDOtherUsers[uDOtherUsers.length] = [usersArray[i].id, toshow];
		}
	}
	uData = uData.concat(uDOtherUsers).concat(compData);
    this.filterNamesCombo = new Ext.form.ComboBox({
    	id: 'ogTasksFilterNamesCombo',
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : uData
	    }),
	    hidden: (ogTasks.userPreferences.filter == 'milestone' || ogTasks.userPreferences.filter == 'priority' || ogTasks.userPreferences.filter == 'assigned_to' || ogTasks.userPreferences.filter == 'subtype' || ogTasks.userPreferences.filter == 'no_filter'),
        displayField:'text',
        //typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:140,
        listWidth: 'auto',
        valueField: 'value',
        emptyText: (lang('select user or group') + '...'),
        valueNotFoundText: '',
        listeners: {
        	'select' : function(combo, record) {
				var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
        		toolbar.load();
        	}
		}
	});
    this.filterNamesCombo.setValue(ogTasks.userPreferences.filterValue);
    
    this.filterPriorityCombo = new Ext.form.ComboBox({
    	id: 'ogTasksFilterPriorityCombo',
        store: new Ext.data.SimpleStore({
			fields: ['value', 'text'],
			data : [[100, lang('low')],[200, lang('normal')],[300, lang('high')],[400, lang('urgent')]]
	    }),
	    hidden: ogTasks.userPreferences.filter != 'priority',
        displayField:'text',
        //typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:140,
        valueField: 'value',
        emptyText: (lang('select priority') + '...'),
        valueNotFoundText: '',
        listeners: {
        	'select' : function(combo, record) {
				var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
        		if (toolbar.filterPriorityCombo == this)
        			toolbar.load();
        	}
        }
    });
    this.filterPriorityCombo.setValue(ogTasks.userPreferences.filterValue);
    
    var subtypesArray = Ext.util.JSON.decode(document.getElementById(config.subtypesHfId).value);
    var subtypes_data = [[0, lang('all')]];
    for (i=0; i<subtypesArray.length; i++) {
    	var ost = subtypesArray[i];
    	subtypes_data[subtypes_data.length] = [ost.id, ost.name];
    }
    this.filterSubtypeCombo = new Ext.form.ComboBox({
    	id: 'ogTasksFilterSubtypeCombo',
        store: new Ext.data.SimpleStore({
			fields: ['value', 'text'],
			data : subtypes_data
	    }),
	    hidden: ogTasks.userPreferences.filter != 'subtype',
        displayField:'text',
        //typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:140,
        valueField: 'value',
        emptyText: '...',
        valueNotFoundText: '',
        listeners: {
        	'select' : function(combo, record) {
				var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
        		if (toolbar.filterSubtypeCombo == this)
        			toolbar.load();
        	}
        }
    });
    this.filterSubtypeCombo.setValue(ogTasks.userPreferences.filterValue);
    
    
    var milestones = Ext.util.JSON.decode(document.getElementById(config.internalMilestonesHfId).value);
    milestones = milestones.concat(Ext.util.JSON.decode(document.getElementById(config.externalMilestonesHfId).value));
    milestonesData = [[0,"--" + lang('none') + "--"]];
    for (var i=0; i<milestones.length; i++){
    	if (milestones[i].id)
    		milestonesData[milestonesData.length] = [milestones[i].id, og.clean(milestones[i].t)];
    }
    this.filterMilestonesCombo = new Ext.form.ComboBox({
    	id: 'ogTasksFilterMilestonesCombo',
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : milestonesData,
	        sortInfo: {field:'text',direction:'ASC'}
	    }),
	    hidden: (ogTasks.userPreferences.filter != 'milestone'),
        displayField:'text',
        //typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:140,
        valueField: 'value',
        emptyText: (lang('select milestone') + '...'),
        valueNotFoundText: '',
        listeners: {
        	'select' : function(combo, record) {
				var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
        		if (toolbar.filterMilestonesCombo == this)
        			toolbar.load();
        	}
        }
    });
    this.filterMilestonesCombo.setValue(ogTasks.userPreferences.filterValue);
	
	
    this.statusCombo = new Ext.form.ComboBox({
    	id: 'ogTasksStatusCombo',
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : [[2, '--' + lang('no filter') + '--'],[0, lang('pending')],[1, lang('complete')], [10, lang('active')], [11, lang('overdue')], [12, lang('today')], [13, lang('overdue')+"+"+lang('today')]]
	    }),
        displayField:'text',
        //typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:120,
        valueField: 'value',
        listeners: {
        	'select' : function(combo, record) {
				var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
        		toolbar.load();
        	}
        }
    });
    // DatePicker Menu  
    this.dateFieldStart = new og.DateField({
		displayField : 'text',
		emptyText : og.preferences['date_format_tip'],
		name : 'ogTasksDateFieldStart',
		id : 'ogTasksDateFieldStart',
		allowBlank : true,
		value : '',
		listeners : {
			'change' : function(A, newValue, oldValue) {
				
				var to_date = null;
				var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
				var dateFieldEnd = Ext.getCmp('ogTasksDateFieldEnd');
				
				if (newValue == '') {
					this.setValue('');
					var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
					toolbar.load({resetDateStart : oldValue});
				} else {
					var from_date = newValue.format(og.preferences['date_format']);
					if (dateFieldEnd.getValue() != '') {
						to_date = dateFieldEnd.getValue().format(og.preferences['date_format']);
						toolbar.load({
							from_date : from_date,
							to_date : to_date
						});
					} else {
						toolbar.load({
							from_date : from_date
						});
					}
				}
			}
		},
		menuListeners : {
			select : function(A, B) {
				this.setValue(B);
				var from_date = B.format(og.preferences['date_format']);
				var to_date = null;
				var dateFieldEnd = Ext.getCmp('ogTasksDateFieldEnd');
				var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
				if (dateFieldEnd.getValue() != '') {
					to_date = dateFieldEnd.getValue().format(og.preferences['date_format']);
					toolbar.load({
						from_date : from_date,
						to_date : to_date
					});
				} else {
					toolbar.load({
						from_date : from_date
					});
				}
				this.setValue(B);
			}
		}
	});

	this.dateFieldEnd = new og.DateField({
		emptyText : og.preferences['date_format_tip'],
		name : 'ogTasksDateFieldEnd',
		id : 'ogTasksDateFieldEnd',
		value : '',
		listeners : {
			'change' : function(A, newValue, oldValue) {
				var from_date = null;
				var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
				var dateFieldStart = Ext.getCmp('ogTasksDateFieldStart');

				if (newValue == '') {
					this.setValue('');
					var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
					toolbar.load({resetDateEnd : oldValue});
				} else {
					var to_date = newValue.format(og.preferences['date_format']);
					if (dateFieldStart.getValue() != '') {
						from_date = dateFieldStart.getValue().format(og.preferences['date_format']);
						toolbar.load({
							from_date : from_date,
							to_date : to_date
						});
					} else {
						toolbar.load({
							to_date : to_date
						});
					}
				}
			}
		},
		menuListeners : {
			select : function(A, B) {
				this.setValue(B);
				to_date = B.format(og.preferences['date_format']);
				from_date = null;

				var dateFieldStart = Ext.getCmp('ogTasksDateFieldStart');
				var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
				if (dateFieldStart.getValue() != '') {

					from_date = dateFieldStart.getValue().format(og.preferences['date_format']);
					toolbar.load({
						from_date : from_date,
						to_date : to_date
					});
				} else {
					toolbar.load({
						to_date : to_date
					});
				}
				this.setValue(B);
			}

		}
	});
    this.dateFieldEnd.setValue(ogTasks.userPreferences.dateEnd); 	
    this.dateFieldStart.setValue(ogTasks.userPreferences.dateStart);
    this.statusCombo.setValue(ogTasks.userPreferences.status);
    this.add(lang('filter') + ':');
    this.add(this.filtercombo);
    this.add(this.filterNamesCombo);
    this.add(this.filterNamesCompaniesCombo);
    this.add(this.filterPriorityCombo);
    this.add(this.filterSubtypeCombo);
    this.add(this.filterMilestonesCombo);
    this.add('&nbsp;&nbsp;&nbsp;' + lang('status') + ':');
    this.add(this.statusCombo);
    
	this.add('&nbsp;&nbsp;&nbsp;' + lang('group by') + ':');
    this.add(this.groupcombo);
    this.add('&nbsp;&nbsp;&nbsp;' + lang('order by') + ':');
    this.add(this.ordercombo);
    this.add('&nbsp;&nbsp;&nbsp;' + lang('from date') + ':');
    this.add(this.dateFieldStart);
    this.add('&nbsp;&nbsp;&nbsp;' + lang('to date') + ':');
    this.add(this.dateFieldEnd);
    
    if (ogTasks.extraBottomToolbarItems) {
    	for (i=0; i<ogTasks.extraTopToolbarItems.length; i++) {
    		this.add(ogTasks.extraTopToolbarItems[i]);
    	}
    }
};

Ext.extend(og.TasksBottomToolbar, Ext.Toolbar, {
	load: function(params) {
		if (!params) params = {};
		Ext.apply(params,this.getFilters());
		og.openLink(og.getUrl('task','new_list_tasks',params));
	},
	getDisplayCriteria : function(){
		return {
			group_by : this.groupcombo.getValue(),
			order_by : this.ordercombo.getValue()
		}
	},
	getFilters : function(){
		var filterValue;
		switch(this.filtercombo.getValue()){
			case 'milestone':
				filterValue = this.filterMilestonesCombo.getValue();
				break;
			case 'priority':
				filterValue = this.filterPriorityCombo.getValue();
				break;
			case 'subtype':
				filterValue = this.filterSubtypeCombo.getValue();
				break;
			case 'assigned_to':
				filterValue = this.filterNamesCompaniesCombo.getValue();
				break;
			default:
				filterValue = this.filterNamesCombo.getValue();
				break;
		}		
		return {
			status : this.statusCombo.getValue(),
			filter : this.filtercombo.getValue(),
			fval : filterValue
		}
	},
	cloneUserCompanyCombo : function(newId){
		var clone = this.filterNamesCompaniesCombo.cloneConfig({id:newId});
		
		return clone;
	}
	 
});

Ext.reg("TasksBottomToolbar", og.TasksBottomToolbar);