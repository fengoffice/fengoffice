<?php

  /**
  * ContactEmail class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class ContactEmail extends BaseContactEmail {

    /**
    * Return Email type
    *
    * @access public
    * @param void
    * @return EmailType
    */
    function getEmailType() {
      return EmailTypes::findById($this->getEmailTypeId());
    } // getEmailType


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
    * Edit Email address
    *
    * @access public
    * @param string $address
    * @return void
    */
    function editEmailAddress($address) {
        if($this->getEmailAddress() != $address){
      		$this->setEmailAddress($address);
      		$this->save();
    	}
    } // editEmailAddress

      function getArrayInfo(){
      	$et = $this->getEmailType();
        return array(
            'type'=> $et ? $et->getName() : '',
            'email'=>$this->getEmailAddress()
        );
      }


    /**
     * Validate data before save
     *
     * @access public
     * @param array $errors
     * @return void
     */
    function validate($errors) {

    	if($this->validatePresenceOf('email_address')) {
    		if(!is_valid_email(trim($this->getEmailAddress()))) {
    			$errors[] = lang('invalid email address');
    		}
    	}

    }

  } // ContactEmail

?>
