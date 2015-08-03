<?php
	require_javascript("og/modules/addUserForm.js");
	require_javascript("og/Permissions.js");
	$genid = gen_id();
	$object = $user;
	set_page_title($user->isNew() ? lang('add user') : lang('edit user'));
	
	$visible_cps = CustomProperties::countVisibleCustomPropertiesByObjectType($object->getObjectTypeId());
?>

<form style="height:100%;background-color:white" class="internalForm" action="<?php echo get_url('contact', 'add_user', array('contact_id' => $contact_id)); ?>" onsubmit="javascript:og.ogPermPrepareSendData('<?php echo $genid ?>');return true;" method="post">
<div class="adminAddUser">
  <div class="adminHeader">
  	<div class="adminHeaderUpperRow">
  		<div class="adminTitle"><table style="width:535px"><tr><td>
  			<?php echo $user->isNew() ? lang('new user') : lang('edit user') ?>
  		</td><td style="text-align:right">
  			<?php echo submit_button($user->isNew() ? lang('add user') : lang('save changes'), 's', array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => '1600')) ?>
  		</td></tr></table></div>
  	</div>
  	
  	<!-- username -->
    <div>
    <?php echo label_tag(lang('username'), $genid.'userFormName', true) ?>
      <?php echo text_field('user[username]', array_var($user_data, 'username'), 
    	array('class' => 'medium', 'id' => $genid.'userFormName', 'tabindex' => '100')) ?>
    </div>
    
    <!-- email -->
    <?php if ($ask_email) : ?>
    <div>
      <?php echo label_tag(lang('email address'), 'userFormEmail', true) ?>
      <?php echo text_field('user[email]', array_var($user_data, 'email'), 
    	array('class' => 'title', 'id' => 'userFormEmail', 'tabindex' => '200')) ?>
    </div>
    <?php else : ?>
    <input name="user[email]" type="hidden" value="<?php echo array_var($user_data, 'email') ?>" />
    <?php endif; ?>
  
  <script>
  <?php 
  	echo "og.roles =".json_encode($roles).";";
  	echo "og.tabs_allowed=".json_encode($tabs_allowed).";";
  ?>
  og.addUserTypeChange = function(genid, type) {
	  $('#'+genid+'userSystemPermissions :input').attr('checked', false);
	  $('#'+genid+'userModulePermissions :input').attr('checked', false);
	  for(i=0; i< og.roles[type].length;i++){
		  $('#'+genid+'userSystemPermissions :input[name$="sys_perm['+og.roles[type][i]+']"]').attr('checked', true);
	  }
	  for(f=0; f< og.tabs_allowed[type].length;f++){
		  $('#'+genid+og.tabs_allowed[type][f]+' :input').attr('checked', true);
	  }
  };
  </script>
  <!-- user type -->
  <div>
    <?php
    echo label_tag(lang('user type'), null, true);
    $permission_groups=array();
    foreach($groups as $group){
    	$permission_groups[] = array($group->getId(), lang($group->getName()));
    }
    
    echo simple_select_box('user[type]', $permission_groups, null, array('onchange' => "og.addUserTypeChange('$genid', this.value)", 'tabindex' => "300")); 
  	?>
  </div>
  
	  <?php $categories = array(); Hook::fire('object_add_categories', $object, $categories); ?>
	  <?php $cps = CustomProperties::countHiddenCustomPropertiesByObjectType(Contacts::getObjectTypeId()); ?>
	  	
	  <div style="padding-top:5px">
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_user_advanced', this)"><?php echo lang('advanced') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_user_permissions', this)"><?php echo lang('permissions') ?></a>
		<?php if ($cps > 0) { ?>
			- <a href="#" class="option <?php echo $visible_cps>0 ? 'bold' : ''?>" onclick="og.toggleAndBolden('<?php echo $genid ?>add_custom_properties_div',this)"><?php echo lang('custom properties') ?></a>
		<?php } ?>
		<?php foreach ($categories as $category) { ?>
			- <a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid . $category['name'] ?>', this)"><?php echo lang($category['name'])?></a>
		<?php } ?>
	  </div>
  </div>

  <div class="adminSeparator"></div>
  <div class="adminMainBlock">
  
  <div id="<?php echo $genid ?>add_user_advanced" style="display:none">
  	<fieldset>
  		<legend><?php echo lang('advanced')?></legend>
  		<div class="formBlock" onclick="og.showSelectTimezone('<?php echo $genid ?>')">
			<?php echo label_tag(lang('timezone'), 'userFormTimezone', false)?>
			<span class="desc"><?php echo lang('auto detect user timezone') ?></span>
			<div id ="<?php echo $genid?>detectTimeZone">
			<?php echo yes_no_widget('user[autodetect_time_zone]', 'userFormAutoDetectTimezone', user_config_option('autodetect_time_zone', null, $user->getId()), lang('yes'), lang('no')) ?>
			<div id="<?php echo $genid?>selecttzdiv" <?php if (user_config_option('autodetect_time_zone', null, $user->getId())) echo 'style="display:none"'; ?>>
			<?php echo select_timezone_widget('user[timezone]', array_var($user_data, 'timezone'), 
				array('id' => 'userFormTimezone', 'class' => 'long', 'tabindex' => '600')) ?>
			</div>
			</div>
			<script type="text/javascript">
			og.addUserTypeChange('<?php echo $genid?>',$(':input[name$="user[type]"]').val());
			
			</script>
		</div>
	</fieldset>
 </div>

  
<?php if($user->isNew() || logged_user()->isAdministrator()) { ?>
  <fieldset>
    <legend><?php echo lang('password') ?></legend>
    <div>
      <?php echo radio_field('user[password_generator]', array_var($user_data, 'password_generator') == 'random', array('value' => 'random', 'class' => 'checkbox', 'id' => 'userFormRandomPassword', 'onclick' => 'App.modules.addUserForm.generateRandomPasswordClick()', 'tabindex' => '700')) ?> <?php echo label_tag(lang('user password generate'), 'userFormRandomPassword', false, array('class' => 'checkbox'), '') ?>
    </div>
    <div>
      <?php echo radio_field('user[password_generator]', array_var($user_data, 'password_generator') == 'specify', array('value' => 'specify', 'class' => 'checkbox', 'id' => 'userFormSpecifyPassword', 'onclick' => 'App.modules.addUserForm.generateSpecifyPasswordClick()', 'tabindex' => '800')) ?> <?php echo label_tag(lang('user password specify'), 'userFormSpecifyPassword', false, array('class' => 'checkbox'), '') ?>
    </div>
    <div id="userFormPasswordInputs">
      <div>
        <?php echo label_tag(lang('password'), 'userFormPassword', true) ?>
        <?php echo password_field('user[password]', null, array('id' => 'userFormPassword', 'tabindex' => '900')) ?>
      </div>
      
      <div>
        <?php echo label_tag(lang('password again'), 'userFormPasswordA', true) ?>
        <?php echo password_field('user[password_a]', null, array('id' => 'userFormPasswordA', 'tabindex' => '1000')) ?>
      </div>
    </div>
    <div style="margin-top:10px">
    <label class="checkbox">
    <?php echo checkbox_field('user[send_email_notification]', array_var($user, 'send_email_notification', 1), array('id' => $genid . 'notif', 'tabindex' => '1050')) ?>
    <?php echo lang('send new account notification')?>
    </label>
    </div>
  </fieldset>
  <script>
	  App.modules.addUserForm.generateRandomPasswordClick();
  </script>
<?php } // if ?>

	<?php if (isset($billing_categories) && count($billing_categories) > 0) {?>
	<fieldset>
		<legend><?php echo lang('billing') ?></legend>
	<?php 
		$options = array();
		
		$options[] = option_tag(lang('none'),0,(0==$user->getDefaultBillingId())?array('selected' => 'selected'):null);
			
		foreach ($billing_categories as $category){
			$options[] = option_tag($category->getName(),$category->getId(),($category->getId()==$user->getDefaultBillingId())?array('selected' => 'selected'):null);	
		}
	    echo label_tag(lang('billing category'), null, false);
		echo select_box('user[default_billing_id]',$options,array('id' => 'userDefaultBilling'))
	?>
	</fieldset>
	<?php } //if ?>


	<div id='<?php echo $genid ?>add_custom_properties_div' style="<?php echo ($visible_cps > 0 ? "" : "display:none") ?>">
		<fieldset>
			<legend><?php echo lang('custom properties') ?></legend>
			<?php echo render_object_custom_properties($user, false) ?>
		</fieldset>
	</div>
	
	<?php foreach ($categories as $category) { ?>
	<div style="display:none" id="<?php echo $genid . $category['name'] ?>">
	<fieldset>
		<legend><?php echo lang($category['name'])?></legend>
		<?php echo $category['content'] ?>
	</fieldset>
	</div>
	<?php } ?>
	
	
	<div id="<?php echo $genid ?>add_user_permissions" style="display:none">
  <?php
  	tpl_assign('genid', $genid);
  	//tpl_assign('disable_sysperm_inputs', true);
  	$this->includeTemplate(get_template_path('system_permissions', 'account'));
  ?>
  	</div>
<?php
  echo input_field('user[contact_id]',array_var($user_data, 'contact_id',''), array('type' => 'hidden'));
  echo submit_button($user->isNew() ? lang('add user') : lang('save changes'), 's', array('tabindex' => '1500')); 
?>
  </div>
</div>
</form>

<script>
Ext.get('<?php echo $genid ?>userFormName').focus();

og.eventManager.addListener("company added", function(company) {
	var id = '<?php echo $genid.'userFormCompany' ?>';
	var select = document.getElementById('<?php echo $genid.'userFormCompany' ?>');
	if (!select) return "remove";
	var newopt = document.createElement('option');
	newopt.value = company.id;
	newopt.innerHTML = company.name;
	select.appendChild(newopt);
	select.value = company.id;
}); 
</script>
