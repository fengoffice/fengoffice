<?php

  /**
  * ContactWebpages
  */
  class ContactWebpages extends BaseContactWebpages {

  	
  	/**
    * Clear Web pages by contact
    *
    * @access public
    * @param Contact $contact
    * @return boolean
    */
    function clearByContact(Contact $contact) {
      return DB::execute('DELETE FROM ' . self::instance()->getTableName(true) . ' WHERE `contact_id` = ?', $contact->getId());
    } // clearByContact
    
  } // ContactWebpages 

?>