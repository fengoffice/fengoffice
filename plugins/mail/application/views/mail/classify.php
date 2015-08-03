<?php $genid = gen_id(); 
	require_javascript('og/modules/addMessageForm.js');
	$on_submit = "og.submit_modal_form('".$genid."formClassify'); return false;";
?>
<form id='<?php echo $genid ?>formClassify' name='formClassify' style='padding: 7px;height:100%;background-color:white; min-width: 550px; min-height: 220px'  class="internalForm miclase" onsubmit="<?php echo $on_submit?>" action="<?php echo get_url('mail','classify', array('id'=>$email->getId())) ?>" method="post">
	<input type="hidden" name="id" value="<?php echo $email->getId() ?>" />
	<input type="hidden" name="submit" value="1" />
	
	<div class="classify mail-classify-selector">
		<?php render_member_selectors(MailContents::instance()->getObjectTypeId(), $genid, $email->getMemberIds()); ?>
			
		<?php 
		if (user_config_option('mail_drag_drop_prompt') == 'prompt' && $email->getHasAttachments()) {
			echo '<div class="clear"></div><div style="margin-top:5px;">';
			echo label_tag(lang('classify attachments'), $genid.'classify_attachments', false, array('style' => 'font-size: 100%; float:left; cursor:pointer;'));
			echo checkbox_field('classify_attachments', false, array('id' => $genid.'classify_attachments', 'style' => 'float:left; margin-top: 3px;'));
			echo '</div><div class="clear"></div>';
		}
		?>
		<div style="float:right;width:70px;margin-left:10px;clear: left;">
			<?php echo submit_button(lang('save'), array('tabindex' => '50'))?>
		</div>
	</div>
	
</form>