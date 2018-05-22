<?php
$selector_genid = gen_id();

if (!isset($is_multiple)) $is_multiple = false;
if (!isset($member_type_id)) $member_type_id = null;
if (!isset($hide_label)) $hide_label = false;
if (!isset($selected_member_ids)) $selected_member_ids = array();
if (!isset($select_current_context)) $select_current_context = true;

if (!is_array($selected_member_ids)) $selected_member_ids = explode(',', $selected_member_ids);

$selector_params = array('is_multiple' => $is_multiple, 'label' => $label, 'hide_label' => $hide_label, 'root_lang' => lang('none'), 
	'hidden_field_name' => $hf_name, 'allowedMemberTypes' => $member_type_id, 'dont_filter_this_selector' => true);

if ($select_current_context) {
	foreach (active_context() as $selection) {
		if ($selection instanceof Member) $selected_member_ids[] = $selection->getId();
	}
}
render_single_member_selector($dim, $selector_genid, $selected_member_ids, $selector_params, false);

?>
<div class="clear"></div>
