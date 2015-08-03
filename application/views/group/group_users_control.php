<?php
	if (!isset($genid)) $genid = gen_id(); 
?>

<div class="og-add-subscribers">
<?php
	$grouped = array();
	$allChecked = true;
	foreach($users as $user) {
		if (!in_array($user->getId(), $groupUserIds)) $allChecked = false;
		if(!isset($grouped[$user->getCompanyId()]) || !is_array($grouped[$user->getCompanyId()])) {
			$grouped[$user->getCompanyId()] = array();
		} // if
		$grouped[$user->getCompanyId()][] = $user;
	} // foreach
	$companyUsers = $grouped;
?>
<div id="<?php echo $genid ?>notify_companies">

<?php foreach($companyUsers as $companyId => $users) { ?>

<div id="<?php echo $companyId?>" class="company-users" <?php echo is_array($users) == true? 'style ="margin-bottom: 10px;"' : '' ?>>

	<?php if(is_array($users) && count($users)) { ?>
		<div onclick="og.subscribeCompany(this)"  class="container-div company-name<?php echo $allChecked ? ' checked' : ''?>" onmouseout="og.rollOut(this,true)" onmouseover="og.rollOver(this)">
		<?php $theCompany = Contacts::findById($companyId) ?>
			<div class="contact-picture-container" style="float:left;margin-left:10px;padding-top:3px;">
				<img class="commentUserAvatar" src="<?php echo ($theCompany instanceof Contact ? $theCompany->getPictureUrl() : get_image_url('48x48/company.png')) ?>" alt="<?php echo clean($theCompany instanceof Contact ? $theCompany->getObjectName() : '') ?>" />
			</div>
			<label for="<?php echo $genid ?>notifyCompany<?php echo ($theCompany instanceof Contact ? $theCompany->getId() : "0") ?>">
				<span class="ico-company link-ico"><?php echo ($theCompany instanceof Contact ? clean($theCompany->getFirstName()) : lang('without company')) ?></span>
			</label>
			<div class="clear"></div>
		</div>
		<div id="<?php echo $genid . $companyId ?>company_users">
		<?php foreach($users as $user) { 
				$checked = in_array($user->getId(), $groupUserIds);
				?>
				<div id="div<?php echo $genid ?>inviteUser<?php echo $user->getId() ?>" class="container-div <?php echo $checked==true? 'checked-user':'user-name' ?>" onmouseout="og.rollOut(this,false <?php echo $checked==true? ',true':',false' ?>)" onmouseover="og.rollOver(this)" onclick="og.checkUser(this)">
					
					<input id="<?php echo $genid ?>inviteUser<?php echo $user->getId()?>" type="hidden" name="<?php echo 'user['.$user->getId() .']' ?>" value="<?php echo $checked?'1':'0' ?>" />
					<div class="contact-picture-container" style="float:left;padding-top:3px;">
						<img class="commentUserAvatar" src="<?php echo ($user instanceof Contact ? $user->getPictureUrl() : get_image_url('default-avatar.png')) ?>" alt="<?php echo clean($user instanceof Contact ? $user->getObjectName() : '') ?>" />
					</div>
					<label for="<?php echo $genid ?>notifyUser<?php echo $user->getId() ?>" style="float:left; max-width: 140px; overflow:hidden; padding-left: 5px;">
						<span class="ico-user link-ico"><?php echo clean($user->getObjectName()) ?></span>
						<br>
						<span style="color:#888888;font-size:90%;font-weight:normal;"> <?php echo $user->getEmailAddress()  ?> </span>
					</label>
					<div class="clear"></div>
				</div>
			
		<?php } // foreach ?>
		<div style="clear:both;"></div>
		</div>
	<?php } // if ?>
</div>	
<?php } // foreach ?>

</div>
</div>