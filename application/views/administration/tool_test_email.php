
<div class="adminConfiguration" style="height:100%;background-color:white">

<div class="coInputHeader">

  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<?php echo clean($tool->getDisplayName()) ?>
	</div>
  </div>

</div>

<div class="adminMainBlock">
  
<form class="internalForm" action="<?php echo $tool->getToolUrl() ?>" method="post">
<?php tpl_display(get_template_path('form_errors')) ?>

  <div>
    <?php echo label_tag(lang('test mail recepient'), 'testMailFormRecipient', true) ?>
    <?php echo text_field('test_mail[recepient]', array_var($test_mail_data, 'recepient'), array('id' => 'testMailFormRecipient', 'class' => 'long')) ?>
  </div>
  
  <div>
    <?php echo label_tag(lang('test mail message'), 'testMailFormMessage', true) ?>
    <?php echo textarea_field('test_mail[message]', array_var($test_mail_data, 'message'), array('id' => 'testMailFormMessage', 'class' => 'huge')) ?>
  </div>
  
  <?php echo submit_button(lang('submit')) ?>
</form>
</div>
</div>