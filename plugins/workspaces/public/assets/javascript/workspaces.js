
og.workspaces = {
	init: function() {
		for (x in og.dimension_object_types) {
			if (!og.dimension_object_types[x] || typeof(og.dimension_object_types[x])=='function') continue;
			if (og.dimension_object_types[x] == 'workspace') {
				og.additional_on_dimension_object_click[x] = 'og.workspaces.onWorkspaceClick(<parameters>);';
				if (!og.ot_hide_vinculations_link) og.ot_hide_vinculations_link = [];
				og.ot_hide_vinculations_link[x] = true;
			} else if (og.dimension_object_types[x] == 'tag') {
				og.additional_on_dimension_object_click[x] = 'og.workspaces.onTagClick(<parameters>);';
			}
		}
	},
	
	
	
	onWorkspaceClick: function(member_id) {
		og.memberTreeExternalClick('workspaces',member_id);		
	},
	
	onTagClick: function(member_id) {
		og.memberTreeExternalClick('tags',member_id);
	}
};