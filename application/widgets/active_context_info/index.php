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

if (count($members) == 1){
	$member = $members[0];
	
	$prop_html = "";
	Hook::fire("render_widget_member_information", $member, $prop_html);
	
	$ot = ObjectTypes::instance()->findById($member->getObjectTypeId());
	if ($ot->getName()=='project_folder' || $ot->getName()=='customer_folder') {
		$ot = ObjectTypes::findByName('folder');
	}
	
	$cp_html = "";
    Hook::fire("render_member_properties_for_view", array('member' => $member, 'visible_by_default' => 'all'), $cp_html);

	// title
	if (trim($prop_html . $cp_html) != "") {
	//	$prop_html = '<tr class="cp-info"><td colspan="2"><h2 style="border-bottom: 0px; ">'.lang('details').'</h2></td></tr>' . $prop_html;
	}
	
	$assoc_mem_html = "";
	
	$render_dim_associations = true;
	Hook::fire('member_form_render_associated_dimension_selectors', array('member' => $member, 'is_new' => $member->isNew()), $render_dim_associations);
	
	if ($render_dim_associations) {
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
				
				$mems = Members::instance()->findAll(array('conditions' => "id IN (".implode(',', $assoc_mem_ids).")"));
				
				$assoc_ot = ObjectTypes::instance()->findById($assoc_otid);
				$custom_ot_name = Members::getTypeNameToShowByObjectType($dim->getId(), $assoc_ot->getId());
				$label = ($a->getIsMultiple() || count($assoc_mem_ids) > 1) ? $dim->getName() : $custom_ot_name;
				
				// title
				if ($assoc_mem_html == "") {
				//	$assoc_mem_html .= '<tr class="cp-info"><td colspan="2"><h2>'.lang('relations').'</h2></td></tr>';
				}
				// content
				$assoc_mem_html .= '<tr class="cp-info"><td style="padding-top:4px;padding-left:21px;width:160px;"><span class="bold">'.$label.': </span></td><td>';
				$assoc_mem_html_vals = "";
				
				foreach ($mems as $m) {
					$assoc_mem_html_vals .= '<span class="member-path real-breadcrumb og-wsname-color-'.$m->getColor().'" style="display:inline-block;margin:1px;padding:2px 4px;">' . $m->getName() . '</span>';
				}
				$assoc_mem_html .= $assoc_mem_html_vals . '</td></tr>';
			}
		}
	  }
	}
	//Logger::log($cp_html);
	if (trim($cp_html) != "") {
		include 'template.php';
	}
}
