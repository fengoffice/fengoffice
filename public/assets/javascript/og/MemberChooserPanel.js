og.MemberChooserPanel = function(config) {
	
	
	Ext.applyIf(config, {
		
		iconCls: 'ico-workspaces',
		
		//layout: 'column',
		
		autoScroll: true,

		renderTo: 'member-chooser-panel'


	});
	
	og.MemberChooserPanel.superclass.constructor.call(this, config);
	
	
	
};

Ext.extend(og.MemberChooserPanel, Ext.Panel,{
	
	checkNodes: function (nodes) {
		
		this.items.each(function(item, index, length){
			item.checkNodes(nodes);
		});

	}
	
});