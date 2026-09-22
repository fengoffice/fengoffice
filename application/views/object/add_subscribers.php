<?php
require_javascript('og/modules/addMessageForm.js'); 
?>

<div class="og-add-subscribers">
<?php
	if (!is_array($subscriberIds)) $subscriberIds = array(logged_user()->getId());
	if (!isset($context)) $context = active_context();
	if (!isset($genid)) $genid = gen_id();
?>
<?php
	// get users with permissions
	$allowed_users = allowed_users_in_context($object_type_id, $context, ACCESS_LEVEL_READ);
	
	// for tasks we must also check the system permission to see if each user can see tasks assinged to other
	if ($object_type_id == ProjectTasks::instance()->getObjectTypeId() && $assigned_to > 0) {
		$allowed_users_task = array();
		foreach ($allowed_users as $allowed_user) {
			if ($assigned_to == $allowed_user->getId() || SystemPermissions::userHasSystemPermission($allowed_user, 'can_see_assigned_to_other_tasks')) {
				$allowed_users_task[] = $allowed_user;
			} 
		}
		$allowed_users = $allowed_users_task;
	}
	
	$hook_params = array('genid' => $genid, 'object_type_id' => $object_type_id, 'context' => $context, 'subscriberIds' => $subscriberIds);
	Hook::fire('filter_subscribers', $hook_params, $allowed_users);
	
	$users = array();
	foreach ($allowed_users as $u) {
		$users["u".$u->getId()] = $u;
	}
	
	$grouped = array();
	$allChecked = true;
	foreach($users as $user) {
		if (!in_array($user->getId(), $subscriberIds)) $allChecked = false;
		if(!isset($grouped[$user->getCompanyId()]) || !is_array($grouped[$user->getCompanyId()])) {
			$grouped[$user->getCompanyId()] = array();
		}
		$grouped[$user->getCompanyId()][] = $user;
	}
	$companyUsers = $grouped;
?>
<script>
	var orig_subs_input = document.getElementById("<?php echo $genid?>original_subscribers");
	if (!orig_subs_input) {
		var container = document.getElementById("<?php echo $genid ?>add_subscribers_content");
		if (container) {
			var element = document.createElement('input');
			element.setAttribute("type", "hidden");
			element.setAttribute("id", "<?php echo $genid?>original_subscribers");
			element.setAttribute("name", "original_subscribers");
			element.setAttribute("value", "<?php echo implode(",", $subscriberIds)?>");
			container.parentNode.insertBefore(element, container);
		}
	}
</script>
<div id="<?php echo $genid ?>notify_companies">

<?php 
	foreach($companyUsers as $companyId => $users) { 
		$theCompany = Contacts::instance()->findById($companyId);
	?>

<div id="<?php echo $companyId?>" class="company-users" <?php echo is_array($users) == true? 'style ="margin-bottom: 10px;"' : '' ?>>

	<?php if(is_array($users) && count($users)) { ?>
		<div onclick="og.subscribeCompany(this)" class="container-div company-name<?php echo $allChecked ? ' checked' : ''?>" onmouseout="og.rollOut(this,true)" onmouseover="og.rollOver(this)">
		<?php ?>
			<div class="contact-picture-container">
				<img class="commentUserAvatar" src="<?php echo ($theCompany instanceof Contact ? $theCompany->getPictureUrl() : get_image_url('48x48/company.png')) ?>" alt="<?php echo clean($theCompany instanceof Contact ? $theCompany->getObjectName() : '') ?>" />
			</div>
			<div class="user-info-container">
				<div class="company-name-text">
					<label for="<?php echo $genid ?>notifyCompany<?php echo ($theCompany instanceof Contact ? $theCompany->getId() : 0) ?>">
						<i class="<?php echo ($theCompany instanceof Contact ? "icon-building-2" : "")?>"></i>
						<span><?php echo ($theCompany instanceof Contact ? clean($theCompany->getFirstName()) : lang('without company')) ?></span>
					</label>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div id="<?php echo $genid . $companyId ?>company_users" class="company-users-container">
		<?php foreach($users as $user) { ?>
				<?php
					$checked = in_array($user->getId(), $subscriberIds);
				?>
				<div 
					id="div<?php echo $genid ?>inviteUser<?php echo $user->getId() ?>" 
					class="user-card container-div <?php echo $checked==true? 'checked-user':'user-name' ?>" 
					onmouseout="og.rollOut(this,false <?php echo $checked==true? ',true':',false' ?>)" 
					onmouseover="og.rollOver(this)" onclick="og.checkUser(this)"
					title="<?php echo clean($user->getObjectName()) ?>"
				>
					<input id="<?php echo $genid ?>inviteUser<?php echo $user->getId()?>" type="hidden" name="<?php echo 'subscribers[user_'.$user->getId() .']' ?>" value="<?php echo $checked?'1':'0' ?>" />
					<div class="contact-picture-container">
						<img class="commentUserAvatar" src="<?php echo ($user instanceof Contact ? $user->getPictureUrl() : get_image_url('default-avatar.png')) ?>" alt="<?php echo clean($user instanceof Contact ? $user->getObjectName() : '') ?>" />
					</div>
					<div for="<?php echo $genid ?>notifyUser<?php echo $user->getId() ?>" class="user-info-container">
						<div class="user-name-text"><?php echo clean($user->getObjectName()) ?></div>
						<div class="user-email-text"><?php echo $user->getEmailAddress(); ?></div>
					</div>
					<div class="clear"></div>
				</div>
			
		<?php } // foreach ?>
		<div style="clear:both;"></div>
		</div>
	<?php } ?>
</div>	
<?php } ?>

</div>
</div>
