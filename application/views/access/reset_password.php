<?php 


$is_finishing_sign_up = false;
if (isset($type_notifier) && $type_notifier == 'link_pass') {
	$is_finishing_sign_up = true;
}

if ($is_finishing_sign_up) {
	$page_title = lang('finish sign up');
	$button_text = lang('finish sign up');
} else {
	$page_title = lang('reset password');
	$button_text = lang('change password');
}

Hook::fire('login_page_title', null, $page_title);
set_page_title($page_title); 
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
		<a class="logo" href="<?php echo clean(PRODUCT_URL); ?>"></a>
	<?php } ?>
	</div>
</div>
<div class="login-body">

<form action="<?php echo get_url('access', 'reset_password', array('t' => $token, 'uid' => $user->getId())) ?>" method="post">
<div class="form-container">

<div id="reset_password_desc"><h1><?php
	echo lang('reset password form desc', $user->getObjectName());
?>
</h1></div>
<div id="reset_password_new" class="input">
	<?php echo label_tag(lang('new password'), 'new_password', true)?>
	<?php echo password_field('new_password', '', array('id' => 'new_password')) ?>
</div>
<div id="reset_password_repeat" class="input">
	<?php echo label_tag(lang('password again'), 'repeat_password', true)?>
	<?php echo password_field('repeat_password', '', array('id' => 'repeat_password')) ?>
</div>
<div id="reset_password_submit">
	<button type="submit" class="submit blue"><?php echo $button_text ?></button>
</div>

</div>
</form>


</div>
<div class="login-footer">
	<div class="powered-by">
		<?php echo lang('footer powered', clean(PRODUCT_URL), clean(product_name())) . ' - ' . lang('version') . ' ' . product_version();?>
	</div>
</div>