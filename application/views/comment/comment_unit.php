<div class="comment-unit">
<?php if ($comment->getCreatedBy()) { ?>
	<a class="person" href="Javascript:;" onclick="og.openLink('<?php echo $comment->getCreatedBy()->getViewUrl(); ?>')"><?php echo $comment->getCreatedBy()->getObjectName();?></a> -
<?php } ?>
	<em class="feed-date"><?php echo friendly_date($comment->getCreatedOn());?></em> 
	<p><?php echo $comment->getText(); ?></p>
</div>