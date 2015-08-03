/*
 *  Feng Office - Open source weboffice suite - http://www.fengoffice.com
 *
 *  Image chooser for Feng Office
 */

og.ImageChooser = function(config) {
	this.config = config;
	this.initTemplates();
			
	this.store = new Ext.data.JsonStore({
	    url: this.config.url,
	    root: 'files',
	    fields: [
	        'id',
	        'name',
	        {name:'size', type: 'float'},
	        'dateUpdated'
	    ],
	    listeners: {
	    	'load': {fn:function() { this.view.select(0); }, scope:this, single:true}
	    }
	});
	
	var formatSize = function(data) {
        if (data.size < 1024) {
            return data.size + " bytes";
        } else {
            return (Math.round(((data.size*10) / 1024))/10) + " KB";
        }
    };
	
	var formatData = function(data) {
    	data.shortName = og.clean(data.name.ellipse(15));
    	data.sizeString = og.clean(formatSize(data));
    	data.dateString = data.dateUpdated;
    	this.lookup[data.name] = data;
    	return data;
    };
	
    this.view = new Ext.DataView({
		tpl: this.thumbTemplate,
		singleSelect: true,
		overClass: 'x-view-over',
		itemSelector: 'div.thumb-wrap',
		emptyText : '<div style="padding:10px;">' + lang('no images match the specified filter') + '</div>',
		store: this.store,
		listeners: {
			'selectionchange': {fn:this.showDetails, scope:this, buffer:100},
			'dblclick'       : {fn:this.doCallback, scope:this},
			'loadexception'  : {fn:this.onLoadException, scope:this},
			'beforeselect'   : {fn:function(view) {
		        return view.store.getRange().length > 0;
		    }}
		},
		prepareData: formatData.createDelegate(this)
	});
    
	var cfg = {
    	title: lang('choose an image'),
    	id: 'img-chooser-dlg',
    	layout: 'border',
		minWidth: 500,
		minHeight: 300,
		modal: true,
		closeAction: 'hide',
		border: false,
		items:[{
			id: 'img-chooser-view',
			region: 'center',
			autoScroll: true,
			items: this.view,
		tbar:[{
			text: lang('filter') + ':'
		},{
			xtype: 'textfield',
			id: 'filter',
			selectOnFocus: true,
			width: 100,
			listeners: {
				'render': {fn:function(){
					Ext.getCmp('filter').getEl().on('keyup', function(){
						this.filter();
					}, this, {buffer:500});
				}, scope:this}
			}
		}, ' ', '-', {
			text: lang('sort by') + ':'
		}, {
			id: 'sortSelect',
			xtype: 'combo',
				typeAhead: true,
				triggerAction: 'all',
				width: 100,
				editable: false,
				mode: 'local',
				displayField: 'desc',
				valueField: 'name',
				lazyInit: false,
				value: 'name',
				store: new Ext.data.SimpleStore({
					fields: ['name', 'desc'],
					data : [['name', lang('name')],['size', lang('file size')],['dateUpdated', lang('last modified')]]
				}),
				listeners: {
					'select': {fn:this.sortImages, scope:this}
				}
			}]
		},{
			id: 'img-detail-panel',
			region: 'east',
			split: true,
			width: 150,
			minWidth: 150,
			maxWidth: 250
		}],
		buttons: [{
			id: 'ok-btn',
			text: lang('ok'),
			handler: this.doCallback,
			scope: this
		},{
			text: lang('cancel'),
			handler: function(){ this.hide(); },
			scope: this
		}],
		keys: {
			key: 27, // Esc key
			handler: function(){ this.hide(); },
			scope: this
		}
	};
	Ext.apply(cfg, this.config);
    og.ImageChooser.superclass.constructor.call(this, cfg);
};

og.ImageChooser.show = function(imagesUrl, func, scope, button) {
	if(!this.chooser) {
		this.chooser = new og.ImageChooser({
			url: imagesUrl,
			width: 515, 
			height:350
		});
	}
	this.chooser.load();
	this.chooser.callback = function(data) {
		func.call(scope, og.getUrl('files', 'download_file', {id: data.id}));
	};
	this.chooser.animateTarget = Ext.get(button);
	this.chooser.on('hide', og.restoreFlashObjects);
	this.chooser.on('close', og.restoreFlashObjects);
	og.hideFlashObjects();
   	this.chooser.show();
};

Ext.extend(og.ImageChooser, Ext.Window, {
    lookup : {},
    
	initTemplates : function(){
		this.thumbTemplate = new Ext.XTemplate(
			'<tpl for=".">',
				'<div class="thumb-wrap" id="{name}">',
				'<div class="thumb"><img src="' + og.getUrl('files', 'download_file', {id: '__FILE_ID__'}).replace("__FILE_ID__", "{id}") + '" title="{name}"></div>',
				'<span>{shortName}</span></div>',
			'</tpl>'
		);
		this.thumbTemplate.compile();
		
		this.detailsTemplate = new Ext.XTemplate(
			'<div class="details">',
				'<tpl for=".">',
					'<img src="' + og.getUrl('files', 'download_file', {id: '__FILE_ID__'}).replace("__FILE_ID__", "{id}") + '"><div class="details-info">',
					'<b>' + lang('image name') + ':</b>',
					'<span>{name}</span>',
					'<b>' + lang('size') + ':</b>',
					'<span>{sizeString}</span>',
					'<b>' + lang('last modified') + ':</b>',
					'<span>{dateString}</span></div>',
				'</tpl>',
			'</div>'
		);
		this.detailsTemplate.compile();
	},
	
	showDetails : function(){
	    var selNode = this.view.getSelectedNodes();
	    var detailEl = Ext.getCmp('img-detail-panel').body;
		if(selNode && selNode.length > 0){
			selNode = selNode[0];
			Ext.getCmp('ok-btn').enable();
		    var data = this.lookup[selNode.id];
            detailEl.hide();
            this.detailsTemplate.overwrite(detailEl, data);
            detailEl.slideIn('l', {stopFx:true,duration:.2});
		}else{
		    Ext.getCmp('ok-btn').disable();
		    detailEl.update('');
		}
	},
	
	filter : function(){
		var filter = Ext.getCmp('filter');
		this.view.store.filter('name', filter.getValue());
		this.view.select(0);
	},
	
	sortImages : function(){
		var v = Ext.getCmp('sortSelect').getValue();
    	this.view.store.sort(v, v == 'name' ? 'asc' : 'desc');
    	this.view.select(0);
    },
	
	reset : function(){
		if(this.rendered){
			Ext.getCmp('filter').reset();
			this.view.getEl().dom.scrollTop = 0;
		}
	    this.store.clearFilter();
		this.view.select(0);
	},
	
	doCallback : function(){
        var selNode = this.view.getSelectedNodes()[0];
		var callback = this.callback;
		var lookup = this.lookup;
		this.hide(this.animateTarget, function(){
            if(selNode && callback){
				var data = lookup[selNode.id];
				callback(data);
			}
		});
    },
    
    load: function() {
    	this.store.load();
    },
	
	onLoadException : function(v,o){
	    this.getEl().update('<div style="padding:10px;">Error loading images.</div>'); 
	}
});

String.prototype.ellipse = function(maxLength){
    if(this.length > maxLength){
        return this.substr(0, maxLength-3) + '...';
    }
    return this;
};
