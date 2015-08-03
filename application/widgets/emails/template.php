

<div class="widget-emails widget dashUnreadEmails">

	<div style="overflow: hidden;" class="widget-header dashHeader" onclick="og.dashExpand('<?php echo $genid?>');">
		<div class="widget-title"><?php echo (isset($widget_title)) ? $widget_title : lang("unread emails");?></div>
		<div class="dash-expander ico-dash-expanded" id="<?php echo $genid; ?>expander"></div>
	</div>
	
	<div class="widget-body" id="<?php echo $genid; ?>_widget_body">
		<ul>
			<?php
			$count = 0;
			$style = '';
			$row_cls = "";
			foreach ($emails as $k => $email): /* @var $email MailContent */
				$crumbOptions = json_encode($email->getMembersToDisplayPath());
				if($crumbOptions == ""){
					$crumbOptions = "{}";
				}
				$crumbJs = " og.getEmptyCrumbHtml($crumbOptions, '.email-row' ) ";
				if ($count >= 5) $style = 'display:none;';
			?>
				<li id="<?php echo "email-".$email->getId()?>" class="email-row ico-email <?php echo $row_cls ?>" style="<?php echo $style;?>">
					<a href="<?php echo $email->getViewUrl() ?>">
						<div class="bold"><?php echo clean($email->getSubject());?>: </div>
						<div class="breadcrumb"></div>
						<div style="float:left;"><?php echo clean($email->getFrom());?></div><div class="desc" style="float:right;"><?php echo friendly_date($email->getSentDate())?></div>
					</a>
					<div class="clear"></div>
					<script>
						var crumbHtml = <?php echo $crumbJs?> ;
						$("#email-<?php echo $email->getId()?> .breadcrumb").html(crumbHtml);
					</script>
				</li>
				<?php 
				$count++;
				?>
			<?php endforeach; ?>
		</ul>
		<?php if ($count > 5) { ?>
		<div style="text-align:right;"><a id='showlnk-email' href="#" onclick="og.showHideWidgetMoreLink('.email-row.ico-email','-email',true)"><?php echo lang("show more") ?></div>
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
