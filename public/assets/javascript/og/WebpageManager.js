/**
 *  WebpageManager
 */
og.WebpageManager = function() {
	var actions, markactions;
	this.doNotRemove = true;
	this.needRefresh = false;
	
	this.fields = [
        'object_id','name', 'description',  'ot_id', 'url', 'updatedBy', 'updatedById',
        'updatedOn', 'updatedOn_today', 'ix', 'isRead', 'memPath'
    ];
	var cps = og.custom_properties_by_type['weblink'] ? og.custom_properties_by_type['weblink'] : [];
   	var cp_names = [];
   	for (i=0; i<cps.length; i++) {
   		cp_names.push('cp_' + cps[i].id);
   	}
   	this.fields = this.fields.concat(cp_names);
	
	if (!og.WebpageManager.store) {
		og.WebpageManager.store = new Ext.data.Store({
	        proxy: new og.GooProxy({
	            url: og.getUrl('webpage', 'list_all')
	        }),
	        reader: new Ext.data.JsonReader({
	            root: 'webpages',
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
							this.fireEvent('messageToShow', lang("no objects message", lang("objects"), sel_context_names.join(', ')));
						} else {
							this.fireEvent('messageToShow', lang("no more objects message", lang("objects")));
						}
					} else {
						this.fireEvent('messageToShow', "");
					}
					
					var cmp = Ext.getCmp('webpage-manager');
					if (cmp) {
						var sm = cmp.getSelectionModel();
						sm.clearSelections();
					}
					Ext.getCmp('webpage-manager').reloadGridPagingToolbar('webpage','list_all','webpage-manager');
					
					og.eventManager.fireEvent('replace all empty breadcrumb', null);
				}
			}
	    });
	    og.WebpageManager.store.setDefaultSort('updated', 'desc');
    }
    this.store = og.WebpageManager.store;
    this.store.addListener({messageToShow: {fn: this.showMessage, scope: this}});
    //--------------------------------------------
    // Renderers
    //--------------------------------------------

	function renderDragHandle(value, p, r, ix) {
		return '<div class="img-grid-drag" title="' + lang('click to drag') + '" onmousedown="var sm = Ext.getCmp(\'webpage-manager\').getSelectionModel();if (!sm.isSelected('+ix+')) sm.clearSelections();sm.selectRow('+ix+', true);"></div>';
	}
    
	var readClass = 'read-unread-' + Ext.id();
	
    function renderName(value, p, r) {
    	var classes = readClass + r.id;
		if (!r.data.isRead) classes += " bold";
		
		var name = String.format(
			'<a style="font-size:120%;" class="{3}" title="{2}" href="{1}" onclick="og.openLink(\'{1}\');return false;">{0}</a>',
			og.clean(value), og.getUrl('webpage', 'view', {id: r.id}), lang('view weblink'), classes);
		
		var actionStyle= ' style="color:#777777;padding-top:3px;padding-left:18px;background-repeat:no-repeat;background-position:0px 1px;" ';
		
		var actions = String.format('<a class="list-action ico-open-link" href="{0}" target="_blank" title="{1}" ' + actionStyle + '>&nbsp;</a>',
			r.data.url.replace(/\"/g, escape("\"")).replace(/\'/g, escape("'")), lang('open link in new window', og.clean(value)));
		actions = '<span>' + actions + '</span>';
			
		var text = '';
		if (r.data.description != ''){
			text = '&nbsp;-&nbsp;<span class="desc nobr">';
			text += og.clean(r.data.description) + "</span>";
		}
		
		mem_path = "";
		var mpath = Ext.util.JSON.decode(r.data.memPath);
		if (mpath){ 
			mem_path = "<div class='breadcrumb-container' style='display: inline-block;min-width: 250px;'>";
			mem_path += og.getEmptyCrumbHtml(mpath, '.breadcrumb-container', og.breadcrumbs_skipped_dimensions);
			mem_path += "</div>";
		}
	    
		return name + actions + mem_path + text;
	}
    
    function renderIcon(value, p, r) {
		return '<div class="db-ico ico-webpage"></div>';
	}
    
    function renderIsRead(value, p, r){
    	var idr = Ext.id();
		var idu = Ext.id();
		var jsr = 'og.WebpageManager.store.getById(\'' + r.id + '\').data.isRead = true; Ext.select(\'.' + readClass + r.id + '\').removeClass(\'bold\'); Ext.get(\'' + idu + '\').setDisplayed(true); Ext.get(\'' + idr + '\').setDisplayed(false); og.openLink(og.getUrl(\'object\', \'mark_as_read\', {ids:' + r.id + '}));'; 
		var jsu = 'og.WebpageManager.store.getById(\'' + r.id + '\').data.isRead = false; Ext.select(\'.' + readClass + r.id + '\').addClass(\'bold\'); Ext.get(\'' + idr + '\').setDisplayed(true); Ext.get(\'' + idu + '\').setDisplayed(false); og.openLink(og.getUrl(\'object\', \'mark_as_unread\', {ids:' + r.id + '}));';
		return String.format(
			'<div id="{0}" title="{1}" class="db-ico ico-read" style="display:{2}" onclick="{3}"></div>' + 
			'<div id="{4}" title="{5}" class="db-ico ico-unread" style="display:{6}" onclick="{7}"></div>',
			idu, lang('mark as unread'), value ? 'block' : 'none', jsu, idr, lang('mark as read'), value ? 'none' : 'block', jsr
		);
	}
    
	function renderDateUpdated(value, p, r) {
		if (!value) {
			return "";
		}
		var userString = String.format('<a href="{1}" onclick="og.openLink(\'{1}\');return false;">{0}</a>', r.data.updatedBy, og.getUrl('contact', 'card', {id: r.data.updatedById}));

		var now = new Date();
		var dateString = '';
		if (!r.data.updatedOn_today) {
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
				ret += "," + selections[i].id;
			}
			og.lastSelectedRow.webpages = selections[selections.length-1].data.ix;
			return ret.substring(1);
		}
	}
	this.getSelectedIds = getSelectedIds;
	
	function getFirstSelectedId() {
		if (sm.hasSelection()) {
			return sm.getSelected().id;
		}
		return '';
	}

	var sm = new Ext.grid.CheckboxSelectionModel();
	sm.on('selectionchange',
		function() {
			var allUnread = true, allRead = true;
			var selections = sm.getSelections();
			for (var i=0; i < selections.length; i++) {
				if (selections[i].data.isRead){
					allUnread = false;
				} else {
					allRead = false;
				}
			}
			if (sm.getCount() <= 0) {
				actions.delWebpage.setDisabled(true);
				actions.editWebpage.setDisabled(true);
				markactions.markAsRead.setDisabled(true);
				markactions.markAsUnread.setDisabled(true);
				actions.archive.setDisabled(true);
			} else {
				actions.editWebpage.setDisabled(sm.getCount() != 1);
				actions.delWebpage.setDisabled(false);
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
			/*id: 'draghandle',
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
        	fixed: true,
        	resizable: false,
        	hideable:true,
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
			id: 'name',
			header: lang("name"),
			dataIndex: 'name',
			width: 300,
			sortable: true,
			renderer: renderName
        },{
        	id: 'updated',
			header: lang("last updated by"),
			dataIndex: 'updatedOn',
			width: 90,
			renderer: renderDateUpdated,
			sortable: true
        }];
    // custom property columns
	var cps = og.custom_properties_by_type['weblink'] ? og.custom_properties_by_type['weblink'] : [];
	for (i=0; i<cps.length; i++) {
		cm_info.push({
			id: 'cp_' + cps[i].id,
			header: cps[i].name,
			dataIndex: 'cp_' + cps[i].id,
			sortable: false,
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
		newWebpage: new Ext.Action({
			text: lang('new'),
            tooltip: lang('add new webpage'),
            iconCls: 'ico-new new_button',
            handler: function() {
				og.render_modal_form('', {c:'webpage', a:'add'});
			}
		}),
		delWebpage: new Ext.Action({
			text: lang('move to trash'),
            tooltip: lang('move selected objects to trash'),
            iconCls: 'ico-trash',
			disabled: true,
			handler: function() {
				if (confirm(lang('confirm move to trash'))) {
					this.load({
						action: 'delete',
						webpages: getSelectedIds()
					});
				}
			},
			scope: this
		}),
		editWebpage: new Ext.Action({
			text: lang('edit'),
            tooltip: lang('edit selected webpage'),
            iconCls: 'ico-edit',
			disabled: true,
			handler: function() {
				og.render_modal_form('', {c:'webpage', a:'edit', params: {id:getFirstSelectedId()}});
			},
			scope: this
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
						webpages: getSelectedIds()
					});
					this.getSelectionModel().clearSelections();
				}
			},
			scope: this
		}),
		refresh: new Ext.Action({
			text: lang('refresh'),
            tooltip: lang('refresh desc'),
            iconCls: 'ico-refresh',
			handler: function() {
				this.store.reload();
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
		})
    };
    
	var tbar = [];
	if (!og.loggedUser.isGuest) {
		tbar.push(actions.newWebpage);
		tbar.push('-');
		tbar.push(actions.editWebpage);
		tbar.push(actions.archive);
		tbar.push(actions.delWebpage);		
		tbar.push('-');
	}
	tbar.push(actions.markAs);

	if (og.additional_list_actions && og.additional_list_actions.webpage) {
		tbar.push('-');
		for (var i=0; i<og.additional_list_actions.webpage.length; i++) {
			tbar.push(og.additional_list_actions.webpage[i]);
		}
	}
	
	og.WebpageManager.superclass.constructor.call(this, {
        store: this.store,
		layout: 'fit',
        cm: cm,
        enableDrag: true,
		ddGroup: 'MemberDD',
		stateful: og.preferences['rememberGUIState'],
        closable: true,
		stripeRows: true,
		loadMask: true,
		id: 'webpage-manager',
		bbar: new og.CurrentPagingToolbar({
            pageSize: og.config['files_per_page'],
            store: this.store,
            displayInfo: true,
            displayMsg: lang('displaying webpages of'),
            emptyMsg: lang("no webpages to display")
        }),
		viewConfig: {
            forceFit: true
        },
		sm: sm,
		tbar: tbar,
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

Ext.extend(og.WebpageManager, Ext.grid.GridPanel, {
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
		this.needRefresh = false;
	},
	
	activate: function() {
		if (this.needRefresh) {
			this.load({start: 0});
		}
	},
	
	reset: function() {
		this.load({start:0});
                this.getSelectionModel().clearSelections();
	},
	
	showMessage: function(text) {
		this.innerMessage.innerHTML = text;
	},
	
	archiveObjects: function() {
		if (confirm(lang('confirm archive selected objects'))) {
			this.load({
				action: 'archive',
				webpages: this.getSelectedIds()
			});
			this.getSelectionModel().clearSelections();
		}
	},
	
	trashObjects: function() {
		if (confirm(lang('confirm move to trash'))) {
			this.load({
				action: 'delete',
				webpages: this.getSelectedIds()
			});
			this.getSelectionModel().clearSelections();
		}
	}
	
});

Ext.reg("webpages", og.WebpageManager);