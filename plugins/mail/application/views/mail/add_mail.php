<?php
	require_javascript('og/modules/linkToObjectForm.js'); 
	require_javascript('og/ObjectPicker.js');
	//require_javascript('AddMail.js', $this->plugin_name); 
 
	set_page_title( lang('write mail'));
  
	$genid = gen_id();
 
	add_page_action(lang('send mail'), "javascript:document.getElementById('sendMail').click();", "mail send blue",null,null,true);
	add_page_action(lang('save')." ".lang('draft'), "javascript:document.getElementById('saveMail').click();", "mail",null,null,true);
	add_page_action(lang('discard'), "javascript:document.getElementById('discardMail').click();","mail",null,null,true);
 	
	$type = array_var($mail_data, 'type', user_config_option('last_mail_format'));
	if (!$type) $type = 'plain';
	$object = $mail;
	$draft_edit = array_var($mail_data, 'draft_edit', false);
	$mail_to = isset($mail_to) ? $mail_to : array_var($mail_data, 'to');
	if (!isset($link_to_objects)) $link_to_objects = null;
	
	$visible_cps = CustomProperties::countVisibleCustomPropertiesByObjectType($object->getObjectTypeId());
?>
<input type="hidden" id="<?php echo $genid ?>signatures" />
<script>
og.attachContents = <?php echo user_config_option('attach_docs_content') ? '1' : '0'; ?>;

var sig = Ext.getDom('<?php echo $genid ?>signatures');
sig.accountSignatures = [];
sig.actualTextSignature = '';
sig.actualHtmlSignature = '';

</script>
<div id="<?php echo $genid ?>main_div" style="height:100%; overflow-y: hidden;">
<form style="height:100%;background-color:white;" id="<?php echo $genid ?>form" name="frmMail"  class="internalForm" action="<?php echo $mail->getSendMailUrl()?>" method="post"  onsubmit="return og.checkFrom() && og.mailSetBody('<?php echo $genid ?>')">
<input type="hidden" name="instanceName" value="<?php echo $genid?>" />
<input type="hidden" name="mail[body]" value="" />
<input type="hidden" name="mail[sendBtnClick]" id="<?php echo $genid ?>sendBtnClick" value="false" />
<input type="hidden" name="mail[isDraft]" id="<?php echo $genid ?>isDraft" value="true" />
<input type="hidden" name="mail[id]" id="<?php echo $genid ?>id" value="<?php echo  array_var($mail_data, 'id') ?>" />
<input type="hidden" name="mail[hf_id]" id="<?php echo $genid ?>hf_id" value="<?php echo $genid ?>id" />
<input type="hidden" name="mail[isUpload]" id="<?php echo $genid ?>isUpload" value="false" />
<input type="hidden" name="mail[autosave]" id="<?php echo $genid ?>autosave" value="false" />
<input type="hidden" name="mail[link_to_objects]" id="<?php echo $genid ?>link_to_objects" value="<?php echo $link_to_objects?>" />

<input type="hidden" name="mail[conversation_id]" value="<?php echo array_var($mail_data, 'conversation_id') ?>" />
<input type="hidden" name="mail[in_reply_to_id]" value="<?php echo array_var($mail_data, 'in_reply_to_id') ?>" />
<input type="hidden" name="mail[original_id]" value="<?php echo array_var($mail_data, 'original_id') ?>" />
<input type="hidden" name="mail[last_mail_in_conversation]" value="<?php echo array_var($mail_data, 'last_mail_in_conversation') ?>" />
<input type="hidden" name="mail[pre_body_fname]" value="<?php echo array_var($mail_data, 'pre_body_fname') ?>" />
<?php 

	tpl_display(get_template_path('form_errors'));
	$account = isset($default_account) ? $default_account : $mail_accounts[0];
	$account_member_id = $mail_accounts[0] instanceof MailAccount ? $mail_accounts[0]->getMemberId() : 0;
    $acc_id = array_var($mail_data, 'account_id', $account instanceof MailAccount ? $account->getId() : 0);
    $acc_id = isset($default_account_replay) ? $default_account_replay : $acc_id;
    
    $orig_textsignature = $orig_htmlsignature = "";
    ?><script type="text/javascript">
    		sig.actualTextSignature = sig.actualHtmlSignature = "";
    </script> <?php
    foreach ($mail_accounts as $m_acc) {
    	$user_settings = MailAccountContacts::getByAccountAndContact($m_acc, logged_user());
    	if ($user_settings instanceof MailAccountContact) {
    		$sig = $user_settings->getSignature();
    	} else {
    		$sig = "";
    	}
    	//$sig = nl2br($sig);
    	$htmlsig = str_replace(array("\r", "\n"), "", "<div class=\"fengoffice_signature\">$sig</div>");
    	$textsig = html_to_text($sig);
    	if ($acc_id) {
	    	if ($m_acc->getId() == $acc_id) {
	    		$orig_textsignature = $textsig;
	    		$orig_htmlsignature = $htmlsig;
	    ?><script type="text/javascript">
	    		sig.actualTextSignature = <?php echo json_encode($textsig) ?>;
	    		sig.actualHtmlSignature = <?php echo json_encode($htmlsig) ?>;
	    </script> <?php
	    	}
    	}
?>
<script type="text/javascript">
		sig.accountSignatures[sig.accountSignatures.length] = {acc:'<?php echo $m_acc->getId() ?>', htmlsig:<?php echo json_encode($htmlsig) ?>, textsig:<?php echo json_encode($textsig) ?>};
</script>
<?php } ?>

<input type="hidden" id="<?php echo $genid ?>hf_mail_contacts" value="" />


<div id="textarea_new"></div>


<div class="mail" id="<?php echo $genid ?>mail_div" style="height:100%;">
<div class="coInputHeader" id="<?php echo $genid ?>header_div" style="border-bottom:0px none;">

  	<div style="display: none;">
  		<table><tr><td>
  			<?php echo submit_button(lang('send mail'), '', 
  			array('id'=>'sendMail','onclick'=>"og.checkAttach();"))?>
  		</td><td>
  			<?php echo submit_button(lang('save')." ".lang('draft'), '', 
  			array('id'=>'saveMail','onclick'=>"og.setHfValue('$genid', 'isDraft', 'true');og.stopAutosave('$genid');")) ?>
  		</td><td>
  			<?php
  			$strDisabled = "";//array_var($mail_data, 'id') == ''?'disabled':'';
  			echo submit_button(lang('discard'), '', 
  			array('id'=>'discardMail','onclick'=>"if (!confirm('" . escape_single_quotes(lang('confirm discard email')) . "')) return false; else {var p = og.getParentContentPanel(Ext.get('{$genid}form'));Ext.getCmp(p.id).setPreventClose(false);} og.setDiscard('$genid', true);og.stopAutosave('$genid');",$strDisabled=>'')) ?>
  		</td>
  		</tr></table>
  	</div>
  
	<div>
		<table style="width:100%"><tr><td style="width: 60px;">
    	<label for='mailTo' style="margin:0;"><?php echo lang('mail to')?> <span class="label_required">*</span></label>
    	</td><td>
    	<?php echo autocomplete_textarea_field('mail[to]', $mail_to, array(), 30, 
    		array('class' => 'title', 'tabindex'=>'10', 'id' => $genid . 'mailTo' )); ?>
    	<?php //echo autocomplete_emailfield('mail[to]', $mail_to, $allEmails, '', 
    		//array('class' => 'title', 'tabindex'=>'10', 'id' => $genid . 'mailTo', 'style' => 'width:100%;', 'onchange' => "og.addContactsToAdd('$genid')"), false); ?>
    	</td></tr></table>
	</div>
  
 	<div id="add_mail_CC" style="padding-top:2px;display:<?php echo (substr_count(array_var($mail_data, 'cc'), '@') > 0 ? 'block' : 'none')?>;">
		<table style="width:100%"><tr><td style="width: 60px;">
		<label for="mailCC" style="margin:0;"><?php echo lang('mail CC')?> </label>
		</td><td>
		<?php echo autocomplete_textarea_field('mail[cc]', array_var($mail_data, 'cc'), array(), 30, 
			array('class' => 'title', 'tabindex'=>'20', 'id' => $genid . 'mailCC' )); ?>
		<?php //echo autocomplete_emailfield('mail[cc]', array_var($mail_data, 'cc'), $allEmails, '', 
			//array('class' => 'title', 'tabindex'=>'20', 'id' => $genid . 'mailCC', 'style' => 'width:100%;', 'onchange' => "og.addContactsToAdd('$genid')"), false); ?>
		</td></tr></table>
	</div>
 	
 	<div id="add_mail_BCC" style="padding-top:2px;display:<?php echo (substr_count(array_var($mail_data, 'bcc'), '@') > 0 ? 'block' : 'none')?>;">
 		<table style="width:100%"><tr><td style="width: 60px;">
	    <label for="mailBCC" style="margin:0;"><?php echo lang('mail BCC')?></label>
	    </td><td>
	    <?php echo autocomplete_textarea_field('mail[bcc]', array_var($mail_data, 'bcc'), array(), 30, 
    		array('class' => 'title', 'tabindex'=>'30', 'id' => $genid . 'mailBCC' )); ?>
    	<?php //echo autocomplete_emailfield('mail[bcc]', array_var($mail_data, 'bcc'), $allEmails, '', 
    		//array('class' => 'title', 'tabindex'=>'30', 'id' => $genid . 'mailBCC', 'style' => 'width:100%;', 'onchange' => "og.addContactsToAdd('$genid')"), false); ?>
    	</td></tr></table>
	</div>
 	
	<div style="padding-top:2px;">
		<table style="width:100%"><tr><td style="width: 60px; padding-top:6px;">
    	<label for='mailSubject'><?php echo lang('mail subject')?></label>
    	</td><td>
    	<?php echo text_field('mail[subject]', array_var($mail_data, 'subject'), 
    		array('class' => 'title x-form-text', 'tabindex'=>'0', 'id' => $genid . 'mailSubject', 'style' => 'width:100%;height:32px;border-radius: 4px;padding-right: 0px;', 'autocomplete' => 'off')) ?>
    	</td></tr></table>
	</div>
	
	<?php $def_acc_id = isset($default_account) ? $default_account->getId() : (count($mail_accounts) > 0 ? $mail_accounts[0]->getId() : 0); ?>
	<input id="<?php echo $genid?>def_acc_id" type="hidden" name="def_acc_id" value="<?php echo $def_acc_id; ?>"/>
	
	<div id="add_mail_account" style="<?php echo ($def_acc_id != array_var($mail_data, 'account_id') ? "" : "display:none;")?> padding:5px 0;">
	  <table><tr><td style="width:60px">
	    <label for="mailAccount"><?php echo lang('mail from')?></label>
	  </td><td>
	    <?php echo render_select_mail_account('mail[account_id]',  $mail_accounts, isset($mail_data['account_id']) ? $mail_data['account_id'] : (isset($default_account) ? $default_account->getId() : (count($mail_accounts) > 0 ? $mail_accounts[0]->getId() : 0)),
	    array('id' => $genid . 'mailAccount', 'tabindex'=>'44', 'onchange' => "og.changeSignature('$genid', this.value);", "style" => "border:1px solid #B5B8C8;")) ?>
	    <span class="desc" style="margin-left:10px;"><?php echo lang('mail account desc') ?></span>
	  </td></tr></table>
	</div>
	
	<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>
	
	<div style="padding-top:5px">

		<a href="#" class="option" onclick="og.toggleAndBolden('add_mail_account', this);og.resizeMailDiv();" id="<?php echo $genid?>mailAccountCombo"><?php echo lang('mail from') ?></a> - 

		<a href="#" class="option" onclick="og.toggleAndBolden('add_mail_CC', this);og.resizeMailDiv();"><?php echo lang('mail CC') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('add_mail_BCC', this);og.resizeMailDiv();"><?php echo lang('mail BCC') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('add_mail_options', this);og.resizeMailDiv();"><?php echo lang('mail format options') ?></a> -
 		<a href="#" class="option" onclick="og.toggleAndBolden('add_mail_attachments', this);og.resizeMailDiv();"><?php echo lang('mail attachments') ?></a> -

		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_mail_add_contacts',this);og.resizeMailDiv();"><?php echo lang('mail add contacts') ?></a>
		<?php foreach ($categories as $category) { ?>
			- <a href="#" class="option" <?php if ($category['visible']) echo 'style="font-weight: bold"'; ?> onclick="og.toggleAndBolden('<?php echo $genid . $category['name'] ?>', this);og.resizeMailDiv();"><?php echo lang($category['name'])?></a>
		<?php } ?>
	</div>

  
	<div id="add_mail_options" style="display:none;">
		<fieldset>
	    <legend><?php echo lang('mail format options')?></legend>
	    <label><?php echo radio_field('mail[format]',$type=='html', array('id' => $genid . 'format_html','value' => 'html', 'tabindex'=>'45','onchange'=>"og.mailAlertFormat('$genid','html')")) ." ".lang('format html') ?></label>
	    <label><?php echo radio_field('mail[format]',$type=='plain', array('id' => $genid . 'format_plain','value' => 'plain', 'tabindex'=>'46', 'onchange'=>"og.mailAlertFormat('$genid','plain')"))." ".lang('format plain')  ?></label>
		</fieldset>
	</div>
	
	<div id="add_mail_attachments" style="display:none;">
 	<fieldset>
 	    <legend><?php echo lang('mail attachments')?></legend>
 	    <div id="<?php echo $genid ?>attachments" style="max-height: 60px; max-width: 550px; margin-bottom:5px;"></div>
 	<a href="#" onclick="og.attachFromWorkspace('<?php echo $genid ?>')">
 		<?php  echo lang('attach from fengoffice') ?>		
 	</a>
 	<br/>
 	<a href="#" onclick="og.attachFromFileSystem('<?php echo $genid ?>', '<?php echo $account_member_id?>')">
 		<?php echo lang ('attach from file system') ?>
 	</a>
 	<script type="text/javascript">
 	// add attachments
 	var container = document.getElementById('<?php echo $genid ?>attachments');
	<?php
		$attachs = array_var($mail_data, 'attachs');
		if (is_array($attachs)) {
			foreach ($attachs as $att) {
				$split = explode(':', $att);
				$icon_class = 'ico-file ico-' . str_replace(".", "_", str_replace("/", "-", $split[2]));
	?>
	og.addMailAttachment(container, {
		object_id: '<?php echo $split[1] . ":" . $split[2] . ":" . $split[3] ?>',
		manager: '<?php echo $split[0] ?>',
		name: '<?php echo $split[1] ?>',
		icocls: '<?php echo $icon_class ?>'
	});
	<?php 
			}
		}
	?>
 	</script>
 	</fieldset>
 	</div>
 	
 	<div id="<?php echo $genid ?>add_mail_add_contacts" style="display:none;">
 	<fieldset id="<?php echo $genid ?>fieldset_add_contacts">
 	    <legend><?php echo lang('mail add contacts')?></legend>
 	    <label id="<?php echo $genid ?>label_no_contacts"><?php echo lang('no contacts to add')?></label>
 	    <div id="<?php echo $genid ?>add_contacts_container"></div>
 	</fieldset>
 	</div>
 	
	<?php foreach ($categories as $category) { ?>
	<div <?php if (!$category['visible']) echo 'style="display:none"' ?> id="<?php echo $genid . $category['name'] ?>">
	<fieldset>
		<legend><?php echo lang($category['name'])?><?php if ($category['required']) echo ' <span class="label_required">*</span>'; ?></legend>
		<?php echo $category['content'] ?>
	</fieldset>
	</div>
	<?php } ?>
	
	<?php if (count($visible_cps) > 0) { ?>
		<div id="<?php echo $genid ?>add_custom_properties_div" style="<?php echo ($visible_cps > 0 ? "" : "display:none") ?>">
			<fieldset>
				<legend><?php echo lang('custom properties') ?></legend>
				<?php echo render_object_custom_properties($object, false) ?>
			</fieldset>
		</div>	
	<?php } ?>
  
</div>

<div id="<?php echo $genid ?>mail_body_container" style="height: 100%; overflow-y: auto">
    <?php 
    $display = ($type == 'html') ? 'none' : 'block';
    $display_fck = ($type == 'html') ? 'block' : 'none';
    
    $plain_body = $draft_edit ? array_var($mail_data, 'body') : "\n\n--\n$orig_textsignature" . array_var($mail_data, 'body');

    if (!$draft_edit) {
    	$body = array_var($mail_data, 'body');
    	$idx = stripos($body, '<body');
    	if (false) {
    		$end_tag = strpos($body, '>', $idx) + 1;
    		$html_body = utf8_substr($body, 0, $end_tag) . "<br />--<br />$orig_htmlsignature" . utf8_substr($body, $end_tag); 
    	} else {
    		$html_body = "<br />--<br />$orig_htmlsignature" . $body;
    	}
    } else $html_body = array_var($mail_data, 'body');
    
    echo textarea_field('plain_body', $plain_body, array('id' => $genid . 'mailBody', 'tabindex' => '50', 
    	'style' => "display:$display;width:97%;margin-left:1%;margin-right:1%; min-height:250px;", 
    	'onkeypress' => "if (!og.thisDraftHasChanges) og.checkMailBodyChanges();", 'autocomplete' => 'off')) ?>

    <div id="<?php echo $genid ?>ck_editor" style="display:<?php echo $display_fck ?>; width:100%; padding:0px; margin:0px; min-height:265px;overflow: hidden">
		<textarea style="display:none;" id="<?php echo $genid ?>ckeditor" tabindex="51"><?php echo clean($html_body) ?></textarea>
	</div>
</div>
</div>

</div>
</form>
</div>
<?php
	$loc = user_config_option('localization');
	if (strlen($loc) > 2) $loc = substr($loc, 0, 2);
?>
<script>
var genid = '<?php echo $genid ?>';
var focus_editor = false;
<?php if ($mail_to != "") { ?>
	focus_editor = true;
<?php } ?>
var h = document.getElementById(genid+"ck_editor").offsetHeight;
try {
var editor = CKEDITOR.replace(genid+'ckeditor', {
	height: (h) + 'px',
	enterMode: CKEDITOR.ENTER_BR,
	allowedContent: true,
	shiftEnterMode: CKEDITOR.ENTER_P,
	disableNativeSpellChecker: false,
	resize_enabled: false, //this causes a bug when pasting info into Google Chrome and other browsers 
	customConfig: '',
	contentsCss: og.getUrl('mail', 'get_mail_css'),
	language: '<?php echo $loc ?>',
	contentsCss: ['<?php echo get_javascript_url('ckeditor/contents.css').'?rev='.product_version_revision();?>', '<?php echo get_stylesheet_url('og/ckeditor_override.css').'?rev='.product_version_revision();?>'],
	toolbar: [
		['Bold','Italic','Underline','Strike','-',
		'Outdent','Indent','Blockquote','-',
		'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-',
		'NumberedList','BulletedList','-',
		'Link','Unlink','-',
		'Image','Table','HorizontalRule','Smiley','-',
		'Font','FontSize','-',
		'TextColor','BGColor','RemoveFormat']
	],
	keystrokes: [
		[ CKEDITOR.ALT + 121 /*F10*/, 'toolbarFocus' ],
		[ CKEDITOR.ALT + 122 /*F11*/, 'elementsPathFocus' ],

		[ CKEDITOR.SHIFT + 121 /*F10*/, 'contextMenu' ],

		[ CKEDITOR.CTRL + 90 /*Z*/, 'undo' ],
		[ CKEDITOR.CTRL + 89 /*Y*/, 'redo' ],
		[ CKEDITOR.CTRL + CKEDITOR.SHIFT + 90 /*Z*/, 'redo' ],

		[ CKEDITOR.CTRL + 76 /*L*/, 'link' ],

		[ CKEDITOR.CTRL + 66 /*B*/, 'bold' ],
		[ CKEDITOR.CTRL + 73 /*I*/, 'italic' ],
		[ CKEDITOR.CTRL + 85 /*U*/, 'underline' ],

		//[ CKEDITOR.CTRL + 83 /*S*/, 'save' ],

		[ CKEDITOR.ALT + 109 /*-*/, 'toolbarCollapse' ]
	],
	filebrowserImageUploadUrl : '<?php echo ROOT_URL ?>/ck_upload_handler.php',
	on: {
		instanceReady: function(ev) {
			og.resizeMailDiv();
			var mb = Ext.getDom(genid + 'mailBody');
			mb.oldMailBody = og.getMailBodyFromUI(genid);
			ev.editor.resetDirty();
			if (focus_editor) ev.editor.focus();
		},
		change: function(ev) {
			var p = og.getParentContentPanel(Ext.get(genid+'ckeditor'));
			Ext.getCmp(p.id).setPreventClose(ev.editor.checkDirty());
		}
	},
	removePlugins: 'scayt,liststyle,tabletools,contextmenu',
	entities_additional : '#39,#336,#337,#368,#369'
});
} catch (e) {
	og.err(e.message);
}
og.eventManager.addListener("email saved", function(obj) {
	var form = Ext.getDom(obj.instance + "form");
	if (!form) return;
	form['mail[id]'].value = obj.id;
	var editor = og.getCkEditorInstance(obj.instance + 'ckeditor');
	if (editor) {
		if (editor.checkDirty()) editor.resetDirty();
		var p = og.getParentContentPanel(Ext.get(obj.instance + 'form'));
		Ext.getCmp(p.id).setPreventClose(false);
	}
}, null, {replace:true});

og.resizeMailDiv = function() {
	setTimeout(function() {
		maindiv = document.getElementById(genid+'main_div');
		headerdiv = document.getElementById(genid+'header_div');
		if (maindiv != null && headerdiv != null) {
			var divHeight = maindiv.offsetHeight - headerdiv.offsetHeight;
			document.getElementById(genid+'mail_div').style.height = divHeight + 'px';
			
			var h = document.getElementById(genid + 'mail_body_container').offsetHeight;
			var parent = document.getElementById('cke_'+genid+'ckeditor');
			if (parent) {
				var iframes = parent.getElementsByTagName('iframe');
				if (iframes.length > 0) {
					iframes[0].style.height = (h-70) + 'px';
					parent.style.height = (h-2) + 'px';
					$('.cke_contents').css({'height': (h - 70) + 'px'});
				}
			}
		}
	}, 100);
}
window.onresize = og.resizeMailDiv;

if (Ext.getDom(genid + 'format_html') && !Ext.getDom(genid + 'format_html').checked) {
	var mb = Ext.getDom(genid + 'mailBody');
	mb.oldMailBody = og.getMailBodyFromUI(genid);
}
if (og.preferences['draft_autosave_timeout'] > 0) {
	var mb = Ext.getDom(genid + 'mailBody');
	mb.genid = genid;
	mb.autoSaveTOut = setTimeout(function() {
		var mb = Ext.getDom(genid + 'mailBody');
		og.autoSaveDraft(genid);
	}, og.preferences['draft_autosave_timeout'] * 1000);
}
if (!editor || !focus_editor) Ext.get('auto_complete_input_'+genid+'mailTo').focus();
og.resetClassButton(genid);

Ext.onReady(function() {
	og.openLink(og.getUrl('mail', 'get_allowed_addresses'), {
		post: {
			max: 1000
		},
		callback: function(success, data) {
			document.getElementById(genid + 'hf_mail_contacts').value = data.addresses;

			var to = Ext.getCmp('auto_complete_input_' + genid + 'mailTo');
			if (to) to.store = to.store.concat(data.addresses);
			var cc = Ext.getCmp('auto_complete_input_' + genid + 'mailCC');
			if (cc) cc.store = cc.store.concat(data.addresses);
			var bcc = Ext.getCmp('auto_complete_input_' + genid + 'mailBCC');
			if (bcc) bcc.store = bcc.store.concat(data.addresses);
		}
	});
});

og.checkAttach = function() {
	var attach = $("#<?php echo $genid;?>attachments").children().length;
	var editor = og.getCkEditorInstance(genid + 'ckeditor');	
	var config = <?php echo user_config_option("check_attach_word") ? '1' : '0';?>;
	text = editor.getData();
	var originalMail = text.indexOf("original_mail");

	if(config && !attach && ((text.indexOf(lang('attach')) !== -1 && (text.indexOf(lang('attach')) < originalMail || originalMail == -1)) || (text.indexOf(lang('attach')) !== -1 && (text.indexOf(lang('attach')) < originalMail || originalMail == -1)))){
		var conf = confirm(lang("confirm_mail_without_attach"));
		if (conf==true){
			og.setHfValue('<?php echo $genid;?>', 'sendBtnClick', 'true');	
			og.setHfValue('<?php echo $genid;?>', 'isDraft', 'false');
			og.stopAutosave('<?php echo $genid;?>');
		}else{
			og.setHfValue('<?php echo $genid;?>', 'sendBtnClick', 'false');	
		}
	}else{
		og.setHfValue('<?php echo $genid;?>', 'sendBtnClick', 'true');	
		og.setHfValue('<?php echo $genid;?>', 'isDraft', 'false');
		og.stopAutosave('<?php echo $genid;?>');
	}
}

og.checkFrom = function() {
	var config = <?php echo user_config_option("check_is_defult_account") ? '1' : '0';?>;
	if (!config){
		return true;
	}
	var sel_acc = $('#' + genid + 'mailAccount').val();
	var def_acc = $('#' + genid + 'def_acc_id').val();
	var is_autosaving = $('#' + genid + 'autosave').val();
	if (is_autosaving == "true" || def_acc == 0) return true;
	
	if (sel_acc != def_acc) {
		var acc_combo = document.getElementById(genid + 'mailAccount');
		var acc_name = acc_combo.options[acc_combo.selectedIndex].text;
		if (! confirm(lang("are you sure you want to send the email using account x", acc_name)) ) {
			if (!$('#' + genid + 'mailAccount').is(":visible")) {
				$('#' + genid + 'mailAccountCombo').click();
			}
			$('#' + genid + 'mailAccount').focus();
		} else {
			return true;
		}
	} else {
		return true;
	}
}

og.changeSignature(genid, $("#"+genid+"mailAccount").val());
</script>