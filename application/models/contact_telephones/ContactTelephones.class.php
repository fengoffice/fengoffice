<?php

  /**
  * ContactTelephones
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class ContactTelephones extends BaseContactTelephones {
  
    /**
    * Clear Contact Telephones by contact
    *
    * @access public
    * @param Contact $contact
    * @return boolean
    */
    function clearByContact(Contact $contact) {
      return DB::execute('DELETE FROM ' . self::instance()->getTableName(true) . ' WHERE `contact_id` = ?', $contact->getId());
    } // clearByContact
    
    
    
  } // ContactTelephones 

?>