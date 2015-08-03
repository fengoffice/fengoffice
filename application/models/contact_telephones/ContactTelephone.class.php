<?php

  /**
  * ContactTelephone class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class ContactTelephone extends BaseContactTelephone {
  
    /**
    * Return Telephone type
    *
    * @access public
    * @param void
    * @return TelephoneType
    */
    function getTelephoneType() {
      return TelephoneTypes::findById($this->getTelephoneTypeId());
    } // getTelephoneType
    
    
    /**
    * Return contact
    *
    * @access public
    * @param void
    * @return Contact
    */
    function getContact() {
      return Contacts::findById($this->getContactId());
    } // getContact
    
    /**
    * edit phone number
    *
    * @access public
    * @param string $number
    * @return void
    */
    function editNumber($number) {
    	if($this->getNumber() != $number){
      		$this->setNumber($number);
      		$this->save();
    	}
    } // editNumber
    
  } // ContactTelephone 

?>