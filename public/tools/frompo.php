<?php
$allowed = include 'access.php';
if (!in_array('frompo.php', $allowed)) die("This tool is disabled.");

/**
 * This tool searches for files ending in .po in the language folder and converts them to PHP or JS files.
 **/

define('LANG_DIR', '../../language');

header("Content-type: text/plain;charset=utf-8");

function loadPOFiles($dir, &$po_files) {
	$handle = opendir($dir);
	while (false !== ($file = readdir($handle))) {
		if ($file == "." || $file == ".." || $file == "CVS") {
			continue;
		} else if (is_dir($dir . "/$file")) {
			loadPOFiles($dir . "/$file", $po_files);
		} else if (substr($file, -3) == ".po") {
			$po_files[] = $dir . "/$file";
		}
	}
	closedir($handle);
}

function convertPOFile($file) {
	$contents = file_get_contents($file);
	$contents = str_replace(array("\n", "\r"), "", $contents);
	$contents = preg_replace("/^.*?msgctxt/", "msgctxt", $contents);
	$contents = preg_replace("/msgid.*?msgstr/", "msgstr", $contents);
	$contents = preg_replace("/([^\\\\])\"\s*\"/", "$1", $contents);
	$contents = substr($contents, strpos($contents, "msgctxt") + 7);
	
	$basename = substr($file, 0, -3);
	if (substr($basename, -4) == 'lang') {
		$basename .= ".js";
	} else if (substr($basename, -3) != '.js' && substr($basename, -4) != '.php') {
		$basename .= ".php";
	}
	if (substr($basename, -3) == '.js') {
		$locale = substr(substr($basename, 0, strrpos($basename, "/")), -5);
		$contents = str_replace(array("msgctxt", "msgstr"), array(",\n", ":"), $contents);
		$contents = "locale = '$locale';\naddLangs({\n$contents\n});";
	} else {
		$contents = str_replace(array("msgctxt", "msgstr"), array(",\n", " =>"), $contents);
		$contents = "<?php return array(\n$contents,\n); ?>";
	}
	$f = fopen($basename, "wb");
	fwrite($f, $contents);
	fclose($f);
}

$po_files = array();
loadPOFiles(LANG_DIR, $po_files);
foreach ($po_files as $po) {
	echo "Converting $po...\n";
	convertPOFile($po);
}
echo "Finished!";

?>