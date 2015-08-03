<?php

/**
 * CronEvent class
 *
 * @author Ignacio de Soto <ignacio.desoto@fengoffice.com>
 */
class CronEvent extends BaseCronEvent {

	function getDisplayName() {
		return lang("cron event name " . $this->getName());
	}
	
	function getDisplayDescription() {
		return lang("cron event desc " . $this->getName());
	}

} // CronEvent

?>