og.ObjectPicker = function(config, object_id, object_id_no_select, ignore_context) {
	if (!config) config = {};

	if (!config.extra_list_params) config.extra_list_params = {};
	extra_list_param = Ext.util.JSON.encode(config.extra_list_params);
	var url_params = {
		ajax: true,
		include_comments: true,
		id_no_select: object_id_no_select,
		count_results : 0,
		extra_list_params: extra_list_param
	};

	var url_controller = 'object';
	var url_action = 'list_objects';
	if (config.url_controller) url_controller = config.url_controller;
	if (config.url_action) url_action = config.url_action;

	var available_fields = [
		'name', 'object_id', 'type', 'ot_id', 'icon', 'object_id', 'mimeType',
		'createdBy', 'createdById', 'dateCreated', 'assignedTo',
		'updatedBy', 'updatedById', 'dateUpdated', 'startDate', 'dueDate',
		'memPath', // this field contains all the classification information
	];

	// let plugins add more fields to use in this component
	if (og.object_picker_additional_fields) {
		available_fields = available_fields.concat(og.object_picker_additional_fields);
	}

	// add dimension keys to available fields
	var dim_names = [];	
	for (did in og.dimensions_info) {
		if (isNaN(did)) continue;
		dim_names.push('dim_' + did);
	}
	available_fields = available_fields.concat(dim_names);
	
	var Grid = function(config) {
		if (!config) config = {};
		this.store = new Ext.data.Store({
        	proxy: new Ext.data.HttpProxy(new Ext.data.Connection({
				method: 'GET',
            	url: og.getUrl(url_controller, url_action, url_params)
        	})),
        	reader: new Ext.data.JsonReader({
            	root: 'objects',
            	totalProperty: 'totalCount',
            	id: 'id',
            	fields: available_fields
        	}),
        	remoteSort: true,
        	listeners: {
        		'load': function(store, result) {
        			// fix count query to be quick and then don't hide texts and reloadGridPagingToolbar
        			var bbar = Ext.getCmp('obj_picker_grid').getBottomToolbar();
        			if (bbar) {
        				bbar.displayMsg = '';
        				bbar.afterPageText = '';
        			}
        			/*this.lastOptions.params = this.baseParams;
        			this.lastOptions.params.count_results = 1;
        			Ext.getCmp('obj_picker_grid').reloadGridPagingToolbar('object','list_objects','obj_picker_grid');*/

					// draw classification breadcrumbs
					og.eventManager.fireEvent('replace all empty breadcrumb', null);
        		}
        	}
    	});
		
		this.store.baseParams.ignore_context = ignore_context ? '1' : '0';
		
		if (config.extra_member_ids) {
			this.store.baseParams.extra_member_ids = config.extra_member_ids;
		}
			
    	this.store.setDefaultSort('dateUpdated', 'desc');

		function renderIcon(value, p, r) {
			var classes = "db-ico ico-unknown ico-" + r.data.type;
			if (r.data.mimeType) {
				var path = r.data.mimeType.replace(/\./ig, "_").replace(/\//ig, "-").split("-");
				var acc = "";
				for (var i=0; i < path.length; i++) {
					acc += path[i];
					classes += " ico-" + acc;
					acc += "-";
				}
			}
			return String.format('<div class="{0}" />', classes);
		}
        
		function renderDate(value, p, r) {
			if (!value) {
				return "";
			}
			return value;
		}

		var sm = new Ext.grid.RowSelectionModel();

		//set array to render columns
		var arrayColumns = [{
				id: 'icon',
				header: '&nbsp;',
				dataIndex: 'icon',
				width: 28,
				renderer: renderIcon,
				sortable: false,
				fixed:true,
				resizable: false,
				hideable:false,
				menuDisabled: true
			},{
				id: 'name',
				header: lang("name"),
				dataIndex: 'name',
				renderer: og.clean,
				width: 350
			},{
				id: 'type',
				header: lang('type'),
				dataIndex: 'type',
				width: 60,
				hidden: true,
				sortable: false
			},{
				id: 'last',
				header: lang("last update"),
				dataIndex: 'dateUpdated',
				width: 90,
				renderer: renderDate
			},{
				id: 'user',
				header: lang('updated by'),
				dataIndex: 'updatedBy',
				width: 90,
				renderer: og.clean,
				sortable: false,
				hidden: true
			},{
				id: 'created',
				header: lang("created on"),
				dataIndex: 'dateCreated',
				width: 90,
				renderer: renderDate,
				hidden: true
			},{
				id: 'assignedto',
				header: lang('assigned to'),
				dataIndex: 'assignedTo',
				width: 120,
				renderer: 'string',
				hidden: true
			},{
				id: 'author',
				header: lang("created by"),
				dataIndex: 'createdBy',
				width: 90,
				renderer: og.clean,
				hidden: true
			},{
				id: 'start',
				header: lang("start date"),
				dataIndex: 'startDate',
				width: 90,
				renderer: renderDate,
				sortable: false,
				hidden: false
			},{
				id: 'due',
				header: lang("due date"),
				dataIndex: 'dueDate',
				width: 90,
				renderer: renderDate,
				sortable: false,
				hidden: false
			}
		];

		// dimension columns for column model
		// use og.dimensionPanels to put the same order as in the left panel dimension trees
		for (let i=0; i < og.dimensionPanels.length; i++) {
			let dim_panel = og.dimensionPanels[i];
			if (!dim_panel) continue;

			let did = dim_panel.dimensionId;
			arrayColumns.push({
				id: 'dim_' + did,
				header: dim_panel.title,
				dataIndex: 'dim_' + did,
				sortable: false,
				renderer: og.renderDimCol
			});
			og.breadcrumbs_skipped_dimensions[did] = did;
		}
		// --

		var cm = new Ext.grid.ColumnModel(arrayColumns);
	    cm.defaultSortable = true;

		Grid.superclass.constructor.call(this, Ext.apply(config, {
	        store: this.store,
			layout: 'fit',
			id: 'obj_picker_grid',
	        cm: cm,
	        stripeRows: true,
	        loadMask: true,
	        bbar: new og.CurrentPagingToolbar({
	            pageSize: og.config['files_per_page'],
	            store: this.store,
	            displayInfo: true,
	            displayMsg: lang('displaying objects of'),
	            emptyMsg: lang("no objects to display")
	        }),
			viewConfig: {
	            forceFit: false // allow horizontal scroll
	        },
			sm: sm,
			listeners: {
				'columnmove': {
					fn: function(old_index, new_index) {
						og.eventManager.fireEvent('replace all empty breadcrumb', null);
					},
					scope: this
				}
			}
	    }));
	}

	Ext.extend(Grid, Ext.grid.GridPanel, {
		member_filter: {},

		getSelected: function() {
			return this.getSelectionModel().getSelections();
		},
		//filter by name
		filterSelect: function(filter) {

			// get column index for the columns we are going to modify
			var dueDateIndex = this.getColumnModel().findColumnIndex('dueDate');
			var startDateIndex = this.getColumnModel().findColumnIndex('startDate');
			var nameIndex = this.getColumnModel().findColumnIndex('name');
			var lastUpdateIndex = this.getColumnModel().findColumnIndex('dateUpdated');
			var assignedToIndex = this.getColumnModel().findColumnIndex('assignedTo');

			/**
			 * If the User clicks on Task dimension (in Link Objects modal), the startDate and dueDate
			 * columns will be visible.
			 * Sets width of columns to see better.
			 */
			//set the columns to visible or not
			if(filter && filter.type) {
				let filtersMenu = filter.type.split(',');
				if(filtersMenu.indexOf("task") > -1) {
					this.getColumnModel().setHidden(startDateIndex, false);
					this.getColumnModel().setHidden(dueDateIndex, false);
					this.getColumnModel().setHidden(assignedToIndex, false);
				}
				else {
					this.getColumnModel().setHidden(startDateIndex, true);
					this.getColumnModel().setHidden(dueDateIndex, true);
					this.getColumnModel().setHidden(assignedToIndex, true);
				}
				this.getColumnModel().setColumnWidth(nameIndex, 400);//name
				this.getColumnModel().setColumnWidth(lastUpdateIndex, 130);//last update
				this.getColumnModel().setColumnWidth(startDateIndex, 130);//start date
				this.getColumnModel().setColumnWidth(dueDateIndex, 130);//due date
				this.getColumnModel().setColumnWidth(assignedToIndex, 130);//assignedTo date
			}

			var use_single_filter_type = true;
			if (filter && filter.filter == 'type') {
				this.type = filter.type;
				this.store.baseParams.type = this.type;
				// no filter selected => only allowed object types
				if (this.store.baseParams.type == '') {
					var types = [];
					for (var i=0; i<og.objPickerTypeFilters.length; i++) {
						types.push(og.objPickerTypeFilters[i].type);
					}
					var use_single_filter_type = false;
					this.store.baseParams.type = types.join(',');
				}
			}
			var member_ids = [];

			var check_ot_member_selector = false;
			if (use_single_filter_type && this.store.baseParams.type != '') {
				var object_type_dimensions = og.dimensionsByObjectTypeInMemberSelector[this.store.baseParams.type];
				if (object_type_dimensions) {
					check_ot_member_selector = true;
				}
			}

			for (x in this.member_filter) {
				if (check_ot_member_selector && object_type_dimensions.indexOf(parseInt(x)) == -1) {
					continue;
				}
				for (var mi=0; mi<this.member_filter[x].length; mi++) {
					member_ids.push(this.member_filter[x][mi]);
				}
			}
			
			// always igonre context, use the filters that the user has chosen 
			this.store.baseParams.ignore_context = 1;
			
			this.store.baseParams.extra_member_ids = Ext.util.JSON.encode(member_ids);
			
			this.load();
		},
		
		load: function(params) {
			Ext.apply(params, {
				start: 0,
				limit: og.config['files_per_page']
			});
			this.store.removeAll();
			this.store.load({
				params: params
			});
		}
	});
	
	var TypeFilter = function(config) {
		TypeFilter.superclass.constructor.call(this, Ext.apply(config, {
			rootVisible: false,
			lines: false,
			root: new Ext.tree.TreeNode(lang('filter')),
			collapseFirst: false
		}));
	
		this.filters = this.root.appendChild(
			new Ext.tree.TreeNode({
				text: lang('all'),
				expanded: true
			})
		);
		this.filters.filter = {filter: 'type', id: 0, name: ''};		
		this.getSelectionModel().on({
			'selectionchange' : function(sm, node) {
				if (node && !this.pauseEvents) {
					this.fireEvent("filterselect", node.filter);
				}
			},
			scope:this
		});
		this.addEvents({filterselect: true});
	};
	Ext.extend(TypeFilter, Ext.tree.TreePanel, {
		addFilter: function(filter, selected, config) {
			if (!config) config = {};
			var exists = this.getNodeById(filter.filter + filter.id);
			if (exists) {
				return;
			}
			var config = Ext.apply(config, {
				iconCls: filter.iconCls || 'ico-' + filter.id,
				leaf: true,
				text: filter.name,
				cls: selected ? 'x-tree-selected' : '',
				id: filter.id
			});
			var node = new Ext.tree.TreeNode(config);
			node.filter = filter;
			this.filters.appendChild(node);
			return node;
		},
		loadFilters: function(types, selected_type) {
			this.removeAll();
			var all_types = [];
			for (var i=0; i<og.objPickerTypeFilters.length; i++) {
				var filter = og.objPickerTypeFilters[i];
				if (!types) {
					this.addFilter(filter, filter.type == selected_type);
				} else {
					for (var j=0; j<types.length; j++) {
						if (types[j] == filter.type) {
							this.addFilter(filter, filter.type == selected_type);
							break;
						}
					}
				}
				all_types.push(filter.type);
			}
			this.filters.filter.type = selected_type ? selected_type : all_types.join(',');
			
			this.filters.expand();
			
			this.pauseEvents = true;
			this.filters.select();
			this.pauseEvents = false;
		},
		
		removeAll: function() {
			var node = this.filters.firstChild;
			while (node) {
				var aux = node;
				node = node.nextSibling;
				aux.remove();
			}
		}
	});
	
	Ext.reg('typefilter', TypeFilter);
	
	var hideUploadButton = config.hideUploadButton || og.preferences.link_objects_hide_upload_button;
	
	var tbarItems = [
	                 {
						text: lang('upload'),
			            tooltip: lang('quick upload desc'),
			            iconCls: 'ico-upload',
			            hidden: (hideUploadButton ? hideUploadButton : false),
			            handler: function() {
							var quickId = Ext.id();
							var picker = this;
							og.openLink(og.getUrl('files', 'quick_add_files', {genid: quickId, object_id: object_id}), {
			        			preventPanelLoad: true,
								onSuccess: function(data) {
				        			og.ExtendedDialog.show({
				                		html: data.current.data,
				                		height: 300,
				                		width: 600,
				                		title: lang('upload file'),
				                		ok_fn: function() {
					        				og.doFileUpload(quickId, {
					        					callback: function() {
					        						form = document.getElementById(quickId + 'quickaddfile');
					        						og.ajaxSubmit(form, {
						    							callback: function(success, data) {
					        								if (success) {
					        									picker.grid.store.reload();
					        								}
						    							}
						    						});
					        					}
					        				});
					                		og.ExtendedDialog.hide();
				            			}
				                	});
				                	return;
			        			}
			        		});
						},
						scope: this
					},
					{
						text: lang('refresh'),
			            tooltip: lang('refresh desc'),
			            iconCls: 'op-ico-refresh',
						handler: function() {
							this.grid.filterSelect();
							this.grid.store.reload();
						},
						scope: this
					},
					"-",
					/*{
						xtype : 'label',
						text: lang('filter') + ': ',
			            iconCls: 'ico-search',
						scope: this
					},*/
					{
						xtype: 'textfield',
						id: 'txtFilreByObjectName',
						fieldLabel: lang('name'),
						emptyText: lang('filter') + '...',
						tooltip: lang('filtre name desc'),
						listeners:{
							render: {
								fn: function(f){
									f.el.on('keyup', function(e) {
										this.filterName(e.target.value);
										this.grid.store.reload();
									},
									this, {buffer: 350});
								},
								scope: this
							}
						},
						scope: this
					}
    ];
	
	if (config.more_tbar_items) {
		for (var i=0; i<config.more_tbar_items.length; i++) {
			tbarItems.push(config.more_tbar_items[i]);
		}
	}

	//get the objects id from og.get_object_type_by_name
	let object_type = {
		id: 0, //set object type id=0 to load default filtes
	};

	//if config.selected_type is not undefined, null or empty get the object type
	if(config.selected_type!=undefined && config.selected_type!='' && config.selected_type!=null) 
	{
		object_type=og.get_object_type_by_name(config.selected_type);
	}

	//construct object picker
	og.ObjectPicker.superclass.constructor.call(this, Ext.apply(config, {
		y: 50,
		width: 800,
		height: 480,
		id: 'object-picker',
		layout: 'border',
		modal: true,
		closeAction: 'close',
		iconCls: 'op-ico',
		title: lang('select an object'),
		buttons: [{
			text: lang('ok'),
			handler: this.accept,
			scope: this
		},{
			text: lang('cancel'),
			handler: this.cancel,
			scope: this
		}],
		items: [
			{
				region: 'center',
				layout: 'fit',
				tbar: tbarItems,
				items: [
					this.grid = new Grid(config)
				]
			},
			{
				layout: 'border',
				split: true,
				width: 200,
				region: 'west',
				collapsible: true,
				hidden: config.hideFilters,
				title: lang('filter'),
				items: [{
						xtype: 'typefilter',
						id: 'typeFilter',
						region: 'north',
						autoScroll: true,
						listeners: {
							filterselect: {
								fn: this.grid.filterSelect,
								scope: this.grid
							}
						}
					},{
						xtype: 'panel',
						id: 'dimFilter',
						region: 'center',
						autoScroll: true,
						split: true,
						autoLoad: {
							scripts: true,
							url: og.getUrl('dimension', 'linked_object_filters', {
								context: config.context ? config.context : og.contextManager.plainContext(),
								object_type_id: object_type.id,
								skip_default_member_selections: true,
								add_on_remove_function: true
							})
						},
						listeners: {
							memberselected: {
								fn: function(context) {
									var grid = Ext.getCmp('obj_picker_grid');
									if (grid) {
										grid.member_filter = context;
										grid.filterSelect();
									}
								}
							},
							clearfilters: {
								fn: function(genid) {
									grid = Ext.getCmp('obj_picker_grid');
									for (x in grid.member_filter) {
										var combo = Ext.getCmp(genid + 'add-member-input-dim' + x);
										if (combo) combo.clearValue();
									}
									grid.member_filter = {};
									grid.filterSelect();
								}
							}
						}
					}
				]
			}
		]
	}));
	this.grid.on('rowdblclick', this.accept, this);
	this.addEvents({'objectselected': true});
}

Ext.extend(og.ObjectPicker, Ext.Window, {
	accept: function() {
		this.fireEvent('objectselected', this.grid.getSelected());
		this.close();
	},
	
	cancel: function() {
		this.close();
	},
	
	loadFilters: function(config) {
		if (!config) config = {};
		delete this.grid.store.baseParams.type;
		var typef = this.findById('typeFilter');
		typef.loadFilters(config.types, config.selected_type);
		this.grid.store.baseParams.type = typef.filters.filter.type;
	},
	filterName: function(value) {
		this.grid.store.baseParams.name = value;
	},
	load: function() {
		this.grid.filterSelect();
		this.grid.load();
	}
});

og.ObjectPicker.show = function(callback, scope, config, object_id, object_id_no_select) {
	if (!config) config = {};
    
	this.dialog = new og.ObjectPicker(config, object_id, object_id_no_select, config.ignore_context);
	
	this.dialog.loadFilters(config);
	if (config.context) {
		this.dialog.grid.store.baseParams.context = config.context;
		var con = Ext.util.JSON.decode(config.context);
	} else {
		var con = og.contextManager.dimensionMembers;
	}
	
	var has_extra_member_ids = typeof(config.extra_member_ids) != 'undefined';
	if (!has_extra_member_ids) config.extra_member_ids = [];
	
	config.ignore_context = 1;
	this.dialog.grid.member_filter = {};
	
	// initialize the member filters with the current context
	for (x in con) {
		this.dialog.grid.member_filter[x] = [];
		for (i=0; i<con[x].length; i++) {
			if (parseInt(con[x][i]) > 0) {
				this.dialog.grid.member_filter[x].push(con[x][i]);
				if (!has_extra_member_ids) {
					config.extra_member_ids.push(con[x][i]);
				}
			}
		}
	}
	
	this.dialog.load();
	this.dialog.purgeListeners();
	this.dialog.on('objectselected', callback, scope, {single:true});
	this.dialog.on('hide', og.restoreFlashObjects);
	this.dialog.on('close', og.restoreFlashObjects);
	og.hideFlashObjects();
	this.dialog.show();
	var pos = this.dialog.getPosition();
	if (pos[0] < 0) pos[0] = 0;
	if (pos[1] < 0) pos[1] = 0;
	this.dialog.setPosition(pos[0], pos[1]);
}