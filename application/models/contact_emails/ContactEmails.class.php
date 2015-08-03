<?php

  /**
  * ContactEmails
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class ContactEmails extends BaseContactEmails {
  
  	
    /**
    * Return default contact Email
    *
    * @access public
    * @param Contact $contact, $type_id
    * @return EmailType
    */
    function getContactMainEmail(Contact $contact, $type_id) {
      	$contact_email = self::findOne(array('conditions' => array("`is_main` = 1 AND `contact_id` = ? AND `email_type_id` = ? AND TRIM(email_address) <> '' ", 
    		$contact->getId(), $type_id)));
     	return $contact_email;
    } // getContactMainEmail
    
    
    /**
    * Return all contact's email addresses of the type received, that are not main
    *
    * @access public
    * @param Contact $contact, $type_id
    * @return array
    */
    function getContactEmails(Contact $contact, $type_id) {
    	$contact_emails = self::findAll(array('conditions' => array("`is_main` = 0 AND `contact_id` = ? AND `email_type_id` = ?", $contact->getId(), $type_id)));
    	return $contact_emails;
    } // getContactEmails
    
    
    
    /**
    * Clear Email values by contact
    *
    * @access public
    * @param Contact $contact
    * @return boolean
    */
    function clearByContact(Contact $contact) {
      return DB::execute('DELETE FROM ' . self::instance()->getTableName(true) . ' WHERE `contact_id` = ?', $contact->getId());
    } // clearByContact
    

    /**
    * Return all main emails
    *
    * @access public
    * @param Contact $contact, integer $type_id
    * @return array
    */
    function getContactMainEmails(Contact $contact, $type_id) {
      	$contact_emails = self::findAll(array('conditions' => array("`is_main` = 1 AND `contact_id` = ? AND `email_type_id` = ? AND TRIM(email_address) <> '' ", 
    		$contact->getId(), $type_id)));
     	return $contact_emails;
    } // getContactMainEmails
    
} // ContactEmails 

?>