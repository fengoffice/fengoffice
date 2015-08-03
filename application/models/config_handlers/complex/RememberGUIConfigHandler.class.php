<?php

  class RememberGUIConfigHandler extends ConfigHandler {
  
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      $html = yes_no_widget($control_name, 'yes_no_' . $this->getConfigOption()->getName(), $this->getValue(), lang('yes'), lang('no'));
      $html .= '<p><a target="new" href="'.get_url('gui', 'delete_state').'">' . lang('reset gui state') . '</a></p>';
      return $html;
    } // render
    
    /**
    * Conver $value to raw value
    *
    * @param mixed $value
    * @return null
    */
    protected function phpToRaw($value) {
      return $value ? '1' : '0';
    } // phpToRaw
    
    /**
    * Convert raw value to php
    *
    * @param string $value
    * @return mixed
    */
    protected function rawToPhp($value) {
      return (integer) $value ? true : false;
    } // rawToPhp
  
  } // RememberGUIConfigHandler

?>