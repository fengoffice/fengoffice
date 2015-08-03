

og.EmailAccountMenu = function(config, accounts, type) {
	if (!config) config = {};
	og.EmailAccountMenu.superclass.constructor.call(this, Ext.apply(config, {}));
	
	this.addEvents({accountselect: true});
	this.accountnames = {};
	if (accounts) this.addAccounts(accounts);
	
	if (type == 'view') {
		if (og.email_accounts_toview.length){
			this.addAccounts(og.email_accounts_toview);
		}else {
			this.loadAccounts(type);
		}
	} else if (type == 'edit') {
		if (og.email_accounts_toview.length){
			this.addAccounts(og.email_accounts_toedit);
		}else {
			this.loadAccounts(type);
		}
	}
	
	og.eventManager.addListener('mail account added', this.addAccount, this);
	og.eventManager.addListener('mail account deleted', this.removeAccount, this);
	og.eventManager.addListener('mail account edited', this.editAccount, this);
};

Ext.extend(og.EmailAccountMenu, Ext.menu.Menu, {

	editAccount: function(account) {
		var item = this.accountnames[account.id];
		if (item) {
			item.setText(account.name);
		}
	},

	removeAccount: function(account) {
		var item = this.accountnames[account.id];
		if (item) {
			this.remove(item);
		}
	},

	addAccount : function(account) {
		var exists = this.accountnames[account.id];
		if (exists) {
			return;
		};
		var item = new Ext.menu.Item({
			text: og.clean(account.name),
            tooltip: og.clean(account.email),
			handler: function() {
				this.fireEvent('accountselect', account.id, account.name);
			},
			scope: this
		});
		this.addItem(item);
		if (account.separator) this.addSeparator();
		this.accountnames[account.id] = item;
		return item;
	},
	
	exists: function(accountname) {
		return this.accountnames[accountname];
	},
	
	addAccounts: function(accounts) {
		for (var i=0; i < accounts.length; i++) {
			this.addAccount(accounts[i]);
		}
	},

	loadAccounts: function(type) {
		og.openLink(og.getUrl('mail', 'list_accounts', {type: type}),{
			callback: function(success, data) {
				if (success) {
					try {
						var accounts = data.accounts;
						this.addAccounts(accounts);
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