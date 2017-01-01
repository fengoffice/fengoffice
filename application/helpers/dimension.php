<?php
require_javascript('og/modules/addMessageForm.js');

function render_member_selectors($content_object_type_id, $genid = null, $selected_member_ids = null, $options = array(), $skipped_dimensions = null, $simulate_required = null, $default_view = true) {
	if (is_numeric($content_object_type_id)) {
		if (is_null($genid)) $genid = gen_id();
		$user_dimensions  = get_user_dimensions_ids(); // User allowed dimensions
		$dimensions = array();
		
		// Diemsions for this content type
		if ( $all_dimensions = Dimensions::getAllowedDimensions($content_object_type_id) ) {
			Hook::fire("allowed_dimensions_in_member_selector", array('ot' => $content_object_type_id), $all_dimensions);
			
			foreach ($all_dimensions as $dimension){
				if ( isset($user_dimensions[$dimension['dimension_id']] ) ){
					$custom_name = DimensionOptions::getOptionValue($dimension['dimension_id'], 'custom_dimension_name');
					$dimension['dimension_name'] = $custom_name && trim($custom_name) != "" ? $custom_name : lang($dimension['dimension_code']);
					
					$dimensions[] = $dimension;
				}
			}
		}
		
		if ($dimensions != null && count($dimensions)) {
			if (is_null($selected_member_ids) && array_var($options, 'select_current_context')) {
				$context = active_context();
				$selected_member_ids = array();
				$assoc_member_ids = array();
				
				foreach ($context as $selection) {
					if ($selection instanceof Member) {
						$selected_member_ids[] = $selection->getId();
					
						// get the related members that are defined to autoclassify in its association config
						$associations = DimensionMemberAssociations::getAllAssociatationsForObjectType($selection->getDimensionId(), $selection->getObjectTypeId());
						foreach ($associations as $a) {
							$autoclassify_in_related = (bool)DimensionAssociationsConfigs::getConfigValue($a->getId(), 'autoclassify_in_property_member');
							if ($autoclassify_in_related) {
								$tmp = MemberPropertyMembers::getAllPropertyMemberIds($a->getId(), $selection->getId());
								$tmp = array_filter(explode(',', $tmp));
								if (is_array($tmp) && count($tmp) > 0) {
									$assoc_member_ids = array_merge($assoc_member_ids, $tmp);
								}
							}
						}
					}
				}
				
				// foreach autoclassified related member do the same
				$assoc_members = array();
				if (count($assoc_member_ids) > 0) {
					$assoc_members = Members::findAll(array('conditions' => "id IN (".implode(',', $assoc_member_ids).")"));
				}
				foreach ($assoc_members as $assoc_member) {
					if ($assoc_member instanceof Member) {
						$associations = DimensionMemberAssociations::getAllAssociatationsForObjectType($assoc_member->getDimensionId(), $assoc_member->getObjectTypeId());
						foreach ($associations as $a) {
							$autoclassify_in_related = (bool)DimensionAssociationsConfigs::getConfigValue($a->getId(), 'autoclassify_in_property_member');
							if ($autoclassify_in_related) {
								$tmp = MemberPropertyMembers::getAllPropertyMemberIds($a->getId(), $assoc_member->getId());
								$tmp = array_filter(explode(',', $tmp));
								if (is_array($tmp) && count($tmp) > 0) {
									$assoc_member_ids = array_merge($assoc_member_ids, $tmp);
								}
							}
						}
					}
				}
				
				// merge the resulting autoclassified related members with the original selected members
				$selected_member_ids = array_merge($selected_member_ids, $assoc_member_ids);
			}
			
			
			if (is_null($selected_member_ids)) $selected_member_ids = array();
			
			$skipped_dimensions_cond = "";
			if (is_array($skipped_dimensions) && count($skipped_dimensions) > 0) {
				$skipped_dimensions_cond = " AND dimension_id NOT IN (".implode(',', $skipped_dimensions).")";
			}
			
			// Set view variables
			$manageable_conds = ' AND dimension_id IN (SELECT id from '.TABLE_PREFIX.'dimensions WHERE is_manageable=1)' . $skipped_dimensions_cond;
			$selected_members = count($selected_member_ids) > 0 ? Members::findAll(array('conditions' => 'id IN ('.implode(',', $selected_member_ids).') '.$manageable_conds)) : array();
			$selected_member_ids = array();
			foreach ($selected_members as $sm) $selected_member_ids[] = $sm->getId();
			$selected_members_json = "[".implode(',', $selected_member_ids)."]";
			$component_id = "$genid-member-selectors-panel-$content_object_type_id";
			$object_is_new = is_null($selected_members);
			
			$listeners = array_var($options, 'listeners', array());
			$allowed_member_type_ids = array_var($options, 'allowedMemberTypes', null);
			
			
			$initial_selected_members = $selected_members;
			if (count($initial_selected_members) == 0) {
				$selected_context_member_ids = active_context_members(false);
				if (count($selected_context_member_ids) > 0) {
					$initial_selected_members = Members::findAll(array('conditions' => 'id IN ('.implode(',', $selected_context_member_ids).')'));
				}
			}
			
			$tmp = array();
			foreach ($initial_selected_members as $ism) {
				if ($ism->getDimension()->getIsManageable()) $tmp[] = $ism;
			}
			$initial_selected_members = $tmp;
			
			// Render view
			include get_template_path("components/multiple_dimension_selector", "dimension");
			
		}
	}
}


function render_single_member_selector(Dimension $dimension, $genid = null, $selected_member_ids = null, $options = array(), $default_view = true) {
	if (is_null($genid)) $genid = gen_id();
	
	$dim_info = array(
		'dimension_id' => $dimension->getId(),
		'dimension_code' => $dimension->getCode(),
		'dimension_name' => $dimension->getName(),
		'is_manageable' => $dimension->getIsManageable(),
		'is_required' => array_var($options, 'is_required'),
		'is_multiple' => array_var($options, 'is_multiple'),
	);
	
	$dimensions = array($dim_info);
	if (!is_array($selected_member_ids)) {
		$selected_member_ids = array();
	}
	foreach ($selected_member_ids as $k => &$v) {
		if (!is_numeric($v)) unset($selected_member_ids[$k]);
	}
	if (count($selected_member_ids) > 0) {
		$sql = "SELECT m.id FROM ".TABLE_PREFIX."members m WHERE m.id IN (".implode(',', $selected_member_ids).") AND m.dimension_id=".$dimension->getId();
		$clean_sel_member_ids = array_flat(DB::executeAll($sql));
		$selected_member_ids = $clean_sel_member_ids;
	}
	
	$content_object_type_id = array_var($options, 'content_object_type_id');
	$initial_selected_members = $selected_member_ids;
	
	if (is_null($selected_member_ids)) $selected_member_ids = array();
			
	// Set view variables
	$selected_members = count($selected_member_ids) > 0 ? Members::findAll(array('conditions' => 'id IN ('.implode(',', $selected_member_ids).')')) : array();
	$selected_members_json = "[".implode(',', $selected_member_ids)."]";
	$component_id = "$genid-member-selectors-panel-$content_object_type_id";
	
	$listeners = array_var($options, 'listeners', array());
	$allowed_member_type_ids = array_var($options, 'allowedMemberTypes', null);
	
	$hide_label = array_var($options, 'hide_label', false);
	
	if (isset($options['label'])) $label = $options['label'];

	// option to disable tree reloading when selectin in a related dimension (e.g. in parent selector)
	$dont_filter_this_selector = array_var($options, 'dont_filter_this_selector', false);
	
	// option to show default selection checkboxes
	$default_selection_checkboxes = array_var($options, 'default_selection_checkboxes', false);
	$related_member_id = array_var($options, 'related_member_id', 0);
	
	// Render view
	include get_template_path("components/multiple_dimension_selector", "dimension");
}

function update_all_childs_depths($member, $old_parent_id) {
	//CHILDS
	//Get all member childs recursive
	$childs = get_all_children_sorted(array($member->getId()));
	if(count($childs) == 0){
		return;
	}
	
	$childs_ids = array();
	foreach ($childs as $child) {
		$childs_ids[] = $child['id'];
	}
	$m_depth = $member->getDepth();

	if($old_parent_id > 0){
		$old_parent_member = Members::findById($old_parent_id);
		$old_member_depth = $old_parent_member->getDepth() + 1;
	}else{
		$old_member_depth = 1;
	}

	$depth_diff = $m_depth - $old_member_depth;

	$childs_ids_string = implode(',', $childs_ids);
	$update_depth_sql = "UPDATE ".TABLE_PREFIX."members SET `depth` = `depth` + $depth_diff WHERE id IN($childs_ids_string);";
	DB::execute($update_depth_sql);
}





function save_associated_dimension_members($params) {

	$member = array_var($params, 'member');
	if (!$member instanceof Member) return;

	$request = array_var($params, 'request');
	$associated_members = array_var($request, 'associated_members', array());
	
	$is_new = array_var($params, 'is_new');
	
	foreach ($associated_members as $assoc_id => $assoc_mem_ids_str) {
		$assoc_mem_ids = json_decode($assoc_mem_ids_str, true);
		
		$a = DimensionMemberAssociations::findById($assoc_id);

		if ($member->getDimensionId() == $a->getDimensionId()) {
			$reverse_relation = false;
			$rel_dimension = Dimensions::getDimensionById($a->getAssociatedDimensionMemberAssociationId());
			$rel_ot = ObjectTypes::findById($a->getAssociatedObjectType());
		} else {
			$reverse_relation = true;
			$rel_dimension = Dimensions::getDimensionById($a->getDimensionId());
			$rel_ot = ObjectTypes::findById($a->getObjectTypeId());
		}
		
		if ($a->getIsMultiple()) {
			$memcol = $reverse_relation ? "property_member_id" : "member_id";
			// if association is multiple delete all relations and add the new ones
			MemberPropertyMembers::instance()->delete('association_id = '.$assoc_id.' AND '.$memcol.' = '.$member->getId());
			
			foreach ($assoc_mem_ids as $rel_mem_id) {
				associate_member_to_status_member($member, 0, $rel_mem_id, $rel_dimension, $rel_ot, false);
			}
		} else {
			// asociate objects to the new related member, remove from the old one
			$old_related_mem_id = get_associated_status_member_id($member, $rel_dimension, $rel_ot, $reverse_relation);
			
			associate_member_to_status_member($member, $old_related_mem_id, array_var($assoc_mem_ids, 0), $rel_dimension, $rel_ot);
		}
		
		if ($a->getAllowsDefaultSelection()) {
			$member_info = array_var($request, 'member');
			$default_selection = array_var($member_info, 'default_selection');
			
			save_default_associated_member_selections($a->getId(), $member->getId(), $default_selection);
		}
	
		$null = null;
		Hook::fire('after_associating_members', array('member' => $member, 'association' => $a, 'is_new' => $is_new,
				'rel_dim' => $rel_dimension, 'rel_ot' => $rel_ot, 'assoc_member_ids' => $assoc_mem_ids), $null);
		
	}
	
	$null = null;
	Hook::fire('after_member_association_changed', array('member' => $member, 'request' => $request, 'is_new' => $is_new), $null);
}




function render_associated_dimensions_selectors($params) {
	
	$member = array_var($params, 'member');
	if (!$member instanceof Member) return;
	
	$is_new = array_var($params, 'is_new');
	
	$enabled_dimensions = config_option('enabled_dimensions');
	
	if (Plugins::instance()->isActivePlugin("member_templates") && get_id('template_id') > 0) {
		$member_template = MemberTemplates::findById(get_id('template_id'));
		if ($member_template instanceof MemberTemplate) {
	
			$ini_assocs = MemberTemplatesInitialAssociations::findAll(array('conditions' => 'template_id='.$member_template->getId()));
			foreach ($ini_assocs as $ini_assoc) {
				$a = DimensionMemberAssociations::findById($ini_assoc->getDimAssociationId());
				if ($a instanceof DimensionMemberAssociation) {
					$initial_values[$a->getAssociatedDimensionMemberAssociationId()] = $ini_assoc->getAssociatedMemberId();
				}
			}
		}
	}
	
	$genid = gen_id();
	$suffix = 1;
	
	$dim_associations = DimensionMemberAssociations::getAllAssociatationsForObjectType($member->getDimensionId(), $member->getObjectTypeId());
	
	foreach ($dim_associations as $dim_association) {
		/* @var $dim_association DimensionMemberAssociation */
		if ($member->getDimensionId() == $dim_association->getDimensionId()) {
			$reverse_relation = false;
			$dimension = Dimensions::getDimensionById($dim_association->getAssociatedDimensionMemberAssociationId());
			$ot = ObjectTypes::findById($dim_association->getAssociatedObjectType());
		} else {
			$reverse_relation = true;
			$dimension = Dimensions::getDimensionById($dim_association->getDimensionId());
			$ot = ObjectTypes::findById($dim_association->getObjectTypeId());
		}
		
		$comp_genid = $genid . "_$suffix";
		if (in_array($dimension->getId(), $enabled_dimensions)) {
			echo '<div class="field '.$ot->getName().'">';
			
			if ($is_new) {
				$selected_ids = array(array_var($initial_values, $dimension->getId()));
			} else {
				$selected_ids = get_all_associated_status_member_ids($member, $dimension, $ot, $reverse_relation);
			}
			
			$select_fn = $dim_association->getIsMultiple() ? "og.onAssociatedMemberTypeSelectMultiple" : "og.onAssociatedMemberTypeSelect";
			$remove_fn = $dim_association->getIsMultiple() ? "og.onAssociatedMemberTypeRemoveMultiple" : "og.onAssociatedMemberTypeRemove";
			
			$label = lang(str_replace('_',' ', $ot->getName()) . ($dim_association->getIsMultiple() ? 's' : ''));
			$hf_name = 'associated_members['.$dim_association->getId().']';
			
			render_single_member_selector($dimension, $comp_genid, $selected_ids, array(
					'is_multiple' => $dim_association->getIsMultiple(),
					//'allowedMemberTypes' => array($ot->getId()),
					'content_object_type_id' => $ot->getId(), 
					'label' => $label, 
					'allow_non_manageable' => true, 
					'hidden_field_name' => $hf_name,
					'select_function' => $select_fn, 
					'listeners' => array('on_remove_relation' => "$remove_fn('$comp_genid', ".$dimension->getId().", '$hf_name');"),
					'default_selection_checkboxes' => $dim_association->getAllowsDefaultSelection(),
					'width' => 400,
					'related_member_id' => $member->getId()
				), false);
			
			echo '</div><div class="clear"></div>';
		}
		
		$suffix++;
	}
	
}










