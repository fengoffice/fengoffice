<?php

function check_mail() {
	if (Plugins::instance()->isActivePlugin('mail')) {
		_log("Checking email...");
		MailUtilities::getmails(null, $err, $succ, $errAcc, $received, 100);
		_log("$received emails fetched.");
		$processed_calendar_events = MailUtilities::processPendingCalendarInvitations();
		_log("$processed_calendar_events calendar invitation events processed.");
	}
}

function send_outbox_mails() {
	if (Plugins::instance()->isActivePlugin('mail')) {
		_log("Sending outbox emails...");
		$from_time = DateTimeValueLib::now();
		$from_time = $from_time->add('h', -24);
		MailUtilities::sendOutboxMailsAllAccounts($from_time);
		_log("End Sending outbox emails.");
	}
}

function purge_trash() {
	_log("Purging trash...");
	$count = Trash::purge_trash();
	_log("$count objects deleted.");
}

function check_upgrade() {
	_log("Checking for upgrades...");
	$version_feed = VersionChecker::check(true);
	if (!($version_feed instanceof VersionsFeed)) {
		_log("Error checking for upgrades.");
	} else {
		if ($version_feed->hasNewVersions(product_version())) {
			_log("Found new versions.");
		} else {
			_log("No new versions.");
		}
	}
}

function send_reminders() {
	_log("Sending reminders...");
	Env::useHelper('permissions');
	$sent = 0;
	$skipped = 0;
	$debug_reminders = defined('DEBUG_NOTIFICATIONS') && DEBUG_NOTIFICATIONS;
	$ors = ObjectReminders::getDueReminders();
	_log("Due reminders selected: " . count($ors));
	foreach ($ors as $or) {
		$object = $or->getObject();
	    //Check disabled object types notificactions.
	    if(!$object instanceof ContentDataObject || ($object instanceof ContentDataObject && in_array($object->getObjectTypeId(),config_option("disable_notifications_for_object_type")))){
	        if ($debug_reminders) {
	        	_log(sprintf(
	        		"[reminder] id=%s object_id=%s skipped=disabled_or_missing",
	        		$or->getId(),
	        		$object instanceof ContentDataObject ? $object->getId() : 0
	        	));
	        }
	        $skipped++;
	        $or->delete();
	        continue;
	    }
		$function = $or->getType();
		try {
			$ret = 0;
			Hook::fire($function, $or, $ret);
			$sent += $ret;
			if ($debug_reminders) {
				_log(sprintf(
					"[reminder] id=%s object_id=%s object_type=%s context=%s type=%s result=%s",
					$or->getId(),
					$object->getId(),
					$object->getObjectTypeName(),
					$or->getContext(),
					$function,
					$ret > 0 ? 'sent' : 'no_send'
				));
			}
		} catch (Exception $ex) {
			_log("Error sending reminder id=".$or->getId()." object_id=".$object->getId().": " . $ex->getMessage());
		}
	}
	_log("$sent reminders sent" . ($skipped ? ", $skipped skipped" : "") . ".");
}

function send_notifications_through_cron() {
	_log("Sending notifications...");
	$count = Notifier::sendQueuedEmails();
	_log("$count notifications sent.");
}

function delete_mails_from_server() {
	if (Plugins::instance()->isActivePlugin('mail')) {
		try {
			_log("Checking mail accounts to delete mails from server...");
			$count = MailUtilities::deleteMailsFromServerAllAccounts();
			_log("Deleted $count mails from server...");
		} catch (Exception $e) {
			_log("Error deleting mails from server: " . $e->getMessage());
		}
	}
}

function delete_spam_emails() {
	if (Plugins::instance()->isActivePlugin('mail')) {
		try {
			_log("Checking mail accounts to delete spam emails from server...");
			$count = MailUtilities::deleteSpamMailsFromServerAllAccounts();
			_log("Deleted $count spam mails from server...");
		} catch (Exception $e) {
			_log("Error deleting spam mails from server: " . $e->getMessage());
		}
	}
}

function clear_tmp_folder($dir = null) {
	try {
		if (!$dir) $dir = ROOT . "/tmp";
		$handle = opendir($dir);
		$left = 0;
		$deleted = 0;
		while (false !== ($f = readdir($handle))) {
			if ($f != "." && $f != ".." && $f != ".htaccess") {
				if ($f == "CVS") {
					$left++;
					continue;
				}
				$path = "$dir/$f";
				if (is_file($path)) {
					$mtime = @filemtime($path);
					if ($mtime && (time() - $mtime > 60*60*24*2)) {
						// if temp file older than 2 days
						@unlink($path);
						if (is_file($path)) {
							$left++;
						} else {
							$deleted++;
						}
					} else {
						$left++;
					}
				} else if (is_dir($path)) {
					$deleted += clear_tmp_folder($path);
					if (is_dir($path)) $left++;
				}
			}
		}
		closedir($handle);
		if ($dir == ROOT . "/tmp") _log("$deleted tmp files deleted.");
		else if ($left == 0) @rmdir($dir);

		return $deleted;
	} catch (Exception $e) {
		_log("Error clearing tmp folder: " . $e->getMessage());
	}
}


function send_password_expiration_reminders(){
	$password_expiration_notification = config_option('password_expiration_notification', 0);
	if($password_expiration_notification > 0){
		_log("Sending password expiration reminders...");
		$count = ContactPasswords::sendPasswordExpirationReminders();
		_log("$count password expiration reminders sent.");
	}
}

function _log($message) {
	echo date("Y-m-d H:i:s") . " - $message\n";
}

function import_google_calendar() {
	_log("import with google calendar...");
	$externalCalendarController = new ExternalCalendarController();
	$externalCalendarController->import_google_calendar();       
    _log("end import with google calendar...");
}

function export_google_calendar() {
	_log("export with google calendar...");
	$externalCalendarController = new ExternalCalendarController();
    $externalCalendarController->export_google_calendar();
    _log("end export with google calendar...");
}


function sharing_table_partial_rebuild() {
	$start_date = config_option('last_sharing_table_rebuild');
	
	// Ensure that start_date is not before 3 days ago
	$three_days_ago = DateTimeValueLib::now();
	$three_days_ago->add('d', -3);
	$start_date_dt = null;
	try {
		$start_date_dt = DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, $start_date);
		if ($start_date_dt instanceof DateTimeValue && $start_date_dt->getTimestamp() < $three_days_ago->getTimestamp()) {
			$start_date = $three_days_ago->toMySQL();
		}
	} catch (Exception $e) {
		$start_date = $three_days_ago->toMySQL();
	}
	// End setting start date

	_log("Rebuilding sharing table since $start_date ...");

	$obj_count = SharingTables::instance()->rebuild($start_date);
	
	_log("Finished rebuilding sharing table - $obj_count objects.");
}

function check_sharing_table_flags() {
	_log("Checking for sharing table pending updates...");
	
	$date = DateTimeValueLib::now();
	$date->add('m', -10);
	$flags = SharingTableFlags::instance()->getFlags($date);
	
	if (is_array($flags) && count($flags) > 0) {
		_log("  " . count($flags) . " permission groups needs to be recalculated...");
		foreach ($flags as $flag) {
			$ok = SharingTableFlags::instance()->healPermissionGroup($flag);
			$info = $flag->getObjectId() > 0 ? "object ".$flag->getObjectId() : "permission_group_id ".$flag->getPermissionGroupId().($flag->getMemberId()>0?" and member_id=".$flag->getMemberId():"");
			if ($ok) {
				_log("    Sharing table updated successfully for $info");
			} else {
				_log("    Failed to update sharing table for $info");
			}
		}
		_log("  Sharing table update finished.");
	} else {
		_log("No permission groups need to be updated.");
	}

	// Requests that die before their shutdown handler runs leave background job spool files
	// behind. Sweep them here rather than adding another cron event.
	Env::useHelper('background_jobs');
	$respawned = respawn_orphan_background_jobs();
	if ($respawned > 0) {
		_log("  Respawned $respawned orphaned background job batch(es).");
	}
}

function rebuild_contact_member_cache() {
	Env::useHelper('permissions');
	_log("Recalculating contact member cache...");
	
	$resource_cond = "";
	if (Plugins::instance()->isActivePlugin('advanced_services')) {
		$resource_cond = " AND is_resource=0 ";
	}
	$users = Contacts::getAllUsers($resource_cond);
	foreach ($users as $user) {
		ContactMemberCaches::updateContactMemberCacheAllMembers($user);
	}
	
	_log("Member cache updated for ".count($users)." users.");
}

function clean_object_selector_temp_selection() {
	_log("Cleaning object selector temp selections...");
	
	Env::useHelper('object_selector');
	clean_old_object_selector_temp_values();
	
	_log("Object selector temp values cleaned.");
}

$files = array();
Hook::fire('additional_cron_function_files', null, $files);
foreach ($files as $file) {
	include_once $file;
}

