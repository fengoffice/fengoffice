/**
 *  MailManager
 *
 */
mails_per_page = parseInt(og.config['mails_per_page']) || og.config['files_per_page'];
og.MailManager = function() {

	var actions, moreActions, selectActions, accountActions, markactions;
	this.accountId = og.emailFilters.account;
	this.readType = og.emailFilters.read;
	this.classifType = og.emailFilters.classif;
	this.viewType = "all";
	this.stateType = "received";
	this.doNotRemove = true;
	this.needRefresh = false;
	this.maxrowidx = 0;
	this.last_email_date = '0000-00-00 00:00:00';
	this.last_context_sent = '';

	this.fields = [
		'object_id', 'type', 'ot_id', 'accountId', 'accountName', 'hasAttachment', 'subject', 'text', 'date', 'rawdate',
		'memberIds', 'projectName', 'userId', 'userName', 'workspaceColors','isRead', 'from', 'memPath',
		'from_email','isDraft','isSent','folder','to', 'ix', 'conv_total', 'conv_unread', 'conv_hasatt'
	];

	var cps = og.custom_properties_by_type['mail'] ? og.custom_properties_by_type['mail'] : [];
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
   	
	this.Record = Ext.data.Record.create(this.fields);
	
	if (!og.MailManager.store) {
		og.MailManager.store = new Ext.data.Store({
			proxy: new og.GooProxy({
				url: og.getUrl('mail', 'list_all'),
				timeout: 0//Ext.Ajax.timeout
			}),
			reader: new Ext.data.JsonReader({
				root: 'messages',
				totalProperty: 'totalCount',
				id: 'id',
				fields: this.fields
			}),
			remoteSort: true,
			listeners: {
				'load': function(store, rs) {
					var d = this.reader.jsonData;
					var manager = Ext.getCmp('mails-manager');
					
					// if response has check_id check if it is the last check_id sent, if not then ignore the response.
					if (d.check_id && d.check_id != manager.last_check_id) {
						return;
					}
					
					store.totalLength = store.proxy.totalLength;
					if (d.totalCount == 0) {
						var sel_context_names = og.contextManager.getActiveContextNames();
						if (sel_context_names.length > 0) {
							this.fireEvent('messageToShow', lang("no objects message", lang("emails"), sel_context_names.join(', ')));
						} else {
							this.fireEvent('messageToShow', lang("no more objects message", lang("emails")));
						}
					} else {
						this.fireEvent('messageToShow', "");
					}
					//Ext.getCmp('mails-manager').getView().focusRow(og.lastSelectedRow.mails+1);
					if (typeof d.unreadCount != 'undefined') {
						og.updateUnreadEmail(d.unreadCount);
					}
					
					
					var view = manager.getView();
					for (i=0; i<manager.maxrowidx; i++) {
						var el = view.getRow(i);
						if (el) el.innerHTML = el.innerHTML.replace('x-grid3-td-draghandle "', 'x-grid3-td-draghandle " onmousedown="var sm = Ext.getCmp(\'mails-manager\').getSelectionModel();if (!sm.isSelected('+i+')) {sm.clearSelections();} sm.selectRow('+i+', true);"');
					}
					
					og.mail.mails_to_remove_from_list = [];
					
					//reload columns for this folder
					showFolderColumns();
					
					var text_filter = $("#mails-manager #text_filter").val();

					if(!text_filter || text_filter.trim() == ''){
						manager.reloadGridPagingToolbar('mail','list_all','mails-manager');
					}
					
					og.eventManager.fireEvent('replace all empty breadcrumb', null);
					
					// save last email date in a variable
					var active_page = manager.getBottomToolbar().getPageData().activePage;
					if (active_page == 1 && d.messages.length > 0) {
						manager.last_email_date = d.messages[0].rawdate;
					}
				}
			}
		});
		og.MailManager.store.setDefaultSort('date', 'desc');
	}
	this.store = og.MailManager.store;
	this.store.addListener({messageToShow: {fn: this.showMessage, scope: this}});
	
	function renderName(value, p, r) {
		var name = '';
		var classes = "";
		if (!r.data.isRead) classes += " bold";
		var strAction = 'view';
		
		if (r.data.isDraft) {
			strDraft = "<span style='font-size:90%;color:red'>"+lang('draft')+"&nbsp;</style>";			
			strAction = 'edit_mail';
		} else {
			strDraft = '';
		}
		
		var subject = value && og.clean(value.trim()) || '<span class="italic">' + lang("no subject") + '</span>';
		var conv_str = r.data.conv_total > 1 ? " <span class='db-ico ico-comment' style='margin-left:3px;padding-left: 18px;'><span style='font-size:80%'>(" + (r.data.conv_unread > 0 ? '<b style="font-size:130%">' + r.data.conv_unread + '</b>/' : '') + r.data.conv_total + ")</span></span>" : "";
		
		mem_path = "";
		var mpath = Ext.util.JSON.decode(r.data.memPath);
		if (typeof mpath.length === "undefined"){ 
			mem_path = "<div class='breadcrumb-container' style='display: inline-block;'>";
			mem_path += og.getEmptyCrumbHtml(mpath, '.breadcrumb-container', og.breadcrumbs_skipped_dimensions);
			mem_path += "</div>";
		}
		
		var js = 'var r = og.MailManager.store.getById(\'' + r.id + '\'); r.data.isRead = true;og.openLink(\'{1}\');r.commit();og.eventManager.fireEvent(\'replace all empty breadcrumb\', null);return false;';
		name = String.format(
				'{4}<a style="font-size:120%;" class="{3}" href="#" onclick="' + js + '" title="{2}">{0}</a>',
				subject + conv_str, og.getUrl('mail', strAction, {id: r.data.object_id}), og.clean(r.data.text),classes,strDraft);
				
		if (r.data.isSent) {
			name = String.format('<span class="db-ico ico-sent" style="padding-left:18px" title="{1}">{0}</span>',name,lang("mail sent"));
		}
		
		var text = '';
		if (r.data.text != ''){
			text = '&nbsp;-&nbsp;<span style="color:#888888;white-space:nowrap">';
			text += og.clean(r.data.text) + "</span></i>";
		}
		return name + mem_path + text ;
	}
	
	

	function renderFrom(value, p, r){
		var strAction = 'view';
		var classes = "";
		
		if (r.data.isDraft) strAction = 'edit_mail';
		if (!r.data.isRead) classes += ' bold';

		var draw_to = (r.data.isSent || r.data.isDraft) && Ext.getCmp('mails-manager').stateType != 'sent';
		if (!r.data.to) r.data.to = "";
		var to_cut = r.data.to.length > 70 ? og.clean(r.data.to.substring(0, 67) + "...") : og.clean(r.data.to);
		var sender = (draw_to ? to_cut : og.clean(value.trim())) || '<span class="italic">' + lang("no sender") + '</span>';
		var title = draw_to ? og.clean(r.data.to) : og.clean(r.data.from_email);
		
		var js = 'var r = og.MailManager.store.getById(\'' + r.id + '\'); r.data.isRead = true;og.openLink(\'{1}\');r.commit();og.eventManager.fireEvent(\'replace all empty breadcrumb\', null);return false;';
		name = String.format(
				'<a style="font-size:120%;" class="{3}" href="#" onclick="' + js + '" title="{2}">{0}</a>',
				sender, og.getUrl('mail', strAction, {id: r.data.object_id}), title, classes);
		return name;
	}
	
	function renderDragHandle(value, p, r, ix) {
		Ext.getCmp('mails-manager').maxrowidx = ix;
		return '<div class="img-grid-drag" title="' + lang('click to drag') + '""></div>';
	}
	
	function renderIcon(value, p, r) {
		if (r.data.memberIds.length > 0) {
			return '<div class="db-ico ico-email"></div>';
		} else {
			return String.format('<a href="#" onclick="{0}" title={1}><div class="db-ico ico-classify"></div></a>', "og.render_modal_form('', {c:'mail', a:'classify', params: {id: "+r.data.object_id+", from_mail_list: true},focusFirst: false})", lang('classify'));
		}
	}

	function renderAttachment(value, p, r){
		if (value)
			return '<div class="db-ico ico-attachment"></div>';
		else
			return '';
	}
	
	function renderIsRead(value, p, r){
		var js = 'var r = og.MailManager.store.getById(\'' + r.id + '\'); r.data.isRead = !r.data.isRead;og.openLink(og.getUrl(\'object\', \'' + (value ? 'mark_as_unread' : 'mark_as_read') + '\', {ids:\'' + r.data.object_id + '\', dont_remove:1}), {hideLoading:true});r.commit();';
		return String.format(
				'<div title="{0}" class="db-ico {2}" onclick="{1}"></div>',
				value ? lang('mark as unread') : lang('mark as read'), js, value ? 'ico-read' : 'ico-unread'
		);
	}

	function renderAccount(value, p, r) {
		return String.format('<a href="#" onclick="og.eventManager.fireEvent(\'mail account select\',[\'{1}\', \'{0}\'])">{0}</a>', og.clean(value), r.data.accountId);
	}
	
	function renderTo(value, p, r) {
		var classes = "";
		var strAction = 'view';
		
		if (r.data.isDraft) strAction = 'edit_mail';
		if (!r.data.isRead) classes += ' bold';
		
		var receiver = value && og.clean(value.trim()) || '<span class="italic">' + lang("no recipient") + '</span>';

		name = String.format(
				'<a style="font-size:120%;" class="{3}" href="#" onclick="og.openLink(\'{1}\');return false;" title="{2}">{0}</a>',
				receiver, og.getUrl('mail', strAction, {id: r.data.object_id}), og.clean(value), classes);
		return name;
	}
	
	function renderFolder(value, p, r) {
		if (r.data.folder != 'undefined')
			return r.data.folder;
		else
			return '';
	}
	
	function renderDate(value, p, r) {
		if (!value) {
			return "";
		}
		return value;
	}
	
	function renderActions(value, p, r) {
		var actions = '';
		var actionStyle= ' style="font-size:105%;padding-top:2px;padding-bottom:3px;padding-left:16px;background-repeat:no-repeat;" '; 
		
		actions += String.format(
			'<a class="list-action ico-reply" href="#" onclick="og.openLink(og.getUrl(\'mail\', \'reply_mail\', {id:{0}}))" title="{1}" ' + actionStyle + '>&nbsp;</a>',
			r.data.object_id, lang('reply mail'));

		actions += String.format(
			'<a class="list-action ico-reply-all" href="#" onclick="og.openLink(og.getUrl(\'mail\', \'reply_mail\', {id:{0}, all:1}))" title="{1}" ' + actionStyle + '>&nbsp;</a>',
			r.data.object_id, lang('reply to all mail'));

		actions += String.format(
			'<a class="list-action ico-forward" href="#" onclick="og.openLink(og.getUrl(\'mail\', \'forward_mail\', {id:{0}}))" title="{1}" ' + actionStyle + '>&nbsp;</a>',
			r.data.object_id, lang('forward mail'));
		
		actions += String.format(
			'<a class="list-action ico-delete" href="#" onclick="og.openLink(og.getUrl(\'mail\', \'delete\', {id:{0}}))" title="{1}" ' + actionStyle + '>&nbsp;</a>',
			r.data.object_id, lang('delete'));
		
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
			og.lastSelectedRow.mails = selections[selections.length-1].data.ix;
			return ret.substring(1);
		}
	}
	this.getSelectedIds = getSelectedIds;
	
	function selectionHasAttachments() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return false;
		} else {
			for (var i=0; i < selections.length; i++) {
				if (selections[i].data.hasAttachment || selections[i].data.conv_hasatt) return true;
			}	
			return false;
		}
	}
	this.selectionHasAttachments = selectionHasAttachments;
	
	function getSelectedReadTypes() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			var read = false;
			var unread = false;
			for (var i=0; i < selections.length; i++) {
				if (selections[i].data.isRead) read = true;
				if (!selections[i].data.isRead) unread = true;
				if (read && unread) return 'all';
			}	
			if (read) return 'read';
			else return 'unread';
		}
	}
	
	
	function getFirstSelectedId() {
		if (sm.hasSelection()) {
			return sm.getSelected().data.object_id;
		}
		return '';
	}
	
	var sm = new Ext.grid.CheckboxSelectionModel();
	sm.on('selectionchange',
		function() {
			if (sm.getCount() <= 0) {
				//actions.tag.setDisabled(true);
				actions.del.setDisabled(true);
				actions.archive.setDisabled(true);
				markactions.markAsRead.setDisabled(true);				
				markactions.markAsUnread.setDisabled(true);
				markactions.markAsSpam.setDisabled(true);				
				markactions.markAsHam.setDisabled(true);
			} else {
				//actions.tag.setDisabled(false);
				actions.del.setDisabled(false);
				actions.archive.setDisabled(false);
				
				markactions.markAsRead.setDisabled(false);
				markactions.markAsUnread.setDisabled(false);
				markactions.markAsSpam.setDisabled(false);				
				markactions.markAsHam.setDisabled(false);
				var selReadTypes = getSelectedReadTypes();
				
				if (selReadTypes == 'read') markactions.markAsRead.setDisabled(true);
				else if (selReadTypes == 'unread') markactions.markAsUnread.setDisabled(true);	
				
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
			id: 'hasAttachment',
			header: '&nbsp;',
			dataIndex: 'hasAttachment',
			width: 24,
        	renderer: renderAttachment,
        	fixed:true,
        	resizable: false,
        	hideable:false,
        	menuDisabled: true
		},{
			id: 'from',
			header: lang("from"),
			dataIndex: 'from',
			width: 120,
			renderer: renderFrom
        },{
			id: 'to',
			header: lang("to"),
			dataIndex: 'to',
			width: 200,
			hidden: true,
			renderer: renderTo
        },{
			id: 'subject',
			header: lang("subject"),
			dataIndex: 'subject',
			width: 250,
			renderer: renderName
        },{
			id: 'account',
			header: lang("account"),
			dataIndex: 'accountName',
			width: 60,
			renderer: renderAccount
        },{
			id: 'date',
			header: lang("date"),
			dataIndex: 'date',
			width: 60,
			renderer: renderDate
        },{
			id: 'folder',
			header: lang("folder"),
			dataIndex: 'folderName',
			width: 60,
			sortable: true,
			hidden: true,
			renderer: renderFolder
        },{
			id: 'actions',
			header: lang("actions"),
			width: 60,
			fixed: true,
			renderer: renderActions,
			sortable: false
		}];
	// custom property columns
	var cps = og.custom_properties_by_type['mail'] ? og.custom_properties_by_type['mail'] : [];
	for (i=0; i<cps.length; i++) {
		cm_info.push({
			id: 'cp_' + cps[i].id,
			hidden: parseInt(cps[i].visible_def) == 0,
			header: cps[i].name,
			align: cps[i].cp_type=='numeric' ? 'right' : 'left',
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
				sortable: true,
				renderer: og.renderDimCol
			});
			og.breadcrumbs_skipped_dimensions[did] = did;
		}
	}
	// create column model
	var cm = new Ext.grid.ColumnModel(cm_info);
	cm.defaultSortable = true;

	moreActions = {};
	
	filterReadUnread = {
		all: new Ext.Action({
			text: lang('view all'),
			handler: function() {
				og.openLink(og.getUrl('object', 'set_user_config_option_value', {config_option_name: 'mails read filter', config_option_value: 'all'}), {preventPanelLoad: true});
				this.reloadFiltering("all", null, null);
				Ext.getCmp('mails-manager').getTopToolbar().items.get('tb-item-read-unread').setText(lang('view by state'))
			},
			scope: this
		}),
		read: new Ext.Action({
			text: lang('read'),
			handler: function() {
				og.openLink(og.getUrl('object', 'set_user_config_option_value', {config_option_name: 'mails read filter', config_option_value: 'read'}), {preventPanelLoad: true});
				this.reloadFiltering("read", null, null);
				Ext.getCmp('mails-manager').getTopToolbar().items.get('tb-item-read-unread').setText(lang('read'))
			},
			scope: this
		}),
		unread: new Ext.Action({
			text: lang('unread'),
			handler: function() {
				og.openLink(og.getUrl('object', 'set_user_config_option_value', {config_option_name: 'mails read filter', config_option_value: 'unread'}), {preventPanelLoad: true});
				this.reloadFiltering("unread", null, null);
				Ext.getCmp('mails-manager').getTopToolbar().items.get('tb-item-read-unread').setText(lang('unread'))
			},
			scope: this
		})
	};
	
	filterClassification = {
		all: new Ext.Action({
			text: lang('view all'),
			handler: function() {
				og.openLink(og.getUrl('object', 'set_user_config_option_value', {config_option_name: 'mails classification filter', config_option_value: 'all'}), {preventPanelLoad: true});
				this.reloadFiltering(null, null, null, 'all');
				Ext.getCmp('mails-manager').getTopToolbar().items.get('tb-item-classification').setText(lang('view by classification'))
			},
			scope: this
		}),
		classified: new Ext.Action({
			text: lang('classified'),
			handler: function() {
				og.openLink(og.getUrl('object', 'set_user_config_option_value', {config_option_name: 'mails classification filter', config_option_value: 'classified'}), {preventPanelLoad: true});
				this.reloadFiltering(null, null, null, "classified");
				Ext.getCmp('mails-manager').getTopToolbar().items.get('tb-item-classification').setText(lang('classified'))
			},
			scope: this
		}),
		unclassified: new Ext.Action({
			text: lang('unclassified'),
			handler: function() {
				og.openLink(og.getUrl('object', 'set_user_config_option_value', {config_option_name: 'mails classification filter', config_option_value: 'unclassified'}), {preventPanelLoad: true});
				this.reloadFiltering(null, null, null, "unclassified");
				Ext.getCmp('mails-manager').getTopToolbar().items.get('tb-item-classification').setText(lang('unclassified'))
			},
			scope: this
		})
	};
	
	filterAccounts = {};
	
	markactions = {
		markAsRead: new Ext.Action({
			text: lang('mark read'),
            tooltip: lang('mark read'),
            iconCls: 'ico-mail-mark-read',
			disabled: true,
			handler: function() {
				var sm = this.getSelectionModel();
				var sel = sm.getSelections();
				var ids = "";
				for (var i=0; i < sel.length; i++) {
					if (ids) ids += ",";
					ids += sel[i].id;
					sel[i].set('isRead', true);
					sel[i].commit();
				}
				if (ids) og.openLink(og.getUrl('object', 'mark_as_read', {ids:ids, dont_remove:1}), {hideLoading:true});
				sm.clearSelections();
			},
			scope: this
		}),
		markAsUnread: new Ext.Action({
			text: lang('mark unread'),
            tooltip: lang('mark unread'),
            iconCls: 'ico-mail-mark-unread',
			disabled: true,
			handler: function() {
				var sm = this.getSelectionModel();
				var sel = sm.getSelections();
				var ids = "";
				for (var i=0; i < sel.length; i++) {
					if (ids) ids += ",";
					ids += sel[i].id;
					sel[i].set('isRead', false);
					sel[i].commit();
				}
				if (ids) og.openLink(og.getUrl('object', 'mark_as_unread', {ids:ids, dont_remove:1}), {hideLoading:true});
				sm.clearSelections();
			},
			scope: this
		}),
		markAsSpam: new Ext.Action({
			text: lang('mark spam'),
            tooltip: lang('mark spam'),
            iconCls: 'ico-mail-mark-spam',
			disabled: true,
			handler: function() {
				var sm = this.getSelectionModel();
				var sel = sm.getSelections();
				var ids = "";
				for (var i=0; i < sel.length; i++) {
					if (ids) ids += ",";
					ids += sel[i].id;
					this.store.remove(sel[i]);
				}
				if (ids) og.openLink(og.getUrl('mail', 'mark_as_spam', {ids:ids}));
				sm.clearSelections();
			},
			scope: this
		}),
		markAsHam: new Ext.Action({
			text: lang('mark ham'),
            tooltip: lang('mark ham'),
            iconCls: 'ico-mail-mark-ham',
			disabled: true,
			hidden: true,
			handler: function() {
				var sm = this.getSelectionModel();
				var sel = sm.getSelections();
				var ids = "";
				for (var i=0; i < sel.length; i++) {
					if (ids) ids += ",";
					ids += sel[i].id;
					this.store.remove(sel[i]);
				}
				if (ids) og.openLink(og.getUrl('mail', 'mark_as_ham', {ids:ids}));
				sm.clearSelections();
			},
			scope: this
		})
	};
	
	accountsMenu = new og.EmailAccountMenu({
		listeners: {
			'accountselect': {
				fn: function(account) {
					var url = og.getUrl('mail', 'edit_account', {id: account});
					og.openLink(url);
				},
				scope: this
			}
		}
	},{},"edit");
	
	//alert(accountsMenu.items.length);
	
	accountActions = {
		addAccount: new Ext.Action({
			text: lang('add mail account'),
			handler: function(e) {
				var url = og.getUrl('mail', 'add_account');
				og.openLink(url);
			}
		}),
		editAccount: new Ext.Action({
			text: lang('edit account'),
            tooltip: lang('edit email account'),
			disabled: false,
			menu: accountsMenu
		})
	};
	
	
	
	selectActions = {
		selectAll: new Ext.Action({
			text: lang('all'),
			handler: function(e) {
				sm.selectAll();
			}
		}),
		selectNone: new Ext.Action({
			text: lang('none'),
			handler: function(e) {
				sm.clearSelections();
			}
		}),
		selectRead: new Ext.Action({
			text: lang('read'),
			handler: function(e) {
				sm.selectAll();
				var selections = sm.getSelections();
				for (var i=0; i < selections.length; i++) {
					if (!selections[i].data.isRead) sm.deselectRow(i, false);
				}	
			}
		}),
		selectUnread: new Ext.Action({
			text: lang('unread'),
			handler: function(e) {
				sm.selectAll();
				var selections = sm.getSelections();
				for (var i=0; i < selections.length; i++) {
					if (selections[i].data.isRead) sm.deselectRow(i, false);
				}	
			}
		}),
		selectClassified: new Ext.Action({
			text: lang('classified'),
			handler: function(e) {
				sm.selectAll();
				var selections = sm.getSelections();
				for (var i=0; i < selections.length; i++) {
					if (!selections[i].data.memberIds.length > 0) sm.deselectRow(i, false);
				}	
			}
		}),
		selectUnclassified: new Ext.Action({
			text: lang('unclassified'),
			handler: function(e) {
				sm.selectAll();
				var selections = sm.getSelections();
				for (var i=0; i < selections.length; i++) {
					if (selections[i].data.memberIds.length > 0) sm.deselectRow(i, false);
				}
			}
		})
	};
	
	actions = {
			
		newCO: new Ext.Action({
			text: lang('new'),
            tooltip: lang('create an email'),
            iconCls: 'ico-new new_button',
            hidden: og.replace_list_new_action && og.replace_list_new_action.mail,
            handler: function() {
            	var url = og.getUrl('mail', 'add_mail');
            	og.openLink(url);
            },
            disabled: (accountsMenu.length)?true:false
		}),
		
		accounts: new Ext.Action({
			text: lang('accounts'),
            tooltip: lang('account options'),
            iconCls: 'ico-administration ico-small16',
			disabled: false,
			menu: {items: [
				accountActions.addAccount,
				accountActions.editAccount
			]}
		}),
		del: new Ext.Action({
			text: lang('move to trash'),
            tooltip: lang('move selected objects to trash'),
            iconCls: 'ico-trash',
			disabled: true,
			handler: function() {
				if (confirm(lang('confirm move to trash'))) {
					var sm = this.getSelectionModel();
					var sel = sm.getSelections();
					var ids = "";
					for (var i=0; i < sel.length; i++) {
						if (ids) ids += ",";
						ids += sel[i].id;
						this.store.remove(sel[i]);
					}
					if (ids) og.openLink(og.getUrl('object', 'trash', {ids:ids}),{callback:function(){Ext.getCmp('mails-manager').load()}});
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
					var sm = this.getSelectionModel();
					var sel = sm.getSelections();
					var ids = "";
					for (var i=0; i < sel.length; i++) {
						if (ids) ids += ",";
						ids += sel[i].id;
						this.store.remove(sel[i]);
					}
					if (ids) og.openLink(og.getUrl('object', 'archive', {ids:ids}));
				}
			},
			scope: this
		}),
		markAs: new Ext.Action({
			text: lang('mark as'),
			tooltip: lang('mark as desc'),
			menu: [
				markactions.markAsRead,
				markactions.markAsUnread,
				markactions.markAsSpam,
				markactions.markAsHam
			]
		}),
		checkMails: new Ext.Action({
			text: lang('check mails'),
			iconCls: 'ico-check_mails',
			handler: function() {
				this.checkmail();
			},
			scope: this
		}),
		sendOutbox: new Ext.Action({
			text: lang('send outbox'),
			tooltip: lang('send outbox title'),
			iconCls: 'ico-sent',
			handler: function() {
				og.msg(lang('success'), lang('sending outbox mails'));
				og.openLink(og.getUrl('mail', 'send_outbox_mails', {}), {hideLoading:1});
			},
			id: 'send_outbox_btn',
			hidden: true,
			scope: this
		}), 
		inbox_email: new Ext.Action({
	        text: lang('inbox'),
	        toggleGroup : 'filter_option',
	        enableToggle: true,
	        pressed: true,
	        id: 'inbox_btn',
	        handler: function(item, event) {
        		if(!item.pressed){
        			item.toggle(true,true);
        		}
			},
	        toggleHandler: function(item, pressed) {
       			if(pressed){
					this.store.removeAll();
       				this.stateType = "received";	
					this.viewType = "all";
					if (obox_btn = Ext.getCmp('send_outbox_btn')) obox_btn.hide();
					markactions.markAsSpam.show();
					markactions.markAsHam.hide();
					//cm.setHidden(cm.getIndexById('from'), false);
					//cm.setHidden(cm.getIndexById('to'), true);
        			this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      classif_type: this.classifType,
				          context: og.contextManager.plainContext(), 
						  account_id: this.accountId
					    };
					this.load({start:0});						
       			}
			},			
			scope: this
    	}),
		sent_email: new Ext.Action({
	        text: lang('sent'),
	        toggleGroup : 'filter_option',
	        enableToggle: true,
	        pressed: false,
	        id: 'sent_btn',
	        handler: function(item, event) {
        		if(!item.pressed){
        			item.toggle(true,true);
        		}
			},
	        toggleHandler: function(item, pressed) {
        		if(pressed){
					this.store.removeAll();
					this.stateType = "sent";
					this.viewType = "all";
					if (obox_btn = Ext.getCmp('send_outbox_btn')) obox_btn.hide();
					markactions.markAsSpam.show();
					markactions.markAsHam.hide();
					//cm.setHidden(cm.getIndexById('from'), true);
					//cm.setHidden(cm.getIndexById('to'), false);
					this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      classif_type: this.classifType,
					      context: og.contextManager.plainContext(), 
						  account_id: this.accountId
					    };
					this.load({start:0});
        		}
			},
			scope: this
    	}),
    	
		draft_email: new Ext.Action({
	        text: lang('draft'),
	        toggleGroup : 'filter_option',
	        enableToggle: true,
	        pressed: false,
	        id: 'draft_btn',
	        handler: function(item, event) {
        		if(!item.pressed){
        			item.toggle(true,true);
        		}
			},
			toggleHandler: function(item, pressed) {
				if(pressed){
					this.store.removeAll();
					this.stateType = "draft";
					this.viewType = "all";
					if (obox_btn = Ext.getCmp('send_outbox_btn')) obox_btn.hide();
					markactions.markAsSpam.show();
					markactions.markAsHam.hide();
					//cm.setHidden(cm.getIndexById('from'), true);
					//cm.setHidden(cm.getIndexById('to'), false);
        			this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      classif_type: this.classifType,
					      context: og.contextManager.plainContext(),

						  account_id: this.accountId
					    };
					this.load({start:0});
        		} 
			},
			scope: this
    	}),
	
		junk_email: new Ext.Action({
	        text: lang('junk'),
	        toggleGroup : 'filter_option',
	        enableToggle: true,
	        pressed: false,
	        id: 'junk_btn',
	        handler: function(item, event) {
        		if(!item.pressed){
        			item.toggle(true,true);
        		}
			},
			toggleHandler: function(item, pressed) {
				if(pressed){
					this.store.removeAll();
					this.stateType = "junk";
					this.viewType = "all";
					if (obox_btn = Ext.getCmp('send_outbox_btn')) obox_btn.hide();
					markactions.markAsSpam.hide();
					markactions.markAsHam.show();
					//cm.setHidden(cm.getIndexById('from'), false);
					//cm.setHidden(cm.getIndexById('to'), true);
        			this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      classif_type: this.classifType,
					      context: og.contextManager.plainContext(), 
						  account_id: this.accountId
					    };
					this.load({start:0});
        		} 
			},
			scope: this
    	}),
	
		out_email: new Ext.Action({
	        text: lang('outbox'),
	        toggleGroup : 'filter_option',
	        enableToggle: true,
	        pressed: false,
	        id: 'outbox_btn',
	        handler: function(item, event) {
        		if(!item.pressed){
        			item.toggle(true,true);
        		}
			},
			toggleHandler: function(item, pressed) {
				if(pressed){
					this.store.removeAll();
					this.stateType = "outbox";
					this.viewType = "all";
					if (obox_btn = Ext.getCmp('send_outbox_btn')) obox_btn.show();
					markactions.markAsSpam.show();
					markactions.markAsHam.hide();
					//cm.setHidden(cm.getIndexById('from'), true);
					//cm.setHidden(cm.getIndexById('to'), false);
        			this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      classif_type: this.classifType,
					      context: og.contextManager.plainContext(),
						  account_id: this.accountId
					    };
					this.load({start:0});
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
		viewReadUnread: new Ext.Action({
			text: this.readType == 'read' ? lang('read') : (this.readType == 'unread' ? lang('unread') : lang('view by state')),
            iconCls: 'ico-mail-mark-read',
			disabled: false,
			id: 'tb-item-read-unread',
			menu: {items: [
				filterReadUnread.all,
				'-',
				filterReadUnread.read,
				filterReadUnread.unread
			]}
		}),
		viewByAccount: new Ext.Action({
			text: this.accountId == 0 ? lang('view by account') : og.emailFilters.accountName,
            iconCls: 'ico-account',
			disabled: false,
			id: 'tb-item-byaccount',
			menu: new og.EmailAccountMenu({
				listeners: {
					'accountselect': {
						fn: function(account, name) {
							og.openLink(og.getUrl('object', 'set_user_config_option_value', {config_option_name: 'mails account filter', config_option_value: account}), {preventPanelLoad: true});
							this.accountId = account;
							this.load();
							if (account == 0) {
								name = lang('view by account');
								this.checkmail(true); // check all account emails because if filter was in a particular account then there are unchecked accounts.
							}
							Ext.getCmp('mails-manager').getTopToolbar().items.get('tb-item-byaccount').setText(name);
						},
						scope: this
					}
				}
			},[{name: lang('view all'), email:'', id: '', separator:true}],"view")
		}),
		viewByClassification: new Ext.Action({
			text: this.classifType == 'classified' ? lang('classified') : (this.classifType == 'unclassified' ? lang('unclassified') : lang('view by classification')),
            iconCls: 'ico-classify',
			disabled: false,
			id: 'tb-item-classification',
			menu: {items: [
				filterClassification.all,
				'-',
				filterClassification.classified,
				filterClassification.unclassified
			]}			
		}),
		select: new Ext.Action({
			text: lang('select'),
            tooltip: lang('select'),
            iconCls: 'ico-select',
			disabled: false,
			menu: {items: [
				selectActions.selectAll,
				selectActions.selectNone,
				'-',
				selectActions.selectRead,
				selectActions.selectUnread,
				'-',
				selectActions.selectClassified,
				selectActions.selectUnclassified
			]}
		})
    };
	
	//show columns for folder
	function showFolderColumns(){
		var allCols = og.MailManager.store.reader.jsonData.folder_columns_all;
		var columns = og.MailManager.store.reader.jsonData.folder_columns;
				
		//hide 
		allCols.forEach(function(entry) {
			if(columns.indexOf(entry) == -1){
				//check if is hidden
				if(!cm.isHidden(cm.getIndexById(entry))){
					cm.setHidden(cm.getIndexById(entry), true);
				}
			}
			
		});
				
		//show only columns from config option for this folder
		columns.forEach(function(entry) {
			cm.setHidden(cm.getIndexById(entry), false);
		});
	}
	
	//save to config option selection of columns
	cm.on('hiddenchange', function(cm,colindex,hidden){
		var allCols = og.MailManager.store.reader.jsonData.folder_columns_all;
		var folderName = og.MailManager.store.reader.jsonData.folder_name;
		var update = false;
		var val = "";
		var columns = og.MailManager.store.reader.jsonData.folder_columns;
		 
		 //if you add a column else you remove a column
		 if(!hidden){
			allCols.forEach(function(entry) {
				if((cm.getIndexById(entry) == colindex)){
					if(columns.indexOf(entry) == -1){  
						if(val != ""){
							val += ",";
						}
						val += entry;
						update = true;
						
						columns.forEach(function(entry2) {
							val += ","+entry2;
						});						
					}					
				}									
			}); 
		 }else{
			 columns.forEach(function(entry) {
					if(!(cm.getIndexById(entry) == colindex)){
						if(val != ""){
							val += ",";
						}
						val += entry;
					}else{
						update = true;
					}
					
				});
		 }
		 		 
		 //update config option 
		 if(update){
			 var url = og.getUrl('account', 'update_user_preference', {name: 'folder_'+folderName+'_columns', value:val});
			 og.openLink(url,{hideLoading:true});
			 og.MailManager.store.load();
		 }
		 
		});
	
	var mas = og.eventManager.addListener("mail account select", function(account) {
		this.accountId = account[0];
		this.load();
		Ext.getCmp('mails-manager').getTopToolbar().items.get('tb-item-byaccount').setText(account[1]);
	}, this);
	
	this.actionRep = actions;

	var top1 = [];
	if (!og.loggedUser.isGuest) {
		if (og.replace_list_new_action && og.replace_list_new_action.mail) {
			top1.push(og.replace_list_new_action.mail);
		}
		top1.push(actions.newCO);
		top1.push('-');
		top1.push(actions.archive);
		top1.push(actions.del);		
		top1.push('-');
	}
	top1.push(actions.markAs);
	if (!og.loggedUser.isGuest) {
		top1.push('-');
		top1.push(actions.checkMails);
		top1.push(actions.accounts);
		top1.push(actions.sendOutbox);
		top1.push('-');
	}
	if (og.additional_mails_top_toolbar_1_items) {
		for ( i=0; i<og.additional_mails_top_toolbar_1_items.length; i++) {
			top1.push(og.additional_mails_top_toolbar_1_items[i]);
		}
	}
	
	this.topTbar1 = new Ext.Toolbar({
		style: 'border:0px none;',
		items: top1
	});
	
	var top2 = [
		actions.select,
		'-',
		actions.inbox_email,
		actions.sent_email,
		actions.draft_email,
		actions.junk_email,
		actions.out_email,
		'-',
		lang('filter')+': ',
		actions.viewReadUnread,
		actions.viewByClassification,
		actions.viewByAccount
	];
	if (og.additional_mails_top_toolbar_2_items) {
		for ( i=0; i<og.additional_mails_top_toolbar_2_items.length; i++) {
			top2.push(og.additional_mails_top_toolbar_2_items[i]);
		}
	}
	
	if (og.additional_list_actions && og.additional_list_actions.mail) {
		for (var i=0; i<og.additional_list_actions.mail.length; i++) {
			top2.push(og.additional_list_actions.mail[i]);
		}
	}
	
	this.topTbar2 = new Ext.Toolbar({
		style: 'border:0px none; padding-top:0px;',
		items: top2
	});
		    
	og.MailManager.superclass.constructor.call(this, {
		store: this.store,
		layout: 'fit',
		cm: cm,
		enableDrag: true,
		ddGroup: 'MemberDD',
		stateful: og.preferences['rememberGUIState'],
		border: false,
		bodyBorder: false,
		stripeRows: true,
		closable: true,
		loadMask: false,
		id: 'mails-manager',
		bbar: new og.CurrentPagingToolbar({
			pageSize: mails_per_page,
			store: this.store,
			displayInfo: true,
			displayMsg: lang('displaying objects of'),
			emptyMsg: lang("no objects to display")
		}),
		viewConfig: {
			forceFit: true
		},
		sm: sm,
		tbar: this.topTbar2,
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
	og.eventManager.addListener('reload mails panel', this.load, this);
	
	function toggleButtons(inb, sent, dra) {
		Ext.getCmp('inbox_btn').toggle(inb);
		Ext.getCmp('sent_btn').toggle(sent);
		Ext.getCmp('draft_btn').toggle(dra);
	}
	
	
	// Send emails in background
	var send_ev = og.eventManager.addListener("must send mails", function(data) {
		og.openLink(og.getUrl('mail', 'send_outbox_mails', {acc_id: data.account}), {hideLoading:1});
	}, this);
	
	var new_mailsent_ev = og.eventManager.addListener("mail sent", function(data) {
		if (this.stateType == "outbox") {
			var view = Ext.getCmp('mails-manager').getView();
	        var sto = og.MailManager.store;
	        var idx = sto.indexOfId(data.id);
	        if (idx == -1) return;
	        var sto_row = sto.getAt(idx);
	        if (sto_row) {
	        	sto.remove(sto_row);
	        }
		}
		og.msg(lang('success'), lang('mail sent msg'), 2);
	}, this);
	
	var mailssent_ev = og.eventManager.addListener("mails sent", function(data) {
		this.load();
	}, this);
	
	// auto refresh emails
	var me = this;
	this.emailRefreshInterval = setInterval(function() {
		me.needRefresh = false;
		me.checkIfNewMails();
	}, 60000);
	/*poll to see if an error has happened while checking mail*/
	if (og.preferences.email_check_acc_errors > 0) {
		this.accountErrCheckInterval = setInterval(function() {
			if (Ext.getCmp('tabs-panel').getActiveTab().id == 'mails-panel') {
				og.openLink(og.getUrl('mail', 'check_account_errors'), {hideLoading:true});
			}
		}, og.preferences.email_check_acc_errors * 1000);
	}
};


Ext.extend(og.MailManager, Ext.grid.GridPanel, {
	load: function(params) {
		var current_context = og.contextManager.plainContext();
		
		// dont reload the list if user was viewing an email and the context has not changed
		if (og.viewing_mail && this.last_context_sent == current_context) {
			og.viewing_mail = false;
			return;
		}
		
		if (!params) params = {};
		var start;
		if (typeof params.start == 'undefined') {
			start = (this.getBottomToolbar().getPageData().activePage - 1) * mails_per_page;
		} else {
			start = isNaN(params.start) ? 0 : params.start;
		}
		
		this.store.baseParams = {
	      read_type: this.readType,
	      view_type: this.viewType,
	      state_type : this.stateType,
	      classif_type: this.classifType,
	      context: og.contextManager.plainContext(),
		  account_id: this.accountId
	    };
		
		// save last context sent to reload the list always if it has changed
		this.last_context_sent = og.contextManager.plainContext();
		
		this.actionRep.checkMails.disable();
		
		// send a random id to the server and save it as the last, if the response has the last check_id then load it, else ignore it.
		this.last_check_id = Ext.id();
		params.check_id = this.last_check_id;

		// disable toolbar actions while reloading
		var bt = this.getBottomToolbar();
		if (bt) bt.disable();
		
		var old_scroll_top = $("#mails-panel .x-grid3-scroller").scrollTop();
		this.store.load({
			params: Ext.apply(params, {
				start: start,
				limit: mails_per_page
			}),
			callback: function() {
				Ext.getCmp('mails-manager').actionRep.checkMails.enable();
				$("#mails-panel .x-grid3-scroller").scrollTop(old_scroll_top);
				
				// disable toolbar actions while reloading
				var bt = Ext.getCmp('mails-manager').getBottomToolbar();
				if (bt) bt.disable();
			}
		});
		this.store.baseParams.action = "";
	},
	
	activate: function() {
		og.mail.removePendingMailsFromList();
		if (this.needRefresh) {
			this.load({start:0});
		}
	},
	
	reset: function() {
		this.load({start:0});
		this.getSelectionModel().clearSelections();
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
	},
	
	getFirstToolbar: function() {
		return this.topTbar1;
	},
	
	checkmail: function(hide_message) {
		this.actionRep.checkMails.disable();
		setTimeout(function() {
			Ext.getCmp("mails-manager").actionRep.checkMails.enable();
		}, 3000);
		var params = {};
		if (!isNaN(this.accountId) && this.accountId > 0) {
			params.account_id = this.accountId;
		}
		if (hide_message) {
			params.hide_message = hide_message;
		}
		og.openLink(og.getUrl('mail', 'checkmail', params), {
			callback: function(success, data) {
				if (data.mails_received > 0) {
					this.checkIfNewMails(false);
				}
			},
			scope: this
		});
	},
	
	checkIfNewMails: function(show_message) {
		if (typeof(show_message) == 'undefined') {
			show_message = true;
		}
		
		// dont check if use has filtered the result
		var text_filter = $("#mails-manager #text_filter").val();
		if(text_filter && text_filter.trim() != ''){
			return;
		}
		
		// use same params of last query
		var params = {
	      read_type: this.readType,
	      view_type: this.viewType,
	      state_type : this.stateType,
	      classif_type: this.classifType,
	      context: og.contextManager.plainContext(),
		  account_id: this.accountId,
		  last_date: this.last_email_date
	    }
		// check if there are new mails
		og.openLink(og.getUrl('mail', 'check_if_new_mails', params), {
			hideLoading: true,
			callback: function(success, data) {
				if (!data) return;
				
				// if context has changed => dont load the response
				var current_context = og.contextManager.plainContext();
				if (current_context != data.context_sent) return;
				
				if (!data.mails || data.mails.length == 0) return;
				
				var man = Ext.getCmp("mails-manager");
				var active_page = man.getBottomToolbar().getPageData().activePage;
				
				if (active_page == 1) {
					// to restore original scroll
					var old_scroll_top = $("#mails-panel .x-grid3-scroller").scrollTop();
					
					// reverse order because they are all inserted in position 0
					var mails = data.mails.reverse();
					
					var records = [];
					for (var x=0; x<mails.length; x++) {
						var obj = mails[x];
						var record = new Ext.data.Record(obj, obj.id);
						records.push(record);
						
						// add record in the first line
						if (og.MailManager.store && typeof(og.MailManager.store.add) == 'function' && og.MailManager.store.data.keys.indexOf(obj.id) == -1) {
							og.MailManager.store.insert(0, record);
						}
						man.last_email_date = obj.rawdate;
					}
					
					// scroll list to original position if user has already scrolled
					if (old_scroll_top > 0) {
						setTimeout(function() {
							var lines_height = 37 * mails.length;
							$("#mails-panel .x-grid3-scroller").scrollTop(old_scroll_top + lines_height);
						}, 50);
					}
				}
				if (show_message) {
					og.msg(lang('information'), lang('you have x new emails', mails.length));
				}
			}
		});
		
	},
	
	reloadFiltering: function(readType, viewType, stateType, classifType) {
		if (readType) this.readType = readType;
		if (viewType) this.viewType = viewType;
		if (stateType) this.stateType = stateType;
		if (classifType) this.classifType = classifType;
		
		this.store.baseParams = {
			read_type: this.readType,
			view_type: this.viewType,
			state_type : this.stateType,
			classif_type : this.classifType
		};
		this.load({start: 0});
	}
});

Ext.reg("mails", og.MailManager);


/************************************************
Container for MailManager, adds a new toolbar
*************************************************/
og.MailManagerPanel = function() {
	this.doNotRemove = true;
	this.needRefresh = false;
	
	this.manager = new og.MailManager();

	og.MailManagerPanel.superclass.constructor.call(this, {
		layout: 'fit',
		border: false,
		bodyBorder: false,
		tbar: this.manager.getFirstToolbar(),
		items: [this.manager],
		closable: true
	});
}

Ext.extend(og.MailManagerPanel, Ext.Panel, {
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

Ext.reg("mails-containerpanel", og.MailManagerPanel);