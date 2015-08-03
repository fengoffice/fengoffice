<?php 
set_page_title(lang('login'));
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
<script>
	showMoreOptions = function() {
		var div = document.getElementById("optionsDiv");
		var more = document.getElementById("optionsLink");
		var hide = document.getElementById("hideOptionsLink");
		div.style.display = "block";
		hide.style.display = "inline";
		more.style.display = "none";
	}
	hideMoreOptions = function() {
		var div = document.getElementById("optionsDiv");
		var more = document.getElementById("optionsLink");
		var hide = document.getElementById("hideOptionsLink");
		div.style.display = "none";
		hide.style.display = "none";
		more.style.display = "inline";
	}
</script>

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

<form action="<?php echo get_url('access', 'login') ?>" method="post">
	
	<img src="<?php echo get_image_url("layout/loading.gif") ?>" width="1" height="1" style="position:absolute; top:0; left:0 ;display:none"/>
	
<?php tpl_display(get_template_path('form_errors')) ?>

<div class="form-container">
  <div class="input">
    <label for="loginUsername"><?php echo lang('email or username') ?>:</label>
    <?php echo text_field('login[username]', array_var($login_data, 'username'), array('id' => 'loginUsername')) ?>
  </div>
  <div class="input">
    <label for="loginPassword"><?php echo lang('password') ?>:</label>
    <?php echo password_field('login[password]', null, array('id' => 'loginPassword')) ?>
  </div>
  <div id="optionsDiv" class="input" style="display:none">
	<label><?php echo lang('language')?>:</label>
  	<div style="float:right"><?php
  		$handler = new LocalizationConfigHandler();
  		echo $handler->render('configOptionSelect', array('text' => lang('last language'), 'value' => 'Default'));
  	?></div>
  </div>
  <div style="clear:both;"></div>
<?php if(isset($login_data) && is_array($login_data) && count($login_data)) { ?>
<?php foreach($login_data as $k => $v) { ?>
<?php if(str_starts_with($k, 'ref_')) { ?>
  <input type="hidden" name="login[<?php echo $k ?>]" value="<?php echo $login_data[$k] ?>" />
<?php } // if ?>
<?php } // foreach ?>
<?php } // if ?>

	<div class="submit-div">
		<?php echo submit_button(lang('login')) ?>
		<span class="forgot-pass"><a class="internalLink" href="<?php echo get_url('access', 'forgot_password') ?>"><?php echo lang('forgot password') ?></a></span>
	</div>
	<div style="clear:both;"></div>
	<div class="options-container">
		<div class="remember-div">
			<?php echo checkbox_field('login[remember]', array_var($login_data, 'remember') == 'checked', array('id' => 'loginRememberMe')) ?>
			<label class="checkbox" for="loginRememberMe"><?php echo lang('remember me') ?></label>
		</div>
		<div class="options-links">
			<a id="optionsLink" href="javascript:showMoreOptions()"><?php echo lang('options'); ?></a>
			<a id="hideOptionsLink" style="display:none" href="javascript:hideMoreOptions()"><?php echo lang ('hide options'); ?></a>
		</div>
	</div>
  	
</div>
</form>

</div>


<div class="login-footer">
	<div class="powered-by">
		<?php echo lang('footer powered', clean(PRODUCT_URL), clean(product_name())) . ' - ' . lang('version') . ' ' . product_version();?>
	</div>
</div>

<script>
document.getElementById('loginUsername').focus();
</script>