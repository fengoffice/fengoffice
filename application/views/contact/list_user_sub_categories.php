<?php
set_page_title(lang($title));
$alt = " odd";
?>

<div class="adminConfiguration" style="height:100%;background-color:white">
	<div class="coInputHeader">
	  <div class="coInputHeaderUpperRow">
		<div class="coInputTitle">
			<?php echo lang($title) ?>
		</div>
	  </div>
	</div>

	<?php 
	$html = '';
	Hook::fire('add_feature_to_sub_categories_list', array('config_category'=>$main_category), $html); 
	echo $html;
	?>
	
	<div class="adminMainBlock">

	<?php if(isset($config_categories) && is_array($config_categories) && count($config_categories)) { ?>
		<?php foreach($config_categories as $config_category) { ?>
				<?php 
				$sub_config_categories = ContactConfigCategories::findAll(array('conditions' => "located_under=" . $config_category->getId()));
				if(!$config_category->isEmpty() || count($sub_config_categories) > 0){
					$url = count($sub_config_categories) > 0 ? $config_category->getSubCategories() : $config_category->getUpdateUrl();
					?>
					<div class="userWsConfigCategory<?php echo $alt?>" id="user_ws_category_<?php echo $config_category->getName() ?>">
						<h2><a class="internalLink" href="<?php echo $url; ?>"><?php echo clean($config_category->getDisplayName()) ?></a></h2>
						<?php if(trim($config_category->getDisplayDescription())) { ?>
							<div class="userWsconfigCategoryDescription"><?php echo nl2br(clean($config_category->getDisplayDescription())) ?></div>
						<?php } // if ?>
					</div>
				
					<?php $alt = ($alt == "" ? " odd" : "")?>
				<?php } //if ?>
		<?php } // foreach ?>
	<?php } // if ?>
	</div>
</div>
