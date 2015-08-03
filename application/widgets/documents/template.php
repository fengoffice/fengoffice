

<div class="widget-documents widget dashDocuments">

	<div style="overflow: hidden;" class="widget-header dashHeader" onclick="og.dashExpand('<?php echo $genid?>');">
		<div class="widget-title"><?php echo (isset($widget_title)) ? $widget_title : lang("documents");?></div>
		<div class="dash-expander ico-dash-expanded" id="<?php echo $genid; ?>expander"></div>
	</div>
	
	<div class="widget-body" id="<?php echo $genid; ?>_widget_body">
		<ul>
			<?php 
			$row_cls = "";
			foreach ($documents as $k => $document): /* @var $document ProjectFile */
				//$iconClass = $document->getIconClass();
				$iconUrl = $document->getTypeIconUrl(true, "16x16");
				$crumbOptions = json_encode($document->getMembersToDisplayPath());
				if($crumbOptions == ""){
					$crumbOptions = "{}";
				}
				$crumbJs = " og.getEmptyCrumbHtml($crumbOptions, '.document-breadcrumb-container' ) ";//document-row
			?>
				<li id="<?php echo "document-".$document->getId()?>" class="document-row co-row <?php echo $row_cls ?>" style="background: url(<?php echo $iconUrl?>) no-repeat left 4px; ">
					<a href="<?php echo $document->getViewUrl() ?>"><span class="document-title"><?php echo clean($document->getName());?></span></a>
					<div class="document-breadcrumb-container">
					<span class="breadcrumb"></span>
					</div>
					<script>
						var crumbHtml = <?php echo $crumbJs?> ;
						$("#document-<?php echo $document->getId()?> .breadcrumb").html(crumbHtml);
					</script>
					<?php if ($document->getUpdatedBy() instanceof Contact) { ?>
					<div class="desc date-container"><?php 
						echo lang('last updated by').' '.lang('user date', $document->getUpdatedBy()->getCardUserUrl(), clean($document->getUpdatedByDisplayName()), lcfirst(friendly_date($document->getUpdatedOn())), clean($document->getUpdatedByDisplayName()));
					?></div>
					<?php } ?>
				</li>
			<?php endforeach; ?>
		</ul>	
		<?php if (count($documents)<$total) :?>
		<div class="view-all-container">
			<a href="#" onclick="og.openLink(og.getUrl('files','init'), {caller:'documents-panel'})"><?php echo lang("view all") ?></a>
		</div>
		<?php endif;?>
		<div class="x-clear"></div>
		<div class="progress-mask"></div>
	</div>
	
</div>
