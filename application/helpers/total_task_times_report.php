<?php

/**
 * Shared column resolution for the Time module's "task time report" (total_task_times*).
 * Used by both the on-screen/print HTML template (total_task_times.php) and the CSV export
 * (ReportingController::cvs_total_task_times_table()) so both outputs always show the same
 * columns, in the same order, resolved the same way.
 */

/**
 * Effective list of report columns, with a fallback used consistently everywhere a
 * column count/colspan is derived, so headers, rows and group totals never disagree
 * on how many columns the table has.
 */
function total_task_times_get_report_columns($options) {
	$report_columns = array_var($options, 'columns', array());
	if (!is_array($report_columns) || count($report_columns) == 0) {
		$report_columns = array('name', 'description', 'rel_object_name', 'start_time', 'worked_time');
	}

	// "Show billing information"/"Show costs information" are meant to control whether billing/cost
	// data appears in the report at all. Without this, unchecking them had no visible effect whenever
	// the captured grid columns already included a billing/cost column (the common case, since
	// "Billing" is usually visible in the Time module grid) — the checkbox looked like it did nothing.
	if (array_var($options, 'show_billing') != 'checked') {
		$report_columns = array_values(array_diff($report_columns, array('fixed_billing', 'hourly_billing')));
	}
	if (array_var($options, 'show_cost') != 'checked') {
		$report_columns = array_values(array_diff($report_columns, array('fixed_cost', 'hourly_cost')));
	}

	return $report_columns;
}

/**
 * Builds the same per-row field data the Time module grid uses (Timeslot::getArrayInfo()
 * merged with the general object info, plugin hook columns, and additional column value hooks),
 * so report columns show exactly what the grid shows for that column.
 */
function total_task_times_build_row_info(Timeslot $ts) {
	// getObject() returns the underlying FengObject (not a ContentDataObject) and can be null
	// for an orphaned timeslot (its underlying object row was deleted without cleaning up the
	// FK) — guard against that so one bad row doesn't fatal the whole report
	$obj = $ts->getObject();
	$general_info = $obj instanceof FengObject ? $obj->getArrayInfo() : array();
	$info = array_merge($ts->getArrayInfo(true, true, true), $general_info);

	$add_columns = array();
	Hook::fire('view_timeslot_render_more_columns', $ts, $add_columns);
	if (is_array($add_columns)) {
		foreach ($add_columns as $col_id => $val) {
			$info[$col_id] = $val;
		}
	}

	Hook::fire('timeslots_list_additional_column_values', array('timeslot' => $ts), $info);

	return $info;
}

function total_task_times_get_column_label($col_id) {
	switch ($col_id) {
		case 'name': return lang('person');
		case 'description': return lang('description');
		case 'qbo_sync_status': return lang('quickbooks');
		case 'start_time': return lang('start time');
		case 'end_time': return lang('end time');
		case 'worked_time': return lang('worked time');
		case 'subtract': return lang('paused time');
		case 'rel_object_name': return lang('task');
		case 'dateCreated': return lang('created on');
		case 'createdBy': return lang('created by');
		case 'dateUpdated': return lang('last updated on');
		case 'updatedBy': return lang('last updated by');
		case 'fixed_billing': return lang('billing');
	}
	if (str_starts_with($col_id, 'cp_')) {
		$cp = CustomProperties::instance()->findById((int) substr($col_id, 3));
		if ($cp instanceof CustomProperty) return $cp->getName();
	} else if (str_starts_with($col_id, 'dim_')) {
		$dim = Dimensions::getDimensionById((int) substr($col_id, 4));
		if ($dim instanceof Dimension) return $dim->getName();
	}
	return lang($col_id);
}

function total_task_times_get_column_css_class($col_id) {
	switch ($col_id) {
		case 'worked_time':
		case 'subtract':
		case 'fixed_billing':
			return 'time nobr right';
		case 'start_time':
		case 'end_time':
			return 'date';
		case 'rel_object_name':
		case 'description':
			return 'name';
		case 'name':
			return 'person';
	}
	return 'nobr';
}

/**
 * Plain-text value for $col_id (no HTML), used by the CSV export. Strips markup that
 * hook-contributed columns (e.g. quickbooks status) may return as HTML.
 */
function total_task_times_get_column_text_value(Timeslot $ts, $col_id, $info) {
	$value = total_task_times_get_column_value($ts, $col_id, $info);
	return trim(html_entity_decode(strip_tags($value), ENT_QUOTES));
}

/**
 * Quotes a CSV field per RFC 4180 (wraps in double quotes, escapes internal double
 * quotes as ""), after collapsing newlines to spaces. Used instead of the old approach
 * of stripping commas out of values, which silently corrupted data containing commas.
 */
function total_task_times_csv_escape($value) {
	$value = str_replace(array("\r\n", "\r", "\n"), " ", (string) $value);
	return '"' . str_replace('"', '""', $value) . '"';
}

function total_task_times_get_column_value(Timeslot $ts, $col_id, $info) {
	switch ($col_id) {
		case 'name':
			return clean(array_var($info, 'uname'));
		case 'description':
			return nl2br(clean($ts->getDescription()));
		case 'start_time':
			return $ts->getStartTime() instanceof DateTimeValue ? format_datetime($ts->getStartTime()) : '';
		case 'end_time':
			return $ts->getEndTime() instanceof DateTimeValue ? format_datetime($ts->getEndTime()) : '';
		case 'worked_time':
			return format_time_column_value($ts->getMinutes());
		case 'subtract':
			return array_var($info, 'subtract', '');
		case 'rel_object_name':
			return $ts->getRelObjectId() == 0 ? clean($ts->getObjectName()) : clean($ts->getRelObject()->getObjectName());
		case 'dateCreated':
		case 'createdBy':
		case 'dateUpdated':
		case 'updatedBy':
			return clean(array_var($info, $col_id, ''));
		case 'fixed_billing':
			$currency = Currencies::instance()->getCurrency($ts->getRateCurrencyId());
			$c_symbol = $currency instanceof Currency ? $currency->getSymbol() : config_option('currency_code', '$');
			return $c_symbol . " " . number_format($ts->getFixedBilling(), 2);
	}

	if (str_starts_with($col_id, 'cp_')) {
		$cp = CustomProperties::instance()->findById((int) substr($col_id, 3));
		return $cp instanceof CustomProperty ? get_custom_property_value_for_listing($cp, $ts) : '';
	}

	if (str_starts_with($col_id, 'dim_')) {
		// $col_id comes straight from the user-submitted report[columns][] POST field; cast to
		// int before it reaches ObjectMembers::getMembersByObjectAndDimension(), which interpolates
		// $dimension_id directly into raw SQL — an uncast value here is a SQL injection vector
		$dim_id = (int) substr($col_id, 4);
		$members = ObjectMembers::getMembersByObjectAndDimension($ts->getId(), $dim_id, "AND om.is_optimization = 0");
		$names = array();
		if (is_array($members)) {
			foreach ($members as $m) {
				if ($m instanceof Member) $names[] = clean($m->getDisplayName());
			}
		}
		return implode(', ', $names);
	}

	// any other column contributed by a plugin hook (e.g. quickbooks status): reuse the
	// same value the time module grid itself shows for this row/column
	return isset($info[$col_id]) ? $info[$col_id] : '';
}
