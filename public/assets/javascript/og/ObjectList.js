og.ObjectList = function(config, ignore_context) {
	if (!config) config = {};
	
	var title = config.title ? config.title : lang('select an object');
	var list_id = config.id ? config.id : 'object-list';
	
	og.ObjectList.superclass.constructor.call(this, Ext.apply(config, {
		y: 50,
		width: 640,
		height: 480,
		id: list_id,
		cls: 'ext-modal-object-list',
		layout: 'border',
		modal: true,
		closeAction: 'close',
		//iconCls: 'op-ico',
		title: title,
		buttons: [{
			text: lang('ok'),
			cls:"submit-btn-blue",
			handler: this.accept,
			scope: this
		},{
			text: lang('cancel'),
			cls:"cancel-btn-g",
			handler: this.cancel,
			scope: this
		}],
		items: [
			{
				region: 'center',
				layout: 'fit',
				items: [
					this.grid = new og.ObjectGrid(config)
				]
			}
		]
	}));	
}

Ext.extend(og.ObjectList, Ext.Window, {
	cancel: function() {
		this.close();
	},	
	load: function() {
		this.grid.load();
	}
});

og.ObjectList.show = function(callback, scope, config) {
	if (!config) config = {};
	if (!config.ignore_context) config.ignore_context = 0;
    
	this.dialog = new og.ObjectList(config, config.ignore_context);
	
	if (config.context) {
		this.dialog.grid.store.baseParams.context = config.context;
	}
	
	this.dialog.load();
	this.dialog.purgeListeners();
	
	this.dialog.on('hide', og.restoreFlashObjects);
	this.dialog.on('close', og.restoreFlashObjects);
	og.hideFlashObjects();
	this.dialog.show();
	var pos = this.dialog.getPosition();
	if (pos[0] < 0) pos[0] = 0;
	if (pos[1] < 0) pos[1] = 0;
	this.dialog.setPosition(pos[0], pos[1]);
}