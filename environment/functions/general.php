<?php

/**
 * Check if $object is valid $class instance
 *
 * @access public
 * @param mixed $object Variable that need to be checked agains classname
 * @param string $class Classname
 * @return null
 */
function instance_of($object, $class) {
	return $object instanceof $class;
} // instance_of

/**
 * Show var dump. pre_var_dump() is used for testing only!
 *
 * @access public
 * @param mixed $var
 * @return null
 */
function pre_var_dump($var) {
	print '<pre>';
	var_dump($var);
	print '</pre>';
} // pre_var_dump

/**
 * Show print_r. pre_print_r() is used for testing only!
 * @author Pepe
 * @access public
 * @param mixed $var
 * @return null
 */
function pre_print_r($var) {
	print '<pre>';
	print_r($var);
	print '</pre>';
} 

function var_alert($var) {
	if ( is_ajax_request() ) return ;
	echo "<script>";
	echo "alert(".json_encode($var).");" ;
	echo "</script>";
}


/**
 * This function will return clean variable info
 *
 * @param mixed $var
 * @param string $indent Indent is used when dumping arrays recursivly
 * @param string $indent_close_bracet Indent close bracket param is used
 *   internaly for array output. It is shorter that var indent for 2 spaces
 * @return null
 */
function clean_var_info($var, $indent = '&nbsp;&nbsp;', $indent_close_bracet = '') {
	if(is_object($var)) {
		return 'Object (class: ' . get_class($var) . ')';
	} elseif(is_resource($var)) {
		return 'Resource (type: ' . get_resource_type($var) . ')';
	} elseif(is_array($var)) {
		$result = 'Array (';
		if(count($var)) {
			foreach($var as $k => $v) {
				$k_for_display = is_integer($k) ? $k : "'" . clean($k) . "'";
				$result .= "\n" . $indent . '[' . $k_for_display . '] => ' . clean_var_info($v, $indent . '&nbsp;&nbsp;', $indent_close_bracet . $indent);
			} // foreach
		} // if
		return $result . "\n$indent_close_bracet)";
	} elseif(is_int($var)) {
		return '(int)' . $var;
	} elseif(is_float($var)) {
		return '(float)' . $var;
	} elseif(is_bool($var)) {
		return $var ? 'true' : 'false';
	} elseif(is_null($var)) {
		return 'NULL';
	} else {
		return "(string) '" . clean($var) . "'";
	} // if
} // clean_var_info

/**
 * Equivalent to htmlspecialchars(), but allows &#[0-9]+ (for unicode)
 *
 * This function was taken from punBB codebase <http://www.punbb.org/>
 *
 * @param string $str
 * @return string
 */
function clean($str) {
	$str = preg_replace('/&(?!#[0-9]+;)/s', '&amp;', $str);
	$str = str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $str);

	return $str;
} // clean

/**
 * Returns a DateTimeValue from a date representation from pick_date_widget2
 *
 * @param string $value
 * @param DateTimeValue $default
 * @return DateTimeValue
 */
function getDateValue($value = '', $default = EMPTY_DATETIME){
	if ($value instanceof DateTimeValue) return $value;
	if ($value != '' && $value != date_format_tip(user_config_option('date_format'))) {
		$date_format = user_config_option('date_format');
		return DateTimeValueLib::dateFromFormatAndString($date_format, $value);
	}	
	return $default;
}

/**
 * Returns an array separating hours and minutes
 *
 * @param string $value
 * @return array
 */
function getTimeValue($value = ''){
	if ($value == '' || $value == 'hh:mm') return null;
	$values = explode(':', $value);
	$h = array_var($values, 0);
	
	$is_pm = str_ends_with(trim(strtoupper(array_var($values, 1))), "PM");
	if ($is_pm && $h < 12) {
		$h = ($h + 12) % 24;
	}
	if ($h == 12 && str_ends_with(trim(strtoupper(array_var($values, 1))), "AM")) {
		$h = 0;
	}
	
	$m = str_replace(array(' AM', ' PM', ' am', 'pm'), "", array_var($values, 1));
	
	return array('hours' => $h, 'mins' => $m);
}

/**
 * Checks a string to see if it is a valid url address and appends http:// if it doesn't have it
 */
function cleanUrl($url, $clean = true){
	if (strpos($url,'://')<=0){
		$url = 'http://' . $url;
	}
	return $clean ? clean($url) : $url;
}

/**
 * Convert entities back to valid characteds
 *
 * @param string $escaped_string
 * @return string
 */
function undo_htmlspecialchars($escaped_string) {
	$search = array('&amp;', '&lt;', '&gt;');
	$replace = array('&', '<', '>');
	return str_replace($search, $replace, $escaped_string);
} // undo_htmlspecialchars

/**
 * This function will return true if $str is valid function name (made out of alpha numeric characters + underscore)
 *
 * @param string $str
 * @return boolean
 */
function is_valid_function_name($str) {
	$check_str = trim($str);
	if($check_str == '') return false; // empty string

	$first_char = substr_utf($check_str, 0, 1);
	if(is_numeric($first_char)) return false; // first char can't be number

	return (boolean) preg_match("/^([a-zA-Z0-9_]*)$/", $check_str);
} // is_valid_function_name


/**
 * Check if specific string is valid sha1() hash
 *
 * @param string $hash
 * @return boolean
 */
function is_valid_hash($hash) {
	return ((strlen($hash) == 32) || (strlen($hash) == 40)) && (boolean) preg_match("/^([a-f0-9]*)$/", $hash);
} // is_valid_hash

/**
 * Return variable from hash (associative array). If value does not exists
 * return default value
 *
 * @access public
 * @param array $from Hash
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
function array_var(&$from, $name, $default = null) {
	if(is_array($from)) return isset($from[$name]) ? $from[$name] : $default;
	return $default;
} // array_var

/**
 * This function will return $str as an array
 *
 * @param string $str
 * @return array
 */
function string_to_array($str) {
	if(!is_string($str) || (strlen($str) == 0)) return array();

	$result = array();
	for($i = 0, $strlen = strlen($str); $i < $strlen; $i++) {
		$result[] = $str[$i];
	} // if

	return $result;
} // string_to_array

/**
 * This function will return ID from array variables. Default settings will get 'id'
 * variable from $_GET. If ID is not found function will return NULL
 *
 * @param string $var_name Variable name. Default is 'id'
 * @param array $from Extract ID from this array. If NULL $_GET will be used
 * @param mixed $default Default value is returned in case of any error
 * @return integer
 */
function get_id($var_name = 'id', $from = null, $default = null) {
	$var_name = trim($var_name);
	if($var_name == '') return $default; // empty varname?

	if(is_null($from)) $from = $_GET;

	if(!is_array($from)) return $default; // $from is array?
	if(!is_valid_function_name($var_name)) return $default; // $var_name is valid?

	$value = array_var($from, $var_name, $default);
	return is_numeric($value) ? (integer) $value : $default;
} // get_id

/**
 * This function checks wether the current http request is ajax
 */
function is_ajax_request() {
	return array_var($_GET, 'ajax') == 'true' || is_iframe_request();
} // is_ajax_request


/**
 * This function checks wether the current http request is an upload
 */
function is_iframe_request() {
	return array_var($_GET, 'upload') == 'true' || array_var($_GET, 'download') == 'true';
} // is_iframe_request

/**
 * This function returns true if the specified value is found in the csv formatted string
 *
 * @param string $csv
 * @param $value
 * @return unknown
 */
function in_csv(string $csv, $value){
	$arr = explode(',',$csv);
	for($i = 0; $i < count($arr); $i++)
		if ($value == trim($arr[$i]))
			return true;
		
	return false;
}

/**
 * Flattens the array. This function does not preserve keys, it just returns
 * array indexed form 0 .. count - 1
 *
 * @access public
 * @param array $array If this value is not array it will be returned as one
 * @return array
 */

function array_flat($array) {
	// Not an array
	if(!is_array($array)) return array($array);

	// Prepare result
	$result = array();

	// Loop elemetns
	foreach($array as $value) {

		// Subelement is array? Flat it
		if(is_array($value)) {
			$value = array_flat($value);
			foreach($value as $subvalue) $result[] = $subvalue;
		} else {
			$result[] = $value;
		} // if
	} // if

	// Return result
	return $result;
} // array_flat

/**
 * Replace first $search_for with $replace_with in $in. If $search_for is not found
 * original $in string will be returned...
 *
 * @access public
 * @param string $search_for Search for this string
 * @param string $replace_with Replace it with this value
 * @param string $in Haystack
 * @return string
 */
function str_replace_first($search_for, $replace_with, $in) {
	$pos = strpos($in, $search_for);
	if($pos === false) {
		return $in;
	} else {
		return substr($in, 0, $pos) . $replace_with . substr($in, $pos + strlen($search_for), strlen($in));
	} // if
} // str_replace_first

/**
 * String starts with something
 *
 * This function will return true only if input string starts with
 * niddle
 *
 * @param string $string Input string
 * @param string $niddle Needle string
 * @return boolean
 */
function str_starts_with($string, $niddle) {
	return substr($string, 0, strlen($niddle)) == $niddle;
} // end func str_starts with

/**
 * String ends with something
 *
 * This function will return true only if input string ends with
 * niddle
 *
 * @param string $string Input string
 * @param string $needdle Needle string
 * @return boolean
 */
function str_ends_with($string, $needdle) {
	return substr($string, strlen($string) - strlen($needdle), strlen($needdle)) == $needdle;
} // end func str_ends_with

/**
 * Return path with trailing slash
 *
 * @param string $path Input path
 * @return string Path with trailing slash
 */

function with_slash($path) {
	return str_ends_with($path, '/') ? $path : $path . '/';
} // end func with_slash

/**
 * Remove trailing slash from the end of the path (if exists)
 *
 * @param string $path File path that need to be handled
 * @return string
 */

function without_slash($path) {
	return str_ends_with($path, '/') ? substr($path, 0, strlen($path) - 1) : $path;
} // without_slash


/**
 * Check if selected email has valid email format
 *
 * @param string $user_email Email address
 * @return boolean
 */
function is_valid_email($user_email) {
	$chars = EMAIL_FORMAT;
	if(strstr($user_email, '@') && strstr($user_email, '.')) {
		return (boolean) preg_match($chars, $user_email);
	} else {
		return false;
	} // if
} // end func is_valid_email

/**
 * Verify the syntax of the given URL.
 *
 * @access public
 * @param $url The URL to verify.
 * @return boolean
 */
function is_valid_url($url) {
	if(str_starts_with($url, '/')) return true;
	return preg_match(URL_FORMAT, $url);
} // end func is_valid_url

/**
 * Redirect to specific URL (header redirection)
 *
 * @access public
 * @param string $to Redirect to this URL
 * @param boolean $die Die when finished
 * @return void
 */
function redirect_to($to, $die = true) {
	$to = trim($to);
	if(strpos($to, '&amp;') !== false) {
		$to = str_replace('&amp;', '&', $to);
	} // if
	if (is_ajax_request()) {
		$to = make_ajax_url($to);
	}
	header('Location: ' . $to);
	if($die) die();
} // end func redirect_to

/**
 * Redirect to referer
 *
 * @access public
 * @param string $alternative Alternative URL is used if referer is not valid URL
 * @return null
 */
function redirect_to_referer($alternative = nulls) {
	$referer = get_referer();
	if(true || !is_valid_url($referer)) {
		if (is_ajax_request()) {
			$alternative = make_ajax_url($alternative);
		}
		redirect_to($alternative);
	} else {
		if (is_ajax_request()) {
			$referer = make_ajax_url($referer);
		}
		redirect_to($referer);
	} // if
} // redirect_to_referer


/**
 * Return referer URL
 *
 * @param string $default This value is returned if referer is not found or is empty
 * @return string
 */
function get_referer($default = null) {
	return array_var($_SERVER, 'HTTP_REFERER', $default);
} // get_referer

/**
 * This function will return max upload size in bytes
 *
 * @param void
 * @return integer
 */
function get_max_upload_size() {
	$max = min(
		php_config_value_to_bytes(ini_get('upload_max_filesize')),
		php_config_value_to_bytes(ini_get('post_max_size'))
	);
	Hook::fire('max_upload_size', null, $max);
	return $max;
} // get_max_upload_size

/**
 * This function will return max execution time in seconds.
 *
 * @param void
 * @return integer
 */
function get_max_execution_time() {
	$max = ini_get("max_execution_time");
	if (!$max) $max = 0;
	return $max;
}

/**
 * Convert PHP config value (2M, 8M, 200K...) to bytes
 *
 * This function was taken from PHP documentation
 *
 * @param string $val
 * @return integer
 */

function php_config_value_to_bytes($val) {
	$val = trim($val);
	if ($val == "") return 0;
	$last = strtolower($val{strlen($val)-1});
	switch($last) {
		// The 'G' modifier is available since PHP 5.1.0
		case 'g':
			$val *= 1024;
		case 'm':
			$val *= 1024;
		case 'k':
			$val *= 1024;
	} // if

	return $val;
} // php_config_value_to_bytes

// ==========================================================
//  POST and GET
// ==========================================================

/**
 * This function will strip slashes if magic quotes is enabled so
 * all input data ($_GET, $_POST, $_COOKIE) is free of slashes
 *
 * @access public
 * @param void
 * @return null
 */
function fix_input_quotes() {
	if(get_magic_quotes_gpc()) {
		array_stripslashes($_GET);
		array_stripslashes($_POST);
		array_stripslashes($_COOKIE);
	} // if
} // fix_input_quotes

/**
 * This function will walk recursivly thorugh array and strip slashes from scalar values
 *
 * @param array $array
 * @return null
 */
function array_stripslashes(&$array) {
	if(!is_array($array)) return;
	foreach($array as $k => $v) {
		if(is_array($array[$k])) {
			array_stripslashes($array[$k]);
		} else {
			$array[$k] = stripslashes($array[$k]);
		} // if
	} // foreach
	return $array;
} // array_stripslashes

/**
 * escapes this characters: & ' " < >
 */
function escapeSLIM($rawSLIM) {
	return rawurlencode($rawSLIM);
}

/**
 * unescapes: &amp; &#39; &quot; &lt; &gt;
 */
function unescapeSLIM($encodedSLIM) {
	return rawurldecode($encodedSLIM);
}

function remove_css_and_scripts($html) {
	$html = preg_replace('/<style[^>]*>.*<\/style[^>]*>/i', '', $html);
	$html = preg_replace('/<script[^>]*>.*(<\/script[^>]*>|$)/i', '', $html);
	return $html;
}

function remove_css($html) {
	return preg_replace('/<style[^>]*>.*<\/style[^>]*>/i', '', $html);
}

/**
 * @deprecated Use HTMLPurifier
 */
function remove_scripts($html) {
	if (is_array($html)) {
		foreach ($html as $k => &$v) $v = remove_scripts($v);
		return $html;
	}
	return preg_replace('/<script[^>]*>.*(<\/script[^>]*>|$)/i', '', $html);
}

function remove_images_from_html($html) {
	$html = preg_replace('/background="[^"]*"/i', '', $html);
	$html = preg_replace('/background-image:url\([^\)]*\)/i', '', $html);
	
	$html = preg_replace('/<img[^>]*>/i', '', $html);
	return preg_replace('/<\/img>/i', '', $html);
}

function html_has_images($html) {
	return preg_match('/<img[^>]*>/i', $html) > 0 || preg_match('/background-image:url\([^\)]*\)/i', $html) > 0 || preg_match('/background="[^"]*"/i', $html) > 0;
}

function make_ajax_url($url) {
	$q = strpos($url, '?');
	$n = strpos($url, '#');
	if ($q === false) {
		if ($n === false) {
			return $url . "?ajax=true";
		} else {
			return substr($url, 0, $n) . "?ajax=true" . substr($url, $n);
		}
	} else {
		return substr($url, 0, $q + 1) . "ajax=true&" . substr($url, $q + 1);
	}
}

/**
 * Preppends a backslash before single quotes
 * @param $text
 * @return string
 */
function escape_single_quotes($text) {
	return str_replace("'", "\\'", $text);
}

function escape_html_whitespace($html) {
	return str_replace(array("\r\n", "\r", "\n", "  ", "\t", "  ", "<br/> "), array("<br/>", "<br/>", "<br/>", "&nbsp; ", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ", "&nbsp; ", "<br/>&nbsp;"), $html);
}

function convert_to_links($text){
	$orig = $text;
	//Replace full urls with hyperinks. Avoids " character for already rendered hyperlinks
	$text = preg_replace('@([^"\']|^)(https?://([-\w\.]+)+(:\d+)?(/([%\w/_\-\.\:\#\+]*(\?[^\s<]+)?)?)?)@', '$1<a href="$2" target="_blank">$2</a>', $text);

	//Convert every word starting with "www." into a hyperlink
	$text = preg_replace('@(>|\s|^)(www.([-\w\.]+)+(:\d+)?(/([%\w/_\-\.\:\#\+]*(\?[^\s<]+)?)?)?)@', '$1<a href="http://$2" target="_blank">$2</a>', $text);
		
	//Convert every email address into an <a href="mailto:... hyperlink
	$text = preg_replace('/([^\:a-zA-Z0-9>"\._\-\+=])([a-zA-Z0-9]+[a-zA-Z0-9\._\-\+]*@[a-zA-Z0-9_\-]+([a-zA-Z0-9\._\-]+)+)/', '$1<a href="mailto:$2" target="_blank">$2</a>', $text);
	Hook::fire('convert_to_links', array('original' => $orig, 'text' => $text), $text);
	return $text;
}

/**
 * Generates a random id to be used as id of HTML elements.
 * It does not guarantee the uniqueness of the id, but the probability
 * of generating a duplicate id is very small.
 *
 */
function gen_id() {
	static $ids = array();
	do {
		$time = time();
		$rand = rand(0, 1000000);
		$id = "og_".$time."_".$rand;
	} while (array_var($ids, $id, false));
	$ids[$id] = true;
	return $id;
}

function purify_html($html) {
	require_once LIBRARY_PATH . "/htmlpurifier/HTMLPurifier.standalone.php";
	$config = null;
	if (defined('CUSTOM_HTMLPURIFIER_CACHEDIR') && is_dir(CUSTOM_HTMLPURIFIER_CACHEDIR)) {
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Cache.SerializerPath', CACHE_DIR);
	}
	$p = new HTMLPurifier($config);
	return $p->purify($html);
}
/**
 * 
 * @return the real Clients IP
 */
function get_ip_address()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function zip_supported() {
	return class_exists('ZipArchive', false);
}

function db_escape_field($field) {
	return DB::escapeField($field);
}

function plugin_sort($a, $b) {
	if (isset ( $a ['order'] ) && isset ( $b ['order'] )) {
		if ($a ['order'] == $b ['order']) {
			return 0;
		}
		return ($a ['order'] < $b ['order']) ? - 1 : 1;
	} elseif (isset ( $a ['order'] )) {
		return - 1;
	} elseif (isset ( $b ['order'] )) {
		return 1;
	} else
	return strcasecmp ( $a ['name'], $b ['name'] );
}

/**
 * 
 * Make a request
 * @param string $url
 * @param string $method ('GET','POST')
 * @param array $data
 * @param string $additional_headers
 * @param bool $followRedirects
 */
function HttpRequest( $url, $method = 'GET', $data = NULL, $additional_headers = NULL, $followRedirects = true, $async = false )
{
	$original_data = $data;
	$header = '';
	$body = '';
	# in compliance with the RFC 2616 post data will not redirected
	$method = strtoupper($method);
	$url_parsed = @parse_url($url);
	if (!@$url_parsed['scheme']) $url_parsed = @parse_url('http://'.$url);
	extract($url_parsed);
	if(!is_array($data)) {
		$data = NULL;
	}
	else {
		$ampersand = '';
		$temp = NULL;
		foreach($data as $k => $v)
		{
			$temp .= $ampersand.urlencode($k).'='.urlencode($v);
			$ampersand = '&';
		}
		$data = $temp;
	}
	if(!isset($port)) $port = 80;
	if(!isset($path)) $path = '/';
	if(($method == 'GET') and ($data)) $query = (@$query)?'&'.$data:'?'.$data;
	if(@isset($query)) $path .= '?'.$query;
	$out = "$method $path HTTP/1.0\r\n";
	$out .= "Host: $host\r\n";
	if($method == 'POST') {
		$out .= "Content-type: application/x-www-form-urlencoded\r\n";
		$out .= "Content-length: " . @strlen($data) . "\r\n";
	}
	$out .= (@$additional_headers)?$additional_headers:'';
	$out .= "Connection: Close\r\n\r\n";
	if($method == 'POST') $out .= $data."\r\n";
	if(!$fp = @fsockopen($host, $port, $es, $en, 5)){
		$err =  error_get_last();
		Logger::log('Error on fsockopen: ' . $err["message"] . $err["file"] . $err["line"]);
		return false;
	}
	fwrite($fp, $out);
	
	if ($async) {
		// don't read from the socket connection if the request is asynchronic
		fclose($fp);
		return;
		
	} else {
		
		$result = ''; 
		while(!feof($fp)) {
			// receive the results of the request
			$result .= fgets($fp, 128);
		}
	    fclose($fp);
	    
	    // split the result header from the content
	    $result = explode("\r\n\r\n", $result, 2);
	 
	    $header = isset($result[0]) ? $result[0] : '';
	    $body = isset($result[1]) ? $result[1] : '';
	    
	    $headers = explode("\r\n", $header);
	    $status = $headers[0];
		
		if ($followRedirects) {
			foreach ($headers as $hline) {
				if (str_starts_with($hline, "Location:")) {
					$url = trim(str_replace("Location:", "", $hline));
					return HttpRequest($url, $method, $original_data, $additional_headers, $followRedirects);
				}
			}
		}
		
	}
    return array('head' => trim($header), 'body' => trim($body), 'status' => $status);
}
