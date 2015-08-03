/**
 *  ReportingManager
 */
og.ReportingManager = function() {
	var actions;
	this.doNotRemove = true;
	this.needRefresh = false;
	
	if (!og.ReportingManager.store) {
		og.ReportingManager.store = new Ext.data.Store({
	        proxy: new Ext.data.HttpProxy(new Ext.data.Connection({
				method: 'GET',
	            url: og.getUrl('reporting', 'list_all', {ajax:true})
	        })),
	        reader: new Ext.data.JsonReader({
	            root: 'charts',
	            totalProperty: 'totalCount',
	            id: 'id',
	            fields: [
	                'name', 'type', 'tags', 'project', 'projectId'
	            ]
	        }),
	        remoteSort: true,
			listeners: {
				'load': function() {
					var d = this.reader.jsonData;
					og.processResponse(d);
					/*var ws = og.clean(Ext.getCmp('workspace-panel').getActiveWorkspace().name);
					var tag = og.clean(Ext.getCmp('tag-panel').getSelectedTag());*/
					if (d.totalCount == 0) {
						if (tag) {
							this.fireEvent('messageToShow', lang("no objects with tag message", lang("charts"), ws, tag));
						} else {
							this.fireEvent('messageToShow', lang("no objects message", lang("charts"), ws));
						}
					} else if (d.charts.length == 0) {
						this.fireEvent('messageToShow', lang("no more objects message", lang("charts")));
					} else {
						this.fireEvent('messageToShow', "");
					}
					og.hideLoading();
				},
				'beforeload': function() {
					og.loading();
					return true;
				},
				'loadexception': function() {
					og.hideLoading();
					var d = this.reader.jsonData;
					og.processResponse(d);
				}
			}
	    });
	    og.ReportingManager.store.setDefaultSort('name', 'asc');
	}
	this.store = og.ReportingManager.store;
	this.store.addListener({messageToShow: {fn: this.showMessage, scope: this}});
    
    //--------------------------------------------
    // Renderers
    //--------------------------------------------

	function renderName(value, p, r) {
		return String.format(
			'<a href="#" onclick="og.openLink(\'{2}\')">{0}</a>',
			og.clean(value), og.clean(r.data.name), og.getUrl('reporting', 'chart_details', {id: r.id}));
	}

	function renderProject(value, p, r) {
		var ids = String(og.clean(r.data.projectId)).split(',');
		var names = value.split(',');
		var result = "";
		for(var i = 0; i < ids.length; i++){
			result += String.format('<a href="#" onclick="Ext.getCmp(\'workspace-panel\').select({1})">{0}</a>', names[i], ids[i]);
			if (i < ids.length - 1)
				result += ",&nbsp";
		}
		return result;
	}
    
	function getSelectedIds() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			var ret = '';
			for (var i=0; i < selections.length; i++) {
				ret += "," + selections[i].id;
			}	
			return ret.substring(1);
		}
	}
	
	function getFirstSelectedId() {
		if (sm.hasSelection()) {
			return sm.getSelected().id;
		}
		return '';
	}

	var sm = new Ext.grid.CheckboxSelectionModel();
	sm.on('selectionchange',
		function() {
			if (sm.getCount() <= 0) {
				actions.tag.setDisabled(true);
				actions.delChart.setDisabled(true);
				actions.editChart.setDisabled(true);
			} else {
				actions.editChart.setDisabled(sm.getCount() != 1);
				actions.tag.setDisabled(false);
				actions.delChart.setDisabled(false);
			}
		});
    var cm = new Ext.grid.ColumnModel([
		sm,{
			id: 'name',
			header: lang("title"),
			dataIndex: 'name',
			width: 200,
			sortable: false,
			renderer: renderName
        },{
			id: 'project',
			header: lang("project"),
			dataIndex: 'project',
			width: 40,
			renderer: renderProject,
			sortable: false
        },{
			id: 'tags',
			header: lang("tags"),
			dataIndex: 'tags',
			width: 120,
			sortable: false
        }]);
    cm.defaultSortable = true;
	
	actions = {
		newChart: new Ext.Action({
			text: lang('new'),
            tooltip: lang('add new chart'),
            iconCls: 'ico-reporting',
            handler: function() {
				var url = og.getUrl('reporting', 'add_chart');
				og.openLink(url, null);
			}
		}),
		delChart: new Ext.Action({
			text: lang('delete'),
            tooltip: lang('delete selected charts'),
            iconCls: 'ico-delete',
			disabled: true,
			handler: function() {
				if (confirm(lang('confirm delete charts'))) {
					this.load({
						action: 'delete',
						charts: getSelectedIds()
					});
				}
			},
			scope: this
		}),
		editChart: new Ext.Action({
			text: lang('edit'),
            tooltip: lang('edit selected chart'),
            iconCls: 'ico-new',
			disabled: true,
			handler: function() {
				var url = og.getUrl('reporting', 'edit_chart', {id:getFirstSelectedId()});
				og.openLink(url, null);
			},
			scope: this
		}),
		refresh: new Ext.Action({
			text: lang('save'),
            tooltip: lang('refresh desc'),
            iconCls: 'ico-refresh',
			handler: function() {
				this.store.reload();
			},
			scope: this
		}),
		tag: new Ext.Action({
			text: lang('tag'),
	        tooltip: lang('tag selected charts'),
	        iconCls: 'ico-tag',
			disabled: true,
			menu: new og.TagMenu({
				listeners: {
					'tagselect': {
						fn: function(tag) {
							this.load({
								action: 'tag',
								charts: getSelectedIds(),
								tagTag: tag
							});
						},
						scope: this
					}
				}
			})
		})
    };
    
	og.ReportingManager.superclass.constructor.call(this, {
		//enableDrag: true,
		ddGroup : 'disabled',
        store: this.store,
		layout: 'fit',
        cm: cm,
        closable: true,
		stripeRows: true,
		loadMask: true,
		stateful: og.preferences['rememberGUIState'],
        style: "padding:7px",
        bbar: new og.CurrentPagingToolbar({
            pageSize: og.config['files_per_page'],
            store: this.store,
            displayInfo: true,
            displayMsg: lang('displaying charts of'),
            emptyMsg: lang("no charts to display")
        }),
		viewConfig: {
            forceFit: true
        },
		sm: sm,
		tbar:[
			actions.newChart,
			'-',
			actions.tag,
			actions.delChart,
			//actions.editChart,
			'-',
			actions.refresh
        ],
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

	var tagevid = og.eventManager.addListener("tag changed", function(tag) {
		if (!this.ownerCt) {
			og.eventManager.removeListener(tagevid);
			return;
		}
		if (this.ownerCt.active) {
			this.load({start:0});
		} else {
    		this.needRefresh = true;
    	}
	}, this);
};

Ext.extend(og.ReportingManager, Ext.grid.GridPanel, {
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
				limit: og.config['files_per_page'],
				/*tag: Ext.getCmp('tag-panel').getSelectedTag(),
				active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id*/
			})
		});
		this.needRefresh = false;
	},
	
	activate: function() {
		if (this.needRefresh) {
			this.load({start: 0});
		}
	},
	
	reset: function() {
		this.load({start:0});
	},
	
	showMessage: function(text) {
		this.innerMessage.innerHTML = text;
	}
});

Ext.reg("reporting", og.ReportingManager);