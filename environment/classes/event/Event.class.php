<?php

class Event {
	
	private $events = array();

	/**
	 * Add an event
	 *
	 * @param string $name
	 * @param unknown $data
	 */
	function addEvent($name, $data) {
		$this->events[] = array(
			"name" => $name,
			"data" => $data
		);
	}
	
	/**
	 * Get the events
	 *
	 * @return array
	 */
	function getEvents() {
		return $this->events;
	}

}

?>