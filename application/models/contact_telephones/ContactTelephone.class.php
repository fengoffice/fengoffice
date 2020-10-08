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

    function getArrayInfo(){
      if (is_null($this->getTelephoneType())){
          return array();
      }else{
      	  $tt = $this->getTelephoneType();
          return array(
              'type'=> $tt ? $tt->getName() : '',
              'number'=>$this->getNumber()
          );
      }
    }

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
