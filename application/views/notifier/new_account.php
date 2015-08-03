<?php if($type_notifier == "specify_pass"){?>
<div style="font-family: Verdana, Arial, sans-serif; font-size: 12px;">

	<?php echo lang('hi john doe', $new_account->getObjectName()) ?>,<br><br>
	
	<?php echo lang('user created your account', $new_account->getCreatedByDisplayName()) ?>.<br><br>
	
	<?php echo lang('visit and login', '<a href="'.ROOT_URL.'" target="_blank">'.ROOT_URL.'</a>') ?>:<br><br>
	
	&nbsp;&nbsp;&nbsp;&nbsp;<?php echo lang('username') ?>: <?php echo $new_account->getUsername() ?><br><br>
	
	&nbsp;&nbsp;&nbsp;&nbsp;<?php echo lang('password') ?>: <?php echo $raw_password ?><br><br>
	
	<br><br>

	<div style="color: #818283; font-style: italic; border-top: 2px solid #818283; padding-top: 2px; font-family: Verdana, Arial, sans-serif; font-size: 12px;">
	<?php echo lang('system notification email'); ?><br>
	<a href="<?php echo ROOT_URL; ?>" target="_blank" style="font-family: Verdana, Arial, sans-serif; font-size: 12px;"><?php echo ROOT_URL; ?></a>
	</div>
</div>
<?php }else if($type_notifier == "link_pass"){?>
<div style="font-family: Verdana, Arial, sans-serif; font-size: 12px;">

	<?php echo lang('hi john doe', $new_account->getObjectName()) ?>,<br><br>
	
	<?php echo lang('user created your account', $new_account->getCreatedByDisplayName()) ?>.<br><br>
	
	<?php echo lang('need to set up your new password') ?><br><br>
	
	<?php echo '<a href="'.get_url('access','reset_password', array('t' => $token, 'uid' => $new_account->getId(), 'type_notifier' => $type_notifier)).'" target="_blank">'.get_url('access','reset_password', array('t' => $token, 'uid' => $new_account->getId(), 'type_notifier' => $type_notifier)).'</a>'?><br><br>
	
	<?php echo lang('visit feel free to log in', '<a href="'.ROOT_URL.'" target="_blank">'.ROOT_URL.'</a>',$new_account->getUsername(),$new_account->getEmailAddress()) ?><br><br>
	
	<br><br>

	<div style="color: #818283; font-style: italic; border-top: 2px solid #818283; padding-top: 2px; font-family: Verdana, Arial, sans-serif; font-size: 12px;">
	<?php echo lang('system notification email'); ?><br>
	<a href="<?php echo ROOT_URL; ?>" target="_blank" style="font-family: Verdana, Arial, sans-serif; font-size: 12px;"><?php echo ROOT_URL; ?></a>
	</div>
</div>
<?php } ?>
