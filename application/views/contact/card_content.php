<?php
	$genid = gen_id();

	$contact = $object;
	$hasEmailAddrs = false;
	
	$main_email = $contact->getEmailAddress('personal');
	$personal_emails = $contact->getContactEmails('personal');
	
	$all_phones = ContactTelephones::findAll(array('conditions' => 'contact_id = '.$contact->getId()));
	$all_addresses = ContactAddresses::findAll(array('conditions' => 'contact_id = '.$contact->getId()));
	$all_webpages = ContactWebpages::findAll(array('conditions' => 'contact_id = '.$contact->getId()));
	$all_other_emails = ContactEmails::findAll(array('conditions' => 'is_main=0 AND contact_id = '.$contact->getId()));

	$all_telephone_types = TelephoneTypes::getAllTelephoneTypesInfo();
	$all_address_types = AddressTypes::getAllAddressTypesInfo();
	$all_webpage_types = WebpageTypes::getAllWebpageTypesInfo();
	$all_email_types = EmailTypes::getAllEmailTypesInfo();
	
	// types ordered
	$all_type_codes = array('work','mobile','home','personal','other','assistant','callback','pager','fax');
	$all_types_by_code = array();
	foreach ($all_type_codes as $code) {
		$t = null;
		foreach ($all_telephone_types as $type) if ($type['code'] == $code) $t = $type;
		if (!$t) {
			foreach ($all_address_types as $type) if ($type['code'] == $code) $t = $type;
		}
		if (!$t) {
			foreach ($all_webpage_types as $type) if ($type['code'] == $code) $t = $type;
		}
		if ($t) {
			$all_types_by_code[$code] = $t;
		}
	}
?>
<style>
.basic-info {
	margin: 0 0 15px 0;
}
.basic-info .alt-row {
	background-color: #E0EAEC;
}
.basic-info .info-type {
	float:left;
	width: 150px;
	font-weight:bold;
	margin-top:10px;
	padding: 2px 0;
}
.basic-info .info-content {
	float:left;
	width: 400px;
	margin-top:10px;
}
.basic-info .info-content .imAddresses {
	width: 400px;
	
}
.basic-info .info-content .info-content-item {
	padding: 2px 0;
}
</style>
<div class="person-view all-info">
<div class="commentsTitle"><?php echo lang ('contact info') ?></div>
<div class="basic-info">
<?php
$is_alt = true;
$is_alt_box = true;

if ($main_email) { ?>
	<div class="info-type" ><?php echo (count($all_other_emails) > 0 ? lang('main email') : lang('email')) ?></div>
	<div class="info-content"><?php 
		$is_alt = !$is_alt;
		echo render_mailto($main_email);
	?></div>
	<div class="clear"></div>
<?php 
}


foreach ($all_type_codes as $type_code) {
	$is_alt_box = !$is_alt_box;
	
	$tel_type = null;
	foreach ($all_telephone_types as $type) if ($type['code'] == $type_code) $tel_type = $type;
	$add_type = null;
	foreach ($all_address_types as $type) if ($type['code'] == $type_code) $add_type = $type;
	$web_type = null;
	foreach ($all_webpage_types as $type) if ($type['code'] == $type_code) $web_type = $type;
	$mail_type = null;
	foreach ($all_email_types as $type) if ($type['code'] == $type_code) $mail_type = $type;
	
	?>

	<div id="<?php echo $genid.'_title_'.$tel_type['code'] ?>" class="info-type" ><?php echo $tel_type['name'] ?></div>
	<div id="<?php echo $genid.'_content_'.$tel_type['code'] ?>" class="info-content"><?php
	$any_obj = false;
	
	if ($tel_type) {
		foreach ($all_phones as $phone) {
			if ($phone->getTelephoneTypeId() != $tel_type['id']) continue; 
			$any_obj = true;
			$is_alt = !$is_alt;
			?>
			<div class="<?php echo ($is_alt ? 'alt-row' : '')?> info-content-item">
			<?php if (in_array($tel_type['code'], array('work', 'home', 'other'))) { ?>
				<strong><?php echo lang('phone')?>: </strong>
			<?php } ?>
				<?php echo trim($phone->getNumber()) . (trim($phone->getName()) == '' ? '' : ' - '.trim($phone->getName()))?>
			</div>
	<?php }
	}

	if ($add_type) {
		foreach ($all_addresses as $address) {
			if (!$add_type || $address->getAddressTypeId() != $add_type['id']) continue;
			$any_obj = true;
			$is_alt = !$is_alt;
			
			$out = $address->getStreet();
			if($address->getCity() != '') $out .= ' - ' . $address->getCity();
			if($address->getZipCode() != '') $out .= ' - ' . $address->getZipCode();
			if($address->getState() != '') $out .= ' - ' . $address->getState();
			if($address->getCountry() != '') $out .= ' - ' . $address->getCountryName();
?>
			<div class="<?php echo ($is_alt ? 'alt-row' : '')?> info-content-item">
				<strong><?php echo lang('address')?>: </strong><?php echo $out ?>
				<a class="map-link coViewAction ico-map" href="http://maps.google.com/?q=<?php echo $out ?>" target="_blank"><?php echo lang('map')?></a>
			</div>
<?php 	}
	}
	
	if ($tel_type) {
		foreach ($all_webpages as $webpage) {
			if ($webpage->getWebTypeId() != $web_type['id']) continue;
			$any_obj = true;
			$is_alt = !$is_alt;
			?>
			<div class="<?php echo ($is_alt ? 'alt-row' : '')?> info-content-item">
				<strong><?php echo lang('webpage')?>: </strong><?php echo trim($webpage->getUrl()) ?>
			</div>
	<?php }
	}
	
	if ($mail_type) {
		foreach ($all_other_emails as $oemail) {
			if ($oemail->getEmailTypeId() != $mail_type['id']) continue;
			$any_obj = true;
			$is_alt = !$is_alt;
			?>
			<div class="<?php echo ($is_alt ? 'alt-row' : '')?> info-content-item">
				<strong><?php echo lang('email')?>: </strong><?php echo render_mailto($oemail->getEmailAddress()) ?>
			</div>
	<?php }
	}
	
	
	if (!$any_obj) { ?>
	<script>
		$('#<?php echo $genid.'_title_'.$tel_type['code'] ?>').remove();
		$('#<?php echo $genid.'_content_'.$tel_type['code'] ?>').remove();
	</script>
<?php }
?></div>
<div class="clear"></div>
<?php 
}
?>

<?php if ($contact->getBirthday()) { 
		$is_alt = !$is_alt; ?>
	<div class="info-type" ><?php echo lang('birthday') ?></div>
	<div class="info-content">
		<div class="<?php echo ($is_alt ? 'alt-row' : '')?> info-content-item"><?php 
			echo lang('month '.$contact->getBirthday()->getMonth()) . ', ' . $contact->getBirthday()->getDay(); 
		?></div>
	</div>
	<div class="clear"></div>
<?php } ?>

    
<?php if(is_array($im_values = $contact->getImValues()) && count($im_values)) { ?>
	<div class="info-type" ><?php echo lang('instant messaging') ?></div>
	<div class="info-content">
		<table class="imAddresses">
<?php 	foreach($im_values as $im_value) { ?>
<?php 		if($im_type = $im_value->getImType()) { 
				$is_alt = !$is_alt; ?>
		  <tr class="<?php echo ($is_alt ? 'alt-row' : '')?> info-content-item">
			<td><img src="<?php echo $im_type->getIconUrl() ?>" alt="<?php echo $im_type->getName() ?>" /></td>
			<td><?php echo clean($im_value->getValue()) ?> <?php if($im_value->getIsMain()) { ?><span class="desc">(<?php echo lang('primary im service') ?>)</span><?php } ?></td>
		  </tr>
<?php 		} ?>
<?php 	} ?>
		</table>
	</div>
	<div class="clear"></div>
<?php } ?>

<?php if ($contact->getCommentsField()) { 
		$is_alt = !$is_alt; ?>
	<div class="info-type" ><?php echo lang('notes') ?></div>
	<div class="info-content">
		<div class="<?php echo ($is_alt ? 'alt-row' : '')?> info-content-item"><?php 
			echo $contact->getCommentsField(); 
		?></div>
	</div>
	<div class="clear"></div>
<?php } ?>

<?php if ($contact->getUserType() > 0) { ?>

</div>
<div class="clear"></div>
<div class="commentsTitle"><?php echo lang ('user info') ?></div>
<div class="basic-info">
	<?php $is_alt = !$is_alt; ?>
	<div class="info-type" ><?php echo lang('username') ?></div>
	<div class="info-content">
		<div class="<?php echo ($is_alt ? 'alt-row' : '')?> info-content-item"><?php 
			echo $contact->getUsername(); 
		?></div>
	</div>
	<div class="clear"></div>
	
	<?php $is_alt = !$is_alt; ?>
	<div class="info-type" ><?php echo lang('user type') ?></div>
	<div class="info-content">
		<div class="<?php echo ($is_alt ? 'alt-row' : '')?> info-content-item"><?php 
			echo $contact->getUserTypeName(); 
		?></div>
	</div>
	<div class="clear"></div>

<?php } ?>

</div>
<div class="clear"></div>
<?php Hook::fire('after_contact_view', $contact, $null); ?>
</div>
<div class="clear"></div>