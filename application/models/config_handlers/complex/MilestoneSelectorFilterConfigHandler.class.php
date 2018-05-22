<?php

  /**
  * Select mail transport (mail or SMPT currently)
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class MilestoneSelectorFilterConfigHandler extends ConfigHandler {
  
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      $options = array();
      
      $option_attributes = $this->getValue() == 'current' ? array('selected' => 'selected') : null;
      $options[] = option_tag(lang('only in current member'), 'current', $option_attributes);
      
      $option_attributes = $this->getValue() == 'current_and_parents' ? array('selected' => 'selected') : null;
      $options[] = option_tag(lang('only in current member and parents'), 'current_and_parents', $option_attributes);
      
      $option_attributes = $this->getValue() == 'all' ? array('selected' => 'selected') : null;
      $options[] = option_tag(lang('all milestones'), 'all', $option_attributes);
      
      return select_box($control_name, $options);
    } // render
  
  } // MailTransportConfigHandler

?>