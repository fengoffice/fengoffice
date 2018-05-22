<?php 
	$genid = gen_id();
	$typeId= ObjectTypes::instance()->findByName("workspace")->getId();
	
	//Check if There is a workspace in the active context
	/* @var $member Member */ 
	foreach (active_context_members(false) as $memberId){
		$member = Members::instance()->findById($memberId);
		if ( $member->getObjectTypeId() == $typeId ) {
			$id = $member->getObjectId();
			
			if ($workspace = Workspaces::instance()->findById($id) && trim($member->getDescription()) != ""){
				$description = $member->getDescription();
				include_once 'template.php';
				break ;
			}
		} 
	}
	
