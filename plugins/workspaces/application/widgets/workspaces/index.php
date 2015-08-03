<?php
$limit = 5;
$genid = gen_id();

$ws_dimension = Dimensions::findByCode('workspaces');
$dim_controller = new DimensionController();

$selected_ws = '0';
$allowed_members = array();
$add_ctx_members = true;
$show_widget = true;

$context = active_context();
if(isset($context)){
	foreach ($context as $selection) {
		if ($selection instanceof Dimension && $selection->getCode() == 'workspaces') {
			$add_ctx_members = false;
		} else if ($selection instanceof Member) { 
			
			if ($selection->getObjectTypeId() == Workspaces::instance()->getObjectTypeId()) {
				$allowed_members[] = $selection->getId();
				$selected_ws = $selection->getId();
			} else {
				$show_widget = false;
			}
		}
	}	
}

if ($show_widget) {
	
	$extra_conditions = " AND parent_member_id " . ($add_ctx_members && count($allowed_members) > 0 ? "IN (". implode(",", $allowed_members) .")" : "=0");
	
	$parent = null;
	$context = active_context();
	if (is_array($context)) {
		foreach ($context as $selection) {
			if ($selection instanceof Member && $selection->getDimensionId() == $ws_dimension->getId()) {
				$parent = $selection;
				break;
			}
		}
	}
	
	$ws_ot_id = ObjectTypes::findByName('workspace')->getId();
	$pg_array = logged_user()->getPermissionGroupIds();
	$current_member_cond = $parent instanceof Member ? "AND parent_member_id=".$parent->getId() : "";
	
	$members = Members::findAll(array(
		'limit' => $limit,
		'order' => "depth, name",
		'conditions' => "object_type_id=$ws_ot_id $current_member_cond AND archived_by_id=0 AND EXISTS (
			SELECT cmp.member_id FROM ".TABLE_PREFIX."contact_member_permissions cmp WHERE cmp.member_id=".TABLE_PREFIX."members.id AND cmp.permission_group_id IN (".implode(',',$pg_array)."))"
	));
	
	if ($parent instanceof Member && count($members) < $limit) {
		$tmp_ids = array();
		foreach ($members as $m) {
			$tmp_ids[] = $m->getId();
		}
		
		$extra_conds = "AND archived_by_id=0 AND EXISTS (
			SELECT cmp.member_id FROM ".TABLE_PREFIX."contact_member_permissions cmp WHERE cmp.member_id=".TABLE_PREFIX."members.id AND cmp.permission_group_id IN (".implode(',',$pg_array)."))";
	
		$childs = $parent->getAllChildren(true, 'name', $extra_conds);
	
		foreach ($childs as $ch) {
			if (in_array($ch->getId(), $tmp_ids)) continue;
			if ($ch->getObjectTypeId() == $ws_ot_id && count($members) <= $limit) {
				$members[] = $ch;
			}
			if (count($members) >= $limit) break;
		}
	}
	
	$total = count($members);
	
	if ((is_array($members) && count($members) > 0) || can_manage_dimension_members(logged_user())) {
		$data_ws = $members;
		include_once 'template.php';
	}

}