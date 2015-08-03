<?php
set_page_title(lang('forgot password'));
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

<form class="internalForm" action="<?php echo get_url('access', 'forgot_password') ?>" method="post">
<?php tpl_display(get_template_path('form_errors')) ?>
<div class="form-container">
<?php if (!isset($_GET['instructions_sent']) || !$_GET['instructions_sent']) { ?>

  <div class="input">
    <?php echo label_tag(lang('email address'), 'forgotPasswordEmail')  ?>
    <?php echo text_field('your_email', $your_email, array('class' => 'long', 'id' => 'forgotPasswordEmail')) ?>
  </div>
  <input type="hidden" name="submited" value="submited" />
<?php } ?>
  <div id="forgotPasswordSubmit">
  <?php if (!isset($_GET['instructions_sent']) || !$_GET['instructions_sent']) { 
  			echo submit_button(lang('change password'));
  		} ?>
  	<span>(<a class="internalLink" href="<?php echo get_url('access', 'login') ?>"><?php echo lang('login') ?></a>)</span>
  </div>
</div>
</form>


</div>
<div class="login-footer">
	<div class="powered-by">
		<?php echo lang('footer powered', clean(PRODUCT_URL), clean(product_name())) . ' - ' . lang('version') . ' ' . product_version();?>
	</div>
</div>