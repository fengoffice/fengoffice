<?php

  /**
  * This class is used to produce DateTimeValue object based on timestamos, strings etc
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class DateTimeValueLib {
  
    /**
    * Returns current time object in gmt 0
    *
    * @param void
    * @return DateTimeValue
    */
    static function now() {
      return new DateTimeValue(time());
    } // now
    
    /**
    * This function works like mktime, just it always returns GMT
    *
    * @param integer $hour
    * @param integer $minute
    * @param integer $second
    * @param integer $month
    * @param integer $day
    * @param integer $year
    * @return DateTimeValue
    */
    static function make($hour, $minute, $second, $month, $day, $year) {
      return new DateTimeValue(mktime($hour, $minute, $second, $month, $day, $year));
    } // make
    
    /**
    * Make time from string using strtotime() function. This function will return null
    * if it fails to convert string to the time
    *
    * @param string $str
    * @return DateTimeValue
    */
    static function makeFromString($str) {
      $timestamp = strtotime($str);// + date('Z');
      return ($timestamp === false) || ($timestamp === -1) ? null : new DateTimeValue($timestamp);
    } // makeFromString
  
    /**
     * Function to calculate date or time difference.
     *
     * Function to calculate date or time difference. Returns an array or
     * false on error.
     *
     * @author       J de Silva                             <giddomains@gmail.com>
     * @copyright    Copyright &copy; 2005, J de Silva
     * @link         http://www.gidnetwork.com/b-16.html    Get the date / time difference with PHP
     * @param        int                                 $start
     * @param        int                                 $end
     * @return       array
     */
    static function get_time_difference($start, $end, $subtract = 0)
    {
    	$uts['start']      = $start;
    	$uts['end']        = $end;
    	if( $uts['start']!==-1 && $uts['end']!==-1 )
    	{
    		if( $uts['end'] >= $uts['start'] ){
    			$diff    =    $uts['end'] - $uts['start'];
    			$sign = 1;
    		} else {
    			$diff    =    $uts['start'] - $uts['end'];
    			$sign = -1;
    		}
    		
    		$diff -= $subtract;
    		
    		if( $days=intval((floor($diff/86400))) )
    			$diff = $diff % 86400;
    		if( $hours=intval((floor($diff/3600))) )
    			$diff = $diff % 3600;
    		if( $minutes=intval((floor($diff/60))) )
    			$diff = $diff % 60;
    		$diff    =    intval( $diff );
    		
    		return( array('days'=>$days * $sign, 'hours'=>$hours * $sign, 'minutes'=>$minutes * $sign, 'seconds'=>$diff * $sign) );
    	}
    	else
    	{
    		throw new Exception("Invalid date/time data detected");
    	}
    	return false;
    }
    
    
	static function dateFromFormatAndString($format, $date_str) {
		if (trim($date_str) == '') return self::now();
		
		$formatChars = array('d', 'D', 'j', 'l', 'N', 'S', 'w', 'z', 'W', 'F', 'm', 'M', 'n', 't', 'L', 'o', 'Y', 'y',
						'a', 'A', 'B', 'g', 'G', 'h', 'H', 'i', 's', 'u', 'e', 'I', 'O', 'P', 'T', 'Z', 'c', 'r', 'U');
		
		$month_nums = array(
			strtolower(lang('month 1')) => 1, 
			strtolower(lang('month 2')) => 2, 
			strtolower(lang('month 3')) => 3, 
			strtolower(lang('month 4')) => 4, 
			strtolower(lang('month 5')) => 5, 
			strtolower(lang('month 6')) => 6,
			strtolower(lang('month 7')) => 7, 
			strtolower(lang('month 8')) => 8, 
			strtolower(lang('month 9')) => 9, 
			strtolower(lang('month 10')) => 10, 
			strtolower(lang('month 11')) => 11, 
			strtolower(lang('month 12')) => 12, 
		);
		
		// Build pattern from format ( generates an array with separators and values to parse)
		$idx = 0;
		$i = 0;
		$pattern = array();
		while ($idx < strlen($format)) {
			while ($idx < strlen($format) && in_array($format[$idx], $formatChars)) {
				$pattern[$i++] = array('val' => $format[$idx], 'type' => 'f', 'len' => 1);
				$idx++;
			}
			
			$sep = '';
			while ($idx < strlen($format) && !in_array($format[$idx], $formatChars)) {
				$sep .= $format[$idx];
				$idx++;
			}
			if ($sep != '') {
				$pattern[$i++] = array('val' => $sep, 'type' => 's', 'len' => strlen($sep));
			}
		}
		
		// Get values from date string, applying the pattern
		$ini = 0;
		$end = 0;
		$i = 0;
		$parsed_date = array();
		while ($i < count($pattern)) {
			$pat = $pattern[$i];
			if ($pat['type'] == 's') {
				$ini += $pat['len'];
			} else {
				if (isset($pattern[$i+1])) {
					$end = strpos($date_str, $pattern[$i+1]['val'], $ini);
					if ($end === false) $end = strlen($date_str);
				} else {
					$end = strlen($date_str);
				}
				$parsed_date[$pat['val']] = substr($date_str, $ini, $end - $ini);
				$ini = $end;
				if ($end == strlen($date_str)) break;
			}
			$i++;
		}
		
		// build the object to return, from the calculated res
		
		// get day of month
		if (isset($parsed_date['d'])) $day = $parsed_date['d'];
		else if (isset($parsed_date['j'])) $day = str_pad($parsed_date['j'], 2, '0', STR_PAD_LEFT);
		else throw new Exception("Day of month not specified, date_str:[$date_str] format:[$format]");

		// get month
		if (isset($parsed_date['m'])) $month = $parsed_date['m'];
		else if (isset($parsed_date['n'])) $month = str_pad($parsed_date['n'], 2, '0', STR_PAD_LEFT);
		else if (isset($parsed_date['F'])) $month = $month_nums[strtolower($parsed_date['F'])];
		else if (isset($parsed_date['M'])) {
			$key = strtolower($parsed_date['M']);
			foreach ($month_nums as $name => $number) {
				if (substr($name, 0, strlen($key)) == $key) {
					$month = $number;
					break;
				}
			}
		}
		else throw new Exception("Month not specified, date_str:[$date_str] format:[$format]");
		
		// get year
		if (isset($parsed_date['Y'])) $year = $parsed_date['Y'];
		else if (isset($parsed_date['o'])) $year = $parsed_date['o'];
		else if (isset($parsed_date['y'])) $year = $parsed_date['y'] >= 70 ? '19'.$parsed_date['y'] : '20'.$parsed_date['y'];
		else throw new Exception("Year not specified, date_str:[$date_str] format:[$format]"); 
		
		// get hour
		if (isset($parsed_date['H'])) $hour = $parsed_date['H'];
		else if (isset($parsed_date['G'])) $hour = $parsed_date['G'];
		else if (isset($parsed_date['h'])) {
			$pm = (isset($parsed_date['a']) && $parsed_date['a'] = 'pm') || (isset($parsed_date['A']) && $parsed_date['A'] = 'PM');
			$hour = $parsed_date['h'] + ($pm ? 12 : 0);
		}
		else if (isset($parsed_date['g'])) {
			$pm = (isset($parsed_date['a']) && $parsed_date['a'] = 'pm') || (isset($parsed_date['A']) && $parsed_date['A'] = 'PM');
			$hour = $parsed_date['g'] + ($pm ? 12 : 0);
		}
		else $hour = "0";
		
		// get minute
		if (isset($parsed_date['i'])) $minute = $parsed_date['i'];
		else $minute = "0";
		
		// get second
		if (isset($parsed_date['s'])) $second = $parsed_date['s'];
		else $second = "0";

		return self::make(trim($hour), trim($minute), trim($second), trim($month), trim($day), trim($year));
	}

    
  } // DateTimeValueLib

?>