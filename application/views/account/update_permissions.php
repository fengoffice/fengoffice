<?php
	require_javascript("og/Permissions.js");
	$genid = gen_id();
	
	set_page_title(lang('update permissions'));
?>
<form style="height:100%;background-color:white" action="<?php echo get_url("account", "update_permissions", array("id" => $user->getId())) ?>" class="internalForm" onsubmit="javascript:og.ogPermPrepareSendData('<?php echo $genid ?>');return true;" method="POST">
<div class="adminClients">
	<div class="coInputHeader">
	  <div>
		<div class="coInputName">
			<div class="coInputTitle">
				<?php echo lang("permissions for user", clean($user->getObjectName())) ?>
			</div>
		</div>
			
		<div class="coInputButtons">
			<?php echo submit_button(lang('update permissions'), 's', array('id' => $genid.'_submit_btn', 'style' => 'margin-top:0;')); ?>
		</div>
		<div class="clear"></div>
	  </div>
	</div>

  <div class="adminMainBlock">
<input name="submitted" type="hidden" value="submitted" />

<div>
<?php
	$actual_user_type = PermissionGroups::instance()->findOne(array("conditions" => "id = ".$user->getUserType()));
	$can_change_type = false;
	$permission_groups = array();
	foreach($groups as $group){
		$permission_groups[] = array($group->getId(), lang($group->getName()));
		if ($group->getId() == $actual_user_type->getId()) $can_change_type = true;
	}

	if ($can_change_type) {
		echo label_tag(lang('user type'), null, true, array('style' => 'display:inline;margin-right:15px; float:left;'));
		echo '<div id="'.$genid.'_user_type_container" style="float:left;"></div><div class="clear"><div>';
	}
	foreach ($guest_groups as $gg) {
  		if ($actual_user_type->getId() == $gg->getId()) echo '<script>og.showHideNonGuestPermissionOptions(true);</script>';
  	}
?>
</div>

<?php
tpl_assign('genid', $genid);
//tpl_assign('disable_sysperm_inputs', true);
$this->includeTemplate(get_template_path('system_permissions', 'account'));

echo submit_button(lang('update permissions'));
?>
</div>
</div>
</form>
<script>
setTimeout(function() {
	document.getElementById('<?php echo $genid.'_submit_btn'?>').focus();

	<?php if ($can_change_type) { ?>
	og.renderUserTypeSelector({container_id:"<?php echo $genid?>_user_type_container", input_name:'contact[user][type]', selected_value: <?php echo $actual_user_type->getId()?>, id:'<?php echo $genid?>_user_type_sel'});
	
	$("#<?php echo $genid?>_user_type_container select.user-type-selector").change(function(){
		og.afterUserTypeChange('<?php echo $genid?>', $(this).val());
		og.ogPermPrepareSendData('<?php echo $genid?>');
	});
	<?php } ?>
}, 500);
</script>
