<?php
Env::useHelper('dimension_widget');
Env::useHelper('tasks_widget_functions');

$header_toggle_groups = array();
if (SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks')) {
	$header_toggle_groups[] = array(
		'key'     => 'scope',
		'default' => 'mine',
		'options' => array(
			array('value' => 'mine', 'label' => lang('assigned to me')),
			array('value' => 'all',  'label' => lang('all')),
		),
	);
}
$header_toggle_groups[] = array(
	'key'     => 'due_filter',
	'default' => 'all',
	'options' => array(
		array('value' => 'all',     'label' => lang('all')),
		array('value' => 'today',   'label' => lang('due today')),
		array('value' => 'week',    'label' => lang('due this week')),
		array('value' => 'overdue', 'label' => lang('overdue')),
	),
);

$widget_data = evx_widgets_build_widget_data(array(
	'widget_name'     => 'tasks',
	'data_provider'   => 'tasks_widget_data_provider',
	'no_objects_text' => lang('no tasks to display'),
	'view_all_onclick' => function() {
		return "og.openLink(og.getUrl('task', 'new_list_tasks'), {caller:'tasks widget'}); event.stopPropagation(); return false;";
	},
	'header_toggle_groups' => $header_toggle_groups,
	'default_selected_columns' => array('name', 'progress', 'due_date', 'status', 'project'),
	'quick_actions_catalog' => tasks_widget_quick_actions_catalog(),
	'labels' => array(
		'noItems'           => lang('no tasks to display'),
		'save'              => lang('save'),
		'cancel'            => lang('cancel'),
		'searchColumns'     => lang('search columns'),
		'dragColumnsHint'   => lang('drag columns hint'),
		'noColumnsMatch'    => lang('no columns match'),
		'saveError'         => lang('save error'),
		'displayLines'      => lang('evx projects display lines'),
		'displayItemsUnit'  => lang('tasks'),
		'generalSettings'   => lang('evx projects general settings'),
		'widgetSettings'    => lang('evx projects widget settings'),
		'columns'           => lang('columns'),
		'available'         => lang('available'),
		'visible'           => lang('visible'),
		'selectAll'         => lang('select all'),
		'removeAll'         => lang('remove all'),
		'dragToReorder'     => lang('drag to reorder'),
		'selectColumnsHint' => lang('select columns hint'),
		'sortBy'            => lang('evx projects sort by'),
		'sortDirection'     => lang('evx projects sort direction'),
		'sortedBy'          => lang('evx projects sorted by'),
		'ascending'         => lang('evx projects ascending'),
		'descending'        => lang('evx projects descending'),
		'apply'             => lang('apply'),
		'quickActions'      => lang('tasks quick actions'),
		'quickActionsHint'  => lang('tasks quick actions hint'),
	),
));

if ($widget_data !== false) {
	include 'template.php';
}
