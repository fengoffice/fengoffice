<?php 
set_page_title(lang('change password'));
add_javascript_to_page('jquery/jquery.js');

$css = array();
Hook::fire('overwrite_login_css', null, $css);
foreach ($css as $c) {
	echo stylesheet_tag($c);
}
?>
<!--[if IE 7]>
<?php echo stylesheet_tag("og/ie7.css"); ?>
<![endif]-->
<!--[if IE 8]>
<?php echo stylesheet_tag("og/ie8.css"); ?>
<![endif]-->
<div class="header-container">
	<div class="header">
	<?php if (Plugins::instance()->isActivePlugin('custom_login')) {
			echo_custom_logo_url();
		  } else { ?>
		<a class="logo" href="http://www.fengoffice.com"></a>
	<?php } ?>
	</div>
</div>
<div class="login-body">

<form action="<?php echo get_url('access', 'change_password', array('id' => $user_id)) ?>" method="post">

<div class="form-container">
<h2><?php echo lang('change password') ?></h2>

<div style="color:red;">
<?php echo $reason ?>
</div>

<?php tpl_display(get_template_path('form_errors')) ?>

  <div id="changePasswordDiv">
    <label for="username"><?php echo lang('username') ?>:</label>
    <?php echo text_field('changePassword[username]', null, array('id' => 'username', 'class' => 'medium')) ?>
  </div>
  <div id="repeatPasswordDiv">
    <label for="oldPassword"><?php echo lang('old password') ?>:</label>
    <?php echo password_field('changePassword[oldPassword]', null, array('id' => 'oldPassword', 'class' => 'medium')) ?>
  </div>
  <div class="clean"></div>
  <div id="changePasswordDiv">
    <label for="newPassword"><?php echo lang('new password') ?>(*):</label>
    <?php echo password_field('changePassword[newPassword]', null, array('id' => 'newPassword', 'class' => 'medium')) ?>
  </div>
  <div id="repeatPasswordDiv">
    <label for="repeatPassword"><?php echo lang('password again') ?>:</label>
    <?php echo password_field('changePassword[repeatPassword]', null, array('id' => 'repeatPassword', 'class' => 'medium')) ?>
  </div>
  <div style="clear:both;"></div>
 
  <br/>
  <div id="loginSubmit"><?php echo submit_button(lang('change')) ?></div>
  
  <?php 
  	$min_pass_length = config_option('min_password_length', 0);	
  	if($min_pass_length > 0) echo '*'.lang('password invalid min length', $min_pass_length).'<br/>';
  	$pass_numbers = config_option('password_numbers', 0);			
	if($pass_numbers > 0) echo '*'.lang('password invalid numbers', $pass_numbers).'<br/>';
	$pass_uppercase = config_option('password_uppercase_characters', 0);		
	if($pass_uppercase) echo '*'.lang('password invalid uppercase', $pass_uppercase).'<br/>';
	$pass_metacharacters = config_option('password_metacharacters', 0);		
	if($pass_metacharacters) echo '*'.lang('password invalid metacharacters', $pass_metacharacters).'<br/>';
  ?>

</div>
</form>

</div>
<div class="login-footer">
	<div class="powered-by">
		<?php echo lang('footer powered', clean(PRODUCT_URL), clean(product_name())) . ' - ' . lang('version') . ' ' . product_version();?>
	</div>
</div>