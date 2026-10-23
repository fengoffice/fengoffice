<?php

/**
 * Add an event
 *
 * @param string $name
 * @param array $data
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

function evt_remove($name) {
	$events = flash_get("events");
	if (!$events) return;
	$filtered = array();
	foreach ($events as $event) {
		if ($event['name'] !== $name) {
			$filtered[] = $event;
		}
	}
	flash_add("events", $filtered);
}

function evt_pop() {
	$events = flash_pop("events");
	if (!$events) return array();
	return $events;
}

?>