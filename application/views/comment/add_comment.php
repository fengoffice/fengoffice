
<div class="adminConfiguration" style="height:100%;background-color:white;">
<div class="coInputHeader">
  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<?php echo $comment->isNew() ? lang('add comment') : lang('edit comment') ?>
	</div>
  </div>
</div>
  
  <div class="adminMainBlock">
  
<?php if($comment->isNew()) {
		$form_action = Comment::getAddUrl($comment_form_object);
	  } else {
	  	$form_action = $comment->getEditUrl();
	  } ?>
	<form class="internalForm" action="<?php echo $form_action ?>" method="post">

		<?php tpl_display(get_template_path('form_errors')) ?>

 		<div class="formAddCommentText">
			<?php //echo label_tag(lang('text'), 'addCommentText', true) ?>
			<?php echo textarea_field("comment[text]", array_var($comment_data, 'text'), array('class' => 'huge', 'id' => 'addCommentText')) ?>
		</div>

    	<?php echo submit_button($comment->isNew() ? lang('add comment') : lang('edit comment')) ?>
	</form>

  </div>
</div>