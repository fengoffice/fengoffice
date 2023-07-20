<?php
	/* @var $timeslot Timeslot */
	$genid = gen_id();
	$object = $timeslot;

?>

<form onsubmit="og.submit_modal_form('<?php echo $genid ?>submit-close-timer-form'); og.eventManager.fireEvent('reload current panel'); return false;" id="<?php echo $genid ?>submit-close-timer-form" action="<?php echo get_url('timeslot', 'close', array('id' => $object->getId())); ?>" method="post" class="internalForm" style='min-width: 400px;' enctype="multipart/form-data">
    <div class="modal-container" style="background-color: white;padding: 10px;">      
        <div>
            <?php echo label_tag(lang('description')) ?>
            <?php echo textarea_field('timeslot[description]', $object->getDescription(), array('class' => 'short', 'cols' => '40', 'rows' => '10'))?>

        </div>

        <?php 
            echo submit_button(lang('save changes')); 
        ?>

    </div>

</form>
