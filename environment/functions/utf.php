<?php

/**
 * Extended substr function. If it finds mbstring extension it will use, else
 * it will use old substr() function
 *
 * @access public
 * @param string $string String that need to be fixed
 * @param integer $start Start extracting from
 * @param integer $length Extract number of characters
 * @return string
 */
function substr_utf($string, $start = 0, $length = false) {
	if (function_exists('utf8_substr'))
		return utf8_substr($string, $start, $length, 'UTF-8');
	else return substr($string, $start, $length);
} // substr_utf

/**
 * Return UTF safe string lenght
 *
 * @access public
 * @param strign $string
 * @return integer
 */
function strlen_utf($string) {
	if (function_exists('utf8_strlen'))
		return utf8_strlen($string);
	else return strlen($string);
} // strlen_utf

if (!function_exists('iconv')) {
	function iconv($in_charset, $out_charset, $text) {
		return $text;
	}
}

function strpos_utf($haystack, $needle, $offset = 0) {
	if (function_exists('utf8_strpos'))
		return utf8_strpos($haystack, $needle, $offset, 'UTF-8');
	else return strpos($haystack, $needle, $offset);
}

function detect_encoding($string, $encoding_list = null, $strict = false) {
	if (function_exists('mb_detect_encoding')) {
		if ($encoding_list == null) $encoding_list = mb_detect_order();
		return mb_detect_encoding($string, $encoding_list, $strict);
	} else {
		return 'UTF-8';
	}
}

?>