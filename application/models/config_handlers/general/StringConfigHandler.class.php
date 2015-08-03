<?php

  /**
  * String config handler represents single line string value
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class StringConfigHandler extends ConfigHandler {
  
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      return text_field($control_name, $this->getValue(), array('class' => 'middle'));
    } // render
    
  } // StringConfigHandler

?>