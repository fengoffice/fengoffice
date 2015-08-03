<script>
var dimensions_panel = Ext.getCmp('menu-panel');
dimensions_panel.items.each(function(item, index, length) {
	if (item.dimensionCode == 'workspaces' || item.dimensionCode == 'tags') {
		if (!ogTasks.additional_groupby_dimensions) ogTasks.additional_groupby_dimensions = [];
		ogTasks.additional_groupby_dimensions.push({id: item.dimensionId, name: item.title});
	}
});
</script>