<?php
/**
 * ExternalCalendarUsers
 * Generado el 22/2/2012
 * 
 */
class ExternalCalendarUsers extends BaseExternalCalendarUsers {
    
    static function findByContactId() {
            return ExternalCalendarUsers::instance()->findOne(array('conditions' => array('`contact_id` = ?', logged_user()->getId())));
    }
    
    static function findByGivenContactId($value) {
    	return ExternalCalendarUsers::instance()->findOne(array('conditions' => array('`contact_id` = ?', $value)));
    }
    
    static function findByEmail($email) {
            return ExternalCalendarUsers::instance()->findOne(array('conditions' => array('`auth_user` = ? AND `contact_id` = ?', $email, logged_user()->getId())));
    }
} 
?>