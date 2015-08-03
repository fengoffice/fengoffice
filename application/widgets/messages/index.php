<?php 

$panel = TabPanels::instance()->findById('messages-panel');
if ($panel instanceof TabPanel && $panel->getEnabled()) {
	$limit = 5;
	$result = ProjectMessages::instance()->listing(array(
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
		$widget_title = lang('notes'). ' '. lang('in').' '. implode(", ", $mnames);
	}
		
	$total = $result->total;
	$messages = $result->objects;
	$genid = gen_id();
	if ($total) {
		include_once 'template.php';
	}
}