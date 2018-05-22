<?php 
	$genid = gen_id();
	$comments_required = config_option('file_revision_comments_required');
	
	// on submit functions
	if (array_var($_REQUEST, 'modal')) {
		$on_submit = "og.submit_modal_form('".$genid."submit-edit-form'); return false;";
	} else {
		$on_submit = "return true;";
	}
?>

<form id="<?php echo $genid ?>submit-edit-form" onsubmit="<?php echo $on_submit?>" class="internalForm" action="<?php echo $revision->getEditUrl() ?>" method="post">

<div class="coInputHeader">

  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><?php echo lang('revision comment') ?></div>
  </div>

  <div>
	<div class="coInputName"></div>
		
	<div class="coInputButtons" style="float:right; margin-right:35px;">
		
	</div>
	<div class="clear"></div>
  </div>
</div>



<div class="coInputMainBlock">

  <div id="fileRevisionComment">
    <?php echo label_tag(lang('comment'), $genid.'fileRevisionComment', $comments_required) ?>
    <?php echo textarea_field('revision[comment]', array_var($revision_data, 'comment'), array('class' => 'long', 'id' => $genid . 'fileRevisionComment', 'style' => 'width:560px;')) ?>
  </div>
  
  <div style="float:right;">
	  <?php echo submit_button(lang('save changes'),'s',array('style'=>'margin-right:2px;')) ?>
  </div>
  <div class="clear"></div>
</div>

</form>

<script>
	document.getElementById('<?php echo $genid ?>fileRevisionComment').focus();
</script>
