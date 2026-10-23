<?php
$timeslot = $object;

// Check if we're in fallback rendering context
$is_fallback_rendering = isset($is_hook_rendering) && $is_hook_rendering === false;

$tz_offset = Timezones::getTimezoneOffsetToApply($timeslot);
if ($timeslot->isNew()) {
    $date = DateTimeValueLib::now();
    $date->add('s', $tz_offset);
} else {
    $date = new DateTimeValue($timeslot->getStartTime()->getTimestamp() + $tz_offset);
}

// Common input elements (avoid duplication)
ob_start();
?>
<div class="inner-row" id="<?php echo $genid?>start_time_container"> 
    <?php
    $listeners = array('change' => "function(){ og.onchangeStartDate(); }");
    echo pick_date_widget2('timeslot[date]', $date, $genid, null, false,'date_input', $listeners);

    $listeners = array('change' => "function(){ og.onchangeStartDate(); }");
    echo pick_time_widget2('timeslot[start_time]', $date, $genid,null,false,'start_time_input', $listeners);
    ?>
</div>
<?php
$input_elements = ob_get_clean();

if ($is_fallback_rendering) {
    // Fallback rendering: render with label and input wrappers for use with form-group > row structure
?>
    <label><?php echo lang('start') ?></label>
    <?php echo $input_elements; ?>
<?php
} else {
    // Hook-based rendering: only render the input content (label is already provided by hook)
    echo $input_elements;
}
?>