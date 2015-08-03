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


function MenuBar(){
	//var self = new ToolBar("CommandBar",0,0,50,22);
	var self = new ToolBar("CommandBar",0,0,365,24);
	var item = new CommandButton("Save As","./toolbar/img/icons/saveAs-32x32.png" ,"Save As",window.saveBookConfirm);
	var save = new CommandButton("Save","./toolbar/img/icons/save-32x32.png" ,"Save Book",window.editBook);

	var item2 = new CommandButton("Load","./toolbar/img/icons/open-32x32.png" ,"Open Book",loadBookWindow);
	var item3 = new CommandButton("New","./toolbar/img/icons/new-32x32.png" ,"New Book",window.newBookConfirm);
	var item4 = new CommandButton("ExportToPDF","./toolbar/img/icons/PDF-32x32.png" ,"Export to PDF",window.exportPDF);
	var item5 = new CommandButton("ExportToXLS","./toolbar/img/icons/XLS-32x32.png" ,"Export to XLS",window.exportXLS);
	var item6 = new CommandButton("ExportToODS","./toolbar/img/icons/ODS-48x48.png" ,"Export to ODS",window.exportODS);
	var item7 = new CommandButton("ExportToXLSX","./toolbar/img/icons/excel_2007_icon2.png" ,"Export to XLSX",window.exportXLSX);
	var item8 = new CommandButton("ExportToHTML","./toolbar/img/icons/HTML2-32x32.png" ,"Export to HTML",window.exportHTML);
	var separator1 = new CommandButton("","./toolbar/img/icons/separator32x8.png" ,"","");
	var separator2 = new CommandButton("","./toolbar/img/icons/separator32x8.png" ,"","");
	//Icons from:www.iconarchive.com
	self.appendChild(save);
	self.appendChild(item);
	self.appendChild(item2);
	self.appendChild(item3);
	self.appendChild(separator1);
	self.appendChild(item4);
	self.appendChild(item5);
	self.appendChild(item6);
	self.appendChild(item7);
	//self.appendChild(item8);
	self.appendChild(separator2);
	self.appendChild(new CommandButton("Bold","./toolbar/img/icons/bold.png" ,"Bold",cmdSetBoldStyle) );
	self.appendChild(new CommandButton("Italic","./toolbar/img/icons/italic.png" ,"Italic",cmdSetItalicStyle) );
	self.appendChild(new CommandButton("Underline","./toolbar/img/icons/underline.png" ,"Underline",cmdSetUnderlineStyle) );
	self.appendChild(new CommandButton("","./toolbar/img/icons/separator32x8.png" ,"","") );

	return self;
}


function FormatBar(){
		//var self = new ToolBar("FormatBar",0,50,500,22);
		var self = new ToolBar("FormatBar",0,285,365,24);
		//self.addItem(new CommandButton("Bold","./toolbar/img/google_icons/bold.gif" ,"Bold",cmdSetBoldStyle));
		//self.addItem(new CommandButton("Italic","./toolbar/img/icons/italic.png" ,"Italic",cmdSetItalicStyle));
		//self.addItem(new CommandButton("Underline","./toolbar/img/icons/underline.png" ,"Underline",cmdSetUnderlineStyle));
		self.addItem(new CommandButton("FontColor","./toolbar/img/icons/font-color.png" ,"FontColor",window.fontColorPalette ));
		self.addItem(new CommandButton("BgColor","./toolbar/img/icons/background-color.png" ,"BgColor",window.bgColorPalette));
/*		self.addItem(new CommandButton("AlignLeft","./toolbar/img/gle_icons/align_left.gif" ,"AlignLeftr",window.borrar));
		self.addItem(new CommandButton("AlignCenter","./toolbar/img/gle_icons/align_center.gif" ,"BgColor",window.borrar));
		self.addItem(new CommandButton("AlignRight","./toolbar/img/gle_icons/align_right.gif" ,"BgColor",window.borrar));
		self.addItem(new CommandButton("VAlignTop","./toolbar/img/gle_icons/valign_top.gif" ,"BgColor",window.borrar));
		self.addItem(new CommandButton("VAlignCenter","./toolbar/img/gle_icons/valign_center.gif" ,"BgColor",window.borrar));
		self.addItem(new CommandButton("VAlignButtons","./toolbar/img/gle_icons/valign_button.gif" ,"BgColor",window.borrar));
*/
		//var fontsArray = new Array("Arial","Courier","Times New Roman","Verdana");
		var fontCombo = new CommandFontCombo("FontSelector","./toolbar/img/gle_icons/valign_button.gif" ,"Font",cmdSetFontStyle,window.Fonts);

		var fontSizeArray = new Array();
		fontSizeArray["8"]  = "8 pt";
		fontSizeArray["9"]  = "9 pt";
		fontSizeArray["10"] = "10 pt";
		fontSizeArray["11"] = "11 pt";
		fontSizeArray["12"] = "12 pt";
		fontSizeArray["14"] = "14 pt";
		fontSizeArray["16"] = "16 pt";
		fontSizeArray["18"] = "18 pt";
		fontSizeArray["24"] = "24 pt";

		var fontSizeCombo = new CommandFontSizeCombo("FontSelector","./toolbar/img/gle_icons/valign_button.gif" ,"Font",cmdSetFontSizeStyle,fontSizeArray);

		self.addItem(fontCombo);
		self.addItem(fontSizeCombo);

		return self;
}

function loadToolbars(application, section){

	var menuBar = new MenuBar();
	section.appendChild(menuBar);
	application.menuBar = menuBar;
	var formatBar = new FormatBar();
	section.appendChild(formatBar);
	application.formatBar = formatBar;
}

function loadBookWindow(){
	fileDialog.show();
}



//A lo pepe
function saveBookConfirm() {
	var valid_name = /([a-zA-Z0-9_-]+)$/;
	Ext.MessageBox.prompt('Save As..', 'Enter a file name', showResultText);

	function showResultText(btn, text){
        //Ext.example.msg('Forma prolija', 'Has hecho clikc en  {0} y vas a guardar el excel : "{1}".', btn, text);
        if (btn == 'ok') {
        	if ( valid_name.test(text) ) {
        		if (text.substring(text.length - 4) != ".gel") {
					text += ".gel";
				}
        		window.saveBook(text);
        	}else {
        		Ext.MessageBox.prompt('Save As..', 'Enter a valid file name', showResultText);
        	}
        }else {

        }
    };
}

function editBook() {
	window.saveBook() ;
}


function newBookConfirm() {
//	alert ('entra');
	Ext.MessageBox.show({
	    title:'Save Changes?',
	    msg: 'You are about to close a document that has unsaved changes. <br />Would you like to continue?',
//	    buttons: Ext.MessageBox.YESNOCANCEL,
	    buttons: Ext.MessageBox.YESNO,
	    fn: showResult ,
	    animEl: 'New',
	    icon: Ext.MessageBox.QUESTION
	});

	function showResult(btn) {

		switch(btn) {
			case 'cancel' :
			break;

			case 'yes' :
//				saveBookConfirm();
				newBook();
			break;

			case 'no' :
			// call to something to clean
//				newBook();
			break;

		}
	}

}

function export (format){
	window.saveBook('',format);
}

function exportPDF() {
	window.export('pdf');
}

function exportXLS() {
	window.export('xls');
}

function exportXLSX() {
	window.export('xlsx');
}

function exportHTML() {
	window.export('html');
}

function exportODS() {
	window.export('ods');
}
