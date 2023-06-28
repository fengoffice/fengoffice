<?php
if (logged_user()->hasMailAccounts()) { 

	$limit = 10;
	$result = MailContents::getEmails(null, 'received', "unread", null, null, 0, $limit, "received_date", "DESC", null, 'unarchived', null, false, "", true);

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
		$widget_title = lang('unread emails');
	}
	
	$total = $result->total;
	$emails = $result->objects;
	$genid = gen_id();
	if ($total > 0) {
		include_once 'template.php';
	}
}