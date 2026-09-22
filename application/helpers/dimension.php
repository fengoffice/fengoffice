<?php
require_javascript('og/modules/addMessageForm.js');

function append_related_members_to_autoclassify(&$object_member_ids) {
	
	$object_members = array();
	if (is_array($object_member_ids) && count($object_member_ids) > 0) {
		$object_members = Members::instance()->findAll(array("conditions" => "id IN (".implode(',', $object_member_ids).")"));
	}
	$assoc_member_ids = array();

	$skipped_association_codes = array();
	Hook::fire("skipped_associations_in_add_to_realted_members", array('members'=>$object_members), $skipped_association_codes);
	
	foreach ($object_members as $selection) {
		// get the related members that are defined to autoclassify in its association config
		$associations = DimensionMemberAssociations::getAllAssociatationsForObjectType($selection->getDimensionId(), $selection->getObjectTypeId());
		foreach ($associations as $a) {
			// skip the associations where we don't want to autoclassify the object
			if (in_array($a->getCode(), $skipped_association_codes)) {
				continue;
			}
			
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
		$assoc_members = Members::instance()->findAll(array("conditions" => "id IN (".implode(',', $assoc_member_ids).")"));
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
			Hook::fire("allowed_dimensions_in_member_selector", array('ot' => $content_object_type_id, 'options' => $options), $all_dimensions);
			
			foreach ($all_dimensions as $dimension){
				if ( isset($user_dimensions[$dimension['dimension_id']] ) ){
					$custom_name = DimensionOptions::getOptionValue($dimension['dimension_id'], 'custom_dimension_name');
					$dimension['dimension_name'] = $custom_name && trim($custom_name) != "" ? $custom_name : lang($dimension['dimension_code']);
 					
					if (isset($options['only_dimension_id'])) {
						if ($options['only_dimension_id'] == $dimension['dimension_id']) {
							$dimensions[] = $dimension;
						}
					} else {
						$dimensions[] = $dimension;
					}
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
							foreach ($associations as $a) {/* @var $a DimensionMemberAssociation */
								$autoclassify_in_related = (bool)DimensionAssociationsConfigs::getConfigValue($a->getId(), 'autoclassify_in_property_member');
								// use only default associations
								$tmp_ot = ObjectTypes::instance()->findById($a->getObjectTypeId());
								$tmp_assoc_ot = ObjectTypes::instance()->findById($a->getAssociatedObjectType());
								if (!$tmp_ot || !$tmp_assoc_ot) continue;
								if ($a->getCode() != $tmp_ot->getName()."_".$tmp_assoc_ot->getName()) {
									continue;
								}
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
				
				$selected_member_ids = array_unique(array_merge($selected_member_ids, $assoc_member_ids));
				
			}
			
			
			if (is_null($selected_member_ids)) $selected_member_ids = array();
			
			// additional selected member ids (e.g.: taken from ot hierarchy)
			$additional_selected_member_ids = member_selector_additional_selected_ids(array_var($options,'object'), $dimensions);
			if (is_array($additional_selected_member_ids)) {
				$selected_member_ids = array_unique(array_filter(array_merge($selected_member_ids, $additional_selected_member_ids)));
			}
			
			// clean selected member ids, keep only the ones that belong to the dimensions we are rendering
			$tmp_selected_member_ids = array();
			$dimension_ids = array_map(function($d) { return $d['dimension_id']; }, $dimensions);

			foreach ($selected_member_ids as $selected_member_id) {
				$sel_mem = Members::getMemberById($selected_member_id);
				if ($sel_mem instanceof Member && in_array($sel_mem->getDimensionId(), $dimension_ids)) {
					$tmp_selected_member_ids[] = $selected_member_id;
				}
			}
			$selected_member_ids = $tmp_selected_member_ids;
			
			// additional filters, by member id
			$additional_filters = member_selector_additional_ids_filter(array_var($options,'object'), $dimensions);
			if (is_array($additional_filters) && count($additional_filters) > 0) {
				$options['filter_by_ids'] = $additional_filters;
			}

			$selected_members = count($selected_member_ids) > 0 ? Members::instance()->findAll(array('conditions' => 'id IN ('.implode(',', $selected_member_ids).') ')) : array();

			foreach($dimensions as $dimension){
				$dimension_id = $dimension['dimension_id'];
				$dim_sel_mems = array();
				foreach ($selected_members as $selected_member) {
					if ($selected_member->getDimensionId() == $dimension_id) $dim_sel_mems[] = $selected_member;
				}
				if (count($dim_sel_mems) == 0 && array_var($options, 'select_current_context') && !array_var($options, 'skip_default_member_selections', false)) {
					$default_value = DimensionOptions::instance()->getOptionValue($dimension_id, 'default_value');
					if ($default_value) {
						$default_member = Members::getMemberById($default_value);
						if ($default_member instanceof Member) $selected_member_ids[] = $default_member->getId();
					}
				}
			}
			
			$skipped_dimensions_cond = "";
			if (is_array($skipped_dimensions) && count($skipped_dimensions) > 0) {
				$skipped_dimensions_cond = " AND dimension_id NOT IN (".implode(',', $skipped_dimensions).")";
			}
			
			// Set view variables
			$manageable_conds = ' AND dimension_id IN (SELECT id from '.TABLE_PREFIX.'dimensions WHERE is_manageable=1)' . $skipped_dimensions_cond;
			$selected_members = count($selected_member_ids) > 0 ? Members::instance()->findAll(array('conditions' => 'id IN ('.implode(',', $selected_member_ids).') '.$manageable_conds)) : array();
			$selected_member_ids = array();
			foreach ($selected_members as $sm) $selected_member_ids[] = $sm->getId();
			$selected_members_json = "[".implode(',', $selected_member_ids)."]";
			$component_id = "$genid-member-selectors-panel-$content_object_type_id";
			$object_is_new = is_null($selected_members);
			
			$listeners = array_var($options, 'listeners', array());
			$allowed_member_type_ids = array_var($options, 'allowedMemberTypes', null);
			
			if (isset($options['label'])) $label = $options['label'];
			$html_help_icon = array_var($options, 'html_help_icon', '');

			$initial_selected_members = $selected_members;
			if (count($initial_selected_members) == 0) {
				$selected_context_member_ids = active_context_members(false);
				if (count($selected_context_member_ids) > 0) {
					$initial_selected_members = Members::instance()->findAll(array('conditions' => 'id IN ('.implode(',', $selected_context_member_ids).')'));
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


/**
 * Member IDs for report parameter forms.
 * Prefer the left-sidebar context when that dimension is selected; otherwise restore
 * the last selection stored in $report_data (session) so filters survive closing the report.
 *
 * @param Dimension $dimension
 * @param array|null $report_data
 * @param string $field_name Session/POST field that holds a JSON array of member ids
 * @return array
 */
function get_report_form_selected_member_ids(Dimension $dimension, $report_data, $field_name) {
	$use_active_context = false;
	foreach (active_context() as $selection) {
		if ($selection instanceof Member && $selection->getDimensionId() == $dimension->getId()) {
			$use_active_context = true;
			break;
		}
	}
	if ($use_active_context) {
		return active_context_members(false);
	}
	$selected_ids = json_decode(array_var($report_data, $field_name, '[]'));
	if (!is_array($selected_ids)) {
		$selected_ids = array();
	}
	return $selected_ids;
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
	$selected_members = count($selected_member_ids) > 0 ? Members::instance()->findAll(array('conditions' => 'id IN ('.implode(',', $selected_member_ids).')')) : array();
	$selected_members_json = "[".implode(',', $selected_member_ids)."]";
	$component_id = "$genid-member-selectors-panel-$content_object_type_id";
	
	$listeners = array_var($options, 'listeners', array());
	$allowed_member_type_ids = array_var($options, 'allowedMemberTypes', null);
	
	$hide_label = array_var($options, 'hide_label', false);
	$description = array_var($options, 'description', '');
	
	if (isset($options['label'])) $label = $options['label'];
	$html_help_icon = array_var($options, 'html_help_icon', '');

	// option to disable tree reloading when selectin in a related dimension (e.g. in parent selector)
	$dont_filter_this_selector = array_var($options, 'dont_filter_this_selector', false);
	
	// option to show default selection checkboxes
	$default_selection_checkboxes = array_var($options, 'default_selection_checkboxes', false);
	$related_member_id = array_var($options, 'related_member_id', 0);
	$member_association_id = array_var($options, 'member_association_id', 0);
	
	// for single member selectors, don't restrict by is_manageable
	$options['allow_non_manageable'] = true;
	
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
		$old_parent_member = Members::instance()->findById($old_parent_id);
		$old_member_depth = $old_parent_member->getDepth() + 1;
	}else{
		$old_member_depth = 1;
	}

	$depth_diff = $m_depth - $old_member_depth;

	$childs_ids_string = implode(',', $childs_ids);
	$update_depth_sql = "UPDATE ".TABLE_PREFIX."members SET `depth` = `depth` + $depth_diff WHERE id IN($childs_ids_string);";
	DB::execute($update_depth_sql);
}

function render_plain_member_selector($config) {

    $dim_id = $config['dim_id'];
    $cache_key = $dim_id . '_dimension_members_tree';
	$expiration = 60; // cache data for 1 minute

	// Check if the members data is already in the cache
    if (!isset($_SESSION[$cache_key] ) || !isset($_SESSION[$cache_key . '_expiration']) || $_SESSION[$cache_key . '_expiration'] < time()) {
       	$_REQUEST['dimension_id'] = $dim_id;
		$_REQUEST['vars'] = $config;	
		$_REQUEST['return_array'] = true;
		$dim_controller = new DimensionController();
		$data = $dim_controller->initial_list_dimension_members_tree();
		if(!empty($data)) {
			$_SESSION[$cache_key] = $data;
			$_SESSION[$cache_key . '_expiration'] = time() + $expiration;
		}
    } 

	$plain_selector = render_plain_member_selector_using_cache($config);

	return $plain_selector;
}

function render_plain_member_selector_using_cache($config) {
	$dim_id = array_var($config, 'dim_id');
	$genid = array_var($config, 'genid');
	$hf_name = array_var($config, 'hf_name');
	$selector_id = array_var($config, 'selector_id');
	$container = array_var($config, 'container');
	$selected_id = array_var($config, 'selected_id');
	$selector_class = array_var($config, 'selector_class', '');
	$onchange = array_var($config, 'onchange', '');
	$value_zero_text = array_var($config, 'value_zero_text', '');

	$cache_key = $dim_id . '_dimension_members_tree';
	$member_tree_data = $_SESSION[$cache_key] ?? [];
	$identation = 0;

	// build HTML selector with members as options
	$selector = '<select id=\'' . $selector_id . '\' onchange=\''.$onchange.'\' name=\'' . $hf_name . '\' class=\''.$selector_class.'\'>';
	$selector .= '<option value=\'0\'>' . $value_zero_text . '</option>';
	$selector .= render_plain_member_selector_options($member_tree_data, $selected_id, $identation);
	$selector .= '</select>';

	return $selector;
}

function render_plain_member_selector_options($member_tree_data, $selected_id, $identation = 0) {
	$options = '';
	foreach ($member_tree_data as $member) {
		$selected = $member['id'] == $selected_id ? 'selected=\'selected\'' : '';
		$ident = '';
		for ($i = 0; $i < $identation; $i++) {
			$ident .= '&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		$options .= '<option value=\'' . clean($member['id']) . '\' ' . $selected . '>' . clean($member['name']) . '</option>';
		if (isset($member['children'])) {
			$options .= render_plain_member_selector_options($member['children'], $selected_id, $identation + 1);
		}
	}
	return $options;
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
		
		$a = DimensionMemberAssociations::instance()->findById($assoc_id);
		if (!$a instanceof DimensionMemberAssociation) {
			// The request carried a key that is not an association id. Skip it instead of failing the whole
			// save: any association that is actually required is still reported by the check below.
			continue;
		}

		if ($member->getDimensionId() == $a->getDimensionId()) {
			$reverse_relation = false;
			$rel_dimension = Dimensions::getDimensionById($a->getAssociatedDimensionMemberAssociationId());
			$rel_ot = ObjectTypes::instance()->findById($a->getAssociatedObjectType());
		} else {
			$reverse_relation = true;
			$rel_dimension = Dimensions::getDimensionById($a->getDimensionId());
			$rel_ot = ObjectTypes::instance()->findById($a->getObjectTypeId());
		}
		
		// use multiple selectors if the association is multiple or if editing the associated member (e.g.: proj. status)
		$is_multiple = $a->getIsMultiple() || $reverse_relation;
		
		if ($is_multiple) {
			$memcol = $reverse_relation ? "property_member_id" : "member_id";
			// if association is multiple delete all relations and add the new ones
			MemberPropertyMembers::instance()->delete('association_id = '.$assoc_id.' AND '.$memcol.' = '.$member->getId());
			
			foreach ($assoc_mem_ids as $rel_mem_id) {
				associate_member_to_status_member($member, 0, $rel_mem_id, $rel_dimension, $rel_ot, false, $a->getCode());
			}
		} else {
			// asociate objects to the new related member, remove from the old one
			$old_related_mem_id = get_associated_status_member_id($member, $rel_dimension, $rel_ot, $reverse_relation, $a->getCode());
			$new_related_mem_id = array_var($assoc_mem_ids, 0);
			
			// make the association only if it has changed
			if ($new_related_mem_id != $old_related_mem_id) {
				associate_member_to_status_member($member, $old_related_mem_id, $new_related_mem_id, $rel_dimension, $rel_ot, true, $a->getCode());
			}
		}
		
		if ($a->getAllowsDefaultSelection()) {
			$member_info = array_var($request, 'member');
			$default_selection = array_var($member_info, 'default_selection');
			
			save_default_associated_member_selections($a->getId(), $member->getId(), $default_selection);
		}
		
		if (is_array($assoc_mem_ids) && count($assoc_mem_ids) > 0) {
			$required_associations_present[$a->getId()] = true;
		}
		
		$null = null;
		Hook::fire('after_associating_members', array('member' => $member, 'association' => $a, 'is_new' => $is_new,
				'rel_dim' => $rel_dimension, 'rel_ot' => $rel_ot, 'assoc_member_ids' => $assoc_mem_ids), $null);
		
	}
	
	$check_required_associations_disabled = array_var($_SESSION, 'dont_check_required_associations');
	if (!$check_required_associations_disabled) {
		// check if all required associated dimensions have a value
		foreach ($required_associations_present as $aid => $present) {
			if (!$present) {
				$assoc = $required_associations_object[$aid];
				if ($assoc->getIsMultiple()) {
					$assoc_dim = Dimensions::instance()->findById($assoc->getColumnValue('associated_dimension_id'));
					$property_name = $assoc_dim instanceof Dimension ? $assoc_dim->getName() : 'dimension';
				} else {
					$assoc_ot = ObjectTypes::instance()->findById($assoc->getColumnValue('associated_object_type_id'));
					$property_name = $assoc_ot instanceof ObjectType ? $assoc_ot->getObjectTypeName() : 'property';
				}
				
				$reverse_relation = $assoc->getDimensionId() != $member->getDimensionId();

				// if association has custom defined name then apply it in the message
				if (!$reverse_relation) {
					$custom_assoc_name = DimensionAssociationsConfigs::getConfigValue($assoc->getId(), 'custom_association_name');
					if ($custom_assoc_name) {
						$property_name = $custom_assoc_name;
					}
				}
				throw new Exception(lang('custom property value required', $property_name));
			}
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
		$member_template = MemberTemplates::instance()->findById(get_id('template_id'));
		if ($member_template instanceof MemberTemplate) {
	
			$ini_assocs = MemberTemplatesInitialAssociations::instance()->findAll(array('conditions' => 'template_id='.$member_template->getId()));
			foreach ($ini_assocs as $ini_assoc) {
				$a = DimensionMemberAssociations::instance()->findById($ini_assoc->getDimAssociationId());
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
			$ot = ObjectTypes::instance()->findById($dim_association->getAssociatedObjectType());
		} else {
			$reverse_relation = true;
			$dimension = Dimensions::getDimensionById($dim_association->getDimensionId());
			$ot = ObjectTypes::instance()->findById($dim_association->getObjectTypeId());
		}

		$default_value = DimensionOptions::instance()->getOptionValue($dimension->getId(), 'default_value');
		if (is_numeric($default_value) && $default_value > 0 && !isset($initial_values[$dimension->getId()])) {
			$initial_values[$dimension->getId()] = $default_value;
		}
		
		$comp_genid = $genid . "_$suffix";
		if (in_array($dimension->getId(), $enabled_dimensions)) {
			echo '<div class="field '.$ot->getName().'">';
			
			$selected_ids = array();

			$hook_params = array(
				'member' => $member,
				'dimension' => $dimension,
				'ot' => $ot,
				'reverse_relation' => $reverse_relation,
				'dim_association_code' => $dim_association->getCode(),
				'parent_member_id' => array_var($_REQUEST, 'parent')
			);

			Hook::fire('get_parent_associated_member_ids', $hook_params, $selected_ids);

			if (empty($selected_ids)){
				if ($is_new) {
					$selected_ids = array(array_var($initial_values, $dimension->getId()));
				} else {
					$selected_ids = get_all_associated_status_member_ids($member, $dimension, $ot, $reverse_relation, $dim_association->getCode());
				}
			}
			
			// use multiple selectors if the association is multiple or if editing the associated member (e.g.: proj. status)
			$is_multiple = $dim_association->getIsMultiple() || $reverse_relation;
			
			$select_fn = $is_multiple ? "og.onAssociatedMemberTypeSelectMultiple" : "og.onAssociatedMemberTypeSelect";
			$remove_fn = $is_multiple ? "og.onAssociatedMemberTypeRemoveMultiple" : "og.onAssociatedMemberTypeRemove";
			
			$custom_assoc_name = null;
			if (!$reverse_relation) {
				$custom_assoc_name = DimensionAssociationsConfigs::getConfigValue($dim_association->getId(), 'custom_association_name');
			}
			if ($custom_assoc_name) {
				$label = $custom_assoc_name;
			} else {
				$custom_name = $dimension->getOptionValue('custom_dimension_name');
				if ($custom_name && trim($custom_name) != "") {
					$label = $custom_name;
				} else {
					$label = Localization::instance()->lang(str_replace('_',' ', $ot->getName()) . ($is_multiple ? 's' : ''));
					if (is_null($label)) {
						$label = $dimension->getName();
					}
				}
			}
			if (isset($params['label']) && isset($params['label'][$dim_association->getId()]) && trim($params['label'][$dim_association->getId()]) != '') {
				$label = $params['label'][$dim_association->getId()];
			}
			
			if ($dim_association->getIsRequired() && !$reverse_relation) {
				$label .= ' <span class="label_required">*</span>';

			}

			$html_help_icon = '';
			if (isset($params['html_help_icon']) && isset($params['html_help_icon'][$dim_association->getId()])) {
				$html_help_icon = $params['html_help_icon'][$dim_association->getId()];
			}

			// get the defined description text for the dimension member association
			$association_desription = '';
			if (!$reverse_relation) {
				$association_desription = DimensionAssociationsConfigs::getConfigValue($dim_association->getId(), 'custom_association_description');
			}
			
			$hf_name = 'associated_members['.$dim_association->getId().']';
			
			$listeners = array('on_remove_relation' => "$remove_fn('$comp_genid', ".$dimension->getId().", '$hf_name');");
			
			Hook::fire("before_render_associated_dimension_selector", array('genid'=>$comp_genid, 'member'=>$member, 'selected_ids'=>$selected_ids, 'dim_association'=>$dim_association), $listeners);
			
			$selector_options = array(
				'is_multiple' => $is_multiple,
				//'allowedMemberTypes' => array($ot->getId()),
				'content_object_type_id' => $ot->getId(),
				'label' => $label,
				'html_help_icon' => $html_help_icon,
				'description' => $association_desription,
				'allow_non_manageable' => true, 
				'hidden_field_name' => $hf_name,
				'select_function' => $select_fn, 
				'listeners' => $listeners,
				// hardcode to false the default_selection_checkboxes value because we don't want those checkboxes there
				'default_selection_checkboxes' => false,// $dim_association->getAllowsDefaultSelection(),
				'width' => 400,
				'related_member_id' => $member->getId(),
				'member_association_id' => $dim_association->getId(),
				'readonly' => array_var($params, 'readonly'),
				'dont_reload_other_trees' => array_var($params, 'dont_reload_other_trees'),
			);
			
			Hook::fire("associated_dimension_selector_edit_options", array('genid'=>$comp_genid, 'member'=>$member, 'selected_ids'=>$selected_ids, 'dim_association'=>$dim_association, 'reverse_relation'=>$reverse_relation), $selector_options);
			
			render_single_member_selector($dimension, $comp_genid, $selected_ids, $selector_options, false);
			
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
							$dotc = DimensionObjectTypeContents::instance()->findOne(array('conditions' => "`dimension_id`=$dim_id AND dimension_object_type_id=$mem_type_id AND `content_object_type_id`='".$object->getObjectTypeId()."'"));
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





function get_object_members_by_type($object, $object_type_id) {
	if (!$object instanceof ContentDataObject) return array();

	$result = array();
	
	$members = $object->getMembers();
	foreach ($members as $m) {
		if ($m->getObjectTypeId() == $object_type_id) {
			$result[] = $m;
		}
	}
	
	return $result;
}

function check_project_client_compatibility(&$member_ids) {
	$result = array('result' => true, 'error_message' => '');

	// Check if the CRPM plugin is active
	if (!Plugins::instance()->isActivePlugin('crpm')) return $result;

	Env::useHelper('functions', 'crpm');
	$clients_dim = get_customers_dimension();
	$project_dim = Dimensions::findByCode('customer_project');
	if (!$clients_dim || !$project_dim || $clients_dim->getId() == $project_dim->getId()) {
		return $result;
	}

	// Check if both client and project dimensions are enabled
	$client_dim_id = $clients_dim->getId();
	$project_dim_id = $project_dim->getId();
	$enabled_dimensions = config_option('enabled_dimensions');
	if(!in_array($client_dim_id, $enabled_dimensions) || !in_array($project_dim_id, $enabled_dimensions)) return $result;

	
	// Get client and project member
	$client_ot_id = ObjectTypes::findByName('customer')->getId();
	$project_ot_id = ObjectTypes::findByName('project')->getId();
	$project_member = null;
	$client_member = null;
	foreach($member_ids as $m_id){
		$member = Members::instance()->findById($m_id);
		if($member->getObjectTypeId() == $client_ot_id) $client_member = $member;
		if($member->getObjectTypeId() == $project_ot_id) $project_member = $member;
	}

	// Return if project is not assigned
	if(!$project_member instanceof Member) return $result;

	// Get project to client associations ids to make the query
	$project_client_associations = DimensionMemberAssociations::instance()->getAllAssociations($project_dim_id, $client_dim_id);
	$project_client_association_ids = array();
	foreach ($project_client_associations as $project_client_assoc) {
		$project_client_association_ids[] = $project_client_assoc->getId();
	}
	// if there are no associations then return true
	if (count($project_client_association_ids) == 0) return $result;

	// find all clients related to the project with any dimension association
	$mpms = MemberPropertyMembers::instance()->findAll(array('conditions' => '`association_id` IN (' . implode(',',$project_client_association_ids). ') AND `member_id` = ' . $project_member->getId().' AND `is_active` = 1'));

    // Return true if project doesn't have any client associations
	if(count($mpms) == 0) return $result;

	// build an array with all the associated client member ids
	$eligible_client_member_ids = array();
	foreach($mpms as $mpm){
		$eligible_client_member_ids[] = $mpm->getPropertyMemberId();
	}

	// if we didn't receive a client and the project has a client then assign it to the timeslot so we can continue with the process
	if(!$client_member instanceof Member){
		$member_ids[] = $eligible_client_member_ids[0];
		return $result;
	}

	// Check if client member is in the list of eligible client members
	if(in_array($client_member->getId(), $eligible_client_member_ids)){
		return $result;
	} else {
		$result['result'] = false;
		$result['error_message'] = lang('project and client are not associated with each other');
		return $result;
	}
}

/**
 * When property groups drive the web form, only classification dimensions
 * configured in those groups should appear in member selectors (mobile included).
 * Matches web: if the OT uses property groups, dimensions not listed as
 * classification_* there are omitted (including when none are configured).
 *
 * @param int $content_object_type_id
 * @param array $dimensions rows with at least an `id` key
 * @return array
 */
function filter_dimensions_by_property_groups_for_forms($content_object_type_id, $dimensions) {
	if (!is_array($dimensions) || count($dimensions) === 0) {
		return $dimensions;
	}
	if (!Plugins::instance()->isActivePlugin('advanced_core')) {
		return $dimensions;
	}

	$object_type = ObjectTypes::instance()->findById($content_object_type_id);
	if (!($object_type instanceof ObjectType)) {
		return $dimensions;
	}

	$handler_class = $object_type->getHandlerClass();
	if (!$handler_class || !class_exists($handler_class)) {
		return $dimensions;
	}

	$manager = new $handler_class();
	if (!($manager instanceof ContentDataObjects) || !$manager->canUsePropertyGroups()) {
		return $dimensions;
	}

	// Include classifications from both main and separate property-group tabs.
	$grouped_properties = PropertyGroups::getAllPropertiesGroupedByPropertyGroup($content_object_type_id, 0, 'all');
	$allowed_dimension_ids = array();

	foreach ($grouped_properties as $group) {
		$pg_perm = null;
		Hook::fire('check_property_group_permissions', array(
			'user' => logged_user(),
			'property_group' => $group,
		), $pg_perm);
		if ($pg_perm === 'none') {
			continue;
		}

		foreach (array_var($group, 'properties', array()) as $property) {
			if (!empty($property['is_disabled'])) {
				continue;
			}
			$property_id = array_var($property, 'property_id', '');
			if (is_string($property_id) && str_starts_with($property_id, 'classification_')) {
				$allowed_dimension_ids[] = (int) str_replace('classification_', '', $property_id);
			}
		}
	}

	$allowed_dimension_ids = array_filter(array_unique($allowed_dimension_ids));

	// Fail-closed: same as web — only show classifications from property groups.
	$filtered = array();
	foreach ($dimensions as $dimension) {
		$dimension_id = is_array($dimension) ? (int) array_var($dimension, 'id', 0) : 0;
		if ($dimension_id > 0 && in_array($dimension_id, $allowed_dimension_ids)) {
			$filtered[] = $dimension;
		}
	}

	return $filtered;
}


/**
 * What to do with the subtasks of a task when its classification in a dimension changes,
 * as configured in the 'apply_classification_to_subtasks' config option.
 *
 * The option stores a JSON object keyed by dimension id, so a dimension with nothing stored
 * (a new dimension, or an installation that never saved the option) falls back to the default.
 *
 * @return array Mode by dimension id
 */
function subtask_classification_modes() {
	static $modes = null;
	if (is_array($modes)) return $modes;

	$modes = array();
	// config_option() returns null on installs where the option row is missing, and the array
	// the handler decodes when the option is there; the raw JSON is handled too so that the
	// helper does not depend on which handler class the option row happens to have
	$stored = config_option('apply_classification_to_subtasks');
	$decoded = is_array($stored) ? $stored : null;
	if (is_null($decoded) && is_string($stored) && trim($stored) != '') {
		$decoded = json_decode($stored, true);
	}
	if (is_array($decoded)) {
		$valid = array(
			SUBTASK_CLASSIFICATION_NEVER,
			SUBTASK_CLASSIFICATION_IF_EMPTY,
			SUBTASK_CLASSIFICATION_ALWAYS,
		);
		foreach ($decoded as $dimension_id => $mode) {
			if (in_array($mode, $valid)) $modes[(int) $dimension_id] = $mode;
		}
	}

	return $modes;
}


/**
 * Mode configured for one dimension, or the default when it has none.
 *
 * @param integer $dimension_id
 * @return string One of the SUBTASK_CLASSIFICATION_* constants
 */
function subtask_classification_mode($dimension_id) {
	$modes = subtask_classification_modes();
	return array_var($modes, (int) $dimension_id, SUBTASK_CLASSIFICATION_DEFAULT);
}


/**
 * Dimension ids whose classification must never be applied to the subtasks.
 *
 * @param array $dimension_ids Dimensions to check, all the configured ones when omitted
 * @return array
 */
function dimensions_not_applied_to_subtasks($dimension_ids = null) {
	if (!is_array($dimension_ids)) $dimension_ids = array_keys(subtask_classification_modes());

	$ignored = array();
	foreach ($dimension_ids as $dimension_id) {
		if (subtask_classification_mode($dimension_id) == SUBTASK_CLASSIFICATION_NEVER) {
			$ignored[] = (int) $dimension_id;
		}
	}
	return $ignored;
}


/**
 * Dimension ids whose set of members is not the same in two classifications of an object.
 *
 * Used to apply to the subtasks only the dimensions a save actually changed, instead of the
 * whole classification of the task.
 *
 * @param array $previous_member_ids
 * @param array $new_member_ids
 * @return array Dimension ids
 */
function changed_classification_dimension_ids($previous_member_ids, $new_member_ids) {
	$previous_member_ids = is_array($previous_member_ids) ? $previous_member_ids : array();
	$new_member_ids = is_array($new_member_ids) ? $new_member_ids : array();

	$all_member_ids = array();
	foreach (array_merge($previous_member_ids, $new_member_ids) as $member_id) {
		if ((int) $member_id > 0) $all_member_ids[] = (int) $member_id;
	}
	$all_member_ids = array_unique($all_member_ids);
	if (count($all_member_ids) == 0) return array();

	// all the ids are cast to integer above, so they can be listed in the query
	$rows = DB::executeAll("SELECT id, dimension_id FROM " . TABLE_PREFIX . "members
		WHERE id IN (" . implode(',', $all_member_ids) . ")");

	$dimension_by_member = array();
	if (is_array($rows)) {
		foreach ($rows as $row) $dimension_by_member[(int) $row['id']] = (int) $row['dimension_id'];
	}

	$group_by_dimension = function($member_ids) use ($dimension_by_member) {
		$grouped = array();
		foreach ($member_ids as $member_id) {
			$member_id = (int) $member_id;
			if (!isset($dimension_by_member[$member_id])) continue;
			$grouped[$dimension_by_member[$member_id]][] = $member_id;
		}
		foreach ($grouped as &$ids) { $ids = array_unique($ids); sort($ids); }
		return $grouped;
	};

	$previous_by_dimension = $group_by_dimension($previous_member_ids);
	$new_by_dimension = $group_by_dimension($new_member_ids);

	$changed_dimension_ids = array();
	foreach (array_unique(array_merge(array_keys($previous_by_dimension), array_keys($new_by_dimension))) as $dimension_id) {
		$previous = array_var($previous_by_dimension, $dimension_id, array());
		$new = array_var($new_by_dimension, $dimension_id, array());
		if ($previous != $new) $changed_dimension_ids[] = (int) $dimension_id;
	}

	return $changed_dimension_ids;
}
