<?php

/**
 * BillingCategory class
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class BillingCategory extends BaseBillingCategory {

	// ---------------------------------------------------
	//  URLs
	// ---------------------------------------------------

	/**
	 * Return tag URL
	 *
	 * @param void
	 * @return string
	 */
	function getViewUrl() {
		$object = $this->getObject();
		return $object instanceof ProjectDataObject ? $object->getObjectUrl() . '#BillingCategory' . $this->getId() : '';
	} // getViewUrl

	/**
	 * Return add BillingCategory URL for specific object
	 *
	 * @param ProjectDataObject $object
	 * @return string
	 */
	static function getAddUrl(ProjectDataObject $object) {
		return get_url('billing', 'add', array(
        'object_id' => $object->getObjectId(),
        'object_manager' => get_class($object->manager())
		)); // get_url
	} // getAddUrl

	/**
	 * Return edit URL
	 *
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return get_url('billing', 'edit', array('id' => $this->getId()));
	} // getEditUrl

	/**
	 * Return delete URL
	 *
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('billing', 'delete', array('id' => $this->getId()));
	} // getDeleteUrl

	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	/**
	 * Can $user view this object
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canView(Contact $user) {
		return can_read($user,$this);
	} // canView

	/**
	 * Empty implementation of static method.
	 *
	 * Add tag permissions are done through ProjectDataObject::canBillingCategory() method. This
	 * will return BillingCategory permissions for specified object
	 *
	 * @param User $user
	 * @param Project $project
	 * @return boolean
	 */
	function canAdd(Contact $user, Project $project) {		
		return can_add($user,$project,get_class(BillingCategories::instance()));
	} // canAdd

	/**
	 * Empty implementation of static method. Update tag permissions are check by the taggable
	 * object, not tag itself
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canEdit(Contact $user) {
		return can_write($user,$this);
	} // canEdit

	/**
	 * Empty implementation of static method. Update tag permissions are check by the taggable
	 * object, not tag itself
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canDelete(Contact $user) {
		return can_delete($user,$this);
	} // canDelete

	// ---------------------------------------------------
	//  System
	// ---------------------------------------------------

	/**
	 * Validate before save
	 *
	 * @param array $error
	 * @return null
	 */
	function validate(&$errors) {
		if(!$this->validatePresenceOf('default_value')) {
			$errors[] = lang('BillingCategory default_value required');
		} // if
	} // validate


	function delete(){
		$users = $this->getCategoryUsers();
		if ($users){
			foreach ($users as $user){
				$user->setDefaultBillingId(0);
				$user->save();
			}
		}
		parent::delete();
	}
	
	function getCategoryUsers() {
		return Contacts::findAll(array('conditions' => 'default_billing_id = ' . $this->getId()));
	}
	
	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------
	

	/**
	 * Return object name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectName() {
		$object = $this->getObject();
		return $object instanceof ProjectDataObject ? lang('BillingCategory on object', substr_utf($this->getText(), 0, 50) . '...', $object->getObjectName()) : $this->getObjectTypeName();
	} // getObjectName

	/**
	 * Return object type name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		return 'BillingCategory';
	} // getObjectTypeName

	/**
	 * Return view tag URL
	 *
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getViewUrl();
	} // getObjectUrl

} // BillingCategory

?>