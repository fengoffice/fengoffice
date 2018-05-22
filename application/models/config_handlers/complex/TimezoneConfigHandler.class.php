<?php

  /**
  * Timezone Selector
  *
  * @version 1.0
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  class TimezoneConfigHandler extends ConfigHandler {
  
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      
      return timezone_selector($control_name, $this->getValue());
      
    } // render
  
  } // SearchEngineConfigHandler

?>