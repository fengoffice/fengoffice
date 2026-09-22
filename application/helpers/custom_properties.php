<?php

/**
 * Boolean CP stores "disallow not specified" in `values` (normally empty for booleans).
 */
if (!defined('CP_BOOLEAN_DISALLOW_NOT_SPECIFIED_MARKER')) {
	define('CP_BOOLEAN_DISALLOW_NOT_SPECIFIED_MARKER', '__cp_bool_no_ns__');
}

/**
 * @param CustomProperty|MemberCustomProperty|array $cp Object or getArrayInfo() row
 */
function cp_boolean_allows_not_specified($cp) {
	if (is_array($cp)) {
		if (array_var($cp, 'type') !== 'boolean') {
			return true;
		}
		return trim((string) array_var($cp, 'values')) !== CP_BOOLEAN_DISALLOW_NOT_SPECIFIED_MARKER;
	}
	if (!is_object($cp) || !method_exists($cp, 'getType')) {
		return true;
	}
	if ($cp->getType() !== 'boolean') {
		return true;
	}
	return trim($cp->getValues()) !== CP_BOOLEAN_DISALLOW_NOT_SPECIFIED_MARKER;
}

/**
 * Normalizes stored CP boolean to '1' (yes), '-1' (no), or '0' (not specified).
 * Same tríada que el select histórico; no forzar cast (int) directo sobre strings raros.
 */
function normalize_cp_boolean_stored_value($raw) {
	if ($raw === null || $raw === '' || $raw === false) {
		return '0';
	}
	if ($raw === true) {
		return '1';
	}
	$s = trim((string) $raw);
	if ($s === '' || strcasecmp($s, 'null') === 0) {
		return '0';
	}
	if ($s === '1' || $s === '1.0' || $s === '+1') {
		return '1';
	}
	if ($s === '-1' || $s === '-1.0') {
		return '-1';
	}
	if ($s === '0' || $s === '0.0') {
		return '0';
	}
	if (is_numeric($s)) {
		$n = (int) round((float) $s);
		if ($n === 1) {
			return '1';
		}
		if ($n === -1) {
			return '-1';
		}
	}
	return '0';
}

/**
 * Formats a boolean CP value for lists and views (not forms).
 * "Not specified" is shown as empty; use render_boolean_custom_property_field() on forms.
 */
function format_boolean_cp_value_for_display($raw) {
	$normalized = normalize_cp_boolean_stored_value($raw);
	if ($normalized === '1') {
		return lang('yes');
	}
	if ($normalized === '-1') {
		return lang('no');
	}
	return '';
}

function cp_info_sort_by_order($a, $b) {
	return $a['property_order'] > $b['property_order'];
}


function render_object_fixed_property_input($genid, $input_name, $col_info, $value, $object=null, $property_perm=null) {
	$html = '<div class="input-container" id="'.$genid.'" style="margin-bottom: 1rem;" id="input-'.array_var($col_info, 'col').'">';

	$html .= "<label>".array_var($col_info, 'label')."</label>";
	
	$disabled = $property_perm == 'view';
	$attr = array();
	if ($disabled) $attr['disabled']='disabled';

	$overriden_html = null;
	Hook::fire('override_object_form_fixed_property_input', array('genid' => $genid, 'input_name' => $input_name, 'col_info' => $col_info, 'value' => $value, 'object' => $object, 'property_perm' => $property_perm), $overriden_html);
	
	if (!is_null($overriden_html)) {
		
		// a renderer that outputs nothing means the property must not be shown (e.g. its feature is
		// disabled), so don't leave an orphan label behind
		if (trim($overriden_html) === '') {
			return '';
		}

		$html .= $overriden_html;

	} else {
		
		switch ($col_info['type']) {
			case DATA_TYPE_TIMEZONE:
				$html .= timezone_selector($input_name, $value);
				break;
			case DATA_TYPE_DATETIME:
				$col_id = array_var($col_info, 'col');
				$time_value = $value;
				if ($object instanceof ProjectEvent) {
					if ($object->getTypeId() == 2) $time_value = "";
				}
				$html .= '<div class="field"><table><tr><td>';
				$html .= pick_date_widget2(str_replace($col_id, $col_id."_date", $input_name), $value, $genid, null, null, null, null, $disabled);
				$html .= '</td><td>';
				$html .= pick_time_widget2(str_replace($col_id, $col_id."_time", $input_name), $time_value, $genid, null, null, null, null, $disabled);
				$html .= '</td></tr></table></div>';
				break;
			case DATA_TYPE_DATE:
				$html .= pick_date_widget2($input_name, $value, $genid, null, null, null, null, $disabled);
				break;
			case DATA_TYPE_INTEGER:
			case DATA_TYPE_FLOAT:
				$html .= number_field($input_name, $value, $attr);
				break;
			case DATA_TYPE_BOOLEAN:
				$html .= yes_no_widget($input_name, $input_name, $value, lang('yes'), lang('no'), null, $attr);
				break;
			case DATA_TYPE_STRING:
				if (array_var($col_info, 'large')) {
					$attr['rows'] = '7';
					$attr['cols'] = '60';
					$html .= textarea_field($input_name, $value, $attr);
				} else {
					$html .= text_field($input_name, $value, $attr);
				}
				break;
			case 'email':
				$html .= '<div class="field" style="float:left;">';
				
				$html .= email_field($input_name, $value, $genid, array(
					'container_id' => $genid.'_email_'.$col_info['col'],
					'input_base_id' => str_replace(array("[","]"), array("-",""), str_replace('[email]', '', $input_name)),
					'multiple' => true,
					'disabled' => $disabled,
				));
				$html .= '</div>';
				break;
			case 'address':
				$html .= '<div class="field" style="float:left;">';
				
				$html .= address_field($input_name, $value, $genid, array(
					'container_id' => $genid.'_address_'.$col_info['col'],
					'input_base_id' => str_replace(array("[","]"), array("-",""), str_replace('[address]', '', $input_name)),
					'multiple' => true,
					'disabled' => $disabled,
				));
				$html .= '</div>';
				break;
			case 'phone':
				$html .= '<div class="field" style="float:left;">';
				
				$html .= phone_field($input_name, $value, $genid, array(
					'container_id' => $genid.'_phone_'.$col_info['col'],
					'input_base_id' => str_replace(array("[","]"), array("-",""), str_replace('[phone]', '', $input_name)),
					'multiple' => true,
					'disabled' => $disabled,
				));
				$html .= '</div>';
				break;
			case 'webpage':
				$html .= '<div class="field" style="float:left;">';
				
				$html .= webpage_field($input_name, $value, $genid, array(
					'container_id' => $genid.'_webpage_'.$col_info['col'],
					'input_base_id' => str_replace(array("[","]"), array("-",""), str_replace('[webpage]', '', $input_name)),
					'multiple' => true,
					'disabled' => $disabled,
				));
				$html .= '</div>';
				break;
			case 'company':
				return ''; // deprecated fixed property type
				/*
				$html .= select_box($input_name, array(), array('id' => $genid.'profileFormCompany', "class" => "og-edit-contact-select-company"));
				$html .= "<script>og.load_company_combo('".$genid."profileFormCompany', '$value');</script>";
				break;*/
			case 'image':
				if (!$object instanceof Contact) {
					$object = new Contact();
				}
				$purl = $object->getPictureUrl();
				$update_purl = $object->getUpdatePictureUrl();
				$is_new = $object->isNew();
			
				$html .= '<div style="text-decoration:underline; display:inline-block;">';
				$html .= '<div class="cardIcon" style="display: inline-block;"><img id="'.$genid.'_logo_img" src="'.$purl.'"/></div>';
				if (!$disabled) {
					$html .= '<a href="#" onclick="og.updatePictureFile(\''.$update_purl.'&reload_picture='.$genid.'_logo_img'.($is_new ? '&new_contact='.$genid.'_logo_file' : ''). '\');"';
					$html .= 'class="coViewAction ico-picture">'. lang('update logo') .'</a>';
				}
				$html .= '<input type="hidden" id="'. $genid .'_logo_file" name="'.$input_name.'" value=""/></div>';
				break;
			default:
				break;
		}
	}

	$html .= '<div class="clear"></div>';
	if (array_var($col_info, 'description', '') != '') {
		$html .= '<div class="desc">'.clean($col_info['description']).'</div>';
	}
	$html .= '</div>';

	return $html;
}


function render_object_fixed_property_for_view($col_info, $value, ContentDataObject $object) {
	
	if (!$value) return "";

	$html = '<tr class="information-widget-table-row"><td class="information-widget-table-definition">'.array_var($col_info, 'label').': </td><td class="information-widget-table-value">';

	switch ($col_info['type']) {
		case DATA_TYPE_TIMEZONE:
			$zone = Timezones::getTimezoneById($value);
			if ($zone) {
				$tz_country = Countries::getCountryNameByCode($zone['country_code']);
				$html .= $tz_country . " - " . Timezones::getFormattedDescription($zone);
			}
			break;
		case DATA_TYPE_DATETIME:
			$tz_val = $object->getTimezoneValue() / 3600;
			if ($object instanceof ProjectEvent && $object->getTypeId() == 2) {
				$html .= format_date($value, null, $tz_val);
			} else {
				$html .= format_datetime($value, null, $tz_val);
			}
			break;
		case DATA_TYPE_DATE:
			$html .= format_date($value, null, 0);
			break;
		case DATA_TYPE_INTEGER:
		case DATA_TYPE_FLOAT:
			$html .= $value;
			break;
		case DATA_TYPE_BOOLEAN:
			$html .= $value ? lang('yes') : lang('no');
			break;
		case DATA_TYPE_STRING:
			$html .= clean($value);
			break;
		case 'email':
			foreach ($value as $email) {
				$html .= '<div class="email">'. $email->getEmailAddress() .'</div>';
			}
			break;
		case 'address':
			foreach ($value as $address) {
				$html .= '<div class="address">'. $address->toString() .'</div>';
			}
			break;
		case 'phone':
			foreach ($value as $phone) {
				$html .= '<div class="email">'. $phone->getNumber() . ($phone->getName() == "" ? "" : " (".$phone->getName().")") .'</div>';
			}
			break;
		case 'webpage':
			foreach ($value as $webpage) {
				$html .= '<div class="webpage">'. $webpage->getFixedUrl() .'</div>';
			}
			break;
		case 'image':
			$html .= '<div class="cardIcon"><img src="'.$value.'"/></div>';
			break;
		default:
			$html .= clean($value);
	}
	$html .= '</td></tr>';

	return $html;
}




function get_custom_property_input_html($customProp, $object, $genid, $input_base_name = 'object_custom_properties',$member_parent, $property_perm = null, $is_bootstrap = false, $hide_label = false, $override_input_name = null, $selected_value = null, $additional_config = array()) {

	$label = clean($customProp->getName());

	if (is_null($selected_value)) {
		
		$default_value = null;
		Hook::fire("custom_property_input_default_value", array('customProp' => $customProp, 'object' => $object, 'member_parent_id' => array_var($_REQUEST, 'parent')), $default_value);

		if (is_null($default_value)) {
			if ($customProp->getIsMultipleValues() || $customProp->getType() == 'table'){
				$default_value = CustomPropertyValues::getCustomPropertyValues($object->getId(), $customProp->getId());
		
				if (!is_array($default_value) || count($default_value) == 0) {
					$default_value = $customProp->getDefaultValue();
				}
			}else{
				if ($object instanceof ContentDataObject) {
					$cpv = CustomPropertyValues::getCustomPropertyValue($object->getId(), $customProp->getId());
				} else {
					$cpv = null;
				}
				$default_value = $customProp->getDefaultValue();
				if($cpv instanceof CustomPropertyValue){
					$default_value = $cpv->getValue();
				}
			}
		}
		
	} else {
		$default_value = $selected_value;
	}
	
	// hard patch to correct the color cp values (when editing member) if they are not consistent with the color attribute of the member
	if ($customProp->getIsSpecial() && $customProp->getType() == 'color' && $object instanceof Member) {
		if($object->getColor() > 0) $default_value = $object->getColor();
	}

	$name = $input_base_name . '[' . $customProp->getId() . ']';
	if ($override_input_name) {
		$name = $override_input_name;
	}
    
	$config = array();
	$config['name'] = $name;
	$config['default_value'] = $default_value;
	$config['genid'] = $genid;
	$config['label'] = $label;
	$config['parent_member_id'] = array_var($_REQUEST, 'parent');
	$config['is_bootstrap'] = $is_bootstrap;
	$config['member_parent'] = $member_parent;
	$config['style'] = '';
	if ($object instanceof Member) {
		$config['member_id'] = $object->getId();
		$config['member_is_new'] = $object->isNew();
		$config['member'] = $object;
        if (!$object->isNew()) $config['parent_member_id'] = $object->getParentMemberId();
	} else {
		$config['object_id'] = $object->getId();
		$config['object_is_new'] = $object->isNew();
		$config['object'] = $object;
		if ($object->isNew()) {
			$ot = ObjectTypes::instance()->findById($customProp->getObjectTypeId());
			if ($ot instanceof ObjectType && $ot->getType() == 'dimension_object') {
				$config['member_is_new'] = true;
			}
		}
	}
	if ($hide_label) {
		$config['hide_label'] = true;
	}

	Hook::fire('custom_property_additional_style', array('object' => $object, 'custom_property' => $customProp), $config);
	
	if ($property_perm) $config['property_perm'] = $property_perm;
	
	// force readonly
	if (array_var($additional_config, 'property_perm') == 'view') {
		$config['property_perm'] = 'view';
	}
	// merge additional config
	$config = array_merge($config, $additional_config);
	
	$html = render_custom_property_by_type($customProp,$config);

	return $html;
}

function render_custom_property_error_field($error_message_code, $configs, $custom_property) {
	$html = '';
	if (!array_var($configs, 'hide_label')) {
		$html = '<label style="display: none !important;" for="' . $configs['genid'] . 'cp' . $custom_property->getId() . '">&nbsp;</label>';
	}
	$html .= '<span id="' . $configs['genid'] . 'cp' . $custom_property->getId() . '_error" class="cp-error-message" style="display: none; color:red">' . lang($error_message_code) . '</span>';
	return $html;
}



/**
 * Shared building blocks for the HTML-help "i" icon, used both for real custom properties
 * (CustomProperty/MemberCustomProperty, keyed by numeric id) and for "fixed properties" (the
 * special non-custom-property rows - fixed columns, dimension classification/association
 * selectors, subtype selector, "located under" - keyed by a string id like "fixedprop_x", whose
 * html help lives in a property_group_properties row instead). See cp_has_html_help() /
 * fixed_property_has_html_help() below for the two "is this enabled" checks, which feed into
 * these two renderers.
 *
 * Deliberately NOT using bootstrap's .popover() for the popover behavior (tried it first): this
 * app runs Bootstrap 3.3.0's positioning math (getPosition()/offset()), which - combined with the
 * icon sitting inside a <label> inside nested card/grid layouts - produced an absolutely-positioned
 * popover that landed on top of the next field instead of next to the icon, and was hard to pin
 * down further without live browser access. og.advanced_core.toggle_cp_help_popover() instead does
 * its own simple, directly-verifiable positioning (icon offset + icon height) with no third-party
 * positioning engine involved.
 */
function render_html_help_icon_trigger_html($icon_id) {
	// Meant to be embedded *inside* the label's own text, not appended as a sibling after it:
	// some layouts (e.g. the property_groups grouped form) render the label as a block element,
	// and a block always starts a new line - anything appended after it as a sibling gets pushed
	// below it. Content placed inside the label instead flows with the label's own text
	// regardless of the label's display type.
	return ' <i class="cp-help-info-icon" id="' . $icon_id . '"></i>';
}

function render_html_help_icon_content_html($icon_id, $content) {
	// $content is raw, admin-authored HTML straight out of CKEditor - occasionally slightly
	// malformed (an unclosed or stray tag from pasted content, etc). Embedding it as literal
	// markup (e.g. inside a <div style="display:none;">) puts it in the real DOM the browser
	// parses; a stray closing tag in there can prematurely close an ANCESTOR element and corrupt
	// everything rendered after it on the page (this is what caused fields to go missing/reorder
	// once several help texts were defined).
	// A <script type="text/html"> was tried first, but og.extractScripts() (og.js) re-executes
	// EVERY <script> tag it finds in AJAX-loaded panel HTML via a regex that ignores the "type"
	// attribute entirely - it doesn't know "text/html" means "don't eval this", so it tried to
	// eval() the raw admin HTML as JavaScript, throwing "Unexpected token '<'" on every object
	// form that has an html-help icon. A <textarea> is a raw-text element like <script> (its
	// contents aren't parsed as markup, so malformed HTML still can't corrupt the page) but it
	// isn't a <script> tag, so extractScripts() leaves it alone. Read back with .val()/.text(),
	// NOT .html() - a textarea isn't in the browser's serialization skip-list ("script", "style",
	// etc.), so innerHTML would come back HTML-entity-escaped.
	$html = '<textarea style="display:none;" id="' . $icon_id . '_content">' . $content . '</textarea>';
	// extractScripts() also re-executes this binder script a second time (same reason as above),
	// which would double-bind the click handler and make a single click open-then-immediately-
	// close the popover. Namespacing the handler and unbinding it first keeps a single run in
	// effect no matter how many times this script actually executes.
	$html .= '<script>
		$(function(){
			$("#' . $icon_id . '").off("click.cpHelp").on("click.cpHelp", function(e){
				e.preventDefault();
				e.stopPropagation();
				og.advanced_core.toggle_cp_help_popover(this, "' . $icon_id . '_content");
			});
		});
	</script>';
	return $html;
}

/**
 * Returns true when the given custom property has HTML help defined (guards against the columns
 * not being registered - e.g. advanced_core not active - and against enabled-but-empty content).
 */
function cp_has_html_help($custom_property) {
	$columns = $custom_property->getColumns();
	if (!in_array('html_help_enabled', $columns) || !in_array('html_help_content', $columns)) {
		return false;
	}
	if (!$custom_property->getColumnValue('html_help_enabled')) {
		return false;
	}
	if (trim((string) $custom_property->getColumnValue('html_help_content')) == '') {
		return false;
	}
	return true;
}

function render_cp_html_help_icon_trigger($custom_property, $genid) {
	if (!cp_has_html_help($custom_property)) return '';
	return render_html_help_icon_trigger_html($genid . 'cp_help_icon' . $custom_property->getId());
}

function render_cp_html_help_icon_content($custom_property, $genid) {
	if (!cp_has_html_help($custom_property)) return '';
	$icon_id = $genid . 'cp_help_icon' . $custom_property->getId();
	return render_html_help_icon_content_html($icon_id, $custom_property->getColumnValue('html_help_content'));
}

/**
 * Same as cp_has_html_help() above, but for a "fixed property" - $prop_data is the plain array
 * built by the various property_groups_hooks.php functions (get_classification_properties_for_cp_form,
 * get_predefined_properties_for_cp_form, property_groups_list_custom_properties_for_type_modify_cps),
 * carrying html_help_enabled/html_help_content once custom_properties_definition_override_custom_properties_list
 * (plugins/advanced_core/hooks/custom_properties_definition_hooks.php) has injected them from the
 * property's property_group_properties row.
 */
function fixed_property_has_html_help($prop_data) {
	if (!array_var($prop_data, 'html_help_enabled')) return false;
	if (trim((string) array_var($prop_data, 'html_help_content', '')) == '') return false;
	return true;
}

/**
 * $property_id is the fixed property's string id (e.g. "fixedprop_x", "assoc_12"). md5() keeps
 * it short and free of characters ("|", etc.) that aren't safe in an HTML id attribute.
 */
function fixed_property_html_help_icon_id($genid, $property_id) {
	return $genid . 'cp_help_icon_fx_' . md5($property_id);
}

function render_fixed_property_html_help_icon_trigger($prop_data, $genid, $property_id) {
	if (!fixed_property_has_html_help($prop_data)) return '';
	return render_html_help_icon_trigger_html(fixed_property_html_help_icon_id($genid, $property_id));
}

function render_fixed_property_html_help_icon_content($prop_data, $genid, $property_id) {
	if (!fixed_property_has_html_help($prop_data)) return '';
	$icon_id = fixed_property_html_help_icon_id($genid, $property_id);
	return render_html_help_icon_content_html($icon_id, array_var($prop_data, 'html_help_content'));
}


function render_custom_property_by_type($custom_property, $configs) {
    $style = "margin-bottom: 1rem;";
    if (array_var($configs,'is_bootstrap')){
        $style = "margin-bottom: 1rem; width:100%;";
    }
	$style .= $configs['style'];
	$custom_property_id = $custom_property->getId();
	$container_id = $configs['genid'] . '-container-cp' . $custom_property_id;
	$html = '<div class="input-container" style="'.$style.'" id="'.$container_id.'">';

	if (!array_var($configs, 'hide_label')) {
		$after_label = $custom_property->getType() == 'boolean' ? '' : ':';
		$label_text = $configs['label'] . $after_label . render_cp_html_help_icon_trigger($custom_property, $configs['genid']);
		$html .= label_tag($label_text, $configs['genid'] . 'cp' . $custom_property->getId(), $custom_property->getIsRequired(), array('style' => 'display:inline-block;'), '');
		$html .= render_cp_html_help_icon_content($custom_property, $configs['genid']);
	}
	//if (isset($configs['member'])) $html .= '<br>';
	
	switch ($custom_property->getType()) {
		case 'text':
			$html .= render_text_custom_property_field($custom_property, $configs);
			break;
		case 'amount':
			$html .= render_money_amount_custom_property_field($custom_property, $configs);
			break;	
		case 'numeric':
			$html .= render_numeric_custom_property_field($custom_property, $configs);
			break;
		case 'memo':
			$html .= render_large_text_custom_property_field($custom_property, $configs);
			break;
		case 'boolean':
			$html .= render_boolean_custom_property_field($custom_property, $configs);
			break;
		case 'date':
			$html .= render_date_custom_property_field($custom_property, $configs);
			break;
		case 'datetime':
			$html .= render_datetime_custom_property_field($custom_property, $configs);
			break;
        case 'list':
			$html .= render_list_custom_property_field($custom_property, $configs);
			break;
		case 'table':
			$html .= render_table_custom_property_field($custom_property, $configs);
			break;
		case 'color':
			$html .= render_color_custom_property_field($custom_property, $configs);
			break;
		case 'address':
			$html .= render_address_custom_property_field($custom_property, $configs);
			break;
		case 'email':
			$email = true;
			$html .= render_email_custom_property_field($custom_property, $configs, $email);
			break;
		case 'user':
		case 'contact':
			$html .= render_contact_custom_property_field($custom_property, $configs);
			break;
		case 'url':
			$html .= render_url_custom_property_field($custom_property, $configs);
			break;
		case 'image':
			$html .= render_image_custom_property_field($custom_property, $configs);
			break;
		case 'object_link':
			$html .= render_object_link_custom_property_field($custom_property, $configs);
			break;
		case 'display_member_property':
			if (Plugins::instance()->isActivePlugin('member_fields_in_objects')) {
				Env::useHelper('functions', 'member_fields_in_objects');
				$html .= render_member_external_property_field($custom_property, $configs);
			}
			break;
		case 'gis_coordinates':
			if (Plugins::instance()->isActivePlugin('gis')) {
				Env::useHelper('functions', 'gis');
				$html .= render_gis_coordinates_property_field($custom_property, $configs);
			}
			break;
		default: break;
	}
	$html .= '<div class="clear"></div>';

	if (isset($configs['warning_msg']) && trim($configs['warning_msg']) != '') {
		$html .= '<div class="cp-warning-message" style="display: none;">' . $configs['warning_msg'] . '</div>';
	}

    if ($custom_property->getDescription() != ''){
        // the label is set to pad the description
        $html .= '<div><span class="desc">' . clean($custom_property->getDescription()) . '</span></div>';
    }
	$html .= '</div>';
	
	Hook::fire("after_render_custom_property_input", array('cp'=>$custom_property, 'config'=>$configs), $html);
	
	return $html;
} // render_custom_property_by_type


function render_multiple_custom_property_field($custom_property, $configs) {

	$view_name = $custom_property->getType() . "_multiple";

	tpl_assign('configs', $configs);
	tpl_assign('cp', $custom_property);
	return tpl_fetch(get_template_path('selectors/'.$view_name, 'custom_properties'));
}

function render_text_custom_property_field($custom_property, $configs) {
	if ($custom_property->getIsMultipleValues()) {
		$html = render_multiple_custom_property_field($custom_property, $configs);
	} else {

	    $class = 'cp-text';
	    $placeholder = '';
	    if (array_var($configs,'is_bootstrap')){
            $style = '';
            $class = 'form-control';
            $placeholder = $configs['label'];
        }
        $attributes = array('id' => $configs['genid'] . 'cp' . $custom_property->getId(),'class'=>$class,'placeholder'=>$placeholder);
        
        if (array_var($configs, 'property_perm') == 'view') $attributes['disabled'] = 'disabled';

		if (isset($configs['warning_msg']) && trim($configs['warning_msg']) != '') {
			$attributes['onfocus'] = "og.show_cp_generic_warning(this);";
			$attributes['onblur'] = "og.hide_cp_generic_warning(this);";
		}
        
		$html = text_field($configs['name'], $configs['default_value'], $attributes);
	}
	return $html;
}
function render_money_amount_custom_property_field($custom_property, $configs) {

    $html = '';
	$html .= '<div class="amount-container-fields">';
	// Currency selector
	$cp_value = CustomPropertyValues::instance()->findOne(array('conditions' => '`custom_property_id`='.$custom_property->getId().' AND `object_id`='.array_var($configs, 'object_id', 0)));
	$selected_currency = $cp_value instanceof CustomPropertyValue ? $cp_value->getCurrencyId() : 1;
	$currencies = Currencies::instance()->findAll();
	$options = '';
	foreach($currencies as $c){
		$selected = $selected_currency == $c->getId() ? 'selected="selected"' : '';
		$options .= '<option '.$selected.' value='.$c->getId().'>'.$c->getSymbol().'</option>';
	}
	$disabled = count($currencies) == 1 || array_var($configs, 'property_perm') == 'view' ? ' disabled="disabled" ' : '';
	
	$cur_readonly = '';
	Hook::fire('cp_amount_currency_selector_readonly', array('cp' => $custom_property, 'configs' => $configs), $cur_readonly);
	$html .= '<select name="object_custom_properties['.$custom_property->getId().'][currency_id]" '.$disabled.' '.$cur_readonly.'>'.$options.'</select>';

	// Amount input
	$id = $configs['genid'] . 'cp' . $custom_property->getId();
	$class = '';
	$placeholder = '';
	$onChange = 'og.formatAmount(\''.$id.'\'); og.check_if_valid_amount_field(this);';
	$name = 'object_custom_properties['.$custom_property->getId().'][amount]';
	$attributes = array('id' => $id,'class'=>$class,'placeholder'=>$placeholder, 'onchange' => $onChange, 'name' => $name);

	Hook::fire('override_numeric_custom_property_attributes', array('cp' => $custom_property, 'configs' => $configs), $attributes);

	if (isset($configs['warning_msg']) && trim($configs['warning_msg']) != '') {
		$attributes['onfocus'] = "og.show_cp_generic_warning(this);";
		$attributes['onblur'] = "og.hide_cp_generic_warning(this);";
	}

	if (array_var($configs, 'property_perm') == 'view') $attributes['disabled'] = 'disabled';
	$value = format_amount($configs['default_value']);
	$html .= text_field($name, $value, $attributes);
	$html .= '</div>';
	$html .= render_custom_property_error_field('invalid_cp_amount_value', $configs, $custom_property);


	return $html;
}

function render_large_text_custom_property_field($custom_property, $configs) {
	if ($custom_property->getIsMultipleValues()) {
		$html = render_multiple_custom_property_field($custom_property, $configs);
	} else {
        $class = '';
        if (array_var($configs,'is_bootstrap')){
            $style = '';
            $class = 'form-control';
        }
        $attributes = array('id' => $configs['genid'] . 'cp' . $custom_property->getId(), 'class'=>$class, 'rows' => 7, 'cols' => '60');
        
        if (array_var($configs, 'property_perm') == 'view') $attributes['disabled'] = 'disabled';

		if (isset($configs['warning_msg']) && trim($configs['warning_msg']) != '') {
			$attributes['onfocus'] = "og.show_cp_generic_warning(this);";
			$attributes['onblur'] = "og.hide_cp_generic_warning(this);";
		}
        
        $html = textarea_field($configs['name'], $configs['default_value'], $attributes);
	}
	return $html;
}

function render_date_custom_property_field($custom_property, $configs) {

	if ($custom_property->getIsMultipleValues()) {
		$html = render_multiple_custom_property_field($custom_property, $configs);
	} else {

		$cp_date_value = null;
		if (trim($configs['default_value']) != '' && trim($configs['default_value']) != EMPTY_DATETIME) {
			$cp_date_value = DateTimeValueLib::makeFromString($configs['default_value']);
		}
		
		$disabled = (array_var($configs, 'property_perm') == 'view');

		$listeners = null;
		if (isset($configs['warning_msg']) && trim($configs['warning_msg']) != '') {
			$listeners = array(
				"focus" => "function(picker){ og.show_cp_generic_warning(picker); }",
				"blur" => "function(picker){ og.hide_cp_generic_warning(picker); }",
			);
		}

		$html = pick_date_widget2($configs['name'], $cp_date_value, $configs['genid'], null, null, $configs['genid'] . 'cp' . $custom_property->getId(), $listeners, $disabled);
	}

	return $html;
}

function render_datetime_custom_property_field($custom_property, $configs) {

	if ($custom_property->getIsMultipleValues()) {
		$html = render_multiple_custom_property_field($custom_property, $configs);
	} else {

		$cp_date_value = null;
		if (trim($configs['default_value']) != '' && trim($configs['default_value']) != EMPTY_DATETIME) {
			$cp_date_value = DateTimeValueLib::makeFromString($configs['default_value']);
		}
		
		$disabled = (array_var($configs, 'property_perm') == 'view');

		$listeners = null;
		if (isset($configs['warning_msg']) && trim($configs['warning_msg']) != '') {
			$listeners = array(
				"focus" => "function(picker){ og.show_cp_date_warning(picker); }",
				"blur" => "function(picker){ og.hide_cp_date_warning(picker); }",
			);
		}

		$html = '<div class="datetime"><div class="inner-row">' . pick_date_widget2($configs['name'], $cp_date_value, $configs['genid'], null, null, $configs['genid'] . 'cp' . $custom_property->getId(), $listeners, $disabled);
		
		$i_name = str_replace('['.$custom_property->getId().']', '[time]['.$custom_property->getId().']', $configs['name']);
		$html .= pick_time_widget2($i_name, $cp_date_value, $configs['genid'], null, null, $configs['genid'] . 'cp' . $custom_property->getId().'_time', null, $disabled) . '</div></div><div class="clear"></div>';
		
	}

	return $html;
}

function render_numeric_custom_property_field($custom_property, $configs) {
	if ($custom_property->getIsMultipleValues()) {
		$html = render_multiple_custom_property_field($custom_property, $configs);
	} else {

        $class = 'cp-numeric';
        $placeholder = '';
        $type = 'numeric';
        $onchange = "og.check_if_valid_cp_num(this);";
        if (array_var($configs,'is_bootstrap')){
	        $type = 'number';
	        $onchange = '';
            $style = '';
            $class = 'form-control';
            $placeholder = $configs['label'];
        }
        $attributes = array('id' => $configs['genid'] . 'cp' . $custom_property->getId(),'type'=>$type,'onchange'=>$onchange,'class'=>$class,'placeholder'=>$placeholder);
        
        if (array_var($configs, 'property_perm') == 'view') $attributes['disabled'] = 'disabled';

		Hook::fire('override_numeric_custom_property_attributes', array('cp' => $custom_property, 'configs' => $configs), $attributes);

		if (isset($configs['warning_msg']) && trim($configs['warning_msg']) != '') {
			$attributes['onfocus'] = "og.show_cp_generic_warning(this);";
			$attributes['onblur'] = "og.hide_cp_generic_warning(this);";
		}

		$html = numeric_field($configs['name'], $configs['default_value'], $attributes);
		$html .= render_custom_property_error_field('invalid_cp_numeric_value', $configs,$custom_property);	
		
	}
	return $html;
}


function render_url_custom_property_field($custom_property, $configs) {
	if ($custom_property->getIsMultipleValues()) {
		$html = render_multiple_custom_property_field($custom_property, $configs);
	} else {

        $class = 'cp-url';
		$type = '';
        $placeholder = '';
        $onchange = "og.check_if_valid_url(this);";
        if (array_var($configs,'is_bootstrap')){
            $style = '';
            $class = 'form-control';
            $placeholder = $configs['label'];
        }
        $attributes = array('id' => $configs['genid'] . 'cp' . $custom_property->getId(),'type'=>$type,'onchange'=>$onchange,'class'=>$class,'placeholder'=>$placeholder);

		if (isset($configs['warning_msg']) && trim($configs['warning_msg']) != '') {
			$attributes['onfocus'] = "og.show_cp_generic_warning(this);";
			$attributes['onblur'] = "og.hide_cp_generic_warning(this);";
		}
        
        if (array_var($configs, 'property_perm') == 'view') $attributes['disabled'] = 'disabled';
		$html = url_field($configs['name'], $configs['default_value'], $attributes);
		$html .= render_custom_property_error_field('invalid_cp_url_value',$configs,$custom_property);
	}
	return $html;
}

function render_email_custom_property_field($custom_property, $configs) {
    if ($custom_property->getIsMultipleValues()) {
        $html = render_multiple_custom_property_field($custom_property, $configs);
    } else {

        $class = 'cp-text';
        $type = '';
        $placeholder = '';
        $onchange = "og.check_if_valid_email(this);";

        if (array_var($configs,'is_bootstrap')){
            $style = '';
            $class = 'form-control';
            $placeholder = $configs['label'];
        }

        $attributes = array(
            'id' => $configs['genid'] . 'cp' . $custom_property->getId(),
            'type' => $type,
            'onchange' => $onchange,
            'class' => $class,
            'placeholder' => $placeholder
        );

        if (array_var($configs, 'property_perm') == 'view') {
            $attributes['disabled'] = 'disabled';
        }

		if (isset($configs['warning_msg']) && trim($configs['warning_msg']) != '') {
			$attributes['onfocus'] = "og.show_cp_generic_warning(this);";
			$attributes['onblur'] = "og.hide_cp_generic_warning(this);";
		}

        // usamos el helper sin validación HTML5
        $html = cp_email_field($configs['name'], $configs['default_value'], $attributes);
        $html .= render_custom_property_error_field('invalid_cp_email_value', $configs, $custom_property);
    }

    return $html;
}


function render_boolean_custom_property_field($custom_property, $configs) {
	$allow_ns = cp_boolean_allows_not_specified($custom_property);
	$current = normalize_cp_boolean_stored_value(array_var($configs, 'default_value'));
	if (!$allow_ns && $current === '0') {
		$fallback = normalize_cp_boolean_stored_value($custom_property->getDefaultValue());
		if ($fallback === '1' || $fallback === '-1') {
			$current = $fallback;
		} else {
			$current = '1';
		}
	}
	$name = $configs['name'];
	$base_id = $configs['genid'] . 'cp' . $custom_property->getId();
	$perm_view = array_var($configs, 'property_perm') == 'view';
	$tabindex = array_var($configs, 'tabindex');

	$html = '<span class="cp-boolean-radios">';
	$choices = array(
		'1' => lang('yes'),
		'-1' => lang('no'),
	);
	if ($allow_ns) {
		$choices['0'] = lang('cp boolean not specified');
	}
	foreach ($choices as $val => $label) {
		$val = (string) $val;
		$id_suffix = str_replace('-', 'neg', $val);
		$input_id = $base_id . '_bool_' . $id_suffix;
		$checked = ($current === $val);
		$ti = ($tabindex !== null) ? ' tabindex="' . (int) $tabindex . '"' : '';
		$dis = $perm_view ? ' disabled="disabled"' : '';
		$chk = $checked ? ' checked="checked"' : '';
		$html .= '<label class="cp-boolean-radio-label" style="margin-right:12px;">';
		$html .= '<input type="radio" class="checkbox" name="' . htmlspecialchars($name, ENT_COMPAT, 'UTF-8') . '" id="' . htmlspecialchars($input_id, ENT_COMPAT, 'UTF-8') . '" value="' . htmlspecialchars($val, ENT_COMPAT, 'UTF-8') . '"' . $chk . $ti . $dis . ' />';
		$html .= ' ' . clean($label) . '</label>';
	}
	$html .= '</span>';

	return $html;
}

function render_list_custom_property_field($custom_property, $configs) {
	if ($custom_property->getIsMultipleValues()) {
		$html = render_multiple_custom_property_field($custom_property, $configs);
	} else {
		$options_html = render_list_options_custom_property_field(explode(',', $custom_property->getValues()), $custom_property, $configs);
        $class = 'cp-list';
        if (array_var($configs,'is_bootstrap')){
            $style = '';
            $class = 'form-control';
        }
        
        $attributes = array('id' => $configs['genid'] . 'cp' . $custom_property->getId(), 'class'=>$class);

		if (isset($configs['warning_msg']) && trim($configs['warning_msg']) != '') {
			$attributes['onfocus'] = "og.show_cp_generic_warning(this);";
			$attributes['onblur'] = "og.hide_cp_generic_warning(this);";
		}
        
        if (array_var($configs, 'property_perm') == 'view') $attributes['disabled'] = 'disabled';
        
		$html = select_box($configs['name'], $options_html, $attributes);
	}
	return $html;
}

function render_color_custom_property_field($custom_property, $configs) {
	$genid = $configs['genid'];
	$name = $configs['name'];
	$default_value = $configs['default_value'];

	$is_new_member = array_var($configs, 'member_is_new') || array_var($configs, 'object_is_new');
	if ($is_new_member && isset($configs['parent_member_id']) && $configs['parent_member_id'] > 0) {
		$pmem = Members::instance()->findById($configs['parent_member_id']);
		if ($pmem instanceof Member) {
			$default_value = $pmem->getMemberColor();
		}
	}
	$disabled = array_var($configs, 'property_perm') == 'view' ? '1' : '0';

	$html = '<div class="cp-color-chooser"><div id="'.$genid.'colorcontainer-cp'.$custom_property->getId().'"></div><div class="x-clear"></div></div>';
	
	$html .= "<script>$(function(){";
	$html .= "var cont = document.getElementById('".$genid."colorcontainer-cp".$custom_property->getId()."');";
	$html .= "if (cont) cont.innerHTML = og.getColorInputHtml('$genid', '$name', '$default_value', '', '', $disabled);";
	$html .= '});</script>';
	return $html;
}


function render_address_custom_property_field($custom_property, $configs) {
	$genid = $configs['genid'];
	$name = $configs['name'];
	$default_value = $configs['default_value'];
	
	$values = null;
	Hook::fire('custom_property_field_initial_value', array('cp' => $custom_property, 'configs' => $configs, 'multiple' => true), $values);
	if (is_null($values) && !empty($configs['object_id'])) {
		$values = CustomPropertyValues::getCustomPropertyValues($configs['object_id'], $custom_property->getId());
	}

	if($default_value != ''){
		$address_values[] = $default_value;
	} else if (is_array($values)) {
		foreach ($values as $v) $address_values[] = $v->getValue();
	}
	
	$disabled = array_var($configs, 'property_perm') == 'view';
	
	$html = '<div class="field" style="float:left;">';
	
	$html .= address_field($name, isset($address_values) ? $address_values : "", $genid, array(
			'container_id' => $genid.'addresscontainer-cp'.$custom_property->getId(),
			'disabled' => $disabled,
			'input_base_id' => $name//"cp".$custom_property->getId(),
	), true);
	$html .= '</div>';

	return $html;
}


/**
 * This function render the custom properties of type contact.
 * It returns the html text that will be included in the form.
 * 
 * It is important to note that the possible values (the list of contacts or list of users) are not retrieved directly here,
 * but are popuated in the public/assets/javascript/og/ContactCombo.js javascript class.
 * This is something that should be improved
 * 
 * @param CustomProperty $custom_property
 * @param array $configs
 * @return string
 */
function render_contact_custom_property_field($custom_property, $configs) {
	$genid = $configs['genid'];
	$name = $configs['name'];
	$default_value = $configs['default_value'];
	$is_multiple = $custom_property->getIsMultipleValues() ? 1 : 0;
	
	$value = '';
	$contact = null;
	
	$cp_value = null;
	Hook::fire('custom_property_field_initial_value', array('cp' => $custom_property, 'configs' => $configs), $cp_value);
	
	if (is_null($cp_value)) {
	    if($is_multiple){
	        $array_cp_values = array();	        
	        $cp_values = CustomPropertyValues::getCustomPropertyValues(array_var($configs, 'object_id', 0), $custom_property->getId());
	        if ( count($cp_values) > 0 ){
	            foreach ($cp_values as $cpv_object){
	                $array_cp_values[] = $cpv_object->getValue();
                }
	        }
	    }else{
	       $cp_value = CustomPropertyValues::getCustomPropertyValue(array_var($configs, 'object_id', 0), $custom_property->getId());
	    }
	}

	if(is_numeric($default_value) && $default_value > 0){
		$value = $default_value;
		$contact = Contacts::instance()->findById($value);
		// set the $contacts and $array_cp_values variables that will be used to 
		// populate the selector with the selected values when multiple values are allowed.
		$contacts = array($contact);
		$array_cp_values = array($value);
	} else if (!$is_multiple && $cp_value) {
		$value = $cp_value->getValue();
		$contact = Contacts::instance()->findById($value);
	}else{
	    $contacts = array();
	    if (isset($array_cp_values) && count($array_cp_values) > 0){
	        foreach ($array_cp_values as $val){
	            $value .= $val.',';
	            $contact = Contacts::instance()->findById($val);
	            if (!empty($contact)){
	                $contacts[] = $contact;
                }
	        }
	        $value = rtrim($value,',');
	    }
	}
	
	$emtpy_text = lang('select contact');
	
	$filters = array();
	if ($custom_property->getType() == 'user') {
		$filters['is_user'] = 1;
		$filters['disabled'] = '0';
		$emtpy_text = lang('select user');
	}

	// The configured contact groups travel as a single bitmask so that any combination can
	// be expressed. See get_contact_cp_type_filters().
	$filters = array_merge($filters, get_contact_cp_type_filters(
		$custom_property->getType(), $custom_property->getFilterValuesBy()
	));

	$ot = ObjectTypes::instance()->findById($custom_property->getObjectTypeId());
	
	if ($ot->getType() == 'dimension_object') {
		$obj_member = null;
		if (array_var($configs, 'object_id', 0) > 0) {
			$obj_member = Members::findOneByObjectId($configs['object_id']);
		}
		$configs['member_is_new'] = array_var($configs, 'object_is_new');
		if ($obj_member instanceof Member) {
			$configs['member'] = $obj_member;
			$configs['member_id'] = $obj_member->getId();
			$configs['parent_member_id'] = $obj_member->getParentMemberId();
		} else {
			$configs['parent_member_id'] = array_var($_REQUEST, 'parent');
		}
	}
	
	if (isset($configs['member']) && $configs['member'] instanceof Member || $ot->getType() == 'dimension_object') {
		
		if ($custom_property->getType() == 'user') {
			if ($configs['member_is_new']) {
			    //We need to explain this here
			    //What does it do? Should it be configurable?
				$filters['has_permissions'] = $configs['parent_member_id'];
			} else {
				$filters['has_permissions'] = $configs['member_id'];
			}
		} else {
			if ($ot->getType() == 'dimension_object') {
				if ($configs['member_is_new']) {
					$filters['member_ids'] = $configs['parent_member_id'];
				} else {
					$filters['member_ids'] = $configs['member_id'];
				}
			}
		}
		
		if (isset($configs['member'])) {		    
    		Hook::fire('member_contact_cp_filters', array(
    			'cp' => $custom_property, 'member' => $configs['member'], 
    			'is_new' => $configs['member_is_new'], 'ot' => $ot
    		), $filters);
		}
		
	}
	
	Hook::fire('override_contact_cp_filters', array("cp"=>$custom_property), $filters);
	
	if (is_array($filters) && count($filters) > 0) {
		$filters_str = '{';
		foreach ($filters as $k => $v) {
			if ($v == '') continue;
			$filters_str .= ($filters_str=='{' ? '' : ',') . "$k : $v";
		}
		$filters_str .= '}';
	} else {
		$filters_str = 'null';
	}

	$selected_names = '';
	$selected_ids = 0;
	if($is_multiple){
	    if (isset($contacts) && count($contacts) >0){
	        foreach($contacts as $contact){
	            $selected_names .= ($contact instanceof Contact ? clean($contact->getObjectName()) : '') .',';
	        }
	        $selected_ids = implode(',',$array_cp_values);
	    }
	    
	}else{
	    $selected_names = $contact instanceof Contact ? clean($contact->getObjectName()) : '';
	    $selected_ids = is_numeric($value) ? "$value" : "";
	}
	
	$html = '<div id="'.$genid.'contacts_combo_container-cp'.$custom_property->getId().'" class="multiple-cp-contact-combo-container"></div>';
        if ($configs["member_parent"] == "") {
            $configs["member_parent"] = 0;
        }
        
        
    $disabled = array_var($configs, 'property_perm') == 'view';
    $disabled_str = $disabled ? 'disabled: true,' : '';
    
    $onchange_fn = "null";
    Hook::fire('contact_cp_selector_onchange', array("cp"=>$custom_property), $onchange_fn);

	$onblur_fn = "null";
	$onfocus_fn = "null";
	if (isset($configs['warning_msg']) && trim($configs['warning_msg']) != '') {
		$onfocus_fn = "function(combo){ og.show_cp_generic_warning(combo); }";
		$onblur_fn = "function(combo){ og.hide_cp_generic_warning(combo); }";
	}
    
	$html .= '<script>
			$(function(){
			  og.renderContactSelector({
				genid: "'.$genid.'",
				id: "cp'.$custom_property->getId().'",
				name: "'.$name.'",
				render_to: "contacts_combo_container-cp'.$custom_property->getId().'",
				selected: "'.$selected_ids.'",
				selected_name: "'.$selected_names.'",
                is_multiple: '.$is_multiple .',
				empty_text: "'. $emtpy_text .'",
				listClass: "custom-prop",
				filters: '.$filters_str.','.
				$disabled_str.'
				memberId:"'.$configs["member_parent"].'",
				cp_type: "'.$custom_property->getType().'",
				linked_to: "'.$custom_property->getLinkedTo().'",
				show_only_name: '.(array_var($configs, 'show_only_name') ? 'true' : 'false').',
				onchange_fn: '.$onchange_fn.',
				onfocus_fn: '.$onfocus_fn.',
				onblur_fn: '.$onblur_fn.'
			  });
			});
			</script>';
	return $html;
}





function render_list_options_custom_property_field($cp_options, $custom_property, $configs) {
	$options = array();
	foreach($cp_options as $value) {

		$text = null;
		$has_id_and_value = false;
		if (strpos($value, '@') !== false) {
			$exp = explode('@', $value);
			$value = array_var($exp, 0);
			$text = array_var($exp, 1);
			$has_id_and_value = true;
		}

		$v = ($text == null ? $value : $text);
		if ($custom_property->getCode() == "") {
			$text = $v;
		} else {
            if ($custom_property->getIsSpecial()) {
                $label_code = str_replace("_special", "", $custom_property->getCode());
                $text = Localization::instance()->lang($label_code." ".$value);
                if (is_null($text)) {
                	$text = Localization::instance()->lang($value);
                }
                // try to get the lang of the text part when cp list value is defined as id@text
                if (is_null($text) && $has_id_and_value) {
                	$text = Localization::instance()->lang($v);
                }
            }
            if (is_null($text)) $text = $v;
		}


		$selected = ($value == $configs['default_value']);

		if ($selected) {
			$options[] = '<option value="' . clean($value) . '" selected>' . clean($text) . '</option>';
		} else {
			$options[] = option_tag($text, $value);
		}
	}
	return $options;
}

function render_table_custom_property_field($custom_property, $configs) {
	
	tpl_assign('configs', $configs);
	tpl_assign('custom_property', $custom_property);
	return tpl_fetch(get_template_path('selectors/table', 'custom_properties'));
	
}

/**
 * Contact groups available as type filters of a contact custom property, in canonical order.
 *
 * @return array
 */
function get_contact_cp_filter_type_tokens() {
	return array('companies', 'contacts', 'users');
}

/**
 * Normalizes the stored filter_values_by value of a contact custom property into the list
 * of contact groups its selector is restricted to.
 *
 * Unknown tokens, duplicates and empty segments are dropped. An empty result means "no
 * restriction", which is also what selecting every group normalizes to.
 *
 * @param string $filter_values_by Raw stored value, comma separated tokens
 * @return array Ordered list of known tokens, empty when there is no restriction
 */
function get_contact_cp_filter_types($filter_values_by) {
	$known = get_contact_cp_filter_type_tokens();

	$selected = array();
	foreach (explode(',', (string) $filter_values_by) as $token) {
		$token = trim($token);
		if ($token != '' && in_array($token, $known) && !in_array($token, $selected)) {
			$selected[] = $token;
		}
	}

	// nothing selected and everything selected both mean "do not filter"
	if (count($selected) == 0 || count($selected) == count($known)) {
		return array();
	}

	return array_values(array_intersect($known, $selected));
}

/**
 * Converts a list of contact group tokens into the bitmask sent to the contact selector.
 *
 * @param array $types Tokens as returned by get_contact_cp_filter_types()
 * @return int Bitmask of CONTACT_CP_FILTER_* constants, 0 when there is no restriction
 */
function contact_cp_filter_types_to_mask($types) {
	if (!is_array($types)) {
		return 0;
	}

	$bits = array(
		'companies' => CONTACT_CP_FILTER_COMPANIES,
		'contacts'  => CONTACT_CP_FILTER_CONTACTS,
		'users'     => CONTACT_CP_FILTER_USERS,
	);

	$mask = 0;
	foreach ($types as $type) {
		if (isset($bits[$type])) {
			$mask = $mask | $bits[$type];
		}
	}

	$all = CONTACT_CP_FILTER_COMPANIES | CONTACT_CP_FILTER_CONTACTS | CONTACT_CP_FILTER_USERS;
	if ($mask == $all) {
		$mask = 0;
	}

	return $mask;
}

/**
 * Builds the SQL condition matching the contact groups encoded in a bitmask. The groups are
 * disjoint, so the condition is an OR of the selected ones. The users group excludes disabled
 * users, the same way the 'user' custom property type does.
 *
 * @param int $mask Bitmask of CONTACT_CP_FILTER_* constants
 * @param string $alias Optional alias of the contacts table, without the trailing dot
 * @return string Parenthesized condition, empty when the mask imposes no restriction
 */
function get_contact_type_mask_sql_condition($mask, $alias = '') {
	$mask = (int) $mask;
	$all = CONTACT_CP_FILTER_COMPANIES | CONTACT_CP_FILTER_CONTACTS | CONTACT_CP_FILTER_USERS;

	if ($mask <= 0 || ($mask & $all) == $all) {
		return '';
	}

	$alias = trim((string) $alias);
	$prefix = $alias == '' ? '' : $alias . '.';

	$conditions = array();
	if ($mask & CONTACT_CP_FILTER_COMPANIES) {
		$conditions[] = "({$prefix}`is_company` = 1)";
	}
	if ($mask & CONTACT_CP_FILTER_CONTACTS) {
		$conditions[] = "({$prefix}`is_company` = 0 AND {$prefix}`user_type` = 0)";
	}
	if ($mask & CONTACT_CP_FILTER_USERS) {
		// disabled users are excluded, matching what the 'user' custom property type does
		$conditions[] = "({$prefix}`is_company` = 0 AND {$prefix}`user_type` > 0 AND {$prefix}`disabled` = 0)";
	}

	if (count($conditions) == 0) {
		return '';
	}

	return '(' . implode(' OR ', $conditions) . ')';
}

/**
 * Returns the contact selector filters that restrict a contact custom property to the
 * contact groups configured by the administrator.
 *
 * The 'user' custom property type restricts the selector to users on its own, so the type
 * filters do not apply to it and its legacy filters are kept untouched.
 *
 * @param string $cp_type Custom property type
 * @param string $filter_values_by Raw stored value of the property
 * @return array Filters to merge into the contact selector configuration
 */
function get_contact_cp_type_filters($cp_type, $filter_values_by) {
	if ($cp_type == 'user') {
		return array('include_companies' => 1);
	}

	$mask = contact_cp_filter_types_to_mask(get_contact_cp_filter_types($filter_values_by));
	if ($mask > 0) {
		return array('contact_type_mask' => $mask);
	}

	return array('include_companies' => 1);
}
