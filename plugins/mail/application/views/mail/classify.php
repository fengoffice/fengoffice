<?php $genid = gen_id(); 
	require_javascript('og/modules/addMessageForm.js');
	$on_submit = "og.submit_modal_form('".$genid."formClassify'); return false;";
	
	$form_url = get_url('mail','classify', array('id'=>$email->getId(), 'only_attachments' => $only_attachments));
?>
<form id='<?php echo $genid ?>formClassify' name='formClassify' style='padding: 7px;height:100%;background-color:white; min-width: 550px; min-height: 220px' 
		class="internalForm miclase" onsubmit="<?php echo $on_submit?>" action="<?php echo $form_url ?>" method="post">
	
	<input type="hidden" name="id" value="<?php echo $email->getId() ?>" />
	<input type="hidden" name="from_mail_view" value="<?php echo array_var($_GET, 'from_mail_view') ?>" />
	<input type="hidden" name="from_mail_list" value="<?php echo array_var($_GET, 'from_mail_list') ?>" />
	<input type="hidden" name="submit" value="1" />
	
	<div class="classify mail-classify-selector">
		<?php 
			$options = array();
			Hook::fire('modify_mail_classify_selector_options', array('object'=>$email, 'genid'=>$genid), $options);
			
			render_member_selectors(MailContents::instance()->getObjectTypeId(), $genid, $email->getMemberIds(), $options); ?>
			
		<?php 
		if (!$only_attachments && user_config_option('mail_drag_drop_prompt') == 'prompt' && $email->getHasAttachments()) {
			echo '<div class="clear"></div><div style="margin-top:5px;">';
			echo label_tag(lang('classify attachments'), $genid.'classify_attachments', false, array('style' => 'font-size: 100%; float:left; cursor:pointer;'));
			echo checkbox_field('classify_attachments', false, array('id' => $genid.'classify_attachments', 'style' => 'float:left; margin-top: 3px;'));
			echo '</div><div class="clear"></div>';
		}
		?>
		<div style="float:right;width:70px;margin-left:10px;clear: left;">
			<?php echo submit_button(lang('save'), null, array('tabindex' => '50'))?>
		</div>
	</div>
	
</form>