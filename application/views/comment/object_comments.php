<?php
	$comments = $__comments_object->getComments();
	$countComments = 0;
	if (is_array($comments) && count($comments)) {
		$countComments = count($comments);
	}
	$random = rand();
?>

    <div class="commentsTitle"><?php echo lang('comments')?> </div>
<?php if ($countComments > 0) { ?>

		<div class="objectComments" id="<?php echo $random ?>objectComments" style="<?php echo $countComments > 0? '':'display:none'?>">
<?php
		if(is_array($comments) && count($comments)) {
			$counter = 0;
			foreach($comments as $comment) {
				$counter++;
				$options = array();
				if ($comment->canEdit(logged_user()) && !$__comments_object->isTrashed()) {
					if ($comment->getCreatedById() == logged_user()->getId() || can_write(logged_user(), $comment->getRelObject()->getMembers(), $comment->getObjectTypeId())) {
						$options[] = '<a class="internalLink" href="' . $comment->getEditUrl() . '">' . lang('edit') . '</a>';
					}
					if ($comment->canLinkObject(logged_user())) {
						$options[] = render_link_to_object($comment,lang('link objects'),true);
					}
				}
				if ($comment->canDelete(logged_user()) && !$__comments_object->isTrashed()) $options[] = '<a class="internalLink" href="' . $comment->getDeleteUrl() . '" onclick="return confirm(\''.escape_single_quotes(lang('confirm move to trash')).'\')">' . lang('move to trash') . '</a>';
?>
			<div class="comment <?php echo $counter % 2 ? 'even' : 'odd' ?>" id="comment<?php echo $comment->getId() ?>">
		
		<?php 	if($comment->getCreatedBy() instanceof Contact) { ?>
				<div class="commentHead">
					<table style="width:100%"><tr><td>
					<span><a class="internalLink" href="<?php echo $comment->getViewUrl() ?>" title="<?php echo lang('permalink') ?>">#<?php echo $counter ?></a>:
					</span> <?php echo lang('comment posted on by', format_datetime($comment->getUpdatedOn()), $comment->getCreatedByCardUrl(), clean($comment->getCreatedByDisplayName())) ?>
					</td>
					<td style="text-align:right">
		<?php 		if(count($options)) { ?>
					<div><?php echo implode(' | ', $options) ?></div>
		<?php 		} // if ?>
					</td></tr></table>
				</div>
		<?php 	} else { ?>
				<div class="commentHead"><span>
				<a class="internalLink" href="<?php echo $comment->getViewUrl() ?>" title="<?php echo lang('permalink') ?>">#<?php echo $counter ?></a>:
				</span> <?php echo lang('comment posted on', format_datetime($comment->getUpdatedOn())) ?>
				</div>
		<?php 	} // if ?>
		
			<?php 	
				$has_avatar = false;
				if(($comment->getCreatedBy() instanceof Contact) && ($comment->getCreatedBy()->hasPicture())) { 
						$has_avatar = true;
				}
			?>
				<div class="commentBody"  style="word-wrap: break-word;padding: 0;position: relative;<?php echo ($has_avatar)? "min-height: 65px;":"" ?>">
					<?php 	if($has_avatar) { ?>
					<div class="contact-picture-container">
						<img class="commentUserAvatar" src="<?php echo $comment->getCreatedBy()->getPictureUrl() ?>" alt="<?php echo clean($comment->getCreatedBy()->getObjectName()) ?>" />
					</div>
					<?php 	} // if ?>
					
					<div class="commentText"><?php echo escape_html_whitespace(convert_to_links(clean($comment->getText()))) ?></div>
					<div class="clear"></div>
					<?php $object_links_render = render_object_links($comment, ($comment->canEdit(logged_user()) && !$__comments_object->isTrashed()), true, false);
						if ($object_links_render != '') { 
							echo '<div>'. $object_links_render .'</div>'; 
						} 
					?>
				</div>
			</div>
	<?php } // foreach ?>
<?php 	} else { ?>
		<p><?php echo lang('no comments associated with object') ?></p>
<?php	} // if ?>
	</div>
<?php } ?>

<?php if(!$__comments_object->isTrashed()) {?>
	<?php echo render_comment_form($__comments_object) ?>
<?php } // if ?>

<script>
og.resizeCommentsText = function() {
	var container_w = $('.objectComments .comment .commentBody').width();
	$('.objectComments .comment .commentText').css('width', (container_w - 90)+'px');
}

$(function() {
	og.resizeCommentsText();
	$(window).resize(function() {
		og.resizeCommentsText();
	});
});
</script>