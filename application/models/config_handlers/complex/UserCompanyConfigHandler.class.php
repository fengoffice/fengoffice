<?php

  /**
  * Class that handles integer config values
  *
  * @version 1.0
  * @author Carlos Palma <chonwil@gmail.com>
  */
  class UserCompanyConfigHandler extends ConfigHandler {
    
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      return filter_assigned_to_select_box($control_name, null, $this->getValue());
    } // render
    
  } // UserCompanyConfigHandler

?>