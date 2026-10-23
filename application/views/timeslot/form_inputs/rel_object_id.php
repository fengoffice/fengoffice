<?php
$timeslot = $object;

// Check if we're in fallback rendering context
$is_fallback_rendering = isset($is_hook_rendering) && $is_hook_rendering === false;

// Common input elements (avoid duplication)
ob_start();

$object_id = $timeslot->getRelObjectId();
$hasTask = (bool)$object_id;

if ($hasTask) {
    $rel_object = $timeslot->getRelObject();
    if ($rel_object) {
        $object_id = $rel_object->getObjectId();
        $object_name = $rel_object->getObjectName();
    }
}
?>
<div id="<?php echo $genid?>rel_object_container">
    <div class="linked-objects-container">
        <a id="<?php echo $genid ?>before"
            class="add-linked-object btn btn-primary-50 btn-sm"
            href="#"
            onclick="og.openObjectTaskPicker('<?php echo $genid ?>')"
            style="<?php echo $hasTask ? 'display:none;' : '' ?>">
            <i class="icon-link"></i> <?php echo lang('link task') ?>
        </a>

        <div class="selected-object-wrapper og-add-template-object" style="<?php echo $hasTask ? 'display:flex;' : 'display:none;' ?>">
            <input id="object_id" type="hidden" name="object_id" value="<?php echo $hasTask ? $object_id : '' ?>" />
            <input type="hidden" name="old_object_id" value="<?php echo $hasTask ? $object_id : '' ?>" />
            <div class="object-badge">
                <i class="icon-list-todo"></i>
                <span class="name"><?php echo $hasTask && isset($object_name) ? $object_name : "" ?></span>
                <a href="#"
                    onclick="og.removeObjectTask(this.parentNode.parentNode)"
                    class="object-remove-btn"
                    title="<?php echo lang('remove') ?>">
                    <i class="icon-circle-x"></i>
                </a>
            </div>
        </div>
    </div>
</div>
<?php
$input_elements = ob_get_clean();

if ($is_fallback_rendering) {
    // Fallback rendering: render with label and input wrappers for use with form-group structure
?>
    <label><?php echo lang('task') ?></label>
    <?php echo $input_elements; ?>
    <div class="clear"></div>
<?php
} else {
    // Hook-based rendering: only render the input content (label is already provided by hook)
    echo $input_elements;
}
?>