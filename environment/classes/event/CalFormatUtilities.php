<?php
require_once ROOT.'/environment/classes/event/parse_ics.php';

class CalFormatUtilities {

	const ICAL_DESC_PROTECTED_SECTION_DELIMITER = '-::~:~::~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~::~:~::-';
	
	static function decode_ical_file($filename, $create_new_contacts = false) {
		$parsed_data = parse_ical($filename);
		
		if (isset($parsed_data[0]['tzoffsetfrom'])){
			$tz_diff = ($parsed_data[0]['tzoffsetfrom'] / 100);
		} else {
			$tz_diff = logged_user()->getUserTimezoneHoursOffset();
		}
		$calendar_data = array_shift($parsed_data);
                
		$events_data = CalFormatUtilities::build_events_data($parsed_data, $tz_diff, $calendar_data, $create_new_contacts);
		return $events_data;
	}
	
	static function build_events_data($ical_events_data, $tz_diff, $calendar_data = array(), $create_new_contacts = false) {
		$result = array();
		
		foreach($ical_events_data as $ical_ev) {
			$base_data = array();

			$base_data['uid'] = array_var($ical_ev, 'uid', '');

			$base_data['name'] = substr_utf(array_var($ical_ev, 'summary', lang("untitle event")), 0, 100);
			$base_data['name'] = html_entity_decode($base_data['name']);
			$base_data['name'] = str_replace('<br />', "\n", $base_data['name']);

			$base_data['description'] = array_var($ical_ev, 'description', '');
			$base_data['description'] = str_replace(self::ICAL_DESC_PROTECTED_SECTION_DELIMITER, '', $base_data['description']);
			$base_data['description'] = html_entity_decode($base_data['description']);
			$base_data['description'] = preg_replace('/<((https?:\/\/)[^>]+)>/', '<br/>$1', $base_data['description']); // to remove < and > from meeting links in the event description
			$base_data['description'] = strip_tags(str_replace(array('<br />', '<br/>', '<br>'), "\n", $base_data['description']));

			// Attachments from .ics (ATTACH). Persist by appending to description (Feng core has no dedicated iCal attachment storage)
			$attachments = array_var($ical_ev, 'attach', array());
			if (is_array($attachments) && count($attachments) > 0) {
				$lines = [];
				foreach ($attachments as $att) {
					$val = is_array($att) ? array_var($att, 'value', '') : (string) $att;
					$val = trim((string) $val);
					if ($val !== '') $lines[] = $val;
				}
				$lines = array_values(array_unique($lines));
				if (count($lines) > 0) {
					$base_data['description'] = rtrim($base_data['description']) . "\n\n" . lang('attachments') . ":\n- " . implode("\n- ", $lines);
				}
			}

			$base_data['type_id'] = array_var($ical_ev, 'all_day', 0) == 0 ? 1 : 2;
			$base_data['start'] = date('Y-m-d H:i:s', array_var($ical_ev, 'start_unix') - $tz_diff * 3600);
			$base_data['duration'] = date('Y-m-d H:i:s', array_var($ical_ev, 'end_unix') - $tz_diff * 3600);

			$base_data['ical_dtstamp'] = date('Y-m-d H:i:s', array_var($ical_ev, 'stamp_unix'));

			$base_data['repeat_num'] = 0;
			$base_data['repeat_h'] = 0;
			$base_data['repeat_d'] = 0;
			$base_data['repeat_m'] = 0;
			$base_data['repeat_y'] = 0;
			$base_data['repeat_forever'] = 0;
			$base_data['repeat_end'] =  0;
			
			$rrule = array_var($ical_ev, 'rrule', null);
			if ($rrule != null) {
				$base_data['repeat_end'] = isset($rrule['until_unix']) ? date('Y-m-d', array_var($rrule, 'until_unix')) : 0;
				$base_data['repeat_num'] = array_var($rrule, 'count', 0);
				$freq = array_var($rrule, 'freq', null);
				$jump = array_var($rrule, 'interval', 1);
				if ($freq != null) {
					switch ($freq) {
						case 'DAILY': $base_data['repeat_d'] = $jump; break;
						case 'WEEKLY': $base_data['repeat_d'] = 7 * $jump; break;
						case 'MONTHLY': $base_data['repeat_m'] = $jump; break;
						case 'YEARLY': $base_data['repeat_y'] = $jump; break;
					}					
				}
				if ($base_data['repeat_end'] == 0 && $base_data['repeat_num'] == 0) $base_data['repeat_forever'] = 1;
			}

			// Organizer
			$organizer = array_var($ical_ev, 'organizer', array());
			if (is_array($organizer) && isset($organizer['email'])) {
				$contact = Contacts::instance()->getByEmail($organizer['email']);
				if ($contact instanceof Contact) {
					$base_data['organizer_id'] = $contact->getId();
				} else if ($create_new_contacts) {
					$contact = self::create_contact_from_name_and_email($organizer['name'], $organizer['email']);
					if ($contact instanceof Contact) {
						$base_data['organizer_id'] = $contact->getId();
					}
				} else {
					// Always create organizer contact so ProjectEvent::save() doesn't default organizer_id to the viewer.
					$contact = self::create_contact_from_name_and_email(array_var($organizer, 'name', $organizer['email']), $organizer['email']);
					if ($contact instanceof Contact) {
						$base_data['organizer_id'] = $contact->getId();
					}
				}
			}

			// Invitations
			$base_data['invited_contact_ids'] = array();
			$base_data['invitation_state'] = array();
			$attendees = array_var($ical_ev, 'attendee', array());
			if (!is_array($attendees)) $attendees = array();
			foreach ($attendees as $attendee) {
				$name = array_var($attendee, 'name', '');
				$email = array_var($attendee, 'mailto', '');
				$contact = null;
				if (is_valid_email($email)) {
					$contact = Contacts::instance()->getByEmail($email);
				} else {
					if (is_valid_email($name)) {
						$contact = Contacts::instance()->getByEmail($name);
					} else {
						$contact = Contacts::instance()->findOne(array('conditions' => array('name=?', $name)));
					}
				}
				if ($create_new_contacts && !$contact instanceof Contact) {
					$contact = self::create_contact_from_name_and_email($name, $email);
				}
				if ($contact instanceof Contact) {
					$base_data['invited_contact_ids'][] = $contact->getId();
					$base_data['invitation_state'][$contact->getId()] = 0;
					switch (array_var($attendee, 'status', '')) {
						case 'ACCEPTED': $base_data['invitation_state'][$contact->getId()] = EventInvitations::EVENT_INVITATION_ACCEPTED; break;
						case 'DECLINED': $base_data['invitation_state'][$contact->getId()] = EventInvitations::EVENT_INVITATION_DECLINED; break;
						case 'TENTATIVE': $base_data['invitation_state'][$contact->getId()] = EventInvitations::EVENT_INVITATION_TENTATIVE; break;
					}
				}
			}

			$status = array_var($ical_ev, 'status', 'CONFIRMED');
			switch ($status) {
				case 'CONFIRMED': $base_data['confirmAttendance'] = EventInvitations::EVENT_INVITATION_ACCEPTED; break;
				case 'CANCELLED': $base_data['confirmAttendance'] = EventInvitations::EVENT_INVITATION_DECLINED; break;
				case 'TENTATIVE': $base_data['confirmAttendance'] = EventInvitations::EVENT_INVITATION_TENTATIVE; break;
			}

			$ical_ev['calendar_data'] = $calendar_data;

			$base_data['parsed_ical'] = $ical_ev;

			// Microsoft/Outlook weekly BYDAY lists (e.g., WE,TH,FR) can't be represented by Feng's single-weekday repeating model.
			// Expand into one weekly event per weekday, keeping the same series metadata but using UID suffixes.
			$expanded = false;
			if ($rrule != null && array_var($rrule, 'freq') === 'WEEKLY') {
				$byday = array_var($rrule, 'byday', '');
				if (is_string($byday) && strpos($byday, ',') !== false) {
					$days = array_filter(array_map('trim', explode(',', $byday)));
					$map = ['SU'=>0,'MO'=>1,'TU'=>2,'WE'=>3,'TH'=>4,'FR'=>5,'SA'=>6];
					$start_ts = array_var($ical_ev, 'start_unix');
					$end_ts = array_var($ical_ev, 'end_unix');
					$start_dow = (int) date('w', $start_ts);
					foreach ($days as $dcode) {
						$dcode = strtoupper($dcode);
						if (!isset($map[$dcode])) continue;
						$target = $map[$dcode];
						$delta_days = ($target - $start_dow + 7) % 7;
						$clone = $base_data;
						$clone['uid'] = $base_data['uid'] . '-BYDAY-' . $dcode;
						$clone['start'] = date('Y-m-d H:i:s', ($start_ts + $delta_days * 86400) - $tz_diff * 3600);
						$clone['duration'] = date('Y-m-d H:i:s', ($end_ts + $delta_days * 86400) - $tz_diff * 3600);
						$result[] = $clone;
						$expanded = true;
					}
				}
			}

			if (!$expanded) {
				$result[] = $base_data;
			}
		}
		
		return $result;
	}

	/**
	 * Create a new contact with the given name and email
	 *
	 * @param string $name
	 * @param string $email
	 * @return Contact
	 */
	static function create_contact_from_name_and_email($name, $email) {
		// Create a new Contact object
		$contact = new Contact();
		
		// Split the name into the first and last name
		$exp_name = explode(' ', $name);
		$first_name = array_shift($exp_name);
		$last_name = count($exp_name) > 0 ? trim(implode(' ', $exp_name)) : '';
		
		// Set the first and last name
		$contact->setFirstName($first_name);
		$contact->setSurname($last_name);
		
		// Save the contact
		$contact->save();
		
		// Add the email address if it is valid
		if (is_valid_email($email)) $contact->addEmail($email, 'personal');
		
		// Add the contact to the searchable objects and sharing table
		$contact->addToSearchableObjects(true);
		$contact->addToSharingTable();
		
		return $contact;
	}

	static function strip_tags_content($text, $tags = '', $invert = FALSE) {
		preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
		$tags = array_unique($tags[1]);
	  
		if(is_array($tags) AND count($tags) > 0) {
		  	if($invert == FALSE) {	  
				return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);	  
		  	}
	  
		  	else {	  
				return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
		  	}	  
		}
	  
		elseif($invert == FALSE) {
			return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
		}
		return $text;
	}
	  
	  
	
	static function generateICalInfo($events, $calendar_name, $user = null, $tasks = null, $notification = null) {
		if ($user == null) $user = logged_user();
		$ical_info = '';
		$ical_info .= "BEGIN:VCALENDAR\n";
		$ical_info .= "VERSION:2.0\n";
		$ical_info .= "PRODID:".product_name()."\n";
		$method = 'REQUEST';
		if (str_starts_with($notification, 'invitation-')) {
			$method = "REPLY";
		}
		$ical_info .= "METHOD:$method\n";
		$ical_info .= "CALSCALE:GREGORIAN\n";
		$ical_info .= "X-WR-CALNAME:$calendar_name\n";
		
		$tz_offset = $user->getUserTimezoneValue();
		$tz_offset_hours = $tz_offset / 3600;
		
		// timezone info
		$tz = ($tz_offset_hours < 0 ? "-":"+").str_pad(abs($tz_offset_hours)*100, 4, '0', STR_PAD_LEFT);
		$tz_name = "GMT".($tz_offset_hours >= 0 ? "+" : "-").abs($tz_offset_hours);
		$tz_id = Timezones::getTimezoneName($user->getUserTimezoneId());
		$ical_info .= "BEGIN:VTIMEZONE\n";
		$ical_info .= "TZID:$tz_id\n";
		$ical_info .= "BEGIN:STANDARD\n";
		$ical_info .= "DTSTART:19700101T000000\n";
		$ical_info .= "TZOFFSETFROM:$tz\n";
		$ical_info .= "TZOFFSETTO:$tz\n";
		$ical_info .= "TZNAME:$tz_name\n";
		$ical_info .= "END:STANDARD\n";
		$ical_info .= "END:VTIMEZONE\n";
		
		foreach ($events as $event) {
			/** @var $event ProjectEvent */
			$ical_info .= "BEGIN:VEVENT\n";
			
			$event_start = new DateTimeValue($event->getStart()->getTimestamp() + $tz_offset);
			$event_duration = new DateTimeValue($event->getDuration()->getTimestamp() + $tz_offset);
			
			$startNext = new DateTimeValue($event_start->getTimestamp());
			$startNext->add('d', 1);
			if ($event->getTypeId() == 2) $ical_info .= "DTSTART;VALUE=DATE:" . $event_start->format('Ymd') ."\n";
			else $ical_info .= "DTSTART;TZID=$tz_id:" . $event_start->format('Ymd') ."T". $event_start->format('His') ."\n";
			if ($event->getTypeId() == 2) $ical_info .= "DTEND;VALUE=DATE:" . $startNext->format('Ymd') ."\n";
			else $ical_info .= "DTEND;TZID=$tz_id:" . $event_duration->format('Ymd') ."T". $event_duration->format('His') ."\n";

			$uid = $event->generateUid();
			
			$subject = $event->getSubject();
			$description = str_replace(array(chr(13).chr(10), chr(13), chr(10)),'\n', $event->getDescription());
			$subject = str_replace(array(',', ';'), array('\,', '\;'), $subject);
			$description = str_replace(array(',', ';'), array('\,', '\;'), $description);

			$subject = trim(chunk_split($subject, 76, "\n "));
			$description = trim(chunk_split($description, 76, "\n "));
			
			$ical_info .= "DESCRIPTION:$description\n";
            $ical_info .= "SUMMARY:$subject\n";
		    $ical_info .= "UID:$uid\n";
		    $is_reply = str_starts_with((string)$notification, 'invitation-');
		    // SEQUENCE: use a non-zero stamp for replies so clients accept the update
		    $sequence = $is_reply ? 1 : 0;
		    $ical_info .= "SEQUENCE:$sequence\n";
		    $dtstamp = DateTimeValueLib::now();
		    $ical_info .= "DTSTAMP:".$dtstamp->format('Ymd').'T'.$dtstamp->format('His')."Z\n";
			
			// organizer
			$organizer = $event->getOrganizer();
			if ($organizer instanceof Contact) {
				$org_name = addslashes(trim($organizer->getName()));
				$org_email = trim($organizer->getEmailAddress());
				$org_str = "ORGANIZER;CN=$org_name:mailto:$org_email";
				$ical_info .= trim(chunk_split($org_str, 76, "\n ")) . "\n";
			}

			// attendees: for REPLY include only the responding user (RFC 5546)
		    $invitations = $event->getInvitations();
			$responder_id = logged_user() instanceof Contact ? logged_user()->getId() : 0;
			foreach ($invitations as $inv) {
				/** @var $inv EventInvitation */
				if ($is_reply && $responder_id > 0 && $inv->getContactId() != $responder_id) {
					continue;
				}
				$contact = $inv->getContact();
				if ($contact instanceof Contact) {
					//$att_name = addslashes(trim($contact->getName()));
					$att_email = trim($contact->getEmailAddress());

					if ($inv->getinvitationState() == EventInvitations::EVENT_INVITATION_ACCEPTED) $att_status = 'ACCEPTED';
					else if ($inv->getinvitationState() == EventInvitations::EVENT_INVITATION_DECLINED) $att_status = 'DECLINED';
					else if ($inv->getinvitationState() == EventInvitations::EVENT_INVITATION_TENTATIVE) $att_status = 'TENTATIVE';
					else $att_status = 'NEEDS-ACTION';

					$attendee_str = "ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=$att_status;RSVP=TRUE;CN=$att_email;X-NUM-GUESTS=0:mailto:$att_email";

					$ical_info .= trim(chunk_split($attendee_str, 76, "\n ")) . "\n";
				}
			}

			// event status
			if ($event->isTrashed() || $notification == 'deleted') {
				$ical_info .= "STATUS:CANCELLED\n";
			} else {
				$ical_info .= "STATUS:CONFIRMED\n";
			}

			// event repetition rules
			$rrule = '';
			if ($event->getRepeatD() > 0 || $event->getRepeatM() > 0 || $event->getRepeatY() > 0 || $event->getRepeatForever() > 0) {
				$rrule_ok = true;
				if ($event->getRepeatD() > 0) {
					if ($event->getRepeatD() % 7 == 0) {
						$freq = "FREQ=WEEKLY;";
						$interval = "INTERVAL=".($event->getRepeatD() / 7);
					} else {
						$freq = "FREQ=DAILY;";
						$interval = "INTERVAL=".$event->getRepeatD();
					}
				} else if ($event->getRepeatM() > 0) {
					$freq = "FREQ=MONTHLY;";
					$interval = "INTERVAL=".$event->getRepeatM();
				} else if ($event->getRepeatY() > 0) {
					$freq = "FREQ=YEARLY;";
					$interval = "INTERVAL=".$event->getRepeatY();
				} else {
					$rrule_ok = false;
				}
				$until = '';
				$count = '';
				if (!$event->getRepeatForever() && $event->getRepeatNum() > 0) $count = ";COUNT=".$event->getRepeatNum();
				else if (!$event->getRepeatForever() && $event->getRepeatEnd()) $until = ";UNTIL=".$event->getRepeatEnd()->format('Ymd').'T'.$event->getRepeatEnd()->format('His');
				
				if ($rrule_ok) $rrule = "RRULE:$freq$interval$count$until\n";
			}
			if ($event->getRepeatH() > 0) {
				"RRULE:FREQ=MONTHLY;INTERVAL=1;BYDAY=1TU";
				$interval = "INTERVAL=".$event->getRepeatMjump();
				switch ($event->getRepeatDow()) {
					case 1: $day = "SU"; break;
					case 2: $day = "MO"; break;
					case 3: $day = "TU"; break;
					case 4: $day = "WE"; break;
					case 5: $day = "TH"; break;
					case 6: $day = "FR"; break;
					case 7: $day = "SA"; break;
					default: $day = "MO"; break;
				}
				$byday = "BYDAY=" . $event->getRepeatWnum() . $day;
				
				$rrule = "RRULE:FREQ=MONTHLY;$interval;$byday\n";
			}
		    $ical_info .= $rrule;
		    
		    $ical_info .= "END:VEVENT\n";
		}

		/**
		 * This loop, generates the ICS calendar file with tasks attached
		 * as events. This allows to generate a new ics file that contains both
		 * Events and Tasks presents in the calendar.
		 */
		if($tasks != null)
		{
			foreach ($tasks as $task) {
				$ical_info .= "BEGIN:VEVENT\n";
	
				$description = $task->getName();
				$uid = $task->getObjectId();
				$timestamp = $task->getStartDate()->format('Ymd') . "T" . $task->getStartDate()->format('His');
	
				$ical_info .= "DTSTART;VALUE=DATE:" . $task->getStartDate()->format('Ymd') ."\n";
				$ical_info .= "DTEND;VALUE=DATE:" . $task->getDueDate()->format('Ymd') ."\n";
	
				$ical_info .= "DESCRIPTION:$description\n";
				$ical_info .= "SUMMARY:$description\n";
				$ical_info .= "UID:".$uid.'@fengoffice.com/html'."\n";
				$ical_info .= "SEQUENCE:0\n";
				$ical_info .= "DTSTAMP:$timestamp\n";
				$ical_info .= "STATUS:CONFIRMED\n";
				$recurrent = "";
				if($task->getRepeatForever() > 0)
				{
					if ($task->getRepeatD() > 0) {
						if ($task->getRepeatD() % 7 == 0) {
							$recurrent = "RRULE:FREQ=WEEKLY;UNTIL=30240101T000000Z\n";
						} else {
							$recurrent = "RRULE:FREQ=DAILY;UNTIL=30240101T000000Z\n";
						}
					} else if ($task->getRepeatM() > 0) {
						$recurrent = "RRULE:FREQ=MONTHLY;UNTIL=30240101T000000Z\n";
					} else if ($task->getRepeatY() > 0) {
						$recurrent = "RRULE:FREQ=YEARLY;UNTIL=30240101T000000Z\n";
					}
					$ical_info .= $recurrent;
				}
	
				$ical_info .= "END:VEVENT\n";
			}
		}


		
		$ical_info .= "END:VCALENDAR\n";

		// Ensure that the encoding is UTF-8
		if (function_exists('mb_detect_order') && function_exists('mb_detect_encoding')) {
			mb_detect_order('auto');
			if (($file_encoding = mb_detect_encoding($ical_info, null, true)) === false) {
				$file_encoding = "auto";
			}
			if (in_array(strtoupper($file_encoding), array('UTF-8','UTF8')) === false) {
				$ical_info = mb_convert_encoding($ical_info, 'UTF-8', $file_encoding);
			}
		}
		
		return $ical_info;
	}

}

