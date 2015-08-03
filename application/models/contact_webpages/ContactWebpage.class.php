<?php

  /**
  * ContactWebpage class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class ContactWebpage extends BaseContactWebpage {
  
    /**
    * Return Webpage type
    *
    * @access public
    * @param void
    * @return WebpageType
    */
    function getWebpageType() {
      return WebpageTypes::findById($this->getWebTypeId());
    } // getWebpageType
    
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
    * Edit webpage URL address
    *
    * @access public
    * @param string $URL
    * @return void
    */
    function editWebpageURL($URL) {
        	if($this->getURL() != $URL){
      		$this->setURL($URL);
      		$this->save();
    	}
    } // editWebpageURL
    
  } // ContactWebpage 

?>