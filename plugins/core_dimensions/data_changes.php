<?php 

/**
 * Recalculate worked time for each grandparent task.
 * This function will update the total worked time for all project tasks that have 2 or more levels of childs.
 */
function core_dimensions_data_changes_20_21() {
	// Get all tasks that have a depth of 2 or more
	$grand_childs = ProjectTasks::instance()->findAll(array(
		'conditions' => 'depth > 1',
	));

	// Initialize an array of tasks to recalculate
	$tasks_to_recalculate = [];

	foreach ($grand_childs as $grand_child) {
		if ($grand_child instanceof ProjectTask) {
			// Get the parent of the grandchild task
			$parent = $grand_child->getParent();

			// Check if the parent is a project task
			if ($parent instanceof ProjectTask) {
				// Get the grandparent of the task
				$grand_parent = $parent->getParent();

				// Check if the grandparent is a project task
				if ($grand_parent instanceof ProjectTask) {
					// Add the grandparent to the array of tasks to recalculate
					$tasks_to_recalculate[$grand_parent->getId()] = $grand_parent;
				}
			}
		}
	}

	// Recalculate the total worked time for each task in the array
	foreach ($tasks_to_recalculate as $task) {
		if ($task instanceof ProjectTask) {
			// Calculate the total worked time for the task
			$task->calculateAndSetOverallTotalWorkedTime();
		}
	}
}

function core_dimensions_data_changes_21_22() {
	// initialize task list user preferences for group by and order allowed columns
	ProjectTasks::instance()->initTaskListUserPreferences();
}

/**
 * Increases the tasks pagination count for existing contact config options values
 *
 * @return void
 */
function core_dimensions_data_changes_22_23() {
	// Get the contact config option for the tasks groups pagination count
	$opt = ContactConfigOptions::getByName('tasksGroupsPaginationCount');
	$new_groups_pagination_count = 15;
	if ($opt instanceof ContactConfigOption) {
		// Set the default value to $new_groups_pagination_count and save
		$opt->setDefaultValue($new_groups_pagination_count);
		$opt->save();

		// Get all contact config option values for the tasks groups pagination count
		$opt_values = ContactConfigOptionValues::instance()->findAll(array('conditions' => 'option_id = '.$opt->getId()));

		// Loop through the contact config option values and set the value to $new_groups_pagination_count 
		// if it is less than $new_groups_pagination_count
		foreach ($opt_values as $opt_value) {
			if ($opt_value->getValue() < $new_groups_pagination_count) {
				$opt_value->setValue($new_groups_pagination_count);
				$opt_value->save();
			}
		}
	}
}

/**
 * Recalculate member display names for object types with custom display name configuration.
 * Ensures address custom properties are formatted instead of showing raw pipe-separated values.
 */
function core_dimensions_data_changes_23_24() {
	@set_time_limit(0);
	Env::useHelper('application');
	Env::useHelper('format');
	Env::useHelper('update_text_to_show_in_trees');

	$ot_ids = array();

	$rows = DB::executeAll("
		SELECT DISTINCT object_type_id
		FROM ".TABLE_PREFIX."dimension_object_type_options
		WHERE name = 'text_to_show_in_trees' AND value != '' AND value IS NOT NULL
	");
	foreach ($rows as $row) {
		$ot_ids[] = $row['object_type_id'];
	}

	// The object_subtype_id column is created by the object_subtypes plugin (update 7->8),
	// so it may not exist if that plugin is not installed or not up to date
	if (check_column_exists(TABLE_PREFIX."dimension_object_type_options", "object_subtype_id")) {
		$subtype_rows = DB::executeAll("
			SELECT DISTINCT object_subtype_id
			FROM ".TABLE_PREFIX."dimension_object_type_options
			WHERE name LIKE 'text_to_show_in_trees_ostId_%' AND value != '' AND value IS NOT NULL AND object_subtype_id > 0
		");
		foreach ($subtype_rows as $row) {
			$ot_ids[] = 'ostId_' . $row['object_subtype_id'];
		}
	}

	$ot_ids = array_unique($ot_ids);
	if (count($ot_ids) > 0) {
		recalculate_members_custom_display_names($ot_ids);
	}
}

/** 
 * Seed the role-based default list-column configuration (role_id = 0 fallback) from the
 * shipped portable defaults, resolving the {{...}} tokens to this install's custom property,
 * dimension and object type ids. Runs with full helpers available.
 *
 * @return void
 */
function core_dimensions_data_changes_24_25() {
	Env::useHelper('role_default_list_config');
	require_once ROOT . '/plugins/core_dimensions/install/role_default_list_config_seed_data.php';
	if (function_exists('role_default_list_config_seed_data') && function_exists('role_default_list_config_seed')) {
		role_default_list_config_seed(role_default_list_config_seed_data(), 0);
	}
}

/**
 * Re-seed the role-based default list-column configuration: the tasks list layout shipped in
 * 24_25 had its columns in the wrong order, because the layout was captured from the reference
 * install while the tasks list was still swapping the saved order with the template one on every
 * render. Re-running the seeder is idempotent (it clears the role's rows first) and only affects
 * the defaults handed to users created from now on.
 *
 * @return void
 */
function core_dimensions_data_changes_25_26() {
	Env::useHelper('role_default_list_config');
	require_once ROOT . '/plugins/core_dimensions/install/role_default_list_config_seed_data.php';
	if (function_exists('role_default_list_config_seed_data') && function_exists('role_default_list_config_seed')) {
		role_default_list_config_seed(role_default_list_config_seed_data(), 0);
	}
}
