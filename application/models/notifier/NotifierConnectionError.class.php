<?php

  /**
  * This exception will be thrown if Notifier fails to construct the mailer object
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class NotifierConnectionError extends Error {
  
    /**
    * Construct the NotifierConnectionError
    *
    * @access public
    * @param void
    * @return NotifierConnectionError
    */
    function __construct($message = null) {
      parent::__construct('Notifier has failed to construct mailer object');
    } // __construct
  
  } // NotifierConnectionError

?>