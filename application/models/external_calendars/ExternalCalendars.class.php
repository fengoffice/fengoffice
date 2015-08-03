<?php
/**
 * ExternalCalendars
 * Generado el 22/2/2012
 * 
 */
class ExternalCalendars extends BaseExternalCalendars {
    
    function findByExtCalUserId($user, $not_calendar_feng = false) {
    	if($not_calendar_feng){
    		return ExternalCalendars::findAll(array('conditions' => array('`ext_cal_user_id` = ? AND `calendar_feng` = 0', $user)));
    	}else{
    		return ExternalCalendars::findAll(array('conditions' => array('`ext_cal_user_id` = ?', $user)));
    	}            
    }
    
    function findFengCalendarByExtCalUserIdValue($user) {
    	return ExternalCalendars::findOne(array('conditions' => array('`ext_cal_user_id` = '.$user.' AND `calendar_feng` = 1')));
    }
    
    function findByExtCalUserIdValue($user) {
    	return ExternalCalendars::findOne(array('conditions' => array('`ext_cal_user_id` = '.$user.' AND `calendar_feng` = 1')));
    }
} 
?>