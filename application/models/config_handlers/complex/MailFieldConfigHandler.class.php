<?php

/**
  * Let user select which field wishes to use for recipients in notifications
  *
  * @author Alvaro Torterola <alvarotm01@gmail.com>
  */
class MailFieldConfigHandler extends ConfigHandler {
	
	/**
	 * Render form control
	 *
	 * @param string $control_name        	
	 * @return string
	 */
	function render($control_name) {
		$options = array ();
		
		$option_attributes = $this->getValue() == 'to' ? array ('selected' => 'selected') : null;
		$options [] = option_tag(lang('mail to'), 'to', $option_attributes);
		
		$option_attributes = $this->getValue() == 'cc' ? array ('selected' => 'selected') : null;
		$options [] = option_tag(lang('mail CC'), 'cc', $option_attributes);
		
		$option_attributes = $this->getValue() == 'bcc' ? array ('selected' => 'selected') : null;
		$options [] = option_tag(lang('mail BCC'), 'bcc', $option_attributes);
		
		return select_box($control_name, $options);
	}
	
} // MailFieldConfigHandler
