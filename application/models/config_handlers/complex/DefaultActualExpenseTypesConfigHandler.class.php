<?php

  /**
  * Let user select where he wants to store uploaded files
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class DefaultActualExpenseTypesConfigHandler extends ConfigHandler {
  
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */

    function render($control_name) {

      $options = array();
      $actual_expense_types = config_option('set_actual_expense_type');
      array_unshift($actual_expense_types, "none_blank");
      $filter_actual_expense_types = array_filter($actual_expense_types);

      foreach($filter_actual_expense_types as $optionItem):
        $option_attributes = $this->getValue() == $optionItem ? array('selected' => 'selected') : null;
        $options[] = option_tag(lang($optionItem), $optionItem, $option_attributes);
      endforeach;
      
      return select_box($control_name, $options);
    } // render
  
  } // DefaultActualExpenseTypesConfigHandler

?>