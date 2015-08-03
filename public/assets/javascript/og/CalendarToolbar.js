
// DatePicker Menu
var tbar_datemenu = new Ext.menu.DateMenu({
    handler : function(dp, date){
    	dp.setValue(date);
    	changeView(cal_actual_view, date.format('d'), date.format('n'), date.format('Y'), actual_user_filter, actual_status_filter, actual_task_filter);
    },
    format: og.preferences['date_format'],
    startDay: og.preferences['start_monday'],
	altFormats: lang('date format alternatives')
});
og.calToolbarDateMenu = tbar_datemenu;

Ext.apply(og.calToolbarDateMenu.picker, { 
	okText: lang('ok'),
	cancelText: lang('cancel'),
	monthNames: [lang('month 1'), lang('month 2'), lang('month 3'), lang('month 4'), lang('month 5'), lang('month 6'), lang('month 7'), lang('month 8'), lang('month 9'), lang('month 10'), lang('month 11'), lang('month 12')],
	dayNames:[lang('sunday'), lang('monday'), lang('tuesday'), lang('wednesday'), lang('thursday'), lang('friday'), lang('saturday')],
	monthYearText: '',
	nextText: lang('next month'),
	prevText: lang('prev month'),
	todayText: lang('today'),
	todayTip: lang('today')
});

// Actual view
var cal_actual_view = 'viewweek';
// Actual user filter
var actual_user_filter = '0'; // 0=logged user, -1=all users
// Actual task filter
var actual_task_filter = 'no filter'; // 0=no filter
// Actual state filter
var actual_status_filter = ' 0 1 3'; // -1=all states


function changeView(action, day, month, year, u_filter, s_filter, t_filter) {
	var url = og.getUrl('event', action, {
		context: og.contextManager.plainContext(),
		day: day,
		month: month,
		year: year,
		user_filter: u_filter,
		status_filter: s_filter,
                task_filter: t_filter,
		view_type: action
	});
	og.openLink(url, null);
}


function addStateFilter(filter) {
	actual_status_filter += ' ' + filter;
}

function removeStateFilter(filter) {
	actual_status_filter = actual_status_filter.replace('/-1/', '');
	actual_status_filter = actual_status_filter.replace(' ' + filter, '');
}

og.getSelectedEventsCsv = function() {
	els = document.getElementsByName('obj_selector');
	ids = '';
	if (els.length > 0) {
		for (i=0; i<els.length; i++) {
			if (els[i].checked)
				ids += ',' + els[i].id.substr(4);
		}
		ids = ids.substr(1);
	}
	return ids;
}

og.calendarOrderUsers = function(usersList){
	for (var i = 0; i < usersList.length - 1; i++) {
		for (var j = i+1; j < usersList.length; j++) {
			if (!usersList[j][1]) continue;
			if (usersList[i][1].toUpperCase() > usersList[j][1].toUpperCase()){
				var aux = usersList[i];
				usersList[i] = usersList[j];
				usersList[j] = aux;
			}
		}
	}
	return usersList;
}

var markactions = {
	markAsRead: new Ext.Action({
		text: lang('mark as read'),
        tooltip: lang('mark as read desc'),
        iconCls: 'ico-mark-as-read',
        disabled: true,
		handler: function() {
			og.openLink(og.getUrl('event', 'markasread', {ids: og.getSelectedEventsCsv()}));		
		}
	}),
	markAsUnread: new Ext.Action({
		text: lang('mark as unread'),
        tooltip: lang('mark as unread desc'),
        iconCls: 'ico-mark-as-unread',
        disabled: true,
		handler: function() {
			og.openLink(og.getUrl('event', 'markasunread', {ids: og.getSelectedEventsCsv()}));		
		}
	})
};

// Toolbar Items
var topToolbarItems = { 
	add: new Ext.Action({
		text: lang('add event'),
        tooltip: lang('add new event'),
        iconCls: 'ico-new new_button',
        handler: function() {
        	/*var date = og.calToolbarDateMenu.picker.getValue();
			changeView('add', date.getDate(), date.getMonth() + 1, date.getFullYear(), actual_user_filter, actual_status_filter, actual_task_filter);*/
			og.render_modal_form('', {c:'event', a:'add'});
		}
	}),
	view_month: new Ext.Action({
		text: lang('month'),
        tooltip: lang('month view'),
        iconCls: 'ico-calendar-month',
        handler: function() {
        	cal_actual_view = 'index';
			var date = og.calToolbarDateMenu.picker.getValue();
			changeView(cal_actual_view, date.getDate(), date.getMonth() + 1, date.getFullYear(), actual_user_filter, actual_status_filter, actual_task_filter);
		}
	}),
	view_week: new Ext.Action({
		text: lang('week'),
        tooltip: lang('week view'),
        iconCls: 'ico-calendar-week',
        handler: function() {
			cal_actual_view = 'viewweek';
			var date = og.calToolbarDateMenu.picker.getValue();
			changeView(cal_actual_view, date.getDate(), date.getMonth() + 1, date.getFullYear(), actual_user_filter, actual_status_filter, actual_task_filter);
		}
	}),
	view_week5days: new Ext.Action({
		text: lang('work week'),
        tooltip: lang('work week view'),
        iconCls: 'ico-calendar-week5',
        handler: function() {
			cal_actual_view = 'viewweek5days';
			var date = og.calToolbarDateMenu.picker.getValue();
			changeView(cal_actual_view, date.getDate(), date.getMonth() + 1, date.getFullYear(), actual_user_filter, actual_status_filter, actual_task_filter);
		}
	}),
	view_date: new Ext.Action({
		text: lang('day'),
        tooltip: lang('day view'),
        iconCls: 'ico-today',
        handler: function() {
			cal_actual_view = 'viewdate';
			var date = og.calToolbarDateMenu.picker.getValue();
			changeView(cal_actual_view, date.getDate(), date.getMonth() + 1, date.getFullYear(), actual_user_filter, actual_status_filter, actual_task_filter);
		}
	}),
	prev: new Ext.Action({
		tooltip: lang('prev'),
        iconCls: 'ico-prevmonth',
        handler: function() {
        	var date = og.calToolbarDateMenu.picker.getValue();
        	if (cal_actual_view == 'index') date = date.add(Date.MONTH, -1);
        	if (cal_actual_view == 'viewweek') date = date.add(Date.DAY, -7);
        	if (cal_actual_view == 'viewweek5days') date = date.add(Date.DAY, -7);
        	if (cal_actual_view == 'viewdate') date = date.add(Date.DAY, -1);
        	og.calToolbarDateMenu.picker.setValue(date);
			
			changeView(cal_actual_view, date.getDate(), date.getMonth() + 1, date.getFullYear(), actual_user_filter, actual_status_filter, actual_task_filter);
		}
	}),
	next: new Ext.Action({
		tooltip: lang('next'),
        iconCls: 'ico-nextmonth',
        handler: function() {
        	var date = og.calToolbarDateMenu.picker.getValue();
        	if (cal_actual_view == 'index') date = date.add(Date.MONTH, 1);
        	if (cal_actual_view == 'viewweek') date = date.add(Date.DAY, 7);
        	if (cal_actual_view == 'viewweek5days') date = date.add(Date.DAY, 7);
        	if (cal_actual_view == 'viewdate') date = date.add(Date.DAY, 1);
        	og.calToolbarDateMenu.picker.setValue(date);
			
			changeView(cal_actual_view, date.getDate(), date.getMonth() + 1, date.getFullYear(), actual_user_filter, actual_status_filter, actual_task_filter);
		}
	}),
	goto: new Ext.Action({
		text: lang('pick a date'),
		tooltip: lang('pick a date'),
		menu: og.calToolbarDateMenu
	}),
	imp_exp: new Ext.Action({
		text: lang('import/export'),
                tooltip: lang('calendar import - export'),
		menu: {items: [
			{text: lang('import'), iconCls: 'ico-upload', handler: function() {
				var url = og.getUrl('event', 'icalendar_import', {from_menu:1});
				og.openLink(url);
			}},
			{text: lang('export'), iconCls: 'ico-download', handler: function() {
				var url = og.getUrl('event', 'icalendar_export');
				og.openLink(url);
			}}
		]}
	}),
	del: new Ext.Action({
		text: lang('move to trash'),
        tooltip: lang('move selected objects to trash'),
        iconCls: 'ico-trash',
		disabled: true,
		handler: function() {
			var ids = og.getSelectedEventsCsv()+'';
			var arr_ids = ids.split(',')
			for(var i = 0; i < arr_ids.length; i++){
				var related = og.checkRelated("event",arr_ids[i]);
				if(related) break;
			}
			if(related){
				this.dialog = new og.EventRelatedPopUp("delete");
				this.dialog.setTitle(lang('events related'));
				this.dialog.show();
			}else{
				if (confirm(lang('confirm move to trash'))) {
					og.openLink(og.getUrl('event', 'delete', {ids: og.getSelectedEventsCsv()}));
				}
			}
		},
		scope: this
	}),
	edit: new Ext.Action({
		text: lang('edit'),
        tooltip: lang('edit selected event'),
        iconCls: 'ico-edit',
		disabled: true,
		handler: function() {
			ev_id = og.getSelectedEventsCsv();
			if (ev_id.length == 0) {
				og.err(lang('must select an event'));
			} else {
				if (ev_id.indexOf(',') != -1) {
					og.err(lang('select only one event'));
				} else {
					og.render_modal_form('', {c:'event', a:'edit', params: {id:ev_id}});
				}
			}
		}
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
			this.dialog = new og.EventRelatedPopUp("archive");
			this.dialog.setTitle(lang('events related'));
			this.dialog.show();
//			if (confirm(lang('confirm archive selected objects'))) {
//				og.openLink(og.getUrl('event', 'archive', {ids: og.getSelectedEventsCsv()}));
//			}
		},
		scope: this
	})
};

/**************************************************************************************/
/* Main Top Toolbar 																  */
/**************************************************************************************/

og.CalendarTopToolbar = function(config) {
	Ext.applyIf(config,{
		id: "calendarPanelTopToolbarObject",
		style:"border:0px none"
	});
		
	og.CalendarTopToolbar.superclass.constructor.call(this, config);
	
	if (!og.loggedUser.isGuest) {
		this.add(topToolbarItems.add);
		this.addSeparator();
		this.add(topToolbarItems.edit);
		//this.add(topToolbarItems.tag);
		this.add(topToolbarItems.archive);
		this.add(topToolbarItems.del);		
		this.addSeparator();                
	}
	this.add(topToolbarItems.markAs);
	this.addSeparator();
	this.add(new Ext.Action({
		text: lang('print'),
		tooltip: lang('print calendar'),
		iconCls: 'ico-print',
		handler: function() {
			og.PrintCalendar.printCalendar(og.config['genid']);
		},
		scope: this
	}));
	if (!og.loggedUser.isGuest) {
		this.addSeparator();
		this.add(topToolbarItems.imp_exp);
		this.add(new Ext.Action({
			text: lang('sync'),
			tooltip: lang('sync'),
			handler: function() {
				var url = og.getUrl('externalCalendar', 'calendar_sinchronization');
				og.openLink(url);
			},
			scope: this
		}));
	}
//        this.add(new Ext.Action({
//                                text: lang('import'),
//                                tooltip: lang('import'),
//                                iconCls: 'ico-download',
//                                handler: function() {
//                                        var url = og.getUrl('event', 'import_google_calendar');
//                                        og.openLink(url);
//                                },
//                                scope: this
//                            }));
}

Ext.extend(og.CalendarTopToolbar, Ext.Toolbar, {
	updateCheckedStatus : function(eventsSelected){
		var allunread = true;
		if (eventsSelected > 0) {
			topToolbarItems.del.enable();
			//topToolbarItems.tag.enable();
			if (allunread){
				markactions.markAsRead.enable();
				
			}
			markactions.markAsUnread.enable();
			topToolbarItems.archive.enable();
			if (eventsSelected == 1) topToolbarItems.edit.enable();
			else topToolbarItems.edit.disable();
		} else {
			topToolbarItems.del.disable();
			//topToolbarItems.tag.disable();
			topToolbarItems.edit.disable();
			markactions.markAsRead.disable();
			markactions.markAsUnread.disable();
			topToolbarItems.archive.disable();
		}
	}
});

/**************************************************************************************/
/* Second Top Toolbar 																  */
/**************************************************************************************/

og.CalendarSecondTopToolbar = function(config) {
	Ext.applyIf(config,{
		id: "calendarPanelSecondTopToolbarObject",
		style:"border:0px none; padding-top: 2px;"
	});
		
	og.CalendarTopToolbar.superclass.constructor.call(this, config);
	
	var currentUser = '';
    var usersArray = Ext.util.JSON.decode(document.getElementById(config.usersHfId).value);
    var companiesArray = Ext.util.JSON.decode(document.getElementById(config.companiesHfId).value);
    for (var k=0; k<usersArray.length; k++){
		if (usersArray[k].isCurrent)
			currentUser = usersArray[k].cid + ':' + usersArray[k].id;
	}
	var ucsData = [[currentUser, lang('my calendar')],['0:0',lang('everyone')],['0:0','--']];

	ucsOtherUsers = [];
	for (var i=0; i<usersArray.length; i++){
		var companyName = '';
		for(var j=0; j<companiesArray.length; j++){
			if (companiesArray[j] && companiesArray[j].id == usersArray[i].cid) {
				companyName = companiesArray[j].name;
			}
		}
		if (usersArray[i] && usersArray[i].cid)  {
			ucsOtherUsers[ucsOtherUsers.length] = [(usersArray[i].cid + ':' + usersArray[i].id), usersArray[i].name + ' : ' + companyName];
		}else if (usersArray[i]) {
			// Users that have not company
			ucsOtherUsers[ucsOtherUsers.length] = [(usersArray[i].cid + ':' + usersArray[i].id), usersArray[i].name];
		}	
		if (usersArray[i].isCurrent){
			currentUser = usersArray[i].cid + ':' + usersArray[i].id;
		}
	}
	ucsData = ucsData.concat(og.calendarOrderUsers(ucsOtherUsers));


    filterNamesCompaniesCombo = new Ext.form.ComboBox({
    	id: 'ogCalendarFilterNamesCompaniesCombo',
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : ucsData
	    }),
	    displayField:'text',
        //typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:160,
        valueField: 'value',
        emptyText: (lang('select user or group') + '...'),
        valueNotFoundText: '',
        listeners: {
        	'select' : function(combo, record) {
        		var splited = record.data.value.split(':');
        		actual_user_filter = splited[1] == 0 ? -1 : splited[1];
        		actual_comp_filter = splited[0];
        		var date = og.calToolbarDateMenu.picker.getValue();
				changeView(cal_actual_view, date.getDate(), date.getMonth() + 1, date.getFullYear(), actual_user_filter, actual_status_filter, actual_task_filter);
        	}
        }
    });
    actual_user_filter = ogCalendarUserPreferences.user_filter;
    u_filter = ogCalendarUserPreferences.user_filter_comp + ':' + (ogCalendarUserPreferences.user_filter == -1 ? 0 : ogCalendarUserPreferences.user_filter); 
    filterNamesCompaniesCombo.setValue(u_filter);
    
    cal_actual_view = ogCalendarUserPreferences.view_type || 'viewweek';
    actual_status_filter = ogCalendarUserPreferences.status_filter;
    if (actual_status_filter == null) actual_status_filter = ' 0 1 3';
    
    var show_task_options = false;
    for (i=0; i<og.objPickerTypeFilters.length; i++) {
    	if (og.objPickerTypeFilters[i].id == 'task') {
    		show_task_options = true;
    		break;
    	}
    }
	
    if (show_task_options) {
	    filterTaskCombo = new Ext.form.ComboBox({
	        id: 'ogCalendarfilterTaskCombo',
	        store: new Ext.data.SimpleStore({
	        	fields: ['value', 'text'],
	        	data :  [["no filter", '--' + lang('no filter') + '--'],["pending", lang('pending')],["complete", lang('complete')], ["hide", lang('none')]]
			}),
	        displayField:'text',
	        //typeAhead: true,
	        mode: 'local',
	        triggerAction: 'all',
	        selectOnFocus:true,
	        width:160,
	        valueField: 'value',
	        listeners: {
	        	'select' : function(combo, record) {
	        		actual_task_filter = record.data.value;
	        		var date = og.calToolbarDateMenu.picker.getValue();
					changeView(cal_actual_view, date.getDate(), date.getMonth() + 1, date.getFullYear(), actual_user_filter, actual_status_filter, actual_task_filter);
	        	}
	        }
	    });
	    
	    actual_task_filter = ogCalendarUserPreferences.task_filter;
	    t_filter = ogCalendarUserPreferences.task_filter;
	    filterTaskCombo.setValue(t_filter);
    }
    
    // Filter by Invitation State
	var viewActionsState = {
		all: new Ext.Action({
			text: lang('view all'),
			handler: function() {
				actual_status_filter = -1;
				var date = og.calToolbarDateMenu.picker.getValue();
				changeView(cal_actual_view, date.getDate(), date.getMonth() + 1, date.getFullYear(), actual_user_filter, actual_status_filter, actual_task_filter);
			}
		}),
		pending: {
			id: 'check_inv_pending',
	        text: lang('view pending response'),
			checked: (actual_status_filter.indexOf('0') != -1 || actual_status_filter == -1),
			checkHandler: function() {
				if (this.checked) addStateFilter('0');
				else removeStateFilter('0');
				var date = og.calToolbarDateMenu.picker.getValue();
				changeView(cal_actual_view, date.getDate(), date.getMonth() + 1, date.getFullYear(), actual_user_filter, actual_status_filter, actual_task_filter);
			}
		},
		yes: {
		    id: 'check_inv_yes',
	        text: lang('view will attend'),
			checked: (actual_status_filter.indexOf('1') != -1 || actual_status_filter == -1),
			checkHandler: function() {
				if (this.checked) addStateFilter('1');
				else removeStateFilter('1');
				var date = og.calToolbarDateMenu.picker.getValue();
				changeView(cal_actual_view, date.getDate(), date.getMonth() + 1, date.getFullYear(), actual_user_filter, actual_status_filter, actual_task_filter);
			}
		},
		no: {
		    id: 'check_inv_no',
	        text: lang('view will not attend'),
			checked: (actual_status_filter.indexOf('2') != -1 || actual_status_filter == -1),
			checkHandler: function() {
				if (this.checked) addStateFilter('2');
				else removeStateFilter('2');
				var date = og.calToolbarDateMenu.picker.getValue();
				changeView(cal_actual_view, date.getDate(), date.getMonth() + 1, date.getFullYear(), actual_user_filter, actual_status_filter, actual_task_filter);
			}
		},
		maybe: {
			id: 'check_inv_maybe',
	        text: lang('view maybe attend'),
			checked: (actual_status_filter.indexOf('3') != -1 || actual_status_filter == -1),
			checkHandler: function() {
				if (this.checked) addStateFilter('3');
				else removeStateFilter('3');
				var date = og.calToolbarDateMenu.picker.getValue();
				changeView(cal_actual_view, date.getDate(), date.getMonth() + 1, date.getFullYear(), actual_user_filter, actual_status_filter, actual_task_filter);
			}
		},
		birthdays: {
			id: 'show_birthdays_in_calendar',
	        text: lang('birthdays'),
			checked: og.preferences['show_birthdays_in_calendar'] == 1,
			checkHandler: function() {
				og.openLink(og.getUrl('account', 'update_user_preference', {name: 'show_birthdays_in_calendar', value:(this.checked?1:0)}), {
					callback: function(success, data) {
						var date = og.calToolbarDateMenu.picker.getValue();
						changeView(cal_actual_view, date.getDate(), date.getMonth() + 1, date.getFullYear(), actual_user_filter, actual_status_filter, actual_task_filter);
					}
				});
			}
		}
	};

	var status_menu = new Ext.Action({
       	iconCls: 'op-ico-details',
		text: lang('show'),
		menu: {items: [
			viewActionsState.pending,
			viewActionsState.yes,
			viewActionsState.no,
			viewActionsState.maybe,
			viewActionsState.birthdays
		]}
	});
	
	this.add(topToolbarItems.view_month);
	this.add(topToolbarItems.view_week);
	this.add(topToolbarItems.view_week5days);
	this.add(topToolbarItems.view_date);
	this.addSeparator();
	this.add(topToolbarItems.prev);
	this.add(topToolbarItems.next);
	this.addSeparator();
	this.add(topToolbarItems.goto);
	this.addSeparator();
	this.add(lang('user'));
	this.add(' ');
	this.add(filterNamesCompaniesCombo);
	this.add(' ');
	this.add(status_menu);
	this.add(' ');
	if (show_task_options) {
		this.add(lang('tasks'));
		this.add(' ');
		this.add(filterTaskCombo);
		this.add(' ');
	}
}

Ext.extend(og.CalendarSecondTopToolbar, Ext.Toolbar, {});

Ext.reg("calendarTopToolbar", og.CalendarTopToolbar);
Ext.reg("calendarSecondTopToolbar", og.CalendarSecondTopToolbar);