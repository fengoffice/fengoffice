<?php 
$icons = array();
if (can_manage_configuration(logged_user())) {
	$icons[] = array(
		'ico' => 'ico-large-tabs',
		'url' => get_url('more', 'system_modules'),
		'name' => lang('system modules'),
		'extra' => '',
	);
}
if (can_manage_security(logged_user())) {
	$icons[] = array(
		'ico' => 'ico-large-group',
		'url' => get_url('more', 'users_and_groups'),
		'name' => lang('users groups and permissions'),
		'extra' => '',
	);
}

Hook::fire('render_administration_dimension_icons', null, $icons);

if (can_manage_configuration(logged_user())) {
	$icons[] = array(
		'ico' => 'ico-large-company',
		'url' => get_url('administration', 'company'),
		'name' => lang('organization data'),
		'extra' => '',
	);
	$icons[] = array(
		'ico' => 'ico-large-configuration',
		'url' => get_url('administration', 'configuration'),
		'name' => lang('configuration'),
		'extra' => '',
	);
}

if (can_manage_templates(logged_user())) {
	$icons[] = array(
		'ico' => 'ico-large-template',
		'url' => get_url('template', 'index'),
		'name' => lang('templates'),
		'extra' => '<a class="internalLink coViewAction ico-add" href="' . get_url('template','add') . '">' . lang('add template') . '</a>',
	);
}

if (can_manage_billing(logged_user())) {
	$icons[] = array(
		'ico' => 'ico-large-billing',
		'url' => get_url('billing', 'index'),
		'name' => lang('billing'),
		'extra' => '<a class="internalLink coViewAction ico-add" href="' . get_url('billing', 'add') . '">' . lang('add billing category') . '</a>',
	);
}

if (can_manage_configuration(logged_user())) {
	$icons[] = array(
		'ico' => 'ico-large-custom-properties',
		'url' => get_url('administration', 'custom_properties'),
		'name' => lang('custom properties'),
		'extra' => '',
	);
}

Hook::fire('render_administration_icons', null, $icons);

if (can_manage_security(logged_user()) && Plugins::instance()->isActivePlugin('income')) {
	$icons[] = array(
		'ico' => 'ico-large-income',
		'url' => get_url('income', 'administration'),
		'name' => lang('income'),
		'extra' => '',
	);
}


if (defined("PLUGIN_MANAGER") && PLUGIN_MANAGER && can_manage_plugins(logged_user())) {
	$icons[] = array(
		'ico' => 'ico-large-plugins',
		'url' => get_url('plugin', 'index'),
		'name' => lang('plugins'),
		'extra' => '',
	);
}

if (!isset($genid)) $genid = gen_id();
?>
<div class="settings-container">
	<div class="title">
		<div class="titletext"><?php echo lang('settings')?></div>
		<button title="<?php echo lang('back')?>" style="float:left; margin: -10px 0 0 0;" class="add-first-btn" onclick="og.goback(this)">
			<img src="public/assets/themes/default/images/16x16/back.png">&nbsp;<?php echo lang('back')?>
		</button>
		<div class="clear"></div>
	</div>
	<div class="settings-section">
		<div class="settings-section-content">
<?php
$count = 0;
foreach ($icons as $link) {
	$count++;
	?>
		<div class="link" id="<?php echo $genid?>_more_settings_<?php echo $count?>">
			
			<a <?php echo (isset($link['onclick']) ? 'onclick="'.$link['onclick'].'"' : '') ?> class="internalLink" href="<?php echo $link['url'] ?>" <?php echo isset($link['target']) ? 'target="'.$link['target'].'"' : '' ?>>
	    		<div class="coViewIconImage <?php echo $link['ico']?>"></div>
	    	</a>
			<a <?php echo (isset($link['onclick']) ? 'onclick="'.$link['onclick'].'"' : '') ?> class="internalLink" href="<?php echo $link['url'] ?>" <?php echo isset($link['target']) ? 'target="'.$link['target'].'"' : '' ?>><?php echo $link['name'] ?></a>
			<div class="clear"></div>
		</div>
<?php
}
?>
		</div>
	</div>
</div>


<script>
	$(function(){
		$(".settings-container").parent().css('backgroundColor', 'white');
	});
</script>