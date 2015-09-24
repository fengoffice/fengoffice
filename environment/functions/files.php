<?php

/**
 * Check if specific folder is writable
 *
 * @param string $path
 * @return boolean
 */
function folder_is_writable($path) {
	if(!is_dir($path)) {
		return false;
	} // if

	do {
		$test_file = with_slash($path) . sha1(uniqid(rand(), true));
	} while(is_file($test_file));

	$put = @file_put_contents($test_file, 'test');
	if($put === false) {
		return false;
	} // if

	@unlink($test_file);
	return true;
} // folder_is_writable

/**
 * Check if specific file is writable
 *
 * @param string $path
 * @return boolean
 */
function file_is_writable($path) {
	if(!is_file($path)) {
		return false;
	} // if

	$open = @fopen($path, 'a+');
	if($open === false) {
		return false;
	} // if

	@fclose($open);
	return true;
} // file_is_writable

/**
 * Return specific line of specific file
 *
 * @access public
 * @param string $file
 * @param integer $line
 * @param midex $default Returned if file or line does not exists
 * @return string
 */
function get_file_line($file, $line, $default = null) {
	if(is_file($file)) {
		$lines = file($file);
		return isset($file[$line]) ? $file[$line] : $default;
	} else {
		return $default;
	} // if
} // get_file_line

/**
 * Return the files from specific directory. This function can filter result
 * by file extension (accepted param is single extension or array of extensions)
 *
 * @example get_files($dir, array('doc', 'pdf', 'xst'))
 *
 * @param string $dir Dir that need to be scaned
 * @param mixed $extension Singe or multiple file extensions that need to be
 *   mached. If null no check is performed...
 * @param boolean $base_name_only Return only filenames. If this option is set to
 *   false this function will return full paths.
 * @return array
 */
function get_files($dir, $extension = null, $base_name_only = false) {
	 
	// Check dir...
	if(!is_dir($dir)) return false;

	// Prepare input data...
	$dir = with_slash($dir);
	if(!is_null($extension)) {
		if(is_array($extension)) {
			foreach($extension as $k => $v) $extension[$k] = strtolower($v);
		} else {
			$extension = strtolower($extension);
		} // if
	} // if
	 
	// We have a dir...
	if(!is_dir($dir)) return null;
	 
	// Open dir and prepare result
	$d = dir($dir);
	if (!is_object($d)) throw new Error($dir);
	$files = array();

	// Loop dir entries
	while(false !== ($entry = $d->read())) {
			
		// Valid entry?
		if(($entry <> '.') && ($entry <> '..')) {
			 
			// Get file path...
			$path = $dir . $entry;

			// If we have valid file that do the checks
			if(is_file($path)) {
				 
				if(is_null($extension)) {
					$files[] = $base_name_only ? basename($path) : $path;
				} else {
					 
					// Match multiple extensions?
					if(is_array($extension)) {
						 
						// If in array add...
						if(in_array( strtolower(get_file_extension($path)), $extension )) {
							$files[] = $base_name_only ? basename($path) : $path;
						} // if

						// Match single extension
					} else {
						 
						// If extensions match add...
						if(strtolower(get_file_extension($path)) == $extension) {
							$files[] = $base_name_only ? basename($path) : $path;
						} // if

					} // if

				} // if

			} // if

		} // if

	} // while

	// Done... close dir...
	$d->close();

	// And return...
	return count($files) > 0 ? $files : null;

} // get_files

/**
 * Return file extension from specific path
 *
 * @access public
 * @param string $path File path
 * @param boolean $leading_dot Include leading dot (or not...)
 * @return string
 */
function get_file_extension($path, $leading_dot = false) {
	$filename = basename($path);
	$dot_offset = (boolean) $leading_dot ? 0 : 1;
	 
	if( ($pos = strrpos($filename, '.')) !== false ) {
		return substr($filename, $pos + $dot_offset, strlen($filename));
	} // if

	return '';
} // get_file_extension

/**
 * Return size of a specific dir in bytes
 *
 * @access public
 * @param string $dir Directory
 * @return integer
 */
function dir_size($dir) {
	$totalsize = 0;
	 
	if ($dirstream = @opendir($dir)) {
		while (false !== ($filename = readdir($dirstream))) {
			if (($filename != ".") && ($filename != "..")) {
				$path = with_slash($dir) . $filename;
				if (is_file($path)) $totalsize += filesize($path);
				if (is_dir($path)) $totalsize += dir_size($path);
			} // if
		} // while
	} // if
	 
	closedir($dirstream);
	return $totalsize;
} // end func dir_size

function file_is_zip($mime_type, $extension = '') {
	return ($mime_type == 'application/zip' || $mime_type == 'application/x-zip-compressed' || 
			($mime_type == 'application/x-compressed' && $extension == 'zip') || $extension == 'zip');
}

/**
 * Remove specific directory
 *
 * @access public
 * @param string $dir Directory path
 * @return boolean
 */
function delete_dir($dir) {
	$dh = opendir($dir);
	while($file = readdir($dh)) {
		if(($file != ".") && ($file != "..")) {
			$fullpath = $dir . "/" . $file;
				
			if(!is_dir($fullpath)) {
				unlink($fullpath);
			} else {
				delete_dir($fullpath);
			} // if
		} // if
	} // while

	closedir($dh);
	return rmdir($dir) ? true : false;
} // end func delete_dir

/**
 * Force creation of all dirs
 *
 * @access public
 * @param void
 * @return null
 */
function force_mkdir($path, $chmod = null) {
	if (mkdir($path, $chmod, true)) {
		$parts = explode('/', $path);
		
		//exec chmod over all folder from upload to the last of the path
		$repository_path = implode("/",array_slice($parts, 0, (count($parts)-3)));
		$partial_path = $repository_path;
				
		$partial_path .= "/".$parts[count($parts)-3];
		chmod($partial_path, $chmod);
				
		$partial_path .= "/".$parts[count($parts)-2];
		chmod($partial_path, $chmod);
		
		chmod($path, $chmod);
		return true;
	}
	return false;
} // force_mkdir

function force_mkdir_from_base($base, $path, $chmod = null) {
	if(is_dir(with_slash($base).$path)) return true;
	$real_path = str_replace('\\', '/', $path);
	$parts = explode('/', $real_path);
	 
	$forced_path = '';
	foreach($parts as $part) {
		if($part !='')
		{
			// Skip first on windows
			if($forced_path == '') {
				$forced_path = with_slash($base) . $part;
			} else {
				$forced_path .= '/' . $part;
			} // if
			if(!is_dir($forced_path)) {
				if(!is_null($chmod)) {
					if(!mkdir($forced_path)) return false;
				} else {
					if(!mkdir($forced_path, $chmod)) return false;
				} // if
			} // if
		} // if
	} // foreach
	 
	return true;
} // force_mkdir

/**
 * This function will return true if $dir_path is empty
 *
 * @param string $dir_path
 * @return boolean
 */
function is_dir_empty($dir_path) {
	$d = dir($dir_path);
	if($d) {
		while(false !== ($entry = $d->read())) {
			if(($entry == '.') || ($entry == '..')) continue;
			return false;
		} // while
	} // if
	return true;
} // is_dir_empty

/**
 * Check if file $in/$desired_filename exists and if it exists save it in
 * $in/$desired_filename(x).exteionsion (X is inserted in front of the extension)
 *
 * @access public
 * @param string $in Directory
 * @param string $desired_filename
 * @return string
 */
function get_unique_filename($in, $desired_filename) {

	if(!is_dir($in)) false;

	$file_path = $in . '/' . $desired_filename;
	$counter = 0;
	while(is_file($file_path)) {
		$counter++;
		$file_path = insert_before_file_extension($file_path, '(' . $counter . ')');
	} // if

	return $file_path;

} // get_unique_filename

/**
 * Set something before file extension
 *
 * @access public
 * @param string $in Filename
 * @param string $insert Insert this
 * @return null
 */
function insert_before_file_extension($filename, $insert) {
	if (strpos($filename,'.') > 0)
	return str_replace_first('.', $insert . '.', $filename);
	else
	return $filename . $insert;
} // insert_before_file_extension

/**
 * Forward specific file to the browser. Download can be forced (dispolition: attachment) or passed as inline file
 *
 * @access public
 * @param string $path File path
 * @param string $type Serve file as this type
 * @param string $name If set use this name, else use filename (basename($path))
 * @param boolean $force_download Force download (add Disposition => attachement)
 * @return boolean
 */
function download_file($path, $type = 'application/octet-stream', $name = '', $disposition_attachment=false, $force_download=true) {
	if (!is_readable($path)) return false;

	$name = trim($name) == '' ? basename($path) : trim($name);
	$size = filesize($path);
	include_once ROOT . "/library/browser/Browser.php";
	if (Browser::instance()->getBrowser() == Browser::BROWSER_IE) {
		$name = rawurlencode($name);
	}
	
	if (!function_exists('readfile')) {
		$contents = file_get_contents($path);
		return download_contents($contents, $type, $name, $size, $disposition_attachment, $force_download);
	}
	if(connection_status() != 0) return false; // check connection
	
	if (ob_get_length()) ob_clean();
	
	header("Content-Type: $type");
	header("Content-Length: " . (string) $size);

	// Prepare disposition
	$disposition = $disposition_attachment ? 'attachment' : 'inline';
	header("Content-Disposition: $disposition; filename=\"" . $name . "\"");
	header("Content-Transfer-Encoding: binary");
	
	if ($force_download) {
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");// Age is in seconds.
	    header("Cache-Control: post-check=0, pre-check=0");
	    header("Expires: " . gmdate("D, d M Y H:i:s", mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"))) . " GMT");
	    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	} else {
   		header("Cache-Control: maxage=2592000"); // 1 month
   		$next = DateTimeValueLib::now(); // next month
   		$next = $next->add('M', 1);
   		header("Expires: " . $next->format("D, d M Y H:i:s") . " GMT");
	}
    
   	header("Pragma: public");
	readfile($path);

	return((connection_status() == 0) && !connection_aborted());
} // download_file

/**
 * Use content (from file, from database, other source...) and pass it to the browser as a file
 *
 * @param string $content
 * @param string $type MIME type
 * @param string $name File name
 * @param integer $size File size
 * @param boolean $force_download Send Content-Disposition: attachment to force save dialog
 * @return boolean
 */
function download_contents($content, $type, $name, $size, $disp_attachment = false, $force_download = true) {
	if(connection_status() != 0) return false; // check connection

	include_once ROOT . "/library/browser/Browser.php";
	if (Browser::instance()->getBrowser() == Browser::BROWSER_IE) {
		$name = rawurlencode($name);
	}
	
	if (ob_get_length()) ob_clean();
	header("Content-Type: $type");
	header("Content-Length: " . (string) $size);

	// Prepare disposition
	$disposition = $disp_attachment ? 'attachment' : 'inline';
	header("Content-Disposition: $disposition; filename=\"" . $name . "\"");
	header("Content-Transfer-Encoding: binary");
   	
   	if ($force_download) {
   		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");// Age is in seconds.
   		header("Cache-Control: post-check=0, pre-check=0");
   		header("Expires: " . gmdate("D, d M Y H:i:s", mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"))) . " GMT");
   		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
   	} else {
   		header("Cache-Control: maxage=2592000"); // 1 month
   		$next = DateTimeValueLib::now(); // next month
   		$next = $next->add('M', 1);
   		header("Expires: " . $next->format("D, d M Y H:i:s") . " GMT");
   	}
   	
   	header("Pragma: public");
	print $content;

	return((connection_status() == 0) && !connection_aborted());
} // download_contents

/**
 * Download a file from the file repository.
 * 
 * @param string $id
 * @param string $type
 * @param string $name
 * @param boolean $force_download
 * @return boolean
 */
function download_from_repository($id, $type, $name, $disp_attachment=false, $force_download = false) {
	if (FileRepository::getBackend() instanceof FileRepository_Backend_FileSystem) {
		$path = FileRepository::getBackend()->getFilePath($id);
		if (is_file($path)) {
			// this method allows downloading big files without exhausting php's memory
			return download_file($path, $type, $name, $disp_attachment, $force_download);
		}
	}
	$content = FileRepository::getBackend()->getFileContent($id);
	return download_contents($content, $type, $name, strlen($content), $disp_attachment, $force_download);
}

/**
 * This function is used for sorting list of files.
 *
 * The most important thing about this function is $extractor. It is function
 * name of function that will be used to extract data that we need for sorting
 *  - filesize, file modification type, content-type... Anything. First param of
 * $extractor function must be filepath.
 *
 * After the extract have all the data it need $sort_with function will be used
 * to sort by the extracted data... First param of the $sort_with function must
 * be array that need to be sorted. This function MUST RETURN SORTED ARRAY, cant
 * use side effect...
 *
 * Important = $sort_with must be key sorting function because this function
 * saves extracted data into the array keys...
 *
 * Examples:
 *
 * sort_files($files, 'filemtime', 'krsort', SORT_NUMERIC) will sort all files
 * by modification time and the freshest files will be at the top of the result
 *
 * @access public
 * @param array $file Array of filenames
 * @param string $extractor Function that will be used for extractiong specific
 *   file data (like file creation time or filesize)
 * @param string $sort_with Function that will be used to sort the array
 *   when we are done...
 * @param mixed $sort_method If this value is <> null that this will be passed
 *   to the sort functions as second param. I added it because there are great
 *   number of function that can use it to make a diffrence between string and int
 *   sorting...
 * @return array
 */
function sort_files($files, $extractor, $sort_with = 'array_ksort', $sort_method = null) {

	// Prepare...
	$extractor = trim($extractor);
	$sort_with = trim($sort_with);

	// Check the input data...
	if(!is_array($files)) return false;
	if(!function_exists($extractor)) return false;
	if(!function_exists($sort_with)) return false;

	// Prepare the tmp array...
	$tmp = array();

	// OK, now get the files...
	foreach($files as $file) {

		// Pass this one?
		if(!is_file($file)) continue;

		// Get data...
		$data = call_user_func($extractor, $file);

		// Prepare array...
		if(!isset($tmp[$data])) $tmp[$data] = array();

		// Add filename to the extracted param...
		$tmp[$data][] = $file;

	} // foreach

	// OK, now sort subarrays
	foreach($tmp as &$subarray) {
		if(count($subarray) > 0) sort($subarray);
	} // if

	// OK, do the sort thing...
	if(is_null($sort_method)) {
		$sorted = call_user_func($sort_with, $tmp);
	} else {
		$sorted = call_user_func_array($sort_with, array($tmp, $sort_method));
	} // if

	// Check sorted array
	if(!is_array($sorted)) return false;

	// OK, flatten...
	$result = array();
	foreach($sorted as &$subarray) {
		$result = array_merge($result, $subarray);
	} // foreach

	// And done...
	return $result;

} // sort_files

// ================================================================
//  SORT FUNC REPLACEMENTS
//
//  These function RETURN sorted array, don't use side effect. They
//  are used by the sort_files() function in the
//  environment/functions/files.php
// ================================================================

/**
 * Replacement function for sort() function. Returns array
 *
 * @access public
 * @param array $array Array that need to be sorted
 * @param int $flag Sort flag, described on sort() function documentation page
 * @return array
 */
function array_sort($array, $flag = SORT_REGULAR) {
	sort($array, $flag);
	return $array;
} // end func

/**
 * Replacement function for rsort() function. Returns array
 *
 * @access public
 * @param array $array Array that need to be sorted
 * @param int $flag Sort flag, described on sort() function documentation page
 * @return array
 */
function array_rsort($array, $flag = SORT_REGULAR) {
	rsort($array, $flag);
	return $array;
} // end func array_rsort

/**
 * Replacement function for ksort() function. Returns array
 *
 * @access public
 * @param array $array Array that need to be sorted
 * @param int $flag Sort flag, described on sort() function documentation page
 * @return array
 */
function array_ksort($array, $flag = SORT_REGULAR) {
	ksort($array, $flag);
	return $array;
} // end func array_ksort

/**
 * Replacement function for krsort() function. Returns array
 *
 * @access public
 * @param array $array Array that need to be sorted
 * @param int $flag Sort flag, described on sort() function documentation page
 * @return array
 */
function array_krsort($array, $flag = SORT_REGULAR) {
	krsort($array, $flag);
	return $array;
} // end func array_krsort

/*** / SORT FUNC REPLACEMENTS ***/

?>