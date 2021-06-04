<?php

function cp_info_sort_by_order($a, $b) {
	return $a['property_order'] > $b['property_order'];
}


function render_object_fixed_property_input($genid, $input_name, $col_info, $value, $object=null, $property_perm=null) {
	$html = '<div class="input-container" style="margin-bottom: 1rem;" id="input-'.array_var($col_info, 'col').'">';

	$html .= "<label>".array_var($col_info, 'label')."</label>";
	
	$disabled = $property_perm == 'view';
	$attr = array();
	if ($disabled) $attr['disabled']='disabled';

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
				$attr['style'] = 'height: 75px; width:60%;';
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
			$html .= select_box($input_name, array(), array('id' => $genid.'profileFormCompany', "class" => "og-edit-contact-select-company"));
			$html .= "<script>og.load_company_combo('".$genid."profileFormCompany', '$value');</script>";
			break;
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
	$html .= '<div class="clear"></div>';
	$html .= '</div>';

	return $html;
}


function render_object_fixed_property_for_view($col_info, $value, ContentDataObject $object) {
	
	if (!$value) return "";

	$html = '<tr class="cp-info"><td style="width:160px;"><span class="bold">'.array_var($col_info, 'label').': </span></td><td>';

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




function get_custom_property_input_html($customProp, $object, $genid, $input_base_name = 'object_custom_properties',$member_parent, $property_perm = null, $is_bootstrap = false) {

	$label = clean($customProp->getName());
	if ($customProp->getIsSpecial()) {
		$label_code = str_replace("_special", "", $customProp->getCode());
		$label_value = Localization::instance()->lang($label_code);
		if (!is_null($label_value)) $label = $label_value;
	}

    $default_value = null;
	Hook::fire("custom_property_input_default_value", array('customProp' => $customProp, 'object' => $object), $default_value);
	
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
	
	// hard patch to correct the color cp values (when editing member) if they are not consistent with the color attribute of the member
	if ($customProp->getIsSpecial() && $customProp->getType() == 'color' && $object instanceof Member) {
		if($object->getColor() > 0) $default_value = $object->getColor();
	}

	$name = $input_base_name . '[' . $customProp->getId() . ']';
    
	$config = array();
	$config['name'] = $name;
	$config['default_value'] = $default_value;
	$config['genid'] = $genid;
	$config['label'] = $label;
	$config['parent_member_id'] = array_var($_REQUEST, 'parent');
	$config['is_bootstrap'] = $is_bootstrap;
	$config['member_parent'] = $member_parent;
	if ($object instanceof Member) {
		$config['member_id'] = $object->getId();
		$config['member_is_new'] = $object->isNew();
		$config['member'] = $object;
        if (!$object->isNew()) $config['parent_member_id'] = $object->getParentMemberId();
	} else {
		$config['object_id'] = $object->getId();
		$config['object_is_new'] = $object->isNew();
		$config['object'] = $object;
	}
	
	if ($property_perm) $config['property_perm'] = $property_perm;
	
	$html = render_custom_property_by_type($customProp,$config);

	return $html;
}



function render_custom_property_by_type($custom_property, $configs) {
    $style = "margin-bottom: 1rem;";
    if (array_var($configs,'is_bootstrap')){
        $style = "margin-bottom: 1rem;width:100%";
    }
	$html = '<div class="input-container" style="'.$style.'">';
	$html .= label_tag($configs['label'], $configs['genid'] . 'cp' . $custom_property->getId(), $custom_property->getIsRequired(), array('style' => 'display:inline-block;'), $custom_property->getType() == 'boolean'?'':':');
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
		case 'user':
		case 'contact':
			$html .= render_contact_custom_property_field($custom_property, $configs);
			break;
		case 'image':
			$html .= render_image_custom_property_field($custom_property, $configs);
			break;
		default: break;
	}
	$html .= '<div class="clear"></div>';

    if ($custom_property->getDescription() != ''){
        // the label is set to pad the description
        $html .= '<div><label>&nbsp;</label><span class="desc">' . clean($custom_property->getDescription()) . '</span></div>';
    }

	$html .= '</div>';
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

	    $class = '';
	    $placeholder = '';
	    if (array_var($configs,'is_bootstrap')){
            $style = '';
            $class = 'form-control';
            $placeholder = $configs['label'];
        }
        $attributes = array('id' => $configs['genid'] . 'cp' . $custom_property->getId(),'class'=>$class,'placeholder'=>$placeholder);
        
        if (array_var($configs, 'property_perm') == 'view') $attributes['disabled'] = 'disabled';
        
		$html = text_field($configs['name'], $configs['default_value'], $attributes);
	}
	return $html;
}

function render_money_amount_custom_property_field($custom_property, $configs) {

    $html = '';
	// Currency selector
	$cp_value = CustomPropertyValues::findOne(array('conditions' => '`custom_property_id`='.$custom_property->getId().' AND `object_id`='.array_var($configs, 'object_id', 0)));
	$selected_currency = $cp_value instanceof CustomPropertyValue ? $cp_value->getCurrencyId() : 1;
	$currencies = Currencies::findAll();
	$options = '';
	foreach($currencies as $c){
		$selected = $selected_currency == $c->getId() ? 'selected="selected"' : '';
		$options .= '<option '.$selected.' value='.$c->getId().'>'.$c->getSymbol().'</option>';
	}
	$disabled = count($currencies) == 1 || array_var($configs, 'property_perm') == 'view' ? ' disabled="disabled" ' : '';
	$html .= '<select name="object_custom_properties['.$custom_property->getId().'][currency_id]" style="min-width: 40px;" '.$disabled.'>'.$options.'</select>';

	// Amount input
	$id = $configs['genid'] . 'cp' . $custom_property->getId();
	$class = '';
	$placeholder = '';
	$onChange = 'og.formatAmount(\''.$id.'\')';
	$name = 'object_custom_properties['.$custom_property->getId().'][amount]';
	$attributes = array('id' => $id,'class'=>$class,'placeholder'=>$placeholder, 'onChange' => $onChange, 'name' => $name);
	if (array_var($configs, 'property_perm') == 'view') $attributes['disabled'] = 'disabled';
	$value = format_amount($configs['default_value']);
	$html .= text_field($name, $value, $attributes);

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
        $attributes = array('id' => $configs['genid'] . 'cp' . $custom_property->getId(), 'class'=>$class, 'rows' => 5);
        
        if (array_var($configs, 'property_perm') == 'view') $attributes['disabled'] = 'disabled';
        
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

		$html = pick_date_widget2($configs['name'], $cp_date_value, $configs['genid'], null, null, $configs['genid'] . 'cp' . $custom_property->getId(), null, $disabled);
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

		$html = pick_date_widget2($configs['name'], $cp_date_value, $configs['genid'], null, null, $configs['genid'] . 'cp' . $custom_property->getId(), null, $disabled);
		
		$i_name = str_replace('['.$custom_property->getId().']', '[time]['.$custom_property->getId().']', $configs['name']);
		$html .= '<div style="float:left;">'. pick_time_widget2($i_name, $cp_date_value, $configs['genid'], null, null, $configs['genid'] . 'cp' . $custom_property->getId().'_time', null, $disabled) . '</div><div class="clear"></div>';
		
	}

	return $html;
}

function render_numeric_custom_property_field($custom_property, $configs) {
	if ($custom_property->getIsMultipleValues()) {
		$html = render_multiple_custom_property_field($custom_property, $configs);
	} else {

        $class = '';
        $placeholder = '';
        $type ='';
        if (array_var($configs,'is_bootstrap')){
            $type = 'number';
            $style = '';
            $class = 'form-control';
            $placeholder = $configs['label'];
        }
        $attributes = array('id' => $configs['genid'] . 'cp' . $custom_property->getId(),'type'=>$type,'class'=>$class,'placeholder'=>$placeholder);
        
        if (array_var($configs, 'property_perm') == 'view') $attributes['disabled'] = 'disabled';

		$html = text_field($configs['name'], $configs['default_value'], $attributes);
	}
	return $html;
}

function render_boolean_custom_property_field($custom_property, $configs) {
	$possible_values = array( 'yes' => 1, 'no' => -1);

	$options = array(option_tag("", "0"));
	foreach ($possible_values as $key => $value) {
		$opt_label = lang($key);
		$option_attributes = $configs['default_value'] == $value ? array('selected' => 'selected') : null;
		$options[] = option_tag($opt_label, $value, $option_attributes);
	}
	
	$attributes = array('id' => $configs['genid'] . 'cp' . $custom_property->getId());
	
	if (array_var($configs, 'property_perm') == 'view') $attributes['disabled'] = 'disabled';
	
	$html = select_box($configs['name'], $options, $attributes);

	return $html;
}

function render_list_custom_property_field($custom_property, $configs) {
	if ($custom_property->getIsMultipleValues()) {
		$html = render_multiple_custom_property_field($custom_property, $configs);
	} else {
		$options_html = render_list_options_custom_property_field(explode(',', $custom_property->getValues()), $custom_property, $configs);
        $class = '';
        if (array_var($configs,'is_bootstrap')){
            $style = '';
            $class = 'form-control';
        }
        
        $attributes = array('id' => $configs['genid'] . 'cp' . $custom_property->getId(), 'class'=>$class);
        
        if (array_var($configs, 'property_perm') == 'view') $attributes['disabled'] = 'disabled';
        
		$html = select_box($configs['name'], $options_html, $attributes);
	}
	return $html;
}

function render_color_custom_property_field($custom_property, $configs) {
	$genid = $configs['genid'];
	$name = $configs['name'];
	$default_value = $configs['default_value'];

	if (isset($configs['parent_member_id']) && $configs['parent_member_id'] > 0) {
		$pmem = Members::findById($configs['parent_member_id']);
		if ($pmem instanceof Member) {
			$default_value = $pmem->getColor();
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
	if (is_null($values)) {
		$values = CustomPropertyValues::getCustomPropertyValues($configs['object_id'], $custom_property->getId());
	}
	$address_values = array();
	if (is_array($values)) {
		foreach ($values as $v) $address_values[] = $v->getValue();
	}
	
	$disabled = array_var($configs, 'property_perm') == 'view';
	
	$html = '<div class="field" style="float:left;">';
	
	$html .= address_field($name, $address_values, $genid, array(
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
	        $cp_values = CustomPropertyValues::getCustomPropertyValues($configs['object_id'], $custom_property->getId());
	        if ( count($cp_values) > 0 ){
	            foreach ($cp_values as $cpv_object){
	                $array_cp_values[] = $cpv_object->getValue();
                }
	        }
	    }else{
	       $cp_value = CustomPropertyValues::getCustomPropertyValue($configs['object_id'], $custom_property->getId());
	    }
	}
	
	
	if (!$is_multiple && $cp_value) {
		$value = $cp_value->getValue();
		$contact = Contacts::findById($value);
	}else{
	    $contacts = array();
	    if (isset($array_cp_values) && count($array_cp_values) > 0){
	        foreach ($array_cp_values as $val){
	            $value .= $val.',';
	            $contact = Contacts::findById($val);
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

	$ot = ObjectTypes::findById($custom_property->getObjectTypeId());
	
	if ($ot->getType() == 'dimension_object') {
		$obj_member = null;
		if ($configs['object_id'] > 0) {
			$obj_member = Members::findOneByObjectId($configs['object_id']);
		}
		$configs['member_is_new'] = $configs['object_is_new'];
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
				cp_type: "'.$custom_property->getType().'"
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
