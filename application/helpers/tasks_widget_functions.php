<?php

/**
 * Data provider + helpers for the "tasks" dashboard widget (see
 * application/helpers/dimension_widget.php for the generic table shell that calls this).
 *
 * Lives in core (not evx_widgets) because it queries ProjectTasks/TaskController/TimeController
 * directly, matching the precedent of application/widgets/completed_tasks_list and
 * application/widgets/overdue_and_upcoming. Its dashboard-widget row registration lives in
 * public/install/installation/templates/sql/mysql_initial_data.php (fresh installs) and
 * public/upgrade/scripts/PaellaUpgradeScript.class.php (existing installations).
 */

/**
 * Format a duration as "Xh YYm". total_time_estimate/total_worked_time on project_tasks
 * are stored in MINUTES (see ProjectTask::updateTaskWithEstimatedAndWorkedTime(), which
 * assigns `total_worked_time` = $worked_minutes) — NOT seconds. Entering "10 hours" of
 * time previously showed as "10m" in this widget because this function divided the raw
 * minutes value as if it were seconds.
 */
function tasks_widget_format_minutes_as_hours_minutes($minutes) {
	$minutes = max(0, (int)$minutes);
	$h = intdiv($minutes, 60);
	$m = $minutes % 60;
	return $h . 'h ' . str_pad($m, 2, '0', STR_PAD_LEFT) . 'm';
}

/**
 * Status bucket -> badge color for the tasks widget's Status column. Real buckets only
 * (completed / overdue / in progress) — the mockup's fictional per-workflow statuses ("Ready
 * for Dev", "Level 3", ...) don't correspond to any real data on fo_project_tasks.
 *
 * @param string $label
 * @param string $bucket  'completed'|'overdue'|'in_progress'
 * @return string
 */
function tasks_widget_render_status_badge($label, $bucket) {
	$colors = array(
		'completed'   => array('bg' => '#e3f6f0', 'fg' => '#1f8a70'),
		'overdue'     => array('bg' => '#fbe4e2', 'fg' => '#c0392b'),
		'in_progress' => array('bg' => '#fdf1d9', 'fg' => '#b8860b'),
	);
	$c = isset($colors[$bucket]) ? $colors[$bucket] : $colors['in_progress'];
	return '<span class="evx-tasks-status" style="background:' . $c['bg'] . ';color:' . $c['fg'] . ';">' . clean($label) . '</span>';
}

/**
 * Render a cell for a possibly-multi-valued relation (e.g. a task linked to more than one
 * project member): the first value, plus a "+N" badge (hover title lists the rest) when
 * there's more than one — instead of silently showing only one value with no indication
 * there are others.
 *
 * @param string[] $values
 * @return string
 */
function tasks_widget_render_multi_value_cell($values) {
	if (empty($values)) {
		return '--';
	}
	$first = clean($values[0]);
	if (count($values) <= 1) {
		return $first;
	}
	$extra_count = count($values) - 1;
	$all_values = clean(implode(', ', $values));
	return $first . ' <span class="evx-tasks-multi-badge" title="' . $all_values . '">+' . $extra_count . '</span>';
}

/**
 * Catalog of the Tasks widget's row-hover "quick actions" — shared between the widget's
 * config (tasks/index.php, for the settings modal) and the data provider below (which
 * builds each row's kebab menu HTML from it).
 *
 * @return array [ ['key'=>, 'label'=>, 'icon'=>], ... ]
 */
function tasks_widget_quick_actions_catalog() {
	return array(
		array('key' => 'add-subtask',  'label' => lang('add subtask'),      'icon' => 'icon-list-plus'),
		array('key' => 'edit',         'label' => lang('edit'),             'icon' => 'icon-pencil-line'),
		array('key' => 'mark-started', 'label' => lang('mark as started'),  'icon' => 'icon-play'),
		array('key' => 'complete',     'label' => lang('complete'),        'icon' => 'icon-circle-check'),
		array('key' => 'add-time',     'label' => lang('add time entries'), 'icon' => 'icon-clock-plus'),
	);
}

/**
 * Data provider for the tasks widget (see dimension_widget.php for the generic
 * shell that calls this). Tasks aren't dimension members, so this queries
 * ProjectTasks::listing() directly instead of using evx_widgets_member_dimension_data_provider().
 *
 * v1 scope: name/progress/estimated/worked/due date/status/project columns, the "Assigned to
 * me / All" scope toggle and the "All / Due today / Due this week / Overdue" quick filter from
 * the mockup header, plus the row-hover quick-actions kebab — wired to real TaskController/
 * TimeController actions (add_task w/ parent_id for subtasks, edit_task, change_mark_as_started,
 * complete_task, and time/add for logging time), the same ones the rest of the app already uses.
 *
 * @param int    $limit
 * @param string $order_by             widget column key
 * @param string $order_dir            ASC|DESC
 * @param array  $toggle_values        ['scope' => 'mine'|'all', 'due_filter' => 'all'|'today'|'week'|'overdue']
 * @param string $genid                Widget instance id, for wiring reload-after-mutation calls
 * @param array  $quick_actions_order  Ordered [{key, visible}] resolved by the shell from tasks_widget_quick_actions_catalog()
 * @return array|false
 */
function tasks_widget_data_provider($limit, $order_by, $order_dir, $toggle_values = array(), $genid = null, $quick_actions_order = array()) {
	// Project isn't a real column on project_tasks (tasks link to it via the generic
	// object_members table, see the project-name lookup below), and Status is a bucket
	// computed in PHP (completed / overdue / in progress), not a stored column — both need
	// a raw SQL expression rather than a simple column name to be sortable.
	$customer_project_dim = Dimensions::findByCode('customer_project');
	$customer_project_dim_id = $customer_project_dim instanceof Dimension ? (int)$customer_project_dim->getId() : 0;

	// "Project" column label: respects the dimension's custom name set in Settings >
	// Dimension options ("Projects" row there renames the whole customer_project dimension,
	// not just the "project" object type — it's stored as DimensionOptions'
	// 'custom_dimension_name', the same option ApiController reads for the dimension's tab
	// label) instead of the fixed "Project" translation. Members::getTypeNameToShowByObjectType()
	// (object-type-level custom naming) is a DIFFERENT, unrelated setting that this
	// particular screen doesn't write to — it was empty in testing even after setting a
	// custom name here, which is what this comment used to (wrongly) assume was in play.
	$custom_dim_name = $customer_project_dim_id ? DimensionOptions::getOptionValue($customer_project_dim_id, 'custom_dimension_name') : null;
	$project_column_label = ($custom_dim_name && trim($custom_dim_name) !== '')
		? $custom_dim_name
		: lang('tasks project column');

	$order_column_map = array(
		'name'      => 'name',
		'progress'  => 'percent_completed',
		'due_date'  => 'due_date',
		'estimated' => 'total_time_estimate',
		'worked'    => 'total_worked_time',
		'status'    => "(CASE WHEN completed_by_id > 0 THEN 2 WHEN due_date != '" . EMPTY_DATETIME . "' AND due_date < NOW() THEN 0 ELSE 1 END)",
		'project'   => $customer_project_dim_id
			? "(SELECT MIN(o2.name) FROM " . TABLE_PREFIX . "object_members om2
			    JOIN " . TABLE_PREFIX . "members m2 ON m2.id = om2.member_id AND m2.dimension_id = " . $customer_project_dim_id . "
			    JOIN " . TABLE_PREFIX . "objects o2 ON o2.id = m2.object_id
			    WHERE om2.object_id = o.id)"
			: 'due_date',
	);
	$order = isset($order_column_map[$order_by]) ? $order_column_map[$order_by] : 'due_date';

	// Scope: "All" is only meaningful (and only ever applied) for users who already have
	// permission to see tasks assigned to others — otherwise the hard permission condition
	// below still forces "mine", exactly like before this toggle existed.
	$can_see_all = SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks');
	$scope = $can_see_all ? (isset($toggle_values['scope']) ? $toggle_values['scope'] : 'mine') : 'mine';
	$task_assignment_conditions = ($scope === 'mine') ? ' AND assigned_to_contact_id = ' . logged_user()->getId() : '';

	// Due-date quick filter: each option also forces due-date-ascending sort (nearest/most
	// overdue first), matching the mockup — but only for THIS request; it doesn't overwrite
	// the user's saved "Sort by" preference (see effective_order_by/dir below).
	$due_filter = isset($toggle_values['due_filter']) ? $toggle_values['due_filter'] : 'all';
	$due_conditions = '';
	$force_due_date_sort = in_array($due_filter, array('today', 'week', 'overdue'), true);
	if ($force_due_date_sort) {
		$tz_offset = -1 * logged_user()->getUserTimezoneValue();
		$today_start = DateTimeValueLib::now();
		$today_start->setHour(0); $today_start->setMinute(0); $today_start->setSecond(0);
		$today_start->add('s', $tz_offset);

		if ($due_filter === 'today') {
			$tomorrow_start = clone $today_start;
			$tomorrow_start->add('d', 1);
			$due_conditions = " AND due_date >= '" . $today_start->toMySQL() . "' AND due_date < '" . $tomorrow_start->toMySQL() . "'";
		} elseif ($due_filter === 'week') {
			$week_end = clone $today_start;
			$week_end->add('d', 7);
			$due_conditions = " AND due_date >= '" . $today_start->toMySQL() . "' AND due_date < '" . $week_end->toMySQL() . "'";
		} elseif ($due_filter === 'overdue') {
			$now_mysql = DateTimeValueLib::now()->toMySQL();
			$due_conditions = " AND completed_by_id = 0 AND due_date != '" . EMPTY_DATETIME . "' AND due_date < '" . $now_mysql . "'";
		}
		$order = 'due_date';
		$order_dir = 'ASC';
	}

	// Small helper so the "does this scope/due-filter combo have anything at all" checks below
	// (there are several, since falling back from "mine" to "all" needs to re-probe) don't each
	// repeat the same listing()-call boilerplate. $for_count_only mirrors the pre-existing
	// base_result check further down: no 'count_results', limit 1, just used for a >0 test.
	$fetch_tasks = function($assignment_conditions, $due_cond, $for_count_only = false) use ($order, $order_dir, $limit) {
		$args = array(
			'raw_data'                  => true,
			'fire_additional_data_hook' => false,
			'extra_conditions'          => ' AND is_template = 0' . $assignment_conditions . $due_cond,
		);
		if ($for_count_only) {
			$args['limit'] = 1;
		} else {
			$args['order']         = $order;
			$args['order_dir']     = $order_dir;
			$args['limit']         = $limit;
			$args['count_results'] = true; // without this, ->total is a "10000000 = more than the page"
			// sentinel (ContentDataObjects::listing() default), not a real count — and we display it as the title badge.
		}
		$result = ProjectTasks::instance()->listing($args);
		$objs   = (is_object($result) && isset($result->objects) && is_array($result->objects)) ? $result->objects : array();
		$total  = (is_object($result) && isset($result->total)) ? (int)$result->total : count($objs);
		return array($objs, $total);
	};

	list($tasks, $total) = $fetch_tasks($task_assignment_conditions, $due_conditions, false);

	// Effective scope actually used for the response — starts as the requested one, but falls
	// back to "all" below when "mine" has nothing to show at all, so the widget doesn't just
	// disappear for a user who happens to have no tasks assigned to themselves. Communicated back
	// via effective_toggle_values so the shell renders "All" (not "Assigned to me") as active,
	// without touching the user's saved toggle preference.
	$effective_scope = $scope;

	if ($total == 0) {
		if ($due_filter === 'all') {
			// No quick filter narrowed this: there really are no tasks for this scope at all.
			if ($scope === 'mine' && $can_see_all) {
				list($all_tasks, $all_total) = $fetch_tasks('', $due_conditions, false);
				if ($all_total == 0) {
					return false;
				}
				$effective_scope = 'all';
				$task_assignment_conditions = '';
				$tasks = $all_tasks;
				$total = $all_total;
			} else {
				return false;
			}
		} else {
			// A quick filter (e.g. "Due today") matched nothing, but that doesn't mean the widget
			// has nothing to show overall — check the scope without the due-date narrowing before
			// deciding whether to hide the widget or render it with an empty-state message (with
			// the toggles still visible, so the user can switch back to "All").
			list(, $base_total) = $fetch_tasks($task_assignment_conditions, '', true);
			if ($base_total == 0) {
				if ($scope === 'mine' && $can_see_all) {
					// "Mine" has nothing at all (not even outside the quick filter) — same
					// fallback as above, re-probing "all" both with and without the due filter.
					list(, $all_base_total) = $fetch_tasks('', '', true);
					if ($all_base_total == 0) {
						return false;
					}
					list($tasks, $total) = $fetch_tasks('', $due_conditions, false);
					$effective_scope = 'all';
					$task_assignment_conditions = '';
				} else {
					return false;
				}
			}
			// else: base_total > 0 in the current scope — render an empty-state message with
			// the toggles still visible, unchanged.
		}
	}

	// Project column: tasks aren't dimension members themselves, but they're linked to the
	// project's member (in the same "customer_project" dimension evx_projects reads from) via
	// the generic object_members table — one batch query for the whole page, reusing that
	// existing relationship instead of inventing a new one. A task can be linked to more than
	// one project member, so this collects ALL of them per task (not just the last row) and
	// the rendering below shows "+N" when there's more than one, instead of silently dropping
	// all but the last.
	$project_names = array();
	$task_ids = array_map(function($t) { return (int)$t['id']; }, $tasks);
	if (!empty($task_ids)) {
		if ($customer_project_dim_id) {
			$proj_rows = DB::executeAll(
				"SELECT om.object_id AS task_id, o.name AS project_name
				 FROM " . TABLE_PREFIX . "object_members om
				 JOIN " . TABLE_PREFIX . "members m ON m.id = om.member_id AND m.dimension_id = " . $customer_project_dim_id . "
				 JOIN " . TABLE_PREFIX . "objects o ON o.id = m.object_id
				 WHERE om.object_id IN (" . implode(',', $task_ids) . ")
				 ORDER BY o.name ASC"
			);
			if (is_array($proj_rows)) {
				foreach ($proj_rows as $pr) {
					$project_names[(int)$pr['task_id']][] = $pr['project_name'];
				}
			}
		}
	}

	$available_columns = array(
		array('key' => '__edit__',  'label' => '',                       'type' => 'native', 'is_html' => true, 'is_action' => true),
		array('key' => 'name',      'label' => lang('Name'),             'type' => 'native', 'is_html' => true),
		array('key' => 'progress',  'label' => lang('progress'),         'type' => 'native', 'is_html' => true),
		array('key' => 'estimated', 'label' => lang('estimated time'),   'type' => 'native', 'numeric' => true),
		array('key' => 'worked',    'label' => lang('worked time'),      'type' => 'native', 'numeric' => true),
		array('key' => 'due_date',  'label' => lang('due date'),         'type' => 'native', 'is_html' => true),
		array('key' => 'status',    'label' => lang('status'),           'type' => 'native', 'is_html' => true),
		array('key' => 'project',   'label' => $project_column_label,        'type' => 'native', 'is_html' => true),
	);
	if (!empty($quick_actions_order)) {
		$available_columns[] = array(
			'key' => '__quick_actions__', 'label' => '', 'type' => 'native',
			'is_html' => true, 'is_action' => true, 'is_trailing_action' => true,
		);
	}

	$orderable_columns = array(
		array('key' => 'name',      'label' => lang('Name')),
		array('key' => 'progress',  'label' => lang('progress')),
		array('key' => 'due_date',  'label' => lang('due date')),
		array('key' => 'estimated', 'label' => lang('estimated time')),
		array('key' => 'worked',    'label' => lang('worked time')),
		array('key' => 'status',    'label' => lang('status')),
		array('key' => 'project',   'label' => $project_column_label),
	);

	// Quick-actions kebab: build once per widget (catalog + reload call), then per-row (only
	// the task id / milestone id change). Reload after a mutation goes through
	// evxWidgetReload_<genid> since these rows are plain server-rendered HTML, not React state.
	$quick_actions_catalog_map = array();
	foreach (tasks_widget_quick_actions_catalog() as $qa) {
		$quick_actions_catalog_map[$qa['key']] = $qa;
	}
	$reload_call = $genid
		? "if (window['evxWidgetReload_" . $genid . "']) window['evxWidgetReload_" . $genid . "']();"
		: "window.location.reload();";
	$logged_user_id = logged_user()->getId();

	$now = DateTimeValueLib::now();
	$rows = array();
	foreach ($tasks as $task) {
		$due_date_str = '--';
		$is_overdue = false;
		if ($task['due_date'] != EMPTY_DATETIME) {
			$dd = DateTimeValueLib::makeFromString($task['due_date']);
			if ($dd instanceof DateTimeValue) {
				// format_date() (no explicit format) uses user_config_option('date_format') —
				// the same system-wide setting the real tasks list's due date column uses —
				// instead of friendly_date()'s relative/short format (which drops the year
				// for anything 7+ days out, e.g. "Aug, 06").
				$due_date_str = format_date($dd);
				$is_overdue = ((int)$task['completed_by_id'] === 0) && ($dd->getTimestamp() < $now->getTimestamp());
			}
		}

		if ((int)$task['completed_by_id'] > 0) {
			$status_bucket = 'completed';
			$status_label = lang('completed');
		} elseif ($is_overdue) {
			$status_bucket = 'overdue';
			$status_label = lang('overdue');
		} else {
			$status_bucket = 'in_progress';
			$status_label = lang('work in progress');
		}

		$task_id = (int)$task['id'];

		$quick_actions_html = '';
		if (!empty($quick_actions_order)) {
			// this.closest('.evx-task-menu').hidden = true closes the dropdown before opening a
			// modal/firing the action — the document-level "click outside closes menu" handler in
			// dimension_widget.php doesn't apply here since the click target is INSIDE the menu,
			// so without this the menu stayed open and rendered on top of/behind the new modal.
			$close_menu_call = "this.closest('.evx-task-menu').hidden = true;";
			$action_urls = array(
				'add-subtask'  => $close_menu_call . "og.render_modal_form('', {c:'task', a:'add_task', params:{milestone_id:" . (int)$task['milestone_id'] . ", parent_id:" . $task_id . ", reload:1}}); return false;",
				'edit'         => $close_menu_call . "og.render_modal_form('', {c:'task', a:'edit_task', params:{id:" . $task_id . "}}); return false;",
				'mark-started' => $close_menu_call . "og.openLink(og.getUrl('task','change_mark_as_started',{id:" . $task_id . ",quick:1}), {postProcess:function(ok){ if (ok) { " . $reload_call . " } }}); return false;",
				'complete'     => $close_menu_call . "og.openLink(og.getUrl('task','complete_task',{id:" . $task_id . "}), {postProcess:function(ok){ if (ok) { " . $reload_call . " } }}); return false;",
				'add-time'     => $close_menu_call . "og.render_modal_form('', {c:'time', a:'add', params:{object_id:" . $task_id . ", contact_id:" . $logged_user_id . "}}); return false;",
			);

			$menu_items_html = '';
			foreach ($quick_actions_order as $qa_entry) {
				if (empty($qa_entry['visible'])) continue;
				$key = $qa_entry['key'];
				if (!isset($quick_actions_catalog_map[$key]) || !isset($action_urls[$key])) continue;
				$def = $quick_actions_catalog_map[$key];
				$menu_items_html .= '<button type="button" class="evx-task-menu-item" onclick="' . $action_urls[$key] . '">' .
					'<i class="' . clean($def['icon']) . '"></i><span>' . clean($def['label']) . '</span>' .
					'</button>';
			}

			if ($menu_items_html !== '') {
				$quick_actions_html =
					'<button type="button" class="evx-task-kebab" data-menu-target="evx-task-menu-' . $task_id . '" title="' . lang('tasks quick actions') . '">' .
						'<i class="icon-ellipsis-vertical"></i>' .
					'</button>' .
					'<div class="evx-task-menu" id="evx-task-menu-' . $task_id . '" hidden>' . $menu_items_html . '</div>';
			}
		}

		$rows[] = array(
			'id'     => $task_id,
			'values' => array(
				'__edit__'  => '<a class="evx-widget-edit-icon" href="#" ' .
					'onclick="og.render_modal_form(\'\', {c:\'task\', a:\'edit_task\', params:{id:' . $task_id . '}}); return false;" ' .
					'title="' . lang('edit') . '">' .
					'<i class="icon-pencil-line"></i>' .
					'</a>',
				'name'      => '<a href="' . get_url('task', 'view', array('id' => $task_id)) . '" class="evx-widget-cell-title" '
					. 'onclick="event.stopPropagation(); og.openLink(og.getUrl(\'task\',\'view\',{id:' . $task_id . '})); return false;">'
					. clean($task['name']) . '</a>',
				'progress'  => evx_widgets_render_percent_completed((int)$task['percent_completed']),
				'due_date'  => $is_overdue
					? '<span style="color:#c0392b;font-weight:600;">' . clean($due_date_str) . '</span>'
					: clean($due_date_str),
				'estimated' => tasks_widget_format_minutes_as_hours_minutes((int)$task['total_time_estimate']),
				'worked'    => tasks_widget_format_minutes_as_hours_minutes((int)$task['total_worked_time']),
				'status'    => tasks_widget_render_status_badge($status_label, $status_bucket),
				'project'   => tasks_widget_render_multi_value_cell(isset($project_names[$task_id]) ? $project_names[$task_id] : array()),
				'__quick_actions__' => $quick_actions_html,
			),
		);
	}

	$result_data = array(
		'total'             => $total,
		'available_columns' => $available_columns,
		'orderable_columns' => $orderable_columns,
		'rows'              => $rows,
		'widget_title'      => lang('tasks'),
		'title_count'       => $total,
	);
	if ($force_due_date_sort) {
		$result_data['effective_order_by']  = 'due_date';
		$result_data['effective_order_dir'] = 'ASC';
	}
	if ($effective_scope !== $scope) {
		$result_data['effective_toggle_values'] = array('scope' => $effective_scope);
	}
	return $result_data;
}
