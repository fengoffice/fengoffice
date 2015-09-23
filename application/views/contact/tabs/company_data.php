<?php require_javascript("og/modules/addContactForm.js"); ?>
<div class="contact_form_container form-tab" id="<?php echo $genid?>company_data">
		<div class="information-block no-border-bottom">
			<!-- <div class="main-data-title"><?php //echo lang('main data')?></div> -->
			
			<?php if ( $renderContext ) :?>
			<div id="<?php echo $genid ?>add_company_select_context_div"><?php 
				$listeners = array('on_selection_change' => 'og.reload_subscribers("'.$genid.'",'.$object->manager()->getObjectTypeId().')');
				if ($company->isNew()) {
					render_member_selectors($company->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'listeners' => $listeners), null, null, false);
				} else {
					render_member_selectors($company->manager()->getObjectTypeId(), $genid, $company->getMemberIds(), array('listeners' => $listeners), null, null, false); 
				} ?>
			</div>
			<?php endif ;?>
		
			<div class="clear"></div>
			
			<div class="input-container">
				<?php echo label_tag(lang('email address'), $genid.'clientFormEmail') ?>
				<?php echo text_field('company[email]', array_var($company_data, 'email'), array('id' => $genid.'clientFormEmail', 'style' => 'width: 247px;')) ?>
			</div>
			<div class="clear"></div>
			
			<div class="input-container">
				<div><?php echo label_tag(lang('phone')) ?></div>
	            <div style="float:left;" id="<?php echo $genid?>_company_phones_container"></div>
	            <div class="clear"></div>
	            <div style="margin:5px 0 10px 200px;">
	            	<a href="#" onclick="og.addNewTelephoneInput('<?php echo $genid?>_company_phones_container', 'company')" class="coViewAction ico-add"><?php echo lang('add new phone number')?></a>
	            </div>
	        </div>
	        
	        <div style="display:none;"><?php echo select_country_widget('country', '', array('id'=>'template_select_country'));?></div>
            <div class="input-container">
	            <div><?php echo label_tag(lang('address')) ?></div>
	            <div style="float:left;" id="<?php echo $genid?>_addresses_container"></div>
	            <div class="clear"></div>
	            <div style="margin:5px 0 10px 200px;">
	            	<a href="#" onclick="og.addNewAddressInput('<?php echo $genid?>_addresses_container', 'company')" class="coViewAction ico-add"><?php echo lang('add new address') ?></a>
	            </div>
            </div>
            
            <div class="input-container">
	            <div><?php echo label_tag(lang('webpage')) ?></div>
	            <div style="float:left;" id="<?php echo $genid?>_webpages_container"></div>
	            <div class="clear"></div>
	            <div style="margin:5px 0 10px 200px;">
	            	<a href="#" onclick="og.addNewWebpageInput('<?php echo $genid?>_webpages_container', 'company')" class="coViewAction ico-add"><?php echo lang('add new webpage') ?></a>
	            </div>
	        </div>
				
			<div class="input-container">
	            <div><?php echo label_tag(lang('other email addresses')) ?></div>
	            <div style="float:left;" id="<?php echo $genid?>_emails_container"></div>
	            <div class="clear"></div>
	            <div style="margin:5px 0 10px 200px;">
	            	<a href="#" onclick="og.addNewEmailInput('<?php echo $genid?>_emails_container', 'company')" class="coViewAction ico-add"><?php echo lang('add new email address') ?></a>
	            </div>
	        </div>
	        
	        <div class="input-container">
				<div><?php echo label_tag(lang('logo')) ?></div>
	            <div style="float:left;" id="<?php echo $genid?>_avatar_container" class="picture-container">
	            	<img src="<?php echo $company->getPictureUrl('medium') ?>" alt="<?php echo clean($company->getObjectName()) ?>" id="<?php echo $genid?>_avatar_img"/>
	            </div>
	            <div style="padding:20px 0 0 20px; text-decoration:underline; float:left; display:none;">
		           	<a href="<?php echo $company->getUpdatePictureUrl()?>&reload_picture=<?php echo $genid?>_avatar_container" class="internallink coViewAction ico-picture" target=""><?php echo lang('update logo') ?></a>
				</div>
				
				<div style="padding:20px 0 0 20px; text-decoration:underline; float:left;">
		           	<a href="#" onclick="og.openLink('<?php echo $company->getUpdatePictureUrl();?>&reload_picture=<?php echo $genid?>_avatar_img<?php echo ($company->isNew() ? '&new_contact='.$genid.'_picture_file' :'')?>', {caller:'edit_picture'});" 
		           		class="coViewAction ico-picture"><?php echo lang('update logo') ?></a>
		           	<?php if ($company->isNew()) { ?>
		           		<input type="hidden" id="<?php echo $genid?>_picture_file" name="company[picture_file]" value=""/>
		           	<?php }?>
				</div>
				
	            <div class="clear"></div>
			</div>
			
			<div class="input-container">
				<?php echo label_tag(lang('timezone'), 'clientFormTimezone', false)?>
    			<?php echo select_timezone_widget('company[timezone]', array_var($company_data, 'timezone'), array('id' => 'clientFormTimezone', 'class' => 'long')) ?>
    			<div class="clear"></div>
			</div>
	        
	        <div class="input-container">
		      <?php echo label_tag(lang('notes'), $genid.'profileFormNotes') ?>
		      <div style="float:left;width:600px;" class="notes-container">
		      <?php echo textarea_field('company[comments]', array_var($company_data, 'comments'), array('id' => $genid.'profileFormNotes', 'style' => 'width: 100%;', 'rows' => 5)) ?>
		      </div>
		      <div class="clear"></div>
		    </div>
		</div>
</div>

<script>

$(document).ready(function() {

	og.telephoneCount = 0;
	og.telephone_types = Ext.util.JSON.decode('<?php echo json_encode($all_telephone_types)?>');

	og.addressCount = 0;
	og.address_types = Ext.util.JSON.decode('<?php echo json_encode($all_address_types)?>');

	og.webpageCount = 0;
	og.webpage_types = Ext.util.JSON.decode('<?php echo json_encode($all_webpage_types)?>');

	og.emailCount = 0;
	og.email_types = Ext.util.JSON.decode('<?php echo json_encode($all_email_types)?>');

	<?php if (!$object->isNew()) { ?>
		<?php foreach ($company_data['all_phones'] as $phone) { ?>
			og.addNewTelephoneInput('<?php echo $genid?>_company_phones_container', 'company', '<?php echo $phone->getTelephoneTypeId()?>', '<?php echo str_replace("'", "\'", $phone->getNumber())?>', '<?php echo str_replace("'", "\'", $phone->getName())?>', '<?php echo $phone->getId()?>');
		<?php } ?>
	
		<?php foreach ($company_data['all_addresses'] as $address) { ?>
			og.addNewAddressInput('<?php echo $genid?>_addresses_container', 'company', '<?php echo $address->getAddressTypeId()?>', {
				street: '<?php echo escape_character(str_replace("\n", " ", $address->getStreet()))?>',
				city: '<?php echo str_replace("'", "\'", $address->getCity())?>',
				state: '<?php echo str_replace("'", "\'", $address->getState())?>',
				zip_code: '<?php echo str_replace("'", "\'", $address->getZipCode())?>',
				country: '<?php echo $address->getCountry()?>',
				id: '<?php echo $address->getId()?>'
			});
		<?php } ?>
		
		<?php foreach ($company_data['all_webpages'] as $webpage) { ?>
			og.addNewWebpageInput('<?php echo $genid?>_webpages_container', 'company', '<?php echo $webpage->getWebTypeId()?>', '<?php echo str_replace("'", "\'", $webpage->getUrl())?>', '<?php echo $webpage->getId()?>');
		<?php } ?>
	
		<?php foreach (array_var($company_data, 'all_emails') as $email) { ?>
			og.addNewEmailInput('<?php echo $genid?>_emails_container', 'company', '<?php echo $email->getEmailTypeId()?>', '<?php echo str_replace("'", "\'", $email->getEmailAddress())?>', '<?php echo $email->getId()?>');
		<?php } ?>
	<?php } ?>

	for (var i=0; i<og.telephone_types.length; i++) {
		if (og.telephone_types[i].code == 'work') def_phone_type = og.telephone_types[i].id;
	}
	for (var i=0; i<og.address_types.length; i++) {
		if (og.address_types[i].code == 'work') def_address_type = og.address_types[i].id;
	}
	for (var i=0; i<og.webpage_types.length; i++) {
		if (og.webpage_types[i].code == 'work') def_web_type = og.webpage_types[i].id;
	}
	for (var i=0; i<og.email_types.length; i++) {
		if (og.email_types[i].code == 'work') def_email_type = og.email_types[i].id;
	}
	
	<?php if (count(array_var($company_data, 'all_phones')) == 0) { ?>
		og.addNewTelephoneInput('<?php echo $genid?>_company_phones_container', 'company', def_phone_type);
	<?php } ?>
	<?php if (count(array_var($company_data, 'all_addresses')) == 0) { ?>
		og.addNewAddressInput('<?php echo $genid?>_addresses_container', 'company', def_address_type);
	<?php } ?>
	<?php if (count(array_var($company_data, 'all_webpages')) == 0) { ?>
		og.addNewWebpageInput('<?php echo $genid?>_webpages_container', 'company', def_web_type);
	<?php } ?>
	<?php if (count(array_var($company_data, 'all_emails')) == 0) { ?>
		og.addNewEmailInput('<?php echo $genid?>_emails_container', 'company', def_email_type);
	<?php } ?>

});
</script>