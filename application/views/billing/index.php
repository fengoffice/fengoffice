<style>
.coInputConfiguration .coInputMainBlock .disabled .configCategory h2 {
	color: #777;
	cursor: pointer;
}
.coInputConfiguration .coInputMainBlock .disabled .configCategory .configCategoryDescription {
	color: #777;
	font-style: italic;
}
</style>
<div class="coInputConfiguration" style="height:100%;background-color:white">
	<div class="coInputHeader">
		<div class="adminTitle"><?php echo lang('billing and invoicing') ?></div>
	</div>
	
	<div class="coInputMainBlock">
	<?php if (!Plugins::instance()->isActivePlugin('advanced_billing')) { ?>
		<div class="configCategory">
			<h2><a class="internalLink" href="<?php echo get_url('billing', 'list_all');?>"><?php echo lang("billing categories")?></a></h2>
			<div class="configCategoryDescription"><?php echo lang("billing categories description")?></div>
			
			<div class="configCategoryDescription"><a href="<?php echo PRODUCT_URL ?>" target="_blank" style="text-decoration:underline;display:none;"><?php 
				echo lang("extend billing categories with advanced billing");
			?></a></div>
		</div>
	<?php } else {
			$ret=null; Hook::fire('render_billing_categories_admin_link', null, $ret);
		  } ?>
	<?php
		$draw_disabled_options = true;
		Hook::fire('render_additional_billing_config_options', null, $draw_disabled_options);
		
		if ($draw_disabled_options) {
	?>
		<div class="disabled">
		    <div class="configCategory odd">
	            <h2><?php echo lang("income config general")?></h2>
	            <div class="configCategoryDescription"><?php echo lang('income general config description')?></div>
	        </div>
	        <div class="configCategory">
	            <h2><?php echo lang("taxes")?></h2>
	            <div class="configCategoryDescription"><?php echo lang('taxes config description')?></div>
	        </div>
	        <div class="configCategory odd">
	            <h2><?php echo lang("invoice copies")?></h2>
	            <div class="configCategoryDescription"><?php echo lang('copies config description')?></div>
	        </div>
	        <div class="configCategory">
	            <h2><?php echo lang("invoice types")?></h2>
	            <div class="configCategoryDescription"><?php echo lang('invoice types config description')?></div>
	        </div>
	        <div class="configCategory odd">
	            <h2><?php echo lang("invoice notebooks")?></h2>
	            <div class="configCategoryDescription"><?php echo lang('invoice notebooks config description')?></div>
	        </div>
	        <div class="configCategory">
	            <h2><?php echo lang("invoice logo")?></h2>
	            <div class="configCategoryDescription"><?php echo lang("invoice logo config description")?></div>
	        </div>
		</div>
	<?php } ?>
	</div>
</div>