<?php if(isset($company) && ($company instanceof Contact)) { ?>
<div class="card">
  <div class="cardIcon"><img src="<?php echo $company->getPictureUrl() ?>" alt="<?php echo clean($company->getObjectName()) ?> logo" /></div>
  <div class="cardData">
  
    <h2><?php echo clean($company->getObjectName()) ?></h2>
    
    <div class="cardBlock">
      <div><span><?php echo lang('email address') ?>:</span> <a <?php echo logged_user()->hasMailAccounts() ? 'href="' . get_url('mail', 'add_mail', array('to' => clean($company->getEmailAddress()))) . '"' : 'target="_self" href="mailto:' . clean($company->getEmailAddress()) . '"' ?>><?php echo $company->getEmailAddress() ?></a></div>
      <div><span><?php echo lang('phone number') ?>:</span> <?php echo $company->getPhoneNumber('work', true) ? clean(clean($company->getPhoneNumber('work', true))) : lang('n/a') ?></div>
      <div><span><?php echo lang('fax number') ?>:</span> <?php echo $company->getPhoneNumber('fax',true) ? clean($company->getPhoneNumber('fax',true)) : lang('n/a') ?></div>
<?php if($company->getWebpageURL('work') != "") { ?>
      <div><span><?php echo lang('homepage') ?>:</span> <a target="_blank" href="<?php echo $company->getWebpageURL('work') ?>"><?php echo clean($company->getWebpageUrl('work')) ?></a></div>
<?php } else { ?>
      <div><span><?php echo lang('homepage') ?>:</span> <?php echo lang('n/a') ?></div>
<?php } // if ?>
    </div>
    

    <h2><?php echo lang('address') ?></h2>
    
    <div class="cardBlock" style="margin-bottom: 0">
<?php    
    $address = $company->getAddress('work',true);
    if($address) { 
       echo clean($address->getStreet()) ;?>
      <br /><?php $city = clean($address->getCity());
      echo $city;
      if( trim($city)!='')
      	echo ',';?> <?php echo clean($address->getState()) ?> <?php echo clean($address->getZipCode()) ?>
<?php if(trim($address->getCountry())) { ?>
      <br /><?php echo clean($address->getCountryName());  
	  } // if  
	  else {  
	  	echo lang('n/a'); 
	  }
	   // if ?>
    </div>
<?php } // if ?> 
 
	</div>
  
  </div>
</div>
<?php }// if  ?>
