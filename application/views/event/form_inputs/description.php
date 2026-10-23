<?php
$is_fallback_rendering = isset($is_hook_rendering) && $is_hook_rendering === false;

ob_start();
echo textarea_field('event[description]', array_var($event_data, 'description'), array('id' => 'descriptionFormText', 'rows' => '5'));
$input_elements = ob_get_clean();

if ($is_fallback_rendering) {
?>
    <div id="<?php echo $genid ?>add_event_description_div">
        <?php echo label_tag(lang('description')) ?>
        <?php echo $input_elements; ?>
    </div>
    <div class="clear"></div>
<?php
} else {
    echo $input_elements;
}
?>                