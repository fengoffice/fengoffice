<?php

  /**
  * Let user select default country for address
  *
  * @author Alex Zonis <zonis7@gmail.com>
  */
  class DefaultCountryAddressConfigHandler extends ConfigHandler {
  
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */

    function render($control_name) {
      $value = $this->getValue();
      return select_country_widget($control_name, $value);
    } // render

  } // DefaultCountryAddressConfigHandler


?>