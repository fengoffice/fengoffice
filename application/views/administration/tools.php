<?php
  set_page_title(lang('administration tools'));
?>

<div class="adminConfiguration" style="height:100%;background-color:white">
	<div class="coInputHeader">
	
		<div class="coInputHeaderUpperRow">
			<div class="coInputTitle">
				<?php echo lang('administration tools') ?>
			</div>
	 	</div>
		
	</div>
	
	<div class="coInputMainBlock adminMainBlock">
  
<?php if(isset($tools) && is_array($tools) && count($tools)) { ?>
<div id="administrationTools">
<?php foreach($tools as $tool) { 
		if ($tool->getVisible()) { ?>
  <div class="administrationTool">
    <div class="administrationToolName">
      <h2><a class="internalLink" href="<?php echo $tool->getToolUrl() ?>"><?php echo clean($tool->getDisplayName()) ?></a></h2>
    </div>
    <div class="administrationToolDesc"><?php echo clean($tool->getDisplayDescription()) ?></div>
  </div>
  <?php } // if ?>
<?php } // foreach ?>
</div>
<?php } else { ?>
<p><?php echo lang('no administration tools') ?></p>
<?php } // if ?>

	</div>
</div>