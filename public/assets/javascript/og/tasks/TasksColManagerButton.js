/**
 * og.TasksColManagerButton — column manager button for the Tasks top toolbar.
 *
 * Extracted from TasksTopToolbar.js to keep each concern in its own file.
 * Call og.TasksColManagerButton(tbar) after tbar.show_menu has been created.
 */
(function () {

// Maps DOM column IDs (tasks_list_cols / localStorage) → menu item IDs.
// Used by the column manager handler to order the right panel like the task list header.
var DOM_TO_MENU_ID = {
	'task_assigned_to_id': 'show_assigned_to',
	'task_name':           'show_task_name',
	'task_assigned_by_id': 'show_by',
	'task_clasification':  'show_classification',
	'task_completed_bar':  'show_percent_completed_bar',
	'task_start_date':     'show_start_dates',
	'task_due_date':       'show_end_dates',
	'task_estimated':      'show_time_estimates',
	'task_total_estimated':'show_total_time_estimates',
	'task_pending':        'show_time_pending',
	'task_worked':         'show_time_worked',
	'task_total_worked':   'show_total_time_worked',
	'task_remaining':      'show_remaining_time',
	'task_total_remaining':'show_total_remaining_time',
	'task_quick_actions':  'show_actions_col'
	// task_clasification{N} → show_dim_{N}  (resolved dynamically in handler)
	// cp_{N}               → tasksShowCP_{N} (resolved dynamically in handler)
};

// Reverse of DOM_TO_MENU_ID — used in Apply to save the new column order to localStorage.
var MENU_TO_DOM_ID = {
	'show_assigned_to':          'task_assigned_to_id',
	'show_task_name':            'task_name',
	'show_by':                   'task_assigned_by_id',
	'show_classification':       'task_clasification',
	'show_percent_completed_bar':'task_completed_bar',
	'show_start_dates':          'task_start_date',
	'show_end_dates':            'task_due_date',
	'show_time_estimates':       'task_estimated',
	'show_total_time_estimates': 'task_total_estimated',
	'show_time_pending':         'task_pending',
	'show_time_worked':          'task_worked',
	'show_total_time_worked':    'task_total_worked',
	'show_remaining_time':       'task_remaining',
	'show_total_remaining_time': 'task_total_remaining',
	'show_actions_col':          'task_quick_actions'
	// show_dim_{N}    → task_clasification{N} (resolved dynamically in Apply)
	// tasksShowCP_{N} → cp_{N}               (resolved dynamically in Apply)
};

// Menu IDs of columns that are always visible and cannot be preference-controlled.
// The task name is structurally required (it anchors the inline add-task button and
// carries the group "Total:" label). The merged Actions column is also always visible
// per its own design (position is configurable, but it can't be hidden entirely). The
// "To" column is a normal preference-controlled column (tasksShowAssignedTo), not locked.
var LOCKED_COL_IDS = { 'show_task_name': 1, 'show_actions_col': 1 };

// The 6 quick actions manageable inside the merged Actions column's nested
// reorder sub-list, keyed by the same act_order_key used in drawing.js's
// taskActions sort, mapped to their "Show" dropdown lang label.
var ACTION_GROUP_LABELS = {
	'add_sub_task':    'quick add sub tasks',
	'edit':            'quick edit',
	'mark_as_started': 'quick mark as started',
	'complete':        'quick complete',
	'quick_time':      'quick add time',
	'time':            'quick start clock'
};

// Preference-name map for built-in column items (dynamic items use their id as the pref name)
var COL_PREF_NAMES = {
	'show_assigned_to':          'tasksShowAssignedTo',
	'show_by':                   'tasksShowAssignedBy',
	'show_time':                 'tasksShowTime',
	'show_time_quick':           'tasksShowTimeQuick',
	'show_start_dates':          'tasksShowStartDates',
	'show_end_dates':            'tasksShowEndDates',
	'show_time_estimates':       'tasksShowTimeEstimates',
	'show_total_time_estimates': 'tasksShowTotalTimeEstimates',
	'show_time_pending':         'tasksShowTimePending',
	'show_time_worked':          'tasksShowTimeWorked',
	'show_total_time_worked':    'tasksShowTotalTimeWorked',
	'show_remaining_time':       'tasksShowRemainingTime',
	'show_total_remaining_time': 'tasksShowTotalRemainingTime',
	'show_percent_completed_bar':'tasksShowPercentCompletedBar',
	'show_quick_edit':           'tasksShowQuickEdit',
	'show_quick_mark_as_started':'tasksShowQuickMarkAsStarted',
	'show_quick_complete':       'tasksShowQuickComplete',
	'show_quick_add_sub_tasks':  'tasksShowQuickAddSubTasks',
	'show_classification':       'tasksShowClassification'
};

// TotalCols side-effects for time-related columns
var TOTAL_COLS_MAP = {
	'show_time_estimates':       { key: 'estimatedTime',     val: { title: 'estimated',          group_total_field: 'TimeEstimate',        row_field: 'estimatedTime' } },
	'show_total_time_estimates': { key: 'totalEstimatedTime', val: { title: 'total estimated',    group_total_field: 'TotalTimeEstimate',   row_field: 'totalTimeEstimateString' } },
	'show_time_pending':         { key: 'pendingTime',        val: { title: 'pending',             group_total_field: 'pending_time',        row_field: 'pending_time_string' } },
	'show_time_worked':          { key: 'workedTime',         val: { title: 'worked',              group_total_field: 'worked_time',         row_field: 'worked_time_string' } },
	'show_total_time_worked':    { key: 'totalWorkedTime',    val: { title: 'total worked',        group_total_field: 'overall_worked_time', row_field: 'overall_worked_time_string' } },
	'show_remaining_time':       { key: 'remainingTime',      val: { title: 'remaining time',      group_total_field: 'remaining_time',      row_field: 'remaining_time_string' } },
	'show_total_remaining_time': { key: 'totalRemainingTime', val: { title: 'total remaining time',group_total_field: 'total_remaining_time', row_field: 'total_remaining_time_string' } }
};

og.TasksColManagerButton = function (tbar) {
	tbar.col_manager_btn = new Ext.Action({
		id:      'tasks-col-manager-btn',
		iconCls: 'btn btn-sm',
		text:    '<i class="icon-settings"></i>' + lang('columns'),
		handler: function () {
			if (tbar._cmgrWinOpen) return;

			// Build idToItem of all column-manager columns
			var idToItem = {};
			var allItems = tbar.show_menu.items[0].menu.items.items;
			for (var ci = 0; ci < allItems.length; ci++) {
				var mi = allItems[ci];
				if (mi.id && mi.cmgrColumn && mi.featureEnabled) idToItem[mi.id] = mi;
			}

			// Order cols to match the current task list visual order.
			// Source of truth: localStorage drag order or tasks_list_cols default order.
			var cols = [];
			var addedMenuIds = {};
			var currentColIds = ogTasks.loadColOrder();
			if (!currentColIds && ogTasks.TasksList && ogTasks.TasksList.tasks_list_cols) {
				currentColIds = ogTasks.TasksList.tasks_list_cols.map(function (c) { return c.id; });
			}
			if (currentColIds) {
				for (var di = 0; di < currentColIds.length; di++) {
					var domId = currentColIds[di];
					var menuId = DOM_TO_MENU_ID[domId];
					if (!menuId) {
						if (/^task_clasification.+/.test(domId)) {
							menuId = 'show_dim_' + domId.replace('task_clasification', '');
						} else if (/^cp_/.test(domId)) {
							menuId = 'tasksShowCP_' + domId.replace('cp_', '');
						} else {
							menuId = domId; // additional columns share id between DOM and menu
						}
					}
					if (menuId && idToItem[menuId] && !addedMenuIds[menuId]) {
						cols.push({ id: idToItem[menuId].id, header: idToItem[menuId].text, hidden: !idToItem[menuId].checked });
						addedMenuIds[menuId] = 1;
					}
				}
			}
			// Append col-manager items not in the current list (show_time, show_time_quick, etc.)
			for (var k in idToItem) {
				if (!addedMenuIds[k]) {
					cols.push({ id: idToItem[k].id, header: idToItem[k].text, hidden: !idToItem[k].checked });
				}
			}

			// Attach the nested quick-action reorder sub-list to the merged Actions column.
			// og.config.tasks_columns_config.actionsOrder is the server-persisted source of
			// truth (refreshed on every panel reset/reload); ogTasks.userPreferences.actionsOrder
			// is only an in-memory cache set right after Apply, for the brief window before a
			// reset happens — it is NOT part of the server-rendered $userPref hash, so it does
			// not survive a reset and must never be checked ahead of the server value.
			var _actionsOrder = (og.config.tasks_columns_config && og.config.tasks_columns_config.actionsOrder)
				|| (ogTasks.userPreferences && ogTasks.userPreferences.actionsOrder)
				|| ogTasks.DEFAULT_ACTIONS_ORDER;
			for (var sci = 0; sci < cols.length; sci++) {
				if (cols[sci].id === 'show_actions_col') {
					cols[sci].subItems = _actionsOrder.map(function (key) {
						return { key: key, label: lang(ACTION_GROUP_LABELS[key] || key) };
					});
					break;
				}
			}

			new og.ColumnManagerWindow({
				id:        'tasks-list-colmgr',
				owner:     tbar,
				columns:   cols,
				lockedIds: LOCKED_COL_IDS,
				onApply: function (colState) {
					var menuItems = tbar.show_menu.items[0].menu.items.items;
					var names  = [];
					var values = [];

					var dimColStates = {}; // dim value → visible (bool)

					for (var ai = 0; ai < colState.length; ai++) {
						var cs = colState[ai];

						if (LOCKED_COL_IDS[cs.id]) continue; // always visible, no preference

						// Update hidden menu-item states so getDrawOptions() reads correct values
						for (var bi = 0; bi < menuItems.length; bi++) {
							if (menuItems[bi].id === cs.id) {
								menuItems[bi].setChecked(!cs.hidden, true);
								break;
							}
						}

						if (/^show_dim_/.test(cs.id)) {
							// Dimension cols share a single array preference — handled after loop
							dimColStates[cs.id.replace('show_dim_', '')] = !cs.hidden;
							continue;
						}

						// Update TotalCols side-effects immediately
						var tc = TOTAL_COLS_MAP[cs.id];
						if (tc) {
							if (!cs.hidden) { ogTasks.TotalCols[tc.key] = tc.val; }
							else            { delete ogTasks.TotalCols[tc.key]; }
						}

						names.push(COL_PREF_NAMES[cs.id] || cs.id);
						values.push(cs.hidden ? 0 : 1);
					}

					// Persist the right-panel order to localStorage so applyColOrder() uses it after reset
					var _newColOrder = ['task_actions'];
					var _addedDom = { 'task_actions': 1 };
					for (var oi = 0; oi < colState.length; oi++) {
						if (colState[oi].hidden) continue;
						var _mid = colState[oi].id;
						var _did = MENU_TO_DOM_ID[_mid];
						if (!_did) {
							if (/^show_dim_/.test(_mid))     _did = 'task_clasification' + _mid.replace('show_dim_', '');
							else if (/^tasksShowCP_/.test(_mid)) _did = 'cp_' + _mid.replace('tasksShowCP_', '');
							else _did = _mid;
						}
						if (_did && !_addedDom[_did]) { _newColOrder.push(_did); _addedDom[_did] = 1; }
					}
					// Ensure always-visible columns are in the saved order. "To" is no longer
					// locked (it's a normal preference-controlled column, tasksShowAssignedTo),
					// so it's intentionally not force-added here — if hidden, it stays out.
					if (!_addedDom['task_name'])          _newColOrder.push('task_name');
					if (!_addedDom['task_quick_actions']) _newColOrder.push('task_quick_actions');
					ogTasks.saveColOrder(_newColOrder);

					// Pull the reordered quick-action keys back out of the Actions column's
					// colState entry (its subOrder, populated by ColumnManagerWindow's nested
					// drag list) and stash it for immediate use + server persistence.
					var _actionsOrderOut = null;
					for (var acsi = 0; acsi < colState.length; acsi++) {
						if (colState[acsi].id === 'show_actions_col' && colState[acsi].subOrder) {
							_actionsOrderOut = colState[acsi].subOrder;
							break;
						}
					}
					if (_actionsOrderOut) {
						ogTasks.userPreferences.actionsOrder = _actionsOrderOut;
						if (og.config.tasks_columns_config) {
							og.config.tasks_columns_config.actionsOrder = _actionsOrderOut;
						}
					}

					// Build updated showDimensionCols array and queue as single preference
					if (Object.keys(dimColStates).length > 0) {
						var newDimCols = [];
						var existingDims = ogTasks.userPreferences.showDimensionCols || [];
						// Retain dim IDs not managed by this col manager session
						for (var ei = 0; ei < existingDims.length; ei++) {
							if (!dimColStates.hasOwnProperty(String(existingDims[ei]))) {
								newDimCols.push(existingDims[ei]);
							}
						}
						// Add visible dim IDs from colState
						for (var dv in dimColStates) {
							if (dimColStates[dv]) newDimCols.push(dv);
						}
						ogTasks.userPreferences.showDimensionCols = newDimCols;
						names.push('tasksShowDimensionCols');
						values.push(newDimCols.toString());
					}

					// Serialize: prefs → column config → reload.
					// By the time tp.reset() fires the server has both the new preferences
					// and the new colOrder, so _applyServerColumnsConfig() reads the correct value.
					og.openLink(og.getUrl('account', 'update_user_preferences'), {
						post:        { names: JSON.stringify(names), values: JSON.stringify(values) },
						hideLoading: true,
						callback:    function () {
							var _colCfgColIds = {};
							try { _colCfgColIds = JSON.parse(localStorage['tasksPanelContainer_colIds'] || '{}'); } catch (e) {}
							og.openLink(og.getUrl('task', 'save_tasks_columns_config'), {
								hideLoading: true,
								post: {
									config: JSON.stringify({
										colIds:   _colCfgColIds,
										colPos:   localStorage['tasksPanelContainer'] || '',
										colOrder: _newColOrder,
										actionsOrder: _actionsOrderOut || ogTasks.userPreferences.actionsOrder || ogTasks.DEFAULT_ACTIONS_ORDER
									})
								},
								callback: function () {
									ogTasks._skipNextScheduledSave = true;
									var tp = Ext.getCmp("tasks-panel");
									if (tp) tp.reset();
								}
							});
						}
					});
					return true;
				}
			});
		}
	});

	tbar.add(tbar.col_manager_btn);
};

}());
