<?php
if (can_manage_time(logged_user())) {
    // Check if we're in fallback rendering context
    $is_fallback_rendering = isset($is_hook_rendering) && $is_hook_rendering === false;

    $input_elements = '
        <div id="' . $genid . 'timeslot_contact_combo_container"></div>
        <input type="hidden" id="' . $genid . 'timeslot_contact_id" name="timeslot[contact_id]" value="' . $object->getContactId() . '" />';

    if ($is_fallback_rendering) {
        // Fallback rendering: render with label and input wrappers for use with form-group structure
?>
        <label><?php echo lang('user') ?></label>
        <?php echo $input_elements; ?>
<?php
    } else {
        // Hook-based rendering: only render the input content (label is already provided by hook)
        echo $input_elements;
    }
} ?>