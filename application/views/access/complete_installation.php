<?php 
set_page_title(lang('complete installation'));
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

<form class="internalForm" action="<?php echo get_url('access', 'complete_installation') ?>" method="post">
<?php tpl_display(get_template_path('form_errors')) ?>

<div class="form-container">
  <h2><?php echo lang('administrator') ?></h2>

  <p><?php echo lang('complete installation desc') ?></p>


  <div class="input">
    <?php echo label_tag(lang('username'), 'adminUsername', true) ?>
    <?php echo text_field('form[admin_username]', array_var($form_data, 'admin_username'), array('id' => 'adminUsername', 'class' => 'medium')) ?>
  </div>
  <div class="input">
    <?php echo label_tag(lang('email address'), 'adminEmail', true) ?>
    <?php echo text_field('form[admin_email]', array_var($form_data, 'admin_email'), array('id' => 'adminEmail', 'class' => 'long')) ?>
  </div>
  <div class="input">
    <?php echo label_tag(lang('password'), 'adminPassword', true) ?>
    <?php echo password_field('form[admin_password]', null, array('id' => 'adminPassword', 'class' => 'medium')) ?>
  </div>
  <div class="input">
    <?php echo label_tag(lang('password again'), 'adminPasswordA', true) ?>
    <?php echo password_field('form[admin_password_a]', null, array('id' => 'adminPasswordA', 'class' => 'medium')) ?>
  </div>
  <div style="clear:both;"></div>
  <div class="input">
    <?php echo label_tag(lang('company'), 'companyName', true) ?>
    <?php echo text_field('form[company_name]', array_var($form_data, 'company_name'), array('id' => 'companyName', 'class' => 'long')) ?>
  </div>
  
  <input type="hidden" name="form[submited]" value="submited" />
  <div style="clear:both;"></div>
  <?php echo submit_button(lang('submit')) ?>
  
</div>

</div>
</form>

</div>
<div class="login-footer">
	<div class="powered-by">
		<?php echo lang('footer powered', clean(PRODUCT_URL), clean(product_name())) . ' - ' . lang('version') . ' ' . product_version();?>
	</div>
</div>