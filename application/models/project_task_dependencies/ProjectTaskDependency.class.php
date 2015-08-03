<?php

  /**
  * ProjectTaskDependency class
  *
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  class ProjectTaskDependency extends BaseProjectTaskDependency {
    
    /**
    * Construct the object
    *
    * @param void
    * @return null
    */
    function __construct() {
      parent::__construct();
    } // __construct
    

    
	/**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return null
	 */
	function validate(&$errors) {
		// TODO: check for circular references
	}
    
  } // ProjectTaskDependency

?>