<?php
$genid = gen_id();
$grid_id = array_var($_REQUEST, 'current') . "_timeslots_module_grid";

$additional_actions_params = array('panel_id' => $grid_id);
$additional_actions = array();
Hook::fire('timeslot_list_additional_actions', $additional_actions_params, $additional_actions);

$show_billing = can_manage_billing(logged_user());

$current_filters = array_var($_SESSION, 'current_time_module_filters', array());
$active_members = array();
foreach (active_context() as $selection) {
    if ($selection instanceof Member)
        $active_members[] = $selection;
}
$can_add_timeslots = can_add_timeslots(logged_user(), $active_members);
?>

<div id="timePanelOverTbar" class="x-panel-tbar" style="width:100%;display:block;background-color:#F0F0F0;"></div>
<div id="timePanel" class="ogContentPanel" style="height:100%;">

    <div id="<?php echo $grid_id ?>_container" style="height:100%;"></div>

</div>


<script>

    var grid_id = '<?php echo $grid_id ?>';

    og.module_timeslots_grid = {

        grid_id: grid_id,

        clocks_to_start: [],

        name_renderer: function (value, p, r) {
            if (r.id == 'quick_add_row')
                return value;
            if (r.data.id == '__total_row__' || r.data.object_id <= 0)
                return '<span id="__total_row__">' + value + '</span>';
            var onclick = "og.openLink(og.getUrl('contact', 'view', {id: " + r.data.uid + "})); return false;";
            return String.format('<a href="#" onclick="{1}" title="{2}" style="font-size:120%;"><span class="bold">{0}</span></a>', og.clean(r.data.uname), onclick, og.clean(r.data.uname));
        },

        task_name_renderer: function (value, p, r) {
            if (r.id == 'quick_add_row')
                return value;
            if (r.data.id == '__total_row__' || r.data.object_id <= 0)
                return '';
            var onclick = "og.openLink(og.getUrl('task', 'view', {id: " + r.data.rel_object_id + "})); return false;";
            return String.format('<a href="#" onclick="{1}" title="{2}" class="underline">{0}</a>', og.clean(value), onclick, og.clean(value));
        },

        delete_timeslot: function (tid) {
            if (confirm('<?php echo escape_single_quotes(lang('confirm delete timeslot')) ?>')) {
                og.openLink(og.getUrl('time', 'delete_timeslot', {id: tid}), {
                    callback: function (success, data) {
                        var g = Ext.getCmp(og.module_timeslots_grid.grid_id);
                        if (g)
                            g.reset();
                    }
                });
            }
        },

        render_grid_actions: function (value, p, r) {
            var actions = '';
            if (r.id == 'quick_add_row' || r.data.id == '__total_row__' || r.data.id <= 0)
                return value;

            var actionStyle = ' style="font-size:105%;padding-top:2px;padding-bottom:3px;padding-left:16px;background-repeat:no-repeat;" ';

            if (r.data.end_time) {
                if (r.data.can_edit) {
                    actions += String.format(
                            '<a class="list-action ico-edit" href="#" onclick="og.render_modal_form(\'\', {c:\'time\', a:\'edit_timeslot\', params:{id:' + r.data.id + '}});" title="{0}" ' +
                            actionStyle + '>&nbsp;</a>', lang('edit')
                            );
                }
                if (r.data.can_delete) {
                    actions += String.format(
                            '<a class="list-action ico-delete" href="#" onclick="og.module_timeslots_grid.delete_timeslot(' + r.data.id + ');" title="{0}" ' +
                            actionStyle + '>&nbsp;</a>', lang('delete')
                            );
                }

            } else {

                var d = new Date(r.data.start_time_ts * 1000);
                var now_time = <?php echo DateTimeValueLib::now()->getTimestamp() * 1000; ?>;
                var html_id = '<?php echo $genid ?>_ts_' + r.data.id;

                actions += '<div class="open-ts-clock"><span id="' + html_id + 'timespan">';
                if (r.data.paused_on_ts > 0) {
                    var diff_m = 0, diff_h = 0;
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
                actions += '<a href="#" class="task-single-div" onclick="og.module_timeslots_grid.close_work(' + r.data.id + ');">';
                actions += '<div class="ogTasksTimeClock ico-time-stop task-action-icon" title="' + lang('close_work') + '"></div></a>';

                if (r.data.paused_on_ts) {
                    actions += '<a href="#" class="task-single-div" onclick="og.module_timeslots_grid.resume_work(' + r.data.id + ');">';
                    actions += '<div class="ogTasksTimeClock ico-time-play task-action-icon" title="' + lang('resume_work') + '"></div></a>'
                } else {
                    actions += '<a href="#" class="task-single-div" onclick="og.module_timeslots_grid.pause_work(' + r.data.id + ');">';
                    actions += '<div class="ogTasksTimeClock ico-time-pause task-action-icon" title="' + lang('pause_work') + '"></div></a>';
                }
                actions += '</div>';
            }

            return '<div>' + actions + '</div>';
        },

        close_work: function (tid) {
            this.execute_action("close", tid);
        },

        resume_work: function (tid) {
            this.execute_action("resume", tid);
        },

        pause_work: function (tid) {
            this.execute_action("pause", tid);
        },

        execute_action: function (action, id) {
            og.openLink(og.getUrl('timeslot', action, {id: id}));
        },

        start_clocks: function () {
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
    var system_columns = ['uid', 'uname', 'can_edit', 'can_delete', 'add_cls', 'start_time_ts', 'paused_on_ts', 'paused_time_sec', 'rel_object_id'];
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
        fixed: true,
        width: 100,
        renderer: og.module_timeslots_grid.render_grid_actions
    });

<?php
$add_columns = "";
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
            handler: function () {
                og.render_modal_form('', {c: 'time', a: 'add', params: {contact_id:<?php echo logged_user()->getId() ?>}});
            }
        }),

        start: new Ext.Button({
            iconCls: 'ico-time add-first-btn',
            text: '<?php echo lang('start work') ?>',
            id: 'start_work_btn',
            handler: function () {
                og.openLink(og.getUrl('timeslot', 'open'));
            }
        }),

        archive: new Ext.Action({
            text: lang('archive'),
            tooltip: lang('archive selected object'),
            iconCls: 'ico-archive-obj',
            disabled: true,
            handler: function () {
                if (confirm(lang('confirm archive selected objects'))) {
                    var cmp = Ext.getCmp(grid_id);
                    cmp.store.load({
                        params: Ext.apply({}, {
                            action: 'archive',
                            ids: getSelectedIds()
                        })
                    });
                }
            },
            scope: this
        }),

        trash: new Ext.Action({
            text: lang('move to trash'),
            tooltip: lang('move selected objects to trash'),
            iconCls: 'ico-trash',
            disabled: true,
            handler: function () {
                if (confirm(lang('confirm move to trash'))) {
                    var cmp = Ext.getCmp(grid_id);
                    cmp.store.load({
                        params: Ext.apply({}, {
                            action: 'trash',
                            ids: getSelectedIds()
                        })
                    });
                }
            },
            scope: this
        }),
        print: new Ext.Button({
            iconCls: 'ico-print',
            text: '<?php echo lang('generate report') ?>',
            id: 'ts_print_btn',
            handler: function () {
                og.openLink(og.getUrl('reporting', 'total_task_times_p'));
            }
        })
    };

    var timeslots_tbar_items = [];

<?php if ($can_add_timeslots) { ?>
        timeslots_tbar_items.push(botonera.newtimeslot);
        <?php if (user_config_option('show_start_time_action')) { ?>
            timeslots_tbar_items.push(botonera.start);
        <?php } ?>
<?php } ?>
    timeslots_tbar_items.push(botonera.archive);
    timeslots_tbar_items.push(botonera.trash);
    timeslots_tbar_items.push(botonera.print);


<?php foreach ($additional_actions as $add_action) { ?>
        timeslots_tbar_items.push(new Ext.Button({
            iconCls: '<?php echo array_var($add_action, 'cls') ?>',
            text: '<?php echo array_var($add_action, 'text') ?>',
            handler: function () {
    <?php if (isset($add_action['onclick'])) { ?>
                    eval("<?php echo $add_action['onclick'] ?>");
    <?php } else if (isset($add_action['url'])) { ?>
                    og.openLink("<?php echo $add_action['url'] ?>");
    <?php } ?>
            }
        }));
<?php } ?>


    // toolbar buttons to the right
    var timeslots_tbar_right_items = [];

    // filters
    var type_options = [
        [0, '<?php echo lang('all timeslots') ?>'], [1, '<?php echo escape_character(lang('task timeslots')) ?>'], [2, '<?php echo escape_character(lang('time timeslots')) ?>']
    ];
    if (og.additional_timeslot_type_filter_values) {
        for (var j = 0; j < og.additional_timeslot_type_filter_values.length; j++) {
            type_options.push(og.additional_timeslot_type_filter_values[j]);
        }
    }

    var add_row_user_options = [];
    var user_options = [[0, lang('everyone')]];
    
    var period_options = [[0, lang('no filter')],[1, lang('today')],[2, lang('this week')],[3, lang('last week+')],[4, lang('this month')],[5, lang('last month+')],[6, lang('select dates...')]];
    var period_ini_val = '<?php echo array_var($current_filters, 'period_filter', '1') ?>';
    
<?php foreach ($users as $user) { ?>
        user_options.push([<?php echo $user->getId() ?>, '<?php echo clean(escape_character($user->getObjectName())) ?>']);
        add_row_user_options.push([<?php echo $user->getId() ?>, '<?php echo clean(escape_character($user->getObjectName())) ?>']);
<?php } ?>

    var type_ini_val = '<?php echo array_var($current_filters, 'type_filter', '0') ?>';
    var user_ini_val = '<?php echo array_var($current_filters, 'user_filter', '0') ?>';

    if(period_ini_val == 6){
        var hidden_from = false;
        var hidden_to = false;
    }else{
        var hidden_from = true;
        var hidden_to = true;
    }

    var filters = {
        type_filter: {type: 'list', label: '&nbsp;' + lang('type') + ': ', options: type_options, width: 200, value: type_ini_val, initial_val: type_ini_val},
        user_filter: {type: 'list', label: '&nbsp;' + lang('person') + ': ', options: user_options, width: 150, value: user_ini_val, initial_val: user_ini_val},
        period_filter: {type: 'period', label: '&nbsp;' + lang('Time period') + ': ', options: period_options, value: period_ini_val, initial_val: period_ini_val},
        from_filter: {type: 'date', label:{id:'label_from_filter',hidden:hidden_from,text: '&nbsp;' + lang('from date') + ': '}, value: '<?php echo array_var($current_filters, 'from_filter') ?>',hidden:hidden_from},
        to_filter: {type: 'date', label: {id:'label_to_filter',hidden:hidden_to,text:'&nbsp;' + lang('to date') + ': '}, value: '<?php echo array_var($current_filters, 'to_filter') ?>',hidden:hidden_to}
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

    var topTbar = new Ext.Toolbar({
        style: 'border:0px none;',
        items: timeslots_tbar_items,
        renderTo: 'timePanelOverTbar'
    })


    var sm = new Ext.grid.CheckboxSelectionModel({
        listeners: {
            selectionchange: function () {
                if (sm.getCount() <= 0) {
                    botonera.trash.setDisabled(true);
                    botonera.archive.setDisabled(true);
                } else {
                    botonera.trash.setDisabled(false);
                    botonera.archive.setDisabled(false);
                }
            }
        }
    });

    var timeslots_module_grid = new og.ObjectGrid({
        genid: '<?php echo $genid ?>',
        renderTo: grid_id + '_container',
        url: og.getUrl('time', 'list_all'),
        type_name: 'none',
        response_objects_root: 'timeslots',
        grid_id: grid_id,
        nameRenderer: og.module_timeslots_grid.name_renderer,
        store_params: store_parameters,
        filters: filters,
        checkbox_sel_model: true,
        fixed_height: true,
        columns: timeslots_columns,
        //tbar_items: timeslots_tbar_items,
        tbar_right_items: timeslots_tbar_right_items,
        quick_add_row: <?php echo $can_add_timeslots ? '1' : '0' ?>,
        quick_add_row_fn: og.add_timeslot_module_quick_add_row,
        quick_add_row_user_options: add_row_user_options,
        add_default_actions_column: false,
        name_fixed: true,
        name_width: 200,
        stateId: 'timeslots-module',
        sm: sm
    });


    var active_tab = Ext.getCmp('tabs-panel').activeTab;
    if (active_tab.events.resize.listeners.length < 2) {
        active_tab.on('resize', function () {
            og.resize_all_grids(this);
        });
    }

    og.resize_all_grids = function (active_tab) {
        var grids = $("#" + active_tab.id + " .x-grid-panel");
        for (var i = 0; i < grids.length; i++) {
            var g = Ext.getCmp($(grids[i]).attr('id'));
            if (g)
                g.fireEvent('resize');
        }
    }

    timeslots_module_grid.getView().override({
        getRowClass: function (record, index, rowParams, store) {
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
