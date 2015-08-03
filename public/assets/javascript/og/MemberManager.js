/**
 *  MemberManager
 *
 */
og.members = {
	onMemberClick: function (member_id) {
		og.contextManager.currentDimension = og.customers.dimension_id ;
		var dimensions_panel = Ext.getCmp('menu-panel');
		dimensions_panel.items.each(function(item, index, length) {
			if (item.dimensionId == og.customers.dimension_id) {
				og.expandCollapseDimensionTree(item);
				var n = item.getNodeById(member_id);
				if (n) {
					if (n.parentNode) item.expandPath(n.parentNode.getPath(), false);
					n.select();
					og.eventManager.fireEvent('member tree node click', n);
				}
				
			}

		});
		
	}
}

og.MemberManager = function() {
	var actions;
	this.doNotRemove = true;
	this.needRefresh = false;
	
	if (!og.MemberManager.store) {
		og.MemberManager.store = new Ext.data.Store({
			proxy: new og.GooProxy({
				url: og.getUrl('member', 'list_all')
			}),
			reader: new Ext.data.JsonReader({
				root: 'members',
				totalProperty: 'totalCount',
				id: 'id',
				dimension_id: 'dimension_id',
				fields: [
					'object_id', 'name', 'depth', 'parent_member_id', 'dimension_id', 'id', 'ico_color'
				]
			}),
			remoteSort: true,
			listeners: {
				'load': function() {
					var d = this.reader.jsonData;
					og.members.dimension_id = d.dimension_id;
					this.fireEvent('after list load', "");
					if (d.totalCount == 0) {
						var sel_context_names = og.contextManager.getActiveContextNames();
						if (sel_context_names.length > 0) {
							this.fireEvent('messageToShow', lang("no objects message", lang("members"), sel_context_names.join(', ')));
						} else {
							this.fireEvent('messageToShow', lang("no more objects message", lang("members")));
						}
					} else {
						this.fireEvent('messageToShow', "");
					}
				}
			}
		});
		og.MemberManager.store.setDefaultSort('name', 'asc');
	}
	this.store = og.MemberManager.store;
	this.store.addListener({messageToShow: {fn: this.showMessage, scope: this}});

	function renderDragHandle(value, p, r, ix) {
		return '<div class="img-grid-drag" title="' + lang('click to drag') + '" onmousedown="var sm = Ext.getCmp(\'member-manager\').getSelectionModel();if (!sm.isSelected('+ix+')) sm.clearSelections();sm.selectRow('+ix+', true);"></div>';
	}
	
	function renderName(value, p, r) {
		var text = '<span>'+ og.clean(value) +'</span>';
		
		var onclick = 'og.workspaces.onWorkspaceClick('+r.data.id+'); return false;';
		
		return String.format(
				'<a style="font-size:120%;" class="{2}" href="#" onclick="{3}" title="{1}">{0}</a>',
				text, og.clean(value), '', onclick);
		
	}

	function renderIcon(value, p, r) {
		return '<div class="db-ico ico-color'+value+'"></div>';
	}

	function getSelectedIds() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			var ret = '';
			for (var i=0; i < selections.length; i++) {
				ret += "," + selections[i].data.id;
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
			return selections[0].data.id;
		}
		return '';
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
	
	var cm = new Ext.grid.ColumnModel([
		sm,{
			id: 'icon',
			header: '&nbsp;',
			dataIndex: 'ico_color',
			width: 28,
                        renderer: renderIcon,
                        fixed:true,
                        resizable: false,
                        hideable:false,
                        menuDisabled: true
                        }
                ,{
                        id: 'name',
                        header: lang("name"),
                        dataIndex: 'name',
                        width: 250,
                        renderer: renderName,
                        sortable:true
                }]);
        cm.defaultSortable = false;

		
	actions = {
		newCO: new Ext.Action({
			text: lang('new'),
                        tooltip: lang('add new workspace'),
                        iconCls: 'ico-new',
                        handler: function() {
                                            var url = og.getUrl('member', 'add', {dim_id:og.members.dimension_id});
                                            og.openLink(url, null);
                                    }
                            }),
                        edit: new Ext.Action({
			text: lang('edit'),
                        tooltip: lang('edit selected workspace'),
                        iconCls: 'ico-edit',
			disabled: true,
			handler: function() {
				var url = og.getUrl('member', 'edit', {id:getFirstSelectedId()});
				og.openLink(url, null);
			},
			scope: this
		}),
		del: new Ext.Action({
			text: lang('delete'),
                        tooltip: lang('delete selected workspace_'),
                        iconCls: 'ico-delete',
			disabled: true,
			handler: function() {
				if (confirm(lang('delete workspace warning'))) {
					var url = og.getUrl('member', 'delete', {id:getFirstSelectedId()});
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
		//tbar.push(actions.del);
	}
	
	og.MemberManager.superclass.constructor.call(this, {
		store: this.store,
		layout: 'fit',
		cm: cm,
		stateful: og.preferences['rememberGUIState'],
		id: 'member-manager',
		stripeRows: true,
		closable: true,
		loadMask: true,
		bbar: new og.CurrentPagingToolbar({
			pageSize: og.config['files_per_page'],
			store: this.store,
			displayInfo: true,
			displayMsg: lang('displaying objects of'),
			emptyMsg: lang("no objects to display")

		}),
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
			  account_id: this.accountId
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
		this.viewUnclassified = false;
		this.accountId = 0;
	},
	
	activate: function() {
		if (this.needRefresh) {
			this.load({start:0});
		}
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

/************************************************
Container for MemberManager,
*************************************************/
og.MemberManagerPanel = function() {
	this.doNotRemove = true;
	this.needRefresh = false;
	
	this.manager = new og.MemberManager();
	
	this.helpPanel = new og.HtmlPanel({
		html:'<div style="height:50px; line-height:50px; background-color:green;">HOLA</div>',
		style:'height: 50px;'
	});

	og.MemberManagerPanel.superclass.constructor.call(this, {
		layout: 'fit',
		border: false,
		bodyBorder: false,
		items: [
			this.helpPanel,
			this.manager
		],
		closable: true
	});
}

Ext.extend(og.MemberManagerPanel, Ext.Panel, {
	load: function(params) {
		this.manager.load(params);
	},
	activate: function() {
		this.manager.activate();
	},	
	reset: function() {
		this.manager.reset();
	},	
	showMessage: function(text) {
		this.manager.showMessage(text);
	}
});

Ext.reg("members-containerpanel", og.MemberManagerPanel);


og.CustomerManagerView = function() {
	og.CustomerManagerView.superclass.constructor.call(this, {});
}


Ext.grid.GridView.override({
	getRowClass: function (  record,  index,  rowParams,  store ) {
		return "";
	},
	focusCell : function(row, col, hscroll){
		this.syncFocusEl(this.ensureVisible(row, col, hscroll));
		this.focusEl.focus.defer(1, this.focusEl);
	},

        syncFocusEl : function(row, col, hscroll){
            var xy = row;
            if(!Ext.isArray(xy)){
                row = Math.min(row, Math.max(0, this.getRows().length-1));
            }
            this.focusEl.setXY(xy||this.scroller.getXY());
        }
});
