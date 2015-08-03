<?php

/**
 * Tools for Feng Office development
 *
 * @version 1.0
 * @author Ignacio de Soto <ignacio.desoto@gmail.com>
 */
class ToolController extends ApplicationController {

	/**
	 * Construct the ToolController
	 *
	 * @access public
	 * @param void
	 * @return ToolController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'html');
	} // __construct

	function minify() {
		$this->setTemplate(get_template_path("empty"));
		
		if (!logged_user()->isAdministrator()) {
			die("You must be an administrator to run this tool.");
		}
		
		// include libraries
		include_once LIBRARY_PATH . '/jsmin/JSMin.class.php';
		include_once LIBRARY_PATH . '/cssmin/CSSMin.class.php';
		
		// process arguments
		$minify = isset($_GET['minify']);
		
		// process javascripts
		echo "Concatenating javascripts ... \n";
		$files = include "application/layouts/javascripts.php";
		
		$jsmin = "";
		foreach ($files as $file) {
			$jsmin .= file_get_contents("public/assets/javascript/$file") . "\n";
		}
		echo "Done!<br>\n";
		
		if ($minify) {
			echo "Minifying javascript ... \n";
			$jsmin = JSMin::minify($jsmin);
			echo "Done!<br>\n";
		}

		echo "Writing to file 'ogmin.js' ... ";
		file_put_contents("public/assets/javascript/ogmin.js", $jsmin);
		echo "Done!<br>";
		
		echo "<br>";
		
		
		// process CSS
		function changeUrls($css, $base) {
			return preg_replace("/url\s*\(\s*['\"]?([^\)'\"]*)['\"]?\s*\)/i", "url(".$base."/$1)", $css);
		}
		
		function parseCSS($filename, $filebase, $imgbase) {
			$css = file_get_contents($filebase.$filename);
			$imports = explode("@import", $css);
			$cssmin = changeUrls($imports[0], $imgbase);
			for ($i=1; $i < count($imports); $i++) {
				$split = explode(";", $imports[$i], 2);
				$import = trim($split[0], " \t\n\r\0\x0B'\"");
				$cssmin .= parseCSS($import, $filebase, $imgbase."/".dirname($import));
				$cssmin .= changeUrls($split[1], $imgbase);
			}
			return $cssmin;	
		}
		
		echo "Concatenating CSS ... ";
		$cssmin = parseCSS("website.css", "public/assets/themes/default/stylesheets/", ".");
		echo "Done!<br>";
		
		if ($minify) {
			echo "Minifying CSS ... ";
			$cssmin = CSSMin::minify($cssmin);
			echo "Done!<br>";
		}
		
		echo "Writing to file 'ogmin.css' ... ";
		file_put_contents("public/assets/themes/default/stylesheets/ogmin.css", $cssmin);
		echo "Done!<br>";
		die();
	}
	
	private function load_languages($dir, $from) {
		$handle = opendir($dir);
		$languages = array();
		while (false !== ($f = readdir($handle))) {
			if ($f != "." && $f != ".." && $f != "CVS" && $f != ".svn" && $f != $from && is_dir("$dir/$f")) {
				$languages[] = $f;
			}
		}
		closedir($handle);
		return $languages;
	}
	
	private function load_language_files(&$files, $dir, $base = '') {            
		if (!is_array($files)) $files = array();
		$handle = opendir($dir);
		while (false !== ($f = readdir($handle))) {
			if ($f == '.' || $f == '..' || $f == 'CVS' || $f == '.svn') continue;
			if (is_dir("$dir/$f")) {
				$this->load_language_files($files, "$dir/$f", $base . $f . "/");
			} else if (substr($f, -4) == '.php' || substr($f, -3) == '.js') {
				$files[] = $base . $f;
			}
		}
		closedir($handle);
	}

	private function load_language_files_plugins(&$files, $dir, $dir_plugin) {
		if (!is_array($files)) $files = array();
		$handle_plugin = opendir($dir_plugin);
		while (false !== ($f_p = readdir($handle_plugin))) {
			if ($f_p != "." && $f_p != ".." && $f_p != "CVS" && $f_p != ".svn" && is_dir("$dir_plugin/$f_p/$dir") && $f_p != 'custom_langs') {
				$handle = opendir($dir_plugin . "/" . $f_p . "/" . $dir);
				while (false !== ($f = readdir($handle))) {
					if ($f == '.' || $f == '..' || $f == 'CVS' || $f == '.svn') continue;
					if (substr($f, -4) == '.php') {
						$files[] = $f_p;
						//$files[] = array($f_p => $f);
					}
				}
				closedir($handle);
			}
		}
		closedir($handle_plugin);
	}
	
	private function load_file_translations($locale, $file) {
		if (substr($file, -4) == '.php' || substr($file, -3) == '.js') {
			$fullpath = LANG_DIR . "/" . $locale . "/" . $file;
		}else{
			$name_plugin = $file;
			$fullpath = PLUGIN_LANG_DIR . "/" . $name_plugin . "/" . LANG_DIR . "/" . $locale . "/lang.php";
		}

		if (!is_file($fullpath)) return array();
		if (substr($fullpath, -4) == ".php") {
			return include $fullpath;
		} else if (substr($fullpath, -3) == ".js") {
			$contents = file_get_contents($fullpath);
			$contents = preg_replace("/.*addLangs\s*\(\s*\{\s*/s", "", $contents);
			$contents = preg_replace("/\s*\}\s*\)\s*;\s*$/", "", $contents);
			$matches = array();
			preg_match_all("/\s*['\"](.*)['\"]\s*:\s*['\"](.*[^\\\\])['\"]\s*,?/", $contents, $matches, PREG_SET_ORDER);
			$lang = array();
			foreach ($matches as $match) {
				$lang[$match[1]] = $this->unescape_lang_js($match[2]);
			}
			return $lang;
		} else {
			return array();
		}
	}
	
	private function download_zip_lang($locale) {
		if (!zip_supported()) {
			die(lang("zip not supported"));
		}	
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
	
	private function escape_lang($string) {
		return str_replace(array("\\", "'", "\r\n", "\r"), array("\\\\", "\\'", "\n", "\n"), $string);
	}
	
	private function escape_lang_js($string) {
		return str_replace(array("\\", "'", "\r\n", "\r", "\n"), array("\\\\", "\\'", "\n", "\n", "\\n"), $string);
	}
	
	private function unescape_lang_js($string) {
		$count = strlen($string);
		$escaped = "";
		$bs = false;
		for ($i=0; $i < $count; $i++) {
			if ($bs) {
				if ($string[$i] == 'n') {
					$escaped .= "\n";
				} else if ($string[$i] == "'") {
					$escaped .= "'";
				} else if ($string[$i] == "\\") {
					$escaped .= "\\";
				} else {
					$escaped .= "\\" . $string[$i];
				}
				$bs = false;
			} else if ($string[$i] == "\\") {
				$bs = true;
			} else {
				$escaped .= $string[$i];
			}
		}
		return $escaped;
	}
	
	function translate() {
		if (!can_manage_security(logged_user())) {
			die(lang('no access permissions'));
		}
		
		if (!defined('LANG_DIR')) define('LANG_DIR', 'language');
		if (!defined('PLUGIN_LANG_DIR')) define('PLUGIN_LANG_DIR', 'plugins');
                
		$download = array_var($_GET, 'download');
		if (isset($download)) {
			// download zip file and die
			$this->download_zip_lang($download);
			die();
		}
		
		// save submissions
		$added = 0;
		$lang = array_var($_POST, 'lang');
		$file = array_var($_POST, 'file');
		$locale = array_var($_POST, 'locale');
		
		if (is_array($lang)) {
			
			if ($file != '') {
				// langs of one file submitted
				$added += $this->write_translations($locale, $file, $lang);
				
			} else {
				// langs of several files submitted (e.g.: a result of a search query)
				
				// load all files
				$from = array_var($_GET, 'from', 'en_us');
				$from_files = array();
				$this->load_language_files($from_files, LANG_DIR . "/$from");
				$this->load_language_files_plugins($from_files, LANG_DIR . "/$from", PLUGIN_LANG_DIR);
				
				// regroup langs foreach file
				$grouped_langs = array();
				$original_langs = array();
				foreach ($from_files as $f) {
					$original_langs[$f] = $this->load_file_translations($from, $f);
					$grouped_langs[$f] = array();
				}
				
				foreach ($lang as $key => $value) {
					// determine which file is foreach lang
					foreach ($original_langs as $fname => $langs) {
						// if found in a file => set the (key,value) in the grouped langs for this file and continue with next translation
						if (array_key_exists($key, $langs)) {
							$grouped_langs[$fname][$key] = $value;
						}
					}
				}
				
				// save each file
				foreach ($grouped_langs as $fname => $langs) {
					$added += $this->write_translations($locale, $fname, $langs);
				}
			}
			
		}
		
		// parameters
		$from = array_var($_GET, 'from', 'en_us');
		$to = array_var($_GET, 'to', '');
		$file = array_var($_GET, "file", "");
		$filter = array_var($_GET, "filter", "all");
		$start = array_var($_GET, 'start', 0);
		$pagesize = array_var($_POST, 'pagesize', array_var($_GET, 'pagesize', 30));
		$search = array_var($_REQUEST, 'search', '');
		
		// load languages
		$languages = $this->load_languages(LANG_DIR, $from);
		sort($languages);
		
		if ($to != "") {
			// load from files
			$from_files = array();
			$this->load_language_files($from_files, LANG_DIR . "/$from");
			$this->load_language_files_plugins($from_files, LANG_DIR . "/$from", PLUGIN_LANG_DIR);
			sort($from_files);
			tpl_assign('from_files', $from_files);
			
			if ($file != "") {
				
				tpl_assign('from_file_translations', $this->load_file_translations($from, $file));
				tpl_assign('to_file_translations', $this->load_file_translations($to, $file));
				
			} else {
				// filter by search criteria
				if ($search != '') {
					$from_file_langs = array();
					$to_file_langs = array();
					
					foreach ($from_files as $f) {
						$from_file_langs = array_merge($from_file_langs, $this->load_file_translations($from, $f));
					}
					foreach ($from_files as $f) {
						$to_file_langs = array_merge($to_file_langs, $this->load_file_translations($to, $f));
					}
						
					$from_filtered_langs = $this->filter_langs($from_file_langs, $search);
					$to_filtered_langs = $this->filter_langs($to_file_langs, $search);
					
					foreach ($from_filtered_langs as $k => $v) {
						if (isset($to_file_langs[$k])) $to_filtered_langs[$k] = $to_file_langs[$k];
					}
						
					tpl_assign('from_file_translations', $from_filtered_langs);
					tpl_assign('to_file_translations', $to_filtered_langs);
				}
			}
		}
		
		tpl_assign('added', $added);
		tpl_assign('from', $from);
		tpl_assign('to', $to);
		tpl_assign('file', $file);
		tpl_assign('filter', $filter);
		tpl_assign('search', $search);
		tpl_assign('start', $start);
		tpl_assign('pagesize', $pagesize);
		tpl_assign('languages', $languages);
		
	}
	
	/**
	 * Filter langs by key or value
	 * @param $langs: Array of traductions
	 * @param $filter: string to match in $langs array
	 * @return An array with the traductions that its key or value contains the string $filter
	 */
	private function filter_langs($langs, $filter) {
		$filtered = array();
		
		foreach ($langs as $key => $value) {
			if (strpos($key, $filter) !== false || strpos($value, $filter) !== false) {
				$filtered[$key] = $value;
			}
		}
		
		return $filtered;
	}
	
	/**
	 * Write language file
	 * @param $locale: the language (e.g.: es_la)
	 * @param $file: the language file to save
	 * @param $lang: An array (key => value) containing the translations 
	 */
	private function write_translations($locale, $file, $lang) {
		
		$create_plugin_lang_js = false;
		$check_root_file = false;
			
		if (substr($file, -4) == '.php' || substr($file, -3) == '.js') {
			$rootfile = LANG_DIR . "/" . $locale . ".php";
			$dirname = LANG_DIR . "/" . $locale;
			$filename = $dirname . "/" . $file;
			$check_root_file = true;
		} else {
			$name_plugin = $file;
			//$file = "lang.php";
			$rootfile = PLUGIN_LANG_DIR . "/" . $name_plugin . "/" .LANG_DIR . "/" . $locale . ".php";
			$dirname = PLUGIN_LANG_DIR . "/" . $name_plugin . "/" .LANG_DIR . "/" . $locale;
			$filename = $dirname . "/lang.php";
			$create_plugin_lang_js = true;
		}
			
		if ($check_root_file && !is_file($rootfile)) {
			$f = fopen($rootfile, "w");
			fwrite($f, '<?php if(!isset($this) || !($this instanceof Localization)) {
					throw new InvalidInstanceError(\'$this\', $this, "Localization", "File \'" . __FILE__ . "\' can be included only from Localization class");
				} ?>');
			fclose($f);
		}
			
		if (!is_dir($dirname)) {
			mkdir($dirname);
		}
		if (!is_file($filename)) {
			// create the file
			$f = fopen($filename, "w");
			fclose($f);
		}
		if ($create_plugin_lang_js) {
			$jsfilename = PLUGIN_LANG_DIR . "/$name_plugin/" .LANG_DIR . "/$locale/lang.js";
			if (!is_file($jsfilename)) {
				$f = fopen($jsfilename, "w");
				fwrite($f, 'locale = "'.$locale.'";
var langObj = {};
<?php $lang_array = include "lang.php"; ?>
<?php foreach ($lang_array as $k => $v): ?>
langObj["<?php echo $k ;?>"] = "<?php echo $v ;?>";
<?php endforeach ;?>
addLangs(langObj);');
				fclose($f);
			}
		}
		
		$added = 0;
		$all = $this->load_file_translations($locale, $file);
		if (!is_array($all)) $all = array();
		foreach ($lang as $k => $v) {
			if (trim($v) != "") {
				if (!isset($all[$k])) {
					$added++;
				}
				$all[$k] = $v;
			}
		}
		
		$f = fopen($filename, "w");
		// write the translations to the file
		if (substr($filename, -4) == ".php") {
			fwrite($f, "<?php return array(\n");
			foreach ($all as $k => $v) {
				fwrite($f, "\t'$k' => '" . $this->escape_lang("$v"). "',\n");
			}
			fwrite($f, "); ?>\n");
		} else if (substr($filename, -3) == ".js") {
			$total = count($all);
			fwrite($f, "locale = '$locale';\n");
			fwrite($f, "addLangs({\n");
			$count = 0;
			foreach ($all as $k => $v) {
				$count++;
				fwrite($f, "\t'$k': '" . $this->escape_lang_js($v). "'");
				if ($count == $total) {
					fwrite($f, "\n");
				} else {
					fwrite($f, ",\n");
				}
			}
			fwrite($f, "});\n");
		}
		fclose($f);
		
		return $added;
	}
	
	function checklang() {
		if (!defined('LANG_DIR')) define('LANG_DIR', 'language');
		if (!defined('PLUGIN_LANG_DIR')) define('PLUGIN_LANG_DIR', 'plugins');
		
		$from = array_var($_GET, 'from', 'en_us');
		$to = array_var($_GET, "to");
		$languages = $this->load_languages(LANG_DIR, $from);
		
		tpl_assign('from', $from);
		tpl_assign('to', $to);
		tpl_assign('languages', $languages);
		
		if ($to) {
			$missing = array();
			$files = array();
			
			$this->load_language_files($files, LANG_DIR . "/$from");
			foreach ($files as $file) {
				if (is_file(LANG_DIR . "/$to/$file")) {
					$missing[$file] = array();
					$ft = $this->load_file_translations($from, $file);
					$tt = $this->load_file_translations($to, $file);
					foreach ($ft as $k => $v) {
						if (!isset($tt[$k])) {
							$missing[$file][$k] = $v;
						}
					}
				} else {
					$missing[$file] = "missing file";
				}
			}
			
			$this->load_language_files_plugins($plugins, LANG_DIR . "/$from", PLUGIN_LANG_DIR);
			foreach ($plugins as $plugin) {
				if (is_file(PLUGIN_LANG_DIR . "/$plugin/" . LANG_DIR . "/$to/lang.php")) {
					$missing[$plugin] = array();
					$ft = $this->load_file_translations($from, $plugin);
					$tt = $this->load_file_translations($to, $plugin);
					foreach ($ft as $k => $v) {
						if (!isset($tt[$k])) {
							$missing[$plugin][$k] = $v;
						}
					}
				} else {
					$missing[$plugin] = "missing file";
				}
			}
			
			tpl_assign('missing', $missing);
		}
	}
	
} // ToolController


