<?php

  /**
  * Return image URL
  *
  * @access public
  * @param string $filename Filename or path relative to images dir
  * @return string
  */
  function image_url($filename) {
    return get_image_url($filename);
  } // image_url
  
  /**
  * Return URL of specific icon
  *
  * @access public
  * @param string $filename Icon filename or file path relative to icons dir
  * @return string
  */
  function icon_url($filename) {
    return image_url("icons/$filename");
  } // icon_url
  
  /**
  * Render icon IMG tag
  *
  * @access public
  * @param string $filename Icon filename
  * @param string $alt Value of alt attrbute for IMG
  * @param array $attributes Array of additional attributes
  * @return string
  */
  function render_icon($filename, $alt = '', $attributes = null) {
    if(is_array($attributes)) {
      $attributes['src'] = icon_url($filename);
      $attributes['alt'] = $alt;
    } else {
      $attributes = array(
        'src' => icon_url($filename),
        'alt' => $alt
      ); // array
    } // if
    return open_html_tag('img', $attributes, true);
  } // render_icon
  
  /**
  * Use widget
  *
  * @access public
  * @param string $widget_name
  * @return void
  */
  function use_widget($widget_name) {
    if(function_exists('add_javascript_to_page') && function_exists('add_stylesheet_to_page')) {
      add_javascript_to_page("widgets/$widget_name/widget.js");
      add_stylesheet_to_page(get_javascript_url("widgets/$widget_name/widget.css"));
    } // if
  } // use_widget
  
  /**
  * Return checkbox link
  *
  * @access public
  * @param string $link
  * @param boolean $checked
  * @param string $hint
  * @return string
  */
  function checkbox_link($link, $checked = false, $hint = null) {
    $title_attribute = is_null($hint) ? '' : 'title="' . clean($hint) . '"';
    $icon_url = $checked ? icon_url('checked.jpg') : icon_url('not-checked.jpg');
    return "<a class=\"internalLink\" href=\"$link\" $title_attribute><img src=\"$icon_url\" alt=\"\" /></a>";
    //return "<a class=\"checkboxLink\" href=\"$link\" $title_attribute onclick=\"og.openLink(this.href);\"><img src=\"$icon_url\" alt=\"\" /></a>";
  } // checkbox_link

  /**
  * Returns an array with urls as keys and file contents as values
  *
  * @access public
  * @param string $source html with image urls
  * @return array
  */
  function get_image_contents($source) {
		preg_match_all("/<img[^>]*src=[\"']([^\"']*)[\"']/", $source, $matches);
		$urls = array_var($matches, 1);
		$images = array();
		if (is_array($urls)) {
			foreach ($urls as $url) {
				$cache_name = preg_replace("/[^a-zA-Z0-9]/", "_", $url);
				$cache_path = "tmp/$cache_name.cache";
				if (!is_file($cache_path)) {
					$content = file_get_contents($url);
					file_put_contents($cache_path, $content);
				} else {
					$content = file_get_contents($cache_path);
				}
				$images[$url] = $content;
			}
		}
		return $images;
	}
	
	/**
  * Returns an array with urls as keys and file contents as values
  *
  * @access public
  * @param string $source html with image urls
  * @return array
  */
  function get_image_paths($source) {
		preg_match_all("/<img[^>]*src=[\"']([^\"']*)[\"']/", $source, $matches);
		$urls = array_var($matches, 1);
		$images = array();
		if (is_array($urls)) {
			foreach ($urls as $url) {
				if (substr($url, 0, 5) != 'http:' && substr($url, 0, 6) != 'https:' || !is_file($url)) continue; // ignore non-http urls
				if (defined('CACHE_EMAIL_IMAGE_URLS') && CACHE_EMAIL_IMAGE_URLS) {
					$cache_name = preg_replace("/[^a-zA-Z0-9]/", "_", $url);
					$cache_path = "tmp/$cache_name.cache";
					if (!is_file($cache_path)) {
						$content = file_get_contents($url);
						file_put_contents($cache_path, $content);
					} 
					$images[$url] = $cache_path;
				} else {
					$images[$url] = $url;
				}
			}
		}
		return $images;
	}
	
	if (!function_exists('array_fill_keys')) {
		function array_fill_keys($keys, $value) {
			$result = array();
			foreach ($keys as $k) {
				$result[$k] = $value;
			}
			return $result;
		}
	}
	
	function get_mysql_date_format_from_config_option() {
		$date_format = user_config_option('date_format');
		$mysql_date_format = "";
		for($i=0; $i<strlen($date_format); $i++) {
			$char = $date_format[$i];
			if (in_array($char, array('d','m','Y'))) {
				$mysql_date_format .= "%";
			}
			$mysql_date_format .= $char;
		}
		return $mysql_date_format;
  }
  
  
  /**
  * Helper function to check if the value is a valid date,
  * returns true or false
  *
  * @access public
  * @param string or date
  * @return boolean
  */
  function isDate($value) {
    if (!$value) {
        return false;
    }
    
    try {
        // use this function to get a DateTimeValue object depending on the date format defined in the user preferences
        $dt_value = getDateValue($value);
        
        return $dt_value instanceof DateTimeValue;
    } catch (Exception $e) {
        return false;
    }
  }

  /**
  *  Helper function to generate monthly repetition date
  * returns newly generated date
  *
  * @access public
  * @return DateTimeValue
  */
  function getMonthlyRepetitionDate($task, $new_date, $original_date, $count){
    $day = $original_date->format('d');
    $new_month = $original_date->format('m') + ($count+1)*$task->getRepeatM();
    $year = $original_date->format('Y');

    // Check the month number, increase the year if needed
    if($new_month%12 == 0) {
        $year += (int)($new_month/12) - 1;
        $month = 12;
    } else {
        $year += (int)($new_month/12);
        $month = $new_month%12;
    }

    // Check if day exist(troubleshoot last day of the month)
    while(!checkdate($month, $day, $year)) {
        $day -=  1;
    }
    
    // Set day to 1, to avoid month adjustments
    $new_date->setDay(1);

    // Set year, month and day
    $new_date->setYear($year);
    $new_date->setMonth($month);
    $new_date->setDay($day);

    return $new_date;
}

?>