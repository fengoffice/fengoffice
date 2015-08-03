<?php

  /**
  * What to do on mail drag and drop
  *
  * @version 1.0
  */
  class MailDragDropPromptConfigHandler extends ConfigHandler {
  
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      $options = array();
      
	  $option_attributes = $this->getValue() == 'classify' ? array('selected' => 'selected') : null;
      $options[] = option_tag(lang('mail drag drop classify option'), 'classify', $option_attributes);
      
      $option_attributes = $this->getValue() == 'dont' ? array('selected' => 'selected') : null;
      $options[] = option_tag(lang('mail drag drop dont option'), 'dont', $option_attributes);
      
      $option_attributes = $this->getValue() == 'prompt' ? array('selected' => 'selected') : null;
      $options[] = option_tag(lang('mail drag drop prompt option'), 'prompt', $option_attributes);
      
      return select_box($control_name, $options);
    } // render
  
  } // DragDropPromptConfigHandler

?>