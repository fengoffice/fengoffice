

<div class="widget-messages widget dashMessages">

	<div style="overflow: hidden;" class="widget-header dashHeader" onclick="og.dashExpand('<?php echo $genid?>');">
		<div class="widget-title"><?php echo (isset($widget_title)) ? $widget_title : lang("notes");?></div>
		<div class="dash-expander ico-dash-expanded" id="<?php echo $genid; ?>expander"></div>
	</div>
	
	<div class="widget-body" id="<?php echo $genid; ?>_widget_body">
		<ul>
			<?php 
			$row_cls = "";
			foreach ($messages as $k => $message): /* @var $message ProjectMessage */
				$crumbOptions = json_encode($message->getMembersToDisplayPath());
				if($crumbOptions == ""){
					$crumbOptions = "{}";
				}
				$crumbJs = " og.getEmptyCrumbHtml($crumbOptions, '.message-breadcrumb-container' ) ";//.message-row
			?>
				<li id="<?php echo "message-".$message->getId()?>" class="message-row ico-message <?php echo $row_cls ?>">
					<a href="<?php echo $message->getViewUrl() ?>"><span class="message-title"><?php echo clean($message->getName());?></span></a>
					<?php if (trim($message->getText()) != "") { ?>
					<span class="message-text"> - <?php echo clean(substr(html_to_text($message->getText()), 0, 100)); ?></span>
					<?php } ?>
					<?php if ($message->getUpdatedBy() instanceof Contact) { ?>
					<div class="desc date-container"><?php 
						echo lang('last updated by').' '.lang('user date', $message->getUpdatedBy()->getCardUserUrl(), clean($message->getUpdatedByDisplayName()), lcfirst(friendly_date($message->getUpdatedOn())), clean($message->getUpdatedByDisplayName()));
					?></div>
					<?php } ?>
					<div class="message-breadcrumb-container">
					<span class="breadcrumb"></span>
					</div>
					<script>
						var crumbHtml = <?php echo $crumbJs?> ;
						$("#message-<?php echo $message->getId()?> .breadcrumb").html(crumbHtml);
					</script>
					
				</li>
			<?php endforeach; ?>
		</ul>	
		<?php if (count($messages)<$total) :?>
		<div class="view-all-container">
			<a href="#" onclick="og.openLink(og.getUrl('message','init'), {caller:'messages-panel'})"><?php echo lang("view all") ?></a>
		</div>
		<?php endif;?>
		<div class="x-clear"></div>
		<div class="progress-mask"></div>
	</div>
	
</div>

<script>
$(function() {
	// og.eventManager.fireEvent('replace all empty breadcrumb', null);
});
</script>
