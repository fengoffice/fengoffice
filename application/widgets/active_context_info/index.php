<?php
$genid = gen_id();

$members = array();

$context = active_context();
if(isset($context)){
	foreach ($context as $selection) {
		if ($selection instanceof Member) {
			$members[] = $selection;
		}
	}	
}

if (count($members) == 1) {
	$member = $members[0];
	
	$prop_html = "";
	Hook::fire("render_widget_member_information", $member, $prop_html);
	
	$ot = ObjectTypes::findById($member->getObjectTypeId());
	if ($ot->getName()=='project_folder' || $ot->getName()=='customer_folder') {
		$ot = ObjectTypes::findByName('folder');
	}
	
	$cp_html = "";
	$custom_properties = MemberCustomProperties::getAllMemberCustomPropertiesByObjectType($ot->getId());
	foreach ($custom_properties as $cp) {
		$cp_name = $cp->getName();
		$cp_values = MemberCustomPropertyValues::getMemberCustomPropertyValues($member->getId(), $cp->getId());
		
		if(is_array($cp_values) && count($cp_values) > 0){
			$first = true;
			$cp_html .= '<div class="cp-info"><span class="bold">'.$cp_name.': </span>';
			foreach ($cp_values as $cp_val) {
				if (!$first) {
					$cp_html .= ", ";
				} 
				$first = false;
				$cp_html .= $cp_val->format_value();
			}
			$cp_html .= '</div>';
		}
	}
	
	if (trim($prop_html . $cp_html) != "") {
		include_once 'template.php';
	}
}
