<?php 
set_page_title(lang('administration'));
$icons = array();

/*FIXME FENG2 if (can_manage_security(logged_user())) {
	$icons[] = array(
		'ico' => 'ico-large-company',
		'url' => get_url('administration', 'clients'),
		'name' => lang('client companies'),
		'extra' => '<a class="internalLink coViewAction ico-add" href="' . get_url('contact', 'add_company') . '">' . lang('add company') . '</a>'
	);
}*/
if (can_manage_security(logged_user())) {
	$icons[] = array(
		'ico' => 'ico-large-user',
		'url' => get_url('administration', 'members'),
		'name' => lang('users'),
		'extra' => '<a class="internalLink coViewAction ico-add" href="' . owner_company()->getAddUserUrl() . '">' . lang('add user') . '</a>',
	);
} 
if (can_manage_security(logged_user())) {
	$icons[] = array(
		'ico' => 'ico-large-group',
		'url' => get_url('administration', 'groups'),
		'name' => lang('groups'),
		'extra' => '<a class="internalLink coViewAction ico-add" href="' . owner_company()->getAddGroupUrl() . '">' . lang('add group') . '</a>',
	);
}
if (can_manage_security(logged_user()) && Plugins::instance()->isActivePlugin('mail')) {
	$icons[] = array(
		'ico' => 'ico-large-email',
		'url' => get_url('administration', 'mail_accounts'),
		'name' => lang('mail accounts'),
		'extra' => '<a class="internalLink coViewAction ico-add" href="' . get_url('mail', 'add_account') . '">' . lang('add mail account') . '</a>',
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
		'ico' => 'ico-large-company',
		'url' => get_url('administration', 'company'),
		'name' => lang('organization data'),
		'extra' => '',
	);
	$icons[] = array(
		'ico' => 'ico-large-custom-properties',
		'url' => get_url('administration', 'custom_properties'),
		'name' => lang('custom properties'),
		'extra' => '',
	);
	/*
	$icons[] = array(
		'ico' => 'ico-large-object-subtypes',
		'url' => get_url('administration', 'object_subtypes'),
		'name' => lang('object subtypes'),
		'extra' => '',
	);*/
	
	$icons[] = array(
		'ico' => 'ico-large-configuration',
		'url' => get_url('administration', 'configuration'),
		'name' => lang('configuration'),
		'extra' => '',
	);
	$icons[] = array(
		'ico' => 'ico-large-tools',
		'url' => get_url('administration', 'tools'),
		'name' => lang('administration tools'),
		'extra' => '',
	);
	/*FIXME if (!defined('ALLOW_UPGRADING') || ALLOW_UPGRADING) {
		$icons[] = array(
			'ico' => 'ico-large-upgrade',
			'url' => get_url('administration', 'upgrade'),
			'name' => lang('upgrade'),
			'extra' => '',
		);
	}*/
	if (!defined('ALLOW_CONFIGURING_CRON') || ALLOW_CONFIGURING_CRON) {
		$icons[] = array(
			'ico' => 'ico-large-cron',
			'url' => get_url('administration', 'cron_events'),
			'name' => lang('cron events'),
			'extra' => '',
		);
	}
	
	$icons[] = array(
		'ico' => 'ico-large-tabs',
		'url' => get_url('administration', 'tabs'),
		'name' => lang('tabs'),
		'extra' => '',
	);
}
if (can_manage_dimension_members(logged_user())) {
	$icons[] = array(
		'ico' => 'ico-large-workspace',
		'url' => get_url('administration', 'edit_members'),
		'name' => lang('dimensions'),
		'extra' => '',
	);
}

if (can_manage_security(logged_user()) && Plugins::instance()->isActivePlugin('income')) {
	$icons[] = array(
		'ico' => 'ico-large-invoice',
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

Hook::fire('render_administration_icons', null, $icons);
if (count($icons > 0)) {}
?>
<div class="coInputHeader">

  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<?php echo lang('administration') ?>
	</div>
  </div>

</div>

<div class="coInputMainBlock">


<?php
// print administration icons
if (count($icons > 0)) {?>
<table><tr>
<?php $count = 0;
foreach ($icons as $icon) {
	if ($count % 4 == 0) { ?>
		</tr><tr>
	<?php }
	$count++;?>
<td align="center">
    <div style="width:150px;display:block; margin-right:10px;margin-bottom:40px">
    <table width="100%" align="center"><tr><td align="center">
    	<a class="internalLink" href="<?php echo $icon['url'] ?>" <?php echo isset($icon['target']) ? 'target="'.$icon['target'].'"' : '' ?> <?php echo isset($icon['onclick']) ? 'onclick="'.$icon['onclick'].'"' : '' ?>>
    		<span style="display: block;" class="coViewIconImage <?php echo $icon['ico']?>"></span>
    	</a>
        </td></tr><tr><td align="center"><b><a class="internalLink" href="<?php echo $icon['url'] ?>" <?php echo isset($icon['target']) ? 'target="'.$icon['target'].'"' : '' ?>><?php echo $icon['name'] ?></a></b>
    <?php if (isset($icon['extra'])) { ?>
    </td></tr><tr><td align="center"><?php echo $icon['extra']; ?>
    <?php } ?>
    </td></tr></table>
    </div>
</td>
<?php } ?>
</tr></table>
<?php } ?>

</div>
    
  </div>
</div>