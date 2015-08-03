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


function JsonHandler(){
	var self = this;
	
	self.exportSheet = function(sheet){
	    var formula = 'null';
	    var json = '{"sheetId":null,"sheetName":"sheet1","cells":[';
	    var cells = "";
	    for(var i=0;i<sheet.cells.length;i++){
	    	if(sheet.cells[i])
	        	for(var j=0;j<sheet.cells[i].length;j++){
	                if(sheet.cells[i][j]){
	                	formula = sheet.cells[i][j].getFormula();
	                	if(formula ==undefined)
	                		formula = 'null' ;
	                	else
	                		formula = '"'+ addslashes(formula) +'"' ;

	                	value = sheet.cells[i][j].getValue();
	                	if(value ==undefined)
	                		value = 'null' ;
	                	else
	                		value = '"'+ addslashes(value) +'"' ;
	      
	                	cells+=',{"dataRow":"' + i +'","dataColumn":"'+j+'","cellFormula":'+ formula +',"cellValue":'+ value + ',"fontStyleId":"'+ sheet.cells[i][j].getFontStyleId() + '","layoutStyleId":"0"}';
	                }
	        	}
	    }
	    json += cells.substr(1);
	    json += "]"; //End of Cells
	    json += "}"; //End of Sheet
	    return json;
	};

	self.exportBook = function(id,book,sheet){
		if(id==undefined) id = "null";
		var json = '{"bookId":'+ id + ',"bookName":"'+ book.getName()+'"';
	    json +=	',"sheets":['; 				//Start of Sheets Array
		json += self.exportSheet(sheet);
	 	json += ']'; 						//End of Sheets Array
	 	json += ',"fontStyles":'+self.exportFontStyles();
	    json += "}"; 						//End of Book
	    return json;
	};
	
	self.exportFontStyles = function(){
		var styles = Styler.getAllFontsStyles();
		var json = "";
		var align = 0;
		var valign = 0;
		for(var item in styles){
			align = Styler.getAlignId(styles[item].align);
			valign = Styler.getValignId(styles[item].valign);
		
			if(item!="remove")
				json+=',{"fontStyleId":"' + item +'","fontId":"'+(styles[item].font )+'","fontBold":"'+toBool(styles[item].bold)+'","fontItalic":"'+toBool(styles[item].italic)+'","fontSize":"'+styles[item].size+'","fontColor":"'+styles[item].color+'","fontUnderline":"'+toBool(styles[item].underline)+'","fontHAlign":'+align+',"fontVAlign":'+valign+'}';
		}
		
		json = '['+ json.substr(1) + ']';
		return json;
	};
	
	
	self.importBook = function(configs,data){  //TODO:configs must be setted on jsondata
		var book = new Book(data.name);
		book.setId (data.id) ;
/*		application.activeBook.setId(data.id);
		application.setBookName(data.name);
*/		self.importFontStyles(data.fontStyles);
		var sheet = self.importSheet(configs,data.sheets[0]); //TODO:load all sheets when implemented
		book.setSheet(sheet);
		return book
	};
	
	self.importSheet = function(configs,data){ //TODO:configs must be setted on jsondata
		var sheet = new Sheet(configs);
		var cells = data.cells;
		for(var i=0;i<cells.length;i++){
			sheet.setFormula(cells[i].r,cells[i].c,stripslashes(cells[i].f), true);
			sheet.setCellFontStyleId(cells[i].r,cells[i].c,cells[i].fs, true);
		}
		return sheet;
	};
	
	self.importFontStyles = function(data){
		var fontStyles = data;
    	for ( var i=0 ; i < fontStyles.length ; i++ ) {
    		fontStyleId 	= parseInt(fontStyles[i].fontStyleId) ;
    		fontId			= parseInt(fontStyles[i].fontId );
    		fontSize		= parseFloat(fontStyles[i].fontSize) ;
    		fontBold		= toBoolFromString(fontStyles[i].fontBold) ;
    		fontItalic		= toBoolFromString(fontStyles[i].fontItalic) ;
    		fontUnderline 	= toBoolFromString(fontStyles[i].fontUnderline) ;
    		fontColor		= fontStyles[i].fontColor ;
    		fontAlign  		= Styler.getAlignName(fontStyles[i].fontHAlign);
    		fontValign  	= Styler.getValignName(fontStyles[i].fontVAlign);
    		Styler.addFontStyle(fontStyleId , fontId, fontSize, fontColor, fontBold, fontItalic, fontUnderline,fontAlign,fontValign) ;
    	}
	};
}