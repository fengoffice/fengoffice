<?php
set_page_title(lang('edit preferences'));
$alt = "";
?>

<div class="adminConfiguration" style="height:100%;background-color:white">
	<div class="coInputHeader">
	  <div class="coInputHeaderUpperRow">
		<div class="coInputTitle">
			<?php echo lang('edit preferences') ?>
		</div>
	  </div>
	</div>
	
	<div class="adminMainBlock">

	<?php if(isset($config_categories) && is_array($config_categories) && count($config_categories)) { ?>
		<?php foreach($config_categories as $config_category) { ?>
			<?php 
			$sub_config_categories = ContactConfigCategories::instance()->findAll(array('conditions' => "located_under=" . $config_category->getId()));
			if(!$config_category->isEmpty() || count($sub_config_categories) > 0) { 
				$url = count($sub_config_categories) > 0 ? $config_category->getSubCategories() : $config_category->getUpdateUrl();
				?>
				<div class="userWsConfigCategory<?php echo $alt?>" id="user_ws_category_<?php echo $config_category->getName() ?>">
					<h2><a class="internalLink" href="<?php echo $url; ?>"><?php echo clean($config_category->getDisplayName()) ?></a></h2>
					<?php if(trim($config_category->getDisplayDescription())) { ?>
						<div class="userWsconfigCategoryDescription"><?php echo nl2br(clean($config_category->getDisplayDescription())) ?></div>
					<?php } // if ?>
				</div>
				<?php $alt = ($alt == "" ? " odd" : "")?>
			<?php } // if ?>
		<?php } // foreach ?>
	<?php } // if ?>
		<div class="userWsConfigCategory<?php echo $alt?>" id="user_ws_category_widgets">
			<h2><a class="internalLink" href="<?php echo get_url('contact', 'configure_widgets'); ?>"><?php echo lang('dashboard options') ?></a></h2>
		</div>
	</div>
</div>
