<?php

  /**
  * Let user select where he wants to store uploaded files
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class DefaultTypePhoneConfigHandler extends ConfigHandler {
  
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */

    function render($control_name) {

      $options = array();

      $all_phone_types = TelephoneTypes::getAllTelephoneTypesInfo();
      foreach($all_phone_types as $optionItem):
        $option_attributes = $this->getValue() == $optionItem['id'] ? array('selected' => 'selected') : null;
        $options[] = option_tag(lang($optionItem['code']), $optionItem['id'], $option_attributes);
      endforeach;
      
      return select_box($control_name, $options);
    } // render
  
  } // DefaultTypePhoneConfigHandler

?>