/**
 *  MessageManager
 *
 */
og.MessageManager = function() {
	var actions, moreActions, markactions;
	this.doNotRemove = true;
	this.needRefresh = false;
	
	this.fields = [
		'object_id', 'type', 'ot_id', 'name', 'text', 'date', 'is_today',
		'userId', 'userName', 'updaterId', 'updaterName', 'ix', 'isRead', 'memPath'
   	];
   	var cps = og.custom_properties_by_type['message'] ? og.custom_properties_by_type['message'] : [];
   	var cp_names = [];
   	for (i=0; i<cps.length; i++) {
   		cp_names.push('cp_' + cps[i].id);
   	}
   	this.fields = this.fields.concat(cp_names);
	
	if (!og.MessageManager.store) {
		og.MessageManager.store = new Ext.data.Store({
			proxy: new og.GooProxy({
				url: og.getUrl('message', 'list_all')
			}),
			reader: new Ext.data.JsonReader({
				root: 'messages',
				totalProperty: 'totalCount',
				id: 'id',
				fields: this.fields
			}),
			remoteSort: true,
			listeners: {
				'load': function(store, result) {
					var d = this.reader.jsonData;
					if (d.totalCount == 0) {
						var sel_context_names = og.contextManager.getActiveContextNames();
						if (sel_context_names.length > 0) {
							this.fireEvent('messageToShow', lang("no objects message", lang("messages"), sel_context_names.join(', ')));
						} else {
							this.fireEvent('messageToShow', lang("no more objects message", lang("messages")));
						}
					} else {
						this.fireEvent('messageToShow', "");
					}
					var cmp = Ext.getCmp('message-manager');
					if (cmp) {
						cmp.getView().focusRow(og.lastSelectedRow.messages+1);
						var sm = cmp.getSelectionModel();
						sm.clearSelections();
					}
					Ext.getCmp('message-manager').reloadGridPagingToolbar('message','list_all','message-manager');
					
					og.eventManager.fireEvent('replace all empty breadcrumb', null);
				}
			}
		});
		og.MessageManager.store.setDefaultSort('date', 'desc');
	}
	this.store = og.MessageManager.store;
	this.store.addListener({messageToShow: {fn: this.showMessage, scope: this}});

	function renderDragHandle(value, p, r, ix) {
		return '<div class="img-grid-drag" title="' + lang('click to drag') + '" onmousedown="var sm = Ext.getCmp(\'message-manager\').getSelectionModel();if (!sm.isSelected('+ix+')) sm.clearSelections();sm.selectRow('+ix+', true);"></div>';
	}
	
	var readClass = 'read-unread-' + Ext.id();
	function renderName(value, p, r) {
		var name = '';
		
		var classes = readClass + r.id;
		if (!r.data.isRead) classes += " bold";
		
		mem_path = "";
		var mpath = Ext.util.JSON.decode(r.data.memPath);
		if (mpath){ 
			mem_path = "<div class='breadcrumb-container' style='display: inline-block;min-width: 250px;'>";
			mem_path += og.getEmptyCrumbHtml(mpath, '.breadcrumb-container', og.breadcrumbs_skipped_dimensions);
			mem_path += "</div>";
		}
		
		var text = '';
		if (r.data.text != ''){
			text = '<span style="color:#888888;white-space:nowrap">&nbsp;-&nbsp;';
			text += og.clean(r.data.text) + "</span></i>";
		}
		
		name = String.format(
				'<a style="font-size:120%;" class="{3}" href="{1}" onclick="og.openLink(\'{1}\');return false;" title="{2}">{0}</a>',
				og.clean(value), og.getUrl('message', 'view', {id: r.data.object_id}), og.clean(r.data.text), classes) + text + mem_path;
	
		
		
		return name;
	}

	function renderIsRead(value, p, r){
		var idr = Ext.id();
		var idu = Ext.id();
		var jsr = 'og.MessageManager.store.getById(\'' + r.id + '\').data.isRead = true; Ext.select(\'.' + readClass + r.id + '\').removeClass(\'bold\'); Ext.get(\'' + idu + '\').setDisplayed(true); Ext.get(\'' + idr + '\').setDisplayed(false); og.openLink(og.getUrl(\'object\', \'mark_as_read\', {ids:' + r.data.object_id + '}));'; 
		var jsu = 'og.MessageManager.store.getById(\'' + r.id + '\').data.isRead = false; Ext.select(\'.' + readClass + r.id + '\').addClass(\'bold\'); Ext.get(\'' + idr + '\').setDisplayed(true); Ext.get(\'' + idu + '\').setDisplayed(false); og.openLink(og.getUrl(\'object\', \'mark_as_unread\', {ids:' + r.data.object_id + '}));';
		return String.format(
			'<div id="{0}" title="{1}" class="db-ico ico-read" style="display:{2}" onclick="{3}"></div>' + 
			'<div id="{4}" title="{5}" class="db-ico ico-unread" style="display:{6}" onclick="{7}"></div>',
			idu, lang('mark as unread'), value ? 'block' : 'none', jsu, idr, lang('mark as read'), value ? 'none' : 'block', jsr
		);
	}	
	function renderFrom(value, p, r){
		var classes = readClass + r.id;
		if (!r.data.isRead) classes += " bold";
		name = String.format(
				'<a style="font-size:120%;" classes="{3}" href="{1}" onclick="og.openLink(\'{1}\');return false;" title="{2}">{0}</a>',
				og.clean(value), og.getUrl('message', 'view', {id: r.data.object_id}), og.clean(r.data.text), classes);
		return name;
	}
	
	
	function renderIcon(value, p, r) {
		return '<div class="db-ico ico-message"></div>';
	}

	function renderDate(value, p, r) {
		if (!value) {
			return "";
		}
		var userString = String.format('<a href="{1}" onclick="og.openLink(\'{1}\');return false;">{0}</a>', og.clean(r.data.updaterName), og.getUrl('contact', 'card', {id: r.data.updaterId}));
	
		if (!r.data.is_today) {
			return lang('last updated by on', userString, value);
		} else {
			return lang('last updated by at', userString, value);
		}
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
			og.lastSelectedRow.messages = selections[selections.length-1].data.ix;
			return ret.substring(1);
		}
	}
	this.getSelectedIds = getSelectedIds;
	
	function getFirstSelectedId() {
		if (sm.hasSelection()) {
			return sm.getSelected().data.object_id;
		}
		return '';
	}

	var sm = new Ext.grid.CheckboxSelectionModel();
	sm.on('selectionchange',
		function() {
			var allUnread = true, allRead = true;
			var selections = sm.getSelections()
			for (var i=0; i < selections.length; i++) {
				if (selections[i].data.isRead){
					allUnread = false;
				} else {
					allRead = false;
				}
			}
			if (sm.getCount() <= 0) {
				actions.del.setDisabled(true);
				actions.edit.setDisabled(true);
				markactions.markAsRead.setDisabled(true);
				markactions.markAsUnread.setDisabled(true);
				actions.archive.setDisabled(true);
			} else {
				actions.del.setDisabled(false);
				actions.edit.setDisabled(false);
				if (allUnread) {
					markactions.markAsUnread.setDisabled(true);
				} else {
					markactions.markAsUnread.setDisabled(false);
				}
				if (allRead) {
					markactions.markAsRead.setDisabled(true);
				} else {
					markactions.markAsRead.setDisabled(false);
				}
				actions.archive.setDisabled(false);
			}
		});
	
	var cm_info = [
		sm,{
	/*		id: 'draghandle',
			header: '&nbsp;',
			width: 18,
        	renderer: renderDragHandle,
        	fixed:true,
        	resizable: false,
        	hideable:false,
        	menuDisabled: true
		},{*/
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
			id: 'isRead',
			header: '&nbsp;',
			dataIndex: 'isRead',
			width: 16,
        	renderer: renderIsRead,
        	fixed:true,
        	resizable: false,
        	hideable:false,
        	menuDisabled: true
		},{
			id: 'title',
			header: lang("message"),
			dataIndex: 'name',
			width: 250,
			renderer: renderName,
			sortable:true
        },{
			id: 'updatedOn',
			header: lang("last updated by"),
			dataIndex: 'date',
			width: 50,
			sortable: true,
			renderer: renderDate
        }
    ];
	
	// custom property columns
	var cps = og.custom_properties_by_type['message'] ? og.custom_properties_by_type['message'] : [];
	for (i=0; i<cps.length; i++) {
		cm_info.push({
			id: 'cp_' + cps[i].id,
			header: cps[i].name,
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
				sortable: false,
				renderer: og.renderDimCol
			});
			og.breadcrumbs_skipped_dimensions[did] = did;
		}
	}
	// create column model
	var cm = new Ext.grid.ColumnModel(cm_info);
	cm.defaultSortable = false;

	moreActions = {};

	markactions = {
		markAsRead: new Ext.Action({
			text: lang('mark as read'),
		    tooltip: lang('mark as read desc'),
		    iconCls: 'ico-mark-as-read',
			disabled: true,
			handler: function() {
				this.load({
					action: 'markasread',
					ids: getSelectedIds()
				});
				this.getSelectionModel().clearSelections();
			},
			scope: this
		}),
		
		markAsUnread: new Ext.Action({
			text: lang('mark as unread'),
            tooltip: lang('mark as unread desc'),
            iconCls: 'ico-mark-as-unread',
			disabled: true,
			handler: function() {
				this.load({
					action: 'markasunread',
					ids: getSelectedIds()
				});
				this.getSelectionModel().clearSelections();
			},
			scope: this
		})
	};
	
	actions = {
		newCO: new Ext.Action({
			text: lang('new'),
            tooltip: lang('add new message'),
            iconCls: 'ico-new new_button',
            handler: function() {
				og.render_modal_form('', {c:'message', a:'add'});
			}
		}),
		del: new Ext.Action({
			text: lang('move to trash'),
            tooltip: lang('move selected objects to trash'),
            iconCls: 'ico-trash',
			disabled: true,
			handler: function() {
				if (confirm(lang('confirm move to trash'))) {
					this.load({
						action: 'delete',
						ids: getSelectedIds()
					});
					this.getSelectionModel().clearSelections();
				}
			},
			scope: this
		}),
		edit: new Ext.Action({
			text: lang('edit'),
            tooltip: lang('edit selected object'),
            iconCls: 'ico-edit',
			disabled: true,
			handler: function() {
				/*var url = og.getUrl('message', 'edit', {id:getFirstSelectedId()});
				og.openLink(url, null);*/
				og.render_modal_form('', {c:'message', a:'edit', params: {id:getFirstSelectedId()}});
			},
			scope: this
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
				if (confirm(lang('confirm archive selected objects'))) {
					this.load({
						action: 'archive',
						ids: getSelectedIds()
					});
					this.getSelectionModel().clearSelections();
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
		tbar.push(actions.archive);
		tbar.push(actions.del);		
		tbar.push('-');
	}
	tbar.push(actions.markAs);
	
	if (og.additional_list_actions && og.additional_list_actions.message) {
		tbar.push('-');
		for (var i=0; i<og.additional_list_actions.message.length; i++) {
			tbar.push(og.additional_list_actions.message[i]);
		}
	}
	
	
	og.MessageManager.superclass.constructor.call(this, {
		store: this.store,
		layout: 'fit',
		cm: cm,
		enableDrag: true,
		ddGroup: 'MemberDD',
		stateful: og.preferences['rememberGUIState'],
		id: 'message-manager',
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

Ext.extend(og.MessageManager, Ext.grid.GridPanel, {
	load: function(params) {
		if (!params) params = {};
		var start;
		if (typeof params.start == 'undefined') {
			start = (this.getBottomToolbar().getPageData().activePage - 1) * og.config['files_per_page'];
		} else {
			start = 0;
		}
		
		this.store.baseParams = {
			context: og.contextManager.plainContext()
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


Ext.reg("messages", og.MessageManager);

/************************************************
Container for MessageManager,
*************************************************/
og.MessageManagerPanel = function() {
	this.doNotRemove = true;
	this.needRefresh = false;
	
	this.manager = new og.MessageManager();
	
	this.helpPanel = new og.HtmlPanel({
		html:'<div style="height:50px; line-height:50px; background-color:green;">HOLA</div>',
		style:'height: 50px;'
	});

	og.MessageManagerPanel.superclass.constructor.call(this, {
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

Ext.extend(og.MessageManagerPanel, Ext.Panel, {
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

Ext.reg("messages-containerpanel", og.MessageManagerPanel);