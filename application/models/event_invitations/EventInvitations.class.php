<?php

  /**
  * EventInvitations class
  * Generated on Mon, 13 Oct 2008
  *
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  class EventInvitations extends BaseEventInvitations {    

	const EVENT_INVITATION_NEEDS_ACTION = 0;
	const EVENT_INVITATION_ACCEPTED = 1;
	const EVENT_INVITATION_DECLINED = 2;
	const EVENT_INVITATION_TENTATIVE = 3;

  	function clearByUser($user) {
  		self::instance()->delete(array(
  			'`contact_id` = ?',
  			$user->getId()
  		));
  	}
        
        function findByEvent($event_id) {
                return EventInvitations::instance()->findAll(array('conditions' => array('`event_id` = ?', $event_id)));
        }
        function findByEventAndContact($event_id, $contact_id) {
        	return EventInvitations::instance()->findOne(array('conditions' => array('`event_id` = ? AND `contact_id` = ?', $event_id, $contact_id)));
        }
        function findSyncById($contact) {
        	return EventInvitations::instance()->findAll(array('conditions' => array('`synced` = 1 AND `contact_id` = ?',$contact)));
        }
        function findBySpecialId($special_id) {
        	return EventInvitations::instance()->findOne(array('conditions' => array('`special_id` = ?', $special_id)));
        }
        function findSyncByEvent($event_id) {
        	return EventInvitations::instance()->findAll(array('conditions' => array('`synced` = 1 AND `event_id` = '.$event_id)));
        }
  } // EventInvitations 

?>