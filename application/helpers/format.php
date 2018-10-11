<?php

  /**
  * Format filesize
  *
  * @access public
  * @param integer $in_bytes Site in bytes
  * @return string
  */
  function format_filesize($in_bytes) {
    $units = array(
      'TB' => 1099511627776,
      'GB' => 1073741824,
      'MB' => 1048576,
      'kb' => 1024,
      //0 => 'bytes'
    ); // array
    
    // Loop units bigger than byte
    foreach($units as $current_unit => $unit_min_value) {
      if($in_bytes >= $unit_min_value) {
        $formated_number = number_format($in_bytes / $unit_min_value, 2);
        
        while(str_ends_with($formated_number, '0')) $formated_number = substr($formated_number, 0, strlen($formated_number) - 1); // remove zeros from the end
        if(str_ends_with($formated_number, '.')) $formated_number = substr($formated_number, 0, strlen($formated_number) - 1); // remove dot from the end
        
        return $formated_number . ' ' . $current_unit;
      } // if
    } // foreach
    
    // Bytes?
    return $in_bytes . ' bytes';
    
  } // format_filesize
  
  /**
  * Return formated datetime
  *
  * @access public
  * @param DateTimeValue $value If value is not instance of DateTime object new DateTime
  *   object will be created with $value as its constructor param
  * @param string $format If $format is NULL default datetime format will be used
  * @param float $timezone Timezone, if NULL it will be autodetected (by currently logged user if we have it)
  * @return string
  */
  function format_datetime($value = null, $format = null, $timezone = null) {
    if(is_null($timezone) && function_exists('logged_user') && (logged_user() instanceof Contact)) {
      $timezone = logged_user()->getUserTimezoneHoursOffset();
    } // if
    $datetime = $value instanceof DateTimeValue ? $value : new DateTimeValue($value);
    if ($format){
    	$l = new Localization();
    	$l->setDateTimeFormat($format);
    }else{
    	$l = Localization::instance();
    	$formatTime = user_config_option('time_format_use_24') ? 'G:i' : 'g:i A';
    	$format = user_config_option('date_format').' '.$formatTime;   	
    	$l->setDateTimeFormat($format);
    }	
    return $l->formatDateTime($datetime, $timezone);
  } // format_datetime
  
  /**
  * Return formated date
  *
  * @access public
  * @param DateTimeValue $value If value is not instance of DateTime object new DateTime
  *   object will be created with $value as its constructor param
  * @param string $format If $format is NULL default date format will be used
  * @param float $timezone Timezone, if NULL it will be autodetected (by currently logged user if we have it)
  * @return string
  */
  function format_date($value = null, $format = null, $timezone = null) {
    if(is_null($timezone) && function_exists('logged_user') && (logged_user() instanceof Contact)) {
      $timezone = logged_user()->getUserTimezoneHoursOffset();
    } // if
    $datetime = $value instanceof DateTimeValue ? $value : new DateTimeValue($value);
    if ($format){
    	$l = new Localization();
    	$l->setDateFormat($format);
    }else{
    	$l = Localization::instance();
    	$format = user_config_option('date_format');
    	$l->setDateTimeFormat($format);
    }
    return $l->formatDate($datetime, $timezone);
  } // format_date
  
  /**
  * Return descriptive date
  *
  * @param DateTimeValue $value If value is not instance of DateTime object new DateTime
  *   object will be created with $value as its constructor param
  * @param float $timezone Timezone, if NULL it will be autodetected (by currently logged user if we have it)
  * @return string
  */
  function format_descriptive_date($value = null, $timezone = null) {
    if(is_null($timezone) && function_exists('logged_user') && (logged_user() instanceof Contact)) {
      $timezone = logged_user()->getUserTimezoneHoursOffset();
    } // if
    $datetime = $value instanceof DateTimeValue ? $value : new DateTimeValue($value);
    return Localization::instance()->formatDescriptiveDate($datetime, $timezone);
  } // format_descriptive_date
  
  /**
  * Return formated time
  *
  * @access public
  * @param DateTime $value If value is not instance of DateTime object new DateTime
  *   object will be created with $value as its constructor param
  * @param string $format If $format is NULL default time format will be used
  * @param float $timezone Timezone, if NULL it will be autodetected (by currently logged user if we have it)
  * @return string
  */
  function format_time($value = null, $format = null, $timezone = null) {
    if(is_null($timezone) && function_exists('logged_user') && (logged_user() instanceof Contact)) {
      $timezone = logged_user()->getUserTimezoneHoursOffset();
    } // if
    $datetime = $value instanceof DateTimeValue ? $value : new DateTimeValue($value);
    //if (!$format) $format = user_config_option('time_format_use_24') ? 'G:i' : 'g:i A';
    if ($format) {
    	$l = new Localization();
    	$l->setTimeFormat($format);
    } else {
    	$l = Localization::instance();
    	$format = user_config_option('time_format_use_24') ? 'G:i' : 'g:i A';
    	$l->setTimeFormat($format);
    }
    return $l->formatTime($datetime, $timezone);
  } // format_time

function friendly_date(DateTimeValue $date, $timezone = null) {
	if ($timezone == null) {
		$timezone = logged_user()->getUserTimezoneHoursOffset();
	}
	
	//TODO: 7 days before: "Dom at 13:43", older: "Oct, 06 at 15:20"
	$dateControl = new DateTimeValue($date->getTimestamp()+$timezone*3600);	
	if ($date->isToday()) {
		$now = DateTimeValueLib::now();
		$diff = DateTimeValueLib::get_time_difference($date->getTimestamp(), $now->getTimestamp());
		if ($diff['hours'] == 0) {
			if ($diff['minutes'] >= 0)
				return lang('minutes ago', $diff['minutes']);
			else
				return format_descriptive_date($date);
		} else if ($diff['hours'] > 0) {
			return lang('about hours ago', round($diff['hours'] + ($diff['minutes'] > 30 ? 1 : 0)));
		} else {
			return format_descriptive_date($date);
		}
	} else if ($dateControl->isYesterday()) {
		return lang('yesterday at', format_time($date));
	} else {
		$now = DateTimeValueLib::now();
		$diff = DateTimeValueLib::get_time_difference($date->getTimestamp(), $now->getTimestamp());
		if ($diff['days'] < 7) {
			return lang('day at', Localization::dateByLocalization("D", $dateControl->getTimestamp()), format_time($date));
		} else if ($now->getYear() != $date->getYear()) {
			return lang('day at', Localization::dateByLocalization("M d, Y", $dateControl->getTimestamp()), format_time($date));
		} else {
			return lang('day at', Localization::dateByLocalization("M, d", $dateControl->getTimestamp()), format_time($date));
		}
	}
}
  
  /**
 * truncate string and add ellipsis
 *
 * Type:     modifier<br>
 * Name:     mb_truncate<br>
 * Purpose:  Truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string or inserting $etc into the middle.
 *           This version also supports multibyte strings.
 * @link http://smarty.php.net/manual/en/language.modifier.truncate.php
 *          truncate (Smarty online manual)
 * @author   Guy Rutenberg <guyrutenberg@gmail.com> based on the original 
 *           truncate by Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param integer
 * @param string
 * @param string
 * @param boolean
 * @param boolean
 * @return string
 */
function truncate($string, $length, $etc = '...', $charset='UTF-8',
                                  $break_words = false, $middle = false)
{
    if ($length == 0)
        return '';
    
 		$len = utf8_strlen($string);
 		$lenetc = utf8_strlen($etc);
    
    if ($len > $length) {
        $length -= min($length, $lenetc);
        if (!$break_words && !$middle) {
        	
            $string = preg_replace('/\s+?(\S+)?$/', '', utf8_substr($string, 0, $length+1, $charset));
        	
        }
        if(!$middle) {
        	
            return utf8_substr($string, 0, $length, $charset) . $etc;
        	
        } else {
        	
            return utf8_substr($string, 0, $length/2, $charset) . $etc . utf8_substr($string, -$length/2, $charset);
        	
        }
    } else {
        return $string;
    }
}

function date_format_tip($format) {
	$traductions = array('d' => 'dd', 'D' => lang('sunday short'), 'j' => 'd', 'l' => lang('sunday'), 'N' => 'w', 'S' => 'st', 
					'w' => 'w', 'z' => 'dy', 'W' => 'W', 'F' => lang('month 1'), 'm' => 'mm', 'M' => substr(lang('month 1'),0,3),
					'n' => 'm', 't' => '', 'L' => '', 'o' => 'yyyy', 'Y' => 'yyyy', 'y' => 'yy',
					'a' => 'am', 'A' => 'AM', 'B' => '000', 'g' => 'h', 'G' => 'h', 'h' => 'hh', 'H' => 'hh', 'i' => 'mm', 
					's' => 'ss', 'u' => 'uuuuu', 'e' => 'GMT', 'I' => '', 'O' => '+hhmm', 'P' => '+hh:mm', 'T' => 'EST', 
					'Z' => 'ssss', 'c' => 'ISO date', 'r' => 'Thu, 21 Dec 2000 16:01:07 +0200', 'U' => 'ssss');
	
	$formatChars = array_keys($traductions);
	$result = '';
	$i = 0;
	while ($i < strlen($format)) {
		$char = $format[$i++];
		if (in_array($char, $formatChars)) $result .= $traductions[$char];
		else $result .= $char;
	}
	
	return $result;
}


	function format_value_to_print($col, $value, $type, $obj_type_id, $textWrapper='', $dateformat='Y-m-d', $tz_offset=null,$is_gruped = false) {
		$is_time_column = false;
		
		$ot = ObjectTypes::findById($obj_type_id);
		if ($ot instanceof ObjectType && $ot->getHandlerClass() != '') {
			eval('$manager = '.$ot->getHandlerClass()."::instance();");
			if ($manager) {
				
				$time_cols = $manager->getTimeColumns();

				Hook::fire('additional_column_timeslots',array('ot'=>$ot),$time_cols);

				if (in_array($col, $time_cols)) {
					$format = user_config_option('report_time_colums_display');
					$is_time_column = true;

                    if ($ot->getName() == 'timeslot' && $col == 'subtract') {
                        $value = round($value/60);
                    }
                    if ($ot->getName() == 'timeslot' && ($col == 'time_estimate' || $col == 'bill_total_price' || $col == 'bill_total_cost') && !$is_gruped) { // don't show value per line, only in totals
                        $value = '--';
                    }

                    switch ($format) {
						case 'seconds': $formatted = $value * 60; break;
						case 'minutes': $formatted = $value; break;
						case 'hours': $formatted = $value / 60; break;
                        case 'hh:mm':

                            $formatted = sprintf('%02d', intval($value / 60)).':'.sprintf('%02d', $value % 60);

                            break;
						default:
							$formatted = '';
							if (!is_numeric($value)) {
								$formatted = $value;
							} else if($value > 0) {
								$formatted = DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($value * 60), 'hm', 60);
							}
							break;
					}
				}
			}
		}
		if (!$is_time_column) {

		  switch ($type) {
			case DATA_TYPE_STRING: 
				if(preg_match(EMAIL_FORMAT, strip_tags($value))){
					$formatted = strip_tags($value);
				}else{
					if ($col == 'is_user') {
						$formatted = ($value == 1 ? lang('yes') : lang('no'));
					} else {
						if (strpos($value, "\xA0") !== false) $value = preg_replace('/\xA0/s', ' ', $value);
						$value = utf8_safe($value);
						$formatted = $textWrapper . $value . $textWrapper;
					}
				}
				break;
			case DATA_TYPE_INTEGER:
				if ($col == 'priority'){
					switch($value){
					case 100:
						$formatted = lang('low priority'); 
						break;
					case 200:
						$formatted = lang('normal priority');
						break;
					case 300:
						$formatted = lang('high priority');
						break;
					case 400:
						$formatted = lang('urgent priority');
						break;
					default: $formatted = clean($value);
					}
					
				} else{
					$formatted = clean($value);
				}
				break;
			case DATA_TYPE_BOOLEAN:
			    
			    if ($value == 1){
			        $formatted = lang('yes');
			    }else if($value == -1){
			        $formatted = lang('no');
			    }else{
			        $formatted = "";
			    }			    
				break;
			case DATA_TYPE_DATE:
				if ($value instanceof DateTimeValue) {
					$formatted = $value->format("$dateformat");
				} else if ($value != 0) { 
					if (str_ends_with($value, "00:00:00")) $dateformat .= " H:i:s";
					try {
                                                $date_format = user_config_option('date_format');
						$dtVal = DateTimeValueLib::dateFromFormatAndString($date_format, $value);
					} catch (Exception $e) {
						$formatted = $value;						
					}
					if (!isset($formatted)) {
						$formatted = format_date($dtVal, $dateformat,0);
					}
				} else $formatted = '';
				break;
			case DATA_TYPE_DATETIME:
				if ($value instanceof DateTimeValue) {
					$formatted = $value->format("$dateformat H:i:s");
				} else if ($value != 0) {
					try {
						$dtVal = DateTimeValueLib::dateFromFormatAndString("$dateformat H:i:s", $value);
					} catch (Exception $e) {
						$formatted = $value;
					}
					if ($dtVal instanceof DateTimeValue) {
						if ($obj_type_id == ProjectEvents::instance()->getObjectTypeId() || $obj_type_id == ProjectTasks::instance()->getObjectTypeId()) {
							if (is_null($tz_offset)) {
								$tz_offset = logged_user()->getUserTimezoneValue();
							}
							$dtVal->advance($tz_offset, true);
						}
						if ($obj_type_id == ProjectEvents::instance()->getObjectTypeId() && ($col == 'start'|| $col == 'duration')) $formatted = format_datetime($dtVal);
						else $formatted = format_date($dtVal, null, 0);
					}
				} else $formatted = '';
				break;
			default: $formatted = $value;
		  }
		}
		if($formatted == ''){
			$formatted = '--';
		}
		
		return $formatted;
	}


function get_format_value_to_header($col, $obj_type_id)
{
    $formatted = '';
    $ot = ObjectTypes::findById($obj_type_id);
    if ($ot instanceof ObjectType && $ot->getHandlerClass() != '') {
        eval('$manager = ' . $ot->getHandlerClass() . "::instance();");
        if ($manager) {

            $time_cols = $manager->getTimeColumns();

            Hook::fire('additional_column_timeslots', array('ot' => $ot), $time_cols);

            if (in_array($col, $time_cols)) {
                $format = user_config_option('report_time_colums_display');

                switch ($format) {
                    case 'seconds':
                        $formatted = '';
                        break;
                    case 'minutes':
                        $formatted = '';
                        break;
                    case 'hours':
                        $formatted = '';
                        break;
                    case 'hh:mm':
                        $formatted = '(hh:mm)';
                        break;
                    default:
                        $formatted = '';
                        break;
                }
            }
        }
    }
    return $formatted;
}

	function get_custom_property_value_for_listing($cp, $obj, $cp_vals=null, $raw_data=false, $options=array()) {
		$object_id = $obj instanceof ContentDataObject ? $obj->getId() : $obj;
		
		if (is_null($cp_vals)) {
			$cp_vals = CustomPropertyValues::getCustomPropertyValues($object_id, $cp->getId());
		}
		$val_to_show = "";
		if ($raw_data && $cp->getType() == 'address') {
			$val_to_show = array();
		}
		
		if ($cp->getType() == 'table') {
			
			$rows = array();
			$cpvs = CustomPropertyValues::getCustomPropertyValues($object_id, $cp->getId());
			foreach ($cpvs as $cpval) {
				$row = array();
				$values = str_replace("\|", "%%_PIPE_%%", $cpval->getValue());
				$exploded = explode("|", $values);
				foreach ($exploded as &$v) {
					$v = str_replace("%%_PIPE_%%", "|", $v);
					$v = escape_character($v);
					if (trim($v) != "") $row[] = $v;
				}
				$rows[] = $row;
			}
			
			if (array_var($options, 'table_html')) {
				tpl_assign('cp', $cp);
				tpl_assign('rows', $rows);
				$formatted = tpl_fetch(get_template_path('table_cp_view', 'custom_properties'));
			} else {
				$formatted = "";
				foreach ($rows as $row) {
					$formatted .= ($formatted == "" ? "" : " - ") . implode(', ', $row);
				}
			}
				
			$val_to_show .= $formatted;
			
		} else {
			
			foreach ($cp_vals as $cp_val) {
				if (in_array($cp->getType(), array('contact', 'user')) && $cp_val instanceof CustomPropertyValue) {
					$cp_contact = Contacts::findById($cp_val->getValue());
					if ($cp_contact instanceof Contact) {
						$cp_val->setValue($cp_contact->getObjectName());
					} else {
						$cp_val->setValue("");
					}
				}
				
				if ($cp->getType() == 'boolean' && $cp_val instanceof CustomPropertyValue) {
				    if ($cp_val->getValue() == 1){
				        $formatted = lang('yes');
				    }else if($cp_val->getValue() == -1){
				        $formatted = lang('no');
				    }else{
				        $formatted = "";
				    }
					$cp_val->setValue($formatted);
				}
				
				if ($cp->getType() == 'list' && $cp->getIsSpecial() && $cp_val instanceof CustomPropertyValue) {
					$cp_list_values = explode(',', $cp->getValues());
					if (in_array($cp_val->getValue(), $cp_list_values)) {
						$lang_value = Localization::instance()->lang($cp_val->getValue());
					} else {
						foreach ($cp_list_values as $cp_list_value) {
							$exp = explode('@', $cp_list_value);
							if (count($exp) == 2 && $exp[0] == $cp_val->getValue()) {
								$lang_value = Localization::instance()->lang($exp[1]);
								if (is_null($lang_value)) {
									$lang_value = $exp[1];
								}
							}
						}
					}
					if (!is_null($lang_value)) {
						$cp_val->setValue($lang_value);
					}
				}
				
				if (($cp->getType() == 'date' || $cp->getType() == 'datetime') && $cp_val instanceof CustomPropertyValue) {
					
					if (!$raw_data) {
						$format = user_config_option('date_format');
						$format_from = "Y-m-d";
						$cp_date_value = $cp_val->getValue();
						
						if ($cp->getType() == 'datetime') {
							$format .= (user_config_option('time_format_use_24') ? " G:i" : " g:i A");
							$format_from = DATE_MYSQL;
						} else {
							// if is date and has time values -> remove them
							if (($tmp_pos = strpos($cp_date_value, " ")) !== false) {
								$cp_date_value = trim(substr($cp_date_value, 0, $tmp_pos));
							}
						}
						
						try {
							$tmp_date = DateTimeValueLib::dateFromFormatAndString($format_from, $cp_date_value);
						} catch (Exception $e) {
							$tmp_date = null;
						}
						if ($cp_val->getValue() == "" || str_starts_with($cp_val->getValue(), EMPTY_DATE)) {
							$formatted = "";
						} else {
							if ($tmp_date instanceof DateTimeValue) $formatted = $tmp_date->format($format);
						}
						$cp_val->setValue($formatted);
					}
				}
				
				if ($cp->getType() == 'address' && $cp_val instanceof CustomPropertyValue) {
					$values = str_replace("\|", "%%_PIPE_%%", $cp_val->getValue());
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
						$country_name = CountryCodes::getCountryNameByCode($country);
						
						if ($raw_data) {
							$formatted = array(
								'street' => $street,
								'city' => $city,
								'state' => $state,
								'country' => $country_name,
								'zip_code' => $zip_code,
							);
							if (count($cp_vals) > 1) {
								$val_to_show[] = $formatted;
							} else {
								return $formatted;
							}
						} else {
							
							$tmp = array();
							if ($street != '') $tmp[] = $street;
							if ($city != '') $tmp[] = $city;
							if ($state != '') $tmp[] = $state;
							if ($zip_code != '') $tmp[] = $zip_code;
							if ($country_name != '') $tmp[] = $country_name;
							$cp_val->setValue(implode(' - ', $tmp));
						}
					}
				}
				
				if ($cp->getType() == 'image' && $cp_val instanceof CustomPropertyValue) {
					// if raw_data=true then return the json value as it is in the db, else render the feng component
					if (!$raw_data) {
						$formatted = render_image_custom_property_value("", $cp, $cp_val->getValue(), "list");
						$cp_val->setValue($formatted);
					}
				}
				
				if ($raw_data && $cp->getType() == 'address') {
					$val_to_show[] = $formatted;
				} else {
					$val_to_show .= ($val_to_show == "" ? "" : ", ") . ($cp_val instanceof CustomPropertyValue ? $cp_val->getValue() : "");
				}
			}
		}
		return $val_to_show;
	}
	
	function get_member_custom_property_value_for_listing($cp, $member_id, $cp_vals=null, $raw_data=false, $options=array()) {
		if (is_null($cp_vals)) {
			$cp_vals = MemberCustomPropertyValues::getMemberCustomPropertyValues($member_id, $cp->getId());
		}
		$val_to_show = "";
		if ($raw_data && $cp->getType() == 'address') {
			$val_to_show = array();
		}
		
		if ($cp->getType() == 'table') {
				
			$rows = array();
			$cpvs = MemberCustomPropertyValues::getMemberCustomPropertyValues($member_id, $cp->getId());
			foreach ($cpvs as $cpval) {
				$row = array();
				$values = str_replace("\|", "%%_PIPE_%%", $cpval->getValue());
				$exploded = explode("|", $values);
				foreach ($exploded as &$v) {
					$v = str_replace("%%_PIPE_%%", "|", $v);
					$v = escape_character($v);
					if (trim($v) != "") $row[] = $v;
				}
				$rows[] = $row;
			}
		
			if (array_var($options, 'table_html')) {
				tpl_assign('cp', $cp);
				tpl_assign('rows', $rows);
				$formatted = tpl_fetch(get_template_path('table_cp_view', 'custom_properties'));
			} else {
				$formatted = "";
				foreach ($rows as $row) {
					$formatted .= ($formatted == "" ? "" : " - ") . implode(', ', $row);
				}
			}
		
			$val_to_show .= $formatted;
			
		} else if ($cp->getType() == 'image') {
			$formatted = "";
			foreach ($cp_vals as $cp_val) {
				if ($cp_val instanceof MemberCustomPropertyValue) {
					// if raw_data=true then return the json value as it is in the db, else render the feng component
					if (!$raw_data) {
						$formatted .= render_image_custom_property_value("", $cp, $cp_val->getValue(), "list");
					}
				}
			}
			$val_to_show .= $formatted;
				
		} else {
			
			foreach ($cp_vals as $cp_val) {
				if (in_array($cp->getType(), array('contact', 'user')) && $cp_val instanceof MemberCustomPropertyValue) {
					$cp_contact = Contacts::findById($cp_val->getValue());
					if ($cp_contact instanceof Contact) {
						$cp_val->setValue($cp_contact->getObjectName());
					} else {
						$cp_val->setValue("");
					}
				}
				
				if ($cp->getType() == 'list' && $cp_val instanceof MemberCustomPropertyValue) {
					
					$all_values = explode(',', $cp->getValues());
					foreach ($all_values as $value) {
						$text = null;
						if (strpos($value, '@') !== false) {
							$exp = explode('@', $value);
							$value = array_var($exp, 0);
							$text = array_var($exp, 1);
						}
						
						if ($text == null) {
							$text = $cp->getCode() == "" ? $value : lang($value);
						} else {
							$text = $cp->getCode() == "" ? $text : lang($text);
						}
						if ($value == $cp_val->getValue()) {
							$cp_val->setValue($text);
							break;
						}
					}
					
				}
					
				if (($cp->getType() == 'date' || $cp->getType() == 'datetime') && $cp_val instanceof MemberCustomPropertyValue) {
		
					if (!$raw_data) {
						$format = user_config_option('date_format');
						$format_from = "Y-m-d";
							
						if ($cp->getType() == 'datetime') {
							$format .= (user_config_option('time_format_use_24') ? " G:i" : " g:i A");
							$format_from = DATE_MYSQL;
						}
						
						$tmp_date = DateTimeValueLib::dateFromFormatAndString($format_from, $cp_val->getValue());
						
						if (str_starts_with($cp_val->getValue(), EMPTY_DATE)) {
							$formatted = "";
						} else {
							$formatted = $tmp_date->format($format);
						}
						$cp_val->setValue($formatted);
					}
				}
				
				if ($cp->getType() == 'address' && $cp_val instanceof MemberCustomPropertyValue) {
					$values = str_replace("\|", "%%_PIPE_%%", $cp_val->getValue());
					$exploded = explode("|", $values);
					foreach ($exploded as &$v) {
						$v = str_replace("%%_PIPE_%%", "|", $v);
						$v = str_replace("'", "\'", $v);
					}
					if (count($exploded) > 0) {
						$address_type = array_var($exploded, 0, '');
						$street = array_var($exploded, 1, '');
						$city = array_var($exploded, 2, '');
						$state = array_var($exploded, 3, '');
						$country = array_var($exploded, 4, '');
						$zip_code = array_var($exploded, 5, '');
						$country_name = CountryCodes::getCountryNameByCode($country);
						
						if ($raw_data) {
							$formatted = array(
								'street' => $street,
								'city' => $city,
								'state' => $state,
								'country' => $country_name,
								'zip_code' => $zip_code,
							);
							if (count($cp_vals) > 1) {
								$val_to_show[] = $formatted;
							} else {
								return $formatted;
							}
						} else {
							$tmp = array();
							if ($street != '') $tmp[] = $street;
							if ($city != '') $tmp[] = $city;
							if ($state != '') $tmp[] = $state;
							if ($zip_code != '') $tmp[] = $zip_code;
							if ($country_name != '') $tmp[] = $country_name;
							$cp_val->setValue(implode(' - ', $tmp));
								
						}
					}
				}
				if ($raw_data && $cp->getType() == 'address') {
					$val_to_show[] = $formatted;
				} else {
					$val_to_show .= ($val_to_show == "" ? "" : ", ") . ($cp_val instanceof MemberCustomPropertyValue ? $cp_val->getValue() : "");
				}
			}
			if (gettype($val_to_show) == "string") {
				$val_to_show = html_to_text($val_to_show);
			}
		}
		
		return $val_to_show;
	}
	
	
	function format_money_amount($number, $symbol = '$', $decimals = 2) {
		
		$sign = "";
		if ($number < 0) {
			$sign = "- ";
		}
		$formatted = $sign . $symbol . " " . number_format(abs($number), $decimals);
		
		return $formatted;
	}
	
	function format_boolean_to_string($value){
	    switch ( $value ){
	        case 1:
	            $result = "yes";
	            break;
	        case -1:
	            $result = "no";
	            break;
	        case 0:
	            $result = "";
	            break;
	    }
	    return $result;
	}

?>