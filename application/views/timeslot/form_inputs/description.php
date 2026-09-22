<?php
// Check if we're in fallback rendering context
$is_fallback_rendering = isset($is_hook_rendering) && $is_hook_rendering === false;

$input_elements = textarea_field('timeslot[description]', $object->getDescription(), array('class' => 'form-control long'));

if ($is_fallback_rendering) {
    // Fallback rendering: render with label and input wrappers for use with form-group structure
?>
    <label><?php echo lang('description') ?></label>
    <?php echo $input_elements; ?>
<?php
} else {
    // Hook-based rendering: only render the input content (label is already provided by hook)
    echo $input_elements;
}
?>