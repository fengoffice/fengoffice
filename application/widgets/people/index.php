<?php
	//limit to the number of users to be displayed in widgets
	$limit = 6;
	$order = array("last_activity", "updated_on");
	
	$active_members = array();
	$context = active_context();
	if (is_array($context)) {
		foreach ($context as $selection) {
			if ($selection instanceof Member) $active_members[] = $selection;
		}
	}
	$mnames = array();
	//if there are members selcted
	if (count($active_members) > 0) {
		$mids = array();
		$allowed_contact_ids = array();
		foreach ($active_members as $member) {
			$allowed_contact_ids[] = $member->getAllowedContactIds();
			$mnames[] = clean($member->getName());
			$mids[] = clean($member->getid());
		}
		$intersection = $allowed_contact_ids[0];
		if (count($allowed_contact_ids) > 1) {
			for ($i = 1; $i < count($allowed_contact_ids); $i++) {
				$intersection = array_intersect($intersection, $allowed_contact_ids[$i]);
			}
		}
		foreach ($intersection as $k => &$v) {
			if ($v == '') unset($intersection[$k]); 
		}
		
		//user to display on the widget
		$intersection_condition = count($intersection) > 0 ? 'object_id IN ('.implode(',',$intersection).') AND' : '';
		$intersection_condition = "";
				
		$result = Contacts::instance()->listing(array(
				"order" => $order,
				"order_dir" => "DESC",
				"extra_conditions" => " AND `is_company` = 0 AND disabled = 0",
				"start" => 0,
				"limit" => $limit
		));
		
		$total = $result->total ;
		$contacts = $result->objects;
		
		$contacts_for_combo = null;
		//if logged user can assign permissions
		if(can_manage_security(logged_user())){
			//users to display on the combo
			$intersection_condition = count($intersection) > 0 ? 'o.id NOT IN ('.implode(',',$intersection).') AND' : '';
			$contacts_for_combo = Contacts::findAll(array(
				'conditions' => $intersection_condition . ' `is_company` = 0 AND `user_type` > '.logged_user()->getUserType().' AND disabled = 0',
				'order' => 'first_name',
				'order_dir' => 'desc',
			));
		}
		
		//add people button name
		if (isset($mnames[0])){
			$add_people_btn = true;
		}
		
		//widget title
		$widget_title = lang("users in", implode(", ", $mnames));
		$mids = implode(",", $mids);
	
	} else {
		$widget_title = lang("users");
		$result = Contacts::instance()->listing(array(
			"order" => $order,
			"order_dir" => "DESC",
			"extra_conditions" => " AND `is_company` = 0 AND disabled = 0 AND `user_type` > 0",
			"start" => 0,
			"limit" => $limit
		));
		
		$total = $result->total ;
		$contacts = $result->objects;
		
	}
	
	$render_add = can_manage_security(logged_user());
	$genid = gen_id();
	
	if (($total > 0 || $render_add) && !logged_user()->isGuest()) {
		include_once 'template.php';
	}