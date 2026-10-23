<?php

function can_send_outbox_in_background() {
	return is_exec_available();
}

function send_outbox_emails_in_background($account) {
	
	if (!$account instanceof MailAccount) {
		Logger::log("Cant send outbox emails in background, account is null");
	}
	
	$user = logged_user();
	
	if (substr(php_uname(), 0, 7) == "Windows" || !can_send_outbox_in_background()) {
		//pclose(popen("start /B ". $command, "r"));
		$from_time = DateTimeValueLib::now();
		$from_time = $from_time->add('h', -24);
		
		$mc = new MailController();
		$mc->send_outbox_mails($user, $account, $from_time);
		
	} else {
		
		$script_path = ROOT . "/plugins/mail/application/helpers/send_outbox_emails.php";
		$command = "nice -n19 ".PHP_PATH." $script_path ".ROOT." ".$user->getId()." ".$user->getTwistedToken()." ".$account->getId();
		exec("$command > /dev/null &");
		
	}
}


/***************************************
 * IMAP folders functions
 **************************************/


/**
 * Remove a mail from the IMAP folders
 * @param object $account the MailAccount object
 * @param object $imap the IMAP connection object
 * @param object $mail the MailContent object
 * @param array $folders_to_remove the names of the folders to remove the mail from
 * @param array $options Optional: allow_message_id_fallback (bool, default false) — slow full-folder search only when true
 */
function remove_mail_from_imap_folders($account, $imap, $mail, $folders_to_remove, $options = null) {
	if (!is_array($options)) {
		$options = array();
	}
	$allow_message_id_fallback = !empty($options['allow_message_id_fallback']);

	foreach ($folders_to_remove as $folder_name) {
		if (is_null($folder_name)) {
			continue;
		}
		
		// if for any reason we have more than one uid for the same email in the same folder try to delete all of them
		$uids_in_folder_rows = DB::executeAll("SELECT uid FROM ".TABLE_PREFIX."mail_content_imap_folders
					WHERE object_id=".$mail->getId()." AND folder=".DB::escape($folder_name));
			
		$folder = utf8_decode($folder_name);
		$imap->selectMailbox($folder);
			
		$uids_in_folder = array_filter(array_flat($uids_in_folder_rows));
		
		if ((!$uids_in_folder || count($uids_in_folder) == 0) && $allow_message_id_fallback) {
			$uids_in_folder = $imap->search('HEADER Message-ID '.$mail->getMessageId(), true);
			if (PEAR::isError($uids_in_folder)) {
				$uids_in_folder = array();
			}
		}
			
		if (is_array($uids_in_folder) && count($uids_in_folder)) {
			$uids_str = implode(',', $uids_in_folder);
			$result = $imap->deleteMessages($uids_str, true);
			$imap->expunge();
	
			// delete regs in mail_content_imap_folders for this folder
			$sql = "DELETE FROM ".TABLE_PREFIX."mail_content_imap_folders
					WHERE object_id=".$mail->getId()." AND folder=".DB::escape($folder_name);
			DB::executeAll($sql);
				
		}
	}
}

/**
 * Delete many messages on the IMAP server in one session per account: one EXPUNGE per folder.
 * Removes matching rows from mail_content_imap_folders for the given mails.
 *
 * @param MailContent[] $mails
 */
function remove_mails_from_imap_server_batch(array $mails) {
	if (!Plugins::instance()->isActivePlugin('mail') || count($mails) < 2) {
		return;
	}

	$by_account = array();
	foreach ($mails as $mail) {
		if (!$mail instanceof MailContent) {
			continue;
		}
		$account = $mail->getAccount();
		if (!$account instanceof MailAccount || !$account->getIsImap()) {
			continue;
		}
		$aid = (int) $account->getId();
		if (!isset($by_account[$aid])) {
			$by_account[$aid] = array(
				'account' => $account,
				'mails' => array(),
			);
		}
		$by_account[$aid]['mails'][$mail->getId()] = $mail;
	}

	foreach ($by_account as $aid => $bundle) {
		$account = $bundle['account'];
		$mails_in_acc = array_values($bundle['mails']);
		$object_ids = array();
		foreach ($mails_in_acc as $m) {
			$object_ids[] = (int) $m->getId();
		}
		$object_ids = array_unique(array_filter($object_ids));
		if (count($object_ids) == 0) {
			continue;
		}

		$ids_str = implode(',', $object_ids);
		$rows = DB::executeAll(
			"SELECT object_id, folder, uid FROM ".TABLE_PREFIX."mail_content_imap_folders
			WHERE account_id=".(int) $aid." AND object_id IN (".$ids_str.")"
		);
		if (!is_array($rows)) {
			$rows = array();
		}

		$object_ids_with_rows = array();
		$folder_uids = array();
		foreach ($rows as $r) {
			$fn = array_var($r, 'folder');
			if ($fn === '' || $fn === null) {
				continue;
			}
			$object_ids_with_rows[(int) array_var($r, 'object_id')] = true;
			if (!isset($folder_uids[$fn])) {
				$folder_uids[$fn] = array();
			}
			$uid = trim((string) array_var($r, 'uid'));
			if ($uid !== '') {
				$folder_uids[$fn][] = $uid;
			}
		}

		$imap_cleanup_done = false;
		try {
			$imap = $account->imapConnect();
			$login_ret = $account->imapLogin($imap);
			if (PEAR::isError($login_ret)) {
				Logger::log(
					"remove_mails_from_imap_server_batch: IMAP login failed: ".$login_ret->getMessage(),
					Logger::WARNING,
					null,
					'imap_delete'
				);
			} else {
				foreach ($folder_uids as $folder_name => $uids) {
					$uids = array_unique(array_filter($uids));
					if (count($uids) == 0) {
						continue;
					}
					$imap->selectMailbox(utf8_decode($folder_name));
					$imap->deleteMessages(implode(',', $uids), true);
					$imap->expunge();
				}

				foreach ($mails_in_acc as $mail) {
					if (empty($object_ids_with_rows[$mail->getId()])) {
						$fallback_folders = array_filter(array($mail->getImapFolderName()));
						if (count($fallback_folders) > 0) {
							remove_mail_from_imap_folders(
								$account,
								$imap,
								$mail,
								$fallback_folders,
								array('allow_message_id_fallback' => true)
							);
						}
					}
				}

				$imap->disconnect();
				$imap_cleanup_done = true;
			}
		} catch (Exception $e) {
			Logger::log(
				"remove_mails_from_imap_server_batch: ".$e->getMessage(),
				Logger::WARNING,
				null,
				'imap_delete'
			);
		}

		if ($imap_cleanup_done) {
			DB::execute(
				"DELETE FROM ".TABLE_PREFIX."mail_content_imap_folders WHERE account_id=".(int) $aid." AND object_id IN (".$ids_str.")"
			);
		}
	}
}



/**
 * Copy a mail to new folders in the IMAP server
 * @param object $account the MailAccount object
 * @param object $imap the IMAP connection object
 * @param object $mail the MailContent object
 * @param array $folders_to_add the names of the folders to add the mail to
 */
function copy_mail_to_imap_folders($account, $imap, $mail, $folders_to_add) {
	
	// Get the source folder and uid of the mail
	$source_row = DB::executeOne("SELECT * FROM ".TABLE_PREFIX."mail_content_imap_folders
					WHERE object_id=".$mail->getId());
	
	// Add the email to the new folders
	if ($source_row) {
		$source_folder = utf8_decode($source_row['folder']);
		$source_uid = $source_row['uid'];
	} else {
		$source_folder = $mail->getImapFolderName();
		$source_uid = $mail->getUid();
	}
		
	if ($source_folder) {
		// copy to destiny folders
		$imap->selectMailbox($source_folder);
		
		foreach ($folders_to_add as $folder_name) {
			if (is_null($folder_name)) continue;

			$dest_folder = utf8_decode($folder_name);
			
			// check if this message already exists in destiny folder
			$imap->selectMailbox($dest_folder);
			$exist_uids = $imap->search('HEADER Message-ID '.$mail->getMessageId(), true);
			$exists_in_folder = is_array($exist_uids) && count($exist_uids) > 0;
			
			$imap->selectMailbox($source_folder);

			// if not exists -> copy it
			if (!$exists_in_folder) {
				$ret = $imap->cmdUidCopy($source_uid, $dest_folder);
				
				// get the new uid of the message in the folder
				$response_string = $ret['RESPONSE']['STR_CODE']; // [COPYUID 1500469762 18 14] Copy completed (0.003 + 0.000 + 0.002 secs).
				$matches = array();
				preg_match_all("/\[COPYUID (.*) (.*) (.*)\]/", $response_string, $matches);
				$new_uid = array_var($matches[3], 0);
				
				// update feng tables
				if ($ret && !Pear::isError($ret)) {
					fill_mail_content_imap_folder_table($imap, $account, $mail, $folder_name, $new_uid);
				}
			} else {
				// if already exists -> update feng info
				fill_mail_content_imap_folder_table($imap, $account, $mail, $folder_name, $exist_uids[0]);
			}
		}
	} else {
		// append to destiny folders
		foreach ($folders_to_add as $folder_name) {
			if (is_null($folder_name)) continue;

			$dest_folder = utf8_decode($folder_name);
			
			// check if this message already exists in destiny folder
			$imap->selectMailbox($dest_folder);
			$exist_uids = $imap->search('HEADER Message-ID '.$mail->getMessageId(), true);
			$exists_in_folder = is_array($exist_uids) && count($exist_uids) > 0;
			
			$imap->selectMailbox($source_folder);

			// if not exists -> append it
			if (!$exists_in_folder) {
				$eml = $mail->getContent();
				$ret = $imap->cmdAppend($dest_folder, $eml);
				
				// get the new uid of the message in the folder
				$response_string = $ret['RESPONSE']['STR_CODE']; // [APPENDUID 1500469762 18] Copy completed (0.003 + 0.000 + 0.002 secs).
				$matches = array();
				preg_match_all("/\[APPENDUID (.*) (.*)\]/", $response_string, $matches);
				$new_uid = array_var($matches[2], 0);
				
				// update feng tables
				if ($ret && !Pear::isError($ret)) {
					fill_mail_content_imap_folder_table($imap, $account, $mail, $folder_name, $new_uid);
				}
			} else {
				// if already exists -> update feng info
				fill_mail_content_imap_folder_table($imap, $account, $mail, $folder_name, $exist_uids[0]);
			}
		}
	}
	
	
}

/**
 * Move a mail to new folders in the IMAP server
 * (copy → then remove)
 *
 * @param object $account the MailAccount object
 * @param object $imap the IMAP connection object
 * @param object $mail the MailContent object
 * @param array $folders_to_add array of folder names
 */
function move_mail_to_imap_folders($account, $imap, $mail, $folders_to_add, $folders_to_remove) {

    Logger::log(
        "MOVE: Starting move_mail_to_imap_folders for mail ID ".$mail->getId(),
        Logger::DEBUG,
        null,
        'imap_move'
    );

    // 1) Copy to new folders
    Logger::log(
        "MOVE: Copying mail to folders: ".json_encode($folders_to_add),
        Logger::DEBUG,
        null,
        'imap_move'
    );

    copy_mail_to_imap_folders($account, $imap, $mail, $folders_to_add);

    // 2) Remove from old folders
    Logger::log(
        "MOVE: Removing mail from folders: ".json_encode($folders_to_remove),
        Logger::DEBUG,
        null,
        'imap_move'
    );

    remove_mail_from_imap_folders(
		$account,
		$imap,
		$mail,
		$folders_to_remove,
		array('allow_message_id_fallback' => true)
	);

    Logger::log(
        "MOVE: Finished move_mail_to_imap_folders for mail ID ".$mail->getId(),
        Logger::DEBUG,
        null,
        'imap_move'
    );
}




/**
 * Fill the mail_content_imap_folder table with the new uid and other info,
 * this is used when we move or copy a mail to a new folder
 * @param object $imap the IMAP connection object
 * @param object $account the MailAccount object
 * @param object $mail the MailContent object
 * @param string $folder_name the name of the folder
 * @param string $new_uid the new uid of the message in the folder
 */
function fill_mail_content_imap_folder_table($imap, $account, $mail, $folder_name, $new_uid) {
	if (!$new_uid) {
		// if we don't have the new uid, get it from the folder
		$imap->selectMailbox(utf8_decode($folder_name));
		$uids = $imap->search('HEADER Message-ID '.$mail->getMessageId(), true);
		$new_uid = array_var($uids, 0);
	}
	
	if ($new_uid) {
		// insert or update the row in the table
		$sql = "INSERT INTO ".TABLE_PREFIX."mail_content_imap_folders (account_id, message_id, folder, uid, object_id) VALUES
					(".$account->getId().",".DB::escape($mail->getMessageId()).",".DB::escape($folder_name).", '$new_uid', ".$mail->getId().")
				ON DUPLICATE KEY UPDATE uid='$new_uid'";
		DB::execute($sql);
		// remove the old rows with empty folder
		DB::execute("DELETE FROM ".TABLE_PREFIX."mail_content_imap_folders
				WHERE account_id=".$account->getId()." AND object_id=".$mail->getId()." AND folder=''");
	}
	
}


/**
 * Repair UTF-8 text that was incorrectly passed through utf8_encode().
 *
 * @param string $text
 * @return string
 */
function mail_repair_misencoded_utf8($text) {
	if (!is_string($text) || $text === '') {
		return $text;
	}

	// Typical artifact: smart quotes/apostrophes become sequences like â€™ (UTF-8: C3 A2 C2 80 C2 99)
	if (!preg_match('/\xC3\xA2\xC2\x80|\xC3\x83\xC2|[\xC3\xA2\xE2][\x80\x99]|[âÃ]/u', $text)) {
		return $text;
	}

	if (function_exists('mb_check_encoding') && !mb_check_encoding($text, 'UTF-8')) {
		return $text;
	}

	$repaired = @iconv('UTF-8', 'ISO-8859-1//IGNORE', $text);
	if ($repaired !== false && $repaired !== '' && (!function_exists('mb_check_encoding') || mb_check_encoding($repaired, 'UTF-8'))) {
		return $repaired;
	}

	return $text;
}

/**
 * Convert email text to UTF-8 without corrupting already valid UTF-8 content.
 *
 * @param string $text
 * @param string $source_encoding
 * @return string
 */
function mail_convert_to_utf8($text, $source_encoding = 'UTF-8') {
	if (!is_string($text) || $text === '') {
		return '';
	}

	$source_encoding = trim((string) $source_encoding);
	if ($source_encoding === '') {
		$source_encoding = 'UTF-8';
	}

	if (strcasecmp($source_encoding, 'UTF-8') === 0 && function_exists('mb_check_encoding') && mb_check_encoding($text, 'UTF-8')) {
		return mail_repair_misencoded_utf8($text);
	}

	$enc_conv = EncodingConverter::instance();
	$converted = $enc_conv->convert($source_encoding, 'UTF-8', $text);

	if (!$enc_conv->hasError() && is_string($converted) && $converted !== '') {
		if (!function_exists('mb_check_encoding') || mb_check_encoding($converted, 'UTF-8')) {
			return mail_repair_misencoded_utf8($converted);
		}
	}

	$detected = detect_encoding($text, array('UTF-8', 'ISO-8859-1', 'Windows-1252', 'CP1252'), true);
	if ($detected && strcasecmp($detected, $source_encoding) !== 0) {
		$enc_conv = EncodingConverter::instance();
		$converted = $enc_conv->convert($detected, 'UTF-8', $text);
		if (!$enc_conv->hasError() && is_string($converted) && $converted !== '') {
			if (!function_exists('mb_check_encoding') || mb_check_encoding($converted, 'UTF-8')) {
				return mail_repair_misencoded_utf8($converted);
			}
		}
	}

	if (function_exists('mb_check_encoding') && mb_check_encoding($text, 'UTF-8')) {
		return mail_repair_misencoded_utf8($text);
	}

	return mail_repair_misencoded_utf8(mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1'));
}

/**
 * Extract plain text from HTML string
 *
 * This function takes an HTML string, remove all the tags and return
 * the plain text.
 *
 * @param string $html HTML string to process
 * @return string Plain text extracted from HTML string
 */
function extract_plain_text_from_html(string $html): string {
	if ($html === '') {
		return '';
	}

	libxml_use_internal_errors(true);

	$dom = new DOMDocument();

	// Force UTF-8 and tolerate broken HTML
	$dom->loadHTML(
		'<?xml encoding="UTF-8">' . $html,
		LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT
	);

	libxml_clear_errors();

	// Remove script and style tags
	$xpath = new DOMXPath($dom);
	foreach ($xpath->query('//script|//style') as $node) {
		$node->parentNode->removeChild($node);
	}

	$text = $dom->textContent;

	// Normalize whitespace
	$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	$text = preg_replace('/\s+/u', ' ', $text);

	return trim($text);
}

/**
 * MIME part header names that may leak into stored body_plain.
 * Shared by detection and stripping so both stay in sync.
 */
function mail_leaked_mime_header_names_pattern() {
	return 'Content-Type|Content-Transfer-Encoding|MIME-Version|Content-Disposition|Content-ID|Content-Description';
}

/**
 * Detect plain bodies that start with leaked MIME part headers
 * (e.g. "Content-Type: text/plain; charset=\"utf-8\"...").
 */
function mail_plain_body_has_leading_mime_headers($text) {
	if (!is_string($text) || $text === '') {
		return false;
	}
	return (bool) preg_match(
		'/^\s*(?:' . mail_leaked_mime_header_names_pattern() . ')\s*:/i',
		$text
	);
}

/**
 * Remove one leading MIME header block terminated by a blank line (or end of headers).
 * Does not flatten the rest of the body.
 */
function mail_strip_leading_header_block_lines($text) {
	$headers = mail_leaked_mime_header_names_pattern();
	$normalized = str_replace(array("\r\n", "\r"), "\n", (string) $text);
	$trim_start = ltrim($normalized);
	$lines = explode("\n", $trim_start);
	$i = 0;
	$n = count($lines);

	while ($i < $n) {
		$line = $lines[$i];
		if ($line === '') {
			$i++;
			break;
		}
		if (preg_match('/^(?:' . $headers . ')\s*:/i', $line)) {
			$i++;
			while ($i < $n && preg_match('/^[ \t]/', $lines[$i]) && $lines[$i] !== '') {
				$i++;
			}
			continue;
		}
		break;
	}

	return implode("\n", array_slice($lines, $i));
}

/**
 * Strip leading MIME header block(s) from plain text.
 * Supports real newlines, nested header blocks, and headers collapsed onto the first line.
 * Never flattens the rest of the body; never returns empty when the input had content.
 */
function mail_strip_leading_mime_headers($text) {
	if (!mail_plain_body_has_leading_mime_headers($text)) {
		return $text;
	}

	$original = (string) $text;
	$normalized = str_replace(array("\r\n", "\r"), "\n", $original);
	$result = ltrim($normalized);

	// Peel successive newline-separated header blocks (nested multipart leaks)
	$guard = 0;
	while ($result !== '' && mail_plain_body_has_leading_mime_headers($result) && $guard++ < 10) {
		$prev = $result;
		$result = mail_strip_leading_header_block_lines($result);
		if ($result === $prev) {
			break;
		}
	}

	if ($result !== '' && !mail_plain_body_has_leading_mime_headers($result)) {
		return $result;
	}

	// Collapsed headers on the first remaining line only — keep the rest of the body intact
	$collapsed_source = ($result !== '') ? $result : ltrim($normalized);
	$nl_pos = strpos($collapsed_source, "\n");
	if ($nl_pos === false) {
		$first = $collapsed_source;
		$rest_body = '';
	} else {
		$first = substr($collapsed_source, 0, $nl_pos);
		$rest_body = substr($collapsed_source, $nl_pos + 1);
	}

	$headers = mail_leaked_mime_header_names_pattern();
	$first_collapsed = trim(preg_replace('/\s+/u', ' ', $first));
	$mime_value = '(?:text|multipart|application|message|image)\/[a-z0-9+\-\.]+'
		. '(?:\s*;\s*[a-z0-9\-]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s;]+))*';
	$cte_value = '7bit|8bit|binary|quoted-printable|base64';
	// Values for MIME-Version / Content-Disposition / Content-ID when they are the last header
	$other_values = '1\.0|inline|attachment[^\s]*|<[^>\s]+>';
	$value_pattern = '(?:' . $mime_value . '|' . $cte_value . '|' . $other_values . ')';
	$strip_failed = false;

	while (preg_match('/^(?:' . $headers . ')\s*:/i', $first_collapsed)) {
		if (!preg_match('/^(?:' . $headers . ')\s*:\s*/i', $first_collapsed, $m)) {
			$strip_failed = true;
			break;
		}
		$after_colon = substr($first_collapsed, strlen($m[0]));
		if (preg_match('/\s+((?:' . $headers . ')\s*:)/i', $after_colon, $m2, PREG_OFFSET_CAPTURE)) {
			$first_collapsed = ltrim(substr($after_colon, $m2[1][1]));
			continue;
		}
		// Header value alone on this line — body is in $rest_body (or absent)
		if (preg_match('/^(?:' . $value_pattern . ')$/i', trim($after_colon))) {
			$first_collapsed = '';
			break;
		}
		if (preg_match('/^(?:' . $value_pattern . ')\s+(.*)$/i', $after_colon, $m3)) {
			$first_collapsed = $m3[1];
			break;
		}
		$strip_failed = true;
		break;
	}

	if ($strip_failed) {
		return $original;
	}

	$combined = $first_collapsed;
	if ($rest_body !== '') {
		$combined = ($combined === '') ? $rest_body : ($combined . "\n" . $rest_body);
	}

	// Never degrade a preview/body with content to an empty string
	if (trim($combined) === '' && trim($original) !== '') {
		return $original;
	}

	return $combined;
}

/**
 * Return a usable plain body: strip leaked MIME headers, or rebuild from HTML when needed.
 * Prefer strip over HTML extraction so formatting in the plain part is preserved when possible.
 * Never return empty when the original plain had content and no HTML fallback succeeded.
 */
function mail_normalize_plain_body($plain, $html = '') {
	$plain = is_string($plain) ? $plain : '';
	$html = is_string($html) ? $html : '';

	if (!mail_plain_body_has_leading_mime_headers($plain)) {
		return $plain;
	}

	$stripped = mail_strip_leading_mime_headers($plain);
	if (trim($stripped) !== '' && !mail_plain_body_has_leading_mime_headers($stripped)) {
		return $stripped;
	}

	if (trim($html) !== '') {
		try {
			return extract_plain_text_from_html($html);
		} catch (Exception $e) {
			return html_to_text(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
		}
	}

	// No usable strip and no HTML: keep the original rather than blanking the preview
	return $plain;
}
