<?php

  /**
  * ObjectSubscriptions, generated on Mon, 29 May 2006 03:51:15 +0200 by 
  * DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class ObjectSubscriptions extends BaseObjectSubscriptions {
  
    /**
    * Return array of users that are subscribed to this specific message
    *
    * @param ContentDataObject $object
    * @return array
    */
    static function getUsersByObject(ContentDataObject $object) {
      $users = array();
      $subscriptions = ObjectSubscriptions::findAll(array(
        'conditions' => '`object_id` = ' . DB::escape($object->getId())
      )); // findAll
      if(is_array($subscriptions)) {
        foreach($subscriptions as $subscription) {
          $user = $subscription->getUser();
          if(!$user instanceof Contact || $user->getDisabled()) continue;
          
          $users[] = $user;
        } // foreach
      } // if
      return $users;
    } // getUsersByMessage
    
    /**
    * Return array of objects that $user is subscribed to
    *
    * @param Contact $user
    * @return array
    */
    static function getObjectsByUser(Contact $user) {
      $objects = array();
      $subscriptions = ObjectSubscriptions::findAll(array(
        'conditions' => '`contact_id` = ' . DB::escape($user->getId())
      )); // findAll
      if(is_array($subscriptions)) {
        foreach($subscriptions as $subscription) {
          $object = $subscription->getObject();
          if($object instanceof ContentDataObject) $objects[] = $object;
        } // foreach
      } // if
      return $objects;
    } // getObjectsByUser
    
    /**
    * Clear subscriptions by object
    *
    * @param ContentDataObject $object
    * @return boolean
    */
    static function clearByObject(ContentDataObject $object) {
      return ObjectSubscriptions::delete(
      		'`object_id` = ' . DB::escape($object->getId())
      );
    } // clearByObject
    
    /**
    * Clear subscriptions by user
    *
    * @param Contact $user
    * @return boolean
    */
    static function clearByUser(Contact $user) {
      return ObjectSubscriptions::delete('`contact_id` = ' . DB::escape($user->getId()));
    } // clearByUser
    
    function findBySubscriptions($event,$contact = '') {
            if (!$contact instanceof Contact) $contact_id = logged_user()->getId();
            else $contact_id = $contact->getId();
            
            return ObjectSubscriptions::findOne(array('conditions' => array('`contact_id` = ? AND object_id = ?', $contact_id,$event)));
    }
    
    function findByEvent($event_id) {
            return ObjectSubscriptions::findAll(array('conditions' => array('`object_id` = ?', $event_id)));
    }
    
  } // ObjectSubscriptions 

?>