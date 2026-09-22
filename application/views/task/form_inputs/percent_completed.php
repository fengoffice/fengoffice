<?php if (config_option('use_task_percent_completed')) { ?>

    <!-- Checkbox manual percent -->
	<?php echo checkbox_field(
            'task[is_manual_percent_completed]',
            $object->getIsManualPercentCompleted(), 
            array(
                'id' => $genid . '_is_manual_percent_completed',
                'onchange' => 'og.updateIsManualPercentCompleted();'
            )
    ); ?>

    <?php
        // Mostrar u ocultar el input según el checkbox
        $show_percent_completed = $object->getIsManualPercentCompleted() ? '' : 'display:none;';
    ?>

    <!-- Contenedor para percent completed -->
    <div 
         style="<?php echo $show_percent_completed; ?>" 
         id="<?php echo $genid; ?>_percent_completed_container">

        <?php echo label_tag(lang('percent completed'), $genid . '_task_percent_completed', false, null, ''); ?>

        <!-- ACA va el input NUEVO -->
        <?php
            echo input_field(
                'task[percent_completed]',
                $object->getPercentCompleted(),
                array(
                    'id'    => $genid . '_task_percent_completed',
                    'class' => 'short',
                    '' . $task_input_disabled . '' => $task_input_disabled
                )
            );
        ?>

        <div class="clear"></div>
    </div>

<?php } ?>
