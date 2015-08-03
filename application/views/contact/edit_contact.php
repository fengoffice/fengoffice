<?php
	require_javascript("og/modules/addContactForm.js");
	$genid = gen_id();
	if (!isset($object)) {
		$object = $contact;
	}
	$renderContext = has_context_to_render($contact->manager()->getObjectTypeId());
	if ((!$object->isNew() && $object->isUser()) || array_var($_GET, 'is_user')) {
		$renderContext = false;
	}
	
	// on submit functions
	if (array_var($_REQUEST, 'modal')) {
		$on_submit = "og.submit_modal_form('".$genid."submit-edit-form'); return false;";
	} else {
		$on_submit = "return true;";
	}
	
	if (array_var($_REQUEST, 'is_user') == 1 && isset($user_type) && $user_type > 0) {
		$on_submit = "og.ogPermPrepareSendData('$genid');" . $on_submit;
	}
	
	$has_custom_properties = CustomProperties::countAllCustomPropertiesByObjectType($object->getObjectTypeId()) > 0;
	
	$categories = array(); Hook::fire('object_edit_categories', $object, $categories);
	
	$add_contact_lang = lang('add contact');
	$new_contact_lang = lang('new contact');
	$edit_contact_lang = lang('edit contact');
	if (array_var($_REQUEST, 'is_user') == 1 && isset($user_type) && $user_type > 0) {
		$add_contact_lang = lang('add user');
		$new_contact_lang = lang('new user');
		$edit_contact_lang = lang('edit user');
	}
	
	$can_change_permissions = $contact->isNew() ? can_manage_security(logged_user()) : $contact->canUpdatePermissions(logged_user());
	
	$all_user_groups = PermissionGroups::instance()->getUserGroupsInfo();
?>

<form id="<?php echo $genid ?>submit-edit-form" onsubmit="<?php echo $on_submit?>" class="internalForm" action="<?php echo $contact->isNew() ? $contact->getAddUrl() : $contact->getEditUrl() ?>" method="post">
<input id="<?php echo $genid ?>hfIsNewCompany" type="hidden" name="contact[isNewCompany]" value=""/>

<?php if (array_var($_REQUEST, 'create_user_from_contact')) { ?>
<input id="<?php echo $genid ?>hfUserFromContact" type="hidden" name="user_from_contact_id" value="<?php echo $userFromContactId?>"/>
<?php } ?>

<div class="contact">
<div class="coInputHeader">
  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<?php echo $contact->isNew() ? $new_contact_lang : $edit_contact_lang ?>
	</div>
	
  </div>
	<div class="coInputName">
		<?php echo text_field('contact[first_name]', (isset ($_POST['widget_name'])? $_POST['widget_name']:array_var($contact_data, 'first_name')), 
				array('id' => $genid . 'profileFormFirstName', 'maxlength' => 50, 'placeholder' => lang('first name')." *", 'class' => 'title short')); ?>
		<?php echo text_field('contact[surname]', (isset ($_POST['widget_surname'])? $_POST['widget_surname']:array_var($contact_data, 'surname')), 
				array('id' => $genid . 'profileFormSurname',  'maxlength' => 50, 'placeholder' => lang('last name')." *", 'class' => 'title short')) ?>
	</div>
	<div class="coInputButtons">
		<?php echo submit_button($contact->isNew() ? $add_contact_lang : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px', 'id' => $genid . 'submit1')) ?>
	</div>
  <input type="hidden" name="contact[new_contact_from_mail_div_id]" value="<?php echo array_var($contact_data, 'new_contact_from_mail_div_id', '') ?>"/>
  <input type="hidden" name="contact[hf_contacts]" value="<?php echo array_var($contact_data, 'hf_contacts') ?>"/>
  <div class="clear"></div>
  
  	<?php if (array_var($_REQUEST, 'is_user')) { ?>
	<div class="coInputName">
		<?php //echo label_tag(lang('email address'), $genid.'profileFormEmail', array_var($_REQUEST, 'is_user') == 1) ?>
		<?php echo text_field('contact[email]', (isset ($_POST['widget_email'])? $_POST['widget_email']:array_var($contact_data, 'email')), 
			array('id' => $genid.'profileFormEmail', 'maxlength' => 100, 'class' => 'title', 'style' => 'width: 412px;margin-top:5px;', 'placeholder' => lang('email address')." *")) ?>
	</div>
	<?php } ?>
	<div class="clear"></div>
  
</div>
	
<div class="coInputMainBlock">
	<input id="<?php echo $genid?>updated-on-hidden" type="hidden" name="updatedon" value="<?php echo !$contact->isNew() ?  $contact->getUpdatedOn()->getTimestamp() : '' ?>">
	<input id="<?php echo $genid?>merge-changes-hidden" type="hidden" name="merge-changes" value="" >
	<input id="<?php echo $genid?>genid" type="hidden" name="genid" value="<?php echo $genid ?>" >
	
  <div id="<?php echo $genid?>tabs" class="edit-form-tabs">
	
	<ul id="<?php echo $genid?>tab_titles">
	
			<li><a id="<?php echo $genid?>contact_data_tab" href="#<?php echo $genid?>contact_data"><?php echo lang('person data') ?></a></li>
			<li><a id="<?php echo $genid?>additional_data_tab" href="#<?php echo $genid?>additional_data" class="additional-data-tab"><?php echo lang('additional data') ?></a></li>
			<?php if ($contact->isNew() && array_var($_REQUEST, 'is_user') == 1 || $contact->isUser()) { ?>
			<li><a href="#<?php echo $genid?>user_data"><?php echo lang('user data') ?></a></li>
			<?php } ?>
			
			<?php if (array_var($_REQUEST, 'is_user') == 1 && isset($user_type) && $user_type > 0 && $can_change_permissions) { ?>
			<li><a id="<?php echo $genid?>permissions_tab" href="#<?php echo $genid?>permissions"><?php echo lang('permissions') ?></a></li>
				<?php if (count($all_user_groups) > 0) {?>
			<li><a id="<?php echo $genid?>groups_tab" href="#<?php echo $genid?>groups"><?php echo lang('groups') ?></a></li>
				<?php } ?>
			<?php } ?>
			
			<?php if ($has_custom_properties || config_option('use_object_properties')) { ?>
			<li><a id="<?php echo $genid?>add_custom_properties_div_tab" href="#<?php echo $genid?>add_custom_properties_div"><?php echo lang('custom properties') ?></a></li>
			<?php } ?>
			
			<?php if (!array_var($_REQUEST, 'is_user')) { ?>
			<li><a id="<?php echo $genid?>add_subscribers_div_tab" href="#<?php echo $genid?>add_subscribers_div"><?php echo lang('object subscribers') ?></a></li>
			<?php } ?>
			
			<?php if($object->isNew() || $object->canLinkObject(logged_user())) { ?>
			<li><a id="<?php echo $genid?>add_linked_objects_div_tab" href="#<?php echo $genid?>add_linked_objects_div"><?php echo lang('linked objects') ?></a></li>
			<?php } ?>
			
			<?php foreach ($categories as $category) { ?>
			<li><a id="<?php echo $genid . $category['name']?>_tab" href="#<?php echo $genid . $category['name'] ?>"><?php echo $category['name'] ?></a></li>
			<?php } ?>
	</ul>

	<?php if (array_var($_REQUEST, 'is_user') == 1 && isset($user_type) && $user_type > 0 && $can_change_permissions) { ?>
	<div class="permissions-container form-tab" id="<?php echo $genid?>permissions" style="display:none;">
	<?php
		if ($contact->isNew()) {
			$pg_id = $user_type;
			tpl_assign('is_new_user', true);
			$user = new Contact();
			$user->setUserType($user_type);
			tpl_assign('user', $user);
			
			// root permissions for new user
			$root_permissions = array();
			if (config_option('let_users_create_objects_in_root') && ($user->isAdminGroup() || $user->isExecutive() || $user->isManager())) {
				$all_object_types = ObjectTypes::instance()->findAll(array('conditions' => "type IN ('content_object', 'located') AND type NOT IN ('comment') AND name <> 'file revision' AND name <> 'template_task' AND name <> 'template_milestone' AND `name` <> 'template' AND
					(plugin_id IS NULL OR plugin_id = 0 OR plugin_id IN (SELECT id FROM ".TABLE_PREFIX."plugins WHERE is_activated > 0 AND is_installed > 0))"));

				foreach ($all_object_types as $ot) {
					$root_permissions[$ot->getId()] = array('w' => 1, 'd' => 1, 'r' => 1);
				}
			}
			
			// Set role permissions for active members
			$sel_members = array();
			$member_permissions = array();
			
			$allowed_user_type_ids = config_option('give_member_permissions_to_new_users');
			$role_ot_permissions = RoleObjectTypePermissions::findAll(array('conditions' => "role_id = '$user_type' AND object_type_id NOT IN (SELECT id FROM ".TABLE_PREFIX."object_types WHERE name IN ('template','comment'))"));
			$members_with_permissions = array();
			
			if (in_array($user_type, $allowed_user_type_ids)) {

				$enabled_dimension_ids = config_option('enabled_dimensions');
				if (count($enabled_dimension_ids) > 0) {
					$dimension_ids = Dimensions::findAll(array('id' => true, 'conditions' => "id in (".implode(',', $enabled_dimension_ids).") AND defines_permissions=1 AND is_manageable=1"));
					if (count($dimension_ids) > 0) {
						$members_with_permissions = Members::findAll(array('id' => true, 'conditions' => "dimension_id IN (".implode(',', $dimension_ids).")"));
					}
				}
				
			}
			
			$active_context = active_context();
			if (is_array($active_context) && count($active_context) > 0) {
				foreach ($active_context as $selection) {
					if ($selection instanceof Member) {
						$members_with_permissions[] = $selection->getId();
					}
				}
			}
			
			foreach ($members_with_permissions as $member_id) {
				foreach ($role_ot_permissions as $p) {
					if (!isset($member_permissions[$member_id])) $member_permissions[$member_id] = array();
					$member_permissions[$member_id][] = array(
							'o' => $p->getObjectTypeId(),
							'w' => $p->getCanWrite(),
							'd' => $p->getCanDelete(),
							'r' => 1
					);
				}
			}
			
		} else {
			$pg_id = $contact->getPermissionGroupId();
			tpl_assign('user', $contact);
		}
		
		$parameters = permission_form_parameters($pg_id);
		if ($contact->isNew()) {
			$parameters['root_permissions'] = $root_permissions;
			$parameters['member_permissions'] = $member_permissions;
		}
		
		
		// Module Permissions
		$module_permissions = TabPanelPermissions::findAll(array("conditions" => "`permission_group_id` = $pg_id"));
		$module_permissions_info = array();
		foreach ($module_permissions as $mp) {
			$module_permissions_info[$mp->getTabPanelId()] = 1;
		}
		$all_modules = TabPanels::findAll(array("conditions" => "`enabled` = 1", "order" => "ordering"));
		$all_modules_info = array();
		foreach ($all_modules as $module) {
			$all_modules_info[] = array('id' => $module->getId(), 'name' => lang($module->getTitle()), 'ot' => $module->getObjectTypeId());
		}
			
		// System Permissions
		$system_permissions = SystemPermissions::findById($pg_id);
			
		tpl_assign('module_permissions_info', $module_permissions_info);
		tpl_assign('all_modules_info', $all_modules_info);
		if (!$system_permissions instanceof SystemPermission) {
			$system_permissions = new SystemPermission();
		}
		tpl_assign('system_permissions', $system_permissions);
			
		tpl_assign('permission_parameters', $parameters);
			
		$more_permissions = array();
		Hook::fire('add_user_permissions', $pg_id, $more_permissions);
		tpl_assign('more_permissions', $more_permissions);
		
		tpl_assign('pg_id', $pg_id);
			
		// Permission Groups
		$groups = PermissionGroups::getNonPersonalSameLevelPermissionsGroups('`parent_id`,`id` ASC');
		tpl_assign('groups', $groups);
		foreach($groups as $group){
	    	$permission_groups[] = array($group->getId(), lang($group->getName()));
	    }
		
		tpl_assign('genid', $genid);
		$this->includeTemplate(get_template_path('system_permissions', 'account'));
	?>
	</div>
	
	<?php if (count($all_user_groups) > 0) { ?>
	<div class="user-groups-container form-tab" id="<?php echo $genid?>groups" style="display:none;">
		<div id="<?php echo $genid?>user_groups_container"></div>
	<?php
		$user_group_ids = array();
		$ugroups_data = array();
		if (!$contact->isNew()) {
			$tmp_user_group_ids = $contact->getPermissionGroupIds();
			foreach ($tmp_user_group_ids as $ugid) {
				if ($ugid != $contact->getPermissionGroupId()) $user_group_ids[] = $ugid;
			}
			$ugroups_data = PermissionGroups::instance()->getUserGroupsInfo(" AND id IN (".implode(',', $user_group_ids).")", null, false);
		}
		echo "<script>";
		foreach ($ugroups_data as $ugdata) {
			echo "og.addUserGroupToUser('$genid', '".$genid."user_groups_container', '".json_encode($ugdata)."');";
		}
		echo "</script>";
	?>
		<input type="hidden" id="<?php echo $genid?>_user_groups" name="user_groups" value=""/>
		<div class="clear"></div>
		<div class="user-groups-selector">
			<?php echo lang('select group to add user')?>
			<div id="<?php echo $genid?>user_groups_selector_div" class="user-groups-selector-container"></div>
		</div>
	</div>
	<?php } ?>
	
	<?php } ?>
	
	<?php
	//Basic contact data tab
	render_contact_data_tab($genid, $object, $renderContext, $contact_data);
	?>
	
	
	<div class="contact_form_container form-tab" id="<?php echo $genid?>additional_data">
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
		            <div style="float:left;" id="<?php echo $genid?>_emails_container"></div>
		            <div class="clear"></div>
		            <div style="margin:5px 0 10px 200px;">
		            	<a href="#" onclick="og.addNewEmailInput('<?php echo $genid?>_emails_container')" class="coViewAction ico-add"><?php echo lang('add new email address') ?></a>
		            </div>
		        </div>
	            
	            <div style="display:none;"><?php echo select_country_widget('country', '', array('id'=>'template_select_country'));?></div>
	            <div class="input-container">
		            <div><?php echo label_tag(lang('address')) ?></div>
		            <div style="float:left;" id="<?php echo $genid?>_addresses_container"></div>
		            <div class="clear"></div>
		            <div style="margin:5px 0 10px 200px;">
		            	<a href="#" onclick="og.addNewAddressInput('<?php echo $genid?>_addresses_container')" class="coViewAction ico-add"><?php echo lang('add new address') ?></a>
		            </div>
	            </div>
	            
	            <div class="input-container">
		            <div><?php echo label_tag(lang('webpage')) ?></div>
		            <div style="float:left;" id="<?php echo $genid?>_webpages_container"></div>
		            <div class="clear"></div>
		            <div style="margin:5px 0 10px 200px;">
		            	<a href="#" onclick="og.addNewWebpageInput('<?php echo $genid?>_webpages_container')" class="coViewAction ico-add"><?php echo lang('add new webpage') ?></a>
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
	</div>
	
	<div class="contact_form_container form-tab" id="<?php echo $genid?>user_data">
		<?php if (!$contact->isNew() && array_var($_REQUEST, 'is_user') == 1 && $contact->isUser()) { ?>
		<div id="<?php echo $genid?>_user_data" class="user-data">
			<div class="information-block no-border-bottom">
            	<div class="input-container">
	            	<div id="<?php echo $genid ?>update_profile_timezone">
						<label><?php echo lang('auto detect user timezone') ?></label>
						<div id="<?php echo $genid?>detectTimeZone" style="vertical-align:middle;">
						<?php
							$now = DateTimeValueLib::now(); 
							$on_autodetect_click = 'og.getTimezoneFromBrowser(new Date('.$now->getYear().','.($now->getMonth() - 1).','.$now->getDay().','.$now->getHour().','.$now->getMinute().','.$now->getSecond().'), \''.$genid.'\');';
						?>
						
							<div style="float:left; padding-top: 5px; margin: 0 15px 0 0;">
							<?php echo yes_no_widget('contact[autodetect_time_zone]', $genid.'userFormAutoDetectTimezone', user_config_option('autodetect_time_zone', null, $contact->getId()), 
								lang('yes'), lang('no'), null, array('onclick' => "og.showSelectTimezone('$genid');$on_autodetect_click")) ?>
							</div>
							<div id="<?php echo $genid?>selecttzdiv" <?php if (user_config_option('autodetect_time_zone', null, $contact->getId())) echo 'style="float:left; display:none; "'; ?>>
								<?php echo select_timezone_widget('contact[timezone]', array_var($contact_data, 'timezone'), array('id' => $genid.'userFormTimezone', 'class' => 'long')) ?>
							</div>
							
						</div>
						<div class="clear"></div>
					</div>
				</div>
				
				<div class="input-container">
					<?php echo label_tag(lang('username'), $genid . 'profileFormUsername') ?>
	      			<?php echo text_field('user[username]', array_var($contact_data, 'username'), array('id' => $genid . 'profileFormUsername')) ?>
				</div>
				
				<div class="field role" style="<?php echo (array_var($_REQUEST, 'is_user') == 1 && isset($user_type) && $user_type > 0 && $can_change_permissions ? "display:none;" : "")?>" id="user_role_div">
					<?php echo label_tag(lang('user type'), '', true) ?>
					<div id="<?php echo $genid?>_user_type_container"></div>
				</div>
				
            </div>
		</div>
		<?php } ?>
		
		<?php if ($contact->isNew() && array_var($_REQUEST, 'is_user') == 1  || $isEdit){ ?>
		<div class="information-block user-data no-border-bottom">
		<?php 
				tpl_assign('contact_mail', $contact_mail);
				tpl_assign('orig_genid', $genid);
				tpl_assign('new_contact', $object->isNew());
				$this->includeTemplate(get_template_path("add_contact/access_data_edit","contact"));
		?>
			<div class="field role" style="display:none;" id="user_role_div">
				<label><?php echo lang("user type")?>:</label>
				<div id="<?php echo $genid?>_user_type_container"></div>
			</div>
		</div>
		<?php } ?>
	</div>
	
	
	<?php if ($has_custom_properties || config_option('use_object_properties')) { ?>
	<div id='<?php echo $genid ?>add_custom_properties_div' class="form-tab">
		<?php echo render_object_custom_properties($object, false) ?>
		<?php echo render_add_custom_properties($object); ?>
	</div>
	<?php } ?>
	
	<div id="<?php echo $genid ?>add_subscribers_div" class="form-tab">
		<?php $subscriber_ids = array();
			if (!$object->isNew()) {
				$subscriber_ids = $object->getSubscriberIds();
			} else {
				$subscriber_ids[] = logged_user()->getId();
			}
			if ((!$object->isNew() && $object->isUser()) || array_var($_GET, 'is_user')) {
			} else {
		?><input type="hidden" id="<?php echo $genid ?>subscribers_ids_hidden" value="<?php echo implode(',',$subscriber_ids)?>"/>
		<?php } ?>
		<div id="<?php echo $genid ?>add_subscribers_content">
		<?php //echo render_add_subscribers($object, $genid); ?>
		</div>
	</div>
	
	
	<?php if($object->isNew() || $object->canLinkObject(logged_user())) : ?>
		<div style="display:none" id="<?php echo $genid ?>add_linked_objects_div" class="form-tab">
			<?php echo render_object_link_form($object) ?>
		</div>
	<?php endif; ?>
	
	<?php foreach ($categories as $category) { ?>
	<div id="<?php echo $genid . $category['name'] ?>" class="form-tab">
		<?php echo $category['content'] ?>
	</div>
	<?php } ?>
	
  </div>
  
  	<?php if (!array_var($_REQUEST, 'modal')) {
		echo submit_button($contact->isNew() ? $add_contact_lang : lang('save changes'),'s', array('id' => $genid . 'submit2'));
  	} ?>

	<script>

		var is_new_contact = <?php echo $object->isNew() ? 'true' : 'false'?>;
		$(document).ready(function() {
					
			og.addressCount = 0;
			og.address_types = Ext.util.JSON.decode('<?php echo json_encode($all_address_types)?>');

			og.webpageCount = 0;
			og.webpage_types = Ext.util.JSON.decode('<?php echo json_encode($all_webpage_types)?>');

			og.emailCount = 0;
			og.email_types = Ext.util.JSON.decode('<?php echo json_encode($all_email_types)?>');

			if (!is_new_contact) {
			<?php foreach (array_var($contact_data, 'all_addresses') as $address) { ?>
				og.addNewAddressInput('<?php echo $genid?>_addresses_container', 'contact', '<?php echo $address->getAddressTypeId()?>', {
					street: '<?php echo str_replace("'", "\'", str_replace("\n", " ", $address->getStreet()))?>',
					city: '<?php echo str_replace("'", "\'", $address->getCity())?>',
					state: '<?php echo str_replace("'", "\'", $address->getState())?>',
					zip_code: '<?php echo str_replace("'", "\'", $address->getZipCode())?>',
					country: '<?php echo $address->getCountry()?>',
					id: '<?php echo $address->getId()?>'
				});
			<?php } ?>
			
			<?php foreach (array_var($contact_data, 'all_webpages') as $webpage) { ?>
				og.addNewWebpageInput('<?php echo $genid?>_webpages_container', 'contact', '<?php echo $webpage->getWebTypeId()?>', '<?php echo str_replace("'", "\'", $webpage->getUrl())?>', '<?php echo $webpage->getId()?>');
			<?php } ?>

			<?php foreach (array_var($contact_data, 'all_emails') as $email) { ?>
				og.addNewEmailInput('<?php echo $genid?>_emails_container', 'contact', '<?php echo $email->getEmailTypeId()?>', '<?php echo str_replace("'", "\'", $email->getEmailAddress())?>', '<?php echo $email->getId()?>');
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
						
			<?php if (count(array_var($contact_data, 'all_addresses')) == 0) { ?>
				og.addNewAddressInput('<?php echo $genid?>_addresses_container', 'contact', def_address_type);
			<?php } ?>
			<?php if (count(array_var($contact_data, 'all_webpages')) == 0) { ?>
				og.addNewWebpageInput('<?php echo $genid?>_webpages_container', 'contact', def_web_type);
			<?php } ?>
			<?php if (count(array_var($contact_data, 'all_emails')) == 0) { ?>
				og.addNewEmailInput('<?php echo $genid?>_emails_container', 'contact', def_email_type);
			<?php } ?>

			og.addNewTelephoneInput('<?php echo $genid?>_comp_phones_container', 'company', def_phone_type);
			og.addNewAddressInput('<?php echo $genid?>_comp_addresses_container', 'company', def_address_type);
			og.addNewWebpageInput('<?php echo $genid?>_comp_webpages_container', 'company', def_web_type);
			
			<?php if(isset ($_POST['widget_is_user'])){ ?>
				$('input[name*="contact[user][create-user]"]').prop("checked",true);
				$(".user-data").show();
			<?php } ?>
			<?php if(isset ($_POST['widget_user_type'])){ ?>
				$('[name="contact[user][type]"]').val(<?php echo $_POST['widget_user_type'] ?>);
			<?php } ?>

			og.checkEmailAddress("#<?php echo $genid ?>profileFormEmail",'<?php echo $contact->getId();?>','<?php echo $genid ?>', 'contact');
			
			Ext.get('<?php echo $genid ?>profileFormFirstName').focus();

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


	    	<?php if (array_var($_REQUEST, 'is_user') == 1 && isset($user_type) && $user_type > 0 && $can_change_permissions) { ?>
		    	og.renderUserTypeSelector({container_id:"<?php echo $genid?>_user_type_container", input_name:'contact[user][type]', selected_value: <?php echo $user_type?>, id:'<?php echo $genid?>_user_type_sel'});

	    		$("#<?php echo $genid?>_contact_data_role").html($("#user_role_div").html() + '<div class="clear"></div>');
	    		$("#<?php echo $genid?>_contact_data_role").show();
	    		$("#<?php echo $genid?>_contact_data_role select.user-type-selector").change(function(){
		    		og.afterUserTypeChange('<?php echo $genid?>', $(this).val());
		    		og.ogPermPrepareSendData('<?php echo $genid?>');
		    	});
	    		og.userPermissions.enableDisableSystemPermissionsByRole('<?php echo $genid?>', <?php echo $user_type ?>);

	    		$("#<?php echo $genid?>permissions_tab").click(function(){

	    			og.afterUserTypeChangeAndPermissionsClick('<?php echo $genid?>');
	    			
		    	});


	    		
	    		<?php if ($contact->isNew()) { ?>
	    		var permissions = og.permissionInfo[genid].permissions;
	    		for (i in permissions){
	    			for (var j = 0; j < permissions[i].length; j++){
	    				var p = permissions[i][j];
	    				if (p) p.modified = true;
	    			}
	    		}
	    		<?php } ?>
	    		
	    		og.ogPermPrepareSendData('<?php echo $genid?>');
	    		$("#user_role_div").remove();

		    	<?php if (count($all_user_groups) > 0) { ?>
		    		var groups_store_tmp = Ext.util.JSON.decode('<?php echo str_replace("'", "\'", json_encode($all_user_groups));?>');
		    		var groups_store = [];
		    		for (x in groups_store_tmp) {
		    			groups_store.push([groups_store_tmp[x].id, groups_store_tmp[x].name]);
		    		}
		    		
		    		var groups_combo = new Ext.form.ComboBox({
		    			renderTo:'<?php echo $genid ?>user_groups_selector_div',
		    			name: 'user_groups_selector',
		    			id: '<?php echo $genid ?>user_groups_selector',
		    			value: '',
		    			store: groups_store,
		    			displayField: 'name',
		    	        mode: 'local',
		    	        cls: 'group-selector-combo',
		    	        triggerAction: 'all',
		    	        selectOnFocus:true,
		    	        width: 400,
		    	        listWidth: 400,
		    	        valueField: 'id',
		    	        emptyText: (lang('select user group') + '...'),
		    	        valueNotFoundText: ''
		    		});
		    		groups_combo.on('select', function(combo, selected, index) {
		    			var group = {id: selected.data.value, name: selected.data.text};
		    			og.addUserGroupToUser('<?php echo $genid?>', '<?php echo $genid?>user_groups_container', group);
		    			combo.clearValue();
		    		});
				<?php } ?>
	    		
	    	<?php } else { ?>
	    		$("#user_role_div").remove();
	    	<?php } ?>

	    	<?php if (isset($active_tab) && $active_tab) { ?>
				setTimeout(function(){
					$('#<?php echo $genid . $active_tab . "_tab";?>').click();
				}, 500);
			<?php } ?>
		});
	</script>
</div>
</div>
</form>