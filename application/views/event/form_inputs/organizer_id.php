<?php
$is_fallback_rendering = isset($is_hook_rendering) && $is_hook_rendering === false;

ob_start();
?>
<input type="hidden" name="event[organizer_id]" id="<?php echo $genid?>hf_organizer" value="<?php echo $event->isNew() ? logged_user()->getId() : $event->getOrganizerId() ?>" />
<div class="object-badge" id="<?php echo $genid ?>event_organizer_selector">
    <i class="icon-user"></i>
    <span id="<?php echo $genid?>task_name" class="name">
        <?php echo $event->isNew() ? logged_user()->getName() : $event->getOrganizer()->getName(); ?>
    </span>
</div>

<?php
$input_elements = ob_get_clean();

if ($is_fallback_rendering) {
?>
    <div id="<?php echo $genid ?>add_event_organizer_div">
        <?php echo label_tag(lang('organizer')) ?>
        <?php echo $input_elements; ?>
    </div>
    <div class="clear"></div>
<?php
} else {
    echo $input_elements;
}
?>