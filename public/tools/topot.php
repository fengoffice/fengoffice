<?php
$allowed = include 'access.php';
if (!in_array('topot.php', $allowed)) die("This tool is disabled.");

define('LANG_DIR', 'language');
chdir("../.."); 

function loadFileTranslations($locale, $file) {
	if (substr($file, -4) == ".php") {
		return include LANG_DIR . "/" . $locale . "/" . $file;
	} else if (substr($file, -3) == ".js") {
		$contents = file_get_contents(LANG_DIR . "/" . $locale . "/" . $file);
		$contents = preg_replace("/.*addLangs\s*\(\s*\{\s*/s", "", $contents);
		$contents = preg_replace("/\s*\}\s*\)\s*;\s*$/", "", $contents);
		$matches = array();
		preg_match_all("/\s*'(.*)'\s*:\s*'(.*[^\\\\])'\s*,?/", $contents, $matches, PREG_SET_ORDER);
		$lang = array();
		foreach ($matches as $match) {
			$lang[$match[1]] = $match[2];
		}
		return $lang;
	} else {
		return array();
	}
}

function download_zip() {
	$filename = "tmp/$locale.zip";
	if (is_file($filename)) unlink($filename);
	$zip = new ZipArchive();
	$zip->open($filename, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
	$zip->addFile(LANG_DIR . "/$locale.php", "$locale.php");
	$zip->addEmptyDir($locale);
	$dir = opendir(LANG_DIR . "/" . $locale);
	while (false !== ($file = readdir($dir))) {
		if ($file != "." && $file != ".." && $file != "CVS") {
			$zip->addFile(LANG_DIR . "/$locale/$file", "$locale/$file");
		}
	}
	closedir($dir);
	$zip->close();
	header("Cache-Control: public");
	header("Expires: -1");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Content-Type: application/zip");
	header("Content-Length: " . (string) filesize($filename));
	header("Content-Disposition: 'attachment'; filename=\"$locale.zip\"");
	header("Content-Transfer-Encoding: binary");
	readfile($filename);
	die();
}

// load translation files
if (isset($_GET['to'])) {
	$translated = array();
}
$translations = array();
$handle = opendir(LANG_DIR . "/en_us");
while (false !== ($file = readdir($handle))) {
	if ($file != "." && $file != ".." && $file != "CVS") {
		$translations[$file] = loadFileTranslations("en_us", $file);
		if (isset($_GET['to'])) {
			$translated[$file] = loadFileTranslations($_GET["to"], $file);
		}
	}
}
closedir($handle);
// finished loading translation files

header("Content-Type: text/plain; charset=UTF-8");

$header = 'msgid ""
msgstr ""
"Project-Id-Version: Feng Office '.(include 'version.php').'\n"
"POT-Creation-Date: '.date("Y-m-d H:iO").'\n"
"PO-Revision-Date: \n"
"Last-Translator: \n"
"Language-Team: \n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"
"Plural-Forms: nplurals=2; plural=(n != 1);\n"

';
$zipname = "tmp/gettext.zip";
if (is_file($zipname)) unlink($zipname);
$zip = new ZipArchive();
$zip->open($zipname, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
foreach ($translations as $file => $pairs) {
	$filename = "tmp/$file.pot";
	if (is_file($filename)) unlink($filename);
	$f = fopen($filename, "wb");
	fwrite($f, $header);
	foreach ($pairs as $key => $value) {
		$value = str_replace(array('\\', '"'), array('\\\\', '\\"'), $value);
		fwrite($f, "msgctxt \"$key\"\n");
		fwrite($f, "msgid \"$value\"\n");
		if (isset($_GET["to"])) {
			$text = $translated[$file][$key];
			$text = str_replace(array('\\', '"'), array('\\\\', '\\"'), $text);
			fwrite($f, "msgstr \"$text\"\n\n");
		} else {
			fwrite($f, "msgstr \"\"\n\n");
		}
	}
	fclose($f);
	if (isset($_GET["to"])) {
		$zip->addFile($filename, "$file.po");
	} else {
		$zip->addFile($filename, "$file.pot");
	}
}
$zip->close();
header("Cache-Control: public");
header("Expires: -1");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Content-Type: application/zip");
header("Content-Length: " . (string) filesize($zipname));
if (isset($_GET["to"])) {
	header("Content-Disposition: 'attachment'; filename=\"".$_GET["to"].".zip\"");
} else {
	header("Content-Disposition: 'attachment'; filename=\"gettext.zip\"");
}
header("Content-Transfer-Encoding: binary");
readfile($zipname);
die();
?>
