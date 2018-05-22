<?php

	$context = active_context();
	$limit = 10;
	$persons_to_show = 5;

	$members_ids = array();
	$active_members = array();
	$contacts = array();
	
	foreach ($context as $member) {
		if ($member instanceof Member){
			$members_ids[] = $member->getId();
			$active_members[] = $member;
		}
	}
	
	$mnames = array();
	//if there are members selcted
	if (count($active_members) > 0) {
		$mids = array();

		foreach ($active_members as $member) {
			$mnames[] = clean($member->getName());
			$mids[] = clean($member->getid());
		}
		
		$extra_conditions = " AND user_type=0 AND is_company=0 ";
		
		$listing = Contacts::instance()->listing(array(
			"order" => '`name`',
			"order_dir" => "ASC",
			"extra_conditions" => $extra_conditions,
			"start" => 0,
			"limit" => $limit,
			'count_results' => false,
		));
		
		$contacts = $listing->objects;
		$total = $listing->total;
		
		//widget title
		$widget_title = lang("contacts in", implode(", ", $mnames));
		$mids = implode(",", $mids);
	
		
		$render_add = can_manage_security(logged_user());
		$genid = gen_id();
		
		if (($total > 0)) {
			include_once 'template.php';
		}
	
	}
