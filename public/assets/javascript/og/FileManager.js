/**
 *  FileManager
 *
 */
og.FileManager = function() {
	var actions, markactions;

	this.doNotRemove = true;
	this.needRefresh = false;
	this.objectTypeId = 0;
	
	this.fields = [
		'name', 'object_id', 'type', 'ot_id', 'createdBy', 'createdById', 'size',
		'dateCreated', 'dateCreated_today',
		'updatedBy', 'updatedById',
		'dateUpdated', 'dateUpdated_today',
		'icon', 'wsIds', 'manager', 'checkedOutById',
		'checkedOutByName', 'mimeType', 'isModifiable',
		'modifyUrl', 'songInfo', 'ftype', 'url', 'ix','isRead', 'isMP3', 'memPath'
	];
	var cps = og.custom_properties_by_type['file'] ? og.custom_properties_by_type['file'] : [];
	var cp_names = [];
	for (i=0; i<cps.length; i++) {
		cp_names.push('cp_' + cps[i].id);
	}
	this.fields = this.fields.concat(cp_names);

	og.eventManager.fireEvent('hook_document_classification', this);
	
	if (!og.FileManager.store) {
		og.FileManager.store = new Ext.data.Store({
			proxy: new og.GooProxy({
				url: og.getUrl('files', 'list_files')
			}),
			reader: new Ext.data.JsonReader({
				root: 'files',
				totalProperty: 'totalCount',
				id: 'id',
				objType: 'objType',
				fields: this.fields
			}),
			remoteSort: true,
			listeners: {
				'load': function(store, result) {
					var d = this.reader.jsonData;
					
					
					if (d.totalCount == 0) {
						var sel_context_names = og.contextManager.getActiveContextNames();
						if (sel_context_names.length > 0) {
							this.fireEvent('messageToShow', lang("no objects message", lang("documents"), sel_context_names.join(', ')));
						} else {
							this.fireEvent('messageToShow', lang("no more objects message", lang("documents")));
						}
					} else {
						this.fireEvent('messageToShow', "");
					}
					var cmp = Ext.getCmp('file-manager');
					if (cmp) {
						cmp.getView().focusRow(og.lastSelectedRow.documents+1);
						cmp.objectTypeId = d.objType;
						var sm = cmp.getSelectionModel();
						sm.clearSelections();
					}
					
					Ext.getCmp('file-manager').reloadGridPagingToolbar('files','list_files','file-manager');
					
					og.eventManager.fireEvent('replace all empty breadcrumb', null);
				}
			}
		});
		og.FileManager.store.setDefaultSort('dateUpdated', 'desc');
	}
	this.store = og.FileManager.store;
	this.store.addListener({messageToShow: {fn: this.showMessage, scope: this}});
	
	function renderDragHandle(value, p, r, ix) {
		return '<div class="img-grid-drag" title="' + lang('click to drag') + '" onmousedown="var sm = Ext.getCmp(\'file-manager\').getSelectionModel();if (!sm.isSelected('+ix+')) sm.clearSelections();sm.selectRow('+ix+', true);"></div>';
	}
	
	var readClass = 'read-unread-' + Ext.id();
	function renderName(value, p, r) {
		var result = '';
		
		var classes = readClass + r.id;
		if (!r.data.isRead) classes += " bold";
		
		mem_path = "";
		var mpath = Ext.util.JSON.decode(r.data.memPath);
		if (mpath){ 
			mem_path = "<div class='breadcrumb-container' style='display: inline-block;min-width: 250px;'>";
			mem_path += og.getEmptyCrumbHtml(mpath, '.breadcrumb-container', og.breadcrumbs_skipped_dimensions);
			mem_path += "</div>";
		}
		var name = String.format(
			'<a style="font-size:120%;" class="{3}" href="{2}" onclick="og.openLink(\'{2}\');return false;">{0}</a>',
			og.clean(og.removeFileExtension(value)), r.data.name, og.getUrl('files', 'file_details', {id: r.data.object_id}), classes) + mem_path;
		
		return name;
	}
	function renderIsRead(value, p, r){
		var idr = Ext.id();
		var idu = Ext.id();
		var jsr = 'og.FileManager.store.getById(\'' + r.id + '\').data.isRead = true; Ext.select(\'.' + readClass + r.id + '\').removeClass(\'bold\'); Ext.get(\'' + idu + '\').setDisplayed(true); Ext.get(\'' + idr + '\').setDisplayed(false); og.openLink(og.getUrl(\'object\', \'mark_as_read\', {ids:' + r.data.object_id + '}));'; 
		var jsu = 'og.FileManager.store.getById(\'' + r.id + '\').data.isRead = false; Ext.select(\'.' + readClass + r.id + '\').addClass(\'bold\'); Ext.get(\'' + idr + '\').setDisplayed(true); Ext.get(\'' + idu + '\').setDisplayed(false); og.openLink(og.getUrl(\'object\', \'mark_as_unread\', {ids:' + r.data.object_id + '}));';
		return String.format(
			'<div id="{0}" title="{1}" class="db-ico ico-read" style="display:{2}" onclick="{3}"></div>' + 
			'<div id="{4}" title="{5}" class="db-ico ico-unread" style="display:{6}" onclick="{7}"></div>',
			idu, lang('mark as unread'), value ? 'block' : 'none', jsu, idr, lang('mark as read'), value ? 'none' : 'block', jsr
		);
	}	

	function renderIcon(value, p, r) {
		var classes = "db-ico ico-unknown ico-" + r.data.type;
		if (r.data.name.indexOf(".") >= 0) {
			var extension = r.data.name.substring(r.data.name.indexOf(".") + 1);
			classes += " ico-ext-" + extension;
		}
		if (r.data.ftype == 1){
			classes += ' ico-webfile';
		}
		if (r.data.mimeType) {
			var path = r.data.mimeType.replace(/[\/\+]/g, "-").split("-");
			var acc = "";
			for (var i=0; i < path.length; i++) {
				acc += path[i];
				classes += " ico-" + acc.replace(/\./g, "_");
				acc += "-";
			}
		}
		return String.format('<div class="{0}" />', classes);
	}

	function renderDateUpdated(value, p, r) {
		if (!value) {
			return "";
		}
		var userString = String.format('<a href="{1}" onclick="og.openLink(\'{1}\');return false;">{0}</a>', r.data.updatedBy, og.getUrl('contact', 'card', {id: r.data.updatedById}));
	
		var now = new Date();
		var dateString = '';
		if (!r.data.dateUpdated_today) {
			return lang('last updated by on', userString, value);
		} else {
			return lang('last updated by at', userString, value);
		}
	}
	
	function renderDateCreated(value, p, r) {
		if (!value) {
			return "";
		}
		var userString = String.format('<a href="{1}" onclick="og.openLink(\'{1}\');return false;">{0}</a>', r.data.createdBy, og.getUrl('contact', 'card', {id: r.data.createdById}));
	
		var now = new Date();
		var dateString = '';
		if (!r.data.dateCreated_today) {
			return lang('last updated by on', userString, value);
		} else {
			return lang('last updated by at', userString, value);
		}
	}

	function renderCheckout(value, p, r) {
		if(r.data.ftype == 0){
			if (value =='') {
				return String.format('<div class="ico-unlocked" style="display:block;height:16px;background-repeat:no-repeat;padding-left:18px;padding-top:3px;">'
				+ '<a href="#" onclick="og.openLink(\'{1}\')" title="{2}">{0}</a>', lang('lock'), og.getUrl('files', 'checkout_file', {id: r.id}), lang('checkout description'));
			} else if (r.data.checkedOutById == og.loggedUser.id || og.loggedUser.type == 1 || og.loggedUser.type == 2) {
				//og.loggedUser.type 1 = Super Administrator, 2 = Administrator
				var html = String.format('<div class="ico-locked" style="display:block;height:16px;background-repeat:no-repeat;padding-left:18px;padding-top:3px;"><a href="#" onclick="og.openLink(\'{1}\')">{0}</a>', 
					lang('unlock'), og.getUrl('files', 'undo_checkout', {id: r.id}));
				
				if (r.data.checkedOutById == og.loggedUser.id) {
					html += String.format(', <a href="#" onclick="og.openLink(\'{1}\')" title="{2}">{0}</a>', lang('checkin'), 
						og.getUrl('files', 'checkin_file', {id: r.id}), lang('checkin description'));
				} else {
					html += ', ' + lang('checked out by', String.format('<a href="#" onclick="og.openLink(\'{1}\')">{0}</a>', 
						r.data.checkedOutByName, og.getUrl('contact', 'card', {id: r.data.checkedOutById})));
				}
				
				html += '</div>';
				
				return html;
			
			} else {
				return '<div class="ico-locked" style="display:block;height:16px;background-repeat:no-repeat;padding-left:18px">' +
					lang('checked out by', String.format('<a href="#" onclick="og.openLink(\'{1}\')">{0}</a>', 
					r.data.checkedOutByName, og.getUrl('contact', 'card', {id: r.data.checkedOutById}))) + '</div>';
			}
		} else {
			return "--";
		}
	}
	
	function renderActions(value, p, r) {
		var actions = '';
		var actionStyle= ' style="font-size:105%;padding-bottom:3px;padding-left:16px;background-repeat:no-repeat;" '; 
		
		if(r.data.ftype == 0){
			if(og.config['checkout_notification_dialog'] == 0){
				actions += String.format('<a class="list-action ico-download" href="{0}" target="_self" title="{1}" ' + actionStyle + '>&nbsp;</a>',
					og.getUrl('files', 'download_file', {id: r.id}),lang('download'));
			}else{
				actions += String.format('<a class="list-action ico-download" href="#" onclick="og.checkDownload(\'{0}\', \'{1}\', \'{2}\', \'{4}\');" title="{3}" ' + actionStyle + '>&nbsp;</a>',
				og.getUrl('files', 'download_file', {id: r.id}), r.data.checkedOutById, r.data.checkedOutByName, lang('download'), r.id);
			}			
		}else{
			actions += String.format("<a href='{0}' class='list-action ico-open-link' target='_blank'" + actionStyle + ">&nbsp;</a>&nbsp;", r.data.url, 'public/assets/themes/default/images/16x16/openlink.png');
		}
		
		if (r.data.isModifiable) {
			actions += String.format(
			'<a class="list-action ico-edit" href="#" onclick="og.openLink(\'{0}\')" title="{1}" ' + actionStyle + '>&nbsp;</a>',
			r.data.modifyUrl,lang('edit this document'));
		}
		
		if (r.data.isMP3) {
			actions += String.format(
			'<a class="list-action ico-play" href="#" onclick="og.playMP3({0})" title="{1}" ' + actionStyle + '>&nbsp;</a>',
					r.data.songInfo.replace(/'/g, "\\'").replace(/"/g, "'"), lang('play this file'));
			actions += String.format(
			'<a class="list-action ico-queue" href="#" onclick="og.queueMP3({0})" title="{1}" ' + actionStyle + '>&nbsp;</a>',
					r.data.songInfo.replace(/'/g, "\\'").replace(/"/g, "'"), lang('queue this file'));
		} else if (r.data.mimeType == 'application/xspf+xml') {
			actions += String.format(
			'<a class="list-action ico-play" href="#" onclick="og.playXSPF({0})" title="{1}" ' + actionStyle + '>&nbsp;</a>',
					r.id, lang('play this file'));
		} else if (r.data.mimeType == 'prsn') {
			actions += String.format(
			'<a class="list-action ico-slideshow" href="#" onclick="og.slideshow({0})" title="{1}" ' + actionStyle + '>&nbsp;</a>',
					r.id, lang('view slideshow'));
		}
		
		if (og.FileIsZip(r.data.mimeType, r.data.name) && og.zipSupported) {
			actions += String.format(
			'<a class="list-action ico-zip-extract" href="#" onclick="og.openLink(og.getUrl(\'files\', \'zip_extract\', {id:{0}}))" title="{1}" ' + actionStyle + '>&nbsp;</a>',
			r.data.object_id,lang('extract files'));
		}
		
		if (actions != '')
			actions = '<span>' + actions + '</span>';
			
		return actions;
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
			og.lastSelectedRow.documents = selections[selections.length-1].data.ix;
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
	
	function zipFiles(zipFileName) {
		if (zipFileName.length == 0) zipFileName = 'new compressed file.zip';
		
		if (zipFileName.lastIndexOf('.zip') == -1 || zipFileName.lastIndexOf('.zip') != zipFileName.length - 4 ) 
			zipFileName += '.zip';
		
		og.openLink(og.getUrl('files', 'list_files', {
			action: 'zip_add',
			filename: zipFileName,
			objects: getSelectedIds()
		}));
		sm.clearSelections();
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
				actions.properties.setDisabled(true);
				actions.zip_add.setDisabled(true);
				actions.del.setDisabled(true);
				markactions.markAsRead.setDisabled(true);
				markactions.markAsUnread.setDisabled(true);
				actions.archive.setDisabled(true);
			} else {
				actions.properties.setDisabled(sm.getCount() != 1);
				actions.zip_add.setDisabled(false);
				actions.del.setDisabled(false);
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
			
			args = {};
			args.sm = sm;
			args.actions = actions;
			og.eventManager.fireEvent('hook_classification_enable', args);		
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
        	dataIndex: 'icon',
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
			id: 'name',
			header: lang("name"),
			dataIndex: 'name',
			width: 300,
			renderer: renderName,
			sortable: true
        },{
			id: 'size',
			header: lang('size'),
			dataIndex: 'size',
			width: 120,
			sortable: true
        },{
			id: 'type',
			header: lang('type'),
			dataIndex: 'type',
			width: 120,
			hidden: true
		},{
			id: 'updated',
			header: lang("last updated by"),
			dataIndex: 'dateUpdated',
			width: 120,
			renderer: renderDateUpdated,
			sortable: true
        },{
			id: 'created',
			header: lang("created by"),
			dataIndex: 'dateCreated',
			width: 120,
			hidden: true,
			renderer: renderDateCreated,
			sortable: true
		},{
			id: 'status',
			header: lang("status"),
			dataIndex: 'checkedOutByName',
			width: 120,
			renderer: renderCheckout
		},{
			id: 'actions',
			header: lang("actions"),
			width: 60,
			fixed: true,
			renderer: renderActions,
			sortable: false
		}
	];
	// custom property columns
	var cps = og.custom_properties_by_type['file'] ? og.custom_properties_by_type['file'] : [];
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

	og.eventManager.fireEvent('hook_filemanager_columns', cm.config);
	
	markactions = {
		markAsRead: new Ext.Action({
			text: lang('mark as read'),
            tooltip: lang('mark as read desc'),
            iconCls: 'ico-mark-as-read',
			disabled: true,
			handler: function() {
				this.load({
					action: 'markasread',
					objects: getSelectedIds()
				});
				this.getSelectionModel().clearSelections();
			},
			scope: this
		}),
		markAsUnread: new Ext.Action({
			text: lang('mark as unread'),
            tooltip: lang('mark as read desc'),
            iconCls: 'ico-mark-as-unread',
			disabled: true,
			handler: function() {
			this.load({
				action: 'markasunread',
				objects: getSelectedIds()				
			});
			this.getSelectionModel().clearSelections();
			},
			scope: this
		})
	};
	
	actions = {
		newCO: new Ext.Action({
			text: lang('new'),
            tooltip: lang('create an object'),
            iconCls: 'ico-new new_button',
			menu: {items: [
				{text: lang('upload file'), iconCls: 'ico-upload', handler: function() {
					og.render_modal_form('', {c:'files', a:'add_file'});
				}},
				'-',
				{text: lang('document'), iconCls: 'ico-doc', handler: function() {
					var url = og.getUrl('files', 'add_document');
					
					var context = Ext.util.JSON.decode(og.contextManager.plainContext());
					
					var can_add = true;
					var dimensions_panel = Ext.getCmp('menu-panel');
					var dim_names = '';
					
					dimensions_panel.items.each(function(item, index, length) {
						for (x = 0; x < item.initialConfig.requiredObjectTypes.length; x++) {
							var t = item.initialConfig.requiredObjectTypes[x];
							var selected_members = og.contextManager.getDimensionMembers(item.dimensionId);

							if (t == Ext.getCmp('file-manager').objectTypeId) {
								if (!selected_members || selected_members == '0' || selected_members.length == 0) {
									can_add = false;
									dim_names += (dim_names == '' ? '"' : '", "') + item.title + '"';
								}
							}
						}
					});
					
					if (can_add) {
						og.openLink(url);
					} else {
						og.err(lang('you must select a member from the following dimensions', dim_names));
					}
					
				}},
				{text: lang('web document'), iconCls: 'ico-weblink', handler: function() {
					og.render_modal_form('', {c:'files', a:'add_weblink'});
				}},
				{text: lang('presentation'), iconCls: 'ico-prsn', handler: function() {
					var url = og.getUrl('files', 'add_presentation');
					
					var context = Ext.util.JSON.decode(og.contextManager.plainContext());
					
					var can_add = true;
					var dimensions_panel = Ext.getCmp('menu-panel');
					var dim_names = '';
					
					dimensions_panel.items.each(function(item, index, length) {
						for (x = 0; x < item.initialConfig.requiredObjectTypes.length; x++) {
							var t = item.initialConfig.requiredObjectTypes[x];
							var selected_members = og.contextManager.getDimensionMembers(item.dimensionId);

							if (t == Ext.getCmp('file-manager').objectTypeId) {
								if (!selected_members || selected_members == '0' || selected_members.length == 0) {
									can_add = false;
									dim_names += (dim_names == '' ? '"' : '", "') + item.title + '"';
								}
							}
						}
					});
					
					if (can_add) {
						og.openLink(url);
					} else {
						og.err(lang('you must select a member from the following dimensions', dim_names));
					}
				}}
			]}
		}),
		properties: new Ext.Action({
			text: lang('update file'),
			tooltip: lang('edit selected file properties'),
			iconCls: 'ico-properties',
			disabled: true,
			handler: function(e) {
				var o = sm.getSelected();
				if(o.data.ftype == 1){
					og.render_modal_form('', {c:'files', a:'edit_weblink', params: {id: o.data.object_id, manager: o.data.manager}});
				}else{
					og.render_modal_form('', {c:'files', a:'edit_file', params: {id: o.data.object_id, manager: o.data.manager}});
				}
			}
		}),
		zip_add: new Ext.Action({
			text: lang('compress'),
            tooltip: lang('compress selected files'),
            iconCls: 'ico-zip-add',
			disabled: true,
			hidden: !og.zipSupported,
			handler: function() {
				Ext.Msg.prompt(lang('new compressed file'),
					lang('name'),
					function (btn, text) {
						if (btn == 'ok' && text) {
							zipFiles(text);
						}
					},
					this	
				);
			},
			scope: this
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
						objects: getSelectedIds()
					});
					this.getSelectionModel().clearSelections();
				}
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
						ids: getSelectedIds()
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
		tbar.push(actions.newCO);
		tbar.push('-');
		tbar.push(actions.properties);
		tbar.push(actions.zip_add);
		tbar.push(actions.archive);
		tbar.push(actions.del);		
		tbar.push('-');
	}
	tbar.push(actions.markAs);
	
	if (og.additional_list_actions && og.additional_list_actions.file) {
		tbar.push('-');
		for (var i=0; i<og.additional_list_actions.file.length; i++) {
			tbar.push(og.additional_list_actions.file[i]);
		}
	}
	
	og.FileManager.superclass.constructor.call(this, {
		store: this.store,
		layout: 'fit',
		cm: cm,
		enableDrag: true,
		ddGroup: 'MemberDD',
		stateful: og.preferences['rememberGUIState'],
		stripeRows: true,
		closable: true,
		id: 'file-manager',
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
	
	args = {};
	args.actions = actions;
	args.fm = this;
	args.sm = sm;
	og.eventManager.fireEvent('hook_filemanager_actions', args);	

};

Ext.extend(og.FileManager, Ext.grid.GridPanel, {
	load: function(params) {
		if (!params) params = {};
		if (typeof params.start == 'undefined') {
			var start = (this.getBottomToolbar().getPageData().activePage - 1) * og.config['files_per_page'];
		} else {
			var start = 0;
		}
		Ext.apply(this.store.baseParams, {
		      context: og.contextManager.plainContext()

		});
		this.store.removeAll();
		this.store.load({
			params: Ext.applyIf(params, {
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
	},
	
	showMessage: function(text) {
		if (this.innerMessage) {
			this.innerMessage.innerHTML = text;
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
	},
	
	trashObjects: function() {
		if (confirm(lang('confirm move to trash'))) {
			this.load({
				action: 'delete',
				objects: this.getSelectedIds()
			});
			this.getSelectionModel().clearSelections();
		}
	}
});

Ext.reg("files", og.FileManager);


