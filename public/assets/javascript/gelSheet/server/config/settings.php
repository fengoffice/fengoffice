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

	global $cnf ;
	global $debugging;
	
	define('OG_ROOT', dirname($_SERVER['SCRIPT_FILENAME'])."/../../../../../" );
	
	include_once OG_ROOT."/config/config.php";

	$debugging = true; //Set true iif on debugging mode
	
	# # # # # INSTALLATION DEPENDENT VARIABLES # # # # # #
	$cnf['db']['url'] 	 = DB_HOST;
	$cnf['db']['name'] 	 = DB_NAME;
	$cnf['db']['user'] 	 = DB_USER;
	$cnf['db']['pass']	 = DB_PASS;
	$cnf['db']['prefix'] = TABLE_PREFIX.'gs_';
	$cnf['site']['path']	= '.';
	
	# # # # # INSTALLATION DEPENDENT VARIABLES # # # # # #

	$cnf['application']['language'] = 'en';
	$cnf['application']['name'] = 'Gel';
	
	include_once ($cnf['site']['path']."/config/tableNames.php");
	include_once ($cnf['site']['path']."/config/classPath.php");

?>