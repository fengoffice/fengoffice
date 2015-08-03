<?php

  /**
  * EventInvitations class
  * Generated on Mon, 13 Oct 2008
  *
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  class EventInvitations extends BaseEventInvitations {    
  	function clearByUser($user) {
  		self::delete(array(
  			'`contact_id` = ?',
  			$user->getId()
  		));
  	}
        
        function findByEvent($event_id) {
                return EventInvitations::findAll(array('conditions' => array('`event_id` = ?', $event_id)));
        }
        function findSyncById($contact) {
        	return EventInvitations::findAll(array('conditions' => array('`synced` = 1 AND `contact_id` = ?',$contact)));
        }
        function findBySpecialId($special_id) {
        	return EventInvitations::findOne(array('conditions' => array('`special_id` = ?', $special_id)));
        }
        function findSyncByEvent($event_id) {
        	return EventInvitations::findAll(array('conditions' => array('`synced` = 1 AND `event_id` = '.$event_id)));
        }
  } // EventInvitations 

?>