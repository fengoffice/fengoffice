<?php $genid = gen_id();?>

<form class="internalForm" action="<?php echo Comment::getAddUrl($comment_form_object) ?>" method="post" enctype="multipart/form-data">
<?php tpl_display(get_template_path('form_errors')) ?>


  <div class="postComment contact-picture-container" id="<?php echo $genid?>_write_comment_div">
  	<img class="commentUserAvatar" src="<?php echo logged_user()->getPictureUrl()?>" alt="<?php echo logged_user()->getObjectName()?>"/>
	<div class="formAddCommentText">
    	<?php echo textarea_field("comment[text]", '', array('class' => 'long', 'id' => $genid.'addCommentText', 'placeholder' => lang('write comment'))) ?>
	</div>
	<div class="button-container" id="<?php echo $genid?>_write_comment_btn"><?php echo submit_button(lang('add comment'), 's', array('id' => 'pcs' . $genid, 'class' => 'blue')) ?></div>
	<div class="clear"></div>
  </div>
</form>
<script>
var genid = '<?php echo $genid?>';

og.resizeCommentsTextarea = function(genid) {
	var container_w = $('#'+genid+'_write_comment_div').width();
	var button_w = $('#'+genid+'_write_comment_btn').width();

	$('#'+genid+'addCommentText').css('width', (container_w - button_w - 120)+'px');
}

$(function() {
	og.resizeCommentsTextarea(genid);
	$(window).resize(function() {
		og.resizeCommentsTextarea(genid);
	});

	$('#'+genid+'addCommentText').keyup(function(){
		var max_height = 200;
		if ($(this)[0].scrollHeight > $(this)[0].clientHeight && $(this)[0].clientHeight < max_height) {
			var h = $(this)[0].scrollHeight + 10;
			if (h > max_height) {
				h = max_height;
				$(this).css('overflow-y', 'auto');
			}
			
			$(this).css('height', h +'px');
		} else {
			if ($(this)[0].clientHeight > max_height) {
				$(this).css('overflow-y', 'auto');
			}
		}
	});
});
</script>