<?php 
	// telephone types
	$all_telephone_types = TelephoneTypes::getAllTelephoneTypesInfo();
	// address types
	$all_address_types = AddressTypes::getAllAddressTypesInfo();
	// webpage types
	$all_webpage_types = WebpageTypes::getAllWebpageTypesInfo();
	// email types
	$all_email_types = EmailTypes::getAllEmailTypesInfo();
	// instant messenger types
	$im_types = ImTypes::findAll(array('order' => '`id`'));
	
	if (!isset($id_prefix)) {
		$id_prefix = '';
	}
?>
<div id="<?php echo $genid?>_additional_data" class="additional-data">
	<div class="information-block no-border-bottom">
		
		<div class="input-container">
			<?php echo label_tag(lang('birthday'), $genid.'profileFormBirthday')?>
			<span style="float:left;"><?php echo pick_date_widget2('contact[birthday]', array_var($contact_data, 'birthday'), $genid, 265) ?></span>
		</div>
		<div class="clear"></div>
		
		<div class="input-container">
			<?php echo label_tag(lang('department'), $genid.'profileFormDepartment') ?>
			<?php echo text_field('contact[department]', array_var($contact_data, 'department'), array('id' => $genid.'profileFormDepartment', 'maxlength' => 50)) ?>
		</div>
		<div class="clear"></div>
		
		<div class="input-container">
            <div><?php echo label_tag(lang('email address')) ?></div>
            <div style="float:left;" id="<?php echo $genid . $id_prefix ?>_emails_container"></div>
            <div class="clear"></div>
            <div style="margin:5px 0 10px 200px;">
            	<a href="#" onclick="og.addNewEmailInput('<?php echo $genid . $id_prefix ?>_emails_container')" class="coViewAction ico-add"><?php echo lang('add new email address') ?></a>
	            </div>
	        </div>
            
            <div style="display:none;"><?php echo select_country_widget('country', '', array('id'=>'template_select_country'));?></div>
            <div class="input-container">
	            <div><?php echo label_tag(lang('address')) ?></div>
            <div style="float:left;" id="<?php echo $genid . $id_prefix ?>_addresses_container"></div>
            <div class="clear"></div>
            <div style="margin:5px 0 10px 200px;">
            	<a href="#" onclick="og.addNewAddressInput('<?php echo $genid . $id_prefix ?>_addresses_container')" class="coViewAction ico-add"><?php echo lang('add new address') ?></a>
	            </div>
            </div>
            
            <div class="input-container">
	            <div><?php echo label_tag(lang('webpage')) ?></div>
            <div style="float:left;" id="<?php echo $genid . $id_prefix ?>_webpages_container"></div>
            <div class="clear"></div>
            <div style="margin:5px 0 10px 200px;">
            	<a href="#" onclick="og.addNewWebpageInput('<?php echo $genid . $id_prefix ?>_webpages_container')" class="coViewAction ico-add"><?php echo lang('add new webpage') ?></a>
	            </div>
	        </div>

            <div class="input-container">
				<div><?php echo label_tag(lang('instant messengers')) ?></div>
			<div style="float:left;" class="im-container">
				<table class="blank">
					<tr>
						<th colspan="2"><?php echo lang('im service') ?></th>
						<th><?php echo lang('value') ?></th>
						<th><?php echo lang('primary im service') ?></th>
					</tr>
					<?php foreach($im_types as $im_type) { ?>
					<tr>
						<td style="vertical-align: middle"><img src="<?php echo $im_type->getIconUrl() ?>" alt="<?php echo $im_type->getName() ?> icon" /></td>
						<td style="vertical-align: middle"><span style="padding:0 5px;"><?php echo $im_type->getName() ?></span></td>
						<td style="vertical-align: middle"><?php echo text_field('contact[im_' . $im_type->getId() . ']', array_var($contact_data, 'im_' . $im_type->getId()), array('id' => $genid.'profileFormIm' . $im_type->getId())) ?></td>
						<td style="vertical-align: middle;text-align: center;"><?php echo radio_field('contact[default_im]', array_var($contact_data, 'default_im') == $im_type->getId(), array('value' => $im_type->getId())) ?></td>
					</tr>
					<?php } // foreach ?>
				</table>
			</div>
		</div>
		<div class="clear"></div>
	
		<div class="input-container">
			<div id="<?php echo $genid ?>add_contact_notes">
				<?php echo label_tag(lang('notes'), $genid.'profileFormNotes') ?>
				<div style="float:left;width:600px;" class="notes-container">
					<?php echo textarea_field('contact[comments]', array_var($contact_data, 'comments'), array('id' => $genid.'profileFormNotes', 'style' => 'width: 100%;', 'rows'=>5)) ?>
				</div>
			</div>
		</div>
		<div class="clear"></div>
	</div>

</div>

<script>

$(document).ready(function() {
	
	var is_new_contact = <?php echo $new_contact ? 'true' : 'false'?>;
	
	og.addressCount = 0;
	og.address_types = Ext.util.JSON.decode('<?php echo json_encode($all_address_types)?>');

	og.webpageCount = 0;
	og.webpage_types = Ext.util.JSON.decode('<?php echo json_encode($all_webpage_types)?>');

	og.emailCount = 0;
	og.email_types = Ext.util.JSON.decode('<?php echo json_encode($all_email_types)?>');
	
	og.telephoneCount = 0;
	og.telephone_types = Ext.util.JSON.decode('<?php echo json_encode($all_telephone_types)?>');

	if (!is_new_contact) {
	<?php foreach (array_var($contact_data, 'all_addresses') as $address) { ?>
		og.addNewAddressInput('<?php echo $genid . $id_prefix ?>_addresses_container', 'contact', '<?php echo $address->getAddressTypeId()?>', {
			street: '<?php echo escape_character(str_replace("\n", " ", $address->getStreet()))?>',
			city: '<?php echo escape_character($address->getCity())?>',
			state: '<?php echo escape_character($address->getState())?>',
			zip_code: '<?php echo escape_character($address->getZipCode())?>',
			country: '<?php echo $address->getCountry()?>',
			id: '<?php echo $address->getId()?>'
		});
	<?php } ?>
	
	<?php foreach (array_var($contact_data, 'all_webpages') as $webpage) { ?>
		og.addNewWebpageInput('<?php echo $genid . $id_prefix ?>_webpages_container', 'contact', '<?php echo $webpage->getWebTypeId()?>', '<?php echo escape_character($webpage->getUrl())?>', '<?php echo $webpage->getId()?>');
	<?php } ?>

	<?php foreach (array_var($contact_data, 'all_emails') as $email) { ?>
		og.addNewEmailInput('<?php echo $genid . $id_prefix ?>_emails_container', 'contact', '<?php echo $email->getEmailTypeId()?>', '<?php echo escape_character($email->getEmailAddress())?>', '<?php echo $email->getId()?>');
	<?php } ?>
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
	for (var i=0; i<og.telephone_types.length; i++) {
		if (og.telephone_types[i].code == 'work') def_phone_type = og.telephone_types[i].id;
	}
				
	<?php if (count(array_var($contact_data, 'all_addresses')) == 0) { ?>
		og.addNewAddressInput('<?php echo $genid . $id_prefix ?>_addresses_container', 'contact', def_address_type);
	<?php } ?>
	<?php if (count(array_var($contact_data, 'all_webpages')) == 0) { ?>
		og.addNewWebpageInput('<?php echo $genid . $id_prefix ?>_webpages_container', 'contact', def_web_type);
	<?php } ?>
	<?php if (count(array_var($contact_data, 'all_emails')) == 0) { ?>
		og.addNewEmailInput('<?php echo $genid . $id_prefix ?>_emails_container', 'contact', def_email_type);
	<?php } ?>

	og.addNewTelephoneInput('<?php echo $genid . $id_prefix ?>_comp_phones_container', 'company', def_phone_type);
	og.addNewAddressInput('<?php echo $genid . $id_prefix ?>_comp_addresses_container', 'company', def_address_type);
	og.addNewWebpageInput('<?php echo $genid . $id_prefix ?>_comp_webpages_container', 'company', def_web_type);


	$("#<?php echo $genid?>tabs").tabs({
		activate: function( event, ui ) {
			og.resizeAddressContainer();
		}
	});

	og.resizeAddressContainer = function() {
		setTimeout(function(){
	    	var container_w = $('.additional-data').width();
	    	$('.address-input-container').css('width', (container_w - 220)+'px').css('max-width', (container_w - 220)+'px');
		}, 250);
    }
	$(window).resize(function() {
		og.resizeAddressContainer();
	});
});

</script>