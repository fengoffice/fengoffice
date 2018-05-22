<?php
set_page_title(lang('configuration'));
$alt = "";
?>

<div class="adminConfiguration">
	<div class="coInputHeader">

	  <div class="coInputHeaderUpperRow">
		<div class="coInputTitle">
			<?php echo lang('configuration') ?>
		</div>
	  </div>
	
	</div>
	
	<div class="coInputMainBlock adminMainBlock">
	
	<?php if (isset($config_categories) && is_array($config_categories) && count($config_categories)) { ?>
		<?php foreach ($config_categories as $config_category) { ?>
			<?php if (!$config_category->isEmpty()) { ?>
				<div class="configCategory<?php echo $alt?>" id="category_<?php echo $config_category->getName() ?>">
					<h2><a class="internalLink" href="<?php echo $config_category->getUpdateUrl() ?>"><?php echo clean($config_category->getDisplayName()) ?></a></h2>
					<?php if (trim($config_category->getDisplayDescription())) { ?>
						<div class="configCategoryDescription"><?php
							echo nl2br(clean($config_category->getDisplayDescription()));
							if ($config_category->getName() == 'mailing') {
								echo '<br /><a class="internalLink" href="'.get_url('administration', 'tool_test_email').'">'.lang('administration tool name test_mail_settings').'</a>';
							}
						?></div>
					<?php } // if ?>
				</div>
				<?php $alt = ($alt == "" ? " odd" : "")?>
			<?php } // if ?>
		<?php } // foreach ?>
	<?php } // if ?>
		
		<div class="configCategory<?php echo $alt?>" id="category_default_user_preferences">
			<h2><a class="internalLink" href="<?php echo get_url('administration', 'company') ?>"><?php echo lang('organization data') ?></a></h2>
			<div class="configCategoryDescription"><?php echo lang('config category desc organization data') ?></div>
		</div>
		
	<?php
		$more_links = array(); 
		Hook::fire("general_settings_additional_links", null, $more_links);
		foreach ($more_links as $link) {
			$alt = ($alt == "" ? " odd" : ""); 
		?>
		<div class="configCategory<?php echo $alt?>" id="category_default_user_preferences">
			<h2><a class="internalLink" href="<?php echo array_var($link, 'url') ?>"><?php echo array_var($link, 'name') ?></a></h2>
			<div class="configCategoryDescription"><?php echo array_var($link, 'desc') ?></div>
		</div>
	<?php
		}
	?>
		
		<?php $alt = ($alt == "" ? " odd" : "")?>
		<div class="configCategory<?php echo $alt?>" id="category_default_user_preferences">
			<h2><a class="internalLink" href="<?php echo get_url('administration', 'documents') ?>"><?php echo lang('config category name documents') ?></a></h2>
			<div class="configCategoryDescription"><?php echo lang('config category desc documents') ?></div>
		</div>
		
		<?php $alt = ($alt == "" ? " odd" : "")?>
		<div class="configCategory<?php echo $alt?>" id="category_default_user_preferences">
			<h2><a class="internalLink" href="<?php echo get_url('administration', 'timezones') ?>"><?php echo lang('config category name timezones') ?></a></h2>
			<div class="configCategoryDescription"><?php echo lang('config category desc timezones') ?></div>
		</div>
		
		<?php $alt = ($alt == "" ? " odd" : "")?>
		<div class="configCategory<?php echo $alt?>" id="category_default_user_preferences">
			<h2><a class="internalLink" href="<?php echo get_url('administration', 'cron_events') ?>"><?php echo lang('cron events') ?></a></h2>
			<div class="configCategoryDescription"><?php echo lang('cron events info') ?></div>
		</div>
		
		<?php $alt = ($alt == "" ? " odd" : "")?>
		<div class="configCategory<?php echo $alt?>" id="category_default_user_preferences">
			<h2><a class="internalLink" href="<?php echo get_url('config', 'default_user_preferences') ?>"><?php echo lang('default user preferences') ?></a></h2>
			<div class="configCategoryDescription"><?php echo lang('default user preferences desc') ?></div>
		</div>
	</div>
</div>