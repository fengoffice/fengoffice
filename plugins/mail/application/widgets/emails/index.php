<?php
Env::useHelper('dimension_widget');
Env::useHelper('emails_functions', 'mail');

$account_options = array();
$default_accounts = array();
if (logged_user()->hasMailAccounts()) {
	$accounts = MailAccounts::getMailAccountsByUser(logged_user());
	foreach ($accounts as $account) { /* @var $account MailAccount */
		$account_options[] = array(
			'value' => (string) $account->getId(),
			'label' => $account->getName() ? $account->getName() : $account->getEmail(),
		);
		$default_accounts[] = (string) $account->getId();
	}
}

$widget_data = evx_widgets_build_feed_widget_data(array(
	'widget_name'     => 'emails',
	'data_provider'   => 'emails_data_provider',
	'no_objects_text' => lang('no emails to display'),
	'view_all_onclick' => function() {
		return "og.openLink(og.getUrl('mail', 'list_all')); event.stopPropagation(); return false;";
	},
	'view_all_label' => lang('view all') . ' ' . lang('emails'),
	'header_extra_actions' => array(
		array(
			'icon'    => 'icon-square-pen',
			'title'   => lang('emails compose title'),
			'onclick' => "og.openLink(og.getUrl('mail', 'add_mail'));",
		),
	),
	'extra_filters' => array(
		array(
			'key'     => 'accounts',
			'label'   => lang('accounts'),
			'type'    => 'checklist',
			'options' => $account_options,
			'default' => $default_accounts,
		),
	),
	'labels' => array(
		'noItems'          => lang('no emails to display'),
		'save'             => lang('save'),
		'cancel'           => lang('cancel'),
		'saveError'        => lang('emails save error'),
		'displayLines'     => lang('emails display lines'),
		'displayItemsUnit' => lang('emails'),
		'generalSettings'  => lang('emails general settings'),
		'widgetSettings'   => lang('emails widget settings'),
		'apply'            => lang('apply'),
	),
));

if ($widget_data !== false) {
	include 'template.php';
}
