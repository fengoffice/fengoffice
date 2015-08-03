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

/*
* fsc prefix denotes Font Style Controller
* All Font Style Operations are defined here
* Interacts with Logic Cells and Font Styles throw StyleHandler
*/
function toBool(val){
	if(val)
		return 1;
	else
		return 0;
}

function toBoolFromString(val){
	if(parseInt(val))
		return true;
	else
		return false;
}

function fscChangeBold(object){
	var fstyle = styleHandler.getFontStyle(object.getFontStyleId());
	var oldValue = fstyle.bold;
	var fsId = styleHandler.getStyleId(fstyle.font,fstyle.size,fstyle.color,!oldValue,fstyle.italic);
	object.setFontStyleId(fsId);
	return oldValue;
}

