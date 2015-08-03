<?php
	$object = $user;
	set_page_title(lang('update profile'));
	$genid = gen_id();
	
	$now = DateTimeValueLib::now();
	$on_submit_click = 'og.getTimezoneFromBrowser(new Date('.$now->getYear().','.($now->getMonth() - 1).','.$now->getDay().','.$now->getHour().','.$now->getMinute().','.$now->getSecond().'))';
	
	$visible_cps = CustomProperties::countVisibleCustomPropertiesByObjectType($object->getObjectTypeId());
?>
<form style="height:100%;background-color:white" class="internalForm" action="<?php echo $user->getEditProfileUrl($redirect_to) ?>" method="post">
	
<div class="adminEditProfile">
	<div class="adminHeader">
		<div class="adminHeaderUpperRow">
			<div class="adminTitle"><table style="width:535px"><tr><td>
				<?php echo lang('update profile') ?>
			</td><td style="text-align:right">
				<?php echo submit_button(lang('save changes'), 's', array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => '1100', 'onclick' => $on_submit_click)) ?>
			</td></tr></table></div>
		</div>
	

		<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>
		<?php $cps = CustomProperties::countHiddenCustomPropertiesByObjectType(Contacts::getObjectTypeId()); ?>
	
		<div style="padding-top:5px">
		<?php if (can_manage_billing(logged_user()) && isset($billing_categories) && count($billing_categories) > 0) {?>
			<a href="#" class="option" tabindex=1010 onclick="og.toggleAndBolden('<?php echo $genid ?>update_profile_billing',this)"><?php echo lang('billing') ?></a> - 
		<?php } // if ?>
		<?php foreach ($categories as $category) { ?>
			- <a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid . $category['name'] ?>', this)"><?php echo lang($category['name'])?></a>
		<?php } ?>
		<?php if ($cps > 0) { ?>
			- <a href="#" class="option <?php echo $visible_cps>0 ? 'bold' : ''?>" onclick="og.toggleAndBolden('<?php echo $genid ?>add_custom_properties_div',this)"><?php echo lang('custom properties') ?></a>
		<?php } ?> 
		</div>
	</div>
	
	
	<div class="adminSeparator"></div>
	<div class="adminMainBlock">
	<?php if(logged_user()->isAdministrator()) : ?>
	    <div class="formBlock">
	      <?php echo label_tag(lang('username'), $genid . 'profileFormUsername', true) ?>
	      <?php echo text_field('user[username]', array_var($user_data, 'username'), array('id' => $genid . 'profileFormUsername', 'tabindex' => '2000')) ?>
	    </div>
		
	<?php else: ?>
		<div>
		  <?php echo label_tag(lang('username')) ?>
		  <?php echo clean(array_var($user_data, 'username')) ?>
		  <input type="hidden" name="user[username]" value="<?php echo clean(array_var($user_data, 'username')) ?>" />
		</div>
	<?php endif; ?>
    <input type="hidden" name="user[company_id]" value="<?php echo array_var($user_data, 'company_id')?>" /> 
	
<?php if (can_manage_billing(logged_user()) && isset($billing_categories) && count($billing_categories) > 0) {?>
	<div id="<?php echo $genid ?>update_profile_billing" style="display:none">
		
		<fieldset>
			<legend><?php echo lang('billing') ?></legend>
			<?php 
				$options = array(option_tag(lang('select billing category'),0,($user->getDefaultBillingId() == 0?array('selected' => 'selected'):null)));
				foreach ($billing_categories as $category){
					$options[] = option_tag($category->getName(), $category->getId(), ($category->getId()==$user->getDefaultBillingId())?array('selected' => 'selected'):null);	
				}
				echo label_tag(lang('billing category'), null, false);
				echo select_box('user[default_billing_id]', $options, array('id' => 'userDefaultBilling'));
			?>
		</fieldset>
		
	</div>
<?php } ?>
	 
<div class="formBlock">	   
	<div id="<?php echo $genid ?>update_profile_timezone" >
		<label><?php echo lang('auto detect user timezone') ?></label>
		<div id ="<?php echo $genid?>detectTimeZone">
			<?php echo yes_no_widget('user[autodetect_time_zone]', $genid.'userFormAutoDetectTimezone', user_config_option('autodetect_time_zone', null, $user->getId()), lang('yes'), lang('no'), null, 
					array('onclick' => "og.showSelectTimezone('$genid')")) ?>
		</div>
		<div id="<?php echo $genid?>selecttzdiv" <?php if (user_config_option('autodetect_time_zone', null, $user->getId())) echo 'style="display:none"'; ?>>
			<?php echo select_timezone_widget('user_timezone', array_var($user_data, 'timezone'), array('id' => 'userFormTimezone', 'class' => 'long', 'tabindex' => '600' )) ?>
			<input id="userFormTimezoneHidden" type="hidden" name="user[timezone]" />
		</div>
	</div>
</div>
	
<?php foreach ($categories as $category) : ?>
	<div style="display:none" id="<?php echo $genid . $category['name'] ?>">
		<fieldset>
			<legend><?php echo lang($category['name'])?></legend>
			<?php echo $category['content'] ?>
		</fieldset>
	</div>
<?php endforeach; ?>
	

<?php if ($cps > 0) { ?>
	<div id='<?php echo $genid ?>add_custom_properties_div' style="<?php echo ($visible_cps > 0 ? "" : "display:none") ?>">
		<fieldset>
			<legend><?php echo lang('custom properties') ?></legend>
			<?php echo render_object_custom_properties($user, false) ?>
		</fieldset>
	</div>
<?php } ?>
	
<?php foreach ($categories as $category) { ?>
	<div style="display:none" id="<?php echo $genid . $category['name'] ?>">
		<fieldset>
			<legend><?php echo lang($category['name'])?></legend>
			<?php echo $category['content'] ?>
		</fieldset>
	</div>
<?php } ?>
	
	<?php echo submit_button(lang('save changes'),'s',array('tabindex' => '3000', 'onclick' => $on_submit_click)) ?>
	</div>
</div>
</form>
