<?php

/**
 * Data provider + helpers for the activity_feed dashboard widget (see
 * application/helpers/dimension_widget.php for the generic feed shell that calls this).
 *
 * Lives in core since activity_feed is a long-standing core widget — this replaces its
 * previous implementation in place (same widget name/row, richer rendering).
 */

/**
 * "Filter by user" select options for the Activity widget — every active company user,
 * same source (Contacts::getAllUsers()) the Tasks "Assigned to" selector falls back to,
 * regardless of whether they've logged any activity yet.
 *
 * @return array [ ['value'=>contact_id, 'label'=>display_name], ... ]
 */
function activity_feed_get_user_options() {
	$users = Contacts::getAllUsers();
	if (!is_array($users)) {
		return array();
	}

	$options = array();
	foreach ($users as $user) { /* @var $user Contact */
		$options[] = array('value' => (string) $user->getId(), 'label' => $user->getDisplayName());
	}
	return $options;
}

/**
 * Icon + badge color for one ApplicationLog action, for the Activity feed widget.
 * All icon-* classes below are Lucide icons already shipped with the app's icon
 * font (public/assets/themes/default/stylesheets/lucide/lucide.css).
 *
 * @param string $action
 * @return array ['icon' => 'icon-x', 'color' => '#hex']
 */
function activity_feed_icon_for_action($action) {
	$map = array(
		'add'               => array('icon' => 'icon-plus',           'color' => '#2a9d8f'),
		'edit'               => array('icon' => 'icon-pencil',         'color' => '#1b75bc'),
		'upload'             => array('icon' => 'icon-file-up',       'color' => '#d99a2b'),
		'download'           => array('icon' => 'icon-download',      'color' => '#6c757d'),
		'comment'            => array('icon' => 'icon-message-square','color' => '#6c757d'),
		'delete'             => array('icon' => 'icon-trash',         'color' => '#c0392b'),
		'trash'              => array('icon' => 'icon-trash',         'color' => '#c0392b'),
		'untrash'            => array('icon' => 'icon-rotate-ccw',    'color' => '#6c757d'),
		'archive'            => array('icon' => 'icon-archive',       'color' => '#6c757d'),
		'unarchive'          => array('icon' => 'icon-archive-restore','color' => '#6c757d'),
		'open'               => array('icon' => 'icon-circle-dot',    'color' => '#6c757d'),
		'close'              => array('icon' => 'icon-check',         'color' => '#2a9d8f'),
		'read'               => array('icon' => 'icon-eye',           'color' => '#6c757d'),
		'move'               => array('icon' => 'icon-move',          'color' => '#6c757d'),
		'copy'               => array('icon' => 'icon-copy',          'color' => '#6c757d'),
		'link'               => array('icon' => 'icon-link',          'color' => '#6c757d'),
		'unlink'             => array('icon' => 'icon-unlink',        'color' => '#6c757d'),
		'subscribe'          => array('icon' => 'icon-bell',          'color' => '#9aa0a6'),
		'unsubscribe'        => array('icon' => 'icon-bell-off',      'color' => '#9aa0a6'),
		'login'              => array('icon' => 'icon-log-in',        'color' => '#6c757d'),
		'logout'             => array('icon' => 'icon-log-out',       'color' => '#6c757d'),
		'checkin'            => array('icon' => 'icon-file-input',    'color' => '#6c757d'),
		'checkout'           => array('icon' => 'icon-file-output',   'color' => '#6c757d'),
		'relation_added'     => array('icon' => 'icon-waypoints',     'color' => '#1b75bc'),
		'relation_edited'    => array('icon' => 'icon-git-compare',   'color' => '#1b75bc'),
		'relation_removed'   => array('icon' => 'icon-scissors',      'color' => '#c0392b'),
		'made several changes' => array('icon' => 'icon-history',     'color' => '#1b75bc'),
		'print'              => array('icon' => 'icon-printer',       'color' => '#6c757d'),
		'mark_as_spam'       => array('icon' => 'icon-shield-alert',  'color' => '#c0392b'),
		'unmark_as_spam'     => array('icon' => 'icon-shield-check',  'color' => '#2a9d8f'),
		'forward'            => array('icon' => 'icon-forward',       'color' => '#6c757d'),
		'reply'              => array('icon' => 'icon-reply',         'color' => '#6c757d'),
		'reply_all'          => array('icon' => 'icon-reply-all',     'color' => '#6c757d'),
	);
	return isset($map[$action]) ? $map[$action] : array('icon' => 'icon-circle-dot', 'color' => '#9aa0a6');
}

/**
 * Data provider for the activity_feed widget (see dimension_widget.php for the
 * generic feed shell that calls this). Reuses ApplicationLogs::getLastActivities()
 * — the same core query the previous activity_feed implementation used, so permission
 * and context filtering stay identical — and just re-renders each entry as a feed
 * item (icon badge + actor/action text via getActivityDataView() + timestamp).
 *
 * v1 scope: "view time entries" toggle. "Filter by user" from the mockup is left
 * for a follow-up pass.
 *
 * @param int   $limit
 * @param array $filter_values  ['show_time_entries' => '1'|'0']
 * @return array|false
 */
function activity_feed_data_provider($limit, $filter_values) {
	$show_time_entries = array_var($filter_values, 'show_time_entries', '1') !== '0';
	$user_id_filter = (int) array_var($filter_values, 'user_id', 0);

	// Fetch a buffer larger than $limit, not ApplicationLogs' full 100-row cap: the loop below
	// still discards entries (show_time_entries/user_id filters, permission checks, deleted
	// objects), so a bare $limit would risk under-filling the widget, but there's no need to
	// pull the full 100 every time either — most widgets show far fewer than that.
	$fetch_limit = max($limit * 4, 30);
	$activities = ApplicationLogs::getLastActivities($fetch_limit);
	if (!is_array($activities)) {
		return false;
	}

	$items_html = array();
	$has_any_activity = false;
	foreach ($activities as $activity) { /* @var $activity ApplicationLog */
		if (count($items_html) >= $limit) break;

		$user = Contacts::instance()->findById($activity->getCreatedById());
		if (!$user instanceof Contact) continue;

		$has_any_activity = true;
		if ($user_id_filter > 0 && $activity->getCreatedById() != $user_id_filter) continue;

		$member_deleted = false;
		if ($activity->getMemberId()) {
			$object = Members::instance()->findById($activity->getMemberId());
		} else if ($activity->getLogData() == 'member deleted') {
			$object = Members::instance()->findById($activity->getRelObjectId());
			$member_deleted = true;
		} else {
			$object = Objects::findObject($activity->getRelObjectId());
			if ($object instanceof Timeslot) {
				if (!$show_time_entries) continue;
				if ($object->getRelObjectId() > 0) {
					$rel_obj = Objects::findObject($object->getRelObjectId());
					if (!$rel_obj instanceof ContentDataObject || !$rel_obj->canView(logged_user())) continue;
				}
			}
		}
		if (!$object && !$member_deleted) continue;

		$action_html = $activity->getActivityDataView($user, $object);
		if ($action_html === false) continue;

		$icon = activity_feed_icon_for_action($activity->getAction());
		$when = $activity->getCreatedOn() instanceof DateTimeValue ? friendly_date($activity->getCreatedOn()) : lang('n/a');

		$items_html[] =
			'<div class="fo-widget-list-item is-activity-item">' .
				'<div class="fo-widget-list-lead">' .
					'<span class="fo-widget-icon-badge" style="background:' . $icon['color'] . ';"><i class="' . $icon['icon'] . '"></i></span>' .
				'</div>' .
				'<div class="fo-widget-list-body">' .
					// activity-info matches getActivityDataView()'s inline .link-ico icons (the small
				// task/contact/project icons embedded in the sentence) with the sizing/centering
				// rule already defined for them in og.css (.activity-info .link-ico) — without it
				// those icons render unsized and vertically misaligned with the surrounding text.
				'<div class="fo-widget-feed-text activity-info">' . $action_html . '</div>' .
					'<div class="fo-widget-feed-time">' . clean($when) . '</div>' .
				'</div>' .
			'</div>';
	}

	if (empty($items_html) && !$has_any_activity) {
		// No activity at all (regardless of filters) — hide the widget, same convention as
		// the other widgets. If a filter (e.g. a specific user) just narrowed it to zero,
		// fall through and show the empty-state message instead, keeping the settings gear
		// reachable so the user can change the filter back.
		return false;
	}

	return array(
		'items_html'   => $items_html,
		'widget_title' => lang('activity'),
	);
}
