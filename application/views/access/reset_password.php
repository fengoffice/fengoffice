<?php 
set_page_title(lang('reset password'));
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

<form action="<?php echo get_url('access', 'reset_password', array('t' => $token, 'uid' => $user->getId())) ?>" method="post">

<div id="reset_password_desc"><?php
	echo lang('reset password form desc', $user->getUsername());
?>
</div>
<div id="reset_password_new">
	<?php echo label_tag(lang('new password'), 'new_password', true)?>
	<?php echo password_field('new_password', '', array('id' => 'new_password')) ?>
</div>
<div id="reset_password_repeat">
	<?php echo label_tag(lang('password again'), 'repeat_password', true)?>
	<?php echo password_field('repeat_password', '', array('id' => 'repeat_password')) ?>
</div>
<div id="reset_password_submit">
	<button type="submit"><?php echo lang('change password') ?></button>
</div>
</div>
</form>


</div>
<div class="login-footer">
	<div class="powered-by">
		<?php echo lang('footer powered', clean(PRODUCT_URL), clean(product_name())) . ' - ' . lang('version') . ' ' . product_version();?>
	</div>
</div>