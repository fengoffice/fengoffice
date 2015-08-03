<?php
$links = array();
/*
$links[] = array(
	'ico' => 'ico-large-group',
	'url' => 'http://wiki.fengoffice.com/doku.php#part_5users_and_rights',
	'name' => lang('about users, groups and permissions'),
	'target' => '_blank',
);
$links[] = array(
		'ico' => 'ico-large-template',
		'url' => 'http://wiki.fengoffice.com/doku.php/templates:templates',
		'name' => lang('about process templates'),
		'target' => '_blank',
);
if (Plugins::instance()->isActivePlugin('crpm')) {
	$links[] = array(
		'ico' => 'ico-large-projects',
		'url' => 'http://wiki.fengoffice.com/doku.php/clientsandprojects:clientsandprojects',
		'name' => lang('about clients and projects'),
		'target' => '_blank',
	);
}
$links[] = array(
	'ico' => 'ico-large-tabs',
	'url' => 'http://wiki.fengoffice.com/doku.php#part_3modules',
	'name' => lang('about modules and dimensions'),
	'target' => '_blank',
);
$links[] = array(
	'ico' => 'ico-large-tasks',
	'url' => 'http://wiki.fengoffice.com/doku.php/tasks',
	'name' => lang('about tasks'),
	'target' => '_blank',
);
*/


$email = logged_user()->getEmailAddress();
$token = logged_user()->getToken();

$right_links = array();
/*
$right_links[] = array(
	'ico' => 'ico-large-services',
	'url' => '#',
	'onclick' => "og.openLink(og.getUrl('more', 'contracted_services'), {caller:'contracted_services'});",
	'name' => lang('contracted services'),
);
*/
Hook::fire('render_help_and_support_links', null, $right_links);

$right_links[] = array(
	'ico' => 'ico-large-help',
	'url' => 'http://wiki.fengoffice.com/',
	'name' => lang('more help'),
	'target' => '_blank',
);


$right_links[] = array(
	'ico' => 'ico-large-comment',
	'url' => '#',
	'onclick' => 'document.getElementById(\'sup_form\').submit(); return false;',
	'name' => lang('open a support ticket'),
	'target' => '_blank',
	'additional_html' => '<form target="_blank" id="sup_form" action="http://www.fengoffice.com/web/inc/_session_recover.php" method="post" style="display:none;">
			<input type="hidden" name="infobar_email" value="'.$email.'" />
			<input type="hidden" name="infobar_token" value="'.$token.'" />
			<input type="hidden" name="infobar_redirect" value="support/tickets.php" />
		</form>',
);

?>
<div class="left-section">
<?php
foreach ($links as $link) {
?>
<a href="<?php echo $link['url'] ?>" <?php echo isset($link['target']) ? 'target="'.$link['target'].'"' : '' ?> <?php echo isset($link['onclick']) ? 'onclick="'.$link['onclick'].'"' : '' ?>>
	<div class="link">
		<div class="coViewIconImage <?php echo $link['ico']?>"></div>
    	<?php echo $link['name'] ?>
    	<?php if (array_var($link, 'additional_html')) echo array_var($link, 'additional_html'); ?>
	</div>
</a>
	
<?php
}
?>
	<div class="clear"></div>
</div>


<div class="right-section">
<?php
foreach ($right_links as $link) {
?>
<a href="<?php echo $link['url'] ?>" <?php echo isset($link['target']) ? 'target="'.$link['target'].'"' : '' ?> <?php echo isset($link['onclick']) ? 'onclick="'.$link['onclick'].'"' : '' ?>>
	<div class="link">
    	<div class="coViewIconImage <?php echo $link['ico']?>"></div>
		<?php echo $link['name'] ?>
		<?php if (array_var($link, 'additional_html')) echo array_var($link, 'additional_html'); ?>
	</div>
</a>
	
<?php
}
?>
</div>

<div class="clear"></div>