<?php

  /**
  * ObjectSubscription class
  * Generated on Mon, 29 May 2006 03:51:15 +0200 by DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class ObjectSubscription extends BaseObjectSubscription {
  
    /**
    * User who is subscribed to this message
    *
    * @var Contact
    */
    private $user;
    
    /**
    * Object
    *
    * @var ApplicationDataObject
    */
    private $object;
    
    /**
    * Return user object
    *
    * @param void
    * @return Contact
    */
    function getUser() {
      if(is_null($this->user)) $this->user = Contacts::findById($this->getContactId());
      return $this->user;
    } // getUser
    
    /**
    * Return object
    *
    * @param void
    * @return ApplicationDataObject
    */
    function getObject() {
      if(is_null($this->object)) $this->object = Objects::findObject($this->getObjectId()); 
      return $this->object;
    } // getObject
    
  } // ObjectSubscription 

?>