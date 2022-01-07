<?php

	$can_see_billing_info = true;
	Hook::fire('get_can_see_billing_information', array('user'=>logged_user()), $can_see_billing_info);
	$show_billing = can_manage_billing(logged_user()) && $can_see_billing_info;
	
	if (!isset($genid)) $genid = gen_id();
	/* @var $__timeslots_object ProjectTask */
	$open_timeslots = $__timeslots_object->getOpenTimeslots();
	
	$open_timeslot = null;
	foreach($open_timeslots as $timeslot) {
		if (!$__timeslots_object->isTrashed() && $timeslot->isOpen() && $timeslot->getContactId() == logged_user()->getId() && $timeslot->canEdit(logged_user())){
			$open_timeslot = $timeslot;
		}
	}
	
?>

    <div class="commentsTitle">
    	<table style="width:100%"><tr><td><?php echo lang('work performed')?></td>
    		<?php if($__timeslots_object instanceof ProjectTask){ ?>
    			<td align=right><a style="display:none;font-weight:normal;font-size:80%;" id="<?php echo $genid?>_print_link" class="coViewAction ico-print" href="<?php echo get_url('reporting','total_task_times_by_task_print',array("id" => $__timeslots_object->getId())) ?>" target="_blank"><?php echo lang('print') ?></a>  </td>
    		<?php } ?>
    	</tr></table> 
    </div>
    
    
    <div id="<?php echo $genid ?>_timeslots_grid_container" class="object-view-grid-container"></div>
    

    <script>
	
	var grid_id = '<?php echo $genid ?>_timeslots_grid'; 
	
	
    og.task_timeslots_grid = {

    	grid_id: grid_id,
    	
    	name_renderer: function (value, p, r) {
    		if (r.data.id == '__total_row__' || r.data.object_id <= 0) return '<span id="__total_row__">'+value+'</span>';
    		var onclick = "og.openLink(og.getUrl('contact', 'view', {id: "+ r.data.uid +"})); return false;";
    		return String.format('<a href="#" onclick="{1}" title="{2}" style="font-size:120%;"><span class="bold">{0}</span></a>', og.clean(r.data.uname), onclick, og.clean(r.data.uname));
    	},

    	delete_timeslot: function(tid) {
    		if (confirm('<?php echo escape_single_quotes(lang('confirm delete timeslot'))?>')) {
    			og.openLink(og.getUrl('time', 'delete_timeslot', {id:tid}), {
    				callback: function(success, data) {
    					var g = Ext.getCmp(og.task_timeslots_grid.grid_id);
    					if (g) {
    						og.openLink(og.getUrl('task','render_task_work_performed_summary',{id:g.store.baseParams.rel_object_id}), {
        						callback: function(success,data){
            						if (data && data.html) {
            							$("#<?php echo $genid ?>_work_performed_summary").html(data.html);
            						}
            					}
    						});
							og.openLink(og.getUrl('task','render_task_financials_summary', {id:g.store.baseParams.rel_object_id}), {
        						callback: function(success,data){
            						if (data && data.html) {
            							$("#<?php echo $genid ?>_task_financials_summary").html(data.html);
            						}
            					}
    						});
    					}
        				if (g) g.reset();
    				}
    			});
    		}
    	},

    	render_grid_actions: function(value, p, r) {
    		var actions = '';
    		if (r.data.id == '__total_row__' || r.data.id <= 0) return actions;
    		
    		var actionStyle= ' style="font-size:105%;padding-top:2px;padding-bottom:3px;padding-left:16px;background-repeat:no-repeat;" '; 

    		if (r.data.can_edit) {
    			actions += String.format(
    				'<a class="list-action ico-edit" href="#" onclick="og.render_modal_form(\'\', {c:\'time\', a:\'edit_timeslot\', params:{id:'+r.data.id+'}});" title="{0}" '+
    				actionStyle + '>&nbsp;</a>', lang('edit')
    			);
    		}
    		if (r.data.can_delete) {
    			actions += String.format(
    				'<a class="list-action ico-delete" href="#" onclick="og.task_timeslots_grid.delete_timeslot('+r.data.id+');" title="{0}" '+
    				actionStyle + '>&nbsp;</a>', lang('delete')
    			);
    		}
    			
    		return '<span>' + actions + '</span>';
    	}
    }
    
	// columns
    var timeslots_columns = [];
    
    timeslots_columns.push({
    	name: 'start_time',
    	header: '<?php echo lang('start time')?>',
    	sortable: true
    });
    timeslots_columns.push({
    	name: 'end_time',
    	header: '<?php echo lang('end time')?>',
    	sortable: true
    });
    timeslots_columns.push({
    	name: 'worked_time',
    	header: lang('worked time'),
    	align: 'right',
    	sortable: true
    });
    timeslots_columns.push({
    	name: 'subtract',
    	header: '<?php echo lang('paused time') ?>',
    	align: 'right',
    	sortable: true
    });
    timeslots_columns.push({
    	name: 'description',
    	header: lang('description'),
    	sortable: true
    });
    <?php if ($show_billing) { ?>
	    timeslots_columns.push({
	    	name: 'fixed_billing',
	    	header: '<?php echo lang('billing') ?>',
	    	align: 'right',
	    	sortable: true
	    });
    <?php } ?>
    
	// system columns
    var system_columns = ['uid','uname','can_edit','can_delete','add_cls','start_time_ts','paused_on_ts','paused_time_sec','rel_object_id'];
    for (var j=0; j<system_columns.length; j++) {
    	timeslots_columns.push({
        	name: system_columns[j],
        	is_system: true
        });
    }
	
    timeslots_columns.push({
    	name: 'actions',
    	is_right_column: true,
    	fixed: true,
    	width: 75, 
    	renderer: og.task_timeslots_grid.render_grid_actions
    });

<?php
	$add_columns = array();
	$dummy_ts = new Timeslot();
	
	Hook::fire("view_timeslot_render_more_columns", $dummy_ts, $add_columns);

	foreach ($add_columns as $col_id => $val) {
		$align = in_array($dummy_ts->getColumnType($col_id), array(DATA_TYPE_FLOAT, DATA_TYPE_INTEGER)) ? 'right' : 'left';
	?>
		timeslots_columns.push({
			name: '<?php echo $col_id ?>',
			header: '<?php echo lang($col_id) ?>',
			align: '<?php echo $align ?>',
			renderer: 'string',
			sortable: true
		});
<?php
	}
?>

    // buttons
    var timeslots_tbar_items = [];
	var timeslots_tbar_right_items = [];

<?php 
	
	$prevent_adding_worked_time_to_parent = false;	
	
	Hook::fire('get_prevent_adding_time_to_parent', array('time_type' => 'worked', 'is_parent' => $__timeslots_object->isParent()), $prevent_adding_worked_time_to_parent);
	
	if ($__timeslots_object->canAddTimeslot(logged_user()) && !$prevent_adding_worked_time_to_parent) { ?>
    
	var new_btn = new Ext.Button({
    	iconCls: 'ico-new add-first-btn blue',
    	text: '<?php echo lang('add work')?>',
    	id: 'new_user_btn',
    	handler: function() {
    		og.render_modal_form('', {c:'time', a:'add', params: {object_id:<?php echo $__timeslots_object->getId() ?>, contact_id:<?php echo logged_user()->getId() ?>}});
    	}
    });
    timeslots_tbar_items.push(new_btn);
    
    
    <?php if (user_config_option('show_start_time_action')) { ?>
	<?php if (!$open_timeslot) { ?>
            var start_work_btn = new Ext.Button({
                iconCls: 'ico-time add-first-btn',
                text: '<?php echo lang('start work')?>',
                id: 'start_work_btn',
                handler: function() {
                        og.openLink(og.getUrl('timeslot', 'open', {object_id:<?php echo $__timeslots_object->getId() ?>}));
                }
            });
            timeslots_tbar_items.push(start_work_btn);
        <?php } ?>
    <?php } ?>    
<?php } ?>

	var ts_print_btn = new Ext.Button({
		iconCls: 'ico-print add-first-btn left',
		text: '<?php echo lang('print')?>',
		id: 'ts_print_btn',
		handler: function() {
			$("#<?php echo $genid?>_print_link")[0].click();
		}
	});
	//timeslots_tbar_items.push(ts_print_btn);
<?php 
$can_delete_timeslots = can_delete(logged_user(), $__timeslots_object->getMembers(), Timeslots::instance()->getObjectTypeId());
$show_delete_all_button = user_config_option('tasksShowWorkPerformedDeleteAllButton');
if ($can_delete_timeslots && $show_delete_all_button){
?>
	var ts_delete_all_btn = new Ext.Button({
		iconCls: 'ico-delete-btn add-first-btn',
		text: '<?php echo lang('delete all timeslots')?>',
		id: 'ts_delete_all_btn',
		handler: function() {
			if (confirm('<?php echo escape_single_quotes(lang('confirm delete all timeslots'))?>')) {
				og.openLink(og.getUrl('timeslot', 'delete_all_from_task', {object_id:<?php echo $__timeslots_object->getId() ?>}));
			}
    				
		}
	});
	timeslots_tbar_right_items.push(ts_delete_all_btn);
	
<?php } ?>



    // filters
    var filters = {};
    
    var timeslots_grid = new og.ObjectGrid({
    	renderTo: grid_id + '_container',
    	url: og.getUrl('time', 'list_all'),
    	type_name: 'timeslot',
    	response_objects_root: 'timeslots',
    	grid_id: grid_id,
    	nameRenderer: og.task_timeslots_grid.name_renderer,
    	store_params: {
    		url_controller: 'time',
    		url_action: 'list_all',
    		rel_object_id: <?php echo $__timeslots_object->getId() ?>,
    	    only_closed: true
    	},
    	filters: filters,
    	//checkbox_sel_model: true,
    	columns: timeslots_columns,
    	tbar_items: timeslots_tbar_items,
		tbar_right_items: timeslots_tbar_right_items,
    	no_totals_row: true,
    	add_default_actions_column: false,
    	stateId: 'task-timeslots-list', // to remember the gui state independent of the panel
    }, true);


	var active_tab = Ext.getCmp('tabs-panel').activeTab;
	if (active_tab.events.resize.listeners.length < 2) {
    	active_tab.on('resize', function() {
    		og.resize_all_grids(this);
    	});
    }

    og.resize_all_grids = function(active_tab) {
    	var grids = $("#"+active_tab.id+" .x-grid-panel");
		for (var i=0; i<grids.length; i++) {
			var g = Ext.getCmp($(grids[i]).attr('id'));
			if (g) g.fireEvent('resize');
		}
    }

    timeslots_grid.getView().override({
    	getRowClass: function (record, index, rowParams, store) {
   			return record.data.add_cls ? record.data.add_cls : "";
    	}
    });

    timeslots_grid.load();

    </script>
    
<?php 
if (!$__timeslots_object->isTrashed()) {
	if ($open_timeslot) {
		echo render_open_timeslot_form($__timeslots_object, $open_timeslot);
	}
}
?>
<br/>
