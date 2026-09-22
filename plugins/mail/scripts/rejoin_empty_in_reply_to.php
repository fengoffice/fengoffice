<?php
/**
 * On-demand repair for mails with empty in_reply_to_id.
 *
 * Re-reads raw MIME (supports Outlook/Exchange folded headers), restores
 * In-Reply-To (with References fallback), and rejoins orphaned conversations.
 *
 * Usage (from FO root):
 *   php plugins/mail/scripts/rejoin_empty_in_reply_to.php --days=45
 *   php plugins/mail/scripts/rejoin_empty_in_reply_to.php 45
 *   php plugins/mail/scripts/rejoin_empty_in_reply_to.php --days=45 --dry-run
 *
 * Options:
 *   --days=N   Look back N days (required, or pass N as first positional arg)
 *   --dry-run  Report what would change without writing to the DB
 *
 * Ops: always run with --dry-run first and keep the output before applying.
 */

chdir(dirname(dirname(dirname(__DIR__))));
define('CONSOLE_MODE', true);
define('PUBLIC_FOLDER', 'public');
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SCRIPT_NAME'] = '/plugins/mail/scripts/rejoin_empty_in_reply_to.php';
$_SERVER['PHP_SELF'] = '/plugins/mail/scripts/rejoin_empty_in_reply_to.php';

include 'init.php';
require_once ROOT . '/plugins/mail/application/helpers/MailUtilities.class.php';

@set_time_limit(0);
ini_set('memory_limit', '512M');

function print_usage() {
	fwrite(STDERR, "Usage:\n");
	fwrite(STDERR, "  php plugins/mail/scripts/rejoin_empty_in_reply_to.php --days=45 [--dry-run]\n");
	fwrite(STDERR, "  php plugins/mail/scripts/rejoin_empty_in_reply_to.php 45 [--dry-run]\n");
}

$days = null;
$dry_run = false;
foreach (array_slice($argv, 1) as $arg) {
	if ($arg === '--dry-run' || $arg === '-n') {
		$dry_run = true;
		continue;
	}
	if (preg_match('/^--days=(\d+)$/', $arg, $m)) {
		$days = (int)$m[1];
		continue;
	}
	if (preg_match('/^\d+$/', $arg)) {
		$days = (int)$arg;
		continue;
	}
	if ($arg === '--help' || $arg === '-h') {
		print_usage();
		exit(0);
	}
	fwrite(STDERR, "Unknown argument: $arg\n");
	print_usage();
	exit(1);
}

if ($days === null || $days < 1) {
	fwrite(STDERR, "Error: --days=N is required (N >= 1).\n");
	print_usage();
	exit(1);
}

$since = date('Y-m-d H:i:s', strtotime('-' . $days . ' days'));
echo "Rejoining mails with empty in_reply_to_id since $since ({$days} days)\n";
if ($dry_run) {
	echo "DRY-RUN: no DB writes will be performed\n";
}
echo "\n";

$rows = DB::executeAll("
	SELECT `object_id`
	FROM `" . TABLE_PREFIX . "mail_contents`
	WHERE (`in_reply_to_id` = '' OR `in_reply_to_id` IS NULL)
		AND `received_date` >= " . DB::escape($since) . "
		AND `state` <> 2
	ORDER BY `received_date` ASC
");

if (!is_array($rows) || count($rows) == 0) {
	echo "No matching mails found.\n";
	exit(0);
}

$scanned = 0;
$filled_irt = 0;
$joined_by_irt = 0;
$joined_by_ref = 0;
$skipped = 0;
$errors = 0;

foreach ($rows as $row) {
	$scanned++;
	$mail = MailContents::instance()->findById($row['object_id']);
	if (!$mail instanceof MailContent) {
		$skipped++;
		continue;
	}

	try {
		$content = $mail->getContent();
	} catch (Exception $e) {
		$errors++;
		echo "ERROR read content mail {$row['object_id']}: " . $e->getMessage() . "\n";
		Logger::log("rejoin_empty_in_reply_to: cannot read content for mail " . $row['object_id'] . " - " . $e->getMessage());
		continue;
	}
	if (!$content) {
		$skipped++;
		continue;
	}

	$threading = MailUtilities::extractThreadingIdsFromRawContent($content);
	$in_reply_to_ids = $threading['in_reply_to_ids'];
	$reference_ids = $threading['reference_ids'];
	$parent_ids = array();
	foreach ($in_reply_to_ids as $parent_id) {
		if ($parent_id !== '' && !in_array($parent_id, $parent_ids)) {
			$parent_ids[] = $parent_id;
		}
	}
	foreach (array_reverse($reference_ids) as $parent_id) {
		if ($parent_id !== '' && !in_array($parent_id, $parent_ids)) {
			$parent_ids[] = $parent_id;
		}
	}
	if (count($parent_ids) == 0) {
		$skipped++;
		continue;
	}

	$stored_irt = $threading['in_reply_to_id'];
	if ($stored_irt == '' && count($reference_ids)) {
		$stored_irt = $reference_ids[count($reference_ids) - 1];
	}

	$parent_mail = null;
	$join_source = '';
	if (count($parent_ids) > 0) {
		$escaped_ids = array();
		foreach ($parent_ids as $parent_id) {
			$escaped_ids[] = DB::escape($parent_id);
		}
		$candidates = MailContents::instance()->findAll(array(
			'conditions' => '`account_id` = ' . DB::escape($mail->getAccountId())
				. ' AND `message_id` IN (' . implode(',', $escaped_ids) . ')'
				. ' AND `object_id` <> ' . DB::escape($mail->getId())
		));
		$by_message_id = array();
		if (is_array($candidates)) {
			foreach ($candidates as $candidate) {
				$by_message_id[$candidate->getMessageId()] = $candidate;
			}
		}
		foreach ($parent_ids as $parent_id) {
			if (isset($by_message_id[$parent_id])) {
				$parent_mail = $by_message_id[$parent_id];
				$join_source = in_array($parent_id, $in_reply_to_ids) ? 'irt' : 'ref';
				break;
			}
		}
	}

	$old_conv_id = (int)$mail->getConversationId();
	$new_conv_id = $old_conv_id;
	$should_join = false;
	if ($parent_mail instanceof MailContent
		&& (int)$parent_mail->getConversationId() > 0
		&& (int)$parent_mail->getConversationId() != $old_conv_id) {
		$new_conv_id = (int)$parent_mail->getConversationId();
		$should_join = true;
	}

	$will_fill_irt = ($stored_irt != '' && $mail->getInReplyToId() != $stored_irt);
	if (!$will_fill_irt && !$should_join) {
		$skipped++;
		continue;
	}

	echo "mail {$mail->getId()}:";
	if ($will_fill_irt) {
		echo " set in_reply_to_id={$stored_irt}";
	}
	if ($should_join) {
		echo " join conv {$old_conv_id}->{$new_conv_id} via {$join_source}";
		if ($old_conv_id <= 0) {
			echo " (single-mail only; skip mass update for conv 0)";
		}
	}
	echo $dry_run ? " [dry-run]\n" : "\n";

	if ($dry_run) {
		if ($will_fill_irt) {
			$filled_irt++;
		}
		if ($should_join) {
			if ($join_source == 'irt') {
				$joined_by_irt++;
			} else {
				$joined_by_ref++;
			}
		}
		continue;
	}

	$dirty = false;
	if ($will_fill_irt) {
		$mail->setInReplyToId($stored_irt);
		$dirty = true;
	}
	if ($should_join) {
		$mail->setConversationId($new_conv_id);
		$dirty = true;
	}

	if (!$dirty) {
		continue;
	}

	try {
		DB::beginWork();

		$mail->save();

		if ($should_join) {
			// Never UPDATE by conversation_id 0/NULL: that would pull every
			// legacy unthreaded mail in the account into the parent thread.
			if ($old_conv_id > 0) {
				DB::execute("
					UPDATE `" . TABLE_PREFIX . "mail_contents`
					SET `conversation_id` = " . DB::escape($new_conv_id) . "
					WHERE `account_id` = " . DB::escape($mail->getAccountId()) . "
						AND `conversation_id` = " . DB::escape($old_conv_id) . "
				");
			}
		}

		DB::commit();
	} catch (Exception $e) {
		try {
			DB::rollback();
		} catch (Exception $rollback_e) {
			// ignore rollback errors
		}
		$errors++;
		echo "ERROR mail {$row['object_id']}: " . $e->getMessage() . "\n";
		Logger::log("rejoin_empty_in_reply_to: cannot process mail " . $row['object_id'] . " - " . $e->getMessage());
		continue;
	}

	if ($will_fill_irt) {
		$filled_irt++;
	}
	if ($should_join) {
		if ($join_source == 'irt') {
			$joined_by_irt++;
		} else {
			$joined_by_ref++;
		}

		try {
			$parent_mail->orderConversation();
			$reloaded = MailContents::instance()->findById($mail->getId(), true);
			if ($reloaded instanceof MailContent) {
				$reloaded->orderConversation();
			}
		} catch (Exception $e) {
			echo "WARN orderConversation mail {$row['object_id']}: " . $e->getMessage() . "\n";
			Logger::log("rejoin_empty_in_reply_to: orderConversation failed for mail " . $row['object_id'] . " - " . $e->getMessage());
		}
	}
}

$summary = "scanned=$scanned filled_irt=$filled_irt joined_by_irt=$joined_by_irt joined_by_ref=$joined_by_ref skipped=$skipped errors=$errors";
echo "\nDone ($summary)" . ($dry_run ? " [dry-run]" : "") . "\n";
Logger::log("rejoin_empty_in_reply_to: days=$days dry_run=" . ($dry_run ? '1' : '0') . " $summary");
exit($errors > 0 ? 1 : 0);
