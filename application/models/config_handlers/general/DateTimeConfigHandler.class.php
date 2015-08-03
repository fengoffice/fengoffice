<?php

  /**
  * Class that handles integer config values
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class DateTimeConfigHandler extends ConfigHandler {
    
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      return pick_date_widget2($control_name, $this->getValue());
    } // render
    
    /**
    * Conver $value to raw value
    *
    * @param DateTimeValue $value
    * @return null
    */
    protected function phpToRaw($value) {
      return $value instanceof DateTimeValue ? $value->toMySQL() : EMPTY_DATETIME;
    } // phpToRaw
    
    /**
    * Convert raw value to php
    *
    * @param string $value
    * @return mixed
    */
    protected function rawToPhp($value) {
      $from_value = trim($value) ? $value : EMPTY_DATETIME;
      return DateTimeValueLib::makeFromString($from_value);
    } // rawToPhp
    
  } // DateTimeConfigHandler

?>