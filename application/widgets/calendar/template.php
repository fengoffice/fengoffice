<div class="widget-calendar widget dashCalendar ">
	<div style="overflow: hidden;" class="widget-header dashHeader" onclick="og.dashExpand('<?php echo $genid?>');">
		<div class="widget-title"><?php echo (isset($widget_title)) ? $widget_title : lang("upcoming events milestones and tasks");?></div>
		<div class="dash-expander ico-dash-expanded" id="<?php echo $genid; ?>expander"></div>
	</div>
	<div class="widget-body" id="<?php echo $genid; ?>_widget_body">

		<?php echo $output; ?>
	</div>
</div>

