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
	
	
	// FIELDS
	this.fields = [
		'id', 'object_id', 'type', 'ot_id', 'name', 'ix', 'isRead', 'memPath'
	];
	
	if (config.columns) {
		for (i=0; i<config.columns.length; i++) {
			var col = config.columns[i];
			if (col) {
				this.fields.push(col.name);
			}
		}
	}
	
	var cps = og.custom_properties_by_type[config.type_name] ? og.custom_properties_by_type[config.type_name] : [];
	var cp_names = [];
	for (i=0; i<cps.length; i++) {
		cp_names.push('cp_' + cps[i].id);
	}
	this.fields = this.fields.concat(cp_names);
	
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
					
					grid.reloadGridPagingToolbar(this.baseParams.url_controller, this.baseParams.url_action, grid_id);
					
					if (!config.fixed_height) {
						var min_h = config.min_height | 245;
						var max_h = config.max_height | 500;
						var h = d[response_objects_root].length * 35 + 80;
						if (h > max_h) h = max_h;
						if (h < min_h) h = min_h;
						
						grid.setHeight(h);
						
					} else {
						var h;
						if (config.max_height) {
							h = config.max_height;
						} else {
							h = grid.container.dom.offsetHeight;
						}
						grid.setHeight(h);
					}
					
					og.eventManager.fireEvent('after grid panel load', {man:grid, data:d});
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

	var sm = new Ext.grid.RowSelectionModel();
	var cm_info = [{
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
    	width: 100,
		renderer: config.nameRenderer ? config.nameRenderer : og.clean
    }];
	
	if (config.columns) {
		for (i=0; i<config.columns.length; i++) {
			var col = config.columns[i];
			if (col && !col.is_system) {
				cm_info.push({
					id: col.id ? col.id : col.name,
			    	header: col.header ? col.header : lang(col.name),
			    	dataIndex: col.dataIndex ? col.dataIndex : col.name,
			    	width: col.width ? col.width : 100,
			    	renderer: col.renderer ? col.renderer : og.clean,
			    	sortable: col.sortable ? col.sortable : false,
			    	fixed: col.fixed ? col.fixed : false,
			    	align: col.align ? col.align : 'left',
			    	resizable: col.resizable ? col.resizable : true,
			    	menuDisabled: col.menuDisabled ? col.menuDisabled : false
				});
			}
		}
	}
	// custom property columns
	var cps = og.custom_properties_by_type[config.type_name] ? og.custom_properties_by_type[config.type_name] : [];
	for (i=0; i<cps.length; i++) {
		cm_info.push({
			id: 'cp_' + cps[i].id,
			hidden: parseInt(cps[i].visible_def) == 0,
			header: cps[i].name,
			align: cps[i].cp_type=='numeric' ? 'right' : 'left',
			dataIndex: 'cp_' + cps[i].id,
			sortable: true,
			renderer: og.clean
		});
	}
	// dimension columns
	for (did in og.dimensions_info) {
		if (isNaN(did)) continue;
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

	var cm = new Ext.grid.ColumnModel(cm_info);
    cm.defaultSortable = true;
    
    
    var tbar = [];
    if (config.tbar_items) {
    	for (i=0; i<config.tbar_items.length; i++) {
    		tbar.push(config.tbar_items[i]);
    	}
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
            forceFit:true
        },
		sm: sm,
		tbar: tbar,
		viewConfig: {
			forceFit: true
		},
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

Ext.extend(og.ObjectGrid, Ext.grid.GridPanel, {
	load: function(params) {
		
		if (!params) params = {};
		if (typeof params.start == 'undefined') {
			var start = (this.getBottomToolbar().getPageData().activePage - 1) * og.config['files_per_page'];
		} else {
			var start = 0;
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
});
