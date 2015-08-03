<?php
require_javascript('og/modules/addMessageForm.js'); 
?>

<form id="<?php echo $genid . 'add-User-Form'?>" class="internalForm" style="height: 100%;width: 100%; overflow: auto;" action="<?php echo get_url("object","add_subscribers_from_object_view",array('object_id'=>$object->getId()))?>" method="post">
<div class="og-add-subscribers">
<?php
	if (!isset($genid)) $genid = gen_id();
	if (!is_array($subscriberIds)) $subscriberIds = array(logged_user()->getId());
	
	// build context to retrieve allowed users, exclude users and persons dimensions
	$context_tmp = active_context();
	$members = $object->getMembers();
	$context = array();
	foreach ($context_tmp as $selection) {
		$dimension = ($selection instanceof Member ? $selection->getDimension() : $selection);
		if (in_array($dimension->getCode(), array('feng_persons', 'feng_users'))) continue;
		$replace_with = null;
		foreach ($members as $member) {
			if ($dimension->getId() == $member->getDimensionId()) {
				if (is_null($replace_with)) $replace_with = array();
				$replace_with[] = $member;
			}
		}
		if (!is_null($replace_with)) {
			foreach ($replace_with as $rw) $context[] = $rw;
		}
		else $context[] = $dimension;
	}
	
	$allowed_users = allowed_users_in_context($objectTypeId, $context, ACCESS_LEVEL_READ);
	
	Hook::fire('allowed_subscribers', $object, $allowed_users);
	
	$users = array();
	foreach ($allowed_users as $u) {
		$users["u".$u->getId()] = $u;
	}
	
	// include subscribed users that now are not allowed
	foreach ($subscriberIds as $uid) {
		if (!array_key_exists($uid, $users)) {
			$cobj = Contacts::findById($uid);
			if ($cobj instanceof Contact) {
				$users["u".$uid] = $cobj;
			}
		}
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
<div id="<?php echo $genid ?>notify_companies">

<?php foreach($companyUsers as $companyId => $users) { ?>

<div id="<?php echo $companyId?>" class="company-users" <?php echo is_array($users) == true? 'style ="margin-bottom: 10px;"' : '' ?> >

	<?php if(is_array($users) && count($users)) { ?>
		<div onclick="og.subscribeCompany(this)" class="container-div company-name<?php echo $allChecked ? ' checked' : '' ?>" onmouseout="og.rollOut(this,true)" onmouseover="og.rollOver(this)">
		<?php $theCompany = Contacts::findById($companyId) ?>
			<div class="contact-picture-container" style="float:left;margin-left:10px;padding-top:3px;">
				<img class="commentUserAvatar" src="<?php echo ($theCompany instanceof Contact ? $theCompany->getPictureUrl() : get_image_url('48x48/company.png')) ?>" alt="<?php echo clean($theCompany instanceof Contact ? $theCompany->getObjectName() : '') ?>" />
			</div>
			<label style="float:left; padding-left:5px;" for="<?php echo $genid ?>notifyCompany<?php echo ($theCompany instanceof Contact ? $theCompany->getId() : "0") ?>"><?php echo ($theCompany instanceof Contact ? clean($theCompany->getFirstName()) : lang('without company')) ?></label><br/>
			<div class="clear"></div>
		</div>
		<div style="padding-left:10px;">
		<?php foreach($users as $user) { ?>
				<?php
					$checked = in_array($user->getId(), $subscriberIds);
				?>
				<div id="div<?php echo $genid ?>inviteUser<?php echo $user->getId() ?>" class="container-div <?php echo $checked==true? 'checked-user':'user-name' ?>" onmouseout="og.rollOut(this,false <?php echo $checked==true? ',true':',false' ?>)" onmouseover="og.rollOver(this)" onclick="og.checkUser(this)">
					<input id="<?php echo $genid ?>inviteUser<?php echo $user->getId()?>" type="hidden" name="<?php echo 'subscribers[user_'.$user->getId() .']' ?>" value="<?php echo $checked?'1':'0' ?>" />
					<div class="contact-picture-container" style="float:left;padding-top:3px;">
						<img class="commentUserAvatar" src="<?php echo ($user instanceof Contact ? $user->getPictureUrl() : get_image_url('default-avatar.png')) ?>" alt="<?php echo clean($user instanceof Contact ? $user->getObjectName() : '') ?>" />
					</div>
					<label for="<?php echo $genid ?>notifyUser<?php echo $user->getId() ?>" style="float:left; max-width: 120px; overflow:hidden; padding-left: 5px;">
						<span class="ico-user link-ico"><?php echo clean($user->getObjectName()) ?></span>
						<br>
						<span style="color:#888888;font-size:90%;font-weight:normal;"> <?php echo $user->getEmailAddress()  ?> </span>
					</label>
					<div class="clear"></div>
					<br/>
				</div>
			
		<?php } // foreach ?>
		<div style="clear:both;"></div>
		</div>
	<?php } // if ?>
</div>	
<?php } // foreach ?>

</div>
</div>
</form>
