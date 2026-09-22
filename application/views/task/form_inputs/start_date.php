<?php

$sd_listeners = array("change" => "function(){ og.init_rep_by_selectbox('" . $genid . "'); }");
?>
<div class="inner-row" id="<?php echo $genid?>start_date_container"> 
	<?php
	echo pick_date_widget2('task_start_date', $value, $genid, null, true, $genid . 'start_date', $sd_listeners);

	if (config_option('use_time_in_task_dates')) {
		echo pick_time_widget2('task_start_time', $object->getUseStartTime() ? $value : user_config_option('work_day_start_time'), $genid, null, null, $genid . 'start_date_time');
	}
	?>
</div>
