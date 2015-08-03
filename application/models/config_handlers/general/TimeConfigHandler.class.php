<?php

  /**
  * Class that handles integer config values
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class TimeConfigHandler extends ConfigHandler {
    
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
    	$value = DateTimeValueLib::makeFromString($this->getValue());
    	return pick_time_widget2($control_name, $value, null, null, null);
    } // render
    
  }    
?>
