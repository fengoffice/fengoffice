<?php

// The following section renders a selector of one or multiple members for a dimension filter

if ($dim instanceof Dimension) {
	
	if (!isset($selector_genid)) {
		$selector_genid = gen_id();
	}
	
	if (!isset($is_multiple)) $is_multiple = false;
	if (!isset($member_type_id)) $member_type_id = null;
	if (!isset($hide_label)) $hide_label = false;
	if (!isset($selected_member_ids)) $selected_member_ids = array();
	if (!isset($select_current_context)) $select_current_context = true;
	if (!isset($select_context_associated_members)) $select_context_associated_members = true;
	if (!isset($label)) $label = null;
	if (!isset($root_lang)) $root_lang = lang('none');
	if (!isset($listeners)) {
		$listeners = array();
	} else {
		$listeners = (array) json_decode($listeners);
	}
	
	if (!is_array($selected_member_ids)) $selected_member_ids = explode(',', $selected_member_ids);
	
	$selector_params = array('is_multiple' => $is_multiple, 
							 'label' => $label, 
							 'hide_label' => $hide_label, 
							 'root_lang' => $root_lang, 
							 'listeners' => $listeners,
							 'hidden_field_name' => $hf_name, 
							 'allowedMemberTypes' => $member_type_id, 
							 'dont_filter_this_selector' => true);
	
	if (isset($width)) $selector_params['width'] = $width;
	if ($select_current_context) {
		foreach (active_context() as $selection) {
			if ($selection instanceof Member) {
				$selected_member_ids[] = $selection->getId();
				if ($select_context_associated_members) {
					$assoc_mem_ids = MemberPropertyMembers::getAllAssociatedMemberIds($selection->getId(),true);
					foreach ($assoc_mem_ids as $aid => $amids) {
						$a = DimensionMemberAssociations::findById($aid);
						// use only default associations
						$tmp_ot = ObjectTypes::instance()->findById($a->getObjectTypeId());
						$tmp_assoc_ot = ObjectTypes::instance()->findById($a->getAssociatedObjectType());
						if (!$tmp_ot || !$tmp_assoc_ot) continue;
						if ($a->getCode() != $tmp_ot->getName()."_".$tmp_assoc_ot->getName()) {
							continue;
						}
						foreach ($amids as $amid) $selected_member_ids[] = $amid;
					}
				}
			}
		}
	}
	$selected_member_ids = array_filter($selected_member_ids);
	render_single_member_selector($dim, $selector_genid, $selected_member_ids, $selector_params, false);

?>
<div class="clear"></div>

<?php } ?>