<div style="font-family: Verdana, Arial, sans-serif; font-size: 12px;">

	<?php echo lang('hi john doe', $user->getObjectName()).',' ?><br><br>
	
	<?php echo lang('user password reseted desc') ?><br><br>
	
        <a href="<?php echo get_url('access','reset_password', array('t' => $token, 'uid' => $user->getId()))?>">
            <?php echo get_url('access','reset_password', array('t' => $token, 'uid' => $user->getId()))?>
        </a>
        <br/><br/>
	
	<?php echo lang('user password reset ignore') ?><br><br>
	<br/><br/>
	

	<div style="color: #818283; font-style: italic; border-top: 2px solid #818283; padding-top: 2px; font-family: Verdana, Arial, sans-serif; font-size: 12px;">
	<?php echo lang('system notification email'); ?><br>
	<a href="<?php echo ROOT_URL; ?>" target="_blank" style="font-family: Verdana, Arial, sans-serif; font-size: 12px;"><?php echo ROOT_URL; ?></a>
	</div>

</div>