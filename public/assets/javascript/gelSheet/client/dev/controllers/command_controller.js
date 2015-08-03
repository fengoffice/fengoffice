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
function cmdSetBoldStyle(){
	application.model.changeBoldToSelection();
}
function cmdSetFontStyleId(fsId){
	application.model.setSelectionFontStyleId(fsId);
}

function cmdSetItalicStyle(){
	application.model.changeItalicToSelection();
}

function cmdSetUnderlineStyle(){
	application.model.changeUnderlineToSelection();
}

function cmdSetFontStyle(font){
	application.model.changeFontToSelection(font);
}

function cmdSetFontColor(color){
	application.model.changeFontColorToSelection(color) ;
}

function cmdSetBgColor(color){
	application.model.changeBgColorToSelection(color) ;
}


function cmdSetFontSizeStyle(size){
	application.model.changeFontSizeToSelection(size);
}

function cmdSetAlignStyle(align){
	application.model.changeAlignToSelection(align);
}

function cmdSetValignStyle(valign){
	application.model.changeValignToSelection(valign);
}


function cmdSetLeftAlign(){
	var selection = window.SelectionMan.getSelection();
	var i =0;
//	for (var i=0;i<selection.length;i++){
		var address = selection[i].getAddress();
		var range = scGetCell(activeSheet,address.row,address.col);
		var fstyle = window.styleHandler.getLayoutStyleById(range.getFontStyleId());
		var italic = !fstyle.italic;
		var newStyle = window.styleHandler.getLayoutStyleId(fstyle.font,fstyle.size,fstyle.color,fstyle.bold,italic,fstyle.underline);
		range.setLayoutStyleId(newStyle);
	//}

	EventManager.fire(EVT_CELL_FONT_STYLE_CHANGE,newStyle);
}



/*******************************************************************************/
/***************************Book Operations ************************************/
function cmdSetBookName(name){
	activeBook.setName(name);
	EventManager.fire(EVT_BOOK_NAME_CHANGE,name);
}



