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
//include_once 'config/settings.php';

include_once "lang_list.php" ;



function language($text) {
	global $cnf;
	global $lang;
	
	$current_lang = $cnf['application']['language'];
	$default_lang = 'en' ;
	
	if (isset($lang[$current_lang][$text]))
		return $lang[$current_lang][$text] ; 

	elseif (isset($lang[$default_lang][$text]))
		return $lang[$default_lang][$text];
	else
		return $text ;
	
}


?>