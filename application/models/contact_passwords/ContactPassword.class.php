<?php

/**
 * ContactPassword class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class ContactPassword extends BaseContactPassword {

	
	/**
	 * Save
	 *
	 */
   	function save() {
   		parent::save();
   		// If more than 10 passwords, delete oldest
   		$passwords = ContactPasswords::findAll(array(
   			'conditions' => array('`contact_id` = ?', $this->getContactId())
   		));
   		
   		if(count($passwords) > 10){
   			$oldest = ContactPasswords::getOldestContactPassword($this->getContactId());
   			$oldest[0]->delete();
   		}        
    }
    
	// ---------------------------------------------------
	//  System functions
	// ---------------------------------------------------

	/**
	 * Validate data before save
	 *
	 * @access public
	 * @param array $errors
	 * @return void
	 */
	function validate(&$errors) {

		if (!isset($this->perform_validation) || $this->perform_validation) {

			// Validate min length for the password
			if(!ContactPasswords::validateMinLength($this->password_temp)) {
				$min_pass_length = config_option('min_password_length', 0);			
				$errors[] = lang('password invalid min length', $min_pass_length);
			} // if
			
			// Validate password numbers
			if(!ContactPasswords::validateNumbers($this->password_temp)) {
				$pass_numbers = config_option('password_numbers', 0);			
				$errors[] = lang('password invalid numbers', $pass_numbers);
			} // if
			
			// Validate uppercase characters
			if(!ContactPasswords::validateUppercaseCharacters($this->password_temp)) {	
				$pass_uppercase = config_option('password_uppercase_characters', 0);		
				$errors[] = lang('password invalid uppercase', $pass_uppercase);
			} // if
			
			// Validate metacharacters
			if(!ContactPasswords::validateMetacharacters($this->password_temp)) {	
				$pass_metacharacters = config_option('password_metacharacters', 0);		
				$errors[] = lang('password invalid metacharacters', $pass_metacharacters);
			} // if
			
			// Validate against password history
			if(!ContactPasswords::validateAgainstPasswordHistory($this->getContactId(), $this->password_temp)) {			
				$errors[] = lang('password exists history');
			} // if
			
			// Validate new password character difference
			if(!ContactPasswords::validateCharDifferences($this->getContactId(), $this->password_temp)) {			
				$errors[] = lang('password invalid difference');
			} // if
		}
	} // validate

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
		return 'contactpassword';
	} // getObjectTypeName


	function getArrayInfo(){
		$result = array(
			'id' => $this->getId(),
			'contact_id' => $this->getContactId(),
			'password' => $this->getPassword(),
			'password_date' => $this->getPasswordDate());
		
		return $result;
	}
} // ContactPassword

?>