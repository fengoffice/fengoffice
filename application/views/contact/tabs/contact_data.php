<div class="contact_form_container form-tab" id="<?php echo $genid?>contact_data">
  <div class="information-block no-border-bottom">
	<div style="float:left;min-width: 100%;">
		<div style="float:left;">
			<?php if ($renderContext): ?>
			<div id="<?php echo $genid ?>add_contact_select_context_div" class="dataBlock"><?php 
				$listeners = array('on_selection_change' => 'og.reload_subscribers("'.$genid.'",'.$object->manager()->getObjectTypeId().')');
				if ($contact->isNew()) {
					render_member_selectors($contact->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'listeners' => $listeners), null, null, false); 
				} else {
					render_member_selectors($contact->manager()->getObjectTypeId(), $genid, $contact->getMemberIds(), array('listeners' => $listeners), null, null, false); 
				} 
			?></div>
			<?php endif ;?>
		  	<div class="clear"></div>
		</div>
		<div class="clear"></div>
		<div id="<?php echo $genid?>_contact_data_role" class="dataBlock" style="display:none;"></div>
			<div>
				<div id="<?php echo $genid ?>existing_company" class="dataBlock">
					<?php echo label_tag(lang('company'), $genid.'profileFormCompany') ?>
					<?php echo select_box('contact[company_id]', array(), array('id' => $genid.'profileFormCompany', "class" => "og-edit-contact-select-company", 'onchange' => 'og.companySelectedIndexChanged(\''.$genid . '\')'))?>
					<span class="widget-body loading" id="<?php echo $genid?>profileFormCompany-loading" style="heigth:20px;background-color:transparent;border:0px none;display:none;"></span>
					<?php if($renderAddCompany){?>
					<a href="#" class="coViewAction ico-add" title="<?php echo lang('add a new company')?>" onclick="og.addNewCompany('<?php echo $genid ?>')"><?php echo lang('add company') . '...' ?></a>
					<?php }?>				
				</div>
				
				<?php if (!array_var($_REQUEST, 'is_user')) { ?>
				<div class="dataBlock">
					<div><?php echo label_tag(lang('email address')) ?></div>
					<?php echo text_field('contact[email]', (isset ($_POST['widget_email'])? $_POST['widget_email']:array_var($contact_data, 'email')), 
						array('id' => $genid.'profileFormEmail', 'maxlength' => 100, 'class' => 'title', 'style' => 'width: 412px;', 'placeholder' => lang('email address'))) ?>
					<div class="clear"></div>
				</div>
				<?php } ?>
				
				<div id="<?php echo $genid?>new_company" style="display:none; padding:6px; margin-top:6px;margin-bottom:6px; background-color:#EEE">
					<div style="float:right;"><a href="#" title="<?php echo lang('cancel')?>" onclick="og.addNewCompany('<?php echo $genid ?>')"><?php echo lang('cancel') ?></a></div>
					
					<div class="dataBlock">
						<div><?php echo label_tag(lang('new company name')) ?></div>
						<div style="float:left;"><?php echo text_field('company[first_name]', '', array('id' => $genid.'profileFormNewCompanyName', 'onchange' => 'og.checkNewCompanyName("'.$genid .'")')) ?></div>
						<div class="clear"></div>
					</div>
					
					<div class="dataBlock">
						<div><?php echo label_tag(lang('email address'), $genid.'clientFormEmail') ?></div>
						<div style="float:left;"><?php echo text_field('company[email]', '', array('id' => $genid.'clientFormAssistantNumber')) ?></div>
						<div class="clear"></div>
					</div>
					
					<div class="dataBlock">
						<div><?php echo label_tag(lang('phone')) ?></div>
			            <div style="float:left;" id="<?php echo $genid?>_comp_phones_container"></div>
			            <div class="clear"></div>
			            <div style="margin:5px 0 10px 200px;">
			            	<a href="#" onclick="og.addNewTelephoneInput('<?php echo $genid?>_comp_phones_container', 'company')" class="coViewAction ico-add"><?php echo lang('add new phone number')?></a>
			            </div>
			        </div>
			        
			        <div class="dataBlock">
			            <div><?php echo label_tag(lang('address')) ?></div>
			            <div style="float:left;" id="<?php echo $genid?>_comp_addresses_container"></div>
			            <div class="clear"></div>
			            <div style="margin:5px 0 10px 200px;">
			            	<a href="#" onclick="og.addNewAddressInput('<?php echo $genid?>_comp_addresses_container', 'company')" class="coViewAction ico-add"><?php echo lang('add new address') ?></a>
			            </div>
		            </div>
		            
		            <div class="dataBlock">
			            <div><?php echo label_tag(lang('webpage')) ?></div>
			            <div style="float:left;" id="<?php echo $genid?>_comp_webpages_container"></div>
			            <div class="clear"></div>
			            <div style="margin:5px 0 10px 200px;">
			            	<a href="#" onclick="og.addNewWebpageInput('<?php echo $genid?>_comp_webpages_container', 'company')" class="coViewAction ico-add"><?php echo lang('add new webpage') ?></a>
			            </div>
			        </div>
				</div>
				
				
			</div>
	
			<div class="clear"></div>
	
			<div class="dataBlock">
				<div><?php echo label_tag(lang('job title'), $genid.'profileFormJobTitle') ?>
				<?php echo text_field('contact[job_title]', array_var($contact_data, 'job_title'), array('id' => $genid.'profileFormJobTitle', 'maxlength' => '40', 'maxlength' => 50)) ?></div>
				<div class="clear"></div>
			</div>
			
			<div class="dataBlock">
				<div><?php echo label_tag(lang('phone')) ?></div>
	            <div style="float:left;" id="<?php echo $genid?>_phones_container"></div>
	            <div class="clear"></div>
	            <div style="margin:5px 0 10px 200px;">
	            	<a href="#" onclick="og.addNewTelephoneInput('<?php echo $genid?>_phones_container')" class="coViewAction ico-add"><?php echo lang('add new phone number')?></a>
	            </div>
	        </div>
	        
	        <?php // if (!$contact->isNew()) { ?>
	        <div class="dataBlock">
				<div><?php echo label_tag(lang('avatar')) ?></div>
	            <div style="float:left;" id="<?php echo $genid?>_avatar_container" class="picture-container">
	            	<img src="<?php echo $contact->getPictureUrl('medium') ?>" alt="<?php echo clean($contact->getObjectName()) ?>" id="<?php echo $genid?>_avatar_img"/>
	            </div>
	            <div style="padding:20px 0 0 20px; text-decoration:underline; float:left; display:none;">
		           	<a href="<?php echo $contact->getUpdatePictureUrl()?>&reload_picture=<?php echo $genid?>_avatar_container" class="internallink coViewAction ico-picture" target=""><?php echo lang('update avatarkkk') ?></a>
				</div>
				
				<div style="padding:20px 0 0 20px; text-decoration:underline; float:left;">
		           	<a href="#" onclick="og.openLink('<?php echo $contact->getUpdatePictureUrl();?>&reload_picture=<?php echo $genid?>_avatar_img<?php echo ($contact->isNew() ? '&new_contact='.$genid.'_picture_file' :'')?>', {caller:'edit_picture'});" 
		           		class="coViewAction ico-picture"><?php echo ($contact->isNew() ? lang('new avatar') : lang('update avatar'))?></a>
		           	<?php if ($contact->isNew()) { ?>
		           		<input type="hidden" id="<?php echo $genid?>_picture_file" name="contact[picture_file]" value=""/>
		           	<?php }?>
				</div>
				
	            <div class="clear"></div>
			</div>
			<?php //} ?>
			
			<?php if (!$renderContext) { ?>
			<div id="<?php echo $genid ?>add_contact_select_context_div" class="dataBlock"><?php
				$skipped_dimensions = array();
				$dims_with_perm = Dimensions::findAll(array('conditions' => 'defines_permissions=1'));
				foreach ($dims_with_perm as $dim_with_perm) {
					$skipped_dimensions[] = $dim_with_perm->getId();
				}
				$listeners = array('on_selection_change' => '');
				$contact_obj = isset($object) && $object instanceof Contact ? $object : $contact;
				if ($contact->isNew()) {
					render_member_selectors($contact_obj->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'listeners' => $listeners, 'hidden_field_name' => 'no_perm_members'), $skipped_dimensions, null, false); 
				} else {
					render_member_selectors($contact_obj->manager()->getObjectTypeId(), $genid, $contact_obj->getMemberIds(), array('listeners' => $listeners, 'hidden_field_name' => 'no_perm_members'), $skipped_dimensions, null, false); 
				} 
			?></div>
			<?php } ?>
	</div>		  
	<div class="clear"></div>
  </div>
</div>

<script>
$(document).ready(function() {
	
	og.load_company_combo("<?php echo $genid?>profileFormCompany", '<?php echo (isset ($_POST['widget_company'])? $_POST['widget_company']:array_var($contact_data, 'company_id', '0')) ?>');

	
	og.telephoneCount = 0;
	og.telephone_types = Ext.util.JSON.decode('<?php echo json_encode($all_telephone_types)?>');

	for (var i=0; i<og.telephone_types.length; i++) {
		if (og.telephone_types[i].code == 'work') def_phone_type = og.telephone_types[i].id;
	}

	<?php if (count(array_var($contact_data, 'all_phones')) == 0) { ?>
	og.addNewTelephoneInput('<?php echo $genid?>_phones_container', 'contact', def_phone_type);
	<?php } else { 
			foreach (array_var($contact_data, 'all_phones') as $phone) { ?>
				og.addNewTelephoneInput('<?php echo $genid?>_phones_container', 'contact', '<?php echo $phone->getTelephoneTypeId()?>', '<?php echo $phone->getNumber()?>', '<?php echo htmlentities($phone->getName(),ENT_QUOTES)?>', '<?php echo $phone->getId()?>');
	  <?php } ?>
	<?php } ?>

	$('#<?php echo $genid?>clientFormAssistantNumber').change(function(){
		$("input[name='company[email]']").val($(this).val());
	});
});
</script>
