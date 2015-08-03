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
var VIEW_MODE_VALUES 	= 0;
var VIEW_MODE_FORMULAS 	= 1;
var VIEW_MODE_TYPES 	= 2;

function GridModel(grid){
	var self = this;
	WrapEvents(self);
//	self.register("NameAdded");
	self.register("Error");
	self.register("NameChanged");
	self.register("ActiveCellChanged");
	self.register("SelectionChanged");
	
	self.construct = function(){
		this.viewport = new Range({row:0,col:0},{row:grid.getViewport().row,col:grid.getViewport().col});
		this.gridPosition = {x:grid.getVisibleWidth(),y:grid.getVisibleHeight()};
		this.scrollPageOffset = {x:800,y:1500};
		this.activeCell = {row:0,col:0};
		this.selection = new DataSelectionHandler();
		this.selection.setSelection(new Range({row:0,col:0}).normalize());
		this.viewMode = VIEW_MODE_VALUES; //Cell Shows Values, {"Values","Formulas","Types"}
		addModelStyleOperations(this);
		this.store = new SimpleStore();
	};
	
	
	self.getRelativeRange = function(range){
		var result = range.clone();
		result.sub(this.viewport.start.row,this.viewport.start.col);
		return result;
	};
	
	self.getAbsoluteRange = function(range){
		range.add(this.viewport.start.row,this.viewport.start.col);
		return range;
	};
	
	self.updateGridHeight = function(){
		if(grid.getMinHeight() < this.model.getHeight())
			grid.setHeight(this.model.getHeight());
	};

	self.updateGridWidth = function(){
		if(grid.getMinWidth() < this.model.getWidth())
			grid.setWidth(this.model.getWidth());
	};

	self.setDataModel = function(model){
		this.model = model;
		this.updateGridHeight();
		this.updateGridWidth();
		this.refresh();
	};

	self.refresh = function(){
		//refresh Columns
		for(var j = 0; j< (this.viewport.end.col - this.viewport.start.col +1);j++){
			grid.getColumn(j).setTitle(this.model.getColumnName(this.viewport.start.col + j));
			grid.getColumn(j).setSize(this.model.getColumnSize(this.viewport.start.col + j));
		}
		grid.adjustViewPortX();
		
		//alert(this.viewport.start.row + " " + this.viewport.start.col + "\n" + this.viewport.end.row + " " + this.viewport.end.col);
		for(var i =0; i< (this.viewport.end.row - this.viewport.start.row +1);i++){
			grid.getRow(i).setTitle(this.model.getRowName(this.viewport.start.row + i));
			grid.getRow(i).setSize(this.model.getRowSize(this.viewport.start.row + i));

			for(var j = 0; j< (this.viewport.end.col - this.viewport.start.col);j++){
				var cell = this.model.getCell(this.viewport.start.row + i, this.viewport.start.col+ j);
				self.refreshVCell(cell,i,j);
			}
		}
		
		grid.adjustViewPort();
		this.viewport.end.row = this.viewport.start.row + grid.getViewport().row;
		this.viewport.end.col = this.viewport.start.col + grid.getViewport().col;
		this.drawSelections();
	};


	self.refreshValues = function(){
		for(var i =0; i< (this.viewport.end.row - this.viewport.start.row +1);i++){
			for(var j = 0; j< (this.viewport.end.col - this.viewport.start.col);j++){
				var cell = this.model.getCell(this.viewport.start.row + i, this.viewport.start.col+ j);
				
				if(cell){
					var value = cell.getValue();
					if (value ==undefined) value = "";
					grid.setValue(i,j,value);
				}
			}
		}
			
	};
	
	self.refreshVCell = function(cell,row,col){
		if(cell){
			if(self.viewMode == VIEW_MODE_VALUES)
//				var value = cell.getFormattedValue();
				var value = cell.getFormattedValue();
			else
//				var value = cell.getFormula();
				var value = cell.getValueTypeName();
			
			if (value == undefined) value = "";
			grid.setCell(row,col,value,cell.getFontStyleId());
		}else
			grid.setCell(row,col,"",0,0);
	};
	
	self.changeActiveCell = function(address){
		if(address!=undefined) //Sometimes its called for firing event, not for changing active cell
			self.activeCell = address;
		
		var value = this.model.getFormula(self.activeCell.row, self.activeCell.col);
		if(value==undefined)
			value = "";
	
		self.fire("ActiveCellChanged",value);
	};
	
	self.setActiveCellFormula = function(value,dontTrigger){
		var oldValue = self.model.getFormula(self.activeCell.row, self.activeCell.col);
		if(oldValue == undefined) oldValue = "";
		
		if(oldValue != value){
			self.beginTransaction() ;	
			try{
				self.model.setFormula(self.activeCell.row, self.activeCell.col,value);
			}catch(e){
				self.fire("Error",e);
			}
		}
		//TODO: arreglar esta linea arregla la celda oculta en la seleccion de filas o columnas pero rompe la navegacion no se porque
//		grid.setValue(self.activeCell.row-self.viewport.start.row,self.activeCell.col-self.viewport.start.col, self.model.getValue(self.activeCell.row, self.activeCell.col,value));
		
//		}else{
//			if(self.model.getCellFontStyleId(absRange.start.row,absRange.start.col)!=0)
//				self.model.deleteCell(absRange.start.row,absRange.start.col);
//		}
		if(dontTrigger == undefined)
			self.fire("ActiveCellChanged",value);
	}
	
	self.setSelection = function(range,dontTrigger){
		self.selection.setSelection(range);		
		if(dontTrigger==undefined)
			self.fire("SelectionChanged",self.model.getRangeName(range));
	};
	
//	self.deleteSelection = function(){
//		var selections = self.selection.getSelection();
//		for(var k=0;k < selections.length;k++){
//			var selection = selections[k].normalize();
//			if(selection.start)
//				if(selection.start.row!=undefined && selection.start.col!=undefined)
//					if(selection.end)
//						if(selection.end.row !=undefined && selection.end.col!=undefined){
//							self.beginTransaction() ;
//							for(var i = selection.start.row ; i<= selection.end.row;i++)
//								for(var j = selection.start.col ; j<= selection.end.col;j++){
//									self.model.setFormula(i,j,"");
//								}
//						}
//		}
//		self.refresh();
//	};
	self._applyToSelection = function(cellCallback, rowCallback, colCallback){
		var selections = self.selection.getSelection();
		for(var k=0;k < selections.length;k++){
			var selection = selections[k].normalize();
			if(selection.start)
				if(selection.start.row!=undefined && selection.start.col!=undefined){			
					if(selection.end){						
						if(selection.end.row !=undefined && selection.end.col!=undefined){
							self.beginTransaction() ;
							for(var i = selection.start.row ; i<= selection.end.row;i++)
								for(var j = selection.start.col ; j<= selection.end.col;j++){
									cellCallback(i,j);
								}
						}
					}
				}else{
					self.beginTransaction() ;
					if(selection.isRow()){
						for(var i= selection.start.row; i <= selection.end.row; i++)								
							rowCallback(i);
					}else{ //isColumn selection
						for(var i= selection.start.col; i <= selection.end.col; i++)								
							colCallback(i);						
					}
				}
		}
	};
	
	self.deleteSelection = function(){		
		self._applyToSelection(
				function(i,j){self.model.setFormula(i,j,"");},
				self.model.deleteRowValues,
				self.model.deleteColValues
		);
		self.refresh();
	};
	
	self.increaseDecimals = function(){
		var decimals = self.model.getDecimals(self.activeCell.row,self.activeCell.col);
		if(decimals > 0)
			decimals++;
		else
			decimals = 1;
		
		self._applyToSelection(
				function(i,j){self.model.setDecimals(i,j,decimals);},
				function(i){}, 	//idle function
				function(i){}	//idle function
		);
		self.refresh();
	};
	
	self.decreaseDecimals = function(){
		var decimals = self.model.getDecimals(self.activeCell.row,self.activeCell.col);
		if(decimals > 0)
			decimals--;
		else
			decimals = 0;
		
		self._applyToSelection(
				function(i,j){self.model.setDecimals(i,j,decimals);},
				function(i){}, 	//idle function
				function(i){}	//idle function
		);
		self.refresh();
	};
	
	
//	self.deleteSelection = function(){
//		var selections = self.selection.getSelection();
//		for(var k=0;k < selections.length;k++){
//			var selection = selections[k].normalize();
//			if(selection.start)
//				if(selection.start.row!=undefined && selection.start.col!=undefined){			
//					if(selection.end){						
//						if(selection.end.row !=undefined && selection.end.col!=undefined){
//							self.beginTransaction() ;
//							for(var i = selection.start.row ; i<= selection.end.row;i++)
//								for(var j = selection.start.col ; j<= selection.end.col;j++){
//									self.model.setFormula(i,j,"");
//								}
//						}
//					}
//				}else{
//					self.beginTransaction() ;
//					if(selection.isRow()){
//						for(var i= selection.start.row; i <= selection.end.row; i++)								
//							self.model.deleteRowValues(i);
//					}else{ //isColumn selection
//						for(var i= selection.start.col; i <= selection.end.col; i++)								
//							self.model.deleteColValues(i);						
//					}
//				}
//		}
//		self.refresh();
//	};
	
	
	self.setValueToSelection = function(){
		var selections = self.selection.getSelection();
		var value = grid.cellEditor.getValue();
		for(var k=0;k < selections.length;k++){
			var selection = selections[k].normalize();
			if(selection.start.row!=undefined && selection.start.col!=undefined)
				if(selection.end.row!=undefined && selection.end.col!=undefined){
					self.beginTransaction() ;
					for(var i = selection.start.row ; i<= selection.end.row;i++)
						for(var j = selection.start.col ; j<= selection.end.col;j++){
							try{
								self.model.setFormula(i,j,value);
							}catch(e){
								self.fire("Error",e);
							}
						}
				}
		}
		self.refresh();
	};

	self.isRangeVisible = function(row,col){
		if(row!=undefined)
			if(row<self.viewport.start.row || row>=self.viewport.end.row)
				return false;
	
		if(col!=undefined)
			if(col<self.viewport.start.col || col >=self.viewport.end.col)
				return false;
		return true;
	};
	
	self.editActiveCell = function(value){
		grid.cellEditor.setValue(value);
	};
	
	//Undo Redo Related functions 
	self.undo = function () {
		self.model.rollBack() ;	
		self.selection.rollBack();
		self.rollBack();
		self.refresh() ;
	}
	
	self.redo = function () {
		self.model.restore() ;
		self.selection.restore();
		self.restore() ;
		self.refresh() ;
	}
	
	self.rollBack = function () {
		if(this.store.canRollBack()){
			var temp = self.store.getCurrent() ;
			self.store.rollBack(self.activeCell) ;
			self.changeActiveCell(temp);
		}
	}


	self.restore = function () {
		if ( this.store.canRestore() ) {
			var temp = this.store.restore(self.activeCell) ; 
			self.changeActiveCell(temp);
		}  
	}
	
	self.saveState = function(){
		self.store.set ({row:self.activeCell.row,col:self.activeCell.col});
	}
	
	self.beginTransaction = function(){
		self.store.beginTransaction() ;
		self.model.beginTransaction() ;
		self.selection.beginTransaction();
		self.saveState();
	}
	
	
	//Names Handling Related Functions
	self.addName = function(name){
		var changed =  self.model.addName(name,self.selection.getActiveSelection().clone());
		self.fire("NameChanged",name);
		return changed;
	};
	
	self.getNames = function(){
		return self.model.getNames();
	}
	
	self.deleteName = function(name){
		self.model.deleteName(name);
		self.fire("NameChanged");
	};
	
	self.existsName = function(name){
		var temp =  self.model.existsName(name);
		return temp;
	};
	
    self.getActiveCellValue = function(){
    	return self.model.getValue()
    };
	
    /**
     * Changes the way the cell content is shown
     * Formulas Or Value, if viewMode is undefined the behaviour is to alter between values
     */
    self.changeViewMode = function(viewMode){
    	if(viewMode!=undefined)
    		self.viewMode = viewMode;
    	else
    		self.viewMode = !self.viewMode;
    	self.refresh();
    };
    
	addDataModelSelection(self,grid);
	self.construct();
	addModelNavigation(self,grid);
	ExtendModelEvents(self,grid);
	return self;
}