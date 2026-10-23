<?php

$dd_listeners = array("change" => "function(){ og.init_rep_by_selectbox('" . $genid . "'); }");
?>
<div class="inner-row" id="<?php echo $genid?>due_date_container"> 
	<?php
	echo pick_date_widget2('task_due_date', $value, $genid, null, true, $genid . 'due_date', $dd_listeners);

	if (config_option('use_time_in_task_dates')) {
		echo pick_time_widget2('task_due_time', $object->getUseDueTime() ? $value : user_config_option('work_day_end_time'), $genid, null, null, $genid . 'due_date_time');
	}
	?>
</div>
