<?php
$is_fallback_rendering = isset($is_hook_rendering) && $is_hook_rendering === false;

// Property-groups (managed_event) context: object_fixed_properties includes this template to render
// the associated event's "start" fixed property inside the property-group box, passing
// $object/$value/$input_name and no $event_data. Render the start date/time + timezone using the
// exact field names the managed_events after_add hook reads, so the event schedule lives inside the
// group and saves through the existing hook. Gated on [associated] so the classic event form is
// unaffected.
if (isset($input_name) && strpos($input_name, '[associated]') !== false) {
	$assoc_base = str_replace('[start]', '', $input_name); // e.g. dim_obj[associated][event_id]
	$ev_obj = (isset($object) && $object instanceof ProjectEvent) ? $object : null;

	$start_dtv = ($value instanceof DateTimeValue) ? $value : null;
	if (!$start_dtv instanceof DateTimeValue) {
		// new event default: next full hour in the user's timezone
		$start_dtv = DateTimeValueLib::now();
		$start_dtv->setMinute(0);
		$start_dtv->setSecond(0);
		$start_dtv->add('h', 1);
		$start_dtv->advance(logged_user()->getUserTimezoneValue());
	}
	$tz_id = ($ev_obj instanceof ProjectEvent && !$ev_obj->isNew()) ? $ev_obj->getTimezoneId() : logged_user()->getUserTimezoneId();
	?>
	<div class="inner-row" style="align-items:center;">
		<?php
		echo pick_date_widget2($assoc_base.'[start_date]', $start_dtv, $genid, 120);
		echo pick_time_widget2($assoc_base.'[start_time]', $start_dtv, $genid, 130);
		?>
		<?php // toggle that reveals the timezone selector, placed next to the start time field ?>
		<a href="#" tabindex="-1" id="<?php echo $genid ?>event_tz_toggle" class="event-tz-toggle"
		   data-tooltip="<?php echo clean(lang('timezone')) ?>"
		   onclick="$('#<?php echo $genid ?>event_tz_field').toggle(); return false;"><i class="icon-globe"></i></a>
	</div>
	<?php // timezone: hidden by default, same label-above-fields layout as the start/end rows ?>
	<div id="<?php echo $genid ?>event_tz_field" style="display:none; margin-top:8px;">
		<label style="display:block; margin-bottom:4px;"><?php echo lang('timezone') ?></label>
		<div class="inner-row">
			<?php echo timezone_selector($assoc_base.'[timezone_id]', $tz_id); ?>
		</div>
	</div>
	<?php
	return;
}

$day =  array_var($event_data, 'day');
$month =  array_var($event_data, 'month');
$year =  array_var($event_data, 'year');
$use_24_hours = user_config_option('time_format_use_24');

// Date Data
$tmph = array_var($event_data, 'hour') == -1 ? 0 : array_var($event_data, 'hour');
$tmpm = array_var($event_data, 'minute') == -1 ? 0 : array_var($event_data, 'minute');
$dv_start = DateTimeValueLib::make($tmph, $tmpm, 0, $month, $day, $year);
$event->setStart($dv_start);

// Time Data
$hr = array_var($event_data, 'hour');
$minute = array_var($event_data, 'minute');
$is_pm = array_var($event_data, 'pm');
$time_val = "$hr:" . str_pad($minute, 2, '0') . ($use_24_hours ? '' : ' '.($is_pm ? 'PM' : 'AM'));

ob_start();
?>
<div class="inner-row">
    <?php 
    echo pick_date_widget2('event[start_value]', $event->getStart(), $genid, 120); 
    echo pick_time_widget2('event[start_time]', $time_val, $genid, 130);
    ?>
</div>

<?php
if (!$event->isNew() && $event->getTimezoneId() != logged_user()->getUserTimezoneId()) {
    echo timezone_selector_hidden($event, $genid);
}

$input_elements = ob_get_clean();

if ($is_fallback_rendering) {
    ?>
    <label><?php echo lang('CAL_DATE') ?></label>
    <?php echo $input_elements; ?>
    <div class="clear"></div>
<?php
} else {
    echo $input_elements;
}
?>