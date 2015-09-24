og.MemberManager = function() {
	var actions;
	this.doNotRemove = true;
	this.needRefresh = false;
	this.fields = [
		'id', 'name', 'dimension_id', 'object_type_id', 'parent_member_id', 'depth', 'object_id', 'color', 'template_id', 'icon_cls', 'member_id', 'mem_path',
		'total_tasks', 'completed_tasks', 'task_completion_p', 'total_estimated_time', 'total_worked_time', 'time_worked_p'
  	];
	
	this.dimension_id = og.member_list_params.dimension_id;
	this.dimension_code = og.member_list_params.dimension_code;
	this.object_type_id = og.member_list_params.object_type_id;
	this.object_type_name = og.member_list_params.object_type_name;
	
	// prepare reader fields for any member type
	var cp_names = [];
  	for (ot_name in og.custom_properties_by_type) {
		var cps = og.custom_properties_by_type[ot_name];
		for (i=0; i<cps.length; i++) {
	  		if (cps[i].member_cp) {
	  			cp_names.push('cp_' + cps[i].id);
	  		}
	  	}
  	}
  	this.fields = this.fields.concat(cp_names);
  	
  	
	if (!this.store) {
		//this.store = new Ext.data.GroupingStore({
		this.store = new Ext.data.Store({
			proxy: new og.GooProxy({
				url: og.getUrl('member', 'list_all')
			}),
			reader: new Ext.data.JsonReader({
				root: 'members',
				totalProperty: 'totalCount',
				id: 'id',
				dimension_id: 'dimension_id',
				dimension_name: 'dimension_name',
				fields: this.fields
			}),
			remoteSort: true,
			listeners: {
				'load': function() {
					var d = this.reader.jsonData;
					
					if (d.totalCount === 0) {
						this.fireEvent('messageToShow', lang("no more objects message", d.dimension_name));
					} else if (d.members.length == 0) {
						this.fireEvent('messageToShow', lang("no more objects message", d.dimension_name));
					} else {
						this.fireEvent('messageToShow', "");
					}
				
					og.eventManager.fireEvent('replace all empty breadcrumb', null);
				}
			}
	        
		});
		og.eventManager.addListener('member changed', this.reset, this);
		this.store.setDefaultSort('name', 'asc');
	}
	
	this.store.addListener({messageToShow: {fn: this.showMessage, scope: this}});

	
	var readClass = 'read-unread-' + Ext.id();
	
	function renderName(value, p, r) {
		var text = '<span class="bold">'+ og.clean(value) +'</span>';
		var dcode = '';
		var treepanel = Ext.getCmp('dimension-panel-'+r.data.dimension_id);
		if (treepanel) dcode = treepanel.dimensionCode;
		var onclick = "og.memberTreeExternalClick('"+dcode+"', "+r.data.id+"); return false;";
		
		return String.format('<a style="font-size:120%;" class="{3}" href="{1}" onclick="{4}" title="{2}">{0}</a>', text, "#", og.clean(value), '', onclick);
	}

	function renderIcon(value, p, r) {
		return '<div class="link-ico '+r.data.icon_cls+'"></div>';
	}
	
	function renderMemberPath(value, p, r) {
		var mem_path = "";
		if (r.data.mem_path) {
			var mpath = Ext.util.JSON.decode(r.data.mem_path);
			if (mpath){ 
				mem_path = "<div class='breadcrumb-container' style='display: inline-block;'>";
				mem_path += og.getEmptyCrumbHtml(mpath, '.breadcrumb-container', og.breadcrumbs_skipped_dimensions);
				mem_path += "</div>";
			}
		}
		return mem_path;
	}
	
	function renderProjectCompletionTasks(value, p, r) {
		return r.data.task_completion_p + " %";
	}
	
	function renderProjectCompletionTime(value, p, r) {
		return r.data.time_worked_p + " %";
	}
	
	function renderTime(value, p, r) {
		var hours = Math.floor(value / 60);
		var mins = value % 60;
		if (hours < 10) hours = '0'+ hours;
		if (mins < 10) mins = '0'+ mins;
		
		return hours +":"+ mins;
	}

	function getSelectedIds() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			var ret = '';
			for (var i=0; i < selections.length; i++) {
				ret += "," + selections[i].data.object_id;
			}
			return ret.substring(1);
		}
	}
	this.getSelectedIds = getSelectedIds;
	
	function getFirstSelectedId() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			return selections[0].data.object_id;
		}
	}
	
	function getFirstSelectedMemberId() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			return selections[0].data.member_id;
		}
	}

	var sm = new Ext.grid.CheckboxSelectionModel();
	sm.on('selectionchange', function() {
		if (sm.getCount() <= 0) {
			actions.edit.setDisabled(true);
			actions.del.setDisabled(true);
		} else {
			actions.edit.setDisabled(false);
			actions.del.setDisabled(false);
		}
	});
	
	var cm_info = [
		sm,{
			id: 'icon',
			header: '&nbsp;',
			dataIndex: 'type',
			width: 28,
        	renderer: renderIcon,
        	fixed:true,
        	resizable: false,
        	hideable:false,
        	menuDisabled: true
		},{
			id: 'name',
			header: lang("name"),
			dataIndex: 'name',
			width: 250,
			renderer: renderName,
			sortable:true
		},{
			id: 'mem_path',
			header: lang("located under"),
			dataIndex: 'mem_path',
			width: 250,
			renderer: renderMemberPath,
			sortable:true
		/*},{
			id: 'task_completion_p',
			header: lang("customer completion perc tasks"),
			dataIndex: 'task_completion_p',
			width: 100,
			align: 'center',
			renderer: renderProjectCompletionTasks,
			sortable:true
        },{
			id: 'completed_tasks',
			header: lang("completed tasks"),
			dataIndex: 'completed_tasks',
			width: 50,
			align: 'center',
			renderer: og.clean,
			hidden: true,
			sortable:true
        },{
			id: 'total_tasks',
			header: lang("total tasks"),
			dataIndex: 'total_tasks',
			width: 50,
			align: 'center',
			renderer: og.clean,
			hidden: true,
			sortable:true
        },{
			id: 'time_worked_p',
			header: lang("customer completion perc time"),
			dataIndex: 'time_worked_p',
			width: 100,
			align: 'center',
			renderer: renderProjectCompletionTime,
			sortable:true
        },{
			id: 'total_worked_time',
			header: lang("worked time"),
			dataIndex: 'total_worked_time',
			width: 50,
			align: 'center',
			renderer: renderTime,
			hidden: true,
			sortable:true
        },{
			id: 'total_estimated_time',
			header: lang("estimated time"),
			dataIndex: 'total_estimated_time',
			width: 50,
			align: 'center',
			renderer: renderTime,
			hidden: true,
			sortable:true*/
        }];
	
	
	
	// custom property columns
	var cps = og.custom_properties_by_type[this.object_type_name] ? og.custom_properties_by_type[this.object_type_name] : [];
	for (i=0; i<cps.length; i++) {
		cm_info.push({
			id: 'cp_' + cps[i].id,
			header: cps[i].name,
			dataIndex: 'cp_' + cps[i].id,
			sortable: true,
			renderer: og.clean
		});
	}
	
    var cm = new Ext.grid.ColumnModel(cm_info);
	cm.defaultSortable = false;

		
	actions = {
		newCO: new Ext.Action({
			text: lang('new'),
            tooltip: lang('add new member', lang(this.object_type_name)),
            iconCls: 'ico-new',
            handler: function() {
            	var parameters = { dim_id: this.dimension_id, type: this.object_type_id };
            	var mem_selection = og.contextManager.getDimensionMembers(this.dimension_id);
            	var parent_id = 0;
            	for (var i=0; i<mem_selection.length; i++) {
            		if (mem_selection[i] > 0) parent_id = mem_selection[i];
            	}
            	if (parent_id > 0) {
            		parameters.parent = parent_id;
            	}
            	var url = og.getUrl('member', 'add', parameters);
				og.openLink(url, null);
			},
			scope: this
		}),
		edit: new Ext.Action({
			text: lang('edit'),
            tooltip: lang('edit selected member', lang(this.object_type_name)),
            iconCls: 'ico-edit',
			disabled: true,
			handler: function() {
				var url = og.getUrl('member', 'edit', {id:getFirstSelectedMemberId()});
				og.openLink(url, null);
			},
			scope: this
		}),
		del: new Ext.Action({
			text: lang('delete'),
            tooltip: lang('delete selected member', lang(this.object_type_name)),
            iconCls: 'ico-delete',
			disabled: true,
			handler: function() {
				if (confirm(lang('delete member warning', lang(this.object_type_name)))) {
					var url = og.getUrl('member', 'delete', {id:getFirstSelectedMemberId()});
					og.openLink(url, null);
				}
			},
			scope: this
		})
    };
    
	var tbar = [];
	if (!og.loggedUser.isGuest) {
		tbar.push(actions.newCO);
		tbar.push('-');
		tbar.push(actions.edit);
		tbar.push(actions.del);
	}
	
	//og.MemberManager.superclass.constructor.call(this, {
	og.MemberManager.superclass.constructor.call(this, {
		store: this.store,
		layout: 'fit',
		cm: cm,
		stateful: og.preferences['rememberGUIState'],
		id: 'member-manager-'+this.dimension_id,
		stripeRows: true,
		closable: true,
		loadMask: true,
		bbar: new og.CurrentPagingToolbar({
			pageSize: og.config['files_per_page'],
			store: this.store,
			displayInfo: true,
			displayMsg: lang('displaying objects of'),
			emptyMsg: lang("no objects to display")
		}),/*
		view: new Ext.grid.GroupingView({
	        forceFit: true,
			//enableGroupingMenu: false,
	        //hideGroupedColumn: true,
	        //groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "'+lang('customers')+'" : "'+lang('customer')+'"]})'
	    }),*/
		viewConfig: {
			forceFit: true
		},
		sm: sm,
		tbar:tbar,
		listeners: {
			'render': {
				fn: function() {
					this.innerMessage = document.createElement('div');
					this.innerMessage.className = 'inner-message';
					var msg = this.innerMessage;
					var elem = Ext.get(this.getEl());
					var scroller = elem.select('.x-grid3-scroller');
					scroller.each(function() {
						this.dom.appendChild(msg);
					});
				},
				scope: this
			},
			'columnmove': {
				fn: function(old_index, new_index) {
					og.eventManager.fireEvent('replace all empty breadcrumb', null);
				},
				scope: this
			}
		}
	});
	
};

Ext.extend(og.MemberManager, Ext.grid.GridPanel, {
	load: function(params) {
		
		if (!params) params = {};
		var start;
		if (typeof params.start == 'undefined') {
			start = (this.getBottomToolbar().getPageData().activePage - 1) * og.config['files_per_page'];
		} else {
			start = 0;
		}
		
		
		this.store.baseParams = {
			context: og.contextManager.plainContext(),
			dim_id: this.dimension_id,
			type_id: this.object_type_id
	    };
		
		this.store.removeAll();
		this.store.load({
			params: Ext.apply(params, {
				start: start,
				limit: og.config['files_per_page']				
			})
		});
	},
	resetVars: function(){
		
	},
	
	activate: function() {
		if (this.needRefresh)
		this.load();
	},
	
	reset: function() {
		this.load({start:0});
	},
	
	showMessage: function(text) {
		this.innerMessage.innerHTML = text;
	},
	
	trashObjects: function() {
		if (confirm(lang('confirm move to trash'))) {
			this.load({
				action: 'delete',
				ids: this.getSelectedIds()
			});
			this.getSelectionModel().clearSelections();
		}
	},
	
	archiveObjects: function() {
		if (confirm(lang('confirm archive selected objects'))) {
			this.load({
				action: 'archive',
				ids: this.getSelectedIds()
			});
			this.getSelectionModel().clearSelections();
		}
	}
});


Ext.reg("members", og.MemberManager);
