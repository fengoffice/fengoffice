<?php
set_page_title(lang('edit default user preferences'));
$alt = "";
?>

<div class="adminConfiguration" style="height:100%;background-color:white">
	
	<div class="coInputHeader">
	  <div>
		<div class="coInputName">
			<div class="coInputTitle">
				<?php echo lang('edit default user preferences') ?>
			</div>
		</div>
			
		<div class="clear"></div>
	  </div>
	</div>
	<div class="adminMainBlock">

	<?php if (isset($config_categories) && is_array($config_categories) && count($config_categories)) { ?>
		<?php foreach($config_categories as $config_category) { ?>
			<?php if(!$config_category->isEmpty()) { ?>
				<div class="userWsConfigCategory<?php echo $alt?>" id="user_ws_category_<?php echo $config_category->getName() ?>">
					<h2><a class="internalLink" href="<?php echo $config_category->getDefaultUpdateUrl() ?>"><?php echo clean($config_category->getDisplayName()) ?></a></h2>
					<?php if(trim($config_category->getDisplayDescription())) { ?>
						<div class="userWsconfigCategoryDescription"><?php echo nl2br(clean($config_category->getDisplayDescription())) ?></div>
					<?php } // if ?>
				</div>
			<?php } // if ?>
			<?php $alt = ($alt == "" ? " alt" : "")?>
		<?php } // foreach ?>
	<?php } // if ?>
		<div class="userWsConfigCategory<?php echo $alt?>" id="user_ws_category_widgets">
			<h2><a class="internalLink" href="<?php echo get_url('config', 'configure_widgets_default'); ?>"><?php echo lang('dashboard options') ?></a></h2>
		</div>
	</div>
</div>
