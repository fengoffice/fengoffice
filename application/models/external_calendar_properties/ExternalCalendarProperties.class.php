<?php
class ExternalCalendarProperties extends BaseExternalCalendarProperties {
    
    static function findByExternalCalendarId($id) {
            return ExternalCalendarProperties::instance()->findAll(array('conditions' => array('`external_calendar_id` = ?', $id)));
    }   
    
    static function findByExternalCalendarIdAndKey($external_calendar_id, $key) {
    	return ExternalCalendarProperties::instance()->findOne(array('conditions' => array('`external_calendar_id` = ? AND `key` = ?', $external_calendar_id, $key)));
    }
} 
?>
