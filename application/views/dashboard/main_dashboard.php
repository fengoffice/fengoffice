<div class=" dashboard-container view-container">
        
	<div class="section-top">
		<div class="dashActions" style="float: right;">
			<?php $actions = array(); 
				Hook::fire('additional_dashboard_actions', null, $actions);
				foreach ($actions as $action) {
					$href = array_var($action, 'href', '#');
					$target_str = array_var($action, 'target') ? 'target="'.array_var($action, 'target').'"' : '';
					
					echo '<a href="'.$href.'" '.$target_str.' onclick="'. $action['onclick'] .'" class="dashAction '. $action['class'] .'">'. $action['name'] .'</a>';
				}
			?>
			<a class="internalLink dashAction link-ico ico-grid" href="#" onclick="og.switchToOverview(); return false;"><?php echo lang('view as list') ?></a>
			
		</div>
		<?php DashboardTools::renderSection('top'); ?>	
	</div> 
        <div class="layout-container" style="clear: both">
		
		<div class="left-column-wrapper">
			<div class="left-column section-left">
				<?php DashboardTools::renderSection('left'); ?>&nbsp;		
            </div>
		</div>
		
		<div class="right-column section-right">
			<?php DashboardTools::renderSection('right'); ?>		
		</div>
	</div>
	<div class="x-clear" ></div>
</div>

<script>
$(function() {
	og.eventManager.fireEvent('replace all empty breadcrumb', null);
});
</script>