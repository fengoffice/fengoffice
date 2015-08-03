og.UserPicker = function(config) {
	this.GID = 10000000;
	if (!config) config = {};
	var users = config.users;
	delete config.users;
	this.userField = Ext.getDom(config.field) || {};
	if (!this.userField.value) this.userField.value = "";

	Ext.applyIf(config, {
		autoScroll: true,
		rootVisible: false,
		lines: false,
		root: new Ext.tree.TreeNode(lang('users')),	
		collapseFirst: false,
		tbar: [{
			xtype: 'textfield',
			width: config.width ? config.width-6 : 234,
			emptyText:lang('filter users and groups'),
			listeners: {
				render: {
					fn: function(f) {
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
	
	og.UserPicker.superclass.constructor.call(this, config);

	this.users = this.root.appendChild(
		new Ext.tree.TreeNode({
			id: "users",
			text: lang('users'),
			expanded: true,
			name: lang('users'),
			iconCls: 'ico-users',
			listeners: {
				click: function() {
					//this.unselect();
					this.select();
				}
			}
		})
	);
	this.users.user = {id: 0, n: lang('users')};
	this.groups = this.root.appendChild(
		new Ext.tree.TreeNode({
			id: "groups",
			text: lang('groups'),
			expanded: true,
			name: lang('groups'),
			iconCls: 'ico-groups',
			listeners: {
				click: function() {
					//this.unselect();
					this.select();
				}
			}
		})
	);
	this.groups.user = {id: 0, n: lang('groups')};
	
	if (users) this.addUsers(users);
	var ids = this.userField.value.split(",");
	for (var i=0; i < ids.length; i++) {
		var n = ids[i].trim();
		var node = this.getNodeById(n);
		if (node) {
			node.ensureVisible();
			node.suspendEvents();
			node.ui.toggleCheck(true);
			node.user.checked = true;
			node.resumeEvents();
		}
	}
	var ownerCo = this.getNode("c1");
	if (ownerCo) ownerCo.expand();

	this.getSelectionModel().on({
		'selectionchange' : function(sm, node) {
			if (node && !this.pauseEvents && node.user && node.user.t == 'user' || node.user.t == 'group') {
				this.fireEvent("userselect", node.user);
				this.clearFilter();
				node.expand();
				node.ensureVisible();
			} else if (node && !this.pauseEvents && node.user && (node.user.t != 'user' && node.user.t != 'group')) {
				this.fireEvent("noneselected", node.user);
			}
		},
		scope:this
	});
	
	this.addEvents({userselect: true});
	this.addEvents({noneselected: true});
};

Ext.extend(og.UserPicker, Ext.tree.TreePanel, {
	removeUser: function(user) {
		var node = this.getNodeById(this.nodeId(user.id));
		if (node) {
			if (node.isSelected()) {
				if (user.id < this.GID) {
					this.users.select();
				} else {
					this.groups.select();
				}
			}
			Ext.fly(node.ui.elNode).ghost('l', {
				callback: node.remove, scope: node, duration: .4
			});
		}
	},
	
	updateUser : function(user) {
		this.addUser(user);
	},

	addUser : function(user) {
		var nid = this.nodeId(user.id);
		var exists = this.getNodeById(nid);
		if (exists) {
			exists.setText(user.n);
			if (user.p != exists.user.p) {
				var selected = exists.isSelected();
				var parent = this.getNode(user.p);
				if (parent) {
					parent.appendChild(exists);
					exists.user.parent = parent.user.id;
					if (selected) exists.select();
				}
			}
			return;
		}
		var config = {
			text: og.clean(user.n),
			id: nid,
			listeners: {
				click: function() {
					//this.unselect();
					this.select();
				},
				checkchange: {
					fn: function(node, checkedValue) {
						if (!node) return;
						node.user.checked = checkedValue;
						node.select();
						if (this.userField) {
							var tids = this.userField.value.split(",");
							var ids = [];
							for (var i=0,j=0; i < tids.length; i++) {
								var x = tids[i].trim();
								if (x && x != node.user.id) {
									ids.push(x);
								}
							} 
							if (checkedValue) {
								ids.push(node.user.id);
							}
							this.userField.value = ids.join(",");
						}
						this.fireEvent("usercheck", node.user, checkedValue);
					},
					scope: this
				}
			}
		};
		if (user.t != 'company') {
			config.checked = false;
			config.cls = 'x-tree-noicon';
		} else {
			config.iconCls = 'ico-company';
		}
		var node = new Ext.tree.TreeNode(config);
		node.user = user;
		node.user.checked = false;
		if (user.p == 'users') {
			var parent = this.users;
		} else if (user.p == 'groups') {
			var parent = this.groups;
		} else {
			var parent = this.getNodeById(this.nodeId(user.p));
		}
		if (!parent) {
			if (user.t == 'company' || user.id < this.GID) {
				parent = this.users;
			} else {
				parent = this.groups;
			}
		}
		var iter = parent.firstChild;
		while (iter && node.text.toLowerCase() > iter.text.toLowerCase()) {
			iter = iter.nextSibling;
		}
		parent.insertBefore(node, iter);
		return node;
	},
	
	uncheckAll: function(node) {
		if (!node) node = this.root;
		var child = node.firstChild;
		if (node.ui.isChecked()) node.ui.toggleCheck(false);
		while (child) {
			this.uncheckAll(child);
			child = child.nextSibling;
		}
	},
	
	getSelectedUser: function() {
		var s = this.getSelectionModel().getSelectedNode();
		if (s) {
			return this.getSelectionModel().getSelectedNode().user;
		} else {
			return {id: 0, name: 'users'};
		}
	},
	
	getSelected: function() {
		return this.getSelectedUser();
	},
	
	nodeId: function(id) {
		return "u" + id;
	},
	
	addUsers: function(users) {
		for (var i=0; i < users.length; i++) {
			this.addUser(users[i]);
		}
		this.root.expand();
	},
	
	select: function(id) {
		if (!id) {
			this.root.ensureVisible();
			this.root.select();
		} else {
			var node = this.getNodeById(this.nodeId(id));
			if (node) {
				node.ensureVisible();
				node.select();
			}
		}
	},
	
	getNode: function(id) {
		if (!id) {
			return this.root;
		} else {
			var node = this.getNodeById(this.nodeId(id));
			if (node) {
				return node;
			}
		}
		return null;
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
			n.previousState = n.expanded ? "expanded" : "collapsed";
		}
		if (f) {
			n.getUI().show();
		} else {
			n.getUI().hide();
		}
		return f;
	},
	
	filterTree: function(text) {
		var re = new RegExp(Ext.escapeRe(text.toLowerCase()), 'i');
		this.filterNode(this.root, re);
		this.root.getUI().show();
		this.expandAll();
	},
	
	clearFilter: function(n) {
		if (!n) n = this.root;
		if (!n.previousState) return;
		var c = n.firstChild;
		while (c) {
			this.clearFilter(c);
			c = c.nextSibling;
		}
		n.getUI().show();
		if (this.getSelectionModel().getSelectedNode().isAncestor(n)) {
			n.previousState = "expanded";
		}
		if (n.previousState == "expanded") {
			n.expand();
		} else if (n.previousState == "collapsed") {
			n.collapse();
		}
		n.previousState = null;
	},
	
	getValue: function() {
		return this.userField.value;
	}
});

Ext.reg('userpicker', og.UserPicker);




