<?php

  /**
  * Abstract output
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class Output {
    
    /**
    * Print a specific message to a specific output
    *
    * @param string $message
    * @param boolean $is_error
    * @return void
    */
    abstract function printMessage($message, $is_error = false);
    
  } // Output

?>