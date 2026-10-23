<?php
$is_fallback_rendering = isset($is_hook_rendering) && $is_hook_rendering === false;

// Property-groups (managed_event) context: render the associated event's "duration" (end datetime)
// fixed property inside the property-group box as an end date/time picker, emitting the field names
// the managed_events after_add hook reads. Gated on [associated] so the classic event form (which
// renders duration as an hours/minutes length) is unaffected.
if (isset($input_name) && strpos($input_name, '[associated]') !== false) {
	$assoc_base = str_replace('[duration]', '', $input_name); // e.g. dim_obj[associated][event_id]

	$end_dtv = ($value instanceof DateTimeValue) ? $value : null;
	if (!$end_dtv instanceof DateTimeValue) {
		// new event default: two hours from now (one hour after the start default)
		$end_dtv = DateTimeValueLib::now();
		$end_dtv->setMinute(0);
		$end_dtv->setSecond(0);
		$end_dtv->add('h', 2);
		$end_dtv->advance(logged_user()->getUserTimezoneValue());
	}
	?>
	<div class="inner-row">
		<?php
		echo pick_date_widget2($assoc_base.'[duration_date]', $end_dtv, $genid, 120);
		echo pick_time_widget2($assoc_base.'[duration_time]', $end_dtv, $genid, 130);
		?>
	</div>
	<?php
	return;
}

ob_start();
?>
<div class="inner-row" id="<?php echo $genid ?>ev_duration_div">
    <div class="inner-col">
        <div class="time-inputs">
            <select name="event_durationhour" size="1" onchange="document.getElementById('<?php echo $genid?>hf_dhour').value=this.options[this.selectedIndex].value;">
                <?php
                for($i = 0; $i < 24; $i++) {
                    echo "<option value='$i'";
                    if(array_var($event_data, 'durationhour')== $i) echo ' selected="selected"';
                    echo ">$i</option>\n";
                }    
                ?>
            </select> 
            <?php // echo lang('CAL_HOURS') ?> 
            <span class="input-separator">:</span>
            <select name="event_durationmin" size="1" onchange="document.getElementById('<?php echo $genid?>hf_dmin').value=this.options[this.selectedIndex].value;">
                <?php    
                // print out the duration minutes drop down
                $durmin = array_var($event_data, 'durationmin');
                for($i = 0; $i <= 59; $i = $i + 15) {
                    echo "<option value='$i'";
                    if ($durmin >= $i && $i > $durmin - 15) echo ' selected="selected"';
                    echo sprintf(">%02d</option>\n", $i);
                }
                ?>
            </select> 
        </div>
    </div>
    <div class="inner-col"></div>
</div>
<input type="hidden" name="event[durationhour]" id="<?php echo $genid?>hf_dhour" value="<?php echo array_var($event_data, 'durationhour') ?>" />
<input type="hidden" name="event[durationmin]" id="<?php echo $genid?>hf_dmin" value="<?php echo array_var($event_data, 'durationmin') ?>" />
<?php
$input_elements = ob_get_clean();

if ($is_fallback_rendering) {
?>
    <div class="dataBlock">
        <?php echo label_tag(lang('CAL_DURATION')) ?>
        <?php echo $input_elements; ?>
    </div>
    <div class="clear"></div>
<?php
} else {
    echo $input_elements;
}
?>