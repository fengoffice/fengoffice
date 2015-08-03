<?php

  /**
  * ProjectChart class
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  class ProjectChartParam extends BaseProjectChartParam {
 
  	// ---------------------------------------------------
	//  System
	// ---------------------------------------------------

	/**
	 * Delete this object
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function delete() {
		return parent::delete();
	} // delete

	/**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return null
	 */
	function validate(&$errors) {
		return true;
	} // validate


  } // ProjectChart 
?>