og.ObjectGrid = function(config, ignore_context) {
	
	if (!config) config = {};
	
	var url_params = {
		ajax: true,
		count_results : 0
	};
	
	var list_url = og.getUrl('object', 'list_objects');
	if (config.url) {
		list_url = config.url;
	}

	if (config.store_params) {
		url_params = config.store_params;
	}

	if (ignore_context) {
		url_params['ignore_context'] = ignore_context ? '1' : '0';
	}
	
	var grid_id = 'obj_list_grid';
	if (config.grid_id) {
		grid_id = config.grid_id;
	}
	
	var response_objects_root = 'objects';
	if (config.response_objects_root) {
		response_objects_root = config.response_objects_root;
	}
	
	if (config.dont_use_gooproxy) {
		var connection_proxy = new Ext.data.HttpProxy(new Ext.data.Connection({
			method: 'GET',
        	url: list_url
		}));
	} else {
		var connection_proxy = new og.GooProxy({
			url: list_url
		});
	}
	
	// filters
	var filters = config.filters;
	
	this.needRefresh = false;
	
	
	// FIELDS
	this.fields = [
		'id', 'object_id', 'type', 'ot_id', 'name', 'ix', 'isRead', 'memPath', 'type_controller'
	];
	
	if (config.columns) {
		for (i=0; i<config.columns.length; i++) {
			var col = config.columns[i];
			if (col) {
				this.fields.push(col.name);
			}
		}
	} else {
		config.columns = [];
	}
	// default actions column
	if (typeof(config.add_default_actions_column) != "undefined") {
		this.add_default_actions_column = config.add_default_actions_column;
	} else {
		this.add_default_actions_column = true;
	}
	if (config.columns.indexOf('actions') >= 0) {
		this.add_default_actions_column = false;
	}
	
	var cps = og.custom_properties_by_type[config.type_name] ? og.custom_properties_by_type[config.type_name] : [];
	var cp_names = [];
	for (i=0; i<cps.length; i++) {
		cp_names.push('cp_' + cps[i].id);
	}
	this.fields = this.fields.concat(cp_names);
	

	
	//Added by Conrado 2019 - Used by a plugin (we should probably change all plugins and make this more generic
//	if (config.dimension_columns) {
//		for (did in config.dimension_columns) {
//			if (isNaN(did)) continue;
//			dim_names.push('dim_' + did);
//	} else {
//		//This was the original implementation
//		//But it returns all the dimensions, always, and not the ones associated to the object type
//		for (did in og.dimensions_info) {
//			if (isNaN(did)) continue;
//			dim_names.push('dim_' + did);
//		}
//	}
	
	var dim_names = [];	
	for (did in og.dimensions_info) {
		if (isNaN(did)) continue;
		dim_names.push('dim_' + did);
	}
	this.fields = this.fields.concat(dim_names);
	// END FIELDS
	
	
	
	this.store = new Ext.data.Store({
    	proxy: connection_proxy,
    	reader: new Ext.data.JsonReader({
        	root: response_objects_root,
        	totalProperty: 'totalCount',
        	id: 'id',
        	fields: this.fields
    	}),
    	remoteSort: true,
    	listeners: {
    		'load': function(store, result) {
    			
    			var d = this.reader.jsonData;
    			
    			var grid = Ext.getCmp(grid_id);
				if (grid) {
					this.lastOptions.params = this.baseParams;
					this.lastOptions.params.count_results = 1;
					
					var h;
					if (!config.fixed_height) {
						var min_h = config.min_height | 245;
						var max_h = config.max_height | 500;
						h = (d[response_objects_root].length * 36) + 125;
						if (h > max_h) h = max_h;
						if (h < min_h) h = min_h;
						
					} else {
						if (config.max_height) {
							h = config.max_height;
						} else {
							h = grid.container.dom.offsetHeight;
						}
					}
					grid.setHeight(h);
					
					if (config.no_totals_row) d.totals = null;
					
					// add first row to add
					if (config.quick_add_row) {
						og.add_object_grid_quick_add_row(grid, config);
					}
					
					og.eventManager.fireEvent('after grid panel load', {man:grid, data:d});
					
					if (config.separate_totals_request) {
						grid.reloadTotalsRow({scroll_to_top: true});
					} else {
						if (d && d.totals) {
							grid.updateGridPagingToolbar({totalCount: d.totals.total_rows});
						} else if (this.baseParams.url_controller && this.baseParams.url_action) {
							grid.reloadGridPagingToolbar(this.baseParams.url_controller, this.baseParams.url_action, grid_id);
						}
					}
						
				}
				
				og.eventManager.fireEvent('replace all empty breadcrumb', null);
    		}
    	}
	});
	this.store.baseParams = jQuery.extend({}, url_params);
   	//this.store.setDefaultSort('name', 'asc');

	function renderIcon(value, p, r) {
		if (r.data.id == '__total_row__' || r.data.id <= 0) return '';
		
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

	this.getValidSelections = function() {
		var selections = this.getSelectionModel().getSelections();
		var valid_selections = [];
		for (var i=0; i<selections.length; i++) {
			// don't count quick add row and totals row as a valid selection to enable/disable the toolbar buttons
			if (selections[i] && selections[i].id != "#__total_row__" && selections[i].id != "quick_add_row") {
				valid_selections.push(selections[i]);
			}
		}

		return valid_selections;
	}
	
	this.getSelectedIds = function() {
		var ret = '';
		var selections = this.getValidSelections();
		for (var i=0; i < selections.length; i++) {
			ret += (ret == "" ? "" : ",") + selections[i].data.object_id;
		}
		return ret;
	}
	
	this.getFirstSelectedId = function() {
		var selections = this.getValidSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			return selections[0].data.object_id;
		}
		return '';
	}

	if (config.checkbox_sel_model) {
		if(config.sm){
			var sm = config.sm;
		}else{
			var sm = new Ext.grid.CheckboxSelectionModel();
			sm.on('selectionchange', function() {
				var selections = this.grid.getValidSelections();
				var sel_count = selections.length;
				
				for (x in this.grid.tbar_items) {
					var action = this.grid.tbar_items[x];
					if (typeof(action) == 'object') {
						// for actions that requires selected rows set if they are enabled or disabled
						if (action.initialConfig.selection_dependant) {
							if (sel_count == 0) {
								// disable if there are no selections
								action.setDisabled(true);
							} else if (sel_count == 1) {
								// enable all if there is only one row selected
								action.setDisabled(false);
							} else {
								// if there are more than one rows selected, enable only the ones defined as multiple
								action.setDisabled(!action.initialConfig.is_multiple);
							}
						}
					}
				}
				
			});
        }
		
		var cm_info = [sm];
		
	} else {
		var sm = new Ext.grid.RowSelectionModel();
		var cm_info = [];
	}
	
	
	if (!config.no_icon_col) {
		cm_info.push({
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
	    });
	}
	
	if (config.columns) {
		for (i=0; i<config.columns.length; i++) {
			var col = config.columns[i];
			if (col && !col.is_system && !col.is_right_column && col.before_name) {
				cm_info.push({
					id: col.id ? col.id : col.name,
			    	header: col.header ? col.header : lang(col.name),
			    	dataIndex: col.dataIndex ? col.dataIndex : col.name,
			    	width: col.width ? col.width : 100,
			    	renderer: col.renderer ? col.renderer : og.default_grid_column_renderer,
			    	sortable: col.sortable ? col.sortable : false,
			    	fixed: col.fixed ? col.fixed : false,
			    	align: col.align ? col.align : 'left',
			    	resizable: col.resizable ? col.resizable : true,
					hidden: col.hidden ? col.hidden : false,
			    	menuDisabled: col.menuDisabled ? col.menuDisabled : false
				});
			}
		}
	}
	
	cm_info.push({
		id: 'name',
		//@ToDo The main ID column might not always be called "Name". This should be an attribute of the column.
		header: config.name_column_text ? config.name_column_text : lang("name"),
		dataIndex: 'name',
		hidden: config.hide_name_column,
		fixed: config.name_fixed | false,
    	width: typeof(config.name_width) != "undefined" ? config.name_width : 200,
		renderer: config.nameRenderer ? config.nameRenderer : og.default_grid_column_renderer
    });
	
	if (config.columns) {
		for (i=0; i<config.columns.length; i++) {
			var col = config.columns[i];
			if (col && !col.is_system && !col.is_right_column && !col.before_name) {
				cm_info.push({
					id: col.id ? col.id : col.name,
			    	header: col.header ? col.header : lang(col.name),
			    	dataIndex: col.dataIndex ? col.dataIndex : col.name,
			    	width: col.width ? col.width : 100,
			    	renderer: col.renderer ? col.renderer : og.default_grid_column_renderer,
			    	sortable: col.sortable ? col.sortable : false,
			    	fixed: col.fixed ? col.fixed : false,
			    	align: col.align ? col.align : 'left',
			    	resizable: col.resizable ? col.resizable : true,
					hidden: col.hidden ? col.hidden : false,
			    	menuDisabled: col.menuDisabled ? col.menuDisabled : false
				});
			}
		}
	}
	

	// This seems to not be working
	// How does this work?
	
	// This variable stores all the custom properties defined for the object.
	var cps = og.custom_properties_by_type[config.type_name] ? og.custom_properties_by_type[config.type_name] : [];
	
	//This goes to public/assets/javascript/og/overrides/extfix.js
	//This is where the columns that will be displayed (or not) for custom properties are defined
	//It is currently using the state saved by ExtJS.
	//Ideally, this should be persisted in the DB.
	this.addCustomPropertyColumns(cps, cm_info, grid_id);
	
	//unless the UX specifically excludes the columns for dimensions, this is where the dimensions are loaded... 
	if (!config.skip_dimension_columns) {
		// dimension columns
		for (did in og.dimensions_info) {
			if (isNaN(did)) continue;
			if (config.allowed_dimension_columns && config.allowed_dimension_columns.length > 0) {
				if (config.allowed_dimension_columns.indexOf(did) == -1) continue;
			}
			var key = 'lp_dim_' + did + '_show_as_column';
			if (og.preferences['listing_preferences'][key]) {
				cm_info.push({
					id: 'dim_' + did,
					header: og.dimensions_info[did].name,
					dataIndex: 'dim_' + did,
					sortable: true,
					renderer: og.renderDimCol
				});
				og.breadcrumbs_skipped_dimensions[did] = did;
			}
		}
	}
	

	
	if (this.add_default_actions_column) {
		config.columns.push({
	    	name: 'actions',
	    	is_right_column: true,
	    	fixed: true,
	    	width: 100, 
	    	renderer: og.render_default_grid_actions
	    });
	}
	
	// columns to the right
	if (config.columns) {
		for (i=0; i<config.columns.length; i++) {
			var col = config.columns[i];
			if (col && !col.is_system && col.is_right_column) {
				cm_info.push({
					id: col.id ? col.id : col.name,
			    	header: col.header ? col.header : lang(col.name),
			    	dataIndex: col.dataIndex ? col.dataIndex : col.name,
			    	width: col.width ? col.width : 100,
			    	renderer: col.renderer ? col.renderer : og.default_grid_column_renderer,
			    	sortable: col.sortable ? col.sortable : false,
			    	fixed: col.fixed ? col.fixed : false,
			    	align: col.align ? col.align : 'left',
			    	resizable: col.resizable ? col.resizable : true,
					hidden: col.hidden ? col.hidden : false,
			    	menuDisabled: col.menuDisabled ? col.menuDisabled : false
				});
			}
		}
	}
	

	var cm = new Ext.grid.ColumnModel(cm_info);
    cm.defaultSortable = true;
    cm.on('hiddenchange', this.afterColumnShowHide, this);
    
    
    // toolbar items
    var tbar = [];
    if (config.tbar_items) {
    	for (i=0; i<config.tbar_items.length; i++) {
    		if (!config.tbar_items[i].initialConfig || !config.tbar_items[i].initialConfig.secondToolbar) {
    			tbar.push(config.tbar_items[i]);
    		}
    	}
    }
    
    // toolbar filter items
    if (config.filters) {
    	for (var filter_name in config.filters) {
    		var filter_data = config.filters[filter_name];
    		if (!filter_data.secondToolbar) {
    			
	    		var items = og.buildToolbarFilterAction(filter_name, filter_data, grid_id);
	    		
	    		if (items.length > 0 && tbar.length == 0) {
	    			tbar.push('&nbsp;'+lang('filters')+":");
	    		}
	    		for (var x=0; x<items.length; x++) {
	    			tbar.push(items[x]);
	    		}
    		}
    	}
    }
    
    // toolbar right items
    if (config.tbar_right_items) {
    	tbar.push('->');
    	for (i=0; i<config.tbar_right_items.length; i++) {
    		if (!config.tbar_right_items[i].initialConfig || !config.tbar_right_items[i].initialConfig.secondToolbar) {
    			tbar.push(config.tbar_right_items[i]);
    		}
    	}
    }
    
    // second toolbar items
    var tbar2 = [];
    if (config.tbar_items) {
    	for (i=0; i<config.tbar_items.length; i++) {
    		if (config.tbar_items[i].initialConfig && config.tbar_items[i].initialConfig.secondToolbar) {
    			tbar2.push(config.tbar_items[i]);
    		}
    	}
    }
    
    // second toolbar filter items
    if (config.filters) {
    	for (var filter_name in config.filters) {
    		var filter_data = config.filters[filter_name];
    		if (filter_data.secondToolbar) {
    			
	    		var items = og.buildToolbarFilterAction(filter_name, filter_data, grid_id);
	    		
	    		if (items.length > 0 && tbar2.length == 0) {
	    			tbar2.push('&nbsp;'+lang('filters')+":");
	    		}
	    		for (var x=0; x<items.length; x++) {
	    			tbar2.push(items[x]);
	    		}
    		}
    	}
    }
    

	
	if (config.allow_drag_drop) {
		config.enableDrag = true;
		config.ddGroup = 'MemberDD';
	}
    

    og.ObjectGrid.superclass.constructor.call(this, Ext.apply(config, {
        store: this.store,
		layout: 'fit',
		id: grid_id,
        cm: cm,
        stripeRows: true,
        stateful: og.preferences['rememberGUIState'],
        loadMask: true,
        bbar: new og.CurrentPagingToolbar({
            pageSize: og.config['files_per_page'],
            store: this.store,
            displayInfo: true,
            displayMsg: lang('displaying objects of'),
            emptyMsg: lang("no objects to display")
        }),
		viewConfig: {
            forceFit: typeof(config.forceFit) != "undefined" ? config.forceFit : true
        },
		sm: sm,
		tbar: tbar,
		cls: "object-grid",
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

					if (config.hide_name_column) {
						var col_model = this.getColumnModel();
						col_model.setHidden(col_model.getIndexById('name'), true);
						//col_model.moveColumn(col_model.getIndexById('product_type_name'),1);
					}
					
					// add the second toolbar
					if (tbar2.length > 0) {
						this.topTbar2 = new Ext.Toolbar({
						    renderTo: this.tbar,
						    items: tbar2
						});
					}
				},
				scope: this
			},
			'resize': {
				fn: function() {
					if (!this.hidden) {
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

								/*
								* Danilo Zurita 15/12/2023
								* These two lines were commented for reasons of identifying if they are used f
								* or something specific, the link of the task was left for quick tracking
								* Link to Task: https://c12.fengoffice.com/_feng/index.php?c=task&a=view&id=4712562
								*/

								// var h = $("#"+v.grid.id).parent().height();
								// v.grid.setSize({height:h});
							}, 200);
						}
					}
				},
				scope: this
			},
			'columnmove': {
				fn: function(old_index, new_index) {
					og.eventManager.fireEvent('replace all empty breadcrumb', null);

					// reset quick add row
					if (config.quick_add_row) {
						// remove previous quick add row
						this.store.remove(this.store.getAt(0));
						// don't focus in first input to prevent scrolling left and let user continue moving columns
						this.initialConfig.dont_focus_in_first_input = true;
						// add new quick add row
						og.add_object_grid_quick_add_row(this, this.initialConfig);
					}
				},
				scope: this
			}
		}
    }));
	
	
}

Ext.extend(og.ObjectGrid, Ext.grid.GridPanel, {
	load: function(params) {
		
		if (!params) params = {};
		if (typeof params.start == 'undefined') {
			var start = (this.getBottomToolbar().getPageData().activePage - 1) * og.config['files_per_page'];
		} else {
			var start = 0;
		}
		
		if (this.filters) {
			for (var key in this.filters) {
				var f_val = this.filters[key].value;
				if (f_val) {
					this.store.baseParams[key] = f_val;
				} else if (this.store.baseParams[key]) {
					delete this.store.baseParams[key];
				}
			}
		}
		
		// don't make the request if panel is hidden
		if (!this.hidden) {
			
			this.updateColumnModelHiddenColumns();
			
			if (!params.req_channel && params.action) {
				params.req_channel = this.store_params.url_controller + ' list - toolbar ' + params.action;
			}
			
			this.store.removeAll();
			this.store.load({
				params: Ext.apply(params, {
					start: start,
					limit: og.config['files_per_page']
				}),
				callback: typeof(params.load_callback)=='function' ? params.load_callback : null
			});
		}
	},
	
	reloadTotalsRow: function(params) {
		if (!params) params = {};
		
		params.load_totals_row = 1; 
		
		Ext.apply(params, this.store_params);

		if (!this.filters) this.filters = {};

		// add the last selected filter values to the totals request parameters
		for (var filter_name in this.filters) {
			if (typeof filter_name == 'function') continue;
			params[filter_name] = this.filters[filter_name].value;
		}

		var objects_grid_id = this.id;
		// call the controller to retrieve the totals
		og.openLink(og.getUrl(this.store_params.url_controller, this.store_params.url_action, params), {
			hideLoading: true,
			callback: function(success, data) {
				var g = Ext.getCmp(objects_grid_id);
				
				if (g && data && data.totals) {
					g.updateGridPagingToolbar({totalCount: data.totals.total_rows});
				}
				
				if (g && typeof(g.addTotalsRow) == 'function' && data && data.totals) {
					
					var totals_row_record = g.store.getById("#__total_row__");
					if (totals_row_record) {
						// remove previous total row
						g.store.remove(totals_row_record);
					}
					// add the totals row
					g.addTotalsRow(data, !params.scroll_to_top);
				}
			}
		})
	},
	
	activate: function() {
		if (this.needRefresh) {
			this.load({start:0});
		}
	},
	
	reset: function() {
		this.load({start:0});
	}
});
