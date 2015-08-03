<?php

$panel = TabPanels::instance()->findById('documents-panel');
if ($panel instanceof TabPanel && $panel->getEnabled()) {
	$limit = 5;
	$result = ProjectFiles::instance()->listing(array(
		"extra_conditions" => "AND updated_by_id > 0",
		"order" => "updated_on",
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
		$widget_title = lang('documents'). ' '. lang('in').' '. implode(", ", $mnames);
	}
	
	$total = $result->total;
	$documents = $result->objects;
	$genid = gen_id();
	if ($total) {
		include_once 'template.php';
	}
}