<div class="adminConfiguration" style="height:100%;background-color:white">
<form class="internalForm" action="<?php echo $category->getUpdateUrl() ?>" method="post" onreset="return confirm('<?php echo escape_single_quotes(lang('confirm reset form')) ?>')">
	<div class="coInputHeader">

	  <div class="coInputHeaderUpperRow"></div>
	  <div>
		<div class="coInputName">
			<div class="coInputTitle"><?php echo $category->getDisplayName() ?></div>
		</div>
		<div class="coInputButtons">
			<?php echo submit_button(lang('save'), 's', array('style' => 'margin-top:0px;')) ?>&nbsp;<button class="submit" type="reset"><?php echo lang('reset') ?></button>
		</div>
		<div class="clear"></div>
	  </div>
	</div>

	<div class="adminMainBlock">

	<?php if(isset($options) && is_array($options) && count($options)) { ?>
			<div id="configCategoryOptions">
			<?php $counter = 0; ?>
			<?php foreach($options as $option) { ?>
				<?php $counter++; ?>
				<div class="configCategoryOtpion " style="<?php echo $counter % 2 ? 'background-color:#F4F8F9' : '' ?>" id="configCategoryOption_<?php echo $option->getName() ?>">
					<div class="configOptionInfo">
						<div class="configOptionLabel"><label><?php echo $option->getDisplayName() ?>:</label></div>
					<?php if(trim($option_description = $option->getDisplayDescription())) { ?>
						<div class="configOptionDescription desc"><?php echo $option_description ?></div>
					<?php } // if ?>
					</div>
					<div class="configOptionControl">
					<?php
					if($option->getName() == "reminders_events" || $option->getName() == "reminders_tasks"){
						echo render_add_reminders_config($option->getName());
					}else{
						echo $option->render('options[' . $option->getName() . ']');
					}
					?>
					</div>
					<div class="clear"></div>
				</div>
			<?php } // foreach ?>
  			</div>
			<?php echo submit_button(lang('save')) ?>&nbsp;<button class="submit" type="reset"><?php echo lang('reset') ?></button>
	<?php } else { ?>
		<p><?php echo lang('config category is empty') ?></p>
	<?php } // if ?>
	</div>
</form>
</div>
