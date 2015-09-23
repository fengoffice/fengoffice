<?php

  /**
  * Render form label element
  *
  * @param void
  * @return null
  */
  function label_tag($text, $for = null, $is_required = false, $attributes = null, $after_label = ':') {
    if(trim($for)) {
      if(is_array($attributes)) {
        $attributes['for'] = trim($for);
      } else {
        $attributes = array('for' => trim($for));
      } // if
    } // if
    
    $render_text = trim($text) . $after_label;
    if($is_required) $render_text .= ' <span class="label_required">*</span>';
    
    return open_html_tag('label', $attributes) . $render_text . close_html_tag('label');
  } // form_label

  /**
  * Render input field
  *
  * @access public
  * @param string $name Field name
  * @param mixed $value Field value. Default is NULL
  * @param array $attributes Additional field attributes
  * @return null
  */
  function input_field($name, $value = null, $attributes = null) {
    $field_attributes = is_array($attributes) ? $attributes : array();
    
    $field_attributes['name'] = $name;
    $field_attributes['value'] = $value;
    
    return open_html_tag('input', $field_attributes, true);
  } // input_field
  
  /**
  * Render text field
  *
  * @access public
  * @param string $name
  * @param mixed $value
  * @param array $attributes Array of additional attributes
  * @return string
  */
  function text_field($name, $value = null, $attributes = null) {
    
    // If we don't have type attribute set it
    if(array_var($attributes, 'type', false) === false) {
      if(is_array($attributes)) {
        $attributes['type'] = 'text';
      } else {
        $attributes = array('type' => 'text');
      } // if
    } // if
    
    // And done!
    return input_field($name, $value, $attributes);
    
  } // text_field
  
  /**
  * Return password field
  *
  * @access public
  * @param string $name
  * @param mixed $value
  * @param array $attributes
  * @return string
  */
  function password_field($name, $value = null, $attributes = null) {
    
    // Set type to password
    if(is_array($attributes)) {
      $attributes['type'] = 'password';
    } else {
      $attributes = array('type' => 'password');
    } // if
    
    // Return text field
    return text_field($name, $value, $attributes);
    
  } // password_filed
  
  /**
  * Return file field
  *
  * @access public
  * @param string $name
  * @param mixed $value
  * @param array $attributes
  * @return string
  */
  function file_field($name, $value = null, $attributes = null) {
    
    // Set type to password
    if(is_array($attributes)) {
      $attributes['type'] = 'file';
    } else {
      $attributes = array('type' => 'file');
    } // if
    
    // Return text field
    return text_field($name, $value, $attributes);
    
  } // file_field
  
  /**
  * Render radio field
  *
  * @access public
  * @param string $name Field name
  * @param mixed $value
  * @param boolean $checked
  * @param array $attributes Additional attributes
  * @return string
  */
  function radio_field($name, $checked = false, $attributes = null) {
    
    // Prepare attributes array
    if(is_array($attributes)) {
      $attributes['type'] = 'radio';
      if(!isset($attributes['class'])) $attributes['class'] = 'checkbox';
    } else {
      $attributes = array('type' => 'radio', 'class' => 'checkbox');
    } // if
    
    // Value
    $value = array_var($attributes, 'value', false);
    if($value === false) $value = 'checked';
    
    // Checked
    if($checked) {
      $attributes['checked'] = 'checked';
    } else {
      if(isset($attributes['checked'])) unset($attributes['checked']);
    } // if
    
    // And done
    return input_field($name, $value, $attributes);
    
  } // radio_field
  
  /**
  * Render checkbox field
  *
  * @access public
  * @param string $name Field name
  * @param mixed $value
  * @param boolean $checked Checked?
  * @param array $attributes Additional attributes
  * @return string
  */
  function checkbox_field($name, $checked = false, $attributes = null) {
    
  	// Prepare attributes array
    if(is_array($attributes)) {
      $attributes['type'] = 'checkbox';
      if(!isset($attributes['class'])) $attributes['class'] = 'checkbox';
    } else {
      $attributes = array('type' => 'checkbox', 'class' => 'checkbox');
    } // if
    
    // Value
    $value = array_var($attributes, 'value', false);
    if($value === false) $value = 'checked';
    
    // Checked
    if($checked) {
      $attributes['checked'] = 'checked';
    } else {
      if(isset($attributes['checked'])) unset($attributes['checked']);
    } // if
    
    // And done
    return input_field($name, $value, $attributes);
    
  } // checkbox_field
  
  /**
  * This helper will render select list box. Options is array of already rendered option tags
  *
  * @access public
  * @param string $name
  * @param array $options Array of already rendered option tags
  * @param array $attributes Additional attributes
  * @return null
  */
  function select_box($name, $options, $attributes = null) {
    if(is_array($attributes)) {
      $attributes['name'] = $name;
    } else {
      $attributes = array('name' => $name);
    } // if
    
    $output = open_html_tag('select', $attributes) . "\n";
    if(is_array($options)) {
      foreach($options as $option) {
        $output .= $option . "\n";
      } // foreach
    } // if
    return $output . close_html_tag('select') . "\n";
  } // select_box
  
  /**
   * 
   * @param $name Control name
   * @param $options Array of array(value, text)
   * @param $selected Selected value string
   * @param $attributes
   * @return unknown_type
   */
  function simple_select_box($name, $options, $selected = null, $attributes = null) {
  	if(is_array($attributes)) {
      $attributes['name'] = $name;
    } else {
      $attributes = array('name' => $name);
    } // if
    
    $output = open_html_tag('select', $attributes) . "\n";
    if(is_array($options)) {
      foreach($options as $option) {
      	if ($selected == $option[0]) {
        	$output .= option_tag($option[1], $option[0], array('selected' => 'selected')) . "\n";
      	} else {
      		$output .= option_tag($option[1], $option[0]) . "\n";
      	}
      } // foreach
    } // if
    return $output . close_html_tag('select') . "\n";
  }

    /**
   * 
   * @param $name Control name
   * @param $options Array of array(value, text)
   * @param $selected Selected value string
   * @param $attributes
   * @return unknown_type
   */
  function multiple_select_box($name, $options, $selected = array(), $attributes = null) {
  	if(is_array($attributes)) {
      $attributes['name'] = $name;
      $attributes['multiple'] = 'multiple' ;
    } else {
      $attributes = array('name' => $name);
      $attributes = array('multiple' => 'multiple');
    }
    
    $output = open_html_tag('select', $attributes) . "\n";
    if(is_array($options)) {
      foreach($options as $option) {
      	if(!$option[0]) continue ;
      	if (in_array($option[0], $selected) ) {
        	$output .= option_tag($option[1], $option[0], array( 'selected' => 'selected')) . "\n";
      	} else {
      		$output .= option_tag($option[1], $option[0] ) . "\n";
      	}
      }
    }
    return $output . close_html_tag('select') . "\n";
  }
	/**
	 * @param unknown_type $table
	 * @param unknown_type $column
	 * @param unknown_type $name
	 * @param unknown_type $selected
	 * @param unknown_type $attributes
	 */
	function enum_select_box($manager, $column, $name, $selected = null, $attributes = null) {
		eval('$table = '.$manager.'::instance()->getTableName ();');
		if ($table && $column) {
			$values = get_enum_values ( $table, $column );
			$option_values = array (array("","--"));
			foreach ( $values as $value ) {
				if ($value)
					$option_values [] = array ($value, lang($value) );
			}
			return  simple_select_box ( $name, $option_values, $selected, $attributes );
		}
	}
	  
  
  /**
  * Render option tag
  *
  * @access public
  * @param string $text Option text
  * @param mixed $value Option value
  * @param array $attributes
  * @return string
  */
  function option_tag($text, $value = null, $attributes = null) {
    if(!($value === null)) {
      if(is_array($attributes)) {
        $attributes['value'] = $value;
      } else {
        $attributes = array('value' => $value);
      } // if
    } // if
    return open_html_tag('option', $attributes) . clean($text) . close_html_tag('option');
  } // option_tag
  
  /**
  * Render option group
  *
  * @param string $labe Group label
  * @param array $options
  * @param array $attributes
  * @return string
  */
  function option_group_tag($label, $options, $attributes = null) {
    if(is_array($attributes)) {
      $attributes['label'] = $label;
    } else {
      $attributes = array('label' => $label);
    } // if
    
    $output = open_html_tag('optgroup', $attributes) . "\n";
    if(is_array($options)) {
      foreach($options as $option) {
        $output .= $option . "\n";
      } // foreach
    } // if
    return $output . close_html_tag('optgroup') . "\n";
  } // option_group_tag

  /**
  * Render submit button
  *
  * @access public
  * @param string $this Button title
  * @param string $accesskey Accesskey. If NULL accesskey will be skipped
  * @param array $attributes Array of additinal attributes
  * @return string
  */
  function submit_button($title, $accesskey = 's', $attributes = null) {
    if(!is_array($attributes)) {
      $attributes = array();
    } // if
    $attributes['class'] = 'submit '.array_var($attributes, 'class', '');
    $attributes['type'] = 'submit';
    $attributes['accesskey'] = $accesskey;
    
    if($accesskey) {
      if(strpos($title, $accesskey) !== false) {
        $title = str_replace_first($accesskey, "<u>$accesskey</u>", $title);
      } // if
    } // if
    
    return open_html_tag('button', $attributes) . $title . close_html_tag('button');
  } // submit_button
  
  /**
  * Render button
  *
  * @access public
  * @param string $this Button title
  * @param string $accesskey Accesskey. If NULL accesskey will be skipped
  * @param array $attributes Array of additinal attributes
  * @return string
  */
  function button($title, $accesskey = 's', $attributes = null) {
    if(!is_array($attributes)) {
      $attributes = array();
    } // if
    $more_classes = array_var($attributes, 'class');
    $attributes['class'] = array_var($attributes, 'class', 'submit');
    $attributes['type'] = 'button';
    $attributes['accesskey'] = $accesskey;
    
    if (trim($more_classes) != '') $attributes['class'] .= " $more_classes";
    
    if($accesskey) {
      if(strpos($title, $accesskey) !== false) {
        $title = str_replace_first($accesskey, "<u>$accesskey</u>", $title);
      } // if
    } // if
    
    return open_html_tag('button', $attributes) . $title . close_html_tag('button');
  } // submit_button
  
  /**
  * Return textarea tag
  *
  * @access public
  * @param string $name
  * @param string $value
  * @param array $attributes Array of additional attributes
  * @return string
  */
  function textarea_field($name, $value, $attributes = null) {
    if(!is_array($attributes)) {
      $attributes = array();
    } // if
    $attributes['name'] = $name;
    if(!isset($attributes['rows']) || trim($attributes['rows'] == '')) {
      $attributes['rows'] = '10'; // required attribute
    } // if
    if(!isset($attributes['cols']) || trim($attributes['cols'] == '')) {
      $attributes['cols'] = '40'; // required attribute
    } // if
    
    return open_html_tag('textarea', $attributes) . clean($value) . close_html_tag('textarea');
  } // textarea
  
  // ---------------------------------------------------
  //  Widgets
  // ---------------------------------------------------
  
  /**
  * Return date time picker widget
  *
  * @access public
  * @param string $name Field name
  * @param string $value Date time value
  * @return string
  */
  function pick_datetime_widget($name, $value = null) {
    return text_field($name, $value);
  } // pick_datetime_widget
    
  /**
  * Return pick date widget
  *
  * @access public
  * @param string $name Name prefix
  * @param DateTimeValue $value Can be DateTimeValue object, integer or string
  * @param integer $year_from Start counting from this year. If NULL this value will be set
  *   to current year - 10
  * @param integer $year_to Count to this year. If NULL this value will be set to current
  * @deprecated
  *   year + 10
  * @return null
  */
  function pick_date_widget($name, $value = null, $year_from = null, $year_to = null, $attributes = null, $id = null) {
  	require_javascript("og/DateField.js");
  	$oldValue = $value;
    if(!($value instanceof DateTimeValue)) $value = new DateTimeValue($value);
    
    $month_options = array();
    for($i = 1; $i <= 12; $i++) {
      $option_attributes = $i == $value->getMonth() ? array('selected' => 'selected') : null;
      $month_options[] = option_tag(lang("month $i"), $i, $option_attributes);
    } // for
    
    $day_options = array();
    for($i = 1; $i <= 31; $i++) {
      $option_attributes = $i == $value->getDay() ? array('selected' => 'selected') : null;
      $day_options[] = option_tag($i, $i, $option_attributes);
    } // for
    
    $year_from = (integer) $year_from < 1 ? $value->getYear() - 10 : (integer) $year_from;
    $year_to = (integer) $year_to < 1 || ((integer) $year_to < $year_from) ? $value->getYear() + 10 : (integer) $year_to;
    
    $year_options = array();
    
    if ($year_from <= 1902)
    {
    	$option_attributes = is_null($oldValue) ? array('selected' => 'selected') : null;
    	$year_options[] = option_tag(lang('select'), 0, $option_attributes);
    }
    
    for($i = $year_from; $i <= $year_to; $i++) {
      $option_attributes = ($i == $value->getYear() && !is_null($oldValue)) ? array('selected' => 'selected') : null;
      $year_options[] = option_tag($i, $i, $option_attributes);
    } // if
    $attM = $attributes;
    $attY = $attributes;
    $attD = $attributes;
    if ($attM['id']) {
    	$attM['id'] .= '_month';
    }
    if ($attY['id']) {
    	$attY['id'] .= '_year';
    }
    if ($attD['id']) {
    	$attD['id'] .= '_day';
    }
    if (strpos($name, "]")) {
    	$preName = substr_utf($name,0,strpos_utf($name,"]"));
    	return select_box($preName . '_month]', $month_options, $attM) . select_box($preName.'_day]', $day_options, $attD) . select_box($preName . '_year]', $year_options, $attY);
    } else
    	return select_box($name . '_month', $month_options, $attM) . select_box($name . '_day', $day_options, $attD) . select_box($name . '_year', $year_options, $attY );
  } // pick_date_widget
  
  function pick_date_widget2($name, $value = null, $genid = null, $tabindex = null, $display_date_info = true, $id = null) {
  	require_javascript('og/DateField.js');
  	
  	$date_format = user_config_option('date_format');
  	if ($genid == null) $genid = gen_id();
  	$dateValue = '';
  	if ($value instanceOf DateTimeValue){
  		$dateValue = $value->format($date_format);
  	}
  	if (!$id) $id = $genid . $name . "Cmp";
  	$daterow = '';
 // 	if ($display_date_info)
 // 		$daterow = "<td style='padding-top:4px;font-size:80%'><span class='desc'>&nbsp;(" . date_format_tip($date_format) . ")</span></td>";
  	$html = "<table class='date-picker'><tr><td><span id='" . $genid . $name . "'></span></td>$daterow</tr></table>
	<script>
		var dtp" . gen_id() . " = new og.DateField({
			renderTo:'" . $genid . $name . "',
			name: '" . $name . "',
			emptyText: '" . date_format_tip($date_format) . "',
			id: '" . $id . "',".
			(isset($tabindex) ? "tabIndex: '$tabindex'," : "").
			"value: '" . $dateValue . "'});
	</script>
	";
  	
	return $html;
  } // pick_date_widget
  
  /**
  * Return pick time widget
  *
  * @access public
  * @param string $name
  * @param string $value
  * @return string
  */
  function pick_time_widget($name, $value = null) {
    return text_field($name, $value);
  } // pick_time_widget
  
  function pick_time_widget2($name, $value = null, $genid = null, $tabindex = null, $format = null, $id = null) {
  	if ($format == null) $format = (user_config_option('time_format_use_24') ? 'G:i' : 'g:i A');
  	if ($value instanceof DateTimeValue) {
  		$value = $value->format($format);
  	}
  	if (!$id) $id = $genid . $name . "Cmp";
  	$html = "<table class='time-picker'><tr><td><div id='" . $genid . $name . "'></div></td></tr></table>
	<script>
		var tp" . gen_id() . " = new Ext.form.TimeField({
			renderTo:'" . $genid . $name . "',
			name: '" . $name . "',
			format: '" . $format . "',
			emptyText: 'hh:mm',
                        id: '" . $id . "',
			width: 80,".
			(isset($tabindex) ? "tabIndex: '$tabindex'," : "").
			"value: '" . $value . "'});
	</script>
	";
  	return $html;
  }
  /**
  * Return WYSIWYG editor widget
  *
  * @access public
  * @param string $name
  * @param string $value
  * @return string
  */
  function editor_widget($name, $value = null, $attributes = null) {
    $editor_attributes = is_array($attributes) ? $attributes : array();
    if(!isset($editor_attributes['class'])) $editor_attributes['class'] = 'editor';
    return textarea_field($name, $value, $editor_attributes);
  } // editor_widget
  
  /**
  * Render yes no widget
  *
  * @access public
  * @param string $name
  * @param $id_base
  * @param boolean $value If true YES will be selected, otherwise NO will be selected
  * @param string $yes_lang
  * @param string $no_lang
  * @return null
  */
  function yes_no_widget($name, $id_base, $value, $yes_lang, $no_lang, $tabindex = null, $attributes = null) {
  	$yes_attributes = array('id' => $id_base . 'Yes', 'class' => 'yes_no', 'value' => 1);
  	$no_attributes = array('id' => $id_base . 'No', 'class' => 'yes_no', 'value' => 0);
  	if ($tabindex != null) {
  		$yes_attributes['tabindex'] = $tabindex;
  		$no_attributes['tabindex'] = $tabindex;
  	}
  	if (is_array($attributes)) {
  		foreach ($attributes as $attr_name => $attr_value) {
  			$yes_attributes[$attr_name] = $attr_value;
  			$no_attributes[$attr_name] = $attr_value;
  		}
  	}
  	
    $yes_input = radio_field($name, $value, $yes_attributes);
    $no_input = radio_field($name, !$value, $no_attributes);
    $yes_label = label_tag($yes_lang, $id_base . 'Yes', false, array('class' => 'yes_no'), '');
    $no_label = label_tag($no_lang, $id_base . 'No', false, array('class' => 'yes_no'), '');
    
    return $yes_input . ' ' . $yes_label . ' ' . $no_input . ' ' . $no_label;
  } // yes_no_widget
  
  /**
  * Show select country box
  *
  * @access public
  * @param string $name Control name
  * @param string $value Country code of selected country
  * @param array $attributes Array of additional select box attributes
  * @return string
  */
  function select_country_widget($name, $value, $attributes = null) {
    $country_codes = array_keys(CountryCodes::getAll());
    $countries = array();
    foreach($country_codes as $code) {
      if (Localization::instance()->lang_exists("country $code")) {
        $countries[$code] = lang("country $code");
      } else {
        $countries[$code] = CountryCodes::getCountryNameByCode($code);
      }
    } // foreach
    
    asort($countries);
    
    $attributes['class'] = array_var($attributes, 'class') . " country-selector";
    
    $country_options = array(option_tag(lang('click to select country'), ''));
    foreach($countries as $country_code => $country_name) {
      $option_attributes = $country_code == $value ? array('selected' => true) : null;
      $country_options[] = option_tag($country_name, $country_code, $option_attributes);
    } // foreach
    
    return select_box($name, $country_options, $attributes);
  } // select_country_widget
  
  /**
  * Render select timezone widget
  *
  * @param string $name Name of the select box
  * @param float $value Timezone value. If NULL GMT will be selected
  * @param array $attributes Array of additional attributes
  * @return string
  */
  function select_timezone_widget($name, $value = null, $attributes = null) {
    $selected_value = (float) $value;
    $all_timezones = Timezones::getTimezones();
    
    $options = array();
    foreach($all_timezones as $timezone) {
      $option_attributes = $selected_value == $timezone ? array('selected' => true) : null;
      $option_text = $timezone > 0 ? lang("timezone gmt +$timezone") : lang("timezone gmt $timezone");
      $options[] = option_tag($option_text, $timezone, $option_attributes);
    } // if
    
    return select_box($name, $options, $attributes);
  } // select_timezone_widget
  
  function number_field($name, $value = null, $attributes = null) {
  	//if (!is_numeric($value)) $value = 0;
  	return text_field($name, $value, array("maxlength" => 9, "style"=> "width:100px", "onkeyup" => "event.target.value = event.target.value.replace(/[^0-9]/g, '')"));
  }
  
  /**
   * Takes an html color and returns it a $percentage % darker
   *
   * @param string $htmlColor
   * @param integer $percentage
   * @return string $darkerColor
   */
  function darkerHtmlColor($htmlColor, $percentage = 20) {
  	if (substr($htmlColor, 0, 1) == '#') {
        $htmlColor = substr($htmlColor, 1);
    }
    if (strlen($htmlColor) != 6) {
        return "#$htmlColor";
    }
    $darkerColor = '';
    $pieces = explode(' ', rtrim(chunk_split($htmlColor, 2, ' ')));
    foreach ($pieces as $piece) {
        # convert from base16 to base10, reduce the value then come back to base16
        $tmp = (int) (base_convert($piece, 16, 10));
        $amount = (int) ($tmp * $percentage / 100);
        $darkpiece = $tmp - $amount;
        if ($darkpiece < 0) $darkpiece = 0;
        if ($darkpiece > 255) $darkpiece = 255;
        $darkerColor .= sprintf("%02x", $darkpiece);
    }
    return '#'. $darkerColor;
  } // darkerHtmlColor

  function doubleListSelect($name, $values, $attributes = null) {
  	if (is_array($attributes)) {
		if (!array_var($attributes, "size")) $attributes['size'] = "15";
	} else {
		$attributes = array("size" => 15);
	}
	if (!array_var($attributes, "class")) $attributes['class'] = "og-double-list-sel";
	
	$id = array_var($attributes, 'id');
	if (!$id) $id = "list_values";
	 
	$options1 = array();
	$options2 = array();
	$hfields = "";
	$order = 1;
	foreach ($values as $val) {
		$sel = array_var($val, 'selected');
		if (!$sel)
			$options1[] = option_tag(array_var($val, 'text'), array_var($val, 'id'));
		else
			$options2[] = option_tag(array_var($val, 'text'), array_var($val, 'id'));
		
		$hfields .= '<input id="'.$id.'['.array_var($val, 'id').']" name="'.$name.'['.array_var($val, 'id').']" type="hidden" value="'.($sel ? $order++ : '0').'" />'; 
	}
	
	// 1st box
	$attributes['id'] = $id . "_box1";
	$html = "<table><tr><td>" . select_box($name."_box1", $options1, $attributes) . "</td>";
	
	// buttons
	$btn_style = 'border:1px solid #bbb; width:35px; margin:2px;';
	$html .= "<td align='center' class='og-double-list-sel-btns'>";
	$html .= "<div style='margin: 5px 10px;' title='".lang('move all to right')."'><a href='#' class='ico-2arrowright' style='padding: 0 0 0 12px;' onclick=\"og.doubleListSelCtrl.selectAll('$id')\">&nbsp;</a></div>";
	$html .= "<div style='margin: 5px 10px 15px;' title='".lang('move to right')."'><a href='#' class='ico-arrowright' style='padding: 0 0 0 12px;' onclick=\"og.doubleListSelCtrl.selectOne('$id')\">&nbsp;</a></div>";
	$html .= "<div style='margin: 15px 10px 5px;' title='".lang('move to left')."'><a href='#' class='ico-arrowleft' style='padding: 0 0 0 12px;' onclick=\"og.doubleListSelCtrl.deselectOne('$id')\">&nbsp;</a></div>";
	$html .= "<div style='margin: 5px 10px;' title='".lang('move all to left')."'><a href='#' class='ico-2arrowleft' style='padding: 0 0 0 12px;' onclick=\"og.doubleListSelCtrl.deselectAll('$id')\">&nbsp;</a></div>";
	$html .= "</td>";
	
	// 2nd box
	$attributes['id'] = $id . "_box2";
	$html .= "<td>" . select_box($name."_box2", $options2, $attributes) . "</td>";
	
	$html .= "<td>";
	$html .= "<div style='margin: 2px;' title='".lang('move up')."'><a href='#' class='ico-arrowup' style='padding: 0 0 0 12px;' onclick=\"og.doubleListSelCtrl.moveUp('$id', '_box2')\">&nbsp;</a></div>";
	$html .= "<div style='margin: 2px;' title='".lang('move down')."'><a href='#' class='ico-arrowdown' style='padding: 0 0 0 12px;' onclick=\"og.doubleListSelCtrl.moveDown('$id', '_box2')\">&nbsp;</a></div>";
	$html .= "</td></tr></table>";
	
	// hidden fields containing the selection
	$html .= $hfields;
	return $html;
  }

  
  function taskPercentCompletedBar(ProjectTask $task, $options = array()) {
 	
  	$color_cls = 'task-percent-completed-';
	if ( $task->getPercentCompleted() < 25) $color_cls .= '0';
	else if ( $task->getPercentCompleted() < 50) $color_cls .= '25';
	else if ( $task->getPercentCompleted() < 75) $color_cls .= '50';
	else if ( $task->getPercentCompleted() < 100) $color_cls .= '75';
        else if ( $task->getPercentCompleted() == 100) $color_cls .= '100';
	else $color_cls .= 'more-estimate';
        
        $percent_complete = 100;
        if($task->getPercentCompleted() <= 100){
            $percent_complete = $task->getPercentCompleted();
        }
	
	$bar_width = array_var($options, 'bar_width', '100px');
  	$bar_height = array_var($options, 'bar_height', '13px');
  	$font_size = array_var($options, 'font_size', '11px');
  	$padding_top = array_var($options, 'padding_top', '1px');
  	
  	$html = "<table style='display:inline;'><tr><td style='padding-left:15px;padding-top:$padding_top'>" .
			"<table style='height:$bar_height;width:$bar_width'><tr><td style='height:$bar_height;width:" . $percent_complete . "%;' class='$color_cls'></td><td style='width:" . (100 - $task->getPercentCompleted()) . "%;background-color:#DDD'></td></tr></table>" .
			"</td><td style='padding-left:3px;line-height:12px'><span style='font-size:$font_size;color:#333'>" . $percent_complete . "%</span></td></tr></table>";
  	
  	return $html;
  }
  
  
  function mark_dao_validation_error_fields($e) {
  	if ($e instanceof DAOValidationError) {
  		$obj = $e->getObject();
  		if (is_array($obj->getFieldsWithErrorsAfterValidation())) {
  			foreach ($obj->getFieldsWithErrorsAfterValidation() as $f) {
  				evt_add('mark_error_field', array('field' => $f));
  			}
  		}
  	}
  }
  
  
  function checkAddressInputMandatoryFields($address_info, $field_name, &$error_msgs) {
  	if ($error_msgs == null) $error_msgs = array();
  	if (!$field_name) $field_name = lang('address');
  	$ok = true;
  	
  	if (is_array($address_info) && count($address_info)) {
	  	$mandatory_fields = config_option('mandatory_address_fields');
	  	foreach ($mandatory_fields as $mfield) {
	  		if (!array_var($address_info, $mfield)) {
	  			$ok = false;
	  			$error_msgs[] = lang('address field is required', $field_name, lang($mfield));
	  		}
	  	}
  	}
  	
  	return $ok;
  }
  
  /**
   * Renders pagination for view history lists
   * @param $base_url: view history url of the object
   * @param $pagination_obj: pagination information
   * @param $page_param_prefix: prefix for the "page" parameter (view or mod)
   * @param $other_list_current_page: current page of the other list, if viewing modification list this param must contain the current read logs page and viceversa
   */
  function render_view_history_pagination($base_url, $pagination_obj, $page_param_prefix, $other_list_current_page) {
  	$current_page = array_var($pagination_obj, 'current_page');
  	$total_pages = array_var($pagination_obj, 'total_pages');
  
  	$add_param = "&curtab=$page_param_prefix";
  	if ($page_param_prefix == 'mod') {
  		$add_param .= '&view_page='.$other_list_current_page;
  	}
  	if ($page_param_prefix == 'view') {
  		$add_param .= '&mod_page='.$other_list_current_page;
  	}
  	$base_url .= $add_param;
  
  	if ($current_page > 1) {
  		echo '<a class="internalLink pagitem x-tbar-page-first" href="'. $base_url . '&'.$page_param_prefix.'_page=1' .'">&nbsp;</a>';
  		echo '<a class="internalLink pagitem x-tbar-page-prev" href="'. $base_url . '&'.$page_param_prefix.'_page='. ($current_page - 1) .'">&nbsp;</a>';
  	}
  
  	echo '<span class="pagitem text">'. lang("page") . " $current_page " . strtolower(lang('of')) . " $total_pages" . '</span>';
  
  	if ($current_page < $total_pages) {
  		echo '<a class="internalLink pagitem x-tbar-page-next" href="'. $base_url . '&'.$page_param_prefix.'_page='. ($current_page + 1) .'">&nbsp;</a>';
  		echo '<a class="internalLink pagitem x-tbar-page-last" href="'. $base_url . '&'.$page_param_prefix.'_page='. $total_pages .'">&nbsp;</a>';
  	}
  }
  
  
  
  function build_percent_completed_bar_html($task) {
  	$color_cls = 'task-percent-completed-';
  	 
  	if (array_var($task, 'percentCompleted') < 25) $color_cls .= '0';
  	else if (array_var($task, 'percentCompleted') < 50) $color_cls .= '25';
  	else if (array_var($task, 'percentCompleted') < 75) $color_cls .= '50';
  	else if (array_var($task, 'percentCompleted') < 100) $color_cls .= '75';
  	else if (array_var($task, 'percentCompleted') == 100) $color_cls .= '100';
  	else $color_cls .= 'more-estimate';
  	 
  	$percent_complete = 100;
  	if(array_var($task, 'percentCompleted') <= 100){
  		$percent_complete = array_var($task, 'percentCompleted');
  	}
  	
  	$html = "<span><span class='nobr'><table style='display:inline;'><tr><td style='padding-left:15px;padding-top:6px'>".
  			"<table style='height:7px;width:50px'><tr><td style='height:7px;width:" . $percent_complete . "%;' class='". $color_cls ."'></td><td style='width:" . (100 - $percent_complete) . "%;background-color:#DDD'></td></tr></table>" .
  			"</td><td style='padding-left:3px;line-height:12px'><span class='percent_num' style='font-size:8px;color:#777'>" . $percent_complete . "%</span></td></tr></table></span></span>";
  	 
  	return $html;
  }