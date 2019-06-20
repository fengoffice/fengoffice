<?php
require_javascript('og/modules/addMessageForm.js');

function append_related_members_to_autoclassify(&$object_member_ids) {
	
	$object_members = array();
	if (is_array($object_member_ids) && count($object_member_ids) > 0) {
		$object_members = Members::findAll(array("conditions" => "id IN (".implode(',', $object_member_ids).")"));
	}
	$assoc_member_ids = array();
	
	foreach ($object_members as $selection) {
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
	$assoc_member_ids = array_unique(array_filter($assoc_member_ids));
	
	if (count($assoc_member_ids) > 0) {
		$assoc_members = Members::findAll(array("conditions" => "id IN (".implode(',', $assoc_member_ids).")"));
		$to_append = array();
		// append only if there is no other member of its dimension already set in the original array
		foreach ($assoc_members as $assoc_member) {
			$append_it = true;
			foreach ($object_members as $object_member) {
				if ($object_member->getDimensionId() == $assoc_member->getDimensionId() && $object_member->getObjectTypeId() == $assoc_member->getObjectTypeId()) {
					$append_it = false;
					break;
				}
			}
			if ($append_it) {
				$to_append[] = $assoc_member->getId();
			}
		}
		
		if (count($to_append) > 0) {
			$object_member_ids = array_merge($object_member_ids, $to_append);
		}
	}
}

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
 					
					//Added for debugging
					//Logger::log_r("Dimension: ".$dimension['dimension_name']."\n");
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
					
						if (!array_var($options, 'dont_select_associated_members')) {
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
				}
				
				if (!array_var($options, 'dont_select_associated_members')) {
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
				
			}
			
			
			if (is_null($selected_member_ids)) $selected_member_ids = array();
			
			// additional selected member ids (e.g.: taken from ot hierarchy)
			$additional_selected_member_ids = member_selector_additional_selected_ids(array_var($options,'object'), $dimensions);
			if (is_array($additional_selected_member_ids)) {
				$selected_member_ids = array_unique(array_filter(array_merge($selected_member_ids, $additional_selected_member_ids)));
			}
			
			
			// additional filters, by member id
			$additional_filters = member_selector_additional_ids_filter(array_var($options,'object'), $dimensions);
			if (is_array($additional_filters) && count($additional_filters) > 0) {
				$options['filter_by_ids'] = $additional_filters;
			}
			
			
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

    if (array_var($options, 'is_bootstrap')){
        include get_template_path("components/bootstrap_multiple_dimension_selector", "dimension");
    }else{
	    include get_template_path("components/multiple_dimension_selector", "dimension");
    }
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





function save_associated_dimension_members($params,$is_api = false,$data_api = null) {
	$member = array_var($params, 'member');


	if (!$member instanceof Member) return;
	
	$required_associations = DimensionMemberAssociations::getRequiredAssociatations($member->getDimensionId(), $member->getObjectTypeId());
	$required_associations_object = array();
	$required_associations_present = array();
	foreach ($required_associations as $a) {
		$required_associations_present[$a->getId()] = false;
		$required_associations_object[$a->getId()] = $a;
	}

	$request = array_var($params, 'request');
	if ($is_api){
        $associated_members = array_var($data_api, 'associated_members', array());
    }else{
	    $associated_members = array_var($request, 'associated_members', array());
    }

	$is_new = array_var($params, 'is_new');

	//Esto va en un hook
    Hook::fire('new_members_api', array('member' => $member,'is_api'=>$is_api), $associated_members);

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
		
		// use multiple selectors if the association is multiple or if editing the associated member (e.g.: proj. status)
		$is_multiple = $a->getIsMultiple() || $reverse_relation;
		
		if ($is_multiple) {
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
		
		if (count($assoc_mem_ids) > 0) {
			$required_associations_present[$a->getId()] = true;
		}
		
		$null = null;
		Hook::fire('after_associating_members', array('member' => $member, 'association' => $a, 'is_new' => $is_new,
				'rel_dim' => $rel_dimension, 'rel_ot' => $rel_ot, 'assoc_member_ids' => $assoc_mem_ids), $null);
		
	}
	
	// check if all required associated dimensions have a value
	foreach ($required_associations_present as $aid => $present) {
		if (!$present) {
			$assoc = $required_associations_object[$aid];
			if ($assoc->getIsMultiple()) {
				$assoc_dim = Dimensions::findById($assoc->getColumnValue('associated_dimension_id'));
				$property_name = $assoc_dim instanceof Dimension ? $assoc_dim->getName() : 'dimension';
			} else {
				$assoc_ot = ObjectTypes::findById($assoc->getColumnValue('associated_object_type_id'));
				$property_name = $assoc_ot instanceof ObjectType ? $assoc_ot->getObjectTypeName() : 'property';
			}
			throw new Exception(lang('custom property value required', $property_name));
		}
	}
	
	$null = null;
	Hook::fire('after_member_association_changed', array('member' => $member, 'request' => $request, 'is_new' => $is_new), $null);
}




function render_associated_dimensions_selectors($params) {
	
	$member = array_var($params, 'member');
	if (!$member instanceof Member) return;
	
	$is_new = array_var($params, 'is_new');
	
	$enabled_dimensions = config_option('enabled_dimensions');
	$initial_values = array();
	
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
	
	// initialize associated dimensions with active context
	if ($is_new) {
		$active_context = active_context();
		foreach ($active_context as $selection) {
			if ($selection instanceof Member && !isset($initial_values[$selection->getDimensionId()])) {
				$initial_values[$selection->getDimensionId()] = $selection->getId();
			}
		}
	}
	
	$genid = gen_id();
	$suffix = array_var($params, 'suffix', 1);
	
	$dim_associations = array_var($params, 'dim_associations');
	if (!is_array($dim_associations) || count($dim_associations) == 0) {
		$dim_associations = DimensionMemberAssociations::getAllAssociatationsForObjectType($member->getDimensionId(), $member->getObjectTypeId());
	}
	
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
			
			// use multiple selectors if the association is multiple or if editing the associated member (e.g.: proj. status)
			$is_multiple = $dim_association->getIsMultiple() || $reverse_relation;
			
			$select_fn = $is_multiple ? "og.onAssociatedMemberTypeSelectMultiple" : "og.onAssociatedMemberTypeSelect";
			$remove_fn = $is_multiple ? "og.onAssociatedMemberTypeRemoveMultiple" : "og.onAssociatedMemberTypeRemove";
			
			$custom_name = $dimension->getOptionValue('custom_dimension_name');
			if ($custom_name && trim($custom_name) != "") {
				$label = $custom_name;
			} else {
				$label = Localization::instance()->lang(str_replace('_',' ', $ot->getName()) . ($is_multiple ? 's' : ''));
				if (is_null($label)) {
					$label = $dimension->getName();
				}
			}
			
			if ($dim_association->getIsRequired()) {
				$label .= " *";
			}
			
			$hf_name = 'associated_members['.$dim_association->getId().']';
			
			render_single_member_selector($dimension, $comp_genid, $selected_ids, array(
					'is_multiple' => $is_multiple,
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






function member_selector_additional_selected_ids($object, $dimensions) {
	$additional_sel_ids = null;
	
	if ($object instanceof ContentDataObject && $object->isNew()) {
		// check if object has parent type 
		$has_parent = ObjectTypeHierarchies::hasParentObjectType($object->getObjectTypeId());
		if ($has_parent) {
			
			$parent_object = Objects::findObject($object->getParentObjectId());
			if ($parent_object instanceof ContentDataObject) {
				
				$additional_sel_ids = array();
				$parent_members = $parent_object->getMembers();
				
				// for each dimension, get the possible member types and check if this hierarchy allows the autoclassification in parent members
				foreach ($dimensions as $dim) {
					$dim_id = $dim['dimension_id'];
					$member_type_ids = DimensionObjectTypes::getObjectTypeIdsByDimension($dim_id);
					
					foreach ($member_type_ids as $mem_type_id) {
						$autoclassify_in_parent_members = ObjectTypeHierarchies::getHierarchyOptionValue($parent_object->getObjectTypeId(), $object->getObjectTypeId(), $dim_id, $mem_type_id, 'autoclassify_in_parent_members');
						
						$this_type_member_ids = array();
						foreach ($parent_members as $pmem) {
							if ($pmem->getDimensionId() == $dim_id && $pmem->getObjectTypeId() == $mem_type_id) {
								$this_type_member_ids[] = $pmem->getId();
							}
						}
						
						if ($autoclassify_in_parent_members) {
							$dotc = DimensionObjectTypeContents::findOne(array('conditions' => "`dimension_id`=$dim_id AND dimension_object_type_id=$mem_type_id AND `content_object_type_id`='".$object->getObjectTypeId()."'"));
							if ($dotc->getIsMultiple()) {
								$additional_sel_ids = array_merge($additional_sel_ids, $this_type_member_ids);
							} else {
								// if selection is not multiple then only autoselect if parent type has only one member of this type
								if (count($this_type_member_ids) == 1) {
									$additional_sel_ids[] = $this_type_member_ids[0];
								}
							}
						}
						
					}
				}
				
				
			}
		}
		
		Hook::fire("more_member_selector_additional_selected_ids", array('object' => $object), $additional_sel_ids);
	}
	
	return $additional_sel_ids;
}


/**
 * 1) Esta función debe devolver los ids de los members del padre para una dimensión
 * 2) Esos ids deben setearse en el componente para que cuando filtre los posibles members a seleccionar le pase estos ids al controlador
 * 3) El controlador debe agregar estos ids a las condiciones de la consulta para no permitir otros members que no sean estos.
 */
function member_selector_additional_ids_filter($object, $dimensions) {
	$additional_filters = array();

	if ($object instanceof ContentDataObject && $object->isNew()) {
		// check if object has parent type
		$has_parent = ObjectTypeHierarchies::hasParentObjectType($object->getObjectTypeId());
		if ($has_parent) {
				
			$parent_object = Objects::findObject($object->getParentObjectId());
			if ($parent_object instanceof ContentDataObject) {

				$additional_sel_ids = array();
				$parent_members = $parent_object->getMembers();

				// for each dimension, get the possible member types and check if this hierarchy allows the autoclassification in parent members
				foreach ($dimensions as $dim) {
					$dim_id = $dim['dimension_id'];
					$member_type_ids = DimensionObjectTypes::getObjectTypeIdsByDimension($dim_id);
						
					foreach ($member_type_ids as $mem_type_id) {
						$filter_by_parent_members = ObjectTypeHierarchies::getHierarchyOptionValue($parent_object->getObjectTypeId(), $object->getObjectTypeId(), $dim_id, $mem_type_id, 'filter_by_parent_members');

						if ($filter_by_parent_members) {
							$this_type_member_ids = array();
							foreach ($parent_members as $pmem) {
								if ($pmem->getDimensionId() == $dim_id && $pmem->getObjectTypeId() == $mem_type_id) {
									$this_type_member_ids[] = $pmem->getId();
								}
							}

							$additional_filters[$dim_id] = $this_type_member_ids;
						}

					}
				}


			}
		}
	}
	
	return $additional_filters;
}



function get_member_paths_for_object_list($object_ids) {
	$member_path_cache = array();
	
	if (count($object_ids) > 0) {
		$member_path_sql = "
				SELECT om.object_id, om.member_id, m.dimension_id, m.object_type_id
				FROM ".TABLE_PREFIX."object_members om
				INNER JOIN ".TABLE_PREFIX."members m ON m.id=om.member_id
				WHERE
					om.is_optimization=0 AND
					m.dimension_id != (select id FROM ".TABLE_PREFIX."dimensions where code='feng_persons') AND
					om.object_id IN (".implode(',', $object_ids).")
				ORDER BY om.object_id, m.dimension_id, m.object_type_id
			";
			
		$member_path_rows = DB::executeAll($member_path_sql);
		foreach ($member_path_rows as $row) {
			if (!isset($member_path_cache[$row['object_id']])) {
				$member_path_cache[$row['object_id']] = array();
			}
			if (!isset($member_path_cache[$row['object_id']][$row['dimension_id']])) {
				$member_path_cache[$row['object_id']][$row['dimension_id']] = array();
			}
			if (!isset($member_path_cache[$row['object_id']][$row['dimension_id']][$row['object_type_id']])) {
				$member_path_cache[$row['object_id']][$row['dimension_id']][$row['object_type_id']] = array();
			}
			$member_path_cache[$row['object_id']][$row['dimension_id']][$row['object_type_id']][] = $row['member_id'];
		}
			
	}
	
	return $member_path_cache;
}

function get_members_info_for_object_list($object_ids) {
	$member_names = array();
	
	if (count($object_ids) > 0) {
		$member_path_sql = "
				SELECT om.object_id, om.member_id, m.dimension_id, m.name, m.color
				FROM ".TABLE_PREFIX."object_members om
				INNER JOIN ".TABLE_PREFIX."members m ON m.id=om.member_id
				WHERE
					om.is_optimization=0 AND
					m.dimension_id != (select id FROM ".TABLE_PREFIX."dimensions where code='feng_persons') AND
					om.object_id IN (".implode(',', $object_ids).")
				ORDER BY om.object_id, m.dimension_id, m.name
			";
			
		$member_path_rows = DB::executeAll($member_path_sql);
		foreach ($member_path_rows as $row) {
			if (!isset($member_names[$row['object_id']])) {
				$member_names[$row['object_id']] = array();
			}
			if (!isset($member_names[$row['object_id']][$row['dimension_id']])) {
				$member_names[$row['object_id']][$row['dimension_id']] = array();
			}
			$member_names[$row['object_id']][$row['dimension_id']][$row['member_id']] = array('name' => $row['name'], 'color' => $row['color']);
		}
			
	}
	
	return $member_names;
}

/**
 * @param $dimension_id
 * @param $object_type_id
 * @param $associated_id
 * @return int
 */
function get_associated_dimensions($dimension_id,$object_type_id,$associated_id){

    $all_dimensions = DimensionMemberAssociations::getAssociatations($dimension_id,$object_type_id);
    foreach ($all_dimensions as $dim){
        if ($dim instanceof DimensionMemberAssociation){
            if($dim->getAssociatedObjectType() == $associated_id){
                return $dim->getId();
            };
        }
    }

}