<?php $genid = gen_id();
	$comments_required = config_option('file_revision_comments_required');
	?>
<div id="<?php echo $genid ?>adminContainer" class="" style="height:100%;background-color:white">
  <div class="adminHeader">
  	<div class="adminTitle"><?php echo lang('revision comment') ?></div>
  </div>
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">
  
<form class="internalForm" action="<?php echo $revision->getEditUrl() ?>" method="post">
  <div id="fileRevisionComment">
    <?php echo label_tag(lang('comment'), $genid.'fileRevisionComment', $comments_required) ?>
    <?php echo textarea_field('revision[comment]', array_var($revision_data, 'comment'), array('class' => 'long', 'id' => $genid . 'fileRevisionComment', 'tabindex' => 1)) ?>
  </div>
  
  <?php echo submit_button(lang('save changes'),'s',array('tabindex' => 2)) ?>
</form>
</div></div>
<script>
	document.getElementById('<?php echo $genid ?>fileRevisionComment').focus();
</script>
