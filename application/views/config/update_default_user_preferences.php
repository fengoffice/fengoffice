<?php if ($category->getName() != 'notification_trigger_options') { ?>

<div class="adminConfiguration" style="height:100%;background-color:white">
<form class="internalForm" action="<?php echo $category->getDefaultUpdateUrl() ?>" method="post" onreset="return confirm('<?php echo escape_single_quotes(lang('confirm reset form')) ?>')">
	<div class="coInputHeader">
	  <div>
		<div class="coInputName">
			<div class="coInputTitle">
				<?php echo $category->getDisplayName() ?>
			</div>
		</div>
		<div class="coInputButtons">
			<?php echo submit_button(lang('save'), 's', array('style' => 'margin-top:0px;')) ?>&nbsp;<button type="reset" class="submit"><?php echo lang('reset') ?></button>
		</div>
		<div class="clear"></div>
	  </div>
	</div>
	
	<div class="adminMainBlock">

	<?php if(isset($options) && is_array($options) && count($options)) { ?>
			<div id="configCategoryOptions">
				<?php $counter = 0; ?>
				<?php foreach($options as $option) { ?>
					<?php $option->useDefaultValue(); ?>
					<?php $counter++; ?>
					<div class="configCategoryOtpion <?php echo $counter % 2 ? 'odd' : 'even' ?>" id="configCategoryOption_<?php echo $option->getName() ?>">
						<div class="configOptionInfo">
							<div class="configOptionLabel"><label><?php echo $option->getDisplayName() ?>:</label></div>
						<?php if(trim($option_description = $option->getDisplayDescription())) { ?>
							<div class="configOptionDescription desc"><?php echo $option_description ?></div>
						<?php } // if ?>
						</div>
						<div class="configOptionControl"><?php echo $option->render('options[' . $option->getName() . ']') ?></div>
						<div class="clear"></div>
					</div>
				<?php } // foreach ?>
			</div>
			<?php echo submit_button(lang('save')) ?>&nbsp;<button type="reset" class="submit"><?php echo lang('reset') ?></button>
	<?php } else { ?>
		<p><?php echo lang('config category is empty') ?></p>
	<?php } // if ?>
	</div>
</form>
</div>


<?php } else { ?>

<div class="adminConfiguration" style="height:100%;background-color:white">
<form class="internalForm" action="<?php echo $category->getDefaultUpdateUrl() ?>" method="post" onreset="return confirm('<?php echo escape_single_quotes(lang('confirm reset form')) ?>')">
	<div class="coInputHeader">
	  <div>
		<div class="coInputName">
			<div class="coInputTitle">
				<?php echo $category->getDisplayName() ?>
			</div>
		</div>
		<div class="coInputButtons">
			<?php echo submit_button(lang('save'), 's', array('style' => 'margin-top:0px;')) ?>&nbsp;<button type="reset" class="submit"><?php echo lang('reset') ?></button>
		</div>
		<div class="clear"></div>
	  </div>
	</div>
	
	<div class="adminMainBlock">

	<?php if(isset($options) && is_array($options) && count($options)) { 
	


		$grouped_options = array(
				'none' => array(),
				'change_in_an_object' => array(),
		);
		
		foreach ($options as $opt) {
			if (in_array($opt->getName(), array('classification_changed', 'start_or_due_date_modified', 'description_changed', 'timeslot_logged', 'task_started_or_completed'))) {
				$grouped_options['change_in_an_object'][] = $opt;
			} else {
				$grouped_options['none'][] = $opt;
			}
		}
		
		?>
			<div id="configCategoryOptions">
				<?php $counter = 0; ?>
			
			<?php foreach ($grouped_options as $group_code => $g_options) { 
			
				$style = '';
				if ($group_code != 'none') {
					echo '<div class="bold" style="font-size:120%;margin-top:20px;">'.lang($group_code).'</div>';
					$style = 'style="margin-left:20px;"';
				}
				?>
				<?php foreach($g_options as $option) { ?>
					<?php $option->useDefaultValue(); ?>
					<?php $counter++; ?>
					<div class="configCategoryOtpion <?php echo $counter % 2 ? 'odd' : 'even' ?>" id="configCategoryOption_<?php echo $option->getName() ?>">
						<div class="configOptionInfo">
							<div class="configOptionLabel" <?php echo $style ?>><label><?php echo $option->getDisplayName() ?>:</label></div>
						<?php if(trim($option_description = $option->getDisplayDescription())) { ?>
							<div class="configOptionDescription desc"><?php echo $option_description ?></div>
						<?php } // if ?>
						</div>
						<div class="configOptionControl"><?php echo $option->render('options[' . $option->getName() . ']') ?></div>
						<div class="clear"></div>
					</div>
				<?php } // foreach ?>
			<?php } // foreach ?>
			</div>
			<?php echo submit_button(lang('save')) ?>&nbsp;<button type="reset" class="submit"><?php echo lang('reset') ?></button>
	<?php } else { ?>
		<p><?php echo lang('config category is empty') ?></p>
	<?php } // if ?>
	</div>
</form>
</div>


<?php } ?>