og.ExtendedDialog = function(config) {
	if (config.html) {
		var panel = new og.HtmlPanel({
			html: config.html
		});
	} else {
		var panel = new Ext.FormPanel({
			frame: false,
			url: '',
			labelWidth: config.labelWidth,
			bodyStyle: 'padding:20px 20px 0',
			border: false,
			bodyBorder: false,
			items: config.dialogItems
		});
	}	
	config.stateful = false;
	if (!config.genid) config.genid = Ext.id();
	og.ExtendedDialog.superclass.constructor.call(this, Ext.applyIf(config, {
		y: 50,
		id: config.genid + 'dialog',
		layout: 'fit',
		modal: true,
		height: 300,
		width: 450,
		resizable: false,
		closeAction: 'hide',
		iconCls: config.iconCls ? config.iconCls : 'op-ico',
		border: false,
		buttons: [{
			text: (config.YESNO ? lang('yes') : lang('ok')),
			handler: this.accept,
			hidden: config.noOkBtn,
			id: config.genid + 'ok_button',
			cls: config.okBtnCls ? config.okBtnCls : '',
			scope: this
		},{
			text: (config.YESNO ? lang('no') : lang('cancel')),
			handler: this.cancel,
			hidden: config.noCancel,
			id: config.genid + 'cancel_button',
			cls: config.cancelBtnCls ? config.cancelBtnCls : '',
			scope: this
		}],
		items: [
			panel
		]
	}));
}

Ext.extend(og.ExtendedDialog, Ext.Window, {
	accept: function() { this.hide(); },
	cancel: function() { this.hide(); }
});


og.ExtendedDialog.show = function(config) {
	if (!config)
		config = {};
	if (!config.genid)
		config.genid = 'Extended';
	if (this.dialog)
		this.dialog.destroy();
	this.dialog = new og.ExtendedDialog(config);
	
	if (config.ok_fn) {
		Ext.getCmp(config.genid + 'ok_button').setHandler(config.ok_fn);
	}
	if (config.cancel_fn) {
		Ext.getCmp(config.genid + 'cancel_button').setHandler(config.cancel_fn);
	}
	
	this.dialog.purgeListeners();
	this.dialog.on('hide', og.restoreFlashObjects);
	this.dialog.on('close', og.restoreFlashObjects);
	og.hideFlashObjects();
	this.dialog.show();
	var pos = this.dialog.getPosition();
	if (pos[0] < 0) pos[0] = 0;
	if (pos[1] < 0) pos[1] = 0;
	this.dialog.setPosition(pos[0], pos[1]);
	
	return this.dialog;
}

og.ExtendedDialog.hide = function() {
	if (this.dialog) this.dialog.hide();
}






og.ExtModal = function(config) {
	
	config.stateful = false;
	if (!config.genid) config.genid = Ext.id();
	
	og.ExtModal.superclass.constructor.call(this, Ext.applyIf(config, {
		y: 75,
		id: config.genid + 'ext_modal',
		layout: 'fit',
		cls: 'ext-modal-object-list ' + config.basecls,
		modal: true,
		resizable: false,
		border: false,
		title: config.title,
		html: config.html
	}));
}
Ext.extend(og.ExtModal, Ext.Window, {
});

og.ExtModal.show = function(config) {
	if (!config) config = {};
	if (this.dialog) {
		this.dialog.destroy();
	}

	this.dialog = new og.ExtModal(config);
	this.dialog.purgeListeners();
	this.dialog.show();
	
	return this.dialog;
}

og.ExtModal.hide = function() {
	if (this.dialog) this.dialog.hide();
}

