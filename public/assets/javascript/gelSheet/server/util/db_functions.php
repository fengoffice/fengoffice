<?php
/*  Gelsheet Project, version 0.0.1 (Pre-alpha)
 *  Copyright (c) 2008 - Ignacio Vazquez, Fernando Rodriguez, Juan Pedro del Campo
 *
 *  Ignacio "Pepe" Vazquez <elpepe22@users.sourceforge.net>
 *  Fernando "Palillo" Rodriguez <fernandor@users.sourceforge.net>
 *  Juan Pedro "Perico" del Campo <pericodc@users.sourceforge.net>
 *
 *  Gelsheet is free distributable under the terms of an GPL license.
 *  For details see: http://www.gnu.org/copyleft/gpl.html
 *
 */

// A Kind of Database Abstraction Layer...
// no so abstract yet ! (dont worry, will be abstract soon ;)
function table ($table) {
	global $cnf ;	
	return (isset($cnf['tableName'][$table]))?$cnf['tableName'][$table]:$cnf['db']['prefix'].$table;
}

/************  not used ****************/

function db_prefix_tables($sql) {
	$db_prefix = '';

  if (is_array($db_prefix)) {
    if (array_key_exists('default', $db_prefix)) {
      $tmp = $db_prefix;
      unset($tmp['default']);
      foreach ($tmp as $key => $val) {
        $sql = strtr($sql, array('{' . $key . '}' => $val . $key));
      }
      return strtr($sql, array('{' => $db_prefix['default'], '}' => ''));
    }
    else {
      foreach ($db_prefix as $key => $val) {
        $sql = strtr($sql, array('{' . $key . '}' => $val . $key));
      }
      return strtr($sql, array('{' => '', '}' => ''));
    }
  }
  else {
    return strtr($sql, array('{' => $db_prefix, '}' => ''));
  }
}

function db_query($sql){
	$newSQL = db_prefix_tables($sql);
	return mysql_query ( $newSQL );
}

function db_fetch_object($result) {
	return mysql_fetch_object($result);
}

function db_fetch_array($result) {
	return mysql_fetch_assoc($result);
}

/**************************************/


?>