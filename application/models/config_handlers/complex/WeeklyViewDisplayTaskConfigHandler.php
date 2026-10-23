<?php

class WeeklyViewDisplayTaskConfigHandler extends ConfigHandler {

    function render($control_name) {

        $options = array();

        $option_attributes = $this->getValue() == 'range' ? array('selected' => 'selected') : null;
        $options[] = option_tag(
            lang('weekly view display range'),
            'range',
            $option_attributes
        );

        $option_attributes = $this->getValue() == 'due_date' ? array('selected' => 'selected') : null;
        $options[] = option_tag(
            lang('weekly view display due date'),
            'due_date',
            $option_attributes
        );

        return select_box($control_name, $options);
    }
}
