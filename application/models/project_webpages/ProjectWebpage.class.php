<?php

/**
 * ProjectWebpage class
 * Generated on Wed, 15 Mar 2006 22:57:46 +0100 by DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ProjectWebpage extends BaseProjectWebpage {

	/**
	 * Array of searchable columns
	 *
	 * @var array
	 */
	protected $searchable_columns = array('name', 'description');

	

	/**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return null
	 */
	function validate(&$errors) {
		if(!$this->validatePresenceOf('name')) {
			$errors[] = lang('webpage title required');
		} // if
		if(!$this->validatePresenceOf('url') || $this->getUrl() == 'http://') {
			$errors[] = lang('webpage url required');
		} // if
	} // validate

	// ---------------------------------------------------
	//  URLs
	// ---------------------------------------------------

	/**
	 * Return view webpage URL of this webpage
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getViewUrl() {
		return get_url('webpage', 'view', $this->getId());
	} // getAccountUrl

	/**
	 * Return edit webpage URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return get_url('webpage', 'edit', $this->getId());
	} // getEditUrl

	/**
	 * Return add webpage URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getAddUrl() {
		return get_url('webpage', 'add');
	} // getEditUrl

	/**
	 * Return delete webpage URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('webpage', 'delete', $this->getId());
	} // getDeleteUrl


	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------


	/**
	 * Return object name
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectName() {
		return parent::getObjectName();
	} // getObjectName


	/**
	 * Return object URl
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getViewUrl();
	} // getObjectUrl

	function getDashboardObject(){
		$result = parent::getDashboardObject();
		$result["url"] = $this->getUrl();
		$result["description"] = $this->getDescription();
		return $result;
	}

	
	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	function canAdd(Contact $user, $context, &$notAllowedMember = ''){
		return can_add($user, $context, ProjectWebpages::instance()->getObjectTypeId(), $notAllowedMember);
	}
	

	/**
	 * Returns true if $user can access this webpage
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canView(Contact $user) {
		return can_read($user, $this->getMembers(), $this->getObjectTypeId());
	} // canView

	/**
	 * Check if specific user can edit this webpage
	 *
	 * @access public
	 * @param Contact $user
	 * @return boolean
	 */
	function canEdit(Contact $user) {
		return can_write($user, $this->getMembers(), $this->getObjectTypeId());
	} // canEdit


	/**
	 * Check if specific user can delete this webpage
	 *
	 * @access public
	 * @param Contact $user
	 * @return boolean
	 */
	function canDelete(Contact $user) {
		return can_delete($user,$this->getMembers(), $this->getObjectTypeId());
	} // canDelete

	/**
	 * Check if specific user can comment this webpage
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function canAddComment(Contact $user) {
		return can_write($user, $this->getMembers(), $this->getObjectTypeId());
	} // canAddComment

	
}
?>