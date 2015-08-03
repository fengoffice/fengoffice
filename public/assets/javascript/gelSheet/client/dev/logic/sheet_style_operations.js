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
function addSheetStyleOperations(sheet){
	//Gets the Font Style Id of a Column if exists else returns default
	sheet.getColumnFontStyleId = function(colIndex){
		if(sheet.cols[colIndex])
			return sheet.cols[colIndex].getFontStyleId();
		else
			return sheet.defaultFontStyleId;
	}
	//Gets the Font Style Id of a Row if exists else returns default
	sheet.getRowFontStyleId = function(rowIndex){
		if(sheet.rows[rowIndex])
			return sheet.rows[rowIndex].getFontStyleId();
		else
			return sheet.defaultFontStyleId;
	}

	//Gets the Font Style Id of a Cell if exists else returns default (of Row, else of Column else of Sheet)
	sheet.getCellFontStyleId = function(rowIndex,colIndex,fontStyleId){
		if(sheet.cells[rowIndex]==undefined)
			if(sheet.rows[rowIndex]!=undefined)
				return sheet.rows[rowIndex].getFontStyleId();
			else
				if(sheet.cols[colIndex]!=undefined)
					return sheet.cols[colIndex].getFontStyleId();
				else
					return sheet.defaultFontStyleId;
		else
			if(sheet.cells[rowIndex][colIndex]==undefined)
				return sheet.rows[rowIndex].getFontStyleId();
			else
				return sheet.cells[rowIndex][colIndex].getFontStyleId();
	}

	//Sets the Font Style Id of a Cell if exists else returns default (of Row, else of Column else of Sheet)
	sheet.setCellFontStyleId = function(rowIndex,colIndex,fontStyleId,dontStore){
		
		if(dontStore == undefined){
			var state = new State({row:rowIndex, col:colIndex},'fstyle',this.getCellFontStyleId(rowIndex,colIndex),fontStyleId) ;
			this.store.add(state);
		}
		
		if(sheet.cells[rowIndex]==undefined)
			sheet.addCell(rowIndex, colIndex);
		else
			if(sheet.cells[rowIndex][colIndex]==undefined)
				sheet.addCell(rowIndex, colIndex);

		sheet.cells[rowIndex][colIndex].setFontStyleId(fontStyleId);
	}
	
	sheet.changeCellFontStyleProp = function(rowIndex,colIndex,property,value,dontStore){
//		var cell = sheet.cells[rowIndex][colIndex];
		var styleId = this.getCellFontStyleId(rowIndex,colIndex);
		var newStyleId = Styler.changeFontStyleProp(styleId,property,value);
		this.setCellFontStyleId(rowIndex,colIndex,newStyleId,dontStore)
//		cell.setFontStyleId(newStyleId);
	}
	
	sheet.changeColumnFontStyleProp = function(column,property,value){
		if(sheet.cols[column]==undefined)
			sheet.addColumn(column);
		//TODO: cambiar acoplamiento con Styler
		var styleId = this.getColumnFontStyleId(column);
		var newStyleId = Styler.changeFontStyleProp(styleId,property,value);
		
		sheet.cols[column].setFontStyleId(newStyleId);

		for(var i=0;i<sheet.cells.length;i++)
			if(sheet.cells[i])
				if(sheet.cells[i][column])
					this.changeCellFontStyleProp(i,column,property,value);
				
	}
	
	sheet.changeRowFontStyleProp = function(row,property,value){
		if(sheet.rows[row]==undefined)
			sheet.addRow(row);
		//TODO: cambiar acoplamiento con Styler
		var styleId = this.getRowFontStyleId(row);
		var newStyleId = Styler.changeFontStyleProp(styleId,property,value);
		
		sheet.rows[row].setFontStyleId(newStyleId);

		if(sheet.cells[row])
			for(var i=0;i<sheet.cells[row].length;i++)			
				if(sheet.cells[row][i])
					this.changeCellFontStyleProp(row,i,property,value);
				
	}

	sheet.setColumnFontStyleId = function(column,fontStyleId, dontStore){
		if(sheet.cols[column]==undefined)
			sheet.addColumn(column);

		sheet.cols[column].setFontStyleId(fontStyleId);

		for(var i=0;i<sheet.cells.length;i++)
			if(sheet.cells[i])
				if(sheet.cells[i][column])
					sheet.setCellFontStyleId(i,column,fontStyleId,dontStore);					
//					sheet.cells[i][column].setFontStyleId(fontStyleId);
	}
	
	/**
	 * Arreglar
	 */
	sheet.setRowFontStyleId = function(row,fontStyleId,dontStore){
		if(sheet.rows[row]==undefined)
			sheet.addRow(row);

		sheet.rows[row].setFontStyleId(fontStyleId);
		
		if(sheet.cells[row])
			for (var i in sheet.cells[row]){
				if (i != 'remove')
					sheet.setCellFontStyleId(row,i,fontStyleId,dontStore);
			}
	}
	
	/**
	 * pepe
	 * Only set the view, not the model.. perico help !
	 */
	sheet.setColumnBgColor = function(column,color){
/*		if(sheet.cols[column]==undefined)
			sheet.addColumn(column);
*/
		for(var i=0;i<sheet.cells.length;i++)
			//if(sheet.cells[i])
				//if(sheet.cells[i][column])
					application.grid.cells[i][column].style.background = color ;
					//Todo change the logic and not the view..
	}

	sheet.setRowBgColor = function(row,color){
		if(sheet.rows[row]==undefined)
			sheet.addRow(row);

		sheet.rows[row].setFontStyleId(fontStyleId);
		if(sheet.cells[row])
			for(var i=0;i<sheet.cells[row].length;i++){
				sheet.cells[row][i].setFontStyleId(fontStyleId);
			}

	}

}