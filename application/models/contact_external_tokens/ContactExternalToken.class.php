<?php

/**
 * ContactExternalToken class
 */
class ContactExternalToken extends BaseContactExternalToken {

	
	/**
	 * Save
	 *
	 */
   	function save() {
   		parent::save();     
    }
    
	// ---------------------------------------------------
	//  System functions
	// ---------------------------------------------------

	/**
	 * Delete this object
	 *
	 * @param void
	 * @return boolean
	 */
	function delete() {		
		return parent::delete();
	} // delete

	// ---------------------------------------------------
	//  DataObject implementation
	// ---------------------------------------------------
 	
	/**
	 * Return object name
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectName() {
		return $this->getDisplayName();
	} // getObjectName

	/**
	 * Return object type name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		return 'ContactExternalToken';
	} // getObjectTypeName


	function getArrayInfo(){
		$result = array(
			'id' => $this->getId(),
			'contact_id' => $this->getContactId(),
			'token' => $this->getToken(),
		    'external_key' => $this->getExternalKey(),
		    'external_name' => $this->getExternalName(),
		    'type' => $this->getType(),
			'expired_date' => $this->getExpiredDate());
		
		return $result;
	}
} // ContactExternalToken

?>