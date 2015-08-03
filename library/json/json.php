<?php

if (!function_exists('json_encode')) {
	require_once 'library/json/JSON.class.php';
	function json_encode($object) {
		$value = new Services_JSON();
		return $value->encode($object); 
	}
}

if (!function_exists('json_decode')) {
	require_once 'library/json/JSON.class.php';
	function json_decode($json_string) {
		$value = new Services_JSON();
		return $value->decode($json_string); 
	}
}


?>