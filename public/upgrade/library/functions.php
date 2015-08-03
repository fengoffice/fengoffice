<?php

// ---------------------------------------------------
//  Upgrader specific
// ---------------------------------------------------

/**
 * Compare two upgrader scripts by version from
 *
 * @param ScriptUpgraderScript $script1
 * @param ScriptUpgraderScript $script2
 * @return integer
 */
function compare_scripts_by_version_from($script1, $script2) {
	if(!($script1 instanceof ScriptUpgraderScript) || !($script2 instanceof ScriptUpgraderScript)) {
		return 0;
	} // if
	return version_compare($script1->getVersionFrom(), $script2->getVersionFrom());
} // compare_scripts_by_version_from

/**
 * Compare two upgrader scripts by version to
 *
 * @param ScriptUpgraderScript $script1
 * @param ScriptUpgraderScript $script2
 * @return integer
 */
function compare_scripts_by_version_to($script1, $script2) {
	if(!($script1 instanceof ScriptUpgraderScript) || !($script2 instanceof ScriptUpgraderScript)) {
		return 0;
	} // if
	return version_compare($script1->getVersionTo(), $script2->getVersionTo());
} // compare_scripts_by_version_to

/**
 * Dump an error
 *
 * @param Exception $exception
 * @return null
 */
function dump_upgrader_exception($exception) {
	print '<pre style="text-align: left">' . $exception->__toString() . '</pre>';
} // dump_upgrader_exception

// ---------------------------------------------------
//  Templates
// ---------------------------------------------------

/**
 * Return full path of specific template file
 *
 * @param string $tpl_file
 * @return string
 */
function get_template_path($tpl_file) {
	return UPGRADER_PATH . '/templates/' . $tpl_file . '.php';
} // get_template_path

/**
 * Assign template variable.
 *
 * If you want to assign multiple variables with one call pass associative array
 * through $varname. In that case $varvalue will be ignored!
 *
 * @param mixed $varname Variable name or associative array of variables that need
 *   to be assigned
 * @param mixed $varvalue Variable name. If $varname is array this param is ignored
 * @return boolean
 */
function tpl_assign($varname, $varvalue = null) {
	$template_instance = Template::instance();
	if(is_array($varname)) {
		foreach($varname as $k => $v) {
			$template_instance->assign($k, $v);
		} // foreach
	} else {
		$template_instance->assign($varname, $varvalue);
	} // if
} // tpl_assign

/**
 * Render template and return it as string
 *
 * @param string $template Template that need to be rendered
 * @return boolean
 */
function tpl_fetch($template) {
	$template_instance = Template::instance();
	return $template_instance->fetch($template);
} // tpl_fetch

/**
 * Render specific template
 *
 * @param string $template Template that need to be rendered
 * @return boolean
 */
function tpl_display($template) {
	$template_instance = Template::instance();
	return $template_instance->display($template);
} // tpl_display

/**
 * Return installed version, wrapper function.
 *
 * @param void
 * @return string
 */
function installed_version() {
	$version = @include ROOT . '/config/installed_version.php';
	if ($version) {
		return $version;
	} else {
		return "unknown";
	}
} // installed_version

/**
 * Deletes a directory and all of its contents
 * @return unknown_type
 */
function unlink_dir($dir) {
	$dh = @opendir($dir);
	if (!is_resource($dh)) return;
    while (false !== ($obj = readdir($dh))) {
		if($obj == '.' || $obj == '..') continue;
		$path = "$dir/$obj";
		if (is_dir($path)) {
			unlink_dir($path);
		} else {
			@unlink($path);
		}
	}
	@closedir($dh);
	@rmdir($dir);
}

function lang($name) {

	// Get function arguments and remove first one.
	$args = func_get_args();
	if(is_array($args)) array_shift($args);

	// Get value and if we have NULL done!
	$value = Localization::instance()->lang($name);
	if(is_null($value)) return $value;

	// We have args? Replace all {x} with arguments
	if(is_array($args) && count($args)) {
		$i = 0;
		foreach($args as $arg) {
			$value = str_replace('{'.$i.'}', $arg, $value);
			$i++;
		} // foreach
	} // if

	// Done here...
	return $value;

} // lang

function help_link() {
	$link = Localization::instance()->lang('wiki help link');
	if (is_null($link)) {
		$link = DEFAULT_HELP_LINK;
	}
	return $link;
}


function feng_upg_autoload($load_class_name) {
	static $loader ;
	$class_name = strtoupper($load_class_name);

	// Try to get this data from index...
	if(isset($GLOBALS[AutoLoader::GLOBAL_VAR])) {
		if(isset($GLOBALS[AutoLoader::GLOBAL_VAR][$class_name])) {
			return include $GLOBALS[AutoLoader::GLOBAL_VAR][$class_name];
		}
	}
	
	if(!$loader) {
		$loader = new AutoLoader();
		$loader->addDir(ROOT . '/application');
		$loader->addDir(ROOT . '/environment');
		$loader->addDir(ROOT . '/library');
		
		//TODO Pepe: No tengo la conexion ni las clases de DB en este momento.. me conecto derecho 
		$temp_link  = mysql_connect(DB_HOST, DB_USER, DB_PASS) ;
		mysql_select_db(DB_NAME) ;
		$res = mysql_query("SELECT name FROM ".TABLE_PREFIX."plugins WHERE is_installed = 1 AND is_activated = 1;");
		while ($row = mysql_fetch_object($res)) {	
			$plugin_name =  strtolower($row->name) ;
			$dir  = ROOT . '/plugins/'.$plugin_name.'/application' ;
			if (is_dir($dir)) {
				$loader->addDir($dir); 
			}
		}
		mysql_close($temp_link);
		
		
		$loader->setIndexFilename(ROOT . '/cache/autoloader.php');
		
	}

	try {
		$loader->loadClass($class_name);
	} catch(Exception $e) {
		try {
			if (function_exists("__autoload")) __autoload($class_name);
		} catch(Exception $ex) {
			die('Caught Exception in AutoLoader: ' . $ex->__toString());
		}
	}
}

if (!function_exists('massiveInsert')) {
	function massiveInsert($tableName, $cols,  $rows, $packageSize = 100, $on_duplicate_key="") {

		$total = count($rows);
		$totalPackets = ceil($total/$packageSize);
		$cols = implode(",", $cols);
		for ($i = 0 ; $i < $totalPackets ; $i++ ) {
			$sql = "INSERT INTO $tableName ($cols) VALUES  ";
			for ($j = $i * $packageSize ; $j < min ( ($i+1) * $packageSize , $total ) ; $j++ ) {
				$sql.= " (";
				$sql.="'".implode("','",$rows[$j])."'";
				$sql.=")";
				if ($j + 1 <  min ( ($i+1) * $packageSize , $total ) ){
					$sql.=",";
				}
			}
			
			$sql .= $on_duplicate_key;
			
			if (!DB::execute($sql)){
				throw new DBQueryError($sql);
			}
		}
	} 
}

if (!function_exists('html_to_text')) {
	function html_to_text($html) {
		include_once ROOT . "/library/html2text/class.html2text.inc";
		$h2t = new html2text($html);
		return $h2t->get_text(); 
	}
}

if (!function_exists('utf8_safe')) {
	function utf8_safe($text) {
		$safe = html_entity_decode(htmlentities($text, ENT_COMPAT, "UTF-8"), ENT_COMPAT, "UTF-8");
		return preg_replace('/[\xF0-\xF4][\x80-\xBF][\x80-\xBF][\x80-\xBF]/', "", $safe);
	}
}

if (!function_exists('is_exec_available')) {
	function is_exec_available() {
		if (ini_get('safe_mode')) {
			return false;
		} else {
			$d = ini_get('disable_functions');
			$s = ini_get('suhosin.executor.func.blacklist');
			if ("$d$s") {
				$array = preg_split('/,\s*/', "$d,$s");
				if (in_array('exec', $array)) {
					return false;
				}
			}
		}
		return true;
	}
}
?>