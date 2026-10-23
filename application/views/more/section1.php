<?php

$links = array();
if (can_manage_configuration(logged_user())) {
	$links[] = array(
		'id' => 'system_modules',
		'ico' => 'ico-large-tabs',
		'url' => get_url('more', 'system_modules'),
		'name' => lang('system modules'),
		'extra' => '',
	);
}
if (can_manage_security(logged_user())) {
	$links[] = array(
		'id' => 'users_and_groups',
		'ico' => 'ico-large-group',
		'url' => get_url('more', 'users_and_groups', array('only_full_users' => (config_option('getting_started_step') < 99 ? '1' : '0'))),
		'name' => lang('users groups and permissions'),
		'extra' => '',
	);
}

Hook::fire('render_administration_dimension_icons', null, $links);

if (can_manage_configuration(logged_user())) {
	$links[] = array(
			'ico' => 'ico-large-configuration',
			'url' => get_url('administration', 'configuration'),
			'name' => lang('configuration'),
			'extra' => '',
	);
}

if (can_manage_templates(logged_user())) {
	$links[] = array(
			'ico' => 'ico-large-template',
			'url' => get_url('template', 'index'),
			'name' => lang('templates'),
			'extra' => '<a class="internalLink coViewAction ico-add" href="' . get_url('template','add') . '">' . lang('add template') . '</a>',
	);
}

if (can_manage_billing(logged_user())) {
	$links[] = array(
			'ico' => 'ico-large-billing',
			'url' => get_url('billing', 'index'),
			'name' => lang('billing and invoicing'),
			'extra' => '<a class="internalLink coViewAction ico-add" href="' . get_url('billing', 'add') . '">' . lang('add billing category') . '</a>',
	);
}

if (can_manage_configuration(logged_user())) {
	$links[] = array(
			'ico' => 'ico-large-custom-properties',
			'url' => get_url('administration', 'custom_properties'),
			'name' => lang('object properties'),
			'extra' => '',
	);
}


Hook::fire('render_administration_icons', null, $links);

if (defined("PLUGIN_MANAGER") && PLUGIN_MANAGER && can_manage_plugins(logged_user())) {
	$links[] = array(
			'ico' => 'ico-large-plugins',
			'url' => get_url('plugin', 'index'),
			'name' => lang('plugins'),
			'extra' => '',
	);
}


$count = 0;
foreach ($links as $link) {
	$count++;
?>
<a <?php echo (isset($link['onclick']) ? 'onclick="'.$link['onclick'].'"' : '') ?> class="internalLink" href="<?php echo $link['url'] ?>" <?php echo isset($link['target']) ? 'target="'.$link['target'].'"' : '' ?>>
	<div class="link highlighted off" id="<?php echo $genid?>_link_<?php echo $count?>">
		
		<div class="highlighted-number"><?php echo $count?></div>
		<div class="coViewIconImage <?php echo $link['ico']?>"></div>
    	
		<?php echo $link['name'] ?>
		<div class="clear"></div>
	</div>
</a>
<?php
}
?>
<div class="clear"></div>