<?php

$total_time = $object->getTimeEstimate();
$minutes = $total_time % 60;
$hours = ($total_time - $minutes) / 60;

$html = '<div class="time-estimate">';
$html .= lang("hours") . ':&nbsp;';
$html .= text_field("task[time_estimate_hours]", $hours, array('id' => 'ogTasksPanelATHours', 'style' => 'width:30px'));
$html .= '<span style="margin-left:10px">'. lang("minutes") .':&nbsp;</span>';
$html .= '<select name="task[time_estimate_minutes]" size="1" id="ogTasksPanelATMinutes">';
	
$minutes = ($total_time % 60);
$minuteOptions = array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55);
Hook::fire('override_minute_options', array('name' => 'minuteOptions'), $minuteOptions);

// if the task has an amount of minutes that is not present in the minuteOptions then add it
// this can happen when estimated time is calculated using some formula in a template
if (!in_array($minutes, $minuteOptions)) {
	$minuteOptions[] = $minutes;
	sort($minuteOptions, SORT_NUMERIC);
}

$options_count = count($minuteOptions);
for ($i = 0; $i < $options_count; $i++) {
	$html .= '<option value="' . $minuteOptions[$i] . '"';
	if ($minutes == $minuteOptions[$i]) $html .= ' selected="selected"';
	$html .= ">" . $minuteOptions[$i] . "</option>\n";
}
$html .= '</select></div>';

echo $html;