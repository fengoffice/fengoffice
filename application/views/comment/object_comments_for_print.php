<?php
	$comments = $__comments_object->getComments();
	$countComments = 0;
	if (is_array($comments) && count($comments))
		$countComments = count($comments);
	$random = rand();
?>
<style>
.objectComments div.comment {
  /*margin: 8px 0;*/
  padding: 10px;
}

.objectComments .commentHead {
  color: #333;
  font-size: 12px;
}

.objectComments .commentHead a {
  font-weight: bolder;
}

.objectComments .commentBody {
  padding: 0 10px;
}

.objectComments .commentUserAvatar {
  float: left;
  margin: 10px 10px 10px 0;
}

.postComment {
  margin-top: 5px;
  margin-bottom: 3px;
  padding: 3px;
  font-size: 120%;
  font-weight: bolder;
  /*border-bottom: 1px solid #ccc;*/
}

.commentsTitle {
  margin-top: 5px;
  margin-bottom: 3px;
  padding: 3px;
  font-size: 120%;
  font-weight: bolder;
  border-bottom: 1px solid #ccc;
}
</style>
<?php if ($countComments > 0) { ?>
    <div class="commentsTitle"><?php echo lang('comments')?> </div>

		<div class="objectComments" id="<?php echo $random ?>objectComments" style="<?php echo $countComments > 0? '':'display:none'?>">
<?php
		if(is_array($comments) && count($comments)) {
			$counter = 0;
			foreach($comments as $comment) {
				$counter++;
?>
			<div class="comment <?php echo $counter % 2 ? 'even' : 'odd' ?>" id="comment<?php echo $comment->getId() ?>">
		
		<?php 	if($comment->getCreatedBy() instanceof Contact) { ?>
				<div class="commentHead">
					<table style="width:100%"><tr><td>
					<span style="font-size:130%"><b>#<?php echo $counter ?>:</b></span> <?php echo lang('comment posted on by', format_datetime($comment->getUpdatedOn()), $comment->getCreatedByCardUrl(), clean($comment->getCreatedByDisplayName())) ?>
					</td>
		</tr></table>
				</div>
		<?php 	} else { ?>
				<div class="commentHead"><span style="font-size:130%"><b>#<?php echo $counter ?>:</b></span> <?php echo lang('comment posted on', format_datetime($comment->getUpdatedOn())) ?>
				</div>
		<?php 	} // if ?>
		
				<div class="commentBody">
					<div class="commentText"><?php echo nl2br(clean($comment->getText())) ?></div>
				</div>
			</div>
		<?php } // foreach ?>
<?php } else { ?>
		<p><?php echo lang('no comments associated with object') ?></p>
<?php } // if ?>
	</div>
<?php } ?>