<?php $genid = gen_id(); 
	require_javascript('og/modules/addMessageForm.js');
	$on_submit = "og.submit_modal_form('".$genid."formClassify'); return false;";
	
	$form_url = get_url('mail','classify', array('id'=>$email->getId(), 'only_attachments' => $only_attachments));
?>
<form id='<?php echo $genid ?>formClassify' name='formClassify' class="feng-forms form-classify" onsubmit="<?php echo $on_submit?>" action="<?php echo $form_url ?>" method="post">
	
	<input type="hidden" name="id" value="<?php echo $email->getId() ?>" />
	<input type="hidden" name="from_mail_view" value="<?php echo array_var($_GET, 'from_mail_view') ?>" />
	<input type="hidden" name="from_mail_list" value="<?php echo array_var($_GET, 'from_mail_list') ?>" />
	<input type="hidden" name="submit" value="1" />
	
	<div class="classify mail-classify-selector">
		<div class="coInputHeader">
			<div class="coInputHeaderUpperRow">
				<div class="coInputTitle"><?php echo lang('classify') ?></div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="coInputMainBlock">
			<?php 
				$options = array();
				Hook::fire('modify_mail_classify_selector_options', array('object'=>$email, 'genid'=>$genid), $options);
				
				render_member_selectors(MailContents::instance()->getObjectTypeId(), $genid, $email->getMemberIds(), $options); ?>
				
			<?php 
			if (!$only_attachments && user_config_option('mail_drag_drop_prompt') == 'prompt' && $email->getHasAttachments()) {
				echo '<div class="clear"></div><div>';
				echo label_tag(lang('classify attachments'), $genid.'classify_attachments', false);
				echo checkbox_field('classify_attachments', false, array('id' => $genid.'classify_attachments'));
				echo '</div><div class="clear"></div>';
			}
			?>
			<!-- <div style="float:right;width:70px;margin-left:10px;clear: left;"> -->
			<div class="coInputSubmitBlock d-flex flex-end">
				<?php echo submit_button(lang('save'), null, array('class' => 'blue'))?>
			</div>
		</div>
	</div>
	
</form>