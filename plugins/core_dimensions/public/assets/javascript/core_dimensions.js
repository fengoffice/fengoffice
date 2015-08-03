
og.core_dimensions = {
	init: function() {
		for (x in og.dimension_object_types) {
			if (!og.dimension_object_types[x] || typeof(og.dimension_object_types[x])=='function') continue;
			if (og.dimension_object_types[x] == 'contact' || og.dimension_object_types[x] == 'person' || og.dimension_object_types[x] == 'company') {
				if (!og.before_object_view) og.before_object_view = [];
				og.before_object_view[x] = 'og.core_dimensions.onContactClick(<parameters>);';
			}
		}
		
		og.eventManager.addListener('member changed', 
		 	function (node){
				var tree = node.getOwnerTree();
				if (tree.dimensionCode == 'feng_persons') {
					var selection = tree.getSelectionModel().getSelectedNode();
					if (selection && selection.id) {
						if (og.core_dimensions.prev_selection[og.core_dimensions.prev_selection.length-1] != selection.id) {
							og.core_dimensions.prev_selection.push(selection.id);
						}
					}
				}
			}
		);
	},
	
	onContactClick: function(member_id) {
		var dimensions_panel = Ext.getCmp('menu-panel');
		dimensions_panel.items.each(function(item, index, length) {
			if (item.dimensionCode == 'feng_persons') {
				og.expandCollapseDimensionTree(item);
				
				var n = item.getNodeById(member_id);
				
				if (n) {
					if (n.parentNode) item.expandPath(n.parentNode.getPath(), false);
					n.select();
				}
			}
		});
	},
	
	buildBeforeObjectViewAction: function(obj_id, suspend_events) {
		var dimensions_panel = Ext.getCmp('menu-panel');
		dimensions_panel.items.each(function(item, index, length) {
			if (item.dimensionCode == 'feng_persons') {
				og.expandCollapseDimensionTree(item);
				
				var member_id = -1;
				item.root.cascade(function(){
					if (this.object_id == obj_id) member_id = this.id;
	 			});
				if (member_id > 0) {
					if (suspend_events) item.getSelectionModel().suspendEvents();
					og.core_dimensions.onContactClick(member_id);
					if (suspend_events) item.getSelectionModel().resumeEvents();
				}
			}
		});
		return "";
	},
	
	prev_selection: []
};