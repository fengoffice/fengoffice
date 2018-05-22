<?php
	$genid = gen_id();
	$exe_user_type = PermissionGroups::findOne(array('conditions' => "type='roles' AND name='Executive'"))->getId();
?>

<div class="user-groups-container" style="height:auto;">

	<div class="user-groups-section" style="margin-top:15px; border-top:0px;">
		<h1><?php echo lang('users') ?></h1>
		<div class="section-description desc" style="float:left; width:80%;">
			<?php echo lang('full access users desc')?><br />
			<?php echo lang('collaborators desc', '<br />')?><br />
			<?php echo lang('guests desc', '<br />')?><br />
		</div>
		
		<button title="<?php echo lang('close')?>" style="float:right; margin: -30px 0 0 15px;" class="add-first-btn" onclick="og.save_user_and_groups_changes(this)">
			<img src="public/assets/themes/default/images/layout/close16.png" style="margin-bottom:-1px;">&nbsp;<?php echo lang('close')?>
		</button>
	</div>
	<div class="clear"></div>
	
	<div id="<?php echo $genid?>_users_groups_grid_container" style="margin:0 20px; border-left:1px solid #ccc; border-top:1px solid #ccc; height:auto; max-height:700px;"></div>

<?php

$groups = PermissionGroups::getNonRolePermissionGroups();
$gr_lengths = array();
foreach ($groups as $gr) {
	$count = ContactPermissionGroups::count("`permission_group_id` = ".$gr->getId());
	$gr_lengths[$gr->getId()] = $count;
}
?>
	<div class="user-groups-section" style="margin-top:30px; border-top:0px;">
		<h1><?php echo lang('groups') ?></h1>
		<div class="section-description desc"><?php echo lang('groups desc', '<br />')?></div>
		<div class="section-content section1">
			<ul>
		<?php
	if (is_array($groups) && count($groups) > 0) {
		foreach ($groups as $group) {?>
			<li class="user">
			  <a href="<?php echo $group->getEditUrl()?>" class="internalLink" target="<?php echo array_var($_REQUEST, 'current')?>"><div class="wrapper">
				<div class="coViewIconImage ico-large-group"></div>
				<div class="user-name-container">
					<?php echo $group->getName() ?>
					<div class="desc"><?php echo $gr_lengths[$group->getId()] . ' ' . lang('users') ?></div>
				</div>
				<div class="clear"></div>
			  </div></a>
			</li>
		<?php 
		}
	} else {
		?><li class="no-user-message"><?php echo lang('no groups in company') ?></li><?php
	} ?>
			</ul>
			<div class="clear"></div>
			<button title="<?php echo lang('add new group')?>" class="add-first-btn blue" onclick="og.openLink(og.getUrl('group','add'));">
				<img src="public/assets/themes/default/images/16x16/add.png" />&nbsp;<?php echo lang('add new group')?>
			</button>
			<div class="clear"></div>
		</div>
	</div>
</div>

<script>

// columns
var user_group_columns = [];
user_group_columns.push({
	name: 'picture',
	header: '&nbsp;',
	dataIndex: 'picture',
	width: 45,
	renderer: og.gridPictureRenderer,
	fixed:true,
	resizable: false,
	hideable:false,
	menuDisabled: true,
	sortable: false,
	before_name: true
});
user_group_columns.push({
	name: 'role',
	header: lang('role'),
	sortable: true
});
user_group_columns.push({
	name: 'company',
	header: lang('company'),
	sortable: true
});
user_group_columns.push({
	name: 'last_activity',
	header: lang('last activity'),
	sortable: true
});
user_group_columns.push({
	name: 'status',
	header: lang('status'),
	sortable: true
});
user_group_columns.push({
	name: 'disabled',
	is_system: true
});

var grid_id = '<?php echo $genid?>_users_groups_grid';

// buttons
var user_group_tbar_items = [];
var new_btn = new Ext.Button({
	iconCls: 'ico-new add-first-btn blue add-new-user-btn',
	text: '<?php echo lang('add user')?>',
	id: 'new_user_btn',
	handler: function() {
		og.openLink(og.getUrl('contact','add',{is_user:1, user_type:'<?php echo $exe_user_type?>'}));
	}
});
user_group_tbar_items.push(new_btn);

// filters
<?php $current_status_filter = array_var($_SESSION, 'users_list_current_status_filter', 'enabled'); ?>
var status_options = [['all', lang('everyone')], ['enabled', '<?php echo lang('active')?>'], ['disabled', '<?php echo lang('inactive')?>']];
var filters = {
	status_filter: {type:'list', label:"&nbsp;"+lang('show')+":", options: status_options, initial_val:'<?php echo $current_status_filter ?>', width:100}
};

<?php
	$plugin_filters = array();
	Hook::fire('user_and_groups_additional_filters', array(), $plugin_filters);
	if (count($plugin_filters) > 0) {
		foreach ($plugin_filters as $f) {?>
			var filter_conf = {
				type: '<?php echo array_var($f, 'type')?>',
				label: '<?php echo array_var($f, 'label')?>',
				initial_val: '<?php echo array_var($f, 'initial_val')?>',
				value: '<?php echo array_var($f, 'value')?>',
				width: <?php echo array_var($f, 'width', 150)?>
			}
		<?php
			if (array_var($f, 'type')=='list' && array_var($f, 'options')) { ?>
				filter_conf.options = Ext.util.JSON.decode('<?php echo array_var($f, 'options')?>');
		<?php 
			}
			?>
			filters['<?php echo $f['filter']?>'] = filter_conf;
		<?php
		}
	}
?>

if (og.additional_list_actions && og.additional_list_actions.users) {
    user_group_tbar_items.push('-');
    for (var i=0; i<og.additional_list_actions.users.length; i++) {
        user_group_tbar_items.push(og.additional_list_actions.users[i]);
    }
}



var user_groups_grid = new og.ObjectGrid({
	renderTo: grid_id + '_container',
	url: og.getUrl('more', 'users_list'),
	type_name: 'none',
	response_objects_root: 'users',
	grid_id: grid_id,
	nameRenderer: og.gridObjectNameRenderer,
	store_params: {
		url_controller: 'more',
		url_action: 'users_list'
	},
	filters: filters,
	//checkbox_sel_model: true,
	skip_dimension_columns: true,
	columns: user_group_columns,
	no_icon_col: true,
	add_default_actions_column: false,
	//max_height: 800,
	tbar_items: user_group_tbar_items
});

var active_tab = Ext.getCmp('tabs-panel').activeTab;
if (active_tab.events.resize.listeners.length < 2) {
	active_tab.on('resize', function() {
		var g = Ext.getCmp(grid_id)
		if (g) g.fireEvent('resize');
	});
}

user_groups_grid.getView().override({
	getRowClass: function (record, index, rowParams, store) {
		if (record.data.disabled) {
			return "users-list-row-disabled";
		}else{
			return "";
		}
	}
});

user_groups_grid.load();



og.save_user_and_groups_changes = function(btn) {
	og.openLink(og.getUrl('more', 'set_getting_started_step', {'step': 3}), {
		callback: function(success, data) {
			og.goback(btn);
		}
	});
}
</script>