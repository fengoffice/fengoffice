/************* auxiliary functions **************/ 

// Generic function to save/export into differents formts including Database
//function export (format){
//	window.saveBook('',format);
//}


/************* callback functions ***************/



//SAVE AS
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

//function exportPDF() {
//	window.exportBook('pdf');
//}
//
//function exportXLS() {
//	window.exportBook('xls');
//}
//
//function exportXLSX() {
//	window.exportBook('xlsx');
//}
//
//function exportHTML() {
//	window.exportBook('html');
//}
//
//function exportODS() {
//	window.exportBook('ods');
//}
//
//function undo() {
//	window.model.undo() ;
//}
//
//function redo() {
//	window.model.redo() ;
//}

function bold() {
	cmdSetBoldStyle() ;
}

function italic() {
	cmdSetItalicStyle() ;
}

function underline() {
	cmdSetUnderlineStyle() ;
}

function unformat() {
	cmdSetFontStyleId(0) ;
}


function setBorderLeft() {
	var cell = application.grid.activeCell ;
	cell.style.borderLeft = "5px solid #000000" ; 
}

function setBorderRight() {

}

function setBorderTop() {

}
function setBorderNone() {

}

function refresh() {
	application.refresh() ;
}	

