<?php

  /**
  * Let user select where he wants to store uploaded files
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class DateRangeConfigHandler extends ConfigHandler {
  
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      $options = array();
      
      $possible_values = array('today', 'this_week', 'last_week', 'this_month', 'last_month', 
      		'this_quarter', 'last_quarter', 'this_year', 'last_year', 'range');
      
      $decoded_value = json_decode($this->getValue(), true);
      
      $date_type = array_var($decoded_value, 'type');
            
      $options[] = option_tag("", "");
      foreach ($possible_values as $value) {
      	
      	  $opt_label = $value == 'range' ? lang('select dates...') : lang(str_replace("_", " ", $value));
      	  
	      $option_attributes = $date_type == $value ? array('selected' => 'selected') : null;
	      $options[] = option_tag($opt_label, $value, $option_attributes);
 		
      }
      
      $conf_option_name = $this->getConfigOption()->getName();
      $attributes = array(
      		'id' => 'date_range_type_' . $this->getConfigOption()->getName(),
      		'onchange' => "og.on_date_range_config_option_change(this, '$conf_option_name')",
      );
      // range type selector
      $html = select_box($control_name."[type]", $options, $attributes);
      
      $html .= '<div class="'.$conf_option_name.' date-range-container" style="'.($date_type == 'range' ? '' : 'display:none;').'">';
      
      $html .= '<table class="date-range-inputs"><tr>';
      $html .= '<td>' . lang('from date') . '</td><td>';
      
      // range start date
      $dts = DateTimeValueLib::makeFromString(array_var($decoded_value, 'range_start', ''));
      $html .= pick_date_widget2($control_name."[range_start]", $dts, '', null, null, 'date_range_start_' . $this->getConfigOption()->getName());
      
      $html .= '</td></tr><tr><td>' . lang('to date') . '</td><td>';
      
      // range end date
      $dte = DateTimeValueLib::makeFromString(array_var($decoded_value, 'range_end', ''));
      $html .= pick_date_widget2($control_name."[range_end]", $dte, '', null, null, 'date_range_end_' . $this->getConfigOption()->getName());
      
      $html .= '</td></tr></table>';
      $html .= '</div>';
      
      return $html;
    } // render
    
    
    function phpToRaw($value) {
    	
    	if (is_string($value)) {
    		$value = json_decode($value, true);
    	}
    	
    	$dts = null;
    	if (isset($value['range_start'])) {
    		$dts = getDateValue($value['range_start']);
    	}
    	$dte = null;
    	if (isset($value['range_end'])) {
    		$dte = getDateValue($value['range_end']);
    	}
    	
    	$value['range_start'] = $dts instanceof DateTimeValue ? $dts->toMySQL() : '';
    	$value['range_end'] = $dte instanceof DateTimeValue ? $dte->toMySQL() : '';
    	
    	return json_encode($value);
    }
  
  } // DateRangeConfigHandler

?>