<div class="custom-properties"><?php
require_javascript("og/CustomProperties.js");

$object_type_id = $_custom_properties_object instanceof TemplateTask ? ProjectTasks::instance()->getObjectTypeId() : $_custom_properties_object->getObjectTypeId();
$ot = ObjectTypes::findById($object_type_id);

$extra_conditions = "";
Hook::fire('object_form_custom_prop_extra_conditions', array('ot_id' => $object_type_id, 'object' => $_custom_properties_object), $extra_conditions, true);

$cps = CustomProperties::getAllCustomPropertiesByObjectType($object_type_id, $visibility, $extra_conditions);
if ($visibility == 'others' && count($cps) == 0) {
	echo lang('there are no custom properties defined message', (lang("the ".$ot->getName()."s")));
	echo '<br />'. lang('there are no custom properties defined link');
}

if (!isset($genid)) $genid = gen_id();

if(count($cps) > 0){
	
	if (count($cps) > 10) {
		echo "<div class='cp-section cp-left'>";
	}
	$cp_idx = 0;
	
	$print_table_functions = false;
	foreach($cps as $customProp){
		if(!isset($required) || ($required && ($customProp->getIsRequired() || $customProp->getVisibleByDefault())) || (!$required && !($customProp->getIsRequired() || $customProp->getVisibleByDefault()))){
			
			$cpv = CustomPropertyValues::getCustomPropertyValue($_custom_properties_object->getId(), $customProp->getId());
			$default_value = $customProp->getDefaultValue();
			if($cpv instanceof CustomPropertyValue){
				$default_value = $cpv->getValue();
			}
			$name = 'object_custom_properties['.$customProp->getId().']';
			echo '<div style="margin-top:12px">';

			if ($customProp->getType() == 'boolean') {
			    
			    $options = array();
			    
			    $possible_values = array( 'yes' => 1, 'no' => -1);
			    
			    $options[] = option_tag("", "0");
			    foreach ($possible_values as $key => $value) {
			        
			        $opt_label = lang($key);
			        $option_attributes = $default_value == $value ? array('selected' => 'selected') : null;
			        $options[] = option_tag($opt_label, $value, $option_attributes);
			        
			    }
			    echo select_box($name, $options, $default_value, array('id' => $genid . 'cp' . $customProp->getId()));
			}
			    
			$label = clean($customProp->getName());
			if ($customProp->getIsSpecial()) {
				$label_code = str_replace("_special", "", $customProp->getCode());
				$label_value = Localization::instance()->lang($label_code);
				if (is_null($label_value)) {
					$label_value = Localization::instance()->lang(str_replace('_', ' ', $label_code));
				}
				if (!is_null($label_value)) $label = $label_value;
			}
			
			$add_style = "";
			echo label_tag($label, $genid . 'cp' . $customProp->getId(), $customProp->getIsRequired(), array('style' => 'display:inline;'.$add_style), $customProp->getType() == 'boolean'?'':':');
			
			echo '</div>';

			switch ($customProp->getType()) {
				case 'text':
				case 'numeric':
				case 'memo':
					if($customProp->getIsMultipleValues()){
						$numeric = ($customProp->getType() == "numeric");
						echo "<table><tr><td>";
						echo '<div id="listValues'.$customProp->getId().'" name="listValues'.$customProp->getId().'">';
						$isMemo = $customProp->getType() == 'memo';
						$count = 0;
						$fieldValues = CustomPropertyValues::getCustomPropertyValues($_custom_properties_object->getId(), $customProp->getId());
						if (!is_array($fieldValues) || count($fieldValues) == 0) {
							$def_cp_value = new CustomPropertyValue();
							$def_cp_value->setValue($default_value);
							$fieldValues = array($def_cp_value);
						}
						foreach($fieldValues as $value){
							$value = str_replace('|', ',', $value->getValue());
							if($value != ''){
								echo '<div id="value'.$count.'">';
								if($isMemo){
									echo textarea_field($name.'[]', $value, array('id' => $name.'[]'));
								}else{
									echo text_field($name.'[]', $value, array('id' => $name.'[]'));
								}
								echo '&nbsp;<a href="#" class="link-ico ico-delete" onclick="og.removeCPValue('.$customProp->getId().','.($count).','.($isMemo ? 1 : 0).')" ></a>';
								echo '</div>';
								$count++;
							}
						}
						echo '<div id="value'.$count.'">';
						if($customProp->getType() == 'memo'){
							echo textarea_field($name.'[]', '', array('id' => $name.'[]'));
						}else{
							echo text_field($name.'[]', '', array('id' => $name.'[]'));
						}
						echo '&nbsp;<a href="#" class="link-ico ico-add" onclick="og.addCPValue('.$customProp->getId().',\''.$isMemo.'\')">'.lang('add value').'</a><br/>';
						echo '</div>';
						echo '</div>';
						echo "</td></tr></table>";
						$include_script = true;
					} else {
						if($customProp->getType() == 'memo'){
							echo textarea_field($name, $default_value, array('class' => 'short', 'id' => $genid . 'cp' . $customProp->getId()));
						}else{
							echo text_field($name, $default_value, array('id' => $genid . 'cp' . $customProp->getId()));
						}
					}
					break;
				case 'boolean':
					break;
				case 'date':
				case 'datetime':
					// dates from table are saved as a string in "Y-m-d H:i:s" format
					if($customProp->getIsMultipleValues()){
						$name .= '[]';
						$count = 0;
						$fieldValues = CustomPropertyValues::getCustomPropertyValues($_custom_properties_object->getId(), $customProp->getId());
						if (!is_array($fieldValues) || count($fieldValues) == 0) {
							$def_cp_value = new CustomPropertyValue();
							$def_cp_value->setValue($default_value);
							$fieldValues = array($def_cp_value);
						}
						echo '<table id="table'.$genid.$customProp->getId().'">';
						$d_format = $customProp->getType() == 'datetime' ? "Y-m-d H:i:s" : "Y-m-d";
						foreach($fieldValues as $idx => $val){
							if (trim($val->getValue()) != '') {
								$value = DateTimeValueLib::dateFromFormatAndString($d_format, $val->getValue());
							} else {
								$value = "";
							}
							echo '<tr><td>';
							$d_name = str_replace('[]', '['.$idx.']', $name);
							
							echo pick_date_widget2($d_name, $value, null, null, null, $genid . 'cp' . $customProp->getId());
							if ($customProp->getType() == 'datetime') {
								$i_name = str_replace('['.$customProp->getId().'][]', '[time]['.$customProp->getId().']['.$idx.']', $name);
								echo '<div style="float:left;">'. pick_time_widget2($i_name, $value) . '</div><div class="clear"></div>';
							}
							echo '</td><td>';
							echo '<a href="#" class="link-ico ico-delete" onclick="og.removeCPDateValue(\''.$genid.'\','.$customProp->getId().','.$count.')"></a>';
							echo '</td></tr>';
							$count++;
						}
						echo '</table>';
						$use_time = $customProp->getType() == 'datetime' ? "1" : "0";
						echo '&nbsp;<a href="#" class="link-ico ico-add" onclick="og.addCPDateValue(\''.$genid.'\','.$customProp->getId().',0,'.$use_time.')">'.lang('add value').'</a><br/>';
					}else{
						if ($default_value != '') {
							try {
								$d_format = $customProp->getType() == 'datetime' ? "Y-m-d H:i:s" : "Y-m-d";
								$value = DateTimeValueLib::dateFromFormatAndString($d_format, $default_value);
							} catch (Exception $e) {
								try {
									$value = DateTimeValueLib::dateFromFormatAndString(user_config_option('date_format'), $default_value);
								} catch (Exception $e2) {
									Logger::log("Error when setting date custom property value:\n".$e2->getMessage()."\n\n".get_back_trace());
									$value = '';
								}
							}
						} else {
							$value = null;
						}
						echo pick_date_widget2($name, $value, null, null, null, $genid . 'cp' . $customProp->getId());
						if ($customProp->getType() == 'datetime') {
							$i_name = str_replace('['.$customProp->getId().']', '[time]['.$customProp->getId().']', $name);
							echo '<div style="float:left;">'. pick_time_widget2($i_name, $value) . '</div><div class="clear"></div>';
						}
					}
					break;
				case 'list':
					$options = array();
					if(!$customProp->getIsRequired() || ($customProp->getIsRequired() || $default_value == '')) {
						$options[] = '<option value=""></option>';
					}
					$totalOptions = 0;
					$multValues = CustomPropertyValues::getCustomPropertyValues($_custom_properties_object->getId(), $customProp->getId());
					$toSelect = array();
					foreach ($multValues as $m){
						$toSelect[] = $m->getValue();
					}
					
					if($customProp->getIsMultipleValues()) {
						echo '<div class="multiple-list-custom-property">';
					}
					
					foreach(explode(',', $customProp->getValues()) as $value){
						
						$text = null;
						if (strpos($value, '@') !== false) {
							$exp = explode('@', $value);
							$value = array_var($exp, 0);
							$text = array_var($exp, 1);
						}
						
                        if ($customProp->getIsSpecial()) {
                            $label_code = str_replace("_special", "", $customProp->getCode());
                            $text = Localization::instance()->lang($label_code." ".$value);
                        }

						if ($text == null) {
							$text = $value;
						}
						
						$selected = ($value == $default_value) || ($customProp->getIsMultipleValues() && (in_array($value, explode(',', $default_value)))||in_array($value,$toSelect));
						
						if($selected){
							$options[] = '<option value="'. clean($value) .'" selected>'. clean($text) .'</option>';
						}else{
							$options[] = option_tag($text, $value);
						}
						
						if($customProp->getIsMultipleValues()) {
							echo '<div class="cp-list-multiple-option-container">';
							echo checkbox_field($name."[$value]", $selected, array('id' => "$genid-$name-$totalOptions", 'class' => "cp-list-multiple-checkbox"));
							echo '<span class="cp-list-multiple-option" onclick="document.getElementById(\''."$genid-$name-$totalOptions".'\').click();">' . $text . '</span>';
							echo '</div>';
						}
						
						$totalOptions++;
					}
					
					if($customProp->getIsMultipleValues()) {
						echo '</div><div class="clear"></div>';
					}
					
					if(!$customProp->getIsMultipleValues()){
						echo select_box($name, $options, array('style' => 'min-width:140px', 'id' => $genid . 'cp' . $customProp->getId()));
					}
					
					break;
				case 'table':
					$columnNames = explode(',', $customProp->getValues());
					$cell_width = (600 / count($columnNames)) . "px";
					$html = '<div class="og-add-custom-properties"><table><tr>';
					foreach ($columnNames as $colName) {
						$html .= '<th style="width:'.$cell_width.';min-width:105px;">'.$colName.'</th>';
					}
					
					$html .= '<th style="width:20px;"></th></tr>';
					$values = CustomPropertyValues::getCustomPropertyValues($_custom_properties_object->getId(), $customProp->getId());
					if (trim($default_value) != '' && (!is_array($values) || count($values) == 0)) {
						$def_cp_value = new CustomPropertyValue();
						$def_cp_value->setValue($default_value);
						$values = array($def_cp_value);
					}
					$rows = 0;
					if (is_array($values) && count($values) > 0) {
						foreach ($values as $val) {
							$html .= '<tr>';
							$col = 0;
							$values = str_replace("\|", "%%_PIPE_%%", $val->getValue());
							$exploded = explode("|", $values);
							foreach ($exploded as $v) {
								$v = str_replace("%%_PIPE_%%", "|", $v);
								$html .= '<td><input class="value" style="width:'.$cell_width.';min-width:105px;" name="'.$name."[$rows][$col]". '" value="'. clean($v) .'" /></td>';
								$col++;
							}
							$html .= '<td><div class="ico ico-delete" style="width: 20px;height: 20px;cursor: pointer;margin-left: 2px;margin-top: 1px;" 
										onclick="og.removeTableCustomPropertyRow(this.parentNode.parentNode);return false;">&nbsp;</div></td>';
							$rows++;
							$html .= '</tr>';
						}
					}
					$html .= '</table>';
					
					$html .= '<a href="#" id="'.$genid.'-add-row-'.$customProp->getId().'" class="link-ico ico-add" onclick="og.addTableCustomPropertyRow(this.parentNode, true, null, '.count($columnNames).', null, '.$customProp->getId().');return false;">' . lang("add new row") . '</a></div>';
					$html .= '<div class="clear"></div>';
					
					if ($rows == 0) {
						// create first empty row
						$html .= '<script>if (!Ext.isIE) document.getElementById("'.$genid.'-add-row-'.$customProp->getId().'").onclick();</script>';
					}
					
					$print_table_functions = true;
					echo $html;
					break;


				case 'address':
						$html = '<div id="'.$genid.'addresscontainer-cp'.$customProp->getId().'" class="address-input-container custom-property"></div>';
						$html .= "<div style='display:none;'>" . select_country_widget('template_country', '', array('id'=>'template_select_country')) . "</div>";
						$html .= "<script>$(function(){";
							
						$all_address_types = AddressTypes::getAllAddressTypesInfo();
						$html .= "og.address_types = Ext.util.JSON.decode('". json_encode($all_address_types) ."');";
							
						$values = CustomPropertyValues::getCustomPropertyValues($_custom_properties_object->getId(), $customProp->getId());
						if (is_array($values) && count($values) > 0) {
							foreach ($values as $val) {
								$values = str_replace("\|", "%%_PIPE_%%", $val->getValue());
								$exploded = explode("|", $values);
								foreach ($exploded as &$v) {
									$v = str_replace("%%_PIPE_%%", "|", $v);
									$v = escape_character($v);
								}
								if (count($exploded) > 0) {
									$address_type = array_var($exploded, 0, '');
									$street = array_var($exploded, 1, '');
									$city = array_var($exploded, 2, '');
									$state = array_var($exploded, 3, '');
									$country = array_var($exploded, 4, '');
									$zip_code = array_var($exploded, 5, '');
									$sel_data_str = "{street:'$street', city:'$city', state:'$state', zip_code:'$zip_code', country:'$country'}";
									$html .= "og.renderAddressInput('cp".$customProp->getId()."', '$name', '".$genid."addresscontainer-cp".$customProp->getId()."', '$address_type', $sel_data_str);";
								} else {
									$html .= "og.renderAddressInput('cp".$customProp->getId()."', '$name', '".$genid."addresscontainer-cp".$customProp->getId()."', '', {});";
								}
							}
						} else {
							$html .= "og.renderAddressInput('cp".$customProp->getId()."', '$name', '".$genid."addresscontainer-cp".$customProp->getId()."', '', {});";
						}
						$html .= '});</script>';
						echo $html;
						
						break;

				case 'user':
				case 'contact':
					$filters = array();
					$value = '0';
					$cp_value = CustomPropertyValues::getCustomPropertyValue($_custom_properties_object->getId(), $customProp->getId());
					if ($cp_value instanceof CustomPropertyValue && is_numeric($cp_value->getValue())) {
						$value = $cp_value->getValue();
						$contact = Contacts::findById($value);
					}
					
					$emtpy_text = lang('select contact');
					
					Hook::fire('object_contact_cp_filters', array('cp' => $customProp, 'object' => $_custom_properties_object), $filters);
					
					if ($customProp->getType() == 'user') {
						$filters['is_user'] = 1;
						$emtpy_text = lang('select user');
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
					
					$html = '<div class="dataBlock" style="width:500px !important">';
					$html .= '<div id="'.$genid.'contacts_combo_container-cp'.$customProp->getId().'"></div><div class="clear"></div></div>';
					$html .= '<script>'.
					'$(function(){
					  og.renderContactSelector({
						genid: "'.$genid.'",
						id: "cp'.$customProp->getId().'",
						name: "'.$name.'",
						render_to: "contacts_combo_container-cp'.$customProp->getId().'",
						selected: '.$value.',
						selected_name: "'.($contact instanceof Contact ? clean($contact->getName()) : '').'",
						empty_text: "'. $emtpy_text .'",
						listClass: "custom-prop",
						listAlign: "tr-br",
						filters: '.$filters_str.'
					  });
					});
					</script>';
					echo $html;
					break;
					
				case 'image':
					$value = "";
					$cp_value = CustomPropertyValues::getCustomPropertyValue($_custom_properties_object->getId(), $customProp->getId());
					if ($cp_value instanceof CustomPropertyValue) {
						$value = $cp_value->getValue();
					}
					$config = array();
		            $config['name'] = $name;
		            $config['default_value'] = $value;
		            $config['genid'] = "";
		            $config['label'] = $label;
		            
					$html = render_image_custom_property_field($customProp, $config);
					echo $html;
					break;
					
				default: break;
			}
			
			if (!isset($value)) $value = "";
			$ret = null;
			Hook::fire('after_render_cp_input', array('custom_prop' => $customProp, 'value' => $value, 'input_name' => $name, 'object' => $_custom_properties_object), $ret);
			
			if ($customProp->getDescription() != ''){
				// the label is set to pad the description
				echo '<div><label>&nbsp;</label><span class="desc">' . clean($customProp->getDescription()) . '</span></div>';
			}
			
			echo '<div class="clear"></div>';
			
			$cp_idx++;
			if ($cp_idx == floor(count($cps)/2)) {
				echo "</div><div class='cp-section cp-right'>";
			}
		}
	}
	
	if (count($cps) > 10) {
		echo '</div><div class="clear"></div>';
	}
	
	Hook::fire('after_render_custom_properties', array('object' => $_custom_properties_object, 'genid' => $genid), $ret);
}

?></div>