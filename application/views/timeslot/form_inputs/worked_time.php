<?php
$timeslot = $object;

// Check if we're in fallback rendering context
$is_fallback_rendering = isset($is_hook_rendering) && $is_hook_rendering === false;

// Common input elements (avoid duplication)
ob_start();
?>
<div class="inner-row" id="<?php echo $genid?>worked_time_container">
    <div class="inner-col">
        <!-- <span class="sub-label"><?php // echo lang('worked time') ?></span> -->
        <div class="time-inputs">
            <?php echo hour_field('timeslot[hours]', floor($timeslot->getMinutes() / 60),array("maxlength" => 4,
                        "id" => "worked_time",
                        "class" => "form-control time-field",
                        "onkeyup" => "event.target.value = event.target.value.replace(/[^0-9]/g, '')",
                        "onchange" => "og.onchangeTimesInputs(this);",
                        "placeholder" => "Hs")) ?>
            <span class="input-separator">:</span>
            <?php echo minute_field('timeslot[minutes]', $timeslot->getMinutes() % 60,array("maxlength" => 2,
                        "id" => "worked_minute",
                        "class" => "form-control time-field",
                        "onkeyup" => "event.target.value = event.target.value.replace(/[^0-9]/g, ''); if (event.target.value > 59) event.target.value = 59;",
                        "onchange" => "og.onchangeTimesInputs(this);",
                        "placeholder" => "Min")) ?>
        </div>
    </div>
    <div class="inner-col">
        <a class="link" style="<?php echo $time_preferences['show_paused_time'] && ($timeslot->getSubtract() == 0) ? '':'display:none;' ?>" onclick="og.toggle_specify_paused_time(this, '<?php echo $genid ?>')" href="#"><?php echo lang('specify paused time') ?></a>
        
        <div id="<?php echo $genid?>paused_time_container" style="<?php if ($timeslot->getSubtract() == 0) echo 'display:none;' ?>;">
            <!-- <span class="sub-label"><?php // echo lang('paused time') ?></span> -->
            <div class="time-inputs">
                <?php echo hour_field('timeslot[subtract_hours]', floor($timeslot->getSubtract() / 3600),array("maxlength" => 4,
                            "id" => "paused_time",
                            "class" => "form-control time-field",
                            "onkeyup" => "event.target.value = event.target.value.replace(/[^0-9]/g, '')",
                            "onchange" => "og.onchangeTimesInputs(this);",
                            "placeholder" => "Hs")) ?>
                <span class="input-separator">:</span>
                <?php echo minute_field('timeslot[subtract_minutes]', ($timeslot->getSubtract() / 60) % 60,array("maxlength" => 2,
                            "id" => "paused_minute",
                            "class" => "form-control time-field",
                            "onkeyup" => "event.target.value = event.target.value.replace(/[^0-9]/g, ''); if (event.target.value > 59) event.target.value = 59;",
                            "onchange" => "og.onchangeTimesInputs(this);",
                            "placeholder" => "Min")) ?>
            </div>
        </div>
    </div>
</div>
<?php
$input_elements = ob_get_clean();

if ($is_fallback_rendering) {
    // Fallback rendering: render with label and input wrappers for use with form-group > row structure
?>
    <label><?php echo lang('time') ?></label>
    <?php echo $input_elements; ?>
<?php
} else {
    // Hook-based rendering: only render the input content (label is already provided by hook)
    echo $input_elements;
}
?>