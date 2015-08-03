<?php
	$exp = explode("/", $_SERVER['REQUEST_URI']);
	$iname = $exp[1];
	
	define('CONSOLE_MODE', true);
	define('APP_ROOT', realpath(dirname(__FILE__)));
	define('TEMP_PATH', realpath(APP_ROOT . '/tmp/'));
	
	// Include library
	//require_once APP_ROOT . '/index.php';
	require_once APP_ROOT . '/config/config.php';
	
	if (count($_FILES) > 0) {
		$file_info = array_shift($_FILES);
		
		$file_name = rand() . $file_info['name'];
		$file_name = preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
		$file_url = ROOT_URL . "/tmp/" . $file_name;
		
		//Exclude non image files.
       /* $pattern = '/^.*\.(jpeg|JPEG|jpg|JPG|gif|GIF|png|PNG|bmp|BMP)$/';
        preg_match($pattern, $file_name, $matches, PREG_OFFSET_CAPTURE);
        if(count($matches) <= 0)
        	die('Invalid File');*/
        	
		copy($file_info['tmp_name'], TEMP_PATH . "/" . $file_name);
		unlink($file_info['tmp_name']);
		
		$err_msg = "";
		$func = preg_replace("/[^0-9]/", "", $_GET['CKEditorFuncNum']);
		
        echo "<script type=\"text/javascript\">";
        echo "window.parent.CKEDITOR.tools.callFunction($func, '" . str_replace("'", "\\'", $file_url) . "', '" .str_replace("'", "\\'", $err_msg). "');";
        echo "</script>";
	}
?>