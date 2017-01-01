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

foreach ($members as $member) {
	
	$prop_html = "";
	Hook::fire("render_widget_member_information", $member, $prop_html);
	
	$ot = ObjectTypes::findById($member->getObjectTypeId());
	if ($ot->getName()=='project_folder' || $ot->getName()=='customer_folder') {
		$ot = ObjectTypes::findByName('folder');
	}
	
	$cp_html = "";
	if (Plugins::instance()->isActivePlugin('member_custom_properties')) {
		$custom_properties = MemberCustomProperties::getAllMemberCustomPropertiesByObjectType($ot->getId());
		foreach ($custom_properties as $cp) {
			if ($cp->getType() == 'color' || $cp->getCode() == 'calculate the status automatically_special') {
				continue;
			}
			$cp_name = $cp->getName();
			$cp_values = MemberCustomPropertyValues::getMemberCustomPropertyValues($member->getId(), $cp->getId());
			
			$cp_html_cp = "";
			if(is_array($cp_values) && count($cp_values) > 0){
				$first = true;
				$cp_html_cp .= '<tr class="cp-info"><td style="width:160px;"><span class="bold">'.$cp_name.': </span></td><td>';
				$cp_html_vals = "";
				foreach ($cp_values as $cp_val) {
					$formatted_val = $cp_val->format_value();
					if ($formatted_val == "") continue;
					
					if (!$first) {
						$cp_html_vals .= ", ";
					}
					$first = false;
					$cp_html_vals .= $formatted_val;
					
					if (!$cp->getIsMultipleValues()) {
						break;
					}
				}
				$cp_html_cp .= $cp_html_vals;
				$cp_html_cp .= '</td></tr>';
				if ($cp_html_vals != "") {
					// content
					$cp_html .= $cp_html_cp;
				}
			}
		}
	}
	// title
	if (trim($prop_html . $cp_html) != "") {
		$prop_html = '<tr class="cp-info"><td colspan="2"><h2>'.lang('properties').'</h2></td></tr>' . $prop_html;
	}
	
	$assoc_mem_html = "";
	$dim_associations = DimensionMemberAssociations::getAllAssociatationsForObjectType($member->getDimensionId(), $member->getObjectTypeId());
	foreach ($dim_associations as $a) {/* @var $a DimensionMemberAssociation */
		
		$assoc_mem_ids = explode(',', MemberPropertyMembers::getAllMemberIds($a->getId(), $member->getId()));
		$assoc_mem_ids = array_merge($assoc_mem_ids, explode(',', MemberPropertyMembers::getAllPropertyMemberIds($a->getId(), $member->getId())));
		$assoc_mem_ids = array_unique(array_filter($assoc_mem_ids));
		
		if (is_array($assoc_mem_ids) && count($assoc_mem_ids) > 0) {
			$did = $member->getDimensionId() == $a->getDimensionId() ? $a->getAssociatedDimensionMemberAssociationId() : $a->getDimensionId();
			$assoc_otid = $member->getDimensionId() == $a->getDimensionId() ? $a->getAssociatedObjectType() : $a->getObjectTypeId();
			$dim = Dimensions::getDimensionById($did);
			
			if ($dim->getCode() != 'feng_persons') {
				
				$mems = Members::findAll(array('conditions' => "id IN (".implode(',', $assoc_mem_ids).")"));
				
				$assoc_ot = ObjectTypes::findById($assoc_otid);
				$label = ($a->getIsMultiple() || count($assoc_mem_ids) > 1) ? $dim->getName() : lang($assoc_ot->getName());
				
				// title
				if ($assoc_mem_html == "") {
					$assoc_mem_html .= '<tr class="cp-info"><td colspan="2"><h2>'.lang('relations').'</h2></td></tr>';
				}
				// content
				$assoc_mem_html .= '<tr class="cp-info"><td style="padding-top:4px;width:160px;"><span class="bold">'.$label.': </span></td><td>';
				$assoc_mem_html_vals = "";
				
				foreach ($mems as $m) {
					$assoc_mem_html_vals .= '<span class="member-path real-breadcrumb og-wsname-color-'.$m->getColor().'" style="display:inline-block;margin:1px;padding:2px 4px;">' . $m->getName() . '</span>';
				}
				$assoc_mem_html .= $assoc_mem_html_vals . '</td></tr>';
			}
		}
	}

	if (trim($prop_html . $cp_html . $assoc_mem_html) != "") {
		include 'template.php';
	}
}
