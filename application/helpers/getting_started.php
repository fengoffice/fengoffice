<?php
/**
 * Getting started checklist: the steps shown in the "Getting started" tab while the initial
 * setup is not finished. The state of every step is computed from the installation data.
 */

// can_manage_configuration() and the other permission checks used below
Env::useHelper('permissions');

/**
 * Runs a "SELECT COUNT(*) AS c ..." query.
 *
 * @param string $sql Query with ? placeholders that returns a column named c
 * @param mixed ...$args Values for the placeholders
 * @return integer|null The count, or null when the query cannot be run (for example a missing table)
 */
function getting_started_count($sql) {
	try {
		$row = call_user_func_array(array('DB', 'executeOne'), func_get_args());
		return is_array($row) ? (int) array_var($row, 'c', 0) : 0;
	} catch (Throwable $e) {
		Logger::log("Getting started: cannot compute a step state. " . $e->getMessage());
		return null;
	}
}

/**
 * Only the admin group can finish or skip the setup (see MoreController::set_getting_started_step).
 *
 * @param Contact $user
 * @return boolean
 */
function getting_started_can_finish(Contact $user) {
	return $user->isAdminGroup();
}

/**
 * Lang key of the line under the title.
 *
 * @param boolean $nothing_pending Every required step is done or was skipped
 * @param boolean $can_finish The user is allowed to finish the setup
 * @param boolean $any_skipped At least one required step was skipped
 * @return string
 */
function getting_started_subtitle_key($nothing_pending, $can_finish, $any_skipped = false) {
	if (!$nothing_pending) {
		return 'getting started subtitle';
	}
	// only the admin group gets the Finish button: do not tell anyone else that they can finish
	if ($any_skipped) {
		// "everything is set" would not be true
		return $can_finish ? 'getting started no pending' : 'getting started no pending ask admin';
	}
	return $can_finish ? 'getting started all set' : 'getting started all set ask admin';
}

/**
 * Steps that can be skipped one by one: the required ones. The optional step never blocks anything.
 *
 * @return array
 */
function getting_started_skippable_steps() {
	return array('modules', 'organization', 'team', 'workspace', 'task');
}

/**
 * Steps the administrators chose to skip, in the order they skipped them.
 *
 * @return array
 */
function getting_started_skipped_steps() {
	try {
		// read from the table: the list is written with plain statements, outside the cached option values
		$row = DB::executeOne("SELECT value FROM " . TABLE_PREFIX . "config_options WHERE name = ?", 'getting_started_skipped_steps');
	} catch (Throwable $e) {
		Logger::log("Getting started: cannot read the skipped steps. " . $e->getMessage());
		return array();
	}
	if (!is_array($row) || trim((string) array_var($row, 'value')) == '') {
		return array();
	}
	return array_values(array_intersect(explode(',', $row['value']), getting_started_skippable_steps()));
}

/**
 * Marks a step as skipped.
 *
 * The option that keeps the list is created here when it does not exist, so installations made before it
 * was added need no upgrade step. The step is added in one statement, so two administrators skipping
 * different steps at the same moment both get theirs stored.
 *
 * @param string $step_id One of getting_started_skippable_steps()
 * @return boolean False when the step cannot be skipped
 */
function getting_started_skip_step($step_id) {
	if (!in_array($step_id, getting_started_skippable_steps(), true)) {
		return false;
	}
	DB::execute("INSERT INTO " . TABLE_PREFIX . "config_options (category_name, name, value, config_handler_class, is_system, option_order, dev_comment)
		VALUES ('system', 'getting_started_skipped_steps', ?, 'StringConfigHandler', 1, 0, 'Steps of the getting started checklist that were skipped')
		ON DUPLICATE KEY UPDATE value = IF(FIND_IN_SET(?, value) > 0, value, CONCAT_WS(',', NULLIF(value, ''), ?))",
		$step_id, $step_id, $step_id);
	return true;
}

/**
 * Ordered list of the checklist steps available to $user.
 *
 * Each step is an array with: id, title, description, optional (boolean), done (boolean),
 * skipped (boolean, never true for a step that is done or optional), action_label and action. The action is array('type' => 'panel', 'url' => ...) for forms that
 * open in the panel, or array('type' => 'modal', 'controller' => ..., 'action' => ..., 'params' => array)
 * for forms that open as a modal.
 *
 * A step is left out when its plugin is inactive, its module is disabled, the user cannot perform
 * it, or its state cannot be computed.
 *
 * @param Contact $user
 * @param integer|null $step Value of the getting_started_step option. Null reads the saved value
 * @param array|null $skipped Ids of the skipped steps. Null reads the saved list
 * @return array
 */
function getting_started_steps(Contact $user, $step = null, $skipped = null) {
	if (!can_manage_configuration($user)) {
		return array();
	}
	if (is_null($step)) {
		$step = config_option('getting_started_step');
	}
	$steps = array();

	// 1. modules: the only step without a signal in the data, it is done once the administrator confirms the selection
	$steps[] = getting_started_step_info('modules', $step >= 2, array('type' => 'panel', 'url' => get_url('more', 'system_modules')));

	// 2. organization details
	$company = owner_company();
	if ($company instanceof Contact) {
		$steps[] = getting_started_step_info('organization', $company->hasPicture(), array('type' => 'panel', 'url' => get_url('administration', 'company')));
	}

	// 3. team
	if (can_manage_security($user)) {
		// the inner LIMIT stops at the rows needed to decide, whatever the size of the installation
		$users = getting_started_count("SELECT COUNT(*) AS c FROM (SELECT c.object_id FROM " . TABLE_PREFIX . "contacts c
			INNER JOIN " . TABLE_PREFIX . "objects o ON o.id = c.object_id
			WHERE c.user_type > 0 AND c.disabled = 0 AND o.trashed_by_id = 0 AND IFNULL(o.archived_by_id, 0) = 0 LIMIT 2) found");
		if (!is_null($users)) {
			$url_params = array('is_user' => 1);
			$default_role = PermissionGroups::instance()->findOne(array('conditions' => "type='roles' AND name='Executive'"));
			if ($default_role instanceof PermissionGroup) {
				$url_params['user_type'] = $default_role->getId();
			}
			$steps[] = getting_started_step_info('team', $users > 1, array('type' => 'panel', 'url' => get_url('contact', 'add', $url_params)));
		}
	}

	// The workspace, task and email forms accept being saved empty, so the three steps below only count
	// records that have a name: a blank one is not what the step asks for.

	// 4. first workspace
	if (Plugins::instance()->isActivePlugin('workspaces') && can_manage_dimension_members($user)) {
		$dimension = Dimensions::findByCode('workspaces');
		$type = ObjectTypes::findByName('workspace');
		// the modules screen turns a dimension off through this option and leaves its plugin active
		$enabled_dimensions = config_option('enabled_dimensions');
		$dimension_enabled = $dimension instanceof Dimension && is_array($enabled_dimensions) && in_array($dimension->getId(), $enabled_dimensions);
		if ($dimension_enabled && $type instanceof ObjectType) {
			$workspaces = getting_started_count("SELECT COUNT(*) AS c FROM (SELECT id FROM " . TABLE_PREFIX . "members
				WHERE dimension_id = ? AND IFNULL(archived_by_id, 0) = 0 AND TRIM(name) <> '' LIMIT 1) found", $dimension->getId());
			if (!is_null($workspaces)) {
				$steps[] = getting_started_step_info('workspace', $workspaces > 0, array(
					'type' => 'modal', 'controller' => 'member', 'action' => 'add',
					'params' => array('dim_id' => $dimension->getId(), 'type' => $type->getId()),
				));
			}
		}
	}

	// 5. first task
	$tasks_tab = getting_started_count("SELECT COUNT(*) AS c FROM " . TABLE_PREFIX . "tab_panels WHERE id = ? AND enabled = 1", 'tasks-panel');
	if ($tasks_tab > 0) {
		$tasks = getting_started_count("SELECT COUNT(*) AS c FROM (SELECT t.object_id FROM " . TABLE_PREFIX . "project_tasks t
			INNER JOIN " . TABLE_PREFIX . "objects o ON o.id = t.object_id
			WHERE t.is_template = 0 AND o.trashed_by_id = 0 AND IFNULL(o.archived_by_id, 0) = 0 AND TRIM(o.name) <> '' LIMIT 1) found");
		if (!is_null($tasks)) {
			$steps[] = getting_started_step_info('task', $tasks > 0, array('type' => 'modal', 'controller' => 'task', 'action' => 'add_task', 'params' => array()));
		}
	}

	// optional: email account
	if (Plugins::instance()->isActivePlugin('mail') && can_add_mail_accounts($user)) {
		$accounts = getting_started_count("SELECT COUNT(*) AS c FROM " . TABLE_PREFIX . "mail_accounts WHERE TRIM(email_addr) <> ''");
		if (!is_null($accounts)) {
			$steps[] = getting_started_step_info('mail', $accounts > 0, array('type' => 'panel', 'url' => get_url('mail', 'add_account')), true);
		}
	}

	// a step that was skipped and then done for real is simply done
	if (is_null($skipped)) {
		$skipped = getting_started_skipped_steps();
	}
	foreach ($steps as $index => $one_step) {
		$steps[$index]['skipped'] = !$one_step['done'] && !$one_step['optional'] && in_array($one_step['id'], $skipped);
	}

	return $steps;
}

/**
 * Builds one step with its texts.
 *
 * @param string $id
 * @param boolean $done
 * @param array $action
 * @param boolean $optional
 * @return array
 */
function getting_started_step_info($id, $done, array $action, $optional = false) {
	return array(
		'id' => $id,
		'title' => lang("getting started $id title"),
		'description' => lang("getting started $id desc"),
		'optional' => $optional,
		'done' => (bool) $done,
		'skipped' => false,
		'action_label' => lang("getting started $id action"),
		'action' => $action,
	);
}

/**
 * Progress over the required steps. Optional steps never count, and a skipped step is not a done one.
 *
 * @param array $steps Result of getting_started_steps()
 * @return array array('done' => integer, 'skipped' => integer, 'total' => integer)
 */
function getting_started_progress(array $steps) {
	$done = 0;
	$skipped = 0;
	$total = 0;
	foreach ($steps as $step) {
		if ($step['optional']) continue;
		$total++;
		if ($step['done']) {
			$done++;
		} else if (array_var($step, 'skipped')) {
			$skipped++;
		}
	}
	return array('done' => $done, 'skipped' => $skipped, 'total' => $total);
}
