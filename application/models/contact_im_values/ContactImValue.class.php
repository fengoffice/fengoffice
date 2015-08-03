<?php

  /**
  * ContactImValue class
  * Generated on Wed, 22 Mar 2006 15:37:58 +0100 by DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class ContactImValue extends BaseContactImValue {
  
    /**
    * Return IM type
    *
    * @access public
    * @param void
    * @return ImType
    */
    function getImType() {
      return ImTypes::findById($this->getImTypeId());
    } // getImType
    
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
    
  } // ContactImValue 

?>