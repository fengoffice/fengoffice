<?php

  /**
  * What to do on drag and drop
  *
  * @version 1.0
  * @author Ignacio de Soto <ignacio.desoto@fengoffice.com>
  */
  class DragDropPromptConfigHandler extends ConfigHandler {
  
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      $options = array();
      
      $option_attributes = $this->getValue() == 'move' ? array('selected' => 'selected') : null;
      $options[] = option_tag(lang('drag drop move option'), 'move', $option_attributes);
      
      $option_attributes = $this->getValue() == 'keep' ? array('selected' => 'selected') : null;
      $options[] = option_tag(lang('drag drop keep option'), 'keep', $option_attributes);
      
      $option_attributes = $this->getValue() == 'prompt' ? array('selected' => 'selected') : null;
      $options[] = option_tag(lang('drag drop prompt option'), 'prompt', $option_attributes);
      
      return select_box($control_name, $options);
    } // render
  
  } // DragDropPromptConfigHandler

?>