<?php $genid = gen_id(); ?>
<table><tr><td style="padding-right:15px" id="<?php echo $genid?>tdstartwork">
<form class="internalForm" action="<?php echo Timeslot::getOpenUrl($timeslot_form_object) ?>" method="post" enctype="multipart/form-data">
<?php echo submit_button(lang('start work')) ?>
</form>
</td><td>

<button id="<?php echo $genid?>buttonAddWork" type="button" class="submit" 
onclick="og.render_modal_form('', {c:'time', a:'add', params: {object_id:<?php echo $timeslot_form_object->getId() ?>, contact_id:<?php echo logged_user()->getId() ?>}});"><?php 
echo lang('add work') ?></button>

</td></tr></table>

