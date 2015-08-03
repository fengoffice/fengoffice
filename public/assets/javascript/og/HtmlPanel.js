og.HtmlPanel = function(config) {
	var html = config.html;
	config.html = null;
	var url = config.url;
	config.url = null;
	og.HtmlPanel.superclass.constructor.call(this, Ext.apply(config, {
		cls: 'html-panel'
	}));
	if (html) {
		this.load(html);
	} else if (url) {
		this.get(url);
	}
};

Ext.extend(og.HtmlPanel, Ext.Panel, {

	get: function(url) {
		this.urli = url;
		if (this.rendered) {
			Ext.Ajax.request({
				url: og.makeAjaxUrl(url),
				callback: function(options, success, response) {
					this.load(response.responseText);
				},
				scope: this
			});
		}
	},

	load: function(html) {
		this.htmli = html;
		if (this.rendered) {
			this.update();
		}
	},
	
	onRender : function(ct, position){
		og.HtmlPanel.superclass.onRender.call(this, ct, position);
		this.rendered = true;
		if (this.urli) {
			this.get(this.urli);
		} else {
			this.update();
		}
	},
	
	update: function() {
		this.body.update(this.htmli, true);
		og.captureLinks(this.id, this.ownerCt);
	}
});

Ext.reg("htmlpanel", og.HtmlPanel);