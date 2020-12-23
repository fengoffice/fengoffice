<?php

/**
 * Render form label element
 *
 * @param void
 * @return null
 */
function label_tag($text, $for = null, $is_required = false, $attributes = null, $after_label = ':') {
    if (trim($for)) {
        if (is_array($attributes)) {
            $attributes['for'] = trim($for);
        } else {
            $attributes = array('for' => trim($for));
        } // if
    } // if

    $render_text = trim($text) . $after_label;
    if ($is_required)
        $render_text .= ' <span class="label_required">*</span>';

    return open_html_tag('label', $attributes) . $render_text . close_html_tag('label');
}

// form_label

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
}

// input_field

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
    if (array_var($attributes, 'type', false) === false) {
        if (is_array($attributes)) {
            if(array_var($attributes, 'type') != 'hidden'){
                $attributes['type'] = 'text';
            }
        } else {
            $attributes = array('type' => 'text');
        } // if
    } // if
    // And done!
    return input_field($name, $value, $attributes);
}

// text_field

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
    if (is_array($attributes)) {
        $attributes['type'] = 'password';
    } else {
        $attributes = array('type' => 'password');
    } // if
    // Return text field
    return text_field($name, $value, $attributes);
}

// password_filed

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
    if (is_array($attributes)) {
        $attributes['type'] = 'file';
    } else {
        $attributes = array('type' => 'file');
    } // if
    // Return text field
    return text_field($name, $value, $attributes);
}

// file_field

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
    if (is_array($attributes)) {
        $attributes['type'] = 'radio';
        if (!isset($attributes['class']))
            $attributes['class'] = 'checkbox';
    } else {
        $attributes = array('type' => 'radio', 'class' => 'checkbox');
    } // if
    // Value
    $value = array_var($attributes, 'value', false);
    if ($value === false)
        $value = 'checked';

    // Checked
    if ($checked) {
        $attributes['checked'] = 'checked';
    } else {
        if (isset($attributes['checked']))
            unset($attributes['checked']);
    } // if
    // And done
    return input_field($name, $value, $attributes);
}

// radio_field

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
    if (is_array($attributes)) {
        $attributes['type'] = 'checkbox';
        if (!isset($attributes['class']))
            $attributes['class'] = 'checkbox';
    } else {
        $attributes = array('type' => 'checkbox', 'class' => 'checkbox');
    } // if
    // Value
    $value = array_var($attributes, 'value', false);
    if ($value === false)
        $value = 'checked';

    // Checked
    if ($checked) {
        $attributes['checked'] = 'checked';
    } else {
        if (isset($attributes['checked']))
            unset($attributes['checked']);
    } // if
    // And done
    return input_field($name, $value, $attributes);
}

// checkbox_field

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
    if (is_array($attributes)) {
        $attributes['name'] = $name;
    } else {
        $attributes = array('name' => $name);
    } // if

    $output = open_html_tag('select', $attributes) . "\n";
    if (is_array($options)) {
        foreach ($options as $option) {
            $output .= $option . "\n";
        } // foreach
    } // if
    return $output . close_html_tag('select') . "\n";
}

// select_box

/**
 * 
 * @param $name string Control name
 * @param $options Array of array(value, text)
 * @param $selected mixed Selected value string
 * @param $attributes
 * @return string The generated html for the component
 */
function simple_select_box($name, $options, $selected = null, $attributes = null) {
    if (is_array($attributes)) {
        $attributes['name'] = $name;
    } else {
        $attributes = array('name' => $name);
    } // if

    $output = open_html_tag('select', $attributes) . "\n";
    if (is_array($options)) {
        foreach ($options as $option) {
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
 * @param $name string Control name
 * @param $options Array of array(value, text)
 * @param $selected mixed Selected value string
 * @param $attributes
 * @return string The generated html for the component
 */
function multiple_select_box($name, $options, $selected = array(), $attributes = null) {
    if (is_array($attributes)) {
        $attributes['name'] = $name;
        $attributes['multiple'] = 'multiple';
    } else {
        $attributes = array('name' => $name);
        $attributes = array('multiple' => 'multiple');
    }

    $output = open_html_tag('select', $attributes) . "\n";
    if (is_array($options)) {
        foreach ($options as $option) {
            if (!$option[0])
                continue;
            if (in_array($option[0], $selected)) {
                $output .= option_tag($option[1], $option[0], array('selected' => 'selected')) . "\n";
            } else {
                $output .= option_tag($option[1], $option[0]) . "\n";
            }
        }
    }
    return $output . close_html_tag('select') . "\n";
}

/**
 * @param string $table
 * @param string $column
 * @param string $name
 * @param mixed $selected
 * @param array $attributes
 */
function enum_select_box($manager, $column, $name, $selected = null, $attributes = null) {
    eval('$table = ' . $manager . '::instance()->getTableName ();');
    if ($table && $column) {
        $values = get_enum_values($table, $column);
        $option_values = array(array("", "--"));
        foreach ($values as $value) {
            if ($value)
                $option_values [] = array($value, lang($value));
        }
        return simple_select_box($name, $option_values, $selected, $attributes);
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
    if (!($value === null)) {
        if (is_array($attributes)) {
            $attributes['value'] = $value;
        } else {
            $attributes = array('value' => $value);
        } // if
    } // if
    return open_html_tag('option', $attributes) . clean($text) . close_html_tag('option');
}

// option_tag

/**
 * Render option group
 *
 * @param string $labe Group label
 * @param array $options
 * @param array $attributes
 * @return string
 */
function option_group_tag($label, $options, $attributes = null) {
    if (is_array($attributes)) {
        $attributes['label'] = $label;
    } else {
        $attributes = array('label' => $label);
    } // if

    $output = open_html_tag('optgroup', $attributes) . "\n";
    if (is_array($options)) {
        foreach ($options as $option) {
            $output .= $option . "\n";
        } // foreach
    } // if
    return $output . close_html_tag('optgroup') . "\n";
}

// option_group_tag

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
    if (!is_array($attributes)) {
        $attributes = array();
    } // if
    $attributes['class'] = 'submit ' . array_var($attributes, 'class', '');
    $attributes['type'] = 'submit';
    $attributes['accesskey'] = $accesskey;

    if ($accesskey) {
        if (strpos($title, $accesskey) !== false) {
            $title = str_replace_first($accesskey, "<u>$accesskey</u>", $title);
        } // if
    } // if

    return open_html_tag('button', $attributes) . $title . close_html_tag('button');
}

// submit_button

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
    if (!is_array($attributes)) {
        $attributes = array();
    } // if
    $more_classes = array_var($attributes, 'class');
    $attributes['class'] = array_var($attributes, 'class', 'submit');
    $attributes['type'] = 'button';
    $attributes['accesskey'] = $accesskey;

    if (trim($more_classes) != '')
        $attributes['class'] .= " $more_classes";

    if ($accesskey) {
        if (strpos($title, $accesskey) !== false) {
            $title = str_replace_first($accesskey, "<u>$accesskey</u>", $title);
        } // if
    } // if

    return open_html_tag('button', $attributes) . $title . close_html_tag('button');
}

// submit_button

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
    if (!is_array($attributes)) {
        $attributes = array();
    } // if
    $attributes['name'] = $name;
    if (!isset($attributes['rows']) || trim($attributes['rows'] == '')) {
        $attributes['rows'] = '10'; // required attribute
    } // if
    if (!isset($attributes['cols']) || trim($attributes['cols'] == '')) {
        $attributes['cols'] = '40'; // required attribute
    } // if

    return open_html_tag('textarea', $attributes) . clean($value) . close_html_tag('textarea');
}

// textarea
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
}

// pick_datetime_widget

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
    if (!($value instanceof DateTimeValue))
        $value = new DateTimeValue($value);

    $month_options = array();
    for ($i = 1; $i <= 12; $i++) {
        $option_attributes = $i == $value->getMonth() ? array('selected' => 'selected') : null;
        $month_options[] = option_tag(lang("month $i"), $i, $option_attributes);
    } // for

    $day_options = array();
    for ($i = 1; $i <= 31; $i++) {
        $option_attributes = $i == $value->getDay() ? array('selected' => 'selected') : null;
        $day_options[] = option_tag($i, $i, $option_attributes);
    } // for

    $year_from = (integer) $year_from < 1 ? $value->getYear() - 10 : (integer) $year_from;
    $year_to = (integer) $year_to < 1 || ((integer) $year_to < $year_from) ? $value->getYear() + 10 : (integer) $year_to;

    $year_options = array();

    if ($year_from <= 1902) {
        $option_attributes = is_null($oldValue) ? array('selected' => 'selected') : null;
        $year_options[] = option_tag(lang('select'), 0, $option_attributes);
    }

    for ($i = $year_from; $i <= $year_to; $i++) {
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
        $preName = substr_utf($name, 0, strpos_utf($name, "]"));
        return select_box($preName . '_month]', $month_options, $attM) . select_box($preName . '_day]', $day_options, $attD) . select_box($preName . '_year]', $year_options, $attY);
    } else
        return select_box($name . '_month', $month_options, $attM) . select_box($name . '_day', $day_options, $attD) . select_box($name . '_year', $year_options, $attY);
}

// pick_date_widget

function pick_date_widget2($name, $value = null, $genid = null, $tabindex = null, $display_date_info = true, $id = null, $listeners = array(), $disabled = false) {
    require_javascript('og/DateField.js');

    $date_format = user_config_option('date_format');
    if ($genid == null)
        $genid = gen_id();
    $dateValue = '';
    if ($value instanceOf DateTimeValue) {
        $dateValue = $value->format($date_format);
    }
    if (!$id)
        $id = $genid . $name . "Cmp";
    $daterow = '';

    $listeners_str = "";
    if (is_array($listeners) && count($listeners) > 0) {
    	$listeners_str = "
    		listeners: {";
    	$i=0;
    	foreach ($listeners as $ev => $fn) {
    		$i++;
    		$listener_config = "'$ev' : $fn" . ($i < count($listeners) ? "," : "");
    		$listeners_str .= "
    			$listener_config";
    	}
    	$listeners_str .= "
    		},
    		";
    }
    $disabled_str = '';
    if ($disabled) $disabled_str = 'disabled: true,';
    
    // 	if ($display_date_info)
    // 		$daterow = "<td style='padding-top:4px;font-size:80%'><span class='desc'>&nbsp;(" . date_format_tip($date_format) . ")</span></td>";
    $html = "<table class='date-picker'><tr><td><span id='" . $genid . $name . "'></span></td>$daterow</tr></table>
	<script>
		var dtp" . gen_id() . " = new og.DateField({
			renderTo:'" . $genid . $name . "',
			name: '" . $name . "',
			emptyText: '" . date_format_tip($date_format) . "',
			id: '" . $id . "'," .
            (isset($tabindex) && !is_null($tabindex) ? "tabIndex: '$tabindex'," : "") .
            $listeners_str .
            $disabled_str .
            "value: '" . $dateValue . "'
		});
	</script>
	";
    return $html;
}


// pick_date_widget

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
}

// pick_time_widget

function pick_time_widget2($name, $value = null, $genid = null, $tabindex = null, $format = null, $id = null, $listeners = null, $disabled = false) {
    if ($format == null)
        $format = (user_config_option('time_format_use_24') ? 'G:i' : 'g:i A');
    if ($value instanceof DateTimeValue) {
        $value = $value->format($format);
    }
    if (!$id)
        $id = $genid . $name . "Cmp";
    
	$listeners_str = "";
	if (is_array($listeners) && count($listeners) > 0) {
		$listeners_str = "
			listeners: {";
		$i=0;
		foreach ($listeners as $ev => $fn) {
			$i++;
			$listener_config = "'$ev' : $fn" . ($i < count($listeners) ? "," : "");
			$listeners_str .= "
				$listener_config";
		}
		$listeners_str .= "
			},
			";
	}
	
	$disabled_str = '';
	if ($disabled) $disabled_str = 'disabled: true,';
	
    $html = "<table class='time-picker'><tr><td><div id='" . $genid . $name . "'></div></td></tr></table>
	<script>
		var tp" . gen_id() . " = new Ext.form.TimeField({
			renderTo:'" . $genid . $name . "',
			name: '" . $name . "',
			format: '" . $format . "',
			emptyText: 'hh:mm',
			id: '" . $id . "',
			width: 80," .
			$listeners_str .
			$disabled_str .
            (isset($tabindex) ? "tabIndex: '$tabindex'," : "") .
            "value: '" . $value . "'
		});
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
    if (!isset($editor_attributes['class']))
        $editor_attributes['class'] = 'editor';
    return textarea_field($name, $value, $editor_attributes);
}

// editor_widget

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
}

// yes_no_widget

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
    foreach ($country_codes as $code) {
        if (Localization::instance()->lang_exists("country $code")) {
            $countries[$code] = lang("country $code");
        } else {
            $countries[$code] = CountryCodes::getCountryNameByCode($code);
        }
    } // foreach

    asort($countries);

    $attributes['class'] = array_var($attributes, 'class') . " country-selector";

    $country_options = array(option_tag(lang('click to select country'), ''));
    foreach ($countries as $country_code => $country_name) {
        $option_attributes = $country_code == $value ? array('selected' => true) : null;
        $country_options[] = option_tag($country_name, $country_code, $option_attributes);
    } // foreach

    return select_box($name, $country_options, $attributes);
}

// select_country_widget

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
    /*
      $all_timezones = Timezones::getTimezones();

      $options = array();
      foreach($all_timezones as $timezone) {
      $option_attributes = $selected_value == $timezone ? array('selected' => true) : null;
      $option_text = $timezone > 0 ? lang("timezone gmt +$timezone") : lang("timezone gmt $timezone");
      $options[] = option_tag($option_text, $timezone, $option_attributes);
      } // if

      return select_box($name, $options, $attributes);
     */
    return '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
}

// select_timezone_widget

/**
 * Render timezone selector
 *
 * @param string $name Name of the select box
 * @param integer $value Id of the timezone.
 * @param array $attributes Array of additional attributes
 * @return string
 */
function timezone_selector($name, $value = null, $attributes = null) {

    $genid = gen_id();
    if (!isset($attributes['id'])) {
        $attributes['id'] = $genid . 'timezoneSelector';
    }

    $sel_country = null;
    $selected_zone = Timezones::getTimezoneById($value);
    if (is_array($selected_zone)) {
        $sel_country = $selected_zone['country_code'];
    }

    $country_options = array();
    $countries = Countries::getAll();
    foreach ($countries as $code => $country_name) {
        $option_attributes = $code == $sel_country ? array('selected' => true) : null;
        $country_options[] = option_tag($country_name, $code, $option_attributes);
    }

    $country_selector_html = select_box('country_code', $country_options, array('id' => $genid . 'countrySelector', 'onchange' => "og.onTzSelectorCountryChange(this, '" . $attributes['id'] . "');"));

    $html = '<div id="' . $genid . 'country_sel_container" class="country-combo-container">' . $country_selector_html . '</div>';

    $options = array();
    if ($sel_country) {
        $zones = Timezones::getTimezonesByCountryCode($sel_country);
        foreach ($zones as $zone) {
            $option_attributes = $zone['id'] == $value ? array('selected' => true) : null;
            $zone_description = Timezones::getFormattedDescription($zone);

            $options[] = option_tag($zone_description, $zone['id'], $option_attributes);
        }
    }

    $html .= '<div id="' . $genid . 'timezone_sel_container" class="tz-combo-container">' . select_box($name, $options, $attributes) . '</div>';

    return $html;
}

function timezone_selector_hidden($object, $genid, $attributes = null) {

    $formatted = Timezones::getFormattedDescription($object->getTimezoneId(), true);
    $formatted_offset = Timezones::getFormattedOffset($object->getTimezonevalue());

    $html = "";
    if (!array_var($attributes, 'hide_label')) {
        $html .= "<label style='height:25px;'>" . lang('timezone') . "</label>";
    }

    $html .= "<div id='" . $genid . "tz_text' style='float:left;'>" . $formatted['name'] . " ($formatted_offset)</div>";
    $html .= "<input id='" . $genid . "tz_edited' value='0' name='timezone_edited' type='hidden'/>";

    $html .= "<div id='" . $genid . "tz_selector' style='display:none;'>" . timezone_selector('timezone_id', $object->getTimezoneId()) . "</div>";
    $html .= '&nbsp;<a href="#" onclick="og.showHiddenTimezoneSelector(\'' . $genid . '\')" id="' . $genid . 'tz_edit_link" class="db-ico link-ico ico-edit"></a>';

    return $html;
}

function number_field($name, $value = null, $attributes = null) {
    //if (!is_numeric($value)) $value = 0;
    return text_field($name, $value, array("maxlength" => 9, "style" => "width:100px", "onkeyup" => "event.target.value = event.target.value.replace(/[^0-9]/g, '')"));
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
        if ($darkpiece < 0)
            $darkpiece = 0;
        if ($darkpiece > 255)
            $darkpiece = 255;
        $darkerColor .= sprintf("%02x", $darkpiece);
    }
    return '#' . $darkerColor;
}

  function doubleListSelect($name, $values, $attributes = null, $option_groups = null) {
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
	
	// first option group
	$current_opt_group = null;
	$gr_count = 0;
	if (is_array($option_groups) && count($option_groups) > 0) {
		$current_opt_group = $option_groups[0];
		$options1[] = '<optgroup label="'.$current_opt_group['name'].'">';
	}
        
	$i = 0;
	foreach ($values as $val) {
		$sel = array_var($val, 'selected');
		if (!$sel)
			$options1[] = option_tag(array_var($val, 'text'), array_var($val, 'id'));
		else
            $options2[array_var($val, 'order')] = option_tag(array_var($val, 'text'), array_var($val, 'id'));
		
		$hfields .= '<input id="'.$id.'['.array_var($val, 'id').']" name="'.$name.'['.array_var($val, 'id').']" type="hidden" value="'.($sel ? array_var($val, 'order')+1 : '0').'" />';
		
		// option groups
		if ($current_opt_group && $current_opt_group['count'] == $i) {
			$gr_count++;
			$current_opt_group = $option_groups[$gr_count];
			$i = 0;
			$options1[] = '</optgroup>';
			$options1[] = '<optgroup label="'.$current_opt_group['name'].'">';
		}
		$i++;
	}
	
	if (is_array($option_groups) && count($option_groups) > 0) {
		$options1[] = '</optgroup>';
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

	ksort($options2);
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
    if ($task->getPercentCompleted() < 25)
        $color_cls .= '0';
    else if ($task->getPercentCompleted() < 50)
        $color_cls .= '25';
    else if ($task->getPercentCompleted() < 75)
        $color_cls .= '50';
    else if ($task->getPercentCompleted() < 100)
        $color_cls .= '75';
    else if ($task->getPercentCompleted() == 100)
        $color_cls .= '100';
    else
        $color_cls .= 'more-estimate';

    $percent_complete = 100;
    if ($task->getPercentCompleted() <= 100) {
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
    if ($error_msgs == null)
        $error_msgs = array();
    if (!$field_name)
        $field_name = lang('address');
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
 * @param $base_url: string view history url of the object
 * @param $pagination_obj: stdClass pagination information
 * @param $page_param_prefix: string prefix for the "page" parameter (view or mod)
 * @param $other_list_current_page: int current page of the other list, if viewing modification list this param must contain the current read logs page and viceversa
 */
function render_view_history_pagination($base_url, $pagination_obj, $page_param_prefix, $other_list_current_page) {
    $current_page = array_var($pagination_obj, 'current_page');
    $total_pages = array_var($pagination_obj, 'total_pages');

    $add_param = "&curtab=$page_param_prefix";
    if ($page_param_prefix == 'mod') {
        $add_param .= '&view_page=' . $other_list_current_page;
    }
    if ($page_param_prefix == 'view') {
        $add_param .= '&mod_page=' . $other_list_current_page;
    }
    $base_url .= $add_param;

    if ($current_page > 1) {
        echo '<a class="internalLink pagitem x-tbar-page-first" href="' . $base_url . '&' . $page_param_prefix . '_page=1' . '">&nbsp;</a>';
        echo '<a class="internalLink pagitem x-tbar-page-prev" href="' . $base_url . '&' . $page_param_prefix . '_page=' . ($current_page - 1) . '">&nbsp;</a>';
    }

    echo '<span class="pagitem text">' . lang("page") . " $current_page " . strtolower(lang('of')) . " $total_pages" . '</span>';

    if ($current_page < $total_pages) {
        echo '<a class="internalLink pagitem x-tbar-page-next" href="' . $base_url . '&' . $page_param_prefix . '_page=' . ($current_page + 1) . '">&nbsp;</a>';
        echo '<a class="internalLink pagitem x-tbar-page-last" href="' . $base_url . '&' . $page_param_prefix . '_page=' . $total_pages . '">&nbsp;</a>';
    }
}

function build_percent_completed_bar_html($task) {
    $color_cls = 'task-percent-completed-';

    if (array_var($task, 'percentCompleted') < 25)
        $color_cls .= '0';
    else if (array_var($task, 'percentCompleted') < 50)
        $color_cls .= '25';
    else if (array_var($task, 'percentCompleted') < 75)
        $color_cls .= '50';
    else if (array_var($task, 'percentCompleted') < 100)
        $color_cls .= '75';
    else if (array_var($task, 'percentCompleted') == 100)
        $color_cls .= '100';
    else
        $color_cls .= 'more-estimate';

    $percent_complete = 100;
    if (array_var($task, 'percentCompleted') <= 100) {
        $percent_complete = array_var($task, 'percentCompleted');
    }

    $html = "<span><span class='nobr'><table style='display:inline;'><tr><td style='padding-left:15px;padding-top:6px'>" .
            "<table style='height:7px;width:50px'><tr><td style='height:7px;width:" . $percent_complete . "%;' class='" . $color_cls . "'></td><td style='width:" . (100 - $percent_complete) . "%;background-color:#DDD'></td></tr></table>" .
            "</td><td style='padding-left:3px;line-height:12px'><span class='percent_num' style='font-size:8px;color:#777'>" . $percent_complete . "%</span></td></tr></table></span></span>";

    return $html;
}

function get_custom_property_type_selector_html($attributes) {

    $sel_type = array_var($attributes, 'sel_type', 'text');
    $name_prefix = array_var($attributes, 'name_prefix');
    $name = array_var($attributes, 'name', 'type');
    if (!isset($attributes['onchange']))
        $attributes['onchange'] = "og.customPropTypeChanged(this);";

    $cp_types = array('text', 'numeric', 'boolean', 'contact', 'user', 'date', 'datetime', 'list', 'memo', 'address', 'table', 'image', 'color');

    $options = array();
    foreach ($cp_types as $t) {
        $attr = $t == $sel_type ? array('selected' => 'selected') : null;
        $options[] = option_tag(lang($t), $t, $attr);
    }

    $cp_types_html = select_box($name_prefix . '[' . $name . ']', $options, $attributes);

    return $cp_types_html;
}

function render_image_custom_property_value($genid, $cp, $cp_value, $add_class = "") {
    if (is_null($cp_value))
        return "";

    tpl_assign('genid', $genid);
    tpl_assign('cp', $cp);
    tpl_assign('cp_value', $cp_value);
    tpl_assign('add_class', $add_class);

    return tpl_fetch(get_template_path('image_cp_view', 'custom_properties'));
}

function render_image_custom_property_field($cp, $config) {

    tpl_assign('genid', $config['genid']);
    tpl_assign('cp', $cp);
    tpl_assign('cp_value', $config['default_value']);
    tpl_assign('label', $config['label']);
    tpl_assign('input_name', $config['name']);
    tpl_assign('disabled', array_var($config, 'property_perm') == 'view');

    return tpl_fetch(get_template_path('image_cp_selector', 'custom_properties'));
}

function webpage_field($name, $values_array = null, $genid, $attributes = null) {
    if (is_null($values_array)) {
        $values_array = array();
    } else if (!is_array($values_array)) {
        $values_array = array($values_array);
    }

    $container_id = array_var($attributes, 'container_id', $genid . 'webpagecontainer-' . $name);
    $input_base_id = array_var($attributes, 'input_base_id', "prop-" . $name);

    $html = '<div id="' . $container_id . '" class="webpages-input-container"></div>';
    if (array_var($attributes, 'multiple')) {
        $html .= '<a href="#" onclick="og.addNewWebpageInput(\'' . $container_id . '\', \'' . $input_base_id . '\', 2)" class="coViewAction ico-add">' . lang('add new webpage') . '</a>';
    }

    $html .= "<script>$(function() {";

    if (is_array($values_array) && count($values_array) > 0) {
        foreach ($values_array as $value) {

            if ($value instanceof ContactWebpage) {
                $tmp_str = $value->getWebTypeId() . "|" . $value->getFixedUrl() . "|" . $value->getId();
                $value = $tmp_str;
            }

            $values = str_replace("\|", "%%_PIPE_%%", $value);
            $values = str_replace(array("\r", "\n"), " ", $values);
            $exploded = explode("|", $values);
            foreach ($exploded as &$v) {
                $v = str_replace("%%_PIPE_%%", "|", $v);
                $v = escape_character($v);
            }
            if (count($exploded) > 0) {
                $type = array_var($exploded, 0, '');
                $url = array_var($exploded, 1, '');
                $id = array_var($exploded, 2, '');
                $html .= "og.addNewWebpageInput('" . $container_id . "', '" . $input_base_id . "', '$type', '$url', '$id');";
            } else {
                $html .= "og.addNewWebpageInput('" . $container_id . "', '" . $input_base_id . "', 2);";
            }
        }
    } else {
        $html .= "og.addNewWebpageInput('" . $container_id . "', '" . $input_base_id . "', 2);";
    }
    
    if (array_var($attributes, 'disabled')) {
    	$html .= '
			$("#'.$container_id.' input").attr("disabled","disabled");
			$("#'.$container_id.' textarea").attr("disabled","disabled");
			$("#'.$container_id.' select").attr("disabled","disabled");
			$("#'.$container_id.'").parent().children("a").remove();
			$("#'.$container_id.' .delete-link").remove();
		';
    }

    $html .= '});</script>';

    return $html;
}

function email_field($name, $values_array = null, $genid, $attributes = null) {
    if (is_null($values_array)) {
        $values_array = array();
    } else if (!is_array($values_array)) {
        $values_array = array($values_array);
    }

    $container_id = array_var($attributes, 'container_id', $genid . 'emailcontainer-' . $name);
    $input_base_id = array_var($attributes, 'input_base_id', "prop-" . $name);

    $html = '<div id="' . $container_id . '" class="emails-input-container"></div>';
    if (array_var($attributes, 'multiple')) {
        $html .= '<a href="#" onclick="og.addNewEmailInput(\'' . $container_id . '\', \'' . $input_base_id . '\', 3)" class="coViewAction ico-add">' . lang('add new email address') . '</a>';
    }

    $html .= "<script>$(function() {";

    if (is_array($values_array) && count($values_array) > 0) {
        foreach ($values_array as $value) {

            if ($value instanceof ContactEmail) {
                $tmp_str = $value->getEmailTypeId() . "|" . $value->getEmailAddress() . "|" . $value->getId();
                $value = $tmp_str;
            }

            $values = str_replace("\|", "%%_PIPE_%%", $value);
            $values = str_replace(array("\r", "\n"), " ", $values);
            $exploded = explode("|", $values);
            foreach ($exploded as &$v) {
                $v = str_replace("%%_PIPE_%%", "|", $v);
                $v = escape_character($v);
            }

            if (count($exploded) > 0) {
                $type = array_var($exploded, 0, '');
                $email = array_var($exploded, 1, '');
                $id = array_var($exploded, 2, '');
                $html .= "og.addNewEmailInput('" . $container_id . "', '" . $input_base_id . "', '$type', '$email', '$id');";
            } else {
                $html .= "og.addNewEmailInput('" . $container_id . "', '" . $input_base_id . "', 3);";
            }
        }
    } else {
        $html .= "og.addNewEmailInput('" . $container_id . "', '" . $input_base_id . "', 3);";
    }
    
    if (array_var($attributes, 'disabled')) {
    	$html .= '
			$("#'.$container_id.' input").attr("disabled","disabled");
			$("#'.$container_id.' textarea").attr("disabled","disabled");
			$("#'.$container_id.' select").attr("disabled","disabled");
			$("#'.$container_id.'").parent().children("a").remove();
			$("#'.$container_id.' .delete-link").remove();
		';
    }

    $html .= '});</script>';

    return $html;
}

function phone_field($name, $values_array = null, $genid, $attributes = null) {
    if (is_null($values_array)) {
        $values_array = array();
    } else if (!is_array($values_array)) {
        $values_array = array($values_array);
    }

    $container_id = array_var($attributes, 'container_id', $genid . 'phonecontainer-' . $name);
    $input_base_id = array_var($attributes, 'input_base_id', "prop-" . $name);

    $html = '<div id="' . $container_id . '" class="phones-input-container"></div>';
    if (array_var($attributes, 'multiple')) {
        $html .= '<a href="#" onclick="og.addNewTelephoneInput(\'' . $container_id . '\', \'' . $input_base_id . '\')" class="coViewAction ico-add">' . lang('add new phone number') . '</a>';
    }

    $html .= "<script>$(function() {";

    if (is_array($values_array) && count($values_array) > 0) {
        foreach ($values_array as $value) {

            if ($value instanceof ContactTelephone) {
                $tmp_str = $value->getTelephoneTypeId() . "|" . $value->getNumber() . "|" . $value->getName() . "|" . $value->getId();
                $value = $tmp_str;
            }

            $values = str_replace("\|", "%%_PIPE_%%", $value);
            $values = str_replace(array("\r", "\n"), " ", $values);
            $exploded = explode("|", $values);
            foreach ($exploded as &$v) {
                $v = str_replace("%%_PIPE_%%", "|", $v);
                $v = escape_character($v);
            }
            if (count($exploded) > 0) {
                $type = array_var($exploded, 0, '');
                $number = array_var($exploded, 1, '');
                $name = array_var($exploded, 2, '');
                $id = array_var($exploded, 3, '');
                $html .= "og.addNewTelephoneInput('" . $container_id . "', '$input_base_id', '$type', '$number', '$name', '$id');";
            } else {
                $html .= "og.addNewTelephoneInput('" . $container_id . "', '$input_base_id');";
            }
        }
    } else {
        $html .= "og.addNewTelephoneInput('" . $container_id . "', '$input_base_id');";
    }
    
    
    if (array_var($attributes, 'disabled')) {
    	$html .= '
			$("#'.$container_id.' input").attr("disabled","disabled");
			$("#'.$container_id.' textarea").attr("disabled","disabled");
			$("#'.$container_id.' select").attr("disabled","disabled");
			$("#'.$container_id.'").parent().children("a").remove();
			$("#'.$container_id.' .delete-link").remove();
		';
    }

    $html .= '});</script>';

    return $html;
}

function address_field($name, $values_array = null, $genid, $attributes = null, $ignore_pre_id = false) {
    if (is_null($values_array)) {
        $values_array = array();
    } else if (!is_array($values_array)) {
        $values_array = array($values_array);
    }

    $container_id = array_var($attributes, 'container_id', $genid . 'addresscontainer-' . $name);
    $input_base_id = array_var($attributes, 'input_base_id', "prop-" . $name);

    $html = '<div id="' . $container_id . '" class="address-input-container"></div>';
    if (array_var($attributes, 'multiple')) {
        $html .= '<a href="#" onclick="og.addNewAddressInput(\'' . $container_id . '\', \'' . $input_base_id . '\')" class="coViewAction ico-add">' . lang('add new address') . '</a>';
    }
    $html .= "<div style='display:none;'>" . select_country_widget('template_country', '', array('id' => 'template_select_country')) . "</div>";
    $html .= "<script>$(function() {";

    $all_address_types = AddressTypes::getAllAddressTypesInfo();
    $html .= "og.address_types = Ext.util.JSON.decode('" . json_encode($all_address_types) . "');";

    $ignore_pre_id = $ignore_pre_id ? "true" : "false";

    if (is_array($values_array) && count($values_array) > 0) {
        foreach ($values_array as $value) {

            if ($value instanceof ContactAddress) {
                $tmp_str = $value->getAddressTypeId() . "|" . $value->getStreet() . "|" . $value->getCity() . "|" .
                        $value->getState() . "|" . $value->getCountry() . "|" . $value->getZipCode() . "|" . $value->getId();
                $value = $tmp_str;
            }

            $values = str_replace("\|", "%%_PIPE_%%", $value);
            $values = str_replace(array("\r", "\n"), " ", $values);
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
                $id = array_var($exploded, 6, '');
                $sel_data_str = "{street:'$street', city:'$city', state:'$state', zip_code:'$zip_code', country:'$country', id:'$id'}";
                $html .= "og.addNewAddressInput('" . $container_id . "', '" . $input_base_id . "', '$address_type', $sel_data_str, $ignore_pre_id);";
            } else {
                $html .= "og.addNewAddressInput('" . $container_id . "', '" . $input_base_id . "', '', {}, $ignore_pre_id);";
            }
        }
    } else {
        $html .= "og.addNewAddressInput('" . $container_id . "', '" . $input_base_id . "', '', {}, $ignore_pre_id);";
    }
    
    if (array_var($attributes, 'disabled')) {
    	$html .= '
			$("#'.$container_id.' input").attr("disabled","disabled");
			$("#'.$container_id.' textarea").attr("disabled","disabled");
			$("#'.$container_id.' select").attr("disabled","disabled");
			$("#'.$container_id.'").parent().children("a").remove();
			$("#'.$container_id.' .delete-link").remove();
		';
    }

    $html .= '});</script>';

    return $html;
}

function hour_field($name, $value = null, $attributes = array()) {
    if ($value == 0)
        $value = '';
    return text_field($name, $value, $attributes);
}

function minute_field($name, $value = null, $attributes = array()) {
    if ($value == 0)
        $value = '';
    
    //Default type of field: text field
    $field = text_field($name, $value, $attributes);
    Hook::fire('override_minute_field', array('name' => $name, 'value' => $value, 'attributes' => $attributes), $field);
    return $field;
}



function render_color_selector($name, $value = 1, $id = null, $genid = null) {
	tpl_assign("genid", $genid);
	tpl_assign("input_name", $name);
	tpl_assign("input_id", $id);
	tpl_assign("selected_color", $value);
	
	$html = tpl_fetch(get_template_path("color_selector", "widget"));
	
	echo $html;
}



function render_dropdown_links_list($links, $trigger_id, $title = "", $genid = null) {
	tpl_assign("genid", $genid);
	tpl_assign("links", $links);
	tpl_assign("trigger_id", $trigger_id);
	tpl_assign("title", $title);

	$html = tpl_fetch(get_template_path("dropdown_links_list", "dashboard"));

	echo $html;
}


