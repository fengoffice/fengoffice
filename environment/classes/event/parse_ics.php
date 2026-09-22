<?php
/*
+------------------------------------------- 
| iCalender File Parser
+------------------------------------------- 
| parse_ics.php
+------------------------------------------- 
| Written by Ben Barnett
| Mail: ben@menial.co.uk
+------------------------------------------- 
| This script is old, has been unused for some 
| time but may be handy for some.
|
| It is not even *near* feature complete to the
| iCalendar specification (RFC 2445), but can 
| deal with many of the common parts of it. 
|
| It is licensed under a Creative Commons
| Attribution, Non-Commercial Share-Alike (v2)
| license. It'd be great to have any
| changes sent to the address above.
+-------------------------------------------
| Pass the function the name of an .ics file
| and it'll return an array of calendar events
| in UNIX timestamp order. To see it's output
| diagramatically, 
| "echo '<pre>'.print_r(parse_ics('name')).'</pre>';
+-------------------------------------------
*/

/**
* iCalendar Parser
*
* Pass it the name (minus extension) of a .ics file and it'll return
* a multidimensional array of calendar events on success, a string on some errors
* and nothing on others as it's quite incomplete.
*
* CANT_OPEN_FILE ==> Function can't read ics file
* INVALID_FILETYPE ==> The file isn't recognised as an ics file
*
* @param string $calendar The file to open
* @return array An array of calendar events
*/
function parse_ical($calendar)
{


// Open calendar
$fp = @fopen($calendar, 'r');
if (!$fp) 
	{
	return 'CANT_OPEN_FILE';
	}
	
	
// Read first line
$buffer_temp = fgets($fp, 1024);

	// Check to see if this is actually an iCalendar file.
	if (trim(strtoupper($buffer_temp)) != 'BEGIN:VCALENDAR') 
		{
		return 'INVALID_FILETYPE';
		}
		
		
	
	// And set a few variables...
	$cal = array();
	$event = 0;
	$cal[0]['generator'] = 'Menial iCal Parser';
	
	// Set variable to enable sorting of array. 
	$cal[0]['start_unix'] = '';
	$flag_valarm = false;

// Now loop through line by line...
while (!feof($fp)) 
	{
		// Save prev read-ahead data
		$buffer = $buffer_temp;
		
		// Then read ahead again
		$buffer_temp = fgets($fp, 1024);
		
		// Remove newlines from new buffer
		$buffer_temp = preg_replace("/[\r\n]/", '', $buffer_temp);
		
		// Check to see if this is a multi-line part.
		// - RFC5545 folding: continuation lines begin with a space.
		// - Quoted-printable soft line breaks: lines may be split with a trailing '=' even if the next line
		//   does not start with a space (common in Gmail/Outlook iTIP bodies).
		while (substr($buffer_temp, 0, 1) == " " || (substr($buffer, -1) === '=' && stripos($buffer, 'ENCODING=QUOTED-PRINTABLE') !== false))
			{
			// If yes, process it and keep reading until condition ends.
			if (substr($buffer_temp, 0, 1) == " ") {
				// RFC folding: remove the leading space
				$buffer = $buffer.substr($buffer_temp, 1);
			} else {
				// QP soft break: remove trailing '=' and join next line as-is
				if (substr($buffer, -1) === '=') {
					$buffer = substr($buffer, 0, -1);
				}
				$buffer = $buffer.$buffer_temp;
			}
			$buffer_temp = fgets($fp, 1024);	
			$buffer_temp = preg_replace("/[\r\n]/", '', $buffer_temp);
			}
		
	
		// Begin parsing directives in current buffer
		switch ($buffer)
			{
			// New event
			case 'BEGIN:VEVENT':
			$attendee = 1;
			$event = $event+1;
			$cal[$event] = array();
			break;
			
			// End current event
			case 'END:VEVENT':
			
			break;
			
			// Begin alarm for current event
			case 'BEGIN:VALARM':
			$flag_valarm = true;
			break;
			
			// End alarm for current event
			case 'END:VALARM':
			$flag_valarm = false;
			break;
			
			
			
			default:
			$line = '';
			//Break up the line. We want indices 1 and 2. Not 0.
			preg_match("/([^:]+):(.*)/", $buffer, $line);
			
			// Need to both trim the field down and keep a copy for later processing.
			$field = $line[1];
			$data = $line[2];
			//****************echo '>>'.$data.'<br />';*****************//
			// Need to keep a copy of each property line.
			$property = $field;
			
			// Trim the property values off the last ';'
			$property_p = strpos($property, ';');
			if ($property_p != false) 
				{
				$property = substr($property, 0, $property_p);
				
				// And make it upper-case
				$property = strtoupper($property);
				}
			
			switch ($property)
				{
					
				/********** CALENDER INFO ***********/
				// Calendar Name
				case 'X-WR-CALNAME':
				$cal[0]['name'] = $data;
				break;
				
				// Calendar Description
				case 'X-WR-CALDESC':
				$cal[0]['description'] = stripslashes($data);
				break;
				
				// Main timezone of calendar
				case 'X-WR-TIMEZONE':
				$cal[0]['timezone'] = $data;
				break;
				
				case 'TZOFFSETFROM':
				if (!isset($cal[0]['tzoffsetfrom']))
					$cal[0]['tzoffsetfrom'] = $data;
				break;
				
				case 'TZOFFSETTO':
				if (!isset($cal[0]['tzoffsetto']))
					$cal[0]['tzoffsetto'] = $data;
				break;
				
				// Calendar ID
				case 'X-WR-RELCALID':
				$cal[0]['relcalid'] = $data;
				break;
				
				// Calendar Scale
				case 'CALSCALE':
				$cal[0]['calscale'] = $data;
				break;
				
				// iCalendar Version
				case 'VERSION':
				$cal[0]['cal_version'] = $data;
				break;
				
				// Product ID of file generator
				case 'PRODID':
				$cal[0]['prodid'] = stripslashes($data);
				break;
				
				// Method (REQUEST, REPLY, etc.)
				case 'METHOD':
				$cal[0]['method'] = stripslashes($data);
				break;
				
				/********** END CALENDER INFO ***********/
				
				
				/********** EVENT INFO ***********/
				
				// Unique ID of event
				case 'UID':
				$cal[$event]['uid'] = $data;
				break;
				
				// Start time of event
				case 'DTSTART':
				$date = '';
				$data = str_replace('T', '', $data);
				
				if (preg_match('/DTSTART;VALUE=DATE/', $field)) 
					{
					// ALL-DAY EVENT
					preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $data, $date);
					
					// UNIX timestamps can't deal with pre 1970 dates
					if ($date[1] <= 1970) 
						{
						$date[1] = 1971;
						}
					
					$cal[$event]['all_day'] = 1;
					$cal[$event]['start_date'] = $date[1].$date[2].$date[3];
					$cal[$event]['start_time'] = 0;
					$cal[$event]['start_unix'] = mktime(0, 0, 0, $date[2],$date[3], $date[1]);
					}
				else 
					{
					// TIME LIMITED EVENT
					preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{0,2})([0-9]{0,2})([0-9]{0,2})/', $data, $date);
					
					// UNIX timestamps can't deal with pre 1970 dates
					if ($date[1] <= 1970) 
						{
						$date[1] = 1971;
						}
					if (!$date[4]) $date[4] = 0;
					if (!$date[5]) $date[5] = 0;
					if (!$date[6]) $date[6] = 0;
					
					$cal[$event]['all_day'] = 0;
					$cal[$event]['start_date'] = $date[1].$date[2].$date[3];
					$cal[$event]['start_time'] = $date[4].$date[5];
					$cal[$event]['start_unix'] = mktime($date[4], $date[5], $date[6], $date[2],$date[3], $date[1]);
					}
				break;
				
				
				
				// End time of event
				case 'DTEND':
				
				$data = str_replace('T', '', $data);
				
				// TIME LIMITED EVENT
				preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{0,2})([0-9]{0,2})([0-9]{0,2})/', $data, $date);
					
				// UNIX timestamps can't deal with pre 1970 dates
				if ($date[1] <= 1970) 
					{
					$date[1] = 1971;
					}
				if (!$date[4]) $date[4] = 0;
				if (!$date[5]) $date[5] = 0;
				if (!$date[6]) $date[6] = 0;
					
				$cal[$event]['end_date'] = $date[1].$date[2].$date[3];
				$cal[$event]['end_time'] = $date[4].$date[5];
				$cal[$event]['end_unix'] = mktime($date[4], $date[5], $date[6], $date[2],$date[3], $date[1]);
				break;
				
				
				
				// Timestamp of event
				case 'DTSTAMP':
				
				$data = str_replace('T', '', $data);
				$data = str_replace('Z', '', $data);
				
				// TIME LIMITED EVENT
				preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{0,2})([0-9]{0,2})([0-9]{0,2})/', $data, $date);
					
				// UNIX timestamps can't deal with pre 1970 dates
				if ($date[1] <= 1970) 
					{
					$date[1] = 1971;
					}
				if (!$date[4]) $date[4] = 0;
				if (!$date[5]) $date[5] = 0;
				if (!$date[6]) $date[6] = 0;
					
				$cal[$event]['stamp_date'] = $date[1].$date[2].$date[3];
				$cal[$event]['stamp_time'] = $date[4].$date[5];
				$cal[$event]['stamp_unix'] = mktime($date[4], $date[5], $date[6], $date[2],$date[3], $date[1]);
				break;
				
				
				// Summary of event
				case 'SUMMARY':
				$data = ical_decode_text_value($field, $data);
				$data = str_replace("\n", '<br />', $data);
				$data = htmlentities($data);
				$cal[$event]['summary'] = $data;
				break;
				
				
				// Event description
				case 'DESCRIPTION':
				$data = ical_decode_text_value($field, $data);
				$data = str_replace("\n", '<br />', $data);
				$data = htmlentities($data);
				if ($flag_valarm == false) 
					{
					$cal[$event]['description'] = $data;
					}
				else 
					{
					$cal[$event]['alarm']['description'] = $data;
					}
				break;

				
				//NOT REMOTELY COMPLIANT WITH
				//ICALENDAR RFC. READ AND DO IT AGAIN!
				
				// List of attendees
				case 'ATTENDEE':
				if (!isset($attendee)) $attendee = 1;

				$att = explode(';', $buffer);
				foreach ($att as $value) 
					{
					$att_content = explode('=', $value);

					// Email
					if (isset($att_content[1]) && strpos($att_content[1], ':mailto:') !== false) {
						$exploded = explode(':mailto:', $att_content[1]);
						$cal[$event]['attendee'][$attendee]['mailto'] = end($exploded);
					}
					
					switch ($att_content[0])
						{
						// Calendar User Type
						case 'CUTYPE':
						$cal[$event]['attendee'][$attendee]['cutype'] = $att_content[1];
						break;
						
						// 
						case 'MEMBER':
						
						break;
						
						// 
						case 'PARTSTAT':
						$cal[$event]['attendee'][$attendee]['status'] = $att_content[1];
						break;
						
						// 
						case 'ROLE':
						$cal[$event]['attendee'][$attendee]['role'] = $att_content[1];
						break;
						
						// RSVP? True/False
						case 'RSVP':
						$cal[$event]['attendee'][$attendee]['rsvp'] = $att_content[1];
						break;
						
						// 
						case 'SENT-BY':
						
						break;
						
						// Common Name
						case 'CN':
							if (strpos($att_content[1], ':mailto:') !== false) {
								$exploded = explode(':mailto:', $att_content[1]);
								$cal[$event]['attendee'][$attendee]['name'] = ical_decode_text_value($field, $exploded[0]);
							} else {
								$cal[$event]['attendee'][$attendee]['name'] = ical_decode_text_value($field, $att_content[1]);
							}
						break;
						
						// 
						case 'DIR':
						
						break;
						
						// 
						case 'DELEGATED-TO':
						
						break;
						
						// 
						case 'DELEGATED-FROM':
						
						break;
						}
					
					}
				
				
				$attendee++;
				unset($temp, $att, $value);
				break;
				
				
				// URL of event
				case 'URL':
				$cal[$event]['url'] = $data;
				break;
				
				// Location of event
				case 'LOCATION':
				$cal[$event]['location'] = ical_decode_text_value($field, $data);
				break;
				
				// Status of event
				case 'STATUS':
				$cal[$event]['status'] = $data;
				break;
				
				// Organizer of event
				case 'ORGANIZER':
					if (str_starts_with($data, 'mailto:')) {
						$email = substr($data, 7);
						$org_exp = explode('=', $field);
						$name = end($org_exp);
					} else {
						$email = $data;
						$name = $data;
					}
				$email = ical_decode_text_value($field, $email);
				$name = ical_decode_text_value($field, $name);
				$cal[$event]['organizer'] = array('email' => $email, 'name' => $name);
				break;
				
				
				/********** ALARM INFO ***********/
		
				// Alarm Action
				case 'ACTION':
				$cal[$event]['alarm']['action'] = $data;
				break;
				
				// When should the alarm go off?
				case 'TRIGGER':
				$cal[$event]['alarm']['trigger'] = $data;
				break;
				
				// Alarm attachment
				case 'ATTACH':
				if ($flag_valarm) {
					$cal[$event]['alarm']['attach'] = ical_decode_text_value($field, $data);

					$temp = explode(';', $field);
					if (isset($temp[1])) {
						$temp2 = explode('=', $temp[1]);
						if (isset($temp2[1])) {
							$cal[$event]['alarm']['attach_value'] = $temp2[1];
						}
					}
					unset($temp);
				} else {
					if (!isset($cal[$event]['attach']) || !is_array($cal[$event]['attach'])) {
						$cal[$event]['attach'] = array();
					}
					$cal[$event]['attach'][] = array(
						'value' => ical_decode_text_value($field, $data),
						'field' => $field,
					);
				}
				break;
				
				// Alarm description handler is joined 
				// with event description handler
				
				/********** END ALARM INFO ***********/
				
				
				/********** RECURRENCE RULE INFO ***********/
				case 'RRULE':
				$cal[$event]['rrule'] = array();
				$rrule = explode(';',$data);
				
				foreach ($rrule as $value)
					{
					$rrule_content = explode('=', $value);
					
					switch ($rrule_content[0])
						{
						// Frequency of repeating event
						case 'FREQ':
						$cal[$event]['rrule']['freq'] = $rrule_content[1];
						break;
						
						// Interval to repeat the frequency
						// eg. FREQ=WEEKLY;INTERVAL=2 ==> repeat every 2 weeks
						case 'INTERVAL':
						$cal[$event]['rrule']['interval'] = $rrule_content[1];
						break;
						
						// Number of times to repeat event
						case 'COUNT':
						$cal[$event]['rrule']['count'] = $rrule_content[1];
						break;
						
						// Repeat event until date/time
						case 'UNTIL':
						$data = str_replace('T', '', $data);
						$data = str_replace('Z', '', $data);
						
						// TIME LIMITED EVENT
						preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{0,2})([0-9]{0,2})([0-9]{0,2})/', $data, $date);
							
						// UNIX timestamps can't deal with pre 1970 dates
						if ($date[1] <= 1970) 
							{
							$date[1] = 1971;
							}
							
						$cal[$event]['rrule']['until_date'] = $date[1].$date[2].$date[3];
						$cal[$event]['rrule']['until_time'] = $date[4].$date[5];
						$cal[$event]['rrule']['until_unix'] = mktime($date[4], $date[5], $date[6], $date[2],$date[3], $date[1]);
						break;
						
						
						//**** BYxxxx RULES ****//
						case 'BYSECOND':
						$cal[$event]['rrule']['bysecond'] = $rrule_content[1];
						break;
						
						case 'BYMINUTE':
						$cal[$event]['rrule']['byminute'] = $rrule_content[1];
						break;
						
						case 'BYHOUR':
						$cal[$event]['rrule']['byhour'] = $rrule_content[1];
						break;
						
						case 'BYDAY':
						$cal[$event]['rrule']['byday'] = $rrule_content[1];
						break;
						
						case 'BYMONTH':
						$cal[$event]['rrule']['bymonth'] = $rrule_content[1];
						break;
						
						case 'BYYEAR':
						$cal[$event]['rrule']['byyear'] = $rrule_content[1];
						break;
						

						case 'BYMONTHDAY':
						$cal[$event]['rrule']['bymonthday'] = $rrule_content[1];
						break;
						
						case 'BYYEARDAY':
						$cal[$event]['rrule']['byyearday'] = $rrule_content[1];
						break;
						
						case 'BYWEEKNO':
						$cal[$event]['rrule']['byweekno'] = $rrule_content[1];
						break;
						//**** END BYxxxx RULES ****//
						
						// Day that work week start
						case 'WKST':
						$cal[$event]['rrule']['wkst'] = $rrule_content[1];
						break;
						
						//
						case 'BYSETPOS':
						$cal[$event]['rrule']['bysetpos'] = $rrule_content[1];
						break;
						
						
						}
					}
				unset($rrule, $rrule_content, $value);
				break;
				/********** RECURRENCE RULE INFO ***********/
				
				
				}
				/********** END EVENT INFO ***********/
			break;
			
			}
			
	}

fclose($fp);

// Puts events in order using UNIX timestamp as
// a comparison point.
//usort($cal, 'compare'); 

// Unset "padding" varible
unset($cal[0]['start_unix']);

// Return parsed data.
return $cal;
}

// The function that does the comparing to 
// order events.
function compare($a, $b) 
	{
	return strnatcasecmp($a['start_unix'], $b['start_unix']);
	}

/**
 * Decode an iCalendar TEXT value, handling ENCODING/CHARSET parameters and RFC5545 escaping.
 *
 * @param string $field The full field name (may include params like ;ENCODING=QUOTED-PRINTABLE;CHARSET=UTF-8)
 * @param string $value The raw value from the line
 * @return string Decoded/unescaped text
 */
function ical_decode_text_value($field, $value) {
	$decoded = $value;

	// Decode based on ENCODING parameter (common: QUOTED-PRINTABLE, BASE64)
	$field_upper = strtoupper($field);
	if (strpos($field_upper, 'ENCODING=QUOTED-PRINTABLE') !== false) {
		if (function_exists('quoted_printable_decode')) {
			$decoded = quoted_printable_decode($decoded);
		}
	} else if (strpos($field_upper, 'ENCODING=BASE64') !== false) {
		$tmp = base64_decode($decoded, true);
		if ($tmp !== false) {
			$decoded = $tmp;
		}
	}

	// Convert charset to UTF-8 if specified
	if (preg_match('/CHARSET=([^;:]+)/i', $field, $m)) {
		$charset = trim($m[1], "\"'");
		if ($charset && function_exists('mb_convert_encoding')) {
			$charset_u = strtoupper($charset);
			if (!in_array($charset_u, array('UTF-8', 'UTF8'))) {
				$converted = @mb_convert_encoding($decoded, 'UTF-8', $charset);
				if ($converted !== false) {
					$decoded = $converted;
				}
			}
		}
	}

	// RFC5545 escaping for TEXT values
	// - \\ => \
	// - \n or \N => newline
	// - \, => ,
	// - \; => ;
	$decoded = str_replace("\\\\", "\\", $decoded);
	$decoded = str_replace(array('\\n', '\\N'), "\n", $decoded);
	$decoded = str_replace(array('\\,', '\\;'), array(',', ';'), $decoded);

	// Historical behavior expected by this codebase
	$decoded = stripslashes($decoded);

	return $decoded;
}
?>