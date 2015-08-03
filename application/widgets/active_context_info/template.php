<div class="widget-active-context-info widget">

	<div class="widget-header" onclick="og.dashExpand('<?php echo $genid?>');">
		<div class="widget-title"><?php echo $member->getName() . ' - ' . lang('information')?></div>
		<div class="dash-expander ico-dash-expanded" id="<?php echo $genid; ?>expander"></div>
	</div>
	
	<div class="widget-body" id="<?php echo $genid; ?>_widget_body"><?php
		
		echo $prop_html . $cp_html;
		
	?></div>
</div>