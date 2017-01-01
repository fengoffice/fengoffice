

og.objSelectorRemoveSelected = function(object_id, row_id) {
	
	var selector = Ext.getCmp('object-selector');
	
	// add id to the removed_ids object
	selector.ids_to_remove[object_id] = object_id;
	
	// remove from the selected_ids object
	if (selector.ids_to_add[object_id]) {
		delete selector.ids_to_add[object_id];
	}
	
	// remove from grid
	var record = selector.grid_selected.store.getById(row_id);
	selector.grid_selected.store.remove(record);
	
	// deselect from left grid
	var sel_model = selector.grid.getSelectionModel();
	for (var i=0; i<selector.grid.store.data.items.length; i++) {
		var rec = selector.grid.store.data.items[i];
		if (rec.data.object_id == object_id) {
			sel_model.suspendEvents();
			sel_model.deselectRow(i);
			sel_model.resumeEvents();
			break;
		}
	}
	
	// trigger save selection
	selector.saveSingleSelection();
}

og.objSelectorAfterRowDeselected = function(r) {
	
	var object_id = r.data.object_id;
	var selector = Ext.getCmp('object-selector');

	var row_id = -1;
	for (var i=0; i<selector.grid_selected.store.data.items.length; i++) {
		var sel_rec = selector.grid_selected.store.data.items[i];
		if (sel_rec.data.object_id == object_id) {
			row_id = sel_rec.id;
			break;
		}
	}
	if (row_id != -1) {
		og.objSelectorRemoveSelected(object_id, row_id);
	}
	
}




og.ObjectSelector = function(config, object_id, object_id_no_select, ignore_context) {
	if (!config) config = {};
	
	if (!config.extra_list_params) config.extra_list_params = {};
	extra_list_param = Ext.util.JSON.encode(config.extra_list_params);
	
	if (typeof(config.show_associated_dimension_filters) == 'undefined') {
		config.show_associated_dimension_filters = false;
	}
		
	var url_params = {
		ajax: true,
		include_comments: true,
		id_no_select: object_id_no_select,
		count_results : 0,
		type: config.selected_type,
		genid: genid,
		extra_list_params: extra_list_param
	};
	
	var Grid = function(config) {
		if (!config) config = {};
		this.store = new Ext.data.Store({
        	proxy: new Ext.data.HttpProxy(new Ext.data.Connection({
				method: 'GET',
            	url: og.getUrl('object', 'list_objects_to_select')
        	})),
        	reader: new Ext.data.JsonReader({
            	root: 'objects',
            	totalProperty: 'totalCount',
            	id: 'id',
            	fields: [
	                'name', 'object_id', 'type', 'ot_id', 'icon'
            	]
        	}),
        	remoteSort: true,
        	listeners: {
        		'load': function(store, result) {
        			
        			var grid = Ext.getCmp(config.id);
        			if (grid) {
        				
        				// check the selected objects
	        			var objects = this.reader.jsonData.objects;
	        			for (var i=0; i<objects.length; i++) {
	        				var obj = objects[i];
	        				
	        				if (obj.checked) {
	        					
	        					for (var j=0; j<store.data.items.length; j++) {
	        						var storeobj = store.data.items[j];
	        						
	        						if (storeobj.data.object_id == obj.object_id) {
	        							var sm = grid.getSelectionModel();
	        							sm.suspendEvents();
	        							sm.selectRow(j, true);
	        							sm.resumeEvents();
	        							break;
	        						}
	        					}
	        				}
	        			}

						this.lastOptions.params = this.baseParams;
						this.lastOptions.params.count_results = 1;

						Ext.getCmp(config.id).reloadGridPagingToolbar('object','list_objects_to_select',config.id);

	        			// fix count query to be quick and then don't hide texts and reloadGridPagingToolbar
        			}
        		}
        	}
    	});


		if (config.id != 'obj_selector_grid'){
			
	    	this.store = new Ext.data.Store({
        	proxy: new Ext.data.HttpProxy(new Ext.data.Connection({
				method: 'GET',
            	url: og.getUrl('object', 'list_all_selected_objects')
        	})),
        	reader: new Ext.data.JsonReader({
            	root: 'objects',
            	totalProperty: 'totalCount',
            	id: 'id',
            	fields: [
	                'name', 'object_id', 'type', 'ot_id', 'icon', 'object_id', 'mimeType',
	                'createdBy', 'createdById', 'dateCreated',
					'updatedBy', 'updatedById', 'dateUpdated'
            	]
        	}),
        	remoteSort: true,
        	listeners: {
        		'load': function(store, result) {
        			// fix count query to be quick and then don't hide texts and reloadGridPagingToolbar
        			var grid = Ext.getCmp(config.id);
					if (grid) {
						this.lastOptions.params = this.baseParams;
						this.lastOptions.params.count_results = 1;

						Ext.getCmp(config.id).reloadGridPagingToolbar('object','list_all_selected_objects',config.id);
        			}
        		}
        	}
    	});
	    }

		this.store.baseParams = jQuery.extend({}, url_params);
		this.store.baseParams.ignore_context = ignore_context ? '1' : '0';

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
		
		function renderRemove(value, p, r) {
			return '<a href="#" onclick="og.objSelectorRemoveSelected('+r.data.object_id+','+r.id+');" class="link-ico ico-delete">&nbsp;</a>';
		}
        
		function renderDate(value, p, r) {
			if (!value) {
				return "";
			}
			return value;
		}

		//var sm = new Ext.grid.RowSelectionModel();
		var sm = new Ext.grid.CheckboxSelectionModel();
		
		// override onMouseDown event to prevent other rows deselection when selecting a row
		sm.onMouseDown = function(e, t){
		    if(e.button === 0){ // Only fire if left-click
	            e.stopEvent();
	            var row = e.getTarget('.x-grid3-row');
	            if(row){
	                var index = row.rowIndex;
	                if(this.isSelected(index)){
	                    this.deselectRow(index);
	                }else{
	                    this.selectRow(index, true);
	                }
	            }
	        }
	    };
		
		sm.on('rowdeselect',
			function(sm, rowIndex, r) {
				if (sm.grid.id == 'obj_selector_grid') {
					// remove
					og.objSelectorAfterRowDeselected(r);
				}
			}
		);
		sm.on('rowselect',
			function(sm, rowIndex, r) {
				
			try {
				
				// update selected ids and removed ids objects.
				var selector = Ext.getCmp('object-selector');
				if (selector && r && r.data && !isNaN(r.data.object_id)) {
					
					var save_selection = true;
					var object_id = r.data.object_id;
					
					if (sm.grid.id == 'obj_selector_grid') {
						// dont submit the save request if id is already in the array
						if (!isNaN(selector.ids_to_add[object_id])) save_selection = false;
						
						if (save_selection) {
							// add record in the selected grid, dont add duplicates.
							var do_add = true;
							for (var i=0; i<selector.grid_selected.store.data.items.length; i++) {
								var rec = selector.grid_selected.store.data.items[i];
								if (rec.data.object_id == r.data.object_id) {
									do_add = false;
									break;
								}
							}
							if (do_add) {
								selector.grid_selected.store.add(r);
							}
							
							// add id to the selected_ids object
							selector.ids_to_add[object_id] = object_id;
							
							// remove from the removed_ids object
							if (selector.ids_to_remove[object_id]) {
								delete selector.ids_to_remove[object_id];
							}
						}
					}
					
					if (save_selection) {
						selector.saveSingleSelection();
					}
					
				}
			} catch (e) {
			//	console.log(e);
			}
		});
		
		var columns = [{
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
				renderer: og.clean
				//,width: 120
	        },{
				id: 'remove',
				header: '',
				sortable: false,
	        	fixed:true,
	        	resizable: false,
	        	renderer: renderRemove,
				hidden: config.id == 'obj_selector_grid',
				width: 40
	        }];

	    if(config.chkBox){
	    	columns.push(sm);
	    }

		var cm = new Ext.grid.ColumnModel(columns);
	    cm.defaultSortable = true;
    
		Grid.superclass.constructor.call(this, Ext.apply(config, {
	        store: this.store,
			layout: 'fit',
			id: config.id,
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
	            forceFit:true
	        },
			sm: sm
	    }));
	}
	Ext.extend(Grid, Ext.grid.GridPanel, {
		member_filter: {},
		
		getSelected: function() {
			return this.getSelectionModel().getSelections();
		},
		
		filterSelect: function(filter) {
			var member_ids = [];
			for (x in this.member_filter) {
				for (var mi=0; mi<this.member_filter[x].length; mi++) {
					member_ids.push(this.member_filter[x][mi]);
				}
				//member_ids.push(this.member_filter[x]);
			}
			this.store.baseParams.extra_member_ids = Ext.util.JSON.encode(member_ids);
			this.store.baseParams.ignore_context = this.store.baseParams.ignore_context || member_ids.length > 0 ? '1' : '0';
			
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
		
	var hideUploadButton = config.hideUploadButton || og.preferences.link_objects_hide_upload_button;
	
	var tbarItems = [
					{
						xtype: 'textfield',
						id: 'byObjectName',
						fieldLabel: lang('name'),
						emptyText: lang('filter') + '...',
						tooltip: lang('filtre name desc'),
						listeners:{
							render: {
								fn: function(f){
									f.el.on('keyup', function(e) {
										this.filterName(e.target.value, false);
										this.grid.store.reload();
									},
									this, {buffer: 350});
								},
								scope: this
							}
						},
						scope: this
					},
					{
						text: lang('select all'),
						cls:"link-btn",
					    tooltip: lang('select all desc'),
					    hidden: (hideUploadButton ? hideUploadButton : false),
					    handler: function() {
					    	var object_selector = this;
					    	
					    	// clean single selection arrays
					    	object_selector.ids_to_add = {};
					    	object_selector.ids_to_remove = {};
					    	
					    	// prevent single selection submit
					    	if (object_selector.send_selected_timeout) {
					    		clearTimeout(object_selector.send_selected_timeout);
					    		object_selector.send_selected_timeout = null;
					    	}
					    	
					    	// save all selected objects
					    	var params = this.grid.store.baseParams;
					    	params.genid = genid;
					    	params.select_all = true;
					    	
							og.openLink(og.getUrl('object', 'save_selected_objects', params), {
			        			preventPanelLoad: true,
								onSuccess: function(data) {
									object_selector.grid_selected.store.reload();
									
									object_selector.grid.getSelectionModel().selectAll();
			        			}
			        		});
						},
						scope: this
					}
    ];

    var selectedTbarItems = [	                					
					{
						xtype: 'textfield',
						id: 'selectedByObjectName',
						size: 15,
						fieldLabel: lang('name'),
						emptyText: lang('filter') + '...',
						tooltip: lang('filtre name desc'),
						listeners:{
							render: {
								fn: function(f){
									f.el.on('keyup', function(e) {
										this.filterName(e.target.value, true);
										this.grid_selected.store.reload();
									},
									this, {buffer: 350});
								},
								scope: this
							}
						},
						scope: this
					},
					{
						text: lang('remove all'),
					    tooltip: lang('remove all desc'),
						cls:"link-btn",
					    hidden: false,
					    handler: function() {
					    	var object_selector = this;
					    	
					    	// clean single selection arrays
					    	object_selector.ids_to_add = {};
					    	object_selector.ids_to_remove = {};
					    	
					    	// prevent single selection submit
					    	if (object_selector.send_selected_timeout) {
					    		clearTimeout(object_selector.send_selected_timeout);
					    		object_selector.send_selected_timeout = null;
					    	}
					    	
					    	og.openLink(og.getUrl('object', 'remove_all_selected_objects', {genid: genid}), {
				    			preventPanelLoad: true,
								onSuccess: function(data) {
									object_selector.grid_selected.store.removeAll();
			        			}
				    		});
					    	
						},
						scope: this
					}
    ];
	
	if (config.more_tbar_items) {
		for (var i=0; i<config.more_tbar_items.length; i++) {
			tbarItems.push(config.more_tbar_items[i]);
		}
	}

	og.ObjectSelector.superclass.constructor.call(this, Ext.apply(config, {
		y: 50,
		width: 900,
		height: 500,
		id: 'object-selector',
		cls: 'ext-modal-object-list',
		layout: 'border',
		modal: true,
		closeAction: 'close',
		title: lang('recipients'),
		buttons: [{
			text: lang('ok'),
			cls:"submit-btn-blue",
			handler: this.accept,
			scope: this
		},{
			text: lang('cancel'),
			cls:"cancel-btn-g",
			handler: this.cancel,
			scope: this
		}],
		items: [
			{
				region: 'center',
				layout: 'fit',
				id:'obj_selector_grid_panel',
				tbar: tbarItems,
				items: [
					this.grid = new Grid({id : 'obj_selector_grid', chkBox: true})
				]
			},
			{
				region: 'east',
				layout: 'fit',
				id:'obj_selector_grid_selected_panel',
				tbar: selectedTbarItems,
				width: 400,
				items: [
					this.grid_selected = new Grid({id : 'obj_selector_grid_selected', chkBox: false})
				]
			},
			{
				layout: 'border',
				split: true,
				width: 350,
				region: 'west',
				collapsible: false,
				hidden: config.hideFilters,
				tbar: [],
				id: 'obj_selector_filters',
				items: [
					{
						xtype: 'panel',
						id: 'dimFilter',
						region: 'center',
						autoScroll: true,
						split: true,
						autoLoad: {
							scripts: true,
							url: og.getUrl('dimension', 'linked_object_filters', {
								context: og.contextManager.plainContext(),
								show_associated_dimension_filters: config.show_associated_dimension_filters
							})
						},
						listeners: {
							memberselected: {
								fn: function(context) {
									var grid = Ext.getCmp('obj_selector_grid');
									if (grid) {
										grid.member_filter = context;
										//grid.member_filter[member.dim] = member.id;
										grid.filterSelect();
									}
								}
							},
							clearfilters: {
								fn: function(genid) {
									grid = Ext.getCmp('obj_selector_grid');
									for (x in grid.member_filter) {
										var combo = Ext.getCmp(genid + 'add-member-input-dim' + x);
										if (combo) combo.clearValue();
									}
									grid.member_filter = {};
									grid.filterSelect();
								}
							}
						}
					},
					{
						xtype: 'panel',
						id: 'object_selector_prop_filter',
						region: 'south',
						autoScroll: true,
						split: true						
					}
				]
			}
		]
	}));
		
	this.addEvents({'save_and_close': true});
}

Ext.extend(og.ObjectSelector, Ext.Window, {
	accept: function() {
		this.fireEvent('save_and_close', this.grid.getSelected());
		this.close();
	},
	
	cancel: function() {
		this.fireEvent('cancel_and_close', this.grid.getSelected());
		this.close();
	},
	filterName: function(value, grid_selected) {
		if(grid_selected){
			this.grid_selected.store.baseParams.name = value;
		}else{
			this.grid.store.baseParams.name = value;
		}
	},
	load: function() {
		this.grid.store.baseParams.context = og.contextManager.plainContext();
		this.grid.load();

		this.grid_selected.store.baseParams.context = og.contextManager.plainContext();
		this.grid_selected.load();
	},
	
	doSaveSingleSelection: function() {
		var selector = this;
		
		var params = {};
    	params.genid = genid;
    	params.ids_to_add = Ext.util.JSON.encode(selector.ids_to_add);
    	params.ids_to_remove = Ext.util.JSON.encode(selector.ids_to_remove);
    	
    	selector.ids_to_add = {};
    	selector.ids_to_remove = {};
    	
		og.openLink(og.getUrl('object', 'save_selected_objects', params), {
			hideLoading: true,
			preventPanelLoad: true
		});
	},
	saveSingleSelection: function() {
		var selector = this;
		
		// clear save timeout
		if (selector.send_selected_timeout) {
			clearTimeout(selector.send_selected_timeout);
		}
		// set timeout to save the selected/removed objects
		selector.send_selected_timeout = setTimeout(function() {
			
			selector.doSaveSingleSelection();
			selector.send_selected_timeout = null;
			
		}, 2000);
	},
	revertSelectionChanges : function() {
		// clean single selection arrays
    	this.ids_to_add = {};
    	this.ids_to_remove = {};
    	
    	// prevent single selection submit
    	if (this.send_selected_timeout) {
    		clearTimeout(this.send_selected_timeout);
    		this.send_selected_timeout = null;
    	}
    	
    	og.openLink(og.getUrl('object', 'revert_object_selector_changes', {genid: genid}));
	},
	isReadyToLoad : function() {
		return this.all_type_menu_items_rendered && this.all_cp_filters_rendered;
	}
	
});

og.ObjectSelector.show = function(callback, scope, config, object_id, object_id_no_select) {
	if (!config) config = {};
	if (!config.ignore_context) config.ignore_context = 0;
	
	var cp_array = [];
	var object_type = config.types && config.types.length > 0 ? config.types[0] : null;
	if (object_type && og.custom_properties_by_type[object_type]) {
		cp_array = og.custom_properties_by_type[object_type];
	}
	
	this.dialog = new og.ObjectSelector(config, object_id, object_id_no_select, config.ignore_context);
	
	//this.dialog.loadFilters(config);
	if (config.context) {
		this.dialog.grid.store.baseParams.context = config.context;
	}
	
	// if no type menu set all_type_menu_items_rendered as true
	this.dialog.all_type_menu_items_rendered = !config.registered_types_of_objects || config.registered_types_of_objects.length==0;
	// if no cp filters set all_cp_filters_rendered as true
	this.dialog.all_cp_filters_rendered = cp_array.length==0 || typeof(og.custom_properties_filter)=='undefined';
	
	
	// add current context to the selected member ids variable
	var member_ids = [];
	var context = Ext.util.JSON.decode(og.contextManager.plainContext());
	for (var did in context) {
		var members = context[did];
		for (var k=0; k<members.length; k++) {
			if (members[k] > 0) member_ids.push(members[k]);
		}
	}
	if (member_ids.length > 0) {
		this.dialog.grid.store.baseParams.extra_member_ids = Ext.util.JSON.encode(member_ids);
	}
	
	// if grid can be loaded (no filters) => then load it
	if (this.dialog.isReadyToLoad()) {
		this.dialog.load();
	}
	
	this.dialog.purgeListeners();
	
	if (!config.keep_selector_changes) {
		// revert changes on close or cancel
		this.dialog.on('cancel_and_close', function(){
			this.revertSelectionChanges();
		});
		
		// after save remove temp vars that contains the original selection
		this.dialog.on('save_and_close', function(){
			
			this.doSaveSingleSelection();
			
			og.openLink(og.getUrl('object', 'clean_temp_object_selector_vars', {genid: genid}));
		});
	}
	
	this.dialog.on('save_and_close', callback, scope, {single:true});
	this.dialog.on('hide', og.restoreFlashObjects);
	this.dialog.on('close', og.restoreFlashObjects);
	
	og.hideFlashObjects();
	this.dialog.show();
	
	var pos = this.dialog.getPosition();
	if (pos[0] < 0) pos[0] = 0;
	if (pos[1] < 0) pos[1] = 0;
	this.dialog.setPosition(pos[0], pos[1]);
	
	// object that contains object ids to select
	this.dialog.ids_to_add = {};
	// object that contains object ids to remove
	this.dialog.ids_to_remove = {};
	// send selected/removed ids to server timeout variable
	this.dialog.send_selected_timeout = null;
	
	if (og.custom_properties_filter) {
		og.custom_properties_filter.init();
	}
	
	if (!config.keep_selector_changes) {
		var obj_selector = this.dialog;
		$('#object-selector .x-tool.x-tool-close').click(function() {
			obj_selector.revertSelectionChanges();
		});
	}
}

og.eventManager.addListener('object selector all type menu items rendered', function(data){
	var selector = Ext.getCmp('object-selector');
	if (!selector) return;
	
	selector.all_type_menu_items_rendered = true;
	if (selector.isReadyToLoad()) {
		selector.load();
	}
	
});

og.eventManager.addListener('object selector all cp filters rendered', function(data){
	var selector = Ext.getCmp('object-selector');
	if (!selector) return;
	
	selector.all_cp_filters_rendered = true;
	if (selector.isReadyToLoad()) {
		selector.load();
	}
	
});

