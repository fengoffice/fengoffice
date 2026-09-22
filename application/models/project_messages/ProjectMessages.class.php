<?php

/**
 * ProjectMessages, generated on Sat, 04 Mar 2006 12:21:44 +0100 by
 * DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ProjectMessages extends BaseProjectMessages {

	function __construct() {
		parent::__construct();
		$this->object_type_name = 'message';
	}

	/**
	 * Returns an array of object columns that are available to be shown in the custom properties form.
	 * This extends the function in the parent class.
	 * 
	 * @access protected
	 * @return array Array of object columns available to be shown in the custom properties form.
	 */
	function getColumnsAvailableInForms() {
		return [
			'text',
		];
	}

	/**
	 * Whether the class can use property groups.
	 * 
	 * @return bool true if the class can use property groups, false otherwise.
	 */
	function canUsePropertyGroups() {
		return true;
	}

} // ProjectMessages

?>