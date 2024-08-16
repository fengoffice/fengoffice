
<div class="adminConfiguration edit-password" style="height:100%;background-color:white">
	<div class="coInputHeader">
	  <div>
		<div class="coInputName">
			<div class="coInputTitle">
			  	<?php echo lang('change password') . ' - ' . $user->getName()?>
			</div>
		</div>
		<div class="clear"></div>
	  </div>
	</div>
  <div class="adminMainBlock coInputMainBlock">
  
<form class="internalForm" action="<?php echo $user->getEditPasswordUrl($redirect_to) ?>" method="post">

	<?php tpl_display(get_template_path('form_errors'));
  		$first_tab = 1; 
	?>
  
	<div class="dataBlock">
		<?php if(!logged_user()->isAdminGroup()) { // Ask the user for his password. ?>
			<?php echo label_tag(lang('old password'), 'passwordFormOldPassword', true) ?>
			<?php echo password_field('password[old_password]', null, array('tabindex' => $first_tab)) ?>
		<?php } else { // Ask the administrator for his password. ?>
			<?php echo label_tag(lang('admin password'), 'passwordFormAdminPassword', true) ?>
			<?php echo password_field('password[admin_password]', null, array('tabindex' => $first_tab)) ?>
			<span class="desc"><?php echo lang('please enter your current administrator password') ?></span>
		<?php } ?>
	</div>
			
	<div class="dataBlock">
		<?php echo label_tag(lang('new password'), 'passwordFormNewPassword', true) ?>
		<?php echo password_field('password[new_password]', null, array('tabindex' => $first_tab + 100)) ?>
	</div>

	<div class="dataBlock">
		<?php echo label_tag(lang('password again'), 'passwordFormNewPasswordAgain', true) ?>
		<?php echo password_field('password[new_password_again]', null, array('tabindex' => $first_tab + 200)) ?>
	</div>

	<?php echo render_password_requirements(); ?>

	<?php echo submit_button(lang('change password'), 'C', array('tabindex' => $first_tab + 300)) ?>

</form>

	</div>
</div>