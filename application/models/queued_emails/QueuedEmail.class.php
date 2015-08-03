<?php

/**
 * QueuedEmail class
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class QueuedEmail extends BaseQueuedEmail {

	function save() {
		$this->setTimestamp(DateTimeValueLib::now());
		parent::save();
	}

} // QueuedEmail

?>