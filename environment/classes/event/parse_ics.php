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
		
		// Check to see if this is a multi-line part,
		// (they begin with a space)
		while (substr($buffer_temp, 0, 1) == " ")
			{
			// If yes, process it and keep reading until
			// new buffer line doesn't begin with " ".
			$buffer = $buffer.substr($buffer_temp, 1);
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
					
				$cal[$event]['stamp_date'] = $date[1].$date[2].$date[3];
				$cal[$event]['stamp_time'] = $date[4].$date[5];
				$cal[$event]['stamp_unix'] = mktime($date[4], $date[5], $date[6], $date[2],$date[3], $date[1]);
				break;
				
				
				// Summary of event
				case 'SUMMARY':
				$data = str_replace("\\n", '<br />', $data);
				$data = str_replace("\\r", '<br />', $data);
				$data = stripslashes($data);
				$data = htmlentities($data);
				$cal[$event]['summary'] = $data;
				break;
				
				
				// Event description
				case 'DESCRIPTION':
				$data = str_replace("\\n", '<br />', $data);
				$data = str_replace("\\r", '<br />', $data);
				$data = stripslashes($data);
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
				
				$att = explode(';', $buffer);
				foreach ($att as $value) 
					{
					$att_content = explode('=', $value);
					
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
						$cal[$event]['attendee'][$attendee]['name'] = $att_content[1];
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
				$cal[$event]['location'] = $data;
				break;
				
				// Status of event
				case 'STATUS':
				$cal[$event]['status'] = $data;
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
				$cal[$event]['alarm']['attach'] = $data;

				$temp = explode(';', $field);
				$temp = explode('=', $temp[1]);
				$cal[$event]['alarm']['attach_value'] = $temp[1];
				unset($temp);
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
?>