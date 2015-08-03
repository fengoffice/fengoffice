<?php

  /**
  * EventReminders
  *
  * @author Marcos Saiz <marcos.saiz@gmail.com>
  */
  class EventReminders extends BaseEventReminders {
    
    /**
    * Return Event Reminder object by extension
    *
    * @access public
    * @param void
    * @return EventReminder
    */
    static function getById($id) {
      return self::findOne(array(
        'conditions' => '`id` = ' . DB::escape($extension)
      )); // findOne
    } // getByExtension
  
  } // EventReminders 

?>