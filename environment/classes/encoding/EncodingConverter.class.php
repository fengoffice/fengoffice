<?php
class EncodingConverter
{
	var $_last_err;
	var $_last_err_no;
	var $_last_err_filename;
	var $_last_err_line;
	var $_last_err_func;

	function _handleError($err, $msg, $errfile, $errline, $errcontext) {
		$trace = debug_backtrace();
		$trace_count = count($trace);
		if (is_array($trace) && $trace_count > 2) {
			$this->_last_err_filename = $trace[2]['file'];
			$this->_last_err_line = $trace[2]['line'];
			$this->_last_err_func = $trace[2]['function'];
		} else {
			$this->_last_err_filename = $errfile;
			$this->_last_err_line = $errline;
			$this->_last_err_func = "";
		}
		
		$this->_last_err_no = $err;
		$this->_last_err = "[Encoding Conversion Error] Msg: $msg in '".$this->_last_err_filename."' on line ".$this->_last_err_line." (error code: $err)";
	}

	function convert($in_enc, $out_enc, $str, $return_original_on_error = true, $ignore_non_compatible = true) {
		$this->_last_err = null;
		set_error_handler(array(&$this, '_handleError'));
		
		if ($ignore_non_compatible) $out_enc .= "//IGNORE//TRANSLIT";
		
		$retval = iconv($in_enc, $out_enc, $str);
		
		restore_error_handler();
		if ($this->hasError()) {
			Logger::log($this->getLastErrorMsg());
			if ($return_original_on_error) {
				// If an error occurs, return the original string
				return $str;
			}
		}
		
		return $retval;
	}
	
	function isUtf8RegExp($text){
		
		return preg_match('%^([\\x00-\\x7f]|
			[\\xc2-\\xdf][\\x80-\\xbf]|
			\\xe0[\\xa0-\\xbf][\\x80-\\xbf]|
			[\\xe1-\\xec][\\x80-\\xbf]{2}|
			\\xed[\\x80-\\x9f][\\x80-\\xbf]|
			\\xef[\\x80-\\xbf][\\x80-\\xbd]|
			\\xee[\\x80-\\xbf]{2}|
			\xf0[\\x90-\\xbf][\\x80-\\xbf]{2}|
			[\\xf1-\\xf3][\\x80-\\xbf]{3}|
			\\xf4[\\x80-\\x8f][\\x80-\\xbf]{2})*$%xs', $text);
		
	}

	function hasError() {
		return $this->_last_err != null;
	}

	function getLastErrorNumber() {
		return $this->_last_err_no;
	}

	function getLastErrorMsg() {
		return $this->_last_err;
	}
	
	function getLastErrorLine() {
		return $this->_last_err_line;
	}
	
	function getLastErrorFilename() {
		return $this->_last_err_filename;
	}
	
	function getLastErrorFunction() {
		return $this->_last_err_func;
	}
	
	function instance() {
		static $instance;
		if(!instance_of($instance, 'EncodingConverter')) {
			$instance = new EncodingConverter();
		} // if
		return $instance;
	} // instance
}
?>