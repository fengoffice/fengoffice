<?php

/**
 * Whether a user should be auto-subscribed to a task by default for the given action.
 * Actions: assigned_to, created_by, edited_by.
 * When notifications_manager is inactive, returns true (legacy behavior unchanged).
 * When active, the plugin hook applies admin configuration.
 */
function should_default_subscribe($type) {
	static $cache = array();
	if (isset($cache[$type])) {
		return $cache[$type];
	}
	$enabled = true;
	if (Plugins::instance()->isActivePlugin('notifications_manager')) {
		Hook::fire('should_default_subscribe', array('type' => $type), $enabled);
	}
	return $cache[$type] = $enabled;
}

/**
 * Default subscriber config flags for task forms (used in PHP views and JS).
 */
function get_task_default_subscriber_config() {
	return array(
		'assigned_to' => should_default_subscribe('assigned_to'),
		'created_by' => should_default_subscribe('created_by'),
		'edited_by' => should_default_subscribe('edited_by'),
	);
}

/**
 * Initial subscriber ids for the task form before AJAX render.
 */
function get_task_form_subscriber_ids($task) {
	if (!$task->isNew()) {
		return $task->getSubscriberIds();
	}
	$subscriber_ids = array();
	if (logged_user() instanceof Contact && should_default_subscribe('created_by')) {
		$subscriber_ids[] = logged_user()->getId();
	}
	return $subscriber_ids;
}

/**
 * Apply default subscriber ids when rendering the task subscribers tab.
 */
function apply_default_subscriber_ids_for_task_form(&$subscriberIds, $object_type_id, $assigned_to, $is_new = false) {
	$task_ot_id = ProjectTasks::instance()->getObjectTypeId();
	if ((int) $object_type_id != $task_ot_id) {
		return;
	}

	if ($is_new && logged_user() instanceof Contact && should_default_subscribe('created_by')) {
		if (!in_array(logged_user()->getId(), $subscriberIds)) {
			$subscriberIds[] = logged_user()->getId();
		}
	}

	if (should_default_subscribe('assigned_to') && (int) $assigned_to > 0) {
		$assigned_to = (int) $assigned_to;
		if (!in_array($assigned_to, $subscriberIds)) {
			$subscriberIds[] = $assigned_to;
		}
	}
}

/**
 * Prepare $_POST['subscribers'] according to default subscriber configuration before save.
 */
function prepare_task_subscribers_post($task, $is_new = false) {
	if (!isset($_POST['subscribers']) || !is_array($_POST['subscribers'])) {
		$_POST['subscribers'] = array();
	}

	if ($is_new && logged_user() instanceof Contact && should_default_subscribe('created_by')) {
		$creator_key = 'user_' . logged_user()->getId();
		if (!isset($_POST['subscribers'][$creator_key]) || $_POST['subscribers'][$creator_key] === '' || $_POST['subscribers'][$creator_key] === null) {
			$_POST['subscribers'][$creator_key] = '1';
		}
	}

	if (should_default_subscribe('assigned_to')) {
		$assigned_to_id = (int) $task->getAssignedToContactId();
		if ($assigned_to_id > 0) {
			$assignee = Contacts::instance()->findById($assigned_to_id);
			if ($assignee instanceof Contact && $assignee->getUserType()) {
				$_POST['subscribers']['user_' . $assignee->getId()] = '1';
			}
		}
	}

	if (!$is_new && logged_user() instanceof Contact && should_default_subscribe('edited_by')) {
		$_POST['subscribers']['user_' . logged_user()->getId()] = '1';
	}
}

/**
 * Subscribe default users directly after save.
 * Used for channels that bypass add_subscribers (quick add, quick edit, inline).
 * subscribeUser() is idempotent (no-op when the user is already subscribed).
 */
function apply_default_task_subscribers($task, $is_new = false) {
	if (!($task instanceof ProjectTask || $task instanceof TemplateTask)) {
		return;
	}

	if ($is_new && logged_user() instanceof Contact && should_default_subscribe('created_by')) {
		$task->subscribeUser(logged_user());
	}

	if (!$is_new && logged_user() instanceof Contact && should_default_subscribe('edited_by')) {
		$task->subscribeUser(logged_user());
	}

	if (should_default_subscribe('assigned_to')) {
		$assignee = Contacts::instance()->findById($task->getAssignedToContactId());
		if ($assignee instanceof Contact && $assignee->getUserType()) {
			$task->subscribeUser($assignee);
		}
	}
}

/**
 * Apply default subscribers after a task content edit outside the full form (quick edit, inline, assignee change).
 */
function apply_default_task_subscribers_on_edit($task) {
	apply_default_task_subscribers($task, false);
}

/**
 * Apply default subscribers after creating a task outside the full form (quick add).
 */
function apply_default_task_subscribers_on_create($task) {
	apply_default_task_subscribers($task, true);
}
