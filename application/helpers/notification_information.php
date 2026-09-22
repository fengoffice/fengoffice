<?php

/**
 * Shared helpers for configurable, email-safe notification information blocks.
 */

if (!defined('NOTIFICATION_INFO_HIERARCHY_SEPARATOR')) {
	// Same separator as UI breadcrumbs (.bullet-separator → U+2022).
	define('NOTIFICATION_INFO_HIERARCHY_SEPARATOR', ' • ');
}

/**
 * Default fields shown per object type when no installation config exists.
 *
 * @return array<object_type_name, string CSV>
 */
function notification_information_default_fields() {
	return array(
		'task' => 'name,assigned_to_contact_id,due_date,status,priority,classifications,description',
		'milestone' => 'name,due_date,status,classifications,description',
		'event' => 'name,start,duration,classifications,description',
		'message' => 'name,classifications,description',
		'weblink' => 'name,classifications,description',
		'file' => 'name,classifications,description',
		'contact' => 'name,classifications',
		'company' => 'name,classifications',
		'timeslot' => 'name,classifications,description',
		'mail' => 'name,classifications',
	);
}

/**
 * @return array<object_type_name, string[]>
 */
function notification_information_fields_config() {
	$raw = config_option('notification_information_fields');
	$defaults = notification_information_default_fields();
	$config = array();

	if (is_string($raw) && trim($raw) !== '') {
		$decoded = json_decode($raw, true);
		if (is_array($decoded)) {
			$config = $decoded;
		}
	} elseif (is_array($raw)) {
		$config = $raw;
	}

	foreach ($defaults as $ot_name => $csv) {
		if (!array_key_exists($ot_name, $config)) {
			$config[$ot_name] = $csv;
		} elseif ($config[$ot_name] === '' || $config[$ot_name] === null) {
			// Legacy: empty string meant "not configured".
			$config[$ot_name] = $csv;
		} elseif ($config[$ot_name] === '__none__') {
			// Intentional empty selection (all fields unchecked).
			$config[$ot_name] = array();
		}
		// else keep as-is (including empty array from the config handler)
	}

	$result = array();
	foreach ($config as $ot_name => $value) {
		if (is_array($value)) {
			$result[$ot_name] = array_values(array_filter(array_map('trim', $value)));
		} else {
			$parts = explode(',', (string) $value);
			$result[$ot_name] = array();
			foreach ($parts as $part) {
				$part = trim($part);
				if ($part !== '') {
					$result[$ot_name][] = $part;
				}
			}
		}
	}
	return $result;
}

/**
 * Fields selected for a given object type name.
 *
 * @param string $object_type_name
 * @return string[]
 */
function notification_information_fields_for_object_type($object_type_name) {
	$config = notification_information_fields_config();
	if (isset($config[$object_type_name])) {
		return $config[$object_type_name];
	}
	$defaults = notification_information_default_fields();
	if (isset($defaults[$object_type_name])) {
		return array_filter(array_map('trim', explode(',', $defaults[$object_type_name])));
	}
	return array('name', 'classifications', 'description');
}

/**
 * Display name for an assignee, or Unassigned.
 *
 * @param ContentDataObject $object
 * @return string
 */
function notification_assigned_to_label($object) {
	if ($object instanceof ProjectTask) {
		if ($object->isAssigned()) {
			$contact = $object->getAssignedToContact();
			if ($contact instanceof Contact) {
				return $contact->getObjectName();
			}
		}
		return lang('unassigned');
	}
	return '';
}

/**
 * Actor label for system/automation actions.
 *
 * @param Contact|null $contact
 * @return string
 */
function notification_actor_label($contact = null) {
	if ($contact instanceof Contact) {
		$name = trim($contact->getObjectName());
		if ($name !== '') {
			return $name;
		}
	}
	$from_name = trim((string) config_option('notification_from_name'));
	if ($from_name !== '') {
		return $from_name;
	}
	return lang('notification from name default');
}

/**
 * Shared CSS for notification information / log blocks (email-safe, no min-width).
 *
 * @param string $mode activity|reminder|assignment
 * @return string
 */
function notification_email_styles($mode = 'activity') {
	// Activity / digests: blue rail. Reminder: amber rail (banner uses its own styles).
	$is_reminder = ($mode === 'reminder');
	$accent = $is_reminder ? '#F59E0B' : '#27a9ff';
	$log_bg = $is_reminder ? '#FFFBEB' : '#F0F9FF';
	$log_border = $is_reminder ? '#FDE68A' : '#E0F2FE';
	return '<style type="text/css">
body{background-color:#F8FAFC;font-family:Arial,Helvetica,sans-serif;margin:0;padding:0;color:#0F172A;}
.notification-wrapper{width:100%;max-width:600px;margin:0 auto;padding:16px;box-sizing:border-box;}
.notification-card{width:100%;max-width:600px;margin:0 auto;padding:20px;box-sizing:border-box;background:#ffffff;border:1px solid #E2E8F0;border-radius:10px;border-top:4px solid #0F9D8C;}
.all-logs{width:100%;max-width:100%;margin:16px 0 0;box-sizing:border-box;}
.logs-group{background-color:'.$log_bg.';padding:12px 14px;width:100%;box-sizing:border-box;border-radius:8px;border:1px solid '.$log_border.';border-left:4px solid '.$accent.';font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.45;color:#334155;margin:10px 0;}
.log-header{color:#64748B;font-size:12px;line-height:1.45;}
.log-header--actor{color:#334155;font-weight:600;}
.log-header--when{color:#64748B;}
.log-details{margin:8px 0 0;padding:0;list-style-type:none;}
.log-detail{color:#334155;font-weight:400;}
.log-detail--comment,.log-detail--description,.log-detail--new-value{font-weight:600;color:#0F172A;word-wrap:break-word;overflow-wrap:break-word;}
.log-detail--old-value{text-decoration:line-through;color:#64748B;word-wrap:break-word;}
.log-detail--arrow{color:#64748B;font-weight:400;padding:0 3px;}
.object-type-name,.digest-object-heading{font-family:Arial,Helvetica,sans-serif;font-weight:400;margin:12px 0 6px;padding:0;line-height:1.35;text-align:left;font-size:15px;color:#0F172A;width:100%;max-width:100%;box-sizing:border-box;}
.digest-project-shell{margin:0 0 18px;padding:0;width:100%;max-width:100%;box-sizing:border-box;background-color:#ffffff;border:1px solid #0F9D8C;border-left:4px solid #0F9D8C;border-radius:10px;overflow:hidden;}
.digest-project-shell:last-child{margin-bottom:0;}
.digest-project-heading{font-family:Arial,Helvetica,sans-serif;margin:0;padding:12px 14px;line-height:1.3;text-align:left;width:100%;max-width:100%;box-sizing:border-box;background-color:#ffffff;border-bottom:1px solid #0F9D8C;}
.digest-project-body{padding:10px 12px 12px 18px;box-sizing:border-box;}
.digest-eyebrow{display:block;font-size:10px;letter-spacing:0.12em;text-transform:uppercase;color:#0F9D8C;font-weight:600;margin:0 0 4px 0;line-height:1.2;}
.digest-project-name{font-size:16px;font-weight:600;color:#0F172A;line-height:1.3;}
.digest-project-name a,.digest-project-name .object-name--link{color:#0C8275;text-decoration:none;font-weight:600;}
.digest-object-type{display:inline-block;font-size:11px;letter-spacing:0.06em;text-transform:uppercase;color:#64748B;font-weight:600;margin:0 8px 0 0;vertical-align:middle;}
.digest-object-name{display:inline;font-size:15px;font-weight:600;color:#0C8275;text-decoration:none;vertical-align:middle;}
.object-type{color:#0F172A;font-weight:400;}
.object-name--link{color:#0C8275;text-decoration:none;font-weight:500;}
.object-info-card{border-radius:8px;margin:12px 0;padding:12px 14px;box-sizing:border-box;width:100%;max-width:100%;background-color:#ffffff;border:1px solid #E2E8F0;border-left:4px solid #0F9D8C;}
.object-info-card .object-type-name,.object-info-card .digest-object-heading{margin:0 0 4px 0;padding:0;max-width:none;font-size:15px;}
.object-info-card .digest-project-heading{margin:0 0 10px 0;padding:8px 12px;border-radius:6px;}
.object-heading-block .digest-project-shell{margin:0 0 12px;}
.all-logs > .digest-project-shell:first-child{margin-top:0;}
.digest-project-body > .digest-object-heading:first-child{margin-top:2px;}
.digest-project-body .logs-group{margin:8px 0;background-color:#F8FAFC;border:1px solid #EEF2F7;border-left:4px solid '.$accent.';}
.digest-project-body .logs-group + .logs-group{margin-top:8px;}
.digest-object-heading + .logs-group{margin-top:6px;}
.logs-group + .logs-group{margin-top:8px;}
.object-type-name + .logs-group,.digest-object-heading + .logs-group{margin-top:8px;}
.digest-project-heading + .digest-object-heading{margin-top:10px;}
.task-info--table,.object-info--table{margin:0;table-layout:fixed;font-family:Arial,Helvetica,sans-serif;font-weight:400;text-align:left;line-height:1.5;width:100%;background-color:transparent;border-collapse:collapse;font-size:13px;}
.task-info--table th,.object-info--table th{font-weight:500;text-align:left;color:#64748B;vertical-align:top;padding:8px 16px 8px 0;word-wrap:break-word;width:34%;border-top:0;}
.task-info--table td,.object-info--table td{padding:8px 0;width:66%;word-wrap:break-word;overflow-wrap:break-word;color:#0F172A;border-top:0;}
.task-info--table tr:first-child th,.object-info--table tr:first-child th,.task-info--table tr:first-child td,.object-info--table tr:first-child td{border-top:0;padding-top:8px;}
.reminder-banner{background-color:#FFFBEB;border:1px solid #FDE68A;border-left:4px solid #F59E0B;border-radius:8px;padding:12px 14px;margin:12px 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#334155;line-height:1.5;width:100%;max-width:100%;box-sizing:border-box;}
.reminder-banner strong{display:block;margin-bottom:4px;color:#0F172A;font-size:13px;font-weight:600;}
.member-chip{display:inline-block;margin:2px 4px 2px 0;padding:3px 8px;border-radius:4px;line-height:1.4;white-space:nowrap;font-size:12px;}
.member-hierarchy{display:inline-block;margin:2px 0;}
.classification-group{margin:6px 0;}
.classification-label{color:#64748B;margin:0 0 4px 0;}
.classification-chips{line-height:1.6;}
.footer-note{text-align:center;color:#64748B;font-size:12px;margin-top:16px;}
@media only screen and (max-width:480px){
.task-info--table th,.object-info--table th,.task-info--table td,.object-info--table td{display:block;width:100%;padding:4px 0;border-top:0;}
.task-info--table th,.object-info--table th{color:#64748B;font-size:12px;padding-top:10px;}
.task-info--table tr:first-child th,.object-info--table tr:first-child th{padding-top:4px;}
}
</style>';
}

/**
 * Build a colored member chip; falls back when color is missing.
 * Uses the same filled (1–12) / unfilled (13–24) palette as the UI, with an explicit border
 * so outline chips render in email clients.
 *
 * @param string $label_html Already-escaped or safe HTML for the chip contents
 * @param int|string|null $color
 * @return string
 */
function notification_member_chip_html_from_label($label_html, $color = null) {
	if (!$color) {
		$style = 'border:1px solid #CBD5E1;background-color:#F1F5F9;color:#334155;padding:3px 8px;font-size:12px;border-radius:4px;';
	} else {
		$style = get_workspace_css_properties($color);
		// Match UI real-breadcrumb padding; email clients need an explicit border for unfilled chips.
		$style = preg_replace('/padding:\s*[^;]+;/', 'padding:3px 8px;', $style);
		if (stripos($style, 'border:') === false && stripos($style, 'border-width') === false) {
			$style = 'border:1px solid;'.$style;
		}
		if (stripos($style, 'border-radius') === false) {
			$style .= 'border-radius:4px;';
		}
		if (stripos($style, 'font-size') === false) {
			$style .= 'font-size:12px;';
		}
	}
	return '<span class="member-chip" style="'.$style.'">'.$label_html.'</span>';
}

/**
 * Resolve the notification recipient Contact for permission/timezone rendering.
 * Prefer notification_user (always set per recipient); mail_to is only filled when
 * the mail template has a To field, which stock reminder templates do not.
 *
 * @param array $template_parameters
 * @return Contact|null
 */
function notification_resolve_email_viewer($template_parameters = array()) {
	$user = array_var($template_parameters, 'notification_user');
	if ($user instanceof Contact) {
		return $user;
	}
	return notification_resolve_viewer_from_mail_to(array_var($template_parameters, 'mail_to', array()));
}

/**
 * Resolve the notification recipient Contact from template mail_to (email list or Contact).
 *
 * @param mixed $mail_to
 * @return Contact|null
 */
function notification_resolve_viewer_from_mail_to($mail_to) {
	if ($mail_to instanceof Contact) {
		return $mail_to;
	}
	if (!is_array($mail_to) || count($mail_to) === 0) {
		return null;
	}
	$first = $mail_to[0];
	if ($first instanceof Contact) {
		return $first;
	}
	$email = trim((string) $first);
	if ($email === '') {
		return null;
	}
	$contact = Contacts::instance()->getByEmail($email);
	return $contact instanceof Contact ? $contact : null;
}

/**
 * Whether a member is readable by the notification recipient.
 * Without a viewer, return false so restricted ancestors are omitted (conservative).
 *
 * @param Member $member
 * @param Contact|null $viewer
 * @return bool
 */
function notification_member_can_be_seen_by(Member $member, $viewer = null) {
	if (!($viewer instanceof Contact)) {
		return false;
	}
	$pg_ids = $viewer->getPermissionGroupIds();
	if (!is_array($pg_ids) || count($pg_ids) === 0) {
		return false;
	}
	$pg_ids_str = implode(',', $pg_ids);
	return ContactMemberPermissions::contactCanAccessMemberAll($pg_ids_str, $member->getId(), $viewer, ACCESS_LEVEL_READ);
}

/**
 * Build a colored member chip for a single member name (leaf only).
 *
 * @param Member $member
 * @return string
 */
function notification_member_chip_html(Member $member) {
	return notification_member_chip_html_from_label(
		clean(Notifier::memberLabelForNotification($member)),
		$member->getMemberColor()
	);
}

/**
 * Render a member path like the UI breadcrumb: one chip with hierarchy inside ("Parent • Child").
 * Ancestors are filtered for the recipient Contact when provided; without a viewer, ancestors are omitted.
 *
 * @param Member $member
 * @param Contact|null $viewer
 * @return string
 */
function notification_member_hierarchy_html(Member $member, $viewer = null) {
	$labels = array();
	$parent_members = $member->getAllParentMembersInHierarchy(false, false);
	// API returns nearest parent first; reverse so root appears first.
	$parent_members = array_reverse($parent_members);
	foreach ($parent_members as $pm) {
		if ($pm instanceof Member && notification_member_can_be_seen_by($pm, $viewer)) {
			$labels[] = clean(Notifier::memberLabelForNotification($pm));
		}
	}
	$labels[] = clean(Notifier::memberLabelForNotification($member));
	$path_html = implode('<span style="color:inherit;opacity:.85;">'.NOTIFICATION_INFO_HIERARCHY_SEPARATOR.'</span>', $labels);
	return '<span class="member-hierarchy">'.notification_member_chip_html_from_label($path_html, $member->getMemberColor()).'</span>';
}

/**
 * Dimension display label (custom name / localized name), never raw code alone when a name exists.
 *
 * @param Dimension $dimension
 * @return string
 */
function notification_dimension_label(Dimension $dimension) {
	$name = trim($dimension->getName());
	return $name !== '' ? $name : $dimension->getCode();
}

/**
 * Object-type label inside a dimension (e.g. Organization instead of Client).
 *
 * @param ObjectType $object_type
 * @param Dimension|null $dimension
 * @return string
 */
function notification_member_type_label(ObjectType $object_type, $dimension = null) {
	if ($dimension instanceof Dimension && class_exists('Members')) {
		$label = trim(Members::getTypeNameToShowByObjectType($dimension->getId(), $object_type->getId(), null, false));
		if ($label !== '') {
			return $label;
		}
	}
	$label = trim($object_type->getObjectTypeName());
	if ($label !== '') {
		return $label;
	}
	return $object_type->getName();
}

/**
 * Selected classification dimension IDs for an object type.
 * Empty array means “all manageable dimensions” (backward compatible).
 *
 * @param string $object_type_name
 * @return int[]
 */
function notification_information_classification_dimension_ids($object_type_name) {
	$fields = notification_information_fields_for_object_type($object_type_name);
	$ids = array();
	foreach ($fields as $field_id) {
		if (!str_starts_with((string) $field_id, 'classification_dim_')) {
			continue;
		}
		$dim_id = (int) substr($field_id, strlen('classification_dim_'));
		if ($dim_id > 0) {
			$ids[$dim_id] = $dim_id;
		}
	}
	return array_values($ids);
}

/**
 * Drop members that are ancestors of another selected member.
 * Child chips already include the path ("Parent • Child"), so showing the parent alone is redundant.
 *
 * @param Member[] $members
 * @return Member[]
 */
function notification_filter_classification_members_omit_ancestors($members) {
	if (!is_array($members) || count($members) <= 1) {
		return is_array($members) ? array_values($members) : array();
	}

	$members = array_values(array_filter($members, function ($m) {
		return $m instanceof Member;
	}));
	if (count($members) <= 1) {
		return $members;
	}

	$kept = array();
	foreach ($members as $member) {
		$is_ancestor_of_another = false;
		foreach ($members as $other) {
			if ($other->getId() == $member->getId()) {
				continue;
			}
			// Email rendering must not depend on the current user's member permissions.
			$parents = $other->getAllParentMembersInHierarchy(false, false);
			if (!is_array($parents)) {
				continue;
			}
			foreach ($parents as $parent) {
				if ($parent instanceof Member && $parent->getId() == $member->getId()) {
					$is_ancestor_of_another = true;
					break 2;
				}
			}
		}
		if (!$is_ancestor_of_another) {
			$kept[] = $member;
		}
	}
	return $kept;
}

/**
 * Classification rows for the information table: dimension/type label left, chips right.
 * No wrapping "Classifications" parent label.
 *
 * @param ContentDataObject $object
 * @param Contact|null $viewer recipient used to filter restricted ancestors
 * @return array<int,array{label:string,value:string}>
 */
function notification_classification_rows(ContentDataObject $object, $viewer = null) {
	if ($object instanceof Comment) {
		$rel = $object->getRelObject();
		if (!($rel instanceof ContentDataObject)) {
			return array();
		}
		$members = $rel->getMembers();
	} else {
		$members = $object->getMembers();
	}
	if (!is_array($members) || count($members) === 0) {
		return array();
	}

	$object_type_id = $object->getObjectTypeId();
	$ot = ObjectTypes::instance()->findById($object_type_id);
	$ot_name = $ot instanceof ObjectType ? $ot->getName() : '';
	$selected_dim_ids = $ot_name !== '' ? notification_information_classification_dimension_ids($ot_name) : array();

	$all_dimensions = Dimensions::getAllowedDimensions($object_type_id);
	Hook::fire('allowed_dimensions_in_member_selector', array('ot' => $object_type_id), $all_dimensions);
	$allowed_dim_ids = array();
	foreach ($all_dimensions as $dim) {
		$allowed_dim_ids[] = array_var($dim, 'dimension_id', array_var($dim, 'id'));
	}

	$grouped = array();
	foreach ($members as $member) {
		if (!$member instanceof Member) {
			continue;
		}
		$dimension = $member->getDimension();
		if (!$dimension instanceof Dimension || !$dimension->getIsManageable()) {
			continue;
		}
		if (count($allowed_dim_ids) && !in_array($dimension->getId(), $allowed_dim_ids)) {
			continue;
		}
		if (count($selected_dim_ids) && !in_array($dimension->getId(), $selected_dim_ids)) {
			continue;
		}

		$obj_type = ObjectTypes::instance()->findById($member->getObjectTypeId());
		$dim_key = $dimension->getId();
		if (!isset($grouped[$dim_key])) {
			$grouped[$dim_key] = array(
				'dimension' => $dimension,
				'by_type' => array(),
			);
		}

		$type_key = $obj_type instanceof ObjectType ? $obj_type->getId() : 0;
		if (!isset($grouped[$dim_key]['by_type'][$type_key])) {
			$grouped[$dim_key]['by_type'][$type_key] = array(
				'object_type' => $obj_type,
				'members' => array(),
			);
		}
		$grouped[$dim_key]['by_type'][$type_key]['members'][] = $member;
	}

	// Within each dimension, omit ancestors when a descendant is also selected
	// (child chip already shows "Parent • Child").
	foreach ($grouped as $dim_key => $group) {
		$all_in_dim = array();
		foreach ($group['by_type'] as $type_group) {
			foreach ($type_group['members'] as $m) {
				$all_in_dim[] = $m;
			}
		}
		$kept_ids = array();
		foreach (notification_filter_classification_members_omit_ancestors($all_in_dim) as $m) {
			$kept_ids[$m->getId()] = true;
		}
		foreach ($grouped[$dim_key]['by_type'] as $type_key => $type_group) {
			$filtered = array();
			foreach ($type_group['members'] as $m) {
				if (isset($kept_ids[$m->getId()])) {
					$filtered[] = $m;
				}
			}
			if (count($filtered) === 0) {
				unset($grouped[$dim_key]['by_type'][$type_key]);
			} else {
				$grouped[$dim_key]['by_type'][$type_key]['members'] = $filtered;
			}
		}
		if (count($grouped[$dim_key]['by_type']) === 0) {
			unset($grouped[$dim_key]);
		}
	}

	$rows = array();
	foreach ($grouped as $group) {
		$dimension = $group['dimension'];
		$type_count = count($group['by_type']);
		foreach ($group['by_type'] as $type_group) {
			$member_htmls = array();
			foreach ($type_group['members'] as $member) {
				$member_htmls[] = notification_member_hierarchy_html($member, $viewer);
			}
			if (count($member_htmls) === 0) {
				continue;
			}
			$label = notification_dimension_label($dimension);
			if ($type_count > 1 && $type_group['object_type'] instanceof ObjectType) {
				$label = notification_member_type_label($type_group['object_type'], $dimension);
			} elseif ($type_group['object_type'] instanceof ObjectType
				&& in_array($dimension->getCode(), array('customer_project', 'customers'))) {
				$label = notification_member_type_label($type_group['object_type'], $dimension);
			}
			$rows[] = array(
				'label' => $label,
				'value' => '<div class="classification-chips">'.implode(' ', $member_htmls).'</div>',
			);
		}
	}

	return $rows;
}

/**
 * Build classification HTML for an object (legacy stacked blocks).
 *
 * @param ContentDataObject $object
 * @return string
 */
function notification_classifications_html(ContentDataObject $object) {
	$parts = array();
	foreach (notification_classification_rows($object) as $row) {
		$parts[] = '<div class="classification-group" style="margin:6px 0;">'
			.'<div class="classification-label" style="color:#8c8c8c;margin:0 0 4px 0;">'.clean($row['label']).':</div>'
			.$row['value']
			.'</div>';
	}
	return implode('', $parts);
}

/**
 * Relative due wording for reminders.
 *
 * @param DateTimeValue $date
 * @param int|float $timezone
 * @param bool $use_time
 * @return string
 */
function notification_reminder_when_label(DateTimeValue $date, $timezone = 0, $use_time = false) {
	$timezone = (float) $timezone;
	$now = DateTimeValueLib::now();
	$local_ts = $date->getTimestamp() + ($timezone * 3600);
	$now_ts = $now->getTimestamp() + ($timezone * 3600);
	$day_diff = (int) floor($local_ts / 86400) - (int) floor($now_ts / 86400);

	if ($day_diff === 0) {
		$relative = lang('notification due today');
	} elseif ($day_diff === 1) {
		$relative = lang('due tomorrow');
	} elseif ($day_diff === -1) {
		$relative = lang('due yesterday');
	} elseif ($day_diff > 1) {
		$relative = lang('due in n days', $day_diff);
	} else {
		$relative = lang('overdue by n days', abs($day_diff));
	}

	$absolute = $use_time ? format_datetime($date, null, $timezone) : format_date($date, null, $timezone);
	return $relative.' ('.$absolute.')';
}

/**
 * Strip leading/trailing HTML filler from descriptions without corrupt character trimming.
 *
 * @param string $text
 * @return string
 */
function notification_clean_description($text) {
	$text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	$text = preg_replace('/^(?:<br\s*\/?>|&nbsp;|\x{00A0}|\s)+/iu', '', $text);
	$text = preg_replace('/(?:<br\s*\/?>|&nbsp;|\x{00A0}|\s)+$/iu', '', $text);
	// Sanitize before embedding in mail HTML (user-authored content / XSS in clients).
	if (function_exists('purify_html')) {
		return purify_html($text);
	}
	return escape_html_whitespace(convert_to_links(clean($text)));
}

/**
 * Display label for a notification information field.
 * Prefers the installation/object-type property title (field ProjectTasks due_date, etc.)
 * so client renames like "Original Date" show up in emails.
 *
 * @param ContentDataObject $object
 * @param string $field_id
 * @param string|null $fallback
 * @return string
 */
function notification_information_field_label(ContentDataObject $object, $field_id, $fallback = null) {
	$candidates = array($field_id);
	if ($field_id === 'assigned_to') {
		$candidates = array('assigned_to_contact_id', 'assigned_to');
	} elseif ($field_id === 'description') {
		$candidates = array('text', 'description');
	} elseif ($field_id === 'text') {
		$candidates = array('text', 'description');
	} elseif ($field_id === 'start') {
		$candidates = array('start', 'start_date');
	} elseif ($field_id === 'start_date') {
		$candidates = array('start_date', 'start');
	}

	$ot = null;
	if (method_exists($object, 'getObjectTypeId')) {
		$ot = ObjectTypes::instance()->findById($object->getObjectTypeId());
	}
	if ($ot instanceof ObjectType) {
		$handler_class = $ot->getHandlerClass();
		$localization = Localization::instance();
		foreach ($candidates as $candidate) {
			$key_handler = 'field '.$handler_class.' '.$candidate;
			if ($localization->lang_exists($key_handler)) {
				$label = $localization->lang($key_handler);
				if (is_string($label) && trim($label) !== '') {
					return $label;
				}
			}
			$key_objects = 'field Objects '.$candidate;
			if ($localization->lang_exists($key_objects)) {
				$label = $localization->lang($key_objects);
				if (is_string($label) && trim($label) !== '') {
					return $label;
				}
			}
		}
	}

	if ($fallback !== null && $fallback !== '') {
		return $fallback;
	}
	return $field_id;
}

/**
 * Build one information table row.
 *
 * @param string $label
 * @param string $value_html
 * @return string
 */
function notification_information_row($label, $value_html) {
	if ($value_html === null || $value_html === '') {
		return '';
	}
	return '<tr><th>'.clean($label).'</th><td>'.$value_html.'</td></tr>';
}

/**
 * Resolve a property value for the information table.
 *
 * @param ContentDataObject $object
 * @param string $field_id
 * @param array $template_parameters
 * @return array{label:string,value:string}|null
 */
function notification_information_field_value(ContentDataObject $object, $field_id, $template_parameters = array()) {
	Env::useHelper('format');
	$mail_to = array_var($template_parameters, 'mail_to', array());
	$viewer = notification_resolve_email_viewer($template_parameters);
	$timezone = function_exists('get_timezone_for_object_dates')
		? get_timezone_for_object_dates($object, $mail_to, $viewer)
		: 0;

	switch ($field_id) {
		case 'name':
			$link = $object->getViewUrl();
			$value = '<a class="object-name--link" style="color:#0C8275;font-weight:500;" href="'.clean($link).'">'.clean($object->getObjectName()).'</a>';
			return array(
				'label' => notification_information_field_label($object, 'name', lang('name')),
				'value' => $value,
			);

		case 'assigned_to':
		case 'assigned_to_contact_id':
			if (!($object instanceof ProjectTask)) {
				return null;
			}
			$assigned_to = notification_assigned_to_label($object);
			$mode = array_var($template_parameters, 'notification_mode', 'activity');
			$label = notification_information_field_label($object, 'assigned_to_contact_id', lang('assigned to'));
			if ($mode === 'reminder') {
				return array('label' => $label, 'value' => clean($assigned_to));
			}
			$assigned_by = '';
			if ($object->isAssigned()) {
				$by = $object->getAssignedBy();
				if ($by instanceof Contact) {
					$assigned_by = notification_actor_label($by);
				}
			}
			$value = clean($assigned_to);
			if ($assigned_by !== '') {
				$value .= ' '.lang('by').' '.clean($assigned_by);
			}
			return array('label' => $label, 'value' => $value);

		case 'due_date':
			if (!method_exists($object, 'getDueDate') || !($object->getDueDate() instanceof DateTimeValue)) {
				return null;
			}
			$use_time = method_exists($object, 'getUseDueTime') ? $object->getUseDueTime() : false;
			$value = $use_time ? format_datetime($object->getDueDate(), null, $timezone) : format_date($object->getDueDate(), null, $timezone);
			return array(
				'label' => notification_information_field_label($object, 'due_date', lang('due date')),
				'value' => clean($value),
			);

		case 'start_date':
		case 'start':
			$getter = method_exists($object, 'getStartDate') ? 'getStartDate' : (method_exists($object, 'getStart') ? 'getStart' : null);
			if (!$getter || !($object->$getter() instanceof DateTimeValue)) {
				return null;
			}
			$use_time = method_exists($object, 'getUseStartTime') ? $object->getUseStartTime() : true;
			$value = $use_time ? format_datetime($object->$getter(), null, $timezone) : format_date($object->$getter(), null, $timezone);
			return array(
				'label' => notification_information_field_label($object, $field_id, lang('start date')),
				'value' => clean($value),
			);

		case 'status':
			if ($object instanceof ProjectTask || $object instanceof ProjectMilestone) {
				$value = $object->getCompletedById() > 0 ? lang('completed') : lang('pending');
				return array(
					'label' => notification_information_field_label($object, 'status', lang('status')),
					'value' => clean($value),
				);
			}
			return null;

		case 'priority':
			if (!($object instanceof ProjectTask)) {
				return null;
			}
			$priority = lang('normal priority');
			if ($object->getPriority() >= ProjectTasks::PRIORITY_URGENT) {
				$priority = lang('urgent priority');
			} elseif ($object->getPriority() >= ProjectTasks::PRIORITY_HIGH) {
				$priority = lang('high priority');
			} elseif ($object->getPriority() <= ProjectTasks::PRIORITY_LOW) {
				$priority = lang('low priority');
			}
			return array(
				'label' => notification_information_field_label($object, 'priority', lang('priority')),
				'value' => clean($priority),
			);

		case 'classifications':
			$viewer = notification_resolve_email_viewer($template_parameters);
			$class_rows = notification_classification_rows($object, $viewer);
			if (count($class_rows) === 0) {
				return null;
			}
			return array('rows' => $class_rows);

		case 'description':
		case 'text':
			$text = '';
			if (method_exists($object, 'getText')) {
				$text = notification_clean_description($object->getText());
			} elseif (method_exists($object, 'getDescription')) {
				$text = notification_clean_description($object->getDescription());
			}
			if (trim(strip_tags($text)) === '') {
				return null;
			}
			return array(
				'label' => notification_information_field_label($object, $field_id, lang('description')),
				'value' => $text,
			);

		case 'duration':
			if ($object instanceof ProjectEvent && method_exists($object, 'getDuration') && $object->getDuration() instanceof DateTimeValue) {
				return array(
					'label' => notification_information_field_label($object, 'duration', lang('duration')),
					'value' => clean(format_datetime($object->getDuration(), null, $timezone)),
				);
			}
			return null;
	}

	if (str_starts_with($field_id, 'cp_')) {
		$cp_id = (int) substr($field_id, 3);
		$cp = CustomProperties::getCustomProperty($cp_id);
		if (!$cp instanceof CustomProperty) {
			return null;
		}
		$value = $object->getCustomPropertyValue($cp_id);
		if ($value === null || $value === '') {
			return null;
		}
		if (is_array($value)) {
			$value = implode(', ', $value);
		}
		return array('label' => $cp->getName(), 'value' => clean((string) $value));
	}

	if (method_exists($object, 'getColumnValue') && $object->columnExists($field_id)) {
		$raw = $object->getColumnValue($field_id);
		if ($raw === null || $raw === '') {
			return null;
		}
		if ($raw instanceof DateTimeValue) {
			$raw = format_datetime($raw, null, $timezone);
		}
		return array(
			'label' => notification_information_field_label($object, $field_id, $field_id),
			'value' => clean((string) $raw),
		);
	}

	return null;
}

/**
 * Project member for an object, if classified in a project.
 *
 * @param ContentDataObject $object
 * @return Member|null
 */
function notification_object_project_member(ContentDataObject $object) {
	$member = null;

	$project_ot = ObjectTypes::instance()->findByName('project');
	if ($project_ot instanceof ObjectType) {
		$member = $object->getMemberOfType($project_ot->getId());
	}

	// Fallback: first manageable member in the customer_project / projects dimension.
	if (!$member instanceof Member) {
		foreach (array('customer_project', 'projects') as $dim_code) {
			$dim = Dimensions::findByCode($dim_code);
			if (!$dim instanceof Dimension) {
				continue;
			}
			$candidate = $object->getMemberByDimensionId($dim->getId());
			if ($candidate instanceof Member) {
				$member = $candidate;
				break;
			}
		}
	}

	return $member instanceof Member ? $member : null;
}

/**
 * Project member display name for an object, if classified in a project.
 *
 * @param ContentDataObject $object
 * @return string
 */
function notification_object_project_name(ContentDataObject $object) {
	$member = notification_object_project_member($object);
	if (!$member instanceof Member) {
		return '';
	}

	if (class_exists('Notifier') && method_exists('Notifier', 'memberLabelForNotification')) {
		$label = trim(Notifier::memberLabelForNotification($member));
		if ($label !== '') {
			return $label;
		}
	}
	if (method_exists($member, 'getDisplayName')) {
		$label = trim($member->getDisplayName());
		if ($label !== '') {
			return $label;
		}
	}
	return trim($member->getName());
}

/**
 * Plain-text object heading: "Project X / Task Y".
 *
 * @param ContentDataObject $object
 * @return string
 */
function notification_object_heading_text(ContentDataObject $object) {
	$ot = ObjectTypes::instance()->findById($object->getObjectTypeId());
	$type_name = $ot instanceof ObjectType ? $ot->getObjectTypeName() : lang('object');
	$name = $object->getObjectName();
	$project = notification_object_project_name($object);
	$object_line = $type_name . ' ' . $name;
	if ($project !== '') {
		$eyebrow = Localization::instance()->lang('notification heading project eyebrow');
		if (!$eyebrow) {
			$eyebrow = 'Project';
		}
		return $eyebrow . ' ' . $project . ' / ' . $object_line;
	}
	return $object_line;
}

/**
 * HTML for a project section heading: eyebrow + plain name with teal rail (not a link).
 * Only the object/task name is clickable in notification emails.
 *
 * @param Member $project_member
 * @return string
 */
function notification_project_heading_html(Member $project_member) {
	$project = '';
	if (class_exists('Notifier') && method_exists('Notifier', 'memberLabelForNotification')) {
		$project = trim(Notifier::memberLabelForNotification($project_member));
	}
	if ($project === '') {
		$project = method_exists($project_member, 'getDisplayName')
			? trim($project_member->getDisplayName())
			: trim($project_member->getName());
	}
	if ($project === '') {
		return '';
	}
	$label = Localization::instance()->lang('notification heading project eyebrow');
	if (!$label) {
		$label = 'Project';
	}
	return '<div class="digest-project-heading">'
		.'<div class="digest-eyebrow">'.clean($label).'</div>'
		.'<div class="digest-project-name">'.clean($project).'</div>'
		.'</div>';
}

/**
 * HTML for an object line under a project: muted type + linked name.
 *
 * @param ContentDataObject $object
 * @return string
 */
function notification_object_only_heading_html(ContentDataObject $object) {
	$ot = ObjectTypes::instance()->findById($object->getObjectTypeId());
	$type_name = $ot instanceof ObjectType ? $ot->getObjectTypeName() : lang('object');
	$name = $object->getObjectName();
	$link = $object->getViewUrl();
	return '<div class="digest-object-heading object-type-name">'
		.'<span class="digest-object-type">'.clean($type_name).'</span>'
		.'<a class="digest-object-name object-name--link" href="'.clean($link).'">'.clean($name).'</a>'
		.'</div>';
}

/**
 * HTML object heading for single activity/reminder emails.
 * Project shell wraps the object line so hierarchy reads clearly.
 *
 * @param ContentDataObject $object
 * @return string
 */
function notification_object_heading_html(ContentDataObject $object) {
	$object_line = notification_object_only_heading_html($object);
	$project_member = notification_object_project_member($object);
	if ($project_member instanceof Member) {
		return '<div class="digest-project-shell">'
			.notification_project_heading_html($project_member)
			.'<div class="digest-project-body">'.$object_line.'</div>'
			.'</div>';
	}
	return $object_line;
}

/**
 * Content object that a log entry's heading should refer to (comment → parent).
 *
 * @param ApplicationLog $log
 * @return ContentDataObject|null
 */
function notification_log_heading_object(ApplicationLog $log) {
	$object = $log->getObject();
	if ($object instanceof Comment) {
		$rel = $object->getRelObject();
		return $rel instanceof ContentDataObject ? $rel : null;
	}
	return $object instanceof ContentDataObject ? $object : null;
}

/**
 * Sort digest logs by project name, then object name (stable by ids).
 * Does not drop entries — only reorders for grouped rendering.
 *
 * @param array $logs
 * @return array
 */
function notification_sort_logs_by_project_then_object($logs) {
	if (!is_array($logs) || count($logs) < 2) {
		return $logs;
	}
	$decorated = array();
	foreach ($logs as $idx => $log) {
		if (!$log instanceof ApplicationLog) {
			continue;
		}
		$obj = notification_log_heading_object($log);
		$project_member = $obj instanceof ContentDataObject ? notification_object_project_member($obj) : null;
		$project_name = '';
		$project_id = 0;
		if ($project_member instanceof Member) {
			$project_id = (int) $project_member->getId();
			$project_name = notification_object_project_name($obj);
		}
		$object_id = $obj instanceof ContentDataObject ? (int) $obj->getId() : (int) $log->getRelObjectId();
		$object_name = $obj instanceof ContentDataObject ? (string) $obj->getObjectName() : (string) $log->getObjectName();
		$decorated[] = array(
			'idx' => $idx,
			'log' => $log,
			'project_id' => $project_id,
			'project_name' => mb_strtolower($project_name),
			'object_id' => $object_id,
			'object_name' => mb_strtolower($object_name),
			'has_project' => $project_id > 0 ? 0 : 1, // projects first, then ungrouped
		);
	}
	usort($decorated, function ($a, $b) {
		if ($a['has_project'] !== $b['has_project']) {
			return $a['has_project'] - $b['has_project'];
		}
		$cmp = strcmp($a['project_name'], $b['project_name']);
		if ($cmp !== 0) {
			return $cmp;
		}
		if ($a['project_id'] !== $b['project_id']) {
			return $a['project_id'] - $b['project_id'];
		}
		$cmp = strcmp($a['object_name'], $b['object_name']);
		if ($cmp !== 0) {
			return $cmp;
		}
		if ($a['object_id'] !== $b['object_id']) {
			return $a['object_id'] - $b['object_id'];
		}
		return $a['idx'] - $b['idx'];
	});
	$sorted = array();
	foreach ($decorated as $row) {
		$sorted[] = $row['log'];
	}
	return $sorted;
}

/**
 * Render the configurable information table for any content object.
 *
 * @param ContentDataObject $object
 * @param array $template_parameters
 * @return string
 */
function build_notification_object_information_html(ContentDataObject $object, $template_parameters = array()) {
	$ot = ObjectTypes::instance()->findById($object->getObjectTypeId());
	$ot_name = $ot instanceof ObjectType ? $ot->getName() : 'object';
	$fields = notification_information_fields_for_object_type($ot_name);
	$mode = array_var($template_parameters, 'notification_mode', 'activity');

	$rows = '';
	foreach ($fields as $field_id) {
		// Name is shown in the section title (same as daily summary), not as a table row.
		if ($field_id === 'name' || $field_id === 'object.name') {
			continue;
		}
		// Dimension filters are applied inside classifications rendering, not as rows.
		if (str_starts_with((string) $field_id, 'classification_dim_')) {
			continue;
		}
		$resolved = notification_information_field_value($object, $field_id, $template_parameters);
		if (!is_array($resolved)) {
			continue;
		}
		if (isset($resolved['rows']) && is_array($resolved['rows'])) {
			foreach ($resolved['rows'] as $class_row) {
				if (!is_array($class_row)) {
					continue;
				}
				$rows .= notification_information_row(
					array_var($class_row, 'label', ''),
					array_var($class_row, 'value', '')
				);
			}
			continue;
		}
		$rows .= notification_information_row($resolved['label'], $resolved['value']);
	}

	// One heading at the top (object.heading) for activity; reminders keep title inside the card.
	$omit_title = (bool) array_var($template_parameters, 'object_information_omit_title', false);
	$title = notification_object_heading_html($object);

	$html = notification_email_styles($mode);
	$html .= '<div class="object-info-card">';
	if (!$omit_title) {
		$html .= '<div class="object-type-name">'.$title.'</div>';
	}
	if ($rows !== '') {
		$html .= '<table class="object-info--table task-info--table" role="presentation" width="100%">';
		$html .= $rows.'</table>';
	}
	$html .= '</div>';
	return $html;
}

/**
 * Reminder banner with explicit due/start messaging.
 *
 * @param ContentDataObject $object
 * @param array $template_parameters
 * @return string
 */
function build_notification_reminder_banner_html(ContentDataObject $object, $template_parameters = array()) {
	Env::useHelper('format');
	$context = array_var($template_parameters, 'reminder_context', 'due_date');
	$mail_to = array_var($template_parameters, 'mail_to', array());
	$viewer = notification_resolve_email_viewer($template_parameters);
	$timezone = function_exists('get_timezone_for_object_dates')
		? get_timezone_for_object_dates($object, $mail_to, $viewer)
		: 0;

	$date = null;
	$use_time = false;
	if ($context === 'start_date' || $context === 'start') {
		if (method_exists($object, 'getStartDate') && $object->getStartDate() instanceof DateTimeValue) {
			$date = $object->getStartDate();
			$use_time = method_exists($object, 'getUseStartTime') ? $object->getUseStartTime() : true;
		} elseif (method_exists($object, 'getStart') && $object->getStart() instanceof DateTimeValue) {
			$date = $object->getStart();
			$use_time = true;
		}
		$title = lang('start date reminder title');
	} else {
		if (method_exists($object, 'getDueDate') && $object->getDueDate() instanceof DateTimeValue) {
			$date = $object->getDueDate();
			$use_time = method_exists($object, 'getUseDueTime') ? $object->getUseDueTime() : false;
		}
		$title = lang('due date reminder title');
	}

	if (!$date instanceof DateTimeValue) {
		return '';
	}

	$when = notification_reminder_when_label($date, $timezone, $use_time);
	$heading = notification_object_heading_text($object);

	return '<div class="reminder-banner"><strong>'.clean($title).'</strong>'
		.clean(lang('reminder banner body', $heading, $when))
		.'</div>';
}

/**
 * Subject line for reminders.
 *
 * @param ContentDataObject $object
 * @param string $context due_date|start_date|start
 * @param int|float $timezone
 * @return string
 */
function build_notification_reminder_subject(ContentDataObject $object, $context = 'due_date', $timezone = 0) {
	$ot = ObjectTypes::instance()->findById($object->getObjectTypeId());
	$type_name = $ot instanceof ObjectType ? $ot->getObjectTypeName() : lang('object');
	$name = $object->getObjectName();

	$date = null;
	$use_time = false;
	if ($context === 'start_date' || $context === 'start') {
		if (method_exists($object, 'getStartDate') && $object->getStartDate() instanceof DateTimeValue) {
			$date = $object->getStartDate();
			$use_time = method_exists($object, 'getUseStartTime') ? $object->getUseStartTime() : true;
		} elseif (method_exists($object, 'getStart') && $object->getStart() instanceof DateTimeValue) {
			$date = $object->getStart();
			$use_time = true;
		}
		$prefix = lang('start reminder subject prefix');
	} else {
		if (method_exists($object, 'getDueDate') && $object->getDueDate() instanceof DateTimeValue) {
			$date = $object->getDueDate();
			$use_time = method_exists($object, 'getUseDueTime') ? $object->getUseDueTime() : false;
		}
		$prefix = lang('due reminder subject prefix');
	}

	$when = $date instanceof DateTimeValue
		? notification_reminder_when_label($date, $timezone, $use_time)
		: '';

	return trim($prefix.': '.$type_name.' '.$name.($when !== '' ? ' — '.$when : ''));
}
