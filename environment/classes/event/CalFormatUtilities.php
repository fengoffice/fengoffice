<?php
require_once ROOT.'/environment/classes/event/parse_ics.php';

class CalFormatUtilities {
	
	function decode_ical_file($filename) {
		$parsed_data = parse_ical($filename);
		
		if (isset($parsed_data[0]['tzoffsetfrom'])){
			$tz_diff = ($parsed_data[0]['tzoffsetfrom'] / 100);
                }else
			$tz_diff = logged_user()->getTimezone();
		unset($parsed_data[0]);
                
		$events_data = CalFormatUtilities::build_events_data($parsed_data, $tz_diff);
		return $events_data;
	}
	
	function build_events_data($ical_events_data, $tz_diff) {
		$result = array();
		
		foreach($ical_events_data as $ical_ev) {
			$data = array();
			$data['name'] = substr_utf(array_var($ical_ev, 'summary', lang("untitle event")), 0, 100);
			$data['description'] = array_var($ical_ev, 'description', '');
			$data['name'] = html_entity_decode($data['name']);
			$data['name'] = str_replace('<br />', "\n", $data['name']);
			$data['description'] = html_entity_decode($data['description']);
			$data['description'] = str_replace('<br />', "\n", $data['description']);
			$data['type_id'] = array_var($ical_ev, 'all_day', 0) == 0 ? 1 : 2;
			
			$data['start'] = date('Y-m-d H:i:s', array_var($ical_ev, 'start_unix') - $tz_diff * 3600);
			$data['duration'] = date('Y-m-d H:i:s', array_var($ical_ev, 'end_unix') - $tz_diff * 3600);

                        $data['repeat_num'] = 0;
			$data['repeat_h'] = 0;
			$data['repeat_d'] = 0;
			$data['repeat_m'] = 0;
			$data['repeat_y'] = 0;
			$data['repeat_forever'] = 0;
			$data['repeat_end'] =  0;
			
			$rrule = array_var($ical_ev, 'rrule', null);
			if ($rrule != null) {
				$data['repeat_end'] = isset($rrule['until_unix']) ? date('Y-m-d', array_var($rrule, 'until_unix')) : 0;
				$data['repeat_num'] = array_var($rrule, 'count', 0);
				$freq = array_var($rrule, 'freq', null);
				$jump = array_var($rrule, 'interval', 1);
				if ($freq != null) {
					switch ($freq) {
						case 'DAILY': $data['repeat_d'] = $jump; break;
						case 'WEEKLY': $data['repeat_d'] = 7 * $jump; break;
						case 'MONTHLY': $data['repeat_m'] = $jump; break;
						case 'YEARLY': $data['repeat_y'] = $jump; break;
					}					
				}
				if ($data['repeat_end'] == 0 && $data['repeat_num'] == 0) $data['repeat_forever'] = 1;
			}
			$data['users_to_invite'] = array();
			$data['users_to_invite'][logged_user()->getId()] = 1; 

			$status = array_var($ical_ev, 'status', 'CONFIRMED');
			switch ($status) {
				case 'CONFIRMED': $data['confirmAttendance'] = 1; break;
				case 'CANCELLED': $data['confirmAttendance'] = 2; break;
				case 'TENTATIVE': $data['confirmAttendance'] = 3; break;
			}
			
			$result[] = $data;
		}
		
		return $result;
	}
	
	function generateICalInfo($events, $calendar_name, $user = null) {
		if ($user == null) $user = logged_user();
		$ical_info = '';
		$ical_info .= "BEGIN:VCALENDAR\n";
		$ical_info .= "VERSION:2.0\n";
		$ical_info .= "PRODID:PHP\n";
		$ical_info .= "METHOD:REQUEST\n";
		$ical_info .= "X-WR-CALNAME:$calendar_name\n";
		
		// timezone info
		$tz = ($user->getTimezone() < 0 ? "-":"+").str_pad(abs($user->getTimezone())*100, 4, '0', STR_PAD_LEFT);
		$tz_desc = $user->getTimezone() > 0 ? lang("timezone gmt +".$user->getTimezone()) : lang("timezone gmt ".$user->getTimezone());
		$ical_info .= "BEGIN:VTIMEZONE\n";
		$ical_info .= "TZID:$tz_desc\n";
		$ical_info .= "BEGIN:STANDARD\n";
		$ical_info .= "TZOFFSETFROM:$tz\n";
		$ical_info .= "TZOFFSETTO:$tz\n";
		$ical_info .= "END:STANDARD\n";
		$ical_info .= "END:VTIMEZONE\n";
		
		foreach ($events as $event) {
			$ical_info .= "BEGIN:VEVENT\n";
			
			$event_start = new DateTimeValue($event->getStart()->getTimestamp() + 3600 * $user->getTimezone());
			$event_duration = new DateTimeValue($event->getDuration()->getTimestamp() + 3600 * $user->getTimezone());
			
			$startNext = new DateTimeValue($event_start->getTimestamp());
			$startNext->add('d', 1);
			if ($event->getTypeId() == 2) $ical_info .= "DTSTART;VALUE=DATE:" . $event_start->format('Ymd') ."\n";
			else $ical_info .= "DTSTART:" . $event_start->format('Ymd') ."T". $event_start->format('His') ."\n";
			if ($event->getTypeId() == 2) $ical_info .= "DTEND;VALUE=DATE:" . $startNext->format('Ymd') ."\n";
			else $ical_info .= "DTEND:" . $event_duration->format('Ymd') ."T". $event_duration->format('His') ."\n";

			$uid = $event->getId() . "@";
			$exploded = explode('/', ROOT);
			$exploded = explode('\\', end($exploded));
			$uid .= "fengoffice.com/".end($exploded);
			
			$subject = $event->getSubject();
			$description = str_replace(array(chr(13).chr(10), chr(13), chr(10)),'\n', $event->getDescription());
			$subject = str_replace(array(',', ';'), array('\,', '\;'), $subject);
			$description = str_replace(array(',', ';'), array('\,', '\;'), $description);
			
			$ical_info .= "DESCRIPTION:$description\n";
            $ical_info .= "SUMMARY:$subject\n";
		    $ical_info .= "UID:$uid\n";
		    $ical_info .= "SEQUENCE:0\n";
		    $ical_info .= "DTSTAMP:".$event->getUpdatedOn()->format('Ymd').'T'.$event->getUpdatedOn()->format('His')."\n";
			
		    $invitations = $event->getInvitations();
			if (is_array($invitations) && array_var($invitations, $user->getId())) {
				$inv = array_var($invitations, $user->getId());
		    	if ($inv->getInvitationState() == 1) $ical_info .= "STATUS:CONFIRMED\n"; 
		    	else if ($inv->getInvitationState() == 2) $ical_info .= "STATUS:CANCELLED\n";
		    	else $ical_info .= "STATUS:TENTATIVE\n";
			}
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
		
		$ical_info .= "END:VCALENDAR\n";
		
		return $ical_info;
	}

}

?>