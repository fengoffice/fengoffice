<?php

/**
 * QueuedEmail class
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class QueuedEmail extends BaseQueuedEmail {

	function save() {
		if (!$this->getTimestamp() instanceof DateTimeValue) {
			$this->setTimestamp(DateTimeValueLib::now());
		}
		parent::save();
	}

} // QueuedEmail

?>