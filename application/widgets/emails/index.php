<?php
if (logged_user()->hasMailAccounts()) { 

	$limit = 10;
	$result = MailContents::instance()->listing(array(
		"order" => "sent_date",
		"order_dir" => "desc",
		"start" => 0,
		"limit" => $limit
	));
	
	$active_members = array();
	$context = active_context();
	foreach ($context as $selection) {
		if ($selection instanceof Member) $active_members[] = $selection;
	}
	if (count($active_members) > 0) {
		$mnames = array();
		$allowed_contact_ids = array();
		foreach ($active_members as $member) {
			$mnames[] = clean($member->getName());
		}
		$widget_title = lang('unread emails'). ' '. lang('in').' '. implode(", ", $mnames);
	}
	
	$total = $result->total;
	$emails = $result->objects;
	$genid = gen_id();
	if ($total > 0) {
		include_once 'template.php';
	}
}