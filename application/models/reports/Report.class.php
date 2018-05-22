<?php

  /**
  * Report class
  *
  * 
  */
  class Report extends BaseReport {
  	
  	protected $is_searchable = false;
  	protected $is_commentable = false;
      
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
		if(trim($this->getObjectName()) == ''){
			$errors[] = lang('report name required');
		}
		if(trim($this->getReportObjectTypeId()) == ''){
			$errors[] = lang('report object type required');
		}
	} // validate
	

	/**
	 * Returns true if $user can access this report
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canView(Contact $user) {
		return can_read($user, $this->getMembers(), $this->getObjectTypeId());
	} // canView

	/**
	 * Check if specific user can add reports
	 *
	 * @access public
	 * @param Contact $user
	 * @param Project $project
	 * @return booelean
	 */
	function canAdd(Contact $user, $context, &$notAllowedMember = '') {
		return can_add($user, $context, $this->manager()->getObjectTypeId(), $notAllowedMember );
	} // canAdd

	/**
	 * Check if specific user can edit this report
	 *
	 * @access public
	 * @param Contact $user
	 * @return boolean
	 */
	function canEdit(Contact $user) {
		return can_write($user, $this->getMembers(), $this->manager()->getObjectTypeId());
	} // canEdit

	/**
	 * Check if specific user can delete this report
	 *
	 * @access public
	 * @param Contact $user
	 * @return boolean
	 */
	function canDelete(Contact $user) {
		
		$can_delete = can_delete($user, $this->getMembers(), $this->manager()->getObjectTypeId());
		Hook::fire('report_can_delete', $this, $can_delete);
		
		return $can_delete;
	} // canDelete
    
	
	private $report_external_columns = null;
	
	function getReportExternalColumns() {
		if (is_null($this->report_external_columns)) {
			$ot = ObjectTypes::findById($this->getReportObjectTypeId());
			
			if ($ot instanceof ObjectType && $ot->getHandlerClass() != '') {
				eval('$ot_manager = '.$ot->getHandlerClass().'::instance();');
				
				if ($ot_manager instanceof ContentDataObjects) {
					$external_columns = $ot_manager->getExternalColumns();
					
					if ($ot_manager instanceof Timeslots) {
						$external_columns = array_merge($external_columns, ProjectTasks::instance()->getExternalColumns());
					}
					
					$this->report_external_columns = $external_columns;
				}
			}
		}
		
		return $this->report_external_columns;
	}
   
  } // Report

?>