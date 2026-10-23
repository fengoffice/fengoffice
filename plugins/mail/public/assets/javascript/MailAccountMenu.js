
og.EmailAccountMenu = function(config, accounts, type) {
	if (!config) config = {};
	og.EmailAccountMenu.superclass.constructor.call(this, Ext.apply(config, {}));
	
	this.addEvents({accountselect: true});
	this.accountnames = {};
	this.menuType = type;
	if (accounts) this.addAccounts(accounts);
	
	if (type == 'view' || type == 'checkboxes') {
		if (og.email_accounts_toview.length){
			this.addAccounts(og.email_accounts_toview, type);
		}else {
			this.loadAccounts(type);
		}
	} else if (type == 'edit') {
		if (og.email_accounts_toedit.length){
			this.addAccounts(og.email_accounts_toedit, type);
		}else {
			this.loadAccounts(type);
		}
	}
	
	og.eventManager.addListener('mail account added', this.addAccount, this);
	og.eventManager.addListener('mail account deleted', this.removeAccount, this);
	og.eventManager.addListener('mail account edited', this.editAccount, this);
};

Ext.extend(og.EmailAccountMenu, Ext.menu.Menu, {

	compareAccountLabels: function(a, b) {
		var nameA = (a || '').toString();
		var nameB = (b || '').toString();
		return nameA.localeCompare(nameB, undefined, {sensitivity: 'base'});
	},

	getAccountSortKey: function(account) {
		return ((account && (account.name || account.email)) || '').toString();
	},

	isSortableAccountItem: function(item) {
		if (!item || item.hidden || item.isSeparator) {
			return false;
		}
		// Keep special rows (e.g. "view all") pinned above real accounts
		if (typeof item.accountId === 'undefined' || item.accountId === '' || item.accountId === 0 || item.accountId === '0') {
			return false;
		}
		return true;
	},

	findInsertIndexForLabel: function(label) {
		var items = this.items;
		if (!items || !items.getCount) {
			return 0;
		}
		var insertAt = items.getCount();
		var sawAccount = false;
		for (var i = 0; i < items.getCount(); i++) {
			var existing = items.get(i);
			if (!this.isSortableAccountItem(existing)) {
				if (!sawAccount) {
					insertAt = i + 1; // after pinned header/separator rows
				}
				continue;
			}
			sawAccount = true;
			if (this.compareAccountLabels(label, existing.text) < 0) {
				return i;
			}
			insertAt = i + 1;
		}
		return insertAt;
	},

	editAccount: function(account) {
		var item = this.accountnames[account.id];
		if (!item) {
			return;
		}
		var type = (item instanceof Ext.menu.CheckItem) ? 'checkboxes' : this.menuType;
		this.remove(item);
		delete this.accountnames[account.id];
		this.addAccount(account, type);
	},

	removeAccount: function(account) {
		var item = this.accountnames[account.id];
		if (item) {
			this.remove(item);
			delete this.accountnames[account.id];
		}
	},

	addAccount : function(account, type) {
		var exists = this.accountnames[account.id];
		if (exists) {
			return;
		};
		if (type == null || type === undefined) {
			type = this.menuType;
		}
		var item_config = {
			text: og.clean(account.name),
            tooltip: og.clean(account.email),
            checked: account.selected,
            hideOnClick: type != 'checkboxes',
			accountId: account.id,
            handler: function() {
            	this.fireEvent('accountselect', account.id, account.name);
			},
			scope: this
		}
		
		var item = type=='checkboxes' ? new Ext.menu.CheckItem(item_config) : new Ext.menu.Item(item_config);
		
		var index = this.findInsertIndexForLabel(this.getAccountSortKey(account));
		this.insert(index, item);
		if (account.separator) this.addSeparator();
		this.accountnames[account.id] = item;
		return item;
	},
	
	exists: function(accountname) {
		return this.accountnames[accountname];
	},
	
	addAccounts: function(accounts, type) {
		if (accounts && accounts.length) {
			accounts = accounts.slice().sort(function(a, b) {
				var nameA = (a.name || a.email || '').toString();
				var nameB = (b.name || b.email || '').toString();
				return nameA.localeCompare(nameB, undefined, {sensitivity: 'base'});
			});
			for (var i=0; i < accounts.length; i++) {
				this.addAccount(accounts[i], type);
			}
		}
	},

	loadAccounts: function(type) {
		og.openLink(og.getUrl('mail', 'list_accounts', {type: type}),{
			callback: function(success, data) {
				if (success) {
					try {
						var accounts = data.accounts;
						this.addAccounts(accounts, type);
					} catch (e) {
						og.err(e.message);
						throw e;
					}
				}else{
					alert("error - MailAccountMenu.js - LINE 76");
				}
			},
			scope: this
		});
	}
});
