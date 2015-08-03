

<div class="widget-comments widget dashComments">

	<div style="overflow: hidden;" class="widget-header dashHeader" onclick="og.dashExpand('<?php echo $genid?>');">
		<div class="widget-title"><?php echo (isset($widget_title)) ? $widget_title : lang("latest comments");?></div>
		<div class="dash-expander ico-dash-expanded" id="<?php echo $genid; ?>expander"></div>
	</div>
	
	<div class="widget-body" id="<?php echo $genid; ?>_widget_body">
		<ul>
			<?php
			$count = 0;
			$style = '';
			$row_cls = "";
			foreach ($comments as $k => $comment): /* @var $comment Comment */
				$crumbOptions = json_encode($comment->getMembersToDisplayPath());
				if($crumbOptions == ""){
					$crumbOptions = "{}";
				}
				$crumbJs = " og.getEmptyCrumbHtml($crumbOptions, '.comment-row' ) ";
				if ($count >= 5) $style = 'display:none;';
			?>
				<li id="<?php echo "comment-".$comment->getId()?>" class="comment-row ico-comment <?php echo $row_cls ?>" style="<?php echo $style;?>">
					<a href="<?php echo $comment->getViewUrl() ?>" title="<?php echo lang('comment posted on by linktitle', format_datetime($comment->getCreatedOn()), clean($comment->getCreatedByDisplayName())) ?>">
						<span class="comment-title"><?php echo clean($comment->getObjectName());?></span>
						<span class="previewText"><?php echo clean($comment->getText());?></span>
					</a>
					<br/>
					<span class="breadcrumb"></span>
					<script>
						var crumbHtml = <?php echo $crumbJs?> ;
						$("#comment-<?php echo $comment->getId()?> .breadcrumb").html(crumbHtml);
					</script>
					<?php if ($comment->getUpdatedBy() instanceof Contact) { ?>
					<div class="desc date-container"><?php 
						echo lang('by').' '.lang('user date', $comment->getUpdatedBy()->getCardUserUrl(), clean($comment->getUpdatedByDisplayName()), lcfirst(friendly_date($comment->getUpdatedOn())), clean($comment->getUpdatedByDisplayName()));
					?></div>
					<?php } ?>
				</li>
				<?php 
				$count++;
				?>
			<?php endforeach; ?>
		</ul>
		<?php if ($count > 5) { ?>
		<div style="text-align:right;"><a id='showlnk-comments' href="#" onclick="og.showHideWidgetMoreLink('.comment-row.ico-comment','-comments',true)"><?php echo lang("show more") ?></div>
		<?php }?>
		<div class="x-clear"></div>
		<div class="progress-mask"></div>
	</div>
	
</div>

<script>
$(function() {
	// og.eventManager.fireEvent('replace all empty breadcrumb', null);
}); 
</script>
