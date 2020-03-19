<div class=" dashboard-container view-container">

	<?php include_once 'dashboard_header.php'; ?>

	<div class="section-top">
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