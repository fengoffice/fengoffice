<?php
require_javascript("og/ReportingFunctions.js");
$genid = gen_id();
$grid_id = array_var($_REQUEST, 'current') . "_timeslots_module_grid";

$additional_actions_params = array('panel_id' => $grid_id);
$additional_actions = array();
Hook::fire('timeslot_list_additional_actions', $additional_actions_params, $additional_actions);

$can_see_billing_info = true;
Hook::fire('get_can_see_billing_information', array('user' => logged_user()), $can_see_billing_info);
$show_billing = can_manage_billing(logged_user()) && $can_see_billing_info;

$current_filters_json = user_config_option('current_time_module_filters');
$current_filters = $current_filters_json == "" ? array() : json_decode($current_filters_json, true);

$active_members = array();
foreach (active_context() as $selection) {
    if ($selection instanceof Member)
        $active_members[] = $selection;
}
if (count($active_members) > 0) {
	// when we are filtering by anything use the usual can_add_timeslots function to check permissions
	$can_add_timeslots = can_add_timeslots(logged_user(), $active_members);
} else {
	// when no context is selected use this function to check permissions, it will not verify required dimensions, so we can put the button and the quick add row
	$can_add_timeslots = can_access(logged_user(), $active_members, Timeslots::instance()->getObjectTypeId(), ACCESS_LEVEL_WRITE);
}

Hook::fire('time_tab_override_can_add_timeslots_permission', array('user' => logged_user()), $can_add_timeslots);


$add_quick_add_row = $can_add_timeslots && config_option('use_time_quick_add_row');

// adds new layouts to the time panel
$panel_view_hook_output = null;
$hook_params = array(
    'genid' => $genid,
    'panel_id' => $grid_id,
    'current_filters' => $current_filters
);
Hook::fire('additional_time_panel_view', $hook_params, $panel_view_hook_output);

// check if we have to hide the list
$list_view_display = '';
if (is_array($panel_view_hook_output) && $panel_view_hook_output['hide_list_view']) {
	$list_view_display = 'display:none;';
}

?>

<div id="timePanel" class="ogContentPanel" style="height:100%; overflow-x: scroll;">

    <div id="<?php echo $grid_id ?>_container" style="height:100%;<?php echo $list_view_display ?>"></div>

	<?php
		// html for the additional layouts
		if (is_array($panel_view_hook_output) && $panel_view_hook_output['html']) {
			echo $panel_view_hook_output['html'];
		}
	?>

</div>


<script>
    var grid_id = '<?php echo $grid_id ?>';

    og.module_timeslots_grid = {

        grid_id: grid_id,

        clocks_to_start: [],

        name_renderer: function(value, p, r) {
            if (r.id == 'quick_add_row')
                return value;
            if (r.data.id == '__total_row__' || r.data.object_id <= 0)
                return '<span id="__total_row__">' + value + '</span>';
            var onclick = "og.openLink(og.getUrl('contact', 'view', {id: " + r.data.uid + "})); return false;";
            return String.format('<a href="#" onclick="{1}" title="{2}" style="font-size:120%;"><span class="bold">{0}</span></a>', og.clean(r.data.uname), onclick, og.clean(r.data.uname));
        },

        task_name_renderer: function(value, p, r) {
            if (r.id == 'quick_add_row') {
                return value;
			}

            if (r.data.id == '__total_row__' || r.data.object_id < 0) {
                return '';
			}

			if (r.data.rel_object_id == 0) {
				let onclick = String.format("og.inline_change_object_task('{0}', '{1}', '{2}', '{3}'); return false;", r.data.object_id, 'time', 'assign_task_to_timeslots', grid_id);
				return '<div class="assign-task-link"><a href="#" onclick="' + onclick + '" class="link-ico ico-add" title="' + lang('assign task') + '"></a></div>';
			}

            var onclick = "og.openLink(og.getUrl('task', 'view', {id: " + r.data.rel_object_id + "})); return false;";

			return String.format(
				'<div class="task-link" data-object-id="{2}" data-task-id="{3}" data-controller="{4}" data-action="{5}"><a href="#" onclick="{1}" class="underline">{0}</a></div>', 
				og.clean(value), 
				onclick, 
				r.data.object_id, 
				r.data.rel_object_id,
				'time',
				'assign_task_to_timeslots',
			);
        },

        delete_timeslot: function (tid) {
            og.openLink(og.getUrl('time', 'check_time_invoicing_status', {id: tid}), {
                callback: function (success, data) {
                    if(data.timeslotId > 0){
                        if (confirm('<?php echo escape_single_quotes(lang('confirm delete timeslot')) ?>')) {
                            og.openLink(og.getUrl('time', 'delete_timeslot', {id: tid, req_channel:'time list - line delete'}), {
                                callback: function (success, data) {
                                    var g = Ext.getCmp(og.module_timeslots_grid.grid_id);
                                    if (g)
                                        g.reset();
                                }
                            });
                        }
                    }
                }
            });
        },

        render_grid_actions: function(value, p, r) {
            var actions = '';
            if (r.id == 'quick_add_row' || r.data.id == '__total_row__' || r.data.id <= 0)
                return value;

            var actionStyle = ' style="font-size:105%;padding-top:2px;padding-bottom:3px;padding-left:16px;background-repeat:no-repeat;" ';

            if (r.data.end_time) {
                if (r.data.can_edit) {
                    actions += String.format(
                        '<a class="list-action ico-edit" href="#" onclick="og.render_modal_form(\'\', {c:\'time\', a:\'edit_timeslot\', params:{id:' + r.data.id + ', req_channel:\'time list - line edit\'}});" title="{0}" ' +
                        actionStyle + '>&nbsp;</a>', lang('edit')
                    );
                }
                if (r.data.can_delete) {
                    actions += String.format(
                        '<a class="list-action ico-delete" href="#" onclick="og.module_timeslots_grid.delete_timeslot(' + r.data.id + ');" title="{0}" ' +
                        actionStyle + '>&nbsp;</a>', lang('delete')
                    );
                }
                if (r.data.can_view_history) {
                    actions += String.format(
                        '<a class="list-action ico-properties" href="#" onclick="og.render_modal_form(\'\', {c:\'object\', a:\'view_history\', params:{id:' + r.data.id + '}});" title="{0}" ' +
                        actionStyle + '>&nbsp;</a>', lang('view history')
                    );
                }

            } else {

                var d = new Date(r.data.start_time_ts * 1000);
                var now_time = <?php echo DateTimeValueLib::now()->getTimestamp() * 1000; ?>;
                var html_id = '<?php echo $genid ?>_ts_' + r.data.id;

                actions += '<div class="open-ts-clock"><span id="' + html_id + 'timespan">';
                if (r.data.paused_on_ts > 0) {
                    var diff_m = 0,
                        diff_h = 0;
                    var diff_s = r.data.paused_on_ts - r.data.start_time_ts - r.data.paused_time_sec;
                    while (diff_s >= 60) {
                        diff_s -= 60;
                        diff_m++;
                    }
                    while (diff_m >= 60) {
                        diff_m -= 60;
                        diff_h++;
                    }
                    if (diff_s < 10)
                        diff_s = '0' + diff_s;
                    if (diff_m < 10)
                        diff_m = '0' + diff_m;
                    if (diff_h < 10)
                        diff_h = '0' + diff_h;
                    actions += diff_h + ':' + diff_m + ':' + diff_s;

                    actions += '<input type="hidden" id="' + html_id + 'user_start_time" value="' + Math.ceil(r.data.paused_on_ts - r.data.start_time_ts - r.data.paused_time_sec) + '"></span></span>';

                } else {

                    actions += '<input type="hidden" id="' + html_id + 'user_start_time" value="' + Math.ceil((now_time - d.getTime()) / 1000 - r.data.paused_time_sec) + '"></span></span>';
                    og.module_timeslots_grid.clocks_to_start.push(html_id);
                }
                actions += '</div>';
 
                
                actions += '<div class="task-single-div">';
                // Stop timer
                actions += '<a href="#" class="task-single-div" onclick="og.module_timeslots_grid.close_work(' + r.data.id + ');">';
                actions += '<div class="ogTasksTimeClock ico-time-stop task-action-icon" title="' + lang('close_work') + '"></div></a>';

                if (r.data.paused_on_ts) {
                    // Resume timer
                    actions += '<a href="#" class="task-single-div" onclick="og.module_timeslots_grid.resume_work(' + r.data.id + ');">';
                    actions += '<div class="ogTasksTimeClock ico-time-play task-action-icon" title="' + lang('resume_work') + '"></div></a>'
                } else {
                    // Pause timer
                    actions += '<a href="#" class="task-single-div" onclick="og.module_timeslots_grid.pause_work(' + r.data.id + ');">';
                    actions += '<div class="ogTasksTimeClock ico-time-pause task-action-icon" title="' + lang('pause_work') + '"></div></a>';
                }

                // Cancel timer
                actions += '<a href="#" class="task-single-div" onclick="og.module_timeslots_grid.cancel_work(' + r.data.id + ');">';
                actions += '<div class="ogTasksTimeClock ico-delete task-action-icon" title="' + lang('discard_work') + '"></div></a>';
                    
                actions += '</div>';
            }

            return '<div>' + actions + '</div>';
        },

        close_work: function(tid) {
            og.render_modal_form('', {
				c: 'timeslot',
				a: 'close_timer',
				params: {
					id: tid,
					req_channel: 'time list - close'
				}
            });
        },

        cancel_work: function(tid) {
            if (confirm(lang('confirm discard work timeslot'))){
                this.execute_action("close", tid, true);
            }
        },

        resume_work: function(tid) {
            this.execute_action("resume", tid);
        },

        pause_work: function(tid) {
            this.execute_action("pause", tid);
        },

        execute_action: function(action, id, cancel = false) {
            og.openLink(og.getUrl('timeslot', action, {
                id: id,
                cancel: cancel,
				req_channel: 'time list - ' + (cancel ? 'cancel' : action)
            }));
        },

        start_clocks: function() {
            for (var i = 0; i < og.module_timeslots_grid.clocks_to_start.length; i++) {
                var pre_id = og.module_timeslots_grid.clocks_to_start[i];
                og.startClock(pre_id, parseInt($("#" + pre_id + 'user_start_time').val()));
            }
            og.module_timeslots_grid.clocks_to_start = [];
        }
    }


    // columns
    var timeslots_columns = [];

    timeslots_columns.push({
        name: 'description',
        header: lang('description'),
        sortable: true
    });

    timeslots_columns.push({
        name: 'qbo_sync_status',
        header: lang('quickbooks'),
        sortable: true
    });
    
    timeslots_columns.push({
        name: 'start_time',
        header: '<?php echo lang('start time') ?>',
        fixed: true,
        width: 200,
        sortable: true
    });
    timeslots_columns.push({
        name: 'end_time',
        header: '<?php echo lang('end time') ?>',
        hidden: true,
        sortable: true
    });
    timeslots_columns.push({
        name: 'worked_time',
        header: lang('worked time'),
        align: 'right',
        fixed: true,
        width: 160,
        sortable: true
    });
    timeslots_columns.push({
        name: 'subtract',
        header: '<?php echo lang('paused time') ?>',
        hidden: true,
        align: 'right',
        sortable: true
    });
    timeslots_columns.push({
        name: 'rel_object_name',
        header: '<?php echo lang('task') ?>',
        renderer: og.module_timeslots_grid.task_name_renderer,
        sortable: true
    });
	// created on column
    timeslots_columns.push({
        name: 'dateCreated',
        header: '<?php echo lang('created on') ?>',
        sortable: true
    });
	// created by column
    timeslots_columns.push({
        name: 'createdBy',
        header: '<?php echo lang('created by') ?>',
        sortable: true
    });
	// updated on column
    timeslots_columns.push({
        name: 'dateUpdated',
        header: '<?php echo lang('last updated on') ?>',
        sortable: true
    });
	// created by column
    timeslots_columns.push({
        name: 'updatedBy',
        header: '<?php echo lang('last updated by') ?>',
        sortable: true
    });
    <?php if ($show_billing) { ?>
        timeslots_columns.push({
            name: 'fixed_billing',
            header: '<?php echo lang('billing') ?>',
            hidden: true,
            align: 'right',
            sortable: true
        });
    <?php } ?>

    // system columns
    var system_columns = ['uid', 'uname', 'can_edit', 'can_delete', 'can_view_history', 'add_cls', 'start_time_ts', 'paused_on_ts', 'paused_time_sec', 'rel_object_id'];
    for (var j = 0; j < system_columns.length; j++) {
        timeslots_columns.push({
            name: system_columns[j],
            is_system: true
        });
    }

    // right columns
    timeslots_columns.push({
        name: 'actions',
        is_right_column: true,
        //fixed: true,
        width: 150,
        renderer: og.module_timeslots_grid.render_grid_actions
    });

    <?php
    $add_columns = array();
    $dummy_ts = new Timeslot();
    Hook::fire("view_timeslot_render_more_columns", $dummy_ts, $add_columns);
    if (is_array($add_columns)) {
        foreach ($add_columns as $col_id => $val) {
            $align = in_array($dummy_ts->getColumnType($col_id), array(DATA_TYPE_FLOAT, DATA_TYPE_INTEGER)) ? 'right' : 'left';
    ?>
            timeslots_columns.push({
                name: '<?php echo $col_id ?>',
                header: '<?php echo lang($col_id) ?>',
                align: '<?php echo $align ?>',
                hidden: true,
                renderer: 'string',
                sortable: true
            });
    <?php
        }
    }
    ?>

    // buttons
    var botonera = {
        newtimeslot: new Ext.Button({
            iconCls: 'ico-new add-first-btn blue',
            text: '<?php echo lang('add work') ?>',
            id: 'new_ts_btn',
            handler: function() {
                og.render_modal_form('', {
                    c: 'time',
                    a: 'add',
                    params: {
                        contact_id: <?php echo logged_user()->getId() ?>,
						req_channel: 'time list - toolbar add button'
                    }
                });
            }
        }),

        start: new Ext.Button({
            iconCls: 'ico-time add-first-btn',
            text: '<?php echo lang('start work') ?>',
            id: 'start_work_btn',
            handler: function() {
                og.openLink(og.getUrl('timeslot', 'open', {req_channel: 'time list - toolbar start clock'}));
            }
        }),

        archive: new Ext.Action({
            text: lang('archive'),
            tooltip: lang('archive selected object'),
            iconCls: 'ico-archive-obj',
            disabled: true,
			selection_dependant: true,
			is_multiple: true,
			grid_id: grid_id,
            handler: function() {
                if (confirm(lang('confirm archive selected objects'))) {
                    var cmp = Ext.getCmp(this.grid_id);
                    cmp.store.load({
                        params: Ext.apply({}, {
                            action: 'archive',
                            ids: cmp.getSelectedIds(),
							req_channel: 'time list - toolbar archive button'
                        })
                    });
                }
            }
        }),

        edit: new Ext.Action({
            text: lang('edit'),
            tooltip: lang('edit selected object'),
            iconCls: 'ico-edit',
            disabled: true,
			selection_dependant: true,
			is_multiple: false,
			grid_id: grid_id,
            handler: function() {
				var cmp = Ext.getCmp(this.grid_id);
                og.render_modal_form('', {
                    c: 'time',
                    a: 'edit_timeslot',
                    params: {
                        id: cmp.getFirstSelectedId(),
						req_channel: 'time list - toolbar edit button'
                    }
                });
            }
        }),
        
        trash: new Ext.Action({
            text: lang('move to trash'),
            tooltip: lang('move selected objects to trash'),
            iconCls: 'ico-trash',
            disabled: true,
			selection_dependant: true,
			is_multiple: true,
			grid_id: grid_id,
            handler: function() {
                if (confirm(lang('confirm move to trash'))) {
                    var cmp = Ext.getCmp(grid_id);
                    cmp.store.load({
                        params: Ext.apply({}, {
                            action: 'trash',
                            ids: cmp.getSelectedIds(),
							req_channel: 'time list - toolbar trash button'
                        })
                    });
                }
            }
        }),
        print: new Ext.Button({
            iconCls: 'ico-print',
            text: '<?php echo lang('generate report') ?>',
            id: 'ts_print_btn',
            handler: function() {
                var tab = Ext.getCmp('reporting-panel');
                if (tab) Ext.getCmp('tabs-panel').setActiveTab(tab);
				
				let grid = Ext.getCmp(og.module_timeslots_grid.grid_id);

				// initialize report form with the same filters we have in the time module
				let post_parameters = {
					'params[user]': grid.filters.user_filter.value,
					'params[timeslot_type]': grid.filters.type_filter.value,
					'params[date_type]': grid.filters.period_filter.value,
					'params[start_value]': grid.filters.from_filter.value,
					'params[end_value]': grid.filters.to_filter.value,
				};
				if (grid.filters.invoicing_status_filter) {
					post_parameters['params[invoicing_status]'] = grid.filters.invoicing_status_filter.value;
				}

                og.openLink(og.getUrl('reporting', 'total_task_times_p'), {
					post: post_parameters,
                    caller: 'reporting-panel'
                });
            }
        }),
		assign_task: new Ext.Button({
			iconCls: 'ico-task',
			text: '<?php echo lang('assign task') ?>',
			id: 'ts_assign_task_btn',
			handler: function() {

				og.assign_task_to_objects(grid_id, 'time', 'assign_task_to_timeslots', 'time list - toolbar assign task button');
				
			},
			disabled: true,
			selection_dependant: true,
			is_multiple: true
        })
    };

    var timeslots_tbar_items = [];

    <?php if ($can_add_timeslots ) { ?>
        timeslots_tbar_items.push(botonera.newtimeslot);
        <?php if (user_config_option('show_start_time_action') && !config_option('select_task_for_time_entry')) { ?>
            timeslots_tbar_items.push(botonera.start);
        <?php } ?>
    <?php } ?>
    timeslots_tbar_items.push(botonera.edit);
    timeslots_tbar_items.push(botonera.archive);
    timeslots_tbar_items.push(botonera.trash);
    timeslots_tbar_items.push(botonera.print);
	timeslots_tbar_items.push(botonera.assign_task);


    <?php foreach ($additional_actions as $add_action) {
        if (array_var($add_action, 'type') == 'menu') {
    ?>
            var menu_items = [];
            <?php foreach ($add_action['items'] as $item) { ?>
                menu_items.push(new Ext.Action({
                    iconCls: '<?php echo array_var($item, 'cls') ?>',
                    text: '<?php echo array_var($item, 'text') ?>',
                    handler: function() {
                        <?php if (isset($item['onclick'])) { ?>
                            eval("<?php echo $item['onclick'] ?>");
                        <?php } else if (isset($item['url'])) { ?>
                            og.openLink("<?php echo $item['url'] ?>");
                        <?php } ?>
                    }
                }));
            <?php } ?>
            var menu_action = new Ext.Action({
                text: '<?php echo array_var($add_action, 'text') ?>',
                menu: menu_items
            });
            timeslots_tbar_items.push(menu_action);
        <?php
        } else {
        ?>
            timeslots_tbar_items.push(new Ext.Button({
                iconCls: '<?php echo array_var($add_action, 'cls') ?>',
                text: '<?php echo array_var($add_action, 'text') ?>',
                handler: function() {
                    <?php if (isset($add_action['onclick'])) { ?>
                        eval("<?php echo $add_action['onclick'] ?>");
                    <?php } else if (isset($add_action['url'])) { ?>
                        og.openLink("<?php echo $add_action['url'] ?>");
                    <?php } ?>
                }
            }));

    <?php     }
    } ?>


    // toolbar buttons to the right
    var timeslots_tbar_right_items = [];

    // filters
    var type_options = [
        [0, '<?php echo lang('all timeslots') ?>'],
        [1, '<?php echo escape_character(lang('task timeslots')) ?>'],
        [2, '<?php echo escape_character(lang('time timeslots')) ?>']
    ];
    if (og.additional_timeslot_type_filter_values) {
        for (var j = 0; j < og.additional_timeslot_type_filter_values.length; j++) {
            type_options.push(og.additional_timeslot_type_filter_values[j]);
        }
    }

    var add_row_user_options = [];
    var user_options = [
        [0, lang('everyone')]
    ];

    var period_options = [
        [0, lang('no filter')],
        [1, lang('today')],
        [11, lang('yesterday+')],
        [2, lang('this week')],
        [3, lang('last week+')],
        [7, lang('First half month+')],
        [8, lang('Second half month+')],
        [9, lang('First half of last month+')],
        [10, lang('Second half of last month+')],
        [4, lang('this month')],
        [5, lang('last month+')],
        [12, lang('year to date')],
        [6, lang('select dates...')]
    ];
    var period_ini_val = '<?php echo array_var($current_filters, 'period_filter') ?>';

    <?php foreach ($users as $user) {
        $user_display_name = str_replace("\\'", "'", $user->getObjectName());
    ?>
        user_options.push([<?php echo $user->getId() ?>, '<?php echo clean(escape_character($user_display_name)) ?>']);
        add_row_user_options.push([<?php echo $user->getId() ?>, '<?php echo clean(escape_character($user_display_name)) ?>']);
    <?php } ?>

    var type_ini_val = '<?php echo array_var($current_filters, 'type_filter', '0') ?>';
    var user_ini_val = '<?php echo array_var($current_filters, 'user_filter', '0') ?>';

    if (period_ini_val == 6) {
        var hidden_from = false;
        var hidden_to = false;
    } else {
        var hidden_from = true;
        var hidden_to = true;
    }

    var filters = {
        type_filter: {
            type: 'list',
            label: '&nbsp;' + lang('type') + ': ',
            options: type_options,
            width: 200,
            value: type_ini_val,
            initial_val: type_ini_val,
            secondToolbar: true
        },
        user_filter: {
            type: 'list',
            label: '&nbsp;' + lang('person') + ': ',
            options: user_options,
            width: 150,
            value: user_ini_val,
            initial_val: user_ini_val,
            secondToolbar: true
        },
        period_filter: {
            type: 'period',
            label: '&nbsp;' + lang('Time period') + ': ',
            options: period_options,
            value: period_ini_val,
            initial_val: period_ini_val,
            secondToolbar: true
        },
        from_filter: {
            type: 'date',
            label: {
                id: 'label_from_filter',
                hidden: hidden_from,
                text: '&nbsp;' + lang('from date') + ': '
            },
            value: '<?php echo array_var($current_filters, 'from_filter') ?>',
            hidden: hidden_from,
            secondToolbar: true
        },
        to_filter: {
            type: 'date',
            label: {
                id: 'label_to_filter',
                hidden: hidden_to,
                text: '&nbsp;' + lang('to date') + ': '
            },
            value: '<?php echo array_var($current_filters, 'to_filter') ?>',
            hidden: hidden_to,
            secondToolbar: true
        }
    };

    var store_parameters = {
        url_controller: 'time',
        url_action: 'list_all'
    }
    store_parameters['type_filter'] = '<?php echo array_var($current_filters, 'type_filter'); ?>';
    store_parameters['user_filter'] = '<?php echo array_var($current_filters, 'user_filter'); ?>';
    store_parameters['period_filter'] = '<?php echo array_var($current_filters, 'period_filter'); ?>';
    store_parameters['from_filter'] = '<?php echo array_var($current_filters, 'from_filter') ?>';
    store_parameters['to_filter'] = '<?php echo array_var($current_filters, 'to_filter') ?>';

    var all_current_filters = Ext.util.JSON.decode('<?php echo json_encode($current_filters) ?>');
    // filters added by plugin
    if (og.additional_timeslots_tab_filters) {
        for (filter_code in og.additional_timeslots_tab_filters) {
            var f = og.additional_timeslots_tab_filters[filter_code];
            if (!f || typeof(f) == 'function') continue;

            if (typeof(all_current_filters[filter_code]) != "undefined") {
                f.value = all_current_filters[filter_code];
                f.initial_val = all_current_filters[filter_code];
            }
            filters[filter_code] = f;
            store_parameters[filter_code] = f.initial_val;
        }
    }

    var timeslots_module_grid = new og.ObjectGrid({ 
        genid: '<?php echo $genid ?>',
        separate_totals_request: true,
        renderTo: grid_id + '_container',
        url: og.getUrl('time', 'list_all'),
        type_name: 'timeslot',
        response_objects_root: 'timeslots',
        grid_id: grid_id,
        nameRenderer: og.module_timeslots_grid.name_renderer,
        store_params: store_parameters,
        filters: filters,
        checkbox_sel_model: true,
        fixed_height: true,
        columns: timeslots_columns,
        tbar_items: timeslots_tbar_items,
        tbar_right_items: timeslots_tbar_right_items,
        quick_add_row: <?php echo $add_quick_add_row ? '1' : '0' ?>,
        quick_add_row_fn: og.add_timeslot_module_quick_add_row,
        quick_add_row_user_options: add_row_user_options,
        add_default_actions_column: false,
        name_fixed: true,
        name_width: 200,
        stateId: 'timeslots-module',
        allow_drag_drop: true,
        forceFit: false
    });


    var active_tab = Ext.getCmp('tabs-panel').activeTab;
    if (active_tab.events.resize.listeners.length < 2) {
        active_tab.on('resize', function() {
            og.resize_all_grids(this);
        });
    }

    og.resize_all_grids = function(active_tab) {
        var grids = $("#" + active_tab.id + " .x-grid-panel");
        for (var i = 0; i < grids.length; i++) {
            var g = Ext.getCmp($(grids[i]).attr('id'));
            if (g)
                g.fireEvent('resize');
        }
    }

    timeslots_module_grid.getView().override({
        getRowClass: function(record, index, rowParams, store) {
            return record.data.add_cls ? record.data.add_cls : "";
        }
    });

    timeslots_module_grid.load();

    function getSelectedIds() {
        var selections = sm.getSelections();
        if (selections.length <= 0) {
            return '';
        } else {
            var ret = '';
            for (var i = 0; i < selections.length; i++) {
                ret += "," + selections[i].data.object_id;
            }
            og.lastSelectedRow.messages = selections[selections.length - 1].data.ix;
            return ret.substring(1);
        }
    }
</script>
