og.LoginDialog = function(config) {
	if (!config) config = {};
		
	og.LoginDialog.superclass.constructor.call(this, Ext.apply(config, {
		id: 'og-login-dialog',
		modal: true,
		layout: 'form',
		closeAction: 'hide',
		iconCls: 'op-ico',
		title: lang('login'),
		labelWidth: 75,
        bodyStyle: 'padding:5px 5px 0',
        width: 250,
        defaults: {width: 130},
        defaultType: 'textfield',
        items: [{
        		xtype: 'label',
        		style: 'color: red',
        		width: 240,
        		hideLabel: true,
        		text: lang('login dialog desc')
        	},{
        		id: 'username',
                fieldLabel: lang('username'),
                name: 'username',
                //allowBlank: false,
                listeners: {
                	specialkey: {
                		fn: function(field, e) {
                			if (e.getKey() == 13) {
                				this.submit();
                			}
                		},
                		scope: this
                	}
                }
            },{
            	id: 'password',
            	inputType: 'password',
                fieldLabel: lang('password'),
                name: 'password',
                //allowBlank: false,
                listeners: {
                	specialkey: {
                		fn: function(field, e) {
                			if (e.getKey() == 13) {
                				this.submit();
                			}
                		},
                		scope: this
                	}
                }
            },{
            	id: 'remember',
            	xtype: 'checkbox',
                fieldLabel: '',
                labelSeparator: '',
                boxLabel: lang('remember me'),
                name: 'remember'
            }
        ],
        buttons: [{
            text: lang('login'),
            handler: this.submit,
            scope: this
        },{
            text: lang('cancel'),
            handler: function() {
            	this.hide()
            },
            scope: this
        }]
	}));
}

Ext.extend(og.LoginDialog, Ext.Window, {
	submit: function() {
		if (this.findById('username').getValue() == og.loggedUser.username) {
			og.openLink(og.getUrl('access', 'relogin'), {
				post: {
					"login[username]": this.findById('username').getValue(),
					"login[password]": this.findById('password').getValue(),
					"login[remember]": this.findById('remember').getValue()
				},
				callback: function(success, data) {
					if (success && data.errorCode == 0) {
						this.hide();
						if (this.params.url) {
							og.openLink(this.params.url, this.params.options);
						}
					}
				},
				scope: this
			});
		} else {
			var form = document.createElement('form');
			form.action = og.getUrl('access', 'login');
			form.method = 'POST';
			var un = document.createElement('input');
			un.name = 'login[username]';
			un.type = 'hidden';
			un.value = this.findById('username').getValue();
			var pw = document.createElement('input');
			pw.name = 'login[password]';
			pw.type = 'hidden';
			pw.value = this.findById('password').getValue();
			var rm = document.createElement('input');
			rm.name = 'login[remember]';
			rm.type = 'hidden';
			rm.value = this.findById('username').getValue() ? 'checked' : '';
			form.appendChild(un);
			form.appendChild(pw);
			form.appendChild(rm);
			document.body.appendChild(form);
			form.submit();
		}
	}
});

og.LoginDialog.show = function(url, options) {
	if (!this.dialog) {
		this.dialog = new og.LoginDialog();
		this.dialog.on('show', function() {
			this.findById('username').focus();
		}, this.dialog);
	}
	this.dialog.findById('username').setValue(og.loggedUser.username);
	this.dialog.findById('password').setValue('');
	this.dialog.params = {url: url, options: options};
	this.dialog.on('hide', og.restoreFlashObjects);
	this.dialog.on('close', og.restoreFlashObjects);
	og.hideFlashObjects();
	this.dialog.show();
}