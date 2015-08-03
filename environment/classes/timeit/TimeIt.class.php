<?php

class TimeIt {
	static $timeslots = array();
	static $stack = array();
	static $index = -1;
	
	static function start($type = 'default') {
		self::$stack[++self::$index] = array(
			'start' => microtime(true),
			'type' => $type
		);
	}
	
	static function stop() {
		$timeslot = self::$stack[self::$index];
		$timeslot['end'] = microtime(true);
		$timeslot['time'] = $timeslot['end'] - $timeslot['start'];
		self::$timeslots[] = $timeslot;
		unset(self::$stack[self::$index--]);
		return $timeslot['time'];
	}
	
	static function add($type, $time, $start = 0, $end = 0) {
		self::$timeslots[] = array(
			'type' => $type,
			'start' => $start,
			'end' => $end,
			'time' => $time
		);
	}
	
	static function getTimeReportByType() {
		$types = array();
		$report = "";
		foreach (self::$timeslots as $t) {
			if (!isset($types[$t['type']])) {
				$types[$t['type']] = $t['time'];
			} else {
				$types[$t['type']] += $t['time'];
			}
		}
		foreach ($types as $k => $v) {
			$report .= "$k: " . number_format($v, 4) . "\n";
		}
		return $report;
	}
}

?>