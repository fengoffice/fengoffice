<?php
/**
 * CKEditor image upload handler.
 *
 * Hardened for CVE-2026-36669: the endpoint now requires an authenticated
 * session (the same level a normal editor upload needs), validates the actual
 * file content against a strict allowlist, rejects executable/renderable types,
 * and stores every upload under a randomized name. See also tmp/.htaccess, which
 * prevents inline rendering / script execution of anything stored here.
 */

	// Prevents init.php from routing to a controller (see init.php bottom).
	define('CONSOLE_MODE', true);

	// Bootstrap the full framework (DB, session, logged user, helpers).
	require_once dirname(__FILE__) . '/index.php';

	define('TEMP_PATH', ROOT . '/tmp');

	$func = preg_replace("/[^0-9]/", "", array_var($_GET, 'CKEditorFuncNum', ''));
	$json_response = array_var($_GET, 'response') === 'json';

	/**
	 * Return the CKEditor callback script (or JSON) and stop. Either $file_url (success)
	 * or $err_msg (failure) is filled in.
	 */
	function ck_upload_callback($func, $file_url, $err_msg) {
		global $json_response;
		if (!empty($json_response)) {
			header('Content-Type: application/json; charset=UTF-8');
			echo json_encode(array(
				'url' => $file_url,
				'error' => $err_msg,
			));
			exit;
		}
		echo "<script type=\"text/javascript\">";
		echo "window.parent.CKEDITOR.tools.callFunction($func, '" . str_replace("'", "\\'", $file_url) . "', '" . str_replace("'", "\\'", $err_msg) . "');";
		echo "</script>";
		exit;
	}

	// ---------------------------------------------------------------
	//  1. Authentication: only logged in users may upload.
	// ---------------------------------------------------------------
	$user = function_exists('logged_user') ? logged_user() : null;
	if (!($user instanceof Contact)) {
		ck_upload_callback($func, '', lang('ck upload not authorized'));
	}

	if (count($_FILES) == 0) {
		ck_upload_callback($func, '', lang('ck upload no file'));
	}

	$file_info = array_shift($_FILES);

	// Reject anything that is not a genuine HTTP upload (anti-spoofing).
	if (!isset($file_info['tmp_name']) || !is_uploaded_file($file_info['tmp_name'])) {
		ck_upload_callback($func, '', lang('ck upload no file'));
	}

	// ---------------------------------------------------------------
	//  2. Filename handling: strip path, look at every extension part.
	// ---------------------------------------------------------------
	$original = basename($file_info['name']); // drop any directory component
	$parts    = explode('.', strtolower($original));
	$ext      = array_pop($parts);

	// Strict allowlist of safe, non-renderable types.
	$allowed_extensions = array('jpg', 'jpeg', 'gif', 'png', 'bmp',
		'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
		'txt', 'csv', 'rtf', 'zip', 'rar', '7z');

	// Always rejected, even as a secondary extension (e.g. "evil.php.png").
	$denied_extensions = array('php', 'php3', 'php4', 'php5', 'php7', 'phtml',
		'pht', 'phtm', 'phps', 'phar', 'svg', 'svgz', 'html', 'htm', 'xhtml',
		'shtml', 'xml', 'swf', 'js', 'htaccess');

	foreach ($parts as $part) {
		if (in_array($part, $denied_extensions, true)) {
			ck_upload_callback($func, '', lang('ck upload invalid type'));
		}
	}
	if (in_array($ext, $denied_extensions, true) || !in_array($ext, $allowed_extensions, true)) {
		ck_upload_callback($func, '', lang('ck upload invalid type'));
	}

	// ---------------------------------------------------------------
	//  3. Content validation: trust the bytes, not the extension.
	// ---------------------------------------------------------------
	$image_extensions = array('jpg', 'jpeg', 'gif', 'png', 'bmp');
	$is_image = in_array($ext, $image_extensions, true);

	if ($is_image) {
		// Images are served inline in the editor, so they must really be images.
		$image_ext_map = array(
			IMAGETYPE_JPEG => array('jpg', 'jpeg'),
			IMAGETYPE_GIF  => array('gif'),
			IMAGETYPE_PNG  => array('png'),
			IMAGETYPE_BMP  => array('bmp'),
		);
		$size = @getimagesize($file_info['tmp_name']);
		if ($size === false || !isset($image_ext_map[$size[2]]) || !in_array($ext, $image_ext_map[$size[2]], true)) {
			ck_upload_callback($func, '', lang('ck upload invalid type'));
		}
	}

	// Reject any payload that sniffs as a renderable/executable type,
	// regardless of its extension (polyglot defence).
	if (function_exists('finfo_open')) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$detected_mime = strtolower((string) finfo_file($finfo, $file_info['tmp_name']));
		finfo_close($finfo);

		$dangerous_mimes = array('text/html', 'application/xhtml+xml', 'image/svg+xml',
			'application/xml', 'text/xml', 'application/x-shockwave-flash',
			'application/javascript', 'text/javascript',
			'application/x-php', 'text/x-php', 'application/php', 'text/php',
			'application/x-httpd-php', 'application/x-httpd-php-source');
		if (in_array($detected_mime, $dangerous_mimes, true)) {
			ck_upload_callback($func, '', lang('ck upload invalid type'));
		}
	}

	// ---------------------------------------------------------------
	//  4. Store under a randomized name and hand the URL back.
	// ---------------------------------------------------------------
	$store_name = md5(uniqid(mt_rand(), true)) . '.' . $ext;
	$dest_path  = TEMP_PATH . '/' . $store_name;

	if (!@move_uploaded_file($file_info['tmp_name'], $dest_path)) {
		ck_upload_callback($func, '', lang('ck upload failed to move'));
	}

	$file_url = ROOT_URL . '/tmp/' . $store_name;
	ck_upload_callback($func, $file_url, '');
?>
