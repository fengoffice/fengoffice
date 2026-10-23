<?php
$is_fallback_rendering = isset($is_hook_rendering) && $is_hook_rendering === false;

$current_event_type = array_var($event_data, 'typeofevent', 1);

ob_start();
?>
<input type="checkbox" name="event_type_id" <?php echo ($current_event_type == 2 ? 'checked="checked"' : '');?> 
    onchange="og.toggleDiv('<?php echo $genid?>event[start_time]'); og.toggleDiv('<?php echo $genid?>ev_duration_div'); document.getElementById('<?php echo $genid?>hf_type').value=(this.checked ? 2 : 1);" />
<input type="hidden" name="event[type_id]" id="<?php echo $genid?>hf_type" value="<?php echo $current_event_type ?>" />
<?php
$input_elements = ob_get_clean();

if ($is_fallback_rendering) {
    echo label_tag(lang('CAL_FULL_DAY'));
    echo $input_elements;
    echo '<div class="clear"></div>';
} else {
    echo $input_elements;
}
?>                