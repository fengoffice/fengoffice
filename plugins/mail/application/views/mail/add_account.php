<?php
$genid = gen_id();
set_page_title($mailAccount->isNew() ? lang('add mail account') : lang('edit mail account'));
if (!$mailAccount->isNew() && $mailAccount->canDelete(logged_user())) {
	add_page_action(lang('delete mail account'),  "javascript:og.promptDeleteAccount(".$mailAccount->getId().");", 'ico-delete');
}
$logged_user_settings = MailAccountContacts::getByAccountAndContact($mailAccount, logged_user());
if (!$logged_user_settings instanceof MailAccountContact) {
	$logged_user_can_edit = $mailAccount->isNew();
	$user_settings = array();
} else {
	$logged_user_can_edit = $logged_user_settings->getCanEdit();
	$user_settings = array(
		'is_default' => $logged_user_settings->getIsDefault(),
		'sender_name' => $logged_user_settings->getSenderName(),
		'signature' => $logged_user_settings->getSignature(),
	);
}

/* @var $mailAccount MailAccount */
if ($mailAccount->getContactId() == logged_user()->getId() || logged_user()->isAdministrator()) {
	// the creator of the account and superadmins can always edit it
	$logged_user_can_edit = true;
}

$uses_oauth2 = 0;
if (!$mailAccount->isNew()){
	$mail_acc_id = $mailAccount->getId();
	$uses_oauth2 = $mailAccount->getUsesOAuth2();	
	$oauth2_provider = $mailAccount->getOAuth2Provider();
}

$categories = array();
Hook::fire('mail_account_edit_categories', $mailAccount, $categories);

$loc = user_config_option('localization');
if (strlen($loc) > 2) $loc = substr($loc, 0, 2);


$oauth2_providers = array();
Hook::fire('oauth2_providers', $mailAccount, $oauth2_providers);

$show_auth_method_selector = count($oauth2_providers) > 0;

?>

<form onsubmit="return og.setDescription();" style="height: 100%; background-color: white" class="internalForm"
	action="<?php echo $mailAccount->isNew() ? get_url('mail', 'add_account') : $mailAccount->getEditUrl()?>"
	id="<?php echo $genid ?>submit-edit-form" method="post">
	
<input type="hidden" name="submitted" value="true" />
<input type="hidden" name="mail_account_id" id="<?php echo $genid ?>mail_account_id" value="<?php echo $mailAccount->isNew() ? '0' : $mailAccount->getId()?>" />

<div class="adminAddMailAccount">
<div class="coInputHeader">

  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<?php echo $mailAccount->isNew() ? lang('new mail account') : lang('edit mail account') ?>
	</div>
  </div>

  <div>
    <div class="coInputName">
		<div class="mail-account-item">
			<label for='<?php echo $genid ?>mailAccountFormName'><?php echo lang('mail account name')?>
				<span class="label_required">*</span>
				<span class="desc"><?php echo lang('mail account name description') ?></span>
			</label>
			<?php if ($logged_user_can_edit) {
				echo text_field('mailAccount[name]', array_var($mailAccount_data, 'name'), array('class' => 'title', 'tabindex'=>'10', 'id' => $genid.'mailAccountFormName'));
			} else {
				echo text_field('', array_var($mailAccount_data, 'name'), array('class' => 'title', 'tabindex'=>'10', 'id' => $genid.'mailAccountFormName', 'disabled' => 'disabled'));
			} ?>
		</div>
		
		<div class="mail-account-item">
			<label for="mailAccountFormEmail"><?php echo lang('mail address')?>
				<span class="label_required">*</span> <span class="desc"><?php echo lang('mail address description') ?></span>
			</label>
			<?php if ($logged_user_can_edit) {
				echo text_field('mailAccount[email_addr]', array_var($mailAccount_data, 'email_addr'), array('id' => 'mailAccountFormEmail', 'class' => 'long', 'tabindex'=>'20','onchange'=> 'og.autofillmailaccountinfo(this.value,\''.$genid.'\',0)'));
			} else {
				echo text_field('', array_var($mailAccount_data, 'email_addr'), array('id' => 'mailAccountFormEmail', 'tabindex'=>'20', 'class' => 'long', 'disabled' => 'disabled'));
			} ?>
			<div style="color:#e22;" id="<?php echo $genid ?>autoconfigmessage"> </div>
			
		</div>
	</div>
	
	<div class="coInputButtons">
		<?php echo submit_button($mailAccount->isNew() ? lang('add mail account') : lang('save changes'), '',  array('style'=>'margin-top:24px;margin-left:10px')) ?>
	</div>
	<div class="clear"></div>
  </div>

</div>

<div class="coInputMainBlock">

	<div id="<?php echo $genid?>tabs" class="edit-form-tabs">
	
		<ul id="<?php echo $genid?>tab_titles">
		
		<?php if ($logged_user_can_edit) { ?>
			<li><a href="#<?php echo $genid?>incoming_settings_div"><?php echo lang('incoming settings') ?></a></li>
			<li><a href="#<?php echo $genid?>smtp_settings_div"><?php echo lang('smtp settings') ?></a></li>
		<?php } ?>	
			
			<li><a href="#<?php echo $genid?>other_settings_div"><?php echo lang('personal settings') ?></a></li>
			
		<?php if ($logged_user_can_edit) { ?>	
			<li><a href="#<?php echo $genid?>account_permissions_div"><?php echo lang('mail account permissions') ?></a></li>
			
		<?php } ?>
		
		<?php foreach ($categories as $category) {
				if (array_var($category, 'hidden')) continue; 
		?>
			<li><a href="#<?php echo $genid . $category['id'] ?>"><?php echo $category['name'] ?></a></li>
		<?php } ?>
		
		</ul>

	
	
<?php if ($logged_user_can_edit) { ?>

	<div id="<?php echo $genid ?>incoming_settings_div" class="form-tab">
		<!-- Show connected auth account if any -->
		<?php 
			if(!$mailAccount->isNew() && $uses_oauth2){
				if($oauth2_provider == 'microsoft'){
					$serialized_token = $mailAccount->getColumnValue('oauth2_access_token');
					$accessToken = unserialize(base64_decode($serialized_token));
					$account_id = $mailAccount->getId();
					$is_mail = true;
					if (isset($accessToken) && is_valid_microsoft_access_token($account_id, $accessToken, $is_mail)) {
						$microsoft_logo = get_image_url('16x16/microsoft.png');
						$image = '<img src="' . $microsoft_logo . '" style="margin-right: 5px;" alt="Microsoft" />';
						echo '<div class="mail-account-item">';
						echo '<label for="mailAccountFormEmail">'.'Account connected to:'.'</label>';
						echo '<div><b>' . 'Microsoft' . '</b></div>';
						echo '</div>';
						echo '<div class="mail-account-item">';
						echo '<div>'.'<a href="#" onclick="og.openLink(og.getUrl(\'microsoft\', \'disconnect_from_microsoft_mail_account\', {account_id: '.$mailAccount->getId().'})); return false;">'.$image.'Disconnect from Microsoft account'.'</a>'.'</div>';
						echo '</div>';
						$show_auth_method_selector = false;			
					}
				}
			}
		?>
		<!-- <a href="<?php echo get_url('microsoft', 'connect_to_microsoft_mail_account', array('controller'=>'microsoft', 'action' => 'reload_current_panel', 'account_id'=>$mailAccount->getId(), 'id'=>'dummy')) ?>">Connect to Microsoft 365</a> 
		<br />
		<a href="<?php echo get_url('microsoft', 'get_microsoft_imap_emails', array('controller'=>'microsoft', 'action' => 'reload_current_panel', 'account_id'=>$mailAccount->getId(), 'id'=>'dummy')) ?>">Download emails</a>
		<br /> -->
		<!-- Authentication method -->
		<div class="mail-account-item dataBlock" style="<?php echo $show_auth_method_selector ? '' : 'display:none;' ?>">
			<label>
				<?php echo lang('auth method')?>
			</label>
			<?php
				$options = array(
					option_tag(lang('basic'), 0, !$mailAccount->getUsesOauth2() ? array("selected"=>"selected") : null),
					option_tag(lang('oauth2'), 1, $mailAccount->getUsesOauth2() ? array("selected"=>"selected") : null),
				);
				echo select_box('mailAccount[uses_oauth2]', $options, array('id' => $genid . 'uses_oauth2'));
			?>
		</div>
		<!-- Select provider if using oauth2 -->
		<div id="<?php echo $genid ?>oauth2_settings" style="<?php echo $mailAccount->getUsesOauth2() && $show_auth_method_selector ? '' : 'display:none;' ?>">
			<label><?php echo lang('select a provider')?>:</label>

			<?php 
				$provider_options = array();
				
				foreach($oauth2_providers as $key => $provider) {
					$selected = $key == 0 ? array("selected"=>"selected") : null;
					$provider_options[] = option_tag($provider['name'], $provider['code'], $selected);
				}

				echo select_box('mailAccount[oauth2_provider]', $provider_options, array('id' => $genid . 'oauth2_provider'));
			
				foreach($oauth2_providers as $key => $provider) {
					$display = $key == 0 ? '' : 'display:none;';
					$display .= 'margin-top:10px; margin-bottom:10px;';
					echo '<div id="' . $genid . 'oauth2_provider_' . $provider['code'] . '" style="' . $display . '">';
					$image = '';
					if(isset($provider['logo'])) {
						$image = '<img src="' . $provider['logo'] . '" style="margin-right: 5px;" alt="' . $provider['name'] . '" />';
					}
					$call_to_connect = $image . lang('connect to') . ' ' . $provider['name'];
					echo '<a href="' . $provider['connect_url'] . '">' .$call_to_connect . '</a>';
					echo '</div>';
				}
			?>
			
			<!-- <a href="<?php echo get_url('microsoft', 'connect_to_microsoft_mail_account', array('controller'=>'microsoft', 'action' => 'reload_current_panel', 'account_id'=>$mailAccount->getId(), 'id'=>'dummy')) ?>">Connect to Microsoft 365</a> 
			<br />
			<a href="<?php echo get_url('microsoft', 'get_microsoft_imap_emails', array('controller'=>'microsoft', 'action' => 'reload_current_panel', 'account_id'=>$mailAccount->getId(), 'id'=>'dummy')) ?>">Download emails</a>
			<br /> -->
			
		</div>

		<div id="<?php echo $genid ?>basic_settings" style="<?php echo $mailAccount->getUsesOauth2() ? 'display:none;' : '' ?>">

		<div class="mail-account-item dataBlock">
			<label for="<?php echo $genid ?>email">
				<?php echo lang('mail account id')?><span class="label_required">*</span>
			</label>
			<?php echo text_field('mailAccount[email]', array_var($mailAccount_data, 'email'), 
					array('id' => $genid.'email', 'class' => 'medium', 'tabindex'=>'30', 'readonly' => 'readonly')) ?>
			<span class="desc"><?php echo lang('mail account id description') ?></span>
		</div>

		<div class="mail-account-item dataBlock">
			<label for="<?php echo $genid?>password">
				<?php echo lang('password')?><span class="label_required">*</span>
			</label>
			<?php echo password_field('mailAccount[password]', array_var($mailAccount_data, 'password'), 
					array('id' => $genid.'password', 'class' => 'medium', 'tabindex'=>'40', 'readonly' => 'readonly')) ?>
			<span class="desc"><?php echo lang('mail account password description') ?></span>
		</div>
		
		<div class="mail-account-item dataBlock" id="<?php echo $genid?>auth_error" style="display:none;">
			<label></label>
			<div id="<?php echo $genid?>auth_error_msg" class="mail-error-msg"></div>
		</div>

		<div class="mail-account-item dataBlock">
			<label for="<?php echo $genid ?>server">
				<?php echo lang('server address')?><span class="label_required">*</span>
			</label>
			<?php echo text_field('mailAccount[server]', array_var($mailAccount_data, 'server'), array('id' => $genid.'server', 'class' => 'medium', 'tabindex'=>'50')) ?>
			<span class="desc"><?php echo lang('mail account server description') ?></span>
		</div>

		<div class="mail-account-item dataBlock">
			<label for="<?php echo $genid ?>method"><?php echo lang('email connection method')?></label><?php
				$options = array();
				$attributes = array();
				if (array_var($mailAccount_data, 'is_imap', false)) {
					$attributes['selected'] = "selected";
				}
				$options[] = option_tag(lang('imap'), '1', $attributes);
				$attributes = array();
				if (!array_var($mailAccount_data, 'is_imap', false)) {
					$attributes['selected'] = "selected";
				}
				$options[] = option_tag(lang('pop3'), '0', $attributes);
				$onchange = "var ssl = document.getElementById('$genid' + 'sslport');var folders = document.getElementById('$genid' + 'folders');var imap_options = document.getElementById('$genid' + 'imap_options'); if (this.value == 1) { folders.style.display = 'block'; imap_options.style.display = 'block'; ssl.value = '993'; } else { folders.style.display = 'none'; imap_options.style.display = 'none'; ssl.value = '995'; }";
				echo '<div style="float:left;">'. select_box('mailAccount[is_imap]', $options, array("onchange" => $onchange, 'tabindex' => '60', 'id' => $genid . 'method')) . '</div>';
			?>
		</div>
		<div class="mail-account-item dataBlock"> 
			<label for="<?php echo $genid ?>ssl" class=""><?php echo lang('incoming ssl') ?></label><?php
			$onchange = "var div = document.getElementById('$genid' + 'ssl_options');if(this.checked) div.style.display='block';else div.style.display='none';";
				echo checkbox_field('mailAccount[incoming_ssl]', array_var($mailAccount_data, 'incoming_ssl'), array('id' => $genid.'ssl', 'tabindex'=>'70', 'onclick' => $onchange, 'style'=>'margin: 5px;')) ?>
		</div>

		<div id="<?php echo $genid?>ssl_options" <?php if (!array_var($mailAccount_data, 'incoming_ssl')) echo 'style="display:none"'; ?>>
			<div class="mail-account-item dataBlock" id="<?php echo $genid ?>sslportdiv">
				<?php echo label_tag(lang('incoming ssl port'), 'mailAccountFormIncomingSslPort') ?>
				<?php echo text_field('mailAccount[incoming_ssl_port]', array_var($mailAccount_data, 'incoming_ssl_port', 995), array('id' => $genid.'sslport', 'tabindex'=>'120')) ?>
			</div>
	
			<div class="mail-account-item dataBlock" id="<?php echo $genid ?>sslverifypeerdiv">
				<?php echo label_tag(lang('incoming ssl verify peer'), $genid.'sslverifypeer') ?>
				<?php echo checkbox_field('mailAccount[incoming_ssl_verify_peer]', array_var($mailAccount_data, 'incoming_ssl_verify_peer'), array('id' => $genid.'sslverifypeer', 'tabindex'=>'121', 'style'=>'margin: 5px;')) ?>
				<div class="desc" style="width:calc(100% - 240px); float:right; margin-top:2px;"><?php echo lang('incoming ssl verify peer desc')?></div>
				<div class="clear"></div>
				
			</div>
		</div>
		
		<div class="mail-account-item dataBlock">
			<div style="width:200px;display:inline-block;">
				<a href="#" onclick="og.checkMailAccountConnection('<?php echo $genid?>');" style="text-decoration:underline;"><?php echo lang("check connection data")?></a>
			</div>
			<span class="desc"><?php echo lang('check connection data link description') ?></span>
		</div>

		<div class="mail-account-item dataBlock" id="<?php echo $genid ?>folders" style="padding:5px 0;<?php if (!array_var($mailAccount_data, 'is_imap', false)) echo 'display:none'; ?>">

			<div id="<?php echo $genid ?>imap_folders"><?php
				tpl_assign('imap_folders', isset($imap_folders) ? $imap_folders : array());
				tpl_assign('can_detect_special_folders', isset($can_detect_special_folders) && $can_detect_special_folders);
				tpl_assign('genid', $genid);
				tpl_display(get_template_path("fetch_imap_folders", "mail")) ?>
			</div>
		</div>

		</div>
		
		<div class="mail-account-item dataBlock">
			<label for="mailAccountDelMailFromServer">
				<?php echo lang('delete mails from server')?>
			</label>
			<?php $del_from_server = array_var($mailAccount_data, 'del_from_server', 0) ?>
			<div style="float:left;padding-right:10px;"><?php 
				echo yes_no_widget('mailAccount[del_mails_from_server]', 'mailAccountDelMailFromServer', $del_from_server > 0, lang('yes'), lang('no'), 130);
			?></div>
			<?php echo '<span style="margin-left: 10px">' . lang('after') . '</span>'?>
			<?php echo text_field('mailAccount[del_from_server]', $del_from_server <= 0 ? 1 : $del_from_server, array('id' => 'mailAccountDelFromServer', 'tabindex'=>'140', 'style'=>'width:25px')) ?>
			<?php echo lang('days'); ?>.&nbsp;
			<span class="desc"><?php echo lang('mail account delete mails from server description') ?></span>
		</div>
		
		<div id="<?php echo $genid?>imap_options" style="<?php if (!array_var($mailAccount_data, 'is_imap', false)) echo 'display:none'; ?>">
		
		<div id="<?php echo $genid ?>readOnServer" class="mail-account-item dataBlock">
			<label for="mailAccountMarkReadOnServer">
				<?php echo lang('mark as read mails from server')?>
			</label>&nbsp;
			<?php $mark_read_on_server = array_var($mailAccount_data, 'mark_read_on_server', 0) ?>
			<?php echo yes_no_widget('mailAccount[mark_read_on_server]', 'mailAccountMarkReadOnServer', $mark_read_on_server > 0, lang('yes'), lang('no'), 130) ?>
			
		</div>

		<div id="<?php echo $genid ?>get_read_state_from_server" class="mail-account-item dataBlock">
			<label for="mailAccountGetReadStateFromServer">
				<?php echo lang('get read state from server')?>
			</label>&nbsp;
			<?php $get_read_state_from_server = array_var($mailAccount_data, 'get_read_state_from_server', 1) ?>
			<?php echo yes_no_widget('mailAccount[get_read_state_from_server]', 'mailAccountGetReadStateFromServer', $get_read_state_from_server > 0, lang('yes'), lang('no'), 130) ?>

		</div>
		
		<?php $null=null; Hook::fire('more_mail_account_imap_options', array('account' => $mailAccount, 'genid' => $genid), $null)?>
		<div class="clear"></div>
		</div>
		
		<div class="mail-account-item dataBlock" style="margin-top:20px;">
			<label>
				<?php echo lang ('classify mails on workspace') ?>
			</label>
				<?php
					if ($mailAccount->isNew()) {
						render_member_selectors(MailContents::instance()->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'hide_label' => true)); 						
					} else {
						render_member_selectors(MailContents::instance()->getObjectTypeId(), $genid, explode(',', $mailAccount->getMemberId())); 
					}
				?>
			<span class="desc"><?php echo lang ('classify mails on workspace desc') ?> </span>
		</div>
		
		<div class="clear"></div>
		
		<?php
			$more_options = array();
			Hook::fire('more_mail_account_incoming_options', $mailAccount, $more_options);
			foreach ($more_options as $option) { ?>
				<div class="mail-account-item dataBlock">
					<label><?php echo array_var($option, 'label')?></label>
					<?php echo array_var($option, 'html'); ?>
					<span class="desc"><?php echo array_var($option, 'description'); ?></span>
				</div>
				<div class="clear"></div>
				<?php
			}
		?>
		
	</div>

	<div id="<?php echo $genid ?>smtp_settings_div" class="form-tab">
		<div class="mail-account-item dataBlock">
			<label for="mailSmtpServer">
				<?php echo lang('smtp server')?> <span class="label_required">*</span>
			</label>
			<?php echo text_field('mailAccount[smtp_server]', array_var($mailAccount_data, 'smtp_server'), array('id' => 'mailSmtpServer', 'class' => 'medium', 'tabindex'=>'150')) ?>
			<span class="desc"><?php echo lang('mail account smtp server description') ?></span>
		</div>

		<div class="mail-account-item dataBlock">
			<label for="mailSmtpPort">
				<?php echo lang('smtp port')?> <span class="label_required">*</span>
			</label>
			<?php echo text_field('mailAccount[smtp_port]', array_var($mailAccount_data, 'smtp_port',25), array('id' => 'mailSmtpPort', 'class' => 'short', 'tabindex'=>'160')) ?>
			<span class="desc"><?php echo lang('mail account smtp port description') ?></span>
		</div>

		<div class="mail-account-item dataBlock">
			<label for="mailSmtpUseAuth">
				<?php echo lang('smtp use auth')?> <span class="label_required">*</span>
			</label> <?php
			$use_auth = array_var($mailAccount_data, 'smtp_use_auth',1);
			$options = array(
				option_tag(lang('no smtp auth'), 0, ($use_auth==0)?array('selected' => 'selected'):null),
				option_tag(lang('same as incoming'), 1, ($use_auth==1)?array('selected' => 'selected'):null),
				option_tag(lang('smtp specific'), 2, ($use_auth==2)?array('selected' => 'selected'):null),
				option_tag(lang('use oauth2'), 3, ($use_auth==3)?array('selected' => 'selected'):null),
			);
			echo select_box('mailAccount[smtp_use_auth]', $options, array(
				'id' => 'mailSmtpUseAuth', 'tabindex'=>'170',
				'onchange' => "if(document.getElementById('mailSmtpUseAuth').selectedIndex ==2) document.getElementById('smtp_specific_auth').style.display = 'block'; else document.getElementById('smtp_specific_auth').style.display = 'none';"
			)); ?>
			<span class="desc"><?php echo lang('mail account smtp use auth description') ?></span>
		</div>

		<div class="mail-account-item dataBlock" id = 'smtp_specific_auth' style='<?php if(array_var($mailAccount_data, 'smtp_use_auth',1)!=2) echo 'display:none';?>'>
			<div class="mail-account-item">
				<label for="mailSmtpUsername"><?php echo lang('smtp username')?> <span class="label_required"></span></label>
				<?php echo text_field('mailAccount[smtp_username]', array_var($mailAccount_data, 'smtp_username'), array('id' => 'mailSmtpUsername', 'class' => 'medium', 'tabindex'=>'180')) ?>
				<span class="desc"><?php echo lang('mail account smtp username description') ?></span>
			</div>

			<div class="mail-account-item">
				<label for="mailSmtpPassword">
					<?php echo lang('smtp password')?> <span class="label_required"></span>
				</label>
				<?php echo password_field('mailAccount[smtp_password]', array_var($mailAccount_data, 'smtp_password'), array('id' => 'mailSmtpPassword', 'class' => 'medium', 'tabindex'=>'190')) ?>
				<span class="desc"><?php echo lang('mail account smtp password description') ?></span>
			</div>
		</div>

		<div class="mail-account-item dataBlock">
			<label for="mailOutgoingTransportType">
				<?php echo lang('outgoing transport type')?><span class="label_required">*</span>
			</label> <?php
			$ottype = array_var($mailAccount_data, 'outgoing_transport_type', '');
			$t_options = array(
				option_tag(lang('no'), '', ($ottype=='')?array('selected' => 'selected'):null),
				option_tag('SSL', 'ssl', ($ottype=='ssl')?array('selected' => 'selected'):null),
				option_tag('TLS', 'tls', ($ottype=='tls')?array('selected' => 'selected'):null)
			);
			echo select_box('mailAccount[outgoing_transport_type]', $t_options,
			array('id' => 'mailOutgoingTransportType', 'tabindex'=>'200', 'onchange' => ""));
			?>
			<span class="desc"><?php echo lang('mail account outgoing transport type description') ?></span>
		</div>
		
		<?php
			$more_options = array();
			Hook::fire('more_mail_account_outgoing_options', $mailAccount, $more_options);
			foreach ($more_options as $option) { ?>
				<div class="mail-account-item dataBlock">
					<label><?php echo array_var($option, 'label')?></label>
					<?php echo array_var($option, 'html'); ?>
					<span class="desc"><?php echo array_var($option, 'description'); ?></span>
				</div>
				<div class="clear"></div>
				<?php
			}
		?>
		
	</div>
	
	
	
<?php } ?>
	
	<div id="<?php echo $genid ?>other_settings_div" class="form-tab">
		
		<div class="desc mail-account-item dataBlock"><?php echo lang('personal settings desc') ?></div>
		<div class="mail-account-item dataBlock">
			<label for="<?php echo $genid?>sender_name">
				<?php echo lang('mail account sender name') ?>
			</label>
			<?php echo input_field('sender_name', array_var($user_settings, 'sender_name', ''), array('id' => $genid."sender_name", 'tabindex' => 1210)) ?>
			<span class="desc"><?php echo lang('mail account sender name description') ?></span>
		</div>
		
		<?php if ($is_admin){?>
		<div class="mail-account-item dataBlock">
			<label for="<?php echo $genid ?>assign_to">
				<?php echo lang('assign to')?>
			</label>
			<?php			
			$select_box_attrib = array('id'=>$genid.'users_select_box');
			echo user_select_box('users_select_box', $mailAccount->getContactId(),$select_box_attrib);
		?>
			<span class="desc"><?php echo lang('assigned to description') ?></span>
							
		</div>
		<?php }?>
		
		<div class="mail-account-item dataBlock">
			<label for="<?php echo $genid ?>is_default">
				<?php echo lang('default account')?>
			</label>
			<div style="float:left;padding:4px;"><?php echo yes_no_widget('is_default', $genid.'is_default', array_var($user_settings, 'is_default', 0) > 0, lang('yes'), lang('no'), 1220) ?></div>
			<span class="desc"><?php echo lang('default account description') ?></span>
		</div>
		
		<div class="mail-account-item dataBlock">
    		<?php $ckEditorContent = $mailAccount->isNew() ? '' : array_var($user_settings, 'signature'); ?>
			<label for="mailSignature" ><?php echo lang('signature')?></label>
			<div class="clear"></div>
	            <div id="<?php echo $genid ?>ckcontainer">
	                <textarea id="<?php echo $genid ?>ckeditor" name="signature" rows="10"><?php echo clean($ckEditorContent) ?></textarea>
	            </div>
	    		<span class="desc"><?php echo lang('signature description') ?></span>
	        
	        
	        <script>
            var h = document.getElementById("<?php echo $genid ?>ckcontainer").offsetHeight;
            var editor = CKEDITOR.replace('<?php echo $genid ?>ckeditor', {
                height: (h-200) + 'px',
            	allowedContent: true,
                enterMode: CKEDITOR.ENTER_BR,
                shiftEnterMode: CKEDITOR.ENTER_BR,
                disableNativeSpellChecker: false,
                language: '<?php echo $loc ?>',
                customConfig: '',
            	contentsCss: ['<?php echo get_javascript_url('ckeditor/contents.css').'?rev='.product_version_revision();?>', '<?php echo get_stylesheet_url('og/ckeditor_override.css').'?rev='.product_version_revision();?>'],
                toolbar: [
							[	'Source','-','Font','FontSize','-','Bold','Italic','Underline','-', 'SpellChecker', 'Scayt','-',
								'Link','Unlink','-',
								'TextColor','BGColor','RemoveFormat','-',
								'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'
							]
						],
                on: {
                        instanceReady: function(ev) {
                                og.adjustCkEditorArea('<?php echo $genid ?>');
                                editor.resetDirty();
                        }
                    },
                fillEmptyBlocks: false,
            	removePlugins: 'scayt,liststyle,magicline',
                entities_additional : '#39,#336,#337,#368,#369'
            });

            og.setDescription = function() {					            		
                    var form = Ext.getDom('<?php echo $genid ?>submit-edit-form');
                    if (form.preventDoubleSubmit) return false;

                    setTimeout(function() {
                            form.preventDoubleSubmit = false;
                    }, 2000);

                    var editor = og.getCkEditorInstance('<?php echo $genid ?>ckeditor');
                    form['signature'].value = editor.getData();				                  

                    return true;
            };   
			</script>	
			<div class="clear"></div>
		</div>
	</div>
	
<?php  if ($logged_user_can_edit) { ?>
	<div id="<?php echo $genid ?>account_permissions_div" style="display:none;" class="form-tab">
		
		<div class="desc"><?php echo lang('mail account permissions desc')?></div>
		<?php
		$account_users = array();
		if (logged_user() instanceof Contact) {
			$conditions = '`user_type` <> 0 AND `disabled` = 0';
			Hook::fire("mail_account_permissions_extra_cond", array('account' => $mailAccount), $conditions);
			$account_users = Contacts::findAll(array('conditions' => $conditions));
		}
		$account_user_ids = is_array($mailAccountUsers) ? array_keys($mailAccountUsers) : array();
		$num = 0;
		$alt = true;
		foreach ($account_users as $user) {
			$num++;
			$alt = !$alt; ?>
			<div class="account_permissions_user<?php if ($alt) echo " odd"; ?>">
				<div class="user_picture cardIcon"><img src="<?php echo $user->getPictureUrl();?>"></img></div>
				<div class="user_name">
					<?php echo clean($user->getObjectName()) ?>
				</div> <?php
				if (in_array($user->getId(), $account_user_ids)) {
					if (array_var($mailAccountUsers[$user->getId()], 'can_edit')) {
						$access = 'write';
					} else {
						$access = 'read';
					} 
				} else {
					$access = 'none';
				} ?>
				<div class="user_access">
					<select name="user_access[<?php echo $user->getId() ?>]" tabindex="<?php echo 200 + $num ?>">
						<option value="none" <?php if ($access == 'none') echo 'selected="selected"'; ?>><?php echo lang('cannot access account')?></option>
						<option value="read" <?php if ($access == 'read') echo 'selected="selected"'; ?>><?php echo lang('can view account emails')?></option>
						<option value="write" <?php if ($access == 'write') echo 'selected="selected"'; ?>><?php echo lang('can view account emails and edit')?></option>
					</select>
				</div>
				<div class="separator"></div>
			</div> <?php
		} ?>
	</div>
		
		
		<?php foreach ($categories as $category) { ?>
		<div id="<?php echo $genid . $category['id'] ?>" class="form-tab">
			<?php echo $category['content'] ?>
		</div>
		<?php } ?>
		
		
<?php } ?>
	
<?php echo submit_button($mailAccount->isNew() ? lang('add mail account') : lang('save changes'), 's', array('tabindex'=>'1240')) ?>
</div>
</div>
</div>
</form>

<style>
.mail-error-msg {
    border-radius: 2px;
    background-color: #FFDADA;
    color: #330000;
    padding: 10px;
    display: inline-block;
    min-width: 400px;
    width: calc(100% - 300px);
}
.mail-error-msg a {
	text-decoration: underline;
}
</style>

<script>
	og.goToGmailUnlockCaptcha = function(genid) {
		
		var msg = lang('gmail authentication failed need unlockcaptcha');
		var link = '<a href="https://www.google.com/accounts/DisplayUnlockCaptcha" id="'+genid+'unlockcaptcha" target="_blank">'+ lang('unlock captcha') +'</a>';

		var msg1 = lang('if auth problem persists');
		var link1 = '<a href="https://support.google.com/accounts/answer/6010255" target="_blank">'+ lang('allowing less secure apps to access your account') +'</a>'

		var msg2 = lang('more information can be found in');
		var link2 = '<a href="https://support.google.com/mail/accounts/answer/78754" id="'+genid+'unlockcaptcha_info" target="_blank">'+ lang('solve gmail login issues') +'</a>';

		var msg3 = lang('after completing the process please try using the fetch imap folders link to check the connection');
		
		var html = '<div>' + msg + '</div>';
		html += '<div>' + link + '</div>';
		html += '<div>' + msg1 + '</div>';
		html += '<div>' + link1 + '</div>';
		html += '<div style="margin-top:10px;">' + msg2 + '</div>';
		html += '<div>' + link2 + '</div>';
		html += '<div style="margin-top:10px;">' + msg3 + '</div>';
		
		$("#"+genid+"auth_error_msg").html(html);
		$("#"+genid+"auth_error").show();

	}

	og.autofillsyncinfo = function (genid, sync){		
		if (sync){					
			var method = document.getElementById(genid+'method');			
			if (method[0].selected){			
				var serverAddress = document.getElementById(genid+'server');
				var serverAddressSync = document.getElementById(genid+'sync_server');				
				serverAddressSync.value = serverAddress.value;
				var emailAddress = document.getElementById(genid+'email');
				var emailAddressSync = document.getElementById(genid+'sync_addr');
				emailAddressSync.value = emailAddress.value;
				var ssl = document.getElementById(genid+'ssl');
				var sslSync = document.getElementById(genid+'sync_ssl');
				sslSync.checked = ssl.checked;
				var sslport = document.getElementById(genid+'sslport');
				var sslportSync = document.getElementById(genid+'sync_sslport');
				sslportSync.value = sslport.value;
				passInput = document.getElementById(genid+'sync_pass');
				var sync = 'og.fetchImapFoldersSync(\''+genid+'\')';
				passInput.setAttribute('onchange',sync);
			}
		}
	}	


	og.autofillmailaccountinfo = function (addres , genid, sync) {
		atIndex = addres.indexOf("@");
		var autoconf = false;
		if (atIndex != -1) {
			domain = addres.substring(atIndex+1);
			domainName = domain.substring(0,domain.indexOf('.')).toLowerCase();
			messageDiv = document.getElementById(genid+'autoconfigmessage');
			messageDiv.innerHTML = "";
			switch (domainName) {
				case 'hotmail':
					autoconf = true;					
					serverAddres = document.getElementById(genid+'server');
					serverAddres.value = 'pop3.live.com';
					smtpServer = document.getElementById('mailSmtpServer');
					smtpServer.value = 'smtp.live.com';
					email = document.getElementById(genid+'email');
					email.value = addres;
					smtpPort = document.getElementById('mailSmtpPort');
					smtpPort.value = '25';
					method = document.getElementById(genid+'method');
					method[1].selected = 'selected';
					method[0].selected = '';
					useSsl = document.getElementById(genid+'ssl');
					useSsl.checked = 'checked';
					ssl = document.getElementById(genid + 'sslport');
					ssl.value = '995';
					divSsl = document.getElementById(genid + 'sslportdiv');
					divSsl.style.display = 'block';
					connectionType = document.getElementById('mailOutgoingTransportType');
					connectionType[1].selected = 'selected';
					connectionType[0].selected = '';
					connectionType[2].selected = '';
					break;
				case 'gmail':
				case 'googlemail':
					autoconf = true;
					serverAddres = document.getElementById(genid+'server');
					serverAddres.value = 'imap.' + domainName + '.com';
					smtpServer = document.getElementById('mailSmtpServer');
					smtpServer.value = 'smtp.' + domainName + '.com';
					email = document.getElementById(genid+'email');
					email.value = addres;
					smtpPort = document.getElementById('mailSmtpPort');
					smtpPort.value = '465';
					method = document.getElementById(genid+'method');
					method[0].selected = 'selected';
					method[1].selected = '';
					ssl = document.getElementById(genid + 'sslport');
					folders = document.getElementById(genid + 'folders');
					folders.style.display = 'block';
					ssl.value = '993';
					useSsl = document.getElementById(genid+'ssl');
					useSsl.checked = 'checked';
					divSsl = document.getElementById(genid + 'sslportdiv');
					divSsl.style.display = 'block';
					//Ext.get(genid+'password').on('onchange',og.fetchImapFolders(genid));
					passInput = document.getElementById(genid+'password');
					var fun = 'og.fetchImapFolders(\''+genid+'\')';
					passInput.setAttribute('onchange',fun);
					connectionType = document.getElementById('mailOutgoingTransportType');
					connectionType[1].selected = 'selected';
					connectionType[0].selected = '';
					connectionType[2].selected = '';
					domainName = 'gmail';
					$("#"+genid+"imap_options").show();
					break;				
				case 'yahoo':
				case 'ymail':
				case 'rocketmail':
					autoconf = true;
					serverAddres = document.getElementById(genid+'server');
					serverAddres.value = 'plus.pop.mail.yahoo.com';
					smtpServer = document.getElementById('mailSmtpServer');
					smtpServer.value = 'plus.smtp.mail.yahoo.com';
					email = document.getElementById(genid+'email');
					email.value = addres.substring(0,addres.indexOf('@'));
					smtpPort = document.getElementById('mailSmtpPort');
					smtpPort.value = '465';
					method = document.getElementById(genid+'method');
					method[1].selected = 'selected';
					method[0].selected = '';
					useSsl = document.getElementById(genid+'ssl');
					useSsl.checked = 'checked';
					divSsl = document.getElementById(genid + 'sslportdiv');
					divSsl.style.display = 'block';
					connectionType = document.getElementById('mailOutgoingTransportType');
					connectionType[1].selected = 'selected';
					connectionType[0].selected = '';
					connectionType[2].selected = '';
					domainName = 'yahoo';
					break;
				default:
					return;
			}
			$("#sync_classify_remove_from_inbox_in_serverNo").attr("checked","checked");
			og.autofillsyncinfo(genid, sync);			
			if (autoconf) {
				messageDiv.innerHTML = lang('autoconfig ' + domainName + ' message');
			}
		}
	};

	<?php if ($logged_user_can_edit) { ?>
		Ext.get('<?php echo $genid ?>mailAccountFormName').focus();
	<?php } else { ?>
		Ext.get('<?php echo $genid ?>sender_name').focus();
	<?php } ?>


	og.checkMailAccountConnection = function(genid, silent) {
		var account_id = document.getElementById(genid + 'mail_account_id').value;
		var server = document.getElementById(genid + 'server').value;
		var email = document.getElementById(genid + 'email').value;
		var password = document.getElementById(genid + 'password').value;
		var ssl = document.getElementById(genid + 'ssl').checked ? document.getElementById(genid + 'ssl').value : '';
		var sslport = document.getElementById(genid + 'sslport').value;
		var is_imap = document.getElementById(genid + 'method').value;
		
		og.openLink(og.getUrl('mail', 'check_mail_account_connection', {
			account_id: account_id,
			server: server,
			email: email,
			pass: password,
			ssl: ssl,
			port: sslport,
			is_imap: is_imap,
			genid: genid,
			hide_success_msg: silent ? '1' : '0'
		}), {
			preventPanelLoad: true
		});
	};

	og.setSmtpConfigurations = function(server) {
		if(server == 'microsoft') {
			$('#mailSmtpServer').val('smtp.office365.com');
			$('#mailSmtpPort').val('587');
			$('#mailOutgoingTransportType').val('tls');
			$('#mailSmtpUseAuth').val('3');
		} else {
			$('#mailSmtpServer').val('');
			$('#mailSmtpPort').val('');
			$('#mailOutgoingTransportType').val('none');
			$('#mailSmtpUseAuth').val('0');
		}
	};

	<?php if($uses_oauth2 && $oauth2_provider == 'microsoft') { ?>
		og.setSmtpConfigurations('microsoft');
	<?php } ?>

	$("#<?php echo $genid?>uses_oauth2").change(function(){
		if ($(this).val() == 0) {
			$("#<?php echo $genid?>oauth2_settings").hide();
			og.setSmtpConfigurations('other');
			$("#<?php echo $genid?>basic_settings").show();
		} else {
			$("#<?php echo $genid?>oauth2_settings").show();
			$("#<?php echo $genid?>oauth2_provider").trigger('change');
			$("#<?php echo $genid?>basic_settings").hide();
		}
	});

	$("#<?php echo $genid?>oauth2_provider").change(function() {
		if ($(this).val() == 'microsoft') {
			$("#<?php echo $genid?>oauth2_provider_microsoft").show();
			og.setSmtpConfigurations('microsoft');
		}
	});

	$(function() {
		$("#<?php echo $genid?>tabs").tabs();
		setTimeout(function() {
			// remove attribute readonly (setted to prevent autofill)
			$("#<?php echo $genid?>email").removeAttr("readonly");
			$("#<?php echo $genid?>password").removeAttr("readonly");
		}, 200);
		
		<?php
		$accountUser = MailAccountContacts::getByAccountAndContact($this, logged_user());
		$can_edit_account = $accountUser instanceof MailAccountContact && $accountUser->getCanEdit();

		if (!$mailAccount->isNew() && $can_edit_account) { ?>
			og.checkMailAccountConnection('<?php echo $genid?>', true);

			$('#<?php echo $genid?>password').change(function() {
				og.checkMailAccountConnection('<?php echo $genid?>');
			});
		<?php
		}
		?>
	});
</script>

