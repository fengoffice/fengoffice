<div style="padding:10px;">
	<?php if ($email->getAccount() instanceof MailAccount) { ?>
	<h1 style="font-family: Helvetica, Arial, sans-serif; font-size: 20px; border-bottom: 2px solid #999999; padding-bottom: 5px;"><?php echo $email->getAccount()->getName() ?></h1>
	<?php } ?>
	<table style=" font-family: Helvetica, Arial, sans-serif;">
		<tr><td style="font-weight: bold;"><?php echo lang('from') ?>:</td><td style="padding-left:10px;">
			<?php echo MailUtilities::displayMultipleAddresses(clean($email->getFromName()." <".$email->getFrom().">"), true, false) ?>
		</td></tr>
		<tr><td style="font-weight: bold;"><?php echo lang('date') ?>:</td><td style="padding-left:10px;">
			<?php echo format_datetime($email->getSentDate()) ?>
		</td></tr>
		<tr><td style="font-weight: bold;"><?php echo lang('to') ?>:</td><td style="padding-left:10px;">
			<?php echo MailUtilities::displayMultipleAddresses(clean($email->getTo()), true, false) ?>
		</td></tr>
		<?php if ($email->getCc() != '') { ?>
		<tr><td style="font-weight: bold;"><?php echo lang('mail CC') ?>:</td><td style="padding-left:10px;">
			<?php echo MailUtilities::displayMultipleAddresses(clean($email->getCc()), true, false) ?>
		</td></tr>		
		<?php } ?>
		<?php if ($email->getBcc() != '') { ?>
		<tr><td style="font-weight: bold;"><?php echo lang('mail BCC') ?>:</td><td style="padding-left:10px;">
			<?php echo MailUtilities::displayMultipleAddresses(clean($email->getBcc()), true, false) ?>
		</td></tr>		
		<?php } ?>	
		<tr><td style="font-weight: bold;"><?php echo lang('subject') ?>:</td><td style="padding-left:10px;">
			<?php echo $email->getSubject() ?>
		</td></tr>
	</table>
	<br /><br />
	
	<?php
		if ($email->getBodyHtml() != '') {
			echo $email->getBodyHtml();
		} else {
			echo '<div>' . nl2br(convert_to_links(clean($email->getBodyPlain()))) . '</div>'; 
		}
	?>
</div>

<script>
window.print();
</script>