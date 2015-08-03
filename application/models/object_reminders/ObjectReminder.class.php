<?php

  /**
  * ObjectReminder class
  * Generated on Mon, 29 May 2006 03:51:15 +0200 by DataObject generation tool
  *
  * @author Ignacio de Soto <ignacio.desoto@fengoffice.com>
  */
  class ObjectReminder extends BaseObjectReminder {
  
    /**
    * Contact who is to be notified
    *
    * @var Contact
    */
    private $user;
    
    /**
    * Object
    *
    * @var ProjectDataObject
    */
    private $object;
    
    /**
    * Return user object
    *
    * @param void
    * @return User
    */
    function getUser() {
      if(is_null($this->user)) $this->user = Contacts::findById($this->getUserId());
      return $this->user;
    } // getUser
    
    function setUser($user) {
    	$this->setUserId($user->getId());
    }
    
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
    
    function setObject($object) {
    	$this->setObjectId($object->getId());
    }
    
  } // ObjectReminder 

?>