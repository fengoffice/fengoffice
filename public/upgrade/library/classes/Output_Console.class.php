<?php

  /**
  * Console output
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class Output_Console extends Output {
  
    /**
    * Print message to the console
    *
    * @param string $message
    * @param boolean $is_error
    * @return void
    */
    function printMessage($message, $is_error = false) {
      if($is_error) {
        print 'Error: ';
      } // if
      
      print "$message\n";
    } // printMessage
  
  } // Output_Console

?>