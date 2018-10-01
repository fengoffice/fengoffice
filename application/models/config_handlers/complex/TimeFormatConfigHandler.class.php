<?php
class TimeFormatConfigHandler extends ConfigHandler {
	
	function render($control_name) {
		
		$options = array();
		
		$option_attributes = $this->getValue() == 'friendly' ? array('selected' => 'selected') : null;
		$options[] = option_tag(lang('friendly date'), 'friendly', $option_attributes);

        $option_attributes = $this->getValue() == 'hh:mm' ? array('selected' => 'selected') : null;
        $options[] = option_tag('hh:mm', 'hh:mm', $option_attributes);

		$option_attributes = $this->getValue() == 'hours' ? array('selected' => 'selected') : null;
		$options[] = option_tag(lang('hours'), 'hours', $option_attributes);
		
		$option_attributes = $this->getValue() == 'minutes' ? array('selected' => 'selected') : null;
		$options[] = option_tag(lang('minutes'), 'minutes', $option_attributes);
		
		$option_attributes = $this->getValue() == 'seconds' ? array('selected' => 'selected') : null;
		$options[] = option_tag(lang('seconds'), 'seconds', $option_attributes);
		
		return select_box($control_name, $options);
	}
	
	
} // TimeFormatConfigHandler
