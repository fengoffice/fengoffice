og.WorkspacePanel = function(config) {
	if (!config) config = {};
	if (!config.wstree)  config.wstree = {};
	config.wstree.xtype = 'wstree';
	
	var tbar = [];
	if (!og.loggedUser.isGuest) {
		tbar.push({
			iconCls: 'ico-workspace-add',
			tooltip: lang('create a workspace'),
			handler: function() {
				this.tree.newWS();
			},
			scope: this
		});
		tbar.push({
			id: 'edit',
			iconCls: 'ico-workspace-edit',
			tooltip: lang('edit workspace'),
			disabled: true,
			handler: function() {
				this.tree.editWS();
			},
			scope: this
		});
	}
	tbar.push({
		iconCls: 'ico-workspace-refresh',
		tooltip: lang('refresh desc'),
		handler: function() {
			this.tree.loadWorkspaces(null,null,true);
			og.updateWsCrumbs({id: 0, name: lang('all')});
		},
		scope: this
	});
	
	Ext.applyIf(config, {
		iconCls: 'ico-workspaces',
		region: 'center',
		minSize: 200,
		layout: 'fit',
		border: false,
		style: 'border-bottom-width: 1px',
		bodyBorder: false,
		items: [config.wstree],
		tbar: tbar
	});
	
	og.WorkspacePanel.superclass.constructor.call(this, config);
	
	this.tree = this.findById('workspace-panel');
	
	this.tree.getSelectionModel().on({
		'selectionchange' : function(sm, node) {
			var edit = this.getTopToolbar().items.get('edit');
			if (edit) edit.setDisabled(!node || node == this.tree.workspaces);
		},
		scope:this
	});
};

Ext.extend(og.WorkspacePanel, Ext.Panel,{});

og.WorkspaceTree = function(config) {
	if (!config) config = {};
	var workspaces = config.workspaces;
	delete config.workspaces;
	var id = config.id || 'workspace-panel';
	Ext.applyIf(config, {
		ddGroup: 'WorkspaceDD',
		ddAppendOnly: true,
		enableDrop: true,
		autoScroll: true,
		autoLoadWorkspaces: false,
		border: false,
		bodyBorder: false,
		id: id,
		rootVisible: false,
		lines: false,
		root: new Ext.tree.TreeNode(lang('workspaces')),
		collapseFirst: false,
		selectedWorkspaceId: og.initialWorkspace,
		tbar: [{
			xtype: 'textfield',
			id: id + 'filter',
			width: 200,
			emptyText:lang('filter workspaces'),
			listeners:{
				render: {
					fn: function(f){
						f.el.on('keyup', function(e) {
							this.filterTree(e.target.value);
						},
						this, {buffer: 350});
					},
					scope: this
				}
			}
		}]
	});
	if (!config.listeners) config.listeners = {};
	Ext.apply(config.listeners, {
		beforenodedrop: function(e) {
			if (e.data.grid) {
				if (e.target.id == 'trash') {
					e.data.grid.trashObjects();
				} else if (e.target.id == 'archived') {
					e.data.grid.archiveObjects();
				} else {
					e.data.grid.moveObjects(e.target.ws.id);
				}
			}
			return false;
		}
    });
	og.WorkspaceTree.superclass.constructor.call(this, config);

	this.workspaces = this.root.appendChild(
		new Ext.tree.TreeNode({
			id: "ws0",
			text: (config.allowNone? lang('none'):lang('all')),
			expanded: true,
			name: (config.allowNone? lang('none'):lang('all')),
			listeners: {
				click: function() {
					this.unselect();
					this.select();
				}
			}
		})
	);
	this.workspaces.ws = {id: 0, name: (config.allowNone? lang('none'):lang('all'))};
	this.previousNode = this.workspaces;
	
	if (!config.isInternalSelector)
	{
		this.getSelectionModel().on({
			'selectionchange' : function(sm, node) {
				if (node && !this.pauseEvents) {
					/*if (node.id == 'trash'){
						this.pauseEvents = true;
						this.previousNode.select();
						this.pauseEvents = false;
						var cp = Ext.getCmp('trash-panel');
						var tp = Ext.getCmp('tabs-panel');
						if (!cp){
							cp = new og.ContentPanel({
								closable: true,
								title: lang('trash'),
								id: 'trash-panel',
								iconCls: 'ico-trash',
								refreshOnWorkspaceChange: true,
								refreshOnTagChange: true,
								defaultContent: {
									type: "url",
									data: og.getUrl('object', 'init_trash')
								}
							});
							tp.add(cp);
						}
						tp.setActiveTab(cp);
					} else if (node.id == 'archived') {
						this.pauseEvents = true;
						this.previousNode.select();
						this.pauseEvents = false;
						var cp = Ext.getCmp('archivedobjs-panel');
						var tp = Ext.getCmp('tabs-panel');
						if (!cp){
							cp = new og.ContentPanel({
								closable: true,
								title: lang('archived objects'),
								id: 'archivedobjs-panel',
								iconCls: 'ico-archive-obj',
								refreshOnWorkspaceChange: true,
								refreshOnTagChange: true,
								defaultContent: {
									type: "url",
									data: og.getUrl('object', 'init_archivedobjs')
								}
							});
							tp.add(cp);
						}
						tp.setActiveTab(cp);*/
					//} else {
						this.fireEvent("workspaceselect", node.ws);
						var tf = this.getTopToolbar().items.get(this.id + 'filter');
						tf.setValue("");
						this.clearFilter();
						node.expand(false, false);
						node.ensureVisible();
						this.previousNode = node;
					//}
				}
			},
			scope:this
		});
		this.addEvents({workspaceselect: true});
	
		og.eventManager.addListener('workspace added', this.addWS, this);
		og.eventManager.addListener('workspace edited', this.updateWS, this);
		og.eventManager.addListener('workspace deleted', this.removeWS, this);
	} else {
		this.getSelectionModel().on({
			'selectionchange' : function(sm, node) {
				if (node && (node.ws.id != 0 || this.initialConfig.allowNone) && !this.pauseEvents) {
					og.WorkspaceSelected(this.initialConfig.controlName, node.ws);
				}
			},
			scope:this
		});
		this.addEvents({workspaceselect: true});
	}
		
	if (config.selectedWorkspaceId) {
		this.initialWorkspaceId = config.selectedWorkspaceId;
	}
	if (workspaces) {
		this.addWorkspaces(workspaces);
		if (config.selectedWorkspaceId) {
			this.pauseEvents = true;
			this.select(config.selectedWorkspaceId);
			this.pauseEvents = false;
		}
	} else if (this.autoLoadWorkspaces) {
		this.loadWorkspaces(null,null,true);
	}
};

Ext.extend(og.WorkspaceTree, Ext.tree.TreePanel, {

	newWS: function() {
		og.openLink(og.getUrl('project', 'add'), {caller:'project'});
	},
	
	delWS: function() {
		og.openLink(og.getUrl('project', 'delete', {id: this.getActiveWorkspace().id}), {caller:'project'});
	},
	
	editWS: function() {
		og.openLink(og.getUrl('project', 'edit', {id: this.getActiveWorkspace().id}), {caller:'project'});
	}, 

	removeWS: function(ws) {
		var node = this.getNode(ws.id);
		if (node) {
			if (node.isSelected()) {
				this.workspaces.select();
			}
			if (node.ui && node.ui.elNode) {
				Ext.fly(node.ui.elNode).ghost('l', {
					callback: node.remove, scope: node, duration: .4
				});
			}
		}
	},
	
	updateWS : function(ws) {
		this.addWS(ws);
		og.updateWsCrumbs(ws);
	},

	addWS : function(ws) {
		var exists = this.getNode(ws.id);
		if (exists) {
			if (ws.id == 0) return;
			exists.setText(og.clean(ws.name));
			var ico = exists.getUI().getIconEl();
			if (ico) ico.className = ico.className.replace(/ico-color([0-9]*)/ig, 'ico-color' + (ws.color || 0));
			if (ws.parent != exists.ws.parent || ws.name != exists.ws.name) {
				var selected = exists.isSelected();
				var parent = this.getNode(ws.parent);
				exists.ws.parent = parent.ws.id;
				this.insertIntoTree(exists);
				if (selected) exists.select();
			}
			exists.ws = ws;
			return;
		}
		var config = {
			iconCls: 'ico-color' + (ws.color || 0),
			text: og.clean(ws.name),
			id: 'ws' + ws.id,
			listeners: {
				click: function() {
					this.unselect();
					this.select();
				}
			}
		};
		var node = new Ext.tree.TreeNode(config);
		node.ws = ws;
		if (ws.isPersonal)
			this.personalNode = node;
		
		this.insertIntoTree(node);

		/*Ext.fly(node.ui.elNode).slideIn('l', {
			callback: Ext.emptyFn, scope: this, duration: .4
		});*/
		return node;
	},
	
	insertIntoTree : function(node){
		if (node.ws.parent == "root") {
			this.root.insertBefore(node, this.root.firstChild);
		} else {
			var parent = this.getNode(node.ws.parent);
			if (!parent) parent = this.workspaces;
			var iter = parent.firstChild;
			while (iter && iter.ws /* <-not trash*/ && (node.ws.id == iter.ws.id || (node.text.toLowerCase() > iter.text.toLowerCase()))) {
				iter = iter.nextSibling;
			}
			parent.insertBefore(node, iter);
		}
	},
	
	/*addTrash: function(){
		var exists = this.getNodeById('trash');
		if (exists)	return;
		var config = {
			iconCls: 'ico-trash',
			text: lang('trash'),
			id: 'trash',
			listeners: {
				click: function() {
					this.unselect();
					this.select();
				}
			}
		};
		var node = new Ext.tree.TreeNode(config);
		var parent = this.workspaces;
		var iter = parent.firstChild;
		while (iter) {
			iter = iter.nextSibling;
		}
		parent.insertBefore(node, iter);
		return node;
	},*/
	
	/*addArchived: function(){
		var exists = this.getNodeById('archived');
		if (exists)	return;
		var config = {
			iconCls: 'ico-archive-obj',
			text: lang('archived objects'),
			id: 'archived',
			listeners: {
				click: function() {
					this.unselect();
					this.select();
				}
			}
		};
		var node = new Ext.tree.TreeNode(config);
		var parent = this.workspaces;
		var iter = parent.firstChild;
		while (iter) {
			iter = iter.nextSibling;
		}
		parent.insertBefore(node, iter);
		return node;
	},*/
	
	getActiveWorkspace: function() {
		var s = this.getSelectionModel().getSelectedNode();
		if (s && s.id != 'trash' && s.id != 'archived') {
			return this.getSelectionModel().getSelectedNode().ws;
		} else {
			return {id: 0, name: 'all'};
		}
	},
	
	getActiveOrPersonalWorkspace: function() {
		var s = this.getSelectionModel().getSelectedNode();
		if (s && s.id != 'trash' && s.id != 'archived' && s.ws.id != 0) {
			return s.ws;
		} else {
			if (this.personalNode)
				return this.personalNode.ws;
			else
				return {id: 0, name: 'all'};
		}
	},
	
	loadWorkspaces: function(node, showWsDiv, isInitial) {
		if (!node) node = this.workspaces;
		if (this.loadWorkspacesFrom) {
			this.removeAll();
			var ws = Ext.getCmp(this.loadWorkspacesFrom).getWsList(0, true);
			this.addWorkspaces(ws);
			this.workspaces.expand();
		} else {
			if (isInitial){
				for (var i = 0; i < node.childNodes.length; i++){
					node.childNodes[i].remove();
					i--;
				}
			}
			var action = 'list_projects';
			if (isInitial)
				action = 'initial_list_projects';
			og.openLink(og.getUrl('project', action, {parent: node.ws.id}), {
				callback: function(success, data, showWsDiv) {
					if (success) {
						// remove deleted nodes
						var ch = node.firstChild;
						while (ch) {
							var exists = false;
							for (var i=0; i < data.workspaces.length; i++) {
								if (ch.ws.id == data.workspaces[i].id) {
									exists = true;
								}
							}
							if (!exists) {
								ch.remove();
							}
							ch = ch.nextSibling;
						}
						
						var workspacesToAdd = new Array();
						if (isInitial)
						{
							//Set order of elements to add to the workspace list. Parents should be added first
	
							var continueOrdering = true;
							while(continueOrdering)
							{
								continueOrdering = false;
								for (var i = 0; i < data.workspaces.length; i++){
									var add = false;
									var ws = data.workspaces[i];
									if (ws.parent == 0)
										add = true;
									else for (var j = 0; j < workspacesToAdd.length; j++)
										if (workspacesToAdd[j].id == ws.parent){
											add = true;
											break;
										}
									if (add){
										continueOrdering = true;
										workspacesToAdd[workspacesToAdd.length] = data.workspaces.splice(i,1)[0];
										i--;
									}
								}
							}
						} else 
							workspacesToAdd = data.workspaces;
	
						this.addWorkspaces(workspacesToAdd);
						if (isInitial)
							this.workspaces.expand();
											
						if (!this.getSelectionModel().getSelectedNode()) {
							this.pauseEvents = true;
							this.workspaces.select();
							this.pauseEvents = false;
						}
						if (isInitial && this.initialWorkspaceId) {
							this.pauseEvents = true;
							this.select(this.initialWorkspaceId);
							this.pauseEvents = false;
							og.updateWsCrumbs(this.getActiveWorkspace());
						}
						//this.addArchived();
						//this.addTrash();						
					}
				},
				scope: this
			});
		}
	},
	
	addWorkspaces: function(workspaces) {
		for (var i=0; i < workspaces.length; i++) {
			this.addWS(workspaces[i]);
		}
	},
	
	select: function(id) {
		if (!id) {
			this.workspaces.ensureVisible();
			this.workspaces.select();
		} else {
			var node = this.getNode(id);
			if (node) {
				node.ensureVisible();
				node.select();
			}
		}
	},
	
	getNode: function(id) {
		if (!id) {
			return this.workspaces;
		} else {
			var node = this.getNodeById('ws' + id);
			if (node) {
				return node;
			}
		}
		return null;
	},
	
	removeAll: function() {
		var node = this.workspaces.firstChild;
		while (node) {
			var aux = node;
			node = node.nextSibling;
			aux.remove();
		}
	},
	
	filterNode: function(n, re) {
		var f = false;
		var c = n.firstChild;
		while (c) {
			f = this.filterNode(c, re) || f;
			c = c.nextSibling;
		}
		f = re.test(n.text.toLowerCase()) || f;
		if (!n.previousState) {
			// save the state before filtering
			n.previousState = n.expanded ? "e" :"c";
		}
		if (f) {
			n.getUI().show();
		} else {
			n.getUI().hide();
		}
		return f;
	},
	
	filterTree: function(text) {
		if (text == this.getTopToolbar().items.get(this.id + 'filter').emptyText) {
			text = "";
		}
		if (text.trim() == '') {
			this.clearFilter();
		} else {
			var re = new RegExp(Ext.escapeRe(text.toLowerCase()), 'i');
			this.filterNode(this.workspaces, re);
			this.workspaces.getUI().show();
			this.workspaces.expand(true, false);
			//this.expandAll();
		}
	},
	
	clearFilter: function(n) {
		if (!n) n = this.workspaces;
		if (!n.previousState) return;
		var c = n.firstChild;
		while (c) {
			this.clearFilter(c);
			c = c.nextSibling;
		}
		n.getUI().show();
		if (n.previousState == "e") {
			n.expand(false, false);
		} else if (n.previousState == "c") {
			n.collapse(false, false);
		}
		n.previousState = null;
	},
	
	getWsList: function(wsId, noRoot){
		var start = this.workspaces;
		if (wsId){
			start = this.getNode(wsId);
		}
		var list = [];
		start.cascade(function (list){
			if (this.id != 'trash' && this.id != 'archived' && (!noRoot || this.ws.id != wsId)) {
				list[list.length] = this.ws;
			}
		}, null, [list]);
		return list;
	},
	
	isSubWorkspace: function(sub, ws) {
		var n = this.getNode(ws);
		if (!n) return false;
		var c = n.firstChild;
		while (c) {
			if (c.ws.id == sub || this.isSubWorkspace(sub, c.ws.id)) return true;
			c = c.nextSibling;
		}
		return false;
	}
});

Ext.reg('wspanel', og.WorkspacePanel);
Ext.reg('wstree', og.WorkspaceTree);
