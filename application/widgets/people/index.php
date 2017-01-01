<?php
	if (!can_manage_security(logged_user())) {
		return;
	}

	$context = active_context();
	$limit = 11;

	$members_ids = array();
	$active_members = array();
	$contacts = array();
	if (is_array($context)) {
		foreach ($context as $member) {
			if ($member instanceof Member){
				$members_ids[] = $member->getId();
				$active_members[] = $member;
			}
		}

		if (count($members_ids) > 0) {
		//get all users with perrmissions in this members
			$dim_c = new DimensionController();
			$contacts_ids = $dim_c->get_allowed_users_in_members($members_ids);

			if(count($contacts_ids) > 0){
				$contacts_ids = array_flat($contacts_ids);
				$contacts_ids = implode(",", $contacts_ids);
				$contacts = Contacts::findAll(array('conditions' => 'o.id IN (' . $contacts_ids . ')'));
			}
		}else{
			$users_with_permissions_in_root = get_users_with_permissions_in_root();
			$contacts = $users_with_permissions_in_root;
		}
	}
	
	Hook::fire('contact_check_can_view_in_array', null, $contacts);

	$mnames = array();
	//if there are members selcted
	if (count($active_members) > 0) {
		$mids = array();

		foreach ($active_members as $member) {
			$mnames[] = clean($member->getName());
			$mids[] = clean($member->getid());
		}
		
		//widget title
		$widget_title = lang("users in", implode(", ", $mnames));
		$mids = implode(",", $mids);
	
	} else {
		$widget_title = lang("users");
	}
	
	$total = count($contacts);
	$render_add = can_manage_security(logged_user());
	$genid = gen_id();
	
	if (($total > 0)) {
		include_once 'template.php';
	}