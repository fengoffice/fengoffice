<?php

  /**
  * Select secure SMTP connection value
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class ShowContextHelpConfigHandler extends ConfigHandler {
  
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      $options = array();
      
      $option_attributes = $this->getValue() == 'always' ? array('selected' => 'selected') : null;
      $options[] = option_tag(lang('show context help always'), 'always', $option_attributes);
      
      $option_attributes = $this->getValue() == 'never' ? array('selected' => 'selected') : null;
      $options[] = option_tag(lang('show context help never'), 'never', $option_attributes);
      
      $option_attributes = $this->getValue() == 'until_close' ? array('selected' => 'selected') : null;
      $options[] = option_tag(lang('show context help until close'), 'until_close', $option_attributes);
      
      return select_box($control_name, $options);
    } // render
  
  } // SecureSmtpConnectionConfigHandler

?>