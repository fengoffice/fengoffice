<?php
/**
 * ExternalCalendars
 * Generado el 22/2/2012
 * 
 */
class ExternalCalendars extends BaseExternalCalendars {
    
    static function findByExtCalUserId($user, $not_calendar_feng = false) {
    	if($not_calendar_feng){
    		return ExternalCalendars::instance()->findAll(array('conditions' => array('`ext_cal_user_id` = ? AND `calendar_feng` = 0', $user)));
    	}else{
    		return ExternalCalendars::instance()->findAll(array('conditions' => array('`ext_cal_user_id` = ?', $user)));
    	}            
    }
    
    static function findFengCalendarByExtCalUserIdValue($user) {
    	return ExternalCalendars::instance()->findOne(array('conditions' => array('`ext_cal_user_id` = '.$user.' AND `calendar_feng` = 1')));
    }
    
    static function findByExtCalUserIdValue($user) {
    	return ExternalCalendars::instance()->findOne(array('conditions' => array('`ext_cal_user_id` = '.$user.' AND `calendar_feng` = 1')));
    }
} 
?>