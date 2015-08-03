<?php

/**
 * Single date time value. This class provides some handy methods for working
 * with timestamps and extracting data from them
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class DateTimeValue {

	/**
	 * Internal timestamp value
	 *
	 * @var integer
	 */
	private $timestamp;

	/**
	 * Cached day value
	 *
	 * @var integer
	 */
	private $day;

	/**
	 * Cached month value
	 *
	 * @var integer
	 */
	private $month;

	/**
	 * Cached year value
	 *
	 * @var integer
	 */
	private $year;

	/**
	 * Cached hour value
	 *
	 * @var integer
	 */
	private $hour;

	/**
	 * Cached minutes value
	 *
	 * @var integer
	 */
	private $minute;

	/**
	 * Cached seconds value
	 *
	 * @var integer
	 */
	private $second;

	/**
	 * Construct the DateTimeValue
	 *
	 * @param integer $timestamp
	 * @return DateTimeValue
	 */
	function __construct($timestamp) {
		$this->setTimestamp($timestamp);
	} // __construct

	/**
	 * Advance for specific time
	 *
	 * @param void
	 * @param integer $input Move the timestamp for this number of seconds
	 * @param boolean $mutate If true update this timestamp, else reutnr new object and dont touch internal timestamp
	 * @throws InvalidParamError
	 */
	function advance($input, $mutate = true) {
		$timestamp = (integer) $input;
		if($mutate) {
			$this->setTimestamp($this->getTimestamp() + $timestamp);
		} else {
			return new DateTimeValue($this->getTimestamp() + $timestamp);
		} // if
	} // advance

	/**
	 * This function will return true if this day is today
	 * Date must be in GMT0
	 * @param void
	 * @return boolean
	 */
	function isToday() {
		$today = DateTimeValueLib::now();
		if (logged_user() instanceof Contact) {
			$date = new DateTimeValue($this->getTimestamp() + logged_user()->getTimezone() * 3600);
			$today = new DateTimeValue($today->getTimestamp() + logged_user()->getTimezone() * 3600);
		} else {
			$date = $this;
		}
		return $date->getDay() == $today->getDay() &&
			$date->getMonth() == $today->getMonth() &&
			$date->getYear() == $today->getYear();
	} // isToday

	/**
	 * This function will return true if this datetime is yesterday
	 *
	 * @param void
	 * @return boolean
	 */
	function isYesterday() {
		$yesterday = DateTimeValueLib::makeFromString('yesterday');
		return $this->getDay() == $yesterday->getDay() && $this->getMonth() == $yesterday->getMonth() && $this->getYear() == $yesterday->getYear();
	} // isYesterday

	/**
	 * This function will move interlan data to the beginning of day and return modified object.
	 * It can be called as:
	 *
	 * $beggining = DateTimeValueLib::now()->beginningOfDay()
	 *
	 * @access public
	 * @param void
	 * @return DateTime
	 */
	function beginningOfDay() {
		$this->setHour(0);
		$this->setMinute(0);
		$this->setSecond(0);
		return $this;
	} // beginningOfDay

	/**
	 * This function will set hours, minutes and seconds to 23:59:59 and return this object.
	 *
	 * If you wish to get end of this day simply type:
	 *
	 * $end = DateTimeValueLib::now()->endOfDay()
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function endOfDay() {
		$this->setHour(23);
		$this->setMinute(59);
		$this->setSecond(59);
		return $this;
	} // endOfDay

	// ---------------------------------------------------
	//  Format to some standard values
	// ---------------------------------------------------

	/**
	 * Return formated datetime
	 *
	 * @param string $format
	 * @return string
	 */
	function format($format) {
		return gmdate($format, $this->getTimestamp());
	} // format

	/**
	 * Return datetime formated in MySQL datetime format
	 *
	 * @param void
	 * @return string
	 */
	function toMySQL() {
		return $this->format(DATE_MYSQL);
	} // toMySQL

	/**
	 * Return ISO8601 formated time
	 *
	 * @param void
	 * @return string
	 */
	function toISO8601() {
		return $this->format(DATE_ISO8601);
	} // toISO

	/**
	 * Return atom formated time (W3C format)
	 *
	 * @param void
	 * @return string
	 */
	function toAtom() {
		return $this->format(DATE_ATOM);
	} // toAtom

	/**
	 * Return RSS format
	 *
	 * @param void
	 * @return string
	 */
	function toRSS() {
		return $this->format(DATE_RSS);
	} // toRSS

	/**
	 * Return iCalendar formated date and time
	 *
	 * @param void
	 * @return string
	 */
	function toICalendar() {
		return $this->format('Ymd\THis\Z');
	} // toICalendar

	// ---------------------------------------------------
	//  Utils
	// ---------------------------------------------------

	/**
	 * Break timestamp into its parts and set internal variables
	 *
	 * @param void
	 * @return null
	 */
	private function parse() {
		$data = gmdate("Y-m-d-H-i-s", $this->timestamp);
		$splitted = explode("-", $data);

		if($data) {
			$this->year   = (integer) $splitted[0];
			$this->month  = (integer) $splitted[1];
			$this->day    = (integer) $splitted[2];
			$this->hour   = (integer) $splitted[3];
			$this->minute = (integer) $splitted[4];
			$this->second = (integer) $splitted[5];
		} // if
	} // parse

	/**
	 * Update internal timestamp based on internal param values
	 *
	 * @param void
	 * @return null
	 */
	private function setTimestampFromAttributes() {
		$this->setTimestamp(mktime(
		$this->hour,
		$this->minute,
		$this->second,
		$this->month,
		$this->day,
		$this->year
		)); // setTimestamp
	} // setTimestampFromAttributes

	// ---------------------------------------------------
	//  Getters and setters
	// ---------------------------------------------------

	/**
	 * Get timestamp
	 *
	 * @param null
	 * @return integer
	 */
	function getTimestamp() {
		return $this->timestamp;
	} // getTimestamp

	/**
	 * Set timestamp value
	 *
	 * @param integer $value
	 * @return null
	 */
	private function setTimestamp($value) {
		if (!is_numeric($value)) $value = 0;
		$this->timestamp = $value;
		$this->parse();
	} // setTimestamp

	/**
	 * Return year
	 *
	 * @param void
	 * @return integer
	 */
	function getYear() {
		return $this->year;
	} // getYear

	/**
	 * Set year value
	 *
	 * @param integer $value
	 * @return null
	 */
	function setYear($value) {
		$this->year = (integer) $value;
		$this->setTimestampFromAttributes();
	} // setYear

	/**
	 * Return numberic representation of month
	 *
	 * @param void
	 * @return integer
	 */
	function getMonth() {
		return $this->month;
	} // getMonth

	/**
	 * Set month value
	 *
	 * @param integer $value
	 * @return null
	 */
	function setMonth($value) {
		$this->month = (integer) $value;
		$this->setTimestampFromAttributes();
	} // setMonth

	/**
	 * Return days
	 *
	 * @param void
	 * @return integer
	 */
	function getDay() {
		return $this->day;
	} // getDay

	/**
	 * Set day value
	 *
	 * @param integer $value
	 * @return null
	 */
	function setDay($value) {
		$this->day = (integer) $value;
		$this->setTimestampFromAttributes();
	} // setDay

	/**
	 * Return hour
	 *
	 * @param void
	 * @return integer
	 */
	function getHour() {
		return $this->hour;
	} // getHour

	/**
	 * Set hour value
	 *
	 * @param integer $value
	 * @return null
	 */
	function setHour($value) {
		$this->hour = (integer) $value;
		$this->setTimestampFromAttributes();
	} // setHour

	/**
	 * Return minute
	 *
	 * @param void
	 * @return integer
	 */
	function getMinute() {
		return $this->minute;
	} // getMinute

	/**
	 * Set minutes value
	 *
	 * @param integer $value
	 * @return null
	 */
	function setMinute($value) {
		$this->minute = (integer) $value;
		$this->setTimestampFromAttributes();
	} // setMinute

	/**
	 * Return seconds
	 *
	 * @param void
	 * @return integer
	 */
	function getSecond() {
		return $this->second;
	} // getSecond

	/**
	 * Set seconds
	 *
	 * @param integer $value
	 * @return null
	 */
	function setSecond($value) {
		$this->second = (integer) $value;
		$this->setTimestampFromAttributes();
	} // setSecond

	/**
	 * Returns the date for the datetimevalue's week's monday
	 *
	 * @return DateTimeValue
	 */
	function getMondayOfWeek(){
		$dow = (integer)$this->format('N');
		$dt = new DateTimeValue($this->getTimestamp());
		$dt->add('d', -($dow - 1));
		return $dt;
	}
	
	/**
	 * Adds a certain value to the date. Type is one of yMwdhms
	 * 
	 * @return DateTimeValue
	 */
	function add($type, $amount){
		$a = array('d'=>86400, 'h'=>3600, 'm'=>60, 's'=>1);
		switch ($type){
			case 'y':
				$this->setYear($this->getYear() + $amount ); break;
			case 'M':
				$this->setMonth($this->getMonth() + ($amount % 12));
				$this->setYear($this->getYear() + (($amount - ($amount % 12)) / 12)); break;
			case 'w':
				$this->setTimestamp($this->getTimestamp() + $a['d'] * $amount * 7); break;
			case 'd':
				$this->setTimestamp($this->getTimestamp() + $a['d'] * $amount); break;
			case 'h':
				$this->setTimestamp($this->getTimestamp() + $a['h'] * $amount); break;
			case 'm':
				$this->setTimestamp($this->getTimestamp() + $a['m'] * $amount); break;
			case 's':
				$this->setTimestamp($this->getTimestamp() + $a['s'] * $amount); break;
		}
		return $this;
	}
	
	static function FormatTimeDiff(DateTimeValue $dt1, DateTimeValue $dt2=null, $format='yfwdhms', $modulus = 1, $subtract = 0){
		$t1 = $dt1->getTimestamp();
		if ($dt2)
			$t2 = $dt2->getTimestamp();
		else
			$t2 = time();
		$s = abs($t2 - $t1);
		$s -= $subtract;
		$s = $s - ($s % $modulus);
		$sign = $t2 > $t1 ? 1 : -1;
		$out = array();
		$left = $s;
		$format = array_unique(str_split(preg_replace('`[^yfwdhms]`', '', strtolower($format))));
		$format_count = count($format);
		$a = array('y'=>31556926, 'f'=>2629744, 'w'=>604800, 'd'=>86400, 'h'=>3600, 'm'=>60, 's'=>1);
		$i = 0;
		foreach($a as $k=>$v){
			if(in_array($k, $format)){
				++$i;
				if($i != $format_count){
					$out[$k] = $sign * (int)($left / $v);
					$left = $left % $v;
				}else{
					$out[$k] = $sign * ($left / $v);
				}
			}else{
				$out[$k] = 0;
			}
		}
		$result = '';
		if ($out['y'] > 0)
			$result .= ($result != ''? ', ':'') . ($out['y'] > 1 ? lang("x years", $out['y']) : lang("1 year"));
		if ($out['f'] > 0)
			$result .= ($result != ''? ', ':'') . ($out['f'] > 1 ? lang("x months", $out['f']) : lang("1 month"));
		if ($out['w'] > 0)
			$result .= ($result != ''? ', ':'') . ($out['w'] > 1 ? lang("x weeks", $out['w']) : lang("1 week"));
		if ($out['d'] > 0)
			$result .= ($result != ''? ', ':'') . ($out['d'] > 1 ? lang("x days", $out['d']) : lang("1 day"));
		if ($out['h'] > 0)
			$result .= ($result != ''? ', ':'') . ($out['h'] > 1 ? lang("x hours", $out['h']) : lang("1 hour"));
		if ($out['m'] > 0)
			$result .= ($result != ''? ', ':'') . ($out['m'] > 1 ? lang("x minutes", $out['m']) : lang("1 minute"));
		if ($out['s'] > 0)
			$result .= ($result != ''? ', ':'') . ($out['s'] > 1 ? lang("x seconds", $out['s']) : lang("1 second"));
			
		if ($result == '')
			$result = '< ' . lang("1 minute");
		return $result;
	}
} // DateTimeValue

?>