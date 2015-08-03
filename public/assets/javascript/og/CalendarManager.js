alert("cal manager") ;
/**
 *  CalendarManager
 */
og.CalendarManager = function() {
	var actions;
	this.doNotRemove = true;
	this.needRefresh = false;
	
	if (!og.CalendarManager.store) {
		og.CalendarManager.store = new Ext.data.Store({
	        proxy: new og.GooProxy({
	            url: og.getUrl('event', 'view_calendar', {})
	        }),
	        reader: new Ext.data.JsonReader({
	            root: 'events',
	            totalProperty: 'totalCount',
	            id: 'id'
	        }),
	        remoteSort: true,
			listeners: {
				'load': function() {
					var d = this.reader.jsonData;
					/*var ws = og.clean(Ext.getCmp('workspace-panel').getActiveWorkspace().name);
					var tag = og.clean(Ext.getCmp('tag-panel').getSelectedTag());*/
                                    
                                        var sm = Ext.getCmp('calendar-manager').getSelectionModel();
                                        sm.clearSelections();
				}
			},
			renderTo: Ext.getBody()
	    });
    }
    this.store = og.CalendarManager.store;
    this.store.addListener({messageToShow: {fn: this.showMessage, scope: this}});
    //--------------------------------------------
    //--------------------------------------------

	og.CalendarManager.superclass.constructor.call(this, {
		store: this.store,
		layout: 'fit',
		border: false,
        closable: true
    });

};

Ext.extend(og.CalendarManager, Ext.Panel, {
	load: function(params) {
		if (!params) params = {};
		Ext.apply(this.store.baseParams, {
		      context: og.contextManager.plainContext()
		});
		this.store.load({
			params: Ext.apply(params, {
		      context: og.contextManager.plainContext()
			})
		});
		this.needRefresh = true;
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
		this.innerMessage.innerHTML = text;
	}
});

Ext.reg("events", og.CalendarManager);