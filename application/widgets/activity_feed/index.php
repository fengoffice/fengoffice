<?php
Env::useHelper('dimension_widget');
Env::useHelper('activity_feed_functions');

$widget_data = evx_widgets_build_feed_widget_data(array(
	'widget_name'     => 'activity_feed',
	'data_provider'   => 'activity_feed_data_provider',
	'no_objects_text' => lang('no activity to display'),
	'extra_filters' => array(
		array('key' => 'show_time_entries', 'label' => lang('view time entries'), 'type' => 'checkbox', 'default' => '1'),
		array(
			'key'     => 'user_id',
			'label'   => lang('filter by user'),
			'type'    => 'select',
			'options' => array_merge(
				array(array('value' => '', 'label' => lang('all users'))),
				activity_feed_get_user_options()
			),
			'default' => '',
		),
	),
	'labels' => array(
		'noItems'          => lang('no activity to display'),
		'save'             => lang('save'),
		'cancel'           => lang('cancel'),
		'saveError'        => lang('save error'),
		'displayLines'     => lang('evx projects display lines'),
		'displayItemsUnit' => lang('activity'),
		'generalSettings'  => lang('evx projects general settings'),
		'widgetSettings'   => lang('evx projects widget settings'),
		'apply'            => lang('apply'),
	),
));

if ($widget_data !== false) {
	include 'template.php';
}
