<?php

  /**
  *  ReadObject class
  * Generated on Wed, 26 Jul 2006 11:18:14 +0200 by DataObject generation tool
  *
  * @author Nicolas Medeiros <nicolas@iugo.com.uy>
  */
  class  ReadObject extends BaseReadObject {  	
    
    /**
    * Contact
    *
    * @var ObjectType
    */
    private $contact;    
   
    /**
    * Return object connected with this action
    *
    * @access public
    * @param void
    * @return ContentDataObject
    */
    function getObject() {
      return Objects::findObject($this->getRelObjectId());
    } // getObject
    
    
     /**
    * Return parent user
    *
    * @param void
    * @return User
    */
    function getUser() {
      if(is_null($this->contact)) {
        $this->contact = Contacts::findById($this->getContactId());
      } // if
      return $this->contact;
    } // getUser
    
    
  } //  ReadObject 

?>