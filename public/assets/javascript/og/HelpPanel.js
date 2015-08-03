og.HelpPanel = function(config) {
	
	this.current = '';
	
	og.HelpPanel.superclass.constructor.call(this, Ext.apply(config, {
		defaultContent: {
			type: 'url',
			data: 'public/help/index.html'
		},
		active: true,
		listeners: {
			'beforeexpand': function(){
				var currentPanel = Ext.getCmp('tabs-panel').getActiveTab();
				og.openLink(og.getUrl('help', 'get_help_content', {template: currentPanel.help.template}), {
					callback: function(success, data) {
						if (success) {
							var help = {
								type: 'html',
								data: data.content
							}
							Ext.getCmp('help-panel').load(help);							
						}
					},
					scope: this
				});			
			}
		}
	}));
	this.load(this.defaultContent);
	
	
};

Ext.extend(og.HelpPanel, og.ContentPanel, {
	workspaceChanged: function() {
	},
	
	setCurrent: function(panel){
		this.current = panel;
	},
	
	getCurrent: function(){
		return this.current;
	}
});