<?php if(isset($company) && ($company instanceof Contact)) { ?>
	<?php echo '<div class="commentsTitle">'.lang('contact information').'</div>'?>
<div class="card">

  <div class="">
    
    <div  class="link-ico ico-email"><h2><?php echo lang('email address') ?></h2></div>
    <div class="cardBlock">
      <?php 
		$all_emails = ContactEmails::findAll(array('conditions' => 'contact_id='.$company->getId(), 'order' => 'is_main DESC'));
		foreach ($all_emails as $email) {
			if ($email->getIsMain()) { ?>
				<div><a <?php echo logged_user()->hasMailAccounts() ? 
					'href="' . get_url('mail', 'add_mail', array('to' => clean($email->getEmailAddress()))) . '"' : 
					'target="_self" href="mailto:' . clean($email->getEmailAddress()) . '"' ?>><?php 
				echo clean($email->getEmailAddress());
			?></a></div>
<?php 		} else {
				$type = $email->getEmailType();
				$type_name = $type instanceof EmailType ? '<span class="bold">'.lang($type->getName()) . ': </span>' : ''; ?>
				<div><?php echo $type_name; ?><a <?php echo logged_user()->hasMailAccounts() ? 
					'href="' . get_url('mail', 'add_mail', array('to' => clean($email->getEmailAddress()))) . '"' : 
					'target="_self" href="mailto:' . clean($email->getEmailAddress()) . '"' ?>><?php 
				echo clean($email->getEmailAddress());
			?></a></div>
<?php 		}
		}
      ?>
    </div>
    
    <div  class="link-ico ico-phone"><h2><?php echo lang('phone number') ?></h2></div>
    <div class="cardBlock">
    <?php
	$phones = $company->getAllPhones();
	if (is_array($phones) && count($phones) > 0) {
		foreach ($phones as $phone) {
			$type = $phone->getTelephoneType();
			$type_name = $type instanceof TelephoneType ? '<span class="bold">'.lang($type->getName()) . ': </span>' : '';
			
      		echo "<div>$type_name" . $phone->getNumber() . (trim($phone->getName()) == "" ? "" : " - " . trim($phone->getName())) . '</div>';
      	} 
	} else {  
	  	echo lang('n/a'); 
	}
	?>
    </div>
    
    <div  class="link-ico ico-company"><h2><?php echo lang('address') ?></h2></div>
    <div class="cardBlock">
<?php
	$addresses = $company->getAllAddresses();
	if (is_array($addresses) && count($addresses) > 0) {
    	foreach ($addresses as $address) {
    		echo '<div>';
    		$type = $address->getAddressType();
    		$type_name = $type instanceof AddressType ? '<span class="bold">'.lang($type->getName()) . ': </span>' : '';
    		
			$out = $address->getStreet();
			if($address->getCity() != '') $out .= ' - ' . $address->getCity();
			if($address->getZipCode() != '') $out .= ' - ' . $address->getZipCode();
			if($address->getState() != '') $out .= ' - ' . $address->getState();
			if($address->getCountry() != '') $out .= ' - ' . $address->getCountryName();
			
			echo $type_name . $out;
			?>&nbsp;<a class="map-link coViewAction ico-map" href="http://maps.google.com/?q=<?php echo $out ?>" target="_blank"><?php echo lang('map')?></a><?php
	
			echo '</div>';
		}
	} else {  
	  	echo lang('n/a'); 
	}
?>  </div>
  
    
    <div  class="link-ico ico-map"><h2><?php echo lang('homepage') ?></h2></div>
    <div class="cardBlock" style="margin-bottom:0px;">
<?php
	$webpages = $company->getAllWebpages();
	if (is_array($webpages) && count($webpages) > 0) {
		foreach ($webpages as $webpage) {
			$type = $webpage->getWebpageType();
			$type_name = $type instanceof WebpageType ? '<span class="bold">'.lang($type->getName()) . ': </span>' : '';
			
		?><div><?php echo $type_name ?><a target="_blank" href="<?php echo $webpage->getUrl() ?>"><?php echo clean($webpage->getUrl()) ?></a></div>
		
<?php   } ?>
<?php } else { 
		echo lang('n/a');
	  } // if ?>
    </div>
    
  </div>

</div>

<?php 
	if ($company->getCommentsField()) {
		echo '<div class="commentsTitle">'.lang('notes').'</div><div style="margin-bottom:20px;">';
		echo escape_html_whitespace(convert_to_links(clean($company->getCommentsField())));
		echo '</div>';
	}
?>


<fieldset><legend class="toggle_expanded" onclick="og.toggle('companyUsers',this)"><?php echo lang('users') ?></legend>
<div id='companyUsers'>
<?php
  $this->assign('users', $company->getUsersByCompany());
  $this->includeTemplate(get_template_path('list_users', 'administration'));
?>
</div>
</fieldset>

<?php if (!$company->isOwnerCompany()) { ?>
<fieldset><legend class="toggle_expanded" onclick="og.toggle('companyContacts',this)"><?php echo lang('people') ?></legend>
<div id='companyContacts'>
<?php

  $this->assign('contacts', $company->getContactsByCompany());
  $this->includeTemplate(get_template_path('list_contacts', 'contact')); ?>
</div>
</fieldset>
<?php } ?>

<?php } ?>