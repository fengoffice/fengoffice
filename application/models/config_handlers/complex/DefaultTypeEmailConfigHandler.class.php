<?php

  /**
  * Let user select where he wants to store uploaded files
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class DefaultTypeEmailConfigHandler extends ConfigHandler {
  
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */

    function render($control_name) {

      $options = array();

      $all_email_types = EmailTypes::getAllEmailTypesInfo();
      foreach($all_email_types as $optionItem):
        $option_attributes = $this->getValue() == $optionItem['id'] ? array('selected' => 'selected') : null;
        $options[] = option_tag(lang($optionItem['code']), $optionItem['id'], $option_attributes);
      endforeach;
      
      return select_box($control_name, $options);
    } // render
  
  } // DefaultTypeEmailConfigHandler

?>