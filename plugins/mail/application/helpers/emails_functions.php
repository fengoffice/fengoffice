<?php

/**
 * Data provider + helpers for the "emails" dashboard widget (see
 * application/helpers/dimension_widget.php for the generic feed shell that calls this).
 *
 * Lives in the mail plugin (not evx_widgets, not core) because it queries MailContents/
 * MailAccounts and fires MailController actions directly — the widget only makes sense
 * when mail is active, so it's registered as one of mail's own widgets (see
 * plugins/mail/update.php). It replaces the plugin's previous simple "unread emails"
 * widget (formerly application/widgets/emails, which was a core-owned row despite
 * depending on mail) in place under the same widget name.
 */

/**
 * Deterministic background color for an initials avatar, picked from a fixed palette by
 * hashing the seed (sender name/email) — same person always gets the same color.
 */
function emails_widget_avatar_color($seed) {
	$palette = array('#1b75bc', '#7c3aed', '#e6007e', '#d99a2b', '#0891b2', '#2a9d8f', '#c0392b', '#546e7a');
	// crc32() is documented to return negative ints on 32-bit builds (it doesn't on the 64-bit
	// PHP this app runs on, but abs() makes the modulo safe regardless of platform).
	return $palette[abs(crc32((string)$seed)) % count($palette)];
}

/**
 * Up to 2 uppercase initials from a display name (first letter of the first two words).
 */
function emails_widget_initials($name) {
	$name = trim((string)$name);
	if ($name === '') {
		return '?';
	}
	$initials = '';
	foreach (preg_split('/\s+/', $name) as $part) {
		if ($part === '') continue;
		$initials .= mb_strtoupper(mb_substr($part, 0, 1, 'UTF-8'), 'UTF-8');
		if (mb_strlen($initials, 'UTF-8') >= 2) break;
	}
	return $initials !== '' ? $initials : '?';
}

function emails_widget_truncate($text, $length) {
	if (function_exists('mb_strlen') && function_exists('mb_substr')) {
		return mb_strlen($text, 'UTF-8') > $length ? mb_substr($text, 0, $length, 'UTF-8') . '...' : $text;
	}
	return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}

/**
 * Data provider for the emails widget. Backed by MailContents::getEmails() — the same
 * query the previous emails widget used — but showing recent mail (read + unread) with an
 * unread indicator, instead of unread-only.
 *
 * v1 scope: "accounts" checklist filter, initials avatar, per-account indicator (shown only
 * when the fetched page actually spans more than one account), and a hover action bar wired
 * to the real mail plugin actions (reply_mail/forward_mail/change via generic object archive/
 * mail delete) — archive/delete trigger a widget reload via evxWidgetReload_<genid> since
 * these rows are plain server-rendered HTML, not React-controlled.
 *
 * @param int      $limit
 * @param array    $filter_values  ['accounts' => [account_id, ...]]
 * @param string   $genid          Widget instance id, for wiring the reload-after-mutation calls
 * @return array|false
 */
function emails_data_provider($limit, $filter_values, $genid = null) {
	if (!logged_user()->hasMailAccounts()) {
		return false;
	}

	$account_name_by_id = array();
	foreach (MailAccounts::getMailAccountsByUser(logged_user()) as $account) { /* @var $account MailAccount */
		$account_name_by_id[(int)$account->getId()] = $account->getName() ? $account->getName() : $account->getEmail();
	}

	$selected_accounts = array_var($filter_values, 'accounts', array());
	$account_id = null;
	if (is_array($selected_accounts) && !empty($selected_accounts)) {
		$account_id = implode(',', array_map('intval', $selected_accounts));
	}

	$result = MailContents::getEmails($account_id, 'received', 'all', null, null, 0, $limit, 'received_date', 'DESC', null, 'unarchived', null, false, '', true);
	$emails = (is_object($result) && isset($result->objects) && is_array($result->objects)) ? $result->objects : array();
	if (empty($emails)) {
		return false;
	}

	$email_ids = array();
	foreach ($emails as $email) { /* @var $email MailContent */
		$email_ids[] = (int)$email->getId();
	}

	$read_ids = array();
	$read_rows = DB::executeAll(
		"SELECT rel_object_id FROM " . TABLE_PREFIX . "read_objects
		 WHERE contact_id = " . logged_user()->getId() . " AND is_read = '1' AND rel_object_id IN (" . implode(',', $email_ids) . ")"
	);
	if (is_array($read_rows)) {
		foreach ($read_rows as $r) {
			$read_ids[] = (int)$r['rel_object_id'];
		}
	}

	$distinct_account_ids = array();
	foreach ($emails as $email) { /* @var $email MailContent */
		$distinct_account_ids[(int)$email->getAccountId()] = true;
	}
	$show_account_indicator = count($distinct_account_ids) > 1;

	$items_html = array();
	$unread_count = 0;
	foreach ($emails as $email) { /* @var $email MailContent */
		$email_id = (int)$email->getId();
		$is_unread = !in_array($email_id, $read_ids);
		if ($is_unread) $unread_count++;
		$from = clean($email->getFrom());
		$subject = clean($email->getSubject());
		$snippet = clean(emails_widget_truncate(trim(strip_tags((string)$email->getTextBody())), 90));
		$when = $email->getSentDate() instanceof DateTimeValue ? friendly_date($email->getSentDate()) : '';

		$avatar_seed = $email->getFrom() !== '' ? $email->getFrom() : $email_id;
		$avatar_html = '<span class="fo-widget-avatar--initials" style="background:' . emails_widget_avatar_color($avatar_seed) . ';">'
			. clean(emails_widget_initials($from)) . '</span>';

		$account_line = '';
		if ($show_account_indicator) {
			$acc_name = isset($account_name_by_id[(int)$email->getAccountId()]) ? $account_name_by_id[(int)$email->getAccountId()] : '';
			if ($acc_name !== '') {
				$account_line = '<span class="fo-widget-mail-account"><i class="icon-mail"></i>' . clean($acc_name) . '</span>';
			}
		}

		$actions_html = '';
		if ($genid) {
			$reload_call = "if (window['evxWidgetReload_" . $genid . "']) window['evxWidgetReload_" . $genid . "']();";
			$actions_html =
				'<div class="fo-widget-mail-actions">' .
					'<button type="button" class="fo-widget-mail-action" title="' . lang('reply mail') . '" '
						. 'onclick="event.stopPropagation(); og.openLink(og.getUrl(\'mail\',\'reply_mail\',{id:' . $email_id . '})); return false;">'
						. '<i class="icon-reply"></i></button>' .
					'<button type="button" class="fo-widget-mail-action" title="' . lang('forward mail') . '" '
						. 'onclick="event.stopPropagation(); og.openLink(og.getUrl(\'mail\',\'forward_mail\',{id:' . $email_id . '})); return false;">'
						. '<i class="icon-forward"></i></button>' .
					'<button type="button" class="fo-widget-mail-action" title="' . lang('archive') . '" '
						. 'onclick="event.stopPropagation(); og.openLink(og.getUrl(\'object\',\'archive\',{ids:' . $email_id . '}), {postProcess:function(ok){ if (ok) { ' . $reload_call . ' } }}); return false;">'
						. '<i class="icon-archive"></i></button>' .
					'<button type="button" class="fo-widget-mail-action" title="' . lang('delete') . '" '
						. 'onclick="event.stopPropagation(); og.openLink(og.getUrl(\'mail\',\'delete\',{id:' . $email_id . '}), {postProcess:function(ok){ if (ok) { ' . $reload_call . ' } }}); return false;">'
						. '<i class="icon-trash-2"></i></button>' .
				'</div>';
		}

		$items_html[] =
			'<div class="fo-widget-list-item is-mail-item' . ($is_unread ? ' is-unread' : '') . '">' .
				'<div class="fo-widget-list-lead">' . $avatar_html . '</div>' .
				'<div class="fo-widget-list-body">' .
					'<span class="fo-widget-email-from">' . ($is_unread ? '<span class="fo-widget-unread-dot"></span>' : '') . $from . '</span>' .
					'<a href="' . $email->getViewUrl() . '" class="fo-widget-cell-title" '
						. 'onclick="event.stopPropagation(); og.openLink(og.getUrl(\'mail\',\'view\',{id:' . $email_id . '})); return false;">'
						. ($subject !== '' ? $subject : lang('no subject')) . '</a>' .
					'<span class="fo-widget-cell-sub">' . $snippet . '</span>' .
				'</div>' .
				'<div class="fo-widget-list-meta">' . clean($when) . $account_line . '</div>' .
				$actions_html .
			'</div>';
	}

	return array(
		'items_html'   => $items_html,
		'widget_title' => lang('emails'),
		// Unread count within the currently displayed items — NOT MailContents::countUserInboxUnreadEmails(),
		// which calls the undefined permissions_sql_for_listings() in this rendering context and references
		// columns (a.id, a.trashed_on, a.archived_by_id) that don't exist on fo_mail_contents; using it here
		// crashed the whole dashboard (everything rendered after this widget in the same request never ran).
		'title_count'  => $unread_count,
	);
}
