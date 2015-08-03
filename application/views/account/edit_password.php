
<div class="adminConfiguration" style="height:100%;background-color:white">
	<div class="coInputHeader">
	  <div>
		<div class="coInputName">
			<div class="coInputTitle">
			  	<?php echo lang('change password') ?>
			</div>
		</div>
		<div class="clear"></div>
	  </div>
	</div>
  <div class="adminMainBlock">
  
<form class="internalForm" action="<?php echo $user->getEditPasswordUrl($redirect_to) ?>" method="post">

  <?php tpl_display(get_template_path('form_errors'));
  		$first_tab = 1; 
  ?>
  
<?php if(!logged_user()->isAdminGroup()) { ?>
  <div>
    <?php echo label_tag(lang('old password'), 'passwordFormOldPassword', true) ?>
    <?php echo password_field('password[old_password]', null, array('tabindex' => $first_tab)) ?>
    <?php $first_tab = '100' ?>
  </div>
<?php } // if ?>
  
  <div>
    <?php echo label_tag(lang('password'), 'passwordFormNewPassword', true) ?>
    <?php echo password_field('password[new_password]', null, array('tabindex' => $first_tab)) ?>
  </div>
  
  <div>
    <?php echo label_tag(lang('password again'), 'passwordFormNewPasswordAgain', true) ?>
    <?php echo password_field('password[new_password_again]', null, array('tabindex' => $first_tab + 100)) ?>
  </div>
  
  <?php echo submit_button(lang('change password'), 'C', array('tabindex' => $first_tab + 200)) ?>
  
</form>

	</div>
</div>