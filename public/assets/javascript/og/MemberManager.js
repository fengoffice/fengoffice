og.MemberManager = function(config) {
	var actions;
	this.doNotRemove = true;
	this.needRefresh = false;
	this.fields = [
		'id', 'name', 'dimension_id', 'object_type_id', 'parent_member_id', 'depth', 'object_id', 'template_id', 'icon_cls', 'member_id', 'mem_path',
		'total_tasks', 'completed_tasks', 'task_completion_p', 'total_estimated_time', 'total_worked_time', 'time_worked_p'
  	];

	if (og.object_subtypes) {
    	this.fields.push('object_subtype_id');
    	this.fields.push('object_subtype_name');
	}
	
	this.dimension_id = config.dimension_id;
	this.dimension_code = config.dimension_code;
	this.object_type_id = config.object_type_id;
	this.object_subtype_id = config.object_subtype_id;
	this.object_type_name = config.object_type_name;
	this.lastGroupField = config.last_group_field;
	this.groups_info = null;
	this.object_type_icon_cls = config.object_type_icon_cls;

	this.members_per_page = og.getMembersPerPage();

	// define the controller that will be used to add/edit members
	// only projects and customers use a different controller, the rest use the member controller
	this.add_edit_controller = 'member';
	if (this.object_type_name == 'customer') {
		this.add_edit_controller = 'customer';
	} else if (this.object_type_name == 'project') {
		this.add_edit_controller = 'project';
	}

	if (!og.saveMemberTypeCustomProperties) {
		this.fields.push('description');
	}
	
	// prepare reader fields for any member type
	var cp_names = [];
  	for (ot_name in og.custom_properties_by_type) {
		var cps = og.custom_properties_by_type[ot_name];
		for (i=0; i<cps.length; i++) {
	  		if (cps[i].member_cp && cps[i].code != 'color_special') {
	  			cp_names.push('cp_' + cps[i].id);
	  		}
	  	}
  	}
	// add the customer's related contact custom properties to the grid fields
	if (this.object_type_name == 'customer') {
		var contact_cps = og.custom_properties_by_type['contact'];
		if (typeof contact_cps == 'undefined') contact_cps = [];
		for (i=0; i<contact_cps.length; i++) {
			cp_names.push('cp_' + contact_cps[i].id);
		}
	}
  	this.fields = this.fields.concat(cp_names);
	
  	// add associated dimensions fields 
  	var dim_assocs = [];
  	var d_associations = null;
  	if (og.dimension_member_associations[this.dimension_id]) {
  		d_associations = og.dimension_member_associations[this.dimension_id][this.object_type_id];
  	}
  	if (d_associations) {
	  	for (var i=0; i<d_associations.length; i++) {
	  		var assoc = d_associations[i];
	  		dim_assocs.push('dimassoc_' + assoc.id);
	  	}
	  	this.fields = this.fields.concat(dim_assocs);
  	}
  	
  	// add specific member type columns
  	var mem_type_cols = [];
  	if (og.listing_member_type_cols && og.listing_member_type_cols[this.dimension_id]) {
  		var mem_type_cols_objs = og.listing_member_type_cols[this.dimension_id][this.object_type_id];
  		if (mem_type_cols_objs) {
	  		for (var i=0; i<mem_type_cols_objs.length; i++) {
		  		mem_type_cols.push(mem_type_cols_objs[i].id);
		  	}
  		}
  	}
  	this.fields = this.fields.concat(mem_type_cols);
  	
  	// data store and grid view configuration
  	if (og.member_list_grouping) {
	  	if (og.member_list_groups_info && og.member_list_groups_info[this.dimension_id+"-"+this.object_type_id]) {
	  		this.lastGroupField = og.member_list_groups_info[this.dimension_id+"-"+this.object_type_id].last_group_by;
	  	}
	  	
	  	var view_object = new Ext.grid.GroupingView({ forceFit: false, enableNoGroups: false, showGroups: this.lastGroupField !== 'none', groupByText: lang('group by this field') });
  		var store_class = Ext.data.GroupingStore;
  		var controller = og.member_list_grouping.controller;
  		var action = og.member_list_grouping.action;
  		
  	} else {
  		
  		var view_object = new Ext.grid.GridView({ forceFit: false });
  		var store_class = Ext.data.Store;
  		var controller = 'member';
  	  	var action = 'listing';
  	}
  	
  	// create the data store
	if (!this.store) {
		this.store = new store_class({
			remoteGroup: true,
			groupField: (this.lastGroupField && this.lastGroupField !== 'none') ? this.lastGroupField : '',
			
			proxy: new og.GooProxy({
				url: og.getUrl(controller, action)
			}),
			reader: new Ext.data.JsonReader({
				root: 'members',
				totalProperty: 'totalCount',
				id: '_record_id', // composite key (mem.id + '_' + group_id) when grouped, absent otherwise -> AUTO_ID
				dimension_id: 'dimension_id',
				object_type_id: 'object_type_id',
				object_subtype_id: 'object_subtype_id',
				dimension_name: 'dimension_name',
				groups_info: 'groups_info',
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

					this.groups_info = d.groups_info;
					this.dimension_id = d.dimension_id;
					this.object_type_id = d.object_type_id;
					this.object_subtype_id = d.object_subtype_id ? d.object_subtype_id : 0;
					var man = Ext.getCmp('member-manager-' + d.dimension_id + '-' + d.object_type_id);
					og.eventManager.fireEvent('after grid panel load', {man:man, data:d});
					
					og.eventManager.fireEvent('replace all empty breadcrumb', null);
					
				},
				'datachanged': function() {
					if (this.dimension_id > 0) {
						var man = Ext.getCmp('member-manager-'+this.dimension_id+'-'+this.object_type_id);
						if (man) {
							var has_associations = man.columnModelHasDimensionAssociations();
							if (has_associations) {
								//man.needRefresh = !man.needRefresh;
								if (man.needRefresh) man.needRefresh=false;
								man.activate();
							}
						}
					}
				}
			}
	        
		});
		// When ExtJS triggers store.groupBy() from the column header context menu,
		// store.groupField changes but man.load() is bypassed. Sync lastGroupField
		// and baseParams so the server receives the correct groupBy parameter.
		this.store.on('beforeload', function(store) {
			if (store.groupField) {
				// Grouping (or re-grouping after flat): always honor store.groupField
				if (store.groupField !== (store.baseParams.groupBy || '')) {
					store.baseParams.groupBy = store.groupField;
				}
				this.lastGroupField = store.groupField;
				if (og.member_list_groups_info) {
					var gkey = this.dimension_id + '-' + this.object_type_id;
					if (!og.member_list_groups_info[gkey]) og.member_list_groups_info[gkey] = {};
					og.member_list_groups_info[gkey].last_group_by = store.groupField;
				}
				var vv = this.rendered ? this.getView() : null;
				if (vv) {
					vv.showGroups = true;
					vv.enableGrouping = true;
				}
				return;
			}
			// Flat mode: do not send groupBy
			if (this.lastGroupField === 'none' && store.baseParams) {
				delete store.baseParams.groupBy;
			}
		}, this);
		og.eventManager.addListener('member changed', this.reset, this);
		this.store.setDefaultSort('name', 'asc');
	}
	this.store.addListener({messageToShow: {fn: this.showMessage, scope: this}});

	// bottom toolbar definition
	if (og.member_list_grouping) {
		// Always use the paging toolbar as bbar so ExtJS layout always has the
		// correct toolbar height. In grouped mode, an overlay div covers the paging
		// controls and shows the groups-specific info instead.
		this.pagingToolbar = new og.CurrentPagingToolbar({
			pageSize: this.members_per_page,
			store: this.store,
			displayInfo: true,
			displayMsg: lang('displaying objects of'),
			emptyMsg: lang("no objects to display")
		});
		var bottom_toolbar = this.pagingToolbar;
  	} else {
  		this.pagingToolbar = new og.CurrentPagingToolbar({
			pageSize: this.members_per_page,
			store: this.store,
			displayInfo: true,
			displayMsg: lang('displaying objects of'),
			emptyMsg: lang("no objects to display")
		});
		var bottom_toolbar = this.pagingToolbar;
  	}

	this.addMembersPerPageSelector(this.pagingToolbar);
	
	
	var readClass = 'read-unread-' + Ext.id();
	
	function renderName(value, p, r) {
		
		if (isNaN(r.data.id)) {
			
			return '<span class="bold" id="'+r.data.id+'">'+ (value ? og.clean(value) : '') +'</span>';
			
		} else {
			var text = '<span class="bold">'+ (value ? og.clean(value) : '') +'</span>';
			var dcode = '';
			var treepanel = Ext.getCmp('dimension-panel-'+r.data.dimension_id);
			if (treepanel) dcode = treepanel.dimensionCode;
			var onclick = "og.memberTreeExternalClick('"+dcode+"', "+r.data.id+"); return false;";
			
			return String.format('<a style="font-size:120%;" class="{3}" href="{1}" onclick="{4}" title="{2}">{0}</a>', text, "#", og.clean(value), '', onclick);
		}
	}

	function renderIcon(value, p, r) {
		return '<div class="link-ico '+r.data.icon_cls+'"></div>';
	}

	function renderActions(value, p, r) {
		if (isNaN(r.data.id)) return "";

		let onclick = "og.render_modal_form('', {c:'member', a:'edit', params:{id:"+r.data.id+", req_channel:'member list - line edit'}});";

		let html = '<div class="member-list actions">';
		html += '<a class="list-action-icon edit" href="#" onclick="'+onclick+'" title="'+lang('edit')+'"><i class="icon-pencil-line"></i></a>'
		html += '</div>';

		return html;
	}
	
	function renderMemberPath(value, p, r) {
		var mem_path = "";
		if (r.data.mem_path) {
			var mpath = Ext.util.JSON.decode(r.data.mem_path);
			if (mpath){
				mem_path = "<div class='breadcrumb-container' style='display: inline-block;'>";
				mem_path += og.getEmptyCrumbHtml(mpath, '.breadcrumb-container');
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

	function renderMemberPathGroupName(value, p, r) {
		if (r.data.id=="__total_row__" || !r.data.dimension_id) return "";

		var mem_id = null;
		if (r.data.mem_path) {
			var mpath = Ext.util.JSON.decode(r.data.mem_path);
			if (mpath){
				var obj = mpath[r.data.dimension_id];
				for (var ot in obj) {
					var mem_ids = obj[ot];
					if (mem_ids.length > 0) {
						mem_id = mem_ids[0];
						break;
					}
				}
			}
		}
		if (mem_id) {
			var man = Ext.getCmp('member-manager-'+r.data.dimension_id+'-'+r.data.object_type_id);
			if (man.store.groups_info && man.store.groups_info.groups && man.store.groups_info.groups[mem_id]) {
				return man.store.groups_info.groups[mem_id].name;
			}			
		}
		
		return "";
	}
	
	function renderMemberGroupName(value, p, r) {
		if (r.data.id=="__total_row__" || !r.data.dimension_id) return "";

		var man = Ext.getCmp('member-manager-'+r.data.dimension_id+'-'+r.data.object_type_id);
		if (!man.store.groups_info || !man.store.groups_info.groups) return "";

		var ids = isNaN(value) ? value.split(',') : [value];
		// ensure ids has unique values, to avoid duplicate group names in case of duplicate ids
		ids = ids.filter(function(item, pos) {
			return ids.indexOf(item) == pos;
		});
		var names = [];
		for (var i = 0; i < ids.length; i++) {
			var ginfo = man.store.groups_info.groups[ids[i]];
			if (ginfo) names.push(ginfo.name);
		}
		return names.join(', ');
	}
	
	function renderDimAssociation(value, p, r) {
		if (typeof(value) == 'string' && value != "") {
		  try {
			var assoc_id = p.id.replace('dimassoc_', '');
			var assoc_def = null;
			
			if (og.dimension_member_associations[config.dimension_id] && 
					og.dimension_member_associations[config.dimension_id][config.object_type_id]) {
				
		  		d_associations = og.dimension_member_associations[config.dimension_id][config.object_type_id];
		  	  	if (d_associations) {
			  		for (var i=0; i<d_associations.length; i++) {
			  	  		var assoc = d_associations[i];
			  	  		if (assoc.id == assoc_id) {
			  	  			assoc_def = assoc;
			  	  			break;
			  	  		}
			  		}
		  	  	}
		  	}
			
			if (assoc_def) {
				var values = value.split(',');
				
				var mem_path = "";
				let mem_obj = null;
				
				for (var j=0; j<values.length; j++) {
					var val = values[j];
					if (val == '0' || val == '') continue;

					if (!mem_obj) {
						mem_obj = {};
					}
					if (!mem_obj[assoc_def.assoc_dimension_id]) {
						mem_obj[assoc_def.assoc_dimension_id] = {};
					}
					if (!mem_obj[assoc_def.assoc_dimension_id][assoc_def.assoc_object_type_id]) {
						mem_obj[assoc_def.assoc_dimension_id][assoc_def.assoc_object_type_id] = [];
					}
					if (!mem_obj[assoc_def.assoc_dimension_id][assoc_def.assoc_object_type_id].includes(val)) {
						mem_obj[assoc_def.assoc_dimension_id][assoc_def.assoc_object_type_id].push(val);
					}
				}
				
				if (mem_obj) {
					mem_path += "<div class='breadcrumb-container' style='display: inline-block;'>";
					mem_path += og.getEmptyCrumbHtml(mem_obj, '.breadcrumb-container');
					mem_path += "</div>";
				}
				
				return mem_path;
			}
		  } catch (e) {
			  console.log(e);
		  }
		}
		return "";
	}

	function getSelectedIds() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			var ret = '';
			for (var i=0; i < selections.length; i++) {
				ret += "," + selections[i].data.member_id;
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
			id: 'actions',
			header: '',
			dataIndex: 'actions',
			width: 28,
			renderer: renderActions,
			fixed: true,
			resizable: false,
			hideable: false,
			menuDisabled: true
		},{
			id: 'name',
			header: lang("name"),
			dataIndex: 'name',
			width: 250,
			renderer: renderName,
			sortable: true,
			groupable: false
		},{
			id: 'mem_path',
			header: lang("located under"),
			dataIndex: 'mem_path',
			width: 100,
			renderer: renderMemberPath,
			groupRenderer: renderMemberPathGroupName,
			sortable:true
        }
	];
	
	if (og.object_subtypes) {
		
		cm_info.push({
			id: 'object_subtype_name',
			header: lang("subtype"),
			dataIndex: 'object_subtype_name',
			align: 'left',
			sortable: true,
			renderer: function(value, p, r) {
				return value ? og.clean(value) : "";
				
			}
		});
	}

		

	
	
	// custom property columns
	var cps = og.custom_properties_by_type[this.object_type_name] ? og.custom_properties_by_type[this.object_type_name] : [];
	for (i=0; i<cps.length; i++) {
		if (!parseInt(cps[i].disabled) && cps[i].code != 'color_special') {
			let data_index = 'cp_' + cps[i].id;
			if (!og.saveMemberTypeCustomProperties && cps[i].code == 'description_special') {
				data_index = 'description';
			}
			
			cm_info.push({
				id: 'cp_' + cps[i].id,
				hidden: parseInt(cps[i].show_in_lists) == 0,
				header: cps[i].name,
				dataIndex: data_index,
				align: cps[i].cp_type=='numeric' ? 'right' : 'left',
				sortable: true,
				//renderer: og.clean
				renderer: cps[i].cp_type === 'url' ? function(value) {
					if (!value) return '';
					var url = value;
					if (!/^https?:\/\//i.test(value)) {
						url = "https://" + value;
					}
					return '<a href="' + url + '" target="_blank">' + url + '</a>';
				} : null
			});
		}
	}
	// add the customer's related contact custom properties to the grid column model
	if (this.object_type_name == 'customer') {
		// client contact custom property columns
		var cps = og.custom_properties_by_type['contact'] ? og.custom_properties_by_type['contact'] : [];
		for (i=0; i<cps.length; i++) {
			if (!parseInt(cps[i].disabled)) {
				cm_info.push({
					id: 'cp_' + cps[i].id,
					hidden: parseInt(cps[i].show_in_lists) == 0,
					header: cps[i].name,
					dataIndex: 'cp_' + cps[i].id,
					align: cps[i].cp_type=='numeric' ? 'right' : 'left',
					sortable: true,
					//renderer: og.clean
				});
			}
		}
	}
	
	// add associated dimensions fields 
  	var dim_assocs = [];
  	var d_associations = [];
  	if (og.dimension_member_associations[this.dimension_id] && og.dimension_member_associations[this.dimension_id][this.object_type_id]) {
  		d_associations = og.dimension_member_associations[this.dimension_id][this.object_type_id];
  	}
  	for (var i=0; i<d_associations.length; i++) {
  		var assoc = d_associations[i];
  		cm_info.push({
			id: 'dimassoc_' + assoc.id,
			header: assoc.name,
			dataIndex: 'dimassoc_' + assoc.id,
			sortable: true,
			renderer: renderDimAssociation,
			groupRenderer: renderMemberGroupName
		});
  	}
  	
  	// member type specific columns
  	if (og.listing_member_type_cols && og.listing_member_type_cols[this.dimension_id] && og.listing_member_type_cols[this.dimension_id][this.object_type_id]) {
  		var mem_type_cols = og.listing_member_type_cols[this.dimension_id][this.object_type_id];
  		if (mem_type_cols) {
	  		for (var i=0; i<mem_type_cols.length; i++) {
	  			var col = mem_type_cols[i];
	  			cm_info.push({
	  				id: 'mem_type_col_' + col.id,
	  				header: col.name,
	  				dataIndex: col.id,
	  				sortable: true,//col.sortable,
	  				renderer: col.renderer,
					align: col.align ? col.align : 'left',
					hidden: col.hidden == true
	  			});
	  		}
  		}
  	}
	
    var cm = new Ext.grid.ColumnModel(cm_info);
	cm.defaultSortable = false;
	cm.on('hiddenchange', this.afterColumnShowHide, this);

		
	actions = {
		newCO: new Ext.Action({
			text: '<i class="icon-circle-plus"></i>' + lang('new'),
            tooltip: lang('add new member', lang(this.object_type_name)),
            iconCls: 'btn btn-sm btn-secondary',
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
				// render modal form when adding member
				og.render_modal_form('', {c:this.add_edit_controller, a:'add', params: parameters});
			},
			scope: this
		}),
		edit: new Ext.Action({
			text: '<i class="icon-pencil-line"></i>' + lang('edit'),
            tooltip: lang('edit selected member', lang(this.object_type_name)),
            iconCls: 'btn btn-sm',
			disabled: true,
			handler: function() {
				// render modal form when editing member
				let parameters = {
					id: getFirstSelectedMemberId(),
					mem_id: true,
				};
				og.render_modal_form('', {c:this.add_edit_controller, a:'edit', params: parameters});
			},
			scope: this
		}),
		del: new Ext.Action({
			text: '<i class="icon-circle-x"></i>' + lang('delete'),
            tooltip: lang('delete selected member', lang(this.object_type_name)),
            iconCls: 'btn btn-sm',
			disabled: true,
			handler: function() {
				if (confirm(lang('delete member warning', lang(this.object_type_name)))) {
					var url = og.getUrl('member', 'delete_multiple', {id:this.getSelectedIds()});
					og.openLink(url, null);
				}
			},
			scope: this
		})
    };

	let subtype_new_buttons = [];
	if (og.object_subtypes && og.object_subtypes_by_otid && og.object_subtypes_by_otid[this.object_type_id] && og.object_subtypes_by_otid[this.object_type_id].length > 0) {
		// genereate a new menu item for each subtype, to create a new member of that subtype
		for (let i=0; i<og.object_subtypes_by_otid[this.object_type_id].length; i++) {
			let subtype = og.object_subtypes_by_otid[this.object_type_id][i];
			subtype_new_buttons.push({
				text: subtype.name, // set subtype name as button text
				iconCls: this.object_type_icon_cls, // set generic object type icon
				handler: function() {
					// render modal form for the subtype
					var parameters = { dim_id: this.dimension_id, type: this.object_type_id, object_subtype_id: subtype.id };
					og.render_modal_form('', {c:this.add_edit_controller, a:'add', params: parameters});
				},
				scope: this
			});
		}
	}
    
	var tbar = [];
	if (!og.loggedUser.isGuest) {
		// if we have subtypes for this type of member, then add a "new" menu with subtypes and the generic type button
		if (subtype_new_buttons.length > 0) {
			// modify generic object type button text and icon
			actions.newCO.setText(og.objectTypes[this.object_type_id].c_name); // set generic object type name
			actions.newCO.setIconClass(this.object_type_icon_cls); // set generic object type icon
			// add subtype buttons after the generic type button
			let new_buttons = [actions.newCO].concat(subtype_new_buttons);
			// create the menu with the new member buttons as items
			let new_menu = new Ext.Button({
				text: '<i class="icon-circle-plus"></i>' + lang('new') + '<i class="icon-chevron-down" style="font-size: 0.8em;"></i>',
				iconCls: 'btn btn-sm btn-secondary',
				menu: new_buttons
			});
			// add the menu to the toolbar
			tbar.push(new_menu);
		} else {
			// if we don't have subtypes then just add the generic type new button to the toolbar
			tbar.push(actions.newCO);
		}
		tbar.push('-');
		tbar.push(actions.edit);
		tbar.push(actions.del);
	}
	
	if (og.additional_member_list_actions) {
		// specific object type actions
		if (og.additional_member_list_actions[this.object_type_id]) {
			var add_actions = og.additional_member_list_actions[this.object_type_id];
			for (var k=0; k<add_actions.length; k++) {
				add_actions[k].initialConfig.dim_id = this.dimension_id;
				tbar.push(add_actions[k]);
			}
		}
		// general actions
		if (og.additional_member_list_actions["general"]) {
			var add_actions = og.additional_member_list_actions["general"];
			for (var k=0; k<add_actions.length; k++) {
				add_actions[k].initialConfig.dim_id = this.dimension_id;
				tbar.push(add_actions[k]);
			}
		}
	}
	
	og.MemberManager.superclass.constructor.call(this, {
		store: this.store,
		layout: 'fit',
		cm: cm,
		stateful: og.preferences['rememberGUIState'],
		id: 'member-manager-'+this.dimension_id + "-" + this.object_type_id,
		stripeRows: true,
		closable: true,
		loadMask: true,
		bbar: bottom_toolbar,
		view: view_object,
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

					// if the list is grouping and the grouping field is not in the list of fields
					// then change it to the default one: mem_path; 'none' is intentionally not a
					// field name and must not be replaced here.
					if (og.member_list_grouping) {
						if (this.lastGroupField !== 'none' && this.fields.indexOf(this.lastGroupField) == -1) {
							this.lastGroupField = 'mem_path';
							this.store.groupField = this.lastGroupField;
						}
					}

					// Ensure that the actions columns (with edit icon) is the second column (after the member icon)
					let actions_col_index = cm.getIndexById('actions');
					if (actions_col_index > -1) {
						this.getColumnModel().moveColumn(actions_col_index, 2);
					}

					// In grouped mode, the paging toolbar is always the bbar so ExtJS
					// layout always measures the correct toolbar height. An overlay div
					// sits on top in grouped mode showing groups-specific info, and is
					// hidden in flat mode to reveal the paging controls underneath.
					if (og.member_list_grouping && this.pagingToolbar) {
						var bbar_el = this.pagingToolbar.el;
						var bbar_wrap = bbar_el.up('.x-panel-bbar') || bbar_el.parent();
						if (bbar_wrap) {
							$(bbar_wrap.dom).css('position', 'relative');
							var man_ref = this;
							var overlay = document.createElement('div');
							overlay.className = 'member-list-groups-overlay';
							overlay.innerHTML =
								'<span style="margin-left:55px;" id="showing_x_groups_' + this.id + '"></span>' +
								'<button class="x-btn-text btn btn-sm" style="margin-left:30px;display:none;" id="load_more_groups_btn_' + this.id + '">' +
									'<i class="icon-refresh-cw"></i> ' + lang('load more groups') +
								'</button>';
							bbar_wrap.dom.appendChild(overlay);
							$(overlay).on('click', 'button', function() {
								og.load_more_member_list_groups(man_ref);
							});
							this.groupsOverlay = overlay;
						}
					}
				},
				scope: this
			},
			'columnmove': {
				fn: function(old_index, new_index) {
					og.eventManager.fireEvent('replace all empty breadcrumb', null);
					
					if (og.member_list_listeners && og.member_list_listeners.columnmoved) {
						for (var i=0; i<og.member_list_listeners.columnmoved.length; i++) {
							og.member_list_listeners.columnmoved[i].call(null, this, old_index, new_index);
						}
					}
				},
				scope: this
			},
			'columnresize': {
				fn: function(col_index, newwidth) {
					this.afterColumnResize(this.getColumnModel(), col_index, newwidth);
					if (og.member_list_listeners && og.member_list_listeners.widthchange) {
						for (var i=0; i<og.member_list_listeners.widthchange.length; i++) {
							og.member_list_listeners.widthchange[i].call(null, this, col_index, newwidth);
						}
					}
				},
				scope: this
			},
			'resize': {
				fn: function() {
					var v = this.getView();
					if (v) {
						setTimeout(function(){
							// fit columns in the view
							if (v.forceFit) {
								v.fitColumns();
							}
							// adjust containers width
							$("#"+v.grid.id+" .x-grid3").css('width', '');
							$("#"+v.grid.id+" .x-grid3 .x-grid3-header-inner").css('width', '');
							$("#"+v.grid.id+" .x-grid3 .x-grid3-scroller").css('width', '');
							// adjust panel height
							var h = $("#"+v.grid.id).parent().height();
							v.grid.setSize({height:h});
							
						}, 200);
					}
					
				},
				scope: this
			}
		}
	});
	
};

Ext.extend(og.MemberManager, Ext.grid.GridPanel, {
	load: function(params) {
		
		if (!params) params = {};
		var start = 0;

		// Preference is source of truth; keep the selector display in sync with it
		this.syncMembersPerPageSelector();
		
		this.store.baseParams = {
			context: og.contextManager.plainContext(),
			dim_id: this.dimension_id,
			type_id: this.object_type_id,
			subtype_id: this.object_subtype_id
	    };
		
		if (og.member_list_grouping) {

			if (og.member_list_groups_info && og.member_list_groups_info[this.dimension_id+"-"+this.object_type_id]) {
				this.lastGroupField = og.member_list_groups_info[this.dimension_id+"-"+this.object_type_id].last_group_by;
			}
			// Active store.groupField (e.g. column header Group By) wins over a previous 'none'
			if (this.store.groupField) {
				this.lastGroupField = this.store.groupField;
			}
			var v = this.getView ? this.getView() : null;
			if (v) v.showGroups = (!!this.lastGroupField && this.lastGroupField !== 'none');
			if (this.lastGroupField && this.lastGroupField !== 'none') {
				this.store.baseParams.groupBy = this.lastGroupField;
				if (!this.store.groupField) {
					this.store.groupField = this.lastGroupField;
				}
				if (v) {
					v.showGroups = true;
					v.enableGrouping = true;
				}
			} else {
				delete this.store.baseParams.groupBy;
				this.store.groupField = false;
				if (typeof params.start == 'undefined') {
					start = (this.getBottomToolbar().getPageData().activePage - 1) * this.members_per_page;
				} else {
					start = parseInt(params.start, 10) || 0;
				}
			}

		} else {
			if (typeof params.start == 'undefined') {
				start = (this.getBottomToolbar().getPageData().activePage - 1) * this.members_per_page;
			} else {
				start = parseInt(params.start, 10) || 0;
			}
		}
		
		this.store.removeAll();
		this.store.load({
			params: Ext.applyIf(Ext.apply({}, params), {
				start: start,
				limit: this.members_per_page				
			})
		});
	},
	resetVars: function(){
		
	},

	/**
	 * Allowed page sizes for the member/project list selector.
	 * Capped to avoid "show all" performance issues.
	 */
	getMembersPerPageOptions: function() {
		return [25, 50, 100, 200, 500, og.MEMBERS_PER_PAGE_MAX];
	},

	addMembersPerPageSelector: function(pagingToolbar) {
		if (!pagingToolbar) return;

		var manager = this;
		pagingToolbar.on('render', function(tb) {
			var options = manager.getMembersPerPageOptions();
			var current = String(og.getMembersPerPage());
			manager.members_per_page = parseInt(current, 10);

			var select = document.createElement('select');
			select.className = 'x-form-text';
			select.style.cssText = 'width:60px;margin-left:4px;height:20px;';

			var found_current = false;
			for (var i = 0; i < options.length; i++) {
				var opt = document.createElement('option');
				opt.value = String(options[i]);
				opt.text = String(options[i]);
				if (opt.value === current) found_current = true;
				select.appendChild(opt);
			}
			if (!found_current && current) {
				var custom = document.createElement('option');
				custom.value = current;
				custom.text = current;
				select.insertBefore(custom, select.firstChild);
			}

			tb.addSeparator();
			tb.addText(lang('items x page') + ':');
			tb.add(select);

			// Set selection AFTER the select is attached to the toolbar (append can reset it)
			select.value = current;
			for (var j = 0; j < select.options.length; j++) {
				if (select.options[j].value === current) {
					select.selectedIndex = j;
					break;
				}
			}

			manager.pageSizeSelect = Ext.get(select);
			manager.pageSizeSelect.on('change', function() {
				manager.setMembersPerPage(parseInt(this.dom.value, 10));
			});
			// Re-apply after Ext finishes toolbar layout (avoids ending on last option / 500)
			setTimeout(function() {
				manager.syncMembersPerPageSelector();
			}, 0);
		}, this);
	},

	/**
	 * Keep selector UI in sync with the saved preference (never the other way around on load).
	 */
	syncMembersPerPageSelector: function() {
		var current = og.getMembersPerPage();
		this.members_per_page = current;
		if (this.pagingToolbar) {
			this.pagingToolbar.pageSize = current;
		}
		if (this.pageSizeSelect && this.pageSizeSelect.dom) {
			this.pageSizeSelect.dom.value = String(current);
			var opts = this.pageSizeSelect.dom.options;
			for (var i = 0; i < opts.length; i++) {
				if (opts[i].value === String(current)) {
					this.pageSizeSelect.dom.selectedIndex = i;
					break;
				}
			}
		}
	},

	setMembersPerPage: function(page_size) {
		page_size = parseInt(page_size, 10);
		if (!page_size || page_size < 1) return;
		if (page_size > og.MEMBERS_PER_PAGE_MAX) page_size = og.MEMBERS_PER_PAGE_MAX;
		if (page_size === this.members_per_page) return;

		this.members_per_page = page_size;
		og.config['members_per_page'] = page_size;
		if (this.pagingToolbar) {
			this.pagingToolbar.pageSize = page_size;
		}
		this.syncMembersPerPageSelector();

		var url = og.getUrl('account', 'update_user_preference', {
			name: 'members_per_page',
			value: page_size
		});
		og.openLink(url, {
			hideLoading: true,
			callback: function() {
				this.load({start: 0});
			},
			scope: this
		});
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
		if (text == '') {
			$(this.innerMessage).hide();
		} else {
			$(this.innerMessage).show();
		}
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
