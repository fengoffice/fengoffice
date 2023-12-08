<?php
	if (!isset($visibility)) $visibility = 'all';
    $object = $__properties_object;
    if (!isset($genid)) $genid = gen_id();
	
	$properties_html = null;
	$params =  array('object' => $object, 'visible_by_default' => $visibility != 'other');
    Hook::fire('override_render_properties_for_view', $params, $properties_html);
	
    //initializing cp_html
    $cp_html = '';
    if (!is_null($properties_html)) {
        $cp_html = $properties_html;
        
    } else {
	
	    if ($object instanceof ContentDataObject) {
			
	        $ot = ObjectTypes::instance()->findById($object->getObjectTypeId());
			$extra_conditions = "";
			if ($ot->getName() == 'contact'){
				if($object->isUser()){
					$extra_conditions .= " AND (contact_type LIKE 'user' OR contact_type LIKE 'all') ";
				} else {
					$extra_conditions .= " AND (contact_type LIKE 'contact' OR contact_type LIKE 'all') ";
				}
			}
	        $custom_properties = CustomProperties::getAllCustomPropertiesByObjectType($ot->getId(), $visibility, $extra_conditions);
	        
	        if (is_array($custom_properties) && count($custom_properties) > 0) {
		        if (!($visibility == 'all' || $visibility == 'visible_by_default')) {
	        		echo '<div class="commentsTitle">' . lang('other properties') . '</div>';
		        } 
	        }
			
            $tr_cls = "";
	        
	        foreach ($custom_properties as $cp) {
	            if ($cp->getType() == 'color' || $cp->getCode() == 'calculate the status automatically_special') {
	                continue;
	            }
	            $cp_name = $cp->getName();
	            $cp_values = CustomPropertyValues::getCustomPropertyValues($object->getId(), $cp->getId());
				
	            $cp_html_cp = "";
	            if (is_array($cp_values) && count($cp_values) > 0) {
	                $first = true;
	                $cp_html_cp .= '<tr class="cp-info '.$tr_cls.'"><td style="width:160px;"><span class="bold">'.$cp_name.': </span></td><td>';
	                
	                $cp_html_vals = get_custom_property_value_for_listing($cp, $object, $cp_values, false, array('table_html'=>true));
	                
	                $cp_html_cp .= $cp_html_vals;
	                $cp_html_cp .= '</td></tr>';
	                
	                if ($cp_html_vals != "") {
	                    $cp_html .= $cp_html_cp;
	                    $tr_cls = $tr_cls == "" ? "altRow" : "";
	                }
	            }
			}
			$cp_html_final = '';
			if(trim($cp_html) != "") {
				$cp_html_final .= '<table style="width:100%;" class="og-custom-properties main">' . $cp_html . '</table>';
			}
			$cp_html = $cp_html_final;
			
	    }
    }
		
	echo $cp_html;
