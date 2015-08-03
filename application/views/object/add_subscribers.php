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
		$theCompany = Contacts::findById($companyId);
	?>

<div id="<?php echo $companyId?>" class="company-users" <?php echo is_array($users) == true? 'style ="margin-bottom: 10px;"' : '' ?>>

	<?php if(is_array($users) && count($users)) { ?>
		<div onclick="og.subscribeCompany(this)" class="container-div company-name<?php echo $allChecked ? ' checked' : ''?>" onmouseout="og.rollOut(this,true)" onmouseover="og.rollOver(this)">
		<?php ?>
			<div class="contact-picture-container" style="float:left;padding-top:3px;">
				<img class="commentUserAvatar" src="<?php echo ($theCompany instanceof Contact ? $theCompany->getPictureUrl() : get_image_url('48x48/company.png')) ?>" alt="<?php echo clean($theCompany instanceof Contact ? $theCompany->getObjectName() : '') ?>" />
			</div>
			<label style="float:left;padding-left:5px;" for="<?php echo $genid ?>notifyCompany<?php echo ($theCompany instanceof Contact ? $theCompany->getId() : 0) ?>">
				<span class="<?php echo ($theCompany instanceof Contact ? "ico-company" : "")?> link-ico"><?php echo ($theCompany instanceof Contact ? clean($theCompany->getFirstName()) : lang('without company')) ?></span>
			</label>
			<div class="clear"></div>
		</div>
		<div id="<?php echo $genid . $companyId ?>company_users">
		<?php foreach($users as $user) { ?>
				<?php
					$checked = in_array($user->getId(), $subscriberIds);
				?>
				<div id="div<?php echo $genid ?>inviteUser<?php echo $user->getId() ?>" class="container-div <?php echo $checked==true? 'checked-user':'user-name' ?>" onmouseout="og.rollOut(this,false <?php echo $checked==true? ',true':',false' ?>)" onmouseover="og.rollOver(this)" onclick="og.checkUser(this)">
					<input id="<?php echo $genid ?>inviteUser<?php echo $user->getId()?>" type="hidden" name="<?php echo 'subscribers[user_'.$user->getId() .']' ?>" value="<?php echo $checked?'1':'0' ?>" />
					<div class="contact-picture-container" style="float:left;padding-top:3px;">
						<img class="commentUserAvatar" src="<?php echo ($user instanceof Contact ? $user->getPictureUrl() : get_image_url('default-avatar.png')) ?>" alt="<?php echo clean($user instanceof Contact ? $user->getObjectName() : '') ?>" />
					</div>
					<label for="<?php echo $genid ?>notifyUser<?php echo $user->getId() ?>" style="float:left; width: 125px; min-width:0px; overflow:hidden; padding-left: 5px;>
						<span class="ico-user link-ico"><?php echo clean($user->getObjectName()) ?></span>
						<br>
						<span style="color:#888888;font-size:90%;font-weight:normal;"> <?php echo $user->getEmailAddress()  ?> </span>
					</label>
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