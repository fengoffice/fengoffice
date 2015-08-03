<?php

/**
 * Add an event
 *
 * @param string $name
 * @param unknown_type $data
 */
function evt_add($name, $data=array()) {
	$events = flash_get("events");
	if (!$events) {
		$events = array();
	}
	$events[] = array("name" => $name, "data" => $data);
	flash_add("events", $events);
}

/**
 * Returns the events
 *
 * @return array
 */
function evt_list() {
	$events = flash_get("events");
	if (!$events) return array();
	return $events;
}

function evt_pop() {
	$events = flash_pop("events");
	if (!$events) return array();
	return $events;
}

?>