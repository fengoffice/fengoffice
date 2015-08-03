<?php 

	$limit = 10;
	$result = Comments::instance()->listing(array(
		"order" => "created_on",
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
		$widget_title = lang('latest comments'). ' '. lang('in').' '. implode(", ", $mnames);
	}
		
	$total = $result->total;
	$comments = $result->objects;
	$genid = gen_id();
	if ($total > 0) {
		include_once 'template.php';
	}
