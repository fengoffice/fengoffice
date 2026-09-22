<?php
$timeslot = $object;

// Check if we're in fallback rendering context
$is_fallback_rendering = isset($is_hook_rendering) && $is_hook_rendering === false;

$tz_offset = Timezones::getTimezoneOffsetToApply($timeslot);
$end_date = $timeslot->isNew() || !$timeslot->getEndTime() instanceof DateTimeValue ?
    null : new DateTimeValue($timeslot->getEndTime()->getTimestamp() + $tz_offset);

// Common input elements (avoid duplication)
ob_start();
?>
<div class="inner-row" id="<?php echo $genid?>end_time_container">
    <?php
    $listeners = array('change' => "function(){ og.onchangeEndDate(); }");
    echo pick_date_widget2('timeslot[end_date]', $end_date, $genid, null, false,'date_end_input', $listeners);

    $listeners = array('change' => "function(){ og.onchangeEndDate(); }");
    echo pick_time_widget2('timeslot[end_time]', $timeslot->isNew() ? null : $end_date, $genid,null,false,'end_time_input', $listeners);
    ?>
</div>
<?php
$input_elements = ob_get_clean();

if ($is_fallback_rendering) {
    // Fallback rendering: render with label and input wrappers for use with form-group structure
?>
    <label><?php echo lang('end') ?></label>
    <?php echo $input_elements; ?>
<?php
} else {
    // Hook-based rendering: only render the input content (label is already provided by hook)
    echo $input_elements;
}
?>